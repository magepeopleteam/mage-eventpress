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
            require(mep_template_file_path('single/ticket_type_list.php'));
             $count++;
        } ?>

<?php
        $loop_list = ob_get_clean();
        echo apply_filters('mep_event_ticket_type_loop', $loop_list, $post_id);
    }
}
