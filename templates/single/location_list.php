<ul>
    <?php if($venue){ ?> <li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_venue'); ?>
    </li> <?php } ?>
    <?php if($street){ ?><li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_street'); ?>
    </li><?php } ?>
    <?php if($city){ ?><li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_city'); ?></li>
    <?php } ?>
    <?php if($state){ ?><li><i class="fa fa-arrow-circle-right"></i> <?php do_action('mep_event_location_state'); ?>
    </li><?php } ?>
    <?php if($country){ ?><li><i class="fa fa-arrow-circle-right"></i>
        <?php do_action('mep_event_location_country'); ?><?php } ?>
    </li>
</ul>