<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_title', 'mep_ev_title');
if (!function_exists('mep_ev_title')) {
    function mep_ev_title($event_id)
    {
        
        global $post;      
        ob_start();
        require(mep_template_file_path('single/title.php'));
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_title', $content, $event_id);
    }
}

add_action('mep_event_only_title', 'mep_ev_only_title');
if (!function_exists('mep_ev_only_title')) {
    function mep_ev_only_title()
    {
        global $post, $event_id;
        ob_start();
        require(mep_template_file_path('single/title_only.php'));
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_title', $content, $event_id);
    }
}