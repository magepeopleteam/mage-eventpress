<?php
/**
 * Self-healing for event orders that never produced attendee posts.
 *
 * WHY THIS EXISTS
 * ---------------
 * checkout_order_processed() used to run json_decode() on whatever its hook handed
 * it. `woocommerce_store_api_checkout_order_processed` passes a WC_Order OBJECT, and
 * on PHP 8 json_decode( WC_Order ) is an uncaught TypeError — so on every site using
 * the block or express checkout (_created_via = store-api) the handler died before
 * creating a single attendee. The orders were taken and paid; the attendees were not
 * written. Seats sold are counted from attendee posts, so those events kept reporting
 * full availability and the attendee report came back empty.
 *
 * The handler itself is fixed, but that only helps orders taken from now on. This
 * class repairs the ones already on the books, and keeps watching so a future gap
 * heals itself instead of waiting to be noticed.
 *
 * HOW IT WORKS
 * ------------
 * 1. A one-time backfill walks the order history newest-first, in small batches on
 *    WP-Cron, rebuilding any event order that has no attendees. It keeps a cursor so
 *    it is a single descending pass, not a repeated full scan, and stops for good
 *    once it reaches the end.
 * 2. A live safety net re-checks each event order as its status changes and rebuilds
 *    it if it somehow still has no attendees.
 *
 * Both routes call the plugin's own checkout handler, so attendees are built exactly
 * the way a normal checkout builds them — there is no second implementation to drift.
 *
 * SAFETY
 * ------
 * - Orders that already have at least one attendee are never touched. That matters:
 *   with the recurring-events addon active, checkout_order_processed() takes its
 *   unconditional create branch, so re-running it over a rebuilt order would
 *   duplicate attendees.
 * - Work happens on cron, never inline on a page load.
 * - A transient lock keeps two runs from overlapping.
 * - `mep_after_event_booking` is unhooked during the backfill so the Pro Google
 *   Sheets sync does not fire hundreds of times for historic orders. Customer
 *   emails are sent from order_status_changed(), which this never triggers.
 * - Orders whose event post has since been deleted simply produce nothing; the
 *   cursor moves past them so they are not retried forever.
 *
 * Opt out entirely with:
 *     add_filter( 'mep_enable_attendee_repair', '__return_false' );
 *
 * Re-run the backfill from scratch (support tool):
 *     delete_option( 'mep_attendee_repair_state' );
 *
 * @package mage-eventpress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Attendee_Repair {

	/** Option holding the backfill cursor and counters. */
	const STATE_OPTION = 'mep_attendee_repair_state';

	/** Cron hook that runs one backfill batch. */
	const CRON_HOOK = 'mep_repair_missing_attendees';

	/** Short-lived lock so two runs never overlap. */
	const LOCK_TRANSIENT = 'mep_attendee_repair_lock';

	/** Daily hook that re-arms the backfill after a later loss of attendees. */
	const AUDIT_HOOK = 'mep_attendee_repair_audit';

	/** Orders inspected per batch. Filter with 'mep_attendee_repair_batch_size'. */
	const BATCH_SIZE = 25;

	/** Newest orders the daily audit samples. Filter with 'mep_attendee_audit_sample'. */
	const AUDIT_SAMPLE = 20;

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_schedule' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'run_batch' ) );
		add_action( self::AUDIT_HOOK, array( __CLASS__, 'audit' ) );
		// Priority 6: after repair_orphan_event_booking() (5) has had a chance to put
		// missing line-item meta back, and before order_status_changed() (10), which
		// expects the attendees to exist so it can move them to the order's status.
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'heal_order' ), 6, 4 );
	}

	/** @return bool Whether the repair is allowed to do anything at all. */
	private static function enabled() {
		return (bool) apply_filters( 'mep_enable_attendee_repair', true );
	}

	/**
	 * Queue the next backfill batch. Never performs work inline.
	 */
	public static function maybe_schedule() {
		if ( ! self::enabled() ) {
			return;
		}

		// Scheduled whether or not the one-time pass has finished: attendee posts can be
		// lost long after it latched `done`, and audit() is the only thing that notices.
		if ( ! wp_next_scheduled( self::AUDIT_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::AUDIT_HOOK );
		}

		$state = self::get_state();
		if ( ! empty( $state['done'] ) ) {
			return;
		}

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_single_event( time() + MINUTE_IN_SECONDS, self::CRON_HOOK );
		}
	}

	/**
	 * Restart the backfill when a rebuildable order has lost its attendees.
	 *
	 * run_batch() sets `done` as soon as it reaches the end of the history and never
	 * looks again. That is right for the original one-time repair, but it also means
	 * attendee posts deleted AFTER that pass — a mistaken bulk delete, a restore from a
	 * partial backup — stay gone. Seats sold are counted from those posts, so every
	 * affected event silently goes back to reporting full availability and the attendee
	 * report comes back empty, with nothing left to heal it: heal_order() only fires on
	 * a status change, which a completed order never makes again.
	 *
	 * Costs one bounded lookup a day, and only re-arms on orders that can actually be
	 * rebuilt, so an order whose event has since been deleted cannot restart the walk
	 * every night for nothing.
	 */
	public static function audit() {
		if ( ! self::enabled() ) {
			return;
		}

		$state = self::get_state();
		if ( empty( $state['done'] ) ) {
			return; // A pass is already in flight; let it finish.
		}

		$sample  = max( 1, (int) apply_filters( 'mep_attendee_audit_sample', self::AUDIT_SAMPLE ) );
		$missing = false;
		foreach ( self::rebuildable_orders( $sample ) as $order_id ) {
			if ( 0 === self::attendee_count( $order_id ) ) {
				$missing = true;
				break;
			}
		}
		if ( ! $missing ) {
			return;
		}

		unset( $state['done'], $state['finished'] );
		$state['cursor'] = PHP_INT_MAX; // Walk the whole history again, newest first.
		update_option( self::STATE_OPTION, $state, false );

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_single_event( time() + MINUTE_IN_SECONDS, self::CRON_HOOK );
		}
	}

	/**
	 * Rebuild one batch of orders, then queue the next one.
	 */
	public static function run_batch() {
		if ( ! self::enabled() ) {
			return;
		}

		$state = self::get_state();
		if ( ! empty( $state['done'] ) ) {
			return;
		}

		if ( get_transient( self::LOCK_TRANSIENT ) ) {
			return;
		}
		set_transient( self::LOCK_TRANSIENT, 1, 10 * MINUTE_IN_SECONDS );

		$batch  = max( 1, (int) apply_filters( 'mep_attendee_repair_batch_size', self::BATCH_SIZE ) );
		$cursor = isset( $state['cursor'] ) ? (int) $state['cursor'] : PHP_INT_MAX;
		$orders = self::event_orders_below( $cursor, $batch );

		if ( empty( $orders ) ) {
			// Reached the end of the history: this is a one-time pass, so stop for good
			// and drop the stale seat caches once, so the counts recomputed from the
			// attendees we just wrote are the ones the capacity screens read.
			self::flush_seat_caches();
			$state['done']      = true;
			$state['finished']  = time();
			update_option( self::STATE_OPTION, $state, false );
			delete_transient( self::LOCK_TRANSIENT );

			return;
		}

		// Historic orders must not re-trigger the Pro Google Sheets sync, the only
		// listener on this hook.
		remove_all_actions( 'mep_after_event_booking' );

		$repaired = 0;
		$lowest   = $cursor;

		foreach ( $orders as $order_id ) {
			$order_id = (int) $order_id;
			$lowest   = min( $lowest, $order_id );

			if ( self::attendee_count( $order_id ) > 0 ) {
				continue; // Already has attendees - never rebuild over the top of them.
			}
			if ( self::rebuild( $order_id ) ) {
				$repaired ++;
			}
		}

		$state['cursor']   = $lowest;            // Strictly descending: no order is seen twice.
		$state['repaired'] = isset( $state['repaired'] ) ? (int) $state['repaired'] + $repaired : $repaired;
		$state['checked']  = isset( $state['checked'] ) ? (int) $state['checked'] + count( $orders ) : count( $orders );
		$state['updated']  = time();
		update_option( self::STATE_OPTION, $state, false );

		delete_transient( self::LOCK_TRANSIENT );

		// Chain the next batch immediately rather than waiting for an admin page view.
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_single_event( time() + 30, self::CRON_HOOK );
		}
	}

	/**
	 * Live safety net: an event order changing status with no attendees gets rebuilt.
	 *
	 * @param int      $order_id    Order id.
	 * @param string   $from_status Previous status.
	 * @param string   $to_status   New status.
	 * @param WC_Order $order       Order object.
	 */
	public static function heal_order( $order_id, $from_status, $to_status, $order = null ) {
		if ( ! self::enabled() ) {
			return;
		}

		// Nothing to hold a seat for on these, and rebuilding would resurrect attendees
		// the cancellation was meant to release.
		$skip = apply_filters(
			'mep_skip_attendee_repair_status',
			array( 'failed', 'cancelled', 'refunded', 'trash', 'draft', 'checkout-draft' )
		);
		if ( in_array( $to_status, (array) $skip, true ) ) {
			return;
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		// Only event orders, and only when nothing was created for them.
		$has_event = false;
		foreach ( $order->get_items() as $item_id => $item ) {
			if ( wc_get_order_item_meta( $item_id, 'event_id', true ) ) {
				$has_event = true;
				break;
			}
		}
		if ( ! $has_event || self::attendee_count( $order->get_id() ) > 0 ) {
			return;
		}

		self::rebuild( $order->get_id() );
	}

	/**
	 * Run the plugin's own checkout handler for one order.
	 *
	 * @param int $order_id Order id.
	 * @return bool Whether the order ended up with at least one attendee.
	 */
	private static function rebuild( $order_id ) {
		if ( ! class_exists( 'MPWEM_Woocommerce' ) ) {
			return false;
		}
		$handler = MPWEM_Woocommerce::instance();
		if ( ! $handler || ! method_exists( $handler, 'checkout_order_processed' ) ) {
			return false;
		}

		$handler->checkout_order_processed( (int) $order_id );

		// Re-count rather than trusting a return value: the handler has none, and an
		// order whose event post was deleted legitimately produces nothing.
		return self::attendee_count( $order_id ) > 0;
	}

	/**
	 * Attendees currently recorded against an order.
	 *
	 * @param int $order_id Order id.
	 * @return int
	 */
	private static function attendee_count( $order_id ) {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} m
				 JOIN {$wpdb->posts} p ON p.ID = m.post_id AND p.post_type = 'mep_events_attendees'
				 WHERE m.meta_key = 'ea_order_id' AND m.meta_value = %d",
				$order_id
			)
		);
	}

	/**
	 * Ids of orders carrying an event line item, below a cursor, newest first.
	 *
	 * Newest first so the events people are still selling are corrected in the first
	 * batches, and a site that never finishes the walk still has its upcoming events
	 * right.
	 *
	 * @param int $cursor Exclusive upper bound on the order id.
	 * @param int $limit  Batch size.
	 * @return int[]
	 */
	private static function event_orders_below( $cursor, $limit ) {
		global $wpdb;

		$items    = $wpdb->prefix . 'woocommerce_order_items';
		$itemmeta = $wpdb->prefix . 'woocommerce_order_itemmeta';

		if ( self::hpos_enabled() ) {
			// High-Performance Order Storage keeps orders in wp_wc_orders, so wp_posts
			// holds no row to join against.
			$orders = $wpdb->prefix . 'wc_orders';
			$sql    = "SELECT o.id
				FROM {$orders} o
				JOIN {$items} oi ON oi.order_id = o.id AND oi.order_item_type = 'line_item'
				JOIN {$itemmeta} im ON im.order_item_id = oi.order_item_id AND im.meta_key = 'event_id'
				WHERE o.type = 'shop_order'
				  AND o.status NOT IN ( 'wc-failed', 'wc-cancelled', 'wc-refunded', 'wc-checkout-draft', 'trash', 'auto-draft' )
				  AND o.id < %d
				GROUP BY o.id
				ORDER BY o.id DESC
				LIMIT %d";
		} else {
			$sql = "SELECT o.ID
				FROM {$wpdb->posts} o
				JOIN {$items} oi ON oi.order_id = o.ID AND oi.order_item_type = 'line_item'
				JOIN {$itemmeta} im ON im.order_item_id = oi.order_item_id AND im.meta_key = 'event_id'
				WHERE o.post_type = 'shop_order'
				  AND o.post_status NOT IN ( 'wc-failed', 'wc-cancelled', 'wc-refunded', 'wc-checkout-draft', 'trash', 'auto-draft' )
				  AND o.ID < %d
				GROUP BY o.ID
				ORDER BY o.ID DESC
				LIMIT %d";
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names are built from $wpdb->prefix; both values are placeholders.
		return array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( $sql, $cursor, $limit ) ) );
	}

	/**
	 * Newest orders that carry an event line item whose event post still exists.
	 *
	 * The event join is what separates "these attendees went missing" from "this event
	 * was deleted, so it can never be rebuilt" — rebuild() produces nothing for the
	 * latter, and without the join a single such order at the top of the history would
	 * make audit() restart the whole walk every night.
	 *
	 * @param int $limit How many orders to return.
	 * @return int[]
	 */
	private static function rebuildable_orders( $limit ) {
		global $wpdb;

		$items    = $wpdb->prefix . 'woocommerce_order_items';
		$itemmeta = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$excluded = "( 'wc-failed', 'wc-cancelled', 'wc-refunded', 'wc-checkout-draft', 'trash', 'auto-draft' )";

		if ( self::hpos_enabled() ) {
			$orders = $wpdb->prefix . 'wc_orders';
			$sql    = "SELECT o.id
				FROM {$orders} o
				JOIN {$items} oi ON oi.order_id = o.id AND oi.order_item_type = 'line_item'
				JOIN {$itemmeta} im ON im.order_item_id = oi.order_item_id AND im.meta_key = 'event_id'
				JOIN {$wpdb->posts} e ON e.ID = im.meta_value AND e.post_type = 'mep_events'
				WHERE o.type = 'shop_order'
				  AND o.status NOT IN {$excluded}
				GROUP BY o.id
				ORDER BY o.id DESC
				LIMIT %d";
		} else {
			$sql = "SELECT o.ID
				FROM {$wpdb->posts} o
				JOIN {$items} oi ON oi.order_id = o.ID AND oi.order_item_type = 'line_item'
				JOIN {$itemmeta} im ON im.order_item_id = oi.order_item_id AND im.meta_key = 'event_id'
				JOIN {$wpdb->posts} e ON e.ID = im.meta_value AND e.post_type = 'mep_events'
				WHERE o.post_type = 'shop_order'
				  AND o.post_status NOT IN {$excluded}
				GROUP BY o.ID
				ORDER BY o.ID DESC
				LIMIT %d";
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names are built from $wpdb->prefix; the status list is a literal; the limit is a placeholder.
		return array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( $sql, $limit ) ) );
	}

	/**
	 * Drop the cached seat counts so they are recomputed from the attendee posts.
	 *
	 * They are only recalculated when the cached value is EMPTY (see
	 * mep_get_event_total_seat_left() and mep_get_ticket_type_seat_count()), so a
	 * stale cache would keep reporting "all available" even once the attendees exist.
	 * Three shapes are cached per event: 'mep_total_seat_left', "<event_id>_<YmdHi>"
	 * and "<Ticket Type Name>_<YmdHi>" - the last two both end in an underscore plus a
	 * 12-digit stamp. Deleting them only drops a derived value; it rebuilds on read.
	 */
	private static function flush_seat_caches() {
		global $wpdb;

		$event_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'mep_events'" );

		foreach ( $event_ids as $event_id ) {
			$event_id = (int) $event_id;
			delete_post_meta( $event_id, 'mep_total_seat_left' );

			$keys = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key REGEXP '_[0-9]{12}$'",
					$event_id
				)
			);
			foreach ( $keys as $key ) {
				delete_post_meta( $event_id, $key );
			}
		}
	}

	/** @return bool Whether WooCommerce stores orders in its own table. */
	private static function hpos_enabled() {
		return class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}

	/** @return array The backfill state. */
	public static function get_state() {
		$state = get_option( self::STATE_OPTION, array() );

		return is_array( $state ) ? $state : array();
	}
}
