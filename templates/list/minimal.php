<?php 
$recurring     = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no'; 
$day                             = get_mep_datetime(get_post_meta($event_id,'event_upcoming_datetime',true),'day');
$month                           = get_mep_datetime(get_post_meta($event_id,'event_upcoming_datetime',true),'month');

?>
<div class='mep-event-list-loop  mep_event_list_item mep_event_minimal_list mix <?php echo $org_class.' '.$cat_class; ?>'>
    <?php do_action('mep_event_minimal_list_loop_header',$event_id); ?>
                    <div class="mep_list_thumb">
                        <a href="<?php echo get_the_permalink($event_id); ?>"></a>
                        <div class="mep-ev-start-date">
                            <div class="mep-day"><?php echo apply_filters('mep_event_list_only_day_number',$day,$event_id); ?></div>
                            <div class="mep-month"><?php echo apply_filters('mep_event_list_only_month_name',$month,$event_id); ?></div>
                        </div>
                    </div>
                    <div class="mep_list_event_details">
                        <a href="<?php the_permalink(); ?>">
                            <div class="mep-list-header">
                                <h2 class='mep_list_title'><?php the_title(); ?></h2>
                                <?php
                                mep_get_event_upcomming_date($event_id,1);
                                ?>
                                <?php if ($available_seat == 0) {
                                    do_action('mep_show_waitlist_label');
                                } ?>
                                <h3 class='mep_list_date'>  <?php do_action('mep_event_list_date_li',$event_id,'minimal');  ?> <span class='mep_minimal_list_location'><i class='fa fa-map-marker'></i> <?php mep_get_event_city($event_id); ?></span></h3>
                    
                        </a>
                        <?php do_action('mep_event_list_loop_footer',$event_id); ?>
                    </div>
                    </div>
                    <?php do_action('mep_event_minimal_list_loop_end',$event_id); ?>
</div>