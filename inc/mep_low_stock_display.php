<?php
/**
 * Low Stock Display Handler for Mage EventPress
 * 
 * @Author 		engr.sumonazma@gmail.com
 * Copyright: 	mage-people.com
 */

if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

class MEP_Low_Stock_Display {
    
    public function __construct() {
        add_action('mep_before_check_low_stock', array($this, 'display_low_stock_warning'), 10, 3);
        add_action('mep_after_check_low_stock', array($this, 'display_limited_availability_ribbon'), 10, 3);
        add_filter('mep_check_low_stock', array($this, 'should_show_low_stock_warning'), 10, 4);
        
        // Add hooks for template integration
        add_action('mep_ticket_type_list_row_start', array($this, 'display_low_stock_warning_template'), 10, 2);
        add_action('mep_ticket_type_list_row_end', array($this, 'display_limited_availability_ribbon_template'), 10, 2);
        
        // Add email notification functionality
        add_action('mep_low_stock_detected', array($this, 'send_low_stock_email'), 10, 4);
    }
            
    /**
     * Check if low stock warning should be shown
     */
    public function should_show_low_stock_warning($show, $post_id, $ticket_type_name, $available_seats) {
        $show_low_stock = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        
        if ($show_low_stock !== 'yes') {
            return false;
        }
        
        $low_stock_threshold = (int) mep_get_option('mep_low_stock_threshold', 'general_setting_sec', 3);
        
        return $available_seats <= $low_stock_threshold && $available_seats > 0;
    }
    
    /**
     * Display low stock warning message
     */
    public function display_low_stock_warning($post_id, $ticket_type_name, $available_seats) {
        $show_low_stock = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        
        if ($show_low_stock !== 'yes') {
            return;
        }
        
        $low_stock_threshold = (int) mep_get_option('mep_low_stock_threshold', 'general_setting_sec', 3);
        $low_stock_text = mep_get_option('mep_low_stock_text', 'general_setting_sec', 'Hurry! Only %s seats left');
        
        if ($available_seats <= $low_stock_threshold && $available_seats > 0) {
            $warning_text = sprintf($low_stock_text, $available_seats);
            echo '<div class="mep-low-stock-warning">' . esc_html($warning_text) . '</div>';
            
            // Trigger email notification
            do_action('mep_low_stock_detected', $post_id, $ticket_type_name, $available_seats, $low_stock_threshold);
        }
        
        // Debug information (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo '<!-- Debug: Low Stock - Available: ' . $available_seats . ', Threshold: ' . $low_stock_threshold . ', Show: ' . $show_low_stock . ' -->';
        }
    }
    
    /**
     * Display limited availability ribbon
     */
    public function display_limited_availability_ribbon($post_id, $ticket_type_name, $available_seats) {
        $show_ribbon = mep_get_option('mep_show_limited_availability_ribbon', 'general_setting_sec', 'no');
        
        if ($show_ribbon !== 'yes') {
            return;
        }
        
        $ribbon_threshold = (int) mep_get_option('mep_limited_availability_threshold', 'general_setting_sec', 5);
        
        if ($available_seats <= $ribbon_threshold && $available_seats > 0) {
            echo '<div class="mep-limited-availability-ribbon">' . esc_html__('Limited Availability', 'mage-eventpress') . '</div>';
        }
        
        // Debug information (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo '<!-- Debug: Ribbon - Available: ' . $available_seats . ', Threshold: ' . $ribbon_threshold . ', Show: ' . $show_ribbon . ' -->';
        }
    }
    
    /**
     * Send low stock email notification to admin
     */
    public function send_low_stock_email($post_id, $ticket_type_name, $available_seats, $threshold) {
        // Check if email notifications are enabled
        $enable_email = mep_get_option('mep_enable_low_stock_email', 'general_setting_sec', 'yes');
        
        if ($enable_email !== 'yes') {
            return;
        }
        
        // Get event details
        $event_title = get_the_title($post_id);
        $event_url = get_permalink($post_id);
        $admin_email = get_option('admin_email');
        
        // Create unique key to prevent duplicate emails for the same event/ticket combination
        $email_key = 'mep_low_stock_email_' . $post_id . '_' . sanitize_title($ticket_type_name);
        $email_sent = get_transient($email_key);
        
        if ($email_sent) {
            return; // Email already sent for this event/ticket combination
        }
        
        // Set transient to prevent duplicate emails (valid for 1 hour)
        set_transient($email_key, true, HOUR_IN_SECONDS);
        
        // Email subject
        $subject = sprintf(__('[%s] Low Stock Alert - %s', 'mage-eventpress'), get_bloginfo('name'), $event_title);
        
        // Email body
        $message = $this->get_low_stock_email_content($post_id, $ticket_type_name, $available_seats, $threshold, $event_title, $event_url);
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>',
            'Reply-To: ' . $admin_email
        );
        
        // Send email
        $email_sent = wp_mail($admin_email, $subject, $message, $headers);
        
        // Log email sending (for debugging)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MEP Low Stock Email: ' . ($email_sent ? 'Sent' : 'Failed') . ' for Event ID: ' . $post_id . ', Ticket: ' . $ticket_type_name);
        }
    }
    
    /**
     * Get low stock email content
     */
    private function get_low_stock_email_content($post_id, $ticket_type_name, $available_seats, $threshold, $event_title, $event_url) {
        $low_stock_text = mep_get_option('mep_low_stock_text', 'general_setting_sec', 'Hurry! Only %s seats left');
        $warning_message = sprintf($low_stock_text, $available_seats);
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . esc_html__('Low Stock Alert', 'mage-eventpress') . '</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;">
                <h2 style="color: #dc3545; margin-top: 0;">' . esc_html__('Low Stock Alert', 'mage-eventpress') . '</h2>
                <p style="margin-bottom: 15px;">' . esc_html__('An event ticket is running low on stock and requires your attention.', 'mage-eventpress') . '</p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
                    <h3 style="margin-top: 0; color: #333;">' . esc_html__('Event Details', 'mage-eventpress') . '</h3>
                    <p><strong>' . esc_html__('Event:', 'mage-eventpress') . '</strong> ' . esc_html($event_title) . '</p>
                    <p><strong>' . esc_html__('Ticket Type:', 'mage-eventpress') . '</strong> ' . esc_html($ticket_type_name) . '</p>
                    <p><strong>' . esc_html__('Available Seats:', 'mage-eventpress') . '</strong> <span style="color: #dc3545; font-weight: bold;">' . esc_html($available_seats) . '</span></p>
                    <p><strong>' . esc_html__('Low Stock Threshold:', 'mage-eventpress') . '</strong> ' . esc_html($threshold) . '</p>
                    <p><strong>' . esc_html__('Warning Message:', 'mage-eventpress') . '</strong> ' . esc_html($warning_message) . '</p>
                </div>
                
                <div style="text-align: center; margin: 20px 0;">
                    <a href="' . esc_url($event_url) . '" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">' . esc_html__('View Event', 'mage-eventpress') . '</a>
                </div>
                
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                    <p style="margin: 0;"><strong>' . esc_html__('Action Required:', 'mage-eventpress') . '</strong> ' . esc_html__('Please review this event and consider adding more tickets or closing sales if necessary.', 'mage-eventpress') . '</p>
                </div>
                
                <hr style="border: none; border-top: 1px solid #dee2e6; margin: 20px 0;">
                <p style="font-size: 12px; color: #6c757d; margin: 0;">' . esc_html__('This email was sent automatically by Mage EventPress plugin.', 'mage-eventpress') . '</p>
            </div>
        </body>
        </html>';
        
        return $message;
    }
    
    /**
     * Get low stock status for a ticket type
     */
    public static function get_low_stock_status($post_id, $ticket_type_name, $available_seats) {
        $show_low_stock = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        $low_stock_threshold = (int) mep_get_option('mep_low_stock_threshold', 'general_setting_sec', 3);
        
        if ($show_low_stock !== 'yes') {
            return false;
        }
        
        return $available_seats <= $low_stock_threshold && $available_seats > 0;
    }
    
    /**
     * Get limited availability status for a ticket type
     */
    public static function get_limited_availability_status($post_id, $ticket_type_name, $available_seats) {
        $show_ribbon = mep_get_option('mep_show_limited_availability_ribbon', 'general_setting_sec', 'no');
        $ribbon_threshold = (int) mep_get_option('mep_limited_availability_threshold', 'general_setting_sec', 5);
        
        if ($show_ribbon !== 'yes') {
            return false;
        }
        
        return $available_seats <= $ribbon_threshold && $available_seats > 0;
    }
    
    /**
     * Get low stock warning text
     */
    public static function get_low_stock_text($available_seats) {
        $low_stock_text = mep_get_option('mep_low_stock_text', 'general_setting_sec', 'Hurry! Only %s seats left');
        return sprintf($low_stock_text, $available_seats);
    }
    
    /**
     * Display low stock warning in template hook
     */
    public function display_low_stock_warning_template($field, $post_id) {
        $ticket_type_name = array_key_exists('option_name_t', $field) ? mep_remove_apostopie($field['option_name_t']) : '';
        $total_quantity = array_key_exists('option_qty_t', $field) ? $field['option_qty_t'] : 0;
        $total_resv_quantity = array_key_exists('option_rsv_t', $field) ? $field['option_rsv_t'] : 0;
        
        // Get available seats calculation
        $event_date = get_post_meta($post_id, 'event_start_date', true) . ' ' . get_post_meta($post_id, 'event_start_time', true);
        $selected_date = isset($_GET['event_date']) ? sanitize_text_field($_GET['event_date']) : $event_date;
        $total_sold = mep_get_ticket_type_seat_count($post_id, $ticket_type_name, $selected_date, $total_quantity, $total_resv_quantity);
        $available_seats = (int)$total_quantity - ((int)$total_sold + (int)$total_resv_quantity);
        
        if ($this->should_show_low_stock_warning(true, $post_id, $ticket_type_name, $available_seats)) {
            $this->display_low_stock_warning($post_id, $ticket_type_name, $available_seats);
        }
    }
    
    /**
     * Display limited availability ribbon in template hook
     */
    public function display_limited_availability_ribbon_template($field, $post_id) {
        $ticket_type_name = array_key_exists('option_name_t', $field) ? mep_remove_apostopie($field['option_name_t']) : '';
        $total_quantity = array_key_exists('option_qty_t', $field) ? $field['option_qty_t'] : 0;
        $total_resv_quantity = array_key_exists('option_rsv_t', $field) ? $field['option_rsv_t'] : 0;
        
        // Get available seats calculation
        $event_date = get_post_meta($post_id, 'event_start_date', true) . ' ' . get_post_meta($post_id, 'event_start_time', true);
        $selected_date = isset($_GET['event_date']) ? sanitize_text_field($_GET['event_date']) : $event_date;
        $total_sold = mep_get_ticket_type_seat_count($post_id, $ticket_type_name, $selected_date, $total_quantity, $total_resv_quantity);
        $available_seats = (int)$total_quantity - ((int)$total_sold + (int)$total_resv_quantity);
        
        if ($this->get_limited_availability_status($post_id, $ticket_type_name, $available_seats)) {
            $this->display_limited_availability_ribbon($post_id, $ticket_type_name, $available_seats);
        }
    }
}

// Initialize the class
new MEP_Low_Stock_Display();

/**
 * Helper function to display low stock warning in templates
 */
function mep_display_low_stock_warning($post_id, $ticket_type_name, $available_seats) {
    $low_stock_display = new MEP_Low_Stock_Display();
    $low_stock_display->display_low_stock_warning($post_id, $ticket_type_name, $available_seats);
}

/**
 * Helper function to display limited availability ribbon in templates
 */
function mep_display_limited_availability_ribbon($post_id, $ticket_type_name, $available_seats) {
    $low_stock_display = new MEP_Low_Stock_Display();
    $low_stock_display->display_limited_availability_ribbon($post_id, $ticket_type_name, $available_seats);
}

/**
 * Helper function to check if low stock warning should be shown
 */
function mep_is_low_stock($post_id, $ticket_type_name, $available_seats) {
    return MEP_Low_Stock_Display::get_low_stock_status($post_id, $ticket_type_name, $available_seats);
}

/**
 * Helper function to check if limited availability ribbon should be shown
 */
function mep_is_limited_availability($post_id, $ticket_type_name, $available_seats) {
    return MEP_Low_Stock_Display::get_limited_availability_status($post_id, $ticket_type_name, $available_seats);
}

/**
 * Helper function to manually trigger low stock email (for testing)
 */
function mep_trigger_low_stock_email($post_id, $ticket_type_name, $available_seats, $threshold = null) {
    if ($threshold === null) {
        $threshold = (int) mep_get_option('mep_low_stock_threshold', 'general_setting_sec', 3);
    }
    
    $low_stock_display = new MEP_Low_Stock_Display();
    $low_stock_display->send_low_stock_email($post_id, $ticket_type_name, $available_seats, $threshold);
} 