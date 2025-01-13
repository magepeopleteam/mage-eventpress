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
            <div class="date-lists">
                <h2><?php esc_html_e( 'Date & Time', 'mage-eventpress' ); ?></h2>
				<?php do_action('mep_event_date_default_theme',$event_id,''); ?>
				<?php  echo mep_add_to_google_calender_link( $event_id ); ?>
            </div>
			
		</div>
		
		<?php
	}