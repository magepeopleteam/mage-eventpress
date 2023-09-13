<?php
if (!defined('ABSPATH')) {
  die;
} // Cannot access pages directly.

add_action('mep_event_ticket_type_extra_service', 'mep_output_add_to_cart_custom_fields', 10,4);

if (!function_exists('mep_output_add_to_cart_custom_fields')) {
  function mep_output_add_to_cart_custom_fields($post_id,$ticket_type_label,$extra_service_label,$select_date_label)
  {
    global $post, $event_meta, $total_book;
    
    $upcoming_date               = !empty(mep_get_event_upcoming_date($post_id)) ? mep_get_event_upcoming_date($post_id) : '';
    $total_seat = mep_event_total_seat($post_id, 'total');
    $total_resv = mep_event_total_seat($post_id, 'resv');
    $total_sold = mep_get_event_total_seat_left($post_id, $upcoming_date);
    $_total_left = $total_seat - ($total_sold + $total_resv);    
    // $_total_left                 = mep_get_event_total_seat_left($post_id, $upcoming_date);
    

    $total_left = apply_filters('mep_event_total_seat_count', $_total_left, $post_id);

    // $total_left = 10;
    
    if ($total_left > 0) {
      
      do_action('mep_event_ticket_types', $post_id,$ticket_type_label,$select_date_label);
      do_action('mep_event_extra_service', $post_id,$extra_service_label);

    } else {
?>
      <span class=event-expire-btn>
        <?php echo mep_get_option('mep_no_seat_available_text', 'label_setting_sec', __('Sorry, There Are No Seats Available', 'mage-eventpress'));  ?>
      </span>
<?php
      do_action('mep_after_no_seat_notice');
    }
  }
}
