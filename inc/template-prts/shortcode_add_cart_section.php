<?php
// mep_shortcode_add_cart_section

add_action('mep_shortcode_add_cart_section','mep_shortcode_add_cart_section_html');
function mep_shortcode_add_cart_section_html($event){
    ob_start();
?>
<div class='mep-events-shortcode-cart-section'>
    <div class='mep-events-wrapper'>
        <div class='mep-default-feature-cart-sec'>
            <?php do_action('mep_add_to_cart',$event); ?>
            <?php do_action('mep_add_to_cart_shortcode_js',$event); ?>
        </div>
    </div>
</div>
<?php
    echo ob_get_clean();
}