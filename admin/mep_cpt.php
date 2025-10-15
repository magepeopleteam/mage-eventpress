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
			/* translators: %s: Event label */
			'name' => $event_label,
			/* translators: %s: Event label */
			'singular_name' => $event_label,
			/* translators: %s: Event label */
			'menu_name' => $event_label,
			/* translators: %s: Event label */
			'name_admin_bar' => $event_label,
			/* translators: %s: Event label */
			'archives' => sprintf( __( '%s List', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'attributes' => sprintf( __( '%s Attributes', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'parent_item_colon' => sprintf( __( '%s Item:', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'all_items' => sprintf( __( 'All %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'add_new_item' => sprintf( __( 'Add New %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'add_new' => sprintf( __( 'Add New %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'new_item' => sprintf( __( 'New %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'edit_item' => sprintf( __( 'Edit %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'update_item' => sprintf( __( 'Update %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'view_item' => sprintf( __( 'View %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'view_items' => sprintf( __( 'View %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'search_items' => sprintf( __( 'Search %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'not_found' => sprintf( __( '%s not found', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'not_found_in_trash' => sprintf( __( '%s not found in Trash', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'featured_image' => sprintf( __( '%s Featured Image', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'set_featured_image' => sprintf( __( 'Set %s featured image', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'remove_featured_image' => sprintf( __( 'Remove %s featured image', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'use_featured_image' => sprintf( __( 'Use as %s featured image', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'insert_into_item' => sprintf( __( 'Insert into %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'items_list' => sprintf( __( '%s list', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'items_list_navigation' => sprintf( __( '%s list navigation', 'mage-eventpress' ), $event_label ),
			/* translators: %s: Event label */
			'filter_items_list' => sprintf( __( 'Filter %s list', 'mage-eventpress' ), $event_label ),
		);


		$rewrite = array(
			'slug' => $event_slug,
			'with_front' => true,
			'pages' => true,
			'feeds' => true,
		);
		$args = array(
			'public' 			=> true,
			'has_archive' 		=> false,
			'labels' 			=> $labels,
			'menu_icon' 		=> $event_icon,
			'supports' 			=> apply_filters('mep_events_post_type_support', array('title', 'editor', 'thumbnail', 'excerpt')),
			'rewrite' 			=> $rewrite,
			'show_in_rest' 		=> apply_filters('mep_events_post_type_show_in_rest', true)
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


		$argsl = array(
			'public'          => true,
			'label'           => __( 'Event Temp Attendee', 'mage-eventpress' ),
			'menu_icon'       => 'dashicons-id',
			'supports'        => array( 'title' ),
			// 'show_in_menu' => 'edit.php?post_type=mep_events',
			'exclude_from_search'   => true,
			'show_in_menu'    => false,
			'capability_type' => 'post',
			'capabilities'    => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'    => true,
			'show_in_rest'    => false,
			'rest_base'       => 'mep_temp_attendee'
		);
		register_post_type( 'mep_temp_attendee', $argsl );




	}
	add_action('init', 'mep_cpt');