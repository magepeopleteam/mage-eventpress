<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_add_to_cart', 'mep_get_event_reg_btn',10,2);
if (!function_exists('mep_get_event_reg_btn')) {
    // Get Event Registration Button
    function mep_get_event_reg_btn($event_id = '',$params=[])
    {        
        global $post, $event_meta;
        $event_id                     = mep_get_default_lang_event_id($event_id);
        $new_registration_system_sp	  = get_post_meta($event_id, 'mpwemasp_seat_plan_system', true) ? get_post_meta($event_id, 'mpwemasp_seat_plan_system', true) : 'off';
	    $seat_plan                    = get_post_meta($event_id, 'mepsp_event_seat_plan_info', true) ? get_post_meta($event_id, 'mepsp_event_seat_plan_info', true) : [];
	    $seat_plan_visible            = get_post_meta($event_id, 'mp_event_seat_plan_visible', true) ? get_post_meta($event_id, 'mp_event_seat_plan_visible', true) : '1';

  //	  if(class_exists('MP_ESP_Frontend') && sizeof($seat_plan) > 0 && $seat_plan_visible ==2 &&$new_registration_system_sp=='on'){
 //		    do_action('mpwem_new_registration_system_sp',$event_id);
//	      }else{

        $saved_user_role 	          = get_post_meta($event_id, 'mep_member_only_user_role', true) ? get_post_meta($event_id, 'mep_member_only_user_role', true) : [];
        $event_member_type 	          = get_post_meta($event_id, 'mep_member_only_event', true) ? get_post_meta($event_id, 'mep_member_only_event', true) : 'for_all';


        $cart_btn_label               = array_key_exists('cart-btn-label',$params) ? esc_html($params['cart-btn-label']) : mep_get_label($event_id, 'mep_cart_btn_text', esc_html__('Register For This Event','mage-eventpress'));
        $ticket_type_label            = array_key_exists('ticket-label',$params) ? esc_html($params['ticket-label']) : mep_get_label($event_id, 'mep_event_ticket_type_text', esc_html__('Ticket Type:','mage-eventpress'));
        $extra_service_label          = array_key_exists('extra-service-label',$params) ? esc_html($params['extra-service-label']) : mep_get_label($event_id, 'mep_event_extra_service_text', esc_html__('Extra Service:','mage-eventpress'));
        $select_date_label            = array_key_exists('select-date-label',$params) ? esc_html($params['select-date-label']) : mep_get_option('mep_event_rec_select_event_date_text', 'label_setting_sec', __('Select Event Date:', 'mage-eventpress'));

        $total_book                 = 0;
        $post_id                    = $event_id ? $event_id : get_the_id();
        $event_meta                 = get_post_custom($post_id);
        $event_expire_on_old         = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
        $event_expire_on            = $event_expire_on_old == 'event_end_datetime' ? esc_html('event_expire_datetime') : $event_expire_on_old;
        $event_expire_date          = $event_meta[$event_expire_on][0];
       // $event_sqi                  = array_key_exists('mep_sqi',$event_meta) ? $event_meta['mep_sqi'][0] : '';
	    //==========
	    $mep_full_name         = array_key_exists('mep_full_name',$event_meta) && $event_meta['mep_full_name'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_full_name'][0]):'';
	    $mep_reg_email         = array_key_exists('mep_reg_email',$event_meta) && $event_meta['mep_reg_email'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_email'][0]):'';
	    $mep_reg_phone         = array_key_exists('mep_reg_phone',$event_meta) && $event_meta['mep_reg_phone'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_phone'][0]):'';
	    $mep_reg_address         = array_key_exists('mep_reg_address',$event_meta) && $event_meta['mep_reg_address'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_address'][0]):'';
	    $mep_reg_designation         = array_key_exists('mep_reg_designation',$event_meta) && $event_meta['mep_reg_designation'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_designation'][0]):'';
	    $mep_reg_website         = array_key_exists('mep_reg_website',$event_meta) && $event_meta['mep_reg_website'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_website'][0]):'';
	    $mep_reg_veg         = array_key_exists('mep_reg_veg',$event_meta) && $event_meta['mep_reg_veg'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_veg'][0]):'';
	    $mep_reg_company         = array_key_exists('mep_reg_company',$event_meta) && $event_meta['mep_reg_company'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_company'][0]):'';
	    $mep_reg_gender         = array_key_exists('mep_reg_gender',$event_meta) && $event_meta['mep_reg_gender'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_gender'][0]):'';
	    $mep_reg_tshirtsize         = array_key_exists('mep_reg_tshirtsize',$event_meta) && $event_meta['mep_reg_tshirtsize'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_tshirtsize'][0]):'';
	    //==========

        $time                       = strtotime($event_expire_date);
        $newformat                  = date('Y-m-d H:i:s', $time);
        $datetime1                  = new DateTime();
        $datetime2                  = new DateTime($newformat);
        $interval                   = $datetime1->diff($datetime2);
        $mep_event_ticket_type      = get_post_meta($post_id, 'mep_event_ticket_type', true) ? get_post_meta($post_id, 'mep_event_ticket_type', true) : array();
        $total_seat                 = apply_filters('mep_event_total_seat_counts', mep_event_total_seat($post_id, 'total'), $post_id);
        $total_resv                 = apply_filters('mep_event_total_resv_seat_count', mep_event_total_seat($post_id, 'resv'), $post_id);
        $recurring                  = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';
        $_upcoming_date              = !empty(mep_get_event_upcoming_date($post_id)) ? mep_get_event_upcoming_date($post_id) : '';
		$upcoming_date              = $recurring == 'no' ? '' : $_upcoming_date;
        $total_sold                 = mep_get_event_total_seat_left($post_id, $upcoming_date);
        $total_left                 = $total_seat - ($total_sold + $total_resv);        
        $total_left                 = $recurring == 'no' ? $total_left : 1;
       $reg_status                 = get_post_meta($event_id,'mep_reg_status',true) ? get_post_meta($event_id,'mep_reg_status',true) : '';
        $seat_left                  = apply_filters('mep_event_total_seat_left_count', $total_left, $post_id);
        $current                    = current_time('Y-m-d H:i:s');
        $time                       = strtotime($event_expire_date);
        $newformat                  = date('Y-m-d H:i:s', $time);
        

        if ($recurring == 'yes') {
            $event_more_dates         = get_post_meta($post_id, 'mep_event_more_date', true) ? get_post_meta($post_id, 'mep_event_more_date', true) : array();
            $md                       = end($event_more_dates);
            $more_date                = is_array($md) && array_key_exists('event_more_start_date', $md) && !empty($md['event_more_start_date']) ? $md['event_more_start_date'] . ' ' . $md['event_more_start_time'] : '';
            $newformat                = empty($event_more_dates) ?  $newformat : date('Y-m-d H:i:s', strtotime($more_date));
        }

        // $default_timezone_val       = get_option('timezone_string') ? get_option('timezone_string') : 'UTC';
        // date_default_timezone_set($default_timezone_val);

        $datetime1                  = new DateTime($newformat);
        $datetime2                  = new DateTime($current);
        $interval                   = date_diff($datetime2, $datetime1);
        $mep_available_seat         = get_post_meta($event_id,'mep_available_seat',true) ? get_post_meta($event_id,'mep_available_seat',true) : 'on';
			//array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';

        $leftt                      = apply_filters('mep_event_total_seat_count', $total_left, $post_id);
        $days                       = $interval->d;
        $hours                      = $interval->h;
        $minutes                    = $interval->i;
        $dd                         = $days > 0 ? $days . " days " : '';
        $hh                         = $hours > 0 ? $hours . " hours " : '';
        $mm                         = $minutes > 0 ? $minutes . " minutes " : '';
       // $qty_typec                  = array_key_exists('qty_box_type',$event_meta) ? $event_meta['qty_box_type'][0] : '';
        $cart_product_id            = get_post_meta($post_id, 'link_wc_product', true) ? esc_attr(get_post_meta($post_id, 'link_wc_product', true)) : esc_attr($post_id);
        $not_in_the_cart            = apply_filters('mep_check_product_into_cart',true,$cart_product_id);
        

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
               do_action('mep_event_no_seat_text',$post_id);
               do_action('mep_after_no_seat_notice',$post_id);

            } else {
                /**
                 * If everything is fine then its go on ....
                 */

                

                 if( $event_member_type == 'for_all' || ($event_member_type != 'for_all'  && is_user_logged_in() && ( in_array(wp_get_current_user()->roles[0],$saved_user_role) || in_array('all',$saved_user_role) ) )){
                     ?>

                    <input type='hidden' value="<?php echo esc_attr($extra_service_label); ?>" id='mep_extra_service_label'/>
                    <input type='hidden' value="<?php echo esc_attr($select_date_label); ?>" id='mep_select_date_label'/>


      <!-- Register Now Title -->
      <h4 class="mep-cart-table-title">
                    <?php echo mep_get_option('mep_register_now_text', 'label_setting_sec',__('Register Now:', 'mage-eventpress'));  ?>
                </h4>
                <!--The event add to cart main form start here-->
                <form action="" method='post' id="mage_event_submit" enctype="multipart/form-data">
			    <input type="hidden" name='' id='mep_event_id' value='<?php echo $event_id; ?>'>
                <div class="mpwemasp_ticket_area">
                    <?php
                    /**
                     * Here is a magic hook which fire just before of the Add to Cart Button, And the Ticket type & Extra service list are hooked up into this, You can find them into inc/template-parts/event_ticket_type_extra_service.php
                     */
                    do_action('mep_event_ticket_type_extra_service', $post_id, $ticket_type_label,$extra_service_label,$select_date_label );
                    ?>
                    <input type='hidden' id='rowtotal' value="<?php echo get_post_meta($post_id, "_price", true); ?>" />
					
					<input type="hidden" name='currency_symbol' value="<?php echo get_woocommerce_currency_symbol(); ?>">
                    <input type="hidden" name='currency_position' value="<?php echo get_option('woocommerce_currency_pos'); ?>">
                    <input type="hidden" name='currency_decimal' value="<?php echo wc_get_price_decimal_separator(); ?>">
                    <input type="hidden" name='currency_thousands_separator' value="<?php echo wc_get_price_thousand_separator(); ?>">
                    <input type="hidden" name='currency_number_of_decimal' value="<?php echo wc_get_price_decimals(); ?>">
                    <?php do_action('mep_add_term_condition',$post_id); ?>
		    </div>
                    <!--The Add to cart button table start Here-->
                    <table class='table table-bordered mep_event_add_cart_table'>
                        <tr>
                            <td align="left" class='total-col'>
                            <?php do_action('mep_before_price_calculation',$post_id); ?>
                            <?php echo mep_get_option('mep_quantity_text', 'label_setting_sec', __('Quantity:', 'mage-eventpress'));
                                                                if ($mep_event_ticket_type) { ?>
                                    <input id="quantity_5a7abbd1bff73" class="input-text qty text extra-qty-box" step="1" min="1" max="<?php echo esc_attr($leftt); ?>" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" type="hidden">
                                    <span id="ttyttl"></span>
                                <?php } ?>
                                <span class='the-total'> <?php echo mep_get_option('mep_total_text', 'label_setting_sec', __('Total', 'mage-eventpress'));  ?>
                                    <span id="usertotal"></span>
                                </span>
                                <?php do_action('mep_after_price_calculation',$post_id); ?>
                            </td>
                            <td align="right">
                                <?php do_action('mep_before_add_cart_btn',$post_id, false); ?>
                                <input type="hidden" name="mep_event_location_cart" value="<?php trim(mep_ev_location_ticket($post_id, $event_meta)); ?>">
                                <input type="hidden" name="mep_event_date_cart" value="<?php //do_action('mep_event_date'); ?>">
                                <?php if($not_in_the_cart && class_exists('MP_ESP_Frontend') && sizeof($seat_plan) > 0 && $seat_plan_visible ==2 &&$new_registration_system_sp=='on'){ ?>
						  <button type="submit" class="mpwemasp_get_sp"><?php esc_html_e("View Seat Plan","mage-eventpress"); ?></button>
						  <?php }if($not_in_the_cart){ ?>
						  <button type="submit" name="add-to-cart" value="<?php echo esc_attr($cart_product_id); ?>" class="button-default woocommerce button alt button alt btn-mep-event-cart"><?php do_action('mep_before_add_cart_button',$post_id); echo "<span class='mep-cart-btn-text'>".esc_html($cart_btn_label)."</span>"; do_action('mep_after_add_cart_button',$post_id); ?></button>
					    <?php }else{  ?>
                                        <a href="<?php echo wc_get_cart_url(); ?>" class="button-default woocommerce button alt button alt btn-mep-event-cart"><?php esc_html_e('You have already added this item to your cart! View your cart to continue shopping or checkout now.', 'mage-eventpress'); ?> </a>
                                    <?php } ?>
                                <?php do_action('mep_after_add_cart_btn',$post_id); ?>
                            </td>
                        </tr>
                    </table>
                    <!--The Add to cart button table start Here-->
                </form>
                <!--The event add to cart main form end here-->
                <?php
                 }else{
                     ?>
                        <span class="mep_warning">
                         <?php
                            esc_html_e("Whoops, this event is for members only. Login to view the content. Not a member? That's easy to fix!","mage-eventpress");
                            ?>
                        </span>
                     <?php
                 }        
        }
        } // End Of checking Registration status
	  }
    //}
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
        $event_expire_on            = $event_expire_on_old == 'event_end_datetime' ? esc_html('event_expire_datetime') : $event_expire_on_old;
        $event_expire_date          = $event_meta[$event_expire_on][0];
       // $event_sqi                  = $event_meta['mep_sqi'][0];
        $mep_full_name              = mage_array_strip($event_meta['mep_full_name'][0]);
        $mep_reg_email              = mage_array_strip($event_meta['mep_reg_email'][0]);
        $mep_reg_phone              = mage_array_strip($event_meta['mep_reg_phone'][0]);
        $mep_reg_address            = mage_array_strip($event_meta['mep_reg_address'][0]);
        $mep_reg_designation        = mage_array_strip($event_meta['mep_reg_designation'][0]);
        $mep_reg_website            = mage_array_strip($event_meta['mep_reg_website'][0]);
        $mep_reg_veg                = mage_array_strip($event_meta['mep_reg_veg'][0]);
        $mep_reg_company            = mage_array_strip($event_meta['mep_reg_company'][0]);
        $mep_reg_gender             = mage_array_strip($event_meta['mep_reg_gender'][0]);
        $mep_reg_tshirtsize         = mage_array_strip($event_meta['mep_reg_tshirtsize'][0]);

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
       
        // $upcoming_date              = !empty(mep_get_event_upcoming_date($post_id)) ? mep_get_event_upcoming_date($post_id) : '';
        $upcoming_date              = '';
        $total_sold                 = mep_get_event_total_seat_left($post_id, $upcoming_date);
        $total_left                 = $total_seat - ($total_sold + $total_resv);        
        // $total_left              = mep_get_event_total_seat_left($post_id, $upcoming_date);

       $reg_status                 = get_post_meta($event_id,'mep_reg_status',true) ? get_post_meta($event_id,'mep_reg_status',true) : '';
        $seat_left                  = apply_filters('mep_event_total_seat_left_count', $total_left, get_the_id());
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
        $mep_available_seat         = get_post_meta($event_id,'mep_available_seat',true) ? get_post_meta($event_id,'mep_available_seat',true) : 'on';
			
			//array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';

        $leftt                      = apply_filters('mep_event_total_seat_left_count', $total_left, get_the_id());
        $days                       = $interval->d;
        $hours                      = $interval->h;
        $minutes                    = $interval->i;
        $dd                         = $days > 0 ? $days . " days " : '';
        $hh                         = $hours > 0 ? $hours . " hours " : '';
        $mm                         = $minutes > 0 ? $minutes . " minutes " : '';
       // $qty_typec                  = $event_meta['qty_box_type'][0];
        $cart_product_id            = get_post_meta($post_id, 'link_wc_product', true) ? esc_html(get_post_meta($post_id, 'link_wc_product', true)) : esc_html($post_id);
        $not_in_the_cart            = apply_filters('mep_check_product_into_cart',true,$cart_product_id);
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
                do_action('mep_event_no_seat_text',$post_id);
                do_action('mep_after_no_seat_notice',$post_id);
            } else {
                /**
                 * If everything is fine then its go on ....
                 */
            ?>
                <!-- Register Now Title -->
                <h4 class="mep-cart-table-title">
                    <?php echo mep_get_option('mep_register_now_text', 'label_setting_sec', __('Register Now:', 'mage-eventpress'));  ?>
                </h4>
                <!--The event add to cart main form start here-->
                <form action="" method='post' id="mage_event_submit" enctype="multipart/form-data">
                    <?php
                    /**
                     * Here is a magic hook which fire just before of the Add to Cart Button, And the Ticket type & Extra service list are hooked up into this, You can find them into inc/template-parts/event_ticket_type_extra_service.php
                     */
                    do_action('mep_event_ticket_types_list');
                    do_action('mep_event_extra_service_list');
                    ?>
                    <input type='hidden' id='rowtotal' value="<?php echo get_post_meta($post_id, "_price", true); ?>" />
					
					<input type="hidden" name='currency_symbol' value="<?php echo get_woocommerce_currency_symbol(); ?>">
                    <input type="hidden" name='currency_position' value="<?php echo get_option('woocommerce_currency_pos'); ?>">
                    <input type="hidden" name='currency_decimal' value="<?php echo wc_get_price_decimal_separator(); ?>">
                    <input type="hidden" name='currency_thousands_separator' value="<?php echo wc_get_price_thousand_separator(); ?>">
                    <input type="hidden" name='currency_number_of_decimal' value="<?php echo wc_get_price_decimals(); ?>">
                    <?php do_action('mep_add_term_condition',$post_id); ?>
                    <!--The Add to cart button table start Here fff-->
                    <table class='table table-bordered mep_event_add_cart_table'>
                        <tr>
                            <td align="left" class='total-col'><?php echo mep_get_option('mep_quantity_text', 'label_setting_sec',__('Quantity:', 'mage-eventpress'));
                                                                if ($mep_event_ticket_type) { ?>
                                    <input id="quantity_5a7abbd1bff73" class="input-text qty text extra-qty-box" step="1" min="1" max="<?php echo esc_attr($leftt); ?>" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" type="hidden">
                                    <span id="ttyttl"></span>
                                <?php } ?>
                                <span class='the-total'> <?php echo mep_get_option('mep_total_text', 'label_setting_sec', __('Total', 'mage-eventpress'));  ?>
                                    <span id="usertotal"></span>
                                </span>
                            </td>
                            <td align="right" class='mep-event-cart-btn-sec'>
                                <input type="hidden" name="mep_event_location_cart" value="<?php trim(mep_ev_location_ticket($post_id, $event_meta)); ?>">
                                <input type="hidden" name="mep_event_date_cart" value="<?php //do_action('mep_event_date'); ?>">
                                <?php if($not_in_the_cart){ ?>
                                    <button type="submit" name="add-to-cart" value="<?php echo esc_html($cart_product_id); ?>" class="button-default woocommerce button alt button alt btn-mep-event-cart"><?php do_action('mep_before_add_cart_button',$post_id); esc_html_e(mep_get_label($post_id, 'mep_cart_btn_text', __('Register For This Event','mage-eventpress')), 'mage-eventpress'); do_action('mep_after_add_cart_button',$post_id); ?></button>
                                <?php }else{ ?>
                                     <a href="<?php echo wc_get_cart_url(); ?>" class="button-default woocommerce button alt button alt btn-mep-event-cart"><?php esc_html_e('Already Added into Cart!', 'mage-eventpress'); ?> </a>
                                <?php } ?>
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