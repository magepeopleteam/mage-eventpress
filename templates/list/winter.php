<?php
$event_type = get_post_meta(get_the_id(), 'mep_event_type', true) ? get_post_meta(get_the_id(), 'mep_event_type', true) : 'offline';

$taxonomy_category = MPWEM_Helper::all_taxonomy_as_text($event_id, 'mep_cat');
$taxonomy_organizer = MPWEM_Helper::all_taxonomy_as_text($event_id, 'mep_org');
// $date = mep_get_event_upcomming_date($event_id, 'date');
$date = get_post_meta($event_id, 'event_upcoming_datetime', true);
$event_date_icon            = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'fa fa-calendar');
$event_time_icon            = mep_get_option('mep_event_time_icon', 'icon_setting_sec', 'fas fa-clock');
$event_location_icon        = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');

// mep_get_event_upcomming_date($event_id, 'day');


// echo get_mep_datetime(get_post_meta($event_id,'event_upcoming_datetime',true),'day');

?>
<div class='filter_item mep-event-list-loop  mep_event_list_item mep_event_winter_list mix <?php echo esc_attr($org_class) . ' ' . esc_attr($cat_class); ?>'
     data-title="<?php echo esc_attr(get_the_title($event_id)); ?>"
     data-city-name="<?php echo esc_attr(get_post_meta($event_id, 'mep_city', true)); ?>"
     data-category="<?php echo esc_attr($taxonomy_category); ?>"
     data-organizer="<?php echo esc_attr($taxonomy_organizer); ?>"
     data-date="<?php echo esc_attr(date('m/d/Y',strtotime($date))); ?>"
>
    <?php do_action('mep_event_winter_list_loop_header', $event_id); ?>
    <div class="mep_list_date_wrapper">
        <i class="fas fa-caret-right"></i>
        <h4 class='mep_winter_list_date'><span class="mep_winter_list_dd"><?php echo esc_html(get_mep_datetime(get_post_meta($event_id,'event_upcoming_datetime',true),'day')); ?></span><span class="mep_winter_list_mm_yy"><?php echo esc_html($start_mm_yy); ?></span></h4>
    </div>
    <div class="mep_list_winter_thumb_wrapper">
        <a href="<?php echo get_the_permalink($event_id); ?>">
            <div class="mep_list_winter_thumb" data-bg-image="<?php mep_get_list_thumbnail_src($event_id, 'thumbnail'); ?>"></div>
        </a>
    </div>
    <div class="mep_list_event_details">
        <h4 class="mep_list_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <div class="mep_list_details_col_wrapper">
            <div class="mep_list_details_col_one">
                        <span class="mep_price">                    
                        <?php if ($show_price == 'yes') {
                            echo esc_html($show_price_label) . " " . mep_event_list_price($event_id);
                        } ?>
                        </span>
                <a href="<?php the_permalink(); ?>">
                    <span class="mep_winter_event_time"><i class="<?php echo esc_attr($event_time_icon); ?>"></i> <?php echo esc_html(get_mep_datetime($start_time_format, 'time')); ?> - <?php echo esc_html(get_mep_datetime($end_time_format, 'time')); ?></span>
                    <span class='mep_winter_event_location'><i class="<?php echo esc_attr($event_location_icon); ?>"></i> <?php mep_get_event_city($event_id); ?></span>
                    <span class="mep_winter_event_date"><i class="<?php echo esc_attr($event_date_icon); ?>"></i> <?php echo esc_html(get_mep_datetime($start_date_format, 'date')); ?> - <?php echo esc_html(get_mep_datetime($end_date_format, 'date')); ?></span>

                </a>
            </div>

            <div class="mep_list_details_col_two">
                <?php if ($available_seat == 0) {
                    do_action('mep_show_waitlist_label');
                } ?>

                <?php if (is_array($event_multidate) && sizeof($event_multidate) > 0 && $recurring == 'no') { ?>
                    <div class='mep-multidate-ribbon mep-tem3-title-sec'>
                        <span><?php echo mep_get_option('mep_event_multidate_ribon_text', 'label_setting_sec', __('Multi Date Event', 'mage-eventpress')); ?></span>
                    </div>
                <?php } elseif ($recurring != 'no') { ?>
                    <div class='mep-multidate-ribbon mep-tem3-title-sec'>
                        <span><?php echo mep_get_option('mep_event_recurring_ribon_text', 'label_setting_sec', __('Recurring Event', 'mage-eventpress')); ?></span>
                    </div>
                <?php }
                if ($event_type == 'online') { ?>
                    <div class='mep-eventtype-ribbon mep-tem3-title-sec'>
                        <span><?php echo mep_get_option('mep_event_virtual_label', 'label_setting_sec', __('Virtual Event', 'mage-eventpress')); ?></span>
                    </div>
                <?php } ?>
                <?php do_action('mep_event_list_loop_footer', $event_id); ?>
            </div>
        </div>
    </div>
    <?php do_action('mep_event_winter_list_loop_end', $event_id); ?>
</div>