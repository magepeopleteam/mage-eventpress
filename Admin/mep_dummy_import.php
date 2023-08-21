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
				add_action('admin_init', array($this, 'dummy_import'), 10);
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
						'post_content' => "[event-list show='10' style='grid']",
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
				$count_existing_event = wp_count_posts('mep_events')->publish;
				$plugin_active = self::check_plugin('mage-eventpress', 'woocommerce-event-press.php');
				if ($count_existing_event == 0 && $plugin_active == 1 && $dummy_post_inserted != 'yes') {
					$dummy_data = $this->dummy_data();
					foreach ($dummy_data as $type => $dummy) {
						if ($type == 'taxonomy') {
							foreach ($dummy as $taxonomy => $dummy_taxonomy) {
								if (taxonomy_exists($taxonomy)) {
									$check_terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
									if (is_string($check_terms) || sizeof($check_terms) == 0) {
										foreach ($dummy_taxonomy as $taxonomy_data) {
											unset($term);
											$term = wp_insert_term($taxonomy_data['name'], $taxonomy);
											if (array_key_exists('tax_data', $taxonomy_data)) {
												foreach ($taxonomy_data['tax_data'] as $meta_key => $data) {
													update_term_meta($term['term_id'], $meta_key, $data);
												}
											}
										}
									}
								}
							}
						}
						if ($type == 'custom_post') {
							foreach ($dummy as $custom_post => $dummy_post) {
								unset($args);
								$args = array(
									'post_type' => $custom_post,
									'posts_per_page' => -1,
								);
								unset($post);
								$post = new WP_Query($args);
								if ($post->post_count == 0) {
									foreach ($dummy_post as $dummy_data) {
										$title = $dummy_data['name'];
										$content = $dummy_data['content'];
										$post_id = wp_insert_post([
											'post_title' => $title,
											'post_content' => $content,
											'post_status' => 'publish',
											'post_type' => $custom_post,
										]);
										if (array_key_exists('taxonomy_terms', $dummy_data) && count($dummy_data['taxonomy_terms'])) {
											foreach ($dummy_data['taxonomy_terms'] as $taxonomy_term) {
												wp_set_object_terms($post_id, $taxonomy_term['terms'], $taxonomy_term['taxonomy_name'], true);
											}
										}
										if (array_key_exists('post_data', $dummy_data)) {
											foreach ($dummy_data['post_data'] as $meta_key => $data) {
												if ($meta_key == 'feature_image') {
													$url = $data;
													$desc = "The Demo Dummy Image of the event";
													$image = media_sideload_image($url, $post_id, $desc, 'id');
													set_post_thumbnail($post_id, $image);
												}
												else {
													update_post_meta($post_id, $meta_key, $data);
												}
											}
										}
									}
								}
							}
						}
					}
					$this->craete_pages();
					update_option('mep_dummy_already_inserted', 'yes');
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
								'name' => 'Coxesbazar Sea beach Chair Booking',
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
									'feature_image' => 'https://img.freepik.com/free-photo/close-up-recording-video-with-smartphone-during-concert_1153-7310.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
									'mep_location_venue' => 'Coxsbazar',
									'mep_street' => '',
									'mep_city' => '',
									'mep_state' => '',
									'mep_postcode' => '',
									'mep_country' => 'Bangladesh',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_show_advance_col_status' => 'on',
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
											'option_qty_type' => 'dropdown',
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
									'mep_event_cc_email_text' => "
												Usable Dynamic tags:
												Attendee Name:{name}
												Event Name: {event}
												Ticket Type: {ticket_type}
												Event Date: {event_date}
												Start Time: {event_time}
												Full DateTime: {event_datetime}
												",
									//faq settings
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
									'mep_event_day' => array(),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							1 => [
								'name' => 'American Towman ShowPlace',
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
									'feature_image' => 'https://img.freepik.com/free-photo/people-having-fun-wedding-hall_1303-19593.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
									'mep_location_venue' => 'Gaylord Texan Resort',
									'mep_street' => '',
									'mep_city' => '',
									'mep_state' => '',
									'mep_postcode' => '',
									'mep_country' => 'USA',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_show_advance_col_status' => 'on',
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
											'option_qty_type' => 'dropdown',
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
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+60 days', time())),
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
									'mep_event_cc_email_text' => "
												Usable Dynamic tags:
												Attendee Name:{name}
												Event Name: {event}
												Ticket Type: {ticket_type}
												Event Date: {event_date}
												Start Time: {event_time}
												Full DateTime: {event_datetime}
												",
									//faq settings
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
									'mep_event_day' => array(),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							2 => [
								'name' => 'Sistahs in Business Expo 2021',
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
									'feature_image' => 'https://img.freepik.com/free-photo/corporate-businessman-giving-presentation-large-audience_53876-101865.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
									'mep_location_venue' => 'Hudson Yards',
									'mep_street' => '',
									'mep_city' => 'New York',
									'mep_state' => 'NY',
									'mep_postcode' => '',
									'mep_country' => 'USA',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_show_advance_col_status' => 'on',
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
											'option_qty_type' => 'dropdown',
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
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+40 days', time())),
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
									'mep_event_cc_email_text' => "
												Usable Dynamic tags:
												Attendee Name:{name}
												Event Name: {event}
												Ticket Type: {ticket_type}
												Event Date: {event_date}
												Start Time: {event_time}
												Full DateTime: {event_datetime}
												",
									//faq settings
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
									'mep_event_day' => array(),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							3 => [
								'name' => 'Tech Career Fair: Exclusive Tech Hiring Event',
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
									'feature_image' => 'https://img.freepik.com/free-photo/female-business-executive-giving-speech_107420-63791.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
									'mep_location_venue' => 'Metropolitan Pavilion',
									'mep_street' => '',
									'mep_city' => 'New York',
									'mep_state' => 'NY',
									'mep_postcode' => '',
									'mep_country' => 'USA',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_show_advance_col_status' => 'on',
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
											'option_qty_type' => 'dropdown',
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
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+50 days', time())),
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
									'mep_event_cc_email_text' => "
												Usable Dynamic tags:
												Attendee Name:{name}
												Event Name: {event}
												Ticket Type: {ticket_type}
												Event Date: {event_date}
												Start Time: {event_time}
												Full DateTime: {event_datetime}
												",
									//faq settings
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
									'mep_event_day' => array(),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							4 => [
								'name' => 'Free Networking Event In NYC',
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
									'feature_image' => 'https://img.freepik.com/free-photo/group-young-people-are-looking-map-where-they-are-while-walking-autumn-forest_613910-15159.jpg',
									'mep_event_type' => 'online',
									'mp_event_virtual_type_des' => 'Test event virtual type',
									'mep_org_address' => '',
									'mep_location_venue' => '',
									'mep_street' => '',
									'mep_city' => '',
									'mep_state' => '',
									'mep_postcode' => '',
									'mep_country' => '',
									'mep_sgm' => '',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_show_advance_col_status' => 'on',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "Early Bird ticket",
											'option_details_t' => "",
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
											'option_details_t' => "",
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
											'option_details_t' => "",
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
											'option_qty_type' => 'dropdown',
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
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+60 days', time())),
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
									'mep_event_cc_email_text' => "
												Usable Dynamic tags:
												Attendee Name:{name}
												Event Name: {event}
												Ticket Type: {ticket_type}
												Event Date: {event_date}
												Start Time: {event_time}
												Full DateTime: {event_datetime}
												",
									//faq settings
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
									'mep_event_day' => array(),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							5 => [
								'name' => 'Austin Tech Career Fair',
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
									'feature_image' => 'https://img.freepik.com/free-photo/yes_53876-47102.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
									'mep_location_venue' => 'Gaylord Resort',
									'mep_street' => '',
									'mep_city' => 'Washington DC',
									'mep_state' => 'NY',
									'mep_postcode' => '32165',
									'mep_country' => 'USA',
									'mep_sgm' => '',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_show_advance_col_status' => 'on',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "VIP",
											'option_details_t' => "",
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
											'option_details_t' => "",
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
											'option_qty_type' => 'dropdown',
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
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+47 days', time())),
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
									'mep_event_cc_email_text' => "
												Usable Dynamic tags:
												Attendee Name:{name}
												Event Name: {event}
												Ticket Type: {ticket_type}
												Event Date: {event_date}
												Start Time: {event_time}
												Full DateTime: {event_datetime}
												",
									//faq settings
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
									'mep_event_day' => array(),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							6 => [
								'name' => 'Ohio and Kentucky Cannabis & Hemp Expo',
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
									'feature_image' => 'https://img.freepik.com/free-photo/speaker-business-meeting-conference-hall_155003-12698.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
									'mep_location_venue' => 'Kolkata wordPress Community',
									'mep_street' => 'Park Street',
									'mep_city' => 'Kolkata',
									'mep_state' => 'West Bengal',
									'mep_postcode' => '1209',
									'mep_country' => 'India',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_show_advance_col_status' => 'on',
									'mep_event_ticket_type' => array(
										0 => array(
											'option_name_t' => "General",
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
											'option_name_t' => "Sponsored",
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
											'option_name_t' => "Free",
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
											'option_qty_type' => 'dropdown',
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
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+25 days', time())),
									'event_start_time' => $start_time = "09:00",
									'event_end_date' => $end_date = date('Y-m-d', strtotime('+40 days', strtotime($start_date))),
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
									'mep_event_cc_email_text' => "
												Usable Dynamic tags:
												Attendee Name:{name}
												Event Name: {event}
												Ticket Type: {ticket_type}
												Event Date: {event_date}
												Start Time: {event_time}
												Full DateTime: {event_datetime}
												",
									//faq settings
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
									'mep_event_day' => array(),
									'mep_list_thumbnail' => '',
									'mep_total_seat_left' => '0',
								],
							],
							7 => [
								'name' => 'Greenwich Economic Forum',
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
									'feature_image' => 'https://img.freepik.com/free-photo/female-african-american-speaker-giving-presentation-hall-university-workshop_155003-3579.jpg',
									'mep_event_type' => 'off',
									'mp_event_virtual_type_des' => '',
									'mep_org_address' => '0',
									'mep_location_venue' => 'The Millennium Gallery Sheffield',
									'mep_street' => 'Arundel Gate',
									'mep_city' => 'Sheffield',
									'mep_state' => 'S1 2PP',
									'mep_postcode' => '',
									'mep_country' => '',
									'mep_sgm' => '1',
									//Ticket Type & prices
									'mep_reg_status' => 'on',
									'mep_show_advance_col_status' => 'on',
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
											'option_qty_type' => 'dropdown',
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
									'event_start_date' => $start_date = date('Y-m-d', strtotime('+40 days', time())),
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
									'mep_event_cc_email_text' => "
												Usable Dynamic tags:
												Attendee Name:{name}
												Event Name: {event}
												Ticket Type: {ticket_type}
												Event Date: {event_date}
												Start Time: {event_time}
												Full DateTime: {event_datetime}
												",
									//faq settings
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
									'mep_event_day' => array(),
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