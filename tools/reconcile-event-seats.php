<?php
	/**
	 * Standalone seat reconciliation report for Event Booking Manager for WooCommerce.
	 *
	 * READ ONLY. Nothing in here writes to the database.
	 *
	 * This file is never loaded by the plugin - it is not in any include path. Run it
	 * explicitly:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tools/reconcile-event-seats.php
	 *   wp eval-file .../reconcile-event-seats.php 8974          # one event only
	 *   wp eval-file .../reconcile-event-seats.php 8974 --csv    # machine readable
	 *
	 * WHY THIS EXISTS
	 * Seats sold are counted from mep_events_attendees posts, not from WooCommerce order
	 * items. Any order whose attendee records were never created is therefore invisible to
	 * the availability calculation, which silently inflates what is still on sale. This
	 * report walks the orders instead and prints, per event and ticket type:
	 *
	 *   - capacity, reserved, attendees actually stored, tickets actually paid for
	 *   - every order whose stored attendee count does not match what it bought
	 *   - every ticket type sold beyond its own capacity
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! function_exists( 'wc_get_orders' ) ) {
		die( "WooCommerce is not active.\n" );
	}

	/**
	 * Normalises an event date so order lines and attendee meta group together.
	 *
	 * Cart lines store "Y-m-d H:i" while attendee meta is often "Y-m-d H:i:s".
	 *
	 * @param string $date Raw date value.
	 * @return string
	 */
	function mep_recon_date_key( $date ) {
		$date = trim( (string) $date );
		if ( $date === '' ) {
			return '';
		}
		$time = strtotime( $date );

		return $time ? date( 'Y-m-d H:i', $time ) : $date;
	}

	/**
	 * Attendee posts stored for one order, grouped by event, ticket type and date.
	 *
	 * @param int $order_id Order ID.
	 * @return array<string,int>
	 */
	function mep_recon_stored_attendees( $order_id ) {
		$stored = array();
		$loop   = new WP_Query( array(
			'post_type'              => 'mep_events_attendees',
			'posts_per_page'         => - 1,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array( 'key' => 'ea_order_id', 'value' => $order_id, 'compare' => '=' ),
			),
		) );
		foreach ( $loop->posts as $attendee_id ) {
			$key = get_post_meta( $attendee_id, 'ea_event_id', true )
			       . '|' . get_post_meta( $attendee_id, 'ea_ticket_type', true )
			       . '|' . mep_recon_date_key( get_post_meta( $attendee_id, 'ea_event_date', true ) );
			$stored[ $key ] = ( array_key_exists( $key, $stored ) ? $stored[ $key ] : 0 ) + 1;
		}

		return $stored;
	}

	$argv_args   = isset( $args ) && is_array( $args ) ? $args : array();
	$only_event  = 0;
	$csv         = false;
	foreach ( $argv_args as $arg ) {
		if ( $arg === '--csv' ) {
			$csv = true;
		} elseif ( is_numeric( $arg ) ) {
			$only_event = (int) $arg;
		}
	}

	$statuses = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
	$statuses = array_values( array_filter( (array) $statuses ) );
	if ( sizeof( $statuses ) === 0 ) {
		$statuses = array( 'processing', 'completed' );
	}

	$expected      = array(); // event|type|date => tickets paid for
	$stored_total  = array(); // event|type|date => attendee posts stored
	$broken_orders = array();
	$page          = 1;

	do {
		$orders = wc_get_orders( array(
			'status'  => $statuses,
			'limit'   => 200,
			'paged'   => $page,
			'orderby' => 'ID',
			'order'   => 'ASC',
		) );
		foreach ( $orders as $order ) {
			$order_id   = $order->get_id();
			$order_rows = array();
			foreach ( $order->get_items() as $item_id => $item ) {
				$event_id = wc_get_order_item_meta( $item_id, 'event_id', true );
				if ( ! $event_id || get_post_type( $event_id ) !== 'mep_events' ) {
					continue;
				}
				if ( $only_event && (int) $event_id !== $only_event ) {
					continue;
				}
				// _event_user_info is one entry per attendee; without a registration form the
				// plugin falls back to one attendee per ticket from _event_ticket_info.
				$user_info = wc_get_order_item_meta( $item_id, '_event_user_info', true );
				if ( is_array( $user_info ) && sizeof( $user_info ) > 0 ) {
					foreach ( $user_info as $info ) {
						if ( ! is_array( $info ) ) {
							continue;
						}
						$type = array_key_exists( 'user_ticket_type', $info ) ? $info['user_ticket_type'] : '';
						$date = array_key_exists( 'user_event_date', $info ) ? $info['user_event_date'] : '';
						$key  = $event_id . '|' . $type . '|' . mep_recon_date_key( $date );
						$order_rows[ $key ] = ( array_key_exists( $key, $order_rows ) ? $order_rows[ $key ] : 0 ) + 1;
					}
					continue;
				}
				$ticket_info = wc_get_order_item_meta( $item_id, '_event_ticket_info', true );
				if ( ! is_array( $ticket_info ) ) {
					continue;
				}
				foreach ( $ticket_info as $tinfo ) {
					if ( ! is_array( $tinfo ) ) {
						continue;
					}
					$qty = array_key_exists( 'ticket_qty', $tinfo ) ? (int) $tinfo['ticket_qty'] : 0;
					if ( $qty < 1 ) {
						continue;
					}
					$type = array_key_exists( 'ticket_name', $tinfo ) ? $tinfo['ticket_name'] : '';
					$date = array_key_exists( 'event_date', $tinfo ) ? $tinfo['event_date'] : '';
					$key  = $event_id . '|' . $type . '|' . mep_recon_date_key( $date );
					$order_rows[ $key ] = ( array_key_exists( $key, $order_rows ) ? $order_rows[ $key ] : 0 ) + $qty;
				}
			}
			if ( sizeof( $order_rows ) === 0 ) {
				continue;
			}
			$stored = mep_recon_stored_attendees( $order_id );
			foreach ( $order_rows as $key => $qty ) {
				$expected[ $key ]     = ( array_key_exists( $key, $expected ) ? $expected[ $key ] : 0 ) + $qty;
				$have                 = array_key_exists( $key, $stored ) ? $stored[ $key ] : 0;
				$stored_total[ $key ] = ( array_key_exists( $key, $stored_total ) ? $stored_total[ $key ] : 0 ) + $have;
				if ( $have !== $qty ) {
					list( $ev, $type, $date ) = explode( '|', $key, 3 );
					$broken_orders[] = array(
						'order'  => $order_id,
						'status' => $order->get_status(),
						'event'  => $ev,
						'type'   => $type,
						'date'   => $date,
						'bought' => $qty,
						'stored' => $have,
					);
				}
			}
		}
		$page ++;
	} while ( sizeof( $orders ) === 200 );

	$rows      = array();
	$event_ids = array();
	foreach ( array_keys( $expected ) as $key ) {
		list( $event_id ) = explode( '|', $key, 2 );
		$event_ids[ $event_id ] = true;
	}
	foreach ( array_keys( $event_ids ) as $event_id ) {
		$types = get_post_meta( $event_id, 'mep_event_ticket_type', true );
		$caps  = array();
		if ( is_array( $types ) ) {
			foreach ( $types as $t ) {
				if ( ! is_array( $t ) || ! array_key_exists( 'option_name_t', $t ) ) {
					continue;
				}
				$caps[ $t['option_name_t'] ] = array(
					'capacity' => array_key_exists( 'option_qty_t', $t ) ? (int) $t['option_qty_t'] : 0,
					'reserved' => array_key_exists( 'option_rsv_t', $t ) ? (int) $t['option_rsv_t'] : 0,
				);
			}
		}
		foreach ( $expected as $key => $qty ) {
			list( $ev, $type, $date ) = explode( '|', $key, 3 );
			if ( (int) $ev !== (int) $event_id ) {
				continue;
			}
			$cap = array_key_exists( $type, $caps ) ? $caps[ $type ] : array( 'capacity' => 0, 'reserved' => 0 );
			$rows[] = array(
				'event'    => $event_id,
				'title'    => get_the_title( $event_id ),
				'type'     => $type,
				'date'     => $date,
				'capacity' => $cap['capacity'],
				'reserved' => $cap['reserved'],
				'stored'   => array_key_exists( $key, $stored_total ) ? $stored_total[ $key ] : 0,
				'paid'     => $qty,
				'over'     => $qty - ( $cap['capacity'] - $cap['reserved'] ),
			);
		}
	}

	if ( $csv ) {
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'event_id', 'event', 'ticket_type', 'event_date', 'capacity', 'reserved', 'attendees_stored', 'tickets_paid_for', 'oversold_by' ) );
		foreach ( $rows as $r ) {
			fputcsv( $out, array( $r['event'], $r['title'], $r['type'], $r['date'], $r['capacity'], $r['reserved'], $r['stored'], $r['paid'], max( 0, $r['over'] ) ) );
		}
		fputcsv( $out, array() );
		fputcsv( $out, array( 'order_id', 'order_status', 'event_id', 'ticket_type', 'event_date', 'tickets_bought', 'attendees_stored' ) );
		foreach ( $broken_orders as $b ) {
			fputcsv( $out, array( $b['order'], $b['status'], $b['event'], $b['type'], $b['date'], $b['bought'], $b['stored'] ) );
		}
		fclose( $out );

		return;
	}

	echo "\nCounting orders with status: " . implode( ', ', $statuses ) . "\n";
	echo "\n=== Capacity vs tickets actually paid for ===\n\n";
	printf( "%-8s %-28s %-26s %-17s %5s %5s %7s %6s %6s\n", 'EVENT', 'EVENT NAME', 'TICKET TYPE', 'DATE', 'CAP', 'RSV', 'STORED', 'PAID', 'OVER' );
	foreach ( $rows as $r ) {
		printf(
			"%-8s %-28s %-26s %-17s %5d %5d %7d %6d %6s\n",
			$r['event'],
			mb_substr( (string) $r['title'], 0, 28 ),
			mb_substr( (string) $r['type'], 0, 26 ),
			$r['date'],
			$r['capacity'],
			$r['reserved'],
			$r['stored'],
			$r['paid'],
			$r['over'] > 0 ? '+' . $r['over'] : '-'
		);
	}

	echo "\n=== Orders whose attendee records do not match what was bought ===\n\n";
	if ( sizeof( $broken_orders ) === 0 ) {
		echo "None. Every order has the attendee records it should.\n";
	} else {
		printf( "%-9s %-12s %-8s %-26s %-17s %7s %7s\n", 'ORDER', 'STATUS', 'EVENT', 'TICKET TYPE', 'DATE', 'BOUGHT', 'STORED' );
		foreach ( $broken_orders as $b ) {
			printf(
				"%-9s %-12s %-8s %-26s %-17s %7d %7d\n",
				$b['order'],
				$b['status'],
				$b['event'],
				mb_substr( (string) $b['type'], 0, 26 ),
				$b['date'],
				$b['bought'],
				$b['stored']
			);
		}
		echo "\n" . sizeof( $broken_orders ) . " order line(s) need attention.\n";
		echo "STORED lower than BOUGHT means seats were sold but never counted, which is what\n";
		echo "lets an event oversell. Use the Sync Attendee Data button on those orders.\n";
	}
	echo "\n";
