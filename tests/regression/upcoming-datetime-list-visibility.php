<?php
	/**
	 * Regression tests: recurring events with dates still to come must stay in the event lists.
	 *
	 * The lists select on the event_upcoming_datetime meta, which is only recalculated when
	 * something renders the event. Two things left it in the past while the event still had
	 * dates ahead, and the event then vanished from [event-list], the archive and the
	 * category filters until someone opened its own page:
	 *  - the PRO Attendee List and Backend Order pickers labelled events through
	 *    get_upcoming_date_time( $id, get_all_dates( $id ) ), which saved the first date ever;
	 *  - the saved occurrence simply passed, and the lists never rendered the event again.
	 *
	 * Not loaded by the plugin. Run it explicitly, on a development or staging site:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/upcoming-datetime-list-visibility.php
	 *
	 * A site whose environment type is "production" is refused unless `allow-production` is
	 * passed as an argument. The run creates its own events and category, deletes them
	 * afterwards and puts back the one setting it changes (the "Expire On" mode). Exits with
	 * status 1 when any case fails. One process per run: the lists refresh once per request.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! class_exists( 'MPWEM_Query' ) || ! class_exists( 'MPWEM_Functions' ) ) {
		WP_CLI::error( 'Event Booking Manager for WooCommerce must be active.' );
	}
	$mep_ul_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_ul_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. Run these tests on a test site, or pass allow-production to override.' );
	}

	if ( ! class_exists( 'MEP_UL_Run' ) ) {
		final class MEP_UL_Run {
			/** @var array<string, bool> */
			public static $results = array();
			public static function check( $label, $pass, $detail = '' ) {
				self::$results[ $label ] = (bool) $pass;
				WP_CLI::log( sprintf( '  %s  %s%s', $pass ? 'PASS' : 'FAIL', $label, ( ! $pass && '' !== $detail ) ? ' - ' . $detail : '' ) );
			}
			public static function day( $days, $time ) {
				return gmdate( 'Y-m-d', current_time( 'timestamp' ) + $days * DAY_IN_SECONDS ) . ' ' . $time;
			}
			public static function stored( $id ) {
				return (string) get_post_meta( $id, 'event_upcoming_datetime', true );
			}
			public static function ids( WP_Query $query ) {
				return array_map( 'intval', wp_list_pluck( $query->posts, 'ID' ) );
			}
			/** A published "Particular Dates" event: first date plus extra date rows. */
			public static function particular( $title, $first, array $more ) {
				$rows = array();
				foreach ( $more as $date ) {
					list( $d, $t ) = explode( ' ', $date );
					$rows[]        = array( 'event_more_start_date' => $d, 'event_more_start_time' => $t, 'event_more_end_date' => $d, 'event_more_end_time' => gmdate( 'H:i', strtotime( $date ) + 2 * HOUR_IN_SECONDS ) );
				}
				list( $d, $t ) = explode( ' ', $first );
				$last          = $more ? end( $more ) : $first;
				return self::event( $title, array(
					'mep_enable_recurring'  => 'yes',
					'event_start_date'      => $d,
					'event_start_time'      => $t,
					'event_end_date'        => $d,
					'event_end_time'        => gmdate( 'H:i', strtotime( $first ) + 2 * HOUR_IN_SECONDS ),
					'event_start_datetime'  => $first . ':00',
					'event_end_datetime'    => gmdate( 'Y-m-d H:i:s', strtotime( $first ) + 2 * HOUR_IN_SECONDS ),
					'event_expire_datetime' => gmdate( 'Y-m-d H:i:s', strtotime( $last ) + 2 * HOUR_IN_SECONDS ),
					'mep_event_more_date'   => $rows,
				) );
			}
			public static function event( $title, array $meta ) {
				$id = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'post_title' => $title ) );
				$meta['mep_event_ticket_type'] = array( array( 'option_name_t' => 'Standard', 'option_price_t' => 10, 'option_qty_t' => 50 ) );
				foreach ( $meta as $key => $value ) {
					update_post_meta( $id, $key, $value );
				}
				return (int) $id;
			}
			/** Renders a private PRO picker and returns its HTML, or null when PRO is not active. */
			public static function render_private( $class, $method ) {
				if ( ! class_exists( $class ) || ! method_exists( $class, $method ) ) {
					return null;
				}
				$object = ( new ReflectionClass( $class ) )->newInstanceWithoutConstructor();
				$call   = new ReflectionMethod( $object, $method );
				$call->setAccessible( true );
				ob_start();
				$call->invoke( $object );
				return (string) ob_get_clean();
			}
		}
	}

	$mep_ul_setting_had = false !== get_option( 'general_setting_sec', false );
	$mep_ul_setting     = get_option( 'general_setting_sec', array() );
	$mep_ul_set_mode    = function ( $mode ) use ( $mep_ul_setting ) {
		$settings                                  = is_array( $mep_ul_setting ) ? $mep_ul_setting : array();
		$settings['mep_event_expire_on_datetimes'] = $mode;
		update_option( 'general_setting_sec', $settings );
	};
	$mep_ul_posts = array();
	$mep_ul_term  = 0;

	try {
		$mep_ul_cat  = wp_insert_term( 'MEP upcoming test ' . wp_generate_password( 6, false ), 'mep_cat' );
		$mep_ul_term = is_wp_error( $mep_ul_cat ) ? 0 : (int) $mep_ul_cat['term_id'];

		// A: like the reported events. First date long past, dates still to come, and the
		// first date saved by the old Attendee List picker.
		$mep_ul_a = MEP_UL_Run::particular( 'UL A first date past', '2025-06-11 19:30', array( MEP_UL_Run::day( 8, '19:30' ), MEP_UL_Run::day( 50, '19:30' ) ) );
		update_post_meta( $mep_ul_a, 'event_upcoming_datetime', '2025-06-11 19:30' );
		wp_set_object_terms( $mep_ul_a, array( $mep_ul_term ), 'mep_cat' );
		// B: the saved occurrence (yesterday) has passed, another one is next week.
		$mep_ul_b = MEP_UL_Run::particular( 'UL B occurrence passed', MEP_UL_Run::day( -30, '10:00' ), array( MEP_UL_Run::day( -1, '10:00' ), MEP_UL_Run::day( 7, '10:00' ) ) );
		update_post_meta( $mep_ul_b, 'event_upcoming_datetime', MEP_UL_Run::day( -1, '10:00' ) );
		// C: control, saved value already correct.
		$mep_ul_c = MEP_UL_Run::particular( 'UL C all future', MEP_UL_Run::day( 3, '09:30' ), array( MEP_UL_Run::day( 40, '09:30' ) ) );
		update_post_meta( $mep_ul_c, 'event_upcoming_datetime', MEP_UL_Run::day( 3, '09:30' ) );
		// D: control, every date past. Must stay out of the upcoming list and keep its value.
		$mep_ul_d = MEP_UL_Run::particular( 'UL D all past', MEP_UL_Run::day( -20, '09:30' ), array( MEP_UL_Run::day( -10, '09:30' ) ) );
		update_post_meta( $mep_ul_d, 'event_upcoming_datetime', MEP_UL_Run::day( -10, '09:30' ) );
		// E: control, a single event that has started. Single events are not recurring and keep their value.
		$mep_ul_e = MEP_UL_Run::event( 'UL E single started', array(
			'mep_enable_recurring'    => 'no',
			'event_start_date'        => trim( MEP_UL_Run::day( -2, '' ) ),
			'event_start_time'        => '10:00',
			'event_end_date'          => trim( MEP_UL_Run::day( 2, '' ) ),
			'event_end_time'          => '12:00',
			'event_start_datetime'    => MEP_UL_Run::day( -2, '10:00:00' ),
			'event_end_datetime'      => MEP_UL_Run::day( 2, '12:00:00' ),
			'event_expire_datetime'   => MEP_UL_Run::day( 2, '12:00:00' ),
			'event_upcoming_datetime' => MEP_UL_Run::day( -2, '10:00:00' ),
		) );
		// F: a repeated ("everyday") event that started last year and runs into next year.
		$mep_ul_f = MEP_UL_Run::event( 'UL F everyday', array(
			'mep_enable_recurring'    => 'everyday',
			'event_start_date'        => trim( MEP_UL_Run::day( -400, '' ) ),
			'event_start_time'        => '10:00',
			'event_end_date'          => trim( MEP_UL_Run::day( 200, '' ) ),
			'event_end_time'          => '12:00',
			'event_start_datetime'    => MEP_UL_Run::day( -400, '10:00:00' ),
			'event_end_datetime'      => MEP_UL_Run::day( 200, '12:00:00' ),
			'event_expire_datetime'   => MEP_UL_Run::day( 200, '12:00:00' ),
			'event_upcoming_datetime' => MEP_UL_Run::day( -400, '10:00' ),
		) );
		$mep_ul_posts = array( $mep_ul_a, $mep_ul_b, $mep_ul_c, $mep_ul_d, $mep_ul_e, $mep_ul_f );
		$mep_ul_before = array_combine( $mep_ul_posts, array_map( array( 'MEP_UL_Run', 'stored' ), $mep_ul_posts ) );
		$mep_ul_now    = current_time( 'mysql' );

		WP_CLI::log( '"Event End Time" mode: the lists select on the expiry, nothing is refreshed' );
		$mep_ul_set_mode( 'event_end_datetime' );
		MPWEM_Query::event_list_query( -1, 'upcoming', 'ASC' );
		MPWEM_Query::event_query( -1, 'ASC' );
		MEP_UL_Run::check( 'saved values untouched in End Time mode', MEP_UL_Run::stored( $mep_ul_a ) === '2025-06-11 19:30' && MEP_UL_Run::stored( $mep_ul_b ) === $mep_ul_before[ $mep_ul_b ] );

		WP_CLI::log( 'Labels: calculate without saving' );
		$mep_ul_set_mode( 'event_start_datetime' );
		$mep_ul_can_calc = method_exists( 'MPWEM_Functions', 'calculate_upcoming_date_time' );
		MEP_UL_Run::check( 'MPWEM_Functions::calculate_upcoming_date_time() exists', $mep_ul_can_calc );
		if ( $mep_ul_can_calc ) {
			update_post_meta( $mep_ul_c, 'event_upcoming_datetime', 'sentinel' );
			$mep_ul_label = MPWEM_Functions::calculate_upcoming_date_time( $mep_ul_c, MPWEM_Functions::get_all_dates( $mep_ul_c ) );
			MEP_UL_Run::check( 'calculating a label does not save it', MEP_UL_Run::stored( $mep_ul_c ) === 'sentinel', MEP_UL_Run::stored( $mep_ul_c ) );
			MEP_UL_Run::check( 'label from get_all_dates() is still the first date', MEP_UL_Run::day( 3, '09:30' ) === $mep_ul_label, $mep_ul_label );
			MEP_UL_Run::check( 'a past first date is still returned for labels', '2025-06-11 19:30' === MPWEM_Functions::calculate_upcoming_date_time( $mep_ul_a, MPWEM_Functions::get_all_dates( $mep_ul_a ) ) );
			update_post_meta( $mep_ul_c, 'event_upcoming_datetime', $mep_ul_before[ $mep_ul_c ] );
		}

		WP_CLI::log( '"Event Start Time" mode: the lists refresh lapsed recurring events first' );
		$mep_ul_terms = MPWEM_Query::get_non_expired_term_ids( 'mep_cat' );
		MEP_UL_Run::check( 'category filter lists the category of event A', in_array( $mep_ul_term, array_map( 'intval', $mep_ul_terms ), true ) );
		MEP_UL_Run::check( 'A: first date replaced by the next date', MEP_UL_Run::stored( $mep_ul_a ) === MEP_UL_Run::day( 8, '19:30' ), MEP_UL_Run::stored( $mep_ul_a ) );
		MEP_UL_Run::check( 'B: passed occurrence replaced by the next one', MEP_UL_Run::stored( $mep_ul_b ) === MEP_UL_Run::day( 7, '10:00' ), MEP_UL_Run::stored( $mep_ul_b ) );
		MEP_UL_Run::check( 'F: everyday event moved to a date still to come', MEP_UL_Run::stored( $mep_ul_f ) > $mep_ul_now, MEP_UL_Run::stored( $mep_ul_f ) );
		MEP_UL_Run::check( 'C, D, E: saved values untouched', MEP_UL_Run::stored( $mep_ul_c ) === $mep_ul_before[ $mep_ul_c ] && MEP_UL_Run::stored( $mep_ul_d ) === $mep_ul_before[ $mep_ul_d ] && MEP_UL_Run::stored( $mep_ul_e ) === $mep_ul_before[ $mep_ul_e ] );

		$mep_ul_list = MEP_UL_Run::ids( MPWEM_Query::event_list_query( -1, 'upcoming', 'ASC' ) );
		MEP_UL_Run::check( '[event-list] shows A, B, C and F', ! array_diff( array( $mep_ul_a, $mep_ul_b, $mep_ul_c, $mep_ul_f ), $mep_ul_list ) );
		MEP_UL_Run::check( '[event-list] leaves out D and the started single event E', ! array_intersect( array( $mep_ul_d, $mep_ul_e ), $mep_ul_list ) );
		$mep_ul_pos = array_flip( $mep_ul_list );
		MEP_UL_Run::check( '[event-list] orders C, B, A by their next dates', isset( $mep_ul_pos[ $mep_ul_a ], $mep_ul_pos[ $mep_ul_b ], $mep_ul_pos[ $mep_ul_c ] ) && $mep_ul_pos[ $mep_ul_c ] < $mep_ul_pos[ $mep_ul_b ] && $mep_ul_pos[ $mep_ul_b ] < $mep_ul_pos[ $mep_ul_a ] );
		$mep_ul_expired = MEP_UL_Run::ids( MPWEM_Query::event_list_query( -1, 'expired', 'ASC' ) );
		MEP_UL_Run::check( 'expired list no longer holds A, B or F', ! array_intersect( array( $mep_ul_a, $mep_ul_b, $mep_ul_f ), $mep_ul_expired ) );
		MEP_UL_Run::check( 'expired list still holds D', in_array( $mep_ul_d, $mep_ul_expired, true ) );
		$mep_ul_archive = MEP_UL_Run::ids( MPWEM_Query::event_query( -1, 'ASC' ) );
		MEP_UL_Run::check( 'event_query() (archive, [event-list-*] filters) shows A, B, C and F', ! array_diff( array( $mep_ul_a, $mep_ul_b, $mep_ul_c, $mep_ul_f ), $mep_ul_archive ) );

		WP_CLI::log( 'get_upcoming_date_time() still saves what it returns' );
		$mep_ul_g = MEP_UL_Run::particular( 'UL G saver', MEP_UL_Run::day( 5, '18:00' ), array( MEP_UL_Run::day( 12, '18:00' ) ) );
		$mep_ul_posts[] = $mep_ul_g;
		delete_post_meta( $mep_ul_g, 'event_upcoming_datetime' );
		$mep_ul_saved = MPWEM_Functions::get_upcoming_date_time( $mep_ul_g );
		MEP_UL_Run::check( 'returns and saves the next date', MEP_UL_Run::day( 5, '18:00' ) === $mep_ul_saved && MEP_UL_Run::stored( $mep_ul_g ) === $mep_ul_saved, $mep_ul_saved . ' / ' . MEP_UL_Run::stored( $mep_ul_g ) );

		$mep_ul_pickers = array(
			'PRO Attendee List picker' => array( 'MPWEM_Passenger_List', 'select_post_id_for_attendee_filter' ),
			'PRO Backend Order picker' => array( 'MPWEM_Backend_Order', 'select_post_id_for_backend_order' ),
		);
		foreach ( $mep_ul_pickers as $mep_ul_label_name => $mep_ul_picker ) {
			if ( ! class_exists( $mep_ul_picker[0] ) ) {
				WP_CLI::log( "  SKIP  $mep_ul_label_name (PRO not active)" );
				continue;
			}
			// A fresh event per picker: get_upcoming_date_time() saves only once per event
			// per request, so an event another picker already touched would prove nothing.
			$mep_ul_h       = MEP_UL_Run::particular( 'UL H picker', '2025-03-04 10:00', array( MEP_UL_Run::day( 9, '10:00' ) ) );
			$mep_ul_posts[] = $mep_ul_h;
			update_post_meta( $mep_ul_h, 'event_upcoming_datetime', MEP_UL_Run::day( 9, '10:00' ) );
			$mep_ul_html = (string) MEP_UL_Run::render_private( $mep_ul_picker[0], $mep_ul_picker[1] );
			MEP_UL_Run::check( "$mep_ul_label_name leaves the saved date alone", MEP_UL_Run::stored( $mep_ul_h ) === MEP_UL_Run::day( 9, '10:00' ), MEP_UL_Run::stored( $mep_ul_h ) );
			MEP_UL_Run::check( "$mep_ul_label_name still labels the event with its first date", false !== strpos( $mep_ul_html, date_i18n( get_option( 'date_format' ), strtotime( '2025-03-04 10:00' ) ) ) );
		}
	} finally {
		foreach ( $mep_ul_posts as $mep_ul_id ) {
			wp_delete_post( $mep_ul_id, true );
		}
		if ( $mep_ul_term ) {
			wp_delete_term( $mep_ul_term, 'mep_cat' );
		}
		if ( $mep_ul_setting_had ) {
			update_option( 'general_setting_sec', $mep_ul_setting );
		} else {
			delete_option( 'general_setting_sec' );
		}
	}

	$mep_ul_failed = array_keys( array_filter( MEP_UL_Run::$results, function ( $pass ) {
		return ! $pass;
	} ) );
	if ( $mep_ul_failed ) {
		WP_CLI::warning( count( $mep_ul_failed ) . ' of ' . count( MEP_UL_Run::$results ) . ' checks failed.' );
		exit( 1 );
	}
	WP_CLI::success( 'All ' . count( MEP_UL_Run::$results ) . ' checks passed.' );
