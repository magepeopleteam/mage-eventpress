<div class="mep-day-details-section">
    <h4><?php esc_html_e('Event Timelines', 'mage-eventpress'); ?></h4>   
    <?php
    $i = 1;
    foreach ($mep_event_day as $field):
    ?>
    <div class="mep-day-details-item">
        <div class="mep-day-icon"><?php echo $i; ?></div>
        <div class="mep-day-content">
            <div class="mep-day-title"><?php echo esc_html($field['mep_day_title']); ?></div>
            <div class="mep-day-details">
                <?php echo mep_esc_html(html_entity_decode(nl2br($field['mep_day_content']))); ?>
            </div>
        </div>
    </div>    
    <?php
    $i++;
    endforeach;
    ?>
</div>