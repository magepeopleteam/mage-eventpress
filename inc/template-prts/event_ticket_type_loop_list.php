<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_ticket_type_loop_list', 'mep_event_ticket_type_loop_list_html');
if (!function_exists('mep_event_ticket_type_loop_list_html')) {
    function mep_event_ticket_type_loop_list_html($post_id)
    {
        $mep_available_seat     = get_post_meta($post_id, 'mep_available_seat', true) ? get_post_meta($post_id, 'mep_available_seat', true) : 'on';
        $mep_event_ticket_type  = get_post_meta($post_id, 'mep_event_ticket_type', true) ? get_post_meta($post_id, 'mep_event_ticket_type', true) : array();
        ob_start();
?>
<<<<<<< HEAD
    <?php
    $count = 1;
    foreach ($mep_event_ticket_type as $field) {
        $qty_t_type             = $field['option_qty_t_type'];
        $total_quantity         = isset($field['option_qty_t']) ? $field['option_qty_t'] : 0;
        $default_qty            = isset($field['option_default_qty_t']) && $field['option_default_qty_t'] > 0 ? $field['option_default_qty_t'] : 0;
        $total_resv_quantity    = isset($field['option_rsv_t']) ? $field['option_rsv_t'] : 0;
        $event_date             = get_post_meta($post_id, 'event_start_date', true) . ' ' . get_post_meta($post_id, 'event_start_time', true);
        $total_sold             = (int) mep_ticket_type_sold($post_id, $field['option_name_t'], $event_date);
        $total_tickets          = (int) $total_quantity - ((int) $total_sold + (int) $total_resv_quantity);
        $total_seats            = apply_filters('mep_total_ticket_of_type', $total_tickets, $post_id, $field);
        $total_min_seat         = apply_filters('mep_ticket_min_qty', 0, $post_id, $field);
        $default_quantity       = apply_filters('mep_ticket_default_qty', $default_qty, $post_id, $field);
        $total_left             = apply_filters('mep_total_ticket_of_type', $total_tickets, $post_id, $field);
        $ticket_price           = apply_filters('mep_ticket_type_price', $field['option_price_t'], $field['option_name_t'], $post_id, $field);
        $passed                 = apply_filters('mep_ticket_type_validation', true);

        
    ?>
        <tr>
            <td align="Left"><?php echo $field['option_name_t']; ?>
                <input type="hidden" name='mep_event_start_date[]' value="<?php echo get_post_meta($post_id, 'event_start_datetime', true); ?>">
                <?php if ($mep_available_seat == 'on') { ?><div class="xtra-item-left"><?php echo max($total_left, 0); ?>

                        <?php echo mep_get_option('mep_left_text', 'label_setting_sec') ? mep_get_option('mep_left_text', 'label_setting_sec') : _e('Left:', 'mage-eventpress');  ?>

                    </div> <?php } ?>
            </td>
            <td class="ticket-qty">
                <span class="tkt-qty">
                    <?php echo mep_get_option('mep_ticket_qty_text', 'label_setting_sec') ? mep_get_option('mep_ticket_qty_text', 'label_setting_sec') : _e('Ticket Qty:', 'mage-eventpress');  ?>
                </span>
                <?php
                if ($total_left > 0) {
                    if ($qty_t_type == 'dropdown') { ?>
                        <select name="option_qty[]" id="eventpxtp_<?php echo $count; ?>" <?php if ($total_left <= 0) { ?> style='display: none!important;' <?php } ?> class='extra-qty-box etp'>
                            <?php
                            for ($i = $total_min_seat; $i <= $total_left; $i++) { ?>
                                <option value="<?php echo $i; ?>" <?php if ($i == $default_quantity) {
                                                                        echo 'Selected';
                                                                    } ?>><?php echo $i; ?>
                                    <?php echo mep_get_option('mep_ticket_text', 'label_setting_sec') ? mep_get_option('mep_ticket_text', 'label_setting_sec') : _e('Ticket:', 'mage-eventpress');  ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>

                        <div class="mage_input_group">
                            <span class="fa fa-minus qty_dec"></span>
                            <input id="eventpxtp_<?php echo $count; ?>" type="text" class='extra-qty-box etp' name='option_qty[]' data-price='<?php echo $ticket_price; ?>' value='<?php echo $default_quantity; ?>' min="<?php echo $total_min_seat; ?>" max="<?php echo max($total_seats, 0); ?>">
                            <span class="fa fa-plus qty_inc"></span>
                        </div>
                <?php }
                } else {
                    _e('No Seat Available', 'mage-eventpress');
                }
                $ticket_name = $field['option_name_t'];
                do_action('mep_after_ticket_type_qty', $post_id, $ticket_name, $field, $default_quantity);
                ?>



            </td>
            <td class="ticket-price"><span class="tkt-pric">

                    <?php echo mep_get_option('mep_per_ticket_price_text', 'label_setting_sec') ? mep_get_option('mep_per_ticket_price_text', 'label_setting_sec') : _e('Per Ticket Price:', 'mage-eventpress');  ?>
                </span> <strong><?php echo wc_price($ticket_price); ?></strong>

                <?php if ($total_left > 0) { ?>
                    <p style="display: none;" class="price_jq"><?php echo $ticket_price; ?></p>
                    <input type="hidden" name='option_name[]' value='<?php echo $field['option_name_t']; ?>'>
                    <input type="hidden" name='option_price[]' value='<?php echo $ticket_price; ?>'>
                    <input type="hidden" name='max_qty[]' value='<?php echo $field['option_max_qty']; ?>'>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td colspan="3" class='user-innnf'>
                <div class="user-info-sec">
                    <div id="dadainfo_<?php echo $count; ?>" class="dada-info"></div>
                </div>
            </td>
        </tr>
    <?php $count++;
    } ?>
=======
        <?php
        $count = 1;
        foreach ($mep_event_ticket_type as $field) {
            $ticket_type_name       = array_key_exists('option_name_t',$field)  ? $field['option_name_t'] : '';
            $ticket_type            = array_key_exists('option_qty_t_type',$field)  ? $field['option_qty_t_type'] : '';
            $ticket_type_qty        = array_key_exists('option_qty_t',$field) ? $field['option_qty_t'] : 0;
            $ticket_type_price      = array_key_exists('option_price_t',$field) ? $field['option_price_t'] : 0;
            $qty_t_type             = $ticket_type;
            $total_quantity         = isset($field['option_qty_t']) ? $field['option_qty_t'] : 0;
            $default_qty            = isset($field['option_default_qty_t']) && $field['option_default_qty_t'] > 0 ? $field['option_default_qty_t'] : 0;
            $total_resv_quantity    = isset($field['option_rsv_t']) ? $field['option_rsv_t'] : 0;
            $event_date             = get_post_meta($post_id, 'event_start_date', true) . ' ' . get_post_meta($post_id, 'event_start_time', true);
            $total_sold             = (int) mep_ticket_type_sold($post_id, $ticket_type_name, $event_date);
            $total_tickets          = (int) $total_quantity - ((int) $total_sold + (int) $total_resv_quantity);
            $total_seats            = apply_filters('mep_total_ticket_of_type', $total_tickets, $post_id, $field);
            $total_min_seat         = apply_filters('mep_ticket_min_qty', 0, $post_id, $field);
            $default_quantity       = apply_filters('mep_ticket_default_qty', $default_qty, $post_id, $field);
            $total_left             = apply_filters('mep_total_ticket_of_type', $total_tickets, $post_id, $field);
            $ticket_price           = apply_filters('mep_ticket_type_price', $ticket_type_price, $ticket_type_name, $post_id, $field);
            $passed                 = apply_filters('mep_ticket_type_validation', true);
            $start_date = get_post_meta($post_id, 'event_start_datetime', true);
            require(mep_template_file_path('single/ticket_type_list.php'));
             $count++;
        } ?>
>>>>>>> d7717dcdf9a7bf6ce93986f4c74fcc7846491831

<?php
        $loop_list = ob_get_clean();
        echo apply_filters('mep_event_ticket_type_loop', $loop_list, $post_id);
    }
}
