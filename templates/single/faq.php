<div class="mep-event-faq-part">
    <h3 class="ex-sec-title"><?php esc_html_e('Event F.A.Q', 'mage-eventpress'); ?></h3>
    <div id='mep-event-accordion' class="">
        <?php
        foreach ($mep_event_faq as $field) {
        ?>
            <h3><?php if ($field['mep_faq_title'] != '') echo esc_html($field['mep_faq_title']); ?></h3>
            <p><?php if ($field['mep_faq_content'] != '') echo esc_html($field['mep_faq_content']); ?></p>
        <?php
        }
        ?>
    </div>
</div>