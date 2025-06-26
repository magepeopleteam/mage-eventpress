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
        }

        function mpwem_trash_multiple_posts() {
//            check_ajax_referer('mep-ajax-nonce', 'security');
            if (!current_user_can('delete_posts')) {
                wp_send_json_error(['message' => 'Permission denied']);
            }
            $post_ids = isset($_POST['post_ids']) ? array_map('intval', $_POST['post_ids']) : [];
            foreach ($post_ids as $post_id) {
                if (get_post_status($post_id) !== 'trash') {
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
            
            add_submenu_page('edit.php?post_type=mep_events', __('Event Lists', 'mage-eventpress'), __('Event Lists', 'mage-eventpress'), 'manage_options', 'mep_event_lists', array($this, 'display_event_list'));
        }
        public function display_event_list() {
            require MPWEM_Functions::template_path('layout/event_lists.php');
        }

    }
    new MPWEM_Event_Lists();
}