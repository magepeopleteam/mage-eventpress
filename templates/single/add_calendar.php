<?php 
$hide_calendar_details = mep_get_option('mep_event_hide_calendar_details', 'single_event_setting_sec', 'no');
$event_date_icon = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'far fa-calendar'); ?>
<?php if($hide_calendar_details=='no'): ?>
<div id="mep_add_calender_button" class='mep-add-calender'>
    <i class="<?php echo $event_date_icon; ?>"></i><?php echo esc_html(mep_get_label($pid,'mep_calender_btn_text',esc_html__('Add Calendar','mage-eventpress'))); ?>
</div>  
  <ul id="mep_add_calender_links">
    <li><a href="https://calendar.google.com/calendar/r/eventedit?text=<?php echo esc_html($event->post_title); ?>&dates=<?php echo esc_html(mep_calender_date($event_start)); ?>/<?php echo esc_html(mep_calender_date($event_end)); ?>&details=<?php echo esc_html(substr(mage_array_strip($event->post_content),0,1000)); ?>&location=<?php echo esc_html($location); ?>&sf=true" rel="noopener noreferrer" target='_blank' class='mep-add-calender' rel="nofollow"><?php esc_html_e('Google','mage-eventpress'); ?></a></li>
    <li><a href="https://calendar.yahoo.com/?v=60&view=d&type=20&title=<?php echo esc_html($event->post_title); ?>&st=<?php echo esc_html(mep_calender_date($event_start)); ?>&et=<?php echo esc_html(mep_calender_date($event_end)); ?>&desc=<?php echo esc_html(substr(mage_array_strip($event->post_content),0,1000)); ?>&in_loc=<?php echo esc_html($location); ?>&uid=" rel="noopener noreferrer" target='_blank' class='mep-add-calender' rel="nofollow"><?php esc_html_e('Yahoo','mage-eventpress'); ?></a></li>

    <li><a href ="https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent&startdt=<?php echo gmdate('Y-m-d\TH:i:s', strtotime($event_start)); ?>&enddt=<?php echo gmdate('Y-m-d\TH:i:s', strtotime($event_end)); ?>&subject=<?php echo esc_html($event->post_title); ?>&body=<?php echo esc_html($event->post_title); ?>" rel="noopener noreferrer" target='_blank' class='mep-add-calender' rel="nofollow"><?php esc_html_e('Outlook','mage-eventpress'); ?></a></li>

    <li><a href="https://webapps.genprod.com/wa/cal/download-ics.php?date_end=<?php echo esc_html(mep_calender_date($event_end)); ?>&date_start=<?php echo esc_html(mep_calender_date($event_start)); ?>&summary=<?php echo esc_html($event->post_title); ?>&location=<?php echo esc_html($location); ?>&description=<?php echo esc_html(substr(mage_array_strip($event->post_content),0,1000)); ?>" rel="noopener noreferrer" target='_blank' class='mep-add-calender'><?php esc_html_e('Apple','mage-eventpress'); ?></a></li>
  </ul>
<?php endif; ?>