<?php
	/**
	 * Regression test: the shared licence AJAX actions cannot write arbitrary options.
	 *
	 * Event add-ons ship mep_wl_ajax_license_activate / _deactivate under one name; some
	 * copies take the option names from the request with no capability check, even for
	 * logged-out visitors. mep_license_request_guard() runs first for every copy. Shop
	 * Managers are also no longer granted manage_options for these actions.
	 *
	 * Meaningful only while an add-on that registers the handlers is active (seat plan,
	 * group qty, PRO); otherwise the run is skipped. Sends real HTTP requests:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/licence-ajax-guard.php
	 *
	 * Refused on a "production" site unless `allow-production` is passed. Restores the
	 * options it touches and deletes the user it creates. Exits 1 on any failure.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI.' );
	}
	$mep_lg_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_lg_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment; pass allow-production to override.' );
	}
	if ( ! function_exists( 'mep_wl_ajax_license_deactivate' ) ) {
		WP_CLI::warning( 'No active add-on registers the licence handlers; nothing to test.' );
		return;
	}

	$mep_lg_results = array();
	$mep_lg_check   = function ( $name, $pass, $detail ) use ( &$mep_lg_results ) {
		$mep_lg_results[] = (bool) $pass;
		WP_CLI::log( sprintf( '%s  %s - %s', $pass ? 'PASS' : 'FAIL', $name, $detail ) );
	};
	$mep_lg_cookies = function ( $user_id ) {
		$expiration = time() + HOUR_IN_SECONDS;
		$token      = WP_Session_Tokens::get_instance( $user_id )->create( $expiration );

		return array(
			AUTH_COOKIE      => wp_generate_auth_cookie( $user_id, $expiration, 'auth', $token ),
			LOGGED_IN_COOKIE => wp_generate_auth_cookie( $user_id, $expiration, 'logged_in', $token ),
		);
	};
	$mep_lg_post = function ( $names, $cookies = array() ) {
		$body = array( 'action' => 'mep_wl_ajax_license_deactivate', 'item_name' => 'x', 'item_id' => '1' );
		foreach ( array( 'key', 'status', 'expire', 'order_id' ) as $i => $part ) {
			$body[ $part . '_option_name' ] = $names[ $i ];
		}
		$res = wp_remote_post( admin_url( 'admin-ajax.php' ), array( 'body' => $body, 'cookies' => $cookies, 'timeout' => 60 ) );
		wp_cache_delete( 'alloptions', 'options' );
		wp_cache_delete( 'zz_mep_lg_canary', 'options' );
		wp_cache_delete( 'zz_mep_lg_license_status', 'options' );

		return is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
	};

	$mep_lg_saved = array();
	foreach ( array( 'zz_mep_lg_canary', 'zz_mep_lg_license_key', 'zz_mep_lg_license_status', 'zz_mep_lg_license_expire', 'zz_mep_lg_license_order_id' ) as $name ) {
		$mep_lg_saved[ $name ] = get_option( $name, null );
	}
	$mep_lg_user = 0;
	$canary      = array_fill( 0, 4, 'zz_mep_lg_canary' );
	$licence     = array( 'zz_mep_lg_license_key', 'zz_mep_lg_license_status', 'zz_mep_lg_license_expire', 'zz_mep_lg_license_order_id' );

	try {
		update_option( 'zz_mep_lg_canary', 'keep' );
		update_option( 'zz_mep_lg_license_status', 'valid' );

		$code = $mep_lg_post( $canary );
		$mep_lg_check( 'a logged-out request cannot blank an arbitrary option', 'keep' === get_option( 'zz_mep_lg_canary' ), 'HTTP ' . $code );
		$code = $mep_lg_post( $licence );
		$mep_lg_check( 'a logged-out request cannot deactivate a licence', 'valid' === get_option( 'zz_mep_lg_license_status' ), 'HTTP ' . $code );

		if ( get_role( 'shop_manager' ) ) {
			$mep_lg_user = wp_insert_user( array( 'user_login' => 'zz_mep_lg_manager', 'user_pass' => wp_generate_password( 24 ), 'user_email' => 'zz-mep-lg-manager@example.com', 'role' => 'shop_manager' ) );
			$code        = $mep_lg_post( $licence, $mep_lg_cookies( $mep_lg_user ) );
			$mep_lg_check( 'a Shop Manager cannot deactivate a licence', 'valid' === get_option( 'zz_mep_lg_license_status' ), 'HTTP ' . $code );
		}

		$admins = get_users( array( 'role' => 'administrator', 'fields' => 'ids', 'number' => 1 ) );
		$admin  = $mep_lg_cookies( (int) $admins[0] );
		$code   = $mep_lg_post( $canary, $admin );
		$mep_lg_check( 'an administrator cannot point the handler at a non-licence option', 'keep' === get_option( 'zz_mep_lg_canary' ), 'HTTP ' . $code );
		$code = $mep_lg_post( $licence, $admin );
		$mep_lg_check( 'an administrator still deactivates a licence', 'invalid' === get_option( 'zz_mep_lg_license_status' ), 'HTTP ' . $code );
	} finally {
		foreach ( $mep_lg_saved as $name => $value ) {
			if ( null === $value ) {
				delete_option( $name );
			} else {
				update_option( $name, $value );
			}
		}
		if ( $mep_lg_user ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( $mep_lg_user );
		}
	}

	$mep_lg_failed = count( array_filter( $mep_lg_results, function ( $pass ) {
		return ! $pass;
	} ) );
	WP_CLI::log( sprintf( '%d checks, %d passed, %d failed.', count( $mep_lg_results ), count( $mep_lg_results ) - $mep_lg_failed, $mep_lg_failed ) );
	if ( $mep_lg_failed ) {
		WP_CLI::halt( 1 );
	}
