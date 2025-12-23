<?php
/**
 * Event Booking Dashboard for WooCommerce My Account
 * 
 * This class handles the enhanced Event Booking Dashboard in WooCommerce My Account page
 * Features: Event Bookings, Booking Details, Attendee Details, Edit Info, PDF Download
 * 
 * @package MageEventPress
 * @since 5.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'MPWEM_My_Account_Dashboard' ) ) {
	class MPWEM_My_Account_Dashboard {
		
		public function __construct() {
			// Add custom endpoint
			add_action( 'init', array( $this, 'add_endpoints' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			
			// Add menu item
			add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_items' ) );
			
			// Add endpoint content
			add_action( 'woocommerce_account_event-bookings_endpoint', array( $this, 'event_bookings_content' ) );
			add_action( 'woocommerce_account_event-booking-details_endpoint', array( $this, 'booking_details_content' ) );
			
			// Enqueue scripts and styles
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			
			// AJAX handlers
			add_action( 'wp_ajax_mpwem_get_booking_details', array( $this, 'ajax_get_booking_details' ) );
			add_action( 'wp_ajax_mpwem_search_bookings', array( $this, 'ajax_search_bookings' ) );
			
			// Remove old account dashboard display
			remove_action( 'woocommerce_account_dashboard', array( 'MPWEM_Woocommerce', 'account_dashboard' ) );
		}
		
		/**
		 * Register custom endpoints
		 */
		public function add_endpoints() {
			add_rewrite_endpoint( 'event-bookings', EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( 'event-booking-details', EP_ROOT | EP_PAGES );
			flush_rewrite_rules();
		}
		
		/**
		 * Add query vars
		 */
		public function add_query_vars( $vars ) {
			$vars[] = 'event-bookings';
			$vars[] = 'event-booking-details';
			return $vars;
		}
		
		/**
		 * Add menu items to My Account
		 */
		public function add_menu_items( $items ) {
			// Find position after 'orders'
			$position = array_search( 'orders', array_keys( $items ) );
			if ( $position !== false ) {
				$position += 1;
				$new_items = array(
					'event-bookings' => __( 'Event Booking Dashboard', 'mage-eventpress' )
				);
				$items = array_slice( $items, 0, $position, true ) + $new_items + array_slice( $items, $position, null, true );
			} else {
				$items['event-bookings'] = __( 'Event Booking Dashboard', 'mage-eventpress' );
			}
			return $items;
		}
		
		/**
		 * Enqueue scripts and styles
		 */
		public function enqueue_scripts() {
			if ( is_account_page() ) {
				wp_enqueue_style( 'mpwem-account-dashboard', MPWEM_PLUGIN_URL . '/assets/frontend/mpwem_account_dashboard.css', array(), time() );
				wp_enqueue_script( 'mpwem-account-dashboard', MPWEM_PLUGIN_URL . '/assets/frontend/mpwem_account_dashboard.js', array( 'jquery' ), time(), true );
				
				wp_localize_script( 'mpwem-account-dashboard', 'mpwem_account_vars', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'mpwem_account_nonce' ),
				) );
			}
		}
		
		/**
		 * Event Bookings Dashboard Content
		 */
		public function event_bookings_content() {
			$user_id = get_current_user_id();
			?>
			<div class="mpwem-event-bookings-dashboard">
				<div class="mpwem-dashboard-header">
					<h2><?php esc_html_e( 'Event Booking Dashboard', 'mage-eventpress' ); ?></h2>
					<p class="mpwem-dashboard-description"><?php esc_html_e( 'View and manage all your event bookings in one place', 'mage-eventpress' ); ?></p>
				</div>
				
				<!-- Search and Filter Section -->
				<div class="mpwem-dashboard-filters">
					<div class="mpwem-filter-group">
						<input type="text" id="mpwem-search-order" class="mpwem-search-input" placeholder="<?php esc_attr_e( 'Search by Order Number...', 'mage-eventpress' ); ?>">
						<button type="button" class="mpwem-btn mpwem-btn-search" id="mpwem-search-btn">
							<span class="dashicons dashicons-search"></span>
							<?php esc_html_e( 'Search', 'mage-eventpress' ); ?>
						</button>
						<button type="button" class="mpwem-btn mpwem-btn-reset" id="mpwem-reset-btn">
							<span class="dashicons dashicons-update"></span>
							<?php esc_html_e( 'Reset', 'mage-eventpress' ); ?>
						</button>
					</div>
					<div class="mpwem-filter-stats">
						<?php echo $this->get_booking_stats( $user_id ); ?>
					</div>
				</div>
				
				<!-- Bookings Table -->
				<div class="mpwem-bookings-table-wrapper">
					<table class="mpwem-bookings-table woocommerce-orders-table shop_table shop_table_responsive">
						<thead>
							<tr>
								<th class="mpwem-col-order"><?php esc_html_e( 'Order', 'mage-eventpress' ); ?></th>
								<th class="mpwem-col-event"><?php esc_html_e( 'Event Details', 'mage-eventpress' ); ?></th>
								<th class="mpwem-col-tickets"><?php esc_html_e( 'Tickets', 'mage-eventpress' ); ?></th>
								<th class="mpwem-col-status"><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></th>
								<th class="mpwem-col-actions"><?php esc_html_e( 'Actions', 'mage-eventpress' ); ?></th>
							</tr>
						</thead>
						<tbody id="mpwem-bookings-list">
							<?php echo $this->get_bookings_list( $user_id ); ?>
						</tbody>
					</table>
				</div>
				
				<!-- Booking Details Modal -->
				<div id="mpwem-booking-details-modal" class="mpwem-modal" style="display:none;">
					<div class="mpwem-modal-content">
						<span class="mpwem-modal-close">&times;</span>
						<div id="mpwem-booking-details-content">
							<div class="mpwem-loading">
								<span class="spinner is-active"></span>
								<?php esc_html_e( 'Loading...', 'mage-eventpress' ); ?>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Cancel Request Modal -->
				<div id="mpwem-cancel-modal" class="mpwem-modal" style="display:none;">
					<div class="mpwem-modal-content mpwem-modal-small">
						<span class="mpwem-modal-close">&times;</span>
						<div id="mpwem-cancel-modal-content">
							<!-- Content will be loaded by JavaScript -->
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		
		/**
		 * Get booking statistics
		 */
		private function get_booking_stats( $user_id ) {
			$_user_set_status = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
			$_order_status    = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
			
			$args = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => -1,
				'author__in'     => array( $user_id ),
				'fields'         => 'ids',
			);
			
			$all_bookings = new WP_Query( $args );
			$total_bookings = $all_bookings->post_count;
			
			// Get upcoming events
			$upcoming = 0;
			$completed = 0;
			
			foreach ( $all_bookings->posts as $attendee_id ) {
				$event_id = get_post_meta( $attendee_id, 'ea_event_id', true );
				$time = get_post_meta( $event_id, 'event_expire_datetime', true ) 
					? strtotime( get_post_meta( $event_id, 'event_expire_datetime', true ) ) 
					: strtotime( get_post_meta( $event_id, 'event_start_datetime', true ) );
				
				if ( strtotime( current_time( 'Y-m-d H:i:s' ) ) < $time ) {
					$upcoming++;
				} else {
					$completed++;
				}
			}
			
			ob_start();
			?>
			<div class="mpwem-stats">
				<div class="mpwem-stat-item mpwem-stat-clickable" data-filter="all">
					<span class="mpwem-stat-number"><?php echo esc_html( $total_bookings ); ?></span>
					<span class="mpwem-stat-label"><?php esc_html_e( 'Total Bookings', 'mage-eventpress' ); ?></span>
				</div>
				<div class="mpwem-stat-item mpwem-stat-clickable" data-filter="upcoming">
					<span class="mpwem-stat-number"><?php echo esc_html( $upcoming ); ?></span>
					<span class="mpwem-stat-label"><?php esc_html_e( 'Upcoming', 'mage-eventpress' ); ?></span>
				</div>
				<div class="mpwem-stat-item mpwem-stat-clickable" data-filter="completed">
					<span class="mpwem-stat-number"><?php echo esc_html( $completed ); ?></span>
					<span class="mpwem-stat-label"><?php esc_html_e( 'Completed', 'mage-eventpress' ); ?></span>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}
		
		/**
		 * Get bookings list HTML
		 */
		private function get_bookings_list( $user_id, $order_search = '', $filter = 'all' ) {
			$_user_set_status = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
			$_order_status    = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
			$order_status     = array_values( $_order_status );
			
			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'     => 'ea_order_status',
					'value'   => $order_status,
					'compare' => 'IN'
				)
			);
			
			if ( ! empty( $order_search ) ) {
				$meta_query[] = array(
					'key'     => 'ea_order_id',
					'value'   => $order_search,
					'compare' => '='
				);
			}
			
			$args = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => -1,
				'author__in'     => array( $user_id ),
				'meta_query'     => $meta_query,
				'orderby'        => 'meta_value_num',
				'meta_key'       => 'ea_order_id',
				'order'          => 'DESC'
			);
			
			$bookings = new WP_Query( $args );
			
			if ( ! $bookings->have_posts() ) {
				return '<tr><td colspan="6" class="mpwem-no-bookings">' . esc_html__( 'No event bookings found.', 'mage-eventpress' ) . '</td></tr>';
			}
			
			ob_start();
			$grouped_orders = array();
			
			// Group by order ID
			while ( $bookings->have_posts() ) {
				$bookings->the_post();
				$order_id = get_post_meta( get_the_ID(), 'ea_order_id', true );
				
				if ( ! isset( $grouped_orders[ $order_id ] ) ) {
					$grouped_orders[ $order_id ] = array();
				}
				$grouped_orders[ $order_id ][] = get_the_ID();
			}
			
			foreach ( $grouped_orders as $order_id => $attendee_ids ) {
				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					continue;
				}
				
				$first_attendee_id = $attendee_ids[0];
				$event_id          = get_post_meta( $first_attendee_id, 'ea_event_id', true );
				$event_name        = get_post_meta( $first_attendee_id, 'ea_event_name', true );
				$event_date        = get_post_meta( $event_id, 'event_start_datetime', true );
				$ticket_count      = count( $attendee_ids );
				$order_status      = $order->get_status();
				
				// Filter logic
				if ( $filter !== 'all' ) {
					$time = get_post_meta( $event_id, 'event_expire_datetime', true ) 
						? strtotime( get_post_meta( $event_id, 'event_expire_datetime', true ) ) 
						: strtotime( $event_date );
					
					$is_upcoming = strtotime( current_time( 'Y-m-d H:i:s' ) ) < $time;
					
					if ( $filter === 'upcoming' && ! $is_upcoming ) {
						continue;
					}
					
					if ( $filter === 'completed' && $is_upcoming ) {
						continue;
					}
				}
				
				// Get event featured image
				$event_thumbnail = get_the_post_thumbnail_url( $event_id, 'thumbnail' );
				$event_link      = get_permalink( $event_id );
				
				?>
				<tr class="mpwem-booking-row" data-order-id="<?php echo esc_attr( $order_id ); ?>">
					<td class="mpwem-col-order" data-label="<?php esc_attr_e( 'Order', 'mage-eventpress' ); ?>">
						<div class="mpwem-order-info">
							<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="mpwem-order-number">
								#<?php echo esc_html( $order_id ); ?>
							</a>
							<span class="mpwem-order-date"><?php echo esc_html( $order->get_date_created()->date_i18n( 'M j, Y' ) ); ?></span>
						</div>
					</td>
					<td class="mpwem-col-event" data-label="<?php esc_attr_e( 'Event Details', 'mage-eventpress' ); ?>">
						<div class="mpwem-event-info">
							<div class="mpwem-event-details">
								<a href="<?php echo esc_url( $event_link ); ?>" target="_blank" class="mpwem-event-name">
									<?php echo esc_html( $event_name ); ?>
								</a>
								<span class="mpwem-event-date">
									<i class="dashicons dashicons-calendar-alt"></i>
									<?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $event_date ) ) ); ?>
								</span>
							</div>
						</div>
					</td>
					<td class="mpwem-col-tickets" data-label="<?php esc_attr_e( 'Tickets', 'mage-eventpress' ); ?>">
						<span class="mpwem-ticket-count"><?php echo esc_html( $ticket_count ); ?> <?php echo _n( 'Ticket', 'Tickets', $ticket_count, 'mage-eventpress' ); ?></span>
					</td>
					<td class="mpwem-col-status" data-label="<?php esc_attr_e( 'Status', 'mage-eventpress' ); ?>">
						<span class="mpwem-status mpwem-status-<?php echo esc_attr( $order_status ); ?>">
							<?php echo esc_html( wc_get_order_status_name( $order_status ) ); ?>
						</span>
					</td>
					<td class="mpwem-col-actions" data-label="<?php esc_attr_e( 'Actions', 'mage-eventpress' ); ?>">
						<div class="mpwem-actions-group">
							<button type="button" class="mpwem-btn mpwem-btn-view" data-order-id="<?php echo esc_attr( $order_id ); ?>" title="<?php esc_attr_e( 'View Details', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-visibility"></span>
								<?php esc_html_e( 'View', 'mage-eventpress' ); ?>
							</button>
							<?php
							// PDF Download button
							if ( class_exists( 'MPWEM_PDF' ) ) {
								$pdf_url = MPWEM_PDF::get_pdf_url( array( 'order_id' => $order_id ) );
								if ( $pdf_url ) {
									?>
									<a href="<?php echo esc_url( $pdf_url ); ?>" class="mpwem-btn mpwem-btn-pdf" target="_blank" title="<?php esc_attr_e( 'Download PDF Ticket', 'mage-eventpress' ); ?>">
										<span class="dashicons dashicons-pdf"></span>
										<?php esc_html_e( 'PDF', 'mage-eventpress' ); ?>
									</a>
									<?php
								}
							}
							
							// Edit button (if form builder addon is active)
							if ( function_exists( 'mfb_endpoint_url' ) ) {
								$is_edit_enable = get_post_meta( $event_id, 'mep_event_attendee_edit_frontend', true );
								if ( $is_edit_enable === 'on' ) {
									?>
									<a href="<?php echo esc_url( mfb_endpoint_url() . $first_attendee_id ); ?>" class="mpwem-btn mpwem-btn-edit" title="<?php esc_attr_e( 'Edit Attendee Info', 'mage-eventpress' ); ?>">
										<span class="dashicons dashicons-edit"></span>
										<?php esc_html_e( 'Edit', 'mage-eventpress' ); ?>
									</a>
									<?php
								}
							}
							
							do_action( 'mpwem_booking_row_actions', $order_id, $attendee_ids );
							?>
						</div>
					</td>
				</tr>
				<?php
			}
			wp_reset_postdata();
			
			return ob_get_clean();
		}
		
		/**
		 * AJAX: Get booking details
		 */
		public function ajax_get_booking_details() {
			check_ajax_referer( 'mpwem_account_nonce', 'nonce' );
			
			$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
			
			if ( ! $order_id ) {
				wp_send_json_error( array( 'message' => __( 'Invalid order ID', 'mage-eventpress' ) ) );
			}
			
			$order = wc_get_order( $order_id );
			if ( ! $order || $order->get_user_id() !== get_current_user_id() ) {
				wp_send_json_error( array( 'message' => __( 'Access denied', 'mage-eventpress' ) ) );
			}
			
			ob_start();
			$this->render_booking_details( $order_id );
			$html = ob_get_clean();
			
			wp_send_json_success( array( 'html' => $html ) );
		}
		
		/**
		 * Render booking details
		 */
		private function render_booking_details( $order_id ) {
			$order = wc_get_order( $order_id );
			
			// Get all attendees for this order
			$args = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => -1,
				'author__in'     => array( get_current_user_id() ),
				'meta_query'     => array(
					array(
						'key'     => 'ea_order_id',
						'value'   => $order_id,
						'compare' => '='
					)
				)
			);
			
			$attendees = new WP_Query( $args );
			
			?>
			<div class="mpwem-booking-details">
				<div class="mpwem-booking-header">
					<h3><?php echo esc_html__( 'Booking Details - Order ', 'mage-eventpress' ).' #'. $order_id ; ?></h3>
					<div class="mpwem-booking-meta">
						<span class="mpwem-booking-date">
							<strong><?php esc_html_e( 'Order Date:', 'mage-eventpress' ); ?></strong>
							<?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) ); ?>
						</span>
						<span class="mpwem-booking-status">
							<strong><?php esc_html_e( 'Status:', 'mage-eventpress' ); ?></strong>
							<span class="mpwem-status mpwem-status-<?php echo esc_attr( $order->get_status() ); ?>">
								<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
							</span>
						</span>
					</div>
				</div>
				
				<div class="mpwem-booking-content">
					<!-- Attendee Details Section -->
					<div class="mpwem-section mpwem-attendees-section">
						<h4><?php esc_html_e( 'Attendee Details', 'mage-eventpress' ); ?></h4>
						<div class="mpwem-attendees-list">
							<?php
							if ( $attendees->have_posts() ) {
								$count = 1;
								while ( $attendees->have_posts() ) {
									$attendees->the_post();
									$attendee_id = get_the_ID();
									$event_id    = get_post_meta( $attendee_id, 'ea_event_id', true );
									$count++;
									?>
									<div class="mpwem-attendee-card">
										<div class="mpwem-attendee-header">
											<h5><?php echo esc_html__( 'Attendee ', 'mage-eventpress' ).'#'. $count ; ?></h5>
											<?php
											// Only show View Ticket button if Form Builder addon is active
											if ( class_exists( 'MPWEM_Addon_Pro' ) ) {
												$ticket_url = get_permalink( $attendee_id );
												if ( $ticket_url ) {
													?>
													<a href="<?php echo esc_url( $ticket_url ); ?>" class="mpwem-btn mpwem-btn-small" target="_blank">
														<?php esc_html_e( 'View Ticket', 'mage-eventpress' ); ?>
													</a>
													<?php
												}
											}
											?>
										</div>
										<div class="mpwem-attendee-info">
											<?php
											// Only show attendee details if Form Builder addon is active
											if ( class_exists( 'MPWEM_Addon_Pro' ) ) {
												$attendee_data = array(
													'ea_name'       => __( 'Name', 'mage-eventpress' ),
													'ea_email'      => __( 'Email', 'mage-eventpress' ),
													'ea_phone'      => __( 'Phone', 'mage-eventpress' ),
													'ea_ticket_type' => __( 'Ticket Type', 'mage-eventpress' ),
													'ea_address_1'  => __( 'Address', 'mage-eventpress' ),
													'ea_company'    => __( 'Company', 'mage-eventpress' ),
												);
												
												foreach ( $attendee_data as $meta_key => $label ) {
													$value = get_post_meta( $attendee_id, $meta_key, true );
													if ( ! empty( $value ) ) {
														?>
														<div class="mpwem-info-row">
															<span class="mpwem-info-label"><?php echo esc_html( $label ); ?>:</span>
															<span class="mpwem-info-value"><?php echo esc_html( $value ); ?></span>
														</div>
														<?php
													}
												}
												
												// Custom form fields
												if ( function_exists( 'mep_get_event_form_data' ) ) {
													$form_data = get_post_meta( $event_id, 'mep_event_form_builder', true );
													if ( is_array( $form_data ) ) {
														foreach ( $form_data as $field ) {
															if ( isset( $field['name'] ) && isset( $field['label'] ) ) {
																$field_value = get_post_meta( $attendee_id, $field['name'], true );
																if ( ! empty( $field_value ) ) {
																	?>
																	<div class="mpwem-info-row">
																		<span class="mpwem-info-label"><?php echo esc_html( $field['label'] ); ?>:</span>
																		<span class="mpwem-info-value"><?php echo esc_html( $field_value ); ?></span>
																	</div>
																	<?php
																}
															}
														}
													}
												}
											} else {
												// Form Builder not active - show message
												?>
												<div class="mpwem-info-row">
													<p style="color: #666; font-style: italic;"><?php esc_html_e( 'Attendee information requires Form Builder addon to be active.', 'mage-eventpress' ); ?></p>
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<?php
								}
								wp_reset_postdata();
							}
							?>
						</div>
					</div>
					
					<!-- Order Information Section -->
					<div class="mpwem-section mpwem-order-section">
						<h4><?php esc_html_e( 'Order Information', 'mage-eventpress' ); ?></h4>
						<table class="mpwem-order-table">
							<tbody>
								<tr>
									<td><strong><?php esc_html_e( 'Order Total:', 'mage-eventpress' ); ?></strong></td>
									<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
								</tr>
								<tr>
									<td><strong><?php esc_html_e( 'Payment Method:', 'mage-eventpress' ); ?></strong></td>
									<td><?php echo esc_html( $order->get_payment_method_title() ); ?></td>
								</tr>
								<?php if ( $order->get_billing_email() ) : ?>
								<tr>
									<td><strong><?php esc_html_e( 'Billing Email:', 'mage-eventpress' ); ?></strong></td>
									<td><?php echo esc_html( $order->get_billing_email() ); ?></td>
								</tr>
								<?php endif; ?>
								<?php if ( $order->get_billing_phone() ) : ?>
								<tr>
									<td><strong><?php esc_html_e( 'Billing Phone:', 'mage-eventpress' ); ?></strong></td>
									<td><?php echo esc_html( $order->get_billing_phone() ); ?></td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
					
					<?php do_action( 'mpwem_booking_details_after_sections', $order_id ); ?>
				</div>
				
				<div class="mpwem-booking-footer">
					<?php
					// PDF Download - only show if PDF plugin is active
					if ( class_exists( 'MPWEM_PDF' ) ) {
						$pdf_url = MPWEM_PDF::get_pdf_url( array( 'order_id' => $order_id ) );
						if ( $pdf_url ) {
							?>
							<a href="<?php echo esc_url( $pdf_url ); ?>" class="mpwem-btn mpwem-btn-primary" target="_blank">
								<span class="dashicons dashicons-pdf"></span>
								<?php esc_html_e( 'Download PDF Tickets', 'mage-eventpress' ); ?>
							</a>
							<?php
						}
					}
					?>
					<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="mpwem-btn mpwem-btn-secondary">
						<?php esc_html_e( 'View Full Order Details', 'mage-eventpress' ); ?>
					</a>
					<?php do_action( 'mpwem_booking_details_footer_actions', $order_id ); ?>
				</div>
			</div>
			<?php
		}
		
		/**
		 * AJAX: Search bookings
		 */
		public function ajax_search_bookings() {
			check_ajax_referer( 'mpwem_account_nonce', 'nonce' );
			
			$search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
			$filter = isset( $_POST['filter'] ) ? sanitize_text_field( $_POST['filter'] ) : 'all';
			$user_id = get_current_user_id();
			
			$html = $this->get_bookings_list( $user_id, $search, $filter );
			
			wp_send_json_success( array( 'html' => $html ) );
		}
	}
	
	new MPWEM_My_Account_Dashboard();
}
