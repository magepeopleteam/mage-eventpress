<?php
	/**
	 * Plugin Name: Event Booking Manager for WooCommerce
	 * Plugin URI: http://mage-people.com
	 * Description: A Complete Event Solution for WordPress by MagePeople..
	 * Version: 5.3.3
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

	// WooCommerce Fallback Stub Functions to prevent Fatal Errors when WooCommerce is inactive.
	// We hook this to plugins_loaded so that WooCommerce (if active or being activated) has loaded first,
	// preventing any redeclaration conflicts.
	add_action( 'plugins_loaded', 'mpwem_define_woocommerce_fallbacks', 1 );
	function mpwem_define_woocommerce_fallbacks() {
		if ( class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Detect if WooCommerce is being activated during this request to avoid redeclaration conflicts
		$is_activating = false;
		if ( isset( $GLOBALS['mpwem_activating_woocommerce'] ) && $GLOBALS['mpwem_activating_woocommerce'] ) {
			$is_activating = true;
		}
		if ( ! $is_activating && ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) || isset( $_SERVER['argv'] ) ) ) {
			// Web activation check (single or bulk)
			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'activate' ) {
				if ( isset( $_REQUEST['plugin'] ) && strpos( $_REQUEST['plugin'], 'woocommerce.php' ) !== false ) {
					$is_activating = true;
				}
				if ( isset( $_REQUEST['checked'] ) && is_array( $_REQUEST['checked'] ) ) {
					foreach ( $_REQUEST['checked'] as $checked_plugin ) {
						if ( strpos( $checked_plugin, 'woocommerce.php' ) !== false ) {
							$is_activating = true;
							break;
						}
					}
				}
			}
			// CLI / script activation check
			if ( ! $is_activating && isset( $_SERVER['argv'] ) && is_array( $_SERVER['argv'] ) ) {
				foreach ( $_SERVER['argv'] as $arg ) {
					if ( strpos( $arg, 'woocommerce' ) !== false ) {
						$is_activating = true;
						break;
					}
				}
			}
		}

		if ( ! $is_activating ) {
			if ( ! class_exists( 'MPWEM_WC_Cart_Fallback' ) ) {
				class MPWEM_WC_Cart_Fallback {
					public function get_cart() { return array(); }
					public function empty_cart() {}
				}
			}
			if ( ! class_exists( 'MPWEM_WC_Customer_Fallback' ) ) {
				class MPWEM_WC_Customer_Fallback {
					public function get_is_vat_exempt() { return false; }
				}
			}
			if ( ! class_exists( 'MPWEM_WC_Fallback' ) ) {
				class MPWEM_WC_Fallback {
					public $cart;
					public $customer;
					public $version = '0.0.0';
					public function __construct() {
						$this->cart = new MPWEM_WC_Cart_Fallback();
						$this->customer = new MPWEM_WC_Customer_Fallback();
					}
				}
			}
			if ( ! function_exists( 'WC' ) ) {
				function WC() {
					static $instance = null;
					if ( null === $instance ) {
						$instance = new MPWEM_WC_Fallback();
					}
					return $instance;
				}
			}
			if ( ! function_exists( 'wc_get_orders' ) ) {
				function wc_get_orders( $args = array() ) { return array(); }
			}
			if ( ! function_exists( 'wc_get_order' ) ) {
				function wc_get_order( $order_id ) { return false; }
			}
			if ( ! function_exists( 'wc_get_product' ) ) {
				function wc_get_product( $product_id ) { return false; }
			}
			if ( ! function_exists( 'wc_price' ) ) {
				function wc_price( $price, $args = array() ) {
					$currency_symbol = get_option( 'woocommerce_currency_symbol', '$' );
					return $currency_symbol . number_format( (float) $price, 2 );
				}
			}
			if ( ! function_exists( 'get_woocommerce_currency' ) ) {
				function get_woocommerce_currency() { return 'USD'; }
			}
			if ( ! function_exists( 'get_woocommerce_currency_symbol' ) ) {
				function get_woocommerce_currency_symbol( $currency = 'USD' ) { return '$'; }
			}
			if ( ! function_exists( 'wc_prices_include_tax' ) ) {
				function wc_prices_include_tax() { return false; }
			}
			if ( ! function_exists( 'wc_get_price_thousand_separator' ) ) {
				function wc_get_price_thousand_separator() { return ','; }
			}
			if ( ! function_exists( 'wc_get_price_decimal_separator' ) ) {
				function wc_get_price_decimal_separator() { return '.'; }
			}
			if ( ! function_exists( 'is_woocommerce' ) ) {
				function is_woocommerce() { return false; }
			}
			if ( ! function_exists( 'is_product' ) ) {
				function is_product() { return false; }
			}
			if ( ! function_exists( 'wc_get_cart_url' ) ) {
				function wc_get_cart_url() { return ''; }
			}
			if ( ! function_exists( 'wc_get_checkout_url' ) ) {
				function wc_get_checkout_url() { return ''; }
			}
		}
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
			filemtime(plugin_dir_path(__FILE__) . 'assets/blocks/event-list-block.js'),
			array('in_footer' => true)
		);

		// Register editor styles
		wp_register_style(
			'mep-blocks-editor',
			plugins_url('assets/blocks/editor.css', __FILE__),
			array('wp-edit-blocks'),
			filemtime(plugin_dir_path(__FILE__) . 'assets/blocks/editor.css')
		);

		// Register front-end styles
		wp_register_style(
			'mep-blocks-style',
			plugins_url('assets/blocks/style.css', __FILE__),
			array(),
			filemtime(plugin_dir_path(__FILE__) . 'assets/blocks/style.css')
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
	remove_action( 'admin_init', 'mep_re_meta_boxs',200);