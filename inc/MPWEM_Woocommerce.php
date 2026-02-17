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
					$event_id = array_key_exists( 'event_id', $value ) ? $value['event_id'] : 0;
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
				$eid = array_key_exists( 'event_id', $cart_item ) ? $cart_item['event_id'] : 0; //$cart_item['event_id'];
				if ( get_post_type( $eid ) == 'mep_events' ) {
					$general_setting_sec  =  MPWEM_Global_Function::get_setting('general_setting_sec') ;
					$hide_location_status = array_key_exists( 'mep_hide_location_from_order_page', $general_setting_sec ) ? $general_setting_sec['mep_hide_location_from_order_page'] : 'no';
					$hide_date_status     = array_key_exists( 'mep_hide_date_from_order_page', $general_setting_sec ) ? $general_setting_sec['mep_hide_date_from_order_page'] : 'no';
					$user_info            = array_key_exists( 'event_user_info', $cart_item ) ? $cart_item['event_user_info'] : [];
					$ticket_type_arr      = array_key_exists( 'event_ticket_info', $cart_item ) ? $cart_item['event_ticket_info'] : [];
					$event_extra_service  = array_key_exists( 'event_extra_service', $cart_item ) ? $cart_item['event_extra_service'] : [];
					$event_date           = array_key_exists( 'event_cart_date', $cart_item ) ? $cart_item['event_cart_date'] : '';
					$date_format          = MPWEM_Global_Function::check_time_exit_date( $event_date ) ? 'full' : 'date';
					$location             = array_key_exists( 'event_cart_location', $cart_item ) ? $cart_item['event_cart_location'] : '';
					$same_attendee        = array_key_exists( 'mep_enable_same_attendee', $general_setting_sec ) ? $general_setting_sec['mep_enable_same_attendee'] : 'no';
					// echo '<pre>';print_r(MPWEM_Form_Builder::get_form_array($eid));echo '</pre>';
					$form_array = MPWEM_Layout::get_form_array( $eid );
					?>
                    <div class="mpwem_style">
						<?php if ( $hide_date_status == 'no' ) { ?>
                            <h6 class="_mp_zero"><?php echo esc_html__( " Date : ", 'mage-eventpress' ) . ' ' . MPWEM_Global_Function::date_format( $event_date, $date_format,$eid ); ?></h6>
						<?php } ?>
						<?php if ( $location && $hide_location_status == 'no' ) { ?>
                            <h6 class="_mp_zero"><?php echo esc_html__( " Location : ", 'mage-eventpress' ) . ' ' . esc_html( $location ); ?></h6>
						<?php }
							if ( ( $same_attendee == 'yes' || $same_attendee == 'must' ) && sizeof( $user_info ) > 0 && sizeof( $form_array ) > 0 ) {
								if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
									?>
                                    <div class="_layout_info_xs_mt_xs">
                                        <h6 class="_mp_zero"><?php esc_html_e( 'Ticket Information', 'mage-eventpress' ); ?></h6>
                                        <div class="_divider_xs"></div>
                                        <ul class="cart_list">
											<?php
												foreach ( $ticket_type_arr as $ticket ) {
													$ticket_text = '<li>' . esc_attr( $ticket['ticket_name'] ) . "&nbsp;&nbsp;" . wc_price( (float) $ticket['ticket_price'] ) . '&nbsp;x&nbsp;' . esc_attr( $ticket['ticket_qty'] ) . '&nbsp;=&nbsp;' . wc_price( (float) $ticket['ticket_price'] * (float) $ticket['ticket_qty'] ) . '</li>';
													echo apply_filters( 'mpwem_display_ticket_in_cart_list', $ticket_text, $ticket, $eid );
													do_action( 'mep_cart_after_ticket_type', $ticket );
												}
											?>
                                        </ul>
                                    </div>
									<?php
								}
								$user = current( $user_info );
								self::show_attendee( $user, $form_array );
							} else {
								if ( sizeof( $user_info ) > 0 ) {
									foreach ( $user_info as $user ) {
										self::show_attendee( $user, $form_array, 'no' );
									}
								}
							}
							if ( is_array( $event_extra_service ) && sizeof( $event_extra_service ) > 0 ) {
								?>
                                <div class="_layout_info_xs_mt_xs">
                                    <h6 class="_mp_zero"><?php esc_html_e( 'Extra Service', 'mage-eventpress' ); ?></h6>
                                    <div class="_divider_xs"></div>
                                    <ul class="cart_list">
										<?php foreach ( $event_extra_service as $extra_service ) {
											echo '<li>' . esc_html( $extra_service['service_name'] ) . " - " . wc_price( $extra_service['service_price'] ) . '&nbsp;x&nbsp;' . esc_html( $extra_service['service_qty'] ) . '&nbsp;=&nbsp;' . wc_price( (float) $extra_service['service_price'] * (float) $extra_service['service_qty'] ) . '</li>';
										} ?>
                                    </ul>
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
					$event_id        = array_key_exists( 'event_id', $values ) ? $values['event_id'] : 0; // $values['event_id'];
					$check_seat_plan = get_post_meta( $event_id, 'mepsp_event_seat_plan_info', true ) ? get_post_meta( $event_id, 'mepsp_event_seat_plan_info', true ) : array();
					if ( get_post_type( $event_id ) == 'mep_events' && sizeof( $check_seat_plan ) == 0 ) {
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
						wc_add_notice( "This event has already been added to the shopping cart. To change the quantity, please remove it from the cart and add it back again.", 'error' );
						$passed = false;
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
				$eid           = array_key_exists( 'event_id', $values ) ? $values['event_id'] : 0; //$values['event_id'];
				$location_text = mep_get_option( 'mep_location_text_x', 'label_setting_sec', esc_html__( 'Location', 'mage-eventpress' ) );
				$date_text     = mep_get_option( 'mep_event_date_text_x', 'label_setting_sec', esc_html__( 'Date', 'mage-eventpress' ) );
				if ( get_post_type( $eid ) == 'mep_events' ) {
					$event_id                = $eid;
					$mep_events_extra_prices = array_key_exists( 'event_extra_option', $values ) ? $values['event_extra_option'] : [];
					$cart_location           = array_key_exists( 'event_cart_location', $values ) ? $values['event_cart_location'] : '';
					$event_extra_service     = array_key_exists( 'event_extra_service', $values ) ? $values['event_extra_service'] : [];
					$ticket_type_arr         = array_key_exists( 'event_ticket_info', $values ) ? $values['event_ticket_info'] : '';
					$cart_date               = get_mep_datetime( $values['event_cart_date'], 'date-time-text' );
					$event_user_info         = $values['event_user_info'];
					$recurring               = get_post_meta( $eid, 'mep_enable_recurring', true ) ? get_post_meta( $eid, 'mep_enable_recurring', true ) : 'no';
					$time_status             = get_post_meta( $eid, 'mep_disable_ticket_time', true ) ? get_post_meta( $eid, 'mep_disable_ticket_time', true ) : 'no';
					if ( $recurring == 'everyday' && $time_status == 'no' ) {
						if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
							$count = 1;
							foreach ( $ticket_type_arr as $_event_recurring_date ) {
								if($count == 1){
									$item->add_meta_data( $date_text, get_mep_datetime( $_event_recurring_date['event_date'], apply_filters( 'mep_cart_date_format', 'date-time-text' ) ) );
								}
								$count++;
								}
						}
					} elseif ( $recurring == 'yes' ) {
						if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
							foreach ( $ticket_type_arr as $_event_recurring_date ) {
								$item->add_meta_data( $date_text, get_mep_datetime( $_event_recurring_date['event_date'], apply_filters( 'mep_cart_date_format', 'date-time-text' ) ) );
							}
						}
					} else {
						$item->add_meta_data( $date_text, $cart_date );
					}
					if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
						mep_cart_order_data_save_ticket_type( $item, $ticket_type_arr, $eid );
					}
					$custom_forms_id = mep_get_user_custom_field_ids( $eid );
					foreach ( $event_user_info as $userinf ) {
						if ( array_key_exists( 'user_name', $userinf ) && ! empty( $userinf['user_name'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Name' ), $userinf['user_name'] );
						}
						if ( array_key_exists( 'user_email', $userinf ) && ! empty( $userinf['user_email'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Email' ), $userinf['user_email'] );
						}
						if ( array_key_exists( 'user_phone', $userinf ) && ! empty( $userinf['user_phone'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Phone' ), $userinf['user_phone'] );
						}
						if ( array_key_exists( 'user_address', $userinf ) && ! empty( $userinf['user_address'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Address' ), $userinf['user_address'] );
						}
						if ( array_key_exists( 'user_gender', $userinf ) && ! empty( $userinf['user_gender'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Gender' ), $userinf['user_gender'] );
						}
						if ( array_key_exists( 'user_tshirtsize', $userinf ) && ! empty( $userinf['user_tshirtsize'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'T-Shirt Size' ), $userinf['user_tshirtsize'] );
						}
						if ( array_key_exists( 'user_company', $userinf ) && ! empty( $userinf['user_company'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Company' ), $userinf['user_company'] );
						}
						if ( array_key_exists( 'user_designation', $userinf ) && ! empty( $userinf['user_designation'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Designation' ), $userinf['user_designation'] );
						}
						if ( array_key_exists( 'user_website', $userinf ) && ! empty( $userinf['user_website'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Website' ), $userinf['user_website'] );
						}
						if ( array_key_exists( 'user_vegetarian', $userinf ) && ! empty( $userinf['user_vegetarian'] ) ) {
							$item->add_meta_data( mep_get_reg_label( $event_id, 'Vegetarian' ), $userinf['user_vegetarian'] );
						}
						if ( sizeof( $custom_forms_id ) > 0 ) {
							foreach ( $custom_forms_id as $key => $value ) {
								$item->add_meta_data( $key, $userinf[ $value ] );
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
				$order_meta           = get_post_meta( $order_id );
				$email                = isset( $order_meta['_billing_email'][0] ) ? $order_meta['_billing_email'][0] : $order->get_billing_email();
				$email_send_status    = mep_get_option( 'mep_email_sending_order_status', 'email_setting_sec', array( 'disable_email' => 'disable_email' ) );
				$email_send_status    = ! empty( $email_send_status ) ? $email_send_status : array( 'disable_email' => 'disable_email' );
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
									mep_event_confirmation_email_sent( $event_id, $email, $order_id );
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
							if ( in_array( 'completed', $email_send_status ) ) {
								mep_event_confirmation_email_sent( $event_id, $email, $order_id );
								if ( ! empty( $org_email ) ) {
									mep_event_confirmation_email_sent( $event_id, $org_email, $order_id );
								}
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
					foreach ( $order->get_items() as $item_id => $item_values ) {
						$event_id = wc_get_order_item_meta( $item_id, 'event_id', true );
						if ( get_post_type( $event_id ) == 'mep_events' ) {
							$user_info_arr         = wc_get_order_item_meta( $item_id, '_event_user_info', true );
							$event_ticket_info_arr = wc_get_order_item_meta( $item_id, '_event_ticket_info', true );
							$_event_extra_service  = wc_get_order_item_meta( $item_id, '_event_extra_service', true );
							$item_quantity         = 0;
							$check_before_create   = mep_check_attendee_exist_before_create( $order_id, $event_id );
							mep_attendee_extra_service_create( $order_id, $event_id, $_event_extra_service );
							mep_delete_attandee_of_an_order( $order_id, $event_id );
							foreach ( $event_ticket_info_arr as $field ) {
								if ( $field['ticket_qty'] > 0 ) {
									$item_quantity = $item_quantity + $field['ticket_qty'];
								}
							}
							if ( is_array( $user_info_arr ) & sizeof( $user_info_arr ) > 0 ) {
								foreach ( $user_info_arr as $_user_info ) {
									$check_before_create_date = mep_check_attendee_exist_before_create( $order_id, $event_id, $_user_info['user_event_date'] );
									if ( function_exists( 'mep_re_language_load' ) ) {
										mep_attendee_create( 'user_form', $order_id, $event_id, $_user_info, 'yes' );
									} else {
										if ( $check_before_create < count( $user_info_arr ) ) {
											if ( $check_before_create_date == 0 ) {
												mep_attendee_create( 'user_form', $order_id, $event_id, $_user_info, 'yes' );
											}
										}
									}
								}
							} else {
								foreach ( $event_ticket_info_arr as $tinfo ) {
									for ( $x = 1; $x <= $tinfo['ticket_qty']; $x ++ ) {
										$check_before_create_date = mep_check_attendee_exist_before_create( $order_id, $event_id, $tinfo['event_date'] );
										if ( function_exists( 'mep_re_language_load' ) ) {
											mep_attendee_create( 'billing', $order_id, $event_id, $tinfo, 'yes' );
										} else {
											if ( $check_before_create < count( $event_ticket_info_arr ) ) {
												if ( $check_before_create_date == 0 ) {
													mep_attendee_create( 'billing', $order_id, $event_id, $tinfo, 'yes' );
												}
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
				if ( sizeof( $names ) > 0 ) {
					foreach ( $names as $key => $name ) {
						$current_qty = array_key_exists( $key, $qty ) ? (int) $qty[ $key ] : 0;
						$ticket_name               = explode( '_', $name );
                        $_name=$ticket_name[0];
						$current_qty = apply_filters('mpwem_group_actual_qty', $current_qty, $post_id, $_name);
						if ( $_name && $current_qty > 0 ) {
							$ticket_info[ $key ]['ticket_name']  = $name;
							$ticket_info[ $key ]['ticket_price'] = MPWEM_Functions::get_ticket_price_by_name( $_name, $post_id );
							$ticket_info[ $key ]['ticket_qty']   = $current_qty;
							$ticket_info[ $key ]['max_qty']      = array_key_exists( $key, $max_qty ) ? $max_qty[ $key ] : 0;
							$ticket_info[ $key ]['event_date']   = $start_date;
							$ticket_info[ $key ]['event_id']     = $post_id;
						}
					}
				}
				return apply_filters( 'mep_cart_ticket_type_data_prepare', $ticket_info, 'ticket_type', $total_price, $post_id );
			}
			public static function get_cart_ticket_price( $ticket_infos ) {
				$price = 0;
				if ( sizeof( $ticket_infos ) > 0 ) {
					foreach ( $ticket_infos as $ticket_info ) {
						$qty           = array_key_exists( 'ticket_qty', $ticket_info ) ? $ticket_info['ticket_qty'] : 0;
						$current_price = array_key_exists( 'ticket_price', $ticket_info ) ? $ticket_info['ticket_price'] : 0;
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
				if ( sizeof( $names ) > 0 ) {
					foreach ( $names as $key => $name ) {
						$current_qty = array_key_exists( $key, $qty ) ? $qty[ $key ] : 0;
						if ( $name && $current_qty > 0 ) {
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
				if ( sizeof( $ticket_infos ) > 0 ) {
					foreach ( $ticket_infos as $ticket_info ) {
						$qty           = array_key_exists( 'service_qty', $ticket_info ) ? $ticket_info['service_qty'] : 0;
						$current_price = array_key_exists( 'service_price', $ticket_info ) ? $ticket_info['service_price'] : 0;
						$price         = $price + $current_price * $qty;
					}
				}
				return $price;
			}
			public static function get_attendee_info( $post_id ) {
				$attendee_info = [];
				$names         = isset( $_POST['option_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_name'] ) ) : [];
				if ( sizeof( $names ) > 0 ) {
					$start_date   = isset( $_POST['mep_event_start_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_event_start_date'] ) ) : [];
					$start_date   = current( $start_date );
					$qty          = isset( $_POST['option_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_qty'] ) ) : [];
					$submit_infos = [];
					$form_array   = MPWEM_Layout::get_form_array( $post_id );
					if ( sizeof( $form_array ) > 0 ) {
						foreach ( $form_array as $form ) {
							if ( sizeof( $form ) > 0 ) {
								$type = array_key_exists( 'type', $form ) ? $form['type'] : '';
								$name = array_key_exists( 'name', $form ) ? $form['name'] : '';
								if ( $type && $name && $type != 'title' ) {
									$submit_infos[ $name ] = isset( $_POST[ $name ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $name ] ) ) : [];
								}
							}
						}
					}
					$same_attendee = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_enable_same_attendee', 'no' );
					$count         = 0;
					foreach ( $names as $key => $name ) {
						$current_qty=$qty[ $key ];
						$ticket_name               = explode( '_', $name );
						$_name=$ticket_name[0];
						$current_qty = apply_filters('mpwem_group_actual_qty', $current_qty, $post_id, $_name);
						if ( $current_qty > 0 && $name ) {
							for ( $j = 0; $j < $qty[ $key ]; $j ++ ) {
								if ( ( $same_attendee == 'yes' || $same_attendee == 'must' ) && sizeof( $attendee_info ) > 0 ) {
									$attendee_info[ $count ] = current( $attendee_info );
								} else {
									if ( sizeof( $form_array ) > 0 && sizeof( $submit_infos ) > 0 ) {
										foreach ( $form_array as $form ) {
											if ( sizeof( $form ) > 0 ) {
												$type       = array_key_exists( 'type', $form ) ? $form['type'] : '';
												$input_name = array_key_exists( 'name', $form ) ? $form['name'] : '';
												if ( $type && $input_name && $type != 'title' ) {
													if ( $type == 'file' ) {
														$attendee_info[ $count ] = apply_filters( 'mpwem_upload_attendee_file', $attendee_info[ $count ], $input_name, $count );
													} else {
														$data                                    = array_key_exists( $input_name, $submit_infos ) ? $submit_infos[ $input_name ] : [];
														$attendee_info[ $count ] [ $input_name ] = $data[ $count ];
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
				if ( sizeof( $user ) >0) {
					$post_id = array_key_exists( 'user_event_id', $user ) ? $user['user_event_id'] : '';
					?>
                    <div class="_layout_info_xs_mt_xs">
						<?php if ( $same_attendee == 'yes' ) { ?>
                            <h6 class="_mp_zero"><?php esc_html_e( 'Attendee Information', 'mage-eventpress' ); ?></h6>
                            <div class="_divider_xs"></div>
						<?php } ?>
                        <ul class="cart_list">
							<?php
								if ( $same_attendee == 'no' ) {
									$ticket_name = array_key_exists( 'ticket_name', $user ) ? $user['ticket_name'] : '';
									$ticket_price = array_key_exists( 'ticket_price', $user ) ? $user['ticket_price'] : 0;
									$ticket_qty = array_key_exists( 'ticket_qty', $user ) ? $user['ticket_qty'] : 1;
									$ticket_text = '<li>' . esc_attr( $ticket_name) . " &nbsp;&nbsp;" . wc_price( (float) $ticket_price) . '&nbsp;x&nbsp;' . esc_attr( $ticket_qty ) . '&nbsp;=&nbsp;' . wc_price( (float) $ticket_price * (float) $ticket_qty ) . '</li>';
                                    //echo '<li><pre>'.print_r($user).'</pre></li>';
									echo apply_filters( 'mpwem_display_ticket_in_cart_list', $ticket_text, $user, $post_id );
									do_action( 'mep_cart_after_ticket_type', $user );
								}
								foreach ( $form_array as $form ) {
									if ( sizeof( $form ) > 0 ) {
										$type = array_key_exists( 'type', $form ) ? $form['type'] : '';
										$name = array_key_exists( 'name', $form ) ? $form['name'] : '';
										if ( $type && $name && $type != 'title' && array_key_exists( $name, $user ) && $user[ $name ] != '' ) {
											$label = array_key_exists( 'label', $form ) ? $form['label'] : '';
											if ( $type == 'file' ) {
												echo '<li>' . esc_html( $label . ' : ' . $user[ $name ] ) . '</li>';
											} else {
												echo '<li>' . esc_html( $label . ' : ' . $user[ $name ] ) . '</li>';
											}
										}
									}
								} ?>
                        </ul>
                    </div>
				<?php }
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
							$order_status        = array_values( $_order_status );
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
				if ( array_key_exists( 'event_id', $cart_item ) && get_post_type( $cart_item['event_id'] ) == 'mep_events' ) {
					$price = wc_price( $cart_item['event_tp']);
				}
				return $price;
			}
		}
		new MPWEM_Woocommerce();
	}
