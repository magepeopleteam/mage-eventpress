<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*
	* Follow-up work for a cancelled event, chosen by the organiser in the Event Status
	* card (MPWEM_Event_Status_Settings) and run in the background:
	*
	*   notify - emails everyone who booked (ticket attendees and RSVP responses), once
	*            per address per cancellation;
	*   refund - refunds this event's line items of paid WooCommerce orders through the
	*            order's gateway, cancels unpaid orders that hold nothing else, and adds
	*            an order note to every order it cannot settle automatically.
	*
	* Jobs run in small batches through Action Scheduler (WP-Cron when it is missing),
	* stop when the event is restored meanwhile, and record a result per recipient and
	* per order in mep_event_cancel_jobs, so a retry never mails or refunds twice.
	*
	* Also keeps "Pay for order" from taking money for a cancelled event.
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Event_Cancellation' ) ) {
		class MPWEM_Event_Cancellation {
			const JOB_HOOK     = 'mpwem_event_cancellation_job';
			const JOBS_META    = 'mep_event_cancel_jobs';
			const NOTIFIED_META = 'mep_event_cancel_notified';
			const NOTIFY_BATCH = 20;
			const REFUND_BATCH = 5;
			/** Order results that are final: never processed again. */
			const FINAL_RESULTS = array( 'refunded', 'manual', 'failed', 'cancelled_unpaid', 'mixed_unpaid', 'already_refunded', 'native_manual', 'skipped' );
			public function __construct() {
				add_action( self::JOB_HOOK, array( $this, 'run_job' ), 10, 2 );
				add_action( 'mpwem_event_restored', array( $this, 'reset_for_restored_event' ) );
				add_filter( 'woocommerce_order_needs_payment', array( $this, 'block_payment_for_cancelled_event' ), 20, 2 );
			}
			/*************** Queueing ***************/
			/**
			 * Starts a follow-up job for a cancelled event.
			 *
			 * @param int    $event_id Event post ID.
			 * @param string $task     'notify' or 'refund'.
			 * @param array  $args     For 'notify': subject and body templates.
			 */
			public static function queue( $event_id, $task, array $args = array() ): void {
				$event_id = absint( $event_id );
				if ( ! $event_id || ! in_array( $task, array( 'notify', 'refund' ), true ) || ! MPWEM_Global_Function::is_cancelled_event( $event_id ) ) {
					return;
				}
				if ( 'refund' === $task && ! function_exists( 'wc_get_order' ) ) {
					return;
				}
				$jobs = self::get_jobs( $event_id );
				$job  = isset( $jobs[ $task ] ) && is_array( $jobs[ $task ] ) ? $jobs[ $task ] : array();
				$job  = array_merge( $job, array(
					'status'     => 'queued',
					'queued_at'  => current_time( 'mysql' ),
					'updated_at' => current_time( 'mysql' ),
				) );
				if ( 'notify' === $task ) {
					$job['subject'] = isset( $args['subject'] ) ? (string) $args['subject'] : self::default_subject();
					$job['body']    = isset( $args['body'] ) ? (string) $args['body'] : self::default_body();
					// Taken now, not when the job runs: settling orders trashes the attendees of
					// refunded and cancelled bookings, and those people must still hear about it.
					$known             = isset( $job['recipients'] ) && is_array( $job['recipients'] ) ? $job['recipients'] : array();
					$job['recipients'] = $known + self::get_recipients( $event_id );
					$job['total']      = max( count( $job['recipients'] ), isset( $job['total'] ) ? (int) $job['total'] : 0 );
					$job['sent']       = isset( $job['sent'] ) ? (int) $job['sent'] : 0;
					$job['failed']     = array();
				} else {
					$job['orders'] = isset( $job['orders'] ) && is_array( $job['orders'] ) ? $job['orders'] : array();
					$job['total']  = count( self::get_order_ids( $event_id ) );
				}
				$jobs[ $task ] = $job;
				self::save_jobs( $event_id, $jobs );
				self::schedule( $event_id, $task );
			}
			private static function schedule( $event_id, $task ): void {
				$args = array( (int) $event_id, (string) $task );
				if ( function_exists( 'as_enqueue_async_action' ) ) {
					as_enqueue_async_action( self::JOB_HOOK, $args, 'mage-eventpress' );
					return;
				}
				if ( ! wp_next_scheduled( self::JOB_HOOK, $args ) ) {
					wp_schedule_single_event( time(), self::JOB_HOOK, $args );
				}
				if ( function_exists( 'spawn_cron' ) ) {
					spawn_cron();
				}
			}
			public static function get_jobs( $event_id ): array {
				$jobs = get_post_meta( $event_id, self::JOBS_META, true );
				return is_array( $jobs ) ? $jobs : array();
			}
			private static function save_jobs( $event_id, array $jobs ): void {
				update_post_meta( $event_id, self::JOBS_META, $jobs );
			}
			/**
			 * A new cancellation starts a new notification round; refund results are kept,
			 * because the money they record has really moved.
			 */
			public function reset_for_restored_event( $event_id ): void {
				delete_post_meta( $event_id, self::NOTIFIED_META );
				$jobs = self::get_jobs( $event_id );
				if ( isset( $jobs['notify'] ) ) {
					unset( $jobs['notify'] );
					self::save_jobs( $event_id, $jobs );
				}
			}
			/*************** Running ***************/
			public function run_job( $event_id, $task ): void {
				$event_id = absint( $event_id );
				$task     = (string) $task;
				$jobs     = self::get_jobs( $event_id );
				if ( ! $event_id || ! isset( $jobs[ $task ] ) ) {
					return;
				}
				if ( ! MPWEM_Global_Function::is_cancelled_event( $event_id ) ) {
					$jobs[ $task ]['status']     = 'aborted';
					$jobs[ $task ]['updated_at'] = current_time( 'mysql' );
					self::save_jobs( $event_id, $jobs );
					return;
				}
				// One runner per event and task; a duplicate trigger simply leaves.
				$lock = 'mpwem_cancel_job_' . $event_id . '_' . $task;
				if ( get_transient( $lock ) ) {
					return;
				}
				set_transient( $lock, 1, 5 * MINUTE_IN_SECONDS );
				try {
					$more = 'notify' === $task ? $this->run_notify_batch( $event_id ) : $this->run_refund_batch( $event_id );
				} finally {
					delete_transient( $lock );
				}
				if ( $more ) {
					self::schedule( $event_id, $task );
				}
			}
			/**
			 * Sends the next batch of cancellation emails.
			 *
			 * @return bool Whether recipients are left for another run.
			 */
			private function run_notify_batch( $event_id ): bool {
				$jobs       = self::get_jobs( $event_id );
				$job        = $jobs['notify'];
				$recipients = isset( $job['recipients'] ) && is_array( $job['recipients'] ) ? $job['recipients'] : array();
				if ( ! $recipients ) {
					// Nobody to mail, or finished earlier: the list is dropped once everyone was mailed.
					if ( 'done' !== ( $job['status'] ?? '' ) ) {
						$job['status']     = 'done';
						$job['updated_at'] = current_time( 'mysql' );
						$jobs['notify']    = $job;
						self::save_jobs( $event_id, $jobs );
					}
					return false;
				}
				// Only hashes are kept of who was mailed, and the list goes when the job ends, so
				// no copy of an address outlives the attendee record it came from.
				$notified = get_post_meta( $event_id, self::NOTIFIED_META, true );
				$notified = is_array( $notified ) ? $notified : array();
				$pending  = array();
				foreach ( $recipients as $email => $name ) {
					if ( ! isset( $notified[ md5( $email ) ] ) ) {
						$pending[ $email ] = $name;
					}
				}
				$batch  = array_slice( $pending, 0, self::NOTIFY_BATCH, true );
				$failed = isset( $job['failed'] ) && is_array( $job['failed'] ) ? $job['failed'] : array();
				foreach ( $batch as $email => $name ) {
					// Marked before sending: a mail server that accepts the message and then
					// times out must not cause a second copy on the next run.
					$notified[ md5( $email ) ] = current_time( 'mysql' );
					update_post_meta( $event_id, self::NOTIFIED_META, $notified );
					if ( self::send_email( $event_id, $email, $name, $job ) ) {
						$job['sent'] = ( isset( $job['sent'] ) ? (int) $job['sent'] : 0 ) + 1;
					} else {
						$failed[] = $email;
					}
				}
				$more              = count( $pending ) > count( $batch );
				$job['failed']     = array_values( array_unique( $failed ) );
				$job['total']      = count( $recipients );
				$job['status']     = $more ? 'running' : 'done';
				$job['updated_at'] = current_time( 'mysql' );
				if ( ! $more ) {
					unset( $job['recipients'] );
				}
				$jobs['notify'] = $job;
				self::save_jobs( $event_id, $jobs );
				return $more;
			}
			/**
			 * Settles the next batch of orders.
			 *
			 * @return bool Whether orders are left for another run.
			 */
			private function run_refund_batch( $event_id ): bool {
				$jobs    = self::get_jobs( $event_id );
				$job     = $jobs['refund'];
				$results = isset( $job['orders'] ) && is_array( $job['orders'] ) ? $job['orders'] : array();
				$pending = array();
				foreach ( self::get_order_ids( $event_id ) as $order_id ) {
					if ( ! isset( $results[ $order_id ] ) || ! in_array( $results[ $order_id ], self::FINAL_RESULTS, true ) ) {
						$pending[] = $order_id;
					}
				}
				$batch = array_slice( $pending, 0, self::REFUND_BATCH );
				foreach ( $batch as $order_id ) {
					$results[ $order_id ] = self::settle_order( $order_id, $event_id );
					// Saved after every order, so an interrupted run never repeats one.
					$job['orders']     = $results;
					$jobs['refund']    = $job;
					self::save_jobs( $event_id, $jobs );
				}
				$more              = count( $pending ) > count( $batch );
				$job['orders']     = $results;
				$job['total']      = count( $results ) + count( $pending ) - count( $batch );
				$job['status']     = $more ? 'running' : 'done';
				$job['updated_at'] = current_time( 'mysql' );
				$jobs['refund']    = $job;
				self::save_jobs( $event_id, $jobs );
				return $more;
			}
			/*************** Recipients and orders ***************/
			/**
			 * Everyone who booked: ticket attendees whose order still stands, plus RSVP
			 * responses. Keyed by lower-cased email, value is the name to greet.
			 *
			 * @return array<string, string>
			 */
			public static function get_recipients( $event_id ): array {
				$recipients = array();
				$inactive   = array( 'cancelled', 'refunded', 'failed', 'trash', 'checkout-draft' );
				foreach ( array( 'mep_events_attendees', 'mep_rsvp_responses' ) as $post_type ) {
					$ids = self::get_booking_post_ids( $post_type, $event_id );
					update_postmeta_cache( $ids );
					foreach ( $ids as $id ) {
						if ( in_array( (string) get_post_meta( $id, 'ea_order_status', true ), $inactive, true ) ) {
							continue;
						}
						$email = trim( (string) get_post_meta( $id, 'ea_email', true ) );
						$name  = trim( (string) get_post_meta( $id, 'ea_name', true ) );
						$order_id = (int) get_post_meta( $id, 'ea_order_id', true );
						// Attendee forms do not always ask for an email; the buyer is told instead.
						if ( ! is_email( $email ) && $order_id && function_exists( 'wc_get_order' ) ) {
							$order = wc_get_order( $order_id );
							if ( $order instanceof WC_Order ) {
								$email = (string) $order->get_billing_email();
								$name  = $name ?: trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
							}
						}
						if ( ! is_email( $email ) ) {
							continue;
						}
						$key = strtolower( $email );
						if ( ! isset( $recipients[ $key ] ) ) {
							$recipients[ $key ] = $name;
						}
					}
				}
				return (array) apply_filters( 'mpwem_cancellation_recipients', $recipients, $event_id );
			}
			/**
			 * Orders that booked the event, from its attendee records.
			 *
			 * @return int[]
			 */
			public static function get_order_ids( $event_id ): array {
				$ids = self::get_booking_post_ids( 'mep_events_attendees', $event_id );
				update_postmeta_cache( $ids );
				$orders = array();
				foreach ( $ids as $id ) {
					$order_id = (int) get_post_meta( $id, 'ea_order_id', true );
					if ( $order_id ) {
						$orders[ $order_id ] = $order_id;
					}
				}
				sort( $orders );
				return array_values( $orders );
			}
			private static function get_booking_post_ids( $post_type, $event_id ): array {
				return get_posts( array(
					'post_type'        => $post_type,
					'post_status'      => 'any',
					'numberposts'      => -1,
					'fields'           => 'ids',
					'suppress_filters' => true,
					'meta_key'         => 'ea_event_id',
					'meta_value'       => (int) $event_id,
				) );
			}
			/*************** Orders ***************/
			/**
			 * Refunds or cancels one order's part in the cancelled event.
			 *
			 * @return string Result code, see FINAL_RESULTS.
			 */
			public static function settle_order( $order_id, $event_id ): string {
				if ( 'mep_custom_order' === get_post_type( $order_id ) ) {
					return 'native_manual'; // Native checkout order: refunded by hand in the gateway.
				}
				$order = wc_get_order( $order_id );
				if ( ! $order instanceof WC_Order || $order instanceof WC_Order_Refund ) {
					return 'skipped';
				}
				$event_items = array();
				$other_items = false;
				foreach ( $order->get_items() as $item_id => $item ) {
					if ( (int) $item->get_meta( 'event_id' ) === (int) $event_id ) {
						$event_items[ $item_id ] = $item;
					} else {
						$other_items = true;
					}
				}
				$title = get_the_title( $event_id );
				if ( ! $event_items || $order->has_status( array( 'cancelled', 'refunded', 'failed', 'trash' ) ) ) {
					return 'skipped';
				}
				// Only orders that never took money are cancelled. On-hold can mean "paid,
				// awaiting review" and a deposit's partially-paid holds real money, so those
				// are left for the organiser instead of being cancelled or over-refunded.
				if ( $order->has_status( array( 'pending', 'checkout-draft' ) ) ) {
					if ( $other_items ) {
						/* translators: %s: event name. */
						$order->add_order_note( sprintf( __( '%s was cancelled. This unpaid order also holds other items, so it was left open: remove the event from it or cancel it.', 'mage-eventpress' ), $title ) );
						return 'mixed_unpaid';
					}
					/* translators: %s: event name. */
					$order->update_status( 'cancelled', sprintf( __( 'Cancelled because the event %s was cancelled.', 'mage-eventpress' ), $title ) );
					return 'cancelled_unpaid';
				}
				if ( ! $order->is_paid() ) {
					$order->add_order_note( sprintf(
					/* translators: 1: event name, 2: order status. */
						__( '%1$s was cancelled. This order is "%2$s", so it was not refunded or cancelled automatically: please settle it by hand.', 'mage-eventpress' ),
						$title,
						wc_get_order_status_name( $order->get_status() )
					) );
					return 'manual';
				}
				$line_items = array();
				$amount     = 0.0;
				foreach ( $event_items as $item_id => $item ) {
					$total      = max( 0, (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item_id ) );
					$refund_tax = array();
					$taxes      = $item->get_taxes();
					foreach ( isset( $taxes['total'] ) && is_array( $taxes['total'] ) ? $taxes['total'] : array() as $tax_id => $tax_total ) {
						$left = (float) $tax_total - (float) $order->get_tax_refunded_for_item( $item_id, $tax_id );
						if ( $left > 0 ) {
							$refund_tax[ $tax_id ] = wc_format_decimal( $left );
							$amount               += $left;
						}
					}
					if ( $total > 0 || $refund_tax ) {
						$amount               += $total;
						$line_items[ $item_id ] = array(
							'qty'          => max( 0, $item->get_quantity() - abs( (int) $order->get_qty_refunded_for_item( $item_id ) ) ),
							'refund_total' => wc_format_decimal( $total ),
							'refund_tax'   => $refund_tax,
						);
					}
				}
				$amount = min( round( $amount, wc_get_price_decimals() ), (float) $order->get_remaining_refund_amount() );
				if ( $amount <= 0 ) {
					return 'already_refunded';
				}
				$gateway    = wc_get_payment_gateway_by_order( $order );
				$can_refund = $gateway && $gateway->supports( 'refunds' ) && ( ! method_exists( $gateway, 'can_refund_order' ) || $gateway->can_refund_order( $order ) );
				if ( ! $can_refund ) {
					$order->add_order_note( sprintf(
					/* translators: 1: event name, 2: amount. */
						__( '%1$s was cancelled. Refund %2$s for its tickets manually: this payment method cannot refund automatically.', 'mage-eventpress' ),
						$title,
						wp_strip_all_tags( wc_price( $amount, array( 'currency' => $order->get_currency() ) ) )
					) );
					return 'manual';
				}
				$refund = wc_create_refund( array(
					'amount'         => $amount,
					/* translators: %s: event name. */
					'reason'         => sprintf( __( 'Event cancelled: %s', 'mage-eventpress' ), $title ),
					'order_id'       => $order->get_id(),
					'line_items'     => $line_items,
					'refund_payment' => true,
					'restock_items'  => false,
				) );
				if ( is_wp_error( $refund ) ) {
					$order->add_order_note( sprintf(
					/* translators: 1: event name, 2: error message. */
						__( 'The automatic refund for the cancelled event %1$s failed: %2$s Please refund it manually.', 'mage-eventpress' ),
						$title,
						$refund->get_error_message()
					) );
					return 'failed';
				}
				self::release_event_bookings( $order, $event_id, $event_items );
				return 'refunded';
			}
			/**
			 * Takes refunded tickets out of the seat count, for this event only.
			 *
			 * A full refund moves the order to "refunded" and order_status_changed() does this
			 * for the whole order. A refund of one event in a larger order leaves the status
			 * alone, so without this the refunded tickets would still hold seats if the event
			 * is restored later. Same treatment as the full-refund path: trashed and marked.
			 */
			private static function release_event_bookings( $order, $event_id, array $event_items ): void {
				$order_id = $order->get_id();
				$bookings = array(
					'mep_events_attendees' => array( 'ea_order_id', 'ea_event_id', array( 'ea_order_status', 'ea_flag' ) ),
					'mep_extra_service'    => array( 'ea_extra_service_order', 'ea_extra_service_event', array( 'ea_extra_service_order_status' ) ),
				);
				foreach ( $bookings as $post_type => $keys ) {
					$ids = get_posts( array(
						'post_type'        => $post_type,
						'post_status'      => 'any',
						'numberposts'      => -1,
						'fields'           => 'ids',
						'suppress_filters' => true,
						'meta_query'       => array(
							array( 'key' => $keys[0], 'value' => $order_id, 'compare' => '=' ),
							array( 'key' => $keys[1], 'value' => (int) $event_id, 'compare' => '=' ),
						),
					) );
					foreach ( $ids as $id ) {
						foreach ( $keys[2] as $status_key ) {
							update_post_meta( $id, $status_key, 'refunded' );
						}
						wp_update_post( array( 'ID' => $id, 'post_status' => 'trash' ) );
					}
				}
				foreach ( $event_items as $item ) {
					$ticket_info = $item->get_meta( '_event_ticket_info' );
					if ( is_array( $ticket_info ) && function_exists( 'mep_update_event_seat_inventory' ) ) {
						mep_update_event_seat_inventory( $event_id, $ticket_info );
					}
				}
			}
			/**
			 * Keeps "Pay for order" from charging for a cancelled event.
			 *
			 * The order-pay page, its form handler, the Store API order-pay route and the
			 * My Account "Pay" button all ask needs_payment() before anything else. The
			 * checkout itself asks it after woocommerce_checkout_order_processed /
			 * woocommerce_store_api_checkout_order_processed to choose between charging
			 * and completing the order for free, so the answer is left alone once either
			 * has fired: a checkout can never become a free order through this filter.
			 * (Cancelled lines never reach a new order anyway: check_cart_items removes them.)
			 *
			 * @param bool     $needs_payment Whether the order needs payment.
			 * @param WC_Order $order         Order.
			 * @return bool
			 */
			public function block_payment_for_cancelled_event( $needs_payment, $order ) {
				if ( ! $needs_payment || ! $order instanceof WC_Order || did_action( 'woocommerce_checkout_order_processed' ) || did_action( 'woocommerce_store_api_checkout_order_processed' ) ) {
					return $needs_payment;
				}
				foreach ( $order->get_items() as $item ) {
					$event_id = (int) $item->get_meta( 'event_id' );
					if ( $event_id && MPWEM_Global_Function::is_cancelled_event( $event_id ) ) {
						return false;
					}
				}
				return $needs_payment;
			}
			/*************** Email ***************/
			public static function default_subject(): string {
				return __( 'Cancelled: {event_name}', 'mage-eventpress' );
			}
			public static function default_body(): string {
				return __( "Hello {attendee_name},\n\nWe are sorry to let you know that {event_name}, scheduled for {event_date}, has been cancelled.\n\n{cancel_message}\n\nIf you paid for your booking, your refund is on its way or we will be in touch about it shortly.\n\n{site_name}", 'mage-eventpress' );
			}
			/**
			 * Placeholder values for one recipient.
			 */
			public static function get_placeholders( $event_id, $name = '' ): array {
				$start = (string) get_post_meta( $event_id, 'event_start_datetime', true );
				$notice = MPWEM_Global_Function::get_cancellation_notice( $event_id );
				return array(
					'{attendee_name}'  => '' !== trim( (string) $name ) ? $name : __( 'there', 'mage-eventpress' ),
					'{event_name}'     => get_the_title( $event_id ),
					'{event_date}'     => $start ? MPWEM_Global_Function::date_format( $start, 'full', $event_id ) : '',
					'{event_url}'      => get_permalink( $event_id ),
					'{cancel_message}' => $notice['message'],
					'{site_name}'      => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				);
			}
			private static function send_email( $event_id, $email, $name, array $job ): bool {
				$vars    = self::get_placeholders( $event_id, $name );
				$subject = wp_strip_all_tags( strtr( (string) ( $job['subject'] ?? self::default_subject() ), $vars ) );
				$text    = strtr( (string) ( $job['body'] ?? self::default_body() ), array_map( 'wp_kses_post', $vars ) );
				$body    = self::wrap_email( wp_kses_post( wpautop( $text ) ), $event_id );
				$from_email = mep_get_option( 'mep_email_form_email', 'email_setting_sec', '' );
				$from_name  = mep_get_option( 'mep_email_form_name', 'email_setting_sec', '' );
				$from_email = is_email( $from_email ) ? $from_email : get_option( 'admin_email' );
				$from_name  = '' !== trim( (string) $from_name ) ? $from_name : wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
				$headers    = array(
					'From: ' . wp_specialchars_decode( $from_name, ENT_QUOTES ) . ' <' . $from_email . '>',
					'Content-Type: text/html; charset=UTF-8',
				);
				$mail = (array) apply_filters( 'mpwem_cancellation_email', array(
					'to'      => $email,
					'subject' => $subject,
					'message' => $body,
					'headers' => $headers,
				), $event_id, $name );
				if ( empty( $mail['to'] ) ) {
					return false;
				}
				return (bool) wp_mail( $mail['to'], $mail['subject'], $mail['message'], $mail['headers'] );
			}
			private static function wrap_email( $content, $event_id ): string {
				$title = esc_html( get_the_title( $event_id ) );
				$label = esc_html__( 'Event cancelled', 'mage-eventpress' );
				return '<!DOCTYPE html><html><body style="margin:0;padding:24px;background:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;color:#1f2937;">'
					. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">'
					. '<tr><td style="padding:20px 28px;border-bottom:4px solid #dc2626;"><span style="display:inline-block;padding:3px 10px;border-radius:999px;background:#fef2f2;color:#991b1b;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">' . $label . '</span>'
					. '<h1 style="margin:10px 0 0;font-size:20px;line-height:1.35;color:#111827;">' . $title . '</h1></td></tr>'
					. '<tr><td style="padding:24px 28px;font-size:15px;line-height:1.65;">' . $content . '</td></tr>'
					. '</table></body></html>';
			}
		}
		new MPWEM_Event_Cancellation();
	}
