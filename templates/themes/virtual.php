<?php
	// Template Name: Virtual Event
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id                = empty( $event_id ) ? get_the_id() : $event_id;
	$hide_date_details       = mep_get_option( 'mep_event_hide_date_from_details', 'single_event_setting_sec', 'no' );
	$hide_time_details       = mep_get_option( 'mep_event_hide_time_from_details', 'single_event_setting_sec', 'no' );
	$hide_location_details   = mep_get_option( 'mep_event_hide_location_from_details', 'single_event_setting_sec', 'no' );
	$hide_total_seat_details = mep_get_option( 'mep_event_hide_total_seat_from_details', 'single_event_setting_sec', 'no' );
	$hide_org_by_details     = mep_get_option( 'mep_event_hide_org_from_details', 'single_event_setting_sec', 'no' );
	$hide_address_details    = mep_get_option( 'mep_event_hide_address_from_details', 'single_event_setting_sec', 'no' );
	$hide_share_details      = mep_get_option( 'mep_event_hide_share_this_details', 'single_event_setting_sec', 'no' );
	$speaker_status          = mep_get_option( 'mep_enable_speaker_list', 'single_event_setting_sec', 'no' );
	$_the_event_id           = $event_id;
	$all_dates               = MPWEM_Functions::get_dates( $event_id );
	$all_times               = MPWEM_Functions::get_times( $event_id, $all_dates );
	$upcoming_date           = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	$hide_date_list          = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_hide_event_schedule_details', 'no' );
?>
<div class="mpwem_style default_theme">
    <div class="content_area">
        <div class="mep-default-content">
            <div class="mep-default-title">
				<?php do_action( 'mep_event_title', $_the_event_id ); ?>
            </div>
            <div class="_mT mpwem_slider_area">
				<?php do_action( 'add_mpwem_custom_slider', $event_id, 'mep_gallery_images' ); ?>
            </div>
            <div class="mep-default-feature-content">
                <div class="mpwem_details_content mp_wp_editor"><?php the_content(); ?></div>
				<?php do_action( 'mpwem_timeline', $event_id ); ?>
            </div>
            <div class="mep-default-feature-cart-sec">
				<?php do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date ); ?>
            </div>
            <div class="mep-default-feature-faq-sec">
				<?php do_action( 'mep_event_faq', $_the_event_id ); ?>
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
					<?php do_action( 'mep_event_seat', $_the_event_id ); ?>
				<?php } ?>

				<?php if ( $speaker_status == 'yes' ) { ?>
                    <div class="mep-default-sidebar-speaker-list">
						<?php do_action( 'mep_event_speakers_list', $_the_event_id ); ?>
                    </div>
					<?php
				}
				?>
				<?php if ( sizeof( $all_dates ) > 0 && $hide_date_list == 'no' ) { ?>
                    <div class="event_date_list_area">
                        <h5 class="_mB_xs"><?php esc_html_e( 'Event Schedule Details', 'mage-eventpress' ) ?></h5>
						<?php do_action( 'mpwem_date_list', $event_id, $all_dates ); ?>
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
							<?php do_action( 'mep_event_social_share', $_the_event_id ); ?>
                        </div>
					<?php } ?>
				<?php do_action( 'mpwem_add_calender', $event_id, $all_dates, $upcoming_date ); ?>
            </div>
        </div>
    </div>
	<?php do_action( 'mpwem_related', $event_id ); ?>
</div>