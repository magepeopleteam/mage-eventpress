<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$type = $type ?? '';
	$hide_location = MP_Global_Function::get_settings('single_event_setting_sec', 'mep_event_hide_location_from_details', 'no');
	if ($event_id > 0 && $hide_location == 'no') {
		$location = MPWEM_Functions::get_location($event_id);
		if (sizeof($location) > 0) {
			ob_start();
			if ($type) {
				echo esc_html($location[$type]);
			} else {
				?>
                <div class="mpwem_location">
                    <span class="fas fa-map-marker-alt"></span>&nbsp;&nbsp;<?php echo esc_html(implode(', ', $location)); ?>
                </div>
				<?php
			}
			echo ob_get_clean();
		}
	}