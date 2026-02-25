<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	appsero_init_tracker_mage_eventpress();


if ( ! function_exists( 'mep_prevent_serialized_input' ) ) {
	function mep_prevent_serialized_input( $value ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}
		// Block any serialized data
		if ( is_serialized( $value ) || preg_match( '/(^|;)O:\d+:"/m', $value ) ) {
			return '';
		}
		return sanitize_text_field( $value );
	}
}





if ( ! function_exists( 'mep_add_show_sku_post_id_in_event_list_dashboard' ) ) {
	function mep_add_show_sku_post_id_in_event_list_dashboard( $actions, $post ) {
		if ( $post->post_type === 'mep_events' ) {
			$custom_meta_value = get_post_meta( $post->ID, '_sku', true ) ? 'SKU: ' . get_post_meta( $post->ID, '_sku', true ) : 'ID: ' . $post->ID;
			if ( ! empty( $custom_meta_value ) ) {
				$custom_action = [
						'custom_meta' => '<span style="color:rgb(117, 111, 111); font-weight: bold;">' . esc_html( $custom_meta_value ) . '</span>'
					];
					$actions       = array_merge( $custom_action, $actions );
				}
			}
			return $actions;
		}
	}
	add_filter( 'post_row_actions', 'mep_add_show_sku_post_id_in_event_list_dashboard', 10, 2 );
	add_filter( 'mep_events_post_type_show_in_rest', 'mep_rest_api_status_check' );
	add_filter( 'mep_event_attendees_type_show_in_rest', 'mep_rest_api_status_check' );
	add_filter( 'mep_speaker_post_type_show_in_rest', 'mep_rest_api_status_check' );
	function mep_rest_api_status_check( $status ) {
		$user_settings_status = mep_get_option( 'mep_rest_api_status', 'general_setting_sec', 'disable' );
		$status               = $user_settings_status == 'enable' ? true : false;
		return $status;
	}
	add_action( 'admin_init', 'mep_flush_rules_event_list_page' );
	function mep_flush_rules_event_list_page() {
		// Only allow logged-in admins
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// Check if this is your specific page
		if ( isset( $_GET['post_type'], $_GET['page'], $_GET['_mep_flush_nonce'] )
		     && sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) === 'mep_events'
		     && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'mep_event_lists'
		     && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_mep_flush_nonce'] ) ), 'mep_flush_rules_action' )
		) {
			flush_rewrite_rules();
		}
	}
	if ( ! function_exists( 'mep_isValidFilename' ) ) {
		function mep_isValidFilename( $filename ) {
			// Define the allowed pattern: lowercase letters, numbers, hyphens, and must end with .php
			$pattern = '/^[a-z0-9-]+\.php$/';
			// Check if the filename matches the pattern
			return preg_match( $pattern, $filename ) === 1;
		}
	}
	function mep_letters_numbers_spaces_only( $value ) {
		// Set encoding explicitly
		mb_regex_encoding( 'UTF-8' );
		return mb_ereg_replace( '[^[:alnum:][:space:]]+', '', $value );
	}
	if ( ! function_exists( 'mep_temp_attendee_create_for_cart_ticket_array' ) ) {
		function mep_temp_attendee_create_for_cart_ticket_array( $event_id, $ticket_type ) {
			foreach ( $ticket_type as $ticket ) {
				mep_temp_attendee_create_for_cart( $event_id, $ticket['ticket_name'], $ticket['ticket_qty'], $ticket['event_date'] );
			}
		}
	}
	if ( ! function_exists( 'mep_temp_attendee_create_for_cart' ) ) {
		function mep_temp_attendee_create_for_cart( $event_id, $ticket_type, $ticket_qty, $event_date ) {
			$new_post = array(
				'post_title'    => 'Temp User For' . get_the_title( $event_id ) . '_' . $event_date,
				'post_content'  => '',
				'post_category' => array(),  // Usable for custom taxonomies too
				'tags_input'    => array(),
				'post_status'   => 'publish', // Choose: publish, preview, future, draft, etc.
				'post_type'     => 'mep_temp_attendee'  //'post',page' or use a custom post type if you want to
			);
			//SAVE THE POST
			$pid          = wp_insert_post( $new_post );
			$current_time = current_time( 'Y-m-d H:i:s' );
			update_post_meta( $pid, 'event_id', $event_id );
			update_post_meta( $pid, 'ticket_type', $ticket_type );
			update_post_meta( $pid, 'ticket_qty', $ticket_qty );
			update_post_meta( $pid, 'event_date', $event_date );
			update_post_meta( $pid, 'added_time', $current_time );
		}
	}
	if ( ! function_exists( 'mep_temp_attendee_delete_for_cart' ) ) {
		function mep_temp_attendee_delete_for_cart( $event_id, $ticket_type, $ticket_qty, $event_date ) {
			$args = array(
				'post_type'      => array( 'mep_temp_attendee' ),
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'event_id',
						'value'   => $event_id,
						'compare' => '='
					),
					array(
						'key'     => 'ticket_type',
						'value'   => $ticket_type,
						'compare' => '='
					),
					array(
						'key'     => 'ticket_qty',
						'value'   => $ticket_qty,
						'compare' => '='
					),
					array(
						'key'     => 'event_date',
						'value'   => $event_date,
						'compare' => '='
					)
				)
			);
			$loop = new WP_Query( $args );
			foreach ( $loop->posts as $ticket ) {
				$post_id = $ticket->ID;
				wp_delete_post( $post_id, true );
			}
		}
	}
	if ( ! function_exists( 'mep_temp_attendee_auto_delete_for_cart' ) ) {
		function mep_temp_attendee_auto_delete_for_cart() {
			global $woocommerce;
			$cart_clear_time     = mep_get_option( 'mep_ticket_expire_time_on_cart', 'general_setting_sec', 10 );
			$cart_clear_time_sec = ! empty( $cart_clear_time ) || $cart_clear_time > 0 ? $cart_clear_time * 60 : 600;
			$args                = array(
				'post_type'      => array( 'mep_temp_attendee' ),
				'posts_per_page' => - 1,
			);
			$loop                = new WP_Query( $args );
			if ( $loop->post_count > 0 ) {
				foreach ( $loop->posts as $ticket ) {
					$post_id   = $ticket->ID;
					$post_date = get_the_date( 'Y-m-d H:i:s', $post_id );
					$time_diff = mep_diff_two_datetime( $post_date, current_time( 'Y-m-d H:i:s' ) );
					if ( $time_diff > $cart_clear_time_sec ) {
						wp_delete_post( $post_id, true );
							if ( ! empty( $woocommerce ) && ! empty( $woocommerce->cart ) ) {
							$woocommerce->cart->empty_cart();
						}
					}
				}
			}
		}
	}
	if ( ! function_exists( 'mep_temp_event_cart_empty' ) ) {
		function mep_temp_event_cart_empty() {
			$args = array(
				'post_type'      => array( 'mep_temp_attendee' ),
				'posts_per_page' => - 1,
			);
			$loop = new WP_Query( $args );
			if ( $loop->post_count > 0 ) {
				foreach ( $loop->posts as $ticket ) {
					$post_id = $ticket->ID;
					wp_delete_post( $post_id, true );
				}
			}
		}
	}
	add_action( 'init', 'mep_auto_load' );
	function mep_auto_load() {
		if ( ! is_admin() && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			mep_temp_attendee_auto_delete_for_cart();
		}
	}
	if ( ! function_exists( 'mep_event_cart_temp_count' ) ) {
		function mep_event_cart_temp_count() {
			$args = array(
				'post_type'      => array( 'mep_temp_attendee' ),
				'posts_per_page' => - 1
			);
			$loop = new WP_Query( $args );
			$qty  = 0;
			if ( $loop->post_count > 0 ) {
				foreach ( $loop->posts as $ticket ) {
					$post_id = $ticket->ID;
					$_qty    = get_post_meta( $post_id, 'ticket_qty', true ) ? get_post_meta( $post_id, 'ticket_qty', true ) : 0;
					$qty     = $qty + $_qty;
				}
			}
			return $qty;
		}
	}
	if ( ! function_exists( 'mep_temp_attendee_count' ) ) {
		function mep_temp_attendee_count( $event_id, $ticket_type, $event_date ) {
			$args = array(
				'post_type'      => array( 'mep_temp_attendee' ),
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'event_id',
						'value'   => $event_id,
						'compare' => '='
					),
					array(
						'key'     => 'ticket_type',
						'value'   => $ticket_type,
						'compare' => '='
					),
					array(
						'key'     => 'event_date',
						'value'   => $event_date,
						'compare' => 'LIKE'
					)
				)
			);
			$loop = new WP_Query( $args );
			$qty  = 0;
			if ( $loop->post_count > 0 ) {
				foreach ( $loop->posts as $ticket ) {
					$post_id = $ticket->ID;
					$_qty    = get_post_meta( $post_id, 'ticket_qty', true ) ? get_post_meta( $post_id, 'ticket_qty', true ) : 0;
					$qty     = $qty + $_qty;
				}
			}
			return $qty;
		}
	}
	function mep_get_page_by_slug( $page_slug, $output = OBJECT, $post_type = 'page' ) {
		global $wpdb;
		if ( is_array( $post_type ) ) {
			$post_type           = esc_sql( $post_type );
			$post_type_in_string = "'" . implode( "','", $post_type ) . "'";
			$sql                 = $wpdb->prepare( "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_name = %s
			AND post_type IN ($post_type_in_string)
		", $page_slug );
		} else {
			$sql = $wpdb->prepare( "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_name = %s
			AND post_type = %s
		", $page_slug, $post_type );
		}
		$page = $wpdb->get_var( $sql );
		if ( $page ) {
			return get_post( $page, $output );
		}
		return null;
	}
	function mep_add_event_into_feed_request( $qv ) {
		if ( isset( $qv['feed'] ) ) {
			// If 'post_type' is already set, make sure it's an array
			if ( isset( $qv['post_type'] ) ) {
				$post_types = (array) $qv['post_type'];
			} else {
				// Default post type for feeds is 'post'
				$post_types = array( 'post' );
			}
			// Add 'mep_events' if not already present
			if ( ! in_array( 'mep_events', $post_types ) ) {
				$post_types[] = 'mep_events';
			}
			$qv['post_type'] = $post_types;
		}
		return $qv;
	}
	add_filter( 'request', 'mep_add_event_into_feed_request' );
	if ( ! function_exists( 'mepfix_sitemap_exclude_post_type' ) ) {
		function mepfix_sitemap_exclude_post_type() {
			return [ 'auto-draft' ];
		}
	}
	if ( ! function_exists( 'mep_get_all_tax_list' ) ) {
		function mep_get_all_tax_list( $current_tax = null ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'wc_tax_rate_classes';
			$result     = $wpdb->get_results( "SELECT * FROM $table_name" );
			foreach ( $result as $tax ) {
				?>
                <option value="<?php echo esc_attr( $tax->slug ); ?>" <?php if ( $current_tax == $tax->slug ) {
					echo 'Selected';
				} ?>><?php echo esc_html( $tax->name ); ?></option>
				<?php
			}
		}
	}
// Class for Linking with Woocommerce with Event Pricing
	add_action( 'init', 'mep_load_wc_class' );
	if ( ! function_exists( 'mep_load_wc_class' ) ) {
		function mep_load_wc_class() {
			if ( class_exists( 'WC_Product_Data_Store_CPT' ) ) {
				class MEP_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {
					public function read( &$product ) {
						$product->set_defaults();
						if ( ! $product->get_id() || ! ( $post_object = get_post( $product->get_id() ) ) || ! in_array( $post_object->post_type, array( 'mep_events', 'product' ) ) ) { // change birds with your post type
							throw new Exception( __( 'Invalid product.', 'mage-eventpress' ) );
						}
						$id = $product->get_id();
						$product->set_props( array(
							'name'              => $post_object->post_title,
							'slug'              => $post_object->post_name,
							'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
							'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
							'product_id'        => $post_object->ID,
							'sku'               => $post_object->ID,
							'status'            => $post_object->post_status,
							'description'       => $post_object->post_content,
							'short_description' => $post_object->post_excerpt,
							'parent_id'         => $post_object->post_parent,
							'menu_order'        => $post_object->menu_order,
							'reviews_allowed'   => 'open' === $post_object->comment_status,
						) );
						$this->read_attributes( $product );
						$this->read_downloads( $product );
						$this->read_visibility( $product );
						$this->read_product_data( $product );
						$this->read_extra_data( $product );
						$product->set_object_read( true );
					}
					/**
					 * Get the product type based on product ID.
					 *
					 * @param int $product_id
					 *
					 * @return bool|string
					 * @since 3.0.0
					 */
					public function get_product_type( $product_id ) {
						$post_type = get_post_type( $product_id );
						if ( 'product_variation' === $post_type ) {
							return 'variation';
						} elseif ( in_array( $post_type, array( 'mep_events', 'product' ) ) ) { // change birds with your post type
							$terms = get_the_terms( $product_id, 'product_type' );
							return ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';
						} else {
							return false;
						}
					}
				}
			}
		}
	}
	if ( ! function_exists( 'mep_get_attendee_info_query' ) ) {
		function mep_get_attendee_info_query( $event_id, $order_id ) {
			$_user_set_status    = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
			$_order_status       = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
			$order_status        = array_values( $_order_status );
			$order_status_filter = array(
				'key'     => 'ea_order_status',
				'value'   => $order_status,
				'compare' => 'IN'
			);
			$args                = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'relation' => 'AND',
						array(
							'key'     => 'ea_event_id',
							'value'   => $event_id,
							'compare' => '='
						),
						array(
							'key'     => 'ea_order_id',
							'value'   => $order_id,
							'compare' => '='
						)
					),
					$order_status_filter
				)
			);
			$loop                = new WP_Query( $args );
			return $loop;
		}
	}
	if ( ! function_exists( 'mep_email_dynamic_content' ) ) {
		function mep_email_dynamic_content( $email_body, $event_id, $order_id, $__attendee_id = 0 ) {
			$event_name   = get_the_title( $event_id );
			$attendee_q   = mep_get_attendee_info_query( $event_id, $order_id );
			$_attendee_id = 0; // Initialize to avoid undefined variable warning
			foreach ( $attendee_q->posts as $_attendee_q ) {
				$_attendee_id = $_attendee_q->ID;
			}
			$attendee_id   = $__attendee_id > 0 ? $__attendee_id : $_attendee_id;
			$attendee_name = get_post_meta( $attendee_id, 'ea_name', true ) ?: '';
			$email         = get_post_meta( $attendee_id, 'ea_email', true ) ?: '';
			$date_time     = get_post_meta( $attendee_id, 'ea_event_date', true ) ? get_mep_datetime( get_post_meta( $attendee_id, 'ea_event_date', true ), 'date-time-text' ) : '';
			$date          = get_post_meta( $attendee_id, 'ea_event_date', true ) ? get_mep_datetime( get_post_meta( $attendee_id, 'ea_event_date', true ), 'date-text' ) : '';
			$time          = get_post_meta( $attendee_id, 'ea_event_date', true ) ? get_mep_datetime( get_post_meta( $attendee_id, 'ea_event_date', true ), 'time' ) : '';
			$ticket_type   = get_post_meta( $attendee_id, 'ea_ticket_type', true ) ?: '';
			$payment_method = get_post_meta( $attendee_id, 'ea_payment_method', true ) ?: '';
			$amount_paid    = '';
			$order          = wc_get_order( $order_id );
			if ( $order instanceof WC_Order ) {
				$payment_method = $order->get_payment_method_title();
				$amount_paid    = wc_price( (float) $order->get_total() );
			} else {
				$attendee_amount = get_post_meta( $attendee_id, 'ea_ticket_order_amount', true );
				$amount_paid     = '' !== $attendee_amount ? wc_price( (float) $attendee_amount ) : '';
			}
			$email_body    = str_replace( "{name}", $attendee_name, $email_body );
			$email_body    = str_replace( "{email}", $email, $email_body );
			$email_body    = str_replace( "{event}", $event_name, $email_body );
			$email_body    = str_replace( "{event_date}", $date, $email_body );
			$email_body    = str_replace( "{event_time}", $time, $email_body );
			$email_body    = str_replace( "{event_datetime}", $date_time, $email_body );
			$email_body    = str_replace( "{ticket_type}", $ticket_type, $email_body );
			$email_body    = str_replace( "{order_id}", $order_id, $email_body );
			$email_body    = str_replace( "{payment_method}", $payment_method, $email_body );
			$email_body    = str_replace( "{amount_paid}", $amount_paid, $email_body );
			return $email_body;
		}
	}
	// Send Confirmation email to customer
	if ( ! function_exists( 'mep_event_confirmation_email_sent' ) ) {
		function mep_event_confirmation_email_sent( $event_id, $sent_email, $order_id, $attendee_id = 0 ) {
			// Global Email Settings
			$global_email_text       = mep_get_option( 'mep_confirmation_email_text', 'email_setting_sec', '' );
			$global_email_form_email = mep_get_option( 'mep_email_form_email', 'email_setting_sec', '' );
			$global_email_form_name  = mep_get_option( 'mep_email_form_name', 'email_setting_sec', '' );
			$global_email_subject    = mep_get_option( 'mep_email_subject', 'email_setting_sec', '' );
			// Site Info
			$admin_email = get_option( 'admin_email' );
			$site_name   = get_option( 'blogname' );
			$form_email  = ! empty( $global_email_form_email ) ? $global_email_form_email : $admin_email;
			$form_name   = ! empty( $global_email_form_name ) ? $global_email_form_name : $site_name;
			$email_sub   = ! empty( $global_email_subject ) ? $global_email_subject : 'Confirmation Email';
			// Event Specific Text
			$event_email_text = get_post_meta( $event_id, 'mep_event_cc_email_text', true );
			$email_body       = ! empty( $event_email_text ) ? $event_email_text : $global_email_text;
			// Dynamic Content Replace
			$email_body = mep_email_dynamic_content( $email_body, $event_id, $order_id, $attendee_id );
			// Allow filter
			$email_body = apply_filters( 'mep_event_confirmation_text', $email_body, $event_id, $order_id );
			// ✨ Format email body properly
			$email_body = wpautop( $email_body );        // Add paragraphs
			$email_body = wp_kses_post( $email_body );   // Secure the content
			// Headers
			$headers   = array();
			$headers[] = "From: $form_name <$form_email>";
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			// Send Email
			wp_mail( $sent_email, $email_sub, $email_body, $headers );
		}
	}
// Function to get page slug
	if ( ! function_exists( 'mep_get_page_by_slug' ) ) {
		function mep_get_page_by_slug( $slug ) {
			if ( $pages = get_pages() ) {
				foreach ( $pages as $page ) {
					if ( $slug === $page->post_name ) {
						return $page;
					}
				}
			}
			return false;
		}
	}
	if ( ! function_exists( 'mep_city_filter_rewrite_rule' ) ) {
		function mep_city_filter_rewrite_rule() {
			add_rewrite_rule(
				'^event-by-city-name/(.+)/?$',
				'index.php?cityname=$matches[1]&pagename=event-by-city-name',
				'top'
			);
		}
	}
	add_action( 'init', 'mep_city_filter_rewrite_rule' );
	if ( ! function_exists( 'mep_city_filter_query_var' ) ) {
		function mep_city_filter_query_var( $vars ) {
			$vars[] = 'cityname';
			return $vars;
		}
	}
	add_filter( 'query_vars', 'mep_city_filter_query_var' );
	if ( ! function_exists( 'mep_get_event_ticket_price_by_name' ) ) {
		function mep_get_event_ticket_price_by_name( $event, $type ) {
			$ticket_type = get_post_meta( $event, 'mep_event_ticket_type', true );
			if ( is_array( $ticket_type ) && sizeof( $ticket_type ) > 0 ) {
				foreach ( $ticket_type as $key => $val ) {
					if ( $val['option_name_t'] === $type ) {
						return array_key_exists( 'option_price_t', $val ) ? $val['option_price_t'] : 0;
					}
				}
				return 0;
			}
		}
	}
	if ( ! function_exists( 'mep_get_ticket_price_by_event' ) ) {
		function mep_get_ticket_price_by_event( $event, $type, $default_price = 0 ) {
			$ticket_type = get_post_meta( $event, 'mep_event_ticket_type', true );
			if ( $ticket_type ) {
				$all_ticket_tyle = get_post_meta( $event, 'mep_event_ticket_type', true );
				foreach ( $all_ticket_tyle as $key => $val ) {
					if ( $val['option_name_t'] === $type ) {
						return array_key_exists( 'option_price_t', $val ) ? (int) $val['option_price_t'] : 0;
					}
				}
			} else {
				return $default_price;
			}
		}
	}
	if ( ! function_exists( 'mep_attendee_create' ) ) {
		function mep_attendee_create( $type, $order_id, $event_id, $_user_info = array(), $force_order_status = 'no' ) {
			// Getting an instance of the order object
			$order             = wc_get_order( $order_id );
			$order_meta        = get_post_meta( $order_id );
			$order_status      = $order instanceof WC_Order ? $order->get_status() : '';
			$payment_method    = $order->get_payment_method_title();
			$user_id           = $order->get_customer_id();
			$first_name        = $order->get_billing_first_name();
			$last_name         = $order->get_billing_last_name();
			$billing_full_name = mep_prevent_serialized_input( $first_name . ' ' . $last_name );
			if ( $type == 'billing' ) {
				// Billing Information
				$company     = isset( $order_meta['_billing_company'][0] ) ? sanitize_text_field( $order_meta['_billing_company'][0] ) : '';
				$address_1   = isset( $order_meta['_billing_address_1'][0] ) ? sanitize_text_field( $order_meta['_billing_address_1'][0] ) : '';
				$address_2   = isset( $order_meta['_billing_address_2'][0] ) ? sanitize_text_field( $order_meta['_billing_address_2'][0] ) : '';
				$address     = $address_1 . ' ' . $address_2;
				$gender      = '';
				$designation = '';
				$website     = '';
				$vegetarian  = '';
				$tshirtsize  = '';
				$email       = isset( $order_meta['_billing_email'][0] ) ? sanitize_text_field( $order_meta['_billing_email'][0] ) : '';
				$phone       = isset( $order_meta['_billing_phone'][0] ) ? sanitize_text_field( $order_meta['_billing_phone'][0] ) : '';
				$ticket_type = stripslashes( sanitize_text_field( $_user_info['ticket_name'] ) );
				$event_date  = sanitize_text_field( $_user_info['event_date'] );
				$ticket_qty  = sanitize_text_field( $_user_info['ticket_qty'] );
			} else {
				$_uname      = array_key_exists( 'user_name', $_user_info ) ? sanitize_text_field( $_user_info['user_name'] ) : "";
				$email       = array_key_exists( 'user_email', $_user_info ) ? sanitize_text_field( $_user_info['user_email'] ) : "";
				$phone       = array_key_exists( 'user_phone', $_user_info ) ? sanitize_text_field( $_user_info['user_phone'] ) : "";
				$address     = array_key_exists( 'user_address', $_user_info ) ? sanitize_text_field( $_user_info['user_address'] ) : "";
				$gender      = array_key_exists( 'user_gender', $_user_info ) ? sanitize_text_field( $_user_info['user_gender'] ) : "";
				$company     = array_key_exists( 'user_company', $_user_info ) ? sanitize_text_field( $_user_info['user_company'] ) : "";
				$designation = array_key_exists( 'user_designation', $_user_info ) ? sanitize_text_field( $_user_info['user_designation'] ) : "";
				$website     = array_key_exists( 'user_website', $_user_info ) ? sanitize_text_field( $_user_info['user_website'] ) : "";
				$vegetarian  = array_key_exists( 'user_vegetarian', $_user_info ) ? sanitize_text_field( $_user_info['user_vegetarian'] ) : "";
				$tshirtsize  = array_key_exists( 'user_tshirtsize', $_user_info ) ? sanitize_text_field( $_user_info['user_tshirtsize'] ) : "";
				$ticket_type = array_key_exists( 'user_ticket_type', $_user_info ) ? stripslashes( $_user_info['user_ticket_type'] ) : "";
				$ticket_qty  = array_key_exists( 'user_ticket_qty', $_user_info ) ? sanitize_text_field( $_user_info['user_ticket_qty'] ) : "";
				$event_date  = array_key_exists( 'user_event_date', $_user_info ) ? sanitize_text_field( $_user_info['user_event_date'] ) : "";
				$event_id    = $_user_info['user_event_id'] ? sanitize_text_field( $_user_info['user_event_id'] ) : $event_id;
			}
			// $ticket_total_price = (int) ( mep_get_event_ticket_price_by_name( $event_id, $ticket_type ) * (int) $ticket_qty );
			$price              = mep_get_event_ticket_price_by_name( $event_id, $ticket_type );
			$price              = (float) preg_replace( '/[^0-9.]/', '', $price );
			$qty                = (int) $ticket_qty;
			$ticket_total_price = (int) ( $price * $qty );
			$uname              = isset( $_uname ) && ! empty( $_uname ) ? $_uname : $billing_full_name;
			$new_post           = array(
				'post_title'    => $uname,
				'post_content'  => '',
				'post_category' => array(),  // Usable for custom taxonomies too
				'tags_input'    => array(),
				'post_status'   => 'publish', // Choose: publish, preview, future, draft, etc.
				'post_type'     => 'mep_events_attendees'  //'post',page' or use a custom post type if you want to
			);
			//SAVE THE POST
			$pid = wp_insert_post( $new_post );
			$pin = $user_id . $order_id . $event_id . $pid;
			update_post_meta( $pid, 'ea_name', mep_prevent_serialized_input( $uname ) );
			update_post_meta( $pid, 'ea_address_1', mep_prevent_serialized_input( $address ) );
			update_post_meta( $pid, 'ea_email', mep_prevent_serialized_input( $email ) );
			update_post_meta( $pid, 'ea_phone', mep_prevent_serialized_input( $phone ) );
			update_post_meta( $pid, 'ea_gender', mep_prevent_serialized_input( $gender ) );
			update_post_meta( $pid, 'ea_company', mep_prevent_serialized_input( $company ) );
			update_post_meta( $pid, 'ea_desg', mep_prevent_serialized_input( $designation ) );
			update_post_meta( $pid, 'ea_website', mep_prevent_serialized_input( $website ) );
			update_post_meta( $pid, 'ea_vegetarian', mep_prevent_serialized_input( $vegetarian ) );
			update_post_meta( $pid, 'ea_tshirtsize', mep_prevent_serialized_input( $tshirtsize ) );
			update_post_meta( $pid, 'ea_ticket_type', $ticket_type );
			update_post_meta( $pid, 'ea_ticket_qty', $ticket_qty );
			update_post_meta( $pid, 'ea_ticket_price', mep_get_ticket_price_by_event( $event_id, $ticket_type, 0 ) );
			update_post_meta( $pid, 'ea_ticket_order_amount', $ticket_total_price );
			update_post_meta( $order_id, 'ea_ticket_qty', $ticket_qty );
			update_post_meta( $order_id, 'ea_ticket_type', $ticket_type );
			update_post_meta( $order_id, 'ea_event_id', $event_id );
			update_post_meta( $pid, 'ea_payment_method', $payment_method );
			update_post_meta( $pid, 'ea_event_name', get_the_title( $event_id ) );
			update_post_meta( $pid, 'ea_event_id', $event_id );
			update_post_meta( $pid, 'ea_order_id', $order_id );
			update_post_meta( $pid, 'ea_user_id', $user_id );
			update_post_meta( $pid, 'mep_checkin', 'No' );
			update_post_meta( $order_id, 'ea_user_id', $user_id );
			update_post_meta( $order_id, 'order_type_name', 'mep_events' );
			update_post_meta( $pid, 'ea_ticket_no', $pin );
			update_post_meta( $pid, 'ea_event_date', $event_date );
			if ( $force_order_status == 'yes' ) {
				update_post_meta( $pid, 'ea_order_status', $order_status );
			}
			update_post_meta( $pid, 'ea_flag', 'checkout_processed' );
			update_post_meta( $order_id, 'ea_order_status', $order_status );
			$hooking_data = apply_filters( 'mep_event_attendee_dynamic_data', array(), $pid, $type, $order_id, $event_id, $_user_info );
			if ( is_array( $hooking_data ) && sizeof( $hooking_data ) > 0 ) {
				foreach ( $hooking_data as $_data ) {
					update_post_meta( $pid, $_data['name'], $_data['value'] );
				}
			}
			// Checking if the form builder addon is active and have any custom fields
			$reg_form_id           = mep_fb_get_reg_form_id( $event_id );
			$mep_form_builder_data = get_post_meta( $reg_form_id, 'mep_form_builder_data', true ) ? get_post_meta( $reg_form_id, 'mep_form_builder_data', true ) : [];
			if ( is_array( $mep_form_builder_data ) && sizeof( $mep_form_builder_data ) > 0 ) {
				foreach ( $mep_form_builder_data as $_field ) {
					update_post_meta( $pid, "ea_" . $_field['mep_fbc_id'], $_user_info[ $_field['mep_fbc_id'] ] );
					do_action( 'mep_attendee_upload_file_save', $event_id, $_user_info, $_field );
				}
			} // End User Form builder data update loop
		}
	}
	if ( ! function_exists( 'mep_attendee_extra_service_create' ) ) {
		function mep_attendee_extra_service_create( $order_id, $event_id, $_event_extra_service ) {
			$order        = wc_get_order( $order_id );
			$order_status = $order->get_status();
			if ( is_array( $_event_extra_service ) && sizeof( $_event_extra_service ) > 0 ) {
				foreach ( $_event_extra_service as $extra_serive ) {
					if ( $extra_serive['service_name'] ) {
						$uname    = 'Extra Service for ' . get_the_title( $event_id ) . ' Order #' . $order_id;
						$new_post = array(
							'post_title'    => $uname,
							'post_content'  => '',
							'post_category' => array(),
							'tags_input'    => array(),
							'post_status'   => 'publish',
							'post_type'     => 'mep_extra_service'
						);
						$pid      = wp_insert_post( $new_post );
						update_post_meta( $pid, 'ea_extra_service_name', $extra_serive['service_name'] );
						update_post_meta( $pid, 'ea_extra_service_qty', $extra_serive['service_qty'] );
						update_post_meta( $pid, 'ea_extra_service_unit_price', $extra_serive['service_price'] );
						update_post_meta( $pid, 'ea_extra_service_total_price', $extra_serive['service_qty'] * (float) $extra_serive['service_price'] );
						update_post_meta( $pid, 'ea_extra_service_event', $event_id );
						update_post_meta( $pid, 'ea_extra_service_order', $order_id );
						update_post_meta( $pid, 'ea_extra_service_order_status', $order_status );
						update_post_meta( $pid, 'ea_extra_service_event_date', $extra_serive['event_date'] );
					}
				}
			}
		}
	}
	if ( ! function_exists( 'mep_check_attendee_exist_before_create' ) ) {
		function mep_check_attendee_exist_before_create( $order_id, $event_id, $date = '' ) {
			$date_filter              = ! empty( $date ) ? array(
				'key'     => 'ea_event_date',
				'value'   => $date,
				'compare' => 'LIKE'
			) : '';
			$pending_status_filter    = array(
				'key'     => 'ea_order_status',
				'value'   => 'pending',
				'compare' => '='
			);
			$hold_status_filter       = array(
				'key'     => 'ea_order_status',
				'value'   => 'on-hold',
				'compare' => '='
			);
			$processing_status_filter = array(
				'key'     => 'ea_order_status',
				'value'   => 'processing',
				'compare' => '='
			);
			$completed_status_filter  = array(
				'key'     => 'ea_order_status',
				'value'   => 'completed',
				'compare' => '='
			);
			$args                     = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'relation' => 'AND',
						array(
							'key'     => 'ea_event_id',
							'value'   => $event_id,
							'compare' => '='
						),
						array(
							'key'     => 'ea_order_id',
							'value'   => $order_id,
							'compare' => '='
						),
						$date_filter
					),
					array(
						'relation' => 'OR',
						$pending_status_filter,
						$hold_status_filter,
						$processing_status_filter,
						$completed_status_filter
					)
				)
			);
			$loop                     = new WP_Query( $args );
			return $loop->post_count;
		}
	}
	if ( ! function_exists( 'mep_diff_two_datetime' ) ) {
		function mep_diff_two_datetime( $d1, $d2 ) {
			$timeFirst  = strtotime( $d1 );
			$timeSecond = strtotime( $d2 );
			return $differenceInSeconds = $timeSecond - $timeFirst;
		}
	}
	if ( ! function_exists( 'mep_delete_attandee_of_an_order' ) ) {
		function mep_delete_attandee_of_an_order( $order_id, $event_id ) {
			$args = array(
				'post_type'      => array( 'mep_events_attendees' ),
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'ea_order_id',
						'value'   => $order_id,
						'compare' => '='
					),
					array(
						'key'     => 'ea_event_id',
						'value'   => $event_id,
						'compare' => '='
					),
					array(
						'key'     => 'ea_flag',
						'value'   => 'checkout_processed',
						'compare' => '='
					)
				)
			);
			$loop = new WP_Query( $args );
			foreach ( $loop->posts as $ticket ) {
				$post_id   = $ticket->ID;
				$post_date = get_the_date( 'Y-m-d H:i:s', $post_id );
				$time_diff = mep_diff_two_datetime( $post_date, current_time( 'Y-m-d H:i:s' ) );
				if ( $time_diff > 15 ) {
					wp_delete_post( $post_id, true );
				}
			}
		}
	}
	if ( ! function_exists( 'change_attandee_order_status' ) ) {
		function change_attandee_order_status( $order_id, $set_status, $post_status, $qr_status = null ) {
			add_filter( 'wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5 );
			$args = array(
				'post_type'      => array( 'mep_events_attendees' ),
				'posts_per_page' => - 1,
				'post_status'    => $post_status,
				'meta_query'     => array(
					array(
						'key'     => 'ea_order_id',
						'value'   => $order_id,
						'compare' => '='
					)
				)
			);
			$loop = new WP_Query( $args );
			$tid  = array();
			foreach ( $loop->posts as $ticket ) {
				$post_id = $ticket->ID;
				update_post_meta( $post_id, 'ea_order_status', $qr_status );
				update_post_meta( $post_id, 'ea_flag', $qr_status );
				$current_post                = get_post( $post_id, 'ARRAY_A' );
				$current_post['post_status'] = $set_status;
				wp_update_post( $current_post );
			}
		}
	}
	if ( ! function_exists( 'change_extra_service_status' ) ) {
		function change_extra_service_status( $order_id, $set_status, $post_status, $qr_status = null ) {
			add_filter( 'wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5 );
			$args = array(
				'post_type'      => array( 'mep_extra_service' ),
				'posts_per_page' => - 1,
				'post_status'    => $post_status,
				'meta_query'     => array(
					array(
						'key'     => 'ea_extra_service_order',
						'value'   => $order_id,
						'compare' => '='
					)
				)
			);
			$loop = new WP_Query( $args );
			$tid  = array();
			foreach ( $loop->posts as $ticket ) {
				$post_id = $ticket->ID;
				update_post_meta( $post_id, 'ea_extra_service_order_status', $qr_status );
				$current_post                = get_post( $post_id, 'ARRAY_A' );
				$current_post['post_status'] = $set_status;
				wp_update_post( $current_post );
			}
		}
	}
	if ( ! function_exists( 'mep_change_wc_event_product_status' ) ) {
		function mep_change_wc_event_product_status( $order_id, $set_status, $post_status, $qr_status = null ) {
			add_filter( 'wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5 );
			$args = array(
				'post_type'      => array( 'product' ),
				'posts_per_page' => - 1,
				'post_status'    => $post_status,
				'meta_query'     => array(
					array(
						'key'     => 'link_mep_event',
						'value'   => $order_id,
						'compare' => '='
					)
				)
			);
			$loop = new WP_Query( $args );
			$tid  = array();
			foreach ( $loop->posts as $ticket ) {
				$post_id = $ticket->ID;
				if ( ! empty( $qr_status ) ) {
					//update_post_meta($post_id, 'ea_order_status', $qr_status);
				}
				$current_post                = get_post( $post_id, 'ARRAY_A' );
				$current_post['post_status'] = $set_status;
				wp_update_post( $current_post );
			}
		}
	}
	add_action( 'wp_trash_post', 'mep_addendee_trash', 90 );
	if ( ! function_exists( 'mep_addendee_trash' ) ) {
		function mep_addendee_trash( $post_id ) {
			$post_type   = get_post_type( $post_id );
			$post_status = get_post_status( $post_id );
			if ( $post_type == 'shop_order' ) {
				change_attandee_order_status( $post_id, 'trash', 'publish', '' );
				change_extra_service_status( $post_id, 'trash', 'publish', '' );
			}
			if ( $post_type == 'mep_events' ) {
				mep_change_wc_event_product_status( $post_id, 'trash', 'publish', '' );
			}
		}
	}
	add_action( 'untrash_post', 'mep_addendee_untrash', 90 );
	if ( ! function_exists( 'mep_addendee_untrash' ) ) {
		function mep_addendee_untrash( $post_id ) {
			$post_type   = get_post_type( $post_id );
			$post_status = get_post_status( $post_id );
			if ( $post_type == 'shop_order' ) {
				$order        = wc_get_order( $post_id );
				$order_status = $order->get_status();
				change_attandee_order_status( $post_id, 'publish', 'trash', '' );
				change_extra_service_status( $post_id, 'publish', 'trash', '' );
			}
			if ( $post_type == 'mep_events' ) {
				mep_change_wc_event_product_status( $post_id, 'publish', 'trash', '' );
			}
		}
	}
	function mep_update_ticket_type_seat( $event_id, $ticket_type_name, $event_date, $total_quantity, $total_resv_quantity ) {
		$total_sold = (int) mep_ticket_type_sold( $event_id, $ticket_type_name, $event_date );
		// $ticket_type_left       = (int) $total_quantity - ((int) $total_sold + (int) $total_resv_quantity);
		$ticket_type_left      = (int) $total_sold;
		$_date                 = date( 'YmdHi', strtotime( $event_date ) );
		$ticket_type_meta_name = $ticket_type_name . '_' . $_date;
		update_post_meta( $event_id, $ticket_type_meta_name, $ticket_type_left );
		return get_post_meta( $event_id, $ticket_type_meta_name, true );
	}
	function mep_update_event_total_seat( $event_id, $date = '' ) {
		$seat_left = mep_get_count_total_available_seat( $event_id );
		update_post_meta( $event_id, 'mep_total_seat_left', $seat_left );
		if ( ! empty( $date ) ) {
			$_date          = ! empty( $date ) ? date( 'YmdHi', strtotime( $date ) ) : 0;
			$event_name     = $event_id . '_' . $_date;
			$seat_left_date = mep_get_count_total_available_seat( $event_id, $date );
			update_post_meta( $event_id, $event_name, $seat_left_date );
		}
		$date      = ! empty( $date ) ? date( 'YmdHi', strtotime( $date ) ) : 0;
		$meta_name = $date > 0 ? $event_id . '_' . $date : 'mep_total_seat_left';
		return get_post_meta( $event_id, $meta_name, true );
	}
	function mep_get_event_total_seat_left( $event_id, $date = '' ) {
		$date          = ! empty( $date ) ? date( 'YmdHi', strtotime( $date ) ) : 0;
		$meta_name     = $date > 0 ? $event_id . '_' . $date : 'mep_total_seat_left';
		$availabe_seat = ! empty( get_post_meta( $event_id, $meta_name, true ) ) ? get_post_meta( $event_id, $meta_name, true ) : mep_update_event_total_seat( $event_id, $date );
		return $availabe_seat;
	}
	function mep_get_ticket_type_seat_count( $event_id, $name, $date, $total, $reserved ) {
		$_date                 = date( 'YmdHi', strtotime( $date ) );
		$ticket_type_meta_name = $name . '_' . $_date;
		$availabe_seat         = ! empty( get_post_meta( $event_id, $ticket_type_meta_name, true ) ) ? get_post_meta( $event_id, $ticket_type_meta_name, true ) : mep_update_ticket_type_seat( $event_id, $name, $date, $total, $reserved );
		// $availabe_seat          = mep_update_ticket_type_seat($event_id,$name,$date,$total,$reserved);
		// return $availabe_seat;
		$temp_count = mep_temp_attendee_count( $event_id, $name, $date );
		return (int) $availabe_seat + (int) $temp_count;
	}
	if ( ! function_exists( 'mep_get_count_total_available_seat' ) ) {
		function mep_get_count_total_available_seat( $event_id, $date = '' ) {
			$total_seat = mep_event_total_seat( $event_id, 'total' );
			$total_resv = mep_event_total_seat( $event_id, 'resv' );
			$total_sold = mep_ticket_type_sold( $event_id, '', $date );
			// $total_left = $total_seat - ($total_sold + $total_resv);
			$total_left = $total_sold;
			return esc_html( $total_left );
		}
	}
	if ( ! function_exists( 'mep_reset_event_booking' ) ) {
		function mep_reset_event_booking( $event_id ) {
			add_filter( 'wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5 );
			$mep_event_ticket_type = get_post_meta( $event_id, 'mep_event_ticket_type', true );
			$date                  = MPWEM_Functions::get_upcoming_date_time( $event_id );
			$args_search_qqq       = array(
				'post_type'      => array( 'mep_events_attendees' ),
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => 'ea_event_id',
						'value'   => $event_id,
						'compare' => '='
					)
				)
			);
			$loop                  = new WP_Query( $args_search_qqq );
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$post_id                     = get_the_id();
				$status                      = 'trash';
				$current_post                = get_post( $post_id, 'ARRAY_A' );
				$current_post['post_status'] = $status;
				wp_update_post( $current_post );
			}
			if ( $mep_event_ticket_type ) {
				foreach ( $mep_event_ticket_type as $field ) {
					$name = $field['option_name_t'];
					mep_update_ticket_type_seat( $event_id, $name, $date, 0, 0 );
				}
			}
			mep_update_event_total_seat( $event_id, $date );
			return true;
		}
	}
	function mep_update_event_seat_inventory( $event_id, $ticket_array, $type = 'order' ) {
		$seat_left = mep_get_count_total_available_seat( $event_id );
		foreach ( $ticket_array as $ticket ) {
			$name                = $ticket['ticket_name'];
			$date                = date( 'Y-m-d H:i', strtotime( $ticket['event_date'] ) );
			$_date               = date( 'YmdHi', strtotime( $date ) );
			$total_quantity      = (int) mep_get_ticket_type_info_by_name( $name, $event_id );
			$total_resv_quantity = (int) mep_get_ticket_type_info_by_name( $name, $event_id, 'option_rsv_t' );
			$total_sold_type     = (int) mep_ticket_type_sold( $event_id, $name, $date );
			$seat_left_date      = mep_get_count_total_available_seat( $event_id, $date );
			// $ticket_type_left      = (int) $total_quantity - ((int) $total_sold_type + (int) $total_resv_quantity);
			$ticket_type_left      = (int) $total_sold_type;
			$ticket_type_meta_name = $name . '_' . $_date;
			$event_name            = $event_id . '_' . $_date;
			//  Update Total Seat Count
			update_post_meta( $event_id, 'mep_total_seat_left', $seat_left );
			// Update Ticket Type Seat Count
			update_post_meta( $event_id, $ticket_type_meta_name, $ticket_type_left );
			// Update Total Event By Date Seat Count
			update_post_meta( $event_id, $event_name, $seat_left_date );
			// mep_update_ticket_type_seat($event_id,$name,$date,$total_quantity,$total_resv_quantity);
			mep_temp_attendee_delete_for_cart( $event_id, $ticket['ticket_name'], $ticket['ticket_qty'], $ticket['event_date'] );
		}
	}
	function mep_update_ticket_type_stat( $event_id, $ticket_name, $date ) {
		$date                  = date( 'Y-m-d H:i', strtotime( $date ) );
		$_date                 = date( 'YmdHi', strtotime( $date ) );
		$name                  = $ticket_name;
		$total_quantity        = (int) mep_get_ticket_type_info_by_name( $name, $event_id );
		$total_resv_quantity   = (int) mep_get_ticket_type_info_by_name( $name, $event_id, 'option_rsv_t' );
		$total_sold_type       = (int) mep_ticket_type_sold( $event_id, $name, $date );
		$seat_left_date        = mep_get_count_total_available_seat( $event_id, $date );
		$ticket_type_left      = (int) $total_sold_type;
		$ticket_type_meta_name = $name . '_' . $_date;
		$event_name            = $event_id . '_' . $_date;
		update_post_meta( $event_id, $ticket_type_meta_name, $ticket_type_left );
	}
	add_action( 'mep_ticket_type_loop_list_row_start', 'mep_ticket_type_update_stat', 10, 3 );
	function mep_ticket_type_update_stat( $event_id, $date, $ticket_type ) {
		$ea_attendee_sync = get_post_meta( $event_id, 'ea_attendee_sync', true ) ? get_post_meta( $event_id, 'ea_attendee_sync', true ) : 'no';
		if ( $ea_attendee_sync == 'no' ) {
			$ticket_name = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
			if ( $ticket_name ) {
				mep_update_ticket_type_stat( $event_id, $ticket_name, $date );
			}
		}
	}
	function mep_get_ticket_type_info_by_name( $name, $event_id, $type = 'option_qty_t' ) {
		$ticket_type_arr = get_post_meta( $event_id, 'mep_event_ticket_type', true ) ? get_post_meta( $event_id, 'mep_event_ticket_type', true ) : [];
		$p               = '';
		foreach ( $ticket_type_arr as $price ) {
			$TicketName = array_key_exists( 'option_name_t', $price ) ? str_replace( "'", "", $price['option_name_t'] ) : '';
			if ( $TicketName === $name ) {
				$p = array_key_exists( $type, $price ) ? $price[ $type ] : '';
			}
		}
		return $p;
	}
	add_action( 'restrict_manage_posts', 'mep_filter_post_type_by_taxonomy' );
	if ( ! function_exists( 'mep_filter_post_type_by_taxonomy' ) ) {
		function mep_filter_post_type_by_taxonomy() {
			global $typenow;
			$post_type = 'mep_events'; // change to your post type
			$taxonomy  = 'mep_cat'; // change to your taxonomy
			if ( $typenow == $post_type ) {
				$selected      = isset( $_GET[ $taxonomy ] ) ? mage_array_strip( $_GET[ $taxonomy ] ) : '';
				$info_taxonomy = get_taxonomy( $taxonomy );
				wp_dropdown_categories( array(
					// translators: %s is the taxonomy label.
					'show_option_all' => sprintf( __( 'Show All %s', 'mage-eventpress' ), $info_taxonomy->label ),
					'taxonomy'        => $taxonomy,
					'name'            => $taxonomy,
					'orderby'         => 'name',
					'selected'        => $selected,
					'show_count'      => true,
					'hide_empty'      => true,
				) );
			};
		}
	}
	add_filter( 'parse_query', 'mep_convert_id_to_term_in_query' );
	if ( ! function_exists( 'mep_convert_id_to_term_in_query' ) ) {
		function mep_convert_id_to_term_in_query( $query ) {
			global $pagenow;
			$post_type = 'mep_events'; // change to your post type
			$taxonomy  = 'mep_cat'; // change to your taxonomy
			$q_vars    = &$query->query_vars;
			if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[ $taxonomy ] ) && is_numeric( $q_vars[ $taxonomy ] ) && $q_vars[ $taxonomy ] != 0 ) {
				$term                = get_term_by( 'id', $q_vars[ $taxonomy ], $taxonomy );
				$q_vars[ $taxonomy ] = $term->slug;
			}
		}
	}
	add_filter( 'parse_query', 'mep_attendee_filter_query' );
	if ( ! function_exists( 'mep_attendee_filter_query' ) ) {
		function mep_attendee_filter_query( $query ) {
			global $pagenow;
			$post_type = 'mep_events_attendees';
			$q_vars    = &$query->query_vars;
			if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && mage_array_strip( $_GET['post_type'] ) == $post_type && isset( $_GET['meta_value'] ) && mage_array_strip( $_GET['meta_value'] ) != 0 ) {
				$q_vars['meta_key']   = 'ea_event_id';
				$q_vars['meta_value'] = mage_array_strip( $_GET['meta_value'] );
			} elseif ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && mage_array_strip( $_GET['post_type'] ) == $post_type && isset( $_GET['event_id'] ) && mage_array_strip( $_GET['event_id'] ) != 0 && ! isset( $_GET['action'] ) ) {
				$event_date = date( 'Y-m-d', strtotime( mage_array_strip( $_GET['ea_event_date'] ) ) );
				$meta_query = array(
					[
						'key'     => 'ea_event_id',
						'value'   => mage_array_strip( $_GET['event_id'] ),
						'compare' => '='
					],
					[
						'key'     => 'ea_event_date',
						'value'   => $event_date,
						'compare' => 'LIKE'
					],
					[
						'key'     => 'ea_order_status',
						'value'   => 'completed',
						'compare' => '='
					]
				);
				$query->set( 'meta_query', $meta_query );
			}
		}
	}
	if ( ! function_exists( 'mep_template_file_path' ) ) {
		function mep_template_file_path( $file_name ) {
			$template_path = get_stylesheet_directory() . '/mage-event/';
			$default_path  = plugin_dir_path( __DIR__ ) . 'templates/';
			$thedir        = is_dir( $template_path ) ? $template_path : $default_path;
			$themedir      = $thedir . $file_name;
			$the_file_path = locate_template( array( 'mage-event/' . $file_name ) ) ? $themedir : $default_path . $file_name;
			return $the_file_path;
		}
	}
	if ( ! function_exists( 'mep_calender_date' ) ) {
		function mep_calender_date( $datetime ) {
			$time      = strtotime( $datetime );
			$newdate   = date_i18n( 'Ymd', $time );
			$newtime   = date( 'Hi', $time );
			$newformat = $newdate . "T" . $newtime . "00";
			return $newformat;
		}
	}
	if ( ! function_exists( 'mep_event_template_name' ) ) {
		function mep_event_template_name() {
			$template_name = 'index.php';
			$template_path = get_stylesheet_directory() . '/mage-event/themes/';
			$default_path  = plugin_dir_path( __DIR__ ) . 'templates/themes/';
			$template      = locate_template( array( $template_path . $template_name ) );
			if ( ! $template ) :
				$template = $default_path . $template_name;
			endif;
			if ( is_dir( $template_path ) ) {
				$thedir = glob( $template_path . "*" );
			} else {
				$thedir = glob( $default_path . "*" );
			}
			$theme = array();
			foreach ( $thedir as $filename ) {
				if ( is_file( $filename ) ) {
					$file  = basename( $filename );
					$naame = str_replace( "?>", "", strip_tags( file_get_contents( $filename, false, null, 25, 15 ) ) );
				}
				$theme[ $file ] = $naame;
			}
			return $theme;
		}
	}
	if ( ! function_exists( 'mep_field_generator' ) ) {
		function mep_field_generator( $type, $option ) {
			$FormFieldsGenerator = new FormFieldsGenerator();
			if ( $type === 'text' ) {
				return $FormFieldsGenerator->field_text( $option );
			} elseif ( $type === 'text_multi' ) {
				return $FormFieldsGenerator->field_text_multi( $option );
			} elseif ( $type === 'textarea' ) {
				return $FormFieldsGenerator->field_textarea( $option );
			} elseif ( $type === 'checkbox' ) {
				return $FormFieldsGenerator->field_checkbox( $option );
			} elseif ( $type === 'checkbox_multi' ) {
				return $FormFieldsGenerator->field_checkbox_multi( $option );
			} elseif ( $type === 'radio' ) {
				return $FormFieldsGenerator->field_radio( $option );
			} elseif ( $type === 'select' ) {
				return $FormFieldsGenerator->field_select( $option );
			} elseif ( $type === 'range' ) {
				return $FormFieldsGenerator->field_range( $option );
			} elseif ( $type === 'range_input' ) {
				return $FormFieldsGenerator->field_range_input( $option );
			} elseif ( $type === 'switch' ) {
				return $FormFieldsGenerator->field_switch( $option );
			} elseif ( $type === 'switch_multi' ) {
				return $FormFieldsGenerator->field_switch_multi( $option );
			} elseif ( $type === 'switch_img' ) {
				return $FormFieldsGenerator->field_switch_img( $option );
			} elseif ( $type === 'time_format' ) {
				return $FormFieldsGenerator->field_time_format( $option );
			} elseif ( $type === 'date_format' ) {
				return $FormFieldsGenerator->field_date_format( $option );
			} elseif ( $type === 'datepicker' ) {
				return $FormFieldsGenerator->field_datepicker( $option );
			} elseif ( $type === 'color_sets' ) {
				return $FormFieldsGenerator->field_color_sets( $option );
			} elseif ( $type === 'colorpicker' ) {
				return $FormFieldsGenerator->field_colorpicker( $option );
			} elseif ( $type === 'colorpicker_multi' ) {
				return $FormFieldsGenerator->field_colorpicker_multi( $option );
			} elseif ( $type === 'link_color' ) {
				return $FormFieldsGenerator->field_link_color( $option );
			} elseif ( $type === 'icon' ) {
				return $FormFieldsGenerator->field_icon( $option );
			} elseif ( $type === 'icon_multi' ) {
				return $FormFieldsGenerator->field_icon_multi( $option );
			} elseif ( $type === 'dimensions' ) {
				return $FormFieldsGenerator->field_dimensions( $option );
			} elseif ( $type === 'wp_editor' ) {
				return $FormFieldsGenerator->field_wp_editor( $option );
			} elseif ( $type === 'select2' ) {
				return $FormFieldsGenerator->field_select2( $option );
			} elseif ( $type === 'faq' ) {
				return $FormFieldsGenerator->field_faq( $option );
			} elseif ( $type === 'grid' ) {
				return $FormFieldsGenerator->field_grid( $option );
			} elseif ( $type === 'color_palette' ) {
				return $FormFieldsGenerator->field_color_palette( $option );
			} elseif ( $type === 'color_palette_multi' ) {
				return $FormFieldsGenerator->field_color_palette_multi( $option );
			} elseif ( $type === 'media' ) {
				return $FormFieldsGenerator->field_media( $option );
			} elseif ( $type === 'media_multi' ) {
				return $FormFieldsGenerator->field_media_multi( $option );
			} elseif ( $type === 'repeatable' ) {
				return $FormFieldsGenerator->field_repeatable( $option );
			} elseif ( $type === 'user' ) {
				return $FormFieldsGenerator->field_user( $option );
			} elseif ( $type === 'margin' ) {
				return $FormFieldsGenerator->field_margin( $option );
			} elseif ( $type === 'padding' ) {
				return $FormFieldsGenerator->field_padding( $option );
			} elseif ( $type === 'border' ) {
				return $FormFieldsGenerator->field_border( $option );
			} elseif ( $type === 'switcher' ) {
				return $FormFieldsGenerator->field_switcher( $option );
			} elseif ( $type === 'password' ) {
				return $FormFieldsGenerator->field_password( $option );
			} elseif ( $type === 'post_objects' ) {
				return $FormFieldsGenerator->field_post_objects( $option );
			} elseif ( $type === 'google_map' ) {
				return $FormFieldsGenerator->field_google_map( $option );
			} elseif ( $type === 'image_link' ) {
				return $FormFieldsGenerator->field_image_link( $option );
			} else {
				return '';
			}
		}
	}
	if ( ! function_exists( 'mep_esc_html' ) ) {
		function mep_esc_html( $string ) {
			$allow_attr = array(
				'input'    => array(
					'br'                 => [],
					'type'               => [],
					'class'              => [],
					'id'                 => [],
					'name'               => [],
					'value'              => [],
					'size'               => [],
					'placeholder'        => [],
					'min'                => [],
					'max'                => [],
					'checked'            => [],
					'required'           => [],
					'disabled'           => [],
					'readonly'           => [],
					'step'               => [],
					'data-default-color' => [],
				),
				'p'        => [
					'class' => []
				],
				'img'      => [
					'class' => [],
					'id'    => [],
					'src'   => [],
					'alt'   => [],
				],
				'fieldset' => [
					'class' => []
				],
				'label'    => [
					'for'   => [],
					'class' => []
				],
				'select'   => [
					'class' => [],
					'name'  => [],
					'id'    => [],
				],
				'option'   => [
					'class'    => [],
					'value'    => [],
					'id'       => [],
					'selected' => [],
				],
				'textarea' => [
					'class' => [],
					'rows'  => [],
					'id'    => [],
					'cols'  => [],
					'name'  => [],
				],
				'h2'       => [ 'class' => [], 'id' => [], ],
				'a'        => [ 'class' => [], 'id' => [], 'href' => [], ],
				'div'      => [ 'class' => [], 'id' => [], 'data' => [], ],
				'span'     => [
					'class' => [],
					'id'    => [],
					'data'  => [],
				],
				'i'        => [
					'class' => [],
					'id'    => [],
					'data'  => [],
				],
				'table'    => [
					'class' => [],
					'id'    => [],
					'data'  => [],
				],
				'tr'       => [
					'class' => [],
					'id'    => [],
					'data'  => [],
				],
				'td'       => [
					'class' => [],
					'id'    => [],
					'data'  => [],
				],
				'thead'    => [
					'class' => [],
					'id'    => [],
					'data'  => [],
				],
				'tbody'    => [
					'class' => [],
					'id'    => [],
					'data'  => [],
				],
				'th'       => [
					'class' => [],
					'id'    => [],
					'data'  => [],
				],
				'svg'      => [
					'class'   => [],
					'id'      => [],
					'width'   => [],
					'height'  => [],
					'viewBox' => [],
					'xmlns'   => [],
				],
				'g'        => [
					'fill' => [],
				],
				'path'     => [
					'd' => [],
				],
				'br'       => array(),
				'em'       => array(),
				'strong'   => array(),
			);
			return wp_kses( $string, $allow_attr );
		}
	}
	if ( ! function_exists( 'mep_event_list_price' ) ) {
		function mep_event_list_price( $pid, $type = 'price' ) {
			$mep_event_ticket_type = get_post_meta( $pid, 'mep_event_ticket_type', true ) ? get_post_meta( $pid, 'mep_event_ticket_type', true ) : [];
			$price_arr             = [];
			if ( is_array( $mep_event_ticket_type ) && sizeof( $mep_event_ticket_type ) > 0 ) {
				foreach ( $mep_event_ticket_type as $ticket ) {
					$price_arr[] = array_key_exists( 'option_price_t', $ticket ) ? $ticket['option_price_t'] : null;
				}
			}
			return $type == 'price' && is_array( $price_arr ) && sizeof( $price_arr ) > 0 ? wc_price( mep_get_price_including_tax( $pid, min( $price_arr ) ) ) : count( $price_arr );
		}
	}
	if ( ! function_exists( 'mep_template_file_validate' ) ) {
		function mep_template_file_validate( $file_name ) {
			$template_path = get_stylesheet_directory() . '/mage-event/';
			$default_path  = plugin_dir_path( __DIR__ ) . 'templates/';
			// Check theme directory first
			$_themedir = $template_path . "themes/" . $file_name;
			if ( file_exists( $_themedir ) ) {
				return $file_name;
			}
			// Fallback to plugin directory
			$_plugindir = $default_path . "themes/" . $file_name;
			if ( file_exists( $_plugindir ) ) {
				return $file_name;
			}
			// Default fallback
			return 'default-theme.php';
		}
	}
	if ( ! function_exists( 'mep_event_list_number_price' ) ) {
		function mep_event_list_number_price( $pid, $type = 'price' ) {
			global $post;
			$cur                   = get_woocommerce_currency_symbol();
			$mep_event_ticket_type = get_post_meta( $pid, 'mep_event_ticket_type', true ) ? get_post_meta( $pid, 'mep_event_ticket_type', true ) : [];
			$n_price               = get_post_meta( $pid, '_price', true );
			$price_arr             = [];
			if ( is_array( $mep_event_ticket_type ) && sizeof( $mep_event_ticket_type ) > 0 ) {
				foreach ( $mep_event_ticket_type as $ticket ) {
					$price_arr[] = array_key_exists( 'option_price_t', $ticket ) ? $ticket['option_price_t'] : null;
				}
			}
			return $type == 'price' && is_array( $price_arr ) && sizeof( $price_arr ) > 0 ? min( $price_arr ) : count( $price_arr );
		}
	}
	if ( ! function_exists( 'mep_get_label' ) ) {
		function mep_get_label( $pid, $label_id, $default_text ) {
			return mep_get_option( $label_id, 'label_setting_sec', $default_text );
		}
	}
	add_filter( 'manage_edit-mep_events_sortable_columns', 'mep_set_column_soartable' );
	if ( ! function_exists( 'mep_set_column_soartable' ) ) {
		function mep_set_column_soartable( $columns ) {
			$columns['mep_event_date'] = 'event_start_datetime';
			//To make a column 'un-sortable' remove it from the array
			//unset($columns['mep_event_date']);
			return $columns;
		}
	}
	if ( ! function_exists( 'mep_remove_date_filter_dropdown' ) ) {
		function mep_remove_date_filter_dropdown( $months ) {
			global $typenow; // use this to restrict it to a particular post type
			if ( $typenow == 'mep_events' ) {
				return array(); // return an empty array
			}
			return $months; // otherwise return the original for other post types
		}
	}
	add_filter( 'months_dropdown_results', 'mep_remove_date_filter_dropdown' );
	add_action( 'pre_get_posts', 'mep_filter_event_list_by_date' );
	if ( ! function_exists( 'mep_filter_event_list_by_date' ) ) {
		function mep_filter_event_list_by_date( $query ) {
			if ( ! is_admin() ) {
				return;
			}
			$orderby = $query->get( 'orderby' );
			if ( 'event_start_datetime' == $orderby ) {
				$query->set( 'meta_key', 'event_start_datetime' );
				$query->set( 'orderby', 'meta_value' );
			}
		}
	}
	if ( ! function_exists( 'mep_get_only_time' ) ) {
		function mep_get_only_time( $datetime ) {
			$user_set_format = mep_get_option( 'mep_event_time_format', 'general_setting_sec', 'wtss' );
			$time_format     = get_option( 'time_format' );
			if ( $user_set_format == 12 ) {
				echo esc_html( date( 'h:i A', strtotime( $datetime ) ) );
			}
			if ( $user_set_format == 24 ) {
				echo esc_html( date( 'H:i', strtotime( $datetime ) ) );
			}
			if ( $user_set_format == 'wtss' ) {
				echo esc_html( date( $time_format, strtotime( $datetime ) ) );
			}
		}
	}
	if ( ! function_exists( 'mep_get_event_city' ) ) {
		function mep_get_event_city( $event_id ) {
			$location_sts = get_post_meta( $event_id, 'mep_org_address', true ) ? get_post_meta( $event_id, 'mep_org_address', true ) : '';
			// ob_start();
			if ( $location_sts ) {
				$org_arr  = get_the_terms( $event_id, 'mep_org' );
				$org_id   = $org_arr[0]->term_id;
				$location = get_term_meta( $org_id, 'org_location', true ) ? esc_html( get_term_meta( $org_id, 'org_location', true ) ) : '';
				$street   = get_term_meta( $org_id, 'org_street', true ) ? esc_html( get_term_meta( $org_id, 'org_street', true ) ) : '';
				$city     = get_term_meta( $org_id, 'org_city', true ) ? esc_html( get_term_meta( $org_id, 'org_city', true ) ) : '';
				$state    = get_term_meta( $org_id, 'org_state', true ) ? esc_html( get_term_meta( $org_id, 'org_state', true ) ) : '';
				$zip      = get_term_meta( $org_id, 'org_postcode', true ) ? esc_html( get_term_meta( $org_id, 'org_postcode', true ) ) : '';
				$country  = get_term_meta( $org_id, 'org_country', true ) ? esc_html( get_term_meta( $org_id, 'org_country', true ) ) : '';
			} else {
				$location = get_post_meta( $event_id, 'mep_location_venue', true ) ? esc_html( get_post_meta( $event_id, 'mep_location_venue', true ) ) : '';
				$street   = get_post_meta( $event_id, 'mep_street', true ) ? esc_html( get_post_meta( $event_id, 'mep_street', true ) ) : '';
				$city     = get_post_meta( $event_id, 'mep_city', true ) ? esc_html( get_post_meta( $event_id, 'mep_city', true ) ) : '';
				$state    = get_post_meta( $event_id, 'mep_state', true ) ? esc_html( get_post_meta( $event_id, 'mep_state', true ) ) : '';
				$zip      = get_post_meta( $event_id, 'mep_postcode', true ) ? esc_html( get_post_meta( $event_id, 'mep_postcode', true ) ) : '';
				$country  = get_post_meta( $event_id, 'mep_country', true ) ? esc_html( get_post_meta( $event_id, 'mep_country', true ) ) : '';
			}
			$location_arr = [ $location, $city ];
			$content      = implode( ', ', array_filter( $location_arr ) );
			$address_arr  = array(
				'location' => $location,
				'street'   => $street,
				'state'    => $state,
				'zip'      => $zip,
				'city'     => $city,
				'country'  => $country
			);
			echo esc_html( apply_filters( 'mage_event_location_in_list_view', $content, $event_id, $address_arr ) );
		}
	}
	if ( ! function_exists( 'mep_get_total_available_seat' ) ) {
		function mep_get_total_available_seat( $event_id, $event_meta ) {
			$availabele_check = mep_get_option( 'mep_speed_up_list_page', 'general_setting_sec', 'no' );
			if ( $availabele_check == 'no' ) {
				$total_seat_left = get_post_meta( $event_id, 'mep_total_seat_left', true ) ? get_post_meta( $event_id, 'mep_total_seat_left', true ) : mep_count_total_available_seat( $event_id );
			} else {
				$total_seat_left = get_post_meta( $event_id, 'mep_total_seat_left', true ) ? get_post_meta( $event_id, 'mep_total_seat_left', true ) : 1;
			}
			return esc_html( $total_seat_left );
		}
	}
	if ( ! function_exists( 'mep_count_total_available_seat' ) ) {
		function mep_count_total_available_seat( $event_id ) {
			$total_seat = mep_event_total_seat( $event_id, 'total' );
			$total_resv = mep_event_total_seat( $event_id, 'resv' );
			$total_sold = mep_ticket_sold( $event_id );
			$total_left = $total_seat - ( $total_sold + $total_resv );
			return esc_html( $total_left );
		}
	}
	if ( ! function_exists( 'mep_get_event_total_available_seat' ) ) {
		function mep_get_event_total_available_seat( $event_id, $date ) {
			$total_seat = mep_event_total_seat( $event_id, 'total' );
			$total_resv = mep_event_total_seat( $event_id, 'resv' );
			$total_sold = mep_ticket_type_sold( $event_id, '', $date );
			$total_left = $total_seat - ( $total_sold + $total_resv );
			return esc_html( $total_left );
		}
	}
	if ( ! function_exists( 'mep_event_org_location_item' ) ) {
		function mep_event_org_location_item( $event_id, $item_name ) {
			$org_arr = get_the_terms( $event_id, 'mep_org' );
			if ( $org_arr ) {
				$org_id = $org_arr[0]->term_id;
				return get_term_meta( $org_id, $item_name, true );
			}
		}
	}
	if ( ! function_exists( 'mep_get_event_locaion_item' ) ) {
		function mep_get_event_locaion_item( $event_id, $item_name ) {
			if ( $event_id ) {
				$location_sts = get_post_meta( $event_id, 'mep_org_address', true );
				if ( $item_name == 'mep_location_venue' ) {
					if ( $location_sts ) {
						$org_arr = get_the_terms( $event_id, 'mep_org' );
						if ( is_array( $org_arr ) && sizeof( $org_arr ) > 0 ) {
							$org_id      = $org_arr[0]->term_id;
							$venue_value = get_term_meta( $org_id, 'org_location', true );
							// Check if it looks like coordinates (lat,lng format)
							if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $venue_value ) ) {
								// For coordinates, use sanitize_text_field to preserve the comma
								return sanitize_text_field( $venue_value );
							} else {
								// For regular location names, use esc_html
								return esc_html( $venue_value );
							}
						}
					} else {
						$venue_value = get_post_meta( $event_id, 'mep_location_venue', true );
						// Check if it looks like coordinates (lat,lng format)
						if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $venue_value ) ) {
							// For coordinates, use sanitize_text_field to preserve the comma
							return sanitize_text_field( $venue_value );
						} else {
							// For regular location names, use esc_html
							return esc_html( $venue_value );
						}
					}
					return null;
				}
				if ( $item_name == 'mep_street' ) {
					if ( $location_sts ) {
						$org_arr = get_the_terms( $event_id, 'mep_org' );
						if ( is_array( $org_arr ) && sizeof( $org_arr ) > 0 ) {
							$org_id = $org_arr[0]->term_id;
							return esc_html( get_term_meta( $org_id, 'org_street', true ) );
						}
					} else {
						return esc_html( get_post_meta( $event_id, 'mep_street', true ) );
					}
				}
				if ( $item_name == 'mep_city' ) {
					if ( $location_sts ) {
						$org_arr = get_the_terms( $event_id, 'mep_org' );
						if ( is_array( $org_arr ) && sizeof( $org_arr ) > 0 ) {
							$org_id = $org_arr[0]->term_id;
							return esc_html( get_term_meta( $org_id, 'org_city', true ) );
						}
					} else {
						return esc_html( get_post_meta( $event_id, 'mep_city', true ) );
					}
				}
				if ( $item_name == 'mep_state' ) {
					if ( $location_sts ) {
						$org_arr = get_the_terms( $event_id, 'mep_org' );
						if ( is_array( $org_arr ) && sizeof( $org_arr ) > 0 ) {
							$org_id = $org_arr[0]->term_id;
							return esc_html( get_term_meta( $org_id, 'org_state', true ) );
						}
					} else {
						return esc_html( get_post_meta( $event_id, 'mep_state', true ) );
					}
				}
				if ( $item_name == 'mep_postcode' ) {
					if ( $location_sts ) {
						$org_arr = get_the_terms( $event_id, 'mep_org' );
						if ( is_array( $org_arr ) && sizeof( $org_arr ) > 0 ) {
							$org_id = $org_arr[0]->term_id;
							return esc_html( get_term_meta( $org_id, 'org_postcode', true ) );
						}
					} else {
						return esc_html( get_post_meta( $event_id, 'mep_postcode', true ) );
					}
				}
				if ( $item_name == 'mep_country' ) {
					if ( $location_sts ) {
						$org_arr = get_the_terms( $event_id, 'mep_org' );
						if ( is_array( $org_arr ) && sizeof( $org_arr ) > 0 ) {
							$org_id = $org_arr[0]->term_id;
							return esc_html( get_term_meta( $org_id, 'org_country', true ) );
						}
					} else {
						return esc_html( get_post_meta( $event_id, 'mep_country', true ) );
					}
				}
			}
		}
	}
	if ( ! function_exists( 'mep_ticket_type_sold' ) ) {
		function mep_ticket_type_sold( $event_id, $type = '', $date = '' ) {
			$type             = ! empty( $type ) ? $type : '';
			$_user_set_status = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
			$_order_status    = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
			$order_status     = array_values( $_order_status );
			if ( count( $order_status ) > 1 ) { // check if more then one tag
				$order_status_filter['relation'] = 'OR';
				foreach ( $order_status as $tag ) { // create a LIKE-comparison for every single tag
					$order_status_filter[] = array( 'key' => 'ea_order_status', 'value' => $tag, 'compare' => '=' );
				}
			} else { // if only one tag then proceed with simple query
				$order_status_filter[] = array( 'key' => 'ea_order_status', 'value' => $order_status[0], 'compare' => '=' );
			}
			$type_filter = ! empty( $type ) ? array(
				'key'     => 'ea_ticket_type',
				'value'   => $type,
				'compare' => '='
			) : '';
			$date_filter = ! empty( $date ) ? array(
				'key'     => 'ea_event_date',
				'value'   => $date,
				'compare' => 'LIKE'
			) : '';
			$args        = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'relation' => 'AND',
						array(
							'key'     => 'ea_event_id',
							'value'   => $event_id,
							'compare' => '='
						),
						$type_filter,
						apply_filters( 'mep_sold_meta_query_and_attribute', $date_filter )
					),
					$order_status_filter
				)
			);
			$loop        = new WP_Query( $args );
			return $loop->post_count;
		}
	}
	if ( ! function_exists( 'mep_extra_service_sold' ) ) {
		function mep_extra_service_sold( $event_id, $type, $date ) {
			$type  = ! empty( $type ) ? html_entity_decode( $type ) : '';
			$args  = array(
				'post_type'      => 'mep_extra_service',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'relation' => 'AND',
						array(
							'key'     => 'ea_extra_service_event',
							'value'   => $event_id,
							'compare' => '='
						),
						array(
							'key'     => 'ea_extra_service_name',
							'value'   => $type,
							'compare' => '='
						),
						array(
							'key'     => 'ea_extra_service_event_date',
							'value'   => $date,
							'compare' => 'LIKE'
						)
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => 'ea_extra_service_order_status',
							'value'   => 'processing',
							'compare' => '='
						),
						array(
							'key'     => 'ea_extra_service_order_status',
							'value'   => 'completed',
							'compare' => '='
						)
					)
				)
			);
			$loop  = new WP_Query( $args );
			$count = 0;
			foreach ( $loop->posts as $sold_service ) {
				$pid   = $sold_service->ID;
				$count = $count + get_post_meta( $pid, 'ea_extra_service_qty', true );
			}
			return $count;
		}
	}
	if ( ! function_exists( 'mep_ticket_sold' ) ) {
		function mep_ticket_sold( $event_id ) {
			$event_start_date = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			// $get_ticket_type_list = get_post_meta($event_id,'mep_event_ticket_type',true) ? get_post_meta($event_id,'mep_event_ticket_type',true) : array();
			$get_ticket_type_list = metadata_exists( 'post', $event_id, 'mep_event_ticket_type' ) ? get_post_meta( $event_id, 'mep_event_ticket_type', true ) : array();
			$recurring            = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
			$sold                 = 0;
			if ( is_array( $get_ticket_type_list ) && sizeof( $get_ticket_type_list ) > 0 ) {
				foreach ( $get_ticket_type_list as $ticket_type ) {
					if ( array_key_exists( 'option_name_t', $ticket_type ) ) {
						$sold = $sold + mep_ticket_type_sold( $event_id, mep_remove_apostopie( $ticket_type['option_name_t'] ), $event_start_date );
					}
				}
			}
			if ( $recurring == 'yes' ) {
				//   $mep_event_more_date = get_post_meta($event_id,'mep_event_more_date',true);
				$mep_event_more_date = metadata_exists( 'post', $event_id, 'mep_event_more_date' ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : array();
				if ( is_array( $mep_event_more_date ) && sizeof( $mep_event_more_date ) > 0 ) {
					foreach ( $mep_event_more_date as $md ) {
						if ( is_array( $get_ticket_type_list ) && sizeof( $get_ticket_type_list ) > 0 ) {
							foreach ( $get_ticket_type_list as $ticket_type ) {
								if ( array_key_exists( 'option_name_t', $ticket_type ) ) {
									$sold = $sold + mep_ticket_type_sold( $event_id, mep_remove_apostopie( $ticket_type['option_name_t'] ), $md['event_more_start_date'] );
								}
							}
						}
					}
				}
			}
			return $sold;
		}
	}
	if ( ! function_exists( 'mep_event_total_seat' ) ) {
		function mep_event_total_seat( $event_id, $type ) {
			$mep_event_ticket_type = get_post_meta( $event_id, 'mep_event_ticket_type', true );
			// print_r($mep_event_ticket_type);
			$total = 0;
			if ( is_array( $mep_event_ticket_type ) && sizeof( $mep_event_ticket_type ) > 0 ) {
				foreach ( $mep_event_ticket_type as $field ) {
					if ( $type == 'total' ) {
						$total_name = array_key_exists( 'option_qty_t', $field ) ? (int) $field['option_qty_t'] : 0;
					} elseif ( $type == 'resv' ) {
						$total_name = array_key_exists( 'option_rsv_t', $field ) ? (int) $field['option_rsv_t'] : 0;
					}
					$total = $total_name + $total;
				}
			}
			return $total;
		}
	}
	if ( ! function_exists( 'get_mep_datetime' ) ) {
		function get_mep_datetime( $date, $type ) {
			// Return empty string if date is empty or invalid
			if ( empty( $date ) ) {
				return '';
			}
			
			$event_id             = get_the_id() ? get_the_id() : 0;
			$date_format          = mep_get_datetime_format( $event_id, 'date' );
			$time_format_timezone = mep_get_datetime_format( $event_id, 'time_timezone' );
			$wpdatesettings       = $date_format . '  ' . $time_format_timezone;
			$timestamp            = strtotime( $date );
			
			// If strtotime fails, return empty string instead of showing 1970
			if ( $timestamp === false || $timestamp < 0 ) {
				return '';
			}
			
			if ( $type == 'date' ) {
				return esc_html( wp_date( $date_format, $timestamp ) );
			}
			if ( $type == 'date-time' ) {
				return esc_html( wp_date( $wpdatesettings, $timestamp ) );
			}
			if ( $type == 'date-text' ) {
				return esc_html( wp_date( $date_format, $timestamp ) );
			}
			if ( $type == 'date-time-text' ) {
				return esc_html( wp_date( $wpdatesettings, $timestamp, wp_timezone() ) );
			}
			if ( $type == 'time' ) {
				return esc_html( wp_date( $time_format_timezone, $timestamp, wp_timezone() ) );
			}
			if ( $type == 'Hour' ) {
				return esc_html( wp_date( 'H', $timestamp, wp_timezone() ) );
			}
			if ( $type == 'hour' ) {
				return esc_html( wp_date( 'h', $timestamp, wp_timezone() ) );
			}
			if ( $type == 'minute' ) {
				return esc_html( wp_date( 'i', $timestamp, wp_timezone() ) );
			}
			if ( $type == 'second' ) {
				return esc_html( wp_date( 's', $timestamp, wp_timezone() ) );
			}
			if ( $type == 'day' ) {
				return esc_html( wp_date( 'd', $timestamp ) );
			}
			if ( $type == 'Dday' ) {
				return esc_html( wp_date( 'D', $timestamp ) );
			}
			if ( $type == 'month' ) {
				return esc_html( wp_date( 'm', $timestamp ) );
			}
			if ( $type == 'month-name' ) {
				return esc_html( wp_date( 'M', $timestamp ) );
			}
			if ( $type == 'year' ) {
				return esc_html( wp_date( 'y', $timestamp ) );
			}
			if ( $type == 'year-full' ) {
				return esc_html( wp_date( 'Y', $timestamp ) );
			}
			if ( $type == 'timezone' ) {
				return esc_html( wp_date( 'T', $timestamp ) );
			}
			return '';
		}
	}
	if ( ! function_exists( 'mep_get_location' ) ) {
		function mep_get_location( $event_id, $type ) {
			$location_sts = get_post_meta( $event_id, 'mep_org_address', true ) ? get_post_meta( $event_id, 'mep_org_address', true ) : '';
			if ( $location_sts ) {
				$org_arr  = get_the_terms( $event_id, 'mep_org' ) ? get_the_terms( $event_id, 'mep_org' ) : [];
				$org_id   = (is_array( $org_arr ) && sizeof( $org_arr ) > 0) ? $org_arr[0]->term_id : '';
				$location = ! empty( $org_id ) && get_term_meta( $org_id, 'org_location', true ) ? get_term_meta( $org_id, 'org_location', true ) : '';
				$street   = ! empty( $org_id ) && get_term_meta( $org_id, 'org_street', true ) ? get_term_meta( $org_id, 'org_street', true ) : '';
				$city     = ! empty( $org_id ) && get_term_meta( $org_id, 'org_city', true ) ? get_term_meta( $org_id, 'org_city', true ) : '';
				$state    = ! empty( $org_id ) && get_term_meta( $org_id, 'org_state', true ) ? get_term_meta( $org_id, 'org_state', true ) : '';
				$zip      = ! empty( $org_id ) && get_term_meta( $org_id, 'org_postcode', true ) ? get_term_meta( $org_id, 'org_postcode', true ) : '';
				$country  = ! empty( $org_id ) && get_term_meta( $org_id, 'org_country', true ) ? get_term_meta( $org_id, 'org_country', true ) : '';
			} else {
				$location = get_post_meta( $event_id, 'mep_location_venue', true ) ? get_post_meta( $event_id, 'mep_location_venue', true ) : '';
				$street   = get_post_meta( $event_id, 'mep_street', true ) ? get_post_meta( $event_id, 'mep_street', true ) : '';
				$city     = get_post_meta( $event_id, 'mep_city', true ) ? get_post_meta( $event_id, 'mep_city', true ) : '';
				$state    = get_post_meta( $event_id, 'mep_state', true ) ? get_post_meta( $event_id, 'mep_state', true ) : '';
				$zip      = get_post_meta( $event_id, 'mep_postcode', true ) ? get_post_meta( $event_id, 'mep_postcode', true ) : '';
				$country  = get_post_meta( $event_id, 'mep_country', true ) ? get_post_meta( $event_id, 'mep_country', true ) : '';
			}
			$location_arr = [ $location, $street, $city, $state, $zip, $country ];
			if ( $type == 'full' ) {
				echo esc_html( implode( ', ', array_filter( $location_arr ) ) );
			}
			if ( $type == 'location' ) {
				echo esc_html( $location );
			}
			if ( $type == 'street' ) {
				echo esc_html( $street );
			}
			if ( $type == 'state' ) {
				echo esc_html( $state );
			}
			if ( $type == 'city' ) {
				echo esc_html( $city );
			}
			if ( $type == 'zip' ) {
				echo esc_html( $zip );
			}
			if ( $type == 'country' ) {
				echo esc_html( $country );
			}
		}
	}
	add_action( 'admin_head', 'mep_hide_date_from_order_page' );
	if ( ! function_exists( 'mep_hide_date_from_order_page' ) ) {
		function mep_hide_date_from_order_page() {
			$product_id = [];
			$hide_wc    = mep_get_option( 'mep_show_hidden_wc_product', 'general_setting_sec', 'no' );
			$args       = array(
				'post_type'      => 'mep_events',
				'posts_per_page' => - 1
			);
			$qr         = new WP_Query( $args );
			foreach ( $qr->posts as $result ) {
				$post_id      = $result->ID;
				$product_id[] = get_post_meta( $post_id, 'link_wc_product', true ) ? '.woocommerce-admin-page .post-' . get_post_meta( $post_id, 'link_wc_product', true ) . '.type-product' : '';
			}
			$product_id = array_filter( $product_id );
			$parr       = implode( ', ', $product_id );
			if ( $hide_wc == 'no' ) {
				echo '<style> ' . esc_html( $parr ) . '{display:none!important}' . ' </style>';
			}
		}
	}
	if ( ! function_exists( 'mep_set_email_content_type' ) ) {
		function mep_set_email_content_type() {
			return "text/html";
		}
	}
	add_filter( 'wp_mail_content_type', 'mep_set_email_content_type' );
	// if ( ! function_exists( 'mage_array_strip' ) ) {
	// 	function mage_array_strip( $array_or_string ) {
	// 		if ( is_string( $array_or_string ) ) {
	// 			// Check if this looks like coordinates (lat,lng format)
	// 			if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', trim( $array_or_string ) ) ) {
	// 				// For coordinates, only use sanitize_text_field to preserve the comma
	// 				$array_or_string = sanitize_text_field( $array_or_string );
	// 			} else {
	// 				// For regular strings, use the original processing
	// 				$array_or_string = sanitize_text_field( htmlentities( nl2br( $array_or_string ) ) );
	// 			}
	// 		} elseif ( is_array( $array_or_string ) ) {
	// 			foreach ( $array_or_string as $key => &$value ) {
	// 				if ( is_array( $value ) ) {
	// 					$value = mage_array_strip( $value );
	// 				} else {
	// 					// Check if this looks like coordinates (lat,lng format)
	// 					if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', trim( $value ) ) ) {
	// 						// For coordinates, only use sanitize_text_field to preserve the comma
	// 						$value = sanitize_text_field( $value );
	// 					} else {
	// 						// For regular values, use the original processing
	// 						$value = sanitize_text_field( htmlentities( nl2br( $value ) ) );
	// 					}
	// 				}
	// 			}
	// 		}
	// 		return $array_or_string;
	// 	}
	// }

	if ( ! function_exists( 'mage_array_strip' ) ) {
		function mage_array_strip( $data ) {
			// Null safety for PHP 8+
			if ( is_null( $data ) ) {
				return '';
			}
			// If string
			if ( is_string( $data ) ) {
				$data = trim( $data );
				// Coordinate format check (lat,lng)
				if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $data ) ) {
					return sanitize_text_field( $data );
				}
				// Regular string
				return sanitize_text_field( $data );
			}
			// If array → recursive sanitize
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					$data[ $key ] = mage_array_strip( $value );
				}
				return $data;
			}
			// For other scalar types (int, float, bool)
			if ( is_scalar( $data ) ) {
				return $data;
			}
			return '';
		}
	}

	if ( ! function_exists( 'mep_letters_numbers_spaces_only' ) ) {
		function mep_letters_numbers_spaces_only( $string ) {
			// Check if this looks like coordinates (lat,lng format)
			if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', trim( $string ) ) ) {
				// For coordinates, preserve comma, decimal point, and minus sign
				return preg_replace( '/[^a-zA-Z0-9\s,.\-]/', '', $string );
			} else {
				// For regular strings, remove all special characters
				return preg_replace( '/[^a-zA-Z0-9\s]/', '', $string );
			}
		}
	}
	add_action( 'wp_ajax_load_event_end_date_normal', 'load_event_end_date_normal' );
	function load_event_end_date_normal() {



		// CSRF protection
		check_ajax_referer( 'mep_admin_nonce', 'nonce' );
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$end_date=strtotime( $end_date )<strtotime($start_date)?$start_date:$end_date;

		$date_format  = MPWEM_Global_Function::date_picker_format();
		$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		$hidden_end_date  = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
		$visible_end_date = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
		$start_year  = date( 'Y', strtotime( $start_date ) );
		$start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
		$start_day   = date( 'j', strtotime( $start_date ) );
		?>
        <label>
            <input type="hidden" name="event_end_date_normal" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
            <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl" id="event_end_date_normal" placeholder="<?php echo esc_attr( $now ); ?>"/>
            <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
        </label>
        <script>
            jQuery(document).ready(function () {
                jQuery("#event_end_date_normal").datepicker({
                    dateFormat: mpwem_date_format,
                    minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                    autoSize: true,
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function (dateString, data) {
                        let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                        jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                    }
                });
            });
        </script><?php
die();
	}

	add_action( 'wp_ajax_load_event_start_date', 'load_event_start_date' );
	function load_event_start_date() {



		// CSRF protection
		check_ajax_referer( 'mep_admin_nonce', 'nonce' );
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$end_date=strtotime( $end_date )<strtotime($start_date)?$start_date:$end_date;

		$date_format  = MPWEM_Global_Function::date_picker_format();
		$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		$hidden_end_date  = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
		$visible_end_date = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
		$start_year  = date( 'Y', strtotime( $start_date ) );
		$start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
		$start_day   = date( 'j', strtotime( $start_date ) );
		?>
        <label>
            <input required type="hidden" name="event_end_date" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
            <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl" id="event_end_date" placeholder="<?php echo esc_attr( $now ); ?>"/>
            <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
        </label>
        <script>
            jQuery(document).ready(function () {
                jQuery("#event_end_date").datepicker({
                    dateFormat: mpwem_date_format,
                    minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                    autoSize: true,
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function (dateString, data) {
                        let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                        jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                    }
                });
            });
        </script><?php
die();
	}
    add_action( 'wp_ajax_load_event_start_date_everyday', 'load_event_start_date_everyday' );
	function load_event_start_date_everyday() {



		// CSRF protection
		check_ajax_referer( 'mep_admin_nonce', 'nonce' );
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$end_date=strtotime( $end_date )<strtotime($start_date)?$start_date:$end_date;

		$date_format  = MPWEM_Global_Function::date_picker_format();
		$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		$hidden_end_date  = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
		$visible_end_date = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
		$start_year  = date( 'Y', strtotime( $start_date ) );
		$start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
		$start_day   = date( 'j', strtotime( $start_date ) );
		?>
        <label>
            <input type="hidden" name="event_end_date_everyday" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
            <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl" id="event_end_date_everyday" placeholder="<?php echo esc_attr( $now ); ?>"/>
            <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
        </label>
        <script>
            jQuery(document).ready(function () {
                jQuery("#event_end_date_everyday").datepicker({
                    dateFormat: mpwem_date_format,
                    minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                    autoSize: true,
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function (dateString, data) {
                        let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                        jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                    }
                });
            });
        </script><?php
die();
	}
    add_action( 'wp_ajax_load_event_more_start_date_normal', 'load_event_more_start_date_normal' );
	function load_event_more_start_date_normal() {



		// CSRF protection
		check_ajax_referer( 'mep_admin_nonce', 'nonce' );
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$end_date=strtotime( $end_date )<strtotime($start_date)?$start_date:$end_date;

		$date_format  = MPWEM_Global_Function::date_picker_format();
		$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		$hidden_end_date  = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
		$visible_end_date = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
		$start_year  = date( 'Y', strtotime( $start_date ) );
		$start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
		$start_day   = date( 'j', strtotime( $start_date ) );
		$id='event_more_end_date_normal_'.rand();
		?>
        <label>
            <input type="hidden" name="event_more_end_date_normal[]" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
            <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl" id="<?php echo esc_attr($id); ?>"  placeholder="<?php echo esc_attr( $now ); ?>"/>
            <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
        </label>
        <script>
            jQuery(document).ready(function () {
                jQuery("#<?php echo esc_attr($id); ?>").datepicker({
                    dateFormat: mpwem_date_format,
                    minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                    autoSize: true,
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function (dateString, data) {
                        let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                        jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                    }
                });
            });
        </script>
        <?php
die();
	}
    add_action( 'wp_ajax_load_event_more_start_date', 'load_event_more_start_date' );
	function load_event_more_start_date() {



		// CSRF protection
		check_ajax_referer( 'mep_admin_nonce', 'nonce' );
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$end_date=strtotime( $end_date )<strtotime($start_date)?$start_date:$end_date;

		$date_format  = MPWEM_Global_Function::date_picker_format();
		$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
		$hidden_end_date  = $end_date ? date( 'Y-m-d', strtotime( $end_date ) ) : '';
		$visible_end_date = $end_date ? date_i18n( $date_format, strtotime( $end_date ) ) : '';
		$start_year  = date( 'Y', strtotime( $start_date ) );
		$start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
		$start_day   = date( 'j', strtotime( $start_date ) );
		$id='event_more_end_date_'.rand();
		?>
        <label>
            <input type="hidden" name="event_more_end_date[]" value="<?php echo esc_attr( $hidden_end_date ); ?>"/>
            <input type="text" value="<?php echo esc_attr( $visible_end_date ); ?>" class="formControl" id="<?php echo esc_attr($id); ?>"  placeholder="<?php echo esc_attr( $now ); ?>"/>
            <span class="fas fa-times remove_icon mpwem_date_reset" title="<?php esc_attr_e( 'Remove Image', 'mage-eventpress' ); ?>"></span>
        </label>
        <script>
            jQuery(document).ready(function () {
                jQuery("#<?php echo esc_attr($id); ?>").datepicker({
                    dateFormat: mpwem_date_format,
                    minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                    autoSize: true,
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function (dateString, data) {
                        let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                        jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                    }
                });
            });
        </script>
        <?php
die();
	}
	/**
	 * The Giant SEO Plugin Yoast PRO doing some weird thing and that is its auto create a 301 redirect url when delete a post its causing our event some issue Thats why i disable those part for our event post type with the below filter hoook which is provide by Yoast.
	 */
	add_filter( 'wpseo_premium_post_redirect_slug_change', '__return_true' );
	add_filter( 'wpseo_premium_term_redirect_slug_change', '__return_true' );
	add_filter( 'wpseo_enable_notification_term_slug_change', '__return_false' );
	function mep_string_sanitize( $s ) {
		$str = str_replace( array( '\'', '"' ), '', $s );
		return $str;
	}
	/**
	 * We added event id with every order for using in the attendee & seat inventory calculation, but this info was showing in the thank you page, so i decided to hide this, and here is the fucntion which will hide the event id from the thank you page.
	 */
	add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'mep_hide_event_order_meta_in_emails' );
	if ( ! function_exists( 'mep_hide_event_order_meta_in_emails' ) ) {
		function mep_hide_event_order_meta_in_emails( $meta ) {
			if ( ! is_admin() ) {
				$criteria = array( 'key' => 'event_id' );
				$meta     = wp_list_filter( $meta, $criteria, 'NOT' );
			}
			return $meta;
		}
	}
	add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'mep_hide_event_order_data_from_thankyou_and_email', 10, 1 );
	if ( ! function_exists( 'mep_hide_event_order_data_from_thankyou_and_email' ) ) {
		function mep_hide_event_order_data_from_thankyou_and_email( $formatted_meta ) {
			$hide_location_status = mep_get_option( 'mep_hide_location_from_order_page', 'general_setting_sec', 'no' );
			$hide_date_status     = mep_get_option( 'mep_hide_date_from_order_page', 'general_setting_sec', 'no' );
			$location_text        = mep_get_option( 'mep_location_text', 'label_setting_sec', esc_html__( 'Location', 'mage-eventpress' ) );
			$date_text            = mep_get_option( 'mep_event_date_text', 'label_setting_sec', esc_html__( 'Date', 'mage-eventpress' ) );
			$hide_location        = $hide_location_status == 'yes' ? array( $location_text ) : array();
			$hide_date            = $hide_date_status == 'yes' ? array( $date_text ) : array();
			$default              = array( 'event_id' );
			$default              = array_merge( $default, $hide_date );
			$hide_them            = array_merge( $default, $hide_location );
			$temp_metas           = [];
			foreach ( $formatted_meta as $key => $meta ) {
				if ( isset( $meta->key ) && ! in_array( $meta->key, $hide_them ) ) {
					$temp_metas[ $key ] = $meta;
				}
			}
			return $temp_metas;
		}
	}
	if ( ! function_exists( 'mep_get_ticket_type_price_by_name' ) ) {
		function mep_get_ticket_type_price_by_name( $name, $event_id ) {
			$ticket_type_arr = get_post_meta( $event_id, 'mep_event_ticket_type', true ) ? get_post_meta( $event_id, 'mep_event_ticket_type', true ) : [];
			$p               = '';
			// Decode and normalize the input name to handle special characters
			$normalized_name = trim( html_entity_decode( urldecode( $name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
			$normalized_name = str_replace( "'", "", $normalized_name );
			foreach ( $ticket_type_arr as $price ) {
				$TicketName = str_replace( "'", "", $price['option_name_t'] );
				// Use normalized comparison to handle encoding differences
				$TicketName_normalized = trim( html_entity_decode( urldecode( $TicketName ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
				if ( $TicketName_normalized === $normalized_name || $TicketName === $normalized_name ) {
					$p = array_key_exists( 'option_price_t', $price ) ? esc_html( $price['option_price_t'] ) : 0;
					break; // Found match, exit loop
				}
			}
			return $p;
		}
	}
	if ( ! function_exists( 'mep_get_ticket_type_price_arr' ) ) {
		function mep_get_ticket_type_price_arr( $ticket_type, $event_id ) {
			$price = [];
			foreach ( $ticket_type as $ticket ) {
				$price[] = mep_get_ticket_type_price_by_name( stripslashes( $ticket ), $event_id );
			}
			return $price;
		}
	}
	if ( ! function_exists( 'mep_get_user_custom_field_ids' ) ) {
		function mep_get_user_custom_field_ids( $event_id ) {
			$reg_form_id           = mep_fb_get_reg_form_id( $event_id );
			$mep_form_builder_data = get_post_meta( $reg_form_id, 'mep_form_builder_data', true ) ? get_post_meta( $reg_form_id, 'mep_form_builder_data', true ) : [];
			$form_id               = [];
			// print_r($mep_form_builder_data); mep_fbc_label
			if ( is_array( $mep_form_builder_data ) && sizeof( $mep_form_builder_data ) > 0 ) {
				foreach ( $mep_form_builder_data as $_field ) {
					$form_id[ $_field['mep_fbc_label'] ] = $_field['mep_fbc_id'];
				}
			}
			return $form_id;
		}
	}
	if ( ! function_exists( 'mep_get_reg_label' ) ) {
		function mep_get_reg_label( $_event_id, $name = '' ) {
			$custom_forms_id = mep_get_user_custom_field_ids( $_event_id );
			$event_id        = mep_fb_get_reg_form_id( $_event_id );
			if ( $name == 'Name' ) {
				return get_post_meta( $event_id, 'mep_name_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_name_label', true ) ) : esc_html__( 'Name', 'mage-eventpress' );
			} elseif ( $name == 'Email' ) {
				return get_post_meta( $event_id, 'mep_email_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_email_label', true ) ) : esc_html__( 'Email', 'mage-eventpress' );
			} elseif ( $name == 'Phone' ) {
				return get_post_meta( $event_id, 'mep_phone_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_phone_label', true ) ) : esc_html__( 'Phone', 'mage-eventpress' );
			} elseif ( $name == 'Address' ) {
				return get_post_meta( $event_id, 'mep_address_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_address_label', true ) ) : esc_html__( 'Address', 'mage-eventpress' );
			} elseif ( $name == 'T-Shirt Size' ) {
				return get_post_meta( $event_id, 'mep_tshirt_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_tshirt_label', true ) ) : esc_html__( 'T-Shirt Size', 'mage-eventpress' );
			} elseif ( $name == 'Gender' ) {
				return get_post_meta( $event_id, 'mep_gender_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_gender_label', true ) ) : esc_html__( 'Gender', 'mage-eventpress' );
			} elseif ( $name == 'Company' ) {
				return get_post_meta( $event_id, 'mep_company_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_company_label', true ) ) : esc_html__( 'Company', 'mage-eventpress' );
			} elseif ( $name == 'Designation' ) {
				return get_post_meta( $event_id, 'mep_desg_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_desg_label', true ) ) : esc_html__( 'Designation', 'mage-eventpress' );
			} elseif ( $name == 'Website' ) {
				return get_post_meta( $event_id, 'mep_website_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_website_label', true ) ) : esc_html__( 'Website', 'mage-eventpress' );
			} elseif ( $name == 'Vegetarian' ) {
				return get_post_meta( $event_id, 'mep_veg_label', true ) ? esc_attr( get_post_meta( $event_id, 'mep_veg_label', true ) ) : esc_html__( 'Vegetarian', 'mage-eventpress' );
			} else {
				return null;
			}
		}
	}
	if ( ! function_exists( 'mep_cart_order_data_save_ticket_type' ) ) {
		function mep_cart_order_data_save_ticket_type( $item, $ticket_type_arr, $eid ) {
			foreach ( $ticket_type_arr as $ticket ) {
				$ticket_type_name = $ticket['ticket_name'] . "   " . wc_price( mep_get_price_including_tax( $eid, (float) $ticket['ticket_price'] ) ) . ' x ' . $ticket['ticket_qty'] . '  =  ';
				$ticket_type_val  = wc_price( mep_get_price_including_tax( $eid, (float) $ticket['ticket_price'] * (float) $ticket['ticket_qty'] ) );
				$ticket_name_meta = apply_filters( 'mep_event_order_meta_ticket_name_filter', $ticket_type_name, $ticket, $eid );
				$item->add_meta_data( $ticket_name_meta, $ticket_type_val );
				do_action( 'mep_event_cart_order_data_add_ef', $item, $eid, $ticket['ticket_name'] );
			}
		}
	}
	if ( ! function_exists( 'mep_remove_apostopie' ) ) {
		function mep_remove_apostopie( $string ) {
			$str = str_replace( "'", '', $string );
			return $str;
		}
	}
	if ( ! function_exists( 'mep_product_exists' ) ) {
		function mep_product_exists( $id ) {
			return is_string( get_post_status( $id ) );
		}
	}
	if ( ! function_exists( 'mep_get_event_dates_arr' ) ) {
		function mep_get_event_dates_arr( $event_id ) {
			$now                   = current_time( 'Y-m-d H:i:s' );
			$event_start_datetime  = get_post_meta( $event_id, 'event_start_datetime', true );
			$event_expire_datetime = get_post_meta( $event_id, 'event_end_datetime', true );
			$event_more_dates      = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : [];
			$date_arr              = array(
				array(
					'start' => $event_start_datetime,
					'end'   => $event_expire_datetime
				)
			);
			$m_date_arr            = [];
			if ( is_array( $event_more_dates ) && sizeof( $event_more_dates ) > 0 ) {
				$i = 0;
				foreach ( $event_more_dates as $mdate ) {
					// if(strtotime($now) < strtotime($mdate['event_more_start_date'].' '.$mdate['event_more_start_time'])){
					$mstart                    = $mdate['event_more_start_date'] . ' ' . $mdate['event_more_start_time'];
					$mend                      = $mdate['event_more_end_date'] . ' ' . $mdate['event_more_end_time'];
					$m_date_arr[ $i ]['start'] = $mstart;
					$m_date_arr[ $i ]['end']   = $mend;
					// }
					$i ++;
				}
			}
			// Add special dates to the event dates array
			$special_dates = get_post_meta( $event_id, 'mep_special_date_info', true ) ? get_post_meta( $event_id, 'mep_special_date_info', true ) : [];
			$s_date_arr    = [];
			if ( is_array( $special_dates ) && sizeof( $special_dates ) > 0 ) {
				$j = 0;
				foreach ( $special_dates as $sdate ) {
					if ( isset( $sdate['start_date'] ) && ! empty( $sdate['start_date'] ) ) {
						$start_date = $sdate['start_date'];
						$end_date   = isset( $sdate['end_date'] ) ? $sdate['end_date'] : $start_date;
						// Get time slots for this special date
						$time_slots = isset( $sdate['time'] ) && is_array( $sdate['time'] ) ? $sdate['time'] : [];
						if ( ! empty( $time_slots ) ) {
							// If there are time slots, create entry for each time slot
							foreach ( $time_slots as $slot ) {
								if ( isset( $slot['mep_ticket_time'] ) && ! empty( $slot['mep_ticket_time'] ) ) {
									$start_time = $slot['mep_ticket_time'];
									// Add 1 hour as approximate duration if no end time specified
									$end_time                  = date( 'H:i', strtotime( $start_time . ' +1 hour' ) );
									$sstart                    = $start_date . ' ' . $start_time;
									$send                      = $end_date . ' ' . $end_time;
									$s_date_arr[ $j ]['start'] = $sstart;
									$s_date_arr[ $j ]['end']   = $send;
									$j ++;
								}
							}
						} else {
							// If no time slots, add the date with default times
							$sstart                    = $start_date . ' 00:00:00';
							$send                      = $end_date . ' 23:59:59';
							$s_date_arr[ $j ]['start'] = $sstart;
							$s_date_arr[ $j ]['end']   = $send;
							$j ++;
						}
					}
				}
			}
			$event_dates = array_merge( $date_arr, $m_date_arr, $s_date_arr );
			return apply_filters( 'mep_event_dates_in_calender_free', $event_dates, $event_id );
		}
	}
	add_action( 'rest_api_init', 'mep_event_cunstom_fields_to_rest_init' );
	if ( ! function_exists( 'mep_event_cunstom_fields_to_rest_init' ) ) {
		function mep_event_cunstom_fields_to_rest_init() {
			register_rest_field( 'mep_events', 'event_informations', array(
				'get_callback' => 'mep_get_events_custom_meta_for_api',
				'schema'       => null,
			) );
		}
	}
	if ( ! function_exists( 'mep_get_events_custom_meta_for_api' ) ) {
		function mep_get_events_custom_meta_for_api( $object ) {
			$post_id                          = $object['id'];
			$post_meta                        = get_post_meta( $post_id );
			$post_image                       = get_post_thumbnail_id( $post_id );
			$image_src                        = wp_get_attachment_image_src( $post_image, 'full' );
			$post_meta["event_feature_image"] = is_array( $image_src ) ? $image_src[0] : '';
			return $post_meta;
		}
	}
	if ( ! function_exists( 'mep_elementor_get_tax_term' ) ) {
		function mep_elementor_get_tax_term( $tax ) {
			$terms = get_terms( array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
			) );
			$list  = array( '0' => __( 'Show All', 'mage-eventpress' ) );
			foreach ( $terms as $_term ) {
				$list[ $_term->term_id ] = $_term->name;
			}
			return $list;
		}
	}
	if ( ! function_exists( 'mep_get_price_excluding_tax' ) ) {
		function mep_get_price_excluding_tax( $event, $price, $args = array() ) {
			$args     = wp_parse_args(
				$args,
				array(
					'qty'   => '',
					'price' => '',
				)
			);
			$_product = get_post_meta( $event, 'link_wc_product', true ) ? get_post_meta( $event, 'link_wc_product', true ) : $event;
			$qty      = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;
			$product  = wc_get_product( $_product );
			if ( '' === $price ) {
				return '';
			} elseif ( empty( $qty ) ) {
				return 0.0;
			}
			$line_price = (float) $price * (float) $qty;
			if ( $product && is_object( $product ) && method_exists( $product, 'is_taxable' ) ) {
				if ( $product->is_taxable() && wc_prices_include_tax() ) {
					$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
					$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
					$remove_taxes   = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
					$return_price   = $line_price - array_sum( $remove_taxes ); // Unrounded since we're dealing with tax inclusive prices. Matches logic in cart-totals class. @see adjust_non_base_location_price.
				} else {
					$return_price = $line_price;
				}
			}
			return apply_filters( 'woocommerce_get_price_excluding_tax', $return_price, $qty, $product );
		}
	}
	function mep_filter_post_name( $data, $postarr, $unsanitized_postarr ) {
		$post_id   = $postarr['ID'];
		$post_type = get_post_type( $post_id );
		if ( $post_type === 'mep_events' ) {
			$data['post_title'] = wp_kses_post( $data['post_title'] );
		}
		return $data;
	}
	add_filter( 'wp_insert_post_data', 'mep_filter_post_name', 10, 3 );
	if ( ! function_exists( 'mep_get_price_including_tax' ) ) {
		function mep_get_price_including_tax( $event, $price, $args = array() ) {
			$args     = wp_parse_args(
				$args,
				array(
					'qty'   => '',
					'price' => '',
				)
			);
			$_product = get_post_meta( $event, 'link_wc_product', true ) ? get_post_meta( $event, 'link_wc_product', true ) : $event;
			// $price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
			$qty            = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;
			$product        = wc_get_product( $_product );
			$tax_with_price = get_option( 'woocommerce_tax_display_shop' );
			if ( '' === $price ) {
				return '';
			} elseif ( empty( $qty ) ) {
				return 0.0;
			}
			$line_price   = (float) $price * (float) $qty;
			$return_price = $line_price;
			if ( $product && is_object( $product ) && method_exists( $product, 'is_taxable' ) ) {
				if ( $product->is_taxable() ) {
					if ( ! wc_prices_include_tax() ) {
						$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
						$taxes     = WC_Tax::calc_tax( $line_price, $tax_rates, false );
						// print_r($tax_rates);
						if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
							$taxes_total = array_sum( $taxes );
						} else {
							$taxes_total = array_sum( array_map( 'wc_round_tax_total', $taxes ) );
						}
						$return_price = $tax_with_price == 'excl' ? round( $line_price, wc_get_price_decimals() ) : round( $line_price + $taxes_total, wc_get_price_decimals() );
					} else {
						$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
						$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
						/**
						 * If the customer is excempt from VAT, remove the taxes here.
						 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
						 */
						if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) { // @codingStandardsIgnoreLine.
							$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
							if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
								$remove_taxes_total = array_sum( $remove_taxes );
							} else {
								$remove_taxes_total = array_sum( array_map( 'wc_round_tax_total', $remove_taxes ) );
							}
							// $return_price = round( $line_price, wc_get_price_decimals() );
							$return_price = round( $line_price - $remove_taxes_total, wc_get_price_decimals() );
							/**
							 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
							 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
							 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
							 */
						} else {
							$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
							$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );
							if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
								$base_taxes_total   = array_sum( $base_taxes );
								$modded_taxes_total = array_sum( $modded_taxes );
							} else {
								$base_taxes_total   = array_sum( array_map( 'wc_round_tax_total', $base_taxes ) );
								$modded_taxes_total = array_sum( array_map( 'wc_round_tax_total', $modded_taxes ) );
							}
							$return_price = $tax_with_price == 'excl' ? round( $line_price - $base_taxes_total, wc_get_price_decimals() ) : round( $line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals() );
						}
					}
				}
			}
			// return 0;
			return apply_filters( 'woocommerce_get_price_including_tax', $return_price, $qty, $product );
		}
	}
	if ( ! function_exists( 'mep_get_list_thumbnail' ) ) {
		function mep_get_list_thumbnail( $event_id ) {
			$thumbnail_id = get_post_meta( $event_id, 'mep_list_thumbnail', true ) ? get_post_meta( $event_id, 'mep_list_thumbnail', true ) : 0;
			if ( $thumbnail_id > 0 ) {
				$thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'full' );
				?>
                <img src="<?php echo esc_url( $thumbnail[0] ); ?>" class="attachment-full size-full wp-post-image" alt="<?php echo get_the_title( $event_id ); ?>"/>
				<?php
			} else {
				echo get_the_post_thumbnail( $event_id, 'full' );
			}
		}
	}
	add_filter( 'mep_event_confirmation_text', 'mep_virtual_join_info_event_email_text', 10, 3 );
	if ( ! function_exists( 'mep_virtual_join_info_event_email_text' ) ) {
		function mep_virtual_join_info_event_email_text( $content, $event_id, $order_id ) {
			$event_type    = get_post_meta( $event_id, 'mep_event_type', true ) ? get_post_meta( $event_id, 'mep_event_type', true ) : 'offline';
			$email_content = get_post_meta( $event_id, 'mp_event_virtual_type_des', true ) ? get_post_meta( $event_id, 'mp_event_virtual_type_des', true ) : '';
			if ( $event_type == 'online' ) {
				$content = $content . '<br/>' . html_entity_decode( $email_content );
			}
			return html_entity_decode( $content );
		}
	}
	if ( ! function_exists( 'mep_fb_get_reg_form_id' ) ) {
		function mep_fb_get_reg_form_id( $event_id ) {
			$global_reg_form   = get_post_meta( $event_id, 'mep_event_reg_form_id', true ) ? get_post_meta( $event_id, 'mep_event_reg_form_id', true ) : 'custom_form';
			$event_reg_form_id = $global_reg_form == 'custom_form' ? $event_id : $global_reg_form;
			return $event_reg_form_id;
		}
	}
	add_action( 'init', 'mep_show_product_cat_in_event' );
	if ( ! function_exists( 'mep_show_product_cat_in_event' ) ) {
		function mep_show_product_cat_in_event() {
			$pro_cat_status = mep_get_option( 'mep_show_product_cat_in_event', 'single_event_setting_sec', 'no' );
			if ( $pro_cat_status == 'yes' ) {
				register_taxonomy_for_object_type( 'product_cat', 'mep_events' );
			} else {
				return null;
			}
		}
	}
	add_filter( 'wp_unique_post_slug_is_bad_hierarchical_slug', 'mep_event_prevent_slug_conflict', 10, 4 );
	add_filter( 'wp_unique_post_slug_is_bad_flat_slug', 'mep_event_prevent_slug_conflict', 10, 3 );
	if ( ! function_exists( 'mep_event_prevent_slug_conflict' ) ) {
		function mep_event_prevent_slug_conflict( $is_bad_slug, $slug, $post_type, $post_parent_id = 0 ) {
			$reserved_top_level_slugs = apply_filters( 'mep_event_prevent_slug_conflict_arr', array( 'events' ) );
			if ( 0 === $post_parent_id && in_array( $slug, $reserved_top_level_slugs ) ) {
				$is_bad_slug = true;
			}
			return $is_bad_slug;
		}
	}
	if ( ! function_exists( 'mep_default_sidebar_reg' ) ) {
		function mep_default_sidebar_reg() {
			$check_sidebar_status = mep_get_option( 'mep_show_event_sidebar', 'general_setting_sec', 'disable' );
			if ( $check_sidebar_status == 'enable' ) {
				register_sidebar( array(
					'name'          => __( 'Event Manager For Woocommerce Sidebar', 'mage-eventpress' ),
					'id'            => 'mep_default_sidebar',
					'description'   => __( 'This is the Default sidebar of the Event manager for Woocommerce  template.', 'mage-eventpress' ),
					'before_widget' => '<div id="%1$s" class="mep_sidebar mep_widget_sec widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h3 class="widgettitle">',
					'after_title'   => '</h3>',
				) );
			}
		}
	}
	add_action( 'widgets_init', 'mep_default_sidebar_reg' );
	add_filter( 'mep_ticket_current_time', 'mep_add_expire_min_in_current_date', 10, 3 );
	if ( ! function_exists( 'mep_add_expire_min_in_current_date' ) ) {
		function mep_add_expire_min_in_current_date( $current_date, $event_date, $event_id ) {
			$minutes_to_add = (int) mep_get_option( 'mep_ticket_expire_time', 'general_setting_sec', 0 );
			$time           = new DateTime( $current_date );
			$time->add( new DateInterval( 'PT' . $minutes_to_add . 'M' ) );
			$current_date = $time->format( 'Y-m-d H:i' );
			return $current_date;
		}
	}
	if ( ! function_exists( 'mep_enable_big_selects_for_queries' ) ) {
		function mep_enable_big_selects_for_queries() {
			global $wpdb;
			$wpdb->query( 'SET SQL_BIG_SELECTS=1' );
		}
	}
	add_action( 'init', 'mep_enable_big_selects_for_queries' );
	if ( ! function_exists( 'mep_get_event_upcoming_date' ) ) {
		function mep_get_event_upcoming_date( $event_id ) {
			$upcoming_date = get_post_meta( $event_id, 'event_start_datetime', true ) ? get_post_meta( $event_id, 'event_start_datetime', true ) : '';
			return apply_filters( 'mep_event_upcoming_date', $upcoming_date, $event_id );
		}
	}
	if ( ! function_exists( 'mep_license_error_code' ) ) {
		function mep_license_error_code( $license_data, $item_name = 'this Plugin' ) {
			switch ( $license_data->error ) {
				case 'expired':
					$message = sprintf(
					// translators: %1$s is the license expiration date.
						__( 'Your license key expired on %1$s.', 'mage-eventpress' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
					);
					break;
				case 'revoked':
					$message = __( 'Your license key has been disabled.', 'mage-eventpress' );
					break;
				case 'missing':
					$message = __( 'Invalid license.', 'mage-eventpress' );
					break;
				case 'invalid':
				case 'site_inactive':
					$message = __( 'Your license is not active for this URL.', 'mage-eventpress' );
					break;
				case 'item_name_mismatch':
					$message = sprintf(
					// translators: %1$s is the item name.
						__( 'This appears to be an invalid license key for %1$s.', 'mage-eventpress' ),
						$item_name
					);
					break;
				case 'no_activations_left':
					$message = __( 'Your license key has reached its activation limit.', 'mage-eventpress' );
					break;
				default:
					$message = __( 'An error occurred, please try again.', 'mage-eventpress' );
					break;
			}
			return $message;
		}
	}
	if ( ! function_exists( 'mep_license_expire_date' ) ) {
		function mep_license_expire_date( $date ) {
			if ( empty( $date ) || $date == 'lifetime' ) {
				echo esc_html( $date );
			} else {
				if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( date( 'Y-m-d H:i', strtotime( $date ) ) ) ) {
					echo get_mep_datetime( $date, 'date-time-text' );
				} else {
					esc_html_e( 'Expired', 'mage-eventpress' );
				}
			}
		}
	}
	/***************************
	 * Functions Dev by @Ariful
	 **************************/
	if ( ! function_exists( 'mep_elementor_get_events' ) ) {
		function mep_elementor_get_events( $default ) {
			$args      = array(
				'post_type'      => 'mep_events',
				'posts_per_page' => - 1, // Fetch all posts
			);
			$list      = array( '0' => $default );
			$the_query = new WP_Query( $args );
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$list[ get_the_ID() ] = get_the_title();
				}
			}
			wp_reset_postdata();
			return $list;
		}
	}
	if ( ! function_exists( 'mep_get_list_thumbnail_src' ) ) {
		function mep_get_list_thumbnail_src( $event_id, $size = 'full' ) {
			$thumbnail_id = get_post_meta( $event_id, 'mep_list_thumbnail', true ) ? get_post_meta( $event_id, 'mep_list_thumbnail', true ) : 0;
			if ( $thumbnail_id > 0 ) {
				$thumbnail = wp_get_attachment_image_src( $thumbnail_id, $size );
				echo esc_attr( is_array( $thumbnail ) && sizeof( $thumbnail ) > 0 ? $thumbnail[0] : '' );
			} else {
				$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $event_id ), $size );
				echo esc_attr( is_array( $thumbnail ) && sizeof( $thumbnail ) > 0 ? $thumbnail[0] : '' );
			}
		}
	}
	add_filter( 'mep_check_product_into_cart', 'mep_disable_add_to_cart_if_product_is_in_cart', 10, 2 );
	if ( ! function_exists( 'mep_disable_add_to_cart_if_product_is_in_cart' ) ) {
		function mep_disable_add_to_cart_if_product_is_in_cart( $is_purchasable, $product ) {
			// Loop through cart items checking if the product is already in cart
			if ( isset( WC()->cart ) && ! is_admin() && ! empty( WC()->cart->get_cart() ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if ( $cart_item['data']->get_id() == $product ) {
						return false;
					}
				}
			}
			return $is_purchasable;
		}
	}
	if ( ! function_exists( 'mep_get_default_lang_event_id' ) ) {
		function mep_get_default_lang_event_id( $event_id ) {
			global $sitepress;
			$multi_lang_plugin = mep_get_option( 'mep_multi_lang_plugin', 'general_setting_sec', 'none' );
			if ( $multi_lang_plugin == 'polylang' ) {
				// Get PolyLang ID
				$defaultLanguage = function_exists( 'pll_default_language' ) ? pll_default_language() : get_locale();
				$translations    = function_exists( 'pll_get_post_translations' ) ? pll_get_post_translations( $event_id ) : [];
				$event_id        = (is_array( $translations ) && sizeof( $translations ) > 0) ? $translations[ $defaultLanguage ] : $event_id;
			} elseif ( $multi_lang_plugin == 'wpml' ) {
				// WPML
				$default_language = function_exists( 'wpml_loaded' ) ? $sitepress->get_default_language() : get_locale(); // will return 'en'
				$event_id         = apply_filters( 'wpml_object_id', $event_id, 'mep_events', true, $default_language );
			}
			return $event_id;
		}
	}
	/*******************************************************************
	 * Function: Update Value Position from Old Settings to New Settings
	 * Developer: Ariful
	 *********************************************************************/
	function mep_change_global_option_section( $option_name, $old_sec_name, $new_sec_name, $default = null ) {
		if ( ! empty( $option_name ) && ! empty( $old_sec_name ) && ! empty( $new_sec_name ) ) {
			$chk_new_value = mep_get_option( $option_name, $new_sec_name );
			$chk_old_value = mep_get_option( $option_name, $old_sec_name );
			$new_sec_array = is_array( get_option( $new_sec_name ) ) ? maybe_unserialize( get_option( $new_sec_name ) ) : array();
			if ( isset( $chk_new_value ) && ! empty( $chk_new_value ) ) {
				return $chk_new_value;
			} else {
				if ( isset( $chk_old_value ) && ! empty( $chk_old_value ) ) {
					$created_array = array( $option_name => $chk_old_value );
					$merged_data   = array_merge( $new_sec_array, $created_array );
					update_option( $new_sec_name, $merged_data );
				}
			}
			if ( isset( $new_sec_array[ $option_name ] ) ) {
				return $new_sec_array[ $option_name ];
			} else {
				return $default;
			}
		}
	}
	if ( ! function_exists( 'mep_woo_install_check' ) ) {
		function mep_woo_install_check() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				return 'Yes';
			} elseif ( is_dir( $plugin_dir ) ) {
				return 'Installed But Not Active';
			} else {
				return 'No';
			}
		}
	}
	function mep_check_plugin_installed( $path ) {
		if ( is_plugin_active( $path ) ) {
			return true;
		} else {
			return false;
		}
	}
	function mep_get_datetime_format( $event_id = 0, $type = 'date' ) {
		$custom_format                     = get_post_meta( $event_id, 'mep_enable_custom_dt_format', true ) ? get_post_meta( $event_id, 'mep_enable_custom_dt_format', true ) : 'off';
		$date_format                       = get_option( 'date_format' );
		$time_format                       = get_option( 'time_format' );
		$current_date_format               = mep_get_option( 'mep_global_date_format', 'datetime_setting_sec', $date_format );
		$current_time_format               = mep_get_option( 'mep_global_time_format', 'datetime_setting_sec', $time_format );
		$current_global_custom_date_format = mep_get_option( 'mep_global_custom_date_format', 'datetime_setting_sec', $date_format );
		$current_global_custom_time_format = mep_get_option( 'mep_global_custom_time_format', 'datetime_setting_sec', $time_format );
		$current_global_timezone_display   = mep_get_option( 'mep_global_timezone_display', 'datetime_setting_sec', 'no' );
		$saved_date_format                 = $custom_format == 'on' && get_post_meta( $event_id, 'mep_event_date_format', true ) ? get_post_meta( $event_id, 'mep_event_date_format', true ) : $current_date_format;
		$saved_custom_date_format          = $custom_format == 'on' && get_post_meta( $event_id, 'mep_event_custom_date_format', true ) ? get_post_meta( $event_id, 'mep_event_custom_date_format', true ) : $current_global_custom_date_format;
		$saved_time_format                 = $custom_format == 'on' && get_post_meta( $event_id, 'mep_event_time_format', true ) ? get_post_meta( $event_id, 'mep_event_time_format', true ) : $current_time_format;
		$saved_custom_time_format          = $custom_format == 'on' && get_post_meta( $event_id, 'mep_custom_event_time_format', true ) ? get_post_meta( $event_id, 'mep_custom_event_time_format', true ) : $current_global_custom_time_format;
		$saved_time_zone_display           = $custom_format == 'on' && get_post_meta( $event_id, 'mep_time_zone_display', true ) ? get_post_meta( $event_id, 'mep_time_zone_display', true ) : $current_global_timezone_display;
		$date_format                       = $saved_date_format == 'custom' ? $saved_custom_date_format : $saved_date_format;
		$time_format                       = $saved_time_format == 'custom' ? $saved_custom_time_format : $saved_time_format;
		$timezone                          = $saved_time_zone_display == 'yes' ? ' T' : '';
		if ( $type == 'date' ) {
			return $date_format;
		} elseif ( $type == 'date_timezone' ) {
			return $date_format . $timezone;
		} elseif ( $type == 'time' ) {
			return $time_format;
		} elseif ( $type == 'time_timezone' ) {
			return $time_format . $timezone;
		} else {
			return $date_format;
		}
	}
	add_filter( 'mep_event_loop_list_available_seat', 'mep_speed_up_list_page', 5, 2 );
	if ( ! function_exists( 'mep_speed_up_list_page' ) ) {
		function mep_speed_up_list_page( $available, $event_id ) {
			$availabele_check = mep_get_option( 'mep_speed_up_list_page', 'general_setting_sec', 'no' );
			$available        = $availabele_check == 'yes' ? 1 : $available;
			return 1;
		}
	}
	add_action( 'admin_menu', 'mep_remove_cpt_list_page' );
	function mep_remove_cpt_list_page() {
		$user_choose_list_style = mep_get_option( 'mep_event_list_page_style', 'general_setting_sec', 'new' );
		if ( $user_choose_list_style == 'new' ) {
			remove_submenu_page( 'edit.php?post_type=mep_events', 'edit.php?post_type=mep_events' );
		} else {
			remove_submenu_page( 'edit.php?post_type=mep_events', 'mep_event_lists' );
		}
	}
	add_action( 'admin_menu', 'mep_move_event_list_to_top', 999 );
	function mep_move_event_list_to_top() {
		global $submenu;
		$parent_slug = 'edit.php?post_type=mep_events';
		if ( isset( $submenu[ $parent_slug ] ) ) {
			foreach ( $submenu[ $parent_slug ] as $key => $item ) {
				if ( $item[2] === 'mep_event_lists' ) {
					$event_list_item = $item;
					unset( $submenu[ $parent_slug ][ $key ] );
					array_unshift( $submenu[ $parent_slug ], $event_list_item );
					break;
				}
			}
		}
	}
	if ( ! function_exists( 'mep_ev_location_ticket' ) ) {
		function mep_ev_location_ticket( $event_id, $event_meta = '' ) {
			$location_info = MPWEM_Functions::get_location( $event_id );
			ob_start();
			echo esc_html( implode( ', ', array_filter( $location_info ) ) );
			$content = ob_get_clean();
			echo apply_filters( 'mage_event_location_in_ticket', $content, $event_id, $event_meta, $location_info );
		}
	}
	function mep_re_get_repeted_event_period_date_arr( $start, $end, $interval ) {
		$interval  = $interval ? $interval : 1;
		$_interval = "P" . $interval . "D";
		$period    = new DatePeriod(
			new DateTime( $start ),
			new DateInterval( $_interval ),
			new DateTime( $end )
		);
		return $period;
	}
	function mep_re_date_range( $first, $last, $period, $output_format = 'Y-m-d' ) {
		$step    = ! empty( $period ) ? "+$period day" : '+1 day';
		$dates   = array();
		$current = strtotime( $first );
		$last    = strtotime( $last );
		while ( $current <= $last ) {
			$dates[] = date( $output_format, $current );
			$current = strtotime( $step, $current );
		}
		return $dates;
	}
	function get_mep_re_recurring_date( $event_id, $event_multi_date, $mep_show_upcoming_event, $select_dateLabel = '' ) {
		$select_dateLabel = $select_dateLabel ?: mep_get_option( 'mep_event_rec_select_event_date_text', 'label_setting_sec', __( 'Select Event Date:', 'mage-eventpress' ) );
		ob_start();
		$mep_show_upcoming_event = get_post_meta( $event_id, 'mep_show_upcoming_event', true ) && ! is_admin() ? get_post_meta( $event_id, 'mep_show_upcoming_event', true ) : 'no';
		?>
        <div class="mep_everyday_date_secs">
            <div class="mep-date-time-select-area ">
                <h3 class='mep_re_datelist_label'>
					<?php echo mep_esc_html( $select_dateLabel ); ?>
                </h3>
                <div>
					<?php
						$cn = 1;
						if ( $mep_show_upcoming_event == 'yes' ) {
							foreach ( $event_multi_date as $event_date ) {
								$start_date = date( 'Y-m-d H:i', strtotime( $event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'] ) );
								$end_date   = date( 'Y-m-d H:i', strtotime( $event_date['event_more_end_date'] . ' ' . $event_date['event_more_end_time'] ) );
								if ( strtotime( current_time( 'Y-m-d H:i:s' ) ) < strtotime( date( 'Y-m-d H:i:s', strtotime( $start_date ) ) ) ) {
									if ( $mep_show_upcoming_event == 'yes' ) {
										$cnt = 1;
									} else {
										$cnt = $cn;
									}
									if ( $cn == $cnt ) {
										?>
                                        <input type='hidden' name="recurring_date" id="mep_recurring_date" value="<?php echo esc_attr( $start_date ); ?>"/>
                                        <span class='mep-re-single-date' style='font-size:18px;font-weight: bold;'><?php echo mep_esc_html( get_mep_datetime( $start_date, 'date-time' ) ); ?></span>
										<?php
									}
									$cn ++;
								}
							}
						} else {
							$cn = 1;
							echo mep_esc_html( '<select name="recurring_date" id="mep_recurring_date">' );
							if ( is_admin() ) {
								echo mep_esc_html( '<option value="">All Attendees</option>' );
							}
							foreach ( $event_multi_date as $event_date ) {
								$start_date = date( 'Y-m-d H:i', strtotime( $event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'] ) );
								$end_date   = date( 'Y-m-d H:i', strtotime( $event_date['event_more_end_date'] . ' ' . $event_date['event_more_end_time'] ) );
								if ( is_admin() ) {
									if ( $mep_show_upcoming_event == 'yes' ) {
										$cnt = 1;
									} else {
										$cnt = $cn;
									}
									if ( $cn == $cnt ) {
										?>
                                        <option value="<?php echo mep_esc_html( $start_date ); ?>" <?php if ( isset( $_GET['date'] ) && ! empty( $_GET['date'] ) ) {
											echo strtotime( $start_date ) == sanitize_text_field( $_GET['date'] ) ? 'selected' : "";
										} ?>><?php echo mep_esc_html( get_mep_datetime( $start_date, 'date-time' ) ); ?></option>
										<?php
									}
								} elseif ( strtotime( current_time( 'Y-m-d H:i:s' ) ) < strtotime( date( 'Y-m-d H:i:s', strtotime( $event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'] ) ) ) ) {
									if ( $mep_show_upcoming_event == 'yes' ) {
										$cnt = 1;
									} else {
										$cnt = $cn;
									}
									if ( $cn == $cnt ) {
										?>
                                        <option value="<?php echo esc_attr( $start_date ); ?>" <?php if ( isset( $_GET['date'] ) && ! empty( $_GET['date'] ) ) {
											echo mep_esc_html( strtotime( $start_date ) ) == sanitize_text_field( $_GET['date'] ) ? 'selected' : "";
										} ?>><?php echo mep_esc_html( get_mep_datetime( $start_date, apply_filters( 'mep_recurring_particular_list_date_format', 'date-time' ) ) ); ?></option>
										<?php
									}
								}
								$cn ++;
							}
							echo '</select>';
						}
					?>
                </div>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}
	function mep_get_event_date( $global_on_days_arr ) {
		global $post;
		$event_id         = is_object( $post ) ? $post->ID : get_the_id();
		$time_status      = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$event_start_time = date( 'H:i:s', strtotime( get_post_meta( $event_id, 'event_start_datetime', true ) ) );
		$event_time       = $time_status == 'no' ? ' ' . $event_start_time : '';
		$now              = $time_status == 'no' ? current_time( 'Y-m-d H:i:s' ) : current_time( 'Y-m-d' );
		$dt               = [];
		foreach ( $global_on_days_arr as $dates ) {
			if ( strtotime( $now ) <= strtotime( $dates . $event_time ) ) {
				$dt[] = $dates;
			}
		}
		return $dt;
	}
	add_filter( 'mep_event_upcoming_date', 'mep_re_event_upcoming_date', 10, 2 );
	function mep_re_event_upcoming_date( $date, $event_id ) {
// print_r(mep_re_event_upcoming_date_filter($date, $event_id));
		$arr = mep_re_event_upcoming_date_filter( $date, $event_id ) ? mep_re_event_upcoming_date_filter( $date, $event_id ) : get_post_meta( $event_id, 'event_start_datetime', true );
		return $arr;
	}
	add_filter( 'mep_event_upcoming_date_filter', 'mep_re_event_upcoming_date_filter', 10, 2 );
	function mep_re_event_upcoming_date_filter( $date, $event_id ) {
		$recurring               = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		$hide_only_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'general_setting_sec', 'no' );
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$every_day               = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? $event_date_display_list[0] : '';
			$every_day               = date( 'Y-m-d', strtotime( $every_day ) );
			if ( $time_status == 'no' ) {
				$start_date = $every_day;
				$start_time = get_post_meta( $event_id, 'event_start_time', true );
				$date       = $every_day . ' ' . $start_time;
			} elseif ( $time_status == 'yes' ) {
				$calender_day = strtolower( date( 'D', strtotime( $every_day ) ) );
				$day_name     = 'mep_ticket_times_' . $calender_day;
				$time         = get_post_meta( $event_id, $day_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_name, true ) ) : maybe_unserialize( $global_time_slots );
				$time_list    = [];
				foreach ( $time as $_time ) {
					$time_list[] = $_time['mep_ticket_time'];
				}
				if ( is_array( $time_list ) && sizeof( $time_list ) > 0 ) {
					$date = date( 'Y-m-d H:i:s', strtotime( $every_day . ' ' . $time_list[0] ) );
				}
			}
		} elseif ( $recurring == 'yes' ) {
			$event_start_datetime = get_post_meta( $event_id, 'event_start_datetime', true ) ? get_post_meta( $event_id, 'event_start_datetime', true ) : '';
			$event_end_datetime   = get_post_meta( $event_id, 'event_end_datetime', true ) ? get_post_meta( $event_id, 'event_end_datetime', true ) : '';
			$event_multidate      = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : '';
			$event_std[]          = array(
				'event_std' => $event_start_datetime,
				'event_etd' => $event_end_datetime
			);
			$a                    = 1;
			if ( is_array( $event_multidate ) && sizeof( $event_multidate ) > 0 ) {
				foreach ( $event_multidate as $event_mdt ) {
					$event_std[ $a ]['event_std'] = $event_mdt['event_more_start_date'] . ' ' . $event_mdt['event_more_start_time'];
					$event_std[ $a ]['event_etd'] = $event_mdt['event_more_end_date'] . ' ' . $event_mdt['event_more_end_time'];
					$a ++;
				}
				$cn = 0;
				foreach ( $event_std as $_event_std ) {
					$std        = $_event_std['event_std'];
					$start_date = date( 'Y-m-d H:i:s', strtotime( $_event_std['event_std'] ) );
					$end_date   = date( 'Y-m-d', strtotime( $_event_std['event_etd'] ) );
					if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $std ) && $cn == 0 ) {
						$date = $start_date;
						$cn ++;
					}
				}
			}
		}
		return $date;
	}
	add_filter( 'mep_event_list_only_day_number', 'mep_re_event_list_only_day_number', 90, 2 );
	function mep_re_event_list_only_day_number( $day, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$day                     = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? get_mep_datetime( $event_date_display_list[0], 'day' ) : '';
		}
		// return $day;
		return get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'day' );
	}
	add_filter( 'mep_event_list_only_month_name', 'mep_re_event_list_only_month_name', 10, 2 );
	function mep_re_event_list_only_month_name( $month, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$month                   = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? get_mep_datetime( $event_date_display_list[0], 'month' ) : '';
		}
		return get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'month-name' );
	}
	add_filter( 'mep_event_date_more_date_array_event_list', 'mep_re_event_date_more_date_array_event_list', 10, 2 );
	function mep_re_event_date_more_date_array_event_list( $more_date, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$moreDate                = $event_date_display_list;
			$more_date               = [];
			foreach ( $moreDate as $_moreDate ) {
				$more_date[]['event_more_start_date'] = $_moreDate;
				$more_date[]['event_more_start_time'] = '12:00 PM';
				$more_date[]['event_more_end_date']   = '';
				$more_date[]['event_more_end_time']   = '';
			}
		}
		return $more_date;
	}
	add_filter( 'mep_event_date_more_date_array', 'mep_re_event_date_more_date_array', 10, 2 );
	function mep_re_event_date_more_date_array( $more_date, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr      = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr      = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$more_date               = $event_date_display_list;
		}
		return $more_date;
	}
	function mep_re_get_the_upcomming_date_arr( $event_id ) {
		$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
		$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : '';
		$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
		$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
		$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
		$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr = [];
		$off_dates          = [];
		foreach ( $period as $key => $value ) {
			$global_on_days_arr[] = $value->format( 'Y-m-d' );
		}
		$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
		// code by user
		$special_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
		// print_r($special_dates);
		if ( is_array( $special_dates ) ) {
			$now = strtotime( current_time( 'Y-m-d' ) );
			foreach ( $special_dates as $special_date ) {
				if ( empty( $special_date['start_date'] ) || $now > strtotime( $special_date['start_date'] ) ) {
					continue;
				}
				// Not today
				if ( $now < strtotime( $special_date['start_date'] ) ) {
					$global_on_days_arr[] = date( 'Y-m-d', strtotime( $special_date['start_date'] ) );
					continue;
				}
				// Today, check time
				if ( isset( $special_date['time'] ) && is_array( $special_date['time'] ) ) {
					foreach ( $special_date['time'] as $sd_time ) {
						if ( empty( $sd_time['mep_ticket_time'] ) ) {
							continue;
						}
						$time_str       = $special_date['start_date'] . ' ' . $sd_time['mep_ticket_time'] . ' ' . wp_timezone_string();
						$event_php_time = strtotime( $time_str );
						if ( time() < $event_php_time ) {
							$global_on_days_arr[] = date( 'Y-m-d', strtotime( $special_date['start_date'] ) );
						}
					}
				}
			}
		}
		sort( $global_on_days_arr );
		$event_date_display_list = mep_get_event_date( $global_on_days_arr );
		if ( is_array( $global_off_dates ) && sizeof( $global_off_dates ) > 0 ) {
			foreach ( $global_off_dates as $key => $value ) {
				$off_dates[] = $value['mep_ticket_off_date'];
			}
		}
		$priority = get_post_meta( $event_id, 'mep_ticket_off_priority', true );
		if ( ! $priority ) {
			$priority = 'offdate';
		}
		if ( is_array( $global_off_dates ) && sizeof( $global_off_dates ) > 0 ) {
			foreach ( $global_off_dates as $key => $value ) {
				$off_dates[] = $value['mep_ticket_off_date'];
			}
		}
		if ( $priority === 'offday' ) {
			// Remove off days first
			$filtered = [];
			foreach ( $event_date_display_list as $every_day ) {
				$event_day = strtolower( date( 'D', strtotime( $every_day ) ) );
				if ( ! in_array( $event_day, $global_off_days ) ) {
					$filtered[] = $every_day;
				}
			}
			// Then remove off dates
			$filtered            = array_diff( $filtered, $off_dates );
			$the_recurring_dates = array_values( $filtered );
		} else {
			// Default: Remove off dates first
			$filtered = array_diff( $event_date_display_list, $off_dates );
			$final    = [];
			foreach ( $filtered as $every_day ) {
				$event_day = strtolower( date( 'D', strtotime( $every_day ) ) );
				if ( ! in_array( $event_day, $global_off_days ) ) {
					$final[] = $every_day;
				}
			}
			$the_recurring_dates = array_values( $final );
		}
		$block_offdays  = get_post_meta( $event_id, 'mep_ticket_block_offdays__', true );
		$block_offdates = get_post_meta( $event_id, 'mep_ticket_block_offdates__', true );
		$filtered       = $event_date_display_list;
		if ( $block_offdates === 'yes' && is_array( $off_dates ) && count( $off_dates ) > 0 ) {
			$filtered = array_diff( $filtered, $off_dates );
		}
		if ( $block_offdays === 'yes' && is_array( $global_off_days ) && count( $global_off_days ) > 0 ) {
			$final = [];
			foreach ( $filtered as $every_day ) {
				$event_day = strtolower( date( 'D', strtotime( $every_day ) ) );
				if ( ! in_array( $event_day, $global_off_days ) ) {
					$final[] = $every_day;
				}
			}
			$filtered = $final;
		}
		$the_recurring_dates = array_values( $filtered );
		return $the_recurring_dates;
	}
	function mep_re_get_everyday_event_date_sec( $event_id ) {
		$time_status       = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days   = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
		$global_off_dates  = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
		$event_start_date  = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
		$event_end_date    = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
		$interval          = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
		$period            = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
		//    $nd = mep_re_date_range($event_start_date, $event_end_date, $interval);
		$global_on_days_arr = [];
		foreach ( $period as $key => $value ) {
			//  print_r($value);
			$global_on_days_arr[] = $value->format( 'Y-m-d' );
		}
		$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
		$event_date_display = mep_re_get_the_upcomming_date_arr( $event_id );
		$datepicker_format  = mep_get_option( 'mep_datepicker_format', 'general_setting_sec', 'yy-mm-dd' );
		$date_format        = mep_rec_get_datepicker_php_format( $datepicker_format );
		if ( is_array( $global_on_days_arr ) && sizeof( $global_on_days_arr ) > 0 ) {
			$date_parameter = isset( $_GET['date'] ) ? sanitize_text_field( date( $date_format, $_GET['date'] ) ) : null;
			ob_start();
			?>
            <div class='mep_everyday_date_secs'>
                <div class="mep-date-time-select-area ">
                    <h3 class='mep_re_datelist_label'>
						<?php echo mep_get_option( 'mep_event_rec_select_event_date_text', 'label_setting_sec', __( 'Select Event Date:', 'mage-eventpress' ) ); ?>
                    </h3>
                    <div class="mep-date-time">
						<?php if ( is_array( $global_on_days_arr ) && sizeof( $global_on_days_arr ) == 1 ) { ?>
                            <span style='font-size: 20px;'><?php if ( $time_status == 'yes' ) {
									echo mep_esc_html( $date_parameter )
									     ?? get_mep_datetime( $global_on_days_arr[0], 'date-text' );
								} else {
									echo $date_parameter ?? get_mep_datetime( $global_on_days_arr[0], 'date-time-text' );
								} ?></span>
                            <input <?php if ( ! is_admin() ) {
								echo 'readonly';
							} ?> type="hidden" name='mep_everyday_dates' id='mep_everyday_datepicker' value="<?php echo $date_parameter ?? mep_esc_html( $global_on_days_arr[0] ); ?>">
						<?php } else { ?>
                            <span class='mep_recurring_datepicker_section'>
                    <span class='mep-datepicker-input-box'>
                        <input <?php if ( ! is_admin() ) {
	                        echo 'readonly';
                        } ?> type="text" name='mep_everyday_dates' id='mep_everyday_datepicker' value="<?php echo $date_parameter ?? date( $date_format, strtotime( mep_re_get_the_upcomming_date_arr( $event_id )[0] ) ); ?>">
                    </span>
                    </span>
						<?php } ?>
                        <!-- time -->
                        <div>
                        <span id="mep_everyday_event_time_list">
                            <?php
	                            if ( $time_status == 'yes' ) {
		                            ?>
                                    <input type="hidden" name='time_slot_name' id='time_slot_name' value=''>
		                            <?php
		                            mep_re_default_load_ticket_time_list( $event_id, $global_on_days_arr[0] );
	                            }
                            ?>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			if ( is_admin() ) {
				require_once( dirname( __DIR__ ) . "/inc/recurring/js/datepicker_calculation.php" );
			}
			require_once( dirname( __DIR__ ) . "/inc/recurring/js/ajax_everyday_datepicker.php" );
		} else {
			?>
            <div>
                <h5 style='text-align:center;color:red;font-size:20px'>
					<?php _e( 'Please Set Correct Event Start & Expire date', 'mage-eventpress' ); ?>
                </h5>
            </div>
			<?php
		}
		echo ob_get_clean();
	}
	function mep_rec_get_datepicker_php_format( $fotmat ) {
		$php_format = str_replace(
			array( "yy-mm-dd", "yy/mm/dd", "yy-dd-mm", "yy/dd/mm", "dd-mm-yy", "dd/mm/yy", "mm-dd-yy", "mm/dd/yy", "d M , yy", "D d M , yy", "M d , yy", "D M d , yy" ),
			array( "Y-m-d", "Y/m/d", "Y-d-m", "Y/d/m", "d-m-Y", "d/m/Y", "m-d-Y", "m/d/Y", "j M , Y", "D j M , Y", "M  j, Y", "D M  j, Y" ),
			$fotmat
		);
		return $php_format;
	}
	function mep_re_default_load_ticket_time_list( $event_id, $event_date ) {
		$selected_day         = strtolower( date( 'D', strtotime( $event_date ) ) );
		$day_time_slot_name   = 'mep_ticket_times_' . $selected_day;
		$time_status          = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots    = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_times_global', true ) ) : [];
		$day_time_slots       = get_post_meta( $event_id, $day_time_slot_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_time_slot_name, true ) ) : [];
		$event_off_days       = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
		$global_off_dates     = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : [];
		$global_off_dates_arr = [];
		if ( is_array( $global_off_dates ) && sizeof( $global_off_dates ) > 0 ) {
			foreach ( $global_off_dates as $off_dates ) {
				$global_off_dates_arr[] = $off_dates['mep_ticket_off_date'];
			}
		}
		if ( ( is_array( $event_off_days ) && sizeof( $event_off_days ) > 0 && in_array( $selected_day, $event_off_days ) ) || in_array( $event_date, $global_off_dates_arr ) ) {
			echo mep_get_option( 'mep_event_rec_day_off_text', 'label_setting_sec', __( 'Day Off', 'mage-eventpress' ) );
		} else {
			?>
            <select name="ea_event_date" id="mep_everyday_ticket_time">
				<?php apply_filters( 'mep_everyday_time_list_item', mep_get_everyday_time_list( $event_id, $event_date ), $event_id ); ?>
            </select>
			<?php
			if ( $time_status == 'yes' ) {
				require_once( dirname( __DIR__ ) . "/inc/recurring/js/onload_timelist.php" );
			}
		}
	}
	add_action( 'mep_event_cart_order_data_add', 'mep_re_add_cart_order_data', 10, 2 );
	function mep_re_add_cart_order_data( $values, $item ) {
		$cart_location = array_key_exists( "event_everyday_time_slot", $values ) ? $values['event_everyday_time_slot'] : '';
		if ( $cart_location ) {
			$item->add_meta_data( mep_get_option( 'mep_event_rec_time_slot_text', 'label_setting_sec', __( 'Time Slot:', 'mage-eventpress' ) ), $cart_location );
			$item->add_meta_data( '_time_slot', $cart_location );
		}
	}
	add_filter( 'mep_event_attendee_dynamic_data', 'mep_re_event_attendee_data_save', 15, 6 );
	function mep_re_event_attendee_data_save( $the_array, $pid, $type, $order_id, $event_id, $_user_info ) {
		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $item_id => $item_values ) {
			$item_id = $item_id;
		}
		$time_slot = wc_get_order_item_meta( $item_id, '_time_slot', true ) ? wc_get_order_item_meta( $item_id, '_time_slot', true ) : '';
		if ( $time_slot ) {
			$the_array[] = array(
				'name'  => 'ea_time_slot',
				'value' => $time_slot
			);
		}
		return $the_array;
	}
	add_action( 'mep_pdf_event_multidate', 'mep_re_show_data_in_pdf', 10, 4 );
	function mep_re_show_data_in_pdf( $ticket_id, $event_id = '', $order_id = '', $ticket_type = '' ) {
		$time_slot = get_post_meta( $ticket_id, 'ea_time_slot', true ) ? get_post_meta( $ticket_id, 'ea_time_slot', true ) : '';
		if ( $time_slot ) {
			?>
            <li><strong><?php echo mep_get_option( 'mep_event_rec_time_slot_text', 'label_setting_sec', __( 'Time Slot:', 'mage-eventpress' ) ); ?></strong> <?php echo mep_esc_html( $time_slot ); ?></li>
			<?php
		}
	}
	function mep_get_everyday_time_list( $event_id, $event_date ) {
		// echo $event_id;
		$hidden_date = $event_date ? date( 'Y-m-d', strtotime( $event_date ) ) : '';
		$all_dates   = MPWEM_Functions::get_dates( $event_id );
		$all_times   = MPWEM_Functions::get_times( $event_id, $all_dates, $hidden_date );
		if ( is_array( $all_times ) && sizeof( $all_times ) ) {
			foreach ( $all_times as $times ) { ?>
                <option value="<?php echo esc_attr( $hidden_date . ' ' . $times['start']['time'] ); ?>"><?php echo esc_html( $times['start']['label'] ); ?></option>
			<?php }
		}
	}
	add_action( 'mep_before_attendee_list_btn', 'mep_rq_show_everyday_datepicker' );
	function mep_rq_show_everyday_datepicker( $event_id ) {
		$time_status       = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days   = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
		$global_off_dates  = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
		$input_name        = $time_status == 'yes' ? 'mep_everyday_dates' : 'ea_event_date';
		ob_start();
		?>
        <div class='mep_everyday_date_secs'>
            <div class="mep-date-time-select-area ">
                <div>
                    <input type="text" name='<?php echo esc_attr( $input_name ); ?>' id='mep_everyday_datepicker_<?php echo esc_attr( $event_id ); ?>' value="<?php echo current_time( 'Y-m-d' ); ?>">
                </div>
                <div>
                    <span id="mep_everyday_event_time_list_<?php echo esc_attr( $event_id ); ?>"></span>
                </div>
            </div>
        </div>
		<?php
		require( dirname( __DIR__ ) . "/inc/recurring/js/before_attendee_list_btn.php" );
		echo ob_get_clean();
	}
	add_action( 'mep_before_csv_export_btn', 'mep_rq_show_everyday_datepicker_csv_btn' );
	function mep_rq_show_everyday_datepicker_csv_btn( $event_id ) {
		$time_status       = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days   = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
		$global_off_dates  = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
		$input_name        = $time_status == 'yes' ? 'mep_everyday_dates' : 'ea_event_date';
		ob_start();
		?>
        <div class='mep_everyday_date_secs'>
            <div class="mep-date-time-select-area ">
                <div>
                    <i class="far fa-calendar-alt icon"></i>
                    <input type="text" name='<?php echo esc_attr( $input_name ); ?>' id='mep_everyday_datepicker_csv_<?php echo esc_attr( $event_id ); ?>' value="<?php echo current_time( 'Y-m-d' ); ?>">
                </div>
                <div>
                    <span id="mep_everyday_event_time_list_csv_<?php echo mep_esc_html( $event_id ); ?>"></span>
                </div>
            </div>
        </div>
		<?php
		require( dirname( __DIR__ ) . "/inc/recurring/js/before_csv_export_btn.php" );
		echo ob_get_clean();
	}
	add_action( 'wp_ajax_mep_fb_ajax_attendee_filter_date', 'mep_fb_ajax_attendee_filter_date' );
	function mep_fb_ajax_attendee_filter_date() {
		$event_id  = sanitize_text_field( $_REQUEST['event_id'] );
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			mep_re_get_everyday_event_date_sec( $event_id );
		} elseif ( $recurring == 'yes' ) {
			$event_more_date[0]['event_more_start_date'] = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_more_date[0]['event_more_start_time'] = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_start_time', true ) ) );
			$event_more_date[0]['event_more_end_date']   = date( 'Y-m-d', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$event_more_date[0]['event_more_end_time']   = date( 'H:i', strtotime( get_post_meta( $event_id, 'event_end_time', true ) ) );
			$event_more_dates                            = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : array();
			$event_multi_date                            = array_merge( $event_more_date, $event_more_dates );
			// $mep_available_seat = array_key_exists('mep_available_seat', $event_meta) ? $event_meta['mep_available_seat'][0] : 'on';
			$count = 1;
			echo get_mep_re_recurring_date( $event_id, $event_multi_date, 'no' );
		} else {
			?>
            <input type="hidden" id='mep_everyday_ticket_time' value='0'>
			<?php
		}
		die();
	}
	add_action( 'mep_fb_attendee_list_script', 'mep_re_attendee_list_filter_script' );
	function mep_re_attendee_list_filter_script() {
		?>
        jQuery('#mep_event_id').on('change', function() {
        var event_id = jQuery(this).val();
        jQuery.ajax({
        type: 'POST',
        // url: mep_ajax.mep_ajaxurl,
        url: ajaxurl,
        data: {
        "action": "mep_fb_ajax_attendee_filter_date",
        "event_id": event_id
        },
        beforeSend: function() {
        jQuery('#event_attendee_filter_btn').hide();
        jQuery('#filter_attitional_btn').html('...');
        },
        success: function(data) {
        jQuery('#event_attendee_filter_btn').show();
        jQuery('#filter_attitional_btn').html(data);
        }
        });
        return false;
        });
		<?php
	}
	add_filter( 'mepca_event_time_list', 'mep_re_event_time_list', 10, 4 );
	function mep_re_event_time_list( $current_time, $date_arr, $event_id, $date ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$time_status       = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			if ( $time_status == 'no' ) {
				return get_mep_datetime( get_post_meta( $event_id, 'event_start_time', true ), 'time' ) . '-' . get_mep_datetime( get_post_meta( $event_id, 'event_end_time', true ), 'time' );
			} else {
				$calender_day   = strtolower( date( 'D', strtotime( $date ) ) );
				$day_name       = 'mep_ticket_times_' . $calender_day;
				$this_day_times = get_post_meta( $event_id, $day_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_name, true ) ) : maybe_unserialize( $global_time_slots );
				$times          = [];
				if ( is_array( $this_day_times ) && sizeof( $this_day_times ) > 0 ) {
					foreach ( $this_day_times as $_time ) {
						$times[] = $_time['mep_ticket_time_name'] . ' (' . get_mep_datetime( $_time['mep_ticket_time'], 'time' ) . ')';
					}
				}
				return implode( ', ', $times );
			}
		} else {
			return $current_time;
		}
	}
	add_filter( 'mep_event_dates_in_calender_free', 'mep_re_modify_calerder_free_dates', 15, 2 );
	function mep_re_modify_calerder_free_dates( $date_arr, $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$event_start_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_datetime', true ) ) );
			$event_end_date       = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_datetime', true ) ) );
			$event_off_days       = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
			$global_off_dates     = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : [];
			$global_off_dates_arr = [];
			if ( is_array( $global_off_dates ) && sizeof( $global_off_dates ) > 0 ) {
				foreach ( $global_off_dates as $off_dates ) {
					$global_off_dates_arr[] = date( 'Y-m-d', strtotime( $off_dates['mep_ticket_off_date'] ) );
				}
			}
			$global_off_days_arr = [];
			if ( is_array( $event_off_days ) && sizeof( $event_off_days ) > 0 ) {
				foreach ( $event_off_days as $off_days ) {
					if ( $off_days == 'sat' ) {
						$off_days = 'sat';
					}
					if ( $off_days == 'tue' ) {
						$off_days = 'tue';
					}
					if ( $off_days == 'wed' ) {
						$off_days = 'wed';
					}
					if ( $off_days == 'thu' ) {
						$off_days = 'thu';
					}
					$global_off_days_arr[] = ucwords( $off_days );
				}
			}
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$the_d = date( 'D', strtotime( $value->format( 'Y-m-d' ) ) );
				if ( ! in_array( $the_d, $global_off_days_arr ) ) {
					$global_on_days_arr[] = date( 'Y-m-d H:i:s', strtotime( $value->format( 'Y-m-d H:i:s' ) ) );
				}
			}
			$fdate      = array_diff( $global_on_days_arr, $global_off_dates_arr );
			$m_date_arr = [];
			if ( is_array( $fdate ) && sizeof( $fdate ) > 0 ) {
				$i = 0;
				foreach ( $fdate as $mdate ) {
					$mstart                    = $mdate;
					$mend                      = $mdate;
					$m_date_arr[ $i ]['start'] = $mstart;
					$m_date_arr[ $i ]['end']   = $mend;
					$i ++;
				}
			}
			return $m_date_arr;
		} else {
			return $date_arr;
		}
	}
	add_filter( 'display_post_states', 'mep_re_event_state_text', 10, 2 );
	function mep_re_event_state_text( $post_states, $post ) {
		$eid       = $post->ID;
		$recurring = get_post_meta( $eid, 'mep_enable_recurring', true ) ? get_post_meta( $eid, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$event_state = __( 'Recurring Event (Repeated)', 'mage-eventpress' );
		} elseif ( $recurring == 'yes' ) {
			$event_state = __( 'Recurring Event (Selected Dates)', 'mage-eventpress' );
		} else {
			$event_state = '';
		}
		$post_states[] = $event_state;
		$post_states   = array_filter( $post_states );
		return $post_states;
	}
// from main file
	add_filter( 'mep_event_total_seat_count', 'mep_update_total_seat_count', 10, 2 );
	function mep_update_total_seat_count( $total, $event_id ) {
		$status = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'normal';
		if ( $status == 'yes' || $status == 'everyday' ) {
			return 100;
		} else {
			return $total;
		}
	}
	function mep_event_pagination( $total_page ) {
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}
		?>
        <div class="row">
            <div class="col-md-12">
				<?php
					$pargs = array(
						"current" => $paged,
						"total"   => $total_page
					);
				?>
                <div class='pagination-sec'><?php echo paginate_links( $pargs ); ?></div>
            </div>
        </div>
		<?php
	}
	/******************** Remove below function after 2025**********************/
	if ( ! function_exists( 'mep_merge_saved_array' ) ) {
		function mep_merge_saved_array( $arr1, $arr2 ) {
			$output = [];
			for ( $i = 0; $i < count( $arr1 ); $i ++ ) {
				$output[ $i ] = array_merge( $arr1[ $i ], $arr2[ $i ] );
			}
			return $output;
		}
	}
	if ( ! function_exists( 'mep_save_attendee_info_into_cart' ) ) {
		function mep_save_attendee_info_into_cart( $product_id ) {
			$user                  = array();
			$mep_user_name         = isset( $_POST['user_name'] ) ? mage_array_strip( $_POST['user_name'] ) : [];
			$mep_user_email        = isset( $_POST['user_email'] ) ? mage_array_strip( $_POST['user_email'] ) : [];
			$mep_user_phone        = isset( $_POST['user_phone'] ) ? mage_array_strip( $_POST['user_phone'] ) : [];
			$mep_user_address      = isset( $_POST['user_address'] ) ? mage_array_strip( $_POST['user_address'] ) : [];
			$mep_user_gender       = isset( $_POST['user_gender'] ) ? mage_array_strip( $_POST['user_gender'] ) : [];
			$mep_user_tshirtsize   = isset( $_POST['tshirtsize'] ) ? mage_array_strip( $_POST['tshirtsize'] ) : [];
			$mep_user_company      = isset( $_POST['user_company'] ) ? mage_array_strip( $_POST['user_company'] ) : [];
			$mep_user_desg         = isset( $_POST['user_designation'] ) ? mage_array_strip( $_POST['user_designation'] ) : [];
			$mep_user_website      = isset( $_POST['user_website'] ) ? mage_array_strip( $_POST['user_website'] ) : [];
			$mep_user_vegetarian   = isset( $_POST['vegetarian'] ) ? mage_array_strip( $_POST['vegetarian'] ) : [];
			$mep_event_start_date  = isset( $_POST['mep_event_start_date'] ) ? mage_array_strip( $_POST['mep_event_start_date'] ) : array();
			$names                 = isset( $_POST['option_name'] ) ? mage_array_strip( $_POST['option_name'] ) : array();
			$qty                   = isset( $_POST['option_qty'] ) ? mage_array_strip( $_POST['option_qty'] ) : array();
			$reg_form_id           = mep_fb_get_reg_form_id( $product_id );
			$mep_form_builder_data = get_post_meta( $reg_form_id, 'mep_form_builder_data', true );
			$iu                    = 0;
			//if ( isset( $_POST['user_name'] ) || isset( $_POST['user_email'] ) || isset( $_POST['user_phone'] ) || isset( $_POST['gender'] ) || isset( $_POST['tshirtsize'] ) || isset( $_POST['user_company'] ) || isset( $_POST['user_designation'] ) || isset( $_POST['user_website'] ) || isset( $_POST['vegetarian'] ) ) {
			if ( is_array( $names ) && sizeof( $names ) > 0 ) {
				$same_attendee     = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_enable_same_attendee', 'no' );
				$current_template  = MPWEM_Global_Function::get_post_info( $product_id, 'mep_event_template' );
				$global_template   = mep_get_option( 'mep_global_single_template', 'single_event_setting_sec', 'default-theme.php' );
				$_current_template = $current_template ?: $global_template;
				foreach ( $names as $key => $name ) {
					$current_qty = $qty[ $key ];
					$current_qty = apply_filters( 'mpwem_group_actual_qty', $current_qty, $product_id, $name );
					if ( $current_qty > 0 && $name ) {
						for ( $j = 0; $j < $qty[ $key ]; $j ++ ) {
							if ( ( $same_attendee == 'yes' || $same_attendee == 'must' ) && $iu > 0 && $_current_template == 'smart.php' ) {
								$user[ $iu ]                     = current( $user );
								$user[ $iu ]['user_ticket_type'] = strip_tags( $name );
								if ( isset( $qty[ $key ] ) ) :
									$user[ $iu ]['user_ticket_qty'] = stripslashes( $qty[ $key ] );
								endif;
							} else {
								if ( isset( $mep_user_name[ $iu ] ) ):
									$user[ $iu ]['user_name'] = stripslashes( strip_tags( $mep_user_name[ $iu ] ) );
								endif;
								if ( isset( $mep_user_email[ $iu ] ) ) :
									$user[ $iu ]['user_email'] = stripslashes( strip_tags( $mep_user_email[ $iu ] ) );
								endif;
								if ( isset( $mep_user_phone[ $iu ] ) ) :
									$user[ $iu ]['user_phone'] = stripslashes( strip_tags( $mep_user_phone[ $iu ] ) );
								endif;
								if ( isset( $mep_user_address[ $iu ] ) ) :
									$user[ $iu ]['user_address'] = stripslashes( strip_tags( $mep_user_address[ $iu ] ) );
								endif;
								if ( isset( $mep_user_gender[ $iu ] ) ) :
									$user[ $iu ]['user_gender'] = stripslashes( strip_tags( $mep_user_gender[ $iu ] ) );
								endif;
								if ( isset( $mep_user_tshirtsize[ $iu ] ) ) :
									$user[ $iu ]['user_tshirtsize'] = stripslashes( strip_tags( $mep_user_tshirtsize[ $iu ] ) );
								endif;
								if ( isset( $mep_user_company[ $iu ] ) ) :
									$user[ $iu ]['user_company'] = stripslashes( strip_tags( $mep_user_company[ $iu ] ) );
								endif;
								if ( isset( $mep_user_desg[ $iu ] ) ) :
									$user[ $iu ]['user_designation'] = stripslashes( strip_tags( $mep_user_desg[ $iu ] ) );
								endif;
								if ( isset( $mep_user_website[ $iu ] ) ) :
									$user[ $iu ]['user_website'] = stripslashes( strip_tags( $mep_user_website[ $iu ] ) );
								endif;
								if ( isset( $mep_user_vegetarian[ $iu ] ) ) :
									$user[ $iu ]['user_vegetarian'] = stripslashes( strip_tags( $mep_user_vegetarian[ $iu ] ) );
								endif;
								$user[ $iu ]['user_ticket_type'] = strip_tags( $name );
								$user[ $iu ]['user_event_date']  = stripslashes( strip_tags( current( $mep_event_start_date ) ) );
								if ( $product_id ) :
									$user[ $iu ]['user_event_id'] = $product_id;
								endif;
								if ( isset( $qty[ $key ] ) ) :
									$user[ $iu ]['user_ticket_qty'] = stripslashes( $qty[ $key ] );
								endif;
								if ( $mep_form_builder_data ) {
									foreach ( $mep_form_builder_data as $_field ) {
										$user[ $iu ][ $_field['mep_fbc_id'] ] = isset( $_POST[ $_field['mep_fbc_id'] ][ $iu ] ) ? stripslashes( mage_array_strip( $_POST[ $_field['mep_fbc_id'] ][ $iu ] ) ) : "";
										$user                                 = apply_filters( 'mep_attendee_upload_file', $user, $iu, $_field );
									}
								}
							}
							$iu ++;
						}
					}
				}
			}
			//}
			return apply_filters( 'mep_cart_user_data_prepare', $user, $product_id );
		}
	}
	if ( ! function_exists( 'mep_cart_display_user_list' ) ) {
		function mep_cart_display_user_list( $user_info, $event_id ) {
			$custom_forms_id = mep_get_user_custom_field_ids( $event_id );
			$form_id         = mep_fb_get_reg_form_id( $event_id );
			ob_start();
			$recurring   = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
			$time_status = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			foreach ( $user_info as $userinf ) {
				// array_key_exists(
				?>
                <ul class='mep_cart_user_inforation_details'>
					<?php if ( array_key_exists( 'user_name', $userinf ) && ! empty( $userinf['user_name'] ) ) { ?>
                        <li class='mep_cart_user_name'><?php echo esc_attr( mep_get_reg_label( $event_id, 'Name' ) ) . ": ";
								echo esc_attr( $userinf['user_name'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_email', $userinf ) && ! empty( $userinf['user_email'] ) ) { ?>
                        <li class='mep_cart_user_email'><?php echo esc_attr( mep_get_reg_label( $event_id, 'Email' ) ) . ": ";
								echo esc_attr( $userinf['user_email'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_phone', $userinf ) && ! empty( $userinf['user_phone'] ) ) { ?>
                        <li class='mep_cart_user_phone'><?php echo esc_attr( mep_get_reg_label( $event_id, 'Phone' ) ) . ": ";
								echo esc_attr( $userinf['user_phone'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_address', $userinf ) && ! empty( $userinf['user_address'] ) ) { ?>
                        <li class='mep_cart_user_address'><?php echo esc_attr( mep_get_reg_label( $event_id, 'Address' ) ) . ": ";
								echo esc_attr( $userinf['user_address'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_gender', $userinf ) && ! empty( $userinf['user_gender'] ) ) { ?>
                        <li class='mep_cart_user_gender'><?php echo esc_attr( mep_get_reg_label( $event_id, 'Gender' ) ) . ": ";
								echo esc_attr( $userinf['user_gender'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_tshirtsize', $userinf ) && ! empty( $userinf['user_tshirtsize'] ) ) { ?>
                        <li class='mep_cart_user_tshirt'><?php echo esc_attr( mep_get_reg_label( $form_id, 'T-Shirt Size' ) ) . ": ";
								echo esc_attr( $userinf['user_tshirtsize'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_company', $userinf ) && ! empty( $userinf['user_company'] ) ) { ?>
                        <li class='mep_cart_user_company'><?php echo esc_attr( mep_get_reg_label( $event_id, 'Company' ) ) . ": ";
								echo esc_attr( $userinf['user_company'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_designation', $userinf ) && ! empty( $userinf['user_designation'] ) ) { ?>
                        <li class='mep_cart_user_designation'><?php echo esc_attr( mep_get_reg_label( $event_id, 'Designation' ) ) . ": ";
								echo esc_attr( $userinf['user_designation'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_website', $userinf ) && ! empty( $userinf['user_website'] ) ) { ?>
                        <li class='mep_cart_user_website'><?php echo esc_attr( mep_get_reg_label( $event_id, 'Website' ) ) . ": ";
								echo esc_attr( $userinf['user_website'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_vegetarian', $userinf ) && ! empty( $userinf['user_vegetarian'] ) ) { ?>
                        <li class='mep_cart_user_vegitarian'>
							<?php
								$vegetarian = strtolower( $userinf['user_vegetarian'] ) == 'yes' ? esc_html__( 'Yes', 'mage-eventpress' ) : esc_html__( 'No', 'mage-eventpress' );
								echo esc_attr( mep_get_reg_label( $event_id, 'Vegetarian' ) ) . ": ";
								echo esc_html( $vegetarian );
							?>
                        </li> <?php } ?>
					<?php if ( is_array( $custom_forms_id ) && sizeof( $custom_forms_id ) > 0 ) {
						foreach ( $custom_forms_id as $key => $value ) {
							?>
                            <li><?php
									echo esc_html( $key );
									echo ": " . esc_attr( $userinf[ $value ] );
								?>
                            </li>
							<?php
						}
					} ?>
					<?php if ( array_key_exists( 'user_ticket_type', $userinf ) && $userinf['user_ticket_type'] ) { ?>
                        <li class='mep_cart_user_ticket_type'><?php esc_html_e( 'Ticket Type', 'mage-eventpress' );
								echo ": " . esc_attr( $userinf['user_ticket_type'] ); ?></li> <?php } ?>
					<?php if ( array_key_exists( 'user_event_date', $userinf ) && $userinf['user_event_date'] ) { ?>
						<?php if ( $recurring == 'everyday' && $time_status == 'no' ) { ?>
                            <li class='mep_cart_user_date'><?php
									esc_html_e( ' Date', 'mage-eventpress' );
									echo ": "; ?><?php echo esc_attr( get_mep_datetime( $userinf['user_event_date'], 'date-text' ) ); ?></li>
						<?php } else { ?>
                            <li class='mep_cart_user_date'><?php
									esc_html_e( ' Date', 'mage-eventpress' );
									echo ": "; ?><?php echo esc_attr( get_mep_datetime( $userinf['user_event_date'], 'date-time-text' ) ); ?></li>
						<?php } ?>
					<?php } ?>
                </ul>
				<?php
			}
			return apply_filters( 'mep_display_user_info_in_cart_list', ob_get_clean(), $user_info );
		}
	}
	if ( ! function_exists( 'mep_cart_display_ticket_type_list' ) ) {
		function mep_cart_display_ticket_type_list( $ticket_type_arr, $eid ) {
			ob_start();
			foreach ( $ticket_type_arr as $ticket ) {
				echo '<li>' . esc_attr( $ticket['ticket_name'] ) . " - " . wc_price( (float) $ticket['ticket_price'] ) . ' x ' . esc_attr( $ticket['ticket_qty'] ) . ' = ' . wc_price( (float) $ticket['ticket_price'] * (float) $ticket['ticket_qty'] ) . '</li>';
			}
			return apply_filters( 'mep_display_ticket_in_cart_list', ob_get_clean(), $ticket_type_arr, $eid );
		}
	}
	if ( ! function_exists( 'mep_get_tshirts_sizes' ) ) {
		function mep_get_tshirts_sizes( $event_id ) {
			$event_meta = get_post_custom( $event_id );
			$tee_sizes  = $event_meta['mep_reg_tshirtsize_list'][0];
			$tszrray    = explode( ',', $tee_sizes );
			$ts         = "";
			foreach ( $tszrray as $value ) {
				$ts .= "<option value='$value'>$value</option>";
			}
			return $ts;
		}
	}
	if ( ! function_exists( 'mep_get_event_expire_date' ) ) {
		function mep_get_event_expire_date( $event_id ) {
			$event_expire_on_old   = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
			$event_expire_on       = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
			$event_start_datetime  = get_post_meta( $event_id, 'event_start_datetime', true );
			$event_expire_datetime = get_post_meta( $event_id, 'event_expire_datetime', true );
			$expire_date           = $event_expire_on == 'event_expire_datetime' ? $event_expire_datetime : $event_start_datetime;
			return $expire_date;
		}
	}
	if ( ! function_exists( 'mep_contains_serialized_object' ) ) {
		function mep_contains_serialized_object( $value ) {
			return is_string( $value ) && preg_match( '/^O:\d+:"[^"]+":\d+:{/', $value );
		}
	}
	if ( ! function_exists( 'mep_get_orginal_ticket_name' ) ) {
		function mep_get_orginal_ticket_name( $names ) {
			$name = [];
			foreach ( $names as $_names ) {
				// Decode HTML entities and URL encoding to handle special characters properly
				$decoded_name = html_entity_decode( urldecode( $_names ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				// Only split by underscore if it's a compound key (e.g., "name_123"), otherwise use the full name
				// Check if the name contains an underscore followed by digits (likely an index suffix)
				if ( preg_match( '/^(.+)_\d+$/', $decoded_name, $matches ) ) {
					$name[] = $matches[1];
				} else {
					$name[] = $decoded_name;
				}
			}
			return $name;
		}
	}
	add_action( 'mep_event_location_street', 'mep_event_location_street' );
	add_action( 'mep_event_location_city', 'mep_event_location_city' );
	add_action( 'mep_event_location_state', 'mep_event_location_state' );
	add_action( 'mep_event_location_postcode', 'mep_event_location_postcode' );
	add_action( 'mep_event_location_country', 'mep_event_location_country' );
	function mep_event_location_street( $event_id ) {
		$location = MPWEM_Functions::get_location( $event_id, 'street' );
		if ( $location ) {
			?><span><?php echo esc_html( $location ); ?></span><?php
		}
	}
	function mep_event_location_city( $event_id ) {
		$location = MPWEM_Functions::get_location( $event_id, 'city' );
		if ( $location ) {
			?><span><?php echo esc_html( $location ); ?></span><?php
		}
	}
	function mep_event_location_state( $event_id ) {
		$location = MPWEM_Functions::get_location( $event_id, 'state' );
		if ( $location ) {
			?><span><?php echo esc_html( $location ); ?></span><?php
		}
	}
	function mep_event_location_postcode( $event_id ) {
		$location = MPWEM_Functions::get_location( $event_id, 'zip' );
		if ( $location ) {
			?><span><?php echo esc_html( $location ); ?></span><?php
		}
	}
	function mep_event_location_country( $event_id ) {
		$location = MPWEM_Functions::get_location( $event_id, 'country' );
		if ( $location ) {
			?><span><?php echo esc_html( $location ); ?></span><?php
		}
	}
	function mep_html_chr( $string ) {
		$find    = [ '&', '#038;' ];
		$replace = [ 'and', '' ];
		return html_entity_decode( str_replace( $find, $replace, $string ) );
		// return str_replace("&","pink",'Test & Time Event');
	}
	add_action( 'mep_event_single_page_after_header', 'mep_update_event_upcoming_date' );
	if ( ! function_exists( 'mep_update_event_upcoming_date' ) ) {
		function mep_update_event_upcoming_date( $event_id ) {
			$event_id              = ! empty( $event_id ) ? $event_id : get_the_id();
			$current_upcoming_date = get_post_meta( $event_id, 'event_upcoming_datetime', true ) ? get_post_meta( $event_id, 'event_upcoming_datetime', true ) : 0;
			$event_upcoming_date   = mep_get_event_upcoming_date( $event_id );
			if ( $current_upcoming_date == 0 || $current_upcoming_date != $event_upcoming_date ) {
				update_post_meta( $event_id, 'event_upcoming_datetime', $event_upcoming_date );
			} else {
				return null;
			}
		}
	}
	// Getting event exprie date & time
	if ( ! function_exists( 'mep_get_event_status' ) ) {
		function mep_get_event_status( $startdatetime ) {
			$current   = current_time( 'Y-m-d H:i:s' );
			$newformat = date( 'Y-m-d H:i:s', strtotime( $startdatetime ) );
			$datetime1 = new DateTime( $newformat );
			$datetime2 = new DateTime( $current );
			$interval  = date_diff( $datetime2, $datetime1 );
			if ( current_time( 'Y-m-d H:i:s' ) > $newformat ) {
				return __( "<span class=err>Expired</span>", "mage-eventpress" );
			} else {
				$days    = $interval->days;
				$hours   = $interval->h;
				$minutes = $interval->i;
				if ( $days > 0 ) {
					$dd = $days . __( " days ", "mage-eventpress" );
				} else {
					$dd = "";
				}
				if ( $hours > 0 ) {
					$hh = $hours . __( " hours ", "mage-eventpress" );
				} else {
					$hh = "";
				}
				if ( $minutes > 0 ) {
					$mm = $minutes . __( " minutes ", "mage-eventpress" );
				} else {
					$mm = "";
				}
				return "<span class='active'>" . esc_html( $dd ) . " " . esc_html( $hh ) . " " . esc_html( $mm ) . "</span>";
			}
		}
	}
	add_action( 'mep_event_list_upcoming_date_li', 'mep_re_event_list_upcoming_date_li' );
	function mep_re_event_list_upcoming_date_li( $event_id ) {
		$recurring               = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		$show_end_date           = get_post_meta( $event_id, 'mep_show_end_datetime', true ) ? get_post_meta( $event_id, 'mep_show_end_datetime', true ) : 'yes';
		$end_date_display_status = apply_filters( 'mep_event_datetime_status', $show_end_date, $event_id );
		$hide_only_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'general_setting_sec', 'no' );
		// $hide_only_end_time_list = 'yes';
		if ( $recurring == 'everyday' ) {
			$time_status        = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
			$global_time_slots  = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
			$global_off_days    = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : '';
			$global_off_dates   = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
			$event_start_date   = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
			$event_end_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				//  print_r($value);
				$global_on_days_arr[] = $value->format( 'Y-m-d' );
			}
			$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
			// $event_date_display_list = mep_get_event_date($global_on_days_arr);
			$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
			$every_day               = is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? $event_date_display_list[0] : '';
			?>
            <li class="mep_list_event_date">
                <div class="evl-ico"><i class="far fa-calendar-alt"></i></div>
                <div class="evl-cc">
                    <h5>
						<?php echo is_array( $event_date_display_list ) && sizeof( $event_date_display_list ) > 0 ? get_mep_datetime( $event_date_display_list[0], 'date-text' ) : ''; ?>
                    </h5>
					<?php do_action( 'mep_event_list_loop_footer', $event_id ); ?>
                </div>
            </li>
			<?php
		} elseif ( $recurring == 'yes' ) {
			$event_start_datetime = get_post_meta( $event_id, 'event_start_datetime', true ) ? get_post_meta( $event_id, 'event_start_datetime', true ) : '';
			$event_end_datetime   = get_post_meta( $event_id, 'event_end_datetime', true ) ? get_post_meta( $event_id, 'event_end_datetime', true ) : '';
			$event_multidate      = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : '';
			//  $event_multidate        = array_key_exists('mep_event_more_date', $event_meta) ? maybe_unserialize($event_meta['mep_event_more_date'][0]) : array();
			// print_r($event_multidate);
			$event_std[] = array(
				'event_std' => $event_start_datetime,
				'event_etd' => $event_end_datetime
			);
			$a           = 1;
			if ( is_array( $event_multidate ) && sizeof( $event_multidate ) > 0 ) {
				foreach ( $event_multidate as $event_mdt ) {
					$event_std[ $a ]['event_std'] = $event_mdt['event_more_start_date'] . ' ' . $event_mdt['event_more_start_time'];
					$event_std[ $a ]['event_etd'] = $event_mdt['event_more_end_date'] . ' ' . $event_mdt['event_more_end_time'];
					$a ++;
				}
			}
			$cn = 0;
			foreach ( $event_std as $_event_std ) {
				$std        = sanitize_text_field( $_event_std['event_std'] );
				$start_date = date( 'Y-m-d', strtotime( $_event_std['event_std'] ) );
				$end_date   = date( 'Y-m-d', strtotime( $_event_std['event_etd'] ) );
				if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $std ) && $cn == 0 ) {
					?>
                    <li class="mep_list_event_date">
                        <div class="evl-ico"><i class="far fa-calendar-alt"></i></div>
                        <div class="evl-cc">
                            <h5>
								<?php echo get_mep_datetime( $std, 'date-text' ); ?>
                            </h5>
                            <h5><?php echo get_mep_datetime( $_event_std['event_std'], 'time' );
									if ( $hide_only_end_time_list == 'no' && $end_date_display_status == 'yes' ) { ?> - <?php if ( $start_date == $end_date ) {
										echo get_mep_datetime( $_event_std['event_etd'], 'time' );
									} else {
										echo get_mep_datetime( $_event_std['event_etd'], 'date-time-text' );
									}
									} ?></h5>
                        </div>
                    </li>
					<?php
					$cn ++;
				}
			}
		}
	}
	add_action( 'mep_event_list_date_li', 'mep_event_list_upcoming_date_li', 10, 2 );
	if ( ! function_exists( 'mep_event_list_upcoming_date_li' ) ) {
		function mep_event_list_upcoming_date_li( $event_id, $type = 'grid' ) {
			$event_date_icon         = mep_get_option( 'mep_event_date_icon', 'icon_setting_sec', 'far fa-calendar-alt' );
			$hide_only_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'event_list_setting_sec', 'no' );
			$event_start_datetime    = get_post_meta( $event_id, 'event_start_datetime', true );
			$event_end_datetime      = get_post_meta( $event_id, 'event_end_datetime', true );
			$event_multidate         = get_post_meta( $event_id, 'mep_event_more_date', true ) ? get_post_meta( $event_id, 'mep_event_more_date', true ) : [];
			$event_std[]             = array(
				'event_std' => $event_start_datetime,
				'event_etd' => $event_end_datetime
			);
			$a                       = 1;
			if ( is_array( $event_multidate ) && sizeof( $event_multidate ) > 0 ) {
				foreach ( $event_multidate as $event_mdt ) {
					$event_std[ $a ]['event_std'] = $event_mdt['event_more_start_date'] . ' ' . $event_mdt['event_more_start_time'];
					$event_std[ $a ]['event_etd'] = $event_mdt['event_more_end_date'] . ' ' . $event_mdt['event_more_end_time'];
					$a ++;
				}
			}
			$cn = 0;
			foreach ( $event_std as $_event_std ) {
				// print_r($_event_std);
				$std        = $_event_std['event_std'];
				$start_date = date( 'Y-m-d', strtotime( $_event_std['event_std'] ) );
				$end_date   = date( 'Y-m-d', strtotime( $_event_std['event_etd'] ) );
				if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $std ) && $cn == 0 ) {
					if ( $type == 'grid' ) {
						?>
                        <li class="mep_list_event_date">
                            <div class="evl-ico"><i class="<?php echo $event_date_icon; ?>"></i></div>
                            <div class="evl-cc">
                                <p>
									<?php echo get_mep_datetime( $std, 'date-text' ); ?>
                                </p>
                                <p><?php echo get_mep_datetime( $_event_std['event_std'], 'time' );
										if ( $hide_only_end_time_list == 'no' ) { ?> - <?php if ( $start_date == $end_date ) {
											echo get_mep_datetime( $_event_std['event_etd'], 'time' );
										} else {
											echo get_mep_datetime( $_event_std['event_etd'], 'date-time-text' );
										}
										} ?></p>
                            </div>
                        </li>
						<?php
					} elseif ( $type == 'minimal' ) {
						?>
                        <span class='mep_minimal_list_date'>
                            <i class="<?php echo $event_date_icon; ?>"></i>
                            <?php echo get_mep_datetime( $std, 'date-text' ) . ' ';
	                            echo get_mep_datetime( $_event_std['event_std'], 'time' );
	                            if ( $hide_only_end_time_list == 'no' ) { ?> - <?php if ( $start_date == $end_date ) {
		                            echo get_mep_datetime( $_event_std['event_etd'], 'time' );
	                            } else {
		                            echo get_mep_datetime( $_event_std['event_etd'], 'date-time-text' );
	                            }
	                            } ?></span>
						<?php
					}
					$cn ++;
				}
			}
		}
	}
	/**
	 * The below function will add the event more date list into the event list shortcode, Bu default it will be hide with a Show Date button, after click on that button it will the full list.
	 */
	add_action( 'mep_event_list_loop_footer', 'mep_event_recurring_date_list_in_event_list_loop' );
	if ( ! function_exists( 'mep_event_recurring_date_list_in_event_list_loop' ) ) {
		function mep_event_recurring_date_list_in_event_list_loop( $event_id ) {
			$_more_dates    = get_post_meta( $event_id, 'mep_event_more_date', true );
			$more_date      = apply_filters( 'mep_event_date_more_date_array_event_list', $_more_dates, $event_id );
			$show_multidate = mep_get_option( 'mep_date_list_in_event_listing', 'event_list_setting_sec', 'yes' );
			if ( is_array( $more_date ) && sizeof( $more_date ) > 0 ) {
				?>
				<?php if ( $show_multidate == 'yes' ) { ?>
                    <span class='mep_more_date_btn mp_event_visible_event_time'
                          data-event-id="<?php echo esc_attr( $event_id ); ?>"
                          data-active-text="<?php echo esc_attr( mep_get_option( 'mep_event_view_more_date_btn_text', 'label_setting_sec', esc_html__( 'View More Date', 'mage-eventpress' ) ) ); ?>"
                          data-hide-text="<?php echo esc_attr( mep_get_option( 'mep_event_hide_date_list_btn_text', 'label_setting_sec', __( 'Hide Date Lists', 'mage-eventpress' ) ) ); ?>">
						<?php echo mep_get_option( 'mep_event_view_more_date_btn_text', 'label_setting_sec', __( 'View More Date', 'mage-eventpress' ) ); ?>
					</span>
				<?php } ?>
				<?php
			}
		}
	}
	add_action( 'wp_ajax_mep_event_list_date_schedule', 'mep_event_list_date_schedule' );
	add_action( 'wp_ajax_nopriv_mep_event_list_date_schedule', 'mep_event_list_date_schedule' );
	if ( ! function_exists( 'mep_event_list_date_schedule' ) ) {
		function mep_event_list_date_schedule() {
			$event_id       = isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : 0;
			$recurring      = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
			$_more_dates    = get_post_meta( $event_id, 'mep_event_more_date', true );
			$more_date      = apply_filters( 'mep_event_date_more_date_array_event_list', $_more_dates, $event_id );
			$start_datetime = get_post_meta( $event_id, 'event_start_datetime', true );
			$start_date     = get_post_meta( $event_id, 'event_start_date', true );
			$end_date       = get_post_meta( $event_id, 'event_end_date', true );
			$end_datetime   = get_post_meta( $event_id, 'event_end_datetime', true );
			if ( is_array( $more_date ) && sizeof( $more_date ) > 0 ) {
				?>
                <ul class='mp_event_more_date_list'>
					<?php
						if ( $recurring == 'everyday' ) {
							do_action( 'mep_event_everyday_date_list_display', $event_id );
						} else {
							foreach ( $more_date as $_more_date ) {
								if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'] ) ) {
									?>
                                    <li>
                                        <a href="<?php echo get_the_permalink( $event_id ) . esc_attr( '?date=' . strtotime( $_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'] ) ); ?>">
											<span class='mep-more-date'>
												<i class="far fa-calendar-alt"></i>
												<?php echo get_mep_datetime( $_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'date-text' ); ?>
											</span>
                                            <span class='mep-more-time'>
												<i class="fa fa-clock-o"></i>
												<?php echo get_mep_datetime( $_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'time' ); ?> - <?php if ( $_more_date['event_more_start_date'] != $_more_date['event_more_end_date'] ) {
													echo get_mep_datetime( $_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'date-text' ) . ' - ';
												}
													echo get_mep_datetime( $_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'time' );
												?>
											</span>
                                        </a>
                                    </li>
									<?php
								}
							}
						}
					?>
                </ul>
				<?php
			}
			die();
		}
	}
	add_action( 'mep_event_everyday_date_list_display', 'mep_re_event_everyday_date_list_display' );
	function mep_re_event_everyday_date_list_display( $event_id, $type = 'display' ) {
		$time_status             = get_post_meta( $event_id, 'mep_disable_ticket_time', true ) ? get_post_meta( $event_id, 'mep_disable_ticket_time', true ) : 'no';
		$global_time_slots       = get_post_meta( $event_id, 'mep_ticket_times_global', true ) ? get_post_meta( $event_id, 'mep_ticket_times_global', true ) : [];
		$global_off_days         = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
		$global_off_dates        = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? get_post_meta( $event_id, 'mep_ticket_off_dates', true ) : '';
		$event_start_date        = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_date', true ) ) );
		$event_end_date          = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_date', true ) ) );
		$interval                = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
		$period                  = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr      = [];
		$show_end_date           = get_post_meta( $event_id, 'mep_show_end_datetime', true ) ? get_post_meta( $event_id, 'mep_show_end_datetime', true ) : 'yes';
		$end_date_display_status = apply_filters( 'mep_event_datetime_status', $show_end_date, $event_id );
		$the_recurring_dates     = [];
		foreach ( $period as $key => $value ) {
			//  print_r($value);
			$global_on_days_arr[] = $value->format( 'Y-m-d' );
		}
		$global_on_days_arr = mep_re_date_range( $event_start_date, $event_end_date, $interval );
		$global_on_days_arr = $event_start_date == $event_end_date ? array( $event_start_date ) : $global_on_days_arr;
		// code by user
		$special_dates = MPWEM_Global_Function::get_post_info( $event_id, 'mep_special_date_info', [] );
		// print_r($special_dates);
		if ( is_array( $special_dates ) ) {
			$now = strtotime( current_time( 'Y-m-d' ) );
			foreach ( $special_dates as $special_date ) {
				if ( empty( $special_date['start_date'] ) || $now > strtotime( $special_date['start_date'] ) ) {
					continue;
				}
				// Not today
				if ( $now < strtotime( $special_date['start_date'] ) ) {
					$global_on_days_arr[] = date( 'Y-m-d', strtotime( $special_date['start_date'] ) );
					continue;
				}
				// Today, check time
				if ( isset( $special_date['time'] ) && is_array( $special_date['time'] ) ) {
					foreach ( $special_date['time'] as $sd_time ) {
						if ( empty( $sd_time['mep_ticket_time'] ) ) {
							continue;
						}
						$time_str       = $special_date['start_date'] . ' ' . $sd_time['mep_ticket_time'] . ' ' . wp_timezone_string();
						$event_php_time = strtotime( $time_str );
						if ( time() < $event_php_time ) {
							$global_on_days_arr[] = date( 'Y-m-d', strtotime( $special_date['start_date'] ) );
						}
					}
				}
			}
		}
		sort( $global_on_days_arr );
		$event_date_display_list = mep_re_get_the_upcomming_date_arr( $event_id );
		foreach ( $event_date_display_list as $every_day ) {
			$event_day = strtolower( date( 'D', strtotime( $every_day ) ) );
			if ( ! in_array( $event_day, $global_off_days ) ) {
				$the_recurring_dates[] = $every_day;
				if ( $type == 'display' ) {
					if ( $time_status == 'no' ) {
						$start_date      = $every_day;
						$end_date        = $every_day;
						$start_time      = get_post_meta( $event_id, 'event_start_time', true ) ? get_post_meta( $event_id, 'event_start_time', true ) : '';
						$end_time        = get_post_meta( $event_id, 'event_end_time', true ) ? get_post_meta( $event_id, 'event_end_time', true ) : '';
						$start_datetime  = $every_day . ' ' . $start_time;
						$end_datetime    = $every_day . ' ' . $end_time;
						$theme           = get_post_meta( $event_id, 'mep_event_template', true );
						$event_date_icon = mep_get_option( 'mep_event_date_icon', 'icon_setting_sec', 'far fa-calendar-alt' ); ?>
						<?php if ( $start_date != $end_date ) : ?>
                            <li>
								<?php do_action( 'mep_single_before_event_date_list_item', $event_id, $start_datetime ); ?>
                                <div class="mep-more-date">
                                    <p class='mep_date_scdl_start_datetime'>
										<?php echo esc_html( get_mep_datetime( $start_datetime, 'date-text' ) ); ?>
										<?php echo esc_html( '-' . get_mep_datetime( $start_datetime, 'time' ) ); ?>
                                    </p>
                                    <p>
										<?php echo esc_html( get_mep_datetime( $end_datetime, 'date-text' ) ); ?>
										<?php if ( $end_date_display_status == 'yes' ) { ?>
											<?php echo esc_html( '-' . get_mep_datetime( $end_datetime, 'time' ) ); ?>
										<?php } ?>
                                    </p>
                                </div>
								<?php do_action( 'mep_single_after_event_date_list_item', $event_id, $start_datetime ); ?>
                            </li>
						<?php else: ?>
                            <li>
								<?php do_action( 'mep_single_before_event_date_list_item', $event_id, $start_datetime ); ?>
                                <div class="mep-more-date">
                                    <p class='mep_date_scdl_start_datetime'>
										<?php echo esc_html( get_mep_datetime( $start_datetime, 'date-text' ) ); ?>
                                    </p>
                                    <p>
										<?php echo esc_html( get_mep_datetime( $start_datetime, 'time' ) ); ?>
										<?php if ( $end_date_display_status == 'yes' ) { ?>
											<?php echo esc_html( '-' . get_mep_datetime( $end_datetime, 'time' ) ); ?>
										<?php } ?>
                                    </p>
                                </div>
								<?php do_action( 'mep_single_after_event_date_list_item', $event_id, $start_datetime ); ?>
                            </li>
						<?php endif;
					} elseif ( $time_status == 'yes' ) {
						?>
                        <li>
                            <a href="<?php echo get_the_permalink( $event_id ) . esc_attr( '?date=' . strtotime( $every_day ) ); ?>">
                                <span class="mep-more-date"><?php echo get_mep_datetime( $every_day, 'date-text' ); ?></span>
                                <span class='mep-more-time'>
                            <?php
	                            $calender_day = strtolower( date( 'D', strtotime( $every_day ) ) );
	                            $day_name     = 'mep_ticket_times_' . $calender_day;
	                            $time         = get_post_meta( $event_id, $day_name, true ) ? maybe_unserialize( get_post_meta( $event_id, $day_name, true ) ) : maybe_unserialize( $global_time_slots );
	                            $time_list    = [];
	                            foreach ( $time as $_time ) { ?>
                                    <span class="time"><?php echo $_time['mep_ticket_time_name'] . '( ' . get_mep_datetime( $_time['mep_ticket_time'], 'time' ) . ')'; ?></span>
	                            <?php } ?>
                        </span>
                            </a>
                        </li>
						<?php
					}
				}
			}
		}
		if ( $type == 'array' ) {
			return $the_recurring_dates;
		}
	}
	add_action( 'mep_single_before_event_date_list_item', 'mep_re_add_link_to_date_list_item', 10, 2 );
	function mep_re_add_link_to_date_list_item( $event_id, $start_datetime ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' || $recurring == 'yes' ) {
			$event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $start_datetime ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
			?>
            <a href="<?php echo esc_url( $event_url ); ?>">
			<?php
		}
	}
	add_action( 'mep_single_after_event_date_list_item', 'mep_re_add_link_to_date_list_item_after', 10, 2 );
	function mep_re_add_link_to_date_list_item_after( $event_id, $start_datetime ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' || $recurring == 'yes' ) {
			?>
            </a>
			<?php
		}
	}
	add_action( 'mep_event_tags_name', 'old_event_tags_name' );
	function old_event_tags_name() {
		global $post;
		ob_start();
		$tags  = get_the_terms( get_the_id(), 'mep_tag' );
		$names = [];
		if ( is_array( $tags ) && sizeof( $tags ) > 0 && ! is_wp_error( $tags ) ) {
			foreach ( $tags as $key => $value ) {
				$names[] = $value->name;
			}
		}
		echo esc_html( implode( ', ', $names ) );
		$content = ob_get_clean();
		echo apply_filters( 'mage_event_single_tags_name', $content, $post->ID );
	}
	add_action( 'mep_event_list_only_date_show', 'mep_event_list_only_date_show_html' );
	function mep_event_list_only_date_show_html( $event_id ) {
		$recurring = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
		if ( $recurring == 'everyday' ) {
			$now                  = current_time( 'Y-m-d H:i:s' );
			$event_start_date     = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_start_datetime', true ) ) );
			$event_end_date       = date( 'Y-m-d H:i:s', strtotime( get_post_meta( $event_id, 'event_end_datetime', true ) ) );
			$event_off_days       = get_post_meta( $event_id, 'mep_ticket_offdays', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_offdays', true ) ) : [];
			$global_off_dates     = get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_ticket_off_dates', true ) ) : [];
			$global_off_dates_arr = [];
			if ( sizeof( $global_off_dates ) > 0 ) {
				foreach ( $global_off_dates as $off_dates ) {
					$global_off_dates_arr[] = date( 'Y-m-d', strtotime( $off_dates['mep_ticket_off_date'] ) );
				}
			}
			$global_off_days_arr = [];
			if ( sizeof( $event_off_days ) > 0 ) {
				foreach ( $event_off_days as $off_days ) {
					if ( $off_days == 'sat' ) {
						$off_days = 'sat';
					}
					if ( $off_days == 'tue' ) {
						$off_days = 'tue';
					}
					if ( $off_days == 'wed' ) {
						$off_days = 'wed';
					}
					if ( $off_days == 'thu' ) {
						$off_days = 'thu';
					}
					$global_off_days_arr[] = ucwords( $off_days );
				}
			}
			$interval           = get_post_meta( $event_id, 'mep_repeated_periods', true ) ? get_post_meta( $event_id, 'mep_repeated_periods', true ) : 1;
			$period             = mep_re_get_repeted_event_period_date_arr( $event_start_date, $event_end_date, $interval );
			$global_on_days_arr = [];
			foreach ( $period as $key => $value ) {
				$the_d = date( 'D', strtotime( $value->format( 'Y-m-d' ) ) );
				if ( ! in_array( $the_d, $global_off_days_arr ) ) {
					$global_on_days_arr[] = date( 'Y-m-d H:i:s', strtotime( $value->format( 'Y-m-d H:i:s' ) ) );
				}
			}
			$fdate      = array_diff( $global_on_days_arr, $global_off_dates_arr );
			$m_date_arr = [];
			if ( sizeof( $fdate ) > 0 ) {
				$i = 0;
				foreach ( $fdate as $mdate ) {
					if ( strtotime( $now ) < strtotime( $mdate ) ) {
						$mstart                    = $mdate;
						$mend                      = $mdate;
						$m_date_arr[ $i ]['start'] = $mstart;
						$m_date_arr[ $i ]['end']   = $mend;
					}
					$i ++;
				}
			}
			$day   = get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'day' );
			$month = get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'month' );
		} else {
			$day   = get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'day' );
			$month = get_mep_datetime( get_post_meta( $event_id, 'event_upcoming_datetime', true ), 'month' );
		}
		?>
        <div class="mep-ev-start-date">
            <div class="mep-day"><?php echo mep_esc_html( $day ); ?></div>
            <div class="mep-month"><?php echo mep_esc_html( $month ); ?></div>
        </div>
		<?php
	}
	add_action( 'mep_event_map', 'mep_event_map_location', 10, 3 );
	function mep_event_map_location( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/map.php' ); }
	add_action( 'mep_add_to_cart', 'old_mep_add_to_cart', 10, 3 );
	function old_mep_add_to_cart( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/registration.php' ); }
	add_action( 'mep_event_social_share', 'old_mep_event_social_share', 10, 3 );
	function old_mep_event_social_share( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/social.php' ); }
	add_action( 'mep_event_location_ticket', 'old_mep__list_location', 10, 3 );
	add_action( 'mep_event_location', 'old_mep__list_location', 10, 3 );
	add_action( 'mep_event_address_list_sidebar', 'old_mep__list_location', 10, 3 );
	function old_mep__list_location( $event_id, $event_infos = [], $type = '' ): void { require MPWEM_Functions::template_path( 'layout/location.php' ); }
	add_action( 'mep_event_date', 'old_mep_event_date' );
	function old_mep_event_date( $event_id ) {
		$start_datetime          = get_post_meta( get_the_id(), 'event_start_datetime', true );
		$start_date              = get_post_meta( get_the_id(), 'event_start_date', true );
		$end_datetime            = get_post_meta( get_the_id(), 'event_end_datetime', true );
		$end_date                = get_post_meta( get_the_id(), 'event_end_date', true );
		$more_date               = get_post_meta( get_the_id(), 'mep_event_more_date', true ) ? maybe_unserialize( get_post_meta( get_the_id(), 'mep_event_more_date', true ) ) : [];
		$recurring               = get_post_meta( get_the_id(), 'mep_enable_recurring', true ) ? get_post_meta( get_the_id(), 'mep_enable_recurring', true ) : 'no';
		$mep_show_upcoming_event = get_post_meta( get_the_id(), 'mep_show_upcoming_event', true ) ? get_post_meta( get_the_id(), 'mep_show_upcoming_event', true ) : 'no';
		$cn                      = 1;
		if ( $recurring == 'yes' ) {
			if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $start_datetime ) ) {
				?>
                <p><?php echo get_mep_datetime( $start_datetime, 'date-text' ) . ' ' . get_mep_datetime( $start_datetime, 'time' ); ?> - <?php if ( $start_date != $end_date ) {
						echo get_mep_datetime( $end_datetime, 'date-text' ) . ' - ';
					}
						echo get_mep_datetime( $end_datetime, 'time' ); ?></p>,
				<?php
			}
			foreach ( $more_date as $_more_date ) {
				if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'] ) ) {
					if ( $mep_show_upcoming_event == 'yes' ) {
						$cnt = 1;
					} else {
						$cnt = $cn;
					}
					if ( $cn == $cnt ) {
						?>
                        <p><?php echo get_mep_datetime( $_more_date['event_more_start_date'], 'date-text' ) . ' ' . get_mep_datetime( $_more_date['event_more_start_time'], 'time' ); ?> - <?php if ( $_more_date['event_more_start_date'] != $_more_date['event_more_end_date'] ) {
								echo get_mep_datetime( $_more_date['event_more_end_date'], 'date-text' ) . ' - ';
							}
								echo get_mep_datetime( $_more_date['event_more_end_time'], 'time' ); ?></p>
						<?php
						$cn ++;
					}
				}
			}
		} elseif ( is_array( $more_date ) && sizeof( $more_date ) > 0 ) {
			?>
            <p><?php echo get_mep_datetime( $start_datetime, 'date-text' ) . ' ' . get_mep_datetime( $start_datetime, 'time' ); ?> - <?php if ( $start_date != $end_date ) {
					echo get_mep_datetime( $end_datetime, 'date-text' ) . ' - ';
				}
					echo get_mep_datetime( $end_datetime, 'time' ); ?></p>
			<?php foreach ( $more_date as $_more_date ) {
				?>
                <p><?php echo get_mep_datetime( $_more_date['event_more_start_date'], 'date-text' ) . ' ' . get_mep_datetime( $_more_date['event_more_start_time'], 'time' ); ?> - <?php if ( $_more_date['event_more_start_date'] != $_more_date['event_more_end_date'] ) {
						echo get_mep_datetime( $_more_date['event_more_end_date'], 'date-text' ) . ' - ';
					}
						echo get_mep_datetime( $_more_date['event_more_end_time'], 'time' ); ?></p>
				<?php
			}
		} else {
			?>
            <p><?php echo get_mep_datetime( $start_datetime, 'date-text' ) . ' ' . get_mep_datetime( $start_datetime, 'time' ); ?> - <?php if ( $start_date != $end_date ) {
					echo get_mep_datetime( $end_datetime, 'date-text' ) . ' - ';
				}
					echo get_mep_datetime( $end_datetime, 'time' ); ?></p>
			<?php
		}
	}
	define( 'MEP_URL', plugin_dir_url( __DIR__ ) );
	define( 'MEP_PATH', plugin_dir_path( __DIR__ ) );
	/******************** Remove upper function after 2025********************** event_start_datetime*/
	add_action( 'mpwem_expired_event_notice_after', 'mpwem_expired_event_notice_after' );
	function mpwem_expired_event_notice_after( $event_id ) {
		$start_datetime   = get_post_meta( $event_id, 'event_start_datetime', true );
		$end_date   = get_post_meta( $event_id, 'event_expire_datetime', true );
		$total_sold = MPWEM_Functions::get_total_sold( $event_id );
		$event_expire_on   = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
		$expired_datetime = $event_expire_on == 'event_start_datetime' ? $start_datetime : $end_date;
		$formatted  = MPWEM_Global_Function::date_format( $expired_datetime, 'full', $event_id );
		?>
        <div class="mpwem-expired-card">
            <div class="mpwem-expired-title">
                ❌ <?php _e( 'Event Expired', 'mage-eventpress' ); ?>
            </div>
            <div class="mpwem-expired-date">
				<?php _e( 'This event expired on', 'mage-eventpress' ); ?>
                <span class="mpwem-date-highlight">
                <?php echo esc_html( $formatted ); ?>
            </span>
            </div>
            <div class="mpwem-total-sold-badge">
                🎟 <?php _e( 'Total tickets sold', 'mage-eventpress' ); ?>:
				<?php echo esc_html( $total_sold ); ?>
            </div>
        </div>
		<?php
	}
