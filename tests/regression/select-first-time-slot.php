<?php
/**
 * Regression test: when a Particular Dates event has multiple time slots on the same day,
 * the first slot (not the last) must be selected by default in the booking date dropdown.
 *
 * Run with:
 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/select-first-time-slot.php
 *
 * @package mage-eventpress
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	die( 'Run this through WP-CLI.' );
}
if ( ! class_exists( 'MPWEM_Functions' ) ) {
	WP_CLI::error( 'Event Booking Manager for WooCommerce must be active.' );
}

$results = array();
$check   = function ( $name, $pass, $detail = '' ) use ( &$results ) {
	$results[] = (bool) $pass;
	WP_CLI::log( sprintf( '%s  %s%s', $pass ? 'PASS' : 'FAIL', $name, '' !== $detail ? ' - ' . $detail : '' ) );
};

$event_id = 0;
try {
	$day      = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
	$event_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'publish',
		'post_title'  => 'ZZ Multi Slot Same Day Test',
		'meta_input'  => array(
			'mep_enable_recurring' => 'yes',
			'mep_reg_status'       => 'on',
			'event_start_date'     => $day,
			'event_start_time'     => '08:00',
			'event_end_date'       => $day,
			'event_end_time'       => '09:00',
			'event_start_datetime' => "$day 08:00:00",
			'event_end_datetime'   => "$day 09:00:00",
			'mep_event_more_date'  => array(
				array( 'event_more_start_date' => $day, 'event_more_start_time' => '11:00', 'event_more_end_date' => $day, 'event_more_end_time' => '12:00' ),
				array( 'event_more_start_date' => $day, 'event_more_start_time' => '13:00', 'event_more_end_date' => $day, 'event_more_end_time' => '14:00' ),
				array( 'event_more_start_date' => $day, 'event_more_start_time' => '15:00', 'event_more_end_date' => $day, 'event_more_end_time' => '15:30' ),
				array( 'event_more_start_date' => $day, 'event_more_start_time' => '16:00', 'event_more_end_date' => $day, 'event_more_end_time' => '17:00' ),
			),
		),
	) );

	$all_dates = MPWEM_Functions::get_dates( $event_id );
	$all_times = MPWEM_Functions::get_times( $event_id, $all_dates );
	$upcoming  = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );

	$check( 'get_times returns all 5 slots on the same day', 5 === count( $all_times ), 'count=' . count( $all_times ) );
	$check( 'get_times first slot is 08:00', '08:00' === ( $all_times[0]['start']['time'] ?? '' ), 'got=' . ( $all_times[0]['start']['time'] ?? '' ) );
	$check( 'get_upcoming_date_time selects 08:00 instead of 16:00', "$day 08:00" === $upcoming, 'got=' . $upcoming );

	ob_start();
	include MPWEM_PLUGIN_DIR . '/templates/layout/date_select.php';
	$html = ob_get_clean();

	$selected_first = (bool) preg_match( '/<option[^>]*value="' . preg_quote( "$day 08:00", '/' ) . '"[^>]*selected/', $html );
	$selected_last  = (bool) preg_match( '/<option[^>]*value="' . preg_quote( "$day 16:00", '/' ) . '"[^>]*selected/', $html );
	$check( 'date_select.php marks 08:00 as selected', $selected_first );
	$check( 'date_select.php does not mark 16:00 as selected', ! $selected_last );
} finally {
	if ( $event_id ) {
		wp_delete_post( $event_id, true );
	}
}

$passed = count( array_filter( $results ) );
$total  = count( $results );
WP_CLI::log( sprintf( 'Summary: %d/%d checks passed.', $passed, $total ) );
if ( $passed !== $total ) {
	WP_CLI::error( 'Select first time slot regression checks failed.' );
}
