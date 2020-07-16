<div class='mep-event-list-loop  mep_event_list_item mep_event_native_list mix <?php if ($tt) {  echo $org_class;  } ?> <?php if ($torg) {  echo $cat_class;  } ?>'>
    <?php do_action('mep_event_minimal_list_loop_header',$event_id); ?>
                    <div class="mep_list_thumb">
                        <a href="<?php echo get_the_permalink($event_id); ?>"><?php echo get_the_post_thumbnail($event_id,'full'); ?></a>
                    </div>
                    <div class="mep_list_event_details">
                        <a href="<?php the_permalink(); ?>">
                            <div class="mep-list-header">
                                <h2 class='mep_list_title'><?php the_title(); ?></h2>
                                <?php if ($available_seat == 0) {
                                    do_action('mep_show_waitlist_label');
                                } ?>
                                <h3 class='mep_list_date'>  <span class='mep_minimal_list_date'><i class="fa fa-calendar"></i> <?php echo get_mep_datetime($event_meta['event_start_datetime'][0],'date-time-text'); ?> - <?php if($start_datetime == $end_datetime){ echo get_mep_datetime($event_meta['event_end_datetime'][0],'time'); }else{ echo get_mep_datetime($event_meta['event_end_datetime'][0],'date-time-text'); } ?></span>  <span class='mep_minimal_list_location'><i class='fa fa-map-marker'></i> <?php mep_get_event_city($event_id); ?></span></h3></a>
                                 <?php do_action('mep_event_list_loop_footer',$event_id); ?>
                            </div>
                    </div>
                     <?php do_action('mep_event_minimal_list_loop_end',$event_id); ?>
                </div>  