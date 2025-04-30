<?php
// Template Name: Franklin


// Settings Value :::::::::::::::::::::::::::::::::::::::;
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
$gallery_image_arr = get_post_meta($event_id,'mep_gallery_images',true) ? get_post_meta($event_id,'mep_gallery_images',true) : [];
?>
<div class="mep-default-theme franklin">
    <div class="mep-default-title">
        <?php do_action('mep_event_title', $event_id); ?>
    </div>
    <?php if(is_array($gallery_image_arr) && count($gallery_image_arr) > 1){ ?>
            <div class="mpStyle">
                    <?php            
                        do_action( 'add_mp_custom_slider', $event_id, 'mep_gallery_images' );             
                    ?>
                </div>
            <?php }else{ ?>
            <div class="mep-default-feature-image">
                    <?php 
                        do_action('mep_event_thumbnail', $event_id); ?>
                </div>
            <?php } ?>
    <div class="mep-default-feature-content">
        <h4 class="mep-cart-table-title"><?php esc_html_e('Description', 'mage-eventpress'); ?></h4>
        <?php do_action('mep_event_details', $event_id); ?>
        <div class="mep-theme1-faq-sec">
            <?php do_action('mep_event_faq',$event_id); ?>
        </div>
    </div>
    <div class="franklin_divided">
        <div class="franklin_divided_left">
            <div class="mep-default-sidrbar-map">
                <?php do_action('mep_event_map',$event_id); ?>
            </div>
            <?php
            if($speaker_status == 'yes'){ ?>
                <div class="mep-default-feature-content mep_theme_franklin_sidebar_speaker_list mep-default-sidebar-speaker-list">              
                    <?php do_action('mep_event_speakers_list',$event_id); ?>
                </div>
            <?php 
            }
    ?>            
            <div class="mep-default-feature-cart-sec">
	            <?php
		            $mep_show_category   = get_post_meta($event_id,'mep_show_category',true) ? get_post_meta($event_id,'mep_show_category',true) : 'off';
		            if($mep_show_category=='on' && class_exists('MPWEMAGT_Helper')){
			            $all_dates          = MPWEM_Functions::get_dates( $event_id );
			            $all_times          = MPWEM_Functions::get_times( $event_id, $all_dates );
			            $upcoming_date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
			            do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date );
		            }else {
			            do_action( 'mep_add_to_cart', $event_id );
		            }
	            ?>
            </div>
        </div>
        <div class="franklin_divided_sidebar">
            <div class="franklin_divided_sidebar_bac">
                <?php if ($hide_total_seat_details == 'no') { ?>
                    <div class="mep-default-sidrbar-price-seat">
                        <div class="df-seat"><?php do_action('mep_event_seat', $event_id); ?></div>
                    </div>
                <?php } ?>
                <?php if ($hide_org_by_details == 'no') { ?>
                    <div class="mep-default-sidrbar-meta">
                    <i class="far fa-list-alt"></i> <?php do_action('mep_event_organizer', $event_id); ?>
                    </div>
                <?php } 
                if ($hide_address_details == 'no') { ?>
                    <div class="mep-default-sidrbar-address">
                        <ul>
                           <?php if(mep_location_existis('mep_location_venue',$event_id)){ ?> <li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_venue', $event_id); ?></li><?php } ?>
                           <?php if(mep_location_existis('mep_street',$event_id)){ ?><li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_street', $event_id); ?></li><?php } ?>
                           <?php if(mep_location_existis('mep_city',$event_id)){ ?><li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_city', $event_id); ?></li><?php } ?>
                           <?php if(mep_location_existis('mep_state',$event_id)){ ?><li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_state', $event_id); ?></li><?php } ?>
                           <?php if(mep_location_existis('mep_country',$event_id)){ ?><li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_country', $event_id); ?><?php } ?>
                            </li>
                        </ul>
                    </div>
                <?php }
                if ($hide_schedule_details == 'no') { ?>
                    <div class="mep-default-sidrbar-events-schedule">
                        <?php do_action('mep_event_date_default_theme',$event_id); ?>
                    </div>
                <?php }
                if ($hide_share_details == 'no') { ?>
                    <div class="mep-default-sidrbar-social">
                        <?php do_action('mep_event_social_share', $event_id); ?>
                    </div>
                <?php }
                if ($hide_calendar_details == 'no') { ?>
                    <div class="mep-default-sidrbar-calender-btn">
                        <?php do_action('mep_event_add_calender',$event_id); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
	<?php do_action( 'mpwem_template_footer', $event_id ); ?>
</div>