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
			public static function details_template_path( $file_name ): string {
				$template_path       = get_stylesheet_directory() . '/mage-event/themes/';
				$default_dir         = MPWEM_PLUGIN_DIR . '/templates/themes/';
				$default_path        = $default_dir . $file_name;
				$theme_template_path = $template_path . $file_name;
				if ( file_exists( $theme_template_path ) ) {
					return $theme_template_path;
				} elseif ( file_exists( $default_path ) ) {
					return $default_path;
				} else {
					return $default_dir . 'default-theme.php';
				}
			}
			public static function template_path( $file_name ): string {
				$template_path       = get_stylesheet_directory() . '/mage-event/';
				$default_dir         = MPWEM_PLUGIN_DIR . '/templates/';
				$theme_template_path = $template_path . $file_name;
				if ( file_exists( $theme_template_path ) ) {
					return $theme_template_path;
				}
				return $default_dir . $file_name;
			}
			public static function get_details_template_name($post_id) {
				$global_template   = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_global_single_template', 'default-theme.php' );
				$current_template  = MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_template' );
				return $current_template ?: $global_template;
			}
			//==========================//
			public static function get_all_info( $event_id ) {
				$event_infos = [];
				$event_meta  = get_post_custom( $event_id );
				if ( $event_meta ) {
					$url_date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : null;
					$url_date=$url_date ? date( 'Y-m-d H:i', $url_date ) : '';
					$date_format = MPWEM_Global_Function::check_time_exit_date( $url_date ) ? 'Y-m-d H:i' : 'Y-m-d';
					$url_date    = $url_date ? date( $date_format, strtotime($url_date) ) : '';
					$all_dates   = MPWEM_Functions::get_dates( $event_id );
					$all_times   = MPWEM_Functions::get_times( $event_id, $all_dates, $url_date );
					$upcoming_date                           = $url_date ?: MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
					$single_event_setting_sec=MPWEM_Global_Function::data_sanitize( get_option( 'single_event_setting_sec' ) );
					$icon_setting_sec=MPWEM_Global_Function::data_sanitize( get_option( 'icon_setting_sec' ) );
					$general_setting_sec=MPWEM_Global_Function::data_sanitize( get_option( 'general_setting_sec' ) );
					$event_infos['event_id']                 = $event_id;
					$event_infos['all_date']                 = $all_dates;
					$event_infos['all_time']                 = $all_times;
					$event_infos['upcoming_date']            = $upcoming_date;
					$location                                = self::get_location( $event_id );
					$event_infos['full_address']             = $location;
					$event_infos['mep_city']                 = is_array( $location ) && array_key_exists( 'city', $location ) ? $location['city'] : '';
					$event_infos['mep_state']                = is_array( $location ) && array_key_exists( 'state', $location ) ? $location['state'] : '';
					$event_infos['mep_country']              = is_array( $location ) && array_key_exists( 'country', $location ) ? $location['country'] : '';
					$event_infos['mep_postcode']             = is_array( $location ) && array_key_exists( 'zip', $location ) ? $location['zip'] : '';
					$event_infos['mep_street']               = is_array( $location ) && array_key_exists( 'street', $location ) ? $location['street'] : '';
					$event_infos['mep_location_venue']       = is_array( $location ) && array_key_exists( 'location', $location ) ? $location['location'] : '';
					$event_infos['single_event_setting_sec'] = $single_event_setting_sec ?: [];
					$event_infos['icon_setting_sec']         = $icon_setting_sec ?: [];
					$event_infos['general_setting_sec']      = $general_setting_sec ?: [];
					$event_meta                              = MPWEM_Global_Function::data_sanitize( $event_meta );
					foreach ( $event_meta as $key => $value ) {
						$val = current( $value );
						if ( ! empty( $val ) || ! isset( $event_infos[ $key ] ) ) {
							$event_infos[ $key ] = $val;
						}
					}
				}
				return $event_infos;
			}
			//==========================//
			public static function get_total_available_seat( $event_id, $date = '' ) {
				$total_sold    = self::get_total_sold( $event_id, $date );
				$total_ticket  = self::get_total_ticket( $event_id, $date );
				$total_reserve = self::get_reserve_ticket( $event_id, $date );
				return $total_ticket - ( $total_sold + $total_reserve );
			}
			public static function get_total_sold( $event_id, $event_date = '' ) {
				$filter_args['post_id']    = $event_id;
				$filter_args['event_date'] = $event_date;
				return MPWEM_Query::attendee_query( $filter_args )->post_count;
			}
			public static function get_total_ticket( $event_id, $date ) {
				$total_ticket = 0;
				$ticket_types = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$total_ticket += array_key_exists( 'option_qty_t', $ticket_type ) ? (int) $ticket_type['option_qty_t'] : 0;
					}
				}
				return max( apply_filters( 'mpwem_event_total_seat_counts', $total_ticket, $event_id, $date ), 0 );
			}
			public static function get_reserve_ticket( $event_id, $date ) {
				$reserve_ticket = 0;
				$ticket_types   = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$reserve_ticket += array_key_exists( 'option_rsv_t', $ticket_type ) ? (int) $ticket_type['option_rsv_t'] : 0;
					}
				}
				return max( apply_filters( 'mpwem_event_total_resv_seat_count', $reserve_ticket, $event_id, $date ), 0 );
			}
			public static function get_available_ticket( $event_id, $ticket_name, $date, $ticket_type = [] ) {
				$ticket_name_ = html_entity_decode( urldecode( $ticket_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				$available_ticket = 0;
				if ( sizeof( $ticket_type ) == 0 ) {
					$ticket_types = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
					if ( sizeof( $ticket_types ) > 0 ) {
						foreach ( $ticket_types as $type ) {
							$name = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
							$name = html_entity_decode( urldecode( $name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
							if ( $name == $ticket_name_ ) {
								$ticket_type = $type;
							}
						}
					}
				}
				if ( sizeof( $ticket_type ) > 0 ) {
					$filter_args['post_id']    = $event_id;
					$filter_args['event_date'] = $date;
					$filter_args['ea_ticket_type'] = $ticket_name;
					$ticket_qty       = array_key_exists( 'option_qty_t', $ticket_type ) ? $ticket_type['option_qty_t'] : 0;
					$ticket_r_qty     = array_key_exists( 'option_rsv_t', $ticket_type ) ? $ticket_type['option_rsv_t'] : 0;
					$total_sold       = MPWEM_Query::attendee_query( $filter_args )->post_count;
					$available_ticket = (int) $ticket_qty - ( $total_sold + (int) $ticket_r_qty );
				}
				return $available_ticket;
			}
			public static function get_total_available_ex( $event_id, $date = '' ) {
				$total_sold     = 0;
				$total_ticket   = 0;
				$reserve_ticket = 0;
				$ticket_types   = MPWEM_Global_Function::get_post_info( $event_id, 'mep_events_extra_prices', [] );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$name           = array_key_exists( 'option_name', $ticket_type ) ? $ticket_type['option_name'] : '';
						$total_sold     += (int) mep_extra_service_sold( $event_id, $name, $date );
						$total_ticket   += array_key_exists( 'option_qty', $ticket_type ) ? (int) $ticket_type['option_qty'] : 0;
						$reserve_ticket += array_key_exists( 'option_rsv', $ticket_type ) ? (int) $ticket_type['option_rsv'] : 0;
					}
				}
				$total_ticket  = max( apply_filters( 'mpwem_event_total_ex_counts', $total_ticket, $event_id, $date ), 0 );
				$total_reserve = max( apply_filters( 'mpwem_event_total_resv_ex_count', $reserve_ticket, $event_id, $date ), 0 );
				return $total_ticket - ( $total_sold + $total_reserve );
			}
			public static function get_available_ex_service( $event_id, $ticket_name, $date, $ticket_type = [] ) {
				$available_ticket = 0;
				if ( sizeof( $ticket_type ) == 0 ) {
					$ticket_types = MPWEM_Global_Function::get_post_info( $event_id, 'mep_events_extra_prices', [] );
					if ( sizeof( $ticket_types ) > 0 ) {
						foreach ( $ticket_types as $type ) {
							$name = array_key_exists( 'option_name', $type ) ? $type['option_name'] : '';
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
			public static function get_ticket_price_by_name( $ticket_name, $post_id, $ticket_types = [] ) {
				$ticket_types = sizeof( $ticket_types ) > 0 ? $ticket_types : MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_ticket_type', [] );
				$price        = 0;
				$ticket_name = html_entity_decode( urldecode( $ticket_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$ticket_price = array_key_exists( 'option_price_t', $ticket_type ) ? $ticket_type['option_price_t'] : 0;
						$name         = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
						$name = html_entity_decode( urldecode( $name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
						if ( $ticket_name == $name ) {
							$price = apply_filters( 'mep_ticket_type_price', $ticket_price, $ticket_name, $post_id, $ticket_type );
							break; // Found match, exit loop
						}
					}
				}
				return MPWEM_Global_Function::get_wc_raw_price( $price );
			}
			public static function get_ex_price_by_name( $ticket_name, $post_id, $ticket_types = [] ) {
				$ticket_types = sizeof( $ticket_types ) > 0 ? $ticket_types : MPWEM_Global_Function::get_post_info( $post_id, 'mep_events_extra_prices', [] );
				$price        = 0;
				$ticket_name  = explode( '_', $ticket_name )[0];
				$ticket_name  = str_replace( "'", "", $ticket_name );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$name = array_key_exists( 'option_name', $ticket_type ) ? $ticket_type['option_name'] : '';
						$name = str_replace( "'", "", $name );
						if ( $ticket_name == $name ) {
							$price = array_key_exists( 'option_price', $ticket_type ) ? $ticket_type['option_price'] : 0;
						}
					}
				}
				return MPWEM_Global_Function::get_wc_raw_price( $price );
			}
			public static function get_min_price( $post_id ) {
				$price        = 0;
				$ticket_types = MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_ticket_type', [] );
				if ( sizeof( $ticket_types ) > 0 ) {
					foreach ( $ticket_types as $ticket_type ) {
						$ticket_price = array_key_exists( 'option_price_t', $ticket_type ) ? $ticket_type['option_price_t'] : 0;
						$ticket_name  = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
						$ticket_price = apply_filters( 'mep_ticket_type_price', $ticket_price, $ticket_name, $post_id, $ticket_type );
						$price        = $price > 0 ? min( $price, $ticket_price ) : $ticket_price;
					}
				}
				return $price;
			}
			//==========================//
			public static function get_upcoming_date_time( $event_id, $all_dates = [], $all_times = [] ) {
				$up_coming_date='';
				$all_dates = sizeof( $all_dates ) > 0 ? $all_dates : self::get_dates( $event_id );
				if ( sizeof( $all_dates ) > 0 ) {
					$all_times = $all_times && sizeof( $all_times ) ? $all_times : MPWEM_Functions::get_times( $event_id, $all_dates );
					$date_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
					if ( $date_type == 'no' || $date_type == 'yes' ) {
						$date = date( 'Y-m-d', strtotime( current( $all_dates )['time'] ) );
					} else {
						$date = date( 'Y-m-d', strtotime( current( $all_dates ) ) );
					}
					$start_time = '';
					if ( sizeof( $all_times ) > 0 ) {
						$all_times  = current( $all_times );
						$start_time = array_key_exists( 'start', $all_times ) ? $all_times['start']['time'] : '';
					}
					$date_time = $date . ' ' . $start_time;
					$up_coming_date= MPWEM_Global_Function::check_time_exit_date( $date_time ) ? date( 'Y-m-d H:i', strtotime( $date_time ) ) : date( 'Y-m-d', strtotime( $date_time ) );
				}
				update_post_meta( $event_id, 'event_upcoming_datetime', $up_coming_date );
				return $up_coming_date;
			}
			public static function get_all_dates( $event_id ) {
				$all_dates = [];
				$date_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				if ( $date_type == 'no' || $date_type == 'yes' ) {
					$start_date      = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_date' );
					$start_time      = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_time' );
					$start_date_time = $start_time ? $start_date . ' ' . $start_time : $start_date;
					$end_date        = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_date' );
					$end_time        = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_time' );
					$end_date_time   = $end_time ? $end_date . ' ' . $end_time : $end_date;
					$count           = 0;
					if ( $start_date_time && $end_date_time ) {
						$all_dates[ $count ]['time'] = $start_date_time;
						$all_dates[ $count ]['end']  = $end_date_time;
					}
					if($date_type=='yes') {
						// Process additional dates for both 'yes' (recurring) and 'no' (single event with multiple dates)
						$more_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_more_date', [] );
						if ( sizeof( $more_dates ) > 0 ) {
							foreach ( $more_dates as $more_date ) {
								$more_start_date      = array_key_exists( 'event_more_start_date', $more_date ) ? $more_date['event_more_start_date'] : '';
								$more_start_time      = array_key_exists( 'event_more_start_time', $more_date ) ? $more_date['event_more_start_time'] : '';
								$more_start_date_time = $more_start_time ? $more_start_date . ' ' . $more_start_time : $more_start_date;
								$more_end_date        = array_key_exists( 'event_more_end_date', $more_date ) ? $more_date['event_more_end_date'] : '';
								$more_end_time        = array_key_exists( 'event_more_end_time', $more_date ) ? $more_date['event_more_end_time'] : '';
								$more_end_date_time   = $more_end_time ? $more_end_date . ' ' . $more_end_time : $more_end_date;
								if ( $more_start_date_time && $more_end_date_time && strtotime( $more_start_date_time ) < strtotime( $more_end_date_time ) ) {
									$count ++;
									$all_dates[ $count ]['time'] = $more_start_date_time;
									$all_dates[ $count ]['end']  = $more_end_date_time;
								}
							}
						}
					}
					if ( sizeof( $all_dates ) >1) {
						usort( $all_dates, "MPWEM_Global_Function::sort_date_array" );
					}
				} else {
					$start_date = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_date' );
					$end_date   = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_date' );
					if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
						$end_date = '';
					}
					$repeated_after = MPWEM_Global_Function::get_post_info( $event_id, 'mep_repeated_periods', 1 );
					if ( $start_date && $end_date ) {
						$dates         = MPWEM_Global_Function::date_separate_period( $start_date, $end_date, $repeated_after );
						$all_off_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_ticket_off_dates', [] );
						$off_dates     = [];
						foreach ( $all_off_dates as $off_date ) {
							$off_dates[] = date( 'Y-m-d', strtotime( current( $off_date ) ) );
						}
						$all_off_days = MPWEM_Global_Function::get_post_info( $event_id, 'mep_ticket_offdays', [] );
						foreach ( $dates as $date ) {
							$date = $date->format( 'Y-m-d' );
							$day  = strtolower( date( 'D', strtotime( $date ) ) );
							if ( ! in_array( $date, $off_dates ) && ! in_array( $day, $all_off_days ) ) {
								$all_dates[] = $date;
							}
						}
					}
					$special_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
					if ( sizeof( $special_dates ) > 0 ) {
						foreach ( $special_dates as $special_date ) {
							$start_date = array_key_exists( 'start_date', $special_date ) ? $special_date['start_date'] : '';
							if ( $start_date ) {
								$all_dates[] = $start_date;
							}
						}
					}
					usort( $all_dates, "MPWEM_Global_Function::sort_date" );
					$all_date  = array_unique( $all_dates );
					$all_dates = [];
					foreach ( $all_date as $date ) {
						if ( $date ) {
							$all_dates[] = $date;
						}
					}
				}
				return $all_dates;
			}
			public static function get_all_times( $event_id, $date ) {
				$date_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				$times     = [];
				if ( $date_type == 'everyday' && $date ) {
					$count         = 0;
					$special_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
					if ( sizeof( $special_dates ) > 0 ) {
						foreach ( $special_dates as $special_date ) {
							$start_date = array_key_exists( 'start_date', $special_date ) ? $special_date['start_date'] : '';
							if ( strtotime( $start_date ) == strtotime( $date ) ) {
								$end_date = array_key_exists( 'end_date', $special_date ) ? $special_date['end_date'] : '';
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
					}
					$disable_time = MPWEM_Global_Function::get_post_info( $event_id, 'mep_disable_ticket_time', 'no' );
					if ( sizeof( $times ) == 0 ) {
						if ( $disable_time == 'yes' ) {
							$global_times = MPWEM_Global_Function::get_post_info( $event_id, 'mep_ticket_times_global', [] );
							$day_key      = strtolower( date( 'D', strtotime( $date ) ) );
							$day_times    = MPWEM_Global_Function::get_post_info( $event_id, 'mep_ticket_times_' . $day_key, [] );
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
						$start_time = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_time' );
						$end_time   = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_time' );
						if ( $start_time ) {
							$times[0]['start']['label'] = '';
							$times[0]['start']['time']  = date( 'H:i', strtotime( $start_time ) );
						}
						if ( $end_time ) {
							$times[0]['end']['label'] = '';
							$times[0]['end']['time']  = date( 'H:i', strtotime( $end_time ) );
						}
					}
				}
				return $times;
			}
			public static function get_dates( $event_id ) {
				$all_dates   = [];
				$date_type   = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				$buffer_time = MPWEM_Global_Function::get_post_info( $event_id, 'mep_buffer_time', 0 ) * 60;
				$now         = strtotime( current_time( 'Y-m-d H:i:s' ) );
				$expire_on   = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_expire_on_datetimes', 'event_start_datetime' );
				if ( $date_type == 'no' || $date_type == 'yes' ) {
					$start_date      = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_date' );
					$start_time      = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_time' );
					$start_date_time = $start_time ? $start_date . ' ' . $start_time : $start_date;
					$end_date        = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_date' );
					$end_time        = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_time' );
					$end_date_time   = $end_time ? $end_date . ' ' . $end_time : $end_date;
					$count           = 0;
					$expire_check    = $expire_on == 'event_start_datetime' ? $start_date_time : $end_date_time;
					$expire_check    = date( 'Y-m-d H:i', strtotime( $expire_check ) - $buffer_time );
					if($date_type=='no'){
						if($expire_on =='event_start_datetime'){
							if ( $start_date_time && $end_date_time && strtotime( $expire_check ) > $now ) {
								$all_dates[ $count ]['time'] = $start_date_time;
								$all_dates[ $count ]['end']  = $end_date_time;
							}
						}else{
							$more_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_more_date', [] );
							if(sizeof($more_dates) > 0){
								$last_date=end( $more_dates );
								$more_start_date      = array_key_exists( 'event_more_start_date', $last_date ) ? $last_date['event_more_start_date'] : '';
								$more_start_time      = array_key_exists( 'event_more_start_time', $last_date ) ? $last_date['event_more_start_time'] : '';
								$more_start_date_time = $more_start_time ? $more_start_date . ' ' . $more_start_time : $more_start_date;
								$more_end_date        = array_key_exists( 'event_more_end_date', $last_date ) ? $last_date['event_more_end_date'] : '';
								$more_end_time        = array_key_exists( 'event_more_end_time', $last_date ) ? $last_date['event_more_end_time'] : '';
								$more_end_date_time   = $more_end_time ? $more_end_date . ' ' . $more_end_time : $more_end_date;
								if ( $start_date_time && $more_end_date_time && strtotime( $more_end_date_time ) > $now ) {
									$all_dates[ $count ]['time'] = $start_date_time;
									$all_dates[ $count ]['end']  = $more_end_date_time;
								}
							}else{
								if ( $start_date_time && $end_date_time && strtotime( $expire_check ) > $now ) {
									$all_dates[ $count ]['time'] = $start_date_time;
									$all_dates[ $count ]['end']  = $end_date_time;
								}
							}
						}
					}else {
						if ( $start_date_time && $end_date_time && strtotime( $expire_check ) > $now ) {
							$all_dates[ $count ]['time'] = $start_date_time;
							$all_dates[ $count ]['end']  = $end_date_time;
						}
					}
					if($date_type=='yes') {
						$more_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_more_date', [] );
						if ( sizeof( $more_dates ) > 0 ) {
							foreach ( $more_dates as $more_date ) {
								$more_start_date      = array_key_exists( 'event_more_start_date', $more_date ) ? $more_date['event_more_start_date'] : '';
								$more_start_time      = array_key_exists( 'event_more_start_time', $more_date ) ? $more_date['event_more_start_time'] : '';
								$more_start_date_time = $more_start_time ? $more_start_date . ' ' . $more_start_time : $more_start_date;
								$more_end_date        = array_key_exists( 'event_more_end_date', $more_date ) ? $more_date['event_more_end_date'] : '';
								$more_end_time        = array_key_exists( 'event_more_end_time', $more_date ) ? $more_date['event_more_end_time'] : '';
								$more_end_date_time   = $more_end_time ? $more_end_date . ' ' . $more_end_time : $more_end_date;
								$expire_check         = $expire_on == 'event_start_datetime' ? $more_start_date_time : $more_end_date_time;
								$expire_check         = date( 'Y-m-d H:i', strtotime( $expire_check ) - $buffer_time );
								if ( $more_start_date_time && $more_end_date_time && strtotime( $expire_check ) > $now && strtotime( $more_start_date_time ) < strtotime( $more_end_date_time ) ) {
									$count ++;
									$all_dates[ $count ]['time'] = $more_start_date_time;
									$all_dates[ $count ]['end']  = $more_end_date_time;
								}
							}
						}
						if ( sizeof( $all_dates ) >1 ) {
							usort( $all_dates, "MPWEM_Global_Function::sort_date_array" );
						}
					}
				} else {
					$start_date = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_date' );
					$end_date   = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_date' );
					if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
						$end_date = '';
					}
					$repeated_after = MPWEM_Global_Function::get_post_info( $event_id, 'mep_repeated_periods', 1 );
					if ( $start_date && $end_date ) {
						$dates         = MPWEM_Global_Function::date_separate_period( $start_date, $end_date, $repeated_after );
						$all_off_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_ticket_off_dates', [] );
						$off_dates     = [];
						foreach ( $all_off_dates as $off_date ) {
							$off_dates[] = date( 'Y-m-d', strtotime( current( $off_date ) ) );
						}
						$all_off_days = MPWEM_Global_Function::get_post_info( $event_id, 'mep_ticket_offdays', [] );
						foreach ( $dates as $date ) {
							$date = $date->format( 'Y-m-d' );
							$day  = strtolower( date( 'D', strtotime( $date ) ) );
							if ( ! in_array( $date, $off_dates ) && ! in_array( $day, $all_off_days ) ) {
								$all_dates[] = $date;
							}
						}
					}
					$special_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
					if ( sizeof( $special_dates ) > 0 ) {
						foreach ( $special_dates as $special_date ) {
							$start_date = array_key_exists( 'start_date', $special_date ) ? $special_date['start_date'] : '';
							if ( $start_date && strtotime( $now ) <= strtotime( $start_date ) ) {
								$all_dates[] = $start_date;
							}
						}
					}
					usort( $all_dates, "MPWEM_Global_Function::sort_date" );
					$all_date  = array_unique( $all_dates );
					$all_dates = [];
					$now       = strtotime( current_time( 'Y-m-d H:i:s' ) );
					foreach ( $all_date as $date ) {
						$all_times = MPWEM_Functions::get_times( $event_id, $all_date, $date );
						if ( sizeof( $all_times ) > 0 ) {
							foreach ( $all_times as $time ) {
								$time_value   = is_array( $time ) && array_key_exists( 'start', $time ) ? $time['start'] : '';
								$time_value   = is_array( $time_value ) && array_key_exists( 'time', $time_value ) ? $time_value['time'] : '';
								$main_date    = $date . ' ' . $time_value;
								$expire_check = date( 'Y-m-d H:i', strtotime( $main_date ) - $buffer_time );
								if ( strtotime( $expire_check ) > $now ) {
									$all_dates[] = $date;
								}
							}
						} else {
							$expire_check = date( 'Y-m-d H:i', strtotime( $date ) - $buffer_time );
							if ( strtotime( $expire_check ) > $now ) {
								$all_dates[] = $date;
							}
						}
					}
					$all_dates  = array_unique( $all_dates );
				}
				return $all_dates;
			}
			public static function get_times( $event_id, $all_dates = [], $date = '' ) {
				$all_dates = sizeof( $all_dates ) > 0 ? $all_dates : self::get_dates( $event_id );
				$date_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				$times     = [];
				if ( sizeof( $all_dates ) > 0 ) {
					if ( $date_type == 'no' || $date_type == 'yes' ) {
						$date = $date ?date( 'Y-m-d', strtotime( $date ) ): date( 'Y-m-d', strtotime( current( $all_dates )['time'] ) );
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
						$count       = 0;
						$date        = $date ?date( 'Y-m-d', strtotime( $date ) ): date( 'Y-m-d', strtotime( current( $all_dates ) ) );
						$buffer_time = MPWEM_Global_Function::get_post_info( $event_id, 'mep_buffer_time', 0 ) * 60;
						$now         = strtotime( current_time( 'Y-m-d H:i:s' ) );
						if ( in_array( $date, $all_dates ) ) {
							$special_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
							if ( sizeof( $special_dates ) > 0 ) {
								foreach ( $special_dates as $special_date ) {
									$start_date = array_key_exists( 'start_date', $special_date ) ? $special_date['start_date'] : '';
									$end_date   = array_key_exists( 'end_date', $special_date ) ? $special_date['end_date'] : '';
									if ( $start_date && $end_date && strtotime( $date ) >= strtotime( $start_date ) && strtotime( $date ) <= strtotime( $end_date ) ) {
										$start_times = array_key_exists( 'time', $special_date ) ? $special_date['time'] : [];
										if ( sizeof( $start_times ) > 0 ) {
											foreach ( $start_times as $start_time ) {
												$time = array_key_exists( 'mep_ticket_time', $start_time ) ? $start_time['mep_ticket_time'] : '';;
												$full_date    = $date . ' ' . $time;
												$expire_check = date( 'Y-m-d H:i', strtotime( $full_date ) - $buffer_time );
												if ( strtotime( $expire_check ) > $now ) {
													$times[ $count ]['start']['label'] = array_key_exists( 'mep_ticket_time_name', $start_time ) ? $start_time['mep_ticket_time_name'] : '';
													$times[ $count ]['start']['time']  = $time;
													$count ++;
												}
											}
										}
									}
								}
							}
							$disable_time = MPWEM_Global_Function::get_post_info( $event_id, 'mep_disable_ticket_time', 'no' );
							if ( sizeof( $times ) == 0 ) {
								if ( $disable_time == 'yes' ) {
									$global_times = MPWEM_Global_Function::get_post_info( $event_id, 'mep_ticket_times_global', [] );
									$day_key      = strtolower( date( 'D', strtotime( $date ) ) );
									$day_times    = MPWEM_Global_Function::get_post_info( $event_id, 'mep_ticket_times_' . $day_key, [] );
									$time_lists   = sizeof( $day_times ) > 0 ? $day_times : $global_times;
									if ( sizeof( $time_lists ) > 0 ) {
										foreach ( $time_lists as $time_list ) {
											$time = array_key_exists( 'mep_ticket_time', $time_list ) ? $time_list['mep_ticket_time'] : '';
											$full_date    = $date . ' ' . $time;
											$expire_check = date( 'Y-m-d H:i', strtotime( $full_date ) - $buffer_time );
											if ( strtotime( $expire_check ) > $now ) {
												$times[ $count ]['start']['label'] = array_key_exists( 'mep_ticket_time_name', $time_list ) ? $time_list['mep_ticket_time_name'] : '';
												$times[ $count ]['start']['time']  = $time;
												$count ++;
											}
										}
									}
								}
							}
							if ( sizeof( $times ) == 0 ) {
								$start_time   = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_time' );
								$end_time     = MPWEM_Global_Function::get_post_info( $event_id, 'event_end_time' );
								$full_date    = $date . ' ' . $start_time;
								$expire_check = date( 'Y-m-d H:i', strtotime( $full_date ) - $buffer_time );
								if ( strtotime( $expire_check ) > $now ) {
									if ( $start_time ) {
										$times[0]['start']['label'] = '';
										$times[0]['start']['time']  = date( 'H:i', strtotime( $start_time ) );
									}
									if ( $end_time ) {
										$times[0]['end']['label'] ='';
										$times[0]['end']['time']  = date( 'H:i', strtotime( $end_time ) );
									}
								}
							}
						}
					}
				}
				return $times;
			}
			//==========================//
			public static function get_location( $event_id, $key = '' ) {
				$address_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_org_address' );
				$address      = [];
				if ( $address_type ) {
					$org_arr  = get_the_terms( $event_id, 'mep_org' );
					$org_id   = $org_arr[0]->term_id;
					$location = get_term_meta( $org_id, 'org_location', true );
					$street   = get_term_meta( $org_id, 'org_street', true );
					$city     = get_term_meta( $org_id, 'org_city', true );
					$state    = get_term_meta( $org_id, 'org_state', true );
					$zip      = get_term_meta( $org_id, 'org_postcode', true );
					$country  = get_term_meta( $org_id, 'org_country', true );
				} else {
					$location = MPWEM_Global_Function::get_post_info( $event_id, 'mep_location_venue' );
					$street   = MPWEM_Global_Function::get_post_info( $event_id, 'mep_street' );
					$city     = MPWEM_Global_Function::get_post_info( $event_id, 'mep_city' );
					$state    = MPWEM_Global_Function::get_post_info( $event_id, 'mep_state' );
					$zip      = MPWEM_Global_Function::get_post_info( $event_id, 'mep_postcode' );
					$country  = MPWEM_Global_Function::get_post_info( $event_id, 'mep_country' );
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