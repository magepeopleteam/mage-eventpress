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
        <button type="button" class="_button_theme mpwem_book_now">
            <i class='fa fa-shopping-cart _mr_xs'></i>
			<?php esc_html_e( 'Register For This Event', 'mage-eventpress' ); ?>
        </button>
        <?php
        $is_woo_active = class_exists( 'WooCommerce' );
        $can_book = true;

        if ( ! $is_woo_active ) {
            if ( class_exists( 'MEP_Payment_Gateway_Manager' ) ) {
                $gateway_manager = MEP_Payment_Gateway_Manager::get_instance();
                $gateways = $gateway_manager->get_available_gateways();
                
                if ( empty( $gateways ) ) {
                    $can_book = false;
                    echo '<div class="mep-payment-warning" style="color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin-bottom: 15px; border-radius: 4px;">';
                    echo esc_html__( 'No payment method is enabled. Please contact the administrator.', 'mage-eventpress' );
                    echo '</div>';
                } else {
                    echo '<div class="mep-payment-gateways" style="margin-bottom: 15px;">';
                    echo '<h4 style="margin-bottom:10px;">' . esc_html__( 'Select Payment Method', 'mage-eventpress' ) . '</h4>';
                    $first = true;
                    foreach ( $gateways as $gateway_id => $gateway ) {
                        $checked = $first ? 'checked="checked"' : '';
                        echo '<label style="display:block; margin-bottom:5px; cursor:pointer;"><input type="radio" name="mep_payment_method" value="' . esc_attr( $gateway_id ) . '" ' . $checked . ' /> ' . esc_html( $gateway->get_title() ) . '</label>';
                        $first = false;
                    }
                    echo '</div>';
                }
            } else {
                $can_book = false;
                echo '<div class="mep-payment-warning" style="color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin-bottom: 15px; border-radius: 4px;">';
                echo esc_html__( 'No payment method is enabled. Please contact the administrator.', 'mage-eventpress' );
                echo '</div>';
            }
        }
        
        if ( $can_book ) {
        ?>
        <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $link_wc_product ); ?>" class="dNone mpwem_add_to_cart">
			<?php esc_html_e( 'Register For This Event', 'mage-eventpress' ); ?>
        </button>
        <?php } ?>
	<?php } ?>
</div>