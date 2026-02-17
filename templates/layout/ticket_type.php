<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id           = $event_id ?? 0;
	$all_dates          = MPWEM_Functions::get_dates( $event_id );
	$all_times          =MPWEM_Functions::get_times( $event_id, $all_dates );
	$date               = empty( $date ) ? MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times ) : $date;
	$total_available    = MPWEM_Functions::get_total_available_seat( $event_id, $date );
	$total_available    = max( $total_available, 0 );
	$mep_available_seat = MPWEM_Global_Function::get_post_info( $event_id, 'mep_available_seat', 'on' );
	$total_sold         = mep_ticket_type_sold( $event_id, '', $date );
	$total_ticket       = MPWEM_Functions::get_total_ticket( $event_id, $date );
	$total_reserve      = MPWEM_Functions::get_reserve_ticket( $event_id, $date );
	$total_available    = $total_ticket - ( $total_sold + $total_reserve );
	$total_available    = max( $total_available, 0 );
	if ( $total_available > 0 ) {
		do_action( 'mepgq_max_qty_hook', $event_id, $total_available, $date );
		$ticket_types = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
		$count        = 0;
		if ( sizeof( $ticket_types ) > 0 ) { ?>
            <div class="mpwem_ticket_type">
                <div class="card-body">
					<?php foreach ( $ticket_types as $ticket_type ) {
						$option_ticket_enable = array_key_exists( 'option_ticket_enable', $ticket_type ) ? $ticket_type['option_ticket_enable'] : 'yes';
						if ( $option_ticket_enable == 'yes' ) {
							$input_data        = [];
							$ticket_permission = apply_filters( 'mpwem_ticket_permission', true, $ticket_type );
							do_action( 'mep_ticket_type_loop_list_row_start', $event_id, $date, $ticket_type );
							if ( $ticket_permission ) {
								//echo '<pre>';print_r($ticket_type);echo '</pre>';
								$ticket_name       = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
								$ticket_details    = array_key_exists( 'option_details_t', $ticket_type ) ? $ticket_type['option_details_t'] : '';
								$ticket_price      = array_key_exists( 'option_price_t', $ticket_type ) ? $ticket_type['option_price_t'] : 0;
								$ticket_price_     = apply_filters( 'mep_ticket_type_price', $ticket_price, $ticket_name, $event_id, $ticket_type );
								$ticket_price_     = apply_filters( 'mpwem_group_ticket_price', $ticket_price_, $event_id, $ticket_name );
								$ticket_price_wc   = wc_price( $ticket_price_ );
								$ticket_price      = MPWEM_Global_Function::price_convert_raw( $ticket_price_wc );
								$ticket_qty        = array_key_exists( 'option_qty_t', $ticket_type ) ? $ticket_type['option_qty_t'] : 0;
								$ticket_qty        = apply_filters( 'filter_mpwem_gq_ticket', $ticket_qty, $total_available, $event_id );
								$ticket_d_qty      = array_key_exists( 'option_default_qty_t', $ticket_type ) ? $ticket_type['option_default_qty_t'] : 0;
								$ticket_min_qty    = apply_filters( 'filter_mpwem_min_ticket', 0, $event_id, $ticket_type );
								$ticket_max_qty    = apply_filters( 'filter_mpwem_max_ticket', '', $event_id, $ticket_type );
								$ticket_input_type = array_key_exists( 'option_qty_t_type', $ticket_type ) ? $ticket_type['option_qty_t_type'] : 'inputbox';
								$available         = MPWEM_Functions::get_available_ticket( $event_id, $ticket_name, $date, $ticket_type );
								$available         = apply_filters( 'filter_mpwem_gq_ticket', $available, $total_available, $event_id );
								$available         = apply_filters( 'mpwem_group_ticket_qty', $available, $event_id, $ticket_name );
								$available         = max( 0, floor( $available ) );
								if ( $ticket_name ) {
									$input_data['name']      = 'option_qty[]';
									$input_data['price']     = $ticket_price;
									$input_data['available'] = $available;
									$input_data['d_qty']     = $ticket_d_qty;
									$input_data['min_qty']   = $ticket_min_qty;
									$input_data['max_qty']   = $ticket_max_qty;
									$input_data['type']      = $ticket_input_type;
									$input_data              = apply_filters( 'filter_mpwem_min_qty_must', $input_data, $event_id );
									//echo '<pre>';print_r($input_data);echo '</pre>';
									$count ++;
                                    $early_msg='yes';
									$early_date = apply_filters( 'mpwem_early_date', true, $ticket_type, $event_id );
									$mep_hide_expire_ticket=mep_get_option( 'mep_hide_expire_ticket', 'general_setting_sec', 'no' );
									if ( $early_date ) {
										$sale_end_datetime = array_key_exists( 'option_sale_end_date_t', $ticket_type ) && ! empty( $ticket_type['option_sale_end_date_t'] ) ? date( 'Y-m-d H:i', strtotime( $ticket_type['option_sale_end_date_t'] ) ) : '';
										if ( $sale_end_datetime ) {
											$current_time = current_time( 'Y-m-d H:i' );
											if ( strtotime( $current_time ) > strtotime( $sale_end_datetime ) && $mep_hide_expire_ticket=='no' ) {
												$early_msg='no';
                                            }
                                        }
                                    }
                                    if($early_msg == 'yes'){
									?>
                                    <div class="mep_ticket_item">
                                        <div class="ticket-data">
                                            <div class="ticket-info">
                                                <div class="ticket-name"><?php echo esc_html( $ticket_name ); ?></div>
												<?php if ( $ticket_details ) { ?>
                                                    <div class="ticket-description"><?php echo esc_html( $ticket_details ); ?></div>
												<?php } ?>

												<?php
													// Check if low stock warning should be shown
													$show_low_stock_warning = false;
													if ( function_exists( 'mep_is_low_stock' ) ) {
														$show_low_stock_warning = mep_is_low_stock( $event_id, $ticket_name, $available );
													}
													// Only show "Tickets remaining" if low stock warning is not shown
													if ( $mep_available_seat == 'on' && ! $show_low_stock_warning ) { ?>
                                                        <div class="ticket-remaining xtra-item-left <?php echo $available <= 10 ? 'remaining-low' : 'remaining-high'; ?>">
															<?php echo esc_html( max( $available, 0 ) ) . __( ' Tickets remaining', 'mage-eventpress' ); ?>
                                                        </div>
													<?php } ?>

												<?php
													// Display low stock warning
													if ( function_exists( 'mep_display_low_stock_warning' ) ) {
														mep_display_low_stock_warning( $event_id, $ticket_name, $available );
													}
												?>
                                            </div>
                                            <div class="quantity-control">
                                                <input type="hidden" name='option_name[]' value='<?php echo esc_attr( $ticket_name ); ?>'/>
                                                <input type="hidden" name='ticket_type[]' value='<?php echo esc_attr( $ticket_name ); ?>'/>
												<?php do_action( 'mpwem_hidden_item_ticket', $ticket_name, $event_id ); ?>
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
                                                                <span class='early-bird-future-date-txt' style="font-size: 12px;"><?php _e( 'Sale close On: ', 'mage-eventpress' );
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
                                                        <span class='early-bird-future-date-txt' style="font-size: 12px;"><?php _e( 'Available On: ', 'mage-eventpress' );
																echo get_mep_datetime( $sale_start_datetime, 'date-time-text' ); ?></span>
                                                        <input type="hidden" name="option_qty[]" value="0" data-price="<?php echo esc_attr( $ticket_price ); ?>"/>
														<?php
													}
												?>
                                            </div>
                                            <div class="ticket-price">
												<?php
													// Display limited availability ribbon above price
													if ( function_exists( 'mep_display_limited_availability_ribbon' ) ) {
														mep_display_limited_availability_ribbon( $event_id, $ticket_name, $available );
													}
												?>
												<?php echo wc_price( $ticket_price_ ); ?>
												<?php //echo wc_price( $ticket_price ); ?>
                                            </div>
                                        </div>
										<?php do_action( 'mpwem_multi_attendee', $event_id ); ?>
                                    </div>
									<?php
                                    }
								}
							}
						}
					}
					?>
                </div>
            </div>
			<?php
		}
	} else {
		MPWEM_Layout::msg( __( 'Sorry, no ticket available', 'mage-eventpress' ), '_layout_default_bg_warning' );
		do_action( 'mep_after_no_seat_notice', $event_id );
	}
// Show waitlist form after ticket types if waitlist is enabled (even when tickets are available)
	do_action( 'mep_after_ticket_types', $event_id );
//	echo '<pre>';print_r($all_dates);echo '</pre>';
