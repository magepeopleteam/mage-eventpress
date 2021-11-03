<div class="mep-day-details-section">
    <h4><?php esc_html_e('Event Timelines', 'mage-eventpress'); ?></h4>
    <?php
    foreach ($mep_event_day as $field) {
    ?>
        <div class="mep-day-title"><?php echo esc_html($field['mep_day_title']); ?></div>
        <div class="mep-day-details">
            <p><?php echo esc_html($field['mep_day_content']); ?></p>
        </div>
    <?php
    }
    ?>
</div>