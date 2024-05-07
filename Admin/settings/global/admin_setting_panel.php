<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * MagePeople Settings API
 * @version 1.0
 *
 */
if (!class_exists('MAGE_Events_Setting_Controls')) :
    class MAGE_Events_Setting_Controls {

        private $settings_api;

        function __construct() {
            $this->settings_api = new MAGE_Setting_API;

            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
        }

        function admin_init() {

            //set the settings
            $this->settings_api->set_sections($this->get_settings_sections());
            $this->settings_api->set_fields($this->get_settings_fields());

            //initialize settings
            $this->settings_api->admin_init();
        }

        function admin_menu() {
            $event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
            //add_options_page( 'Event Settings', 'Event Settings', 'delete_posts', 'mep_event_settings_page', array($this, 'plugin_page') );

            add_submenu_page('edit.php?post_type=mep_events', __($event_label . ' Settings', 'mage-eventpress'), __($event_label . ' Settings', 'mage-eventpress'), 'manage_options', 'mep_event_settings_page', array($this, 'plugin_page'));
        }

        function get_settings_sections() {

            $sections = array(
                array(
                    'id' => 'general_setting_sec',
                    'title' => '<i class="fas fa-cogs"></i>'.__('General Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'event_list_setting_sec',
                    'title' => '<i class="far fa-calendar-alt"></i>'.__('Event List Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'single_event_setting_sec',
                    'title' => '<i class="far fa-calendar-check"></i>'.__('Single Event Settings', 'mage-eventpress')
                ),                               
                array(
                    'id' => 'email_setting_sec',
                    'title' => '<i class="fas fa-envelope"></i>'.__('Email Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'datetime_setting_sec',
                    'title' => '<i class="fas fa-clock"></i>'.__('Date & Time Format Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'style_setting_sec',
                    'title' => '<i class="fas fa-palette"></i>'.__('Style Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'icon_setting_sec',
                    'title' => '<i class="fab fa-font-awesome"></i>'.__('Icon Settings', 'mage-eventpress')
                ),                
                array(
                    'id' => 'label_setting_sec',
                    'title' => '<i class="fas fa-language"></i>'.__('Translation Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'carousel_setting_sec',
                    'title' => '<i class="fas fa-sliders-h"></i>'.__('Carousel Settings', 'mage-eventpress')
                )
            );


            return apply_filters('mep_settings_sec_reg', $sections);
        }

        /**
         * Returns all the settings fields
         *
         * @return array settings fields
         */
        function get_settings_fields() {
            $settings_fields = array(
                'general_setting_sec' => apply_filters('mep_settings_general_arr', array(

                    array(
                        'name'          => 'seat_reserved_order_status',
                        'label'         => __('Seat Reserved Order Status', 'mage-eventpress'),
                        'desc'          => __('Please select in which order status seat will mark as reserved/booked. By Default is Processing & Completed.', 'mage-eventpress'),
                        'type'          => 'multicheck',
                        'default'       => array('processing' => 'processing','completed' => 'completed'),
                        'options'       => apply_filters('mep_event_seat_reduce_status_name_list',array(
                            'on-hold'       => 'On Hold',
                            'pending'       => 'Pending',
                            'processing'    => 'Processing',
                            'completed'     => 'Completed'
                            // 'cancelled'     => 'Cancelled'
                        ))
                    ),

                        array(
                            'name' => 'mep_disable_block_editor',
                            'label' => __('On/Off Block/Gutenberg Editor', 'mage-eventpress'),
                            'desc' => __('Enable/Disable gutenburg editor.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'yes',
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_multi_lang_plugin',
                            'label' => __('Choose Multilingual Plugin', 'mage-eventpress'),
                            'desc' => __('Please select the name of your multilingual plugin from the list below.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'none',
                            'options' => array(
                                'none' => 'None',
                                'polylang' => 'Polylang',
                                'wpml' => 'WPML'
                            )
                        ),
                        array(
                            'name'      => 'mep_event_list_order_by',
                            'label'     => __('Event List Order By', 'mage-eventpress'),
                            'desc'      => __('Please select Event list order by which value Event Title or Event Date. By Default is: Event Upcoming Date', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'meta_value',
                            'options'   => array(
                                'meta_value'    => 'Event Upcoming Date',
                                'title'         => 'Event Title'
                            )
                        ),                        
                        array(
                            'name' => 'mep_event_label',
                            'label' => __('Event Label', 'mage-eventpress'),
                            'desc' => __('It will change the event post type label for the entire plugin.', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => 'Events'
                        ),

                        array(
                            'name' => 'mep_event_slug',
                            'label' => __('Event Slug', 'mage-eventpress'),
                            'desc' => __('It will change the event slug throughout the entire plugin. Remember, after changing this slug you need to flush permalinks. Just go to <strong>Settings->Permalinks</strong> hit the Save Settings button', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => 'events'
                        ),

                        array(
                            'name' => 'mep_event_icon',
                            'label' => __('Event Icon', 'mage-eventpress'),
                            'desc' => __('Please enter the icon class name for the event custom post type. You can find icons here: Example: dashicons-calendar-alt. Find Icons: <a href="https://developer.wordpress.org/resource/dashicons/">Dashicons</a>', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => 'dashicons-calendar-alt'
                        ),


                        array(
                            'name' => 'mep_event_cat_label',
                            'label' => __('Event Category Label', 'mage-eventpress'),
                            'desc' => __('This change will apply the event category label to the whole plugin.', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => 'Category'
                        ),
                        array(
                            'name' => 'mep_event_cat_slug',
                            'label' => __('Event Category Slug', 'mage-eventpress'),
                            'desc' => __('It will change the category slug for the entire plugin. Remember that after you change this slug, you need to flush permalinks. To do this, just go to <strong>Settings->Permalinks</strong> hit the Save Settings button.', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => 'mep_cat'
                        ),
                        array(
                            'name' => 'mep_event_org_label',
                            'label' => __('Event Organizer Label', 'mage-eventpress'),
                            'desc' => __('This will change the event organizer label throughout the plugin.', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => 'Organizer'
                        ),
                        array(
                            'name' => 'mep_event_org_slug',
                            'label' => __('Event Organizer Slug', 'mage-eventpress'),
                            'desc' => __('Changing the event organizer slug will have an effect on the entire plugin. Remember, after changing the slug, you will need to flush the permalinks. To do so, simply go to your settings page and select the flush permalinks option. <strong>Settings->Permalinks</strong> hit the Save Settings button.', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => 'mep_org'
                        ),


                        array(
                            'name' => 'mep_google_map_type',
                            'label' => __('Google Map Type', 'mage-eventpress'),
                            'desc' => __('Please select the preferred map type you want to be displayed on the front page.<br><strong>Note:</strong> It"s been known that Iframe does not always show the precise location, whereas the API enabled map has a drag and drop feature for more accuracy. So if necessary, you can drag the point to the desired location.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'yes',
                            'options' => array(
                                '' => 'Please Select a Map Type',
                                'api' => 'API',
                                'iframe' => 'Iframe'
                            )
                        ),
                        array(
                            'name' => 'google-map-api',
                            'label' => __('Google Map API Key', 'mage-eventpress'),
                            'desc' => __('Enter Your Google Map API key. <a href=https://developers.google.com/maps/documentation/javascript/get-api-key target=_blank>Get API Key</a>. <br><strong>Note:</strong> You must enter your billing address and information into the Google Maps API account to make it perfectly workable on your website.', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => ''
                        ),
                        array(
                            'name' => 'mep_event_expire_on_datetimes',
                            'label' => __('When will the event expire', 'mage-eventpress'),
                            'desc' => __('Please select when the event will end', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'mep_event_start_date',
                            'options' => array(
                                'event_start_datetime' => 'Event Start Time',
                                'event_expire_datetime' => 'Event End Time'
                            )
                        ),
                        array(
                            'name' => 'mep_hide_location_from_order_page',
                            'label' => __('Hide Location From Order Details & Email Section', 'mage-eventpress'),
                            'desc' => __('If you would like to hide the location details from the order details section on the thank you page and confirmation email body, please choose "Yes". If you would like to show the location details, please choose "No". The default setting is "No".', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'no',
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_hide_date_from_order_page',
                            'label' => __('Hide Date From Order Details & Email Section', 'mage-eventpress'),
                            'desc' => __('This toggle determines whether or not the date is shown in the order details section of the thank you page and confirmation email body. Choose "Yes" to hide the date or "No" to show it. The default is "No".', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'no',
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_hide_expired_date_in_calendar',
                            'label' => __('Hide Expired Event from Calendar', 'mage-eventpress'),
                            'desc' => __('If you want to hide the expired event from the calendar please select Yes. Its applicable for the Free Calendar', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'no',
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),                        
                        array(
                            'name' => 'mep_event_direct_checkout',
                            'label' => __('Redirect Checkout after Booking', 'mage-eventpress'),
                            'desc' => __('This setting controls whether or not the checkout page is redirected after booking an event.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'yes',
                            'options' => array(
                                'yes' => 'Enable',
                                'no' => 'Disable'
                            )
                        ), 
                        array(
                            'name' => 'mep_show_zero_as_free',
                            'label' => __('Show 0 Price as Free', 'mage-eventpress'),
                            'desc' => __('This setting enables you to a "Free" at a price of 0. By default, this setting is enabled.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'yes',
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_ticket_expire_time',
                            'label' => __('Event Ticket Expire before minutes', 'mage-eventpress'),
                            'desc' => __('Please enter the number of minutes before the event that an attendee cannot book/register a ticket.', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => '0',
                            'placeholder' => '15'
                        ),   
                        array(
                            'name'      => 'mep_load_fontawesome_from_theme',
                            'label'     => __('Load Font Awesome From Theme?', 'mage-eventpress'),
                            'desc'      => __('If the icons are not working and you want to disable Font Awesome loading from the plugin, select Yes.', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'no',
                            'options'   => array(
                                'yes'   => 'Yes',
                                'no'    => 'No'
                            )
                        ),                                                                    
                        array(
                            'name'      => 'mep_load_flaticon_from_theme',
                            'label'     => __('Load Flat Icon From Theme?', 'mage-eventpress'),
                            'desc'      => __('If the icons are not working, and you want to remove Flat Icon load from the plugin, select "Yes."', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'no',
                            'options'   => array(
                                'yes'   => 'Yes',
                                'no'    => 'No'
                            )
                        ),                                                                    
                        array(
                            'name'      => 'mep_speed_up_list_page',
                            'label'     => __('Speed up the Event List Page Loading?', 'mage-eventpress'),
                            'desc'      => __('If your event list page is loading slowly, you can select this option to improve performance. Keep in mind that selecting this option will disable Waitlist and Seat count features. ', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'no',
                            'options'   => array(
                                'yes'   => 'Yes',
                                'no'    => 'No'
                            )
                        ),                                                                    
                        array(
                            'name'      => 'mep_hide_not_available_event_from_list_page',
                            'label'     => __('Disappear Event from list when fully booked?', 'mage-eventpress'),
                            'desc'      => __('If you want your event to be removed from the list once it is fully booked, you can select "Yes" here.', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'no',
                            'options'   => array(
                                'yes'   => 'Yes',
                                'no'    => 'No'
                            )
                        ),                                                                    
                        array(
                            'name'      => 'mep_show_sold_out_ribbon_list_page',
                            'label'     => __('Show Sold out Ribon?', 'mage-eventpress'),
                            'desc'      => __('You can show a "Sold Out" Ribbon on the event list when it is fully booked by selecting "Yes" here.', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'no',
                            'options'   => array(
                                'yes'   => 'Yes',
                                'no'    => 'No'
                            )
                        ),   
                     

                        array(
                            'name'      => 'mep_show_hidden_wc_product',
                            'label'     => __('Show Hidden Woocommerce Products?', 'mage-eventpress'),
                            'desc'      => __('With every creation of an event there is a Woocommerce product is also created. By default its hidden in the Product list. If you want to show them in the list select Yes', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'no',
                            'options'   => array(
                                'yes'   => 'Yes',
                                'no'    => 'No'
                            )
                        ),  

                        array(
                            'name'      => 'mep_google_map_zoom_level',
                            'label'     => __('Set the Google Map Zoom Level', 'mage-eventpress'),
                            'desc'      => __('Select the Google Map zoom level. By default is 17', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => '17',
                            'options'   => array(
                                '5'   => '5',
                                '6'   => '6',
                                '7'   => '7',
                                '8'   => '8',
                                '9'   => '9',
                                '10'   => '10',
                                '11'   => '11',
                                '12'   => '12',
                                '13'   => '13',
                                '14'   => '14',
                                '15'   => '15',
                                '16'   => '16',
                                '17'   => '17',
                                '18'   => '18',
                                '19'   => '19',
                                '20'   => '20',                                
                                '21'   => '21',
                                '22'   => '22',
                                '23'   => '23',
                                '24'   => '24',
                                '25'   => '25'
                            )
                        ),   
                        
                        array(
                            'name'      => 'mep_show_event_sidebar',
                            'label'     => __('Show Event Sidebar Widgets?', 'mage-eventpress'),
                            'desc'      => __('If you enable this then a Widget area will be registred and you can add any widgets from the Widget Menu. By default its disabled', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'disable',
                            'options'   => array(
                                'enable'   => 'Enable',
                                'disable'    => 'Disable'
                            )
                        ),                          
                        array(
                            'name'      => 'mep_clear_cart_after_checkout',
                            'label'     => __('Clear Cart after Checkout Order Placed?', 'mage-eventpress'),
                            'desc'      => __('By default we clear the cart after order placed, But some payment gateway need cart data after order placed. If you get any warning after order placed please disabled this and try again. Unless please do not change this settings.', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'enable',
                            'options'   => array(
                                'enable'        => 'Enable',
                                'disable'       => 'Disable'
                            )
                        )                      
                    )
                ),
                'event_list_setting_sec' => apply_filters('mep_settings_event_list_arr', array(
                        array(
                            'name' => 'mep_event_price_show',
                            'label' => __('On/Off Event Price in List', 'mage-eventpress'),
                            'desc' => __('This enables or disables the event price in the list. By default, it is enabled.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_price_show', 'general_setting_sec', 'event_list_setting_sec', 'yes'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_date_list_in_event_listing',
                            'label' => __('On/Off Multi Date List in Event listing Page', 'mage-eventpress'),
                            'desc' => __('This feature enables or disables the full date list for multi-date events in the event listing page. By default, this feature is enabled.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_date_list_in_event_listing', 'general_setting_sec', 'event_list_setting_sec', 'yes'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_organizer_list',
                            'label' => __('Hide Organizer Section from list', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_organizer_list', 'general_setting_sec', 'event_list_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_location_list',
                            'label' => __('Hide Location Section from list', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_location_list', 'general_setting_sec', 'event_list_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_time_list',
                            'label' => __('Hide Full Time Section from list', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_time_list', 'general_setting_sec', 'event_list_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_end_time_list',
                            'label' => __('Hide Only End Time Section from list', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_end_time_list', 'general_setting_sec', 'event_list_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_hide_event_hover_btn',
                            'label' => __('Hide/Show Event Hover Book Now Button', 'mage-eventpress'),
                            'desc' => __('Please select either \'Yes\' to hide or \'No\' to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_hide_event_hover_btn', 'general_setting_sec', 'event_list_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                    )
                ),
                'single_event_setting_sec' => apply_filters('mep_settings_single_event_arr', array(
                        array(
                            'name' => 'mep_enable_speaker_list',
                            'label' => __('On/Off Speaker List', 'mage-eventpress'),
                            'desc' => __('Please select \'Yes\' to display the speaker list. By default, the speaker list is disabled.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_enable_speaker_list', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_show_product_cat_in_event',
                            'label' => __('On/Off Product Category in Event', 'mage-eventpress'),
                            'desc' => __('Enabling this feature will allow you to assign a product category to the event edit page. If you have a product category-based coupon code that you want to use, you have to assign the event to the same product category. In order to enable this feature, please select \'Yes\'.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_show_product_cat_in_event', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_global_single_template',
                            'label' => __('Single Event Page Template', 'mage-eventpress'),
                            'desc' => __('This change will impact the template for the single event details page.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_global_single_template', 'general_setting_sec', 'single_event_setting_sec', 'default-theme.php'),
                            'options' => mep_event_template_name()
                        ),
                        array(
                            'name' => 'mep_event_product_type',
                            'label' => __('On/Off Shipping Method on event', 'mage-eventpress'),
                            'desc' => __('The event product type in WooCommerce is set to virtual by default. If you change this type, you will need to save all of your events again.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_product_type', 'general_setting_sec', 'single_event_setting_sec', 'yes'),
                            'options' => array(
                                'yes' => 'No',
                                'no' => 'Yes'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_date_from_details',
                            'label' => __('Hide Event Date Section from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_date_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_time_from_details',
                            'label' => __('Hide Event Time Section from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_time_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_location_from_details',
                            'label' => __('Hide Event Location Section from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_location_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_total_seat_from_details',
                            'label' => __('Hide Event Total Seats Section from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_total_seat_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_org_from_details',
                            'label' => __('Hide "Org By" Section from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_org_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_address_from_details',
                            'label' => __('Hide Event Address Section from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_address_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_event_schedule_details',
                            'label' => __('Hide Event Schedule Section from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_event_schedule_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_share_this_details',
                            'label' => __('Hide Event Share this Section from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_share_this_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_event_hide_calendar_details',
                            'label' => __('Hide Add Calendar Button from Single Event Details page', 'mage-eventpress'),
                            'desc' => __('Please select "Yes" to hide or "No" to display.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => mep_change_global_option_section('mep_event_hide_calendar_details', 'general_setting_sec', 'single_event_setting_sec', 'no'),
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),
                    )
                ),

                'email_setting_sec' => apply_filters('mep_settings_email_arr', array(
                        array(
                            'name' => 'mep_email_sending_order_status',
                            'label' => __('Email Sent on order status', 'mage-eventpress'),
                            'desc' => __('Please select when you would like the customer to receive an email confirming their order status event.', 'mage-eventpress'),
                            'type' => 'multicheck',
                            'default' => array('completed' => 'completed'),
                            'options' => array(
                                'processing' => 'Processing',
                                'completed' => 'Completed'
                            )
                        ),

                        array(
                            'name' => 'mep_email_form_name',
                            'label' => __('Email From Name', 'mage-eventpress'),
                            'desc' => __('Email From Name', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => get_bloginfo( 'name' )
                        ),
                        array(
                            'name' => 'mep_email_form_email',
                            'label' => __('From Email', 'mage-eventpress'),
                            'desc' => __('From Email', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => get_option('admin_email')
                        ),
                        array(
                            'name' => 'mep_email_subject',
                            'label' => __('Email Subject', 'mage-eventpress'),
                            'desc' => __('Email Subject', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => 'Event Notification'
                        ),
                        array(
                            'name' => 'mep_confirmation_email_text',
                            'label' => __('Confirmation Email Text', 'mage-eventpress'),
                            'desc' => __('Confirmation Email Text <b>Usable Dynamic tags:</b><br/> Attendee
                                        Name:<b>{name}</b><br/>
                                        Event Name: <b>{event}</b><br/>
                                        Ticket Type: <b>{ticket_type}</b><br/>
                                        Event Date: <b>{event_date}</b><br/>
                                        Start Time: <b>{event_time}</b><br/>
                                        Full DateTime: <b>{event_datetime}</b>', 'mage-eventpress'),
                            'type' => 'wysiwyg',
                            'default' => 'Hi {name},<br><br>Thanks for joining the event.<br><br>Here are the event details:<br><br>Event Name: {event}<br><br>Ticket Type: {ticket_type}<br><br>Event Date: {event_date}<br><br>Start Time: {event_time}<br><br>Full DateTime: {event_datetime}<br><br>Thanks',
                        ),
                        array(
                            'name'      => 'mep_send_confirmation_to_billing_email',
                            'label'     => __('Send Confirmation Email to Billing Email Address', 'mage-eventpress'),
                            'desc'      => __('By default Plugin sent the Event Confirmation Email to the Billing Email Address. If you want to turn off this you can disbale this setting.', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'enable',
                            'options'   => array(
                                'enable'        => 'Enable',
                                'disable'       => 'Disable'
                            )
                        )                         
                    )
                ),

                'label_setting_sec' => apply_filters('mep_translation_string_arr', array(
                    array(
                        'name' => 'mep_hide_event_hover_btn_text',
                        'label' => __('Book Now', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Book Now.</strong>', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Book Now',
                        'placeholder' => 'Book Now'
                    ),
                    array(
                        'name' => 'event-price-label',
                        'label' => __('Price Starts from:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Price Starts from:</strong>', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Price Starts from:'
                    ),
                    array(
                        'name' => 'event_price_label_single',
                        'label' => __('Price:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Price:</strong>', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Price:'
                    ),                    
                    array(
                        'name' => 'mep_free_price_text',
                        'label' => __('Free', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Free</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Free',
                        'placeholder' => 'Free'
                    ),
                    array(
                        'name' => 'mep_event_ticket_type_text',
                        'label' => __('Ticket Type:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Ticket Type:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Ticket Type:'
                    ),
                    array(
                        'name' => 'mep_event_extra_service_text',
                        'label' => __('Extra Service:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Extra Service:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Extra Service:'
                    ),
                    array(
                        'name' => 'mep_cart_btn_text',
                        'label' => __('Register This Event', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Register This Event</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Register This Event'
                    ),
                    array(
                        'name' => 'mep_calender_btn_text',
                        'label' => __('Add To Your Calender', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Add To Your Calender</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'ADD TO YOUR CALENDAR'
                    ),
                    array(
                        'name' => 'mep_share_text',
                        'label' => __('Share This Event', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Share This Event</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Share This Event'
                    ),
                    array(
                        'name' => 'mep_organized_by_text',
                        'label' => __('Organized By:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Organized By:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_location_text',
                        'label' => __('Location:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Location:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_time_text',
                        'label' => __('Time:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Time:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_event_location_text',
                        'label' => __('Event Location:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Event Location:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_event_date_text',
                        'label' => __('Event Date:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Event Date:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_event_time_text',
                        'label' => __('Event Time:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Event Time:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_by_text',
                        'label' => __('By:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>By:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_total_seat_text',
                        'label' => __('Total Seats:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Total Seats:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_register_now_text',
                        'label' => __('Register Now:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Register Now:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_quantity_text',
                        'label' => __('Quantity:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Quantity:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_name_text',
                        'label' => __('Name:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Name:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Name:'
                    ),
                    array(
                        'name' => 'mep_price_text',
                        'label' => __('Price:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Price:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Price:'
                    ),
                    array(
                        'name' => 'mep_event_schedule_text',
                        'label' => __('Event Schedule Details', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Event Schedule Details</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Event Schedule Details'
                    ),
                    array(
                        'name' => 'mep_total_text',
                        'label' => __('Total:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Total:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_ticket_qty_text',
                        'label' => __('Ticket Qty', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Ticket Qty</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_per_ticket_price_text',
                        'label' => __('Per Ticket Price:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Per Ticket Price:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_no_ticket_selected_text',
                        'label' => __('No Ticket Selected!', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>No Ticket Selected!</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_no_seat_available_text',
                        'label' => __('No Seat Available', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>No Seat Available</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_not_available_text',
                        'label' => __('Not Available', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Not Available</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_event_expired_text',
                        'label' => __('Event Expired', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Event Expired</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_ticket_text',
                        'label' => __('Ticket', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Ticket</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_left_text',
                        'label' => __('Left', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Left</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_attendee_info_text',
                        'label' => __('Attendee info:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Attendee info:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_select_ticket_error_message',
                        'label' => __('Select Ticket Error Message:', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Select Ticket Error Message:</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Please select atleast one(1) ticket Quantity !'
                    ),
                    array(
                        'name' => 'mep_event_virtual_label',
                        'label' => __('Virtual Event', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Virtual Event</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Virtual Event'
                    ),
                    array(
                        'name' => 'mep_event_multidate_ribon_text',
                        'label' => __('Multi Date Event', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Multi Date Event</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Multi Date Event'
                    ),
                    array(
                        'name' => 'mep_event_view_more_date_btn_text',
                        'label' => __('View More Date', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>View More Date</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'View More Date'
                    ),
                    array(
                        'name' => 'mep_event_hide_date_list_btn_text',
                        'label' => __('Hide Date Lists', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Hide Date Lists</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Hide Date Lists'
                    ),
                    array(
                        'name' => 'mep_event_recurring_ribon_text',
                        'label' => __('Recurring Event', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Recurring Event</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Recurring Event'
                    ),
                    array(
                        'name' => 'mep_event_sold_out_label',
                        'label' => __('Sold Out', 'mage-eventpress'),
                        'desc' => __('Please enter the translated text of <strong>Sold Out</strong>.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Sold Out'
                    )

                )),

                'style_setting_sec' => apply_filters('mep_settings_styling_arr', array(

                    // Base Background & Text Color
                        array(
                            'name' => 'mep_base_color',
                            'label' => __('Base Background Color', 'mage-eventpress'),
                            'desc' => __('Choose a basic color, it will change the icon background color & border color.', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => ''
                        ),
                        array(
                            'name' => 'mep_base_text_color',
                            'label' => __('Base Text Color', 'mage-eventpress'),
                            'desc' => __('Choose a basic text color, it will change the text color.', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => ''
                        ),

                    // Label Background & Text Color
                        array(
                            'name' => 'mep_title_bg_color',
                            'label' => __('Label Background Color', 'mage-eventpress'),
                            'desc' => __('Choose label background color', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#ffbe30'
                        ),
                        array(
                            'name' => 'mep_title_text_color',
                            'label' => __('Label Text Color', 'mage-eventpress'),
                            'desc' => __('Choose label text color', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#fff'
                        ),
                        
                        array(
                            'name' => 'mep_cart_btn_bg_color',
                            'label' => __('Cart Button Background Color', 'mage-eventpress'),
                            'desc' => __('Choose cart button background color', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#ffbe30'
                        ),
                        array(
                            'name' => 'mep_cart_btn_text_color',
                            'label' => __('Cart Button Text Color', 'mage-eventpress'),
                            'desc' => __('Choose cart button text color', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#fff'
                        ),
                        array(
                            'name' => 'mep_calender_btn_bg_color',
                            'label' => __('Calender Button Background Color', 'mage-eventpress'),
                            'desc' => __('Choose calender button background color', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#ffbe30'
                        ),
                        array(
                            'name' => 'mep_calender_btn_text_color',
                            'label' => __('Calender Button Text Color', 'mage-eventpress'),
                            'desc' => __('Choose calender button text color', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#fff'
                        ),
                        array(
                            'name' => 'mep_faq_title_bg_color',
                            'label' => __('FAQ Title Background Color', 'mage-eventpress'),
                            'desc' => __('Choose FAQ title background color', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#ffbe30'
                        ),
                        array(
                            'name' => 'mep_faq_title_text_color',
                            'label' => __('FAQ Title Text Color', 'mage-eventpress'),
                            'desc' => __('Choose FAQ title text color', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#fff'
                        ),
                        array(
                            'name' => 'mep_royal_primary_bg_color',
                            'label' => __('Royal Template Primary Background Color', 'mage-eventpress'),
                            'desc' => __('Choose primary background color for royal template', 'mage-eventpress'),
                            'type' => 'color',
                        ),
                        array(
                            'name' => 'mep_royal_secondary_bg_color',
                            'label' => __('Royal Template Secondary Background Color', 'mage-eventpress'),
                            'desc' => __('Choose secondary background color for royal template', 'mage-eventpress'),
                            'type' => 'color',
                        ),
                        array(
                            'name' => 'mep_royal_icons_bg_color',
                            'label' => __('Royal Template Icons Background Color', 'mage-eventpress'),
                            'desc' => __('Choose icons background color for royal template', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#ffbe30'
                        ),
                        array(
                            'name' => 'mep_royal_border_color',
                            'label' => __('Royal Template Border Color', 'mage-eventpress'),
                            'desc' => __('Choose border color for royal template', 'mage-eventpress'),
                            'type' => 'color',
                        ),
                        array(
                            'name' => 'mep_royal_text_color',
                            'label' => __('Royal Template Text Color', 'mage-eventpress'),
                            'desc' => __('Choose text color for royal template', 'mage-eventpress'),
                            'type' => 'color',
                            'default' => '#000'
                        ),						
                    )
                ),

                'icon_setting_sec' => apply_filters('mep_settings_icon_arr', array(

                    array(
                        'name' => 'mep_event_date_icon',
                        'label' => __('Choose Event Date Icon', 'mage-eventpress'),
                        'desc' => __('Please choose event date icon.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fa fa-calendar',
                    ),
                    array(
                        'name' => 'mep_event_time_icon',
                        'label' => __('Choose Event Time Icon', 'mage-eventpress'),
                        'desc' => __('Please choose event time icon.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fas fa-clock',
                    ),
                    array(
                        'name' => 'mep_event_location_icon',
                        'label' => __('Choose Event Location Icon', 'mage-eventpress'),
                        'desc' => __('Please choose event location icon.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fas fa-map-marker-alt',
                    ),
                    array(
                        'name' => 'mep_event_organizer_icon',
                        'label' => __('Choose Event Organizer Icon', 'mage-eventpress'),
                        'desc' => __('Please choose event organizer icon.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'far fa-list-alt',
                    ),
                    array(
                        'name' => 'mep_event_location_list_icon',
                        'label' => __('Choose Event Sidebar Location List Icon', 'mage-eventpress'),
                        'desc' => __('Please choose event sidebar location list icon.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fa fa-arrow-circle-right',
                    ),
                    array(
                        'name' => 'mep_event_ss_fb_icon',
                        'label' => __('Choose Event Social Share Icon for Facebook', 'mage-eventpress'),
                        'desc' => __('Please choose event social share icon for facebook.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fab fa-facebook-f',
                    ),
                    array(
                        'name' => 'mep_event_ss_twitter_icon',
                        'label' => __('Choose Event Social Share Icon for Twitter', 'mage-eventpress'),
                        'desc' => __('Please choose event social share icon for twitter.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fab fa-twitter',
                    ),
                    array(
                        'name' => 'mep_event_ss_linkedin_icon',
                        'label' => __('Choose Event Social Share Icon for Linkedin', 'mage-eventpress'),
                        'desc' => __('Please choose event social share icon for linkedin.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fab fa-linkedin',
                    ),
                    array(
                        'name' => 'mep_event_ss_whatsapp_icon',
                        'label' => __('Choose Event Social Share Icon for Whatsapp', 'mage-eventpress'),
                        'desc' => __('Please choose event social share icon for whatsapp.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fab fa-whatsapp',
                    ),
                    array(
                        'name' => 'mep_event_ss_email_icon',
                        'label' => __('Choose Event Social Share Icon for Email', 'mage-eventpress'),
                        'desc' => __('Please choose event social share icon for email.', 'mage-eventpress'),
                        'type' => 'iconlib',
                        'default' => 'fa fa-envelope',
                    ),                                                                                                                                                         
                )
                ),

                'carousel_setting_sec' => apply_filters('mep_settings_carousel_arr', array(

                        array(
                            'name' => 'mep_load_carousal_from_theme',
                            'label' => __('Load Owl Carousel From Theme', 'mage-eventpress'),
                            'desc' => __('If your theme is not loading the Owl Carousel Library, or if you"re having issues with Owl Carousel, you can set this option to "Yes" to fix the issue.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'no',
                            'options' => array(
                                'yes' => 'Yes',
                                'no' => 'No'
                            )
                        ),

                        array(
                            'name' => 'mep_autoplay_carousal',
                            'label' => __('Auto Play', 'mage-eventpress'),
                            'desc' => __('Please select Carousel will auto play or not.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'yes',
                            'options' => array(
                                'true' => 'Yes',
                                'false' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_loop_carousal',
                            'label' => __('Infinite Loop', 'mage-eventpress'),
                            'desc' => __('Please select Carousel will Infinite Loop or not.', 'mage-eventpress'),
                            'type' => 'select',
                            'default' => 'yes',
                            'options' => array(
                                'true' => 'Yes',
                                'false' => 'No'
                            )
                        ),
                        array(
                            'name' => 'mep_speed_carousal',
                            'label' => __('Carousel Auto Play Speed', 'mage-eventpress'),
                            'desc' => __('Please Enter Carousel Auto Play Speed. Default is 5000', 'mage-eventpress'),
                            'type' => 'text',
                            'default' => '5000'
                        ),


                    )

                    ),

                'datetime_setting_sec' => apply_filters('mep_settings_datetime_arr', array(

                        array(
                            'name'      => 'mep_global_date_format',
                            'label'     => __('Date Format', 'mage-eventpress'),
                            'desc'      => __('Please select your desired date format from the list below. If you wish to use a custom date format, select the "Custom" option and write your format in the box provided. Note that this date format will apply to all events.', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => get_option('date_format'),
                            'class'     => 'mep_global_date_format',
                            'options'   => mep_date_format_list()
                        ),
                        array(
                            'name'      => 'mep_global_custom_date_format',
                            'label'     => __('Custom Date Format', 'mage-eventpress'),
                            'desc'      => __('<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">Documentation on date and time formatting</a>', 'mage-eventpress'),
                            'type'      => 'text',
                            'default'   => 'F j, Y'
                        ),
                        array(
                            'name'      => 'mep_global_time_format',
                            'label'     => __('Time Format', 'mage-eventpress'),
                            'desc'      => __('Please select the time format you want to use from the list. If you want to use a custom time format, select Custom and write your custom time format. This time format will apply to all events.', 'mage-eventpress'),
                            'type'      => 'select',
                            'class'     => 'mep_global_time_format',
                            'default'   => get_option('time_format'),
                            'options'   => mep_time_format_list()
                        ),
                        array(
                            'name'      => 'mep_global_custom_time_format',
                            'label'     => __('Custom Time Format', 'mage-eventpress'),
                            'desc'      => __('<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">Documentation on date and time formatting</a>', 'mage-eventpress'),
                            'type'      => 'text',
                            'default'   => 'g:i a'
                        ),
                        array(
                            'name'      => 'mep_global_timezone_display',
                            'label'     => __('Show Timezone', 'mage-eventpress'),
                            'desc'      => __('If you want to show the time zone with the date and time, please select "Yes".', 'mage-eventpress'),
                            'type'      => 'select',
                            'default'   => 'no',
                            'options'   => array(
                                'yes'   => 'Yes',
                                'no'    => 'No'
                            )
                        ),                      


                    )

                )











            );

            return apply_filters('mep_settings_sec_fields', $settings_fields);
        }

        function plugin_page() {
	        $label = mep_get_plugin_data('Name');
	        ?>
	        <div class="mp_settings_panel_header">
		        <h3>
			        <?php echo esc_html($label . esc_html__(' Global Settings', 'mage-eventpress')); ?>
		        </h3>
	        </div>
	        <div class="mp_settings_panel">
		        <?php $this->settings_api->show_navigation(); ?>
		        <?php $this->settings_api->show_forms(); ?>
	        </div>
	        <?php
        }

        /**
         * Get all the pages
         *
         * @return array page names with key value pairs
         */
        function get_pages() {
            $pages = get_pages();
            $pages_options = array();
            if ($pages) {
                foreach ($pages as $page) {
                    $pages_options[$page->ID] = $page->post_title;
                }
            }

            return $pages_options;
        }
    }
endif;

$settings = new MAGE_Events_Setting_Controls();


function mep_get_option($option, $section, $default = '') {

    $options = get_option($section);

    if (isset($options[$option])) {
        if(is_array($options[$option])){

            if(!empty($options[$option])){
                return $options[$option];
            }else{
                return $default;
            }

        }else{
            
            if(!empty($options[$option])){
                // return $options[$option];
                return wp_kses_post($options[$option]);
            }else{
                return $default;
            }
           

        }
    }

    if(is_array($default)){
        return $default;
    }else{
        return wp_kses_post($default);
    }

}


add_action('wsa_form_bottom_mep_settings_licensing', 'mep_licensing_page', 5);
function mep_licensing_page($form) {
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
            <?php do_action('mep_license_page_addon_list'); ?>
            </tbody>
        </table>
    </div>
    <?php
}

add_action('wsa_form_bottom_mep_settings_templates', 'mep_settings_template_page', 5);
function mep_settings_template_page($form) {
    ?>
    <div class='mep-licensing-page'>
        <h3>Ready Templates For Event Details Page.</h3>


        <div class="mep_licensae_info"></div>


        <div class="mep-template-lists">


            <?php
            $url = 'https://vaincode.com/update/template/template.json';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $data = curl_exec($curl);
            curl_close($curl);
            $obj = json_decode($data, true);

            // print_r($data);

            if (is_array($obj) && sizeof($obj) > 0) {
                ?>
                <div class="mep_ready_template_sec">
                    <ul class="mep_ready_template_list">
                        <?php
                        foreach ($obj as $list) {
                            $name = $list['name'];
                            $banner = $list['banner'];
                            $url = $list['url'];
                            $type = $list['type'];
                            $editor = $list['editor'];
                            $preview = $list['preview'];
                            $name_slug = sanitize_title($name);
                            $count_import = get_option('mep_import_template_' . $name_slug) ? get_option('mep_import_template_' . $name_slug) : 0;
                            ?>
                            <li>
                                <div class="template-thumb"><img src="<?php echo esc_url($banner); ?>" alt=""></div>
                                <h3><?php echo esc_html($name); ?></h3>
                                <?php if ($count_import > 0) { ?>
                                    <p class="mep-template-import-count"> Imported <?php echo esc_html($count_import); ?> times</p>
                                    <?php
                                }

                                if (did_action('elementor/loaded') && $editor == 'elm') {
                                    ?>
                                    <button class='import_template' data-file="<?php echo esc_attr($url); ?>" data-name="<?php echo esc_attr($name); ?>" data-editor="<?php echo esc_attr($editor); ?>" data-type="<?php echo esc_attr($type); ?>">Import</button>
                                    <?php
                                } else {
                                    ?>
                                    <p class='mep-msg mep-msg-warning'>Elementor Not Installed</p>
                                <?php } ?>
                                <a href="<?php echo esc_url($preview); ?>" class='preview-btn btn' target='_blank'>Preview</a>

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
                            url: mp_ajax_url,
                            data: {
                                "action": "mep_import_ajax_template",
                                "nonce": '<?php echo wp_create_nonce('mep-ajax-import-template-nonce'); ?>',
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