<?php 
function mep_output_add_to_cart_custom_fields() {
global $post,$event_meta,$total_book;

		$total_seat = mep_event_total_seat(get_the_id(),'total');
		$total_resv = mep_event_total_seat(get_the_id(),'resv');
		$total_sold = mep_ticket_sold(get_the_id());
		$total_left = $total_seat - ($total_sold + $total_resv);
        $total_left = apply_filters( 'mep_event_total_seat_count', $total_left, get_the_id() );
if($total_left>0){
    do_action('mep_event_ticket_types');
    do_action('mep_event_extra_service');
}else{
    ?>
      <span class=event-expire-btn>
       <?php  echo mep_get_option('mep_no_seat_available_text', 'label_setting_sec') ? mep_get_option('mep_no_seat_available_text', 'label_setting_sec') : _e('No Seat Available','mage-eventpress');  ?>
      </span>
    <?php
    do_action('mep_after_no_seat_notice');
  }
}
add_action( 'mep_event_ticket_type_extra_service', 'mep_output_add_to_cart_custom_fields', 10 );