<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
function mep_cpt_tax(){

	$event_label        	= mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
	$event_cat_label        = mep_get_option('mep_event_cat_label', 'general_setting_sec', 'Category');
	$event_org__label       = mep_get_option('mep_event_org_label', 'general_setting_sec', 'Organizer');
	$event_cat_slug        	= mep_get_option('mep_event_cat_slug', 'general_setting_sec', 'mep_cat');
	$event_org_slug        	= mep_get_option('mep_event_org_slug', 'general_setting_sec', 'mep_org');

$labels = array(
 // translators: %1$s is the event label, %2$s is the category label.
    'name' => sprintf( _x( '%1$s %2$s', 'Taxonomy general name', 'mage-eventpress' ), $event_label, $event_cat_label ),
    // translators: %1$s is the event label, %2$s is the category label.
    'singular_name' => sprintf( _x( '%1$s %2$s', 'Taxonomy singular name', 'mage-eventpress' ), $event_label, $event_cat_label ),
	'menu_name' => $event_cat_label,
    // translators: %s is the event label.
    'all_items' => sprintf( __( 'All %s Category', 'mage-eventpress' ), $event_label ),
    // translators: %s is the category label.
    'parent_item' => sprintf( __( 'Parent %s', 'mage-eventpress' ), $event_cat_label ),
    // translators: %s is the category label.
    'parent_item_colon' => sprintf( __( 'Parent %s:', 'mage-eventpress' ), $event_cat_label ),
    // translators: %s is the category label.
    'new_item_name'              => sprintf( __( 'New %s Name', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'add_new_item'               => sprintf( __( 'Add New %s', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'edit_item'                  => sprintf( __( 'Edit %s', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'update_item'                => sprintf( __( 'Update %s', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'view_item'                  => sprintf( __( 'View %s', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'choose_from_most_used'      => __( 'Choose from the most used', 'mage-eventpress' ),
	    // translators: %s is the category label.
    'popular_items'              => sprintf( __( 'Popular %s', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'search_items'               => sprintf( __( 'Search %s', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'not_found'                  => __( 'Not Found', 'mage-eventpress' ),
	    // translators: %s is the category label.
    'no_terms'                   => sprintf( __( 'No %s', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'items_list'                 => sprintf( __( '%s list', 'mage-eventpress' ), $event_cat_label ),
	    // translators: %s is the category label.
    'items_list_navigation'      => sprintf( __( '%s list navigation', 'mage-eventpress' ), $event_cat_label ),
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
		'show_in_rest'          => true,
		'rest_base'             => 'mep_cat'
	);
register_taxonomy('mep_cat', 'mep_events', $args);


$labelso = array(
    // translators: %1$s is the event label, %2$s is the organization label.
    'name'                       => sprintf( _x( '%1$s %2$s', 'taxonomy general name', 'mage-eventpress' ), $event_label, $event_org__label ),

    // translators: %1$s is the event label, %2$s is the organization label.
    'singular_name'              => sprintf( _x( '%1$s %2$s', 'taxonomy singular name', 'mage-eventpress' ), $event_label, $event_org__label ),

    // translators: %s is the organization label.
    'menu_name'                  => $event_org__label,

    // translators: %1$s is the event label, %2$s is the organization label.
    'all_items'                  => sprintf( __( 'All %1$s %2$s', 'mage-eventpress' ), $event_label, $event_org__label ),

    // translators: %s is the organization label.
    'parent_item'                => sprintf( __( 'Parent %s', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'parent_item_colon'          => sprintf( __( 'Parent %s:', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'new_item_name'              => sprintf( __( 'New %s Name', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'add_new_item'               => sprintf( __( 'Add New %s', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'edit_item'                  => sprintf( __( 'Edit %s', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'update_item'                => sprintf( __( 'Update %s', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'view_item'                  => sprintf( __( 'View %s', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'mage-eventpress' ), $event_org__label ),

    'choose_from_most_used'      => __( 'Choose from the most used', 'mage-eventpress' ),

    // translators: %s is the organization label.
    'popular_items'              => sprintf( __( 'Popular %s', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'search_items'               => sprintf( __( 'Search %s', 'mage-eventpress' ), $event_org__label ),

    'not_found'                  => __( 'Not Found', 'mage-eventpress' ),

    // translators: %s is the organization label.
    'no_terms'                   => sprintf( __( 'No %s', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'items_list'                 => sprintf( __( '%s list', 'mage-eventpress' ), $event_org__label ),

    // translators: %s is the organization label.
    'items_list_navigation'      => sprintf( __( '%s list navigation', 'mage-eventpress' ), $event_org__label ),
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
		'show_in_rest'          => true,
		'rest_base'             => 'mep_org',		
	);
register_taxonomy('mep_org', 'mep_events', $argso);

}
add_action("init","mep_cpt_tax",10);





add_filter("manage_edit-mep_cat_columns", 'mep_add_cat_tax_column'); 
function mep_add_cat_tax_column($theme_columns) {
    $new_columns = array(
        'cb' => '<input type="checkbox" />',
        'name' => __('Name','mage-eventpress'),
         'mep_cat_id' => 'CatID',
//      'description' => __('Description'),
        'slug' => __('Slug','mage-eventpress'),
        'posts' => __('Posts','mage-eventpress')
        );
    return $new_columns;
}


add_filter("manage_mep_cat_custom_column", 'mep_display_cat_id_to_column', 10, 3);
function mep_display_cat_id_to_column($out, $column_name, $theme_id) {
    switch ($column_name) {
        case 'mep_cat_id': 
                echo esc_html($theme_id);
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
        'name' => __('Name', 'mage-eventpress'),
         'mep_org_id' => 'OrgID',
//      'description' => __('Description', 'mage-eventpress'),
        'slug' => __('Slug', 'mage-eventpress'),
        'posts' => __('Posts', 'mage-eventpress')
        );
    return $new_columns;
}


add_filter("manage_mep_org_custom_column", 'mep_display_org_id_to_column', 10, 3);
function mep_display_org_id_to_column($out, $column_name, $theme_id) {
    switch ($column_name) {
        case 'mep_org_id': 
                echo esc_html($theme_id);
            break;
 
        default:
            break;
    }
    return $out;    
}