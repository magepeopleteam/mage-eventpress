<?php
// Template Name: Default Theme

// Settings Value :::::::::::::::::::::::::::::::::::::::;
$event_id                   = empty($event_id) ? get_the_id() : $event_id;
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
$event_date_icon            = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'fa fa-calendar');
$event_time_icon            = mep_get_option('mep_event_time_icon', 'icon_setting_sec', 'fas fa-clock');
$event_location_icon        = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');
$event_organizer_icon       = mep_get_option('mep_event_organizer_icon', 'icon_setting_sec', 'far fa-list-alt');
$show_google_map_location   = get_post_meta($event_id,'mep_sgm',true) ? get_post_meta($event_id,'mep_sgm',true) : 'no';
// echo $event_id;
?>
<div class="mep-default-theme mep_flex default_theme">
    <div class="mep-default-content">
        <div class="mep-default-title">
            <?php do_action('mep_event_title', $event_id); ?>
        </div>
        <div class="mep-default-feature-image">
            <?php do_action('mep_event_thumbnail', $event_id); ?>
        </div>
        <div class="mep-default-feature-date-location">
            <?php if ($hide_date_details == 'no') { ?>
                <div class="mep-default-feature-date">
                    <div class="df-ico"><i class="<?php echo $event_date_icon; ?>"></i></div>
                    <div class='df-dtl'>
                        <h3>
                            <?php 
                             echo mep_get_option('mep_event_date_text', 'label_setting_sec', __('Event Date:', 'mage-eventpress')); 
                            ?>
                        </h3>
                        <?php do_action('mep_event_date_only',$event_id); ?>
                    </div>
                </div>
            <?php }
            if ($hide_time_details == 'no') { ?>
                <div class="mep-default-feature-time">
                    <div class="df-ico"><i class="<?php echo $event_time_icon; ?>"></i></div>
                    <div class='df-dtl'>
                        <h3>
                            <?php echo mep_get_option('mep_event_time_text', 'label_setting_sec', __('Event Time:', 'mage-eventpress')); ?>
                        </h3>
                        <?php do_action('mep_event_time_only',$event_id); ?>
                    </div>
                </div>
            <?php }
            if ($hide_location_details == 'no' ) { ?>
                <div class="mep-default-feature-location">
                <div class="df-ico"><i class="<?php echo $event_location_icon; ?>"></i></div>
                    <div class='df-dtl'>
                        <h3>
                            <?php echo mep_get_option('mep_event_location_text', 'label_setting_sec', __('Event Location:', 'mage-eventpress')); ?>
                        </h3>
                        <p><?php do_action('mep_event_location_venue', $event_id); ?>
                            <?php //do_action('mep_event_location_city'); ?>    </p>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="mep-default-feature-content">
            <?php do_action('mep_event_details', $event_id); ?>
        </div>
        <div class="mep-default-feature-cart-sec">
            <?php do_action('mep_add_to_cart', $event_id) ?>
        </div>

        <div class="mep-default-feature-faq-sec">
            <?php do_action('mep_event_faq',$event_id); ?>
        </div>

    </div>
    <div class="mep-default-sidebar">
    <?php if ($hide_location_details == 'no' && $show_google_map_location != 'no') { ?>
        <div class="mep-default-sidrbar-map">
            <h3>
                <?php echo mep_get_option('mep_event_location_text', 'label_setting_sec', __('Event Location:', 'mage-eventpress')); ?>
            </h3>
            <?php do_action('mep_event_map',$event_id); ?>
        </div>
    <?php } ?> 
        <div class="df-sidebar-part">
            <?php if ($hide_total_seat_details == 'no') { ?>
                <div class="mep-default-sidrbar-price-seat">
                    <div class="df-seat"><?php do_action('mep_event_seat', $event_id); ?></div>
                </div>
            <?php } ?>
            <?php if ($hide_org_by_details == 'no' && has_term('','mep_org',$event_id)) { ?>
                <div class="mep-default-sidrbar-meta">
                    <i class="<?php echo $event_organizer_icon; ?>"></i> <?php do_action('mep_event_organizer', $event_id); ?>
                </div>
            <?php }

            if ($hide_address_details == 'no') { ?>
                <div class="mep-default-sidrbar-address">
                    <?php do_action('mep_event_address_list_sidebar',$event_id); ?>
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
            if($speaker_status == 'yes'){ ?>
                <div class="mep-default-sidebar-speaker-list">
               
                    <?php do_action('mep_event_speakers_list',$event_id); ?>
                </div>
            <?php 
            }
            if ($hide_calendar_details == 'no') { ?>
                <div class="mep-default-sidrbar-calender-btn">
                    <?php do_action('mep_event_add_calender',$event_id); ?>
                </div>
            <?php }    

            dynamic_sidebar('mep_default_sidebar');
            
            ?>
        </div>
    </div>
</div>