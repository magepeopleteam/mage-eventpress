<?php
/*
* Additional methods for MPWEM_Documentation class
* @Author 		MagePeople Team  
* Copyright: 	mage-people.com
*/

if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

// This file contains additional methods that extend the MPWEM_Documentation class
if (class_exists('MPWEM_Documentation')) {
    
    class MPWEM_Documentation_Extended extends MPWEM_Documentation {
        
        protected function render_event_creation_docs() {
            ?>
            <h2><?php esc_html_e('Event Creation Guide', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Learn how to create and configure events step by step.', 'mage-eventpress'); ?></p>

            <h3><?php esc_html_e('Creating a New Event', 'mage-eventpress'); ?></h3>
            
            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Basic Event Information', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Start by adding basic event details in the standard WordPress editor.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Required Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Title:</strong> <?php esc_html_e('Event name that will appear on frontend', 'mage-eventpress'); ?></li>
                        <li><strong>Description:</strong> <?php esc_html_e('Detailed event information using WordPress editor', 'mage-eventpress'); ?></li>
                        <li><strong>Featured Image:</strong> <?php esc_html_e('Main event image displayed in lists and details', 'mage-eventpress'); ?></li>
                        <li><strong>Excerpt:</strong> <?php esc_html_e('Short summary for event listings', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Event Categories & Tags', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Organize events using categories and organizers for better navigation and filtering.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Organization Options:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Event Categories:</strong> <?php esc_html_e('Group similar events (Music, Sports, Business, etc.)', 'mage-eventpress'); ?></li>
                        <li><strong>Event Organizers:</strong> <?php esc_html_e('Associate events with specific organizers', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Categories and organizers can be created beforehand or added while creating events.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        protected function render_venue_location_docs() {
            ?>
            <h2><?php esc_html_e('Venue/Location Settings', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Configure where your event takes place - physical location, virtual venue, or hybrid.', 'mage-eventpress'); ?></p>

            <h3><?php esc_html_e('Event Type Configuration', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Online Event Enable/Disable', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Choose whether your event is physical, virtual, or hybrid. This affects how location information is displayed.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Event Types:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Physical Event:</strong> <?php esc_html_e('In-person event with physical address', 'mage-eventpress'); ?></li>
                        <li><strong>Online Event:</strong> <?php esc_html_e('Virtual event with meeting links', 'mage-eventpress'); ?></li>
                        <li><strong>Hybrid Event:</strong> <?php esc_html_e('Both physical and virtual attendance options', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Physical Venue Settings', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure physical location details including address, coordinates, and map display.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Location Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Venue Name:</strong> <?php esc_html_e('Name of the venue or location', 'mage-eventpress'); ?></li>
                        <li><strong>Address Line 1:</strong> <?php esc_html_e('Street address', 'mage-eventpress'); ?></li>
                        <li><strong>Address Line 2:</strong> <?php esc_html_e('Additional address info (apartment, suite)', 'mage-eventpress'); ?></li>
                        <li><strong>City:</strong> <?php esc_html_e('City name', 'mage-eventpress'); ?></li>
                        <li><strong>State:</strong> <?php esc_html_e('State or province', 'mage-eventpress'); ?></li>
                        <li><strong>Zip Code:</strong> <?php esc_html_e('Postal code', 'mage-eventpress'); ?></li>
                        <li><strong>Country:</strong> <?php esc_html_e('Country name', 'mage-eventpress'); ?></li>
                        <li><strong>Latitude/Longitude:</strong> <?php esc_html_e('GPS coordinates for precise mapping', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('GPS coordinates provide the most accurate map display. You can find coordinates using Google Maps.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Virtual Event Settings', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Set up virtual meeting information for online events.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Virtual Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Meeting Platform:</strong> <?php esc_html_e('Zoom, Google Meet, Teams, etc.', 'mage-eventpress'); ?></li>
                        <li><strong>Meeting Link:</strong> <?php esc_html_e('URL for joining the event', 'mage-eventpress'); ?></li>
                        <li><strong>Meeting ID:</strong> <?php esc_html_e('Meeting room identifier', 'mage-eventpress'); ?></li>
                        <li><strong>Password:</strong> <?php esc_html_e('Meeting access password', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Virtual event details are typically sent to attendees via email after successful booking.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        protected function render_ticket_pricing_docs() {
            ?>
            <h2><?php esc_html_e('Ticket & Pricing Configuration', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Set up ticket types, pricing, and extra services for your events.', 'mage-eventpress'); ?></p>

            <h3><?php esc_html_e('Registration Controls', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Registration On/Off', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Enable or disable ticket sales and registration for the event. When disabled, the event becomes informational only.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('States:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>On (Default):</strong> <?php esc_html_e('Visitors can purchase tickets and register', 'mage-eventpress'); ?></li>
                        <li><strong>Off:</strong> <?php esc_html_e('Event is display-only, no registration available', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Turn off registration for past events or when tickets are sold out.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <h3><?php esc_html_e('Ticket Types', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Ticket Type Configuration', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Create different ticket categories with varying prices, features, and availability.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Ticket Fields:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Ticket Name:</strong> <?php esc_html_e('Descriptive ticket type name (e.g., "Early Bird", "VIP", "Student")', 'mage-eventpress'); ?></li>
                        <li><strong>Price:</strong> <?php esc_html_e('Ticket cost in your currency', 'mage-eventpress'); ?></li>
                        <li><strong>Available Qty:</strong> <?php esc_html_e('Maximum number of this ticket type available', 'mage-eventpress'); ?></li>
                        <li><strong>Description:</strong> <?php esc_html_e('What\'s included with this ticket type', 'mage-eventpress'); ?></li>
                        <li><strong>Icon:</strong> <?php esc_html_e('Visual icon for the ticket type', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Set realistic quantities - overselling can cause customer service issues.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Advanced Ticket Options', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Configure advanced ticket features like early bird pricing, group discounts, and time-based availability.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Advanced Features (Pro):', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Early Bird Pricing:</strong> <?php esc_html_e('Discounted prices until specific date', 'mage-eventpress'); ?></li>
                        <li><strong>Group Discounts:</strong> <?php esc_html_e('Reduced prices for bulk purchases', 'mage-eventpress'); ?></li>
                        <li><strong>Time-based Availability:</strong> <?php esc_html_e('Tickets available only during certain periods', 'mage-eventpress'); ?></li>
                        <li><strong>Member Pricing:</strong> <?php esc_html_e('Special prices for registered users', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Extra Services', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Add-on Services', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Offer additional paid services like meals, materials, or special access alongside ticket purchases.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Service Examples:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Catering:</strong> <?php esc_html_e('Lunch, dinner, or refreshment packages', 'mage-eventpress'); ?></li>
                        <li><strong>Materials:</strong> <?php esc_html_e('Workshop materials, books, or supplies', 'mage-eventpress'); ?></li>
                        <li><strong>Transportation:</strong> <?php esc_html_e('Shuttle service or parking', 'mage-eventpress'); ?></li>
                        <li><strong>Accommodation:</strong> <?php esc_html_e('Hotel bookings or accommodation packages', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Extra services are added to the base ticket price during checkout.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        protected function render_date_time_docs() {
            ?>
            <h2><?php esc_html_e('Date & Time Configuration', 'mage-eventpress'); ?></h2>
            <p><?php esc_html_e('Configure when your event occurs - single events, multiple dates, or recurring schedules.', 'mage-eventpress'); ?></p>

            <h3><?php esc_html_e('Event Types', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Single Event', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('One-time event occurring on a specific date and time.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Configuration:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Start Date:</strong> <?php esc_html_e('When the event begins', 'mage-eventpress'); ?></li>
                        <li><strong>Start Time:</strong> <?php esc_html_e('Event start time', 'mage-eventpress'); ?></li>
                        <li><strong>End Date:</strong> <?php esc_html_e('When the event ends (can be same day)', 'mage-eventpress'); ?></li>
                        <li><strong>End Time:</strong> <?php esc_html_e('Event end time', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Particular Event (Multiple Dates)', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Event occurring on specific multiple dates (e.g., a conference over 3 days, or classes on certain dates).', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Use Cases:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Multi-day conferences', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Workshop series on non-consecutive dates', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Training courses spread over weeks', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Shows with multiple performance dates', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="info">
                        <?php esc_html_e('Add as many date/time combinations as needed using the "Add More Dates" button.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Repeated Event (Daily Recurring)', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Event that occurs daily between start and end dates, perfect for ongoing activities.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Configuration:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Start Date & Time:</strong> <?php esc_html_e('When the recurring period begins', 'mage-eventpress'); ?></li>
                        <li><strong>End Date & Time:</strong> <?php esc_html_e('When the recurring period ends', 'mage-eventpress'); ?></li>
                        <li><strong>Repeat Period:</strong> <?php esc_html_e('Number of days the event repeats', 'mage-eventpress'); ?></li>
                    </ul>
                    <strong><?php esc_html_e('Perfect For:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('Daily fitness classes', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Museum exhibitions', 'mage-eventpress'); ?></li>
                        <li><?php esc_html_e('Food festivals running multiple days', 'mage-eventpress'); ?></li>
                    </ul>
                </div>
            </div>

            <h3><?php esc_html_e('Off Days Configuration', 'mage-eventpress'); ?></h3>

            <div class="setting-item">
                <div class="setting-title"><?php esc_html_e('Ticket Off Days', 'mage-eventpress'); ?></div>
                <div class="setting-description">
                    <?php esc_html_e('Specify which days of the week tickets are not available for purchase, even during the event period.', 'mage-eventpress'); ?>
                </div>
                <div class="setting-usage">
                    <strong><?php esc_html_e('Common Off Days:', 'mage-eventpress'); ?></strong>
                    <ul>
                        <li><strong>Sundays:</strong> <?php esc_html_e('Many businesses closed on Sundays', 'mage-eventpress'); ?></li>
                        <li><strong>Mondays:</strong> <?php esc_html_e('Museums often closed on Mondays', 'mage-eventpress'); ?></li>
                        <li><strong>Weekends:</strong> <?php esc_html_e('Business events may exclude weekends', 'mage-eventpress'); ?></li>
                    </ul>
                    <div class="warning">
                        <?php esc_html_e('Off days apply to recurring events and prevent ticket sales on those days.', 'mage-eventpress'); ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}
?> 