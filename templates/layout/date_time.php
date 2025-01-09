<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id  = $event_id ?? 0;
	$all_dates = $all_dates ?? MPWEM_Functions::get_dates( $event_id );
	//echo '<pre>';	print_r( $all_dates );	echo '</pre>';
	if ( sizeof( $all_dates ) > 0 ) {
		$date_type = MP_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
		?>
        <div class="date_widgets">
            <i class="fa fa-calendar"></i>
            <div>
                <h2><?php esc_html_e( 'Date & Time', 'mage-eventpress' ); ?></h2>
				<?php if ( $date_type == 'no' || $date_type == 'yes' ) {
					foreach ( $all_dates as $key => $dates ) {
						$start_date        = $dates['time'];
						$start_date_format = MP_Global_Function::check_time_exit_date( $start_date ) ? 'full' : 'date';
						$end_date          = $dates['end'];
						$end_date_format   = MP_Global_Function::check_time_exit_date( $end_date ) ? 'full' : 'date';
						if ( $key > 0 ) { ?>
                            <div class="_divider_xs"></div>
						<?php } ?>
                        <p><?php echo esc_html( MP_Global_Function::date_format( $start_date, $start_date_format ) . ' - ' . MP_Global_Function::date_format( $end_date, $end_date_format ) ); ?></p><?php
					}
				} else {
					$key = 0;
					foreach ( $all_dates as $key => $dates ) {
						$all_times = MPWEM_Functions::get_times( $event_id, $all_dates, $dates );
						if ( $key == 4 ) { ?>
                            <div data-collapse="#mpwem_load_more_date">
						<?php }
						if ( $key > 0 ) { ?>
                            <div class="_divider_xs"></div>
						<?php } ?>
                        <p><?php echo esc_html( MP_Global_Function::date_format( $dates ) ); ?></p>
						<?php if ( sizeof( $all_times ) > 0 ) {
							foreach ( $all_times as $time ) {
								$start_time = array_key_exists( 'start', $time ) ? $time['start']['time'] : '';
								?><p><?php echo esc_html( $time['start']['label'] ) . ' :  ' . esc_html( MP_Global_Function::date_format( $start_time, 'time' ) ); ?></p><?php
							}
						}
					}
					if ( $key > 3 ) {
						?>
                        </div>
                        <div data-collapse-target="#mpwem_load_more_date" data-read data-open-text="<?php esc_attr_e( 'View More dates', 'mage-eventpress' ); ?>" data-close-text="<?php esc_attr_e( 'Less More dates', 'mage-eventpress' ); ?>">
                            <span data-text><?php esc_html_e( 'View More dates', 'mage-eventpress' ); ?></span>
                        </div>
					<?php } ?>
				<?php } ?>
				<?php echo mep_add_to_google_calender_link( $event_id ); ?>
            </div>
        </div>
		<?php
	}