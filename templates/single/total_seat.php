<div class="mep-default-sidrbar-price-seat">
    <div class="setas-info">
        <div class="total-seats">
            <div><?php echo mep_get_option('mep_total_seat_text', 'label_setting_sec', __('Total Seats', 'mage-eventpress')) ; ?></div>
            <strong><?php echo esc_html($total_seat); ?></strong>
        </div>
        <div class="available-seats">
            <?php if ($mep_available_seat == 'on') : ?>
                <div><?php echo mep_get_option('mep_left_text', 'label_setting_sec', __('Available', 'mage-eventpress')); ?></div>
                <strong><?php echo esc_html($total_left); ?></strong>
            <?php endif; ?>
        </div>
    </div>
</div>