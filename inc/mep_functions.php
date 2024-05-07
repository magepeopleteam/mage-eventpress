<?php
if (!defined('ABSPATH')) {
    die;
}

appsero_init_tracker_mage_eventpress();



define( 'MEP_URL', plugin_dir_url( __DIR__ ) );
define( 'MEP_PATH', plugin_dir_path(__DIR__ ) );


function mep_get_page_by_slug( $page_slug, $output = OBJECT, $post_type = 'page' ) {
	global $wpdb;

	if ( is_array( $post_type ) ) {
		$post_type = esc_sql( $post_type );
		$post_type_in_string = "'" . implode( "','", $post_type ) . "'";
		$sql = $wpdb->prepare( "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_name = %s
			AND post_type IN ($post_type_in_string)
		", $page_slug );
	} else {
		$sql = $wpdb->prepare( "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_name = %s
			AND post_type = %s
		", $page_slug, $post_type );
	}

	$page = $wpdb->get_var( $sql );

	if ( $page )
		return get_post( $page, $output );

	return null;
}


function mep_add_event_into_feed_request($qv) {
    if (isset($qv['feed']) && !isset($qv['post_type']))
        $qv['post_type'] = array('mep_events');
    return $qv;
}
add_filter('request', 'mep_add_event_into_feed_request');

if (!function_exists('mepfix_sitemap_exclude_post_type')) {
function mepfix_sitemap_exclude_post_type() {
    return ['auto-draft'];
}
}

if (!function_exists('mep_get_builder_version')) {
    function mep_get_builder_version() {        
            return '3.8.1';        
    }
}

if (!function_exists('mep_check_builder_status')) {
    function mep_check_builder_status() {
        $version = '3.2';     
        return true;        
    }
}


if (!function_exists('mep_get_all_tax_list')) {
    function mep_get_all_tax_list($current_tax = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_tax_rate_classes';
        $result = $wpdb->get_results("SELECT * FROM $table_name");

        foreach ($result as $tax) {
            ?>
            <option value="<?php echo esc_attr($tax->slug); ?>" <?php if ($current_tax == $tax->slug) {
                echo 'Selected';
            } ?>><?php echo esc_html($tax->name); ?></option>
            <?php
        }
    }
}


// Class for Linking with Woocommerce with Event Pricing
add_action('plugins_loaded', 'mep_load_wc_class');
if (!function_exists('mep_load_wc_class')) {
    function mep_load_wc_class() {

        if (class_exists('WC_Product_Data_Store_CPT')) {

            class MEP_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {

                public function read(&$product) {
                    $product->set_defaults();
                    if (!$product->get_id() || !($post_object = get_post($product->get_id())) || !in_array($post_object->post_type, array('mep_events', 'product'))) { // change birds with your post type
                        throw new Exception(__('Invalid product.', 'woocommerce'));
                    }

                    $id = $product->get_id();

                    $product->set_props(array(
                        'name'              => $post_object->post_title,
                        'slug'              => $post_object->post_name,
                        'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp($post_object->post_date_gmt) : null,
                        'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp($post_object->post_modified_gmt) : null,
                        'product_id'        => $post_object->ID,
                        'sku'               => $post_object->ID,
                        'status'            => $post_object->post_status,
                        'description'       => $post_object->post_content,
                        'short_description' => $post_object->post_excerpt,
                        'parent_id'         => $post_object->post_parent,
                        'menu_order'        => $post_object->menu_order,
                        'reviews_allowed'   => 'open' === $post_object->comment_status,
                    ));

                    $this->read_attributes($product);
                    $this->read_downloads($product);
                    $this->read_visibility($product);
                    $this->read_product_data($product);
                    $this->read_extra_data($product);
                    $product->set_object_read(true);
                }

                /**
                 * Get the product type based on product ID.
                 *
                 * @param int $product_id
                 *
                 * @return bool|string
                 * @since 3.0.0
                 */
                public function get_product_type($product_id) {
                    $post_type = get_post_type($product_id);
                    if ('product_variation' === $post_type) {
                        return 'variation';
                    } elseif (in_array($post_type, array('mep_events', 'product'))) { // change birds with your post type
                        $terms = get_the_terms($product_id, 'product_type');
                        return !empty($terms) ? sanitize_title(current($terms)->name) : 'simple';
                    } else {
                        return false;
                    }
                }
            }
        }
    }
}


add_action('woocommerce_before_checkout_form', 'mep_displays_cart_products_feature_image');
if (!function_exists('mep_displays_cart_products_feature_image')) {
    function mep_displays_cart_products_feature_image() {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $item = $cart_item['data'];
        }
    }
}

if (!function_exists('mep_get_attendee_info_query')) {
    function mep_get_attendee_info_query($event_id, $order_id) {

        $_user_set_status   = apply_filters('mep_event_seat_reduce_status',mep_get_option('seat_reserved_order_status', 'general_setting_sec', array('processing','completed')));
        $_order_status      = !empty($_user_set_status) ? $_user_set_status : array('processing','completed');
        $order_status       = array_values($_order_status);

        $order_status_filter =      array(
            'key' => 'ea_order_status',
            'value' => $order_status,
            'compare' => 'OR'
        );

        $args = array(
            'post_type' => 'mep_events_attendees',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'ea_event_id',
                        'value' => $event_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'ea_order_id',
                        'value' => $order_id,
                        'compare' => '='
                    )
                ),
                $order_status_filter
            )
        );
        $loop = new WP_Query($args);
        return $loop;
    }
}

if (!function_exists('mep_email_dynamic_content')) {
    function mep_email_dynamic_content($email_body, $event_id, $order_id, $__attendee_id = 0) {
        $event_name = get_the_title($event_id);

        $attendee_q = mep_get_attendee_info_query($event_id, $order_id);
        foreach ($attendee_q->posts as $_attendee_q) {
            $_attendee_id = $_attendee_q->ID;
        }
        $attendee_id = $__attendee_id > 0 ? $__attendee_id : $_attendee_id;
        $attendee_name = get_post_meta($attendee_id, 'ea_name', true) ? get_post_meta($attendee_id, 'ea_name', true) : '';
        $email = get_post_meta($attendee_id, 'ea_email', true) ? get_post_meta($attendee_id, 'ea_email', true) : '';
        $date_time = get_post_meta($attendee_id, 'ea_event_date', true) ? get_mep_datetime(get_post_meta($attendee_id, 'ea_event_date', true), 'date-time-text') : '';
        $date = get_post_meta($attendee_id, 'ea_event_date', true) ? get_mep_datetime(get_post_meta($attendee_id, 'ea_event_date', true), 'date-text') : '';
        $time = get_post_meta($attendee_id, 'ea_event_date', true) ? get_mep_datetime(get_post_meta($attendee_id, 'ea_event_date', true), 'time') : '';
        $ticket_type = get_post_meta($attendee_id, 'ea_ticket_type', true) ? get_post_meta($attendee_id, 'ea_ticket_type', true) : '';

        $email_body = str_replace("{name}", $attendee_name, $email_body);
        $email_body = str_replace("{email}", $email, $email_body);
        $email_body = str_replace("{event}", $event_name, $email_body);
        $email_body = str_replace("{event_date}", $date, $email_body);
        $email_body = str_replace("{event_time}", $time, $email_body);
        $email_body = str_replace("{event_datetime}", $date_time, $email_body);
        $email_body = str_replace("{ticket_type}", $ticket_type, $email_body);
        return $email_body;
    }
}

// Send Confirmation email to customer
if (!function_exists('mep_event_confirmation_email_sent')) {
    function mep_event_confirmation_email_sent($event_id, $sent_email, $order_id, $attendee_id=0) {
        $values = get_post_custom($event_id);

        $global_email_text          = mep_get_option('mep_confirmation_email_text', 'email_setting_sec', '');
        $global_email_form_email    = mep_get_option('mep_email_form_email', 'email_setting_sec', '');
        $global_email_form          = mep_get_option('mep_email_form_name', 'email_setting_sec', '');
        $global_email_sub           = mep_get_option('mep_email_subject', 'email_setting_sec', '');
        $event_email_text           = $values['mep_event_cc_email_text'][0];
        $admin_email                = get_option('admin_email');
        $site_name                  = get_option('blogname');


        if ($global_email_sub) {
            $email_sub = $global_email_sub;
        } else {
            $email_sub = 'Confirmation Email';
        }

        if ($global_email_form) {
            $form_name = $global_email_form;
        } else {
            $form_name = $site_name;
        }

        if ($global_email_form_email) {
            $form_email = $global_email_form_email;
        } else {
            $form_email = $admin_email;
        }

        if ($event_email_text) {
            $email_body = $event_email_text;
        } else {
            $email_body = $global_email_text;
        }

        $headers[] = "From: $form_name <$form_email>";

        if ($email_body) {
            $email_body = mep_email_dynamic_content($email_body, $event_id, $order_id, $attendee_id);
            $confirmation_email_text = apply_filters('mep_event_confirmation_text', $email_body, $event_id, $order_id);
            wp_mail($sent_email, $email_sub, nl2br($confirmation_email_text), $headers);
        }
    }
}


if (!function_exists('mep_event_get_order_meta')) {
    function mep_event_get_order_meta($item_id, $key) {
        global $wpdb;
        $table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE order_item_id = %d AND meta_key = %s", $item_id, $key));


        foreach ($results as $result) {
            $value = $result->meta_value;
        }
        $val = isset($value) ? $value : '';
        return $val;
    }
}


if (!function_exists('mep_event_get_event_city_list')) {
    function mep_event_get_event_city_list() {
        global $wpdb;
        $table_name = $wpdb->prefix . "postmeta";
        $sql = "SELECT meta_value FROM $table_name WHERE meta_key ='mep_city' GROUP BY meta_value";
        $results = $wpdb->get_results($sql); //or die(mysql_error());
        ob_start();
        ?>
        <div class='mep-city-list'>
            <ul>
                <?php
                foreach ($results as $result) {
                    ?>
                    <li><a href='<?php echo get_site_url(); ?>/event-by-city-name/<?php echo esc_attr($result->meta_value); ?>/'><?php echo esc_html($result->meta_value); ?></a></li>
                    <?php
                }
                ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }
}


// Function to get page slug
if (!function_exists('mep_get_page_by_slug')) {
    function mep_get_page_by_slug($slug) {
        if ($pages = get_pages()) {
            foreach ($pages as $page) {
                if ($slug === $page->post_name) {
                    return $page;
                }
            }
        }
        return false;
    }
}

if (!function_exists('mep_page_create')) {
    function mep_page_create() {

        if (!mep_get_page_by_slug('event-by-city-name')) {
            $mep_search_page = array(
                'post_type' => 'page',
                'post_name' => 'event-by-city-name',
                'post_title' => 'Event By City',
                'post_content' => '',
                'post_status' => 'publish',
            );

            wp_insert_post($mep_search_page);
        }

    }
}

if (!function_exists('mep_city_filter_rewrite_rule')) {
    function mep_city_filter_rewrite_rule() {
        add_rewrite_rule(
            '^event-by-city-name/(.+)/?$',
            'index.php?cityname=$matches[1]&pagename=event-by-city-name',
            'top'
        );
    }
}
add_action('init', 'mep_city_filter_rewrite_rule');


if (!function_exists('mep_city_filter_query_var')) {
    function mep_city_filter_query_var($vars) {
        $vars[] = 'cityname';
        return $vars;
    }
}
add_filter('query_vars', 'mep_city_filter_query_var');


if (!function_exists('mep_city_template_chooser')) {
    function mep_city_template_chooser($template) {
        if (get_query_var('cityname')) {
            $template = mep_template_file_path('page-city-filter.php');
        }
        return $template;
    }
}
add_filter('template_include', 'mep_city_template_chooser');

if (!function_exists('mep_get_event_ticket_price_by_name')) {
function mep_get_event_ticket_price_by_name($event, $type) {
    $ticket_type = get_post_meta($event, 'mep_event_ticket_type', true);
    if (sizeof($ticket_type) > 0) {
        foreach ($ticket_type as $key => $val) {
            if ($val['option_name_t'] === $type) {
                return array_key_exists('option_price_t',$val) ? $val['option_price_t'] : 0;
            }
        }
        return 0;
    }
}
}

add_filter( 'archive_template', 'mep_load_default_event_archive_template');
if (!function_exists('mep_load_default_event_archive_template')) {
function mep_load_default_event_archive_template($template){
    if ( is_post_type_archive ( 'mep_events' ) ) {    
      $template = mep_template_file_path('event-archive.php');
    }
    return $template;
  }
}

if (!function_exists('mep_get_ticket_price_by_event')) {
function mep_get_ticket_price_by_event($event, $type, $default_price = 0) {
    $ticket_type = get_post_meta($event, 'mep_event_ticket_type', true);
    if ($ticket_type) {
        $all_ticket_tyle = get_post_meta($event, 'mep_event_ticket_type', true);
        foreach ($all_ticket_tyle as $key => $val) {
            if ($val['option_name_t'] === $type) {
                return array_key_exists('option_price_t',$val) ? (int)$val['option_price_t'] : 0;
            }
        }
    } else {
        return $default_price;
    }
}
}

if (!function_exists('mep_attendee_create')) {
    function mep_attendee_create($type, $order_id, $event_id, $_user_info = array()) {

        // Getting an instance of the order object
        $order              = wc_get_order($order_id);
        $order_meta         = get_post_meta($order_id);
        $order_status       = $order->get_status();



        // Customer billing information details
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name  = $order->get_billing_last_name();
        $billing_company    = $order->get_billing_company();
        $billing_address_1  = $order->get_billing_address_1();
        $billing_address_2  = $order->get_billing_address_2();
        $billing_city       = $order->get_billing_city();
        $billing_state      = $order->get_billing_state();
        $billing_postcode   = $order->get_billing_postcode();
        $billing_country    = $order->get_billing_country();
        $payment_method     = $order->get_payment_method_title();
        $customer_id        = $order->get_customer_id();
        // Get the WP_User Object instance
        $user               = $order->get_user();

        // Get the WP_User roles and capabilities
        $user_roles         = $user->roles;

        // Get the Customer billing email
        $billing_email      = $order->get_billing_email();

        // Get the Customer billing phone
        $billing_phone      = $order->get_billing_phone();


       
        $payment_method     = !empty($payment_method) ? sanitize_text_field($payment_method) : '';
        $user_id            = isset($customer_id) ? sanitize_text_field($customer_id) : '';

        $first_name         = isset($billing_first_name) ? sanitize_text_field($billing_first_name) : '';
        $last_name          = isset($billing_last_name) ? sanitize_text_field($billing_last_name) : '';
        $billing_full_name  = $first_name . ' ' . $last_name;





        if ($type == 'billing') {
            // Billing Information

            $company            = isset($billing_company) ? sanitize_text_field($billing_company) : '';
            $address_1          = isset($billing_address_1) ? sanitize_text_field($billing_address_1) : '';
            $address_2          = isset($billing_address_2) ? sanitize_text_field($billing_address_2) : '';
            $address            = $address_1 . ' ' . $address_2;
            $gender             = '';
            $designation        = '';
            $website            = '';
            $vegetarian         = '';
            $tshirtsize         = '';
            $city               = isset($billing_city) ? sanitize_text_field($billing_city) : '';
            $state              = isset($billing_state) ? sanitize_text_field($billing_state) : '';
            $postcode           = isset($billing_postcode) ? sanitize_text_field($billing_postcode) : '';
            $country            = isset($billing_country) ? sanitize_text_field($billing_country) : '';
            $email              = isset($billing_email ) ? sanitize_text_field($billing_email ) : '';
            $phone              = isset($billing_phone) ? sanitize_text_field($billing_phone) : '';
            $ticket_type        = stripslashes(sanitize_text_field($_user_info['ticket_name']));
            $event_date         = sanitize_text_field($_user_info['event_date']);
            $ticket_qty         = sanitize_text_field($_user_info['ticket_qty']);

        } elseif ($type == 'user_form') {

            $_uname             = array_key_exists('user_name',$_user_info) ? sanitize_text_field($_user_info['user_name']) : "";
            $email              = array_key_exists('user_email',$_user_info) ? sanitize_text_field($_user_info['user_email']) : "";
            $phone              = array_key_exists('user_phone',$_user_info) ? sanitize_text_field($_user_info['user_phone']) : "";
            $address            = array_key_exists('user_address',$_user_info) ? sanitize_text_field($_user_info['user_address']) : "";
            $gender             = array_key_exists('user_gender',$_user_info) ? sanitize_text_field($_user_info['user_gender']) : "";
            $company            = array_key_exists('user_company',$_user_info) ? sanitize_text_field($_user_info['user_company']) : "";
            $designation        = array_key_exists('user_designation',$_user_info) ? sanitize_text_field($_user_info['user_designation']) : "";
            $website            = array_key_exists('user_website',$_user_info) ? sanitize_text_field($_user_info['user_website']) : "";
            $vegetarian         = array_key_exists('user_vegetarian',$_user_info) ? sanitize_text_field($_user_info['user_vegetarian']) : "";
            $tshirtsize         = array_key_exists('user_tshirtsize',$_user_info) ? sanitize_text_field($_user_info['user_tshirtsize']) : "";
            $ticket_type        = array_key_exists('user_ticket_type',$_user_info) ? stripslashes($_user_info['user_ticket_type']) : "";
            $ticket_qty         = array_key_exists('user_ticket_qty',$_user_info) ? sanitize_text_field($_user_info['user_ticket_qty']) : "";
            $event_date         = array_key_exists('user_event_date',$_user_info) ? sanitize_text_field($_user_info['user_event_date']) : "";
            $event_id           = $_user_info['user_event_id'] ? sanitize_text_field($_user_info['user_event_id']) : $event_id;
            $mep_ucf            = isset($_user_info['mep_ucf']) ? sanitize_text_field($_user_info['mep_ucf']) : "";

        }

        $ticket_total_price = (int) (mep_get_event_ticket_price_by_name($event_id, $ticket_type) * (int) $ticket_qty);
        $uname = isset($_uname) && !empty($_uname) ? $_uname : $billing_full_name;
        
        $new_post = array(
            'post_title' => $uname,
            'post_content' => '',
            'post_category' => array(),  // Usable for custom taxonomies too
            'tags_input' => array(),
            'post_status' => 'publish', // Choose: publish, preview, future, draft, etc.
            'post_type' => 'mep_events_attendees'  //'post',page' or use a custom post type if you want to
        );

        //SAVE THE POST
        $pid = wp_insert_post($new_post);
        $pin = $user_id . $order_id . $event_id . $pid;
        update_post_meta($pid, 'ea_name', $uname);
        update_post_meta($pid, 'ea_address_1', $address);
        update_post_meta($pid, 'ea_email', $email);
        update_post_meta($pid, 'ea_phone', $phone);
        update_post_meta($pid, 'ea_gender', $gender);
        update_post_meta($pid, 'ea_company', $company);
        update_post_meta($pid, 'ea_desg', $designation);
        update_post_meta($pid, 'ea_website', $website);
        update_post_meta($pid, 'ea_vegetarian', $vegetarian);
        update_post_meta($pid, 'ea_tshirtsize', $tshirtsize);
        update_post_meta($pid, 'ea_ticket_type', $ticket_type);
        update_post_meta($pid, 'ea_ticket_qty', $ticket_qty);
        update_post_meta($pid, 'ea_ticket_price', mep_get_ticket_price_by_event($event_id, $ticket_type, 0));
        update_post_meta($pid, 'ea_ticket_order_amount', $ticket_total_price);
        update_post_meta($order_id, 'ea_ticket_qty', $ticket_qty);
        update_post_meta($order_id, 'ea_ticket_type', $ticket_type);
        update_post_meta($order_id, 'ea_event_id', $event_id);
        update_post_meta($pid, 'ea_payment_method', $payment_method);
        update_post_meta($pid, 'ea_event_name', get_the_title($event_id));
        update_post_meta($pid, 'ea_event_id', $event_id);
        update_post_meta($pid, 'ea_order_id', $order_id);
        update_post_meta($pid, 'ea_user_id', $user_id);
        update_post_meta($pid, 'mep_checkin', 'No');
        update_post_meta($order_id, 'ea_user_id', $user_id);
        update_post_meta($order_id, 'order_type_name', 'mep_events');
        update_post_meta($pid, 'ea_ticket_no', $pin);
        update_post_meta($pid, 'ea_event_date', $event_date);
        // update_post_meta($pid, 'ea_order_status', $order_status);
        update_post_meta($pid, 'ea_flag', 'checkout_processed');
        update_post_meta($order_id, 'ea_order_status', $order_status);

        $hooking_data = apply_filters('mep_event_attendee_dynamic_data', array(), $pid, $type, $order_id, $event_id, $_user_info);

        if (is_array($hooking_data) && sizeof($hooking_data) > 0) {
            foreach ($hooking_data as $_data) {
                update_post_meta($pid, $_data['name'], $_data['value']);
            }
        }

        // Checking if the form builder addon is active and have any custom fields
        $reg_form_id = mep_fb_get_reg_form_id($event_id);
        $mep_form_builder_data = get_post_meta($reg_form_id, 'mep_form_builder_data', true) ? get_post_meta($reg_form_id, 'mep_form_builder_data', true) : [];
        if (sizeof($mep_form_builder_data) > 0) {
            foreach ($mep_form_builder_data as $_field) {
                update_post_meta($pid, "ea_" . $_field['mep_fbc_id'], $_user_info[$_field['mep_fbc_id']]);
			    do_action('mep_attendee_upload_file_save',$event_id,$_user_info,$_field);
            }
        } // End User Form builder data update loop

    }
}


if (!function_exists('mep_attendee_extra_service_create')) {
    function mep_attendee_extra_service_create($order_id, $event_id, $_event_extra_service) {

        $order = wc_get_order($order_id);
        $order_meta = get_post_meta($order_id);
        $order_status = $order->get_status();
        if (is_array($_event_extra_service) && sizeof($_event_extra_service) > 0) {

            foreach ($_event_extra_service as $extra_serive) {
                if ($extra_serive['service_name']) {
                    $uname = 'Extra Service for ' . get_the_title($event_id) . ' Order #' . $order_id;
                    $new_post = array(
                        'post_title' => $uname,
                        'post_content' => '',
                        'post_category' => array(),
                        'tags_input' => array(),
                        'post_status' => 'publish',
                        'post_type' => 'mep_extra_service'
                    );

                    $pid = wp_insert_post($new_post);

                    update_post_meta($pid, 'ea_extra_service_name', $extra_serive['service_name']);
                    update_post_meta($pid, 'ea_extra_service_qty', $extra_serive['service_qty']);
                    update_post_meta($pid, 'ea_extra_service_unit_price', $extra_serive['service_price']);
                    update_post_meta($pid, 'ea_extra_service_total_price', $extra_serive['service_qty'] * (float) $extra_serive['service_price']);
                    update_post_meta($pid, 'ea_extra_service_event', $event_id);
                    update_post_meta($pid, 'ea_extra_service_order', $order_id);
                    update_post_meta($pid, 'ea_extra_service_order_status', $order_status);
                    update_post_meta($pid, 'ea_extra_service_event_date', $extra_serive['event_date']);
                }
            }
        }
    }
}

if (!function_exists('mep_check_attendee_exist_before_create')) {
function mep_check_attendee_exist_before_create($order_id, $event_id, $date ='') {
    $date_filter = !empty($date) ? array(
        'key'       => 'ea_event_date',
        'value'     => $date,
        'compare'   => 'LIKE'
    ) : '';
    $pending_status_filter = array(
        'key' => 'ea_order_status',
        'value' => 'pending',
        'compare' => '='
    );

    $hold_status_filter = array(
        'key' => 'ea_order_status',
        'value' => 'on-hold',
        'compare' => '='
    );

    $processing_status_filter = array(
        'key' => 'ea_order_status',
        'value' => 'processing',
        'compare' => '='
    );
    $completed_status_filter = array(
        'key' => 'ea_order_status',
        'value' => 'completed',
        'compare' => '='
    );

    $args = array(
        'post_type' => 'mep_events_attendees',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'relation' => 'AND',
                array(
                    'key' => 'ea_event_id',
                    'value' => $event_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'ea_order_id',
                    'value' => $order_id,
                    'compare' => '='
                ),
                $date_filter
            ),
            array(
                'relation' => 'OR',
                $pending_status_filter,
                $hold_status_filter,
                $processing_status_filter,
                $completed_status_filter
            )
        )
    );
    $loop = new WP_Query($args);
    return $loop->post_count;
}
}



// add_filter('mep_check_product_into_cart', 'mep_beta_disable_add_to_cart_if_product_is_in_cart', 90, 2);
function mep_beta_disable_add_to_cart_if_product_is_in_cart($is_purchasable, $product){
    $all_multiple_cart        = mep_get_option('mep_allow_multiple_add_cart_event', 'general_setting_sec', 'no');
    if($all_multiple_cart == 'yes'){
        return true;
    }
    
}

// add_action('init','mme_dbg');
// function mme_dbg(){
//     $order              = wc_get_order(752);
//     echo '<pre>';
//     print_r($order->get_items());
//     echo '</pre>';
//     die();
// }

  add_action('woocommerce_checkout_order_processed', 'mep_event_booking_management', 90);
//   add_action('__experimental_woocommerce_blocks_checkout_order_processed', 'mep_event_booking_management', 90); 
  add_action('woocommerce_store_api_checkout_order_processed', 'mep_event_booking_management', 90);
  if (!function_exists('mep_event_booking_management')) {
  function mep_event_booking_management( $order_id) {
    global $woocommerce;

    $result         = !is_numeric($order_id) ? json_decode($order_id) : [0];
    $order_id       = !is_numeric($order_id) ? $result->id : $order_id;

  if ( ! $order_id )
    {return;}
  
  // Getting an instance of the order object
  $order              = wc_get_order( $order_id );
  $order_meta         = get_post_meta($order_id); 
  $order_status       = $order->get_status();






  if($order_status != 'failed'){

  $form_position = mep_get_option( 'mep_user_form_position', 'general_attendee_sec', 'details_page' );
  
  if($form_position=='checkout_page'){
  
    foreach ( $order->get_items() as $item_id => $item_values ) {
      $item_id                    = $item_id;
    }
    $event_id                     = wc_get_order_item_meta($item_id,'event_id',true);
    if (get_post_type($event_id)  == 'mep_events') { 
            
      $event_name               = get_the_title($event_id);
      $user_info_arr            = wc_get_order_item_meta($item_id,'_event_user_info',true);
      $service_info_arr         = wc_get_order_item_meta($item_id,'_event_service_info',true);
      $event_ticket_info_arr    = wc_get_order_item_meta($item_id,'_event_ticket_info',true);
      $item_quantity            = 0;
      $check_before_create      = mep_check_attendee_exist_before_create($order_id,$event_id);
      mep_delete_attandee_of_an_order($order_id, $event_id);
      foreach ( $event_ticket_info_arr as $field ) {
        if($field['ticket_qty']>0){
            $item_quantity = $item_quantity + $field['ticket_qty'];
        }
      } 
      if(is_array($user_info_arr) & sizeof($user_info_arr) > 0){
        foreach ($user_info_arr as $_user_info) {
        //   if($check_before_create < count($user_info_arr)){
             mep_attendee_create('user_form',$order_id,$event_id,$_user_info);
        //   } 
        } 
      }else{
          foreach($event_ticket_info_arr as $tinfo){
            for ($x = 1; $x <= $tinfo['ticket_qty']; $x++) {

            //   if($check_before_create < count($event_ticket_info_arr)){
                mep_attendee_create('billing',$order_id,$event_id,$tinfo);
            //   }
            } 
          }
      }  
    }
  
  }else{

    foreach ( $order->get_items() as $item_id => $item_values ) {
      $item_id                    = $item_id;
      $event_id                   = wc_get_order_item_meta($item_id,'event_id',true);

        if (get_post_type($event_id) == 'mep_events') { 
          $event_name             = get_the_title($event_id);
          $user_info_arr          = wc_get_order_item_meta($item_id,'_event_user_info',true);
          $service_info_arr       = wc_get_order_item_meta($item_id,'_event_service_info',true);
          $event_ticket_info_arr  = wc_get_order_item_meta($item_id,'_event_ticket_info',true);
          $_event_extra_service   = wc_get_order_item_meta($item_id,'_event_extra_service',true);
          $item_quantity          = 0;
          $check_before_create      = mep_check_attendee_exist_before_create($order_id,$event_id);
          mep_attendee_extra_service_create($order_id,$event_id,$_event_extra_service);

          mep_delete_attandee_of_an_order($order_id, $event_id);

    // print_r($order->get_items());
    // die();

          foreach ( $event_ticket_info_arr as $field ) {
            if($field['ticket_qty']>0){
                $item_quantity    = $item_quantity + $field['ticket_qty'];
            }
          } 
          if(is_array($user_info_arr) & sizeof($user_info_arr) > 0){
            foreach ($user_info_arr as $_user_info) {
                $check_before_create_date      = mep_check_attendee_exist_before_create($order_id,$event_id, $_user_info['user_event_date']);
           
                if(function_exists('mep_re_language_load')){               
               
                    mep_attendee_create('user_form',$order_id,$event_id,$_user_info);

                }else{

                  if($check_before_create < count($user_info_arr)){
                        if($check_before_create_date == 0){
                            mep_attendee_create('user_form',$order_id,$event_id,$_user_info);
                        }
                    }

                }


            } 
          }else{
              foreach($event_ticket_info_arr as $tinfo){
                for ($x = 1; $x <= $tinfo['ticket_qty']; $x++) {


                    
                    $check_before_create_date      = mep_check_attendee_exist_before_create($order_id,$event_id, $tinfo['event_date']);
               
                    if(function_exists('mep_re_language_load')){               
               
                        mep_attendee_create('billing',$order_id,$event_id,$tinfo);

                    }else{
                        if($check_before_create < count($event_ticket_info_arr)){
                            if($check_before_create_date == 0){
                                mep_attendee_create('billing',$order_id,$event_id,$tinfo);
                            }
                        }
                    }


                  
                } 
              }
          }  

          $enable_clear_cart = mep_get_option('mep_clear_cart_after_checkout', 'general_setting_sec','enable');

          if($enable_clear_cart == 'enable'){
              //   PayplugWoocommerce
              if ( ! class_exists( 'Payplug\PayplugWoocommerce' ) ) { 
              if(!class_exists('WC_Xendit_CC')){		
                  if(!class_exists( 'PaysonCheckout_For_WooCommerce' )){		
                      if(!class_exists( 'RP_SUB' )){		
                          if(!class_exists( 'Afterpay_Plugin' )){ 
                              if(!class_exists( 'WC_Subscriptions' )){ 
                                  if ( !is_plugin_active( 'woo-juno/main.php' )){ 
                                      if ( ! class_exists( 'WC_Saferpay' ) ) { 
                                          // mep_clear_cart_after_checkout
                                          $woocommerce->cart->empty_cart(); 	
                                      }	
                                  }
                                  }	 
                              }	 
                          }		        								
                      }		        								
                  }
              }
          }

        } // end of check post type
    }
  }
  do_action('mep_after_event_booking',$order_id,$order->get_status());  
        
    }
  }
}


if (!function_exists('mep_diff_two_datetime')) {
    function mep_diff_two_datetime($d1,$d2){
        $timeFirst  = strtotime($d1);
        $timeSecond = strtotime($d2);
        return $differenceInSeconds = $timeSecond - $timeFirst;
    }
}

if (!function_exists('mep_delete_attandee_of_an_order')) {
function mep_delete_attandee_of_an_order($order_id, $event_id) {

    $args = array(
        'post_type' => array('mep_events_attendees'),
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'ea_order_id',
                'value' => $order_id,
                'compare' => '='
            ),
            array(
                'key' => 'ea_event_id',
                'value' => $event_id,
                'compare' => '='
            ),
            array(
                'key' => 'ea_flag',
                'value' => 'checkout_processed',
                'compare' => '='
            )
        )
    );
    $loop = new WP_Query($args);
    foreach ($loop->posts as $ticket) {
        $post_id = $ticket->ID;
        $post_date = get_the_date('Y-m-d H:i:s',$post_id);
        $time_diff = mep_diff_two_datetime($post_date,current_time('Y-m-d H:i:s'));
        if($time_diff > 15){
            wp_delete_post($post_id, true);
        }
    }
}
}


if (!function_exists('change_attandee_order_status')) {
    function change_attandee_order_status($order_id, $set_status, $post_status, $qr_status = null) {
        add_filter('wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5);

        $args = array(
            'post_type' => array('mep_events_attendees'),
            'posts_per_page' => -1,
            'post_status' => $post_status,
            'meta_query' => array(
                array(
                    'key' => 'ea_order_id',
                    'value' => $order_id,
                    'compare' => '='
                )
            )
        );
        $loop = new WP_Query($args);
        $tid = array();
        foreach ($loop->posts as $ticket) {
            $post_id = $ticket->ID;
            update_post_meta($post_id, 'ea_order_status', $qr_status);
            update_post_meta($post_id, 'ea_flag', $qr_status);
            $current_post = get_post($post_id, 'ARRAY_A');
            $current_post['post_status'] = $set_status;
            wp_update_post($current_post);
        }
    }
}


if (!function_exists('change_extra_service_status')) {
    function change_extra_service_status($order_id, $set_status, $post_status, $qr_status = null) {
        add_filter('wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5);
        $args = array(
            'post_type' => array('mep_extra_service'),
            'posts_per_page' => -1,
            'post_status' => $post_status,
            'meta_query' => array(
                array(
                    'key' => 'ea_extra_service_order',
                    'value' => $order_id,
                    'compare' => '='
                )
            )
        );
        $loop = new WP_Query($args);
        $tid = array();
        foreach ($loop->posts as $ticket) {
            $post_id = $ticket->ID;

            update_post_meta($post_id, 'ea_extra_service_order_status', $qr_status);

            $current_post = get_post($post_id, 'ARRAY_A');
            $current_post['post_status'] = $set_status;
            wp_update_post($current_post);
        }
    }
}


if (!function_exists('mep_change_wc_event_product_status')) {
    function mep_change_wc_event_product_status($order_id, $set_status, $post_status, $qr_status = null) {
        add_filter('wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5);
        $args = array(
            'post_type' => array('product'),
            'posts_per_page' => -1,
            'post_status' => $post_status,
            'meta_query' => array(
                array(
                    'key' => 'link_mep_event',
                    'value' => $order_id,
                    'compare' => '='
                )
            )
        );
        $loop = new WP_Query($args);
        $tid = array();
        foreach ($loop->posts as $ticket) {
            $post_id = $ticket->ID;
            if (!empty($qr_status)) {
                //update_post_meta($post_id, 'ea_order_status', $qr_status);
            }
            $current_post = get_post($post_id, 'ARRAY_A');
            $current_post['post_status'] = $set_status;
            wp_update_post($current_post);
        }
    }
}


add_action('wp_trash_post', 'mep_addendee_trash', 90);
if (!function_exists('mep_addendee_trash')) {
    function mep_addendee_trash($post_id) {
        $post_type = get_post_type($post_id);
        $post_status = get_post_status($post_id);

        if ($post_type == 'shop_order') {
            change_attandee_order_status($post_id, 'trash', 'publish', '');
            change_extra_service_status($post_id, 'trash', 'publish', '');
        }


        if ($post_type == 'mep_events') {
            mep_change_wc_event_product_status($post_id, 'trash', 'publish', '');
        }
    }
}

add_action('untrash_post', 'mep_addendee_untrash', 90);
if (!function_exists('mep_addendee_untrash')) {
    function mep_addendee_untrash($post_id) {
        $post_type = get_post_type($post_id);
        $post_status = get_post_status($post_id);
        if ($post_type == 'shop_order') {
            $order = wc_get_order($post_id);
            $order_status = $order->get_status();
            change_attandee_order_status($post_id, 'publish', 'trash', '');
            change_extra_service_status($post_id, 'publish', 'trash', '');
        }

        if ($post_type == 'mep_events') {
            mep_change_wc_event_product_status($post_id, 'publish', 'trash', '');
        }

    }
}


add_action('woocommerce_order_status_changed', 'mep_attendee_status_update', 10, 4);
if (!function_exists('mep_attendee_status_update')) {
    function mep_attendee_status_update($order_id, $from_status, $to_status, $order) {
   
        // Getting an instance of the order object
        $order              = wc_get_order($order_id);
        $order_meta         = get_post_meta($order_id);
        $email              = isset($order_meta['_billing_email'][0]) ? $order_meta['_billing_email'][0] : $order->get_billing_email();
        $email_send_status  = mep_get_option('mep_email_sending_order_status', 'email_setting_sec', array('disable_email' => 'disable_email'));
        $email_send_status  = !empty($email_send_status) ? $email_send_status : array('disable_email' => 'disable_email');
        $enable_billing_email   = mep_get_option('mep_send_confirmation_to_billing_email', 'email_setting_sec','enable'); 
        //  mep_email_sending_order_status
        $order_status = $order->get_status();

        $cn = 1;
        $event_arr = [];
        foreach ($order->get_items() as $item_id => $item_values) {
            $item_id = $item_id;
            $event_id = mep_event_get_order_meta($item_id, 'event_id');
            $event_arr[] = $event_id;


            if (get_post_type($event_id) == 'mep_events') {
                $event_ticket_info_arr    = wc_get_order_item_meta($item_id,'_event_ticket_info',true);

                $org        = get_the_terms($event_id, 'mep_org');
                $term_id    = isset($org[0]->term_id) ? $org[0]->term_id : '';
                $org_email  = get_term_meta( $term_id, 'org_email', true ) ? get_term_meta( $term_id, 'org_email', true ) : '';

                if ($order->has_status('processing')) {


                    change_attandee_order_status($order_id, 'publish', 'trash', 'processing');
                    change_attandee_order_status($order_id, 'publish', 'publish', 'processing');
                    change_extra_service_status($order_id, 'publish', 'trash', 'processing');
                    change_extra_service_status($order_id, 'publish', 'publish', 'processing');
                    do_action('mep_wc_order_status_change', $order_status, $event_id, $order_id);
                if($enable_billing_email == 'enable'){
                    
                    if (in_array('processing', $email_send_status)) {
                        mep_event_confirmation_email_sent($event_id, $email, $order_id);

                        if(!empty($org_email)){
                           // mep_event_confirmation_email_sent($event_id, $org_email, $order_id);
                        }
                    }
                }

                }
                if ($order->has_status('pending')) {
                    change_attandee_order_status($order_id, 'publish', 'trash', 'pending');
                    change_attandee_order_status($order_id, 'publish', 'publish', 'pending');
                    change_extra_service_status($order_id, 'publish', 'trash', 'pending');
                    change_extra_service_status($order_id, 'publish', 'publish', 'pending');
                    do_action('mep_wc_order_status_change', $order_status, $event_id, $order_id);
                }
                if ($order->has_status('on-hold')) {
                    change_attandee_order_status($order_id, 'publish', 'trash', 'on-hold');
                    change_attandee_order_status($order_id, 'publish', 'publish', 'on-hold');
                    do_action('mep_wc_order_status_change', $order_status, $event_id, $order_id);
                }
                if ($order->has_status('completed')) {


                    change_attandee_order_status($order_id, 'publish', 'trash', 'completed');
                    change_attandee_order_status($order_id, 'publish', 'publish', 'completed');
                    change_extra_service_status($order_id, 'publish', 'trash', 'completed');
                    change_extra_service_status($order_id, 'publish', 'publish', 'completed');
                    do_action('mep_wc_order_status_change', $order_status, $event_id, $order_id);

                    if (in_array('completed', $email_send_status)) {
                        mep_event_confirmation_email_sent($event_id, $email, $order_id);

                        if(!empty($org_email)){
                            mep_event_confirmation_email_sent($event_id, $org_email, $order_id);
                        }


                    }
                }
                if ($order->has_status('cancelled')) {
                    change_attandee_order_status($order_id, 'trash', 'publish', 'cancelled');
                    change_extra_service_status($order_id, 'trash', 'publish', 'cancelled');
                    do_action('mep_wc_order_status_change', $order_status, $event_id, $order_id);
                }
                if ($order->has_status('refunded')) {
                    change_attandee_order_status($order_id, 'trash', 'publish', 'refunded');
                    change_extra_service_status($order_id, 'trash', 'publish', 'refunded');
                    do_action('mep_wc_order_status_change', $order_status, $event_id, $order_id);
                }
                if ($order->has_status('failed')) {
                    change_attandee_order_status($order_id, 'trash', 'publish', 'failed');
                    change_extra_service_status($order_id, 'trash', 'publish', 'failed');
                    do_action('mep_wc_order_status_change', $order_status, $event_id, $order_id);
                }


               
                mep_update_event_seat_inventory($event_id,$event_ticket_info_arr);
              
            } // End of Post Type Check           
            $cn++;
        } // End order item foreach
        
        do_action('mep_wc_order_status_change_single', $order_status, $event_id, $order_id,$cn,$event_arr);
    } // End Function
}



function mep_update_ticket_type_seat($event_id,$ticket_type_name,$event_date,$total_quantity,$total_resv_quantity){
    $total_sold             = (int) mep_ticket_type_sold($event_id, $ticket_type_name, $event_date);
    // $ticket_type_left       = (int) $total_quantity - ((int) $total_sold + (int) $total_resv_quantity);
    $ticket_type_left       = (int) $total_sold;
    $_date                  = date('YmdHi',strtotime($event_date));
    $ticket_type_meta_name  = $ticket_type_name.'_'.$_date;
    update_post_meta($event_id,$ticket_type_meta_name,$ticket_type_left);
    return get_post_meta($event_id,$ticket_type_meta_name,true);
}

function mep_update_event_total_seat($event_id,$date=''){

    $seat_left  = mep_get_count_total_available_seat($event_id);
    update_post_meta($event_id,'mep_total_seat_left',$seat_left);

    if(!empty($date)){
		$_date       = !empty($date) ? date('YmdHi',strtotime($date)) : 0;
        $event_name  = $event_id.'_'.$_date;
        $seat_left_date  = mep_get_count_total_available_seat($event_id, $date);
        update_post_meta($event_id,$event_name,$seat_left_date);
    }

    $date       = !empty($date) ? date('YmdHi',strtotime($date)) : 0;
    $meta_name  = $date > 0 ? $event_id.'_'.$date : 'mep_total_seat_left';
    return get_post_meta($event_id,$meta_name,true);

}

function mep_update_total_seat_on_demand($event_id){
    $upcoming_date              = !empty(mep_get_event_upcoming_date($event_id)) ? mep_get_event_upcoming_date($event_id) : '';
    mep_update_event_total_seat($event_id,$upcoming_date);
}


function mep_get_event_total_seat_left($event_id,$date=''){
    $date       = !empty($date) ? date('YmdHi',strtotime($date)) : 0;
    $meta_name  = $date > 0 ? $event_id.'_'.$date : 'mep_total_seat_left';
    $availabe_seat          = !empty(get_post_meta($event_id,$meta_name,true)) ? get_post_meta($event_id,$meta_name,true) : mep_update_event_total_seat($event_id,$date);
    return $availabe_seat;
}


function mep_get_ticket_type_seat_count($event_id,$name,$date,$total,$reserved){

    $_date                  = date('YmdHi',strtotime($date));
    $ticket_type_meta_name  = $name.'_'.$_date;
    $availabe_seat          = !empty(get_post_meta($event_id,$ticket_type_meta_name,true)) ? get_post_meta($event_id,$ticket_type_meta_name,true) : mep_update_ticket_type_seat($event_id,$name,$date,$total,$reserved);
    // $availabe_seat          = mep_update_ticket_type_seat($event_id,$name,$date,$total,$reserved);
    return $availabe_seat;
}


if (!function_exists('mep_get_count_total_available_seat')) {
    function mep_get_count_total_available_seat($event_id, $date='') {
        $total_seat = mep_event_total_seat($event_id, 'total');
        $total_resv = mep_event_total_seat($event_id, 'resv');
        $total_sold = mep_ticket_type_sold($event_id, '', $date);
        // $total_left = $total_seat - ($total_sold + $total_resv);
        $total_left = $total_sold;
        return esc_html($total_left);
    }
  }
  if (!function_exists('mep_reset_event_booking')) {
    function mep_reset_event_booking($event_id) {
        add_filter('wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5);
        $mep_event_ticket_type = get_post_meta($event_id, 'mep_event_ticket_type', true);
       
        $date       = mep_get_event_upcoming_date($event_id);

        $args_search_qqq = array(
            'post_type' => array('mep_events_attendees'),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'ea_event_id',
                    'value' => $event_id,
                    'compare' => '='
                )
            )
        );
        $loop = new WP_Query($args_search_qqq);
        while ($loop->have_posts()) {
            $loop->the_post();
            $post_id = get_the_id();
            $status = 'trash';
            $current_post = get_post($post_id, 'ARRAY_A');
            $current_post['post_status'] = $status;
            wp_update_post($current_post);
        }

        if ($mep_event_ticket_type) {
            foreach ($mep_event_ticket_type as $field) {
                $name       = $field['option_name_t'];               
                mep_update_ticket_type_seat($event_id,$name,$date,0,0);
            }
           
        } 

        mep_update_event_total_seat($event_id,$date);
    }
}


function mep_update_event_seat_inventory($event_id,$ticket_array,$type='order'){
    
    $seat_left = mep_get_count_total_available_seat($event_id);
    
    foreach($ticket_array as $ticket){
        $name                  = $ticket['ticket_name'];
        $date                  = date('Y-m-d H:i',strtotime($ticket['event_date']));
        $_date                 = date('YmdHi',strtotime($date));
        $total_quantity        = (int) mep_get_ticket_type_info_by_name($name, $event_id);
        $total_resv_quantity   = (int) mep_get_ticket_type_info_by_name($name, $event_id,'option_rsv_t');
        $total_sold_type       = (int) mep_ticket_type_sold($event_id, $name, $date);
        $seat_left_date        = mep_get_count_total_available_seat($event_id, $date);
        // $ticket_type_left      = (int) $total_quantity - ((int) $total_sold_type + (int) $total_resv_quantity);
        $ticket_type_left      =  (int) $total_sold_type;

        $ticket_type_meta_name  = $name.'_'.$_date;
        $event_name  = $event_id.'_'.$_date;

        //  Update Total Seat Count
        update_post_meta($event_id,'mep_total_seat_left',$seat_left);

        // Update Ticket Type Seat Count
        update_post_meta($event_id,$ticket_type_meta_name,$ticket_type_left);

        // Update Total Event By Date Seat Count
        update_post_meta($event_id,$event_name,$seat_left_date);

        // mep_update_ticket_type_seat($event_id,$name,$date,$total_quantity,$total_resv_quantity);

    }

}

function mep_get_ticket_type_info_by_name($name, $event_id, $type ='option_qty_t') {
    $ticket_type_arr = get_post_meta($event_id, 'mep_event_ticket_type', true) ? get_post_meta($event_id, 'mep_event_ticket_type', true) : [];
    $p = '';
    foreach ($ticket_type_arr as $price) {
        $TicketName = str_replace("'", "", $price['option_name_t']);
        if ($TicketName === $name) {
            $p = array_key_exists($type,$price) ? $price[$type] : '';
        }
    }
    return $p;
}


add_action('restrict_manage_posts', 'mep_filter_post_type_by_taxonomy');
if (!function_exists('mep_filter_post_type_by_taxonomy')) {
    function mep_filter_post_type_by_taxonomy() {
        global $typenow;
        $post_type = 'mep_events'; // change to your post type
        $taxonomy = 'mep_cat'; // change to your taxonomy
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? mage_array_strip($_GET[$taxonomy]) : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => __("Show All {$info_taxonomy->label}"),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => true,
                'hide_empty' => true,
            ));
        };
    }
}


add_filter('parse_query', 'mep_convert_id_to_term_in_query');
if (!function_exists('mep_convert_id_to_term_in_query')) {
    function mep_convert_id_to_term_in_query($query) {
        global $pagenow;
        $post_type = 'mep_events'; // change to your post type
        $taxonomy = 'mep_cat'; // change to your taxonomy
        $q_vars = &$query->query_vars;

        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }

    }
}


add_filter('parse_query', 'mep_attendee_filter_query');
if (!function_exists('mep_attendee_filter_query')) {
    function mep_attendee_filter_query($query) {
        global $pagenow;
        $post_type = 'mep_events_attendees';
        $q_vars = &$query->query_vars;

        if ($pagenow == 'edit.php' && isset($_GET['post_type']) && mage_array_strip($_GET['post_type']) == $post_type && isset($_GET['meta_value']) && mage_array_strip($_GET['meta_value']) != 0) {

            $q_vars['meta_key'] = 'ea_event_id';
            $q_vars['meta_value'] = mage_array_strip($_GET['meta_value']);

        } elseif ($pagenow == 'edit.php' && isset($_GET['post_type']) && mage_array_strip($_GET['post_type']) == $post_type && isset($_GET['event_id']) && mage_array_strip($_GET['event_id']) != 0 && !isset($_GET['action'])) {

            $event_date = date('Y-m-d', strtotime(mage_array_strip($_GET['ea_event_date'])));
            $meta_query = array([
                'key' => 'ea_event_id',
                'value' => mage_array_strip($_GET['event_id']),
                'compare' => '='
            ], [
                'key' => 'ea_event_date',
                'value' => $event_date,
                'compare' => 'LIKE'
            ], [
                'key' => 'ea_order_status',
                'value' => 'completed',
                'compare' => '='
            ]);

            $query->set('meta_query', $meta_query);

        }
    }
}


// Add the data to the custom columns for the book post type:
add_action('manage_mep_events_posts_custom_column', 'mep_custom_event_column', 10, 2);
if (!function_exists('mep_custom_event_column')) {
    function mep_custom_event_column($column, $post_id) {
        
        mep_update_event_upcoming_date($post_id);
        $post_id = mep_get_default_lang_event_id($post_id);

        switch ($column) {
            case 'mep_status' :
                $values = get_post_custom($post_id);
                $recurring = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';
                if ($recurring == 'yes') {
                    $event_more_dates = get_post_meta($post_id, 'mep_event_more_date', true) ? get_post_meta($post_id, 'mep_event_more_date', true) : [];
                    $seat_left = 10;
                    $md = is_array($event_more_dates) ? end($event_more_dates) : [];
                    $more_date = is_array($md) && array_key_exists('event_more_start_date', $md) && !empty($md['event_more_start_date']) ? $md['event_more_start_date'] . ' ' . $md['event_more_start_time'] : '';
                    $event_date = !empty($more_date) ? date('Y-m-d H:i:s', strtotime($more_date)) : '';
                } else {
                    $event_expire_on_old = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
                    $event_expire_on = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
                    // $event_date = $values[$event_expire_on][0];
                    $event_date = !empty(get_post_meta($post_id,$event_expire_on,true)) ? get_post_meta($post_id,$event_expire_on,true) : '';
                }
                echo mep_get_event_status($event_date);
                break;


            case 'mep_event_date' :                
                echo "<span class='mep_event_date'>" . get_mep_datetime(get_post_meta($post_id, 'event_upcoming_datetime', true), 'date-time-text') . "</span>";
                break;
        }
    }
}

// Getting event exprie date & time
if (!function_exists('mep_get_event_status')) {
    function mep_get_event_status($startdatetime) {

        $current = current_time('Y-m-d H:i:s');
        $newformat = date('Y-m-d H:i:s', strtotime($startdatetime));

        $datetime1 = new DateTime($newformat);
        $datetime2 = new DateTime($current);

        $interval = date_diff($datetime2, $datetime1);

        if (current_time('Y-m-d H:i:s') > $newformat) {
            return __("<span class=err>Expired</span>","mage-eventpress");
        } else {
            $days = $interval->days;
            $hours = $interval->h;
            $minutes = $interval->i;
            if ($days > 0) {
                $dd = $days . __(" days ","mage-eventpress");
            } else {
                $dd = "";
            }
            if ($hours > 0) {
                $hh = $hours . __(" hours ","mage-eventpress");
            } else {
                $hh = "";
            }
            if ($minutes > 0) {
                $mm = $minutes . __(" minutes ","mage-eventpress");
            } else {
                $mm = "";
            }
            return "<span class='active'>".esc_html($dd)." ".esc_html($hh)." ".esc_html($mm)."</span>";
        }
    }
}

if (!function_exists('mep_merge_saved_array')) {
    function mep_merge_saved_array($arr1, $arr2) {
        $output = [];
        for ($i = 0; $i < count($arr1); $i++) {
            $output[$i] = array_merge($arr1[$i], $arr2[$i]);
        }
        return $output;
    }
}

// Redirect to Checkout after successfuly event registration
add_filter('woocommerce_add_to_cart_redirect', 'mep_event_redirect_to_checkout');
if (!function_exists('mep_event_redirect_to_checkout')) {
    function mep_event_redirect_to_checkout($wc_get_cart_url) {
        global $woocommerce;
        $redirect_status = mep_get_option('mep_event_direct_checkout', 'general_setting_sec', 'yes');
        if ($redirect_status == 'yes') {
            $wc_get_cart_url = wc_get_checkout_url();
        }
        return $wc_get_cart_url;
    }
}

add_action('init', 'mep_include_template_parts');
if (!function_exists('mep_include_template_parts')) {
    function mep_include_template_parts() {
        require_once(dirname(__DIR__) . "/inc/template-prts/templating.php");
    }
}


if (!function_exists('mep_template_file_path')) {
    function mep_template_file_path($file_name) {
        $template_path = get_stylesheet_directory() . '/mage-events/';
        $default_path = plugin_dir_path(__DIR__) . 'templates/';
        
        $thedir = is_dir($template_path) ? $template_path : $default_path;
        $themedir = $thedir . $file_name;
        $the_file_path = locate_template(array('mage-events/' . $file_name)) ? $themedir : $default_path . $file_name;
        return $the_file_path;
    }
}

if (!function_exists('mep_template_part_file_path')) {
    function mep_template_part_file_path($file_name) {
        $the_file_path = plugin_dir_path(__DIR__) . 'inc/template-prts/' . $file_name;
        return $the_file_path;
    }
}

if (!function_exists('mep_load_events_templates')) {
    function mep_load_events_templates($template) {
        global $post;

        if ($post->post_type == "mep_events") {
            $template = mep_template_part_file_path('single-events.php');
            return $template;
        }

        if ($post->post_type == "mep_event_speaker") {
            $template = mep_template_file_path('single-speaker.php');
            return $template;
        }

        if ($post->post_type == "mep_events_attendees") {
            $template = mep_template_part_file_path('single-mep_events_attendees.php');
            return $template;
        }

        return $template;
    }
}
add_filter('single_template', 'mep_load_events_templates');


add_filter('template_include', 'mep_organizer_set_template');
if (!function_exists('mep_organizer_set_template')) {
    function mep_organizer_set_template($template) {
        if (is_tax('mep_org')) {
            $template = mep_template_file_path('taxonomy-organozer.php');
        }
        if (is_tax('mep_cat')) {
            $template = mep_template_file_path('taxonomy-category.php');
        }
        return $template;
    }
}

if (!function_exists('mep_social_share')) {
    function mep_social_share() {
        $event_ss_fb_icon       = mep_get_option('mep_event_ss_fb_icon', 'icon_setting_sec', 'fab fa-facebook-f');
        $event_ss_twitter_icon  = mep_get_option('mep_event_ss_twitter_icon', 'icon_setting_sec', 'fab fa-twitter');    
        ?>
        <ul class='mep-social-share'>
            <?php do_action('mep_before_social_share_list', get_the_id()); ?>
            <li><a data-toggle="tooltip" title="" class="facebook" onclick="window.open('https://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="https://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" data-original-title="Share on Facebook"><i class="<?php echo $event_ss_fb_icon; ?>"></i></a></li>
            <li><a data-toggle="tooltip" title="" class="twitter" onclick="window.open('https://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title(); ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="https://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title(); ?>" data-original-title="Twittet it"><i class="<?php echo $event_ss_twitter_icon; ?>"></i></a></li>
            <?php do_action('mep_after_social_share_list', get_the_id()); ?>
        </ul>
        <?php
    }
}

if (!function_exists('mep_calender_date')) {
    function mep_calender_date($datetime) {
        $time = strtotime($datetime);
        $newdate = date_i18n('Ymd', $time);
        $newtime = date('Hi', $time);
        $newformat = $newdate . "T" . $newtime . "00";
        return $newformat;
    }
}


if (!function_exists('mep_add_to_google_calender_link')) {
    function mep_add_to_google_calender_link($pid) {
        $event      = get_post($pid);
        $event_meta = get_post_custom($pid);

        $start_date = !empty(get_post_meta($pid,'event_start_date',true)) ? get_post_meta($pid,'event_start_date',true) : "";
        $start_time = !empty(get_post_meta($pid,'event_start_time',true)) ? get_post_meta($pid,'event_start_time',true) : "";

        $end_date = !empty(get_post_meta($pid,'event_end_date',true)) ? get_post_meta($pid,'event_end_date',true) : "";
        $end_time = !empty(get_post_meta($pid,'event_end_time',true)) ? get_post_meta($pid,'event_end_time',true) : "";

        $venue = !empty(get_post_meta($pid,'mep_location_venue',true)) ? get_post_meta($pid,'mep_location_venue',true) : "";
        $street = !empty(get_post_meta($pid,'mep_street',true)) ? get_post_meta($pid,'mep_street',true) : "";
        $city = !empty(get_post_meta($pid,'mep_city',true)) ? get_post_meta($pid,'mep_city',true) : "";
        $state = !empty(get_post_meta($pid,'mep_state',true)) ? get_post_meta($pid,'mep_state',true) : "";
        $postcode = !empty(get_post_meta($pid,'mep_postcode',true)) ? get_post_meta($pid,'mep_postcode',true) : "";
        $country = !empty(get_post_meta($pid,'mep_country',true)) ? get_post_meta($pid,'mep_country',true) : "";

        $event_start = $start_date . ' ' . $start_time;
        $event_end = $end_date . ' ' . $end_time;
        $location = $venue . " " . $street . " " . $city . " " . $state . " " . $postcode . " " . $country;
        ob_start();
        require(mep_template_file_path('single/add_calendar.php'));
        ?>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery("#mep_add_calender_button").click(function () {
                    jQuery("#mep_add_calender_links").toggle()
                });
            });

        </script>
        <style type="text/css">
            #mep_add_calender_links { display: none;
                background: transparent;
                margin-top: -7px;
                list-style: navajowhite;
                margin: 0;
                padding: 0;}
            /*  #mep_add_calender_links li{list-style: none !important; line-height: 0.2px; border:1px solid #d5d5d5; border-radius: 10px; margin-bottom: 5px;}
    #mep_add_calender_links a{background: none !important; color: #333 !important; line-height: 0.5px !important; padding:10px; margin-bottom: 3px;}
    #mep_add_calender_links a:hover{color:#ffbe30;}*/
            #mep_add_calender_button {
                /*background: #ffbe30 none repeat scroll 0 0;*/
                border: 0 none;
                border-radius: 50px;
                /*color: #ffffff !important;*/
                display: inline-flex;
                font-size: 14px;
                font-weight: 600;
                overflow: hidden;
                padding: 15px 35px;
                position: relative;
                text-align: center;
                text-transform: uppercase;
                z-index: 1;
                cursor: pointer;
            }
            .mep-default-sidrbar-social .mep-event-meta {text-align: center;}
        </style>
        <?php
      return ob_get_clean();       
    }
}


if (!function_exists('mep_get_item_name')) {
    function mep_get_item_name($name) {
        $explode_name = explode('_', $name, 2);
        $the_item_name = str_replace('-', ' ', $explode_name[0]);
        return $the_item_name;
    }
}

if (!function_exists('mep_get_item_price')) {
    function mep_get_item_price($name) {
        $explode_name = explode('_', $name, 2);
        $the_item_name = str_replace('-', ' ', $explode_name[1]);
        return $the_item_name;
    }
}


if (!function_exists('mep_get_string_part')) {
    function mep_get_string_part($data, $string) {
        $pieces = explode(" x ", $data);
        return esc_html($pieces[$string]); // piece1
    }
}


if (!function_exists('mep_get_event_order_metadata')) {
    function mep_get_event_order_metadata($id, $part) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_order_itemmeta';
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE order_item_id = %d", $id));
        foreach ($result as $page) {
            if (strpos($page->meta_key, '_') !== 0) {
                echo mep_get_string_part($page->meta_key, $part) . '<br/>';
            }
        }
    }
}


// add_action( 'wp_head', 'mep_remove_my_event_order_list_from_my_account_page_action' );
// function mep_remove_my_event_order_list_from_my_account_page_action() {
// 	remove_action( 'woocommerce_account_dashboard', 'mep_ticket_lits_users' );
// }


add_action('woocommerce_account_dashboard', 'mep_ticket_lits_users');
if (!function_exists('mep_ticket_lits_users')) {
    function mep_ticket_lits_users() {
        ob_start();
        ?>
        <div class="mep-user-ticket-list">
            <table>
                <tr>
                    <th><?php esc_html_e('Name', 'mage-eventpress'); ?></th>
                    <th><?php esc_html_e('Ticket', 'mage-eventpress'); ?></th>
                    <th><?php esc_html_e('Event', 'mage-eventpress'); ?></th>
                    <?php do_action('mep_user_order_list_table_head'); ?>
                </tr>
                <?php
                    $_user_set_status   = apply_filters('mep_event_seat_reduce_status',mep_get_option('seat_reserved_order_status', 'general_setting_sec', array('processing','completed')));
                    $_order_status      = !empty($_user_set_status) ? $_user_set_status : array('processing','completed');
                    $order_status       = array_values($_order_status);
                
                    $order_status_filter =      array(
                        'key' => 'ea_order_status',
                        'value' => $order_status,
                        'compare' => 'OR'
                    );

                $args_search_qqq = array(
                    'post_type' => array('mep_events_attendees'),
                    'posts_per_page' => -1,
                    'author__in' => array(get_current_user_id()),
                    'meta_query' => array(
                        $order_status_filter
                    )
                );
                $loop = new WP_Query($args_search_qqq);
                while ($loop->have_posts()) {
                    $loop->the_post();
                    $event_id       = get_post_meta(get_the_id(), 'ea_event_id', true);
                    $virtual_info   = get_post_meta($event_id, 'mp_event_virtual_type_des', true) ? get_post_meta($event_id, 'mp_event_virtual_type_des', true) : '';
                    $event_meta     = get_post_custom($event_id);
                    $time           = get_post_meta($event_id, 'event_expire_datetime', true) ? strtotime(get_post_meta($event_id, 'event_expire_datetime', true)) : strtotime(get_post_meta($event_id, 'event_start_datetime', true));
                    $newformat      = date('Y-m-d H:i:s', $time);
                    if (strtotime(current_time('Y-m-d H:i:s')) < strtotime($newformat)) {
                        ?>
                        <tr>
                            <td><?php echo get_post_meta(get_the_id(), 'ea_name', true); ?></td>
                            <td><?php echo get_post_meta(get_the_id(), 'ea_ticket_type', true); ?></td>
                            <td><?php echo get_post_meta(get_the_id(), 'ea_event_name', true);
                                if ($virtual_info) { ?>
                                    <button id='mep_vr_view_btn_<?php echo get_the_id(); ?>' class='mep_view_vr_btn'><?php esc_html_e('View Virtual Info', 'mage-eventpress'); ?></button> <?php } ?>

                                    <?php do_action('mep_user_order_list_table_action_col', get_the_id()); ?>
                            </td>
                            <?php do_action('mep_user_order_list_table_row', get_the_id()); ?>
                        </tr>
                        <?php
                        if ($virtual_info) {
                            ?>
                            <tr id='mep_vr_view_sec_<?php echo get_the_id(); ?>' class='mep_virtual_event_info_sec' style='display:none'>
                                <td colspan='4'>
                                    <div class='mep-vr-vs-content'>
                                        <h3><?php esc_html_e('Virtual Event Information:', 'mage-eventpress'); ?></h3>
                                        <?php echo mep_esc_html(html_entity_decode($virtual_info)); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                }
                ?>
            </table>
        </div>
        <?php
        $content = ob_get_clean();
        echo wp_kses_post(html_entity_decode($content));
    }
}


if (!function_exists('mep_event_template_name')) {
    function mep_event_template_name() {

        $template_name = 'index.php';
        $template_path = get_stylesheet_directory() . '/mage-events/themes/';
        $default_path = plugin_dir_path(__DIR__) . 'templates/themes/';

        $template = locate_template(array($template_path . $template_name));

        if (!$template) :
            $template = $default_path . $template_name;
        endif;
        if (is_dir($template_path)) {
            $thedir = glob($template_path . "*");
        } else {
            $thedir = glob($default_path . "*");
        }

        $theme = array();
        foreach ($thedir as $filename) {
            if (is_file($filename)) {
                $file = basename($filename);
                $naame = str_replace("?>", "", strip_tags(file_get_contents($filename, false, null, 24, 14)));
            }
            $theme[$file] = $naame;
        }
        return $theme;
    }
}


if (!function_exists('event_single_template_list')) {
    function event_single_template_list($current_theme) {
        $themes = mep_event_template_name();
        $buffer = '<select name="mep_event_template">';
        foreach ($themes as $num => $desc) {
            if ($current_theme == $num) {
                $cc = 'selected';
            } else {
                $cc = '';
            }
            $name = preg_replace("/[^a-zA-Z0-9]+/", " ", $desc);
            $buffer .= "<option value=$num $cc>$name</option>";
        }//end foreach
        $buffer .= '</select>';
        echo wp_kses($buffer,['select' => array('name'=>[]),'option' => array('value'=>[], 'selected'=>[])]);
    }
}

if (!function_exists('mep_field_generator')) {
function mep_field_generator($type,$option){
    $FormFieldsGenerator = new FormFieldsGenerator();

    if ( $type === 'text' ) {
        return $FormFieldsGenerator->field_text( $option );
    } elseif ( $type === 'text_multi' ) {
        return $FormFieldsGenerator->field_text_multi( $option );
    } elseif ( $type === 'textarea' ) {
        return $FormFieldsGenerator->field_textarea( $option );
    } elseif ( $type === 'checkbox' ) {
        return $FormFieldsGenerator->field_checkbox( $option );
    } elseif ( $type === 'checkbox_multi' ) {
        return $FormFieldsGenerator->field_checkbox_multi( $option );
    } elseif ( $type === 'radio' ) {
        return $FormFieldsGenerator->field_radio( $option );
    } elseif ( $type === 'select' ) {
        return $FormFieldsGenerator->field_select( $option );
    } elseif ( $type === 'range' ) {
        return $FormFieldsGenerator->field_range( $option );
    } elseif ( $type === 'range_input' ) {
        return $FormFieldsGenerator->field_range_input( $option );
    } elseif ( $type === 'switch' ) {
        return $FormFieldsGenerator->field_switch( $option );
    } elseif ( $type === 'switch_multi' ) {
        return $FormFieldsGenerator->field_switch_multi( $option );
    } elseif ( $type === 'switch_img' ) {
        return $FormFieldsGenerator->field_switch_img( $option );
    } elseif ( $type === 'time_format' ) {
        return $FormFieldsGenerator->field_time_format( $option );
    } elseif ( $type === 'date_format' ) {
        return $FormFieldsGenerator->field_date_format( $option );
    } elseif ( $type === 'datepicker' ) {
        return $FormFieldsGenerator->field_datepicker( $option );
    } elseif ( $type === 'color_sets' ) {
        return $FormFieldsGenerator->field_color_sets( $option );
    } elseif ( $type === 'colorpicker' ) {
        return $FormFieldsGenerator->field_colorpicker( $option );
    } elseif ( $type === 'colorpicker_multi' ) {
        return $FormFieldsGenerator->field_colorpicker_multi( $option );
    } elseif ( $type === 'link_color' ) {
        return $FormFieldsGenerator->field_link_color( $option );
    } elseif ( $type === 'icon' ) {
        return $FormFieldsGenerator->field_icon( $option );
    } elseif ( $type === 'icon_multi' ) {
        return $FormFieldsGenerator->field_icon_multi( $option );
    } elseif ( $type === 'dimensions' ) {
        return $FormFieldsGenerator->field_dimensions( $option );
    } elseif ( $type === 'wp_editor' ) {
        return $FormFieldsGenerator->field_wp_editor( $option );
    } elseif ( $type === 'select2' ) {
        return $FormFieldsGenerator->field_select2( $option );
    } elseif ( $type === 'faq' ) {
        return $FormFieldsGenerator->field_faq( $option );
    } elseif ( $type === 'grid' ) {
        return $FormFieldsGenerator->field_grid( $option );
    } elseif ( $type === 'color_palette' ) {
        return $FormFieldsGenerator->field_color_palette( $option );
    } elseif ( $type === 'color_palette_multi' ) {
        return $FormFieldsGenerator->field_color_palette_multi( $option );
    } elseif ( $type === 'media' ) {
        return $FormFieldsGenerator->field_media( $option );
    } elseif ( $type === 'media_multi' ) {
        return $FormFieldsGenerator->field_media_multi( $option );
    } elseif ( $type === 'repeatable' ) {
        return $FormFieldsGenerator->field_repeatable( $option );
    } elseif ( $type === 'user' ) {
        return $FormFieldsGenerator->field_user( $option );
    } elseif ( $type === 'margin' ) {
        return $FormFieldsGenerator->field_margin( $option );
    } elseif ( $type === 'padding' ) {
        return $FormFieldsGenerator->field_padding( $option );
    } elseif ( $type === 'border' ) {
        return $FormFieldsGenerator->field_border( $option );
    } elseif ( $type === 'switcher' ) {
        return $FormFieldsGenerator->field_switcher( $option );
    } elseif ( $type === 'password' ) {
        return $FormFieldsGenerator->field_password( $option );
    } elseif ( $type === 'post_objects' ) {
        return $FormFieldsGenerator->field_post_objects( $option );
    } elseif ( $type === 'google_map' ) {
        return $FormFieldsGenerator->field_google_map( $option );
    }elseif ( $type === 'image_link' ) {
        return $FormFieldsGenerator->field_image_link($option);
    }else {
        return '';
    }    
}
}

if (!function_exists('mep_esc_html')) {
function mep_esc_html($string){
    $allow_attr = array(
        'input' => array(
            'br'                    => [],
            'type'                  => [],
            'class'                 => [],
            'id'                    => [],
            'name'                  => [],
            'value'                 => [],
            'size'                  => [],
            'placeholder'           => [],
            'min'                   => [],
            'max'                   => [],
            'checked'               => [],
            'required'              => [],
            'disabled'              => [],
            'readonly'              => [],
            'step'                  => [],
            'data-default-color'    => [],
        ),
        'p' => [
            'class'     => []
        ],
        'img' => [
            'class'     => [],
            'id'        => [],
            'src'       => [],
            'alt'       => [],
        ],
        'fieldset' => [
            'class'     => []
        ],
        'label' => [
            'for'       => [],
            'class'     => []
        ],
        'select' => [
            'class'     => [],
            'name'      => [],
            'id'        => [],
        ],
        'option' => [
            'class'     => [],
            'value'     => [],
            'id'        => [],
            'selected'  => [],
        ],
        'textarea' => [
            'class'     => [],
            'rows'      => [],
            'id'        => [],
            'cols'      => [],
            'name'      => [],
        ],
        'h2' => ['class'=> [],'id'=> [],],
        'a' => ['class'=> [],'id'=> [],'href'=> [],],
        'div' => ['class'=> [],'id'=> [],'data'=> [],],
        'span' => [
            'class'     => [],            
            'id'        => [],
            'data'      => [],
        ],
        'i' => [
            'class'     => [],            
            'id'        => [],
            'data'      => [],
        ],
        'table' => [
            'class'     => [],            
            'id'        => [],
            'data'      => [],
        ],
        'tr' => [
            'class'     => [],            
            'id'        => [],
            'data'      => [],
        ],
        'td' => [
            'class'     => [],            
            'id'        => [],
            'data'      => [],
        ],
        'thead' => [
            'class'     => [],            
            'id'        => [],
            'data'      => [],
        ],
        'tbody' => [
            'class'     => [],            
            'id'        => [],
            'data'      => [],
        ],
        'th' => [
            'class'     => [],            
            'id'        => [],
            'data'      => [],
        ],
        'svg' => [
            'class'     => [],            
            'id'        => [],
            'width'     => [],
            'height'    => [],
            'viewBox'   => [],
            'xmlns'     => [],
        ],
        'g' => [
            'fill'      => [],            
        ],
        'path' => [
            'd'         => [],            
        ],
        'br'            => array(),
        'em'            => array(),
        'strong'        => array(),        
    );
    return wp_kses($string,$allow_attr);
}
}


if (!function_exists('mep_title_cutoff_words')) {
    function mep_title_cutoff_words($text, $length) {
        if (strlen($text) > $length) {
            $text = substr($text, 0, strpos($text, ' ', $length));
        }

        return $text;
    }
}


if (!function_exists('mep_get_tshirts_sizes')) {
    function mep_get_tshirts_sizes($event_id) {
        $event_meta = get_post_custom($event_id);
        $tee_sizes = $event_meta['mep_reg_tshirtsize_list'][0];
        $tszrray = explode(',', $tee_sizes);
        $ts = "";
        foreach ($tszrray as $value) {
            $ts .= "<option value='$value'>$value</option>";
        }
        return $ts;
    }
}


if (!function_exists('mep_event_list_price')) {
    function mep_event_list_price($pid, $type='price') {
        global $post;
        $cur                        = get_woocommerce_currency_symbol();
        $mep_event_ticket_type      = get_post_meta($pid, 'mep_event_ticket_type', true) ? get_post_meta($pid, 'mep_event_ticket_type', true) : [];        
        $n_price                    = get_post_meta($pid, '_price', true);
        $price_arr                  = [];

        if(sizeof($mep_event_ticket_type) > 0){
            foreach ($mep_event_ticket_type as $ticket) {
                $price_arr[]    = array_key_exists('option_price_t',$ticket) ? $ticket['option_price_t'] : null;
            }
        }
        return $type == 'price' && sizeof($price_arr) > 0 ?  wc_price(mep_get_price_including_tax($pid,min($price_arr))) : count($price_arr);
    }
}

if (!function_exists('mep_event_list_number_price')) {
    function mep_event_list_number_price($pid, $type='price') {
        global $post;
        $cur                        = get_woocommerce_currency_symbol();
        $mep_event_ticket_type      = get_post_meta($pid, 'mep_event_ticket_type', true) ? get_post_meta($pid, 'mep_event_ticket_type', true) : [];        
        $n_price                    = get_post_meta($pid, '_price', true);
        $price_arr                  = [];

        if(sizeof($mep_event_ticket_type) > 0){
            foreach ($mep_event_ticket_type as $ticket) {
                $price_arr[]    = array_key_exists('option_price_t',$ticket) ? $ticket['option_price_t'] : null;
            }
        }

        return $type == 'price' && sizeof($price_arr) > 0 ?  min($price_arr) : count($price_arr);
    }
}


if (!function_exists('mep_get_label')) {
    function mep_get_label($pid, $label_id, $default_text) {
        return mep_get_option($label_id, 'label_setting_sec', $default_text);
    }
}


add_filter('manage_edit-mep_events_sortable_columns', 'mep_set_column_soartable');
if (!function_exists('mep_set_column_soartable')) {
    function mep_set_column_soartable($columns) {
        $columns['mep_event_date'] = 'event_start_datetime';

        //To make a column 'un-sortable' remove it from the array
        //unset($columns['mep_event_date']);

        return $columns;
    }
}

if (!function_exists('mep_remove_date_filter_dropdown')) {
function mep_remove_date_filter_dropdown($months) {
    global $typenow; // use this to restrict it to a particular post type
    if ($typenow == 'mep_events') {
        return array(); // return an empty array
    }
    return $months; // otherwise return the original for other post types
}
}
add_filter('months_dropdown_results', 'mep_remove_date_filter_dropdown');


add_action('pre_get_posts', 'mep_filter_event_list_by_date');
if (!function_exists('mep_filter_event_list_by_date')) {
function mep_filter_event_list_by_date($query) {
    if (!is_admin()) {
        return;
    }
    $orderby = $query->get('orderby');
    if ('event_start_datetime' == $orderby) {
        $query->set('meta_key', 'event_start_datetime');
        $query->set('orderby', 'meta_value');
    }
}
}
// Add the custom columns to the book post type:
add_filter('manage_mep_events_posts_columns', 'mep_set_custom_edit_event_columns');
if (!function_exists('mep_set_custom_edit_event_columns')) {
    function mep_set_custom_edit_event_columns($columns) {
        unset($columns['date']);
        $columns['mep_status'] = esc_html__('Status', 'mage-eventpress');
        $columns['mep_event_date'] = esc_html__('Event Start Date', 'mage-eventpress');
        return $columns;
    }
}

if (!function_exists('mep_get_full_time_and_date')) {
    function mep_get_full_time_and_date($datetime) {
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $wpdatesettings = $date_format . '  ' . $time_format;
        $user_set_format = mep_get_option('mep_event_time_format', 'general_setting_sec', 'wtss');

        if ($user_set_format == 12) {
            echo esc_html(wp_date('D, d M Y  h:i A', strtotime($datetime)));
        }
        if ($user_set_format == 24) {
            echo esc_html(wp_date('D, d M Y  H:i', strtotime($datetime)));
        }
        if ($user_set_format == 'wtss') {
            echo esc_html(wp_date($wpdatesettings, strtotime($datetime)));
        }
    }
}

if (!function_exists('mep_get_only_time')) {
    function mep_get_only_time($datetime) {
        $user_set_format = mep_get_option('mep_event_time_format', 'general_setting_sec', 'wtss');
        $time_format = get_option('time_format');
        if ($user_set_format == 12) {
            echo esc_html(date('h:i A', strtotime($datetime)));
        }
        if ($user_set_format == 24) {
            echo esc_html(date('H:i', strtotime($datetime)));
        }
        if ($user_set_format == 'wtss') {
            echo esc_html(date($time_format, strtotime($datetime)));
        }
    }
}


if (!function_exists('mep_get_event_city')) {
    function mep_get_event_city($event_id) {

    $location_sts = get_post_meta($event_id, 'mep_org_address', true) ? get_post_meta($event_id, 'mep_org_address', true) : '';
    // ob_start();
    if ($location_sts) {
        $org_arr    = get_the_terms($event_id, 'mep_org');
        $org_id     = $org_arr[0]->term_id;
        $location   =  get_term_meta($org_id, 'org_location', true) ? get_term_meta($org_id, 'org_location', true) : '';
        $street     =  get_term_meta($org_id, 'org_street', true) ? get_term_meta($org_id, 'org_street', true) : '';
        $city       =  get_term_meta($org_id, 'org_city', true) ? get_term_meta($org_id, 'org_city', true) : '';
        $state      =  get_term_meta($org_id, 'org_state', true) ? get_term_meta($org_id, 'org_state', true) : '';
        $zip        =  get_term_meta($org_id, 'org_postcode', true) ? get_term_meta($org_id, 'org_postcode', true) : '';
        $country    =  get_term_meta($org_id, 'org_country', true) ? get_term_meta($org_id, 'org_country', true) : '';
    } else {
        $location   =  get_post_meta($event_id, 'mep_location_venue', true) ? get_post_meta($event_id, 'mep_location_venue', true) : '';
        $street     =  get_post_meta($event_id, 'mep_street', true) ? get_post_meta($event_id, 'mep_street', true) : '';
        $city       =  get_post_meta($event_id, 'mep_city', true) ? get_post_meta($event_id, 'mep_city', true) : '';
        $state      =  get_post_meta($event_id, 'mep_state', true) ? get_post_meta($event_id, 'mep_state', true) : '';
        $zip        =  get_post_meta($event_id, 'mep_postcode', true) ? get_post_meta($event_id, 'mep_postcode', true) : '';
        $country    =  get_post_meta($event_id, 'mep_country', true) ? get_post_meta($event_id, 'mep_country', true) : '';
    }

    $location_arr = [$location, $city];
    $content = implode(', ', array_filter($location_arr));
    $address_arr = array(
        'location'  => $location,
        'street'    => $street,
        'state'     => $state,
        'zip'       => $zip,
        'city'      => $city,
        'country'   => $country
    );
   echo esc_html(apply_filters('mage_event_location_in_list_view', $content, $event_id, $address_arr));        
    }
}

if (!function_exists('mep_get_total_available_seat')) {
    function mep_get_total_available_seat($event_id, $event_meta) {
		$availabele_check = mep_get_option('mep_speed_up_list_page', 'general_setting_sec', 'no');
		if($availabele_check == 'no'){
        $total_seat_left    = get_post_meta($event_id,'mep_total_seat_left',true) ? get_post_meta($event_id,'mep_total_seat_left',true) : mep_count_total_available_seat($event_id);  
		}else{
		$total_seat_left    = get_post_meta($event_id,'mep_total_seat_left',true) ? get_post_meta($event_id,'mep_total_seat_left',true) : 1;  
		}
        return esc_html($total_seat_left);
    }
}


  if (!function_exists('mep_count_total_available_seat')) {
    function mep_count_total_available_seat($event_id) {
        $total_seat = mep_event_total_seat($event_id, 'total');
        $total_resv = mep_event_total_seat($event_id, 'resv');
        $total_sold = mep_ticket_sold($event_id);
        $total_left = $total_seat - ($total_sold + $total_resv);
        return esc_html($total_left);
    }
  }

if (!function_exists('mep_get_event_total_available_seat')) {
    function mep_get_event_total_available_seat($event_id, $date) {
        $total_seat = mep_event_total_seat($event_id, 'total');
        $total_resv = mep_event_total_seat($event_id, 'resv');
        $total_sold = mep_ticket_type_sold($event_id, '', $date);
        $total_left = $total_seat - ($total_sold + $total_resv);
        return esc_html($total_left);
    }
}


if (!function_exists('mep_event_location_item')) {
    function mep_event_location_item($event_id, $item_name) {
        return get_post_meta($event_id, $item_name, true);
    }
}

if (!function_exists('mep_event_org_location_item')) {
    function mep_event_org_location_item($event_id, $item_name) {
        $location_sts = get_post_meta($event_id, 'mep_org_address', true);

        $org_arr = get_the_terms($event_id, 'mep_org');
        if ($org_arr) {
            $org_id = $org_arr[0]->term_id;
            return get_term_meta($org_id, $item_name, true);
        }
    }
}

if (!function_exists('mep_get_all_date_time')) {
    function mep_get_all_date_time($start_datetime, $more_datetime, $end_datetime) {
        ob_start();

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $wpdatesettings = $date_format . $time_format;
        $user_set_format = mep_get_option('mep_event_time_format', 'general_setting_sec', 'wtss');
        ?>
        <ul>
            <?php if ($user_set_format == 12) { ?>
                <?php $timeformatassettings = 'h:i A'; ?>
                <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime($start_datetime)); ?> <i class="fa fa-clock-o"></i> <?php echo date_i18n('h:i A', strtotime($start_datetime)); ?></li>
            <?php } ?>
            <?php if ($user_set_format == 24) { ?>
                <?php $timeformatassettings = 'H:i'; ?>
                <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime($start_datetime)); ?> <i class="fa fa-clock-o"></i> <?php echo date_i18n('H:i', strtotime($start_datetime)); ?></li>
            <?php } ?>
            <?php if ($user_set_format == 'wtss'){ ?>
        <?php $timeformatassettings = get_option('time_format'); ?>
            <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime($start_datetime)); ?> <i class="fa fa-clock-o"></i> <?php echo date_i18n($time_format, strtotime($start_datetime));
                } ?></li>
            }
            }

            ?>
            <?php


            foreach ($more_datetime as $_more_datetime) {
                ?>
                <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime($_more_datetime['event_more_date'])); ?> <i class="fa fa-clock-o"></i> <?php echo date_i18n($timeformatassettings, strtotime($_more_datetime['event_more_date'])) ?></li>
                <?php
            }
            ?>

            <?php
            if ($user_set_format == 12) {
                $timeformatassettings = 'h:i A';
            }
            if ($user_set_format == 24) {
                $timeformatassettings = 'H:i';
            }
            if ($user_set_format == 'wtss') {
                $timeformatassettings = get_option('time_format');
            }

            ?>
            <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime($end_datetime)); ?> <i class="fa fa-clock-o"></i> <?php echo date($timeformatassettings, strtotime($end_datetime)); ?> <span style='font-size: 12px;font-weight: bold;'>(<?php esc_html_e('End', 'mage-eventpress'); ?>)</span></li>
        </ul>
        <?php
        echo ob_get_clean();        
    }
}


if (!function_exists('mep_get_event_locaion_item')) {
    function mep_get_event_locaion_item($event_id, $item_name) {
        if ($event_id) {
            $location_sts = get_post_meta($event_id, 'mep_org_address', true);


            if ($item_name == 'mep_location_venue') {
                if ($location_sts) {
                    $org_arr = get_the_terms($event_id, 'mep_org');

                    if (is_array($org_arr) && sizeof($org_arr) > 0) {
                        $org_id = $org_arr[0]->term_id;
                        return get_term_meta($org_id, 'org_location', true);
                    }
                } else {
                    return get_post_meta($event_id, 'mep_location_venue', true);
                }
                return null;
            }

            if ($item_name == 'mep_location_venue') {
                if ($location_sts) {
                    $org_arr = get_the_terms($event_id, 'mep_org');
                    if (is_array($org_arr) && sizeof($org_arr) > 0) {
                        $org_id = $org_arr[0]->term_id;
                        return get_term_meta($org_id, 'org_location', true);
                    }

                } else {
                    return get_post_meta($event_id, 'mep_location_venue', true);
                }
            }


            if ($item_name == 'mep_street') {
                if ($location_sts) {
                    $org_arr = get_the_terms($event_id, 'mep_org');
                    if (is_array($org_arr) && sizeof($org_arr) > 0) {
                        $org_id = $org_arr[0]->term_id;
                        return get_term_meta($org_id, 'org_street', true);
                    }
                } else {
                    return get_post_meta($event_id, 'mep_street', true);
                }
            }


            if ($item_name == 'mep_city') {
                if ($location_sts) {
                    $org_arr = get_the_terms($event_id, 'mep_org');
                    if (is_array($org_arr) && sizeof($org_arr) > 0) {
                        $org_id = $org_arr[0]->term_id;
                        return get_term_meta($org_id, 'org_city', true);
                    }
                } else {
                    return get_post_meta($event_id, 'mep_city', true);
                }
            }


            if ($item_name == 'mep_state') {
                if ($location_sts) {
                    $org_arr = get_the_terms($event_id, 'mep_org');
                    if (is_array($org_arr) && sizeof($org_arr) > 0) {
                        $org_id = $org_arr[0]->term_id;
                        return get_term_meta($org_id, 'org_state', true);
                    }
                } else {
                    return get_post_meta($event_id, 'mep_state', true);
                }
            }


            if ($item_name == 'mep_postcode') {
                if ($location_sts) {
                    $org_arr = get_the_terms($event_id, 'mep_org');
                    if (is_array($org_arr) && sizeof($org_arr) > 0) {
                        $org_id = $org_arr[0]->term_id;
                        return get_term_meta($org_id, 'org_postcode', true);
                    }
                } else {
                    return get_post_meta($event_id, 'mep_postcode', true);
                }
            }


            if ($item_name == 'mep_country') {
                if ($location_sts) {
                    $org_arr = get_the_terms($event_id, 'mep_org');
                    if (is_array($org_arr) && sizeof($org_arr) > 0) {
                        $org_id = $org_arr[0]->term_id;
                        return get_term_meta($org_id, 'org_country', true);
                    }
                } else {
                    return get_post_meta($event_id, 'mep_country', true);
                }
            }
        }
    }
}


if (!function_exists('mep_save_attendee_info_into_cart')) {
    function mep_save_attendee_info_into_cart($product_id) {

        $user = array();

        $mep_user_name          = isset($_POST['user_name']) ? mage_array_strip($_POST['user_name']) : [];
        $mep_user_email         = isset($_POST['user_email']) ? mage_array_strip($_POST['user_email']) : [];
        $mep_user_phone         = isset($_POST['user_phone']) ? mage_array_strip($_POST['user_phone']) : [];
        $mep_user_address       = isset($_POST['user_address']) ? mage_array_strip($_POST['user_address']) : [];
        $mep_user_gender        = isset($_POST['gender']) ? mage_array_strip($_POST['gender']) : [];
        $mep_user_tshirtsize    = isset($_POST['tshirtsize']) ? mage_array_strip($_POST['tshirtsize']) : [];
        $mep_user_company       = isset($_POST['user_company']) ? mage_array_strip($_POST['user_company']) : [];
        $mep_user_desg          = isset($_POST['user_designation']) ? mage_array_strip($_POST['user_designation']) : [];
        $mep_user_website       = isset($_POST['user_website']) ? mage_array_strip($_POST['user_website']) : [];
        $mep_user_vegetarian    = isset($_POST['vegetarian']) ? mage_array_strip($_POST['vegetarian']) : [];
        $mep_user_ticket_type   = isset($_POST['ticket_type']) ? mage_array_strip($_POST['ticket_type']) : [];
        $event_date             = isset($_POST['event_date']) ? mage_array_strip($_POST['event_date']) : [];
        $mep_event_id           = isset($_POST['mep_event_id']) ? mage_array_strip($_POST['mep_event_id']) : [];
        $mep_user_option_qty    = isset($_POST['option_qty']) ? mage_array_strip($_POST['option_qty']) : [];
        $mep_user_cfd           = isset($_POST['mep_ucf']) ? mage_array_strip($_POST['mep_ucf']) : [];



        if ($mep_user_name) {
            $count_user = count($mep_user_name);
        } else {
            $count_user = 0;
        }

        for ($iu = 0; $iu < $count_user; $iu++) {

            if (isset($mep_user_name[$iu])):
                $user[$iu]['user_name'] = stripslashes(strip_tags($mep_user_name[$iu]));
            endif;

            if (isset($mep_user_email[$iu])) :
                $user[$iu]['user_email'] = stripslashes(strip_tags($mep_user_email[$iu]));
            endif;

            if (isset($mep_user_phone[$iu])) :
                $user[$iu]['user_phone'] = stripslashes(strip_tags($mep_user_phone[$iu]));
            endif;

            if (isset($mep_user_address[$iu])) :
                $user[$iu]['user_address'] = stripslashes(strip_tags($mep_user_address[$iu]));
            endif;

            if (isset($mep_user_gender[$iu])) :
                $user[$iu]['user_gender'] = stripslashes(strip_tags($mep_user_gender[$iu]));
            endif;

            if (isset($mep_user_tshirtsize[$iu])) :
                $user[$iu]['user_tshirtsize'] = stripslashes(strip_tags($mep_user_tshirtsize[$iu]));
            endif;

            if (isset($mep_user_company[$iu])) :
                $user[$iu]['user_company'] = stripslashes(strip_tags($mep_user_company[$iu]));
            endif;

            if (isset($mep_user_desg[$iu])) :
                $user[$iu]['user_designation'] = stripslashes(strip_tags($mep_user_desg[$iu]));
            endif;

            if (isset($mep_user_website[$iu])) :
                $user[$iu]['user_website'] = stripslashes(strip_tags($mep_user_website[$iu]));
            endif;

            if (isset($mep_user_vegetarian[$iu])) :
                $user[$iu]['user_vegetarian'] = stripslashes(strip_tags($mep_user_vegetarian[$iu]));
            endif;

            if (isset($mep_user_ticket_type[$iu])) :
                $user[$iu]['user_ticket_type'] = strip_tags($mep_user_ticket_type[$iu]);
            endif;

            if (isset($event_date[$iu])) :
                $user[$iu]['user_event_date'] = stripslashes(strip_tags($event_date[$iu]));
            endif;

            if (isset($mep_event_id[$iu])) :
                $user[$iu]['user_event_id'] = stripslashes(strip_tags($mep_event_id[$iu]));
            endif;

            if (isset($mep_user_option_qty[$iu])) :
                $user[$iu]['user_ticket_qty'] = stripslashes(strip_tags($mep_user_option_qty[$iu]));
            endif;

            $reg_form_id = mep_fb_get_reg_form_id($product_id);
            $mep_form_builder_data = get_post_meta($reg_form_id, 'mep_form_builder_data', true);
            if ($mep_form_builder_data) {
                foreach ($mep_form_builder_data as $_field) {
                    $user[$iu][$_field['mep_fbc_id']] = isset($_POST[$_field['mep_fbc_id']][$iu]) ? stripslashes(mage_array_strip($_POST[$_field['mep_fbc_id']][$iu])) : "";
			    //mep_attendee_upload_file_system($user,$iu,$_field);
			    $user=apply_filters('mep_attendee_upload_file',$user,$iu,$_field);
                }
            }


        }
        return apply_filters('mep_cart_user_data_prepare', $user, $product_id);
    }
}


if (!function_exists('mep_wc_price')) {
    function mep_wc_price($price, $args = array()) {
        $args = apply_filters(
            'wc_price_args', wp_parse_args(
                $args, array(
                    'ex_tax_label' => false,
                    'currency' => '',
                    'decimal_separator' => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'decimals' => wc_get_price_decimals(),
                    'price_format' => get_woocommerce_price_format(),
                )
            )
        );

        $unformatted_price = $price;
        $negative = $price < 0;
        $price = apply_filters('raw_woocommerce_price', floatval($negative ? $price * -1 : $price));
        $price = apply_filters('formatted_woocommerce_price', number_format($price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator']), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator']);

        if (apply_filters('woocommerce_price_trim_zeros', false) && $args['decimals'] > 0) {
            $price = wc_trim_zeros($price);
        }

        $formatted_price = ($negative ? '-' : '') . sprintf($args['price_format'], '' . '' . '', $price);
        $return = '' . $formatted_price . '';

        if ($args['ex_tax_label'] && wc_tax_enabled()) {
            $return .= '' . WC()->countries->ex_tax_or_vat() . '';
        }

        /**
         * Filters the string of price markup.
         *
         * @param string $return Price HTML markup.
         * @param string $price Formatted price.
         * @param array $args Pass on the args.
         * @param float $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
         */
        return apply_filters('mep_wc_price', $return, $price, $args, $unformatted_price);
    }
}


if (!function_exists('mep_get_event_total_seat')) {
    function mep_get_event_total_seat($event_id, $m = null, $t = null) {

        $upcoming_date                  = !empty($m) && !empty(mep_get_event_upcoming_date($event_id)) ? mep_get_event_upcoming_date($event_id) : '';
        $total_seat                     = apply_filters('mep_event_total_seat_counts', mep_event_total_seat($event_id, 'total'), $event_id);
        $total_resv                     = apply_filters('mep_event_total_resv_seat_count', mep_event_total_seat($event_id, 'resv'), $event_id);
       
        $total_sold = mep_get_event_total_seat_left($event_id, $upcoming_date);
        //$total_sold = mep_ticket_type_sold($event_id);
        $recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
        $total_left = (int) $total_seat - ((int) $total_sold + (int) $total_resv);
        $event_date = date('Y-m-d H:i', strtotime(mep_get_event_upcoming_date($event_id)));

        ob_start();
        if ($recurring != 'no') {
			$total_sold     = $recurring == 'everyday' ? mep_get_event_total_seat_left($event_id, $event_date) : mep_get_event_total_seat_left($event_id, $upcoming_date);
            $total          = $m != null ? (int)$total_seat * (int)$m : $total_seat;
            $sold           = $total - ($total_sold + $total_resv);
            $available      = $total - $sold;
            ?>
            <span style="background: #dc3232;color: #fff;padding: 5px 10px; display:block">
                <span class="mep_seat_stat_info_<?php echo $event_id; ?>">
                    <?php
                    $seat_count_var = apply_filters('mep_event_total_seat_counts', $total, $event_id) . ' - ' . apply_filters('mep_event_total_seat_sold', $available, $event_id, $event_date) . ' = ' . apply_filters('mep_event_total_seat_left', $sold, $event_id, '', $event_date);         
                    echo apply_filters('mep_event_seat_status_text', $seat_count_var, $total, $available, $sold);
                    ?>
                </span>
                <?php //do_action('mep_after_seat_stat_info',$event_id); ?>
            </span>
            <?php
        } else {
            ?>
            <span style="background: #dc3232;color: #fff;padding: 5px 10px; display:block">
                <span class="mep_seat_stat_info_<?php echo $event_id; ?>">
                    <?php
                    // $sold = ($total_seat - $total_left);
                    $seat_count_var = apply_filters('mep_event_total_seat_counts', $total_seat, $event_id) . ' - ' . apply_filters('mep_event_total_seat_sold', $total_sold, $event_id, $event_date) . ' = ' . apply_filters('mep_event_total_seat_left', $total_left, $event_id, '', $event_date);       
                    echo apply_filters('mep_event_seat_status_text', $seat_count_var, $total_seat, $total_sold, $total_left);
                    ?>
                </span>
                <?php do_action('mep_after_seat_stat_info',$event_id); ?>
            </span>
            <?php
        }
        return ob_get_clean();
    }
}





add_filter('manage_mep_events_posts_columns', 'mep_set_custom_mep_events_columns');
if (!function_exists('mep_set_custom_mep_events_columns')) {
    function mep_set_custom_mep_events_columns($columns) {
        $columns['mep_event_seat'] = apply_filters('mep_seat_status_head_text', esc_html__('Seats [ Total - Sold = Available ]', 'mage-eventpress'));
        return $columns;
    }
}

// Add the data to the custom columns for the book post type:
add_action('manage_mep_events_posts_custom_column', 'mep_mep_events_column', 10, 2);
if (!function_exists('mep_mep_events_column')) {
    function mep_mep_events_column($column, $post_id) {
        $post_id = mep_get_default_lang_event_id($post_id);
        switch ($column) {
            case 'mep_event_seat' :
                $recurring = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';
                if ($recurring == 'yes') {
                    $more_date = get_post_meta($post_id, 'mep_event_more_date', true) ? get_post_meta($post_id, 'mep_event_more_date', true) : array();
                    $event_more_dates = is_array($more_date) && sizeof($more_date) > 0 ? count($more_date) + 1 : '';
                        
                    echo apply_filters( 'mep_attendee_stat_recurring', mep_get_event_total_seat($post_id, $event_more_dates, 'multi'),$post_id);
                } else {
                    $event_upcoming_date = date('Y-m-d H:i',strtotime(mep_get_event_upcoming_date($post_id)));
                    echo mep_get_event_total_seat($post_id,$event_upcoming_date);
                }

                break;
        }
    }
}

if (!function_exists('mep_get_term_as_class')) {
    function mep_get_term_as_class($post_id, $taxonomy, $unq_id = '') {
        $tt = get_the_terms($post_id, $taxonomy) ? get_the_terms($post_id, $taxonomy) : [];
        if (is_array($tt) && sizeof($tt) > 0) {
            $t_class = array();
            foreach ($tt as $tclass) {
                $t_class[] = $unq_id . 'mage-' . $tclass->term_id;
            }
            $main_class = implode(' ', $t_class);
            return $main_class;
        } else {
            return null;
        }
    }
}


add_action('mep_event_seat_reduce_status_name_list','mep_add_partial_payment_name_to_event_seat_reduce');
function mep_add_partial_payment_name_to_event_seat_reduce($name){
    $new_name = array(
        'partially-paid'  => esc_html__( 'Partially Paid', 'tour-booking-manager' ),
    );
    return array_merge($name, $new_name);
}


if (!function_exists('mep_ticket_type_sold')) {
    function mep_ticket_type_sold($event_id, $type = '', $date = '') {
        $type           = !empty($type) ? $type : '';

        $_user_set_status   = apply_filters('mep_event_seat_reduce_status',mep_get_option('seat_reserved_order_status', 'general_setting_sec', array('processing','completed')));
        $_order_status      = !empty($_user_set_status) ? $_user_set_status : array('processing','completed');
        $order_status       = array_values($_order_status);

        // $order_status_filter =      array(
        //     'key' => 'ea_order_status',
        //     'value' => $order_status,
        //     'compare' => 'OR'
        // );


        if ( count( $order_status ) > 1 ) { // check if more then one tag
            $order_status_filter['relation'] = 'OR';
            
            foreach($order_status as $tag) { // create a LIKE-comparison for every single tag
                $order_status_filter[] = array( 'key' => 'ea_order_status', 'value' => $tag, 'compare' => '=' );
            }

        } else { // if only one tag then proceed with simple query
                $order_status_filter[] = array( 'key' => 'ea_order_status', 'value' => $order_status[0], 'compare' => '=' );
        }


        $type_filter = !empty($type) ? array(
            'key' => 'ea_ticket_type',
            'value' => $type,
            'compare' => '='
        ) : '';

        $date_filter = !empty($date) ? array(
            'key' => 'ea_event_date',
            'value' => $date,
            'compare' => 'LIKE'
        ) : '';

        $args = array(
            'post_type' => 'mep_events_attendees',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'ea_event_id',
                        'value' => $event_id,
                        'compare' => '='
                    ),
                    $type_filter,
                    apply_filters('mep_sold_meta_query_and_attribute', $date_filter)
                ), 
                $order_status_filter
            )
        );
        $loop = new WP_Query($args);
        // echo '<pre>'; print_r($loop); echo '</pre>';
        // // die();
        return $loop->post_count;
    }
}


if (!function_exists('mep_extra_service_sold')) {
    function mep_extra_service_sold($event_id, $type, $date) {
        $type = !empty($type) ? html_entity_decode($type) : '';
        $args = array(
            'post_type' => 'mep_extra_service',
            'posts_per_page' => -1,

            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'ea_extra_service_event',
                        'value' => $event_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'ea_extra_service_name',
                        'value' => $type,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'ea_extra_service_event_date',
                        'value' => $date,
                        'compare' => 'LIKE'
                    )
                ), array(
                    'relation' => 'OR',
                    array(
                        'key' => 'ea_extra_service_order_status',
                        'value' => 'processing',
                        'compare' => '='
                    ),
                    array(
                        'key' => 'ea_extra_service_order_status',
                        'value' => 'completed',
                        'compare' => '='
                    )
                )
            )
        );
        $loop = new WP_Query($args);
        $count = 0;
        foreach ($loop->posts as $sold_service) {
            $pid = $sold_service->ID;
            $count = $count + get_post_meta($pid, 'ea_extra_service_qty', true);
        }
        return $count;
    }
}

if (!function_exists('mep_ticket_sold')) {
    function mep_ticket_sold($event_id) {
        $event_start_date = date('Y-m-d', strtotime(get_post_meta($event_id, 'event_start_date', true)));
        // $get_ticket_type_list = get_post_meta($event_id,'mep_event_ticket_type',true) ? get_post_meta($event_id,'mep_event_ticket_type',true) : array();
        $get_ticket_type_list = metadata_exists('post', $event_id, 'mep_event_ticket_type') ? get_post_meta($event_id, 'mep_event_ticket_type', true) : array();

        $recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';

        $sold = 0;
        if (is_array($get_ticket_type_list) && sizeof($get_ticket_type_list) > 0) {
            foreach ($get_ticket_type_list as $ticket_type) {
                if(array_key_exists('option_name_t',$ticket_type)){
                    $sold = $sold + mep_ticket_type_sold($event_id, mep_remove_apostopie($ticket_type['option_name_t']), $event_start_date);
                }
            }
        }

        if ($recurring == 'yes') {
            //   $mep_event_more_date = get_post_meta($event_id,'mep_event_more_date',true);
            $mep_event_more_date = metadata_exists('post', $event_id, 'mep_event_more_date') ? get_post_meta($event_id, 'mep_event_more_date', true) : array();
            if (is_array($mep_event_more_date) && sizeof($mep_event_more_date) > 0) {
                foreach ($mep_event_more_date as $md) {
                    if (is_array($get_ticket_type_list) && sizeof($get_ticket_type_list) > 0) {
                        foreach ($get_ticket_type_list as $ticket_type) {
                            if(array_key_exists('option_name_t',$ticket_type)){
                             $sold = $sold + mep_ticket_type_sold($event_id, mep_remove_apostopie($ticket_type['option_name_t']), $md['event_more_start_date']);
                            }
                        }
                    }
                }
            }
        }

        return $sold;
    }
}


if (!function_exists('mep_event_total_seat')) {
    function mep_event_total_seat($event_id, $type) {
        $mep_event_ticket_type = get_post_meta($event_id, 'mep_event_ticket_type', true);
        // print_r($mep_event_ticket_type);
        $total = 0;
        if (is_array($mep_event_ticket_type) && sizeof($mep_event_ticket_type) > 0) {
            foreach ($mep_event_ticket_type as $field) {
                if ($type == 'total') {
                    $total_name = array_key_exists('option_qty_t', $field) ? (int)$field['option_qty_t'] : 0;
                } elseif ($type == 'resv') {
                    $total_name = array_key_exists('option_rsv_t', $field) ? (int)$field['option_rsv_t'] : 0;
                }
                $total = $total_name + $total;
            }
        }
        return $total;
    }
}


if (!function_exists('get_mep_datetime')) {
    function get_mep_datetime($date, $type) {

        $event_id = get_the_id() ? get_the_id() : 0;

       
        $date_format            = mep_get_datetime_format($event_id,'date');
        $date_format_timezone   = mep_get_datetime_format($event_id,'date_timezone');
      
        $time_format            = mep_get_datetime_format($event_id,'time');
        $time_format_timezone   = mep_get_datetime_format($event_id,'time_timezone');

        
        $wpdatesettings = $date_format . '  ' . $time_format_timezone;
        $timezone = wp_timezone_string();
        $timestamp = strtotime($date . ' ' . $timezone);

        if ($type == 'date') {
            return esc_html(wp_date($date_format, $timestamp));
        }
        if ($type == 'date-time') {
            return esc_html(wp_date($wpdatesettings, $timestamp));
        }
        if ($type == 'date-text') {

            return esc_html(wp_date($date_format, $timestamp));
        }

        if ($type == 'date-time-text') {
            return esc_html(wp_date($wpdatesettings, $timestamp, wp_timezone()));
        }
        if ($type == 'time') {
            return esc_html(wp_date($time_format_timezone, $timestamp, wp_timezone()));
        }

        if ($type == 'Hour') {
            return esc_html(wp_date('H', $timestamp, wp_timezone()));
        }
        if ($type == 'hour') {
            return esc_html(wp_date('h', $timestamp, wp_timezone()));
        }
        if ($type == 'minute') {
            return esc_html(wp_date('i', $timestamp, wp_timezone()));
        }

        if ($type == 'second') {
            return esc_html(wp_date('s', $timestamp, wp_timezone()));
        }

        if ($type == 'day') {
            return esc_html(wp_date('d', $timestamp));
        }
        if ($type == 'Dday') {
            return esc_html(wp_date('D', $timestamp));
        }
        if ($type == 'month') {
            return esc_html(wp_date('m', $timestamp));
        }
        if ($type == 'month-name') {
            return esc_html(wp_date('M', $timestamp));
        }
        if ($type == 'year') {
            return esc_html(wp_date('y', $timestamp));
        }
        if ($type == 'year-full') {
            return esc_html(wp_date('Y', $timestamp));
        }
        if ($type == 'timezone') {
            return esc_html(wp_date('T', $timestamp));
        }
        return '';
    }
}

if (!function_exists('mep_get_location')) {
function mep_get_location($event_id, $type) {

    $location_sts = get_post_meta($event_id, 'mep_org_address', true) ? get_post_meta($event_id, 'mep_org_address', true) : '';
    if ($location_sts) {
        $org_arr = get_the_terms($event_id, 'mep_org') ? get_the_terms($event_id, 'mep_org') : [];
        $org_id = sizeof($org_arr) > 0 ? $org_arr[0]->term_id : '';
        $location = !empty($org_id) && get_term_meta($org_id, 'org_location', true) ? get_term_meta($org_id, 'org_location', true) : '';
        $street = !empty($org_id) && get_term_meta($org_id, 'org_street', true) ? get_term_meta($org_id, 'org_street', true) : '';
        $city = !empty($org_id) && get_term_meta($org_id, 'org_city', true) ? get_term_meta($org_id, 'org_city', true) : '';
        $state = !empty($org_id) && get_term_meta($org_id, 'org_state', true) ? get_term_meta($org_id, 'org_state', true) : '';
        $zip = !empty($org_id) && get_term_meta($org_id, 'org_postcode', true) ? get_term_meta($org_id, 'org_postcode', true) : '';
        $country = !empty($org_id) && get_term_meta($org_id, 'org_country', true) ? get_term_meta($org_id, 'org_country', true) : '';
    } else {
        $location = get_post_meta($event_id, 'mep_location_venue', true) ? get_post_meta($event_id, 'mep_location_venue', true) : '';
        $street = get_post_meta($event_id, 'mep_street', true) ? get_post_meta($event_id, 'mep_street', true) : '';
        $city = get_post_meta($event_id, 'mep_city', true) ? get_post_meta($event_id, 'mep_city', true) : '';
        $state = get_post_meta($event_id, 'mep_state', true) ? get_post_meta($event_id, 'mep_state', true) : '';
        $zip = get_post_meta($event_id, 'mep_postcode', true) ? get_post_meta($event_id, 'mep_postcode', true) : '';
        $country = get_post_meta($event_id, 'mep_country', true) ? get_post_meta($event_id, 'mep_country', true) : '';
    }
    $location_arr = [$location, $street, $city, $state, $zip, $country];

    if ($type == 'full') {

        echo esc_html(implode(', ', array_filter($location_arr)));
    }

    if ($type == 'location') {
        echo esc_html($location);
    }

    if ($type == 'street') {
        echo esc_html($street);
    }

    if ($type == 'state') {
        echo esc_html($state);
    }

    if ($type == 'city') {
        echo esc_html($city);
    }

    if ($type == 'zip') {
        echo esc_html($zip);
    }

    if ($type == 'country') {
        echo esc_html($country);
    }

}
}

if (!function_exists('mep_get_event_upcomming_date')) {
    function mep_get_event_upcomming_date($event_id, $type) {

        $recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
        $more_date = get_post_meta($event_id, 'mep_event_more_date', true) ? get_post_meta($event_id, 'mep_event_more_date', true) : array();
        $start_datetime = get_post_meta($event_id, 'event_start_datetime', true);
        $start_date = date('Y-m-d H:i:s', strtotime(get_post_meta($event_id, 'event_start_datetime', true)));
        $end_date = get_post_meta($event_id, 'event_end_date', true);
        $end_datetime = get_post_meta($event_id, 'event_end_datetime', true);
        $show_multidate = mep_get_option('mep_date_list_in_event_listing', 'event_list_setting_sec', 'no');

        //     if (strtotime(current_time('Y-m-d H:i')) < strtotime($start_datetime)) {

        $all_datetime = array($start_date);

        if (sizeof($more_date) > 0) {
            foreach ($more_date as $mdate) {
                $all_datetime[] = date('Y-m-d H:i:s', strtotime($mdate['event_more_start_date'] . ' ' . $mdate['event_more_start_time']));
            }
        }
        $adt = [];
        foreach ($all_datetime as $ald) {
            if (strtotime(current_time('Y-m-d H:i')) < strtotime($ald)) {
                $adt[] = $ald;
            }
        }
        if (sizeof($adt) > 0) {
            return get_mep_datetime($adt[0], $type);
        }
    }
}


if (!function_exists('mep_on_post_publish')) {
    function mep_on_post_publish($post_id, $post, $update) {
        if ($post->post_type == 'mep_events' && $post->post_status == 'publish' && empty(get_post_meta($post_id, 'check_if_run_once'))) {
            $product_cat_ids = wp_get_post_terms($post_id, 'product_cat', array('fields' => 'ids'));
            // ADD THE FORM INPUT TO $new_post ARRAY
            $new_post = array(
                'post_title' => $post->post_title,
                'post_content' => '',
                'post_name' => uniqid(),
                'post_category' => array(),  // Usable for custom taxonomies too
                'tags_input' => array(),
                'post_status' => 'publish', // Choose: publish, preview, future, draft, etc.
                'post_type' => 'product'  //'post',page' or use a custom post type if you want to
            );
            //SAVE THE POST
            $pid            = wp_insert_post($new_post);
            $product_type   = mep_get_option('mep_event_product_type', 'single_event_setting_sec', 'yes');
            $_tax_status =  'none';            
            update_post_meta($pid, '_tax_status', $_tax_status);            
            update_post_meta($post_id, '_tax_status', $_tax_status);            
            update_post_meta($post_id, 'link_wc_product', $pid);
            update_post_meta($pid, 'link_mep_event', $post_id);
            update_post_meta($pid, '_price', 0.01);
            update_post_meta($pid, '_sold_individually', 'yes');
            update_post_meta($pid, '_downloadable', $product_type);
            update_post_meta($pid, '_virtual', $product_type);
            $terms          = array('exclude-from-catalog', 'exclude-from-search');
            wp_set_object_terms($pid, $terms, 'product_visibility');
            wp_set_post_terms($pid, $product_cat_ids, 'product_cat');
            update_post_meta($post_id, 'check_if_run_once', true);
        }
    }
}
add_action('wp_insert_post', 'mep_on_post_publish', 10, 3);

if (!function_exists('mep_count_hidden_wc_product')) {
    function mep_count_hidden_wc_product($event_id) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'link_mep_event',
                    'value' => $event_id,
                    'compare' => '='
                )
            )
        );
        $loop = new WP_Query($args);
        // print_r($loop->posts);
        return $loop->post_count;
    }
}

add_action('save_post', 'mep_wc_link_product_on_save', 99, 1);
if (!function_exists('mep_wc_link_product_on_save')) {
    function mep_wc_link_product_on_save($post_id) {
        add_filter('wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5);
        if (get_post_type($post_id) == 'mep_events') {

            if (!isset($_POST['mep_event_reg_btn_nonce']) ||
                !wp_verify_nonce($_POST['mep_event_reg_btn_nonce'], 'mep_event_reg_btn_nonce')) {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
            $event_name = get_the_title($post_id);

            if (mep_count_hidden_wc_product($post_id) == 0 || empty(get_post_meta($post_id, 'link_wc_product', true))) {
                mep_create_hidden_event_product($post_id, $event_name);
            }
            $product_cat_ids = wp_get_post_terms($post_id, 'product_cat', array('fields' => 'ids'));

            $product_id = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : $post_id;
            set_post_thumbnail($product_id, get_post_thumbnail_id($post_id));
            wp_publish_post($product_id);

            $product_type = mep_get_option('mep_event_product_type', 'single_event_setting_sec', 'yes');

            $_tax_status = isset($_POST['_tax_status']) ? sanitize_text_field($_POST['_tax_status']) : 'none';
            $_tax_class  = isset($_POST['_tax_class']) ? sanitize_text_field($_POST['_tax_class']) : '';
            $sku 		 = isset($_POST['mep_event_sku']) ? sanitize_text_field($_POST['mep_event_sku']) : $product_id;
            
            update_post_meta($product_id, '_sku', $sku);
 
            update_post_meta($product_id, '_tax_status', $_tax_status);
            update_post_meta($product_id, '_tax_class', $_tax_class);
            update_post_meta($product_id, '_stock_status', 'instock');
            update_post_meta($product_id, '_manage_stock', 'no');
            update_post_meta($product_id, '_virtual', $product_type);
            update_post_meta($product_id, '_sold_individually', 'yes');
            update_post_meta($product_id, '_downloadable', $product_type);



            wp_set_post_terms($product_id, $product_cat_ids, 'product_cat');
            $terms = array('exclude-from-catalog', 'exclude-from-search');
            wp_set_object_terms($product_id, $terms, 'product_visibility');
            // Update post
            $my_post = array(
                'ID'            => $product_id,
                'post_title'    => $event_name, // new title
                'post_name'     => uniqid()// do your thing here
            );

            // unhook this function so it doesn't loop infinitely
            remove_action('save_post', 'mep_wc_link_product_on_save');
            // update the post, which calls save_post again
            wp_update_post($my_post);
            // re-hook this function
            add_action('save_post', 'mep_wc_link_product_on_save');
            // Update the post into the database

        }

    }
}

add_action('admin_head', 'mep_hide_date_from_order_page');
if (!function_exists('mep_hide_date_from_order_page')) {
    function mep_hide_date_from_order_page() {
        $product_id = [];
        $hide_wc       = mep_get_option('mep_show_hidden_wc_product', 'general_setting_sec', 'no');    
        $args = array(
            'post_type'         => 'mep_events',
            'posts_per_page'    => -1
        );
        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            $product_id[] = get_post_meta($post_id, 'link_wc_product', true) ? '.woocommerce-admin-page .post-' . get_post_meta($post_id, 'link_wc_product', true) . '.type-product' : '';
        }
        $product_id = array_filter($product_id);
        $parr = implode(', ', $product_id);
        if($hide_wc == 'no'){
            echo '<style> ' . esc_html($parr) . '{display:none!important}' . ' </style>';
        }
    }
}

add_action('init','mep_get_all_hidden_product_id_array');
function mep_get_all_hidden_product_id_array() {
    $product_id = [];
    $args = array(
        'post_type' => 'mep_events',
        'posts_per_page' => -1
    );
    $qr = new WP_Query($args);
    foreach ($qr->posts as $result) {
        $post_id = $result->ID;
        $product_id[] = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : '';
    }
    $product_id = array_filter($product_id);
    return $product_id;
}
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'mep_get_all_hidden_product_id_array' );



// add_action('parse_query', 'mep_product_tags_sorting_query');
if (!function_exists('mep_product_tags_sorting_query')) {
    function mep_product_tags_sorting_query($query) {
        global $pagenow;
        $taxonomy = 'product_visibility';
        $q_vars = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == 'product') {
            $tax_query = array(
                [
                    'taxonomy' => 'product_visibility',
                    'field' => 'slug',
                    'terms' => 'exclude-from-catalog',
                    'operator' => 'NOT IN',
                ],
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => 'uncategorized	',
                    'operator' => 'NOT IN',
                ]
            );
            $query->set('tax_query', $tax_query);
        }

    }
}


add_action('wp_head', 'mep_exclude_hidden_product_from_search_engine');
if (!function_exists('mep_exclude_hidden_product_from_search_engine')) {
function mep_exclude_hidden_product_from_search_engine() {
    global $post;
    if (is_single() && is_product()) {
        $post_id = $post->ID;
        $visibility = get_the_terms($post_id, 'product_visibility') ? get_the_terms($post_id, 'product_visibility') : [0];
        if (is_object($visibility[0])) {
            if ($visibility[0]->name == 'exclude-from-catalog') {
                $check_event_hidden = get_post_meta($post_id, 'link_mep_event', true) ? get_post_meta($post_id, 'link_mep_event', true) : 0;
                if ($check_event_hidden > 0) {
                    echo '<meta name="robots" content="noindex, nofollow">';
                }
            }
        }
    }
}
}

add_action('wp', 'mep_hide_hidden_product_from_single', 90);
if (!function_exists('mep_hide_hidden_product_from_single')) {
    function mep_hide_hidden_product_from_single() {
        global $post, $wp_query;
        if (is_product()) {
            $post_id = $post->ID;
            $visibility = get_the_terms($post_id, 'product_visibility') ? get_the_terms($post_id, 'product_visibility') : [0];
            if (is_object($visibility[0])) {
                if ($visibility[0]->name == 'exclude-from-catalog') {
                    $check_event_hidden = get_post_meta($post_id, 'link_mep_event', true) ? get_post_meta($post_id, 'link_mep_event', true) : 0;
                    if ($check_event_hidden > 0) {
                        $wp_query->set_404();
                        status_header(404);
                        get_template_part(404);
                        exit();
                    }
                }
            }
        }
    }
}


if (!function_exists('get_event_list_js')) {
    function get_event_list_js($id, $event_meta, $currency_pos) {
        ob_start();
        ?>
        <script>
            jQuery(document).ready(function () {


                jQuery(document).on("change", ".etp_<?php echo esc_attr($id); ?>", function () {
                    var sum = 0;
                    jQuery(".etp_<?php echo esc_attr($id); ?>").each(function () {
                        sum += +jQuery(this).val();
                    });
                    jQuery("#ttyttl_<?php echo esc_attr($id); ?>").html(sum);
                });

                jQuery(".extra-qty-box_<?php echo esc_attr($id); ?>").on('change', function () {
                    var sum = 0;
                    var total = <?php if ($event_meta['_price'][0]) {
                        echo esc_attr($event_meta['_price'][0]);
                    } else {
                        echo 0;
                    } ?>;

                    jQuery('.price_jq_<?php echo esc_attr($id); ?>').each(function () {
                        var price = jQuery(this);
                        var count = price.closest('tr').find('.extra-qty-box_<?php echo esc_attr($id); ?>');
                        sum = (price.html() * count.val());
                        total = total + sum;
                        // price.closest('tr').find('.cart_total_price').html(sum + "â‚´");

                    });
                    jQuery('#rowtotal_<?php echo esc_attr($id); ?>').val(total);
                    jQuery('#usertotal_<?php echo esc_attr($id); ?>').html(mp_event_wo_commerce_price_format(total));


                }).change(); //trigger change event on page load


                <?php
                $mep_event_ticket_type = get_post_meta($id, 'mep_event_ticket_type', true);
                if($mep_event_ticket_type){
                $count = 1;
                foreach ( $mep_event_ticket_type as $field ) {
                $qm = mep_remove_apostopie($field['option_name_t']);
                ?>

                //jQuery('.btn-mep-event-cart').hide();

                jQuery('.btn-mep-event-cart_<?php echo esc_attr($id); ?>').attr('disabled', 'disabled');

                jQuery('#eventpxtp_<?php echo esc_attr($id); ?>_<?php echo esc_attr($count); ?>').on('change', function () {

                    var inputs = jQuery("#ttyttl_<?php echo esc_attr($id); ?>").html() || 0;
                    var inputs = jQuery('#eventpxtp_<?php echo esc_attr($id); ?>_<?php echo esc_attr($count); ?>').val() || 0;
                    var input = parseInt(inputs);
                    var children = jQuery('#dadainfo_<?php echo esc_attr($count); ?> > div').length || 0;

                    jQuery(document).on("change", ".etp_<?php echo esc_attr($id); ?>", function () {
                        var TotalQty = 0;
                        jQuery(".etp_<?php echo esc_attr($id); ?>").each(function () {
                            TotalQty += +jQuery(this).val();
                        });
                        //alert(sum);

                        if (TotalQty == 0) {
                            //jQuery('.btn-mep-event-cart').hide();
                            jQuery('.btn-mep-event-cart_<?php echo esc_attr($id); ?>').attr('disabled', 'disabled');
                            jQuery('#mep_btn_notice_<?php echo esc_attr($id); ?>').show();
                        } else {
                            //jQuery('.btn-mep-event-cart').show();
                            jQuery('.btn-mep-event-cart_<?php echo esc_attr($id); ?>').removeAttr('disabled');
                            jQuery('#mep_btn_notice_<?php echo esc_attr($id); ?>').hide();
                        }

                    });

                    if (input < children) {
                        let target=jQuery('#dadainfo_<?php echo esc_attr($count); ?>');
                        while (input < children) {
                            target.children().last().remove();
                            children--;
                        }
                    } else {
                        for (let i = children + 1; i <= input; i++) {

                            let target=jQuery(this).closest('tr').next().find('[name="mp_form_builder_same_attendee"]');
                            if (target.is(":checked")) {
                                jQuery('#dadainfo_<?php echo esc_attr($count); ?>').append(
                                    jQuery('<div/>').attr("id", "newDiv" + i).html("<?php do_action('mep_reg_fields', $id); ?>").css('display','none')
                                );
                            }else{
                                jQuery('#dadainfo_<?php echo esc_attr($count); ?>').append(
                                    jQuery('<div/>').attr("id", "newDiv" + i).html("<?php do_action('mep_reg_fields', $id); ?>")
                                );
                            }
                        }
                    }
                });
                <?php
                $count++;
                }
                }else{
                ?>

                jQuery('#mep_btn_notice_<?php echo esc_attr($id); ?>').hide();

                jQuery('#quantity_5a7abbd1bff73').on('change', function () {
                    var input = jQuery('#quantity_5a7abbd1bff73').val() || 0;
                    var children = jQuery('#divParent > div').length || 0;

                    if (input < children) {
                        jQuery('#divParent').empty();
                        children = 0;
                    }
                    for (var i = children + 1; i <= input; i++) {
                        jQuery('#divParent').append(
                            jQuery('<div/>')
                                .attr("id", "newDiv" + i)
                                .html("<?php do_action('mep_reg_fields', $id); ?>")
                        );
                    }
                });
                <?php
                }
                ?>
            });
        </script>

        <?php
        echo ob_get_clean();
    }
}

if (!function_exists('mep_set_email_content_type')) {
    function mep_set_email_content_type() {
        return "text/html";
    }
}
add_filter('wp_mail_content_type', 'mep_set_email_content_type');


add_filter('woocommerce_cart_item_price', 'mep_avada_mini_cart_price_fixed', 100, 3);
if (!function_exists('mep_avada_mini_cart_price_fixed')) {
    function mep_avada_mini_cart_price_fixed($price, $cart_item, $r) {
        if (array_key_exists('event_id', $cart_item) && get_post_type($cart_item['event_id']) == 'mep_events') {
            $price = wc_price(mep_get_price_including_tax($cart_item['event_id'], $cart_item['event_tp']));
        }
        return $price;
    }
}

if (!function_exists('mage_array_strip')) {
    function mage_array_strip($array_or_string) {
        if (is_string($array_or_string)) {
            $array_or_string = sanitize_text_field(htmlentities(nl2br($array_or_string)));
        } elseif (is_array($array_or_string)) {
            foreach ($array_or_string as $key => &$value) {
                if (is_array($value)) {
                    $value = mage_array_strip($value);
                } else {
                    $value = sanitize_text_field(htmlentities(nl2br($value)));
                }
            }
        }
        return $array_or_string;
    }
}


if (!function_exists('mage_array_sanitize')) {
function mage_array_sanitize($string, $allowed_tags = null) {
    if (is_array($string)) {
        foreach ($string as $k => $v) {
            $string[$k] = mage_array_sanitize($v, $allowed_tags);
        }
        return $string;
    }
    return sanitize_text_field($string, $allowed_tags);
}
}


/**
 * The Giant SEO Plugin Yoast PRO doing some weird thing and that is its auto create a 301 redirect url when delete a post its causing our event some issue Thats why i disable those part for our event post type with the below filter hoook which is provide by Yoast.
 */
add_filter('wpseo_premium_post_redirect_slug_change', '__return_true');
add_filter('wpseo_premium_term_redirect_slug_change', '__return_true');
add_filter('wpseo_enable_notification_term_slug_change', '__return_false');


if (!function_exists('mep_event_get_the_content')) {
    function mep_event_get_the_content($post = 0) {
        $post = get_post($post);
        return (!empty(apply_filters('the_content', $post->post_content)));
    }
}



function mep_string_sanitize($s) {

    $str = str_replace(array('\'', '"'), '', $s); 
    return $str;

}

/**
 * We added event id with every order for using in the attendee & seat inventory calculation, but this info was showing in the thank you page, so i decided to hide this, and here is the fucntion which will hide the event id from the thank you page.
 */
add_filter('woocommerce_order_item_get_formatted_meta_data', 'mep_hide_event_order_meta_in_emails');
if (!function_exists('mep_hide_event_order_meta_in_emails')) {
    function mep_hide_event_order_meta_in_emails($meta) {
        if (!is_admin()) {
            $criteria = array('key' => 'event_id');
            $meta = wp_list_filter($meta, $criteria, 'NOT');
        }
        return $meta;
    }
}
add_filter('woocommerce_order_item_get_formatted_meta_data', 'mep_hide_event_order_data_from_thankyou_and_email', 10, 1);
if (!function_exists('mep_hide_event_order_data_from_thankyou_and_email')) {
    function mep_hide_event_order_data_from_thankyou_and_email($formatted_meta) {

        $hide_location_status   = mep_get_option('mep_hide_location_from_order_page', 'general_setting_sec', 'no');
        $hide_date_status       = mep_get_option('mep_hide_date_from_order_page', 'general_setting_sec', 'no');
        $location_text          = mep_get_option('mep_location_text', 'label_setting_sec', esc_html__('Location', 'mage-eventpress'));
        $date_text              = mep_get_option('mep_event_date_text', 'label_setting_sec', esc_html__('Date', 'mage-eventpress'));
        $hide_location          = $hide_location_status == 'yes' ? array($location_text) : array();
        $hide_date              = $hide_date_status == 'yes' ? array($date_text) : array();
        $default                = array('event_id');
        $default                = array_merge($default, $hide_date);
        $hide_them              = array_merge($default, $hide_location);
        $temp_metas             = [];

        foreach ($formatted_meta as $key => $meta) {
            if (isset($meta->key) && !in_array($meta->key, $hide_them)) {
                $temp_metas[$key] = $meta;
            }
        }
        return $temp_metas;
    }
}


/**
 * This will create a new section Custom CSS into the Event Settings Page, I write this code here instead of the Admin Settings Class because of YOU! Yes who is reading this comment!! to get the clear idea how you can craete your own settings section and settings fields by using the filter hook from any where or your own plugin. Thanks For reading this comment. Cheers!!
 */
add_filter('mep_settings_sec_reg', 'mep_custom_css_settings_reg', 90);
if (!function_exists('mep_custom_css_settings_reg')) {
    function mep_custom_css_settings_reg($default_sec) {
        $sections = array(
            array(
                'id' => 'mep_settings_custom_css',
                'title' => '<i class="fa fa-file-code"></i>'.__('Custom CSS', 'mage-eventpress')
            ),
            array(
                'id' => 'mep_settings_licensing',
                'title' => __('License', 'mage-eventpress')
            )
        );
        return array_merge($default_sec, $sections);
    }
}
add_filter('mep_settings_sec_fields', 'mep_custom_css_sectings_fields', 90);
if (!function_exists('mep_custom_css_sectings_fields')) {
    function mep_custom_css_sectings_fields($default_fields) {
        $settings_fields = array(
            'mep_settings_custom_css' => array(
                array(
                    'name' => 'mep_custom_css',
                    'label' => __('Custom CSS', 'mage-eventpress'),
                    'desc' => __('Please enter your custom CSS code below. Do not include the STYLE tag here.', 'mage-eventpress'),
                    'type' => 'textarea',

                )
            )
        );
        return array_merge($default_fields, $settings_fields);
    }
}

if (!function_exists('mep_get_ticket_type_price_by_name')) {
function mep_get_ticket_type_price_by_name($name, $event_id) {
    $ticket_type_arr = get_post_meta($event_id, 'mep_event_ticket_type', true) ? get_post_meta($event_id, 'mep_event_ticket_type', true) : [];
    $p = '';
    foreach ($ticket_type_arr as $price) {
        $TicketName = str_replace("'", "", $price['option_name_t']);
        if ($TicketName === $name) {
            $p = array_key_exists('option_price_t',$price) ? esc_html($price['option_price_t']) : 0;
        }
    }
    return $p;
}
}

if (!function_exists('mep_get_ticket_type_price_arr')) {
function mep_get_ticket_type_price_arr($ticket_type, $event_id) {
    $price = [];
    foreach ($ticket_type as $ticket) {
        $price[] = mep_get_ticket_type_price_by_name(stripslashes($ticket), $event_id);
    }

    return $price;
}
}

if (!function_exists('mep_get_ticket_name')) {
    function mep_get_ticket_name($name) {    
        // if (function_exists('mep_sp_not_active_warning')) { 
        //     $ticket = explode('_', $name);
        //     return $ticket[0];
        // }else{
        //     return $name;
        // }  
        $ticket = explode('_', $name);
        return $ticket[0];                
    } 
}

if (!function_exists('mep_get_seat_name')) {
function mep_get_seat_name($name) {
    $ticket = explode('_', $name);
    return $ticket[1];
}
}

if (!function_exists('mep_get_orginal_ticket_name')) {
function mep_get_orginal_ticket_name($names) {
    $name = [];
    foreach ($names as $_names) {
        $name[] = mep_get_ticket_name($_names);
    }
    return $name;
}
}

if (!function_exists('mep_cart_ticket_type')) {
    function mep_cart_ticket_type($type, $total_price, $product_id) {

        $mep_event_start_date = isset($_POST['mep_event_start_date']) ? mage_array_strip($_POST['mep_event_start_date']) : array();
        $names = isset($_POST['option_name']) ? mage_array_strip($_POST['option_name']) : array();

        $qty = isset($_POST['option_qty']) ? mage_array_strip($_POST['option_qty']) : array();
        $max_qty = isset($_POST['max_qty']) ? mage_array_strip($_POST['max_qty']) : array();
        $price = mep_get_ticket_type_price_arr(mep_get_orginal_ticket_name($names), $product_id);
        $count = count($names);
        $ticket_type_arr = [];
            
        $vald = 0;
        if (sizeof($names) > 0) {
            for ($i = 0; $i < $count; $i++) {
                if ($qty[$i] > 0) {
                    $ticket_type_arr[$i]['ticket_name'] = !empty($names[$i]) ? stripslashes(strip_tags($names[$i])) : '';

                    $ticket_type_arr[$i]['ticket_price'] = !empty($price[$i]) ? stripslashes(strip_tags($price[$i])) : '';
                    $ticket_type_arr[$i]['ticket_qty'] = !empty($qty[$i]) ? stripslashes(strip_tags($qty[$i])) : '';
                    $ticket_type_arr[$i]['max_qty'] = !empty($max_qty[$i]) ? stripslashes(strip_tags($max_qty[$i])) : '';
                    $ticket_type_arr[$i]['event_date'] = !empty($mep_event_start_date[$i]) ? stripslashes(strip_tags($mep_event_start_date[$i])) : '';
                    $opttprice = ( (float) $price[$i] * (float) $qty[$i]);
                    $total_price = ( (float) $total_price + (float) $opttprice);
                    $validate[$i]['validation_ticket_qty'] = $vald + stripslashes(strip_tags($qty[$i]));
                    $validate[$i]['event_id'] = stripslashes(strip_tags($product_id));
                }
            }
        }


        if ($type == 'ticket_price') {
            return $total_price;
        } elseif ($type == 'validation_data') {
            return $validate;
        } else {
            return apply_filters('mep_cart_ticket_type_data_prepare', $ticket_type_arr, $type, $total_price, $product_id);
        }
    }
}

if (!function_exists('mep_get_event_extra_price_by_name')) {
function mep_get_event_extra_price_by_name($name, $event_id) {
    $ticket_type_arr = get_post_meta($event_id, 'mep_events_extra_prices', true) ? get_post_meta($event_id, 'mep_events_extra_prices', true) : [];

    foreach ($ticket_type_arr as $price) {
        if ($price['option_name'] === $name) {
            $p = $price['option_price'];
        }
    }
    return $p;
}
}

if (!function_exists('mep_get_extra_price_arr')) {
function mep_get_extra_price_arr($ticket_type, $event_id) {
    $price = [];
    foreach ($ticket_type as $ticket) {
        $price[] = mep_get_event_extra_price_by_name($ticket, $event_id);
    }
    return $price;
}
}

if (!function_exists('mep_cart_event_extra_service')) {
    function mep_cart_event_extra_service($type, $total_price, $product_id) {
        $mep_event_start_date_es        = isset($_POST['mep_event_start_date_es']) ? mage_array_strip($_POST['mep_event_start_date_es']) : array();
        $extra_service_name             = isset($_POST['event_extra_service_name']) ? mage_array_strip($_POST['event_extra_service_name']) : array();
        $extra_service_qty              = isset($_POST['event_extra_service_qty']) ? mage_array_strip($_POST['event_extra_service_qty']) : array();
        $extra_service_price            = isset($_POST['event_extra_service_price']) ? mage_array_strip($_POST['event_extra_service_price']) : array();
        $extra_service_price            = mep_get_extra_price_arr($extra_service_name, $product_id);
        $event_extra                    = [];

        if ($extra_service_name) {
            for ($i = 0; $i < count($extra_service_name); $i++) {
                if ($extra_service_qty[$i] > 0) {
                    $event_extra[$i]['service_name']    = !empty($extra_service_name[$i]) ? stripslashes(strip_tags($extra_service_name[$i])) : '';
                    $event_extra[$i]['service_price']   = !empty($extra_service_price[$i]) ? stripslashes(strip_tags($extra_service_price[$i])) : '';
                    $event_extra[$i]['service_qty']     = !empty($extra_service_qty[$i]) ? stripslashes(strip_tags($extra_service_qty[$i])) : '';
                    $event_extra[$i]['event_date']      = !empty($mep_event_start_date_es[$i]) ? stripslashes(strip_tags($mep_event_start_date_es[$i])) : '';
                    $extprice                           = ( (float) $extra_service_price[$i] * (float) $extra_service_qty[$i]);
                    $total_price                        = ( (float) $total_price + (float) $extprice);
                }
            }
        }
        if ($type == 'ticket_price') {
            return $total_price;
        } else {
            return $event_extra;
        }
    }
}


if (!function_exists('mep_get_user_custom_field_ids')) {
function mep_get_user_custom_field_ids($event_id) {
    $reg_form_id = mep_fb_get_reg_form_id($event_id);
    $mep_form_builder_data = get_post_meta($reg_form_id, 'mep_form_builder_data', true) ? get_post_meta($reg_form_id, 'mep_form_builder_data', true) : [];
    $form_id = [];
    // print_r($mep_form_builder_data); mep_fbc_label
    if (sizeof($mep_form_builder_data) > 0) {
        foreach ($mep_form_builder_data as $_field) {
            $form_id[$_field['mep_fbc_label']] = $_field['mep_fbc_id'];
        }
    }
    return $form_id;
}
}


if (!function_exists('mep_get_reg_label')) {
function mep_get_reg_label($_event_id, $name = '') {

    $custom_forms_id = mep_get_user_custom_field_ids($_event_id);
    $event_id     = mep_fb_get_reg_form_id( $_event_id );


    if ($name == 'Name') {
        return get_post_meta($event_id, 'mep_name_label', true) ? get_post_meta($event_id, 'mep_name_label', true) : esc_html__('Name', 'mage-eventpress');
    } elseif ($name == 'Email') {
        return get_post_meta($event_id, 'mep_email_label', true) ? get_post_meta($event_id, 'mep_email_label', true) : esc_html__('Email', 'mage-eventpress');
    } elseif ($name == 'Phone') {
        return get_post_meta($event_id, 'mep_phone_label', true) ? get_post_meta($event_id, 'mep_phone_label', true) : esc_html__('Phone', 'mage-eventpress');
    } elseif ($name == 'Address') {
        return get_post_meta($event_id, 'mep_address_label', true) ? get_post_meta($event_id, 'mep_address_label', true) : esc_html__('Address', 'mage-eventpress');
    } elseif ($name == 'T-Shirt Size') {
        return get_post_meta($event_id, 'mep_tshirt_label', true) ? get_post_meta($event_id, 'mep_tshirt_label', true) : esc_html__('T-Shirt Size', 'mage-eventpress');
    } elseif ($name == 'Gender') {
        return get_post_meta($event_id, 'mep_gender_label', true) ? get_post_meta($event_id, 'mep_gender_label', true) : esc_html__('Gender', 'mage-eventpress');
    } elseif ($name == 'Company') {
        return get_post_meta($event_id, 'mep_company_label', true) ? get_post_meta($event_id, 'mep_company_label', true) : esc_html__('Company', 'mage-eventpress');
    } elseif ($name == 'Designation') {
        return get_post_meta($event_id, 'mep_desg_label', true) ? get_post_meta($event_id, 'mep_desg_label', true) : esc_html__('Designation', 'mage-eventpress');
    } elseif ($name == 'Website') {
        return get_post_meta($event_id, 'mep_website_label', true) ? get_post_meta($event_id, 'mep_website_label', true) : esc_html__('Website', 'mage-eventpress');
    } elseif ($name == 'Vegetarian') {
        return get_post_meta($event_id, 'mep_veg_label', true) ? get_post_meta($event_id, 'mep_veg_label', true) : esc_html__('Vegetarian', 'mage-eventpress');
    } else {
        return null;
    }

}
}


if (!function_exists('mep_cart_display_user_list')) {
    function mep_cart_display_user_list($user_info, $event_id) {
        $custom_forms_id = mep_get_user_custom_field_ids($event_id);
        $form_id     = mep_fb_get_reg_form_id( $event_id );
        ob_start();
        $recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
        $time_status = get_post_meta($event_id, 'mep_disable_ticket_time', true) ? get_post_meta($event_id, 'mep_disable_ticket_time', true) : 'no';



        foreach ($user_info as $userinf) {
            // array_key_exists(
            ?>
            <ul class='mep_cart_user_inforation_details'>
                <?php if (array_key_exists('user_name',$userinf) && !empty($userinf['user_name'])) { ?>
                    <li class='mep_cart_user_name'><?php echo esc_attr(mep_get_reg_label($event_id, 'Name')) . ": ";
                        echo esc_attr($userinf['user_name']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_email',$userinf) && !empty($userinf['user_email'])) { ?>
                    <li class='mep_cart_user_email'><?php echo esc_attr(mep_get_reg_label($event_id, 'Email')) . ": ";
                        echo esc_attr($userinf['user_email']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_phone',$userinf) && !empty($userinf['user_phone'])) { ?>
                    <li class='mep_cart_user_phone'><?php echo esc_attr(mep_get_reg_label($event_id, 'Phone')) . ": ";
                        echo esc_attr($userinf['user_phone']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_address',$userinf) && !empty($userinf['user_address'])) { ?>
                    <li class='mep_cart_user_address'><?php echo esc_attr(mep_get_reg_label($event_id, 'Address')) . ": ";
                        echo esc_attr($userinf['user_address']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_gender',$userinf) && !empty($userinf['user_gender'])) { ?>
                    <li class='mep_cart_user_gender'><?php echo esc_attr(mep_get_reg_label($event_id, 'Gender')) . ": ";
                        echo esc_attr($userinf['user_gender']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_tshirtsize',$userinf) && !empty($userinf['user_tshirtsize'])) { ?>
                    <li class='mep_cart_user_tshirt'><?php echo esc_attr(mep_get_reg_label($form_id, 'T-Shirt Size')) . ": ";
                        echo esc_attr($userinf['user_tshirtsize']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_company',$userinf) && !empty($userinf['user_company'])) { ?>
                    <li class='mep_cart_user_company'><?php echo esc_attr(mep_get_reg_label($event_id, 'Company')) . ": ";
                        echo esc_attr($userinf['user_company']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_designation',$userinf) && !empty($userinf['user_designation'])) { ?>
                    <li class='mep_cart_user_designation'><?php echo esc_attr(mep_get_reg_label($event_id, 'Designation')) . ": ";
                        echo esc_attr($userinf['user_designation']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_website',$userinf) && !empty($userinf['user_website'])) { ?>
                    <li class='mep_cart_user_website'><?php echo esc_attr(mep_get_reg_label($event_id, 'Website')) . ": ";
                        echo esc_attr($userinf['user_website']); ?></li> <?php } ?>
                <?php if (array_key_exists('user_vegetarian',$userinf) && !empty($userinf['user_vegetarian'])) { ?>
                    <li class='mep_cart_user_vegitarian'>
						<?php
                        $vegetarian=strtolower($userinf['user_vegetarian'])=='yes'?esc_html__('Yes','mage-eventpress'):esc_html__('No','mage-eventpress');
                        echo esc_attr(mep_get_reg_label($event_id, 'Vegetarian')) . ": ";
                        echo esc_html($vegetarian);
                        ?>
				</li> <?php } ?>
                <?php if (sizeof($custom_forms_id) > 0) {
                    foreach ($custom_forms_id as $key => $value) {
                        ?>
                        <li><?php esc_html_e($key, 'mage-eventpress');
                            echo ": " . esc_attr($userinf[$value]); ?></li>
                        <?php
                    }
                } ?>
                <?php if ($userinf['user_ticket_type']) { ?>
                    <li class='mep_cart_user_ticket_type'><?php esc_html_e('Ticket Type', 'mage-eventpress');
                        echo ": " . esc_attr($userinf['user_ticket_type']); ?></li> <?php } ?>

                <?php if ($recurring == 'everyday' && $time_status == 'no') { ?>
                    <li class='mep_cart_user_date'><?php
                        esc_html_e(' Date', 'mage-eventpress');
                        echo ": "; ?><?php echo esc_attr(get_mep_datetime($userinf['user_event_date'], 'date-text')); ?></li>
                <?php } else { ?>
                    <li class='mep_cart_user_date'><?php
                        esc_html_e(' Date', 'mage-eventpress');
                        echo ": "; ?><?php echo esc_attr(get_mep_datetime($userinf['user_event_date'], 'date-time-text')); ?></li>
                <?php } ?>
            </ul>
            <?php
        }
        return apply_filters('mep_display_user_info_in_cart_list', ob_get_clean(), $user_info);
    }
}


if (!function_exists('mep_cart_display_ticket_type_list')) {
    function mep_cart_display_ticket_type_list($ticket_type_arr, $eid) {
        ob_start();
        foreach ($ticket_type_arr as $ticket) {
            echo '<li>' . esc_attr($ticket['ticket_name']) . " - " . wc_price(esc_attr(mep_get_price_including_tax($eid, (float)$ticket['ticket_price']))) . ' x ' . esc_attr($ticket['ticket_qty']) . ' = ' . wc_price(esc_attr(mep_get_price_including_tax($eid, (float)$ticket['ticket_price'] * (float)$ticket['ticket_qty']))) . '</li>';
        }
        return apply_filters('mep_display_ticket_in_cart_list', ob_get_clean(), $ticket_type_arr, $eid);
    }
}


if (!function_exists('mep_cart_order_data_save_ticket_type')) {
    function mep_cart_order_data_save_ticket_type($item, $ticket_type_arr, $eid) {
        foreach ($ticket_type_arr as $ticket) {
            $ticket_type_name = $ticket['ticket_name'] . " - " . wc_price(mep_get_price_including_tax($eid, (float)$ticket['ticket_price'])) . ' x ' . $ticket['ticket_qty'] . ' = ';
            $ticket_type_val = wc_price(mep_get_price_including_tax($eid,(float)$ticket['ticket_price'] * (float)$ticket['ticket_qty']));
            $ticket_name_meta = apply_filters('mep_event_order_meta_ticket_name_filter', $ticket_type_name, $ticket);
            $item->add_meta_data($ticket_name_meta, $ticket_type_val);
			do_action('mep_event_cart_order_data_add_ef', $item, $eid,$ticket['ticket_name']);
        }
    }
}


if (!function_exists('mep_get_event_expire_date')) {
    function mep_get_event_expire_date($event_id) {
        $event_expire_on_old = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
        $event_expire_on = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
        $event_start_datetime = get_post_meta($event_id, 'event_start_datetime', true);
        $event_expire_datetime = get_post_meta($event_id, 'event_expire_datetime', true);
        $expire_date = $event_expire_on == 'event_expire_datetime' ? $event_expire_datetime : $event_start_datetime;
        return $expire_date;
    }
}

if (!function_exists('mep_remove_apostopie')) {
function mep_remove_apostopie($string) {
    $str = str_replace("'", '', $string);
    return $str;
}
}


add_action('mep_event_single_template_end', 'mep_single_page_js_script');
add_action('mep_add_to_cart_shortcode_js', 'mep_single_page_js_script');
add_action('mep_event_admin_booking_js', 'mep_single_page_js_script');
if (!function_exists('mep_single_page_js_script')) {
    function mep_single_page_js_script($event_id) {
        $event_id = mep_get_default_lang_event_id($event_id);
        $currency_pos = get_option('woocommerce_currency_pos');
        $mep_event_faq = get_post_meta($event_id, 'mep_event_faq', true) ? maybe_unserialize(get_post_meta($event_id, 'mep_event_faq', true)) : [];
        ob_start();
        ?>
        <script>
            jQuery(document).ready(function () {

                <?php if(sizeof($mep_event_faq) > 0 && !is_admin() ){ ?>
                jQuery("#mep-event-accordion").accordion({
                    collapsible: true,
                    active: false
                });
                <?php } ?>

                jQuery(document).on("change", ".etp", function () {
                    var sum = 0;
                    jQuery(".etp").each(function () {
                        sum += +jQuery(this).val();
                    });
                    jQuery("#ttyttl").html(sum);
                });

                jQuery("#ttypelist").change(function () {
                    vallllp = jQuery(this).val() + "_";
                    var n = vallllp.split('_');
                    var price = n[0];
                    var ctt = 99;
                    if (vallllp != "_") {

                        var currentValue = parseInt(ctt);
                        jQuery('#rowtotal').val(currentValue += parseFloat(price));
                    }
                    if (vallllp == "_") {
                        jQuery('#eventtp').attr('value', 0);
                        jQuery('#eventtp').attr('max', 0);
                        jQuery("#ttypeprice_show").html("")
                    }
                });

                function updateTotal() {
                    var total = 0;
                    vallllp = jQuery(this).val() + "_";
                    var n = vallllp.split('_');
                    var price = n[0];
                    total += parseFloat(price);
                    jQuery('#rowtotal').val(total);
                }

                //Bind the change event
                jQuery(".extra-qty-box").on('change', function () {
                    var sum = 0;
                    var total = 0;
                    jQuery('.price_jq').each(function () {
                        var price = jQuery(this);
                        var count = price.closest('tr').find('.extra-qty-box');
                        sum = (parseFloat(price.html().match(/-?(?:\d+(?:\.\d*)?|\.\d+)/)) * count.val());
                        total = total + sum;
                        // price.closest('tr').find('.cart_total_price').html(sum + "â‚´");

                    });
                    //Fix 27.10.2020 Tony
                    jQuery('#rowtotal').val(total);
                    jQuery('#usertotal').html(mp_event_wo_commerce_price_format(total));

                }).change(); //trigger change event on page load
                <?php
                $mep_event_ticket_type = get_post_meta($event_id, 'mep_event_ticket_type', true) ? get_post_meta($event_id, 'mep_event_ticket_type', true) : array();
                //This is if no ticket type
                if (sizeof($mep_event_ticket_type) > 0 ) {
                //This is if get ticket type
                $count = 1;
                $event_more_date[0]['event_more_start_date'] = date('Y-m-d', strtotime(get_post_meta($event_id, 'event_start_date', true)));
                $event_more_date[0]['event_more_start_time'] = date('H:i', strtotime(get_post_meta($event_id, 'event_start_time', true)));
                $event_more_date[0]['event_more_end_date'] = date('Y-m-d', strtotime(get_post_meta($event_id, 'event_end_date', true)));
                $event_more_date[0]['event_more_end_time'] = date('H:i', strtotime(get_post_meta($event_id, 'event_end_time', true)));
                $event_more_dates = get_post_meta($event_id, 'mep_event_more_date', true) ? get_post_meta($event_id, 'mep_event_more_date', true) : array();
                $recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
                if ($recurring == 'yes' && function_exists('get_mep_re_recurring_date')) {
                    $event_multi_date = array_merge($event_more_date, $event_more_dates);
                } else {
                    $event_multi_date = $event_more_date;
                }
                foreach ($event_multi_date as $event_date) {

                $start_date = $recurring == 'yes' && function_exists('get_mep_re_recurring_date') ? date('Y-m-d H:i:s', strtotime($event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'])) : date('Y-m-d H:i:s', strtotime(mep_get_event_expire_date($event_id)));
                $event_start_date = $recurring == 'yes' && function_exists('get_mep_re_recurring_date') ? date('Y-m-d H:i:s', strtotime($event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'])) : get_post_meta($event_id, 'event_start_datetime', true);

                if (strtotime(current_time('Y-m-d H:i:s')) < strtotime($start_date)) {
                foreach ($mep_event_ticket_type as $field) {
                $ticket_type = mep_remove_apostopie($field['option_name_t']);
                ?>
                var inputs = jQuery("#ttyttl").html() || 0;
                var inputs = jQuery('#eventpxtp_<?php echo esc_attr($count); ?>').val() || 0;
                var input = parseInt(inputs);
                var children = jQuery('#dadainfo_<?php echo esc_attr($count); ?> > div').length || 0;

                var selected_ticket = jQuery('#ttyttl').html();

                if (input < children) {
                    jQuery('#dadainfo_<?php echo esc_attr($count); ?>').empty();
                    children = 0;
                }
                for (var i = children + 1; i <= input; i++) {
                    jQuery('#dadainfo_<?php echo esc_attr($count); ?>').append(
                        jQuery('<div/>')
                            .attr("id", "newDiv" + i)
                            .html("<?php do_action('mep_reg_fields', $event_start_date, $event_id, $ticket_type); ?>")
                    );
                }
                jQuery('#eventpxtp_<?php echo esc_attr($count); ?>').on('change', function () {
                    var inputs = jQuery("#ttyttl").html() || 0;
                    var inputs = jQuery('#eventpxtp_<?php echo esc_attr($count); ?>').val() || 0;
                    var input = parseInt(inputs);
                    var children = jQuery('#dadainfo_<?php echo esc_attr($count); ?> > div').length || 0;
                    jQuery(document).on("change", ".etp", function () {
                        var TotalQty = 0;
                        jQuery(".etp").each(function () {
                            TotalQty += +jQuery(this).val();
                        });
                    });
                    if (input < children) {
                        let target=jQuery('#dadainfo_<?php echo esc_attr($count); ?>');
                        while (input < children) {
                            target.children().last().remove();
                            children--;
                        }
                    } else {
                        for (var i = children + 1; i <= input; i++) {

                            let target=jQuery(this).closest('tr').next().find('[name="mp_form_builder_same_attendee"]');
                            if (target.is(":checked")) {
                                jQuery('#dadainfo_<?php echo esc_attr($count); ?>').append(
                                    jQuery('<div/>').attr("id", "newDiv" + i).html("<?php do_action('mep_reg_fields', $event_start_date, $event_id, $ticket_type); ?>").css('display','none')
                                );
                            }else{
                                jQuery('#dadainfo_<?php echo esc_attr($count); ?>').append(
                                    jQuery('<div/>').attr("id", "newDiv" + i).html("<?php do_action('mep_reg_fields', $event_start_date, $event_id, $ticket_type); ?>")
                                );
                            }
                        }
                    }
                });
                <?php
                $count++;
                }
                }
                }
                }
                ?>
            });
        </script>
        <?php
        echo ob_get_clean();
    }
}


add_action('after-single-events', 'mep_single_page_script');
if (!function_exists('mep_single_page_script')) {
    function mep_single_page_script() {
        ob_start();
        ?>
        <script>
            jQuery('#mep_single_view_all_date').click(function () {
                jQuery(this).hide()
                jQuery('#mep_event_date_sch').addClass('mep_view_all_date');
                jQuery('#mep_single_hide_all_date').show();
            });
            jQuery('#mep_single_hide_all_date').click(function () {
                jQuery(this).hide()
                jQuery('#mep_event_date_sch').removeClass('mep_view_all_date');
                jQuery('#mep_single_view_all_date').show()
            });
        </script>
        <?php
        echo ob_get_clean();
    }
}

if (!function_exists('mep_product_exists')) {
    function mep_product_exists($id) {
        return is_string(get_post_status($id));
    }
}

if (!function_exists('mep_get_event_dates_arr')) {
    function mep_get_event_dates_arr($event_id) {
        $now = current_time('Y-m-d H:i:s');
        $event_start_datetime = get_post_meta($event_id, 'event_start_datetime', true);
        $event_expire_datetime = get_post_meta($event_id, 'event_end_datetime', true);
        $event_more_dates = get_post_meta($event_id, 'mep_event_more_date', true) ? get_post_meta($event_id, 'mep_event_more_date', true) : [];
        $date_arr = array(array(
            'start' => $event_start_datetime,
            'end' => $event_expire_datetime
        ));
        $m_date_arr = [];
        if (sizeof($event_more_dates) > 0) {
            $i = 0;
            foreach ($event_more_dates as $mdate) {
                // if(strtotime($now) < strtotime($mdate['event_more_start_date'].' '.$mdate['event_more_start_time'])){
                $mstart = $mdate['event_more_start_date'] . ' ' . $mdate['event_more_start_time'];
                $mend = $mdate['event_more_end_date'] . ' ' . $mdate['event_more_end_time'];
                $m_date_arr[$i]['start'] = $mstart;
                $m_date_arr[$i]['end'] = $mend;
                // }
                $i++;
            }
        }
        $event_dates = array_merge($date_arr, $m_date_arr);
        return apply_filters('mep_event_dates_in_calender_free', $event_dates, $event_id);
    }
}

add_action('rest_api_init', 'mep_event_cunstom_fields_to_rest_init');
if (!function_exists('mep_event_cunstom_fields_to_rest_init')) {
    function mep_event_cunstom_fields_to_rest_init() {
        register_rest_field('mep_events', 'event_informations', array(
            'get_callback' => 'mep_get_events_custom_meta_for_api',
            'schema' => null,
        ));
    }
}
if (!function_exists('mep_get_events_custom_meta_for_api')) {
    function mep_get_events_custom_meta_for_api($object) {
        $post_id = $object['id'];

        $post_meta = get_post_meta($post_id);
        $post_image = get_post_thumbnail_id($post_id);
        $post_meta["event_feature_image"] = wp_get_attachment_image_src($post_image, 'full')[0];


        return $post_meta;
    }
}

if (!function_exists('mep_elementor_get_tax_term')) {
function mep_elementor_get_tax_term($tax) {
    $terms = get_terms(array(
        'taxonomy' => $tax,
        'hide_empty' => false,
    ));
    $list = array('0' => __('Show All', ''));
    foreach ($terms as $_term) {
        $list[$_term->term_id] = $_term->name;
    }
    return $list;
}
}

if (!function_exists('mep_get_price_excluding_tax')) {
function mep_get_price_excluding_tax($event, $price, $args = array()) {
    $args = wp_parse_args(
        $args,
        array(
            'qty' => '',
            'price' => '',
        )
    );

    $_product = get_post_meta($event, 'link_wc_product', true) ? get_post_meta($event, 'link_wc_product', true) : $event;
    $qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;

    $product = wc_get_product($_product);

    if ('' === $price) {
        return '';
    } elseif (empty($qty)) {
        return 0.0;
    }

    $line_price = (float) $price * (float) $qty;

    if ($product->is_taxable() && wc_prices_include_tax()) {
        $tax_rates = WC_Tax::get_rates($product->get_tax_class());
        $base_tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
        $remove_taxes = apply_filters('woocommerce_adjust_non_base_location_prices', true) ? WC_Tax::calc_tax($line_price, $base_tax_rates, true) : WC_Tax::calc_tax($line_price, $tax_rates, true);
        $return_price = $line_price - array_sum($remove_taxes); // Unrounded since we're dealing with tax inclusive prices. Matches logic in cart-totals class. @see adjust_non_base_location_price.
    } else {
        $return_price = $line_price;
    }
    return apply_filters('woocommerce_get_price_excluding_tax', $return_price, $qty, $product);
}
}


function mep_filter_post_name( $data, $postarr, $unsanitized_postarr){
	$post_id 	= $postarr['ID'];
	$post_type 	= get_post_type($post_id);
	if ($post_type === 'mep_events'){
    	$data['post_title'] = wp_kses_post($data['post_title']);
	}
    return $data;
}
add_filter( 'wp_insert_post_data', 'mep_filter_post_name',10,3);



if (!function_exists('mep_get_price_including_tax')) {
function mep_get_price_including_tax($event, $price, $args = array()) {

    $args = wp_parse_args(
        $args,
        array(
            'qty'   => '',
            'price' => '',
        )
    );

    $_product = get_post_meta($event, 'link_wc_product', true) ? get_post_meta($event, 'link_wc_product', true) : $event;
    // $price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
    $qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;

    $product = wc_get_product($_product);


    $tax_with_price = get_option('woocommerce_tax_display_shop');


    if ('' === $price) {
        return '';
    } elseif (empty($qty)) {
        return 0.0;
    }

    $line_price = (float) $price * (float) $qty;
    $return_price = $line_price;

    if ($product->is_taxable()) {


        if (!wc_prices_include_tax()) {
            $tax_rates = WC_Tax::get_rates($product->get_tax_class());
            $taxes = WC_Tax::calc_tax($line_price, $tax_rates, false);

            // print_r($tax_rates);

            if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {

                $taxes_total = array_sum($taxes);

            } else {

                $taxes_total = array_sum(array_map('wc_round_tax_total', $taxes));
            }

            $return_price = $tax_with_price == 'excl' ? round($line_price, wc_get_price_decimals()) : round($line_price + $taxes_total, wc_get_price_decimals());


        } else {


            $tax_rates = WC_Tax::get_rates($product->get_tax_class());
            $base_tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));

            /**
             * If the customer is excempt from VAT, remove the taxes here.
             * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
             */
            if (!empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) { // @codingStandardsIgnoreLine.
                $remove_taxes = apply_filters('woocommerce_adjust_non_base_location_prices', true) ? WC_Tax::calc_tax($line_price, $base_tax_rates, true) : WC_Tax::calc_tax($line_price, $tax_rates, true);

                if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                    $remove_taxes_total = array_sum($remove_taxes);
                } else {
                    $remove_taxes_total = array_sum(array_map('wc_round_tax_total', $remove_taxes));
                }

                // $return_price = round( $line_price, wc_get_price_decimals() );
                $return_price = round($line_price - $remove_taxes_total, wc_get_price_decimals());
                /**
                 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
                 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
                 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
                 */
            } else {
                $base_taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true);
                $modded_taxes = WC_Tax::calc_tax($line_price - array_sum($base_taxes), $tax_rates, false);

                if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                    $base_taxes_total = array_sum($base_taxes);
                    $modded_taxes_total = array_sum($modded_taxes);
                } else {
                    $base_taxes_total = array_sum(array_map('wc_round_tax_total', $base_taxes));
                    $modded_taxes_total = array_sum(array_map('wc_round_tax_total', $modded_taxes));
                }

                $return_price = $tax_with_price == 'excl' ? round($line_price - $base_taxes_total, wc_get_price_decimals()) : round($line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals());
            }
        }
    }
    // return 0;
    return apply_filters('woocommerce_get_price_including_tax', $return_price, $qty, $product);
}
}


add_filter('wc_price', 'mep_show_custom_text_for_zero_price', 10, 4);
if (!function_exists('mep_show_custom_text_for_zero_price')) {
function mep_show_custom_text_for_zero_price($return, $price, $args, $unformatted_price) {
    $show_free_text = mep_get_option('mep_show_zero_as_free', 'general_setting_sec', 'yes');
    if ($unformatted_price == 0 && $show_free_text == 'yes') {
        $return = mep_get_option('mep_free_price_text', 'label_setting_sec', esc_html__('Free', 'mage-eventpress'));
    }
    return $return;
}
}

if (!function_exists('mep_check_ticket_type_availaility_before_checkout')) {
function mep_check_ticket_type_availaility_before_checkout($event_id, $type, $date) {

    $_user_set_status   = apply_filters('mep_event_seat_reduce_status',mep_get_option('seat_reserved_order_status', 'general_setting_sec', array('processing','completed')));
    $_order_status      = !empty($_user_set_status) ? $_user_set_status : array('processing','completed');
    $order_status       = array_values($_order_status);

    $order_status_filter =      array(
        'key' => 'ea_order_status',
        'value' => $order_status,
        'compare' => 'OR'
    );

    $args = array(
        'post_type' => 'mep_events_attendees',
        'posts_per_page' => -1,

        'meta_query' => array(
            'relation' => 'AND',
            array(
                'relation' => 'AND',
                array(
                    'key' => 'ea_event_id',
                    'value' => $event_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'ea_ticket_type',
                    'value' => $type,
                    'compare' => '='
                ),
                array(
                    'key' => 'ea_event_date',
                    'value' => $date,
                    'compare' => '='
                )
            ), 
            $order_status_filter
        )
    );
    $loop = new WP_Query($args);
    $count = $loop->post_count;
    return $count;
}
}

if (!function_exists('mep_get_list_thumbnail')) {
function mep_get_list_thumbnail($event_id) {

    $thumbnail_id = get_post_meta($event_id, 'mep_list_thumbnail', true) ? get_post_meta($event_id, 'mep_list_thumbnail', true) : 0;


    if ($thumbnail_id > 0) {
        $thumbnail = wp_get_attachment_image_src($thumbnail_id, 'full');


        ?>
        <img src="<?php echo esc_url($thumbnail[0]); ?>" class="attachment-full size-full wp-post-image" alt="<?php echo get_the_title($event_id); ?>"/>
        <?php
    } else {
        echo get_the_post_thumbnail($event_id, 'full');
    }
}
}


add_action('mep_event_list_date_li', 'mep_event_list_upcoming_date_li', 10, 2);
if (!function_exists('mep_event_list_upcoming_date_li')) {
function mep_event_list_upcoming_date_li($event_id, $type = 'grid') {
    $event_date_icon = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'fa fa-calendar');
    $hide_only_end_time_list = mep_get_option('mep_event_hide_end_time_list', 'event_list_setting_sec', 'no');
    $event_start_datetime = get_post_meta($event_id, 'event_start_datetime', true);
    $event_end_datetime = get_post_meta($event_id, 'event_end_datetime', true);
    $event_multidate = get_post_meta($event_id, 'mep_event_more_date', true) ? get_post_meta($event_id, 'mep_event_more_date', true) : [];
    $event_std[] = array(
        'event_std' => $event_start_datetime,
        'event_etd' => $event_end_datetime
    );
    $a = 1;
    if (sizeof($event_multidate) > 0) {
        foreach ($event_multidate as $event_mdt) {
            $event_std[$a]['event_std'] = $event_mdt['event_more_start_date'] . ' ' . $event_mdt['event_more_start_time'];
            $event_std[$a]['event_etd'] = $event_mdt['event_more_end_date'] . ' ' . $event_mdt['event_more_end_time'];
            $a++;
        }
    }
    $cn = 0;
    foreach ($event_std as $_event_std) {
        // print_r($_event_std);
        $std = $_event_std['event_std'];
        $start_date = date('Y-m-d', strtotime($_event_std['event_std']));
        $end_date = date('Y-m-d', strtotime($_event_std['event_etd']));
        if (strtotime(current_time('Y-m-d H:i')) < strtotime($std) && $cn == 0) {
            if ($type == 'grid') {
                ?>
                <li class="mep_list_event_date">
                    <div class="evl-ico"><i class="<?php echo $event_date_icon; ?>"></i></div>
                    <div class="evl-cc">
                        <h5>
                            <?php echo get_mep_datetime($std, 'date-text'); ?>
                        </h5>
                        <h5><?php echo get_mep_datetime($_event_std['event_std'], 'time');
                            if ($hide_only_end_time_list == 'no') { ?> - <?php if ($start_date == $end_date) {
                                echo get_mep_datetime($_event_std['event_etd'], 'time');
                            } else {
                                echo get_mep_datetime($_event_std['event_etd'], 'date-time-text');
                            }
                            } ?></h5>
                    </div>
                </li>
                <?php
            } elseif ($type == 'minimal') {
                ?>
                <span class='mep_minimal_list_date'><i class="<?php echo $event_date_icon; ?>"></i> <?php echo get_mep_datetime($std, 'date-text') . ' ';
                    echo get_mep_datetime($_event_std['event_std'], 'time');
                    if ($hide_only_end_time_list == 'no') { ?> - <?php if ($start_date == $end_date) {
                        echo get_mep_datetime($_event_std['event_etd'], 'time');
                    } else {
                        echo get_mep_datetime($_event_std['event_etd'], 'date-time-text');
                    }
                    } ?></span>
                <?php
            }


            $cn++;
        }
    }
}
}


add_filter('mep_event_confirmation_text', 'mep_virtual_join_info_event_email_text', 10, 3);
if (!function_exists('mep_virtual_join_info_event_email_text')) {
function mep_virtual_join_info_event_email_text($content, $event_id, $order_id) {
    $event_type         = get_post_meta($event_id, 'mep_event_type', true) ? get_post_meta($event_id, 'mep_event_type', true) : 'offline';
    $email_content      = get_post_meta($event_id, 'mp_event_virtual_type_des', true) ? get_post_meta($event_id, 'mp_event_virtual_type_des', true) : '';
    if ($event_type == 'online') {
        $content = $content . '<br/>' . html_entity_decode($email_content);
    }
    return html_entity_decode($content);
}
}

if (!function_exists('mep_fb_get_reg_form_id')) {
function mep_fb_get_reg_form_id($event_id) {
    $global_reg_form = get_post_meta($event_id, 'mep_event_reg_form_id', true) ? get_post_meta($event_id, 'mep_event_reg_form_id', true) : 'custom_form';
    $event_reg_form_id = $global_reg_form == 'custom_form' ? $event_id : $global_reg_form;
    return $event_reg_form_id;
}
}


add_action('init', 'mep_show_product_cat_in_event');
if (!function_exists('mep_show_product_cat_in_event')) {
function mep_show_product_cat_in_event() {
    $pro_cat_status = mep_get_option('mep_show_product_cat_in_event', 'single_event_setting_sec', 'no');
    if ($pro_cat_status == 'yes') {
        register_taxonomy_for_object_type('product_cat', 'mep_events');
    } else {
        return null;
    }
}
}




add_filter('wp_unique_post_slug_is_bad_hierarchical_slug', 'mep_event_prevent_slug_conflict', 10, 4);
add_filter('wp_unique_post_slug_is_bad_flat_slug', 'mep_event_prevent_slug_conflict', 10, 3);
if (!function_exists('mep_event_prevent_slug_conflict')) {
function mep_event_prevent_slug_conflict($is_bad_slug, $slug, $post_type, $post_parent_id = 0) {
    $reserved_top_level_slugs = apply_filters('mep_event_prevent_slug_conflict_arr', array('events'));
    if (0 === $post_parent_id && in_array($slug, $reserved_top_level_slugs)) {
        $is_bad_slug = true;
    }
    return $is_bad_slug;
}
}

if (!function_exists('mep_get_user_list')) {
function mep_get_user_list($name = []) {
    ob_start();
    $editable_roles = get_editable_roles();
    foreach ($editable_roles as $role => $details) {
        $sub['role'] = esc_attr($role);
        $sub['name'] = translate_user_role($details['name']);
        $roles[] = $sub;
        ?>
        <option value="<?php echo esc_attr($role); ?>" <?php if (in_array(esc_attr($role), $name)) {
            echo 'Selected';
        } ?>><?php echo translate_user_role($details['name']); ?></option>
        <?php

    }
    return ob_get_clean();
}
}

if (!function_exists('mep_get_event_add_cart_sec')) {
function mep_get_event_add_cart_sec($post_id) {
    global $event_meta;
    $mep_event_ticket_type = get_post_meta($post_id, 'mep_event_ticket_type', true) ? get_post_meta($post_id, 'mep_event_ticket_type', true) : array();
    $cart_product_id = get_post_meta($post_id, 'link_wc_product', true) ? esc_attr(get_post_meta($post_id, 'link_wc_product', true)) : esc_attr($post_id);
    ?>
    <!-- Register Now Title -->
    <h4 class="mep-cart-table-title">
        <?php echo mep_get_option('mep_register_now_text', 'label_setting_sec',__('Register Now:', 'mage-eventpress')); ?>
    </h4>
    <!--The event add to cart main form start here-->
    <form action="" method='post' id="mage_event_submit" enctype="multipart/form-data">
        <?php
        /**
         * Here is a magic hook which fire just before of the Add to Cart Button, And the Ticket type & Extra service list are hooked up into this, You can find them into inc/template-parts/event_ticket_type_extra_service.php
         */
        do_action('mep_event_ticket_type_extra_service', $post_id);
        ?>
        <input type='hidden' id='rowtotal' value="<?php echo get_post_meta($post_id, "_price", true); ?>"/>
        <input type="hidden" name='currency_symbol' value="<?php echo get_woocommerce_currency_symbol(); ?>">
        <input type="hidden" name='currency_position' value="<?php echo get_option('woocommerce_currency_pos'); ?>">
        <input type="hidden" name='currency_decimal' value="<?php echo wc_get_price_decimal_separator(); ?>">
        <input type="hidden" name='currency_thousands_separator' value="<?php echo wc_get_price_thousand_separator(); ?>">
        <input type="hidden" name='currency_number_of_decimal' value="<?php echo wc_get_price_decimals(); ?>">
        <?php do_action('mep_add_term_condition', $post_id); ?>
        <!--The Add to cart button table start Here-->
        <table class='table table-bordered mep_event_add_cart_table'>
            <tr>
                <td style='text-align:left;' class='total-col'><?php echo mep_get_option('mep_quantity_text', 'label_setting_sec',__('Quantity:', 'mage-eventpress'));
                    if ($mep_event_ticket_type) { ?>
                        <input id="quantity_5a7abbd1bff73" class="input-text qty text extra-qty-box" step="1" min="1" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" type="hidden">
                        <span id="ttyttl"></span>
                    <?php } ?>
                    <span class='the-total'> <?php echo mep_get_option('mep_total_text', 'label_setting_sec', __('Total', 'mage-eventpress')); ?>
                                    <span id="usertotal"></span>
                                </span>
                </td>
                <td style='text-align:right;'>
                    <input type="hidden" name="mep_event_location_cart" value="<?php trim(mep_ev_location_ticket($post_id, $event_meta)); ?>">
                    <input type="hidden" name="mep_event_date_cart" value="<?php do_action('mep_event_date'); ?>">
                    <button type="submit" name="add-to-cart" value="<?php echo esc_attr($cart_product_id); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart"><?php esc_html_e(mep_get_label($post_id, 'mep_cart_btn_text', 'Register For This Event'), 'mage-eventpress'); ?> </button>
                </td>
            </tr>
        </table>
        <!--The Add to cart button table start Here-->
    </form>
    <!--The event add to cart main form end here-->

    <?php
}
}

if (!function_exists('mep_default_sidebar_reg')) {
function mep_default_sidebar_reg() {
$check_sidebar_status = mep_get_option('mep_show_event_sidebar', 'general_setting_sec','disable');

if($check_sidebar_status == 'enable'){
    register_sidebar(array(
        'name' => __('Event Manager For Woocommerce Sidebar', 'mage-eventpress'),
        'id' => 'mep_default_sidebar',
        'description' => __('This is the Default sidebar of the Event manager for Woocommerce  template.', 'mage-eventpress'),
        'before_widget' => '<div id="%1$s" class="mep_sidebar mep_widget_sec widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widgettitle">',
        'after_title' => '</h3>',
    ));
}

}
}
add_action('widgets_init', 'mep_default_sidebar_reg');


function mep_html_chr($string){    
    $find       = ['&','#038;'];
    $replace    = ['and',''];
    return html_entity_decode(str_replace($find,$replace,$string));
    // return str_replace("&","pink",'Test & Time Event');
}


//********************Share button*************//
add_action('mep_after_social_share_list', 'mep_custom_share_btn', 10, 1);
if (!function_exists('mep_custom_share_btn')) {
function mep_custom_share_btn($event_id) {
    $event_ss_linkedin_icon = mep_get_option('mep_event_ss_linkedin_icon', 'icon_setting_sec', 'fab fa-linkedin');
    $event_ss_whatsapp_icon = mep_get_option('mep_event_ss_whatsapp_icon', 'icon_setting_sec', 'fab fa-whatsapp');
    $event_ss_email_icon    = mep_get_option('mep_event_ss_email_icon', 'icon_setting_sec', 'fa fa-envelope');
    ?>
    <li>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo get_the_permalink($event_id); ?>&title=<?php echo mep_esc_html(get_the_title($event_id)) . ' '; ?>&summary=<?php echo esc_html(get_the_excerpt($event_id)); ?>&source=web" target="_blank">
            <i class="<?php echo $event_ss_linkedin_icon; ?>"></i>
        </a>
    </li>    
    <li>
        <a href="https://api.whatsapp.com/send?text=<?php echo mep_esc_html(get_the_title($event_id)) . ' '; ?><?php echo get_the_permalink($event_id); ?>" target="_blank">
            <i class="<?php echo $event_ss_whatsapp_icon; ?>"></i>
        </a>
    </li>
    <li>
        <a href="mailto:?subject=I wanted you to see this site&amp;body=<?php echo mep_esc_html(get_the_title($event_id)) . ' '; ?><?php echo get_the_permalink($event_id); ?>" title="Share by Email">
            <i class="<?php echo $event_ss_email_icon; ?>"></i>
        </a>
    </li>
    <?php
}
}


add_filter('mep_ticket_current_time', 'mep_add_expire_min_in_current_date', 10, 3);
if (!function_exists('mep_add_expire_min_in_current_date')) {
function mep_add_expire_min_in_current_date($current_date, $event_date, $event_id) {

    $minutes_to_add = (int)mep_get_option('mep_ticket_expire_time', 'general_setting_sec', 0);
    
    $time = new DateTime($current_date);
    $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
    $current_date = $time->format('Y-m-d H:i');

    return $current_date;

}
}


if (!function_exists('mep_enable_big_selects_for_queries')) {
function mep_enable_big_selects_for_queries() {
    global $wpdb;
    $wpdb->query('SET SQL_BIG_SELECTS=1');
}
}

add_action('init', 'mep_enable_big_selects_for_queries');
if (!function_exists('mep_get_event_upcoming_date')) {
function mep_get_event_upcoming_date($event_id) {
    $upcoming_date = get_post_meta($event_id, 'event_start_datetime', true) ? get_post_meta($event_id, 'event_start_datetime', true) : '';
    return apply_filters('mep_event_upcoming_date', $upcoming_date, $event_id);
}
}


add_action('mep_event_single_page_after_header', 'mep_update_event_upcoming_date');
if (!function_exists('mep_update_event_upcoming_date')) {
function mep_update_event_upcoming_date($event_id) {
    
    $event_id = !empty($event_id) ? $event_id : get_the_id();
    $current_upcoming_date = get_post_meta($event_id, 'event_upcoming_datetime', true) ? get_post_meta($event_id, 'event_upcoming_datetime', true) : 0;
    $event_upcoming_date = mep_get_event_upcoming_date($event_id);

    if ($current_upcoming_date == 0 || $current_upcoming_date != $event_upcoming_date) {
        update_post_meta($event_id, 'event_upcoming_datetime', $event_upcoming_date);
    } else {
        return null;
    }
}
}

if (!function_exists('mep_license_error_code')) {
function mep_license_error_code($license_data, $item_name = 'this Plugin') {

    switch ($license_data->error) {
        case 'expired':
            $message = sprintf(
                __('Your license key expired on %s.'),
                date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
            );
            break;

        case 'revoked':
            $message = __('Your license key has been disabled.');
            break;

        case 'missing':
            $message = __('Invalid license.');
            break;

        case 'invalid':
        case 'site_inactive':
            $message = __('Your license is not active for this URL.');
            break;

        case 'item_name_mismatch':

            $message = sprintf(__('This appears to be an invalid license key for %s.'), $item_name);
            break;

        case 'no_activations_left':
            $message = __('Your license key has reached its activation limit.');
            break;
        default:

            $message = __('An error occurred, please try again.');
            break;
    }
    return $message;
}
}

if (!function_exists('mep_license_expire_date')) {
function mep_license_expire_date($date) {
    if (empty($date) || $date == 'lifetime') {
        echo esc_html($date);
    } else {
        if (strtotime(current_time('Y-m-d H:i')) < strtotime(date('Y-m-d H:i', strtotime($date)))) {
            echo get_mep_datetime($date, 'date-time-text');
        } else {
            esc_html_e('Expired', 'mage-eventpress');
        }
    }
}
}

if (!function_exists('mep_section_existis')) {
function mep_section_existis($meta_name, $event_id) {
    $services = get_post_meta($event_id, $meta_name, true) ? maybe_unserialize(get_post_meta($event_id, $meta_name, true)) : [];
    if (!empty($services)) {
        return true;
    } else {
        return false;
    }
}
}

if (!function_exists('mep_location_existis')) {
function mep_location_existis($meta_name, $event_id) {

    $location_sts = get_post_meta($event_id, 'mep_org_address', true) ? get_post_meta($event_id, 'mep_org_address', true) : '';
    $org_arr = get_the_terms($event_id, 'mep_org') ? get_the_terms($event_id, 'mep_org') : '';
    $org_id = !empty($org_arr) ? $org_arr[0]->term_id : '';

    if ($meta_name == 'mep_location_venue' && !empty($location_sts)) {
        $meta_name = 'org_location';
    } else {
        $meta_name = $meta_name;
    }

    if ($meta_name == 'mep_street' && !empty($location_sts)) {
        $meta_name = 'org_street';
    } else {
        $meta_name = $meta_name;
    }

    if ($meta_name == 'mep_city' && !empty($location_sts)) {
        $meta_name = 'org_city';
    } else {
        $meta_name = $meta_name;
    }

    if ($meta_name == 'mep_state' && !empty($location_sts)) {
        $meta_name = 'org_state';
    } else {
        $meta_name = $meta_name;
    }

    if ($meta_name == 'mep_postcode' && !empty($location_sts)) {
        $meta_name = 'org_postcode';
    } else {
        $meta_name = $meta_name;
    }

    if ($meta_name == 'mep_country' && !empty($location_sts)) {
        $meta_name = 'org_country';
    } else {
        $meta_name = $meta_name;
    }

    $services = !empty($location_sts) ? get_term_meta($org_id, $meta_name, true) : get_post_meta($event_id, $meta_name, true);

    if (!empty($services)) {
        return true;
    } else {
        return false;
    }

}
}


/***************************
 * Functions Dev by @Ariful
 **************************/
if (!function_exists('mep_elementor_get_events')) {
    function mep_elementor_get_events($default) {
        $args = array('post_type' => 'mep_events',);
        $list = array('0' => $default);
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $list[get_the_id()] = get_the_title();
            }
        }
        wp_reset_postdata();
        return $list;
    }
}

if (!function_exists('mep_get_list_thumbnail_src')) {
    function mep_get_list_thumbnail_src($event_id,$size='full') {

        $thumbnail_id = get_post_meta($event_id, 'mep_list_thumbnail', true) ? get_post_meta($event_id, 'mep_list_thumbnail', true) : 0;

        if ($thumbnail_id > 0) {
            $thumbnail = wp_get_attachment_image_src($thumbnail_id, $size);
            echo esc_attr(is_array($thumbnail) && sizeof($thumbnail) > 0 ? $thumbnail[0] : '');
        } else {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($event_id), $size);
            echo esc_attr(is_array($thumbnail) && sizeof($thumbnail) > 0 ? $thumbnail[0] : '');
        }
    }
}


add_filter('mep_check_product_into_cart', 'mep_disable_add_to_cart_if_product_is_in_cart', 10, 2);
if (!function_exists('mep_disable_add_to_cart_if_product_is_in_cart')) {
    function mep_disable_add_to_cart_if_product_is_in_cart($is_purchasable, $product) {        
        // Loop through cart items checking if the product is already in cart
        if (isset(WC()->cart) && !is_admin() && !empty(WC()->cart->get_cart())) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['data']->get_id() == $product) {
                    return false;
                }
            }
        }
        return $is_purchasable;
    }
}

if (!function_exists('mep_get_default_lang_event_id')) {
    function mep_get_default_lang_event_id($event_id) {
        global $sitepress;
        $multi_lang_plugin = mep_get_option('mep_multi_lang_plugin', 'general_setting_sec', 'none');

        if ($multi_lang_plugin == 'polylang') {
            // Get PolyLang ID
            $defaultLanguage = function_exists('pll_default_language') ? pll_default_language() : get_locale();
            $translations = function_exists('pll_get_post_translations') ? pll_get_post_translations($event_id) : [];
            $event_id = sizeof($translations) > 0 ? $translations[$defaultLanguage] : $event_id;

        } elseif ($multi_lang_plugin == 'wpml') {
            // WPML
            $default_language = function_exists('wpml_loaded') ? $sitepress->get_default_language() : get_locale(); // will return 'en'
            $event_id = apply_filters('wpml_object_id', $event_id, 'mep_events', TRUE, $default_language);
        } else {
            $event_id = $event_id;        
        }
        return $event_id;
    }
}


/**
 * The below function will add the event more date list into the event list shortcode, Bu default it will be hide with a Show Date button, after click on that button it will the full list.
 */
add_action('mep_event_list_loop_footer', 'mep_event_recurring_date_list_in_event_list_loop');
if (!function_exists('mep_event_recurring_date_list_in_event_list_loop')) {
    function mep_event_recurring_date_list_in_event_list_loop($event_id) {
        $_more_dates = get_post_meta($event_id, 'mep_event_more_date', true);
        $more_date = apply_filters('mep_event_date_more_date_array_event_list', $_more_dates, $event_id);
        $show_multidate = mep_get_option('mep_date_list_in_event_listing', 'event_list_setting_sec', 'no');

        if (is_array($more_date) && sizeof($more_date) > 0) {
            ?>
            <?php if ($show_multidate == 'yes') { ?>
                <span class='mep_more_date_btn mep-tem3-title-sec mp_event_visible_event_time'
                      data-event-id="<?php echo esc_attr($event_id); ?>"
                      data-active-text="<?php echo esc_attr(mep_get_option('mep_event_view_more_date_btn_text', 'label_setting_sec', esc_html__('View More Date', 'mage-eventpress'))); ?>"
                      data-hide-text="<?php echo esc_attr(mep_get_option('mep_event_hide_date_list_btn_text', 'label_setting_sec', __('Hide Date Lists', 'mage-eventpress'))); ?>"
                >
            <?php echo mep_get_option('mep_event_view_more_date_btn_text', 'label_setting_sec', __('View More Date', 'mage-eventpress')); ?>
        </span>
            <?php } ?>
            <?php
        }
    }
}

add_action('wp_ajax_mep_event_list_date_schedule', 'mep_event_list_date_schedule');
add_action('wp_ajax_nopriv_mep_event_list_date_schedule', 'mep_event_list_date_schedule');
if (!function_exists('mep_event_list_date_schedule')) {
    function mep_event_list_date_schedule() {
        $event_id           = isset($_POST['event_id']) ? sanitize_text_field($_POST['event_id']) : 0;
        $recurring          = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
        $_more_dates        = get_post_meta($event_id, 'mep_event_more_date', true);
        $more_date          = apply_filters('mep_event_date_more_date_array_event_list', $_more_dates, $event_id);
        $start_datetime     = get_post_meta($event_id, 'event_start_datetime', true);
        $start_date         = get_post_meta($event_id, 'event_start_date', true);
        $end_date           = get_post_meta($event_id, 'event_end_date', true);
        $end_datetime       = get_post_meta($event_id, 'event_end_datetime', true);
        if (is_array($more_date) && sizeof($more_date) > 0) {
            ?>
            <ul class='mp_event_more_date_list'>
                <?php
                if ($recurring == 'everyday') {
                    do_action('mep_event_everyday_date_list_display', $event_id);
                } else {
                    foreach ($more_date as $_more_date) {
                        if (strtotime(current_time('Y-m-d H:i')) < strtotime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'])) {
                            ?>
                            <li>
                            <a href="<?php echo get_the_permalink($event_id).esc_attr('?date=' . strtotime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'])); ?>">
                     <span class='mep-more-date'>
                        <i class="fa fa-calendar"></i>
                        <?php echo get_mep_datetime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'date-text'); ?>
                      </span>
                        <span class='mep-more-time'>
                         <i class="fa fa-clock-o"></i>
                        <?php echo get_mep_datetime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'time'); ?> - <?php if ($_more_date['event_more_start_date'] != $_more_date['event_more_end_date']) {
                                        echo get_mep_datetime($_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'date-text') . ' - ';
                                    }
                            echo get_mep_datetime($_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'time'); 
                            
                        ?>
                      </span>
                                </a>
                            </li>
                            <?php
                        }
                    }
                }
                ?>
            </ul>
            <?php
        }
        die();
    }
}






// Function for create hidden product for bus
if (!function_exists('mep_create_hidden_event_product')) {
function mep_create_hidden_event_product($post_id, $title)
{
    $new_post = array(
        'post_title'    =>   $title,
        'post_content'  =>   '',
        'post_name'     =>   uniqid(),
        'post_category' =>   array(),
        'tags_input'    =>   array(),
        'post_status'   =>   'publish',
        'post_type'     =>   'product'
    );

    $_tax_status        =  'none';   
    $pid                = wp_insert_post($new_post);
    update_post_meta($post_id, 'link_wc_product', $pid);
    update_post_meta($pid, 'link_mep_event', $post_id);
    update_post_meta($pid, '_price', 0.01);             
    update_post_meta($pid, '_tax_status', $_tax_status); 
    update_post_meta($pid, '_sold_individually', 'yes');
    update_post_meta($pid, '_virtual', 'yes');
    $terms = array('exclude-from-catalog', 'exclude-from-search');
    wp_set_object_terms($pid, $terms, 'product_visibility');
    update_post_meta($post_id, 'check_if_run_once', true);
}
}

// Flash Permalink only Once 
if (!function_exists('mep_flash_permalink_once')) {
    function mep_flash_permalink_once()
        {
            if (get_option('mep_flash_event_permalink') != 'completed') {
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
                update_option('mep_flash_event_permalink', 'completed');
            }

            if (get_option('mep_event_seat_left_data_update_01') != 'completed') {

                $args = array(
                    'post_type' => 'mep_events',
                    'posts_per_page' => -1
                );
        
                $qr = new WP_Query($args);
                foreach ($qr->posts as $result) {
                    $post_id = $result->ID;
                    $seat_left =  mep_count_total_available_seat($post_id);
                    update_post_meta($post_id,'mep_total_seat_left',$seat_left);
                }        
                update_option('mep_event_seat_left_data_update_01', 'completed');
            }





        }
}
add_action('admin_init', 'mep_flash_permalink_once');

/******************************************
 * Function: Get User Display Name By Email
 * Developer: Ariful
******************************************/
function mep_get_user_display_name_by_email($email = null){
    // if( empty($email) ){
    //     return false;
    // }
    // else{
    //     $user_obj     = get_user_by('email', $email);
    //     $display_name =  $user_obj->display_name;
        
    //     return $display_name;
    // }


    return get_bloginfo('name');


}

/*******************************************************************
 * Function: Update Value Position from Old Settings to New Settings
 * Developer: Ariful
*********************************************************************/
function mep_change_global_option_section($option_name, $old_sec_name, $new_sec_name, $default = null){
    if(! empty($option_name) && ! empty($old_sec_name) && ! empty($new_sec_name)){
        $chk_new_value = mep_get_option($option_name, $new_sec_name);
        $chk_old_value = mep_get_option($option_name, $old_sec_name);
        $new_sec_array = is_array(get_option( $new_sec_name )) ? maybe_unserialize(get_option( $new_sec_name )) : array();
              
        if(isset($chk_new_value) && ! empty($chk_new_value)){
            return $chk_new_value;
        }
        else{
            if(isset($chk_old_value) && ! empty($chk_old_value)){
                $created_array = array($option_name => $chk_old_value);
                $merged_data = array_merge($new_sec_array,$created_array);
                update_option( $new_sec_name, $merged_data);
            }
        }
    
        if(isset($new_sec_array[$option_name])){
            return $new_sec_array[$option_name];
        }
        else{
            return $default;
        }
    }
}


    if (!function_exists('mep_woo_install_check')) {
        function mep_woo_install_check() {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
            if (is_plugin_active('woocommerce/woocommerce.php')) {
                return 'Yes';
            } elseif (is_dir($plugin_dir)) {
                return 'Installed But Not Active';
            } else {
                return 'No';
            }
        }
    }


add_action( 'pre_get_posts', 'mep_search_query_exlude_hidden_wc_fix' );
function mep_search_query_exlude_hidden_wc_fix( $query ) {
    if ($query->is_search && !is_admin() ) {
        $query -> set( 'tax_query', array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'exclude-from-search',
                'operator' => 'NOT IN',
            )
        ));
  }
  return $query;
}


function mep_check_plugin_installed($path){
    if (is_plugin_active($path)) {
        return true;
    }else{
        return false;
    }
}




add_action('mep_event_tab_before_location','mep_event_tab_before_location_virtual_event');
function mep_event_tab_before_location_virtual_event($post_id){
    $event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
    $event_type 		= get_post_meta($post_id, 'mep_event_type', true);
    $event_member_type 	= get_post_meta($post_id, 'mep_member_only_event', true);   
    $description 		= html_entity_decode(get_post_meta($post_id, 'mp_event_virtual_type_des', true));
    $checked 			= ($event_type == 'online') ? 'checked' : '';     
    ?>
<div>
    <h3 class='mep_virtual_sec_title'><span><?php esc_html_e('Online/Virtual ', 'mage-eventpress'); echo esc_html($event_label . '?');  ?> (No/Yes)</span></h3>
			
				<label class="mp_event_virtual_type_des_switch">
					<input class="mp_opacity_zero" type="checkbox" name="mep_event_type" <?php echo esc_attr($checked); ?> />
					<span class="mep_slider round"></span>
				</label>
				<p class="event_meta_help_txt"><?php _e('If your event is online or virtual, please ensure that this option is enabled.','mage-eventpress'); ?></p>
				<?php do_action('mep_event_details_before_virtual_event_info_text_box',$post_id); ?>
                <label class="mp_event_virtual_type_des <?php echo ($event_type == 'online') ? esc_attr('active') : ''; ?>">
					<?php wp_editor(html_entity_decode(nl2br($description)), 'mp_event_virtual_type_des'); ?>
					<p class="event_meta_help_txt"><?php esc_html_e('Please enter your virtual event joining details in the form below. This information will be sent to the buyer along with a confirmation email.', 'mage-eventpress') ?></p>
				</label>
                <?php do_action('mep_event_details_after_virtual_event_info_text_box',$post_id); ?>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {

    <?php if($checked == 'checked'){ ?>
            jQuery(".mep_event_tab_location_content").hide(200);
    <?php } ?>
    
});
</script>
<?php
}


add_action('mep_event_tab_before_ticket_pricing','mep_event_shortcode_info');
function mep_event_shortcode_info($post_id){
    if($post_id){

		$values = get_post_custom($post_id);
		wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
		$reg_checked = '';
		if (array_key_exists('mep_reg_status', $values)) {
			if ($values['mep_reg_status'][0] == 'on') {
				$reg_checked = 'checked';
			}
		} else {
			    $reg_checked = 'checked';
		}
// echo $reg_checked;
        ?>
<div class='mep-event-shortcode-info'>
    <p><?php _e('<b>Add To Cart Form Shortcode</b>','mage-eventpress'); ?></p>
    <code> [event-add-cart-section event="<?php echo $post_id; ?>"]</code>
    <p><?php _e('If you want to display the ticket type list with an add-to-cart button on any post or page of your website, simply copy the shortcode and paste it where desired.','mage-eventpress'); ?></p>
    <ul>
        <li><span><?php esc_html_e('Registration Off/On:', 'mage-eventpress'); ?></span></li>
        <li>
            <label class='mp_event_ticket_type_des_switch'>
                <input class="mp_opacity_zero" type="checkbox" name="mep_reg_status" <?php echo esc_attr($reg_checked); ?> /><span class="mep_slider round"></span>
            </label>
            </li>
    </ul>

</div>

<script type="text/javascript">
jQuery(document).ready(function() {
    <?php if($reg_checked != 'checked'){ ?>
        jQuery(".mep_ticket_type_setting_sec").hide(200);
    <?php } ?>
});
</script>

<?php
    }
}



add_action('mep_event_tab_after_ticket_pricing','mep_event_pro_purchase_notice');
function mep_event_pro_purchase_notice(){
    
    
    if(!mep_check_plugin_installed('woocommerce-event-manager-addon-form-builder/addon-builder.php') ) { ?>
       
       <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Get Individual Attendee  Information, PDF Ticketing and Email Function with <a href='https://mage-people.com/product/mage-woo-event-booking-manager-pro/' target='_blank'>Event Manager Pro</a>", 'mage-eventpress'); ?></p>

    <?php } if(!mep_check_plugin_installed('woocommerce-event-manager-addon-global-quantity/global-quantity.php')){ ?>
   
        <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Setup Event Common QTY of All Ticket Type get <a href='https://mage-people.com/product/global-common-qty-addon-for-event-manager' target='_blank'>Global QTY Addon</a>", 'mage-eventpress'); ?></p>

    <?php } if(!mep_check_plugin_installed('woocommerce-event-manager-addon-membership-price/membership-price.php')){ ?>


    <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Special Price Option for each user type or membership get <a href='https://mage-people.com/product/membership-pricing-for-event-manager-plugin' target='_blank'>Membership Pricing Addon</a>", 'mage-eventpress'); ?></p>

    <?php } if(!mep_check_plugin_installed('woocommerce-event-manager-min-max-quantity-addon/mep_min_max_qty.php')){ ?>

    <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Set maximum/minimum qty buying option with <a href='https://mage-people.com/product/event-max-min-quantity-limiting-addon-for-woocommerce-event-manager' target='_blank'>Max/Min Qty Addon</a>", 'mage-eventpress'); ?></p>

    <?php
    }
}


function mep_get_datetime_format($event_id=0,$type='date'){

    $custom_format = get_post_meta($event_id,'mep_enable_custom_dt_format',true) ? get_post_meta($event_id,'mep_enable_custom_dt_format',true) : 'off';

    $date_format = get_option('date_format');
    $time_format = get_option('time_format');

    $current_date_format                = mep_get_option('mep_global_date_format','datetime_setting_sec',$date_format);
    $current_time_format                = mep_get_option('mep_global_time_format','datetime_setting_sec',$time_format);
    $current_global_custom_date_format  = mep_get_option('mep_global_custom_date_format','datetime_setting_sec',$date_format);
    $current_global_custom_time_format  = mep_get_option('mep_global_custom_time_format','datetime_setting_sec',$time_format);
   
    $current_global_timezone_display    = mep_get_option('mep_global_timezone_display','datetime_setting_sec','no');

    $saved_date_format                  = $custom_format == 'on' && get_post_meta($event_id,'mep_event_date_format',true) ? get_post_meta($event_id,'mep_event_date_format',true) : $current_date_format;


    $saved_custom_date_format           = $custom_format == 'on' &&  get_post_meta($event_id,'mep_event_custom_date_format',true) ? get_post_meta($event_id,'mep_event_custom_date_format',true) : $current_global_custom_date_format;
    $saved_time_format                  = $custom_format == 'on' &&  get_post_meta($event_id,'mep_event_time_format',true) ? get_post_meta($event_id,'mep_event_time_format',true) : $current_time_format;
    $saved_custom_time_format           = $custom_format == 'on' &&  get_post_meta($event_id,'mep_custom_event_time_format',true) ? get_post_meta($event_id,'mep_custom_event_time_format',true) : $current_global_custom_time_format;
   
    $saved_time_zone_display            = $custom_format == 'on' && get_post_meta($event_id,'mep_time_zone_display',true) ? get_post_meta($event_id,'mep_time_zone_display',true) : $current_global_timezone_display;


 

    $date_format            = $saved_date_format == 'custom' ? $saved_custom_date_format : $saved_date_format;
    $time_format            = $saved_time_format == 'custom' ? $saved_custom_time_format : $saved_time_format;
    $timezone               = $saved_time_zone_display == 'yes' ? ' T' : '';



    if($type == 'date'){
        return $date_format;
    }elseif($type == 'date_timezone'){
        return $date_format.$timezone;
    }elseif($type == 'time'){
        return $time_format;
    }elseif($type == 'time_timezone'){
        return $time_format.$timezone;
    }
    else{
        return $date_format;
    }

}






add_action('mp_event_recurring_every_day_setting','mep_event_recurring_purchase_notice',90);
function mep_event_recurring_purchase_notice(){
    $event_id = get_the_id();
    $event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Event');


    $date_format = get_option('date_format');
    $time_format = get_option('time_format');


    $date_format_arr = mep_date_format_list();
    $time_format_arr = mep_time_format_list();

    $current_date_format = mep_get_option('mep_global_date_format','datetime_setting_sec',$date_format);
    $current_time_format = mep_get_option('mep_global_time_format','datetime_setting_sec',$time_format);

    $current_global_custom_date_format = mep_get_option('mep_global_custom_date_format','datetime_setting_sec',$date_format);
    $current_global_custom_time_format = mep_get_option('mep_global_custom_time_format','datetime_setting_sec',$time_format);

    $current_global_timezone_display = mep_get_option('mep_global_timezone_display','datetime_setting_sec','no');

    $saved_date_format = get_post_meta($event_id,'mep_event_date_format',true) ? get_post_meta($event_id,'mep_event_date_format',true) : $current_date_format;

    $saved_custom_date_format = get_post_meta($event_id,'mep_event_custom_date_format',true) ? get_post_meta($event_id,'mep_event_custom_date_format',true) : $current_global_custom_date_format;

    $saved_time_format = get_post_meta($event_id,'mep_event_time_format',true) ? get_post_meta($event_id,'mep_event_time_format',true) : $current_time_format;

    $saved_custom_time_format = get_post_meta($event_id,'mep_custom_event_time_format',true) ? get_post_meta($event_id,'mep_custom_event_time_format',true) : $current_global_custom_time_format;

    $saved_time_zone_display = get_post_meta($event_id,'mep_time_zone_display',true) ? get_post_meta($event_id,'mep_time_zone_display',true) : $current_global_timezone_display;


	$values     = get_post_custom($event_id);
		
		$mep_enable_custom_dt_format = '';
		if (array_key_exists('mep_enable_custom_dt_format', $values)) {
			if ($values['mep_enable_custom_dt_format'][0] == 'on') {
				$mep_enable_custom_dt_format = 'checked';
			}
		} else {
			    $mep_enable_custom_dt_format = '';
		}



?>


<ul>
        <li><h3><?php esc_html_e('You can change the date and time format by going to the settings '.$event_label.' (Off/On):', 'mage-eventpress'); ?></h3><hr /></li>
        <li>
            <label class='mep_enable_custom_dt_format'>
                <input class="mp_opacity_zero " type="checkbox" name="mep_enable_custom_dt_format" <?php echo esc_attr($mep_enable_custom_dt_format); ?> /><span class="mep_slider round"></span>
            </label>
            </li>
    </ul>

<script type="text/javascript">
jQuery(document).ready(function() {
    <?php if($mep_enable_custom_dt_format != 'checked'){ ?>
        jQuery(".mep_custom_timezone_setting").hide(200);
    <?php } ?>
});
</script>



<div class='mep_custom_timezone_setting'>
<!-- <h3><?php esc_html_e(' Date & TIme Format For this '.$event_label.': ', 'mage-eventpress'); ?></h3> -->
<table class="form-table">
		<tbody>




			<tr class='mep_global_date_format'>
				<th scope="row"><?php _e('Date Format','mage-eventpress'); ?></th>
				<td>
                    <select class="regular mep_global_date_format" name="mep_event_date_format" id="datetime_setting_sec[mep_global_date_format]">
                        <?php                                                 
                        foreach($date_format_arr as $key => $date){ ?>                                            
                                <option value='<?php echo $key;?>' <?php if($saved_date_format == $key){ echo 'Selected'; } ?>><?php echo $date;?></option>
                        <?php } ?>
                    </select>                    
                    <p class='event_meta_help_txt'>
                        <?php _e('Please select your preferred date format from the options below. If you wish to use a custom date format, select the Custom option and enter your desired date format. Please note that this date format will only apply to events.','mage-eventpress'); ?>
                    </p>
                </td>
            </tr>	
            
            <tr class="mep_global_custom_date_format" style="">
                <th scope="row"><label for="datetime_setting_sec[mep_global_custom_date_format]"><?php _e('Custom Date Format','mage-eventpress'); ?></label></th>
                <td>
                    <input type="text" class="regular-text" id="datetime_setting_sec[mep_global_custom_date_format]" name="mep_event_custom_date_format" value="<?php echo $saved_custom_date_format; ?>">
                    <p class="event_meta_help_txt">
                        <a href="https://wordpress.org/support/article/formatting-date-and-time/">Documentation on date and time formatting</a>
                    </p>
                </td>
            </tr>           

            <tr class="mep_global_time_format">
                <th scope="row"><label for="datetime_setting_sec[mep_global_time_format]"><?php _e('Time Format','mage-eventpress'); ?></label></th>
                <td>
                    <select class="regular mep_global_time_format" name="mep_event_time_format" id="datetime_setting_sec[mep_global_time_format]">
                    <?php                                                 
                        foreach($time_format_arr as $key => $date){ ?>                                            
                                <option value='<?php echo $key;?>' <?php if($saved_time_format == $key){ echo 'Selected'; } ?>><?php echo $date;?></option>
                        <?php } ?>                  
                    </select>
                    <p class="event_meta_help_txt">
                        <?php _e('Please select the time format from the list. If you want to use a custom time format, select Custom and write your desired time format. This time format will only apply to events.','mage-eventpress'); ?>
                    </p>
                </td>
            </tr>

            <tr class="mep_global_custom_time_format">
                <th scope="row"><label for="datetime_setting_sec[mep_global_custom_time_format]"><?php _e('Custom Time Format','mage-eventpress'); ?></label></th>
                <td>
                    <input type="text" class="regular-text" id="datetime_setting_sec[mep_global_custom_time_format]" name="mep_custom_event_time_format" value="<?php echo $saved_custom_time_format; ?>">
                    <p class="event_meta_help_txt">
                        <a href="https://wordpress.org/support/article/formatting-date-and-time/">Documentation on date and time formatting</a>
                    </p>
                </td>
            </tr>


            <tr class="mep_global_timezone_display">
                <th scope="row"><label for="datetime_setting_sec[mep_global_timezone_display]"><?php _e('Show Timezone','mage-eventpress'); ?></label></th>
                <td>
                    <select class="regular mep_global_timezone_display" name="mep_time_zone_display" id="datetime_setting_sec[mep_global_timezone_display]">
                        <option value="yes" <?php if($saved_time_zone_display == 'yes'){ echo 'Selected'; } ?>><?php _e('Yes','mage-eventpress'); ?></option>
                        <option value="no" <?php if($saved_time_zone_display == 'no'){ echo 'Selected'; } ?>><?php _e('No','mage-eventpress'); ?></option>
                    </select>
                    <p class="event_meta_help_txt">
                        <?php _e('If you want to show the date and time in your local timezone, please select Yes.','mage-eventpress'); ?>
                    </p>
                </td>
            </tr>
         </tbody>
</table>
                        </div>
<?php




    if(!mep_check_plugin_installed('woocommerce-event-manager-addon-recurring-event/recurring_events.php') ) {
    ?>
    <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("If you're looking for a recurring events function where customers can choose date and time, check out our website. We have a wide selection of options to choose from to make sure your event is perfect. <a href='https://mage-people.com/product/recurring-events-addon-for-event-manager/' target='_blank'>Recurring Addon</a>", 'mage-eventpress'); ?></p>   
    <?php
    }
}

function mep_date_format_list(){
    $format = [
        'F j, Y'    => date('F j, Y'),
        'j F, Y'    => date('j F, Y'),
        'D, F j, Y' => date('D, F j, Y'),
        'l, F j, Y' => date('l, F j, Y'),
        'Y-m-d'     => date('Y-m-d'),
        'm/d/Y'     => date('m/d/Y'),
        'd/m/Y'     => date('d/m/Y'),
        'custom'     => __('Custom Date Format','mage-eventpress'),
    ];
    return $format;
}

function mep_time_format_list(){
    $format = [
        'g:i a'             => date('g:i a'),
        'g:i A'             => date('g:i A'),
        'H:i'               => date('H:i'),       
        'H\H i\m\i\n'       => date('H\H i\m\i\n'),     
        'custom'     => __('Custom Time Format','mage-eventpress'),  
    ];
    return $format;
}

add_action('admin_footer','mep_admin_footer_js');
function mep_admin_footer_js(){
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    $current_date_format = mep_get_option('mep_global_date_format','datetime_setting_sec',$date_format);
    $current_time_format = mep_get_option('mep_global_time_format','datetime_setting_sec',$time_format);
?>
<script>

<?php 
if($current_date_format == 'custom'){
    ?>
        jQuery(".mep_global_custom_date_format").slideDown(200);
    <?php
}else{
    ?>
        jQuery(".mep_global_custom_date_format").slideUp(200);
    <?php
}
?>

<?php 
if($current_time_format == 'custom'){
    ?>
        jQuery(".mep_global_custom_time_format").slideDown(200);
    <?php
}else{
    ?>
        jQuery(".mep_global_custom_time_format").slideUp(200);
    <?php
}
?>

jQuery(document).on('change', '.mep_global_date_format', function () {
        if (jQuery(this).val() != '' && jQuery(this).val() == 'custom') {        
            jQuery(".mep_global_custom_date_format").slideDown(200);
        } else {          
            jQuery(".mep_global_custom_date_format").slideUp(200);
        }
    return false;
});


jQuery(document).on('change', '.mep_global_time_format', function () {
        if (jQuery(this).val() != '' && jQuery(this).val() == 'custom') {        
            jQuery(".mep_global_custom_time_format").slideDown(200);
        } else {          
            jQuery(".mep_global_custom_time_format").slideUp(200);
        }
    return false;
});

</script>
<?php
}


add_filter('mep_event_loop_list_available_seat', 'mep_speed_up_list_page',5,2);
if (!function_exists('mep_speed_up_list_page')) {
    function mep_speed_up_list_page($available,$event_id) {
        $availabele_check = mep_get_option('mep_speed_up_list_page', 'general_setting_sec', 'no');
        $available = $availabele_check == 'yes' ? 1 : $available;
        return 1;
    }
}



add_action('mep_before_add_cart_button','mep_add_cart_btn_icon');
function mep_add_cart_btn_icon($event_id){

$button = apply_filters('mep_cart_icon',"<i class='fa fa-shopping-cart'></i>",$event_id);

echo '<span class="mep-cart-btn-icon">'.$button.'</span>';

}