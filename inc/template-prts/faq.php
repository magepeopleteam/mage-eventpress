<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_faq', 'mep_faq_part');
if (!function_exists('mep_faq_part')) {
    function mep_faq_part($event_id)
    {
        ob_start();
        $mep_event_faq = get_post_meta($event_id, 'mep_event_faq', true) ? get_post_meta($event_id, 'mep_event_faq', true) : '';
        if ($mep_event_faq) {
            require(mep_template_file_path('single/faq.php'));
        }
        $content = ob_get_clean();
        echo apply_filters('mage_event_faq_list', $content, $event_id);
    }
}
