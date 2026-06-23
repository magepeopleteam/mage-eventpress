<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? [];
	$event_infos               = (is_array( $event_infos ) && sizeof( $event_infos ) > 0) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$all_dates                 = is_array($event_infos) && array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_dates                 = (is_array( $all_dates ) && sizeof( $all_dates ) > 0) ? $all_dates : MPWEM_Functions::get_dates( $event_id );
	$upcoming_date             = is_array($event_infos) && array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$mep_show_end_datetime     = is_array($event_infos) && array_key_exists( 'mep_show_end_datetime', $event_infos ) ? $event_infos['mep_show_end_datetime'] : 'yes';
	$_single_event_setting_sec = is_array($event_infos) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_date_list            = is_array($single_event_setting_sec) && array_key_exists( 'mep_event_hide_event_schedule_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_event_schedule_details'] : 'no';
	$date_count                = 0;
	if ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 && $hide_date_list == 'no' ) { ?>
        <div class="date_list_area">
			<?php
				$date_type = is_array($event_infos) && array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
				if ( $date_type == 'no' || $date_type == 'yes' ) {
					$date        = ! empty( $date ) ? $date : current( $all_dates )['time'];
					$date_format = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : 'date';
					foreach ( $all_dates as $dates ) {
						$start_time = is_array($dates) && array_key_exists( 'time', $dates ) ? $dates['time'] : '';
						$end_time   = is_array($dates) && array_key_exists( 'end', $dates ) ? $dates['end'] : '';
						if ( $start_time ) {
							$event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $start_time ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
							?>
                            <div class="_layout_info_xs date-list-item" <?php if ( $date_count > 4 ) { ?>data-collapse="#mpwem_more_date"<?php } ?>>
                                <div class="date_item">
									<?php if ( $end_time && $mep_show_end_datetime == 'yes' ) {
										if ( strtotime( gmdate( 'Y-m-d', strtotime( $start_time ) ) ) == strtotime( gmdate( 'Y-m-d', strtotime( $end_time ) ) ) ) { ?>
                                            <a class="" href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $start_time, $date_format ) . ' - ' . MPWEM_Global_Function::date_format( $end_time, 'time' ) ); ?></a>
										<?php } else { ?>
                                            <a class="" href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $start_time, $date_format ) ).'-'.esc_html( MPWEM_Global_Function::date_format( $end_time, $date_format )); ?></a>
											<?php
										}
									} else { ?>
                                        <a class="" href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $start_time, $date_format ) ); ?></a>
									<?php } ?>
                                </div>
                            </div>
							<?php
							$date_count ++;
						}
					}
				} else {
					$only_upcoming_date = date( 'Y-m-d', strtotime( $upcoming_date ) );
					foreach ( $all_dates as $date ) {
						$all_times = MPWEM_Functions::get_times( $event_id, $all_dates, $date );
						$event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $date ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
						?>
                        <div class="_layout_info_xs date-list-item" <?php if ( $date_count > 4 ) { ?>data-collapse="#mpwem_more_date"<?php } ?>>
                            <div class="date_item">
                                <?php
						if ( is_array( $all_times ) && sizeof( $all_times )>0 ) {
                            if(is_array($all_times) && sizeof($all_times)==1){
	                            foreach ( $all_times as $times ) {
		                            $time_info = is_array($times) && array_key_exists( 'start', $times ) ? $times['start'] : [];
		                            if ( is_array( $time_info ) && sizeof( $time_info ) > 0 ) {
			                            $label = is_array($time_info) && array_key_exists( 'label', $time_info ) ? $time_info['label'] : '';
			                            $time  = is_array($time_info) && array_key_exists( 'time', $time_info ) ? $time_info['time'] : '';
			                            if ( $time ) {
				                            $full_date = $date . ' ' . $time;
				                            $time      = MPWEM_Global_Function::date_format( $full_date, 'time' );
				                            $event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $full_date ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
				                            ?>
                                            <a class="_fw_500 " href="<?php echo esc_url( $event_url ); ?>"><?php echo get_mep_datetime( $full_date, 'date-time-text' ) ; ?></a>
				                            <?php
			                            }
		                            }
	                            }
                            }else{
                                $fist_time=current($all_times);
	                            $time_info = is_array($fist_time) && array_key_exists( 'start', $fist_time ) ? $fist_time['start'] : [];
	                            if ( is_array( $time_info ) && sizeof( $time_info ) > 0 ) {
		                            $label = is_array($time_info) && array_key_exists( 'label', $time_info ) ? $time_info['label'] : '';
		                            $time  = is_array($time_info) && array_key_exists( 'time', $time_info ) ? $time_info['time'] : '';
		                            if ( $time ) {
			                            $full_date = $date . ' ' . $time;
			                            $time      = MPWEM_Global_Function::date_format( $full_date, 'time' );
			                            $event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $full_date ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
			                            ?>
                                        <a class="_fw_500 " href="<?php echo esc_url( $event_url ); ?>"><?php echo get_mep_datetime( $full_date, 'date' ) ; ?></a>
			                            <?php
		                            }
	                            }
	                            if ( is_array( $all_times ) && sizeof( $all_times ) ) {
		                            foreach ( $all_times as $times ) {
			                            $time_info = is_array($times) && array_key_exists( 'start', $times ) ? $times['start'] : [];
			                            if ( is_array( $time_info ) && sizeof( $time_info ) > 0 ) {
				                            $label = is_array($time_info) && array_key_exists( 'label', $time_info ) ? $time_info['label'] : '';
				                            $time  = is_array($time_info) && array_key_exists( 'time', $time_info ) ? $time_info['time'] : '';
				                            if ( $time ) {
					                            $full_date = $date . ' ' . $time;
					                            $time      = MPWEM_Global_Function::date_format( $full_date, 'time' );
					                            $event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $full_date ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
					                            ?>
                                                <a class="_ml " href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( $label ? $label . '(' . $time . ')' : $time ) ?></a>
					                            <?php
				                            }
			                            }
		                            }
	                            }
                            }
                        }else{
                            ?>
                            <a class="_fw_500" href="<?php echo esc_url( $event_url ); ?>"><?php echo get_mep_datetime( $date, 'date' ); ?></a>
                            <?php
                        }
                                ?>

								<?php  ?>
                            </div>
                        </div>
						<?php
						$date_count ++;
					}
				}
			?>
        </div>
		<?php if ( $date_count > 4 ) { ?>
            <button type="button" class="_button_theme_margin_auto" data-collapse-target="#mpwem_more_date" data-open-text="<?php esc_attr_e( 'Hide Date Lists', 'mage-eventpress' ); ?>" data-close-text="<?php esc_attr_e( 'View More Date', 'mage-eventpress' ); ?>"><span data-text><?php esc_html_e( 'View More Date', 'mage-eventpress' ); ?></span></button>
		<?php } ?>
		<?php
	}