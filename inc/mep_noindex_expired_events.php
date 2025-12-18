<?php
/**
 * Noindex Expired Events
 * 
 * Adds noindex meta tag to expired event pages to prevent search engines
 * from indexing old content.
 * 
 * @package MageEventPress
 * @since 5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Add noindex meta tag to expired single event pages
 * 
 * This function checks if:
 * 1. We're on a single event page
 * 2. The event's end date has passed
 * 3. If so, it outputs a noindex, nofollow meta tag
 * 
 * @return void
 */
function mep_noindex_expired_events() {
	global $post;
	
	// Only run on single event pages
	if ( ! is_single() || ! isset( $post->ID ) ) {
		return;
	}
	
	$event_id = $post->ID;
	
	// Verify this is an event post type
	if ( get_post_type( $event_id ) !== 'mep_events' ) {
		return;
	}
	
	// Check if the event has expired
	if ( mep_is_event_expired( $event_id ) ) {
		echo '<meta name="robots" content="noindex, nofollow">' . "\n";
	}
}
add_action( 'wp_head', 'mep_noindex_expired_events', 1 );

/**
 * Check if an event has expired based on its end date
 * 
 * This function determines if an event is expired by:
 * 1. Getting the event's end datetime from post meta
 * 2. Comparing it with the current datetime
 * 3. Accounting for buffer time if configured
 * 
 * @param int $event_id The event post ID
 * @return bool True if event has expired, false otherwise
 */
function mep_is_event_expired( $event_id ) {
	// Get the event end datetime
	$event_end_datetime = get_post_meta( $event_id, 'event_end_datetime', true );
	
	// If no end date is set, consider it not expired
	if ( empty( $event_end_datetime ) ) {
		return false;
	}
	
	// Get buffer time (in minutes) and convert to seconds
	$buffer_time = get_post_meta( $event_id, 'mep_buffer_time', true );
	$buffer_time = ! empty( $buffer_time ) ? (int) $buffer_time * 60 : 0;
	
	// Get current time
	$current_time = current_time( 'timestamp' );
	
	// Calculate expiration time (end datetime + buffer time)
	$expiration_time = strtotime( $event_end_datetime ) + $buffer_time;
	
	// Event is expired if current time is past the expiration time
	return $current_time > $expiration_time;
}

/**
 * Alternative: Check if event is expired based on recurring dates
 * 
 * For events with multiple dates, this checks if ALL dates have passed.
 * Uncomment and use this function instead if you want to handle recurring events.
 * 
 * @param int $event_id The event post ID
 * @return bool True if all event dates have expired, false otherwise
 */
/*
function mep_is_event_fully_expired( $event_id ) {
	// Get all event dates (including recurring dates)
	$more_dates = get_post_meta( $event_id, 'mep_event_more_date', true );
	
	if ( empty( $more_dates ) || ! is_array( $more_dates ) ) {
		// No recurring dates, fall back to single date check
		return mep_is_event_expired( $event_id );
	}
	
	// Get buffer time
	$buffer_time = get_post_meta( $event_id, 'mep_buffer_time', true );
	$buffer_time = ! empty( $buffer_time ) ? (int) $buffer_time * 60 : 0;
	
	// Get current time
	$current_time = current_time( 'timestamp' );
	
	// Check if at least one date is still valid (not expired)
	foreach ( $more_dates as $date_info ) {
		$end_datetime = isset( $date_info['event_more_end_date'] ) ? $date_info['event_more_end_date'] : '';
		$end_time = isset( $date_info['event_more_end_time'] ) ? $date_info['event_more_end_time'] : '';
		
		if ( empty( $end_datetime ) ) {
			continue;
		}
		
		// Combine date and time
		$full_end_datetime = $end_time ? $end_datetime . ' ' . $end_time : $end_datetime;
		$expiration_time = strtotime( $full_end_datetime ) + $buffer_time;
		
		// If any date is still valid, event is not fully expired
		if ( $current_time <= $expiration_time ) {
			return false;
		}
	}
	
	// All dates have passed
	return true;
}
*/
