<?php
	// Template Name: Smart Theme
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id           = empty( $event_id ) ? get_the_id() : $event_id;
	$all_dates          = MPWEM_Functions::get_dates( $event_id );
	$all_times          = MPWEM_Functions::get_times( $event_id, $all_dates );
	$upcoming_date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	$speaker_status     = mep_get_option('mep_enable_speaker_list', 'single_event_setting_sec', 'no');

	
?>
<div class="mpStyle mep_smart_theme">
	<?php do_action( 'mpwem_title', $event_id ); ?>
	<?php do_action( 'mpwem_organizer', $event_id ); ?>
    <div class="mpwem_location_time">
		<?php do_action( 'mpwem_location', $event_id ); ?>
		<?php $hide_time = mep_get_option('mep_event_hide_time', 'single_event_setting_sec', 'no');
			if($hide_time=='no'): ?>
			<?php do_action( 'mpwem_time', $event_id, $all_dates, $all_times ); ?>
		<?php endif; ?>
    </div>
    <div class="_mT mpwem_slider_area">
		<?php do_action( 'add_mp_custom_slider', $event_id, 'mep_gallery_images' ); ?>
    </div>
    <div class="mpwem_content_area">
        <div class="mpwem_left_content">
			<?php //if ( get_the_content( $event_id ) ) { ?>
                <div class="mpwem_details">
                    <?php $description_title = mep_get_option('mep_event_hide_description_title', 'single_event_setting_sec', 'no');
					if($description_title=='no'): ?>
						<h2 class="_mB"><?php esc_html_e( 'Event  Description', 'mage-eventpress' ); ?></h2>
					<?php endif; ?>
					<div class="mpwem_details_content"><?php the_content(); ?></div>
                </div>
			<?php //} ?>
			<!-- timeline data display -->
		    <?php do_action('mpwem_timeline'); ?>
			<?php do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date ); ?>
        </div>
        <div class="mpwem_right_content">
			<?php $left_sidebar_title = mep_get_option('mep_event_hide_left_sidebar_title', 'single_event_setting_sec', 'no');
			if($left_sidebar_title=='no'): ?>
				<h2 class="_mB"><?php esc_html_e( 'When and where', 'mage-eventpress' ); ?></h2>
			<?php endif; ?>
            <div class="mpwem_sidebar_content">
				<?php do_action( 'mpwem_date_time', $event_id, $all_dates, $all_times ); ?>
				<?php do_action( 'mpwem_location', $event_id, 'sidebar' ); ?>
				<?php do_action( 'mpwem_social', $event_id ); ?>
				<?php echo mep_add_to_google_calender_link( $event_id ); ?>
            </div>
			<!-- show speaker lists -->
			<?php do_action( 'mep_event_speaker', $event_id ); ?>
        </div>
    </div>
	<?php do_action( 'mpwem_map', $event_id ); ?>
	<?php do_action( 'mpwem_faq', $event_id ); ?>
	<?php do_action( 'mpwem_related', $event_id ); ?>
	<?php do_action( 'mpwem_template_footer', $event_id ); ?>
</div>