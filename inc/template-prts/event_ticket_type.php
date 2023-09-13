<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.  

add_action('mep_event_ticket_types', 'mep_ev_ticket_type',10,3);
if (!function_exists('mep_ev_ticket_type')) {
    function mep_ev_ticket_type($post_id,$ticket_type_label,$select_date_label)
    {
        global $post, $product, $event_meta; 
        $event_meta = get_post_custom($post_id);
        $count = 1;
        ob_start();
        $mep_available_seat     = array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';
        $mep_event_ticket_type  = get_post_meta($post_id, 'mep_event_ticket_type', true) ? get_post_meta($post_id, 'mep_event_ticket_type', true) : array();

        if ($mep_event_ticket_type) {
?>
            <!-- <h3 class='ex-sec-title mep_ticket_type_title'><?php echo esc_html($ticket_type_label); ?> </h3> -->
            <table id='mep_event_ticket_type_table'>
                <thead class='ex-sec-title mep_ticket_type_title'>
                    <tr>
                        <th> 
                        <span class="tkt-qty" style="text-align: left;">
                            <?php //_e('Ticket type','mage-eventpress'); ?> 
                            <?php echo mep_get_option('mep_event_ticket_type_text', 'label_setting_sec', __('Ticket type:', 'mage-eventpress'));  ?>                            
                        </span>
                        </th>
                        <th>
                        <span class="tkt-qty" style="text-align: center;">
                            <?php echo mep_get_option('mep_ticket_qty_text', 'label_setting_sec', __('Ticket Qty:', 'mage-eventpress'));  ?>
                        </span>
                        </th>
                        <th>
                        <span class="tkt-pric" style="text-align: center;">
                            <?php echo mep_get_option('mep_per_ticket_price_text', 'label_setting_sec', __('Per Ticket Price:', 'mage-eventpress'));  ?>
                        </span> 
                        </th>
                    </tr>
                </thead>
                <?php do_action('mep_event_ticket_type_loop_list', $post_id); ?>
            </table>
        <?php
        }

        $content = ob_get_clean();
        echo apply_filters('mage_event_ticket_type_list', $content, $post_id, $event_meta,$ticket_type_label,$select_date_label);
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.qty_dec').on('click', function() {
                    let target = $(this).siblings('input');
                    let value = parseInt(target.val()) - 1;
                    qtyPlace(target, value);
                });
                $('.qty_inc').on('click', function() {
                    let target = $(this).siblings('input');
                    let value = parseInt(target.val()) + 1;
                    qtyPlace(target, value);
                });
                $('.mage_input_group input').on('keyup', function() {
                    let target = $(this);
                    let value = parseInt(target.val());
                    if (target.val().length > 0) {
                        qtyPlace(target, value);
                    }

                });
                $('#mage_event_submit').on('submit', function(e) {
					//e.stopPropagation();
                    if (mageErrorQty()) {
                        return true;
                    }
                    return false;
                });
                $("select[name='option_qty[]']").on('blur', function() {
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

                function mageErrorQty() {
                    let total_ticket = 0;
                    let target = $("[name='option_qty[]']");
                    target.each(function(index) {
                        total_ticket = total_ticket + parseInt($(this).val());
                    });
                    if (total_ticket > 0) {
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
}
