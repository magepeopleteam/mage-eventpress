<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
function mep_cpt_tax(){

	$event_label        	= mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
	$event_cat_label        = mep_get_option('mep_event_cat_label', 'general_setting_sec', 'Category');
	$event_org__label       = mep_get_option('mep_event_org_label', 'general_setting_sec', 'Organizer');
	$event_cat_slug        	= mep_get_option('mep_event_cat_slug', 'general_setting_sec', 'mep_cat');
	$event_org_slug        	= mep_get_option('mep_event_org_slug', 'general_setting_sec', 'mep_org');

	$labels = array(
		'name'                       => _x( $event_label.' '.$event_cat_label,'mage-eventpress' ),
		'singular_name'              => _x( $event_label.' '.$event_cat_label,'mage-eventpress' ),
		'menu_name'                  => __( $event_cat_label, 'mage-eventpress' ),
		'all_items'                  => __( 'All '.$event_label.' Category', 'mage-eventpress' ),
		'parent_item'                => __( 'Parent '.$event_cat_label, 'mage-eventpress' ),
		'parent_item_colon'          => __( 'Parent '.$event_cat_label.':', 'mage-eventpress' ),
		'new_item_name'              => __( 'New '.$event_cat_label.' Name', 'mage-eventpress' ),
		'add_new_item'               => __( 'Add New '.$event_cat_label, 'mage-eventpress' ),
		'edit_item'                  => __( 'Edit '.$event_cat_label, 'mage-eventpress' ),
		'update_item'                => __( 'Update '.$event_cat_label, 'mage-eventpress' ),
		'view_item'                  => __( 'View '.$event_cat_label, 'mage-eventpress' ),
		'separate_items_with_commas' => __( 'Separate '.$event_cat_label.' with commas', 'mage-eventpress' ),
		'add_or_remove_items'        => __( 'Add or remove '.$event_cat_label, 'mage-eventpress' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'mage-eventpress' ),
		'popular_items'              => __( 'Popular '.$event_cat_label, 'mage-eventpress' ),
		'search_items'               => __( 'Search '.$event_cat_label, 'mage-eventpress' ),
		'not_found'                  => __( 'Not Found', 'mage-eventpress' ),
		'no_terms'                   => __( 'No '.$event_cat_label, 'mage-eventpress' ),
		'items_list'                 => __( $event_cat_label.' list', 'mage-eventpress' ),
		'items_list_navigation'      => __( $event_cat_label.' list navigation', 'mage-eventpress' ),
	);

	$args = array(
		'hierarchical'          => true,
		"public" 				=> true,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => $event_cat_slug ),
	);
register_taxonomy('mep_cat', 'mep_events', $args);


	$labelso = array(
		'name'                       => _x( $event_label.' '.$event_org__label,'mage-eventpress' ),
		'singular_name'              => _x( $event_label.' '.$event_org__label,'mage-eventpress' ),
		'menu_name'                  => __( $event_org__label, 'mage-eventpress' ),
		'all_items'                  => __( 'All '.$event_label.' '.$event_org__label, 'mage-eventpress' ),
		'parent_item'                => __( 'Parent '.$event_org__label, 'mage-eventpress' ),
		'parent_item_colon'          => __( 'Parent '.$event_org__label.':', 'mage-eventpress' ),
		'new_item_name'              => __( 'New '.$event_org__label.' Name', 'mage-eventpress' ),
		'add_new_item'               => __( 'Add New '.$event_org__label, 'mage-eventpress' ),
		'edit_item'                  => __( 'Edit '.$event_org__label, 'mage-eventpress' ),
		'update_item'                => __( 'Update '.$event_org__label, 'mage-eventpress' ),
		'view_item'                  => __( 'View '.$event_org__label, 'mage-eventpress' ),
		'separate_items_with_commas' => __( 'Separate '.$event_org__label.' with commas', 'mage-eventpress' ),
		'add_or_remove_items'        => __( 'Add or remove '.$event_org__label, 'mage-eventpress' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'mage-eventpress' ),
		'popular_items'              => __( 'Popular '.$event_org__label, 'mage-eventpress' ),
		'search_items'               => __( 'Search '.$event_org__label, 'mage-eventpress' ),
		'not_found'                  => __( 'Not Found', 'mage-eventpress' ),
		'no_terms'                   => __( 'No '.$event_org__label, 'mage-eventpress' ),
		'items_list'                 => __( $event_org__label.' list', 'mage-eventpress' ),
		'items_list_navigation'      => __( $event_org__label.' list navigation', 'mage-eventpress' ),
	);

	$argso = array(
		'hierarchical'          => true,
		"public" 				=> true,
		'labels'                => $labelso,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => $event_org_slug ),
	);
register_taxonomy('mep_org', 'mep_events', $argso);

}
add_action("init","mep_cpt_tax",10);





add_filter("manage_edit-mep_cat_columns", 'mep_add_cat_tax_column'); 
function mep_add_cat_tax_column($theme_columns) {
    $new_columns = array(
        'cb' => '<input type="checkbox" />',
        'name' => __('Name'),
         'mep_cat_id' => 'CatID',
//      'description' => __('Description'),
        'slug' => __('Slug'),
        'posts' => __('Posts')
        );
    return $new_columns;
}


add_filter("manage_mep_cat_custom_column", 'mep_display_cat_id_to_column', 10, 3);
function mep_display_cat_id_to_column($out, $column_name, $theme_id) {
    switch ($column_name) {
        case 'mep_cat_id': 
                echo $theme_id;
            break;
 
        default:
            break;
    }
    return $out;    
}



add_filter("manage_edit-mep_org_columns", 'mep_add_org_tax_column'); 
function mep_add_org_tax_column($theme_columns) {
    $new_columns = array(
        'cb' => '<input type="checkbox" />',
        'name' => __('Name'),
         'mep_org_id' => 'OrgID',
//      'description' => __('Description'),
        'slug' => __('Slug'),
        'posts' => __('Posts')
        );
    return $new_columns;
}


add_filter("manage_mep_org_custom_column", 'mep_display_org_id_to_column', 10, 3);
function mep_display_org_id_to_column($out, $column_name, $theme_id) {
    switch ($column_name) {
        case 'mep_org_id': 
                echo $theme_id;
            break;
 
        default:
            break;
    }
    return $out;    
}