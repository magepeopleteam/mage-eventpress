<?php
	/**
	 * Lets the admin turn off WooCommerce's own order emails for event bookings,
	 * so buyers get only the EventPress confirmation / PDF ticket emails.
	 *
	 * Setting: Email Settings > Confirmation Email > "Disable WooCommerce Emails
	 * for Event Orders" (email_setting_sec[mep_disable_wc_emails]).
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_WC_Email_Control' ) ) {
		class MPWEM_WC_Email_Control {

			/**
			 * WooCommerce emails sent automatically on an order status change.
			 * Manual ones (invoice, customer note) and refund mails stay untouched.
			 */
			const EMAIL_IDS = array( 'new_order', 'customer_on_hold_order', 'customer_processing_order', 'customer_completed_order' );

			/**
			 * True while an admin is resending an order email by hand.
			 *
			 * @var bool
			 */
			private static $manual_resend = false;

			public static function init() {
				foreach ( self::EMAIL_IDS as $email_id ) {
					add_filter( 'woocommerce_email_enabled_' . $email_id, array( __CLASS__, 'filter_email_enabled' ), 20, 3 );
				}
				// "Resend new order notification" is an explicit admin request; honour it.
				add_action( 'woocommerce_before_resend_order_emails', array( __CLASS__, 'start_manual_resend' ) );
				add_action( 'woocommerce_after_resend_order_email', array( __CLASS__, 'end_manual_resend' ) );
			}

			/**
			 * Labels for the settings screen, keyed by WooCommerce email ID.
			 *
			 * @return array
			 */
			public static function get_email_choices() {
				return array(
					'new_order'                 => __( 'New order (to admin)', 'mage-eventpress' ),
					'customer_on_hold_order'    => __( 'Order on-hold', 'mage-eventpress' ),
					'customer_processing_order' => __( 'Processing order', 'mage-eventpress' ),
					'customer_completed_order'  => __( 'Completed order', 'mage-eventpress' ),
				);
			}

			public static function start_manual_resend() {
				self::$manual_resend = true;
			}

			public static function end_manual_resend() {
				self::$manual_resend = false;
			}

			/**
			 * @param bool          $enabled Whether WooCommerce would send the email.
			 * @param mixed         $object  The order the email is about (null on the settings list).
			 * @param WC_Email|null $email   Email instance.
			 * @return bool
			 */
			public static function filter_email_enabled( $enabled, $object = null, $email = null ) {
				if ( ! $enabled || self::$manual_resend || ! $object instanceof WC_Order || ! $email instanceof WC_Email ) {
					return $enabled;
				}
				if ( ! in_array( $email->id, mep_get_disabled_wc_emails(), true ) || ! self::is_event_only_order( $object ) ) {
					return $enabled;
				}

				/**
				 * Filters whether a disabled WooCommerce email still goes out for an event order.
				 *
				 * @param bool     $enabled  False: the admin disabled this email for event orders.
				 * @param string   $email_id WooCommerce email ID.
				 * @param WC_Order $order    The order.
				 */
				return (bool) apply_filters( 'mep_wc_email_enabled_for_event_order', false, $email->id, $object );
			}

			/**
			 * Whether every product line of the order is an event booking.
			 *
			 * Orders that also carry other products keep their WooCommerce emails,
			 * because those are the buyer's only receipt for the other products.
			 *
			 * @param WC_Order $order Order.
			 * @return bool
			 */
			private static function is_event_only_order( $order ) {
				$items = $order->get_items();
				if ( empty( $items ) ) {
					return false;
				}
				foreach ( $items as $item ) {
					$event_id = absint( $item->get_meta( 'event_id', true ) );
					if ( ! $event_id || 'mep_events' !== get_post_type( $event_id ) ) {
						return false;
					}
				}

				return true;
			}
		}
	}
