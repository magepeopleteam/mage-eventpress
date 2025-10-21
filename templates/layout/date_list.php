<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id       = $event_id ?? 0;
	$all_dates      = $all_dates ?? [];
	$all_dates      = sizeof( $all_dates ) > 0 ? $all_dates : MPWEM_Functions::get_dates( $event_id );
	$hide_date_list = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_hide_event_schedule_details', 'no' );
	$date_count     = 0;
	if ( sizeof( $all_dates ) > 0 && $hide_date_list == 'no' ) { ?>
            <div class="date_list_area">
				<?php
					$date_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
					if ( $date_type == 'no' || $date_type == 'yes' ) {
						$date        = ! empty( $date ) ? $date : current( $all_dates )['time'];
						$date_format = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : 'date';
						foreach ( $all_dates as $dates ) {
							$start_time = array_key_exists( 'time', $dates ) ? $dates['time'] : '';
							$end_time   = array_key_exists( 'end', $dates ) ? $dates['end'] : '';
							if ( $start_time ) {
								$event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $start_time ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
								?>
                                <div class="_infoLayout_xs" <?php if ( $date_count > 4 ) { ?>data-collapse="#mpwem_more_date"<?php } ?>>
                                    <div class="date_item">
                                        <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $start_time, $date_format ) ); ?></a>
										<?php if ( $end_time ) { ?>
                                            <p>-</p>
                                            <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $end_time, $date_format ) ); ?></a>
										<?php } ?>
                                    </div>
                                </div>
								<?php
								$date_count ++;
							}
						}
					} else {
						foreach ( $all_dates as $date ) {
							$all_times = MPWEM_Functions::get_times( $event_id, $all_dates, $date );
							$event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $date ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
							?>
                            <div class="_infoLayout_xs" <?php if ( $date_count > 4 ) { ?>data-collapse="#mpwem_more_date"<?php } ?>>
                                <div class="date_item">
                                    <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $date ) ); ?></a>
									<?php if ( sizeof( $all_times ) ) {
										foreach ( $all_times as $times ) {
											$time_info = array_key_exists( 'start', $times ) ? $times['start'] : [];
											if ( sizeof( $time_info ) > 0 ) {
												$label = array_key_exists( 'label', $time_info ) ? $time_info['label'] : '';
												$time  = array_key_exists( 'time', $time_info ) ? $time_info['time'] : '';
												if ( $time ) {
													$full_date = $date . ' ' . $time;
													$time      = MPWEM_Global_Function::date_format( $full_date, 'time' )
													?>
                                                    <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( $label ? $label . '(' . $time . ')' : $time ) ?></a>
													<?php
												}
											}
										}
									} ?>
                                </div>
                            </div>
							<?php
							$date_count ++;
						}
					}
				?>
            </div>
			<?php if ( $date_count > 4 ) { ?>
                <button type="button" class="_themeButton_mAuto" data-collapse-target="#mpwem_more_date" data-open-text="<?php esc_attr_e( 'Hide Date Lists', 'mage-eventpress' ); ?>" data-close-text="<?php esc_attr_e( 'View More Date', 'mage-eventpress' ); ?>"><span data-text><?php esc_html_e( 'View More Date', 'mage-eventpress' ); ?></span></button>
			<?php } ?>
		<?php
	}