<li>
    <i class="fa fa-calendar"></i> <?php echo get_mep_datetime($start_datetime, 'date-text'); ?><br>
    <i class="fa fa-clock-o"></i> <?php echo get_mep_datetime($start_datetime, 'time'); ?> <?php if ($start_date != $end_date) { echo ' - ' . get_mep_datetime($end_datetime, 'date-text'); } echo ' - ' . get_mep_datetime($end_datetime, 'time'); ?>
</li>