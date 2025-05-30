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

	public function mep_requested_plugin_install(){

		if(isset($_GET['mep_plugin_install']) && $this->mep_chk_plugin_folder_exist($_GET['mep_plugin_install']) == false){
			$slug = $_GET['mep_plugin_install'];
				$action = 'install-plugin';
				$url = wp_nonce_url(
					add_query_arg(
						array(
							'action' => $action,
							'plugin' => $slug
						),
						admin_url( 'update.php' )
					),
					$action.'_'.$slug
				);
				if(isset($url)){
					echo '<script>
						str = "'.$url.'";
						var url = str.replace(/&amp;/g, "&");
						window.location.replace(url);
						</script>';
				}
		}
		else{
			return false;
		}
	}

	public function mep_plugin_activate(){
		// Only allow users with the right capability
		if (!current_user_can('activate_plugins')) {
			return false;
		}
		// Check required params and nonce
		if (isset($_GET['mep_plugin_activate'], $_GET['_mep_nonce'])) {
			$slug = $_GET['mep_plugin_activate'];
			$allowed_plugins = array('woocommerce/woocommerce.php'); // Whitelist
			if (!in_array($slug, $allowed_plugins, true)) {
				return false;
			}
			if (!wp_verify_nonce($_GET['_mep_nonce'], 'mep_activate_plugin_' . $slug)) {
				return false;
			}
			if (!is_plugin_active($slug)) {
				$activate = activate_plugin($slug);
				$url = admin_url('plugins.php');
				echo '<script>var url = "' . esc_js($url) . '"; window.location.replace(url);</script>';
			}
		}
		return false;
	}

	public function mep_admin_notices(){		
        $slug = 'woocommerce';
        $style = 'background: #f8d7da; font-size: 13px; color: #721c24;';
        if( $this->mep_chk_plugin_folder_exist($slug) == false ){
            $this->mep_requested_plugin_install($slug);  
            $wc_btn = '<a href="'.admin_url('plugins.php').'?mep_plugin_install='.$slug.'">Install Now</a>';
            printf(
                '<div class="error" style="'.$style.'"><p><strong>%s</strong> '.$wc_btn.'</p></div>',
                __('You must install the WooCommerce plugin before activating Event Manager For WooCommerce. Because it is dependent on the WooCommerce plugin.','mage-eventpress')
            );
        }
        elseif($this->mep_chk_plugin_folder_exist($slug) == true && !is_plugin_active( 'woocommerce/woocommerce.php')){
            $plugin = 'woocommerce/woocommerce.php';
            $nonce = wp_create_nonce('mep_activate_plugin_' . $plugin);
            $url = admin_url('plugins.php').'?mep_plugin_activate='.$plugin.'&_mep_nonce='.$nonce;
            $wc_btn = '<a href="'.esc_url($url).'">Activate Now</a>';
            printf(
                '<div class="error" style="'.$style.'"><p><strong>%s</strong> '.$wc_btn.'</p></div>',
                __('You must activate the WooCommerce plugin before activating Event Manager For WooCommerce. Because it is dependent on the WooCommerce plugin.','mage-eventpress')
            );
            }
        else{
          return false;
        }			
	}

}
}
new MEP_Required_Plugins();
