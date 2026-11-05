<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*
	* Event Status: lets an organiser cancel an event without deleting or hiding it.
	*
	* Meta written here:
	*   mep_event_cancelled      'yes' | 'no'
	*   mep_event_cancel_message optional visitor notice (wp_kses_post); kept when the
	*                            event is restored, so cancelling again reuses it
	*   mep_event_cancelled_at   local MySQL datetime of the latest cancellation
	*   mep_event_cancel_email_subject, mep_event_cancel_email_body  email template
	*
	* Emailing attendees and settling orders run in MPWEM_Event_Cancellation.
	*
	* The same fields render in the Modern editor (a card on the Ticket & Pricing step,
	* see MPWEM_Event_Edit_Page) and the Classic editor (a side meta box). Both post
	* mep_event_status_present, and the save only runs when it is there, so Quick Edit,
	* the frontend submit addon and other writers never reset the status.
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Event_Status_Settings' ) ) {
		class MPWEM_Event_Status_Settings {
			const NONCE_ACTION = 'mep_event_status_save';
			const NONCE_NAME   = 'mep_event_status_nonce';
			public function __construct() {
				add_action( 'add_meta_boxes_mep_events', array( $this, 'add_meta_box' ) );
				add_action( 'save_post_mep_events', array( $this, 'save' ), 20 );
				add_filter( 'display_post_states', array( $this, 'post_states' ), 10, 2 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 95 );
			}
			public function add_meta_box() {
				add_meta_box( 'mpwem_event_status', esc_html__( 'Event Status', 'mage-eventpress' ), array( $this, 'render_meta_box' ), 'mep_events', 'side', 'high' );
			}
			public function render_meta_box( $post ) {
				self::render_fields( (int) $post->ID );
			}
			/**
			 * Whether the event is saved as cancelled.
			 *
			 * Reads the meta directly rather than MPWEM_Global_Function::is_cancelled_event():
			 * that one passes through the mpwem_is_cancelled_event filter, and drawing the
			 * switch from a filtered value would let one save overwrite the organiser's choice.
			 */
			private static function is_saved_cancelled( $post_id ): bool {
				return 'yes' === get_post_meta( $post_id, 'mep_event_cancelled', true );
			}
			/**
			 * Bookings already made for the event: ticket attendees in a seat-holding order
			 * status plus RSVP responses. Shown so the organiser knows who to contact.
			 */
			public static function get_registration_count( $post_id ): int {
				$count = function_exists( 'mep_ticket_type_sold' ) ? (int) mep_ticket_type_sold( $post_id ) : 0;
				$rsvp  = new WP_Query( array(
					'post_type'              => 'mep_rsvp_responses',
					'post_status'            => 'any',
					'posts_per_page'         => - 1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						array(
							'key'     => 'ea_event_id',
							'value'   => $post_id,
							'compare' => '=',
						),
					),
				) );
				return $count + (int) $rsvp->post_count;
			}
			/**
			 * Status fields shared by both editors.
			 *
			 * @param int $post_id Event post ID.
			 */
			public static function render_fields( int $post_id ): void {
				$cancelled     = self::is_saved_cancelled( $post_id );
				$message       = (string) get_post_meta( $post_id, 'mep_event_cancel_message', true );
				$cancelled_at  = (string) get_post_meta( $post_id, 'mep_event_cancelled_at', true );
				$registrations = $post_id ? self::get_registration_count( $post_id ) : 0;
				$field_id      = 'mep_event_cancel_message_' . $post_id;
				?>
                <div class="mpwem-event-status<?php echo $cancelled ? ' is-cancelled' : ''; ?>" data-mpwem-event-status>
                    <input type="hidden" name="mep_event_status_present" value="1"/>
					<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
                    <div class="mpwem-event-status__current" aria-live="polite">
                        <span class="mpwem-event-status__chip mpwem-event-status__chip--scheduled">
                            <span class="mpwem-event-status__dot" aria-hidden="true"></span><?php esc_html_e( 'Scheduled', 'mage-eventpress' ); ?>
                        </span>
                        <span class="mpwem-event-status__chip mpwem-event-status__chip--cancelled">
                            <span class="dashicons dashicons-dismiss" aria-hidden="true"></span><?php esc_html_e( 'Cancelled', 'mage-eventpress' ); ?>
                        </span>
						<?php if ( $cancelled && $cancelled_at ) { ?>
                            <span class="mpwem-event-status__since">
								<?php
									/* translators: %s: date and time the event was cancelled. */
									echo esc_html( sprintf( __( 'since %s', 'mage-eventpress' ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $cancelled_at ) ) );
								?>
                            </span>
						<?php } ?>
                    </div>
                    <label class="mpwem-event-status__toggle">
                        <span class="mpwem-event-status__switch">
                            <input type="checkbox" name="mep_event_cancelled" value="yes" data-no-mpwem-switch="1" data-mpwem-event-status-toggle <?php checked( $cancelled ); ?> />
                            <span class="mpwem-event-status__switch-ui" aria-hidden="true"></span>
                        </span>
                        <span class="mpwem-event-status__toggle-copy">
                            <strong><?php esc_html_e( 'Cancel this event', 'mage-eventpress' ); ?></strong>
                            <small><?php esc_html_e( 'The event stays on its page and in every event list with a cancellation notice. Tickets, RSVP and checkout are switched off.', 'mage-eventpress' ); ?></small>
                        </span>
                    </label>
                    <div class="mpwem-event-status__details" data-mpwem-event-status-details<?php echo $cancelled ? '' : ' hidden'; ?>>
                        <label class="mpwem-event-status__label" for="<?php echo esc_attr( $field_id ); ?>">
							<?php esc_html_e( 'Cancellation notice', 'mage-eventpress' ); ?>
                            <span><?php esc_html_e( '(optional)', 'mage-eventpress' ); ?></span>
                        </label>
                        <textarea class="mpwem-event-status__message" id="<?php echo esc_attr( $field_id ); ?>" name="mep_event_cancel_message" rows="3" placeholder="<?php esc_attr_e( 'e.g. Unfortunately this course has been cancelled. Everyone who booked will be contacted about a refund.', 'mage-eventpress' ); ?>"><?php echo esc_textarea( $message ); ?></textarea>
                        <p class="mpwem-event-status__help"><?php esc_html_e( 'Shown under the title on the event page and, shortened, on event list cards. Leave empty to show "This event has been cancelled".', 'mage-eventpress' ); ?></p>
						<?php if ( $registrations > 0 ) { ?>
                            <div class="mpwem-event-status__warning">
                                <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                                <p>
                                    <strong>
										<?php
											/* translators: %d: number of existing registrations. */
											echo esc_html( sprintf( _n( '%d existing registration', '%d existing registrations', $registrations, 'mage-eventpress' ), $registrations ) );
										?>
                                    </strong>
									<?php esc_html_e( 'Use the options below to email them and settle their orders, or contact them yourself.', 'mage-eventpress' ); ?>
                                </p>
                            </div>
						<?php } ?>
						<?php self::render_follow_up_fields( $post_id ); ?>
                    </div>
                </div>
				<?php
			}
			/**
			 * Opt-in follow-up actions run when the cancelled event is saved, and the
			 * progress of earlier runs.
			 */
			private static function render_follow_up_fields( int $post_id ): void {
				if ( ! $post_id || ! class_exists( 'MPWEM_Event_Cancellation' ) ) {
					return;
				}
				$recipients = count( MPWEM_Event_Cancellation::get_recipients( $post_id ) );
				$has_wc     = function_exists( 'wc_get_order' );
				$orders     = $has_wc ? count( MPWEM_Event_Cancellation::get_order_ids( $post_id ) ) : 0;
				$subject    = (string) get_post_meta( $post_id, 'mep_event_cancel_email_subject', true );
				$body       = (string) get_post_meta( $post_id, 'mep_event_cancel_email_body', true );
				$subject    = '' !== trim( $subject ) ? $subject : MPWEM_Event_Cancellation::default_subject();
				$body       = '' !== trim( $body ) ? $body : MPWEM_Event_Cancellation::default_body();
				?>
                <div class="mpwem-event-status__followup">
                    <span class="mpwem-event-status__label"><?php esc_html_e( 'When you save', 'mage-eventpress' ); ?></span>
                    <label class="mpwem-event-status__option<?php echo $recipients ? '' : ' is-disabled'; ?>">
                        <input type="checkbox" name="mep_event_cancel_notify" value="yes" data-no-mpwem-switch="1" data-mpwem-event-status-notify <?php disabled( 0, $recipients ); ?> />
                        <span>
                            <strong><?php esc_html_e( 'Email everyone who booked', 'mage-eventpress' ); ?></strong>
                            <small>
								<?php
									/* translators: %d: number of people who will be emailed. */
									echo esc_html( sprintf( _n( '%d recipient. Each address is emailed once per cancellation.', '%d recipients. Each address is emailed once per cancellation.', $recipients, 'mage-eventpress' ), $recipients ) );
								?>
                            </small>
                        </span>
                    </label>
                    <div class="mpwem-event-status__email" data-mpwem-event-status-email hidden>
                        <label class="mpwem-event-status__label" for="mep_event_cancel_email_subject_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Email subject', 'mage-eventpress' ); ?></label>
                        <input type="text" class="mpwem-event-status__input" id="mep_event_cancel_email_subject_<?php echo esc_attr( $post_id ); ?>" name="mep_event_cancel_email_subject" value="<?php echo esc_attr( $subject ); ?>"/>
                        <label class="mpwem-event-status__label" for="mep_event_cancel_email_body_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Email message', 'mage-eventpress' ); ?></label>
                        <textarea class="mpwem-event-status__message" id="mep_event_cancel_email_body_<?php echo esc_attr( $post_id ); ?>" name="mep_event_cancel_email_body" rows="11"><?php echo esc_textarea( $body ); ?></textarea>
                        <p class="mpwem-event-status__help">
							<?php esc_html_e( 'Placeholders:', 'mage-eventpress' ); ?>
                            <code>{attendee_name}</code> <code>{event_name}</code> <code>{event_date}</code> <code>{cancel_message}</code> <code>{event_url}</code> <code>{site_name}</code>
                        </p>
                    </div>
					<?php if ( $has_wc ) {
						/* translators: %d: number of orders. */
						$confirm = sprintf( _n( 'Settle %d order when you save? Paid tickets are refunded to the customer through the order\'s payment method, and refunds cannot be undone.', 'Settle %d orders when you save? Paid tickets are refunded to the customers through each order\'s payment method, and refunds cannot be undone.', $orders, 'mage-eventpress' ), $orders );
						?>
                        <label class="mpwem-event-status__option<?php echo $orders ? '' : ' is-disabled'; ?>">
                            <input type="checkbox" name="mep_event_cancel_refund" value="yes" data-no-mpwem-switch="1" data-mpwem-event-status-refund data-confirm="<?php echo esc_attr( $confirm ); ?>" <?php disabled( 0, $orders ); ?> />
                            <span>
                                <strong><?php esc_html_e( 'Refund paid orders and cancel unpaid ones', 'mage-eventpress' ); ?></strong>
                                <small>
									<?php
										/* translators: %d: number of orders. */
										echo esc_html( sprintf( _n( '%d order.', '%d orders.', $orders, 'mage-eventpress' ), $orders ) ) . ' ';
										esc_html_e( 'Only this event\'s tickets are refunded, through the order\'s own payment method. Orders it cannot settle automatically get an order note and are listed below.', 'mage-eventpress' );
									?>
                                </small>
                            </span>
                        </label>
					<?php } ?>
					<?php self::render_job_status( $post_id ); ?>
                </div>
				<?php
			}
			/**
			 * Progress and outcome of the email and refund jobs.
			 */
			private static function render_job_status( int $post_id ): void {
				$jobs = MPWEM_Event_Cancellation::get_jobs( $post_id );
				if ( empty( $jobs ) ) {
					return;
				}
				$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
				?>
                <ul class="mpwem-event-status__jobs">
					<?php
						if ( ! empty( $jobs['notify'] ) ) {
							$job    = $jobs['notify'];
							$sent   = (int) ( $job['sent'] ?? 0 );
							$total  = (int) ( $job['total'] ?? 0 );
							$failed = isset( $job['failed'] ) && is_array( $job['failed'] ) ? $job['failed'] : array();
							$state  = (string) ( $job['status'] ?? '' );
							if ( 'aborted' === $state ) {
								$text = __( 'Emails stopped: the event was restored.', 'mage-eventpress' );
							} elseif ( 'done' === $state ) {
								/* translators: 1: emails sent, 2: recipients, 3: date. */
								$text = sprintf( __( 'Emails sent to %1$d of %2$d people (%3$s).', 'mage-eventpress' ), $sent, $total, mysql2date( $format, (string) ( $job['updated_at'] ?? '' ) ) );
							} else {
								/* translators: 1: emails sent so far, 2: recipients. */
								$text = sprintf( __( 'Sending emails in the background: %1$d of %2$d sent.', 'mage-eventpress' ), $sent, $total );
							}
							if ( $failed ) {
								/* translators: %s: email addresses. */
								$text .= ' ' . sprintf( __( 'Could not send to: %s', 'mage-eventpress' ), implode( ', ', $failed ) );
							}
							?>
                            <li class="is-<?php echo esc_attr( $state ); ?>"><span class="dashicons dashicons-email-alt" aria-hidden="true"></span><span><?php echo esc_html( $text ); ?></span></li>
							<?php
						}
						if ( ! empty( $jobs['refund'] ) ) {
							$job     = $jobs['refund'];
							$results = isset( $job['orders'] ) && is_array( $job['orders'] ) ? $job['orders'] : array();
							$state   = (string) ( $job['status'] ?? '' );
							$counts  = array_count_values( $results );
							$labels  = array(
								'refunded'         => __( 'refunded', 'mage-eventpress' ),
								'cancelled_unpaid' => __( 'unpaid, cancelled', 'mage-eventpress' ),
								'already_refunded' => __( 'already refunded', 'mage-eventpress' ),
								'manual'           => __( 'need settling by hand', 'mage-eventpress' ),
								'failed'           => __( 'refund failed', 'mage-eventpress' ),
								'mixed_unpaid'     => __( 'unpaid with other items, left open', 'mage-eventpress' ),
								'native_manual'    => __( 'native checkout, refund by hand', 'mage-eventpress' ),
							);
							$parts   = array();
							foreach ( $labels as $code => $label ) {
								if ( ! empty( $counts[ $code ] ) ) {
									$parts[] = $counts[ $code ] . ' ' . $label;
								}
							}
							if ( 'aborted' === $state ) {
								$text = __( 'Order settlement stopped: the event was restored.', 'mage-eventpress' );
							} else {
								$text = ( 'done' === $state ? __( 'Orders settled:', 'mage-eventpress' ) : __( 'Settling orders in the background:', 'mage-eventpress' ) )
									. ' ' . ( $parts ? implode( ', ', $parts ) : __( 'nothing to do yet', 'mage-eventpress' ) ) . '.';
							}
							$attention = array_keys( array_filter( $results, function ( $result ) {
								return in_array( $result, array( 'manual', 'failed', 'mixed_unpaid', 'native_manual' ), true );
							} ) );
							$links     = array();
							foreach ( array_slice( $attention, 0, 20 ) as $order_id ) {
								$order   = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
								$url     = $order ? $order->get_edit_order_url() : get_edit_post_link( $order_id );
								$links[] = '<a href="' . esc_url( (string) $url ) . '">#' . (int) $order_id . '</a>';
							}
							?>
                            <li class="is-<?php echo esc_attr( $state ); ?>">
                                <span class="dashicons dashicons-money-alt" aria-hidden="true"></span>
                                <span><?php echo esc_html( $text ); ?><?php if ( $links ) { echo ' ' . esc_html__( 'Check:', 'mage-eventpress' ) . ' ' . wp_kses_post( implode( ', ', $links ) ); } ?></span>
                            </li>
							<?php
						}
					?>
                </ul>
				<?php
			}
			public function save( $post_id ) {
				if ( ! isset( $_POST['mep_event_status_present'], $_POST[ self::NONCE_NAME ] ) ) {
					return;
				}
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
					return;
				}
				if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				$was_cancelled = self::is_saved_cancelled( $post_id );
				$cancelled     = isset( $_POST['mep_event_cancelled'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['mep_event_cancelled'] ) );
				update_post_meta( $post_id, 'mep_event_cancelled', $cancelled ? 'yes' : 'no' );
				if ( isset( $_POST['mep_event_cancel_message'] ) ) {
					update_post_meta( $post_id, 'mep_event_cancel_message', wp_kses_post( wp_unslash( $_POST['mep_event_cancel_message'] ) ) );
				}
				if ( $cancelled && ! $was_cancelled ) {
					update_post_meta( $post_id, 'mep_event_cancelled_at', current_time( 'mysql' ) );
				} elseif ( ! $cancelled ) {
					delete_post_meta( $post_id, 'mep_event_cancelled_at' );
				}
				if ( $cancelled !== $was_cancelled ) {
					/**
					 * Fires once when an event is cancelled or restored from the editor.
					 *
					 * Use it to notify attendees, cancel reminders or sync an external calendar.
					 *
					 * @param int $post_id Event post ID.
					 */
					do_action( $cancelled ? 'mpwem_event_cancelled' : 'mpwem_event_restored', $post_id );
				}
				if ( ! $cancelled || ! class_exists( 'MPWEM_Event_Cancellation' ) ) {
					return;
				}
				// Email template is kept per event, so a later run reuses the wording.
				if ( isset( $_POST['mep_event_cancel_email_subject'] ) ) {
					update_post_meta( $post_id, 'mep_event_cancel_email_subject', sanitize_text_field( wp_unslash( $_POST['mep_event_cancel_email_subject'] ) ) );
				}
				if ( isset( $_POST['mep_event_cancel_email_body'] ) ) {
					update_post_meta( $post_id, 'mep_event_cancel_email_body', wp_kses_post( wp_unslash( $_POST['mep_event_cancel_email_body'] ) ) );
				}
				if ( isset( $_POST['mep_event_cancel_notify'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['mep_event_cancel_notify'] ) ) ) {
					MPWEM_Event_Cancellation::queue( $post_id, 'notify', array(
						'subject' => (string) get_post_meta( $post_id, 'mep_event_cancel_email_subject', true ) ?: MPWEM_Event_Cancellation::default_subject(),
						'body'    => (string) get_post_meta( $post_id, 'mep_event_cancel_email_body', true ) ?: MPWEM_Event_Cancellation::default_body(),
					) );
				}
				// Refunds move money, so they also need the right to manage the shop's orders.
				if ( isset( $_POST['mep_event_cancel_refund'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['mep_event_cancel_refund'] ) ) && current_user_can( 'edit_shop_orders' ) ) {
					MPWEM_Event_Cancellation::queue( $post_id, 'refund' );
				}
			}
			/**
			 * Adds "Cancelled" beside the title in the WordPress events list table.
			 */
			public function post_states( $states, $post ) {
				if ( $post instanceof WP_Post && 'mep_events' === $post->post_type && self::is_saved_cancelled( $post->ID ) ) {
					$states['mpwem_cancelled'] = esc_html__( 'Cancelled', 'mage-eventpress' );
				}
				return $states;
			}
			public function enqueue_assets( $hook ) {
				unset( $hook );
				$screen        = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
				$is_classic    = $screen && 'mep_events' === $screen->post_type && 'post' === $screen->base;
				$is_modern     = isset( $_GET['page'] ) && 'mpwem_event_edit' === sanitize_key( wp_unslash( $_GET['page'] ) );
				if ( ! $is_classic && ! $is_modern ) {
					return;
				}
				wp_enqueue_style( 'mpwem_event_status_admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_event_status.css', array(), MPWEM_Dependencies::asset_version( 'assets/admin/mpwem_event_status.css' ) );
				wp_enqueue_script( 'mpwem_event_status_admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_event_status.js', array(), MPWEM_Dependencies::asset_version( 'assets/admin/mpwem_event_status.js' ), true );
			}
		}
		new MPWEM_Event_Status_Settings();
	}
