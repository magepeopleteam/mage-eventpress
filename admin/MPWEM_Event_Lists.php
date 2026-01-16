<?php
	/*
	* @Author 		rubelcuet10@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Event_Lists' ) ) {
		class MPWEM_Event_Lists {
			public function __construct() {
				add_action( 'admin_menu', array( $this, 'event_list_menu' ) );
				add_action( 'admin_action_mpwem_duplicate_post', [ $this, 'mpwem_duplicate_post_function' ] );
				add_action( 'wp_ajax_mpwem_trash_multiple_posts', [ $this, 'mpwem_trash_multiple_posts' ] );
				add_action( 'wp_ajax_mpwem_quick_edit_event', array( $this, 'mpwem_quick_edit_event' ) );
				add_action( 'wp_ajax_mpwem_popup_attendee_statistic', array( $this, 'mpwem_popup_attendee_statistic' ) );
				add_action( 'wp_ajax_mpwem_load_popup_attendee_statistics', array( $this, 'mpwem_load_popup_attendee_statistics' ) );
				add_action( 'wp_ajax_mpwem_load_time', array( $this, 'mpwem_load_time' ) );
			}
			public function event_list_menu() {
				add_submenu_page( 'edit.php?post_type=mep_events', __( 'Event Lists', 'mage-eventpress' ), __( 'Event Lists', 'mage-eventpress' ), 'manage_woocommerce', 'mep_event_lists', array( $this, 'display_event_list' ) );
			}
			public function display_event_list() {
				$counts = wp_count_posts( 'mep_events' );
				// Prepare the count data
				$post_counts = array(
					'publish' => isset( $counts->publish ) ? $counts->publish : 0,
					'draft'   => isset( $counts->draft ) ? $counts->draft : 0,
					'private' => isset( $counts->private ) ? $counts->private : 0,
					'trash'   => isset( $counts->trash ) ? $counts->trash : 0,
				);
				$total_event = $post_counts['publish'] + $post_counts['draft'] + $post_counts['private'] + $post_counts['trash'];
				//$statuses = ['publish', 'draft', 'trash'];
				$statuses           = [ 'publish', 'draft', 'private' ];
				$events             = get_posts( array(
					'post_type'   => 'mep_events',
					'post_status' => $statuses,
					'numberposts' => - 1
				) );
				$event_status_count = get_active_expire_upcoming_count( $events );
				$post_type          = 'mep_events';
				$add_new_link       = admin_url( 'post-new.php?post_type=' . $post_type );
				$trash_url          = admin_url( 'edit.php?post_status=trash&post_type=mep_events' );
				$order_status       = array( 'wc-completed', 'wc-processing' );
				$completed_orders   = wc_get_orders( [
					'status' => $order_status,
					'limit'  => - 1,
					'return' => 'ids',
				] );
				$total_registration = count( $completed_orders );
				$year               = date( 'Y' );
				$month              = date( 'm' );
				$prev_year          = $year;
				$prev_month         = $month - 1;
				if ( $month === 1 ) {
					$prev_month = 12;
					$prev_year  = $year - 1;
				}
				$currency                   = get_woocommerce_currency();
				$currency_symbol            = get_woocommerce_currency_symbol( $currency );
				$header_info                = get_monthly_revenue( $year, $month );
				$prev_header_info           = get_monthly_revenue( $prev_year, $prev_month );
				$current_month_revenue      = $header_info['revenue'];
				$current_month_registration = $header_info['each_month_registration'];
				$prev_month_revenue         = $prev_header_info['revenue'];
				$prev_month_registration    = $prev_header_info['each_month_registration'];
				$rev_change                 = $current_month_revenue - $prev_month_revenue;
				$revenue_percent_change     = get_change_in_percent( $current_month_revenue, $prev_month_revenue );
				$reg_percent_change         = get_change_in_percent( $current_month_registration, $prev_month_registration );
				$get_all_categories         = get_all_event_taxonomy( 'mep_cat' );
				?>
                <div class="wrap">
                    <div class="mpwem_event_list mpwem_style mpwem_welcome_page">
                        <div class="container">
                            <div class="header">
                                <div class="header-top">
                                    <h1><?php esc_html_e( 'Event Management Dashboard', 'mage-eventpress' ) ?></h1>
                                    <a href="<?php echo esc_url( $add_new_link ); ?>">
                                        <button class="add-event-btn">
                                            <span>+</span>
											<?php esc_html_e( 'Add New Event', 'mage-eventpress' ) ?>
                                        </button>
                                    </a>
                                </div>
                                <div class="analytics">
                                    <div class="analytics-card">
                                        <h3><?php echo esc_html( $total_event ); ?></h3>
                                        <p><?php esc_html_e( 'Total Events', 'mage-eventpress' ); ?></p>
                                        <div class="trend up">↗ +12% this month</div>
                                    </div>
                                    <div class="analytics-card">
                                        <h3><?php echo esc_html( $event_status_count['active_count'] ); ?></h3>
                                        <p><?php esc_html_e( 'Active Events', 'mage-eventpress' ); ?></p>
                                        <div class="trend neutral">→ <?php esc_html_e( 'Same as last week', 'mage-eventpress' ); ?></div>
                                    </div>
                                    <div class="analytics-card">
                                        <h3><?php echo esc_html( $total_registration ); ?></h3>
                                        <p><?php esc_html_e( 'Total Registrations', 'mage-eventpress' ); ?></p>
                                        <div class="trend up">↗ <?php echo esc_html( $reg_percent_change['inc_dec_sign'] . '%' . $reg_percent_change['percent_change'] . ' vs last month' ); ?></div>
                                    </div>
                                    <div class="analytics-card">
                                        <h3><?php echo esc_html( $currency_symbol . ' ' . $current_month_revenue ); ?></h3>
                                        <p><?php esc_html_e( 'Revenue This Month', 'mage-eventpress' ); ?></p>
                                        <div class="trend up">
											<?php printf( '↗ %1$s%2$s%% vs last month', esc_html( $revenue_percent_change['inc_dec_sign'] ), esc_html( $revenue_percent_change['percent_change'] ) ); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="stats-summary">
                                    <div class="stat-item mpwem_filter_by_status mpwem_filter_btn_active_bg_color" data-by-filter="all">
                                        <span><?php esc_html_e( 'All Events', 'mage-eventpress' ); ?></span>
                                        <span class="stat-number">(<?php echo esc_html( $total_event ); ?>)</span>
                                    </div>
                                    <div class="stat-item mpwem_filter_by_status" data-by-filter="publish">
                                        <span><?php esc_html_e( 'Published', 'mage-eventpress' ); ?></span>
                                        <span class="stat-number">(<?php echo esc_html( $post_counts['publish'] ); ?>)</span>
                                    </div>
                                    <div class="stat-item mpwem_filter_by_status" data-by-filter="draft">
                                        <span><?php esc_html_e( 'Draft', 'mage-eventpress' ); ?></span>
                                        <span class="stat-number">(<?php echo esc_html( $post_counts['draft'] ); ?>)</span>
                                    </div>
                                    <div class="stat-item mpwem_filter_by_status" data-by-filter="private">
                                        <span><?php esc_html_e( 'Private', 'mage-eventpress' ); ?></span>
                                        <span class="stat-number">(<?php echo esc_html( $post_counts['private'] ); ?>)</span>
                                    </div>
                                    <div class="stat-item mpwem_filter_by_active_status" data-by-filter="active">
                                        <span><?php esc_html_e( 'Active', 'mage-eventpress' ); ?></span>
                                        <span class="stat-number">(<?php echo esc_html( $event_status_count['active_count'] ); ?>)</span>
                                    </div>
                                    <div class="stat-item mpwem_filter_by_active_status" data-by-filter="expired">
                                        <span><?php esc_html_e( 'Expired', 'mage-eventpress' ); ?></span>
                                        <span class="stat-number">(<?php echo esc_html( $event_status_count['expire_count'] ); ?>)</span>
                                    </div>
                                    <a href="<?php echo esc_url( $trash_url ); ?>">
                                        <div class="stat-item">
                                            <span><?php esc_html_e( 'Trash', 'mage-eventpress' ); ?></span>
                                            <span class="stat-number">(<?php echo esc_html( $post_counts['trash'] ); ?>)</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="controls">
                                <div class="mpwem_multiple_trash_holder" id="mpwem_multiple_trash_holder" style="display: none">
                                    <button class="mpwem_multiple_trash_btn" id="mpwem_multiple_trash_btn">Trash</button>
                                    <input type="hidden" id='mpwem_multiple_trash_nonce' value="<?php echo esc_attr( wp_create_nonce( 'mpwem_multiple_trash_nonce' ) ); ?>">
                                </div>
                                <div class="search-box">
                                    <div class="search-icon">🔍</div>
                                    <input id="mpwem_search_event_list" type="text" placeholder="<?php esc_attr_e( 'Search events, locations, or organizers...', 'mage-eventpress' ); ?>">
                                </div>
                                <select class="category-select" id="mpwem_event_filter_by_category">
                                    <option><?php esc_html_e( 'All Categories', 'mage-eventpress' ); ?></option>
									<?php
										if ( is_array( $get_all_categories ) && ! empty( $get_all_categories ) ) {
											foreach ( $get_all_categories as $key => $event_categories ) { ?>
                                                <option><?php echo esc_html( $event_categories ); ?></option>
											<?php }
										}
									?>
                                </select>
                                <div class="date-filter-container">
                                    <label for="mpwem_date_from"><?php esc_html_e( 'From:', 'mage-eventpress' ); ?></label>
                                    <input type="date" id="mpwem_date_from" class="date-filter">
                                    <label for="mpwem_date_to"><?php esc_html_e( 'To:', 'mage-eventpress' ); ?></label>
                                    <input type="date" id="mpwem_date_to" class="date-filter">
                                    <button type="button" id="mpwem_clear_date_filter" class="clear-date-btn"><?php esc_html_e( 'Clear', 'mage-eventpress' ); ?></button>
                                </div>
                            </div>
                            <div class="table-container">
                                <table class="event-table">
                                    <thead>
                                    <tr>
                                        <th><input type="checkbox" class="checkbox" id="mpwem_select_all_post"></th>
                                        <th><?php esc_html_e( 'Image', 'mage-eventpress' ); ?></th>
                                        <th class="sortable" data-sort="title"><?php esc_html_e( 'Event Name', 'mage-eventpress' ); ?> <span class="sort-indicator"></span></th>
                                        <th><?php esc_html_e( 'Location', 'mage-eventpress' ); ?></th>
                                        <th class="sortable" data-sort="date"><?php esc_html_e( 'Event Date', 'mage-eventpress' ); ?> <span class="sort-indicator"></span></th>
                                        <th><?php esc_html_e( 'Event Starts In', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Ticket Types', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Capacity', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Actions', 'mage-eventpress' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php render_mep_events_by_status( $events ); ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination">
                                <div class="pagination-info">
									<?php esc_html_e( 'Showing', 'mage-eventpress' ); ?> <span id="visibleCount">0</span> of <span id="totalCount">0</span> <?php esc_html_e( ' Events', 'mage-eventpress' ); ?>
                                </div>
                                <button class="load-more-btn" id="loadMoreBtn">
                                    <span><?php esc_html_e( 'Load More Events', 'mage-eventpress' ); ?></span>
                                    <span>↓</span>
                                </button>
                            </div>
                            <div class="mpPopup mpwem_popup_attendee_statistic" data-popup="mpwem_popup_attendee_statistic"></div>
                        </div>
                    </div>
                </div>
				<?php
			}
			function mpwem_trash_multiple_posts() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mpwem_multiple_trash_nonce' ) ) {
					wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
				}
				if ( ! is_user_logged_in() ) {
					wp_send_json_error( [ 'message' => 'User not logged in' ] );
				}
				if ( ! current_user_can( 'delete_posts' ) ) {
					wp_send_json_error( [ 'message' => 'Permission denied' ] );
				}
				// Sanitize and validate post IDs
				$post_ids = ( isset( $_POST['post_ids'] ) && is_array( $_POST['post_ids'] ) ) ? array_map( 'intval', $_POST['post_ids'] ) : [];
				if ( empty( $post_ids ) ) {
					wp_send_json_error( [ 'message' => 'No valid post IDs provided.' ] );
				}
				foreach ( $post_ids as $post_id ) {
					if ( get_post_type( $post_id ) === 'mep_events' && get_post_status( $post_id ) !== 'trash' && ( get_post_field( 'post_author', $post_id ) == get_current_user_id() || is_super_admin() ) ) {
						wp_trash_post( $post_id );
					}
				}
				wp_send_json_success( [ 'message' => 'Selected posts moved to trash successfully.' ] );
			}
			function mpwem_duplicate_post_function() {
				if ( ! isset( $_GET['post_id'] ) || ! isset( $_GET['_wpnonce'] ) ||
				     ! wp_verify_nonce( $_GET['_wpnonce'], 'mpwem_duplicate_post_' . sanitize_text_field( $_GET['post_id'] ) )
				) {
					wp_die( 'Invalid request (missing or invalid nonce).' );
				}
				$post_id     = (int) sanitize_text_field( wp_unslash( $_GET['post_id'] ) );
				$post        = get_post( $post_id );
				$new_post    = array(
					'post_title'   => $post->post_title . ' (Copy)',
					'post_content' => $post->post_content,
					'post_status'  => 'draft',
					'post_type'    => $post->post_type,
					'post_author'  => get_current_user_id(),
				);
				$new_post_id = wp_insert_post( $new_post );
				if ( is_wp_error( $new_post_id ) || ! $new_post_id ) {
					wp_die( 'Failed to duplicate post.' );
				}
				$meta = get_post_meta( $post_id );
				foreach ( $meta as $key => $values ) {
					foreach ( $values as $value ) {
						add_post_meta( $new_post_id, $key, maybe_unserialize( $value ) );
					}
				}
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
				exit;
			}
			public function mpwem_quick_edit_event() {
				// Verify nonce
				if ( ! wp_verify_nonce( $_POST['nonce'], 'mep_nonce' ) ) {
					wp_send_json_error( array( 'message' => 'Security check failed' ) );
				}
				// Check user capabilities
				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_send_json_error( array( 'message' => 'You do not have permission to edit events' ) );
				}
				$post_id = intval( $_POST['post_id'] );
				if ( ! $post_id ) {
					wp_send_json_error( array( 'message' => 'Invalid event ID' ) );
				}
				// Update post data
				$post_data = array(
					'ID'          => $post_id,
					'post_title'  => sanitize_text_field( $_POST['post_title'] ),
					'post_status' => sanitize_text_field( $_POST['post_status'] )
				);
				$result    = wp_update_post( $post_data );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array( 'message' => 'Failed to update event' ) );
				}
				// Update event meta data
				if ( isset( $_POST['event_start_datetime'] ) ) {
					update_post_meta( $post_id, 'event_start_datetime', sanitize_text_field( $_POST['event_start_datetime'] ) );
				}
				if ( isset( $_POST['event_end_datetime'] ) ) {
					update_post_meta( $post_id, 'event_end_datetime', sanitize_text_field( $_POST['event_end_datetime'] ) );
				}
				if ( isset( $_POST['mep_location_venue'] ) ) {
					update_post_meta( $post_id, 'mep_location_venue', sanitize_text_field( $_POST['mep_location_venue'] ) );
				}
				// Update categories
				if ( isset( $_POST['mep_cat'] ) && is_array( $_POST['mep_cat'] ) ) {
					$categories = array_map( 'intval', $_POST['mep_cat'] );
					wp_set_post_terms( $post_id, $categories, 'mep_cat' );
				}
				wp_send_json_success( array( 'message' => 'Event updated successfully' ) );
			}
			public function mpwem_popup_attendee_statistic() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				$dates   = isset( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					wp_die();
				}
				$all_dates = MPWEM_Functions::get_all_dates( $post_id );
				$date      = MPWEM_Functions::get_upcoming_date_time( $post_id );
				$date      = $dates ?: $date;
				if ( ! $date && sizeof( $all_dates ) > 0 ) {
					$date_type = MPWEM_Global_Function::get_post_info( $post_id, 'mep_enable_recurring', 'no' );
					if ( $date_type == 'no' || $date_type == 'yes' ) {
						$date = date( 'Y-m-d', strtotime( end( $all_dates )['time'] ) );
					} else {
						$date = date( 'Y-m-d', strtotime( end( $all_dates ) ) );
					}
				}
				if ( $date ) {
					?>
                    <div class="popupMainArea min_1000">
                        <div class="popupHeader">
                            <input type="hidden" name="mpwem_post_id" value="<?php echo esc_attr( $post_id ); ?>"/>
                            <div class="_dFlex_fdColumn_align_center">
                                <h4 class="_mb_xs"><?php echo esc_html( get_the_title( $post_id ) ); ?></h4>
                                <div class="date_time_area">
									<?php MPWEM_Layout::load_date( $post_id, $all_dates ); ?>
                                </div>
                            </div>
                            <span class="fas fa-times popup_close"></span>
                        </div>
                        <div class="popupBody mpwem_popup_attendee_statistic_body ">
							<?php $this->popup_static_list( $post_id, $date ); ?>
                        </div>
                    </div>
					<?php
				}
				wp_die();
			}
			public function mpwem_load_popup_attendee_statistics() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					wp_die();
				}
				$date = isset( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : '';
				if ( $post_id && $date ) {
					$this->popup_static_list( $post_id, $date );
				}
				wp_die();
			}
			public function popup_static_list( $post_id, $date ) {
				$date_format = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : 'date';
				?>
                <h4 class="_text_center"><?php echo esc_html( MPWEM_Global_Function::date_format( $date, $date_format ,$post_id) ); ?></h4>
                <div class="_divider"></div>
				<?php $this->attendee_statistic_list( $post_id, $date ); ?>
				<?php
			}
			public static function attendee_statistic_list( $event_id, $date ) {
				if ( $event_id && $date ) {
					$ticket_types = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
					?>
                    <table>
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Ticket Type Name', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Total Seat', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Total Reserved', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Ticket Sold', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Available Seat', 'mage-eventpress' ); ?></th>
                        </tr>
                        </thead>
						<?php if ( sizeof( $ticket_types ) > 0 ) { ?>
                            <tbody>
							<?php
								do_action( 'mpwem_gq_statistics', $event_id, $date );
								foreach ( $ticket_types as $ticket_type ) {
									$ticket_name      = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
									$ticket_qty       = array_key_exists( 'option_qty_t', $ticket_type ) ? $ticket_type['option_qty_t'] : 0;
									$ticket_r_qty     = array_key_exists( 'option_rsv_t', $ticket_type ) ? $ticket_type['option_rsv_t'] : 0;
									$total_sold       = mep_get_ticket_type_seat_count( $event_id, $ticket_name, $date, $ticket_qty, $ticket_r_qty );
									$available_ticket = (int) $ticket_qty - ( (int) $total_sold + (int) $ticket_r_qty );
									?>
                                    <tr>
                                        <th><?php echo esc_html( $ticket_name ); ?></th>
                                        <th><?php echo esc_html( apply_filters( 'mpwem_gq_qty_statistics', $ticket_qty, $event_id ) ); ?></th>
                                        <th><?php echo esc_html( apply_filters( 'mpwem_gq_qty_statistics', $ticket_r_qty, $event_id ) ); ?></th>
                                        <th><?php echo esc_html( $total_sold ); ?></th>
                                        <th><?php echo esc_html( apply_filters( 'mpwem_gq_qty_statistics', $available_ticket, $event_id ) ); ?></th>
                                    </tr>
								<?php } ?>
                            </tbody>
						<?php } ?>
                    </table>
					<?php
				}
			}
			public function mpwem_load_time() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$date      = isset( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : '';
				$all_times = MPWEM_Functions::get_all_times( $post_id, $date );
				MPWEM_Layout::load_time( $all_times, $date );
				//echo '<pre>';print_r(MPWEM_Functions::get_all_dates($post_id));echo '</pre>';
				die();
			}
		}
		new MPWEM_Event_Lists();
	}
	function get_active_expire_upcoming_count( $events ) {
		$active_count   = 0;
		$expire_count   = 0;
		$upcoming_count = 0;
		if ( ! empty( $events ) ) {
			foreach ( $events as $post ) {
				$id              = $post->ID;
				$start_date      = get_post_meta( $id, 'event_start_datetime', true );
				$start_date      = date( 'F j, Y', strtotime( $start_date ) );
				$end_date        = get_post_meta( $id, 'event_end_datetime', true );
				$start_timestamp = strtotime( $start_date );
				$end_timestamp   = strtotime( $end_date );
				$now             = time();
				if ( $now < $start_timestamp ) {
					$upcoming_count ++;
				} elseif ( $now >= $start_timestamp && $now <= $end_timestamp ) {
					$active_count ++;
				} else if ( $now > $end_timestamp ) {
					$expire_count ++;
				}
			}
		}
		$active_count = $active_count + $upcoming_count;
		return array(
			'active_count'   => $active_count,
			'expire_count'   => $expire_count,
			'upcoming_count' => $upcoming_count,
		);
	}
	function get_all_event_taxonomy( $taxonomy ) {
		$taxonomies = array();
		$terms      = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$taxonomies[ $term->slug ] = $term->name;
			}
		}
		return $taxonomies;
	}
	function get_event_wise_taxonomy( $event_id, $taxonomy ) {
		$terms        = get_the_terms( $event_id, $taxonomy );
		$cat_data     = $category_data = [];
		$all_category = '';
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$all_category .= $term->name . ', ';
				$cat_data[]   = [
					'name' => $term->name,
					'slug' => $term->slug,
				];
			}
		}
		$all_category  = rtrim( $all_category, ", \t\n\r\0\x0B" );
		$category_data = array(
			'all_category' => $all_category,
			'cat_data'     => $cat_data,
		);
		return $category_data;
	}
	function get_monthly_revenue( $year = null, $month = null ) {
		if ( ! $year ) {
			$year = date( 'Y' );
		}
		if ( ! $month ) {
			$month = date( 'm' );
		}
		$start_date             = "$year-$month-01 00:00:00";
		$order_status           = array( 'wc-completed', 'wc-processing' );
		$end_date               = date( 'Y-m-t 23:59:59', strtotime( $start_date ) );
		$orders                 = wc_get_orders( [
			'limit'        => - 1,
			'status'       => $order_status,
			'date_created' => $start_date . '...' . $end_date,
			'return'       => 'ids',
		] );
		$total                  = 0;
		$each_month_order_count = count( $orders );
		foreach ( $orders as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$total += $order->get_total();
			}
		}
		return array(
			'revenue'                 => $total,
			'each_month_registration' => $each_month_order_count,
		);
	}
	function get_change_in_percent( $current_month, $prev_month ) {
		$change = $current_month - $prev_month;
		if ( $prev_month > 0 ) {
			$percent_change = ( $change / $prev_month ) * 100;
		} else {
			$percent_change = 100;
		}
		$direction_icon = $change > 0 ? '+' : ( $change < 0 ? '-' : '+' );
		return array(
			'percent_change' => $percent_change,
			'inc_dec_sign'   => $direction_icon,
		);
	}
	function get_time_remaining_fixed( $event_id, $end_date ) {
		$all_dates      = MPWEM_Functions::get_dates( $event_id );
		$all_times      = MPWEM_Functions::get_times( $event_id, $all_dates );
		$now            = time();
		$future_found   = false;
		$closest_future = null;
		foreach ( $all_dates as $date_info ) {
			$date_str = is_array( $date_info ) && isset( $date_info['time'] ) ? $date_info['time'] : $date_info;
			$date_ts  = strtotime( $date_str );
			if ( $date_ts > $now && ( ! $closest_future || $date_ts < $closest_future ) ) {
				$closest_future = $date_ts;
				$future_found   = true;
			}
		}
		if ( $future_found && $closest_future ) {
			$interval = $closest_future - $now;
			$days     = floor( $interval / 86400 );
			$hours    = floor( ( $interval % 86400 ) / 3600 );
			$minutes  = floor( ( $interval % 3600 ) / 60 );
			return sprintf( '%d days, %d hours, %d minutes remaining', $days, $hours, $minutes );
		}
		// fallback: check end_date
		if ( strtotime( $end_date ) > $now ) {
			$interval = strtotime( $end_date ) - $now;
			$days     = floor( $interval / 86400 );
			$hours    = floor( ( $interval % 86400 ) / 3600 );
			$minutes  = floor( ( $interval % 3600 ) / 60 );
			return sprintf( '%d days, %d hours, %d minutes remaining', $days, $hours, $minutes );
		}
		return 'Expired!';
	}
	function render_mep_events_by_status( $posts ) {
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$id             = $post->ID;
				$title          = get_the_title( $id );
				$thumbnail_url  = get_the_post_thumbnail_url( $id, 'small' );
				$status         = get_post_status( $id );
				$edit_link      = get_edit_post_link( $id );
				$delete_link    = get_delete_post_link( $id ); // Moves to Trash
				$view_link      = get_permalink( $id );
				$start_date     = get_post_meta( $id, 'event_start_datetime', true );
				$start_date     = date( 'F j, Y', strtotime( $start_date ) );
				$start_time     = get_post_meta( $id, 'event_start_time', true );
				$end_date       = get_post_meta( $id, 'event_end_datetime', true );
				$ticket_type    = get_post_meta( $id, 'mep_event_ticket_type', true );
				$location       = get_post_meta( $id, 'mep_location_venue', true );
				$time_remaining = get_time_remaining_fixed( $id, $end_date );
				$event_type     = MPWEM_Global_Function::get_post_info( $id, 'mep_enable_recurring', 'no' );
				$event_id       = $id ?? 0;
				$all_dates      = MPWEM_Functions::get_dates( $event_id );
				$all_times      = MPWEM_Functions::get_times( $event_id, $all_dates );
				if ( ! empty( $all_dates ) ) {
					$date = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
				} else {
					$date = $start_date;
				}
				if ( ! empty( $all_dates ) && ! empty( $all_times ) ) {
					$time = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
					$time = date( 'H:i', strtotime( $time ) );
				} else {
					$time = $start_time;
				}
				$total_ticket = MPWEM_Functions::get_total_ticket( $id, $date );
				$total_sold   = mep_get_event_total_seat_left( $id );
				if ( $event_type === 'everyday' ) {
					$time_remaining    = get_time_remaining_fixed( $id, $end_date );
					$start_date        = date( 'F j, Y', strtotime( $date ) );
					$event_type_status = 'Recurring Event (Repeated)';
					$total_sold        = mep_get_event_total_seat_left( $id, $date );
				} else if ( $event_type === 'yes' ) {
					$time_remaining    = get_time_remaining_fixed( $id, $end_date );
					$start_date        = date( 'F j, Y', strtotime( $date ) );
					$event_type_status = 'Recurring Event (Selected Dates)';
					$total_sold        = mep_get_event_total_seat_left( $id, $date );
				} else {
					$event_type_status = '';
				}
				if ( $total_ticket === $total_sold ) {
					$text       = 'Full';
					$full_class = 'capacity-full';
				} else {
					$text       = 'Available';
					$full_class = '';
				}
				$cat_data        = get_event_wise_taxonomy( $id, 'mep_cat' );
				$organiser_data  = get_event_wise_taxonomy( $id, 'mep_org' );
				$category        = isset( $cat_data['cat_data'][0] ) ? $cat_data['cat_data'][0]['name'] : '';
				$event_category  = isset( $cat_data['all_category'] ) ? $cat_data['all_category'] : '';
				$event_organiser = isset( $organiser_data['all_category'] ) ? $organiser_data['all_category'] : '';
				$start_timestamp = strtotime( $start_date );
				$end_timestamp   = strtotime( $end_date );
				$now             = time();
				if ( $now < $start_timestamp ) {
					$event_status       = 'Active';
					$event_status_class = 'status-active';
				} elseif ( $now >= $start_timestamp && $now <= $end_timestamp ) {
					$event_status       = 'Active';
					$event_status_class = 'status-active';
				} elseif ( $now > $end_timestamp ) {
					$event_status       = 'Expired';
					$event_status_class = 'status-expired';
				} else {
					$event_status       = '';
					$event_status_class = '';
				}
				if ( $time_remaining === 'Expired!' ) {
					$event_status_class = 'status-expired';
				}
				$ticket_type_count = 0;
				?>
                <tr class="mpwem_event_list_card"
                    data-event-status="<?php echo esc_attr( $status ); ?>"
                    data-event-active-status="<?php echo esc_attr( $event_status ); ?>"
                    data-filter-by-category="<?php echo esc_attr( $event_category ); ?>"
                    data-filter-by-event-name="<?php echo esc_attr( $title ); ?>"
                    data-filter-by-event-organiser="<?php echo esc_attr( $event_organiser ); ?>"
                    data-event-date="<?php echo esc_attr( strtotime( $start_date ) ); ?>"
                    data-event-title="<?php echo esc_attr( $title ); ?>"
                    data-event-id="<?php echo esc_attr( $id ); ?>"
                >
                    <td data-event-id="<?php echo esc_attr( $id ); ?>">
                        <input type="checkbox" class="checkbox mpwem_select_single_post" id="mpwem_select_single_post_<?php echo esc_attr( $id ); ?>" name="mpwem_checkbox_post_id[]">
                    </td>
                    <td>
                        <div class="mpwem_event-image-placeholder">
                            <img class="mpwem_event_feature_image" src="<?php echo esc_url( ! empty( $thumbnail_url ) ? $thumbnail_url : 'https://placehold.co/300x300?text=No+Event+Image+Found' ); ?>" alt="">
                        </div>
                    </td>
                    <td class="mpwem_event_title">
                        <div class="event-name">
                            <strong class="row-title">
                                <a href="<?php echo esc_url( $edit_link ); ?>" class="row-title-link"><?php echo esc_attr( $title . ' ' . $event_type_status ); ?></a>
                            </strong>
                            <div class="event-status-inline">
								<?php if ( $status === 'publish' ) { ?>
                                    <div class="status-live-inline">
                                        <div class="live-indicator-inline"></div>
										<?php esc_html_e( 'Published', 'mage-eventpress' ); ?>
                                    </div>
								<?php } else if ( $status === 'draft' ) { ?>
                                    <div class="event-status-inline">
                                        <div class="status-draft-inline"><?php esc_html_e( 'Draft', 'mage-eventpress' ); ?></div>
                                    </div>
								<?php } else if ( $status === 'private' ) { ?>
                                    <div class="status-private-inline">
                                        <div class="private-indicator-inline"></div>
										<?php esc_html_e( 'Private', 'mage-eventpress' ); ?>
                                    </div>
								<?php } else { ?>
                                    <div class="event-status-inline">
                                        <div class="status-draft-inline"><?php esc_html_e( 'Trash', 'mage-eventpress' ); ?></div>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                        <div class='mep_after_event_title'>
							<?php
								$custom_meta_value = get_post_meta( $id, '_sku', true ) ? 'SKU: ' . get_post_meta( $id, '_sku', true ) : 'ID: ' . $id;
								if ( ! empty( $custom_meta_value ) ) {
									echo '<span style="color:rgb(117, 111, 111); font-weight: bold;font-size: 12px;">' . esc_html( $custom_meta_value ) . '</span>';
								}
							?>
                        </div>
                        <div class="event-category" style='margin:10px 0;'><?php echo esc_html( $category ); ?></div>
                    </td>
                    <td>
                        <div class="location">
                            <i class="mi mi-marker"></i> <?php echo esc_html( $location ); ?>
                        </div>
                    </td>
                    <td>
                        <div class="date-time">
                            <span><?php echo esc_html( $start_date ); ?></span>
                            <span class="time"><?php echo esc_html( $time ); ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="status-badge mpwem_remaining_days <?php echo esc_attr( $event_status_class ); ?>"><?php echo esc_html( $time_remaining ); ?></div>
                    </td>
                    <td>
                        <div class="ticket-types">
							<?php
								$dis_ticket_type_count = 0;;
								if ( is_array( $ticket_type ) && ! empty( $ticket_type ) ) {
									$ticket_type_count = count( $ticket_type );
									foreach ( $ticket_type as $type ) {
										if ( $dis_ticket_type_count < 2 ) {
											?>
                                            <div class="ticket-item">
                                                <span class="ticket-name"><?php echo array_key_exists( 'option_name_t', $type ) ? esc_html( $type['option_name_t'] ) : ''; ?></span>
                                                <span class="ticket-price ticket-free"><?php echo isset( $type['option_price_t'] ) ? esc_html( $type['option_price_t'] ) : ''; ?></span>
                                            </div>
											<?php
										}
										$dis_ticket_type_count ++;
									}
									?>
								<?php }
								if ( $ticket_type_count > 2 ) {
									$more_ticket_type = $ticket_type_count - 2;
									?>
                                    <div class="ticket-more">+<?php echo esc_html( $more_ticket_type ); ?> more</div>
								<?php } ?>
                        </div>
                    </td>
                    <td class="mpwem_event_list_capacity">
                        <div class="mpwem_event_list_capacity-number"><?php echo esc_html( $total_sold ); ?>/<?php echo esc_html( $total_ticket ); ?></div>
                        <div class="mpwem_event_list_capacity-bar">
                            <div class="mpwem_event_list_capacity-fill <?php echo esc_attr( $full_class ); ?>" style="width: 100%"></div>
                        </div>
                        <div class="mpwem_event_list_capacity-status"><?php echo esc_html( $text ); ?></div>
                    </td>
                    <td>
                        <div class="actions">
							<?php do_action( 'mep_before_dashboard_event_list', $id ); ?>
                            <a href="<?php echo esc_url( $view_link ); ?>">
                                <button class="action-btn view" title="View Event"><span class="mi mi-eye"></span></button>
                            </a>
                            <a href="#">
                                <button class="action-btn quick-edit" title="Quick Edit" data-event-id="<?php echo esc_attr( $id ); ?>"><span class="mi mi-file-edit"></span></button>
                            </a>
                            <a href="<?php echo esc_url( $edit_link ); ?>">
                                <button class="action-btn edit" title="Edit Event"><span class="mi mi-pencil"></span></button>
                            </a>
                            <a href="#" data-mpwem_popup_attendee_statistic="mpwem_popup_attendee_statistic" data-event-id="<?php echo esc_attr( $id ); ?>">
                                <button class="action-btn"><i class="mi mi-stats"></i></button>
                            </a>
                            <a href="<?php echo esc_url( $delete_link ); ?>">
                                <button class="action-btn delete" title="Delete Event"><span class="mi mi-trash"></span></button>
                            </a>
							<?php do_action( 'mep_after_dashboard_event_list', $id ); ?>
                        </div>
                    </td>
                </tr>
                <!-- Quick Edit Row -->
                <tr class="quick-edit-row quick-edit-row-post inline-edit-row" style="display: none;" data-event-id="<?php echo esc_attr( $id ); ?>">
                    <td colspan="9" class="colspanchange">
                        <fieldset class="inline-edit-col-left">
                            <legend class="inline-edit-legend"><?php esc_html_e( 'Quick Edit', 'mage-eventpress' ); ?></legend>
                            <div class="inline-edit-col">
                                <label>
                                    <span class="title"><?php esc_html_e( 'Title', 'mage-eventpress' ); ?></span>
                                    <span class="input-text-wrap">
                                        <input type="text" name="post_title" class="ptitle" value="<?php echo esc_attr( $title ); ?>">
                                    </span>
                                </label>
                                <label>
                                    <span class="title"><?php esc_html_e( 'Event Start Date', 'mage-eventpress' ); ?></span>
                                    <span class="input-text-wrap">
                                        <input type="datetime-local" name="event_start_datetime" class="event-start-date" value="<?php echo esc_attr( date( 'Y-m-d\TH:i', strtotime( $start_date . ' ' . $start_time ) ) ); ?>">
                                    </span>
                                </label>
                                <label>
                                    <span class="title"><?php esc_html_e( 'Event End Date', 'mage-eventpress' ); ?></span>
                                    <span class="input-text-wrap">
                                        <input type="datetime-local" name="event_end_datetime" class="event-end-date" value="<?php echo esc_attr( date( 'Y-m-d\TH:i', strtotime( $end_date ) ) ); ?>">
                                    </span>
                                </label>
                                <label>
                                    <span class="title"><?php esc_html_e( 'Location', 'mage-eventpress' ); ?></span>
                                    <span class="input-text-wrap">
                                        <input type="text" name="mep_location_venue" class="event-location" value="<?php echo esc_attr( $location ); ?>">
                                    </span>
                                </label>
                            </div>
                        </fieldset>
                        <fieldset class="inline-edit-col-right">
                            <div class="inline-edit-col">
                                <label class="inline-edit-status">
                                    <span class="title"><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></span>
                                    <select name="_status">
										<?php $current_status = get_post_status( $id ); ?>
                                        <option value="publish" <?php selected( $current_status, 'publish' ); ?>><?php esc_html_e( 'Published', 'mage-eventpress' ); ?></option>
                                        <option value="draft" <?php selected( $current_status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'mage-eventpress' ); ?></option>
                                        <option value="private" <?php selected( $current_status, 'private' ); ?>><?php esc_html_e( 'Private', 'mage-eventpress' ); ?></option>
                                    </select>
                                </label>
                                <label>
                                    <span class="title"><?php esc_html_e( 'Categories', 'mage-eventpress' ); ?></span>
                                    <select name="mep_cat[]" multiple class="event-categories">
										<?php
											$event_categories = get_the_terms( $id, 'mep_cat' );
											$selected_cats    = array();
											if ( $event_categories && ! is_wp_error( $event_categories ) ) {
												foreach ( $event_categories as $cat ) {
													$selected_cats[] = $cat->term_id;
												}
											}
											$all_categories = MPWEM_Global_Function::get_taxonomy( 'mep_cat' );
											if ( $all_categories && ! is_wp_error( $all_categories ) ) {
												foreach ( $all_categories as $cat_term ) {
													?>
                                                    <option value="<?php echo esc_attr( $cat_term->term_id ); ?>" <?php echo esc_attr( in_array( $cat_term->term_id, $selected_cats ) ? 'selected' : '' ); ?>><?php echo esc_html( $cat_term->name ); ?></option>
													<?php
												}
											}
										?>
                                    </select>
                                </label>
                            </div>
                        </fieldset>
                        <input type="hidden" class="mep-quick-edit-nonce" value="<?php echo esc_attr( wp_create_nonce( 'mep_nonce' ) ); ?>"/>
                        <div class="submit inline-edit-save">
                            <button type="button" class="button cancel alignleft"><?php esc_html_e( 'Cancel', 'mage-eventpress' ); ?></button>
                            <button type="button" class="button button-primary save alignright"><?php esc_html_e( 'Update', 'mage-eventpress' ); ?></button>
                            <span class="spinner"></span>
                            <br class="clear">
                        </div>
                    </td>
                </tr>
			<?php }
		} else {
			echo '<p>No posts found.</p>';
		}
	}