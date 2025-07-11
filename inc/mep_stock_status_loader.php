<?php
/**
 * Stock Status Loader
 * 
 * This file loads all stock status related functionality and CSS.
 * 
 * @package MagePeople Event Press
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Include the frontend stock status file
 */
require_once dirname(__FILE__) . '/frontend/mep_frontend_stock_status.php';

/**
 * Include the email settings file
 */
require_once dirname(__FILE__) . '/frontend/mep_email_settings.php';

/**
 * Register and enqueue stock status CSS
 * 
 * @return void
 */
function mep_stock_status_enqueue_styles() {
    wp_register_style(
        'mep-stock-status-styles',
        plugins_url('/assets/frontend/css/mep_stock_status.css', dirname(__FILE__)),
        array()
    );
    
    wp_enqueue_style('mep-stock-status-styles');
}
add_action('wp_enqueue_scripts', 'mep_stock_status_enqueue_styles');

/**
 * Add email notification for low stock
 * 
 * This function checks if an event has low stock and triggers notifications
 * 
 * @param int $event_id The event ID
 * @param string $ticket_type_name The ticket type name
 * @param int $available_seats Number of available seats
 * @return void
 */
function mep_check_low_stock_and_notify($event_id, $ticket_type_name, $available_seats) {
    // Skip if event ID is not provided
    if (!$event_id || !is_numeric($event_id)) {
        return;
    }
    
    // Check if low stock warning should be shown
    if (function_exists('mep_should_show_low_stock_warning') && mep_should_show_low_stock_warning($available_seats, $event_id)) {
        // Trigger low stock notification
        do_action('mep_event_low_stock_notification', $available_seats, $event_id);
    } else {
        // Reset notification flag if stock is no longer low
        // This will allow sending notifications again if stock becomes low in the future
        delete_post_meta($event_id, '_mep_low_stock_notification_sent');
    }
}
add_action('mep_after_ticket_type_qty', 'mep_check_low_stock_and_notify', 10, 3);

/**
 * Add admin notice for low stock events
 * 
 * Displays a warning notice in the admin when editing an event with low stock
 * 
 * @return void
 */
function mep_low_stock_admin_notice() {
    // Only show on event edit screens
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'mep_events') {
        return;
    }
    
    // Get the event ID
    $event_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    if (!$event_id) {
        return;
    }
    
    // Check if this event has low stock
    $ticket_types = get_post_meta($event_id, 'mep_event_ticket_type', true);
    if (!is_array($ticket_types) || empty($ticket_types)) {
        return;
    }
    
    $low_stock_threshold = (int)mep_get_option('mep_low_stock_threshold', 'general_setting_sec', 3);
    $low_stock_found = false;
    
    foreach ($ticket_types as $ticket_type) {
        $ticket_name = array_key_exists('option_name_t', $ticket_type) ? $ticket_type['option_name_t'] : '';
        $total_qty = array_key_exists('option_qty_t', $ticket_type) ? $ticket_type['option_qty_t'] : 0;
        $total_resv = array_key_exists('option_rsv_t', $ticket_type) ? $ticket_type['option_rsv_t'] : 0;
        
        $event_date = get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
        $total_sold = mep_get_ticket_type_seat_count($event_id, $ticket_name, $event_date, $total_qty, $total_resv);
        $available_seats = (int)$total_qty - ((int)$total_sold + (int)$total_resv);
        
        if ($available_seats > 0 && $available_seats <= $low_stock_threshold) {
            $low_stock_found = true;
            break;
        }
    }
    
    if ($low_stock_found) {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php esc_html_e('Low Stock Alert:', 'mage-eventpress'); ?></strong> 
                <?php esc_html_e('This event has tickets with low stock. Please check ticket availability.', 'mage-eventpress'); ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'mep_low_stock_admin_notice'); 