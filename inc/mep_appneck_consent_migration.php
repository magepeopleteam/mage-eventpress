<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	/**
	 * Carry the old Appsero tracking opt-in over to the Appneck consent.
	 *
	 * Appsero saved the site owner's answer in the option
	 * `mage-eventpress_allow_tracking` ('yes' = allowed, 'no' = refused).
	 * Appneck keeps its own decision and shows its allow/skip admin notice
	 * while that decision is pending, so a site that already said yes to
	 * Appsero would be asked a second time.
	 *
	 * When the legacy option is 'yes' and Appneck has no decision yet, this
	 * accepts the Appneck consent through the SDK (local write first, then
	 * the one-off server sync), so the popup never renders.
	 *
	 * Hooked on admin_init priority 5: Sdk::bootstrap() already ran at file
	 * include, and admin_notices fires after admin_init, so the decision is
	 * in place before the notice would draw. A site that already answered
	 * Appneck (accepted or rejected) is never overwritten, and once the
	 * migration has accepted, a marker option stops it running again —
	 * `$sdk->consent()->reset()` (ask again) must stay meaningful.
	 */
if ( ! function_exists( 'mep_migrate_appsero_tracking_consent' ) ) {
	function mep_migrate_appsero_tracking_consent() {
		if ( get_option( 'mep_appneck_consent_migrated', false ) ) {
			return;
		}

		// No Appsero opt-in stored (fresh install) or it was 'no': do nothing,
		// so the Appneck popup shows exactly as it normally would. Only a
		// legacy 'yes' suppresses the prompt.
		if ( 'yes' !== get_option( 'mage-eventpress_allow_tracking', 'no' ) ) {
			return;
		}

		$sdk = isset( $GLOBALS['my_plugin_sdk'] ) ? $GLOBALS['my_plugin_sdk'] : null;
		if ( ! is_object( $sdk ) || ! method_exists( $sdk, 'consent' ) ) {
			return;
		}

		$consent = $sdk->consent();
		if ( ! is_object( $consent ) || ! $consent->is_pending() ) {
			return;
		}

		$consent->accept();
		update_option( 'mep_appneck_consent_migrated', 'yes', true );
	}
	add_action( 'admin_init', 'mep_migrate_appsero_tracking_consent', 5 );
}
