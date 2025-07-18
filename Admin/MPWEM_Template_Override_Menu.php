<?php
/**
 * Template Override Menu Class
 * 
 * Handles the admin menu registration for template override functionality.
 * Provides secure access to template override settings page.
 * 
 * @package MageEventPress
 * @subpackage Admin
 * @author engr.sumonazma@gmail.com
 * @copyright mage-people.com
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('MPWEM_Template_Override_Menu')) {
	/**
	 * MPWEM_Template_Override_Menu Class
	 * 
	 * Manages admin menu for template override functionality with security measures.
	 */
	class MPWEM_Template_Override_Menu {
		/**
		 * Constructor
		 * 
		 * Initializes the menu class and registers admin menu action.
		 */
		public function __construct() {
			add_action('admin_menu', array($this, 'add_template_override_menu'));
		}

		/**
		 * Add template override menu to WordPress admin
		 * 
		 * Registers a submenu page under Events with proper capability checks.
		 * Only users with 'manage_options' capability can access this page.
		 */
		public function add_template_override_menu() {
			// Only add menu if user has proper capabilities
			if (!current_user_can('manage_options')) {
				return;
			}
			
			add_submenu_page(
				'edit.php?post_type=mep_events',
				esc_html__('Template Override', 'mage-eventpress'),
				esc_html__('Template Override', 'mage-eventpress'),
				'manage_options',
				'mep_template_override',
				array($this, 'template_override_page')
			);
		}

		/**
		 * Render the template override page
		 * 
		 * Displays the template override interface with proper security checks.
		 * Verifies user capabilities before rendering content.
		 */
		public function template_override_page() {
			// Security check: verify user capabilities
			if (!current_user_can('manage_options')) {
				wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mage-eventpress'));
			}
			
			// Verify nonce for additional security (if coming from form submission)
			if (isset($_POST['submit']) && (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'mep_template_override_page'))) {
				wp_die(esc_html__('Security check failed.', 'mage-eventpress'));
			}
			
			echo '<div class="wrap">';
			echo '<h1>' . esc_html__('Template Override System', 'mage-eventpress') . '</h1>';
			
			// Check if required class exists
			if (class_exists('MPWEM_Template_Override_Settings')) {
				try {
					$template_override = new MPWEM_Template_Override_Settings();
					$template_override->template_override_settings_page();
				} catch (Exception $e) {
					echo '<div class="notice notice-error">';
					echo '<p>' . esc_html__('Error loading template override settings.', 'mage-eventpress') . '</p>';
					echo '</div>';
					// Log error for debugging (don't expose to user)
					error_log('MPWEM Template Override Error: ' . $e->getMessage());
				}
			} else {
				echo '<div class="notice notice-error">';
				echo '<p>' . esc_html__('Template Override Settings class not found. Please ensure the plugin is properly installed.', 'mage-eventpress') . '</p>';
				echo '</div>';
			}
			
			echo '</div>';
		}
	}
	new MPWEM_Template_Override_Menu();
}