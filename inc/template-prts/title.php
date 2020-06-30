<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_title', 'mep_ev_title');
if (!function_exists('mep_ev_title')) {
    function mep_ev_title()
    {
        global $post;
        ob_start();
        require(mep_template_file_path('single/title.php'));
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_title', $content, $post->ID);
    }
}