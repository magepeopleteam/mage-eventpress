<div class='mep-event-list-loop <?php echo $columnNumber; ?> mep_event_<?php echo $style; ?>_item mix <?php if ($tt) { echo $org_class;  } ?> <?php if ($torg) { echo $cat_class;  } ?>'>
    <?php do_action('mep_event_list_loop_header', $event_id); ?>
    <div class="mep_list_thumb">
        <a href="<?php echo get_the_permalink($event_id); ?>"><?php echo get_the_post_thumbnail($event_id, 'full'); ?></a>
        <?php if (sizeof($event_multidate) == 0) { ?>
            <div class="mep-ev-start-date">
                <div class="mep-day"><?php echo get_mep_datetime($event_meta['event_start_datetime'][0], 'day'); ?></div>
                <div class="mep-month"><?php echo get_mep_datetime($event_meta['event_start_datetime'][0], 'month'); ?></div>
            </div>
        <?php }
        if (is_array($event_multidate) && sizeof($event_multidate) > 0) { ?>
            <div class='mep-multidate-ribbon mep-tem3-title-sec'>
                <span><?php echo mep_get_option('mep_event_multidate_ribon_text', 'label_setting_sec', __('Multi Date Event', 'mage-eventpress'));   ?></span>
            </div>
        <?php }
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
                            <li>
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

                            <li>
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
                    if ($hide_time_list == 'no') {
                        if (strtotime(current_time('Y-m-d H:i')) < strtotime($event_meta['event_start_datetime'][0])) {
                        ?>
                            <li>
                                <div class="evl-ico"><i class="fa fa-calendar"></i></div>
                                <div class="evl-cc">
                                    <h5>
                                        <?php if (sizeof($event_multidate) > 0) {

                                            echo get_mep_datetime($event_meta['event_start_datetime'][0], 'date-text');
                                        }
                                        ?>
                                        <?php echo mep_get_option('mep_time_text', 'label_setting_sec') ? mep_get_option('mep_time_text', 'label_setting_sec') : _e('Time:', 'mage-eventpress'); ?>
                                    </h5>
                                    <h6><?php echo get_mep_datetime($event_meta['event_start_datetime'][0], 'time');
                                        if ($hide_only_end_time_list == 'no') { ?> - <?php echo get_mep_datetime($event_meta['event_end_datetime'][0], 'time');
                                                                                        } ?></h6>
                                </div>
                            </li>
                    <?php }
                    } ?>
                   
                </ul>
        </a>
        <?php do_action('mep_event_list_loop_footer', $event_id); ?>
    </div>
</div>
