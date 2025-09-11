# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

**Mage EventPress** (WpEvently) is a comprehensive WordPress event management plugin that integrates with WooCommerce to provide event booking, ticketing, and management functionality.

- **Plugin Name**: Event Booking Manager for WooCommerce – WpEvently
- **Version**: 5.0.1
- **Text Domain**: mage-eventpress
- **Main File**: `woocommerce-event-press.php`

## Architecture & Code Structure

### Core Components

**Plugin Bootstrap** (`woocommerce-event-press.php`)
- Main plugin entry point
- Handles WooCommerce dependency checks
- Initializes block editor support
- Sets up plugin constants (`MPWEM_PLUGIN_DIR`, `MPWEM_PLUGIN_URL`)

**Dependencies Management** (`inc/MPWEM_Dependencies.php`)
- Central class for loading all plugin components
- Handles script/style enqueuing for admin and frontend
- Manages language loading and asset dependencies
- CSS custom properties for theming support

**Admin System** (`Admin/MPWEM_Admin.php`)
- Loads all admin-related functionality
- Manages meta boxes and settings panels
- Handles Gutenberg editor integration

### Key Functional Areas

**Event Management**
- Custom Post Type: `mep_events`
- Event metadata handling via `MPWEM_Functions` class
- Support for recurring events and multiple date/time configurations
- Venue/location management with Google Maps integration

**Ticketing System**
- Ticket types with pricing and quantity management
- Extra services and add-ons
- Seat availability calculations
- Integration with WooCommerce cart system

**Frontend Display**
- Template system with theme override support
- Multiple display styles (grid, list, timeline, etc.)
- Shortcode system for flexible content placement
- Responsive design with customizable styling

**Location & Maps**
- Dual mode support: Google Maps API and iframe embedding
- Coordinate-based location input (lat,lng format)
- Address-based geocoding
- Venue management with organizer integration

### Template System

Templates are located in `/templates/` and can be overridden by themes:
- Theme override path: `{theme}/mage-events/`
- Default templates: `{plugin}/templates/`
- Template parts: `inc/template-prts/`

## Development Commands

### Asset Management
```bash
# CSS files are located in:
# - assets/admin/ (admin styles)
# - assets/frontend/ (frontend styles)
# - assets/helper/ (utility styles)

# JavaScript files are located in:
# - assets/admin/ (admin scripts)
# - assets/frontend/ (frontend scripts)
# - assets/helper/ (utility scripts)
```

### WordPress Development
```bash
# Activate plugin in WordPress admin
# Or via WP-CLI:
wp plugin activate mage-eventpress

# Check plugin status
wp plugin status mage-eventpress

# Update database if needed
wp option update mep_plugin_version 5.0.1
```

### Database Operations
```bash
# Check event posts
wp post list --post_type=mep_events

# Check event meta
wp post meta list {event_id}

# Check taxonomy terms (organizers, categories)
wp term list mep_org
wp term list mep_cat
```

## Key Configuration Points

### Google Maps Integration
- API Key setting: `general_setting_sec > google-map-api`
- Map type: `general_setting_sec > mep_google_map_type` (iframe/api)
- For coordinates: Use format `latitude, longitude` (e.g., "56.976239, 24.419633")

### Event Settings Structure
```php
// Main event settings
$event_settings = [
    'mep_event_ticket_type' => [], // Ticket types array
    'mep_events_extra_prices' => [], // Extra services
    'mep_location_venue' => '', // Venue name or coordinates
    'mep_org_address' => '0', // Use organizer address (0=event, 1=organizer)
    'event_start_date' => '', // Event start date
    'event_end_date' => '', // Event end date
];
```

### Shortcodes
```php
// Main event list shortcode
[event-list cat='' org='' column='2' style='grid' show='10' pagination='yes']

// Add to cart section
[event-add-cart-section event="123"]
```

## Important Functions & Hooks

### Core Functions
- `mep_get_event_locaion_item()` - Retrieves location data with coordinate handling
- `MPWEM_Functions::get_dates()` - Gets event dates
- `MPWEM_Functions::get_total_available_seat()` - Calculates available seats

### Action Hooks
- `mp_event_all_in_tab_item` - Add event meta box tabs
- `mpwem_registration` - Event registration form
- `mep_event_tab_before_location` - Before location settings
- `add_mpwem_admin_script` - Add admin scripts
- `add_mpwem_frontend_script` - Add frontend scripts

### Filter Hooks
- `mpwem_event_total_seat_counts` - Modify total seat count
- `mage_event_location_in_list_view` - Customize location display
- `mep_ticket_type_price` - Modify ticket pricing

## File Structure Patterns

### Settings Files
`Admin/settings/MPWEM_*_Settings.php` - Individual setting panels
- `MPWEM_Venue_Settings.php` - Location and venue configuration
- `MPWEM_Ticket_Price_Settings.php` - Ticket pricing
- `MPWEM_Date_Settings.php` - Date and time settings

### Template Parts
`inc/template-prts/*.php` - Reusable template components
- `event_location.php` - Location display
- `google_map.php` - Map integration
- `event_add_cart.php` - Cart functionality

### Asset Organization
```
assets/
├── admin/          # Admin panel assets
├── frontend/       # Public-facing assets
├── helper/         # Shared utilities
└── blocks/         # Gutenberg block assets
```

## Common Development Tasks

### Adding New Event Meta Field
1. Add field to appropriate settings class in `Admin/settings/`
2. Update `MPWEM_Functions` class if needed
3. Add field to save_post handler in meta box class
4. Update templates to display the field

### Customizing Location Handling
- Location data supports both address strings and coordinates
- Coordinate format: `"latitude, longitude"` (preserved with comma)
- Use `mep_get_event_locaion_item()` for retrieval
- Google Maps iframe auto-detects coordinate vs. address format
- **Fixed Issue**: Coordinates are now properly preserved during save (handles comma stripping in `mep_letters_numbers_spaces_only()`)

#### Coordinate Handling Functions Modified
- `mage_array_strip()` - Updated to detect and preserve coordinate format
- `mep_letters_numbers_spaces_only()` - Updated to allow comma, decimal, minus for coordinates
- `mep_get_event_locaion_item()` - Updated to use appropriate sanitization for coordinates vs text
- Custom save handler in `MPWEM_Venue_Settings` prevents coordinate corruption

### Theme Integration
- Override templates in `{theme}/mage-events/`
- Use `MPWEM_Functions::template_path()` for proper template loading
- CSS custom properties available for styling (`:root` variables)

## Dependencies

### Required
- WordPress 5.3+
- WooCommerce 3.0+
- PHP 7.4+

### Optional Integrations
- Google Maps API (for enhanced mapping)
- Elementor (widget support available)
- Various payment gateways via WooCommerce

## Security Considerations

- All user inputs are sanitized via `mage_array_strip()`
- Location coordinates use `sanitize_text_field()` to preserve format
- Nonce verification for admin forms: `mep_fw_nonce`
- Capability checks: `edit_post` for event management

