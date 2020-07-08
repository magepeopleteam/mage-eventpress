<?php
if (!defined('ABSPATH')) {
  die;
} // Cannot access pages directly.

/**
 * This File is a very important file, Because Its gettings Data from user selection on event details page, and prepare the data send to cart item and lastly save into order table after checkout
 */


 /**
  * This Function Recieve the date from user selection and add them into the cart session data
  */
function mep_add_custom_fields_text_to_cart_item($cart_item_data, $product_id, $variation_id)
{

  $linked_event_id        = get_post_meta($product_id, 'link_mep_event', true) ? get_post_meta($product_id, 'link_mep_event', true) : $product_id;
  $product_id             = mep_product_exists($linked_event_id) ? $linked_event_id : $product_id;
  $recurring              = get_post_meta($product_id, 'mep_enable_recurring', true) ? get_post_meta($product_id, 'mep_enable_recurring', true) : 'no';

  if (get_post_type($product_id) == 'mep_events') {
    /**
     * Getting and Preparing Data From User Selection
     */
    $total_price            = get_post_meta($product_id, '_price', true);
    $form_position          = mep_get_option('mep_user_form_position', 'general_attendee_sec', 'details_page');
    $mep_event_start_date   = isset($_POST['mep_event_start_date']) ? $_POST['mep_event_start_date'] : array();
    $event_cart_location    = isset($_POST['mep_event_location_cart']) ? $_POST['mep_event_location_cart'] : array();
    $event_cart_date        = isset($_POST['mep_event_date_cart']) ? $_POST['mep_event_date_cart'] : array();
    $recurring_event_date   = $recurring == 'yes' && isset($_POST['recurring_event_date']) ? $_POST['recurring_event_date'] : array();
    $ticket_type_arr        = mep_cart_ticket_type('ticket_type', $total_price,$product_id);
    $total_price            = mep_cart_ticket_type('ticket_price', $total_price,$product_id);
    $event_extra            =  mep_cart_event_extra_service('event_extra_service', $total_price);
    $total_price            =  mep_cart_event_extra_service('ticket_price', $total_price);
    $user                   = $form_position == 'details_page' ? mep_save_attendee_info_into_cart($product_id) : array();
    $validate               = mep_cart_ticket_type('validation_data', $total_price,$product_id);


// echo '<pre>';
// print_r($user);
// print_r($ticket_type_arr);
// die();

    /**
     * Now Store the datas into Cart Session
     */
    $time_slot_text = isset($_REQUEST['time_slot_name']) ? $_REQUEST['time_slot_name'] : '';
    if(!empty($time_slot_text)){
     $cart_item_data['event_everyday_time_slot']  = $time_slot_text;
    } 
    
    
    $cart_item_data['event_ticket_info']    = $ticket_type_arr;
    $cart_item_data['event_validate_info']  = $validate;
    $cart_item_data['event_user_info']      = $user;
    $cart_item_data['event_tp']             = $total_price;
    $cart_item_data['line_total']           = $total_price;
    $cart_item_data['line_subtotal']        = $total_price;
    $cart_item_data['event_extra_service']  = $event_extra;
    $cart_item_data['event_cart_location']  = $event_cart_location;
    $cart_item_data['event_cart_date']      = $mep_event_start_date[0];
    $cart_item_data['event_recurring_date'] = array_unique($recurring_event_date);
    $cart_item_data['event_recurring_date_arr'] = $recurring_event_date;
    $cart_item_data['event_cart_display_date']  = $mep_event_start_date[0];
    do_action('mep_event_cart_data_reg');
  }
  $cart_item_data['event_id']             = $product_id;

  return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'mep_add_custom_fields_text_to_cart_item', 90, 3);


/**
 * Now need to update the cart price according to user selection, the below function is doing this part, Its getting the new parice and update the cart price to new
 */
add_action('woocommerce_before_calculate_totals', 'mep_add_custom_price', 90, 1);
function mep_add_custom_price($cart_object)
{
  foreach ($cart_object->cart_contents as $key => $value) {
    $event_id = array_key_exists('event_id', $value) ? $value['event_id'] : 0;
      if (get_post_type($event_id) == 'mep_events') {
        $event_total_price = $value['event_tp'];
        $value['data']->set_price($event_total_price);
        $value['data']->set_regular_price($event_total_price);
        $value['data']->set_sale_price($event_total_price);
        $value['data']->set_sold_individually('yes');
        $value['data']->get_price();
      }
  }
}




/**
 * After update the price now need to show user what they selected and the Price details, the below fuunction is for that, Its showing the details into the cart below the event name.
 */
function mep_display_custom_fields_text_cart($item_data, $cart_item)
{
  $mep_events_extra_prices = array_key_exists('event_extra_option', $cart_item) ? $cart_item['event_extra_option'] : array(); //$cart_item['event_extra_option'];

  $eid                    = array_key_exists('event_id', $cart_item) ? $cart_item['event_id'] : 0; //$cart_item['event_id'];

  if (get_post_type($eid) == 'mep_events') {
$hide_location_status  = mep_get_option('mep_hide_location_from_order_page', 'general_setting_sec', 'no');
$hide_date_status  = mep_get_option('mep_hide_date_from_order_page', 'general_setting_sec', 'no');
    $user_info                  = $cart_item['event_user_info'];
    $ticket_type_arr            = $cart_item['event_ticket_info'];
    $event_extra_service        = $cart_item['event_extra_service'];
    $event_recurring_date       = $cart_item['event_recurring_date'];
 


    $recurring = get_post_meta($eid, 'mep_enable_recurring', true) ? get_post_meta($eid, 'mep_enable_recurring', true) : 'no';

    echo "<ul class='event-custom-price'>";

    if ($recurring == 'yes') {
      if (is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0 && sizeof($user_info) == 0) {
     
        foreach ($ticket_type_arr as $_event_recurring_date) {
            if($hide_date_status == 'no'){
          ?>
          <li><?php _e('Event Date', 'mage-eventpress'); ?>: <?php echo get_mep_datetime($_event_recurring_date['event_date'],'date-text'); ?></li>
        <?php
            }
        }
      }
      if (is_array($user_info) && sizeof($user_info) > 0) {
        echo '<li>';
           echo mep_cart_display_user_list($user_info);
        echo '</li>';
      }
    } else {
      if (is_array($user_info) && sizeof($user_info) > 0) {
        echo '<li>';
          echo mep_cart_display_user_list($user_info);
        echo '</li>';
      } else {
           if($hide_date_status == 'no'){
      ?>
        <li><?php _e('Event Date', 'mage-eventpress'); ?>: <?php echo get_mep_datetime($cart_item['event_cart_display_date'],'date-time-text'); ?></li>
      <?php
           }
      }
    }
     if($hide_location_status == 'no'){
  ?>    
    <li><?php _e('Event Location', 'mage-eventpress'); ?>: <?php echo $cart_item['event_cart_location']; ?></li>
  <?php
     }
    if (is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0) {
      echo mep_cart_display_ticket_type_list($ticket_type_arr);
    }
    if (is_array($event_extra_service) && sizeof($event_extra_service) > 0) {
      foreach ($event_extra_service as $extra_service) {
        echo '<li>' . $extra_service['service_name'] . " - " . wc_price($extra_service['service_price']) . ' x ' . $extra_service['service_qty'] . ' = ' . wc_price( (int) $extra_service['service_price'] * (int) $extra_service['service_qty']) . '</li>';
      }
    }
    do_action('mep_after_cart_item_display_list',$cart_item);
    echo "</ul>";
  }
  return $item_data;
}
add_filter('woocommerce_get_item_data', 'mep_display_custom_fields_text_cart', 90, 2);


/**
 * Now before placing the order we need to check seats are available or not, the below function doing this task its validate the user selected seat numbers are available or not.
 */
add_action('woocommerce_after_checkout_validation', 'mep_checkout_validation');
function mep_checkout_validation($posted)
{
  global $woocommerce;
  $items    = $woocommerce->cart->get_cart();
  foreach ($items as $item => $values) {
    $event_id              = array_key_exists('event_id', $values) ? $values['event_id'] : 0; // $values['event_id'];
    if (get_post_type($event_id) == 'mep_events') {
      $recurring                  = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
      $total_seat = apply_filters('mep_event_total_seat_counts', mep_event_total_seat($event_id, 'total'), $event_id);
      $total_resv = apply_filters('mep_event_total_resv_seat_count', mep_event_total_seat($event_id, 'resv'), $event_id);      
      $total_sold = mep_ticket_sold($event_id);
      $total_left = $total_seat - ($total_sold + $total_resv);
      if($recurring == 'no'){
        $event_validate_info        = $values['event_validate_info'] ? $values['event_validate_info'] : array();
        $ee = 0;
        if (is_array($event_validate_info) && sizeof($event_validate_info) > 0) {
          foreach ($event_validate_info as $inf) {
            $ee = $ee + $inf['validation_ticket_qty'];
          }
        }
        if ($ee > $total_left) {
          $event = get_the_title($event_id);
          wc_add_notice(__("Sorry, Seats are not available in <b>$event</b>, Available Seats <b>$total_left</b> but you selected <b>$ee</b>", 'mage-eventpress'), 'error');
        }
    }
    }
  }
}



/**
 * The Final function for cart handleing, If everything is fine after user hit the place order button then the below function will send the order data into the next hook for order processing and save to order meta data.
 */
function mep_add_custom_fields_text_to_order_items($item, $cart_item_key, $values, $order)
{

  $eid   = array_key_exists('event_id', $values) ? $values['event_id'] : 0; //$values['event_id'];

  if (get_post_type($eid) == 'mep_events') {
    $mep_events_extra_prices = $values['event_extra_option'];
    $cart_location           = $values['event_cart_location'];
    $event_extra_service     = $values['event_extra_service'];
    $ticket_type_arr         = $values['event_ticket_info'];
    $cart_date               = $values['event_cart_date'];
    $form_position           = mep_get_option('mep_user_form_position', 'general_attendee_sec', 'details_page');
    $event_user_info         = $form_position == 'details_page' ? $values['event_user_info'] : mep_save_attendee_info_into_cart($eid);
    $recurring               = get_post_meta($eid, 'mep_enable_recurring', true) ? get_post_meta($eid, 'mep_enable_recurring', true) : 'no';

    if ($recurring == 'yes') {
      if (is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0) {
        foreach ($ticket_type_arr as $_event_recurring_date) {
          $item->add_meta_data('Date', get_mep_datetime($_event_recurring_date['event_date'], 'date-time-text'));
        }
      }
    } else {
      $item->add_meta_data('Date',$cart_date);
    }

    if (is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0) {    
      
     mep_cart_order_data_save_ticket_type($item,$ticket_type_arr);

    }

    if (is_array($event_extra_service) && sizeof($event_extra_service) > 0) {
      foreach ($event_extra_service as $extra_service) {
        $service_type_name = $extra_service['service_name'] . " - " . wc_price($extra_service['service_price']) . ' x ' . $extra_service['service_qty'] . ' = ';
        $service_type_val = wc_price($extra_service['service_price'] * $extra_service['service_qty']);
        $item->add_meta_data($service_type_name, $service_type_val);
      }
    }

    $item->add_meta_data('Location', $cart_location);
    $item->add_meta_data('_event_ticket_info', $ticket_type_arr);
    $item->add_meta_data('_event_user_info', $event_user_info);
    $item->add_meta_data('_event_service_info', $mep_events_extra_prices);
    $item->add_meta_data('event_id', $eid);
    $item->add_meta_data('_product_id', $eid);
    $item->add_meta_data('_event_extra_service', $event_extra_service);
    do_action('mep_event_cart_order_data_add',$values,$item);
  }
}
add_action('woocommerce_checkout_create_order_line_item', 'mep_add_custom_fields_text_to_order_items', 90, 4);