<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
// Enquing The recurring scripts for the front-end
	add_action( 'wp_enqueue_scripts', 'mep_re_enqueue_scripts', 90 );
	function mep_re_enqueue_scripts() {
		wp_enqueue_style( 'mep-re-style', plugin_dir_url( __DIR__ ) . 'css/mep_re_style.css', array() );
	}
// Enquing The recurring scripts for the back-end
	add_action( 'admin_enqueue_scripts', 'mep_re_admin_enqueue_scripts', 90 );
	function mep_re_admin_enqueue_scripts() {
		wp_enqueue_style( 'mep-re-admin-style', plugin_dir_url( __DIR__ ) . 'css/mep_re_admin_style.css', array(), time() );
		wp_enqueue_script( 'mp_recurring_admin_script', plugin_dir_url( __DIR__ ) . 'js/admin_recurring.js', array( 'jquery' ), time(), true );
	}
	add_filter( 'mep_event_expire_datetime_val', 'mep_re_modify_event_expire_date', 15, 2 );
	function mep_re_modify_event_expire_date( $expire_date, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$start_date  = date( $expire_date );
			$expire_date = date( "Y-m-d 23:59:59", strtotime( '+1 days', strtotime( $start_date ) ) );
		}

		return $expire_date;
	}
	function mep_re_get_repeted_event_period_date_arr( $start, $end, $interval ) {
		$interval  = $interval ? $interval : 1;
		$_interval = "P" . $interval . "D";
		$period    = new DatePeriod(
			new DateTime( $start ),
			new DateInterval( $_interval ),
			new DateTime( $end )
		);

		return $period;
	}
	function mep_re_date_range( $first, $last, $period, $output_format = 'Y-m-d' ) {
		$step    = ! empty( $period ) ? "+$period day" : '+1 day';
		$dates   = array();
		$current = strtotime( $first );
		$last    = strtotime( $last );
		while ( $current <= $last ) {
			$dates[] = date( $output_format, $current );
			$current = strtotime( $step, $current );
		}

		return $dates;
	}
	function get_mep_re_recurring_date( $event_id, $event_multi_date, $mep_show_upcoming_event, $select_dateLabel = '' ) {
		$select_dateLabel = $select_dateLabel ?: mep_get_option( 'mep_event_rec_select_event_date_text', 'label_setting_sec', __( 'Select Event Date:', 'mage-eventpress' ) );
		ob_start();
		$mep_show_upcoming_event = get_post_meta( $event_id, 'mep_show_upcoming_event', true ) && ! is_admin() ? get_post_meta( $event_id, 'mep_show_upcoming_event', true ) : 'no';
		?>
        <div class="mep_everyday_date_secs">
            <div class="mep-date-time-select-area ">
                <h3 class='mep_re_datelist_label'>
					<?php echo mep_esc_html( $select_dateLabel ); ?>
                </h3>
                <div>
					<?php
						$cn = 1;
						if ( $mep_show_upcoming_event == 'yes' ) {
							foreach ( $event_multi_date as $event_date ) {
								$start_date = date( 'Y-m-d H:i', strtotime( $event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'] ) );
								$end_date   = date( 'Y-m-d H:i', strtotime( $event_date['event_more_end_date'] . ' ' . $event_date['event_more_end_time'] ) );
								if ( strtotime( current_time( 'Y-m-d H:i:s' ) ) < strtotime( date( 'Y-m-d H:i:s', strtotime( $start_date ) ) ) ) {
									if ( $mep_show_upcoming_event == 'yes' ) {
										$cnt = 1;
									} else {
										$cnt = $cn;
									}
									if ( $cn == $cnt ) {
										?>
                                        <input type='hidden' name="recurring_date" id="mep_recurring_date" value="<?php echo esc_attr( $start_date ); ?>"/>
                                        <span class='mep-re-single-date' style='font-size:18px;font-weight: bold;'><?php echo mep_esc_html( get_mep_datetime( $start_date, 'date-time' ) ); ?></span>
										<?php
									}
									$cn ++;
								}
							}
						} else {
							$cn = 1;
							echo mep_esc_html( '<select name="recurring_date" id="mep_recurring_date">' );
							if ( is_admin() ) {
								echo mep_esc_html( '<option value="">All Attendees</option>' );
							}
							foreach ( $event_multi_date as $event_date ) {
								$start_date = date( 'Y-m-d H:i', strtotime( $event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'] ) );
								$end_date   = date( 'Y-m-d H:i', strtotime( $event_date['event_more_end_date'] . ' ' . $event_date['event_more_end_time'] ) );
								if ( is_admin() ) {
									if ( $mep_show_upcoming_event == 'yes' ) {
										$cnt = 1;
									} else {
										$cnt = $cn;
									}
									if ( $cn == $cnt ) {
										?>
                                        <option value="<?php echo mep_esc_html( $start_date ); ?>" <?php if ( isset( $_GET['date'] ) && ! empty( $_GET['date'] ) ) {
											echo strtotime( $start_date ) == sanitize_text_field( $_GET['date'] ) ? 'selected' : "";
										} ?>><?php echo mep_esc_html( get_mep_datetime( $start_date, 'date-time' ) ); ?></option>
										<?php
									}
								} elseif ( strtotime( current_time( 'Y-m-d H:i:s' ) ) < strtotime( date( 'Y-m-d H:i:s', strtotime( $event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'] ) ) ) ) {
									if ( $mep_show_upcoming_event == 'yes' ) {
										$cnt = 1;
									} else {
										$cnt = $cn;
									}
									if ( $cn == $cnt ) {
										?>
                                        <option value="<?php echo esc_attr( $start_date ); ?>" <?php if ( isset( $_GET['date'] ) && ! empty( $_GET['date'] ) ) {
											echo mep_esc_html( strtotime( $start_date ) ) == sanitize_text_field( $_GET['date'] ) ? 'selected' : "";
										} ?>><?php echo mep_esc_html( get_mep_datetime( $start_date, apply_filters( 'mep_recurring_particular_list_date_format', 'date-time' ) ) ); ?></option>
										<?php
									}
								}
								$cn ++;
							}
							echo '</select>';
						}
					?>
                </div>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}
	add_action( 'wp_ajax_mep_re_ajax_load_ticket_type_list', 'mep_re_ajax_load_ticket_type_list' );
	add_action( 'wp_ajax_nopriv_mep_re_ajax_load_ticket_type_list', 'mep_re_ajax_load_ticket_type_list' );
	function mep_re_ajax_load_ticket_type_list() {
		$event_id = isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : 0;
		if ( wp_verify_nonce( $_POST['nonce'], 'mep-ajax-recurring-nonce' ) && $event_id > 0 ) {
			$recurring        = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
			$time_status      = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$start_time       = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_start_time', true ) ) );
			$_start_date      = isset( $_REQUEST['event_date'] ) && $time_status == 'yes' ? sanitize_text_field( $_REQUEST['event_date'] ) : sanitize_text_field( $_REQUEST['event_date'] ) . ' ' . $start_time;
			$event_start_date = isset( $_REQUEST['event_date'] ) && $time_status == 'yes' ? sanitize_text_field( $_REQUEST['event_date'] ) : sanitize_text_field( $_REQUEST['event_date'] ) . ' ' . $start_time;
			$start_date       = $recurring == 'yes' && isset( $_REQUEST['event_date'] ) ? sanitize_text_field( $_REQUEST['event_date'] ) : $_start_date;
			$datepicker_format = mep_get_option( 'mep_datepicker_format', 'general_setting_sec', 'yy-mm-dd' );
			$date_format       = mep_rec_get_datepicker_php_format( $datepicker_format );
			$start_date = str_replace( [ ',', '-' ], [ '', '-' ], $start_date );
			$start_date = date( "Y-m-d H:i", strtotime( html_entity_decode( $start_date ) ) );
			$post_id                                     = $event_id;
			$event_more_date[0]['event_more_start_date'] = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_more_date[0]['event_more_start_time'] = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_start_time', true ) ) );
			$event_more_date[0]['event_more_end_date']   = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$event_more_date[0]['event_more_end_time']   = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_end_time', true ) ) );
			$event_more_dates                            = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : array();
			$event_multi_date                            = array_merge( $event_more_date, $event_more_dates );
			$event_expire_date                           = mep_esc_html( $start_date );
			$total_event_available_seat                  = mep_re_event_available_seat( $event_id, $start_date );
			// $total_event_available_seat                     = 10;
			if ( $total_event_available_seat <= 0 ) {
				/**
				 * If All the seats are booked then it fire the below hooks, The event no seat texts are in the inc/template-parts/event_labels.php file
				 */
				do_action( 'mep_event_no_seat_text', $event_id );
				do_action( 'mep_after_no_seat_notice', $event_id );
				?>
                <script>
                    //jQuery('.mep_event_add_cart_table').hide();
                </script>
				<?php
			} else {
				$count                     = 1;
				$mep_event_ticket_type_arr = get_post_meta( $event_id, 'mep_event_ticket_type', true ) ? get_post_meta( $event_id, 'mep_event_ticket_type', true ) : [];
				$mep_event_ticket_type     = apply_filters( 'mep_event_ticket_type_arr', $mep_event_ticket_type_arr, $event_id, $start_date );
				$mep_available_seat        = get_post_meta( $event_id, 'mep_available_seat', true ) ? get_post_meta( $event_id, 'mep_available_seat', true ) : 'on';
				$seat_plan                 = get_post_meta( $event_id, 'mepsp_event_seat_plan_info', true ) ? get_post_meta( $event_id, 'mepsp_event_seat_plan_info', true ) : [];
				$seat_plan_visible         = get_post_meta( $event_id, 'mp_event_seat_plan_visible', true ) ? get_post_meta( $event_id, 'mp_event_seat_plan_visible', true ) : '1';
				if ( class_exists( 'MP_ESP_Frontend' ) && sizeof( $seat_plan ) > 0 && $seat_plan_visible == 2 ) {
					$ticket_type_file_path = apply_filters( 'mep_ticket_type_file_path', mep_template_file_path( 'single/ticket_type_list.php' ), $event_id, $start_date );
					require( $ticket_type_file_path );
				} else {
					?>
                    <script>
                        jQuery('.mep_event_add_cart_table').show();
                    </script>
                    <table id='mep_event_ticket_type_table'>
                        <thead>
                        <tr class='ex-sec-title mep_ticket_type_title'>
                            <th>
                        <span class="tkt-qty" style="text-align: left;">
                            <?php echo _e( 'Ticket type', 'mage-eventpress' ); ?>
                        </span>
                            </th>
                            <th>
                        <span class="tkt-qty" style="text-align: center;">
                            <?php echo mep_get_option( 'mep_ticket_qty_text', 'label_setting_sec' ) ? mep_get_option( 'mep_ticket_qty_text', 'label_setting_sec' ) : mep_esc_html( 'Ticket Qty:', 'mage-eventpress' ); ?>
                        </span>
                            </th>
                            <th>
                        <span class="tkt-pric" style="text-align: center;">
                            <?php echo mep_get_option( 'mep_per_ticket_price_text', 'label_setting_sec' ) ? mep_get_option( 'mep_per_ticket_price_text', 'label_setting_sec' ) : mep_esc_html( 'Per Ticket Price:', 'mage-eventpress' ); ?>
                        </span>
                            </th>
                        </tr>
                        </thead>
						<?php
							$current_date        = apply_filters( 'mep_ticket_current_time', current_time( 'Y-m-d H:i' ), $start_date, $event_id );
							$is_admin            = 0;
							$event_selected_date = $is_admin > 0 ? date( 'Y-m-d H:i', strtotime( 'tomorrow' ) ) : date( 'Y-m-d H:i', strtotime( $start_date ) );
							// $event_selected_date           = date('Y-m-d H:i', strtotime($start_date));
							if ( strtotime( $current_date ) < strtotime( $event_selected_date ) ) {
								foreach ( $mep_event_ticket_type as $field ) {
									$ticket_type_name    = array_key_exists( 'option_name_t', $field ) ? mep_remove_apostopie( $field['option_name_t'] ) : '';
									$qty_t_type          = isset( $field['option_qty_t_type'] ) ? mep_esc_html( $field['option_qty_t_type'] ) : 'input';
									$total_quantity      = isset( $field['option_qty_t'] ) ? mep_esc_html( $field['option_qty_t'] ) : 0;
									$default_qty         = isset( $field['option_default_qty_t'] ) && $field['option_default_qty_t'] > 0 ? mep_esc_html( $field['option_default_qty_t'] ) : 0;
									$total_resv_quantity = isset( $field['option_rsv_t'] ) ? mep_esc_html( $field['option_rsv_t'] ) : 0;
									$ticket_details      = isset( $field['option_details_t'] ) ? mep_esc_html( $field['option_details_t'] ) : '';
									$event_date          = $start_date;
									// $total_sold             = (int) mep_ticket_type_sold($event_id, $field['option_name_t'], $event_date);
									$total_sold    = mep_get_ticket_type_seat_count( $event_id, $ticket_type_name, $event_date, $total_quantity, $total_resv_quantity );
									$total_tickets = (int) $total_quantity - ( (int) $total_sold + (int) $total_resv_quantity );
									// echo mep_get_count_total_available_seat($event_id);
									// $total_tickets          = mep_get_ticket_type_seat_count($event_id,$ticket_type_name,$event_date,$total_quantity,$total_resv_quantity);
									$total_seats       = apply_filters( 'mep_total_ticket_of_type', $total_tickets, $event_id, $field, $event_date );
									$total_seats       = apply_filters( 'mep_total_ticket_left_of_type', $total_seats, $event_id, $field, $event_date );
									$total_min_seat    = apply_filters( 'mep_ticket_min_qty', 0, $event_id, $field );
									$default_quantity  = apply_filters( 'mep_ticket_default_qty', $default_qty, $event_id, $field );
									$total_left        = apply_filters( 'mep_total_ticket_of_type', $total_tickets, $event_id, $field, $event_date );
									$total_ticket_left = apply_filters( 'mep_total_ticket_left_of_type', $total_tickets, $post_id, $field, $event_date );
									// $total_left             = apply_filters('mep_total_ticket_left_of_type', $total_left, $event_id, $field,$event_date);
									$ticket_price          = apply_filters( 'mep_ticket_type_price', $field['option_price_t'], $field['option_name_t'], $event_id, $field );
									$passed                = apply_filters( 'mep_ticket_type_validation', true );
									$post_id               = $event_id;
									$ticket_type_file_path = apply_filters( 'mep_ticket_type_file_path', mep_template_file_path( 'single/ticket_type_list.php' ), $post_id, $start_date );
									$sale_end_datetime = isset( $field['option_sale_end_date_t'] ) ? date( 'Y-m-d H:i', strtotime( $field['option_sale_end_date_t'] ) ) : date( 'Y-m-d H:i', strtotime( $event_date ) );
									if ( strtotime( $current_date ) <= strtotime( $sale_end_datetime ) ) {
										require( $ticket_type_file_path );
									}
									?>
									<?php $count ++;
								}
							} else {
								?>
                                <tr>
                                    <td colspan=3>
										<?php _e( 'Sorry, Event Date & Time is already over. Select another time.', 'mage-eventpress' ); ?>
                                    </td>
                                </tr>
								<?php
							}
						?>
                    </table>
					<?php
					do_action( 'mep_re_after_ajax_ticket_type', $event_id, $start_date );
				}
			}
		}
		die();
	}
	function show_none( $content, $event_id ) {
		echo $content = esc_attr( $event_id );
	}
	add_action( 'wp_ajax_mep_re_ajax_load_extra_service_list', 'mep_re_ajax_load_extra_service_list' );
	add_action( 'wp_ajax_nopriv_mep_re_ajax_load_extra_service_list', 'mep_re_ajax_load_extra_service_list' );
	function mep_re_ajax_load_extra_service_list() {
		if ( wp_verify_nonce( $_POST['nonce'], 'mep-ajax-recurring-nonce' ) ) {
			$start_date              = isset( $_REQUEST['event_date'] ) ? sanitize_text_field( $_REQUEST['event_date'] ) : '';
			$event_date              = isset( $_REQUEST['event_date'] ) ? sanitize_text_field( $_REQUEST['event_date'] ) : '';
			$event_id                = isset( $_REQUEST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';
			$post_id                 = isset( $_REQUEST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';
			$extra_service_label     = isset( $_REQUEST['mep_extra_service_label'] ) ? sanitize_text_field( $_POST['mep_extra_service_label'] ) : __( 'Extra Services', 'mage-eventpress' );
			$count                   = 1;
			$mep_events_extra_prices = get_post_meta( $post_id, 'mep_events_extra_prices', true ) ? get_post_meta( $post_id, 'mep_events_extra_prices', true ) : array();
			if ( sizeof( $mep_events_extra_prices ) > 0 ) {
				require( mep_template_file_path( 'single/extra_service_list.php' ) );
			}
			do_action( 'mep_re_after_ajax_extra_service_list', $event_id, $start_date );
		}
		die();
	}
	add_action( 'mep_re_after_ajax_ticket_type', 'mep_re_single_page_js_script', 10, 2 );
	function mep_re_single_page_js_script( $event_id, $estart_date ) {
		$currency_pos = get_option( 'woocommerce_currency_pos' );
		ob_start();
		require_once( dirname( __DIR__ ) . "/js/ajax_after_ticket_type.php" );
		echo ob_get_clean();
	}
	add_action( 'mep_re_after_ajax_extra_service_list', 'mep_re_single_page_js_script_extra_service', 10, 2 );
	function mep_re_single_page_js_script_extra_service( $event_id, $estart_date ) {
		$currency_pos = get_option( 'woocommerce_currency_pos' );
		ob_start();
		require_once( dirname( __DIR__ ) . "/js/ajax_after_extra_service.php" );
		echo ob_get_clean();
	}
	function mep_get_event_date( $global_on_days_arr ) {
		global $post;
		$event_id         = is_object( $post ) ? $post->ID : get_the_id();
		$time_status      = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$event_start_time = date( 'H:i:s', strtotime( get_post_meta( $event_id, 'event_start_datetime', true ) ) );
		$event_time       = $time_status == 'no' ? ' ' . $event_start_time : '';
		$now              = $time_status == 'no' ? current_time( 'Y-m-d H:i:s' ) : current_time( 'Y-m-d' );
		$dt               = [];
		foreach ( $global_on_days_arr as $dates ) {
			if ( strtotime( $now ) <= strtotime( $dates . $event_time ) ) {
				$dt[] = $dates;
			}
		}

		return $dt;
	}
	add_filter( 'mep_display_date_only', 'mep_re_display_date_only', 10, 2 );
	function mep_re_display_date_only( $date, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				//  print_r($value);
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			// $event_date_display_list = mep_get_event_date($global_on_days_arr);
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$date                    = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? get_mep_datetime( $event_date_display_list[0], 'date-text' ) : '';
		}

		return $date;
	}
	add_action( 'mep_event_list_upcoming_date_li', 'mep_re_event_list_upcoming_date_li' );
	function mep_re_event_list_upcoming_date_li( $event_id ) {
		$recurring               = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		$show_end_date           = get_post_meta( $event_id, 'mep_show_end_datetime', true ) ? get_post_meta( $event_id, 'mep_show_end_datetime', true ) : 'yes';
		$end_date_display_status = apply_filters( 'mep_event_datetime_status', $show_end_date, $event_id );
		$hide_only_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'general_setting_sec', 'no' );
		// $hide_only_end_time_list = 'yes';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				//  print_r($value);
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			// $event_date_display_list = mep_get_event_date($global_on_days_arr);
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$every_day               = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? $event_date_display_list[0] : '';
			?>
            <li class="mep_list_event_date">
                <div class="evl-ico"><i class="far fa-calendar-alt"></i></div>
                <div class="evl-cc">
                    <h5>
						<?php echo is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? get_mep_datetime( $event_date_display_list[0], 'date-text' ) : ''; ?>
                    </h5>
					<?php do_action( 'mep_event_list_loop_footer', $event_id ); ?>
                </div>
            </li>
			<?php
		} elseif ( $recurring == 'yes' ) {
			$event_start_datetime = get_post_meta( $event_id, 'event_start_datetime', true ) ? get_post_meta( $event_id, 'event_start_datetime', true ) : '';
			$event_end_datetime   = get_post_meta( $event_id, 'event_end_datetime', true ) ? get_post_meta( $event_id, 'event_end_datetime', true ) : '';
			$event_multidate      = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : '';
			//  $event_multidate        = array_key_exists('mep_event_more_date', $event_meta) ? maybe_unserialize($event_meta['mep_event_more_date'][0]) : array();
			// print_r($event_multidate);
			$event_std[] = array(
				'event_std' => $event_start_datetime,
				'event_etd' => $event_end_datetime
			);
			$a           = 1;
			if ( is_array( $event_multidate ) && sizeof( $event_multidate ) > 0 ) {
				foreach ( $event_multidate as $event_mdt ) {
					$event_std[ $a ]['event_std'] = $event_mdt['event_more_start_date'] . ' ' . $event_mdt['event_more_start_time'];
					$event_std[ $a ]['event_etd'] = $event_mdt['event_more_end_date'] . ' ' . $event_mdt['event_more_end_time'];
					$a ++;
				}
			}
			$cn = 0;
			foreach ( $event_std as $_event_std ) {
				$std        = sanitize_text_field( $_event_std['event_std'] );
				$start_date = date( 'Y-m-d', strtotime( $_event_std['event_std'] ) );
				$end_date   = date( 'Y-m-d', strtotime( $_event_std['event_etd'] ) );
				if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $std ) && $cn == 0 ) {
					?>
                    <li class="mep_list_event_date">
                        <div class="evl-ico"><i class="far fa-calendar-alt"></i></div>
                        <div class="evl-cc">
                            <h5>
								<?php echo get_mep_datetime( $std, 'date-text' ); ?>
                            </h5>
                            <h5><?php echo get_mep_datetime( $_event_std['event_std'], 'time' );
									if ( $hide_only_end_time_list == 'no' && $end_date_display_status == 'yes' ) { ?> - <?php if ( $start_date == $end_date ) {
										echo get_mep_datetime( $_event_std['event_etd'], 'time' );
									} else {
										echo get_mep_datetime( $_event_std['event_etd'], 'date-time-text' );
									}
									} ?></h5>
                        </div>
                    </li>
					<?php
					$cn ++;
				}
			}
		}
	}
	add_filter( 'mep_event_upcoming_date', 'mep_re_event_upcoming_date', 10, 2 );
	function mep_re_event_upcoming_date( $date, $event_id ) {
// print_r(mep_re_event_upcoming_date_filter($date, $event_id));
		$arr = mep_re_event_upcoming_date_filter( $date, $event_id ) ? mep_re_event_upcoming_date_filter( $date, $event_id ) : get_post_meta( $event_id, 'event_start_datetime', true );

		return $arr;
	}
	add_filter( 'mep_event_upcoming_date_filter', 'mep_re_event_upcoming_date_filter', 10, 2 );
	function mep_re_event_upcoming_date_filter( $date, $event_id ) {
		$recurring               = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		$hide_only_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'general_setting_sec', 'no' );
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$every_day               = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? $event_date_display_list[0] : '';
			$every_day               = date( 'Y-m-d', strtotime( $every_day ) );
			if ( $time_status == 'no' ) {
				$start_date = $every_day;
				$start_time = get_post_meta( $event_id, 'event_start_time', true );
				$date       = $every_day . ' ' . $start_time;
			} elseif ( $time_status == 'yes' ) {
				$calender_day = strtolower( date( 'D', strtotime( $every_day ) ) );
				$day_name     = 'mep_ticket_times_' . $calender_day;
				$time         = get_post_meta( $event_id, $day_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_name, true ) ) : maybe_unserialize( $global_time_slots );
				$time_list    = [];
				foreach ( $time as $_time ) {
					$time_list[] = $_time['mep_ticket_time'];
				}
				if ( sizeof( $time_list ) > 0 ) {
					$date = date( 'Y-m-d H:i:s', strtotime( $every_day . ' ' . $time_list[0] ) );
				}
			}
		} elseif ( $recurring == 'yes' ) {
			$event_start_datetime = get_post_meta( $event_id, 'event_start_datetime', true ) ? get_post_meta( $event_id, 'event_start_datetime', true ) : '';
			$event_end_datetime   = get_post_meta( $event_id, 'event_end_datetime', true ) ? get_post_meta( $event_id, 'event_end_datetime', true ) : '';
			$event_multidate      = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : '';
			$event_std[]          = array(
				'event_std' => $event_start_datetime,
				'event_etd' => $event_end_datetime
			);
			$a                    = 1;
			if ( is_array( $event_multidate ) && sizeof( $event_multidate ) > 0 ) {
				foreach ( $event_multidate as $event_mdt ) {
					$event_std[ $a ]['event_std'] = $event_mdt['event_more_start_date'] . ' ' . $event_mdt['event_more_start_time'];
					$event_std[ $a ]['event_etd'] = $event_mdt['event_more_end_date'] . ' ' . $event_mdt['event_more_end_time'];
					$a ++;
				}
				$cn = 0;
				foreach ( $event_std as $_event_std ) {
					$std        = $_event_std['event_std'];
					$start_date = date( 'Y-m-d H:i:s', strtotime( $_event_std['event_std'] ) );
					$end_date   = date( 'Y-m-d', strtotime( $_event_std['event_etd'] ) );
					if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $std ) && $cn == 0 ) {
						$date = $start_date;
						$cn ++;
					}
				}
			}
		}

		return $date;
	}
	add_filter( 'mep_event_list_only_day_number', 'mep_re_event_list_only_day_number', 90, 2 );
	function mep_re_event_list_only_day_number( $day, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$day                     = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? get_mep_datetime( $event_date_display_list[0], 'day' ) : '';
		}

		// return $day;
		return get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'day' );
	}
	add_filter( 'mep_event_details_only_time', 'mep_re_event_details_only_time', 10, 2 );
	function mep_re_event_details_only_time( $time, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_times_global', true ) ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			$global_time_arr    = [];
			foreach ( $global_time_slots as $gt ) {
				$global_time_arr[] = $gt['mep_ticket_time'];
			}
			$start_time = $time_status == 'yes' && sizeof( $global_time_arr ) > 0 ? $global_time_arr[0] : get_post_meta( $event_id, 'event_start_time', true );
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$time                    = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? get_mep_datetime( $event_date_display_list[0] . ' ' . $start_time, 'time' ) : '';
		}

		return $time;
	}
	add_filter( 'mep_event_list_only_month_name', 'mep_re_event_list_only_month_name', 10, 2 );
	function mep_re_event_list_only_month_name( $month, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$month                   = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? get_mep_datetime( $event_date_display_list[0], 'month' ) : '';
		}

		return get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'month-name' );
	}
	add_filter( 'mep_event_date_more_date_array_event_list', 'mep_re_event_date_more_date_array_event_list', 10, 2 );
	function mep_re_event_date_more_date_array_event_list( $more_date, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$moreDate                = $event_date_display_list;
			$more_date               = [];
			foreach ( $moreDate as $_moreDate ) {
				$more_date[]['event_more_start_date'] = $_moreDate;
				$more_date[]['event_more_start_time'] = '12:00 PM';
				$more_date[]['event_more_end_date']   = '';
				$more_date[]['event_more_end_time']   = '';
			}
		}

		return $more_date;
	}
	add_filter( 'mep_event_date_more_date_array', 'mep_re_event_date_more_date_array', 10, 2 );
	function mep_re_event_date_more_date_array( $more_date, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$more_date               = $event_date_display_list;
		}

		return $more_date;
	}
	add_action( 'mep_event_everyday_date_list_display', 'mep_re_event_everyday_date_list_display' );
	function mep_re_event_everyday_date_list_display( $event_id, $type = 'display' ) {
		$time_status             = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots       = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days         = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
		$global_off_dates        = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
		$event_start_date        = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
		$event_end_date          = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
		$interval                = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
		$period                  = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr      = [];
		$show_end_date           = get_post_meta( $event_id, 'mep_show_end_datetime', true ) ? get_post_meta( $event_id, 'mep_show_end_datetime', true ) : 'yes';
		$end_date_display_status = apply_filters( 'mep_event_datetime_status', $show_end_date, $event_id );
		$the_recurring_dates = [];



		foreach ( $period as $key => $value ) {
			//  print_r($value);
			$global_on_days_arr[] = $value->format( 'Y-m-d' );
		}
		
		$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;

		            // code by user
					$special_dates = MP_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
					// print_r($special_dates);
					if ( is_array( $special_dates ) ) {
						$now = strtotime(current_time( 'Y-m-d' ));
						foreach ( $special_dates as $special_date ) {
							if (empty($special_date['start_date']) || $now > strtotime( $special_date['start_date'] ) ) {
								continue;
							}
							// Not today
							if ($now < strtotime( $special_date['start_date'] )) {
								$global_on_days_arr[] = date('Y-m-d',strtotime($special_date['start_date']));
								continue;
							}
							// Today, check time
							if ( isset( $special_date['time'] ) && is_array( $special_date['time'] ) ) {
								foreach ( $special_date['time'] as $sd_time ) {
									if (empty($sd_time['mep_ticket_time'])) {
										continue;
									}
									$time_str = $special_date['start_date'] . ' ' . $sd_time['mep_ticket_time'] . ' ' . wp_timezone_string();
									$event_php_time = strtotime( $time_str );
									if ( time() < $event_php_time ) {
										$global_on_days_arr[] = date('Y-m-d',strtotime($special_date['start_date']));
									}
								}
							}
						}
					}
					sort($global_on_days_arr);

		
		$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
		foreach ( $event_date_display_list as $every_day ) {
			$event_day = strtolower( date( 'D', strtotime( $every_day ) ) );
			if ( ! in_array( $event_day, $global_off_days ) ) {
				$the_recurring_dates[] = $every_day;
				if ( $type == 'display' ) {
					if ( $time_status == 'no' ) {
						$start_date     = $every_day;
						$end_date       = $every_day;
						$start_time     = get_post_meta( $event_id, 'event_start_time', true ) ? get_post_meta( $event_id, 'event_start_time', true ) : '';
						$end_time       = get_post_meta( $event_id, 'event_end_time', true ) ? get_post_meta( $event_id, 'event_end_time', true ) : '';
						$start_datetime = $every_day . ' ' . $start_time;
						$end_datetime   = $every_day . ' ' . $end_time;
						require( mep_template_file_path( 'single/date_list.php' ) );
					} elseif ( $time_status == 'yes' ) {
						?>
                        <li>
                            <a href="<?php echo get_the_permalink( $event_id ) . esc_attr( '?date=' . strtotime( $every_day ) ); ?>">
                                <span class="mep-more-date"><i class="far fa-calendar-alt"></i> <?php echo get_mep_datetime( $every_day, 'date-text' ); ?></span>
                                <span class='mep-more-time'>
                            <?php
	                            $calender_day = strtolower( date( 'D', strtotime( $every_day ) ) );
	                            $day_name     = 'mep_ticket_times_' . $calender_day;
	                            $time         = get_post_meta( $event_id, $day_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_name, true ) ) : maybe_unserialize( $global_time_slots );
	                            $time_list    = [];
	                            foreach ( $time as $_time ) { ?>
                                    <span class="time"><?php echo $_time['mep_ticket_time_name'] . '( ' . get_mep_datetime( $_time['mep_ticket_time'], 'time' ) . ')'; ?></span>
	                            <?php } ?>
                        </span>
                            </a>
                        </li>
						<?php
					}
				}
			}
		}
		if ( $type == 'array' ) {
			return $the_recurring_dates;
		}
	}
	function mep_re_get_the_upcomming_date_arr( $event_id ) {
		$time_status        	= get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots  	= get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days    	= get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
		$global_off_dates   	= get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : '';
		$event_start_date   	= date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
		$event_end_date     	= date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
		$interval           	= get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
		$period             	= mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr 	= [];
		$off_dates          	= [];
		$the_recurring_dates 	= [];
		
		foreach ( $period as $key => $value ) {
			$global_on_days_arr[] = $value->format( 'Y-m-d' );
		}
		$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
				            // code by user
							$special_dates = MP_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
							// print_r($special_dates);
							if ( is_array( $special_dates ) ) {
								$now = strtotime(current_time( 'Y-m-d' ));
								foreach ( $special_dates as $special_date ) {
									if (empty($special_date['start_date']) || $now > strtotime( $special_date['start_date'] ) ) {
										continue;
									}
									// Not today
									if ($now < strtotime( $special_date['start_date'] )) {
										$global_on_days_arr[] = date('Y-m-d',strtotime($special_date['start_date']));
										continue;
									}
									// Today, check time
									if ( isset( $special_date['time'] ) && is_array( $special_date['time'] ) ) {
										foreach ( $special_date['time'] as $sd_time ) {
											if (empty($sd_time['mep_ticket_time'])) {
												continue;
											}
											$time_str = $special_date['start_date'] . ' ' . $sd_time['mep_ticket_time'] . ' ' . wp_timezone_string();
											$event_php_time = strtotime( $time_str );
											if ( time() < $event_php_time ) {
												$global_on_days_arr[] = date('Y-m-d',strtotime($special_date['start_date']));
											}
										}
									}
								}
							}
		sort($global_on_days_arr);
		$event_date_display_list = mep_get_event_date( $global_on_days_arr );

		if ( is_array( $global_off_dates ) && sizeof( $global_off_dates ) > 0 ) {
			foreach ( $global_off_dates as $key => $value ) {
				$off_dates[] = $value['mep_ticket_off_date'];
			}
		}
		$event_date_display_list = array_diff( $event_date_display_list, $off_dates );
		foreach ( $event_date_display_list as $every_day ) {
			$event_day = strtolower( date( 'D', strtotime( $every_day ) ) );
			if ( ! in_array( $event_day, $global_off_days ) ) {
				$the_recurring_dates[] = $every_day;
			}
		}

		return $the_recurring_dates;
	}
	function mep_re_get_everyday_event_date_sec( $event_id ) {
		$time_status       = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days   = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
		$global_off_dates  = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
		$event_start_date  = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
		$event_end_date    = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
		$interval          = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
		$period            = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
		//    $nd = mep_re_date_range($event_start_date, $event_end_date, $interval);
		$global_on_days_arr = [];
		foreach ( $period as $key => $value ) {
			//  print_r($value);
			$global_on_days_arr[] = $value->format( 'Y-m-d' );
		}
		$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
		$event_date_display = mep_re_get_the_upcomming_date_arr( $event_id );
		$datepicker_format  = mep_get_option( 'mep_datepicker_format', 'general_setting_sec', 'yy-mm-dd' );
		$date_format        = mep_rec_get_datepicker_php_format( $datepicker_format );
		if ( sizeof( $global_on_days_arr ) > 0 ) {
			$date_parameter = isset( $_GET['date'] ) ? sanitize_text_field( date( $date_format, $_GET['date'] ) ) : null;
			ob_start();
			?>
            <div class='mep_everyday_date_secs'>
                <div class="mep-date-time-select-area ">
                    <h3 class='mep_re_datelist_label'>
						<?php echo mep_get_option( 'mep_event_rec_select_event_date_text', 'label_setting_sec', __( 'Select Event Date:', 'mage-eventpress' ) ); ?>
                    </h3>
                    <div class="mep-date-time">
						<?php if ( sizeof( $global_on_days_arr ) == 1 ) { ?>
                            <span style='font-size: 20px;'><?php if ( $time_status == 'yes' ) {
									echo mep_esc_html( $date_parameter )
									     ?? get_mep_datetime( $global_on_days_arr[0], 'date-text' );
								} else {
									echo $date_parameter ?? get_mep_datetime( $global_on_days_arr[0], 'date-time-text' );
								} ?></span>
                            <input <?php if ( ! is_admin() ) {
								echo 'readonly';
							} ?> type="hidden" name='mep_everyday_dates' id='mep_everyday_datepicker' value="<?php echo $date_parameter ?? mep_esc_html( $global_on_days_arr[0] ); ?>">
						<?php } else { ?>
                            <span class='mep_recurring_datepicker_section'>
                    <span class='mep-datepicker-input-box'>
                        <input <?php if ( ! is_admin() ) {
	                        echo 'readonly';
                        } ?> type="text" name='mep_everyday_dates' id='mep_everyday_datepicker' value="<?php echo $date_parameter ?? date( $date_format, strtotime( mep_re_get_the_upcomming_date_arr( $event_id )[0] ) ); ?>">
                    </span>
                    </span>
						<?php } ?>
                        <!-- time -->
                        <div>
                        <span id="mep_everyday_event_time_list">
                            <?php
	                            if ( $time_status == 'yes' ) {
		                            ?>
                                    <input type="hidden" name='time_slot_name' id='time_slot_name' value=''>
		                            <?php
		                            mep_re_default_load_ticket_time_list( $event_id, $global_on_days_arr[0] );
	                            }
                            ?>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			if ( is_admin() ) {
				require_once( dirname( __DIR__ ) . "/js/datepicker_calculation.php" );
			}
			require_once( dirname( __DIR__ ) . "/js/ajax_everyday_datepicker.php" );
		} else {
			?>
            <div>
                <h5 style='text-align:center;color:red;font-size:20px'>
					<?php _e( 'Please Set Correct Event Start & Expire date', 'mage-event-press' ); ?>
                </h5>
            </div>
			<?php
		}
		echo ob_get_clean();
	}
	add_filter( 'mep_settings_general_arr', 'mep_recurring_gen_settings_item' );
	function mep_recurring_gen_settings_item( $default_translation ) {
		$current_date = current_time( 'Y-m-d' );
		$lang         = get_bloginfo( "language" );
		$gen_settings = array(
			array(
				'name'    => 'mep_datepicker_format',
				'label'   => __( 'Date Picker Format', 'mage-eventpress' ),
				'desc'    => __( 'If you want to change Date Picker Format, please select format. Default is yy-mm-dd. <b>Text Based Date format will not works in other language except english. Is your website is not English language please do not use any text based datepicker.</b>', 'mep-form-builder' ),
				'type'    => 'select',
				'default' => 'no',
				'options' => array(
					'yy-mm-dd'   => $current_date,
					'yy/mm/dd'   => date( 'Y/m/d', strtotime( $current_date ) ),
					// 'yy-dd-mm'      => date('Y-d-m',strtotime($current_date)),
					// 'yy/dd/mm'      => date('Y/d/m',strtotime($current_date)),
					'dd-mm-yy'   => date( 'd-m-Y', strtotime( $current_date ) ),
					// 'dd/mm/yy'      => date('d/m/Y',strtotime($current_date)),
					'mm-dd-yy'   => date( 'm-d-Y', strtotime( $current_date ) ),
					'mm/dd/yy'   => date( 'm/d/Y', strtotime( $current_date ) ),
					'd M , yy'   => date( 'j M , Y', strtotime( $current_date ) ),
					'D d M , yy' => date( 'D j M , Y', strtotime( $current_date ) ),
					'M d , yy'   => date( 'M  j, Y', strtotime( $current_date ) ),
					'D M d , yy' => date( 'D M  j, Y', strtotime( $current_date ) ),
					$lang        => $lang,
				)
			)
		);

		return array_merge( $default_translation, $gen_settings );
	}
	function mep_rec_get_datepicker_php_format( $fotmat ) {
		$php_format = str_replace(
			array( "yy-mm-dd", "yy/mm/dd", "yy-dd-mm", "yy/dd/mm", "dd-mm-yy", "dd/mm/yy", "mm-dd-yy", "mm/dd/yy", "d M , yy", "D d M , yy", "M d , yy", "D M d , yy" ),
			array( "Y-m-d", "Y/m/d", "Y-d-m", "Y/d/m", "d-m-Y", "d/m/Y", "m-d-Y", "m/d/Y", "j M , Y", "D j M , Y", "M  j, Y", "D M  j, Y" ),
			$fotmat
		);

		return $php_format;
	}
	function mep_re_default_load_ticket_time_list( $event_id, $event_date ) {
		$selected_day       = strtolower( date( 'D', strtotime( $event_date ) ) );
		$day_time_slot_name = 'mep_ticket_times_' . $selected_day;
		$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_times_global', true ) ) : [];
		$day_time_slots     = get_post_meta( $event_id, $day_time_slot_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_time_slot_name, true ) ) : [];
		$event_off_days     = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
		$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : [];
		$global_off_dates_arr = [];
		if ( sizeof( $global_off_dates ) > 0 ) {
			foreach ( $global_off_dates as $off_dates ) {
				$global_off_dates_arr[] = $off_dates['mep_ticket_off_date'];
			}
		}
		if ( ( is_array( $event_off_days ) && sizeof( $event_off_days ) > 0 && in_array( $selected_day, $event_off_days ) ) || in_array( $event_date, $global_off_dates_arr ) ) {
			echo mep_get_option( 'mep_event_rec_day_off_text', 'label_setting_sec', __( 'Day Off', 'mage-eventpress' ) );
		} else {
			?>
            <select name="ea_event_date" id="mep_everyday_ticket_time">
				<?php apply_filters( 'mep_everyday_time_list_item', mep_get_everyday_time_list( $event_id, $event_date ), $event_id ); ?>
            </select>
			<?php
			if ( $time_status == 'yes' ) {
				require_once( dirname( __DIR__ ) . "/js/onload_timelist.php" );
			}
		}
	}
	add_action( 'wp_ajax_mep_re_ajax_load_ticket_time_list', 'mep_re_ajax_load_ticket_time_list' );
	add_action( 'wp_ajax_nopriv_mep_re_ajax_load_ticket_time_list', 'mep_re_ajax_load_ticket_time_list' );
	function mep_re_ajax_load_ticket_time_list() {
		if ( wp_verify_nonce( $_POST['nonce'], 'mep-ajax-recurring-nonce' ) ) {
			$event_id   = sanitize_text_field( $_REQUEST['event_id'] );
			$event_date = isset( $_REQUEST['event_date'] ) ? sanitize_text_field( $_REQUEST['event_date'] ) : '';
			$event_date = str_replace( [ ',' ], [ '' ], $event_date );
			$event_date = date( 'Y-m-d', strtotime( $event_date ) );
			$selected_day         = strtolower( date( 'D', strtotime( $event_date ) ) );
			$day_time_slot_name   = 'mep_ticket_times_' . $selected_day;
			$time_status          = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots    = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_times_global', true ) ) : [];
			$day_time_slots       = get_post_meta( $event_id, $day_time_slot_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_time_slot_name, true ) ) : [];
			$event_off_days       = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates     = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : [];
			$global_off_dates_arr = [];
			if ( sizeof( $global_off_dates ) > 0 ) {
				foreach ( $global_off_dates as $off_dates ) {
					$global_off_dates_arr[] = $off_dates['mep_ticket_off_date'];
				}
			}
			if ( ( is_array( $event_off_days ) && sizeof( $event_off_days ) > 0 && in_array( $selected_day, $event_off_days ) ) || in_array( $event_date, $global_off_dates_arr ) ) {
				echo mep_get_option( 'mep_event_rec_day_off_text', 'label_setting_sec', __( 'Day Off', 'mage-eventpress' ) );
			} else {
				?>
				
                <input type="hidden" name='time_slot_name' id='time_slot_name' value=''>
                <select name="ea_event_date" id="mep_everyday_ticket_time">
                    <option value="0"><?php echo mep_get_option( 'mep_event_rec_select_a_time_text', 'label_setting_sec', __( 'Please Select A Time', 'mage-eventpress' ) ); ?></option>
					<?php apply_filters( 'mep_everyday_time_list_item', mep_get_everyday_time_list( $event_id, $event_date ), $event_id ); ?>
                </select>
				<?php
				require_once( dirname( __DIR__ ) . "/js/ajax_ticket_time_list.php" );
			}
		}
		die();
	}
	add_action( 'mep_after_cart_item_display_list', 'mep_re_display_cart_data' );
	function mep_re_display_cart_data( $cart_item ) {
		$time_slot = array_key_exists( "event_everyday_time_slot", $cart_item ) ? $cart_item['event_everyday_time_slot'] : '';
		if ( $time_slot ) {
			?>
            <li><?php echo mep_get_option( 'mep_event_rec_time_slot_text', 'label_setting_sec', __( 'Time Slot:', 'mage-eventpress' ) );
					echo mep_esc_html( $time_slot ); ?></li>
			<?php
		}
	}
	add_action( 'mep_event_cart_order_data_add', 'mep_re_add_cart_order_data', 10, 2 );
	function mep_re_add_cart_order_data( $values, $item ) {
		$cart_location = array_key_exists( "event_everyday_time_slot", $values ) ? $values['event_everyday_time_slot'] : '';
		if ( $cart_location ) {
			$item->add_meta_data( mep_get_option( 'mep_event_rec_time_slot_text', 'label_setting_sec', __( 'Time Slot:', 'mage-eventpress' ) ), $cart_location );
			$item->add_meta_data( '_time_slot', $cart_location );
		}
	}
	add_filter( 'mep_event_attendee_dynamic_data', 'mep_re_event_attendee_data_save', 15, 6 );
	function mep_re_event_attendee_data_save( $the_array, $pid, $type, $order_id, $event_id, $_user_info ) {
		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $item_id => $item_values ) {
			$item_id = $item_id;
		}
		$time_slot = wc_get_order_item_meta( $item_id, '_time_slot', true ) ? wc_get_order_item_meta( $item_id, '_time_slot', true ) : '';
		if ( $time_slot ) {
			$the_array[] = array(
				'name'  => 'ea_time_slot',
				'value' => $time_slot
			);
		}

		return $the_array;
	}
	add_action( 'mep_pdf_event_multidate', 'mep_re_show_data_in_pdf', 10, 4 );
	function mep_re_show_data_in_pdf( $ticket_id, $event_id = '', $order_id = '', $ticket_type = '' ) {
		$time_slot = get_post_meta( $ticket_id, 'ea_time_slot', true ) ? get_post_meta( $ticket_id, 'ea_time_slot', true ) : '';
		if ( $time_slot ) {
			?>
            <li><strong><?php echo mep_get_option( 'mep_event_rec_time_slot_text', 'label_setting_sec', __( 'Time Slot:', 'mage-eventpress' ) ); ?></strong> <?php echo mep_esc_html( $time_slot ); ?></li>
			<?php
		}
	}
	function mep_get_everyday_time_list( $event_id, $event_date ) {
		// echo $event_id;
		$hidden_date = $event_date ? date( 'Y-m-d', strtotime( $event_date ) ) : '';
		$all_dates   = MPWEM_Functions::get_dates( $event_id );
		$all_times   = MPWEM_Functions::get_times( $event_id, $all_dates, $hidden_date );

		if ( sizeof( $all_times ) ) {
			foreach ( $all_times as $times ) { ?>
                <option value="<?php echo esc_attr( $hidden_date . ' ' . $times['start']['time'] ); ?>"><?php echo esc_html( $times['start']['label'] ); ?></option>
			<?php }
		}
	}
	add_action( 'admin_footer', 'mep_re_script', 99 );
	add_action( 'mep_event_single_template_end', 'mep_re_script', 99 );
	add_action( 'mep_after_event_cart_shortcode', 'mep_re_script', 99 );
	function mep_re_script( $event_id ) {
		require_once( dirname( __DIR__ ) . "/js/datepicker_calculation.php" );
	}
	add_action( 'mep_before_attendee_list_btn', 'mep_rq_show_everyday_datepicker' );
	function mep_rq_show_everyday_datepicker( $event_id ) {
		$time_status       = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days   = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
		$global_off_dates  = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
		$input_name        = $time_status == 'yes' ? 'mep_everyday_dates' : 'ea_event_date';
		ob_start();
		?>
        <div class='mep_everyday_date_secs'>
            <div class="mep-date-time-select-area ">
                <div>
                    <input type="text" name='<?php echo esc_attr( $input_name ); ?>' id='mep_everyday_datepicker_<?php echo esc_attr( $event_id ); ?>' value="<?php echo current_time( 'Y-m-d' ); ?>">
                </div>
                <div>
                    <span id="mep_everyday_event_time_list_<?php echo esc_attr( $event_id ); ?>"></span>
                </div>
            </div>
        </div>
		<?php
		require( dirname( __DIR__ ) . "/js/before_attendee_list_btn.php" );
		echo ob_get_clean();
	}
	add_action( 'mep_before_csv_export_btn', 'mep_rq_show_everyday_datepicker_csv_btn' );
	function mep_rq_show_everyday_datepicker_csv_btn( $event_id ) {
		$time_status       = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days   = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
		$global_off_dates  = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
		$input_name        = $time_status == 'yes' ? 'mep_everyday_dates' : 'ea_event_date';
		ob_start();
		?>
        <div class='mep_everyday_date_secs'>
            <div class="mep-date-time-select-area ">
                <div>
                    <i class="far fa-calendar-alt icon"></i>
                    <input type="text" name='<?php echo esc_attr( $input_name ); ?>' id='mep_everyday_datepicker_csv_<?php echo esc_attr( $event_id ); ?>' value="<?php echo current_time( 'Y-m-d' ); ?>">
                </div>
                <div>
                    <span id="mep_everyday_event_time_list_csv_<?php echo mep_esc_html( $event_id ); ?>"></span>
                </div>
            </div>
        </div>
		<?php
		require( dirname( __DIR__ ) . "/js/before_csv_export_btn.php" );
		echo ob_get_clean();
	}
	add_filter( 'mep_translation_string_arr', 'mep_re_translation_strings_reg' );
	function mep_re_translation_strings_reg( $default_translation ) {
		$recurring_translation = array(
			array(
				'name'    => 'mep_event_rec_time_slot_text',
				'label'   => __( 'Time Slot', 'mage-eventpress' ),
				'desc'    => __( 'Enter Text For Time Slot', 'mage-eventpress' ),
				'type'    => 'text',
				'default' => 'Time Slot'
			),
			array(
				'name'    => 'mep_event_rec_select_event_date_text',
				'label'   => __( 'Select Event Date:', 'mage-eventpress' ),
				'desc'    => __( 'Enter Text For Select Event Date: Text', 'mage-eventpress' ),
				'type'    => 'text',
				'default' => 'Select Event Date:'
			),
			array(
				'name'    => 'mep_event_rec_please_select_time_text',
				'label'   => __( 'Please Select Time', 'mage-eventpress' ),
				'desc'    => __( 'Enter Text For Please Select Time Text', 'mage-eventpress' ),
				'type'    => 'text',
				'default' => 'Please Select Time'
			),
			array(
				'name'    => 'mep_event_rec_please_wait_ticket_loading_text',
				'label'   => __( 'Please Wait! Ticket List is Loading......', 'mage-eventpress' ),
				'desc'    => __( 'Enter Text For Please Wait! Ticket List is Loading...... Text', 'mage-eventpress' ),
				'type'    => 'text',
				'default' => 'Please Wait! Ticket List is Loading......'
			),
			array(
				'name'    => 'mep_event_rec_please_wait_time_loading_text',
				'label'   => __( 'Time List is Loading..', 'mage-eventpress' ),
				'desc'    => __( 'Enter Text For Time List is Loading.. Text', 'mage-eventpress' ),
				'type'    => 'text',
				'default' => 'Time List is Loading..'
			),
			array(
				'name'    => 'mep_event_rec_day_off_text',
				'label'   => __( 'Day Off', 'mage-eventpress' ),
				'desc'    => __( 'Enter Text For Day Off Text', 'mage-eventpress' ),
				'type'    => 'text',
				'default' => 'Day Off'
			),
			array(
				'name'    => 'mep_event_rec_select_a_time_text',
				'label'   => __( 'Please Select A Time', 'mage-eventpress' ),
				'desc'    => __( 'Enter Text For Please Select A Time Text', 'mage-eventpress' ),
				'type'    => 'text',
				'default' => 'Please Select A Time'
			)
		);

		return array_merge( $default_translation, $recurring_translation );
	}
	add_filter( 'mep_settings_styling_arr', 'mep_re_style_strings_reg' );
	function mep_re_style_strings_reg( $default_translation ) {
		$recurring_translation = array(
			array(
				'name'    => 'mep_re_datepicker_bg_color',
				'label'   => __( 'Recurring/Repeated Datepicker Background Color', 'mage-eventpress' ),
				'desc'    => __( 'Select a color for Recurring/Repeated Datepickers Background', 'mage-eventpress' ),
				'default' => '#ffbe30',
				'type'    => 'color',
			),
			array(
				'name'    => 'mep_re_datepicker_text_color',
				'label'   => __( 'Recurring/Repeated Datepicker Text Color', 'mage-eventpress' ),
				'desc'    => __( 'Select a Color for Recurring/Repeated Datepicker text', 'mage-eventpress' ),
				'type'    => 'color',
				'default' => '#ffffff',
			),
		);

		return array_merge( $default_translation, $recurring_translation );
	}
	add_action( 'wp_ajax_mep_fb_ajax_attendee_filter_date', 'mep_fb_ajax_attendee_filter_date' );
	function mep_fb_ajax_attendee_filter_date() {
		$event_id  = sanitize_text_field( $_REQUEST['event_id'] );
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			mep_re_get_everyday_event_date_sec( $event_id );
		} elseif ( $recurring == 'yes' ) {
			$event_more_date[0]['event_more_start_date'] = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_more_date[0]['event_more_start_time'] = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_start_time', true ) ) );
			$event_more_date[0]['event_more_end_date']   = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$event_more_date[0]['event_more_end_time']   = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_end_time', true ) ) );
			$event_more_dates                            = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : array();
			$event_multi_date                            = array_merge( $event_more_date, $event_more_dates );
			// $mep_available_seat = array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';
			$count = 1;
			echo get_mep_re_recurring_date( $event_id, $event_multi_date, 'no' );
		} else {
			?>
            <input type="hidden" id='mep_everyday_ticket_time' value='0'>
			<?php
		}
		die();
	}
	add_action( 'mep_fb_attendee_list_script', 'mep_re_attendee_list_filter_script' );
	function mep_re_attendee_list_filter_script() {
		?>
        jQuery('#mep_event_id').on('change', function() {
        var event_id = jQuery(this).val();
        jQuery.ajax({
        type: 'POST',
        // url: mep_ajax.mep_ajaxurl,
        url: ajaxurl,
        data: {
        "action": "mep_fb_ajax_attendee_filter_date",
        "event_id": event_id
        },
        beforeSend: function() {
        jQuery('#event_attendee_filter_btn').hide();
        jQuery('#filter_attitional_btn').html('...');
        },
        success: function(data) {
        jQuery('#event_attendee_filter_btn').show();
        jQuery('#filter_attitional_btn').html(data);
        }
        });
        return false;
        });
		<?php
	}
	add_filter( 'mepca_event_time_list', 'mep_re_event_time_list', 10, 4 );
	function mep_re_event_time_list( $current_time, $date_arr, $event_id, $date ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status       = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			if ( $time_status == 'no' ) {
				return get_mep_datetime( get_post_meta( $event_id, 'event_start_time', true ), 'time' ) . '-' . get_mep_datetime( get_post_meta( $event_id, 'event_end_time', true ), 'time' );
			} else {
				$calender_day   = strtolower( date( 'D', strtotime( $date ) ) );
				$day_name       = 'mep_ticket_times_' . $calender_day;
				$this_day_times = get_post_meta( $event_id, $day_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_name, true ) ) : maybe_unserialize( $global_time_slots );
				$times          = [];
				if ( sizeof( $this_day_times ) > 0 ) {
					foreach ( $this_day_times as $_time ) {
						$times[] = $_time['mep_ticket_time_name'] . ' (' . get_mep_datetime( $_time['mep_ticket_time'], 'time' ) . ')';
					}
				}

				return implode( ', ', $times );
			}
		} else {
			return $current_time;
		}
	}
	add_filter( 'mepca_event_more_datetime_arr', 'mep_re_modify_calerder_pro_dates', 15, 2 );
	function mep_re_modify_calerder_pro_dates( $date_arr, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$now                  = current_time( 'Y-m-d H:i:s' );
			$event_start_date     = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date       = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$recurring            = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
			$event_off_days       = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
			$global_off_dates     = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : [];
			$global_off_dates_arr = [];
			$off_dates            = '';
			if ( sizeof( $global_off_dates ) > 0 ) {
				foreach ( $global_off_dates as $off_dates ) {
					$global_off_dates_arr[] = date( 'Y-m-d', strtotime( $off_dates['mep_ticket_off_date'] ) );
				}
				$off_dates = implode( ',', $global_off_dates_arr );
			}
			$global_off_days_arr = [];
			$off_days            = '';
			if ( sizeof( $event_off_days ) > 0 ) {
				foreach ( $event_off_days as $off_days ) {
					if ( $off_days == 'sat' ) {
						$off_days = 'sat';
					}
					if ( $off_days == 'tue' ) {
						$off_days = 'tue';
					}
					if ( $off_days == 'wed' ) {
						$off_days = 'wed';
					}
					if ( $off_days == 'thu' ) {
						$off_days = 'thu';
					}
					$global_off_days_arr[] = ucwords( $off_days );
				}
				$off_days = implode( ',', $global_off_days_arr );
			}
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			$events_days        = '';
			$cn                 = 0;
			foreach ( $period as $key => $value ) {
				$the_d = date( 'D', strtotime( $value->format( 'Y-m-d' ) ) );
				if ( ! in_array( $the_d, $global_off_days_arr ) ) {
					$global_on_days_arr[ $cn ]['event_more_start_date'] = date( 'Y-m-d', strtotime( $value->format( 'Y-m-d' ) ) );
					$global_on_days_arr[ $cn ]['event_more_start_time'] = '12:00';
					$global_on_days_arr[ $cn ]['event_more_end_date']   = date( 'Y-m-d', strtotime( $value->format( 'Y-m-d' ) ) );
					$global_on_days_arr[ $cn ]['event_more_end_time']   = '2:00';
				}
				$cn ++;
			}
			$every_date_arr = array_diff( $global_on_days_arr, $global_off_dates_arr );

			return array_merge( $every_date_arr, $date_arr );
		} else {
			return $date_arr;
		}
	}
	add_filter( 'mep_event_dates_in_calender_free', 'mep_re_modify_calerder_free_dates', 15, 2 );
	function mep_re_modify_calerder_free_dates( $date_arr, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$now                  = current_time( 'Y-m-d H:i:s' );
			$event_start_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_datetime', true ) ) );
			$event_end_date       = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_datetime', true ) ) );
			$recurring            = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
			$event_off_days       = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
			$global_off_dates     = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : [];
			$global_off_dates_arr = [];
			$off_dates            = '';
			if ( sizeof( $global_off_dates ) > 0 ) {
				foreach ( $global_off_dates as $off_dates ) {
					$global_off_dates_arr[] = date( 'Y-m-d', strtotime( $off_dates['mep_ticket_off_date'] ) );
				}
				$off_dates = implode( ',', $global_off_dates_arr );
			}
			$global_off_days_arr = [];
			$off_days            = '';
			if ( sizeof( $event_off_days ) > 0 ) {
				foreach ( $event_off_days as $off_days ) {
					if ( $off_days == 'sat' ) {
						$off_days = 'sat';
					}
					if ( $off_days == 'tue' ) {
						$off_days = 'tue';
					}
					if ( $off_days == 'wed' ) {
						$off_days = 'wed';
					}
					if ( $off_days == 'thu' ) {
						$off_days = 'thu';
					}
					$global_off_days_arr[] = ucwords( $off_days );
				}
				$off_days = implode( ',', $global_off_days_arr );
			}
			$interval = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period   = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			$events_days        = '';
			foreach ( $period as $key => $value ) {
				$the_d = date( 'D', strtotime( $value->format( 'Y-m-d' ) ) );
				if ( ! in_array( $the_d, $global_off_days_arr ) ) {
					$global_on_days_arr[] = date( 'Y-m-d H:i:s', strtotime( $value->format( 'Y-m-d H:i:s' ) ) );
				}
			}
			$fdate = array_diff( $global_on_days_arr, $global_off_dates_arr );
			$m_date_arr = [];
			if ( sizeof( $fdate ) > 0 ) {
				$i = 0;
				foreach ( $fdate as $mdate ) {
					// if(strtotime($now) < strtotime($mdate['event_more_start_date'].' '.$mdate['event_more_start_time'])){
					$mstart                    = $mdate;
					$mend                      = $mdate;
					$m_date_arr[ $i ]['start'] = $mstart;
					$m_date_arr[ $i ]['end']   = $mend;
					// }
					$i ++;
				}
			}

			return $m_date_arr;
		} else {
			return $date_arr;
		}
	}
	add_action( 'mep_event_list_only_date_show', 'mep_event_list_only_date_show_html' );
	function mep_event_list_only_date_show_html( $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$now              = current_time( 'Y-m-d H:i:s' );
			$event_start_date = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_datetime', true ) ) );
			$event_end_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_datetime', true ) ) );
			$recurring        = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
			$event_off_days   = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
			$global_off_dates     = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : [];
			$global_off_dates_arr = [];
			$off_dates            = '';
			if ( sizeof( $global_off_dates ) > 0 ) {
				foreach ( $global_off_dates as $off_dates ) {
					$global_off_dates_arr[] = date( 'Y-m-d', strtotime( $off_dates['mep_ticket_off_date'] ) );
				}
				$off_dates = implode( ',', $global_off_dates_arr );
			}
			$global_off_days_arr = [];
			$off_days            = '';
			if ( sizeof( $event_off_days ) > 0 ) {
				foreach ( $event_off_days as $off_days ) {
					if ( $off_days == 'sat' ) {
						$off_days = 'sat';
					}
					if ( $off_days == 'tue' ) {
						$off_days = 'tue';
					}
					if ( $off_days == 'wed' ) {
						$off_days = 'wed';
					}
					if ( $off_days == 'thu' ) {
						$off_days = 'thu';
					}
					$global_off_days_arr[] = ucwords( $off_days );
				}
				$off_days = implode( ',', $global_off_days_arr );
			}
			$interval = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period   = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			$events_days        = '';
			foreach ( $period as $key => $value ) {
				$the_d = date( 'D', strtotime( $value->format( 'Y-m-d' ) ) );
				if ( ! in_array( $the_d, $global_off_days_arr ) ) {
					$global_on_days_arr[] = date( 'Y-m-d H:i:s', strtotime( $value->format( 'Y-m-d H:i:s' ) ) );
				}
			}
			$fdate = array_diff( $global_on_days_arr, $global_off_dates_arr );
			$m_date_arr = [];
			if ( sizeof( $fdate ) > 0 ) {
				$i = 0;
				foreach ( $fdate as $mdate ) {
					if ( strtotime( $now ) < strtotime( $mdate ) ) {
						$mstart                    = $mdate;
						$mend                      = $mdate;
						$m_date_arr[ $i ]['start'] = $mstart;
						$m_date_arr[ $i ]['end']   = $mend;
					}
					$i ++;
				}
			}
			$day   = get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'day' );
			$month = get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'month' );
		} else {
			$day   = get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'day' );
			$month = get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'month' );
		}
		?>
        <div class="mep-ev-start-date">
            <div class="mep-day"><?php echo mep_esc_html( $day ); ?></div>
            <div class="mep-month"><?php echo mep_esc_html( $month ); ?></div>
        </div>
		<?php
	}
// Ajax Issue
	add_action( 'wp_head', 'mep_re_ajax_url', 5 );
	add_action( 'admin_head', 'mep_re_ajax_url', 5 );
	function mep_re_ajax_url() {
		?>
        <script type="text/javascript">
            var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
        </script>
		<?php
	}
	add_filter( 'display_post_states', 'mep_re_event_state_text', 10, 2 );
	function mep_re_event_state_text( $post_states, $post ) {
		$eid       = $post->ID;
		$recurring = get_post_meta( $eid, 'mep_enable_recurring', true ) ? get_post_meta( $eid, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$event_state = __( 'Recurring Event (Repeated)', 'mage-eventpresscurring' );
		} elseif ( $recurring == 'yes' ) {
			$event_state = __( 'Recurring Event (Selected Dates)', 'mage-eventpresscurring' );
		} else {
			$event_state = '';
		}
		$post_states[] = $event_state;
		$post_states = array_filter( $post_states );

		return $post_states;
	}
	add_action( 'mep_single_before_event_date_list_item', 'mep_re_add_link_to_date_list_item', 10, 2 );
function mep_re_add_link_to_date_list_item( $event_id, $start_datetime ){
	$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
if ( $recurring == 'everyday' || $recurring == 'yes' ){
	?>
    <a href="<?php echo get_the_permalink( $event_id ) . esc_attr( '?date=' . strtotime( $start_datetime ) ); ?>">
		<?php
			}
			}
			add_action( 'mep_single_after_event_date_list_item', 'mep_re_add_link_to_date_list_item_after', 10, 2 );
			function mep_re_add_link_to_date_list_item_after( $event_id, $start_datetime ){
			$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
			if ( $recurring == 'everyday' || $recurring == 'yes' ){
		?>
    </a>
	<?php
}
}
	add_filter( 'mep_check_product_into_cart', 'mep_rq_disable_add_to_cart_if_product_is_in_cart', 90, 2 );
	function mep_rq_disable_add_to_cart_if_product_is_in_cart( $is_purchasable, $product ) {
		return true;
	}
	add_filter( 'mep_settings_general_arr', 'mep_re_gen_settings_item' );
	function mep_re_gen_settings_item( $default_translation ) {
		$gen_settings = array(
			array(
				'name'    => 'mep_auto_select_first_time',
				'label'   => __( 'Auto Select the First Time Slot?', 'mage-eventpress' ),
				'desc'    => __( 'Please select Yes if you want to automatically seelct the first available time slot of the recurring event time list', 'mage-eventpress' ),
				'type'    => 'select',
				// 'type' => 'multicheck',
				'default' => 'yes',
				'options' => array(
					'yes' => 'Yes',
					'no'  => 'No'
				)
			),
		);

		return array_merge( $default_translation, $gen_settings );
	}
// from main file
	add_filter( 'mep_event_total_seat_count', 'mep_update_total_seat_count', 10, 2 );
	function mep_update_total_seat_count( $total, $event_id ) {
		$status = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'normal';
		if ( $status == 'yes' || $status == 'everyday' ) {
			return 100;
		} else {
			return $total;
		}
	}
	add_action( 'mep_after_date_section', 'show_recurring_box' );
	function show_recurring_box( $post_id ) {
		$status                  = get_post_meta( $post_id, 'mep_enable_recurring', true );
		$periods                 = get_post_meta( $post_id, 'mep_repeated_periods', true );
		$mep_show_upcoming_event = get_post_meta( $post_id, 'mep_show_upcoming_event', true );
		?>
        <div class="show_rec_checkbox">
            <label for="mep_normal_event">
                <input id='mep_normal_event' type="radio" name='mep_enable_recurring' value='no' <?php if ( $status == 'no' ) {
					echo 'Checked';
				} ?> /> <?php _e( 'Normal Event', 'mage-eventpresscurring' ); ?>
            </label>
            <label for="mep_everyday_event">
                <input id='mep_everyday_event' type="radio" name='mep_enable_recurring' value='everyday' <?php if ( $status == 'everyday' ) {
					echo 'Checked';
				} ?> /> <?php _e( 'Repeated Event?', 'mage-eventpresscurring' ); ?>
            </label>
            <span id='mep_repeated_periods_sec'>
<label for="mep_repeated_periods">
<?php _e( 'Repeated After ', 'mage-eventpresscurring' ); ?> <input style='width:60px' id='mep_repeated_periods' type="number" name='mep_repeated_periods' value='<?php echo esc_attr( $periods ); ?>'/><?php _e( ' Days', 'mage-eventpress' ); ?> </label>
</span>
            <label for="mep_recurring_event">
                <input id='mep_recurring_event' type="radio" name='mep_enable_recurring' value='yes' <?php if ( $status == 'yes' ) {
					echo 'Checked';
				} ?> /> <?php _e( 'Recurring Event of date listed above?', 'mage-eventpresscurring' ); ?>
            </label>
        </div>
        <div class="show_rec_checkbox" id='show_rec_checkbox'>
            <label for="mep_show_upcoming_event">
                <input id='mep_show_upcoming_event' type="checkbox" name='mep_show_upcoming_event' value='yes' <?php if ( $mep_show_upcoming_event == 'yes' ) {
					echo 'Checked';
				} ?> /> <?php _e( 'Show Only Upcoming Event?', 'mage-eventpresscurring' ); ?>
            </label>
        </div>
        <script>
            jQuery(document).ready(function ($) {

				<?php
				if($status == 'everyday'){
				?>
                jQuery('#mep_repeated_periods_sec').show();
                jQuery('#mp_event_all_info_in_tab [data-tab-item="#mp_event_time"] .wrap.ppof-settings.ppof-metabox').show();
				<?php
				}else{
				?>
                jQuery('#mep_repeated_periods_sec').hide();
                jQuery('#mp_event_all_info_in_tab [data-tab-item="#mp_event_time"] .wrap.ppof-settings.ppof-metabox').hide();
				<?php
				}
				?>

				<?php
				if($status == 'yes'){
				?>
                jQuery('#show_rec_checkbox').show();
				<?php
				}else{
				?>
                jQuery('#show_rec_checkbox').hide();
				<?php
				}
				?>

                jQuery('input[name="mep_enable_recurring"]').click(function () {
                    if (jQuery(this).attr("value") == "everyday") {
                        jQuery('#mep_repeated_periods_sec').show();
                        jQuery('#mp_event_all_info_in_tab [data-tab-item="#mp_event_time"] .wrap.ppof-settings.ppof-metabox').show();
                    } else {
                        jQuery('#mep_repeated_periods_sec').hide();
                        jQuery('#mp_event_all_info_in_tab [data-tab-item="#mp_event_time"] .wrap.ppof-settings.ppof-metabox').hide();
                    }
                });
                jQuery('input[name="mep_enable_recurring"]').click(function () {
                    if (jQuery(this).attr("value") == "yes") {
                        jQuery('#show_rec_checkbox').show();
                    } else {
                        jQuery('#show_rec_checkbox').hide();
                    }
                });
            });
        </script>
		<?php
	}
	add_action( 'save_post', 'mep_recurring_events_meta_save' );
	function mep_recurring_events_meta_save( $post_id ) {
		if ( ! isset( $_POST['mep_event_ticket_type_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['mep_event_ticket_type_nonce'], 'mep_event_ticket_type_nonce' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( get_post_type( $post_id ) == 'mep_events' ) {
			$mep_show_upcoming_event = isset( $_POST['mep_show_upcoming_event'] ) ? sanitize_text_field( $_POST['mep_show_upcoming_event'] ) : '';
			$mep_repeated_periods    = isset( $_POST['mep_repeated_periods'] ) ? sanitize_text_field( $_POST['mep_repeated_periods'] ) : '';
			update_post_meta( $post_id, 'mep_show_upcoming_event', $mep_show_upcoming_event );
			update_post_meta( $post_id, 'mep_repeated_periods', $mep_repeated_periods );
		}
	}
	add_filter( 'mage_event_extra_service_list', 'mep_rq_extra_service_list', 10, 4 );
	function mep_rq_extra_service_list( $content, $event_id, $event_meta, $start_date ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$count      = 1;
			$start_date = wp_date( 'Y-m-d' );
			?>
            <input type="hidden" name='mepre_event_id' id='mep_event_id' value='<?php echo $event_id; ?>'>
            <div id='mep_recurring_extra_service_list'></div>
			<?php
		} elseif ( $recurring == 'yes' ) {
			$event_more_date[0]['event_more_start_date'] = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_more_date[0]['event_more_start_time'] = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_start_time', true ) ) );
			$event_more_date[0]['event_more_end_date']   = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$event_more_date[0]['event_more_end_time']   = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_end_time', true ) ) );
			$event_more_dates                            = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : array();
			$event_multi_date                            = array_merge( $event_more_date, $event_more_dates );
			$mep_available_seat                          = array_key_exists( 'mep_available_seat', $event_meta ) ? $event_meta['mep_available_seat'][0] : 'on';
			?>
            <div id='mep_recurring_extra_service_list'></div>
			<?php
		} else {
			return apply_filters( 'mage_event_extra_service_list_recurring', $content, $event_id, $event_meta, $start_date );
		}
	}
	add_filter( 'mage_event_ticket_type_list', 'multi_date_event_list', 10, 4 );
	function multi_date_event_list( $content, $event_id, $event_meta, $ticket_type_label ) {
		$recurring               = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		$mep_show_upcoming_event = get_post_meta( $event_id, 'mep_show_upcoming_event', true ) ? get_post_meta( $event_id, 'mep_show_upcoming_event', true ) : 'no';
		$mep_event_ticket_type   = get_post_meta( $event_id, 'mep_event_ticket_type', true ) ? get_post_meta( $event_id, 'mep_event_ticket_type', true ) : [];
		if ( $recurring == 'everyday' ) {
			$count      = 1;
			$start_date = wp_date( 'Y-m-d' );
			?>
            <input type="hidden" name='mepre_event_id' id='mep_event_id' value='<?php echo esc_attr( $event_id ); ?>'>
			<?php mep_re_get_everyday_event_date_sec( $event_id ); ?>
            <div id='mep_recutting_ticket_type_list'></div>
			<?php
		} elseif ( $recurring == 'yes' ) {
			$event_more_date[0]['event_more_start_date'] = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_more_date[0]['event_more_start_time'] = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_start_time', true ) ) );
			$event_more_date[0]['event_more_end_date']   = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$event_more_date[0]['event_more_end_time']   = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_end_time', true ) ) );
			$event_more_dates                            = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : array();
			$event_multi_date                            = array_merge( $event_more_date, $event_more_dates );
			$mep_available_seat                          = array_key_exists( 'mep_available_seat', $event_meta ) ? $event_meta['mep_available_seat'][0] : 'on';
			if ( empty( $event_multi_date ) ) {
				return apply_filters( 'mep_event_ticket_type_loop', $content, $event_id );
			}
			$count = 1;
			?>
			<?php echo get_mep_re_recurring_date( $event_id, $event_multi_date, $mep_show_upcoming_event ); ?>
            <input type="hidden" name='mepre_event_id' id='mep_event_id' value='<?php echo esc_attr( $event_id ); ?>'>
            <!-- <h3 class='ex-sec-title'> <?php echo mep_get_label( $event_id, 'mep_event_ticket_type_text', 'Ticket Type For
          ' ); ?></h3> -->
            <div id='mep_recutting_ticket_type_list'></div>
			<?php
		} else {
			return $content;
		}
	}
	if ( ! function_exists( 'mep_re_event_ticket_sold' ) ) {
		function mep_re_event_ticket_sold( $event_id, $date ) {
			$args = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => - 1,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'relation' => 'AND',
						array(
							'key'     => 'ea_event_id',
							'value'   => $event_id,
							'compare' => '='
						),
						array(
							'key'     => 'ea_event_date',
							'value'   => $date,
							'compare' => 'LIKE'
						)
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => 'ea_order_status',
							'value'   => 'processing',
							'compare' => '='
						),
						array(
							'key'     => 'ea_order_status',
							'value'   => 'completed',
							'compare' => '='
						)
					)
				)
			);
			$loop = new WP_Query( $args );

			return $loop->post_count;
		}
	}
	if ( ! function_exists( 'mep_re_event_available_seat' ) ) {
		function mep_re_event_available_seat( $event_id, $date ) {
			$total_seat = mep_event_total_seat( $event_id, 'total' );
			$total_resv = mep_event_total_seat( $event_id, 'resv' );
			$total_sold = mep_re_event_ticket_sold( $event_id, $date );
			$total_left = $total_seat - ( $total_sold + $total_resv );
			$total_left = apply_filters( 'mep_total_ticket_of_type', $total_left, $event_id, '', $date );

			return $total_left;
		}
	}
// Shortcode
	add_shortcode( 'event-list-recurring', 'mep_recurring_event_list' );
	function mep_recurring_event_list( $atts, $content = null ) {
		$defaults   = array(
			"cat"        => "0",
			"org"        => "0",
			"style"      => "grid",
			"column"     => 2,
			"cat-filter" => "no",
			"org-filter" => "no",
			"show"       => "-1",
			"pagination" => "no",
			'sort'       => 'ASC'
		);
		$params     = shortcode_atts( $defaults, $atts );
		$cat        = $params['cat'];
		$org        = $params['org'];
		$style      = $params['style'];
		$column     = $params['column'];
		$cat_f      = $params['cat-filter'];
		$org_f      = $params['org-filter'];
		$show       = $params['show'];
		$pagination = $params['pagination'];
		$sort       = $params['sort'];
		ob_start();
		$content = ob_get_clean();

		return $content;
	}
