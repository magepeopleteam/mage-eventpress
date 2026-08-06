<?php
	/*
	 * @Author 		a.a.mahin@gmail.com
	 * Copyright: 	mage-people.com
	 *
	 * Free-tier Attendee List: deliberately limited to an Event filter, a fixed
	 * set of columns, and read-only rows — every privileged action (view/edit/
	 * sync/delete, CSV export, extra filters, column settings) is rendered
	 * disabled with a "PRO" upsell and has no server-side handler behind it here.
	 *
	 * This class must never grow the code that performs those actions — that
	 * lives only in PRO's own MPWEM_Passenger_List, which registers the exact
	 * same 'attendee_list' submenu slug and simply takes over (see
	 * passenger_menu() below) the moment PRO is active, so upgrading is seamless
	 * and there is nothing in this file a developer could re-enable to unlock
	 * PRO behaviour without PRO actually being installed.
	 *
	 * The page shares its visual design and pagination component with the
	 * Event Orders page (assets/admin/mep-orders-page.css/.mep-orders-*,
	 * .mep-filter-*, .mep-page-btn* classes) so both admin screens look and
	 * behave the same.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Attendee_List_Free' ) ) {
		class MPWEM_Attendee_List_Free {
			const PRO_PLUGIN = 'mage-eventpress-pro/woocommerce-event-manager-pro.php';
			const PER_PAGE   = 20;

			public function __construct() {
				add_action( 'admin_menu', array( $this, 'passenger_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
				add_action( 'wp_ajax_mep_free_attendee_list_filter', array( $this, 'ajax_filter' ) );
			}

			public function passenger_menu() {
				if ( mep_check_plugin_installed( self::PRO_PLUGIN ) ) {
					return; // PRO registers the full-featured page on this same slug.
				}
				$cpt = MPWEM_Functions::get_cpt();
				add_submenu_page(
					'edit.php?post_type=' . $cpt,
					__( 'Attendee List', 'mage-eventpress' ),
					__( 'Attendee List', 'mage-eventpress' ),
					// Deliberately not get_admin_capability(): that falls back to the bare
					// 'edit_posts' cap (Contributor-level) when WooCommerce is inactive, and
					// this page lists every attendee's name/email/phone across every event
					// site-wide with no per-event ownership check. manage_options matches the
					// sibling Event Orders page added alongside this one.
					'manage_options',
					'attendee_list',
					array( $this, 'render_page' )
				);
			}

			public function enqueue_assets( $hook ) {
				if ( strpos( $hook, 'attendee_list' ) === false ) {
					return;
				}
				if ( mep_check_plugin_installed( self::PRO_PLUGIN ) ) {
					return; // PRO renders this page now and enqueues its own assets.
				}
				// Reuse the Event Orders page's stylesheet so both admin screens
				// share an identical design system (header, filter panel, table,
				// pagination) instead of a second, drifting copy of the same CSS.
				wp_enqueue_style(
					'mep-orders-page',
					MPWEM_PLUGIN_URL . '/assets/admin/mep-orders-page.css',
					array(),
					'1.3.6'
				);
			}

			private function normalize_args( $args = array() ) {
				$args     = is_array( $args ) ? $args : array();
				$page     = isset( $args['page'] ) ? absint( $args['page'] ) : 1;
				$post_id  = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
				$per_page = isset( $args['post_per_page'] ) ? absint( $args['post_per_page'] ) : self::PER_PAGE;

				return array(
					'page'          => max( 1, $page ),
					'post_id'       => $post_id,
					'post_per_page' => $per_page > 0 ? $per_page : self::PER_PAGE,
				);
			}

			public function render_page() {
				$label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				?>
				<div class="wrap">
					<div class="mep-orders-wrap mep-free-attlist">

						<!-- Header -->
						<div class="mep-orders-header">
							<div class="mep-orders-title-group">
								<h1 class="mep-orders-title">
									<span class="dashicons dashicons-groups"></span>
									<?php esc_html_e( 'Attendee List', 'mage-eventpress' ); ?>
								</h1>
								<p class="mep-orders-subtitle"><?php esc_html_e( 'View attendees registered across all your events.', 'mage-eventpress' ); ?></p>
							</div>
							<div class="mep-orders-header-actions">
								<button type="button" class="mep-btn mep-btn-outline" disabled title="<?php esc_attr_e( 'Available in PRO version', 'mage-eventpress' ); ?>">
									<span class="dashicons dashicons-download"></span>
									<?php esc_html_e( 'Export CSV', 'mage-eventpress' ); ?>
									<span class="mep-free-attlist-pro-flag"><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'PRO', 'mage-eventpress' ); ?></span>
								</button>
							</div>
						</div>

						<!-- Filter Panel -->
						<div class="mep-filter-panel">
							<div class="mep-filter-panel-header">
								<span class="dashicons dashicons-filter"></span>
								<strong><?php esc_html_e( 'Filter Attendees', 'mage-eventpress' ); ?></strong>
								<button type="button" id="mep-attlist-filter-toggle" class="mep-filter-toggle" aria-expanded="true">
									<span class="dashicons dashicons-arrow-up-alt2"></span>
								</button>
							</div>
							<div class="mep-filter-body" id="mep-attlist-filter-body">
								<div class="mep-filter-grid">
									<div class="mep-filter-field">
										<label for="mep_free_attlist_event">
											<?php
												/* translators: %s: the configured "Events" label */
												printf( esc_html__( 'Filter by %s', 'mage-eventpress' ), esc_html( $label ) );
											?>
										</label>
										<select id="mep_free_attlist_event">
											<option value="0"><?php esc_html_e( 'All Events', 'mage-eventpress' ); ?></option>
											<?php
												$post_ids = MPWEM_Query::get_all_post_ids( 'mep_events' );
												if ( is_array( $post_ids ) ) {
													foreach ( $post_ids as $post_id ) {
														?>
														<option value="<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></option>
														<?php
													}
												}
											?>
										</select>
									</div>
								</div><!-- .mep-filter-grid -->
								<div class="mep-filter-actions">
									<button type="button" class="mep-btn mep-btn-primary" id="mep_free_attlist_filter_btn">
										<span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Filter', 'mage-eventpress' ); ?>
									</button>
									<?php $upgrade_url = apply_filters( 'mep_pro_upgrade_url', 'https://mage-people.com/', 0 ); ?>
									<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="mep-free-attlist-upgrade">
										<?php esc_html_e( 'More filters, columns & actions in PRO', 'mage-eventpress' ); ?> &rarr;
									</a>
								</div>
							</div><!-- .mep-filter-body -->
						</div><!-- .mep-filter-panel -->

						<div id="mep_free_attlist_result">
							<?php $this->render_result(); ?>
						</div>
					</div>
				</div>
				<input type="hidden" id="mep_free_attlist_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mpwem_admin_nonce' ) ); ?>"/>
				<?php $this->print_assets(); ?>
				<?php
			}

			public function render_result( $args = array() ) {
				$args  = $this->normalize_args( $args );
				$query = MPWEM_Query::attendee_query( $args, $args['post_per_page'], $args['page'] );
				$total = (int) $query->post_count;

				if ( $args['page'] > 1 && $total === 0 ) {
					// Mirror the PRO list's out-of-range clamp: WordPress returns
					// found_posts = 0 / max_num_pages = 0 for a page beyond the real
					// range, so probe page 1 to discover the real last page.
					$probe     = MPWEM_Query::attendee_query( $args, $args['post_per_page'], 1 );
					$last_page = (int) $probe->max_num_pages;
					if ( $last_page > 0 && $args['page'] > $last_page ) {
						$args['page'] = $last_page;
					} else {
						$args['page'] = 1;
					}
					$query = MPWEM_Query::attendee_query( $args, $args['post_per_page'], $args['page'] );
					$total = (int) $query->post_count;
				}

				$found_posts = (int) $query->found_posts;
				$pages       = max( 1, (int) $query->max_num_pages );
				?>
				<div class="mep-table-wrap">
					<div class="mep-table-toolbar">
						<span class="mep-result-count">
							<?php
								printf(
									/* translators: %d: total number of attendees found */
									esc_html( _n( '%d attendee found', '%d attendees found', $found_posts, 'mage-eventpress' ) ),
									esc_html( $found_posts )
								);
							?>
						</span>
					</div>

					<div id="mep-attlist-table-container">
						<table class="mep-orders-table">
							<thead>
							<tr>
								<th><?php esc_html_e( 'SI.', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Order No', 'mage-eventpress' ); ?></th>
								<th><?php echo esc_html( MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' ) ); ?></th>
								<th><?php esc_html_e( 'Ticket', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Full Name', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Email', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Phone', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Event Datetime', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Order Status', 'mage-eventpress' ); ?></th>
								<th class="mep-free-attlist-action-head-cell">
									<div class="mep-free-attlist-action-head">
										<span><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></span>
										<span class="mep-free-attlist-pro-flag" title="<?php esc_attr_e( 'Available in PRO version', 'mage-eventpress' ); ?>">
											<span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'PRO', 'mage-eventpress' ); ?>
										</span>
									</div>
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								if ( $total > 0 ) {
									$count = ( max( 1, $args['page'] ) - 1 ) * $args['post_per_page'] + 1;
									foreach ( $query->posts as $attendee ) {
										$this->render_row( $attendee->ID, $count );
										$count ++;
									}
								} else {
									?>
									<tr>
										<td colspan="10" class="mep-no-orders">
											<span class="dashicons dashicons-groups"></span>
											<p><?php esc_html_e( 'No attendees found.', 'mage-eventpress' ); ?></p>
										</td>
									</tr>
									<?php
								}
							?>
							</tbody>
						</table>
					</div><!-- #mep-attlist-table-container -->

					<div class="mep-pagination" id="mep-attlist-pagination">
						<?php $this->render_pagination( $args['page'], $pages, $found_posts, $args['post_per_page'] ); ?>
					</div>
				</div><!-- .mep-table-wrap -->
				<?php
			}

			private function render_pagination( $current, $total_pages, $total_items, $per_page ) {
				$start = ( ( $current - 1 ) * $per_page ) + 1;
				$end   = min( $current * $per_page, $total_items );
				?>
				<div class="mep-pagination-info">
					<?php
						if ( $total_items > 0 ) {
							printf( esc_html__( 'Showing %1$d–%2$d of %3$d attendees', 'mage-eventpress' ), $start, $end, $total_items );
						}
					?>
				</div>

				<?php if ( $total_pages > 1 ) : ?>
				<div class="mep-pagination-links">
					<!-- First -->
					<button type="button" class="mep-page-btn mep-page-edge <?php echo $current <= 1 ? 'mep-page-btn-disabled' : ''; ?>"
						data-page="1" <?php echo $current <= 1 ? 'disabled' : ''; ?> title="<?php esc_attr_e( 'First page', 'mage-eventpress' ); ?>">
						<span class="dashicons dashicons-controls-skipback"></span>
					</button>
					<!-- Prev -->
					<button type="button" class="mep-page-btn <?php echo $current <= 1 ? 'mep-page-btn-disabled' : ''; ?>"
						data-page="<?php echo esc_attr( $current - 1 ); ?>" <?php echo $current <= 1 ? 'disabled' : ''; ?>>
						<span class="dashicons dashicons-arrow-left-alt2"></span>
					</button>

					<!-- Leading ellipsis -->
					<?php if ( $current > 3 ) : ?>
						<button type="button" class="mep-page-btn" data-page="1">1</button>
						<?php if ( $current > 4 ) : ?>
							<span class="mep-page-ellipsis">…</span>
						<?php endif; ?>
					<?php endif; ?>

					<!-- Numbered range -->
					<?php
					$range = 2;
					for ( $i = max( 1, $current - $range ); $i <= min( $total_pages, $current + $range ); $i++ ) :
						$active = $i === $current ? 'mep-page-btn-active' : '';
					?>
						<button type="button" class="mep-page-btn <?php echo esc_attr( $active ); ?>" data-page="<?php echo esc_attr( $i ); ?>">
							<?php echo esc_html( $i ); ?>
						</button>
					<?php endfor; ?>

					<!-- Trailing ellipsis -->
					<?php if ( $current < $total_pages - 2 ) : ?>
						<?php if ( $current < $total_pages - 3 ) : ?>
							<span class="mep-page-ellipsis">…</span>
						<?php endif; ?>
						<button type="button" class="mep-page-btn" data-page="<?php echo esc_attr( $total_pages ); ?>">
							<?php echo esc_html( $total_pages ); ?>
						</button>
					<?php endif; ?>

					<!-- Next -->
					<button type="button" class="mep-page-btn <?php echo $current >= $total_pages ? 'mep-page-btn-disabled' : ''; ?>"
						data-page="<?php echo esc_attr( $current + 1 ); ?>" <?php echo $current >= $total_pages ? 'disabled' : ''; ?>>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
					<!-- Last -->
					<button type="button" class="mep-page-btn mep-page-edge <?php echo $current >= $total_pages ? 'mep-page-btn-disabled' : ''; ?>"
						data-page="<?php echo esc_attr( $total_pages ); ?>" <?php echo $current >= $total_pages ? 'disabled' : ''; ?> title="<?php esc_attr_e( 'Last page', 'mage-eventpress' ); ?>">
						<span class="dashicons dashicons-controls-skipforward"></span>
					</button>
				</div>

				<div class="mep-pagination-jump">
					<label for="mep-attlist-page-jump"><?php esc_html_e( 'Go to', 'mage-eventpress' ); ?></label>
					<input type="number" id="mep-attlist-page-jump" min="1" max="<?php echo esc_attr( $total_pages ); ?>"
						placeholder="<?php echo esc_attr( $current ); ?>" />
					<button type="button" id="mep-attlist-page-jump-btn" class="mep-btn mep-btn-outline mep-btn-sm"
						data-total-pages="<?php echo esc_attr( $total_pages ); ?>">
						<?php esc_html_e( 'Go', 'mage-eventpress' ); ?>
					</button>
				</div>
				<?php endif; ?>
				<?php
			}

			private function render_row( $attendee_id, $count ) {
				$attendee_meta = get_post_custom( $attendee_id );
				$attendee_info = array();
				if ( $attendee_meta ) {
					$attendee_meta = MPWEM_Global_Function::data_sanitize( $attendee_meta );
					foreach ( $attendee_meta as $key => $value ) {
						$attendee_info[ $key ] = current( $value );
					}
				}
				$event_id    = array_key_exists( 'ea_event_id', $attendee_info ) ? $attendee_info['ea_event_id'] : '';
				$order_id    = array_key_exists( 'ea_order_id', $attendee_info ) ? $attendee_info['ea_order_id'] : '';
				$event_date  = array_key_exists( 'ea_event_date', $attendee_info ) ? $attendee_info['ea_event_date'] : '';
				$date_format = MPWEM_Global_Function::check_time_exit_date( $event_date ) ? 'full' : 'date';
				$order       = $order_id ? wc_get_order( $order_id ) : false;

				$ea_name = array_key_exists( 'ea_name', $attendee_info ) ? $attendee_info['ea_name'] : '';
				if ( empty( $ea_name ) ) {
					$f_name  = MPWEM_Global_Function::get_post_info( $order_id, '_billing_first_name' );
					$l_name  = MPWEM_Global_Function::get_post_info( $order_id, '_billing_last_name' );
					$ea_name = trim( $f_name . ' ' . $l_name );
				}
				$email = array_key_exists( 'ea_email', $attendee_info ) && $attendee_info['ea_email'] ? $attendee_info['ea_email'] : ( $order && is_object( $order ) ? $order->get_billing_email() : '' );
				$phone = array_key_exists( 'ea_phone', $attendee_info ) && $attendee_info['ea_phone'] ? $attendee_info['ea_phone'] : ( $order && is_object( $order ) ? $order->get_billing_phone() : '' );

				$order_status       = array_key_exists( 'ea_order_status', $attendee_info ) ? $attendee_info['ea_order_status'] : '';
				$order_status_class = $order_status ? sanitize_html_class( strtolower( str_replace( ' ', '-', $order_status ) ) ) : 'default';
				$pro_tip            = esc_attr__( 'Available in PRO version', 'mage-eventpress' );
				?>
				<tr>
					<td class="mep-free-attlist-si"><?php echo esc_html( $count ); ?></td>
					<td>#<?php echo esc_html( $order_id ); ?></td>
					<td class="mep-free-attlist-event"><?php echo esc_html( get_the_title( $event_id ) ); ?></td>
					<td><?php echo esc_html( array_key_exists( 'ea_ticket_type', $attendee_info ) ? $attendee_info['ea_ticket_type'] : '' ); ?></td>
					<td><?php echo esc_html( $ea_name ); ?></td>
					<td><?php echo esc_html( $email ); ?></td>
					<td><?php echo esc_html( $phone ); ?></td>
					<td><?php echo esc_html( MPWEM_Global_Function::date_format( $event_date, $date_format ) ); ?></td>
					<td>
						<span class="mep-status-pill mep-status-<?php echo esc_attr( $order_status_class ); ?>">
							<?php echo esc_html( $order_status ? ucfirst( $order_status ) : '—' ); ?>
						</span>
					</td>
					<td class="mep-free-attlist-action-cell">
						<div class="mep-free-attlist-actions">
							<button type="button" class="mep-free-attlist-action-btn" disabled title="<?php echo $pro_tip; ?>"><span class="dashicons dashicons-visibility"></span></button>
							<button type="button" class="mep-free-attlist-action-btn" disabled title="<?php echo $pro_tip; ?>"><span class="dashicons dashicons-edit"></span></button>
							<button type="button" class="mep-free-attlist-action-btn" disabled title="<?php echo $pro_tip; ?>"><span class="dashicons dashicons-update"></span></button>
							<button type="button" class="mep-free-attlist-action-btn" disabled title="<?php echo $pro_tip; ?>"><span class="dashicons dashicons-trash"></span></button>
						</div>
					</td>
				</tr>
				<?php
			}

			public function ajax_filter() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' );
				}
				// See passenger_menu() for why this is manage_options and not get_admin_capability().
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( 'Permission denied' );
				}
				$args = array(
					'post_id'       => isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0,
					'page'          => isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1,
					'post_per_page' => self::PER_PAGE,
				);
				ob_start();
				$this->render_result( $args );
				$html = ob_get_clean();
				wp_send_json_success( array( 'html' => $html ) );
			}

			private function print_assets() {
				?>
				<style>
					/* ---- Header PRO flag on the (disabled) Export CSV button ------------- */
					.mep-free-attlist-pro-flag {
						display: inline-flex; align-items: center; gap: 3px;
						background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
						color: #fff; padding: 2px 7px; border-radius: 999px; margin-left: 6px;
						font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
					}
					.mep-free-attlist-pro-flag .dashicons { font-size: 10.5px; width: 10.5px; height: 10.5px; }
					.mep-orders-header-actions .mep-btn[disabled] { cursor: not-allowed; opacity: .85; }

					.mep-free-attlist-upgrade { font-size: 12.5px; color: #4f46e5; text-decoration: none; font-weight: 500; margin-left: auto; }
					.mep-free-attlist-upgrade:hover { text-decoration: underline; }

					/* ---- Extra order-status colour not present on the Orders page ------- */
					.mep-status-partially-paid { background: #e0e7ff; color: #3730a3; }
					.mep-status-default { background: #f1f5f9; color: #64748b; }

					.mep-free-attlist-si { color: #94a3b8; font-variant-numeric: tabular-nums; }
					.mep-free-attlist-event { font-weight: 600; color: #1e293b; }

					/* ---- Action column: narrow, compact buttons + an always-visible PRO flag -- */
					.mep-free-attlist-action-head-cell { width: 1%; }
					.mep-free-attlist-action-head { display: flex; flex-direction: column; align-items: flex-start; gap: 5px; }
					.mep-free-attlist-action-cell { width: 1%; }
					.mep-free-attlist-actions { display: inline-flex; gap: 3px; }
					/* Qualified as ".mep-free-attlist .mep-free-attlist-action-btn" (two classes) on
					   purpose: the plugin's own global admin.css ships a bare ".mpwem_style button"
					   reset (one class + the element type) that otherwise outranks a single-class
					   selector here and silently strips the background/padding/appearance back to
					   the browser's native button chrome. */
					.mep-free-attlist .mep-free-attlist-action-btn {
						-webkit-appearance: none; appearance: none;
						width: 26px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center;
						border: 1px solid #e2e8f0; border-radius: 6px; background-color: #fff; color: #94a3b8; cursor: not-allowed;
					}
					.mep-free-attlist .mep-free-attlist-action-btn[disabled] { opacity: .6; }
					.mep-free-attlist .mep-free-attlist-action-btn:hover,
					.mep-free-attlist .mep-free-attlist-action-btn:focus {
						background-color: #fff; color: #94a3b8;
					}
					.mep-free-attlist .mep-free-attlist-action-btn .dashicons { font-size: 14px; width: 14px; height: 14px; }
				</style>
				<script>
					(function ($) {
						function currentPostId() {
							return $('#mep_free_attlist_event').val() || 0;
						}
						function loadPage(page) {
							var $result = $('#mep_free_attlist_result');
							$result.css('opacity', 0.5);
							$.post(ajaxurl, {
								action: 'mep_free_attendee_list_filter',
								nonce: $('#mep_free_attlist_nonce').val(),
								post_id: currentPostId(),
								page: page || 1
							}).done(function (response) {
								if (response && response.success) {
									$result.html(response.data.html);
								}
							}).always(function () {
								$result.css('opacity', 1);
								$('html, body').animate({ scrollTop: $('#mep_free_attlist_result').offset().top - 40 }, 200);
							});
						}
						$(document).on('click', '#mep_free_attlist_filter_btn', function () {
							loadPage(1);
						});
						$(document).on('click', '#mep_free_attlist_result .mep-page-btn:not([disabled])', function () {
							var page = parseInt($(this).data('page'), 10);
							if (page) {
								loadPage(page);
							}
						});
						$(document).on('click', '#mep-attlist-page-jump-btn', function () {
							var totalPages = parseInt($(this).data('total-pages'), 10) || 1;
							var page       = parseInt($('#mep-attlist-page-jump').val(), 10);
							if (page && page >= 1 && page <= totalPages) {
								loadPage(page);
							}
						});
						$(document).on('keydown', '#mep-attlist-page-jump', function (e) {
							if (e.key === 'Enter') {
								$('#mep-attlist-page-jump-btn').trigger('click');
							}
						});
						$(document).on('click', '#mep-attlist-filter-toggle', function () {
							var $body = $('#mep-attlist-filter-body');
							var $btn  = $(this);
							$body.toggleClass('mep-hidden');
							$btn.toggleClass('collapsed');
							$btn.attr('aria-expanded', !$body.hasClass('mep-hidden'));
						});
					})(jQuery);
				</script>
				<?php
			}
		}
		new MPWEM_Attendee_List_Free();
	}
