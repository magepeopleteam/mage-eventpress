<div class="mep-event-faq-part">
  <div class="faq-title-section"><?php esc_html_e('Event FAQs', 'mage-eventpress'); ?></div>
    <div class="faq-body">
      <?php foreach ($mep_event_faq as $field):
	      //echo '<pre>';print_r( $field );echo '</pre>';
          ?>
        <div class="mep-event-faq-set">
          <a class="faq-question">
              <?php if ($field['mep_faq_title'] != '') echo esc_html($field['mep_faq_title']); ?> 
              <i class="fa fa-plus"></i>
          </a>
          <div class="mep-event-faq-content">
              <?php if ($field['mep_faq_content'] != '') echo wpautop(wp_kses_post($field['mep_faq_content']));; ?>
          </div>
        </div>
      <?php endforeach; ?>
  </div>
</div>