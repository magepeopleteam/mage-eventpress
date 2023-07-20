<?php
if (!defined('ABSPATH')) {
  die;
} // Cannot access pages directly.

add_action('mep_event_details', 'mep_ev_details');
if (!function_exists('mep_ev_details')) {
  function mep_ev_details($event_id)
  {
    global $post, $event_meta;
    $content_event      = get_post($event_id);
    $content            = $content_event->post_content;
    $content            = apply_filters('the_content', $content);
    $content            = str_replace(']]>', ']]&gt;', $content);
    require(mep_template_file_path('single/details.php'));    
    do_action('mep_after_event_details', $event_id);
  }
}

add_action('mep_event_details_only', 'mep_ev_details_only');
if (!function_exists('mep_ev_details_only')) {
  function mep_ev_details_only()
  {
    global $post, $event_meta;
    $content_event      = get_post($post->ID);
    $content            = $content_event->post_content;
    $content            = apply_filters('the_content', $content);
    $content            = str_replace(']]>', ']]&gt;', $content);
    echo $content;
  }
}

add_action('mep_after_event_details', 'mep_display_event_daywise_details');
if (!function_exists('mep_display_event_daywise_details')) {
  function mep_display_event_daywise_details($event_id)
  {
    global $post, $event_meta;
    $mep_event_day = get_post_meta($event_id, 'mep_event_day', true) ?  maybe_unserialize(get_post_meta($event_id, 'mep_event_day', true)) : array();
    if (sizeof($mep_event_day) > 0) {
      require(mep_template_file_path('single/daywise_details.php'));    
    }
  }
}