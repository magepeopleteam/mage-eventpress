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
			wp_enqueue_style( 'mpwem-event-edit', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_event_edit.css', array(), time() );
			wp_enqueue_script( 'mpwem-rsvp-admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_rsvp_admin.js', array( 'jquery' ), time(), true );
			wp_localize_script( 'mpwem-rsvp-admin', 'mep_rsvp_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'mep_rsvp_nonce' )
			) );
		}

		public function render_page() {
			?>
			<div class="wrap mep-rsvp-admin-wrap">
				<div class="mep-rsvp-header">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'RSVP Responses', 'mage-eventpress' ); ?></h1>
					<a href="#" class="page-title-action mep-export-rsvp-csv"><?php esc_html_e( 'Export to CSV', 'mage-eventpress' ); ?></a>
				</div>
				
				<div class="mep-rsvp-statistics">
					<div class="mep-stat-box">
						<div class="mep-stat-title"><?php esc_html_e( 'Total RSVPs', 'mage-eventpress' ); ?></div>
						<div class="mep-stat-value" id="mep-total-rsvps">0</div>
					</div>
					<div class="mep-stat-box">
						<div class="mep-stat-title"><?php esc_html_e( 'Checked In', 'mage-eventpress' ); ?></div>
						<div class="mep-stat-value" id="mep-total-checkedin">0</div>
					</div>
				</div>

				<div class="mep-rsvp-toolbar">
					<div class="mep-toolbar-left">
						<select id="mep-bulk-action-selector">
							<option value="-1"><?php esc_html_e( 'Bulk Actions', 'mage-eventpress' ); ?></option>
							<option value="checkin"><?php esc_html_e( 'Mark Checked In', 'mage-eventpress' ); ?></option>
							<option value="uncheckin"><?php esc_html_e( 'Mark Not Checked In', 'mage-eventpress' ); ?></option>
							<option value="delete"><?php esc_html_e( 'Delete', 'mage-eventpress' ); ?></option>
						</select>
						<button id="mep-do-bulk-action" class="button action"><?php esc_html_e( 'Apply', 'mage-eventpress' ); ?></button>
						
						<?php
						$events = get_posts( array(
							'post_type'      => 'mep_events',
							'posts_per_page' => -1,
							'post_status'    => 'publish',
						) );
						?>
						<select id="mep-filter-event">
							<option value=""><?php esc_html_e( 'All Events', 'mage-eventpress' ); ?></option>
							<?php foreach ( $events as $event ) : ?>
								<option value="<?php echo esc_attr( $event->ID ); ?>"><?php echo esc_html( $event->post_title ); ?></option>
							<?php endforeach; ?>
						</select>

						<select id="mep-filter-status">
							<option value=""><?php esc_html_e( 'All Statuses', 'mage-eventpress' ); ?></option>
							<option value="checked_in"><?php esc_html_e( 'Checked In', 'mage-eventpress' ); ?></option>
							<option value="not_checked_in"><?php esc_html_e( 'Not Checked In', 'mage-eventpress' ); ?></option>
						</select>
					</div>
					<div class="mep-toolbar-right">
						<input type="search" id="mep-rsvp-search" placeholder="<?php esc_attr_e( 'Search by Name or Email', 'mage-eventpress' ); ?>">
						<button id="mep-do-search" class="button"><?php esc_html_e( 'Search', 'mage-eventpress' ); ?></button>
					</div>
				</div>

				<div class="mep-rsvp-table-container">
					<table class="wp-list-table widefat fixed striped mep-rsvp-table">
						<thead>
							<tr>
								<td class="manage-column column-cb check-column">
									<input type="checkbox" id="mep-select-all">
								</td>
								<th class="column-name"><?php esc_html_e( 'Attendee', 'mage-eventpress' ); ?></th>
								<th class="column-event"><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></th>
								<th class="column-event-date"><?php esc_html_e( 'Event Date', 'mage-eventpress' ); ?></th>
								<th class="column-qty"><?php esc_html_e( 'Qty', 'mage-eventpress' ); ?></th>
								<th class="column-status"><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></th>
								<th class="column-date"><?php esc_html_e( 'Date', 'mage-eventpress' ); ?></th>
								<th class="column-actions"><?php esc_html_e( 'Actions', 'mage-eventpress' ); ?></th>
								<?php do_action('mep_rsvp_table_header'); ?>
							</tr>
						</thead>
						<tbody id="mep-rsvp-table-body">
							<tr>
								<td colspan="8" class="mep-loading-msg"><?php esc_html_e( 'Loading...', 'mage-eventpress' ); ?></td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td class="manage-column column-cb check-column">
									<input type="checkbox" id="mep-select-all-footer">
								</td>
								<th class="column-name"><?php esc_html_e( 'Attendee', 'mage-eventpress' ); ?></th>
								<th class="column-event"><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></th>
								<th class="column-event-date"><?php esc_html_e( 'Event Date', 'mage-eventpress' ); ?></th>
								<th class="column-qty"><?php esc_html_e( 'Qty', 'mage-eventpress' ); ?></th>
								<th class="column-status"><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></th>
								<th class="column-date"><?php esc_html_e( 'Date', 'mage-eventpress' ); ?></th>
								<th class="column-actions"><?php esc_html_e( 'Actions', 'mage-eventpress' ); ?></th>
								<?php do_action('mep_rsvp_table_footer'); ?>
							</tr>
						</tfoot>
					</table>
				</div>
				<div class="mep-rsvp-pagination tablenav-pages">
					<!-- Pagination injected via JS -->
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
				'meta_query'     => array()
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
					)
				);
			}

			$query = new WP_Query( $args );
			$rsvps = array();

			$total_checked_in = 0;

			// Quick count of overall checked in (approximate, since it runs a separate query if needed)
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
					)
				)
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

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$id = get_the_ID();
					
					$name       = get_post_meta( $id, 'ea_name', true );
					if ( empty($name) ) $name = get_the_title();

					$email      = get_post_meta( $id, 'ea_email', true );
					$phone      = get_post_meta( $id, 'ea_phone', true ); // Or 'mep_phone'
					$qty        = get_post_meta( $id, 'ea_ticket_qty', true );
					if ( empty($qty) ) $qty = 1;

					$e_id       = get_post_meta( $id, 'ea_event_id', true );
					$event_name = get_the_title( $e_id );
					$event_date = get_post_meta( $id, 'ea_event_date', true );
					
					$checkin    = get_post_meta( $id, 'mep_checkin', true );
					$is_checked = ( 'Yes' === $checkin );

					ob_start();
					do_action('mep_rsvp_table_row_actions', $id);
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
						'extra_actions' => $extra_actions
					);
				}
			}
			wp_reset_postdata();

			wp_send_json_success( array(
				'rsvps'         => $rsvps,
				'total_pages'   => $query->max_num_pages,
				'total_items'   => $query->found_posts,
				'total_checked' => $total_checked_in,
				'current_page'  => $paged
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
					wp_delete_post( $id, true ); // force delete
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
					'meta_query'     => array()
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
						)
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
						
						$name       = get_post_meta( $id, 'ea_name', true );
						if ( empty($name) ) $name = get_the_title();

						$email      = get_post_meta( $id, 'ea_email', true );
						$phone      = get_post_meta( $id, 'ea_phone', true ); 
						$qty        = get_post_meta( $id, 'ea_ticket_qty', true );
						if ( empty($qty) ) $qty = 1;

						$e_id       = get_post_meta( $id, 'ea_event_id', true );
						$event_name = get_the_title( $e_id );
						
						$checkin    = get_post_meta( $id, 'mep_checkin', true );
						$checkin_str = ( 'Yes' === $checkin ) ? 'Checked In' : 'Not Checked In';

						$event_date = get_post_meta( $id, 'ea_event_date', true );
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
