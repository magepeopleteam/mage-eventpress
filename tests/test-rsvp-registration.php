<?php
/**
 * Test RSVP Registration flow.
 * Run this from CLI: php tests/test-rsvp-registration.php
 */

// Try to locate wp-load.php dynamically
$wp_load_path = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    // Try current workspace relative paths
    $wp_load_path = '/home/luna/Lerd/mage/eventpress/wp-load.php';
}

if (!file_exists($wp_load_path)) {
    echo "ERROR: WordPress root wp-load.php not found. Please adjust the path in this script.\n";
    exit(1);
}

require_once $wp_load_path;
require_once dirname(dirname(__FILE__)) . '/admin/mep_analytics.php';

// Find a valid event ID
$events = get_posts(array(
    'post_type'      => 'mep_events',
    'posts_per_page' => 1,
    'post_status'    => 'publish',
));

if (empty($events)) {
    echo "ERROR: No publish events found in the database. Please create an event first.\n";
    exit(1);
}

$event = $events[0];
$event_id = $event->ID;
echo "Found test event: '{$event->post_title}' (ID: $event_id)\n";

$unique_email = 'rsvp_test_' . time() . '@example.com';
$user_info = array(
    'user_name'       => 'RSVP Automated Test User',
    'user_email'      => $unique_email,
    'user_phone'      => '555-0199',
    'user_event_date' => date('Y-m-d H:i'),
    'user_ticket_qty' => 1,
);

echo "1. Creating RSVP attendee...\n";
$pid = mep_rsvp_attendee_create($event_id, $user_info);

if (!$pid || is_wp_error($pid)) {
    echo "ERROR: Failed to create RSVP attendee.\n";
    exit(1);
}

echo "Created Attendee CPT Post ID: $pid\n";

$expected_meta = array(
    'ea_name'                => 'RSVP Automated Test User',
    'ea_email'               => $unique_email,
    'ea_phone'               => '555-0199',
    'ea_ticket_qty'          => '1',
    'ea_event_id'            => (string)$event_id,
    'mep_checkin'            => 'No',
    'ea_order_status'        => 'completed',
    'ea_flag'                => 'rsvp_processed',
    'ea_ticket_type'         => 'RSVP',
    'ea_ticket_price'        => '0',
    'ea_ticket_order_amount' => '0',
    'ea_payment_method'      => 'RSVP',
    'ea_order_id'            => '0',
);

$all_passed = true;
echo "\n2. Verifying meta keys on CPT post...\n";
foreach ($expected_meta as $key => $expected_val) {
    $val = get_post_meta($pid, $key, true);
    if ((string)$val !== (string)$expected_val) {
        echo "  [FAIL] '$key': expected '$expected_val', got '$val'\n";
        $all_passed = false;
    } else {
        echo "  [PASS] '$key': matches '$val'\n";
    }
}

echo "\n3. Testing analytics collection logic...\n";
$start_ts = strtotime('-1 day');
$end_ts   = strtotime('+1 day');
$statuses = array('completed', 'processing');
$events_list = array($event);

$analytics_data = mep_analytics_collect_from_attendees($events_list, $start_ts, $end_ts, $statuses);

if ($analytics_data['tickets_sold'] > 0 && isset($analytics_data['ticket_types']['RSVP'])) {
    echo "  [PASS] Analytics correctly registered the RSVP registration.\n";
} else {
    echo "  [FAIL] Analytics failed to register RSVP attendee.\n";
    $all_passed = false;
}

echo "\n4. Cleaning up test attendee...\n";
wp_delete_post($pid, true);
echo "Cleanup complete.\n";

if ($all_passed) {
    echo "\n=== ALL TESTS PASSED SUCCESSFULLY ===\n";
    exit(0);
} else {
    echo "\n=== SOME TESTS FAILED ===\n";
    exit(1);
}
