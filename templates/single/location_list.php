<?php
$event_location_list_icon = mep_get_option('mep_event_location_list_icon', 'icon_setting_sec', 'fa fa-arrow-circle-right');
?>
<ul>
    <?php if($venue){ ?> <li><i class="<?php echo $event_location_list_icon; ?>"></i> <span><?php do_action('mep_event_location_venue'); ?></span>
    </li> <?php } ?>
    <?php if($street){ ?><li><i class="<?php echo $event_location_list_icon; ?>"></i> <?php do_action('mep_event_location_street'); ?>
    </li><?php } ?>
    <?php if($city){ ?><li><i class="<?php echo $event_location_list_icon; ?>"></i> <?php do_action('mep_event_location_city'); ?></li>
    <?php } ?>
    <?php if($state){ ?><li><i class="<?php echo $event_location_list_icon; ?>"></i> <?php do_action('mep_event_location_state'); ?>
    </li><?php } ?>
    <?php if($country){ ?><li><i class="<?php echo $event_location_list_icon; ?>"></i>
        <?php do_action('mep_event_location_country'); ?><?php } ?>
    </li>
</ul>