<?php
	/**
	 * Regression tests: attendee records stay private.
	 *
	 *  - The attendee post type is never public, whichever add-on registers it (the
	 *    standalone Form Builder add-on registered it with 'public' => true), so
	 *    sitemaps and SEO plugins skip it; admin screens and the ticket page stay.
	 *  - Anonymous visitors cannot list, search, feed or embed attendees (by slug
	 *    or by id); the ticket page still answers its buyer and administrators.
	 *  - New attendees get a random slug, not their name; stored and explicit slugs
	 *    are kept, so QR codes on tickets already sent keep working.
	 *
	 * HTTP cases send real requests to this site. Needs this plugin and a plugin that
	 * registers mep_events_attendees (Event Booking Manager PRO or the Form Builder
	 * add-on) active:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/attendee-privacy.php
	 *
	 * Refused on a "production" site unless `allow-production` is passed. Deletes the
	 * users, orders and attendee posts it creates; probe attendees belong to no real
	 * event, so no seat count changes.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI.' );
	}
	if ( ! function_exists( 'mep_native_ticket_attendee_create' ) || ! function_exists( 'mep_attendee_neutral_slug' ) ) {
		WP_CLI::error( 'Event Booking Manager for WooCommerce must be active (this branch).' );
	}
	if ( ! post_type_exists( 'mep_events_attendees' ) ) {
		WP_CLI::error( 'Nothing registers mep_events_attendees; activate Event Booking Manager PRO.' );
	}
	$mep_ap_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_ap_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment; pass allow-production to override.' );
	}

	$mep_ap_results = array();
	$mep_ap_check   = function ( $name, $pass, $detail = '' ) use ( &$mep_ap_results ) {
		$mep_ap_results[] = (bool) $pass;
		WP_CLI::log( sprintf( '%s  %s%s', $pass ? 'PASS' : 'FAIL', $name, '' !== $detail ? ' - ' . $detail : '' ) );
	};
	$mep_ap_cookies = function ( $user_id ) {
		$expiration = time() + HOUR_IN_SECONDS;
		$token      = WP_Session_Tokens::get_instance( $user_id )->create( $expiration );

		return array(
			AUTH_COOKIE      => wp_generate_auth_cookie( $user_id, $expiration, 'auth', $token ),
			LOGGED_IN_COOKIE => wp_generate_auth_cookie( $user_id, $expiration, 'logged_in', $token ),
		);
	};
	$mep_ap_get = function ( $url, $cookies = array() ) {
		$res = wp_remote_get( $url, array( 'cookies' => $cookies, 'timeout' => 60, 'redirection' => 0 ) );
		if ( is_wp_error( $res ) ) {
			return array( 'code' => 0, 'body' => '', 'location' => '' );
		}

		return array(
			'code'     => (int) wp_remote_retrieve_response_code( $res ),
			'body'     => (string) wp_remote_retrieve_body( $res ),
			'location' => (string) wp_remote_retrieve_header( $res, 'location' ),
		);
	};

	$mep_ap_users  = array();
	$mep_ap_posts  = array();
	$mep_ap_orders = array();
	$mep_ap_user  = function ( $role ) use ( &$mep_ap_users ) {
		$login = 'zz_mep_ap_' . $role . '_' . wp_rand( 1000, 9999 );
		$id    = wp_insert_user( array( 'user_login' => $login, 'user_pass' => wp_generate_password( 24 ), 'user_email' => $login . '@example.com', 'role' => $role ) );
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( 'Could not create a ' . $role . ': ' . $id->get_error_message() );
		}
		$mep_ap_users[] = $id;

		return $id;
	};
	// Probe attendees point at an event id that does not exist, so no real event's
	// sold count moves while the test runs.
	$mep_ap_event_id = 990000 + wp_rand( 1, 9999 );
	while ( get_post( $mep_ap_event_id ) ) {
		++$mep_ap_event_id;
	}
	$mep_ap_attendee = function ( $name, $owner_id ) use ( &$mep_ap_posts, $mep_ap_event_id ) {
		$prev = get_current_user_id();
		wp_set_current_user( $owner_id );
		$id = mep_native_ticket_attendee_create(
			$mep_ap_event_id,
			0,
			array( 'user_name' => $name, 'user_email' => 'zz-mep-ap@example.com', 'user_phone' => '', 'user_event_date' => '' ),
			array( 'ticket_name' => 'Probe', 'ticket_qty' => 1, 'ticket_price' => 0 ),
			'offline',
			'completed'
		);
		wp_set_current_user( $prev );
		if ( ! $id ) {
			WP_CLI::error( 'mep_native_ticket_attendee_create() refused the probe attendee.' );
		}
		$mep_ap_posts[] = $id;

		return (int) $id;
	};
	$mep_ap_has_name = function ( $text, $names ) {
		foreach ( $names as $n ) {
			if ( '' !== $n && false !== stripos( rawurldecode( $text ), $n ) ) {
				return true;
			}
		}

		return false;
	};

	try {
		// ------------------------------------------------------------------ post type
		$type = get_post_type_object( 'mep_events_attendees' );
		$mep_ap_check( 'attendee post type is not public', ! $type->public );
		$mep_ap_check( 'ticket page stays reachable (publicly_queryable)', (bool) $type->publicly_queryable );
		$mep_ap_check( 'admin screens stay (show_ui)', (bool) $type->show_ui );
		$mep_ap_check( 'not offered in nav menus', ! $type->show_in_nav_menus );
		$mep_ap_check( 'not in get_post_types( public ) - what Yoast / Rank Math / core sitemaps list', ! in_array( 'mep_events_attendees', get_post_types( array( 'public' => true ) ), true ) );
		$sitemap_types = array();
		if ( function_exists( 'wp_sitemaps_get_server' ) ) {
			$provider = wp_sitemaps_get_server()->registry->get_provider( 'posts' );
			if ( $provider ) {
				$sitemap_types = array_keys( $provider->get_object_subtypes() );
			}
		}
		$mep_ap_check( 'core sitemap provider skips attendees', ! in_array( 'mep_events_attendees', $sitemap_types, true ), implode( ',', $sitemap_types ) );

		// Arguments of the standalone Form Builder add-on (4.3.2), which leave show_ui
		// and publicly_queryable to follow 'public'.
		$fb_args = apply_filters(
			'register_post_type_args',
			array(
				'public'              => true,
				'label'               => 'Event Attendees',
				'supports'            => array( 'title' ),
				'exclude_from_search' => true,
				'show_in_menu'        => false,
				'capability_type'     => 'post',
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
				'show_in_rest'        => false,
			),
			'mep_events_attendees'
		);
		$mep_ap_check( 'Form Builder add-on registration: public => true becomes false', false === $fb_args['public'] );
		$mep_ap_check( 'Form Builder add-on registration: show_ui and publicly_queryable keep true', true === $fb_args['show_ui'] && true === $fb_args['publicly_queryable'] );
		$mep_ap_check( 'Form Builder add-on registration: show_in_nav_menus false, exclude_from_search true', false === $fb_args['show_in_nav_menus'] && true === $fb_args['exclude_from_search'] );
		$explicit = apply_filters( 'register_post_type_args', array( 'public' => true, 'show_ui' => false ), 'mep_events_attendees' );
		$mep_ap_check( 'an explicit show_ui => false is kept', false === $explicit['show_ui'] );
		$other = apply_filters( 'register_post_type_args', array( 'public' => true ), 'mep_events' );
		$mep_ap_check( 'other post types are untouched', true === $other['public'] && ! isset( $other['show_ui'] ) );

		// ---------------------------------------------------------------- slugs
		$owner_id = $mep_ap_user( 'subscriber' );
		$other_id = $mep_ap_user( 'subscriber' );
		$admin_id = $mep_ap_user( 'administrator' );
		$name     = 'Zzprivacy Probeperson';
		$new_id   = $mep_ap_attendee( $name, $owner_id );
		$new_slug = get_post_field( 'post_name', $new_id );
		$mep_ap_check( 'new attendee (native checkout path) gets a ticket- slug', 0 === strpos( $new_slug, 'ticket-' ), $new_slug );
		$mep_ap_check( 'new attendee slug does not contain the name', ! $mep_ap_has_name( $new_slug, array( 'zzprivacy', 'probeperson' ) ) );
		$mep_ap_check( 'attendee title is still the name (admin lists unchanged)', $name === get_the_title( $new_id ) );
		if ( function_exists( 'wc_create_order' ) && function_exists( 'mep_attendee_create' ) ) {
			// The WooCommerce checkout path: the attendee is named after the billing name.
			$order = wc_create_order( array( 'customer_id' => $owner_id ) );
			$order->set_billing_first_name( 'Zzorder' );
			$order->set_billing_last_name( 'Billingname' );
			$order->set_billing_email( 'zz-mep-ap@example.com' );
			$order->save();
			$mep_ap_orders[] = $order->get_id();
			$wc_attendee     = mep_attendee_create( 'billing', $order->get_id(), $mep_ap_event_id, array( 'ticket_name' => 'Probe', 'event_date' => '', 'ticket_qty' => 1 ) );
			if ( $wc_attendee ) {
				$mep_ap_posts[] = (int) $wc_attendee;
			}
			$wc_slug = $wc_attendee ? get_post_field( 'post_name', $wc_attendee ) : '';
			$mep_ap_check( 'WooCommerce order attendee gets a ticket- slug without the billing name', 0 === strpos( $wc_slug, 'ticket-' ) && false === stripos( $wc_slug, 'zzorder' ) && false === stripos( $wc_slug, 'billingname' ), $wc_slug );
			$mep_ap_check( 'WooCommerce order attendee is still titled with the billing name', $wc_attendee && 'Zzorder Billingname' === get_the_title( $wc_attendee ) );
		}
		$second = $mep_ap_attendee( $name, $owner_id );
		$mep_ap_check( 'two attendees with the same name get different slugs', get_post_field( 'post_name', $second ) !== $new_slug );

		wp_update_post( array( 'ID' => $new_id, 'post_title' => 'Zzprivacy Renamed' ) );
		$mep_ap_check( 'renaming an attendee keeps its slug (QR code unchanged)', get_post_field( 'post_name', $new_id ) === $new_slug );

		$legacy_id = wp_insert_post( array( 'post_title' => 'Zzlegacy Person', 'post_name' => 'zzlegacy-person', 'post_status' => 'publish', 'post_type' => 'mep_events_attendees' ) );
		$mep_ap_posts[] = $legacy_id;
		update_post_meta( $legacy_id, 'ea_user_id', $owner_id );
		update_post_meta( $legacy_id, 'ea_event_id', $mep_ap_event_id );
		$mep_ap_check( 'an explicit slug is kept', 'zzlegacy-person' === get_post_field( 'post_name', $legacy_id ) );
		wp_update_post( array( 'ID' => $legacy_id, 'post_title' => 'Zzlegacy Renamed' ) );
		$mep_ap_check( 'an existing name slug is kept on update (old QR codes)', 'zzlegacy-person' === get_post_field( 'post_name', $legacy_id ) );

		$draft_id = wp_insert_post( array( 'post_title' => 'Zzdraft Person', 'post_status' => 'draft', 'post_type' => 'mep_events_attendees' ) );
		$mep_ap_posts[] = $draft_id;
		$mep_ap_check( 'a draft attendee has no slug yet (WordPress default)', '' === get_post_field( 'post_name', $draft_id ) );
		wp_update_post( array( 'ID' => $draft_id, 'post_status' => 'publish' ) );
		$draft_slug = get_post_field( 'post_name', $draft_id );
		$mep_ap_check( 'publishing a draft attendee gives a ticket- slug', 0 === strpos( $draft_slug, 'ticket-' ), $draft_slug );

		wp_trash_post( $draft_id );
		wp_untrash_post( $draft_id );
		$mep_ap_check( 'trash + restore keeps the slug', get_post_field( 'post_name', $draft_id ) === $draft_slug, get_post_field( 'post_name', $draft_id ) );

		// A draft so it never shows in the theme's page navigation during the HTTP checks.
		$page_id        = wp_insert_post( array( 'post_title' => 'Zzslugcheck Page', 'post_status' => 'draft', 'post_type' => 'page' ) );
		$mep_ap_posts[] = $page_id;
		wp_update_post( array( 'ID' => $page_id, 'post_status' => 'private' ) );
		$mep_ap_check( 'other post types keep title slugs', 'zzslugcheck-page' === get_post_field( 'post_name', $page_id ), get_post_field( 'post_name', $page_id ) );

		// ------------------------------------------------------- anonymous requests
		// Full stored titles and the old slug: a search page may echo the visitor's own
		// search word back, which is not a leak.
		$names   = array( 'Zzprivacy Renamed', 'Zzprivacy Probeperson', 'Zzlegacy Renamed', 'zzlegacy-person', 'Zzdraft Person' );
		$home    = home_url( '/' );
		$refused = array(
			'attendee list'               => add_query_arg( 'post_type', 'mep_events_attendees', $home ),
			'attendee list page 2'        => add_query_arg( array( 'post_type' => 'mep_events_attendees', 'paged' => 2 ), $home ),
			'attendee list, mixed types'  => $home . '?post_type[]=post&post_type[]=mep_events_attendees',
			'attendee search'             => add_query_arg( array( 's' => 'Zzlegacy', 'post_type' => 'mep_events_attendees' ), $home ),
			'attendee RSS feed'           => add_query_arg( array( 'post_type' => 'mep_events_attendees', 'feed' => 'rss2' ), $home ),
			'attendee embed by slug'      => trailingslashit( get_permalink( $legacy_id ) ) . 'embed/',
			'attendee embed by id'        => add_query_arg( array( 'post_type' => 'mep_events_attendees', 'p' => $legacy_id, 'embed' => 'true' ), $home ),
			'attendee comment feed by id' => add_query_arg( array( 'post_type' => 'mep_events_attendees', 'p' => $legacy_id, 'feed' => 'rss2' ), $home ),
			'attendee embed by query var' => add_query_arg( array( 'mep_events_attendees' => 'zzlegacy-person', 'embed' => 'true' ), $home ),
		);
		foreach ( $refused as $label => $url ) {
			$r = $mep_ap_get( $url );
			// A redirect to a cleaner URL of the same refused query is fine as long as it
			// carries no name; follow it once.
			if ( in_array( $r['code'], array( 301, 302 ), true ) && $r['location'] && ! $mep_ap_has_name( $r['location'], $names ) ) {
				$r = $mep_ap_get( $r['location'] );
			}
			$mep_ap_check( 'anonymous ' . $label . ': 404 without names', 404 === $r['code'] && ! $mep_ap_has_name( $r['body'], $names ), 'HTTP ' . $r['code'] );
		}
		$r = $mep_ap_get( get_permalink( $legacy_id ) );
		$mep_ap_check( 'anonymous ticket page: login redirect without the name', 302 === $r['code'] && false !== strpos( $r['location'], 'wp-login.php' ) && ! $mep_ap_has_name( $r['location'], $names ), $r['location'] );
		$r = $mep_ap_get( add_query_arg( array( 'post_type' => 'mep_events_attendees', 'p' => $legacy_id ), $home ) );
		$mep_ap_check( 'anonymous ?p=ID ticket page: login redirect, no canonical redirect to the name slug', 302 === $r['code'] && false !== strpos( $r['location'], 'wp-login.php' ) && ! $mep_ap_has_name( $r['location'], $names ), $r['location'] );

		// ----------------------------------------------------- allowed / refused users
		$owner_cookies = $mep_ap_cookies( $owner_id );
		$admin_cookies = $mep_ap_cookies( $admin_id );
		$other_cookies = $mep_ap_cookies( $other_id );
		$new_url       = get_permalink( $new_id );
		$mep_ap_check( 'ticket permalink (QR code target) uses the neutral slug', 0 === strpos( basename( untrailingslashit( (string) wp_parse_url( $new_url, PHP_URL_PATH ) ) ), 'ticket-' ) && false === stripos( $new_url, 'probeperson' ), $new_url );
		$r = $mep_ap_get( $new_url, $admin_cookies );
		$mep_ap_check( 'administrator opens the ticket page at the new slug', 200 === $r['code'] && false !== strpos( $r['body'], 'mep-reg-user-details' ) && false === strpos( $r['body'], 'Its not your Attendee Information' ), 'HTTP ' . $r['code'] );
		$r = $mep_ap_get( $new_url, $owner_cookies );
		$mep_ap_check( 'buyer opens their own ticket page', 200 === $r['code'] && false !== strpos( $r['body'], 'mep-reg-user-details' ), 'HTTP ' . $r['code'] );
		$r = $mep_ap_get( $new_url, $other_cookies );
		$mep_ap_check( 'another customer is refused the ticket page', false !== strpos( $r['body'], 'Its not your Attendee Information' ) && false === strpos( $r['body'], 'mep-reg-user-details' ), 'HTTP ' . $r['code'] );
		$r = $mep_ap_get( get_permalink( $legacy_id ), $admin_cookies );
		$mep_ap_check( 'an old name-slug ticket URL still opens for the administrator', 200 === $r['code'] && false !== strpos( $r['body'], 'mep-reg-user-details' ), 'HTTP ' . $r['code'] );
		$r = $mep_ap_get( add_query_arg( array( 'post_type' => 'mep_events_attendees' ), $home ), $admin_cookies );
		$mep_ap_check( 'attendee list is refused to administrators on the front end too', 404 === $r['code'], 'HTTP ' . $r['code'] );
		$r = $mep_ap_get( admin_url( 'post.php?post=' . $new_id . '&action=edit' ), $admin_cookies );
		$mep_ap_check( 'administrator attendee edit screen still opens', 200 === $r['code'] && 1 === preg_match( '/id=["\']post_ID["\'][^>]*value=["\']' . $new_id . '["\']/', $r['body'] ), 'HTTP ' . $r['code'] );

		// ------------------------------------------------- the rest of the site
		$events = get_posts( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
		$others = array(
			'home page'   => $home,
			'site search' => add_query_arg( 's', 'event', $home ),
			'main feed'   => get_feed_link(),
		);
		if ( $events ) {
			$others['event page'] = get_permalink( $events[0] );
		}
		foreach ( $others as $label => $url ) {
			$r = $mep_ap_get( $url );
			$mep_ap_check( 'anonymous ' . $label . ' still answers 200', 200 === $r['code'], 'HTTP ' . $r['code'] );
		}
	} finally {
		foreach ( $mep_ap_orders as $id ) {
			$o = function_exists( 'wc_get_order' ) ? wc_get_order( $id ) : false;
			if ( $o ) {
				$o->delete( true );
			}
		}
		foreach ( $mep_ap_posts as $id ) {
			wp_delete_post( $id, true );
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $mep_ap_users as $id ) {
			wp_delete_user( $id );
		}
	}

	$mep_ap_failed = count( array_filter( $mep_ap_results, function ( $r ) {
		return ! $r;
	} ) );
	$mep_ap_total  = count( $mep_ap_results );
	if ( $mep_ap_failed ) {
		WP_CLI::error( sprintf( '%d of %d checks failed.', $mep_ap_failed, $mep_ap_total ) );
	}
	WP_CLI::success( sprintf( 'All %d checks passed.', $mep_ap_total ) );
