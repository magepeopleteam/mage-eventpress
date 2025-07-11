<?php
/**
 * Email Settings for Low Stock Notifications
 * 
 * This file contains settings for low stock email notifications.
 * 
 * @package MagePeople Event Press
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add settings to the email settings section
 * 
 * @param array $settings_arr Existing settings array
 * @return array Modified settings array with low stock email settings
 */
function mep_add_low_stock_email_settings($settings_arr) {
    // Add low stock email settings
    $settings_arr[] = array(
        'name' => __('Low Stock Email Notifications', 'mage-eventpress'),
        'id' => 'mep_low_stock_email_heading',
        'label' => __('Low Stock Email Notifications', 'mage-eventpress'),
        'desc' => __('Configure email notifications for low stock alerts', 'mage-eventpress'),
        'type' => 'title',
    );
    
    $settings_arr[] = array(
        'name' => 'mep_enable_low_stock_email',
        'label' => __('Send Low Stock Email Notifications?', 'mage-eventpress'),
        'desc' => __('Enable this to send email notifications to admin when event seats are running low.', 'mage-eventpress'),
        'type' => 'select',
        'default' => 'no',
        'options' => array(
            'yes' => __('Yes', 'mage-eventpress'),
            'no' => __('No', 'mage-eventpress')
        )
    );
    
    $settings_arr[] = array(
        'name' => 'mep_low_stock_email_recipient',
        'label' => __('Low Stock Email Recipient', 'mage-eventpress'),
        'desc' => __('Email address to receive low stock notifications. Leave blank to use admin email.', 'mage-eventpress'),
        'type' => 'text',
        'default' => get_option('admin_email')
    );
    
    $settings_arr[] = array(
        'name' => 'mep_low_stock_email_subject',
        'label' => __('Low Stock Email Subject', 'mage-eventpress'),
        'desc' => __('Subject line for low stock notification emails.', 'mage-eventpress'),
        'type' => 'text',
        'default' => __('Low Stock Alert: {event_name}', 'mage-eventpress')
    );
    
    $settings_arr[] = array(
        'name' => 'mep_low_stock_email_body',
        'label' => __('Low Stock Email Body', 'mage-eventpress'),
        'desc' => __('Email body for low stock notifications. You can use these placeholders: {event_name}, {event_date}, {event_time}, {available_seats}, {event_link}', 'mage-eventpress'),
        'type' => 'textarea',
        'default' => __('The event "{event_name}" is running low on tickets.

Event Details:
- Event: {event_name}
- Date: {event_date}
- Time: {event_time}
- Available Seats: {available_seats}

Please check the event and take necessary actions.

View Event: {event_link}', 'mage-eventpress')
    );
    
    return $settings_arr;
}
add_filter('mep_settings_email_arr', 'mep_add_low_stock_email_settings');

/**
 * Update the send_low_stock_email_notification method to use custom settings
 * 
 * @param int $available_seats Number of available seats
 * @param int $event_id Event ID
 * @return void
 */
function mep_update_low_stock_email_notification($available_seats, $event_id) {
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
    
    // Get email settings
    $recipient = mep_get_option('mep_low_stock_email_recipient', 'email_setting_sec', get_option('admin_email'));
    $global_email_form_email = mep_get_option('mep_email_form_email', 'email_setting_sec', get_option('admin_email'));
    $global_email_form_name = mep_get_option('mep_email_form_name', 'email_setting_sec', get_bloginfo('name'));
    
    // Sanitize recipient email
    $recipient = sanitize_email($recipient);
    if (empty($recipient)) {
        $recipient = get_option('admin_email');
    }
    
    // Email subject
    $subject_template = mep_get_option('mep_low_stock_email_subject', 'email_setting_sec', __('Low Stock Alert: {event_name}', 'mage-eventpress'));
    $subject = str_replace(
        array('{event_name}', '{event_date}', '{event_time}', '{available_seats}'),
        array($event_title, $event_date, $event_time, $available_seats),
        $subject_template
    );
    
    // Email body
    $body_template = mep_get_option('mep_low_stock_email_body', 'email_setting_sec', __('The event "{event_name}" is running low on tickets.', 'mage-eventpress'));
    $message = str_replace(
        array('{event_name}', '{event_date}', '{event_time}', '{available_seats}', '{event_link}'),
        array($event_title, $event_date, $event_time, $available_seats, $event_link),
        $body_template
    );
    
    // Email headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', esc_html($global_email_form_name), sanitize_email($global_email_form_email))
    );
    
    // Allow filtering of email data before sending
    $email_data = apply_filters('mep_low_stock_email_data', array(
        'to' => $recipient,
        'subject' => $subject,
        'message' => nl2br($message),
        'headers' => $headers,
        'event_id' => $event_id,
        'available_seats' => $available_seats
    ));
    
    // Send email
    $email_sent = wp_mail(
        $email_data['to'], 
        $email_data['subject'], 
        $email_data['message'], 
        $email_data['headers']
    );
    
    // Mark notification as sent
    if ($email_sent) {
        update_post_meta($event_id, '_mep_low_stock_notification_sent', current_time('timestamp'));
        
        // Log email sent for debugging (if WP_DEBUG is enabled)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('Low stock notification sent for event #%d with %d seats available', $event_id, $available_seats));
        }
        
        // Action hook for after email is sent
        do_action('mep_after_low_stock_email_sent', $event_id, $available_seats);
    }
}
add_action('mep_event_low_stock_notification', 'mep_update_low_stock_email_notification', 10, 2); 