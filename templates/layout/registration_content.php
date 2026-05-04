<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id  = $event_id ?? 0;
	$all_dates = MPWEM_Functions::get_dates( $event_id );
	$all_times = MPWEM_Functions::get_times( $event_id, $all_dates );

	$event_type     = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
	// $date      = empty( $date ) || $event_type == 'no' ? get_post_meta( $event_id, 'event_start_datetime', true ) : MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
// echo $date;

	$date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	//echo '<pre>';			print_r($all_times);			echo '</pre>';
	//echo '<pre>';			print_r($all_dates);			echo '</pre>'; everyday2026-04-30 12:00 no2026-04-30 11:59:00
	$event_infos              = MPWEM_Functions::get_all_info( $event_id );
	// echo '<pre>';			print_r($event_infos);			echo '</pre>';
	$event_recurring			= array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
    $url_date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : null;
    $url_date_2 = isset( $_GET['date_time'] ) ? sanitize_text_field( wp_unslash( $_GET['date_time'] ) ) : null;
    $url_date=$url_date?:$url_date_2;
    $url_date=$url_date ? date( 'Y-m-d H:i', $url_date ) : '';
    $date_format = MPWEM_Global_Function::check_time_exit_date( $url_date ) ? 'Y-m-d H:i' : 'Y-m-d';
    $url_date    = $url_date ? date( $date_format, strtotime($url_date) ) : '';
    $all_dates   = MPWEM_Functions::get_dates( $event_id );
    $all_times   = MPWEM_Functions::get_times( $event_id, $all_dates, $url_date );
	$upcoming_date            = array_key_exists( 'event_upcoming_datetime', $event_infos ) && $event_recurring == 'no' ? $event_infos['event_start_datetime'] : $event_infos['event_upcoming_datetime'];
    $date                    = $url_date ?: $upcoming_date;
	ob_start();
	if ( $event_id > 0 ) {
		$reg_status = MPWEM_Global_Function::get_post_info( $event_id, 'mep_reg_status', 'on' );
		if ( $reg_status == 'on' ) {
			$full_location = MPWEM_Functions::get_location( $event_id );
			 //$total_available = MPWEM_Functions::get_total_available_seat( $event_id, $date );
			// $total_available = max( $total_available, 0 );
			$total_sold      = mep_ticket_type_sold( $event_id, '', $date );
			$total_ticket    = MPWEM_Functions::get_total_ticket( $event_id, $date );
			$total_reserve   = MPWEM_Functions::get_reserve_ticket( $event_id, $date );
			$total_available = $total_ticket - ( $total_sold + $total_reserve );
			$total_available = max( $total_available, 0 );
			?>
            <div class="mpwem_booking_panel">
                <input type="hidden" name='mpwem_post_id' value='<?php echo esc_attr( $event_id ); ?>'/>
                <input type="hidden" name='mep_event_start_date[]' value='<?php echo esc_attr( $date ); ?>'/>
                <input type="hidden" name='mep_event_location_cart' value='<?php echo esc_attr( implode( ', ', $full_location ) ); ?>'/>
                <input type="hidden" name='mep_same_attendee' value='<?php echo esc_attr( MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_enable_same_attendee', 'no' ) ); ?>'/>
				<?php require apply_filters( 'mpwem_ticket_file', MPWEM_Functions::template_path( 'layout/ticket_type.php' ), $event_id ); ?>
				<?php do_action( 'mpwem_single_attendee', $event_id ); ?>
				<?php require MPWEM_Functions::template_path( 'layout/extra_service.php' ); ?>
                <div class="mpwem_form_submit_area">
					<?php do_action( 'mep_add_term_condition', $event_id ); ?>
					<?php
						if ( $total_available > 0 ) {
							require MPWEM_Functions::template_path( 'layout/add_to_cart.php' );
						}
					?>
                </div>
            </div>
			<?php
		} else {
			MPWEM_Layout::msg( esc_html__( 'Sorry, this event is  no longer available', 'mage-eventpress' ) );
		}
	}
	echo ob_get_clean();