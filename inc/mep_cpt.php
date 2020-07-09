<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * The Magical Event Post Type Going to registred here to make your WordPress and WooCommerce a Event Manager :) 
 */
function mep_cpt()
{
    $speaker_status     = mep_get_option('mep_enable_speaker_list', 'general_setting_sec', 'no');
    $event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
    $event_slug         = mep_get_option('mep_event_slug', 'general_setting_sec', 'events');
    $event_icon         = mep_get_option('mep_event_icon', 'general_setting_sec', 'dashicons-calendar-alt');

    $labels = array(
        'name'                  => __($event_label, 'mage-eventpress'),
        'singular_name'         => __($event_label, 'mage-eventpress'),
        'menu_name'             => __($event_label, 'mage-eventpress'),
        'name_admin_bar'        => __($event_label, 'mage-eventpress'),
        'archives'              => __($event_label.' List', 'mage-eventpress'),
        'attributes'            => __($event_label.' List', 'mage-eventpress'),
        'parent_item_colon'     => __($event_label.' Item:', 'mage-eventpress'),
        'all_items'             => __('All '.$event_label, 'mage-eventpress'),
        'add_new_item'          => __('Add New '.$event_label, 'mage-eventpress'),
        'add_new'               => __('Add New '.$event_label, 'mage-eventpress'),
        'new_item'              => __('New '.$event_label, 'mage-eventpress'),
        'edit_item'             => __('Edit '.$event_label, 'mage-eventpress'),
        'update_item'           => __('Update '.$event_label, 'mage-eventpress'),
        'view_item'             => __('View '.$event_label, 'mage-eventpress'),
        'view_items'            => __('View '.$event_label, 'mage-eventpress'),
        'search_items'          => __('Search '.$event_label, 'mage-eventpress'),
        'not_found'             => __($event_label.' Not found', 'mage-eventpress'),
        'not_found_in_trash'    => __($event_label.' Not found in Trash', 'mage-eventpress'),
        'featured_image'        => __($event_label.' Feature Image', 'mage-eventpress'),
        'set_featured_image'    => __('Set '.$event_label.' featured image', 'mage-eventpress'),
        'remove_featured_image' => __('Remove '.$event_label.' featured image', 'mage-eventpress'),
        'use_featured_image'    => __('Use as '.$event_label.' featured image', 'mage-eventpress'),
        'insert_into_item'      => __('Insert into '.$event_label, 'mage-eventpress'),
        'uploaded_to_this_item' => __('Uploaded to this '.$event_label, 'mage-eventpress'),
        'items_list'            => __($event_label.' list', 'mage-eventpress'),
        'items_list_navigation' => __($event_label.' list navigation', 'mage-eventpress'),
        'filter_items_list'     => __('Filter '.$event_label.' list', 'mage-eventpress'),
    );

    $args = array(
        'public'                => true,
        'labels'                => $labels,
        'menu_icon'             => $event_icon,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite'               => array('slug' => $event_slug)

    );
    register_post_type('mep_events', $args);

    $labels = array(
        'name'                  => __('Speakers', 'mage-eventpress'),
        'singular_name'         => __('Speaker', 'mage-eventpress'),
        'menu_name'             => __('Speakers', 'mage-eventpress'),
        'name_admin_bar'        => __('Speakers', 'mage-eventpress'),
        'archives'              => __('Speakers List', 'mage-eventpress'),
        'attributes'            => __('Speakers List', 'mage-eventpress'),
        'parent_item_colon'     => __('Speakers Item:', 'mage-eventpress'),
        'all_items'             => __('Speakers', 'mage-eventpress'),
        'add_new_item'          => __('Add New Speaker', 'mage-eventpress'),
        'add_new'               => __('Add New Speaker', 'mage-eventpress'),
        'new_item'              => __('New Speaker', 'mage-eventpress'),
        'edit_item'             => __('Edit Speaker', 'mage-eventpress'),
        'update_item'           => __('Update Speaker', 'mage-eventpress'),
        'view_item'             => __('View Speaker', 'mage-eventpress'),
        'view_items'            => __('View Speaker', 'mage-eventpress'),
        'search_items'          => __('Search Speaker', 'mage-eventpress'),
        'not_found'             => __('Speaker Not found', 'mage-eventpress'),
        'not_found_in_trash'    => __('Speaker Not found in Trash', 'mage-eventpress'),
        'featured_image'        => __('Speaker Image', 'mage-eventpress'),
        'set_featured_image'    => __('Set Speaker image', 'mage-eventpress'),
        'remove_featured_image' => __('Remove Speaker image', 'mage-eventpress'),
        'use_featured_image'    => __('Use as Speaker image', 'mage-eventpress'),
        'insert_into_item'      => __('Insert into Speaker', 'mage-eventpress'),
        'uploaded_to_this_item' => __('Uploaded to this Speaker', 'mage-eventpress'),
        'items_list'            => __('Speaker list', 'mage-eventpress'),
        'items_list_navigation' => __('Speaker list navigation', 'mage-eventpress'),
        'filter_items_list'     => __('Filter Speaker list', 'mage-eventpress'),
    );

    $args = array(
        'public'                => true,
        'labels'                => $labels,
        'menu_icon'             => 'dashicons-calendar-alt',
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite'               => array('slug' => 'event-speaker'),
        'show_in_menu' => 'edit.php?post_type=mep_events',

    );
    
    if($speaker_status == 'yes'){
        register_post_type('mep_event_speaker', $args);
    }
}
add_action('init', 'mep_cpt');