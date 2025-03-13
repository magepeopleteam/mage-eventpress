<?php 
$theme = get_post_meta($event_id,'mep_event_template',true);
if($theme!='smart.php'):
$event_date_icon = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'far fa-calendar-alt'); ?>
<li>
    <?php do_action('mep_single_before_event_date_list_item',$event_id,$start_datetime); ?>    
    <span class="mep-more-date">
        <i class="<?php echo $event_date_icon; ?>"></i>
        <span class='mep_date_scdl_start_datetime'>
            <?php echo esc_html(get_mep_datetime($start_datetime, 'date-text')); ?>
            <?php echo esc_html(get_mep_datetime($start_datetime, 'time')); ?>
        </span>
        <?php if ($end_date_display_status == 'yes') { if ($start_date != $end_date) { ?>
            <span class='mep_date_scdl_end_datetime'>
                <?php                
                    echo esc_html(get_mep_datetime($end_datetime, 'date-text'));
                    echo ' '.esc_html(get_mep_datetime($end_datetime, 'time'));
                    echo '</span>';
                }else{
                    ?>
                     <span class='mep_date_scdl_end_time' style="    display: inline-block;margin-left: 6px;">
                        <?php
                            echo ' - '.esc_html(get_mep_datetime($end_datetime, 'time'));
                        ?>
                    </span>
                    <?php } } ?>
            </span>
    <?php do_action('mep_single_after_event_date_list_item',$event_id,$start_datetime); ?>  
</li>
<!-- if smart theme selected show below style -->
<?php else: ?>
    <?php if ($start_date != $end_date) : ?>
        <li>    
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
        </li>
    <?php else: ?>
        <li>    
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
        </li>
    <?php endif; ?>
<?php endif; ?>

