<?php $event_date_icon = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'fa fa-calendar'); ?>
<h3>
    <i class="<?php echo $event_date_icon; ?>"></i>
    <?php echo mep_get_option('mep_event_schedule_text', 'label_setting_sec', __('Event Schedule Details', 'mage-eventpress')); ?>
</h3>