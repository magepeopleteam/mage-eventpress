<?php
/**
 * Regression test: Hide expired events in Related Events section.
 *
 * Run via: wp --path=/var/www/html/magepeople eval-file wp-content/plugins/mage-eventpress/tests/regression/hide-expired-related-events.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$pass = 0;
$fail = 0;

$check = static function ( $cond, $label ) use ( &$pass, &$fail ) {
	if ( $cond ) {
		$pass++;
	} else {
		$fail++;
		fwrite( STDERR, "FAIL: {$label}\n" );
	}
};

$created_posts = array();

try {
	$past_start = date( 'Y-m-d', strtotime( '-5 days' ) );
	$past_end   = date( 'Y-m-d', strtotime( '-4 days' ) );
	$fut_start  = date( 'Y-m-d', strtotime( '+10 days' ) );
	$fut_end    = date( 'Y-m-d', strtotime( '+11 days' ) );

	$expired_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'publish',
		'post_title'  => 'ZZ Expired Related Event',
	) );
	$created_posts[] = $expired_id;
	update_post_meta( $expired_id, 'mep_enable_recurring', 'no' );
	update_post_meta( $expired_id, 'event_start_date', $past_start );
	update_post_meta( $expired_id, 'event_start_time', '09:00' );
	update_post_meta( $expired_id, 'event_end_date', $past_end );
	update_post_meta( $expired_id, 'event_end_time', '13:00' );

	$upcoming_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'publish',
		'post_title'  => 'ZZ Upcoming Related Event',
	) );
	$created_posts[] = $upcoming_id;
	update_post_meta( $upcoming_id, 'mep_enable_recurring', 'no' );
	update_post_meta( $upcoming_id, 'event_start_date', $fut_start );
	update_post_meta( $upcoming_id, 'event_start_time', '09:00' );
	update_post_meta( $upcoming_id, 'event_end_date', $fut_end );
	update_post_meta( $upcoming_id, 'event_end_time', '13:00' );

	$undated_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'publish',
		'post_title'  => 'ZZ Undated Related Event',
	) );
	$created_posts[] = $undated_id;
	update_post_meta( $undated_id, 'mep_event_no_date', 'yes' );

	$draft_future_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'draft',
		'post_title'  => 'ZZ Draft Related Event',
	) );
	$created_posts[] = $draft_future_id;
	update_post_meta( $draft_future_id, 'mep_enable_recurring', 'no' );
	update_post_meta( $draft_future_id, 'event_start_date', $fut_start );
	update_post_meta( $draft_future_id, 'event_start_time', '09:00' );
	update_post_meta( $draft_future_id, 'event_end_date', $fut_end );
	update_post_meta( $draft_future_id, 'event_end_time', '13:00' );

	$filtered = array_values( mep_filter_public_related_events( array( $expired_id, $upcoming_id, $undated_id, $draft_future_id ) ) );
	$check( ! in_array( $expired_id, $filtered, true ), 'Expired event is excluded from related events' );
	$check( in_array( $upcoming_id, $filtered, true ), 'Upcoming event is kept in related events' );
	$check( in_array( $undated_id, $filtered, true ), 'Undated event is kept in related events' );
	$check( ! in_array( $draft_future_id, $filtered, true ), 'Draft event is still excluded' );

	// Adding a future extra date to the expired event makes it reappear.
	update_post_meta( $expired_id, 'mep_event_more_date', array(
		array(
			'event_more_start_date' => $fut_start,
			'event_more_start_time' => '09:00',
			'event_more_end_date'   => $fut_end,
			'event_more_end_time'   => '13:00',
		),
	) );
	$filtered_with_more = array_values( mep_filter_public_related_events( array( $expired_id ) ) );
	$check( in_array( $expired_id, $filtered_with_more, true ), 'Event with an upcoming more_date is kept' );

	// Filter opt-out keeps expired events when returning false.
	delete_post_meta( $expired_id, 'mep_event_more_date' );
	add_filter( 'mep_hide_expired_related_events', '__return_false' );
	$filtered_opt_out = array_values( mep_filter_public_related_events( array( $expired_id ) ) );
	remove_filter( 'mep_hide_expired_related_events', '__return_false' );
	$check( in_array( $expired_id, $filtered_opt_out, true ), 'mep_hide_expired_related_events filter allows opting out' );

	// Template renders nothing when all related events are expired.
	$host_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'publish',
		'post_title'  => 'ZZ Host Event',
	) );
	$created_posts[] = $host_id;
	$event_id    = $host_id;
	$event_infos = array(
		'event_list'               => array( $expired_id ),
		'mep_related_event_status' => 'on',
	);
	ob_start();
	require MPWEM_PLUGIN_DIR . '/templates/layout/related_event.php';
	$rel_html = trim( ob_get_clean() );
	$check( '' === $rel_html, 'related_event.php renders nothing when all related events are expired' );
} finally {
	foreach ( $created_posts as $pid ) {
		wp_delete_post( $pid, true );
	}
}

echo "PASS: {$pass}, FAIL: {$fail}\n";
if ( $fail > 0 ) {
	exit( 1 );
}
