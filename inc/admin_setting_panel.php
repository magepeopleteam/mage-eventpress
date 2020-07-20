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
    class MAGE_Events_Setting_Controls
    {

        private $settings_api;

        function __construct()
        {
            $this->settings_api = new MAGE_Setting_API;

            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
        }

        function admin_init()
        {

            //set the settings
            $this->settings_api->set_sections($this->get_settings_sections());
            $this->settings_api->set_fields($this->get_settings_fields());

            //initialize settings
            $this->settings_api->admin_init();
        }

        function admin_menu()
        {
            $event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
            //add_options_page( 'Event Settings', 'Event Settings', 'delete_posts', 'mep_event_settings_page', array($this, 'plugin_page') );

            add_submenu_page('edit.php?post_type=mep_events', __($event_label.' Settings', 'mage-eventpress'), __($event_label.' Settings', 'mage-eventpress'), 'manage_options', 'mep_event_settings_page', array($this, 'plugin_page'));
        }

        function get_settings_sections()
        {

            $sections = array(
                array(
                    'id' => 'general_setting_sec',
                    'title' => __('General Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'email_setting_sec',
                    'title' => __('Email Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'style_setting_sec',
                    'title' => __('Style Settings', 'mage-eventpress')
                ),
                array(
                    'id' => 'label_setting_sec',
                    'title' => __('Translation Settings', 'mage-eventpress')
                )
            );



            return apply_filters('mep_settings_sec_reg', $sections);
        }

        /**
         * Returns all the settings fields
         *
         * @return array settings fields
         */
        function get_settings_fields()
        {
            $settings_fields = array(
                'general_setting_sec' => apply_filters('mep_settings_general_arr',array(
                   
                    array(
                        'name' => 'mep_event_label',
                        'label' => __('Event Label', 'mage-eventpress'),
                        'desc' => __('If you want to change the event label in the dashboard menu you can change here', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Events'
                    ),
                   
                    array(
                        'name' => 'mep_event_slug',
                        'label' => __('Event Slug', 'mage-eventpress'),
                        'desc' => __('Please enter the slug name you want. Remember after change this slug you need to flush permalink, Just go to Settings->Permalink hit the Save Settings button', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'events'
                    ),
                   
                    array(
                        'name' => 'mep_event_icon',
                        'label' => __('Event Icon', 'mage-eventpress'),
                        'desc' => __('If you want to change the event icon in the dashboard menu you can change from here, Dashboard icon only support dashicon, So please go to <a href=https://developer.wordpress.org/resource/dashicons/#calendar-alt target=_blank>Dash Icon</a> and copy your icon code and paste here', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'dashicons-calendar-alt'
                    ),
                   
                   
                    array(
                        'name' => 'mep_google_map_type',
                        'label' => __('Google Map Type?', 'mage-eventpress'),
                        'desc' => __('Please select the map type you  want to show in fronntend, Note it: Iframe Not always show the accurate location where API enable map has drag & drop feature so you can drag the point to accurate location.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'yes',
                        'options' =>  array(
                            '' => 'Please Select a Map Type',
                            'api' => 'API',
                            'iframe' => 'Iframe'
                        )
                    ),
                    array(
                        'name' => 'google-map-api',
                        'label' => __('Google Map API Key', 'mage-eventpress'),
                        'desc' => __('Enter Your Google Map API key. <a href=https://developers.google.com/maps/documentation/javascript/get-api-key target=_blank>Get KEY</a>. Note: You must enter billing address and information into google map api account to work perfectly the google map in website.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_enable_speaker_list',
                        'label' => __('Enable Speaker List?', 'mage-eventpress'),
                        'desc' => __('If you want to enable speaker list, Please select Yes by default its disable', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_global_single_template',
                        'label' => __('Event Details Template', 'mage-eventpress'),
                        'desc' => __('Event Details Template', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  mep_event_template_name()
                    ),
                    array(
                        'name' => 'mep_event_price_show',
                        'label' => __('Show Event Price in List?', 'mage-eventpress'),
                        'desc' => __('Please select if you want to show event price in the list Yes/No', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'yes',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_date_list_in_event_listing',
                        'label' => __('Show Multi Date List in Event listing Page?', 'mage-eventpress'),
                        'desc' => __('Please select if you want to show the full date list for multi date event in the event listing page', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'yes',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_product_type',
                        'label' => __('Enable Shipping Method on event?', 'mage-eventpress'),
                        'desc' => __('Please Select The event product type which is using in WooCommerce, By default its virtual. If you change this type you need to re-save all the event again.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'yes',
                        'options' =>  array(
                            'yes' => 'No',
                            'no' => 'Yes'
                        )
                    ),
                    array(
                        'name' => 'event-price-label',
                        'label' => __('Event Price Label', 'mage-eventpress'),
                        'desc' => __('Enter The text which you want to show as price label, Its only displayed if Show Event price value is YES above. ', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Price Starts from:'
                    ),
                    array(
                        'name' => 'mep_event_time_format',
                        'label' => __('Event Time Format', 'mage-eventpress'),
                        'desc' => __('Please select what format time you want to display in event fronntend.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'wtss',
                        'options' =>  array(
                            'wtss' => 'WordPress TimeStamp Settings'
                        )
                    ),
                    array(
                        'name' => 'mep_event_direct_checkout',
                        'label' => __('Redirect Checkout after Booking?', 'mage-eventpress'),
                        'desc' => __('If you want to go direct checkout page after booking an event please enable/disable this.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'yes',
                        'options' =>  array(
                            'yes' => 'Enable',
                            'no' => 'Disable'
                        )
                    ),
                    array(
                        'name' => 'mep_event_expire_on_datetimes',
                        'label' => __('When will event expire', 'mage-eventpress'),
                        'desc' => __('Please select when event will expire, On event start time or event endtime', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'mep_event_start_date',
                        'options' =>  array(
                            'event_start_datetime' => 'Event Start Time',
                            'event_expire_datetime' => 'Event End Time'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_organizer_list',
                        'label' => __('Hide Organizer Section from list ?', 'mage-eventpress'),
                        'desc' => __('Select yes to hide organizer section from list.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_location_list',
                        'label' => __('Hide Location Section from list ?', 'mage-eventpress'),
                        'desc' => __('Select yes to hide location section from list.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_time_list',
                        'label' => __('Hide Full Time Section from list ?', 'mage-eventpress'),
                        'desc' => __('Select yes to hide time section from list.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_end_time_list',
                        'label' => __('Hide Only End Time Section from list ?', 'mage-eventpress'),
                        'desc' => __('Select yes to hide Only End Time  section from list.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_date_from_details',
                        'label' => __('Hide Event Date Section from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to hide time section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_time_from_details',
                        'label' => __('Hide Event Time Section from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to hide time section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_location_from_details',
                        'label' => __('Hide Event Location Section from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to hide location section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_total_seat_from_details',
                        'label' => __('Hide Event Total Seat Section from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to hide Total Seat Section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_org_from_details',
                        'label' => __('Hide "Org By" Section from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to Event by Section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_address_from_details',
                        'label' => __('Hide Event Address Section from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to Event Address Section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_event_schedule_details',
                        'label' => __('Hide Event Schedule Section from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to Event Schedule Section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_share_this_details',
                        'label' => __('Hide Event Share this Section from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to Event Share this Section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_event_hide_calendar_details',
                        'label' => __('Hide Add Calendar Button from Details page ?', 'mage-eventpress'),
                        'desc' => __('Select yes to Event google map Section from details.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_hide_location_from_order_page',
                        'label' => __('Hide Location From Order Details & Email Section?', 'mage-eventpress'),
                        'desc' => __('If you want to hide Location from order details section in the tankyou page & from the confirmation email body, Please select Yes. By default is: No.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    ),
                    array(
                        'name' => 'mep_hide_date_from_order_page',
                        'label' => __('Hide Date From Order Details & Email Section?', 'mage-eventpress'),
                        'desc' => __('If you want to hide Date from order details section in the tankyou page & from the confirmation email body, Please select Yes. By default is: No.', 'mage-eventpress'),
                        'type' => 'select',
                        'default' => 'no',
                        'options' =>  array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        )
                    )
                )
                ),

                'email_setting_sec' => apply_filters('mep_settings_email_arr',array(
                    array(
                        'name' => 'mep_email_sending_order_status',
                        'label' => __( 'Email Sent on order status', 'mage-eventpress' ),
                        'desc' => __( 'Please Select when and which order status event confirmation email will send to customer', 'mage-eventpress' ),
                        'type' => 'multicheck',
                        'default' => array( 'completed' => 'completed' ),
                        'options' => array(
                            'processing' => 'Processing',
                            'completed' => 'Completed'
                        )
                    ),        
                                           
                    array(
                        'name' => 'mep_email_form_name',
                        'label' => __('Email Form Name', 'mage-eventpress'),
                        'desc' => __('Email Form Name', 'mage-eventpress'),
                        'default' => '',
                        'type' => 'text'
                    ),
                    array(
                        'name' => 'mep_email_form_email',
                        'label' => __('Form Email', 'mage-eventpress'),
                        'desc' => __('Form Email', 'mage-eventpress'),
                        'type' => 'text'
                    ),
                    array(
                        'name' => 'mep_email_subject',
                        'label' => __('Email Subject', 'mage-eventpress'),
                        'desc' => __('Email Subject', 'mage-eventpress'),
                        'type' => 'text'
                    ),
                    array(
                        'name' => 'mep_confirmation_email_text',
                        'label' => __('Confirmation Email Text', 'mage-eventpress'),
                        'desc' => __('Confirmation Email Text', 'mage-eventpress'),
                        'type' => 'wysiwyg',
                        'default' => '',
                    ),
                )
                ),

                'label_setting_sec' =>  apply_filters('mep_translation_string_arr',array(
                    array(
                        'name' => 'mep_event_ticket_type_text',
                        'label' => __('Ticket Type Table Label', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as ticket type table in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Ticket Type:'
                    ),
                    array(
                        'name' => 'mep_event_extra_service_text',
                        'label' => __('Extra Service Table Label', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as extra service table in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Extra Service:'
                    ),
                    array(
                        'name' => 'mep_cart_btn_text',
                        'label' => __('Cart Button Label', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display in Cart button in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Register This Event'
                    ),
                    array(
                        'name' => 'mep_calender_btn_text',
                        'label' => __('Add Calendar Button Label', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display in Add you calender in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'ADD TO YOUR CALENDAR'
                    ),
                    array(
                        'name' => 'mep_share_text',
                        'label' => __('Social Share Label', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as share button title in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Share This Event'
                    ),
                    array(
                        'name' => 'mep_organized_by_text',
                        'label' => __('Organized By:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Organized By in event list page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_location_text',
                        'label' => __('Location:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Location in event list page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_time_text',
                        'label' => __('Time:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Time in event list page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_event_location_text',
                        'label' => __('Event Location:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Event Location in event list page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_event_date_text',
                        'label' => __('Event Date:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Event Date in event list page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_event_time_text',
                        'label' => __('Event Time:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Event Time in event list page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_by_text',
                        'label' => __('By:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as By in event list page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_total_seat_text',
                        'label' => __('Total Seat:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Total Seat in event list page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_register_now_text',
                        'label' => __('Register Now:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Register Now in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_quantity_text',
                        'label' => __('Quantity:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Quantity in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_name_text',
                        'label' => __('Name:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Name in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Name:'
                    ),
                    array(
                        'name' => 'mep_price_text',
                        'label' => __('Price:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Price in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Price:'
                    ),
                    array(
                        'name' => 'mep_event_schedule_text',
                        'label' => __('Event Schedule Details', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Event Schedule Details in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Event Schedule Details'
                    ),
                    array(
                        'name' => 'mep_total_text',
                        'label' => __('Total:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Total in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_ticket_qty_text',
                        'label' => __('Ticket Qty', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Ticket Qty in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_per_ticket_price_text',
                        'label' => __('Per Ticket Price:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as per Ticket price in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_no_ticket_selected_text',
                        'label' => __('No Ticket Selected!', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as No Ticket Selected in event details page.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_no_seat_available_text',
                        'label' => __('No Seat Available', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as No Seat Available.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_not_available_text',
                        'label' => __('Not Available', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Not Available.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_event_expired_text',
                        'label' => __('Event Expired', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Event Expired.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),

                    array(
                        'name' => 'mep_ticket_text',
                        'label' => __('Ticket', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Ticket.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_left_text',
                        'label' => __('Left', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Left.', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_attendee_info_text',
                        'label' => __('Attendee info:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display as Attendee info:', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'mep_select_ticket_error_message',
                        'label' => __('Select Ticket Error Message:', 'mage-eventpress'),
                        'desc' => __('Enter the text which you want to display when no ticket selected popup display:', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Please select atleast one(1) ticket Quantity !'
                    ),
                    array(
                        'name' => 'mep_event_virtual_label',
                        'label' => __('Virtual Event Rebon', 'mage-eventpress'),
                        'desc' => __('Enter Text For Virtual Event Rebon', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Virtual Event'
                    ),
                    array(
                        'name' => 'mep_event_multidate_ribon_text',
                        'label' => __('Multi Date Event Rebon', 'mage-eventpress'),
                        'desc' => __('Enter Text For Multi Date Event Rebon', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Multi Date Event'
                    ),
                    array(
                        'name' => 'mep_event_view_more_date_btn_text',
                        'label' => __('View More Date Button', 'mage-eventpress'),
                        'desc' => __('Enter Text For View More Date button', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'View More Date'
                    ),
                    array(
                        'name' => 'mep_event_hide_date_list_btn_text',
                        'label' => __('Hide Date Lists Button', 'mage-eventpress'),
                        'desc' => __('Enter Text For Hide Date Lists button', 'mage-eventpress'),
                        'type' => 'text',
                        'default' => 'Hide Date Lists'
                    )
                   
                )),

                'style_setting_sec' => apply_filters('mep_settings_styling_arr',array(
                    array(
                        'name' => 'mep_base_color',
                        'label' => __('Base Color', 'mage-eventpress'),
                        'desc' => __('Select a Basic Color, It will chanage the icon background color, border color', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                    array(
                        'name' => 'mep_title_bg_color',
                        'label' => __('Label Background Color', 'mage-eventpress'),
                        'desc' => __('Select a Color Label Background', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                    array(
                        'name' => 'mep_title_text_color',
                        'label' => __('Label Text Color', 'mage-eventpress'),
                        'desc' => __('Select a Color Label Text', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                    array(
                        'name' => 'mep_cart_btn_bg_color',
                        'label' => __('Cart Button Background Color', 'mage-eventpress'),
                        'desc' => __('Select a color for Cart Button Background', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                    array(
                        'name' => 'mep_cart_btn_text_color',
                        'label' => __('Cart Button Text Color', 'mage-eventpress'),
                        'desc' => __('Select a color for Cart Button Text', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                    array(
                        'name' => 'mep_calender_btn_bg_color',
                        'label' => __('Calender Button Background Color', 'mage-eventpress'),
                        'desc' => __('Select a color for Calender Button Background', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                    array(
                        'name' => 'mep_calender_btn_text_color',
                        'label' => __('Calender Button Text Color', 'mage-eventpress'),
                        'desc' => __('Select a color for Calender Button Text', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                    array(
                        'name' => 'mep_faq_title_bg_color',
                        'label' => __('FAQ Title Background Color', 'mage-eventpress'),
                        'desc' => __('Select a color for FAQ title Background', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                    array(
                        'name' => 'mep_faq_title_text_color',
                        'label' => __('FAQ Title Text Color', 'mage-eventpress'),
                        'desc' => __('Select a color for FAQ Title Text', 'mage-eventpress'),
                        'type' => 'color',
                    ),
                )

                )
            );

            return apply_filters('mep_settings_sec_fields', $settings_fields);
        }

        function plugin_page()
        {
            echo '<div class="wrap mage_settings_panel_wrap">';

            $this->settings_api->show_navigation();
            $this->settings_api->show_forms();

            echo '</div>';
        }

        /**
         * Get all the pages
         *
         * @return array page names with key value pairs
         */
        function get_pages()
        {
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


function mep_get_option($option, $section, $default = '')
{
    $options = get_option($section);

    if (isset($options[$option])) {
        return $options[$option];
    }

    return $default;
}
