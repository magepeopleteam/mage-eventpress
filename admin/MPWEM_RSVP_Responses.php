<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'MPWEM_RSVP_Responses' ) ) {
	class MPWEM_RSVP_Responses {

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_ajax_mep_fetch_rsvp_responses', array( $this, 'ajax_fetch_rsvps' ) );
			add_action( 'wp_ajax_mep_checkin_rsvp', array( $this, 'ajax_checkin_rsvp' ) );
			add_action( 'wp_ajax_mep_bulk_action_rsvp', array( $this, 'ajax_bulk_action' ) );
			add_action( 'admin_init', array( $this, 'export_csv' ) );
		}

		public function add_menu_page() {
			add_submenu_page(
				'edit.php?post_type=mep_events',
				__( 'RSVP Responses', 'mage-eventpress' ),
				__( 'RSVP Responses', 'mage-eventpress' ),
				'manage_options',
				'event-rsvp-responses',
				array( $this, 'render_page' )
			);
		}

		public function enqueue_scripts( $hook ) {
			if ( 'mep_events_page_event-rsvp-responses' !== $hook ) {
				return;
			}
			wp_enqueue_style( 'mpwem-rsvp-admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_rsvp_admin.css', array(), time() );
			wp_enqueue_script( 'mpwem-rsvp-admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_rsvp_admin.js', array( 'jquery' ), time(), true );
			wp_localize_script( 'mpwem-rsvp-admin', 'mep_rsvp_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'mep_rsvp_nonce' ),
				'i18n'     => array(
					'loading'          => __( 'Loading responses…', 'mage-eventpress' ),
					'updating'         => __( 'Updating…', 'mage-eventpress' ),
					'error_load'       => __( 'Could not load RSVP responses.', 'mage-eventpress' ),
					'error_status'     => __( 'Error updating status.', 'mage-eventpress' ),
					'error_bulk'       => __( 'Error applying bulk action.', 'mage-eventpress' ),
					'no_results'       => __( 'No RSVP responses found.', 'mage-eventpress' ),
					'no_results_hint'  => __( 'Try adjusting your filters or search.', 'mage-eventpress' ),
					'check_in'         => __( 'Check In', 'mage-eventpress' ),
					'checked_in'       => __( 'Checked In', 'mage-eventpress' ),
					'not_checked_in'   => __( 'Not Checked In', 'mage-eventpress' ),
					'select_bulk'      => __( 'Please select a bulk action.', 'mage-eventpress' ),
					'select_items'     => __( 'Please select at least one item.', 'mage-eventpress' ),
					'confirm_delete'   => __( 'Are you sure you want to delete the selected RSVPs?', 'mage-eventpress' ),
					'apply'            => __( 'Apply', 'mage-eventpress' ),
					'applying'         => __( 'Applying…', 'mage-eventpress' ),
					/* translators: %d: number of RSVPs */
					'result_one'       => __( '%d response found', 'mage-eventpress' ),
					/* translators: %d: number of RSVPs */
					'result_many'      => __( '%d responses found', 'mage-eventpress' ),
					'of'               => __( 'of', 'mage-eventpress' ),
				),
			) );
		}

		public function render_page() {
			$events = get_posts( array(
				'post_type'      => 'mep_events',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			) );
			?>
			<div class="wrap mep-rsvp-admin-wrap">

				<header class="bde-hero">
					<div class="bde-hero-copy">
						<span class="bde-eyebrow">
							<span class="dashicons dashicons-groups"></span>
							<?php esc_html_e( 'Attendee tools', 'mage-eventpress' ); ?>
						</span>
						<h1 class="bde-title"><?php esc_html_e( 'RSVP Responses', 'mage-eventpress' ); ?></h1>
						<p class="bde-subtitle"><?php esc_html_e( 'Track attendees, manage check-ins, and export RSVP data.', 'mage-eventpress' ); ?></p>
					</div>
					<div class="bde-hero-actions">
						<div class="bde-hero-badge">
							<span class="dashicons dashicons-yes-alt"></span>
							<span><?php esc_html_e( 'RSVP tracking', 'mage-eventpress' ); ?></span>
						</div>
						<a href="#" class="mep-rsvp-btn mep-rsvp-btn-outline mep-export-rsvp-csv">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Export CSV', 'mage-eventpress' ); ?>
						</a>
					</div>
				</header>

				<div class="mep-rsvp-stats">
					<div class="mep-rsvp-stat-card mep-rsvp-stat-total">
						<span class="mep-rsvp-stat-icon dashicons dashicons-tickets-alt"></span>
						<div class="mep-rsvp-stat-info">
							<span class="mep-rsvp-stat-value" id="mep-total-rsvps">—</span>
							<span class="mep-rsvp-stat-label"><?php esc_html_e( 'Total RSVPs', 'mage-eventpress' ); ?></span>
						</div>
					</div>
					<div class="mep-rsvp-stat-card mep-rsvp-stat-checked">
						<span class="mep-rsvp-stat-icon dashicons dashicons-yes-alt"></span>
						<div class="mep-rsvp-stat-info">
							<span class="mep-rsvp-stat-value" id="mep-total-checkedin">—</span>
							<span class="mep-rsvp-stat-label"><?php esc_html_e( 'Checked In', 'mage-eventpress' ); ?></span>
						</div>
					</div>
					<div class="mep-rsvp-stat-card mep-rsvp-stat-pending">
						<span class="mep-rsvp-stat-icon dashicons dashicons-clock"></span>
						<div class="mep-rsvp-stat-info">
							<span class="mep-rsvp-stat-value" id="mep-total-pending">—</span>
							<span class="mep-rsvp-stat-label"><?php esc_html_e( 'Pending', 'mage-eventpress' ); ?></span>
						</div>
					</div>
					<div class="mep-rsvp-stat-card mep-rsvp-stat-rate">
						<span class="mep-rsvp-stat-icon dashicons dashicons-chart-bar"></span>
						<div class="mep-rsvp-stat-info">
							<span class="mep-rsvp-stat-value" id="mep-checkin-rate">—</span>
							<span class="mep-rsvp-stat-label"><?php esc_html_e( 'Check-in Rate', 'mage-eventpress' ); ?></span>
						</div>
					</div>
				</div>

				<div class="mep-rsvp-filter-panel">
					<div class="mep-rsvp-filter-header">
						<span class="dashicons dashicons-filter"></span>
						<strong><?php esc_html_e( 'Filter Responses', 'mage-eventpress' ); ?></strong>
					</div>
					<div class="mep-rsvp-filter-body">
						<div class="mep-rsvp-filter-grid">
							<div class="mep-rsvp-filter-field mep-rsvp-filter-search">
								<label for="mep-rsvp-search"><?php esc_html_e( 'Search', 'mage-eventpress' ); ?></label>
								<div class="mep-rsvp-input-icon">
									<span class="dashicons dashicons-search"></span>
									<input type="search" id="mep-rsvp-search" placeholder="<?php esc_attr_e( 'Name or email…', 'mage-eventpress' ); ?>" autocomplete="off">
								</div>
							</div>
							<div class="mep-rsvp-filter-field">
								<label for="mep-filter-event"><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></label>
								<select id="mep-filter-event">
									<option value=""><?php esc_html_e( 'All Events', 'mage-eventpress' ); ?></option>
									<?php foreach ( $events as $event ) : ?>
										<option value="<?php echo esc_attr( $event->ID ); ?>"><?php echo esc_html( $event->post_title ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="mep-rsvp-filter-field">
								<label for="mep-filter-status"><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></label>
								<select id="mep-filter-status">
									<option value=""><?php esc_html_e( 'All Statuses', 'mage-eventpress' ); ?></option>
									<option value="checked_in"><?php esc_html_e( 'Checked In', 'mage-eventpress' ); ?></option>
									<option value="not_checked_in"><?php esc_html_e( 'Not Checked In', 'mage-eventpress' ); ?></option>
								</select>
							</div>
							<div class="mep-rsvp-filter-actions">
								<button type="button" id="mep-do-search" class="mep-rsvp-btn mep-rsvp-btn-primary">
									<span class="dashicons dashicons-search"></span>
									<?php esc_html_e( 'Search', 'mage-eventpress' ); ?>
								</button>
								<button type="button" id="mep-reset-filters" class="mep-rsvp-btn mep-rsvp-btn-ghost">
									<span class="dashicons dashicons-dismiss"></span>
									<?php esc_html_e( 'Reset', 'mage-eventpress' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="mep-rsvp-table-wrap">
					<div class="mep-rsvp-table-toolbar">
						<span class="mep-rsvp-result-count" id="mep-result-count"><?php esc_html_e( 'Loading…', 'mage-eventpress' ); ?></span>
						<div class="mep-rsvp-bulk-actions">
							<select id="mep-bulk-action-selector">
								<option value="-1"><?php esc_html_e( 'Bulk actions…', 'mage-eventpress' ); ?></option>
								<option value="checkin"><?php esc_html_e( 'Mark Checked In', 'mage-eventpress' ); ?></option>
								<option value="uncheckin"><?php esc_html_e( 'Mark Not Checked In', 'mage-eventpress' ); ?></option>
								<option value="delete"><?php esc_html_e( 'Delete', 'mage-eventpress' ); ?></option>
							</select>
							<button type="button" id="mep-do-bulk-action" class="mep-rsvp-btn mep-rsvp-btn-outline" disabled>
								<?php esc_html_e( 'Apply', 'mage-eventpress' ); ?>
							</button>
							<span class="mep-rsvp-bulk-count" id="mep-bulk-count"></span>
						</div>
					</div>

					<div class="mep-rsvp-table-container">
						<table class="mep-rsvp-table">
							<thead>
								<tr>
									<th class="column-cb">
										<input type="checkbox" id="mep-select-all" aria-label="<?php esc_attr_e( 'Select all', 'mage-eventpress' ); ?>">
									</th>
									<th class="column-name"><?php esc_html_e( 'Attendee', 'mage-eventpress' ); ?></th>
									<th class="column-event"><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></th>
									<th class="column-event-date"><?php esc_html_e( 'Event Date', 'mage-eventpress' ); ?></th>
									<th class="column-qty"><?php esc_html_e( 'Qty', 'mage-eventpress' ); ?></th>
									<th class="column-status"><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></th>
									<th class="column-date"><?php esc_html_e( 'Submitted', 'mage-eventpress' ); ?></th>
									<th class="column-actions"><?php esc_html_e( 'Actions', 'mage-eventpress' ); ?></th>
									<?php do_action( 'mep_rsvp_table_header' ); ?>
								</tr>
							</thead>
							<tbody id="mep-rsvp-table-body">
								<tr>
									<td colspan="8" class="mep-rsvp-loading">
										<span class="mep-rsvp-spinner"></span>
										<?php esc_html_e( 'Loading responses…', 'mage-eventpress' ); ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="mep-rsvp-pagination" id="mep-rsvp-pagination"></div>
				</div>

			</div>
			<?php
		}

		public function ajax_fetch_rsvps() {
			check_ajax_referer( 'mep_rsvp_nonce', 'nonce' );

			$paged    = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
			$search   = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
			$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
			$status   = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

			$args = array(
				'post_type'      => 'mep_rsvp_responses',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'paged'          => $paged,
				'meta_query'     => array(),
			);

			if ( ! empty( $search ) ) {
				$args['s'] = $search;
			}

			if ( $event_id > 0 ) {
				$args['meta_query'][] = array(
					'key'     => 'ea_event_id',
					'value'   => $event_id,
					'compare' => '=',
				);
			}

			if ( 'checked_in' === $status ) {
				$args['meta_query'][] = array(
					'key'     => 'mep_checkin',
					'value'   => 'Yes',
					'compare' => '=',
				);
			} elseif ( 'not_checked_in' === $status ) {
				$args['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'     => 'mep_checkin',
						'value'   => 'No',
						'compare' => '=',
					),
					array(
						'key'     => 'mep_checkin',
						'compare' => 'NOT EXISTS',
					),
				);
			}

			$query = new WP_Query( $args );
			$rsvps = array();

			$checkin_count_args = array(
				'post_type'      => 'mep_rsvp_responses',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'mep_checkin',
						'value'   => 'Yes',
						'compare' => '=',
					),
				),
			);
			if ( $event_id > 0 ) {
				$checkin_count_args['meta_query'][] = array(
					'key'     => 'ea_event_id',
					'value'   => $event_id,
					'compare' => '=',
				);
			}
			$checked_in_posts = new WP_Query( $checkin_count_args );
			$total_checked_in = $checked_in_posts->found_posts;

			$total_all_args = array(
				'post_type'      => 'mep_rsvp_responses',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			);
			if ( $event_id > 0 ) {
				$total_all_args['meta_query'] = array(
					array(
						'key'     => 'ea_event_id',
						'value'   => $event_id,
						'compare' => '=',
					),
				);
			}
			$total_all_query = new WP_Query( $total_all_args );
			$total_all       = $total_all_query->found_posts;

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$id = get_the_ID();

					$name = get_post_meta( $id, 'ea_name', true );
					if ( empty( $name ) ) {
						$name = get_the_title();
					}

					$email = get_post_meta( $id, 'ea_email', true );
					$phone = get_post_meta( $id, 'ea_phone', true );
					$qty   = get_post_meta( $id, 'ea_ticket_qty', true );
					if ( empty( $qty ) ) {
						$qty = 1;
					}

					$e_id       = get_post_meta( $id, 'ea_event_id', true );
					$event_name = get_the_title( $e_id );
					$event_date = get_post_meta( $id, 'ea_event_date', true );

					$checkin    = get_post_meta( $id, 'mep_checkin', true );
					$is_checked = ( 'Yes' === $checkin );

					ob_start();
					do_action( 'mep_rsvp_table_row_actions', $id );
					$extra_actions = ob_get_clean();

					$rsvps[] = array(
						'id'            => $id,
						'name'          => $name,
						'email'         => $email,
						'phone'         => $phone,
						'qty'           => $qty,
						'event_name'    => $event_name,
						'event_date'    => $event_date,
						'date'          => get_the_date(),
						'is_checked_in' => $is_checked,
						'extra_actions' => $extra_actions,
					);
				}
			}
			wp_reset_postdata();

			wp_send_json_success( array(
				'rsvps'         => $rsvps,
				'total_pages'   => $query->max_num_pages,
				'total_items'   => $query->found_posts,
				'total_all'     => $total_all,
				'total_checked' => $total_checked_in,
				'current_page'  => $paged,
			) );
		}

		public function ajax_checkin_rsvp() {
			check_ajax_referer( 'mep_rsvp_nonce', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Permission denied.' );
			}

			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			$status  = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 0;

			if ( $post_id ) {
				update_post_meta( $post_id, 'mep_checkin', $status ? 'Yes' : 'No' );
				wp_send_json_success( 'Status updated' );
			}
			wp_send_json_error( 'Invalid post ID' );
		}

		public function ajax_bulk_action() {
			check_ajax_referer( 'mep_rsvp_nonce', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Permission denied.' );
			}

			$action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( $_POST['bulk_action'] ) : '';
			$ids    = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'intval', $_POST['ids'] ) : array();

			if ( empty( $ids ) ) {
				wp_send_json_error( 'No items selected.' );
			}

			foreach ( $ids as $id ) {
				if ( 'checkin' === $action ) {
					update_post_meta( $id, 'mep_checkin', 'Yes' );
				} elseif ( 'uncheckin' === $action ) {
					update_post_meta( $id, 'mep_checkin', 'No' );
				} elseif ( 'delete' === $action ) {
					wp_delete_post( $id, true );
				}
			}

			wp_send_json_success( 'Bulk action completed' );
		}

		public function export_csv() {
			if ( isset( $_GET['mep_export_rsvps'] ) && current_user_can( 'manage_options' ) ) {

				$args = array(
					'post_type'      => 'mep_rsvp_responses',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'meta_query'     => array(),
				);

				$search   = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
				$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
				$status   = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

				if ( ! empty( $search ) ) {
					$args['s'] = $search;
				}

				if ( $event_id > 0 ) {
					$args['meta_query'][] = array(
						'key'     => 'ea_event_id',
						'value'   => $event_id,
						'compare' => '=',
					);
				}

				if ( 'checked_in' === $status ) {
					$args['meta_query'][] = array(
						'key'     => 'mep_checkin',
						'value'   => 'Yes',
						'compare' => '=',
					);
				} elseif ( 'not_checked_in' === $status ) {
					$args['meta_query'][] = array(
						'relation' => 'OR',
						array(
							'key'     => 'mep_checkin',
							'value'   => 'No',
							'compare' => '=',
						),
						array(
							'key'     => 'mep_checkin',
							'compare' => 'NOT EXISTS',
						),
					);
				}

				$query = new WP_Query( $args );

				if ( ob_get_length() ) {
					ob_end_clean();
				}

				header( 'Content-Type: text/csv; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=rsvp_responses_' . date( 'Y-m-d' ) . '.csv' );

				$output = fopen( 'php://output', 'w' );
				fputcsv( $output, array( 'ID', 'Name', 'Email', 'Phone', 'Quantity', 'Event', 'Event Date', 'Check-in Status', 'Date' ), ',', '"', '\\' );

				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						$id = get_the_ID();

						$name = get_post_meta( $id, 'ea_name', true );
						if ( empty( $name ) ) {
							$name = get_the_title();
						}

						$email = get_post_meta( $id, 'ea_email', true );
						$phone = get_post_meta( $id, 'ea_phone', true );
						$qty   = get_post_meta( $id, 'ea_ticket_qty', true );
						if ( empty( $qty ) ) {
							$qty = 1;
						}

						$e_id        = get_post_meta( $id, 'ea_event_id', true );
						$event_name  = get_the_title( $e_id );
						$checkin     = get_post_meta( $id, 'mep_checkin', true );
						$checkin_str = ( 'Yes' === $checkin ) ? 'Checked In' : 'Not Checked In';
						$event_date  = get_post_meta( $id, 'ea_event_date', true );

						fputcsv( $output, array( $id, $name, $email, $phone, $qty, $event_name, $event_date, $checkin_str, get_the_date() ), ',', '"', '\\' );
					}
				}
				wp_reset_postdata();
				fclose( $output );
				exit;
			}
		}
	}
}
new MPWEM_RSVP_Responses();
