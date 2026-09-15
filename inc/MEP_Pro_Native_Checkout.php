<?php
/*
 * @Author      engr.sumonazma@gmail.com
 * Copyright:   mage-people.com
 *
 * Native (offline) checkout handler — creates a mep_custom_order record alongside the
 * mep_events_attendees records. Offline/pay-at-door works standalone; PayPal/Stripe are
 * a PRO extension registered via MEP_Payment_Gateway_Manager (guarded with class_exists()
 * below since PRO may not be active — in that case every checkout simply falls through to
 * the offline/pending branch in process_checkout()).
 *
 * Handles:
 *   - AJAX action 'mep_native_checkout' (frontend ticket booking when WooCommerce is absent)
 *   - GET ?mep_payment_return=1  (gateway callback after external payment e.g. PayPal)
 *   - GET ?mep_payment_cancel=1  (user cancelled payment at gateway; requires the
 *     single-use cancel token issued with that payment attempt)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MEP_Pro_Native_Checkout' ) ) {
	class MEP_Pro_Native_Checkout {

		/**
		 * Single-use secret bound to one payment attempt and handed only to the
		 * gateway's cancel URL, which is the only thing that may trash the order it
		 * belongs to.
		 *
		 * Deliberately NOT _mep_booking_token: that one is the read-only key to the
		 * confirmation page, it travels in a URL customers bookmark and forward, and
		 * it has to keep working after the booking is over. A cancellation key must be
		 * able to do neither - be reusable, nor survive being spent.
		 */
		const CANCEL_TOKEN_META = '_mep_cancel_token';
		const CANCEL_TOKEN_ARG  = 'mep_cancel_token';

		public function __construct() {
			// Priority 5 ensures this runs before the basic plugin's handler (default priority 10).
			// wp_send_json_success/error both call wp_die(), so the basic handler never runs.
			add_action( 'wp_ajax_mep_native_checkout',        array( $this, 'process_checkout' ), 5 );
			add_action( 'wp_ajax_nopriv_mep_native_checkout', array( $this, 'process_checkout' ), 5 );

			// Handle gateway payment-return and cancel redirects on page load.
			add_action( 'init', array( $this, 'handle_payment_return' ) );

			// Registration is open to both guests and logged-in users.
		}

		// -------------------------------------------------------------------------
		// AJAX checkout handler
		// -------------------------------------------------------------------------

		public function process_checkout() {
			// 1. Nonce verification
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_native_checkout_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'mage-eventpress' ) ) );
			}

			// 2. Validate required fields
			$event_id      = isset( $_POST['event_id'] )      ? absint( $_POST['event_id'] )                                  : 0;
			$billing_name  = isset( $_POST['billing_name'] )  ? sanitize_text_field( wp_unslash( $_POST['billing_name'] ) )   : '';
			$billing_email = isset( $_POST['billing_email'] ) ? sanitize_email( wp_unslash( $_POST['billing_email'] ) )       : '';
			$billing_phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) )  : '';
			$event_date    = isset( $_POST['event_date'] )    ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) )     : '';
			$ticket_data   = isset( $_POST['ticket_data'] )   ? wp_unslash( $_POST['ticket_data'] )                          : '';
			$attendee_data = isset( $_POST['attendee_fields'] ) ? wp_unslash( $_POST['attendee_fields'] )                   : '';
			$gateway_id    = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';

			if ( ! $event_id ) {
				wp_send_json_error( array( 'message' => __( 'Invalid event.', 'mage-eventpress' ) ) );
			}

			// Everyday events that hide the ticket-time selector post a date with no real
			// time. Default it to the event's "Start Date Time" time field so the booking —
			// and the attendee record it creates — carries the event's configured time.
			$event_date = $this->resolve_event_datetime( $event_id, $event_date );

			// Both guests and logged-in users may register; the billing form
			// (name/email/phone, all required) supplies the customer details.
			// Fall back to the logged-in user's account data when a value is missing.
			if ( ! $billing_name || ! $billing_email ) {
				$u = wp_get_current_user();
				if ( ! $billing_name )  $billing_name  = $u->display_name;
				if ( ! $billing_email ) $billing_email = $u->user_email;
			}
			// Billing form requires name, email and phone for every payment method.
			if ( ! $billing_name || ! $billing_email || ! $billing_phone ) {
				wp_send_json_error( array( 'message' => __( 'Please provide your billing name, email and phone.', 'mage-eventpress' ) ) );
			}
			if ( ! is_email( $billing_email ) ) {
				wp_send_json_error( array( 'message' => __( 'A valid email address is required.', 'mage-eventpress' ) ) );
			}

			// 3. Decode ticket JSON
			$tickets = array();
			if ( $ticket_data ) {
				$tickets = json_decode( $ticket_data, true );
			}
			if ( ! is_array( $tickets ) || empty( $tickets ) ) {
				wp_send_json_error( array( 'message' => __( 'Please select at least one ticket.', 'mage-eventpress' ) ) );
			}

			// 4. Build normalised items list and calculate total.
			//
			// SECURITY: never trust the client-supplied ticket_price. This endpoint is reachable
			// unauthenticated (wp_ajax_nopriv_mep_native_checkout) and $total decides whether the
			// booking is treated as free/completed — a forged ticket_price:0 would grant a paid
			// ticket for free. Validate each ticket name against the event's configured ticket
			// types and take the price from the server-side configuration (mirroring the
			// WooCommerce cart path, which already looks the price up by name). The client price
			// is discarded.
			$configured_types = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', array() );
			$valid_names      = array();
			if ( is_array( $configured_types ) ) {
				foreach ( $configured_types as $configured_type ) {
					$configured_name = is_array( $configured_type ) && isset( $configured_type['option_name_t'] ) ? $configured_type['option_name_t'] : '';
					$valid_names[]   = html_entity_decode( urldecode( $configured_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				}
			}
			$total = 0.0;
			$items = array();
			foreach ( $tickets as $ticket ) {
				$qty   = isset( $ticket['ticket_qty'] )   ? absint( $ticket['ticket_qty'] )           : 0;
				$name  = isset( $ticket['ticket_name'] )  ? sanitize_text_field( $ticket['ticket_name'] ) : '';
				if ( $qty > 0 ) {
					$name_decoded = html_entity_decode( urldecode( $name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					if ( '' === $name || ! in_array( $name_decoded, $valid_names, true ) ) {
						wp_send_json_error( array( 'message' => __( 'Invalid ticket selected.', 'mage-eventpress' ) ) );
					}
					// Authoritative price from the event configuration; the client value is discarded.
					$price   = (float) MPWEM_Functions::get_ticket_price_by_name( $name, $event_id, $configured_types );
					$total  += $price * $qty;
					$items[] = array(
						'name'  => $name,
						'qty'   => $qty,
						'price' => $price,
						'total' => $price * $qty,
					);
				}
			}

			if ( empty( $items ) ) {
				wp_send_json_error( array( 'message' => __( 'Please select at least one ticket.', 'mage-eventpress' ) ) );
			}

			// 5. Create the mep_custom_order record (tracks payments independently of WooCommerce)
			$order_id = wp_insert_post( array(
				'post_title'  => sprintf( __( 'Order - %s', 'mage-eventpress' ), date_i18n( 'M d, Y @ h:i A' ) ),
				'post_type'   => 'mep_custom_order',
				'post_status' => 'pending',
				'post_author' => get_current_user_id(),
			) );

			if ( is_wp_error( $order_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Could not create order record. Please try again.', 'mage-eventpress' ) ) );
			}

			// Store the WP user ID of the booker so user info can always be fetched fresh.
			update_post_meta( $order_id, '_mep_user_id', get_current_user_id() );

			// Random per-order secret required (alongside the order ID) to view the booking
			// confirmation page — prevents enumerating mep_booking_id to read other
			// customers' name/email/phone/order details (the confirmation page is public
			// and otherwise has no ownership check).
			update_post_meta( $order_id, '_mep_booking_token', wp_generate_password( 32, false ) );

			update_post_meta( $order_id, '_mep_event_id',       $event_id );
			update_post_meta( $order_id, '_mep_order_total',    $total );
			update_post_meta( $order_id, '_mep_customer_name',  $billing_name );
			update_post_meta( $order_id, '_mep_customer_email', $billing_email );
			update_post_meta( $order_id, '_mep_customer_phone', $billing_phone );
			update_post_meta( $order_id, '_mep_order_items',    $items );
			update_post_meta( $order_id, '_mep_event_date',     $event_date );

			// Store the billing details collected on the registration modal.
			update_post_meta( $order_id, '_mep_billing', array(
				'name'  => $billing_name,
				'email' => $billing_email,
				'phone' => $billing_phone,
			) );

			// 5b. Map the posted registration-form fields to ea_* meta. Each field is posted
			// as an array indexed by seat (mirroring the WooCommerce flow), so build one meta
			// set per seat and store the list for the payment-return callback, which has no
			// access to the original POST data.
			$field_arrays = array();
			if ( $attendee_data ) {
				$field_arrays = json_decode( $attendee_data, true );
				if ( ! is_array( $field_arrays ) ) {
					$field_arrays = array();
				}
			}
			$seat_metas = $this->build_seat_metas( $event_id, $items, $field_arrays );
			if ( ! empty( $seat_metas ) ) {
				update_post_meta( $order_id, '_mep_attendee_extra_meta', $seat_metas );
			}

			// Store a labelled copy of the first attendee's fields so the admin order
			// list/detail can show human-readable labels (each attendee's own values live
			// on its attendee record).
			if ( ! empty( $field_arrays ) && class_exists( 'MPWEM_Layout' ) ) {
				$first_posted = array();
				foreach ( $field_arrays as $fname => $vals ) {
					$first_posted[ $fname ] = is_array( $vals ) ? ( isset( $vals[0] ) ? $vals[0] : '' ) : $vals;
				}
				$labelled_fields = array();
				$form_array      = MPWEM_Layout::get_form_array( $event_id );
				foreach ( $form_array as $field ) {
					if ( ! is_array( $field ) ) {
						continue;
					}
					$fname = $field['name']  ?? '';
					$label = $field['label'] ?? '';
					$type  = $field['type']  ?? '';
					if ( ! $fname || ! $label || $type === 'title' || ! array_key_exists( $fname, $first_posted ) ) {
						continue;
					}
					$raw   = $first_posted[ $fname ];
					$value = is_array( $raw ) ? implode( ', ', $raw ) : (string) $raw;
					$value = $type === 'textarea' ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
					if ( $value === '' ) {
						continue;
					}
					$labelled_fields[] = array(
						'label' => sanitize_text_field( $label ),
						'value' => $value,
					);
				}
				if ( ! empty( $labelled_fields ) ) {
					update_post_meta( $order_id, '_mep_attendee_form_fields', $labelled_fields );
				}
			}

			// 6. Route by payment type
			if ( $total <= 0 ) {
				// Free event — complete immediately
				$this->complete_order( $order_id, $event_id, $items, $billing_name, $billing_email, $billing_phone, $event_date, 'free', 'completed', $seat_metas );
				return;
			}

			// Resolve gateway — PayPal/Stripe only exist when PRO is active; without it,
			// $gateway stays null and checkout falls through to the offline branch below.
			$gateway = null;
			if ( class_exists( 'MEP_Payment_Gateway_Manager' ) ) {
				$gateway_manager = MEP_Payment_Gateway_Manager::get_instance();
				$available       = $gateway_manager->get_available_gateways();

				if ( empty( $gateway_id ) && ! empty( $available ) ) {
					$gateway_id = array_key_first( $available );
				}

				$gateway = $gateway_id ? $gateway_manager->get_gateway( $gateway_id ) : null;
			}
			update_post_meta( $order_id, '_mep_payment_gateway', $gateway_id );

			// If gateway supports get_payment_url(), redirect the browser to the payment page
			if ( $gateway && $gateway->is_enabled() && method_exists( $gateway, 'get_payment_url' ) ) {
				$currency_settings = get_option( 'mep_currency_settings', array() );
				$currency_code     = ! empty( $currency_settings['mep_currency_code'] ) ? $currency_settings['mep_currency_code'] : 'USD';

				$return_url = add_query_arg( array(
					'mep_payment_return' => 1,
					'order_id'           => $order_id,
					'gateway'            => $gateway_id,
				), home_url() );

				// Mint the cancel key for THIS payment attempt. It exists only from here
				// until the customer either pays or backs out, and only the gateway is
				// ever handed it - so possession of it is what tells handle_payment_return()
				// that a cancellation really came back from the checkout we started.
				$cancel_token = wp_generate_password( 32, false );
				update_post_meta( $order_id, self::CANCEL_TOKEN_META, $cancel_token );

				$cancel_url = add_query_arg( array(
					'mep_payment_cancel' => 1,
					'order_id'           => $order_id,
					'gateway'            => $gateway_id,
					self::CANCEL_TOKEN_ARG => $cancel_token,
				), home_url() );

				$payment_url = $gateway->get_payment_url( array(
					'order_id'       => $order_id,
					'total'          => $total,
					'currency'       => $currency_code,
					'event_id'       => $event_id,
					'customer_name'  => $billing_name,
					'customer_email' => $billing_email,
					'return_url'     => $return_url,
					'cancel_url'     => $cancel_url,
				) );

				if ( $payment_url && ! is_wp_error( $payment_url ) ) {
					wp_send_json_success( array(
						'requires_redirect' => true,
						'redirect'          => $payment_url,
						'booking_id'        => $order_id,
					) );
					return;
				}

				// Gateway call failed — delete the pending order and return the error
				wp_delete_post( $order_id, true );
				$error_msg = is_wp_error( $payment_url )
					? $payment_url->get_error_message()
					: __( 'Could not connect to the payment gateway. Please check your gateway credentials or try again.', 'mage-eventpress' );
				wp_send_json_error( array( 'message' => $error_msg ) );
				return;
			}

			// No gateway configured — offline / pay at door
			$this->complete_order( $order_id, $event_id, $items, $billing_name, $billing_email, $billing_phone, $event_date, 'offline', 'pending', $seat_metas );
		}

		/**
		 * Resolve the booking's event datetime, defaulting the time for everyday events
		 * that hide the ticket-time selector.
		 *
		 * Those events have no time picker in the booking form, so the posted date arrives
		 * without a real time (it collapses to midnight). Per the event's design, the time
		 * should come from the "Start Date Time" section's time field. Other event types
		 * already carry the chosen/occurrence time and are returned unchanged.
		 *
		 * @param int    $event_id
		 * @param string $event_date Posted booking date (possibly time-less).
		 * @return string
		 */
		private function resolve_event_datetime( $event_id, $event_date ) {
			if ( ! $event_id || ! $event_date ) {
				return $event_date;
			}

			// Only everyday events that hide the time selector need this fallback.
			$recurring   = get_post_meta( $event_id, 'mep_enable_recurring', true );
			$time_hidden = get_post_meta( $event_id, 'mep_disable_ticket_time', true ); // 'no' = selector hidden
			if ( 'everyday' !== $recurring || 'no' !== $time_hidden ) {
				return $event_date;
			}

			// Keep any real (non-midnight) time already present on the posted value.
			if ( class_exists( 'MPWEM_Global_Function' )
				&& MPWEM_Global_Function::check_time_exit_date( $event_date ) ) {
				return $event_date;
			}

			// Default the time to the event's Start Date Time time field.
			$start_time = get_post_meta( $event_id, 'event_start_time', true );
			$timestamp  = strtotime( $event_date );
			if ( $start_time && $timestamp ) {
				return date( 'Y-m-d', $timestamp ) . ' ' . $start_time;
			}

			return $event_date;
		}

		// -------------------------------------------------------------------------
		// Internal helper — create attendees, update order status, send email, respond
		// -------------------------------------------------------------------------

		/**
		 * Build one attendee meta set per seat from the posted registration-form fields.
		 *
		 * Mirrors the WooCommerce flow: every field is posted as an array indexed by the
		 * global seat order across all ticket types, so seat N reads index N from each field.
		 * When the "same attendee" setting is on, every seat reuses the first attendee's data.
		 *
		 * @param int   $event_id
		 * @param array $items        Normalised order items (name, qty, price).
		 * @param array $field_arrays field name => array of values in seat order.
		 * @return array Numerically-indexed list of ea_* meta arrays, one per seat.
		 */
		private function build_seat_metas( $event_id, $items, $field_arrays ) {
			$seat_metas = array();
			if ( ! function_exists( 'mep_collect_attendee_form_fields' ) ) {
				return $seat_metas;
			}
			$same          = function_exists( 'mep_get_option' ) ? mep_get_option( 'mep_enable_same_attendee', 'general_setting_sec', 'no' ) : 'no';
			$same_attendee = ( $same === 'yes' || $same === 'must' );

			$count = 0;
			foreach ( (array) $items as $item ) {
				$qty = isset( $item['qty'] ) ? absint( $item['qty'] ) : 0;
				for ( $j = 0; $j < $qty; $j++ ) {
					$idx    = $same_attendee ? 0 : $count;
					$posted = array();
					foreach ( (array) $field_arrays as $fname => $vals ) {
						if ( is_array( $vals ) ) {
							$posted[ $fname ] = array_key_exists( $idx, $vals ) ? $vals[ $idx ] : '';
						} else {
							$posted[ $fname ] = $vals; // legacy scalar payload
						}
					}
					$seat_metas[] = mep_collect_attendee_form_fields( $event_id, $posted );
					$count++;
				}
			}
			return $seat_metas;
		}

		private function complete_order( $order_id, $event_id, $items, $billing_name, $billing_email, $billing_phone, $event_date, $payment_method, $order_status, $seat_metas = array() ) {
			$post_status = ( $order_status === 'completed' ) ? 'publish' : 'pending';
			wp_update_post( array(
				'ID'          => $order_id,
				'post_status' => $post_status,
			) );
			update_post_meta( $order_id, '_mep_payment_gateway', $payment_method );

			$user_info = array(
				'user_name'       => $billing_name,
				'user_email'      => $billing_email,
				'user_phone'      => $billing_phone,
				'user_event_date' => $event_date,
			);

			$attendee_ids = array();
			$seat_index   = 0;
			foreach ( $items as $item ) {
				$qty = isset( $item['qty'] ) ? absint( $item['qty'] ) : 0;
				if ( $qty <= 0 ) {
					continue;
				}
				// One attendee record per seat (quantity) so seat counts are accurate and
				// each ticket has its own attendee. With a registration form each seat gets
				// its own submitted data; without one every seat shares the billing details.
				$ticket_info = array(
					'ticket_name'  => isset( $item['name'] )  ? $item['name']            : '',
					'ticket_qty'   => 1,
					'ticket_price' => isset( $item['price'] ) ? (float) $item['price']   : 0.0,
				);
				if ( function_exists( 'mep_native_ticket_attendee_create' ) ) {
					for ( $seat = 0; $seat < $qty; $seat++ ) {
						$seat_meta = ( isset( $seat_metas[ $seat_index ] ) && is_array( $seat_metas[ $seat_index ] ) ) ? $seat_metas[ $seat_index ] : array();
						$pid = mep_native_ticket_attendee_create( $event_id, $order_id, $user_info, $ticket_info, $payment_method, $order_status, $seat_meta );
						if ( $pid ) {
							$attendee_ids[] = $pid;
						}
						$seat_index++;
					}
				}
			}

			// Store attendee IDs on order so the payment-return handler can skip re-creation
			if ( ! empty( $attendee_ids ) ) {
				update_post_meta( $order_id, '_mep_attendee_ids', $attendee_ids );
			}

			// Confirmation email for completed orders
			if ( $order_status === 'completed' && ! empty( $attendee_ids ) && function_exists( 'mep_event_confirmation_email_sent' ) && function_exists( 'mep_should_send_billing_confirmation' ) && mep_should_send_billing_confirmation( 'completed' ) ) {
				mep_event_confirmation_email_sent( $event_id, $billing_email, $order_id, $attendee_ids[0] );
			}

			// The order's post status was set above, before the attendee records were
			// created, so the admin status-transition hook deliberately skips it. Fire
			// the status-changed action here, now that the attendees exist, so the PDF
			// Ticket addon can send a status-based PDF email (e.g. on free/completed
			// orders) — the same behaviour as WooCommerce on an order status change.
			if ( ! empty( $attendee_ids ) ) {
				do_action( 'mep_native_order_status_changed', $order_id, $order_status, 'pending' );
			}

			$redirect_url = $this->get_confirmation_url( $order_id, $order_status, $event_id );

			$message = ( $order_status === 'completed' )
				? __( 'Registration successful! A confirmation email has been sent.', 'mage-eventpress' )
				: __( 'Registration received! Please complete payment to confirm your spot.', 'mage-eventpress' );

			wp_send_json_success( array(
				'message'      => $message,
				'redirect'     => $redirect_url,
				'booking_id'   => $order_id,
				'status'       => $order_status,
				'attendee_ids' => $attendee_ids,
			) );
		}

		/**
		 * Returns the URL to redirect to after checkout.
		 * Uses the dedicated confirmation page when configured, otherwise falls back to the event page.
		 */
		private function get_confirmation_url( $order_id, $order_status, $event_id ) {
			$opts    = get_option( 'payment_setting_sec', array() );
			$page_id = ! empty( $opts['mep_confirmation_page_id'] ) ? absint( $opts['mep_confirmation_page_id'] ) : 0;
			$base    = ( $page_id && get_post_status( $page_id ) === 'publish' )
				? get_permalink( $page_id )
				: get_permalink( $event_id );

			return add_query_arg(
				array(
					'mep_booking'       => ( $order_status === 'completed' ) ? 'success' : 'pending',
					'mep_booking_id'    => $order_id,
					'mep_booking_token' => get_post_meta( $order_id, '_mep_booking_token', true ),
				),
				$base
			);
		}

		// -------------------------------------------------------------------------
		// Payment gateway return / cancel handling (runs on 'init')
		// -------------------------------------------------------------------------

		public function handle_payment_return() {
			// Payment successful — verify with gateway then create attendees
			if ( isset( $_GET['mep_payment_return'], $_GET['order_id'] ) ) {
				$order_id   = absint( $_GET['order_id'] );
				$gateway_id = isset( $_GET['gateway'] ) ? sanitize_text_field( $_GET['gateway'] ) : '';

				if ( get_post_type( $order_id ) !== 'mep_custom_order' ) {
					return;
				}

				// This callback only ever fires for a gateway-initiated redirect, which
				// requires PRO's gateway manager to have created it in the first place —
				// but guard anyway in case PRO was deactivated in between.
				$gateway = null;
				if ( class_exists( 'MEP_Payment_Gateway_Manager' ) ) {
					$gateway_manager = MEP_Payment_Gateway_Manager::get_instance();
					$gateway         = $gateway_manager->get_gateway( $gateway_id );
				}

				if ( $gateway && method_exists( $gateway, 'verify_payment' ) && $gateway->verify_payment( $order_id ) ) {
					wp_update_post( array(
						'ID'          => $order_id,
						'post_status' => 'publish',
					) );

					$event_id = get_post_meta( $order_id, '_mep_event_id', true );

					// Only create attendees if not already created (idempotency guard)
					$existing = get_post_meta( $order_id, '_mep_attendee_ids', true );
					if ( empty( $existing ) ) {
						$items          = (array) get_post_meta( $order_id, '_mep_order_items', true );
						$customer_name  = get_post_meta( $order_id, '_mep_customer_name', true );
						$customer_email = get_post_meta( $order_id, '_mep_customer_email', true );
						$customer_phone = get_post_meta( $order_id, '_mep_customer_phone', true );
						$event_date     = get_post_meta( $order_id, '_mep_event_date', true );
						$seat_metas     = (array) get_post_meta( $order_id, '_mep_attendee_extra_meta', true );
						// Per-seat list = numeric keys with array values; a legacy single
						// (flat) meta set is reused for every seat.
						$per_seat_metas = ( ! empty( $seat_metas ) && is_array( reset( $seat_metas ) ) );

						$user_info = array(
							'user_name'       => $customer_name,
							'user_email'      => $customer_email,
							'user_phone'      => $customer_phone,
							'user_event_date' => $event_date,
						);

						$attendee_ids = array();
						$seat_index   = 0;
						foreach ( $items as $item ) {
							$qty = isset( $item['qty'] ) ? absint( $item['qty'] ) : 0;
							if ( $qty <= 0 ) {
								continue;
							}
							// One attendee record per seat (quantity) — see complete_order().
							$ticket_info = array(
								'ticket_name'  => isset( $item['name'] )  ? $item['name']           : '',
								'ticket_qty'   => 1,
								'ticket_price' => isset( $item['price'] ) ? (float) $item['price']  : 0.0,
							);
							if ( function_exists( 'mep_native_ticket_attendee_create' ) ) {
								for ( $seat = 0; $seat < $qty; $seat++ ) {
									if ( $per_seat_metas ) {
										$seat_meta = ( isset( $seat_metas[ $seat_index ] ) && is_array( $seat_metas[ $seat_index ] ) ) ? $seat_metas[ $seat_index ] : array();
									} else {
										$seat_meta = $seat_metas; // legacy single meta applied to all seats
									}
									$pid = mep_native_ticket_attendee_create( $event_id, $order_id, $user_info, $ticket_info, $gateway_id, 'completed', $seat_meta );
									if ( $pid ) {
										$attendee_ids[] = $pid;
									}
									$seat_index++;
								}
							}
						}

						if ( ! empty( $attendee_ids ) ) {
							update_post_meta( $order_id, '_mep_attendee_ids', $attendee_ids );
							if ( function_exists( 'mep_event_confirmation_email_sent' ) && function_exists( 'mep_should_send_billing_confirmation' ) && mep_should_send_billing_confirmation( 'completed' ) ) {
								mep_event_confirmation_email_sent( $event_id, $customer_email, $order_id, $attendee_ids[0] );
							}
							// Attendees now exist, so let the PDF Ticket addon send a
							// status-based PDF email for this completed payment (mirrors
							// WooCommerce's behaviour on an order status change).
							do_action( 'mep_native_order_status_changed', $order_id, 'completed', 'pending' );
						}
					}

					wp_safe_redirect( $this->get_confirmation_url( $order_id, 'completed', $event_id ) );
					exit;
				}

				wp_die( esc_html__( 'Payment verification failed. Please contact us if you believe this is an error.', 'mage-eventpress' ) );
			}

			// Payment cancelled at gateway
			if ( isset( $_GET['mep_payment_cancel'], $_GET['order_id'] ) ) {
				$order_id = absint( $_GET['order_id'] );
				if ( get_post_type( $order_id ) !== 'mep_custom_order' ) {
					return;
				}

				/*
				 * SECURITY (IDOR): this runs on `init` for every front-end request and
				 * trashes a real registration, so the order id alone must never be enough
				 * to trigger it. mep_custom_order ids are global sequential post ids that
				 * every booker learns from their own confirmation redirect, which makes a
				 * neighbour's id guessable rather than secret.
				 *
				 * Three gates, all required: the caller must present the single-use key
				 * issued to the gateway for this payment attempt, the order must still be
				 * awaiting that payment, and the key is spent on use so the callback
				 * cannot be replayed.
				 */
				$presented_token = isset( $_GET[ self::CANCEL_TOKEN_ARG ] )
					? sanitize_text_field( wp_unslash( $_GET[ self::CANCEL_TOKEN_ARG ] ) )
					: '';
				$stored_token = (string) get_post_meta( $order_id, self::CANCEL_TOKEN_META, true );
				if ( '' === $presented_token || '' === $stored_token || ! hash_equals( $stored_token, $presented_token ) ) {
					return;
				}

				// Only an order still waiting on its payment may be cancelled this way. A
				// completed registration is settled: it is the admin's to refund or trash,
				// never a stray gateway redirect's.
				if ( 'pending' !== get_post_status( $order_id ) ) {
					return;
				}

				// One attempt, one cancellation.
				delete_post_meta( $order_id, self::CANCEL_TOKEN_META );

				wp_update_post( array(
					'ID'          => $order_id,
					'post_status' => 'trash',
				) );
				$event_id = get_post_meta( $order_id, '_mep_event_id', true );
				wp_safe_redirect( add_query_arg( 'mep_booking', 'cancelled', get_permalink( $event_id ) ) );
				exit;
			}
		}
	}
}
