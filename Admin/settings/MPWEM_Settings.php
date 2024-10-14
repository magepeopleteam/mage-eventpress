<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	

	// if (!class_exists('MPWEM_Settings')) {
	// 	class MPWEM_Settings {
	// 		public function __construct() {
	// 			add_action('save_post', array($this, 'save_settings'), 99, 1);
	// 		}

	// 		public function save_settings($post_id): void { 
	// 			if (!isset($_POST['mpwem_type_nonce']) || !wp_verify_nonce($_POST['mpwem_type_nonce'], 'mpwem_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
	// 				return;
	// 			}
	// 			do_action('mpwem_settings_save', $post_id);
	// 		}
	// 	}
	// }


	add_action('save_post', 'mep_re_save_settings');
	function mep_re_save_settings($post_id) { 
		if (!isset($_POST['mpwem_type_nonce']) || !wp_verify_nonce($_POST['mpwem_type_nonce'], 'mpwem_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
			return;
		}
		do_action('mpwem_settings_save', $post_id);
	}