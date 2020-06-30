<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_shortcode_add_cart_section', 'mep_shortcode_add_cart_section_html');
if (!function_exists('mep_shortcode_add_cart_section_html')) {
    function mep_shortcode_add_cart_section_html($event)
    {
?>
        <div class='mep-events-shortcode-cart-section'>
            <div class='mep-events-wrapper'>
                <div class='mep-default-feature-cart-sec'>
                    <?php mep_get_event_reg_btn($event); ?>
                    <?php mep_single_page_js_script($event); //do_action('mep_add_to_cart_shortcode_js',$event); 
                    ?>
                </div>
            </div>
        </div>
<?php
    }
}
