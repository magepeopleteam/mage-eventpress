<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * This Function will hooked up with the speaker action hook mep_event_speakers_list to display the Event Speaker List
 */

add_action('mep_event_speakers_list', 'mep_display_speaker_list');
if (!function_exists('mep_display_speaker_list')) {
    function mep_display_speaker_list($event_id)
    {
        $speakers_id = get_post_meta($event_id, 'mep_event_speakers_list', true) ? maybe_unserialize(get_post_meta($event_id, 'mep_event_speakers_list', true)) : array();
        $speaker_icon               = get_post_meta($event_id, 'mep_event_speaker_icon', true) ? get_post_meta($event_id, 'mep_event_speaker_icon', true) : 'fa fa-microphone';
        $speaker_label              = get_post_meta($event_id, 'mep_speaker_title', true) ? get_post_meta($event_id, 'mep_speaker_title', true) : __("Speaker's", "mage-eventpress");
        if (is_array($speakers_id) && sizeof($speakers_id) > 0) {
            require(mep_template_file_path('single/speaker-list.php'));
        }
    }
}

if (!function_exists('mep_display_all_speaker_list')) {
    function mep_display_all_speaker_list()
    {
        $args = array(
            'post_type'         => array('mep_event_speaker'),
            'posts_per_page'    => -1

        );
        $loop = new WP_Query($args);
        echo '<ul>';
        foreach ($loop->posts as $speaker) {
            $speakers = $speaker->ID;
            require(mep_template_file_path('all-speaker-list.php'));
        }
        echo '</ul>';
    }
}
