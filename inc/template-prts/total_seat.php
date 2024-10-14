<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_seat', 'mep_ev_seat');
if (!function_exists('mep_ev_seat')) {
    function mep_ev_seat($event_id)
    {
        global $post;
        // echo $event_id;
        $event_id 		= mep_get_default_lang_event_id($event_id);
        $event_meta 	= get_post_custom($event_id);
        $recurring 		= get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';

        ob_start();
        if ($recurring == 'no') {
            $mep_event_ticket_type 	= get_post_meta($event_id, 'mep_event_ticket_type', true) ? get_post_meta($event_id, 'mep_event_ticket_type', true) : array();
            $event_date 			= get_post_meta($event_id, 'event_start_date', true) ? get_post_meta($event_id, 'event_start_date', true) : '';
            $mep_available_seat 	= get_post_meta($event_id, 'mep_available_seat', true) ? get_post_meta($event_id, 'mep_available_seat', true) : 'on';
            //array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';
            if (is_array($mep_event_ticket_type) && sizeof($mep_event_ticket_type) > 0) {
                // $upcoming_date 		= '';
				$upcoming_date      = !empty(mep_get_event_upcoming_date($event_id)) ? mep_get_event_upcoming_date($event_id) : '';
                $total_seat 		= apply_filters('mep_event_total_seat_counts', mep_event_total_seat($event_id, 'total'), $event_id);
                $total_resv 		= apply_filters('mep_event_total_resv_seat_count', mep_event_total_seat($event_id, 'resv'), $event_id);
             $total_sold = mep_ticket_type_sold($event_id);
                $total_sold 		= mep_get_event_total_seat_left($event_id, $upcoming_date);
                $total_left 		= (int) $total_seat - ((int) $total_sold + (int) $total_resv);
                // $total_seat = apply_filters('mep_event_total_seat_count', $_total_left, $event_id,'',$event_date);
                require mep_template_file_path('single/total_seat.php');
            }
        }
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_total_seat', $content, $event_id);
    }
}
