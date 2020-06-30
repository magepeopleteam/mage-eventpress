<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_extra_service', 'mep_ev_extra_serv');
if (!function_exists('mep_ev_extra_serv')) {
    function mep_ev_extra_serv($post_id)
    {
        global $post, $product;
        $post_id                        = $post_id;
        $count                      = 1;
        $mep_events_extra_prices    = get_post_meta($post_id, 'mep_events_extra_prices', true) ? get_post_meta($post_id, 'mep_events_extra_prices', true) : array();
        ob_start();
        if (sizeof($mep_events_extra_prices) > 0) {
            require(mep_template_file_path('single/extra_service_list.php'));
        }
        $content = ob_get_clean();
        $event_meta = get_post_custom($post_id);
        echo apply_filters('mage_event_extra_service_list', $content, $post_id, $event_meta);
    }
}
