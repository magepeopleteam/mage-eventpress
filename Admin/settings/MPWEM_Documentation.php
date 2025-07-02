<?php
/*
* Documentation Page for Event Manager Settings
* @Author 		MagePeople Team
* Copyright: 	mage-people.com
*/

if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.



class MPWEM_Documentation {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_documentation_submenu'));
        add_action('wp_ajax_mep_load_documentation_tab', array($this, 'load_documentation_tab_ajax'));
    }

    public function add_documentation_submenu() {
        add_submenu_page(
            'edit.php?post_type=mep_events',
            esc_html__('Documentation', 'mage-eventpress'),
            esc_html__('Documentation', 'mage-eventpress'),
            'manage_options',
            'mep_documentation',
            array($this, 'documentation_page')
        );
    }

    public function documentation_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'global-settings';
        ?>
        <div class="wrap mep-documentation-page">
            <h1><?php esc_html_e('Event Manager Documentation', 'mage-eventpress'); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="#" data-tab="global-settings" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'global-settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Global Settings', 'mage-eventpress'); ?>
                </a>
                <a href="#" data-tab="event-creation" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'event-creation' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Event Creation', 'mage-eventpress'); ?>
                </a>
                <a href="#" data-tab="venue-location" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'venue-location' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Venue/Location', 'mage-eventpress'); ?>
                </a>
                <a href="#" data-tab="ticket-pricing" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'ticket-pricing' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Ticket & Pricing', 'mage-eventpress'); ?>
                </a>
                <a href="#" data-tab="date-time" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'date-time' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Date & Time', 'mage-eventpress'); ?>
                </a>
                <a href="#" data-tab="event-settings" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'event-settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Event Settings', 'mage-eventpress'); ?>
                </a>
                <a href="#" data-tab="tax-settings" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'tax-settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Tax Settings', 'mage-eventpress'); ?>
                </a>
                <a href="#" data-tab="seo-content" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'seo-content' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('SEO Content', 'mage-eventpress'); ?>
                </a>
                <a href="#" data-tab="shortcodes" 
                   class="nav-tab mep-doc-tab <?php echo $active_tab === 'shortcodes' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Shortcodes', 'mage-eventpress'); ?>
                </a>
            </nav>

            <div class="tab-content">
                <div class="mep-loading" style="display: none;">
                    <p><?php esc_html_e('Loading...', 'mage-eventpress'); ?></p>
                </div>
                
                <!-- Global Settings Tab Content -->
                <div id="tab-global-settings" class="tab-panel <?php echo $active_tab === 'global-settings' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'global-settings') $this->render_global_settings_docs(); ?>
                </div>
                
                <!-- Event Creation Tab Content -->
                <div id="tab-event-creation" class="tab-panel <?php echo $active_tab === 'event-creation' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'event-creation') $this->render_event_creation_docs(); ?>
                </div>
                
                <!-- Venue/Location Tab Content -->
                <div id="tab-venue-location" class="tab-panel <?php echo $active_tab === 'venue-location' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'venue-location') $this->render_venue_location_docs(); ?>
                </div>
                
                <!-- Ticket & Pricing Tab Content -->
                <div id="tab-ticket-pricing" class="tab-panel <?php echo $active_tab === 'ticket-pricing' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'ticket-pricing') $this->render_ticket_pricing_docs(); ?>
                </div>
                
                <!-- Date & Time Tab Content -->
                <div id="tab-date-time" class="tab-panel <?php echo $active_tab === 'date-time' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'date-time') $this->render_date_time_docs(); ?>
                </div>
                
                <!-- Event Settings Tab Content -->
                <div id="tab-event-settings" class="tab-panel <?php echo $active_tab === 'event-settings' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'event-settings') $this->render_event_settings_docs(); ?>
                </div>
                
                <!-- Tax Settings Tab Content -->
                <div id="tab-tax-settings" class="tab-panel <?php echo $active_tab === 'tax-settings' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'tax-settings') $this->render_tax_settings_docs(); ?>
                </div>
                
                <!-- SEO Content Tab Content -->
                <div id="tab-seo-content" class="tab-panel <?php echo $active_tab === 'seo-content' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'seo-content') $this->render_seo_content_docs(); ?>
                </div>
                
                <!-- Shortcodes Tab Content -->
                <div id="tab-shortcodes" class="tab-panel <?php echo $active_tab === 'shortcodes' ? 'active' : ''; ?>">
                    <?php if ($active_tab === 'shortcodes') $this->render_shortcodes_docs(); ?>
                </div>
            </div>
        </div>

        <style>
            .mep-documentation-page {
                max-width: 1200px;
            }
            .nav-tab-wrapper {
                margin-bottom: 20px;
            }
            .tab-content {
                background: #fff;
                padding: 20px;
                border: 1px solid #ccd0d4;
                margin-top: -1px;
                position: relative;
            }
            .tab-panel {
                display: none;
            }
            .tab-panel.active {
                display: block;
            }
            .mep-loading {
                text-align: center;
                padding: 40px 20px;
                color: #666;
                font-style: italic;
            }
            .mep-loading::before {
                content: "";
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #0073aa;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-right: 10px;
                vertical-align: middle;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .setting-item {
                margin-bottom: 30px;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
            }
            .setting-title {
                background: #f8f9fa;
                padding: 15px 20px;
                font-size: 16px;
                font-weight: 600;
                border-bottom: 1px solid #e1e1e1;
            }
            .setting-description {
                padding: 15px 20px;
                color: #666;
                border-bottom: 1px solid #e1e1e1;
            }
            .setting-usage {
                padding: 15px 20px;
            }
            .setting-usage ul {
                margin: 10px 0;
                padding-left: 20px;
            }
            .setting-usage li {
                margin-bottom: 8px;
            }
            .info {
                background: #e7f3ff;
                border: 1px solid #b3d9ff;
                border-radius: 4px;
                padding: 12px 15px;
                margin: 15px 0;
                color: #0073aa;
            }
            .warning {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 4px;
                padding: 12px 15px;
                margin: 15px 0;
                color: #856404;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Handle tab switching
            $('.mep-doc-tab').on('click', function(e) {
                e.preventDefault();
                
                var targetTab = $(this).data('tab');
                var $targetPanel = $('#tab-' + targetTab);
                
                // Don't do anything if already active
                if ($(this).hasClass('nav-tab-active')) {
                    return false;
                }
                
                // Update active tab
                $('.mep-doc-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Update URL without reload
                if (history.pushState) {
                    var newUrl = window.location.href.split('&tab=')[0] + '&tab=' + targetTab;
                    if (window.location.href.indexOf('&tab=') === -1) {
                        newUrl = window.location.href + '&tab=' + targetTab;
                    }
                    history.pushState({ tab: targetTab }, '', newUrl);
                }
                
                // Hide all panels
                $('.tab-panel').removeClass('active');
                
                // Check if content is already loaded
                if ($targetPanel.html().trim() === '') {
                    // Show loading
                    $('.mep-loading').show();
                    
                    // Load content via AJAX
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'mep_load_documentation_tab',
                            tab: targetTab,
                            nonce: '<?php echo wp_create_nonce('mep_doc_nonce'); ?>'
                        },
                        success: function(response) {
                            $('.mep-loading').hide();
                            if (response.success) {
                                $targetPanel.html(response.data).addClass('active');
                            } else {
                                $targetPanel.html('<p>Error loading content. Please refresh the page.</p>').addClass('active');
                            }
                        },
                        error: function() {
                            $('.mep-loading').hide();
                            $targetPanel.html('<p>Error loading content. Please refresh the page.</p>').addClass('active');
                        }
                    });
                } else {
                    // Content already loaded, just show it
                    $targetPanel.addClass('active');
                }
            });
            
            // Handle browser back/forward
            $(window).on('popstate', function(e) {
                if (e.originalEvent.state && e.originalEvent.state.tab) {
                    var tab = e.originalEvent.state.tab;
                    $('.mep-doc-tab[data-tab="' + tab + '"]').trigger('click');
                }
            });
            
            // Set initial state for browser history
            if (history.pushState) {
                var currentTab = $('.nav-tab-active').data('tab');
                history.replaceState({ tab: currentTab }, '', window.location.href);
            }
        });
        </script>
        <?php
    }

    public function load_documentation_tab_ajax() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mep_doc_nonce')) {
            wp_die('Security check failed');
        }
        
        $tab = sanitize_text_field($_POST['tab']);
        
        // Start output buffering
        ob_start();
        
        // Render the appropriate content
        switch ($tab) {
            case 'global-settings':
                $this->render_global_settings_docs();
                break;
            case 'event-creation':
                $this->render_event_creation_docs();
                break;
            case 'venue-location':
                $this->render_venue_location_docs();
                break;
            case 'ticket-pricing':
                $this->render_ticket_pricing_docs();
                break;
            case 'date-time':
                $this->render_date_time_docs();
                break;
            case 'event-settings':
                $this->render_event_settings_docs();
                break;
            case 'tax-settings':
                $this->render_tax_settings_docs();
                break;
            case 'seo-content':
                $this->render_seo_content_docs();
                break;
            case 'shortcodes':
                $this->render_shortcodes_docs();
                break;
            default:
                echo '<p>Invalid tab requested.</p>';
                break;
        }
        
        // Get the content
        $content = ob_get_clean();
        
        // Return success response
        wp_send_json_success($content);
    }

    private function render_global_settings_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Global Settings Documentation', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Configure global plugin settings that apply to all events. This comprehensive guide covers every setting available in the Global Settings panel.', 'mage-eventpress'); ?></p>

        <h3><?php esc_html_e('General Settings Tab', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Basic Configuration Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Fundamental settings that control core plugin behavior and display options.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Seat Reserved Order Status:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>On Hold:</strong> <?php esc_html_e('Reserve seats for orders with on-hold status', 'mage-eventpress'); ?></li>
                    <li><strong>Pending:</strong> <?php esc_html_e('Reserve seats for pending payment orders', 'mage-eventpress'); ?></li>
                    <li><strong>Processing:</strong> <?php esc_html_e('Reserve seats for processing orders', 'mage-eventpress'); ?></li>
                    <li><strong>Completed:</strong> <?php esc_html_e('Reserve seats only for completed orders', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Default is Processing & Completed. This controls when seats are actually reserved during the checkout process.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('seat-reserved-order-status', 'Seat Reserved Order Status', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Type & Content Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure how events are structured and labeled throughout your website.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Block/Gutenberg Editor in Event:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Use Gutenberg block editor for event creation', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Use classic editor for event creation', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Enable Rest API:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Make event data available via WordPress REST API', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Disable REST API access for events', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Choose Multilingual Plugin:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>None:</strong> <?php esc_html_e('No multilingual plugin integration', 'mage-eventpress'); ?></li>
                    <li><strong>Polylang:</strong> <?php esc_html_e('Integrate with Polylang plugin', 'mage-eventpress'); ?></li>
                    <li><strong>WPML:</strong> <?php esc_html_e('Integrate with WPML plugin', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event List Order By:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event Upcoming Date:</strong> <?php esc_html_e('Sort by next event date (default)', 'mage-eventpress'); ?></li>
                    <li><strong>Event Title:</strong> <?php esc_html_e('Sort alphabetically by event title', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('event-configuration', 'Event Configuration', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Post Type & URL Customization', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Customize how events appear in WordPress admin and frontend URLs.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Event Label:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Changes the event post type label throughout the entire plugin', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: "Events" - can be changed to "Concerts", "Workshops", etc.', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Slug:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Changes the URL structure for event pages', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: "events" - example: yoursite.com/events/event-name', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('After changing, go to Settings → Permalinks and save to flush permalinks', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Icon:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Icon displayed in WordPress admin menu', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: "dashicons-calendar-alt"', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Find more icons at:', 'mage-eventpress'); ?> <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a></li>
                </ul>
                <strong><?php esc_html_e('Event Category Label & Slug:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Customize category taxonomy name and URL structure', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default Label: "Category", Default Slug: "mep_cat"', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Organizer Label & Slug:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Customize organizer taxonomy name and URL structure', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default Label: "Organizer", Default Slug: "mep_org"', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Remember to flush permalinks after changing any slug settings by going to Settings → Permalinks and clicking Save Settings.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('post-type-customization', 'Post Type Customization', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Google Maps Integration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure Google Maps for event location display and functionality.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Google Map Type:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>API:</strong> <?php esc_html_e('Interactive maps with drag-and-drop positioning (recommended)', 'mage-eventpress'); ?></li>
                    <li><strong>Iframe:</strong> <?php esc_html_e('Simple embedded maps (less accurate positioning)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Google Map API Key:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Required for interactive maps and address validation', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Get your API key from:', 'mage-eventpress'); ?> <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google Maps Platform</a></li>
                    <li><?php esc_html_e('Note: Billing information must be entered in Google Maps API account', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Google Map Zoom Level:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Control how close the map zooms in (5-25)', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: 17 (good balance of detail and context)', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Higher numbers = closer zoom, Lower numbers = wider view', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('google-maps-configuration', 'Google Maps Configuration', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Expiration & Booking Control', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control when events expire and how booking deadlines work.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('When will the event expire:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event Start Time:</strong> <?php esc_html_e('Event becomes "expired" when it begins', 'mage-eventpress'); ?></li>
                    <li><strong>Event End Time:</strong> <?php esc_html_e('Event remains "active" until it ends', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Ticket Expire before minutes:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Stop ticket sales X minutes before event starts', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: 0 (allow booking until event starts)', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Example: 15 = stop booking 15 minutes before event', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Redirect Checkout after Booking:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Automatically redirect to checkout after adding event to cart', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Stay on event page after adding to cart', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('event-expiration', 'Event Expiration Settings', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Order Details & Email Control', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control what information appears in order confirmations and emails.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Hide Location From Order Details & Email:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Remove venue information from order confirmations', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show venue information in order confirmations (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Hide Date From Order Details & Email:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Remove event date from order confirmations', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show event date in order confirmations (default)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('These settings affect both the thank you page and email confirmations sent to customers.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('order-details-control', 'Order Details Control', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Calendar & Display Options', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure calendar display and general event visibility options.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Hide Expired Event from Calendar:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Remove past events from calendar display', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show all events in calendar (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show 0 Price as Free:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display "Free" instead of "$0" (default)', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show "$0" for zero-cost events', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show Event Sidebar Widgets:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Register widget area for event pages', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('No sidebar widgets on event pages (default)', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('calendar-display-options', 'Calendar Display Options', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Asset Loading & Performance', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control how CSS/JS assets are loaded and optimize performance.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Load Font Awesome From Theme:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Use theme\'s Font Awesome instead of plugin\'s version', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Load Font Awesome from plugin (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Load Flat Icon From Theme:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Use theme\'s Flat Icons instead of plugin\'s version', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Load Flat Icons from plugin (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Speed up the Event List Page Loading:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Optimize loading but disable Waitlist and Seat count features', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Normal loading with all features enabled (default)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Only enable speed optimization if your event list pages are loading slowly and you don\'t need waitlist functionality.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('asset-loading-performance', 'Asset Loading Performance', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Availability & Stock Management', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure how sold-out events and low stock warnings are handled.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Disappear Event from list when fully booked:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Remove sold-out events from event listings', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show sold-out events in listings (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show Sold out Ribbon:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display "Sold Out" badge on fully booked events', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('No special marking for sold-out events (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show Limited Availability Ribbon:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display "Limited Availability" badge when seats are low', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('No limited availability warnings (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Limited Availability Threshold:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Show "Limited Availability" when seats ≤ this number', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: 5 seats remaining', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show Low Stock Warning:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display warning message when seats are running low (default)', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('No low stock warnings', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Low Stock Threshold:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Show warning when seats ≤ this number', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: 3 seats remaining', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Low Stock Warning Text:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Custom message for low stock alerts', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: "Hurry! Only %s seats left" (%s = seat count)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Send Low Stock Email Notifications:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Email admin when seats are running low (default)', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('No email notifications for low stock', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('availability-stock-management', 'Availability Stock Management', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('WooCommerce Integration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control how events integrate with WooCommerce functionality.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Show Hidden WooCommerce Products:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display auto-created event products in WooCommerce product list', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Keep event products hidden from product list (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Clear Cart after Checkout Order Placed:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Clear cart after order is placed (default)', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Keep cart data (needed for some payment gateways)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Only disable cart clearing if you experience issues with payment gateways after checkout.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('woocommerce-integration', 'WooCommerce Integration', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Troubleshooting & Bug Fixes', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Special settings to resolve specific technical issues and compatibility problems.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Manual Seat Left Fixing:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Fix "Sorry, There Are No Seats Available" issue after v4.3.0+ update', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Normal seat calculation (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Details Page Fatal Error Fix:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Apply patch for fatal errors on event detail pages', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Normal operation (default)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Only enable these troubleshooting options if you\'re experiencing the specific issues they address. Keep disabled otherwise.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('troubleshooting-settings', 'Troubleshooting Settings', true); ?>
            </div>
        </div>

        <h3><?php esc_html_e('Other Settings Tabs', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Additional Configuration Sections', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('The Global Settings page contains multiple tabs with specialized configuration options.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Available Settings Sections:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event List Settings:</strong> <?php esc_html_e('Control how events display in list views', 'mage-eventpress'); ?></li>
                    <li><strong>Single Event Settings:</strong> <?php esc_html_e('Configure individual event page display options', 'mage-eventpress'); ?></li>
                    <li><strong>Email Settings:</strong> <?php esc_html_e('Set up email templates and sender information', 'mage-eventpress'); ?></li>
                    <li><strong>Date & Time Format Settings:</strong> <?php esc_html_e('Customize how dates and times appear', 'mage-eventpress'); ?></li>
                    <li><strong>Style Settings:</strong> <?php esc_html_e('Set colors and visual appearance', 'mage-eventpress'); ?></li>
                    <li><strong>Icon Settings:</strong> <?php esc_html_e('Choose icons for different event elements', 'mage-eventpress'); ?></li>
                    <li><strong>Translation Settings:</strong> <?php esc_html_e('Customize all text labels and messages', 'mage-eventpress'); ?></li>
                    <li><strong>Carousel Settings:</strong> <?php esc_html_e('Configure event carousel/slider options', 'mage-eventpress'); ?></li>
                    <li><strong>Slider Settings:</strong> <?php esc_html_e('Set up featured event sliders', 'mage-eventpress'); ?></li>
                    <li><strong>Custom CSS:</strong> <?php esc_html_e('Add custom styling to override plugin CSS', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Each tab contains detailed settings for specific aspects of the plugin. Visit each tab to configure options relevant to your needs.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('settings-tabs-overview', 'Settings Tabs Overview', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Type & Content Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure how events are structured and labeled throughout your website.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Block/Gutenberg Editor in Event:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Use Gutenberg block editor for event creation', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Use classic editor for event creation', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Enable Rest API:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Make event data available via WordPress REST API', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Disable REST API access for events', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Choose Multilingual Plugin:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>None:</strong> <?php esc_html_e('No multilingual plugin integration', 'mage-eventpress'); ?></li>
                    <li><strong>Polylang:</strong> <?php esc_html_e('Integrate with Polylang plugin', 'mage-eventpress'); ?></li>
                    <li><strong>WPML:</strong> <?php esc_html_e('Integrate with WPML plugin', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event List Order By:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event Upcoming Date:</strong> <?php esc_html_e('Sort by next event date (default)', 'mage-eventpress'); ?></li>
                    <li><strong>Event Title:</strong> <?php esc_html_e('Sort alphabetically by event title', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-configuration-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-configuration-settings.png" alt="Event Configuration - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-configuration-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-configuration-frontend.png" alt="Event Configuration - Frontend Result">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Post Type & URL Customization', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Customize how events appear in WordPress admin and frontend URLs.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Event Label:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Changes the event post type label throughout the entire plugin', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: "Events" - can be changed to "Concerts", "Workshops", etc.', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Slug:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Changes the URL structure for event pages', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: "events" - example: yoursite.com/events/event-name', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('After changing, go to Settings → Permalinks and save to flush permalinks', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Icon:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Icon displayed in WordPress admin menu', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: "dashicons-calendar-alt"', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Find more icons at:', 'mage-eventpress'); ?> <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a></li>
                </ul>
                <strong><?php esc_html_e('Event Category Label & Slug:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Customize category taxonomy name and URL structure', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default Label: "Category", Default Slug: "mep_cat"', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Organizer Label & Slug:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Customize organizer taxonomy name and URL structure', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default Label: "Organizer", Default Slug: "mep_org"', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Remember to flush permalinks after changing any slug settings by going to Settings → Permalinks and clicking Save Settings.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/post-type-customization.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/post-type-customization.png" alt="Post Type Customization - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/post-type-frontend-urls.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/post-type-frontend-urls.png" alt="Post Type Customization - Frontend URLs">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Google Maps Integration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure Google Maps for event location display and functionality.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Google Map Type:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>API:</strong> <?php esc_html_e('Interactive maps with drag-and-drop positioning (recommended)', 'mage-eventpress'); ?></li>
                    <li><strong>Iframe:</strong> <?php esc_html_e('Simple embedded maps (less accurate positioning)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Google Map API Key:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Required for interactive maps and address validation', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Get your API key from:', 'mage-eventpress'); ?> <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google Maps Platform</a></li>
                    <li><?php esc_html_e('Note: Billing information must be entered in Google Maps API account', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Google Map Zoom Level:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Control how close the map zooms in (5-25)', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: 17 (good balance of detail and context)', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Higher numbers = closer zoom, Lower numbers = wider view', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/google-maps-configuration.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/google-maps-configuration.png" alt="Google Maps Configuration - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/google-maps-frontend-display.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/google-maps-frontend-display.png" alt="Google Maps - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Expiration & Booking Control', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control when events expire and how booking deadlines work.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('When will the event expire:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event Start Time:</strong> <?php esc_html_e('Event becomes "expired" when it begins', 'mage-eventpress'); ?></li>
                    <li><strong>Event End Time:</strong> <?php esc_html_e('Event remains "active" until it ends', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Ticket Expire before minutes:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Stop ticket sales X minutes before event starts', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: 0 (allow booking until event starts)', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Example: 15 = stop booking 15 minutes before event', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Redirect Checkout after Booking:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Automatically redirect to checkout after adding event to cart', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Stay on event page after adding to cart', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-expiration-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-expiration-settings.png" alt="Event Expiration Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-expiration-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/event-expiration-frontend.png" alt="Event Expiration - Frontend Effect">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Order Details & Email Control', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control what information appears in order confirmations and emails.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Hide Location From Order Details & Email:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Remove venue information from order confirmations', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show venue information in order confirmations (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Hide Date From Order Details & Email:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Remove event date from order confirmations', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show event date in order confirmations (default)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('These settings affect both the thank you page and email confirmations sent to customers.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/order-details-control.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/order-details-control.png" alt="Order Details Control - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/order-details-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/order-details-frontend.png" alt="Order Details - Frontend Result">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Calendar & Display Options', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure calendar display and general event visibility options.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Hide Expired Event from Calendar:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Remove past events from calendar display', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show all events in calendar (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show 0 Price as Free:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display "Free" instead of "$0" (default)', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show "$0" for zero-cost events', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show Event Sidebar Widgets:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Register widget area for event pages', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('No sidebar widgets on event pages (default)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/calendar-display-options.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/calendar-display-options.png" alt="Calendar Display Options - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/calendar-display-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/calendar-display-frontend.png" alt="Calendar Display - Frontend Result">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Asset Loading & Performance', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control how CSS/JS assets are loaded and optimize performance.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Load Font Awesome From Theme:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Use theme\'s Font Awesome instead of plugin\'s version', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Load Font Awesome from plugin (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Load Flat Icon From Theme:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Use theme\'s Flat Icons instead of plugin\'s version', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Load Flat Icons from plugin (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Speed up the Event List Page Loading:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Optimize loading but disable Waitlist and Seat count features', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Normal loading with all features enabled (default)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Only enable speed optimization if your event list pages are loading slowly and you don\'t need waitlist functionality.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/asset-loading-performance.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/asset-loading-performance.png" alt="Asset Loading Performance - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/performance-optimization-result.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/performance-optimization-result.png" alt="Performance Optimization - Frontend Result">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Availability & Stock Management', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure how sold-out events and low stock warnings are handled.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Disappear Event from list when fully booked:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Remove sold-out events from event listings', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Show sold-out events in listings (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show Sold out Ribbon:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display "Sold Out" badge on fully booked events', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('No special marking for sold-out events (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show Limited Availability Ribbon:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display "Limited Availability" badge when seats are low', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('No limited availability warnings (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Limited Availability Threshold:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Show "Limited Availability" when seats ≤ this number', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: 5 seats remaining', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Show Low Stock Warning:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display warning message when seats are running low (default)', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('No low stock warnings', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Low Stock Threshold:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Show warning when seats ≤ this number', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: 3 seats remaining', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Low Stock Warning Text:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Custom message for low stock alerts', 'mage-eventpress'); ?></li>
                    <li><?php esc_html_e('Default: "Hurry! Only %s seats left" (%s = seat count)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Send Low Stock Email Notifications:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Email admin when seats are running low (default)', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('No email notifications for low stock', 'mage-eventpress'); ?></li>
                </ul>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/availability-stock-management.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/availability-stock-management.png" alt="Availability Stock Management - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/stock-warnings-frontend.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/stock-warnings-frontend.png" alt="Stock Warnings - Frontend Display">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('WooCommerce Integration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control how events integrate with WooCommerce functionality.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Show Hidden WooCommerce Products:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Yes:</strong> <?php esc_html_e('Display auto-created event products in WooCommerce product list', 'mage-eventpress'); ?></li>
                    <li><strong>No:</strong> <?php esc_html_e('Keep event products hidden from product list (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Clear Cart after Checkout Order Placed:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Clear cart after order is placed (default)', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Keep cart data (needed for some payment gateways)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Only disable cart clearing if you experience issues with payment gateways after checkout.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/woocommerce-integration.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/woocommerce-integration.png" alt="WooCommerce Integration - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/woocommerce-frontend-integration.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/woocommerce-frontend-integration.png" alt="WooCommerce Integration - Frontend Effect">
                    </a>
                </div>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Troubleshooting & Bug Fixes', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Special settings to resolve specific technical issues and compatibility problems.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Manual Seat Left Fixing:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Fix "Sorry, There Are No Seats Available" issue after v4.3.0+ update', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Normal seat calculation (default)', 'mage-eventpress'); ?></li>
                </ul>
                <strong><?php esc_html_e('Event Details Page Fatal Error Fix:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Enable:</strong> <?php esc_html_e('Apply patch for fatal errors on event detail pages', 'mage-eventpress'); ?></li>
                    <li><strong>Disable:</strong> <?php esc_html_e('Normal operation (default)', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Only enable these troubleshooting options if you\'re experiencing the specific issues they address. Keep disabled otherwise.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/troubleshooting-settings.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/troubleshooting-settings.png" alt="Troubleshooting Settings - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/troubleshooting-fixes-result.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/troubleshooting-fixes-result.png" alt="Troubleshooting Fixes - Result">
                    </a>
                </div>
            </div>
        </div>

        <h3><?php esc_html_e('Other Settings Tabs', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Additional Configuration Sections', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('The Global Settings page contains multiple tabs with specialized configuration options.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Available Settings Sections:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event List Settings:</strong> <?php esc_html_e('Control how events display in list views', 'mage-eventpress'); ?></li>
                    <li><strong>Single Event Settings:</strong> <?php esc_html_e('Configure individual event page display options', 'mage-eventpress'); ?></li>
                    <li><strong>Email Settings:</strong> <?php esc_html_e('Set up email templates and sender information', 'mage-eventpress'); ?></li>
                    <li><strong>Date & Time Format Settings:</strong> <?php esc_html_e('Customize how dates and times appear', 'mage-eventpress'); ?></li>
                    <li><strong>Style Settings:</strong> <?php esc_html_e('Set colors and visual appearance', 'mage-eventpress'); ?></li>
                    <li><strong>Icon Settings:</strong> <?php esc_html_e('Choose icons for different event elements', 'mage-eventpress'); ?></li>
                    <li><strong>Translation Settings:</strong> <?php esc_html_e('Customize all text labels and messages', 'mage-eventpress'); ?></li>
                    <li><strong>Carousel Settings:</strong> <?php esc_html_e('Configure event carousel/slider options', 'mage-eventpress'); ?></li>
                    <li><strong>Slider Settings:</strong> <?php esc_html_e('Set up featured event sliders', 'mage-eventpress'); ?></li>
                    <li><strong>Custom CSS:</strong> <?php esc_html_e('Add custom styling to override plugin CSS', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Each tab contains detailed settings for specific aspects of the plugin. Visit each tab to configure options relevant to your needs.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-screenshots">
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/settings-tabs-overview.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/settings-tabs-overview.png" alt="Settings Tabs Overview - Admin">
                    </a>
                    <a href="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/settings-comprehensive-view.png" class="mep-lightbox">
                        <img src="https://raw.githubusercontent.com/magepeopleteam/mageresource/main/Event/settings-comprehensive-view.png" alt="Settings Comprehensive View - Admin">
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_event_creation_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Event Creation Guide', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Step-by-step guide to creating events. After mastering the basics here, visit the specific tabs below for detailed configuration of each feature.', 'mage-eventpress'); ?></p>

        <h3><?php esc_html_e('Quick Start - Creating Your First Event', 'mage-eventpress'); ?></h3>
        
        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Step 1: Add New Event', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Navigate to Events > Add New to create your event with basic information.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Basic Information:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Event Title:</strong> <?php esc_html_e('Enter a clear, descriptive event name', 'mage-eventpress'); ?></li>
                    <li><strong>Event Description:</strong> <?php esc_html_e('Add detailed content using the WordPress editor', 'mage-eventpress'); ?></li>
                    <li><strong>Featured Image:</strong> <?php esc_html_e('Upload a main event image (1200x800px recommended)', 'mage-eventpress'); ?></li>
                    <li><strong>Categories & Organizers:</strong> <?php esc_html_e('Select or create event categories and organizers', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Save as Draft while building your event. You can publish it after configuring all the details below.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('event-creation-basic', 'Event Creation Basic Information', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Step 2: Configure Essential Settings', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('After saving your basic event information, configure the essential tabs below the editor.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Required Configuration (in order):', 'mage-eventpress'); ?></strong>
                <ol>
                    <li><strong>Venue/Location Tab:</strong> <?php esc_html_e('Set where your event takes place (physical, virtual, or hybrid)', 'mage-eventpress'); ?></li>
                    <li><strong>Date & Time Tab:</strong> <?php esc_html_e('Configure when your event occurs (single, multiple, or recurring)', 'mage-eventpress'); ?></li>
                    <li><strong>Ticket & Pricing Tab:</strong> <?php esc_html_e('Create ticket types, set prices, and enable registration', 'mage-eventpress'); ?></li>
                </ol>
                <div class="warning">
                    <?php esc_html_e('Events need at least these three essential tabs configured to accept bookings. Detailed instructions for each tab are available in the respective documentation sections.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('event-meta-box-tabs', 'Event Configuration Tabs', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Step 3: Add Advanced Features (Optional)', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Enhance your event with additional features and information.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Optional Enhancement Tabs:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>FAQ Settings:</strong> <?php esc_html_e('Add frequently asked questions specific to this event', 'mage-eventpress'); ?></li>
                    <li><strong>Email Text:</strong> <?php esc_html_e('Customize confirmation email templates for this event', 'mage-eventpress'); ?></li>
                    <li><strong>Speaker:</strong> <?php esc_html_e('Add speaker profiles and information', 'mage-eventpress'); ?></li>
                    <li><strong>Timeline Details:</strong> <?php esc_html_e('Create detailed event schedule and agenda', 'mage-eventpress'); ?></li>
                    <li><strong>Gallery:</strong> <?php esc_html_e('Add image galleries and custom thumbnails', 'mage-eventpress'); ?></li>
                    <li><strong>Template:</strong> <?php esc_html_e('Choose display templates for this event', 'mage-eventpress'); ?></li>
                    <li><strong>Related Events:</strong> <?php esc_html_e('Select related events for cross-promotion', 'mage-eventpress'); ?></li>
                    <li><strong>Event Settings:</strong> <?php esc_html_e('Configure display options and booking controls', 'mage-eventpress'); ?></li>
                    <li><strong>Tax Settings:</strong> <?php esc_html_e('Set tax configuration (if WooCommerce tax is enabled)', 'mage-eventpress'); ?></li>
                    <li><strong>SEO Content:</strong> <?php esc_html_e('Add additional content for search engine optimization', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Each tab has detailed documentation in its respective section. Start with the basics and add advanced features as needed.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('advanced-features-tabs', 'Advanced Features Configuration', true); ?>
            </div>
        </div>

        <h3><?php esc_html_e('Publishing Your Event', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Pre-Launch Checklist', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Verify these items before publishing your event to ensure everything works correctly.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Essential Checklist:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li>✓ <?php esc_html_e('Event title, description, and featured image added', 'mage-eventpress'); ?></li>
                    <li>✓ <?php esc_html_e('Venue/Location configured (address or virtual details)', 'mage-eventpress'); ?></li>
                    <li>✓ <?php esc_html_e('Date and time set correctly', 'mage-eventpress'); ?></li>
                    <li>✓ <?php esc_html_e('At least one ticket type created with pricing', 'mage-eventpress'); ?></li>
                    <li>✓ <?php esc_html_e('Registration enabled in Ticket & Pricing tab', 'mage-eventpress'); ?></li>
                    <li>✓ <?php esc_html_e('Test booking process works end-to-end', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Test your event booking process before going live. Use WooCommerce test mode to verify payments work correctly.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('event-pre-launch-checklist', 'Pre-Launch Checklist', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Status & Visibility', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Control when and how your event appears to visitors.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Publication Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Draft:</strong> <?php esc_html_e('Event saved but not visible to public - perfect for preparation', 'mage-eventpress'); ?></li>
                    <li><strong>Published:</strong> <?php esc_html_e('Event is live and visible - ready for bookings', 'mage-eventpress'); ?></li>
                    <li><strong>Private:</strong> <?php esc_html_e('Only administrators can see the event', 'mage-eventpress'); ?></li>
                    <li><strong>Scheduled:</strong> <?php esc_html_e('Set a future date when the event should go live automatically', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('event-publication-status', 'Event Publication Status', true); ?>
            </div>
        </div>

        <h3><?php esc_html_e('Next Steps', 'mage-eventpress'); ?></h3>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Explore Detailed Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Now that you understand the basics, explore the detailed documentation for each feature.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Documentation Sections:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Global Settings:</strong> <?php esc_html_e('Configure plugin-wide settings, email templates, and default options', 'mage-eventpress'); ?></li>
                    <li><strong>Venue/Location:</strong> <?php esc_html_e('Detailed guide for physical, virtual, and hybrid event setup', 'mage-eventpress'); ?></li>
                    <li><strong>Ticket & Pricing:</strong> <?php esc_html_e('Advanced ticket types, pricing strategies, and extra services', 'mage-eventpress'); ?></li>
                    <li><strong>Date & Time:</strong> <?php esc_html_e('Complex scheduling, recurring events, and multi-date configuration', 'mage-eventpress'); ?></li>
                    <li><strong>Event Settings:</strong> <?php esc_html_e('Individual event controls and display options', 'mage-eventpress'); ?></li>
                    <li><strong>Shortcodes:</strong> <?php esc_html_e('Display events anywhere on your site with shortcodes', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Each section provides comprehensive details and examples. Use the tab navigation above to access specific documentation.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('documentation-sections', 'Documentation Navigation', true); ?>
            </div>
        </div>
        <?php
    }

    private function render_venue_location_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Venue/Location Configuration', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Configure where your event takes place - physical location, virtual venue, or hybrid events.', 'mage-eventpress'); ?></p>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Type Selection', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Choose between physical, virtual, or hybrid event formats.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Available Event Types:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Physical Event:</strong> <?php esc_html_e('Traditional in-person events with venue address and Google Maps integration', 'mage-eventpress'); ?></li>
                    <li><strong>Virtual Event:</strong> <?php esc_html_e('Online events with meeting links, access codes, and joining instructions', 'mage-eventpress'); ?></li>
                    <li><strong>Hybrid Event:</strong> <?php esc_html_e('Combined physical and virtual attendance options', 'mage-eventpress'); ?></li>
                </ul>
                <div class="info">
                    <?php esc_html_e('Virtual event details are sent to attendees via confirmation emails. Ensure all joining information is accurate.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('venue-event-types', 'Event Type Selection', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Physical Location Fields', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Complete address information for in-person events.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Address Fields:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Venue Name:</strong> <?php esc_html_e('Name of the event location', 'mage-eventpress'); ?></li>
                    <li><strong>Street Address:</strong> <?php esc_html_e('Complete street address', 'mage-eventpress'); ?></li>
                    <li><strong>City, State, Postal Code, Country:</strong> <?php esc_html_e('Complete location details', 'mage-eventpress'); ?></li>
                    <li><strong>Google Maps Integration:</strong> <?php esc_html_e('Interactive map with location picker (requires API key)', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('venue-physical-location', 'Physical Location Configuration', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Virtual Event Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Setup online meeting details and access information.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Virtual Event Details:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Meeting Platform:</strong> <?php esc_html_e('Zoom, Google Meet, Microsoft Teams, etc.', 'mage-eventpress'); ?></li>
                    <li><strong>Access Information:</strong> <?php esc_html_e('Meeting links, IDs, passwords, dial-in numbers', 'mage-eventpress'); ?></li>
                    <li><strong>Technical Requirements:</strong> <?php esc_html_e('Software downloads, browser requirements', 'mage-eventpress'); ?></li>
                    <li><strong>Support Information:</strong> <?php esc_html_e('Technical support contacts', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('venue-virtual-event', 'Virtual Event Configuration', true); ?>
            </div>
        </div>
        <?php
    }

    private function render_ticket_pricing_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Ticket & Pricing Configuration', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Create ticket types, set pricing, and configure the booking system.', 'mage-eventpress'); ?></p>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Registration Control', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Enable or disable ticket sales for the event.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Registration Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>On:</strong> <?php esc_html_e('Visitors can purchase tickets and register', 'mage-eventpress'); ?></li>
                    <li><strong>Off:</strong> <?php esc_html_e('Event is display-only, no booking available', 'mage-eventpress'); ?></li>
                </ul>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Ticket Types & Pricing', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Create multiple ticket types with different pricing and availability.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Ticket Configuration:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Ticket Name:</strong> <?php esc_html_e('Descriptive name (General, VIP, Student, etc.)', 'mage-eventpress'); ?></li>
                    <li><strong>Price:</strong> <?php esc_html_e('Individual ticket price', 'mage-eventpress'); ?></li>
                    <li><strong>Available Quantity:</strong> <?php esc_html_e('Number of tickets available', 'mage-eventpress'); ?></li>
                    <li><strong>Description:</strong> <?php esc_html_e('What the ticket includes', 'mage-eventpress'); ?></li>
                    <li><strong>Icon:</strong> <?php esc_html_e('Font Awesome icon for visual identification', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('ticket-configuration', 'Ticket Configuration', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Extra Services', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Add optional services and add-ons to increase revenue.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Service Examples:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Catering:</strong> <?php esc_html_e('Lunch packages, coffee breaks, special meals', 'mage-eventpress'); ?></li>
                    <li><strong>Materials:</strong> <?php esc_html_e('Books, USB drives, certificates', 'mage-eventpress'); ?></li>
                    <li><strong>Transportation:</strong> <?php esc_html_e('Parking passes, shuttle service', 'mage-eventpress'); ?></li>
                    <li><strong>Accommodation:</strong> <?php esc_html_e('Hotel packages, group lodging', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('extra-services', 'Extra Services Configuration', true); ?>
            </div>
        </div>
        <?php
    }

    private function render_date_time_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Date & Time Configuration', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Configure event scheduling with flexible options for simple to complex patterns.', 'mage-eventpress'); ?></p>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event Schedule Types', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Choose how your event is scheduled.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Schedule Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Single Event:</strong> <?php esc_html_e('One-time event with specific start and end times', 'mage-eventpress'); ?></li>
                    <li><strong>Multiple Dates:</strong> <?php esc_html_e('Event occurs on specific selected dates', 'mage-eventpress'); ?></li>
                    <li><strong>Recurring Event:</strong> <?php esc_html_e('Daily recurring pattern with off-days configuration', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('date-time-schedule-types', 'Event Schedule Types', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Time Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Set precise timing for your events.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Time Settings:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Start Date & Time:</strong> <?php esc_html_e('When the event begins', 'mage-eventpress'); ?></li>
                    <li><strong>End Date & Time:</strong> <?php esc_html_e('When the event concludes', 'mage-eventpress'); ?></li>
                    <li><strong>Multiple Time Slots:</strong> <?php esc_html_e('Different time options for the same date', 'mage-eventpress'); ?></li>
                    <li><strong>Time Zone:</strong> <?php esc_html_e('Consider your target audience timezone', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('date-time-configuration', 'Time Configuration', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Recurring Events', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure events that repeat daily with off-days.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Recurring Features:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Repeat Duration:</strong> <?php esc_html_e('Number of days the pattern continues', 'mage-eventpress'); ?></li>
                    <li><strong>Off Days:</strong> <?php esc_html_e('Select weekdays when event doesn\'t occur', 'mage-eventpress'); ?></li>
                    <li><strong>Special Pricing:</strong> <?php esc_html_e('Different pricing for specific dates', 'mage-eventpress'); ?></li>
                    <li><strong>Individual Capacity:</strong> <?php esc_html_e('Separate capacity for each occurrence', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('recurring-events', 'Recurring Events Configuration', true); ?>
            </div>
        </div>
        <?php
    }

    private function render_event_settings_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Event Settings', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Configure individual event display options and administrative controls.', 'mage-eventpress'); ?></p>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Display Controls', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Configure what information is displayed for this specific event.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Display Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>End Date/Time Status:</strong> <?php esc_html_e('Show or hide event end date/time on frontend', 'mage-eventpress'); ?></li>
                    <li><strong>Available Seat Status:</strong> <?php esc_html_e('Display remaining seat count to users', 'mage-eventpress'); ?></li>
                    <li><strong>Registration Status:</strong> <?php esc_html_e('Enable/disable registration for this specific event', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('event-settings-display', 'Event Settings Display Controls', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Administrative Tools', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Admin-only controls and tools for managing this event.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Admin Tools:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Booking Reset:</strong> <?php esc_html_e('Reset all bookings for the event (admin only)', 'mage-eventpress'); ?></li>
                    <li><strong>Event Shortcode:</strong> <?php esc_html_e('Display shortcode with copy functionality', 'mage-eventpress'); ?></li>
                    <li><strong>WooCommerce Product ID:</strong> <?php esc_html_e('View linked WooCommerce product information', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('These settings override global preferences for individual events, allowing fine-grained control over event behavior.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('event-settings-admin', 'Event Settings Admin Tools', true); ?>
            </div>
        </div>
        <?php
    }

    private function render_tax_settings_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Tax Settings', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Configure tax settings for individual events when WooCommerce tax is enabled.', 'mage-eventpress'); ?></p>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Tax Configuration', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Set tax status and class for this specific event.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Tax Options:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Tax Status:</strong> <?php esc_html_e('Taxable, Non-taxable, or Shipping only', 'mage-eventpress'); ?></li>
                    <li><strong>Tax Class:</strong> <?php esc_html_e('Standard rate, Reduced rate, or Zero rate', 'mage-eventpress'); ?></li>
                </ul>
                <div class="warning">
                    <?php esc_html_e('Tax settings only appear when WooCommerce tax calculation is enabled.', 'mage-eventpress'); ?>
                </div>
                <?php echo mep_generate_setting_images('tax-configuration', 'Tax Configuration', true); ?>
            </div>
        </div>
        <?php
    }

    private function render_seo_content_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('SEO Content', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Add SEO-optimized content for better search engine visibility.', 'mage-eventpress'); ?></p>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Rich Content for SEO', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Additional content fields for search engines and schema markup.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('SEO Benefits:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>Rich Text Editor:</strong> <?php esc_html_e('Full WordPress editor for detailed descriptions', 'mage-eventpress'); ?></li>
                    <li><strong>Schema Markup:</strong> <?php esc_html_e('Structured data for Google rich snippets', 'mage-eventpress'); ?></li>
                    <li><strong>Search Optimization:</strong> <?php esc_html_e('Additional content for search indexing', 'mage-eventpress'); ?></li>
                    <li><strong>Social Sharing:</strong> <?php esc_html_e('Better social media preview content', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('seo-content', 'SEO Content Configuration', true); ?>
            </div>
        </div>
        <?php
    }

    private function render_shortcodes_docs() {
        // Add lightbox styles and scripts
        mep_add_lightbox_styles_and_scripts();
        ?>
        <h2><?php esc_html_e('Shortcodes Reference', 'mage-eventpress'); ?></h2>
        <p><?php esc_html_e('Display events anywhere on your site using shortcodes.', 'mage-eventpress'); ?></p>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Event List Shortcodes', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Display multiple events with various layout options.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Basic Shortcodes:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><code>[event-list]</code> - <?php esc_html_e('Display all upcoming events', 'mage-eventpress'); ?></li>
                    <li><code>[event-list cat="category-slug"]</code> - <?php esc_html_e('Show events from specific category', 'mage-eventpress'); ?></li>
                    <li><code>[event-list org="organizer-slug"]</code> - <?php esc_html_e('Display events by organizer', 'mage-eventpress'); ?></li>
                    <li><code>[event-list style="grid" columns="3"]</code> - <?php esc_html_e('Grid layout with columns', 'mage-eventpress'); ?></li>
                    <li><code>[event-list style="carousel"]</code> - <?php esc_html_e('Carousel/slider display', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('shortcodes-event-list', 'Event List Shortcodes', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Individual Event Shortcodes', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Display specific event components.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Component Shortcodes:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><code>[event-add-cart-section event="ID"]</code> - <?php esc_html_e('Booking form for specific event', 'mage-eventpress'); ?></li>
                    <li><code>[event-single event="ID"]</code> - <?php esc_html_e('Complete event details', 'mage-eventpress'); ?></li>
                    <li><code>[event-speaker event="ID"]</code> - <?php esc_html_e('Event speakers list', 'mage-eventpress'); ?></li>
                    <li><code>[event-timeline event="ID"]</code> - <?php esc_html_e('Event schedule/timeline', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('shortcodes-individual-event', 'Individual Event Shortcodes', true); ?>
            </div>
        </div>

        <div class="setting-item">
            <div class="setting-title"><?php esc_html_e('Shortcode Parameters', 'mage-eventpress'); ?></div>
            <div class="setting-description">
                <?php esc_html_e('Customize shortcode output with parameters.', 'mage-eventpress'); ?>
            </div>
            <div class="setting-usage">
                <strong><?php esc_html_e('Available Parameters:', 'mage-eventpress'); ?></strong>
                <ul>
                    <li><strong>show:</strong> <?php esc_html_e('Number of events to display', 'mage-eventpress'); ?></li>
                    <li><strong>status:</strong> <?php esc_html_e('upcoming, past, or all events', 'mage-eventpress'); ?></li>
                    <li><strong>city:</strong> <?php esc_html_e('Filter by city', 'mage-eventpress'); ?></li>
                    <li><strong>country:</strong> <?php esc_html_e('Filter by country', 'mage-eventpress'); ?></li>
                    <li><strong>pagination:</strong> <?php esc_html_e('Enable pagination (yes/no)', 'mage-eventpress'); ?></li>
                </ul>
                <?php echo mep_generate_setting_images('shortcodes-parameters', 'Shortcode Parameters', true); ?>
            </div>
        </div>
        <?php
    }
}

// Initialize the documentation
new MPWEM_Documentation();
?> 