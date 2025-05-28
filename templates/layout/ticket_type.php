<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id        = $event_id ?? 0;
	$all_dates       = $all_dates ?? MPWEM_Functions::get_dates( $event_id );
	$all_times       = $all_times ?? MPWEM_Functions::get_times( $event_id, $all_dates );
	$date            = $date ?? MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	$total_sold      = mep_get_event_total_seat_left( $event_id, $date );
	$total_ticket    = MPWEM_Functions::get_total_ticket( $event_id );
	$total_reserve   = MPWEM_Functions::get_reserve_ticket( $event_id );
	$total_available = $total_ticket - ( $total_sold + $total_reserve );
	//echo '<pre>';print_r(current_time( 'Y-m-d H:i:s' ) );echo '</pre>';
	//echo '<pre>';print_r($date);echo '</pre>';
	if ( $total_available > 0 ) {
		do_action( 'mepgq_max_qty_hook', $event_id, max( $total_available, 0 ) );
		$ticket_types = MP_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
		$count        = 0;
		if ( sizeof( $ticket_types ) > 0 ) { ?>
            <div class="mpwem_ticket_type">
				<div class="card-header"><?php esc_html_e('Ticket Options', 'mage-eventpress'); ?></div>
				<div class="card-body">
					<?php foreach ( $ticket_types as $ticket_type ) {
						// ===============remaining items===================
						$event_expire_on_old        = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
						$event_expire_on            = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
						$current_time = apply_filters(
							'mep_ticket_current_time',
							current_time('Y-m-d H:i'),
							get_post_meta($event_id, $event_expire_on, true),
							$event_id
						);

						$ticket_type_name    = array_key_exists('option_name_t', $ticket_type) ? mep_remove_apostopie($ticket_type['option_name_t']) : '';
						$ticket_type_mode    = array_key_exists('option_qty_t_type', $ticket_type) ? $ticket_type['option_qty_t_type'] : 'input';
						$ticket_type_qty     = array_key_exists('option_qty_t', $ticket_type) ? $ticket_type['option_qty_t'] : 0;
						$ticket_type_price   = array_key_exists('option_price_t', $ticket_type) ? $ticket_type['option_price_t'] : 0;
						$qty_t_type          = $ticket_type_mode;
						$total_quantity      = array_key_exists('option_qty_t', $ticket_type) ? $ticket_type['option_qty_t'] : 0;
						$ticket_details      = array_key_exists('option_details_t', $ticket_type) ? esc_html($ticket_type['option_details_t']) : '';

						$sale_start_datetime = apply_filters(
							'mep_sale_start_datetime',
							date('Y-m-d H:i', strtotime(get_the_date('Y-m-d H:i:s', $event_id))),
							$event_id,
							$ticket_type
						);
						$event_expire_date   = get_post_meta($event_id, 'event_expire_datetime', true) ? get_post_meta($event_id, 'event_expire_datetime', true) : '';
						$sale_end_datetime = array_key_exists('option_sale_end_date_t', $ticket_type) && !empty($ticket_type['option_sale_end_date_t'])
							? date('Y-m-d H:i', strtotime($ticket_type['option_sale_end_date_t']))
							: date('Y-m-d H:i', strtotime($event_expire_date));

						$default_qty         = array_key_exists('option_default_qty_t', $ticket_type) && $ticket_type['option_default_qty_t'] > 0 ? $ticket_type['option_default_qty_t'] : 0;
						$total_resv_quantity = array_key_exists('option_rsv_t', $ticket_type) ? $ticket_type['option_rsv_t'] : 0;

						$event_date = get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
						$event_start_date = $event_date;

						$total_sold = mep_get_ticket_type_seat_count($event_id, $ticket_type_name, $event_date, $total_quantity, $total_resv_quantity);
						$total_tickets = (int) $total_quantity - ((int) $total_sold + (int) $total_resv_quantity);

						$total_seats = apply_filters('mep_total_ticket_of_type', $total_tickets, $event_id, $ticket_type, $event_date);
						$total_min_seat = apply_filters('mep_ticket_min_qty', 0, $event_id, $ticket_type);
						$default_quantity = apply_filters('mep_ticket_default_qty', $default_qty, $event_id, $ticket_type);
						$total_left = apply_filters('mep_total_ticket_of_type', $total_tickets, $event_id, $ticket_type, $event_date);
						$total_ticket_left = apply_filters('mep_total_ticket_left_of_type', $total_tickets, $event_id, $ticket_type, $event_date);

						$ticket_price = apply_filters('mep_ticket_type_price', $ticket_type_price, $ticket_type_name, $event_id, $ticket_type);
						$passed = apply_filters('mep_ticket_type_validation', true);

						$start_date = get_post_meta($event_id, 'event_start_datetime', true);
						$end_date = get_post_meta($event_id, 'event_end_datetime', true);

						$mep_available_seat = get_post_meta($event_id, 'mep_available_seat', true);
						$mep_available_seat = isset($mep_available_seat) ? $mep_available_seat : 'on';
						$low_stock_displayed = isset($GLOBALS[$date_specific_low_stock_key]) ? $GLOBALS[$date_specific_low_stock_key] : false;
						// ===============remaining items===================
						$ticket_permission = apply_filters( 'mpwem_ticket_permission', true, $ticket_type );
						if ( $ticket_permission ) {
							//echo '<pre>';print_r($ticket_type);echo '</pre>';
							$ticket_name       = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
							$ticket_details    = array_key_exists( 'option_details_t', $ticket_type ) ? $ticket_type['option_details_t'] : '';
							$ticket_price      = array_key_exists( 'option_price_t', $ticket_type ) ? $ticket_type['option_price_t'] : 0;
							$ticket_price      = MPWEM_Functions::get_ticket_price( $event_id, $ticket_price, $ticket_name, $ticket_type );
							$ticket_qty        = array_key_exists( 'option_qty_t', $ticket_type ) ? $ticket_type['option_qty_t'] : 0;
							$ticket_d_qty      = array_key_exists( 'option_default_qty_t', $ticket_type ) ? $ticket_type['option_default_qty_t'] : 0;
							$ticket_min_qty    = array_key_exists( 'option_min_qty', $ticket_type ) ? $ticket_type['option_min_qty'] : 0;
							$ticket_max_qty    = array_key_exists( 'option_max_qty', $ticket_type ) ? $ticket_type['option_max_qty'] : '';
							$ticket_input_type = array_key_exists( 'option_qty_t_type', $ticket_type ) ? $ticket_type['option_qty_t_type'] : 'inputbox';
							$available         = MPWEM_Functions::get_available_ticket( $event_id, $ticket_name, $date, $ticket_type );
							if ( $ticket_name && $ticket_qty > 0 ) {
								$input_data['name']      = 'option_qty[]';
								$input_data['price']     = $ticket_price;
								$input_data['available'] = $available;
								$input_data['d_qty']     = $ticket_d_qty;
								$input_data['min_qty']   = $ticket_min_qty;
								$input_data['max_qty']   = $ticket_max_qty;
								$input_data['type']      = $ticket_input_type;
								$count ++;
								?>
								<div class="mep_ticket_item">
									<div class="ticket-info">
										<div class="ticket-name"><?php echo esc_html( $ticket_name ); ?></div>
										<?php if ( $ticket_details ) { ?>
											<div class="ticket-description"><?php echo esc_html( $ticket_details ); ?></div>
										<?php } ?>
										<?php 
											if ($mep_available_seat == 'on' && !$low_stock_displayed):
												$ticket_left = max($total_ticket_left, 0);
											?>
											<div class="ticket-remaining xtra-item-left <?php echo $ticket_left <= 10 ?'remaining-low':'remaining-high'; ?>">
												<?php echo esc_html($ticket_left).__(' Tickets remaining'); ?>
											</div>
										<?php endif; ?>
										
									</div>
									<div class="quantity-control">
										<input type="hidden" name='option_name[]' value='<?php echo esc_attr( $ticket_name ); ?>'/>
										<input type="hidden" name='ticket_type[]' value='<?php echo esc_attr( $ticket_name ); ?>'/>
										<?php
											$early_date = apply_filters( 'mpwem_early_date', true, $ticket_type, $event_id );
											if ( $early_date ) {
												$sale_end_datetime   = array_key_exists( 'option_sale_end_date_t', $ticket_type ) && ! empty( $ticket_type['option_sale_end_date_t'] ) ? date( 'Y-m-d H:i', strtotime( $ticket_type['option_sale_end_date_t'] ) ) : '';
												if($sale_end_datetime){
													$current_time=current_time( 'Y-m-d H:i' );
													if ( strtotime( $current_time ) < strtotime( $sale_end_datetime )) {
														MP_Custom_Layout::qty_input( $input_data );
													}else{
														?>
                                                        <span class='early-bird-future-date-txt' style="font-size: 12px;"><?php _e( 'Sale close On: ', 'mage-evetpress' );
																echo get_mep_datetime( $sale_end_datetime, 'date-time-text' ); ?></span>
                                                        <input type="hidden" name="option_qty[]" value="0" data-price="<?php echo esc_attr( $ticket_price ); ?>"/>
														<?php
													}
												}else {
													MP_Custom_Layout::qty_input( $input_data );
												}
											} else {
												$sale_start_datetime = array_key_exists( 'option_sale_start_date_t', $ticket_type ) && ! empty( $ticket_type['option_sale_start_date_t'] ) ? date( 'Y-m-d H:i', strtotime( $ticket_type['option_sale_start_date_t'] ) ) : '';
												?>
												<span class='early-bird-future-date-txt' style="font-size: 12px;"><?php _e( 'Available On: ', 'mage-evetpress' );
														echo get_mep_datetime( $sale_start_datetime, 'date-time-text' ); ?></span>
												<input type="hidden" name="option_qty[]" value="0" data-price="<?php echo esc_attr( $ticket_price ); ?>"/>
												<?php
											}
										?>
									</div>
									<div class="ticket-price">
										<?php echo wc_price( $ticket_price ); ?>
									</div>
								</div>
								<?php do_action( 'mpwem_multi_attendee', $event_id ); ?>
								<?php
							}
						}
					} ?>
				</div>
            </div>
			<?php
			//echo '<pre>';print_r($ticket_types);echo '</pre>';
		}
	} else {
		MPWEM_Layout::msg( esc_html__( 'Sorry, no ticket available', 'mage-eventpress' ) );
	}

//	echo '<pre>';print_r($total_ticket);echo '</pre>';
//	echo '<pre>';print_r($total_reserve);echo '</pre>';
//	echo '<pre>';print_r($total_sold);echo '</pre>';
//	echo '<pre>';print_r($all_dates);echo '</pre>';