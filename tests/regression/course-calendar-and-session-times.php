<?php
	/**
	 * Regression tests: "Add Calendar" saves the right session, and the session list shows end times.
	 *
	 * A course sold as one Single Event with "Add More Date" rows (Fri 5-8 pm, Sat 9-5,
	 * Sun 9-12) has one ticket and several sessions. Before this fix:
	 *  - with Event Expiry Time = end, every "Add Calendar" link (Google, Yahoo, Outlook,
	 *    Apple .ics) was built from the event's expiry, i.e. the last session's end, so the
	 *    calendar entry sat at Sunday noon (zero length on Google);
	 *  - in both modes the Google/Yahoo/Outlook end was the first row's end even when another
	 *    date was picked (a particular event's 2nd date ended before it started), a repeated
	 *    event got a zero-length entry, and the event list's "Add To Calendar" started at the
	 *    current day's midnight because its upcoming date was never set;
	 *  - the "Event Schedule Details" list printed only each session's start time and ignored
	 *    the per-event "Display End Datetime" switch;
	 *  - "Add Calendar" saved only one session of such a course: every link (and the .ics)
	 *    held the first date. Now each session gets its own Google/Yahoo/Outlook links and
	 *    the Apple .ics holds every session. The links also sent the title as
	 *    "http://<title>" and the raw post HTML (block comments included) as the description.
	 *
	 * Not loaded by the plugin. Run it explicitly, on a development or staging site, once per
	 * Event Expiry Time mode (the setting is cached for the request, so one mode per process):
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/course-calendar-and-session-times.php
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/course-calendar-and-session-times.php expire-end
	 *
	 * A site whose environment type is "production" is refused unless `allow-production` is
	 * passed as an argument. The run creates its own events, deletes them afterwards and puts
	 * back the one setting it changes (Event Expiry Time). Exits with status 1 when any case fails.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! class_exists( 'MPWEM_Functions' ) || ! function_exists( 'mep_get_event_ics_url' ) ) {
		WP_CLI::error( 'Event Booking Manager for WooCommerce must be active.' );
	}
	$mep_cc_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_cc_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. Run these tests on a test site, or pass allow-production to override.' );
	}

	if ( ! class_exists( 'MEP_CC_Run' ) ) {
		final class MEP_CC_Run {
			/** @var array<string, bool> */
			public static $results = array();
			public static function check( $label, $pass, $detail = '' ) {
				self::$results[ $label ] = (bool) $pass;
				WP_CLI::log( sprintf( '  %s  %s%s', $pass ? 'PASS' : 'FAIL', $label, ( ! $pass && '' !== $detail ) ? ' - ' . $detail : '' ) );
			}
			public static function render( callable $callback ) {
				ob_start();
				$callback();
				return (string) ob_get_clean();
			}
			/**
			 * Creates a published event. $sessions: list of [ start 'Y-m-d H:i', end 'Y-m-d H:i' or 'Y-m-d' ];
			 * the first is the main date, the rest are "Add More Date" rows.
			 */
			public static function event( $title, $type, array $sessions, array $extra = array() ) {
				$id    = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => '<!-- wp:paragraph --><p>Test event.</p><!-- /wp:paragraph -->' ) );
				$split = static function ( $value ) {
					$parts = explode( ' ', $value );
					return array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
				};
				$first = array_shift( $sessions );
				list( $start_date, $start_time ) = $split( $first[0] );
				list( $end_date, $end_time )     = $split( $first[1] );
				$more = array();
				foreach ( $sessions as $session ) {
					list( $more_start_date, $more_start_time ) = $split( $session[0] );
					list( $more_end_date, $more_end_time )     = $split( $session[1] );
					$more[] = array(
						'event_more_start_date' => $more_start_date,
						'event_more_start_time' => $more_start_time,
						'event_more_end_date'   => $more_end_date,
						'event_more_end_time'   => $more_end_time,
					);
				}
				$last = $sessions ? end( $sessions ) : $first;
				$meta = array_merge(
					array(
						'mep_event_ticket_type'   => array( array( 'option_name_t' => 'Full course', 'option_price_t' => '200', 'option_qty_t' => '50', 'option_rsv_t' => '0', 'option_default_qty_t' => '0', 'option_qty_t_type' => 'inputbox' ) ),
						'mep_enable_recurring'    => $type,
						'event_start_date'        => $start_date,
						'event_start_time'        => $start_time,
						'event_end_date'          => $end_date,
						'event_end_time'          => $end_time,
						'event_start_datetime'    => $first[0] . ':00',
						'event_end_datetime'      => $end_time ? $first[1] . ':00' : $end_date,
						'event_upcoming_datetime' => $first[0] . ':00',
						'event_expire_datetime'   => false !== strpos( $last[1], ':' ) ? $last[1] . ':00' : $last[1],
						'mep_event_more_date'     => $more,
					),
					$extra
				);
				foreach ( $meta as $key => $value ) {
					update_post_meta( $id, $key, $value );
				}
				return (int) $id;
			}
			/** The upcoming date the single-event page would pass ($event_infos['upcoming_date']). */
			public static function upcoming( $event_id ) {
				$infos = MPWEM_Functions::get_all_info( $event_id );
				return is_array( $infos ) && isset( $infos['upcoming_date'] ) ? (string) $infos['upcoming_date'] : '';
			}
			/** Decoded href of every link in $html whose class or URL matches $needle. */
			public static function hrefs( $html, $needle ) {
				preg_match_all( '#<a [^>]*href="([^"]*)"#', $html, $m );
				$urls = array_map( function ( $url ) {
					return html_entity_decode( $url, ENT_QUOTES );
				}, $m[1] );
				return array_values( array_filter( $urls, function ( $url ) use ( $needle ) {
					return false !== strpos( $url, $needle );
				} ) );
			}
			public static function query( $url ) {
				parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
				return $query;
			}
			/** 'dates' of the first Google link in $html ("start/end" in UTC), or '' when there is none. */
			public static function google_dates( $html ) {
				$links = self::hrefs( $html, 'calendar.google.com' );
				return $links ? (string) ( self::query( $links[0] )['dates'] ?? '' ) : '';
			}
			/** Calendar-link time: UTC, basic format (Google, Yahoo, .ics). */
			public static function cal( $date_time ) {
				return get_gmt_from_date( $date_time . ':00', 'Ymd\THis\Z' );
			}
			/** Outlook.com time: UTC, ISO 8601. */
			public static function iso( $date_time ) {
				return get_gmt_from_date( $date_time . ':00', 'Y-m-d\TH:i:s\Z' );
			}
			/** Text of every row of the "Event Schedule Details" list. */
			public static function date_list( $event_id ) {
				$html = self::render( function () use ( $event_id ) {
					do_action( 'mpwem_date_list', $event_id, MPWEM_Functions::get_all_info( $event_id ) );
				} );
				preg_match_all( '#<span class="mpwem-date-list__text">(.*?)</span>#s', $html, $m );
				return array_map( function ( $text ) {
					return trim( html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES ) );
				}, $m[1] );
			}
			public static function label( $date_time, $format = 'full', $event_id = 0 ) {
				return MPWEM_Global_Function::date_format( $date_time, $format, $event_id );
			}
		}
	}

	$mep_cc_mode        = in_array( 'expire-end', $mep_cc_args, true ) ? 'event_end_datetime' : 'event_start_datetime';
	$mep_cc_setting_had = false !== get_option( 'general_setting_sec', false );
	$mep_cc_setting     = get_option( 'general_setting_sec', array() );
	$mep_cc_settings    = is_array( $mep_cc_setting ) ? $mep_cc_setting : array();
	$mep_cc_settings['mep_event_expire_on_datetimes'] = $mep_cc_mode;
	update_option( 'general_setting_sec', $mep_cc_settings );
	$mep_cc_posts = array();

	try {
		$mep_cc_effective = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_expire_on_datetimes', 'event_start_datetime' );
		if ( $mep_cc_effective !== $mep_cc_mode ) {
			throw new RuntimeException( "Event Expiry Time was already read in this request as $mep_cc_effective; run this file in a fresh process." );
		}
		WP_CLI::log( "Event Expiry Time: $mep_cc_mode" );

		$mep_cc_weekend = MEP_CC_Run::event( 'CC weekend course', 'no', array(
			array( '2030-01-18 17:00', '2030-01-18 20:00' ),
			array( '2030-01-19 09:00', '2030-01-19 17:00' ),
			array( '2030-01-20 09:00', '2030-01-20 12:00' ),
		) );
		$mep_cc_particular = MEP_CC_Run::event( 'CC particular', 'yes', array(
			array( '2030-02-01 10:00', '2030-02-01 12:00' ),
			array( '2030-02-08 14:00', '2030-02-08 17:30' ),
		) );
		$mep_cc_multiday = MEP_CC_Run::event( 'CC multi-day', 'no', array( array( '2030-01-27 18:00', '2030-01-29 20:00' ) ) );
		$mep_cc_endoff   = MEP_CC_Run::event( 'CC end hidden', 'no', array(
			array( '2030-01-18 17:00', '2030-01-18 20:00' ),
			array( '2030-01-19 09:00', '2030-01-19 17:00' ),
		), array( 'mep_show_end_datetime' => 'no' ) );
		$mep_cc_noend = MEP_CC_Run::event( 'CC no end time', 'yes', array(
			array( '2030-02-01 10:00', '2030-02-01' ),
			array( '2030-02-08 14:00', '2030-02-08' ),
		) );
		$mep_cc_from = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' +1 day' ) );
		$mep_cc_to   = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' +30 days' ) );
		$mep_cc_repeated = MEP_CC_Run::event( 'CC repeated', 'everyday', array( array( "$mep_cc_from 09:00", "$mep_cc_to 11:00" ) ), array(
			'event_start_date_everyday' => $mep_cc_from,
			'event_end_date_everyday'   => $mep_cc_to,
			'mep_repeated_periods'      => 1,
			'mep_disable_ticket_time'   => 'no',
			'event_expire_datetime'     => "$mep_cc_to 11:00:00",
		) );
		$mep_cc_posts = array( $mep_cc_weekend, $mep_cc_particular, $mep_cc_multiday, $mep_cc_endoff, $mep_cc_noend, $mep_cc_repeated );

		WP_CLI::log( 'Add Calendar: the occurrence saved' );
		$mep_cc_occ = MPWEM_Functions::get_calendar_occurrence( $mep_cc_weekend, array(), MEP_CC_Run::upcoming( $mep_cc_weekend ) );
		MEP_CC_Run::check( 'Course: first session, its own end', '2030-01-18 17:00' === $mep_cc_occ['start'] && '2030-01-18 20:00' === $mep_cc_occ['end'], wp_json_encode( $mep_cc_occ ) );
		$mep_cc_occ = MPWEM_Functions::get_calendar_occurrence( $mep_cc_weekend, array(), '2030-01-20 12:00:00' );
		MEP_CC_Run::check( 'Course: the expiry (last end) is never used as the start', '2030-01-18 17:00' === $mep_cc_occ['start'], wp_json_encode( $mep_cc_occ ) );
		$mep_cc_occ = MPWEM_Functions::get_calendar_occurrence( $mep_cc_weekend );
		MEP_CC_Run::check( 'Course without a date (Elementor widget): first session', '2030-01-18 17:00' === $mep_cc_occ['start'] && '2030-01-18 20:00' === $mep_cc_occ['end'], wp_json_encode( $mep_cc_occ ) );
		$mep_cc_occ = MPWEM_Functions::get_calendar_occurrence( $mep_cc_particular, array(), '2030-02-08 14:00' );
		MEP_CC_Run::check( 'Particular, 2nd date picked: that date and its own end', '2030-02-08 14:00' === $mep_cc_occ['start'] && '2030-02-08 17:30' === $mep_cc_occ['end'], wp_json_encode( $mep_cc_occ ) );
		$mep_cc_occ = MPWEM_Functions::get_calendar_occurrence( $mep_cc_noend, array(), MEP_CC_Run::upcoming( $mep_cc_noend ) );
		MEP_CC_Run::check( 'No end time saved: ends after the start (1 hour)', '2030-02-01 10:00' === $mep_cc_occ['start'] && '2030-02-01 11:00' === $mep_cc_occ['end'], wp_json_encode( $mep_cc_occ ) );
		$mep_cc_occ = MPWEM_Functions::get_calendar_occurrence( $mep_cc_repeated, array(), MEP_CC_Run::upcoming( $mep_cc_repeated ) );
		MEP_CC_Run::check( 'Repeated: not zero length', "$mep_cc_from 09:00" === $mep_cc_occ['start'] && "$mep_cc_from 11:00" === $mep_cc_occ['end'], wp_json_encode( $mep_cc_occ ) );
		$mep_cc_occ = MPWEM_Functions::get_calendar_occurrence( $mep_cc_repeated );
		MEP_CC_Run::check( 'Repeated without a date: next date', "$mep_cc_from 09:00" === $mep_cc_occ['start'], wp_json_encode( $mep_cc_occ ) );
		$mep_cc_all = MPWEM_Functions::get_calendar_occurrences( $mep_cc_weekend, array(), MEP_CC_Run::upcoming( $mep_cc_weekend ) );
		MEP_CC_Run::check( 'Course: every session, each with its own end', array(
			array( 'start' => '2030-01-18 17:00', 'end' => '2030-01-18 20:00' ),
			array( 'start' => '2030-01-19 09:00', 'end' => '2030-01-19 17:00' ),
			array( 'start' => '2030-01-20 09:00', 'end' => '2030-01-20 12:00' ),
		) === $mep_cc_all, wp_json_encode( $mep_cc_all ) );
		$mep_cc_all = MPWEM_Functions::get_calendar_occurrences( $mep_cc_particular, array(), '2030-02-08 14:00' );
		MEP_CC_Run::check( 'Particular: only the picked date (each date is its own booking)', array( array( 'start' => '2030-02-08 14:00', 'end' => '2030-02-08 17:30' ) ) === $mep_cc_all, wp_json_encode( $mep_cc_all ) );
		$mep_cc_all = MPWEM_Functions::get_calendar_occurrences( $mep_cc_repeated, array(), MEP_CC_Run::upcoming( $mep_cc_repeated ) );
		MEP_CC_Run::check( 'Repeated: one occurrence', 1 === count( $mep_cc_all ) && "$mep_cc_from 09:00" === $mep_cc_all[0]['start'], wp_json_encode( $mep_cc_all ) );
		$mep_cc_all = MPWEM_Functions::get_calendar_occurrences( $mep_cc_multiday );
		MEP_CC_Run::check( 'Single Event with one date: one occurrence', array( array( 'start' => '2030-01-27 18:00', 'end' => '2030-01-29 20:00' ) ) === $mep_cc_all, wp_json_encode( $mep_cc_all ) );

		WP_CLI::log( 'Add Calendar: the links' );
		$mep_cc_html = MEP_CC_Run::render( function () use ( $mep_cc_weekend ) {
			do_action( 'mpwem_add_calender', $mep_cc_weekend, MPWEM_Functions::get_dates( $mep_cc_weekend ), MEP_CC_Run::upcoming( $mep_cc_weekend ) );
		} );
		$mep_cc_sessions = array(
			array( '2030-01-18 17:00', '2030-01-18 20:00' ),
			array( '2030-01-19 09:00', '2030-01-19 17:00' ),
			array( '2030-01-20 09:00', '2030-01-20 12:00' ),
		);
		$mep_cc_google  = MEP_CC_Run::hrefs( $mep_cc_html, 'calendar.google.com' );
		$mep_cc_yahoo   = MEP_CC_Run::hrefs( $mep_cc_html, 'calendar.yahoo.com' );
		$mep_cc_outlook = MEP_CC_Run::hrefs( $mep_cc_html, 'outlook.live.com' );
		$mep_cc_got     = array();
		$mep_cc_want    = array();
		foreach ( $mep_cc_sessions as $mep_cc_i => $mep_cc_session ) {
			$mep_cc_want[] = array( MEP_CC_Run::cal( $mep_cc_session[0] ) . '/' . MEP_CC_Run::cal( $mep_cc_session[1] ), MEP_CC_Run::cal( $mep_cc_session[0] ), MEP_CC_Run::cal( $mep_cc_session[1] ), MEP_CC_Run::iso( $mep_cc_session[0] ), MEP_CC_Run::iso( $mep_cc_session[1] ) );
			$mep_cc_g      = isset( $mep_cc_google[ $mep_cc_i ] ) ? MEP_CC_Run::query( $mep_cc_google[ $mep_cc_i ] ) : array();
			$mep_cc_y      = isset( $mep_cc_yahoo[ $mep_cc_i ] ) ? MEP_CC_Run::query( $mep_cc_yahoo[ $mep_cc_i ] ) : array();
			$mep_cc_o      = isset( $mep_cc_outlook[ $mep_cc_i ] ) ? MEP_CC_Run::query( $mep_cc_outlook[ $mep_cc_i ] ) : array();
			$mep_cc_got[]  = array( $mep_cc_g['dates'] ?? '', $mep_cc_y['st'] ?? '', $mep_cc_y['et'] ?? '', $mep_cc_o['startdt'] ?? '', $mep_cc_o['enddt'] ?? '' );
		}
		MEP_CC_Run::check( 'Course: one Google/Yahoo/Outlook link per session', 3 === count( $mep_cc_google ) && 3 === count( $mep_cc_yahoo ) && 3 === count( $mep_cc_outlook ), count( $mep_cc_google ) . '/' . count( $mep_cc_yahoo ) . '/' . count( $mep_cc_outlook ) );
		MEP_CC_Run::check( 'Course: each session link carries that session\'s own start and end (UTC)', $mep_cc_got === $mep_cc_want, wp_json_encode( $mep_cc_got ) );
		preg_match_all( '#<div class="mpwem_calender_session__date">(.*?)</div>#s', $mep_cc_html, $mep_cc_m );
		$mep_cc_labels = array_map( function ( $text ) {
			return trim( html_entity_decode( $text, ENT_QUOTES ) );
		}, $mep_cc_m[1] );
		MEP_CC_Run::check( 'Course: each row is labelled like Event Schedule Details', $mep_cc_labels === MEP_CC_Run::date_list( $mep_cc_weekend ), wp_json_encode( $mep_cc_labels ) );
		$mep_cc_all_url = mep_get_event_ics_url( $mep_cc_weekend, '2030-01-18 17:00', false, true );
		MEP_CC_Run::check( 'Course: Apple / iCal button = all-sessions .ics', array( $mep_cc_all_url ) === MEP_CC_Run::hrefs( $mep_cc_html, 'mep_ics=' ), wp_json_encode( MEP_CC_Run::hrefs( $mep_cc_html, 'mep_ics=' ) ) );
		$mep_cc_q = MEP_CC_Run::query( $mep_cc_all_url );
		MEP_CC_Run::check( 'All-sessions link is signed with its flag', isset( $mep_cc_q['all'], $mep_cc_q['key'] ) && hash_equals( mep_event_ics_signature( $mep_cc_weekend, '203001181700|all', false ), $mep_cc_q['key'] ) );
		$mep_cc_q1 = MEP_CC_Run::query( mep_get_event_ics_url( $mep_cc_weekend, '2030-01-18 17:00' ) );
		MEP_CC_Run::check( 'One-session links keep their old signature (links already emailed still work)', ! isset( $mep_cc_q1['all'] ) && hash_equals( mep_event_ics_signature( $mep_cc_weekend, '203001181700', false ), $mep_cc_q1['key'] ) && $mep_cc_q1['key'] !== $mep_cc_q['key'] );
		$mep_cc_ics = mep_get_event_ics_content( $mep_cc_weekend, wp_list_pluck( MPWEM_Functions::get_calendar_occurrences( $mep_cc_weekend ), 'start' ) );
		preg_match_all( '#^DTSTART:(\S+)\r$#m', $mep_cc_ics, $mep_cc_ds );
		preg_match_all( '#^DTEND:(\S+)\r$#m', $mep_cc_ics, $mep_cc_de );
		preg_match_all( '#^UID:(\S+)\r$#m', $mep_cc_ics, $mep_cc_uid );
		$mep_cc_want = array( array(), array() );
		foreach ( $mep_cc_sessions as $mep_cc_session ) {
			$mep_cc_want[0][] = MEP_CC_Run::cal( $mep_cc_session[0] );
			$mep_cc_want[1][] = MEP_CC_Run::cal( $mep_cc_session[1] );
		}
		MEP_CC_Run::check( 'Course .ics: one VEVENT per session with its own start and end', 3 === substr_count( $mep_cc_ics, "BEGIN:VEVENT\r\n" ) && array( $mep_cc_ds[1], $mep_cc_de[1] ) === $mep_cc_want, wp_json_encode( array( $mep_cc_ds[1], $mep_cc_de[1] ) ) );
		MEP_CC_Run::check( 'Course .ics: distinct UIDs, the first equal to the one-session file\'s', 3 === count( array_unique( $mep_cc_uid[1] ) ) && 0 === strpos( $mep_cc_uid[1][0] ?? '', 'mep-' . $mep_cc_weekend . '-203001181700@' ) );
		$mep_cc_g = $mep_cc_google ? MEP_CC_Run::query( $mep_cc_google[0] ) : array();
		MEP_CC_Run::check( 'Links send the plain title (no "http://") and plain-text details', 'CC weekend course' === ( $mep_cc_g['text'] ?? '' ) && false === strpos( $mep_cc_g['details'] ?? '<', '<' ) && false !== strpos( $mep_cc_g['details'] ?? '', 'Test event.' ), wp_json_encode( array( $mep_cc_g['text'] ?? '', $mep_cc_g['details'] ?? '' ) ) );
		$mep_cc_o = $mep_cc_outlook ? MEP_CC_Run::query( $mep_cc_outlook[0] ) : array();
		MEP_CC_Run::check( 'Outlook subject is the plain title', 'CC weekend course' === ( $mep_cc_o['subject'] ?? '' ) && false === strpos( $mep_cc_o['body'] ?? '', 'http://CC' ), wp_json_encode( array( $mep_cc_o['subject'] ?? '', $mep_cc_o['body'] ?? '' ) ) );
		$mep_cc_html = MEP_CC_Run::render( function () use ( $mep_cc_particular ) {
			do_action( 'mpwem_add_calender', $mep_cc_particular, MPWEM_Functions::get_dates( $mep_cc_particular ), '2030-02-08 14:00' );
		} );
		MEP_CC_Run::check( 'Event page, particular 2nd date: Google ends after it starts', MEP_CC_Run::google_dates( $mep_cc_html ) === MEP_CC_Run::cal( '2030-02-08 14:00' ) . '/' . MEP_CC_Run::cal( '2030-02-08 17:30' ), MEP_CC_Run::google_dates( $mep_cc_html ) );
		MEP_CC_Run::check( 'Particular event: one set of four buttons, Apple = that date only', false === strpos( $mep_cc_html, 'mpwem_calender_sessions' ) && 1 === count( MEP_CC_Run::hrefs( $mep_cc_html, 'calendar.google.com' ) ) && array( mep_get_event_ics_url( $mep_cc_particular, '2030-02-08 14:00' ) ) === MEP_CC_Run::hrefs( $mep_cc_html, 'mep_ics=' ) );
		$mep_cc_html = MEP_CC_Run::render( function () use ( $mep_cc_multiday ) {
			do_action( 'mpwem_add_calender', $mep_cc_multiday, MPWEM_Functions::get_dates( $mep_cc_multiday ), MEP_CC_Run::upcoming( $mep_cc_multiday ) );
		} );
		MEP_CC_Run::check( 'Single Event with one date: unchanged four buttons', false === strpos( $mep_cc_html, 'mpwem_calender_sessions' ) && MEP_CC_Run::google_dates( $mep_cc_html ) === MEP_CC_Run::cal( '2030-01-27 18:00' ) . '/' . MEP_CC_Run::cal( '2030-01-29 20:00' ), MEP_CC_Run::google_dates( $mep_cc_html ) );
		$mep_cc_html = MEP_CC_Run::render( function () use ( $mep_cc_multiday ) {
			do_action( 'mpwem_list_more_date_button', MPWEM_Functions::get_all_info( $mep_cc_multiday ) );
		} );
		$mep_cc_dates = MEP_CC_Run::google_dates( $mep_cc_html );
		if ( '' === $mep_cc_dates ) {
			WP_CLI::log( '  SKIP  Event list Add To Calendar (the card shows a status instead on this site)' );
		} else {
			MEP_CC_Run::check( 'Event list Add To Calendar = the event, not today', $mep_cc_dates === MEP_CC_Run::cal( '2030-01-27 18:00' ) . '/' . MEP_CC_Run::cal( '2030-01-29 20:00' ), $mep_cc_dates );
		}

		WP_CLI::log( 'Event Schedule Details: end times' );
		$mep_cc_rows = MEP_CC_Run::date_list( $mep_cc_weekend );
		$mep_cc_want = array(
			MEP_CC_Run::label( '2030-01-18 17:00', 'full', $mep_cc_weekend ) . ' - ' . MEP_CC_Run::label( '2030-01-18 20:00', 'time', $mep_cc_weekend ),
			MEP_CC_Run::label( '2030-01-19 09:00', 'full', $mep_cc_weekend ) . ' - ' . MEP_CC_Run::label( '2030-01-19 17:00', 'time', $mep_cc_weekend ),
			MEP_CC_Run::label( '2030-01-20 09:00', 'full', $mep_cc_weekend ) . ' - ' . MEP_CC_Run::label( '2030-01-20 12:00', 'time', $mep_cc_weekend ),
		);
		MEP_CC_Run::check( 'Course: every session shows its own start - end', $mep_cc_rows === $mep_cc_want, wp_json_encode( $mep_cc_rows ) );
		$mep_cc_rows = MEP_CC_Run::date_list( $mep_cc_multiday );
		MEP_CC_Run::check( 'Multi-day session shows the end date and time', array( MEP_CC_Run::label( '2030-01-27 18:00', 'full', $mep_cc_multiday ) . ' - ' . MEP_CC_Run::label( '2030-01-29 20:00', 'full', $mep_cc_multiday ) ) === $mep_cc_rows, wp_json_encode( $mep_cc_rows ) );
		$mep_cc_rows = MEP_CC_Run::date_list( $mep_cc_endoff );
		MEP_CC_Run::check( 'Display End Datetime off: start only', array( MEP_CC_Run::label( '2030-01-18 17:00', 'full', $mep_cc_endoff ), MEP_CC_Run::label( '2030-01-19 09:00', 'full', $mep_cc_endoff ) ) === $mep_cc_rows, wp_json_encode( $mep_cc_rows ) );
		$mep_cc_rows = MEP_CC_Run::date_list( $mep_cc_noend );
		MEP_CC_Run::check( 'No end time saved: start only', array( MEP_CC_Run::label( '2030-02-01 10:00', 'full', $mep_cc_noend ), MEP_CC_Run::label( '2030-02-08 14:00', 'full', $mep_cc_noend ) ) === $mep_cc_rows, wp_json_encode( $mep_cc_rows ) );
	} catch ( Throwable $mep_cc_error ) {
		MEP_CC_Run::check( 'Run completed', false, $mep_cc_error->getMessage() );
	} finally {
		foreach ( $mep_cc_posts as $mep_cc_id ) {
			wp_delete_post( $mep_cc_id, true );
		}
		if ( $mep_cc_setting_had ) {
			update_option( 'general_setting_sec', $mep_cc_setting );
		} else {
			delete_option( 'general_setting_sec' );
		}
	}

	$mep_cc_failed = array_keys( array_filter( MEP_CC_Run::$results, function ( $pass ) {
		return ! $pass;
	} ) );
	if ( $mep_cc_failed ) {
		WP_CLI::warning( count( $mep_cc_failed ) . ' of ' . count( MEP_CC_Run::$results ) . ' checks failed.' );
		exit( 1 );
	}
	WP_CLI::success( 'All ' . count( MEP_CC_Run::$results ) . ' checks passed.' );
