<?php
$recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
$day = get_mep_datetime(get_post_meta($event_id, 'event_upcoming_datetime', true), 'day');
$month = get_mep_datetime(get_post_meta($event_id, 'event_upcoming_datetime', true), 'month-name');

$taxonomy_category = MPWEM_Helper::all_taxonomy_as_text($event_id, 'mep_cat');
$taxonomy_organizer = MPWEM_Helper::all_taxonomy_as_text($event_id, 'mep_org');
// $date = mep_get_event_upcomming_date($event_id, 'date');
$date = get_post_meta($event_id, 'event_upcoming_datetime', true);
$event_location_icon = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');
?>
<div class='filter_item mep-event-list-loop  mep_event_list_item mep_event_minimal_list mix <?php echo esc_attr($org_class) . ' ' . esc_attr($cat_class); ?>'
     data-title="<?php echo esc_attr(get_the_title($event_id)); ?>"
     data-city-name="<?php echo esc_attr(get_post_meta($event_id, 'mep_city', true)); ?>"
     data-category="<?php echo esc_attr($taxonomy_category); ?>"
     data-organizer="<?php echo esc_attr($taxonomy_organizer); ?>"
     data-date="<?php echo esc_attr(date('m/d/Y',strtotime($date))); ?>"
>
    <?php do_action('mep_event_minimal_list_loop_header', $event_id); ?>
    <div class="mep_list_thumb">
        <a href="<?php echo get_the_permalink($event_id); ?>"></a>
        <div class="mep-ev-start-date">
            <div class="mep-day"><?php echo apply_filters('mep_event_list_only_day_number', $day, $event_id); ?></div>
            <div class="mep-month"><?php echo apply_filters('mep_event_list_only_month_name', $month, $event_id); ?></div>
        </div>
    </div>
    <div class="mep_list_event_details">
        <a href="<?php the_permalink(); ?>">
            <div class="mep-list-header">
                <h2 class='mep_list_title'><?php the_title(); ?></h2>
                <?php mep_get_event_upcomming_date($event_id, 1); ?>
                <?php
                if ($available_seat == 0) {
                    do_action('mep_show_waitlist_label');
                }
                ?>
                <h3 class='mep_list_date'>  <?php do_action('mep_event_list_date_li', $event_id, 'minimal'); ?>
                    <span class='mep_minimal_list_location'>
                        <i class="<?php echo $event_location_icon; ?>"></i>
                        <?php mep_get_event_city($event_id); ?>
                    </span>
                </h3>

        </a>
        <?php do_action('mep_event_list_loop_footer', $event_id); ?>
    </div>
</div>
<?php do_action('mep_event_minimal_list_loop_end', $event_id); ?>
</div>