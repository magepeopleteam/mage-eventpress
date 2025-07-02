<?php
/*
* Complete Settings Documentation for ALL Event Manager Settings
* @Author 		MagePeople Team
* Copyright: 	mage-people.com
*/

if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

// Helper function to generate image placeholders for any setting
function mep_generate_setting_images($setting_key, $setting_name, $has_frontend = true) {
    $admin_image = "https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/" . sanitize_title($setting_key) . ".png";
    $frontend_image = "https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/" . sanitize_title($setting_key) . "-display.png";
    
    $output = '<div class="setting-screenshots">';
    
    // Admin image - First column
    $output .= '<a href="' . esc_url($admin_image) . '" class="mep-lightbox">';
    $output .= '<img src="' . esc_url($admin_image) . '" alt="' . esc_attr($setting_name) . ' - Admin Setting">';
    $output .= '</a>';
    
    // Frontend image - Second column
    if ($has_frontend) {
        $output .= '<a href="' . esc_url($frontend_image) . '" class="mep-lightbox">';
        $output .= '<img src="' . esc_url($frontend_image) . '" alt="' . esc_attr($setting_name) . ' - Frontend Display">';
        $output .= '</a>';
    } else {
        // Placeholder for frontend if not available
        $output .= '<div style="flex: 1; max-width: 50%; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc; color: #999; font-style: italic; padding: 20px; text-align: center;">Frontend screenshot not available</div>';
    }
    
    $output .= '</div>';
    return $output;
}

// Add lightbox CSS and JavaScript
function mep_add_lightbox_styles_and_scripts() {
    ?>
    <style>
        .setting-screenshots {
            display: flex;
            flex-wrap: nowrap;
            gap: 15px;
            margin: 15px 0;
            align-items: flex-start;
        }
        
        .setting-screenshots a {
            display: block;
            text-decoration: none;
            flex: 1;
            max-width: 50%;
        }
        
        .setting-screenshots a:first-child {
            /* Admin image - First column */
            order: 1;
        }
        
        .setting-screenshots a:last-child {
            /* Frontend image - Second column */
            order: 2;
        }
        
        .setting-screenshots img {
            width: 100%;
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            object-fit: cover;
            object-position: top;
        }
        
        .setting-screenshots img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        /* Labels for columns */
        .setting-screenshots a::before {
            content: attr(data-label);
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
            text-align: center;
        }
        
        .setting-screenshots a:first-child::before {
            content: "Admin Setting";
        }
        
        .setting-screenshots a:last-child::before {
            content: "Frontend Display";
        }
        
        /* Lightbox overlay */
        .mep-lightbox-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 999999;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .mep-lightbox-content {
            max-width: 95%;
            max-height: 95%;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .mep-lightbox-content img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            object-fit: contain;
        }
        
        .mep-lightbox-close {
            position: absolute;
            top: -50px;
            right: -10px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(0,0,0,0.5);
            border: none;
            padding: 10px 15px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }
        
        .mep-lightbox-close:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }
        
        @media (max-width: 768px) {
            .setting-screenshots {
                flex-direction: column;
                flex-wrap: wrap;
            }
            
            .setting-screenshots a {
                max-width: 100%;
                flex: none;
            }
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Create lightbox overlay
        if (!$('.mep-lightbox-overlay').length) {
            $('body').append('<div class="mep-lightbox-overlay"><div class="mep-lightbox-content"><button class="mep-lightbox-close">&times;</button><img src="" alt=""></div></div>');
        }
        
        // Handle lightbox click
        $(document).on('click', '.mep-lightbox', function(e) {
            e.preventDefault();
            var imgSrc = $(this).find('img').attr('src');
            var imgAlt = $(this).find('img').attr('alt');
            
            // Set image source and alt
            var $lightboxImg = $('.mep-lightbox-overlay .mep-lightbox-content img');
            $lightboxImg.attr('src', imgSrc).attr('alt', imgAlt);
            
            // Show lightbox
            $('.mep-lightbox-overlay').css('display', 'flex').hide().fadeIn(300);
            
            // Prevent body scrolling when lightbox is open
            $('body').css('overflow', 'hidden');
            
            // Ensure image fits within viewport
            $lightboxImg.on('load', function() {
                var windowWidth = $(window).width();
                var windowHeight = $(window).height();
                var imgWidth = this.naturalWidth;
                var imgHeight = this.naturalHeight;
                
                // Calculate maximum dimensions (95% of viewport)
                var maxWidth = windowWidth * 0.95;
                var maxHeight = windowHeight * 0.95;
                
                // Apply sizing
                $(this).css({
                    'max-width': maxWidth + 'px',
                    'max-height': maxHeight + 'px',
                    'width': 'auto',
                    'height': 'auto'
                });
            });
        });
        
        // Close lightbox function
        function closeLightbox() {
            $('.mep-lightbox-overlay').fadeOut(300, function() {
                $('body').css('overflow', 'auto');
            });
        }
        
        // Close lightbox events
        $(document).on('click', '.mep-lightbox-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeLightbox();
        });
        
        $(document).on('click', '.mep-lightbox-overlay', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
        
        // Close lightbox with ESC key
        $(document).keyup(function(e) {
            if (e.keyCode === 27) {
                closeLightbox();
            }
        });
        
        // Handle window resize
        $(window).resize(function() {
            if ($('.mep-lightbox-overlay').is(':visible')) {
                var $lightboxImg = $('.mep-lightbox-overlay .mep-lightbox-content img');
                var windowWidth = $(window).width();
                var windowHeight = $(window).height();
                
                var maxWidth = windowWidth * 0.95;
                var maxHeight = windowHeight * 0.95;
                
                $lightboxImg.css({
                    'max-width': maxWidth + 'px',
                    'max-height': maxHeight + 'px'
                });
            }
        });
    });
    </script>
    <?php
}

// Complete documentation for ALL settings found in the codebase
class MPWEM_Complete_Settings_Documentation {
    
    public static function render_complete_global_settings_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Complete Global Settings Documentation', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('This section covers every single setting available in the global settings area.', 'mage-eventpress'); ?></p>

        <h3><?php esc_html_e('General Settings Tab - Complete List', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Advanced Global Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Additional advanced settings that control various aspects of event functionality.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('All Global Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Google Map API Key:</strong> <?php esc_html_e('Enter your Google Maps API key for interactive maps. Get it from Google Maps Platform.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/Events-Settings-%E2%80%B9-Google-Api-key.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/Events-Settings-%E2%80%B9-Google-Api-key.png" alt="Google Map API Key Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/google%20-location.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/google%20-location.png" alt="Google Map API Key - Frontend Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Event Expiry Time:</strong> <?php esc_html_e('Choose when events expire - at start time or end time.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event_expiered_time_back.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event_expiered_time_back.png" alt="Event Expiry Time Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event_expiered_time_fornt.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event_expiered_time_fornt.png" alt="Event Expiry Time - Frontend Behavior">
                            </a>
                        </div>
                    </li>
                    <li><strong>Hide Location from Order Page:</strong> <?php esc_html_e('Hide venue details from order confirmation and emails.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide_location_from_order_details_backend.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide_location_from_order_details_backend.png" alt="Hide Location Order Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide_location_from_order_details_front.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide_location_from_order_details_front.png" alt="Hide Location Order - Frontend Result">
                            </a>
                        </div>
                    </li>
                    <li><strong>Hide Date from Order Page:</strong> <?php esc_html_e('Hide event date from order confirmation and emails.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide_date_from_order_details_backend.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide_date_from_order_details_backend.png" alt="Hide Date Order Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide_date_from_order_details_front.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide_date_from_order_details_front.png" alt="Hide Date Order - Frontend Result">
                            </a>
                        </div>
                    </li>
                    <li><strong>Hide Expired Events from Calendar:</strong> <?php esc_html_e('Remove past events from calendar display.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/general-settings-hide-expired-calendar.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/general-settings-hide-expired-calendar.png" alt="Hide Expired Calendar Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/calendar-expired-hidden.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/calendar-expired-hidden.png" alt="Hide Expired Calendar - Frontend Result">
                            </a>
                        </div>
                    </li>
                    <li><strong>Redirect to Checkout after Booking:</strong> <?php esc_html_e('Auto-redirect to checkout page after adding event to cart.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/redirect-checkout-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/redirect-checkout-setting.png" alt="Redirect Checkout Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/checkout-redirect-flow.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/checkout-redirect-flow.png" alt="Redirect Checkout - Frontend Flow">
                            </a>
                        </div>
                    </li>
                    <li><strong>Show 0 Price as Free:</strong> <?php esc_html_e('Display "Free" instead of "$0" for no-cost events.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/show-free-price-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/show-free-price-setting.png" alt="Show Free Price Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/free-price-display.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/free-price-display.png" alt="Show Free Price - Frontend Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Ticket Expire Before Minutes:</strong> <?php esc_html_e('Stop ticket sales X minutes before event starts.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/ticket-expire-minutes-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/ticket-expire-minutes-setting.png" alt="Ticket Expire Minutes Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/ticket-sales-stopped.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/ticket-sales-stopped.png" alt="Ticket Expire Minutes - Frontend Effect">
                            </a>
                        </div>
                    </li>
                    <li><strong>Load Font Awesome from Theme:</strong> <?php esc_html_e('Use theme\'s Font Awesome instead of plugin\'s version.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/font-awesome-theme-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/font-awesome-theme-setting.png" alt="Font Awesome Theme Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/font-awesome-icons.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/font-awesome-icons.png" alt="Font Awesome Theme - Frontend Icons">
                            </a>
                        </div>
                    </li>
                    <li><strong>Load Flat Icon from Theme:</strong> <?php esc_html_e('Use theme\'s Flat Icons instead of plugin\'s version.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/flat-icon-theme-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/flat-icon-theme-setting.png" alt="Flat Icon Theme Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/flat-icons-display.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/flat-icons-display.png" alt="Flat Icon Theme - Frontend Icons">
                            </a>
                        </div>
                    </li>
                    <li><strong>Speed up Event List Page:</strong> <?php esc_html_e('Optimize loading by disabling some features (waitlist, seat count).', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/speed-up-list-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/speed-up-list-setting.png" alt="Speed Up List Page Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/optimized-event-list.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/optimized-event-list.png" alt="Speed Up List Page - Frontend Performance">
                            </a>
                        </div>
                    </li>
                    <li><strong>Hide Fully Booked Events:</strong> <?php esc_html_e('Remove sold-out events from event listings.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide-fully-booked-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hide-fully-booked-setting.png" alt="Hide Fully Booked Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/no-sold-out-events.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/no-sold-out-events.png" alt="Hide Fully Booked - Frontend Result">
                            </a>
                        </div>
                    </li>
                    <li><strong>Show Sold Out Ribbon:</strong> <?php esc_html_e('Display "Sold Out" badge on fully booked events.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/sold-out-ribbon-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/sold-out-ribbon-setting.png" alt="Sold Out Ribbon Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/sold-out-ribbon-display.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/sold-out-ribbon-display.png" alt="Sold Out Ribbon - Frontend Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Show Limited Availability Ribbon:</strong> <?php esc_html_e('Display badge when tickets are running low.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/limited-ribbon-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/limited-ribbon-setting.png" alt="Limited Availability Ribbon Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/limited-availability-ribbon.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/limited-availability-ribbon.png" alt="Limited Availability Ribbon - Frontend Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Limited Availability Threshold:</strong> <?php esc_html_e('Number of remaining seats to trigger "Limited" warning.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/limited-threshold-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/limited-threshold-setting.png" alt="Limited Threshold Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/limited-threshold-trigger.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/limited-threshold-trigger.png" alt="Limited Threshold - Frontend Trigger">
                            </a>
                        </div>
                    </li>
                    <li><strong>Show Low Stock Warning:</strong> <?php esc_html_e('Display warning when seats are running low.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-warning-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-warning-setting.png" alt="Low Stock Warning Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-warning-display.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-warning-display.png" alt="Low Stock Warning - Frontend Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Low Stock Threshold:</strong> <?php esc_html_e('Number of seats to trigger low stock warning.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-threshold-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-threshold-setting.png" alt="Low Stock Threshold Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-threshold-trigger.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-threshold-trigger.png" alt="Low Stock Threshold - Frontend Trigger">
                            </a>
                        </div>
                    </li>
                    <li><strong>Low Stock Warning Text:</strong> <?php esc_html_e('Custom message for low stock alerts.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-text-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-text-setting.png" alt="Low Stock Warning Text Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-text-display.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-text-display.png" alt="Low Stock Warning Text - Frontend Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Send Low Stock Email Notifications:</strong> <?php esc_html_e('Email admin when seats are running low.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-email-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-email-setting.png" alt="Low Stock Email Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-email-example.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/low-stock-email-example.png" alt="Low Stock Email - Example">
                            </a>
                        </div>
                    </li>
                    <li><strong>Show Hidden WooCommerce Products:</strong> <?php esc_html_e('Display auto-created event products in WooCommerce.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/show-hidden-products-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/show-hidden-products-setting.png" alt="Show Hidden Products Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hidden-products-display.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/hidden-products-display.png" alt="Hidden Products - WooCommerce Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Google Map Zoom Level:</strong> <?php esc_html_e('Set map zoom level (5-25) for location display.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/map-zoom-level-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/map-zoom-level-setting.png" alt="Map Zoom Level Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/map-zoom-level-display.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/map-zoom-level-display.png" alt="Map Zoom Level - Frontend Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Show Event Sidebar Widgets:</strong> <?php esc_html_e('Enable widget area for event pages.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/sidebar-widgets-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/sidebar-widgets-setting.png" alt="Sidebar Widgets Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/sidebar-widgets-display.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/sidebar-widgets-display.png" alt="Sidebar Widgets - Frontend Display">
                            </a>
                        </div>
                    </li>
                    <li><strong>Shipping Method on Events:</strong> <?php esc_html_e('Enable/disable shipping for virtual event products.', 'mage-eventpress'); ?>
                        <div class="setting-screenshots">
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/shipping-method-setting.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/shipping-method-setting.png" alt="Shipping Method Setting - Admin">
                            </a>
                            <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/shipping-method-checkout.png" class="mep-lightbox">
                                <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/shipping-method-checkout.png" alt="Shipping Method - Checkout Display">
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <h3><?php esc_html_e('Single Event Settings - Hide/Show Options', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Detail Page Display Controls', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Fine-tune what information appears on individual event detail pages.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Available Hide/Show Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Hide Event Date Section:</strong> <?php esc_html_e('Remove date display from event details', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Event Time Section:</strong> <?php esc_html_e('Remove time display from event details', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Event Location Section:</strong> <?php esc_html_e('Remove venue info from event details', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Total Seats Section:</strong> <?php esc_html_e('Remove capacity info from event details', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Organizer Section:</strong> <?php esc_html_e('Remove "Organized By" from event details', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Address Section:</strong> <?php esc_html_e('Remove full address from event details', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Event Schedule:</strong> <?php esc_html_e('Remove timeline/schedule from event details', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Share This Section:</strong> <?php esc_html_e('Remove social sharing buttons', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Add Calendar Button:</strong> <?php esc_html_e('Remove "Add to Calendar" functionality', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Description Title:</strong> <?php esc_html_e('Remove the "Description" heading', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Left Sidebar Title:</strong> <?php esc_html_e('Remove sidebar section headings', 'mage-eventpress'); ?></li>
                    <li><strong>Hide Event Time Below Title:</strong> <?php esc_html_e('Remove time display under event title', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/single-event-hide-show-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/single-event-hide-show-settings.png" alt="Single Event Hide/Show Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/single-event-display-options.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/single-event-display-options.png" alt="Single Event Display Options - Frontend">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Email Settings - Complete Configuration', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Email System Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Complete email configuration options for automated event communications.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Email Configuration Fields:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Email Sent on Order Status:</strong> <?php esc_html_e('Choose when to send confirmation emails (Processing/Completed)', 'mage-eventpress'); ?></li>
                    <li><strong>Email From Name:</strong> <?php esc_html_e('Sender name for all event emails', 'mage-eventpress'); ?></li>
                    <li><strong>From Email Address:</strong> <?php esc_html_e('Sender email address for event notifications', 'mage-eventpress'); ?></li>
                    <li><strong>Email Subject:</strong> <?php esc_html_e('Default subject line for event emails', 'mage-eventpress'); ?></li>
                    <li><strong>Confirmation Email Text:</strong> <?php esc_html_e('Template for confirmation emails with dynamic tags', 'mage-eventpress'); ?></li>
                    <li><strong>Send to Billing Email:</strong> <?php esc_html_e('Send confirmations to customer\'s billing email', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Available Dynamic Tags:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><code>{name}</code> - <?php esc_html_e('Attendee name', 'mage-eventpress'); ?></li>
                    <li><code>{event}</code> - <?php esc_html_e('Event name', 'mage-eventpress'); ?></li>
                    <li><code>{ticket_type}</code> - <?php esc_html_e('Ticket type name', 'mage-eventpress'); ?></li>
                    <li><code>{event_date}</code> - <?php esc_html_e('Event date', 'mage-eventpress'); ?></li>
                    <li><code>{event_time}</code> - <?php esc_html_e('Start time', 'mage-eventpress'); ?></li>
                    <li><code>{event_datetime}</code> - <?php esc_html_e('Full date and time', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-settings-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-settings-admin.png" alt="Email Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-settings-preview.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-settings-preview.png" alt="Email Settings - Preview">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Translation/Label Settings - All Text Customization', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Complete Text Label Customization', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Customize every text label and message displayed by the plugin.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-screenshots">
                <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/translation-settings-admin.png" class="mep-lightbox">
                    <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/translation-settings-admin.png" alt="Translation Settings - Admin">
                </a>
                <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/translation-frontend-display.png" class="mep-lightbox">
                    <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/translation-frontend-display.png" alt="Translation Settings - Frontend Display">
                </a>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('All Customizable Labels:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Book Now:</strong> <?php esc_html_e('Button text for event booking', 'mage-eventpress'); ?></li>
                    <li><strong>Price Starts from:</strong> <?php esc_html_e('Price prefix for event listings', 'mage-eventpress'); ?></li>
                    <li><strong>Price:</strong> <?php esc_html_e('Single price label', 'mage-eventpress'); ?></li>
                    <li><strong>Free:</strong> <?php esc_html_e('Text for zero-cost events', 'mage-eventpress'); ?></li>
                    <li><strong>Ticket Type:</strong> <?php esc_html_e('Label for ticket selection', 'mage-eventpress'); ?></li>
                    <li><strong>Extra Service:</strong> <?php esc_html_e('Label for add-on services', 'mage-eventpress'); ?></li>
                    <li><strong>Register This Event:</strong> <?php esc_html_e('Main registration button text', 'mage-eventpress'); ?></li>
                    <li><strong>Event Expired:</strong> <?php esc_html_e('Message for past events', 'mage-eventpress'); ?></li>
                    <li><strong>Ticket/Left:</strong> <?php esc_html_e('Labels for availability display', 'mage-eventpress'); ?></li>
                    <li><strong>Attendee info:</strong> <?php esc_html_e('Form section label', 'mage-eventpress'); ?></li>
                    <li><strong>Select Ticket Error Message:</strong> <?php esc_html_e('Validation error text', 'mage-eventpress'); ?></li>
                    <li><strong>Virtual Event:</strong> <?php esc_html_e('Label for online events', 'mage-eventpress'); ?></li>
                    <li><strong>Multi Date Event:</strong> <?php esc_html_e('Label for multi-day events', 'mage-eventpress'); ?></li>
                    <li><strong>View More Date/Hide Date Lists:</strong> <?php esc_html_e('Date navigation buttons', 'mage-eventpress'); ?></li>
                    <li><strong>Recurring Event:</strong> <?php esc_html_e('Label for repeating events', 'mage-eventpress'); ?></li>
                    <li><strong>Sold Out:</strong> <?php esc_html_e('Label for fully booked events', 'mage-eventpress'); ?></li>
                    <li><strong>Limited Availability:</strong> <?php esc_html_e('Label for low stock events', 'mage-eventpress'); ?></li>
                </ul>
            </div>
        </div>

        <h3><?php esc_html_e('Style Settings - Visual Customization', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Color and Visual Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Customize the visual appearance of events throughout your site.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Available Style Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Primary Color:</strong> <?php esc_html_e('Main brand color for icons, buttons, and highlights', 'mage-eventpress'); ?></li>
                    <li><strong>Secondary Color:</strong> <?php esc_html_e('Supporting color for backgrounds and accents', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('These colors will be applied across all event displays, maintaining consistent branding throughout your site.', 'mage-eventpress'); ?>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Icon Settings - Complete Icon Customization', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Icon Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Customize icons used throughout the event system for better visual communication.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('All Customizable Icons:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event Date Icon:</strong> <?php esc_html_e('Icon displayed next to event dates', 'mage-eventpress'); ?></li>
                    <li><strong>Event Time Icon:</strong> <?php esc_html_e('Icon displayed next to event times', 'mage-eventpress'); ?></li>
                    <li><strong>Event Location Icon:</strong> <?php esc_html_e('Icon displayed next to venue information', 'mage-eventpress'); ?></li>
                    <li><strong>Event Organizer Icon:</strong> <?php esc_html_e('Icon displayed next to organizer info', 'mage-eventpress'); ?></li>
                    <li><strong>Location List Icon:</strong> <?php esc_html_e('Icon for sidebar location listings', 'mage-eventpress'); ?></li>
                    <li><strong>Social Share Icons:</strong> <?php esc_html_e('Facebook and other social platform icons', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Icons use Font Awesome classes. Examples: "fas fa-calendar", "fab fa-facebook"', 'mage-eventpress'); ?>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Date & Time Format Settings - Complete Configuration', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Date & Time Display Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure how dates and times are displayed throughout the plugin.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Date & Time Format Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event Date Format:</strong> <?php esc_html_e('Choose how dates appear (e.g., F j, Y or Y-m-d)', 'mage-eventpress'); ?></li>
                    <li><strong>Event Time Format:</strong> <?php esc_html_e('Choose how times appear (e.g., g:i a or H:i)', 'mage-eventpress'); ?></li>
                    <li><strong>Time Separator:</strong> <?php esc_html_e('Character to separate start and end times', 'mage-eventpress'); ?></li>
                    <li><strong>Week Days Format:</strong> <?php esc_html_e('Full name (Monday) or abbreviated (Mon)', 'mage-eventpress'); ?></li>
                    <li><strong>Month Names Format:</strong> <?php esc_html_e('Full name (January) or abbreviated (Jan)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('These settings affect how dates and times are displayed in event listings, detail pages, emails, and tickets.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/date-time-format-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/date-time-format-settings.png" alt="Date & Time Format Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/date-time-format-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/date-time-format-frontend.png" alt="Date & Time Format - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Carousel Settings - Complete Configuration', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Carousel Display Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure carousel display options for event sliders.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Carousel Configuration Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Items Per Slide:</strong> <?php esc_html_e('Number of events to show per slide (default: 3)', 'mage-eventpress'); ?></li>
                    <li><strong>Loop:</strong> <?php esc_html_e('Enable/disable infinite loop scrolling', 'mage-eventpress'); ?></li>
                    <li><strong>Navigation Arrows:</strong> <?php esc_html_e('Show/hide prev/next navigation arrows', 'mage-eventpress'); ?></li>
                    <li><strong>Pagination Dots:</strong> <?php esc_html_e('Show/hide pagination indicator dots', 'mage-eventpress'); ?></li>
                    <li><strong>Autoplay:</strong> <?php esc_html_e('Enable/disable automatic sliding', 'mage-eventpress'); ?></li>
                    <li><strong>Autoplay Speed:</strong> <?php esc_html_e('Time between slides in milliseconds', 'mage-eventpress'); ?></li>
                    <li><strong>Responsive Breakpoints:</strong> <?php esc_html_e('Configure items per slide at different screen sizes', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Carousel settings apply to event sliders displayed using the carousel shortcode or block.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/carousel-settings-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/carousel-settings-admin.png" alt="Carousel Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/carousel-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/carousel-frontend-display.png" alt="Carousel - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Slider Settings - Complete Configuration', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Slider Display Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure slider display options for featured events.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Slider Configuration Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Slider Type:</strong> <?php esc_html_e('Choose slider style (default, fade, slide)', 'mage-eventpress'); ?></li>
                    <li><strong>Transition Effect:</strong> <?php esc_html_e('Animation between slides', 'mage-eventpress'); ?></li>
                    <li><strong>Slider Height:</strong> <?php esc_html_e('Height of the slider in pixels', 'mage-eventpress'); ?></li>
                    <li><strong>Show Caption:</strong> <?php esc_html_e('Display event title and details on slider', 'mage-eventpress'); ?></li>
                    <li><strong>Caption Position:</strong> <?php esc_html_e('Where to display caption (top, bottom, left, right)', 'mage-eventpress'); ?></li>
                    <li><strong>Autoplay:</strong> <?php esc_html_e('Enable/disable automatic sliding', 'mage-eventpress'); ?></li>
                    <li><strong>Autoplay Speed:</strong> <?php esc_html_e('Time between slides in milliseconds', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Slider settings apply to featured event displays and can be customized per shortcode.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/slider-settings-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/slider-settings-admin.png" alt="Slider Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/slider-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/slider-frontend-display.png" alt="Slider - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Recurring Events - Complete Configuration', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Recurring Event Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure options for recurring and multi-date events.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Recurring Event Types:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Single Event:</strong> <?php esc_html_e('One-time event with a single date/time', 'mage-eventpress'); ?></li>
                    <li><strong>Particular Event:</strong> <?php esc_html_e('Multi-date event with specific dates/times', 'mage-eventpress'); ?></li>
                    <li><strong>Repeated Event:</strong> <?php esc_html_e('Event that occurs regularly based on a pattern', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Off Days Configuration:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Weekly Off Days:</strong> <?php esc_html_e('Select days of the week when the event doesn\'t occur', 'mage-eventpress'); ?></li>
                    <li><strong>Specific Off Dates:</strong> <?php esc_html_e('Select specific calendar dates when the event doesn\'t occur', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Time Slot Configuration:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Multiple Time Slots:</strong> <?php esc_html_e('Configure multiple time options for each event date', 'mage-eventpress'); ?></li>
                    <li><strong>Time Slot Capacity:</strong> <?php esc_html_e('Set separate capacity limits for each time slot', 'mage-eventpress'); ?></li>
                    <li><strong>Time Slot Pricing:</strong> <?php esc_html_e('Configure different pricing for different time slots', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Recurring event settings provide powerful options for creating complex event schedules with fine-grained control over dates, times, and availability.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/recurring-event-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/recurring-event-settings.png" alt="Recurring Event Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/recurring-event-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/recurring-event-frontend.png" alt="Recurring Event - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('REST API Settings', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('REST API Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure REST API access for event data.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('API Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable REST API:</strong> <?php esc_html_e('Make event data available via WordPress REST API', 'mage-eventpress'); ?></li>
                    <li><strong>Block/Gutenberg Editor:</strong> <?php esc_html_e('Enable/disable Gutenberg editor for events', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('REST API access is required for Gutenberg editor support and for accessing event data programmatically.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/rest-api-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/rest-api-settings.png" alt="REST API Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/rest-api-response.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/rest-api-response.png" alt="REST API - Response Example">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Multilingual Support', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Multilingual Plugin Integration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure integration with multilingual plugins.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Multilingual Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Multilingual Plugin:</strong> <?php esc_html_e('Select which multilingual plugin you\'re using (None, WPML, Polylang)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Proper multilingual plugin integration ensures event content can be translated correctly.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/multilingual-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/multilingual-settings.png" alt="Multilingual Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/multilingual-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/multilingual-frontend.png" alt="Multilingual - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Troubleshooting Settings', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Troubleshooting Options', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Special settings to resolve specific issues.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Troubleshooting Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Manual Seat Left Fixing:</strong> <?php esc_html_e('Fix "No Seats Available" issue after updating to v4.3.0+', 'mage-eventpress'); ?></li>
                    <li><strong>Event Details Page Fatal Error Fix:</strong> <?php esc_html_e('Resolve fatal errors on event detail pages', 'mage-eventpress'); ?></li>
                    <li><strong>Clear Cart after Checkout:</strong> <?php esc_html_e('Control cart clearing behavior after checkout', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Only enable these settings if you\'re experiencing the specific issues they address. Otherwise, keep them at default values.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/troubleshooting-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/troubleshooting-settings.png" alt="Troubleshooting Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/troubleshooting-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/troubleshooting-frontend.png" alt="Troubleshooting - Frontend Result">
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    public static function render_individual_event_settings_docs() {
        ?>
        <h2><?php esc_html_e('Individual Event Settings - Complete Guide', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Every setting available when creating or editing individual events.', 'mage-eventpress'); ?></p>

        <h3><?php esc_html_e('FAQ Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Frequently Asked Questions Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Create and manage FAQ sections for individual events to address common attendee questions.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('FAQ Components:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>FAQ Description:</strong> <?php esc_html_e('Introductory text explaining the FAQ section purpose', 'mage-eventpress'); ?></li>
                    <li><strong>Questions & Answers:</strong> <?php esc_html_e('Unlimited Q&A pairs with rich text answers', 'mage-eventpress'); ?></li>
                    <li><strong>Collapsible Display:</strong> <?php esc_html_e('Questions expand/collapse for better user experience', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Management Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Add unlimited FAQ items', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Edit existing FAQs inline', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Delete unwanted FAQ items', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Rich text editor for detailed answers', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/faq-settings-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/faq-settings-admin.png" alt="FAQ Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/faq-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/faq-frontend-display.png" alt="FAQ - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Email Text Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Custom Event Email Templates', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Create custom email content specific to individual events, overriding global email templates.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Email Customization Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Custom Email Content:</strong> <?php esc_html_e('Event-specific email templates with rich text editor', 'mage-eventpress'); ?></li>
                    <li><strong>Dynamic Tag Support:</strong> <?php esc_html_e('Use merge tags for personalized content', 'mage-eventpress'); ?></li>
                    <li><strong>Preview System:</strong> <?php esc_html_e('Preview how emails will look before sending', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Available Dynamic Tags:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><code>{name}</code> - <?php esc_html_e('Attendee Name', 'mage-eventpress'); ?></li>
                    <li><code>{event}</code> - <?php esc_html_e('Event Name', 'mage-eventpress'); ?></li>
                    <li><code>{ticket_type}</code> - <?php esc_html_e('Ticket Type', 'mage-eventpress'); ?></li>
                    <li><code>{event_date}</code> - <?php esc_html_e('Event Date', 'mage-eventpress'); ?></li>
                    <li><code>{event_time}</code> - <?php esc_html_e('Start Time', 'mage-eventpress'); ?></li>
                    <li><code>{event_datetime}</code> - <?php esc_html_e('Full DateTime', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-text-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-text-settings.png" alt="Email Text Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-text-preview.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-text-preview.png" alt="Email Text - Preview">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Speaker Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Speaker Management', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Add and manage speakers for events, displaying their information on event pages.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Speaker Configuration:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Speaker Section Label:</strong> <?php esc_html_e('Custom heading for the speakers section', 'mage-eventpress'); ?></li>
                    <li><strong>Speaker Icon:</strong> <?php esc_html_e('Custom icon for the speakers section', 'mage-eventpress'); ?></li>
                    <li><strong>Speaker Selection:</strong> <?php esc_html_e('Choose from pre-created speaker profiles', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Speakers must be created first using the "Event Speakers" post type before being assigned to events.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/Events-Settings-Speaker.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/Events-Settings-Speaker.png" alt="Speaker Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/Events-Settings-Speaker-create.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/Events-Settings-Speaker-create.png" alt="Speaker Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-details-page-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-details-page-frontend.png" alt="Speaker - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Timeline Details Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Schedule & Timeline', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Create detailed event schedules with timeline visualization for complex events.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Timeline Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Timeline Items:</strong> <?php esc_html_e('Unlimited schedule entries with title, time, and description', 'mage-eventpress'); ?></li>
                    <li><strong>Rich Content:</strong> <?php esc_html_e('Full text editor for detailed activity descriptions', 'mage-eventpress'); ?></li>
                    <li><strong>Time Specifications:</strong> <?php esc_html_e('Flexible time format for each timeline item', 'mage-eventpress'); ?></li>
                    <li><strong>Visual Display:</strong> <?php esc_html_e('Numbered timeline presentation on frontend', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Management Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Add/edit/delete timeline items', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Drag-and-drop reordering', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Collapsible item editing', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/timeline-settings-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/timeline-settings-admin.png" alt="Timeline Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/timeline-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/timeline-frontend-display.png" alt="Timeline - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Gallery Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Image Gallery', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Create image galleries and manage thumbnails for enhanced event presentation.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Gallery Configuration:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Slider On/Off:</strong> <?php esc_html_e('Enable/disable gallery slider functionality', 'mage-eventpress'); ?></li>
                    <li><strong>Gallery Images:</strong> <?php esc_html_e('Upload multiple images for event galleries', 'mage-eventpress'); ?></li>
                    <li><strong>List Thumbnail:</strong> <?php esc_html_e('Custom thumbnail for event listings (separate from featured image)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Recommended image ratio: 4:3 (e.g., 1200x900px) for consistent gallery display.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/gallery-settings-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/gallery-settings-admin.png" alt="Gallery Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/gallery-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/gallery-frontend-display.png" alt="Gallery - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Template Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Display Templates', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Choose from different visual templates to customize how individual events are displayed.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Template Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Template Selection:</strong> <?php esc_html_e('Choose from available event layout templates', 'mage-eventpress'); ?></li>
                    <li><strong>Visual Preview:</strong> <?php esc_html_e('See template screenshots before selection', 'mage-eventpress'); ?></li>
                    <li><strong>Override Global:</strong> <?php esc_html_e('Override global template setting for specific events', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Templates can be customized further by overriding them in your theme. See documentation for template override instructions.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/template-settings-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/template-settings-admin.png" alt="Template Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/template-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/template-frontend-display.png" alt="Template - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Related Events Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Related Events Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Display related or similar events on individual event pages to encourage cross-promotion and increased engagement.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Related Events Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Show Related Events Toggle:</strong> <?php esc_html_e('Enable/disable related events display on frontend', 'mage-eventpress'); ?></li>
                    <li><strong>Section Label:</strong> <?php esc_html_e('Custom heading for the related events section', 'mage-eventpress'); ?></li>
                    <li><strong>Event Selection:</strong> <?php esc_html_e('Choose specific events to display as related', 'mage-eventpress'); ?></li>
                    <li><strong>Multi-Select Interface:</strong> <?php esc_html_e('Search and select multiple events with autocomplete', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Display Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Responsive grid layout for related events', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Automatic filtering of expired events', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Consistent styling with main event displays', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Carousel/slider support for multiple related events', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Related events help increase event discovery and can boost overall ticket sales by showcasing similar or complementary events.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/related-events-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/related-events-settings.png" alt="Related Events Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/related-events-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/related-events-frontend.png" alt="Related Events - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Event Organizers - Taxonomy Settings', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Organizer Profile Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Create detailed organizer profiles with contact information and location details for comprehensive event organization.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Organizer Profile Fields:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Location/Venue:</strong> <?php esc_html_e('Primary venue or location associated with the organizer', 'mage-eventpress'); ?></li>
                    <li><strong>Street Address:</strong> <?php esc_html_e('Complete street address for the organizer', 'mage-eventpress'); ?></li>
                    <li><strong>City:</strong> <?php esc_html_e('City where the organizer is based', 'mage-eventpress'); ?></li>
                    <li><strong>State/Province:</strong> <?php esc_html_e('State or province information', 'mage-eventpress'); ?></li>
                    <li><strong>Postal Code:</strong> <?php esc_html_e('ZIP or postal code for location', 'mage-eventpress'); ?></li>
                    <li><strong>Country:</strong> <?php esc_html_e('Country information for international organizers', 'mage-eventpress'); ?></li>
                    <li><strong>Email Address:</strong> <?php esc_html_e('Contact email for the organizer', 'mage-eventpress'); ?></li>
                    <li><strong>Google Maps Integration:</strong> <?php esc_html_e('Interactive map location picker with coordinates', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Advanced Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Google Maps autocomplete for accurate location selection', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Automatic latitude/longitude coordinate capture', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Draggable map markers for precise positioning', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Address validation through Google Places API', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Google Maps integration requires a valid API key configured in global settings.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/organizer-settings-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/organizer-settings-admin.png" alt="Organizer Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/organizer-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/organizer-frontend-display.png" alt="Organizer - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Gutenberg Block Integration', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event List Block Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Use the Gutenberg block editor to display event lists with extensive customization options and filtering capabilities.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Block Attributes - Content Filtering:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Category (cat):</strong> <?php esc_html_e('Filter events by specific category ID (default: 0 = all)', 'mage-eventpress'); ?></li>
                    <li><strong>Organizer (org):</strong> <?php esc_html_e('Filter events by specific organizer ID (default: 0 = all)', 'mage-eventpress'); ?></li>
                    <li><strong>City:</strong> <?php esc_html_e('Filter events by city location', 'mage-eventpress'); ?></li>
                    <li><strong>Country:</strong> <?php esc_html_e('Filter events by country', 'mage-eventpress'); ?></li>
                    <li><strong>Status:</strong> <?php esc_html_e('Show upcoming, past, or all events (default: upcoming)', 'mage-eventpress'); ?></li>
                    <li><strong>Show Count:</strong> <?php esc_html_e('Number of events to display (default: -1 = all)', 'mage-eventpress'); ?></li>
                    <li><strong>Sort Order:</strong> <?php esc_html_e('ASC or DESC sorting (default: ASC)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Block Attributes - Display Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Style:</strong> <?php esc_html_e('Display style - grid, list, carousel, timeline (default: grid)', 'mage-eventpress'); ?></li>
                    <li><strong>Columns:</strong> <?php esc_html_e('Number of columns for grid layout (default: 3)', 'mage-eventpress'); ?></li>
                    <li><strong>Pagination:</strong> <?php esc_html_e('Enable/disable pagination (yes/no, default: no)', 'mage-eventpress'); ?></li>
                    <li><strong>Timeline Mode:</strong> <?php esc_html_e('Vertical or horizontal timeline (default: vertical)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Block Attributes - Filter Controls:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Category Filter:</strong> <?php esc_html_e('Show category filter dropdown (yes/no, default: no)', 'mage-eventpress'); ?></li>
                    <li><strong>Organizer Filter:</strong> <?php esc_html_e('Show organizer filter dropdown (yes/no, default: no)', 'mage-eventpress'); ?></li>
                    <li><strong>Search Filter:</strong> <?php esc_html_e('Show search box (yes/no, default: no)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Block Attributes - Carousel Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Carousel Navigation:</strong> <?php esc_html_e('Show prev/next arrows (yes/no, default: yes)', 'mage-eventpress'); ?></li>
                    <li><strong>Carousel Dots:</strong> <?php esc_html_e('Show pagination dots (yes/no, default: yes)', 'mage-eventpress'); ?></li>
                    <li><strong>Carousel ID:</strong> <?php esc_html_e('Unique identifier for multiple carousels (default: 102448)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Block is registered under "WpEvently - By Magepeople" category in Gutenberg editor.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/gutenberg-block-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/gutenberg-block-admin.png" alt="Gutenberg Block - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/gutenberg-block-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/gutenberg-block-frontend.png" alt="Gutenberg Block - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Analytics Dashboard', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Analytics & Reporting', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Comprehensive analytics dashboard for tracking event performance, sales data, and attendee statistics.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Analytics Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Summary Cards:</strong> <?php esc_html_e('Total sales, tickets sold, event count, average ticket price', 'mage-eventpress'); ?></li>
                    <li><strong>Date Range Filters:</strong> <?php esc_html_e('Last 7/30/90/365 days or custom date range', 'mage-eventpress'); ?></li>
                    <li><strong>Event-Specific Analytics:</strong> <?php esc_html_e('Filter data by individual events', 'mage-eventpress'); ?></li>
                    <li><strong>Export Functionality:</strong> <?php esc_html_e('Export analytics data to CSV format', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Visual Charts & Reports:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Sales Over Time:</strong> <?php esc_html_e('Line chart showing sales trends by date', 'mage-eventpress'); ?></li>
                    <li><strong>Tickets by Event:</strong> <?php esc_html_e('Bar chart comparing ticket sales across events', 'mage-eventpress'); ?></li>
                    <li><strong>Ticket Types Distribution:</strong> <?php esc_html_e('Pie chart showing ticket type popularity', 'mage-eventpress'); ?></li>
                    <li><strong>Sales by Day of Week:</strong> <?php esc_html_e('Pattern analysis for optimal event scheduling', 'mage-eventpress'); ?></li>
                    <li><strong>Detailed Data Table:</strong> <?php esc_html_e('Event-by-event breakdown with occupancy rates', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Data Points Tracked:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Total revenue and sales volumes', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Ticket quantities sold per event', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Available seats and occupancy rates', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Average ticket pricing trends', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Time-based sales patterns', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Analytics dashboard uses Chart.js for interactive data visualization and includes AJAX-powered real-time filtering.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/analytics-dashboard.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/analytics-dashboard.png" alt="Analytics Dashboard - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/analytics-charts.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/analytics-charts.png" alt="Analytics Charts - Admin">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Quick Setup Wizard', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Initial Plugin Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Step-by-step setup wizard for new installations to configure essential settings quickly.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Setup Wizard Steps:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Step 1 - Welcome:</strong> <?php esc_html_e('WooCommerce dependency check and installation', 'mage-eventpress'); ?></li>
                    <li><strong>Step 2 - General Settings:</strong> <?php esc_html_e('Configure essential plugin options', 'mage-eventpress'); ?></li>
                    <li><strong>Step 3 - Completion:</strong> <?php esc_html_e('Finalize setup and redirect to main admin', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Configurable Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event Label:</strong> <?php esc_html_e('Customize post type name (default: "Events")', 'mage-eventpress'); ?></li>
                    <li><strong>Event Slug:</strong> <?php esc_html_e('Set URL structure (default: "event")', 'mage-eventpress'); ?></li>
                    <li><strong>Event Expiry:</strong> <?php esc_html_e('Choose when events expire (start time vs end time)', 'mage-eventpress'); ?></li>
                    <li><strong>Email From Name:</strong> <?php esc_html_e('Default sender name for event emails', 'mage-eventpress'); ?></li>
                    <li><strong>Email From Address:</strong> <?php esc_html_e('Default sender email address', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('WooCommerce Integration:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Automatic WooCommerce plugin installation if not present', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Plugin activation and configuration', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Compatibility verification and setup', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Quick setup runs once during initial installation. Settings can be modified later in the main settings panel.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/quick-setup-welcome.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/quick-setup-welcome.png" alt="Quick Setup Wizard - Welcome">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/quick-setup-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/quick-setup-settings.png" alt="Quick Setup Wizard - Settings">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Individual Event Meta Box Settings', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Settings Tab - Additional Options', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Advanced individual event settings available in the Settings tab of each event.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Event Behavior Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>End Date/Time Status:</strong> <?php esc_html_e('Show or hide event end date/time on frontend', 'mage-eventpress'); ?></li>
                    <li><strong>Available Seat Status:</strong> <?php esc_html_e('Display remaining seat count to users', 'mage-eventpress'); ?></li>
                    <li><strong>Booking Reset:</strong> <?php esc_html_e('Reset all bookings for the event (admin only)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Display Preferences:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Event shortcode display with copy functionality', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Registration toggle for individual events', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Visual feedback for setting changes', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('These settings override global preferences for individual events, allowing fine-grained control.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-meta-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-meta-settings.png" alt="Event Meta Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-meta-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-meta-frontend.png" alt="Event Meta Settings - Frontend Effect">
                    </a>
                </div>
            </div>
        </div>
        
        <h3><?php esc_html_e('SEO Content Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Rich Text Content for SEO & Schema', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Additional content fields for improved search engine optimization and Google Schema markup.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('SEO Content Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Rich Text Editor:</strong> <?php esc_html_e('Full WordPress editor for detailed event descriptions', 'mage-eventpress'); ?></li>
                    <li><strong>Schema Markup:</strong> <?php esc_html_e('Content used for Google structured data', 'mage-eventpress'); ?></li>
                    <li><strong>SEO Optimization:</strong> <?php esc_html_e('Additional content for search engine indexing', 'mage-eventpress'); ?></li>
                    <li><strong>Media Support:</strong> <?php esc_html_e('Images, videos, and rich media for enhanced content', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('SEO content appears in search results and helps Google understand your event structure for better visibility.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/seo-content-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/seo-content-admin.png" alt="SEO Content - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/seo-content-schema.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/seo-content-schema.png" alt="SEO Content - Schema Example">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Email Notification System - Complete Configuration', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Low Stock Email Notifications', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Automated email notification system for administrators when event ticket stock runs low.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Low Stock Notification Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable/Disable Notifications:</strong> <?php esc_html_e('Global toggle for low stock email alerts', 'mage-eventpress'); ?></li>
                    <li><strong>Stock Threshold Setting:</strong> <?php esc_html_e('Configure when to trigger low stock warnings', 'mage-eventpress'); ?></li>
                    <li><strong>Email Recipients:</strong> <?php esc_html_e('Send notifications to admin email or custom addresses', 'mage-eventpress'); ?></li>
                    <li><strong>Frequency Control:</strong> <?php esc_html_e('Prevent spam with 24-hour transient locks per event/date', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Email Content & Customization:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>HTML Email Templates:</strong> <?php esc_html_e('Professional formatted emails with styling', 'mage-eventpress'); ?></li>
                    <li><strong>Dynamic Content:</strong> <?php esc_html_e('Event name, ticket type, available seats, edit links', 'mage-eventpress'); ?></li>
                    <li><strong>Date-Specific Alerts:</strong> <?php esc_html_e('Separate notifications for different event dates', 'mage-eventpress'); ?></li>
                    <li><strong>Customizable Subject Lines:</strong> <?php esc_html_e('Site name and event details in email subjects', 'mage-eventpress'); ?></li>
                    <li><strong>Fallback Email System:</strong> <?php esc_html_e('Alternative PHP mail if WordPress mail fails', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Integration Points:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Hooks into ticket quantity checking system', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Works with recurring events and date-specific bookings', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Respects global email sender settings', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Includes WordPress action hooks for customization', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Email notifications require WordPress mail functionality to be working correctly. Test with a simple contact form first.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-notification-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-notification-settings.png" alt="Email Notification Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-notification-example.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/email-notification-example.png" alt="Email Notification - Example">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Taxonomy & Category Management', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Categories & Organizers', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Complete taxonomy system for organizing events with categories and detailed organizer profiles.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Category Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Hierarchical Categories:</strong> <?php esc_html_e('Parent and child categories for event organization', 'mage-eventpress'); ?></li>
                    <li><strong>Category Archives:</strong> <?php esc_html_e('Dedicated archive pages for each category', 'mage-eventpress'); ?></li>
                    <li><strong>Category Filtering:</strong> <?php esc_html_e('Frontend filters and search by category', 'mage-eventpress'); ?></li>
                    <li><strong>Custom Category Templates:</strong> <?php esc_html_e('Specific layouts for category archive pages', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Organizer Profile System:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Detailed Contact Information:</strong> <?php esc_html_e('Complete address, phone, email fields', 'mage-eventpress'); ?></li>
                    <li><strong>Location Integration:</strong> <?php esc_html_e('Google Maps integration with coordinates', 'mage-eventpress'); ?></li>
                    <li><strong>Organizer Archives:</strong> <?php esc_html_e('Dedicated pages showing all events by organizer', 'mage-eventpress'); ?></li>
                    <li><strong>Profile Display:</strong> <?php esc_html_e('Organizer information on event detail pages', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Advanced Taxonomy Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Custom taxonomy meta fields and data', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Search and filtering by taxonomy terms', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Widget support for category and organizer lists', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('REST API endpoints for taxonomy data', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/taxonomy-categories-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/taxonomy-categories-admin.png" alt="Taxonomy Categories - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/taxonomy-frontend-filtering.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/taxonomy-frontend-filtering.png" alt="Taxonomy - Frontend Filtering">
                    </a>
                </div>
            </div>
        </div>

        <h2><?php esc_html_e('Individual Event Meta Box Tabs - Complete Guide', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Comprehensive documentation for all tabs that appear when creating or editing individual events.', 'mage-eventpress'); ?></p>

        <h3><?php esc_html_e('Venue/Location Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Location Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure where your event takes place - physical location, virtual venue, or hybrid events.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Event Type Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Online/Virtual Event:</strong> <?php esc_html_e('Enable for virtual events with online joining details', 'mage-eventpress'); ?></li>
                    <li><strong>Physical Event:</strong> <?php esc_html_e('Traditional in-person events with venue address', 'mage-eventpress'); ?></li>
                    <li><strong>Hybrid Event:</strong> <?php esc_html_e('Both online and physical attendance options', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Virtual Event Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Virtual Event Description:</strong> <?php esc_html_e('Rich text editor for joining instructions', 'mage-eventpress'); ?></li>
                    <li><strong>Meeting Links:</strong> <?php esc_html_e('Zoom, Google Meet, or other platform links', 'mage-eventpress'); ?></li>
                    <li><strong>Access Codes:</strong> <?php esc_html_e('Meeting IDs, passwords, and access information', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Physical Location Fields:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Venue Name:</strong> <?php esc_html_e('Name of the event location', 'mage-eventpress'); ?></li>
                    <li><strong>Street Address:</strong> <?php esc_html_e('Complete street address', 'mage-eventpress'); ?></li>
                    <li><strong>City:</strong> <?php esc_html_e('City name', 'mage-eventpress'); ?></li>
                    <li><strong>State/Province:</strong> <?php esc_html_e('State or province', 'mage-eventpress'); ?></li>
                    <li><strong>Postal Code:</strong> <?php esc_html_e('ZIP or postal code', 'mage-eventpress'); ?></li>
                    <li><strong>Country:</strong> <?php esc_html_e('Country name', 'mage-eventpress'); ?></li>
                    <li><strong>Google Maps Integration:</strong> <?php esc_html_e('Interactive map display with location picker', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Virtual event details are sent to attendees via confirmation emails. Ensure all joining information is accurate.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/venue-location-tab-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/venue-location-tab-admin.png" alt="Venue/Location Tab - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/venue-location-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/venue-location-frontend-display.png" alt="Venue/Location - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Ticket & Pricing Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Ticket Types & Pricing Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Create and manage ticket types with different pricing, availability, and restrictions.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Registration Controls:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Registration On/Off:</strong> <?php esc_html_e('Enable or disable ticket sales for the event', 'mage-eventpress'); ?></li>
                    <li><strong>Event Shortcode:</strong> <?php esc_html_e('Copy shortcode to display ticket form anywhere', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Ticket Type Configuration:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Ticket Name:</strong> <?php esc_html_e('Name for the ticket type (General, VIP, Student, etc.)', 'mage-eventpress'); ?></li>
                    <li><strong>Price:</strong> <?php esc_html_e('Ticket price with currency formatting', 'mage-eventpress'); ?></li>
                    <li><strong>Available Quantity:</strong> <?php esc_html_e('Number of tickets available for sale', 'mage-eventpress'); ?></li>
                    <li><strong>Description:</strong> <?php esc_html_e('Detailed description of what\'s included', 'mage-eventpress'); ?></li>
                    <li><strong>Ticket Icon:</strong> <?php esc_html_e('Custom icon for visual identification', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Extra Services:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Additional Services:</strong> <?php esc_html_e('Optional add-ons like parking, meals, merchandise', 'mage-eventpress'); ?></li>
                    <li><strong>Service Pricing:</strong> <?php esc_html_e('Individual pricing for each extra service', 'mage-eventpress'); ?></li>
                    <li><strong>Quantity Limits:</strong> <?php esc_html_e('Maximum quantity per customer for services', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Multiple ticket types allow for tiered pricing and different access levels to your event.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/ticket-pricing-tab-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/ticket-pricing-tab-admin.png" alt="Ticket & Pricing Tab - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/ticket-pricing-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/ticket-pricing-frontend-display.png" alt="Ticket & Pricing - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Date & Time Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Scheduling & Timing', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure event dates, times, and recurring patterns for complex scheduling needs.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Event Type Selection:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Single Event:</strong> <?php esc_html_e('One-time event with single date and time', 'mage-eventpress'); ?></li>
                    <li><strong>Particular Event:</strong> <?php esc_html_e('Multiple specific dates with individual scheduling', 'mage-eventpress'); ?></li>
                    <li><strong>Repeated Event:</strong> <?php esc_html_e('Daily recurring events with off-days configuration', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Date & Time Configuration:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Start Date & Time:</strong> <?php esc_html_e('When the event begins', 'mage-eventpress'); ?></li>
                    <li><strong>End Date & Time:</strong> <?php esc_html_e('When the event concludes', 'mage-eventpress'); ?></li>
                    <li><strong>Multiple Dates:</strong> <?php esc_html_e('Add unlimited additional event dates', 'mage-eventpress'); ?></li>
                    <li><strong>Time Slots:</strong> <?php esc_html_e('Different time slots for the same day', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Recurring Event Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Repeat Duration:</strong> <?php esc_html_e('Number of days to repeat the event', 'mage-eventpress'); ?></li>
                    <li><strong>Off Days:</strong> <?php esc_html_e('Select weekdays when event doesn\'t occur', 'mage-eventpress'); ?></li>
                    <li><strong>Special Dates:</strong> <?php esc_html_e('Override pricing or availability for specific dates', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Date and time settings affect ticket availability and pricing calculations. Verify all times are in the correct timezone.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/date-time-tab-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/date-time-tab-admin.png" alt="Date & Time Tab - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/date-time-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/date-time-frontend-display.png" alt="Date & Time - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Event Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Individual Event Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure specific settings for individual events that override global preferences.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Display Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>End Date/Time Status:</strong> <?php esc_html_e('Show or hide event end date/time on frontend', 'mage-eventpress'); ?></li>
                    <li><strong>Available Seat Status:</strong> <?php esc_html_e('Display remaining seat count to users', 'mage-eventpress'); ?></li>
                    <li><strong>Registration Status:</strong> <?php esc_html_e('Enable/disable registration for this specific event', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Administrative Tools:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Booking Reset:</strong> <?php esc_html_e('Reset all bookings for the event (admin only)', 'mage-eventpress'); ?></li>
                    <li><strong>Event Shortcode:</strong> <?php esc_html_e('Display shortcode with copy functionality', 'mage-eventpress'); ?></li>
                    <li><strong>WooCommerce Product ID:</strong> <?php esc_html_e('View linked WooCommerce product information', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('These settings override global preferences for individual events, allowing fine-grained control over event behavior.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-settings-tab-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-settings-tab-admin.png" alt="Event Settings Tab - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-settings-frontend-effect.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-settings-frontend-effect.png" alt="Event Settings - Frontend Effect">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Tax Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Tax Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure tax settings for individual events when WooCommerce tax calculation is enabled.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Tax Configuration Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Tax Status:</strong> <?php esc_html_e('Taxable, Non-taxable, or Shipping only', 'mage-eventpress'); ?></li>
                    <li><strong>Tax Class:</strong> <?php esc_html_e('Standard rate, Reduced rate, or Zero rate', 'mage-eventpress'); ?></li>
                    <li><strong>Override Global Tax:</strong> <?php esc_html_e('Use event-specific tax settings instead of global', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Tax Display Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Price Display:</strong> <?php esc_html_e('Show prices including or excluding tax', 'mage-eventpress'); ?></li>
                    <li><strong>Tax Label:</strong> <?php esc_html_e('Custom tax label for this event', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Tax settings only appear when WooCommerce tax calculation is enabled in WooCommerce settings.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/tax-settings-tab-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/tax-settings-tab-admin.png" alt="Tax Settings Tab - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/tax-settings-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/tax-settings-frontend-display.png" alt="Tax Settings - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('SEO Content Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Rich Text Content for SEO & Schema', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Additional content fields for improved search engine optimization and Google Schema markup.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('SEO Content Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Rich Text Editor:</strong> <?php esc_html_e('Full WordPress editor for detailed event descriptions', 'mage-eventpress'); ?></li>
                    <li><strong>Schema Markup:</strong> <?php esc_html_e('Content used for Google structured data', 'mage-eventpress'); ?></li>
                    <li><strong>SEO Optimization:</strong> <?php esc_html_e('Additional content for search engine indexing', 'mage-eventpress'); ?></li>
                    <li><strong>Media Support:</strong> <?php esc_html_e('Images, videos, and rich media for enhanced content', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Content Benefits:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Improved search engine visibility', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Enhanced Google rich snippets display', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Better social media sharing previews', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Additional content for event detail pages', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('SEO content appears in search results and helps Google understand your event structure for better visibility.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/seo-content-tab-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/seo-content-tab-admin.png" alt="SEO Content Tab - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/seo-content-schema-result.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/seo-content-schema-result.png" alt="SEO Content - Schema Result">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Event Shortcodes', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Display Shortcodes', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Complete list of shortcodes available for displaying events and event components anywhere on your site.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Individual Event Shortcodes:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><code>[event-add-cart-section event="ID"]</code> - <?php esc_html_e('Display ticket booking form for specific event', 'mage-eventpress'); ?></li>
                    <li><code>[event-single event="ID"]</code> - <?php esc_html_e('Show complete event details page', 'mage-eventpress'); ?></li>
                    <li><code>[event-speaker event="ID"]</code> - <?php esc_html_e('Display event speakers list', 'mage-eventpress'); ?></li>
                    <li><code>[event-timeline event="ID"]</code> - <?php esc_html_e('Show event schedule/timeline', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event List Shortcodes:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><code>[event-list]</code> - <?php esc_html_e('Display all upcoming events', 'mage-eventpress'); ?></li>
                    <li><code>[event-list cat="category-slug"]</code> - <?php esc_html_e('Show events from specific category', 'mage-eventpress'); ?></li>
                    <li><code>[event-list org="organizer-slug"]</code> - <?php esc_html_e('Display events by specific organizer', 'mage-eventpress'); ?></li>
                    <li><code>[event-list style="grid" columns="3"]</code> - <?php esc_html_e('Grid layout with custom columns', 'mage-eventpress'); ?></li>
                    <li><code>[event-list style="carousel"]</code> - <?php esc_html_e('Carousel/slider display', 'mage-eventpress'); ?></li>
                    <li><code>[event-list style="timeline"]</code> - <?php esc_html_e('Timeline layout display', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Advanced Shortcode Parameters:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>show:</strong> <?php esc_html_e('Number of events to display (-1 for all)', 'mage-eventpress'); ?></li>
                    <li><strong>status:</strong> <?php esc_html_e('upcoming, past, or all events', 'mage-eventpress'); ?></li>
                    <li><strong>city:</strong> <?php esc_html_e('Filter by city name', 'mage-eventpress'); ?></li>
                    <li><strong>country:</strong> <?php esc_html_e('Filter by country', 'mage-eventpress'); ?></li>
                    <li><strong>pagination:</strong> <?php esc_html_e('Enable pagination (yes/no)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Shortcodes can be used in posts, pages, widgets, and theme templates to display events anywhere on your site.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/shortcodes-usage-admin.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/shortcodes-usage-admin.png" alt="Shortcodes Usage - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/shortcodes-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/shortcodes-frontend-display.png" alt="Shortcodes - Frontend Display">
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}
?> 