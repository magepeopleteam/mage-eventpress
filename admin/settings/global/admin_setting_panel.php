<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	/**
	 * MagePeople Settings API
	 * @version 1.0
	 *
	 */
	if ( ! class_exists( 'MAGE_Events_Setting_Controls' ) ) :
		class MAGE_Events_Setting_Controls {
			private $settings_api;

			function __construct() {
				$this->settings_api = new MAGE_Setting_API;
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			}

			function admin_init() {
				//set the settings
				$this->settings_api->set_sections( $this->get_settings_sections() );
				$this->settings_api->set_fields( $this->get_settings_fields() );
				//initialize settings
				$this->settings_api->admin_init();
			}

			function admin_menu() {
				$event_label = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				//add_options_page( 'Event Settings', 'Event Settings', 'delete_posts', 'mep_event_settings_page', array($this, 'plugin_page') );
				$menu_label = sprintf(
				/* translators: %s is the event label, e.g., "Conference" */
					__( '%s Settings', 'mage-eventpress' ),
					$event_label
				);
				add_submenu_page(
					'edit.php?post_type=mep_events',
					$menu_label,
					$menu_label,
					'manage_options',
					'mep_event_settings_page',
					array( $this, 'plugin_page' )
				);
			}

			function get_settings_sections() {
				$sections = array(
					array(
						'id'    => 'general_setting_sec',
						'title' => '<i class="fas fa-cogs"></i>' . __( 'General Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'event_list_setting_sec',
						'title' => '<i class="far fa-calendar-alt"></i>' . __( 'Event List Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'single_event_setting_sec',
						'title' => '<i class="far fa-calendar-check"></i>' . __( 'Single Event Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'email_setting_sec',
						'title' => '<i class="fas fa-envelope"></i>' . __( 'Email Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'style_setting_sec',
						'title' => '<i class="fas fa-palette"></i>' . __( 'Style Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'icon_setting_sec',
						'title' => '<i class="fab fa-font-awesome"></i>' . __( 'Icon Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'carousel_setting_sec',
						'title' => '<i class="fas fa-sliders-h"></i>' . __( 'Carousel Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'mp_slider_settings',
						'title' => '<i class="fas fa-sliders-h"></i>' . __( 'Slider Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'mep_settings_licensing',
						'title' => __( 'License', 'mage-eventpress' )
					)
				);

				return apply_filters( 'mep_settings_sec_reg', $sections );
			}

			/**
			 * Returns all the settings fields
			 *
			 * @return array settings fields
			 */
			function get_settings_fields() {
				$current_date = current_time( 'Y-m-d' );
				$lang         = get_bloginfo( "language" );
				$settings_fields = array(
					'general_setting_sec'      => apply_filters( 'mep_settings_general_arr', array(
							array(
								'name'    => 'seat_reserved_order_status',
								'label'   => __( 'Seat Reserved Order Status', 'mage-eventpress' ),
								'desc'    => __( 'Please select in which order status seat will mark as reserved/booked. By Default is Processing & Completed.', 'mage-eventpress' ),
								'type'    => 'multicheck',
								'default' => array( 'processing' => 'processing', 'completed' => 'completed' ),
								'options' => array(
									'on-hold'    => 'On Hold',
									'pending'    => 'Pending',
									'processing' => 'Processing',
									'completed'  => 'Completed'
									// 'cancelled'     => 'Cancelled'
								)
							),
							array(
								'name'    => 'mep_disable_block_editor',
								'label'   => __( 'Block/Gutenberg Editor In Event', 'mage-eventpress' ),
								'desc'    => __( 'By default, the Gutenberg editor is disabled. To enable the Gutenberg editor, you need to activate this option and also ensure that the REST API is enabled in the settings below.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Disable',
									'no'  => 'Enable'
								)
							),
							array(
								'name'    => 'mep_event_list_page_style',
								'label'   => __( 'Dashboard Event List Page Style', 'mage-eventpress' ),
								'desc'    => __( 'You can choose the Event List Page Design in Dasboard', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'new',
								'options' => array(
									'new' => 'New Modern Style',
									'wp'  => 'WordPress Default Post List Style'
								)
							),
							array(
								'name'    => 'mep_rest_api_status',
								'label'   => __( 'Enable Rest API?', 'mage-eventpress' ),
								'desc'    => __( 'If you want to enable event data available in the Rest API Please enable this.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_multi_lang_plugin',
								'label'   => __( 'Choose Multilingual Plugin', 'mage-eventpress' ),
								'desc'    => __( 'Please select the name of your multilingual plugin from the list below.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'none',
								'options' => array(
									'none'     => 'None',
									'polylang' => 'Polylang',
									'wpml'     => 'WPML'
								)
							),
							array(
								'name'    => 'mep_event_list_order_by',
								'label'   => __( 'Event List Order By', 'mage-eventpress' ),
								'desc'    => __( 'Please select Event list order by which value Event Title or Event Date. By Default is: Event Upcoming Date', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'meta_value',
								'options' => array(
									'meta_value' => 'Event Upcoming Date',
									'title'      => 'Event Title'
								)
							),
							array(
								'name'    => 'mep_event_label',
								'label'   => __( 'Event Label', 'mage-eventpress' ),
								'desc'    => __( 'It will change the event post type label for the entire plugin.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Events'
							),
							array(
								'name'    => 'mep_event_slug',
								'label'   => __( 'Event Slug', 'mage-eventpress' ),
								'desc'    => __( 'It will change the event slug throughout the entire plugin. Remember, after changing this slug you need to flush permalinks. Just go to <strong>Settings->Permalinks</strong> hit the Save Settings button', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'events'
							),
							array(
								'name'    => 'mep_event_icon',
								'label'   => __( 'Event Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please enter the icon class name for the event custom post type. You can find icons here: Example: dashicons-calendar-alt. Find Icons: <a href="https://developer.wordpress.org/resource/dashicons/">Dashicons</a>', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'dashicons-calendar-alt'
							),
							array(
								'name'    => 'mep_event_cat_label',
								'label'   => __( 'Event Category Label', 'mage-eventpress' ),
								'desc'    => __( 'This change will apply the event category label to the whole plugin.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Category'
							),
							array(
								'name'    => 'mep_event_cat_slug',
								'label'   => __( 'Event Category Slug', 'mage-eventpress' ),
								'desc'    => __( 'It will change the category slug for the entire plugin. Remember that after you change this slug, you need to flush permalinks. To do this, just go to <strong>Settings->Permalinks</strong> hit the Save Settings button.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'mep_cat'
							),
							array(
								'name'    => 'mep_event_org_label',
								'label'   => __( 'Event Organizer Label', 'mage-eventpress' ),
								'desc'    => __( 'This will change the event organizer label throughout the plugin.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Organizer'
							),
							array(
								'name'    => 'mep_event_org_slug',
								'label'   => __( 'Event Organizer Slug', 'mage-eventpress' ),
								'desc'    => __( 'Changing the event organizer slug will have an effect on the entire plugin. Remember, after changing the slug, you will need to flush the permalinks. To do so, simply go to your settings page and select the flush permalinks option. <strong>Settings->Permalinks</strong> hit the Save Settings button.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'mep_org'
							),
							array(
								'name'    => 'mep_google_map_type',
								'label'   => __( 'Google Map Type', 'mage-eventpress' ),
								'desc'    => __( 'Please select the preferred map type you want to be displayed on the front page.<br><strong>Note:</strong> It"s been known that Iframe does not always show the precise location, whereas the API enabled map has a drag and drop feature for more accuracy. So if necessary, you can drag the point to the desired location.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									''       => 'Please Select a Map Type',
									'api'    => 'API',
									'iframe' => 'Iframe'
								)
							),
							array(
								'name'    => 'google-map-api',
								'label'   => __( 'Google Map API Key', 'mage-eventpress' ),
								'desc'    => __( 'Enter Your Google Map API key. <a href=https://developers.google.com/maps/documentation/javascript/get-api-key target=_blank>Get API Key</a>. <br><strong>Note:</strong> You must enter your billing address and information into the Google Maps API account to make it perfectly workable on your website.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => ''
							),
							array(
								'name'    => 'mep_event_expire_on_datetimes',
								'label'   => __( 'When will the event expire', 'mage-eventpress' ),
								'desc'    => __( 'Please select when the event will end', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'mep_event_start_date',
								'options' => array(
									'event_start_datetime'  => 'Event Start Time',
									'event_expire_datetime' => 'Event End Time'
								)
							),
							array(
								'name'    => 'mep_hide_old_date',
								'label'   => __( 'Hide old date from date picker', 'mage-eventpress' ),
								'desc'    => __( 'If you would like to hide the location details from the order details section on the thank you page and confirmation email body, please choose "Yes". If you would like to show the location details, please choose "No". The default setting is "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_expire_ticket',
								'label'   => __( 'Hide expire ticket type', 'mage-eventpress' ),
								'desc'    => __( 'If you would like to hide the location details from the order details section on the thank you page and confirmation email body, please choose "Yes". If you would like to show the location details, please choose "No". The default setting is "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_location_from_order_page',
								'label'   => __( 'Hide Location From Order Details & Email Section', 'mage-eventpress' ),
								'desc'    => __( 'If you would like to hide the location details from the order details section on the thank you page and confirmation email body, please choose "Yes". If you would like to show the location details, please choose "No". The default setting is "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_date_from_order_page',
								'label'   => __( 'Hide Date From Order Details & Email Section', 'mage-eventpress' ),
								'desc'    => __( 'This toggle determines whether or not the date is shown in the order details section of the thank you page and confirmation email body. Choose "Yes" to hide the date or "No" to show it. The default is "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_expired_date_in_calendar',
								'label'   => __( 'Hide Expired Event from Calendar', 'mage-eventpress' ),
								'desc'    => __( 'If you want to hide the expired event from the calendar please select Yes. Its applicable for the Free Calendar', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_direct_checkout',
								'label'   => __( 'Redirect Checkout after Booking', 'mage-eventpress' ),
								'desc'    => __( 'This setting controls whether or not the checkout page is redirected after booking an event.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Enable',
									'no'  => 'Disable'
								)
							),
							array(
								'name'    => 'mep_show_zero_as_free',
								'label'   => __( 'Show 0 Price as Free', 'mage-eventpress' ),
								'desc'    => __( 'This setting enables you to a "Free" at a price of 0. By default, this setting is enabled.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'        => 'mep_ticket_expire_time',
								'label'       => __( 'Event Ticket Expire before minutes', 'mage-eventpress' ),
								'desc'        => __( 'Please enter the number of minutes before the event that an attendee cannot book/register a ticket.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => '0',
								'placeholder' => '15'
							),
							array(
								'name'        => 'mep_ticket_expire_time_on_cart',
								'label'       => __( 'Event Expire Time on Cart', 'mage-eventpress' ),
								'desc'        => __( 'Please enter the number of minutes after that the event will removed from the cart', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => '10',
								'placeholder' => '10'
							),							
							array(
								'name'    => 'mep_load_fontawesome_from_theme',
								'label'   => __( 'Load Font Awesome From Theme?', 'mage-eventpress' ),
								'desc'    => __( 'If the icons are not working and you want to disable Font Awesome loading from the plugin, select Yes.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_load_flaticon_from_theme',
								'label'   => __( 'Load Flat Icon From Theme?', 'mage-eventpress' ),
								'desc'    => __( 'If the icons are not working, and you want to remove Flat Icon load from the plugin, select "Yes."', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_speed_up_list_page',
								'label'   => __( 'Speed up the Event List Page Loading?', 'mage-eventpress' ),
								'desc'    => __( 'If your event list page is loading slowly, you can select this option to improve performance. Keep in mind that selecting this option will disable Waitlist and Seat count features. ', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_not_available_event_from_list_page',
								'label'   => __( 'Disappear Event from list when fully booked?', 'mage-eventpress' ),
								'desc'    => __( 'If you want your event to be removed from the list once it is fully booked, you can select "Yes" here.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_sold_out_ribbon_list_page',
								'label'   => __( 'Show Sold out Ribon?', 'mage-eventpress' ),
								'desc'    => __( 'You can show a "Sold Out" Ribbon on the event list when it is fully booked by selecting "Yes" here.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_limited_availability_ribbon',
								'label'   => __( 'Show Limited Availability Ribbon?', 'mage-eventpress' ),
								'desc'    => __( 'Display a "Limited Availability" ribbon when tickets are running low but not sold out yet.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'        => 'mep_limited_availability_threshold',
								'label'       => __( 'Limited Availability Threshold', 'mage-eventpress' ),
								'desc'        => __( 'Show "Limited Availability" ribbon when available seats are less than or equal to this number.', 'mage-eventpress' ),
								'type'        => 'number',
								'default'     => '0',
								'placeholder' => '5'
							),
							array(
								'name'        => 'mep_limited_availability_text',
								'label'       => __( 'Limited Availability Ribbon Text', 'mage-eventpress' ),
								'desc'        => __( 'The text to display on the limited availability ribbon.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => 'Limited Availability',
								'placeholder' => 'Limited Availability'
							),
							array(
								'name'    => 'mep_show_low_stock_warning',
								'label'   => __( 'Show Low Stock Warning?', 'mage-eventpress' ),
								'desc'    => __( 'Enable this to show a warning message when event seats are running low.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'        => 'mep_low_stock_threshold',
								'label'       => __( 'Low Stock Threshold', 'mage-eventpress' ),
								'desc'        => __( 'Show low stock warning when available seats are less than or equal to this number.', 'mage-eventpress' ),
								'type'        => 'number',
								'default'     => '0',
								'placeholder' => '3'
							),
							array(
								'name'        => 'mep_low_stock_text',
								'label'       => __( 'Low Stock Warning Text', 'mage-eventpress' ),
								'desc'        => __( 'The text to display when seats are running low.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => 'Hurry! Only %s seats left',
								'placeholder' => 'Hurry! Only %s seats left'
							),
							array(
								'name'    => 'mep_enable_low_stock_email',
								'label'   => __( 'Send Low Stock Email Notifications?', 'mage-eventpress' ),
								'desc'    => __( 'Enable this to send email notifications to admin when event seats are running low.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_hidden_wc_product',
								'label'   => __( 'Show Hidden Woocommerce Products?', 'mage-eventpress' ),
								'desc'    => __( 'With every creation of an event there is a Woocommerce product is also created. By default its hidden in the Product list. If you want to show them in the list select Yes', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_google_map_zoom_level',
								'label'   => __( 'Set the Google Map Zoom Level', 'mage-eventpress' ),
								'desc'    => __( 'Select the Google Map zoom level. By default is 17', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => '17',
								'options' => array(
									'5'  => '5',
									'6'  => '6',
									'7'  => '7',
									'8'  => '8',
									'9'  => '9',
									'10' => '10',
									'11' => '11',
									'12' => '12',
									'13' => '13',
									'14' => '14',
									'15' => '15',
									'16' => '16',
									'17' => '17',
									'18' => '18',
									'19' => '19',
									'20' => '20',
									'21' => '21',
									'22' => '22',
									'23' => '23',
									'24' => '24',
									'25' => '25'
								)
							),
							array(
								'name'    => 'mep_show_event_sidebar',
								'label'   => __( 'Show Event Sidebar Widgets?', 'mage-eventpress' ),
								'desc'    => __( 'If you enable this then a Widget area will be registred and you can add any widgets from the Widget Menu. By default its disabled', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_clear_cart_after_checkout',
								'label'   => __( 'Clear Cart after Checkout Order Placed?', 'mage-eventpress' ),
								'desc'    => __( 'By default we clear the cart after order placed, But some payment gateway need cart data after order placed. If you get any warning after order placed please disabled this and try again. Unless please do not change this settings.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'enable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_manual_seat_Left_fix',
								'label'   => __( 'Manual Seat Left Fixing?', 'mage-eventpress' ),
								'desc'    => __( 'If you encounter the message "Sorry, There Are No Seats Available" after updating to version 4.3.0 or later, you may enable this setting. Otherwise, please keep it unchanged.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_fix_details_page_fatal_error',
								'label'   => __( 'Event Details Page Fatal Error Fix?', 'mage-eventpress' ),
								'desc'    => __( 'If you encounter a Fatal Error message on the event details page, you can enable this patch and check if the error persists. However, if there is no error, we recommend keeping the patch disabled', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_datepicker_format',
								'label'   => __( 'Date Picker Format', 'mage-eventpress' ),
								'desc'    => __( 'If you want to change Date Picker Format, please select format. Default is yy-mm-dd. <b>Text Based Date format will not works in other language except english. Is your website is not English language please do not use any text based datepicker.</b>', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yy-mm-dd'   => $current_date,
									'yy/mm/dd'   => date( 'Y/m/d', strtotime( $current_date ) ),
									// 'yy-dd-mm'      => date('Y-d-m',strtotime($current_date)),
									// 'yy/dd/mm'      => date('Y/d/m',strtotime($current_date)),
									'dd-mm-yy'   => date( 'd-m-Y', strtotime( $current_date ) ),
									// 'dd/mm/yy'      => date('d/m/Y',strtotime($current_date)),
									'mm-dd-yy'   => date( 'm-d-Y', strtotime( $current_date ) ),
									'mm/dd/yy'   => date( 'm/d/Y', strtotime( $current_date ) ),
									'd M , yy'   => date( 'j M , Y', strtotime( $current_date ) ),
									'D d M , yy' => date( 'D j M , Y', strtotime( $current_date ) ),
									'M d , yy'   => date( 'M  j, Y', strtotime( $current_date ) ),
									'D M d , yy' => date( 'D M  j, Y', strtotime( $current_date ) ),
									$lang        => $lang,
								)
							)
						)
					),
					'event_list_setting_sec'   => apply_filters( 'mep_settings_event_list_arr', array(
							array(
								'name'    => 'mep_event_price_show',
								'label'   => __( 'On/Off Event Price in List', 'mage-eventpress' ),
								'desc'    => __( 'This enables or disables the event price in the list. By default, it is enabled.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_price_show', 'general_setting_sec', 'event_list_setting_sec', 'yes' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_date_list_in_event_listing',
								'label'   => __( 'On/Off Multi Date List in Event listing Page', 'mage-eventpress' ),
								'desc'    => __( 'This feature enables or disables the full date list for multi-date events in the event listing page. By default, this feature is enabled.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_date_list_in_event_listing', 'general_setting_sec', 'event_list_setting_sec', 'yes' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_organizer_list',
								'label'   => __( 'Hide Organizer Section from list', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_organizer_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_location_list',
								'label'   => __( 'Hide Location Section from list', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_location_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time_list',
								'label'   => __( 'Hide Full Time Section from list', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_time_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_end_time_list',
								'label'   => __( 'Hide Only End Time Section from list', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_end_time_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_event_hover_btn',
								'label'   => __( 'Hide/Show Event Hover Book Now Button', 'mage-eventpress' ),
								'desc'    => __( 'Please select either \'Yes\' to hide or \'No\' to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_hide_event_hover_btn', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
						)
					),
					'single_event_setting_sec' => apply_filters( 'mep_settings_single_event_arr', array(
							array(
								'name'    => 'mep_enable_speaker_list',
								'label'   => __( 'On/Off Speaker List', 'mage-eventpress' ),
								'desc'    => __( 'Please select \'Yes\' to display the speaker list. By default, the speaker list is disabled.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_enable_speaker_list', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_product_cat_in_event',
								'label'   => __( 'On/Off Product Category in Event', 'mage-eventpress' ),
								'desc'    => __( 'Enabling this feature will allow you to assign a product category to the event edit page. If you have a product category-based coupon code that you want to use, you have to assign the event to the same product category. In order to enable this feature, please select \'Yes\'.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_show_product_cat_in_event', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_global_single_template',
								'label'   => __( 'Single Event Page Template', 'mage-eventpress' ),
								'desc'    => __( 'This change will impact the template for the single event details page.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'default-theme.php',
								'options' => mep_event_template_name()
							),
							array(
								'name'    => 'mep_event_product_type',
								'label'   => __( 'On/Off Shipping Method on event', 'mage-eventpress' ),
								'desc'    => __( 'The event product type in WooCommerce is set to virtual by default. If you change this type, you will need to save all of your events again.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_product_type', 'general_setting_sec', 'single_event_setting_sec', 'yes' ),
								'options' => array(
									'yes' => 'No',
									'no'  => 'Yes'
								)
							),
							array(
								'name'    => 'mep_event_hide_date_from_details',
								'label'   => __( 'Hide Event Date Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_date_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time_from_details',
								'label'   => __( 'Hide Event Time Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_time_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_location_from_details',
								'label'   => __( 'Hide Event Location Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_location_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_total_seat_from_details',
								'label'   => __( 'Hide Event Total Seats Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_total_seat_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_org_from_details',
								'label'   => __( 'Hide "Org By" Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_org_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_address_from_details',
								'label'   => __( 'Hide Event Address Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_address_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_event_schedule_details',
								'label'   => __( 'Hide Event Schedule Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_event_schedule_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_share_this_details',
								'label'   => __( 'Hide Event Share this Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_share_this_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_calendar_details',
								'label'   => __( 'Hide Add Calendar Button from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_calendar_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_description_title',
								'label'   => __( 'Hide Description Title', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_description_title', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_left_sidebar_title',
								'label'   => __( 'Hide Left Sidebar Title', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_left_sidebar_title', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time',
								'label'   => __( 'Hide Display Event Time Below Title', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_time', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
						)
					),
					'email_setting_sec'        => apply_filters( 'mep_settings_email_arr', array(
							array(
								'name'    => 'mep_email_sending_order_status',
								'label'   => __( 'Email Sent on order status', 'mage-eventpress' ),
								'desc'    => __( 'Please select when you would like the customer to receive an email confirming their order status event.', 'mage-eventpress' ),
								'type'    => 'multicheck',
								'default' => array( 'completed' => 'completed' ),
								'options' => array(
									'processing' => 'Processing',
									'completed'  => 'Completed'
								)
							),
							array(
								'name'    => 'mep_email_form_name',
								'label'   => __( 'Email From Name', 'mage-eventpress' ),
								'desc'    => __( 'Email From Name', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => get_bloginfo( 'name' )
							),
							array(
								'name'    => 'mep_email_form_email',
								'label'   => __( 'From Email', 'mage-eventpress' ),
								'desc'    => __( 'From Email', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => get_option( 'admin_email' )
							),
							array(
								'name'    => 'mep_email_subject',
								'label'   => __( 'Email Subject', 'mage-eventpress' ),
								'desc'    => __( 'Email Subject', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Event Notification'
							),
							array(
								'name'    => 'mep_confirmation_email_text',
								'label'   => __( 'Confirmation Email Text', 'mage-eventpress' ),
								'desc'    => __( 'Confirmation Email Text <b>Usable Dynamic tags:</b><br/> Attendee
                                        Name:<b>{name}</b><br/>
                                        Event Name: <b>{event}</b><br/>
                                        Ticket Type: <b>{ticket_type}</b><br/>
										Order ID: <b>{order_id}</b><br/>
                                        Event Date: <b>{event_date}</b><br/>
                                        Start Time: <b>{event_time}</b><br/>
                                        Full DateTime: <b>{event_datetime}</b><br/>
                                        Payment Method: <b>{payment_method}</b><br/>
                                        Amount Paid: <b>{amount_paid}</b>', 'mage-eventpress' ),
								'type'    => 'wysiwyg',
								'default' => 'Hi {name},<br><br>Thanks for joining the event.<br><br>Here are the event details:<br><br>Event Name: {event}<br><br>Ticket Type: {ticket_type}<br><br>Event Date: {event_date}<br><br>Start Time: {event_time}<br><br>Full DateTime: {event_datetime}<br><br>Payment Method: {payment_method}<br><br>Amount Paid: {amount_paid}<br><br>Thanks',
							),
							array(
								'name'    => 'mep_send_confirmation_to_billing_email',
								'label'   => __( 'Send Confirmation Email to Billing Email Address', 'mage-eventpress' ),
								'desc'    => __( 'By default Plugin sent the Event Confirmation Email to the Billing Email Address. If you want to turn off this you can disbale this setting.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'enable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							)
						)
					),
					'style_setting_sec'        => apply_filters( 'mep_settings_styling_arr', array(
							// Base Background & Text Color
							array(
								'name'    => 'mpev_primary_color',
								'label'   => __( 'Primary Color', 'mage-eventpress' ),
								'desc'    => __( 'Choose a basic color, it will change the icon background color & border color.', 'mage-eventpress' ),
								'type'    => 'color',
								'default' => '#6046ff'
							),
							array(
								'name'    => 'mpev_secondary_color',
								'label'   => __( 'Secondary Color', 'mage-eventpress' ),
								'desc'    => __( 'Choose a basic text color, it will change the text color.', 'mage-eventpress' ),
								'type'    => 'color',
								'default' => '#f1f5ff'
							),
						)
					),
					'icon_setting_sec'         => apply_filters( 'mep_settings_icon_arr', array(
							array(
								'name'    => 'mep_event_date_icon',
								'label'   => __( 'Choose Event Date Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event date icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-calendar',
							),
							array(
								'name'    => 'mep_event_time_icon',
								'label'   => __( 'Choose Event Time Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event time icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-clock',
							),
							array(
								'name'    => 'mep_event_location_icon',
								'label'   => __( 'Choose Event Location Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event location icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-marker',
							),
							array(
								'name'    => 'mep_event_organizer_icon',
								'label'   => __( 'Choose Event Organizer Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event organizer icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-badge',
							),
							array(
								'name'    => 'mep_event_location_list_icon',
								'label'   => __( 'Choose Event Sidebar Location List Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event sidebar location list icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-arrow-circle-right',
							),
							array(
								'name'    => 'mep_event_ss_fb_icon',
								'label'   => __( 'Choose Event Social Share Icon for Facebook', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for facebook.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-facebook-f',
							),
							array(
								'name'    => 'mep_event_ss_twitter_icon',
								'label'   => __( 'Choose Event Social Share Icon for Twitter', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for twitter.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-twitter',
							),
							array(
								'name'    => 'mep_event_ss_linkedin_icon',
								'label'   => __( 'Choose Event Social Share Icon for Linkedin', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for linkedin.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-linkedin',
							),
							array(
								'name'    => 'mep_event_ss_whatsapp_icon',
								'label'   => __( 'Choose Event Social Share Icon for Whatsapp', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for whatsapp.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-whatsapp',
							),
							array(
								'name'    => 'mep_event_ss_email_icon',
								'label'   => __( 'Choose Event Social Share Icon for Email', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for email.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-envelope',
							),
						)
					),
					'carousel_setting_sec'     => apply_filters( 'mep_settings_carousel_arr', array(
							array(
								'name'    => 'mep_load_carousal_from_theme',
								'label'   => __( 'Load Owl Carousel From Theme', 'mage-eventpress' ),
								'desc'    => __( 'Select "Yes" only if your theme already includes Owl Carousel library. Select "No" (recommended) to let the plugin load its own Owl Carousel library. If carousel is not working, ensure this is set to "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'no'  => __( 'No - Load from Plugin (Recommended)', 'mage-eventpress' ),
									'yes' => __( 'Yes - Load from Theme', 'mage-eventpress' )
								)
							),
							array(
								'name'    => 'mep_autoplay_carousal',
								'label'   => __( 'Auto Play', 'mage-eventpress' ),
								'desc'    => __( 'Please select Carousel will auto play or not.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'true'  => 'Yes',
									'false' => 'No'
								)
							),
							array(
								'name'    => 'mep_loop_carousal',
								'label'   => __( 'Infinite Loop', 'mage-eventpress' ),
								'desc'    => __( 'Please select Carousel will Infinite Loop or not.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'true'  => 'Yes',
									'false' => 'No'
								)
							),
							array(
								'name'    => 'mep_speed_carousal',
								'label'   => __( 'Carousel Auto Play Speed', 'mage-eventpress' ),
								'desc'    => __( 'Please Enter Carousel Auto Play Speed. Default is 5000', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => '5000'
							),
						)
					),
					'mp_slider_settings'       => array(
						array(
							'name'    => 'slider_type',
							'label'   => esc_html__( 'Slider Type', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Type Default Slider', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'slider',
							'options' => array(
								'slider'       => esc_html__( 'Slider', 'mage-eventpress' ),
								'single_image' => esc_html__( 'Post Thumbnail', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'slider_style',
							'label'   => esc_html__( 'Slider Style', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Style Default Style One', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'style_1',
							'options' => array(
								'style_1' => esc_html__( 'Style One', 'mage-eventpress' ),
								'style_2' => esc_html__( 'Style Two', 'mage-eventpress' ),
							)
						),
						array(
							'name'    => 'indicator_visible',
							'label'   => esc_html__( 'Slider Indicator Visible?', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Indicator Visible or Not? Default ON', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'indicator_type',
							'label'   => esc_html__( 'Slider Indicator Type', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Indicator Type Default Icon', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'icon',
							'options' => array(
								'icon'  => esc_html__( 'Icon Indicator', 'mage-eventpress' ),
								'image' => esc_html__( 'image Indicator', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'showcase_visible',
							'label'   => esc_html__( 'Slider Showcase Visible?', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Showcase Visible or Not? Default ON', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'showcase_position',
							'label'   => esc_html__( 'Slider Showcase Position', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Showcase Position Default Right', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'right',
							'options' => array(
								'top'    => esc_html__( 'At Top Position', 'mage-eventpress' ),
								'right'  => esc_html__( 'At Right Position', 'mage-eventpress' ),
								'bottom' => esc_html__( 'At Bottom Position', 'mage-eventpress' ),
								'left'   => esc_html__( 'At Left Position', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'popup_image_indicator',
							'label'   => esc_html__( 'Slider Popup Image Indicator', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Popup Indicator Image ON or Off? Default ON', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'popup_icon_indicator',
							'label'   => esc_html__( 'Slider Popup Icon Indicator', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Popup Indicator Icon ON or Off? Default ON', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'slider_height',
							'label'   => esc_html__( 'Slider height', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Height', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'avg',
							'options' => array(
								'min' => esc_html__( 'Minimum', 'mage-eventpress' ),
								'avg' => esc_html__( 'Average', 'mage-eventpress' ),
								'max' => esc_html__( 'Maximum', 'mage-eventpress' )
							)
						)
					),
				);

				return apply_filters( 'mep_settings_sec_fields', $settings_fields );
			}

			function plugin_page() {
				$label = get_plugin_data( __FILE__ )['Name'];
				?>
                <div class="wrap">
                    <div class="mp_settings_panel_header">
                        <h3>
							<?php echo esc_html( $label . esc_html__( ' Global Settings', 'mage-eventpress' ) ); ?>
                        </h3>
                    </div>
                    <div class="mp_settings_panel">
						<?php $this->settings_api->show_navigation(); ?>
						<?php $this->settings_api->show_forms(); ?>
                    </div>
                </div>
				<?php
			}

			/**
			 * Get all the pages
			 *
			 * @return array page names with key value pairs
			 */
			function get_pages() {
				$pages         = get_pages();
				$pages_options = array();
				if ( $pages ) {
					foreach ( $pages as $page ) {
						$pages_options[ $page->ID ] = $page->post_title;
					}
				}

				return $pages_options;
			}
		}
	endif;
	$settings = new MAGE_Events_Setting_Controls();
	function mep_get_option( $option, $section, $default = '' ) {
		$options = get_option( $section );
		if ( isset( $options[ $option ] ) ) {
			if ( is_array( $options[ $option ] ) ) {
				if ( ! empty( $options[ $option ] ) ) {
					return $options[ $option ];
				} else {
					return $default;
				}
			} else {
				if ( ! empty( $options[ $option ] ) ) {
					// return $options[$option];
					return wp_kses_post( $options[ $option ] );
				} else {
					return $default;
				}
			}
		}
		if ( is_array( $default ) ) {
			return $default;
		} else {
			return wp_kses_post( $default );
		}
	}
	add_action( 'wsa_form_bottom_mep_settings_licensing', 'mep_licensing_page', 5 );
	function mep_licensing_page( $form ) {
		?>
        <div class='mep-licensing-page'>
            <h3>Event Manager For Woocommerce Licensing</h3>
            <p>Thank you for using our Event Manager for WooCommerce plugin! This plugin is free to use and no license is required. However, we do have some additional add-ons which enhance the features and functionality of this plugin. If you have any of these add-ons, you will need to enter a valid license key below in order to continue using them. </p>
            <div class="mep_licensae_info"></div>
            <table class='wp-list-table widefat striped posts mep-licensing-table'>
                <thead>
                <tr>
                    <th>Plugin Name</th>
                    <th width=10%>Order No</th>
                    <th width=15%>Expire on</th>
                    <th width=30%>License Key</th>
                    <th width=10%>Status</th>
                    <th width=10%>Action</th>
                </tr>
                </thead>
                <tbody>
				<?php do_action( 'mep_license_page_addon_list' ); ?>
                </tbody>
            </table>
        </div>
		<?php
	}
	add_action( 'wsa_form_bottom_mep_settings_templates', 'mep_settings_template_page', 5 );
	function mep_settings_template_page( $form ) {
		?>
        <div class='mep-licensing-page'>
            <h3>Ready Templates For Event Details Page.</h3>
            <div class="mep_licensae_info"></div>
            <div class="mep-template-lists">
				<?php
					$url  = 'https://vaincode.com/update/template/template.json';
					$curl = curl_init();
					curl_setopt( $curl, CURLOPT_URL, $url );
					curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $curl, CURLOPT_HEADER, false );
					$data = curl_exec( $curl );
					curl_close( $curl );
					$obj = json_decode( $data, true );
					// print_r($data);
					if ( is_array( $obj ) && sizeof( $obj ) > 0 ) {
						?>
                        <div class="mep_ready_template_sec">
                            <ul class="mep_ready_template_list">
								<?php
									foreach ( $obj as $list ) {
										$name         = $list['name'];
										$banner       = $list['banner'];
										$url          = $list['url'];
										$type         = $list['type'];
										$editor       = $list['editor'];
										$preview      = $list['preview'];
										$name_slug    = sanitize_title( $name );
										$count_import = get_option( 'mep_import_template_' . $name_slug ) ? get_option( 'mep_import_template_' . $name_slug ) : 0;
										?>
                                        <li>
                                            <div class="template-thumb"><img src="<?php echo esc_url( $banner ); ?>" alt=""></div>
                                            <h3><?php echo esc_html( $name ); ?></h3>
											<?php if ( $count_import > 0 ) { ?>
                                                <p class="mep-template-import-count"> Imported <?php echo esc_html( $count_import ); ?> times</p>
												<?php
											}
												if ( did_action( 'elementor/loaded' ) && $editor == 'elm' ) {
													?>
                                                    <button class='import_template' data-file="<?php echo esc_attr( $url ); ?>" data-name="<?php echo esc_attr( $name ); ?>" data-editor="<?php echo esc_attr( $editor ); ?>" data-type="<?php echo esc_attr( $type ); ?>">Import</button>
													<?php
												} else {
													?>
                                                    <p class='mep-msg mep-msg-warning'>Elementor Not Installed</p>
												<?php } ?>
                                            <a href="<?php echo esc_url( $preview ); ?>" class='preview-btn btn' target='_blank'>Preview</a>
                                        </li>
									<?php } ?>
                            </ul>
                        </div>
					<?php } ?>
            </div>
            <script>
                (function ($) {
                    'use strict';
                    jQuery('.import_template').on('click', function () {
                        if (confirm('Are You Sure to Import this Template ? \n\n 1. Ok : To Import . \n 2. Cancel : To Cancel .')) {
                            let file = jQuery(this).data('file');
                            let type = jQuery(this).data('type');
                            let editor = jQuery(this).data('editor');
                            let name = jQuery(this).data('name');
                            jQuery.ajax({
                                type: 'POST',
                                url: mpwem_ajax_url,
                                data: {
                                    "action": "mep_import_ajax_template",
                                    "nonce": '<?php echo wp_create_nonce( 'mep-ajax-import-template-nonce' ); ?>',
                                    "file": file,
                                    "editor": editor,
                                    "name": name,
                                    "type": type
                                },
                                beforeSend: function () {
                                    jQuery('.mep_licensae_info').html('<h5 class="mep-msg mep-msg-process">Please wait.. Importing Template..</h5>');
                                },
                                success: function (data) {
                                    jQuery('.mep_licensae_info').html(data);
                                    window.location.reload();
                                }
                            });
                        } else {
                            return false;
                        }
                        return false;
                    });
                })(jQuery);
            </script>
        </div>
		<?php
	}
