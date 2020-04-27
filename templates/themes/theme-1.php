<?php
// Template Name: Springfield

// Settings Value :::::::::::::::::::::::::::::::::::::::;
$hide_date_details = mep_get_option('mep_event_hide_date_from_details', 'general_setting_sec', 'no');
$hide_time_details = mep_get_option('mep_event_hide_time_from_details', 'general_setting_sec', 'no');
$hide_location_details = mep_get_option('mep_event_hide_location_from_details', 'general_setting_sec', 'no');
$hide_total_seat_details = mep_get_option('mep_event_hide_total_seat_from_details', 'general_setting_sec', 'no');
$hide_org_by_details = mep_get_option('mep_event_hide_org_from_details', 'general_setting_sec', 'no');
$hide_address_details = mep_get_option('mep_event_hide_address_from_details', 'general_setting_sec', 'no');
$hide_schedule_details = mep_get_option('mep_event_hide_event_schedule_details', 'general_setting_sec', 'no');
$hide_share_details = mep_get_option('mep_event_hide_share_this_details', 'general_setting_sec', 'no');
$hide_calendar_details = mep_get_option('mep_event_hide_calendar_details', 'general_setting_sec', 'no');
$speaker_status             = mep_get_option('mep_enable_speaker_list', 'general_setting_sec', 'no');
?>
<div class="mep-default-theme spring_field">
    <div class="mep_flex">
        <div class="spring_field_banner">
            <div class="mep-default-feature-image">
                <?php do_action('mep_event_thumbnail'); ?>
            </div>
        </div>
        <div class="spring_field_banner_right">
            <div class="mep-default-title">
                <?php do_action('mep_event_title'); ?>
            </div>
            <?php if ($hide_org_by_details == 'no') { ?>
                <div class="mep-default-sidrbar-meta">
                    <?php do_action('mep_event_organizer'); ?>
                </div>
            <?php } ?>
            <?php if ($hide_total_seat_details == 'no') { ?>
                <div class="mep-default-sidrbar-price-seat">
                    <div class="df-seat"><?php do_action('mep_event_seat'); ?></div>
                </div>
            <?php } ?>
            <?php if ($hide_calendar_details == 'no') { ?>
                <div class="mep-default-sidrbar-calender-btn">
                    <?php do_action('mep_event_add_calender',get_the_id()); ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="mep-default-sidrbar-map">
        <?php do_action('mep_event_map',get_the_id()); ?>
    </div>
    <div class="mep_spring_date">
        <?php if ($hide_schedule_details == 'no') { ?>
            <div class="mep-default-feature-date">
                <div class="df-ico"><i class="fa fa-calendar"></i></div>
                <div class='df-dtl'>
                    <h3><?php _e('Date and Time:', 'mage-eventpress'); ?></h3>
                    <?php do_action('mep_event_date'); ?>
                </div>
            </div>
        <?php }?>
        <?php if ($hide_location_details == 'no') { ?>
            <div class="mep-default-feature-location">
                <div class="df-ico"><i class="fa fa-map-marker"></i></div>
                <div class='df-dtl'>
                    <h3>
                        <?php echo mep_get_option('mep_event_location_text', 'label_setting_sec') ? mep_get_option('mep_event_location_text', 'label_setting_sec') : _e('Event Location:', 'mage-eventpress'); ?>
                    </h3>
                    <p><?php do_action('mep_event_location_venue'); ?>
                        , <?php do_action('mep_event_location_city'); ?>    </p>
                </div>
            </div>
        <?php } ?>
        <?php if ($hide_share_details == 'no') { ?>
            <div class="mep-default-sidrbar-social">
                <?php do_action('mep_event_social_share'); ?>
            </div>
        <?php } ?>
    </div>
    <?php
            if($speaker_status == 'yes'){ ?>
                <div class="mep-theme_springfield-sidebar-speaker-list mep-default-sidebar-speaker-list">              
                    <?php do_action('mep_event_speakers_list',get_the_id()); ?>
                </div>
            <?php 
            }
    ?>
    <div class="mep-default-feature-content">
        <h4 class="mep-cart-table-title"><?php _e('Description', 'mage-eventpress'); ?></h4>
        <?php do_action('mep_event_details'); ?>
        <div class="mep-theme1-faq-sec">
            <?php do_action('mep_event_faq',get_the_id()); ?>
        </div>
    </div>
    <div class="mep-default-feature-cart-sec">
        <?php do_action('mep_add_to_cart',get_the_id()) ?>
    </div>
</div>
