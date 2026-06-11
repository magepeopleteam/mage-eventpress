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

							$fill.css('width', '50%');
							$status.text('<?php echo esc_js(__("Importing dummy data. This may take a moment...", "mage-eventpress")); ?>').removeClass('mpwem-success mpwem-error');

							$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
									action: 'mep_import_dummy_data',
									nonce: '<?php echo wp_create_nonce("mep_import_dummy"); ?>'
								},
								success: function(response) {
									if (response.success) {
										$fill.css('width', '100%');
										$status.text('<?php echo esc_js(__("Import complete!", "mage-eventpress")); ?>').addClass('mpwem-success');
										$popup.addClass('mpwem-state-success');
										$popup.find('.mpwem-woo-icon').html(
											'<svg width="40" height="40" viewBox="0 0 24 24" fill="none">' +
											'<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>' +
											'<path d="M8 12l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
											'</svg>'
										);
										$popup.find('.mpwem-woo-title').text('<?php echo esc_js(__("Success", "mage-eventpress")); ?>');
										$popup.find('.mpwem-woo-desc').text('<?php echo esc_js(__("Dummy data imported successfully. Reloading page...", "mage-eventpress")); ?>');
										
										setTimeout(function() {
											window.location.reload();
										}, 1500);
									} else {
										showError(response.data && response.data.message ? response.data.message : '<?php echo esc_js(__("Failed to import.", "mage-eventpress")); ?>');
									}
								},
								error: function() {
									showError('<?php echo esc_js(__("Failed to import. Please try again.", "mage-eventpress")); ?>');
								}
							});
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
				$this->dummy_import();
				wp_send_json_success();
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
			public function dummy_import() {

				$dummy_post_inserted = get_option('mep_dummy_already_inserted');
				if ($dummy_post_inserted) {
					return;
				}
				$count_existing_event = wp_count_posts('mep_events')->publish;
				$plugin_active = self::check_plugin('mage-eventpress', 'woocommerce-event-press.php');
				$gallery_images = [];
				$related_events = [];
				if ($count_existing_event == 0 && $plugin_active == 1 && $dummy_post_inserted != 'yes') {
					$dummy_data = $this->dummy_data();
					foreach ($dummy_data as $type => $dummy) {
						if ($type == 'taxonomy') {
							foreach ($dummy as $taxonomy => $dummy_taxonomy) {
								if (taxonomy_exists($taxonomy)) {
									$check_terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
									if (is_string($check_terms) || (is_array($check_terms) && sizeof($check_terms) == 0)) {
										foreach ($dummy_taxonomy as $taxonomy_data) {
											$term = wp_insert_term($taxonomy_data['name'], $taxonomy);
											if (is_array($taxonomy_data) && array_key_exists( 'tax_data', $taxonomy_data )) {
												foreach ($taxonomy_data['tax_data'] as $meta_key => $data) {
													update_term_meta($term['term_id'], $meta_key, $data);
												}
											}
										}
									}
								}
							}
						}
					}
					if ($type == 'custom_post') {
						foreach ($dummy as $custom_post => $dummy_post) {
							$args = array(
								'post_type' => $custom_post,
								'posts_per_page' => -1,
							);
							$post = new WP_Query($args);
							if ($post->post_count == 0) {
								foreach ($dummy_post as $dummy_data) {
									$post_id = wp_insert_post([
										'post_title' => $dummy_data['name'],
										'post_content' => $dummy_data['content'],
										'post_status' => 'publish',
										'post_type' => $custom_post,
									]);
									$related_events[] = $post_id;
									if (is_array($dummy_data) && array_key_exists( 'taxonomy_terms', $dummy_data )) {
										foreach ($dummy_data['taxonomy_terms'] as $taxonomy_term) {
											wp_set_object_terms($post_id, $taxonomy_term['terms'], $taxonomy_term['taxonomy_name'], true);
										}
									}
									if (is_array($dummy_data) && array_key_exists( 'post_data', $dummy_data )) {
										foreach ($dummy_data['post_data'] as $meta_key => $data) {
											if ($meta_key == 'feature_image') {
												$url = $data;
												$image = media_sideload_image($url, $post_id, null, 'id');
												$gallery_images[] = $image;
												set_post_thumbnail($post_id, $image);
											} else {
												update_post_meta($post_id, $meta_key, $data);

											}
											update_option('mep_dummy_post_data_inserted', 'yes');

										}
									}
								}
							}
						}
					}
					$this->craete_pages();
					$this->add_gallery_images($custom_post,$gallery_images);
					$this->add_related_events($custom_post,$related_events);
					update_option('mep_dummy_already_inserted', 'yes');
				}
			}

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
								'mep_location_venue' => 'Hotel Ramada, Coxsbazar',
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
								'mep_location_venue' => 'Gaylord Texan Resort & Convention Center',
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
								'mep_location_venue' => 'Hudson Yards',
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
								'mep_location_venue' => 'Metropolitan Pavilion',
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
								'mep_location_venue' => 'Gaylord Texan Resort & Convention Center',
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
								'mep_location_venue' => 'Radisson Collection Hotel',
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
								'mep_location_venue' => 'The Millennium Gallery Sheffield',
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
					],
				];
			}
		}
		new mep_dummy_import();
	}