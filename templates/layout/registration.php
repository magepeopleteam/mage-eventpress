<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id    = $event_id ?? 0;
	$event_infos                 = $event_infos ?? [];
	$event_infos              = (is_array($event_infos) && sizeof($event_infos)>0) ?$event_infos: MPWEM_Functions::get_all_info( $event_id );
	$all_dates   = array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_times   = array_key_exists( 'all_time', $event_infos ) ? $event_infos['all_time'] : [];
	$date        = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$date        = $date ?? MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	//echo '<pre>';			print_r($event_id);			echo '</pre>';
	ob_start();
	if ( $event_id > 0 ) {
		//  $saved_user_role 	          = get_post_meta($event_id, 'mep_member_only_user_role', true) ? get_post_meta($event_id, 'mep_member_only_user_role', true) : [];
        // echo $event_member_type 	          = get_post_meta($event_id, 'mep_member_only_event', true) ? get_post_meta($event_id, 'mep_member_only_event', true) : 'for_all';

		?>
        <div class="mpwem_style"><?php
		$reg_status 					= array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
		$reg_status_msg_status 	= array_key_exists( 'mep_reg_status_show_msg', $event_infos ) ? $event_infos['mep_reg_status_show_msg'] : 'off';
		$reg_status_msg_txt 		= array_key_exists( 'mep_reg_status_show_msg_txt', $event_infos ) ? $event_infos['mep_reg_status_show_msg_txt'] : '';
		$reg_off_msg 				= $reg_status_msg_status == 'on' ? $reg_status_msg_txt : '';
		if ( $reg_status == 'on' ) {
			if ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) {
				$event_member_type = array_key_exists( 'mep_member_only_event', $event_infos ) ? $event_infos['mep_member_only_event'] : 'for_all';
				$saved_user_role   = array_key_exists( 'mep_member_only_user_role', $event_infos ) ? $event_infos['mep_member_only_user_role'] : [];
				// if ( $event_member_type == 'for_all' || ( is_user_logged_in() && ( array_intersect( wp_get_current_user()->roles, $saved_user_role ) ) || in_array( 'all', $saved_user_role ) ) ) {
				if( $event_member_type == 'for_all' || ($event_member_type != 'for_all'  && is_user_logged_in() && ( in_array(wp_get_current_user()->roles[0],$saved_user_role) || in_array('all',$saved_user_role) ) )){
				 ?>
                    <div class="mpwem_registration_area">
						<?php do_action( 'mpwem_date_select', $event_id, $event_infos); ?>
                        <form action="" method='post' id="mpwem_registration" enctype="multipart/form-data">
							<?php do_action( 'mpwem_registration_content', $event_id, $all_dates, $all_times, $date ); ?>
                        </form>
						<?php do_action( 'mpwem_hidden_content', $event_id ); ?>
                    </div>
					<?php
				}else{
					echo '<div class="mpwem_registration_area_show_msg">';
						MPWEM_Layout::msg( esc_html__( 'This event is available for our members. Please log in to continue. Not a member yet? You can easily join and enjoy full access.', 'mage-eventpress' ) );
					echo '</div>';
				}
			} else {
				// MPWEM_Layout::msg( esc_html__( 'Sorry, this event is expired and no longer available', 'mage-eventpress' ) );
				do_action('mpwem_expired_event_notice_after',$event_id);
			}
		} else {
			echo '<div class="reg_close_msg">'.esc_html($reg_off_msg).'</div>';
		}
		?></div><?php
	}
	echo ob_get_clean();