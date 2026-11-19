<?php
	/**
	 * Regression tests: a booking is charged for every ticket it registers.
	 *
	 * The event page posts one option_name[] / option_qty[] pair per ticket row. The price
	 * was built from the quantities cast with (int), the attendee list from the raw strings,
	 * so a row posted as 0.9 cost nothing and still registered an attendee. Seats were only
	 * checked at checkout and only per row name, and the native checkout never checked them.
	 *
	 * Each case sends the request a visitor would send, to this site, over HTTP: the classic
	 * event page form, the Store API cart and checkout, and the native checkout AJAX call.
	 *
	 * Not loaded by the plugin. Run it explicitly on a development or staging site - it
	 * places real orders (Cash on delivery) while the vulnerability is present:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/whole-ticket-quantities.php
	 *
	 * Needs WooCommerce with guest checkout and Cash on delivery (allowed for virtual orders)
	 * enabled. A "production" site is refused unless `allow-production` is passed. Every
	 * event, order, attendee, cart hold, native order and cart session the run creates is
	 * deleted afterwards, and the settings it switches are restored. Exits 1 on any failure.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! function_exists( 'WC' ) || ! class_exists( 'MPWEM_Woocommerce' ) ) {
		WP_CLI::error( 'WooCommerce and Event Booking Manager for WooCommerce must both be active.' );
	}
	$mep_wq_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_wq_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. These tests place real orders; run them on a test site, or pass allow-production to override.' );
	}

	final class MEP_WQ_Run {
		/** @var bool[] */
		public static $results = array();
		/** @var string[] */
		public static $session_keys = array();

		public static function check( $name, $pass, $detail = '' ) {
			self::$results[] = (bool) $pass;
			WP_CLI::log( sprintf( '%s  %s%s', $pass ? 'PASS' : 'FAIL', $name, '' !== $detail ? ' - ' . $detail : '' ) );
		}
	}

	/**
	 * A visitor with its own cookies (or Store API Cart-Token) across requests.
	 */
	final class MEP_WQ_Client {
		private $cookies = array();
		private $cart_token = '';
		private $nonce = '';
		private $use_token;

		public function __construct( $use_token = false ) {
			$this->use_token = $use_token;
		}

		public function send( $method, $url, $body = null, $json = false ) {
			$request = array(
				'method'      => $method,
				'timeout'     => 60,
				'redirection' => 0,
				'sslverify'   => false,
				'cookies'     => $this->cookies,
				'headers'     => array(),
			);
			if ( $this->use_token && $this->cart_token ) {
				$request['headers']['Cart-Token'] = $this->cart_token;
			}
			if ( ! $this->use_token && $this->nonce ) {
				$request['headers']['Nonce'] = $this->nonce;
			}
			if ( null !== $body ) {
				if ( $json ) {
					$request['headers']['Content-Type'] = 'application/json';
					$request['body']                    = wp_json_encode( $body );
				} else {
					$request['body'] = $body;
				}
			}
			$response = wp_remote_request( $url, $request );
			if ( is_wp_error( $response ) ) {
				WP_CLI::error( 'HTTP request to ' . $url . ' failed: ' . $response->get_error_message() );
			}
			foreach ( wp_remote_retrieve_cookies( $response ) as $cookie ) {
				if ( $cookie->expires && $cookie->expires < time() ) {
					unset( $this->cookies[ $cookie->name ] );
					continue;
				}
				$this->cookies[ $cookie->name ] = $cookie->value;
				if ( 0 === strpos( $cookie->name, 'wp_woocommerce_session_' ) ) {
					// WooCommerce 11 joins the parts with "|", older versions with "||".
					$parts                      = explode( '|', urldecode( $cookie->value ) );
					MEP_WQ_Run::$session_keys[] = $parts[0];
				}
			}
			$nonce = wp_remote_retrieve_header( $response, 'nonce' );
			if ( $nonce ) {
				$this->nonce = $nonce;
			}
			$token = wp_remote_retrieve_header( $response, 'cart-token' );
			if ( $this->use_token && $token ) {
				$this->cart_token           = $token;
				MEP_WQ_Run::$session_keys[] = mep_wq_token_customer( $token );
			}
			$raw = wp_remote_retrieve_body( $response );

			return array(
				'code' => (int) wp_remote_retrieve_response_code( $response ),
				'json' => json_decode( $raw, true ),
				'body' => $raw,
			);
		}

		public function token() {
			return $this->cart_token;
		}

		/**
		 * The cart as the Store API reports it (runs woocommerce_check_cart_items).
		 *
		 * @return array{count:int,total:float,errors:string}
		 */
		public function cart() {
			$cart  = $this->send( 'GET', mep_wq_store_url( 'cart' ) )['json'];
			$items = is_array( $cart ) && isset( $cart['items'] ) && is_array( $cart['items'] ) ? $cart['items'] : array();
			$minor = is_array( $cart ) && isset( $cart['totals']['currency_minor_unit'] ) ? (int) $cart['totals']['currency_minor_unit'] : 2;
			$total = is_array( $cart ) && isset( $cart['totals']['total_price'] ) ? (int) $cart['totals']['total_price'] : 0;

			return array(
				'count'  => count( $items ),
				'total'  => $total / pow( 10, $minor ),
				'errors' => is_array( $cart ) && ! empty( $cart['errors'] ) ? wp_json_encode( $cart['errors'] ) : '',
			);
		}
	}

	function mep_wq_store_url( $route ) {
		return add_query_arg( 'rest_route', '/wc/store/v1/' . $route, home_url( '/' ) );
	}

	function mep_wq_token_customer( $token ) {
		$parts   = explode( '.', (string) $token );
		$payload = isset( $parts[1] ) ? json_decode( base64_decode( strtr( $parts[1], '-_', '+/' ) ), true ) : null;

		return is_array( $payload ) && isset( $payload['user_id'] ) ? (string) $payload['user_id'] : '';
	}

	function mep_wq_ticket_type( $name, $price, $capacity, $extra = array() ) {
		return $extra + array(
			'option_name_t'        => $name,
			'option_price_t'       => (string) $price,
			'option_qty_t'         => (string) $capacity,
			'option_rsv_t'         => '0',
			'option_default_qty_t' => '0',
			'option_qty_t_type'    => 'inputbox',
			'option_ticket_enable' => 'yes',
		);
	}

	/**
	 * An event with its hidden helper product, the way the editor saves one.
	 *
	 * @return array{event_id:int,product_id:int,date:string}
	 */
	function mep_wq_create_event( $title, $ticket_types, $extra_services = array() ) {
		$start_ts = strtotime( current_time( 'Y-m-d H:i:s' ) ) + 30 * DAY_IN_SECONDS;
		$end_ts   = $start_ts + 2 * HOUR_IN_SECONDS;
		$event_id = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'post_title' => $title ), true );
		if ( is_wp_error( $event_id ) ) {
			WP_CLI::error( 'Could not create test event: ' . $event_id->get_error_message() );
		}
		$meta = array(
			'event_start_date'        => gmdate( 'Y-m-d', $start_ts ),
			'event_start_time'        => gmdate( 'H:i', $start_ts ),
			'event_end_date'          => gmdate( 'Y-m-d', $end_ts ),
			'event_end_time'          => gmdate( 'H:i', $end_ts ),
			'event_start_datetime'    => gmdate( 'Y-m-d H:i:s', $start_ts ),
			'event_end_datetime'      => gmdate( 'Y-m-d H:i:s', $end_ts ),
			'event_expire_datetime'   => gmdate( 'Y-m-d H:i:s', $end_ts ),
			'event_upcoming_datetime' => gmdate( 'Y-m-d H:i:s', $start_ts ),
			'mep_enable_recurring'    => 'no',
			'mep_reg_status'          => 'on',
			'mep_event_ticket_type'   => $ticket_types,
			'mep_events_extra_prices' => $extra_services,
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $event_id, $key, $value );
		}
		$product_id = (int) get_post_meta( $event_id, 'link_wc_product', true );
		if ( ( ! $product_id || 'product' !== get_post_type( $product_id ) ) && class_exists( 'MPWEM_Hidden_Product' ) ) {
			MPWEM_Hidden_Product::create_hidden_wc_product( $event_id, $title );
			$product_id = (int) get_post_meta( $event_id, 'link_wc_product', true );
		}
		if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
			wp_delete_post( $event_id, true );
			WP_CLI::error( 'The test event got no linked WooCommerce product.' );
		}
		wp_update_post( array( 'ID' => $product_id, 'post_status' => 'publish' ) );

		return array(
			'event_id'   => (int) $event_id,
			'product_id' => $product_id,
			'date'       => gmdate( 'Y-m-d H:i', $start_ts ),
		);
	}

	/**
	 * Classic event page add-to-cart post.
	 *
	 * @param array $rows   List of array( ticket name, quantity ) - a name may repeat.
	 * @param array $extras List of array( service name, quantity ).
	 */
	function mep_wq_add( $client, $fixture, $rows, $extras = array() ) {
		$body = array(
			'add-to-cart'             => $fixture['product_id'],
			'mep_event_start_date[0]' => $fixture['date'],
			'mep_event_location_cart' => '',
			'mep_same_attendee'       => 'no',
		);
		foreach ( array_values( $rows ) as $i => $row ) {
			$body[ 'option_name[' . $i . ']' ] = $row[0];
			if ( is_array( $row[1] ) ) {
				foreach ( $row[1] as $j => $v ) {
					$body[ 'option_qty[' . $i . '][' . $j . ']' ] = $v;
				}
			} else {
				$body[ 'option_qty[' . $i . ']' ] = (string) $row[1];
			}
		}
		foreach ( array_values( $extras ) as $i => $row ) {
			$body[ 'event_extra_service_name[' . $i . ']' ] = $row[0];
			$body[ 'event_extra_service_qty[' . $i . ']' ]  = (string) $row[1];
		}
		$client->send( 'POST', home_url( '/' ), $body );

		return $client->cart();
	}

	/** Attendee records of an event: ticket type => count. Fresh query: requests ran elsewhere. */
	function mep_wq_attendees( $event_id ) {
		global $wpdb;
		$rows   = $wpdb->get_col( $wpdb->prepare(
			"SELECT pm.meta_value FROM {$wpdb->posts} p
			 JOIN {$wpdb->postmeta} e ON e.post_id = p.ID AND e.meta_key = 'ea_event_id' AND e.meta_value = %s
			 JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'ea_ticket_type'
			 WHERE p.post_type = 'mep_events_attendees' AND p.post_status <> 'trash'",
			(string) $event_id
		) );
		$counts = array();
		foreach ( $rows as $type ) {
			$counts[ $type ] = ( isset( $counts[ $type ] ) ? $counts[ $type ] : 0 ) + 1;
		}
		ksort( $counts );

		return $counts;
	}

	function mep_wq_describe( $counts ) {
		$out = array();
		foreach ( $counts as $type => $n ) {
			$out[] = $type . ' x' . $n;
		}

		return $out ? implode( ', ', $out ) : 'none';
	}

	/** The event lines stored in a WooCommerce session, read straight from the table. */
	function mep_wq_session_lines( $session_key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM {$wpdb->prefix}woocommerce_sessions WHERE session_key = %s", $session_key ) );
		$data  = $value ? maybe_unserialize( $value ) : array();
		$cart  = is_array( $data ) && isset( $data['cart'] ) ? maybe_unserialize( $data['cart'] ) : array();

		return is_array( $cart ) ? array_values( $cart ) : array();
	}

	function mep_wq_checkout_body() {
		$address = array(
			'first_name' => 'Whole',
			'last_name'  => 'Quantity',
			'address_1'  => '1 Main St',
			'city'       => 'San Francisco',
			'state'      => 'CA',
			'postcode'   => '94105',
			'country'    => 'US',
			'phone'      => '5550100',
		);

		return array(
			'billing_address'  => $address + array( 'email' => 'whole-quantity-regression@example.com' ),
			'shipping_address' => $address,
			'payment_method'   => 'cod',
		);
	}

	/**
	 * Seeds a cart line into a session - the state of a cart saved before the fix.
	 */
	function mep_wq_seed_session( $customer_id, $fixture, $ticket_info, $user_info, $event_tp ) {
		global $wpdb;
		$product = wc_get_product( $fixture['product_id'] );
		$key     = md5( 'mep-wq-seeded-' . $fixture['product_id'] );
		$cart    = array(
			$key => array(
				'key'                 => $key,
				'product_id'          => $fixture['product_id'],
				'variation_id'        => 0,
				'variation'           => array(),
				'quantity'            => 1,
				'data_hash'           => wc_get_cart_item_data_hash( $product ),
				'line_tax_data'       => array( 'subtotal' => array(), 'total' => array() ),
				'line_subtotal'       => $event_tp,
				'line_subtotal_tax'   => 0,
				'line_total'          => $event_tp,
				'line_tax'            => 0,
				'event_ticket_info'   => $ticket_info,
				'event_user_info'     => $user_info,
				'event_tp'            => $event_tp,
				'event_extra_service' => array(),
				'event_cart_location' => '',
				'event_cart_date'     => $fixture['date'],
				'event_id'            => $fixture['event_id'],
			),
		);
		$wpdb->replace(
			$wpdb->prefix . 'woocommerce_sessions',
			array(
				'session_key'    => $customer_id,
				'session_value'  => maybe_serialize( array( 'cart' => maybe_serialize( $cart ) ) ),
				'session_expiry' => time() + DAY_IN_SECONDS,
			)
		);
		wp_cache_delete( WC_Cache_Helper::get_cache_prefix( WC_SESSION_CACHE_GROUP ) . $customer_id, WC_SESSION_CACHE_GROUP );
	}

	/** Native checkout AJAX call as the registration modal sends it. */
	function mep_wq_native( $fixture, $tickets, $date = null, $method = '' ) {
		$client = new MEP_WQ_Client();
		$res    = $client->send( 'POST', admin_url( 'admin-ajax.php' ), array(
			'action'          => 'mep_native_checkout',
			'nonce'           => wp_create_nonce( 'mep_native_checkout_nonce' ),
			'event_id'        => $fixture['event_id'],
			'event_date'      => null === $date ? $fixture['date'] : $date,
			'billing_name'    => 'Native Guest',
			'billing_email'   => 'whole-quantity-native@example.com',
			'billing_phone'   => '5550100',
			'ticket_data'     => wp_json_encode( $tickets ),
			'attendee_fields' => '',
			'payment_method'  => $method,
		) );

		return array(
			'success' => is_array( $res['json'] ) && ! empty( $res['json']['success'] ),
			'message' => is_array( $res['json'] ) && isset( $res['json']['data']['message'] ) ? (string) $res['json']['data']['message'] : substr( $res['body'], 0, 160 ),
		);
	}

	function mep_wq_orders( $product_ids, $since ) {
		$found = array();
		foreach ( wc_get_orders( array( 'limit' => -1, 'date_created' => '>=' . $since, 'type' => 'shop_order' ) ) as $order ) {
			foreach ( $order->get_items() as $item ) {
				if ( in_array( (int) $item->get_product_id(), $product_ids, true ) ) {
					$found[ $order->get_id() ] = $order;
				}
			}
		}

		return $found;
	}

	function mep_wq_cleanup( $fixtures, $since ) {
		global $wpdb;
		$product_ids = array_map( 'intval', wp_list_pluck( $fixtures, 'product_id' ) );
		foreach ( mep_wq_orders( $product_ids, $since ) as $order ) {
			$order->delete( true );
		}
		foreach ( $fixtures as $fixture ) {
			foreach ( array( 'mep_temp_attendee' => 'event_id', 'mep_events_attendees' => 'ea_event_id', 'mep_extra_service' => 'ea_extra_service_event', 'mep_custom_order' => '_mep_event_id', 'mep_rsvp_responses' => 'ea_event_id' ) as $post_type => $meta_key ) {
				$ids = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => $meta_key, 'meta_value' => $fixture['event_id'] ) );
				foreach ( $ids as $id ) {
					wp_delete_post( $id, true );
				}
			}
			wp_delete_post( $fixture['product_id'], true );
			wp_delete_post( $fixture['event_id'], true );
		}
		foreach ( array_unique( array_filter( MEP_WQ_Run::$session_keys ) ) as $session_key ) {
			$wpdb->delete( $wpdb->prefix . 'woocommerce_sessions', array( 'session_key' => $session_key ) );
		}
	}

	/* ------------------------------------------------------------------------------------ */

	$mep_wq_since       = time() - MINUTE_IN_SECONDS;
	$mep_wq_fixtures    = array();
	$mep_wq_payment_opt = get_option( 'payment_setting_sec', null );
	$mep_wq_general_opt = get_option( 'general_setting_sec', null );

	try {
		// Prices as in the report: Cheap 1.00, VIP 100.00.
		$attack = $mep_wq_fixtures[] = mep_wq_create_event( 'WQ regression - reported', array(
			mep_wq_ticket_type( 'Cheap', 1, 50 ),
			mep_wq_ticket_type( 'VIP', 100, 50 ),
			mep_wq_ticket_type( 'Staff Comp', 0, 50, array( 'option_ticket_enable' => 'no' ) ),
		), array(
			array( 'option_name' => 'Lunch', 'option_price' => '10', 'option_qty' => '5', 'option_qty_type' => 'inputbox' ),
		) );
		$seats  = $mep_wq_fixtures[] = mep_wq_create_event( 'WQ regression - seats', array(
			mep_wq_ticket_type( 'Cheap', 1, 50 ),
			mep_wq_ticket_type( 'VIP', 100, 3 ),
		) );
		$native = $mep_wq_fixtures[] = mep_wq_create_event( 'WQ regression - native', array(
			mep_wq_ticket_type( 'Free', 0, 3 ),
			mep_wq_ticket_type( 'Staff Comp', 0, 50, array( 'option_ticket_enable' => 'no' ) ),
			mep_wq_ticket_type( 'Paid', 5, 10 ),
		) );
		WP_CLI::log( sprintf( 'Fixtures: reported #%d, seats #%d, native #%d', $attack['event_id'], $seats['event_id'], $native['event_id'] ) );

		// --- The reported request --------------------------------------------------------

		$client = new MEP_WQ_Client();
		$cart   = mep_wq_add( $client, $attack, array( array( 'Cheap', 1 ), array( 'VIP', '0.9' ), array( 'VIP', '0.9' ) ) );
		MEP_WQ_Run::check( 'reported request (Cheap 1, VIP 0.9, VIP 0.9) is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );
		$res = $client->send( 'POST', mep_wq_store_url( 'checkout' ), mep_wq_checkout_body(), true );
		MEP_WQ_Run::check( 'and its checkout places no order and registers no one', 0 === count( mep_wq_orders( array( $attack['product_id'] ), $mep_wq_since ) ) && array() === mep_wq_attendees( $attack['event_id'] ), sprintf( 'HTTP %d, attendees: %s', $res['code'], mep_wq_describe( mep_wq_attendees( $attack['event_id'] ) ) ) );

		foreach ( array( '1.5', '-1', '1e1', '0x1', '+1', '1.0', '١', array( '1' ) ) as $bad ) {
			$cart = mep_wq_add( new MEP_WQ_Client(), $attack, array( array( 'Cheap', 1 ), array( 'VIP', $bad ) ) );
			MEP_WQ_Run::check( sprintf( 'quantity %s is refused', wp_json_encode( $bad ) ), 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );
		}

		// --- The control from the report: whole numbers are charged in full --------------

		$client = new MEP_WQ_Client();
		$cart   = mep_wq_add( $client, $attack, array( array( 'Cheap', 1 ), array( 'VIP', 1 ), array( 'VIP', 1 ) ) );
		MEP_WQ_Run::check( 'control (Cheap 1, VIP 1, VIP 1) costs 201.00', 1 === $cart['count'] && abs( $cart['total'] - 201 ) < 0.001, sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );
		$res      = $client->send( 'POST', mep_wq_store_url( 'checkout' ), mep_wq_checkout_body(), true );
		$order_id = is_array( $res['json'] ) && isset( $res['json']['order_id'] ) ? (int) $res['json']['order_id'] : 0;
		$order    = $order_id ? wc_get_order( $order_id ) : false;
		MEP_WQ_Run::check( 'control checks out at 201.00 with Cheap x1, VIP x2 registered', $order && abs( (float) $order->get_total() - 201 ) < 0.001 && array( 'Cheap' => 1, 'VIP' => 2 ) === mep_wq_attendees( $attack['event_id'] ), sprintf( 'HTTP %d, order %s, total %s, attendees: %s', $res['code'], $order_id ?: '-', $order ? $order->get_total() : '-', mep_wq_describe( mep_wq_attendees( $attack['event_id'] ) ) ) );

		// --- Ticket types the page does not offer ----------------------------------------

		$cart = mep_wq_add( new MEP_WQ_Client(), $attack, array( array( 'Staff Comp', 1 ) ) );
		MEP_WQ_Run::check( 'a switched-off ticket type is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		// --- The posted date ----------------------------------------------------------------

		$dated     = $attack;
		$dated['date'] = gmdate( 'Y-m-d H:i', strtotime( $attack['date'] ) + DAY_IN_SECONDS );
		$cart          = mep_wq_add( new MEP_WQ_Client(), $dated, array( array( 'Cheap', 1 ) ) );
		MEP_WQ_Run::check( 'a day the event does not run is refused', 0 === $cart['count'], sprintf( '%d line(s)', $cart['count'] ) );
		$dated['date'] = gmdate( 'Y-m-d H:i', strtotime( $attack['date'] ) + MINUTE_IN_SECONDS );
		$cart          = mep_wq_add( new MEP_WQ_Client(), $dated, array( array( 'Cheap', 1 ) ) );
		MEP_WQ_Run::check( 'a start time the day does not have is refused', 0 === $cart['count'], sprintf( '%d line(s)', $cart['count'] ) );
		$dated['date'] = str_replace( ' ', 'T', $attack['date'] );
		$client        = new MEP_WQ_Client();
		$cart          = mep_wq_add( $client, $dated, array( array( 'Cheap', 1 ) ) );
		$lines         = mep_wq_session_lines( end( MEP_WQ_Run::$session_keys ) );
		$stored        = $lines && isset( $lines[0]['event_cart_date'] ) ? $lines[0]['event_cart_date'] : '';
		MEP_WQ_Run::check( 'a "T" date is booked in the canonical spelling', 1 === $cart['count'] && $attack['date'] === $stored, 'stored ' . $stored );

		// --- Extra services ----------------------------------------------------------------

		$cart = mep_wq_add( new MEP_WQ_Client(), $attack, array( array( 'Cheap', 1 ) ), array( array( 'Lunch', '0.5' ) ) );
		MEP_WQ_Run::check( 'extra service quantity 0.5 is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );
		$cart = mep_wq_add( new MEP_WQ_Client(), $attack, array( array( 'Cheap', 1 ) ), array( array( 'Lunch', 2 ) ) );
		MEP_WQ_Run::check( 'extra service quantity 2 is charged (21.00)', 1 === $cart['count'] && abs( $cart['total'] - 21 ) < 0.001, sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );
		$cart = mep_wq_add( new MEP_WQ_Client(), $attack, array( array( 'Cheap', 1 ) ), array( array( 'Lunch', 3 ), array( 'Lunch', 3 ) ) );
		MEP_WQ_Run::check( 'extra service rows summed past its stock (3 + 3 of 5) are refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		// --- Seats are checked when the tickets go into the cart -----------------------------

		$cart = mep_wq_add( new MEP_WQ_Client(), $seats, array( array( 'VIP', 4 ) ) );
		MEP_WQ_Run::check( 'VIP 4 of 3 seats is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );
		$cart = mep_wq_add( new MEP_WQ_Client(), $seats, array( array( 'VIP', 2 ), array( 'VIP', 2 ) ) );
		MEP_WQ_Run::check( 'VIP rows summed past the seats (2 + 2 of 3) are refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );
		$cart = mep_wq_add( new MEP_WQ_Client(), $seats, array( array( 'VIP_x', 4 ) ) );
		MEP_WQ_Run::check( 'a suffixed name (VIP_x 4 of 3) is checked as VIP and refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );
		$client = new MEP_WQ_Client();
		$cart   = mep_wq_add( $client, $seats, array( array( 'VIP_x', 1 ), array( 'VIP', 2 ) ) );
		$lines  = mep_wq_session_lines( end( MEP_WQ_Run::$session_keys ) );
		$names  = $lines ? array_values( array_unique( wp_list_pluck( (array) $lines[0]['event_ticket_info'], 'ticket_name' ) ) ) : array();
		MEP_WQ_Run::check( 'VIP_x 1 + VIP 2 of 3 is accepted and stored as the VIP ticket type', 1 === $cart['count'] && abs( $cart['total'] - 300 ) < 0.001 && array( 'VIP' ) === $names, sprintf( '%d line(s), total %.2f, stored names %s', $cart['count'], $cart['total'], wp_json_encode( $names ) ) );

		// --- Ticket types without a seat limit still have a per-booking ceiling -----------

		update_option( 'general_setting_sec', array_merge( (array) $mep_wq_general_opt, array( 'mep_enable_unlimited_ticket' => 'yes' ) ) );
		$unlimited = $mep_wq_fixtures[] = mep_wq_create_event( 'WQ regression - unlimited', array(
			mep_wq_ticket_type( 'Open', 0, 0, array( 'option_qty_t_unlimited' => 'yes' ) ),
		) );
		$cart = mep_wq_add( new MEP_WQ_Client(), $unlimited, array( array( 'Open', MPWEM_Functions::MAX_TICKETS_PER_BOOKING + 1 ) ) );
		MEP_WQ_Run::check( sprintf( 'an unlimited ticket type refuses %d tickets in one booking', MPWEM_Functions::MAX_TICKETS_PER_BOOKING + 1 ), 0 === $cart['count'], sprintf( '%d line(s)', $cart['count'] ) );
		$cart = mep_wq_add( new MEP_WQ_Client(), $unlimited, array( array( 'Open', 25 ) ) );
		MEP_WQ_Run::check( 'an unlimited ticket type accepts 25', 1 === $cart['count'], sprintf( '%d line(s)', $cart['count'] ) );
		if ( null === $mep_wq_general_opt ) {
			delete_option( 'general_setting_sec' );
		} else {
			update_option( 'general_setting_sec', $mep_wq_general_opt );
		}

		// --- A cart saved before the fix registers only what it paid for -----------------

		$client = new MEP_WQ_Client( true );
		$client->send( 'GET', mep_wq_store_url( 'cart' ) );
		$attendee = function ( $type, $price ) use ( $seats ) {
			return array( 'user_ticket_type' => $type, 'ticket_name' => $type, 'user_ticket_qty' => 1, 'ticket_qty' => 1, 'ticket_price' => $price, 'user_event_date' => $seats['date'], 'user_event_id' => $seats['event_id'] );
		};
		mep_wq_seed_session(
			mep_wq_token_customer( $client->token() ),
			$seats,
			array( array( 'ticket_name' => 'Cheap', 'ticket_price' => 1, 'ticket_qty' => 1, 'max_qty' => 0, 'event_date' => $seats['date'], 'event_id' => $seats['event_id'] ) ),
			array( $attendee( 'Cheap', 1 ), $attendee( 'VIP', 100 ), $attendee( 'VIP', 100 ) ),
			1
		);
		$res      = $client->send( 'POST', mep_wq_store_url( 'checkout' ), mep_wq_checkout_body(), true );
		$order_id = is_array( $res['json'] ) && isset( $res['json']['order_id'] ) ? (int) $res['json']['order_id'] : 0;
		$order    = $order_id ? wc_get_order( $order_id ) : false;
		MEP_WQ_Run::check( 'a saved cart paying for Cheap x1 but listing VIP attendees registers Cheap x1 only', $order && abs( (float) $order->get_total() - 1 ) < 0.001 && array( 'Cheap' => 1 ) === mep_wq_attendees( $seats['event_id'] ), sprintf( 'HTTP %d, order %s, total %s, attendees: %s', $res['code'], $order_id ?: '-', $order ? $order->get_total() : '-', mep_wq_describe( mep_wq_attendees( $seats['event_id'] ) ) ) );

		// --- Native checkout (WooCommerce payment switched off) --------------------------

		update_option( 'payment_setting_sec', array_merge( (array) $mep_wq_payment_opt, array( 'mep_enable_wc_payment' => 'off' ) ) );
		foreach ( array( '0.9', '-3', 1.5 ) as $bad ) {
			$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Free', 'ticket_qty' => $bad ) ) );
			MEP_WQ_Run::check( sprintf( 'native checkout refuses quantity %s', wp_json_encode( $bad ) ), ! $res['success'] && array() === mep_wq_attendees( $native['event_id'] ), $res['message'] );
		}
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Staff Comp', 'ticket_qty' => 1 ) ) );
		MEP_WQ_Run::check( 'native checkout refuses a switched-off ticket type', ! $res['success'] && array() === mep_wq_attendees( $native['event_id'] ), $res['message'] );
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Free', 'ticket_qty' => 2 ), array( 'ticket_name' => 'Free', 'ticket_qty' => 2 ) ) );
		MEP_WQ_Run::check( 'native checkout refuses rows summed past the seats (2 + 2 of 3)', ! $res['success'] && array() === mep_wq_attendees( $native['event_id'] ), $res['message'] );
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Free', 'ticket_qty' => 2 ) ) );
		MEP_WQ_Run::check( 'native checkout books 2 of 3 free seats', $res['success'] && array( 'Free' => 2 ) === mep_wq_attendees( $native['event_id'] ), $res['message'] . ' attendees: ' . mep_wq_describe( mep_wq_attendees( $native['event_id'] ) ) );
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Free', 'ticket_qty' => 2 ) ) );
		MEP_WQ_Run::check( 'native checkout then refuses 2 more (1 left)', ! $res['success'] && array( 'Free' => 2 ) === mep_wq_attendees( $native['event_id'] ), $res['message'] );
		$t_date = str_replace( ' ', 'T', $native['date'] );
		$res    = mep_wq_native( $native, array( array( 'ticket_name' => 'Free', 'ticket_qty' => 2 ) ), $t_date );
		MEP_WQ_Run::check( 'native checkout counts a "T" date against the same seats (2 more refused)', ! $res['success'] && array( 'Free' => 2 ) === mep_wq_attendees( $native['event_id'] ), $res['message'] );
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Free', 'ticket_qty' => 1 ) ), gmdate( 'Y-m-d H:i', strtotime( $native['date'] ) + DAY_IN_SECONDS ) );
		MEP_WQ_Run::check( 'native checkout refuses a day the event does not run', ! $res['success'] && array( 'Free' => 2 ) === mep_wq_attendees( $native['event_id'] ), $res['message'] );
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Free', 'ticket_qty' => 1 ) ), gmdate( 'Y-m-d H:i', strtotime( $native['date'] ) + MINUTE_IN_SECONDS ) );
		MEP_WQ_Run::check( 'native checkout refuses a start time the day does not have', ! $res['success'] && array( 'Free' => 2 ) === mep_wq_attendees( $native['event_id'] ), $res['message'] );
		update_option( 'payment_setting_sec', array_merge( (array) $mep_wq_payment_opt, array( 'mep_enable_wc_payment' => 'off', 'mep_offline_enable' => 'off', 'mep_stripe_enable' => 'on' ) ) );
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Paid', 'ticket_qty' => 1 ) ), null, 'bogus' );
		$orphans = get_posts( array( 'post_type' => 'mep_custom_order', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_mep_order_total', 'meta_value' => '5' ) );
		MEP_WQ_Run::check( 'with offline payment off, an unknown method is refused and leaves no order', ! $res['success'] && false !== strpos( $res['message'], 'payment methods offered' ) && array() === $orphans && ! isset( mep_wq_attendees( $native['event_id'] )['Paid'] ), $res['message'] );
		update_option( 'payment_setting_sec', array_merge( (array) $mep_wq_payment_opt, array( 'mep_enable_wc_payment' => 'off', 'mep_offline_enable' => 'on' ) ) );
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Paid', 'ticket_qty' => 1 ) ), null, 'offline' );
		MEP_WQ_Run::check( 'with offline payment on, a paid booking is registered for payment later', $res['success'] && isset( mep_wq_attendees( $native['event_id'] )['Paid'] ), $res['message'] );
		update_post_meta( $native['event_id'], 'mep_reg_status', 'rsvp' );
		$res = mep_wq_native( $native, array( array( 'ticket_name' => 'Free', 'ticket_qty' => 1 ) ) );
		MEP_WQ_Run::check( 'native checkout refuses an RSVP-mode event', ! $res['success'] && false !== strpos( $res['message'], 'not available for booking' ), $res['message'] );

		// --- RSVP dates ---------------------------------------------------------------------
		wp_set_current_user( 0 );
		$rsvp_nonce = wp_create_nonce( 'mep_rsvp_nonce_' . $native['event_id'] );
		$rsvp       = function ( $date, $email ) use ( $native, $rsvp_nonce ) {
			$client = new MEP_WQ_Client();
			$res    = $client->send( 'POST', admin_url( 'admin-ajax.php' ), array( 'action' => 'mep_submit_rsvp', 'nonce' => $rsvp_nonce, 'event_id' => $native['event_id'], 'rsvp_name' => 'RSVP Guest', 'rsvp_email' => $email, 'rsvp_phone' => '5550100', 'rsvp_date' => $date, 'rsvp_qty' => 1 ) );

			return is_array( $res['json'] ) && ! empty( $res['json']['success'] );
		};
		$rsvp_dates = function () use ( $native ) {
			global $wpdb;

			return $wpdb->get_col( $wpdb->prepare( "SELECT pm.meta_value FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} e ON e.post_id = p.ID AND e.meta_key = 'ea_event_id' AND e.meta_value = %s JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'ea_event_date' WHERE p.post_type = 'mep_rsvp_responses'", (string) $native['event_id'] ) );
		};
		$ok_fake = $rsvp( gmdate( 'Y-m-d H:i', strtotime( $native['date'] ) + DAY_IN_SECONDS ), 'rsvp-fake@example.com' );
		MEP_WQ_Run::check( 'RSVP refuses a day the event does not run', ! $ok_fake && array() === $rsvp_dates(), wp_json_encode( $rsvp_dates() ) );
		$ok_t = $rsvp( str_replace( ' ', 'T', $native['date'] ), 'rsvp-t@example.com' );
		MEP_WQ_Run::check( 'RSVP stores a "T" date in the canonical spelling', $ok_t && array( $native['date'] ) === $rsvp_dates(), wp_json_encode( $rsvp_dates() ) );
	} finally {
		if ( null === $mep_wq_payment_opt ) {
			delete_option( 'payment_setting_sec' );
		} else {
			update_option( 'payment_setting_sec', $mep_wq_payment_opt );
		}
		if ( null === $mep_wq_general_opt ) {
			delete_option( 'general_setting_sec' );
		} else {
			update_option( 'general_setting_sec', $mep_wq_general_opt );
		}
		mep_wq_cleanup( $mep_wq_fixtures, $mep_wq_since );
	}

	$mep_wq_failed = count( array_filter( MEP_WQ_Run::$results, function ( $pass ) {
		return ! $pass;
	} ) );
	WP_CLI::log( sprintf( '%d checks, %d passed, %d failed.', count( MEP_WQ_Run::$results ), count( MEP_WQ_Run::$results ) - $mep_wq_failed, $mep_wq_failed ) );
	if ( $mep_wq_failed ) {
		WP_CLI::halt( 1 );
	}
