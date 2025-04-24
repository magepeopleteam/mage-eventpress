<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Functions' ) ) {
		class MPWEM_Functions {
			public function __construct() { }

			public static function template_path( $file_name ): string {
				$template_path = get_stylesheet_directory() . '/mage-events/';
				$default_dir   = MPWEM_PLUGIN_DIR . '/templates/';
				$dir           = is_dir( $template_path ) ? $template_path : $default_dir;
				$file_path     = $dir . $file_name;

				return locate_template( array( 'templates/' . $file_name ) ) ? $file_path : $default_dir . $file_name;
			}

			//==========================//
			public static function get_total_ticket( $event_id ) {
				$total_ticket = 0;
				$ticket_types = MP_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$total_ticket += array_key_exists( 'option_qty_t', $ticket_type ) ? (int) $ticket_type['option_qty_t'] : 0;
					}
				}

				return apply_filters( 'mep_event_total_seat_counts', $total_ticket, $event_id );
			}

			public static function get_reserve_ticket( $event_id ) {
				$reserve_ticket = 0;
				$ticket_types   = MP_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$reserve_ticket += array_key_exists( 'option_rsv_t', $ticket_type ) ? (int) $ticket_type['option_rsv_t'] : 0;
					}
				}

				return apply_filters( 'mep_event_total_resv_seat_count', $reserve_ticket, $event_id );
			}

			public static function get_available_ticket( $event_id, $ticket_name, $date, $ticket_type = [] ) {
				$available_ticket = 0;
				if ( sizeof( $ticket_type ) == 0 ) {
					$ticket_types = MP_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
					if ( sizeof( $ticket_types ) > 0 ) {
						foreach ( $ticket_types as $type ) {
							$name = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
							if ( $name == $ticket_name ) {
								$ticket_type = $type;
							}
						}
					}
				}
				if ( sizeof( $ticket_type ) > 0 ) {
					$ticket_qty       = array_key_exists( 'option_qty_t', $ticket_type ) ? $ticket_type['option_qty_t'] : 0;
					$ticket_r_qty     = array_key_exists( 'option_rsv_t', $ticket_type ) ? $ticket_type['option_rsv_t'] : 0;
					$total_sold       = mep_get_ticket_type_seat_count( $event_id, $ticket_name, $date, $ticket_qty, $ticket_r_qty );
					$available_ticket = (int) $ticket_qty - ( (int) $total_sold + (int) $ticket_r_qty );
				}

				return $available_ticket;
			}

			public static function get_available_ex_service( $event_id, $ticket_name, $date, $ticket_type = [] ) {
				$available_ticket = 0;
				if ( sizeof( $ticket_type ) == 0 ) {
					$ticket_types = MP_Global_Function::get_post_info( $event_id, 'mep_events_extra_prices', [] );
					if ( sizeof( $ticket_types ) > 0 ) {
						foreach ( $ticket_types as $type ) {
							$name = array_key_exists( 'option_name', $ticket_type ) ? $ticket_type['option_name'] : '';
							if ( $name == $ticket_name ) {
								$ticket_type = $type;
							}
						}
					}
				}
				if ( sizeof( $ticket_type ) > 0 ) {
					$ticket_qty       = array_key_exists( 'option_qty', $ticket_type ) ? $ticket_type['option_qty'] : 0;
					$total_sold       = (int) mep_extra_service_sold( $event_id, $ticket_name, $date );
					$available_ticket = $ticket_qty - $total_sold;
				}

				return $available_ticket;
			}

			//==========================//
			public static function get_ticket_price( $event_id, $ticket_price, $ticket_name, $ticket_type = [] ) {
				$ticket_price = apply_filters( 'mep_ticket_type_price', $ticket_price, $ticket_name, $event_id, $ticket_type );

				return MP_Global_Function::get_wc_raw_price( $event_id, $ticket_price );
			}

			public static function get_min_price( $post_id ) {
				$price        = 0;
				$ticket_types = MP_Global_Function::get_post_info( $post_id, 'mep_event_ticket_type', [] );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$ticket_price = array_key_exists( 'option_price_t', $ticket_type ) ? $ticket_type['option_price_t'] : 0;
						$ticket_name  = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
						$ticket_price = MPWEM_Functions::get_ticket_price( $post_id, $ticket_price, $ticket_name, $ticket_type );
						$price        = $price > 0 ? min( $price, $ticket_price ) : $ticket_price;
					}
				}

				return $price;
			}

			//==========================//
			public static function get_upcoming_date_time( $event_id, $all_dates = [], $all_times = [] ) {
				$date_time = '';
				$all_dates = sizeof( $all_dates ) > 0 ? $all_dates : self::get_dates( $event_id );
				$all_times = $all_times ?? MPWEM_Functions::get_times( $event_id, $all_dates );
				$date_type = MP_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				if ( sizeof( $all_dates ) > 0 ) {
					if ( $date_type == 'no' || $date_type == 'yes' ) {
						$date       = date( 'Y-m-d', strtotime( current( $all_dates )['time'] ) );
						$start_time = '';
						if ( sizeof( $all_times ) > 0 ) {
							$all_times  = current( $all_times );
							$start_time = array_key_exists( 'start', $all_times ) ? $all_times['start']['time'] : '';
						}
						$date_time = $date . ' ' . $start_time;
					} else {
						$count      = 0;
						$date       = date( 'Y-m-d', strtotime( current( $all_dates ) ) );
						$start_time = '';
						if ( sizeof( $all_times ) > 0 ) {
							$all_times  = current( $all_times );
							$start_time = array_key_exists( 'start', $all_times ) ? $all_times['start']['time'] : '';
						}
						$date_time = $date . ' ' . $start_time;
					}
				}

				return MP_Global_Function::check_time_exit_date( $date_time ) ? date( 'Y-m-d H:i', strtotime( $date_time ) ) : date( 'Y-m-d', strtotime( $date_time ) );
			}

			public static function get_dates( $event_id ) {
				$all_dates = [];
				$date_type = MP_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				$now       = strtotime( current_time( 'Y-m-d H:i:s' ) );
				$expire_on = MP_Global_Function::get_settings( 'general_setting_sec', 'mep_event_expire_on_datetimes', 'event_start_datetime' );
				if ( $date_type == 'no' || $date_type == 'yes' ) {
					$start_date      = MP_Global_Function::get_post_info( $event_id, 'event_start_date' );
					$start_time      = MP_Global_Function::get_post_info( $event_id, 'event_start_time' );
					$start_date_time = $start_time ? $start_date . ' ' . $start_time : $start_date;
					$end_date        = MP_Global_Function::get_post_info( $event_id, 'event_end_date' );
					$end_time        = MP_Global_Function::get_post_info( $event_id, 'event_end_time' );
					$end_date_time   = $end_time ? $end_date . ' ' . $end_time : $end_date;
					$count           = 0;
					$expire_check    = $expire_on == 'event_start_datetime' ? $start_date_time : $end_date_time;
					if ( $start_date_time && $end_date_time && strtotime( $expire_check ) > $now && strtotime( $start_date_time ) < strtotime( $end_date_time ) ) {
						$all_dates[ $count ]['time'] = $start_date_time;
						$all_dates[ $count ]['end']  = $end_date_time;
					}
					if ( $date_type == 'yes' ) {
						$more_dates = MP_Global_Function::get_post_info( $event_id, 'mep_event_more_date', [] );
						if ( sizeof( $more_dates ) > 0 ) {
							foreach ( $more_dates as $more_date ) {
								$more_start_date      = array_key_exists( 'event_more_start_date', $more_date ) ? $more_date['event_more_start_date'] : '';
								$more_start_time      = array_key_exists( 'event_more_start_time', $more_date ) ? $more_date['event_more_start_time'] : '';
								$more_start_date_time = $more_start_time ? $more_start_date . ' ' . $more_start_time : $more_start_date;
								$more_end_date        = array_key_exists( 'event_more_end_date', $more_date ) ? $more_date['event_more_end_date'] : '';
								$more_end_time        = array_key_exists( 'event_more_end_time', $more_date ) ? $more_date['event_more_end_time'] : '';
								$more_end_date_time   = $more_end_time ? $more_end_date . ' ' . $more_end_time : $more_end_date;
								$expire_check         = $expire_on == 'event_start_datetime' ? $more_start_date_time : $more_end_date_time;
								if ( $more_start_date_time && $more_end_date_time && strtotime( $expire_check ) > $now && strtotime( $more_start_date_time ) < strtotime( $more_end_date_time ) ) {
									$count ++;
									$all_dates[ $count ]['time'] = $more_start_date_time;
									$all_dates[ $count ]['end']  = $more_end_date_time;
								}
							}
						}
						if ( sizeof( $all_dates ) ) {
							usort( $all_dates, "MP_Global_Function::sort_date_array" );
						}
					}
				} else {
					$now        = current_time( 'Y-m-d' );
					$start_date = MP_Global_Function::get_post_info( $event_id, 'event_start_date' );
					if ( strtotime( $now ) >= strtotime( $start_date ) ) {
						$start_date = $now;
					}
					$end_date = MP_Global_Function::get_post_info( $event_id, 'event_end_date' );
					if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
						$end_date = '';
					}
					$repeated_after = MP_Global_Function::get_post_info( $event_id, 'mep_repeated_periods', 1 );
					if ( $start_date && $end_date ) {
						$dates         = MP_Global_Function::date_separate_period( $start_date, $end_date, $repeated_after );
						$all_off_dates = MP_Global_Function::get_post_info( $event_id, 'mep_ticket_off_dates', [] );
						$off_dates     = [];
						foreach ( $all_off_dates as $off_date ) {
							$off_dates[] = date( 'Y-m-d', strtotime( current( $off_date ) ) );
						}
						$all_off_days = MP_Global_Function::get_post_info( $event_id, 'mep_ticket_offdays', [] );
						foreach ( $dates as $date ) {
							$date = $date->format( 'Y-m-d' );
							$day  = strtolower( date( 'D', strtotime( $date ) ) );
							if ( ! in_array( $date, $off_dates ) && ! in_array( $day, $all_off_days ) ) {
								$all_dates[] = $date;
							}
						}
					}
					$special_dates = MP_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
					if ( sizeof( $special_dates ) > 0 ) {
						foreach ( $special_dates as $special_date ) {
							$start_date = array_key_exists( 'start_date', $special_date ) ? $special_date['start_date'] : '';
							if ( $start_date && strtotime( $now ) <= strtotime( $start_date ) ) {
								$all_dates[] = $start_date;
							}
						}
					}
					usort( $all_dates, "MP_Global_Function::sort_date" );
					$all_dates = array_unique( $all_dates );
				}

				return $all_dates;
			}

			public static function get_times( $event_id, $all_dates = [], $date = '' ) {
				$all_dates = sizeof( $all_dates ) > 0 ? $all_dates : self::get_dates( $event_id );
				$date_type = MP_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				$times     = [];
				
				if ( sizeof( $all_dates ) > 0 ) {
					if ( $date_type == 'no' || $date_type == 'yes' ) {
						$date = $date ?: date( 'Y-m-d', strtotime( current( $all_dates )['time'] ) );
						foreach ( $all_dates as $dates ) {
							$current_date = date( 'Y-m-d', strtotime( $dates['time'] ) );
							if ( strtotime( $current_date ) == strtotime( $date ) ) {
								$times[0]['start']['label'] = '';
								$times[0]['start']['time']  = date( 'H:i', strtotime( $dates['time'] ) );
								$times[0]['end']['label']   = '';
								$times[0]['end']['time']    = date( 'H:i', strtotime( $dates['end'] ) );
							}
						}
					} else {
						
						$count = 0;
						$date  = $date ?: date( 'Y-m-d', strtotime( current( $all_dates ) ) );
						if ( in_array( $date, $all_dates ) ) {
							echo 123;
							$special_dates = MP_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
							if ( sizeof( $special_dates ) > 0 ) {
								foreach ( $special_dates as $special_date ) {
									$start_date = array_key_exists( 'start_date', $special_date ) ? $special_date['start_date'] : '';
									$end_date   = array_key_exists( 'end_date', $special_date ) ? $special_date['end_date'] : '';
									if ( $start_date && $end_date && strtotime( $date ) >= strtotime( $start_date ) && strtotime( $date ) <= strtotime( $end_date ) ) {
										$start_times = array_key_exists( 'time', $special_date ) ? $special_date['time'] : [];
										if ( sizeof( $start_times ) > 0 ) {
											foreach ( $start_times as $start_time ) {
												$times[ $count ]['start']['label'] = array_key_exists( 'mep_ticket_time_name', $start_time ) ? $start_time['mep_ticket_time_name'] : '';
												$times[ $count ]['start']['time']  = array_key_exists( 'mep_ticket_time', $start_time ) ? $start_time['mep_ticket_time'] : '';
												$count ++;
											}
										}
									}
								}
							}
							echo $disable_time = MP_Global_Function::get_post_info( $event_id, 'mep_disable_ticket_time', 'no' );
							if ( sizeof( $times ) == 0 ) {
								if ( $disable_time == 'yes' ) {
									$global_times = MP_Global_Function::get_post_info( $event_id, 'mep_ticket_times_global', [] );
									$day_key      = strtolower( date( 'D', strtotime( $date ) ) );
									$day_times    = MP_Global_Function::get_post_info( $event_id, 'mep_ticket_times_' . $day_key, [] );
									$time_lists   = sizeof( $day_times ) > 0 ? $day_times : $global_times;
									if ( sizeof( $time_lists ) > 0 ) {
										foreach ( $time_lists as $time_list ) {
											$times[ $count ]['start']['label'] = array_key_exists( 'mep_ticket_time_name', $time_list ) ? $time_list['mep_ticket_time_name'] : '';
											$times[ $count ]['start']['time']  = array_key_exists( 'mep_ticket_time', $time_list ) ? $time_list['mep_ticket_time'] : '';
											$count ++;
										}
									}
								}
							}
							if ( sizeof( $times ) == 0 ) {
								$start_time = MP_Global_Function::get_post_info( $event_id, 'event_start_time' );
								$end_time   = MP_Global_Function::get_post_info( $event_id, 'event_end_time' );
								if ( $start_time ) {
									$times[0]['start']['label'] = date( 'H:i', strtotime( $start_time ) );
									$times[0]['start']['time']  = date( 'H:i', strtotime( $start_time ) );
								}
								if ( $end_time ) {
									$times[0]['end']['label'] = date( 'H:i', strtotime( $end_time ) );
									$times[0]['end']['time']  = date( 'H:i', strtotime( $end_time ) );
								}
							}
						}
					}
				}

				return $times;
			}

			//==========================//
			public static function get_location( $event_id, $key = '' ) {
				$address_type = MP_Global_Function::get_post_info( $event_id, 'mep_org_address' );
				$address      = [];
				if ( $address_type ) {
					$org_arr  = get_the_terms( $event_id, 'mep_org' );
					$org_id   = $org_arr[0]->term_id;
					$location = get_term_meta( $org_id, 'org_location', true ) ? get_term_meta( $org_id, 'org_location', true ) : '';
					$street   = get_term_meta( $org_id, 'org_street', true ) ? get_term_meta( $org_id, 'org_street', true ) : '';
					$city     = get_term_meta( $org_id, 'org_city', true ) ? get_term_meta( $org_id, 'org_city', true ) : '';
					$state    = get_term_meta( $org_id, 'org_state', true ) ? get_term_meta( $org_id, 'org_state', true ) : '';
					$zip      = get_term_meta( $org_id, 'org_postcode', true ) ? get_term_meta( $org_id, 'org_postcode', true ) : '';
					$country  = get_term_meta( $org_id, 'org_country', true ) ? get_term_meta( $org_id, 'org_country', true ) : '';
				} else {
					$location = MP_Global_Function::get_post_info( $event_id, 'mep_location_venue' );
					$street   = MP_Global_Function::get_post_info( $event_id, 'mep_street' );
					$city     = MP_Global_Function::get_post_info( $event_id, 'mep_city' );
					$state    = MP_Global_Function::get_post_info( $event_id, 'mep_state' );
					$zip      = MP_Global_Function::get_post_info( $event_id, 'mep_postcode' );
					$country  = MP_Global_Function::get_post_info( $event_id, 'mep_country' );
				}
				if ( $location ) {
					$address['location'] = $location;
				}
				if ( $street ) {
					$address['street'] = $street;
				}
				if ( $city ) {
					$address['city'] = $city;
				}
				if ( $state ) {
					$address['state'] = $state;
				}
				if ( $zip ) {
					$address['zip'] = $zip;
				}
				if ( $country ) {
					$address['country'] = $country;
				}

				return $key ? ( array_key_exists( $key, $address ) ? $address[ $key ] : '' ) : $address;
			}

			//==========================//
			public static function get_cpt(): string {
				return 'mep_events';
			}
			//==========================//
		}
		new MPWEM_Functions();
	}