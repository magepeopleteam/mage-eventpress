<?php 
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_organizer','mep_ev_org');
if (!function_exists('mep_ev_org')) {
function mep_ev_org(){
	global $post,$author_terms;
    ob_start();
    if($author_terms){ ?><p> <?php echo mep_get_option('mep_by_text', 'label_setting_sec') ? mep_get_option('mep_by_text', 'label_setting_sec') : _e('By:','mage-eventpress');  ?> <a href="<?php  echo get_term_link( $author_terms[0]->term_id, 'mep_org' );  ?>"><?php  echo $author_terms[0]->name; ?></a></p><?php } 
    $content = ob_get_clean();
    echo apply_filters('mage_event_single_org_name', $content,$post->ID);
}
}