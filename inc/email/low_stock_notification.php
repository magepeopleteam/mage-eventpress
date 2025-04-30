<?php
/**
 * Low stock notification functions for Mage Eventpress
 */

if (!defined('ABSPATH')) {
    die;
} // Cannot access directly.

if (!function_exists('mep_notify_admin_low_ticket_stock')) {
    /**
     * Send email notification to admin when ticket stock is low
     * 
     * @param int $event_id The event ID
     * @param string $ticket_type_name The name of the ticket type
     * @param int $available_seats Number of available seats
     * @return void
     */
    function mep_notify_admin_low_ticket_stock($event_id, $ticket_type_name, $available_seats) {
        // Check if email notifications are enabled
        $email_enabled = mep_get_option('mep_enable_low_stock_email', 'general_setting_sec', 'yes');
        
        // If email notifications are disabled, return early
        if ($email_enabled !== 'yes') {
            return;
        }
        
        // Get the transient name for this notification
        $transient_name = 'mep_low_stock_notified_' . $event_id . '_' . sanitize_title($ticket_type_name);
        
        // Check if we've already sent a notification recently (within 24 hours)
        if (get_transient($transient_name)) {
            return;
        }
        
        // Get event details
        $event_name = get_the_title($event_id);
        $event_edit_link = admin_url('post.php?post=' . $event_id . '&action=edit');
        
        // Get admin email and name
        $admin_email = get_option('admin_email');
        $admin_user = get_user_by('email', $admin_email);
        $admin_name = $admin_user ? $admin_user->display_name : 'Admin';
        $site_name = get_bloginfo('name');
        
        // Get email settings from admin_setting_panel.php
        $from_name = mep_get_option('mep_email_form_name', 'email_setting_sec', $site_name);
        $from_email = mep_get_option('mep_email_form_email', 'email_setting_sec', $admin_email);
        
        // Email subject
        $subject = sprintf(
            __('[%s] Low Ticket Stock Alert - %s', 'mage-eventpress'),
            $site_name,
            $event_name
        );
        
        // Email message
        $message = sprintf(
            /* translators: 1: Ticket type name 2: Event name 3: Available seats 4: Edit event link 5: Admin name 6: Site name */
            __(
                'Hello %5$s,

This is an automated notification to inform you about low ticket stock:

Event: %2$s
Ticket Type: %1$s
Available Seats: %3$d

Please consider adding more seats or taking appropriate action.

You can manage this event here: %4$s

Best regards,
%6$s', 
                'mage-eventpress'
            ),
            $ticket_type_name,
            $event_name,
            $available_seats,
            $event_edit_link,
            $admin_name,
            $site_name
        );
        
        // Convert message to HTML
        $html_message = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">';
        $html_message .= '<h2 style="color: #e63946; margin-bottom: 20px;">Low Ticket Stock Alert</h2>';
        $html_message .= '<p>Hello ' . esc_html($admin_name) . ',</p>';
        $html_message .= '<p>This is an automated notification to inform you about low ticket stock:</p>';
        $html_message .= '<div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #e63946; margin: 20px 0;">';
        $html_message .= '<p><strong>Event:</strong> ' . esc_html($event_name) . '</p>';
        $html_message .= '<p><strong>Ticket Type:</strong> ' . esc_html($ticket_type_name) . '</p>';
        $html_message .= '<p><strong>Available Seats:</strong> ' . esc_html($available_seats) . '</p>';
        $html_message .= '</div>';
        $html_message .= '<p>Please consider adding more seats or taking appropriate action.</p>';
        $html_message .= '<p><a href="' . esc_url($event_edit_link) . '" style="display: inline-block; background-color: #457b9d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-top: 10px;">Manage This Event</a></p>';
        $html_message .= '<p>Best regards,<br>' . esc_html($site_name) . '</p>';
        $html_message .= '</div>';
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        // Apply filters to allow customization of email parameters
        $subject = apply_filters('mep_low_stock_email_subject', $subject, $event_id, $ticket_type_name, $available_seats);
        $html_message = apply_filters('mep_low_stock_email_message', $html_message, $event_id, $ticket_type_name, $available_seats, $event_name, $admin_name);
        $admin_email = apply_filters('mep_low_stock_email_recipient', $admin_email, $event_id);
        $headers = apply_filters('mep_low_stock_email_headers', $headers, $event_id);
        
        // Send email
        $sent = wp_mail($admin_email, $subject, $html_message, $headers);
        
        if ($sent) {
            // Set a transient to prevent sending multiple notifications
            // This will expire after 24 hours (86400 seconds)
            set_transient($transient_name, true, 86400);
            
            // Log that the email was sent successfully
            do_action('mep_after_low_stock_email_sent', $event_id, $ticket_type_name, $available_seats);
        } else {
            // Try an alternative method (direct PHP mail) as a fallback
            $mail_sent = mail(
                $admin_email,
                $subject,
                strip_tags($message), // Plain text version
                'From: ' . $from_name . ' <' . $from_email . '>'
            );
            
            if ($mail_sent) {
                set_transient($transient_name, true, 86400);
                do_action('mep_after_low_stock_email_sent', $event_id, $ticket_type_name, $available_seats);
            } else {
                do_action('mep_low_stock_email_failed', $event_id, $ticket_type_name, $available_seats);
            }
        }
    }
}

if (!function_exists('mep_check_and_notify_low_ticket_stock')) {
    /**
     * Check ticket stock and send notification if needed
     * 
     * @param int $event_id The event ID
     * @param string $ticket_type_name The name of the ticket type
     * @param int $available_seats Number of available seats
     * @return void
     */
    function mep_check_and_notify_low_ticket_stock($event_id, $ticket_type_name, $available_seats) {
        // Get the threshold from settings
        $show_warning = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        $threshold = (int)mep_get_option('mep_low_stock_threshold', 'general_setting_sec', '3');
        
        // Allow filtering of the decision to notify
        $should_notify = apply_filters('mep_should_notify_low_stock', 
            ($show_warning === 'yes' && $available_seats > 0 && $available_seats <= $threshold),
            $event_id, 
            $ticket_type_name, 
            $available_seats,
            $threshold
        );
        
        // If warning is enabled and seats are low
        if ($should_notify) {
            mep_notify_admin_low_ticket_stock($event_id, $ticket_type_name, $available_seats);
        }
    }
}

if (!function_exists('mep_display_low_stock_warning')) {
    /**
     * Display low stock warning in the event ticket listing
     * 
     * @param int $event_id The event ID
     * @param string $ticket_type_name The name of the ticket type
     * @param int $available_seats Number of available seats
     * @return void
     */
    function mep_display_low_stock_warning($event_id, $ticket_type_name, $available_seats) {
        // Get settings
        $show_warning = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        $threshold = (int)mep_get_option('mep_low_stock_threshold', 'general_setting_sec', '0');
        
        // Check if we should show low stock warning
        $show_low_stock = ($show_warning === 'yes' && $available_seats > 0 && $available_seats <= $threshold);
        
        // Allow filtering of the decision to display warning
        $show_low_stock = apply_filters('mep_show_low_stock_warning', 
            $show_low_stock,
            $event_id, 
            $ticket_type_name, 
            $available_seats,
            $threshold
        );
        
        if ($show_low_stock) {
            // Allow filtering of the warning text
            $warning_text = apply_filters(
                'mep_low_stock_warning_text',
                sprintf(
                    esc_html__('Hurry! Only %s %s tickets left', 'mage-eventpress'),
                    esc_html($available_seats),
                    esc_html($ticket_type_name)
                ),
                $available_seats,
                $ticket_type_name,
                $event_id
            );
            
            ?>
            <div class="mep-low-stock-warning">
                <span class="mep-low-stock-warning-text">
                    <?php echo $warning_text; ?>
                </span>
            </div>
            <?php
        }
        
        // Return the value for use in conditional statements
        return $show_low_stock;
    }
}

// Hook into the ticket listing to display low stock warnings
add_action('mep_after_ticket_type_qty', 'mep_hook_display_low_stock_warning', 10, 5);

if (!function_exists('mep_hook_display_low_stock_warning')) {
    /**
     * Hook function to display low stock warning after ticket quantity selector
     * 
     * @param int $post_id The event ID
     * @param string $ticket_name The name of the ticket type
     * @param array $field Ticket field data
     * @param int $default_quantity Default quantity
     * @param string $start_date Event start date
     * @return void
     */
    function mep_hook_display_low_stock_warning($post_id, $ticket_name, $field, $default_quantity, $start_date) {
        // Calculate available seats - this should match the calculation in ticket_type_list.php
        $total_quantity = array_key_exists('option_qty_t', $field) ? $field['option_qty_t'] : 0;
        $total_resv_quantity = array_key_exists('option_rsv_t', $field) ? $field['option_rsv_t'] : 0;
        $event_date = get_post_meta($post_id, 'event_start_date', true) . ' ' . get_post_meta($post_id, 'event_start_time', true);
        $total_sold = mep_get_ticket_type_seat_count($post_id, $ticket_name, $event_date, $total_quantity, $total_resv_quantity);
        $available_seats = (int)$total_quantity - ((int)$total_sold + (int)$total_resv_quantity);
        
        // Display low stock warning
        $show_low_stock = mep_display_low_stock_warning($post_id, $ticket_name, $available_seats);
        
        // Store the result in a global variable or transient if needed for later use
        // For example, to conditionally show/hide the "X Left" text in the template
        if ($show_low_stock) {
            $GLOBALS['mep_showed_low_stock_' . sanitize_title($ticket_name)] = true;
        }
    }
} 