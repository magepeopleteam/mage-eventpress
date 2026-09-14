<?php
	/**
	 * Registration-form data audit for Event Booking Manager for WooCommerce.
	 *
	 * READ ONLY. Nothing in here writes to the database.
	 *
	 * This file is never loaded by the plugin - it is not in any include path. Run it
	 * explicitly:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tools/audit-attendee-form-data.php
	 *   wp eval-file .../audit-attendee-form-data.php 2026-09-01          # orders from a date
	 *   wp eval-file .../audit-attendee-form-data.php 2026-09-01 --csv    # machine readable
	 *
	 * WHY THIS EXISTS
	 * Custom registration fields travel in two hops: the add-to-cart request stores them
	 * on the cart line, checkout copies that onto the order line item as
	 * _event_user_info, and attendee creation copies each value onto the attendee post as
	 * ea_<field id>. When the order line has no _event_user_info, attendee creation falls
	 * back to the buyer's billing details and writes every custom field as an empty
	 * string - the attendee row exists, with a name and phone, and every custom column is
	 * blank.
	 *
	 * Whether the answers can still be recovered depends on which hop lost them, and only
	 * the order line item can say. For every event line this prints:
	 *
	 *   OK           attendees hold the answers; nothing to do
	 *   RECOVERABLE  the order line still holds them, the attendee posts do not
	 *                -> re-sync that order (Attendees screen) to copy them across
	 *   LOST         neither holds them; the browser never sent them
	 *                -> only the buyer can supply them again
	 *   NO FIELDS    this event's form has no custom fields; nothing to check
	 *
	 * It also groups the orders by how they were created (created_via) and by payment
	 * method, because a single bad path - express buttons, a block checkout, a quick-buy
	 * button - is the usual reason a launch day loses answers while manual testing cannot
	 * reproduce it.
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
	 * Custom registration fields of one event, as id => label.
	 *
	 * These are the form-builder fields only: the ones stored on the attendee post as
	 * ea_<mep_fbc_id> by mep_attendee_create(). The built-in name/email/phone fields are
	 * left out because billing details fill those in even when the form is lost, so they
	 * cannot tell a good booking from a broken one.
	 *
	 * @param int $event_id Event post id.
	 * @return array<string,string>
	 */
	function mep_audit_custom_fields( $event_id ) {
		static $cache = array();
		$event_id = (int) $event_id;
		if ( array_key_exists( $event_id, $cache ) ) {
			return $cache[ $event_id ];
		}
		$fields = array();
		if ( function_exists( 'mep_fb_get_reg_form_id' ) ) {
			$form_id = mep_fb_get_reg_form_id( $event_id );
			$rows    = $form_id ? get_post_meta( $form_id, 'mep_form_builder_data', true ) : array();
			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					$id = is_array( $row ) && array_key_exists( 'mep_fbc_id', $row ) ? $row['mep_fbc_id'] : '';
					if ( $id ) {
						$fields[ $id ] = is_array( $row ) && array_key_exists( 'mep_fbc_label', $row ) ? $row['mep_fbc_label'] : $id;
					}
				}
			}
		}
		$cache[ $event_id ] = $fields;

		return $fields;
	}

	/**
	 * How many of the given fields hold a value in one _event_user_info entry.
	 *
	 * @param array                $entry  One entry of the order item's _event_user_info.
	 * @param array<string,string> $fields Field id => label.
	 * @return int
	 */
	function mep_audit_entry_filled( $entry, $fields ) {
		$filled = 0;
		if ( is_array( $entry ) ) {
			foreach ( $fields as $id => $label ) {
				if ( array_key_exists( $id, $entry ) && '' !== trim( (string) $entry[ $id ] ) ) {
					$filled ++;
				}
			}
		}

		return $filled;
	}

	/**
	 * Attendee posts of one order and event, with how many custom fields each holds.
	 *
	 * @param int                  $order_id Order id.
	 * @param int                  $event_id Event id.
	 * @param array<string,string> $fields   Field id => label.
	 * @return array{count:int,filled:int,with_values:int}
	 */
	function mep_audit_attendees( $order_id, $event_id, $fields ) {
		$posts = get_posts( array(
			'post_type'      => 'mep_events_attendees',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array( 'key' => 'ea_order_id', 'value' => $order_id ),
				array( 'key' => 'ea_event_id', 'value' => $event_id ),
			),
		) );
		$result = array( 'count' => count( $posts ), 'filled' => 0, 'with_values' => 0 );
		foreach ( $posts as $post_id ) {
			$filled = 0;
			foreach ( $fields as $id => $label ) {
				if ( '' !== trim( (string) get_post_meta( $post_id, 'ea_' . $id, true ) ) ) {
					$filled ++;
				}
			}
			$result['filled'] += $filled;
			if ( $filled > 0 ) {
				$result['with_values'] ++;
			}
		}

		return $result;
	}

	$args       = isset( $args ) && is_array( $args ) ? $args : array();
	$csv        = in_array( '--csv', $args, true );
	$date_args  = array_values( array_filter( $args, static function ( $a ) {
		return '--csv' !== $a;
	} ) );
	$after      = count( $date_args ) > 0 ? (string) $date_args[0] : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
	$after_ts   = strtotime( $after );
	if ( ! $after_ts ) {
		die( "Could not read the start date. Use Y-m-d, e.g. 2026-09-01\n" );
	}

	$orders = wc_get_orders( array(
		'limit'        => -1,
		'orderby'      => 'ID',
		'order'        => 'ASC',
		'date_created' => '>=' . gmdate( 'Y-m-d', $after_ts ),
		'status'       => array_keys( wc_get_order_statuses() ),
		'return'       => 'objects',
	) );

	$rows     = array();
	$totals   = array( 'OK' => 0, 'RECOVERABLE' => 0, 'LOST' => 0, 'NO FIELDS' => 0 );
	$by_via   = array();
	$by_pay   = array();
	$attendee_gap = 0;

	foreach ( $orders as $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			continue;
		}
		$order_id = $order->get_id();
		$via      = $order->get_created_via() ? $order->get_created_via() : 'checkout';
		$pay      = $order->get_payment_method_title() ? $order->get_payment_method_title() : $order->get_payment_method();
		foreach ( $order->get_items() as $item_id => $item ) {
			$event_id = wc_get_order_item_meta( $item_id, 'event_id', true );
			if ( ! $event_id || 'mep_events' !== get_post_type( $event_id ) ) {
				continue;
			}
			$fields = mep_audit_custom_fields( $event_id );
			$info   = wc_get_order_item_meta( $item_id, '_event_user_info', true );
			$info   = is_array( $info ) ? $info : array();

			$item_filled   = 0;
			$entries_filled = 0;
			foreach ( $info as $entry ) {
				$filled = mep_audit_entry_filled( $entry, $fields );
				$item_filled += $filled;
				if ( $filled > 0 ) {
					$entries_filled ++;
				}
			}
			$attendees = mep_audit_attendees( $order_id, $event_id, $fields );

			if ( 0 === count( $fields ) ) {
				$verdict = 'NO FIELDS';
			} elseif ( $attendees['with_values'] >= count( $info ) && $attendees['with_values'] > 0 ) {
				$verdict = 'OK';
			} elseif ( $item_filled > 0 && $attendees['filled'] < $item_filled ) {
				$verdict = 'RECOVERABLE';
			} elseif ( $item_filled > 0 ) {
				$verdict = 'OK';
			} else {
				$verdict = 'LOST';
			}

			$totals[ $verdict ] ++;
			if ( 'NO FIELDS' !== $verdict ) {
				$by_via[ $via ][ $verdict ] = ( isset( $by_via[ $via ][ $verdict ] ) ? $by_via[ $via ][ $verdict ] : 0 ) + 1;
				$by_pay[ $pay ][ $verdict ] = ( isset( $by_pay[ $pay ][ $verdict ] ) ? $by_pay[ $pay ][ $verdict ] : 0 ) + 1;
			}
			if ( count( $info ) > 0 && $attendees['count'] < count( $info ) ) {
				$attendee_gap ++;
			}

			$rows[] = array(
				'order'       => $order_id,
				'date'        => $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i' ) : '',
				'status'      => $order->get_status(),
				'via'         => $via,
				'payment'     => $pay,
				'event'       => get_the_title( $event_id ),
				'fields'      => count( $fields ),
				'entries'     => count( $info ),
				'entries_ok'  => $entries_filled,
				'attendees'   => $attendees['count'],
				'att_ok'      => $attendees['with_values'],
				'verdict'     => $verdict,
			);
		}
	}

	if ( $csv ) {
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'order', 'date', 'status', 'created_via', 'payment', 'event', 'custom_fields', 'form_entries', 'entries_with_answers', 'attendees', 'attendees_with_answers', 'verdict' ) );
		foreach ( $rows as $row ) {
			fputcsv( $out, $row );
		}
		fclose( $out );
		exit;
	}

	printf( "Registration-form data audit, orders created on or after %s\n", gmdate( 'Y-m-d', $after_ts ) );
	printf( "Orders scanned: %d, event lines: %d\n\n", count( $orders ), count( $rows ) );

	printf( "%-8s %-16s %-12s %-14s %-28s %6s %6s %6s %6s  %s\n", 'ORDER', 'DATE', 'STATUS', 'VIA', 'EVENT', 'FORMS', 'ANSW', 'ATTS', 'A/ANS', 'VERDICT' );
	foreach ( $rows as $row ) {
		if ( 'NO FIELDS' === $row['verdict'] ) {
			continue;
		}
		printf(
			"%-8s %-16s %-12s %-14s %-28s %6d %6d %6d %6d  %s\n",
			$row['order'],
			$row['date'],
			$row['status'],
			substr( $row['via'], 0, 14 ),
			substr( $row['event'], 0, 28 ),
			$row['entries'],
			$row['entries_ok'],
			$row['attendees'],
			$row['att_ok'],
			$row['verdict']
		);
	}

	echo "\nVerdicts\n";
	foreach ( $totals as $verdict => $count ) {
		printf( "  %-12s %d\n", $verdict, $count );
	}

	echo "\nBy created_via\n";
	foreach ( $by_via as $via => $counts ) {
		printf( "  %-16s OK %-5d RECOVERABLE %-5d LOST %-5d\n", $via, isset( $counts['OK'] ) ? $counts['OK'] : 0, isset( $counts['RECOVERABLE'] ) ? $counts['RECOVERABLE'] : 0, isset( $counts['LOST'] ) ? $counts['LOST'] : 0 );
	}

	echo "\nBy payment method\n";
	foreach ( $by_pay as $pay => $counts ) {
		printf( "  %-24s OK %-5d RECOVERABLE %-5d LOST %-5d\n", substr( $pay, 0, 24 ), isset( $counts['OK'] ) ? $counts['OK'] : 0, isset( $counts['RECOVERABLE'] ) ? $counts['RECOVERABLE'] : 0, isset( $counts['LOST'] ) ? $counts['LOST'] : 0 );
	}

	if ( $attendee_gap > 0 ) {
		printf( "\n%d event line(s) have fewer attendee posts than form entries: those seats are also missing from availability.\n", $attendee_gap );
		echo "Run tools/reconcile-event-seats.php for the seat side of that.\n";
	}
	if ( $totals['RECOVERABLE'] > 0 ) {
		printf( "\n%d event line(s) still hold the answers on the order item: re-sync those orders to copy them onto the attendees.\n", $totals['RECOVERABLE'] );
	}
	if ( $totals['LOST'] > 0 ) {
		printf( "\n%d event line(s) never received the answers: the browser did not send them, so only the buyer can supply them again.\n", $totals['LOST'] );
	}
