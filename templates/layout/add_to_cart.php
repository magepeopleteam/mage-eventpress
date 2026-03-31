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
			<?php esc_html_e( 'Book Now ', 'mage-eventpress' ); ?>
        </button>
	<?php } else { ?>
        <button type="button" class="_button_theme mpwem_book_now" data-in-cart="<?php echo esc_attr( $in_cart ); ?>">
            <i class='fa fa-shopping-cart _mr_xs'></i>
			<?php esc_html_e( 'Register For This Event', 'mage-eventpress' ); ?>
        </button>
        <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $link_wc_product ); ?>" class="dNone mpwem_add_to_cart">
			<?php esc_html_e( 'Register For This Event', 'mage-eventpress' ); ?>
        </button>
	<?php } ?>
</div>