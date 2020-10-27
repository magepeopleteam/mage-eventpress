<div id="mep_add_calender_button" class='mep-add-calender'><i class="fa fa-calendar"></i><?php _e(mep_get_label($pid,'mep_calender_btn_text','Add Calendar'),'mage-eventpress'); ?></div>  
  <ul id="mep_add_calender_links">
  <li><a href="https://calendar.google.com/calendar/r/eventedit?text=<?php echo $event->post_title; ?>&dates=<?php echo mep_calender_date($event_start); ?>/<?php echo mep_calender_date($event_end); ?>&details=<?php echo substr(strip_tags($event->post_content),0,1000); ?>&location=<?php echo $location; ?>&sf=true" rel="noopener noreferrer" target='_blank' class='mep-add-calender' rel="nofollow">Google</a></li>
  <li><a href="https://calendar.yahoo.com/?v=60&view=d&type=20&title=<?php echo $event->post_title; ?>&st=<?php echo mep_calender_date($event_start); ?>&et=<?php echo mep_calender_date($event_end); ?>&desc=<?php echo substr(strip_tags($event->post_content),0,1000); ?>&in_loc=<?php echo $location; ?>&uid=" rel="noopener noreferrer" target='_blank' class='mep-add-calender' rel="nofollow">Yahoo</a></li>
  <li><a href ="https://outlook.live.com/owa/?path=/calendar/view/Month&rru=addevent&startdt=<?php echo mep_calender_date($event_start); ?>&enddt=<?php echo mep_calender_date($event_end); ?>&subject=<?php echo $event->post_title; ?>" rel="noopener noreferrer" target='_blank' class='mep-add-calender' rel="nofollow">Outlook</a></li>
  <li><a href="https://webapps.genprod.com/wa/cal/download-ics.php?date_end=<?php echo mep_calender_date($event_end); ?>&date_start=<?php echo mep_calender_date($event_start); ?>&summary=<?php echo $event->post_title; ?>&location=<?php echo $location; ?>&description=<?php echo substr(strip_tags($event->post_content),0,1000); ?>" rel="noopener noreferrer" target='_blank' class='mep-add-calender'>Apple</a></li>
  </ul>