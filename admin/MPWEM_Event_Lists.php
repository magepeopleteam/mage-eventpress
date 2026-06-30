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
				require_once MPWEM_PLUGIN_DIR . '/admin/mep_dummy_import.php';
				add_action( 'admin_menu', array( $this, 'event_list_menu' ) );
				add_action( 'admin_action_mpwem_duplicate_post', [ $this, 'mpwem_duplicate_post_function' ] );
				add_action( 'wp_ajax_mpwem_trash_multiple_posts', [ $this, 'mpwem_trash_multiple_posts' ] );
				add_action( 'wp_ajax_mpwem_restore_event', array( $this, 'mpwem_restore_event' ) );
				add_action( 'wp_ajax_mpwem_delete_event_permanently', array( $this, 'mpwem_delete_event_permanently' ) );
				add_action( 'wp_ajax_mpwem_empty_event_trash', array( $this, 'mpwem_empty_event_trash' ) );
				add_action( 'wp_ajax_mpwem_load_event_list', array( $this, 'mpwem_load_event_list' ) );
				add_action( 'wp_ajax_mpwem_dashboard_stats', array( $this, 'mpwem_dashboard_stats' ) );
				add_action( 'wp_ajax_mpwem_quick_edit_event', array( $this, 'mpwem_quick_edit_event' ) );
				add_action( 'wp_ajax_mpwem_popup_attendee_statistic', array( $this, 'mpwem_popup_attendee_statistic' ) );
				add_action( 'wp_ajax_mpwem_load_popup_attendee_statistics', array( $this, 'mpwem_load_popup_attendee_statistics' ) );
				add_action( 'wp_ajax_mpwem_load_time', array( $this, 'mpwem_load_time' ) );
			}
			public function event_list_menu() {
				add_submenu_page( 'edit.php?post_type=mep_events', __( 'Event Lists', 'mage-eventpress' ), __( 'Event Lists', 'mage-eventpress' ), MPWEM_Global_Function::get_admin_capability(), 'mep_event_lists', array( $this, 'display_event_list' ) );
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
				// Active/Expired counts via cheap meta-compare queries instead of loading every event.
				$event_status_count = mpwem_active_expire_count();
				$post_type          = 'mep_events';
				$add_new_link       = admin_url( 'post-new.php?post_type=' . $post_type );
				// Heavy, order-scanning analytics (registrations + revenue) are loaded asynchronously
				// via the mpwem_dashboard_stats AJAX handler so the page can paint immediately.
				$get_all_categories         = get_all_event_taxonomy( 'mep_cat' );
				?>
                <div class="wrap">
                    <div class="mpwem_event_list mpwem_style mpwem_welcome_page">
                        <div class="container">
                            <div class="header">
                                <div class="header-top">
                                    <h1><?php esc_html_e( 'Event Management Dashboard', 'mage-eventpress' ) ?></h1>
                                    <div style="display: flex; gap: 10px;">
                                        <?php if ( get_option('mep_dummy_already_inserted') !== 'yes' && empty($post_counts['publish']) && \Admin\mep_dummy_import::check_plugin('mage-eventpress', 'woocommerce-event-press.php') == 1 ) : ?>
                                            <button type="button" class="add-event-btn" id="mep-trigger-dummy-import-btn" style="background-color: #6366f1; color: white; border: none;">
                                                <span style="margin-right: 5px;">↓</span>
                                                <?php esc_html_e( 'Import Dummy Data', 'mage-eventpress' ) ?>
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?php echo esc_url( $add_new_link ); ?>">
                                            <button class="add-event-btn">
                                                <span>+</span>
                                                <?php esc_html_e( 'Add New Event', 'mage-eventpress' ) ?>
                                            </button>
                                        </a>
                                    </div>
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
                                        <h3 id="mpwem_total_registration" class="mpwem-stat-loading">&hellip;</h3>
                                        <p><?php esc_html_e( 'Total Registrations', 'mage-eventpress' ); ?></p>
                                        <div class="trend up" id="mpwem_registration_trend">&nbsp;</div>
                                    </div>
                                    <div class="analytics-card">
                                        <h3 id="mpwem_month_revenue" class="mpwem-stat-loading">&hellip;</h3>
                                        <p><?php esc_html_e( 'Revenue This Month', 'mage-eventpress' ); ?></p>
                                        <div class="trend up" id="mpwem_revenue_trend">&nbsp;</div>
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
                                    <div class="stat-item mpwem_filter_by_status" data-by-filter="trash">
                                        <span><?php esc_html_e( 'Trash', 'mage-eventpress' ); ?></span>
                                        <span class="stat-number" id="mpwem_trash_count">(<?php echo esc_html( $post_counts['trash'] ); ?>)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="controls">
                                <div class="mpwem_multiple_trash_holder" id="mpwem_multiple_trash_holder" style="display: none">
                                    <button class="mpwem_multiple_trash_btn" id="mpwem_multiple_trash_btn">Trash</button>
                                    <input type="hidden" id='mpwem_multiple_trash_nonce' value="<?php echo esc_attr( wp_create_nonce( 'mpwem_multiple_trash_nonce' ) ); ?>">
                                </div>
                                <button type="button" class="mpwem_empty_trash_btn" id="mpwem_empty_trash_btn" style="display: none;"><?php esc_html_e( 'Empty Trash', 'mage-eventpress' ); ?></button>
                                <div class="search-box">
                                    <div class="search-icon">🔍</div>
                                    <input id="mpwem_search_event_list" type="text" placeholder="<?php esc_attr_e( 'Search events, locations, or organizers...', 'mage-eventpress' ); ?>">
                                </div>
                                <select class="category-select" id="mpwem_event_filter_by_category">
                                    <option value=""><?php esc_html_e( 'All Categories', 'mage-eventpress' ); ?></option>
									<?php
										if ( is_array( $get_all_categories ) && ! empty( $get_all_categories ) ) {
											foreach ( $get_all_categories as $key => $event_categories ) { ?>
                                                <option value="<?php echo esc_attr( $key ); ?>" data-slug="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $event_categories ); ?></option>
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
                                    <tbody id="mpwem_event_list_body">
                                    <tr class="mpwem_event_list_loading_row">
                                        <td colspan="9" style="text-align:center;padding:30px;">
                                            <?php esc_html_e( 'Loading events…', 'mage-eventpress' ); ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination">
                                <div class="mpwem-pg-left">
                                    <div class="pagination-info">
										<?php esc_html_e( 'Showing', 'mage-eventpress' ); ?> <span id="visibleCount">0</span> of <span id="totalCount">0</span> <?php esc_html_e( ' Events', 'mage-eventpress' ); ?>
                                    </div>
                                    <div class="mpwem-perpage">
                                        <label for="mpwem_per_page"><?php esc_html_e( 'Show', 'mage-eventpress' ); ?></label>
                                        <select id="mpwem_per_page" class="mpwem-perpage-input">
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                        <span><?php esc_html_e( 'events', 'mage-eventpress' ); ?></span>
                                    </div>
                                </div>
                                <div class="mpwem-pagination-switch" id="mpwem_pagination_switch" role="tablist" aria-label="<?php esc_attr_e( 'Pagination mode', 'mage-eventpress' ); ?>">
                                    <button type="button" class="mpwem-pg-mode" data-mode="loadmore"><?php esc_html_e( 'Load More', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="mpwem-pg-mode" data-mode="numbered"><?php esc_html_e( 'Numbered', 'mage-eventpress' ); ?></button>
                                </div>
                                <div class="mpwem-pg-right">
                                    <button class="load-more-btn" id="loadMoreBtn">
                                        <span><?php esc_html_e( 'Load More Events', 'mage-eventpress' ); ?></span>
                                        <svg class="mpwem-pg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M6 13l6 6 6-6"/></svg>
                                    </button>
                                    <div class="mpwem-numbered-pagination" id="mpwem_numbered_pagination"></div>
                                </div>
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
			/**
			 * Restore a single trashed event (Trash → its previous status), like WordPress.
			 */
			public function mpwem_restore_event() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_nonce' ) ) {
					wp_send_json_error( array( 'message' => 'Security check failed' ) );
				}
				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_send_json_error( array( 'message' => 'Permission denied' ) );
				}
				$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
				if ( ! $post_id || get_post_type( $post_id ) !== 'mep_events' ) {
					wp_send_json_error( array( 'message' => 'Invalid event' ) );
				}
				if ( ! ( get_post_field( 'post_author', $post_id ) == get_current_user_id() || is_super_admin() ) ) {
					wp_send_json_error( array( 'message' => 'Permission denied' ) );
				}
				wp_untrash_post( $post_id );
				wp_send_json_success( array( 'message' => 'Event restored.' ) );
			}
			/**
			 * Permanently delete a single trashed event, like WordPress.
			 */
			public function mpwem_delete_event_permanently() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_nonce' ) ) {
					wp_send_json_error( array( 'message' => 'Security check failed' ) );
				}
				if ( ! current_user_can( 'delete_posts' ) ) {
					wp_send_json_error( array( 'message' => 'Permission denied' ) );
				}
				$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
				if ( ! $post_id || get_post_type( $post_id ) !== 'mep_events' || get_post_status( $post_id ) !== 'trash' ) {
					wp_send_json_error( array( 'message' => 'Invalid event' ) );
				}
				if ( ! ( get_post_field( 'post_author', $post_id ) == get_current_user_id() || is_super_admin() ) ) {
					wp_send_json_error( array( 'message' => 'Permission denied' ) );
				}
				wp_delete_post( $post_id, true );
				wp_send_json_success( array( 'message' => 'Event permanently deleted.' ) );
			}
			/**
			 * Permanently delete every trashed event the user may delete (Empty Trash).
			 */
			public function mpwem_empty_event_trash() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_nonce' ) ) {
					wp_send_json_error( array( 'message' => 'Security check failed' ) );
				}
				if ( ! current_user_can( 'delete_posts' ) ) {
					wp_send_json_error( array( 'message' => 'Permission denied' ) );
				}
				$trashed = get_posts( array(
					'post_type'      => 'mep_events',
					'post_status'    => 'trash',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				) );
				$deleted = 0;
				foreach ( $trashed as $pid ) {
					if ( get_post_field( 'post_author', $pid ) == get_current_user_id() || is_super_admin() ) {
						wp_delete_post( $pid, true );
						$deleted ++;
					}
				}
				wp_send_json_success( array( 'message' => 'Trash emptied.', 'deleted' => $deleted ) );
			}
			public function mpwem_load_event_list() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_nonce' ) ) {
					wp_send_json_error( array( 'message' => 'Security check failed' ) );
				}
				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_send_json_error( array( 'message' => 'Permission denied' ) );
				}
				$page          = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
				$per_page      = isset( $_POST['per_page'] ) ? min( 100, max( 1, absint( $_POST['per_page'] ) ) ) : 20;
				$search        = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
				$category      = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
				$date_from     = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
				$date_to       = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';
				$status        = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'all';
				$active_status = isset( $_POST['active_status'] ) ? sanitize_text_field( wp_unslash( $_POST['active_status'] ) ) : 'all';
				$orderby       = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : '';
				$order         = ( isset( $_POST['order'] ) && strtolower( $_POST['order'] ) === 'desc' ) ? 'DESC' : 'ASC';

				$allowed_status = array( 'publish', 'draft', 'private', 'trash' );
				// "all" (or anything unrecognised) lists the live statuses but never Trash.
				$post_status    = in_array( $status, $allowed_status, true ) ? array( $status ) : array( 'publish', 'draft', 'private' );

				$args = array(
					'post_type'      => 'mep_events',
					'post_status'    => $post_status,
					'posts_per_page' => $per_page,
					'paged'          => $page,
				);
				if ( $search !== '' ) {
					$args['s'] = $search;
				}
				$meta_query = array();
				if ( $category !== '' ) {
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'mep_cat',
							'field'    => 'slug',
							'terms'    => $category,
						),
					);
				}
				if ( $date_from !== '' ) {
					$meta_query[] = array(
						'key'     => 'event_start_datetime',
						'value'   => $date_from . ' 00:00:00',
						'compare' => '>=',
						'type'    => 'DATETIME',
					);
				}
				if ( $date_to !== '' ) {
					$meta_query[] = array(
						'key'     => 'event_start_datetime',
						'value'   => $date_to . ' 23:59:59',
						'compare' => '<=',
						'type'    => 'DATETIME',
					);
				}
				if ( $active_status === 'active' || $active_status === 'expired' ) {
					$now          = current_time( 'mysql' );
					$meta_query[] = array(
						'key'     => 'event_end_datetime',
						'value'   => $now,
						'compare' => ( $active_status === 'active' ) ? '>=' : '<',
						'type'    => 'DATETIME',
					);
				}
				if ( ! empty( $meta_query ) ) {
					$meta_query['relation'] = 'AND';
					$args['meta_query']     = $meta_query;
				}
				if ( $orderby === 'title' ) {
					$args['orderby'] = 'title';
					$args['order']   = $order;
				} elseif ( $orderby === 'date' ) {
					$args['orderby']  = 'meta_value';
					$args['meta_key'] = 'event_start_datetime';
					$args['meta_type'] = 'DATETIME';
					$args['order']    = $order;
				}

				$query = new WP_Query( $args );
				ob_start();
				render_mep_events_by_status( $query->posts );
				$html = ob_get_clean();

				wp_send_json_success( array(
					'html'      => $html,
					'found'     => (int) $query->found_posts,
					'max_pages' => (int) $query->max_num_pages,
					'page'      => $page,
				) );
			}
			public function mpwem_dashboard_stats() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_nonce' ) ) {
					wp_send_json_error( array( 'message' => 'Security check failed' ) );
				}
				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_send_json_error( array( 'message' => 'Permission denied' ) );
				}
				$order_status       = array( 'wc-completed', 'wc-processing' );
				$total_registration = 0;
				if ( MPWEM_Global_Function::has_woocommerce() ) {
					$completed_orders   = wc_get_orders( array(
						'status' => $order_status,
						'limit'  => - 1,
						'return' => 'ids',
					) );
					$total_registration = count( $completed_orders );
				} else {
					$rsvp_attendees     = get_posts( array(
						'post_type'   => 'mep_events_attendees',
						'numberposts' => - 1,
						'post_status' => 'publish',
					) );
					$total_registration = count( $rsvp_attendees );
				}
				$year       = date( 'Y' );
				$month      = date( 'm' );
				$prev_year  = $year;
				$prev_month = $month - 1;
				if ( (int) $month === 1 ) {
					$prev_month = 12;
					$prev_year  = $year - 1;
				}
				$currency                   = get_woocommerce_currency();
				// get_woocommerce_currency_symbol() returns an HTML entity (e.g. &#36;); decode it
				// so the JSON carries the real glyph and JS .text() renders it correctly.
				$currency_symbol            = html_entity_decode( get_woocommerce_currency_symbol( $currency ), ENT_QUOTES, 'UTF-8' );
				$header_info                = get_monthly_revenue( $year, $month );
				$prev_header_info           = get_monthly_revenue( $prev_year, $prev_month );
				$current_month_revenue      = $header_info['revenue'];
				$current_month_registration = $header_info['each_month_registration'];
				$prev_month_revenue         = $prev_header_info['revenue'];
				$prev_month_registration    = $prev_header_info['each_month_registration'];
				$revenue_percent_change     = get_change_in_percent( $current_month_revenue, $prev_month_revenue );
				$reg_percent_change         = get_change_in_percent( $current_month_registration, $prev_month_registration );

				wp_send_json_success( array(
					'total_registration' => $total_registration,
					'revenue'            => $currency_symbol . ' ' . $current_month_revenue,
					'registration_trend' => '↗ ' . $reg_percent_change['inc_dec_sign'] . '%' . $reg_percent_change['percent_change'] . ' vs last month',
					'revenue_trend'      => sprintf( '↗ %1$s%2$s%% vs last month', $revenue_percent_change['inc_dec_sign'], $revenue_percent_change['percent_change'] ),
				) );
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
				if ( ! $date && is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) {
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
						<?php if ( is_array( $ticket_types ) && sizeof( $ticket_types ) > 0 ) { ?>
                            <tbody>
							<?php
								do_action( 'mpwem_gq_statistics', $event_id, $date );
								foreach ( $ticket_types as $ticket_type ) {
									$ticket_name      = is_array($ticket_type) && array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
									$ticket_qty       = is_array($ticket_type) && array_key_exists( 'option_qty_t', $ticket_type ) ? $ticket_type['option_qty_t'] : 0;
									$ticket_r_qty     = is_array($ticket_type) && array_key_exists( 'option_rsv_t', $ticket_type ) ? $ticket_type['option_rsv_t'] : 0;
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
	function mpwem_active_expire_count() {
		$now    = current_time( 'mysql' );
		$base   = array(
			'post_type'      => 'mep_events',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		);
		$active = new WP_Query( array_merge( $base, array(
			'meta_query' => array(
				array(
					'key'     => 'event_end_datetime',
					'value'   => $now,
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		) ) );
		$expire = new WP_Query( array_merge( $base, array(
			'meta_query' => array(
				array(
					'key'     => 'event_end_datetime',
					'value'   => $now,
					'compare' => '<',
					'type'    => 'DATETIME',
				),
			),
		) ) );
		return array(
			'active_count' => (int) $active->found_posts,
			'expire_count' => (int) $expire->found_posts,
		);
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
				$now             = current_time( 'timestamp' );
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
		$end_date               = date( 'Y-m-t 23:59:59', strtotime( $start_date ) );

		// Native (custom-payment) order revenue for the month. These mep_custom_order
		// records are independent of WooCommerce, so they must be counted whether or
		// not WooCommerce is active. Paid statuses mirror WooCommerce's processing +
		// completed (native "completed" is the post status "publish").
		$native_revenue = mpwem_native_orders_revenue( $start_date, $end_date );

		if ( ! MPWEM_Global_Function::has_woocommerce() ) {
			$rsvp_attendees = get_posts( array(
				'post_type'   => 'mep_events_attendees',
				'numberposts' => -1,
				'post_status' => 'publish',
				'date_query'  => array(
					array(
						'after'     => $start_date,
						'before'    => $end_date,
						'inclusive' => true,
					),
				),
			) );
			return array(
				'revenue'                 => $native_revenue,
				'each_month_registration' => count( $rsvp_attendees ),
			);
		}

		$order_status           = array( 'wc-completed', 'wc-processing' );
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
			'revenue'                 => $total + $native_revenue,
			'each_month_registration' => $each_month_order_count,
		);
	}
	/**
	 * Sum the order totals of paid native (custom-payment) orders created within a
	 * date range. Paid = post status "processing" or "publish" (native Completed),
	 * mirroring WooCommerce's processing + completed revenue statuses.
	 *
	 * @param string $start_date 'Y-m-d H:i:s'
	 * @param string $end_date   'Y-m-d H:i:s'
	 * @return float
	 */
	function mpwem_native_orders_revenue( $start_date, $end_date ) {
		$native_orders = get_posts( array(
			'post_type'      => 'mep_custom_order',
			'post_status'    => array( 'processing', 'publish' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'date_query'     => array(
				array(
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				),
			),
		) );
		$total = 0;
		foreach ( $native_orders as $order_id ) {
			$total += (float) get_post_meta( $order_id, '_mep_order_total', true );
		}
		return $total;
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
		$now            = current_time( 'timestamp' );
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
				$upcoming_date  = get_post_meta( $id, 'event_upcoming_datetime', true );
				$ticket_type    = get_post_meta( $id, 'mep_event_ticket_type', true );
				$location       = get_post_meta( $id, 'mep_location_venue', true );
				$time_remaining = get_time_remaining_fixed( $id, $upcoming_date );
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
				

				$date 			= $event_type == 'no' ? date('Y-m-d H:i', strtotime(get_post_meta( $id, 'event_start_datetime', true ))) : date('Y-m-d H:i', strtotime($date));
				$time =$event_type == 'no' ? date('H:i', strtotime(get_post_meta( $id, 'event_start_datetime', true ))) : $time;
				$total_ticket = MPWEM_Functions::get_total_ticket( $id, $date );
				// $total_sold   = mep_get_event_total_seat_left( $id );
				$total_sold = (int) mep_ticket_type_sold( $event_id, '', $date );
				if ( $event_type === 'everyday' ) {
					$time_remaining    = get_time_remaining_fixed( $id, $upcoming_date );
					$start_date        = date( 'F j, Y', strtotime( $date ) );
					$event_type_status = 'Recurring Event (Repeated)';
					// $total_sold        = mep_get_event_total_seat_left( $id, $date );
					$total_sold = (int) mep_ticket_type_sold( $event_id, '', $date );
				} else if ( $event_type === 'yes' ) {
					$time_remaining    = get_time_remaining_fixed( $id, $upcoming_date );
					$start_date        = date( 'F j, Y', strtotime( $date ) );
					$event_type_status = 'Recurring Event (Selected Dates)';
					// $total_sold        = mep_get_event_total_seat_left( $id, $date );
					$total_sold = (int) mep_ticket_type_sold( $event_id, '', $date );
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
				$now             = current_time( 'timestamp' );
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
                                                <span class="ticket-name"><?php echo is_array($type) && array_key_exists( 'option_name_t', $type ) ? esc_html( $type['option_name_t'] ) : ''; ?></span>
                                                <span class="ticket-price ticket-free"><?php echo isset( $type['option_price_t'] ) ? wc_price(esc_html( $type['option_price_t'] )) : ''; ?></span>
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
							<?php if ( $status === 'trash' ) : ?>
                            <a href="#" class="action-btn mpwem_restore_event" title="<?php esc_attr_e( 'Restore Event', 'mage-eventpress' ); ?>" data-event-id="<?php echo esc_attr( $id ); ?>"><span class="dashicons dashicons-image-rotate"></span></a>
                            <a href="#" class="action-btn mpwem_delete_permanently" title="<?php esc_attr_e( 'Delete Permanently', 'mage-eventpress' ); ?>" data-event-id="<?php echo esc_attr( $id ); ?>"><span class="dashicons dashicons-trash"></span></a>
							<?php else : ?>
                            <a href="<?php echo esc_url( $view_link ); ?>" class="action-btn view" title="View Event"><span class="mi mi-eye"></span></a>
                            <a href="#" class="action-btn quick-edit" title="Quick Edit" data-event-id="<?php echo esc_attr( $id ); ?>"><span class="mi mi-file-edit"></span></a>
                            <a href="<?php echo esc_url( $edit_link ); ?>" class="action-btn edit" title="Edit Event"><span class="mi mi-pencil"></span></a>
                            <a href="#" class="action-btn" data-mpwem_popup_attendee_statistic="mpwem_popup_attendee_statistic" data-event-id="<?php echo esc_attr( $id ); ?>" title="<?php esc_attr_e( 'Attendee Statistics', 'mage-eventpress' ); ?>"><i class="mi mi-stats"></i></a>
                            <a href="<?php echo esc_url( $delete_link ); ?>" class="action-btn delete" title="Delete Event"><span class="mi mi-trash"></span></a>
							<?php endif; ?>
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