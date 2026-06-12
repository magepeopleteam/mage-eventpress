<?php
	/**
	 * Plugin Name: Event Booking Manager for WooCommerce
	 * Plugin URI: http://mage-people.com
	 * Description: A Complete Event Solution for WordPress by MagePeople..
	 * Version: 5.3.5
	 * Author: MagePeople Team
	 * Author URI: http://www.mage-people.com/
	 * Text Domain: mage-eventpress
	 * Domain Path: /languages/
	 */
	
	if (!defined('ABSPATH')) { 
		die;
	} // Cannot access pages directly.

	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	if (!defined('MPWEM_PLUGIN_DIR')) {
		define('MPWEM_PLUGIN_DIR', dirname(__FILE__));
	}
	if (!defined('MPWEM_PLUGIN_URL')) {
		define('MPWEM_PLUGIN_URL', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
	}
	if (!defined('MPWEM_PLUGIN_VERSION')) {
		define('MPWEM_PLUGIN_VERSION', '5.3.4');
	}

	if (is_plugin_active('woocommerce-event-manager-addon-recurring-event/recurring_events.php')) {
		deactivate_plugins( '/woocommerce-event-manager-addon-recurring-event/recurring_events.php' );
	}
	if (is_plugin_active('woocommerce-event-manager-addon-global-quantity/global-quantity.php')) {
		deactivate_plugins( '/woocommerce-event-manager-addon-global-quantity/global-quantity.php' );
	}
	if (is_plugin_active('woocommerce-event-manager-addon-early-bird/early-bird.php')) {
		deactivate_plugins( '/woocommerce-event-manager-addon-early-bird/early-bird.php' );
	}

	/**
	 * Set a transient on plugin activation to trigger the
	 * WooCommerce check / redirect on next admin page load.
	 */
	register_activation_hook( __FILE__, 'mpwem_on_plugin_activation' );
	function mpwem_on_plugin_activation() {
		set_transient( 'mpwem_plugin_activated', true, 60 );
	}

	/**
	 * Always load the WooCommerce Installer module in admin.
	 * It handles: activation redirect when WooCommerce IS active,
	 * and shows the beautiful popup when WooCommerce is NOT active.
	 */
	if ( is_admin() ) {
		require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Woo_Installer.php';
	}

	if (is_plugin_active('woocommerce/woocommerce.php')) {
		function appsero_init_tracker_mage_eventpress() {
			if (!class_exists('Appsero\\Client')) {
				require_once __DIR__ . '/lib/appsero/src/Client.php';
			}
			$client = new Appsero\Client('08cd627c-4ed9-49cf-a9b5-1536ec384a5a', 'Event Manager For Woocommerce ', __FILE__);
			$client->insights()->init();
		}

		// add_action('activated_plugin', 'mep_event_activation_redirect');
		require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Dependencies.php';
		require_once MPWEM_PLUGIN_DIR . '/inc/blocks.php';

		// Register block editor assets
		add_action('init', 'mep_register_block_assets');
		function mep_register_block_assets() {
			if (!function_exists('register_block_type')) {
				return;
			}

			// Register block editor script
			wp_register_script(
				'mep-blocks-editor',
				plugins_url('assets/blocks/event-list-block.js', __FILE__),
				array(
					'wp-blocks',
					'wp-i18n',
					'wp-element',
					'wp-editor',
					'wp-components',
					'wp-block-editor'
				),
				MPWEM_PLUGIN_VERSION,
				array('in_footer' => true)
			);

			// Register editor styles
			wp_register_style(
				'mep-blocks-editor',
				plugins_url('assets/blocks/editor.css', __FILE__),
				array('wp-edit-blocks'),
				MPWEM_PLUGIN_VERSION
			);

			// Register front-end styles
			wp_register_style(
				'mep-blocks-style',
				plugins_url('assets/blocks/style.css', __FILE__),
				array(),
				MPWEM_PLUGIN_VERSION
			);

			// Enqueue block editor assets
			if (is_admin()) {
				wp_enqueue_script('mep-blocks-editor');
				//wp_enqueue_style('mep-blocks-editor');
			}
		}

		// Added Settings link to plugin action links
		add_filter('plugin_action_links', 'mep_plugin_action_link', 10, 2);
		function mep_plugin_action_link($links_array, $plugin_file_name) {
			if (strpos($plugin_file_name, basename(__FILE__))) {
				array_unshift($links_array, '<a href="' . esc_url(admin_url()) . 'edit.php?post_type=mep_events&page=mep_event_settings_page">' . __('Settings', 'mage-eventpress') . '</a>');
			}
			return $links_array;
		}
		// Added links to plugin row meta
		add_filter('plugin_row_meta', 'mep_plugin_row_meta', 10, 2);
		function mep_plugin_row_meta($links_array, $plugin_file_name) {
			if (strpos($plugin_file_name, basename(__FILE__))) {
				if (!is_plugin_active('woocommerce-event-manager-pdf-ticket/tickets.php') || !is_plugin_active('woocommerce-event-manager-addon-form-builder/addon-builder.php')) {
					$wbbm_links = array(
						'docs' => '<a href="' . esc_url("https://docs.mage-people.com/woocommerce-event-manager/") . '" target="_blank">' . __('Docs', 'mage-eventpress') . '</a>',
						'support' => '<a href="' . esc_url("https://mage-people.com/my-account") . '" target="_blank">' . __('Support', 'mage-eventpress') . '</a>',
						'get_pro' => '<a href="' . esc_url("https://mage-people.com/product/mage-woo-event-booking-manager-pro/") . '" target="_blank" class="mep_plugin_pro_meta_link">' . __('Upgrade to PRO Version', 'mage-eventpress') . '</a>'
					);
				}
				else {
					$wbbm_links = array(
						'docs' => '<a href="' . esc_url("https://docs.mage-people.com/woocommerce-event-manager/") . '" target="_blank">' . __('Docs', 'mage-eventpress') . '</a>',
						'support' => '<a href="' . esc_url("https://mage-people.com/my-account") . '" target="_blank">' . __('Support', 'mage-eventpress') . '</a>'
					);
				}
				$links_array = array_merge($links_array, $wbbm_links);
			}
			return $links_array;
		}
	}
	else {
		require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Global_Function.php';
		require_once MPWEM_PLUGIN_DIR . '/inc/MPWEM_Global_Style.php';
		require_once MPWEM_PLUGIN_DIR . '/admin/MPWEM_Quick_Setup.php';
	}
	remove_action( 'admin_init', 'mep_re_meta_boxs',200);

/**
 * Grant WooCommerce Shop Managers access to Events plugin settings and menus.
 * Only runs when PRO version is not active to avoid conflicts.
 */
if ( ! function_exists( 'mep_pro_modify_admin_menu_capabilities' ) ) {
	add_action( 'admin_menu', 'mep_modify_admin_menu_capabilities', 999 );
	function mep_modify_admin_menu_capabilities() {
		global $menu, $submenu;

		if ( ! empty( $menu ) ) {
			foreach ( $menu as $key => $item ) {
				if ( isset( $item[2] ) && $item[2] === 'mep_events' ) {
					if ( isset( $item[1] ) && $item[1] === 'manage_options' ) {
						$menu[ $key ][1] = 'manage_woocommerce';
					}
				}
			}
		}

		$parents_to_modify = array( 'edit.php?post_type=mep_events', 'mep_events' );
		foreach ( $parents_to_modify as $parent ) {
			if ( isset( $submenu[ $parent ] ) ) {
				foreach ( $submenu[ $parent ] as $key => $sub_item ) {
					if ( isset( $sub_item[1] ) && $sub_item[1] === 'manage_options' ) {
						$submenu[ $parent ][ $key ][1] = 'manage_woocommerce';
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'mep_pro_grant_shop_manager_access' ) ) {
	add_filter( 'user_has_cap', 'mep_grant_shop_manager_access', 10, 4 );
	function mep_grant_shop_manager_access( $allcaps, $caps, $args, $user ) {
		// Fast exit: this filter only matters for manage_options checks.
		if ( ! isset( $args[0] ) || $args[0] !== 'manage_options' ) {
			return $allcaps;
		}
		// Fast exit: only shop managers need elevation — skip everyone else immediately.
		if ( empty( $allcaps['manage_woocommerce'] ) ) {
			return $allcaps;
		}

		// Cache the context result per user per request so the string comparisons
		// below run at most ONCE per user rather than on every capability check.
		static $mep_cap_cache = array();
		$uid = isset( $user->ID ) ? (int) $user->ID : 0;
		if ( isset( $mep_cap_cache[ $uid ] ) ) {
			if ( $mep_cap_cache[ $uid ] ) {
				$allcaps['manage_options'] = true;
			}
			return $allcaps;
		}

		$is_eventpress_context = false;

		if ( is_admin() ) {
			global $pagenow;

			if ( isset( $_GET['page'] ) && is_string( $_GET['page'] ) ) {
				$page = $_GET['page'];
				if (
					strpos( $page, 'mep_' ) === 0 ||
					strpos( $page, 'mpwem_' ) === 0 ||
					$page === 'attendee_list' ||
					$page === 'mep_event_welcome_page' ||
					$page === 'mpwem_quick_setup'
				) {
					$is_eventpress_context = true;
				}
			}

			if ( isset( $_GET['post_type'] ) && ( $_GET['post_type'] === 'mep_events' || $_GET['post_type'] === 'mep_event_speaker' ) ) {
				$is_eventpress_context = true;
			}

			if ( $pagenow === 'post.php' && isset( $_GET['post'] ) ) {
				$post_id = absint( $_GET['post'] );
				if ( $post_id && get_post_type( $post_id ) === 'mep_events' ) {
					$is_eventpress_context = true;
				}
			}

			if ( $pagenow === 'options.php' && isset( $_POST['option_page'] ) && is_string( $_POST['option_page'] ) ) {
				$option_page = $_POST['option_page'];
				if (
					strpos( $option_page, 'mep_' ) === 0 ||
					strpos( $option_page, 'mpwem_' ) === 0 ||
					strpos( $option_page, 'general_setting_sec' ) === 0 ||
					strpos( $option_page, 'event_list_setting_sec' ) === 0 ||
					strpos( $option_page, 'single_event_setting_sec' ) === 0 ||
					strpos( $option_page, 'email_setting_sec' ) === 0 ||
					strpos( $option_page, 'style_setting_sec' ) === 0 ||
					strpos( $option_page, 'icon_setting_sec' ) === 0 ||
					strpos( $option_page, 'carousel_setting_sec' ) === 0 ||
					strpos( $option_page, 'mp_slider_settings' ) === 0 ||
					strpos( $option_page, 'mep_settings_licensing' ) === 0
				) {
					$is_eventpress_context = true;
				}
			}
		}

		if ( wp_doing_ajax() ) {
			if ( isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] ) ) {
				$action = $_REQUEST['action'];
				if (
					strpos( $action, 'mep_' ) === 0 ||
					strpos( $action, 'mpwem_' ) === 0 ||
					strpos( $action, 'wbtm_' ) === 0 ||
					strpos( $action, 'wtbm_' ) === 0 ||
					strpos( $action, 'wbbm_' ) === 0 ||
					$action === 'generate_attendee_pdf'
				) {
					$is_eventpress_context = true;
				}
			}
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				if ( stripos( $_SERVER['REQUEST_URI'], '/wp-json/mep/' ) !== false || stripos( $_SERVER['REQUEST_URI'], '/wp-json/mpwem/' ) !== false ) {
					$is_eventpress_context = true;
				}
			}
		}

		// Store result so all subsequent capability checks in this request are instant.
		$mep_cap_cache[ $uid ] = $is_eventpress_context;

		if ( $is_eventpress_context ) {
			$allcaps['manage_options'] = true;
		}

		return $allcaps;
	}
}