<?php
	// Template Name: Royal Theme
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id           = empty( $event_id ) ? get_the_id() : $event_id;
	$hide_date_details       = mep_get_option( 'mep_event_hide_date_from_details', 'single_event_setting_sec', 'no' );
	$hide_time_details       = mep_get_option( 'mep_event_hide_time_from_details', 'single_event_setting_sec', 'no' );
	$hide_location_details   = mep_get_option( 'mep_event_hide_location_from_details', 'single_event_setting_sec', 'no' );
	$hide_total_seat_details = mep_get_option( 'mep_event_hide_total_seat_from_details', 'single_event_setting_sec', 'no' );
	$hide_org_by_details     = mep_get_option( 'mep_event_hide_org_from_details', 'single_event_setting_sec', 'no' );
	$hide_address_details    = mep_get_option( 'mep_event_hide_address_from_details', 'single_event_setting_sec', 'no' );
	$hide_schedule_details   = mep_get_option( 'mep_event_hide_event_schedule_details', 'single_event_setting_sec', 'no' );
	$hide_share_details      = mep_get_option( 'mep_event_hide_share_this_details', 'single_event_setting_sec', 'no' );
	$hide_calendar_details   = mep_get_option( 'mep_event_hide_calendar_details', 'single_event_setting_sec', 'no' );
	$speaker_status          = mep_get_option( 'mep_enable_speaker_list', 'single_event_setting_sec', 'no' );
	$event_date_icon         = mep_get_option( 'mep_event_date_icon', 'icon_setting_sec', 'far fa-calendar-alt' );
	$event_time_icon         = mep_get_option( 'mep_event_time_icon', 'icon_setting_sec', 'fas fa-clock' );
	$event_location_icon     = mep_get_option( 'mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt' );
?>
<div class="mpStyle mep-default-theme royal_theme">
    <div class="mep-default-content">
		<?php if ( $hide_location_details == 'no' ) { ?>
            <div class="mep-default-sidrbar-map">
				<?php do_action( 'mep_event_map', $event_id ); ?>
            </div>
		<?php } ?>
        
        <div class="mep-royal-header">
            <div class="mep-royal-header-col-1">
                <div class="mep-default-title">
					<?php do_action( 'mep_event_title', $event_id ); ?>
                </div>
                <div class="mep-default-feature-date-location">
					<?php if ( $hide_date_details == 'no' ) { ?>
                        <div class="mep-default-feature-date">
                            <div class="df-ico"><i class="<?php echo $event_date_icon; ?>"></i></div>
                            <div class='df-dtl'>
                                <h3>
									<?php echo mep_get_option( 'mep_event_date_text', 'label_setting_sec', __( 'Event Date:', 'mage-eventpress' ) ); ?>
                                </h3>
								<?php do_action( 'mep_event_date_only', $event_id ); ?>
                            </div>
                        </div>
					<?php } ?>
                    
					<?php if ( $hide_time_details == 'no' ) { ?>
                        <div class="mep-default-feature-time">
                            <div class="df-ico"><i class="<?php echo $event_time_icon; ?>"></i></div>
                            <div class='df-dtl'>
                                <h3>
									<?php echo mep_get_option( 'mep_event_time_text', 'label_setting_sec', __( 'Event Time:', 'mage-eventpress' ) ); ?>
                                </h3>
								<?php do_action( 'mep_event_time_only', $event_id ); ?>
                            </div>
                        </div>
					<?php } ?>
                    
					<?php if ( $hide_location_details == 'no' ) { ?>
                        <div class="mep-default-feature-location">
                            <div class="df-ico"><i class="<?php echo $event_location_icon; ?>"></i></div>
                            <div class='df-dtl'>
                                <h3>
									<?php echo mep_get_option( 'mep_event_location_text', 'label_setting_sec', __( 'Event Location:', 'mage-eventpress' ) ); ?>
                                </h3>
                                <p>
                                    <span><?php do_action( 'mep_event_location_venue', $event_id ); ?></span>
									<?php do_action( 'mep_event_location_street', $event_id ); ?>
									<?php do_action( 'mep_event_location_city', $event_id ); ?>
									<?php do_action( 'mep_event_location_state', $event_id ); ?>
									<?php do_action( 'mep_event_location_postcode' ); ?>
									<?php do_action( 'mep_event_location_country', $event_id ); ?>
                                </p>
                            </div>
                        </div>
					<?php } ?>
                </div>
            </div>
            
            <div class="mep-royal-header-col-2">
                <div class="mpwem_slider_area">
		            <?php do_action( 'add_mp_custom_slider', $event_id, 'mep_gallery_images' ); ?>
                </div>
            </div>
        </div>    
        
        <div class="mep-default-col-wrapper">
            <div class="mep-default-col-1">
                <div class="mep-default-feature-cart-sec">
					<?php
						$all_dates     = MPWEM_Functions::get_dates( $event_id );
						$all_times     = MPWEM_Functions::get_times( $event_id, $all_dates );
						$upcoming_date = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
						do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date );
					?>
                </div>
            </div>
            
            <div class="mep-default-col-2">
				<?php if ( $hide_org_by_details == 'no' ) { ?>
                    <div class="mep-default-sidrbar-meta">
                        <i class="far fa-list-alt"></i> 
                        <?php 
                        // Get organizer terms to identify primary organizer
                        $org_terms = get_the_terms($event_id, 'mep_org');
                        if ($org_terms && !is_wp_error($org_terms) && count($org_terms) > 0) {
                            echo mep_get_option('mep_by_text', 'label_setting_sec', __('By:', 'mage-eventpress')) . ' <strong class="mep-primary-organizer">' . esc_html($org_terms[0]->name) . '</strong>';
                            
                            // Display other organizers if there are more than one
                            if (count($org_terms) > 1) {
                                echo ' ' . __('and', 'mage-eventpress') . ' ';
                                $other_orgs = array();
                                for ($i = 1; $i < count($org_terms); $i++) {
                                    $other_orgs[] = '<a href="' . get_term_link($org_terms[$i]->term_id, 'mep_org') . '">' . esc_html($org_terms[$i]->name) . '</a>';
                                }
                                echo implode(', ', $other_orgs);
                            }
                        } else {
                            // If no custom organizer display is needed, use the default
                            do_action('mep_event_organizer', $event_id);
                        }
                        ?>
                    </div>
				<?php } ?>
                
				<?php if ( $hide_schedule_details == 'no' ) { ?>
                    <div class="mep-default-sidrbar-events-schedule">
						<?php do_action( 'mep_event_date_default_theme', $event_id ); ?>
                    </div>
				<?php } ?>
                
				<?php if ( $hide_total_seat_details == 'no' ) { ?>
                    <div class="mep-default-sidrbar-price-seat">
                        <div class="df-seat"><?php do_action( 'mep_event_seat', $event_id ); ?></div>
                    </div>
				<?php } ?>
                
				<?php if ( $hide_calendar_details == 'no' ) { ?>
                    <div class="mep-default-sidrbar-calender-btn">
						<?php do_action( 'mep_event_add_calender', $event_id ); ?>
                    </div>
				<?php } ?>
            </div>
        </div>
        
        <div class="mep-default-feature-content">
            <?php do_action('mep_event_details', $event_id); ?>
        </div>
        
        <?php if ($hide_share_details == 'no') { ?>
            <div class="mep-default-sidrbar-social">
                <?php do_action('mep_event_social_share', $event_id); ?>
            </div>
        <?php } ?>
        
        <div class="mep-default-feature-faq-sec">
            <?php do_action('mep_event_faq',$event_id); ?>
        </div>
    </div>
	<?php do_action( 'mpwem_template_footer', $event_id ); ?>
</div>