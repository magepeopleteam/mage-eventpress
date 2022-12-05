<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('EDD_SL_Plugin_Updater')) {
    require_once(dirname(__DIR__) . '/lib/classes/EDD_SL_Plugin_Updater.php');
}
    require_once(dirname(__DIR__) . '/lib/classes/class-wc-product-data.php');
    require_once(dirname(__DIR__) . '/lib/classes/class-form-fields-generator.php');    
    require_once(dirname(__DIR__) . '/lib/classes/class-meta-box.php');
    require_once(dirname(__DIR__) . '/lib/classes/class-taxonomy-edit.php');
    require_once(dirname(__DIR__) . '/lib/classes/class-mep.php');
    require_once(dirname(__DIR__) . "/inc/class/mep_settings_api.php");
    
    require_once(dirname(__DIR__) . "/inc/welcome.php");
    require_once(dirname(__DIR__) . "/inc/status.php");
    require_once(dirname(__DIR__) . "/inc/mep_cpt.php");
    require_once(dirname(__DIR__) . "/inc/mep_tax.php");
    require_once(dirname(__DIR__) . "/inc/mep_event_meta.php");
    require_once(dirname(__DIR__) . "/inc/mep_event_fw_meta.php");
    require_once(dirname(__DIR__) . "/inc/mep_extra_price.php");
    require_once(dirname(__DIR__) . "/inc/mep_shortcode.php");
    require_once(dirname(__DIR__) . "/inc/admin_setting_panel.php");
    require_once(dirname(__DIR__) . "/inc/mep_enque.php");
    require_once(dirname(__DIR__) . "/inc/mep_user_custom_style.php");
    require_once(dirname(__DIR__) . "/inc/mep_tax_meta.php");
    
    require_once(dirname(__DIR__) . "/inc/mep_functions.php");
    require_once(dirname(__DIR__) . "/inc/mep_query.php");
    require_once(dirname(__DIR__) . "/support/elementor/elementor-support.php");
    require_once(dirname(__DIR__) . '/lib/classes/class-icon-library.php');
    require_once(dirname(__DIR__) . '/lib/classes/class-icon-popup.php');