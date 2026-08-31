<?php
/**
 * Order lookup for the "Event Orders" screen.
 *
 * The screen lists two different kinds of order side by side:
 *
 *   - native  : `mep_custom_order` posts written by the plugin's own checkout;
 *   - WooCommerce: orders that contain at least one line item carrying an
 *                  `event_id` item meta.
 *
 * The list used to be produced by loading *every* order of both kinds into a PHP
 * array, filtering it in PHP and then `array_slice()`ing 20 rows out of it. That
 * is O(all orders) for every page view, every AJAX filter and every keystroke —
 * on a store with real history it burns tens of thousands of queries and hundreds
 * of megabytes to show twenty rows, and eventually takes the screen down with
 * "There has been a critical error on this website."
 *
 * Everything here works the other way round: filtering, ordering, counting and
 * the revenue total happen in SQL, `LIMIT`/`OFFSET` is applied by the database,
 * and only the orders actually on screen are hydrated. Cost is O(page size).
 *
 * Both WooCommerce storage engines are supported: legacy post storage and High
 * Performance Order Storage (HPOS, `wp_wc_orders`).
 *
 * @package mage-eventpress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Orders_Query {

	/** Source key for the plugin's own orders. */
	const SRC_NATIVE = 'mep_custom_order';

	/** Source key for WooCommerce orders. */
	const SRC_WC = 'shop_order';

	/**
	 * Default (unset) filter set.
	 *
	 * @return array
	 */
	public static function default_filters() {
		return array(
			'search'    => '',
			'event_id'  => 0,
			'status'    => '',
			'gateway'   => '',
			'date_from' => '',
			'date_to'   => '',
			'source'    => 'all',
			// Internal: keyset-pagination cursor used by each(). Not a user filter.
			'before_id' => 0,
		);
	}

	/* ---------------------------------------------------------------------
	 * Public API
	 * ------------------------------------------------------------------ */

	/**
	 * One page of orders, plus the totals for the whole filtered set.
	 *
	 * @param array $filters  See default_filters().
	 * @param int   $page     1-based page number.
	 * @param int   $per_page Rows per page.
	 *
	 * @return array{rows:array,total:int,revenue:float}
	 */
	public static function get_page( $filters, $page = 1, $per_page = 20 ) {
		global $wpdb;

		$filters  = wp_parse_args( $filters, self::default_filters() );
		$page     = max( 1, (int) $page );
		$per_page = max( 1, (int) $per_page );
		$offset   = ( $page - 1 ) * $per_page;

		// The page itself only needs each branch's newest ($offset + $per_page)
		// rows, so that cap is pushed down into the UNION branches.
		$page_union = self::build_union( $filters, $offset + $per_page );
		if ( ! $page_union ) {
			return array( 'rows' => array(), 'total' => 0, 'revenue' => 0.0 );
		}

		$refs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, src FROM ({$page_union}) mep_page ORDER BY id DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);
		$refs = (array) $refs;

		// Count and revenue cover the whole filtered set, so they cannot use the
		// cap — but on a first page that is not even full there is nothing more
		// to count, and the second round trip is skipped entirely.
		if ( 1 === $page && count( $refs ) < $per_page ) {
			$total   = count( $refs );
			$revenue = 0.0;
			foreach ( $refs as $ref ) {
				$revenue += (float) ( isset( $ref['total'] ) ? $ref['total'] : 0 );
			}
			if ( ! $total ) {
				return array( 'rows' => array(), 'total' => 0, 'revenue' => 0.0 );
			}
			$totals_union = self::build_union( $filters );
			$revenue      = (float) $wpdb->get_var( "SELECT COALESCE(SUM(total),0) FROM ({$totals_union}) mep_agg" );
		} else {
			$totals_union = self::build_union( $filters );
			$totals       = $wpdb->get_row(
				"SELECT COUNT(*) AS c, COALESCE(SUM(total),0) AS revenue FROM ({$totals_union}) mep_agg",
				ARRAY_A
			);
			$total   = $totals ? (int) $totals['c'] : 0;
			$revenue = $totals ? (float) $totals['revenue'] : 0.0;
		}

		return array(
			'rows'    => self::hydrate( $refs ),
			'total'   => $total,
			'revenue' => $revenue,
		);
	}

	/**
	 * Walk every order matching the filters, in bounded batches.
	 *
	 * Used by the CSV export, which by definition needs the whole set. Memory
	 * stays flat because only one batch is hydrated at a time.
	 *
	 * @param array    $filters  See default_filters().
	 * @param callable $callback Receives one hydrated row at a time.
	 * @param int      $batch    Rows hydrated per round trip.
	 */
	public static function each( $filters, $callback, $batch = 200 ) {
		global $wpdb;

		$filters = wp_parse_args( $filters, self::default_filters() );
		$batch   = max( 1, (int) $batch );

		// Keyset ("seek") pagination rather than OFFSET: each round trip asks for
		// the next $batch orders *older than the last one seen*. OFFSET would make
		// the database re-scan and discard everything already exported, turning a
		// full export into quadratic work.
		$cursor = 0;

		do {
			$filters['before_id'] = $cursor;

			$union = self::build_union( $filters, $batch );
			if ( ! $union ) {
				return;
			}

			$refs = (array) $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, src FROM ({$union}) mep_stream ORDER BY id DESC LIMIT %d",
					$batch
				),
				ARRAY_A
			);

			foreach ( self::hydrate( $refs ) as $row ) {
				call_user_func( $callback, $row );
			}

			$fetched = count( $refs );
			if ( $fetched ) {
				$last   = end( $refs );
				$cursor = (int) $last['id'];
			}

			self::release_caches();
		} while ( $fetched === $batch );
	}

	/**
	 * Id + title of every published event, for the filter dropdown.
	 *
	 * Deliberately not get_posts(): the dropdown needs two columns, not whole
	 * post objects with their meta primed.
	 *
	 * @return array<int,string> Event id => title.
	 */
	public static function get_event_options() {
		global $wpdb;

		$rows = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts}
			 WHERE post_type = 'mep_events' AND post_status = 'publish'
			 ORDER BY post_title ASC",
			ARRAY_A
		);

		$out = array();
		foreach ( (array) $rows as $row ) {
			$out[ (int) $row['ID'] ] = $row['post_title'];
		}
		return $out;
	}

	/**
	 * Distinct payment gateways seen on native orders, for the filter dropdown.
	 *
	 * Cached: it is a DISTINCT over postmeta, and the list changes only when a
	 * new gateway is used for the first time.
	 *
	 * @return string[]
	 */
	public static function get_gateway_options() {
		$cached = get_transient( 'mep_orders_gateways' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$rows = $wpdb->get_col(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta}
			 WHERE meta_key = '_mep_payment_gateway' AND meta_value != ''"
		);
		$rows = array_values( array_filter( (array) $rows ) );

		set_transient( 'mep_orders_gateways', $rows, HOUR_IN_SECONDS );
		return $rows;
	}

	/**
	 * Drop the cached gateway list (a new gateway has been recorded).
	 */
	public static function flush_gateway_options() {
		delete_transient( 'mep_orders_gateways' );
	}

	/* ---------------------------------------------------------------------
	 * Query building
	 * ------------------------------------------------------------------ */

	/**
	 * Build the derived table that both the count and the page query select from.
	 *
	 * Each branch yields the same three columns — id, src, total — so the two
	 * order stores can be paged and summed as one list.
	 *
	 * @param array $filters
	 * @return string Prepared SQL, or '' when nothing can match.
	 */
	private static function build_union( $filters, $cap = 0 ) {
		$parts = array();

		if ( in_array( $filters['source'], array( 'all', self::SRC_NATIVE ), true ) ) {
			$sql = self::native_select( $filters, $cap );
			if ( $sql ) {
				$parts[] = $cap ? "({$sql})" : $sql;
			}
		}

		if ( in_array( $filters['source'], array( 'all', self::SRC_WC ), true ) && self::has_woocommerce() ) {
			$sql = self::hpos_enabled() ? self::wc_hpos_select( $filters, $cap ) : self::wc_legacy_select( $filters, $cap );
			if ( $sql ) {
				$parts[] = $cap ? "({$sql})" : $sql;
			}
		}

		if ( ! $parts ) {
			return '';
		}

		return implode( ' UNION ALL ', $parts );
	}

	/**
	 * `ORDER BY id DESC LIMIT n` for one UNION branch.
	 *
	 * The page query only ever needs the newest `offset + per_page` rows, and the
	 * global newest N can only come from each branch's own newest N. Capping each
	 * branch lets the database stop early instead of materialising the whole order
	 * history just to throw all but twenty rows away.
	 *
	 * @param int $cap 0 for no cap.
	 * @return string
	 */
	private static function branch_tail( $cap ) {
		return $cap > 0 ? ' ORDER BY id DESC LIMIT ' . (int) $cap : '';
	}

	/**
	 * Native (`mep_custom_order`) branch.
	 *
	 * @param array $filters
	 * @return string
	 */
	private static function native_select( $filters, $cap = 0 ) {
		global $wpdb;

		$joins = array(
			"LEFT JOIN {$wpdb->postmeta} mep_t ON mep_t.post_id = p.ID AND mep_t.meta_key = '_mep_order_total'",
		);
		$where = array( "p.post_type = 'mep_custom_order'" );
		if ( ! empty( $filters['before_id'] ) ) {
			$where[] = 'p.ID < ' . absint( $filters['before_id'] );
		}

		// Placeholders are bound in the order they appear in the finished
		// statement — every JOIN comes before the WHERE — so the two sets of
		// arguments are collected separately and merged in that same order.
		$join_args  = array();
		$where_args = array();

		if ( $filters['event_id'] ) {
			$joins[]     = "INNER JOIN {$wpdb->postmeta} mep_e ON mep_e.post_id = p.ID AND mep_e.meta_key = '_mep_event_id' AND mep_e.meta_value = %s";
			$join_args[] = (string) absint( $filters['event_id'] );
		}

		if ( $filters['gateway'] ) {
			$joins[]     = "INNER JOIN {$wpdb->postmeta} mep_g ON mep_g.post_id = p.ID AND mep_g.meta_key = '_mep_payment_gateway' AND mep_g.meta_value = %s";
			$join_args[] = (string) $filters['gateway'];
		}

		// Free-text search over customer name / email / order id. The Customer
		// column prefers the linked WP user's details over the stored billing
		// meta, so the search has to look at the same resolved values.
		if ( $filters['search'] ) {
			$joins[] = "LEFT JOIN {$wpdb->postmeta} mep_u ON mep_u.post_id = p.ID AND mep_u.meta_key = '_mep_user_id'";
			$joins[] = "LEFT JOIN {$wpdb->users} mep_usr ON mep_usr.ID = mep_u.meta_value";
			$joins[] = "LEFT JOIN {$wpdb->postmeta} mep_n ON mep_n.post_id = p.ID AND mep_n.meta_key = '_mep_customer_name'";
			$joins[] = "LEFT JOIN {$wpdb->postmeta} mep_m ON mep_m.post_id = p.ID AND mep_m.meta_key = '_mep_customer_email'";
		}

		// Status. Mirrors WP_Query's post_status => 'any', which excludes only
		// statuses registered as exclude_from_search (trash, auto-draft, …).
		if ( $filters['status'] ) {
			// Native orders store "Completed" as the post status `publish` — the
			// same equivalence get_order_statuses() and the WooCommerce branch use.
			$status       = sanitize_key( $filters['status'] );
			$where[]      = 'p.post_status = %s';
			$where_args[] = ( 'completed' === $status ) ? 'publish' : $status;
		} else {
			$excluded = get_post_stati( array( 'exclude_from_search' => true ) );
			if ( $excluded ) {
				$where[]    = 'p.post_status NOT IN (' . self::placeholders( $excluded ) . ')';
				$where_args = array_merge( $where_args, array_values( $excluded ) );
			}
		}

		$range = self::date_range( $filters );
		if ( $range['from'] ) {
			$where[]      = 'p.post_date >= %s';
			$where_args[] = $range['from'];
		}
		if ( $range['to'] ) {
			$where[]      = 'p.post_date <= %s';
			$where_args[] = $range['to'];
		}

		if ( $filters['search'] ) {
			$like         = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
			$where[]      = '( COALESCE(mep_usr.display_name, mep_n.meta_value) LIKE %s'
				. ' OR COALESCE(mep_usr.user_email, mep_m.meta_value) LIKE %s'
				. ' OR CAST(p.ID AS CHAR) LIKE %s )';
			$where_args[] = $like;
			$where_args[] = $like;
			$where_args[] = $like;
		}

		$sql = "SELECT p.ID AS id, '" . self::SRC_NATIVE . "' AS src, COALESCE(mep_t.meta_value + 0, 0) AS total
			FROM {$wpdb->posts} p
			" . implode( "\n", $joins ) . "
			WHERE " . implode( ' AND ', $where ) . self::branch_tail( $cap );

		return self::prepare( $sql, array_merge( $join_args, $where_args ) );
	}

	/**
	 * WooCommerce branch, legacy post storage.
	 *
	 * @param array $filters
	 * @return string
	 */
	private static function wc_legacy_select( $filters, $cap = 0 ) {
		global $wpdb;

		$items    = $wpdb->prefix . 'woocommerce_order_items';
		$itemmeta = $wpdb->prefix . 'woocommerce_order_itemmeta';

		$joins = array(
			"LEFT JOIN {$wpdb->postmeta} mep_ot ON mep_ot.post_id = p.ID AND mep_ot.meta_key = '_order_total'",
		);
		$where = array( "p.post_type = 'shop_order'" );
		if ( ! empty( $filters['before_id'] ) ) {
			$where[] = 'p.ID < ' . absint( $filters['before_id'] );
		}

		// See native_select(): JOIN placeholders bind before WHERE placeholders.
		$join_args  = array();
		$where_args = array();

		$statuses = self::wc_statuses( $filters['status'] );
		if ( ! $statuses ) {
			return '';
		}

		if ( $filters['gateway'] ) {
			$joins[]     = "INNER JOIN {$wpdb->postmeta} mep_pm ON mep_pm.post_id = p.ID AND mep_pm.meta_key = '_payment_method_title' AND mep_pm.meta_value = %s";
			$join_args[] = (string) $filters['gateway'];
		}

		if ( $filters['search'] ) {
			$joins[] = "LEFT JOIN {$wpdb->postmeta} mep_bf ON mep_bf.post_id = p.ID AND mep_bf.meta_key = '_billing_first_name'";
			$joins[] = "LEFT JOIN {$wpdb->postmeta} mep_bl ON mep_bl.post_id = p.ID AND mep_bl.meta_key = '_billing_last_name'";
			$joins[] = "LEFT JOIN {$wpdb->postmeta} mep_be ON mep_be.post_id = p.ID AND mep_be.meta_key = '_billing_email'";
		}

		$where[]    = 'p.post_status IN (' . self::placeholders( $statuses ) . ')';
		$where_args = array_merge( $where_args, $statuses );

		// "Has at least one event line item", as a semi-join. EXISTS stops at the
		// first matching item and — unlike joining the item tables in — cannot
		// duplicate an order that books several events, so no GROUP BY is needed.
		$event_clause = '';
		if ( $filters['event_id'] ) {
			$event_clause = ' AND mep_oim.meta_value = %s';
		}
		$where[] = "EXISTS ( SELECT 1 FROM {$items} mep_oi
			INNER JOIN {$itemmeta} mep_oim ON mep_oim.order_item_id = mep_oi.order_item_id
			WHERE mep_oi.order_id = p.ID
			  AND mep_oim.meta_key = 'event_id' AND mep_oim.meta_value <> '' AND mep_oim.meta_value <> '0'"
			. $event_clause . ' )';
		if ( $filters['event_id'] ) {
			$where_args[] = (string) absint( $filters['event_id'] );
		}

		$range = self::date_range( $filters );
		if ( $range['from'] ) {
			$where[]      = 'p.post_date >= %s';
			$where_args[] = $range['from'];
		}
		if ( $range['to'] ) {
			$where[]      = 'p.post_date <= %s';
			$where_args[] = $range['to'];
		}

		if ( $filters['search'] ) {
			$like         = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
			$where[]      = "( TRIM(CONCAT_WS(' ', mep_bf.meta_value, mep_bl.meta_value)) LIKE %s"
				. ' OR mep_be.meta_value LIKE %s'
				. ' OR CAST(p.ID AS CHAR) LIKE %s )';
			$where_args[] = $like;
			$where_args[] = $like;
			$where_args[] = $like;
		}

		$sql = "SELECT p.ID AS id, '" . self::SRC_WC . "' AS src, COALESCE(mep_ot.meta_value + 0, 0) AS total
			FROM {$wpdb->posts} p
			" . implode( "\n", $joins ) . "
			WHERE " . implode( ' AND ', $where ) . self::branch_tail( $cap );

		return self::prepare( $sql, array_merge( $join_args, $where_args ) );
	}

	/**
	 * WooCommerce branch, High Performance Order Storage.
	 *
	 * @param array $filters
	 * @return string
	 */
	private static function wc_hpos_select( $filters, $cap = 0 ) {
		global $wpdb;

		$orders    = $wpdb->prefix . 'wc_orders';
		$addresses = $wpdb->prefix . 'wc_order_addresses';
		$items     = $wpdb->prefix . 'woocommerce_order_items';
		$itemmeta  = $wpdb->prefix . 'woocommerce_order_itemmeta';

		$joins = array();
		$where = array( "o.type = 'shop_order'" );
		if ( ! empty( $filters['before_id'] ) ) {
			$where[] = 'o.id < ' . absint( $filters['before_id'] );
		}

		// See native_select(): JOIN placeholders bind before WHERE placeholders.
		$join_args  = array();
		$where_args = array();

		$statuses = self::wc_statuses( $filters['status'] );
		if ( ! $statuses ) {
			return '';
		}

		if ( $filters['search'] ) {
			$joins[] = "LEFT JOIN {$addresses} mep_ba ON mep_ba.order_id = o.id AND mep_ba.address_type = 'billing'";
		}

		$where[]    = 'o.status IN (' . self::placeholders( $statuses ) . ')';
		$where_args = array_merge( $where_args, $statuses );

		// See wc_legacy_select(): semi-join on "has an event line item".
		$event_clause = $filters['event_id'] ? ' AND mep_oim.meta_value = %s' : '';
		$where[]      = "EXISTS ( SELECT 1 FROM {$items} mep_oi
			INNER JOIN {$itemmeta} mep_oim ON mep_oim.order_item_id = mep_oi.order_item_id
			WHERE mep_oi.order_id = o.id
			  AND mep_oim.meta_key = 'event_id' AND mep_oim.meta_value <> '' AND mep_oim.meta_value <> '0'"
			. $event_clause . ' )';
		if ( $filters['event_id'] ) {
			$where_args[] = (string) absint( $filters['event_id'] );
		}

		if ( $filters['gateway'] ) {
			$where[]      = 'o.payment_method_title = %s';
			$where_args[] = (string) $filters['gateway'];
		}

		// HPOS stores only a GMT creation date, so the site-local filter bounds
		// are converted before comparison.
		$range = self::date_range( $filters );
		if ( $range['from'] ) {
			$where[]      = 'o.date_created_gmt >= %s';
			$where_args[] = get_gmt_from_date( $range['from'] );
		}
		if ( $range['to'] ) {
			$where[]      = 'o.date_created_gmt <= %s';
			$where_args[] = get_gmt_from_date( $range['to'] );
		}

		if ( $filters['search'] ) {
			$like         = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
			$where[]      = "( TRIM(CONCAT_WS(' ', mep_ba.first_name, mep_ba.last_name)) LIKE %s"
				. ' OR COALESCE(mep_ba.email, o.billing_email) LIKE %s'
				. ' OR CAST(o.id AS CHAR) LIKE %s )';
			$where_args[] = $like;
			$where_args[] = $like;
			$where_args[] = $like;
		}

		$sql = "SELECT o.id AS id, '" . self::SRC_WC . "' AS src, COALESCE(o.total_amount + 0, 0) AS total
			FROM {$orders} o
			" . implode( "\n", $joins ) . "
			WHERE " . implode( ' AND ', $where ) . self::branch_tail( $cap );

		return self::prepare( $sql, array_merge( $join_args, $where_args ) );
	}

	/* ---------------------------------------------------------------------
	 * Hydration
	 * ------------------------------------------------------------------ */

	/**
	 * Turn a list of {id, src} references into the row shape the table renderer
	 * expects. Only the references handed in are loaded.
	 *
	 * @param array $refs Ordered list of array{id:int|string,src:string}.
	 * @return array
	 */
	private static function hydrate( $refs ) {
		if ( ! $refs ) {
			return array();
		}

		$native_ids = array();
		$wc_ids     = array();
		foreach ( $refs as $ref ) {
			if ( self::SRC_WC === $ref['src'] ) {
				$wc_ids[] = (int) $ref['id'];
			} else {
				$native_ids[] = (int) $ref['id'];
			}
		}

		$native = $native_ids ? self::hydrate_native( $native_ids ) : array();
		$wc     = $wc_ids ? self::hydrate_wc( $wc_ids ) : array();

		// Re-assemble in the order the SQL returned.
		$rows = array();
		foreach ( $refs as $ref ) {
			$id  = (int) $ref['id'];
			$src = self::SRC_WC === $ref['src'] ? self::SRC_WC : self::SRC_NATIVE;
			if ( self::SRC_WC === $src && isset( $wc[ $id ] ) ) {
				$rows[] = $wc[ $id ];
			} elseif ( self::SRC_NATIVE === $src && isset( $native[ $id ] ) ) {
				$rows[] = $native[ $id ];
			}
		}
		return $rows;
	}

	/**
	 * Hydrate native orders. One cache-priming query covers the whole batch, so
	 * every get_post_meta() below is served from memory.
	 *
	 * @param int[] $ids
	 * @return array<int,array>
	 */
	private static function hydrate_native( $ids ) {
		_prime_post_caches( $ids, false, true );

		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$rows        = array();

		foreach ( $ids as $id ) {
			$event_id   = (int) get_post_meta( $id, '_mep_event_id', true );
			$event_name = $event_id ? get_the_title( $event_id ) : '-';
			$total_raw  = (float) get_post_meta( $id, '_mep_order_total', true );

			// The Customer column always prefers the linked WP user's account
			// details, so it stays clearly distinct from attendee form values.
			$booker_id = (int) get_post_meta( $id, '_mep_user_id', true );
			$booker    = $booker_id ? get_userdata( $booker_id ) : false;

			$raw_status = get_post_status( $id );

			$rows[ $id ] = array(
				'ID'             => $id,
				'source'         => self::SRC_NATIVE,
				'order_status'   => self::status_label( $raw_status ),
				'raw_status'     => $raw_status,
				'customer_name'  => $booker ? $booker->display_name : get_post_meta( $id, '_mep_customer_name', true ),
				'customer_email' => $booker ? $booker->user_email : get_post_meta( $id, '_mep_customer_email', true ),
				'customer_phone' => get_post_meta( $id, '_mep_customer_phone', true ),
				'event_id'       => $event_id,
				'event'          => $event_name,
				'total_raw'      => $total_raw,
				'total'          => self::format_price( $total_raw ),
				'gateway'        => get_post_meta( $id, '_mep_payment_gateway', true ) ?: '-',
				'date'           => get_the_date( $date_format, $id ),
				'timestamp'      => get_post_time( 'U', true, $id ),
				'attendee_info'  => (array) get_post_meta( $id, '_mep_attendee_form_fields', true ),
			);
		}

		return $rows;
	}

	/**
	 * Hydrate WooCommerce orders.
	 *
	 * @param int[] $ids
	 * @return array<int,array>
	 */
	private static function hydrate_wc( $ids ) {
		// Legacy storage keeps orders in wp_posts/wp_postmeta, so one priming
		// query serves the whole batch instead of two per order. Under HPOS the
		// order rows live elsewhere and this is a no-op worth skipping.
		if ( ! self::hpos_enabled() ) {
			_prime_post_caches( $ids, false, true );
		}

		$event_map = self::event_titles_for_orders( $ids );
		$rows      = array();
		$format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		foreach ( $ids as $id ) {
			$order = wc_get_order( $id );
			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$created = $order->get_date_created();
			$events  = isset( $event_map[ $id ] ) ? $event_map[ $id ] : array( 'ids' => array(), 'titles' => array() );

			$rows[ $id ] = array(
				'ID'             => $id,
				'source'         => self::SRC_WC,
				'order_status'   => wc_get_order_status_name( $order->get_status() ),
				'raw_status'     => $order->get_status(),
				'customer_name'  => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
				'customer_email' => $order->get_billing_email(),
				'customer_phone' => $order->get_billing_phone(),
				'event_id'       => $events['ids'] ? (int) end( $events['ids'] ) : 0,
				'event'          => $events['titles'] ? implode( ', ', $events['titles'] ) : '-',
				'total_raw'      => (float) $order->get_total(),
				'total'          => $order->get_formatted_order_total(),
				'gateway'        => $order->get_payment_method_title() ?: '-',
				'date'           => $created ? $created->date_i18n( $format ) : '-',
				'timestamp'      => $created ? $created->getTimestamp() : 0,
				'attendee_info'  => array(),
			);
		}

		return $rows;
	}

	/**
	 * Event ids and titles for a batch of WooCommerce orders, read straight from
	 * the order item meta store in one query rather than by hydrating every
	 * order's line items.
	 *
	 * @param int[] $order_ids
	 * @return array<int,array{ids:int[],titles:string[]}>
	 */
	private static function event_titles_for_orders( $order_ids ) {
		global $wpdb;

		$order_ids = array_map( 'absint', $order_ids );
		if ( ! $order_ids ) {
			return array();
		}

		$items    = $wpdb->prefix . 'woocommerce_order_items';
		$itemmeta = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$in       = implode( ',', $order_ids );

		$rows = $wpdb->get_results(
			"SELECT oi.order_id, oim.meta_value AS event_id
			 FROM {$items} oi
			 INNER JOIN {$itemmeta} oim ON oim.order_item_id = oi.order_item_id
			 WHERE oi.order_id IN ({$in})
			   AND oim.meta_key = 'event_id' AND oim.meta_value <> '' AND oim.meta_value <> '0'
			 ORDER BY oi.order_item_id ASC",
			ARRAY_A
		);

		$event_ids = array();
		foreach ( (array) $rows as $row ) {
			$event_ids[] = (int) $row['event_id'];
		}
		if ( $event_ids ) {
			// One query for every event title on the page.
			_prime_post_caches( array_values( array_unique( $event_ids ) ), false, false );
		}

		$out = array();
		foreach ( (array) $rows as $row ) {
			$oid = (int) $row['order_id'];
			$eid = (int) $row['event_id'];
			if ( ! isset( $out[ $oid ] ) ) {
				$out[ $oid ] = array( 'ids' => array(), 'titles' => array() );
			}
			$out[ $oid ]['ids'][]    = $eid;
			$out[ $oid ]['titles'][] = get_the_title( $eid );
		}
		return $out;
	}

	/* ---------------------------------------------------------------------
	 * Small helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Order statuses to match, mirroring wc_get_orders()'s own handling of
	 * `status => any` so the list is identical to what it used to return.
	 *
	 * @param string $filter_status Requested status, '' for all.
	 * @return string[]
	 */
	private static function wc_statuses( $filter_status ) {
		if ( $filter_status ) {
			// The native list stores "Completed" as `publish`; WooCommerce uses `wc-completed`.
			$status = ( 'publish' === $filter_status ) ? 'completed' : sanitize_key( $filter_status );
			return array( 'wc-' . $status );
		}

		$valid   = array_keys( wc_get_order_statuses() );
		$exclude = get_post_stati( array( 'exclude_from_search' => true ) );

		return array_values( array_diff( $valid, $exclude ) );
	}

	/**
	 * Normalise the Y-m-d filter bounds into full datetime bounds.
	 *
	 * @param array $filters
	 * @return array{from:string,to:string}
	 */
	private static function date_range( $filters ) {
		$from = '';
		$to   = '';

		if ( ! empty( $filters['date_from'] ) ) {
			$ts = strtotime( $filters['date_from'] );
			if ( $ts ) {
				$from = gmdate( 'Y-m-d 00:00:00', $ts );
			}
		}
		if ( ! empty( $filters['date_to'] ) ) {
			$ts = strtotime( $filters['date_to'] );
			if ( $ts ) {
				$to = gmdate( 'Y-m-d 23:59:59', $ts );
			}
		}

		return array( 'from' => $from, 'to' => $to );
	}

	/**
	 * Comma-separated %s list for an IN () clause.
	 *
	 * @param array $values
	 * @return string
	 */
	private static function placeholders( $values ) {
		return implode( ',', array_fill( 0, count( $values ), '%s' ) );
	}

	/**
	 * $wpdb->prepare(), tolerating a placeholder-free statement.
	 *
	 * @param string $sql
	 * @param array  $args
	 * @return string
	 */
	private static function prepare( $sql, $args ) {
		global $wpdb;
		return $args ? $wpdb->prepare( $sql, $args ) : $sql;
	}

	/**
	 * Human label for a native order's post status.
	 *
	 * @param string $post_status
	 * @return string
	 */
	private static function status_label( $post_status ) {
		if ( 'trash' === $post_status ) {
			return __( 'Cancelled', 'mage-eventpress' );
		}
		$map = MEP_Custom_Orders_Page::get_order_statuses();
		return isset( $map[ $post_status ] ) ? $map[ $post_status ] : ucfirst( $post_status );
	}

	/**
	 * @param float $amount
	 * @return string
	 */
	private static function format_price( $amount ) {
		return class_exists( 'MPWEM_Global_Function' ) && method_exists( 'MPWEM_Global_Function', 'mep_format_price' )
			? MPWEM_Global_Function::mep_format_price( $amount )
			: number_format( (float) $amount, 2 );
	}

	/**
	 * @return bool
	 */
	private static function has_woocommerce() {
		return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_order' );
	}

	/**
	 * @return bool True when WooCommerce keeps orders in its own tables.
	 */
	private static function hpos_enabled() {
		return class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}

	/**
	 * Release the caches a long export walk fills up. Releasing each WC_Order is
	 * not enough on its own — WooCommerce also keeps them in the "orders" cache
	 * group. Guarded: flush_group support depends on the object-cache backend.
	 */
	private static function release_caches() {
		if ( function_exists( 'wp_cache_supports' ) && wp_cache_supports( 'flush_group' ) ) {
			wp_cache_flush_group( 'orders' );
		}
	}
}
