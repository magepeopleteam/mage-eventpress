<?php
/**
 * Regression test: Attendee Form deletion & persistence (Name-only / empty forms).
 *
 * Run via: wp --path=/var/www/html/magepeople eval-file wp-content/plugins/mage-eventpress/tests/regression/attendee-form-delete-fields.php
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

try {
	$admin_id = wp_insert_user( array(
		'user_login' => 'zz_af_admin_' . wp_generate_password( 6, false, false ),
		'user_pass'  => wp_generate_password( 24 ),
		'user_email' => 'zz-af-admin-' . wp_generate_password( 6, false, false ) . '@example.invalid',
		'role'       => 'administrator',
	) );
	$sub_id   = wp_insert_user( array(
		'user_login' => 'zz_af_sub_' . wp_generate_password( 6, false, false ),
		'user_pass'  => wp_generate_password( 24 ),
		'user_email' => 'zz-af-sub-' . wp_generate_password( 6, false, false ) . '@example.invalid',
		'role'       => 'subscriber',
	) );
	$created_users[] = $admin_id;
	$created_users[] = $sub_id;

	$event_id = wp_insert_post( array(
		'post_type'   => 'mep_events',
		'post_status' => 'draft',
		'post_title'  => 'ZZ Attendee Form Delete Regression',
	) );
	$created_posts[] = $event_id;
	update_post_meta( $event_id, 'mep_event_reg_form_status', 'on' );
	update_post_meta( $event_id, 'mep_event_reg_form_id', 'custom_form' );

	// 1. Unconfigured enabled event still seeds Name/Email/Phone.
	$default_fields = MPWEM_Layout::get_form_array( $event_id );
	$check( isset( $default_fields['user_name'], $default_fields['user_email'], $default_fields['user_phone'] ), 'Unconfigured enabled event seeds Name, Email, Phone' );

	if ( class_exists( 'MPWEM_Form_Manager' ) ) {
		$check( count( json_decode( MPWEM_Form_Manager::get_event_formbuilder_json( $event_id ), true ) ) === 3, 'Unconfigured event builder JSON seeds 3 core fields' );

		// 2. Save Name-only via save_post path (simulating deleting Email & Phone).
		wp_set_current_user( $admin_id );
		$name_only_json = wp_json_encode( array(
			array(
				'type'      => 'text',
				'subtype'   => 'text',
				'label'     => 'Full Name',
				'className' => 'form-control',
				'name'      => 'user_name',
				'required'  => true,
			),
		) );

		$_POST = array(
			'mpwem_type_nonce'            => wp_create_nonce( 'mpwem_type_nonce' ),
			'mep_event_form_builder_nonce' => wp_create_nonce( 'mep_event_form_builder_nonce' ),
			'mep_event_reg_form_id'       => 'custom_form',
			'mep_fb_formbuilder_json'     => wp_slash( $name_only_json ),
			'mep_full_name'               => '1',
			'mep_reg_email'               => '1', // Simulate stale/omitted legacy POST input; JSON must win.
			'mep_reg_phone'               => '1',
		);
		do_action( 'save_post', $event_id, get_post( $event_id ), true );

		$check( '1' === get_post_meta( $event_id, 'mep_full_name', true ), 'Name-only save sets mep_full_name=1' );
		$check( '' === get_post_meta( $event_id, 'mep_reg_email', true ), 'Name-only save clears mep_reg_email even if stale legacy POST key sent' );
		$check( '' === get_post_meta( $event_id, 'mep_reg_phone', true ), 'Name-only save clears mep_reg_phone even if stale legacy POST key sent' );

		$loaded = json_decode( MPWEM_Form_Manager::get_event_formbuilder_json( $event_id ), true );
		$check( is_array( $loaded ) && 1 === count( $loaded ) && 'user_name' === $loaded[0]['name'], 'Reloaded builder JSON keeps only user_name' );

		$front_fields = MPWEM_Layout::get_form_array( $event_id );
		$check( array( 'user_name' ) === array_keys( $front_fields ), 'Frontend get_form_array returns only user_name' );

		// 3. Delete ALL fields ([]) and persist empty form.
		$_POST['mep_fb_formbuilder_json'] = wp_slash( '[]' );
		do_action( 'save_post', $event_id, get_post( $event_id ), true );

		$check( '[]' === get_post_meta( $event_id, 'mep_fb_formbuilder_json', true ), 'Empty form save stores []' );
		$check( '' === get_post_meta( $event_id, 'mep_full_name', true ), 'Empty form save clears mep_full_name' );
		$check( '[]' === MPWEM_Form_Manager::get_event_formbuilder_json( $event_id ), 'Reloaded builder JSON stays [] after deleting all fields' );
		$check( array() === MPWEM_Layout::get_form_array( $event_id ), 'Frontend get_form_array stays empty when [] explicitly saved' );

		// 4. Security: low-privilege user & invalid nonce cannot mutate form.
		wp_set_current_user( $sub_id );
		$_POST['mep_fb_formbuilder_json'] = wp_slash( $name_only_json );
		do_action( 'save_post', $event_id, get_post( $event_id ), true );
		$check( '[]' === get_post_meta( $event_id, 'mep_fb_formbuilder_json', true ), 'Subscriber cannot overwrite event form via save_post' );

		wp_set_current_user( $admin_id );
		$_POST['mep_event_form_builder_nonce'] = 'invalid-nonce';
		do_action( 'save_post', $event_id, get_post( $event_id ), true );
		$check( '[]' === get_post_meta( $event_id, 'mep_fb_formbuilder_json', true ), 'Invalid nonce cannot overwrite event form' );

		// 5. Global Reg Form: saving Name-only + filtering disabled builtins.
		$form_id = wp_insert_post( array(
			'post_type'   => 'mep_events_reg_form',
			'post_status' => 'publish',
			'post_title'  => 'ZZ Global Form Regression',
		) );
		$created_posts[] = $form_id;
		MPWEM_Form_Manager::save_formbuilder_json( $form_id, $name_only_json );
		$g_loaded = json_decode( MPWEM_Form_Manager::get_formbuilder_json( $form_id ), true );
		$check( is_array( $g_loaded ) && 1 === count( $g_loaded ) && 'user_name' === $g_loaded[0]['name'], 'Global Reg Form keeps only user_name' );

		// Also verify classic checkbox uncheck updates get_formbuilder_json().
		update_post_meta( $form_id, 'mep_full_name', '' );
		$check( array() === json_decode( MPWEM_Form_Manager::get_formbuilder_json( $form_id ), true ), 'Global Reg Form filters out unchecked builtin' );
	} else {
		// Free-only check when PRO is inactive: explicit [] stays empty.
		update_post_meta( $event_id, 'mep_fb_formbuilder_json', '[]' );
		$check( array() === MPWEM_Layout::get_form_array( $event_id ), 'Free get_form_array respects explicit []' );
	}
} finally {
	$_POST = array();
	wp_set_current_user( 0 );
	foreach ( $created_posts as $pid ) {
		if ( $pid ) {
			wp_delete_post( $pid, true );
		}
	}
	foreach ( $created_users as $uid ) {
		if ( $uid ) {
			wp_delete_user( $uid );
		}
	}
}

echo "attendee-form-delete-fields: {$pass} passed, {$fail} failed\n";
exit( $fail > 0 ? 1 : 0 );
