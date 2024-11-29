<?php
	// Template Name: Smart Theme
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id           = empty( $event_id ) ? get_the_id() : $event_id;
	$all_dates          = MPWEM_Functions::get_dates( $event_id );
	$all_times          = MPWEM_Functions::get_times( $event_id, $all_dates );
	$upcoming_date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	$hide_share_details = mep_get_option( 'mep_event_hide_share_this_details', 'single_event_setting_sec', 'no' );
	// echo $event_id;
	//echo '<pre>';print_r($upcoming_date);echo '</pre>';
	//echo '<pre>';print_r($all_dates);echo '</pre>';
?>
<div class="mpStyle mep_smart_theme">
	<?php do_action( 'mpwem_title', $event_id ); ?>
	<?php do_action( 'mpwem_organizer', $event_id ); ?>
    <div class="_dFlex">
		<?php do_action( 'mpwem_location', $event_id ); ?>
		<?php do_action( 'mpwem_time', $event_id, $all_dates, $all_times ); ?>
    </div>
	<?php do_action( 'add_mp_custom_slider', $event_id, 'mep_gallery_images' ); ?>
    <div class="_dFlex _mT_40">
        <div class="mainSection">
			<?php if ( get_the_content( $event_id ) ) { ?>
                <div class="mpwem_details">
                    <h4><?php esc_html_e( 'Event  Description', 'mage-eventpress' ); ?></h4>
					<?php the_content(); ?>
                </div>
			<?php } ?>
			<?php do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date ); ?>
        </div>
        <div class="rightSidebar">
            <h4><?php esc_html_e( 'When and where', 'mage-eventpress' ); ?></h4>
			<?php
				if ( $hide_share_details == 'no' ) {
					do_action( 'mep_event_add_calender', $event_id );
					do_action( 'mep_event_social_share', $event_id );
				}
			?>
        </div>
    </div>
</div>