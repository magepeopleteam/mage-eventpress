<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id        = $event_id ?? 0;
	$backend_order   = isset( $_REQUEST['backend_order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['backend_order'] ) ) : null;
	$link_wc_product = MPWEM_Global_Function::get_post_info( $event_id, 'link_wc_product' );;
    $in_cart = 0;
    $all_dates = MPWEM_Functions::get_dates( $event_id );
    $all_times = MPWEM_Functions::get_times( $event_id, $all_dates );
    $date      = empty( $date ) ? MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times ) : $date;
    $product_id = get_post_meta( $event_id, 'link_wc_product' );
    if ( isset( WC()->cart ) && ! empty( WC()->cart->get_cart() ) && ! empty( $date ) ) {
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            $cart_event_id = isset( $cart_item['event_id'] ) ? $cart_item['event_id'] : 0;
            $cart_event_date = isset( $cart_item['event_cart_date'] ) ? $cart_item['event_cart_date'] : '';
            if ( $cart_event_id == $event_id && ! empty( $cart_event_date ) && $date == $cart_event_date ) {
                $in_cart=1;
            }
        }
    }
?>
<div class="mpwem_summery">
    <div class="total"><?php esc_html_e( 'Total Price : ', 'mage-eventpress' ); ?>
        <span class="mpwem_total"><?php echo wc_price( 0 ); ?></span>
    </div>
	<?php if ( is_admin() && str_contains( wp_get_referer(), 'backend_order' ) ) { ?>
		<?php do_action( 'mpwem_bo_hidden', $event_id ); ?>
        <button type="submit" class="_button_theme">
			<?php esc_html_e( 'Book Now', 'mage-eventpress' ); ?>
        </button>
	<?php } else {
        // Use the WooCommerce checkout flow only when WooCommerce is active AND the
        // global "Enable WooCommerce Payment" setting is on. Otherwise the booking uses
        // the custom (native) payment checkout, which is a PRO feature — its request
        // handler ships with the PRO plugin. We detect it by whether that handler is
        // registered, so the free plugin never renders a non-functional checkout.
        $use_wc_payment    = MPWEM_Global_Function::use_wc_payment();
        $native_available  = has_action( 'wp_ajax_nopriv_mep_native_checkout' ) || has_action( 'wp_ajax_mep_native_checkout' );

        if ( $use_wc_payment || $native_available ) {
        ?>
        <button type="button" class="_button_theme mpwem_book_now">
            <i class='fa fa-shopping-cart _mr_xs'></i>
			<?php esc_html_e( 'Register For This Event', 'mage-eventpress' ); ?>
        </button>
        <?php
            if ( $use_wc_payment ) {
            ?>
            <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $link_wc_product ); ?>" class="dNone mpwem_add_to_cart">
				<?php esc_html_e( 'Register For This Event', 'mage-eventpress' ); ?>
            </button>
            <?php } else {
                // Native (custom payment) mode with the PRO handler present — include the
                // modal and a trigger button.
                require MPWEM_Functions::template_path( 'layout/native_checkout_modal.php' );
                ?>
            <button type="button" class="dNone mpwem_native_checkout_trigger">
				<?php esc_html_e( 'Register For This Event', 'mage-eventpress' ); ?>
            </button>
            <?php }
        } else {
            // Custom payment checkout is a PRO feature and PRO is not active — show an
            // upsell notice instead of a booking button that could not be processed.
            require MPWEM_Functions::template_path( 'layout/native_checkout_pro_notice.php' );
        } ?>
	<?php } ?>
</div>