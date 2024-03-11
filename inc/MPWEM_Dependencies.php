<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Dependencies')) {
		class MPWEM_Dependencies {
			public function __construct() {
				add_action('init', array($this, 'language_load'));
				$this->load_global_file();
				$this->load_file();
				add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'), 90);
				add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue'), 90);
				add_action('admin_head', array($this, 'add_admin_head'), 5);
				add_action('wp_head', array($this, 'add_frontend_head'), 5);
			}
			public function language_load(): void {
				$plugin_dir = basename(dirname(__DIR__)) . "/languages/";
				load_plugin_textdomain('mage-eventpress', false, $plugin_dir);
			}
			public function load_global_file() {
				require_once MPWEM_PLUGIN_DIR . '/inc/global/MP_Global_Function.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/global/MP_Global_Style.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/global/MP_Custom_Layout.php';
				//require_once MPWEM_PLUGIN_DIR . '/inc/global/MP_Custom_Slider.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/global/MP_Select_Icon_image.php';
			}
			private function load_file(): void {
				require_once MPWEM_PLUGIN_DIR . '/Admin/MPWEM_Admin.php';
				require_once(dirname(__DIR__) . '/lib/classes/class-mep.php');
				require_once(dirname(__DIR__) . "/inc/mep_functions.php");
				require_once(dirname(__DIR__) . "/inc/mep_tax.php");
				require_once(dirname(__DIR__) . "/inc/mep_event_meta.php");
				require_once(dirname(__DIR__) . "/inc/mep_event_fw_meta.php");
				require_once(dirname(__DIR__) . "/inc/mep_extra_price.php");
				require_once(dirname(__DIR__) . "/inc/mep_shortcode.php");
				require_once(dirname(__DIR__) . "/inc/mep_user_custom_style.php");
				require_once(dirname(__DIR__) . "/inc/mep_tax_meta.php");
				require_once(dirname(__DIR__) . "/inc/mep_query.php");
				//require_once MPWEM_PLUGIN_DIR . '/inc/MPTBM_Function.php';
				//require_once MPTBM_PLUGIN_DIR . '/inc/MPTBM_Query.php';
				//require_once MPTBM_PLUGIN_DIR . '/inc/MPTBM_Layout.php';
				//require_once MPTBM_PLUGIN_DIR . '/Admin/MPTBM_Admin.php';
				//require_once MPTBM_PLUGIN_DIR . '/Frontend/MPTBM_Frontend.php';
			}
			public function global_enqueue() {
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_script('jquery-ui-accordion');
				wp_localize_script('jquery', 'mep_ajax', array('mep_ajaxurl' => admin_url('admin-ajax.php')));
				wp_enqueue_style('mp_jquery_ui', MPWEM_PLUGIN_URL . '/assets/helper/jquery-ui.min.css', array(), '1.13.2');
				$fontAwesome = MP_Global_Function::get_settings('general_setting_sec', 'mep_load_fontawesome_from_theme', 'no');
				if ($fontAwesome == 'no') {
					wp_enqueue_style('mp_font_awesome-430', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css', array(), '4.3.0');					
					wp_enqueue_style('mp_font_awesome', '//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css', array(), '5.15.4');
				}
				$flatIcon = MP_Global_Function::get_settings('general_setting_sec', 'mep_load_flaticon_from_theme', 'no');
				if ($flatIcon == 'no') {
					wp_enqueue_style('mp_flat_icon', MPWEM_PLUGIN_URL . '/assets/helper/flaticon/flaticon.css');
				}
				wp_enqueue_style('mp_select_2', MPWEM_PLUGIN_URL . '/assets/helper/select_2/select2.min.css', array(), '4.0.13');
				wp_enqueue_script('mp_select_2', MPWEM_PLUGIN_URL . '/assets/helper/select_2/select2.min.js', array(), '4.0.13');
				$owlCarousel = MP_Global_Function::get_settings('carousel_setting_sec', 'mep_load_carousal_from_theme', 'no');
				if ($owlCarousel == 'no') {
					wp_enqueue_style('mp_owl_carousel', MPWEM_PLUGIN_URL . '/assets/helper/owl_carousel/owl.carousel.min.css', array(), '2.3.4');
					wp_enqueue_script('mp_owl_carousel', MPWEM_PLUGIN_URL . '/assets/helper/owl_carousel/owl.carousel.min.js', array(), '2.3.4');
				}
				if (is_admin()) {
					wp_enqueue_style('mp_plugin_global', MPWEM_PLUGIN_URL . '/assets/helper/mp_style/mp_style.css', array(), time());
					wp_enqueue_script('mp_plugin_global', MPWEM_PLUGIN_URL . '/assets/helper/mp_style/mp_script.js', array('jquery'), time(), true);
				}
				do_action('add_mpwem_common_script');
			}
			public function admin_enqueue($hook) {
				global $post;
				$this->global_enqueue();
				wp_enqueue_editor();
				//admin script
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
				wp_enqueue_style('wp-codemirror');
				wp_enqueue_script('wp-codemirror');
				//wp_enqueue_script('jquery-ui-accordion');
				//********//
				$user_api = mep_get_option('google-map-api', 'general_setting_sec', '');
				// Load Only when the New Event Add Page Open.
				if ($hook == 'post-new.php' || $hook == 'post.php') {
					if ('mep_events' === $post->post_type) {
						wp_enqueue_script('gmap-scripts', MPWEM_PLUGIN_URL . '/assets/admin/mkb-admin.js', array('jquery', 'jquery-ui-core'), time(), true);
					}
				}
				if ($user_api) {
					wp_enqueue_script('gmap-libs', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($user_api) . '&libraries=places&callback=initMap', array('jquery', 'gmap-scripts'), 1, true);
				}
				wp_localize_script('mep_ajax', 'mep_ajax_var', array('url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('mep-ajax-nonce')));
				//loading pick plugin
				wp_enqueue_style('mage-options-framework', MPWEM_PLUGIN_URL . '/assets/helper/pick_plugin/mage-options-framework.css');
				wp_enqueue_script('magepeople-options-framework', MPWEM_PLUGIN_URL . '/assets/helper/pick_plugin/mage-options-framework.js', array('jquery'));
				wp_localize_script('PickpluginsOptionsFramework', 'PickpluginsOptionsFramework_ajax', array('PickpluginsOptionsFramework_ajaxurl' => admin_url('admin-ajax.php')));
				wp_enqueue_script('form-field-dependency', MPWEM_PLUGIN_URL . '/assets/helper/form-field-dependency.js', array('jquery'), null, false);
				//loading modal
				wp_enqueue_style('jquery.modal.min', MPWEM_PLUGIN_URL . '/assets/helper/jquery_modal/jquery.modal.min.css', array(), 1.0);
				wp_enqueue_script('jquery.modal.min', MPWEM_PLUGIN_URL . '/assets/helper/jquery_modal/jquery.modal.min.js', array('jquery'), 1.0, true);
				//wp_enqueue_script('mp_admin_settings', MPWEM_PLUGIN_URL . '/assets/admin/mp_admin_settings.js', array('jquery'), time(), true);
				wp_enqueue_style('mp_admin_settings', MPWEM_PLUGIN_URL . '/assets/admin/mp_admin_settings.css', array(), time());
				// custom
				wp_enqueue_script('mpwem_admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_admin.js', array('jquery'), time(), true);
				wp_enqueue_style('mpwem_admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_admin.css', array(), time());
				do_action('add_mpwem_admin_script');
			}
			public function frontend_enqueue() {
				$this->global_enqueue();
				//wp_enqueue_script('wc-checkout');
				//wp_enqueue_script('mptbm_registration', MPWEM_PLUGIN_URL . '/assets/frontend/mptbm_registration.js', array('jquery'), time(), true);
				//wp_enqueue_style('mptbm_registration', MPWEM_PLUGIN_URL . '/assets/frontend/mptbm_registration.css', array(), time());
				//timeline
				wp_enqueue_style('mep-event-timeline-min-style', MPWEM_PLUGIN_URL . '/assets/helper/timeline/timeline.min.css', array(''));
				wp_enqueue_script('mep-timeline-min', MPWEM_PLUGIN_URL . '/assets/helper/timeline/timeline.min.js', array('jquery'), 1, true);
				//calender
				wp_enqueue_style('mep-calendar-min-style', MPWEM_PLUGIN_URL . '/assets/helper/calender/calendar.min.css', array());
				wp_enqueue_script('mep-calendar-scripts', MPWEM_PLUGIN_URL . '/assets/helper/calender/calendar.min.js', array('jquery', 'mep-moment-js'), 1, true);
				//
				wp_enqueue_script('mep-mixitup-min-js', 'https://cdnjs.cloudflare.com/ajax/libs/mixitup/3.3.0/mixitup.min.js', array(), '3.3.0', true);
				wp_enqueue_script('mep-countdown-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.countdown/2.2.0/jquery.countdown.min.js', array('jquery'), 1, true);
				wp_enqueue_script('mep-moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js', array(), 1, true);
				//custom
				wp_enqueue_style('filter_pagination', MPWEM_PLUGIN_URL . '/assets/frontend/filter_pagination.css', array(), time());
				wp_enqueue_script('filter_pagination', MPWEM_PLUGIN_URL . '/assets/frontend/filter_pagination.js', array(), time(), true);
				wp_enqueue_style('mpwem_style', MPWEM_PLUGIN_URL . '/assets/frontend/mpwem_style.css', array(), time());
				wp_enqueue_script('mpwem_script', MPWEM_PLUGIN_URL . '/assets/frontend/mpwem_script.js', array('jquery'), time(), true);
				do_action('add_mpwem_frontend_script');
			}
			public function add_admin_head() {
				$this->js_constant();
			}
			public function add_frontend_head() {
				$this->js_constant();
				$this->custom_css();
				$this->event_rich_text_data();
			}
			public function js_constant() {
				?>
				<script type="text/javascript">
					let mp_ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
					let mp_currency_symbol = "<?php echo get_woocommerce_currency_symbol(); ?>";
					let mp_currency_position = "<?php echo get_option('woocommerce_currency_pos'); ?>";
					let mp_currency_decimal = "<?php echo wc_get_price_decimal_separator(); ?>";
					let mp_currency_thousands_separator = "<?php echo wc_get_price_thousand_separator(); ?>";
					let mp_num_of_decimal = "<?php echo get_option('woocommerce_price_num_decimals', 2); ?>";
					let mp_empty_image_url = "<?php echo esc_attr(MPWEM_PLUGIN_URL . '/assets/helper/images/no_image.png'); ?>";
					let mp_date_format = "D d M , yy";
				</script>
				<?php
			}
			public function custom_css() {
				$custom_css = MP_Global_Function::get_settings('mep_settings_custom_css', 'mep_custom_css');
				$not_available_hide = MP_Global_Function::get_settings('general_setting_sec', 'mep_hide_not_available_event_from_list_page', 'no');
				ob_start();
				?>
				<style>
					<?php echo $custom_css; ?>
					<?php  if($not_available_hide == 'yes'){ ?>
					.event-no-availabe-seat { display: none !important; }
					<?php } 	?>
				</style>
				<?php
				echo ob_get_clean();
			}
			//This the function which will create the Rich Text Schema For each event into the <head></head> section.
			public function event_rich_text_data() {
				global $post;
				if (is_single()) {
					$event_id = $post->ID;
					if ($event_id && get_post_type($event_id) == 'mep_events') {
						$event_name = get_the_title($event_id);
						$event_start_date = get_post_meta($post->ID, 'event_start_datetime', true) ? wp_date('Y-m-d H:i:s T', strtotime(get_post_meta($post->ID, 'event_start_datetime', true))) : '';
						$event_end_date = get_post_meta($post->ID, 'event_end_datetime', true) ? get_post_meta($post->ID, 'event_end_datetime', true) : '';
						$event_rt_status = get_post_meta($post->ID, 'mep_rt_event_status', true) ? get_post_meta($post->ID, 'mep_rt_event_status', true) : 'EventRescheduled';
						$event_rt_atdnce_mode = get_post_meta($post->ID, 'mep_rt_event_attandence_mode', true) ? get_post_meta($post->ID, 'mep_rt_event_attandence_mode', true) : 'OfflineEventAttendanceMode';
						$event_rt_prv_date = get_post_meta($post->ID, 'mep_rt_event_prvdate', true) ? get_post_meta($post->ID, 'mep_rt_event_prvdate', true) : $event_start_date;
						$terms = get_the_terms($event_id, 'mep_org');
						$org_name = is_array($terms) && sizeof($terms) > 0 ? $terms[0]->name : 'No Performer';
						$rt_status = get_post_meta($event_id, 'mep_rich_text_status', true) ? get_post_meta($event_id, 'mep_rich_text_status', true) : 'enable';
						if ($rt_status == 'enable') {
							ob_start();
							?>
							<script type="application/ld+json">
								{
								"@context"  : "https://schema.org",
								"@type"     : "Event",
								"name"      : "<?php echo esc_attr($event_name); ?>",
                            "startDate" : "<?php echo esc_attr($event_start_date); ?>",
                            "endDate"   : "<?php echo esc_attr($event_end_date); ?>",
                            "offers": {
                                "@type"         : "Offer",
                                "url"           : "<?php echo get_the_permalink($event_id); ?>",
                                "price"         : "<?php echo strip_tags(mep_event_list_number_price($event_id)); ?>",
                                "priceCurrency" : "<?php echo get_woocommerce_currency(); ?>",
                                "availability"  : "https://schema.org/InStock",
                                "validFrom"     : "<?php echo esc_attr($event_end_date); ?>"
                            },
                            "organizer": {
                                "@type" : "Organization",
                                "name"  : "<?php echo esc_attr($org_name); ?>",
                                "url"   : "<?php echo get_the_permalink($event_id); ?>"
                            },
                            "eventStatus"           : "https://schema.org/<?php echo esc_attr($event_rt_status); ?>",
                            "eventAttendanceMode"   : "https://schema.org/<?php echo esc_attr($event_rt_atdnce_mode); ?>",
                            "previousStartDate"     : "<?php echo esc_attr($event_rt_prv_date); ?>",
                            "location"  : {
                                "@type"         : "Place",
                                "name"          : "<?php echo mep_get_event_location($event_id); ?>",
                                "address"       : {
                                "@type"         : "PostalAddress",
                                "streetAddress" : "<?php echo mep_get_event_location_street($event_id); ?>",
                                "addressLocality": "<?php echo mep_get_event_location_city($event_id); ?>",
                                "postalCode"    : "<?php echo mep_get_event_location_postcode($event_id) ?>",
                                "addressRegion" : "<?php echo mep_get_event_location_state($event_id) ?>",
                                "addressCountry": "<?php echo mep_get_event_location_country($event_id) ?>"
                                }
                            },
                            "image": [
                                "<?php echo get_the_post_thumbnail_url($event_id, 'full'); ?>"
                            ],
                            "description": "<?php echo strip_tags(mep_string_sanitize(get_the_excerpt($event_id))); ?>",
                            "performer": {
                                "@type" : "PerformingGroup",
                                "name"  : "<?php echo esc_attr($org_name); ?>"
                            }
                            }
							</script>
							<?php
							echo ob_get_clean();
						}
					}
				}
			}
		}
		new MPWEM_Dependencies();
	}
	