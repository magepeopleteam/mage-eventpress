<?php
	// Template Name: Franklin
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
	$gallery_image_arr       = get_post_meta( $event_id, 'mep_gallery_images', true ) ? get_post_meta( $event_id, 'mep_gallery_images', true ) : [];
	$all_dates               = MPWEM_Functions::get_dates( $event_id );
	$all_times               = MPWEM_Functions::get_times( $event_id, $all_dates );
	$upcoming_date           = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	$hide_date_list = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_hide_event_schedule_details', 'no' );
?>
<div class="mpwem_style default_theme franklin">
    <div class="mep-default-title">
		<?php do_action( 'mep_event_title', $event_id ); ?>
    </div>
    <div class="_mT mpwem_slider_area">
		<?php do_action( 'add_mpwem_custom_slider', $event_id, 'mep_gallery_images' ); ?>
    </div>
    <div class="mep-default-feature-content">
        <h4 class="mep-cart-table-title"><?php esc_html_e( 'Description', 'mage-eventpress' ); ?></h4>
		<?php do_action( 'mep_event_details', $event_id ); ?>
        <div class="mep-theme1-faq-sec">
			<?php do_action( 'mep_event_faq', $event_id ); ?>
        </div>
    </div>
    <div class="franklin_divided">
        <div class="franklin_divided_left">
            <div class="mep-default-sidrbar-map" id="mep-map-location">
				<?php do_action( 'mep_event_map', $event_id ); ?>
            </div>
			<?php
				if ( $speaker_status == 'yes' ) { ?>
                    <div class="mep-default-feature-content mep_theme_franklin_sidebar_speaker_list mep-default-sidebar-speaker-list">
						<?php do_action( 'mep_event_speakers_list', $event_id ); ?>
                    </div>
					<?php
				}
			?>
            <div class="mep-default-feature-cart-sec">
				<?php do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date ); ?>
            </div>
        </div>
        <div class="franklin_divided_sidebar">
            <div class="franklin_divided_sidebar_bac">
				<?php if ( $hide_total_seat_details == 'no' ) { ?>
                    <div class="mep-default-sidrbar-price-seat">
                        <div class="df-seat"><?php do_action( 'mep_event_seat', $event_id ); ?></div>
                    </div>
				<?php } ?>
				<?php if ( $hide_org_by_details == 'no' ) { ?>
                    <div class="mep-default-sidrbar-meta">
                        <i class="far fa-list-alt"></i>
						<?php
							// Get organizer terms to identify primary organizer
							$org_terms = get_the_terms( $event_id, 'mep_org' );
							if ( $org_terms && ! is_wp_error( $org_terms ) && count( $org_terms ) > 0 ) {
								echo mep_get_option( 'mep_by_text', 'label_setting_sec', __( 'By:', 'mage-eventpress' ) ) . ' <strong class="mep-primary-organizer">' . esc_html( $org_terms[0]->name ) . '</strong>';
								// Display other organizers if there are more than one
								if ( count( $org_terms ) > 1 ) {
									echo ' ' . __( 'and', 'mage-eventpress' ) . ' ';
									$other_orgs = array();
									for ( $i = 1; $i < count( $org_terms ); $i ++ ) {
										$other_orgs[] = '<a href="' . get_term_link( $org_terms[ $i ]->term_id, 'mep_org' ) . '">' . esc_html( $org_terms[ $i ]->name ) . '</a>';
									}
									echo implode( ', ', $other_orgs );
								}
							} else {
								// If no custom organizer display is needed, use the default
								do_action( 'mep_event_organizer', $event_id );
							}
						?>
                    </div>
				<?php }
					if ( $hide_address_details == 'no' ) { ?>
                        <div class="mep-default-sidebar-address">
							<?php do_action( 'mep_event_address_list_sidebar', $event_id ); ?>
                        </div>
					<?php } ?>
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
							<?php do_action( 'mep_event_social_share', $event_id ); ?>
                        </div>
					<?php } ?>
					<?php do_action( 'mpwem_add_calender', $event_id ,$all_dates,$upcoming_date); ?>
            </div>
        </div>
    </div>
	<?php do_action( 'mpwem_template_footer', $event_id ); ?>
</div>