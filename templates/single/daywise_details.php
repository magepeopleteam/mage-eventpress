<div class="mep-day-details-section">
    <h4><?php _e('Event Days', 'mage-eventpress'); ?></h4>
    <?php
    foreach ($mep_event_day as $field) {
    ?>
        <div class="mep-day-title"><?php echo $field['mep_day_title']; ?></div>
        <div class="mep-day-details">
            <p><?php echo $field['mep_day_content']; ?></p>
        </div>
    <?php
    }
    ?>
</div>