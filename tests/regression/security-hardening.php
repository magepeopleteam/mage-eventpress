<?php
	/**
	 * Regression tests for the free plugin's security hardening.
	 *
	 *  - Single-event template names cannot leave the themes folders.
	 *  - Plugin event meta cannot be written through core's Custom Fields box by
	 *    non-administrators; other custom fields still can.
	 *  - Values unserialized a second time never become objects.
	 *  - Share buttons do not splice the event title into JavaScript.
	 *  - CSV cells cannot start a formula.
	 *  - Shop Managers are not granted manage_options for licence, MCP or theme-file actions.
	 *  - Quick Setup needs its nonce; Contributors cannot publish speakers; order note
	 *    deletion only removes order notes; [event-add-cart-section] hides draft events.
	 *
	 * HTTP cases send real requests to this site. Needs WooCommerce and this plugin active:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/security-hardening.php
	 *
	 * Refused on a "production" site unless `allow-production` is passed. Restores the
	 * options it touches and deletes the users, posts and comments it creates.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI.' );
	}
	if ( ! class_exists( 'MPWEM_Functions' ) || ! function_exists( 'mep_safe_maybe_unserialize' ) ) {
		WP_CLI::error( 'Event Booking Manager for WooCommerce must be active (this branch).' );
	}
	$mep_fs_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_fs_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment; pass allow-production to override.' );
	}

	$mep_fs_results = array();
	$mep_fs_check   = function ( $name, $pass, $detail = '' ) use ( &$mep_fs_results ) {
		$mep_fs_results[] = (bool) $pass;
		WP_CLI::log( sprintf( '%s  %s%s', $pass ? 'PASS' : 'FAIL', $name, '' !== $detail ? ' - ' . $detail : '' ) );
	};
	$mep_fs_session = function ( $user_id ) {
		$expiration = time() + HOUR_IN_SECONDS;
		$token      = WP_Session_Tokens::get_instance( $user_id )->create( $expiration );
		$cookies    = array(
			AUTH_COOKIE      => wp_generate_auth_cookie( $user_id, $expiration, 'auth', $token ),
			LOGGED_IN_COOKIE => wp_generate_auth_cookie( $user_id, $expiration, 'logged_in', $token ),
		);
		$nonce      = function ( $action ) use ( $user_id, $cookies ) {
			$saved                       = isset( $_COOKIE[ LOGGED_IN_COOKIE ] ) ? $_COOKIE[ LOGGED_IN_COOKIE ] : null;
			$_COOKIE[ LOGGED_IN_COOKIE ] = $cookies[ LOGGED_IN_COOKIE ];
			$prev                        = get_current_user_id();
			wp_set_current_user( $user_id );
			$value = wp_create_nonce( $action );
			wp_set_current_user( $prev );
			if ( null === $saved ) {
				unset( $_COOKIE[ LOGGED_IN_COOKIE ] );
			} else {
				$_COOKIE[ LOGGED_IN_COOKIE ] = $saved;
			}

			return $value;
		};

		return array( 'cookies' => $cookies, 'nonce' => $nonce );
	};
	$mep_fs_post = function ( $url, $body, $cookies ) {
		$res = wp_remote_post( $url, array( 'body' => $body, 'cookies' => $cookies, 'timeout' => 60, 'redirection' => 0 ) );

		return is_wp_error( $res ) ? array( 'code' => 0, 'body' => '' ) : array( 'code' => (int) wp_remote_retrieve_response_code( $res ), 'body' => wp_remote_retrieve_body( $res ) );
	};

	$mep_fs_saved_options = array();
	foreach ( array( 'general_setting_sec', 'email_setting_sec', 'mep_quick_setup' ) as $name ) {
		$mep_fs_saved_options[ $name ] = get_option( $name, null );
	}
	$mep_fs_users    = array();
	$mep_fs_posts    = array();
	$mep_fs_comments = array();
	$mep_fs_user     = function ( $role ) use ( &$mep_fs_users ) {
		$id             = wp_insert_user( array( 'user_login' => 'zz_mep_fs_' . $role, 'user_pass' => wp_generate_password( 24 ), 'user_email' => 'zz-mep-fs-' . $role . '@example.com', 'role' => $role ) );
		$mep_fs_users[] = $id;

		return $id;
	};

	try {
		$admins   = get_users( array( 'role' => 'administrator', 'fields' => 'ids', 'number' => 1 ) );
		$admin_id = (int) $admins[0];
		$author   = $mep_fs_user( 'author' );

		// --- Template path ------------------------------------------------------------------
		$default = MPWEM_PLUGIN_DIR . '/templates/themes/default-theme.php';
		// ../../readme.txt is a real file two levels above the themes folder.
		$mep_fs_check( 'a "../" template name falls back to the default template', $default === MPWEM_Functions::details_template_path( '../../readme.txt' ) && $default === MPWEM_Functions::details_template_path( '../../woocommerce-event-press.php' ), MPWEM_Functions::details_template_path( '../../readme.txt' ) );
		$mep_fs_check( 'a real template name still resolves', $default === MPWEM_Functions::details_template_path( 'default-theme.php' ) && file_exists( MPWEM_Functions::details_template_path( 'default-theme.php' ) ) );

		// --- Meta keys through core's generic editors ----------------------------------------
		$own_event      = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'draft', 'post_title' => 'FS regression - author', 'post_author' => $author ) );
		$mep_fs_posts[] = $own_event;
		wp_set_current_user( $author );
		$author_plugin_key = current_user_can( 'add_post_meta', $own_event, 'mep_event_template' ) || current_user_can( 'edit_post_meta', $own_event, 'mep_ticket_offdays' ) || current_user_can( 'add_post_meta', $own_event, 'latitude' );
		$author_custom_key = current_user_can( 'add_post_meta', $own_event, 'my_custom_field' );
		wp_set_current_user( $admin_id );
		$admin_plugin_key = current_user_can( 'add_post_meta', $own_event, 'mep_event_template' );
		wp_set_current_user( 0 );
		$mep_fs_check( 'an Author cannot write plugin event meta through Custom Fields', ! $author_plugin_key );
		$mep_fs_check( 'an Author can still add their own custom fields', $author_custom_key );
		$mep_fs_check( 'an administrator can still edit plugin event meta', $admin_plugin_key );

		// --- Second unserialize ----------------------------------------------------------------
		$object = mep_safe_maybe_unserialize( serialize( new ArrayObject( array( 1 ) ) ) );
		$mep_fs_check( 'a serialized object is never instantiated', ! ( $object instanceof ArrayObject ), is_object( $object ) ? get_class( $object ) : gettype( $object ) );
		$mep_fs_check( 'a serialized array is still read', array( 'sun', 'mon' ) === mep_safe_maybe_unserialize( serialize( array( 'sun', 'mon' ) ) ) && array( 'x' ) === mep_safe_maybe_unserialize( array( 'x' ) ) );

		// --- Share buttons ---------------------------------------------------------------------
		$share_event    = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'publish', 'post_title' => "<code>x');alert(document.domain)//</code>" ) );
		$mep_fs_posts[] = $share_event;
		$event_id       = $share_event;
		ob_start();
		include MPWEM_PLUGIN_DIR . '/templates/layout/social.php';
		$share_html = ob_get_clean();
		unset( $event_id );
		$onclicks = preg_match_all( '/onclick="([^"]*)"/', $share_html, $m ) ? $m[1] : array();
		$spliced  = array_filter( $onclicks, function ( $js ) {
			return false !== stripos( $js, 'alert' );
		} );
		$mep_fs_check( 'share buttons keep the title out of JavaScript', $onclicks && ! $spliced && false !== strpos( $share_html, 'window.open(this.href' ), count( $onclicks ) . ' onclick handler(s)' );

		// --- CSV cells -----------------------------------------------------------------------
		$mep_fs_check( 'CSV cells cannot start a formula', array( "'=HYPERLINK(1)", "'@x", "'+1 5", '-5', 'Anna', 7 ) === array_map( 'mep_csv_safe_cell', array( '=HYPERLINK(1)', '@x', '+1 5', '-5', 'Anna', 7 ) ) );

		// --- Shop Manager grant ----------------------------------------------------------------
		$saved_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null;
		add_filter( 'wp_doing_ajax', '__return_true' );
		foreach ( array( 'mep_wl_ajax_license_deactivate' => false, 'mep_mcp' => false, 'mep_copy_template_to_theme' => false, 'mep_remove_template_from_theme' => false, 'mep_calendar_get_events' => true ) as $action => $expected ) {
			$_REQUEST['action'] = $action;
			$mep_fs_check( sprintf( 'Shop Manager grant %s %s', $expected ? 'still covers' : 'excludes', $action ), $expected === mep_is_shop_manager_eventpress_request() );
		}
		remove_filter( 'wp_doing_ajax', '__return_true' );
		if ( null === $saved_action ) {
			unset( $_REQUEST['action'] );
		} else {
			$_REQUEST['action'] = $saved_action;
		}

		// --- [event-add-cart-section] --------------------------------------------------------
		$draft          = wp_insert_post( array( 'post_type' => 'mep_events', 'post_status' => 'draft', 'post_title' => 'FS regression - draft' ) );
		$mep_fs_posts[] = $draft;
		wp_set_current_user( $mep_fs_user( 'subscriber' ) );
		$draft_html = do_shortcode( '[event-add-cart-section event="' . $draft . '"]' );
		wp_set_current_user( 0 );
		$mep_fs_check( 'the add-to-cart shortcode shows nothing for a draft event', '' === trim( $draft_html ), strlen( $draft_html ) . ' bytes' );

		// --- Quick Setup over HTTP ------------------------------------------------------------
		$admin = $mep_fs_session( $admin_id );
		$qs    = admin_url( 'edit.php?post_type=mep_events&page=mpwem_quick_setup' );
		$mep_fs_post( $qs, array( 'finish_quick_setup' => '1', 'event_label' => 'ZZ Forged', 'event_slug' => 'zz-forged', 'email_from_name' => 'Forged', 'email_from_address' => 'forged@example.com' ), $admin['cookies'] );
		wp_cache_delete( 'alloptions', 'options' );
		$general = get_option( 'general_setting_sec' );
		$mep_fs_check( 'Quick Setup ignores a POST without its nonce', ! is_array( $general ) || ! isset( $general['mep_event_label'] ) || 'ZZ Forged' !== $general['mep_event_label'] );
		$res = $mep_fs_post( $qs, array( 'finish_quick_setup' => '1', '_mpwem_qs_nonce' => $admin['nonce']( 'mpwem_quick_setup' ), 'event_label' => 'ZZ Real', 'event_slug' => 'zz-real', 'email_from_name' => 'Real', 'email_from_address' => 'real@example.com' ), $admin['cookies'] );
		wp_cache_delete( 'alloptions', 'options' );
		$general = get_option( 'general_setting_sec' );
		$mep_fs_check( 'Quick Setup saves with its nonce', is_array( $general ) && isset( $general['mep_event_label'] ) && 'ZZ Real' === $general['mep_event_label'], 'HTTP ' . $res['code'] );

		// --- Contributor speaker over HTTP -----------------------------------------------------
		$contrib = $mep_fs_user( 'contributor' );
		$cs      = $mep_fs_session( $contrib );
		$res     = $mep_fs_post( admin_url( 'admin-ajax.php' ), array( 'action' => 'mpwem_speaker_create', 'nonce' => $cs['nonce']( 'mpwem_speaker_list' ), 'name' => 'ZZ FS Speaker', 'status' => 'publish' ), $cs['cookies'] );
		$speakers = get_posts( array( 'post_type' => 'mep_event_speaker', 'post_status' => 'any', 'title' => 'ZZ FS Speaker', 'numberposts' => 5 ) );
		foreach ( $speakers as $speaker ) {
			$mep_fs_posts[] = $speaker->ID;
		}
		$mep_fs_check( 'a Contributor\'s new speaker waits for review', 1 === count( $speakers ) && 'pending' === $speakers[0]->post_status, $speakers ? $speakers[0]->post_status : 'HTTP ' . $res['code'] . ' ' . substr( $res['body'], 0, 80 ) );

		// --- Order note deletion over HTTP -----------------------------------------------------
		$page              = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'FS regression - comments' ) );
		$mep_fs_posts[]    = $page;
		$comment           = wp_insert_comment( array( 'comment_post_ID' => $page, 'comment_content' => 'keep me', 'comment_approved' => 1 ) );
		$mep_fs_comments[] = $comment;
		$res               = $mep_fs_post( admin_url( 'admin-ajax.php' ), array( 'action' => 'mep_order_delete_note', 'nonce' => $admin['nonce']( 'mep_orders_nonce' ), 'note_id' => $comment ), $admin['cookies'] );
		clean_comment_cache( $comment );
		$mep_fs_check( 'deleting an "order note" cannot remove another comment', null !== get_comment( $comment ), 'HTTP ' . $res['code'] );
	} finally {
		wp_set_current_user( 0 );
		foreach ( $mep_fs_saved_options as $name => $value ) {
			if ( null === $value ) {
				delete_option( $name );
			} else {
				update_option( $name, $value );
			}
		}
		foreach ( $mep_fs_comments as $comment_id ) {
			wp_delete_comment( $comment_id, true );
		}
		foreach ( $mep_fs_posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $mep_fs_users as $user_id ) {
			wp_delete_user( $user_id );
		}
	}

	$mep_fs_failed = count( array_filter( $mep_fs_results, function ( $pass ) {
		return ! $pass;
	} ) );
	WP_CLI::log( sprintf( '%d checks, %d passed, %d failed.', count( $mep_fs_results ), count( $mep_fs_results ) - $mep_fs_failed, $mep_fs_failed ) );
	if ( $mep_fs_failed ) {
		WP_CLI::halt( 1 );
	}
