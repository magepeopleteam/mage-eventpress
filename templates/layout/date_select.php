<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id  = $event_id ?? 0;
	$all_dates = $all_dates ?? [];
	$date      = $date ?? '';
	if ( sizeof( $all_dates ) > 0 ) {
		$date_type = MP_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
		if ( $date_type == 'no' || $date_type == 'yes' ) {
			$date        = $date ?? current( $all_dates )['time'];
			$date_format = MP_Global_Function::check_time_exit_date( $date ) ? 'full' : '';
			if ( sizeof( $all_dates ) == 1 ) {
				?>
                <input type="hidden" id="mpwem_date_time" name='mpwem_date_time' value='<?php echo esc_attr( $date ); ?>'/>
			<?php } else { ?>
                <div class="date-time-area">
                    <label>
                        <span><?php esc_html_e( 'Select Date', 'mage-eventpress' ); ?></span>
                        <i class="far fa-calendar"></i>
						<select class="formControl" name="mpwem_date_time" id="mpwem_date_time">
							<?php foreach ( $all_dates as $dates ) { ?>
                                <option value="<?php echo esc_attr( $dates['time'] ); ?>" <?php echo esc_attr( strtotime( $date ) == strtotime( $dates['time'] ) ? 'selected' : '' ); ?>><?php echo esc_html( MP_Global_Function::date_format( $dates['time'], $date_format ) ); ?></option>
							<?php } ?>
                        </select>
                    </label>
                </div>
				<?php
			}
		} else {
			$date         = $date ?: current( $all_dates );
			$date_format  = MP_Global_Function::date_picker_format();
			$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
			$hidden_date  = $date ? date( 'Y-m-d', strtotime( $date ) ) : '';
			$visible_date = $date ? date_i18n( $date_format, strtotime( $date ) ) : '';
			$all_times    = $all_times ?? MPWEM_Functions::get_times( $event_id, $all_dates, $date );
			$display_time = get_post_meta($event_id,'mep_disable_ticket_time',true);
			$display_time = $display_time?$display_time:'no';
			?>
            <div class="date-time-area">
                <label>
                    <span><?php esc_html_e( 'Select date', 'mage-eventpress' ); ?></span>
					<i class="far fa-calendar"></i>
                    <input type="hidden" name="mpwem_date_time" value="<?php echo esc_attr( $hidden_date ); ?>" required/>
                    <input id="mpwem_date_time" type="text" value="<?php echo esc_attr( $visible_date ); ?>" class="formControl " placeholder="<?php echo esc_attr( $now ); ?>" readonly required/>
                </label>
				<?php 
				if ($display_time!='no') { ?>
                    <div class="mpwem_time_area">
                        <label>
                            <span><?php esc_html_e( 'Select Time', 'mage-eventpress' ); ?></span>
                            <i class="far fa-clock"></i>
							<select class="formControl" name="mpwem_time" id="mpwem_time">
								<?php foreach ( $all_times as $times ) { ?>
                                    <option value="<?php echo esc_attr( $hidden_date . ' ' . $times['start']['time'] ); ?>"><?php echo esc_html( $times['start']['label'] ? $times['start']['label'] : $times['start']['time'] ); ?></option>
								<?php } ?>
                            </select>
                        </label>
                    </div>
				<?php } ?>
            </div>
			<?php
			do_action( 'mp_load_date_picker_js', '#mpwem_date_time', $all_dates );
			//echo '<pre>';			print_r($all_times);			echo '</pre>';
		}
	}
