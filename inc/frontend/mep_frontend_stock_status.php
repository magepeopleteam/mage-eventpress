<?php
/**
 * Frontend Stock Status Display Functions
 * 
 * This file contains all functions related to displaying stock status,
 * low stock warnings, limited availability ribbons, and stock-related
 * email notifications in the frontend.
 * 
 * @package MagePeople Event Press
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle all frontend stock status related functionality
 */
class MPWEM_Frontend_Stock_Status {

    /**
     * Constructor to initialize hooks and filters
     */
    public function __construct() {
        // Add hooks for ticket type list
        add_action('mep_ticket_type_list_low_stock_warning', array($this, 'display_low_stock_warning'), 10, 2);
        add_action('mep_ticket_type_list_limited_ribbon', array($this, 'display_limited_availability_ribbon'), 10, 2);
        
        // Add hooks for ticket type layout
        add_action('mep_ticket_type_low_stock_warning', array($this, 'display_low_stock_warning'), 10, 2);
        add_action('mep_ticket_type_limited_ribbon', array($this, 'display_limited_availability_ribbon'), 10, 2);
        
        // Filter to check if low stock warning should be shown
        add_filter('mep_show_low_stock_warning', array($this, 'should_show_low_stock_warning'), 10, 2);
        
        // Filter to check if limited availability ribbon should be shown
        add_filter('mep_show_limited_availability_ribbon', array($this, 'should_show_limited_availability_ribbon'), 10, 2);
        
        // Email notification for low stock
        add_action('mep_event_low_stock_notification', array($this, 'send_low_stock_email_notification'), 10, 2);
        
        // Schedule daily stock check
        add_action('wp', array($this, 'schedule_daily_stock_check'));
        
        // Add action for daily stock check
        add_action('mep_daily_stock_check', array($this, 'check_all_events_for_low_stock'));
    }

    /**
     * Display low stock warning
     *
     * @param int $available_seats Number of available seats
     * @param int $event_id Event ID
     * @return void
     */
    public function display_low_stock_warning($available_seats, $event_id) {
        // Check if low stock warning should be shown
        if (!$this->should_show_low_stock_warning($available_seats, $event_id)) {
            return;
        }
        
        // Get low stock text and format it
        $low_stock_text = mep_get_option('mep_low_stock_text', 'general_setting_sec', 'Hurry! Only %s seats left');
        $warning_text = sprintf($low_stock_text, $available_seats);
        
        // Output the warning HTML
        ?>
        <div class="mep-low-stock-warning">
            <i class="fa fa-exclamation-circle"></i>
            <span class="mep-low-stock-warning-text"><?php echo esc_html($warning_text); ?></span>
        </div>
        <?php
    }

    /**
     * Display limited availability ribbon
     *
     * @param int $available_seats Number of available seats
     * @param int $event_id Event ID
     * @return void
     */
    public function display_limited_availability_ribbon($available_seats, $event_id) {
        // Check if limited availability ribbon should be shown
        if (!$this->should_show_limited_availability_ribbon($available_seats, $event_id)) {
            return;
        }
        
        // Output the ribbon HTML
        ?>
        <div class="mep-limited-ribbon-section">
            <span class="mep-limited-ribbon"><?php _e('Limited Availability', 'mage-eventpress'); ?></span>
        </div>
        <?php
    }

    /**
     * Check if low stock warning should be shown
     *
     * @param int $available_seats Number of available seats
     * @param int $event_id Event ID
     * @return bool
     */
    public function should_show_low_stock_warning($available_seats, $event_id) {
        // Get settings
        $show_low_stock_warning = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        $low_stock_threshold = (int)mep_get_option('mep_low_stock_threshold', 'general_setting_sec', 3);
        
        // Allow filtering of low stock threshold per event
        $low_stock_threshold = apply_filters('mep_low_stock_threshold', $low_stock_threshold, $event_id);
        
        // Check if warning should be shown
        $should_show = ($show_low_stock_warning === 'yes' && $available_seats > 0 && $available_seats <= $low_stock_threshold);
        
        return $should_show;
    }

    /**
     * Check if limited availability ribbon should be shown
     *
     * @param int $available_seats Number of available seats
     * @param int $event_id Event ID
     * @return bool
     */
    public function should_show_limited_availability_ribbon($available_seats, $event_id) {
        // Get settings
        $show_limited_availability = mep_get_option('mep_show_limited_availability_ribbon', 'general_setting_sec', 'no');
        $limited_availability_threshold = (int)mep_get_option('mep_limited_availability_threshold', 'general_setting_sec', 5);
        
        // Allow filtering of limited availability threshold per event
        $limited_availability_threshold = apply_filters('mep_limited_availability_threshold', $limited_availability_threshold, $event_id);
        
        // Check if ribbon should be shown
        return ($show_limited_availability === 'yes' && $available_seats > 0 && $available_seats <= $limited_availability_threshold);
    }

    /**
     * Send email notification for low stock
     *
     * @param int $available_seats Number of available seats
     * @param int $event_id Event ID
     * @return void
     */
    public function send_low_stock_email_notification($available_seats, $event_id) {
        // Check if low stock email notifications are enabled
        $enable_low_stock_email = mep_get_option('mep_enable_low_stock_email', 'email_setting_sec', 'yes');
        
        if ($enable_low_stock_email !== 'yes') {
            return;
        }
        
        // Check if notification was already sent for this event
        $notification_sent = get_post_meta($event_id, '_mep_low_stock_notification_sent', true);
        if ($notification_sent) {
            return;
        }
        
        // Get event details
        $event_title = get_the_title($event_id);
        $event_link = get_permalink($event_id);
        $event_date = get_post_meta($event_id, 'event_start_date', true);
        $event_time = get_post_meta($event_id, 'event_start_time', true);
        
        // Get admin email
        $admin_email = get_option('admin_email');
        
        // Get email settings
        $recipient = mep_get_option('mep_low_stock_email_recipient', 'email_setting_sec', $admin_email);
        $global_email_form_email = mep_get_option('mep_email_form_email', 'email_setting_sec', $admin_email);
        $global_email_form_name = mep_get_option('mep_email_form_name', 'email_setting_sec', get_bloginfo('name'));
        
        // Email subject
        $subject_template = mep_get_option('mep_low_stock_email_subject', 'email_setting_sec', 'Low Stock Alert: {event_name}');
        $subject = str_replace(
            array('{event_name}', '{event_date}', '{event_time}', '{available_seats}'),
            array($event_title, $event_date, $event_time, $available_seats),
            $subject_template
        );
        
        // Email body
        $body_template = mep_get_option('mep_low_stock_email_body', 'email_setting_sec', 'The event "{event_name}" is running low on tickets.');
        $message = str_replace(
            array('{event_name}', '{event_date}', '{event_time}', '{available_seats}', '{event_link}'),
            array($event_title, $event_date, $event_time, $available_seats, $event_link),
            $body_template
        );
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            sprintf('From: %s <%s>', $global_email_form_name, $global_email_form_email)
        );
        
        // Send email
        $email_sent = wp_mail($recipient, $subject, nl2br($message), $headers);
        
        // Mark notification as sent
        if ($email_sent) {
            update_post_meta($event_id, '_mep_low_stock_notification_sent', current_time('timestamp'));
        }
    }
    
    /**
     * Schedule daily stock check
     */
    public function schedule_daily_stock_check() {
        if (!wp_next_scheduled('mep_daily_stock_check')) {
            wp_schedule_event(time(), 'daily', 'mep_daily_stock_check');
        }
    }
    
    /**
     * Check all events for low stock
     * 
     * @return void
     */
    public function check_all_events_for_low_stock() {
        // Get all published events with pagination for better performance
        $per_page = apply_filters('mep_low_stock_check_batch_size', 50);
        $paged = 1;
        
        do {
            $events = get_posts(array(
                'post_type' => 'mep_events',
                'post_status' => 'publish',
                'posts_per_page' => $per_page,
                'paged' => $paged,
                'meta_query' => array(
                    array(
                        'key' => 'mep_event_ticket_type',
                        'compare' => 'EXISTS'
                    )
                )
            ));
            
            if (empty($events)) {
                break;
            }
            
            foreach ($events as $event) {
                $event_id = $event->ID;
                $ticket_types = get_post_meta($event_id, 'mep_event_ticket_type', true);
                
                if (!is_array($ticket_types) || empty($ticket_types)) {
                    continue;
                }
                
                $low_stock_threshold = (int)mep_get_option('mep_low_stock_threshold', 'general_setting_sec', 3);
                
                foreach ($ticket_types as $ticket_type) {
                    $ticket_name = array_key_exists('option_name_t', $ticket_type) ? $ticket_type['option_name_t'] : '';
                    $total_qty = array_key_exists('option_qty_t', $ticket_type) ? $ticket_type['option_qty_t'] : 0;
                    $total_resv = array_key_exists('option_rsv_t', $ticket_type) ? $ticket_type['option_rsv_t'] : 0;
                    
                    $event_date = get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
                    $total_sold = mep_get_ticket_type_seat_count($event_id, $ticket_name, $event_date, $total_qty, $total_resv);
                    $available_seats = (int)$total_qty - ((int)$total_sold + (int)$total_resv);
                    
                    if ($available_seats > 0 && $available_seats <= $low_stock_threshold) {
                        // Reset notification flag if it's been more than 24 hours since the last notification
                        $notification_sent = get_post_meta($event_id, '_mep_low_stock_notification_sent', true);
                        $reset_hours = apply_filters('mep_low_stock_notification_reset_hours', 24);
                        
                        if ($notification_sent && (current_time('timestamp') - $notification_sent) > ($reset_hours * HOUR_IN_SECONDS)) {
                            delete_post_meta($event_id, '_mep_low_stock_notification_sent');
                        }
                        
                        // Trigger low stock notification
                        do_action('mep_event_low_stock_notification', $available_seats, $event_id);
                        
                        // Only need to check one ticket type per event
                        break;
                    }
                }
            }
            
            $paged++;
            
        } while (count($events) === $per_page);
    }
    
    /**
     * Reset low stock notification flag when ticket stock is updated
     * 
     * @param int $post_id Post ID
     * @return void
     */
    public function reset_notification_flag($post_id) {
        // Only run for event post type
        if (get_post_type($post_id) !== 'mep_events') {
            return;
        }
        
        // Check if this is a valid event with ticket types
        $ticket_types = get_post_meta($post_id, 'mep_event_ticket_type', true);
        if (!is_array($ticket_types) || empty($ticket_types)) {
            return;
        }
        
        // Reset the notification flag when the event is updated
        delete_post_meta($post_id, '_mep_low_stock_notification_sent');
        
        // Allow developers to perform additional actions when notification flag is reset
        do_action('mep_after_low_stock_notification_reset', $post_id);
    }
}

// Initialize the class
$mpwem_frontend_stock_status = new MPWEM_Frontend_Stock_Status();

// Add hook to reset notification flag when event is updated
add_action('save_post', array($mpwem_frontend_stock_status, 'reset_notification_flag'), 10, 1);

/**
 * Helper function to display low stock warning
 *
 * @param int $available_seats Number of available seats
 * @param int $event_id Event ID
 * @return void
 */
function mep_display_low_stock_warning($available_seats, $event_id = 0) {
    do_action('mep_ticket_type_list_low_stock_warning', $available_seats, $event_id);
}

/**
 * Helper function to display limited availability ribbon
 *
 * @param int $available_seats Number of available seats
 * @param int $event_id Event ID
 * @return void
 */
function mep_display_limited_availability_ribbon($available_seats, $event_id = 0) {
    do_action('mep_ticket_type_list_limited_ribbon', $available_seats, $event_id);
}

/**
 * Helper function to check if low stock warning should be shown
 *
 * @param int $available_seats Number of available seats
 * @param int $event_id Event ID
 * @return bool
 */
function mep_should_show_low_stock_warning($available_seats, $event_id = 0) {
    return apply_filters('mep_show_low_stock_warning', false, $available_seats, $event_id);
}

/**
 * Helper function to check if limited availability ribbon should be shown
 *
 * @param int $available_seats Number of available seats
 * @param int $event_id Event ID
 * @return bool
 */
function mep_should_show_limited_availability_ribbon($available_seats, $event_id = 0) {
    return apply_filters('mep_show_limited_availability_ribbon', false, $available_seats, $event_id);
}

/**
 * Manually trigger stock check for all events (for testing)
 * 
 * Usage: add ?mep_check_stock=1 to any admin URL
 */
function mep_manual_stock_check_init() {
    // Only run for admin users
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }
    
    // Check if test parameter is set
    if (isset($_GET['mep_check_stock']) && $_GET['mep_check_stock'] == 1) {
        global $mpwem_frontend_stock_status;
        $mpwem_frontend_stock_status->check_all_events_for_low_stock();
        
        // Add admin notice
        add_action('admin_notices', 'mep_stock_check_notice');
    }
}
add_action('admin_init', 'mep_manual_stock_check_init');

/**
 * Display admin notice after stock check
 */
function mep_stock_check_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <strong><?php _e('Stock Check Completed!', 'mage-eventpress'); ?></strong> 
            <?php _e('All events have been checked for low stock.', 'mage-eventpress'); ?>
        </p>
    </div>
    <?php
} 