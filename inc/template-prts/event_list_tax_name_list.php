<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_list_cat_names', 'mep_display_event_cat_name_in_list',10,2);
if (!function_exists('mep_display_event_cat_name_in_list')) {
    function mep_display_event_cat_name_in_list($cat,$unq_id='')
    {
        ob_start();
?>
        <div class="mep-events-cats-list">
            <?php
            if($cat > 0){
            $terms = get_terms(array(
                'parent' => $cat,
                'taxonomy' => 'mep_cat'
            )); }else{
                $terms = get_terms(array(
                    'taxonomy' => 'mep_cat'
                ));
            }
            ?>
            <div class="mep-event-cat-controls">
                <button type="button" class="mep-cat-control" data-filter="all"><?php _e('All', 'mage-eventpress'); ?></button>
                <?php foreach ($terms as $_terms) { ?>
                    <button type="button" class="mep-cat-control" data-filter=".<?php echo $unq_id.'mage-' . $_terms->term_id; ?>"><?php echo $_terms->name; ?></button>
                <?php } ?>
            </div>
        </div>
    <?php
        $content = ob_get_clean();
        echo apply_filters('mage_event_category_name_filter_list', $content);
    }
}

// 



add_action('mep_event_list_org_names', 'mep_display_event_org_name_in_list',10,2);
if (!function_exists('mep_display_event_org_name_in_list')) {
    function mep_display_event_org_name_in_list($org,$unq_id='')
    {
        ob_start();
    ?>
        <div class="mep-events-cats-list">
            <?php
            if($org > 0){
                $terms = get_terms(array(
                    'parent' => $org,
                    'taxonomy' => 'mep_org'
                )); }else{
                    $terms = get_terms(array(
                        'taxonomy' => 'mep_org'
                    ));
                }
            ?>
            <div class="mep-event-cat-controls">
                <button type="button" class="mep-cat-control" data-filter="all"><?php _e('All', 'mage-eventpress'); ?></button><?php  foreach ($terms as $_terms) {  ?>
                    <button type="button" class="mep-cat-control" data-filter=".<?php echo $unq_id.'mage-' . $_terms->term_id; ?>"><?php echo $_terms->name; ?></button><?php  }  ?>
            </div>
        </div>
    <?php
        $content = ob_get_clean();
        echo apply_filters('mage_event_organization_name_filter_list', $content);
    }
}