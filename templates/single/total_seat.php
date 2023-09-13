<h5>
    <strong>
        <?php echo mep_get_option('mep_total_seat_text', 'label_setting_sec', __('Total Seats:', 'mage-eventpress')) ; ?>
    </strong>
    <?php
    echo esc_html($total_seat); 
    if ($mep_available_seat == 'on') {
        ?>
        (<strong><?php echo esc_html(max($total_left, 0)); ?></strong>
        <?php echo mep_get_option('mep_left_text', 'label_setting_sec', __('Left:', 'mage-eventpress')); ?>)
    <?php } ?>
</h5>