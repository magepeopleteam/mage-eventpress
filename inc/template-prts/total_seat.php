<?php
if (!defined('ABSPATH')) {
	die;
} // Cannot access pages directly.

add_action('mep_event_seat', 'mep_ev_seat');
if (!function_exists('mep_ev_seat')) {
	function mep_ev_seat()
	{
		global $post;
		$event_meta               = get_post_custom(get_the_id());
		$recurring = get_post_meta(get_the_id(), 'mep_enable_recurring', true) ? get_post_meta(get_the_id(), 'mep_enable_recurring', true) : 'no';
		ob_start();
		if ($recurring == 'no') {

			$mep_event_ticket_type      = get_post_meta($post->ID, 'mep_event_ticket_type', true) ? get_post_meta($post->ID, 'mep_event_ticket_type', true) : array();
			$mep_available_seat         = array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';

			if (is_array($mep_event_ticket_type) && sizeof($mep_event_ticket_type) > 0) {
				$total_seat = apply_filters('mep_event_total_seat_counts', mep_event_total_seat(get_the_id(), 'total'), get_the_id());
				$total_resv = apply_filters('mep_event_total_resv_seat_count', mep_event_total_seat(get_the_id(), 'resv'), get_the_id());
				$total_sold = mep_ticket_sold(get_the_id());
				$total_left = $total_seat - ($total_sold + $total_resv);
				require(mep_template_file_path('single/total_seat.php'));
			}
		}

		$content = ob_get_clean();
		echo apply_filters('mage_event_single_title', $content, $post->ID);
	}
}
