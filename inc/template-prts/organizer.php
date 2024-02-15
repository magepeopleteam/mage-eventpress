<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_organizer', 'mep_ev_org');
if (!function_exists('mep_ev_org')) {
    function mep_ev_org($event_id)
    {
        global $post, $author_terms;
        ob_start();
        $org = get_the_terms($event_id, 'mep_org'); 
		if(!empty($org)){
            require(mep_template_file_path('single/organizer.php'));
		}
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_org_name', $content, $event_id);
    }
}


add_action('mep_event_organizer_name', 'mep_ev_org_name');
if (!function_exists('mep_ev_org_name')) {
    function mep_ev_org_name()
    {
        global $post, $author_terms;
        ob_start();
            $org = get_the_terms(get_the_id(), 'mep_org');
            $names = [];
            if(sizeof($org) > 0){
            foreach ($org as $key => $value) {
                $names[] = $value->name;
            }
            }
            echo esc_html(implode(', ',$names));        
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_org_name', $content, $post->ID);
    }
}