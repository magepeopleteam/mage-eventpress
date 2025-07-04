<?php
/*
* Complete Documentation for Event Manager
* @Author 		MagePeople Team
* Copyright: 	mage-people.com
*/

if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

// Extend the main documentation class with remaining methods
add_action('admin_init', function() {
    if (class_exists('MPWEM_Documentation')) {
        // Add remaining methods to the documentation class
        
        // Event Settings Documentation
        add_action('mep_render_event_settings_docs', function() {
            ?>
            <h2><?php esc_html_e('Event Settings', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Configure specific settings for individual events including display options, notifications, and advanced features.', 'mage-eventpress'); ?></p>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Event Display Settings', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Control how event information is displayed to visitors.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Display Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Show End Date/Time:</strong> <?php esc_html_e('Display when the event ends', 'mage-eventpress'); ?></li>
                        <li><strong>Show Available Seats:</strong> <?php esc_html_e('Display remaining tickets count', 'mage-eventpress'); ?></li>
                        <li><strong>Hide Event After Expiry:</strong> <?php esc_html_e('Remove from listings after event ends', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Advanced Settings', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Enhanced features available with Pro version.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Pro Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Speaker Management:</strong> <?php esc_html_e('Add speakers with photos and bios', 'mage-eventpress'); ?></li>
                        <li><strong>Timeline/Schedule:</strong> <?php esc_html_e('Detailed event agenda', 'mage-eventpress'); ?></li>
                        <li><strong>FAQ Section:</strong> <?php esc_html_e('Frequently asked questions', 'mage-eventpress'); ?></li>
                        <li><strong>Gallery:</strong> <?php esc_html_e('Event photo galleries', 'mage-eventpress'); ?></li>
                        <li><strong>Related Events:</strong> <?php esc_html_e('Show similar events', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>
            <?php
        });

        // Global Settings Additional Tabs
        add_action('mep_render_additional_global_settings_docs', function() {
            ?>
            <h3><?php esc_html_e('Event List Settings Tab', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('List Display Options', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Control how events appear in listing pages and archives.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Display Settings:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Events Per Page:</strong> <?php esc_html_e('Number of events to show per page', 'mage-eventpress'); ?></li>
                        <li><strong>Show Pagination:</strong> <?php esc_html_e('Enable/disable page navigation', 'mage-eventpress'); ?></li>
                        <li><strong>Default View:</strong> <?php esc_html_e('Grid, list, or calendar view', 'mage-eventpress'); ?></li>
                        <li><strong>Show Filters:</strong> <?php esc_html_e('Category and date filtering options', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Single Event Settings Tab', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Event Detail Page Options', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure what information appears on individual event pages.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Content Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Show Event Info:</strong> <?php esc_html_e('Date, time, location details', 'mage-eventpress'); ?></li>
                        <li><strong>Show Organizer:</strong> <?php esc_html_e('Event organizer information', 'mage-eventpress'); ?></li>
                        <li><strong>Show Social Share:</strong> <?php esc_html_e('Social media sharing buttons', 'mage-eventpress'); ?></li>
                        <li><strong>Show Related Events:</strong> <?php esc_html_e('Similar events section', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Email Settings Tab', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Email Notifications', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure automatic email communications for bookings and events.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Email Types:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Booking Confirmation:</strong> <?php esc_html_e('Sent when customer completes purchase', 'mage-eventpress'); ?></li>
                        <li><strong>Event Reminder:</strong> <?php esc_html_e('Sent before event starts', 'mage-eventpress'); ?></li>
                        <li><strong>Cancellation Notice:</strong> <?php esc_html_e('Sent if event is cancelled', 'mage-eventpress'); ?></li>
                        <li><strong>Admin Notification:</strong> <?php esc_html_e('Notify admin of new bookings', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Email templates can be customized in WooCommerce → Settings → Emails.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <h3><?php esc_html_e('Date & Time Format Settings Tab', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Display Formats', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Control how dates and times are displayed throughout the plugin.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Format Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Date Format:</strong> <?php esc_html_e('How dates appear (MM/DD/YYYY, DD-MM-YYYY, etc.)', 'mage-eventpress'); ?></li>
                        <li><strong>Time Format:</strong> <?php esc_html_e('12-hour or 24-hour time display', 'mage-eventpress'); ?></li>
                        <li><strong>Timezone Display:</strong> <?php esc_html_e('Show timezone with times', 'mage-eventpress'); ?></li>
                        <li><strong>Calendar Language:</strong> <?php esc_html_e('Language for month/day names', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Style Settings Tab', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Visual Customization', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Customize the appearance of events to match your site design.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Style Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Primary Color:</strong> <?php esc_html_e('Main accent color for buttons and highlights', 'mage-eventpress'); ?></li>
                        <li><strong>Secondary Color:</strong> <?php esc_html_e('Supporting color for backgrounds', 'mage-eventpress'); ?></li>
                        <li><strong>Typography:</strong> <?php esc_html_e('Font choices for headings and text', 'mage-eventpress'); ?></li>
                        <li><strong>Button Styles:</strong> <?php esc_html_e('Customize booking button appearance', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Translation Settings Tab', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Text Customization', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Change text labels and messages throughout the plugin without editing code.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Customizable Text:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Button Text:</strong> <?php esc_html_e('"Book Now", "Register", "Add to Cart"', 'mage-eventpress'); ?></li>
                        <li><strong>Status Messages:</strong> <?php esc_html_e('"Sold Out", "Available", "Expired"', 'mage-eventpress'); ?></li>
                        <li><strong>Form Labels:</strong> <?php esc_html_e('Booking form field labels', 'mage-eventpress'); ?></li>
                        <li><strong>Error Messages:</strong> <?php esc_html_e('User-facing error messages', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>
            <?php
        });

        // Best Practices Documentation
        add_action('mep_render_best_practices_docs', function() {
            ?>
            <h2><?php esc_html_e('Best Practices & Tips', 'mage-eventpress'); ?></h2>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Event Planning Best Practices', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Follow these guidelines for successful event management.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Planning Tips:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Set up events at least 2-4 weeks before the date', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Create compelling event descriptions with clear benefits', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Use high-quality featured images (1200x800px recommended)', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Set realistic ticket quantities to avoid overselling', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Test the booking process before promoting the event', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Performance Optimization', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Keep your event site running smoothly with these optimization tips.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Optimization Tips:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Optimize images before uploading (use WebP format when possible)', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Use caching plugins for better load times', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Regularly clean up expired events to reduce database size', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Monitor site performance during high-traffic ticket releases', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Troubleshooting Common Issues', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Solutions for frequently encountered problems.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Common Problems & Solutions:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Events not showing:</strong> <?php esc_html_e('Check if events are published and dates are correct', 'mage-eventpress'); ?></li>
                        <li><strong>Booking errors:</strong> <?php esc_html_e('Verify WooCommerce is active and properly configured', 'mage-eventpress'); ?></li>
                        <li><strong>Email not sending:</strong> <?php esc_html_e('Check WooCommerce email settings and SMTP configuration', 'mage-eventpress'); ?></li>
                        <li><strong>Payment issues:</strong> <?php esc_html_e('Ensure payment gateways are properly set up in WooCommerce', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>
            <?php
        });
    }
});
?> 