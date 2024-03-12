<h3 class='ex-sec-title mep_extra_service_title'><?php echo esc_html($extra_service_label); ?></h3>
<table id='mep_event_extra_service_table'>    
    <?php
    $mep_available_seat         = get_post_meta($post_id, 'mep_available_seat', true) ? get_post_meta($post_id, 'mep_available_seat', true) : 'on';
    foreach ($mep_events_extra_prices as $field) {

        $service_name       = array_key_exists('option_name', $field) ? $field['option_name'] : '';
        $service_qty        = array_key_exists('option_qty', $field) ? $field['option_qty'] : 0;
        $service_price      = array_key_exists('option_price', $field) ? $field['option_price'] : 0;
        $service_qty_type   = array_key_exists('option_qty_type', $field) ? $field['option_qty_type'] : 'input';

        $total_extra_service    = (int) $service_qty;
        $qty_type               = $service_qty_type;
        $total_sold             = (int) mep_extra_service_sold($post_id, $service_name, $event_date);
        $ext_left               = ($total_extra_service - $total_sold);

        $tic_price      = mep_get_price_including_tax($post_id, $service_price);
        $actual_price   = mage_array_strip(wc_price(mep_get_price_including_tax($post_id, $service_price)));
        $data_price     = str_replace(get_woocommerce_currency_symbol(), '', $actual_price);
        $data_price     = str_replace(wc_get_price_thousand_separator(), '', $data_price);
        $data_price     = str_replace(wc_get_price_decimal_separator(), '.', $data_price);
    ?>
        <tr>
            <td align="Left"><?php echo esc_html($service_name); ?>
            <?php if ($mep_available_seat == 'on') { ?>
                <div class="xtra-item-left"><?php echo esc_html($ext_left); ?>
                    <?php echo mep_get_option('mep_left_text', 'label_setting_sec', __('Left:', 'mage-eventpress'));  ?>
                </div>
                <?php } ?>
                
                <input type="hidden" name='mep_event_start_date_es[]' value='<?php echo esc_attr($event_date); ?>'>
            </td>
            <td class="mage_text_center">
                <?php
                if ($ext_left > 0) {
                    if ($qty_type == 'dropdown') { ?>
                        <select name="event_extra_service_qty[]" id="eventpxtp_" class='extra-qty-box'>
                            <?php for ($i = 0; $i <= $ext_left; $i++) { ?>
                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?> <?php echo esc_html($service_name); ?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <div class="mage_input_group">
                            <span class="fa fa-minus qty_dec"></span>
                            <input id="eventpx" size="4" inputmode="numeric" type="text" class='extra-qty-box' name='event_extra_service_qty[]' data-price='<?php echo esc_attr($data_price); ?>' value='0' min="0" max="<?php echo esc_attr($ext_left); ?>">
                            <span class="fa fa-plus qty_inc"></span>
                        </div>
                <?php }
                } else {
                    echo mep_get_option('mep_not_available_text', 'label_setting_sec', __('Not Available', 'mage-eventpress'));
                } ?>
            </td>
            <td class="mage_text_center"><?php echo wc_price(esc_html(mep_get_price_including_tax($post_id, $service_price)));
                                            if ($ext_left > 0) { ?>
                    <p style="display: none;" class="price_jq"><?php echo esc_html($tic_price) > 0 ? esc_html($tic_price) : 0;  ?></p>
                    <input type="hidden" name='event_extra_service_name[]' value='<?php echo esc_attr($service_name); ?>'>
                    <input type="hidden" name='event_extra_service_price[]' value='<?php echo esc_attr($service_price); ?>'>
                <?php } ?>
            </td>
        </tr>
    <?php
        $count++;
    }
    ?>
</table>