<?php
if (!defined('ABSPATH')) {
   die;
} // Cannot access pages directly.

add_action('mep_event_speakers_list_shortcode_template', 'mep_shortcode_speaker_list_html');
if (!function_exists('mep_shortcode_speaker_list_html')) {
   function mep_shortcode_speaker_list_html($event_id)
   {
?>
      <div class="mep-default-sidebar-speaker-list">
         <?php echo mep_display_speaker_list($event_id); ?>
      </div>
<?php
   }
}

if (!function_exists('mep_shortcode_all_speaker_list_html')) {
   function mep_shortcode_all_speaker_list_html()
   {
?>
      <div class="mep-default-sidebar-speaker-list">
         <?php echo mep_display_all_speaker_list(); ?>
      </div>
<?php
   }
}
