<?php
/**
 * Add noindex meta tag to expired event pages
 * 
 * This file adds SEO functionality to prevent search engines from indexing
 * expired event pages, helping maintain clean search results.
 * 
 * @package MageEventPress
 * @since 5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Add noindex meta robots tag to expired single event pages
 * 
 * This function checks if:
 * 1. We're on a single event page (mep_events post type)
 * 2. The event has passed its expiration date
 * 3. If both conditions are true, it adds a noindex, nofollow meta tag
 * 
 * @since 5.0.8
 */
function mep_add_noindex_to_expired_events() {
	// Only run on single event pages
	if ( ! is_singular( 'mep_events' ) ) {
		return;
	}
	
	global $post;
	
	if ( ! $post ) {
		return;
	}
	
	$event_id = $post->ID;
	
	// Get the event expiration date using the plugin's built-in function
	if ( function_exists( 'mep_get_event_expire_date' ) ) {
		$expire_date = mep_get_event_expire_date( $event_id );
	} else {
		// Fallback: Get expiration date manually
		$event_expire_on_old   = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
		$event_expire_on       = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
		$event_start_datetime  = get_post_meta( $event_id, 'event_start_datetime', true );
		$event_expire_datetime = get_post_meta( $event_id, 'event_expire_datetime', true );
		$expire_date           = $event_expire_on == 'event_expire_datetime' ? $event_expire_datetime : $event_start_datetime;
	}
	
	// Check if event has expired
	if ( ! empty( $expire_date ) ) {
		$current_time = current_time( 'Y-m-d H:i:s' );
		
		// If current time is past the expiration date, add noindex tag
		if ( strtotime( $current_time ) > strtotime( $expire_date ) ) {
			echo '<meta name="robots" content="noindex, nofollow">' . "\n";
			
			// Optional: Add a comment for debugging (remove in production if needed)
			echo '<!-- Event expired on: ' . esc_html( $expire_date ) . ' -->' . "\n";
		}
	}
}
add_action( 'wp_head', 'mep_add_noindex_to_expired_events', 1 );

/**
 * Filter for Yoast SEO - Exclude expired events from XML sitemap
 * 
 * This function works with Yoast SEO to automatically exclude
 * expired events from the XML sitemap.
 * 
 * @param array $excluded_post_ids Array of post IDs to exclude
 * @return array Modified array with expired event IDs added
 * @since 5.0.8
 */
function mep_exclude_expired_events_from_yoast_sitemap( $excluded_post_ids ) {
	// Query all events
	$args = array(
		'post_type'      => 'mep_events',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids', // Only get IDs for performance
	);
	
	$event_ids = get_posts( $args );
	
	if ( ! empty( $event_ids ) ) {
		$current_time = current_time( 'Y-m-d H:i:s' );
		
		foreach ( $event_ids as $event_id ) {
			// Get expiration date
			if ( function_exists( 'mep_get_event_expire_date' ) ) {
				$expire_date = mep_get_event_expire_date( $event_id );
			} else {
				$event_expire_on_old   = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
				$event_expire_on       = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
				$event_start_datetime  = get_post_meta( $event_id, 'event_start_datetime', true );
				$event_expire_datetime = get_post_meta( $event_id, 'event_expire_datetime', true );
				$expire_date           = $event_expire_on == 'event_expire_datetime' ? $event_expire_datetime : $event_start_datetime;
			}
			
			// If expired, add to exclusion list
			if ( ! empty( $expire_date ) && strtotime( $current_time ) > strtotime( $expire_date ) ) {
				$excluded_post_ids[] = $event_id;
			}
		}
	}
	
	return $excluded_post_ids;
}
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'mep_exclude_expired_events_from_yoast_sitemap' );

/**
 * Filter for RankMath SEO - Exclude expired events from XML sitemap
 * 
 * @param bool   $exclude Whether to exclude the post
 * @param string $post_type Post type
 * @return bool
 * @since 5.0.8
 */
function mep_exclude_expired_events_from_rankmath_sitemap( $exclude, $post_type ) {
	if ( $post_type !== 'mep_events' ) {
		return $exclude;
	}
	
	global $post;
	
	if ( ! $post ) {
		return $exclude;
	}
	
	$event_id = $post->ID;
	
	// Get expiration date
	if ( function_exists( 'mep_get_event_expire_date' ) ) {
		$expire_date = mep_get_event_expire_date( $event_id );
	} else {
		$event_expire_on_old   = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
		$event_expire_on       = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
		$event_start_datetime  = get_post_meta( $event_id, 'event_start_datetime', true );
		$event_expire_datetime = get_post_meta( $event_id, 'event_expire_datetime', true );
		$expire_date           = $event_expire_on == 'event_expire_datetime' ? $event_expire_datetime : $event_start_datetime;
	}
	
	// Check if expired
	if ( ! empty( $expire_date ) ) {
		$current_time = current_time( 'Y-m-d H:i:s' );
		
		if ( strtotime( $current_time ) > strtotime( $expire_date ) ) {
			return true; // Exclude from sitemap
		}
	}
	
	return $exclude;
}
add_filter( 'rank_math/sitemap/entry', 'mep_exclude_expired_events_from_rankmath_sitemap', 10, 2 );
