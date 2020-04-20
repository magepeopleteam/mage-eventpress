<?php 
add_action('mep_event_details','mep_ev_details');
function mep_ev_details(){
    global $post, $event_meta;  
    $content_event      = get_post($post->ID);
    $content            = $content_event->post_content;
    $content            = apply_filters('the_content', $content);
    $content            = str_replace(']]>', ']]&gt;', $content);
    echo apply_filters( 'mep_event_details_content', $content, get_the_id() );
    do_action('mep_after_event_details');
}



add_action('mep_after_event_details','mep_display_event_daywise_details');
function mep_display_event_daywise_details(){
    global $post, $event_meta;  
    $mep_event_day = get_post_meta($post->ID, 'mep_event_day', true) ? get_post_meta($post->ID, 'mep_event_day', true) : array();
    if ( sizeof($mep_event_day) > 0 ){
        echo '<div class="mep-day-details-section">';
    ?>
        <h4><?php _e('Event Days','mage-eventpress'); ?></h4>
        <?php
        foreach ( $mep_event_day as $field ) {
        ?>
          <div class="mep-day-title"><?php echo $field['mep_day_title']; ?></div>
          <div class="mep-day-details">
            <p><?php echo $field['mep_day_content']; ?></p>
          </div>
        <?php
      }
        echo '</div>';
    }
}