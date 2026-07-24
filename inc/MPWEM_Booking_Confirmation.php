<?php
/*
 * @Author      engr.sumonazma@gmail.com
 * Copyright:   mage-people.com
 *
 * Renders a booking confirmation/cancellation notice on the event page after a native
 * (non-WooCommerce) checkout redirects back with ?mep_booking=success|pending|cancelled.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'MPWEM_Booking_Confirmation' ) ) {
	class MPWEM_Booking_Confirmation {

		public function __construct() {
			// Priority 5 so the notice appears above the registration form (added at priority 10).
			add_action( 'mpwem_registration', array( $this, 'render' ), 5, 1 );
		}

		public function render( $event_id ) {
			$event_id = absint( $event_id );
			if ( ! $event_id ) {
				return;
			}

			$booking_status = isset( $_GET['mep_booking'] ) ? sanitize_key( wp_unslash( $_GET['mep_booking'] ) ) : '';
			if ( ! in_array( $booking_status, array( 'success', 'pending', 'cancelled' ), true ) ) {
				return;
			}

			$booking_id = isset( $_GET['mep_booking_id'] ) ? sanitize_text_field( wp_unslash( $_GET['mep_booking_id'] ) ) : '';

			// This is the fallback destination MEP_Pro_Native_Checkout::get_confirmation_url()
			// redirects to when no dedicated confirmation page is configured, so it carries
			// the same mep_booking_token. Deny by default: the template below looks up
			// attendees purely by ea_order_id/ea_event_id meta match, with no ownership
			// check of its own, so an unverified mep_booking_id (native order, WooCommerce
			// order, or any other guessed value) must never reach it — otherwise anyone
			// could view another customer's name/email/phone/order details by loading this
			// same event URL with a guessed ID.
			$token_ok = false;
			if ( $booking_id && get_post_type( $booking_id ) === 'mep_custom_order' ) {
				$token        = isset( $_GET['mep_booking_token'] ) ? sanitize_text_field( wp_unslash( $_GET['mep_booking_token'] ) ) : '';
				$stored_token = get_post_meta( $booking_id, '_mep_booking_token', true );
				$token_ok     = $stored_token && hash_equals( $stored_token, $token );
			}
			if ( ! $token_ok ) {
				$booking_id = '';
			}

			require MPWEM_Functions::template_path( 'layout/booking_confirmation.php' );
		}
	}
	new MPWEM_Booking_Confirmation();
}
