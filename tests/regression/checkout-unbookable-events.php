<?php
	/**
	 * Regression tests: an event line in the cart must not check out once the event has
	 * become password-protected, private, drafted, restricted to members, or closed.
	 *
	 * A visitor may add tickets while the event is published and open, and only later
	 * reach the cart or checkout. The checkout-time checks must remove lines that are no
	 * longer bookable for that visitor and display a clear reason, stopping both classic
	 * and Store API / block checkout from placing the order.
	 *
	 * Run explicitly through WP-CLI:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/checkout-unbookable-events.php allow-production
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! function_exists( 'WC' ) || ! class_exists( 'MPWEM_Woocommerce' ) ) {
		WP_CLI::error( 'WooCommerce and Event Booking Manager for WooCommerce must both be active.' );
	}
	$mep_cue_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_cue_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. Run these tests on a test site, or pass allow-production to override.' );
	}

	final class MEP_CUE_Run {
		public static $results = array();

		public static function check( $name, $pass, $detail = '' ) {
			self::$results[] = (bool) $pass;
			WP_CLI::log( sprintf( '%s  %s%s', $pass ? 'PASS' : 'FAIL', $name, $detail ? ' - ' . $detail : '' ) );
		}
	}

	$fixtures = array(
		'events'   => array(),
		'products' => array(),
		'users'    => array(),
	);

	function mep_cue_create_event( $title, $status = 'publish', $meta_overrides = array() ) {
		global $fixtures;
		$day  = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
		$meta = array_merge( array(
			'mep_reg_status'          => 'on',
			'mep_enable_recurring'    => 'no',
			'event_start_date'        => $day,
			'event_start_time'        => '10:00',
			'event_end_date'          => $day,
			'event_end_time'          => '12:00',
			'event_start_datetime'    => "$day 10:00:00",
			'event_end_datetime'      => "$day 12:00:00",
			'event_expire_datetime'   => "$day 12:00:00",
			'event_upcoming_datetime' => "$day 10:00:00",
			'mep_available_seat'      => 'on',
			'mep_event_ticket_type'   => array(
				array(
					'option_name_t'        => 'Standard',
					'option_details_t'     => '',
					'option_price_t'       => 25,
					'option_qty_t'         => 50,
					'option_rsv_t'         => 0,
					'option_default_qty_t' => 0,
					'option_qty_t_type'    => 'inputbox',
					'option_ticket_enable' => 'yes',
					'option_ticket_mode_t' => 'inperson',
				),
			),
		), $meta_overrides );

		$event_id = wp_insert_post( array(
			'post_type'   => 'mep_events',
			'post_status' => $status,
			'post_title'  => $title,
			'meta_input'  => $meta,
		) );

		$product_id = (int) get_post_meta( $event_id, 'link_wc_product', true );
		if ( ( ! $product_id || 'product' !== get_post_type( $product_id ) ) && class_exists( 'MPWEM_Hidden_Product' ) ) {
			MPWEM_Hidden_Product::create_hidden_wc_product( $event_id, $title );
			$product_id = (int) get_post_meta( $event_id, 'link_wc_product', true );
		}

		$fixtures['events'][]   = $event_id;
		$fixtures['products'][] = $product_id;

		return array(
			'event_id'   => $event_id,
			'product_id' => $product_id,
			'date'       => "$day 10:00",
		);
	}

	function mep_cue_count_holds( $event_id ) {
		return count( get_posts( array(
			'post_type'        => 'mep_temp_attendee',
			'post_status'      => 'any',
			'numberposts'      => -1,
			'fields'           => 'ids',
			'suppress_filters' => true,
			'meta_query'       => array(
				array( 'key' => 'event_id', 'value' => $event_id ),
			),
		) ) );
	}

	function mep_cue_add_to_cart( $fixture ) {
		$_POST = array(
			'option_name'          => array( 'Standard' ),
			'option_qty'           => array( 1 ),
			'max_qty'              => array( 10 ),
			'mep_event_start_date' => array( $fixture['date'] ),
		);
		$added = WC()->cart->add_to_cart( $fixture['product_id'], 1 );
		$_POST = array();
		return $added;
	}

	try {
		$admin_user = (int) get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) )[0];

		// ---------------------------------------------------------------------
		WP_CLI::log( 'Password-protected event' );
		// ---------------------------------------------------------------------
		$ev_pw = mep_cue_create_event( 'CUE Password Event' );
		wp_set_current_user( 0 );
		wc_load_cart();
		WC()->cart->empty_cart();
		wc_clear_notices();

		MEP_CUE_Run::check( 'bookable before password is set', (bool) mep_cue_add_to_cart( $ev_pw ) );
		MEP_CUE_Run::check( 'line sits in the cart', 1 === count( WC()->cart->get_cart() ) );

		wp_update_post( array( 'ID' => $ev_pw['event_id'], 'post_password' => 'secret123' ) );
		do_action( 'woocommerce_check_cart_items' );

		MEP_CUE_Run::check( 'unauthenticated cart check removes the protected line', 0 === count( WC()->cart->get_cart() ) );
		MEP_CUE_Run::check( 'customer is told the event is password-protected', false !== strpos( wp_json_encode( wc_get_notices( 'error' ) ), 'is password-protected' ) );
		wc_clear_notices();

		// ---------------------------------------------------------------------
		WP_CLI::log( 'Draft event' );
		// ---------------------------------------------------------------------
		$ev_draft = mep_cue_create_event( 'CUE Draft Event' );
		wp_set_current_user( 0 );
		WC()->cart->empty_cart();
		wc_clear_notices();

		MEP_CUE_Run::check( 'bookable while published', (bool) mep_cue_add_to_cart( $ev_draft ) );
		wp_update_post( array( 'ID' => $ev_draft['event_id'], 'post_status' => 'draft' ) );
		do_action( 'woocommerce_check_cart_items' );

		MEP_CUE_Run::check( 'visitor cart check removes the drafted line', 0 === count( WC()->cart->get_cart() ) );
		MEP_CUE_Run::check( 'customer is told the event is no longer available', false !== strpos( wp_json_encode( wc_get_notices( 'error' ) ), 'is no longer available' ) );
		wc_clear_notices();

		// Editor test-booking
		wp_set_current_user( $admin_user );
		MEP_CUE_Run::check( 'editor can add draft event to cart', (bool) mep_cue_add_to_cart( $ev_draft ) );
		do_action( 'woocommerce_check_cart_items' );
		MEP_CUE_Run::check( 'editor cart keeps draft event line', 1 === count( WC()->cart->get_cart() ) );
		WC()->cart->empty_cart();
		wc_clear_notices();
		wp_set_current_user( 0 );

		// ---------------------------------------------------------------------
		WP_CLI::log( 'Private event' );
		// ---------------------------------------------------------------------
		$ev_priv = mep_cue_create_event( 'CUE Private Event' );
		wp_set_current_user( 0 );
		WC()->cart->empty_cart();
		wc_clear_notices();

		MEP_CUE_Run::check( 'bookable while public', (bool) mep_cue_add_to_cart( $ev_priv ) );
		wp_update_post( array( 'ID' => $ev_priv['event_id'], 'post_status' => 'private' ) );
		do_action( 'woocommerce_check_cart_items' );

		MEP_CUE_Run::check( 'visitor cart check removes the private line', 0 === count( WC()->cart->get_cart() ) );
		MEP_CUE_Run::check( 'customer is told the private event is no longer available', false !== strpos( wp_json_encode( wc_get_notices( 'error' ) ), 'is no longer available' ) );
		wc_clear_notices();

		// ---------------------------------------------------------------------
		WP_CLI::log( 'Member-only event' );
		// ---------------------------------------------------------------------
		$ev_mem = mep_cue_create_event( 'CUE Member Event' );
		wp_set_current_user( 0 );
		WC()->cart->empty_cart();
		wc_clear_notices();

		MEP_CUE_Run::check( 'bookable before member rule is set', (bool) mep_cue_add_to_cart( $ev_mem ) );
		update_post_meta( $ev_mem['event_id'], 'mep_member_only_event', 'member_only' );
		update_post_meta( $ev_mem['event_id'], 'mep_member_only_user_role', array( 'administrator' ) );
		do_action( 'woocommerce_check_cart_items' );

		MEP_CUE_Run::check( 'guest cart check removes the member-only line', 0 === count( WC()->cart->get_cart() ) );
		MEP_CUE_Run::check( 'customer is told the event is for members only', false !== strpos( wp_json_encode( wc_get_notices( 'error' ) ), 'is available for members only' ) );
		wc_clear_notices();

		// Logged in as administrator
		wp_set_current_user( $admin_user );
		MEP_CUE_Run::check( 'administrator can add member-only event to cart', (bool) mep_cue_add_to_cart( $ev_mem ) );
		do_action( 'woocommerce_check_cart_items' );
		MEP_CUE_Run::check( 'administrator cart keeps member-only line', 1 === count( WC()->cart->get_cart() ) );
		WC()->cart->empty_cart();
		wc_clear_notices();
		wp_set_current_user( 0 );

		// ---------------------------------------------------------------------
		WP_CLI::log( 'Booking turned off / unbookable' );
		// ---------------------------------------------------------------------
		$ev_off = mep_cue_create_event( 'CUE Closed Event' );
		wp_set_current_user( 0 );
		WC()->cart->empty_cart();
		wc_clear_notices();

		MEP_CUE_Run::check( 'bookable initially', (bool) mep_cue_add_to_cart( $ev_off ) );
		update_post_meta( $ev_off['event_id'], 'mep_reg_status', 'off' );
		do_action( 'woocommerce_check_cart_items' );

		MEP_CUE_Run::check( 'cart check removes line when booking is turned off', 0 === count( WC()->cart->get_cart() ) );
		MEP_CUE_Run::check( 'customer is told booking is closed', false !== strpos( wp_json_encode( wc_get_notices( 'error' ) ), 'booking for this event is closed' ) );
		wc_clear_notices();

		// ---------------------------------------------------------------------
		WP_CLI::log( 'Event dates removed or changed' );
		// ---------------------------------------------------------------------
		$ev_date = mep_cue_create_event( 'CUE Date Event' );
		wp_set_current_user( 0 );
		WC()->cart->empty_cart();
		wc_clear_notices();

		MEP_CUE_Run::check( 'bookable with original date', (bool) mep_cue_add_to_cart( $ev_date ) );
		// Move event to another date
		$new_day = gmdate( 'Y-m-d', strtotime( '+45 days' ) );
		update_post_meta( $ev_date['event_id'], 'event_start_date', $new_day );
		update_post_meta( $ev_date['event_id'], 'event_end_date', $new_day );
		update_post_meta( $ev_date['event_id'], 'event_start_datetime', "$new_day 10:00:00" );
		update_post_meta( $ev_date['event_id'], 'event_end_datetime', "$new_day 12:00:00" );
		update_post_meta( $ev_date['event_id'], 'event_upcoming_datetime', "$new_day 10:00:00" );
		do_action( 'woocommerce_check_cart_items' );

		MEP_CUE_Run::check( 'cart check removes line when booked date is no longer offered', 0 === count( WC()->cart->get_cart() ) );
		MEP_CUE_Run::check( 'customer is told selected date is no longer available', false !== strpos( wp_json_encode( wc_get_notices( 'error' ) ), 'selected date is no longer available' ) );
		wc_clear_notices();

		// ---------------------------------------------------------------------
		WP_CLI::log( 'Seat holds release' );
		// ---------------------------------------------------------------------
		$ev_hold = mep_cue_create_event( 'CUE Hold Event' );
		wp_set_current_user( 0 );
		WC()->cart->empty_cart();
		wc_clear_notices();

		mep_cue_add_to_cart( $ev_hold );
		$holds_before = mep_cue_count_holds( $ev_hold['event_id'] );
		MEP_CUE_Run::check( 'seat hold is registered when added to cart', $holds_before > 0, "holds: $holds_before" );

		wp_update_post( array( 'ID' => $ev_hold['event_id'], 'post_password' => 'pass' ) );
		do_action( 'woocommerce_check_cart_items' );

		$holds_after = mep_cue_count_holds( $ev_hold['event_id'] );
		MEP_CUE_Run::check( 'seat hold is released when unbookable line is removed', 0 === $holds_after, "holds: $holds_after" );
		wc_clear_notices();

		// ---------------------------------------------------------------------
		WP_CLI::log( 'Checkout attempt validation' );
		// ---------------------------------------------------------------------
		$ev_chk = mep_cue_create_event( 'CUE Checkout Block Test' );
		wp_set_current_user( 0 );
		WC()->cart->empty_cart();
		wc_clear_notices();

		mep_cue_add_to_cart( $ev_chk );
		MEP_CUE_Run::check( 'line added for checkout test', 1 === count( WC()->cart->get_cart() ) );

		wp_update_post( array( 'ID' => $ev_chk['event_id'], 'post_password' => 'secret_pass_test' ) );
		$orders_before = count( wc_get_orders( array( 'limit' => -1 ) ) );

		$_REQUEST = $_POST = array(
			'billing_first_name'                 => 'Guest',
			'billing_last_name'                  => 'Tester',
			'billing_country'                    => 'US',
			'billing_address_1'                  => '123 Main St',
			'billing_city'                       => 'Anytown',
			'billing_state'                      => 'CA',
			'billing_postcode'                   => '90210',
			'billing_phone'                      => '5551234567',
			'billing_email'                      => 'guest@example.com',
			'payment_method'                     => 'cod',
			'woocommerce-process-checkout-nonce' => wp_create_nonce( 'woocommerce-process_checkout' ),
		);

		ob_start();
		try {
			WC()->checkout()->process_checkout();
		} catch ( Throwable $e ) {
			// Checkout aborts with error notice or exception
		}
		ob_end_clean();
		$_POST = $_REQUEST = array();

		$orders_after = count( wc_get_orders( array( 'limit' => -1 ) ) );
		MEP_CUE_Run::check( 'checkout of password-protected event is blocked and places no order', $orders_before === $orders_after );
		MEP_CUE_Run::check( 'cart is now empty', 0 === count( WC()->cart->get_cart() ) );
		wc_clear_notices();

	} finally {
		// Clean up fixtures
		WC()->cart->empty_cart();
		wc_clear_notices();
		foreach ( $fixtures['events'] as $eid ) {
			wp_delete_post( $eid, true );
		}
		foreach ( $fixtures['products'] as $pid ) {
			if ( $pid ) {
				wp_delete_post( $pid, true );
			}
		}
		foreach ( $fixtures['users'] as $uid ) {
			wp_delete_user( $uid );
		}
	}

	$total  = count( MEP_CUE_Run::$results );
	$passed = count( array_filter( MEP_CUE_Run::$results ) );
	$failed = $total - $passed;

	if ( $failed > 0 ) {
		WP_CLI::error( sprintf( '%d checks, %d passed, %d failed.', $total, $passed, $failed ) );
	}

	WP_CLI::success( sprintf( 'All %d unbookable cart and checkout checks passed.', $total ) );
