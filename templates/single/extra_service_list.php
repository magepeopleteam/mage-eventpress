<h3 class='ex-sec-title'><?php echo mep_get_label($post_id, 'mep_event_extra_service_text', 'Extra Service:'); ?></h3>
            <table>
                <tr>
                    <td align="left"><?php echo mep_get_option('mep_name_text', 'label_setting_sec', __('Name:', 'mage-eventpress')); ?></td>
                    <td class="mage_text_center"><?php echo mep_get_option('mep_quantity_text', 'label_setting_sec', __('Quantity:', 'mage-eventpress')); ?></td>
                    <td class="mage_text_center"><?php echo mep_get_option('mep_price_text', 'label_setting_sec', __('Price:', 'mage-eventpress')); ?></td>
                </tr>
                <?php
                foreach ($mep_events_extra_prices as $field) {
                    $event_date = get_post_meta($post_id, 'event_start_date', true) . ' ' . get_post_meta($post_id, 'event_start_time', true);
                    $total_extra_service = (int) $field['option_qty'];
                    $qty_type = $field['option_qty_type'];
                    $total_sold = (int) mep_extra_service_sold($post_id, $field['option_name'], $event_date);
                    $ext_left = ($total_extra_service - $total_sold);
                ?>
                    <tr>
                        <td align="Left"><?php echo $field['option_name']; ?>
                            <div class="xtra-item-left"><?php echo $ext_left; ?>
                                <?php echo mep_get_option('mep_left_text', 'label_setting_sec') ? mep_get_option('mep_left_text', 'label_setting_sec') : _e('Left:', 'mage-eventpress');  ?>
                            </div>
                            <input type="hidden" name='mep_event_start_date_es[]' value='<?php echo $event_date; ?>'>
                        </td>
                        <td class="mage_text_center">
                            <?php
                            if ($ext_left > 0) {
                                if ($qty_type == 'dropdown') { ?>
                                    <select name="event_extra_service_qty[]" id="eventpxtp_<?php //echo $count;
                                                                                            ?>" class='extra-qty-box'>
                                        <?php for ($i = 0; $i <= $ext_left; $i++) { ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $field['option_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                <?php } else { ?>
                                    <div class="mage_input_group">
                                        <span class="fa fa-minus qty_dec"></span>
                                        <input id="eventpx" <?php //if($ext_left<=0){ echo "disabled"; }
                                                            ?> size="4" inputmode="numeric" type="text" class='extra-qty-box' name='event_extra_service_qty[]' data-price='<?php echo $field['option_price']; ?>' value='0' min="0" max="<?php echo $ext_left; ?>">
                                        <span class="fa fa-plus qty_inc"></span>
                                    </div>
                            <?php }
                            } else {
                                echo mep_get_option('mep_not_available_text', 'label_setting_sec') ? mep_get_option('mep_not_available_text', 'label_setting_sec') : _e('Not Available', 'mage-eventpress');
                            } ?>
                        </td>
                        <td class="mage_text_center"><?php echo wc_price($field['option_price']);
                                                        if ($ext_left > 0) { ?>
                                <p style="display: none;" class="price_jq"><?php echo $field['option_price']; ?></p>
                                <input type="hidden" name='event_extra_service_name[]' value='<?php echo $field['option_name']; ?>'>
                                <input type="hidden" name='event_extra_service_price[]' value='<?php echo $field['option_price']; ?>'>
                            <?php } ?>
                        </td>
                    </tr>
                <?php
                    $count++;
                }
                ?>
            </table>