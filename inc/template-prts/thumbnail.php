<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_thumbnail', 'mep_thumbnail');
if (!function_exists('mep_thumbnail')) {
    function mep_thumbnail()
    {
        global $post;
        ob_start();
?>
        <div class="mep-event-thumbnail">
            <?php the_post_thumbnail('full'); ?>
        </div>
<?php
        $content = ob_get_clean();
        echo apply_filters('mage_event_single_thumbnail', $content, $post->ID);
    }
}
