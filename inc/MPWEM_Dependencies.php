<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Dependencies' ) ) {
		class MPWEM_Dependencies {
			public function __construct() {
				add_action( 'init', array( $this, 'language_load' ) );
				$this->load_file();
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 90 );
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ), 90 );
				add_action( 'admin_head', array( $this, 'add_admin_head' ), 5 );
				add_action( 'wp_head', array( $this, 'add_frontend_head' ), 5 );
			}
			public function language_load(): void {
				$plugin_dir = basename( dirname( __DIR__ ) ) . "/languages/";
				load_plugin_textdomain( 'mage-eventpress', false, $plugin_dir );
			}
			private function load_file(): void {
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Global_Function.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Global_Style.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Custom_Layout.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Custom_Slider.php';
				require_once MPWEM_PLUGIN_DIR . '/admin/MPWEM_Select_Icon_image.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Layout.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Functions.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Frontend.php';
				require_once MPWEM_PLUGIN_DIR . '/admin/MPWEM_Admin.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Hooks.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Shortcodes.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Event_List.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Woocommerce.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_My_Account_Dashboard.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/mep-google-maps-fix.php';
				require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Query.php';
				require_once( dirname( __DIR__ ) . "/inc/mep_functions.php" );
				require_once( dirname( __DIR__ ) . "/inc/mep_tax.php" );
				require_once( dirname( __DIR__ ) . "/inc/mep_tax_meta.php" );
				require_once( dirname( __DIR__ ) . "/inc/mep_low_stock_display.php" );
				require_once( dirname( __DIR__ ) . "/inc/mep-expired-event-noindex.php" );
			}
			public function global_enqueue() {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_script( 'jquery-ui-accordion' );
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_script( 'select2' );
				wp_enqueue_style( 'select2' );
				wp_localize_script( 'jquery', 'mep_ajax', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'mep-ajax-nonce' ) ) );
				wp_enqueue_style( 'mp_jquery_ui', MPWEM_PLUGIN_URL . '/assets/helper/jquery-ui.min.css', array(), '1.13.2' );
//				wp_enqueue_script(
//					'fecha',
//					'https://cdn.jsdelivr.net/npm/fecha@4.2.3/dist/fecha.min.js',
//					array('jquery','jquery-ui-datepicker'),
//					'4.2.3',
//					true
//				);
//
//				wp_add_inline_script(
//					'fecha',
//					'window.Fecha = fecha;'
//				);
				$fontAwesome = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_load_fontawesome_from_theme', 'no' );
				if ( $fontAwesome == 'no' ) {
					wp_enqueue_style( 'mp_font_awesome-430', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css', array(), '4.3.0' );
					wp_enqueue_style( 'mp_font_awesome-660', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css', array(), '6.6.0' );
					wp_enqueue_style( 'mp_font_awesome', '//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css', array(), '5.15.4' );
				}
				$flatIcon = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_load_flaticon_from_theme', 'no' );
				if ( $flatIcon == 'no' ) {
					wp_enqueue_style( 'mp_flat_icon', MPWEM_PLUGIN_URL . '/assets/helper/flaticon/flaticon.css' );
				}
				$owlCarousel = MPWEM_Global_Function::get_settings( 'carousel_setting_sec', 'mep_load_carousal_from_theme', 'no' );
				if ( $owlCarousel == 'no' ) {
					wp_enqueue_style( 'mp_owl_carousel', MPWEM_PLUGIN_URL . '/assets/helper/owl_carousel/owl.carousel.min.css', array(), '2.3.4' );
					wp_enqueue_script( 'mp_owl_carousel', MPWEM_PLUGIN_URL . '/assets/helper/owl_carousel/owl.carousel.min.js', array( 'jquery' ), '2.3.4', true );
				}
				//slick
				wp_enqueue_style( 'mpwem_slick', MPWEM_PLUGIN_URL . '/assets/helper/slick/slick.css', array(), '1.8.1', 'all' );
				wp_enqueue_script( 'mpwem_slick', MPWEM_PLUGIN_URL . '/assets/helper/slick/slick.min.js', array( 'jquery' ), '1.8.1', false );
				wp_enqueue_style( 'mpwem_slick_theme', MPWEM_PLUGIN_URL . '/assets/helper/slick/slick_theme.css', array( 'mpwem_slick' ), '1.8.1' );
				wp_enqueue_style( 'mpwem_global', MPWEM_PLUGIN_URL . '/assets/helper/mp_style/mpwem_global.css', array(), time() );
				wp_enqueue_script( 'mpwem_global', MPWEM_PLUGIN_URL . '/assets/helper/mp_style/mpwem_global.js', array( 'jquery' ), time(), true );
				do_action( 'add_mpwem_common_script' );
				wp_enqueue_style( 'mage-icons', MPWEM_PLUGIN_URL . '/assets/mage-icon/css/mage-icon.css', array(), time() );
			}
			public function admin_enqueue( $hook ) {
				wp_enqueue_editor();
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-codemirror' );
				wp_enqueue_script( 'wp-codemirror' );
				wp_enqueue_script( 'editor' );
				wp_enqueue_script( 'quicktags' );
				wp_enqueue_script( 'media-upload' );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'editor-buttons' );
				//********//
				$this->global_enqueue();
				//********//
				$user_api = mep_get_option( 'google-map-api', 'general_setting_sec', '' );
				if ( $user_api ) {
					wp_enqueue_script( 'gmap-libs', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $user_api ) . '&libraries=places&callback=initMap', array( 'jquery', 'gmap-scripts' ), 1, true );
				}

				//loading pick plugin
				wp_enqueue_style( 'mage-options-framework', MPWEM_PLUGIN_URL . '/assets/helper/pick_plugin/mage-options-framework.css' );
				wp_enqueue_script( 'magepeople-options-framework', MPWEM_PLUGIN_URL . '/assets/helper/pick_plugin/mage-options-framework.js', array( 'jquery', 'wp-color-picker' ) );
				wp_localize_script( 'PickpluginsOptionsFramework', 'PickpluginsOptionsFramework_ajax', array( 'PickpluginsOptionsFramework_ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				wp_enqueue_script( 'form-field-dependency', MPWEM_PLUGIN_URL . '/assets/helper/form-field-dependency.js', array( 'jquery' ), null, false );
				//******************/
				//loading modal
				wp_enqueue_style( 'jquery.modal.min', MPWEM_PLUGIN_URL . '/assets/helper/jquery_modal/jquery.modal.min.css', array(), 1.0 );
				wp_enqueue_script( 'jquery.modal.min', MPWEM_PLUGIN_URL . '/assets/helper/jquery_modal/jquery.modal.min.js', array( 'jquery' ), 1.0, true );
				//******************/
				if ( $hook == 'mep_events_page_mep_event_analytics_page' ) {
					// Enqueue Chart.js
					wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true );
					wp_enqueue_script( 'chartjs-date-adapter', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@2.0.0/dist/chartjs-adapter-date-fns.bundle.min.js', array( 'chartjs' ), '2.0.0', true );
					// Enqueue custom analytics scripts
					wp_enqueue_script( 'mep-analytics', MPWEM_PLUGIN_URL . '/assets/admin/mep_analytics.js', array( 'jquery', 'chartjs' ), time(), true );
					wp_enqueue_style( 'mep-analytics', MPWEM_PLUGIN_URL . '/assets/admin/mep_analytics.css', array(), time() );
					// Localize script with AJAX URL, nonce, and currency symbol
					wp_localize_script( 'mep-analytics', 'mep_analytics_data', array(
						'ajax_url'        => admin_url( 'admin-ajax.php' ),
						'nonce'           => wp_create_nonce( 'mep_analytics_nonce' ),
						'currency_symbol' => get_woocommerce_currency_symbol(),
					) );
				}
				//******************/
				wp_localize_script( 'mkb-admin', 'mep_ajax_var', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'mep-ajax-nonce' ) ) );
				// Only load event lists scripts on relevant pages
				if ( $hook == 'mep_events_page_mep_event_lists' || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'mep_events' && ( $hook == 'edit.php' || isset( $_GET['page'] ) && $_GET['page'] == 'mep_event_lists' ) ) ) {
					wp_enqueue_script( 'mpwem_event_lists', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_event_lists.js', array( 'jquery' ), time(), true );
					wp_localize_script( 'mpwem_event_lists', 'mep_ajax', array(
						'url'   => admin_url( 'admin-ajax.php' ),
						'nonce' => wp_create_nonce( 'mep_nonce' )
					) );
					wp_enqueue_style( 'mpwem_event_lists', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_event_lists.css', array(), time() );
				}
				/******************************/
				// custom
				wp_enqueue_style( 'mpwem_admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_admin.css', array(), time() );
				wp_enqueue_script( 'mpwem_admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_admin.js', array( 'jquery' ), time(), true );
				wp_localize_script( 'mpwem_admin', 'mpwem_admin_var', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'mpwem_admin_nonce' ) ) );
				/******************************/
				
				wp_localize_script('mpwem_admin', 'mepAjax', ['ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'mep_admin_nonce' ),]);



				do_action( 'add_mpwem_admin_script' );
			}
			public function frontend_enqueue() {
				$this->global_enqueue();
				$is_divi = function_exists('et_divi_builder_init') || defined('ET_BUILDER_PLUGIN_ACTIVE');
				wp_enqueue_script( 'mep-mixitup-min-js', 'https://cdnjs.cloudflare.com/ajax/libs/mixitup/3.3.0/mixitup.min.js', array(), '3.3.0', true );
				wp_enqueue_script( 'mep-countdown-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.countdown/2.2.0/jquery.countdown.min.js', array( 'jquery' ), 1, true );
				wp_enqueue_script( 'mep-moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js', array(), 1, true );
				//timeline
				wp_enqueue_style( 'mep-timeline-min', MPWEM_PLUGIN_URL . '/assets/helper/timeline/timeline.min.css', array(), '1.0.0', 'all' );
				wp_enqueue_script( 'mep-timeline-min', MPWEM_PLUGIN_URL . '/assets/helper/timeline/timeline.min.js', array( 'jquery' ), 1, true );
				//calender
				wp_enqueue_style( 'mep-calendar-min-style', MPWEM_PLUGIN_URL . '/assets/helper/calender/calendar.min.css', array() );
				wp_enqueue_script( 'mep-calendar-scripts', MPWEM_PLUGIN_URL . '/assets/helper/calender/calendar.min.js', array( 'jquery', 'mep-moment-js' ), 1, true );
				//custom
				wp_enqueue_script( 'filter_pagination', MPWEM_PLUGIN_URL . '/assets/frontend/filter_pagination.js', array(), time(), true );

				if ($is_divi) {
					wp_enqueue_style( 'divi_style', MPWEM_PLUGIN_URL . '/assets/frontend/divi_style.css', array(), time() );
				} else {
					wp_enqueue_style( 'mpwem_style', MPWEM_PLUGIN_URL . '/assets/frontend/mpwem_style.css', array(), time() );
				}
				wp_enqueue_script( 'mpwem_script', MPWEM_PLUGIN_URL . '/assets/frontend/mpwem_script.js', array( 'jquery' ), time(), true );
				wp_localize_script( 'mpwem_script', 'mpwem_script_var', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'mpwem_nonce' ) ) );
				do_action( 'add_mpwem_frontend_script' );

			}
			public function add_admin_head() {
				$this->js_constant();
			}
			public function add_frontend_head() {
				$this->js_constant();
				$this->event_rich_text_data();
				$this->add_open_graph_tags();
			}
			public function js_constant() {
				?>
                <script type="text/javascript">
                    let mp_ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
                    var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
                    let mpwem_ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
                    let mpwem_currency_symbol = "<?php echo get_woocommerce_currency_symbol(); ?>";
                    let mpwem_currency_position = "<?php echo get_option( 'woocommerce_currency_pos' ); ?>";
                    let mpwem_currency_decimal = "<?php echo wc_get_price_decimal_separator(); ?>";
                    let mpwem_currency_thousands_separator = "<?php echo wc_get_price_thousand_separator(); ?>";
                    let mpwem_num_of_decimal = "<?php echo get_option( 'woocommerce_price_num_decimals', 2 ); ?>";
                    let mpwem_empty_image_url = "<?php echo esc_attr( MPWEM_PLUGIN_URL . '/assets/helper/images/no_image.png' ); ?>";
                    let mpwem_date_format = "<?php echo  MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_datepicker_format', 'D d M , yy' );?>";
                    //let mp_nonce = wp_create_nonce('mep-ajax-nonce');
                </script>
				<?php
			}
			//This the function which will create the Rich Text Schema For each event into the <head></head> section.
			public function event_rich_text_data() {
				global $post;
				if ( is_single() ) {
					$event_id = $post->ID;
					if ( $event_id && get_post_type( $event_id ) == 'mep_events' ) {
						$event_name           = get_the_title( $event_id );
						$event_start_date     = get_post_meta( $post->ID, 'event_start_datetime', true ) ? wp_date( 'Y-m-d H:i:s T', strtotime( get_post_meta( $post->ID, 'event_start_datetime', true ) ) ) : '';
						$event_end_date       = get_post_meta( $post->ID, 'event_end_datetime', true ) ? get_post_meta( $post->ID, 'event_end_datetime', true ) : '';
						$event_rt_status      = get_post_meta( $post->ID, 'mep_rt_event_status', true ) ? get_post_meta( $post->ID, 'mep_rt_event_status', true ) : 'EventRescheduled';
						$event_rt_atdnce_mode = get_post_meta( $post->ID, 'mep_rt_event_attandence_mode', true ) ? get_post_meta( $post->ID, 'mep_rt_event_attandence_mode', true ) : 'OfflineEventAttendanceMode';
						$event_rt_prv_date    = get_post_meta( $post->ID, 'mep_rt_event_prvdate', true ) ? get_post_meta( $post->ID, 'mep_rt_event_prvdate', true ) : $event_start_date;
						$terms                = get_the_terms( $event_id, 'mep_org' );
						$org_name             = is_array( $terms ) && sizeof( $terms ) > 0 ? $terms[0]->name : 'No Performer';
						$rt_status            = get_post_meta( $event_id, 'mep_rich_text_status', true ) ? get_post_meta( $event_id, 'mep_rich_text_status', true ) : 'enable';
						if ( $rt_status == 'enable' ) {
							ob_start();
							?>
                            <script type="application/ld+json">
                                {
								"@context"  : "https://schema.org",
								"@type"     : "Event",
								"name"      : "<?php echo esc_attr( $event_name ); ?>",
                            "startDate" : "<?php echo esc_attr( $event_start_date ); ?>",
                            "endDate"   : "<?php echo esc_attr( $event_end_date ); ?>",
                            "offers": {
                                "@type"         : "Offer",
                                "url"           : "<?php echo get_the_permalink( $event_id ); ?>",
                                "price"         : "<?php echo strip_tags( mep_event_list_number_price( $event_id ) ); ?>",
                                "priceCurrency" : "<?php echo get_woocommerce_currency(); ?>",
                                "availability"  : "https://schema.org/InStock",
                                "validFrom"     : "<?php echo esc_attr( $event_end_date ); ?>"
                            },
                            "organizer": {
                                "@type" : "Organization",
                                "name"  : "<?php echo esc_attr( $org_name ); ?>",
                                "url"   : "<?php echo get_the_permalink( $event_id ); ?>"
                            },
                            "eventStatus"           : "https://schema.org/<?php echo esc_attr( $event_rt_status ); ?>",
                            "eventAttendanceMode"   : "https://schema.org/<?php echo esc_attr( $event_rt_atdnce_mode ); ?>",
                            "previousStartDate"     : "<?php echo esc_attr( $event_rt_prv_date ); ?>",

                            "location"  : <?php
									// Determine if this is an online/virtual event
									$location_data    = MPWEM_Functions::get_location( $event_id );
									$location_display = '';
									// Get location/venue first
									if ( ! empty( $location_data['location'] ) ) {
										$location_display = $location_data['location'];
									} else {
										// If no location/venue, build from street + city
										$location_parts = array();
										if ( ! empty( $location_data['street'] ) ) {
											$location_parts[] = $location_data['street'];
										}
										if ( ! empty( $location_data['city'] ) ) {
											$location_parts[] = $location_data['city'];
										}
										if ( ! empty( $location_parts ) ) {
											$location_display = implode( ' ', $location_parts );
										}
									}
									// Check if event is virtual/online
									$is_online_event = ! empty( $location_display ) && stripos( $location_display, 'virtual' ) !== false;
									if ( $is_online_event || $event_rt_atdnce_mode === 'OnlineEventAttendanceMode' ) {
										// Output VirtualLocation for online events
										echo '{
                                        "@type"         : "VirtualLocation",
                                        "url"           : "' . esc_url( get_the_permalink( $event_id ) ) . '"
                                    }';
									} else {
										// Output Place for physical events
										echo '{
                                        "@type"         : "Place",
                                        "name"          : "' . esc_attr( MPWEM_Functions::get_location( $event_id, 'location' ) ) . '",
                                        "address"       : {
                                        "@type"         : "PostalAddress",
                                        "streetAddress" : "' . esc_attr( MPWEM_Functions::get_location( $event_id, 'street' ) ) . '",
                                        "addressLocality": "' . esc_attr( MPWEM_Functions::get_location( $event_id, 'city' ) ) . '",
                                        "postalCode"    : "' . esc_attr( MPWEM_Functions::get_location( $event_id, 'zip' ) ) . '",
                                        "addressRegion" : "' . esc_attr( MPWEM_Functions::get_location( $event_id, 'state' ) ) . '",
                                        "addressCountry": "' . esc_attr( MPWEM_Functions::get_location( $event_id, 'country' ) ) . '"
                                        }
                                    }';
									}
								?>,
                            "image": [
                                "<?php echo get_the_post_thumbnail_url( $event_id, 'full' ); ?>"
                            ],
                            "description": "<?php echo strip_tags( mep_string_sanitize( get_the_excerpt( $event_id ) ) ); ?>",
                            "performer": {
                                "@type" : "PerformingGroup",
                                "name"  : "<?php echo esc_attr( $org_name ); ?>"
                            }
                            }
                            </script>
							<?php
							echo ob_get_clean();
						}
					}
				}
			}
			// Add Open Graph meta tags for better social sharing
			public function add_open_graph_tags() {
				global $post;
				if ( is_single() ) {
					$event_id = $post->ID;
					if ( $event_id && get_post_type( $event_id ) == 'mep_events' ) {
						$event_name        = get_the_title( $event_id );
						$event_description = strip_tags( mep_string_sanitize( get_the_excerpt( $event_id ) ) );
						$event_url         = get_the_permalink( $event_id );
						$event_image       = get_the_post_thumbnail_url( $event_id, 'full' );
						// Output Open Graph meta tags
						echo '<meta property="og:type" content="website" />';
						echo '<meta property="og:title" content="' . esc_attr( $event_name ) . '" />';
						echo '<meta property="og:description" content="' . esc_attr( $event_description ) . '" />';
						echo '<meta property="og:url" content="' . esc_url( $event_url ) . '" />';
						if ( $event_image ) {
							echo '<meta property="og:image" content="' . esc_url( $event_image ) . '" />';
							echo '<meta property="og:image:width" content="1200" />';
							echo '<meta property="og:image:height" content="630" />';
						}
						// Facebook specific
						echo '<meta property="fb:app_id" content="' . esc_attr( apply_filters( 'mep_fb_app_id', '' ) ) . '" />';
						// Twitter Card
						echo '<meta name="twitter:card" content="summary_large_image" />';
						echo '<meta name="twitter:title" content="' . esc_attr( $event_name ) . '" />';
						echo '<meta name="twitter:description" content="' . esc_attr( $event_description ) . '" />';
						if ( $event_image ) {
							echo '<meta name="twitter:image" content="' . esc_url( $event_image ) . '" />';
						}
					}
				}
			}
		}
		new MPWEM_Dependencies();
	}
