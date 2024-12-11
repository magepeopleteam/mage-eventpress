<?php
	// Template Name: Smart Theme
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id           = empty( $event_id ) ? get_the_id() : $event_id;
	$all_dates          = MPWEM_Functions::get_dates( $event_id );
	$all_times          = MPWEM_Functions::get_times( $event_id, $all_dates );
	$upcoming_date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	$hide_share_details = mep_get_option( 'mep_event_hide_share_this_details', 'single_event_setting_sec', 'no' );
	//echo '<pre>';print_r($all_dates);echo '</pre>';
?>
<div class="mpStyle mep_smart_theme">
	<?php do_action( 'mpwem_title', $event_id ); ?>
	<?php do_action( 'mpwem_organizer', $event_id ); ?>
    <div class="mpwem_location_time">
		<?php do_action( 'mpwem_location', $event_id ); ?>
		<?php do_action( 'mpwem_time', $event_id, $all_dates, $all_times ); ?>
    </div>
    <div class="_mT mpwem_slider_area">
		<?php do_action( 'add_mp_custom_slider', $event_id, 'mep_gallery_images' ); ?>
    </div>
    <div class="mpwem_content_area">
        <div class="mpwem_left_content">
			<?php if ( get_the_content( $event_id ) ) { ?>
                <div class="mpwem_details">
                    <h2 class="_mB"><?php esc_html_e( 'Event  Description', 'mage-eventpress' ); ?></h2>
                    <div class="mpwem_details_content"><?php the_content(); ?></div>
                </div>
			<?php } ?>
			<?php do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date ); ?>
        </div>
        <div class="mpwem_right_content">
			<h2 class="_mB"><?php esc_html_e( 'When and where', 'mage-eventpress' ); ?></h2>
			
			<div class="mpwem_sidebar_content">
				<div class="date_widgets">
					<i class="fa fa-calendar"></i>
					<div>
						<h2>Date & Time</h2>
						<p>Sunday, March 12, 2023</p>
						<p>7:45 PM – 9:15 PM EDT</p>
						<button>
							<i class="fa fa-calendar"></i>
							Add To Calender
						</button>
					</div>
				</div>
				<div class="location_widgets">
					<i class="fa fa-map-marker"></i>
					<div>
						<h2>Location</h2>
						<p>110 Delancey Street, New York, NY 10002, United States. </p>
						<button>
							<i class="fa fa-map-marker"></i>
							Find In Map
						</button>
					</div>
				</div>
				<div class="share_widgets">
					<h2>Share This Event</h2>
					<?php
						if ( $hide_share_details == 'no' ) {
							do_action( 'mep_event_social_share', $event_id );
						}
					?>
				</div>
			</div>
        </div>
    </div>
</div>