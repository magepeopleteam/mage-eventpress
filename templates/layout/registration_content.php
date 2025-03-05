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
	$all_times = $all_times ?? MPWEM_Functions::get_times( $event_id, $all_dates );
	$date      = $date ?? MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	//echo '<pre>';			print_r($all_dates);			echo '</pre>';
	ob_start();
	if ( $event_id > 0 ) {
		$reg_status = MP_Global_Function::get_post_info( $event_id, 'mep_reg_status', 'on' );
		//echo '<pre>';			print_r($reg_status);			echo '</pre>';
		if ( $reg_status == 'on' ) {
			$full_location = MPWEM_Functions::get_location( $event_id );
			?>
            <div class="mpwem_booking_panel">
                <input type="hidden" name='mpwem_post_id' value='<?php echo esc_attr( $event_id ); ?>'/>
                <input type="hidden" name='mep_event_start_date[]' value='<?php echo esc_attr( $date ); ?>'/>
                <input type="hidden" name='mep_event_location_cart' value='<?php echo esc_attr( implode( ', ', $full_location ) ); ?>'/>
                <input type="hidden" name='mep_same_attendee' value='<?php echo esc_attr( MP_Global_Function::get_settings( 'general_setting_sec', 'mep_enable_same_attendee', 'no' ) ); ?>'/>
				<?php require apply_filters( 'mpwem_ticket_file', MPWEM_Functions::template_path( 'layout/ticket_type.php' ), $event_id ); ?>
				<?php do_action( 'mpwem_single_attendee', $event_id ); ?>
				<?php require MPWEM_Functions::template_path( 'layout/extra_service.php' ); ?>
            </div>
			<?php
		} else {
			MPWEM_Layout::msg( esc_html__( 'Sorry, this event is  no longer available', 'mage-eventpress' ) );
		}
	}
	echo ob_get_clean();