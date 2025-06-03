<?php

/*
* @Author 		rubelcuet10@gmail.com
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('MPWEM_Event_Lists')) {
    class MPWEM_Event_Lists{
        public function __construct() {
            add_action('admin_menu', array($this, 'event_list_menu'));
        }
        public function event_list_menu() {
            add_submenu_page('edit.php?post_type=mep_events', __('Event Lists', 'mage-eventpress'), __('Event Lists', 'mage-eventpress'), 'manage_options', 'mep_event_lists', array($this, 'display_event_list'));
        }
        public function display_event_list() {
            require MPWEM_Functions::template_path('layout/event_lists.php');
        }

    }

    new MPWEM_Event_Lists();
}