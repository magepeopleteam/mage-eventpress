<?php
$recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';

$taxonomy_category = MPWEM_Global_Function::all_taxonomy_as_text($event_id, 'mep_cat');
$taxonomy_organizer = MPWEM_Global_Function::all_taxonomy_as_text($event_id, 'mep_org');
// $date = mep_get_event_upcomming_date($event_id, 'date');
$date = get_post_meta($event_id, 'event_upcoming_datetime', true);
$event_location_icon = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');
$event_organizer_icon = mep_get_option('mep_event_organizer_icon', 'icon_setting_sec', 'far fa-list-alt');
?>
<div class='filter_item mep-event-list-loop  mep_event_list_item mep_event_native_list mix <?php echo esc_attr($org_class) . ' ' . esc_attr($cat_class); ?>'
     data-title="<?php echo esc_attr(get_the_title($event_id)); ?>"
     data-city-name="<?php echo esc_attr(get_post_meta($event_id, 'mep_city', true)); ?>"
     data-category="<?php echo esc_attr($taxonomy_category); ?>"
     data-organizer="<?php echo esc_attr($taxonomy_organizer); ?>"
     data-date="<?php echo esc_attr(date('m/d/Y',strtotime($date))); ?>"
>
    <?php do_action('mep_event_minimal_list_loop_header', $event_id); ?>
    <div class="mep_list_thumb">
        <a href="<?php echo get_the_permalink($event_id); ?>">
            <div class="mep_bg_thumb" data-bg-image="<?php echo esc_url( MPWEM_Global_Function::get_image_url( $event_id, '', 'thumbnail' ) );?>"></div>
        </a>
    </div>

    <div class="mep_list_event_details">
        <a href="<?php the_permalink(); ?>">
            <div class="mep-list-header">
                <h2 class='mep_list_title'><?php the_title(); ?></h2>
                <?php if ($available_seat == 0) {
                    do_action('mep_show_waitlist_label');
                } ?>
                <h3 class='mep_list_date'>
                    <?php do_action('mep_event_list_date_li', $event_id, 'minimal'); ?>
                    <span class='mep_minimal_list_location'>
                        <i class='<?php echo $event_location_icon; ?>'></i>
                        <?php mep_get_event_city($event_id); ?>
                    </span>
                    <?php 
                    // Display the first organizer (primary organizer)
                    $org_terms = get_the_terms($event_id, 'mep_org');
                    if ($org_terms && !is_wp_error($org_terms) && count($org_terms) > 0) {
                    ?>
                    <span class='mep_minimal_list_organizer'>
                        <i class="<?php echo $event_organizer_icon; ?>"></i>
                       <?php echo esc_html($org_terms[0]->name); ?>
                    </span>
                    <?php } ?>
                </h3>
        </a>
        <?php do_action('mep_event_list_loop_footer', $event_id); ?>
    </div>
</div>
<?php do_action('mep_event_minimal_list_loop_end', $event_id); ?>
</div>