<?php
/**
 * Postmeta helper indexes.
 *
 * Almost everything this plugin counts — seats sold, tickets per event date,
 * attendees per order — is a postmeta lookup by (meta_key, meta_value) or a
 * fetch of several meta keys for one post. WordPress ships `wp_postmeta` with
 * an index on `meta_key` alone and one on `post_id` alone, so both shapes
 * degrade into scanning every row that shares a meta key.
 *
 * On a store with real booking history that is the difference between a 2 ms
 * seat count and a 220 ms one — and `mep_ticket_type_sold()` runs on every
 * event page, every availability check and every admin booking form.
 *
 * The two indexes below are added once, in the background, and can be removed
 * again at any time; nothing in the plugin depends on their existence.
 *
 * Opt out entirely with:
 *     add_filter( 'mep_enable_postmeta_indexes', '__return_false' );
 *
 * @package mage-eventpress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_DB_Index {

	/** Option holding the install state for each index. */
	const STATE_OPTION = 'mep_postmeta_index_state';

	/** Cron hook that performs the (potentially slow) ALTER TABLE. */
	const CRON_HOOK = 'mep_create_postmeta_indexes';

	/** Short-lived lock so two requests never ALTER the same table at once. */
	const LOCK_TRANSIENT = 'mep_postmeta_index_lock';

	/**
	 * The indexes this plugin would like on wp_postmeta.
	 *
	 * @return array<string,string> Index name => column list.
	 */
	public static function wanted() {
		return array(
			// Seat/ticket counts: "every attendee of event X", "…with status Y".
			'mep_key_value' => 'meta_key(32), meta_value(64)',
			// Reading several meta keys for one post, which is what the report
			// screens do for every attendee row they aggregate.
			'mep_post_key'  => 'post_id, meta_key(32)',
		);
	}

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_schedule' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'install' ) );
	}

	/**
	 * Queue the index creation for a background run.
	 *
	 * Never performed inline: on a large table the ALTER can take a while, and
	 * no admin page should wait for it.
	 */
	public static function maybe_schedule() {
		if ( ! apply_filters( 'mep_enable_postmeta_indexes', true ) ) {
			return;
		}

		$state = self::get_state();
		if ( ! self::pending_indexes( $state ) ) {
			return;
		}

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_single_event( time() + MINUTE_IN_SECONDS, self::CRON_HOOK );
		}
	}

	/**
	 * Create any missing index. Safe to call repeatedly.
	 */
	public static function install() {
		global $wpdb;

		if ( ! apply_filters( 'mep_enable_postmeta_indexes', true ) ) {
			return;
		}

		// A single attempt at a time, and never a second attempt while one runs.
		if ( get_transient( self::LOCK_TRANSIENT ) ) {
			return;
		}
		set_transient( self::LOCK_TRANSIENT, 1, 15 * MINUTE_IN_SECONDS );

		$state    = self::get_state();
		$existing = self::existing_indexes();

		foreach ( self::wanted() as $name => $columns ) {
			if ( isset( $state[ $name ] ) && 'created' === $state[ $name ] ) {
				continue;
			}

			// Someone else (a host, another plugin) may already provide it.
			if ( in_array( $name, $existing, true ) ) {
				$state[ $name ] = 'created';
				continue;
			}

			$suppress = $wpdb->suppress_errors( true );
			$hide     = $wpdb->hide_errors();

			// INPLACE/LOCK=NONE keeps the table readable and writable while the
			// index builds. Older servers reject the clause, so a plain ADD INDEX
			// is tried next.
			$ok = $wpdb->query( "ALTER TABLE {$wpdb->postmeta} ADD INDEX {$name} ({$columns}), ALGORITHM=INPLACE, LOCK=NONE" );
			if ( false === $ok ) {
				$ok = $wpdb->query( "ALTER TABLE {$wpdb->postmeta} ADD INDEX {$name} ({$columns})" );
			}

			$wpdb->suppress_errors( $suppress );
			if ( $hide ) {
				$wpdb->show_errors();
			}

			// A failure is recorded, not retried forever: the plugin works
			// without these indexes, just more slowly.
			$state[ $name ] = ( false === $ok ) ? 'failed' : 'created';
		}

		$state['checked'] = time();
		update_option( self::STATE_OPTION, $state, false );
		delete_transient( self::LOCK_TRANSIENT );
	}

	/**
	 * Remove the indexes this class added. Nothing depends on them.
	 *
	 * @return array<string,bool> Index name => removed.
	 */
	public static function uninstall() {
		global $wpdb;

		$existing = self::existing_indexes();
		$result   = array();

		foreach ( array_keys( self::wanted() ) as $name ) {
			if ( ! in_array( $name, $existing, true ) ) {
				$result[ $name ] = true;
				continue;
			}
			$suppress        = $wpdb->suppress_errors( true );
			$result[ $name ] = ( false !== $wpdb->query( "ALTER TABLE {$wpdb->postmeta} DROP INDEX {$name}" ) );
			$wpdb->suppress_errors( $suppress );
		}

		delete_option( self::STATE_OPTION );
		return $result;
	}

	/**
	 * Index names currently present on wp_postmeta.
	 *
	 * @return string[]
	 */
	public static function existing_indexes() {
		global $wpdb;

		$rows  = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->postmeta}", ARRAY_A );
		$names = array();
		foreach ( (array) $rows as $row ) {
			if ( isset( $row['Key_name'] ) ) {
				$names[ $row['Key_name'] ] = true;
			}
		}
		return array_keys( $names );
	}

	/**
	 * @return array
	 */
	private static function get_state() {
		$state = get_option( self::STATE_OPTION, array() );
		return is_array( $state ) ? $state : array();
	}

	/**
	 * Index names still to attempt.
	 *
	 * @param array $state
	 * @return string[]
	 */
	private static function pending_indexes( $state ) {
		$pending = array();
		foreach ( array_keys( self::wanted() ) as $name ) {
			$done = isset( $state[ $name ] ) && in_array( $state[ $name ], array( 'created', 'failed' ), true );
			if ( ! $done ) {
				$pending[] = $name;
			}
		}
		return $pending;
	}
}
