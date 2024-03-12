<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * The Magical Event Post Type Going to registred here to make your WordPress and WooCommerce a Event Manager :)
	 */
	function mep_cpt() {
		$speaker_status = mep_get_option('mep_enable_speaker_list', 'single_event_setting_sec', 'no');
		$event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
		$event_slug = mep_get_option('mep_event_slug', 'general_setting_sec', 'events');
		$event_icon = mep_get_option('mep_event_icon', 'general_setting_sec', 'dashicons-calendar-alt');
		$labels = array(
			'name' => __($event_label, 'mage-eventpress'),
			'singular_name' => __($event_label, 'mage-eventpress'),
			'menu_name' => __($event_label, 'mage-eventpress'),
			'name_admin_bar' => __($event_label, 'mage-eventpress'),
			'archives' => __($event_label . ' List', 'mage-eventpress'),
			'attributes' => __($event_label . ' List', 'mage-eventpress'),
			'parent_item_colon' => __($event_label . ' Item:', 'mage-eventpress'),
			'all_items' => __('All ', 'mage-eventpress') . $event_label,
			'add_new_item' => __('Add New ', 'mage-eventpress') . $event_label,
			'add_new' => __('Add New ', 'mage-eventpress') . $event_label,
			'new_item' => __('New ', 'mage-eventpress') . $event_label,
			'edit_item' => __('Edit ', 'mage-eventpress') . $event_label,
			'update_item' => __('Update ', 'mage-eventpress') . $event_label,
			'view_item' => __('View ', 'mage-eventpress') . $event_label,
			'view_items' => __('View ', 'mage-eventpress') . $event_label,
			'search_items' => __('Search ', 'mage-eventpress') . $event_label,
			'not_found' => $event_label . __(' Not found', 'mage-eventpress'),
			'not_found_in_trash' => $event_label . __(' Not found in Trash', 'mage-eventpress'),
			'featured_image' => $event_label . __(' Feature Image', 'mage-eventpress'),
			'set_featured_image' => __('Set ', 'mage-eventpress') . $event_label . __(' featured image', 'mage-eventpress'),
			'remove_featured_image' => __('Remove ', 'mage-eventpress') . $event_label . __(' featured image', 'mage-eventpress'),
			'use_featured_image' => __('Use as ', 'mage-eventpress') . $event_label . __(' featured image', 'mage-eventpress'),
			'insert_into_item' => __('Insert into ', 'mage-eventpress') . $event_label,
			'uploaded_to_this_item' => __('Uploaded to this ', 'mage-eventpress') . $event_label,
			'items_list' => $event_label . __(' list', 'mage-eventpress'),
			'items_list_navigation' => $event_label . __(' list navigation', 'mage-eventpress'),
			'filter_items_list' => __('Filter ', 'mage-eventpress') . $event_label . __(' list', 'mage-eventpress'),
		);
		$rewrite = array(
			'slug' => $event_slug,
			'with_front' => true,
			'pages' => true,
			'feeds' => true,
		);
		$args = array(
			'public' => true,
			'has_archive' => false,
			'labels' => $labels,
			'menu_icon' => $event_icon,
			'supports' => apply_filters('mep_events_post_type_support', array('title', 'editor', 'thumbnail', 'excerpt')),
			'rewrite' => $rewrite,
			'show_in_rest' => apply_filters('mep_events_post_type_show_in_rest', true)
		);
		register_post_type('mep_events', $args);
		$labels = array(
			'name' => __('Speakers', 'mage-eventpress'),
			'singular_name' => __('Speaker', 'mage-eventpress'),
			'menu_name' => __('Speakers', 'mage-eventpress'),
			'name_admin_bar' => __('Speakers', 'mage-eventpress'),
			'archives' => __('Speakers List', 'mage-eventpress'),
			'attributes' => __('Speakers List', 'mage-eventpress'),
			'parent_item_colon' => __('Speakers Item:', 'mage-eventpress'),
			'all_items' => __('Speakers', 'mage-eventpress'),
			'add_new_item' => __('Add New Speaker', 'mage-eventpress'),
			'add_new' => __('Add New Speaker', 'mage-eventpress'),
			'new_item' => __('New Speaker', 'mage-eventpress'),
			'edit_item' => __('Edit Speaker', 'mage-eventpress'),
			'update_item' => __('Update Speaker', 'mage-eventpress'),
			'view_item' => __('View Speaker', 'mage-eventpress'),
			'view_items' => __('View Speaker', 'mage-eventpress'),
			'search_items' => __('Search Speaker', 'mage-eventpress'),
			'not_found' => __('Speaker Not found', 'mage-eventpress'),
			'not_found_in_trash' => __('Speaker Not found in Trash', 'mage-eventpress'),
			'featured_image' => __('Speaker Image', 'mage-eventpress'),
			'set_featured_image' => __('Set Speaker image', 'mage-eventpress'),
			'remove_featured_image' => __('Remove Speaker image', 'mage-eventpress'),
			'use_featured_image' => __('Use as Speaker image', 'mage-eventpress'),
			'insert_into_item' => __('Insert into Speaker', 'mage-eventpress'),
			'uploaded_to_this_item' => __('Uploaded to this Speaker', 'mage-eventpress'),
			'items_list' => __('Speaker list', 'mage-eventpress'),
			'items_list_navigation' => __('Speaker list navigation', 'mage-eventpress'),
			'filter_items_list' => __('Filter Speaker list', 'mage-eventpress'),
		);
		$sprewrite = array(
			'slug' => 'event-speaker',
			'with_front' => true,
			'pages' => true,
			'feeds' => true,
		);
		$args = array(
			'public' => true,
			'labels' => $labels,
			'menu_icon' => 'dashicons-calendar-alt',
			'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
			'rewrite' => $sprewrite,
			'show_in_menu' => 'edit.php?post_type=mep_events',
			'show_in_rest' => apply_filters('mep_speaker_post_type_show_in_rest', true)
		);
		if ($speaker_status == 'yes') {
			register_post_type('mep_event_speaker', $args);
		}
	}
	add_action('init', 'mep_cpt');