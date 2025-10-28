<?php
	// Template Name: Virtual Event
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id                 = $event_id ?? 0;
	$event_infos              = $event_infos ?? MPWEM_Functions::get_all_info( $event_id );
	$all_dates                = array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_times                = array_key_exists( 'all_time', $event_infos ) ? $event_infos['all_time'] : [];
	$upcoming_date            = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$speakers_id              = array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
	$single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$hide_date_list           = array_key_exists( 'mep_event_hide_event_schedule_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_event_schedule_details'] : 'no';
	$hide_total_seat_details  = array_key_exists( 'mep_event_hide_total_seat_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_total_seat_from_details'] : 'no';
	$hide_org_by_details      = array_key_exists( 'mep_event_hide_org_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_org_from_details'] : 'no';
	$hide_share_details       = array_key_exists( 'mep_event_hide_share_this_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_share_this_details'] : 'no';
	$speaker_status           = array_key_exists( 'mep_enable_speaker_list', $single_event_setting_sec ) ? $single_event_setting_sec['mep_enable_speaker_list'] : 'no';
	//echo '<pre>';print_r( $event_infos );echo '</pre>';
?>
<div class="default_theme virtual_theme">
	<?php do_action( 'mpwem_title', $event_id ); ?>
    <div class="content_area">
        <div class="mep-default-content">
			<?php do_action( 'add_mpwem_custom_slider', $event_id, 'mep_gallery_images' ); ?>
            <div class="mep-default-feature-content">
                <div class="mpwem_details_content mp_wp_editor"><?php the_content(); ?></div>
				<?php do_action( 'mpwem_timeline', $event_id ); ?>
            </div>
            <div class="mep-default-feature-cart-sec">
				<?php do_action( 'mpwem_registration', $event_id, $event_infos ); ?>
            </div>
            <div class="mep-default-feature-faq-sec">
				<?php do_action( 'mep_event_faq', $event_id ); ?>
            </div>
			<?php do_action( 'mpwem_template_footer', $event_id ); ?>
        </div>
        <div class="mep-default-sidebar">
            <div class="df-sidebar-part">
				<?php if ( $hide_org_by_details == 'no' && has_term( '', 'mep_org', $event_id ) ) : ?>
                    <div class="mep-default-sidrbar-meta">
						<?php do_action( 'mep_event_organized_by', $event_id ); ?>
                    </div>
				<?php endif; ?>

				<?php if ( $hide_total_seat_details == 'no' ) { ?>
					<?php do_action( 'mep_event_seat', $event_id ); ?>
				<?php } ?>

				<?php if ( $speaker_status == 'yes' ) { ?>
                    <div class="mep-default-sidebar-speaker-list">
						<?php do_action( 'mep_event_speakers_list', $event_id ); ?>
                    </div>
					<?php
				}
				?>
				<?php if ( sizeof( $all_dates ) > 0 && $hide_date_list == 'no' ) { ?>
                    <div class="event_date_list_area">
                        <h5 class="_mB_xs"><?php esc_html_e( 'Event Schedule Details', 'mage-eventpress' ) ?></h5>
						<?php do_action( 'mpwem_date_list', $event_id, $event_infos ); ?>
                    </div>
				<?php } ?>
				<?php
					if ( has_term( '', 'mep_tag', $event_id ) ): ?>
                        <div class="mep-default-sidebar-tags">
							<?php do_action( 'mep_event_tags', $event_id ); ?>
                        </div>
					<?php endif;
					if ( $hide_share_details == 'no' ) { ?>
                        <div class="mep-default-sidrbar-social">
							<?php do_action( 'mep_event_social_share', $event_id ); ?>
                        </div>
					<?php } ?>
				<?php do_action( 'mpwem_add_calender', $event_id, $all_dates, $upcoming_date ); ?>
            </div>
        </div>
    </div>
	<?php do_action( 'mpwem_related', $event_id ); ?>
</div>