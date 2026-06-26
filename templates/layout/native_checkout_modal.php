<?php
/*
 * @Author      engr.sumonazma@gmail.com
 * Copyright:   mage-people.com
 *
 * Native checkout modal — rendered inside add_to_cart.php when the WooCommerce payment
 * flow is not in use (WooCommerce inactive, or active but "Enable WooCommerce Payment" off).
 * Opened by JS when the user clicks "Register For This Event".
 * Attendee fields are shown in the ticket type section below; on open the JS snapshots
 * their values and passes them to the server so they are saved with the order.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$event_id = $event_id ?? 0;
$payment_opts   = get_option( 'payment_setting_sec', array() );
$paypal_enabled  = ! empty( $payment_opts['mep_paypal_enable'] ) && $payment_opts['mep_paypal_enable'] === 'on';
$stripe_enabled  = ! empty( $payment_opts['mep_stripe_enable'] ) && $payment_opts['mep_stripe_enable'] === 'on';
$offline_enabled = ! empty( $payment_opts['mep_offline_enable'] ) && $payment_opts['mep_offline_enable'] === 'on';
$offline_label   = ! empty( $payment_opts['mep_offline_label'] ) ? $payment_opts['mep_offline_label'] : __( 'Pay Later / Offline', 'mage-eventpress' );
$has_gateways    = $paypal_enabled || $stripe_enabled || $offline_enabled;

// Add-ons (e.g. Event Pro) can require a logged-in account to complete registration.
$needs_login = apply_filters( 'mep_native_checkout_requires_login', false, $event_id ) && ! is_user_logged_in();
?>
<div id="mep-native-checkout-modal" class="mep-native-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Complete Registration', 'mage-eventpress' ); ?>">
	<div class="mep-native-modal-box">

		<!-- Header -->
		<div class="mep-native-modal-header">
			<h3 class="mep-native-modal-title"><?php esc_html_e( 'Complete Registration', 'mage-eventpress' ); ?></h3>
			<button type="button" class="mep-native-modal-close" aria-label="<?php esc_attr_e( 'Close', 'mage-eventpress' ); ?>">&times;</button>
		</div>

		<!-- Ticket summary (populated by JS) -->
		<div class="mep-native-modal-summary">
			<h4 class="mep-native-section-title"><?php esc_html_e( 'Order Summary', 'mage-eventpress' ); ?></h4>
			<div id="mep-native-ticket-summary"></div>
			<div class="mep-native-total-row">
				<span><?php esc_html_e( 'Total:', 'mage-eventpress' ); ?></span>
				<span id="mep-native-total-display"></span>
			</div>
		</div>

		<!-- Billing details (all fields required) — shown for guests and logged-in users -->
		<div class="mep-native-modal-billing">
			<h4 class="mep-native-section-title"><?php esc_html_e( 'Billing Details', 'mage-eventpress' ); ?></h4>
			<div class="mep-native-field">
				<label for="mep-native-billing-name"><?php esc_html_e( 'Full Name', 'mage-eventpress' ); ?> <span class="mep-native-req">*</span></label>
				<input type="text" id="mep-native-billing-name" class="mep-native-input" autocomplete="name" required />
			</div>
			<div class="mep-native-field">
				<label for="mep-native-billing-email"><?php esc_html_e( 'Email Address', 'mage-eventpress' ); ?> <span class="mep-native-req">*</span></label>
				<input type="email" id="mep-native-billing-email" class="mep-native-input" autocomplete="email" required />
			</div>
			<div class="mep-native-field">
				<label for="mep-native-billing-phone"><?php esc_html_e( 'Phone', 'mage-eventpress' ); ?> <span class="mep-native-req">*</span></label>
				<input type="tel" id="mep-native-billing-phone" class="mep-native-input" autocomplete="tel" required />
			</div>
		</div>

		<?php if ( $needs_login ) : ?>
		<!-- Login required before this registration can be completed -->
		<div class="mep-native-login-required">
			<p><?php esc_html_e( 'You need to be logged in to complete your registration for this event.', 'mage-eventpress' ); ?></p>
		</div>
		<?php else : ?>

		<?php if ( $has_gateways ) : ?>
		<!-- Payment method selection -->
		<div class="mep-native-modal-payment">
			<h4 class="mep-native-section-title"><?php esc_html_e( 'Payment Method', 'mage-eventpress' ); ?></h4>
			<div class="mep-native-payment-options">
				<?php $method_selected = false; // Mark the first available method as the default. ?>
				<?php if ( $paypal_enabled ) : ?>
				<label class="mep-native-payment-option">
					<input type="radio" name="mep_payment_method" value="paypal" checked="checked" />
					<span><?php esc_html_e( 'PayPal', 'mage-eventpress' ); ?></span>
				</label>
				<?php $method_selected = true; ?>
				<?php endif; ?>
				<?php if ( $stripe_enabled ) : ?>
				<label class="mep-native-payment-option">
					<input type="radio" name="mep_payment_method" value="stripe" <?php echo $method_selected ? '' : 'checked="checked"'; ?> />
					<span><?php esc_html_e( 'Credit / Debit Card (Stripe)', 'mage-eventpress' ); ?></span>
				</label>
				<?php $method_selected = true; ?>
				<?php endif; ?>
				<?php if ( $offline_enabled ) : ?>
				<label class="mep-native-payment-option">
					<input type="radio" name="mep_payment_method" value="offline" <?php echo $method_selected ? '' : 'checked="checked"'; ?> />
					<span><?php echo esc_html( $offline_label ); ?></span>
				</label>
				<?php $method_selected = true; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php else : ?>
		<!-- No gateway configured — offline registration notice -->
		<div class="mep-native-offline-notice">
			<p><?php esc_html_e( 'Your registration will be submitted for review. The organizer will contact you regarding payment.', 'mage-eventpress' ); ?></p>
		</div>
		<input type="hidden" name="mep_payment_method" value="offline" />
		<?php endif; ?>
		<?php endif; ?>

		<!-- Status message -->
		<div id="mep-native-checkout-msg" class="mep-native-msg" style="display:none;"></div>

		<!-- Hidden fields -->
		<input type="hidden" id="mep-native-event-id" value="<?php echo esc_attr( $event_id ); ?>" />
		<input type="hidden" id="mep-native-nonce" value="<?php echo esc_attr( wp_create_nonce( 'mep_native_checkout_nonce' ) ); ?>" />
		<input type="hidden" id="mep-native-ticket-data" value="" />
		<input type="hidden" id="mep-native-event-date" value="" />
		<input type="hidden" id="mep-native-attendee-snapshot" value="" />

		<!-- Submit -->
		<div class="mep-native-modal-footer">
			<?php if ( $needs_login ) : ?>
			<a href="<?php echo esc_url( wp_login_url( get_permalink( $event_id ) ) ); ?>" class="_button_theme">
				<?php esc_html_e( 'Log In to Continue', 'mage-eventpress' ); ?>
			</a>
			<?php else : ?>
			<button type="button" id="mep-native-confirm-btn" class="_button_theme">
				<span class="mep-native-btn-text"><?php esc_html_e( 'Complete Registration', 'mage-eventpress' ); ?></span>
				<span class="mep-native-btn-loading" style="display:none;"><?php esc_html_e( 'Processing…', 'mage-eventpress' ); ?></span>
			</button>
			<?php endif; ?>
			<button type="button" class="mep-native-modal-close mep-native-cancel-btn">
				<?php esc_html_e( 'Cancel', 'mage-eventpress' ); ?>
			</button>
		</div>

	</div>
</div>

<style>
.mep-native-modal-overlay {
	position: fixed;
	inset: 0;
	z-index: 999999;
	background: rgba(0, 0, 0, 0.6);
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 16px;
}
.mep-native-modal-box {
	background: #fff;
	border-radius: 12px;
	width: 100%;
	max-width: 520px;
	max-height: 90vh;
	overflow-y: auto;
	box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}
.mep-native-modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20px 24px 16px;
	border-bottom: 1px solid #e5e7eb;
}
.mep-native-modal-title {
	margin: 0;
	font-size: 18px;
	font-weight: 700;
	color: #111827;
}
.mep-native-modal-close {
	background: none;
	border: none;
	font-size: 24px;
	line-height: 1;
	cursor: pointer;
	color: #6b7280;
	padding: 0 4px;
}
.mep-native-modal-close:hover { color: #111827; }
.mep-native-modal-summary,
.mep-native-modal-payment,
.mep-native-modal-billing,
.mep-native-offline-notice {
	padding: 20px 24px 0;
}
.mep-native-field { margin-bottom: 12px; }
.mep-native-field:last-child { margin-bottom: 0; }
.mep-native-field label {
	display: block;
	font-size: 13px;
	font-weight: 600;
	color: #374151;
	margin-bottom: 5px;
}
.mep-native-req { color: #dc2626; }
.mep-native-input {
	width: 100%;
	box-sizing: border-box;
	padding: 10px 12px;
	border: 1.5px solid #d1d5db;
	border-radius: 8px;
	font-size: 14px;
	color: #111827;
	transition: border-color 0.18s, box-shadow 0.18s;
}
.mep-native-input:focus {
	border-color: #6366f1;
	box-shadow: 0 0 0 3px rgba(99,102,241,.12);
	outline: none;
}
.mep-native-input.mep-native-input-error { border-color: #dc2626; }
.mep-native-section-title {
	font-size: 13px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.06em;
	color: #6b7280;
	margin: 0 0 12px;
}
#mep-native-ticket-summary .mep-ticket-summary-row {
	display: flex;
	justify-content: space-between;
	font-size: 14px;
	color: #374151;
	padding: 4px 0;
}
.mep-native-total-row {
	display: flex;
	justify-content: space-between;
	font-size: 15px;
	font-weight: 700;
	color: #111827;
	border-top: 1px solid #e5e7eb;
	padding-top: 10px;
	margin-top: 8px;
}
.mep-native-payment-options { display: flex; flex-direction: column; gap: 10px; }
.mep-native-payment-option {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 10px 14px;
	border: 1.5px solid #d1d5db;
	border-radius: 8px;
	cursor: pointer;
	font-size: 14px;
	color: #374151;
	transition: border-color 0.18s, background 0.18s;
}
.mep-native-payment-option:has(input:checked) {
	border-color: #6366f1;
	background: #f5f3ff;
}
.mep-native-offline-notice,
.mep-native-login-required {
	background: #f0f9ff;
	border-left: 4px solid #0ea5e9;
	border-radius: 4px;
	padding: 12px 16px;
	margin: 12px 24px 0;
	font-size: 13px;
	color: #0c4a6e;
}
.mep-native-offline-notice p,
.mep-native-login-required p { margin: 0; }
.mep-native-msg {
	margin: 12px 24px 0;
	padding: 11px 15px;
	border-radius: 7px;
	font-size: 13px;
	font-weight: 500;
}
.mep-native-msg.success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.mep-native-msg.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.mep-native-modal-footer {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 20px 24px;
}
.mep-native-cancel-btn {
	font-size: 14px;
	color: #6b7280;
	text-decoration: underline;
	cursor: pointer;
	border: none;
	background: none;
	padding: 0;
}
.mep-native-cancel-btn:hover { color: #111827; }
</style>
