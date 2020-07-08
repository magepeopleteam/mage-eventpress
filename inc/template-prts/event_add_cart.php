<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_add_to_cart', 'mep_get_event_reg_btn');
if (!function_exists('mep_get_event_reg_btn')) {
    // Get Event Registration Button
    function mep_get_event_reg_btn($event_id = '')
    {
        global $post, $event_meta;
        $total_book                 = 0;
        $post_id                    = $event_id ? $event_id : get_the_id();
        $event_meta                 = get_post_custom($post_id);
        $event_expire_on_old         = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
        $event_expire_on            = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
        $event_expire_date          = $event_meta[$event_expire_on][0];
       // $event_sqi                  = array_key_exists('mep_sqi',$event_meta) ? $event_meta['mep_sqi'][0] : '';
        $mep_full_name              = strip_tags($event_meta['mep_full_name'][0]);
        $mep_reg_email              = strip_tags($event_meta['mep_reg_email'][0]);
        $mep_reg_phone              = strip_tags($event_meta['mep_reg_phone'][0]);
        $mep_reg_address            = strip_tags($event_meta['mep_reg_address'][0]);
        $mep_reg_designation        = strip_tags($event_meta['mep_reg_designation'][0]);
        $mep_reg_website            = strip_tags($event_meta['mep_reg_website'][0]);
        $mep_reg_veg                = strip_tags($event_meta['mep_reg_veg'][0]);
        $mep_reg_company            = strip_tags($event_meta['mep_reg_company'][0]);
        $mep_reg_gender             = strip_tags($event_meta['mep_reg_gender'][0]);
        $mep_reg_tshirtsize         = strip_tags($event_meta['mep_reg_tshirtsize'][0]);
        $time                       = strtotime($event_expire_date);
        $newformat                  = date('Y-m-d H:i:s', $time);
        $datetime1                  = new DateTime();
        $datetime2                  = new DateTime($newformat);
        $interval                   = $datetime1->diff($datetime2);
        $mep_event_ticket_type      = get_post_meta($post_id, 'mep_event_ticket_type', true) ? get_post_meta($post_id, 'mep_event_ticket_type', true) : array();
        $total_seat                 = apply_filters('mep_event_total_seat_counts', mep_event_total_seat($post_id, 'total'), $post_id);
        $total_resv                 = apply_filters('mep_event_total_resv_seat_count', mep_event_total_seat($post_id, 'resv'), $post_id);
        $total_sold                 = mep_ticket_sold($post_id);
        $total_left                 = $total_seat - ($total_sold + $total_resv);
        $reg_status                 = array_key_exists('mep_reg_status', $event_meta) ? $event_meta['mep_reg_status'][0] : '';
        $seat_left                  = apply_filters('mep_event_total_seat_count', $total_left, $post_id);
        $current                    = current_time('Y-m-d H:i:s');
        $time                       = strtotime($event_expire_date);
        $newformat                  = date('Y-m-d H:i:s', $time);
        $recurring                  = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';

        if ($recurring == 'yes') {
            $event_more_dates         = get_post_meta($post_id, 'mep_event_more_date', true) ? get_post_meta($post_id, 'mep_event_more_date', true) : array();
            $md                       = end($event_more_dates);
            $more_date                = $md['event_more_start_date'] . ' ' . $md['event_more_start_time'];
            $newformat                = empty($event_more_dates) ?  $newformat : date('Y-m-d H:i:s', strtotime($more_date));
        }

        // $default_timezone_val       = get_option('timezone_string') ? get_option('timezone_string') : 'UTC';
        // date_default_timezone_set($default_timezone_val);

        $datetime1                  = new DateTime($newformat);
        $datetime2                  = new DateTime($current);
        $interval                   = date_diff($datetime2, $datetime1);
        $mep_available_seat         = array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';

        $leftt                      = apply_filters('mep_event_total_seat_count', $total_left, $post_id);
        $days                       = $interval->d;
        $hours                      = $interval->h;
        $minutes                    = $interval->i;
        $dd                         = $days > 0 ? $days . " days " : '';
        $hh                         = $hours > 0 ? $hours . " hours " : '';
        $mm                         = $minutes > 0 ? $minutes . " minutes " : '';
       // $qty_typec                  = array_key_exists('qty_box_type',$event_meta) ? $event_meta['qty_box_type'][0] : '';
        $cart_product_id            = get_post_meta($post_id, 'link_wc_product', true) ? esc_attr(get_post_meta($post_id, 'link_wc_product', true)) : esc_attr($post_id);

        

        /**
         * First Checking If the registration status enable or disable 
         */
        if ($reg_status != 'off') {
            /**
             * Then Checking If the event date already gone or not 
             */
            if (strtotime(current_time('Y-m-d H:i:s')) > strtotime(apply_filters('mep_event_expire_datetime_val',$newformat,$post_id))) {
                /**
                 * If The event expired then it fire below Hook, The event expire texts arein the inc/template-parts/event_labels.php file
                 */
                do_action('mep_event_expire_text');

                /**
                 * If the event is not expired then Its checking the available seat status
                 */
            } elseif ($seat_left <= 0) {
                /**
                 * If All the seats are booked then it fire the below hooks, The event no seat texts are in the inc/template-parts/event_labels.php file
                 */
                do_action('mep_event_no_seat_text');
                do_action('mep_after_no_seat_notice');
            } else {
                /**
                 * If everything is fine then its go on ....
                 */
?>
                <!-- Register Now Title -->
                <h4 class="mep-cart-table-title">
                    <?php echo mep_get_option('mep_register_now_text', 'label_setting_sec') ? mep_get_option('mep_register_now_text', 'label_setting_sec') : _e('Register Now:', 'mage-eventpress');  ?>
                </h4>
                <!--The event add to cart main form start here-->
                <form action="" method='post' id="mage_event_submit">
                    <?php
                    /**
                     * Here is a magic hook which fire just before of the Add to Cart Button, And the Ticket type & Extra service list are hooked up into this, You can find them into inc/template-parts/event_ticket_type_extra_service.php
                     */
                    do_action('mep_event_ticket_type_extra_service', $post_id);
                    ?>
                    <input type='hidden' id='rowtotal' value="<?php echo get_post_meta($post_id, "_price", true); ?>" />

                    <!--The Add to cart button table start Here-->
                    <table class='table table-bordered mep_event_add_cart_table'>
                        <tr>
                            <td align="left" class='total-col'><?php echo mep_get_option('mep_quantity_text', 'label_setting_sec') ? mep_get_option('mep_quantity_text', 'label_setting_sec') : _e('Quantity:', 'mage-eventpress');
                                                                if ($mep_event_ticket_type) { ?>
                                    <input id="quantity_5a7abbd1bff73" class="input-text qty text extra-qty-box" step="1" min="1" max="<?php echo $leftt; ?>" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" type="hidden">
                                    <span id="ttyttl"></span>
                                <?php } ?>
                                <span class='the-total'> <?php echo mep_get_option('mep_total_text', 'label_setting_sec') ? mep_get_option('mep_total_text', 'label_setting_sec') : _e('Total', 'mage-eventpress');  ?>
                                    <span id="usertotal"></span>
                                </span>
                            </td>
                            <td align="right">
                                <input type="hidden" name="mep_event_location_cart" value="<?php trim(mep_ev_location_ticket($post_id, $event_meta)); ?>">
                                <input type="hidden" name="mep_event_date_cart" value="<?php do_action('mep_event_date'); ?>">
                                <button type="submit" name="add-to-cart" value="<?php echo $cart_product_id; ?>" class="single_add_to_cart_button button alt btn-mep-event-cart"><?php _e(mep_get_label($post_id, 'mep_cart_btn_text', 'Register This Event'), 'mage-eventpress'); ?> </button>
                            </td>
                        </tr>
                    </table>
                    <!--The Add to cart button table start Here-->
                </form>
                <!--The event add to cart main form end here-->
            <?php
            }
        } // End Of checking Registration status  
    }
}




add_action('mep_add_to_cart_list', 'mep_get_event_reg_btn_list');

if (!function_exists('mep_get_event_reg_btn_list')) {
    // Get Event Registration Button
    function mep_get_event_reg_btn_list()
    {
        global $post, $event_meta;
        $total_book = 0;
        $post_id                    = $post->ID;
        $event_meta                 = get_post_custom($post_id);
        $event_expire_on_old         = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
        $event_expire_on            = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
        $event_expire_date          = $event_meta[$event_expire_on][0];
       // $event_sqi                  = $event_meta['mep_sqi'][0];
        $mep_full_name              = strip_tags($event_meta['mep_full_name'][0]);
        $mep_reg_email              = strip_tags($event_meta['mep_reg_email'][0]);
        $mep_reg_phone              = strip_tags($event_meta['mep_reg_phone'][0]);
        $mep_reg_address            = strip_tags($event_meta['mep_reg_address'][0]);
        $mep_reg_designation        = strip_tags($event_meta['mep_reg_designation'][0]);
        $mep_reg_website            = strip_tags($event_meta['mep_reg_website'][0]);
        $mep_reg_veg                = strip_tags($event_meta['mep_reg_veg'][0]);
        $mep_reg_company            = strip_tags($event_meta['mep_reg_company'][0]);
        $mep_reg_gender             = strip_tags($event_meta['mep_reg_gender'][0]);
        $mep_reg_tshirtsize         = strip_tags($event_meta['mep_reg_tshirtsize'][0]);
        // $simple_rsv                 = array_key_exists('mep_rsv_seat', $event_meta) ? $event_meta['mep_rsv_seat'][0] : 0;
        // $total_book                 = ($total_book + $simple_rsv);
        // $seat_left                  = ((int)$event_meta['mep_total_seat'][0]- (int)$total_book);
        $time                       = strtotime($event_expire_date);
        $newformat                  = date('Y-m-d H:i:s', $time);
        $datetime1                  = new DateTime();
        $datetime2                  = new DateTime($newformat);
        $interval                   = $datetime1->diff($datetime2);
        $mep_event_ticket_type      = get_post_meta($post_id, 'mep_event_ticket_type', true) ? get_post_meta($post_id, 'mep_event_ticket_type', true) : array();
        $total_seat                 = mep_event_total_seat(get_the_id(), 'total');
        $total_resv                 = mep_event_total_seat(get_the_id(), 'resv');
        $total_sold                 = mep_ticket_sold(get_the_id());
        $total_left                 = $total_seat - ($total_sold + $total_resv);
        $reg_status                 = array_key_exists('mep_reg_status', $event_meta) ? $event_meta['mep_reg_status'][0] : '';
        $seat_left                  = apply_filters('mep_event_total_seat_count', $total_left, get_the_id());
        $current                    = current_time('Y-m-d H:i:s');
        $time                       = strtotime($event_expire_date);
        $newformat                  = date('Y-m-d H:i:s', $time);
        $recurring                  = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';

        if ($recurring == 'yes') {
            $event_more_dates         = get_post_meta($post_id, 'mep_event_more_date', true) ? get_post_meta($post_id, 'mep_event_more_date', true) : array();
            $md                       = end($event_more_dates);
            $more_date                = $md['event_more_start_date'] . ' ' . $md['event_more_start_time'];
            $newformat                = empty($event_more_dates) ?  $newformat : date('Y-m-d H:i:s', strtotime($more_date));
        }

        // $default_timezone_val       = get_option('timezone_string') ? get_option('timezone_string') : 'UTC';
        // date_default_timezone_set($default_timezone_val);

        $datetime1                  = new DateTime($newformat);
        $datetime2                  = new DateTime($current);
        $interval                   = date_diff($datetime2, $datetime1);
        $mep_available_seat         = array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';

        $leftt                      = apply_filters('mep_event_total_seat_count', $total_left, get_the_id());
        $days                       = $interval->d;
        $hours                      = $interval->h;
        $minutes                    = $interval->i;
        $dd                         = $days > 0 ? $days . " days " : '';
        $hh                         = $hours > 0 ? $hours . " hours " : '';
        $mm                         = $minutes > 0 ? $minutes . " minutes " : '';
       // $qty_typec                  = $event_meta['qty_box_type'][0];
        $cart_product_id            = get_post_meta($post_id, 'link_wc_product', true) ? esc_attr(get_post_meta($post_id, 'link_wc_product', true)) : esc_attr($post_id);


        /**
         * First Checking If the registration status enable or disable 
         */
        if ($reg_status != 'off') {
            /**
             * Then Checking If the event date already gone or not 
             */
            if (strtotime(current_time('Y-m-d H:i:s')) > strtotime($newformat)) {
                /**
                 * If The event expired then it fire below Hook, The event expire texts arein the inc/template-parts/event_labels.php file
                 */
                do_action('mep_event_expire_text');

                /**
                 * If the event is not expired then Its checking the available seat status
                 */
            } elseif ($seat_left <= 0) {
                /**
                 * If All the seats are booked then it fire the below hooks, The event no seat texts are in the inc/template-parts/event_labels.php file
                 */
                do_action('mep_event_no_seat_text');
                do_action('mep_after_no_seat_notice');
            } else {
                /**
                 * If everything is fine then its go on ....
                 */
            ?>
                <!-- Register Now Title -->
                <h4 class="mep-cart-table-title">
                    <?php echo mep_get_option('mep_register_now_text', 'label_setting_sec') ? mep_get_option('mep_register_now_text', 'label_setting_sec') : _e('Register Now:', 'mage-eventpress');  ?>
                </h4>
                <!--The event add to cart main form start here-->
                <form action="" method='post' id="mage_event_submit">
                    <?php
                    /**
                     * Here is a magic hook which fire just before of the Add to Cart Button, And the Ticket type & Extra service list are hooked up into this, You can find them into inc/template-parts/event_ticket_type_extra_service.php
                     */
                    do_action('mep_event_ticket_types_list');
                    do_action('mep_event_extra_service_list');
                    ?>
                    <input type='hidden' id='rowtotal' value="<?php echo get_post_meta($post_id, "_price", true); ?>" />

                    <!--The Add to cart button table start Here-->
                    <table class='table table-bordered mep_event_add_cart_table'>
                        <tr>
                            <td align="left" class='total-col'><?php echo mep_get_option('mep_quantity_text', 'label_setting_sec') ? mep_get_option('mep_quantity_text', 'label_setting_sec') : _e('Quantity:', 'mage-eventpress');
                                                                if ($mep_event_ticket_type) { ?>
                                    <input id="quantity_5a7abbd1bff73" class="input-text qty text extra-qty-box" step="1" min="1" max="<?php echo $leftt; ?>" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" type="hidden">
                                    <span id="ttyttl"></span>
                                <?php } ?>
                                <span class='the-total'> <?php echo mep_get_option('mep_total_text', 'label_setting_sec') ? mep_get_option('mep_total_text', 'label_setting_sec') : _e('Total', 'mage-eventpress');  ?>
                                    <span id="usertotal"></span>
                                </span>
                            </td>
                            <td align="right">
                                <input type="hidden" name="mep_event_location_cart" value="<?php trim(mep_ev_location_ticket($post_id, $event_meta)); ?>">
                                <input type="hidden" name="mep_event_date_cart" value="<?php do_action('mep_event_date'); ?>">
                                <button type="submit" name="add-to-cart" value="<?php echo $cart_product_id; ?>" class="single_add_to_cart_button button alt btn-mep-event-cart"><?php _e(mep_get_label($post_id, 'mep_cart_btn_text', 'Register This Event'), 'mage-eventpress'); ?> </button>
                            </td>
                        </tr>
                    </table>
                    <!--The Add to cart button table start Here-->
                </form>
                <!--The event add to cart main form end here-->
<?php
            }
        } // End Of checking Registration status    
    }
}