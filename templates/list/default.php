<?php
$day                             = mep_get_event_upcomming_date($event_id,'day');
$month                           = mep_get_event_upcomming_date($event_id,'month');
$recurring                       = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
$mep_hide_event_hover_btn        = mep_get_option('mep_hide_event_hover_btn', 'general_setting_sec', 'no');
$mep_hide_event_hover_btn_text   = mep_get_option('mep_hide_event_hover_btn_text', 'general_setting_sec', 'Book Now');
?>
<div class='mep-event-list-loop <?php echo $columnNumber; ?> mep_event_<?php echo $style; ?>_item mix <?php echo $org_class.' '.$cat_class; ?>' style="width:calc(<?php echo $width; ?>% - 14px);">
    <?php do_action('mep_event_list_loop_header', $event_id); ?>
    <div class="mep_list_thumb">
        <a href="<?php echo get_the_permalink($event_id); ?>"><?php mep_get_list_thumbnail($event_id);  ?></a>
        <?php if ($recurring == 'no') { ?>
            <div class="mep-ev-start-date">
                <div class="mep-day"><?php echo apply_filters('mep_event_list_only_day_number',$day,$event_id); ?></div>
                <div class="mep-month"><?php echo apply_filters('mep_event_list_only_month_name',$month,$event_id); ?></div>
            </div>
        <?php }else{
            do_action('mep_event_list_only_date_show',$event_id);
        }

        if (is_array($event_multidate) && sizeof($event_multidate) > 0 && $recurring == 'no') { ?>
            <div class='mep-multidate-ribbon mep-tem3-title-sec'>
                <span><?php echo mep_get_option('mep_event_multidate_ribon_text', 'label_setting_sec', __('Multi Date Event', 'mage-eventpress'));   ?></span>
            </div>
        <?php }elseif($recurring != 'no'){
            ?>
                <div class='mep-multidate-ribbon mep-tem3-title-sec'>
                    <span><?php echo mep_get_option('mep_event_recurring_ribon_text', 'label_setting_sec', __('Recurring Event', 'mage-eventpress'));   ?></span>
            </div>
            <?php
        }

        if ($event_type == 'online') { ?>
            <div class='mep-eventtype-ribbon mep-tem3-title-sec'>
                <span><?php echo mep_get_option('mep_event_virtual_label', 'label_setting_sec') ? mep_get_option('mep_event_virtual_label', 'label_setting_sec') : _e('Virtual Event', 'mage-eventpress'); ?></span>
            </div>
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
                        echo $show_price_label . " " . mep_event_list_price($event_id);
                    } ?>
                </h3>
            </div>
            <?php
            if ($style == 'list') {
            ?>
                <div class="mep-event-excerpt">
                    <?php the_excerpt(); ?>
                </div>
            <?php }  ?>

            <div class="mep-list-footer">
                <ul>
                    <?php
                    if ($hide_org_list == 'no') {
                        if (sizeof($author_terms) > 0) {
                    ?>
                            <li class="mep_list_org_name">
                                <div class="evl-ico"><i class="fa fa-university"></i></div>
                                <div class="evl-cc">
                                    <h5>
                                        <?php echo mep_get_option('mep_organized_by_text', 'label_setting_sec') ? mep_get_option('mep_organized_by_text', 'label_setting_sec') : _e('Organized By:', 'mage-eventpress'); ?>
                                    </h5>
                                    <h6><?php
                                        echo $author_terms[0]->name;
                                        ?></h6>
                                </div>
                            </li>
                        <?php }
                    }
                    if ($event_type != 'online') {
                        if ($hide_location_list == 'no') { ?>

                            <li class="mep_list_location_name">
                                <div class="evl-ico"><i class="fa fa-location-arrow"></i></div>
                                <div class="evl-cc">
                                    <h5>
                                        <?php echo mep_get_option('mep_location_text', 'label_setting_sec') ? mep_get_option('mep_location_text', 'label_setting_sec') : _e('Location:', 'mage-eventpress'); ?>

                                    </h5>
                                    <h6><?php mep_get_event_city($event_id); ?></h6>
                                </div>
                            </li>
                        <?php }
                    }
                    if ($hide_time_list == 'no' && $recurring == 'no') {
                       do_action('mep_event_list_date_li',$event_id,'grid');
                    }elseif($hide_time_list == 'no' && $recurring != 'no'){
                        do_action('mep_event_list_upcoming_date_li',$event_id);
                    }?>
                   
                </ul>
        </a>
        <?php do_action('mep_event_list_loop_footer', $event_id); ?>
    </div>
    <?php if('yes'==$mep_hide_event_hover_btn){ ?>
    <div class="item_hover_effect">
        <a href="<?php echo get_the_permalink($event_id); ?>"><?php echo $mep_hide_event_hover_btn_text; ?></a>
    </div>
    <?php } ?>
</div>
