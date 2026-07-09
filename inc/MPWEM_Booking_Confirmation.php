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

			require MPWEM_Functions::template_path( 'layout/booking_confirmation.php' );
		}
	}
	new MPWEM_Booking_Confirmation();
}
