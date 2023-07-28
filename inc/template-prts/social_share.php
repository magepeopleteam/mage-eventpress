<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_social_share', 'mep_ev_social_share');
if (!function_exists('mep_ev_social_share')) {
    function mep_ev_social_share($event_id)
    {
        global $post;
        ob_start();
        $post_id = $event_id;
        $event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
        require(mep_template_file_path('single/share_btn.php'));
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_social_share', $content, $event_id);
    }
}
