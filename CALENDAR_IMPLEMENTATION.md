# Calendar Addon Migration — Implementation Details

> **Branch:** `feature/calendar-addon-merge`  
> **Date:** 2026-04-22  
> **Objective:** Merge all functionality from `woocommerce-event-manager-addon-calender` into the main `mage-eventpress` plugin so the addon can be removed.

---

## Table of Contents
1. [Overview](#overview)
2. [Files Added](#files-added)
3. [Files Modified](#files-modified)
4. [Architecture](#architecture)
5. [Shortcodes](#shortcodes)
6. [AJAX Endpoints](#ajax-endpoints)
7. [Admin Settings](#admin-settings)
8. [Asset Loading Strategy](#asset-loading-strategy)
9. [AJAX Calendar Toggle in `[events_list]`](#ajax-calendar-toggle-in-events_list)
10. [Backward Compatibility](#backward-compatibility)
11. [Database / Settings Preservation](#database--settings-preservation)
12. [Testing Checklist](#testing-checklist)

---

## Overview

The `woocommerce-event-manager-addon-calender` plugin provided a robust FullCalendar 6.x integration for the main `mage-eventpress` plugin. This migration ports **100%** of that functionality into the core plugin, eliminating the need for the addon while preserving all existing data, settings, and shortcodes.

### Key Features Migrated
- FullCalendar 6.x frontend calendar (`[mep-event-calendar]`)
- 37+ shortcode parameters
- Interactive tooltips with lazy stock loading
- Filter bar (search, category, organizer, location, date range)
- Admin settings page under **Events → Calendar Settings**
- Color customization, weekday column styles, day background images
- Stock indicator colors (sold out, low stock)
- Calendar view toggle inside `[events_list]` shortcode

---

## Files Added

| File | Purpose |
|------|---------|
| `inc/MPWEM_Calendar.php` | **Main integration file.** Contains helper functions, shortcode class, AJAX class, and admin settings class. |
| `assets/helper/calendar/calendar.css` | Frontend FullCalendar overrides, tooltip styles, filter bar, responsive breakpoints. |
| `assets/helper/calendar/calendar.js` | FullCalendar initialization, AJAX event source, custom tooltips, filters, locale handling, day backgrounds. |
| `assets/helper/calendar/fullcalendar.index.global.min.js` | FullCalendar v6.1.17 Standard Bundle (local fallback). |
| `assets/admin/calendar-admin.css` | Admin settings page styling (color pickers, rule grid, shortcode reference). |
| `assets/admin/calendar-admin.js` | WP Color Picker init, dynamic add/remove day background rules, WP Media Uploader. |
| `CALENDAR_IMPLEMENTATION.md` | This documentation file. |

---

## Files Modified

| File | Change |
|------|--------|
| `inc/MPWEM_Dependencies.php` | Added `require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Calendar.php';` to load the new calendar module. |
| `inc/MPWEM_Shortcodes.php` | Replaced old Equinox-based `calender()` method with `do_shortcode('[mep-event-calendar]')` for FullCalendar. |
| `inc/mep_functions.php` | Updated `gat_event_calender()` AJAX handler to return actual FullCalendar HTML via `[mep-event-calendar]` shortcode instead of the placeholder text `'calender content here'`. |
| `assets/frontend/mpwem_script.js` | Calendar toggle now passes filter parameters (`cat`, `org`, `tag`, `city`, `country`, `status`, `year`) to the AJAX endpoint and calls `mepCalendarInit()` after inserting the response HTML. |

---

## Architecture

### Class Structure

```
MPWEM_Calendar.php
├── Helper Functions
│   ├── mep_cal_get_all_settings()   // Returns merged settings with defaults
│   └── mep_cal_get_setting()        // Single setting getter
├── MPWEM_Calendar_Shortcode
│   └── render_calendar( $atts )     // [mep-event-calendar] handler
├── MPWEM_Calendar_Ajax
│   ├── get_events()                 // AJAX: mep_calendar_get_events
│   └── get_event_stock()            // AJAX: mep_calendar_get_event_stock
└── MPWEM_Calendar_Settings
    ├── add_settings_menu()          // Submenu under mep_events
    ├── register_settings()          // Registers mep_calendar_settings option
    └── render_settings_page()       // Full settings form + shortcode reference
```

### Script Registration

```php
// FullCalendar 6.x library
fullcalendar-js      → assets/helper/calendar/fullcalendar.index.global.min.js

// Plugin calendar engine
mep-calendar-js      → assets/helper/calendar/calendar.js
                     → deps: [jquery, fullcalendar-js]
                     → localized: mepCalendar { ajaxurl, nonce, settings, i18n }

// Plugin calendar styles
mep-calendar-css     → assets/helper/calendar/calendar.css
```

---

## Shortcodes

### `[mep-event-calendar]` (New / Migrated)

The primary calendar shortcode. Supports **37 parameters**:

| Parameter | Default | Description |
|-----------|---------|-------------|
| `style` | `full` | `full` or `lite` |
| `show_stock_details` | `no` | Show seat availability in tooltip |
| `hide_time` | `no` | Hide event time display |
| `split_multi_day` | `no` | Split multi-day events into separate day blocks |
| `cat` | `0` | Filter by category ID/slug |
| `org` | `0` | Filter by organizer ID/slug |
| `tag` | `0` | Filter by tag ID/slug |
| `city` | `''` | Filter by city |
| `country` | `''` | Filter by country |
| `status` | `upcoming` | `upcoming`, `all`, or `expired` |
| `event_source` | *(setting)* | `all`, `single`, `multi_date`, `repeated`, `specific` |
| `specific_events` | *(setting)* | Comma-separated event IDs |
| `default_view` | *(setting)* | `dayGridMonth`, `timeGridWeek`, `timeGridDay`, `listMonth` |
| `first_day` | *(setting)* | First day of week: `0`=Sun, `1`=Mon, `6`=Sat |
| `event_limit` | `-1` | Max events to load |
| `show_category_filter` | `no` | Show category dropdown |
| `show_organizer_filter` | `no` | Show organizer dropdown |
| `show_location_filter` | `no` | Show location text input |
| `show_date_range_filter` | `no` | Show start/end date filters |
| `show_reset_filters` | `yes` | Show reset button |
| `show_search` | `no` | Show search input |
| `event_color` | *(setting)* | Override event hex color |
| `text_color` | *(setting)* | Override text hex color |
| `width` | *(setting)* | Calendar width (e.g., `100%`, `1200px`) |
| `height` | *(setting)* | Calendar height (e.g., `auto`, `700px`) |
| `show_price` | `no` | Show min price in tooltip |
| `show_location` | `no` | Show location in tooltip |
| `show_organizer` | `no` | Show organizer in tooltip |
| `show_recurring_badge` | `yes` | Show recurring/multi-date badge |
| `hide_tooltip` | *(setting)* | Disable tooltip |
| `show_navigation` | `yes` | Show prev/next/today buttons |
| `show_view_switcher` | *(setting)* | Show view switcher buttons |
| `show_year_nav` | *(setting)* | Show prev/next year buttons |
| `show_prev_next` | *(setting)* | Show prev/next month arrows |
| `show_expired_events` | *(setting)* | Show expired events (`yes`/`no`) |
| `expired_event_color` | *(setting)* | Override expired event color |
| `expired_opacity` | *(setting)* | Expired event opacity (`0.3` to `1`) |
| `event_click` | *(setting)* | Click behavior: `navigate`, `tooltip`, `none` |

### `[event-calendar]` (Legacy)

Backward-compatible alias. Now proxies to `[mep-event-calendar]` with default settings.

---

## AJAX Endpoints

### `mep_calendar_get_events` (FullCalendar Event Source)
- **Handler:** `MPWEM_Calendar_Ajax::get_events()`
- **Access:** Public (logged-in & visitors)
- **Purpose:** Returns FullCalendar-compatible JSON event arrays
- **Filters Supported:** category, organizer, tag, city, country, status, event_source, specific_events, search, location_filter, date_start, date_end
- **Event Types Handled:** Single-date, multi-date, recurring/everyday, fallback datetime meta

### `mep_calendar_get_event_stock` (Lazy Stock Loading)
- **Handler:** `MPWEM_Calendar_Ajax::get_event_stock()`
- **Access:** Public
- **Purpose:** Returns ticket-level stock details for tooltips
- **Output:** totalSeats, availableSeats, soldSeats, reservedSeats, ticketTypes[], minPriceHtml

### `mep_gat_event_calender` (List View Toggle)
- **Handler:** `gat_event_calender()` in `inc/mep_functions.php`
- **Access:** Public
- **Purpose:** Returns `[mep-event-calendar]` HTML for the `[events_list]` Calender toggle
- **Improvement:** Now passes current list filters (`cat`, `org`, `tag`, `city`, `country`, `status`, `year`) into the shortcode so the calendar respects the same filters as the list view.

---

## Admin Settings

**Location:** `wp-admin → Events → Calendar Settings`

### Settings Sections

1. **General Settings**
   - Default View, First Day of Week, Show Tooltip, Hide Expired Events
   - Calendar Locale, Width, Height
   - Event Source Filter, Specific Event IDs

2. **Navigation Settings**
   - Show Previous/Next Buttons, Show Year Navigation

3. **Expired Event Settings**
   - Show Expired Events, Expired Event Color, Expired Event Opacity

4. **Color Settings**
   - Event Background, Event Text, Today Highlight, Header Background, Header Text, Cell Border

5. **Weekday Column Styles**
   - Background color per weekday (Sun–Sat)

6. **Day Background Images**
   - Repeater field for background image rules by **weekday** or **exact date**
   - Uses WP Media Uploader

7. **Stock Indicator Colors**
   - Sold Out Color, Low Stock Color, Low Stock Threshold

8. **Shortcode Reference**
   - Complete parameter table with description and example shortcode

### Option Key
- `mep_calendar_settings` — **preserved from addon**. Existing user settings are retained automatically.

---

## Asset Loading Strategy

### Problem Solved
The calendar can be loaded in two ways:
1. **Direct shortcode** on page load — scripts enqueue normally via `wp_footer()`
2. **AJAX toggle** in `[events_list]` — HTML is injected after page load, so scripts must already be present

### Solution
```php
// In mep_cal_enqueue_assets() (hooked to wp_enqueue_scripts)
wp_register_script( 'fullcalendar-js', ... );
wp_register_script( 'mep-calendar-js', ... );
wp_register_style( 'mep-calendar-css', ... );

// Scripts are NOW ENQUEUED globally on all frontend pages
wp_enqueue_style( 'mep-calendar-css' );
wp_enqueue_script( 'mep-calendar-js' );
```

This ensures FullCalendar is always available when the `[events_list]` AJAX toggle fires.

### Dynamic Initialization
An inline `<script>` is appended to every `[mep-event-calendar]` output:
```js
(function() {
    function tryInit() {
        if (typeof mepCalendarInit === 'function') {
            mepCalendarInit();
        } else {
            setTimeout(tryInit, 100);
        }
    }
    tryInit();
})();
```

This polls until `mepCalendarInit` (exposed globally from `calendar.js`) is available, then initializes the calendar immediately — critical for AJAX-injected HTML where `document.ready` has already fired.

---

## AJAX Calendar Toggle in `[events_list]`

### Flow
```
User clicks "Calender" button
        ↓
JS sends AJAX to admin-ajax.php?action=mep_gat_event_calender
        ↓
PHP builds [mep-event-calendar cat="X" org="Y" status="Z" ...] shortcode
        ↓
echo do_shortcode( $shortcode_str ); die();
        ↓
JS inserts response HTML into .mage_grid_box
        ↓
Inline script in HTML detects mepCalendarInit → calls it
        ↓
calendar.js finds .mep-calendar-container → initializes FullCalendar
        ↓
FullCalendar fetches events via AJAX (mep_calendar_get_events)
        ↓
Calendar renders with events
```

### JS Modifications in `assets/frontend/mpwem_script.js`
- Calendar click handler now sends filter params (`cat`, `org`, `tag`, `city`, `country`, `status`, `year`)
- After `target.html(data)`, calls `mepCalendarInit()` as a fallback

---

## Backward Compatibility

| Feature | Status |
|---------|--------|
| `[mep-event-calendar]` shortcode | ✅ Fully preserved |
| `[event-calendar]` shortcode | ✅ Now proxies to FullCalendar |
| `mep_calendar_settings` option | ✅ Same option key — settings retained |
| Admin menu: Calendar Settings | ✅ Same location and slug |
| FullCalendar 6.x | ✅ Same library version (6.1.17) |
| Elementor calendar widget | ✅ Uses `do_shortcode('[event-calendar]')` — works automatically |

---

## Database / Settings Preservation

No database migration is required. The addon stored all settings in:
- `wp_options` → `mep_calendar_settings`

The main plugin now reads from the **exact same option key**, so:
- Colors, views, navigation settings → preserved
- Weekday column colors → preserved
- Day background image rules → preserved
- Stock indicator thresholds → preserved

---

## Testing Checklist

- [ ] `[mep-event-calendar]` renders on a standalone page
- [ ] `[event-calendar]` renders correctly (backward compat)
- [ ] `[events_list]` → click **Calender** → FullCalendar appears
- [ ] `[events_list]` → click **Grid** → returns to grid view
- [ ] `[events_list]` → click **List** → returns to list view
- [ ] Calendar filters (search, category, organizer, location, date range) work
- [ ] Tooltip shows on hover with correct event details
- [ ] Expired events appear faded (or hidden based on settings)
- [ ] Multi-day events span correctly (or split when `split_multi_day="yes"`)
- [ ] Recurring events show on all applicable dates
- [ ] Stock colors change based on availability (sold out = red, low stock = yellow)
- [ ] Admin settings page saves and reflects changes on frontend
- [ ] Day background image rules save correctly
- [ ] Calendar page (if created by addon) still works after addon is deleted
- [ ] Elementor calendar widget renders correctly

---

## Addon Removal Instructions

After confirming everything works:
1. Go to **Plugins → Installed Plugins**
2. **Deactivate** `Woocommerce Event Manager Addon: Calender`
3. **Delete** the addon plugin
4. No further action needed — all data is in the main plugin

---

*End of documentation*
