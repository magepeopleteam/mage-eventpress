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
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Attendee_List_Free' ) ) {
		class MPWEM_Attendee_List_Free {
			const PRO_PLUGIN = 'mage-eventpress-pro/woocommerce-event-manager-pro.php';

			public function __construct() {
				add_action( 'admin_menu', array( $this, 'passenger_menu' ) );
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
					MPWEM_Global_Function::get_admin_capability(),
					'attendee_list',
					array( $this, 'render_page' )
				);
			}

			private function normalize_args( $args = array() ) {
				$args     = is_array( $args ) ? $args : array();
				$page     = isset( $args['page'] ) ? absint( $args['page'] ) : 1;
				$post_id  = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
				$per_page = isset( $args['post_per_page'] ) ? absint( $args['post_per_page'] ) : 20;

				return array(
					'page'          => max( 1, $page ),
					'post_id'       => $post_id,
					'post_per_page' => $per_page > 0 ? $per_page : 20,
				);
			}

			public function render_page() {
				$label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				?>
				<div class="wrap">
					<div class="mpwem_style mep-free-attlist">
						<h1 class="mep-free-attlist-title"><?php esc_html_e( 'Attendee List', 'mage-eventpress' ); ?></h1>
						<div class="mep-free-attlist-filter">
							<div class="mep-free-attlist-filter-field">
								<label for="mep_free_attlist_event" class="mep-free-attlist-filter-label">
									<span class="dashicons dashicons-filter"></span>
									<?php
										/* translators: %s: the configured "Events" label */
										printf( esc_html__( 'Filter by %s', 'mage-eventpress' ), esc_html( $label ) );
									?>
								</label>
								<select id="mep_free_attlist_event" class="mep-free-attlist-select">
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
							<button type="button" class="mep-free-attlist-filter-btn" id="mep_free_attlist_filter_btn">
								<span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Filter', 'mage-eventpress' ); ?>
							</button>
							<div class="mep-free-attlist-filter-spacer"></div>
							<span class="mep-free-attlist-pro-pill" title="<?php esc_attr_e( 'Available in PRO version', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Export CSV', 'mage-eventpress' ); ?>
							</span>
							<?php
								$upgrade_url = apply_filters( 'mep_pro_upgrade_url', 'https://mage-people.com/', 0 );
							?>
							<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="mep-free-attlist-upgrade">
								<?php esc_html_e( 'More filters, columns & actions in PRO', 'mage-eventpress' ); ?> &rarr;
							</a>
						</div>
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
				?>
				<p class="mep-free-attlist-count">
					<?php
						printf(
							/* translators: 1: shown count, 2: total found */
							esc_html__( 'Showing %1$d of %2$d attendee(s).', 'mage-eventpress' ),
							(int) $total,
							(int) $query->found_posts
						);
					?>
				</p>
				<div class="mep-free-attlist-table-wrap">
					<table class="mep-free-attlist-table">
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
									<td colspan="10"><?php esc_html_e( 'No Record Found.', 'mage-eventpress' ); ?></td>
								</tr>
								<?php
							}
						?>
						</tbody>
					</table>
				</div>
				<?php
				if ( $total > 0 ) {
					$parameter = array(
						'show'             => $args['post_per_page'],
						'pagination'       => 'yes',
						'pagination-style' => 'ajax',
					);
					do_action( 'add_mpwem_pagination_section', $parameter, (int) $query->found_posts, $args['page'] );
				}
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
				$order_status_class = $order_status ? sanitize_html_class( strtolower( $order_status ) ) : 'default';
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
						<span class="mep-free-attlist-status mep-free-attlist-status--<?php echo esc_attr( $order_status_class ); ?>">
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
				if ( ! current_user_can( MPWEM_Global_Function::get_admin_capability() ) ) {
					wp_send_json_error( 'Permission denied' );
				}
				$args = array(
					'post_id'       => isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0,
					'page'          => isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1,
					'post_per_page' => 20,
				);
				ob_start();
				$this->render_result( $args );
				$html = ob_get_clean();
				wp_send_json_success( array( 'html' => $html ) );
			}

			private function print_assets() {
				?>
				<style>
					.mep-free-attlist-title { display: flex; align-items: center; gap: 10px; font-weight: 600; }

					/* ---- Filter bar ---------------------------------------------------- */
					.mep-free-attlist-filter {
						display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
						margin: 16px 0 20px; padding: 18px 22px;
						background: linear-gradient(135deg, #f8faff 0%, #eef2ff 100%);
						border: 1px solid #e0e7ff; border-radius: 14px;
						box-shadow: 0 2px 10px -4px rgba(79, 70, 229, .12);
					}
					.mep-free-attlist-filter-field { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
					.mep-free-attlist-filter-label { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: #334155; font-size: 13px; }
					.mep-free-attlist-filter-label .dashicons { color: #6366f1; font-size: 16px; width: 16px; height: 16px; }
					.mep-free-attlist-select {
						min-width: 260px; height: 38px; padding: 0 12px; border-radius: 9px;
						border: 1px solid #c7d2fe; background: #fff; font-size: 13px; color: #1e293b;
						box-shadow: none; transition: border-color .15s ease, box-shadow .15s ease;
					}
					.mep-free-attlist-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .15); outline: none; }
					/* Qualified as ".mep-free-attlist .mep-free-attlist-filter-btn" (two classes) on
					   purpose: the plugin's own global admin.css ships a bare ".mpwem_style button"
					   reset (one class + the element type) that otherwise outranks a single-class
					   selector here and silently strips the background/padding/appearance back to
					   the browser's native button chrome. */
					.mep-free-attlist .mep-free-attlist-filter-btn {
						-webkit-appearance: none; appearance: none;
						display: inline-flex; align-items: center; gap: 7px;
						margin-left: 6px; padding: 9px 20px; border: 1px solid transparent; border-radius: 8px;
						background-color: #4338ca; background-image: none; color: #ffffff; font-weight: 600; font-size: 13px;
						line-height: 1.4; cursor: pointer; box-shadow: 0 2px 6px rgba(67, 56, 202, .3);
						transition: background-color .18s ease, box-shadow .18s ease, transform .18s ease;
					}
					.mep-free-attlist .mep-free-attlist-filter-btn:hover,
					.mep-free-attlist .mep-free-attlist-filter-btn:focus {
						background-color: #372aa8; color: #ffffff; outline: none;
						box-shadow: 0 4px 10px rgba(67, 56, 202, .4); transform: translateY(-1px);
					}
					.mep-free-attlist .mep-free-attlist-filter-btn:active { transform: translateY(0); box-shadow: 0 2px 4px rgba(67, 56, 202, .3); }
					.mep-free-attlist .mep-free-attlist-filter-btn .dashicons { font-size: 14px; width: 14px; height: 14px; color: #ffffff; }
					.mep-free-attlist-filter-spacer { flex: 1 1 auto; }
					.mep-free-attlist-pro-pill {
						display: inline-flex; align-items: center; gap: 4px;
						background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
						color: #fff; padding: 8px 14px; border-radius: 9px;
						font-weight: 700; font-size: 11px; text-transform: uppercase;
						letter-spacing: .05em; cursor: not-allowed; box-shadow: 0 3px 8px -3px rgba(253, 160, 133, .6);
					}
					.mep-free-attlist-pro-pill .dashicons { font-size: 14px; width: 14px; height: 14px; }
					.mep-free-attlist-upgrade { font-size: 12.5px; color: #4f46e5; text-decoration: none; font-weight: 500; }
					.mep-free-attlist-upgrade:hover { text-decoration: underline; }

					/* ---- Result count --------------------------------------------------- */
					.mep-free-attlist-count { font-size: 13px; color: #64748b; margin: 0 2px 10px; }

					/* ---- Table ------------------------------------------------------------ */
					.mep-free-attlist-table-wrap {
						overflow-x: auto; background: #fff; border: 1px solid #e2e8f0;
						border-radius: 14px; box-shadow: 0 4px 16px -8px rgba(15, 23, 42, .12);
					}
					.mep-free-attlist-table { width: 100%; border-collapse: separate; border-spacing: 0; }
					.mep-free-attlist-table thead th {
						background: linear-gradient(180deg, #f8fafc, #f1f5f9);
						color: #475569; font-size: 11.5px; font-weight: 700; text-transform: uppercase;
						letter-spacing: .045em; text-align: left; padding: 13px 14px;
						border-bottom: 1px solid #e2e8f0; white-space: nowrap;
					}
					.mep-free-attlist-table thead th:first-child { border-top-left-radius: 14px; }
					.mep-free-attlist-table thead th:last-child { border-top-right-radius: 14px; }
					.mep-free-attlist-table tbody td {
						padding: 11px 14px; border-bottom: 1px solid #f1f5f9;
						font-size: 13px; color: #334155; white-space: nowrap;
					}
					.mep-free-attlist-table tbody tr:nth-child(even) { background: #fbfcff; }
					.mep-free-attlist-table tbody tr:hover { background: #eef2ff; }
					.mep-free-attlist-table tbody tr:last-child td { border-bottom: 0; }
					.mep-free-attlist-si { color: #94a3b8; font-variant-numeric: tabular-nums; }
					.mep-free-attlist-event { font-weight: 600; color: #1e293b; }

					/* ---- Order status pill ------------------------------------------------ */
					.mep-free-attlist-status {
						display: inline-block; padding: 3px 11px; border-radius: 999px;
						font-size: 11px; font-weight: 600; text-transform: capitalize; white-space: nowrap;
					}
					.mep-free-attlist-status--processing { background: #fef3c7; color: #92400e; }
					.mep-free-attlist-status--completed { background: #d1fae5; color: #065f46; }
					.mep-free-attlist-status--partially-paid { background: #e0e7ff; color: #3730a3; }
					.mep-free-attlist-status--default { background: #f1f5f9; color: #64748b; }

					/* ---- Action column: narrow, compact buttons + an always-visible PRO flag -- */
					.mep-free-attlist-action-head-cell { width: 1%; }
					.mep-free-attlist-action-head { display: flex; flex-direction: column; align-items: flex-start; gap: 5px; }
					.mep-free-attlist-pro-flag {
						display: inline-flex; align-items: center; gap: 3px;
						background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
						color: #fff; padding: 2px 7px; border-radius: 999px;
						font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
					}
					.mep-free-attlist-pro-flag .dashicons { font-size: 10.5px; width: 10.5px; height: 10.5px; }
					.mep-free-attlist-action-cell { width: 1%; }
					.mep-free-attlist-actions { display: inline-flex; gap: 3px; }
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
							});
						}
						$(document).on('click', '#mep_free_attlist_filter_btn', function () {
							loadPage(1);
						});
						$(document).on('click', '#mep_free_attlist_result [data-pagination]', function () {
							loadPage($(this).data('pagination'));
						});
						$(document).on('click', '#mep_free_attlist_result .page_prev, #mep_free_attlist_result .page_next', function () {
							var $active = $('#mep_free_attlist_result .active_pagination');
							var current = $active.length ? parseInt($active.data('pagination'), 10) : 1;
							var page    = $(this).hasClass('page_prev') ? Math.max(1, current - 1) : current + 1;
							loadPage(page);
						});
					})(jQuery);
				</script>
				<?php
			}
		}
		new MPWEM_Attendee_List_Free();
	}
