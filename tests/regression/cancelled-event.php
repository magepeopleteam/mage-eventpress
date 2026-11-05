<?php
	/**
	 * Regression tests: a cancelled event stays visible but can no longer be booked.
	 *
	 * The "Cancel this event" switch (MPWEM_Event_Status_Settings) keeps the event on its
	 * page and in every event list, marked as cancelled, while every booking path refuses
	 * it: the WooCommerce add-to-cart and checkout, carts filled before the cancellation,
	 * the ticket-panel AJAX and RSVP. These cases also pin the save rules (nonce,
	 * capability, presence flag, one action per transition), the frontend markup of all
	 * six list layouts, the schema.org status, the calendar feed, duplication and restore,
	 * plus the follow-up jobs: emailing everyone who booked, settling orders against a fake
	 * refund gateway, and blocking "Pay for order". Every email is intercepted, so nothing
	 * leaves the site and no real gateway is called.
	 *
	 * Not loaded by the plugin. Run it explicitly, on a development or staging site:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/cancelled-event.php
	 *
	 * A site whose environment type is "production" is refused unless `allow-production` is
	 * passed as an argument. The run creates its own category, events, orders, product and
	 * test user and deletes them afterwards, together with the cart holds, attendees and RSVP
	 * responses it produced, and puts back the one setting it changes. Exits with status 1
	 * when any case fails.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! function_exists( 'WC' ) || ! class_exists( 'MPWEM_Woocommerce' ) || ! class_exists( 'MPWEM_Event_Status_Settings' ) ) {
		WP_CLI::error( 'WooCommerce and Event Booking Manager for WooCommerce must both be active.' );
	}
	$mep_ce_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_ce_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. Run these tests on a test site, or pass allow-production to override.' );
	}
	if ( ! MPWEM_Global_Function::use_wc_payment() ) {
		WP_CLI::error( 'WooCommerce payment is switched off in Event Settings, so the WooCommerce booking path these cases cover is not loaded.' );
	}

	if ( ! class_exists( 'MEP_CE_Run' ) ) {
		final class MEP_CE_Run {
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
			 * Runs a public admin-ajax action in-process and returns its decoded JSON reply.
			 */
			public static function ajax( $action, array $post ) {
				$_POST    = $post;
				$_REQUEST = $post;
				$die      = function () {
					return function () {
						throw new Exception( 'wp_die' );
					};
				};
				add_filter( 'wp_doing_ajax', '__return_true' );
				add_filter( 'wp_die_ajax_handler', $die );
				ob_start();
				try {
					do_action( 'wp_ajax_nopriv_' . $action );
				} catch ( Exception $e ) {
					unset( $e );
				}
				$out = (string) ob_get_clean();
				remove_filter( 'wp_die_ajax_handler', $die );
				remove_filter( 'wp_doing_ajax', '__return_true' );
				$_POST    = array();
				$_REQUEST = array();
				return json_decode( $out, true );
			}
			/** @var array[] Mails caught by pre_wp_mail: nothing leaves the site. */
			public static $mails = array();
			public static function catch_mail( $return, $atts ) {
				self::$mails[] = $atts;
				return true;
			}
			public static function recipients( $event_id ) {
				$list = array_keys( MPWEM_Event_Cancellation::get_recipients( $event_id ) );
				sort( $list );
				return $list;
			}
			public static function refunds() {
				return class_exists( 'MEP_CE_Refund_Gateway' ) ? MEP_CE_Refund_Gateway::$refunds : array();
			}
			public static function count_notes( $order_id, $needle ) {
				$count = 0;
				foreach ( wc_get_order_notes( array( 'order_id' => $order_id ) ) as $note ) {
					$count += false !== strpos( $note->content, $needle ) ? 1 : 0;
				}
				return $count;
			}
			public static function has_note( $order_id, $needle ) {
				return self::count_notes( $order_id, $needle ) > 0;
			}
			public static function count_posts( $post_type, $meta_key, $value ) {
				return count( get_posts( array(
					'post_type'        => $post_type,
					'post_status'      => 'any',
					'numberposts'      => -1,
					'fields'           => 'ids',
					'suppress_filters' => true,
					'meta_key'         => $meta_key,
					'meta_value'       => $value,
				) ) );
			}
		}
	}

	$mep_ce_admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	if ( empty( $mep_ce_admins ) ) {
		WP_CLI::error( 'No administrator account to run the editor cases as.' );
	}
	$mep_ce_admin     = (int) $mep_ce_admins[0];
	$mep_ce_prev_user = get_current_user_id();
	$mep_ce_general   = get_option( 'general_setting_sec', null );
	$mep_ce_fixtures  = array( 'events' => array(), 'products' => array(), 'orders' => array(), 'term' => 0, 'user' => 0 );
	add_filter( 'pre_wp_mail', array( 'MEP_CE_Run', 'catch_mail' ), 10, 2 );

	try {
		$mep_ce_term = wp_insert_term( 'MEP cancellation test ' . wp_generate_password( 6, false ), 'mep_cat' );
		if ( is_wp_error( $mep_ce_term ) ) {
			throw new Exception( 'Could not create the test category: ' . $mep_ce_term->get_error_message() );
		}
		$mep_ce_fixtures['term'] = (int) $mep_ce_term['term_id'];
		$mep_ce_day              = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
		$mep_ce_meta             = array(
			'mep_reg_status'          => 'on',
			'mep_enable_recurring'    => 'no',
			'event_start_date'        => $mep_ce_day,
			'event_start_time'        => '10:00',
			'event_end_date'          => $mep_ce_day,
			'event_end_time'          => '12:00',
			'event_start_datetime'    => "$mep_ce_day 10:00:00",
			'event_end_datetime'      => "$mep_ce_day 12:00:00",
			'event_expire_datetime'   => "$mep_ce_day 12:00:00",
			'event_upcoming_datetime' => "$mep_ce_day 10:00:00",
			'mep_available_seat'      => 'on',
			'mep_event_ticket_type'   => array(
				array( 'option_name_t' => 'Standard', 'option_details_t' => '', 'option_price_t' => 25, 'option_qty_t' => 50, 'option_rsv_t' => 0, 'option_default_qty_t' => 0, 'option_qty_t_type' => 'inputbox', 'option_ticket_enable' => 'yes', 'option_ticket_mode_t' => 'inperson' ),
			),
		);
		foreach ( array( 'cancelled' => 'MEP Cancelled Course', 'open' => 'MEP Open Course' ) as $mep_ce_key => $mep_ce_title ) {
			$mep_ce_id = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'post_title' => $mep_ce_title, 'meta_input' => $mep_ce_meta ) );
			wp_set_object_terms( $mep_ce_id, array( $mep_ce_fixtures['term'] ), 'mep_cat' );
			$mep_ce_fixtures['events'][ $mep_ce_key ] = (int) $mep_ce_id;
			$mep_ce_fixtures['products'][]            = (int) get_post_meta( $mep_ce_id, 'link_wc_product', true );
		}
		$mep_ce_event   = $mep_ce_fixtures['events']['cancelled'];
		$mep_ce_open    = $mep_ce_fixtures['events']['open'];
		$mep_ce_product = (int) get_post_meta( $mep_ce_event, 'link_wc_product', true );
		$mep_ce_booking = array( 'option_name' => array( 'Standard' ), 'option_qty' => array( 1 ), 'max_qty' => array( 10 ), 'mep_event_start_date' => array( "$mep_ce_day 10:00" ) );
		if ( ! $mep_ce_product ) {
			throw new Exception( 'The event got no hidden WooCommerce product.' );
		}

		WP_CLI::log( 'Before cancelling' );
		wp_set_current_user( 0 );
		wc_load_cart();
		WC()->cart->empty_cart();
		$_POST = $mep_ce_booking;
		MEP_CE_Run::check( 'a scheduled event is bookable', MPWEM_Global_Function::is_bookable_event( $mep_ce_event ) && ! MPWEM_Global_Function::is_cancelled_event( $mep_ce_event ) );
		MEP_CE_Run::check( 'its tickets go into the cart', (bool) WC()->cart->add_to_cart( $mep_ce_product, 1 ) );
		$_POST = array();
		wc_clear_notices();

		WP_CLI::log( 'Saving the status' );
		$mep_ce_saver  = new MPWEM_Event_Status_Settings();
		$mep_ce_events = array();
		add_action( 'mpwem_event_cancelled', function ( $id ) use ( &$mep_ce_events ) {
			$mep_ce_events[] = 'cancelled:' . $id;
		} );
		add_action( 'mpwem_event_restored', function ( $id ) use ( &$mep_ce_events ) {
			$mep_ce_events[] = 'restored:' . $id;
		} );
		wp_set_current_user( $mep_ce_admin );
		$mep_ce_nonce = wp_create_nonce( MPWEM_Event_Status_Settings::NONCE_ACTION );
		$_POST        = array( 'mep_event_cancelled' => 'yes', MPWEM_Event_Status_Settings::NONCE_NAME => $mep_ce_nonce );
		$mep_ce_saver->save( $mep_ce_event );
		MEP_CE_Run::check( 'a form without the presence flag leaves the status alone', '' === get_post_meta( $mep_ce_event, 'mep_event_cancelled', true ) );
		$_POST = array( 'mep_event_status_present' => '1', MPWEM_Event_Status_Settings::NONCE_NAME => 'forged', 'mep_event_cancelled' => 'yes' );
		$mep_ce_saver->save( $mep_ce_event );
		MEP_CE_Run::check( 'a bad nonce is refused', '' === get_post_meta( $mep_ce_event, 'mep_event_cancelled', true ) );
		$mep_ce_fixtures['user'] = (int) wp_insert_user( array( 'user_login' => 'mep_ce_' . wp_generate_password( 6, false, false ), 'user_pass' => wp_generate_password(), 'role' => 'subscriber', 'user_email' => 'mep-ce-' . wp_generate_password( 6, false, false ) . '@example.invalid' ) );
		wp_set_current_user( $mep_ce_fixtures['user'] );
		$_POST = array( 'mep_event_status_present' => '1', MPWEM_Event_Status_Settings::NONCE_NAME => wp_create_nonce( MPWEM_Event_Status_Settings::NONCE_ACTION ), 'mep_event_cancelled' => 'yes' );
		$mep_ce_saver->save( $mep_ce_event );
		MEP_CE_Run::check( 'a subscriber cannot cancel', '' === get_post_meta( $mep_ce_event, 'mep_event_cancelled', true ) );
		wp_set_current_user( $mep_ce_admin );
		$_POST = array(
			'mep_event_status_present'               => '1',
			MPWEM_Event_Status_Settings::NONCE_NAME => $mep_ce_nonce,
			'mep_event_cancelled'                    => 'yes',
			'mep_event_cancel_message'               => wp_slash( 'This course has been cancelled due to illness. <strong>Refunds</strong> follow.<script>alert(1)</script>' ),
		);
		$mep_ce_saver->save( $mep_ce_event );
		$mep_ce_at  = get_post_meta( $mep_ce_event, 'mep_event_cancelled_at', true );
		$mep_ce_msg = (string) get_post_meta( $mep_ce_event, 'mep_event_cancel_message', true );
		MEP_CE_Run::check( 'an editor cancels the event', 'yes' === get_post_meta( $mep_ce_event, 'mep_event_cancelled', true ) && '' !== $mep_ce_at );
		MEP_CE_Run::check( 'the notice keeps formatting but loses scripts', false !== strpos( $mep_ce_msg, '<strong>Refunds</strong>' ) && false === strpos( $mep_ce_msg, '<script' ) );
		MEP_CE_Run::check( 'mpwem_event_cancelled fires once', array( 'cancelled:' . $mep_ce_event ) === $mep_ce_events, wp_json_encode( $mep_ce_events ) );
		$mep_ce_saver->save( $mep_ce_event );
		MEP_CE_Run::check( 'saving again keeps the date and fires nothing', $mep_ce_at === get_post_meta( $mep_ce_event, 'mep_event_cancelled_at', true ) && 1 === count( $mep_ce_events ) );
		MEP_CE_Run::check( 'the Event Mode is kept for a restore', 'on' === MPWEM_Global_Function::get_event_mode( $mep_ce_event ) );
		MEP_CE_Run::check( 'the event is no longer bookable', ! MPWEM_Global_Function::is_bookable_event( $mep_ce_event ) && MPWEM_Global_Function::is_bookable_event( $mep_ce_open ) );
		$_POST = array();

		WP_CLI::log( 'Booking paths' );
		wp_set_current_user( 0 );
		do_action( 'woocommerce_check_cart_items' );
		MEP_CE_Run::check( 'a cart line added before the cancellation is removed', 0 === count( WC()->cart->get_cart() ) );
		MEP_CE_Run::check( 'the customer is told why', false !== strpos( wp_json_encode( wc_get_notices( 'error' ) ), 'has been cancelled' ) );
		wc_clear_notices();
		$_POST = $mep_ce_booking;
		MEP_CE_Run::check( 'add to cart is refused', ! WC()->cart->add_to_cart( $mep_ce_product, 1 ) && false !== strpos( wp_json_encode( wc_get_notices( 'error' ) ), 'has been cancelled' ) );
		wc_clear_notices();
		MEP_CE_Run::check( 'add-to-cart validation fails', false === apply_filters( 'woocommerce_add_to_cart_validation', true, $mep_ce_product, 1 ) );
		wc_clear_notices();
		$_POST = array();
		$mep_ce_reply = MEP_CE_Run::ajax( 'get_mpwem_ticket', array( 'post_id' => $mep_ce_event ) );
		MEP_CE_Run::check( 'the ticket panel AJAX refuses', is_array( $mep_ce_reply ) && false === $mep_ce_reply['success'], wp_json_encode( $mep_ce_reply ) );
		update_post_meta( $mep_ce_event, 'mep_reg_status', 'rsvp' );
		$mep_ce_reply = MEP_CE_Run::ajax( 'mep_submit_rsvp', array( 'event_id' => $mep_ce_event, 'nonce' => wp_create_nonce( 'mep_rsvp_nonce_' . $mep_ce_event ), 'rsvp_name' => 'Test', 'rsvp_email' => 'test@example.invalid', 'rsvp_phone' => '1', 'rsvp_date' => "$mep_ce_day 10:00", 'rsvp_qty' => 1 ) );
		MEP_CE_Run::check( 'an RSVP is refused and stores nothing', is_array( $mep_ce_reply ) && false === $mep_ce_reply['success'] && 0 === MEP_CE_Run::count_posts( 'mep_rsvp_responses', 'ea_event_id', $mep_ce_event ), wp_json_encode( $mep_ce_reply ) );
		update_post_meta( $mep_ce_event, 'mep_reg_status', 'on' );

		WP_CLI::log( 'Emailing everyone who booked' );
		wp_set_current_user( $mep_ce_admin );
		// Fixture bookings: Ann (own email), Bob (no attendee email, buyer billing email),
		// Carol (RSVP), Dave (cancelled order, must not be mailed).
		$mep_ce_status_hooks = isset( $GLOBALS['wp_filter']['woocommerce_order_status_changed'] ) ? $GLOBALS['wp_filter']['woocommerce_order_status_changed'] : null;
		unset( $GLOBALS['wp_filter']['woocommerce_order_status_changed'] ); // Fixtures only: no confirmation mails, no attendee repair.
		$mep_ce_other = new WC_Product_Simple();
		$mep_ce_other->set_name( 'MEP test mug' );
		$mep_ce_other->set_regular_price( '30' );
		$mep_ce_other->save();
		$mep_ce_fixtures['products'][] = $mep_ce_other->get_id();
		$mep_ce_make_order = function ( $status, $gateway, $email, $with_other = false ) use ( $mep_ce_product, $mep_ce_event, $mep_ce_other, $mep_ce_day, &$mep_ce_fixtures ) {
			$order = wc_create_order();
			$item  = new WC_Order_Item_Product();
			$item->set_product_id( $mep_ce_product );
			$item->set_name( 'MEP Cancelled Course' );
			$item->set_quantity( 2 );
			$item->set_subtotal( 50 );
			$item->set_total( 50 );
			$item->add_meta_data( 'event_id', $mep_ce_event );
			$item->add_meta_data( '_event_ticket_info', array( array( 'ticket_name' => 'Standard', 'ticket_price' => 25, 'ticket_qty' => 2, 'event_date' => $mep_ce_day . ' 10:00', 'event_id' => $mep_ce_event ) ) );
			$order->add_item( $item );
			if ( $with_other ) {
				$order->add_product( $mep_ce_other, 1 );
			}
			$order->set_billing_email( $email );
			$order->set_billing_first_name( 'Buyer' );
			$order->set_payment_method( $gateway );
			$order->calculate_totals( false );
			$order->set_status( $status );
			if ( in_array( $status, array( 'processing', 'completed' ), true ) ) {
				$order->set_date_paid( time() );
			}
			$order->save();
			$mep_ce_fixtures['orders'][] = $order->get_id();
			return $order;
		};
		$mep_ce_add_attendee = function ( $post_type, $order_id, $name, $email, $status ) use ( $mep_ce_event ) {
			return wp_insert_post( array( 'post_type' => $post_type, 'post_status' => 'publish', 'post_title' => $name, 'meta_input' => array(
				'ea_event_id' => $mep_ce_event, 'ea_order_id' => $order_id, 'ea_name' => $name, 'ea_email' => $email, 'ea_order_status' => $status,
			) ) );
		};
		if ( ! class_exists( 'MEP_CE_Refund_Gateway' ) ) {
			class MEP_CE_Refund_Gateway extends WC_Payment_Gateway {
				public static $refunds = array();
				public function __construct() {
					$this->id = 'mep_ce_refund_gw'; $this->method_title = 'MEP test refunds'; $this->title = 'MEP test refunds';
					$this->supports = array( 'products', 'refunds' ); $this->enabled = 'yes';
				}
				public function process_refund( $order_id, $amount = null, $reason = '' ) {
					self::$refunds[] = array( (int) $order_id, (float) $amount );
					return true;
				}
			}
		}
		add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {
			$gateways[] = 'MEP_CE_Refund_Gateway';
			return $gateways;
		} );
		WC()->payment_gateways()->init();
		$mep_ce_paid      = $mep_ce_make_order( 'processing', 'mep_ce_refund_gw', 'ann@example.invalid', true );
		$mep_ce_cod       = $mep_ce_make_order( 'processing', 'cod', 'bob@example.invalid' );
		$mep_ce_pending   = $mep_ce_make_order( 'pending', 'bacs', 'erin@example.invalid' );
		$mep_ce_mixed     = $mep_ce_make_order( 'pending', 'bacs', 'frank@example.invalid', true );
		$mep_ce_onhold    = $mep_ce_make_order( 'on-hold', 'bacs', 'gina@example.invalid' );
		$mep_ce_cancelled = $mep_ce_make_order( 'cancelled', 'bacs', 'dave@example.invalid' );
		if ( null !== $mep_ce_status_hooks ) {
			$GLOBALS['wp_filter']['woocommerce_order_status_changed'] = $mep_ce_status_hooks;
		}
		$mep_ce_add_attendee( 'mep_events_attendees', $mep_ce_paid->get_id(), 'Ann', 'ANN@example.invalid', 'processing' );
		$mep_ce_add_attendee( 'mep_events_attendees', $mep_ce_paid->get_id(), 'Ann', 'ann@example.invalid', 'processing' );
		$mep_ce_add_attendee( 'mep_events_attendees', $mep_ce_cod->get_id(), 'Bob', '', 'processing' );
		$mep_ce_add_attendee( 'mep_events_attendees', $mep_ce_pending->get_id(), 'Erin', 'erin@example.invalid', 'pending' );
		$mep_ce_add_attendee( 'mep_events_attendees', $mep_ce_mixed->get_id(), 'Frank', 'frank@example.invalid', 'pending' );
		$mep_ce_add_attendee( 'mep_events_attendees', $mep_ce_onhold->get_id(), 'Gina', 'gina@example.invalid', 'on-hold' );
		$mep_ce_add_attendee( 'mep_events_attendees', $mep_ce_cancelled->get_id(), 'Dave', 'dave@example.invalid', 'cancelled' );
		$mep_ce_add_attendee( 'mep_rsvp_responses', 0, 'Carol', 'carol@example.invalid', 'completed' );
		$mep_ce_recipients = MEP_CE_Run::recipients( $mep_ce_event );
		MEP_CE_Run::check( 'recipients: one per address, buyer email as fallback, cancelled orders left out', array( 'ann@example.invalid', 'bob@example.invalid', 'carol@example.invalid', 'erin@example.invalid', 'frank@example.invalid', 'gina@example.invalid' ) === $mep_ce_recipients, implode( ',', $mep_ce_recipients ) );
		$_POST = array(
			'mep_event_status_present'               => '1',
			MPWEM_Event_Status_Settings::NONCE_NAME => $mep_ce_nonce,
			'mep_event_cancelled'                    => 'yes',
			'mep_event_cancel_notify'                => 'yes',
			'mep_event_cancel_refund'                => 'yes',
			'mep_event_cancel_email_subject'         => 'Off: {event_name}',
			'mep_event_cancel_email_body'            => "Dear {attendee_name}, {event_name} is off. {cancel_message}",
		);
		$mep_ce_saver->save( $mep_ce_event );
		$_POST = array();
		$mep_ce_jobs = MPWEM_Event_Cancellation::get_jobs( $mep_ce_event );
		MEP_CE_Run::check( 'saving queues both jobs', isset( $mep_ce_jobs['notify'], $mep_ce_jobs['refund'] ) && 'queued' === $mep_ce_jobs['notify']['status'] );
		WP_CLI::log( 'Settling orders' );
		// Refunds run first on purpose, the worst case for the email job that follows.
		do_action( MPWEM_Event_Cancellation::JOB_HOOK, $mep_ce_event, 'refund' );
		$mep_ce_jobs = MPWEM_Event_Cancellation::get_jobs( $mep_ce_event );
		MEP_CE_Run::check( 'orders are settled in batches', MPWEM_Event_Cancellation::REFUND_BATCH === count( $mep_ce_jobs['refund']['orders'] ) && 'running' === $mep_ce_jobs['refund']['status'] );
		do_action( MPWEM_Event_Cancellation::JOB_HOOK, $mep_ce_event, 'refund' );
		$mep_ce_jobs = MPWEM_Event_Cancellation::get_jobs( $mep_ce_event );
		MEP_CE_Run::check( 'the next run finishes them', 'done' === $mep_ce_jobs['refund']['status'] && 6 === count( $mep_ce_jobs['refund']['orders'] ), wp_json_encode( $mep_ce_jobs['refund'] ) );
		$mep_ce_res  = $mep_ce_jobs['refund']['orders'];
		$mep_ce_paid = wc_get_order( $mep_ce_paid->get_id() );
		MEP_CE_Run::check( 'a paid order is refunded for the event line only, through its gateway', 'refunded' === ( $mep_ce_res[ $mep_ce_paid->get_id() ] ?? '' ) && array( array( $mep_ce_paid->get_id(), 50.0 ) ) === MEP_CE_Run::refunds() && abs( (float) $mep_ce_paid->get_total_refunded() - 50 ) < 0.001 && $mep_ce_paid->has_status( 'processing' ), wp_json_encode( array( $mep_ce_res, MEP_CE_Run::refunds() ) ) );
		MEP_CE_Run::check( 'a gateway without refunds is left for a manual refund, with a note', 'manual' === ( $mep_ce_res[ $mep_ce_cod->get_id() ] ?? '' ) && MEP_CE_Run::has_note( $mep_ce_cod->get_id(), 'manually' ) && abs( (float) wc_get_order( $mep_ce_cod->get_id() )->get_total_refunded() ) < 0.001 );
		MEP_CE_Run::check( 'an unpaid event-only order is cancelled', 'cancelled_unpaid' === ( $mep_ce_res[ $mep_ce_pending->get_id() ] ?? '' ) && wc_get_order( $mep_ce_pending->get_id() )->has_status( 'cancelled' ) );
		MEP_CE_Run::check( 'an unpaid order with other items stays open, with a note', 'mixed_unpaid' === ( $mep_ce_res[ $mep_ce_mixed->get_id() ] ?? '' ) && wc_get_order( $mep_ce_mixed->get_id() )->has_status( 'pending' ) && MEP_CE_Run::has_note( $mep_ce_mixed->get_id(), 'left open' ) );
		MEP_CE_Run::check( 'an on-hold order is not cancelled', 'manual' === ( $mep_ce_res[ $mep_ce_onhold->get_id() ] ?? '' ) && wc_get_order( $mep_ce_onhold->get_id() )->has_status( 'on-hold' ) );
		MEP_CE_Run::check( 'an already cancelled order is skipped', 'skipped' === ( $mep_ce_res[ $mep_ce_cancelled->get_id() ] ?? '' ) );
		do_action( MPWEM_Event_Cancellation::JOB_HOOK, $mep_ce_event, 'refund' );
		MEP_CE_Run::check( 'running it again refunds nothing twice', 1 === count( MEP_CE_Run::refunds() ) );
		$mep_ce_jobs = MPWEM_Event_Cancellation::get_jobs( $mep_ce_event );
		MEP_CE_Run::check( 'running it again leaves the recorded results alone', $mep_ce_res === $mep_ce_jobs['refund']['orders'], wp_json_encode( $mep_ce_jobs['refund']['orders'] ) );
		MEP_CE_Run::check( 'running it again adds no second order note', 1 === MEP_CE_Run::count_notes( $mep_ce_cod->get_id(), 'manually' ), (string) MEP_CE_Run::count_notes( $mep_ce_cod->get_id(), 'manually' ) );
		$mep_ce_ann = get_posts( array( 'post_type' => 'mep_events_attendees', 'post_status' => array( 'any', 'trash' ), 'numberposts' => -1, 'fields' => 'ids', 'suppress_filters' => true, 'meta_key' => 'ea_order_id', 'meta_value' => $mep_ce_paid->get_id() ) );
		MEP_CE_Run::check( 'a partial refund stops those tickets holding seats', 2 === count( $mep_ce_ann ) && 'trash' === get_post_status( $mep_ce_ann[0] ) && 'refunded' === get_post_meta( $mep_ce_ann[0], 'ea_order_status', true ) );
		MEP_CE_Run::check( '"Pay for order" is refused for the cancelled event', ! wc_get_order( $mep_ce_mixed->get_id() )->needs_payment() );
		WP_CLI::log( 'Emailing, after the orders were settled' );
		MEP_CE_Run::$mails = array();
		do_action( MPWEM_Event_Cancellation::JOB_HOOK, $mep_ce_event, 'notify' );
		$mep_ce_to = array_map( function ( $mail ) {
			return strtolower( is_array( $mail['to'] ) ? implode( ',', $mail['to'] ) : $mail['to'] );
		}, MEP_CE_Run::$mails );
		sort( $mep_ce_to );
		MEP_CE_Run::check( 'each person gets exactly one email', $mep_ce_recipients === $mep_ce_to, implode( ',', $mep_ce_to ) );
		$mep_ce_first = MEP_CE_Run::$mails ? MEP_CE_Run::$mails[0] : array( 'subject' => '', 'message' => '' );
		MEP_CE_Run::check( 'the email uses the saved template and placeholders', 'Off: MEP Cancelled Course' === $mep_ce_first['subject'] && false !== strpos( $mep_ce_first['message'], 'MEP Cancelled Course is off' ) && false !== strpos( $mep_ce_first['message'], 'due to illness' ) && false === strpos( $mep_ce_first['message'], '{' ) );
		MEP_CE_Run::$mails = array();
		do_action( MPWEM_Event_Cancellation::JOB_HOOK, $mep_ce_event, 'notify' );
		MEP_CE_Run::check( 'running the job again mails nobody twice', 0 === count( MEP_CE_Run::$mails ) );
		$mep_ce_jobs = MPWEM_Event_Cancellation::get_jobs( $mep_ce_event );
		MEP_CE_Run::check( 'the email job reports done', 'done' === $mep_ce_jobs['notify']['status'] && 6 === (int) $mep_ce_jobs['notify']['sent'] );
		MEP_CE_Run::check( 'no copy of the addresses stays behind', ! isset( $mep_ce_jobs['notify']['recipients'] ) && false === strpos( wp_json_encode( get_post_meta( $mep_ce_event, 'mep_event_cancel_notified', true ) ), '@' ) );
		MEP_CE_Run::check( 'the editor shows the results', false !== strpos( MEP_CE_Run::render( function () use ( $mep_ce_event ) {
			MPWEM_Event_Status_Settings::render_fields( $mep_ce_event );
		} ), 'Emails sent to 6 of 6 people' ) );
		$mep_ce_counts = mpwem_active_expire_count();
		MEP_CE_Run::check( 'the backend list counts it as cancelled, not active', $mep_ce_counts['cancelled_count'] >= 1 );
		wp_set_current_user( 0 );

		WP_CLI::log( 'Event page' );
		wp_set_current_user( $mep_ce_admin );
		$mep_ce_slot = MEP_CE_Run::render( function () use ( $mep_ce_event ) {
			do_action( 'mpwem_registration', $mep_ce_event, array() );
		} );
		MEP_CE_Run::check( 'the booking box becomes the full notice', false !== strpos( $mep_ce_slot, 'mpwem_cancelled_notice--booking' ) && false === strpos( $mep_ce_slot, 'id="mpwem_registration"' ) && false !== strpos( $mep_ce_slot, 'due to illness' ) );
		$mep_ce_title = MEP_CE_Run::render( function () use ( $mep_ce_event ) {
			do_action( 'mpwem_title', $mep_ce_event );
		} );
		MEP_CE_Run::check( 'a banner follows the title', false !== strpos( $mep_ce_title, 'mpwem_cancelled_notice--banner' ) && strpos( $mep_ce_title, 'mpwem_tile' ) < strpos( $mep_ce_title, 'mpwem_cancelled_notice' ) );
		MEP_CE_Run::check( 'the title-only call prints no banner', false === strpos( MEP_CE_Run::render( function () use ( $mep_ce_event ) {
			do_action( 'mpwem_title', $mep_ce_event, 'only' );
		} ), 'mpwem_cancelled_notice' ) );
		$mep_ce_slot = MEP_CE_Run::render( function () use ( $mep_ce_event ) {
			do_action( 'mpwem_registration', $mep_ce_event, array() );
		} );
		MEP_CE_Run::check( 'after the banner the booking box is a short note', false !== strpos( $mep_ce_slot, 'mpwem_cancelled_notice--compact' ) );
		MEP_CE_Run::check( 'seat count and calendar links are hidden', '' === trim( MEP_CE_Run::render( function () use ( $mep_ce_event ) {
			do_action( 'mpwem_seat_status', $mep_ce_event, array() );
			do_action( 'mpwem_add_calender', $mep_ce_event, array(), '' );
			do_action( 'mpwem_registration_content', $mep_ce_event, array(), array(), '' );
		} ) ) );
		MEP_CE_Run::check( 'an open event keeps its booking form', false !== strpos( MEP_CE_Run::render( function () use ( $mep_ce_open ) {
			do_action( 'mpwem_registration', $mep_ce_open, array() );
		} ), 'id="mpwem_registration"' ) );
		$mep_ce_schema = apply_filters( 'mpwem_event_schema', array( 'eventStatus' => 'https://schema.org/EventRescheduled', 'previousStartDate' => '2026-01-01' ), $mep_ce_event );
		MEP_CE_Run::check( 'schema.org reports EventCancelled', 'https://schema.org/EventCancelled' === $mep_ce_schema['eventStatus'] && ! isset( $mep_ce_schema['previousStartDate'] ) );

		WP_CLI::log( 'Event lists' );
		$mep_ce_settings = is_array( $mep_ce_general ) ? $mep_ce_general : array();
		update_option( 'general_setting_sec', array_merge( $mep_ce_settings, array( 'mep_show_sold_out_ribbon_list_page' => 'yes' ) ) );
		foreach ( array( 'grid', 'list', 'minimal', 'native', 'timeline', 'winter', 'spring' ) as $mep_ce_style ) {
			$mep_ce_html = do_shortcode( '[event-list style="' . $mep_ce_style . '" cat="' . $mep_ce_fixtures['term'] . '" show="10"]' );
			$mep_ce_ok   = false !== strpos( $mep_ce_html, 'MEP Cancelled Course' ) && false !== strpos( $mep_ce_html, 'MEP Open Course' )
				&& 1 === substr_count( $mep_ce_html, 'mep-event-is-cancelled' )
				&& 1 === substr_count( $mep_ce_html, 'mpwem_list_cancelled_notice' )
				&& false !== strpos( $mep_ce_html, 'This course has been cancelled due to illness' )
				&& false === strpos( $mep_ce_html, 'mepev-ribbon sold-out' )
				&& ( 'minimal' === $mep_ce_style || 1 === substr_count( $mep_ce_html, 'mepev-ribbon cancelled' ) );
			MEP_CE_Run::check( "the $mep_ce_style layout lists it as cancelled", $mep_ce_ok );
		}
		$mep_ce_grid = do_shortcode( '[event-list style="grid" cat="' . $mep_ce_fixtures['term'] . '" show="10"]' );
		MEP_CE_Run::check( 'only the open card shows a price', 1 === substr_count( $mep_ce_grid, 'class="list_price"' ) );

		WP_CLI::log( 'Calendar, admin and duplication' );
		if ( class_exists( 'MPWEM_Calendar_Ajax' ) && method_exists( 'MPWEM_Calendar_Ajax', 'build_event_data' ) ) {
			$mep_ce_cal    = ( new ReflectionClass( 'MPWEM_Calendar_Ajax' ) )->newInstanceWithoutConstructor();
			$mep_ce_method = new ReflectionMethod( $mep_ce_cal, 'build_event_data' );
			$mep_ce_method->setAccessible( true );
			$mep_ce_entry = $mep_ce_method->invoke( $mep_ce_cal, $mep_ce_event, 'x', 'http://example.invalid', "$mep_ce_day 10:00", "$mep_ce_day 12:00", 'no', 'offline', 'on', '', '', array(), 25, '', '#0073aa', '#fff', '#dc3545', '#ffc107', 5, 'yes', 'no', 'yes', 'yes', 'yes' );
			MEP_CE_Run::check( 'the calendar marks it cancelled, without seats or price', true === $mep_ce_entry['extendedProps']['isCancelled'] && in_array( 'mep-cal-cancelled-event', $mep_ce_entry['classNames'], true ) && 'no' === $mep_ce_entry['extendedProps']['showStock'] && 'no' === $mep_ce_entry['extendedProps']['showPrice'] );
		}
		MEP_CE_Run::check( 'the editor draws the switch on', (bool) preg_match( '/name="mep_event_cancelled"[^>]*checked/', MEP_CE_Run::render( function () use ( $mep_ce_event ) {
			MPWEM_Event_Status_Settings::render_fields( $mep_ce_event );
		} ) ) );
		$mep_ce_states = apply_filters( 'display_post_states', array(), get_post( $mep_ce_event ) );
		MEP_CE_Run::check( 'the events table shows a Cancelled state', isset( $mep_ce_states['mpwem_cancelled'] ) );
		$_GET     = array( 'post_id' => (string) $mep_ce_event, '_wpnonce' => wp_create_nonce( 'mpwem_duplicate_post_' . $mep_ce_event ) );
		$mep_ce_r = function ( $location ) {
			throw new Exception( (string) $location );
		};
		add_filter( 'wp_redirect', $mep_ce_r, -1000 );
		$mep_ce_copy = 0;
		try {
			( new MPWEM_Event_Lists() )->mpwem_duplicate_post_function();
		} catch ( Exception $e ) {
			if ( preg_match( '/post=(\d+)/', $e->getMessage(), $mep_ce_m ) ) {
				$mep_ce_copy = (int) $mep_ce_m[1];
			}
		}
		remove_filter( 'wp_redirect', $mep_ce_r, -1000 );
		$_GET = array();
		if ( $mep_ce_copy ) {
			$mep_ce_fixtures['events']['copy'] = $mep_ce_copy;
		}
		MEP_CE_Run::check( 'a duplicate starts out not cancelled', $mep_ce_copy && ! MPWEM_Global_Function::is_cancelled_event( $mep_ce_copy ) && 'on' === get_post_meta( $mep_ce_copy, 'mep_reg_status', true ) );

		WP_CLI::log( 'Restoring' );
		$_POST = array( 'mep_event_status_present' => '1', MPWEM_Event_Status_Settings::NONCE_NAME => $mep_ce_nonce, 'mep_event_cancel_message' => wp_slash( $mep_ce_msg ) );
		$mep_ce_saver->save( $mep_ce_event );
		$_POST = array();
		MEP_CE_Run::check( 'restoring clears the status and keeps the notice', 'no' === get_post_meta( $mep_ce_event, 'mep_event_cancelled', true ) && '' === get_post_meta( $mep_ce_event, 'mep_event_cancelled_at', true ) && '' !== get_post_meta( $mep_ce_event, 'mep_event_cancel_message', true ) );
		MEP_CE_Run::check( 'mpwem_event_restored fires', 'restored:' . $mep_ce_event === end( $mep_ce_events ) );
		MEP_CE_Run::check( 'restoring starts a fresh email round', '' === get_post_meta( $mep_ce_event, 'mep_event_cancel_notified', true ) && ! isset( MPWEM_Event_Cancellation::get_jobs( $mep_ce_event )['notify'] ) );
		wp_set_current_user( 0 );
		$_POST = $mep_ce_booking;
		MEP_CE_Run::check( 'the restored event books again', (bool) WC()->cart->add_to_cart( $mep_ce_product, 1 ) );
		$_POST = array();
	} catch ( Exception $mep_ce_error ) {
		MEP_CE_Run::check( 'test run completed', false, $mep_ce_error->getMessage() );
	} finally {
		$_POST = array();
		if ( function_exists( 'WC' ) && WC()->cart ) {
			WC()->cart->empty_cart();
			wc_clear_notices();
		}
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->destroy_session();
		}
		foreach ( $mep_ce_fixtures['events'] as $mep_ce_id ) {
			foreach ( array( 'mep_temp_attendee' => 'event_id', 'mep_events_attendees' => 'ea_event_id', 'mep_rsvp_responses' => 'ea_event_id' ) as $mep_ce_type => $mep_ce_key ) {
				foreach ( get_posts( array( 'post_type' => $mep_ce_type, 'post_status' => array( 'any', 'trash' ), 'numberposts' => -1, 'fields' => 'ids', 'suppress_filters' => true, 'meta_key' => $mep_ce_key, 'meta_value' => $mep_ce_id ) ) as $mep_ce_child ) {
					wp_delete_post( $mep_ce_child, true );
				}
			}
			wp_delete_post( $mep_ce_id, true );
		}
		foreach ( $mep_ce_fixtures['orders'] as $mep_ce_id ) {
			$mep_ce_order = wc_get_order( $mep_ce_id );
			if ( $mep_ce_order ) {
				$mep_ce_order->delete( true );
			}
		}
		if ( function_exists( 'as_unschedule_all_actions' ) && class_exists( 'MPWEM_Event_Cancellation' ) ) {
			foreach ( $mep_ce_fixtures['events'] as $mep_ce_id ) {
				foreach ( array( 'notify', 'refund' ) as $mep_ce_task ) {
					as_unschedule_all_actions( MPWEM_Event_Cancellation::JOB_HOOK, array( (int) $mep_ce_id, $mep_ce_task ), 'mage-eventpress' );
				}
			}
		}
		remove_filter( 'pre_wp_mail', array( 'MEP_CE_Run', 'catch_mail' ), 10 );
		foreach ( array_filter( $mep_ce_fixtures['products'] ) as $mep_ce_id ) {
			wp_delete_post( $mep_ce_id, true );
		}
		if ( $mep_ce_fixtures['term'] ) {
			wp_delete_term( $mep_ce_fixtures['term'], 'mep_cat' );
		}
		if ( $mep_ce_fixtures['user'] ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( $mep_ce_fixtures['user'] );
		}
		if ( null === $mep_ce_general ) {
			delete_option( 'general_setting_sec' );
		} else {
			update_option( 'general_setting_sec', $mep_ce_general );
		}
		wp_set_current_user( $mep_ce_prev_user );
	}

	$mep_ce_failed = count( array_filter( MEP_CE_Run::$results, function ( $pass ) {
		return ! $pass;
	} ) );
	if ( $mep_ce_failed ) {
		WP_CLI::log( sprintf( '%d of %d case(s) failed.', $mep_ce_failed, count( MEP_CE_Run::$results ) ) );
		WP_CLI::halt( 1 );
	}
	WP_CLI::success( sprintf( 'All %d cases passed.', count( MEP_CE_Run::$results ) ) );
