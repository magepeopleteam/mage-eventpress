<tr>
                <?php do_action('mep_ticket_type_list_row_start',$field, $post_id); ?>
                    <td align="Left">
                        <span class='mep_ticket_type_name'> <?php echo array_key_exists('option_name_t',$field) ? esc_html($field['option_name_t']) : ""; ?></span>
                        <?php if(!empty($ticket_details)){ ?>
                        <div class="mep_ticket_details">
                            <p><?php echo esc_html($ticket_details); ?></p>
                        </div>
                        <?php } ?>
                    </td>
                    <td class="ticket-qty">
                        
                        <?php
                        $tic_price      =   mep_get_price_including_tax($post_id,$ticket_price);
                        $actual_price   =   mage_array_strip(wc_price(mep_get_price_including_tax($post_id,$ticket_price)));
                        $data_price     =   str_replace(get_woocommerce_currency_symbol(), '', $actual_price);
                        $data_price     =   str_replace(wc_get_price_thousand_separator(), '', $data_price);
						$data_price     =   str_replace(wc_get_price_decimal_separator(), '.', $data_price);
                        if ($total_left > 0) {
                            if ($qty_t_type == 'dropdown') { ?>
                                <select name="option_qty[]" id="eventpxtp_<?php echo esc_attr($count); ?>" <?php if ($total_left <= 0) { ?> style='display: none!important;' <?php } ?> class='extra-qty-box etp'>
                                    <?php
                                        if($total_min_seat > 0){
                                    ?>
                                        <option value="0" <?php echo esc_attr('Selected'); ?>>
                                            <?php _e('0 Ticket:', 'mage-eventpress');  ?>
                                        </option>
                                        <?php
                                    }

                                    for ($i = $total_min_seat; $i <= $total_left; $i++) { ?>
                                        <option value="<?php echo esc_attr($i); ?>" <?php if ($i == $default_quantity) { echo esc_attr('Selected'); } ?>><?php echo esc_html($i); ?>
                                            <?php echo mep_get_option('mep_ticket_text', 'label_setting_sec', __('Ticket:', 'mage-eventpress'));  ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            <?php } else { ?>

                                <div class="mage_input_group">
                                    <span class="fa fa-minus qty_dec"></span>
                                    <input id="eventpxtp_<?php echo esc_attr($count); ?>" type="text" class='extra-qty-box etp' name='option_qty[]' data-price='<?php echo esc_attr($data_price); ?>' value='<?php echo esc_attr($default_qty); ?>' min="<?php echo esc_attr($total_min_seat); ?>" max="<?php echo esc_attr(max($total_left, 0)); ?>">
                                    <span class="fa fa-plus qty_inc"></span>
                                </div>
                            <?php }
                        } else {
                            ?>
                            <input id="eventpxtp_<?php echo esc_attr($count); ?>" type="hidden" class='extra-qty-box etp' name='option_qty[]' data-price='0' value='0' min="0" max="0">
                            <?php echo mep_get_option('mep_no_seat_available_text', 'label_setting_sec', __('No Seat Availables', 'mage-eventpress'));
                        }
                            $ticket_name = array_key_exists('option_name_t',$field) ? mep_remove_apostopie($field['option_name_t']) : "";
                            do_action('mep_after_ticket_type_qty', $post_id, $ticket_name, $field, $default_quantity,$start_date);
                            do_action('mepgq_max_qty_hook',$post_id, max($total_ticket_left, 0)); 

                        ?>
                        <?php if ($mep_available_seat == 'on') { ?>                            
                            <div class="xtra-item-left"><?php echo esc_html(max($total_ticket_left, 0)); ?>
                            
                                <?php echo mep_get_option('mep_left_text', 'label_setting_sec', __('Left:', 'mage-eventpress'));  ?>
                            </div> 
                        <?php } ?>                        
                    </td>
                    <td class="ticket-price"><strong><?php echo wc_price(esc_html(mep_get_price_including_tax($post_id,$ticket_price))); ?></strong>
                        <?php if ($total_seats > 0) { ?>
                            <p style="display: none;" class="price_jq"><?php echo esc_html($tic_price) > 0 ? esc_html($tic_price) : 0;  ?></p>

                        <?php } ?>
                    </td>
                    <?php do_action('mep_ticket_type_list_row_end',$field, $post_id); ?>
                </tr>
                <tr> 
                    <td colspan="<?php echo apply_filters('mep_hidden_row_colspan_no',3); ?>" class='user-innnf'>
                    
                        <input type="hidden" name='mep_event_start_date[]' value="<?php echo esc_attr($start_date); ?>">
                        <input type="hidden" name='mep_event_end_date[]' value="<?php echo isset($end_date) && !empty($end_date) ? esc_attr($end_date) : ''; ?>">
                        <input type="hidden" name='option_name[]' value='<?php echo esc_attr($ticket_name); ?>'>
                        <input type="hidden" name='option_price[]' value='<?php echo esc_attr($ticket_price); ?>'>
                        <input type="hidden" name='max_qty[]' value='<?php if(array_key_exists('option_max_qty',$field)){ echo esc_attr($field['option_max_qty']); }else{ echo ''; } ?>'>
                        <div class="user-info-sec">
                            <div id="dadainfo_<?php echo esc_attr($count); ?>" class="dada-info"></div>
                        </div>
                    </td>
                </tr>