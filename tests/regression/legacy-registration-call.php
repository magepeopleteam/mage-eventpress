<?php
	/**
	 * Regression tests: the booking section must render however a template calls
	 * mpwem_registration.
	 *
	 * Up to 5.0.1 the single event templates called
	 *     do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date );
	 * Since the template refactor the hook is ( $event_id, $event_infos ). Template copies
	 * in a theme's mage-event/ folder kept the old call, so the list of dates arrived as
	 * $event_infos, registration.php found no 'all_date' in it and printed an empty box:
	 * the event page showed its title, image and description but no dates, tickets or
	 * booking button, while [event-add-cart-section] (which passes only the ID) worked.
	 *
	 * MPWEM_Hooks::registration() now uses the second argument only when it is this
	 * event's own info array, and otherwise lets registration.php rebuild it.
	 *
	 * Not loaded by the plugin. Run it explicitly, on a development or staging site:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/legacy-registration-call.php
	 *
	 * A site whose environment type is "production" is refused unless `allow-production` is
	 * passed as an argument. The run creates its own events, deletes them (and their linked
	 * WooCommerce products) afterwards. Exits with status 1 when any case fails.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! class_exists( 'MPWEM_Functions' ) || ! has_action( 'mpwem_registration' ) ) {
		WP_CLI::error( 'Event Booking Manager for WooCommerce must be active.' );
	}
	$mep_lr_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_lr_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. Run these tests on a test site, or pass allow-production to override.' );
	}

	$mep_lr_events = array();
	$mep_lr_pass   = 0;
	$mep_lr_fail   = 0;
	$mep_lr_check  = static function ( $label, $ok, $detail = '' ) use ( &$mep_lr_pass, &$mep_lr_fail ) {
		if ( $ok ) {
			$mep_lr_pass++;
			WP_CLI::log( 'PASS  ' . $label );
		} else {
			$mep_lr_fail++;
			WP_CLI::log( 'FAIL  ' . $label . ( '' !== $detail ? ' - ' . $detail : '' ) );
		}
	};

	/** A published, single-date, ticket-selling event in the future with one ticket type. */
	$mep_lr_create = static function ( $title, $ticket ) use ( &$mep_lr_events ) {
		$start = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' +20 days' ) );
		$id    = wp_insert_post(
			array(
				'post_type'    => 'mep_events',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => 'Legacy registration call regression fixture.',
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( 'Could not create the test event: ' . $id->get_error_message() );
		}
		$mep_lr_events[] = (int) $id;
		$meta            = array(
			'mep_reg_status'          => 'on',
			'mep_event_no_date'       => 'no',
			'mep_enable_recurring'    => 'no',
			'mep_event_template'      => 'default-theme.php',
			'event_start_date'        => $start,
			'event_start_time'        => '10:00',
			'event_end_date'          => $start,
			'event_end_time'          => '18:00',
			'event_start_datetime'    => $start . ' 10:00:00',
			'event_end_datetime'      => $start . ' 18:00:00',
			'event_expire_datetime'   => $start . ' 18:00:00',
			'event_upcoming_datetime' => $start . ' 10:00:00',
			'mep_event_ticket_type'   => array(
				array(
					'option_name_t'          => $ticket,
					'option_details_t'       => '',
					'option_price_t'         => '0',
					'option_qty_t'           => '50',
					'option_qty_t_unlimited' => 'no',
					'option_rsv_t'           => '0',
					'option_default_qty_t'   => '0',
					'option_qty_t_type'      => 'inputbox',
					'option_ticket_enable'   => 'yes',
				),
			),
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
		clean_post_cache( $id );
		return (int) $id;
	};

	/** Output of the hook for the given arguments. */
	$mep_lr_render = static function ( array $hook_args ) {
		ob_start();
		do_action_ref_array( 'mpwem_registration', $hook_args );
		return (string) ob_get_clean();
	};
	$mep_lr_has_form = static function ( $html, $ticket ) {
		return false !== strpos( $html, 'id="mpwem_registration"' )
			&& false !== strpos( $html, $ticket )
			&& false !== strpos( $html, 'mpwem_registration_area' );
	};

	try {
		$event  = $mep_lr_create( 'Legacy registration call - main', 'LegacyTicketAlpha' );
		$other  = $mep_lr_create( 'Legacy registration call - other', 'LegacyTicketBeta' );
		$infos  = MPWEM_Functions::get_all_info( $event );
		$oinfos = MPWEM_Functions::get_all_info( $other );

		$mep_lr_check( 'fixture event has an upcoming date', ! empty( $infos['all_date'] ), wp_json_encode( $infos['all_date'] ?? null ) );

		$by_id = $mep_lr_render( array( $event ) );
		$mep_lr_check( 'event ID only (the shortcode) renders the booking form', $mep_lr_has_form( $by_id, 'LegacyTicketAlpha' ), strlen( $by_id ) . ' bytes' );

		$current = $mep_lr_render( array( $event, $infos ) );
		$mep_lr_check( '5.x templates ( ID, event infos ) render the booking form', $mep_lr_has_form( $current, 'LegacyTicketAlpha' ), strlen( $current ) . ' bytes' );
		$mep_lr_check( '5.x call is unchanged: same output as the ID-only call', $current === $by_id );

		$legacy = $mep_lr_render( array( $event, $infos['all_date'], $infos['all_time'], $infos['upcoming_date'] ) );
		$mep_lr_check( 'pre-5.0.2 template call ( ID, dates, times, date ) renders the booking form', $mep_lr_has_form( $legacy, 'LegacyTicketAlpha' ), strlen( $legacy ) . ' bytes' );
		$mep_lr_check( 'pre-5.0.2 call gives the same output as the ID-only call', $legacy === $by_id );

		$legacy_short = $mep_lr_render( array( $event, $infos['all_date'] ) );
		$mep_lr_check( 'pre-5.0.2 call with only the dates renders the booking form', $mep_lr_has_form( $legacy_short, 'LegacyTicketAlpha' ), strlen( $legacy_short ) . ' bytes' );

		$wrong = $mep_lr_render( array( $event, $oinfos ) );
		$mep_lr_check( 'another event\'s infos are not used for this event', $mep_lr_has_form( $wrong, 'LegacyTicketAlpha' ) && false === strpos( $wrong, 'LegacyTicketBeta' ) );

		$junk = $mep_lr_render( array( $event, 'not-an-array' ) );
		$mep_lr_check( 'a non-array second argument renders the booking form', $mep_lr_has_form( $junk, 'LegacyTicketAlpha' ) );

		$empty = $mep_lr_render( array( $event, array() ) );
		$mep_lr_check( 'an empty second argument renders the booking form', $mep_lr_has_form( $empty, 'LegacyTicketAlpha' ) );

		// The guards in front of the form still apply to the legacy call.
		update_post_meta( $event, 'mep_event_cancelled', 'yes' );
		clean_post_cache( $event );
		$cancelled = $mep_lr_render( array( $event, $infos['all_date'], $infos['all_time'], $infos['upcoming_date'] ) );
		$mep_lr_check( 'a cancelled event still shows no booking form on the legacy call', false === strpos( $cancelled, 'id="mpwem_registration"' ), strlen( $cancelled ) . ' bytes' );
		delete_post_meta( $event, 'mep_event_cancelled' );

		wp_update_post( array( 'ID' => $event, 'post_password' => 'secret-lr' ) );
		clean_post_cache( $event );
		$locked = $mep_lr_render( array( $event, $infos['all_date'], $infos['all_time'], $infos['upcoming_date'] ) );
		$mep_lr_check( 'a password protected event still asks for the password on the legacy call', false === strpos( $locked, 'id="mpwem_registration"' ) && false !== strpos( $locked, 'post_password' ) );
		wp_update_post( array( 'ID' => $event, 'post_password' => '' ) );

		update_post_meta( $event, 'mep_reg_status', 'off' );
		clean_post_cache( $event );
		$listing = $mep_lr_render( array( $event, $infos['all_date'], $infos['all_time'], $infos['upcoming_date'] ) );
		$mep_lr_check( 'a Listing-Only event still shows no booking form on the legacy call', false === strpos( $listing, 'id="mpwem_registration"' ) );
	} finally {
		foreach ( $mep_lr_events as $mep_lr_id ) {
			$mep_lr_product = (int) get_post_meta( $mep_lr_id, 'link_wc_product', true );
			if ( $mep_lr_product > 0 && 'product' === get_post_type( $mep_lr_product ) ) {
				wp_delete_post( $mep_lr_product, true );
			}
			wp_delete_post( $mep_lr_id, true );
		}
	}

	WP_CLI::log( sprintf( '%d checks, %d passed, %d failed.', $mep_lr_pass + $mep_lr_fail, $mep_lr_pass, $mep_lr_fail ) );
	if ( $mep_lr_fail > 0 ) {
		exit( 1 );
	}
