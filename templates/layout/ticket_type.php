<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id           = $event_id ?? 0;
	$all_dates          = $all_dates ?? MPWEM_Functions::get_dates( $event_id );
	$all_times          = $all_times ?? MPWEM_Functions::get_times( $event_id, $all_dates );
	$date               = empty($date )? MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times ):$date;
	$total_available    = MPWEM_Functions::get_total_available_seat( $event_id, $date );
	$total_available=max( $total_available, 0 );
	$mep_available_seat = MP_Global_Function::get_post_info( $event_id, 'mep_available_seat', 'on' );
	if ( $total_available > 0 ) {
		do_action( 'mepgq_max_qty_hook', $event_id, $total_available, $date );
		$ticket_types = MP_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
		$date_type    = MP_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
		$count        = 0;
		if ( sizeof( $ticket_types ) > 0 ) { ?>
            <div class="mpwem_ticket_type">
				<?php if ( $date_type == 'no' ) { ?>
                    <div class="card-header"><?php esc_html_e( 'Ticket Options', 'mage-eventpress' ); ?></div>
				<?php } ?>
                <div class="card-body">
					<?php foreach ( $ticket_types as $ticket_type ) {
						$input_data        = [];
						$ticket_permission = apply_filters( 'mpwem_ticket_permission', true, $ticket_type );
						if ( $ticket_permission ) {
							//echo '<pre>';print_r($ticket_type);echo '</pre>';
							$ticket_name       = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
							$ticket_details    = array_key_exists( 'option_details_t', $ticket_type ) ? $ticket_type['option_details_t'] : '';
							$ticket_price      = array_key_exists( 'option_price_t', $ticket_type ) ? $ticket_type['option_price_t'] : 0;
							$ticket_price      = MPWEM_Functions::get_ticket_price( $event_id, $ticket_price, $ticket_name, $ticket_type );
							$ticket_qty        = array_key_exists( 'option_qty_t', $ticket_type ) ? $ticket_type['option_qty_t'] : 0;
							$ticket_qty = apply_filters( 'filter_mpwem_gq_ticket', $ticket_qty, $total_available, $event_id );
							$ticket_d_qty      = array_key_exists( 'option_default_qty_t', $ticket_type ) ? $ticket_type['option_default_qty_t'] : 0;
							//$ticket_min_qty    = array_key_exists( 'option_min_qty', $ticket_type ) ? $ticket_type['option_min_qty'] : 0;
							$ticket_min_qty = apply_filters( 'filter_mpwem_min_ticket', 0, $event_id, $ticket_type );
							$ticket_max_qty = apply_filters( 'filter_mpwem_max_ticket', '', $event_id, $ticket_type );

							//$ticket_max_qty    = array_key_exists( 'option_max_qty', $ticket_type ) ? $ticket_type['option_max_qty'] : '';
							$ticket_input_type = array_key_exists( 'option_qty_t_type', $ticket_type ) ? $ticket_type['option_qty_t_type'] : 'inputbox';
							$available         = MPWEM_Functions::get_available_ticket( $event_id, $ticket_name, $date, $ticket_type );
							$available = apply_filters( 'filter_mpwem_gq_ticket', $available, $total_available, $event_id );
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
                                    <div class="ticket-data">
                                        <div class="ticket-info">
                                            <div class="ticket-name"><?php echo esc_html( $ticket_name ); ?></div>
											<?php if ( $ticket_details ) { ?>
                                                <div class="ticket-description"><?php echo esc_html( $ticket_details ); ?></div>
											<?php } ?>
											<?php if ( $mep_available_seat == 'on' ) { ?>
                                                <div class="ticket-remaining xtra-item-left <?php echo $available <= 10 ? 'remaining-low' : 'remaining-high'; ?>">
													<?php echo esc_html( max( $available, 0 ) ) . __( ' Tickets remaining','mage-eventpress' ); ?>
                                                </div>
											<?php } ?>
                                        </div>
                                        <div class="quantity-control">
                                            <input type="hidden" name='option_name[]' value='<?php echo esc_attr( $ticket_name ); ?>'/>
                                            <input type="hidden" name='ticket_type[]' value='<?php echo esc_attr( $ticket_name ); ?>'/>
											<?php
												$early_date = apply_filters( 'mpwem_early_date', true, $ticket_type, $event_id );
												if ( $early_date ) {
													$sale_end_datetime = array_key_exists( 'option_sale_end_date_t', $ticket_type ) && ! empty( $ticket_type['option_sale_end_date_t'] ) ? date( 'Y-m-d H:i', strtotime( $ticket_type['option_sale_end_date_t'] ) ) : '';
													if ( $sale_end_datetime ) {
														$current_time = current_time( 'Y-m-d H:i' );
														if ( strtotime( $current_time ) < strtotime( $sale_end_datetime ) ) {
															MPWEM_Custom_Layout::qty_input( $input_data );
														} else {
															?>
                                                            <span class='early-bird-future-date-txt' style="font-size: 12px;"><?php _e( 'Sale close On: ', 'mage-evetpress' );
																	echo get_mep_datetime( $sale_end_datetime, 'date-time-text' ); ?></span>
                                                            <input type="hidden" name="option_qty[]" value="0" data-price="<?php echo esc_attr( $ticket_price ); ?>"/>
															<?php
														}
													} else {
														MPWEM_Custom_Layout::qty_input( $input_data );
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
                                </div>
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
        do_action('mep_after_no_seat_notice', $event_id);
	}

//	echo '<pre>';print_r($total_ticket);echo '</pre>';
//	echo '<pre>';print_r($total_reserve);echo '</pre>';
//	echo '<pre>';print_r($total_sold);echo '</pre>';
//	echo '<pre>';print_r($all_dates);echo '</pre>';