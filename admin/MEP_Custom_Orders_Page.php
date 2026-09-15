<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Custom_Orders_Page {

	/** Rows shown per page on the order list. */
	const PER_PAGE = 20;

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_mep_orders_filter', array( __CLASS__, 'ajax_filter' ) );
		add_action( 'wp_ajax_mep_orders_export_csv', array( __CLASS__, 'export_csv' ) );
		add_action( 'wp_ajax_mep_order_update_status', array( __CLASS__, 'ajax_update_status' ) );
		add_action( 'wp_ajax_mep_orders_bulk_status', array( __CLASS__, 'ajax_bulk_status' ) );
		add_action( 'wp_ajax_mep_order_resend_pdf', array( __CLASS__, 'ajax_resend_pdf' ) );
		add_action( 'wp_ajax_mep_order_add_note', array( __CLASS__, 'ajax_add_note' ) );
		add_action( 'wp_ajax_mep_order_delete_note', array( __CLASS__, 'ajax_delete_note' ) );
		add_action( 'admin_head', array( __CLASS__, 'menu_counter_style' ) );
		add_action( 'transition_post_status', array( __CLASS__, 'log_status_change' ), 10, 3 );
		add_action( 'transition_post_status', array( __CLASS__, 'sync_attendee_status' ), 10, 3 );
		// The Payment Gateway filter list is a DISTINCT over postmeta, so it is
		// cached. Drop the cache the moment a gateway value is written, so a
		// gateway used for the first time appears in the dropdown immediately.
		add_action( 'added_post_meta', array( __CLASS__, 'maybe_flush_gateway_cache' ), 10, 3 );
		add_action( 'updated_post_meta', array( __CLASS__, 'maybe_flush_gateway_cache' ), 10, 3 );
	}

	/**
	 * Invalidate the cached gateway filter list when an order records a gateway.
	 *
	 * @param int    $meta_id  Unused.
	 * @param int    $post_id  Unused.
	 * @param string $meta_key Meta key that was written.
	 */
	public static function maybe_flush_gateway_cache( $meta_id, $post_id, $meta_key ) {
		if ( '_mep_payment_gateway' === $meta_key ) {
			MEP_Orders_Query::flush_gateway_options();
		}
	}

	/**
	 * Keep each order's attendee records in step with the order status.
	 *
	 * Seat availability (mep_ticket_type_sold) only counts attendees whose
	 * `ea_order_status` is one of the "seat reserved" statuses (processing /
	 * completed by default). When an order's status changes — from checkout,
	 * the admin status switcher, or anywhere else — every attendee linked to
	 * that order must be updated so the seat/stock count stays correct and the
	 * Attendee list shows the same status as the order.
	 *
	 * @param string  $new_status New post status of the order.
	 * @param string  $old_status Previous post status.
	 * @param WP_Post $post       The order post.
	 */
	public static function sync_attendee_status( $new_status, $old_status, $post ) {
		if ( ! $post || 'mep_custom_order' !== $post->post_type ) {
			return;
		}
		if ( $new_status === $old_status ) {
			return;
		}
		if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
			return;
		}
		// Ignore transient/internal WP statuses that are never real order states.
		if ( in_array( $new_status, array( 'auto-draft', 'inherit', 'draft', 'new' ), true ) ) {
			return;
		}

		// The order post-status "publish" represents a Completed order; the
		// attendee meta uses the slug "completed" for that state. Every other
		// status maps across unchanged.
		$attendee_status = ( 'publish' === $new_status ) ? 'completed' : $new_status;

		$updated = self::set_order_attendees_status( $post->ID, $attendee_status );

		// Notify listeners (e.g. the PDF Ticket addon) that a native order reached a
		// new status so they can send status-based emails — but only once the order's
		// attendee records exist. During checkout the order's post status is set
		// BEFORE the attendees are created, so this fires nothing then; the checkout
		// flow fires the same action itself, after the attendees have been created.
		if ( $updated > 0 ) {
			do_action( 'mep_native_order_status_changed', $post->ID, $attendee_status, $old_status );
		}
	}

	/**
	 * Update the `ea_order_status` meta of every native attendee belonging to
	 * an order. Scoped to native_checkout attendees so it never touches
	 * WooCommerce-created attendees that may share the same numeric order id.
	 *
	 * @param int    $order_id        The mep_custom_order ID.
	 * @param string $attendee_status The ea_order_status value to set.
	 * @return int   Number of attendee records updated.
	 */
	private static function set_order_attendees_status( $order_id, $attendee_status ) {
		$attendee_ids = get_posts( array(
			'post_type'      => 'mep_events_attendees',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'ea_order_id',
					'value'   => $order_id,
					'compare' => '=',
				),
				array(
					'key'     => 'ea_flag',
					'value'   => 'native_checkout',
					'compare' => '=',
				),
			),
		) );

		foreach ( $attendee_ids as $attendee_id ) {
			update_post_meta( $attendee_id, 'ea_order_status', $attendee_status );
		}

		return count( $attendee_ids );
	}

	/**
	 * Canonical order statuses, using the same labels as WooCommerce.
	 *
	 * Keys are the stored post statuses. `publish` is used as "Completed"
	 * for native orders (this is what the checkout sets), the rest mirror
	 * WooCommerce's own status slugs.
	 *
	 * @return array<string,string>
	 */
	public static function get_order_statuses() {
		return array(
			'pending'    => __( 'Pending payment', 'mage-eventpress' ),
			'processing' => __( 'Processing', 'mage-eventpress' ),
			'on-hold'    => __( 'On hold', 'mage-eventpress' ),
			'publish'    => __( 'Completed', 'mage-eventpress' ),
			'cancelled'  => __( 'Cancelled', 'mage-eventpress' ),
			'refunded'   => __( 'Refunded', 'mage-eventpress' ),
			'failed'     => __( 'Failed', 'mage-eventpress' ),
		);
	}

	/**
	 * Record an order note for every status change on a native order, so the
	 * notes panel keeps a full history like WooCommerce. Fires for changes made
	 * from checkout, the admin, or the AJAX status switcher alike.
	 */
	public static function log_status_change( $new_status, $old_status, $post ) {
		if ( ! $post || 'mep_custom_order' !== $post->post_type ) {
			return;
		}
		if ( $new_status === $old_status ) {
			return;
		}
		if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
			return;
		}
		// Ignore transient/internal WP statuses we never expose as order states.
		if ( in_array( $new_status, array( 'auto-draft', 'inherit', 'draft' ), true ) ) {
			return;
		}

		$statuses  = self::get_order_statuses();
		$new_label = isset( $statuses[ $new_status ] )
			? $statuses[ $new_status ]
			: ( 'trash' === $new_status ? __( 'Trashed', 'mage-eventpress' ) : ucfirst( $new_status ) );

		// First transition out of creation = order placed.
		if ( in_array( $old_status, array( 'new', 'auto-draft', '' ), true ) ) {
			self::add_order_note(
				$post->ID,
				sprintf( __( 'Order created with status: %s.', 'mage-eventpress' ), '<strong>' . $new_label . '</strong>' ),
				'system'
			);
			return;
		}

		$old_label = isset( $statuses[ $old_status ] ) ? $statuses[ $old_status ] : ucfirst( $old_status );
		self::add_order_note(
			$post->ID,
			sprintf(
				__( 'Order status changed from %1$s to %2$s.', 'mage-eventpress' ),
				'<strong>' . $old_label . '</strong>',
				'<strong>' . $new_label . '</strong>'
			),
			'system'
		);
	}

	public static function add_menu_page() {
		// Show a pending-order count bubble on the menu item (like WooCommerce's Orders).
		$menu_title = __( 'Event Orders', 'mage-eventpress' );
		$pending    = self::get_pending_order_count();
		if ( $pending > 0 ) {
			$menu_title .= ' <span class="awaiting-mod mep-pending-count"><span class="pending-count">'
				. number_format_i18n( $pending ) . '</span></span>';
		}

		add_submenu_page(
			'edit.php?post_type=mep_events',
			__( 'Event Orders', 'mage-eventpress' ),
			$menu_title,
			MPWEM_Global_Function::get_shop_manager_capability(),
			'mep_event_orders',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Number of native orders awaiting payment (post status "pending").
	 *
	 * @return int
	 */
	public static function get_pending_order_count() {
		$counts = wp_count_posts( 'mep_custom_order' );
		return isset( $counts->pending ) ? (int) $counts->pending : 0;
	}

	/**
	 * Make the Event Orders menu pending-count bubble red. Runs on every admin page
	 * (the bubble lives in the sidebar) only while there are pending orders.
	 */
	public static function menu_counter_style() {
		if ( self::get_pending_order_count() < 1 ) {
			return;
		}
		echo '<style>'
			. '#adminmenu .mep-pending-count,'
			. '#adminmenu li.menu-top:hover .mep-pending-count,'
			. '#adminmenu li.opensub .mep-pending-count,'
			. '#adminmenu li.current .mep-pending-count,'
			. '#adminmenu .wp-has-current-submenu .mep-pending-count,'
			. '#adminmenu a:focus .mep-pending-count'
			. '{background-color:#d63638!important;color:#fff!important;}'
			. '</style>';
	}

	public static function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'mep_event_orders' ) === false ) {
			return;
		}
		wp_enqueue_style(
			'mep-orders-page',
			MPWEM_PLUGIN_URL . '/assets/admin/mep-orders-page.css',
			array(),
			'1.4.0'
		);
		wp_enqueue_script(
			'mep-orders-page',
			MPWEM_PLUGIN_URL . '/assets/admin/mep-orders-page.js',
			array( 'jquery' ),
			'1.4.0',
			true
		);
		wp_localize_script( 'mep-orders-page', 'mepOrders', array(
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'nonce'           => wp_create_nonce( 'mep_orders_nonce' ),
			'filtersEnabled'  => self::is_pro_active(),
			'i18n'            => array(
				'loading'   => __( 'Loading orders…', 'mage-eventpress' ),
				'noOrders'  => __( 'No orders found.', 'mage-eventpress' ),
				'error'     => __( 'Something went wrong. Please try again.', 'mage-eventpress' ),
				'proOnly'   => __( 'Order filters are available in the PRO version.', 'mage-eventpress' ),
			),
		) );
	}

	/**
	 * Whether Event Manager Pro is active.
	 *
	 * @return bool
	 */
	private static function is_pro_active() {
		return function_exists( 'mep_check_plugin_installed' )
			&& mep_check_plugin_installed( 'mage-eventpress-pro/woocommerce-event-manager-pro.php' );
	}

	/* -----------------------------------------------------------------------
	 * Data helpers
	 * -------------------------------------------------------------------- */

	/**
	 * Event id => title for the filter dropdown. Two columns, not whole post
	 * objects: the dropdown never needed the post bodies or their meta.
	 *
	 * @return array<int,string>
	 */
	private static function get_all_events() {
		return MEP_Orders_Query::get_event_options();
	}

	private static function get_all_gateways() {
		return MEP_Orders_Query::get_gateway_options();
	}

	/**
	 * One page of orders, with the totals for the whole filtered set.
	 *
	 * Filtering, ordering, counting and the revenue sum are all done by the
	 * database (see MEP_Orders_Query); only the rows actually shown are loaded.
	 * The previous implementation built the entire order history in PHP and then
	 * sliced 20 rows out of it, which is what made this screen unusable — and
	 * eventually fatal — once a store had real order volume.
	 *
	 * @param array $filters {
	 *   string  $search     Free-text (customer name / email / order id)
	 *   int     $event_id
	 *   string  $status
	 *   string  $gateway
	 *   string  $date_from  Y-m-d
	 *   string  $date_to    Y-m-d
	 *   string  $source     'all' | 'mep_custom_order' | 'shop_order'
	 * }
	 * @param int   $page
	 * @param int   $per_page
	 *
	 * @return array{rows:array,total:int,revenue:float}
	 */
	private static function fetch_orders_page( $filters = array(), $page = 1, $per_page = self::PER_PAGE ) {
		return MEP_Orders_Query::get_page( $filters, $page, $per_page );
	}

	private static function payment_status_label( $post_status ) {
		if ( 'trash' === $post_status ) {
			return __( 'Cancelled', 'mage-eventpress' );
		}
		$map = self::get_order_statuses();
		return isset( $map[ $post_status ] ) ? $map[ $post_status ] : ucfirst( $post_status );
	}

	private static function format_price( $amount ) {
		return class_exists( 'MPWEM_Global_Function' ) && method_exists( 'MPWEM_Global_Function', 'mep_format_price' )
			? MPWEM_Global_Function::mep_format_price( $amount )
			: number_format( (float) $amount, 2 );
	}

	private static function sanitize_filters_from_request() {
		// Advanced filters are a PRO feature — free installs always get an unfiltered list.
		if ( ! self::is_pro_active() ) {
			return array(
				'search'    => '',
				'event_id'  => 0,
				'status'    => '',
				'gateway'   => '',
				'date_from' => '',
				'date_to'   => '',
				'source'    => 'all',
			);
		}

		return array(
			'search'    => isset( $_REQUEST['mep_search'] )    ? sanitize_text_field( wp_unslash( $_REQUEST['mep_search'] ) )    : '',
			'event_id'  => isset( $_REQUEST['mep_event_id'] )  ? absint( $_REQUEST['mep_event_id'] )                             : 0,
			'status'    => isset( $_REQUEST['mep_status'] )    ? sanitize_key( wp_unslash( $_REQUEST['mep_status'] ) )           : '',
			'gateway'   => isset( $_REQUEST['mep_gateway'] )   ? sanitize_text_field( wp_unslash( $_REQUEST['mep_gateway'] ) )   : '',
			'date_from' => isset( $_REQUEST['mep_date_from'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mep_date_from'] ) ) : '',
			'date_to'   => isset( $_REQUEST['mep_date_to'] )   ? sanitize_text_field( wp_unslash( $_REQUEST['mep_date_to'] ) )   : '',
			'source'    => isset( $_REQUEST['mep_source'] )    ? sanitize_key( wp_unslash( $_REQUEST['mep_source'] ) )           : 'all',
		);
	}

	/* -----------------------------------------------------------------------
	 * AJAX: filter orders → return HTML table body + stats
	 * -------------------------------------------------------------------- */
	public static function ajax_filter() {
		check_ajax_referer( 'mep_orders_nonce', 'nonce' );
		if ( ! current_user_can( MPWEM_Global_Function::get_shop_manager_capability() ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$filters  = self::sanitize_filters_from_request();
		$page     = max( 1, isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1 );
		$per_page = self::PER_PAGE;

		$result = self::fetch_orders_page( $filters, $page, $per_page );
		$slice  = $result['rows'];
		$total  = $result['total'];
		$pages  = max( 1, (int) ceil( $total / $per_page ) );

		ob_start();
		if ( empty( $slice ) ) {
			echo '<tr><td colspan="9" class="mep-no-orders">'
				. '<span class="dashicons dashicons-clipboard"></span>'
				. '<p>' . esc_html__( 'No orders found matching your criteria.', 'mage-eventpress' ) . '</p>'
				. '</td></tr>';
		} else {
			foreach ( $slice as $order ) {
				self::render_table_row( $order );
			}
		}
		$tbody_html = ob_get_clean();

		wp_send_json_success( array(
			'tbody'    => $tbody_html,
			'total'    => $total,
			'pages'    => $pages,
			'page'     => $page,
			'revenue'  => self::format_price( $result['revenue'] ),
		) );
	}

	/* -----------------------------------------------------------------------
	 * AJAX: export CSV
	 * -------------------------------------------------------------------- */
	public static function export_csv() {
		check_ajax_referer( 'mep_orders_nonce', 'nonce' );
		if ( ! current_user_can( MPWEM_Global_Function::get_shop_manager_capability() ) ) {
			wp_die( 'Unauthorized', 403 );
		}

		$filters = self::sanitize_filters_from_request();

		// An export legitimately covers the whole filtered set, so it is streamed
		// in bounded batches rather than assembled in memory first: rows are
		// written out as they are read and peak memory stays flat.
		@set_time_limit( 0 );

		$filename = 'event-orders-' . gmdate( 'Y-m-d-His' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$out = fopen( 'php://output', 'w' );
		// BOM for Excel UTF-8
		fputs( $out, "\xEF\xBB\xBF" );

		fputcsv( $out, array(
			__( 'Order ID', 'mage-eventpress' ),
			__( 'Source', 'mage-eventpress' ),
			__( 'Status', 'mage-eventpress' ),
			__( 'Customer Name', 'mage-eventpress' ),
			__( 'Customer Email', 'mage-eventpress' ),
			__( 'Customer Phone', 'mage-eventpress' ),
			__( 'Event', 'mage-eventpress' ),
			__( 'Total', 'mage-eventpress' ),
			__( 'Payment Gateway', 'mage-eventpress' ),
			__( 'Date', 'mage-eventpress' ),
		) );

		MEP_Orders_Query::each( $filters, function( $o ) use ( $out ) {
			fputcsv( $out, array_map( array( __CLASS__, 'csv_safe_cell' ), array(
				'#' . $o['ID'],
				$o['source'] === 'shop_order' ? 'WooCommerce' : 'Native',
				$o['order_status'],
				$o['customer_name'],
				$o['customer_email'],
				$o['customer_phone'],
				$o['event'],
				$o['total_raw'],
				$o['gateway'],
				$o['date'],
			) ) );
			// Flush as we go so a large export starts downloading immediately
			// instead of buffering the whole file first.
			if ( ob_get_level() ) {
				@ob_flush();
			}
			flush();
		} );

		fclose( $out );
		exit;
	}

	/**
	 * Neutralise CSV/formula injection (CWE-1236): customer_name/phone/email come
	 * from the unauthenticated native checkout billing form, so a cell starting
	 * with =, +, -, @, tab or CR could be interpreted as a formula by Excel/Sheets
	 * when an admin opens the export. Prefixing with a single quote forces those
	 * spreadsheet apps to treat the cell as plain text.
	 */
	private static function csv_safe_cell( $value ) {
		$value = (string) $value;
		if ( isset( $value[0] ) && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			return "'" . $value;
		}
		return $value;
	}

	/* -----------------------------------------------------------------------
	 * AJAX: Update order status
	 * -------------------------------------------------------------------- */
	public static function ajax_update_status() {
		check_ajax_referer( 'mep_orders_nonce', 'nonce' );
		if ( ! current_user_can( MPWEM_Global_Function::get_shop_manager_capability() ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$new_status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';

		if ( ! $order_id ) {
			wp_send_json_error( 'Invalid order' );
		}

		// WooCommerce order: update the real WC_Order, not wp_update_post(). Checked
		// via wc_get_order() first since HPOS orders never have a matching post type.
		if ( 'mep_custom_order' !== get_post_type( $order_id ) ) {
			$wc_order = class_exists( 'WooCommerce' ) ? wc_get_order( $order_id ) : false;
			if ( ! $wc_order instanceof WC_Order ) {
				wp_send_json_error( 'Invalid order' );
			}
			$valid_wc_statuses = array_map( function( $key ) {
				return str_replace( 'wc-', '', $key );
			}, array_keys( wc_get_order_statuses() ) );
			if ( ! in_array( $new_status, $valid_wc_statuses, true ) ) {
				wp_send_json_error( 'Invalid status' );
			}
			$old_status = $wc_order->get_status();
			if ( $new_status !== $old_status ) {
				$wc_order->update_status( $new_status, '', true );
			}
			wp_send_json_success( array(
				'old_status' => $old_status,
				'new_status' => $new_status,
				'label'      => wc_get_order_status_name( $new_status ),
			) );
		}

		$valid_statuses = array_keys( self::get_order_statuses() );
		if ( ! in_array( $new_status, $valid_statuses, true ) ) {
			wp_send_json_error( 'Invalid status' );
		}

		$old_status = get_post_status( $order_id );

		if ( $new_status === $old_status ) {
			wp_send_json_success( array(
				'old_status' => $old_status,
				'new_status' => $new_status,
				'label'      => self::payment_status_label( $new_status ),
			) );
		}

		$updated = wp_update_post( array(
			'ID'          => $order_id,
			'post_status' => $new_status,
		) );

		if ( is_wp_error( $updated ) ) {
			wp_send_json_error( $updated->get_error_message() );
		}

		// The status-change note is recorded centrally by log_status_change()
		// via the transition_post_status hook, so every change is captured.
		$new_label = self::payment_status_label( $new_status );

		wp_send_json_success( array(
			'old_status' => $old_status,
			'new_status' => $new_status,
			'label'      => $new_label,
		) );
	}

	/* -----------------------------------------------------------------------
	 * AJAX: Re-send the PDF ticket email for a native order
	 *
	 * Manually re-triggers the same PDF ticket email the status change sends.
	 * Depends on the PDF Ticket addon only at runtime (the MEP_Native_Order shim
	 * and $meppdf->send_email() live in that addon), so it is fully guarded and
	 * degrades to an error message when the addon is not active.
	 * -------------------------------------------------------------------- */
	public static function ajax_resend_pdf() {
		check_ajax_referer( 'mep_orders_nonce', 'nonce' );
		if ( ! current_user_can( MPWEM_Global_Function::get_shop_manager_capability() ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'mage-eventpress' ) ), 403 );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		if ( ! $order_id || 'mep_custom_order' !== get_post_type( $order_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order.', 'mage-eventpress' ) ) );
		}

		global $meppdf;
		if ( ! is_object( $meppdf ) || ! method_exists( $meppdf, 'send_email' ) || ! class_exists( 'MEP_Native_Order' ) ) {
			wp_send_json_error( array( 'message' => __( 'PDF Ticket addon is not active.', 'mage-eventpress' ) ) );
		}

		// Hybrid online-only orders never get a PDF ticket (guard is WooCommerce-safe).
		if ( function_exists( 'mep_is_hybrid_online_only_order' ) && mep_is_hybrid_online_only_order( $order_id ) ) {
			wp_send_json_error( array( 'message' => __( 'No PDF ticket is available for online-only orders.', 'mage-eventpress' ) ) );
		}

		$order         = new MEP_Native_Order( $order_id );
		$billing_email = $order->get_billing_email();
		if ( empty( $billing_email ) ) {
			wp_send_json_error( array( 'message' => __( 'No billing email is set on this order.', 'mage-eventpress' ) ) );
		}

		// A manual resend always goes to the billing email; also send to individual
		// attendees when that option is enabled in the PDF Email Settings.
		$meppdf->send_email( $order_id, $order );
		if ( function_exists( 'mep_native_pdf_send_email_to_attendees' )
			&& mep_get_option( 'mep_pdf_send_to_attendees', 'mep_pdf_email_settings', 'no' ) === 'yes' ) {
			mep_native_pdf_send_email_to_attendees( $order_id, $order );
		}

		wp_send_json_success( array(
			'message' => sprintf( __( 'PDF ticket re-sent to %s', 'mage-eventpress' ), $billing_email ),
		) );
	}

	/* -----------------------------------------------------------------------
	 * AJAX: Bulk change status (native orders only)
	 * -------------------------------------------------------------------- */
	public static function ajax_bulk_status() {
		check_ajax_referer( 'mep_orders_nonce', 'nonce' );
		if ( ! current_user_can( MPWEM_Global_Function::get_shop_manager_capability() ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$order_ids  = isset( $_POST['order_ids'] ) ? array_map( 'absint', (array) $_POST['order_ids'] ) : array();
		$new_status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';

		if ( empty( $order_ids ) ) {
			wp_send_json_error( 'No orders selected' );
		}
		if ( ! in_array( $new_status, array_keys( self::get_order_statuses() ), true ) ) {
			wp_send_json_error( 'Invalid status' );
		}

		$updated = 0;
		foreach ( array_unique( $order_ids ) as $order_id ) {
			// Bulk action applies to native orders only; WooCommerce orders are managed
			// from the WooCommerce screens.
			if ( 'mep_custom_order' !== get_post_type( $order_id ) ) {
				continue;
			}
			if ( get_post_status( $order_id ) === $new_status ) {
				$updated++;
				continue;
			}
			// wp_update_post fires transition_post_status, so the status note and the
			// attendee-status sync run for every order just like the single switcher.
			$result = wp_update_post( array(
				'ID'          => $order_id,
				'post_status' => $new_status,
			) );
			if ( ! is_wp_error( $result ) && $result ) {
				$updated++;
			}
		}

		wp_send_json_success( array(
			'updated' => $updated,
			'label'   => self::payment_status_label( $new_status ),
		) );
	}

	/* -----------------------------------------------------------------------
	 * AJAX: Add order note
	 * -------------------------------------------------------------------- */
	public static function ajax_add_note() {
		check_ajax_referer( 'mep_orders_nonce', 'nonce' );
		if ( ! current_user_can( MPWEM_Global_Function::get_shop_manager_capability() ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$note_text = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
		$note_type = isset( $_POST['note_type'] ) ? sanitize_key( $_POST['note_type'] ) : 'private';

		if ( ! $order_id ) {
			wp_send_json_error( 'Invalid order' );
		}

		if ( empty( $note_text ) ) {
			wp_send_json_error( 'Note cannot be empty' );
		}

		// WooCommerce order: use WC's own notes system, not the native comment-based one.
		if ( 'mep_custom_order' !== get_post_type( $order_id ) ) {
			$wc_order = class_exists( 'WooCommerce' ) ? wc_get_order( $order_id ) : false;
			if ( ! $wc_order instanceof WC_Order ) {
				wp_send_json_error( 'Invalid order' );
			}
			$wc_note_id = $wc_order->add_order_note( $note_text, ( 'customer' === $note_type ) ? 1 : 0 );
			if ( ! $wc_note_id ) {
				wp_send_json_error( 'Failed to add note' );
			}
			$note = null;
			foreach ( wc_get_order_notes( array( 'order_id' => $order_id ) ) as $n ) {
				if ( (int) $n->id === (int) $wc_note_id ) {
					$note = $n;
					break;
				}
			}
			ob_start();
			if ( $note ) {
				self::render_wc_note( $note );
			}
			$html = ob_get_clean();

			wp_send_json_success( array(
				'note_id' => $wc_note_id,
				'html'    => $html,
			) );
		}

		$note_id = self::add_order_note( $order_id, $note_text, $note_type );
		if ( ! $note_id ) {
			wp_send_json_error( 'Failed to add note' );
		}

		// Return the rendered note HTML
		$note = self::get_note( $note_id );
		ob_start();
		self::render_single_note( $note, $order_id );
		$html = ob_get_clean();

		wp_send_json_success( array(
			'note_id' => $note_id,
			'html'    => $html,
		) );
	}

	/* -----------------------------------------------------------------------
	 * AJAX: Delete order note
	 * -------------------------------------------------------------------- */
	public static function ajax_delete_note() {
		check_ajax_referer( 'mep_orders_nonce', 'nonce' );
		if ( ! current_user_can( MPWEM_Global_Function::get_shop_manager_capability() ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$note_id = isset( $_POST['note_id'] ) ? absint( $_POST['note_id'] ) : 0;
		if ( ! $note_id ) {
			wp_send_json_error( 'Invalid note ID' );
		}

		$deleted = wp_delete_comment( $note_id, true );
		if ( ! $deleted ) {
			wp_send_json_error( 'Failed to delete note' );
		}

		wp_send_json_success();
	}

	/* -----------------------------------------------------------------------
	 * Order Notes helpers
	 * -------------------------------------------------------------------- */

	/**
	 * Add an order note as a custom comment.
	 */
	private static function add_order_note( $order_id, $note, $type = 'private' ) {
		$comment_data = array(
			'comment_post_ID' => $order_id,
			'comment_content' => $note,
			'comment_type'    => 'mep_order_note',
			'comment_author'  => wp_get_current_user()->display_name,
			'comment_author_email' => wp_get_current_user()->user_email,
			'user_id'         => get_current_user_id(),
			'comment_approved' => $type, // store note type in approved field
		);
		return wp_insert_comment( $comment_data );
	}

	/**
	 * Get a single note by ID.
	 */
	private static function get_note( $note_id ) {
		return get_comment( $note_id );
	}

	/**
	 * Get all notes for an order.
	 */
	private static function get_order_notes( $order_id ) {
		$args = array(
			'post_id'      => $order_id,
			'comment_type' => 'mep_order_note',
			'orderby'      => 'comment_date_gmt',
			'order'        => 'DESC',
			// Note type (system/private/customer) is stored in comment_approved,
			// so we must match any value ('all' maps to approved IN (0,1) and
			// would exclude our notes); 'any' adds no comment_approved clause.
			'status'       => 'any',
		);
		return get_comments( $args );
	}

	/**
	 * Render a single note.
	 */
	private static function render_single_note( $comment, $order_id ) {
		$note_type   = $comment->comment_approved;
		$is_system   = $note_type === 'system';
		$is_customer = $note_type === 'customer';
		$type_class  = $is_system ? 'system-note' : ( $is_customer ? 'customer-note' : 'private-note' );

		$date_str = strtoupper( date_i18n( 'F j, Y \a\t g:i A', strtotime( $comment->comment_date_gmt ) ) );
		?>
		<li class="mep-note-item note <?php echo esc_attr( $type_class ); ?>" data-note-id="<?php echo esc_attr( $comment->comment_ID ); ?>">
			<span class="mep-note-dot"></span>
			<div class="mep-note-body">
				<div class="mep-note-content">
					<?php if ( $is_customer ) : ?>
						<span class="mep-note-tag"><?php esc_html_e( 'Note to customer', 'mage-eventpress' ); ?></span>
					<?php endif; ?>
					<?php echo wpautop( wp_kses_post( $comment->comment_content ) ); ?>
				</div>
				<div class="mep-note-meta">
					<span class="mep-note-date"><?php echo esc_html( $date_str ); ?></span>
					<?php if ( ! $is_system ) : ?>
						<a href="#" class="mep-note-delete" data-note-id="<?php echo esc_attr( $comment->comment_ID ); ?>"><?php esc_html_e( 'Delete', 'mage-eventpress' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</li>
		<?php
	}

	/* -----------------------------------------------------------------------
	 * Render helpers
	 * -------------------------------------------------------------------- */

	private static function render_table_row( $order ) {
		$source_badge = $order['source'] === 'shop_order'
			? '<span class="mep-badge mep-badge-woo">WooCommerce</span>'
			: '<span class="mep-badge mep-badge-native">Native</span>';

		$raw_status   = isset( $order['raw_status'] ) ? $order['raw_status'] : strtolower( str_replace( ' ', '-', $order['order_status'] ) );
		$status_class = 'mep-status-' . sanitize_html_class( $raw_status );
		$status_label = esc_html( $order['order_status'] );

		// Both sources open in this plugin's own detail view (see render_order_detail()),
		// so status changes on a WooCommerce order use the same "Change Status" control —
		// wired to update the real WC_Order status, not a WooCommerce-native edit screen.
		$view_url = esc_url( add_query_arg( array(
			'post_type' => 'mep_events',
			'page'      => 'mep_event_orders',
			'action'    => 'view',
			'order'     => $order['ID'],
		), admin_url( 'edit.php' ) ) );

		$order_link = $view_url
			? '<a href="' . $view_url . '" class="mep-order-link">#' . esc_html( $order['ID'] ) . '</a>'
			: '<span class="mep-order-link">#' . esc_html( $order['ID'] ) . '</span>';

		$attendee_summary = '';
		if ( ! empty( $order['attendee_info'] ) ) {
			$parts = array();
			foreach ( (array) $order['attendee_info'] as $f ) {
				if ( ! empty( $f['label'] ) && isset( $f['value'] ) && $f['value'] !== '' ) {
					$parts[] = esc_html( $f['label'] ) . ': ' . esc_html( $f['value'] );
				}
			}
			$attendee_summary = implode( ' · ', array_slice( $parts, 0, 2 ) );
			if ( count( $parts ) > 2 ) {
				$attendee_summary .= ' <em>+' . ( count( $parts ) - 2 ) . ' more</em>';
			}
		}
		?>
		<tr>
			<td class="mep-col-check">
				<?php if ( $order['source'] === 'mep_custom_order' ) : ?>
					<input type="checkbox" class="mep-row-check" value="<?php echo esc_attr( $order['ID'] ); ?>" aria-label="<?php esc_attr_e( 'Select order', 'mage-eventpress' ); ?>" />
				<?php endif; ?>
			</td>
			<td class="mep-col-id">
				<?php echo $order_link; ?>
				<?php echo $source_badge; ?>
				<?php if ( $view_url ) : ?>
					<a href="<?php echo $view_url; ?>" class="mep-view-order-link"><?php esc_html_e( 'View order', 'mage-eventpress' ); ?></a>
				<?php endif; ?>
			</td>
			<td class="mep-col-customer">
				<strong><?php echo esc_html( $order['customer_name'] ?: '—' ); ?></strong>
				<?php if ( $order['customer_email'] ) : ?>
					<br><a href="mailto:<?php echo esc_attr( $order['customer_email'] ); ?>" class="mep-email-link"><?php echo esc_html( $order['customer_email'] ); ?></a>
				<?php endif; ?>
				<?php if ( $order['customer_phone'] ) : ?>
					<br><span class="mep-phone"><?php echo esc_html( $order['customer_phone'] ); ?></span>
				<?php endif; ?>
			</td>
			<td class="mep-col-event">
				<?php if ( $order['event_id'] ) : ?>
					<a href="<?php echo esc_url( get_edit_post_link( $order['event_id'] ) ); ?>"><?php echo esc_html( $order['event'] ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $order['event'] ); ?>
				<?php endif; ?>
			</td>
			<td class="mep-col-attendee">
				<?php echo $attendee_summary ? wp_kses_post( $attendee_summary ) : '<span class="mep-muted">—</span>'; ?>
			</td>
			<td class="mep-col-gateway">
				<span class="mep-gateway-label"><?php echo esc_html( $order['gateway'] ); ?></span>
			</td>
			<td class="mep-col-total">
				<strong><?php echo wp_kses_post( $order['total'] ); ?></strong>
			</td>
			<td class="mep-col-date">
				<?php echo esc_html( $order['date'] ); ?>
			</td>
			<td class="mep-col-status">
				<span class="mep-status-pill <?php echo esc_attr( $status_class ); ?>">
					<?php echo $status_label; ?>
				</span>
			</td>
		</tr>
		<?php
	}

	/* -----------------------------------------------------------------------
	 * Main page render
	 * -------------------------------------------------------------------- */
	public static function render_page() {
		if ( isset( $_GET['action'], $_GET['order'] ) && 'view' === $_GET['action'] ) {
			self::render_order_detail( absint( $_GET['order'] ) );
			return;
		}

		$events   = self::get_all_events();
		$gateways = self::get_all_gateways();

		// Initial load (non-AJAX)
		$filters  = self::sanitize_filters_from_request();
		$per_page = self::PER_PAGE;
		$page     = max( 1, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 );

		$result = self::fetch_orders_page( $filters, $page, $per_page );
		$slice  = $result['rows'];
		$total  = $result['total'];
		$pages  = max( 1, (int) ceil( $total / $per_page ) );

		$total_revenue = $result['revenue'];
		$is_pro        = self::is_pro_active();
		$upgrade_url   = apply_filters( 'mep_pro_upgrade_url', 'https://mage-people.com/product/mage-woo-event-booking-manager-pro/', 0 );
		?>
		<div class="mep-orders-wrap">

			<!-- Header -->
			<header class="bde-hero">
				<div class="bde-hero-copy">
					<span class="bde-eyebrow">
						<span class="dashicons dashicons-clipboard"></span>
						<?php esc_html_e( 'Orders', 'mage-eventpress' ); ?>
					</span>
					<h1 class="bde-title"><?php esc_html_e( 'Event Orders', 'mage-eventpress' ); ?></h1>
					<p class="bde-subtitle"><?php esc_html_e( 'Manage and track all event bookings in one place.', 'mage-eventpress' ); ?></p>
				</div>
				<div class="bde-hero-side">
					<div class="bde-hero-badge">
						<span class="dashicons dashicons-cart"></span>
						<span><?php echo esc_html( sprintf( _n( '%s order', '%s orders', $total, 'mage-eventpress' ), number_format_i18n( $total ) ) ); ?></span>
					</div>
					<div class="bde-hero-actions">
						<button type="button" id="mep-export-csv" class="mep-btn mep-btn-outline">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Export CSV', 'mage-eventpress' ); ?>
						</button>
					</div>
				</div>
			</header>

			<!-- Stats Bar -->
			<div class="mep-stats-bar">
				<div class="mep-stat-card">
					<span class="mep-stat-icon dashicons dashicons-cart"></span>
					<div class="mep-stat-info">
						<span class="mep-stat-value" id="mep-stat-total"><?php echo esc_html( $total ); ?></span>
						<span class="mep-stat-label"><?php esc_html_e( 'Total Orders', 'mage-eventpress' ); ?></span>
					</div>
				</div>
				<div class="mep-stat-card">
					<span class="mep-stat-icon dashicons dashicons-money-alt"></span>
					<div class="mep-stat-info">
						<span class="mep-stat-value" id="mep-stat-revenue"><?php echo wp_kses_post( self::format_price( $total_revenue ) ); ?></span>
						<span class="mep-stat-label"><?php esc_html_e( 'Total Revenue', 'mage-eventpress' ); ?></span>
					</div>
				</div>
				<div class="mep-stat-card">
					<span class="mep-stat-icon dashicons dashicons-calendar-alt"></span>
					<div class="mep-stat-info">
						<span class="mep-stat-value"><?php echo esc_html( count( $events ) ); ?></span>
						<span class="mep-stat-label"><?php esc_html_e( 'Active Events', 'mage-eventpress' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Filter Panel -->
			<div class="mep-filter-panel<?php echo $is_pro ? '' : ' mep-filter-panel--pro-locked'; ?>">
				<div class="mep-filter-panel-header">
					<span class="dashicons dashicons-filter"></span>
					<strong><?php esc_html_e( 'Filter Orders', 'mage-eventpress' ); ?></strong>
					<?php if ( ! $is_pro ) : ?>
						<span class="mep-pro-badge" title="<?php esc_attr_e( 'Available in PRO version', 'mage-eventpress' ); ?>">
							<span class="dashicons dashicons-lock"></span>
							<?php esc_html_e( 'PRO', 'mage-eventpress' ); ?>
						</span>
					<?php endif; ?>
					<button type="button" id="mep-filter-toggle" class="mep-filter-toggle" aria-expanded="true">
						<span class="dashicons dashicons-arrow-up-alt2"></span>
					</button>
				</div>
				<div class="mep-filter-body<?php echo $is_pro ? '' : ' mep-filter-body--disabled'; ?>" id="mep-filter-body">
					<?php if ( ! $is_pro ) : ?>
						<div class="mep-filter-pro-card">
							<div class="mep-filter-pro-icon" aria-hidden="true">
								<span class="dashicons dashicons-lock"></span>
							</div>
							<div class="mep-filter-pro-content">
								<span class="mep-filter-pro-eyebrow"><?php esc_html_e( 'PRO Feature', 'mage-eventpress' ); ?></span>
								<strong class="mep-filter-pro-title"><?php esc_html_e( 'Order filters are available in PRO', 'mage-eventpress' ); ?></strong>
								<p class="mep-filter-pro-text"><?php esc_html_e( 'Search and filter bookings by event, status, payment gateway, source, and date range.', 'mage-eventpress' ); ?></p>
								<ul class="mep-filter-pro-chips">
									<li><?php esc_html_e( 'Search', 'mage-eventpress' ); ?></li>
									<li><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></li>
									<li><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></li>
									<li><?php esc_html_e( 'Gateway', 'mage-eventpress' ); ?></li>
									<li><?php esc_html_e( 'Date range', 'mage-eventpress' ); ?></li>
								</ul>
							</div>
							<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="mep-filter-pro-cta">
								<?php esc_html_e( 'Upgrade to PRO', 'mage-eventpress' ); ?>
								<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
							</a>
						</div>
					<?php else : ?>
					<div class="mep-filter-grid">

						<div class="mep-filter-field mep-filter-field--search">
							<label for="mep-search"><?php esc_html_e( 'Search', 'mage-eventpress' ); ?></label>
							<div class="mep-input-icon-wrap">
								<span class="dashicons dashicons-search"></span>
								<input type="text" id="mep-search" name="mep_search" placeholder="<?php esc_attr_e( 'Name, email or order #…', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $filters['search'] ); ?>" />
							</div>
						</div>

						<div class="mep-filter-field">
							<label for="mep-event-id"><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></label>
							<select id="mep-event-id" name="mep_event_id">
								<option value=""><?php esc_html_e( 'All Events', 'mage-eventpress' ); ?></option>
								<?php foreach ( $events as $ev_id => $ev_title ) : ?>
									<option value="<?php echo esc_attr( $ev_id ); ?>" <?php selected( $filters['event_id'], $ev_id ); ?>>
										<?php echo esc_html( $ev_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="mep-filter-field mep-filter-field--sm">
							<label for="mep-status"><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></label>
							<select id="mep-status" name="mep_status">
								<option value=""><?php esc_html_e( 'All Statuses', 'mage-eventpress' ); ?></option>
								<?php foreach ( self::get_order_statuses() as $st_key => $st_label ) : ?>
									<option value="<?php echo esc_attr( $st_key ); ?>" <?php selected( $filters['status'], $st_key ); ?>><?php echo esc_html( $st_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="mep-filter-field mep-filter-field--sm">
							<label for="mep-gateway"><?php esc_html_e( 'Payment Gateway', 'mage-eventpress' ); ?></label>
							<select id="mep-gateway" name="mep_gateway">
								<option value=""><?php esc_html_e( 'All Gateways', 'mage-eventpress' ); ?></option>
								<?php foreach ( $gateways as $gw ) : ?>
									<option value="<?php echo esc_attr( $gw ); ?>" <?php selected( $filters['gateway'], $gw ); ?>>
										<?php echo esc_html( $gw ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="mep-filter-field mep-filter-field--sm">
							<label for="mep-source"><?php esc_html_e( 'Order Source', 'mage-eventpress' ); ?></label>
							<select id="mep-source" name="mep_source">
								<option value="all"              <?php selected( $filters['source'], 'all' ); ?>><?php esc_html_e( 'All Sources', 'mage-eventpress' ); ?></option>
								<option value="mep_custom_order" <?php selected( $filters['source'], 'mep_custom_order' ); ?>><?php esc_html_e( 'Native Checkout', 'mage-eventpress' ); ?></option>
								<?php if ( class_exists( 'WooCommerce' ) ) : ?>
								<option value="shop_order"       <?php selected( $filters['source'], 'shop_order' ); ?>><?php esc_html_e( 'WooCommerce', 'mage-eventpress' ); ?></option>
								<?php endif; ?>
							</select>
						</div>

						<div class="mep-filter-field mep-filter-field--date">
							<label for="mep-date-from"><?php esc_html_e( 'Date From', 'mage-eventpress' ); ?></label>
							<input type="date" id="mep-date-from" name="mep_date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" />
						</div>

						<div class="mep-filter-field mep-filter-field--date">
							<label for="mep-date-to"><?php esc_html_e( 'Date To', 'mage-eventpress' ); ?></label>
							<input type="date" id="mep-date-to" name="mep_date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />
						</div>

						<div class="mep-filter-actions">
							<button type="button" id="mep-reset-filters" class="mep-btn mep-btn-ghost">
								<span class="dashicons dashicons-dismiss"></span>
								<?php esc_html_e( 'Reset', 'mage-eventpress' ); ?>
							</button>
						</div>

					</div><!-- .mep-filter-grid -->
					<?php endif; ?>
				</div><!-- .mep-filter-body -->
			</div><!-- .mep-filter-panel -->

			<!-- Table -->
			<div class="mep-table-wrap">
				<div class="mep-table-toolbar">
					<span class="mep-result-count">
						<?php
						printf(
							/* translators: %d: number of orders */
							esc_html( _n( '%d order found', '%d orders found', $total, 'mage-eventpress' ) ),
							esc_html( $total )
						);
						?>
					</span>
					<div class="mep-bulk-actions">
						<select id="mep-bulk-status" class="mep-bulk-select">
							<option value=""><?php esc_html_e( 'Bulk: set status to…', 'mage-eventpress' ); ?></option>
							<?php foreach ( self::get_order_statuses() as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" id="mep-bulk-apply" class="button" disabled><?php esc_html_e( 'Apply', 'mage-eventpress' ); ?></button>
						<span class="mep-bulk-count"></span>
					</div>
					<div id="mep-table-loader" class="mep-table-loader" style="display:none;">
						<span class="mep-spinner"></span>
						<?php esc_html_e( 'Updating…', 'mage-eventpress' ); ?>
					</div>
				</div>

				<div id="mep-table-container">
					<table class="mep-orders-table">
						<thead>
							<tr>
								<th class="mep-col-check"><input type="checkbox" id="mep-check-all" aria-label="<?php esc_attr_e( 'Select all orders', 'mage-eventpress' ); ?>" /></th>
								<th><?php esc_html_e( 'Order', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Customer', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Attendee Info', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Gateway', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Total', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Date', 'mage-eventpress' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></th>
							</tr>
						</thead>
						<tbody id="mep-orders-tbody">
							<?php
							if ( empty( $slice ) ) {
								echo '<tr><td colspan="9" class="mep-no-orders">'
									. '<span class="dashicons dashicons-clipboard"></span>'
									. '<p>' . esc_html__( 'No orders found.', 'mage-eventpress' ) . '</p>'
									. '</td></tr>';
							} else {
								foreach ( $slice as $order ) {
									self::render_table_row( $order );
								}
							}
							?>
						</tbody>
					</table>
				</div><!-- #mep-table-container -->

				<!-- Pagination -->
				<div class="mep-pagination" id="mep-pagination">
					<?php self::render_pagination( $page, $pages, $total ); ?>
				</div>
			</div><!-- .mep-table-wrap -->

		</div><!-- .mep-orders-wrap -->
		<?php
	}

	private static function render_pagination( $current, $total_pages, $total_items ) {
		$per_page = self::PER_PAGE;
		$start    = ( ( $current - 1 ) * $per_page ) + 1;
		$end      = min( $current * $per_page, $total_items );
		?>
		<div class="mep-pagination-info">
			<?php printf( esc_html__( 'Showing %1$d–%2$d of %3$d orders', 'mage-eventpress' ), $start, $end, $total_items ); ?>
		</div>

		<?php if ( $total_pages > 1 ) : ?>
		<div class="mep-pagination-links">
			<!-- First -->
			<button type="button" class="mep-page-btn mep-page-edge <?php echo $current <= 1 ? 'mep-page-btn-disabled' : ''; ?>"
				data-page="1" <?php echo $current <= 1 ? 'disabled' : ''; ?> title="<?php esc_attr_e( 'First page', 'mage-eventpress' ); ?>">
				<span class="dashicons dashicons-controls-skipback"></span>
			</button>
			<!-- Prev -->
			<button type="button" class="mep-page-btn <?php echo $current <= 1 ? 'mep-page-btn-disabled' : ''; ?>"
				data-page="<?php echo esc_attr( $current - 1 ); ?>" <?php echo $current <= 1 ? 'disabled' : ''; ?>>
				<span class="dashicons dashicons-arrow-left-alt2"></span>
			</button>

			<!-- Leading ellipsis -->
			<?php if ( $current > 3 ) : ?>
				<button type="button" class="mep-page-btn" data-page="1">1</button>
				<?php if ( $current > 4 ) : ?>
					<span class="mep-page-ellipsis">…</span>
				<?php endif; ?>
			<?php endif; ?>

			<!-- Numbered range -->
			<?php
			$range = 2;
			for ( $i = max( 1, $current - $range ); $i <= min( $total_pages, $current + $range ); $i++ ) :
				$active = $i === $current ? 'mep-page-btn-active' : '';
			?>
				<button type="button" class="mep-page-btn <?php echo esc_attr( $active ); ?>" data-page="<?php echo esc_attr( $i ); ?>">
					<?php echo esc_html( $i ); ?>
				</button>
			<?php endfor; ?>

			<!-- Trailing ellipsis -->
			<?php if ( $current < $total_pages - 2 ) : ?>
				<?php if ( $current < $total_pages - 3 ) : ?>
					<span class="mep-page-ellipsis">…</span>
				<?php endif; ?>
				<button type="button" class="mep-page-btn" data-page="<?php echo esc_attr( $total_pages ); ?>">
					<?php echo esc_html( $total_pages ); ?>
				</button>
			<?php endif; ?>

			<!-- Next -->
			<button type="button" class="mep-page-btn <?php echo $current >= $total_pages ? 'mep-page-btn-disabled' : ''; ?>"
				data-page="<?php echo esc_attr( $current + 1 ); ?>" <?php echo $current >= $total_pages ? 'disabled' : ''; ?>>
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</button>
			<!-- Last -->
			<button type="button" class="mep-page-btn mep-page-edge <?php echo $current >= $total_pages ? 'mep-page-btn-disabled' : ''; ?>"
				data-page="<?php echo esc_attr( $total_pages ); ?>" <?php echo $current >= $total_pages ? 'disabled' : ''; ?> title="<?php esc_attr_e( 'Last page', 'mage-eventpress' ); ?>">
				<span class="dashicons dashicons-controls-skipforward"></span>
			</button>
		</div>

		<div class="mep-pagination-jump">
			<label for="mep-page-jump"><?php esc_html_e( 'Go to', 'mage-eventpress' ); ?></label>
			<input type="number" id="mep-page-jump" min="1" max="<?php echo esc_attr( $total_pages ); ?>"
				placeholder="<?php echo esc_attr( $current ); ?>" />
			<button type="button" id="mep-page-jump-btn" class="mep-btn mep-btn-outline mep-btn-sm"
				data-total-pages="<?php echo esc_attr( $total_pages ); ?>">
				<?php esc_html_e( 'Go', 'mage-eventpress' ); ?>
			</button>
		</div>
		<?php endif; ?>
		<?php
	}

	/* -----------------------------------------------------------------------
	 * Order Detail Page
	 * -------------------------------------------------------------------- */
	public static function render_order_detail( $order_id ) {
		$back_url = admin_url( 'edit.php?post_type=mep_events&page=mep_event_orders' );

		if ( 'mep_custom_order' !== get_post_type( $order_id ) ) {
			// Not a native order — try WooCommerce. Checked via wc_get_order(), not
			// get_post_type(): under High-Performance Order Storage a WC order never
			// lives in wp_posts at all, so get_post_type() can't identify it.
			$wc_order = class_exists( 'WooCommerce' ) ? wc_get_order( $order_id ) : false;
			if ( $wc_order instanceof WC_Order ) {
				self::render_wc_order_detail( $wc_order, $back_url );
				return;
			}
			?>
			<div class="mep-orders-wrap">
				<div class="mep-orders-header">
					<h1 class="mep-orders-title"><?php esc_html_e( 'Order not found', 'mage-eventpress' ); ?></h1>
				</div>
				<p><a href="<?php echo esc_url( $back_url ); ?>" class="mep-btn mep-btn-outline">← <?php esc_html_e( 'Back to Orders', 'mage-eventpress' ); ?></a></p>
			</div>
			<?php
			return;
		}

		$event_id             = (int) get_post_meta( $order_id, '_mep_event_id', true );
		$event_name           = $event_id ? get_the_title( $event_id ) : '-';
		$event_date           = get_post_meta( $order_id, '_mep_event_date', true );
		$customer_phone       = get_post_meta( $order_id, '_mep_customer_phone', true );
		$gateway              = get_post_meta( $order_id, '_mep_payment_gateway', true );
		$total                = get_post_meta( $order_id, '_mep_order_total', true );
		$items                = (array) get_post_meta( $order_id, '_mep_order_items', true );
		$attendee_ids         = (array) get_post_meta( $order_id, '_mep_attendee_ids', true );
		$status               = get_post_status( $order_id );

		// Use WP user account data for the customer section so it stays distinct
		// from the attendee form field values shown separately below.
		$booker_id      = (int) get_post_meta( $order_id, '_mep_user_id', true );
		$booker         = $booker_id ? get_userdata( $booker_id ) : false;
		$customer_name  = $booker ? $booker->display_name : get_post_meta( $order_id, '_mep_customer_name', true );
		$customer_email = $booker ? $booker->user_email   : get_post_meta( $order_id, '_mep_customer_email', true );

		$author_id      = (int) get_post_field( 'post_author', $order_id );
		$author         = $author_id ? get_userdata( $author_id ) : false;
		$payment_label  = self::payment_status_label( $status );
		$status_class   = 'mep-status-' . sanitize_html_class( $status );

		// PDF ticket download link — shown only when the PDF Ticket addon is active
		// AND the order has reached one of the statuses configured on the PDF Email
		// Settings page (the same rule that sends the ticket email). The native
		// "publish" status maps to the WooCommerce "completed" slug used in settings.
		$pdf_ticket_url = '';
		global $meppdf;
		if ( is_object( $meppdf ) && method_exists( $meppdf, 'get_invoice_ajax_url' ) ) {
			$status_slug      = ( 'publish' === $status ) ? 'completed' : $status;
			$pdf_email_status = function_exists( 'mep_get_pdf_email_statuses' ) ? mep_get_pdf_email_statuses() : array();
			$status_matched   = function_exists( 'mep_is_status_match_for_pdf_email' )
				? mep_is_status_match_for_pdf_email( $status_slug, $pdf_email_status )
				: in_array( $status_slug, (array) $pdf_email_status, true );
			if ( $status_matched ) {
				$pdf_ticket_url = $meppdf->get_invoice_ajax_url( array( 'order_id' => $order_id ) );
			}
		}
		?>
		<div class="mep-orders-wrap mep-order-detail-wrap">

			<div class="mep-od-header">
				<div class="mep-od-head-left">
					<span class="mep-od-head-icon">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>
					</span>
					<div class="mep-od-head-text">
						<h1 class="mep-od-title">
							<?php printf( esc_html__( 'Order #%d', 'mage-eventpress' ), $order_id ); ?>
							<span class="mep-status-pill <?php echo esc_attr( $status_class ); ?> mep-status-inline"><?php echo esc_html( $payment_label ); ?></span>
						</h1>
						<p class="mep-od-placed">
							<span class="dashicons dashicons-calendar-alt"></span>
							<?php esc_html_e( 'Placed on', 'mage-eventpress' ); ?>
							<?php echo esc_html( get_the_date( 'F j, Y \a\t g:i A', $order_id ) ); ?>
						</p>
					</div>
				</div>
				<div class="mep-od-head-actions">
					<?php if ( $pdf_ticket_url ) : ?>
						<a href="<?php echo esc_url( $pdf_ticket_url ); ?>" class="mep-od-pdf-btn" target="_blank" rel="noopener" title="<?php esc_attr_e( 'Download the PDF ticket for this order', 'mage-eventpress' ); ?>">
							<span class="dashicons dashicons-media-document"></span>
							<?php esc_html_e( 'Download PDF', 'mage-eventpress' ); ?>
						</a>
						<button type="button" class="mep-od-resend-btn" id="mep-resend-pdf" data-order="<?php echo esc_attr( $order_id ); ?>" title="<?php esc_attr_e( 'Re-send the PDF ticket email to the customer', 'mage-eventpress' ); ?>">
							<span class="dashicons dashicons-email-alt"></span>
							<?php esc_html_e( 'Resend PDF', 'mage-eventpress' ); ?>
						</button>
					<?php endif; ?>
					<button type="button" class="mep-od-icon-btn" id="mep-print-order" title="<?php esc_attr_e( 'Print', 'mage-eventpress' ); ?>">
						<span class="dashicons dashicons-printer"></span>
					</button>
					<button type="button" class="mep-od-icon-btn" id="mep-download-order" title="<?php esc_attr_e( 'Download / Save as PDF', 'mage-eventpress' ); ?>">
						<span class="dashicons dashicons-download"></span>
					</button>
					<a href="<?php echo esc_url( $back_url ); ?>" class="mep-od-icon-btn" title="<?php esc_attr_e( 'Back to Orders', 'mage-eventpress' ); ?>">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
					</a>
				</div>
			</div>

			<!-- Two-column layout: Main + Sidebar -->
			<div class="mep-order-layout">

				<!-- Main Content Column -->
				<div class="mep-order-main">

					<div class="mep-od-two-col">

					<!-- Order Details Card -->
					<div class="mep-detail-card">
						<div class="mep-detail-card-header">
							<span class="dashicons dashicons-info-outline"></span>
							<?php esc_html_e( 'Order Details', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-detail-card-body">
							<dl class="mep-dl">
								<dt><?php esc_html_e( 'Order ID', 'mage-eventpress' ); ?></dt>
								<dd class="mep-dd-strong">#<?php echo esc_html( $order_id ); ?></dd>

								<dt><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></dt>
								<dd><?php echo $event_id ? '<a href="' . esc_url( get_edit_post_link( $event_id ) ) . '">' . esc_html( $event_name ) . '</a>' : esc_html( $event_name ); ?></dd>

								<?php if ( $event_date ) : ?>
								<dt><?php esc_html_e( 'Event Date', 'mage-eventpress' ); ?></dt>
								<dd><?php echo esc_html( $event_date ); ?></dd>
								<?php endif; ?>

								<dt><?php esc_html_e( 'Payment Method', 'mage-eventpress' ); ?></dt>
								<dd><?php echo $gateway ? '<span class="mep-od-tag">' . esc_html( strtoupper( $gateway ) ) . '</span>' : '&mdash;'; ?></dd>

								<dt><?php esc_html_e( 'Order Total', 'mage-eventpress' ); ?></dt>
								<dd class="mep-dd-amount"><?php echo wp_kses_post( self::format_price( $total ) ); ?></dd>
							</dl>
						</div>
					</div>

					<!-- Purchased Tickets -->
					<div class="mep-detail-card">
						<div class="mep-detail-card-header">
							<span class="dashicons dashicons-tickets-alt"></span>
							<?php esc_html_e( 'Purchased Tickets', 'mage-eventpress' ); ?>
						</div>
						<table class="mep-od-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Ticket Type', 'mage-eventpress' ); ?></th>
									<th class="mep-od-num"><?php esc_html_e( 'Qty', 'mage-eventpress' ); ?></th>
									<th class="mep-od-num"><?php esc_html_e( 'Unit Price', 'mage-eventpress' ); ?></th>
									<th class="mep-od-num"><?php esc_html_e( 'Subtotal', 'mage-eventpress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( empty( $items ) ) : ?>
								<tr><td colspan="4" class="mep-od-empty-cell"><?php esc_html_e( 'No tickets recorded for this order.', 'mage-eventpress' ); ?></td></tr>
								<?php else : ?>
									<?php foreach ( $items as $item ) : ?>
									<tr>
										<td><?php echo esc_html( $item['name'] ?? '' ); ?></td>
										<td class="mep-od-num"><?php echo esc_html( $item['qty'] ?? 0 ); ?></td>
										<td class="mep-od-num"><?php echo wp_kses_post( self::format_price( $item['price'] ?? 0 ) ); ?></td>
										<td class="mep-od-num mep-od-strong"><?php echo wp_kses_post( self::format_price( $item['total'] ?? 0 ) ); ?></td>
									</tr>
									<?php endforeach; ?>
									<tr class="mep-od-grand">
										<td colspan="3"><?php esc_html_e( 'Grand Total', 'mage-eventpress' ); ?></td>
										<td class="mep-od-num"><?php echo wp_kses_post( self::format_price( $total ) ); ?></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					</div><!-- .mep-od-two-col -->

					<!-- Attendees List -->
					<div class="mep-detail-card">
						<div class="mep-detail-card-header">
							<span class="dashicons dashicons-groups"></span>
							<?php esc_html_e( 'Attendees List', 'mage-eventpress' ); ?>
						</div>
						<?php
						$attendee_ids = array_filter( $attendee_ids, function( $id ) {
							return 'mep_events_attendees' === get_post_type( $id );
						} );
						?>
						<table class="mep-od-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Name', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Ticket Details', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Reference', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Check-in', 'mage-eventpress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( empty( $attendee_ids ) ) : ?>
								<tr><td colspan="4" class="mep-od-empty-cell"><?php esc_html_e( 'No attendees recorded for this order yet.', 'mage-eventpress' ); ?></td></tr>
								<?php else : ?>
									<?php foreach ( $attendee_ids as $attendee_id ) :
										$checkin = get_post_meta( $attendee_id, 'mep_checkin', true );
										$a_name  = get_post_meta( $attendee_id, 'ea_name', true );
										$a_email = get_post_meta( $attendee_id, 'ea_email', true );
										$a_type  = get_post_meta( $attendee_id, 'ea_ticket_type', true );
										$a_qty   = get_post_meta( $attendee_id, 'ea_ticket_qty', true );
										$a_ref   = get_post_meta( $attendee_id, 'ea_ticket_no', true );
									?>
									<tr>
										<td>
											<a class="mep-od-att-name" href="<?php echo esc_url( get_edit_post_link( $attendee_id ) ); ?>"><?php echo esc_html( $a_name ?: '—' ); ?></a>
											<?php if ( $a_email ) : ?><span class="mep-od-att-email"><?php echo esc_html( $a_email ); ?></span><?php endif; ?>
										</td>
										<td>
											<span class="mep-od-att-type"><?php echo esc_html( $a_type ?: '—' ); ?></span>
											<span class="mep-od-att-qty"><?php printf( esc_html__( 'Qty: %s', 'mage-eventpress' ), esc_html( $a_qty !== '' ? $a_qty : '0' ) ); ?></span>
										</td>
										<td><span class="mep-od-ref"><?php echo esc_html( $a_ref ?: '—' ); ?></span></td>
										<td>
											<?php if ( $checkin ) : ?>
												<span class="mep-status-pill mep-status-publish"><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></span>
											<?php else : ?>
												<span class="mep-status-pill mep-status-cancelled"><?php esc_html_e( 'No', 'mage-eventpress' ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

				</div><!-- .mep-order-main -->

				<!-- Sidebar Column -->
				<div class="mep-order-sidebar">

					<!-- Order Actions / Status Change -->
					<div class="mep-sidebar-card">
						<div class="mep-sidebar-card-header">
							<span class="mep-od-bolt"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h7l-1 8 10-12h-7z"/></svg></span>
							<?php esc_html_e( 'Order Actions', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-sidebar-card-body">
							<div class="mep-sidebar-field">
								<label for="mep-order-status-select"><?php esc_html_e( 'Change Status', 'mage-eventpress' ); ?></label>
								<select id="mep-order-status-select" data-order-id="<?php echo esc_attr( $order_id ); ?>" class="mep-status-select mep-sidebar-select">
									<?php
									foreach ( self::get_order_statuses() as $key => $label ) :
									?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<button type="button" id="mep-update-status" class="mep-btn mep-btn-primary mep-sidebar-btn" data-order-id="<?php echo esc_attr( $order_id ); ?>">
								<?php esc_html_e( 'Update Order', 'mage-eventpress' ); ?>
							</button>
							<div id="mep-status-feedback" class="mep-status-feedback"></div>
						</div>
					</div>

					<!-- Customer Info -->
					<div class="mep-sidebar-card">
						<div class="mep-sidebar-card-header">
							<span class="dashicons dashicons-admin-users"></span>
							<?php esc_html_e( 'Customer', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-sidebar-card-body">
							<?php
							$cust_display = $customer_name ?: ( $author ? $author->display_name : '' );
							$initials     = '';
							if ( $cust_display ) {
								$np       = preg_split( '/\s+/', trim( $cust_display ) );
								$initials = mb_strtoupper( mb_substr( $np[0], 0, 1 ) . ( isset( $np[1] ) ? mb_substr( $np[1], 0, 1 ) : mb_substr( $np[0], 1, 1 ) ) );
							}
							$profile_url = $booker_id ? get_edit_user_link( $booker_id ) : ( $author_id ? get_edit_user_link( $author_id ) : '' );
							?>
							<div class="mep-cust-id">
								<span class="mep-cust-avatar"><?php echo esc_html( $initials ?: '#' ); ?></span>
								<div class="mep-cust-meta">
									<span class="mep-cust-name"><?php echo esc_html( $cust_display ?: '—' ); ?></span>
									<?php if ( $customer_email ) : ?>
										<a class="mep-cust-email" href="mailto:<?php echo esc_attr( $customer_email ); ?>"><?php echo esc_html( $customer_email ); ?></a>
									<?php endif; ?>
								</div>
							</div>
							<div class="mep-cust-row">
								<span class="dashicons dashicons-phone"></span>
								<?php echo $customer_phone ? esc_html( $customer_phone ) : '<span class="mep-cust-muted">' . esc_html__( 'Not provided', 'mage-eventpress' ) . '</span>'; ?>
							</div>
							<?php if ( $profile_url ) : ?>
							<a class="mep-cust-link" href="<?php echo esc_url( $profile_url ); ?>">
								<span class="dashicons dashicons-admin-site-alt3"></span>
								<?php esc_html_e( 'View Profile & History', 'mage-eventpress' ); ?>
							</a>
							<?php endif; ?>
						</div>
					</div>

					<!-- Order Notes & History -->
					<div class="mep-sidebar-card mep-notes-card">
						<div class="mep-sidebar-card-header">
							<span class="dashicons dashicons-menu-alt2"></span>
							<?php esc_html_e( 'Order Notes', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-sidebar-card-body">
							<!-- Add Note Form -->
							<div class="mep-add-note-form">
								<textarea id="mep-new-note" class="mep-note-textarea" rows="3" placeholder="<?php esc_attr_e( 'Add a private note...', 'mage-eventpress' ); ?>"></textarea>
								<div class="mep-note-form-actions">
									<select id="mep-note-type" class="mep-note-type-select">
										<option value="private"><?php esc_html_e( 'Private Note', 'mage-eventpress' ); ?></option>
										<option value="customer"><?php esc_html_e( 'Note to Customer', 'mage-eventpress' ); ?></option>
									</select>
									<button type="button" id="mep-add-note-btn" class="mep-btn mep-btn-primary mep-btn-sm" data-order-id="<?php echo esc_attr( $order_id ); ?>">
										<?php esc_html_e( 'Add', 'mage-eventpress' ); ?>
									</button>
								</div>
							</div>

							<!-- Notes List -->
							<ul class="mep-notes-list mep-notes-timeline order_notes" id="mep-notes-list">
								<?php
								$notes = self::get_order_notes( $order_id );
								if ( empty( $notes ) ) :
								?>
									<li class="mep-no-notes">
										<span class="dashicons dashicons-format-chat"></span>
										<p><?php esc_html_e( 'No notes yet.', 'mage-eventpress' ); ?></p>
									</li>
								<?php else : ?>
									<?php foreach ( $notes as $note ) : ?>
										<?php self::render_single_note( $note, $order_id ); ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
					</div>

				</div><!-- .mep-order-sidebar -->

			</div><!-- .mep-order-layout -->

		</div><!-- .mep-orders-wrap -->
		<?php
	}

	/* -----------------------------------------------------------------------
	 * Order Detail Page — WooCommerce orders
	 *
	 * Mirrors render_order_detail()'s native layout so both order sources share
	 * one consistent screen, but reads/writes the real WC_Order instead of
	 * mep_custom_order post meta — the "Change Status" control here updates the
	 * actual WooCommerce order, and Order Notes uses WooCommerce's own notes
	 * (wc_get_order_notes() / $order->add_order_note()), not the native
	 * comment-based mep_order_note system.
	 * -------------------------------------------------------------------- */
	private static function render_wc_order_detail( $order, $back_url ) {
		$order_id = $order->get_id();

		$event_id    = 0;
		$event_name  = '-';
		$event_names = array();
		foreach ( $order->get_items() as $item ) {
			$eid = (int) $item->get_meta( 'event_id' );
			if ( $eid ) {
				$event_id      = $event_id ?: $eid;
				$event_names[] = get_the_title( $eid );
			}
		}
		if ( ! empty( $event_names ) ) {
			$event_name = implode( ', ', array_unique( $event_names ) );
		}

		$customer_name  = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$customer_email = $order->get_billing_email();
		$customer_phone = $order->get_billing_phone();
		$gateway        = $order->get_payment_method_title();
		$status         = $order->get_status(); // no 'wc-' prefix
		$status_class   = 'mep-status-' . sanitize_html_class( $status );
		$payment_label  = wc_get_order_status_name( $status );

		$attendee_ids = get_posts( array(
			'post_type'      => 'mep_events_attendees',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => 'ea_order_id',
					'value' => $order_id,
				),
			),
		) );
		?>
		<div class="mep-orders-wrap mep-order-detail-wrap">

			<div class="mep-od-header">
				<div class="mep-od-head-left">
					<span class="mep-od-head-icon">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>
					</span>
					<div class="mep-od-head-text">
						<h1 class="mep-od-title">
							<?php printf( esc_html__( 'Order #%d', 'mage-eventpress' ), $order_id ); ?>
							<span class="mep-badge mep-badge-woo">WooCommerce</span>
							<span class="mep-status-pill <?php echo esc_attr( $status_class ); ?> mep-status-inline"><?php echo esc_html( $payment_label ); ?></span>
						</h1>
						<p class="mep-od-placed">
							<span class="dashicons dashicons-calendar-alt"></span>
							<?php esc_html_e( 'Placed on', 'mage-eventpress' ); ?>
							<?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date_i18n( 'F j, Y \a\t g:i A' ) : '-' ); ?>
						</p>
					</div>
				</div>
				<div class="mep-od-head-actions">
					<button type="button" class="mep-od-icon-btn" id="mep-print-order" title="<?php esc_attr_e( 'Print', 'mage-eventpress' ); ?>">
						<span class="dashicons dashicons-printer"></span>
					</button>
					<a href="<?php echo esc_url( $back_url ); ?>" class="mep-od-icon-btn" title="<?php esc_attr_e( 'Back to Orders', 'mage-eventpress' ); ?>">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
					</a>
				</div>
			</div>

			<!-- Two-column layout: Main + Sidebar -->
			<div class="mep-order-layout">

				<!-- Main Content Column -->
				<div class="mep-order-main">

					<div class="mep-od-two-col">

					<!-- Order Details Card -->
					<div class="mep-detail-card">
						<div class="mep-detail-card-header">
							<span class="dashicons dashicons-info-outline"></span>
							<?php esc_html_e( 'Order Details', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-detail-card-body">
							<dl class="mep-dl">
								<dt><?php esc_html_e( 'Order ID', 'mage-eventpress' ); ?></dt>
								<dd class="mep-dd-strong">#<?php echo esc_html( $order_id ); ?></dd>

								<dt><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></dt>
								<dd><?php echo $event_id ? '<a href="' . esc_url( get_edit_post_link( $event_id ) ) . '">' . esc_html( $event_name ) . '</a>' : esc_html( $event_name ); ?></dd>

								<dt><?php esc_html_e( 'Payment Method', 'mage-eventpress' ); ?></dt>
								<dd><?php echo $gateway ? '<span class="mep-od-tag">' . esc_html( strtoupper( $gateway ) ) . '</span>' : '&mdash;'; ?></dd>

								<dt><?php esc_html_e( 'Order Total', 'mage-eventpress' ); ?></dt>
								<dd class="mep-dd-amount"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></dd>
							</dl>
						</div>
					</div>

					<!-- Purchased Tickets -->
					<div class="mep-detail-card">
						<div class="mep-detail-card-header">
							<span class="dashicons dashicons-tickets-alt"></span>
							<?php esc_html_e( 'Purchased Tickets', 'mage-eventpress' ); ?>
						</div>
						<table class="mep-od-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Ticket Type', 'mage-eventpress' ); ?></th>
									<th class="mep-od-num"><?php esc_html_e( 'Qty', 'mage-eventpress' ); ?></th>
									<th class="mep-od-num"><?php esc_html_e( 'Subtotal', 'mage-eventpress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php $line_items = $order->get_items(); ?>
								<?php if ( empty( $line_items ) ) : ?>
								<tr><td colspan="3" class="mep-od-empty-cell"><?php esc_html_e( 'No tickets recorded for this order.', 'mage-eventpress' ); ?></td></tr>
								<?php else : ?>
									<?php foreach ( $line_items as $item ) : ?>
									<tr>
										<td><?php echo esc_html( $item->get_name() ); ?></td>
										<td class="mep-od-num"><?php echo esc_html( $item->get_quantity() ); ?></td>
										<td class="mep-od-num mep-od-strong"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
									</tr>
									<?php endforeach; ?>
									<tr class="mep-od-grand">
										<td colspan="2"><?php esc_html_e( 'Grand Total', 'mage-eventpress' ); ?></td>
										<td class="mep-od-num"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					</div><!-- .mep-od-two-col -->

					<!-- Attendees List -->
					<div class="mep-detail-card">
						<div class="mep-detail-card-header">
							<span class="dashicons dashicons-groups"></span>
							<?php esc_html_e( 'Attendees List', 'mage-eventpress' ); ?>
						</div>
						<table class="mep-od-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Name', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Ticket Details', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Reference', 'mage-eventpress' ); ?></th>
									<th><?php esc_html_e( 'Check-in', 'mage-eventpress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( empty( $attendee_ids ) ) : ?>
								<tr><td colspan="4" class="mep-od-empty-cell"><?php esc_html_e( 'No attendees recorded for this order yet.', 'mage-eventpress' ); ?></td></tr>
								<?php else : ?>
									<?php foreach ( $attendee_ids as $attendee_id ) :
										$checkin = get_post_meta( $attendee_id, 'mep_checkin', true );
										$a_name  = get_post_meta( $attendee_id, 'ea_name', true );
										$a_email = get_post_meta( $attendee_id, 'ea_email', true );
										$a_type  = get_post_meta( $attendee_id, 'ea_ticket_type', true );
										$a_qty   = get_post_meta( $attendee_id, 'ea_ticket_qty', true );
										$a_ref   = get_post_meta( $attendee_id, 'ea_ticket_no', true );
									?>
									<tr>
										<td>
											<a class="mep-od-att-name" href="<?php echo esc_url( get_edit_post_link( $attendee_id ) ); ?>"><?php echo esc_html( $a_name ?: '—' ); ?></a>
											<?php if ( $a_email ) : ?><span class="mep-od-att-email"><?php echo esc_html( $a_email ); ?></span><?php endif; ?>
										</td>
										<td>
											<span class="mep-od-att-type"><?php echo esc_html( $a_type ?: '—' ); ?></span>
											<span class="mep-od-att-qty"><?php printf( esc_html__( 'Qty: %s', 'mage-eventpress' ), esc_html( $a_qty !== '' ? $a_qty : '0' ) ); ?></span>
										</td>
										<td><span class="mep-od-ref"><?php echo esc_html( $a_ref ?: '—' ); ?></span></td>
										<td>
											<?php if ( $checkin ) : ?>
												<span class="mep-status-pill mep-status-publish"><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></span>
											<?php else : ?>
												<span class="mep-status-pill mep-status-cancelled"><?php esc_html_e( 'No', 'mage-eventpress' ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

				</div><!-- .mep-order-main -->

				<!-- Sidebar Column -->
				<div class="mep-order-sidebar">

					<!-- Order Actions / Status Change -->
					<div class="mep-sidebar-card">
						<div class="mep-sidebar-card-header">
							<span class="mep-od-bolt"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h7l-1 8 10-12h-7z"/></svg></span>
							<?php esc_html_e( 'Order Actions', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-sidebar-card-body">
							<div class="mep-sidebar-field">
								<label for="mep-order-status-select"><?php esc_html_e( 'Change Status', 'mage-eventpress' ); ?></label>
								<select id="mep-order-status-select" data-order-id="<?php echo esc_attr( $order_id ); ?>" class="mep-status-select mep-sidebar-select">
									<?php foreach ( wc_get_order_statuses() as $key => $label ) :
										$clean_key = str_replace( 'wc-', '', $key );
									?>
										<option value="<?php echo esc_attr( $clean_key ); ?>" <?php selected( $status, $clean_key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<button type="button" id="mep-update-status" class="mep-btn mep-btn-primary mep-sidebar-btn" data-order-id="<?php echo esc_attr( $order_id ); ?>">
								<?php esc_html_e( 'Update Order', 'mage-eventpress' ); ?>
							</button>
							<div id="mep-status-feedback" class="mep-status-feedback"></div>
						</div>
					</div>

					<!-- Customer Info -->
					<div class="mep-sidebar-card">
						<div class="mep-sidebar-card-header">
							<span class="dashicons dashicons-admin-users"></span>
							<?php esc_html_e( 'Customer', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-sidebar-card-body">
							<?php
							$initials = '';
							if ( $customer_name ) {
								$np       = preg_split( '/\s+/', trim( $customer_name ) );
								$initials = mb_strtoupper( mb_substr( $np[0], 0, 1 ) . ( isset( $np[1] ) ? mb_substr( $np[1], 0, 1 ) : mb_substr( $np[0], 1, 1 ) ) );
							}
							$customer_id = $order->get_customer_id();
							$profile_url = $customer_id ? get_edit_user_link( $customer_id ) : '';
							?>
							<div class="mep-cust-id">
								<span class="mep-cust-avatar"><?php echo esc_html( $initials ?: '#' ); ?></span>
								<div class="mep-cust-meta">
									<span class="mep-cust-name"><?php echo esc_html( $customer_name ?: '—' ); ?></span>
									<?php if ( $customer_email ) : ?>
										<a class="mep-cust-email" href="mailto:<?php echo esc_attr( $customer_email ); ?>"><?php echo esc_html( $customer_email ); ?></a>
									<?php endif; ?>
								</div>
							</div>
							<div class="mep-cust-row">
								<span class="dashicons dashicons-phone"></span>
								<?php echo $customer_phone ? esc_html( $customer_phone ) : '<span class="mep-cust-muted">' . esc_html__( 'Not provided', 'mage-eventpress' ) . '</span>'; ?>
							</div>
							<?php if ( $profile_url ) : ?>
							<a class="mep-cust-link" href="<?php echo esc_url( $profile_url ); ?>">
								<span class="dashicons dashicons-admin-site-alt3"></span>
								<?php esc_html_e( 'View Profile & History', 'mage-eventpress' ); ?>
							</a>
							<?php endif; ?>
						</div>
					</div>

					<!-- Order Notes & History (WooCommerce's own notes) -->
					<div class="mep-sidebar-card mep-notes-card">
						<div class="mep-sidebar-card-header">
							<span class="dashicons dashicons-menu-alt2"></span>
							<?php esc_html_e( 'Order Notes', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-sidebar-card-body">
							<!-- Add Note Form -->
							<div class="mep-add-note-form">
								<textarea id="mep-new-note" class="mep-note-textarea" rows="3" placeholder="<?php esc_attr_e( 'Add a private note...', 'mage-eventpress' ); ?>"></textarea>
								<div class="mep-note-form-actions">
									<select id="mep-note-type" class="mep-note-type-select">
										<option value="private"><?php esc_html_e( 'Private Note', 'mage-eventpress' ); ?></option>
										<option value="customer"><?php esc_html_e( 'Note to Customer', 'mage-eventpress' ); ?></option>
									</select>
									<button type="button" id="mep-add-note-btn" class="mep-btn mep-btn-primary mep-btn-sm" data-order-id="<?php echo esc_attr( $order_id ); ?>">
										<?php esc_html_e( 'Add', 'mage-eventpress' ); ?>
									</button>
								</div>
							</div>

							<!-- Notes List -->
							<ul class="mep-notes-list mep-notes-timeline order_notes" id="mep-notes-list">
								<?php
								$wc_notes = wc_get_order_notes( array( 'order_id' => $order_id ) );
								if ( empty( $wc_notes ) ) :
								?>
									<li class="mep-no-notes">
										<span class="dashicons dashicons-format-chat"></span>
										<p><?php esc_html_e( 'No notes yet.', 'mage-eventpress' ); ?></p>
									</li>
								<?php else : ?>
									<?php foreach ( $wc_notes as $wc_note ) : ?>
										<?php self::render_wc_note( $wc_note ); ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
					</div>

				</div><!-- .mep-order-sidebar -->

			</div><!-- .mep-order-layout -->

		</div><!-- .mep-orders-wrap -->
		<?php
	}

	/**
	 * Render one WooCommerce order note using the same markup as render_single_note()
	 * so both order sources share one visual style in the notes timeline.
	 */
	private static function render_wc_note( $note ) {
		$is_system   = 'system' === $note->added_by;
		$is_customer = (bool) $note->customer_note;
		$type_class  = $is_system ? 'system-note' : ( $is_customer ? 'customer-note' : 'private-note' );
		$date_str    = strtoupper( date_i18n( 'F j, Y \a\t g:i A', strtotime( $note->date_created->date( 'Y-m-d H:i:s' ) ) ) );
		?>
		<li class="mep-note-item note <?php echo esc_attr( $type_class ); ?>" data-note-id="<?php echo esc_attr( $note->id ); ?>" data-wc-note="1">
			<span class="mep-note-dot"></span>
			<div class="mep-note-body">
				<div class="mep-note-content">
					<?php if ( $is_customer ) : ?>
						<span class="mep-note-tag"><?php esc_html_e( 'Note to customer', 'mage-eventpress' ); ?></span>
					<?php endif; ?>
					<?php echo wpautop( wp_kses_post( $note->content ) ); ?>
				</div>
				<div class="mep-note-meta">
					<span class="mep-note-date"><?php echo esc_html( $date_str ); ?></span>
					<?php if ( ! $is_system ) : ?>
						<a href="#" class="mep-note-delete" data-note-id="<?php echo esc_attr( $note->id ); ?>" data-wc-note="1"><?php esc_html_e( 'Delete', 'mage-eventpress' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</li>
		<?php
	}
}
