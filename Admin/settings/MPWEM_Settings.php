<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Settings')) {
		class MPWEM_Settings {
			public function __construct() {
				add_action('save_post', array($this, 'save_settings'));
			}
			public function save_settings($post_id) {
				if (!isset($_POST['mpwem_type_nonce']) || !wp_verify_nonce($_POST['mpwem_type_nonce'], 'mpwem_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
					return;
				}
				do_action('mpwem_settings_save', $post_id);
			}
			public static function des_array($key) {
				$des = array(
					'mep_display_slider' => esc_html__('By default slider is ON but you can keep it off by switching this option', 'mage-eventpress'),
					'mep_gallery_images' => esc_html__('Please upload images for gallery', 'mage-eventpress'),
					'gallery_settings_description' => esc_html__('Here gallery image can be added  to event so that guest can understand about this event.', 'mage-eventpress'),
				);
				$des = apply_filters('mpwem_filter_description_array', $des);
				return $des[$key];
			}
			public static function des_p($key) {
				echo self::des_array($key);
			}
		}
		new MPWEM_Settings();
	}
