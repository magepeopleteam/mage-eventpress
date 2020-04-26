<?php
add_action('mep_event_ticket_types','mep_ev_ticket_type');
function mep_ev_ticket_type($post_id){
    global $post, $product,$event_meta;
    $count=1;
    ob_start();
    $mep_available_seat     = array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';
    $mep_event_ticket_type  = get_post_meta($post_id, 'mep_event_ticket_type', true) ? get_post_meta($post_id, 'mep_event_ticket_type', true) : array();

    if($mep_event_ticket_type){
        ?>
        <?php echo "<h3 class='ex-sec-title'>".mep_get_label($post_id,'mep_event_ticket_type_text','Ticket Type:')."</h3>"; ?>    

        <table>
            <?php
            $count =1;
            foreach ( $mep_event_ticket_type as $field ) {
                $qty_t_type             = $field['option_qty_t_type'];
                $total_quantity         = isset($field['option_qty_t']) ? $field['option_qty_t'] : 0;
                $default_qty            = isset($field['option_default_qty_t']) && $field['option_default_qty_t'] > 0 ? $field['option_default_qty_t'] : 0;
                $total_resv_quantity    = isset($field['option_rsv_t']) ? $field['option_rsv_t'] : 0;
                $event_date             = get_post_meta($post_id, 'event_start_date', true).' '.get_post_meta($post_id, 'event_start_time', true);
                $total_sold             = (int) mep_ticket_type_sold($post_id,$field['option_name_t'],$event_date);
                $total_tickets          = (int) $total_quantity - ((int) $total_sold + (int) $total_resv_quantity);
                $total_seats            = apply_filters('mep_total_ticket_of_type',$total_tickets,$post_id,$field);
                $total_min_seat         = apply_filters('mep_ticket_min_qty',0,$post_id,$field);
                $default_quantity       = apply_filters('mep_ticket_default_qty',$default_qty,$post_id,$field);
                $total_left             = apply_filters('mep_total_ticket_of_type',$total_tickets,$post_id,$field);
                $passed                 =  apply_filters('mep_ticket_type_validation',true);
                ?>
                <tr>
                    <td align="Left"><?php echo $field['option_name_t']; ?>
                    <input type="hidden" name='mep_event_start_date[]' value="<?php echo get_post_meta($post_id, 'event_start_datetime', true); ?>">
                        <?php if($mep_available_seat=='on'){ ?><div class="xtra-item-left"><?php echo max($total_left,0); ?>

                            <?php echo mep_get_option('mep_left_text', 'label_setting_sec') ? mep_get_option('mep_left_text', 'label_setting_sec') : _e('Left:','mage-eventpress');  ?>

                            </div> <?php } ?>
                    </td>
                    <td class="ticket-qty">
                        <span class="tkt-qty">
                            <?php echo mep_get_option('mep_ticket_qty_text', 'label_setting_sec') ? mep_get_option('mep_ticket_qty_text', 'label_setting_sec') : _e('Ticket Qty:','mage-eventpress');  ?>
                        </span>
                        <?php
                        if($total_left>0){
                            if($qty_t_type=='dropdown'){ ?>
                                <select name="option_qty[]" id="eventpxtp_<?php echo $count; ?>" <?php  if($total_left<=0){ ?> style='display: none!important;' <?php } ?> class='extra-qty-box etp'>
                                    <?php
                                        for ($i = $total_min_seat; $i <= $total_left; $i++) { ?>
                                           <option value="<?php echo $i; ?>" <?php if($i == $default_quantity){ echo 'Selected'; } ?>><?php echo $i; ?>
                                                <?php echo mep_get_option('mep_ticket_text', 'label_setting_sec') ? mep_get_option('mep_ticket_text', 'label_setting_sec') : _e('Ticket:','mage-eventpress');  ?>
                                            </option>
                                        <?php } ?>
                                </select>
                            <?php }else{ ?>

                                    <div class="mage_input_group">
                                        <span class="fa fa-minus qty_dec"></span>
                                        <!--input id="eventpxtp_<?php echo $count; ?>" <?php //if($ext_left<=0){ echo "disabled"; } ?> type="text" class='extra-qty-box etp' name='option_qty[]' data-price='<?php echo $field['option_price_t']; ?>' value='<?php echo $default_quantity; ?>' min="<?php echo $default_quantity; ?>" max="<?php echo max($total_left,0); ?>"-->
                                         <input id="eventpxtp_<?php echo $count; ?>" <?php //if($ext_left<=0){ echo "disabled"; } ?> type="text" class='extra-qty-box etp' name='option_qty[]' data-price='<?php echo $field['option_price_t']; ?>' value='<?php echo $default_quantity; ?>' min="<?php echo $total_min_seat; ?>" max="<?php echo max($total_seats,0); ?>">
                                        <span class="fa fa-plus qty_inc"></span>
                                    </div>
                                        <?php } }else{ _e('No Seat Available','mage-eventpress'); } 
                                        $ticket_name = $field['option_name_t'];
                                        do_action('mep_after_ticket_type_qty',$post_id,$ticket_name,$field,$default_quantity);
                                        ?>
                                        
                                        

                    </td>
                    <td class="ticket-price"><span class="tkt-pric">

<?php echo mep_get_option('mep_per_ticket_price_text', 'label_setting_sec') ? mep_get_option('mep_per_ticket_price_text', 'label_setting_sec') : _e('Per Ticket Price:','mage-eventpress');  ?>  
</span>  <strong><?php echo wc_price($field['option_price_t']); ?></strong>
                        <?php if($total_left>0){ ?>
                            <p style="display: none;" class="price_jq"><?php echo $field['option_price_t']; ?></p>
                            <input type="hidden" name='option_name[]' value='<?php echo $field['option_name_t']; ?>'>
                            <input type="hidden" name='option_price[]' value='<?php echo $field['option_price_t']; ?>'>
                            <input type="hidden" name='max_qty[]' value='<?php echo $field['option_max_qty']; ?>'>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class='user-innnf'> <div class="user-info-sec">
                            <div id="dadainfo_<?php echo $count; ?>" class="dada-info"></div></div>
                    </td>
                </tr>
                <?php $count++; } ?>
        </table>
        <?php
    }

    $content = ob_get_clean();
    echo apply_filters('mage_event_ticket_type_list', $content,$post_id,$event_meta);
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.qty_dec').on('click', function () {
                let target = $(this).siblings('input');
                let value = parseInt(target.val()) - 1;
                qtyPlace(target, value);
            });
            $('.qty_inc').on('click', function () {
                let target = $(this).siblings('input');
                let value = parseInt(target.val()) + 1;
                qtyPlace(target, value);
            });
            $('.mage_input_group input').on('keyup', function () {
                let target = $(this);
                let value = parseInt(target.val());
                if(target.val().length>0){
                    qtyPlace(target, value);
                }

            });
            $('#mage_event_submit').on('submit', function () {
                if(mageErrorQty()){
                    return true;
                }
                return false;
            });
            $("select[name='option_qty[]']").on('blur', function () {
                            mageErrorQty();
                        });
            function qtyPlace(target, value) {
                let minSeat = parseInt(target.attr('min'));
                let maxSeat = parseInt(target.attr('max'));
                if (value < minSeat || isNaN(value)) {
                    value = minSeat;
                }
                if (value > maxSeat) {
                    value = maxSeat
                }
                target.val(value).change();
                mageErrorQty();

            }
            function mageErrorQty(){
                let total_ticket = 0;
                let target=$("[name='option_qty[]']");
                target.each(function (index) {
                    total_ticket = total_ticket + parseInt($(this).val());
                });
                if(total_ticket>0){
                    target.removeClass('mage_error');
                    return true;
                }
                target.addClass('mage_error');                
                return false;
            }

        });
    </script>
    <?php
}