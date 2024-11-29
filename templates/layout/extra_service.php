<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$all_dates = $all_dates ?? MPWEM_Functions::get_dates($event_id);
	$all_times = $all_times ?? MPWEM_Functions::get_times($event_id, $all_dates);
	$date = $date ?? MPWEM_Functions::get_upcoming_date_time($event_id, $all_dates, $all_times);
	$total_sold = mep_get_event_total_seat_left($event_id, $date);
	$total_ticket = MPWEM_Functions::get_total_ticket($event_id);
	$total_reserve = MPWEM_Functions::get_reserve_ticket($event_id);
	$total_available = $total_ticket - ($total_sold + $total_reserve);
	if ($total_available > 0) {
		$ex_services = MP_Global_Function::get_post_info($event_id, 'mep_events_extra_prices', []);
		//echo '<pre>'; print_r($ex_services); echo '</pre>';
		$count = 0;
		if (sizeof($ex_services) > 0) { ?>
            <div class="mpwem_ex_service ">
                <h4><?php esc_html_e('Extra Service', 'mage-eventpress'); ?></h4>
                <div class="_dLayout">
					<?php foreach ($ex_services as $ticket_type) {
						$ticket_name = array_key_exists('option_name', $ticket_type) ? $ticket_type['option_name'] : '';
						$ticket_price = array_key_exists('option_price', $ticket_type) ? $ticket_type['option_price'] : 0;
						$ticket_price = MP_Global_Function::get_wc_raw_price($event_id, $ticket_price);
						$ticket_qty = array_key_exists('option_qty', $ticket_type) ? $ticket_type['option_qty'] : 0;
						$ticket_input_type = array_key_exists('option_qty_type', $ticket_type) ? $ticket_type['option_qty_type'] : 'inputbox';
						$available = MPWEM_Functions::get_available_ex_service($event_id, $ticket_name, $date, $ticket_type);
						if ($ticket_name && $ticket_qty > 0) {
							$input_data['name'] = 'event_extra_service_qty[]';
							$input_data['price'] = $ticket_price;
							$input_data['available'] = $available;
							$input_data['type'] = $ticket_input_type;
							$count++;
							if ($count > 1) { ?>
                                <div class="_divider"></div>
							<?php } ?>
                            <div class="justifyBetween">
                                <div class="">
                                    <h6><?php echo esc_html($ticket_name); ?></h6>
                                    <input type="hidden" name="event_extra_service_name[]" value="<?php echo esc_attr($ticket_name); ?>" />
                                </div>
                                <div class="">
                                    <h6 class="_textCenter"><?php echo wc_price($ticket_price); ?></h6>
									<?php MP_Custom_Layout::qty_input($input_data); ?>
                                </div>
                            </div>
							<?php
						}
					} ?>
                </div>
            </div>
			<?php //echo '<pre>';print_r($ticket_types);echo '</pre>';
		}
	}