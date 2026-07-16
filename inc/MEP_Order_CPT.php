<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Order_CPT {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		add_action( 'init', array( __CLASS__, 'register_statuses' ) );
	}

	/**
	 * Register the WooCommerce-equivalent order statuses so native orders
	 * can carry them, show correct labels, and remain included in
	 * `post_status => 'any'` queries.
	 *
	 * Note: `pending` and `publish` are core WP statuses already (publish is
	 * used as "Completed" for native orders), so they are not re-registered.
	 */
	public static function register_statuses() {
		$statuses = array(
			'processing' => _x( 'Processing', 'Order status', 'mage-eventpress' ),
			'on-hold'    => _x( 'On hold', 'Order status', 'mage-eventpress' ),
			'cancelled'  => _x( 'Cancelled', 'Order status', 'mage-eventpress' ),
			'refunded'   => _x( 'Refunded', 'Order status', 'mage-eventpress' ),
			'failed'     => _x( 'Failed', 'Order status', 'mage-eventpress' ),
		);

		foreach ( $statuses as $status => $label ) {
			register_post_status( $status, array(
				'label'                     => $label,
				'public'                    => false,
				'internal'                  => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of orders with this status */
				'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'mage-eventpress' ),
			) );
		}
	}

	public static function register_cpt() {
		$labels = array(
			'name'                  => _x( 'Event Orders', 'Post Type General Name', 'mage-eventpress' ),
			'singular_name'         => _x( 'Event Order', 'Post Type Singular Name', 'mage-eventpress' ),
			'menu_name'             => __( 'Event Orders', 'mage-eventpress' ),
			'name_admin_bar'        => __( 'Event Order', 'mage-eventpress' ),
			'archives'              => __( 'Order Archives', 'mage-eventpress' ),
			'attributes'            => __( 'Order Attributes', 'mage-eventpress' ),
			'parent_item_colon'     => __( 'Parent Order:', 'mage-eventpress' ),
			'all_items'             => __( 'All Orders', 'mage-eventpress' ),
			'add_new_item'          => __( 'Add New Order', 'mage-eventpress' ),
			'add_new'               => __( 'Add New', 'mage-eventpress' ),
			'new_item'              => __( 'New Order', 'mage-eventpress' ),
			'edit_item'             => __( 'Edit Order', 'mage-eventpress' ),
			'update_item'           => __( 'Update Order', 'mage-eventpress' ),
			'view_item'             => __( 'View Order', 'mage-eventpress' ),
			'view_items'            => __( 'View Orders', 'mage-eventpress' ),
			'search_items'          => __( 'Search Order', 'mage-eventpress' ),
			'not_found'             => __( 'Not found', 'mage-eventpress' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'mage-eventpress' ),
			'featured_image'        => __( 'Featured Image', 'mage-eventpress' ),
			'set_featured_image'    => __( 'Set featured image', 'mage-eventpress' ),
			'remove_featured_image' => __( 'Remove featured image', 'mage-eventpress' ),
			'use_featured_image'    => __( 'Use as featured image', 'mage-eventpress' ),
			'insert_into_item'      => __( 'Insert into order', 'mage-eventpress' ),
			'uploaded_to_this_item' => __( 'Uploaded to this order', 'mage-eventpress' ),
			'items_list'            => __( 'Orders list', 'mage-eventpress' ),
			'items_list_navigation' => __( 'Orders list navigation', 'mage-eventpress' ),
			'filter_items_list'     => __( 'Filter orders list', 'mage-eventpress' ),
		);
		$args = array(
			'label'                 => __( 'Event Order', 'mage-eventpress' ),
			'description'           => __( 'Custom event orders', 'mage-eventpress' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'custom-fields' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => false, // We will show it in our custom unified page
			'menu_position'         => 5,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'capability_type'       => 'post',
			'show_in_rest'          => false,
		);
		register_post_type( 'mep_custom_order', $args );
	}
}
