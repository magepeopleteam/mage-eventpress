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
            <i class="far fa-calendar"></i>
            <div class="date_time_box">
				<h2><?php esc_html_e( 'Date & Time', 'mage-eventpress' ); ?></h2>
				<div class="date_time">
					<?php if ( $date_type == 'no' || $date_type == 'yes' ) {
					} else {
						foreach ( $all_dates as $key => $dates ) {
							$all_times = MPWEM_Functions::get_times( $event_id, $all_dates, $dates );
							if ( $key > 0 ) { ?>
								<div class="_divider_xs"></div>
							<?php } ?>
							<p><?php echo esc_html( MP_Global_Function::date_format( $dates ) ); ?></p>
							<?php if ( sizeof( $all_times ) > 0 ) {
								foreach ( $all_times as $time ) {
									$start_time = array_key_exists( 'start', $time ) ? $time['start']['time'] : '';
									?>
									<p><?php echo esc_html( $time['start']['label']). ' :  '.esc_html( MP_Global_Function::date_format( $start_time, 'time' )); ?></p>
								<?php }
							}
						} ?>
					<?php } ?>
				</div>
                <button>
                    <i class="far fa-calendar"></i>
                    <?php _e('Add To Calender','mage-eventpress');?>
                </button>
            </div>
        </div>
		<?php
	}