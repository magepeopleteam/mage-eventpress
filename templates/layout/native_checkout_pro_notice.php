<?php
/*
 * @Author      engr.sumonazma@gmail.com
 * Copyright:   mage-people.com
 *
 * Custom-payment (native checkout) PRO upsell notice.
 *
 * Rendered by add_to_cart.php in place of the booking button when the WooCommerce
 * payment flow is not in use AND no native checkout handler is registered — i.e. the
 * free plugin without the PRO add-on. Custom payment (register/checkout without
 * WooCommerce, via PayPal / Stripe / Offline) is a PRO feature, so instead of a
 * non-functional "Register" button we tell the visitor the option exists in PRO.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$event_id    = $event_id ?? 0;
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
		<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="mep-native-pro-notice-btn">
			<?php esc_html_e( 'Upgrade to PRO', 'mage-eventpress' ); ?>
		</a>
	</div>
</div>
<style>
	.mep-native-pro-notice {
		display: flex;
		align-items: flex-start;
		gap: 14px;
		padding: 18px 20px;
		margin-top: 10px;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		background: linear-gradient(135deg, #f8faff 0%, #eef2ff 100%);
	}
	.mep-native-pro-notice-icon {
		flex: 0 0 auto;
		width: 40px;
		height: 40px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 10px;
		background: #6366f1;
		color: #fff;
		font-size: 16px;
	}
	.mep-native-pro-notice-body {
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
	.mep-native-pro-notice-title {
		font-size: 15px;
		color: #1e293b;
	}
	.mep-native-pro-notice-text {
		font-size: 13px;
		color: #475569;
		line-height: 1.5;
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
