<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * This is the templates of the event timeline view shortcode
 */

add_filter('mage_event_loop_list_shortcode', 'mep_event_loop_timeline_style', 10, 3);
if (!function_exists('mep_event_loop_timeline_style')) {
    function mep_event_loop_timeline_style($content, $event_id, $style)
    {
        if ($style == 'timeline') {
            $now                        = current_time('Y-m-d H:i:s');
            $show_price                 = mep_get_option('mep_event_price_show', 'general_setting_sec', 'yes');
            $show_price_label           = mep_get_option('event-price-label', 'general_setting_sec', 'Price Starts from:');
            $event_meta                 = get_post_custom($event_id);
            $author_terms               = get_the_terms($event_id, 'mep_org');
            $time                       = strtotime($event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0]);
            $newformat                  = date_i18n('Y-m-d H:i:s', $time);
            $tt                         = get_the_terms($event_id, 'mep_cat');
            $torg                       = get_the_terms($event_id, 'mep_org');
            $org_class                  = mep_get_term_as_class($event_id, 'mep_org');
            $cat_class                  = mep_get_term_as_class($event_id, 'mep_cat');
            $event_multidate            = array_key_exists('mep_event_more_date', $event_meta) ? maybe_unserialize($event_meta['mep_event_more_date'][0]) : array();
            $available_seat             = mep_get_total_available_seat($event_id, $event_meta);
            $hide_org_list              = mep_get_option('mep_event_hide_organizer_list', 'general_setting_sec', 'no');
            $hide_location_list         = mep_get_option('mep_event_hide_location_list', 'general_setting_sec', 'no');
            $hide_time_list             = mep_get_option('mep_event_hide_time_list', 'general_setting_sec', 'no');
            $hide_only_end_time_list    = mep_get_option('mep_event_hide_end_time_list', 'general_setting_sec', 'no');
            $recurring                  = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
            $start_datetime             = $event_meta['event_start_date'][0];
            $end_datetime               = $event_meta['event_end_date'][0];
            ob_start();
            require(mep_template_file_path('list/timeline.php'));
            $content = ob_get_clean();
            return $content;
        } else {
            return $content;
        }
    }
}

add_action('mep_event_shortcode_js_script', 'mep_shortcode_timeline_js_script');
if (!function_exists('mep_shortcode_timeline_js_script')) {
    function mep_shortcode_timeline_js_script($params)
    {
        $cat            = $params['cat'];
        $org            = $params['org'];
        $style          = $params['style'];
        $cat_f          = $params['cat-filter'];
        $org_f          = $params['org-filter'];
        $show           = $params['show'];
        $pagination     = $params['pagination'];
        $sort           = $params['sort'];
        $column         = $style != 'grid' ? 1 : $params['column'];
        $nav            = $params['carousal-nav'] == 'yes' ? 1 : 0;
        $dot            = $params['carousal-dots'] == 'yes' ? 1 : 0;
        $city           = $params['city'];
        $country        = $params['country'];
        $cid            = $params['carousal-id'];
        $tmode            = $params['timeline-mode'];
        $main_div       = $pagination == 'carousal' ? '<div class="mage_grid_box owl-theme owl-carousel"  id="mep-carousel' . $cid . '">' : '<div class="mage_grid_box">';
        ob_start();
        if ($style == 'timeline') {
?>

            jQuery('.timeline').timeline({
            mode: '<?php echo $tmode; ?>',
            visibleItems: 4
            });
<?php
        }
        echo ob_get_clean();
    }
}