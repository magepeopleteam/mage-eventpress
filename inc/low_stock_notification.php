<?php
if (!defined('ABSPATH')) {
    die;
}

class MEP_Low_Stock_Notification {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init_hooks'));
    }
    
    public function init_hooks() {
        add_filter('mep_settings_sec_reg', array($this, 'add_settings_section'));
        add_filter('mep_settings_sec_fields', array($this, 'add_low_stock_settings_section'));
        add_filter('mep_enable_stock_ribbons', array($this, 'is_ribbon_system_enabled'), 10, 3);
        add_filter('mep_low_stock_threshold', array($this, 'get_low_stock_threshold'), 10, 3);
        add_filter('mep_very_low_stock_threshold', array($this, 'get_very_low_stock_threshold'), 10, 3);
        add_filter('mep_should_hide_default_remaining', array($this, 'should_hide_default_remaining'), 10, 4);
        add_action('mep_ticket_stock_ribbons', array($this, 'display_stock_ribbons'), 10, 3);
        add_action('mep_ticket_stock_warnings', array($this, 'display_stock_warnings'), 10, 3);
        add_action('mep_low_stock_detected', array($this, 'send_low_stock_email'), 10, 4);
        add_action('wp_head', array($this, 'inject_ribbon_css'));
    }
    
    public function add_settings_section($sections) {
        $sections[] = array(
            'id' => 'low_stock_setting_sec',
            'title' => '<i class="fas fa-exclamation-triangle"></i>' . __('Low Stock Settings', 'mage-eventpress')
        );
        return $sections;
    }
    
    public function add_low_stock_settings_section($settings_fields) {
        $fields = array(
            array(
                'name' => 'mep_enable_low_stock_system',
                'label' => __('Enable Low Stock System', 'mage-eventpress'),
                'desc' => __('Enable low stock ribbons, warnings and notifications for tickets', 'mage-eventpress'),
                'type' => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => 'Enable',
                    'no' => 'Disable'
                )
            ),
            array(
                'name' => 'mep_low_stock_threshold',
                'label' => __('Low Stock Threshold', 'mage-eventpress'),
                'desc' => __('Show "LIMITED" ribbon when tickets are below this number', 'mage-eventpress'),
                'type' => 'number',
                'default' => '5'
            ),
            array(
                'name' => 'mep_very_low_stock_threshold', 
                'label' => __('Very Low Stock Threshold', 'mage-eventpress'),
                'desc' => __('Show "LAST CHANCE" ribbon when tickets are below this number', 'mage-eventpress'),
                'type' => 'number',
                'default' => '2'
            ),
            array(
                'name' => 'mep_sold_out_text',
                'label' => __('Sold Out Warning Text', 'mage-eventpress'),
                'desc' => __('Text to display when tickets are sold out', 'mage-eventpress'),
                'type' => 'text',
                'default' => 'This ticket type is no longer available'
            ),
            array(
                'name' => 'mep_very_low_stock_text',
                'label' => __('Very Low Stock Warning Text', 'mage-eventpress'),
                'desc' => __('Text to display for very low stock. Use {count} to show remaining tickets', 'mage-eventpress'),
                'type' => 'text',
                'default' => 'Only {count} tickets left!'
            ),
            array(
                'name' => 'mep_low_stock_text',
                'label' => __('Low Stock Warning Text', 'mage-eventpress'),
                'desc' => __('Text to display for low stock. Use {count} to show remaining tickets', 'mage-eventpress'),
                'type' => 'text',
                'default' => 'Hurry! Only {count} tickets remaining'
            ),
            array(
                'name' => 'mep_enable_low_stock_email',
                'label' => __('Send Low Stock Email Notifications?', 'mage-eventpress'),
                'desc' => __('Send email notifications when stock is low', 'mage-eventpress'),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => 'Enable',
                    'no' => 'Disable'
                )
            ),
            array(
                'name' => 'mep_low_stock_email_recipients',
                'label' => __('Low Stock Email Recipients', 'mage-eventpress'),
                'desc' => __('Email addresses to notify (comma separated)', 'mage-eventpress'),
                'type' => 'text',
                'default' => get_option('admin_email')
            ),
            array(
                'name' => 'mep_low_stock_email_subject',
                'label' => __('Low Stock Email Subject', 'mage-eventpress'),
                'desc' => __('Subject line for low stock emails. Use {event_title} and {ticket_name}', 'mage-eventpress'),
                'type' => 'text',
                'default' => 'Low Stock Alert: {ticket_name} for {event_title}'
            ),
            array(
                'name' => 'mep_low_stock_email_message',
                'label' => __('Low Stock Email Message', 'mage-eventpress'),
                'desc' => __('Email message body. Use {event_title}, {ticket_name}, {remaining_count}, {event_url}', 'mage-eventpress'),
                'type' => 'textarea',
                'default' => "Low stock alert!\n\nEvent: {event_title}\nTicket: {ticket_name}\nRemaining: {remaining_count}\n\nView Event: {event_url}"
            )
        );
        $settings_fields['low_stock_setting_sec'] = apply_filters('mep_settings_low_stock_arr', $fields);
        return $settings_fields;
    }
    
    public function is_ribbon_system_enabled($enabled, $event_id, $ticket_name) {
        $system_enabled = mep_get_option('mep_enable_low_stock_system', 'low_stock_setting_sec', 'yes');
        return $system_enabled === 'yes';
    }
    
    public function get_low_stock_threshold($threshold, $event_id, $ticket_name) {
        return (int) mep_get_option('mep_low_stock_threshold', 'low_stock_setting_sec', 5);
    }
    
    public function get_very_low_stock_threshold($threshold, $event_id, $ticket_name) {
        return (int) mep_get_option('mep_very_low_stock_threshold', 'low_stock_setting_sec', 2);
    }
    
    public function display_stock_ribbons($event_id, $ticket_name, $available) {
        if (!$this->is_ribbon_system_enabled(true, $event_id, $ticket_name)) {
            return '';
        }
        $low_threshold = $this->get_low_stock_threshold(5, $event_id, $ticket_name);
        $very_low_threshold = $this->get_very_low_stock_threshold(2, $event_id, $ticket_name);
        $ribbon = '';
        if ($available <= 0) {
            $ribbon = '<div class="mep-stock-ribbon mep-sold-out">SOLD OUT</div>';
        } elseif ($available <= $very_low_threshold) {
            $ribbon = '<div class="mep-stock-ribbon mep-very-low-stock">LAST CHANCE</div>';
        } elseif ($available <= $low_threshold) {
            $ribbon = '<div class="mep-stock-ribbon mep-low-stock">LIMITED</div>';
        }
        echo $ribbon;
    }
    
    public function display_stock_warnings($event_id, $ticket_name, $available) {
        if (!$this->is_ribbon_system_enabled(true, $event_id, $ticket_name)) {
            return '';
        }
        
        $low_threshold = $this->get_low_stock_threshold(5, $event_id, $ticket_name);
        $very_low_threshold = $this->get_very_low_stock_threshold(2, $event_id, $ticket_name);
        
        $warning = '';
        
        if ($available <= 0) {
            $text = mep_get_option('mep_sold_out_text', 'low_stock_setting_sec', 'This ticket type is no longer available');
            $warning = '<div class="mep-stock-warning mep-sold-out-warning">' . esc_html($text) . '</div>';
        } elseif ($available <= $very_low_threshold) {
            $text = mep_get_option('mep_very_low_stock_text', 'low_stock_setting_sec', 'Only {count} tickets left!');
            $text = str_replace('{count}', $available, $text);
            $warning = '<div class="mep-stock-warning mep-very-low-warning">' . esc_html($text) . '</div>';
        } elseif ($available <= $low_threshold) {
            $text = mep_get_option('mep_low_stock_text', 'low_stock_setting_sec', 'Hurry! Only {count} tickets remaining');
            $text = str_replace('{count}', $available, $text);
            $warning = '<div class="mep-stock-warning mep-low-warning">' . esc_html($text) . '</div>';
        }
        
        echo $warning;
    }
    
    public function send_low_stock_email($event_id, $ticket_name, $available, $stock_level) {
        static $sent_this_request = array();
        $unique_key = $event_id . '|' . $ticket_name . '|' . $stock_level;
        if (isset($sent_this_request[$unique_key])) {
            return;
        }
        $sent_this_request[$unique_key] = true;
        $email_enabled = mep_get_option('mep_enable_low_stock_email', 'low_stock_setting_sec', 'no');
        if ($email_enabled !== 'yes') {
            return;
        }
        $notification_key = 'mep_low_stock_sent_' . $event_id . '_' . sanitize_title($ticket_name) . '_' . $stock_level;
        if (get_transient($notification_key)) {
            return;
        }
        $event_title = get_the_title($event_id);
        $event_url = get_permalink($event_id);
        $recipients = mep_get_option('mep_low_stock_email_recipients', 'low_stock_setting_sec', get_option('admin_email'));
        $subject = mep_get_option('mep_low_stock_email_subject', 'low_stock_setting_sec', 'Low Stock Alert: {ticket_name} for {event_title}');
        // Force the HTML template for the message
        $message = $this->get_default_html_email_template();
        $subject = str_replace(
            array('{event_title}', '{ticket_name}'),
            array($event_title, $ticket_name),
            $subject
        );
        $message = str_replace(
            array('{event_title}', '{ticket_name}', '{remaining_count}', '{event_url}'),
            array($event_title, $ticket_name, $available, $event_url),
            $message
        );
        $recipients_array = array_map('trim', explode(',', $recipients));
        foreach ($recipients_array as $recipient) {
            if (is_email($recipient)) {
                $result = wp_mail($recipient, $subject, $message);
            }
        }
        set_transient($notification_key, true, HOUR_IN_SECONDS);
    }
    
    public function set_html_content_type() {
        return 'text/html';
    }
    
    public function get_default_html_email_template() {
        return '<div style="font-family:Arial,sans-serif;max-width:500px;margin:0 auto;background:#fff;border:1px solid #eee;padding:24px;">
            <h2 style="color:#dc3545;margin-top:0;">🚨 Low Stock Alert!</h2>
            <p style="font-size:16px;">
                <strong>Event:</strong> {event_title}<br>
                <strong>Ticket Type:</strong> {ticket_name}<br>
                <strong>Remaining:</strong> <span style="color:#fd7e14;font-size:18px;">{remaining_count}</span>
            </p>
            <p style="margin:24px 0;">
                <a href="{event_url}" style="background:#0073aa;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;font-weight:bold;display:inline-block;">View Event</a>
            </p>
            <hr style="border:none;border-top:1px solid #eee;margin:32px 0 16px 0;">
            <p style="font-size:12px;color:#888;">This is an automated notification from <strong>Mage EventPress</strong>.</p>
        </div>';
    }
    
    public function inject_ribbon_css() {
        static $css_added = false;
        if (!$css_added && $this->is_ribbon_system_enabled(true, 0, '')) {
            echo '<style>
                .mep_ticket_item { position: relative; }
                .mep-stock-ribbon {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    padding: 5px 10px;
                    font-size: 11px;
                    font-weight: bold;
                    color: white;
                    border-radius: 15px;
                    z-index: 10;
                    text-transform: uppercase;
                }
                .mep-sold-out { background: #dc3545; }
                .mep-very-low-stock { background: #fd7e14; animation: mep_pulse 1s infinite; }
                .mep-low-stock { background: #ffc107; color: #212529; }
                .mep-stock-warning {
                    font-size: 12px;
                    padding: 5px 8px;
                    margin-top: 5px;
                    border-radius: 4px;
                    font-weight: 500;
                }
                .mep-sold-out-warning { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
                .mep-very-low-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
                .mep-low-warning { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
                @keyframes mep_pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                    100% { transform: scale(1); }
                }
            </style>';
            $css_added = true;
        }
    }
    
    public function should_hide_default_remaining($hide, $event_id, $ticket_name, $available) {
        if (!$this->is_ribbon_system_enabled(true, $event_id, $ticket_name)) {
            return false;
        }
        
        $low_threshold = $this->get_low_stock_threshold(5, $event_id, $ticket_name);
        return ($available <= $low_threshold);
    }
}

MEP_Low_Stock_Notification::get_instance();