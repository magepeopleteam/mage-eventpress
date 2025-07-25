<?php 
$theme = get_post_meta($event_id,'mep_event_template',true);
$event_date_icon = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'far fa-calendar-alt'); ?>
<?php if ($start_date != $end_date) : ?>
    <li>          
        <?php do_action('mep_single_before_event_date_list_item',$event_id,$start_datetime); ?>         
        <div class="mep-more-date">
            <p class='mep_date_scdl_start_datetime'>
                <?php echo esc_html(get_mep_datetime($start_datetime, 'date-text')); ?>
                <?php echo esc_html('-'.get_mep_datetime($start_datetime, 'time')); ?>
            </p>
            <p>
                <?php echo esc_html(get_mep_datetime($end_datetime, 'date-text')); ?>
                <?php if ($end_date_display_status == 'yes') { ?>
                    <?php echo esc_html('-'.get_mep_datetime($end_datetime, 'time')); ?>
                <?php } ?>
            </p>
        </div> 
         <?php do_action('mep_single_after_event_date_list_item',$event_id,$start_datetime); ?>  
    </li>
<?php else: ?>
    <li>  
        <?php do_action('mep_single_before_event_date_list_item',$event_id,$start_datetime); ?>        
        <div class="mep-more-date">
            <p class='mep_date_scdl_start_datetime'>
                <?php echo esc_html(get_mep_datetime($start_datetime, 'date-text')); ?>
            </p>
            <p>
                <?php echo esc_html(get_mep_datetime($start_datetime, 'time')); ?>
                <?php if ($end_date_display_status == 'yes') { ?>
                    <?php echo esc_html('-'.get_mep_datetime($end_datetime, 'time')); ?>
                <?php } ?>
            </p>
        </div> 
         <?php do_action('mep_single_after_event_date_list_item',$event_id,$start_datetime); ?> 
    </li>
<?php endif; ?>