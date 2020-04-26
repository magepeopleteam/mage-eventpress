<?php
add_action('mep_event_list_shortcode','mep_display_event_loop_list',10,3);
function mep_display_event_loop_list($event_id,$columnNumber,$style){
                $now                    = current_time('Y-m-d H:i:s');
                $show_price             = mep_get_option('mep_event_price_show', 'general_setting_sec', 'yes');
               
                $show_price_label       = mep_get_option('event-price-label', 'general_setting_sec', 'Price Starts from:');
                $event_meta             = get_post_custom($event_id);
                $author_terms           = get_the_terms($event_id, 'mep_org');
                $time                   = strtotime($event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0]);
                $newformat              = date_i18n('Y-m-d H:i:s', $time);
                $tt                     = get_the_terms($event_id, 'mep_cat');
                $torg                   = get_the_terms($event_id, 'mep_org');
                $org_class              = mep_get_term_as_class($event_id, 'mep_org');
                $cat_class              = mep_get_term_as_class($event_id, 'mep_cat');
                $event_multidate        = array_key_exists('mep_event_more_date', $event_meta) ? maybe_unserialize($event_meta['mep_event_more_date'][0]) : array();
                $available_seat         = mep_get_total_available_seat($event_id, $event_meta);
                $hide_org_list          = mep_get_option('mep_event_hide_organizer_list', 'general_setting_sec', 'no');
                $hide_location_list     = mep_get_option('mep_event_hide_location_list', 'general_setting_sec', 'no');
                $hide_time_list         = mep_get_option('mep_event_hide_time_list', 'general_setting_sec', 'no');
                $hide_only_end_time_list = mep_get_option('mep_event_hide_end_time_list', 'general_setting_sec', 'no');
                $recurring              = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';                
                $event_type              = get_post_meta(get_the_id(),'mep_event_type',true) ? get_post_meta(get_the_id(),'mep_event_type',true) : '';

ob_start();
?>
    
<div class='mep-event-list-loop <?php echo $columnNumber; ?> mep_event_<?php echo $style; ?>_item mix <?php if ($tt) {  echo $org_class;  } ?> <?php if ($torg) {  echo $cat_class;  } ?>'>
    <?php do_action('mep_event_list_loop_header',$event_id); ?>
                    <div class="mep_list_thumb">
                        <a href="<?php echo get_the_permalink($event_id); ?>"><?php echo get_the_post_thumbnail($event_id,'full'); ?></a>
                        <?php if(sizeof($event_multidate) == 0){ ?>
                        <div class="mep-ev-start-date">
                            <div class="mep-day"><?php echo get_mep_datetime($event_meta['event_start_datetime'][0],'day'); ?></div>
                            <div class="mep-month"><?php echo get_mep_datetime($event_meta['event_start_datetime'][0],'month'); ?></div>
                        </div>
                        <?php } if(is_array($event_multidate) && sizeof($event_multidate) >0){ ?>
                        <div class='mep-multidate-ribbon mep-tem3-title-sec'>
                            <span><?php _e('Multi Date Event','mage-eventpress'); ?></span>
                        </div>
                        <?php } if($event_type == 'online'){ ?>
                        <div class='mep-eventtype-ribbon mep-tem3-title-sec'>
                            <span><?php echo mep_get_option('mep_event_virtual_label', 'label_setting_sec') ? mep_get_option('mep_event_virtual_label', 'label_setting_sec') : _e('Virtual Event','mage-eventpress'); ?></span>
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
                                <h3 class='mep_list_date'> <?php if ($show_price == 'yes') {
                                        echo $show_price_label . " " . mep_event_list_price($event_id);
                                    } ?></h3>
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
                                    <?php if ($hide_org_list == 'no') { ?>
                                        <li>
                                            <div class="evl-ico"><i class="fa fa-university"></i></div>
                                            <div class="evl-cc">
                                                <h5>
                                                    <?php echo mep_get_option('mep_organized_by_text', 'label_setting_sec') ? mep_get_option('mep_organized_by_text', 'label_setting_sec') : _e('Organized By:', 'mage-eventpress'); ?>
                                                </h5>
                                                <h6><?php if ($author_terms) {
                                                        echo $author_terms[0]->name;
                                                    } ?></h6>
                                            </div>
                                        </li>
                                    <?php }
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
                                    if ($hide_time_list == 'no') { ?>
                                        <li>
                                            <div class="evl-ico"><i class="fa fa-calendar"></i></div>
                                            <div class="evl-cc">
                                                <h5>
                                                    <?php if(sizeof($event_multidate) > 0){ echo get_mep_datetime($event_meta['event_start_datetime'][0],'date-text'); } ?>
                                                    <?php echo mep_get_option('mep_time_text', 'label_setting_sec') ? mep_get_option('mep_time_text', 'label_setting_sec') : _e('Time:', 'mage-eventpress'); ?>
                                                </h5>
                                                <h6><?php echo get_mep_datetime($event_meta['event_start_datetime'][0],'time');
                                                    if ($hide_only_end_time_list == 'no') { ?> - <?php echo get_mep_datetime($event_meta['event_end_datetime'][0],'time');
                                                    } ?></h6>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                                  </a>
                                <?php do_action('mep_event_list_loop_footer',$event_id); ?>
                            </div>
                      
                    </div>
                     <?php do_action('mep_event_list_loop_end',$event_id); ?>
                </div>    

<?php  
$content = ob_get_clean();
echo apply_filters('mage_event_loop_list_shortcode', $content, $event_id,$style);  
}