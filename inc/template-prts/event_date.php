<?php
add_action('mep_event_date', 'mep_ev_datetime');
// This Function Will be depricate soon, Please don't use this any where
function mep_ev_datetime(){
    global $event_meta;
    $start_datetime         = $event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0];
    $start_date             = $event_meta['event_start_date'][0];
    $start_time             = $event_meta['event_start_time'][0];
    $end_datetime           = $event_meta['event_end_date'][0] . ' ' . $event_meta['event_end_time'][0];
    $end_date               = $event_meta['event_end_date'][0];
    $end_time               = $event_meta['event_end_time'][0];
    $more_date              = array_key_exists('mep_event_more_date', $event_meta) ? unserialize($event_meta['mep_event_more_date'][0]) : array();
    $recurring              = get_post_meta(get_the_id(), 'mep_enable_recurring', true) ? get_post_meta(get_the_id(), 'mep_enable_recurring', true) : 'no';
    $mep_show_upcoming_event = get_post_meta(get_the_id(), 'mep_show_upcoming_event', true) ? get_post_meta(get_the_id(), 'mep_show_upcoming_event', true) : 'no';
    $cn                     = 1;

    if ($recurring == 'yes') {
        if (strtotime(current_time('Y-m-d H:i')) < strtotime($start_datetime)) {
            ?>
            <p><?php echo get_mep_datetime($start_datetime, 'date-text') . ' ' . get_mep_datetime($start_datetime, 'time'); ?> - <?php if ($start_date != $end_date) {
                    echo get_mep_datetime($end_datetime, 'date-text') . ' - ';
                }
                echo get_mep_datetime($end_datetime, 'time'); ?></p>,
            <?php
        }
        foreach ($more_date as $_more_date) {
            if (strtotime(current_time('Y-m-d H:i')) < strtotime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'])) {
                if ($mep_show_upcoming_event == 'yes') {
                    $cnt = 1;
                } else {
                    $cnt = $cn;
                }

                if ($cn == $cnt) {
                    ?>

                    <p><?php echo get_mep_datetime($_more_date['event_more_start_date'], 'date-text') . ' ' . get_mep_datetime($_more_date['event_more_start_time'], 'time'); ?> - <?php if ($_more_date['event_more_start_date'] != $_more_date['event_more_end_date']) {
                            echo get_mep_datetime($_more_date['event_more_end_date'], 'date-text') . ' - ';
                        }
                        echo get_mep_datetime($_more_date['event_more_end_time'], 'time'); ?></p>
                    <?php
                    $cn++;
                }
            }
        }
    } elseif (is_array($more_date) && sizeof($more_date) > 0) {
        ?>
        <p><?php echo get_mep_datetime($start_datetime, 'date-text') . ' ' . get_mep_datetime($start_datetime, 'time'); ?> - <?php if ($start_date != $end_date) {
                echo get_mep_datetime($end_datetime, 'date-text') . ' - ';
            }
            echo get_mep_datetime($end_datetime, 'time'); ?></p>
        <?php foreach ($more_date as $_more_date) {
            ?>

            <p><?php echo get_mep_datetime($_more_date['event_more_start_date'], 'date-text') . ' ' . get_mep_datetime($_more_date['event_more_start_time'], 'time'); ?> - <?php if ($_more_date['event_more_start_date'] != $_more_date['event_more_end_date']) {
                    echo get_mep_datetime($_more_date['event_more_end_date'], 'date-text') . ' - ';
                }
                echo get_mep_datetime($_more_date['event_more_end_time'], 'time'); ?></p>
            <?php
        }

    } else {
        ?>
        <p><?php echo get_mep_datetime($start_datetime, 'date-text') . ' ' . get_mep_datetime($start_datetime, 'time'); ?> - <?php if ($start_date != $end_date) {
                echo get_mep_datetime($end_datetime, 'date-text') . ' - ';
            }
            echo get_mep_datetime($end_datetime, 'time'); ?></p>
        <?php
    }
}



add_action('mep_event_date_default_theme', 'mep_date_in_default_theme');
function mep_date_in_default_theme($event_id){
    $event_meta                 = get_post_custom($event_id);
    $start_datetime             = $event_meta['event_start_datetime'][0];
    $start_date                 = $event_meta['event_start_date'][0];
    $start_time                 = $event_meta['event_start_time'][0];
    $end_datetime               = $event_meta['event_end_datetime'][0];
    $end_date                   = $event_meta['event_end_date'][0];
    $end_time                   = $event_meta['event_end_time'][0];
    $recurring                  = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
    $mep_show_upcoming_event    = get_post_meta($event_id, 'mep_show_upcoming_event', true) ? get_post_meta($event_id, 'mep_show_upcoming_event', true) : 'no';
    $cn                         = 1;
    $more_date                  = array_key_exists('mep_event_more_date', $event_meta) ? unserialize($event_meta['mep_event_more_date'][0]) : array();
    ?>
    <h3><i class="fa fa-calendar"></i> <?php _e('Event Schedule Details', 'mage-eventpress'); ?></h3>
    <?php
    echo '<ul>';

    if ($recurring == 'yes') {
        if (strtotime(current_time('Y-m-d H:i')) < strtotime($start_datetime)) {
            ?>
            <li><i class="fa fa-calendar"></i> <?php echo get_mep_datetime($start_datetime, 'date-text'); ?> <br><i class="fa fa-clock-o"></i> <?php echo get_mep_datetime($start_datetime, 'time'); ?> - <?php if ($start_date != $end_date) {
                    echo get_mep_datetime($end_datetime, 'date-text') . ' - ';
                }
                echo get_mep_datetime($end_datetime, 'time'); ?></li>
            <?php
        }
        foreach ($more_date as $_more_date) {
            if (strtotime(current_time('Y-m-d H:i')) < strtotime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'])) {
                if ($mep_show_upcoming_event == 'yes') {
                    $cnt = 1;
                } else {
                    $cnt = $cn;
                }
                if ($cn == $cnt) {
                    ?>
                    <li><i class="fa fa-calendar"></i> <?php echo get_mep_datetime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'date-text'); ?> <br><i class="fa fa-clock-o"></i> <?php echo get_mep_datetime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'time'); ?> - <?php if ($_more_date['event_more_start_date'] != $_more_date['event_more_end_date']) {
                            echo get_mep_datetime($_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'date-text') . ' - ';
                        }
                        echo get_mep_datetime($_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'time'); ?></li>
                    <?php
                    $cn++;
                }
            }
        }
    } else {
        if (is_array($more_date) && sizeof($more_date) > 0) {
            ?>
            <li><i class="fa fa-calendar"></i> <?php echo get_mep_datetime($start_datetime, 'date-text'); ?><br>
                <i class="fa fa-clock-o"></i> <?php echo get_mep_datetime($start_datetime, 'time'); ?> <?php if($start_date != $end_date) {
                    echo ' - '.get_mep_datetime($end_datetime, 'date-text');
                }
                echo ' - '.get_mep_datetime($end_datetime, 'time'); ?></li>
            <?php


            foreach ($more_date as $_more_date) {
                ?>
                <li><i class="fa fa-calendar"></i> <?php echo get_mep_datetime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'date-text'); ?> <br><i class="fa fa-clock-o"></i> <?php echo get_mep_datetime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'time'); ?> - <?php if ($_more_date['event_more_start_date'] != $_more_date['event_more_end_date']) {
                        echo get_mep_datetime($_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'date-text') . ' - ';
                    }
                    echo get_mep_datetime($_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'time'); ?></li>
                <?php
            }

        } else {

            ?>
            <li><i class="fa fa-calendar"></i> <?php echo get_mep_datetime($start_datetime, 'date-text'); ?> <br><i class="fa fa-clock-o"></i> <?php echo get_mep_datetime($start_datetime, 'time'); ?> - <?php if ($start_date != $end_date) {
                    echo get_mep_datetime($end_datetime, 'date-text') . ' - ';
                }
                echo get_mep_datetime($end_datetime, 'time'); ?></li>
            <?php
        }
    }
    echo '</ul>';
}


add_action('mep_event_date_only', 'mep_ev_date');
function mep_ev_date()
{
    global $event_meta;
    $start_datetime = $event_meta['event_start_datetime'][0];
    $start_date = $event_meta['event_start_date'][0];
    $start_time = $event_meta['event_start_time'][0];

    $end_datetime = $event_meta['event_end_datetime'][0];

    $end_date = $event_meta['event_end_date'][0];
    $end_time = $event_meta['event_end_time'][0];
    $cn = 1;
    $more_date = array($event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0]);
    $recurring = get_post_meta(get_the_id(), 'mep_enable_recurring', true) ? get_post_meta(get_the_id(), 'mep_enable_recurring', true) : 'no';
    $mep_show_upcoming_event = get_post_meta(get_the_id(), 'mep_show_upcoming_event', true) ? get_post_meta(get_the_id(), 'mep_show_upcoming_event', true) : 'no';


    if ($recurring == 'yes') {
        $event_more_dates = get_post_meta(get_the_id(), 'mep_event_more_date', true);
        foreach ($event_more_dates as $md) {
            $more_date[] = $md['event_more_start_date'] . ' ' . $md['event_more_start_time'];
        }

        foreach ($more_date as $ev_date) {
            if (strtotime(current_time('Y-m-d H:i:s')) < strtotime($ev_date)) {
                if ($mep_show_upcoming_event == 'yes') {
                    $cnt = 1;
                } else {
                    $cnt = $cn;
                }
                if ($cn == $cnt) {
                    ?>
                    <p><?php echo get_mep_datetime($ev_date, 'date-text'); ?></p>
                    <?php
                    $cn++;
                }
            }
        }
    } else {
        ?>
        <p><?php echo get_mep_datetime($start_datetime, 'date-text'); ?></p>
        <?php
    }
}


add_action('mep_event_time_only', 'mep_ev_time');
function mep_ev_time()
{
    global $event_meta;
    $start_datetime             = $event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0];
    $start_date                 = $event_meta['event_start_date'][0];
    $start_time                 = $event_meta['event_start_time'][0];
    $end_datetime               = $event_meta['event_end_date'][0] . ' ' . $event_meta['event_end_time'][0];
    $end_date                   = $event_meta['event_end_date'][0];
    $end_time                   = $event_meta['event_end_time'][0];
    $cn                         = 1;
    $more_date                  = array($event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0]);
    $recurring                  = get_post_meta(get_the_id(), 'mep_enable_recurring', true) ? get_post_meta(get_the_id(), 'mep_enable_recurring', true) : 'no';
    $mep_show_upcoming_event    = get_post_meta(get_the_id(), 'mep_show_upcoming_event', true) ? get_post_meta(get_the_id(), 'mep_show_upcoming_event', true) : 'no';


    if ($recurring == 'yes') {
        $event_more_dates       = get_post_meta(get_the_id(), 'mep_event_more_date', true);
        foreach ($event_more_dates as $md) {
            $more_date[]        = $md['event_more_start_date'] . ' ' . $md['event_more_start_time'];
        }

        foreach ($more_date as $ev_date) {
            if (strtotime(current_time('Y-m-d H:i:s')) < strtotime($ev_date)) {
                if ($mep_show_upcoming_event == 'yes') {
                    $cnt = 1;
                } else {
                    $cnt = $cn;
                }
                if ($cn == $cnt) {
                    ?>
                    <p><?php echo get_mep_datetime($ev_date, 'time'); ?> </p>
                    <?php
                    $cn++;
                }
            }
        }
    } else {
        ?>
        <p><?php echo get_mep_datetime($start_datetime, 'time'); ?></p>
        <?php
    }
}

function mep_ev_time_ticket($event_meta){
    $start_datetime = $event_meta['event_start_datetime'][0];
    echo get_mep_datetime($start_datetime, 'time');
}

function mep_ev_date_ticket($event_meta){
    $start_datetime = $event_meta['event_start_datetime'][0];
    echo get_mep_datetime($start_datetime, 'date-text');
}
