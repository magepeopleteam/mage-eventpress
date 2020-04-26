<?php
add_action('mep_event_speakers_list_shortcode_template','mep_shortcode_speaker_list_html');
function mep_shortcode_speaker_list_html($event_id){
ob_start();
?>
 <div class="mep-default-sidebar-speaker-list">            
    <?php do_action('mep_event_speakers_list',$event_id); ?>
 </div>
<?php
echo ob_get_clean();
}