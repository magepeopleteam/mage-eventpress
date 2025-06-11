<?php
// Template Name: Springfield

// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id           = empty( $event_id ) ? get_the_id() : $event_id;
$hide_date_details          = mep_get_option('mep_event_hide_date_from_details', 'single_event_setting_sec', 'no');
$hide_time_details          = mep_get_option('mep_event_hide_time_from_details', 'single_event_setting_sec', 'no');
$hide_location_details      = mep_get_option('mep_event_hide_location_from_details', 'single_event_setting_sec', 'no');
$hide_total_seat_details    = mep_get_option('mep_event_hide_total_seat_from_details', 'single_event_setting_sec', 'no');
$hide_org_by_details        = mep_get_option('mep_event_hide_org_from_details', 'single_event_setting_sec', 'no');
$hide_address_details       = mep_get_option('mep_event_hide_address_from_details', 'single_event_setting_sec', 'no');
$hide_schedule_details      = mep_get_option('mep_event_hide_event_schedule_details', 'single_event_setting_sec', 'no');
$hide_share_details         = mep_get_option('mep_event_hide_share_this_details', 'single_event_setting_sec', 'no');
$hide_calendar_details      = mep_get_option('mep_event_hide_calendar_details', 'single_event_setting_sec', 'no');
$speaker_status             = mep_get_option('mep_enable_speaker_list', 'single_event_setting_sec', 'no');
$event_date_icon            = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'far fa-calendar-alt');
$event_location_icon        = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');
?>
<div class="mpStyle mep-default-theme spring_field">
    <div class="mep_flex">
        <div class="spring_field_banner">
            <div class="_mT mpwem_slider_area">
		        <?php do_action( 'add_mp_custom_slider', $event_id, 'mep_gallery_images' ); ?>
            </div>
        </div>
        <div class="spring_field_banner_right">
            <div class="mep-default-title">
                <?php do_action('mep_event_title', $event_id); ?>
            </div>
            <?php if ($hide_org_by_details == 'no' && has_term('','mep_org',$event_id)) : ?>
                <div class="mep-default-sidrbar-meta">
                    <?php 
                    // Get organizer terms to identify primary organizer
                    $org_terms = get_the_terms($event_id, 'mep_org');
                    $links = array();
                    if ($org_terms && !is_wp_error($org_terms) && count($org_terms) > 0) :?>
                            <div class="mep-org-details">
                                <div class="org-name">
                                    <div><?php echo _e('Organized By:'); ?></div>
                                    <?php foreach ($org_terms as $index => $org): ?>
                                        <strong><?php echo esc_html($org->name); ?><?php if ($index < count($org_terms) - 1): ?>|<?php endif; ?></strong>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else :
                            // If no custom organizer display is needed, use the default
                            do_action('mep_event_organizer', $event_id);
                    endif;
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if ($hide_total_seat_details == 'no') { ?>
                <div class="mep-default-sidrbar-price-seat">
                    <div class="df-seat"><?php do_action('mep_event_seat', $event_id); ?></div>
                </div>
            <?php } ?>
            <?php if ($hide_calendar_details == 'no') { ?>
                <div class="mep-default-sidrbar-calender-btn">
                    <?php do_action('mep_event_add_calender', $event_id); ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="mep-default-sidrbar-map">
        <?php do_action('mep_event_map', $event_id); ?>
    </div>
    <div class="mep_spring_date">
        <?php if ($hide_schedule_details == 'no') { ?>
            <div class="mep-default-feature-date">
                <div class="df-ico"><i class="<?php echo $event_date_icon; ?>"></i></div>
                <div class='df-dtl'>
                    <h3><?php esc_html_e('Date and Time:', 'mage-eventpress'); ?></h3>
                    <?php do_action('mep_event_date', $event_id); ?>
                </div>
            </div>
        <?php } ?>
        <?php if ($hide_location_details == 'no') { ?>
            <div class="mep-default-feature-location">
                <div class="df-ico"><i class="<?php echo $event_location_icon; ?>"></i></div>
                <div class='df-dtl'>
                    <h3>
                        <?php echo mep_get_option('mep_event_location_text', 'label_setting_sec', __('Event Location:', 'mage-eventpress')); ?>
                    </h3>
                        <p><?php do_action('mep_event_location', $event_id); ?></p>
                </div>
            </div>
        <?php } ?>
        <?php if ($hide_share_details == 'no') { ?>
            <div class="mep-default-sidrbar-social">
                <?php do_action('mep_event_social_share', $event_id); ?>
            </div>
        <?php } ?>
    </div>
    <?php
    if ($speaker_status == 'yes') { ?>
        <div class="mep-theme_springfield-sidebar-speaker-list mep-default-sidebar-speaker-list">
            <?php do_action('mep_event_speakers_list', $event_id); ?>
        </div>
    <?php
    }
    ?>
    <div class="mep-default-feature-content">
        <h4 class="mep-cart-table-title"><?php esc_html_e('Description', 'mage-eventpress'); ?></h4>
        <?php do_action('mep_event_details', $event_id); ?>
        <div class="mep-theme1-faq-sec">
            <?php do_action('mep_event_faq', $event_id); ?>
        </div>
    </div>
    <div class="mep-default-feature-cart-sec">
	    <?php
		    $all_dates          = MPWEM_Functions::get_dates( $event_id );
		    $all_times          = MPWEM_Functions::get_times( $event_id, $all_dates );
		    $upcoming_date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
		    do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date );
	    ?>
    </div>
	<?php do_action( 'mpwem_template_footer', $event_id ); ?>
</div>