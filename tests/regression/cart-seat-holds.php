<?php
	/**
	 * Regression tests: cart seat holds follow the cart line that made them.
	 *
	 * Adding event tickets to the WooCommerce cart holds their seats (mep_temp_attendee)
	 * for the Cart Hold Time. These cases pin the rules around those holds:
	 *
	 * - a hold is made only once WooCommerce has really added the line, so a line
	 *   WooCommerce refuses after the item-data filter leaves no seats held;
	 * - each hold records its session and cart item key;
	 * - removing a line releases exactly its own holds, never another customer's
	 *   identical booking, and an undo holds them again;
	 * - emptying the cart releases that cart's holds only;
	 * - adding the same line again does not double its holds;
	 * - a hold made before this version (no session recorded) is still released by
	 *   the line it matches;
	 * - an expired hold is released without emptying the cart of whoever happens to
	 *   load the page.
	 *
	 * Runs the real WooCommerce cart API (add_to_cart, remove_cart_item,
	 * restore_cart_item, empty_cart), so the plugin's own hooks do the work.
	 *
	 * Not loaded by the plugin. Run it explicitly, on a development or staging site:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/cart-seat-holds.php
	 *
	 * A site whose environment type is "production" is refused unless `allow-production` is
	 * passed as an argument. The run creates its own event and product and deletes them,
	 * together with every hold it made. Exits with status 1 when any case fails.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! function_exists( 'WC' ) || ! class_exists( 'MPWEM_Woocommerce' ) || ! function_exists( 'mep_temp_attendee_create_for_cart' ) ) {
		WP_CLI::error( 'WooCommerce and Event Booking Manager for WooCommerce must both be active.' );
	}
	$mep_sh_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_sh_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. Run these tests on a test site, or pass allow-production to override.' );
	}
	if ( ! MPWEM_Global_Function::use_wc_payment() ) {
		WP_CLI::error( 'WooCommerce payment is switched off in Event Settings, so the WooCommerce booking path these cases cover is not loaded.' );
	}

	$mep_sh_failed = 0;
	$mep_sh_check  = static function ( $label, $pass, $detail = '' ) use ( &$mep_sh_failed ) {
		WP_CLI::log( sprintf( '  %s  %s%s', $pass ? 'PASS' : 'FAIL', $label, ( ! $pass && '' !== $detail ) ? ' - ' . $detail : '' ) );
		if ( ! $pass ) {
			$mep_sh_failed++;
		}
	};
	// Holds for the test event, optionally only those of one session.
	$mep_sh_holds = static function ( $event_id, $session = null ) {
		$query = array( array( 'key' => 'event_id', 'value' => $event_id ) );
		if ( null !== $session ) {
			$query[] = array( 'key' => 'mep_cart_session', 'value' => $session );
		}
		return get_posts(
			array(
				'post_type'      => 'mep_temp_attendee',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => $query,
			)
		);
	};

	$mep_sh_day     = date( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' +9 days' ) );
	$mep_sh_event   = 0;
	$mep_sh_product = 0;
	$mep_sh_simple  = 0;
	try {
		$mep_sh_event = wp_insert_post(
			array(
				'post_type'   => 'mep_events',
				'post_status' => 'publish',
				'post_title'  => 'MEP Seat Hold Test',
				'meta_input'  => array(
					'mep_enable_recurring'  => 'no',
					'mep_reg_status'        => 'on',
					'event_start_date'      => $mep_sh_day,
					'event_start_time'      => '10:00',
					'event_end_date'        => $mep_sh_day,
					'event_end_time'        => '12:00',
					'event_start_datetime'  => "$mep_sh_day 10:00:00",
					'event_end_datetime'    => "$mep_sh_day 12:00:00",
					'event_expire_datetime' => "$mep_sh_day 12:00:00",
					'mep_event_ticket_type' => array(
						array( 'option_name_t' => 'Standard', 'option_price_t' => '10', 'option_qty_t' => '10', 'option_rsv_t' => '0', 'option_ticket_enable' => 'yes' ),
						array( 'option_name_t' => 'VIP', 'option_price_t' => '20', 'option_qty_t' => '5', 'option_rsv_t' => '0', 'option_ticket_enable' => 'yes' ),
					),
				),
			)
		);
		$mep_sh_product = (int) get_post_meta( $mep_sh_event, 'link_wc_product', true );
		if ( ! $mep_sh_product && class_exists( 'MPWEM_Hidden_Product' ) ) {
			$mep_sh_product = (int) MPWEM_Hidden_Product::create_hidden_wc_product( $mep_sh_event, 'MEP Seat Hold Test' );
			update_post_meta( $mep_sh_event, 'link_wc_product', $mep_sh_product );
		}
		if ( ! $mep_sh_product ) {
			throw new Exception( 'The event got no hidden WooCommerce product.' );
		}
		$mep_sh_simple_product = new WC_Product_Simple();
		$mep_sh_simple_product->set_name( 'MEP Seat Hold Test Mug' );
		$mep_sh_simple_product->set_regular_price( '5' );
		$mep_sh_simple_product->set_status( 'publish' );
		$mep_sh_simple = (int) $mep_sh_simple_product->save();
		$mep_sh_booking = array(
			'option_name'          => array( 'Standard', 'VIP' ),
			'option_qty'           => array( 2, 1 ),
			'max_qty'              => array( 10, 5 ),
			'mep_event_start_date' => array( "$mep_sh_day 10:00" ),
		);

		wp_set_current_user( 0 );
		wc_load_cart();
		WC()->cart->empty_cart();
		$mep_sh_session = (string) WC()->session->get_customer_id();

		WP_CLI::log( 'Adding to the cart' );
		$_POST       = $mep_sh_booking;
		$mep_sh_line = WC()->cart->add_to_cart( $mep_sh_product, 1 );
		$_POST       = array();
		$mep_sh_mine = $mep_sh_holds( $mep_sh_event, $mep_sh_session );
		$mep_sh_check( 'the line goes into the cart', (bool) $mep_sh_line );
		$mep_sh_check( 'one hold per ticket row, tied to this session', 2 === count( $mep_sh_mine ), count( $mep_sh_mine ) . ' holds' );
		$mep_sh_keys = array_map(
			static function ( $id ) {
				return get_post_meta( $id, 'mep_cart_item_key', true );
			},
			$mep_sh_mine
		);
		$mep_sh_check( 'each hold records the cart item key', array( $mep_sh_line, $mep_sh_line ) === $mep_sh_keys );
		$mep_sh_check( 'held seats reduce what the event page offers', 8 === (int) MPWEM_Functions::get_available_ticket( $mep_sh_event, 'Standard', "$mep_sh_day 10:00", get_post_meta( $mep_sh_event, 'mep_event_ticket_type', true )[0] ) );

		WP_CLI::log( 'Adding the same line again' );
		do_action( 'woocommerce_add_to_cart', $mep_sh_line, $mep_sh_product, 1, 0, array(), WC()->cart->get_cart_item( $mep_sh_line ) );
		$mep_sh_check( 'its holds are not doubled', 2 === count( $mep_sh_holds( $mep_sh_event, $mep_sh_session ) ) );

		WP_CLI::log( 'Another customer with the identical booking' );
		mep_temp_attendee_create_for_cart( $mep_sh_event, 'VIP', 1, "$mep_sh_day 10:00", 'other-customer-session', $mep_sh_line );

		WP_CLI::log( 'Removing the line' );
		WC()->cart->remove_cart_item( $mep_sh_line );
		$mep_sh_check( 'its own holds are released at once', 0 === count( $mep_sh_holds( $mep_sh_event, $mep_sh_session ) ) );
		$mep_sh_check( "the other customer's identical hold stays", 1 === count( $mep_sh_holds( $mep_sh_event, 'other-customer-session' ) ) );

		WP_CLI::log( 'Undo' );
		WC()->cart->restore_cart_item( $mep_sh_line );
		$mep_sh_check( 'the line is back in the cart', (bool) WC()->cart->get_cart_item( $mep_sh_line ) );
		$mep_sh_check( 'its seats are held again', 2 === count( $mep_sh_holds( $mep_sh_event, $mep_sh_session ) ) );

		WP_CLI::log( 'Emptying the cart' );
		WC()->cart->empty_cart();
		$mep_sh_check( "the cart's holds are released", 0 === count( $mep_sh_holds( $mep_sh_event, $mep_sh_session ) ) );
		$mep_sh_check( "another customer's hold is untouched", 1 === count( $mep_sh_holds( $mep_sh_event, 'other-customer-session' ) ) );

		WP_CLI::log( 'A hold made before this version' );
		mep_temp_attendee_create_for_cart( $mep_sh_event, 'Standard', 2, "$mep_sh_day 10:00" );
		$_POST       = $mep_sh_booking;
		$mep_sh_line = WC()->cart->add_to_cart( $mep_sh_product, 1 );
		$_POST       = array();
		// Drop the new holds so only the old-style one can match the line.
		foreach ( $mep_sh_holds( $mep_sh_event, $mep_sh_session ) as $mep_sh_id ) {
			wp_delete_post( $mep_sh_id, true );
		}
		WC()->cart->remove_cart_item( $mep_sh_line );
		$mep_sh_legacy = get_posts(
			array(
				'post_type'      => 'mep_temp_attendee',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array( 'key' => 'event_id', 'value' => $mep_sh_event ),
					array( 'key' => 'mep_cart_session', 'compare' => 'NOT EXISTS' ),
				),
			)
		);
		$mep_sh_check( 'the old-style hold the line matches is released', 0 === count( $mep_sh_legacy ), count( $mep_sh_legacy ) . ' left' );
		WC()->cart->empty_cart();

		WP_CLI::log( 'WooCommerce refuses the line after the item-data filter' );
		$mep_sh_before = count( $mep_sh_holds( $mep_sh_event ) );
		add_filter( 'woocommerce_is_sold_individually', '__return_true', 999 );
		add_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', '__return_true', 999 );
		$_POST         = $mep_sh_booking;
		$mep_sh_refuse = WC()->cart->add_to_cart( $mep_sh_product, 1 );
		$_POST         = array();
		remove_filter( 'woocommerce_is_sold_individually', '__return_true', 999 );
		remove_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', '__return_true', 999 );
		wc_clear_notices();
		$mep_sh_check( 'the line is refused', false === $mep_sh_refuse );
		$mep_sh_check( 'no seats are left held', count( $mep_sh_holds( $mep_sh_event ) ) === $mep_sh_before, ( count( $mep_sh_holds( $mep_sh_event ) ) - $mep_sh_before ) . ' new holds' );

		WP_CLI::log( 'Another visitor\'s hold expires' );
		WC()->cart->add_to_cart( $mep_sh_simple, 1 );
		$mep_sh_old = wp_insert_post( array( 'post_type' => 'mep_temp_attendee', 'post_status' => 'publish', 'post_title' => 'Expired hold' ) );
		update_post_meta( $mep_sh_old, 'event_id', $mep_sh_event );
		update_post_meta( $mep_sh_old, 'ticket_type', 'Standard' );
		update_post_meta( $mep_sh_old, 'ticket_qty', 1 );
		update_post_meta( $mep_sh_old, 'event_date', "$mep_sh_day 10:00" );
		wp_update_post( array( 'ID' => $mep_sh_old, 'post_date' => date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) - 2 * HOUR_IN_SECONDS ), 'post_date_gmt' => gmdate( 'Y-m-d H:i:s', time() - 2 * HOUR_IN_SECONDS ) ) );
		$mep_sh_fresh = wp_insert_post( array( 'post_type' => 'mep_temp_attendee', 'post_status' => 'publish', 'post_title' => 'Fresh hold' ) );
		update_post_meta( $mep_sh_fresh, 'event_id', $mep_sh_event );
		mep_temp_attendee_auto_delete_for_cart();
		$mep_sh_check( 'the expired hold is released', ! get_post( $mep_sh_old ) );
		$mep_sh_check( 'a hold still within the Cart Hold Time stays', (bool) get_post( $mep_sh_fresh ) );
		$mep_sh_check( "the current visitor's cart is not emptied", 1 === count( WC()->cart->get_cart() ) );
	} catch ( Throwable $mep_sh_error ) {
		$mep_sh_check( 'run without errors', false, $mep_sh_error->getMessage() );
	} finally {
		$_POST = array();
		if ( function_exists( 'WC' ) && WC()->cart ) {
			WC()->cart->empty_cart();
		}
		foreach ( get_posts( array( 'post_type' => 'mep_temp_attendee', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => 'event_id', 'value' => $mep_sh_event ) ) ) ) as $mep_sh_id ) {
			wp_delete_post( $mep_sh_id, true );
		}
		foreach ( array( $mep_sh_product, $mep_sh_simple ) as $mep_sh_id ) {
			if ( $mep_sh_id && function_exists( 'wc_get_product' ) && wc_get_product( $mep_sh_id ) ) {
				wc_get_product( $mep_sh_id )->delete( true );
			}
		}
		if ( $mep_sh_event ) {
			wp_delete_post( $mep_sh_event, true );
		}
	}

	if ( $mep_sh_failed ) {
		WP_CLI::error( $mep_sh_failed . ' case(s) failed.' );
	}
	WP_CLI::success( 'Cart seat holds follow their cart line.' );
