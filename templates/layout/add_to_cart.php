<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	//$backend_order = MP_Global_Function::data_sanitize($_POST['backend_order']);
	$link_wc_product = MP_Global_Function::get_post_info($event_id, 'link_wc_product');;
?>
	<div class="col_12 mpwem_form_submit_area mT_xs">
		<div class="justifyBetween _alignCenter">
			<h5 class="_mpBtn"><?php esc_html_e('Total Price : ', 'mage-eventpress'); ?><span class="mpwem_total _textTheme"><?php echo wc_price(0); ?></span></h5>
			<?php if ( is_admin() && str_contains( wp_get_referer(), 'admin_purchase_ticket' ) ) { ?>
				<button type="submit" class="_themeButton">
					<?php esc_html_e('Book Now ', 'mage-eventpress'); ?>
				</button>
			<?php } else { ?>
				<button type="submit" class="_themeButton" name="add-to-cart" value="<?php echo esc_attr($link_wc_product); ?>">
					<?php do_action('mep_before_add_cart_button', $event_id); esc_html_e(mep_get_label($event_id, 'mep_cart_btn_text', __('Register For This Event', 'mage-eventpress')), 'mage-eventpress'); do_action('mep_after_add_cart_button', $event_id); ?>
				</button>
			<?php } ?>
		</div>
	</div>