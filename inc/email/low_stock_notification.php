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
            'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>'
        );
        
        // Send email
        $sent = wp_mail($admin_email, $subject, $html_message, $headers);
        
        if ($sent) {
            // Set a transient to prevent sending multiple notifications
            // This will expire after 24 hours (86400 seconds)
            set_transient($transient_name, true, 86400);
        } else {
            // Try an alternative method (direct PHP mail) as a fallback
            $mail_sent = mail(
                $admin_email,
                $subject,
                strip_tags($message), // Plain text version
                'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>'
            );
            
            if ($mail_sent) {
                set_transient($transient_name, true, 86400);
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
        
        // If warning is enabled and seats are low
        if ($show_warning === 'yes' && $available_seats > 0 && $available_seats <= $threshold) {
            mep_notify_admin_low_ticket_stock($event_id, $ticket_type_name, $available_seats);
        }
    }
} 