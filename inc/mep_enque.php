<?php
if (!defined('ABSPATH')) {
  die;
} // Cannot access pages directly.

/**
 * The Admin Enqueue Scripts & Style Files are Hooked up below for WooOCmmerce Event Manager Plugin
 */
add_action('admin_enqueue_scripts', 'mep_add_admin_scripts', 10, 1);
function mep_add_admin_scripts($hook)
{
  global $post;
  $user_api = mep_get_option('google-map-api', 'general_setting_sec', '');

  /**
   * Load Only when the New Event Add Page Open.
   */
  if ($hook == 'post-new.php' || $hook == 'post.php') {
    if ('mep_events' === $post->post_type) {
    //   wp_enqueue_script('jquery-ui-timepicker-addon', plugin_dir_url(__DIR__) . 'js/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-core'), 1, true);
    //  wp_enqueue_script('jquery-ui-timepicker-addon', plugin_dir_url(__DIR__) . 'js/jquery-ui-sliderAccess.js', array('jquery', 'jquery-ui-core', 'jquery-ui-timepicker-addon'), 1, true);
    //  wp_enqueue_script('mep_datepicker', plugin_dir_url(__DIR__) . 'js/mep_datepicker.js', array('jquery', 'jquery-ui-core', 'jquery-ui-timepicker-addon'), 1, true);
    //  wp_enqueue_style('jquery-ui-timepicker-addon', plugin_dir_url(__DIR__) . 'css/jquery-ui-timepicker-addon.css', array());
      wp_enqueue_style('mep-jquery-ui-style', plugin_dir_url(__DIR__) . 'css/jquery-ui.css', array());
      wp_enqueue_script('gmap-scripts', plugin_dir_url(__DIR__) . 'js/mkb-admin.js', array('jquery', 'jquery-ui-core'), 1, true);
    }
  }

  /**
   * If Your Save Google API Then Load the Google Map API 
   */
  if ($user_api) {
    wp_enqueue_script('gmap-libs', 'https://maps.googleapis.com/maps/api/js?key=' . $user_api . '&libraries=places&callback=initMap', array('jquery', 'gmap-scripts'), 1, true);
  }

  /**
   * Enquue Admin Styles
   */

  wp_enqueue_style('mage-jquery-ui-style', plugin_dir_url(__DIR__) . 'css/jquery-ui.css', array());
  wp_enqueue_style('mage-options-framework', plugin_dir_url(__DIR__) . 'css/mage-options-framework.css');
  wp_enqueue_style('jquery-ui', plugin_dir_url(__DIR__) . 'css/jquery-ui.css');
  wp_enqueue_style('select2.min', plugin_dir_url(__DIR__) . 'css/select2.min.css');
  wp_enqueue_style('codemirror', plugin_dir_url(__DIR__) . 'css/codemirror.css');
  wp_enqueue_style('font-awesome-css-cdn', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.2.0/css/all.min.css", null, 1);
  wp_enqueue_style('mep-admin-style', plugin_dir_url(__DIR__) . 'css/admin_style.css', array());


  /**
   * Enquue Admin Scripts
   */
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-datepicker');
  wp_enqueue_script('jquery-ui-sortable');
  wp_enqueue_style('wp-color-picker');
  wp_enqueue_script('wp-color-picker');
  wp_enqueue_script('magepeople-options-framework', plugins_url('js/mage-options-framework.js', __DIR__), array('jquery'));
  wp_localize_script('PickpluginsOptionsFramework', 'PickpluginsOptionsFramework_ajax', array('PickpluginsOptionsFramework_ajaxurl' => admin_url('admin-ajax.php')));
  wp_enqueue_script('select2.min', plugins_url('js/select2.min.js', __DIR__), array('jquery'));
  wp_enqueue_script('codemirror', plugin_dir_url(__DIR__) . 'js/codemirror.min.js', array('jquery'), null, false);
  wp_enqueue_script('form-field-dependency', plugins_url('js/form-field-dependency.js', __DIR__), array('jquery'), null, false);
  wp_localize_script('jquery', 'mep_ajax', array( 'mep_ajaxurl' => admin_url( 'admin-ajax.php')));
}

/**
 * Woocommerce Event Manager Style & Scripts Hooked up below for the Frontend  
 */
add_action('wp_enqueue_scripts', 'mep_event_enqueue_scripts', 90);
function mep_event_enqueue_scripts()
{
  wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-ui-datepicker');
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-accordion');
  wp_enqueue_style('mep-jquery-ui-style', plugin_dir_url(__DIR__) . 'css/jquery-ui.css', array());
  wp_enqueue_style('mep-event-style', plugin_dir_url(__DIR__) . 'css/style.css', array());
  wp_enqueue_style('mep-event-owl-carousal-main-style', plugin_dir_url(__DIR__) . 'css/owl.carousel.min.css', array('mep-event-style'));
  wp_enqueue_style('mep-event-owl-carousal-default-style', plugin_dir_url(__DIR__) . 'css/owl.theme.default.min.css', array('mep-event-style'));
  wp_enqueue_style('mep-event-timeline-min-style', plugin_dir_url(__DIR__) . 'css/timeline.min.css', array('mep-event-style'));
  wp_enqueue_style('font-awesome-css-cdn-5.2.0', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.2.0/css/all.min.css", null, 1);
  wp_enqueue_style('font-awesome-css-cdn', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css", null, 1);
  wp_enqueue_style('mep-calendar-min-style', plugin_dir_url(__DIR__) . 'css/calendar.min.css', array());
  wp_enqueue_script('mep-moment-js', plugin_dir_url(__DIR__) . 'js/moment.js', array(), 1, true);
  wp_enqueue_script('mep-calendar-scripts', plugin_dir_url(__DIR__) . 'js/calendar.min.js', array('jquery', 'mep-moment-js'), 1, false);
  wp_enqueue_script('mep-mixitup-min-js', plugin_dir_url(__DIR__) . 'js/mixitup.min.js', array(), 1, true);
  wp_enqueue_script('mep-owl-carousel-min', plugin_dir_url(__DIR__) . 'js/owl.carousel.min.js', array('jquery'), 1, true);
  wp_enqueue_script('mep-timeline-min', plugin_dir_url(__DIR__) . 'js/timeline.min.js', array('jquery'), 1, true);
  wp_enqueue_script('mep-event-custom-scripts', plugin_dir_url(__DIR__) . 'js/mkb-scripts.js', array(), 1, true);
  wp_localize_script('jquery', 'mep_ajax', array( 'mep_ajaxurl' => admin_url( 'admin-ajax.php')));
}

