<div class="mep-default-sidrbar-price-seat">
    <div class="df-seat">
        <strong>
            <?php echo mep_get_option('mep_total_seat_text', 'label_setting_sec', __('Total Seats:', 'mage-eventpress')) ; ?>
        <?php
        echo esc_html($total_seat); 
        if ($mep_available_seat == 'on') {
            ?>
            <?php echo mep_get_option('mep_left_text', 'label_setting_sec', __('| Left:', 'mage-eventpress')); ?>
            <?php echo esc_html(max($total_left, 0)); ?></strong>
        <?php } ?>
        </strong>
    </div>
</div>