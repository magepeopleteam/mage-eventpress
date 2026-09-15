<?php
	/**
	 * Zero-price event order audit for Event Booking Manager for WooCommerce.
	 *
	 * READ ONLY. Nothing in here writes to the database.
	 *
	 * This file is never loaded by the plugin - it is not in any include path. Run it
	 * explicitly:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tools/audit-zero-price-event-orders.php
	 *   wp eval-file .../audit-zero-price-event-orders.php 2026-01-01        # orders from a date
	 *   wp eval-file .../audit-zero-price-event-orders.php 2026-01-01 csv    # machine readable
	 *
	 * WHY THIS EXISTS
	 * Up to and including 5.7.0, an event's hidden helper product could reach the cart
	 * with no ticket selected - a bare add-to-cart link, a Store API request, an order
	 * again - and the line was then priced at 0.00, so it checked out as a free order.
	 * That worked for draft and expired events too. A switched-off 0.00 ticket type
	 * could also be booked by posting its name. The fix stops new ones; this finds the
	 * ones already placed. Every event order line that matches is printed as:
	 *
	 *   TICKETLESS  no ticket on the line has a quantity. Nobody chose a ticket and
	 *               nothing was paid. Such a line normally produces no attendee and holds
	 *               no seat, but the order exists, and order automation may have
	 *               completed it and emailed the buyer.
	 *   OFF-TYPE    the line was free and every ticket on it is of a type that is switched
	 *               off today. Either a switched-off free ticket booked by name, or a type
	 *               the organiser disabled after a genuine sale - compare the order date
	 *               with when that ticket type was switched off. Unlike a TICKETLESS line
	 *               it normally did create attendees, so it holds seats and its tickets
	 *               were issued.
	 *
	 * Lines that fit neither are not listed. The flagged lines are also grouped by
	 * created_via: "store-api" means the Store API (or a block checkout) placed the order.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! function_exists( 'wc_get_order' ) ) {
		die( "WooCommerce is not active.\n" );
	}

	/**
	 * A ticket type name decoded the way the cart compares names.
	 *
	 * @param string $name Raw name.
	 * @return string
	 */
	function mep_zpa_decode( $name ) {
		return html_entity_decode( urldecode( (string) $name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

	/**
	 * The event's ticket types as they are configured today, decoded name => enabled.
	 *
	 * @param int $event_id Event post id.
	 * @return array<string,bool>
	 */
	function mep_zpa_ticket_types( $event_id ) {
		static $cache = array();
		$event_id = (int) $event_id;
		if ( array_key_exists( $event_id, $cache ) ) {
			return $cache[ $event_id ];
		}
		$types = array();
		$rows  = get_post_meta( $event_id, 'mep_event_ticket_type', true );
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! is_array( $row ) || empty( $row['option_name_t'] ) ) {
					continue;
				}
				// Same default as the event page: a type without the flag is on sale.
				$types[ mep_zpa_decode( $row['option_name_t'] ) ] = ! array_key_exists( 'option_ticket_enable', $row ) || 'yes' === $row['option_ticket_enable'];
			}
		}
		$cache[ $event_id ] = $types;

		return $types;
	}

	/**
	 * Verdict for one event order line.
	 *
	 * @param int   $event_id    Event post id.
	 * @param mixed $ticket_info Unserialised _event_ticket_info.
	 * @param mixed $line_total  _line_total.
	 * @return string 'TICKETLESS', 'OFF-TYPE', or '' when the line is not suspicious.
	 */
	function mep_zpa_verdict( $event_id, $ticket_info, $line_total ) {
		$chosen = array();
		if ( is_array( $ticket_info ) ) {
			foreach ( $ticket_info as $ticket ) {
				if ( is_array( $ticket ) && ! empty( $ticket['ticket_name'] ) && isset( $ticket['ticket_qty'] ) && (int) $ticket['ticket_qty'] > 0 ) {
					// The cart stores the posted name, which may carry an "_<index>" suffix.
					$parts    = explode( '_', (string) $ticket['ticket_name'] );
					$chosen[] = mep_zpa_decode( $parts[0] );
				}
			}
		}
		if ( ! $chosen ) {
			return 'TICKETLESS';
		}
		if ( abs( (float) $line_total ) >= 0.005 ) {
			return '';
		}
		$types = mep_zpa_ticket_types( $event_id );
		foreach ( $chosen as $name ) {
			// A type that is on sale, or one renamed or removed since, cannot be judged.
			if ( ! array_key_exists( $name, $types ) || $types[ $name ] ) {
				return '';
			}
		}

		return 'OFF-TYPE';
	}

	/**
	 * Attendee posts stored for an order, across all of its events.
	 *
	 * @param int $order_id Order id.
	 * @return int
	 */
	function mep_zpa_attendees( $order_id ) {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = 'ea_order_id' AND pm.meta_value = %s
			AND p.post_type = 'mep_events_attendees' AND p.post_status <> 'trash'",
			(string) $order_id
		) );
	}

	global $wpdb;
	$args     = isset( $args ) && is_array( $args ) ? $args : array();
	$csv      = in_array( 'csv', $args, true ) || in_array( '--csv', $args, true );
	$args     = array_values( array_diff( $args, array( 'csv', '--csv' ) ) );
	$after_ts = isset( $args[0] ) ? strtotime( $args[0] . ' 00:00:00' ) : 0;
	if ( isset( $args[0] ) && ! $after_ts ) {
		die( "Unrecognised date: {$args[0]} - use YYYY-MM-DD.\n" );
	}

	// Order line items live in these two tables under both HPOS and post storage, so the
	// scan never has to load an order unless one of its lines is flagged.
	$items_table = $wpdb->prefix . 'woocommerce_order_items';
	$meta_table  = $wpdb->prefix . 'woocommerce_order_itemmeta';
	$batch       = 1000;
	$last_id     = 0;
	$scanned     = 0;
	$rows        = array();
	do {
		$lines = $wpdb->get_results( $wpdb->prepare(
			"SELECT oi.order_item_id, oi.order_id, ev.meta_value AS event_id, ti.meta_value AS ticket_info, lt.meta_value AS line_total
			FROM {$items_table} oi
			INNER JOIN {$meta_table} ev ON ev.order_item_id = oi.order_item_id AND ev.meta_key = 'event_id'
			LEFT JOIN {$meta_table} ti ON ti.order_item_id = oi.order_item_id AND ti.meta_key = '_event_ticket_info'
			LEFT JOIN {$meta_table} lt ON lt.order_item_id = oi.order_item_id AND lt.meta_key = '_line_total'
			WHERE oi.order_item_type = 'line_item' AND oi.order_item_id > %d
			ORDER BY oi.order_item_id
			LIMIT %d",
			$last_id,
			$batch
		) );
		foreach ( $lines as $line ) {
			$last_id = (int) $line->order_item_id;
			if ( isset( $rows[ $last_id ] ) ) {
				continue;
			}
			$scanned ++;
			$event_id = (int) $line->event_id;
			$verdict  = mep_zpa_verdict( $event_id, maybe_unserialize( $line->ticket_info ), $line->line_total );
			if ( '' === $verdict ) {
				continue;
			}
			$order = wc_get_order( (int) $line->order_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				continue;
			}
			$created = $order->get_date_created();
			if ( $after_ts && ( ! $created || $created->getTimestamp() < $after_ts ) ) {
				continue;
			}
			$event_state = get_post_status( $event_id );
			$event_state = $event_state ? $event_state : 'deleted';
			$expire      = get_post_meta( $event_id, 'event_expire_datetime', true );
			if ( $expire && $created && strtotime( $expire ) < strtotime( $created->date( 'Y-m-d H:i:s' ) ) ) {
				$event_state .= '+expired';
			}
			$rows[ $last_id ] = array(
				'order'       => $order->get_id(),
				'date'        => $created ? $created->date( 'Y-m-d H:i' ) : '',
				'status'      => $order->get_status(),
				'created_via' => $order->get_created_via(),
				'total'       => wc_format_decimal( $order->get_total(), 2 ),
				'event'       => $event_id,
				'event_title' => $event_id ? html_entity_decode( get_the_title( $event_id ), ENT_QUOTES, 'UTF-8' ) : '',
				'event_state' => $event_state,
				'attendees'   => mep_zpa_attendees( $order->get_id() ),
				'verdict'     => $verdict,
			);
		}
	} while ( count( $lines ) === $batch );

	if ( $csv ) {
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'order', 'date', 'status', 'created_via', 'order_total', 'event', 'event_title', 'event_state_at_order', 'attendees_on_order', 'verdict' ) );
		foreach ( $rows as $row ) {
			fputcsv( $out, $row );
		}
		fclose( $out );
		return;
	}

	printf( "Zero-price event order audit%s\n", $after_ts ? ', orders created on or after ' . gmdate( 'Y-m-d', $after_ts ) : '' );
	printf( "Event order lines scanned: %d, flagged: %d\n", $scanned, count( $rows ) );
	if ( ! $rows ) {
		echo "\nNothing to report.\n";
		return;
	}
	printf( "\n%-8s %-16s %-12s %-12s %9s %-30s %-16s %4s  %s\n", 'ORDER', 'DATE', 'STATUS', 'VIA', 'TOTAL', 'EVENT', 'EVENT STATE', 'ATTS', 'VERDICT' );
	$verdicts = array();
	$via      = array();
	foreach ( $rows as $row ) {
		printf(
			"%-8d %-16s %-12s %-12s %9s %-30s %-16s %4d  %s\n",
			$row['order'],
			$row['date'],
			substr( $row['status'], 0, 12 ),
			substr( $row['created_via'] ? $row['created_via'] : '-', 0, 12 ),
			$row['total'],
			substr( '#' . $row['event'] . ' ' . $row['event_title'], 0, 30 ),
			$row['event_state'],
			$row['attendees'],
			$row['verdict']
		);
		$verdicts[ $row['verdict'] ] = isset( $verdicts[ $row['verdict'] ] ) ? $verdicts[ $row['verdict'] ] + 1 : 1;
		$key                         = $row['created_via'] ? $row['created_via'] : '-';
		$via[ $key ]                 = isset( $via[ $key ] ) ? $via[ $key ] + 1 : 1;
	}
	echo "\nVerdicts\n";
	foreach ( $verdicts as $verdict => $count ) {
		printf( "  %-12s %d\n", $verdict, $count );
	}
	echo "\nBy created_via\n";
	foreach ( $via as $key => $count ) {
		printf( "  %-16s %d\n", $key, $count );
	}
	echo "\nEVENT STATE is the event's status today, with +expired when the order was placed after its final date.\n";
	echo "ATTS counts attendee posts on the whole order, including any other event on it.\n";
	if ( isset( $verdicts['TICKETLESS'] ) ) {
		printf( "\n%d line(s) were ordered with no ticket selected: nothing was bought. Cancel or refund them under your own policy.\n", $verdicts['TICKETLESS'] );
	}
	if ( isset( $verdicts['OFF-TYPE'] ) ) {
		printf( "\n%d free line(s) carry only ticket types that are switched off today. Check each order date against when the type was switched off. These usually carry attendees, so they hold seats (see ATTS).\n", $verdicts['OFF-TYPE'] );
	}
