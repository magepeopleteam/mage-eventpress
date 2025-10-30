<?php
	// Template Name: Smart Theme
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id                 = $event_id ?? 0;
	$event_infos              = $event_infos ?? MPWEM_Functions::get_all_info( $event_id );
	$all_dates                = array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_times                = array_key_exists( 'all_time', $event_infos ) ? $event_infos['all_time'] : [];
	$upcoming_date            = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec = is_array($_single_event_setting_sec) && !empty($_single_event_setting_sec) ? $_single_event_setting_sec : [];
	$hide_date_list           = array_key_exists( 'mep_event_hide_event_schedule_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_event_schedule_details'] : 'no';
	$speaker_status           = array_key_exists( 'mep_enable_speaker_list', $single_event_setting_sec ) ? $single_event_setting_sec['mep_enable_speaker_list'] : 'no';
	$hide_time                = array_key_exists( 'mep_event_hide_time', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_time'] : 'no';
	$description_title        = array_key_exists( 'mep_event_hide_description_title', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_description_title'] : 'no';
	$left_sidebar_title       = array_key_exists( 'mep_event_hide_left_sidebar_title', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_left_sidebar_title'] : 'no';
	$icon_setting_sec         = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
	$icon_setting_sec 		  = empty($icon_setting_sec) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
	$event_location_icon      = array_key_exists( 'mep_event_location_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_location_icon'] : 'fas fa-map-marker-alt';
?>
<div class="default_theme mep_smart_theme">
	<?php do_action( 'mpwem_title', $event_id ); ?>
	<?php do_action( 'mpwem_organizer', $event_id ); ?>
    <div class="mpwem_location_time">
		<?php do_action( 'mpwem_location', $event_id ); ?>
		<?php
			if ( $hide_time == 'no' ): ?>
				<?php do_action( 'mpwem_time', $event_id, $all_dates, $all_times ); ?>
			<?php endif; ?>
    </div>
	<?php do_action( 'add_mpwem_custom_slider', $event_id, 'mep_gallery_images' ); ?>
    <div class="mpwem_content_area">
        <div class="mpwem_left_content">
            <div class="mpwem_details">
				<?php if ( $description_title == 'no' ): ?>
                    <h2 class="_mB"><?php esc_html_e( 'Event  Description', 'mage-eventpress' ); ?></h2>
				<?php endif; ?>
                <div class="mpwem_details_content mp_wp_editor"><?php the_content(); ?></div>
            </div>
			<?php do_action( 'mpwem_timeline', $event_id ); ?>
			<?php do_action( 'mpwem_registration', $event_id, $event_infos ); ?>
			<?php do_action( 'mpwem_faq', $event_id ); ?>
        </div>
        <div class="mpwem_right_content">
			<?php if ( $left_sidebar_title == 'no' ): ?>
                <h2 class="_mB"><?php esc_html_e( 'When and where', 'mage-eventpress' ); ?></h2>
			<?php endif; ?>
            <div class="mpwem_sidebar_content">
				<?php if ( sizeof( $all_dates ) > 0 && $hide_date_list == 'no' ) { ?>
                    <div class="event_date_list_area">
                        <h5 class="_mB_xs"><span class="<?php echo esc_attr( $event_location_icon ); ?> _mR_xs"></span><?php esc_html_e( 'Event Schedule Details', 'mage-eventpress' ) ?></h5>
						<?php do_action( 'mpwem_date_list', $event_id, $event_infos ); ?>
                    </div>
				<?php } ?>
				<?php do_action( 'mpwem_location', $event_id, 'sidebar' ); ?>
				<?php if ( has_term( '', 'mep_tag', $event_id ) ): ?>
                    <div class="mep-default-sidebar-tags">
						<?php do_action( 'mep_event_tags', $event_id ); ?>
                    </div>
				<?php endif; ?>
				<?php do_action( 'mpwem_social', $event_id ); ?>
				<?php do_action( 'mpwem_add_calender', $event_id, $all_dates, $upcoming_date ); ?>
            </div>
            <!-- show speaker lists -->
			<?php if ( $speaker_status == 'yes' ): ?>
                <div class="mep-default-sidebar-speaker-list">
					<?php do_action( 'mep_event_speaker', $event_id ); ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
	<?php do_action( 'mpwem_map', $event_id ); ?>
	<?php do_action( 'mpwem_related', $event_id ); ?>
	<?php do_action( 'mpwem_template_footer', $event_id ); ?>
</div>