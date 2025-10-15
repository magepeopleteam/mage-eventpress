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

            add_action('admin_action_mpwem_duplicate_post', [$this,'mpwem_duplicate_post_function']);

            add_action('wp_ajax_mpwem_trash_multiple_posts', [$this,'mpwem_trash_multiple_posts']);
            add_action('wp_ajax_mpwem_quick_edit_event', array($this, 'mpwem_quick_edit_event'));
        }

        /**
         * Handles AJAX request to move multiple mep_events posts to trash.
         *
         * Checks nonce, user authentication, and permissions before processing.
         * Expects $_POST['post_ids'] as an array of post IDs and $_POST['nonce'] for security.
         *
         * @return void Outputs JSON response and terminates execution.
         */
        function mpwem_trash_multiple_posts() {           
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mpwem_multiple_trash_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }
            if (!is_user_logged_in()) {
                wp_send_json_error(['message' => 'User not logged in']);
            }
            if (!current_user_can('delete_posts')) {
                wp_send_json_error(['message' => 'Permission denied']);
            }
            // Sanitize and validate post IDs
            $post_ids = (isset($_POST['post_ids']) && is_array($_POST['post_ids'])) ? array_map('intval', $_POST['post_ids']) : [];
            if (empty($post_ids)) {
                wp_send_json_error(['message' => 'No valid post IDs provided.']);
            }
            foreach ($post_ids as $post_id) {
                if (get_post_type($post_id) === 'mep_events' && get_post_status($post_id) !== 'trash' && (get_post_field('post_author', $post_id) == get_current_user_id() || is_super_admin())) {
                    wp_trash_post($post_id);
                }
            }
            wp_send_json_success(['message' => 'Selected posts moved to trash successfully.']);
        }

        function mpwem_duplicate_post_function() {
            if ( !isset( $_GET['post_id']) || !isset($_GET['_wpnonce']) ||
                !wp_verify_nonce($_GET['_wpnonce'], 'mpwem_duplicate_post_' . sanitize_text_field( $_GET['post_id'] ) )
            ) {
                wp_die('Invalid request (missing or invalid nonce).');
            }

            $post_id = (int)sanitize_text_field( wp_unslash( $_GET['post_id'] ) );
            $post = get_post($post_id);

            $new_post = array(
                'post_title'   => $post->post_title . ' (Copy)',
                'post_content' => $post->post_content,
                'post_status'  => 'draft',
                'post_type'    => $post->post_type,
                'post_author'  => get_current_user_id(),
            );

            $new_post_id = wp_insert_post($new_post);

            if (is_wp_error($new_post_id) || !$new_post_id) {
                wp_die('Failed to duplicate post.');
            }
            $meta = get_post_meta($post_id);
            foreach ($meta as $key => $values) {
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        }

        public function event_list_menu() {
            
            add_submenu_page('edit.php?post_type=mep_events', __('Event Lists', 'mage-eventpress'), __('Event Lists', 'mage-eventpress'), 'manage_woocommerce', 'mep_event_lists', array($this, 'display_event_list'));
        }
        public function display_event_list() {
            require MPWEM_Functions::template_path('layout/event_lists.php');
        }

        public function mpwem_quick_edit_event() {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'mep_nonce')) {
                wp_send_json_error(array('message' => 'Security check failed'));
            }

            // Check user capabilities
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(array('message' => 'You do not have permission to edit events'));
            }

            $post_id = intval($_POST['post_id']);
            if (!$post_id) {
                wp_send_json_error(array('message' => 'Invalid event ID'));
            }

            // Update post data
            $post_data = array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($_POST['post_title']),
                'post_status' => sanitize_text_field($_POST['post_status'])
            );

            $result = wp_update_post($post_data);
            if (is_wp_error($result)) {
                wp_send_json_error(array('message' => 'Failed to update event'));
            }

            // Update event meta data
            if (isset($_POST['event_start_datetime'])) {
                update_post_meta($post_id, 'event_start_datetime', sanitize_text_field($_POST['event_start_datetime']));
            }

            if (isset($_POST['event_end_datetime'])) {
                update_post_meta($post_id, 'event_end_datetime', sanitize_text_field($_POST['event_end_datetime']));
            }

            if (isset($_POST['mep_location_venue'])) {
                update_post_meta($post_id, 'mep_location_venue', sanitize_text_field($_POST['mep_location_venue']));
            }

            // Update categories
            if (isset($_POST['mep_cat']) && is_array($_POST['mep_cat'])) {
                $categories = array_map('intval', $_POST['mep_cat']);
                wp_set_post_terms($post_id, $categories, 'mep_cat');
            }

            wp_send_json_success(array('message' => 'Event updated successfully'));
        }

    }
    new MPWEM_Event_Lists();
}