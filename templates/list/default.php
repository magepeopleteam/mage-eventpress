<?php
$day                            = mep_get_event_upcomming_date($event_id, 'day'); 
$month                          = mep_get_event_upcomming_date($event_id, 'month-name'); 
$recurring                      = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
$mep_hide_event_hover_btn       = mep_get_option('mep_hide_event_hover_btn', 'event_list_setting_sec', 'no');
$mep_hide_event_hover_btn_text  = mep_get_option('mep_hide_event_hover_btn_text', 'general_setting_sec', __('Book Now','mage-eventpress'));
$sold_out_ribbon                = mep_get_option('mep_show_sold_out_ribbon_list_page', 'general_setting_sec', 'no');
$taxonomy_category              = MPWEM_Helper::all_taxonomy_as_text($event_id, 'mep_cat');
$taxonomy_organizer             = MPWEM_Helper::all_taxonomy_as_text($event_id, 'mep_org');
$date                           = get_post_meta($event_id, 'event_upcoming_datetime', true);
$event_location_icon            = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');
$event_organizer_icon           = mep_get_option('mep_event_organizer_icon', 'icon_setting_sec', 'far fa-list-alt');

?>
<div class='filter_item mep-event-list-loop <?php echo esc_attr($columnNumber); echo ' '.esc_attr($class_name); ?> mep_event_<?php echo esc_attr($style); ?>_item mix <?php echo esc_attr($org_class) . ' ' . esc_attr($cat_class); ?>' data-title="<?php echo esc_attr(get_the_title($event_id)); ?>" data-city-name="<?php echo esc_attr(get_post_meta($event_id, 'mep_city', true)); ?>" data-category="<?php echo esc_attr($taxonomy_category); ?>" data-organizer="<?php echo esc_attr($taxonomy_organizer); ?>" data-date="<?php echo esc_attr(date('m/d/Y',strtotime($date))); ?>" style="width:calc(<?php echo esc_attr($width); ?>% - 14px);">
    <?php do_action('mep_event_list_loop_header', $event_id); ?>
    <div class="mep_list_thumb">
        <a href="<?php echo esc_url(get_the_permalink()); ?>">
            <div class="mep_bg_thumb" data-bg-image="<?php mep_get_list_thumbnail_src($event_id, 'large'); ?>"></div>
        </a>
            <div class="mep-ev-start-date">
                <div class="mep-day"><?php echo esc_html(apply_filters('mep_event_list_only_day_number', $day, $event_id)); ?></div>
                <div class="mep-month"><?php echo esc_html(apply_filters('mep_event_list_only_month_name', $month, $event_id)); ?></div>
            </div>

        <?php
        if (is_array($event_multidate) && sizeof($event_multidate) > 0 && $recurring == 'no') { ?>

            <div class='mep-multidate-ribbon mep-tem3-title-sec'>
                <span><?php echo mep_get_option('mep_event_multidate_ribon_text', 'label_setting_sec', __('Multi Date Event', 'mage-eventpress')); ?></span>
            </div>

        <?php } elseif ($recurring != 'no') {  ?>

            <div class='mep-multidate-ribbon mep-tem3-title-sec'>
                <span><?php echo mep_get_option('mep_event_recurring_ribon_text', 'label_setting_sec', __('Recurring Event', 'mage-eventpress')); ?></span>
            </div>

        <?php  }  if ($event_type == 'online') { ?>

            <div class='mep-eventtype-ribbon mep-tem3-title-sec'>
                <span><?php echo mep_get_option('mep_event_virtual_label', 'label_setting_sec', __('Virtual Event', 'mage-eventpress')); ?></span>
            </div>

        <?php } if($sold_out_ribbon == 'yes' && $total_left <= 0){  ?>

            <div class="mep-eventtype-ribbon mep-tem3-title-sec sold-out-ribbon"><?php echo mep_get_option('mep_event_sold_out_label', 'label_setting_sec', __('Sold Out', 'mage-eventpress')); ?></div>
        
        <?php } ?>        
    </div>
    <div class="mep_list_event_details">
        <a href="<?php the_permalink(); ?>">
            <div class="mep-list-header">
                <h2 class='mep_list_title'><?php the_title(); ?></h2>
                <?php if ($available_seat == 0) {
                    do_action('mep_show_waitlist_label');
                } ?>
                <h3 class='mep_list_date'>
                    <?php if ($show_price == 'yes') {
                        echo esc_html($show_price_label). " " . mep_event_list_price($event_id);
                    } ?>
                </h3>
            </div>
            <?php
            if ($style == 'list') {
                ?>
                <div class="mep-event-excerpt">
                    <?php the_excerpt(); ?>
                </div>
            <?php } ?>

            <div class="mep-list-footer">
                <ul>
                    <?php
                    if ($hide_org_list == 'no') {
                        if (sizeof($author_terms) > 0) {
                            ?>
                            <li class="mep_list_org_name">
                                <div class="evl-ico"><i class="<?php echo esc_attr($event_organizer_icon); ?>"></i></div>
                                <div class="evl-cc">
                                    <h5>
                                        <?php echo mep_get_option('mep_organized_by_text', 'label_setting_sec', __('Organized By:', 'mage-eventpress')); ?>
                                    </h5>
                                    <h6><?php echo esc_html($author_terms[0]->name); ?></h6>
                                </div>
                            </li>
                        <?php }
                    }
                    if ($event_type != 'online') {
                        if ($hide_location_list == 'no') { ?>

                            <li class="mep_list_location_name">
                                <div class="evl-ico"><i class="<?php echo esc_attr($event_location_icon); ?>"></i></div>
                                <div class="evl-cc">
                                    <h5>
                                        <?php echo mep_get_option('mep_location_text', 'label_setting_sec', __('Location:', 'mage-eventpress')); ?>

                                    </h5>
                                    <h6><?php mep_get_event_city($event_id); ?></h6>
                                </div>
                            </li>
                        <?php }
                    }
                    if ($hide_time_list == 'no' && $recurring == 'no') {
                        do_action('mep_event_list_date_li', $event_id, 'grid');
                    } elseif ($hide_time_list == 'no' && $recurring != 'no') {
                        do_action('mep_event_list_upcoming_date_li', $event_id);
                    } ?>

                </ul>
        </a>
        <?php do_action('mep_event_list_loop_footer', $event_id); ?>
    </div>
    <?php if ('yes' == $mep_hide_event_hover_btn) { ?>
        <div class="item_hover_effect">
            <a href="<?php echo esc_url(get_the_permalink($event_id)); ?>"><?php echo esc_html($mep_hide_event_hover_btn_text); ?></a>
        </div>
    <?php } ?>
</div>

<?php //} ?>