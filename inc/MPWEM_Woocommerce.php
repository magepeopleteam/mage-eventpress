<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Woocommerce' ) ) {
		class MPWEM_Woocommerce {
			public function __construct() {
				add_filter( 'woocommerce_is_purchasable', array( $this, 'make_event_product_purchasable' ), 10, 2 );
				add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 90, 3 );
				add_action( 'woocommerce_before_calculate_totals', array( $this, 'before_calculate_totals' ) );
				add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 20, 2 );
				add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 10, 90 );
				add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'add_to_cart_redirect' ) );
				/**********************************************/
				add_action( 'woocommerce_after_checkout_validation', array( $this, 'after_checkout_validation' ) );
				add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item' ), 10, 4 );
				add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 10, 4 );
				add_action( 'woocommerce_checkout_order_processed', array( $this, 'checkout_order_processed' ), 90 );
				add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'checkout_order_processed' ), 90 );
				add_action( 'woocommerce_paypal_payments_woocommerce_order_created_from_cart', array( $this, 'express_order_created_from_cart' ), 10, 2 );
				add_action( 'woocommerce_order_status_changed', array( $this, 'repair_orphan_event_booking' ), 5, 4 );
				/**********************************************/
				// Old dashboard - Replaced by MPWEM_My_Account_Dashboard
				// add_action( 'woocommerce_account_dashboard', array( $this, 'account_dashboard' ) );
				add_filter( 'woocommerce_cart_item_price', array( $this, 'cart_item_price' ), 10, 4 );
			}
			/**
			 * Make event-linked WooCommerce products purchasable.
			 * Hidden products (exclude-from-catalog) fail WooCommerce's default is_purchasable check.
			 * Event products must remain purchasable when adding to cart from the event page.
			 *
			 * @param bool       $is_purchasable Whether the product is purchasable.
			 * @param WC_Product $product       The product object.
			 * @return bool
			 */
			public function make_event_product_purchasable( $is_purchasable, $product ) {
				if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
					return $is_purchasable;
				}
				$linked_event_id = get_post_meta( $product->get_id(), 'link_mep_event', true );
				if ( ! empty( $linked_event_id ) && get_post_type( $linked_event_id ) === 'mep_events' && get_post_status( $linked_event_id ) === 'publish' ) {
					return true;
				}
				return $is_purchasable;
			}
			public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
				$linked_event_id = MPWEM_Global_Function::get_post_info( $product_id, 'link_mep_event', $product_id );
				$product_id      = mep_product_exists( $linked_event_id ) ? $linked_event_id : $product_id;
				if ( get_post_type( $product_id ) == 'mep_events' ) {
					$recurring      = MPWEM_Global_Function::get_post_info( $product_id, 'mep_enable_recurring', 'no' );
					$start_date     = isset( $_POST['mep_event_start_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_event_start_date'] ) ) : [];
					$start_date     = current( $start_date );
					$location       = isset( $_POST['mep_event_location_cart'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_event_location_cart'] ) ) : '';
					$recurring_date = $recurring == 'yes' && isset( $_POST['recurring_event_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['recurring_event_date'] ) ) : [];
					$time_slot_text = isset( $_POST['time_slot_name'] ) ? sanitize_text_field( wp_unslash( $_POST['time_slot_name'] ) ) : '';
					$ticket_info    = self::get_cart_ticket_info( $product_id );
					$ticket_price   = self::get_cart_ticket_price( $ticket_info );
					$ex_infos       = self::get_cart_ex_info( $product_id );
					$ex_price       = self::get_cart_ex_price( $ex_infos );
					$user_info      = self::get_attendee_info( $product_id );
					$total_price    = $ticket_price + $ex_price;
					if ( ! empty( $time_slot_text ) ) {
						$cart_item_data['event_everyday_time_slot'] = $time_slot_text;
					}
					$cart_item_data['event_ticket_info']        = $ticket_info;
					$cart_item_data['event_user_info']          = $user_info;
					$cart_item_data['event_tp']                 = $total_price;
					$cart_item_data['line_total']               = $total_price;
					$cart_item_data['line_subtotal']            = $total_price;
					$cart_item_data['event_extra_service']      = $ex_infos;
					$cart_item_data['event_cart_location']      = $location;
					$cart_item_data['event_cart_date']          = $start_date;
					$cart_item_data['event_recurring_date']     = array_unique( $recurring_date );
					$cart_item_data['event_recurring_date_arr'] = $recurring_date;
					$cart_item_data['event_cart_display_date']  = $start_date;
					do_action( 'mep_event_cart_data_reg' );
					$cart_item_data['event_id'] = $product_id;
					mep_temp_attendee_create_for_cart_ticket_array( $product_id, $ticket_info );
					//echo '<pre>';print_r( $cart_item_data );echo '</pre>';die();
					$cart_item_data = apply_filters( 'mep_event_cart_item_data', $cart_item_data, $product_id, $total_price, $user_info, $ticket_info, $ex_infos );
				}
				//echo '<pre>';print_r( $cart_item_data );echo '</pre>';die();
				return $cart_item_data;
			}
			public function before_calculate_totals( $cart_object ) {
				foreach ( $cart_object->cart_contents as $key => $value ) {
					$event_id = is_array($value) && array_key_exists( 'event_id', $value ) ? $value['event_id'] : 0;
					if ( get_post_type( $event_id ) == 'mep_events' ) {
						$event_total_price = $value['event_tp'];
						$value['data']->set_price( $event_total_price );
						$value['data']->set_regular_price( $event_total_price );
						$value['data']->set_sale_price( $event_total_price );
						$value['data']->set_sold_individually( 'yes' );
						$value['data']->get_price();
					}
				}
			}
			public function get_item_data( $item_data, $cart_item ) {
				ob_start();
				$eid = is_array( $cart_item ) && array_key_exists( 'event_id', $cart_item ) ? $cart_item['event_id'] : 0;
				if ( get_post_type( $eid ) == 'mep_events' ) {
					$general_setting_sec  = MPWEM_Global_Function::get_setting( 'general_setting_sec' );
					$hide_location_status = is_array( $general_setting_sec ) && array_key_exists( 'mep_hide_location_from_order_page', $general_setting_sec ) ? $general_setting_sec['mep_hide_location_from_order_page'] : 'no';
					$hide_date_status     = is_array( $general_setting_sec ) && array_key_exists( 'mep_hide_date_from_order_page', $general_setting_sec ) ? $general_setting_sec['mep_hide_date_from_order_page'] : 'no';
					$user_info            = is_array( $cart_item ) && array_key_exists( 'event_user_info', $cart_item ) ? $cart_item['event_user_info'] : [];
					$ticket_type_arr      = is_array( $cart_item ) && array_key_exists( 'event_ticket_info', $cart_item ) ? $cart_item['event_ticket_info'] : [];
					$event_extra_service  = is_array( $cart_item ) && array_key_exists( 'event_extra_service', $cart_item ) ? $cart_item['event_extra_service'] : [];
					$event_date           = is_array( $cart_item ) && array_key_exists( 'event_cart_date', $cart_item ) ? $cart_item['event_cart_date'] : '';
					$date_format          = MPWEM_Global_Function::check_time_exit_date( $event_date ) ? 'full' : 'date';
					$location             = is_array( $cart_item ) && array_key_exists( 'event_cart_location', $cart_item ) ? $cart_item['event_cart_location'] : '';
					$same_attendee        = is_array( $general_setting_sec ) && array_key_exists( 'mep_enable_same_attendee', $general_setting_sec ) ? $general_setting_sec['mep_enable_same_attendee'] : 'no';
					$form_array           = MPWEM_Layout::get_form_array( $eid );
					?>
                    <div class="mep-cart-details" style="display:block;width:100%;margin:6px 0 0;padding:0;font-size:13px;line-height:1.5;color:#2c2c34;text-align:left;">
						<?php if ( $hide_date_status == 'no' || ( $location && $hide_location_status == 'no' ) ) { ?>
                            <div class="mep-cart-details__section" style="display:block;margin:0 0 12px;padding:12px;background:#f7f7f9;border:1px solid #ececf1;">
								<?php if ( $hide_date_status == 'no' ) { ?>
                                    <strong style="color:#6f6f7a;"><?php esc_html_e( 'Date', 'mage-eventpress' ); ?>:</strong>&nbsp;<?php echo esc_html( MPWEM_Global_Function::date_format( $event_date, $date_format, $eid ) ); ?><br />
								<?php } ?>
								<?php if ( $location && $hide_location_status == 'no' ) { ?>
                                    <strong style="color:#6f6f7a;"><?php esc_html_e( 'Location', 'mage-eventpress' ); ?>:</strong>&nbsp;<?php echo esc_html( $location ); ?><br />
								<?php } ?>
                            </div>
						<?php }
							if ( ( $same_attendee == 'yes' || $same_attendee == 'must' ) && is_array( $user_info ) && sizeof( $user_info ) > 0 && is_array( $form_array ) && sizeof( $form_array ) > 0 ) {
								if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
									$_event_type_ci = MPWEM_Global_Function::get_post_info( $eid, 'mep_event_type', 'offline' );
									$_mode_map_ci   = [];
									if ( $_event_type_ci === 'hybrid' ) {
										$_types_ci = get_post_meta( $eid, 'mep_event_ticket_type', true );
										if ( is_array( $_types_ci ) ) {
											foreach ( $_types_ci as $_t ) {
												if ( ! empty( $_t['option_name_t'] ) ) {
													$_mode_map_ci[ $_t['option_name_t'] ] = isset( $_t['option_ticket_mode_t'] ) ? $_t['option_ticket_mode_t'] : 'inperson';
												}
											}
										}
									}
									?>
                                    <div class="mep-cart-details__section" style="display:block;margin:0 0 12px;padding:12px;background:#f7f7f9;border:1px solid #ececf1;">
                                        <p style="margin:0 0 10px;padding:0 0 8px;border-bottom:1px solid #e4e4ec;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#5b5b66;"><?php esc_html_e( 'Ticket Information', 'mage-eventpress' ); ?></p>
                                        <table class="mep-cart-details-table" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
                                            <tbody>
											<?php
												foreach ( $ticket_type_arr as $ticket ) {
													$_badge = '';
													if ( $_event_type_ci === 'hybrid' ) {
														$_mode  = isset( $_mode_map_ci[ $ticket['ticket_name'] ] ) ? $_mode_map_ci[ $ticket['ticket_name'] ] : 'inperson';
														$_label = $_mode === 'online' ? esc_html__( 'Online Event', 'mage-eventpress' ) : esc_html__( 'In Person', 'mage-eventpress' );
														$_cls   = $_mode === 'online' ? 'mep-ticket-mode-badge--online' : 'mep-ticket-mode-badge--inperson';
														$_badge = ' <span class="mep-ticket-mode-badge ' . $_cls . '" style="display:inline-block;margin-left:6px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:700;line-height:1.4;vertical-align:middle;">' . $_label . '</span>';
													}
													$line_total  = (float) $ticket['ticket_price'] * (float) $ticket['ticket_qty'];
													$ticket_text = '<tr>'
														. '<td style="padding:8px 0;vertical-align:top;border-bottom:1px solid #ececf1;font-weight:600;color:#1f1f27;">' . esc_html( $ticket['ticket_name'] ) . $_badge . '</td>'
														. '<td style="padding:8px 8px;vertical-align:top;border-bottom:1px solid #ececf1;text-align:right;white-space:nowrap;color:#6f6f7a;font-size:12px;">' . wc_price( (float) $ticket['ticket_price'] ) . ' &times; ' . esc_html( $ticket['ticket_qty'] ) . '</td>'
														. '<td style="padding:8px 0 8px 8px;vertical-align:top;border-bottom:1px solid #ececf1;text-align:right;white-space:nowrap;font-weight:700;color:#1f1f27;">' . wc_price( $line_total ) . '</td>'
														. '</tr>';
													echo apply_filters( 'mpwem_display_ticket_in_cart_list', $ticket_text, $ticket, $eid );
													do_action( 'mep_cart_after_ticket_type', $ticket );
												}
											?>
                                            </tbody>
                                        </table>
                                    </div>
									<?php
								}
								$user = current( $user_info );
								self::show_attendee( $user, $form_array );
							} else {
								if ( is_array( $user_info ) && sizeof( $user_info ) > 0 ) {
									foreach ( $user_info as $user ) {
										self::show_attendee( $user, $form_array, 'no' );
									}
								}
							}
							if ( is_array( $event_extra_service ) && sizeof( $event_extra_service ) > 0 ) {
								?>
                                <div class="mep-cart-details__section" style="display:block;margin:0 0 12px;padding:12px;background:#f7f7f9;border:1px solid #ececf1;">
                                    <p style="margin:0 0 10px;padding:0 0 8px;border-bottom:1px solid #e4e4ec;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#5b5b66;"><?php esc_html_e( 'Extra Service', 'mage-eventpress' ); ?></p>
                                    <table class="mep-cart-details-table" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">
                                        <tbody>
										<?php foreach ( $event_extra_service as $extra_service ) {
											$ex_total = (float) $extra_service['service_price'] * (float) $extra_service['service_qty'];
											echo '<tr>'
												. '<td style="padding:8px 0;vertical-align:top;border-bottom:1px solid #ececf1;font-weight:600;color:#1f1f27;">' . esc_html( $extra_service['service_name'] ) . '</td>'
												. '<td style="padding:8px 8px;vertical-align:top;border-bottom:1px solid #ececf1;text-align:right;white-space:nowrap;color:#6f6f7a;font-size:12px;">' . wc_price( (float) $extra_service['service_price'] ) . ' &times; ' . esc_html( $extra_service['service_qty'] ) . '</td>'
												. '<td style="padding:8px 0 8px 8px;vertical-align:top;border-bottom:1px solid #ececf1;text-align:right;white-space:nowrap;font-weight:700;color:#1f1f27;">' . wc_price( $ex_total ) . '</td>'
												. '</tr>';
										} ?>
                                        </tbody>
                                    </table>
                                </div>
								<?php
							}
						?>
                    </div>
					<?php
					do_action( 'mep_after_cart_item_display_list', $cart_item );
				}
				$item_data[] = array( 'key' => __( 'Details Information', 'mage-eventpress' ), 'value' => ob_get_clean() );
				return $item_data;
			}
			public function after_checkout_validation( $posted ) {
				global $woocommerce;
				$items = $woocommerce->cart->get_cart();
				foreach ( $items as $item => $values ) {
					$event_id        = is_array($values) && array_key_exists( 'event_id', $values ) ? $values['event_id'] : 0; // $values['event_id'];
					$check_seat_plan = get_post_meta( $event_id, 'mepsp_event_seat_plan_info', true ) ? get_post_meta( $event_id, 'mepsp_event_seat_plan_info', true ) : array();
					if ( get_post_type( $event_id ) == 'mep_events' && is_array( $check_seat_plan ) && sizeof( $check_seat_plan ) == 0 ) {
						$total_seat = apply_filters( 'mep_event_total_seat_counts', mep_event_total_seat( $event_id, 'total' ), $event_id );
						$total_resv = apply_filters( 'mep_event_total_resv_seat_count', mep_event_total_seat( $event_id, 'resv' ), $event_id );
						$ticket_arr = $values['event_ticket_info'];
						foreach ( $ticket_arr as $ticket ) {
							$event_name        = get_the_title( $event_id );
							$type              = $ticket['ticket_name'];
							$event_date        = $ticket['event_date'];
							$ticket_qty        = $ticket['ticket_qty'];
							$event_date_txt    = get_mep_datetime( $ticket['event_date'], 'date-time-text' );
							$total_sold        = mep_ticket_type_sold( $event_id, $type, $event_date );
							$total_seats_count = apply_filters( 'mep_event_total_seat_count_checkout', $total_seat, $event_id, $event_date );
							$available_seat    = (int) $total_seats_count - ( (int) $total_resv + (int) $total_sold );
						}
						if ( $ticket_qty > $available_seat ) {
							wc_add_notice( "Sorry, $type not available. Total available $type is $available_seat of $event_name on $event_date_txt but you select $ticket_qty . Please Try Again", 'error' );
						}
					}
				}
			}
			public function add_to_cart_validation( $passed ) {
				$wc_product_id   = isset( $_REQUEST['add-to-cart'] ) ? sanitize_text_field( $_REQUEST['add-to-cart'] ) : '';
				$product_id      = isset( $_REQUEST['add-to-cart'] ) ? sanitize_text_field( $_REQUEST['add-to-cart'] ) : '';
				$linked_event_id = get_post_meta( $product_id, 'link_mep_event', true ) ? get_post_meta( $product_id, 'link_mep_event', true ) : $product_id;
				$product_id      = mep_product_exists( $linked_event_id ) ? $linked_event_id : $product_id;
				$event_id        = $product_id;
				if ( get_post_type( $event_id ) == 'mep_events' ) {
					$not_in_the_cart = apply_filters( 'mep_check_product_into_cart', true, $wc_product_id );
					if ( ! $not_in_the_cart ) {
						// Check if it's a date conflict
						$current_event_date = isset( $_POST['mep_event_start_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_event_start_date'] ) ) : [];
						$current_event_date = ! empty( $current_event_date ) ? current( $current_event_date ) : '';
						
						// Check cart for same event with different date
						$has_date_conflict = false;
						if ( isset( WC()->cart ) && ! empty( WC()->cart->get_cart() ) && ! empty( $current_event_date ) ) {
							foreach ( WC()->cart->get_cart() as $cart_item ) {
								$cart_event_id = isset( $cart_item['event_id'] ) ? $cart_item['event_id'] : 0;
								$cart_event_date = isset( $cart_item['event_cart_date'] ) ? $cart_item['event_cart_date'] : '';
								if ( $cart_event_id == $event_id && ! empty( $cart_event_date ) && $current_event_date == $cart_event_date ) {
									$passed = false;
									break;
								}
							}
						}
						
						if (!$passed ) {
							wc_add_notice( __( "This event has already been added to the shopping cart. To change the quantity, please remove it from the cart and add it back again.", 'mage-eventpress' ), 'error' );
							// Event single pages never call wc_print_notices(), so without this
							// redirect the error stays invisible while the page silently reloads.
							if ( ! wp_doing_ajax() && ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
								wp_safe_redirect( wc_get_cart_url() );
								exit;
							}
						}

					}
				}
				return $passed;
			}
			public function add_to_cart_redirect( $wc_get_cart_url ) {
				$redirect_status = mep_get_option( 'mep_event_direct_checkout', 'general_setting_sec', 'yes' );
				if ( $redirect_status == 'yes' ) {
					$wc_get_cart_url = wc_get_checkout_url();
				}
				return $wc_get_cart_url;
			}
			public function checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
				self::add_event_line_item_meta( $item, $values );
			}
			/**
			 * Rebuild the booking on an order that was created outside WooCommerce checkout.
			 *
			 * WooCommerce PayPal Payments' express buttons (the product, cart and express
			 * checkout placements) never run WC_Checkout - they hand-build the order in
			 * Button\Helper\WooCommerceOrderCreator, copying only the product, quantity and
			 * totals onto each line item. So neither woocommerce_checkout_create_order_line_item
			 * nor woocommerce_checkout_order_processed fires: the line item loses event_id,
			 * _event_ticket_info and _event_user_info, no attendee is ever created, and the
			 * order never appears on the event. The payment still succeeds, so the booking
			 * disappears silently.
			 *
			 * PayPal does fire this action once it has built the order, handing over the cart
			 * it used - WC_Cart::get_cart_for_session(), which keeps our custom cart keys - and
			 * it stamps every line item with _bundle_cart_key, the cart item key, so each line
			 * item maps back to the cart item it came from. That pairing is enough to run the
			 * two skipped steps and leave the order identical to a checkout-placed one.
			 *
			 * @param WC_Order $order     Order PayPal has just created.
			 * @param object   $cart_data CartData wrapper around the cart it was built from.
			 * @return void
			 */
			public function express_order_created_from_cart( $order, $cart_data ) {
				if ( ! is_a( $order, 'WC_Order' ) || ! is_object( $cart_data ) || ! method_exists( $cart_data, 'items' ) ) {
					return;
				}
				$cart_items = $cart_data->items();
				if ( ! is_array( $cart_items ) || sizeof( $cart_items ) == 0 ) {
					return;
				}
				$rebuilt = false;
				$claimed = [];
				foreach ( $order->get_items() as $item ) {
					if ( ! is_a( $item, 'WC_Order_Item_Product' ) || $item->get_meta( 'event_id' ) ) {
						continue; // Already carries a booking, nothing to rebuild.
					}
					$cart_key = $item->get_meta( '_bundle_cart_key' );
					if ( ! $cart_key || ! array_key_exists( $cart_key, $cart_items ) ) {
						$cart_key = self::match_cart_item_by_product( $item, $cart_items, $claimed );
					}
					if ( ! $cart_key ) {
						continue;
					}
					$values   = $cart_items[ $cart_key ];
					$event_id = is_array( $values ) && array_key_exists( 'event_id', $values ) ? $values['event_id'] : 0;
					if ( get_post_type( $event_id ) != 'mep_events' ) {
						continue;
					}
					$claimed[ $cart_key ] = true;
					self::add_event_line_item_meta( $item, $values );
					$item->save();
					$rebuilt = true;
				}
				if ( $rebuilt ) {
					$order->save();
					$this->checkout_order_processed( $order->get_id() );
				}
			}
			/**
			 * Fall back to pairing a line item with its cart item on product id.
			 *
			 * Only used when _bundle_cart_key is absent - older and future PayPal releases
			 * are not obliged to set it. Claimed keys are skipped so that two line items for
			 * the same event (the same event booked twice on different dates) cannot both
			 * resolve to the same cart item.
			 *
			 * @param WC_Order_Item_Product $item       Line item being matched.
			 * @param array                 $cart_items Cart items keyed by cart item key.
			 * @param array                 $claimed    Cart item keys already paired off.
			 * @return string Matching cart item key, or '' when there is no unambiguous match.
			 */
			private static function match_cart_item_by_product( $item, $cart_items, $claimed ) {
				foreach ( $cart_items as $key => $values ) {
					if ( array_key_exists( $key, $claimed ) || ! is_array( $values ) ) {
						continue;
					}
					$product_id   = array_key_exists( 'product_id', $values ) ? (int) $values['product_id'] : 0;
					$variation_id = array_key_exists( 'variation_id', $values ) ? (int) $values['variation_id'] : 0;
					if ( $product_id == (int) $item->get_product_id() && $variation_id == (int) $item->get_variation_id() ) {
						return $key;
					}
				}
				return '';
			}
			/**
			 * Last-resort repair for a paid order whose event booking never got attached.
			 *
			 * express_order_created_from_cart() covers PayPal, but any gateway that builds the
			 * order itself instead of running WC_Checkout has the same hole, and a booking lost
			 * that way is invisible: the payment succeeds, the customer gets a normal order
			 * email, and only the event organiser eventually notices the attendee is missing.
			 * This runs on every status change and rebuilds anything still orphaned, so a
			 * booking can never silently disappear again.
			 *
			 * What it can rebuild is limited by what survives on the order. The registration
			 * form answers only ever existed in the cart session, so they are gone for good;
			 * the event, ticket type, quantity, price and date are all recoverable. Leaving
			 * event_user_info empty makes checkout_order_processed() fall back to EventPress's
			 * own billing-details attendee, which is the same thing it does for an event that
			 * collects no registration form. An order note records what happened so staff know
			 * to collect the missing answers.
			 *
			 * Runs before order_status_changed() (priority 10) so that once an order is
			 * repaired the normal status handling sees an ordinary booking.
			 *
			 * @param int      $order_id    Order being transitioned.
			 * @param string   $from_status Previous status.
			 * @param string   $to_status   New status.
			 * @param WC_Order $order       Order object.
			 * @return void
			 */
			public function repair_orphan_event_booking( $order_id, $from_status, $to_status, $order ) {
				$skip_status = apply_filters( 'mep_skip_booking_repair_status', array( 'failed', 'cancelled', 'refunded', 'trash', 'draft', 'checkout-draft' ) );
				if ( in_array( $to_status, $skip_status, true ) ) {
					return;
				}
				if ( ! is_a( $order, 'WC_Order' ) ) {
					$order = wc_get_order( $order_id );
				}
				if ( ! is_a( $order, 'WC_Order' ) || $order->get_meta( '_mep_booking_auto_repaired' ) ) {
					return;
				}
				$repaired = [];
				foreach ( $order->get_items() as $item ) {
					if ( ! is_a( $item, 'WC_Order_Item_Product' ) || $item->get_meta( 'event_id' ) ) {
						continue;
					}
					$event_id = self::resolve_event_for_product( $item->get_product_id() );
					if ( ! $event_id ) {
						continue; // Ordinary product, nothing to do.
					}
					$ticket_info = self::rebuild_ticket_info( $item, $event_id );
					if ( sizeof( $ticket_info ) == 0 ) {
						continue;
					}
					$event_date = array_key_exists( 'event_date', $ticket_info[0] ) ? $ticket_info[0]['event_date'] : '';
					self::add_event_line_item_meta( $item, array(
						'event_id'            => $event_id,
						'event_ticket_info'   => $ticket_info,
						'event_user_info'     => [],
						'event_extra_service' => [],
						'event_extra_option'  => [],
						'event_cart_location' => '',
						'event_cart_date'     => $event_date,
					) );
					$item->save();
					$repaired[] = get_the_title( $event_id );
				}
				if ( sizeof( $repaired ) == 0 ) {
					return;
				}
				$order->update_meta_data( '_mep_booking_auto_repaired', current_time( 'mysql' ) );
				$order->save();
				$order->add_order_note( sprintf(
				/* translators: %s: comma separated list of event names. */
					esc_html__( 'EventPress: this order reached the site without its booking details, so the booking has been rebuilt automatically for %s. The attendee was created from the billing details - the registration form answers were not recoverable and need to be collected from the customer.', 'mage-eventpress' ),
					implode( ', ', $repaired )
				) );
				do_action( 'mep_event_booking_repaired', $order->get_id(), $repaired );
				$this->checkout_order_processed( $order->get_id() );
			}
			/**
			 * Resolve the event a purchased product belongs to.
			 *
			 * @param int $product_id Purchased product id.
			 * @return int Event id, or 0 when the product is not an event product.
			 */
			private static function resolve_event_for_product( $product_id ) {
				if ( ! $product_id ) {
					return 0;
				}
				if ( get_post_type( $product_id ) == 'mep_events' ) {
					return (int) $product_id;
				}
				$event_id = MPWEM_Global_Function::get_post_info( $product_id, 'link_mep_event', 0 );
				return get_post_type( $event_id ) == 'mep_events' ? (int) $event_id : 0;
			}
			/**
			 * Reconstruct the ticket payload for a line item that lost it.
			 *
			 * The ticket type is identified by matching the price actually paid against the
			 * event's ticket types, which is exact whenever the prices differ. When nothing
			 * matches - a since-edited price, or a ticket type that has been removed - the
			 * price paid is kept and the first ticket type lends its name, so the booking is
			 * still recorded rather than dropped.
			 *
			 * @param WC_Order_Item_Product $item     Line item being rebuilt.
			 * @param int                   $event_id Event the item belongs to.
			 * @return array Ticket info payload, shaped as the cart would have built it.
			 */
			private static function rebuild_ticket_info( $item, $event_id ) {
				$qty = (int) $item->get_quantity();
				if ( $qty < 1 ) {
					$qty = 1;
				}
				$unit_price   = round( (float) $item->get_subtotal() / $qty, 2 );
				$ticket_types = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
				$ticket_name  = '';
				$fallback     = '';
				if ( is_array( $ticket_types ) ) {
					foreach ( $ticket_types as $ticket_type ) {
						if ( ! is_array( $ticket_type ) || ! array_key_exists( 'option_name_t', $ticket_type ) ) {
							continue;
						}
						$name = html_entity_decode( urldecode( $ticket_type['option_name_t'] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
						if ( ! $name ) {
							continue;
						}
						$fallback = $fallback ? $fallback : $name;
						$price    = array_key_exists( 'option_price_t', $ticket_type ) ? round( (float) $ticket_type['option_price_t'], 2 ) : 0;
						if ( abs( $price - $unit_price ) < 0.01 ) {
							$ticket_name = $name;
							break;
						}
					}
				}
				$ticket_name = $ticket_name ? $ticket_name : $fallback;
				if ( ! $ticket_name ) {
					$ticket_name = $item->get_name();
				}
				$event_date = MPWEM_Global_Function::get_post_info( $event_id, 'event_start_datetime', '' );
				return array(
					array(
						'ticket_name'  => $ticket_name,
						'ticket_price' => $unit_price,
						'ticket_qty'   => $qty,
						'max_qty'      => 0,
						'event_date'   => $event_date,
						'event_id'     => (string) $event_id,
					),
				);
			}
			/**
			 * Attach the event booking details to a WooCommerce order line item.
			 *
			 * Everything a customer sees about a booking in the order emails, the order
			 * screen and the PDF ticket comes from this meta: the visible rows (Date,
			 * each ticket type with its price, every registration-form field, extra
			 * services, Location) plus the hidden `_event_*` payloads the PDF/CSV/attendee
			 * modules read back.
			 *
			 * It used to live inline in checkout_create_order_line_item(), which meant only
			 * a frontend checkout produced it — orders created from the admin "Book an Event"
			 * screen wrote just the hidden payloads, so their emails showed no attendee
			 * details at all. Both paths now share this one builder, so an admin-created
			 * order carries exactly the same information as a customer-placed one.
			 *
			 * @param WC_Order_Item_Product $item   Line item being built (not yet saved).
			 * @param array                 $values Cart-item shaped data for the booking.
			 * @return void
			 */
			public static function add_event_line_item_meta( $item, $values ) {
				$eid           = is_array($values) && array_key_exists( 'event_id', $values ) ? $values['event_id'] : 0; //$values['event_id'];
				$location_text = mep_get_option( 'mep_location_text_x', 'label_setting_sec', esc_html__( 'Location', 'mage-eventpress' ) );
				$date_text     = mep_get_option( 'mep_event_date_text_x', 'label_setting_sec', esc_html__( 'Date', 'mage-eventpress' ) );
				if ( get_post_type( $eid ) == 'mep_events' ) {
					$event_id                = $eid;
					$mep_events_extra_prices = is_array($values) && array_key_exists( 'event_extra_option', $values ) ? $values['event_extra_option'] : [];
					$cart_location           = is_array($values) && array_key_exists( 'event_cart_location', $values ) ? $values['event_cart_location'] : '';
					$event_extra_service     = is_array($values) && array_key_exists( 'event_extra_service', $values ) ? $values['event_extra_service'] : [];
					$ticket_type_arr         = is_array($values) && array_key_exists( 'event_ticket_info', $values ) ? $values['event_ticket_info'] : '';
					$event_cart_date_raw     = is_array($values) && array_key_exists( 'event_cart_date', $values ) ? $values['event_cart_date'] : '';
					$cart_date               = ! empty( $event_cart_date_raw ) ? $event_cart_date_raw : '';
					$event_user_info         = is_array($values) && array_key_exists( 'event_user_info', $values ) && is_array( $values['event_user_info'] ) ? $values['event_user_info'] : [];
					$recurring               = get_post_meta( $eid, 'mep_enable_recurring', true ) ? get_post_meta( $eid, 'mep_enable_recurring', true ) : 'no';
					$time_status             = get_post_meta( $eid, 'mep_disable_ticket_time', true ) ? get_post_meta( $eid, 'mep_disable_ticket_time', true ) : 'no';
					if ( $recurring == 'everyday' && $time_status == 'no' ) {
						if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
							$count = 1;
							foreach ( $ticket_type_arr as $_event_recurring_date ) {
								if($count == 1){
									$event_date_value = is_array($_event_recurring_date) && array_key_exists( 'event_date', $_event_recurring_date ) ? $_event_recurring_date['event_date'] : '';
									if ( ! empty( $event_date_value ) ) {
										$item->add_meta_data( $date_text, get_mep_datetime( $event_date_value, apply_filters( 'mep_cart_date_format', 'date-time-text' )) );
									}
								}
								$count++;
								}
						}
					} elseif ( $recurring == 'yes' ) {
						if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
							$count = 1;
							foreach ( $ticket_type_arr as $_event_recurring_date ) {
								if($count == 1){
									$event_date_value = is_array($_event_recurring_date) && array_key_exists( 'event_date', $_event_recurring_date ) ? $_event_recurring_date['event_date'] : '';
									if ( ! empty( $event_date_value ) ) {
										$item->add_meta_data( $date_text, get_mep_datetime( $event_date_value, apply_filters( 'mep_cart_date_format', 'date-time-text' ),$event_id ) );
									}
								}
							$count++;
							}
						}
					} else {
						if ( ! empty( $cart_date ) ) {
							$item->add_meta_data( $date_text, get_mep_datetime( $cart_date, apply_filters( 'mep_cart_date_format', 'date-time-text' ), $event_id ) );
						}
					}
					if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
						mep_cart_order_data_save_ticket_type( $item, $ticket_type_arr, $eid );
					}
					$custom_forms_id = mep_get_user_custom_field_ids( $eid );
					foreach ( $event_user_info as $userinf ) {
						if ( is_array($userinf) && array_key_exists( 'user_name', $userinf ) && ! empty( $userinf['user_name'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Name' ), $userinf['user_name'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_email', $userinf ) && ! empty( $userinf['user_email'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Email' ), $userinf['user_email'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_phone', $userinf ) && ! empty( $userinf['user_phone'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Phone' ), $userinf['user_phone'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_address', $userinf ) && ! empty( $userinf['user_address'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Address' ), $userinf['user_address'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_gender', $userinf ) && ! empty( $userinf['user_gender'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Gender' ), $userinf['user_gender'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_tshirtsize', $userinf ) && ! empty( $userinf['user_tshirtsize'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'T-Shirt Size' ), $userinf['user_tshirtsize'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_company', $userinf ) && ! empty( $userinf['user_company'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Company' ), $userinf['user_company'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_designation', $userinf ) && ! empty( $userinf['user_designation'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Designation' ), $userinf['user_designation'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_website', $userinf ) && ! empty( $userinf['user_website'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Website' ), $userinf['user_website'] );
						}
						if ( is_array($userinf) && array_key_exists( 'user_vegetarian', $userinf ) && ! empty( $userinf['user_vegetarian'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Vegetarian' ), $userinf['user_vegetarian'] );
						}
						if ( is_array( $custom_forms_id ) && sizeof( $custom_forms_id ) > 0 ) {
							foreach ( $custom_forms_id as $key => $value ) {
								// A form-builder field the attendee left blank (or one added to the
								// form after this booking was configured) has no key in $userinf.
								if ( is_array( $userinf ) && array_key_exists( $value, $userinf ) ) {
									$item->add_meta_data( $key, $userinf[ $value ] );
								}
							}
						}
					}
					if ( is_array( $event_extra_service ) && sizeof( $event_extra_service ) > 0 ) {
						foreach ( $event_extra_service as $extra_service ) {
							$service_type_name = $extra_service['service_name'] . " - " . wc_price( $extra_service['service_price'] ) . ' x ' . $extra_service['service_qty'] . ' = ';
							$service_type_val  = wc_price( (float) $extra_service['service_price'] * (float) $extra_service['service_qty']  );
							$item->add_meta_data( $service_type_name, $service_type_val );
						}
					}
					$item->add_meta_data( $location_text, $cart_location );
					$item->add_meta_data( '_event_ticket_info', $ticket_type_arr );
					$item->add_meta_data( '_event_user_info', $event_user_info );
					$item->add_meta_data( '_event_service_info', $mep_events_extra_prices );
					$item->add_meta_data( 'event_id', $eid );
					// $item->add_meta_data('_product_id', $eid);
					$item->add_meta_data( '_event_extra_service', $event_extra_service );
					do_action( 'mep_event_cart_order_data_add', $values, $item );
				}
			}
			public function order_status_changed( $order_id, $from_status, $to_status, $order ) {
				// Getting an instance of the order object
				$order                = wc_get_order( $order_id );
				$order_meta           = mep_get_order_meta_map( $order_id );
				$email                = isset( $order_meta['_billing_email'][0] ) ? $order_meta['_billing_email'][0] : $order->get_billing_email();
				// Resolved centrally so the runtime agrees with the Email Settings screen.
				$email_send_status    = mep_get_email_sending_order_statuses();
				$enable_billing_email = mep_get_option( 'mep_send_confirmation_to_billing_email', 'email_setting_sec', 'enable' );
				//  mep_email_sending_order_status
				$order_status = $order->get_status();
				$cn           = 1;
				$event_arr    = [];
				foreach ( $order->get_items() as $item_id => $item_values ) {
					$event_id    = MPWEM_Global_Function::get_order_item_meta( $item_id, 'event_id' );
					$event_arr[] = $event_id;
					if ( get_post_type( $event_id ) == 'mep_events' ) {
						$event_ticket_info_arr = wc_get_order_item_meta( $item_id, '_event_ticket_info', true );


						$org                   = get_the_terms( $event_id, 'mep_org' );
						$term_id               = isset( $org[0]->term_id ) ? $org[0]->term_id : '';
						$org_email             = get_term_meta( $term_id, 'org_email', true ) ? get_term_meta( $term_id, 'org_email', true ) : '';
						if ( $order->has_status( 'processing' ) ) {
							change_attandee_order_status( $order_id, 'publish', 'trash', 'processing' );
							change_attandee_order_status( $order_id, 'publish', 'publish', 'processing' );
							change_extra_service_status( $order_id, 'publish', 'trash', 'processing' );
							change_extra_service_status( $order_id, 'publish', 'publish', 'processing' );
							do_action( 'mep_wc_order_status_change', $order_status, $event_id, $order_id );
							if ( $enable_billing_email == 'enable' ) {
								if ( in_array( 'processing', $email_send_status ) ) {
									mep_event_confirmation_email_sent( $event_id, $email, $order_id, 0, $event_ticket_info_arr );
								}
							}
						}
						if ( $order->has_status( 'pending' ) ) {
							change_attandee_order_status( $order_id, 'publish', 'trash', 'pending' );
							change_attandee_order_status( $order_id, 'publish', 'publish', 'pending' );
							change_extra_service_status( $order_id, 'publish', 'trash', 'pending' );
							change_extra_service_status( $order_id, 'publish', 'publish', 'pending' );
							do_action( 'mep_wc_order_status_change', $order_status, $event_id, $order_id );
						}
						if ( $order->has_status( 'on-hold' ) ) {
							change_attandee_order_status( $order_id, 'publish', 'trash', 'on-hold' );
							change_attandee_order_status( $order_id, 'publish', 'publish', 'on-hold' );
							do_action( 'mep_wc_order_status_change', $order_status, $event_id, $order_id );
						}
						if ( $order->has_status( 'completed' ) ) {
							change_attandee_order_status( $order_id, 'publish', 'trash', 'completed' );
							change_attandee_order_status( $order_id, 'publish', 'publish', 'completed' );
							change_extra_service_status( $order_id, 'publish', 'trash', 'completed' );
							change_extra_service_status( $order_id, 'publish', 'publish', 'completed' );
							do_action( 'mep_wc_order_status_change', $order_status, $event_id, $order_id );
							if ( function_exists( 'mep_should_send_billing_confirmation' ) && mep_should_send_billing_confirmation( 'completed' ) ) {
								mep_event_confirmation_email_sent( $event_id, $email, $order_id, 0, $event_ticket_info_arr );
							}
							if ( in_array( 'completed', $email_send_status, true ) && ! empty( $org_email ) ) {
								mep_event_confirmation_email_sent( $event_id, $org_email, $order_id, 0, $event_ticket_info_arr );
							}
						}
						if ( $order->has_status( 'cancelled' ) ) {
							change_attandee_order_status( $order_id, 'trash', 'publish', 'cancelled' );
							change_extra_service_status( $order_id, 'trash', 'publish', 'cancelled' );
							do_action( 'mep_wc_order_status_change', $order_status, $event_id, $order_id );
						}
						if ( $order->has_status( 'refunded' ) ) {
							change_attandee_order_status( $order_id, 'trash', 'publish', 'refunded' );
							change_extra_service_status( $order_id, 'trash', 'publish', 'refunded' );
							do_action( 'mep_wc_order_status_change', $order_status, $event_id, $order_id );
						}
						if ( $order->has_status( 'failed' ) ) {
							change_attandee_order_status( $order_id, 'trash', 'publish', 'failed' );
							change_extra_service_status( $order_id, 'trash', 'publish', 'failed' );
							do_action( 'mep_wc_order_status_change', $order_status, $event_id, $order_id );
						}
						mep_update_event_seat_inventory( $event_id, $event_ticket_info_arr );
						do_action( 'mep_wc_order_status_change_single', $order_status, $event_id, $order_id, $cn, $event_arr );
					} // End of Post Type Check
					$cn ++;
				} // End order item foreach
			} // End Function
			/**
			 * Count how many attendees an order should end up with, per event and event date.
			 *
			 * One order can carry several line items for the same recurring event on different
			 * dates - the customer books 3, 10 and 17 September of the same weekly class in one
			 * go. The duplicate guard below therefore has to be counted per event date: counted
			 * per event, the attendee created for the first date made the guard believe every
			 * later line item had already been handled, so only the first date ever got an
			 * attendee record and only the first date's seat count went down.
			 *
			 * Counting order wide, rather than per line item, also keeps two line items that
			 * share one date (the same date bought as two different ticket types) from
			 * cancelling each other out, while still stopping a second run of this method from
			 * duplicating attendees that already exist.
			 *
			 * @param WC_Order $order Order being processed.
			 * @return array Map of "<event id>|<event date>" => number of attendees expected.
			 */
			private static function count_expected_attendees_per_date( $order ) {
				$expected = array();
				if ( ! $order instanceof WC_Order ) {
					return $expected;
				}
				foreach ( $order->get_items() as $item_id => $item_values ) {
					$event_id = wc_get_order_item_meta( $item_id, 'event_id', true );
					if ( get_post_type( $event_id ) != 'mep_events' ) {
						continue;
					}
					$user_info_arr = wc_get_order_item_meta( $item_id, '_event_user_info', true );
					if ( is_array( $user_info_arr ) && sizeof( $user_info_arr ) > 0 ) {
						foreach ( $user_info_arr as $_user_info ) {
							$date = is_array( $_user_info ) && array_key_exists( 'user_event_date', $_user_info ) ? $_user_info['user_event_date'] : '';
							$key  = $event_id . '|' . $date;
							$expected[ $key ] = ( array_key_exists( $key, $expected ) ? $expected[ $key ] : 0 ) + 1;
						}
						continue;
					}
					// No registration form on this event, so mep_attendee_create() is called once per
					// ticket from the billing details instead - count the ticket quantities.
					$event_ticket_info_arr = wc_get_order_item_meta( $item_id, '_event_ticket_info', true );
					if ( ! is_array( $event_ticket_info_arr ) ) {
						continue;
					}
					foreach ( $event_ticket_info_arr as $tinfo ) {
						$qty = is_array( $tinfo ) && array_key_exists( 'ticket_qty', $tinfo ) ? (int) $tinfo['ticket_qty'] : 0;
						if ( $qty < 1 ) {
							continue;
						}
						$date = is_array( $tinfo ) && array_key_exists( 'event_date', $tinfo ) ? $tinfo['event_date'] : '';
						$key  = $event_id . '|' . $date;
						$expected[ $key ] = ( array_key_exists( $key, $expected ) ? $expected[ $key ] : 0 ) + $qty;
					}
				}
				return $expected;
			}
			/**
			 * Read one entry out of the map built by count_expected_attendees_per_date().
			 *
			 * @param array  $expected   Map returned by count_expected_attendees_per_date().
			 * @param int    $event_id   Event the line item belongs to.
			 * @param string $event_date Event date on the line item.
			 * @return int Attendees expected for that event date, at least one.
			 */
			private static function expected_attendee_count( $expected, $event_id, $event_date ) {
				$key = $event_id . '|' . $event_date;
				return is_array( $expected ) && array_key_exists( $key, $expected ) ? (int) $expected[ $key ] : 1;
			}
			public function checkout_order_processed( $order_id ) {
				global $woocommerce;
				$result   = ! is_numeric( $order_id ) ? json_decode( $order_id ) : [ 0 ];
				$order_id = ! is_numeric( $order_id ) ? $result->id : $order_id;
				if ( ! $order_id ) {
					return;
				}
				// Getting an instance of the order object
				$order        = wc_get_order( $order_id );
				$order_status = $order->get_status();
				if ( $order_status != 'failed' ) {
					$expected_attendees = self::count_expected_attendees_per_date( $order );
					foreach ( $order->get_items() as $item_id => $item_values ) {
						$event_id = wc_get_order_item_meta( $item_id, 'event_id', true );
						if ( get_post_type( $event_id ) == 'mep_events' ) {
							$user_info_arr         = wc_get_order_item_meta( $item_id, '_event_user_info', true );
							$event_ticket_info_arr = wc_get_order_item_meta( $item_id, '_event_ticket_info', true );
							$_event_extra_service  = wc_get_order_item_meta( $item_id, '_event_extra_service', true );
							$item_quantity         = 0;
							mep_attendee_extra_service_create( $order_id, $event_id, $_event_extra_service );
							mep_delete_attandee_of_an_order( $order_id, $event_id );
							foreach ( $event_ticket_info_arr as $field ) {
								if ( $field['ticket_qty'] > 0 ) {
									$item_quantity = $item_quantity + $field['ticket_qty'];
								}
							}
							if ( is_array( $user_info_arr ) && sizeof( $user_info_arr ) > 0 ) {
								foreach ( $user_info_arr as $_user_info ) {
									$_event_date              = is_array( $_user_info ) && array_key_exists( 'user_event_date', $_user_info ) ? $_user_info['user_event_date'] : '';
									$check_before_create_date = mep_check_attendee_exist_before_create( $order_id, $event_id, $_event_date );
									$expected_for_this_date   = self::expected_attendee_count( $expected_attendees, $event_id, $_event_date );
									if ( function_exists( 'mep_re_language_load' ) ) {
										mep_attendee_create( 'user_form', $order_id, $event_id, $_user_info, 'yes' );
									} else {
										if ( $check_before_create_date < $expected_for_this_date ) {
											mep_attendee_create( 'user_form', $order_id, $event_id, $_user_info, 'yes' );
										}
									}
								}
							} else {
								foreach ( $event_ticket_info_arr as $tinfo ) {
									$_event_date            = is_array( $tinfo ) && array_key_exists( 'event_date', $tinfo ) ? $tinfo['event_date'] : '';
									$expected_for_this_date = self::expected_attendee_count( $expected_attendees, $event_id, $_event_date );
									for ( $x = 1; $x <= $tinfo['ticket_qty']; $x ++ ) {
										$check_before_create_date = mep_check_attendee_exist_before_create( $order_id, $event_id, $_event_date );
										if ( function_exists( 'mep_re_language_load' ) ) {
											mep_attendee_create( 'billing', $order_id, $event_id, $tinfo, 'yes' );
										} else {
											if ( $check_before_create_date < $expected_for_this_date ) {
												mep_attendee_create( 'billing', $order_id, $event_id, $tinfo, 'yes' );
											}
										}
									}
								}
							}
							$enable_clear_cart = mep_get_option( 'mep_clear_cart_after_checkout', 'general_setting_sec', 'enable' );
							if ( $enable_clear_cart == 'enable' ) {
								//   PayplugWoocommerce
								if ( ! class_exists( 'Payplug\PayplugWoocommerce' ) ) {
									if ( ! class_exists( 'WC_Xendit_CC' ) ) {
										if ( ! class_exists( 'PaysonCheckout_For_WooCommerce' ) ) {
											if ( ! class_exists( 'RP_SUB' ) ) {
												if ( ! class_exists( 'Afterpay_Plugin' ) ) {
													if ( ! class_exists( 'WC_Subscriptions' ) ) {
														if ( ! is_plugin_active( 'woo-juno/main.php' ) ) {
															if ( ! class_exists( 'WC_Saferpay' ) ) {
																// mep_clear_cart_after_checkout
																$woocommerce->cart->empty_cart();
															}
														}
													}
												}
											}
										}
									}
								}
							}
						} // end of check post type
					}
					do_action( 'mep_after_event_booking', $order_id, $order->get_status() );
				}
			}
			public static function get_cart_ticket_info( $post_id ) {
				$ticket_info = [];
				$start_date  = isset( $_POST['mep_event_start_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_event_start_date'] ) ) : [];
				$start_date  = current( $start_date );
				$names       = isset( $_POST['option_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_name'] ) ) : [];
				$qty         = isset( $_POST['option_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_qty'] ) ) : [];
				$max_qty     = isset( $_POST['max_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['max_qty'] ) ) : [];
				$total_price = 0;
				if ( is_array( $names ) && sizeof( $names ) > 0 ) {
					$event_ticket_types = MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_ticket_type', [] );
					$valid_ticket_names = [];
					if ( is_array( $event_ticket_types ) && sizeof( $event_ticket_types ) > 0 ) {
						foreach ( $event_ticket_types as $t_type ) {
							$t_name = is_array($t_type) && array_key_exists( 'option_name_t', $t_type ) ? $t_type['option_name_t'] : '';
							$t_name = html_entity_decode( urldecode( $t_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
							if ( $t_name ) {
								$valid_ticket_names[] = $t_name;
							}
						}
					}
					foreach ( $names as $key => $name ) {
						$current_qty = is_array($qty) && array_key_exists( $key, $qty ) ? (int) $qty[ $key ] : 0;
						$ticket_name               = explode( '_', $name );
                        $_name=$ticket_name[0];
						$decoded_name = html_entity_decode( urldecode( $_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
						$current_qty = apply_filters('mpwem_group_actual_qty', $current_qty, $post_id, $_name);
						$current_qty = apply_filters('mpwem_group_qty_actual', $current_qty, $post_id, $_name);
						if ( $_name && $current_qty > 0 && in_array( $decoded_name, $valid_ticket_names ) ) {
							$ticket_info[ $key ]['ticket_name']  = $name;
							$ticket_info[ $key ]['ticket_price'] = MPWEM_Functions::get_ticket_price_by_name( $_name, $post_id );
							$ticket_info[ $key ]['ticket_qty']   = $current_qty;
							$ticket_info[ $key ]['max_qty']      = is_array($max_qty) && array_key_exists( $key, $max_qty ) ? $max_qty[ $key ] : 0;
							$ticket_info[ $key ]['event_date']   = $start_date;
							$ticket_info[ $key ]['event_id']     = $post_id;
						}
					}
				}
				return apply_filters( 'mep_cart_ticket_type_data_prepare', $ticket_info, 'ticket_type', $total_price, $post_id );
			}
			public static function get_cart_ticket_price( $ticket_infos ) {
				$price = 0;
				if ( is_array( $ticket_infos ) && sizeof( $ticket_infos ) > 0 ) {
					foreach ( $ticket_infos as $ticket_info ) {
						$qty           = is_array($ticket_info) && array_key_exists( 'ticket_qty', $ticket_info ) ? $ticket_info['ticket_qty'] : 0;
						$current_price = is_array($ticket_info) && array_key_exists( 'ticket_price', $ticket_info ) ? $ticket_info['ticket_price'] : 0;
						$price         = $price + $current_price * $qty;
					}
				}
				return $price;
			}
			public static function get_cart_ex_info( $post_id ) {
				$ticket_info  = [];
				$ticket_types = MPWEM_Global_Function::get_post_info( $post_id, 'mep_events_extra_prices', [] );
				$start_date   = isset( $_POST['mep_event_start_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_event_start_date'] ) ) : [];
				$start_date   = current( $start_date );
				$names        = isset( $_POST['event_extra_service_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_extra_service_name'] ) ) : [];
				$qty          = isset( $_POST['event_extra_service_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_extra_service_qty'] ) ) : [];
				if ( is_array( $names ) && sizeof( $names ) > 0 ) {
					$valid_ex_names = [];
					if ( is_array( $ticket_types ) && sizeof( $ticket_types ) > 0 ) {
						foreach ( $ticket_types as $t_type ) {
							$t_name = is_array($t_type) && array_key_exists( 'option_name', $t_type ) ? $t_type['option_name'] : '';
							$t_name = str_replace( "'", "", $t_name );
							if ( $t_name ) {
								$valid_ex_names[] = $t_name;
							}
						}
					}
					foreach ( $names as $key => $name ) {
						$current_qty = is_array($qty) && array_key_exists( $key, $qty ) ? $qty[ $key ] : 0;
						$ex_name = explode( '_', $name )[0];
						$ex_name = str_replace( "'", "", $ex_name );
						if ( $name && $current_qty > 0 && in_array( $ex_name, $valid_ex_names ) ) {
							$ticket_info[ $key ]['service_name']  = $name;
							$ticket_info[ $key ]['service_price'] = MPWEM_Functions::get_ex_price_by_name( $name, $post_id, $ticket_types );
							$ticket_info[ $key ]['service_qty']   = $current_qty;
							$ticket_info[ $key ]['event_date']    = $start_date;
						}
					}
				}
				return $ticket_info;
			}
			public static function get_cart_ex_price( $ticket_infos ) {
				$price = 0;
				if ( is_array( $ticket_infos ) && sizeof( $ticket_infos ) > 0 ) {
					foreach ( $ticket_infos as $ticket_info ) {
						$qty           = is_array($ticket_info) && array_key_exists( 'service_qty', $ticket_info ) ? $ticket_info['service_qty'] : 0;
						$current_price = is_array($ticket_info) && array_key_exists( 'service_price', $ticket_info ) ? $ticket_info['service_price'] : 0;
						$price         = $price + $current_price * $qty;
					}
				}
				return $price;
			}
			public static function get_attendee_info( $post_id ) {
				$attendee_info = [];
				$names         = isset( $_POST['option_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_name'] ) ) : [];
				if ( is_array( $names ) && sizeof( $names ) > 0 ) {
					$start_date   = isset( $_POST['mep_event_start_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_event_start_date'] ) ) : [];
					$start_date   = current( $start_date );
					$qty          = isset( $_POST['option_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_qty'] ) ) : [];
					$submit_infos = [];
					$form_array   = MPWEM_Layout::get_form_array( $post_id );
					if ( is_array( $form_array ) && sizeof( $form_array ) > 0 ) {
						foreach ( $form_array as $form ) {
							if ( is_array( $form ) && sizeof( $form ) > 0 ) {
								$type = is_array($form) && array_key_exists( 'type', $form ) ? $form['type'] : '';
								$name = is_array($form) && array_key_exists( 'name', $form ) ? $form['name'] : '';
								if ( $type && $name && $type != 'title' ) {
									$submit_infos[ $name ] = isset( $_POST[ $name ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $name ] ) ) : [];
								}
							}
						}
					}
					$same_attendee = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_enable_same_attendee', 'no' );
					$count         = 0;

					$event_ticket_types = MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_ticket_type', [] );
					$valid_ticket_names = [];
					if ( is_array( $event_ticket_types ) && sizeof( $event_ticket_types ) > 0 ) {
						foreach ( $event_ticket_types as $t_type ) {
							$t_name = is_array($t_type) && array_key_exists( 'option_name_t', $t_type ) ? $t_type['option_name_t'] : '';
							$t_name = html_entity_decode( urldecode( $t_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
							if ( $t_name ) {
								$valid_ticket_names[] = $t_name;
							}
						}
					}

					foreach ( $names as $key => $name ) {
						$current_qty=$qty[ $key ];
						$ticket_name               = explode( '_', $name );
						$_name=$ticket_name[0];
						$decoded_name = html_entity_decode( urldecode( $_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
						$current_qty = apply_filters('mpwem_group_actual_qty', $current_qty, $post_id, $_name);
						$current_qty = apply_filters('mpwem_group_qty_actual', $current_qty, $post_id, $_name);
						if ( $current_qty > 0 && $name && in_array( $decoded_name, $valid_ticket_names ) ) {
							for ( $j = 0; $j < $current_qty; $j ++ ) {
								if ( ( $same_attendee == 'yes' || $same_attendee == 'must' ) && is_array( $attendee_info ) && sizeof( $attendee_info ) > 0 ) {
									$attendee_info[ $count ] = current( $attendee_info );
								} else {
									if ( is_array( $form_array ) && sizeof( $form_array ) > 0 && is_array( $submit_infos ) && sizeof( $submit_infos ) > 0 ) {
										foreach ( $form_array as $form ) {
											if ( is_array( $form ) && sizeof( $form ) > 0 ) {
												$type       = is_array($form) && array_key_exists( 'type', $form ) ? $form['type'] : '';
												$input_name = is_array($form) && array_key_exists( 'name', $form ) ? $form['name'] : '';
												if ( $type && $input_name && $type != 'title' ) {
													if ( $type == 'file' ) {
														$attendee_info[ $count ] = apply_filters( 'mpwem_upload_attendee_file', $attendee_info[ $count ], $input_name, $count );
													} else {
														$data                                    = is_array($submit_infos) && array_key_exists( $input_name, $submit_infos ) ? $submit_infos[ $input_name ] : [];
														// A field the browser never submitted (unchecked checkbox, a field
														// hidden by conditional logic, or one the admin left out on the
														// backend-order screen) has no row for this attendee index.
														$attendee_info[ $count ] [ $input_name ] = is_array( $data ) && array_key_exists( $count, $data ) ? $data[ $count ] : '';
													}
												}
											}
										}
									}
								}
								$attendee_info[ $count ]['user_ticket_type'] = $_name;
								$attendee_info[ $count ]['ticket_name']      = $_name;
								$attendee_info[ $count ]['user_ticket_qty']  = 1;
								$attendee_info[ $count ]['ticket_qty']       = 1;
								$attendee_info[ $count ]['ticket_price']     = MPWEM_Functions::get_ticket_price_by_name( $_name, $post_id );
								$attendee_info[ $count ]['user_event_date']  = $start_date;
								$attendee_info[ $count ]['user_event_id']    = $post_id;
								$count ++;
							}
						}
					}
				}
				return apply_filters( 'mep_cart_user_data_prepare', $attendee_info, $post_id );
			}
			public static function show_attendee( $user, $form_array, $same_attendee = 'yes' ) {
				if ( is_array( $user ) && sizeof( $user ) > 0 ) {
					$post_id = is_array( $user ) && array_key_exists( 'user_event_id', $user ) ? $user['user_event_id'] : '';
					?>
                    <div class="mep-cart-details__section" style="display:block;margin:0 0 12px;padding:12px;background:#f7f7f9;border:1px solid #ececf1;">
						<?php if ( $same_attendee == 'yes' ) { ?>
                            <p style="margin:0 0 10px;padding:0 0 8px;border-bottom:1px solid #e4e4ec;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#5b5b66;"><?php esc_html_e( 'Attendee Information', 'mage-eventpress' ); ?></p>
						<?php } ?>
						<?php
							if ( $same_attendee == 'no' ) {
								$ticket_name  = is_array( $user ) && array_key_exists( 'ticket_name', $user ) ? $user['ticket_name'] : '';
								$ticket_price = is_array( $user ) && array_key_exists( 'ticket_price', $user ) ? $user['ticket_price'] : 0;
								$ticket_qty   = is_array( $user ) && array_key_exists( 'ticket_qty', $user ) ? $user['ticket_qty'] : 1;
								$line_total   = (float) $ticket_price * (float) $ticket_qty;
								$ticket_text  = '<table cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;margin:0 0 10px;"><tr>'
									. '<td style="padding:8px 0;vertical-align:top;border-bottom:1px solid #ececf1;font-weight:600;color:#1f1f27;">' . esc_html( $ticket_name ) . '</td>'
									. '<td style="padding:8px 8px;vertical-align:top;border-bottom:1px solid #ececf1;text-align:right;white-space:nowrap;color:#6f6f7a;font-size:12px;">' . wc_price( (float) $ticket_price ) . ' &times; ' . esc_html( $ticket_qty ) . '</td>'
									. '<td style="padding:8px 0 8px 8px;vertical-align:top;border-bottom:1px solid #ececf1;text-align:right;white-space:nowrap;font-weight:700;color:#1f1f27;">' . wc_price( $line_total ) . '</td>'
									. '</tr></table>';
								echo apply_filters( 'mpwem_display_ticket_in_cart_list', $ticket_text, $user, $post_id );
								do_action( 'mep_cart_after_ticket_type', $user );
							}
						?>
						<?php
							foreach ( $form_array as $form ) {
								if ( is_array( $form ) && sizeof( $form ) > 0 ) {
									$type = is_array( $form ) && array_key_exists( 'type', $form ) ? $form['type'] : '';
									$name = is_array( $form ) && array_key_exists( 'name', $form ) ? $form['name'] : '';
									if ( $type && $name && $type != 'title' && is_array( $user ) && array_key_exists( $name, $user ) && $user[ $name ] != '' ) {
										$label = is_array( $form ) && array_key_exists( 'label', $form ) ? $form['label'] : '';
										echo '<strong style="color:#6f6f7a;">' . esc_html( $label ) . ':</strong>&nbsp;';
										if ( $type == 'file' ) {
											$upload_dir = wp_upload_dir();
											$file_url   = $upload_dir['baseurl'] . '/mep_attendee_file_list/' . $user[ $name ];
											$file_url   = str_replace( 'http://', 'https://', $file_url );
											echo '<a href="' . esc_url( $file_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $user[ $name ] ) . '</a>';
										} else {
											echo esc_html( $user[ $name ] );
										}
										echo "\n<br />\n";
									}
								}
							}
						?>
                    </div>
					<?php
				}
			}
			public function account_dashboard() {
				ob_start();
				?>
                <div class="mep-user-ticket-list">
                    <table>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Ticket', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></th>
							<?php do_action( 'mep_user_order_list_table_head' ); ?>
                        </tr>
						<?php
							$_user_set_status    = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
							$_order_status       = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
							$order_status        = array_values( array_filter( (array) $_order_status ) ?: array( 'processing', 'completed' ) );
							$order_status_filter = array(
								'key'     => 'ea_order_status',
								'value'   => $order_status,
								'compare' => 'OR'
							);
							$args_search_qqq     = array(
								'post_type'      => array( 'mep_events_attendees' ),
								'posts_per_page' => - 1,
								'author__in'     => array( get_current_user_id() ),
								'meta_query'     => array(
									$order_status_filter
								)
							);
							$loop                = new WP_Query( $args_search_qqq );
							while ( $loop->have_posts() ) {
								$loop->the_post();
								$event_id     = get_post_meta( get_the_id(), 'ea_event_id', true );
								$virtual_info = get_post_meta( $event_id, 'mp_event_virtual_type_des', true ) ? get_post_meta( $event_id, 'mp_event_virtual_type_des', true ) : '';
								$time         = get_post_meta( $event_id, 'event_expire_datetime', true ) ? strtotime( get_post_meta( $event_id, 'event_expire_datetime', true ) ) : strtotime( get_post_meta( $event_id, 'event_start_datetime', true ) );
								$newformat    = date( 'Y-m-d H:i:s', $time );
								if ( strtotime( current_time( 'Y-m-d H:i:s' ) ) < strtotime( $newformat ) ) {
									?>
                                    <tr>
                                        <td><?php echo get_post_meta( get_the_id(), 'ea_name', true ); ?></td>
                                        <td><?php echo get_post_meta( get_the_id(), 'ea_ticket_type', true ); ?></td>
                                        <td><?php echo get_post_meta( get_the_id(), 'ea_event_name', true );
												if ( $virtual_info ) { ?>
                                                    <button id='mep_vr_view_btn_<?php echo get_the_id(); ?>' class='mep_view_vr_btn'><?php esc_html_e( 'View Virtual Info', 'mage-eventpress' ); ?></button> <?php } ?>

											<?php do_action( 'mep_user_order_list_table_action_col', get_the_id() ); ?>
                                        </td>
										<?php do_action( 'mep_user_order_list_table_row', get_the_id() ); ?>
                                    </tr>
									<?php
									if ( $virtual_info ) {
										?>
                                        <tr id='mep_vr_view_sec_<?php echo get_the_id(); ?>' class='mep_virtual_event_info_sec' style='display:none'>
                                            <td colspan='4'>
                                                <div class='mep-vr-vs-content'>
                                                    <h3><?php esc_html_e( 'Virtual Event Information:', 'mage-eventpress' ); ?></h3>
													<?php echo wp_kses_post( html_entity_decode( $virtual_info ) ); ?>
                                                </div>
                                            </td>
                                        </tr>
										<?php
									}
								}
							}
						?>
                    </table>
                </div>
				<?php
				$content = ob_get_clean();
				echo wp_kses_post( html_entity_decode( $content ) );
			}
			public function cart_item_price( $price, $cart_item, $r ) {
				if ( is_array($cart_item) && array_key_exists( 'event_id', $cart_item ) && get_post_type( $cart_item['event_id'] ) == 'mep_events' ) {
					$price = wc_price( $cart_item['event_tp']);
				}
				return $price;
			}
		}
		new MPWEM_Woocommerce();
	}
