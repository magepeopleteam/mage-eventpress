<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Admin')) {
		class MPWEM_Admin {
			public function __construct() {
				//if (is_admin()) {
					$this->load_file();
					add_action('init', [$this, 'add_dummy_data']);
					add_filter('use_block_editor_for_post_type', [$this, 'disable_gutenberg'], 10, 2);
					add_action('upgrader_process_complete', [$this, 'flush_rewrite'], 0);
				//}
			}
			public function flush_rewrite() {
				flush_rewrite_rules();
			}
			private function load_file(): void {
				if (!class_exists('EDD_SL_Plugin_Updater')) {
					require_once MPWEM_PLUGIN_DIR . '/lib/classes/EDD_SL_Plugin_Updater.php';
				}
				// require_once(dirname(__DIR__) . '/lib/classes/class-wc-product-data.php');
				require_once(dirname(__DIR__) . '/lib/classes/class-form-fields-generator.php');
				require_once(dirname(__DIR__) . '/lib/classes/class-meta-box.php');
				require_once(dirname(__DIR__) . '/lib/classes/class-taxonomy-edit.php');
				require_once(dirname(__DIR__) . "/support/elementor/elementor-support.php");
				require_once(dirname(__DIR__) . '/lib/classes/class-icon-library.php');
				require_once(dirname(__DIR__) . '/lib/classes/class-icon-popup.php');
				//****************Global settings************************//
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/global/MAGE_Setting_API.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/global/admin_setting_panel.php';
				//************************************//
				require_once MPWEM_PLUGIN_DIR . '/Admin/mep_dummy_import.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/mep_cpt.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/status.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/MPWEM_Welcome.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/MPWEM_Quick_Setup.php';
//				//****************Taxi settings************************//
//				require_once MPTBM_PLUGIN_DIR . '/Admin/settings/taxi/MPTBM_Settings.php';
//				require_once MPTBM_PLUGIN_DIR . '/Admin/settings/taxi/MPTBM_General_Settings.php';
//				require_once MPTBM_PLUGIN_DIR . '/Admin/settings/taxi/MPTBM_Price_Settings.php';
//				require_once MPTBM_PLUGIN_DIR . '/Admin/settings/taxi/MPTBM_Extra_Service.php';
//				require_once MPTBM_PLUGIN_DIR . '/Admin/settings/taxi/MPTBM_Date_Settings.php';
				//require_once MPTBM_PLUGIN_DIR . '/Admin/settings/taxi/MPTBM_Gallery_Settings.php';
			}
			public function add_dummy_data() {
				//new MPTBM_Dummy_Import();
			}
			//************Disable Gutenberg************************//
			public function disable_gutenberg($current_status, $post_type) {
				$user_status = MP_Global_Function::get_settings('general_setting_sec', 'mep_disable_block_editor', 'yes');
				if ($post_type === 'mep_events' && $user_status == 'yes') {
					return false;
				}
				return $current_status;
			}
		}
		new MPWEM_Admin();
	}