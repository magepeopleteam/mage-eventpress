<?php
/*
 * @Author      engr.sumonazma@gmail.com
 * Copyright:   mage-people.com
 *
 * Native checkout handler — processes event registrations through the custom payment flow,
 * used when WooCommerce is not active OR the "Enable WooCommerce Payment" setting is disabled.
 * Creates mep_events_attendees records directly using mep_native_ticket_attendee_create().
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'MPWEM_Native_Checkout' ) ) {
	class MPWEM_Native_Checkout {

		public function __construct() {
			add_action( 'wp_ajax_mep_native_checkout', array( $this, 'process_checkout' ) );
			add_action( 'wp_ajax_nopriv_mep_native_checkout', array( $this, 'process_checkout' ) );
		}

		public function process_checkout() {
			// 1. Nonce verification
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_native_checkout_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'mage-eventpress' ) ) );
			}

			// 2. Validate required fields
			$event_id     = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
			$billing_name  = isset( $_POST['billing_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_name'] ) ) : '';
			$billing_email = isset( $_POST['billing_email'] ) ? sanitize_email( wp_unslash( $_POST['billing_email'] ) ) : '';
			$billing_phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : '';
			$event_date    = isset( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : '';
			$ticket_data   = isset( $_POST['ticket_data'] ) ? wp_unslash( $_POST['ticket_data'] ) : '';
			$attendee_data = isset( $_POST['attendee_fields'] ) ? wp_unslash( $_POST['attendee_fields'] ) : '';

			if ( ! $event_id ) {
				wp_send_json_error( array( 'message' => __( 'Invalid event.', 'mage-eventpress' ) ) );
			}
			// billing_name and billing_email come from the event's Attendee Form fields (if enabled).
			// If the admin has not enabled a Name or Email field the values will be empty — that is
			// a valid admin choice (e.g. anonymous RSVP), so we do not hard-reject here.
			// We do validate the email format when one is provided, and we fall back to
			// extracting it from the attendee_fields payload if the JS sent it there.
			if ( ! $billing_email && $attendee_data ) {
				$_af = json_decode( $attendee_data, true );
				if ( is_array( $_af ) && ! empty( $_af['user_email'] ) ) {
					$billing_email = sanitize_email( $_af['user_email'] );
				}
			}
			if ( $billing_email && ! is_email( $billing_email ) ) {
				wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'mage-eventpress' ) ) );
			}

			// 3. Decode ticket data
			$tickets = array();
			if ( $ticket_data ) {
				$tickets = json_decode( $ticket_data, true );
			}
			if ( ! is_array( $tickets ) || empty( $tickets ) ) {
				wp_send_json_error( array( 'message' => __( 'Please select at least one ticket.', 'mage-eventpress' ) ) );
			}

			// 4. Validate each ticket has a positive quantity
			$has_qty = false;
			foreach ( $tickets as $ticket ) {
				$qty = isset( $ticket['ticket_qty'] ) ? absint( $ticket['ticket_qty'] ) : 0;
				if ( $qty > 0 ) {
					$has_qty = true;
					break;
				}
			}
			if ( ! $has_qty ) {
				wp_send_json_error( array( 'message' => __( 'Please select at least one ticket.', 'mage-eventpress' ) ) );
			}

			// 5. Capacity check
			$availability_error = $this->check_availability( $event_id, $tickets, $event_date );
			if ( $availability_error ) {
				wp_send_json_error( array( 'message' => $availability_error ) );
			}

			// 6. Calculate total
			$total = 0.0;
			foreach ( $tickets as $ticket ) {
				$qty   = isset( $ticket['ticket_qty'] ) ? absint( $ticket['ticket_qty'] ) : 0;
				$price = isset( $ticket['ticket_price'] ) ? (float) $ticket['ticket_price'] : 0.0;
				$total += $price * $qty;
			}

			// 7. Determine payment path and booking status
			$payment_opts    = get_option( 'payment_setting_sec', array() );
			$paypal_enabled  = ! empty( $payment_opts['mep_paypal_enable'] ) && $payment_opts['mep_paypal_enable'] === 'on';
			$stripe_enabled  = ! empty( $payment_opts['mep_stripe_enable'] ) && $payment_opts['mep_stripe_enable'] === 'on';
			$offline_enabled = ! empty( $payment_opts['mep_offline_enable'] ) && $payment_opts['mep_offline_enable'] === 'on';

			// The customer's chosen method from the modal. Only honoured when that method
			// is actually enabled; otherwise we fall back to the first available gateway.
			$selected_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';

			if ( $total <= 0 ) {
				$payment_method = 'free';
				$order_status   = 'completed';
			} elseif ( $selected_method === 'paypal' && $paypal_enabled ) {
				$payment_method = 'paypal';
				$order_status   = 'pending';
			} elseif ( $selected_method === 'stripe' && $stripe_enabled ) {
				$payment_method = 'stripe';
				$order_status   = 'pending';
			} elseif ( $selected_method === 'offline' && $offline_enabled ) {
				$payment_method = 'offline';
				$order_status   = 'pending';
			} elseif ( $paypal_enabled ) {
				$payment_method = 'paypal';
				$order_status   = 'pending';
			} elseif ( $stripe_enabled ) {
				$payment_method = 'stripe';
				$order_status   = 'pending';
			} else {
				// Offline / pay-later fallback (also covers the "no gateway configured" notice).
				$payment_method = 'offline';
				$order_status   = 'pending';
			}

			// 8. Generate a native booking reference
			$booking_id = time() . rand( 100, 999 );

			// 9. User info array passed to attendee creation
			$user_info = array(
				'user_name'       => $billing_name,
				'user_email'      => $billing_email,
				'user_phone'      => $billing_phone,
				'user_event_date' => $event_date,
			);

			// 9b. Map any posted form-builder attendee fields to ea_* meta
			$extra_meta = array();
			if ( $attendee_data && function_exists( 'mep_collect_attendee_form_fields' ) ) {
				$posted_fields = json_decode( $attendee_data, true );
				$extra_meta    = mep_collect_attendee_form_fields( $event_id, $posted_fields );
			}

			// 10. Create attendees for each ticket type
			$attendee_ids = array();
			foreach ( $tickets as $ticket ) {
				$qty = isset( $ticket['ticket_qty'] ) ? absint( $ticket['ticket_qty'] ) : 0;
				if ( $qty <= 0 ) {
					continue;
				}
				$ticket_info = array(
					'ticket_name'  => isset( $ticket['ticket_name'] ) ? sanitize_text_field( $ticket['ticket_name'] ) : '',
					'ticket_qty'   => $qty,
					'ticket_price' => isset( $ticket['ticket_price'] ) ? (float) $ticket['ticket_price'] : 0.0,
				);
				$pid = mep_native_ticket_attendee_create( $event_id, $booking_id, $user_info, $ticket_info, $payment_method, $order_status, $extra_meta );
				if ( $pid ) {
					$attendee_ids[] = $pid;
				}
			}

			if ( empty( $attendee_ids ) ) {
				wp_send_json_error( array( 'message' => __( 'Registration could not be completed. Please try again.', 'mage-eventpress' ) ) );
			}

			// 11. Send confirmation email for completed bookings (only when we have an email address).
			if ( $order_status === 'completed' && $billing_email && function_exists( 'mep_event_confirmation_email_sent' ) ) {
				mep_event_confirmation_email_sent( $event_id, $billing_email, $booking_id, $attendee_ids[0] );
			}

			// 12. Build response
			$redirect_url = $this->get_thank_you_url( $event_id, $order_status, $booking_id );

			if ( $order_status === 'completed' ) {
				$message = __( 'Registration successful! A confirmation email has been sent to you.', 'mage-eventpress' );
			} else {
				$message = __( 'Registration received! Please follow up with payment to confirm your spot.', 'mage-eventpress' );
			}

			wp_send_json_success( array(
				'message'     => $message,
				'redirect'    => $redirect_url,
				'booking_id'  => $booking_id,
				'status'      => $order_status,
				'attendee_ids' => $attendee_ids,
			) );
		}

		/**
		 * Check ticket availability for each ticket type in the submission.
		 *
		 * @return string|null  Error message, or null if all tickets are available.
		 */
		private function check_availability( $event_id, $tickets, $event_date ) {
			$enable_global_qty = get_post_meta( $event_id, 'enable_global_qty', true );

			if ( $enable_global_qty === 'on' ) {
				$total_requested = 0;
				foreach ( $tickets as $ticket ) {
					$total_requested += isset( $ticket['ticket_qty'] ) ? absint( $ticket['ticket_qty'] ) : 0;
				}

				if ( function_exists( 'mep_event_total_seat' ) && function_exists( 'mep_ticket_type_sold' ) ) {
					$total_seat    = (int) MPWEM_Functions::get_total_ticket( $event_id, $event_date );
					$total_sold    = (int) mep_ticket_type_sold( $event_id, '', $event_date );
					$total_reserve = (int) MPWEM_Functions::get_reserve_ticket( $event_id, $event_date );
					$available     = $total_seat - ( $total_sold + $total_reserve );

					if ( $total_requested > $available ) {
						return sprintf(
							/* translators: %d: available seats */
							__( 'Not enough seats available. Only %d seat(s) left.', 'mage-eventpress' ),
							max( 0, $available )
						);
					}
				}
			}

			return null;
		}

		/**
		 * Returns the URL to redirect to after native checkout.
		 */
		private function get_thank_you_url( $event_id, $order_status, $booking_id ) {
			$event_url = get_permalink( $event_id );
			$status    = $order_status === 'completed' ? 'success' : 'pending';
			return add_query_arg(
				array(
					'mep_booking'    => $status,
					'mep_booking_id' => $booking_id,
				),
				$event_url
			);
		}
	}
	new MPWEM_Native_Checkout();
}
