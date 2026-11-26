<?php
/**
 * Regression test: Adding or editing an event date earlier than the latest posted date
 * across Classic Editor, Modern Editor, backend save, and get_dates().
 *
 * Run via: wp --path=/var/www/html/magepeople eval-file wp-content/plugins/mage-eventpress/tests/regression/allow-earlier-event-dates.php
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
$created_users = array();
$orig_general  = get_option( 'general_setting_sec', array() );

try {
	$admin_id = wp_insert_user( array(
		'user_login' => 'zz_ed_admin_' . wp_generate_password( 6, false, false ),
		'user_pass'  => wp_generate_password( 24 ),
		'user_email' => 'zz-ed-admin-' . wp_generate_password( 6, false, false ) . '@example.invalid',
		'role'       => 'administrator',
	) );
	$sub_id   = wp_insert_user( array(
		'user_login' => 'zz_ed_sub_' . wp_generate_password( 6, false, false ),
		'user_pass'  => wp_generate_password( 24 ),
		'user_email' => 'zz-ed-sub-' . wp_generate_password( 6, false, false ) . '@example.invalid',
		'role'       => 'subscriber',
	) );
	$created_users[] = $admin_id;
	$created_users[] = $sub_id;

	$today     = current_time( 'Y-m-d' );
	$today_y   = (int) date( 'Y', strtotime( $today ) );
	$today_m   = (int) date( 'n', strtotime( $today ) ) - 1;
	$today_d   = (int) date( 'j', strtotime( $today ) );
	$today_js  = "new Date({$today_y}, {$today_m}, {$today_d})";

	$main_start = date( 'Y-m-d', strtotime( '+5 days', strtotime( $today ) ) );
	$main_end   = date( 'Y-m-d', strtotime( '+6 days', strtotime( $today ) ) );
	$late_start = date( 'Y-m-d', strtotime( '+30 days', strtotime( $today ) ) );
	$late_end   = date( 'Y-m-d', strtotime( '+32 days', strtotime( $today ) ) );
	$late_y     = (int) date( 'Y', strtotime( $late_start ) );
	$late_m     = (int) date( 'n', strtotime( $late_start ) ) - 1;
	$late_d     = (int) date( 'j', strtotime( $late_start ) );
	$late_js    = "new Date({$late_y}, {$late_m}, {$late_d})";

	$mid_start  = date( 'Y-m-d', strtotime( '+15 days', strtotime( $today ) ) );
	$mid_end    = date( 'Y-m-d', strtotime( '+16 days', strtotime( $today ) ) );

	// Ensure mep_hide_old_date = yes and event_end_datetime expiry mode before get_settings() caches general_setting_sec.
	$gen = is_array( $orig_general ) ? $orig_general : array();
	$gen['mep_hide_old_date']             = 'yes';
	$gen['mep_event_expire_on_datetimes'] = 'event_end_datetime';
	update_option( 'general_setting_sec', $gen );

	$event_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'publish',
		'post_title'  => 'ZZ Earlier Date Regression',
	) );
	$created_posts[] = $event_id;

	update_post_meta( $event_id, 'mep_enable_recurring', 'yes' );
	update_post_meta( $event_id, 'event_start_date', $main_start );
	update_post_meta( $event_id, 'event_start_time', '09:00' );
	update_post_meta( $event_id, 'event_end_date', $main_end );
	update_post_meta( $event_id, 'event_end_time', '13:00' );
	update_post_meta( $event_id, 'mep_event_more_date', array(
		array(
			'event_more_start_date' => $late_start,
			'event_more_start_time' => '09:00',
			'event_more_end_date'   => $late_end,
			'event_more_end_time'   => '13:00',
		),
	) );

	// 1. Classic Editor: MPWEM_Date_Settings::normal_date() & particular_section()
	$ds = new MPWEM_Date_Settings();
	ob_start();
	$ds->normal_date( $event_id );
	$normal_html = ob_get_clean();

	ob_start();
	$ds->particular_section( $event_id );
	$particular_html = ob_get_clean();

	$check( strpos( $normal_html, 'jQuery("#event_more_start_date_normal_0").datepicker' ) !== false, 'Classic Single Event renders more_start_date_normal_0 datepicker' );
	$check(
		preg_match( '/jQuery\("#event_more_start_date_normal_0"\)\.datepicker\(\{.*?minDate:\s*' . preg_quote( $today_js, '/' ) . '/s', $normal_html ) === 1,
		'Classic Single Event existing more_start_date minDate is today, not the saved future date'
	);
	$check( strpos( $particular_html, 'jQuery("#event_more_start_date_0").datepicker' ) !== false, 'Classic Particular Event renders more_start_date_0 datepicker' );
	$check(
		preg_match( '/jQuery\("#event_more_start_date_0"\)\.datepicker\(\{.*?minDate:\s*' . preg_quote( $today_js, '/' ) . '/s', $particular_html ) === 1,
		'Classic Particular Event existing more_start_date minDate is today, not the saved future date'
	);
	$check(
		preg_match( '/jQuery\("#event_more_end_date_0"\)\.datepicker\(\{.*?minDate:\s*' . preg_quote( $late_js, '/' ) . '/s', $particular_html ) === 1,
		'Classic Particular Event existing more_end_date minDate is its row start date, not its old end date'
	);

	// Empty start date should not emit 1970 in end date minDate.
	$empty_event_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'draft',
		'post_title'  => 'ZZ Empty Date Regression',
	) );
	$created_posts[] = $empty_event_id;
	ob_start();
	$ds->normal_date( $empty_event_id );
	$empty_normal_html = ob_get_clean();
	$check( strpos( $empty_normal_html, 'new Date(1970,' ) === false, 'Empty start date does not emit 1970 in end date minDate' );

	// 2. Backend Save: adding an earlier date ($mid_start) AFTER a later date ($late_start)
	wp_set_current_user( $admin_id );
	$_POST = array(
		'mpwem_type_nonce'       => wp_create_nonce( 'mpwem_type_nonce' ),
		'mep_event_type'         => 'offline',
		'mep_enable_recurring'   => 'yes',
		'event_start_date'       => $main_start,
		'event_start_time'       => '09:00',
		'event_end_date'         => $main_end,
		'event_end_time'         => '13:00',
		'event_more_start_date'  => array( $late_start, $mid_start ),
		'event_more_start_time'  => array( '09:00', '09:00' ),
		'event_more_end_date'    => array( $late_end, $mid_end ),
		'event_more_end_time'    => array( '13:00', '13:00' ),
	);
	$settings = new MPWEM_Settings();
	$settings->save_settings( $event_id );

	$saved_more = get_post_meta( $event_id, 'mep_event_more_date', true );
	$check( is_array( $saved_more ) && count( $saved_more ) === 2, 'Backend saves both later and earlier more_dates' );
	$check( ( $saved_more[1]['event_more_start_date'] ?? '' ) === $mid_start, 'Earlier date added after later date is persisted' );
	$check( get_post_meta( $event_id, 'event_expire_datetime', true ) === $late_end . ' 13:00:00', 'event_expire_datetime uses latest end date, not last-entered row' );

	// 3. Frontend get_all_dates() & get_dates() sort chronologically regardless of row order
	$all_dates = MPWEM_Functions::get_all_dates( $event_id );
	$check( count( $all_dates ) === 3, 'get_all_dates returns all 3 dates' );
	$check( substr( $all_dates[0]['time'], 0, 10 ) === $main_start, 'get_all_dates[0] is earliest' );
	$check( substr( $all_dates[1]['time'], 0, 10 ) === $mid_start, 'get_all_dates[1] is newly added earlier date' );
	$check( substr( $all_dates[2]['time'], 0, 10 ) === $late_start, 'get_all_dates[2] is latest date' );

	// Test Single Event ('no') in event_end_datetime expiry mode when earlier date is last in mep_event_more_date.
	update_post_meta( $event_id, 'mep_enable_recurring', 'no' );
	$gen['mep_event_expire_on_datetimes'] = 'event_end_datetime';
	update_option( 'general_setting_sec', $gen );
	$single_dates = MPWEM_Functions::get_dates( $event_id );
	$check( count( $single_dates ) === 3, 'Single Event get_dates returns all 3 dates in event_end_datetime mode' );
	$check( substr( $single_dates[0]['end'], 0, 10 ) === $late_end, 'Single Event primary row end spans to latest end date even when earlier date is last in meta' );

	// 4. Security: unauthorized user cannot save settings.
	wp_set_current_user( $sub_id );
	$_POST['event_more_start_date'] = array( $late_start );
	$_POST['event_more_end_date']   = array( $late_end );
	$_POST['event_more_start_time'] = array( '09:00' );
	$_POST['event_more_end_time']   = array( '13:00' );
	$settings->save_settings( $event_id );
	$after_sub = get_post_meta( $event_id, 'mep_event_more_date', true );
	$check( is_array( $after_sub ) && count( $after_sub ) === 2, 'Subscriber cannot overwrite event dates via save_settings()' );

	// 5. Security: invalid nonce cannot save settings.
	wp_set_current_user( $admin_id );
	$_POST['mpwem_type_nonce'] = 'invalid-nonce';
	$settings->save_settings( $event_id );
	$after_nonce = get_post_meta( $event_id, 'mep_event_more_date', true );
	$check( is_array( $after_nonce ) && count( $after_nonce ) === 2, 'Invalid nonce cannot overwrite event dates via save_settings()' );
	$_POST = array();

	// 6. Modern Editor JS & PHP localization checks.
	$edit_page_src = file_get_contents( MPWEM_PLUGIN_DIR . '/admin/MPWEM_Event_Edit_Page.php' );
	$check( strpos( $edit_page_src, "'hide_old_date'" ) !== false, 'MPWEM_Event_Edit_Page localizes hide_old_date' );

	$edit_js_src = file_get_contents( MPWEM_PLUGIN_DIR . '/assets/admin/mpwem_event_edit.js' );
	$check( strpos( $edit_js_src, 'moreStartPrevEnds' ) === false, 'mpwem_event_edit.js no longer restricts Add More Date start to previous row end date' );
	$check( strpos( $edit_js_src, 'shouldHideOldDate' ) !== false, 'mpwem_event_edit.js respects mep_hide_old_date setting' );
} finally {
	update_option( 'general_setting_sec', $orig_general );
	foreach ( $created_posts as $pid ) {
		wp_delete_post( $pid, true );
	}
	foreach ( $created_users as $uid ) {
		wp_delete_user( $uid );
	}
}

echo "PASS: {$pass}, FAIL: {$fail}\n";
if ( $fail > 0 ) {
	exit( 1 );
}
