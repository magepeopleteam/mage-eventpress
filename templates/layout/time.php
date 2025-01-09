<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$date = $date ?? '';
	$single = $single ?? true;
	$all_dates = $all_dates ?? MPWEM_Functions::get_dates($event_id);
	$date_type = MP_Global_Function::get_post_info($event_id, 'mep_enable_recurring', 'no');
	if (sizeof($all_dates) > 0) {
		$all_times = $all_times ?? MPWEM_Functions::get_times($event_id, $all_dates, $date);
		if (sizeof($all_times) > 0) {
			if ($single) {
				$all_times = current($all_times);
				$start_time = array_key_exists('start', $all_times) ? $all_times['start']['time'] : '';
				$end_time = array_key_exists('end', $all_times) ? $all_times['end']['time'] : '';
				?>
                <div class="mpwem_time">
                    <i class="far fa-clock"></i>&nbsp;&nbsp;<?php echo esc_html(MP_Global_Function::date_format($start_time, 'time') . ' ' . ($end_time ? ' - ' . MP_Global_Function::date_format($end_time, 'time') : '')); ?>
                </div>
				<?php
			} else {
				foreach ($all_times as $time) {
					$start_time = array_key_exists('start', $time) ? $time['start']['time'] : '';
					$end_time = array_key_exists('end', $time) ? $time['end']['time'] : '';
					?>
                    <div class="mpwem_time">
                        <i class="fas fa-clock"></i>&nbsp;&nbsp;<?php echo esc_html(MP_Global_Function::date_format($start_time, 'time') . ' ' . ($end_time ? ' - ' . MP_Global_Function::date_format($end_time, 'time') : '')); ?>
                    </div>
					<?php
				}
			}
		}
	}