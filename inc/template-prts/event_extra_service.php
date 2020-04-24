<?php

add_action('mep_event_extra_service', 'mep_ev_extra_serv');
function mep_ev_extra_serv(){
    global $post, $product;
    $pid                        = $post->ID;
    $count                      = 1;
    $mep_events_extra_prices    = get_post_meta($post->ID, 'mep_events_extra_prices', true) ? get_post_meta($post->ID, 'mep_events_extra_prices', true) : array();
    ob_start();
    if (sizeof($mep_events_extra_prices) > 0) {
        echo "<h3 class='ex-sec-title'>" . mep_get_label($pid, 'mep_event_extra_service_text', 'Extra Service:') . "</h3>";
        ?>
        <table>
            <tr>
                <td align="left"><?php _e('Name', 'mage-eventpress'); ?></td>
                <td class="mage_text_center"><?php _e('Quantity', 'mage-eventpress'); ?></td>
                <td class="mage_text_center"><?php _e('Price', 'mage-eventpress'); ?></td>
            </tr>
            <?php
            foreach ($mep_events_extra_prices as $field) {
                $event_date = get_post_meta($post->ID, 'event_start_date', true).' '.get_post_meta($post->ID, 'event_start_time', true);
                $total_extra_service = (int)$field['option_qty'];
                $qty_type = $field['option_qty_type'];
                $total_sold = (int) mep_extra_service_sold(get_the_id(),$field['option_name'],$event_date);
                $ext_left = ($total_extra_service - $total_sold);
                ?>
                <tr>
                    <td align="Left"><?php echo $field['option_name']; ?>
                        <div class="xtra-item-left"><?php echo $ext_left; ?>
                            <?php echo mep_get_option('mep_left_text', 'label_setting_sec') ? mep_get_option('mep_left_text', 'label_setting_sec') : _e('Left:','mage-eventpress');  ?>
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
        <?php
    }
    $content = ob_get_clean();
    $event_meta = get_post_custom($pid);
    echo apply_filters('mage_event_extra_service_list', $content,$pid,$event_meta);

}
