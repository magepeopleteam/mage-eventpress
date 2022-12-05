<div class="mep-event-faq-part">
<h3 class="ex-sec-title"><?php esc_html_e('Event FAQs', 'mage-eventpress'); ?></h3>
<?php foreach ($mep_event_faq as $field): ?>  
  <div class="mep-event-faq-set">
    <a>
        <?php if ($field['mep_faq_title'] != '') echo esc_html($field['mep_faq_title']); ?> 
        <i class="fa fa-plus"></i>
    </a>
    <div class="mep-event-faq-content">
        <?php if ($field['mep_faq_content'] != '') echo mep_esc_html(html_entity_decode(nl2br($field['mep_faq_content']))); ?>
    </div>
  </div>
<?php endforeach; ?>
</div>