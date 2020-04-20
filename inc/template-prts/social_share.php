<?php
add_action('mep_event_social_share', 'mep_ev_social_share');
function mep_ev_social_share(){
global $post;
    ob_start();    
    $post_id = $post->ID;
    ?>
    <div class="mep-event-meta">
        <?php _e(mep_get_label($post_id, 'mep_share_text', 'Share This Event:'), 'mage-eventpress'); ?><?php mep_social_share(); ?>
    </div>
    <?php
    $content = ob_get_clean();
    echo apply_filters('mage_event_single_social_share', $content,$post->ID);    
}
