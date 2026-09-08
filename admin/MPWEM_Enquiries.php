<?php
	/*
	 * Events -> Enquiries.
	 *
	 * Lists the enquiries ("queries") visitors submit from Announcement-mode events
	 * (MPWEM_Hooks::mep_submit_enquiry). Server-rendered rather than AJAX-driven: the
	 * screen only needs filter / paginate / mark-read / delete / export, and reuses the
	 * RSVP Responses stylesheet so both attendee screens look the same.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Enquiries' ) ) {
		class MPWEM_Enquiries {

			const POST_TYPE  = 'mep_event_enquiry';
			const MENU_SLUG  = 'event-enquiries';
			const PER_PAGE   = 20;

			public function __construct() {
				add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'admin_init', array( $this, 'handle_actions' ) );
			}

			private static function capability() {
				return MPWEM_Global_Function::get_shop_manager_capability();
			}

			public function add_menu_page() {
				add_submenu_page(
					'edit.php?post_type=mep_events',
					__( 'Enquiries', 'mage-eventpress' ),
					__( 'Enquiries', 'mage-eventpress' ),
					self::capability(),
					self::MENU_SLUG,
					array( $this, 'render_page' )
				);
			}

			public function enqueue_scripts( $hook ) {
				if ( 'mep_events_page_' . self::MENU_SLUG !== $hook ) {
					return;
				}
				wp_enqueue_style( 'mpwem-rsvp-admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_rsvp_admin.css', array(), MPWEM_PLUGIN_VERSION );
			}

			/** Current screen URL with the list filters preserved. */
			private function base_url( array $extra = array() ) {
				$args = array(
					'post_type' => 'mep_events',
					'page'      => self::MENU_SLUG,
				);
				foreach ( array( 'event_id', 'enquiry_status', 's', 'paged' ) as $key ) {
					if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) {
						$args[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
					}
				}
				$args = array_merge( $args, $extra );
				return add_query_arg( array_filter( $args, static function ( $v ) { return '' !== $v && null !== $v; } ), admin_url( 'edit.php' ) );
			}

			/**
			 * Row actions (mark read / unread / delete) and the CSV export.
			 * Every one of them is nonce-checked and capability-checked.
			 */
			public function handle_actions() {
				if ( ! isset( $_GET['page'] ) || self::MENU_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
					return;
				}
				$action = isset( $_GET['mpwem_action'] ) ? sanitize_key( wp_unslash( $_GET['mpwem_action'] ) ) : '';
				if ( '' === $action ) {
					return;
				}
				if ( ! current_user_can( self::capability() ) ) {
					wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'mage-eventpress' ) );
				}

				if ( 'export' === $action ) {
					check_admin_referer( 'mpwem_enquiry_export' );
					$this->export_csv();
					return;
				}

				$enquiry_id = isset( $_GET['enquiry_id'] ) ? absint( $_GET['enquiry_id'] ) : 0;
				if ( ! $enquiry_id || self::POST_TYPE !== get_post_type( $enquiry_id ) ) {
					return;
				}
				check_admin_referer( 'mpwem_enquiry_' . $action . '_' . $enquiry_id );

				if ( 'read' === $action || 'unread' === $action ) {
					update_post_meta( $enquiry_id, 'mep_enquiry_status_flag', 'read' === $action ? 'read' : 'new' );
				} elseif ( 'delete' === $action ) {
					wp_delete_post( $enquiry_id, true );
				}

				wp_safe_redirect( $this->base_url( array( 'mpwem_done' => $action ) ) );
				exit;
			}

			/** Build the WP_Query args shared by the table and the CSV export. */
			private function query_args( $per_page = self::PER_PAGE, $paged = 1 ) {
				$event_id = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;
				$status   = isset( $_GET['enquiry_status'] ) ? sanitize_key( wp_unslash( $_GET['enquiry_status'] ) ) : '';
				$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

				$args = array(
					'post_type'      => self::POST_TYPE,
					'post_status'    => 'publish',
					'posts_per_page' => $per_page,
					'paged'          => max( 1, (int) $paged ),
					'orderby'        => 'date',
					'order'          => 'DESC',
				);
				if ( '' !== $search ) {
					$args['s'] = $search;
				}
				$meta_query = array();
				if ( $event_id > 0 ) {
					$meta_query[] = array( 'key' => 'mep_enquiry_event_id', 'value' => $event_id, 'compare' => '=' );
				}
				if ( 'read' === $status ) {
					$meta_query[] = array( 'key' => 'mep_enquiry_status_flag', 'value' => 'read', 'compare' => '=' );
				} elseif ( 'new' === $status ) {
					$meta_query[] = array(
						'relation' => 'OR',
						array( 'key' => 'mep_enquiry_status_flag', 'value' => 'new', 'compare' => '=' ),
						array( 'key' => 'mep_enquiry_status_flag', 'compare' => 'NOT EXISTS' ),
					);
				}
				if ( ! empty( $meta_query ) ) {
					$args['meta_query'] = $meta_query;
				}
				return $args;
			}

			private function export_csv() {
				$args           = $this->query_args( -1, 1 );
				$args['fields'] = 'ids';
				$ids            = get_posts( $args );

				nocache_headers();
				header( 'Content-Type: text/csv; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=event-enquiries-' . gmdate( 'Y-m-d' ) . '.csv' );

				$out = fopen( 'php://output', 'w' );
				fputcsv( $out, array(
					__( 'Date', 'mage-eventpress' ),
					__( 'Event', 'mage-eventpress' ),
					__( 'Name', 'mage-eventpress' ),
					__( 'Email', 'mage-eventpress' ),
					__( 'Phone', 'mage-eventpress' ),
					__( 'Subject', 'mage-eventpress' ),
					__( 'Message', 'mage-eventpress' ),
					__( 'Status', 'mage-eventpress' ),
				), ',', '"', '\\' );
				foreach ( $ids as $id ) {
					$event_id = (int) get_post_meta( $id, 'mep_enquiry_event_id', true );
					fputcsv( $out, array(
						get_the_date( 'Y-m-d H:i', $id ),
						$event_id ? get_the_title( $event_id ) : '',
						(string) get_post_meta( $id, 'mep_enquiry_name', true ),
						(string) get_post_meta( $id, 'mep_enquiry_email', true ),
						(string) get_post_meta( $id, 'mep_enquiry_phone', true ),
						(string) get_post_meta( $id, 'mep_enquiry_subject', true ),
						(string) get_post_meta( $id, 'mep_enquiry_message', true ),
						'read' === get_post_meta( $id, 'mep_enquiry_status_flag', true ) ? __( 'Read', 'mage-eventpress' ) : __( 'New', 'mage-eventpress' ),
					), ',', '"', '\\' );
				}
				fclose( $out );
				exit;
			}

			/** Count of enquiries matching one status filter (ignores the other filters). */
			private function count_by_status( $status ) {
				$args = array(
					'post_type'      => self::POST_TYPE,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'fields'         => 'ids',
				);
				if ( 'read' === $status ) {
					$args['meta_query'] = array( array( 'key' => 'mep_enquiry_status_flag', 'value' => 'read', 'compare' => '=' ) );
				} elseif ( 'new' === $status ) {
					$args['meta_query'] = array(
						'relation' => 'OR',
						array( 'key' => 'mep_enquiry_status_flag', 'value' => 'new', 'compare' => '=' ),
						array( 'key' => 'mep_enquiry_status_flag', 'compare' => 'NOT EXISTS' ),
					);
				}
				$q = new WP_Query( $args );
				return (int) $q->found_posts;
			}

			public function render_page() {
				if ( ! current_user_can( self::capability() ) ) {
					wp_die( esc_html__( 'Sorry, you are not allowed to view this page.', 'mage-eventpress' ) );
				}

				$paged   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
				$query   = new WP_Query( $this->query_args( self::PER_PAGE, $paged ) );
				$total   = (int) $query->found_posts;
				$pages   = (int) $query->max_num_pages;

				$sel_event  = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;
				$sel_status = isset( $_GET['enquiry_status'] ) ? sanitize_key( wp_unslash( $_GET['enquiry_status'] ) ) : '';
				$search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

				// Only events that can actually receive an enquiry are worth filtering by.
				$events = get_posts( array(
					'post_type'      => 'mep_events',
					'posts_per_page' => -1,
					'post_status'    => array( 'publish', 'draft' ),
					'orderby'        => 'title',
					'order'          => 'ASC',
					'meta_query'     => array( array( 'key' => 'mep_reg_status', 'value' => 'announcement', 'compare' => '=' ) ),
				) );
				?>
				<div class="wrap mep-rsvp-admin-wrap">
					<header class="bde-hero">
						<div class="bde-hero-copy">
							<span class="bde-eyebrow">
								<span class="dashicons dashicons-megaphone"></span>
								<?php esc_html_e( 'Announcement tools', 'mage-eventpress' ); ?>
							</span>
							<h1 class="bde-title"><?php esc_html_e( 'Enquiries', 'mage-eventpress' ); ?></h1>
							<p class="bde-subtitle"><?php esc_html_e( 'Questions visitors sent from the enquiry form on your announcement events.', 'mage-eventpress' ); ?></p>
						</div>
						<div class="bde-hero-actions">
							<a href="<?php echo esc_url( wp_nonce_url( $this->base_url( array( 'mpwem_action' => 'export' ) ), 'mpwem_enquiry_export' ) ); ?>" class="mep-rsvp-btn mep-rsvp-btn-outline">
								<span class="dashicons dashicons-download"></span>
								<?php esc_html_e( 'Export CSV', 'mage-eventpress' ); ?>
							</a>
						</div>
					</header>

					<?php if ( isset( $_GET['mpwem_done'] ) ) : ?>
						<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Enquiry updated.', 'mage-eventpress' ); ?></p></div>
					<?php endif; ?>

					<div class="mep-rsvp-stats">
						<div class="mep-rsvp-stat-card mep-rsvp-stat-total">
							<span class="mep-rsvp-stat-icon dashicons dashicons-email"></span>
							<div class="mep-rsvp-stat-info">
								<span class="mep-rsvp-stat-value"><?php echo esc_html( $this->count_by_status( '' ) ); ?></span>
								<span class="mep-rsvp-stat-label"><?php esc_html_e( 'Total Enquiries', 'mage-eventpress' ); ?></span>
							</div>
						</div>
						<div class="mep-rsvp-stat-card mep-rsvp-stat-pending">
							<span class="mep-rsvp-stat-icon dashicons dashicons-bell"></span>
							<div class="mep-rsvp-stat-info">
								<span class="mep-rsvp-stat-value"><?php echo esc_html( $this->count_by_status( 'new' ) ); ?></span>
								<span class="mep-rsvp-stat-label"><?php esc_html_e( 'New', 'mage-eventpress' ); ?></span>
							</div>
						</div>
						<div class="mep-rsvp-stat-card mep-rsvp-stat-checked">
							<span class="mep-rsvp-stat-icon dashicons dashicons-yes-alt"></span>
							<div class="mep-rsvp-stat-info">
								<span class="mep-rsvp-stat-value"><?php echo esc_html( $this->count_by_status( 'read' ) ); ?></span>
								<span class="mep-rsvp-stat-label"><?php esc_html_e( 'Read', 'mage-eventpress' ); ?></span>
							</div>
						</div>
					</div>

					<div class="mep-rsvp-filter-panel">
						<form method="get" class="mep-rsvp-filter-body">
							<input type="hidden" name="post_type" value="mep_events"/>
							<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU_SLUG ); ?>"/>
							<div class="mep-rsvp-filter-grid">
								<div class="mep-rsvp-filter-field">
									<label for="mpwem_enquiry_event"><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></label>
									<select id="mpwem_enquiry_event" name="event_id">
										<option value=""><?php esc_html_e( 'All events', 'mage-eventpress' ); ?></option>
										<?php foreach ( $events as $event ) : ?>
											<option value="<?php echo esc_attr( $event->ID ); ?>" <?php selected( $sel_event, $event->ID ); ?>><?php echo esc_html( $event->post_title ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="mep-rsvp-filter-field">
									<label for="mpwem_enquiry_status"><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></label>
									<select id="mpwem_enquiry_status" name="enquiry_status">
										<option value=""><?php esc_html_e( 'All statuses', 'mage-eventpress' ); ?></option>
										<option value="new" <?php selected( $sel_status, 'new' ); ?>><?php esc_html_e( 'New', 'mage-eventpress' ); ?></option>
										<option value="read" <?php selected( $sel_status, 'read' ); ?>><?php esc_html_e( 'Read', 'mage-eventpress' ); ?></option>
									</select>
								</div>
								<div class="mep-rsvp-filter-field">
									<label for="mpwem_enquiry_search"><?php esc_html_e( 'Search', 'mage-eventpress' ); ?></label>
									<input type="search" id="mpwem_enquiry_search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Name or event…', 'mage-eventpress' ); ?>"/>
								</div>
							</div>
							<div class="mep-rsvp-filter-actions">
								<button type="submit" class="mep-rsvp-btn mep-rsvp-btn-primary"><?php esc_html_e( 'Filter', 'mage-eventpress' ); ?></button>
								<a href="<?php echo esc_url( add_query_arg( array( 'post_type' => 'mep_events', 'page' => self::MENU_SLUG ), admin_url( 'edit.php' ) ) ); ?>" class="mep-rsvp-btn mep-rsvp-btn-ghost"><?php esc_html_e( 'Reset', 'mage-eventpress' ); ?></a>
							</div>
						</form>
					</div>

					<div class="mep-rsvp-table-container">
						<div class="mep-rsvp-table-toolbar">
							<span class="mep-rsvp-result-count">
								<?php
									printf(
										/* translators: %s: number of enquiries. */
										esc_html( _n( '%s enquiry found', '%s enquiries found', $total, 'mage-eventpress' ) ),
										esc_html( number_format_i18n( $total ) )
									);
								?>
							</span>
						</div>
						<div class="mep-rsvp-table-wrap">
							<table class="mep-rsvp-table widefat">
								<thead>
								<tr>
									<th><?php esc_html_e( 'Date', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'From', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Subject & Message', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'mage-eventpress' ); ?></th>
								</tr>
								</thead>
								<tbody>
								<?php if ( ! $query->have_posts() ) : ?>
									<tr>
										<td colspan="6" class="mep-rsvp-empty">
											<div class="mep-rsvp-empty-inner">
												<span class="dashicons dashicons-email"></span>
												<p><?php esc_html_e( 'No enquiries found.', 'mage-eventpress' ); ?></p>
												<p><?php esc_html_e( 'Enquiries arrive here when a visitor uses the form on an Announcement-mode event.', 'mage-eventpress' ); ?></p>
											</div>
										</td>
									</tr>
								<?php else : ?>
									<?php
									while ( $query->have_posts() ) :
										$query->the_post();
										$id       = get_the_ID();
										$event_id = (int) get_post_meta( $id, 'mep_enquiry_event_id', true );
										$name     = (string) get_post_meta( $id, 'mep_enquiry_name', true );
										$email    = (string) get_post_meta( $id, 'mep_enquiry_email', true );
										$phone    = (string) get_post_meta( $id, 'mep_enquiry_phone', true );
										$subject  = (string) get_post_meta( $id, 'mep_enquiry_subject', true );
										$message  = (string) get_post_meta( $id, 'mep_enquiry_message', true );
										$is_read  = 'read' === get_post_meta( $id, 'mep_enquiry_status_flag', true );
										?>
										<tr>
											<td><?php echo esc_html( get_the_date( '', $id ) . ' ' . get_the_time( '', $id ) ); ?></td>
											<td>
												<?php if ( $event_id ) : ?>
													<a class="mep-rsvp-event-name" href="<?php echo esc_url( get_edit_post_link( $event_id ) ); ?>"><?php echo esc_html( get_the_title( $event_id ) ); ?></a>
												<?php else : ?>
													&mdash;
												<?php endif; ?>
											</td>
											<td>
												<div class="mep-rsvp-attendee">
													<strong><?php echo esc_html( $name ); ?></strong>
													<span class="mep-rsvp-attendee-meta">
														<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
														<?php if ( '' !== $phone ) : ?>
															<br/><?php echo esc_html( $phone ); ?>
														<?php endif; ?>
													</span>
												</div>
											</td>
											<td>
												<strong><?php echo esc_html( $subject ); ?></strong>
												<p style="margin:4px 0 0;white-space:pre-wrap;"><?php echo esc_html( $message ); ?></p>
											</td>
											<td>
												<span class="mep-rsvp-badge <?php echo $is_read ? 'mep-rsvp-badge-success' : 'mep-rsvp-badge-warning'; ?>">
													<?php echo $is_read ? esc_html__( 'Read', 'mage-eventpress' ) : esc_html__( 'New', 'mage-eventpress' ); ?>
												</span>
											</td>
											<td>
												<?php $toggle = $is_read ? 'unread' : 'read'; ?>
												<a class="mep-rsvp-btn mep-rsvp-btn-ghost" href="<?php echo esc_url( wp_nonce_url( $this->base_url( array( 'mpwem_action' => $toggle, 'enquiry_id' => $id ) ), 'mpwem_enquiry_' . $toggle . '_' . $id ) ); ?>">
													<?php echo $is_read ? esc_html__( 'Mark as new', 'mage-eventpress' ) : esc_html__( 'Mark as read', 'mage-eventpress' ); ?>
												</a>
												<a class="mep-rsvp-btn mep-rsvp-btn-ghost" style="color:#b32d2e;" onclick="return confirm('<?php echo esc_js( __( 'Delete this enquiry permanently?', 'mage-eventpress' ) ); ?>');" href="<?php echo esc_url( wp_nonce_url( $this->base_url( array( 'mpwem_action' => 'delete', 'enquiry_id' => $id ) ), 'mpwem_enquiry_delete_' . $id ) ); ?>">
													<?php esc_html_e( 'Delete', 'mage-eventpress' ); ?>
												</a>
											</td>
										</tr>
									<?php endwhile; ?>
									<?php wp_reset_postdata(); ?>
								<?php endif; ?>
								</tbody>
							</table>
						</div>

						<?php if ( $pages > 1 ) : ?>
							<div class="mep-rsvp-pagination">
								<?php
									echo wp_kses_post( paginate_links( array(
										'base'      => str_replace( 999999999, '%#%', esc_url( $this->base_url( array( 'paged' => 999999999 ) ) ) ),
										'format'    => '',
										'current'   => $paged,
										'total'     => $pages,
										'prev_text' => '&laquo;',
										'next_text' => '&raquo;',
									) ) );
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}
		}

		new MPWEM_Enquiries();
	}
