<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

// Create MKB CPT
function mep_cpt() {
// mep_event_slug

//  $event_slug = mep_get_option( 'mep_event_slug', 'general_setting_sec', 'events' );


    $labels = array(
        'name'                  => __( 'Events','mage-eventpress' ),
        'singular_name'         => __( 'Events','mage-eventpress' ),
        'menu_name'             => __( 'Events','mage-eventpress' ),
        'name_admin_bar'        => __( 'Events','mage-eventpress' ),
        'archives'              => __( 'Events List','mage-eventpress' ),
        'attributes'            => __( 'Events List','mage-eventpress' ),
        'parent_item_colon'     => __( 'Event Item:','mage-eventpress' ),
        'all_items'             => __( 'All Events','mage-eventpress' ),
        'add_new_item'          => __( 'Add New Event','mage-eventpress' ),
        'add_new'               => __( 'Add New Event','mage-eventpress' ),
        'new_item'              => __( 'New Event','mage-eventpress' ),
        'edit_item'             => __( 'Edit Event','mage-eventpress' ),
        'update_item'           => __( 'Update Event','mage-eventpress' ),
        'view_item'             => __( 'View Event','mage-eventpress' ),
        'view_items'            => __( 'View Event', 'mage-eventpress' ),
        'search_items'          => __( 'Search Event', 'mage-eventpress' ),
        'not_found'             => __( 'Event Not found', 'mage-eventpress' ),
        'not_found_in_trash'    => __( 'Event Not found in Trash', 'mage-eventpress' ),
        'featured_image'        => __( 'Event Feature Image', 'mage-eventpress' ),
        'set_featured_image'    => __( 'Set Event featured image', 'mage-eventpress' ),
        'remove_featured_image' => __( 'Remove Event featured image', 'mage-eventpress' ),
        'use_featured_image'    => __( 'Use as Event featured image', 'mage-eventpress' ),
        'insert_into_item'      => __( 'Insert into Event', 'mage-eventpress' ),
        'uploaded_to_this_item' => __( 'Uploaded to this Event', 'mage-eventpress' ),
        'items_list'            => __( 'Event list', 'mage-eventpress' ),
        'items_list_navigation' => __( 'Event list navigation', 'mage-eventpress' ),
        'filter_items_list'     => __( 'Filter Event list', 'mage-eventpress' ),
    );




    $args = array(
        'public'                => true,
        'labels'                => $labels,
        'menu_icon'             => 'dashicons-calendar-alt',
        'supports'              => array('title','editor','thumbnail','excerpt'),
        'rewrite'               => array('slug' => 'events')

    );
    register_post_type( 'mep_events', $args );
}
add_action( 'init', 'mep_cpt' );