<?php
/**
 * Google Maps Enhancement Fix for Mage EventPress
 * Professional solution for Google Maps integration issues
 * 
 * @version 1.0.0
 * @author MagePeople Team
 */

if (!defined('ABSPATH')) {
    die;
}

class MEP_GoogleMaps_Fix {
    
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_footer', array($this, 'add_admin_inline_styles'));
        
        // Fix coordinate saving
        add_action('save_post', array($this, 'ensure_coordinate_save'), 20, 2);
        
        // Add AJAX handlers for coordinate validation
        add_action('wp_ajax_mep_validate_coordinates', array($this, 'ajax_validate_coordinates'));
        add_action('wp_ajax_nopriv_mep_validate_coordinates', array($this, 'ajax_validate_coordinates'));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        // Only load on event edit pages
        if ($post_type === 'mep_events' || strpos($hook, 'mep_event') !== false) {
            
            // Enqueue enhanced JavaScript
            wp_enqueue_script(
                'mep-google-maps-enhanced',
                MPWEM_PLUGIN_URL . '/assets/admin/mep-google-maps-enhanced.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            // Enqueue enhanced CSS
            wp_enqueue_style(
                'mep-google-maps-enhanced',
                MPWEM_PLUGIN_URL . '/assets/admin/mep-google-maps-enhanced.css',
                array(),
                '1.0.0'
            );
            
            // Localize script with AJAX URL and nonce
            wp_localize_script('mep-google-maps-enhanced', 'mep_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mep_coordinate_nonce'),
                'messages' => array(
                    'coordinate_saved' => __('Coordinates saved successfully', 'mage-eventpress'),
                    'coordinate_error' => __('Error saving coordinates', 'mage-eventpress'),
                    'geocoding_failed' => __('Location not found', 'mage-eventpress'),
                    'api_error' => __('Google Maps API error', 'mage-eventpress')
                )
            ));
        }
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if (is_singular('mep_events') || is_post_type_archive('mep_events')) {
            wp_enqueue_style(
                'mep-google-maps-enhanced-frontend',
                MPWEM_PLUGIN_URL . '/assets/css/mep-google-maps-enhanced.css',
                array(),
                '1.0.0'
            );
        }
    }
    
    /**
     * Add admin inline styles for better integration
     */
    public function add_admin_inline_styles() {
        global $post_type;
        
        if ($post_type === 'mep_events') {
            ?>
            <style>
                /* Ensure maps display properly in admin */
                .mp_form_area .mep_google_map {
                    width: 100% !important;
                    height: 400px !important;
                    display: block !important;
                }
                
                /* Fix for WordPress admin conflicts */
                .mp_form_area #pac-input {
                    width: 100% !important;
                    max-width: 400px !important;
                    margin: 10px 0 !important;
                }
                
                /* Loading state */
                .mep-map-loading {
                    background: #f9f9f9;
                    text-align: center;
                    padding: 50px;
                    color: #666;
                }
            </style>
            <?php
        }
    }

    /**
     * Ensure coordinates are properly saved
     */
    public function ensure_coordinate_save($post_id, $post) {
        // Check if this is an event post
        if ($post->post_type !== 'mep_events') {
            return;
        }
        
        // Check if we have coordinates to save
        if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
            $latitude = sanitize_text_field($_POST['latitude']);
            $longitude = sanitize_text_field($_POST['longitude']);
            
            // Validate coordinates
            if ($this->validate_coordinates($latitude, $longitude)) {
                update_post_meta($post_id, 'latitude', $latitude);
                update_post_meta($post_id, 'longitude', $longitude);
                
                // Log successful save
                error_log("MEP: Coordinates saved for event {$post_id}: {$latitude}, {$longitude}");
            } else {
                // Clear invalid coordinates
                delete_post_meta($post_id, 'latitude');
                delete_post_meta($post_id, 'longitude');
                
                error_log("MEP: Invalid coordinates cleared for event {$post_id}");
            }
        }
    }
    
    /**
     * Validate coordinate values
     */
    private function validate_coordinates($lat, $lng) {
        // Check if values are numeric
        if (!is_numeric($lat) || !is_numeric($lng)) {
            return false;
        }
        
        $lat = floatval($lat);
        $lng = floatval($lng);
        
        // Check coordinate ranges
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return false;
        }
        
        // Check if coordinates are not zero (unless specifically set)
        if ($lat == 0 && $lng == 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * AJAX handler for coordinate validation
     */
    public function ajax_validate_coordinates() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mep_coordinate_nonce')) {
            wp_die('Security check failed');
        }
        
        $latitude = sanitize_text_field($_POST['latitude']);
        $longitude = sanitize_text_field($_POST['longitude']);
        
        $is_valid = $this->validate_coordinates($latitude, $longitude);
        
        wp_send_json(array(
            'success' => $is_valid,
            'message' => $is_valid ? 
                __('Coordinates are valid', 'mage-eventpress') : 
                __('Invalid coordinates', 'mage-eventpress')
        ));
    }
    
    /**
     * Get saved coordinates for an event
     */
    public static function get_event_coordinates($event_id) {
        $lat = get_post_meta($event_id, 'latitude', true);
        $lng = get_post_meta($event_id, 'longitude', true);
        
        if (empty($lat) || empty($lng)) {
            return false;
        }
        
        return array(
            'latitude' => floatval($lat),
            'longitude' => floatval($lng)
        );
    }
    
    /**
     * Check if Google Maps API key is configured
     */
    public static function has_api_key() {
        $api_key = mep_get_option('google_map_api', 'general_setting_sec', '');
        return !empty($api_key);
    }
    
    /**
     * Get Google Maps API key
     */
    public static function get_api_key() {
        return mep_get_option('google_map_api', 'general_setting_sec', '');
    }
}

// Initialize the fix
new MEP_GoogleMaps_Fix();

/**
 * Helper function to get coordinates for templates
 */
function mep_get_event_coordinates($event_id) {
    return MEP_GoogleMaps_Fix::get_event_coordinates($event_id);
}

/**
 * Helper function to check if maps are available
 */
function mep_has_google_maps() {
    return MEP_GoogleMaps_Fix::has_api_key();
}
