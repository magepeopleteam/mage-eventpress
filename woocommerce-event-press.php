<?php
/**
 * Plugin Name: Woocommerce Events Manager
 * Plugin URI: http://mage-people.com
 * Description: A Complete Event Solution for WordPress by MagePeople..
 * Version: 3.3.1
 * Author: MagePeople Team
 * Author URI: http://www.mage-people.com/
 * Text Domain: mage-eventpress
 * Domain Path: /languages/
 * WC requires at least: 3.0.9
 * WC tested up to: 4.2.0* 
 */

if (!defined('ABSPATH')) {
  die;
} // Cannot access pages directly.

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (is_plugin_active('woocommerce/woocommerce.php')) {
  function appsero_init_tracker_mage_eventpress() {
    if ( ! class_exists( 'Appsero\Client' ) ) {
      require_once __DIR__ . '/lib/appsero/src/Client.php';
    }
    $client = new Appsero\Client( '08cd627c-4ed9-49cf-a9b5-1536ec384a5a', 'WooCommerce Event Manager', __FILE__ );    
    $client->insights()->init();
}  
  require_once(dirname(__FILE__) . "/inc/mep_file_include.php");
} else {
  function mep_admin_notice_wc_not_active()
  {
    $class = 'notice notice-error';
    printf(
      '<div class="error" style="background:red; color:#fff;"><p>%s</p></div>',
      __('You Must Install WooCommerce Plugin before activating WooCommerce Event Manager, Becuase It is dependent on Woocommerce Plugin')
    );
  }
  add_action('admin_notices', 'mep_admin_notice_wc_not_active');
}