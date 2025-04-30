<div class="mep-event-ticket-type">
  <h3 class="mep-event-ticket-type-header"><?php esc_html_e('Event FAQs', 'mage-eventpress'); ?></h3>
  <?php foreach ($mep_event_faq as $field): ?>  
    <div class="mep-event-faq-set">
      <a class="mep_faq_title">
          <?php if ($field['mep_faq_title'] != '') echo esc_html($field['mep_faq_title']); ?> 
          <i class="fa fa-plus"></i>
      </a>
      <div class="mep-event-faq-content">
          <?php if ($field['mep_faq_content'] != '') echo wp_kses_post(html_entity_decode(nl2br($field['mep_faq_content']))); ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>