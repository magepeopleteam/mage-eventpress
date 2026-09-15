<?php
	/**
	 * Regression tests: an event's helper product must never reach the cart - or an order -
	 * without a real, server-validated ticket selection.
	 *
	 * Every event owns a hidden WooCommerce product priced 0.01. The amount charged is
	 * written onto the cart line from the tickets the visitor selected, which were only
	 * ever read from $_POST. So any add-to-cart that carried no ticket fields produced a
	 * 0.00 line that checked out as a free order: a bare ?add-to-cart=<product>, a Store API
	 * add-item (a JSON body never populates $_POST), or a plugin calling
	 * WC()->cart->add_to_cart() directly - which on WooCommerce 11 skips
	 * woocommerce_add_to_cart_validation altogether. It worked for draft and expired events
	 * too, because the helper product stays published whatever the event's state.
	 *
	 * Nothing here calls a validator directly. Each case sends the request a visitor would
	 * send, to this site, over HTTP, and reads the resulting cart back through the Store API.
	 * A passing run therefore proves the guards sit on the paths that are actually taken.
	 *
	 * Not loaded by the plugin. Run it explicitly, on a development or staging site - it
	 * places real orders while the vulnerability is present:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/zero-price-add-to-cart.php
	 *
	 * A site whose environment type is "production" is refused unless `allow-production` is
	 * passed as an argument. The run creates its own events and deletes them afterwards,
	 * together with every order, attendee, cart hold and cart session they produced.
	 * Exits with status 1 when any case fails.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! function_exists( 'WC' ) || ! class_exists( 'MPWEM_Woocommerce' ) ) {
		WP_CLI::error( 'WooCommerce and Event Booking Manager for WooCommerce must both be active.' );
	}
	$mep_zp_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_zp_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. These tests place real orders; run them on a test site, or pass allow-production to override.' );
	}

	/**
	 * Collects results and the cart sessions every client opened, for the summary and cleanup.
	 */
	final class MEP_ZP_Run {
		/** @var array<int,bool> */
		public static $results = array();
		/** @var array<int,string> */
		public static $session_keys = array();
		/** @var array<int,int> */
		public static $user_ids = array();

		public static function check( $name, $pass, $detail = '' ) {
			self::$results[] = (bool) $pass;
			WP_CLI::log( sprintf( '%s  %s%s', $pass ? 'PASS' : 'FAIL', $name, $detail ? ' - ' . $detail : '' ) );
		}
	}

	/**
	 * A visitor: keeps its own cookies, or its own Store API Cart-Token, across requests.
	 */
	final class MEP_ZP_Client {
		private $cookies = array();
		private $cart_token = '';
		private $nonce = '';
		private $use_token;

		/**
		 * @param bool $use_token True to identify the cart by Cart-Token (Store API), false for cookies.
		 */
		public function __construct( $use_token = false ) {
			$this->use_token = $use_token;
		}

		/**
		 * @param string            $method HTTP method.
		 * @param string            $url    Absolute URL.
		 * @param array|null        $body   Request body.
		 * @param bool              $json   Send the body as JSON instead of form fields.
		 * @return array{code:int,json:mixed,body:string}
		 */
		public function send( $method, $url, $body = null, $json = false ) {
			$request = array(
				'method'      => $method,
				'timeout'     => 45,
				'redirection' => 0, // The session cookie is set on the redirecting response itself.
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
					$parts                        = explode( '||', urldecode( $cookie->value ) );
					MEP_ZP_Run::$session_keys[] = $parts[0];
				}
			}
			$nonce = wp_remote_retrieve_header( $response, 'nonce' );
			if ( $nonce ) {
				$this->nonce = $nonce;
			}
			$token = wp_remote_retrieve_header( $response, 'cart-token' );
			if ( $this->use_token && $token ) {
				$this->cart_token             = $token;
				MEP_ZP_Run::$session_keys[] = mep_zp_token_customer( $token );
			}
			$raw = wp_remote_retrieve_body( $response );

			return array(
				'code' => (int) wp_remote_retrieve_response_code( $response ),
				'json' => json_decode( $raw, true ),
				'body' => $raw,
			);
		}

		/** @return string */
		public function token() {
			return $this->cart_token;
		}

		public function set_cookie( $name, $value ) {
			$this->cookies[ $name ] = $value;
		}

		/**
		 * The visitor's cart as the Store API reports it.
		 *
		 * Reading it runs the Store API's cart validation, woocommerce_check_cart_items included.
		 *
		 * @return array{count:int,total:float,errors:string}
		 */
		public function cart() {
			$cart  = $this->send( 'GET', mep_zp_store_url( 'cart' ) )['json'];
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

	/** A 4xx: the request was refused on purpose, not by an unhandled error. */
	function mep_zp_is_client_error( $res ) {
		return $res['code'] >= 400 && $res['code'] < 500;
	}

	function mep_zp_error_code( $res ) {
		return is_array( $res['json'] ) && isset( $res['json']['code'] ) ? (string) $res['json']['code'] : '';
	}

	function mep_zp_store_url( $route ) {
		return add_query_arg( 'rest_route', '/wc/store/v1/' . $route, home_url( '/' ) );
	}

	/** The session key a Store API Cart-Token belongs to. */
	function mep_zp_token_customer( $token ) {
		$parts   = explode( '.', (string) $token );
		$payload = isset( $parts[1] ) ? json_decode( base64_decode( strtr( $parts[1], '-_', '+/' ) ), true ) : null;

		return is_array( $payload ) && isset( $payload['user_id'] ) ? (string) $payload['user_id'] : '';
	}

	/**
	 * Creates an event with its hidden helper product, the way the editor does.
	 *
	 * @param string $title    Event title.
	 * @param int    $start_ts Start as a site-local timestamp.
	 * @param string $status   Event status to leave it in.
	 * @return array{event_id:int,product_id:int,date:string}
	 */
	function mep_zp_create_event( $title, $start_ts, $status = 'publish' ) {
		$event_id = wp_insert_post( array(
			'post_type'   => 'mep_events',
			'post_status' => 'publish',
			'post_title'  => $title,
		), true );
		if ( is_wp_error( $event_id ) ) {
			WP_CLI::error( 'Could not create test event: ' . $event_id->get_error_message() );
		}
		$end_ts = $start_ts + 2 * HOUR_IN_SECONDS;
		$meta   = array(
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
			'mep_event_ticket_type'   => array(
				mep_zp_ticket_type( 'Adult', 25 ),
				mep_zp_ticket_type( 'Community Pass', 0 ),
				// A switched-off complimentary ticket: never offered on the event page.
				mep_zp_ticket_type( 'Staff Comp', 0, 'no' ),
			),
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
		if ( 'publish' !== $status ) {
			wp_update_post( array( 'ID' => $event_id, 'post_status' => $status ) );
		}
		// The helper product stays published whatever happens to its event - that is the
		// state the reported orders were placed against.
		wp_update_post( array( 'ID' => $product_id, 'post_status' => 'publish' ) );

		return array(
			'event_id'   => (int) $event_id,
			'product_id' => $product_id,
			'date'       => gmdate( 'Y-m-d H:i', $start_ts ),
		);
	}

	function mep_zp_ticket_type( $name, $price, $enabled = 'yes' ) {
		return array(
			'option_name_t'        => $name,
			'option_price_t'       => (string) $price,
			'option_qty_t'         => '50',
			'option_rsv_t'         => '0',
			'option_default_qty_t' => '0',
			'option_qty_t_type'    => 'inputbox',
			'option_ticket_enable' => $enabled,
		);
	}

	/**
	 * The fields the event page's booking form posts.
	 *
	 * @param array $fixture Event fixture.
	 * @param array $tickets Ticket name => quantity.
	 * @return array
	 */
	function mep_zp_ticket_fields( $fixture, $tickets ) {
		$fields = array(
			'mep_event_start_date' => array( $fixture['date'] ),
			'option_name'          => array(),
			'option_qty'           => array(),
		);
		foreach ( $tickets as $name => $qty ) {
			$fields['option_name'][] = $name;
			$fields['option_qty'][]  = (string) $qty;
		}

		return $fields;
	}

	/**
	 * Classic add-to-cart form post, as the event page submits it.
	 *
	 * @return array{count:int,total:float}
	 */
	function mep_zp_classic_post( $fixture, $tickets ) {
		$client = new MEP_ZP_Client();
		$body   = array( 'add-to-cart' => $fixture['product_id'] ) + mep_zp_ticket_fields( $fixture, $tickets );
		$client->send( 'POST', home_url( '/' ), $body );

		return $client->cart();
	}

	/**
	 * Cart holds (mep_temp_attendee) stored for one ticket type of an event.
	 *
	 * The requests run in another PHP process, so the query cache here must not answer.
	 */
	function mep_zp_holds( $event_id, $ticket_name ) {
		return count( get_posts( array(
			'post_type'        => 'mep_temp_attendee',
			'post_status'      => 'any',
			'numberposts'      => -1,
			'fields'           => 'ids',
			'cache_results'    => false,
			'suppress_filters' => true,
			'meta_query'       => array(
				array( 'key' => 'event_id', 'value' => $event_id ),
				array( 'key' => 'ticket_type', 'value' => $ticket_name ),
			),
		) ) );
	}

	/**
	 * Every order that contains one of the given products, placed since $since.
	 *
	 * @param int[] $product_ids Product ids.
	 * @param int   $since       Unix timestamp.
	 * @param bool  $placed_only Leave out checkout drafts.
	 * @return WC_Order[]
	 */
	function mep_zp_orders_with_products( $product_ids, $since, $placed_only = false ) {
		$statuses = array_keys( wc_get_order_statuses() );
		if ( ! $placed_only ) {
			$statuses[] = 'wc-checkout-draft';
		}
		$found = array();
		foreach ( wc_get_orders( array( 'limit' => -1, 'status' => $statuses, 'date_created' => '>=' . $since ) ) as $order ) {
			foreach ( $order->get_items() as $item ) {
				if ( is_a( $item, 'WC_Order_Item_Product' ) && in_array( (int) $item->get_product_id(), $product_ids, true ) ) {
					$found[ $order->get_id() ] = $order;
					break;
				}
			}
		}

		return array_values( $found );
	}

	/**
	 * Leaves a ticketless event line in a Store API cart session - the state a cart built
	 * before the fix is still in when the fixed code first sees it.
	 */
	function mep_zp_seed_ticketless_session( $customer_id, $fixture ) {
		global $wpdb;
		$product = wc_get_product( $fixture['product_id'] );
		$key     = md5( 'mep-zp-ticketless-' . $fixture['product_id'] );
		$cart    = array(
			$key => array(
				'key'                 => $key,
				'product_id'          => $fixture['product_id'],
				'variation_id'        => 0,
				'variation'           => array(),
				'quantity'            => 1,
				'data_hash'           => wc_get_cart_item_data_hash( $product ),
				'line_tax_data'       => array( 'subtotal' => array(), 'total' => array() ),
				'line_subtotal'       => 0,
				'line_subtotal_tax'   => 0,
				'line_total'          => 0,
				'line_tax'            => 0,
				'event_ticket_info'   => array(),
				'event_user_info'     => array(),
				'event_tp'            => 0,
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

	/** A guest checkout request for the Store API. */
	function mep_zp_checkout_body() {
		$address = array(
			'first_name' => 'Zero',
			'last_name'  => 'Price',
			'address_1'  => '1 Market Street',
			'city'       => 'San Francisco',
			'state'      => 'CA',
			'postcode'   => '94105',
			'country'    => 'US',
		);

		return array(
			'billing_address'  => $address + array( 'email' => 'zero-price-regression@example.com' ),
			'shipping_address' => $address,
		);
	}

	function mep_zp_cleanup( $fixtures, $since ) {
		global $wpdb;
		if ( ! $fixtures ) {
			return;
		}
		$product_ids = array_map( 'intval', wp_list_pluck( $fixtures, 'product_id' ) );
		foreach ( mep_zp_orders_with_products( $product_ids, $since ) as $order ) {
			$order->delete( true );
		}
		foreach ( $fixtures as $fixture ) {
			foreach ( array( 'mep_temp_attendee' => 'event_id', 'mep_events_attendees' => 'ea_event_id' ) as $post_type => $meta_key ) {
				$ids = get_posts( array(
					'post_type'   => $post_type,
					'post_status' => 'any',
					'numberposts' => -1,
					'fields'      => 'ids',
					'meta_key'    => $meta_key,
					'meta_value'  => $fixture['event_id'],
				) );
				foreach ( $ids as $id ) {
					wp_delete_post( $id, true );
				}
			}
			wp_delete_post( $fixture['product_id'], true );
			wp_delete_post( $fixture['event_id'], true );
		}
		foreach ( array_unique( array_filter( MEP_ZP_Run::$session_keys ) ) as $session_key ) {
			$wpdb->delete( $wpdb->prefix . 'woocommerce_sessions', array( 'session_key' => $session_key ) );
		}
		foreach ( MEP_ZP_Run::$user_ids as $user_id ) {
			wp_delete_user( $user_id );
		}
	}

	/* ------------------------------------------------------------------------------------ */

	$mep_zp_since    = time() - MINUTE_IN_SECONDS;
	$mep_zp_now      = strtotime( current_time( 'Y-m-d H:i:s' ) );
	$mep_zp_fixtures = array();

	try {
		$upcoming = $mep_zp_fixtures[] = mep_zp_create_event( 'ZP regression - upcoming', $mep_zp_now + 30 * DAY_IN_SECONDS );
		$expired  = $mep_zp_fixtures[] = mep_zp_create_event( 'ZP regression - expired', $mep_zp_now - 30 * DAY_IN_SECONDS );
		$draft    = $mep_zp_fixtures[] = mep_zp_create_event( 'ZP regression - draft', $mep_zp_now + 30 * DAY_IN_SECONDS, 'draft' );
		WP_CLI::log( sprintf( 'Fixtures: upcoming #%d (product #%d), expired #%d (product #%d), draft #%d (product #%d)', $upcoming['event_id'], $upcoming['product_id'], $expired['event_id'], $expired['product_id'], $draft['event_id'], $draft['product_id'] ) );

		// --- Requests that must never produce a cart line -------------------------------

		$client = new MEP_ZP_Client();
		$client->send( 'GET', add_query_arg( array( 'add-to-cart' => $upcoming['product_id'], 'quantity' => 1 ), home_url( '/' ) ) );
		$cart = $client->cart();
		MEP_ZP_Run::check( 'classic GET ?add-to-cart=<product> without tickets is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		$client = new MEP_ZP_Client();
		$client->send( 'POST', add_query_arg( 'wc-ajax', 'add_to_cart', home_url( '/' ) ), array( 'product_id' => $upcoming['product_id'], 'quantity' => 1 ) );
		$cart = $client->cart();
		MEP_ZP_Run::check( 'AJAX ?wc-ajax=add_to_cart without tickets is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		$client = new MEP_ZP_Client( true );
		$client->send( 'GET', mep_zp_store_url( 'cart' ) );
		// Refused as a client error: a 500 would mean the refusal escaped as an unhandled exception.
		$res  = $client->send( 'POST', mep_zp_store_url( 'cart/add-item' ), array( 'id' => $upcoming['product_id'], 'quantity' => 1 ), true );
		$cart = $client->cart();
		MEP_ZP_Run::check( 'Store API add-item (JSON) without tickets is refused', mep_zp_is_client_error( $res ) && 0 === $cart['count'], sprintf( 'HTTP %d %s, %d line(s), total %.2f', $res['code'], mep_zp_error_code( $res ), $cart['count'], $cart['total'] ) );

		$client     = new MEP_ZP_Client( true );
		$client->send( 'GET', mep_zp_store_url( 'cart' ) );
		$held_before = mep_zp_holds( $upcoming['event_id'], 'Adult' );
		$res         = $client->send( 'POST', mep_zp_store_url( 'cart/add-item' ), array( 'id' => $upcoming['product_id'], 'quantity' => 1 ) + mep_zp_ticket_fields( $upcoming, array( 'Adult' => 1 ) ) );
		$held        = mep_zp_holds( $upcoming['event_id'], 'Adult' ) - $held_before;
		$cart        = $client->cart();
		MEP_ZP_Run::check( 'Store API add-item is not a supported booking route, even with ticket fields', mep_zp_is_client_error( $res ) && 0 === $cart['count'] && 0 === $held, sprintf( 'HTTP %d %s, %d line(s), %d seat hold(s) left behind', $res['code'], mep_zp_error_code( $res ), $cart['count'], $held ) );

		$client = new MEP_ZP_Client();
		$client->send( 'POST', home_url( '/' ), array( 'add-to-cart' => $upcoming['product_id'], 'mep_event_start_date' => array( $upcoming['date'] ) ) );
		$cart = $client->cart();
		MEP_ZP_Run::check( 'classic POST with a date but no ticket fields is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		$cart = mep_zp_classic_post( $upcoming, array( 'Adult' => 0, 'Community Pass' => 0 ) );
		MEP_ZP_Run::check( 'classic POST with every ticket quantity at 0 is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		$cart = mep_zp_classic_post( $upcoming, array( 'VIP Backstage' => 3 ) );
		MEP_ZP_Run::check( 'classic POST with a ticket type the event does not have is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		$cart = mep_zp_classic_post( $upcoming, array( 'Staff Comp' => 2 ) );
		MEP_ZP_Run::check( 'classic POST for a switched-off ticket type is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		$cart = mep_zp_classic_post( $draft, array( 'Adult' => 1 ) );
		MEP_ZP_Run::check( 'booking a draft event through its helper product is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		// The event's editors may still test-book it before publishing. A logged-in cart
		// cannot be read back through the Store API without a nonce, so count the ticket
		// rows the server accepted instead.
		require_once ABSPATH . 'wp-admin/includes/user.php';
		$editor_id = wp_insert_user( array(
			'user_login' => 'zp_regression_' . strtolower( wp_generate_password( 8, false, false ) ),
			'user_pass'  => wp_generate_password( 32 ),
			'user_email' => 'zp-regression-' . strtolower( wp_generate_password( 8, false, false ) ) . '@example.com',
			'role'       => 'editor',
		) );
		if ( is_wp_error( $editor_id ) ) {
			MEP_ZP_Run::check( 'an editor can still test-book a draft event', false, 'could not create the editor: ' . $editor_id->get_error_message() );
		} else {
			MEP_ZP_Run::$user_ids[]     = (int) $editor_id;
			MEP_ZP_Run::$session_keys[] = (string) $editor_id;
			$held_before                = mep_zp_holds( $draft['event_id'], 'Adult' );
			$client                     = new MEP_ZP_Client();
			$client->set_cookie( LOGGED_IN_COOKIE, wp_generate_auth_cookie( $editor_id, time() + HOUR_IN_SECONDS, 'logged_in' ) );
			$client->send( 'POST', home_url( '/' ), array( 'add-to-cart' => $draft['product_id'] ) + mep_zp_ticket_fields( $draft, array( 'Adult' => 1 ) ) );
			$held = mep_zp_holds( $draft['event_id'], 'Adult' ) - $held_before;
			MEP_ZP_Run::check( 'an editor can still test-book a draft event', 1 === $held, sprintf( '%d ticket row(s) accepted (expected 1)', $held ) );
		}

		$cart = mep_zp_classic_post( $expired, array( 'Adult' => 1 ) );
		MEP_ZP_Run::check( 'booking an expired event through its helper product is refused', 0 === $cart['count'], sprintf( '%d line(s), total %.2f', $cart['count'], $cart['total'] ) );

		// WooCommerce 11 no longer runs woocommerce_add_to_cart_validation inside
		// WC_Cart::add_to_cart(), so this path only has the cart item data guard.
		$_POST = array();
		wc_load_cart();
		WC()->cart->empty_cart( false );
		$direct_key   = WC()->cart->add_to_cart( $upcoming['product_id'], 1 );
		$direct_lines = count( WC()->cart->get_cart() );
		MEP_ZP_Run::$session_keys[] = (string) WC()->session->get_customer_id();
		WC()->cart->empty_cart( false );
		wc_clear_notices();
		MEP_ZP_Run::check( 'direct WC()->cart->add_to_cart() without tickets is refused', false === $direct_key && 0 === $direct_lines, sprintf( 'returned %s, %d line(s)', var_export( $direct_key, true ), $direct_lines ) );

		// --- A ticketless line already sitting in a cart must not check out -------------
		// Seeded straight into the session table - the state a cart saved before the fix is
		// in. Both cases require the removal notice, so a seed that never loaded cannot pass.

		$mep_zp_removed = 'because no tickets were selected';

		$client = new MEP_ZP_Client( true );
		$client->send( 'GET', mep_zp_store_url( 'cart' ) );
		mep_zp_seed_ticketless_session( mep_zp_token_customer( $client->token() ), $upcoming );
		$cart = $client->cart();
		MEP_ZP_Run::check( 'a ticketless line already in a cart is removed when the cart is read', 0 === $cart['count'] && false !== strpos( $cart['errors'], $mep_zp_removed ), sprintf( '%d line(s), total %.2f, errors: %s', $cart['count'], $cart['total'], $cart['errors'] ? $cart['errors'] : 'none' ) );

		$client = new MEP_ZP_Client( true );
		$client->send( 'GET', mep_zp_store_url( 'cart' ) );
		mep_zp_seed_ticketless_session( mep_zp_token_customer( $client->token() ), $upcoming );
		$res    = $client->send( 'POST', mep_zp_store_url( 'checkout' ), mep_zp_checkout_body(), true );
		$placed = mep_zp_orders_with_products( array( $upcoming['product_id'] ), $mep_zp_since, true );
		MEP_ZP_Run::check( 'Store API checkout of a ticketless line is refused and places no order', ! in_array( $res['code'], array( 200, 201 ), true ) && 0 === count( $placed ) && false !== strpos( $res['body'], $mep_zp_removed ), sprintf( 'HTTP %d %s, %d order(s) placed', $res['code'], mep_zp_error_code( $res ), count( $placed ) ) );

		// --- Genuine bookings keep working ---------------------------------------------

		$cart = mep_zp_classic_post( $upcoming, array( 'Adult' => 2 ) );
		MEP_ZP_Run::check( 'paid tickets from the event form are added at the server price', 1 === $cart['count'] && abs( $cart['total'] - 50.0 ) < 0.001, sprintf( '%d line(s), total %.2f (expected 1 line, 50.00)', $cart['count'], $cart['total'] ) );

		$cart = mep_zp_classic_post( $upcoming, array( 'Community Pass' => 1 ) );
		MEP_ZP_Run::check( 'a genuinely free ticket from the event form is still added', 1 === $cart['count'] && abs( $cart['total'] ) < 0.001, sprintf( '%d line(s), total %.2f (expected 1 line, 0.00)', $cart['count'], $cart['total'] ) );

		// A 0.00 ticket does not move the total, so read the per-ticket-type cart holds the
		// request left behind: one hold per ticket row the server accepted.
		$holds_before = array( 'Adult' => mep_zp_holds( $upcoming['event_id'], 'Adult' ), 'Staff Comp' => mep_zp_holds( $upcoming['event_id'], 'Staff Comp' ) );
		$cart         = mep_zp_classic_post( $upcoming, array( 'Adult' => 1, 'Community Pass' => 2, 'Staff Comp' => 5 ) );
		$adult_held   = mep_zp_holds( $upcoming['event_id'], 'Adult' ) - $holds_before['Adult'];
		$comp_held    = mep_zp_holds( $upcoming['event_id'], 'Staff Comp' ) - $holds_before['Staff Comp'];
		MEP_ZP_Run::check( 'a mixed selection keeps the valid tickets and drops the switched-off one', 1 === $cart['count'] && abs( $cart['total'] - 25.0 ) < 0.001 && 1 === $adult_held && 0 === $comp_held, sprintf( '%d line(s), total %.2f, Adult rows %d, Staff Comp rows %d (expected 1 line, 25.00, 1, 0)', $cart['count'], $cart['total'], $adult_held, $comp_held ) );

		// The checkout backstop must leave genuine lines alone: a free ticket chosen on the
		// event page still becomes an order through the block checkout.
		$client = new MEP_ZP_Client();
		$client->send( 'POST', home_url( '/' ), array( 'add-to-cart' => $upcoming['product_id'] ) + mep_zp_ticket_fields( $upcoming, array( 'Community Pass' => 1 ) ) );
		$client->cart(); // Collects the Store API nonce for this cart session.
		$res       = $client->send( 'POST', mep_zp_store_url( 'checkout' ), mep_zp_checkout_body(), true );
		$order_id  = is_array( $res['json'] ) && isset( $res['json']['order_id'] ) ? (int) $res['json']['order_id'] : 0;
		$order     = $order_id ? wc_get_order( $order_id ) : false;
		$attendees = $order_id ? count( get_posts( array(
			'post_type'        => 'mep_events_attendees',
			'post_status'      => 'any',
			'numberposts'      => -1,
			'fields'           => 'ids',
			'cache_results'    => false,
			'suppress_filters' => true,
			'meta_key'         => 'ea_order_id',
			'meta_value'       => $order_id,
		) ) ) : 0;
		MEP_ZP_Run::check( 'a genuine free ticket still checks out through the Store API', in_array( $res['code'], array( 200, 201 ), true ) && $order && abs( (float) $order->get_total() ) < 0.001, sprintf( 'HTTP %d, order #%d %s, %d attendee(s)', $res['code'], $order_id, $order ? $order->get_status() : '-', $attendees ) );
	} finally {
		mep_zp_cleanup( $mep_zp_fixtures, $mep_zp_since );
	}

	$mep_zp_failed = count( array_filter( MEP_ZP_Run::$results, function ( $pass ) {
		return ! $pass;
	} ) );
	if ( $mep_zp_failed ) {
		WP_CLI::log( sprintf( '%d of %d case(s) failed.', $mep_zp_failed, count( MEP_ZP_Run::$results ) ) );
		WP_CLI::halt( 1 );
	}
	WP_CLI::success( sprintf( 'All %d cases passed.', count( MEP_ZP_Run::$results ) ) );
