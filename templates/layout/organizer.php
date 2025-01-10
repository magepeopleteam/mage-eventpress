<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$only = $only ?? '';
	$hide_organizer = MP_Global_Function::get_settings('single_event_setting_sec', 'mep_event_hide_org_from_details', 'no');
	if ($event_id > 0 && $hide_organizer == 'no') {
		$org = get_the_terms($event_id, 'mep_org');
		$names = [];
		if ($org && is_array($org) && sizeof($org) > 0) {
			foreach ($org as $value) {
				$names[] = $value->name;
			}
		}
		if (sizeof($names) > 0) {
			ob_start();
			if ($only) {
				echo esc_html(implode(', ', $names));
			} else {
				$org_title = MP_Global_Function::get_settings('label_setting_sec', 'mep_by_text', esc_html__('By:', 'mage-eventpress'));
				?>
                <div class="mpwem_organizer">
                    <span><?php echo esc_html($org_title); ?></span>&nbsp;&nbsp;
                    <div class="mpwem_organizer_item">
						<?php
							$count = 0;
							foreach ($org as $value) {
								?><a href="<?php esc_url(get_term_link($value->term_id, 'mep_org')); ?>"><?php echo esc_html($value->name); ?></a><?php
								echo $count > 0 ? ' , ' : '';
								$count++;
							}
						?>
                    </div>
                </div>
				<?php
			}
			$content = ob_get_clean();
			echo apply_filters('mage_event_single_org_name', $content, $event_id);
		}
	}