<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_list_cat_names', 'mep_display_event_cat_name_in_list');
if (!function_exists('mep_display_event_cat_name_in_list')) {
    function mep_display_event_cat_name_in_list()
    {
        ob_start();
?>
        <div class="mep-events-cats-list">
            <?php
            $terms = get_terms(array(
                'taxonomy' => 'mep_cat'
            ));
            ?>
            <div class="mep-event-cat-controls">
                <button type="button" class="mep-cat-control" data-filter="all"><?php _e('All', 'mage-eventpress'); ?></button>
                <?php foreach ($terms as $_terms) { ?>
                    <button type="button" class="mep-cat-control" data-filter=".<?php echo 'mage-' . $_terms->slug; ?>"><?php echo $_terms->name; ?></button>
                <?php } ?>
            </div>
        </div>
    <?php
        $content = ob_get_clean();
        echo apply_filters('mage_event_category_name_filter_list', $content);
    }
}
add_action('mep_event_list_org_names', 'mep_display_event_org_name_in_list');
if (!function_exists('mep_display_event_org_name_in_list')) {
    function mep_display_event_org_name_in_list()
    {
        ob_start();
    ?>
        <div class="mep-events-cats-list">
            <?php
            $terms = get_terms(
                array(
                    'taxonomy' => 'mep_org'
                )
            );
            ?>
            <div class="mep-event-cat-controls">
                <button type="button" class="mep-cat-control" data-filter="all"><?php _e('All', 'mage-eventpress'); ?></button><?php  foreach ($terms as $_terms) {  ?>
                    <button type="button" class="mep-cat-control" data-filter=".<?php echo 'mage-' . $_terms->slug; ?>"><?php echo $_terms->name; ?></button><?php  }  ?>
            </div>
        </div>
    <?php
        $content = ob_get_clean();
        echo apply_filters('mage_event_organization_name_filter_list', $content);
    }
}