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
        // Validate and sanitize input parameters
        $event_id = absint($event_id);
        $ticket_type_name = sanitize_text_field($ticket_type_name);
        $available_seats = absint($available_seats);
        
        // Validate that event exists
        if (!$event_id || !get_post($event_id) || get_post_type($event_id) !== 'mep_events') {
            return;
        }
        
        // Check if email notifications are enabled
        $email_enabled = mep_get_option('mep_enable_low_stock_email', 'general_setting_sec', 'yes');
        
        // If email notifications are disabled, return early
        if ($email_enabled !== 'yes') {
            return;
        }
        
        // Get the current selected date with proper sanitization
        $event_default_date = get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
        $selected_date = isset($_GET['event_date']) ? sanitize_text_field(wp_unslash($_GET['event_date'])) : $event_default_date;
        
        // Validate date format
        if (!strtotime($selected_date)) {
            $selected_date = $event_default_date;
        }
        
        // Get the transient name for this notification with date-specific key
        $transient_name = 'mep_low_stock_notified_' . $event_id . '_' . sanitize_key($ticket_type_name) . '_' . sanitize_key($selected_date);
        
        // Check if we've already sent a notification recently (within 24 hours)
        if (get_transient($transient_name)) {
            return;
        }
        
        // Get event details with proper sanitization
        $event_name = sanitize_text_field(get_the_title($event_id));
        $event_edit_link = esc_url(admin_url('post.php?post=' . $event_id . '&action=edit'));
        
        // Get admin email and name with proper validation
        $admin_email = sanitize_email(get_option('admin_email'));
        $admin_user = get_user_by('email', $admin_email);
        $admin_name = $admin_user ? sanitize_text_field($admin_user->display_name) : 'Admin';
        $site_name = sanitize_text_field(get_bloginfo('name'));
        
        // Get email settings from admin_setting_panel.php with proper sanitization
        $from_name = sanitize_text_field(mep_get_option('mep_email_form_name', 'email_setting_sec', $site_name));
        $from_email = sanitize_email(mep_get_option('mep_email_form_email', 'email_setting_sec', $admin_email));
        
        // Validate email addresses
        if (!is_email($admin_email) || !is_email($from_email)) {
            return;
        }
        
        // Format the date for display with proper validation
        $formatted_date = '';
        if (strtotime($selected_date)) {
            $formatted_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($selected_date));
        } else {
            $formatted_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'));
        }

        $subject = sprintf(
            // translators: %1$s is the site name, %2$s is the event name, %3$s is the formatted event date.
            esc_html__('[%1$s] Low Ticket Stock Alert - %2$s - %3$s', 'mage-eventpress'),
            $site_name,
            $event_name,
            $formatted_date
        );

        
        // Email message with proper escaping
        $message = sprintf(
            /* translators: 1: Ticket type name 2: Event name 3: Available seats 4: Edit event link 5: Admin name 6: Site name 7: Event date */
            esc_html__(
                'Hello %5$s,

                This is an automated notification to inform you about low ticket stock:

                Event: %2$s
                Date: %7$s
                Ticket Type: %1$s
                Available Seats: %3$d

                Please consider adding more seats or taking appropriate action.

                You can manage this event here: %4$s

                Best regards,
                %6$s', 
                'mage-eventpress'
            ),
            esc_html($ticket_type_name),
            esc_html($event_name),
            esc_html($available_seats),
            esc_url($event_edit_link),
            esc_html($admin_name),
            esc_html($site_name),
            esc_html($formatted_date)
        );
        
        // Convert message to HTML with proper escaping
        $html_message = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">';
        $html_message .= '<h2 style="color: #e63946; margin-bottom: 20px;">' . esc_html__('Low Ticket Stock Alert', 'mage-eventpress') . '</h2>';
        $html_message .= '<p style="margin-bottom: 15px;">' . sprintf(
            /* translators: 1: Admin name */
            esc_html__('Hello %1$s,', 'mage-eventpress'),
            esc_html($admin_name)
        ) . '</p>';
        $html_message .= '<p style="margin-bottom: 15px;">' . esc_html__('This is an automated notification to inform you about low ticket stock:', 'mage-eventpress') . '</p>';
        $html_message .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">';
        $html_message .= '<p style="margin: 5px 0;"><strong>' . esc_html__('Event:', 'mage-eventpress') . '</strong> ' . esc_html($event_name) . '</p>';
        $html_message .= '<p style="margin: 5px 0;"><strong>' . esc_html__('Date:', 'mage-eventpress') . '</strong> ' . esc_html($formatted_date) . '</p>';
        $html_message .= '<p style="margin: 5px 0;"><strong>' . esc_html__('Ticket Type:', 'mage-eventpress') . '</strong> ' . esc_html($ticket_type_name) . '</p>';
        $html_message .= '<p style="margin: 5px 0;"><strong>' . esc_html__('Available Seats:', 'mage-eventpress') . '</strong> ' . esc_html($available_seats) . '</p>';
        $html_message .= '</div>';
        $html_message .= '<p style="margin-bottom: 15px;">' . esc_html__('Please consider adding more seats or taking appropriate action.', 'mage-eventpress') . '</p>';
        $html_message .= '<p style="margin-bottom: 20px;">' . sprintf(
            /* translators: %s is the edit event link */
            esc_html__('You can manage this event here: <a href="%s">Edit Event</a>', 'mage-eventpress'),
            esc_url($event_edit_link)
        ) . '</p>';
        $html_message .= '<p style="margin-top: 20px; color: #666;">' . sprintf(
            /* translators: %s is the site name */
            esc_html__('Best regards,<br>%s', 'mage-eventpress'),
            esc_html($site_name)
        ) . '</p>';
        $html_message .= '</div>';
        
        // Email headers with proper sanitization
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . esc_html($from_name) . ' <' . esc_html($from_email) . '>',
            'Reply-To: ' . esc_html($from_email)
        );
        
        // Allow filtering of recipient and headers
        $admin_email = apply_filters('mep_low_stock_email_recipient', $admin_email, $event_id);
        $headers = apply_filters('mep_low_stock_email_headers', $headers, $event_id);
        
        // Validate filtered email
        if (!is_email($admin_email)) {
            return;
        }
        
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
                wp_strip_all_tags($message), // Plain text version
                'From: ' . esc_html($from_name) . ' <' . esc_html($from_email) . '>'
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
        // Validate and sanitize input parameters
        $event_id = absint($event_id);
        $ticket_type_name = sanitize_text_field($ticket_type_name);
        $available_seats = absint($available_seats);
        
        // Validate that event exists
        if (!$event_id || !get_post($event_id) || get_post_type($event_id) !== 'mep_events') {
            return;
        }
        
        // Get the threshold from settings
        $show_warning = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        $threshold = absint(mep_get_option('mep_low_stock_threshold', 'general_setting_sec', '3'));
        
        // Get the current selected date with proper sanitization
        $event_default_date = get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
        $selected_date = isset($_GET['event_date']) ? sanitize_text_field(wp_unslash($_GET['event_date'])) : $event_default_date;
        
        // Validate date format
        if (!strtotime($selected_date)) {
            $selected_date = $event_default_date;
        }
        
        // Create a unique key for this ticket type on this date for cache/transient usage
        $date_specific_key = sanitize_key($ticket_type_name) . '_' . sanitize_key($selected_date);
        
        // Allow filtering of the decision to notify
        $should_notify = apply_filters('mep_should_notify_low_stock', 
            ($show_warning === 'yes' && $available_seats > 0 && $available_seats <= $threshold),
            $event_id, 
            $ticket_type_name, 
            $available_seats,
            $threshold,
            $selected_date
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
     * @return bool Whether the warning was displayed
     */
    function mep_display_low_stock_warning($event_id, $ticket_type_name, $available_seats) {
        // Validate and sanitize input parameters
        $event_id = absint($event_id);
        $ticket_type_name = sanitize_text_field($ticket_type_name);
        $available_seats = absint($available_seats);
        
        // Validate that event exists
        if (!$event_id || !get_post($event_id) || get_post_type($event_id) !== 'mep_events') {
            return false;
        }
        
        // Get settings
        $show_warning = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        $threshold = absint(mep_get_option('mep_low_stock_threshold', 'general_setting_sec', '3'));
        
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
            // Get the custom warning text from settings
            $custom_warning_text = sanitize_text_field(mep_get_option('mep_low_stock_text', 'general_setting_sec', 'Hurry! Only %s seats left'));
            
            // Allow filtering of the warning text
            $warning_text = apply_filters(
                'mep_low_stock_warning_text',
                sprintf(
                    // translators: %s is the number of available seats.
                    esc_html__($custom_warning_text, 'mage-eventpress'),
                    esc_html($available_seats)
                ),
                $available_seats,
                $ticket_type_name,
                $event_id
            );
            
            ?>
            <div class="mep-low-stock-warning" style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 8px 12px; border-radius: 4px; margin: 5px 0; font-size: 14px;">
                <span class="mep-low-stock-warning-text">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>
                    <?php echo wp_kses_post($warning_text); ?>
                </span>
            </div>
            <?php
            
            // Get the current selected date with proper sanitization
            $selected_date = isset($_GET['event_date']) ? sanitize_text_field(wp_unslash($_GET['event_date'])) : get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
            
            // Validate date format
            if (!strtotime($selected_date)) {
                $selected_date = get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
            }
            
            // Set global variable with date-specific key to indicate low stock warning was shown
            $GLOBALS['mep_showed_low_stock_' . sanitize_key($ticket_type_name) . '_' . sanitize_key($selected_date)] = true;
        }
        
        // Return the value for use in conditional statements
        return $show_low_stock;
    }
}

if (!function_exists('mep_display_limited_availability_ribbon')) {
    /**
     * Display limited availability ribbon above ticket price
     * 
     * @param int $event_id The event ID
     * @param string $ticket_type_name The name of the ticket type
     * @param int $available_seats Number of available seats
     * @param float $ticket_price The ticket price
     * @param array $field Ticket field data
     * @return bool Whether the ribbon was displayed
     */
    function mep_display_limited_availability_ribbon($event_id, $ticket_type_name, $available_seats, $ticket_price, $field) {
        // Validate and sanitize input parameters
        $event_id = absint($event_id);
        $ticket_type_name = sanitize_text_field($ticket_type_name);
        $available_seats = absint($available_seats);
        $ticket_price = floatval($ticket_price);
        $field = is_array($field) ? $field : array();
        
        // Validate that event exists
        if (!$event_id || !get_post($event_id) || get_post_type($event_id) !== 'mep_events') {
            return false;
        }
        
        // Get settings
        $show_ribbon = mep_get_option('mep_show_limited_availability_ribbon', 'general_setting_sec', 'no');
        $threshold = absint(mep_get_option('mep_limited_availability_threshold', 'general_setting_sec', '5'));
        
        // Check if we should show limited availability ribbon
        $show_limited_availability = ($show_ribbon === 'yes' && $available_seats > 0 && $available_seats <= $threshold);
        
        // Allow filtering of the decision to display ribbon
        $show_limited_availability = apply_filters('mep_show_limited_availability_ribbon', 
            $show_limited_availability,
            $event_id, 
            $ticket_type_name, 
            $available_seats,
            $threshold
        );
        
        if ($show_limited_availability) {
            // Get the custom ribbon text from settings
            $custom_ribbon_text = sanitize_text_field(mep_get_option('mep_limited_availability_text', 'general_setting_sec', 'Limited Availability'));
            
            // Allow filtering of the ribbon text
            $ribbon_text = apply_filters(
                'mep_limited_availability_ribbon_text',
                esc_html__($custom_ribbon_text, 'mage-eventpress'),
                $available_seats,
                $ticket_type_name,
                $event_id
            );
            
            ?>
            <div class="mep-limited-availability-ribbon mep-low-stock-warning-ribbon-text" >
                <i class="fas fa-clock" style="margin-right: 3px;"></i>
                <?php echo wp_kses_post($ribbon_text); ?>
            </div>
            <?php
            
            // Get the current selected date with proper sanitization
            $selected_date = isset($_GET['event_date']) ? sanitize_text_field(wp_unslash($_GET['event_date'])) : get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
            
            // Validate date format
            if (!strtotime($selected_date)) {
                $selected_date = get_post_meta($event_id, 'event_start_date', true) . ' ' . get_post_meta($event_id, 'event_start_time', true);
            }
            
            // Set global variable with date-specific key to indicate ribbon was shown
            $GLOBALS['mep_showed_limited_availability_' . sanitize_key($ticket_type_name) . '_' . sanitize_key($selected_date)] = true;
        }
        
        // Return the value for use in conditional statements
        return $show_limited_availability;
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
        // Validate and sanitize input parameters
        $post_id = absint($post_id);
        $ticket_name = sanitize_text_field($ticket_name);
        $field = is_array($field) ? $field : array();
        $default_quantity = absint($default_quantity);
        $start_date = sanitize_text_field($start_date);
        
        // Validate that event exists
        if (!$post_id || !get_post($post_id) || get_post_type($post_id) !== 'mep_events') {
            return;
        }
        
        // Get the current selected date with proper sanitization
        $event_default_date = get_post_meta($post_id, 'event_start_date', true) . ' ' . get_post_meta($post_id, 'event_start_time', true);
        $selected_date = isset($_GET['event_date']) ? sanitize_text_field(wp_unslash($_GET['event_date'])) : $event_default_date;
        
        // Validate date format
        if (!strtotime($selected_date)) {
            $selected_date = $event_default_date;
        }
        
        // Calculate available seats - this should match the calculation in ticket_type_list.php
        $total_quantity = array_key_exists('option_qty_t', $field) ? absint($field['option_qty_t']) : 0;
        $total_resv_quantity = array_key_exists('option_rsv_t', $field) ? absint($field['option_rsv_t']) : 0;
        
        // Use the selected date for calculating availability
        $total_sold = mep_get_ticket_type_seat_count($post_id, $ticket_name, $selected_date, $total_quantity, $total_resv_quantity);
        $available_seats = (int)$total_quantity - ((int)$total_sold + (int)$total_resv_quantity);
        
        // Check if low stock warning is enabled and if we should show it
        $show_low_stock_warning = mep_get_option('mep_show_low_stock_warning', 'general_setting_sec', 'yes');
        $low_stock_threshold = absint(mep_get_option('mep_low_stock_threshold', 'general_setting_sec', '3'));
        $should_show_warning = ($show_low_stock_warning === 'yes' && $available_seats > 0 && $available_seats <= $low_stock_threshold);
        
        // Only display the hook-based warning if we're NOT showing the template-based warning
        // This prevents duplicate warnings
        if ($should_show_warning) {
            // Don't display the hook-based warning since we're showing it in the template
            // Just check and notify admin if needed
            mep_check_and_notify_low_ticket_stock($post_id, $ticket_name, $available_seats);
        } else {
            // Display low stock warning only when not in low stock threshold
            // This is for cases where warning is enabled but seats are above threshold
            $show_low_stock = mep_display_low_stock_warning($post_id, $ticket_name, $available_seats);
            
            // Also check and notify admin if needed
            if ($show_low_stock) {
                mep_check_and_notify_low_ticket_stock($post_id, $ticket_name, $available_seats);
            }
        }
    }
}

// Hook into the ticket price display to show limited availability ribbon
add_action('mep_before_ticket_price', 'mep_hook_display_limited_availability_ribbon', 10, 5);

if (!function_exists('mep_hook_display_limited_availability_ribbon')) {
    /**
     * Hook function to display limited availability ribbon before ticket price
     * 
     * @param int $post_id The event ID
     * @param string $ticket_name The name of the ticket type
     * @param array $field Ticket field data
     * @param float $ticket_price The ticket price
     * @param string $start_date Event start date
     * @return void
     */
    function mep_hook_display_limited_availability_ribbon($post_id, $ticket_name, $field, $ticket_price, $start_date) {
        // Validate and sanitize input parameters
        $post_id = absint($post_id);
        $ticket_name = sanitize_text_field($ticket_name);
        $field = is_array($field) ? $field : array();
        $ticket_price = floatval($ticket_price);
        $start_date = sanitize_text_field($start_date);
        
        // Validate that event exists
        if (!$post_id || !get_post($post_id) || get_post_type($post_id) !== 'mep_events') {
            return;
        }
        
        // Get the current selected date with proper sanitization
        $event_default_date = get_post_meta($post_id, 'event_start_date', true) . ' ' . get_post_meta($post_id, 'event_start_time', true);
        $selected_date = isset($_GET['event_date']) ? sanitize_text_field(wp_unslash($_GET['event_date'])) : $event_default_date;
        
        // Validate date format
        if (!strtotime($selected_date)) {
            $selected_date = $event_default_date;
        }
        
        // Calculate available seats
        $total_quantity = array_key_exists('option_qty_t', $field) ? absint($field['option_qty_t']) : 0;
        $total_resv_quantity = array_key_exists('option_rsv_t', $field) ? absint($field['option_rsv_t']) : 0;
        
        // Use the selected date for calculating availability
        $total_sold = mep_get_ticket_type_seat_count($post_id, $ticket_name, $selected_date, $total_quantity, $total_resv_quantity);
        $available_seats = (int)$total_quantity - ((int)$total_sold + (int)$total_resv_quantity);
        
        // Display limited availability ribbon
        mep_display_limited_availability_ribbon($post_id, $ticket_name, $available_seats, $ticket_price, $field);
    }
} 