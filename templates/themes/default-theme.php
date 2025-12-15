<?php
	// Template Name: Default Theme
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id    = $event_id ?? 0;
	$event_infos = $event_infos ?? MPWEM_Functions::get_all_info( $event_id );
	if ( ! is_array( $event_infos ) ) {
		$event_infos = [];
	}
	$all_dates                 = array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_times                 = array_key_exists( 'all_time', $event_infos ) ? $event_infos['all_time'] : [];
	$upcoming_date             = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$event_type                = array_key_exists( 'mep_event_type', $event_infos ) ? $event_infos['mep_event_type'] : 'offline';
	$mep_enable_recurring      = array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
	$show_google_map_location  = isset($event_infos['mep_sgm']) ? $event_infos['mep_sgm'] : 0;
	$speaker_title             = array_key_exists( 'mep_speaker_title', $event_infos ) ? $event_infos['mep_speaker_title'] : __( "Speaker", "mage-eventpress" );
	$speaker_icon              = array_key_exists( 'mep_event_speaker_icon', $event_infos ) ? $event_infos['mep_event_speaker_icon'] : '';
	$speaker_lists             = array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
	$speaker_lists             = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
	$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_date_list            = array_key_exists( 'mep_event_hide_event_schedule_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_event_schedule_details'] : 'no';
	$hide_location_details     = array_key_exists( 'mep_event_hide_location_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_location_from_details'] : 'no';
	$hide_address_details      = array_key_exists( 'mep_event_hide_address_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_address_from_details'] : 'no';
	$hide_share_details        = array_key_exists( 'mep_event_hide_share_this_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_share_this_details'] : 'no';
	$speaker_status            = array_key_exists( 'mep_enable_speaker_list', $single_event_setting_sec ) ? $single_event_setting_sec['mep_enable_speaker_list'] : 'no';
	$icon_setting_sec     = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
	$icon_setting_sec     = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
	$event_location_icon  = array_key_exists( 'mep_event_location_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_location_icon'] : 'fas fa-map-marker-alt';
	$event_organizer_icon = array_key_exists( 'mep_event_organizer_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_organizer_icon'] : 'far fa-list-alt';
?>
<div class="default_theme">
	<?php do_action( 'mpwem_title', $event_id ); ?>
    <div class="content_area">
        <div class="mep-default-content">
			<?php do_action( 'add_mpwem_custom_slider', $event_id,$event_infos ); ?>
            <div class="date_time_location_short _mT">
				<?php do_action( 'mpwem_date_only', $event_id, $event_infos ); ?>
				<?php do_action( 'mpwem_time_only', $event_id, $event_infos ); ?>
				<?php do_action( 'mpwem_location_only', $event_id, $event_infos ); ?>
            </div>
            <div class="mep-default-feature-content _mT">
                <div class="mpwem_details_content mp_wp_editor"><?php the_content(); ?></div>
				<?php do_action( 'mpwem_timeline', $event_id ); ?>
            </div>
            <div class="mep-default-feature-cart-sec _mT">
				<?php do_action( 'mpwem_registration', $event_id, $event_infos ); ?>
            </div>
	        <?php do_action( 'mpwem_faq', $event_id ); ?>
			<?php 
				
				$location_venue = get_post_meta( $event_id, 'mep_location_venue', true );
				if (! empty( $location_venue ) ):
			?>
			<?php if ( $hide_location_details == 'no' && $show_google_map_location == 1 ) { ?>
				<?php if ( $event_type != 'online' ): ?>
                    <div class="mep-default-map" id="mep-map-location">
                        <div class="map-title">
							<?php echo mep_get_option( 'mep_event_location_text', 'label_setting_sec', __( 'Event Location', 'mage-eventpress' ) ); ?>
                        </div>
                        <div class="display-map">
							<?php do_action( 'mep_event_map', $event_id ); ?>
                        </div>
                    </div>
				<?php endif; ?>
			<?php } ?>
			<?php endif; ?>
			<?php do_action( 'mpwem_template_footer', $event_id ); ?>
        </div>
        <div class="mep-default-sidebar">
            <div class="df-sidebar-part">
	            <?php do_action( 'mpwem_organizer', $event_id ,$event_infos); ?>
	            <?php do_action( 'mpwem_seat_status', $event_id, $event_infos ); ?>
				<?php if ( sizeof( $all_dates ) > 0 && $hide_date_list == 'no' ) { ?>
                    <div class="event_date_list_area">
                        <h5 class="_mB_xs"><?php esc_html_e( 'Event Schedule Details', 'mage-eventpress' ) ?></h5>
						<?php do_action( 'mpwem_date_list', $event_id, $event_infos ); ?>
                    </div>
				<?php } ?>
				<?php if ( $hide_address_details == 'no' ): ?>
                    <div class="mep-default-sidebar-address">
						<?php do_action( 'mep_event_address_list_sidebar', $event_id ); ?>
                    </div>
				<?php endif; ?>

				<?php if ( has_term( '', 'mep_tag', $event_id ) ): ?>
                    <div class="mep-default-sidebar-tags">
						<?php do_action( 'mep_event_tags', $event_id ); ?>
                    </div>
				<?php endif; ?>

				<?php if ( $hide_share_details == 'no' ) { ?>
                    <div class="mep-default-sidrbar-social">
						<?php do_action( 'mep_event_social_share', $event_id ); ?>
                    </div>
				<?php } ?>
				<?php if ( $speaker_status == 'yes' && sizeof( $speaker_lists ) > 0 ) { ?>
                    <div class="event_speaker_list_area">
                        <h5><span class="<?php echo esc_attr( $speaker_icon ); ?> _mR_xs"></span><?php echo esc_html( $speaker_title ); ?></h5>
						<?php do_action( 'mpwem_speaker', $event_id, $event_infos ); ?>
                    </div>
				<?php } ?>
				<?php do_action( 'mpwem_add_calender', $event_id, $all_dates, $upcoming_date ); ?>
				<?php dynamic_sidebar( 'mep_default_sidebar' );
				?>
            </div>
        </div>
    </div>
</div>