<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

function mep_check_attendee_exists($event_id, $order_id, $name = null, $email = null, $phone = null, $address = null, $gender = null, $company = null, $desg = null, $website = null, $veg = null, $tshirt = null, $type)
{

    $args = array(
        'post_type'      => 'mep_events_attendees',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key'       => 'ea_event_id',
                'value'     => $event_id,
                'compare'   => '='
            ),
            array(
                'key'       => 'ea_order_id',
                'value'     => $order_id,
                'compare'   => '='
            ),
            array(
                'key'       => 'ea_ticket_type',
                'value'     => $type,
                'compare'   => '='
            ),
            array(
                'key'       => 'ea_name',
                'value'     => $name,
                'compare'   => '='
            ),
            array(
                'key'       => 'ea_email',
                'value'     => $email,
                'compare'   => '='
            ),
            array(
                'key'       => 'ea_phone',
                'value'     => $phone,
                'compare'   => '='
            )
        )
    );
    $loop = new WP_Query($args);
    return $loop->post_count;
}

// Flash Permalink only Once 
function mep_flash_permalink_once()
{
    if (get_option('mep_flash_event_permalink') != 'completed') {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
        update_option('mep_flash_event_permalink', 'completed');
    }
}
add_action('admin_init', 'mep_flash_permalink_once');


add_action('admin_init', 'mep_get_all_order_data_and_create_attendee');
function mep_get_all_order_data_and_create_attendee()
{


    if (get_option('mep_hidden_product_thumbnail_update_02') != 'completed') {

        $args = array(
            'post_type' => 'mep_events',
            'posts_per_page' => -1
        );

        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            $product_id = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : $post_id;
            set_post_thumbnail($product_id, get_post_thumbnail_id($post_id));
        }
        update_option('mep_hidden_product_thumbnail_update_02', 'completed');
    }


    if (get_option('mep_event_default_date_update_2020') != 'completed') {

        $args = array(
            'post_type' => 'mep_events',
            'posts_per_page' => -1
        );

        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            $mep_start_date        = get_post_meta($post_id, 'event_start_date', true);
            $mep_start_time        = get_post_meta($post_id, 'event_start_time', true);
            $mep_end_date          = get_post_meta($post_id, 'event_end_date', true);
            $mep_end_time          = get_post_meta($post_id, 'event_end_time', true);

            $event_start_datetime   = date('Y-m-d H:i:s', strtotime($mep_start_date . ' ' . $mep_start_time));
            $event_end_datetime     = date('Y-m-d H:i:s', strtotime($mep_end_date . ' ' . $mep_end_time));

            update_post_meta($post_id, 'event_start_datetime', $event_start_datetime);
            update_post_meta($post_id, 'event_end_datetime', $event_end_datetime);
        }
        update_option('mep_event_default_date_update_2020', 'completed');
        //die();
    }

    /**
     * Event Expire Date Upgrade
     */
    if (get_option('mep_event_expire_date_upgration') != 'completed') {

        $args = array(
            'post_type' => 'mep_events',
            'posts_per_page' => -1
        );

        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            $event_more_dates    = get_post_meta($post_id, 'mep_event_more_date', true) ? get_post_meta($post_id, 'mep_event_more_date', true) : array();
            $md                     = sizeof($event_more_dates) > 0 ? end($event_more_dates) : array();
            $event_expire_datetime  = sizeof($md) > 0 ? date('Y-m-d H:i:s', strtotime($md['event_more_end_date'] . ' ' . $md['event_more_end_time'])) : date('Y-m-d H:i:s', strtotime(get_post_meta($post_id, 'event_end_datetime', true)));
            update_post_meta($post_id, 'event_expire_datetime', $event_expire_datetime);
        }
        update_option('mep_event_expire_date_upgration', 'completed');
        // die();
    }




    if (get_option('mep_event_default_date_update_20') != 'completed') {

        $args = array(
            'post_type' => 'mep_events',
            'posts_per_page' => -1
        );

        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            $mep_start_date        = get_post_meta($post_id, 'mep_event_start_date', true);
            $mep_end_date          = get_post_meta($post_id, 'mep_event_end_date', true);

            $event_start_date       = date('Y-m-d', strtotime($mep_start_date));
            $event_start_time       = date('H:i', strtotime($mep_start_date));
            $event_end_date         = date('Y-m-d', strtotime($mep_end_date));
            $event_end_time         = date('H:i', strtotime($mep_end_date));

            update_post_meta($post_id, 'event_start_date', $event_start_date);
            update_post_meta($post_id, 'event_start_time', $event_start_time);
            update_post_meta($post_id, 'event_end_date', $event_end_date);
            update_post_meta($post_id, 'event_end_time', $event_end_time);
        }
        update_option('mep_event_default_date_update_20', 'completed');
    }





    if (get_option('mep_attendee_event_date_update_20') != 'completed') {

        $args = array(
            'post_type' => 'mep_events_attendees',
            'posts_per_page' => -1
        );

        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            $ea_event_date        = get_post_meta($post_id, 'ea_event_date', true);
            if (empty($ea_event_date)) {
                $event_id    = get_post_meta($post_id, 'ea_event_id', true);
                $event_old_date  = get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
                update_post_meta($post_id, 'ea_event_date', $event_old_date);
            }
        }
        update_option('mep_attendee_event_date_update_20', 'completed');
    }




    if (get_option('mep_event_multidate_update_2') != 'completed') {

        $args = array(
            'post_type' => 'mep_events',
            'posts_per_page' => -1
        );

        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            $more_date   = get_post_meta($post_id, 'mep_event_more_date', true);
            if (is_array($more_date) && sizeof($more_date) > 0) {
                $count = 0;
                foreach ($more_date as $_multi_date) {
                    $start_date = date('Y-m-d', strtotime($_multi_date['event_more_date']));
                    $start_time = date('H:i A', strtotime($_multi_date['event_more_date']));
                    $multi_dates[$count]['event_more_start_date'] = stripslashes(strip_tags($start_date));
                    $multi_dates[$count]['event_more_start_time'] = stripslashes(strip_tags($start_time));
                    $multi_dates[$count]['event_more_end_date'] = stripslashes(strip_tags(''));
                    $multi_dates[$count]['event_more_end_time'] = stripslashes(strip_tags(''));
                    $count++;
                }
                update_post_meta($post_id, 'mep_event_more_date', $multi_dates);
            }
        }

        update_option('mep_event_multidate_update_2', 'completed');
    }


    if (get_option('mep_event_magor_update_3') != 'completed') {

        global $wpdb;
        $args = array(
            'limit' => -1,
            'return' => 'ids',
        );
        $query = new WC_Order_Query($args);
        $orders = $query->get_orders();
        $c = 1;

        foreach ($orders as $order_id) {
            $order      = wc_get_order($order_id);
            $order_meta = get_post_meta($order_id);

            foreach ($order->get_items() as $item_id => $item_values) {
                $item_id        = $item_id;
            }

            $event_info = maybe_unserialize(mep_event_get_order_meta($item_id, '_event_user_info'));

            $event_id = mep_event_get_order_meta($item_id, 'event_id') ? mep_event_get_order_meta($item_id, 'event_id') : 0;

            if (is_array($event_info) && sizeof($event_info) > 0 && $event_id > 0) {


                foreach ($event_info as $_event_info) {

                    $user_name        = isset($_event_info['user_name']) ? $_event_info['user_name'] : '';
                    $user_email       = isset($_event_info['user_email']) ? $_event_info['user_email'] : '';
                    $user_phone       = isset($_event_info['user_phone']) ? $_event_info['user_phone'] : '';
                    $user_address     = isset($_event_info['user_address']) ? $_event_info['user_address'] : '';
                    $user_gender      = isset($_event_info['user_gender']) ? $_event_info['user_gender'] : '';
                    $user_tshirtsize  = isset($_event_info['user_tshirtsize']) ? $_event_info['user_tshirtsize'] : '';
                    $user_company     = isset($_event_info['user_company']) ? $_event_info['user_company'] : '';

                    $user_designation = isset($_event_info['user_designation']) ? $_event_info['user_designation'] : '';

                    $user_website     = isset($_event_info['user_website']) ? $_event_info['user_website'] : '';
                    $user_vegetarian  = isset($_event_info['user_vegetarian']) ? $_event_info['user_vegetarian'] : '';
                    $user_ticket_type = isset($_event_info['user_ticket_type']) ? $_event_info['user_ticket_type'] : '';


                    $check           = mep_check_attendee_exists($event_id, $order_id, $user_name, $user_email, $user_phone, $user_address, $user_gender, $user_company, $user_designation, $user_website, $user_vegetarian, $user_tshirtsize, $user_ticket_type);

                    if ($check == 0) {

                        $first_name       = isset($order_meta['_billing_first_name'][0]) ? $order_meta['_billing_first_name'][0] : '';
                        $last_name        = isset($order_meta['_billing_last_name'][0]) ? $order_meta['_billing_last_name'][0] : '';
                        $uname            = $first_name . ' ' . $last_name;
                        $payment_method   = isset($order_meta['_payment_method_title'][0]) ? $order_meta['_payment_method_title'][0] : array();
                        $user_id          = isset($order_meta['_customer_user'][0]) ? $order_meta['_customer_user'][0] : array();
                        $event_name       = get_the_title($event_id);
                        $order_status     = $order->get_status();

                        $new_post = array(
                            'post_title'    =>   $uname,
                            'post_content'  =>   '',
                            'post_category' =>   array(),  // Usable for custom taxonomies too
                            'tags_input'    =>   array(),
                            'post_status'   =>   'publish', // Choose: publish, preview, future, draft, etc.
                            'post_type'     =>   'mep_events_attendees'  //'post',page' or use a custom post type if you want to
                        );

                        //SAVE THE POST
                        $pid                = wp_insert_post($new_post);
                        $pin = $user_id . $order_id . $event_id . $pid;
                        update_post_meta($pid, 'ea_name', $user_name);
                        update_post_meta($pid, 'ea_address_1', $user_address);
                        update_post_meta($pid, 'ea_email', $user_email);
                        update_post_meta($pid, 'ea_phone', $user_phone);
                        update_post_meta($pid, 'ea_gender', $user_gender);
                        update_post_meta($pid, 'ea_company', $user_company);
                        update_post_meta($pid, 'ea_desg', $user_designation);
                        update_post_meta($pid, 'ea_website', $user_website);
                        update_post_meta($pid, 'ea_vegetarian', $user_vegetarian);
                        update_post_meta($pid, 'ea_tshirtsize', $user_tshirtsize);
                        update_post_meta($pid, 'ea_ticket_type', $user_ticket_type);
                        update_post_meta($pid, 'ea_payment_method', $payment_method);
                        update_post_meta($pid, 'ea_event_name', $event_name);
                        update_post_meta($pid, 'ea_event_id', $event_id);
                        update_post_meta($pid, 'ea_order_id', $order_id);
                        update_post_meta($pid, 'ea_user_id', $user_id);
                        update_post_meta($pid, 'ea_ticket_no', $pin);
                        update_post_meta($pid, 'ea_order_status', $order_status);
                    }
                }
            }
        }
        update_option('mep_event_magor_update_3', 'completed');
    }



/**
 * Update Ticket Price for all existing event attendee
 */

if (get_option('mep_attendee_price_update_2') != 'completed') {
    $args = array(
        'post_type' => 'mep_events_attendees',
        'posts_per_page' => -1
    );
    $qr = new WP_Query($args);
    foreach ($qr->posts as $result) {
        $post_id = $result->ID;
        $ea_ticket_price        = get_post_meta($post_id, 'ea_ticket_price', true);
        // if (empty($ea_ticket_price)) {
            $event_id               = get_post_meta($post_id, 'ea_event_id', true);
            $ea_ticket_type         = get_post_meta($post_id, 'ea_ticket_type', true);
            $ticket_total_price     = mep_get_event_ticket_price_by_name($event_id,$ea_ticket_type);
            update_post_meta($post_id, 'ea_ticket_price', $ticket_total_price);
        // }
    }
    update_option('mep_attendee_price_update_2', 'completed');
}










}




// Function for create hidden product for bus
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


    $pid                = wp_insert_post($new_post);

    update_post_meta($post_id, 'link_wc_product', $pid);
    update_post_meta($pid, 'link_mep_event', $post_id);
    update_post_meta($pid, '_price', 0.01);

    update_post_meta($pid, '_sold_individually', 'yes');
    update_post_meta($pid, '_virtual', 'yes');
    $terms = array('exclude-from-catalog', 'exclude-from-search');
    wp_set_object_terms($pid, $terms, 'product_visibility');
    update_post_meta($post_id, 'check_if_run_once', true);
}

add_action('admin_init', 'mep_create_old_event_product', 10);
function mep_create_old_event_product()
{

    if (get_option('wbtm_create_old_bus_products_101') != 'completed') {

        $args = array(
            'post_type' => 'mep_events',
            'posts_per_page' => -1
        );

        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            mep_create_hidden_event_product($post_id, get_the_title($post_id));
        }

        update_option('wbtm_create_old_bus_products_101', 'completed');
    }
}