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
		do_action('mepgq_max_qty_hook', $event_id, max($total_available, 0));
		$ticket_types = MP_Global_Function::get_post_info($event_id, 'mep_event_ticket_type', []);
		$count = 0;
		if (sizeof($ticket_types) > 0) { ?>
            <div class="mpwem_ticket_type _dLayout">
				<?php foreach ($ticket_types as $ticket_type) {
                   // echo '<pre>';print_r($ticket_type);echo '</pre>';
					$ticket_name = array_key_exists('option_name_t', $ticket_type) ? $ticket_type['option_name_t'] : '';
					$ticket_details = array_key_exists('option_details_t', $ticket_type) ? $ticket_type['option_details_t'] : '';
					$ticket_price = array_key_exists('option_price_t', $ticket_type) ? $ticket_type['option_price_t'] : 0;
					$ticket_price = MPWEM_Functions::get_ticket_price($event_id, $ticket_price, $ticket_name, $ticket_type);
					$ticket_qty = array_key_exists('option_qty_t', $ticket_type) ? $ticket_type['option_qty_t'] : 0;
					$ticket_d_qty = array_key_exists('option_default_qty_t', $ticket_type) ? $ticket_type['option_default_qty_t'] : 0;
					$ticket_min_qty = array_key_exists('option_min_qty', $ticket_type) ? $ticket_type['option_min_qty'] : 0;
					$ticket_max_qty = array_key_exists('option_max_qty', $ticket_type) ? $ticket_type['option_max_qty'] : '';
					$ticket_input_type = array_key_exists('option_qty_t_type', $ticket_type) ? $ticket_type['option_qty_t_type'] : 'inputbox';
					$available = MPWEM_Functions::get_available_ticket($event_id, $ticket_name, $date, $ticket_type);
					if ($ticket_name && $ticket_qty > 0) {
						$input_data['name'] = 'option_qty[]';
						$input_data['price'] = $ticket_price;
						$input_data['available'] = $available;
						$input_data['d_qty'] = $ticket_d_qty;
						$input_data['min_qty'] = $ticket_min_qty;
						$input_data['max_qty'] = $ticket_max_qty;
						$input_data['type'] = $ticket_input_type;
						$count++;
						if ($count > 1) { ?>
                            <div class="_divider"></div>
						<?php } ?>
                        <div class="mep_ticket_item">
                            <div class="justifyBetween">
                                <div class="">
                                    <h6><?php echo esc_html($ticket_name); ?></h6>
									<?php if ($ticket_details) { ?>
                                        <p><?php echo esc_html($ticket_details); ?></p>
									<?php } ?>
                                </div>
                                <div class="">
                                    <h6 class="_textCenter"><?php echo wc_price($ticket_price); ?></h6>
                                    <input type="hidden" name='option_name[]' value='<?php echo esc_attr($ticket_name); ?>'/>
                                    <input type="hidden" name='ticket_type[]' value='<?php echo esc_attr($ticket_name); ?>'/>
									<?php MP_Custom_Layout::qty_input($input_data); ?>
                                </div>
                            </div>
							<?php do_action('mpwem_multi_attendee', $event_id); ?>
                        </div>
						<?php
					}
				} ?>
            </div>
			<?php
			//echo '<pre>';print_r($ticket_types);echo '</pre>';
		}
	} else {
		MPWEM_Layout::msg(esc_html__('Sorry, no ticket available', 'mage-eventpress'));
	}

//	echo '<pre>';print_r($total_ticket);echo '</pre>';
//	echo '<pre>';print_r($total_reserve);echo '</pre>';
//	echo '<pre>';print_r($total_sold);echo '</pre>';
//	echo '<pre>';print_r($all_dates);echo '</pre>';