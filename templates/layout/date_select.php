<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id    = $event_id ?? 0;
	$event_infos = $event_infos ?? [];
	$event_infos = (is_array( $event_infos ) && sizeof( $event_infos ) > 0) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$all_dates   = array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_times   = array_key_exists( 'all_time', $event_infos ) ? $event_infos['all_time'] : [];
	$date        = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$date        = $date ?: MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	if ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) {
		?>
        <div class="date-time-header">
            <div class="ticket-title"><?php esc_html_e( 'Ticket Options', 'mage-eventpress' ); ?></div>
			<?php
				$date_type = array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
				$date_type=$date_type?:'no';
				if ( $date_type == 'no' || $date_type == 'yes' ) {
					$date        = ! empty( $date ) ? $date : current( $all_dates )['time'];
					$date_format = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : 'date';
					if ( is_array( $all_dates ) && sizeof( $all_dates ) == 1 ) {
						?>
                        <input type="hidden" id="mpwem_date_time" name='mpwem_date_time' value='<?php echo esc_attr( $date ); ?>'/>
					<?php } else { ?>
                        <div class="date-time-area">
                            <label>
                                <span><?php esc_html_e( 'Select Date', 'mage-eventpress' ); ?></span>
                                <i class="far fa-calendar"></i>
                                <select class="formControl" name="mpwem_date_time" id="mpwem_date_time">
									<?php foreach ( $all_dates as $dates ) { ?>
                                        <option value="<?php echo esc_attr( $dates['time'] ); ?>" <?php echo esc_attr( strtotime( $date ) == strtotime( $dates['time'] ) ? 'selected' : '' ); ?>><?php echo esc_html( MPWEM_Global_Function::date_format( $dates['time'], $date_format ) ); ?></option>
									<?php } ?>
                                </select>
                            </label>
                        </div>
						<?php
					}
				} else {
					$date         = $date ?: current( $all_dates );
					$date_format  = MPWEM_Global_Function::date_picker_format();
					$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
					$hidden_date  = $date ? date( 'Y-m-d', strtotime( $date ) ) : '';
					$visible_date = $date ? date_i18n( $date_format, strtotime( $date ) ) : '';
					$all_times    = $all_times && is_array($all_times) && sizeof($all_times)>0 ?$all_times: MPWEM_Functions::get_times( $event_id, $all_dates, $date );
					$display_time = array_key_exists( 'mep_disable_ticket_time', $event_infos ) ? $event_infos['mep_disable_ticket_time'] : 'no';
					?>
                    <div class="date-time-area">
                        <label>
                            <span><?php esc_html_e( 'Select date', 'mage-eventpress' ); ?></span>
                            <i class="far fa-calendar"></i>
                            <input type="hidden" name="mpwem_date_time" value="<?php echo esc_attr( $hidden_date ); ?>" required/>
                            <input id="mpwem_date_time" type="text" value="<?php echo esc_attr( $visible_date ); ?>" class="formControl " placeholder="<?php echo esc_attr( $now ); ?>" readonly required/>
                        </label>
						<?php if ( $display_time != 'no' ) { ?>
                            <div class="mpwem_time_area">
                                <label>
                                    <span><?php esc_html_e( 'Select Time', 'mage-eventpress' ); ?></span>
                                    <i class="far fa-clock"></i>
                                    <select class="formControl" name="mpwem_time" id="mpwem_time">
										<?php foreach ( $all_times as $times ) { ?>
											<?php $current_date = $hidden_date . ' ' . $times['start']['time']; ?>
                                            <option value="<?php echo esc_attr( $current_date ); ?>" <?php echo esc_attr( strtotime( $date ) == strtotime( $current_date ) ? 'selected' : '' ); ?>><?php echo esc_html( $times['start']['label'] ?: $times['start']['time'] ); ?></option>
										<?php } ?>
                                    </select>
                                </label>
                            </div>
						<?php } ?>
                    </div>
					<?php
					do_action( 'mpwem_load_date_picker_js', '#mpwem_date_time', $all_dates );
				}
			?>
        </div>
		<?php
	}
