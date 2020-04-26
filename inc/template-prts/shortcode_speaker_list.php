<?php
add_action('mep_event_speakers_list_shortcode_template','mep_shortcode_speaker_list_html');
function mep_shortcode_speaker_list_html($event_id){
?>
 <div class="mep-default-sidebar-speaker-list">           
    <?php echo mep_display_speaker_list($event_id); ?>
 </div>
<?php
}