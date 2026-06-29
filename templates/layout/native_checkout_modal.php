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
			<div class="mep-native-modal-heading">
				<h3 class="mep-native-modal-title"><?php esc_html_e( 'Complete Registration', 'mage-eventpress' ); ?></h3>
				<p class="mep-native-modal-subtitle"><?php esc_html_e( 'Review your order and confirm your spot.', 'mage-eventpress' ); ?></p>
			</div>
			<button type="button" class="mep-native-modal-close" aria-label="<?php esc_attr_e( 'Close', 'mage-eventpress' ); ?>">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
			</button>
		</div>

		<div class="mep-native-modal-body">

			<!-- Ticket summary (populated by JS) -->
			<div class="mep-native-modal-summary mep-native-card">
				<h4 class="mep-native-section-title"><?php esc_html_e( 'Order Summary', 'mage-eventpress' ); ?></h4>
				<div id="mep-native-ticket-summary"></div>
				<div class="mep-native-total-row">
					<span><?php esc_html_e( 'Total', 'mage-eventpress' ); ?></span>
					<span id="mep-native-total-display"></span>
				</div>
			</div>

			<!-- Billing details (all fields required) — shown for guests and logged-in users -->
			<div class="mep-native-modal-billing">
				<h4 class="mep-native-section-title"><?php esc_html_e( 'Billing Details', 'mage-eventpress' ); ?></h4>
				<div class="mep-native-field">
					<label for="mep-native-billing-name"><?php esc_html_e( 'Full Name', 'mage-eventpress' ); ?> <span class="mep-native-req">*</span></label>
					<input type="text" id="mep-native-billing-name" class="mep-native-input" placeholder="<?php esc_attr_e( 'Jane Doe', 'mage-eventpress' ); ?>" autocomplete="name" required />
				</div>
				<div class="mep-native-field">
					<label for="mep-native-billing-email"><?php esc_html_e( 'Email Address', 'mage-eventpress' ); ?> <span class="mep-native-req">*</span></label>
					<input type="email" id="mep-native-billing-email" class="mep-native-input" placeholder="<?php esc_attr_e( 'you@example.com', 'mage-eventpress' ); ?>" autocomplete="email" required />
				</div>
				<div class="mep-native-field">
					<label for="mep-native-billing-phone"><?php esc_html_e( 'Phone', 'mage-eventpress' ); ?> <span class="mep-native-req">*</span></label>
					<input type="tel" id="mep-native-billing-phone" class="mep-native-input" placeholder="<?php esc_attr_e( '+1 555 000 0000', 'mage-eventpress' ); ?>" autocomplete="tel" required />
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
						<span class="mep-native-pay-check" aria-hidden="true"></span>
						<span class="mep-native-pay-name"><?php esc_html_e( 'PayPal', 'mage-eventpress' ); ?></span>
					</label>
					<?php $method_selected = true; ?>
					<?php endif; ?>
					<?php if ( $stripe_enabled ) : ?>
					<label class="mep-native-payment-option">
						<input type="radio" name="mep_payment_method" value="stripe" <?php echo $method_selected ? '' : 'checked="checked"'; ?> />
						<span class="mep-native-pay-check" aria-hidden="true"></span>
						<span class="mep-native-pay-name"><?php esc_html_e( 'Credit / Debit Card (Stripe)', 'mage-eventpress' ); ?></span>
					</label>
					<?php $method_selected = true; ?>
					<?php endif; ?>
					<?php if ( $offline_enabled ) : ?>
					<label class="mep-native-payment-option">
						<input type="radio" name="mep_payment_method" value="offline" <?php echo $method_selected ? '' : 'checked="checked"'; ?> />
						<span class="mep-native-pay-check" aria-hidden="true"></span>
						<span class="mep-native-pay-name"><?php echo esc_html( $offline_label ); ?></span>
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

		</div>

		<!-- Hidden fields -->
		<input type="hidden" id="mep-native-event-id" value="<?php echo esc_attr( $event_id ); ?>" />
		<input type="hidden" id="mep-native-nonce" value="<?php echo esc_attr( wp_create_nonce( 'mep_native_checkout_nonce' ) ); ?>" />
		<input type="hidden" id="mep-native-ticket-data" value="" />
		<input type="hidden" id="mep-native-event-date" value="" />
		<input type="hidden" id="mep-native-attendee-snapshot" value="" />

		<!-- Submit -->
		<div class="mep-native-modal-footer">
			<button type="button" class="mep-native-modal-close mep-native-cancel-btn">
				<?php esc_html_e( 'Cancel', 'mage-eventpress' ); ?>
			</button>
			<?php if ( $needs_login ) : ?>
			<a href="<?php echo esc_url( wp_login_url( get_permalink( $event_id ) ) ); ?>" class="_button_theme mep-native-primary-btn">
				<?php esc_html_e( 'Log In to Continue', 'mage-eventpress' ); ?>
			</a>
			<?php else : ?>
			<button type="button" id="mep-native-confirm-btn" class="_button_theme mep-native-primary-btn">
				<span class="mep-native-btn-text">
					<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					<?php esc_html_e( 'Complete Registration', 'mage-eventpress' ); ?>
				</span>
				<span class="mep-native-btn-loading" style="display:none;">
					<span class="mep-native-spinner" aria-hidden="true"></span>
					<?php esc_html_e( 'Processing…', 'mage-eventpress' ); ?>
				</span>
			</button>
			<?php endif; ?>
		</div>

	</div>
</div>

<style>
#mep-native-checkout-modal,
#mep-native-checkout-modal * { box-sizing: border-box; }

#mep-native-checkout-modal {
	--mep-accent: #6366f1;
	--mep-accent-2: #8b5cf6;
	--mep-accent-soft: #eef2ff;
	--mep-ink: #0f172a;
	--mep-ink-soft: #475569;
	--mep-muted: #94a3b8;
	--mep-line: #e6e8ee;
	--mep-bg-soft: #f8fafc;
	--mep-radius: 12px;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
	-webkit-font-smoothing: antialiased;
}

.mep-native-modal-overlay {
	position: fixed;
	inset: 0;
	z-index: 999999;
	background: rgba(15, 23, 42, 0.55);
	-webkit-backdrop-filter: blur(6px);
	backdrop-filter: blur(6px);
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 16px;
}

.mep-native-modal-box {
	background: #fff;
	border-radius: 20px;
	width: 100%;
	max-width: 480px;
	max-height: 92vh;
	display: flex;
	flex-direction: column;
	overflow: hidden;
	box-shadow: 0 24px 70px -12px rgba(15, 23, 42, 0.35), 0 8px 24px -8px rgba(15, 23, 42, 0.2);
	animation: mepNativePop .42s cubic-bezier(.16, 1, .3, 1) both;
}
@keyframes mepNativePop {
	from { opacity: 0; transform: translateY(26px) scale(.96); }
	to   { opacity: 1; transform: none; }
}

/* Header */
.mep-native-modal-header {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 16px;
	padding: 22px 24px 18px;
	background: linear-gradient(135deg, var(--mep-accent-soft), #faf5ff);
	border-bottom: 1px solid var(--mep-line);
}
.mep-native-modal-title {
	margin: 0;
	font-size: 19px;
	font-weight: 700;
	letter-spacing: -0.01em;
	color: var(--mep-ink);
	line-height: 1.25;
}
.mep-native-modal-subtitle {
	margin: 3px 0 0;
	font-size: 13px;
	color: var(--mep-ink-soft);
	line-height: 1.4;
}
.mep-native-modal-close {
	flex: 0 0 auto;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 34px;
	height: 34px;
	border: none;
	border-radius: 50%;
	background: rgba(255, 255, 255, 0.7);
	color: var(--mep-ink-soft);
	cursor: pointer;
	transition: background .18s ease, color .18s ease, transform .18s ease;
}
.mep-native-modal-close:hover { background: #fff; color: var(--mep-ink); transform: rotate(90deg); }

/* Scrollable body */
.mep-native-modal-body {
	padding: 20px 24px 4px;
	overflow-y: auto;
}
.mep-native-modal-body::-webkit-scrollbar { width: 8px; }
.mep-native-modal-body::-webkit-scrollbar-thumb { background: #d8dbe3; border-radius: 8px; }
.mep-native-modal-body::-webkit-scrollbar-thumb:hover { background: #c4c8d2; }

.mep-native-section-title {
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.08em;
	color: var(--mep-muted);
	margin: 0 0 12px;
}

/* Order summary card */
.mep-native-card {
	background: var(--mep-bg-soft);
	border: 1px solid var(--mep-line);
	border-radius: var(--mep-radius);
	padding: 16px 18px;
	margin-bottom: 22px;
}
#mep-native-ticket-summary .mep-ticket-summary-row {
	display: flex;
	justify-content: space-between;
	gap: 12px;
	font-size: 14px;
	color: var(--mep-ink-soft);
	padding: 5px 0;
}
.mep-native-total-row {
	display: flex;
	justify-content: space-between;
	align-items: baseline;
	font-size: 16px;
	font-weight: 700;
	color: var(--mep-ink);
	border-top: 1px dashed var(--mep-line);
	padding-top: 12px;
	margin-top: 8px;
}
#mep-native-total-display { font-size: 19px; color: var(--mep-accent); }

/* Sections */
.mep-native-modal-billing,
.mep-native-modal-payment { margin-bottom: 22px; }

/* Form fields */
.mep-native-field { margin-bottom: 14px; }
.mep-native-field:last-child { margin-bottom: 0; }
.mep-native-field label {
	display: block;
	font-size: 13px;
	font-weight: 600;
	color: var(--mep-ink);
	margin-bottom: 6px;
}
.mep-native-req { color: #ef4444; font-weight: 700; }
#mep-native-checkout-modal .mep-native-input {
	width: 100%;
	padding: 12px 14px;
	border: 1.5px solid var(--mep-line);
	border-radius: 10px;
	font-size: 14.5px;
	font-family: inherit;
	color: var(--mep-ink);
	background: #fff;
	line-height: 1.3;
	transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
}
#mep-native-checkout-modal .mep-native-input::placeholder { color: #b6bdca; }
#mep-native-checkout-modal .mep-native-input:hover { border-color: #cfd4df; }
#mep-native-checkout-modal .mep-native-input:focus {
	border-color: var(--mep-accent);
	box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.14);
	outline: none;
	background: #fff;
}
#mep-native-checkout-modal .mep-native-input.mep-native-input-error {
	border-color: #ef4444;
	box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
}

/* Payment method cards */
.mep-native-payment-options { display: flex; flex-direction: column; gap: 10px; }
.mep-native-payment-option {
	position: relative;
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 14px 16px;
	border: 1.5px solid var(--mep-line);
	border-radius: 12px;
	cursor: pointer;
	font-size: 14.5px;
	font-weight: 500;
	color: var(--mep-ink);
	background: #fff;
	transition: border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .12s ease;
}
.mep-native-payment-option:hover { border-color: #cfd4df; transform: translateY(-1px); }
.mep-native-payment-option input[type="radio"] {
	position: absolute;
	opacity: 0;
	width: 0;
	height: 0;
}
.mep-native-pay-check {
	flex: 0 0 auto;
	width: 20px;
	height: 20px;
	border-radius: 50%;
	border: 2px solid #cbd2de;
	background: #fff;
	position: relative;
	transition: border-color .18s ease;
}
.mep-native-pay-check::after {
	content: "";
	position: absolute;
	inset: 0;
	margin: auto;
	width: 9px;
	height: 9px;
	border-radius: 50%;
	background: var(--mep-accent);
	transform: scale(0);
	transition: transform .18s cubic-bezier(.16, 1, .3, 1);
}
.mep-native-payment-option:has(input:checked) {
	border-color: var(--mep-accent);
	background: var(--mep-accent-soft);
	box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.mep-native-payment-option:has(input:checked) .mep-native-pay-check { border-color: var(--mep-accent); }
.mep-native-payment-option:has(input:checked) .mep-native-pay-check::after { transform: scale(1); }
/* Keyboard focus ring fallback */
.mep-native-payment-option input:focus-visible + .mep-native-pay-check {
	box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.18);
}

/* Notices */
.mep-native-offline-notice,
.mep-native-login-required {
	background: #f0f9ff;
	border: 1px solid #bae6fd;
	border-left: 4px solid #0ea5e9;
	border-radius: 10px;
	padding: 14px 16px;
	margin-bottom: 22px;
	font-size: 13.5px;
	line-height: 1.5;
	color: #0c4a6e;
}
.mep-native-offline-notice p,
.mep-native-login-required p { margin: 0; }

/* Status message */
.mep-native-msg {
	margin-bottom: 18px;
	padding: 12px 15px;
	border-radius: 10px;
	font-size: 13.5px;
	font-weight: 500;
	line-height: 1.45;
}
.mep-native-msg.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.mep-native-msg.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* Footer */
.mep-native-modal-footer {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 18px 24px 22px;
	border-top: 1px solid var(--mep-line);
	background: #fff;
}
.mep-native-cancel-btn {
	flex: 0 0 auto;
	width: auto;
	height: auto;
	border-radius: 10px;
	padding: 12px 18px;
	font-size: 14px;
	font-weight: 600;
	color: var(--mep-ink-soft);
	background: var(--mep-bg-soft);
	border: 1.5px solid var(--mep-line);
	cursor: pointer;
	transition: background .18s ease, color .18s ease, border-color .18s ease;
}
.mep-native-cancel-btn:hover { background: #eef0f4; color: var(--mep-ink); transform: none; }

#mep-native-checkout-modal .mep-native-primary-btn {
	flex: 1 1 auto;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 9px;
	padding: 13px 20px;
	border: none;
	border-radius: 10px;
	font-size: 14.5px;
	font-weight: 700;
	line-height: 1.2;
	text-align: center;
	text-decoration: none;
	color: #fff;
	background: linear-gradient(135deg, var(--mep-accent), var(--mep-accent-2));
	box-shadow: 0 8px 20px -6px rgba(99, 102, 241, 0.6);
	cursor: pointer;
	transition: transform .14s ease, box-shadow .18s ease, filter .18s ease;
}
#mep-native-checkout-modal .mep-native-primary-btn:hover {
	transform: translateY(-1px);
	box-shadow: 0 12px 26px -6px rgba(99, 102, 241, 0.7);
	filter: brightness(1.04);
}
#mep-native-checkout-modal .mep-native-primary-btn:active { transform: translateY(0); }
.mep-native-btn-text,
.mep-native-btn-loading { display: inline-flex; align-items: center; gap: 9px; }

.mep-native-spinner {
	width: 15px;
	height: 15px;
	border: 2px solid rgba(255, 255, 255, 0.45);
	border-top-color: #fff;
	border-radius: 50%;
	animation: mepNativeSpin .7s linear infinite;
}
@keyframes mepNativeSpin { to { transform: rotate(360deg); } }

@media (max-width: 520px) {
	.mep-native-modal-box { max-width: 100%; border-radius: 16px; }
	.mep-native-modal-header { padding: 18px 18px 16px; }
	.mep-native-modal-body { padding: 18px 18px 2px; }
	.mep-native-modal-footer { padding: 16px 18px 18px; flex-wrap: wrap; }
	.mep-native-cancel-btn { order: 2; flex: 1 1 auto; }
	#mep-native-checkout-modal .mep-native-primary-btn { order: 1; width: 100%; flex-basis: 100%; }
}

@media (prefers-reduced-motion: reduce) {
	.mep-native-modal-box { animation: none; }
	.mep-native-modal-close:hover { transform: none; }
	.mep-native-payment-option:hover,
	#mep-native-checkout-modal .mep-native-primary-btn:hover { transform: none; }
}
</style>
