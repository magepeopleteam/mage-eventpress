<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Ticket_Price_Settings' ) ) {
		class MPWEM_Ticket_Price_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', array( $this, 'ticket_settings' ), 10, 2 );
				// The Payment Configuration modal is printed in the footer so it lives
				// OUTSIDE the event edit <form> — nested forms would break the embedded
				// WooCommerce gateway settings forms.
				add_action( 'admin_footer', array( $this, 'render_payment_modal' ) );
			}
			public function ticket_settings( $event_id, $event_infos ) {
				$reg_status        = is_array($event_infos) && array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
				$active_reg_status = $reg_status == 'on' ? 'mActive' : '';
				$display_rsvp = $reg_status == 'rsvp' ? '' : 'display:none;';

				// Currency symbol for the price inputs: WooCommerce currency when
				// WooCommerce is active, otherwise Event Settings -> Currency Symbol
				// (mep_currency_symbol). Drives the CSS ::before via a variable so
				// dynamically cloned ticket rows pick it up too.
				$currency_symbol = html_entity_decode( MPWEM_Global_Function::get_currency_symbol(), ENT_QUOTES, 'UTF-8' );
				$css_currency    = "'" . str_replace( array( '\\', "'" ), array( '\\\\', "\\'" ), $currency_symbol ) . "'";
				?>
				<style>:root{--mpwem-currency-symbol:<?php echo $css_currency; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;}</style>
                <div class="mpwem_style mp_tab_item mpwem_ticket_pricing_settings" data-tab-item="#mpwem_ticket_pricing_settings">
					<?php
					$payment_opts = get_option('payment_setting_sec', []);
					$woo_enabled = isset($payment_opts['mep_enable_wc_payment']) && $payment_opts['mep_enable_wc_payment'] === 'on';
					$paypal_enabled = isset($payment_opts['mep_paypal_enable']) && $payment_opts['mep_paypal_enable'] === 'on';
					$stripe_enabled = isset($payment_opts['mep_stripe_enable']) && $payment_opts['mep_stripe_enable'] === 'on';
					$show_payment_warning = !$woo_enabled && !$paypal_enabled && !$stripe_enabled;
					
					$wc_add_to_cart_redirect = isset($payment_opts['mep_wc_add_to_cart_redirect']) ? $payment_opts['mep_wc_add_to_cart_redirect'] : 'checkout';
					$wc_after_order_redirect = isset($payment_opts['mep_wc_after_order_redirect']) ? $payment_opts['mep_wc_after_order_redirect'] : 'plugin_thankyou';
					$wc_require_login = isset($payment_opts['mep_wc_require_login']) && $payment_opts['mep_wc_require_login'] === 'on';
					$wc_show_billing_info = isset($payment_opts['mep_wc_show_billing_info']) && $payment_opts['mep_wc_show_billing_info'] === 'on';
					$wc_confirm_ticket_status = isset($payment_opts['mep_wc_confirm_ticket_status']) && is_array($payment_opts['mep_wc_confirm_ticket_status']) ? $payment_opts['mep_wc_confirm_ticket_status'] : array('processing' => 'processing', 'completed' => 'completed');
					$wc_active = MPWEM_Global_Function::has_woocommerce();
					?>
					<div class="mpwem-ticket-warnings <?php echo esc_attr( $active_reg_status ); ?>" data-collapse="#mep_reg_status" style="margin-bottom: 20px;">
						<?php
						// This row is always visible. The exact enabled state of WooCommerce
						// gateways is resolved on the client (the modal already renders each
						// gateway's enabled state), so we render both the warning and the
						// "method enabled" state and let JS show the correct one. The data-*
						// flags pass the server-known state (saved option, PayPal, Stripe).
						?>
						<div class="mpwem-payment-warning" style="background: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; border-radius: var(--mpwem-radius); display: <?php echo $show_payment_warning ? 'flex' : 'none'; ?>; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
							<div>
								<strong style="display: block; font-size: 14px; margin-bottom: 5px;"><i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i><?php esc_html_e( 'No Payment Method Enabled', 'mage-eventpress' ); ?></strong>
								<span style="font-size: 13px;"><?php esc_html_e( 'Please configure at least one Payment gateway to start selling tickets.', 'mage-eventpress' ); ?></span>
							</div>
							<div>
								<button type="button" class="button button-primary mep-payment-settings-trigger" style="white-space: nowrap;"><?php esc_html_e( 'Configure Payments', 'mage-eventpress' ); ?></button>
							</div>
						</div>
						<div class="mpwem-payment-status"
							data-woo-enabled="<?php echo $woo_enabled ? '1' : '0'; ?>"
							data-option-set="<?php echo isset( $payment_opts['mep_enable_wc_payment'] ) ? '1' : '0'; ?>"
							data-wc-active="<?php echo $wc_active ? '1' : '0'; ?>"
							data-paypal="<?php echo $paypal_enabled ? '1' : '0'; ?>"
							data-stripe="<?php echo $stripe_enabled ? '1' : '0'; ?>"
							style="background: #e6f4ea; color: #0a7c2f; padding: 15px; border-left: 4px solid #34c759; border-radius: var(--mpwem-radius); display: <?php echo $show_payment_warning ? 'none' : 'flex'; ?>; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
							<div>
								<strong style="display: block; font-size: 14px; margin-bottom: 5px;"><i class="fas fa-check-circle" style="margin-right: 5px;"></i><?php esc_html_e( 'Payment Method Enabled', 'mage-eventpress' ); ?></strong>
								<span style="font-size: 13px;"><?php esc_html_e( 'Active:', 'mage-eventpress' ); ?> <span class="mpwem-payment-status__methods" style="font-weight: 600;"></span></span>
							</div>
							<div>
								<button type="button" class="button button-secondary mep-payment-settings-trigger" style="white-space: nowrap;"><?php esc_html_e( 'Change Payment Method', 'mage-eventpress' ); ?></button>
							</div>
						</div>
					</div>
					
					<?php $this->setting_head( $event_id, $event_infos ); ?>
                    <div class="<?php echo esc_attr( $active_reg_status ); ?>" data-collapse="#mep_reg_status">
						<?php $this->ticket_setting( $event_id, $event_infos ); ?>
						<?php $this->ex_service_setting( $event_id ); ?>
                    </div>
					
					<div class="mpwem-rsvp-settings-area" style="<?php echo esc_attr( $display_rsvp ); ?>">
						<?php $this->rsvp_setting( $event_id, $event_infos ); ?>
					</div>

					<?php $this->mep_event_pro_purchase_notice(); ?>
	                </div>
					<?php
			}
			/**
			 * Render the Payment Configuration modal + scripts in the admin footer,
			 * OUTSIDE the event edit <form>. Runs once, only on event edit screens.
			 */
			public function render_payment_modal() {
				static $rendered = false;
				if ( $rendered ) {
					return;
				}
				$screen  = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
				$allowed = array( 'mep_events', 'mep_events_page_mpwem_event_edit' );
				if ( ! $screen || ! in_array( $screen->id, $allowed, true ) ) {
					return;
				}
				$rendered = true;

				$payment_opts             = get_option( 'payment_setting_sec', array() );
				// Whether the admin has explicitly saved the WooCommerce gateway toggle
				// before. When they have not, JS auto-enables it if WooCommerce already
				// has an enabled gateway (detected from the rendered gateway list, so no
				// extra server-side WooCommerce call is made here).
				$wc_payment_option_set    = isset( $payment_opts['mep_enable_wc_payment'] );
				$woo_enabled              = $wc_payment_option_set && $payment_opts['mep_enable_wc_payment'] === 'on';
				$paypal_enabled           = isset( $payment_opts['mep_paypal_enable'] ) && $payment_opts['mep_paypal_enable'] === 'on';
				$stripe_enabled           = isset( $payment_opts['mep_stripe_enable'] ) && $payment_opts['mep_stripe_enable'] === 'on';
				$wc_add_to_cart_redirect  = isset( $payment_opts['mep_wc_add_to_cart_redirect'] ) ? $payment_opts['mep_wc_add_to_cart_redirect'] : 'checkout';
				$wc_after_order_redirect  = isset( $payment_opts['mep_wc_after_order_redirect'] ) ? $payment_opts['mep_wc_after_order_redirect'] : 'plugin_thankyou';
				$wc_require_login         = isset( $payment_opts['mep_wc_require_login'] ) && $payment_opts['mep_wc_require_login'] === 'on';
				$wc_show_billing_info     = isset( $payment_opts['mep_wc_show_billing_info'] ) && $payment_opts['mep_wc_show_billing_info'] === 'on';
				$wc_confirm_ticket_status = isset( $payment_opts['mep_wc_confirm_ticket_status'] ) && is_array( $payment_opts['mep_wc_confirm_ticket_status'] ) ? $payment_opts['mep_wc_confirm_ticket_status'] : array( 'processing' => 'processing', 'completed' => 'completed' );
				$wc_active                = MPWEM_Global_Function::has_woocommerce();

				$this->payment_modal_styles();
				?>
					<!-- Payment Configuration Modal -->
					<div id="mep-payment-settings-modal" class="mpwem-pm-overlay" style="display:none;">
						<div class="mpwem-pm-dialog">
							<div class="mpwem-pm-header">
								<div class="mpwem-pm-header__title">
									<span class="mpwem-pm-header__icon dashicons dashicons-money-alt"></span>
									<div>
										<h3><?php esc_html_e( 'Payment Configuration', 'mage-eventpress' ); ?></h3>
										<p><?php esc_html_e( 'These options apply to every event. Configure how customers pay for tickets.', 'mage-eventpress' ); ?></p>
									</div>
								</div>
								<button type="button" class="mpwem-pm-close mep-close-payment-modal" aria-label="<?php esc_attr_e( 'Close', 'mage-eventpress' ); ?>">&times;</button>
							</div>

							<div class="mpwem-pm-tabs">
								<button type="button" class="mep-modal-tab-btn active" data-target="#mep-modal-tab-woo"><span class="dashicons dashicons-cart"></span><?php esc_html_e( 'WooCommerce', 'mage-eventpress' ); ?></button>
								<button type="button" class="mep-modal-tab-btn" data-target="#mep-modal-tab-custom"><span class="dashicons dashicons-admin-network"></span><?php esc_html_e( 'Custom Payment', 'mage-eventpress' ); ?></button>
							</div>

							<div class="mpwem-pm-body">
								<div id="mep-payment-settings-form">
									<div id="mep-modal-tab-woo" class="mep-modal-tab-content">
										<div class="mpwem-pm-card">
											<h4 class="mpwem-pm-card__title"><?php esc_html_e( 'WooCommerce Payment', 'mage-eventpress' ); ?></h4>
											<div class="mpwem-woo-warning-notice" style="display: <?php echo $wc_active ? 'none' : 'block'; ?>; background: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; border-radius: var(--mpwem-radius); margin-bottom: 10px;">
												<div style="display: flex; flex-direction: column; align-items: flex-start; gap: 15px;">
													<div style="width: 100%;">
														<strong style="display: block; font-size: 14px; margin-bottom: 5px;"><i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i><?php esc_html_e( 'Notice: WooCommerce is Not Activated', 'mage-eventpress' ); ?></strong>
														<span style="font-size: 13px; display: block;"><?php esc_html_e( 'You can explore and manage ticket types, prices, and related settings here. To actually use the "Ticket-Selling" event type and allow ticket sales, you must install and activate WooCommerce.', 'mage-eventpress' ); ?></span>
													</div>
													<div class="mep-woo-install-action-wrapper" style="display:flex; align-items:center; gap:15px; width:100%;">
														<button type="button" class="button button-primary ticket-settings-trigger" style="white-space: nowrap;"><?php echo file_exists( WP_PLUGIN_DIR . "/woocommerce/woocommerce.php" ) ? esc_html__( "Activate WooCommerce Now", "mage-eventpress" ) : esc_html__( "Install & Activate Now", "mage-eventpress" ); ?></button>
														<div class="mep-wc-install-progress" style="display:none; flex:1;">
															<div class="mep-wc-install-progress-bar">
																<div class="mep-wc-install-progress-fill"></div>
															</div>
															<p class="mep-wc-install-status-text"></p>
														</div>
													</div>
													
													<script>
													jQuery(document).ready(function($) {
														$(document).on('click', '.ticket-settings-trigger', function(e) {
															e.preventDefault();
															var $btn           = $(this);
															var $wrapper       = $btn.closest('.mep-woo-install-action-wrapper');
															var $progress      = $wrapper.find('.mep-wc-install-progress');
															var $fill          = $wrapper.find('.mep-wc-install-progress-fill');
															var $status        = $wrapper.find('.mep-wc-install-status-text');
															var $notice        = $btn.closest('.mpwem-woo-warning-notice');
															var $warningText   = $notice.find('>div>div:first-child');
															var $tabHeader     = $btn.closest('#mep-modal-tab-woo').find('h4').first();
															var origStyle      = { bg: $notice.css('background-color'), border: $notice.css('border-left-color'), pad: $notice.css('padding') };
															
															$btn.hide();
															$warningText.hide();
															$notice.css({ 'background': 'transparent', 'border-left': 'none', 'padding': '10px 0' });
															$fill.css('width', '0%');
															$status.removeClass('mep-success mep-error').addClass('mep-loading');
															$progress.fadeIn(250);
															
															var isInstalled = <?php echo file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ? 'true' : 'false'; ?>;
															$tabHeader.text( isInstalled
																? <?php echo wp_json_encode( __( 'Activating WooCommerce…', 'mage-eventpress' ) ); ?>
																: <?php echo wp_json_encode( __( 'Installing WooCommerce…', 'mage-eventpress' ) ); ?>
															);
															var nonce       = '<?php echo esc_js( wp_create_nonce( 'mep_install_wc' ) ); ?>';
															var texts = isInstalled
																? [<?php echo implode( ',', array_map( 'json_encode', array(
																	__( 'Activating WooCommerce...', 'mage-eventpress' ),
																	__( 'Configuring settings...', 'mage-eventpress' ),
																	__( 'Finalizing setup...', 'mage-eventpress' ),
																) ) ); ?>]
																: [<?php echo implode( ',', array_map( 'json_encode', array(
																	__( 'Downloading WooCommerce...', 'mage-eventpress' ),
																	__( 'Installing WooCommerce...', 'mage-eventpress' ),
																	__( 'Activating WooCommerce...', 'mage-eventpress' ),
																	__( 'Configuring settings...', 'mage-eventpress' ),
																	__( 'Finalizing...', 'mage-eventpress' ),
																) ) ); ?>];
															
															// easing phase: 5s for activate-only, 30s for fresh install
															var easeDuration = isInstalled ? 5000 : 30000;
															var startTime    = Date.now();
															var isDone       = false;
															var frameId;
															
															$status.text(texts[0]);
															
															function animateBar() {
																if (isDone) return;
																var elapsed = Date.now() - startTime;
																var pct;
																if (elapsed < easeDuration) {
																	// easeOutQuad: 0 → 95% over easeDuration
																	var raw   = elapsed / easeDuration;
																	var eased = raw * (2 - raw);
																	pct = eased * 95;
																} else {
																	// Slow crawl: asymptotically approaches 99% — always moving, never stuck
																	var extra = elapsed - easeDuration;
																	pct = 95 + 4 * (1 - Math.exp(-extra / 60000));
																}
																$fill.css('width', pct + '%');
																var idx = Math.min(Math.floor((pct / 99) * texts.length), texts.length - 1);
																$status.text(texts[idx] + ' ' + Math.round(pct) + '%');
																frameId = requestAnimationFrame(animateBar); // always keep going until AJAX responds
															}
															frameId = requestAnimationFrame(animateBar);
															
															$.ajax({
																url:     ajaxurl,
																type:    'POST',
																data:    { action: 'mep_install_activate_wc', nonce: nonce },
																timeout: 300000, // 5-minute timeout — enough for any slow connection
																success: function(response) {
																	isDone = true;
																	cancelAnimationFrame(frameId);
																	$fill.css('width', '100%');
																	if (response.success) {
																		$status.removeClass('mep-loading mep-error').addClass('mep-success');
																		$status.text(<?php echo wp_json_encode( __( 'Successfully Activated! 100%', 'mage-eventpress' ) ); ?>);
																		setTimeout(function() {
																			$tabHeader.text(<?php echo wp_json_encode( __( 'WooCommerce Payment', 'mage-eventpress' ) ); ?>);
																			$notice.slideUp(300);
																			$('.mep-woo-enable-label').css('display', 'flex').hide().fadeIn(300);
																			$('#mep_modal_enable_wc').trigger('change');
																		}, 1200);
																	} else {
																		$status.removeClass('mep-loading mep-success').addClass('mep-error');
																		$status.text(<?php echo wp_json_encode( __( 'Error: ', 'mage-eventpress' ) ); ?> + (response.data || 'Unknown error'));
																		setTimeout(function() {
																			$tabHeader.text(<?php echo wp_json_encode( __( 'WooCommerce Payment', 'mage-eventpress' ) ); ?>);
																			$progress.hide();
																			$btn.show();
																			$warningText.show();
																			$notice.css({ 'background': '#fff3cd', 'border-left': '4px solid #ffeeba', 'padding': '15px' });
																		}, 5000);
																	}
																},
																error: function() {
																	isDone = true;
																	cancelAnimationFrame(frameId);
																	$fill.css('width', '100%');
																	$status.removeClass('mep-loading mep-success').addClass('mep-error');
																	$status.text(<?php echo wp_json_encode( __( 'A network error occurred. Please try again.', 'mage-eventpress' ) ); ?>);
																	setTimeout(function() {
																		$tabHeader.text(<?php echo wp_json_encode( __( 'WooCommerce Payment', 'mage-eventpress' ) ); ?>);
																		$progress.hide();
																		$btn.show();
																		$warningText.show();
																		$notice.css({ 'background': '#fff3cd', 'border-left': '4px solid #ffeeba', 'padding': '15px' });
																	}, 5000);
																}
															});
														});
													});
													</script>
												</div>
											</div>

											<label class="mpwem-pm-toggle-row mep-woo-enable-label" style="display:flex;">
												<span class="mpwem-pm-toggle-row__text">
													<span class="mpwem-pm-toggle-row__label"><?php esc_html_e( 'Enable WooCommerce Payment Gateway', 'mage-eventpress' ); ?></span>
													<span class="mpwem-pm-toggle-row__sub"><?php esc_html_e( 'Process ticket checkout through WooCommerce.', 'mage-eventpress' ); ?></span>
												</span>
												<span class="mpwem-pm-switch">
													<input type="checkbox" name="mep_enable_wc_payment" id="mep_modal_enable_wc" value="on" <?php checked( $woo_enabled ); ?> />
													<span class="mpwem-pm-switch__slider"></span>
												</span>
											</label>
										</div><!-- /.mpwem-pm-card -->

										<div class="mep-modal-wc-fields" style="display: <?php echo $woo_enabled ? 'block' : 'none'; ?>;">
											<!-- Payment Methods accordion (expanded by default) -->
											<div class="mpwem-pm-acc mpwem-pm-acc--methods is-open">
												<button type="button" class="mpwem-pm-acc__bar">
													<span class="mpwem-pm-acc__title"><?php esc_html_e( 'Payment Methods', 'mage-eventpress' ); ?></span>
													<span class="mpwem-pm-acc__arrow dashicons dashicons-arrow-down-alt2"></span>
												</button>
												<div class="mpwem-pm-acc__panel">
													<p class="mpwem-pm-card__sub"><?php esc_html_e( 'Enable and configure the WooCommerce gateways customers can pay with.', 'mage-eventpress' ); ?></p>
													<?php
													if ( class_exists( 'MPWEM_WC_Payment_Manager' ) ) {
														MPWEM_WC_Payment_Manager::instance()->render();
													}
													?>
												</div>
											</div>

											<!-- Additional accordion (collapsed by default) -->
											<div class="mpwem-pm-acc mpwem-pm-acc--additional">
												<button type="button" class="mpwem-pm-acc__bar">
													<span class="mpwem-pm-acc__title"><?php esc_html_e( 'Additional', 'mage-eventpress' ); ?></span>
													<span class="mpwem-pm-acc__arrow dashicons dashicons-arrow-down-alt2"></span>
												</button>
												<div class="mpwem-pm-acc__panel" style="display:none;">
													<div class="mpwem-pm-field">
														<label class="mpwem-pm-label"><?php esc_html_e( 'After Adding to Cart, Redirect to', 'mage-eventpress' ); ?></label>
														<select class="mpwem-pm-control" name="mep_wc_add_to_cart_redirect">
															<option value="cart" <?php selected( $wc_add_to_cart_redirect, 'cart' ); ?>><?php esc_html_e( 'Cart', 'mage-eventpress' ); ?></option>
															<option value="checkout" <?php selected( $wc_add_to_cart_redirect, 'checkout' ); ?>><?php esc_html_e( 'Checkout', 'mage-eventpress' ); ?></option>
														</select>
													</div>
													<div class="mpwem-pm-field">
														<label class="mpwem-pm-label"><?php esc_html_e( 'After Confirming the Order, Redirect To', 'mage-eventpress' ); ?></label>
														<select class="mpwem-pm-control" name="mep_wc_after_order_redirect">
															<option value="plugin_thankyou" <?php selected( $wc_after_order_redirect, 'plugin_thankyou' ); ?>><?php esc_html_e( 'Plugin Thank You Page', 'mage-eventpress' ); ?></option>
															<option value="woo_thankyou" <?php selected( $wc_after_order_redirect, 'woo_thankyou' ); ?>><?php esc_html_e( 'WooCommerce Thank You Page', 'mage-eventpress' ); ?></option>
														</select>
													</div>
													<div class="mpwem-pm-checks">
														<label class="mpwem-pm-check">
															<input type="checkbox" name="mep_wc_require_login" value="on" <?php checked( $wc_require_login ); ?> />
															<span><?php esc_html_e( 'Require Account Login to Purchase', 'mage-eventpress' ); ?></span>
														</label>
														<label class="mpwem-pm-check">
															<input type="checkbox" name="mep_wc_show_billing_info" value="on" <?php checked( $wc_show_billing_info ); ?> />
															<span><?php esc_html_e( 'Show Billing Info on Checkout', 'mage-eventpress' ); ?></span>
														</label>
													</div>
													<div class="mpwem-pm-field" style="margin-top:6px;">
														<label class="mpwem-pm-label"><?php esc_html_e( 'Confirm Ticket Based on Payment Status', 'mage-eventpress' ); ?></label>
														<div class="mpwem-pm-checks mpwem-pm-checks--inline">
															<label class="mpwem-pm-check"><input type="checkbox" name="mep_wc_confirm_ticket_status[]" value="pending" <?php echo in_array('pending', $wc_confirm_ticket_status) ? 'checked' : ''; ?>> <span><?php esc_html_e( 'Pending', 'mage-eventpress' ); ?></span></label>
															<label class="mpwem-pm-check"><input type="checkbox" name="mep_wc_confirm_ticket_status[]" value="processing" <?php echo in_array('processing', $wc_confirm_ticket_status) ? 'checked' : ''; ?>> <span><?php esc_html_e( 'Processing', 'mage-eventpress' ); ?></span></label>
															<label class="mpwem-pm-check"><input type="checkbox" name="mep_wc_confirm_ticket_status[]" value="on-hold" <?php echo in_array('on-hold', $wc_confirm_ticket_status) ? 'checked' : ''; ?>> <span><?php esc_html_e( 'On hold', 'mage-eventpress' ); ?></span></label>
															<label class="mpwem-pm-check"><input type="checkbox" name="mep_wc_confirm_ticket_status[]" value="completed" <?php echo in_array('completed', $wc_confirm_ticket_status) ? 'checked' : ''; ?>> <span><?php esc_html_e( 'Completed', 'mage-eventpress' ); ?></span></label>
														</div>
													</div>
												</div>
											</div>
										</div><!-- /.mep-modal-wc-fields -->
									</div>

									<div id="mep-modal-tab-custom" class="mep-modal-tab-content" style="display:none;">
										<?php $is_pro = mep_check_plugin_installed( 'mage-eventpress-pro/woocommerce-event-manager-pro.php' ); ?>
										<div class="mpwem-pm-card">
											<h4 class="mpwem-pm-card__title"><?php esc_html_e( 'Custom Payment Gateways', 'mage-eventpress' ); ?></h4>
											<p class="mpwem-pm-card__sub"><?php esc_html_e( 'Accept payments without WooCommerce through the native checkout.', 'mage-eventpress' ); ?></p>

											<div class="mpwem-pm-gateway">
												<div class="mpwem-pm-gateway__main">
													<span class="mpwem-pm-gateway__icon">
														<svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z" fill="#003087"/>
															<path d="M11.5 7.1c.05.27.01.59-.09.91-.98 5.05-4.35 6.79-8.65 6.79H4.95l-1.12 7.11a.64.64 0 0 0 .63.74h4.6a.64.64 0 0 0 .63-.54l.87-5.55a.64.64 0 0 1 .63-.54h1.08c3.5 0 6.23-1.42 7.03-5.52.2-.99.23-1.89.09-2.65-.48-2.6-2.58-3.41-5.63-3.41h-2.22z" fill="#0079C1"/>
															<path d="M11.5 7.1c-.02-.13-.05-.27-.08-.41C10.3 5.4 8.3 4.86 5.73 4.86H3.54l-1.5 9.54h2.72c.52 0 .97-.38 1.05-.9l.87-5.5c.08-.52.53-.9.1-.9h2.19c3.5 0 6.23-1.42 7.03-5.52-.06.32-.14.64-.09.91z" fill="#00457C"/>
														</svg>
													</span>
													<div>
														<strong class="mpwem-pm-gateway__name"><?php esc_html_e( 'PayPal', 'mage-eventpress' ); ?></strong>
													</div>
												</div>
												<?php if ( $is_pro ) : ?>
													<button type="button" id="mep-paypal-configure-btn" class="button button-secondary"><?php esc_html_e( 'Configure', 'mage-eventpress' ); ?></button>
												<?php else : ?>
													<span class="mpwem-pm-pro-badge" title="<?php esc_attr_e('Available in Pro version', 'mage-eventpress'); ?>">PRO</span>
												<?php endif; ?>
											</div>

											<div class="mpwem-pm-gateway">
												<div class="mpwem-pm-gateway__main">
													<span class="mpwem-pm-gateway__icon">
														<svg width="30" height="30" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
															<path fill="#6772E5" d="M14.07 15.11c-1.85-.43-2.61-.79-2.61-1.63 0-.79.75-1.33 1.95-1.33 1.34 0 2.87.41 4.31 1.09V8.65c-1.39-.56-2.93-.84-4.52-.84-3.8 0-6.66 1.96-6.66 5.25 0 3.73 3.32 4.96 6.03 5.61 2.05.49 2.8.92 2.8 1.8 0 .86-.87 1.48-2.3 1.48-1.57 0-3.37-.53-5.06-1.54v4.75c1.67.75 3.59 1.13 5.51 1.13 4.13 0 7-2 7-5.34-.01-3.6-3.6-4.41-6.45-5.84z"/>
														</svg>
													</span>
													<div>
														<strong class="mpwem-pm-gateway__name"><?php esc_html_e( 'Stripe', 'mage-eventpress' ); ?></strong>
													</div>
												</div>
												<?php if ( $is_pro ) : ?>
													<button type="button" id="mep-stripe-configure-btn" class="button button-secondary"><?php esc_html_e( 'Configure', 'mage-eventpress' ); ?></button>
												<?php else : ?>
													<span class="mpwem-pm-pro-badge" title="<?php esc_attr_e('Available in Pro version', 'mage-eventpress'); ?>">PRO</span>
												<?php endif; ?>
											</div>

											<div class="mpwem-pm-gateway">
												<div class="mpwem-pm-gateway__main">
													<span class="mpwem-pm-gateway__icon">
														<svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M3 19h18a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" stroke="#0f766e" stroke-width="1.6" stroke-linejoin="round"/>
															<path d="M2 10h20M6 14h4" stroke="#0f766e" stroke-width="1.6" stroke-linecap="round"/>
														</svg>
													</span>
													<div>
														<strong class="mpwem-pm-gateway__name"><?php esc_html_e( 'Offline Payment', 'mage-eventpress' ); ?></strong>
													</div>
												</div>
												<button type="button" id="mep-offline-configure-btn" class="button button-secondary"><?php esc_html_e( 'Configure', 'mage-eventpress' ); ?></button>
											</div>
										</div>

										<?php
										$payment_opts    = get_option( 'payment_setting_sec', array() );
										$confirm_page_id = ! empty( $payment_opts['mep_confirmation_page_id'] ) ? absint( $payment_opts['mep_confirmation_page_id'] ) : 0;
										?>
										<div class="mpwem-pm-card">
											<div class="mpwem-pm-confirm-row">
												<div class="mpwem-pm-confirm-row__text">
													<label class="mpwem-pm-label" for="mep_confirmation_page_id"><?php esc_html_e( 'Booking Confirmation Page', 'mage-eventpress' ); ?></label>
													<p class="mpwem-pm-card__sub"><?php esc_html_e( 'Select a page with the [mep_booking_confirmation] shortcode. After booking, customers are redirected here instead of back to the event page.', 'mage-eventpress' ); ?></p>
												</div>
												<div class="mpwem-pm-confirm-row__control">
													<?php wp_dropdown_pages( array(
														'name'              => 'mep_confirmation_page_id',
														'id'                => 'mep_confirmation_page_id',
														'selected'          => $confirm_page_id,
														'show_option_none'  => __( '— Default —', 'mage-eventpress' ),
														'option_none_value' => '0',
														'class'             => 'mpwem-pm-control',
													) ); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="mpwem-pm-footer">
								<span id="mep-payment-save-status" class="mpwem-pm-save-status" style="display:none;"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Saved!', 'mage-eventpress' ); ?></span>
								<button type="button" class="button button-secondary mep-close-payment-modal"><?php esc_html_e( 'Cancel', 'mage-eventpress' ); ?></button>
								<button type="button" id="mep-save-payment-settings" class="button button-primary"><?php esc_html_e( 'Save Changes', 'mage-eventpress' ); ?></button>
							</div>
						</div>
					</div>
					
					<script type="text/javascript">
					jQuery(document).ready(function($) {
						if ($('#mep-progress-styles').length === 0) {
							var styles = `
							.mep-wc-install-progress { width: 100%; padding: 10px 0; margin-bottom: 8px; animation: mpwemFadeIn 0.35s ease both; flex: 1; }
							@keyframes mpwemFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
							.mep-wc-install-progress-bar { width: 100%; height: 8px; background: #f0f0f1; border-radius: 100px; overflow: hidden; }
							.mep-wc-install-progress-fill { height: 100%; width: 0%; border-radius: 100px; background: linear-gradient(90deg, #7b5ea7, #9b72cf); transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1); position: relative; }
							.mep-wc-install-progress-fill::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%); animation: mpwemShimmer 1.5s linear infinite; }
							@keyframes mpwemShimmer { from { transform: translateX(-100%); } to { transform: translateX(100%); } }
							.mep-wc-install-status-text { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; font-size: 14px; color: #50575e; margin: 10px 0 0; display: flex; align-items: center; justify-content: center; gap: 8px; }
							.mep-wc-install-status-text.mep-loading::before { content: ''; display: inline-block; width: 14px; height: 14px; border: 2px solid #dcdde1; border-top-color: #7b5ea7; border-radius: 50%; animation: mpwemSpin 0.7s linear infinite; flex-shrink: 0; }
							@keyframes mpwemSpin { to { transform: rotate(360deg); } }
							.mep-wc-install-status-text.mep-success { color: #039855; }
							.mep-wc-install-status-text.mep-success::before { display: none; }
							.mep-wc-install-status-text.mep-error { color: #d92d20; }
							.mep-wc-install-status-text.mep-error::before { display: none; }
							`;
							$('head').append('<style id="mep-progress-styles">' + styles + '</style>');
						}
						// Open Payment Modal
						$(document).on('click', '.mep-payment-settings-trigger', function(e) {
							e.preventDefault();
							$('#mep-payment-settings-modal').css('display', 'flex').hide().fadeIn(200);
						});

						// Close Payment Modal
						$(document).on('click', '.mep-close-payment-modal', function() {
							$('#mep-payment-settings-modal').fadeOut(200);
						});
						// Close on backdrop click
						$(document).on('click', '#mep-payment-settings-modal', function(e) {
							if (e.target === this) { $(this).fadeOut(200); }
						});

						// Toggle WooCommerce fields
						$(document).on('change', '#mep_modal_enable_wc', function() {
							if ($(this).is(':checked')) {
								$('.mep-modal-wc-fields').stop(true, true).slideDown(200);
							} else {
								$('.mep-modal-wc-fields').stop(true, true).slideUp(200);
							}
						});

						// Resolve the payment status row on the ticket tab and the modal
						// toggle from the gateway state the modal already renders (no extra
						// server-side WooCommerce call). The status row stays visible at all
						// times: it shows the enabled method name + a "Change" button, or the
						// "no method" warning when nothing is configured.
						function mepRefreshPaymentStatus() {
							var $statusRow = $('.mpwem-payment-status');
							var $warnRow   = $('.mpwem-payment-warning');
							if (!$statusRow.length) {
								return;
							}

							var optionSet = $statusRow.data('option-set') == 1;
							var wooOption = $statusRow.data('woo-enabled') == 1;
							var wcActive  = $statusRow.data('wc-active') == 1;
							var paypalOn  = $statusRow.data('paypal') == 1;
							var stripeOn  = $statusRow.data('stripe') == 1;

							// Use the live checkbox state when the modal is open; fall back to
							// saved data-attrs when the modal hasn't been opened yet.
							var $wcToggle   = $('#mep_modal_enable_wc');
							var wcSectionOn = $wcToggle.length ? $wcToggle.is(':checked') : (wooOption || !optionSet);

							// Split enabled gateways: WC-registered vs built-in (BACS / COD).
							var wcGwNames    = [];
							var builtinNames = [];
							$('.mep-gw-toggle-input:checked').each(function() {
								var $card  = $(this).closest('.mep-gw-card');
								var source = $card.data('gateway-source') || 'wc';
								var name   = $card.find('.mep-gw-title').first().text().trim();
								if (!name) { return; }
								if (source === 'builtin') {
									builtinNames.push(name);
								} else {
									wcGwNames.push(name);
								}
							});

							var methods = [];
							if (wcSectionOn) {
								// WC-registered gateways only count when WooCommerce is active.
								if (wcActive && wcGwNames.length > 0) {
									methods.push('WooCommerce (' + wcGwNames.join(', ') + ')');
								}
								// Built-in gateways (Bank Transfer, Cash on Delivery) are always
								// valid regardless of WooCommerce being active.
								if (builtinNames.length > 0) {
									methods = methods.concat(builtinNames);
								}
							}
							if (paypalOn) { methods.push('PayPal'); }
							if (stripeOn) { methods.push('Stripe'); }

							if (methods.length > 0) {
								$statusRow.find('.mpwem-payment-status__methods').text(methods.join(', '));
								$warnRow.hide();
								$statusRow.css('display', 'flex');
							} else {
								$statusRow.hide();
								$warnRow.css('display', 'flex');
							}

							// Auto-enable the payment section toggle when a gateway is already
							// enabled but the admin hasn't explicitly saved the setting yet.
							var anyGwEnabled = wcGwNames.length > 0 || builtinNames.length > 0;
							if (!optionSet && anyGwEnabled) {
								var $toggle = $('#mep_modal_enable_wc');
								if ($toggle.length && !$toggle.is(':checked')) {
									$toggle.prop('checked', true).trigger('change');
								}
							}
						}

						mepRefreshPaymentStatus();

						// Keep the status row in sync when a gateway is toggled inside the modal.
						$(document).on('change', '.mep-gw-toggle-input', function() {
							mepRefreshPaymentStatus();
						});

						// Modal Tabs Switching
						$(document).on('click', '.mep-modal-tab-btn', function(e) {
							e.preventDefault();
							$('.mep-modal-tab-btn').removeClass('active');
							$(this).addClass('active');
							$('.mep-modal-tab-content').hide();
							$($(this).data('target')).fadeIn(200);
						});

						// WooCommerce accordions — only one open at a time
						$(document).on('click', '.mpwem-pm-acc__bar', function(e) {
							e.preventDefault();
							var $acc = $(this).closest('.mpwem-pm-acc');
							if ($acc.hasClass('is-open')) {
								$acc.removeClass('is-open').find('.mpwem-pm-acc__panel').stop(true, true).slideUp(180);
								return;
							}
							$acc.siblings('.mpwem-pm-acc.is-open').removeClass('is-open')
								.find('.mpwem-pm-acc__panel').stop(true, true).slideUp(180);
							$acc.addClass('is-open').find('.mpwem-pm-acc__panel').stop(true, true).slideDown(180);
						});
						
						// Save Payment Settings
						$('#mep-save-payment-settings').click(function() {
							var $btn = $(this);
							var $status = $('#mep-payment-save-status');
							var formData = $('#mep-payment-settings-form :input').serialize();
							
							$btn.prop('disabled', true).css('opacity', '0.6');
							$status.hide();
							
							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: formData + '&action=mep_save_payment_settings_modal',
								success: function(response) {
									if (response.success) {
										// No page reload — reloading would re-render the PayPal/Stripe
										// Configure modals from the DB and discard anything typed there
										// that wasn't saved with the gateway's own Save button. Those
										// gateways are saved independently via their own modals.
										$status.css('color', '#0f5132').html('<span class="dashicons dashicons-yes"></span> ' + response.data).fadeIn(200);
										$btn.prop('disabled', false).css('opacity', '1');

										// Sync the ticket-step payment status with what was just saved so the
										// "No Payment Method Enabled" banner clears without a page reload.
										var $statusRow = $('.mpwem-payment-status');
										if ($statusRow.length) {
											var wooOn = $('#mep_modal_enable_wc').is(':checked') ? 1 : 0;
											$statusRow.data('option-set', 1).attr('data-option-set', '1');
											$statusRow.data('woo-enabled', wooOn).attr('data-woo-enabled', String(wooOn));
										}
										mepRefreshPaymentStatus();

										// Confirm briefly, then auto-close the modal.
										setTimeout(function() {
											$status.fadeOut(300);
											$('#mep-payment-settings-modal').fadeOut(200);
										}, 1200);
									} else {
										$status.css('color', '#dc3545').html('<span class="dashicons dashicons-no"></span> ' + (response.data || 'Error')).fadeIn(200);
										$btn.prop('disabled', false).css('opacity', '1');
									}
								},
								error: function() {
									$status.css('color', '#dc3545').html('<span class="dashicons dashicons-no"></span> Network Error').fadeIn(200);
									$btn.prop('disabled', false).css('opacity', '1');
								}
							});
						});
					});
					</script>
				<?php
			}
			/**
			 * Inline styles for the Payment Configuration modal.
			 * Printed once per request (guarded by a static flag).
			 */
			public function payment_modal_styles() {
				static $printed = false;
				if ( $printed ) {
					return;
				}
				$printed = true;
				?>
				<style>
					/* ---- Payment Configuration modal ---- */
					.mpwem-pm-overlay {
						position: fixed; inset: 0; z-index: 999999;
						background: rgba(15, 23, 42, 0.55);
						backdrop-filter: blur(3px);
						align-items: center; justify-content: center;
					}
					/* PayPal/Stripe configuration modals (rendered separately) must
					   stack ABOVE this modal when opened from the Custom Payment tab. */
					.mep-gw-modal { z-index: 1000001 !important; }
					.mpwem-pm-dialog {
						background: #fff; border-radius: 14px;
						width: 640px; max-width: 94vw; max-height: 92vh;
						display: flex; flex-direction: column; overflow: hidden;
						box-shadow: 0 24px 64px rgba(0,0,0,0.30);
						animation: mpwemPmIn .22s ease;
					}
					@keyframes mpwemPmIn { from { transform: translateY(12px) scale(.97); opacity: 0; } to { transform: none; opacity: 1; } }
					.mpwem-pm-header {
						display: flex; align-items: center; justify-content: space-between; gap: 16px;
						padding: 20px 24px;
						background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
						color: #fff;
					}
					.mpwem-pm-header__title { display: flex; align-items: center; gap: 14px; }
					.mpwem-pm-header__icon {
						width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
						background: rgba(255,255,255,0.18);
						display: flex; align-items: center; justify-content: center;
						font-size: 22px; line-height: 1;
					}
					.mpwem-pm-header h3 { margin: 0; font-size: 18px; font-weight: 700; color: #fff; line-height: 1.2; }
					.mpwem-pm-header p { margin: 3px 0 0; font-size: 12.5px; color: rgba(255,255,255,0.85); }
					.mpwem-pm-close {
						background: rgba(255,255,255,0.18); border: none; border-radius: 50%;
						width: 34px; height: 34px; flex-shrink: 0;
						font-size: 22px; line-height: 1; color: #fff; cursor: pointer;
						display: flex; align-items: center; justify-content: center; transition: background .2s;
					}
					.mpwem-pm-close:hover { background: rgba(255,255,255,0.34); }

					/* Tabs */
					.mpwem-pm-tabs {
						display: flex; gap: 6px; padding: 14px 24px 0;
						background: #f6f7f9; border-bottom: 1px solid #e2e4e7;
					}
					.mpwem-pm-tabs .mep-modal-tab-btn {
						display: inline-flex; align-items: center; gap: 7px;
						background: transparent; border: none; cursor: pointer;
						padding: 11px 16px; margin: 0; font-size: 13.5px; font-weight: 600;
						color: #646970; border-bottom: 3px solid transparent;
						border-radius: 8px 8px 0 0; transition: color .2s, border-color .2s, background .2s;
					}
					.mpwem-pm-tabs .mep-modal-tab-btn .dashicons { font-size: 17px; width: 17px; height: 17px; }
					.mpwem-pm-tabs .mep-modal-tab-btn:hover { color: #2271b1; background: #eef5fb; }
					.mpwem-pm-tabs .mep-modal-tab-btn.active { color: #2271b1; border-bottom-color: #2271b1; background: #fff; }

					/* Body */
					.mpwem-pm-body { padding: 20px 24px; overflow-y: auto; flex: 1; background: #f6f7f9; }

					/* Cards */
					.mpwem-pm-card {
						background: #fff; border: 1px solid #e2e4e7; border-radius: 10px;
						padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);
					}
					.mpwem-pm-card:last-child { margin-bottom: 0; }
					.mpwem-pm-card__title { margin: 0 0 2px; font-size: 14.5px; font-weight: 700; color: #1d2327; }
					.mpwem-pm-card__sub { margin: 4px 0 14px; font-size: 12.5px; color: #6b7280; line-height: 1.5; }
					.mpwem-pm-card__title + .mpwem-pm-field,
					.mpwem-pm-card__title + .mpwem-pm-checks { margin-top: 14px; }

					/* Toggle row (enable WooCommerce) */
					.mpwem-pm-toggle-row {
						display: flex; align-items: center; justify-content: space-between; gap: 16px;
						margin-top: 14px; padding: 14px 16px; cursor: pointer;
						background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px;
					}
					.mpwem-pm-toggle-row__text { display: flex; flex-direction: column; }
					.mpwem-pm-toggle-row__label { font-size: 13.5px; font-weight: 600; color: #111827; }
					.mpwem-pm-toggle-row__sub { font-size: 12px; color: #6b7280; margin-top: 2px; }

					/* Switch */
					.mpwem-pm-switch { position: relative; display: inline-block; width: 46px; height: 25px; flex-shrink: 0; }
					.mpwem-pm-switch input { opacity: 0; width: 0; height: 0; }
					.mpwem-pm-switch__slider { position: absolute; inset: 0; cursor: pointer; background: #cbd5e1; border-radius: 25px; transition: .3s; }
					.mpwem-pm-switch__slider:before { content: ""; position: absolute; height: 19px; width: 19px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .3s; box-shadow: 0 1px 3px rgba(0,0,0,0.25); }
					.mpwem-pm-switch input:checked + .mpwem-pm-switch__slider { background: #2271b1; }
					.mpwem-pm-switch input:checked + .mpwem-pm-switch__slider:before { transform: translateX(21px); }

					/* Fields */
					.mpwem-pm-field { margin-bottom: 14px; }
					.mpwem-pm-field:last-child { margin-bottom: 0; }
					.mpwem-pm-label { display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px; }
					.mpwem-pm-control {
						width: 100%; max-width: 320px; box-sizing: border-box;
						border: 1px solid #d1d5db; border-radius: 7px; padding: 8px 12px; font-size: 13px; background: #fff;
					}
					.mpwem-pm-control:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }

					/* Check rows */
					.mpwem-pm-checks { display: flex; flex-direction: column; gap: 10px; }
					.mpwem-pm-checks--inline { flex-direction: row; flex-wrap: wrap; gap: 10px 18px; }
					.mpwem-pm-check { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #374151; }
					.mpwem-pm-check input { margin: 0; }

					/* Custom gateways */
					.mpwem-pm-gateway {
						display: flex; align-items: center; justify-content: space-between; gap: 14px;
						padding: 14px 16px; margin-top: 12px;
						background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px;
					}
					.mpwem-pm-gateway__main { display: flex; align-items: center; gap: 14px; }
					.mpwem-pm-gateway__icon { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
					.mpwem-pm-gateway__name { display: block; font-size: 14px; font-weight: 600; color: #1d2327; margin-bottom: 3px; }
					.mpwem-pm-gateway__enable { font-size: 12.5px; color: #555; }
					.mpwem-pm-pro-badge {
						background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); color: #fff;
						padding: 4px 10px; border-radius: 4px; font-weight: 700; font-size: 11px;
						text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(253,160,133,0.3); user-select: none;
					}

					/* Confirmation page row */
					.mpwem-pm-confirm-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }
					.mpwem-pm-confirm-row__text { flex: 1; min-width: 0; }
					.mpwem-pm-confirm-row__text .mpwem-pm-card__sub { margin: 4px 0 0; }
					.mpwem-pm-confirm-row__control { flex-shrink: 0; width: 240px; }
					.mpwem-pm-confirm-row__control .mpwem-pm-control { max-width: 100%; }

					/* Accordions (WooCommerce tab) */
					.mpwem-pm-acc {
						background: #fff; border: 1px solid #e2e4e7; border-radius: 10px;
						margin-bottom: 14px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.04);
					}
					.mpwem-pm-acc:last-child { margin-bottom: 0; }
					.mpwem-pm-acc.is-open { border-color: #2271b1; }
					.mpwem-pm-acc__bar {
						display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%;
						background: #f6f7f9; border: none; border-bottom: 1px solid transparent;
						padding: 14px 18px; cursor: pointer; text-align: left; transition: background .2s, color .2s;
					}
					.mpwem-pm-acc__bar:hover { background: #eef5fb; }
					.mpwem-pm-acc.is-open .mpwem-pm-acc__bar { background: #eef5fb; border-bottom-color: #e2e4e7; }
					.mpwem-pm-acc__title { font-size: 14px; font-weight: 700; color: #1d2327; }
					.mpwem-pm-acc.is-open .mpwem-pm-acc__title { color: #2271b1; }
					.mpwem-pm-acc__arrow { color: #50575e; transition: transform .2s ease; }
					.mpwem-pm-acc.is-open .mpwem-pm-acc__arrow { transform: rotate(180deg); color: #2271b1; }
					.mpwem-pm-acc__panel { padding: 16px 18px; }

					/* WooCommerce Payment Methods manager embedded in the accordion */
					.mpwem-pm-acc--methods .mep-wc-payment-manager { margin-top: 4px; }
					.mpwem-pm-acc--methods .mep-wc-pm-heading { display: none; }
					.mpwem-pm-acc--methods .mep-wc-pm-bar { margin-bottom: 12px; justify-content: flex-end; }

					/* ---- Buttons: modern + consistent across the whole modal ---- */
					#mep-payment-settings-modal .button,
					#mep-payment-settings-modal .button-secondary,
					#mep-payment-settings-modal .button-primary,
					#mep-payment-settings-modal button.mep-gw-configure-btn,
					#mep-payment-settings-modal button.mep-gw-save-btn {
						display: inline-flex; align-items: center; justify-content: center; gap: 6px;
						height: auto; min-height: 34px; margin: 0;
						padding: 7px 16px; border-radius: 8px;
						font-size: 13px; font-weight: 600; line-height: 1.4; text-decoration: none;
						border: 1px solid #d1d5db; background: #fff; color: #374151;
						box-shadow: none; cursor: pointer;
						transition: background .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease, transform .12s ease;
					}
					#mep-payment-settings-modal .button .dashicons,
					#mep-payment-settings-modal .button-secondary .dashicons {
						font-size: 16px; width: 16px; height: 16px; line-height: 1;
					}
					#mep-payment-settings-modal .button:hover,
					#mep-payment-settings-modal .button-secondary:hover,
					#mep-payment-settings-modal button.mep-gw-configure-btn:hover {
						background: #f3f4f6; border-color: #9ca3af; color: #111827; transform: translateY(-1px);
					}
					/* Primary / call-to-action buttons */
					#mep-payment-settings-modal .button-primary,
					#mep-payment-settings-modal button.mep-gw-save-btn {
						background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
						border-color: #135e96; color: #fff;
						box-shadow: 0 2px 6px rgba(34,113,177,0.25);
						text-shadow: none;
					}
					#mep-payment-settings-modal .button-primary:hover,
					#mep-payment-settings-modal button.mep-gw-save-btn:hover {
						background: linear-gradient(135deg, #1f6aa6 0%, #0f5388 100%);
						border-color: #0f5388; color: #fff;
						box-shadow: 0 4px 12px rgba(34,113,177,0.35); transform: translateY(-1px);
					}
					#mep-payment-settings-modal .button:focus,
					#mep-payment-settings-modal .button-secondary:focus,
					#mep-payment-settings-modal .button-primary:focus,
					#mep-payment-settings-modal button.mep-gw-configure-btn:focus,
					#mep-payment-settings-modal button.mep-gw-save-btn:focus {
						outline: none; box-shadow: 0 0 0 3px rgba(34,113,177,0.18);
					}
					#mep-payment-settings-modal .button:disabled,
					#mep-payment-settings-modal .button-primary:disabled,
					#mep-payment-settings-modal button.mep-gw-save-btn:disabled {
						opacity: .6; cursor: not-allowed; transform: none; box-shadow: none;
					}
					/* "Open in WooCommerce" small link-button inside the methods accordion */
					#mep-payment-settings-modal .mep-wc-pm-wc-link.button-small {
						min-height: 0; padding: 5px 12px; font-size: 12px; font-weight: 600;
					}

					/* Footer */
					.mpwem-pm-footer {
						display: flex; align-items: center; justify-content: flex-end; gap: 10px;
						padding: 15px 24px; border-top: 1px solid #e2e4e7; background: #fff;
					}
					.mpwem-pm-save-status { display: inline-flex; align-items: center; gap: 4px; margin-right: auto; font-size: 13.5px; color: #0f5132; }

					@media (max-width: 600px) {
						.mpwem-pm-confirm-row { flex-direction: column; }
						.mpwem-pm-confirm-row__control { width: 100%; }
						.mpwem-pm-control { max-width: 100%; }
					}
				</style>
				<?php
			}
			public function setting_head( $event_id, $event_infos ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$is_custom_event_edit = is_admin()
					&& isset( $_GET['page'] )
					&& sanitize_key( wp_unslash( $_GET['page'] ) ) === 'mpwem_event_edit';
				?>
                <div class="_layout_default_xs_mp_zero mpwem-ticket-settings-head" style="border-radius: var(--mpwem-radius);">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Ticket & Pricing Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Your Ticket & Pricing Settings Here', 'mage-eventpress' ); ?></span>
                    </div>
					<?php
						do_action( 'mep_event_tab_before_ticket_pricing', $event_id );
						if ( ! $is_custom_event_edit ) {
							$this->event_view_shortcode( $event_id );
						}
						do_action( 'mep_add_category_display', $event_id );
						$this->registration_on_off( $event_id, $event_infos );
						if ( ! $is_custom_event_edit ) {
							do_action( 'mpwem_after_registration_on_off', $event_id );
						}
					?>
                </div>
				<?php
			}
			public function ticket_setting( $event_id, $event_infos ) {
				$ticket_infos        = is_array($event_infos) && array_key_exists( 'mep_event_ticket_type', $event_infos ) ? $event_infos['mep_event_ticket_type'] : [];
				$show_advance_column = is_array($event_infos) && array_key_exists( 'mep_show_advance_col_status', $event_infos ) ? $event_infos['mep_show_advance_col_status'] : 'off';
				$active_category     = $show_advance_column == 'on' ? 'mActive' : '';
				$ticket_infos          = array_key_exists( 'mep_event_ticket_type', $event_infos ) ? $event_infos['mep_event_ticket_type'] : [];
					$early_bird_status     = array_key_exists( 'mep_enable_early_bird_status', $event_infos ) ? $event_infos['mep_enable_early_bird_status'] : 'off';
					$advanced_col_status   = array_key_exists( 'mep_show_advanced_column', $event_infos ) ? $event_infos['mep_show_advanced_column'] : 'off';
					$global_qty_status     = array_key_exists( 'enable_global_qty', $event_infos ) ? $event_infos['enable_global_qty'] : 'off';
					$active_category       = $early_bird_status == 'on' ? 'mActive' : 'mpwem-ticket-col-hidden';
					$capacity_col_status   = $global_qty_status == 'on' ? 'mpwem-ticket-col-hidden' : '';
					$event_label         = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				//echo '<pre>';print_r($ticket_infos);echo '</pre>';
				?>
                <div class="_mt"></div>
                <div class="_layout_default_xs_mp_zero mpwem-ticket-editor-section">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Ticket Type Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Ticket Type', 'mage-eventpress' ); ?></span>
                    </div>
					<?php
						do_action( 'mpwem_before_ticket_type', $event_id );
						$this->show_advance_column( $event_id, $event_infos );
					?>
                    <div class="_padding_bt mpwem_settings_area">
                        <div class="_ov_auto mpwem-ticket-table-wrap">
                            <table class="mpwem_ticket_table mpwem-ticket-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Ticket Type', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></th>
	                                        <th class="mpwem-ticket-card__capacity <?php echo esc_attr( $capacity_col_status ); ?>"><?php esc_html_e( 'Capacity', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Qty Box', 'mage-eventpress' ); ?></th>
                                        <th class="mpwem-ticket-card__mode mpwem-ticket-col-hidden" data-hybrid-col="1"><?php esc_html_e( 'Ticket Mode', 'mage-eventpress' ); ?></th>
                                        <th class="<?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column"><?php esc_html_e( 'Default Qty', 'mage-eventpress' ); ?></th>
                                        <th class="<?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column"><?php esc_html_e( 'Reserve Qty', 'mage-eventpress' ); ?></th>
                                        <?php do_action( 'mpwem_add_extra_column', $event_id ); ?>
                                        <th class="mpwem-ticket-table__sale-period <?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_enable_early_bird_status"><?php esc_html_e( 'Sale Period', 'mage-eventpress' ); ?></th>
                                        <th class="mpwem-ticket-table__actions"><?php esc_html_e( 'Actions', 'mage-eventpress' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="mpwem-ticket-cards-container mpwem_sortable_area mpwem_item_insert">
                                    <?php
                                        if ( is_array($ticket_infos) && sizeof( $ticket_infos ) > 0 ) {
                                            foreach ( $ticket_infos as $ticket_info ) {
	                                                $this->ticket_info( $event_id, $active_category, $advanced_col_status, $ticket_info, $global_qty_status );
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mpwem-ticket-footer">
                            <?php MPWEM_Custom_Layout::add_new_button( __( 'Add New Ticket Type', 'mage-eventpress' ), 'mpwem_add_item', 'mpwem-add-ticket-btn', 'fas fa-plus' ); ?>
                        </div>

                        <div class="mpwem_hidden_content">
                            <table>
                                <tbody class="mpwem_hidden_item">
                                    <?php $this->ticket_info( $event_id, $active_category, $advanced_col_status, [], $global_qty_status ); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
				<?php
			}
				public function ticket_info( $event_id, $active_category, $advanced_col_status, $ticket_info = [], $global_qty_status = 'off' ) {
				$qty_t_type           = is_array($ticket_info) && array_key_exists( 'option_qty_t_type', $ticket_info ) ? $ticket_info['option_qty_t_type'] : 'inputbox';
				$option_details       = is_array($ticket_info) && array_key_exists( 'option_details_t', $ticket_info ) ? $ticket_info['option_details_t'] : '';
				$option_name          = is_array($ticket_info) && array_key_exists( 'option_name_t', $ticket_info ) ? $ticket_info['option_name_t'] : '';
				$option_name_text     = preg_replace( "/[{}()<>+ ]/", '_', $option_name ) . '_' . $event_id;
				$option_price         = is_array($ticket_info) && array_key_exists( 'option_price_t', $ticket_info ) ? $ticket_info['option_price_t'] : '';
				$option_qty           = is_array($ticket_info) && array_key_exists( 'option_qty_t', $ticket_info ) ? $ticket_info['option_qty_t'] : 0;
				$option_default_qty   = is_array($ticket_info) && array_key_exists( 'option_default_qty_t', $ticket_info ) ? $ticket_info['option_default_qty_t'] : 0;
				$option_rsv_qty       = is_array($ticket_info) && array_key_exists( 'option_rsv_t', $ticket_info ) ? $ticket_info['option_rsv_t'] : 0;
				$sale_end             = is_array($ticket_info) && array_key_exists( 'option_sale_end_date_t', $ticket_info ) ? $ticket_info['option_sale_end_date_t'] : '';
				$ticket_mode          = is_array($ticket_info) && array_key_exists( 'option_ticket_mode_t', $ticket_info ) ? $ticket_info['option_ticket_mode_t'] : 'inperson';
				$option_ticket_enable = is_array($ticket_info) && array_key_exists( 'option_ticket_enable', $ticket_info ) && $ticket_info['option_ticket_enable'] ? $ticket_info['option_ticket_enable'] : 'yes';
				$checked              = $option_ticket_enable == 'yes' ? 'checked' : '';
				$ticket_sold          = 0;
				if ( $option_name ) {
					$filter_args['post_id']        = $event_id;
					$filter_args['ea_ticket_type'] = $option_name;
					$ticket_sold                   = MPWEM_Query::attendee_query( $filter_args )->post_count;
				}
				?>
                <tr class="mpwem-ticket-card mpwem-ticket-row mpwem_remove_area data_required <?php echo esc_attr( $option_ticket_enable !== 'yes'  && $ticket_sold>0? 'disable_row' : '' ); ?>">
                    <td class="mpwem-ticket-card__group mpwem-ticket-card__identity">
                        <label class="mpwem-card-label"><?php esc_html_e( 'Ticket Type', 'mage-eventpress' ); ?></label>
                        <div class="mpwem-ticket-card__field">
                            <input type="hidden" name="hidden_option_name_t[]" value="<?php echo esc_attr( $option_name_text ); ?>"/>
                            <?php if ( $ticket_sold > 0 ) { ?>
                                <input type="hidden" name="option_name_t[]" value="<?php echo esc_attr( $option_name ); ?>"/>
                                <div class="mpwem-ticket-card__locked-name"><?php echo esc_html( $option_name ); ?></div>
                            <?php } else { ?>
                                <input data-required="" type="text" class="mpwem-card-input mpwem-card-input--large name_validation" name="option_name_t[]" placeholder="Ticket Name (Ex: Adult)" value="<?php echo esc_attr( $option_name ); ?>"/>
                            <?php } ?>
                        </div>
                        <div class="mpwem-ticket-card__field mpwem-ticket-card__description <?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column">
                            <input type="text" class="mpwem-card-input" name="option_details_t[]" placeholder="Add short description" value="<?php echo esc_attr( $option_details ); ?>"/>
                        </div>
                    </td>

                    <td class="mpwem-ticket-card__group mpwem-ticket-card__price">
                        <label class="mpwem-card-label"><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></label>
                        <div class="mpwem-card-input-wrapper mpwem-card-input-wrapper--currency">
                            <input type="number" size="4" pattern="[0-9]*" step="0.001" class="mpwem-card-input" name="option_price_t[]" placeholder="0.00" value="<?php echo esc_attr( $option_price ); ?>"/>
                        </div>
                    </td>

	                    <td class="mpwem-ticket-card__group mpwem-ticket-card__capacity <?php echo esc_attr( $global_qty_status == 'on' ? 'mpwem-ticket-col-hidden' : '' ); ?>">
                        <label class="mpwem-card-label"><?php esc_html_e( 'Capacity', 'mage-eventpress' ); ?></label>
                        <input type="number" size="4" pattern="[0-9]*" step="1" class="mpwem-card-input" name="option_qty_t[]" placeholder="100" value="<?php echo esc_attr( $option_qty ) ?>"/>
                    </td>

					<td class="mpwem-ticket-card__group mpwem-ticket-card__qty-box">
						<label class="mpwem-card-label"><?php esc_html_e( 'Qty Box', 'mage-eventpress' ); ?></label>
						<select class="mpwem-card-input" name="option_qty_t_type[]">
							<option value="inputbox" <?php selected( $qty_t_type, 'inputbox' ); ?>><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
							<option value="dropdown" <?php selected( $qty_t_type, 'dropdown' ); ?>><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
						</select>
					</td>

					<td class="mpwem-ticket-card__group mpwem-ticket-card__mode mpwem-ticket-col-hidden" data-hybrid-col="1">
						<label class="mpwem-card-label"><?php esc_html_e( 'Ticket Mode', 'mage-eventpress' ); ?></label>
						<select class="mpwem-card-input mpwem-ticket-mode-select" name="option_ticket_mode_t[]">
							<option value="inperson" <?php selected( $ticket_mode, 'inperson' ); ?>><?php esc_html_e( 'In Person (Physical)', 'mage-eventpress' ); ?></option>
							<option value="online" <?php selected( $ticket_mode, 'online' ); ?>><?php esc_html_e( 'Online Event', 'mage-eventpress' ); ?></option>
						</select>
					</td>

					<td class="mpwem-ticket-card__group mpwem-ticket-card__default-qty <?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column">
						<label class="mpwem-card-label"><?php esc_html_e( 'Default Qty', 'mage-eventpress' ); ?></label>
						<input type="number" size="2" pattern="[0-9]*" step="1" class="mpwem-card-input mpwem-card-input--small" name="option_default_qty_t[]" placeholder="1" value="<?php echo esc_attr( $option_default_qty ); ?>"/>
					</td>

					<td class="mpwem-ticket-card__group mpwem-ticket-card__advance-qty <?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column">
						<label class="mpwem-card-label"><?php esc_html_e( 'Reserve Qty', 'mage-eventpress' ); ?></label>
						<input type="number" class="mpwem-card-input mpwem-card-input--small" name="option_rsv_t[]" placeholder="0" value="<?php echo esc_attr( $option_rsv_qty ); ?>"/>
					</td>

                    <?php do_action( 'mpwem_add_extra_input_box', $event_id, $ticket_info ); ?>

                    <td class="mpwem-ticket-card__group mpwem-ticket-card__sale-period <?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_enable_early_bird_status">
                        <label class="mpwem-card-label"><?php esc_html_e( 'Sale Period', 'mage-eventpress' ); ?></label>
                        <div class="mpwem-card-row mpwem-card-row--date">
                            <?php do_action( 'mpwem_add_sale_period_input_box', $event_id, $ticket_info ); ?>
                            <div class="mpwem-card-date-wrapper mpwem-card-date-wrapper--end">
                                <div style="font-size: 11px; color: #646970; margin-bottom: 0; font-weight: 600; text-transform: uppercase;text-align:left"><?php esc_html_e('End Date', 'mage-eventpress'); ?></div>
                                <div class="mpwem-card-date-field">
                                <?php MPWEM_Date_Settings::date_item( 'option_sale_end_date[]', $sale_end ); ?>
                                    <div class="mpwem-card-time-field">
                                        <input type="time" value="<?php echo esc_attr( strlen(trim((string)$sale_end)) > 10 ? date( 'H:i', strtotime( $sale_end ) ) : '' ); ?>" name="option_sale_end_time[]" class="formControl"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="mpwem-ticket-card__actions">
                        <div class="mpwem-ticket-card__action-btn mpwem_sortable_button" title="<?php esc_attr_e( 'Drag to reorder', 'mage-eventpress' ); ?>">
                            <span class="fas fa-ellipsis-v"></span>
                            <span class="fas fa-ellipsis-v"></span>
                        </div>
                        <button class="mpwem-ticket-card__action-btn mpwem-ticket-card__action-btn--danger mpwem_item_remove" type="button" title="<?php esc_attr_e( 'Delete', 'mage-eventpress' ); ?>">
                            <span class="fas fa-trash-alt"></span>
                        </button>
                        <?php if($ticket_sold > 0) { 
                            MPWEM_Custom_Layout::show_hide_button( 'option_ticket_enable[]', $option_ticket_enable );
                        } else { ?>
                            <input type="hidden" name="option_ticket_enable[]" value="yes">
                        <?php } ?>
                    </td>
                </tr>
				<?php
			}
			public function ex_service_setting( $event_id ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$ex_infos    = MPWEM_Global_Function::get_post_info( $event_id, 'mep_events_extra_prices', [] );
				?>
                <div class="_mt"></div>
                <div class="_layout_default_xs_mp_zero mpwem-extra-service-section">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Extra Service Area', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Extra Service Here. Extra Service as Product that you can sell and it is not included on event package', 'mage-eventpress' ); ?></span>
                    </div>
					<?php do_action( 'mpwem_before_ex_service', $event_id ); ?>
                    <div class="_padding_bt mpwem_settings_area">
                        
                        <div class="mpwem-ticket-cards-container mpwem_sortable_area mpwem_item_insert">
                            <?php
                                if ( is_array($ex_infos) && sizeof( $ex_infos ) > 0 ) {
                                    foreach ( $ex_infos as $ticket_info ) {
                                        $this->ex_info( $ticket_info );
                                    }
                                }
                            ?>
                        </div>

                        <div class="mpwem-ticket-footer">
                            <?php MPWEM_Custom_Layout::add_new_button( __( 'Add Extra Price', 'mage-eventpress' ), 'mpwem_add_item', 'mpwem-add-ticket-btn', 'fas fa-plus' ); ?>
                        </div>

                        <div class="mpwem_hidden_content">
                            <div class="mpwem_hidden_item">
                                <?php $this->ex_info(); ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function ex_info( $ticket_info = [] ) {
				$option_name  = is_array($ticket_info) && array_key_exists( 'option_name', $ticket_info ) ? $ticket_info['option_name'] : '';
				$option_price = is_array($ticket_info) && array_key_exists( 'option_price', $ticket_info ) ? $ticket_info['option_price'] : '';
				$option_qty   = is_array($ticket_info) && array_key_exists( 'option_qty', $ticket_info ) ? $ticket_info['option_qty'] : 0;
				$qty_t_type   = is_array($ticket_info) && array_key_exists( 'option_qty_type', $ticket_info ) ? $ticket_info['option_qty_type'] : 'inputbox';
				?>
                <div class="mpwem-ticket-card mpwem_remove_area data_required">
                    <div class="mpwem-ticket-card__main mpwem-ticket-card__main--extra-service">
                        <!-- Identity Group -->
                        <div class="mpwem-ticket-card__group mpwem-ticket-card__identity">
                            <div class="mpwem-ticket-card__field">
                                <label class="mpwem-card-label"><?php esc_html_e( 'Title', 'mage-eventpress' ); ?></label>
                                <input type="text" class="mpwem-card-input mpwem-card-input--large" name="option_name[]" placeholder="Service Name" value="<?php echo esc_attr( $option_name ); ?>"/>
                            </div>
                        </div>

                        <!-- Price Group -->
                        <div class="mpwem-ticket-card__group mpwem-ticket-card__price">
                            <label class="mpwem-card-label"><?php esc_html_e( 'PRICE', 'mage-eventpress' ); ?></label>
                            <div class="mpwem-card-input-wrapper mpwem-card-input-wrapper--currency">
                                <input type="number" class="mpwem-card-input" name="option_price[]" placeholder="0.00" value="<?php echo esc_attr( $option_price ); ?>"/>
                            </div>
                        </div>

                        <!-- Capacity Group -->
                        <div class="mpwem-ticket-card__group mpwem-ticket-card__capacity">
                            <label class="mpwem-card-label"><?php esc_html_e( 'AVAILABLE QTY', 'mage-eventpress' ); ?></label>
                            <input type="number" class="mpwem-card-input" name="option_qty[]" placeholder="100" value="<?php echo esc_attr( $option_qty ); ?>"/>
                        </div>

                        <!-- Qty Box Group -->
                        <div class="mpwem-ticket-card__group mpwem-ticket-card__qty-box">
                            <label class="mpwem-card-label"><?php esc_html_e( 'QTY BOX', 'mage-eventpress' ); ?></label>
                            <select class="mpwem-card-input" name="option_qty_type[]">
                                <option value="inputbox" <?php echo esc_attr( $qty_t_type == 'inputbox' ? 'Selected' : '' ); ?>><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
                                <option value="dropdown" <?php echo esc_attr( $qty_t_type == 'dropdown' ? 'Selected' : '' ); ?>><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
                            </select>
                        </div>

                        <!-- Spacer for Sale Period (not used in Extra Service usually) -->
                        <!-- <div class="mpwem-ticket-card__group"></div> -->

                        <!-- Action Group -->
                        <div class="mpwem-ticket-card__actions">
                            <div class="mpwem-ticket-card__action-btn mpwem_sortable_button" title="<?php esc_attr_e( 'Drag to reorder', 'mage-eventpress' ); ?>">
                                <span class="fas fa-ellipsis-v"></span>
                                <span class="fas fa-ellipsis-v"></span>
                            </div>
                            <button class="mpwem-ticket-card__action-btn mpwem-ticket-card__action-btn--danger mpwem_item_remove" type="button" title="<?php esc_attr_e( 'Delete', 'mage-eventpress' ); ?>">
                                <span class="fas fa-trash-alt"></span>
                            </button>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function event_view_shortcode( $post_id ) {
				self::render_shortcode_help( $post_id );
			}
			public static function render_shortcode_help( $post_id, $is_sidebar = false ) {
				$wrapper_classes = 'mpwem-shortcode-help';
				if ( $is_sidebar ) {
					$wrapper_classes .= ' mpwem-shortcode-help--sidebar';
				}
				?>
                <div class="<?php echo esc_attr( $wrapper_classes ); ?>">
                    <div class="mpwem-shortcode-help__row">
                        <span class="mpwem-shortcode-help__title"><?php esc_html_e( 'Add To Cart Form Shortcode', 'mage-eventpress' ); ?></span>
                        <code class="mpwem-shortcode-help__code">[event-add-cart-section event="<?php echo esc_html( $post_id ); ?>"]</code>
                    </div>
                    <p class="mpwem-shortcode-help__description"><?php esc_html_e( 'If you want to display the ticket type list with an add-to-cart button on any post or page of your website, simply copy the shortcode and paste it where desired.', 'mage-eventpress' ); ?></p>
                </div>
				<?php
			}
			public function registration_on_off( $event_id, $event_infos ) {
				$reg_status = is_array($event_infos) && array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
                $reg_status_msg_status = is_array($event_infos) && array_key_exists( 'mep_reg_status_show_msg', $event_infos ) ? $event_infos['mep_reg_status_show_msg'] : '';
                $reg_status_msg_txt = is_array($event_infos) && array_key_exists( 'mep_reg_status_show_msg_txt', $event_infos ) ? $event_infos['mep_reg_status_show_msg_txt'] : '';
				$is_custom_event_edit = is_admin()
					&& isset( $_GET['page'] )
					&& sanitize_key( wp_unslash( $_GET['page'] ) ) === 'mpwem_event_edit';

				$checked    = $reg_status == 'on' ? 'checked' : '';
                $reg_msg_checked    = $reg_status_msg_status == 'on' ? 'checked' : '';

				if ( ! $is_custom_event_edit ) {
					?>
					<div class="mpwem-ticket-registration-block">
						<div class=" _justify_between_align_center_wrap">
							<label><span class="_mr"><?php esc_html_e( 'Registration Off/On', 'mage-eventpress' ); ?></span></label>
							<?php MPWEM_Custom_Layout::switch_button( 'mep_reg_status', $checked ); ?>
						</div>
						<span class="label-text"><?php esc_html_e( 'Registration Off/On', 'mage-eventpress' ); ?></span>
					</div>
					<?php
				}
				?>
                <div class="_padding_bt reg_close_msg_dash mpwem-ticket-registration-message">
                    <div class=" _justify_between_align_center_wrap">
                        <label><span class="_mr"><?php esc_html_e( 'Show Registration Off Message in Event details Page?', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_reg_status_show_msg', $reg_msg_checked ); ?>
                        <div class="mep_reg_status_show_msg_txt_sec">
                            <textarea name="mep_reg_status_show_msg_txt" id="mep_reg_status_show_msg_txt" class="formControl" placeholder="<?php _e( 'Registration for this event is currently closed.', 'mage-eventpress' ); ?>"><?php echo esc_html( $reg_status_msg_txt ); ?></textarea>
                        </div>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'Show Message Off/On', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}
			public function show_advance_column( $event_id, $event_infos ) {
				$early_bird_status   = array_key_exists( 'mep_enable_early_bird_status', $event_infos ) ? $event_infos['mep_enable_early_bird_status'] : 'off';
				$global_qty_status   = array_key_exists( 'enable_global_qty', $event_infos ) ? $event_infos['enable_global_qty'] : 'off';
				$advanced_col_status = array_key_exists( 'mep_show_advanced_column', $event_infos ) ? $event_infos['mep_show_advanced_column'] : 'off';
				$global_qty_type     = array_key_exists( 'mep_gq_type', $event_infos ) ? $event_infos['mep_gq_type'] : 'global';
				$date_schedule_type  = array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
				$total_qty           = array_key_exists( 'mep_gq_total_seat', $event_infos ) ? $event_infos['mep_gq_total_seat'] : 0;
				$reserve_qty         = array_key_exists( 'mep_gq_total_resv_seat', $event_infos ) ? $event_infos['mep_gq_total_resv_seat'] : 0;

				$early_bird_checked = $early_bird_status == 'on' ? 'checked' : '';
				$global_qty_checked = $global_qty_status == 'on' ? 'checked' : '';
				$advanced_checked    = $advanced_col_status == 'on' ? 'checked' : '';
				?>
                <div class="mpwem-ticket-action-bar">
                    <div class="mpwem-ticket-action-bar__item">
                        <label><?php esc_html_e( 'ENABLE GLOBAL QTY', 'mage-eventpress' ); ?></label>
                        <?php MPWEM_Custom_Layout::switch_button( 'enable_global_qty', $global_qty_checked ); ?>
                    </div>
                    <div class="mpwem-ticket-action-bar__divider"></div>
                    <div class="mpwem-ticket-action-bar__item">
                        <label><?php esc_html_e( 'EARLY BIRD', 'mage-eventpress' ); ?></label>
                        <?php MPWEM_Custom_Layout::switch_button( 'mep_enable_early_bird_status', $early_bird_checked ); ?>
                    </div>
                    <div class="mpwem-ticket-action-bar__divider"></div>
                    <div class="mpwem-ticket-action-bar__item">
                        <label><?php esc_html_e( 'SHOW ADVANCED COLUMN', 'mage-eventpress' ); ?></label>
                        <?php MPWEM_Custom_Layout::switch_button( 'mep_show_advanced_column', $advanced_checked ); ?>
                    </div>
                </div>

                <!-- Global Settings Card -->
                <div class="mpwem-ticket-global-card <?php echo esc_attr( $global_qty_status == 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#enable_global_qty">
                    <div class="mpwem-ticket-global-card__content">
                        <div class="mpwem-ticket-card__group">
                            <label class="mpwem-card-label"><?php esc_html_e( 'GLOBAL QUANTITY TYPE?', 'mage-eventpress' ); ?></label>
                            <select class="mpwem-card-input" name="mep_gq_type">
                                <option value="date_wise" <?php selected( $global_qty_type, 'date_wise' ); ?>><?php esc_html_e( 'Particular Date Wise', 'mage-eventpress' ); ?></option>
                                <option value="global" <?php selected( $global_qty_type, 'global' ); ?>><?php esc_html_e( 'Full Event Base', 'mage-eventpress' ); ?></option>
                            </select>
                            <p class="mpwem-global-qty-warning <?php echo esc_attr( ( $global_qty_status === 'on' && $global_qty_type === 'date_wise' && $date_schedule_type === 'yes' ) ? 'is-visible' : '' ); ?>">
                                <span class="mpwem-global-qty-warning__text">
								    <?php esc_html_e( 'Please set the Global Qty in Date & Time Steps -> Particular Date Wise table.', 'mage-eventpress' ); ?>
                                </span>
                                <button type="button" class="button button-secondary mpwem-global-qty-warning__action" data-mpwem-open-particular-date-modal>
									<?php esc_html_e( 'Open Particular Date Table', 'mage-eventpress' ); ?>
                                </button>
                            </p>
                        </div>
                        <div class="mpwem-ticket-card__group <?php echo esc_attr( $global_qty_type === 'date_wise' ? 'mpwem-ticket-col-hidden' : '' ); ?>">
                            <label class="mpwem-card-label">
								<?php esc_html_e( 'TOTAL QTY', 'mage-eventpress' ); ?>
                                <span class="mpwem-info-tip mpwem-info-tip--mini" title="<?php echo esc_attr__( 'Enter The Total Seat of this event.', 'mage-eventpress' ); ?>">i</span>
                            </label>
                            <input type="number" class="mpwem-card-input" name="mep_gq_total_seat" placeholder="0" value="<?php echo esc_attr( $total_qty ); ?>"/>
                        </div>
                        <div class="mpwem-ticket-card__group <?php echo esc_attr( $global_qty_type === 'date_wise' ? 'mpwem-ticket-col-hidden' : '' ); ?>">
                            <label class="mpwem-card-label">
								<?php esc_html_e( 'RESERVE QTY', 'mage-eventpress' ); ?>
                                <span class="mpwem-info-tip mpwem-info-tip--mini" title="<?php echo esc_attr__( 'Enter The Total Reserve Seat Qty of this event.', 'mage-eventpress' ); ?>">i</span>
                            </label>
                            <input type="number" class="mpwem-card-input" name="mep_gq_total_resv_seat" placeholder="0" value="<?php echo esc_attr( $reserve_qty ); ?>"/>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function rsvp_setting( $event_id, $event_infos ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$name_label  = is_array($event_infos) && array_key_exists( 'mep_rsvp_name_label', $event_infos ) ? $event_infos['mep_rsvp_name_label'] : '';
				$email_label = is_array($event_infos) && array_key_exists( 'mep_rsvp_email_label', $event_infos ) ? $event_infos['mep_rsvp_email_label'] : '';
				$phone_label = is_array($event_infos) && array_key_exists( 'mep_rsvp_phone_label', $event_infos ) ? $event_infos['mep_rsvp_phone_label'] : '';
				$qty_label   = is_array($event_infos) && array_key_exists( 'mep_rsvp_qty_label', $event_infos ) ? $event_infos['mep_rsvp_qty_label'] : '';
				?>
                <style>
                .mpwem-rsvp-card {
                    background: #fff;
                    border: 1px solid #e2e8f0;
                    border-radius: var(--mpwem-radius, 12px);
                    overflow: hidden;
                    margin-top: 16px;
                    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
                }
                .mpwem-rsvp-card__header {
                    padding: 18px 22px;
                    background: var(--mpwem-card-header-bg, #f8fafc);
                    border-bottom: 1px solid #e2e8f0;
                    display: flex;
                    align-items: center;
                    gap: 14px;
                }
                .mpwem-rsvp-card__header-icon {
                    width: 38px;
                    height: 38px;
                    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    color: #fff;
                }
                .mpwem-rsvp-card__header-icon .dashicons {
                    font-size: 18px;
                    width: 18px;
                    height: 18px;
                }
                .mpwem-rsvp-card__header-title {
                    font-size: 15px !important;
                    font-weight: 700 !important;
                    color: #1e293b !important;
                    margin: 0 0 3px !important;
                    line-height: 1.3 !important;
                    display: block !important;
                }
                .mpwem-rsvp-card__header-sub {
                    font-size: 12px !important;
                    color: #64748b !important;
                    margin: 0 !important;
                    display: block !important;
                }
                .mpwem-rsvp-card__body {
                    padding: 22px;
                }
                .mpwem-rsvp-section-title {
                    font-size: 11px;
                    font-weight: 700;
                    color: #94a3b8;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                    margin: 0 0 16px;
                }
                .mpwem-rsvp-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 16px;
                }
                @media (max-width: 900px) {
                    .mpwem-rsvp-grid { grid-template-columns: 1fr; }
                }
                .mpwem-rsvp-field {
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                }
                .mpwem-rsvp-label {
                    display: block;
                    font-weight: 600;
                    font-size: 12px;
                    color: #374151;
                    letter-spacing: 0.01em;
                    margin: 0;
                }
                .mpwem-rsvp-input {
                    width: 100%;
                    padding: 9px 13px !important;
                    border: 1.5px solid #d1d5db !important;
                    border-radius: 8px !important;
                    font-size: 13px;
                    color: #1e293b;
                    background: #f9fafb;
                    box-sizing: border-box;
                    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
                    height: auto;
                    line-height: 1.5;
                    box-shadow: none;
                }
                .mpwem-rsvp-input:focus {
                    border-color: var(--mpwem-primary, #2563eb);
                    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
                    outline: none;
                    background: #fff;
                }
                .mpwem-rsvp-input::placeholder {
                    color: #9ca3af;
                }
                .mpwem-rsvp-note {
                    margin: 18px 0 0;
                    padding: 10px 14px;
                    background: #f0f9ff;
                    border: 1px solid #bae6fd;
                    border-radius: 8px;
                    font-size: 12px;
                    color: #0369a1;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .mpwem-rsvp-note .dashicons {
                    font-size: 15px;
                    width: 15px;
                    height: 15px;
                    flex-shrink: 0;
                }
                </style>

                <div class="mpwem-rsvp-settings-section">
                    <div class="mpwem-rsvp-card">
                        <div class="mpwem-rsvp-card__header">
                            <div class="mpwem-rsvp-card__header-icon">
                                <span class="dashicons dashicons-groups"></span>
                            </div>
                            <div>
                                <h4 class="mpwem-rsvp-card__header-title"><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'RSVP Settings', 'mage-eventpress' ); ?></h4>
                                <p class="mpwem-rsvp-card__header-sub"><?php esc_html_e( 'Configure RSVP Registration Field Labels', 'mage-eventpress' ); ?></p>
                            </div>
                        </div>
                        <div class="mpwem-rsvp-card__body">
                            <p class="mpwem-rsvp-section-title"><?php esc_html_e( 'Field Labels', 'mage-eventpress' ); ?></p>
                            <div class="mpwem-rsvp-grid">
                                <div class="mpwem-rsvp-field">
                                    <label class="mpwem-rsvp-label"><?php esc_html_e( 'Full Name Label', 'mage-eventpress' ); ?></label>
                                    <input type="text" class="mpwem-rsvp-input" name="mep_rsvp_name_label" placeholder="<?php esc_attr_e( 'Full Name', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $name_label ); ?>"/>
                                </div>
                                <div class="mpwem-rsvp-field">
                                    <label class="mpwem-rsvp-label"><?php esc_html_e( 'Email Address Label', 'mage-eventpress' ); ?></label>
                                    <input type="text" class="mpwem-rsvp-input" name="mep_rsvp_email_label" placeholder="<?php esc_attr_e( 'Email Address', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $email_label ); ?>"/>
                                </div>
                                <div class="mpwem-rsvp-field">
                                    <label class="mpwem-rsvp-label"><?php esc_html_e( 'Phone Number Label', 'mage-eventpress' ); ?></label>
                                    <input type="text" class="mpwem-rsvp-input" name="mep_rsvp_phone_label" placeholder="<?php esc_attr_e( 'Phone Number', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $phone_label ); ?>"/>
                                </div>
                                <div class="mpwem-rsvp-field">
                                    <label class="mpwem-rsvp-label"><?php esc_html_e( 'Number of Seats Label', 'mage-eventpress' ); ?></label>
                                    <input type="text" class="mpwem-rsvp-input" name="mep_rsvp_qty_label" placeholder="<?php esc_attr_e( 'Number of Seats', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $qty_label ); ?>"/>
                                </div>
                            </div>
                            <p class="mpwem-rsvp-note">
                                <span class="dashicons dashicons-info-outline"></span>
                                <?php esc_html_e( 'Leave blank to use the default labels.', 'mage-eventpress' ); ?>
                            </p>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function mep_event_pro_purchase_notice() {
				?>
                <section class="bg-light" style="margin-top: 20px;">
                    <h2><?php esc_html_e( 'Documentation Links', 'mage-eventpress' ) ?></h2>
                    <span><?php esc_html_e( 'Get Documentation', 'mage-eventpress' ) ?></span>
                </section>
                <section>
					<?php if ( ! mep_check_plugin_installed( 'woocommerce-event-manager-addon-form-builder/addon-builder.php' ) ) : ?>
                        <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Get Individual Attendee  Information, PDF Ticketing and Email Function with <a href='https://mage-people.com/product/mage-woo-event-booking-manager-pro/' target='_blank'>Event Manager Pro</a>", 'mage-eventpress' ); ?></p>
					<?php endif;
						if ( ! mep_check_plugin_installed( 'woocommerce-event-manager-addon-membership-price/membership-price.php' ) ): ?>
                            <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Special Price Option for each user type or membership get <a href='https://mage-people.com/product/membership-pricing-for-event-manager-plugin' target='_blank'>Membership Pricing Addon</a>", 'mage-eventpress' ); ?></p>
						<?php endif;
						if ( ! mep_check_plugin_installed( 'woocommerce-event-manager-min-max-quantity-addon/mep_min_max_qty.php' ) ): ?>
                            <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Set maximum/minimum qty buying option with <a href='https://mage-people.com/product/event-max-min-quantity-limiting-addon-for-woocommerce-event-manager' target='_blank'>Max/Min Qty Addon</a>", 'mage-eventpress' ); ?></p>
						<?php endif; ?>
                    <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Read Documentation <a href='https://docs.mage-people.com/woocommerce-event-manager/' target='_blank'>Read Documentation</a>", 'mage-eventpress' ); ?></p>
                </section>
				<?php
			}
		}
		new MPWEM_Ticket_Price_Settings();
	}
