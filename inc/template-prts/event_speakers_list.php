<?php

/**
 * This Function will hooked up with the speaker action hook mep_event_speakers_list to display the Event Speaker List
 */

 add_action('mep_event_speakers_list','mep_display_speaker_list');
 function mep_display_speaker_list($event_id){
    $speakers_id = get_post_meta($event_id,'mep_event_speakers_list',true) ? maybe_unserialize(get_post_meta($event_id,'mep_event_speakers_list',true)) : array();


    if(is_array($speakers_id) && sizeof($speakers_id) > 0){
        echo '<ul>';
        foreach($speakers_id as $speakers){
        ?>
        <li>
            <?php if(has_post_thumbnail($speakers)){ echo get_the_post_thumbnail($speakers,'medium'); }else{ echo '<img src="'.plugins_url( '../images/no-photo.jpg' , __DIR__ ).'"/>'; } ?>
            <h6><?php echo get_the_title($speakers); ?></h6>
        </li>
        <?php
        }
        echo '</ul>';
    }
 }