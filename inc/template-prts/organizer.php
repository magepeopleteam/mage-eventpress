<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_organizer', 'mep_ev_org');
if (!function_exists('mep_ev_org')) {
    function mep_ev_org()
    {
        global $post, $author_terms;
        ob_start();
        if ($author_terms) {
            require(mep_template_file_path('single/organizer.php'));
        }
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_org_name', $content, $post->ID);
    }
}
