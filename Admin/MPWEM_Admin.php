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
                require_once MPWEM_PLUGIN_DIR . '/Admin/MPWEM_Event_Lists.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/mep_dummy_import.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/mep_cpt.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/status.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/MPWEM_Welcome.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/MPWEM_Quick_Setup.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/mep_analytics.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/MPWEM_Template_Override_Menu.php';
				//****************Meta Settings File Include************************//
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Venue_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Ticket_Price_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Seo_content_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_event_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Tax_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Date_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Email_Text.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Faq_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Speaker_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Timeline_Details.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Template_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Settings_Gallery.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Related_Settings.php';
				require_once MPWEM_PLUGIN_DIR . '/Admin/settings/MPWEM_Template_Override_Settings.php';
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