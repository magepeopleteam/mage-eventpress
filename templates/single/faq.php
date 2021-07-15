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