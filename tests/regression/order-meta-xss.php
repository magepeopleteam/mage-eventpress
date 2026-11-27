<?php
/**
 * Regression tests for Patchstack #41618: order item meta XSS & server-authoritative venue/time-slot.
 *
 * Run with:
 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/order-meta-xss.php
 *
 * @package mage-eventpress
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	die( 'Run this through WP-CLI.' );
}
if ( ! class_exists( 'MPWEM_Woocommerce' ) || ! class_exists( 'WC_Order_Item_Product' ) ) {
	WP_CLI::error( 'WooCommerce and Event Booking Manager for WooCommerce must be active.' );
}
$mep_xss_args = isset( $args ) && is_array( $args ) ? $args : array();
if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_xss_args, true ) ) {
	WP_CLI::error( 'This site reports a production environment; pass allow-production to override.' );
}

$results = array();
$check   = function ( $name, $pass, $detail = '' ) use ( &$results ) {
	$results[] = (bool) $pass;
	WP_CLI::log( sprintf( '%s  %s%s', $pass ? 'PASS' : 'FAIL', $name, '' !== $detail ? ' - ' . $detail : '' ) );
};

$created_posts = array();
$saved_post    = $_POST;

try {
	$payload = '< div id="tagsdiv-post_tag">< /div>< div class="tagsdiv" id="x&quot;&gt;&lt;img src=x onerror=alert(document.domain)&gt;">< textarea class="the-tags">a< /textarea>< ul class="tagchecklist">< /ul>< /div>';

	// Create a linked WooCommerce product and a single event with a real venue.
	$wc_product_id   = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'ZZ XSS Test Product' ) );
	$created_posts[] = $wc_product_id;
	update_post_meta( $wc_product_id, '_price', '10' );
	update_post_meta( $wc_product_id, '_regular_price', '10' );

	$event_id        = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'post_title' => 'ZZ XSS Test Event' ) );
	$created_posts[] = $event_id;
	update_post_meta( $event_id, 'link_wc_product', $wc_product_id );
	update_post_meta( $wc_product_id, 'link_mep_event', $event_id );
	update_post_meta( $event_id, 'mep_location_venue', 'Grand Hall' );
	update_post_meta( $event_id, 'mep_city', 'Dhaka' );
	update_post_meta( $event_id, 'mep_enable_recurring', 'everyday' );
	update_post_meta( $event_id, 'mep_disable_ticket_time', 'yes' );
	$start_day = date( 'Y-m-d', strtotime( '+1 day' ) );
	$end_day   = date( 'Y-m-d', strtotime( '+10 days' ) );
	update_post_meta( $event_id, 'event_start_date', $start_day );
	update_post_meta( $event_id, 'event_start_time', '09:00' );
	update_post_meta( $event_id, 'event_end_date', $end_day );
	update_post_meta( $event_id, 'event_end_time', '18:00' );
	update_post_meta( $event_id, 'event_start_datetime', "$start_day 09:00:00" );
	update_post_meta( $event_id, 'event_end_datetime', "$end_day 18:00:00" );
	update_post_meta( $event_id, 'event_expire_datetime', "$end_day 18:00:00" );
	update_post_meta( $event_id, 'event_upcoming_datetime', "$start_day 09:00:00" );
	update_post_meta( $event_id, 'mep_available_seat', 'on' );
	update_post_meta( $event_id, 'mep_ticket_times_global', array(
		array( 'mep_ticket_time_name' => 'Morning Session', 'mep_ticket_time' => '09:00' ),
		array( 'mep_ticket_time_name' => 'Morning & Afternoon', 'mep_ticket_time' => '18:00' ),
	) );
	update_post_meta( $event_id, 'mep_event_ticket_type', array(
		array( 'option_name_t' => 'Adult', 'option_price_t' => 10, 'option_qty_t' => 50, 'option_default_qty_t' => 0, 'option_rsv_t' => 0, 'option_ticket_enable' => 'yes' ),
	) );
	update_post_meta( $event_id, 'mep_reg_status', 'on' );

	$target_date = date( 'Y-m-d', strtotime( '+2 days' ) ) . ' 09:00:00';
	$woo         = new MPWEM_Woocommerce();

	// 1. Forged mep_event_location_cart & forged time_slot_name in add_cart_item_data().
	$_POST = array(
		'mpwem_post_id'           => $event_id,
		'mep_event_start_date'    => array( $target_date ),
		'option_name'             => array( 'Adult' ),
		'ticket_type'             => array( 'Adult' ),
		'option_qty'              => array( 1 ),
		'mep_event_location_cart' => $payload,
		'time_slot_name'          => $payload,
	);
	$cart_data = $woo->add_cart_item_data( array(), $wc_product_id, 0 );
	$check(
		'add_cart_item_data ignores forged mep_event_location_cart and derives server venue',
		'Grand Hall, Dhaka' === $cart_data['event_cart_location'],
		'got: ' . ( $cart_data['event_cart_location'] ?? 'unset' )
	);
	$check(
		'add_cart_item_data rejects forged time_slot_name',
		empty( $cart_data['event_everyday_time_slot'] ),
		'got: ' . ( $cart_data['event_everyday_time_slot'] ?? 'unset' )
	);

	// 2. Legitimate configured time slot is accepted.
	$_POST['time_slot_name'] = 'Morning Session';
	$cart_data_valid         = $woo->add_cart_item_data( array(), $wc_product_id, 0 );
	$check(
		'add_cart_item_data accepts configured time_slot_name',
		'Morning Session' === ( $cart_data_valid['event_everyday_time_slot'] ?? '' ),
		'got: ' . ( $cart_data_valid['event_everyday_time_slot'] ?? 'unset' )
	);

	// 3. Checkout line-item meta ignores poisoned cart values for location & time slot, and escapes attendee values.
	$item = new WC_Order_Item_Product();
	$item->set_product_id( $wc_product_id );
	$poisoned_values = array(
		'event_id'                 => $event_id,
		'event_cart_location'      => $payload,
		'event_everyday_time_slot' => $payload,
		'event_cart_date'          => $target_date,
		'event_ticket_info'        => array(),
		'event_extra_service'      => array(),
		'event_extra_option'       => array(),
		'event_user_info'          => array(
			array(
				'user_name'    => $payload,
				'user_address' => '123 < b > St',
			),
		),
	);
	MPWEM_Woocommerce::add_event_line_item_meta( $item, $poisoned_values );

	$location_label = mep_get_option( 'mep_location_text_x', 'label_setting_sec', esc_html__( 'Location', 'mage-eventpress' ) );
	$slot_label     = mep_get_option( 'mep_event_rec_time_slot_text', 'label_setting_sec', __( 'Time Slot:', 'mage-eventpress' ) );
	$name_label     = mep_get_reg_label( $event_id, 'Name' );

	$saved_location = (string) $item->get_meta( $location_label, true );
	$saved_slot     = (string) $item->get_meta( $slot_label, true );
	$saved_name     = (string) $item->get_meta( $name_label, true );

	$check(
		'add_event_line_item_meta writes server-derived location instead of poisoned cart value',
		'Grand Hall, Dhaka' === $saved_location && false === strpos( $saved_location, 'tagsdiv' ),
		'got: ' . $saved_location
	);
	$check(
		'mep_re_add_cart_order_data rejects poisoned time_slot in cart values',
		'' === $saved_slot && '' === (string) $item->get_meta( '_time_slot', true ),
		'got: ' . $saved_slot
	);
	$check(
		'attendee visible order item meta is HTML-escaped against wp_kses_post tag revival',
		false === strpos( wp_kses_post( $saved_name ), '<div' ) && false !== strpos( $saved_name, '&lt;' ),
		'got: ' . substr( $saved_name, 0, 60 )
	);

	// 4. Configured time slot with special characters is escaped in order item meta.
	$item2 = new WC_Order_Item_Product();
	$item2->set_product_id( $wc_product_id );
	MPWEM_Woocommerce::add_event_line_item_meta( $item2, array(
		'event_id'                 => $event_id,
		'event_cart_location'      => '',
		'event_everyday_time_slot' => 'Morning & Afternoon',
		'event_cart_date'          => $target_date,
		'event_ticket_info'        => array(),
		'event_extra_service'      => array(),
		'event_extra_option'       => array(),
		'event_user_info'          => array(),
	) );
	$saved_slot2 = (string) $item2->get_meta( $slot_label, true );
	$check(
		'configured time slot is saved escaped in order item meta',
		'Morning &amp; Afternoon' === $saved_slot2 && false === strpos( wp_kses_post( $saved_slot2 ), '<' ),
		'got: ' . $saved_slot2
	);
} finally {
	$_POST = $saved_post;
	foreach ( $created_posts as $pid ) {
		if ( $pid ) {
			wp_delete_post( $pid, true );
		}
	}
}

$passed = count( array_filter( $results ) );
$total  = count( $results );
WP_CLI::log( sprintf( 'Summary: %d/%d checks passed.', $passed, $total ) );
if ( $passed !== $total ) {
	WP_CLI::error( 'One or more order-meta XSS regression checks failed.' );
}
