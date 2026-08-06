<?php
	namespace Admin;
	use WP_Query;
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	if (!class_exists('Admin\mep_dummy_import')) {
		class mep_dummy_import {
			public function __construct() {
				update_option('mep_event_seat_left_data_update_01', 'completed');
				add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
				add_action('admin_footer', array($this, 'render_popup'));
				add_action('wp_ajax_mep_import_dummy_data', array($this, 'ajax_import_dummy_data'));
				add_action('wp_ajax_mep_dismiss_dummy_import', array($this, 'ajax_dismiss_dummy_import'));
			}

			public function is_eligible() {
				$dummy_post_inserted = get_option('mep_dummy_already_inserted');
				if ($dummy_post_inserted == 'yes') {
					return false;
				}
				$count_posts = wp_count_posts('mep_events');
				$count_existing_event = isset($count_posts->publish) ? $count_posts->publish : 0;
				$plugin_active = self::check_plugin('mage-eventpress', 'woocommerce-event-press.php');
				
				if (empty($count_existing_event) && $plugin_active == 1) {
					return true;
				}
				return false;
			}

			private function should_auto_show_popup() {
				if (!$this->is_eligible()) {
					return false;
				}
				$dismissed = get_option('mep_dummy_import_dismissed');
				if ($dismissed == 'yes') {
					return false;
				}

				global $pagenow;
				if ($pagenow !== 'edit.php' || !isset($_GET['post_type']) || $_GET['post_type'] !== 'mep_events') {
					return false;
				}

				return true;
			}

			public function enqueue_assets() {
				if (!$this->is_eligible()) {
					return;
				}
				wp_enqueue_style(
					'mep-dummy-installer',
					plugins_url('mage-eventpress/assets/admin/mpwem_woo_installer.css'),
					array(),
					filemtime(ABSPATH . 'wp-content/plugins/mage-eventpress/assets/admin/mpwem_woo_installer.css')
				);
			}

			public function render_popup() {
				if (!$this->is_eligible()) {
					return;
				}
				$display_style = $this->should_auto_show_popup() ? '' : 'display: none;';
				?>
				<!-- MPWEM Dummy Import Popup Overlay -->
				<div id="mpwem-woo-overlay" class="mpwem-woo-overlay mep-dummy-overlay" style="<?php echo esc_attr($display_style); ?>">
					<div class="mpwem-woo-popup">
						<div class="mpwem-woo-header">
							<div class="mpwem-woo-header-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<span class="mpwem-woo-header-text"><?php esc_html_e( 'Event Booking Manager', 'mage-eventpress' ); ?></span>
						</div>

						<div class="mpwem-woo-icon-wrapper">
							<div class="mpwem-woo-icon">
								<svg width="40" height="40" viewBox="0 0 24 24" fill="none">
									<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
									<path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								</svg>
							</div>
						</div>

						<div class="mpwem-woo-content">
							<h2 class="mpwem-woo-title"><?php esc_html_e( 'Import Dummy Events?', 'mage-eventpress' ); ?></h2>
							<p class="mpwem-woo-desc">
								<?php esc_html_e( 'Would you like to import dummy events, categories, and settings to see how Event Booking Manager works?', 'mage-eventpress' ); ?>
							</p>
						</div>

						<div id="mpwem-woo-progress" class="mpwem-woo-progress" style="display:none;">
							<div class="mpwem-woo-progress-bar">
								<div id="mpwem-woo-progress-fill" class="mpwem-woo-progress-fill"></div>
							</div>
							<p id="mpwem-woo-status-text" class="mpwem-woo-status-text"></p>
						</div>

						<div class="mpwem-woo-actions">
							<button type="button" id="mep-dummy-install-btn" class="mpwem-woo-btn mpwem-woo-btn-primary">
								<span class="mpwem-woo-btn-icon">
									<svg width="18" height="18" viewBox="0 0 20 20" fill="none">
										<path d="M10 3v10m0 0l-4-4m4 4l4-4M3 17h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
								<span class="mpwem-woo-btn-text"><?php esc_html_e( 'Yes, Import Data', 'mage-eventpress' ); ?></span>
							</button>
							<button type="button" id="mep-dummy-dismiss-btn" class="mpwem-woo-btn mpwem-woo-btn-secondary">
								<?php esc_html_e( 'No, Skip', 'mage-eventpress' ); ?>
							</button>
						</div>
					</div>
				</div>

				<script>
				(function($) {
					$(document).ready(function() {
						var $overlay = $('#mpwem-woo-overlay.mep-dummy-overlay');
						var $popup = $overlay.find('.mpwem-woo-popup');
						var $btn = $('#mep-dummy-install-btn');
						var $dismissBtn = $('#mep-dummy-dismiss-btn');
						var $progress = $('#mpwem-woo-progress');
						var $fill = $('#mpwem-woo-progress-fill');
						var $status = $('#mpwem-woo-status-text');
						var $actions = $overlay.find('.mpwem-woo-actions');
						var isWorking = false;

						if (!$overlay.length) return;

						// Manual Trigger from other pages
						$(document).on('click', '#mep-trigger-dummy-import-btn', function(e) {
							e.preventDefault();
							$overlay.css('display', 'flex').hide().fadeIn(300);
						});

						$btn.on('click', function(e) {
							e.preventDefault();
							if (isWorking) return;
							isWorking = true;
							$btn.prop('disabled', true);
							$dismissBtn.prop('disabled', true);

							$actions.slideUp(250);
							$progress.slideDown(300);

							$fill.css('width', '0%');
							$status.text('<?php echo esc_js(__("Starting import...", "mage-eventpress")); ?>').removeClass('mpwem-success mpwem-error');

							function doStep(stepName, index, totalEvents) {
								$.ajax({
									url: ajaxurl,
									type: 'POST',
									data: {
										action: 'mep_import_dummy_data',
										nonce: '<?php echo wp_create_nonce("mep_import_dummy"); ?>',
										step: stepName,
										index: index
									},
									success: function(response) {
										if (response.success) {
											if (stepName === 'init') {
												$fill.css('width', '10%');
												$status.text('<?php echo esc_js(__("Importing events...", "mage-eventpress")); ?>');
												var total = response.data.total_events || 0;
												if (total > 0) {
													doStep('event', 0, total);
												} else {
													doStep('finalize', 0, 0);
												}
											} else if (stepName === 'event') {
												var nextIndex = index + 1;
												var percent = 10 + Math.floor((nextIndex / totalEvents) * 80);
												$fill.css('width', percent + '%');
												$status.text('<?php echo esc_js(__("Importing events...", "mage-eventpress")); ?> (' + nextIndex + '/' + totalEvents + ')');
												
												if (nextIndex < totalEvents) {
													doStep('event', nextIndex, totalEvents);
												} else {
													doStep('finalize', 0, 0);
												}
											} else if (stepName === 'finalize') {
												$fill.css('width', '100%');
												$status.text('<?php echo esc_js(__("Import complete! 100%", "mage-eventpress")); ?>').addClass('mpwem-success');
												$popup.addClass('mpwem-state-success');
												$popup.find('.mpwem-woo-icon').html(
													'<svg width="40" height="40" viewBox="0 0 24 24" fill="none">' +
													'<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>' +
													'<path d="M8 12l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
													'</svg>'
												);
												$popup.find('.mpwem-woo-title').text('<?php echo esc_js(__("Success", "mage-eventpress")); ?>');
												$popup.find('.mpwem-woo-desc').text('<?php echo esc_js(__("Dummy data imported successfully. Reloading page...", "mage-eventpress")); ?>');
												setTimeout(function() { window.location.reload(); }, 1500);
											}
										} else {
											showError(response.data && response.data.message ? response.data.message : '<?php echo esc_js(__("Failed to import.", "mage-eventpress")); ?>');
										}
									},
									error: function() {
										showError('<?php echo esc_js(__("Failed to import. Please try again.", "mage-eventpress")); ?>');
									}
								});
							}

							doStep('init', 0, 0);
						});

						$dismissBtn.on('click', function(e) {
							e.preventDefault();
							if (isWorking) return;
							isWorking = true;
							
							$overlay.css('opacity', '0.5');
							
							$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
									action: 'mep_dismiss_dummy_import',
									nonce: '<?php echo wp_create_nonce("mep_dismiss_dummy"); ?>'
								},
								success: function() {
									$overlay.fadeOut(300, function() { $(this).remove(); });
								},
								error: function() {
									$overlay.fadeOut(300, function() { $(this).remove(); });
								}
							});
						});

						function showError(message) {
							isWorking = false;
							$popup.addClass('mpwem-state-error');
							$status.text(message).addClass('mpwem-error');
							$fill.css('width', '100%');

							$btn.prop('disabled', false);
							$dismissBtn.prop('disabled', false);
							$actions.slideDown(250);

							setTimeout(function() {
								$popup.removeClass('mpwem-state-error');
								$progress.slideUp(250);
								$fill.css('width', '0%');
							}, 3000);
						}
					});
				})(jQuery);
				</script>
				<?php
			}

			public function ajax_import_dummy_data() {
				check_ajax_referer('mep_import_dummy', 'nonce');
				if (!current_user_can('manage_options')) {
					wp_send_json_error(array('message' => 'Permission denied.'));
				}

				$step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : 'all';
				$index = isset($_POST['index']) ? intval($_POST['index']) : 0;

				try {
					if ($step === 'init') {
						$this->dummy_import_init();
						$dummy_data = $this->dummy_data();
						$total_events = isset($dummy_data['custom_post']['mep_events']) ? count($dummy_data['custom_post']['mep_events']) : 0;
						wp_send_json_success(array('message' => 'Init complete', 'total_events' => $total_events));
					} elseif ($step === 'event') {
						$this->dummy_import_event($index);
						wp_send_json_success(array('message' => "Event $index complete"));
					} elseif ($step === 'finalize') {
						$this->dummy_import_finalize();
						wp_send_json_success(array('message' => 'Finalize complete'));
					} else {
						$this->dummy_import();
						wp_send_json_success();
					}
				} catch (\Exception $e) {
					wp_send_json_error(array('message' => $e->getMessage()));
				}
			}

			public function ajax_dismiss_dummy_import() {
				check_ajax_referer('mep_dismiss_dummy', 'nonce');
				if (!current_user_can('manage_options')) {
					wp_send_json_error(array('message' => 'Permission denied.'));
				}
				update_option('mep_dummy_import_dismissed', 'yes');
				wp_send_json_success();
			}
			public static function check_plugin($plugin_dir_name, $plugin_file): int {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				$plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
				if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) {
					return 1;
				}
				elseif (is_dir($plugin_dir)) {
					return 2;
				}
				else {
					return 0;
				}
			}
			function craete_pages() {
				if (empty(mep_get_page_by_slug('events-list-style'))) {
					$post_details = array(
						'post_title' => 'Events – List Style',
						'post_content' => '[event-list show="10" style="list" pagination="yes"]',
						'post_status' => 'publish',
						'post_author' => 1,
						'post_type' => 'page'
					);
					wp_insert_post($post_details);
				}
				if (empty(mep_get_page_by_slug('events-grid-style'))) {
					$post_details = array(
						'post_title' => 'Events – Grid Style',
						'post_content' => "[event-list show='6' style='grid' pagination='yes']",
						'post_status' => 'publish',
						'post_author' => 1,
						'post_type' => 'page'
					);
					wp_insert_post($post_details);
				}
				if (empty(mep_get_page_by_slug('events-grid-new-style'))) {
					$post_details = array(
						'post_title' => 'Events – New Style',
						'post_content' => "[events_list]",
						'post_status' => 'publish',
						'post_author' => 1,
						'post_type' => 'page'
					);
					wp_insert_post($post_details);
				}
				if (empty(mep_get_page_by_slug('events-list-style-with-search-box'))) {
					$post_details = array(
						'post_title' => 'Events – List Style with Search Box',
						'post_content' => "[event-list column=4 search-filter='yes']",
						'post_status' => 'publish',
						'post_author' => 1,
						'post_type' => 'page'
					);
					wp_insert_post($post_details);
				}
			}
			public function dummy_import_init() {
				$dummy_post_inserted = get_option('mep_dummy_already_inserted');
				if ($dummy_post_inserted) return;

				$dummy_data = $this->dummy_data();
				if (isset($dummy_data['taxonomy'])) {
					foreach ($dummy_data['taxonomy'] as $taxonomy => $dummy_taxonomy) {
						if (taxonomy_exists($taxonomy)) {
							$check_terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
							if (is_string($check_terms) || (is_array($check_terms) && sizeof($check_terms) == 0)) {
								foreach ($dummy_taxonomy as $taxonomy_data) {
									$term = wp_insert_term($taxonomy_data['name'], $taxonomy);
									if (!is_wp_error($term) && is_array($taxonomy_data) && array_key_exists('tax_data', $taxonomy_data)) {
										foreach ($taxonomy_data['tax_data'] as $meta_key => $data) {
											update_term_meta($term['term_id'], $meta_key, $data);
										}
									}
								}
							}
						}
					}
				}
				$this->dummy_import_speakers();
				$this->dummy_import_reg_forms();
				$this->craete_pages();
			}

			/**
			 * Import sample speaker posts from dummy_data().
			 *
			 * @return int[] Created/existing speaker IDs.
			 */
			public function dummy_import_speakers() {
				$dummy_data = $this->dummy_data();
				$speakers   = isset( $dummy_data['custom_post']['mep_event_speaker'] ) ? $dummy_data['custom_post']['mep_event_speaker'] : array();
				$ids        = array();
				if ( ! is_array( $speakers ) || empty( $speakers ) ) {
					return $ids;
				}
				foreach ( $speakers as $speaker ) {
					if ( empty( $speaker['name'] ) ) {
						continue;
					}
					$existing = get_page_by_title( $speaker['name'], OBJECT, 'mep_event_speaker' );
					if ( $existing ) {
						$ids[] = (int) $existing->ID;
						continue;
					}
					$post_id = wp_insert_post(
						array(
							'post_title'   => $speaker['name'],
							'post_content' => isset( $speaker['content'] ) ? $speaker['content'] : '',
							'post_excerpt' => isset( $speaker['excerpt'] ) ? $speaker['excerpt'] : '',
							'post_status'  => 'publish',
							'post_type'    => 'mep_event_speaker',
						)
					);
					if ( is_wp_error( $post_id ) || ! $post_id ) {
						continue;
					}
					$ids[] = (int) $post_id;
					if ( isset( $speaker['post_data'] ) && is_array( $speaker['post_data'] ) ) {
						foreach ( $speaker['post_data'] as $meta_key => $data ) {
							if ( 'feature_image' === $meta_key && $data ) {
								$image = media_sideload_image( $data, $post_id, null, 'id' );
								if ( ! is_wp_error( $image ) ) {
									set_post_thumbnail( $post_id, $image );
								}
							} else {
								update_post_meta( $post_id, $meta_key, $data );
							}
						}
					}
				}
				return $ids;
			}

			/**
			 * Attach sample speakers to published events.
			 *
			 * @param int[] $speaker_ids Speaker post IDs.
			 */
			public function assign_speakers_to_events( $speaker_ids = array() ) {
				if ( empty( $speaker_ids ) ) {
					$speaker_ids = get_posts(
						array(
							'post_type'      => 'mep_event_speaker',
							'post_status'    => 'publish',
							'posts_per_page' => -1,
							'fields'         => 'ids',
							'orderby'        => 'ID',
							'order'          => 'ASC',
						)
					);
				}
				$speaker_ids = array_values( array_filter( array_map( 'intval', (array) $speaker_ids ) ) );
				if ( empty( $speaker_ids ) ) {
					return;
				}
				$events = get_posts(
					array(
						'post_type'      => 'mep_events',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'ASC',
					)
				);
				if ( empty( $events ) ) {
					return;
				}
				$count = count( $speaker_ids );
				foreach ( $events as $i => $event_id ) {
					$chunk = array();
					for ( $n = 0; $n < 4; $n++ ) {
						$chunk[] = (string) $speaker_ids[ ( $i * 2 + $n ) % $count ];
					}
					$chunk = array_values( array_unique( $chunk ) );
					update_post_meta( $event_id, 'mep_event_enable_speaker', 'yes' );
					update_post_meta( $event_id, 'mep_speaker_title', 'Speakers' );
					update_post_meta( $event_id, 'mep_event_speaker_icon', 'fas fa-user-tie' );
					update_post_meta( $event_id, 'mep_event_speakers_list', $chunk );
				}
			}

			/**
			 * Import sample Global Reg Form posts from dummy_data().
			 *
			 * @return int[] Created/existing form IDs.
			 */
			public function dummy_import_reg_forms() {
				if ( ! post_type_exists( 'mep_events_reg_form' ) ) {
					return array();
				}
				$dummy_data = $this->dummy_data();
				$forms      = isset( $dummy_data['custom_post']['mep_events_reg_form'] ) ? $dummy_data['custom_post']['mep_events_reg_form'] : array();
				$ids        = array();
				if ( ! is_array( $forms ) || empty( $forms ) ) {
					return $ids;
				}
				foreach ( $forms as $form ) {
					if ( empty( $form['name'] ) ) {
						continue;
					}
					$existing = get_page_by_title( $form['name'], OBJECT, 'mep_events_reg_form' );
					if ( $existing ) {
						$ids[] = (int) $existing->ID;
						continue;
					}
					$post_id = wp_insert_post(
						array(
							'post_title'  => $form['name'],
							'post_status' => 'publish',
							'post_type'   => 'mep_events_reg_form',
						)
					);
					if ( is_wp_error( $post_id ) || ! $post_id ) {
						continue;
					}
					$ids[] = (int) $post_id;
					if ( isset( $form['post_data'] ) && is_array( $form['post_data'] ) ) {
						foreach ( $form['post_data'] as $meta_key => $data ) {
							update_post_meta( $post_id, $meta_key, $data );
						}
					}
				}
				return $ids;
			}

			/**
			 * Attach sample Global Reg Forms to published events (round-robin).
			 *
			 * @param int[] $form_ids Form post IDs.
			 */
			public function assign_reg_forms_to_events( $form_ids = array() ) {
				if ( empty( $form_ids ) ) {
					$form_ids = get_posts(
						array(
							'post_type'      => 'mep_events_reg_form',
							'post_status'    => 'publish',
							'posts_per_page' => 3,
							'fields'         => 'ids',
							'orderby'        => 'ID',
							'order'          => 'ASC',
						)
					);
				}
				$form_ids = array_values( array_filter( array_map( 'intval', (array) $form_ids ) ) );
				if ( empty( $form_ids ) ) {
					return;
				}
				$events = get_posts(
					array(
						'post_type'      => 'mep_events',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'ASC',
					)
				);
				if ( empty( $events ) ) {
					return;
				}
				$count = count( $form_ids );
				foreach ( $events as $i => $event_id ) {
					update_post_meta( $event_id, 'mep_event_reg_form_id', (string) $form_ids[ $i % $count ] );
				}
			}

			/**
			 * Import sample RSVP response posts and attach them to events.
			 *
			 * @return int[] Created RSVP post IDs.
			 */
			public function dummy_import_rsvp_responses() {
				if ( ! post_type_exists( 'mep_rsvp_responses' ) ) {
					return array();
				}
				$dummy_data = $this->dummy_data();
				$items      = isset( $dummy_data['custom_post']['mep_rsvp_responses'] ) ? $dummy_data['custom_post']['mep_rsvp_responses'] : array();
				$ids        = array();
				if ( ! is_array( $items ) || empty( $items ) ) {
					return $ids;
				}
				$events = get_posts(
					array(
						'post_type'      => 'mep_events',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'ASC',
					)
				);
				if ( empty( $events ) ) {
					return $ids;
				}
				$event_count = count( $events );
				foreach ( $items as $i => $item ) {
					if ( empty( $item['name'] ) || empty( $item['email'] ) ) {
						continue;
					}
					$existing = get_posts(
						array(
							'post_type'      => 'mep_rsvp_responses',
							'posts_per_page' => 1,
							'fields'         => 'ids',
							'meta_query'     => array(
								'relation' => 'AND',
								array(
									'key'   => 'ea_email',
									'value' => $item['email'],
								),
								array(
									'key'   => 'ea_event_id',
									'value' => (int) $events[ $i % $event_count ],
								),
							),
						)
					);
					if ( ! empty( $existing ) ) {
						$ids[] = (int) $existing[0];
						continue;
					}
					$event_id   = (int) $events[ $i % $event_count ];
					$event_date = get_post_meta( $event_id, 'event_start_datetime', true );
					if ( ! $event_date ) {
						$event_date = get_post_meta( $event_id, 'event_start_date', true );
					}
					$user_info = array(
						'user_name'       => $item['name'],
						'user_email'      => $item['email'],
						'user_phone'      => isset( $item['phone'] ) ? $item['phone'] : '',
						'user_event_date' => $event_date,
						'user_ticket_qty' => isset( $item['qty'] ) ? absint( $item['qty'] ) : 1,
					);
					if ( function_exists( 'mep_rsvp_attendee_create' ) ) {
						$post_id = mep_rsvp_attendee_create( $event_id, $user_info );
					} else {
						$post_id = false;
					}
					if ( ! $post_id ) {
						continue;
					}
					$ids[] = (int) $post_id;
					$checkin = ( ! empty( $item['checkin'] ) && 'Yes' === $item['checkin'] ) ? 'Yes' : 'No';
					update_post_meta( $post_id, 'mep_checkin', $checkin );
				}
				return $ids;
			}

			/**
			 * Import sample Event Orders (mep_custom_order + attendees).
			 *
			 * @return int[] Created/existing order IDs.
			 */
			public function dummy_import_event_orders() {
				if ( ! post_type_exists( 'mep_custom_order' ) ) {
					return array();
				}
				$dummy_data = $this->dummy_data();
				$items      = isset( $dummy_data['custom_post']['mep_custom_order'] ) ? $dummy_data['custom_post']['mep_custom_order'] : array();
				$ids        = array();
				if ( ! is_array( $items ) || empty( $items ) ) {
					return $ids;
				}
				$events = get_posts(
					array(
						'post_type'      => 'mep_events',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'ASC',
					)
				);
				if ( empty( $events ) ) {
					return $ids;
				}
				$event_count = count( $events );
				$statuses    = array( 'publish', 'publish', 'publish', 'processing', 'pending', 'on-hold', 'cancelled' );
				$gateways    = array( 'offline', 'paypal', 'stripe', 'offline', 'free' );

				foreach ( $items as $i => $item ) {
					if ( empty( $item['name'] ) || empty( $item['email'] ) ) {
						continue;
					}
					$existing = get_posts(
						array(
							'post_type'      => 'mep_custom_order',
							'posts_per_page' => 1,
							'post_status'    => 'any',
							'fields'         => 'ids',
							'meta_query'     => array(
								array(
									'key'   => '_mep_customer_email',
									'value' => $item['email'],
								),
								array(
									'key'   => '_mep_sample_order',
									'value' => 'yes',
								),
							),
						)
					);
					if ( ! empty( $existing ) ) {
						$ids[] = (int) $existing[0];
						continue;
					}

					$event_id = (int) $events[ $i % $event_count ];
					$event_date = get_post_meta( $event_id, 'event_start_datetime', true );
					if ( ! $event_date ) {
						$event_date = get_post_meta( $event_id, 'event_start_date', true );
					}

					$ticket_types = get_post_meta( $event_id, 'mep_event_ticket_type', true );
					$ticket_name  = 'General Admission';
					$ticket_price = isset( $item['price'] ) ? (float) $item['price'] : 50;
					if ( is_array( $ticket_types ) && ! empty( $ticket_types ) ) {
						$tt = $ticket_types[ $i % count( $ticket_types ) ];
						if ( ! empty( $tt['option_name_t'] ) ) {
							$ticket_name = $tt['option_name_t'];
						}
						if ( isset( $tt['option_price_t'] ) && '' !== $tt['option_price_t'] ) {
							$ticket_price = (float) $tt['option_price_t'];
						}
					}
					$qty   = isset( $item['qty'] ) ? max( 1, absint( $item['qty'] ) ) : 1;
					$total = $ticket_price * $qty;
					$order_items = array(
						array(
							'name'  => $ticket_name,
							'qty'   => $qty,
							'price' => $ticket_price,
							'total' => $total,
						),
					);

					$status  = isset( $item['status'] ) ? $item['status'] : $statuses[ $i % count( $statuses ) ];
					$gateway = isset( $item['gateway'] ) ? $item['gateway'] : $gateways[ $i % count( $gateways ) ];
					if ( 'free' === $gateway ) {
						$total       = 0;
						$ticket_price = 0;
						$order_items[0]['price'] = 0;
						$order_items[0]['total'] = 0;
					}

					$order_id = wp_insert_post(
						array(
							'post_title'  => sprintf( 'Order - %s', $item['name'] ),
							'post_type'   => 'mep_custom_order',
							'post_status' => $status,
							'post_author' => 1,
							'post_date'   => gmdate( 'Y-m-d H:i:s', time() - ( ( count( $items ) - $i ) * DAY_IN_SECONDS ) ),
						)
					);
					if ( is_wp_error( $order_id ) || ! $order_id ) {
						continue;
					}
					$ids[] = (int) $order_id;

					update_post_meta( $order_id, '_mep_sample_order', 'yes' );
					update_post_meta( $order_id, '_mep_user_id', 0 );
					update_post_meta( $order_id, '_mep_booking_token', wp_generate_password( 32, false ) );
					update_post_meta( $order_id, '_mep_event_id', $event_id );
					update_post_meta( $order_id, '_mep_order_total', $total );
					update_post_meta( $order_id, '_mep_customer_name', $item['name'] );
					update_post_meta( $order_id, '_mep_customer_email', $item['email'] );
					update_post_meta( $order_id, '_mep_customer_phone', isset( $item['phone'] ) ? $item['phone'] : '' );
					update_post_meta( $order_id, '_mep_order_items', $order_items );
					update_post_meta( $order_id, '_mep_event_date', $event_date );
					update_post_meta( $order_id, '_mep_payment_gateway', $gateway );
					update_post_meta(
						$order_id,
						'_mep_billing',
						array(
							'name'  => $item['name'],
							'email' => $item['email'],
							'phone' => isset( $item['phone'] ) ? $item['phone'] : '',
						)
					);

					$attendee_status = ( 'publish' === $status ) ? 'completed' : $status;
					$user_info       = array(
						'user_name'       => $item['name'],
						'user_email'      => $item['email'],
						'user_phone'      => isset( $item['phone'] ) ? $item['phone'] : '',
						'user_event_date' => $event_date,
					);
					$attendee_ids = array();
					if ( function_exists( 'mep_native_ticket_attendee_create' ) ) {
						for ( $seat = 0; $seat < $qty; $seat++ ) {
							$ticket_info = array(
								'ticket_name'  => $ticket_name,
								'ticket_qty'   => 1,
								'ticket_price' => $ticket_price,
							);
							$pid = mep_native_ticket_attendee_create( $event_id, $order_id, $user_info, $ticket_info, $gateway, $attendee_status );
							if ( $pid ) {
								$attendee_ids[] = $pid;
								if ( ! empty( $item['checkin'] ) && 'Yes' === $item['checkin'] && 0 === $seat ) {
									update_post_meta( $pid, 'mep_checkin', 'Yes' );
								}
							}
						}
					}
					if ( ! empty( $attendee_ids ) ) {
						update_post_meta( $order_id, '_mep_attendee_ids', $attendee_ids );
					}
				}
				return $ids;
			}

			/**
			 * Import sample Cancellation Request posts (linked to WooCommerce orders).
			 *
			 * @return int[] Created/existing cancel request IDs.
			 */
			public function dummy_import_cancel_requests() {
				if ( ! function_exists( 'wc_create_order' ) ) {
					return array();
				}
				$dummy_data = $this->dummy_data();
				$items      = isset( $dummy_data['custom_post']['mep_order_cancel_req'] ) ? $dummy_data['custom_post']['mep_order_cancel_req'] : array();
				$ids        = array();
				if ( ! is_array( $items ) || empty( $items ) ) {
					return $ids;
				}

				$admin_id = 1;
				$admins   = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
				if ( ! empty( $admins ) ) {
					$admin_id = (int) $admins[0];
				}

				foreach ( $items as $i => $item ) {
					if ( empty( $item['name'] ) || empty( $item['reason'] ) ) {
						continue;
					}
					$existing = get_posts(
						array(
							'post_type'      => 'mep_order_cancel_req',
							'posts_per_page' => 1,
							'post_status'    => 'any',
							'fields'         => 'ids',
							'meta_query'     => array(
								array(
									'key'   => '_mep_sample_cancel',
									'value' => sanitize_title( $item['name'] ),
								),
							),
						)
					);
					if ( ! empty( $existing ) ) {
						$ids[] = (int) $existing[0];
						continue;
					}

					$name_parts = preg_split( '/\s+/', trim( $item['name'] ), 2 );
					$first      = $name_parts[0];
					$last       = isset( $name_parts[1] ) ? $name_parts[1] : '';
					$email      = isset( $item['email'] ) ? $item['email'] : ( sanitize_title( $item['name'] ) . '@example.com' );
					$total      = isset( $item['total'] ) ? (float) $item['total'] : 100;
					$wc_status  = isset( $item['order_status'] ) ? $item['order_status'] : 'processing';
					$req_status = isset( $item['status'] ) ? $item['status'] : 'pending';
					$reason     = $item['reason'];

					$order = wc_create_order();
					if ( is_wp_error( $order ) || ! $order ) {
						continue;
					}
					$order->set_billing_first_name( $first );
					$order->set_billing_last_name( $last );
					$order->set_billing_email( $email );
					$order->set_billing_phone( isset( $item['phone'] ) ? $item['phone'] : '' );
					$order->set_created_via( 'mep_sample_data' );
					$order->set_currency( get_woocommerce_currency() );
					$fee = new \WC_Order_Item_Fee();
					$fee->set_name( isset( $item['ticket'] ) ? $item['ticket'] : 'Event Ticket' );
					$fee->set_total( $total );
					$order->add_item( $fee );
					$order->set_total( $total );
					$order->set_status( $wc_status );
					$order->update_meta_data( '_mep_sample_order', 'yes' );
					$order->save();
					$wc_order_id = $order->get_id();

					$cancel_id = wp_insert_post(
						array(
							'post_title'   => sprintf( 'Order Cancellation Request #%d', $wc_order_id ),
							'post_content' => $reason,
							'post_status'  => 'publish',
							'post_type'    => 'mep_order_cancel_req',
							'post_author'  => $admin_id,
							'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( ( 12 - $i ) * DAY_IN_SECONDS ) ),
						)
					);
					if ( is_wp_error( $cancel_id ) || ! $cancel_id ) {
						continue;
					}
					$ids[] = (int) $cancel_id;
					update_post_meta( $cancel_id, 'mep_cancel_order_id', $wc_order_id );
					update_post_meta( $cancel_id, 'mep_cancel_reason', $reason );
					update_post_meta( $cancel_id, 'mep_cancel_req_status', $req_status );
					update_post_meta( $cancel_id, 'mep_cancel_user_id', $admin_id );
					update_post_meta( $cancel_id, '_mep_sample_cancel', sanitize_title( $item['name'] ) );
				}
				return $ids;
			}

			/**
			 * Import sample Waitlist entries.
			 *
			 * @return int[] Created/existing waitlist IDs.
			 */
			public function dummy_import_waitlist() {
				if ( ! post_type_exists( 'mep_event_waitlist' ) ) {
					return array();
				}
				$dummy_data = $this->dummy_data();
				$items      = isset( $dummy_data['custom_post']['mep_event_waitlist'] ) ? $dummy_data['custom_post']['mep_event_waitlist'] : array();
				$ids        = array();
				if ( ! is_array( $items ) || empty( $items ) ) {
					return $ids;
				}
				$events = get_posts(
					array(
						'post_type'      => 'mep_events',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'ASC',
					)
				);
				if ( empty( $events ) ) {
					return $ids;
				}
				foreach ( $events as $event_id ) {
					update_post_meta( $event_id, 'mep_show_waitlist', 'on' );
				}
				$event_count = count( $events );
				foreach ( $items as $i => $item ) {
					if ( empty( $item['name'] ) || empty( $item['email'] ) ) {
						continue;
					}
					$event_id   = (int) $events[ $i % $event_count ];
					$event_date = get_post_meta( $event_id, 'event_start_datetime', true );
					if ( ! $event_date ) {
						$event_date = get_post_meta( $event_id, 'event_start_date', true );
					}
					$existing = get_posts(
						array(
							'post_type'      => 'mep_event_waitlist',
							'posts_per_page' => 1,
							'fields'         => 'ids',
							'meta_query'     => array(
								'relation' => 'AND',
								array(
									'key'   => 'user_email',
									'value' => $item['email'],
								),
								array(
									'key'   => 'event_id',
									'value' => $event_id,
								),
								array(
									'key'   => 'status',
									'value' => 1,
								),
							),
						)
					);
					if ( ! empty( $existing ) ) {
						$ids[] = (int) $existing[0];
						continue;
					}
					$wt = array(
						'event_id'       => $event_id,
						'user_name'      => $item['name'],
						'user_email'     => $item['email'],
						'user_phone'     => isset( $item['phone'] ) ? $item['phone'] : '',
						'ticket_qty'     => isset( $item['qty'] ) ? absint( $item['qty'] ) : 1,
						'event_datetime' => $event_date,
						'status'         => 1,
						'email_status'   => isset( $item['email_status'] ) ? absint( $item['email_status'] ) : 0,
					);
					if ( function_exists( 'mep_wl_create_new_waitlist' ) ) {
						$pid = mep_wl_create_new_waitlist( $wt );
					} else {
						$pid = wp_insert_post(
							array(
								'post_title'  => $item['name'] . ' - ' . get_the_title( $event_id ),
								'post_status' => 'publish',
								'post_type'   => 'mep_event_waitlist',
								'post_author' => 1,
							)
						);
						if ( $pid && ! is_wp_error( $pid ) ) {
							foreach ( $wt as $meta_key => $meta_val ) {
								update_post_meta( $pid, $meta_key, $meta_val );
							}
						} else {
							$pid = false;
						}
					}
					if ( $pid ) {
						$ids[] = (int) $pid;
						update_post_meta( $pid, 'email_processed', 'yes' );
						update_post_meta( $pid, '_mep_sample_waitlist', 'yes' );
					}
				}
				return $ids;
			}

			/**
			 * Import 55 sample Event Attendee List records.
			 *
			 * @return int[] Created/existing attendee IDs.
			 */
			public function dummy_import_attendees() {
				if ( ! post_type_exists( 'mep_events_attendees' ) ) {
					return array();
				}
				$dummy_data = $this->dummy_data();
				$items      = isset( $dummy_data['custom_post']['mep_events_attendees'] ) ? $dummy_data['custom_post']['mep_events_attendees'] : array();
				$ids        = array();
				if ( ! is_array( $items ) || empty( $items ) ) {
					return $ids;
				}
				$events = get_posts(
					array(
						'post_type'      => 'mep_events',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'ASC',
					)
				);
				if ( empty( $events ) ) {
					return $ids;
				}
				$event_count = count( $events );
				$statuses    = array( 'completed', 'completed', 'completed', 'processing', 'pending', 'completed', 'on-hold' );
				$gateways    = array( 'offline', 'paypal', 'stripe', 'offline', 'woocommerce' );

				foreach ( $items as $i => $item ) {
					if ( empty( $item['name'] ) || empty( $item['email'] ) ) {
						continue;
					}
					$sample_key = isset( $item['key'] ) ? $item['key'] : ( 'attendee-' . ( $i + 1 ) );
					$existing   = get_posts(
						array(
							'post_type'      => 'mep_events_attendees',
							'posts_per_page' => 1,
							'fields'         => 'ids',
							'meta_query'     => array(
								array(
									'key'   => '_mep_sample_attendee',
									'value' => $sample_key,
								),
							),
						)
					);
					if ( ! empty( $existing ) ) {
						$ids[] = (int) $existing[0];
						continue;
					}

					$event_id   = (int) $events[ $i % $event_count ];
					$event_date = get_post_meta( $event_id, 'event_start_datetime', true );
					if ( ! $event_date ) {
						$event_date = get_post_meta( $event_id, 'event_start_date', true );
					}

					$ticket_types = get_post_meta( $event_id, 'mep_event_ticket_type', true );
					$ticket_name  = 'General Admission';
					$ticket_price = 100;
					if ( is_array( $ticket_types ) && ! empty( $ticket_types ) ) {
						$tt = $ticket_types[ $i % count( $ticket_types ) ];
						if ( ! empty( $tt['option_name_t'] ) ) {
							$ticket_name = $tt['option_name_t'];
						}
						if ( isset( $tt['option_price_t'] ) && '' !== $tt['option_price_t'] ) {
							$ticket_price = (float) $tt['option_price_t'];
						}
					}

					$status  = isset( $item['status'] ) ? $item['status'] : $statuses[ $i % count( $statuses ) ];
					$gateway = isset( $item['gateway'] ) ? $item['gateway'] : $gateways[ $i % count( $gateways ) ];
					$user_info = array(
						'user_name'       => $item['name'],
						'user_email'      => $item['email'],
						'user_phone'      => isset( $item['phone'] ) ? $item['phone'] : '',
						'user_event_date' => $event_date,
					);
					$ticket_info = array(
						'ticket_name'  => $ticket_name,
						'ticket_qty'   => 1,
						'ticket_price' => $ticket_price,
					);

					if ( function_exists( 'mep_native_ticket_attendee_create' ) ) {
						$pid = mep_native_ticket_attendee_create( $event_id, 0, $user_info, $ticket_info, $gateway, $status );
					} else {
						$pid = false;
					}
					if ( ! $pid ) {
						continue;
					}
					$ids[] = (int) $pid;
					update_post_meta( $pid, '_mep_sample_attendee', $sample_key );
					update_post_meta( $pid, 'ea_flag', 'sample_attendee' );
					if ( ! empty( $item['checkin'] ) && 'Yes' === $item['checkin'] ) {
						update_post_meta( $pid, 'mep_checkin', 'Yes' );
					}
					if ( ! empty( $item['company'] ) ) {
						update_post_meta( $pid, 'ea_company', $item['company'] );
					}
					if ( ! empty( $item['desg'] ) ) {
						update_post_meta( $pid, 'ea_desg', $item['desg'] );
					}
				}
				return $ids;
			}

			/**
			 * Build 55 sample attendee people for dummy_data().
			 *
			 * @return array
			 */
			private static function sample_attendee_people() {
				$first = array(
					'Aaron', 'Bella', 'Cameron', 'Diana', 'Elliot', 'Fiona', 'George', 'Holly', 'Ian', 'Julia',
					'Kevin', 'Laura', 'Miles', 'Naomi', 'Oscar', 'Penny', 'Quincy', 'Rachel', 'Steven', 'Tara',
					'Uma', 'Victor', 'Wendy', 'Xavier', 'Yvonne', 'Zach', 'Andrea', 'Blake', 'Celia', 'Derek',
					'Erin', 'Felix', 'Gloria', 'Hugo', 'Iris', 'Jason', 'Kate', 'Leon', 'Mona', 'Nate',
					'Olive', 'Paul', 'Queen', 'Roger', 'Sara', 'Tom', 'Una', 'Vince', 'Willa', 'Xander',
					'Yasmin', 'Zane', 'Amy', 'Brett', 'Claire',
				);
				$last = array(
					'Adams', 'Baker', 'Carter', 'Davis', 'Edwards', 'Fisher', 'Green', 'Hayes', 'Ingram', 'Jones',
					'Kelly', 'Lopez', 'Miller', 'Nelson', 'Owens', 'Parker', 'Quinn', 'Roberts', 'Smith', 'Taylor',
					'Underwood', 'Vargas', 'Walker', 'Xu', 'Young', 'Zimmerman', 'Allen', 'Brooks', 'Clark', 'Dixon',
					'Ellis', 'Ford', 'Garcia', 'Hill', 'Ivy', 'Jenkins', 'King', 'Lewis', 'Moore', 'Norris',
					'Ortiz', 'Perez', 'Queen', 'Reed', 'Stewart', 'Turner', 'Upton', 'Vaughn', 'White', 'York',
					'Abbott', 'Bishop', 'Cohen', 'Drake', 'Evans',
				);
				$companies = array( 'Acme Corp', 'Bright Labs', 'Northwind', 'Summit Group', 'Blue Peak', 'Orbit Inc', 'Cascade Co' );
				$roles     = array( 'Manager', 'Developer', 'Designer', 'Analyst', 'Director', 'Consultant', 'Coordinator' );
				$people    = array();
				for ( $i = 0; $i < 55; $i++ ) {
					$fname = $first[ $i % count( $first ) ];
					$lname = $last[ ( $i * 3 ) % count( $last ) ];
					$name  = $fname . ' ' . $lname;
					$people[ $i ] = array(
						'key'     => 'attendee-' . ( $i + 1 ),
						'name'    => $name,
						'email'   => strtolower( $fname . '.' . $lname . '.' . ( $i + 1 ) ) . '@example.com',
						'phone'   => sprintf( '+1 646-555-%04d', 500 + $i ),
						'company' => $companies[ $i % count( $companies ) ],
						'desg'    => $roles[ $i % count( $roles ) ],
						'checkin' => ( 0 === $i % 3 ) ? 'Yes' : 'No',
					);
				}
				return $people;
			}

			/**
			 * Import sample Review & Rating posts and attach them to events.
			 *
			 * @return int[] Created/existing review IDs.
			 */
			public function dummy_import_reviews() {
				if ( ! post_type_exists( 'mep_events_review' ) ) {
					return array();
				}
				$dummy_data = $this->dummy_data();
				$reviews    = isset( $dummy_data['custom_post']['mep_events_review'] ) ? $dummy_data['custom_post']['mep_events_review'] : array();
				$ids        = array();
				if ( ! is_array( $reviews ) || empty( $reviews ) ) {
					return $ids;
				}
				$events = get_posts(
					array(
						'post_type'      => 'mep_events',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'ASC',
					)
				);
				if ( empty( $events ) ) {
					return $ids;
				}
				$event_count = count( $events );
				foreach ( $events as $event_id ) {
					update_post_meta( $event_id, 'mep_show_review', 'on' );
				}
				foreach ( $reviews as $i => $review ) {
					if ( empty( $review['name'] ) ) {
						continue;
					}
					$existing = get_page_by_title( $review['name'], OBJECT, 'mep_events_review' );
					if ( $existing ) {
						$ids[] = (int) $existing->ID;
						continue;
					}
					$event_id = (int) $events[ $i % $event_count ];
					$post_id  = wp_insert_post(
						array(
							'post_title'   => $review['name'],
							'post_content' => isset( $review['content'] ) ? $review['content'] : '',
							'post_status'  => 'publish',
							'post_type'    => 'mep_events_review',
							'post_author'  => 0,
						)
					);
					if ( is_wp_error( $post_id ) || ! $post_id ) {
						continue;
					}
					$ids[] = (int) $post_id;
					$meta  = isset( $review['post_data'] ) && is_array( $review['post_data'] ) ? $review['post_data'] : array();
					$meta['mep_event_id'] = $event_id;
					if ( empty( $meta['mep_event_rating'] ) ) {
						$meta['mep_event_rating'] = '5';
					}
					if ( empty( $meta['mep_event_review_cust_ID'] ) ) {
						$meta['mep_event_review_cust_ID'] = 0;
					}
					foreach ( $meta as $meta_key => $data ) {
						update_post_meta( $post_id, $meta_key, $data );
					}
				}
				return $ids;
			}

			public function dummy_import_event($index) {
				$dummy_post_inserted = get_option('mep_dummy_already_inserted');
				if ($dummy_post_inserted) return;

				$dummy_data = $this->dummy_data();
				if (!isset($dummy_data['custom_post']['mep_events'][$index])) return;
				
				$dummy_event = $dummy_data['custom_post']['mep_events'][$index];
				$existing = get_page_by_title($dummy_event['name'], OBJECT, 'mep_events');
				if ($existing) return;

				$post_id = wp_insert_post([
					'post_title' => $dummy_event['name'],
					'post_content' => $dummy_event['content'],
					'post_status' => 'publish',
					'post_type' => 'mep_events',
				]);

				if (is_array($dummy_event) && array_key_exists('taxonomy_terms', $dummy_event)) {
					foreach ($dummy_event['taxonomy_terms'] as $taxonomy_term) {
						wp_set_object_terms($post_id, $taxonomy_term['terms'], $taxonomy_term['taxonomy_name'], true);
					}
				}

				if (is_array($dummy_event) && array_key_exists('post_data', $dummy_event)) {
					foreach ($dummy_event['post_data'] as $meta_key => $data) {
						if ($meta_key == 'feature_image') {
							$url = $data;
							$image = media_sideload_image($url, $post_id, null, 'id');
							if (!is_wp_error($image)) {
								set_post_thumbnail($post_id, $image);
							}
						} else {
							update_post_meta($post_id, $meta_key, $data);
						}
					}
					update_option('mep_dummy_post_data_inserted', 'yes');
				}
			}

			public function dummy_import_finalize() {
				$dummy_post_inserted = get_option('mep_dummy_already_inserted');
				if ($dummy_post_inserted) return;

				$args = array(
					'post_type' => 'mep_events',
					'posts_per_page' => -1,
					'post_status' => 'publish'
				);
				$query = new WP_Query($args);
				$related_events = [];
				$gallery_images = [];

				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$related_events[] = get_the_ID();
						if (has_post_thumbnail()) {
							$gallery_images[] = get_post_thumbnail_id();
						}
					}
					wp_reset_postdata();
				}

				$this->add_gallery_images('mep_events', $gallery_images);
				$this->add_related_events('mep_events', $related_events);
				$this->assign_speakers_to_events();
				$this->assign_reg_forms_to_events();
				$this->dummy_import_reviews();
				$this->dummy_import_rsvp_responses();
				$this->dummy_import_event_orders();
				$this->dummy_import_cancel_requests();
				$this->dummy_import_waitlist();
				$this->dummy_import_attendees();

				update_option('mep_dummy_already_inserted', 'yes');
			}

			public function dummy_import() {}

			public function add_gallery_images($custom_post,$images){
				$args = array(
					'post_type'      => $custom_post, 
					'posts_per_page' => -1,           
					'post_status'    => 'publish',    
				);
				$query = new WP_Query($args);
				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$post_id = get_the_ID();
						update_post_meta($post_id, 'mep_gallery_images', $images);
					}
					wp_reset_postdata();
				} else {
					echo "No posts found for the post type: $custom_post";
				}
				
			}

			public function add_related_events($custom_post,$related_events){
				$args = array(
					'post_type'      => $custom_post, 
					'posts_per_page' => -1,           
					'post_status'    => 'publish',    
				);
				$query = new WP_Query($args);
				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$post_id = get_the_ID();
						foreach ($related_events as $related_id) {
							if ($related_id != $post_id) {
								update_post_meta($related_id, 'event_list', $related_events);
							}
						}
					}
					wp_reset_postdata();
				} else {
					echo "No posts found for the post type: $custom_post";
				}
				
			}

			public function dummy_data(): array {
				return [
					'taxonomy' => [
						'mep_cat' => [
							0 => ['name' => 'Business Event'],
							1 => ['name' => 'Cooking Class'],
							2 => ['name' => 'Home Event'],
							3 => ['name' => 'Indoor Games'],
							4 => ['name' => 'Live Event'],
							5 => ['name' => 'Online Event'],
							6 => ['name' => 'Other Event'],
							7 => ['name' => 'Press Conference Event'],
							8 => ['name' => 'Reunion Event'],
						],
						'mep_org' => [
							0 => ['name' => 'Best Buy Ltd'],
							1 => [
								'name' => 'Cooking Studio',
								'tax_data' => [
									'org_location' => '',
									'org_street' => '',
									'org_city' => '',
									'org_state' => '',
									'org_postcode' => '',
									'org_country' => '',
									'latitude' => '',
									'longitude' => '',
								],
							],
							2 => [
								'name' => 'Doogle Inc',
								'tax_data' => [
									'org_location' => '',
									'org_street' => '',
									'org_city' => '',
									'org_state' => '',
									'org_postcode' => '',
									'org_country' => '',
									'latitude' => '',
									'longitude' => '',
								],
							],
							3 => [
								'name' => 'Duperstar LLC',
								'tax_data' => [
									'org_location' => '',
									'org_street' => '',
									'org_city' => '',
									'org_state' => '',
									'org_postcode' => '',
									'org_country' => '',
									'latitude' => '',
									'longitude' => '',
								],
							],
							4 => [
								'name' => 'Myamazon Inc',
								'tax_data' => [
									'org_location' => '',
									'org_street' => '',
									'org_city' => '',
									'org_state' => '',
									'org_postcode' => '',
									'org_country' => '',
									'latitude' => '',
									'longitude' => '',
								],
							],
							5 => [
								'name' => 'Myceremic Industries Ltd',
								'tax_data' => [
									'org_location' => '',
									'org_street' => '',
									'org_city' => '',
									'org_state' => '',
									'org_postcode' => '',
									'org_country' => '',
									'latitude' => '',
									'longitude' => '',
								],
							],
							6 => [
								'name' => 'MyPeople Inc',
								'tax_data' => [
									'org_location' => '',
									'org_street' => '',
									'org_city' => '',
									'org_state' => '',
									'org_postcode' => '',
									'org_country' => '',
									'latitude' => '',
									'longitude' => '',
								],
							],
							7 => [
								'name' => 'RTC Consultants LLC',
								'tax_data' => [
									'org_location' => '',
									'org_street' => '',
									'org_city' => '',
									'org_state' => '',
									'org_postcode' => '',
									'org_country' => '',
									'latitude' => '',
									'longitude' => '',
								],
							],
						],
					],
					'custom_post' => [
						'mep_events' => [
							0 => [
								'name' => 'Beachfront Conference & Networking Event',
								'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
								'taxonomy_terms' => [
									0 => array(
										'taxonomy_name' => 'mep_cat',
										'terms' => array(
											0 => 'Home Event',
											1 => 'Indoor Games',
										)
									),
									1 => array(
										'taxonomy_name' => 'mep_org',
										'terms' => array(
											0 => 'Duperstar LLC',
											1 => 'Doogle Inc',
										)
									)
								],
								'post_data' => [
									//venue/location
									'feature_image' => 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/eventpress/event-1.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
								'mep_location_venue' => 'Hotel Sea Crown, Coxsbazar, Bangladesh',
								'mep_street' => '',
								'mep_city' => '',
								'mep_state' => '',
								'mep_postcode' => '',
								'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_display_slider' => 'off',
									'mep_show_advance_col_status' => 'off',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "Chair with Umbrella",
											'option_details_t' => "Ticket valid for those aged 12 years and older.",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => date('Y-m-d', strtotime('+60 days', strtotime(date('Y-m-d', strtotime('+30 days', time()))))) . '19:00:00',
										),
									),
									//Extra Services
									'mep_events_extra_prices' => array(
										0 => array(
											'option_name' => 'Chips',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										1 => array(
											'option_name' => 'Water',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										2 => array(
											'option_name' => 'Welcome Drink',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
									),
									//Date Time Settings
									'mep_enable_custom_dt_format' => 'off',
									'mep_event_date_format' => 'F j, Y',
									'mep_event_time_format' => 'g:i a',
									'mep_event_custom_date_format' => 'F j, Y',
									'mep_custom_event_time_format' => 'g:i a',
									'mep_time_zone_display' => 'no',
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+8 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+60 days', strtotime($start_date))),
									'event_end_time' => $end_time = "19:00",
									'event_start_datetime' => $start_datetime = $start_date . ' ' . $start_time . ':00',
									'event_end_datetime' => $end_datetime = $end_date . ' ' . $end_time . ':00',
									'event_expire_datetime' => $expire_datetime = $end_date . ' ' . $end_time . ':00',
									//'mep_enable_recurring' => 'no',
									//Event Settings
									'_sku' => '',
									'mep_show_end_datetime' => 'yes',
									'mep_available_seat' => 'on',
									'mep_reset_status' => 'off',
									'mep_member_only_event' => 'for_all',
									'mep_member_only_user_role' => array(
										0 => 'all',
									),
									//Rich text
									'mep_rich_text_status' => 'enable',
									//email
									'mep_event_cc_email_text' => '
												<h2>Your Ticket for {event}</h2>
												<p>Hi <strong>{name}</strong>,</p>
												<p>Thank you for registering for <strong>{event}</strong>!</p>
												<p><strong>Details of Your Ticket:</strong></p>
												<ul>
													<li>Ticket Type:<strong>{ticket_type}</strong></li>
													<li>Event Date:<strong>{event_date}</strong></li>
													<li>Start Time:<strong>{event_time}</strong></li>
												</ul>
												<p>We look forward to seeing you there!</p>
												<p>Best regards,<br>[Your Event Team]</p>
												',
									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),

									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),

									// default theme
									'mep_event_template'=>'default-theme.php',

									//faq settings
									'mep_faq_description'=>'Explore essential details and clear up any doubts about the event.',
									'mep_event_faq' => array(
										0 => array(
											'mep_faq_title' => 'Who can attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										1 => array(
											'mep_faq_title' => 'How to attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										2 => array(
											'mep_faq_title' => 'When is the event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										3 => array(
											'mep_faq_title' => 'What is the exact location?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
									),
									//Daywise Details
									'mep_event_day' => array(
										[
										'mep_day_title' => 'Pre-Event Setup',
										'mep_day_time' => '8:00 AM - 9:00 AM',
										'mep_day_content' => 'Venue setup: arrange seating, stage, podium, and registration desk. <br>Test AV equipment: microphones, projectors, screens, and internet connections. <br>Set up signage, banners, and branding materials',
										],
										[
										'mep_day_title' => 'Morning Session',
										'mep_day_time' => 	'9:00 AM - 12:00 PM',
										'mep_day_content' => 'Welcome speech by the host/emcee. <br>Overview of the seminar agenda and objectives. <br>Topic: "The Future of IT in Business."',
										],
										[
										'mep_day_title' => 'Lunch Break',
										'mep_day_time'  =>  '12:00 PM - 1:00 PM',
										'mep_day_content' => ' Lunch served. Open networking opportunity for attendees. <br>Session 1: "Cybersecurity Best Practices."',
										],
										[
										'mep_day_title' => 'Post-Event Wrap-Up',
										'mep_day_time'  => '4:30 PM - 5:00 PM',
										'mep_day_content' => ' Collect attendee feedback forms or distribute online survey links. <br>Pack up materials, banners, and equipment. <br>Final networking and informal conversations.',
										],
									),
									'mep_gallery_images' => Array (),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							1 => [
								'name' => 'City Festival & Community Gathering',
								'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
								'taxonomy_terms' => [
									0 => array(
										'taxonomy_name' => 'mep_cat',
										'terms' => array(
											0 => 'Business Event',
											1 => 'Cooking Class',
											2 => 'Home Event',
											3 => 'Indoor Games',
										)
									),
									1 => array(
										'taxonomy_name' => 'mep_org',
										'terms' => array(
											0 => 'Best Buy Ltd',
											1 => 'Cooking Studio',
											2 => 'Duperstar LLC',
											3 => 'Doogle Inc',
										)
									)
								],
								'post_data' => [
									//venue/location
'feature_image' => 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/eventpress/event-2.jpg',
								'mep_event_type' => 'off',
								'mp_event_virtual_type_des' => '',
								'mep_org_address' => '0',
							'mep_location_venue' => 'McCormick Place, Chicago, IL, USA',
								'mep_street' => '',
								'mep_city' => '',
								'mep_state' => '',
								'mep_postcode' => '',
								'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_display_slider' => 'off',
									'mep_show_advance_col_status' => 'off',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "Normal",
											'option_details_t' => "Ticket without Lunch Party",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										1 => array(
											'option_name_t' => "VIP",
											'option_details_t' => "Ticket with Lunch Party",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
									),
									//Extra Services
									'mep_events_extra_prices' => array(
										0 => array(
											'option_name' => 'T-Shirt',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										1 => array(
											'option_name' => 'Logo Printed Mug',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										2 => array(
											'option_name' => 'Welcome Drink',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
									),
									//Date Time Settings
									'mep_enable_custom_dt_format' => 'off',
									'mep_event_date_format' => 'F j, Y',
									'mep_event_time_format' => 'g:i a',
									'mep_event_custom_date_format' => 'F j, Y',
									'mep_custom_event_time_format' => 'g:i a',
									'mep_time_zone_display' => 'no',
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+10 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+90 days', strtotime($start_date))),
									'event_end_time' => $end_time = "19:00",
									'event_start_datetime' => $start_datetime = $start_date . ' ' . $start_time . ':00',
									'event_end_datetime' => $end_datetime = $end_date . ' ' . $end_time . ':00',
									'event_expire_datetime' => $expire_datetime = $end_date . ' ' . $end_time . ':00',
									//'mep_enable_recurring' => 'no',
									//Event Settings
									'_sku' => '',
									'mep_show_end_datetime' => 'yes',
									'mep_available_seat' => 'on',
									'mep_reset_status' => 'off',
									'mep_member_only_event' => 'for_all',
									'mep_member_only_user_role' => array(
										0 => 'all',
									),
									//Rich text
									'mep_rich_text_status' => 'enable',
									//email
									'mep_event_cc_email_text' => '
												<h2>Your Ticket for {event}</h2>
												<p>Hi <strong>{name}</strong>,</p>
												<p>Thank you for registering for <strong>{event}</strong>!</p>
												<p><strong>Details of Your Ticket:</strong></p>
												<ul>
													<li>Ticket Type:<strong>{ticket_type}</strong></li>
													<li>Event Date:<strong>{event_date}</strong></li>
													<li>Start Time:<strong>{event_time}</strong></li>
												</ul>
												<p>We look forward to seeing you there!</p>
												<p>Best regards,<br>[Your Event Team]</p>
											',
									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),

									// default theme
									'mep_event_template'=>'default-theme.php',

									//faq settings
									'mep_faq_description'=>'Explore essential details and clear up any doubts about the event.',
									'mep_event_faq' => array(
										0 => array(
											'mep_faq_title' => 'Who can attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										1 => array(
											'mep_faq_title' => 'How to attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										2 => array(
											'mep_faq_title' => 'When is the event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										3 => array(
											'mep_faq_title' => 'What is the exact location?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
									),
									//Daywise Details
									'mep_event_day' => array(
										[
										'mep_day_title' => 'Pre-Event Setup',
										'mep_day_time' => '8:00 AM - 9:00 AM',
										'mep_day_content' => 'Venue setup: arrange seating, stage, podium, and registration desk. <br>Test AV equipment: microphones, projectors, screens, and internet connections. <br>Set up signage, banners, and branding materials',
										],
										[
										'mep_day_title' => 'Morning Session',
										'mep_day_time'  => '9:00 AM - 12:00 PM',
										'mep_day_content' => 'Welcome speech by the host/emcee. <br>Overview of the seminar agenda and objectives. <br>Topic: "The Future of IT in Business."',
										],
										[
										'mep_day_title' => 'Lunch Break',
										'mep_day_time'  => '12:00 PM - 1:00 PM',
										'mep_day_content' => ' Lunch served. Open networking opportunity for attendees. <br>Session 1: "Cybersecurity Best Practices."',
										],
										[
										'mep_day_title' => 'Post-Event Wrap-Up',
										'mep_day_time' => '4:30 PM - 5:00 PM',
										'mep_day_content' => ' Collect attendee feedback forms or distribute online survey links. <br>Pack up materials, banners, and equipment. <br>Final networking and informal conversations.',
										],
									),
									'mep_gallery_images' => Array (),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							2 => [
								'name' => 'Modern Business Meetup & Workshop',
								'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
								'taxonomy_terms' => [
									0 => array(
										'taxonomy_name' => 'mep_cat',
										'terms' => array(
											0 => 'Indoor Games',
										)
									),
									1 => array(
										'taxonomy_name' => 'mep_org',
										'terms' => array(
											0 => 'Best Buy Ltd',
											1 => 'Cooking Studio',
											2 => 'Duperstar LLC',
										)
									)
								],
								'post_data' => [
									//venue/location
									'feature_image' => 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/eventpress/event-3.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
								'mep_location_venue' => 'The Shed at Hudson Yards, New York, NY, USA',
								'mep_street' => '',
								'mep_city' => '',
								'mep_state' => '',
								'mep_postcode' => '',
								'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_display_slider' => 'off',
									'mep_show_advance_col_status' => 'off',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "Adult",
											'option_details_t' => "This ticket is valid for those above the age of 12 years old.",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										1 => array(
											'option_name_t' => "Child",
											'option_details_t' => "This ticket is valid for those under the age of 12 years old.",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
									),
									//Extra Services
									'mep_events_extra_prices' => array(
										0 => array(
											'option_name' => 'T-Shirt',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										1 => array(
											'option_name' => 'Logo Printed Mug',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										2 => array(
											'option_name' => 'Welcome Drink',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
									),
									//Date Time Settings
									'mep_enable_custom_dt_format' => 'off',
									'mep_event_date_format' => 'F j, Y',
									'mep_event_time_format' => 'g:i a',
									'mep_event_custom_date_format' => 'F j, Y',
									'mep_custom_event_time_format' => 'g:i a',
									'mep_time_zone_display' => 'no',
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+15 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+80 days', strtotime($start_date))),
									'event_end_time' => $end_time = "19:00",
									'event_start_datetime' => $start_datetime = $start_date . ' ' . $start_time . ':00',
									'event_end_datetime' => $end_datetime = $end_date . ' ' . $end_time . ':00',
									'event_expire_datetime' => $expire_datetime = $end_date . ' ' . $end_time . ':00',
									//'mep_enable_recurring' => 'no',
									//Event Settings
									'_sku' => '',
									'mep_show_end_datetime' => 'yes',
									'mep_available_seat' => 'on',
									'mep_reset_status' => 'off',
									'mep_member_only_event' => 'for_all',
									'mep_member_only_user_role' => array(
										0 => 'all',
									),
									//Rich text
									'mep_rich_text_status' => 'enable',
									//email
									'mep_event_cc_email_text' => '
												<h2>Your Ticket for {event}</h2>
												<p>Hi <strong>{name}</strong>,</p>
												<p>Thank you for registering for <strong>{event}</strong>!</p>
												<p><strong>Details of Your Ticket:</strong></p>
												<ul>
													<li>Ticket Type:<strong>{ticket_type}</strong></li>
													<li>Event Date:<strong>{event_date}</strong></li>
													<li>Start Time:<strong>{event_time}</strong></li>
												</ul>
												<p>We look forward to seeing you there!</p>
												<p>Best regards,<br>[Your Event Team]</p>
											',
									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),

									// default theme
									'mep_event_template'=>'default-theme.php',

									//faq settings
									'mep_faq_description'=>'Explore essential details and clear up any doubts about the event.',
									'mep_event_faq' => array(
										0 => array(
											'mep_faq_title' => 'Who can attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										1 => array(
											'mep_faq_title' => 'How to attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										2 => array(
											'mep_faq_title' => 'When is the event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										3 => array(
											'mep_faq_title' => 'What is the exact location?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
									),
									//Daywise Details
									'mep_event_day' => array(
										[
										'mep_day_title' => 'Pre-Event Setup',
										'mep_day_time' => '8:00 AM - 9:00 AM',
										'mep_day_content' => 'Venue setup: arrange seating, stage, podium, and registration desk. <br>Test AV equipment: microphones, projectors, screens, and internet connections. <br>Set up signage, banners, and branding materials',
										],
										[
										'mep_day_title' => 'Morning Session',
										'mep_day_time' => '9:00 AM - 12:00 PM',
										'mep_day_content' => 'Welcome speech by the host/emcee. <br>Overview of the seminar agenda and objectives. <br>Topic: "The Future of IT in Business."',
										],
										[
										'mep_day_title' => 'Lunch Break',
										'mep_day_time' => '12:00 PM - 1:00 PM',
										'mep_day_content' => ' Lunch served. Open networking opportunity for attendees. <br>Session 1: "Cybersecurity Best Practices."',
										],
										[
										'mep_day_title' => 'Post-Event Wrap-Up',
										'mep_day_time' => '4:30 PM - 5:00 PM',
										'mep_day_content' => ' Collect attendee feedback forms or distribute online survey links. <br>Pack up materials, banners, and equipment. <br>Final networking and informal conversations.',
										],
									),
									'mep_gallery_images' => Array (),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							3 => [
								'name' => 'Exclusive Tech Hiring & Networking Event',
								'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
								'taxonomy_terms' => [
									0 => array(
										'taxonomy_name' => 'mep_cat',
										'terms' => array(
											0 => 'Business Event',
											1 => 'Cooking Class',
										)
									),
									1 => array(
										'taxonomy_name' => 'mep_org',
										'terms' => array(
											0 => 'Best Buy Ltd',
											1 => 'Cooking Studio',
										)
									)
								],
								'post_data' => [
									//venue/location
									'feature_image' => 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/eventpress/event-4.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
								'mep_location_venue' => 'Javits Center, New York, NY, USA',
								'mep_street' => '',
								'mep_city' => '',
								'mep_state' => '',
								'mep_postcode' => '',
								'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_display_slider' => 'off',
									'mep_show_advance_col_status' => 'off',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "VIP",
											'option_details_t' => "Ticket for elite and vip persons.",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										1 => array(
											'option_name_t' => "Normal",
											'option_details_t' => "Ticket for normal persions.",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
									),
									//Extra Services
									'mep_events_extra_prices' => array(
										0 => array(
											'option_name' => 'T-Shirt',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										1 => array(
											'option_name' => 'Logo Printed Mug',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										2 => array(
											'option_name' => 'Welcome Drink',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
									),
									//Date Time Settings
									'mep_enable_custom_dt_format' => 'off',
									'mep_event_date_format' => 'F j, Y',
									'mep_event_time_format' => 'g:i a',
									'mep_event_custom_date_format' => 'F j, Y',
									'mep_custom_event_time_format' => 'g:i a',
									'mep_time_zone_display' => 'no',
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+30 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+100 days', strtotime($start_date))),
									'event_end_time' => $end_time = "19:00",
									'event_start_datetime' => $start_datetime = $start_date . ' ' . $start_time . ':00',
									'event_end_datetime' => $end_datetime = $end_date . ' ' . $end_time . ':00',
									'event_expire_datetime' => $expire_datetime = $end_date . ' ' . $end_time . ':00',
									//'mep_enable_recurring' => 'no',
									//Event Settings
									'_sku' => '',
									'mep_show_end_datetime' => 'yes',
									'mep_available_seat' => 'on',
									'mep_reset_status' => 'off',
									'mep_member_only_event' => 'for_all',
									'mep_member_only_user_role' => array(
										0 => 'all',
									),
									//Rich text
									'mep_rich_text_status' => 'enable',
									//email
									'mep_event_cc_email_text' => '
												<h2>Your Ticket for {event}</h2>
												<p>Hi <strong>{name}</strong>,</p>
												<p>Thank you for registering for <strong>{event}</strong>!</p>
												<p><strong>Details of Your Ticket:</strong></p>
												<ul>
													<li>Ticket Type:<strong>{ticket_type}</strong></li>
													<li>Event Date:<strong>{event_date}</strong></li>
													<li>Start Time:<strong>{event_time}</strong></li>
												</ul>
												<p>We look forward to seeing you there!</p>
												<p>Best regards,<br>[Your Event Team]</p>
											',
									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),

									// default theme
									'mep_event_template'=>'default-theme.php',

									//faq settings
									'mep_faq_description'=>'Explore essential details and clear up any doubts about the event.',
									'mep_event_faq' => array(
										0 => array(
											'mep_faq_title' => 'Who can attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										1 => array(
											'mep_faq_title' => 'How to attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										2 => array(
											'mep_faq_title' => 'When is the event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										3 => array(
											'mep_faq_title' => 'What is the exact location?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
									),
									//Daywise Details
									'mep_event_day' => array(
										[
										'mep_day_title' => 'Pre-Event Setup',
										'mep_day_time'  => '8:00 AM - 9:00 AM',
										'mep_day_content' => 'Venue setup: arrange seating, stage, podium, and registration desk. <br>Test AV equipment: microphones, projectors, screens, and internet connections. <br>Set up signage, banners, and branding materials',
										],
										[
										'mep_day_title' => 'Morning Session',
										'mep_day_time'  => '9:00 AM - 12:00 PM',
										'mep_day_content' => 'Welcome speech by the host/emcee. <br>Overview of the seminar agenda and objectives. <br>Topic: "The Future of IT in Business."',
										],
										[
										'mep_day_title' => 'Lunch Break',
										'mep_day_time'  => '12:00 PM - 1:00 PM',
										'mep_day_content' => ' Lunch served. Open networking opportunity for attendees. <br>Session 1: "Cybersecurity Best Practices."',
										],
										[
										'mep_day_title' => 'Post-Event Wrap-Up',
										'mep_day_time' => '4:30 PM - 5:00 PM',
										'mep_day_content' => ' Collect attendee feedback forms or distribute online survey links. <br>Pack up materials, banners, and equipment. <br>Final networking and informal conversations.',
										],
									),
									'mep_gallery_images' => Array (),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							4 => [
								'name' => 'Luxury Business Gala & Private Networking Night',
								'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
								'taxonomy_terms' => [
									0 => array(
										'taxonomy_name' => 'mep_cat',
										'terms' => array(
											0 => 'Cooking Class',
											1 => 'Home Event',
										)
									),
									1 => array(
										'taxonomy_name' => 'mep_org',
										'terms' => array(
											0 => 'Duperstar LLC',
										)
									)
								],
								'post_data' => [
									//venue/location
									'feature_image' => 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/eventpress/event-5.jpg',
									'mep_event_type' => 'online',
									'mp_event_virtual_type_des' => 'Virtual Event',
									'mep_org_address' => '',
									'mep_location_venue' => '',
									'mep_street' => '',
									'mep_city' => '',
									'mep_state' => '',
									'mep_postcode' => '',
									'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_display_slider' => 'off',
									'mep_show_advance_col_status' => 'off',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "Early Bird ticket",
											'option_details_t' => "Valid for individuals aged 18 and above, providing full access to all designated areas and activities.",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										1 => array(
											'option_name_t' => "Regular/Standards ticket",
											'option_details_t' => "For children aged 3 to 12, offering access to designated areas and activities suitable for young visitors",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										2 => array(
											'option_name_t' => "VIP",
											'option_details_t' => "Valid for individuals aged 18 and above, providing full access to all designated areas and activities",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
									),
									//Extra Services
									'mep_events_extra_prices' => array(
										0 => array(
											'option_name' => 'T-Shirt',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										1 => array(
											'option_name' => 'Logo Printed Mug',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										2 => array(
											'option_name' => 'Welcome Drink',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
									),
									//Date Time Settings
									'mep_enable_custom_dt_format' => 'off',
									'mep_event_date_format' => 'F j, Y',
									'mep_event_time_format' => 'g:i a',
									'mep_event_custom_date_format' => 'F j, Y',
									'mep_custom_event_time_format' => 'g:i a',
									'mep_time_zone_display' => 'no',
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+0 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+70 days', strtotime($start_date))),
									'event_end_time' => $end_time = "19:00",
									'event_start_datetime' => $start_datetime = $start_date . ' ' . $start_time . ':00',
									'event_end_datetime' => $end_datetime = $end_date . ' ' . $end_time . ':00',
									'event_expire_datetime' => $expire_datetime = $end_date . ' ' . $end_time . ':00',
									//'mep_enable_recurring' => 'no',
									//Event Settings
									'_sku' => '',
									'mep_show_end_datetime' => 'yes',
									'mep_available_seat' => 'on',
									'mep_reset_status' => 'off',
									'mep_member_only_event' => 'for_all',
									'mep_member_only_user_role' => array(
										0 => 'all',
									),
									//Rich text
									'mep_rich_text_status' => 'enable',
									//email
									'mep_event_cc_email_text' => '
												<h2>Your Ticket for {event}</h2>
												<p>Hi <strong>{name}</strong>,</p>
												<p>Thank you for registering for <strong>{event}</strong>!</p>
												<p><strong>Details of Your Ticket:</strong></p>
												<ul>
													<li>Ticket Type:<strong>{ticket_type}</strong></li>
													<li>Event Date:<strong>{event_date}</strong></li>
													<li>Start Time:<strong>{event_time}</strong></li>
												</ul>
												<p>We look forward to seeing you there!</p>
												<p>Best regards,<br>[Your Event Team]</p>
											',
									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),

									// default theme
									'mep_event_template'=>'default-theme.php',

									//faq settings
									'mep_faq_description'=>'Explore essential details and clear up any doubts about the event.',
									'mep_event_faq' => array(
										0 => array(
											'mep_faq_title' => 'Who can attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										1 => array(
											'mep_faq_title' => 'How to attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										2 => array(
											'mep_faq_title' => 'When is the event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										3 => array(
											'mep_faq_title' => 'What is the exact location?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
									),
									//Daywise Details
									'mep_event_day' => array(
										[
										'mep_day_title' => 'Pre-Event Setup',
										'mep_day_time' => '8:00 AM - 9:00 AM',
										'mep_day_content' => 'Venue setup: arrange seating, stage, podium, and registration desk. <br>Test AV equipment: microphones, projectors, screens, and internet connections. <br>Set up signage, banners, and branding materials',
										],
										[
										'mep_day_title' => 'Morning Session (9:00 AM - 12:00 PM)',
										'mep_day_time'  => '9:00 AM - 12:00 PM',
										'mep_day_content' => 'Welcome speech by the host/emcee. <br>Overview of the seminar agenda and objectives. <br>Topic: "The Future of IT in Business."',
										],
										[
										'mep_day_title' => 'Lunch Break',
										'mep_day_time' => '12:00 PM - 1:00 PM',
										'mep_day_content' => ' Lunch served. Open networking opportunity for attendees. <br>Session 1: "Cybersecurity Best Practices."',
										],
										[
										'mep_day_title' => 'Post-Event Wrap-Up',
										'mep_day_time' => '4:30 PM - 5:00 PM',
										'mep_day_content' => ' Collect attendee feedback forms or distribute online survey links. <br>Pack up materials, banners, and equipment. <br>Final networking and informal conversations.',
										],
									),
									'mep_gallery_images' => Array (),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							5 => [
								'name' => 'Ultimate Live Music Experience & Concert Night',
								'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
								'taxonomy_terms' => [
									0 => array(
										'taxonomy_name' => 'mep_cat',
										'terms' => array(
											0 => 'Business Event',
											1 => 'Cooking Class',
											2 => 'Home Event',
										)
									),
									1 => array(
										'taxonomy_name' => 'mep_org',
										'terms' => array(
											0 => 'Duperstar LLC',
											1 => 'Doogle Inc',
										)
									)
								],
								'post_data' => [
									//venue/location
'feature_image' => 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/eventpress/event-6.jpg',
								'mep_event_type' => 'off',
								'mp_event_virtual_type_des' => '',
								'mep_org_address' => '0',
							'mep_location_venue' => 'Moscone Center, San Francisco, CA, USA',
								'mep_street' => '',
								'mep_city' => '',
								'mep_state' => '',
								'mep_postcode' => '',
								'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_display_slider' => 'off',
									'mep_show_advance_col_status' => 'off',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "VIP",
											'option_details_t' => "Valid for individuals aged 18 and above, providing full access to all designated areas and activities",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										1 => array(
											'option_name_t' => "Normal",
											'option_details_t' => "Standard entry ticket providing access to the event and all general areas included in the admission",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
									),
									//Extra Services
									'mep_events_extra_prices' => array(
										0 => array(
											'option_name' => 'T-Shirt',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										1 => array(
											'option_name' => 'Logo Printed Mug',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										2 => array(
											'option_name' => 'Welcome Drink',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
									),
									//Date Time Settings
									'mep_enable_custom_dt_format' => 'off',
									'mep_event_date_format' => 'F j, Y',
									'mep_event_time_format' => 'g:i a',
									'mep_event_custom_date_format' => 'F j, Y',
									'mep_custom_event_time_format' => 'g:i a',
									'mep_time_zone_display' => 'no',
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+0 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+65 days', strtotime($start_date))),
									'event_end_time' => $end_time = "19:00",
									'event_start_datetime' => $start_datetime = $start_date . ' ' . $start_time . ':00',
									'event_end_datetime' => $end_datetime = $end_date . ' ' . $end_time . ':00',
									'event_expire_datetime' => $expire_datetime = $end_date . ' ' . $end_time . ':00',
									//'mep_enable_recurring' => 'no',
									//Event Settings
									'_sku' => '',
									'mep_show_end_datetime' => 'yes',
									'mep_available_seat' => 'on',
									'mep_reset_status' => 'off',
									'mep_member_only_event' => 'for_all',
									'mep_member_only_user_role' => array(
										0 => 'all',
									),
									//Rich text
									'mep_rich_text_status' => 'enable',
									//email
									'mep_event_cc_email_text' => '
												<h2>Your Ticket for {event}</h2>
												<p>Hi <strong>{name}</strong>,</p>
												<p>Thank you for registering for <strong>{event}</strong>!</p>
												<p><strong>Details of Your Ticket:</strong></p>
												<ul>
													<li>Ticket Type:<strong>{ticket_type}</strong></li>
													<li>Event Date:<strong>{event_date}</strong></li>
													<li>Start Time:<strong>{event_time}</strong></li>
												</ul>
												<p>We look forward to seeing you there!</p>
												<p>Best regards,<br>[Your Event Team]</p>
											',
									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),

									// default theme
									'mep_event_template'=>'default-theme.php',
									
									//faq settings
									'mep_faq_description'=>'Explore essential details and clear up any doubts about the event.',
									'mep_event_faq' => array(
										0 => array(
											'mep_faq_title' => 'Who can attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										1 => array(
											'mep_faq_title' => 'How to attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										2 => array(
											'mep_faq_title' => 'When is the event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										3 => array(
											'mep_faq_title' => 'What is the exact location?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
									),
									//Daywise Details
									'mep_event_day' => array(
										[
										'mep_day_title' => 'Pre-Event Setup',
										'mep_day_time' => '8:00 AM - 9:00 AM',
										'mep_day_content' => 'Venue setup: arrange seating, stage, podium, and registration desk. <br>Test AV equipment: microphones, projectors, screens, and internet connections. <br>Set up signage, banners, and branding materials',
										],
										[
										'mep_day_title' => 'Morning Session',
										'mep_day_time' => '9:00 AM - 12:00 PM',
										'mep_day_content' => 'Welcome speech by the host/emcee. <br>Overview of the seminar agenda and objectives. <br>Topic: "The Future of IT in Business."',
										],
										[
										'mep_day_title' => 'Lunch Break',
										'mep_day_time' => '12:00 PM - 1:00 PM',
										'mep_day_content' => ' Lunch served. Open networking opportunity for attendees. <br>Session 1: "Cybersecurity Best Practices."',
										],
										[
										'mep_day_title' => 'Post-Event Wrap-Up',
										'mep_day_time' => '4:30 PM - 5:00 PM',
										'mep_day_content' => ' Collect attendee feedback forms or distribute online survey links. <br>Pack up materials, banners, and equipment. <br>Final networking and informal conversations.',
										],
									),
									'mep_gallery_images' => Array (),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							6 => [
								'name' => 'Live Music & Cannabis Industry Expo',
								'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
								'taxonomy_terms' => [
									0 => array(
										'taxonomy_name' => 'mep_cat',
										'terms' => array(
											0 => 'Home Event',
											1 => 'Indoor Games',
										)
									),
									1 => array(
										'taxonomy_name' => 'mep_org',
										'terms' => array(
											0 => 'Cooking Studio',
											1 => 'Duperstar LLC',
											2 => 'Doogle Inc',
										)
									)
								],
								'post_data' => [
									//venue/location
									'feature_image' => 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/eventpress/event-7.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
								'mep_location_venue' => 'Estrel Berlin, Sonnenallee 225, Berlin, Germany',
								'mep_street' => '',
								'mep_city' => '',
								'mep_state' => '',
								'mep_postcode' => '',
								'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_display_slider' => 'off',
									'mep_show_advance_col_status' => 'off',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "General",
											'option_details_t' => "Valid for individuals aged 18 and above, providing full access to all designated areas and activities",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										1 => array(
											'option_name_t' => "Sponsored",
											'option_details_t' => "For children aged 3 to 12, offering access to designated areas and activities suitable for young visitors",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										2 => array(
											'option_name_t' => "Free",
											'option_details_t' => "Standard entry ticket providing access to the event and all general areas included in the admission.",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
									),
									//Extra Services
									'mep_events_extra_prices' => array(
										0 => array(
											'option_name' => 'T-Shirt',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										1 => array(
											'option_name' => 'Logo Printed Mug',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										2 => array(
											'option_name' => 'Welcome Drink',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
									),
									//Date Time Settings
									'mep_enable_custom_dt_format' => 'off',
									'mep_event_date_format' => 'F j, Y',
									'mep_event_time_format' => 'g:i a',
									'mep_event_custom_date_format' => 'F j, Y',
									'mep_custom_event_time_format' => 'g:i a',
									'mep_time_zone_display' => 'no',
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+0 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+10 days', strtotime($start_date))),
									'event_end_time' => $end_time = "19:00",
									'event_start_datetime' => $start_datetime = $start_date . ' ' . $start_time . ':00',
									'event_end_datetime' => $end_datetime = $end_date . ' ' . $end_time . ':00',
									'event_expire_datetime' => $expire_datetime = $end_date . ' ' . $end_time . ':00',
									'event_start_date_everyday' => $start_date,
									'event_start_time_everyday' => $start_time,
									'event_end_date_everyday' => $end_date,
									'event_end_time_everyday' => $end_time,
									
									'mep_enable_recurring' => 'everyday',
									//Event Settings
									'_sku' => '',
									'mep_show_end_datetime' => 'yes',
									'mep_available_seat' => 'on',
									'mep_reset_status' => 'off',
									'mep_member_only_event' => 'for_all',
									'mep_member_only_user_role' => array(
										0 => 'all',
									),
									//Rich text
									'mep_rich_text_status' => 'enable',
									//email
									'mep_event_cc_email_text' => '
												<h2>Your Ticket for {event}</h2>
												<p>Hi <strong>{name}</strong>,</p>
												<p>Thank you for registering for <strong>{event}</strong>!</p>
												<p><strong>Details of Your Ticket:</strong></p>
												<ul>
													<li>Ticket Type:<strong>{ticket_type}</strong></li>
													<li>Event Date:<strong>{event_date}</strong></li>
													<li>Start Time:<strong>{event_time}</strong></li>
												</ul>
												<p>We look forward to seeing you there!</p>
												<p>Best regards,<br>[Your Event Team]</p>
											',
									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),

									// default theme
									'mep_event_template'=>'default-theme.php',

									//faq settings
									'mep_faq_description'=>'Explore essential details and clear up any doubts about the event.',
									'mep_event_faq' => array(
										0 => array(
											'mep_faq_title' => 'Who can attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										1 => array(
											'mep_faq_title' => 'How to attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										2 => array(
											'mep_faq_title' => 'When is the event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										3 => array(
											'mep_faq_title' => 'What is the exact location?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
									),
									//Daywise Details
									'mep_event_day' => array(
										[
										'mep_day_title' => 'Pre-Event Setup',
										'mep_day_time' => '8:00 AM - 9:00 AM',
										'mep_day_content' => 'Venue setup: arrange seating, stage, podium, and registration desk. <br>Test AV equipment: microphones, projectors, screens, and internet connections. <br>Set up signage, banners, and branding materials',
										],
										[
										'mep_day_title' => 'Morning Session',
										'mep_day_time'  => '9:00 AM - 12:00 PM',
										'mep_day_content' => 'Welcome speech by the host/emcee. <br>Overview of the seminar agenda and objectives. <br>Topic: "The Future of IT in Business."',
										],
										[
										'mep_day_title' => 'Lunch Break (12:00 PM - 1:00 PM)',
										'mep_day_time' 	=> '12:00 PM - 1:00 PM',
										'mep_day_content' => ' Lunch served. Open networking opportunity for attendees. <br>Session 1: "Cybersecurity Best Practices."',
										],
										[
										'mep_day_title' => 'Post-Event Wrap-Up',
										'mep_day_time' 	=> '4:30 PM - 5:00 PM',
										'mep_day_content' => ' Collect attendee feedback forms or distribute online survey links. <br>Pack up materials, banners, and equipment. <br>Final networking and informal conversations.',
										],
									),
									'mep_gallery_images' => Array (),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							7 => [
								'name' => 'Global Economic Leadership Summit',
								'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
								'taxonomy_terms' => [
									0 => array(
										'taxonomy_name' => 'mep_cat',
										'terms' => array(
											0 => 'Business Event',
											1 => 'Cooking Class',
										)
									),
									1 => array(
										'taxonomy_name' => 'mep_org',
										'terms' => array(
											0 => 'Best Buy Ltd',
											1 => 'Cooking Studio',
											2 => 'Duperstar LLC',
										)
									)
								],
								'post_data' => [
									//venue/location
									'feature_image' => 'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/eventpress/event-8.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
								'mep_location_venue' => 'Sheffield City Hall, Barkers Pool, Sheffield S1 2HB, UK',
								'mep_street' => '',
								'mep_city' => '',
								'mep_state' => '',
								'mep_postcode' => '',
								'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_display_slider' => 'off',
									'mep_show_advance_col_status' => 'off',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "VIP",
											'option_details_t' => "Dinner Party Ticket Included with this Ticket",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										1 => array(
											'option_name_t' => "Medium",
											'option_details_t' => "Dinner Party Ticket Included with this Ticket",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
										2 => array(
											'option_name_t' => "Normal Chair",
											'option_details_t' => "Ticket without Dinner Party",
											'option_price_t' => "100",
											'option_qty_t' => "200",
											'option_rsv_t' => "0",
											'option_default_qty_t' => "0",
											'option_qty_t_type' => "inputbox",
											'option_sale_end_date' => "",
											'option_sale_end_time' => "",
											'option_sale_end_date_t' => $end_date . ' ' . $end_time . ':00',
										),
									),
									//Extra Services
									'mep_events_extra_prices' => array(
										0 => array(
											'option_name' => 'T-Shirt',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										1 => array(
											'option_name' => 'Logo Printed Mug',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
										2 => array(
											'option_name' => 'Welcome Drink',
											'option_price' => '150',
											'option_qty' => '100',
											'option_qty_type' => 'inputbox',
										),
									),
									//Date Time Settings
									'mep_enable_custom_dt_format' => 'off',
									'mep_event_date_format' => 'F j, Y',
									'mep_event_time_format' => 'g:i a',
									'mep_event_custom_date_format' => 'F j, Y',
									'mep_custom_event_time_format' => 'g:i a',
									'mep_time_zone_display' => 'no',
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+0 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+10 days', strtotime($start_date))),
									'event_end_time' => $end_time = "19:00",
									'event_start_datetime' => $start_datetime = $start_date . ' ' . $start_time . ':00',
									'event_end_datetime' => $end_datetime = $end_date . ' ' . $end_time . ':00',
									'event_expire_datetime' => $expire_datetime = $end_date . ' ' . $end_time . ':00',
									'mep_event_more_date' =>[
										[
											'event_more_start_date' => $start_date = date('Y-m-d', strtotime('+40 days', time())),
											'event_more_start_time' => $start_time = "09:00",
											'event_more_end_date' => $end_date = date('Y-m-d', strtotime('+10 days', strtotime($start_date))),
											'event_more_end_time' => $end_time = "19:00",
										],
										[
											'event_more_start_date' => $start_date = date('Y-m-d', strtotime('+50 days', time())),
											'event_more_start_time' => $start_time = "09:00",
											'event_more_end_date' => $end_date = date('Y-m-d', strtotime('+10 days', strtotime($start_date))),
											'event_more_end_time' => $end_time = "19:00",
										],
										[
											'event_more_start_date' => $start_date = date('Y-m-d', strtotime('+60 days', time())),
											'event_more_start_time' => $start_time = "09:00",
											'event_more_end_date' => $end_date = date('Y-m-d', strtotime('+10 days', strtotime($start_date))),
											'event_more_end_time' => $end_time = "19:00",
										],
										[
											'event_more_start_date' => $start_date = date('Y-m-d', strtotime('+70 days', time())),
											'event_more_start_time' => $start_time = "09:00",
											'event_more_end_date' => $end_date = date('Y-m-d', strtotime('+10 days', strtotime($start_date))),
											'event_more_end_time' => $end_time = "19:00",
										],
									],
									'mep_enable_recurring' => 'yes',
									//Event Settings
									'_sku' => '',
									'mep_show_end_datetime' => 'yes',
									'mep_available_seat' => 'on',
									'mep_reset_status' => 'off',
									'mep_member_only_event' => 'for_all',
									'mep_member_only_user_role' => array(
										0 => 'all',
									),
									//Rich text
									'mep_rich_text_status' => 'enable',
									//email
									'mep_event_cc_email_text' => '
												<h2>Your Ticket for {event}</h2>
												<p>Hi <strong>{name}</strong>,</p>
												<p>Thank you for registering for <strong>{event}</strong>!</p>
												<p><strong>Details of Your Ticket:</strong></p>
												<ul>
													<li>Ticket Type:<strong>{ticket_type}</strong></li>
													<li>Event Date:<strong>{event_date}</strong></li>
													<li>Start Time:<strong>{event_time}</strong></li>
												</ul>
												<p>We look forward to seeing you there!</p>
												<p>Best regards,<br>[Your Event Team]</p>
											',

									// related events settings
									'mep_related_event_status'=>'on',
									'related_section_label'=>'Releted Events',
									'event_list'=>array(),
									
									// default theme
									'mep_event_template'=>'default-theme.php',

									//faq settings
									'mep_faq_description'=>'Explore essential details and clear up any doubts about the event.',
									'mep_event_faq' => array(
										0 => array(
											'mep_faq_title' => 'Who can attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										1 => array(
											'mep_faq_title' => 'How to attend this event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										2 => array(
											'mep_faq_title' => 'When is the event?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
										3 => array(
											'mep_faq_title' => 'What is the exact location?',
											'mep_faq_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
										),
									),
									//Daywise Details
									'mep_event_day' => array(
										[
										'mep_day_title' => 'Pre-Event Setup',
										'mep_day_time' => '8:00 AM - 9:00 AM',
										'mep_day_content' => 'Venue setup: arrange seating, stage, podium, and registration desk. <br>Test AV equipment: microphones, projectors, screens, and internet connections. <br>Set up signage, banners, and branding materials',
										],
										[
										'mep_day_title' => 'Morning Session',
										'mep_day_time' => '9:00 AM - 12:00 PM',
										'mep_day_content' => 'Welcome speech by the host/emcee. <br>Overview of the seminar agenda and objectives. <br>Topic: "The Future of IT in Business."',
										],
										[
										'mep_day_title' => 'Lunch Break',
										'mep_day_time' 	=> '12:00 PM - 1:00 PM',
										'mep_day_content' => ' Lunch served. Open networking opportunity for attendees. <br>Session 1: "Cybersecurity Best Practices."',
										],
										[
										'mep_day_title' => 'Post-Event Wrap-Up',
										'mep_day_time' 	=> '4:30 PM - 5:00 PM',
										'mep_day_content' => ' Collect attendee feedback forms or distribute online survey links. <br>Pack up materials, banners, and equipment. <br>Final networking and informal conversations.',
										],
									),
									'mep_gallery_images' => Array (),
									'mep_list_thumbnail' => '',
									
									'mep_total_seat_left' => '0',
								],
							],
						],
						'mep_event_speaker' => [
							0 => [
								'name' => 'Alex Rivera',
								'excerpt' => 'Keynote Speaker · CEO, TechVision',
								'content' => 'Alex Rivera is a keynote speaker and CEO of TechVision, known for translating complex product strategy into practical growth playbooks for event audiences.',
								'post_data' => [
									'feature_image' => 'https://randomuser.me/api/portraits/men/32.jpg',
								],
							],
							1 => [
								'name' => 'Maya Chen',
								'excerpt' => 'Product Strategist · NorthPeak Labs',
								'content' => 'Maya Chen leads product strategy at NorthPeak Labs and helps teams design customer journeys that convert curiosity into long-term engagement.',
								'post_data' => [
									'feature_image' => 'https://randomuser.me/api/portraits/women/44.jpg',
								],
							],
							2 => [
								'name' => 'Jordan Blake',
								'excerpt' => 'AI Researcher · OpenForge AI',
								'content' => 'Jordan Blake researches applied machine learning and speaks about responsible AI adoption for businesses of every size.',
								'post_data' => [
									'feature_image' => 'https://randomuser.me/api/portraits/men/11.jpg',
								],
							],
							3 => [
								'name' => 'Sofia Alvarez',
								'excerpt' => 'UX Director · BrightCanvas',
								'content' => 'Sofia Alvarez is a UX director focused on inclusive design systems, accessibility, and memorable brand experiences.',
								'post_data' => [
									'feature_image' => 'https://randomuser.me/api/portraits/women/68.jpg',
								],
							],
							4 => [
								'name' => 'Liam Okonkwo',
								'excerpt' => 'Cloud Architect · NimbusWorks',
								'content' => 'Liam Okonkwo designs scalable cloud platforms and shares practical guidance on reliability, cost control, and modern infrastructure.',
								'post_data' => [
									'feature_image' => 'https://randomuser.me/api/portraits/men/75.jpg',
								],
							],
							5 => [
								'name' => 'Priya Nair',
								'excerpt' => 'Growth Lead · Orbit Commerce',
								'content' => 'Priya Nair specializes in growth marketing loops, lifecycle automation, and data-backed campaign experimentation.',
								'post_data' => [
									'feature_image' => 'https://randomuser.me/api/portraits/women/21.jpg',
								],
							],
						],
						'mep_events_reg_form' => [
							0 => [
								'name' => 'Standard Attendee Form',
								'post_data' => [
									'mep_full_name'       => '1',
									'mep_reg_email'       => '1',
									'mep_reg_phone'       => '1',
									'mep_reg_address'     => '',
									'mep_reg_designation' => '',
									'mep_reg_website'     => '',
									'mep_reg_veg'         => '',
									'mep_reg_company'     => '',
									'mep_reg_gender'      => '',
									'mep_reg_tshirtsize'  => '',
									'mep_name_label'      => 'Full Name',
									'mep_email_label'     => 'Email Address',
									'mep_phone_label'     => 'Phone Number',
									'mep_form_builder_data' => [
										[
											'mep_fbc_id'       => 'how-did-you-hear',
											'mep_fbc_label'    => 'How did you hear about this event?',
											'mep_fbc_type'     => 'select',
											'mep_fbc_required' => '',
											'mep_fbc_dp_data'  => 'Social Media,Friend Referral,Email Newsletter,Search Engine,Other',
										],
										[
											'mep_fbc_id'       => 'special-notes',
											'mep_fbc_label'    => 'Special requests or notes',
											'mep_fbc_type'     => 'textarea',
											'mep_fbc_required' => '',
										],
									],
								],
							],
							1 => [
								'name' => 'Business Conference Form',
								'post_data' => [
									'mep_full_name'       => '1',
									'mep_reg_email'       => '1',
									'mep_reg_phone'       => '1',
									'mep_reg_address'     => '',
									'mep_reg_designation' => '1',
									'mep_reg_website'     => '1',
									'mep_reg_veg'         => '',
									'mep_reg_company'     => '1',
									'mep_reg_gender'      => '',
									'mep_reg_tshirtsize'  => '',
									'mep_name_label'      => 'Full Name',
									'mep_email_label'     => 'Work Email',
									'mep_phone_label'     => 'Mobile Number',
									'mep_desg_label'      => 'Job Title',
									'mep_company_label'   => 'Company / Organization',
									'mep_website_label'   => 'Company Website',
									'mep_form_builder_data' => [
										[
											'mep_fbc_id'       => 'industry',
											'mep_fbc_label'    => 'Industry',
											'mep_fbc_type'     => 'select',
											'mep_fbc_required' => '1',
											'mep_fbc_dp_data'  => 'Technology,Finance,Healthcare,Education,Retail,Other',
										],
										[
											'mep_fbc_id'       => 'networking-goals',
											'mep_fbc_label'    => 'What are your networking goals?',
											'mep_fbc_type'     => 'textarea',
											'mep_fbc_required' => '',
										],
										[
											'mep_fbc_id'       => 'linkedin-profile',
											'mep_fbc_label'    => 'LinkedIn Profile URL',
											'mep_fbc_type'     => 'text',
											'mep_fbc_required' => '',
										],
									],
								],
							],
							2 => [
								'name' => 'Workshop Registration Form',
								'post_data' => [
									'mep_full_name'            => '1',
									'mep_reg_email'            => '1',
									'mep_reg_phone'            => '1',
									'mep_reg_address'          => '1',
									'mep_reg_designation'      => '',
									'mep_reg_website'          => '',
									'mep_reg_veg'              => '1',
									'mep_reg_company'          => '',
									'mep_reg_gender'           => '1',
									'mep_reg_tshirtsize'       => '1',
									'mep_reg_tshirtsize_list'  => 'S,M,L,XL,XXL',
									'mep_name_label'           => 'Participant Name',
									'mep_email_label'          => 'Email',
									'mep_phone_label'          => 'Phone',
									'mep_address_label'        => 'Mailing Address',
									'mep_veg_label'            => 'Meal Preference',
									'mep_gender_label'         => 'Gender',
									'mep_tshirt_label'         => 'T-Shirt Size',
									'mep_form_builder_data'    => [
										[
											'mep_fbc_id'       => 'experience-level',
											'mep_fbc_label'    => 'Experience Level',
											'mep_fbc_type'     => 'radio',
											'mep_fbc_required' => '1',
											'mep_fbc_dp_data'  => 'Beginner,Intermediate,Advanced',
										],
										[
											'mep_fbc_id'       => 'session-interest',
											'mep_fbc_label'    => 'Preferred Session Track',
											'mep_fbc_type'     => 'select',
											'mep_fbc_required' => '',
											'mep_fbc_dp_data'  => 'Design,Development,Marketing,Leadership',
										],
										[
											'mep_fbc_id'       => 'emergency-contact',
											'mep_fbc_label'    => 'Emergency Contact Name & Phone',
											'mep_fbc_type'     => 'text',
											'mep_fbc_required' => '1',
										],
									],
								],
							],
						],
						'mep_events_review' => [
							0 => [
								'name' => 'Outstanding experience overall',
								'content' => 'The event was beautifully organized from check-in to the closing session. Speakers were engaging and the venue setup made networking effortless.',
								'post_data' => [
									'mep_event_rating'            => '5',
									'mep_event_review_cust_name'  => 'Hannah Brooks',
									'mep_event_review_cust_email' => 'hannah.brooks@example.com',
								],
							],
							1 => [
								'name' => 'Great speakers and atmosphere',
								'content' => 'I attended for the keynotes and left with practical takeaways I could use immediately. The staff was helpful and the schedule ran on time.',
								'post_data' => [
									'mep_event_rating'            => '5',
									'mep_event_review_cust_name'  => 'Daniel Okoro',
									'mep_event_review_cust_email' => 'daniel.okoro@example.com',
								],
							],
							2 => [
								'name' => 'Well organized networking event',
								'content' => 'Plenty of opportunities to meet peers and partners. The registration process was smooth and the breakout rooms were easy to find.',
								'post_data' => [
									'mep_event_rating'            => '4',
									'mep_event_review_cust_name'  => 'Sophie Laurent',
									'mep_event_review_cust_email' => 'sophie.laurent@example.com',
								],
							],
							3 => [
								'name' => 'Informative sessions with minor delays',
								'content' => 'Content quality was excellent. A few sessions started a little late, but the hosts recovered well and kept energy high throughout the day.',
								'post_data' => [
									'mep_event_rating'            => '4',
									'mep_event_review_cust_name'  => 'Michael Trent',
									'mep_event_review_cust_email' => 'michael.trent@example.com',
								],
							],
							4 => [
								'name' => 'Solid value for the ticket price',
								'content' => 'Worth attending if you want industry updates and practical workshops. Catering was good and the Q&A segments were especially useful.',
								'post_data' => [
									'mep_event_rating'            => '5',
									'mep_event_review_cust_name'  => 'Priya Desai',
									'mep_event_review_cust_email' => 'priya.desai@example.com',
								],
							],
							5 => [
								'name' => 'Enjoyable but could improve seating',
								'content' => 'I enjoyed the program and met interesting people. Seating in the main hall felt tight during peak sessions, but otherwise a strong event.',
								'post_data' => [
									'mep_event_rating'            => '3',
									'mep_event_review_cust_name'  => 'James Carter',
									'mep_event_review_cust_email' => 'james.carter@example.com',
								],
							],
						],
						'mep_rsvp_responses' => [
							0  => [ 'name' => 'Emma Thompson',      'email' => 'emma.thompson@example.com',      'phone' => '+1 202-555-0101', 'qty' => 1, 'checkin' => 'Yes' ],
							1  => [ 'name' => 'Noah Patel',         'email' => 'noah.patel@example.com',         'phone' => '+1 202-555-0102', 'qty' => 2, 'checkin' => 'Yes' ],
							2  => [ 'name' => 'Olivia Martinez',    'email' => 'olivia.martinez@example.com',    'phone' => '+1 202-555-0103', 'qty' => 1, 'checkin' => 'No' ],
							3  => [ 'name' => 'Liam Chen',          'email' => 'liam.chen@example.com',          'phone' => '+1 202-555-0104', 'qty' => 3, 'checkin' => 'Yes' ],
							4  => [ 'name' => 'Ava Johnson',        'email' => 'ava.johnson@example.com',        'phone' => '+1 202-555-0105', 'qty' => 1, 'checkin' => 'No' ],
							5  => [ 'name' => 'William Garcia',     'email' => 'william.garcia@example.com',     'phone' => '+1 202-555-0106', 'qty' => 2, 'checkin' => 'Yes' ],
							6  => [ 'name' => 'Sophia Nguyen',      'email' => 'sophia.nguyen@example.com',      'phone' => '+1 202-555-0107', 'qty' => 1, 'checkin' => 'No' ],
							7  => [ 'name' => 'James Wilson',       'email' => 'james.wilson@example.com',       'phone' => '+1 202-555-0108', 'qty' => 4, 'checkin' => 'Yes' ],
							8  => [ 'name' => 'Isabella Rossi',     'email' => 'isabella.rossi@example.com',     'phone' => '+1 202-555-0109', 'qty' => 1, 'checkin' => 'No' ],
							9  => [ 'name' => 'Benjamin Kim',       'email' => 'benjamin.kim@example.com',       'phone' => '+1 202-555-0110', 'qty' => 2, 'checkin' => 'Yes' ],
							10 => [ 'name' => 'Mia Andersson',      'email' => 'mia.andersson@example.com',      'phone' => '+1 202-555-0111', 'qty' => 1, 'checkin' => 'No' ],
							11 => [ 'name' => 'Lucas Brown',        'email' => 'lucas.brown@example.com',        'phone' => '+1 202-555-0112', 'qty' => 2, 'checkin' => 'Yes' ],
							12 => [ 'name' => 'Charlotte Dubois',   'email' => 'charlotte.dubois@example.com',   'phone' => '+1 202-555-0113', 'qty' => 1, 'checkin' => 'No' ],
							13 => [ 'name' => 'Henry Silva',        'email' => 'henry.silva@example.com',        'phone' => '+1 202-555-0114', 'qty' => 3, 'checkin' => 'Yes' ],
							14 => [ 'name' => 'Amelia Wright',      'email' => 'amelia.wright@example.com',      'phone' => '+1 202-555-0115', 'qty' => 1, 'checkin' => 'No' ],
							15 => [ 'name' => 'Alexander Müller',   'email' => 'alexander.muller@example.com',   'phone' => '+1 202-555-0116', 'qty' => 2, 'checkin' => 'Yes' ],
							16 => [ 'name' => 'Harper Lee',         'email' => 'harper.lee@example.com',         'phone' => '+1 202-555-0117', 'qty' => 1, 'checkin' => 'No' ],
							17 => [ 'name' => 'Evelyn Park',        'email' => 'evelyn.park@example.com',        'phone' => '+1 202-555-0118', 'qty' => 2, 'checkin' => 'Yes' ],
							18 => [ 'name' => 'Jack Rivera',        'email' => 'jack.rivera@example.com',        'phone' => '+1 202-555-0119', 'qty' => 1, 'checkin' => 'No' ],
							19 => [ 'name' => 'Grace Okafor',       'email' => 'grace.okafor@example.com',       'phone' => '+1 202-555-0120', 'qty' => 3, 'checkin' => 'Yes' ],
							20 => [ 'name' => 'Sebastian Torres',   'email' => 'sebastian.torres@example.com',   'phone' => '+1 202-555-0121', 'qty' => 1, 'checkin' => 'No' ],
							21 => [ 'name' => 'Chloe Bennett',      'email' => 'chloe.bennett@example.com',      'phone' => '+1 202-555-0122', 'qty' => 2, 'checkin' => 'Yes' ],
						],
						'mep_custom_order' => [
							0  => [ 'name' => 'Ryan Cooper',       'email' => 'ryan.cooper@example.com',       'phone' => '+1 415-555-0201', 'qty' => 1, 'status' => 'publish',    'gateway' => 'offline', 'checkin' => 'Yes' ],
							1  => [ 'name' => 'Natalie Cruz',      'email' => 'natalie.cruz@example.com',      'phone' => '+1 415-555-0202', 'qty' => 2, 'status' => 'publish',    'gateway' => 'paypal',  'checkin' => 'Yes' ],
							2  => [ 'name' => 'Ethan Brooks',      'email' => 'ethan.brooks.ord@example.com',  'phone' => '+1 415-555-0203', 'qty' => 1, 'status' => 'processing', 'gateway' => 'stripe' ],
							3  => [ 'name' => 'Zoe Mitchell',      'email' => 'zoe.mitchell@example.com',      'phone' => '+1 415-555-0204', 'qty' => 3, 'status' => 'publish',    'gateway' => 'offline', 'checkin' => 'No' ],
							4  => [ 'name' => 'Caleb Foster',      'email' => 'caleb.foster@example.com',      'phone' => '+1 415-555-0205', 'qty' => 1, 'status' => 'pending',    'gateway' => 'paypal' ],
							5  => [ 'name' => 'Layla Hughes',      'email' => 'layla.hughes@example.com',      'phone' => '+1 415-555-0206', 'qty' => 2, 'status' => 'publish',    'gateway' => 'stripe',  'checkin' => 'Yes' ],
							6  => [ 'name' => 'Owen Reed',         'email' => 'owen.reed@example.com',         'phone' => '+1 415-555-0207', 'qty' => 1, 'status' => 'on-hold',    'gateway' => 'offline' ],
							7  => [ 'name' => 'Aria Collins',      'email' => 'aria.collins@example.com',      'phone' => '+1 415-555-0208', 'qty' => 2, 'status' => 'publish',    'gateway' => 'free',    'checkin' => 'No' ],
							8  => [ 'name' => 'Mason Price',       'email' => 'mason.price@example.com',       'phone' => '+1 415-555-0209', 'qty' => 1, 'status' => 'cancelled',  'gateway' => 'paypal' ],
							9  => [ 'name' => 'Isla Morgan',       'email' => 'isla.morgan@example.com',       'phone' => '+1 415-555-0210', 'qty' => 4, 'status' => 'publish',    'gateway' => 'offline', 'checkin' => 'Yes' ],
							10 => [ 'name' => 'Leo Sanders',       'email' => 'leo.sanders@example.com',       'phone' => '+1 415-555-0211', 'qty' => 1, 'status' => 'publish',    'gateway' => 'stripe' ],
							11 => [ 'name' => 'Nora Bennett',      'email' => 'nora.bennett.ord@example.com',  'phone' => '+1 415-555-0212', 'qty' => 2, 'status' => 'processing', 'gateway' => 'offline' ],
						],
						'mep_order_cancel_req' => [
							0  => [ 'name' => 'Alice Morgan',    'email' => 'alice.morgan@example.com',    'phone' => '+1 212-555-0301', 'total' => 100, 'ticket' => 'VIP Pass',              'order_status' => 'processing', 'status' => 'pending',  'reason' => 'Travel plans changed and I can no longer attend the event.' ],
							1  => [ 'name' => 'Brian Scott',     'email' => 'brian.scott@example.com',     'phone' => '+1 212-555-0302', 'total' => 200, 'ticket' => 'General Admission x2', 'order_status' => 'processing', 'status' => 'pending',  'reason' => 'Bought the wrong ticket type by mistake and need a refund.' ],
							2  => [ 'name' => 'Clara Diaz',      'email' => 'clara.diaz@example.com',      'phone' => '+1 212-555-0303', 'total' => 150, 'ticket' => 'Early Bird',           'order_status' => 'completed',  'status' => 'Approved', 'reason' => 'Medical appointment conflict on the event date.' ],
							3  => [ 'name' => 'Derek Quinn',     'email' => 'derek.quinn@example.com',     'phone' => '+1 212-555-0304', 'total' => 100, 'ticket' => 'Standard Ticket',      'order_status' => 'processing', 'status' => 'pending',  'reason' => 'Company travel policy changed and the trip was cancelled.' ],
							4  => [ 'name' => 'Elena Vargas',    'email' => 'elena.vargas@example.com',    'phone' => '+1 212-555-0305', 'total' => 300, 'ticket' => 'VIP Pass x3',          'order_status' => 'completed',  'status' => 'Rejected', 'reason' => 'Found a scheduling conflict with another conference.' ],
							5  => [ 'name' => 'Frank Liu',       'email' => 'frank.liu@example.com',       'phone' => '+1 212-555-0306', 'total' => 100, 'ticket' => 'Workshop Seat',        'order_status' => 'processing', 'status' => 'pending',  'reason' => 'Family emergency — please cancel and refund if possible.' ],
							6  => [ 'name' => 'Gina Patel',      'email' => 'gina.patel@example.com',      'phone' => '+1 212-555-0307', 'total' => 80,  'ticket' => 'Student Ticket',       'order_status' => 'on-hold',    'status' => 'pending',  'reason' => 'Duplicate booking created while checking out twice.' ],
							7  => [ 'name' => 'Harry Cole',      'email' => 'harry.cole@example.com',      'phone' => '+1 212-555-0308', 'total' => 120, 'ticket' => 'General Admission',    'order_status' => 'completed',  'status' => 'Approved', 'reason' => 'Visa application delayed; cannot travel in time.' ],
							8  => [ 'name' => 'Ivy Chen',        'email' => 'ivy.chen@example.com',        'phone' => '+1 212-555-0309', 'total' => 100, 'ticket' => 'Standard Ticket',      'order_status' => 'processing', 'status' => 'pending',  'reason' => 'Need to transfer attendance to a colleague instead.' ],
							9  => [ 'name' => 'Jake Romero',     'email' => 'jake.romero@example.com',     'phone' => '+1 212-555-0310', 'total' => 250, 'ticket' => 'VIP Pass x2',          'order_status' => 'completed',  'status' => 'Rejected', 'reason' => 'Event date no longer works with my project deadline.' ],
							10 => [ 'name' => 'Kara Singh',      'email' => 'kara.singh@example.com',      'phone' => '+1 212-555-0311', 'total' => 100, 'ticket' => 'General Admission',    'order_status' => 'processing', 'status' => 'pending',  'reason' => 'Accidental purchase while testing the checkout flow.' ],
							11 => [ 'name' => 'Leo Hartmann',    'email' => 'leo.hartmann@example.com',    'phone' => '+1 212-555-0312', 'total' => 180, 'ticket' => 'Premium Seat',         'order_status' => 'completed',  'status' => 'Approved', 'reason' => 'Hotel booking fell through; unable to attend in person.' ],
						],
						'mep_event_waitlist' => [
							0  => [ 'name' => 'Nina Alvarez',     'email' => 'nina.alvarez@example.com',     'phone' => '+1 312-555-0401', 'qty' => 2 ],
							1  => [ 'name' => 'Omar Hassan',      'email' => 'omar.hassan@example.com',      'phone' => '+1 312-555-0402', 'qty' => 1 ],
							2  => [ 'name' => 'Paula Berg',       'email' => 'paula.berg@example.com',       'phone' => '+1 312-555-0403', 'qty' => 3 ],
							3  => [ 'name' => 'Quinn Adler',      'email' => 'quinn.adler@example.com',      'phone' => '+1 312-555-0404', 'qty' => 1 ],
							4  => [ 'name' => 'Rita Kowalski',    'email' => 'rita.kowalski@example.com',    'phone' => '+1 312-555-0405', 'qty' => 2 ],
						],
						'mep_events_attendees' => self::sample_attendee_people(),
					],
				];
			}
		}
		new mep_dummy_import();
	}