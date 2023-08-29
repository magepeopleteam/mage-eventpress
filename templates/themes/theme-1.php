<?php
// Template Name: Springfield

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
$event_date_icon            = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'fa fa-calendar');
$event_location_icon        = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');
?>
<div class="mep-default-theme spring_field">
    <div class="mep_flex">
        <div class="spring_field_banner">
            <div class="mep-default-feature-image">
                <?php do_action('mep_event_thumbnail', $event_id); ?>
            </div>
        </div>
        <div class="spring_field_banner_right">
            <div class="mep-default-title">
                <?php do_action('mep_event_title', $event_id); ?>
            </div>
            <?php if ($hide_org_by_details == 'no') { ?>
                <div class="mep-default-sidrbar-meta">
                    <?php do_action('mep_event_organizer', $event_id); ?>
                </div>
            <?php } ?>
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
        <?php do_action('mep_add_to_cart', $event_id) ?>
    </div>
</div>