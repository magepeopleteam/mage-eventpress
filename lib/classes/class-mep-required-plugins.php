<?php
/**
 *  Required Plugins Notification
 *  Dev: Ariful
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if (!class_exists('MEP_Required_Plugins')) {

class MEP_Required_Plugins
{
	public function __construct() {
		add_action( 'admin_notices',array($this,'mep_admin_notices'));
		add_action( 'admin_init', array( $this, 'mep_plugin_activate' ) );
	}
	public function mep_chk_plugin_folder_exist($slug){
		$plugin_dir = ABSPATH . 'wp-content/plugins/'.$slug;
		if(is_dir($plugin_dir)){
			return true;
		}
		else{
			return false;
		}		
	}

	public function mep_requested_plugin_install() {
		if (
			is_admin() &&
			current_user_can('install_plugins') &&
			isset($_GET['mep_plugin_install'], $_GET['_wpnonce']) &&
			wp_verify_nonce($_GET['_wpnonce'], 'mep_plugin_install')
		) {
			$slug = sanitize_text_field($_GET['mep_plugin_install']);

			if (!$this->mep_chk_plugin_folder_exist($slug)) {
				$action = 'install-plugin';
				$url = wp_nonce_url(
					add_query_arg(
						array(
							'action' => $action,
							'plugin' => $slug,
						),
						admin_url('update.php')
					),
					$action . '_' . $slug
				);

				echo '<script>
					var url = "' . esc_js($url) . '";
					url = url.replace(/&amp;/g, "&");
					window.location.replace(url);
				</script>';
				exit;
			}
		}
	}


	public function mep_plugin_activate() {
		if (
			is_admin() &&
			current_user_can('activate_plugins') &&
			isset($_GET['mep_plugin_activate'], $_GET['_wpnonce']) &&
			wp_verify_nonce($_GET['_wpnonce'], 'mep_plugin_activate')
		) {
			$slug = sanitize_text_field($_GET['mep_plugin_activate']);

			// Optional: whitelist allowed plugins
			$allowed_plugins = array(
				'mage-eventpress/mage-eventpress.php',
			);

			if (in_array($slug, $allowed_plugins)) {
				$activate = activate_plugin($slug);
			}

			// Redirect to plugins page
			$url = admin_url('plugins.php');
			echo '<script>
				window.location.replace("' . esc_url($url) . '");
			</script>';
			exit;
		}
	}


	public function mep_admin_notices(){		
        $slug = 'woocommerce';
        $style = 'background: #f8d7da; font-size: 13px; color: #721c24;';
		if (!$this->mep_chk_plugin_folder_exist($slug)) {
			// Create a secure nonce
			$nonce = wp_create_nonce('mep_plugin_install');
			$url = admin_url('plugins.php') . '?mep_plugin_install=' . urlencode($slug) . '&_wpnonce=' . $nonce;

			// Generate install button with secure URL
			$wc_btn = '<a href="' . esc_url($url) . '">Install Now</a>';

			printf(
				'<div class="error" style="%s"><p><strong>%s</strong> %s</p></div>',
				esc_attr($style),
				esc_html__('You must install the WooCommerce plugin before activating Event Manager For WooCommerce. Because it is dependent on the WooCommerce plugin.', 'mage-eventpress'),
				$wc_btn
			);

			// Optional: auto-initiate installation in some conditions
			$this->mep_requested_plugin_install();
		}
        elseif($this->mep_chk_plugin_folder_exist($slug) == true && !is_plugin_active( 'woocommerce/woocommerce.php')){

			$plugin = 'woocommerce/woocommerce.php';
			$nonce = wp_create_nonce('mep_plugin_activate');
			$url = admin_url('plugins.php') . '?mep_plugin_activate=' . urlencode($plugin) . '&_wpnonce=' . $nonce;

			$wc_btn = '<a href="' . esc_url($url) . '">Activate Now</a>';

			printf(
				'<div class="error" style="%s"><p><strong>%s</strong> %s</p></div>',
				esc_attr($style),
				esc_html__('You must activate the WooCommerce plugin before activating Event Manager For WooCommerce. Because it is dependent on the WooCommerce plugin.', 'mage-eventpress'),
				$wc_btn
			);


            }
        else{
          return false;
        }			
	}

}
}
new MEP_Required_Plugins();
