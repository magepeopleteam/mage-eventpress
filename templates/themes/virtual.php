<?php
// Template Name: Virtual Event

// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id                 = empty( $event_id ) ? get_the_id() : $event_id;
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

$_the_event_id = $event_id;

?>
<div class="mpStyle mep-default-theme mep_flex default_theme">
    <div class="mep-default-content">
        <div class="mep-default-title">
            <?php do_action('mep_event_title', $_the_event_id); ?>
        </div>
        <div class="_mT mpwem_slider_area">
		    <?php do_action( 'add_mp_custom_slider', $event_id, 'mep_gallery_images' ); ?>
        </div>
        <div class="mep-default-feature-content">
            <div class="mpwem_details_content mp_wp_editor"><?php the_content(); ?></div>
            <?php do_action('mpwem_timeline',$event_id); ?>
        </div>
        <div class="mep-default-feature-cart-sec">
	        <?php
		        $all_dates          = MPWEM_Functions::get_dates( $event_id );
		        $all_times          = MPWEM_Functions::get_times( $event_id, $all_dates );
		        $upcoming_date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
		        do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date );
	        ?>
        </div>
        <div class="mep-default-feature-faq-sec">
            <?php do_action('mep_event_faq',$_the_event_id); ?>
        </div>
        <?php do_action( 'mpwem_template_footer', $event_id ); ?>
    </div>
    <div class="mep-default-sidebar">
        <div class="df-sidebar-part">
            <?php if ($hide_org_by_details == 'no' && has_term('','mep_org',$event_id)) : ?>
                <div class="mep-default-sidrbar-meta">
                    <?php  do_action('mep_event_organized_by', $event_id); ?>
                </div>
            <?php endif; ?>

            <?php if ($hide_total_seat_details == 'no') { ?>
	            <?php do_action('mep_event_seat', $_the_event_id); ?>
            <?php } ?>

            <?php if($speaker_status == 'yes'){ ?>
                <div class="mep-default-sidebar-speaker-list">               
                    <?php do_action('mep_event_speakers_list',$_the_event_id); ?>
                </div>
            <?php 
            }
            if ($hide_schedule_details == 'no') { ?>
                <div class="mep-default-sidrbar-events-schedule">
                    <?php do_action('mep_event_date_default_theme',$_the_event_id); ?>
                </div>
            <?php }
            if ($hide_share_details == 'no') { ?>
                <div class="mep-default-sidrbar-social">
                    <?php do_action('mep_event_social_share', $_the_event_id); ?>
                </div>
            <?php }
            if ($hide_calendar_details == 'no') { ?>
                <div class="mep-default-sidrbar-calender-btn">
                    <?php do_action('mep_event_add_calender',$_the_event_id); ?>
                </div>
            <?php } ?>
        </div>
    </div>
	
</div>