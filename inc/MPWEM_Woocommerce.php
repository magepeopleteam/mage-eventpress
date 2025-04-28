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
				add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 90, 3 );
				add_action( 'woocommerce_before_calculate_totals', array( $this, 'before_calculate_totals' ) );
				add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 20, 2 );
				add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 10, 90 );
				/**********************************************/
				add_action( 'woocommerce_after_checkout_validation', array( $this, 'after_checkout_validation' ) );
				add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item' ), 10, 4 );
			}

			public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
				$linked_event_id = get_post_meta( $product_id, 'link_mep_event', true ) ? get_post_meta( $product_id, 'link_mep_event', true ) : $product_id;
				$product_id      = mep_product_exists( $linked_event_id ) ? $linked_event_id : $product_id;
				$recurring       = get_post_meta( $product_id, 'mep_enable_recurring', true ) ? get_post_meta( $product_id, 'mep_enable_recurring', true ) : 'no';
				if ( get_post_type( $product_id ) == 'mep_events' ) {
					$total_price          = get_post_meta( $product_id, '_price', true );
					$form_position        = mep_get_option( 'mep_user_form_position', 'general_attendee_sec', 'details_page' );
					$mep_event_start_date = isset( $_POST['mep_event_start_date'] ) ? mage_array_strip( $_POST['mep_event_start_date'] ) : array();
					$event_cart_location  = isset( $_POST['mep_event_location_cart'] ) ? sanitize_text_field( $_POST['mep_event_location_cart'] ) : '';
					$recurring_event_date = $recurring == 'yes' && isset( $_POST['recurring_event_date'] ) ? mage_array_strip( $_POST['recurring_event_date'] ) : array();
					$ticket_type_arr      = mep_cart_ticket_type( 'ticket_type', $total_price, $product_id );
					$total_price          = mep_cart_ticket_type( 'ticket_price', $total_price, $product_id );
					$event_extra          = mep_cart_event_extra_service( 'event_extra_service', $total_price, $product_id );
					$total_price          = mep_cart_event_extra_service( 'ticket_price', $total_price, $product_id );
					$user                 = $form_position == 'details_page' ? mep_save_attendee_info_into_cart( $product_id ) : array();

					$validate       = mep_cart_ticket_type( 'validation_data', $total_price, $product_id );
					$time_slot_text = isset( $_REQUEST['time_slot_name'] ) ? sanitize_text_field( $_REQUEST['time_slot_name'] ) : '';
					if ( ! empty( $time_slot_text ) ) {
						$cart_item_data['event_everyday_time_slot'] = $time_slot_text;
					}
					$cart_item_data['event_ticket_info']        = $ticket_type_arr;
					$cart_item_data['event_validate_info']      = $validate;
					$cart_item_data['event_user_info']          = $user;
					$cart_item_data['event_tp']                 = $total_price;
					$cart_item_data['line_total']               = $total_price;
					$cart_item_data['line_subtotal']            = $total_price;
					$cart_item_data['event_extra_service']      = $event_extra;
					$cart_item_data['event_cart_location']      = $event_cart_location;
					$cart_item_data['event_cart_date']          = $mep_event_start_date[0];
					$cart_item_data['event_recurring_date']     = array_unique( $recurring_event_date );
					$cart_item_data['event_recurring_date_arr'] = $recurring_event_date;
					$cart_item_data['event_cart_display_date']  = $mep_event_start_date[0];
					do_action( 'mep_event_cart_data_reg' );
					$cart_item_data['event_id'] = $product_id;
					mep_temp_attendee_create_for_cart_ticket_array($product_id, $ticket_type_arr); 
                    //echo '<pre>';print_r( $cart_item_data );echo '</pre>';die();

					return apply_filters( 'mep_event_cart_item_data', $cart_item_data, $product_id, $total_price, $user, $ticket_type_arr, $event_extra );
				} else {
					return $cart_item_data;
				}
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
					$hide_location_status = mep_get_option( 'mep_hide_location_from_order_page', 'general_setting_sec', 'no' );
					$hide_date_status     = mep_get_option( 'mep_hide_date_from_order_page', 'general_setting_sec', 'no' );
					$user_info            = $cart_item['event_user_info'];
					$ticket_type_arr      = $cart_item['event_ticket_info'];
					$event_extra_service  = $cart_item['event_extra_service'];
					$recurring            = get_post_meta( $eid, 'mep_enable_recurring', true ) ? get_post_meta( $eid, 'mep_enable_recurring', true ) : 'no';
					$time_status          = get_post_meta( $eid, 'mep_disable_ticket_time', true ) ? get_post_meta( $eid, 'mep_disable_ticket_time', true ) : 'no';
					echo "<ul class='event-custom-price'>";
					if ( $recurring == 'everyday' && $time_status == 'no' ) {
						if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 && sizeof( $user_info ) == 0 ) {
							foreach ( $ticket_type_arr as $_event_recurring_date ) {
								if ( $hide_date_status == 'no' ) {
									?>
                                    <li><?php esc_html_e( " Date", 'mage-eventpress' ); ?>: <?php echo esc_html( get_mep_datetime( $_event_recurring_date['event_date'], apply_filters( 'mep_cart_date_format', 'date-time-text' ) ) ); ?></li>
									<?php
								}
							}
						}
						if ( is_array( $user_info ) && sizeof( $user_info ) > 0 ) {
							echo '<li>';
							echo mep_cart_display_user_list( $user_info, $eid );
							echo '</li>';
						}
					} elseif ( $recurring == 'everyday' && $time_status == 'yes' ) {
						if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 && sizeof( $user_info ) == 0 ) {
							foreach ( $ticket_type_arr as $_event_recurring_date ) {
								if ( $hide_date_status == 'no' ) {
									?>
                                    <li><?php esc_html_e( " Date", 'mage-eventpress' ); ?>: <?php echo esc_html( get_mep_datetime( $_event_recurring_date['event_date'], apply_filters( 'mep_cart_date_format', 'date-time-text' ) ) ); ?></li>
									<?php
								}
							}
						}
						if ( is_array( $user_info ) && sizeof( $user_info ) > 0 ) {
							echo '<li>' . mep_cart_display_user_list( $user_info, $eid ) . '</li>';
						}
					} elseif ( $recurring == 'yes' ) {
						if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 && sizeof( $user_info ) == 0 && $hide_date_status == 'no' ) {
							foreach ( $ticket_type_arr as $_event_recurring_date ) {
								?>
                                <li><?php esc_html_e( " Date", 'mage-eventpress' ); ?>: <?php echo esc_html( get_mep_datetime( $_event_recurring_date['event_date'], apply_filters( 'mep_cart_date_format', 'date-text' ) ) ); ?></li>
								<?php
							}
						}
						if ( is_array( $user_info ) && sizeof( $user_info ) > 0 ) {
							echo '<li>' . mep_cart_display_user_list( $user_info, $eid ) . '</li>';
						}
					} else {
						if ( is_array( $user_info ) && sizeof( $user_info ) > 0 ) {
							echo '<li>' . mep_cart_display_user_list( $user_info, $eid ) . '</li>';
						} else {
							if ( $hide_date_status == 'no' ) {
								?>
                                <li><?php esc_html_e( " Date", 'mage-eventpress' ); ?>: <?php echo esc_html( get_mep_datetime( $cart_item['event_cart_display_date'], apply_filters( 'mep_cart_date_format', 'date-time-text' ) ) ); ?></li>
								<?php
							}
						}
					}
					if ( $hide_location_status == 'no' ) {
						?>
                        <li><?php esc_html_e( " Location", 'mage-eventpress' ); ?>: <?php echo esc_html( $cart_item['event_cart_location'] ); ?></li>
						<?php
					}
					if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
						echo mep_cart_display_ticket_type_list( $ticket_type_arr, $eid );
					}
					if ( is_array( $event_extra_service ) && sizeof( $event_extra_service ) > 0 ) {
						foreach ( $event_extra_service as $extra_service ) {
							echo '<li>' . esc_html( $extra_service['service_name'] ) . " - " . wc_price( esc_html( mep_get_price_including_tax( $eid, $extra_service['service_price'] ) ) ) . ' x ' . esc_html( $extra_service['service_qty'] ) . ' = ' . wc_price( esc_html( mep_get_price_including_tax( $eid, (float) $extra_service['service_price'] * (float) $extra_service['service_qty'] ) ) ) . '</li>';
						}
					}
					do_action( 'mep_after_cart_item_display_list', $cart_item );
					echo "</ul>";
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
						$recurring  = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
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
							wc_add_notice( "Sorry, $type not availabe. Total available $type is $available_seat of $event_name on $event_date_txt but you select $ticket_qty . Please Try Again", 'error' );
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

			public function checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
				$eid           = array_key_exists( 'event_id', $values ) ? $values['event_id'] : 0; //$values['event_id'];
				$location_text = mep_get_option( 'mep_location_text', 'label_setting_sec', esc_html__( 'Location', 'mage-eventpress' ) );
				$date_text     = mep_get_option( 'mep_event_date_text', 'label_setting_sec', esc_html__( 'Date', 'mage-eventpress' ) );
				if ( get_post_type( $eid ) == 'mep_events' ) {
					$event_id                = $eid;
					$mep_events_extra_prices = array_key_exists( 'event_extra_option', $values ) ? $values['event_extra_option'] : [];
					$cart_location           = array_key_exists( 'event_cart_location', $values ) ? $values['event_cart_location'] : '';
					$event_extra_service     = array_key_exists( 'event_extra_service', $values ) ? $values['event_extra_service'] : [];
					$ticket_type_arr         = array_key_exists( 'event_ticket_info', $values ) ? $values['event_ticket_info'] : '';
					$cart_date               = get_mep_datetime( $values['event_cart_date'], 'date-time-text' );
					$form_position           = mep_get_option( 'mep_user_form_position', 'general_attendee_sec', 'details_page' );
					$event_user_info         = $form_position == 'details_page' ? $values['event_user_info'] : mep_save_attendee_info_into_cart( $eid );
					$recurring               = get_post_meta( $eid, 'mep_enable_recurring', true ) ? get_post_meta( $eid, 'mep_enable_recurring', true ) : 'no';
					$time_status             = get_post_meta( $eid, 'mep_disable_ticket_time', true ) ? get_post_meta( $eid, 'mep_disable_ticket_time', true ) : 'no';
					if ( $recurring == 'everyday' && $time_status == 'no' ) {
						if ( is_array( $ticket_type_arr ) && sizeof( $ticket_type_arr ) > 0 ) {
							foreach ( $ticket_type_arr as $_event_recurring_date ) {
								$item->add_meta_data( $date_text, get_mep_datetime( $_event_recurring_date['event_date'], apply_filters( 'mep_cart_date_format', 'date-time-text' ) ) );
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
								$item->add_meta_data( __( $key, 'mage-eventpress' ), $userinf[ $value ] );
							}
						}
					}
					if ( is_array( $event_extra_service ) && sizeof( $event_extra_service ) > 0 ) {
						foreach ( $event_extra_service as $extra_service ) {
							$service_type_name = $extra_service['service_name'] . " - " . wc_price( mep_get_price_including_tax( $eid, $extra_service['service_price'] ) ) . ' x ' . $extra_service['service_qty'] . ' = ';
							$service_type_val  = wc_price( mep_get_price_including_tax( $eid, (float) $extra_service['service_price'] * (float) $extra_service['service_qty'] ) );
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
		}
		new MPWEM_Woocommerce();
	}