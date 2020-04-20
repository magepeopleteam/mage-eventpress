<?php
/**
* Plugin Name: Woocommerce Events Manager
* Plugin URI: http://mage-people.com
* Description: A Complete Event Solution for WordPress by MagePeople..
* Version: 3.2.3
* Author: MagePeople Team
* Author URI: http://www.mage-people.com/
* Text Domain: mage-eventpress
* Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

  require_once(dirname(__FILE__) . "/inc/mep_file_include.php");

}else{
function mep_admin_notice_wc_not_active() {
  $class = 'notice notice-error';
    printf(
      '<div class="error" style="background:red; color:#fff;"><p>%s</p></div>',
      __('You Must Install WooCommerce Plugin before activating WooCommerce Event Manager, Becuase It is dependent on Woocommerce Plugin')
    );
}
add_action( 'admin_notices', 'mep_admin_notice_wc_not_active' );
}