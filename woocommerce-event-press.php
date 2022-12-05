<?php
/**
 * Plugin Name: Event Manager and Tickets Selling Plugin for WooCommerce
 * Plugin URI: http://mage-people.com
 * Description: A Complete Event Solution for WordPress by MagePeople..
 * Version: 3.7.8
 * Author: MagePeople Team
 * Author URI: http://www.mage-people.com/
 * Text Domain: mage-eventpress
 * Domain Path: /languages/
 * WC requires at least: 3.0.9
 * WC tested up to: 6.5
 */

if (!defined('ABSPATH')) {
  die;
} // Cannot access pages directly.

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (is_plugin_active('woocommerce/woocommerce.php')) {
  function appsero_init_tracker_mage_eventpress()
  {
    if (!class_exists('Appsero\Client')) {
      require_once __DIR__ . '/lib/appsero/src/Client.php';
    }
    $client = new Appsero\Client('08cd627c-4ed9-49cf-a9b5-1536ec384a5a', 'Event Manager For Woocommerce ', __FILE__);
    $client->insights()->init();
  }

  function mep_event_activation_redirect($plugin)
  {
    $check_quick_setup = get_option('mep_quick_setup') ? get_option('mep_quick_setup') : 'no-done';

    $slug = $check_quick_setup == 'done' ? 'edit.php?post_type=mep_events&page=mep_event_welcome_page' : 'edit.php?post_type=mep_events&page=mep_event_quick_setup_page';
    if ($plugin == plugin_basename(__FILE__)) {
      exit(wp_redirect(admin_url($slug)));
    }
  }
  // add_action('activated_plugin', 'mep_event_activation_redirect');
  require_once(dirname(__FILE__) . "/inc/mep_file_include.php");

    // Get Plugin Data
    if(!function_exists('mep_get_plugin_data')) {
      function mep_get_plugin_data($data) {
        $get_mep_plugin_data = get_plugin_data( __FILE__ );
        $mep_data = $get_mep_plugin_data[$data];
        return $mep_data;
      }
    }
  
  // Added Settings link to plugin action links
  add_filter( 'plugin_action_links', 'mep_plugin_action_link', 10, 2 );
  
  function mep_plugin_action_link( $links_array, $plugin_file_name ){
  
    if( strpos( $plugin_file_name, basename(__FILE__) ) ) {
  
      array_unshift( $links_array, '<a href="'.esc_url(admin_url()).'edit.php?post_type=mep_events&page=mep_event_settings_page">'.__('Settings','mage-eventpress').'</a>');
    }
    
    return $links_array;
  }
  
  // Added links to plugin row meta
  add_filter( 'plugin_row_meta', 'mep_plugin_row_meta', 10, 2 );
   
  function mep_plugin_row_meta( $links_array, $plugin_file_name ) {
  
      if( strpos( $plugin_file_name, basename(__FILE__) ) ) {
  
          if(!is_plugin_active('woocommerce-event-manager-pdf-ticket/tickets.php') || !is_plugin_active('woocommerce-event-manager-addon-form-builder/addon-builder.php')){
              $wbbm_links = array(
                  'docs'    => '<a href="'.esc_url("https://docs.mage-people.com/woocommerce-event-manager/").'" target="_blank">'.__('Docs','mage-eventpress').'</a>',
                  'support' => '<a href="'.esc_url("https://mage-people.com/my-account").'" target="_blank">'.__('Support','mage-eventpress').'</a>',
                  'get_pro' => '<a href="'.esc_url("https://mage-people.com/product/mage-woo-event-booking-manager-pro/").'" target="_blank" class="mep_plugin_pro_meta_link">'.__('Upgrade to PRO Version','mage-eventpress').'</a>'             
                  );            
          }else{
              $wbbm_links = array(
                  'docs'    => '<a href="'.esc_url("https://docs.mage-people.com/woocommerce-event-manager/").'" target="_blank">'.__('Docs','mage-eventpress').'</a>',
                  'support' => '<a href="'.esc_url("https://mage-people.com/my-account").'" target="_blank">'.__('Support','mage-eventpress').'</a>'            
                  );            
          }        
          $links_array = array_merge( $links_array, $wbbm_links );
      }
       
      return $links_array;
  }

} else {


  add_action('admin_enqueue_scripts', 'mep_add_admin_scripts', 10, 1);
  function mep_add_admin_scripts($hook)
  {

  
    wp_register_script('welcome-tabs', plugin_dir_url(__FILE__) . 'js/welcome-tabs.js', array('jquery'));
    wp_register_style('welcome-tabs', plugin_dir_url(__FILE__) . 'css/welcome-tabs.css');
  
    wp_register_script('welcome-tabs', plugin_dir_url(__FILE__) . 'js/welcome-tabs.js', array('jquery'));
    wp_register_style('welcome-tabs', plugin_dir_url(__FILE__) . 'css/welcome-tabs.css');
  }



  add_action('admin_menu', 'mep_event_welcome_admin_menu');
  function mep_event_welcome_admin_menu(){  
    add_menu_page(__( 'Events', 'mage-eventpress' ), __( 'Events', 'mage-eventpress' ), 'manage_options', 'mep_events','mep_event_quick_setup_page','dashicons-calendar-alt', 6); 
    add_submenu_page('mep_events', __('Quick Setup', 'mage-eventpress'), __('<span style="color:#10dd10">Quick Setup</span>', 'mage-eventpress'), 'manage_options', 'mep_event_quick_setup_page',  'mep_event_quick_setup_page');
  }
    
  function mep_event_quick_setup_page(){  
    require_once(dirname(__FILE__) . "/inc/quick_setup.php");
  }

  function mep_no_woo_event_activation_redirect($plugin)
  {
    // $check_quick_setup = get_option('mep_quick_setup') ? get_option('mep_quick_setup') : 'no-done';

    $slug = 'admin.php?page=mep_event_quick_setup_page';
    if ($plugin == plugin_basename(__FILE__)) {
      exit(wp_redirect(admin_url($slug)));
    }
  }
  add_action('activated_plugin', 'mep_no_woo_event_activation_redirect');
  
}