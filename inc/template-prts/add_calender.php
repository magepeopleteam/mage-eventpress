<?php 
add_action('mep_event_add_calender','mep_ev_calender');

function mep_ev_calender(){
?>
<div class="calender-url">
	<?php 
	/**
	 * Action Hook mep_before_add_calendar_button & mep_after_add_calendar_button
	 */
	    do_action('mep_before_add_calendar_button');
	        mep_add_to_google_calender_link(get_the_id()); 
	    do_action('mep_after_add_calendar_button');
	?>
</div>
<?php

}

