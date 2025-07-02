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
	$total_available    = MPWEM_Functions::get_total_available_seat( $event_id, $date );
	$total_ex_available    = MPWEM_Functions::get_total_available_ex( $event_id, $date );
	$mep_available_seat = MP_Global_Function::get_post_info( $event_id, 'mep_available_seat', 'on' );
	if ($total_available > 0 && $total_ex_available>0) {
		do_action( 'mepgq_max_ex_qty_hook', $event_id, $total_ex_available, $date );
		$ex_services = MP_Global_Function::get_post_info($event_id, 'mep_events_extra_prices', []);
		//echo '<pre>'; print_r($ex_services); echo '</pre>';
		$count = 0;
		if (sizeof($ex_services) > 0) { ?>
            <div class="mpwem_ex_service ">
				<div class="card-header"><?php esc_html_e('Extra Service', 'mage-eventpress'); ?></div>
                <div class="card-body">
					<?php foreach ($ex_services as $ticket_type) {
						$ticket_name = array_key_exists('option_name', $ticket_type) ? $ticket_type['option_name'] : '';
						$ticket_price = array_key_exists('option_price', $ticket_type) ? $ticket_type['option_price'] : 0;
						$ticket_price = MP_Global_Function::get_wc_raw_price($event_id, $ticket_price);
						$ticket_qty = array_key_exists('option_qty', $ticket_type) ? $ticket_type['option_qty'] : 0;
						$ticket_qty = apply_filters( 'filter_mpwem_gq_ticket', $ticket_qty, $total_ex_available, $event_id );
						$ticket_input_type = array_key_exists('option_qty_type', $ticket_type) ? $ticket_type['option_qty_type'] : 'inputbox';
						$available = MPWEM_Functions::get_available_ex_service($event_id, $ticket_name, $date, $ticket_type);
						$available = apply_filters( 'filter_mpwem_gq_ex_service', $available, $total_ex_available, $event_id );
						if ($ticket_name && $ticket_qty > 0) {
							$input_data=[];
							$input_data['name'] = 'event_extra_service_qty[]';
							$input_data['price'] = $ticket_price;
							$input_data['available'] = $available;
							$input_data['type'] = $ticket_input_type;
							$count++;?>
                            <div class="mep_ticket_item">
								<div class="ticket-data">
									<div class="ticket-info">
										<div class="ticket-name"><?php echo esc_html($ticket_name); ?></div>
										<input type="hidden" name="event_extra_service_name[]" value="<?php echo esc_attr($ticket_name); ?>" />
										<?php if ( $mep_available_seat == 'on' ) { ?>
											<div class="ticket-remaining xtra-item-left <?php echo $available <= 10 ? 'remaining-low' : 'remaining-high'; ?>">
												<?php echo esc_html( max( $available, 0 ) ) . __( ' Tickets remaining', 'mage-eventpress' ); ?>
											</div>
										<?php } ?>
									</div>
									<div class="quantity-control">
										<?php MPWEM_Custom_Layout::qty_input($input_data); ?>
									</div>
									<div class="ticket-price">
										<?php echo wc_price($ticket_price); ?>
									</div>
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