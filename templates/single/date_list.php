<?php $event_date_icon = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'fa fa-calendar'); ?>
<li>
    <?php do_action('mep_single_before_event_date_list_item',$event_id,$start_datetime); ?>    
    <span class="mep-more-date">
        <i class="<?php echo $event_date_icon; ?>"></i>
        <span class='mep_date_scdl_start_datetime'>
            <?php echo esc_html(get_mep_datetime($start_datetime, 'date-text')); ?>
            <?php echo esc_html(get_mep_datetime($start_datetime, 'time')); ?>
        </span>
        <?php if ($end_date_display_status == 'yes') { ?>
            <span class='mep_date_scdl_end_datetime'>
                 &nbsp;<span class="mep_date_scdl_separator"> - </span>&nbsp;
                <?php
                if ($start_date != $end_date) {
                    echo esc_html(get_mep_datetime($end_datetime, 'date-text'));
                }
                echo ' ' . esc_html(get_mep_datetime($end_datetime, 'time'));
                ?>
            </span>
        <?php } ?>
    </span>
    <?php do_action('mep_single_after_event_date_list_item',$event_id,$start_datetime); ?>  
</li>