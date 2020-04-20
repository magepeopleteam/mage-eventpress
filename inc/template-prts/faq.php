<?php
add_action('mep_event_faq', 'mep_faq_part');
function mep_faq_part(){
    global $post;
    ob_start();
    $mep_event_faq = get_post_meta($post->ID, 'mep_event_faq', true) ? get_post_meta($post->ID, 'mep_event_faq', true) : '';
    if ($mep_event_faq) {
        ?>
        <div class="mep-event-faq-part">
            <h3 class="ex-sec-title"><?php _e('Event F.A.Q', 'mage-eventpress'); ?></h3>
            <div id='mep-event-accordion' class="">
                <?php
                foreach ($mep_event_faq as $field) {
                    ?>
                    <h3><?php if ($field['mep_faq_title'] != '') echo esc_attr($field['mep_faq_title']); ?></h3>
                    <p><?php if ($field['mep_faq_content'] != '') echo esc_attr($field['mep_faq_content']); ?></p>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    $content = ob_get_clean();
    echo apply_filters('mage_event_faq_list', $content,$post->ID);
}
