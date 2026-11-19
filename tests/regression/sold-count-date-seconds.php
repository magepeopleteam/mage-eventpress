<?php
	/**
	 * Regression tests: a fully booked event must count as sold out in the event list.
	 *
	 * Bookings store ea_event_date as 'Y-m-d H:i', and sold seats are counted with a LIKE
	 * on that value. For a non-recurring event the list ribbon and the list card pass the
	 * event_start_datetime meta, which has seconds ('Y-m-d H:i:s'). That never matched,
	 * so a full event counted 0 sold: no "Sold Out" ribbon, card class
	 * event-availabe-seat, while the event page itself correctly said 0 left.
	 *
	 * The sold counters (mep_ticket_type_sold(), MPWEM_Query::attendee_query() behind
	 * MPWEM_Functions::get_total_sold(), mep_gq_ticket_total_sold()) now drop the seconds
	 * before matching. A date-only value must still count the whole day, and a value
	 * that already has no seconds must count exactly as before.
	 *
	 * Not loaded by the plugin. Run it explicitly, on a development or staging site:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/sold-count-date-seconds.php
	 *
	 * A site whose environment type is "production" is refused unless `allow-production` is
	 * passed as an argument. The run creates its own event and attendees, deletes them
	 * afterwards and puts back the one setting it changes (the list "Sold Out" ribbon).
	 * Exits with status 1 when any case fails.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! class_exists( 'MPWEM_Functions' ) || ! function_exists( 'mep_ticket_type_sold' ) ) {
		WP_CLI::error( 'Event Booking Manager for WooCommerce must be active.' );
	}
	$mep_sd_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_sd_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. Run these tests on a test site, or pass allow-production to override.' );
	}

	if ( ! class_exists( 'MEP_SD_Run' ) ) {
		final class MEP_SD_Run {
			/** @var array<string, bool> */
			public static $results = array();
			public static function check( $label, $pass, $detail = '' ) {
				self::$results[ $label ] = (bool) $pass;
				WP_CLI::log( sprintf( '  %s  %s%s', $pass ? 'PASS' : 'FAIL', $label, ( ! $pass && '' !== (string) $detail ) ? ' - got ' . $detail : '' ) );
			}
			public static function attendee( $event_id, $date, $status = 'completed' ) {
				$id = wp_insert_post( array( 'post_type' => 'mep_events_attendees', 'post_status' => 'publish', 'post_title' => 'SD attendee' ) );
				update_post_meta( $id, 'ea_event_id', $event_id );
				update_post_meta( $id, 'ea_event_date', $date );
				update_post_meta( $id, 'ea_order_status', $status );
				update_post_meta( $id, 'ea_ticket_type', 'Standard' );
				return (int) $id;
			}
			public static function render( $hook, ...$hook_args ) {
				ob_start();
				do_action( $hook, ...$hook_args );
				return (string) ob_get_clean();
			}
		}
	}

	$mep_sd_posts       = array();
	$mep_sd_setting     = get_option( 'general_setting_sec', null );
	$mep_sd_setting_had = null !== $mep_sd_setting;

	try {
		$mep_sd_settings = is_array( $mep_sd_setting ) ? $mep_sd_setting : array();
		$mep_sd_settings['mep_show_sold_out_ribbon_list_page'] = 'yes';
		update_option( 'general_setting_sec', $mep_sd_settings );

		// Non-recurring, capacity 12, 15:00-17:00 eight days ahead; the *_datetime meta
		// carries seconds, as the event editor saves it.
		$mep_sd_day   = gmdate( 'Y-m-d', current_time( 'timestamp' ) + 8 * DAY_IN_SECONDS );
		$mep_sd_slot  = $mep_sd_day . ' 15:00';
		$mep_sd_secs  = $mep_sd_slot . ':00';
		$mep_sd_event = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'post_title' => 'SD sold-out event' ) );
		$mep_sd_posts[] = (int) $mep_sd_event;
		$mep_sd_ticket  = array( 'option_name_t' => 'Standard', 'option_qty_t' => 12, 'option_price_t' => 10, 'option_rsv_t' => 0 );
		$mep_sd_meta    = array(
			'mep_enable_recurring'  => 'no',
			'mep_reg_status'        => 'on',
			'mep_event_type'        => 'offline',
			'event_start_date'      => $mep_sd_day,
			'event_start_time'      => '15:00',
			'event_end_date'        => $mep_sd_day,
			'event_end_time'        => '17:00',
			'event_start_datetime'  => $mep_sd_secs,
			'event_end_datetime'    => $mep_sd_day . ' 17:00:00',
			'event_expire_datetime' => $mep_sd_day . ' 17:00:00',
			'mep_event_ticket_type' => array( $mep_sd_ticket ),
		);
		foreach ( $mep_sd_meta as $mep_sd_key => $mep_sd_value ) {
			update_post_meta( $mep_sd_event, $mep_sd_key, $mep_sd_value );
		}
		// 13 bookings on the 15:00 slot (one more than capacity), one on another slot.
		$mep_sd_statuses = array( 'completed', 'processing' );
		for ( $mep_sd_i = 0; $mep_sd_i < 13; $mep_sd_i++ ) {
			$mep_sd_posts[] = MEP_SD_Run::attendee( $mep_sd_event, $mep_sd_slot, $mep_sd_statuses[ $mep_sd_i % 2 ] );
		}
		$mep_sd_posts[] = MEP_SD_Run::attendee( $mep_sd_event, $mep_sd_day . ' 16:00' );

		WP_CLI::log( 'Full event, date passed with seconds:' );
		$mep_sd_n = (int) mep_ticket_type_sold( $mep_sd_event, '', $mep_sd_secs );
		MEP_SD_Run::check( 'mep_ticket_type_sold counts the 13 bookings', 13 === $mep_sd_n, $mep_sd_n );
		$mep_sd_n = (int) mep_ticket_type_sold( $mep_sd_event, 'Standard', $mep_sd_secs );
		MEP_SD_Run::check( 'mep_ticket_type_sold by ticket type counts 13', 13 === $mep_sd_n, $mep_sd_n );
		$mep_sd_n = (int) mep_ticket_type_sold( $mep_sd_event, '', str_replace( ' ', 'T', $mep_sd_secs ) );
		MEP_SD_Run::check( 'an ISO "T" date with seconds counts 13', 13 === $mep_sd_n, $mep_sd_n );
		$mep_sd_n = (int) MPWEM_Functions::get_total_sold( $mep_sd_event, $mep_sd_secs );
		MEP_SD_Run::check( 'get_total_sold counts 13', 13 === $mep_sd_n, $mep_sd_n );
		if ( function_exists( 'mep_gq_ticket_total_sold' ) ) {
			$mep_sd_n = (int) mep_gq_ticket_total_sold( $mep_sd_event, $mep_sd_secs );
			MEP_SD_Run::check( 'mep_gq_ticket_total_sold counts 13', 13 === $mep_sd_n, $mep_sd_n );
		}
		$mep_sd_n = (int) mep_ticket_type_sold( $mep_sd_event, '', $mep_sd_day . ' 16:00:00' );
		MEP_SD_Run::check( 'another slot with seconds counts only its own booking', 1 === $mep_sd_n, $mep_sd_n );

		WP_CLI::log( 'Event list:' );
		$mep_sd_info = MPWEM_Functions::get_all_info( $mep_sd_event );
		$mep_sd_n    = (int) MPWEM_Functions::get_total_available_seat( $mep_sd_event, $mep_sd_info['upcoming_date'] ?? '' );
		MEP_SD_Run::check( 'list card availability is 0 or less', $mep_sd_n <= 0, $mep_sd_n );
		$mep_sd_html = MEP_SD_Run::render( 'mpwem_list_ribbon', $mep_sd_event );
		MEP_SD_Run::check( 'list ribbon shows "Sold Out"', false !== strpos( $mep_sd_html, 'mepev-ribbon sold-out' ) );
		$mep_sd_html = MEP_SD_Run::render( 'mep_event_list_shortcode', $mep_sd_event, 3, 'grid', '', 'sd' );
		MEP_SD_Run::check( 'list card has class event-no-availabe-seat', false !== strpos( $mep_sd_html, 'event-no-availabe-seat' ) && false === strpos( $mep_sd_html, 'event-availabe-seat' ) );

		WP_CLI::log( 'Unchanged:' );
		$mep_sd_n = (int) mep_ticket_type_sold( $mep_sd_event, '', $mep_sd_slot );
		MEP_SD_Run::check( 'a date without seconds counts 13', 13 === $mep_sd_n, $mep_sd_n );
		$mep_sd_n = (int) mep_ticket_type_sold( $mep_sd_event, '', $mep_sd_day );
		MEP_SD_Run::check( 'a date-only value counts the whole day (14)', 14 === $mep_sd_n, $mep_sd_n );
		$mep_sd_n = (int) mep_ticket_type_sold( $mep_sd_event, '', $mep_sd_day . ' 18:00:00' );
		MEP_SD_Run::check( 'an unbooked slot counts 0', 0 === $mep_sd_n, $mep_sd_n );
		$mep_sd_n = (int) mep_ticket_type_sold( $mep_sd_event );
		MEP_SD_Run::check( 'no date counts every booking (14)', 14 === $mep_sd_n, $mep_sd_n );
		$mep_sd_n = (int) MPWEM_Functions::get_available_ticket( $mep_sd_event, 'Standard', $mep_sd_secs, $mep_sd_ticket );
		MEP_SD_Run::check( 'event page availability stays 12 - 13 = -1', -1 === $mep_sd_n, $mep_sd_n );

		// "Expire On: event end time" makes upcoming_date the event's end (17:00), while the
		// bookings sit on its start (15:00). A fresh event: dates are cached per request.
		WP_CLI::log( 'Event list with "Expire On" set to the event end time:' );
		$mep_sd_settings['mep_event_expire_on_datetimes'] = 'event_end_datetime';
		update_option( 'general_setting_sec', $mep_sd_settings );
		$mep_sd_end_event = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'post_title' => 'SD sold-out event (end mode)' ) );
		$mep_sd_posts[]   = (int) $mep_sd_end_event;
		foreach ( $mep_sd_meta as $mep_sd_key => $mep_sd_value ) {
			update_post_meta( $mep_sd_end_event, $mep_sd_key, $mep_sd_value );
		}
		for ( $mep_sd_i = 0; $mep_sd_i < 13; $mep_sd_i++ ) {
			$mep_sd_posts[] = MEP_SD_Run::attendee( $mep_sd_end_event, $mep_sd_slot );
		}
		$mep_sd_html = MEP_SD_Run::render( 'mpwem_list_ribbon', $mep_sd_end_event );
		MEP_SD_Run::check( 'end-time mode: list ribbon shows "Sold Out"', false !== strpos( $mep_sd_html, 'mepev-ribbon sold-out' ) );
		$mep_sd_html = MEP_SD_Run::render( 'mep_event_list_shortcode', $mep_sd_end_event, 3, 'grid', '', 'sd-end' );
		MEP_SD_Run::check( 'end-time mode: list card has class event-no-availabe-seat', false !== strpos( $mep_sd_html, 'event-no-availabe-seat' ) && false === strpos( $mep_sd_html, 'event-availabe-seat' ) );
	} finally {
		foreach ( $mep_sd_posts as $mep_sd_id ) {
			wp_delete_post( $mep_sd_id, true );
		}
		if ( $mep_sd_setting_had ) {
			update_option( 'general_setting_sec', $mep_sd_setting );
		} else {
			delete_option( 'general_setting_sec' );
		}
	}

	$mep_sd_failed = array_keys( array_filter( MEP_SD_Run::$results, function ( $pass ) {
		return ! $pass;
	} ) );
	if ( $mep_sd_failed ) {
		WP_CLI::warning( count( $mep_sd_failed ) . ' of ' . count( MEP_SD_Run::$results ) . ' checks failed.' );
		exit( 1 );
	}
	WP_CLI::success( 'All ' . count( MEP_SD_Run::$results ) . ' checks passed.' );
