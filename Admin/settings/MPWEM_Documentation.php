<?php
/*
* @Author 		MagePeople Team
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

if (!class_exists('MPWEM_Documentation')) {
    class MPWEM_Documentation {
        public function __construct() {
            add_action('admin_menu', array($this, 'add_documentation_submenu'));
        }

        public function add_documentation_submenu() {
            $event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
            add_submenu_page(
                'edit.php?post_type=mep_events',
                __($event_label . ' Documentation', 'mage-eventpress'),
                __('📖 Documentation', 'mage-eventpress'),
                'manage_options',
                'mep_documentation',
                array($this, 'documentation_page')
            );
        }

        public function documentation_page() {
            $event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
            ?>
            <div class="wrap">
                <h1><?php echo esc_html($event_label); ?> <?php esc_html_e('Complete Documentation', 'mage-eventpress'); ?></h1>
                
                <div class="mep-documentation-container">
                    <div class="mep-doc-sidebar">
                        <ul class="mep-doc-nav">
                            <li><a href="#global-settings" class="nav-link active"><?php esc_html_e('Global Settings', 'mage-eventpress'); ?></a></li>
                            <li><a href="#event-creation" class="nav-link"><?php esc_html_e('Event Creation', 'mage-eventpress'); ?></a></li>
                            <li><a href="#venue-location" class="nav-link"><?php esc_html_e('Venue/Location', 'mage-eventpress'); ?></a></li>
                            <li><a href="#ticket-pricing" class="nav-link"><?php esc_html_e('Ticket & Pricing', 'mage-eventpress'); ?></a></li>
                            <li><a href="#date-time" class="nav-link"><?php esc_html_e('Date & Time', 'mage-eventpress'); ?></a></li>
                            <li><a href="#event-settings" class="nav-link"><?php esc_html_e('Event Settings', 'mage-eventpress'); ?></a></li>
                            <li><a href="#tax-settings" class="nav-link"><?php esc_html_e('Tax Settings', 'mage-eventpress'); ?></a></li>
                            <li><a href="#seo-content" class="nav-link"><?php esc_html_e('SEO Content', 'mage-eventpress'); ?></a></li>
                            <li><a href="#shortcodes" class="nav-link"><?php esc_html_e('Shortcodes', 'mage-eventpress'); ?></a></li>
                        </ul>
                    </div>
                    
                    <div class="mep-doc-content">
                        <!-- Global Settings -->
                        <section id="global-settings" class="doc-section active">
                            <?php $this->render_global_settings_docs(); ?>
                        </section>

                        <!-- Event Creation -->
                        <section id="event-creation" class="doc-section">
                            <?php $this->render_event_creation_docs(); ?>
                        </section>

                        <!-- Venue/Location -->
                        <section id="venue-location" class="doc-section">
                            <?php $this->render_venue_location_docs(); ?>
                        </section>

                        <!-- Ticket & Pricing -->
                        <section id="ticket-pricing" class="doc-section">
                            <?php $this->render_ticket_pricing_docs(); ?>
                        </section>

                        <!-- Date & Time -->
                        <section id="date-time" class="doc-section">
                            <?php $this->render_date_time_docs(); ?>
                        </section>

                        <!-- Event Settings -->
                        <section id="event-settings" class="doc-section">
                            <?php $this->render_event_settings_docs(); ?>
                        </section>

                        <!-- Tax Settings -->
                        <section id="tax-settings" class="doc-section">
                            <?php $this->render_tax_settings_docs(); ?>
                        </section>

                        <!-- SEO Content -->
                        <section id="seo-content" class="doc-section">
                            <?php $this->render_seo_content_docs(); ?>
                        </section>

                        <!-- Shortcodes -->
                        <section id="shortcodes" class="doc-section">
                            <?php $this->render_shortcodes_docs(); ?>
                        </section>
                    </div>
                </div>
            </div>

            <style>
                .mep-documentation-container {
                    display: flex;
                    gap: 20px;
                    margin-top: 20px;
                }
                .mep-doc-sidebar {
                    width: 250px;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 20px;
                    position: sticky;
                    top: 32px;
                    height: fit-content;
                }
                .mep-doc-nav {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                }
                .mep-doc-nav li {
                    margin-bottom: 10px;
                }
                .mep-doc-nav .nav-link {
                    display: block;
                    padding: 10px 15px;
                    text-decoration: none;
                    color: #333;
                    border-radius: 3px;
                    transition: all 0.3s;
                }
                .mep-doc-nav .nav-link:hover,
                .mep-doc-nav .nav-link.active {
                    background: #0073aa;
                    color: #fff;
                }
                .mep-doc-content {
                    flex: 1;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 30px;
                }
                .doc-section {
                    display: none;
                }
                .doc-section.active {
                    display: block;
                }
                .doc-section h2 {
                    color: #0073aa;
                    border-bottom: 2px solid #0073aa;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                .doc-section h3 {
                    color: #333;
                    margin-top: 25px;
                    margin-bottom: 15px;
                }
                .setting-item {
                    background: #f9f9f9;
                    border: 1px solid #e5e5e5;
                    border-radius: 5px;
                    padding: 15px;
                    margin-bottom: 15px;
                }
                .setting-title {
                    font-weight: bold;
                    color: #0073aa;
                    margin-bottom: 5px;
                }
                .setting-description {
                    margin-bottom: 10px;
                    line-height: 1.6;
                }
                .setting-usage {
                    background: #fff;
                    border-left: 4px solid #0073aa;
                    padding: 10px 15px;
                    margin-top: 10px;
                }
                .code-example {
                    background: #272822;
                    color: #f8f8f2;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 10px 0;
                    font-family: monospace;
                    overflow-x: auto;
                }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 5px;
                    padding: 10px;
                    margin: 10px 0;
                    color: #856404;
                }
                .info {
                    background: #d1ecf1;
                    border: 1px solid #bee5eb;
                    border-radius: 5px;
                    padding: 10px;
                    margin: 10px 0;
                    color: #0c5460;
                }
            </style>

            <script>
                jQuery(document).ready(function($) {
                    $('.nav-link').click(function(e) {
                        e.preventDefault();
                        
                        // Remove active class from all
                        $('.nav-link').removeClass('active');
                        $('.doc-section').removeClass('active');
                        
                        // Add active to clicked
                        $(this).addClass('active');
                        var target = $(this).attr('href');
                        $(target).addClass('active');
                    });
                });
            </script>
            <?php
        }

        private function render_global_settings_docs() {
            // Include the complete global settings documentation
            require_once __DIR__ . '/MPWEM_Documentation_Complete_Settings.php';
            MPWEM_Complete_Settings_Documentation::render_complete_global_settings_docs();
        }

        private function render_event_creation_docs() {
            // Add lightbox styles and scripts
            mep_add_lightbox_styles_and_scripts();
            ?>
            <h2><?php esc_html_e('Event Creation - Complete Guide', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Comprehensive step-by-step guide to creating and configuring events with all available options and features.', 'mage-eventpress'); ?></p>

            <h3><?php esc_html_e('Getting Started - Creating a New Event', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Step 1: Basic Event Information', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Start by adding essential event details in the standard WordPress editor area.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Required Basic Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Event Title:</strong> <?php esc_html_e('Clear, descriptive name that will appear on frontend and in listings', 'mage-eventpress'); ?></li>
                        <li><strong>Event Description:</strong> <?php esc_html_e('Detailed information using WordPress editor with images, videos, formatting', 'mage-eventpress'); ?></li>
                        <li><strong>Featured Image:</strong> <?php esc_html_e('Main event image (recommended 1200x800px) displayed in lists and details', 'mage-eventpress'); ?></li>
                        <li><strong>Excerpt:</strong> <?php esc_html_e('Short summary for event listings and previews', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Content Best Practices:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Use engaging, benefit-focused language in descriptions', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Include what attendees will learn, experience, or gain', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Add speaker information, agenda highlights, or special features', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Use headers, bullet points, and formatting for readability', 'mage-eventpress'); ?></li>
                    </ul>
                    <?php echo mep_generate_setting_images('event-creation-basic', 'Event Creation Basic Info', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Step 2: Event Categories & Organization', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Organize events using categories and organizers for better navigation, filtering, and discovery.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Organization Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Event Categories:</strong> <?php esc_html_e('Group similar events (Music, Sports, Business, Workshops, etc.)', 'mage-eventpress'); ?></li>
                        <li><strong>Event Organizers:</strong> <?php esc_html_e('Associate events with specific organizers for branding and filtering', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Category Examples:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Business & Professional:</strong> <?php esc_html_e('Conferences, workshops, networking events', 'mage-eventpress'); ?></li>
                        <li><strong>Entertainment:</strong> <?php esc_html_e('Concerts, shows, festivals, comedy nights', 'mage-eventpress'); ?></li>
                        <li><strong>Education & Training:</strong> <?php esc_html_e('Seminars, courses, certification programs', 'mage-eventpress'); ?></li>
                        <li><strong>Sports & Fitness:</strong> <?php esc_html_e('Competitions, marathons, fitness classes', 'mage-eventpress'); ?></li>
                        <li><strong>Community & Social:</strong> <?php esc_html_e('Meetups, charity events, cultural celebrations', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Categories and organizers can be created beforehand or added while creating events. They appear in filtering options and help visitors find relevant events.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('event-categories-organizers', 'Event Categories and Organizers', true); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Step 3: Essential Event Configuration Tabs', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Understanding Event Meta Box Tabs', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('After saving your basic event information, you\'ll see several configuration tabs below the editor. Each tab handles specific aspects of your event setup.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Essential Configuration Tabs (Required):', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Venue/Location:</strong> <?php esc_html_e('Physical address, virtual meeting details, or hybrid setup - determines where your event happens', 'mage-eventpress'); ?></li>
                        <li><strong>Ticket & Pricing:</strong> <?php esc_html_e('Create ticket types, set prices, configure capacity and extra services', 'mage-eventpress'); ?></li>
                        <li><strong>Date & Time:</strong> <?php esc_html_e('Single, multiple, or recurring event schedules with start/end times', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Additional Configuration Tabs (Optional):', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Event Settings:</strong> <?php esc_html_e('Display options, booking controls, and administrative settings', 'mage-eventpress'); ?></li>
                        <li><strong>Tax Settings:</strong> <?php esc_html_e('Tax configuration when WooCommerce tax calculation is enabled', 'mage-eventpress'); ?></li>
                        <li><strong>SEO Content:</strong> <?php esc_html_e('Additional content for search engines and schema markup optimization', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Advanced Feature Tabs (Premium Features):', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>FAQ Settings:</strong> <?php esc_html_e('Create frequently asked questions specific to this event', 'mage-eventpress'); ?></li>
                        <li><strong>Email Text:</strong> <?php esc_html_e('Custom confirmation email templates for this specific event', 'mage-eventpress'); ?></li>
                        <li><strong>Speaker Information:</strong> <?php esc_html_e('Assign and display event speakers with photos and bios', 'mage-eventpress'); ?></li>
                        <li><strong>Timeline Details:</strong> <?php esc_html_e('Create detailed event schedule/agenda with time-based activities', 'mage-eventpress'); ?></li>
                        <li><strong>Gallery:</strong> <?php esc_html_e('Image galleries, slideshows, and custom thumbnails', 'mage-eventpress'); ?></li>
                        <li><strong>Template:</strong> <?php esc_html_e('Choose display template and layout for this specific event', 'mage-eventpress'); ?></li>
                        <li><strong>Related Events:</strong> <?php esc_html_e('Show related or similar events to boost cross-promotion', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Start with the essential tabs (Venue/Location, Ticket & Pricing, Date & Time) for a basic functioning event. Add advanced features as needed for enhanced user experience.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('event-meta-box-tabs', 'Event Meta Box Configuration Tabs', true); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Essential Tab Configuration Details', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Venue/Location Tab - Complete Setup', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure where your event takes place - the foundation for attendee logistics and information.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Event Type Selection:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Physical Event (Default):</strong> <?php esc_html_e('In-person event with venue address, maps, and directions', 'mage-eventpress'); ?></li>
                        <li><strong>Online/Virtual Event:</strong> <?php esc_html_e('Remote event with meeting links, passwords, and joining instructions', 'mage-eventpress'); ?></li>
                        <li><strong>Hybrid Event:</strong> <?php esc_html_e('Both physical and virtual attendance options available simultaneously', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Physical Event Configuration:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Venue Name:</strong> <?php esc_html_e('Complete venue name (e.g., "Grand Convention Center Hall A")', 'mage-eventpress'); ?></li>
                        <li><strong>Complete Address:</strong> <?php esc_html_e('Street address, city, state/province, postal code, country', 'mage-eventpress'); ?></li>
                        <li><strong>Google Maps Integration:</strong> <?php esc_html_e('Interactive map with location picker and directions', 'mage-eventpress'); ?></li>
                        <li><strong>Parking Information:</strong> <?php esc_html_e('Include parking availability, costs, and alternatives in description', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Virtual Event Configuration:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Platform Information:</strong> <?php esc_html_e('Zoom, Google Meet, Microsoft Teams, custom platform details', 'mage-eventpress'); ?></li>
                        <li><strong>Access Details:</strong> <?php esc_html_e('Meeting links, IDs, passwords, dial-in numbers', 'mage-eventpress'); ?></li>
                        <li><strong>Technical Requirements:</strong> <?php esc_html_e('Software downloads, browser requirements, system specifications', 'mage-eventpress'); ?></li>
                        <li><strong>Support Information:</strong> <?php esc_html_e('Technical support contacts and troubleshooting resources', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Virtual event details are sent to attendees via confirmation emails. Double-check all joining information for accuracy before publishing.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('venue-location-setup', 'Venue Location Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Ticket & Pricing Tab - Revenue Setup', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Create ticket types, set pricing, and configure the booking system that drives your event revenue.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Registration Control:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Registration On/Off:</strong> <?php esc_html_e('Global toggle to enable/disable all ticket sales for this event', 'mage-eventpress'); ?></li>
                        <li><strong>Event Shortcode:</strong> <?php esc_html_e('Copy shortcode to display booking form on any page or post', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Ticket Type Creation:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Ticket Name:</strong> <?php esc_html_e('Descriptive names like "General Admission", "VIP Access", "Student Discount"', 'mage-eventpress'); ?></li>
                        <li><strong>Pricing:</strong> <?php esc_html_e('Set individual prices with currency formatting and decimal support', 'mage-eventpress'); ?></li>
                        <li><strong>Available Quantity:</strong> <?php esc_html_e('Total tickets available for this type (capacity management)', 'mage-eventpress'); ?></li>
                        <li><strong>Descriptions:</strong> <?php esc_html_e('Detail what each ticket includes, benefits, restrictions', 'mage-eventpress'); ?></li>
                        <li><strong>Custom Icons:</strong> <?php esc_html_e('Font Awesome icons for visual identification', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Pricing Strategy Examples:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Tiered Pricing:</strong> <?php esc_html_e('General ($50), Premium ($75), VIP ($150)', 'mage-eventpress'); ?></li>
                        <li><strong>Early Bird Discounts:</strong> <?php esc_html_e('Early Bird ($40), Regular ($60)', 'mage-eventpress'); ?></li>
                        <li><strong>Group Discounts:</strong> <?php esc_html_e('Individual ($100), Group of 5+ ($80 each)', 'mage-eventpress'); ?></li>
                        <li><strong>Student/Senior:</strong> <?php esc_html_e('Regular ($50), Student/Senior ($35)', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Extra Services & Add-ons:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Catering Options:</strong> <?php esc_html_e('Lunch packages, coffee breaks, special dietary meals', 'mage-eventpress'); ?></li>
                        <li><strong>Materials & Resources:</strong> <?php esc_html_e('Workshop materials, books, USB drives, certificates', 'mage-eventpress'); ?></li>
                        <li><strong>Transportation:</strong> <?php esc_html_e('Shuttle service, parking passes, airport transfers', 'mage-eventpress'); ?></li>
                        <li><strong>Accommodation:</strong> <?php esc_html_e('Hotel packages, group lodging, extended stay options', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Extra services are added to the base ticket price during checkout. Price strategically based on your target audience and local market rates.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('ticket-pricing-setup', 'Ticket Pricing Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Date & Time Tab - Schedule Configuration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure when your event occurs with flexible scheduling options for simple to complex event patterns.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Event Schedule Types:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Single Event:</strong> <?php esc_html_e('One-time event with specific start and end date/time', 'mage-eventpress'); ?></li>
                        <li><strong>Multiple Dates (Particular):</strong> <?php esc_html_e('Event occurs on specific selected dates with custom scheduling', 'mage-eventpress'); ?></li>
                        <li><strong>Recurring Event (Repeated):</strong> <?php esc_html_e('Daily recurring pattern with off-days and date range configuration', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Time Configuration Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Start Date & Time:</strong> <?php esc_html_e('When the event begins (affects ticket availability)', 'mage-eventpress'); ?></li>
                        <li><strong>End Date & Time:</strong> <?php esc_html_e('When the event concludes (can span multiple days)', 'mage-eventpress'); ?></li>
                        <li><strong>Multiple Time Slots:</strong> <?php esc_html_e('Different time options for the same event date', 'mage-eventpress'); ?></li>
                        <li><strong>Time Zone Consideration:</strong> <?php esc_html_e('Ensure times are set for your target audience\'s time zone', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Recurring Event Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Repeat Duration:</strong> <?php esc_html_e('Set how many days the event pattern continues', 'mage-eventpress'); ?></li>
                        <li><strong>Off Days Configuration:</strong> <?php esc_html_e('Select weekdays when event doesn\'t occur (e.g., no Sundays)', 'mage-eventpress'); ?></li>
                        <li><strong>Special Date Pricing:</strong> <?php esc_html_e('Different pricing for specific dates within the recurring pattern', 'mage-eventpress'); ?></li>
                        <li><strong>Capacity Per Date:</strong> <?php esc_html_e('Individual capacity limits for each occurrence', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Scheduling Best Practices:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Consider your target audience\'s availability (evenings for working professionals)', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Check for conflicting events or holidays in your market', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Allow sufficient time for promotion (2-4 weeks minimum)', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Set realistic event durations that match content and expectations', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Date and time settings affect ticket availability and pricing calculations. Changes after promotion may confuse attendees - plan carefully.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('date-time-setup', 'Date Time Configuration', true); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Advanced Feature Tabs - Enhanced Experience', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('FAQ Settings - Address Common Questions', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Create event-specific frequently asked questions to reduce support inquiries and improve attendee confidence.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('FAQ Components:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>FAQ Description:</strong> <?php esc_html_e('Introductory text explaining the FAQ section purpose', 'mage-eventpress'); ?></li>
                        <li><strong>Question & Answer Pairs:</strong> <?php esc_html_e('Unlimited Q&A with rich text editor for detailed responses', 'mage-eventpress'); ?></li>
                        <li><strong>Collapsible Display:</strong> <?php esc_html_e('Questions expand/collapse for clean user interface', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Common FAQ Topics:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Event Logistics:</strong> <?php esc_html_e('Parking, arrival times, check-in process, what to bring', 'mage-eventpress'); ?></li>
                        <li><strong>Content & Format:</strong> <?php esc_html_e('Agenda details, speaker information, materials provided', 'mage-eventpress'); ?></li>
                        <li><strong>Policies:</strong> <?php esc_html_e('Cancellation, refunds, transfers, photography, recording', 'mage-eventpress'); ?></li>
                        <li><strong>Technical Support:</strong> <?php esc_html_e('For virtual events - platform help, requirements, troubleshooting', 'mage-eventpress'); ?></li>
                    </ul>
                    <?php echo mep_generate_setting_images('faq-settings', 'FAQ Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Speaker Management - Build Authority', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Add credible speakers to increase event value and attendee trust through expert association.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Speaker Setup Process:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Create Speaker Profiles:</strong> <?php esc_html_e('First create speakers using "Event Speakers" post type', 'mage-eventpress'); ?></li>
                        <li><strong>Speaker Information:</strong> <?php esc_html_e('Name, photo, bio, credentials, social links, contact info', 'mage-eventpress'); ?></li>
                        <li><strong>Assign to Events:</strong> <?php esc_html_e('Select speakers from dropdown in this tab', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Speaker Display Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Section Label:</strong> <?php esc_html_e('Custom heading for speakers section (e.g., "Featured Experts")', 'mage-eventpress'); ?></li>
                        <li><strong>Custom Icon:</strong> <?php esc_html_e('Font Awesome icon for speakers section', 'mage-eventpress'); ?></li>
                        <li><strong>Multiple Speakers:</strong> <?php esc_html_e('Select multiple speakers for panel discussions or multi-session events', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Speaker profiles can be reused across multiple events. Create detailed, professional profiles to enhance credibility and attract more attendees.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('speaker-management', 'Speaker Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Timeline Details - Event Agenda', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Create detailed event schedules and agendas to help attendees plan their participation and understand event flow.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Timeline Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Activity Entries:</strong> <?php esc_html_e('Unlimited schedule items with title, time, and detailed descriptions', 'mage-eventpress'); ?></li>
                        <li><strong>Rich Content Editor:</strong> <?php esc_html_e('Full text editor for activity descriptions with formatting', 'mage-eventpress'); ?></li>
                        <li><strong>Flexible Timing:</strong> <?php esc_html_e('Any time format for schedule entries (9:00 AM, 14:30, etc.)', 'mage-eventpress'); ?></li>
                        <li><strong>Visual Timeline:</strong> <?php esc_html_e('Numbered timeline presentation on event detail pages', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Timeline Examples:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Conference Schedule:</strong> <?php esc_html_e('Registration, keynote, breakout sessions, lunch, networking', 'mage-eventpress'); ?></li>
                        <li><strong>Workshop Agenda:</strong> <?php esc_html_e('Introduction, hands-on activities, Q&A, wrap-up', 'mage-eventpress'); ?></li>
                        <li><strong>Multi-Day Event:</strong> <?php esc_html_e('Day-by-day breakdown with session details', 'mage-eventpress'); ?></li>
                    </ul>
                    <?php echo mep_generate_setting_images('timeline-details', 'Timeline Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Email Text - Custom Confirmation Templates', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Create custom email confirmation templates specific to this event with personalized content and dynamic tags.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Email Text Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Rich Text Editor:</strong> <?php esc_html_e('Full WordPress editor with HTML support for styled emails', 'mage-eventpress'); ?></li>
                        <li><strong>Dynamic Tags:</strong> <?php esc_html_e('Use variables like {name}, {event}, {ticket_type}, {event_date}, {event_time}', 'mage-eventpress'); ?></li>
                        <li><strong>Event-Specific Content:</strong> <?php esc_html_e('Customize confirmation emails per event instead of using global templates', 'mage-eventpress'); ?></li>
                        <li><strong>Professional Formatting:</strong> <?php esc_html_e('Add images, logos, styled text, and branded content', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Available Dynamic Tags:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>{name}:</strong> <?php esc_html_e('Attendee\'s full name', 'mage-eventpress'); ?></li>
                        <li><strong>{event}:</strong> <?php esc_html_e('Event title/name', 'mage-eventpress'); ?></li>
                        <li><strong>{ticket_type}:</strong> <?php esc_html_e('Selected ticket type name', 'mage-eventpress'); ?></li>
                        <li><strong>{event_date}:</strong> <?php esc_html_e('Event date formatted', 'mage-eventpress'); ?></li>
                        <li><strong>{event_time}:</strong> <?php esc_html_e('Event start time', 'mage-eventpress'); ?></li>
                        <li><strong>{event_datetime}:</strong> <?php esc_html_e('Complete date and time information', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Custom email text overrides global email settings for this specific event. Test with real bookings to ensure dynamic tags work correctly.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('email-text-settings', 'Email Text Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Gallery & Visual Content', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Enhance event presentation with image galleries and custom thumbnails for better visual appeal.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Gallery Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Gallery Images:</strong> <?php esc_html_e('Upload multiple high-quality images showcasing venue, activities, past events', 'mage-eventpress'); ?></li>
                        <li><strong>Image Slider:</strong> <?php esc_html_e('Enable/disable automatic slideshow functionality', 'mage-eventpress'); ?></li>
                        <li><strong>Custom Thumbnail:</strong> <?php esc_html_e('Separate thumbnail image for event listings (different from featured image)', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Image Best Practices:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Use consistent 4:3 ratio (e.g., 1200x900px) for gallery images', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Show venue, attendee interactions, key moments from similar events', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Optimize image sizes for web (compress without losing quality)', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Include descriptive alt text for accessibility', 'mage-eventpress'); ?></li>
                    </ul>
                    <?php echo mep_generate_setting_images('gallery-setup', 'Gallery Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Template Settings - Display Layout', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Choose specific display templates and layouts for individual events to match your design requirements.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Template Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Event Layout Templates:</strong> <?php esc_html_e('Select from different event detail page layouts', 'mage-eventpress'); ?></li>
                        <li><strong>Custom CSS Classes:</strong> <?php esc_html_e('Add specific CSS classes for unique styling', 'mage-eventpress'); ?></li>
                        <li><strong>Display Variations:</strong> <?php esc_html_e('Override global template settings for this specific event', 'mage-eventpress'); ?></li>
                        <li><strong>Responsive Design:</strong> <?php esc_html_e('Ensure templates work across all device sizes', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Template settings allow you to customize the appearance of individual events without affecting the global design.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('template-settings', 'Template Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Related Events - Cross Promotion', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Display related or similar events to increase attendee engagement and boost cross-event promotion.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Related Events Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Manual Selection:</strong> <?php esc_html_e('Choose specific events to display as related', 'mage-eventpress'); ?></li>
                        <li><strong>Automatic Suggestions:</strong> <?php esc_html_e('System suggests events based on categories and tags', 'mage-eventpress'); ?></li>
                        <li><strong>Display Control:</strong> <?php esc_html_e('Set number of related events to show', 'mage-eventpress'); ?></li>
                        <li><strong>Strategic Placement:</strong> <?php esc_html_e('Related events appear at the bottom of event detail pages', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Cross-Promotion Benefits:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Increased overall ticket sales across multiple events', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Better user engagement and longer website sessions', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Enhanced discovery of your event portfolio', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Improved attendee experience through relevant recommendations', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Related events work best when you have multiple events in similar categories or target audiences.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('related-events', 'Related Events Configuration', true); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Additional Configuration Tabs', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Event Settings Tab - Administrative Controls', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure administrative settings, display options, and booking controls specific to this event.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Display & Booking Controls:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Registration Control:</strong> <?php esc_html_e('Enable/disable registration independently from ticket setup', 'mage-eventpress'); ?></li>
                        <li><strong>Display Options:</strong> <?php esc_html_e('Control what information appears on the frontend', 'mage-eventpress'); ?></li>
                        <li><strong>Booking Restrictions:</strong> <?php esc_html_e('Set maximum tickets per order, minimum purchase requirements', 'mage-eventpress'); ?></li>
                        <li><strong>Administrative Notes:</strong> <?php esc_html_e('Internal notes and comments for event management', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Event Settings provide granular control over how individual events behave differently from global settings.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('event-settings', 'Event Settings Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Tax Settings Tab - Tax Configuration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure tax settings for this specific event when WooCommerce tax calculation is enabled.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Tax Configuration Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Tax Class Assignment:</strong> <?php esc_html_e('Assign specific WooCommerce tax classes to this event', 'mage-eventpress'); ?></li>
                        <li><strong>Tax Exemptions:</strong> <?php esc_html_e('Set up tax-exempt status for certain ticket types', 'mage-eventpress'); ?></li>
                        <li><strong>Regional Tax Rules:</strong> <?php esc_html_e('Apply different tax rates based on attendee location', 'mage-eventpress'); ?></li>
                        <li><strong>Tax Display Options:</strong> <?php esc_html_e('Show tax-inclusive or tax-exclusive pricing', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Tax settings require WooCommerce to be installed and tax calculation enabled in WooCommerce settings.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('tax-settings', 'Tax Settings Configuration', true); ?>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('SEO Content Tab - Search Optimization', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Add SEO-specific content and structured data to improve search engine visibility and rich snippet display.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('SEO Enhancement Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Rich Text Content:</strong> <?php esc_html_e('Additional content optimized for search engines', 'mage-eventpress'); ?></li>
                        <li><strong>Schema Markup:</strong> <?php esc_html_e('Structured data for better search result display', 'mage-eventpress'); ?></li>
                        <li><strong>Meta Descriptions:</strong> <?php esc_html_e('Custom meta descriptions for search results', 'mage-eventpress'); ?></li>
                        <li><strong>Keywords Integration:</strong> <?php esc_html_e('Target specific keywords related to your event', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Search Visibility Benefits:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Improved search engine rankings for event-related searches', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Rich snippets with event dates, location, and pricing', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Better click-through rates from search results', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Enhanced local search visibility for location-based events', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('SEO content works alongside your existing SEO plugins to maximize search visibility without conflicts.', 'mage-eventpress'); ?>
                    </div>
                    <?php echo mep_generate_setting_images('seo-content', 'SEO Content Configuration', true); ?>
                </div>
            </div>

            <h3><?php esc_html_e('Event Publishing & Launch', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Pre-Launch Checklist', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Essential verification steps before making your event live to ensure everything works perfectly.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Content & Information Checklist:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('✓ Event title and description are complete and compelling', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ High-quality featured image uploaded (1200x800px recommended)', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Event category and organizer selected', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Complete venue/location information added', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ All dates and times are accurate and verified', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Booking System Checklist:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('✓ At least one ticket type created with pricing', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Ticket quantities and capacity limits set correctly', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Registration enabled in Event Settings tab', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Payment methods configured and tested', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Email notification settings configured', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Technical & Display Checklist:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('✓ Event displays correctly on desktop and mobile', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Booking process works from start to finish', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Confirmation emails are being sent and received', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ Virtual meeting links tested (for online events)', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('✓ All links and contact information verified', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Events won\'t accept bookings unless registration is enabled AND at least one ticket type is configured with available quantity greater than 0.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Publication & Visibility Control', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Control when and how your event appears to visitors using WordPress publication settings.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Publication Status Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Draft:</strong> <?php esc_html_e('Event is saved but not visible to public - perfect for preparation phase', 'mage-eventpress'); ?></li>
                        <li><strong>Published:</strong> <?php esc_html_e('Event is live and visible on your site - ready for bookings', 'mage-eventpress'); ?></li>
                        <li><strong>Private:</strong> <?php esc_html_e('Only logged-in administrators can see the event - for internal events', 'mage-eventpress'); ?></li>
                        <li><strong>Scheduled:</strong> <?php esc_html_e('Set a future date when the event should automatically go live', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Launch Strategy Recommendations:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Use "Draft" status while building and testing your event', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Schedule publication to coincide with marketing campaigns', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Test the complete booking process before going live', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Have promotional materials ready before publication', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Testing & Verification', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Complete Testing Process', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Thorough testing ensures your event works perfectly when attendees start booking.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Frontend Display Testing:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Event Detail Page:</strong> <?php esc_html_e('Visit the event page to verify all information displays correctly', 'mage-eventpress'); ?></li>
                        <li><strong>Event Listings:</strong> <?php esc_html_e('Check how the event appears in category and archive pages', 'mage-eventpress'); ?></li>
                        <li><strong>Mobile Responsiveness:</strong> <?php esc_html_e('Test on various devices and screen sizes', 'mage-eventpress'); ?></li>
                        <li><strong>Search Functionality:</strong> <?php esc_html_e('Verify event appears in search results and filters', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Booking Process Testing:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Complete Purchase Flow:</strong> <?php esc_html_e('Test the entire booking process from selection to confirmation', 'mage-eventpress'); ?></li>
                        <li><strong>Payment Processing:</strong> <?php esc_html_e('Verify payment methods work correctly (use test mode)', 'mage-eventpress'); ?></li>
                        <li><strong>Email Notifications:</strong> <?php esc_html_e('Confirm confirmation emails are sent with correct information', 'mage-eventpress'); ?></li>
                        <li><strong>Capacity Management:</strong> <?php esc_html_e('Test that ticket quantities decrease correctly after purchases', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Content & Feature Testing:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>All Links & Information:</strong> <?php esc_html_e('Verify contact details, venue information, and external links', 'mage-eventpress'); ?></li>
                        <li><strong>Virtual Event Access:</strong> <?php esc_html_e('Test meeting links and platform access for online events', 'mage-eventpress'); ?></li>
                        <li><strong>FAQ & Additional Content:</strong> <?php esc_html_e('Check that all supplementary content displays correctly', 'mage-eventpress'); ?></li>
                        <li><strong>Speaker & Timeline Data:</strong> <?php esc_html_e('Verify advanced features display as expected', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Use the event shortcode to display your event in different locations and test various display options. Consider having a colleague test the booking process for objectivity.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <h3><?php esc_html_e('Post-Launch Management', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Ongoing Event Management', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Best practices for managing your event after it goes live to ensure success.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Regular Monitoring Tasks:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Booking Analytics:</strong> <?php esc_html_e('Monitor ticket sales progress and adjust marketing if needed', 'mage-eventpress'); ?></li>
                        <li><strong>Capacity Management:</strong> <?php esc_html_e('Track remaining seats and consider increasing capacity if demand is high', 'mage-eventpress'); ?></li>
                        <li><strong>Attendee Communication:</strong> <?php esc_html_e('Send updates, reminders, and important information via email', 'mage-eventpress'); ?></li>
                        <li><strong>Content Updates:</strong> <?php esc_html_e('Add speakers, update agendas, or modify details as needed', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Promotion & Marketing Support:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Social Media Integration:</strong> <?php esc_html_e('Use social sharing features and post regular updates', 'mage-eventpress'); ?></li>
                        <li><strong>Content Marketing:</strong> <?php esc_html_e('Create blog posts, speaker interviews, or preview content', 'mage-eventpress'); ?></li>
                        <li><strong>Email Campaigns:</strong> <?php esc_html_e('Send targeted promotions to specific audience segments', 'mage-eventpress'); ?></li>
                        <li><strong>Partnership Outreach:</strong> <?php esc_html_e('Collaborate with partners, sponsors, or related organizations', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Attendee Experience Optimization:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>FAQ Updates:</strong> <?php esc_html_e('Add common questions that arise during promotion phase', 'mage-eventpress'); ?></li>
                        <li><strong>Support Response:</strong> <?php esc_html_e('Monitor and respond to attendee inquiries promptly', 'mage-eventpress'); ?></li>
                        <li><strong>Accessibility Improvements:</strong> <?php esc_html_e('Add accommodations or accessibility information as requested', 'mage-eventpress'); ?></li>
                        <li><strong>Last-Minute Updates:</strong> <?php esc_html_e('Communicate any changes clearly and in advance', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Successful events require ongoing attention and optimization. Use analytics data to understand what\'s working and make improvements for future events.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        private function render_venue_location_docs() {
            ?>
            <h2><?php esc_html_e('Venue/Location Configuration', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Configure where your event takes place - physical location, virtual venue, or hybrid events.', 'mage-eventpress'); ?></p>

            <h3><?php esc_html_e('Event Type Configuration', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Online Event Enable/Disable', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Choose whether your event is physical, virtual, or hybrid.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Event Types:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Physical Event (Default):</strong> <?php esc_html_e('In-person event with physical address and venue details', 'mage-eventpress'); ?></li>
                        <li><strong>Online Event:</strong> <?php esc_html_e('Virtual event with meeting links and online joining instructions', 'mage-eventpress'); ?></li>
                        <li><strong>Hybrid Event:</strong> <?php esc_html_e('Both physical and virtual attendance options available', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Virtual Event Settings', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Virtual Event Description', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Provide detailed instructions for virtual event access, including platform requirements and joining procedures.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Virtual Event Information:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Rich Text Editor:</strong> <?php esc_html_e('Full WordPress editor for detailed joining instructions', 'mage-eventpress'); ?></li>
                        <li><strong>Meeting Links:</strong> <?php esc_html_e('Zoom, Google Meet, Microsoft Teams, or other platform links', 'mage-eventpress'); ?></li>
                        <li><strong>Access Codes:</strong> <?php esc_html_e('Meeting IDs, passwords, and conference dial-in numbers', 'mage-eventpress'); ?></li>
                        <li><strong>Platform Requirements:</strong> <?php esc_html_e('Software downloads, browser requirements, system specs', 'mage-eventpress'); ?></li>
                        <li><strong>Technical Support:</strong> <?php esc_html_e('Contact information for technical assistance', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Virtual event details are sent to attendees via confirmation emails. Ensure all joining information is accurate and up-to-date.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <h3><?php esc_html_e('Physical Location Settings', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Venue Information', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Complete address and venue details for physical events.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Physical Location Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Venue Name:</strong> <?php esc_html_e('Name of the event location (e.g., "Grand Convention Center")', 'mage-eventpress'); ?></li>
                        <li><strong>Street Address:</strong> <?php esc_html_e('Complete street address including building/suite numbers', 'mage-eventpress'); ?></li>
                        <li><strong>City:</strong> <?php esc_html_e('City where the event takes place', 'mage-eventpress'); ?></li>
                        <li><strong>State/Province:</strong> <?php esc_html_e('State, province, or region', 'mage-eventpress'); ?></li>
                        <li><strong>Postal Code:</strong> <?php esc_html_e('ZIP code or postal code for accurate location', 'mage-eventpress'); ?></li>
                        <li><strong>Country:</strong> <?php esc_html_e('Country for international events', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Google Maps Integration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Interactive map display and location services for better user experience.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Map Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Interactive Map Display:</strong> <?php esc_html_e('Visual map showing event location on frontend', 'mage-eventpress'); ?></li>
                        <li><strong>Location Picker:</strong> <?php esc_html_e('Drag-and-drop map interface for precise location setting', 'mage-eventpress'); ?></li>
                        <li><strong>Autocomplete Address:</strong> <?php esc_html_e('Google Places API for accurate address validation', 'mage-eventpress'); ?></li>
                        <li><strong>Coordinates Capture:</strong> <?php esc_html_e('Automatic latitude/longitude for map centering', 'mage-eventpress'); ?></li>
                        <li><strong>Directions Integration:</strong> <?php esc_html_e('One-click directions from Google Maps/Apple Maps', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Google Maps integration requires a valid API key configured in Global Settings. Get your API key from Google Cloud Console.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <h3><?php esc_html_e('Hybrid Event Configuration', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Combined Physical & Virtual Setup', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure events that offer both in-person and virtual attendance options.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Hybrid Event Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Dual Ticket Types:</strong> <?php esc_html_e('Separate tickets for in-person and virtual attendees', 'mage-eventpress'); ?></li>
                        <li><strong>Location + Virtual Info:</strong> <?php esc_html_e('Both physical address and virtual joining details displayed', 'mage-eventpress'); ?></li>
                        <li><strong>Attendance Options:</strong> <?php esc_html_e('Clear indication of what each ticket type includes', 'mage-eventpress'); ?></li>
                        <li><strong>Capacity Management:</strong> <?php esc_html_e('Separate capacity limits for physical venue and virtual platform', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Hybrid events are perfect for maximizing attendance while maintaining physical venue capacity limits.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        private function render_ticket_pricing_docs() {
            ?>
            <h2><?php esc_html_e('Ticket & Pricing Configuration', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Set up ticket types, pricing, and extra services for your events.', 'mage-eventpress'); ?></p>

            <h3><?php esc_html_e('Registration Control', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Registration On/Off', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Enable or disable ticket sales and registration for the event.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Registration States:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>On (Default):</strong> <?php esc_html_e('Visitors can purchase tickets and register for the event', 'mage-eventpress'); ?></li>
                        <li><strong>Off:</strong> <?php esc_html_e('Event is display-only, no registration or booking available', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('When registration is off, the event appears as informational only. Turn this on when you\'re ready to sell tickets.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Event Shortcode', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Copy the shortcode to display this event\'s booking form anywhere on your site.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Shortcode Usage:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Page Integration:</strong> <?php esc_html_e('Paste shortcode into any page or post', 'mage-eventpress'); ?></li>
                        <li><strong>Widget Areas:</strong> <?php esc_html_e('Use in sidebars or footer areas', 'mage-eventpress'); ?></li>
                        <li><strong>Custom Templates:</strong> <?php esc_html_e('Include in theme templates using do_shortcode()', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Ticket Types Configuration', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Creating Ticket Types', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Create multiple ticket types with different pricing, descriptions, and availability.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Ticket Type Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Ticket Name:</strong> <?php esc_html_e('Display name for the ticket type (e.g., "General Admission", "VIP", "Student")', 'mage-eventpress'); ?></li>
                        <li><strong>Ticket Price:</strong> <?php esc_html_e('Cost per ticket in your site currency', 'mage-eventpress'); ?></li>
                        <li><strong>Available Quantity:</strong> <?php esc_html_e('Total number of tickets available for this type', 'mage-eventpress'); ?></li>
                        <li><strong>Ticket Description:</strong> <?php esc_html_e('Detailed description of what this ticket includes', 'mage-eventpress'); ?></li>
                        <li><strong>Ticket Icon:</strong> <?php esc_html_e('Font Awesome icon to represent this ticket type', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Common Ticket Type Examples:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>General Admission:</strong> <?php esc_html_e('Standard entry with basic access', 'mage-eventpress'); ?></li>
                        <li><strong>VIP Access:</strong> <?php esc_html_e('Premium experience with special perks', 'mage-eventpress'); ?></li>
                        <li><strong>Student/Senior:</strong> <?php esc_html_e('Discounted tickets for specific groups', 'mage-eventpress'); ?></li>
                        <li><strong>Early Bird:</strong> <?php esc_html_e('Limited-time discount pricing', 'mage-eventpress'); ?></li>
                        <li><strong>Group Package:</strong> <?php esc_html_e('Special pricing for multiple attendees', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Pricing Strategies', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Effective pricing approaches for different event types and audiences.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Pricing Models:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Free Events:</strong> <?php esc_html_e('Set price to 0 for no-cost events with capacity tracking', 'mage-eventpress'); ?></li>
                        <li><strong>Tiered Pricing:</strong> <?php esc_html_e('Multiple price levels (Standard, Premium, VIP)', 'mage-eventpress'); ?></li>
                        <li><strong>Early Bird Discounts:</strong> <?php esc_html_e('Lower prices for advance bookings', 'mage-eventpress'); ?></li>
                        <li><strong>Group Discounts:</strong> <?php esc_html_e('Reduced per-person pricing for bulk purchases', 'mage-eventpress'); ?></li>
                        <li><strong>Dynamic Pricing:</strong> <?php esc_html_e('Different prices for different dates/times', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Consider your target audience and local market rates when setting prices. Free events still require ticket creation for capacity management.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <h3><?php esc_html_e('Extra Services & Add-ons', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Additional Services Configuration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Offer optional add-on services that attendees can purchase along with their tickets.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Extra Service Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Service Name:</strong> <?php esc_html_e('Name of the additional service', 'mage-eventpress'); ?></li>
                        <li><strong>Service Price:</strong> <?php esc_html_e('Additional cost for this service', 'mage-eventpress'); ?></li>
                        <li><strong>Service Description:</strong> <?php esc_html_e('What the service includes', 'mage-eventpress'); ?></li>
                        <li><strong>Quantity Limit:</strong> <?php esc_html_e('Maximum number per customer', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Popular Add-on Services:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Parking:</strong> <?php esc_html_e('Reserved parking spots or valet service', 'mage-eventpress'); ?></li>
                        <li><strong>Meals:</strong> <?php esc_html_e('Lunch, dinner, or catering options', 'mage-eventpress'); ?></li>
                        <li><strong>Merchandise:</strong> <?php esc_html_e('T-shirts, programs, or branded items', 'mage-eventpress'); ?></li>
                        <li><strong>Workshops:</strong> <?php esc_html_e('Additional training or breakout sessions', 'mage-eventpress'); ?></li>
                        <li><strong>Accommodation:</strong> <?php esc_html_e('Hotel bookings or travel packages', 'mage-eventpress'); ?></li>
                        <li><strong>Premium Content:</strong> <?php esc_html_e('Digital downloads, recordings, or resources', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Extra services can significantly increase your event revenue while providing additional value to attendees.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <h3><?php esc_html_e('Advanced Pricing Features', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Capacity Management', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Control ticket availability and venue capacity limits effectively.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Capacity Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Per-Ticket Limits:</strong> <?php esc_html_e('Different availability for each ticket type', 'mage-eventpress'); ?></li>
                        <li><strong>Total Event Capacity:</strong> <?php esc_html_e('Overall venue limit across all ticket types', 'mage-eventpress'); ?></li>
                        <li><strong>Unlimited Tickets:</strong> <?php esc_html_e('Set quantity to -1 for no limit (virtual events)', 'mage-eventpress'); ?></li>
                        <li><strong>Sold Out Handling:</strong> <?php esc_html_e('Automatic "Sold Out" display when capacity reached', 'mage-eventpress'); ?></li>
                        <li><strong>Low Stock Warnings:</strong> <?php esc_html_e('Alert when tickets are running low', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Multiple Ticket Selection', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Allow customers to purchase multiple tickets and add-ons in a single transaction.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Multi-Ticket Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Quantity Selection:</strong> <?php esc_html_e('Dropdown or input field for ticket quantities', 'mage-eventpress'); ?></li>
                        <li><strong>Mixed Ticket Types:</strong> <?php esc_html_e('Combine different ticket types in one order', 'mage-eventpress'); ?></li>
                        <li><strong>Add-on Combinations:</strong> <?php esc_html_e('Select multiple extra services per ticket', 'mage-eventpress'); ?></li>
                        <li><strong>Price Calculation:</strong> <?php esc_html_e('Real-time total price updates', 'mage-eventpress'); ?></li>
                        <li><strong>Maximum Per Order:</strong> <?php esc_html_e('Limit total tickets per customer (prevents scalping)', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Integration & Payment', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('WooCommerce Integration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Seamless integration with WooCommerce for payment processing and order management.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Payment Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Payment Gateways:</strong> <?php esc_html_e('All WooCommerce payment methods supported', 'mage-eventpress'); ?></li>
                        <li><strong>Order Management:</strong> <?php esc_html_e('Event tickets appear as WooCommerce orders', 'mage-eventpress'); ?></li>
                        <li><strong>Tax Integration:</strong> <?php esc_html_e('Automatic tax calculation based on WooCommerce settings', 'mage-eventpress'); ?></li>
                        <li><strong>Coupons & Discounts:</strong> <?php esc_html_e('WooCommerce coupon system fully supported', 'mage-eventpress'); ?></li>
                        <li><strong>Shipping Options:</strong> <?php esc_html_e('Configure if event tickets require shipping', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('WooCommerce must be installed and configured before event ticket sales can process payments.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Currency & Localization', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Price display and currency formatting options for international events.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Currency Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Currency Selection:</strong> <?php esc_html_e('Uses WooCommerce currency settings', 'mage-eventpress'); ?></li>
                        <li><strong>Price Formatting:</strong> <?php esc_html_e('Automatic currency symbol and decimal formatting', 'mage-eventpress'); ?></li>
                        <li><strong>Multi-Currency:</strong> <?php esc_html_e('Compatible with multi-currency plugins', 'mage-eventpress'); ?></li>
                        <li><strong>Free Event Labels:</strong> <?php esc_html_e('Display "Free" instead of currency for $0 events', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>
            <?php
        }

        private function render_date_time_docs() {
            ?>
            <h2><?php esc_html_e('Date & Time Configuration - Complete Guide', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Configure when your event occurs - single events, multiple dates, or recurring schedules with advanced time management.', 'mage-eventpress'); ?></p>

            <h3><?php esc_html_e('Event Type Selection', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Single Event', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('One-time event occurring on a specific date and time.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Configuration Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Start Date:</strong> <?php esc_html_e('When the event begins (required)', 'mage-eventpress'); ?></li>
                        <li><strong>Start Time:</strong> <?php esc_html_e('Event start time (required)', 'mage-eventpress'); ?></li>
                        <li><strong>End Date:</strong> <?php esc_html_e('When the event ends (can be same day)', 'mage-eventpress'); ?></li>
                        <li><strong>End Time:</strong> <?php esc_html_e('Event end time', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Perfect for workshops, concerts, meetings, or any one-time occurrence.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Particular Event (Multiple Specific Dates)', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Event occurring on specific multiple dates (e.g., a conference over 3 days, or classes on certain dates).', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Primary Date/Time:</strong> <?php esc_html_e('Main event date and time', 'mage-eventpress'); ?></li>
                        <li><strong>Additional Dates:</strong> <?php esc_html_e('Add unlimited additional date/time combinations', 'mage-eventpress'); ?></li>
                        <li><strong>Flexible Scheduling:</strong> <?php esc_html_e('Each date can have different start/end times', 'mage-eventpress'); ?></li>
                        <li><strong>Sortable Management:</strong> <?php esc_html_e('Drag and drop to reorder dates', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Perfect For:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Multi-day conferences with different daily schedules', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Training courses spread over non-consecutive weeks', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Theater shows with multiple performance dates', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Workshop series on specific dates', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Repeated Event (Daily Recurring)', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Event that occurs daily between start and end dates, perfect for ongoing activities like exhibitions.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Configuration:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Start Date & Time:</strong> <?php esc_html_e('When the recurring period begins', 'mage-eventpress'); ?></li>
                        <li><strong>End Date & Time:</strong> <?php esc_html_e('When the recurring period ends', 'mage-eventpress'); ?></li>
                        <li><strong>Repeat Period (Days):</strong> <?php esc_html_e('Number of days the event repeats', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Perfect For:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Daily fitness classes or yoga sessions', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Museum exhibitions running for weeks/months', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Food festivals or markets', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Art gallery showings', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Advanced Time Management', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Off Days Configuration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Specify which days of the week tickets are not available for purchase, even during the event period.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Available Off Days:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Sunday:</strong> <?php esc_html_e('Exclude all Sundays from ticket availability', 'mage-eventpress'); ?></li>
                        <li><strong>Monday:</strong> <?php esc_html_e('Exclude all Mondays (common for museums)', 'mage-eventpress'); ?></li>
                        <li><strong>Tuesday through Saturday:</strong> <?php esc_html_e('Any day of the week can be excluded', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Common Use Cases:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Museums closed on Mondays', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Business events excluding weekends', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Religious venues with specific closed days', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Off days only apply to recurring events and prevent ticket sales on those specific weekdays.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Special Date Pricing & Availability', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Set different prices or availability for specific dates within recurring events.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Special Date Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Date-Specific Pricing:</strong> <?php esc_html_e('Different ticket prices for holidays or special occasions', 'mage-eventpress'); ?></li>
                        <li><strong>Limited Availability:</strong> <?php esc_html_e('Reduce capacity for certain dates', 'mage-eventpress'); ?></li>
                        <li><strong>Closed Dates:</strong> <?php esc_html_e('Completely block specific dates (holidays, maintenance)', 'mage-eventpress'); ?></li>
                        <li><strong>Multiple Special Dates:</strong> <?php esc_html_e('Add unlimited special date configurations', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Perfect for events with holiday pricing, maintenance closures, or special event days.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Time Slot Management', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Create multiple time slots for the same date, allowing attendees to choose their preferred time.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Time Slot Features:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Multiple Slots Per Day:</strong> <?php esc_html_e('Create morning, afternoon, evening sessions', 'mage-eventpress'); ?></li>
                        <li><strong>Capacity Per Slot:</strong> <?php esc_html_e('Different attendance limits for each time slot', 'mage-eventpress'); ?></li>
                        <li><strong>Day-Specific Slots:</strong> <?php esc_html_e('Different time slots for different days of the week', 'mage-eventpress'); ?></li>
                        <li><strong>Flexible Timing:</strong> <?php esc_html_e('Custom start and end times for each slot', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Perfect For:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Museum tours with hourly slots', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Fitness classes with morning/evening options', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Workshop sessions with limited capacity', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Restaurant events with multiple seatings', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Date & Time Format Settings', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Display Format Configuration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Control how dates and times appear throughout your site to match local conventions.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Available Formats:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Date Format:</strong> <?php esc_html_e('MM/DD/YYYY, DD/MM/YYYY, DD-MM-YYYY, etc.', 'mage-eventpress'); ?></li>
                        <li><strong>Time Format:</strong> <?php esc_html_e('12-hour (3:00 PM) or 24-hour (15:00) display', 'mage-eventpress'); ?></li>
                        <li><strong>Date Separator:</strong> <?php esc_html_e('Choose between slash (/), dash (-), or dot (.) separators', 'mage-eventpress'); ?></li>
                        <li><strong>Month Display:</strong> <?php esc_html_e('Numeric or abbreviated month names', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Format settings apply to all date/time displays including event lists, details, and calendar views.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        private function render_event_settings_docs() {
            do_action('mep_render_event_settings_docs');
        }

        private function render_tax_settings_docs() {
            ?>
            <h2><?php esc_html_e('Tax Settings', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Configure tax options for event tickets. This tab only appears when WooCommerce tax calculation is enabled.', 'mage-eventpress'); ?></p>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Tax Configuration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Set up tax rates and tax classes for your event tickets according to local tax requirements.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Tax Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Tax Status:</strong> <?php esc_html_e('Taxable or tax exempt', 'mage-eventpress'); ?></li>
                        <li><strong>Tax Class:</strong> <?php esc_html_e('Standard rate, reduced rate, or custom tax class', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Tax settings must first be configured in WooCommerce → Settings → Tax before they appear here.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        private function render_seo_content_docs() {
            ?>
            <h2><?php esc_html_e('SEO Content & Rich Text', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Optimize your events for search engines with structured data and additional content fields.', 'mage-eventpress'); ?></p>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Rich Text Content', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Add additional content sections that help with SEO and provide more event information.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Content Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Event Highlights:</strong> <?php esc_html_e('Key features and benefits', 'mage-eventpress'); ?></li>
                        <li><strong>What to Expect:</strong> <?php esc_html_e('Detailed agenda or program', 'mage-eventpress'); ?></li>
                        <li><strong>Terms & Conditions:</strong> <?php esc_html_e('Event-specific policies', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>
            <?php
        }

        private function render_shortcodes_docs() {
            ?>
            <h2><?php esc_html_e('Shortcodes Reference', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Use these shortcodes to display events anywhere on your website.', 'mage-eventpress'); ?></p>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Event List Shortcode', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Display a list of events with various filtering and display options.', 'mage-eventpress'); ?>
                </div>
                <div class="code-example">[event-list]</div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Parameters:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>cat:</strong> <?php esc_html_e('Filter by category ID or slug', 'mage-eventpress'); ?></li>
                        <li><strong>org:</strong> <?php esc_html_e('Filter by organizer ID or slug', 'mage-eventpress'); ?></li>
                        <li><strong>posts:</strong> <?php esc_html_e('Number of events to show', 'mage-eventpress'); ?></li>
                        <li><strong>style:</strong> <?php esc_html_e('Display style (list, grid, timeline)', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="code-example">[event-list cat="music" posts="6" style="grid"]</div>
                </div>
            </div>
            <?php
        }
    }
    
    // Include additional documentation methods
    require_once __DIR__ . '/MPWEM_Documentation_Complete.php';
    
    new MPWEM_Documentation();
} 