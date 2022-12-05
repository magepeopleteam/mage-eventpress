<?php
$day = get_mep_datetime(get_post_meta($event_id, 'event_upcoming_datetime', true), 'day');
$month = get_mep_datetime(get_post_meta($event_id, 'event_upcoming_datetime', true), 'month-name');
// $date = mep_get_event_upcomming_date($event_id, 'date');
$date = get_post_meta($event_id, 'event_upcoming_datetime', true);
$event_date_icon  = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'fa fa-calendar');
$event_location_icon = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');
?>
<div class="timeline__item">
    <div class="timeline__content">
        <div class='mep_event_timeline_list'>
            <?php do_action('mep_event_minimal_list_loop_header', $event_id); ?>
            <div class="mep_list_thumb">
                <a href="<?php echo get_the_permalink($event_id); ?>"><?php mep_get_list_thumbnail($event_id); ?></a>
                <div class="mep-ev-start-date">
                    <div class="mep-day"><?php echo apply_filters('mep_event_list_only_day_number', $day, $event_id); ?></div>
                    <div class="mep-month"><?php echo apply_filters('mep_event_list_only_month_name', $month, $event_id); ?></div>
                </div>
            </div>
            <div class="mep_list_event_details">
                <a href="<?php the_permalink(); ?>">
                    <div class="mep-list-header">
                        <h2 class='mep_list_title'><?php the_title(); ?></h2>
                        <?php if ($available_seat == 0) {
                            do_action('mep_show_waitlist_label');
                        } ?>
                        <h3 class='mep_list_date'>
                            <span class='mep_minimal_list_date'>
                                <i class="<?php echo $event_date_icon; ?>"></i>
                                <?php echo esc_html(get_mep_datetime($event_meta['event_start_datetime'][0], 'time')); ?> - <?php if ($start_datetime == $end_datetime) {
                                    echo esc_html(get_mep_datetime($event_meta['event_end_datetime'][0], 'time'));
                                } else {
                                    echo esc_html(get_mep_datetime($event_meta['event_end_datetime'][0], 'date-time-text'));
                                } ?>
                            </span>
                            <span class='mep_minimal_list_location'><i class="<?php echo $event_location_icon; ?>"></i> <?php mep_get_event_city($event_id); ?></span></h3>
                </a>
                <?php do_action('mep_event_list_loop_footer', $event_id); ?>
            </div>
        </div>
        <?php do_action('mep_event_minimal_list_loop_end', $event_id); ?>
    </div>
</div>
</div>