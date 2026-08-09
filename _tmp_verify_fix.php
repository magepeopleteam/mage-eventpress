<?php
require_once 'C:/Program Files/Ampps/www/event/wp-load.php';

$event_id = 310;
$arr = MPWEM_Layout::get_form_array( $event_id );
echo "form_count=" . count( $arr ) . " keys=" . implode( ',', array_keys( $arr ) ) . "\n";

// Heal empty [] meta so admin/save stays consistent with frontend.
if ( class_exists( 'MPWEM_Form_Manager' ) ) {
	$json = get_post_meta( $event_id, 'mep_fb_formbuilder_json', true );
	if ( $json === '[]' || $json === '' || $json === false ) {
		MPWEM_Form_Manager::save_formbuilder_json( $event_id, '[]' );
		echo "healed_json=" . get_post_meta( $event_id, 'mep_fb_formbuilder_json', true ) . "\n";
		echo "name=" . get_post_meta( $event_id, 'mep_full_name', true ) . " email=" . get_post_meta( $event_id, 'mep_reg_email', true ) . " phone=" . get_post_meta( $event_id, 'mep_reg_phone', true ) . "\n";
		$arr2 = MPWEM_Layout::get_form_array( $event_id );
		echo "after_heal_count=" . count( $arr2 ) . "\n";
	}
}

$html = @file_get_contents( 'http://localhost/event/events/startup-founders-meetup/' );
echo "mep_attendee_info_hidden=" . substr_count( (string) $html, 'mep_attendee_info_hidden' ) . "\n";
echo "mep_form_item=" . substr_count( (string) $html, 'mep_form_item' ) . "\n";
echo "mep_attendee_info=" . substr_count( (string) $html, 'mep_attendee_info' ) . "\n";
