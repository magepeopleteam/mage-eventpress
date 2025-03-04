<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * Register custom block category
 */
function mep_register_block_category($categories) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'magepeople',
                'title' => __('WpEvently - By Magepeople', 'mage-eventpress'),
                'icon'  => 'calendar',
            ),
        )
    );
}
add_filter('block_categories_all', 'mep_register_block_category', 10, 1);

/**
 * Register Event List Block
 */
function mep_register_event_list_block() {
    if (!function_exists('register_block_type')) {
        return;
    }

    // Register block editor script
    wp_register_script(
        'mep-blocks-editor',
        plugins_url('../assets/blocks/event-list-block.js', __FILE__),
        array(
            'wp-blocks',
            'wp-i18n',
            'wp-element',
            'wp-components',
            'wp-editor'
        ),
        filemtime(plugin_dir_path(__FILE__) . '../assets/blocks/event-list-block.js'),
        true
    );

    // Register editor styles
    wp_register_style(
        'mep-blocks-editor',
        plugins_url('../assets/blocks/editor.css', __FILE__),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(__FILE__) . '../assets/blocks/editor.css')
    );

    // Register front-end styles
    wp_register_style(
        'mep-blocks-style',
        plugins_url('../assets/blocks/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . '../assets/blocks/style.css')
    );

    // Register the block
    register_block_type('mage/event-list', array(
        'editor_script' => 'mep-blocks-editor',
        'editor_style' => 'mep-blocks-editor',
        'style' => 'mep-blocks-style',
        'render_callback' => 'mep_render_event_list_block',
        'attributes' => array(
            'cat' => array(
                'type' => 'string',
                'default' => '0'
            ),
            'org' => array(
                'type' => 'string',
                'default' => '0'
            ),
            'style' => array(
                'type' => 'string',
                'default' => 'grid'
            ),
            'column' => array(
                'type' => 'number',
                'default' => 3
            ),
            'cat-filter' => array(
                'type' => 'string',
                'default' => 'no'
            ),
            'org-filter' => array(
                'type' => 'string',
                'default' => 'no'
            ),
            'show' => array(
                'type' => 'string',
                'default' => '-1'
            ),
            'pagination' => array(
                'type' => 'string',
                'default' => 'no'
            ),
            'city' => array(
                'type' => 'string',
                'default' => ''
            ),
            'country' => array(
                'type' => 'string',
                'default' => ''
            ),
            'carousal-nav' => array(
                'type' => 'string',
                'default' => 'yes'
            ),
            'carousal-dots' => array(
                'type' => 'string',
                'default' => 'yes'
            ),
            'carousal-id' => array(
                'type' => 'string',
                'default' => '102448'
            ),
            'timeline-mode' => array(
                'type' => 'string',
                'default' => 'vertical'
            ),
            'sort' => array(
                'type' => 'string',
                'default' => 'ASC'
            ),
            'status' => array(
                'type' => 'string',
                'default' => 'upcoming'
            ),
            'search-filter' => array(
                'type' => 'string',
                'default' => 'no'
            )
        )
    ));
}
add_action('init', 'mep_register_event_list_block');

/**
 * Render callback for the event list block
 */
function mep_render_event_list_block($attributes) {
    // Map block attributes to shortcode attributes
    $shortcode_attrs = array();
    
    // Only add attributes that have values
    foreach ($attributes as $key => $value) {
        if (!empty($value) || $value === '0' || $value === 0) {
            $shortcode_attrs[$key] = $value;
        }
    }

    // Build shortcode attributes string
    $shortcode_string = '';
    foreach ($shortcode_attrs as $key => $value) {
        $shortcode_string .= ' ' . $key . '="' . esc_attr($value) . '"';
    }

    // Create and execute shortcode
    $shortcode = '[event-list' . $shortcode_string . ']';
    return do_shortcode($shortcode);
}

// Ensure block editor assets are loaded
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script('mep-blocks-editor');
    wp_enqueue_style('mep-blocks-editor');
});
