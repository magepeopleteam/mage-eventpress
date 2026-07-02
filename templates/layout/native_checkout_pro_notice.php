<?php
/*
 * @Author      engr.sumonazma@gmail.com
 * Copyright:   mage-people.com
 *
 * Rendered by add_to_cart.php in place of the booking button when the WooCommerce
 * payment flow is not in use AND no native checkout handler is registered — i.e. the
 * free plugin without the PRO add-on. Custom payment (register/checkout without
 * WooCommerce, via PayPal / Stripe / Offline) is a PRO feature.
 *
 * Audience-aware:
 *  - Admins (manage_options) see a PRO upsell explaining the feature is in PRO.
 *  - Everyone else (customers / guests) see a neutral "registration not possible,
 *    please contact the administrator" notice — no upsell.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$event_id  = $event_id ?? 0;
$is_admin_user = current_user_can( 'manage_options' );
?>
<?php if ( $is_admin_user ) {
	$upgrade_url = apply_filters( 'mep_pro_upgrade_url', 'https://mage-people.com/', $event_id );
	?>
	<div class="mep-native-pro-notice">
		<span class="mep-native-pro-notice-icon fas fa-lock" aria-hidden="true"></span>
		<div class="mep-native-pro-notice-body">
			<span class="mep-native-pro-notice-badge"><?php esc_html_e( 'PRO Feature', 'mage-eventpress' ); ?></span>
			<strong class="mep-native-pro-notice-title"><?php esc_html_e( 'Custom Payment Checkout', 'mage-eventpress' ); ?></strong>
			<span class="mep-native-pro-notice-text">
				<?php esc_html_e( 'Online registration with custom payment (PayPal, Stripe, Offline) — without WooCommerce — is available in the PRO version.', 'mage-eventpress' ); ?>
			</span>
			<span class="mep-native-pro-notice-hint">
				<?php esc_html_e( 'Only you (site administrators) can see this message.', 'mage-eventpress' ); ?>
			</span>
			<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="mep-native-pro-notice-btn">
				<?php esc_html_e( 'Upgrade to PRO', 'mage-eventpress' ); ?>
			</a>
		</div>
	</div>
<?php } else { ?>
	<div class="mep-native-unavailable-notice">
		<span class="mep-native-unavailable-icon fas fa-info-circle" aria-hidden="true"></span>
		<div class="mep-native-unavailable-body">
			<strong class="mep-native-unavailable-title"><?php esc_html_e( 'Registration is currently not available', 'mage-eventpress' ); ?></strong>
			<span class="mep-native-unavailable-text">
				<?php esc_html_e( 'Online registration for this event is not possible right now. Please contact the event administrator for assistance.', 'mage-eventpress' ); ?>
			</span>
		</div>
	</div>
<?php } ?>
<style>
	.mep-native-pro-notice,
	.mep-native-unavailable-notice {
		display: flex;
		align-items: flex-start;
		gap: 14px;
		padding: 18px 20px;
		margin-top: 10px;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
	}
	.mep-native-pro-notice {
		background: linear-gradient(135deg, #f8faff 0%, #eef2ff 100%);
	}
	.mep-native-unavailable-notice {
		background: #f8fafc;
	}
	.mep-native-pro-notice-icon,
	.mep-native-unavailable-icon {
		flex: 0 0 auto;
		width: 40px;
		height: 40px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 10px;
		color: #fff;
		font-size: 16px;
	}
	.mep-native-pro-notice-icon { background: #6366f1; }
	.mep-native-unavailable-icon { background: #94a3b8; }
	.mep-native-pro-notice-body,
	.mep-native-unavailable-body {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}
	.mep-native-pro-notice-badge {
		align-self: flex-start;
		font-size: 10.5px;
		font-weight: 700;
		letter-spacing: .06em;
		text-transform: uppercase;
		color: #4f46e5;
		background: #e0e7ff;
		padding: 2px 8px;
		border-radius: 999px;
	}
	.mep-native-pro-notice-title,
	.mep-native-unavailable-title {
		font-size: 15px;
		color: #1e293b;
	}
	.mep-native-pro-notice-text,
	.mep-native-unavailable-text {
		font-size: 13px;
		color: #475569;
		line-height: 1.5;
	}
	.mep-native-pro-notice-hint {
		font-size: 11.5px;
		color: #94a3b8;
		font-style: italic;
	}
	.mep-native-pro-notice-btn {
		align-self: flex-start;
		margin-top: 8px;
		display: inline-block;
		padding: 9px 18px;
		border-radius: 8px;
		background: linear-gradient(180deg, #6366f1, #4f46e5);
		color: #fff !important;
		font-size: 13px;
		font-weight: 600;
		text-decoration: none;
		transition: filter .15s ease;
	}
	.mep-native-pro-notice-btn:hover {
		filter: brightness(1.08);
		color: #fff !important;
	}
</style>
