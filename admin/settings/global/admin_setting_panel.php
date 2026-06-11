<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	/**
	 * MagePeople Settings API
	 * @version 1.0
	 *
	 */
	if ( ! class_exists( 'MAGE_Events_Setting_Controls' ) ) :
		class MAGE_Events_Setting_Controls {
			private $settings_api;

			function __construct() {
				$this->settings_api = new MAGE_Setting_API;
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_footer', array( $this, 'payment_tabs_script' ) );
				add_action( 'wp_ajax_mep_install_activate_wc', array( $this, 'ajax_install_activate_wc' ) );
				add_action( 'wp_ajax_mep_save_gateway_settings', array( $this, 'ajax_save_gateway_settings' ) );
				// Inject WooCommerce warning + modal in footer so it's outside hidden group divs
				add_action( 'admin_footer', array( $this, 'render_wc_warning_banner' ) );
				// Inject PayPal + Stripe config modals in footer
				add_action( 'admin_footer', array( $this, 'render_gateway_modals' ) );
				add_action( 'wp_ajax_mep_save_payment_settings_modal', array( $this, 'ajax_save_payment_settings_modal' ) );
			}

			function render_wc_warning_banner() {
			// Only output on our settings page
			$screen = get_current_screen();
			if ( ! $screen || $screen->id !== 'mep_events_page_mep_event_settings_page' ) {
				return;
			}

			// If WooCommerce is active, we don't need the warning banner
			if ( class_exists( 'WooCommerce' ) ) {
				return;
			}

			$btn_label = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' )
				? esc_html__( 'Activate WooCommerce', 'mage-eventpress' )
				: esc_html__( 'Install WooCommerce', 'mage-eventpress' );

			$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
			$modal_desc   = $is_installed
				? esc_html__( 'WooCommerce is already installed but not active. Click the button below to activate it right now.', 'mage-eventpress' )
				: esc_html__( 'WooCommerce is required to process payments. We will securely download, install, and activate it for you right now.', 'mage-eventpress' );
			$modal_btn    = $is_installed
				? esc_html__( 'Activate WooCommerce Now', 'mage-eventpress' )
				: esc_html__( 'Install & Activate Now', 'mage-eventpress' );
			?>
			<!-- WooCommerce Warning Banner — injected by JS after .payment-sub-tabs-wrapper -->
			<div id="mep-wc-warning-banner" style="display:none; background:linear-gradient(135deg, rgb(223 169 182) 0%, rgb(255 202 193) 100%); color:#fff; padding:15px; border-radius:6px; margin:10px 0 15px 0; font-weight:500; box-shadow:0 3px 10px rgba(255,65,108,0.3);">
				<div style="display:flex; align-items:flex-start;">
					<span class="dashicons dashicons-warning" style="color:#fff; font-size:24px; width:24px; height:24px; margin-right:15px; margin-top:2px; flex-shrink:0;"></span>
					<div>
						<strong style="display:block; font-size:16px; margin-bottom:4px; color:#4d4d4d; line-height:1.2;"><?php esc_html_e( 'Action Required: WooCommerce is Not Active', 'mage-eventpress' ); ?></strong>
						<p style="margin:0 0 10px 0; font-size:13px; color:#4d4d4d; line-height:1.4;"><?php esc_html_e( 'WooCommerce must be installed and active to process payments and use these features properly.', 'mage-eventpress' ); ?></p>
						<button type="button" class="mep-install-wc-trigger" style="background:#fff; color:#ff416c; border:none; padding:8px 16px; font-size:13px; font-weight:700; border-radius:4px; cursor:pointer; box-shadow:0 2px 5px rgba(0,0,0,0.1); display:inline-flex; align-items:center; gap:6px;">
							<span class="dashicons dashicons-admin-plugins" style="font-size:16px; width:16px; height:16px;"></span>
							<?php echo esc_html( $btn_label ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- WooCommerce Install/Activate Modal -->
			<div id="mep-wc-install-modal" style="display:none; position:fixed; z-index:99999999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
				<div style="background:#fff; border-radius:12px; width:480px; max-width:92%; box-shadow:0 10px 40px rgba(0,0,0,0.35); overflow:hidden;">
					<div style="padding:22px 25px; border-bottom:1px solid #e2e4e7; display:flex; justify-content:space-between; align-items:center; background:#f8f9fa;">
						<h3 style="margin:0; font-size:18px; color:#2c3338;"><?php esc_html_e( 'WooCommerce Setup', 'mage-eventpress' ); ?></h3>
						<button type="button" class="mep-close-modal" style="background:none; border:none; font-size:26px; cursor:pointer; color:#666; line-height:1; padding:0;">&times;</button>
					</div>
					<div style="padding:32px 28px; text-align:center;">
						<span class="dashicons dashicons-cart" style="font-size:72px; width:72px; height:72px; color:#96588a; margin-bottom:18px; display:block; margin-left:auto; margin-right:auto;"></span>
						<p style="font-size:15px; color:#444; margin-bottom:28px; line-height:1.6;"><?php echo esc_html( $modal_desc ); ?></p>
						<div id="mep-wc-install-progress" style="display:none; margin-bottom:22px; font-weight:500; font-size:15px; background:#f0f0f1; padding:14px; border-radius:8px;">
							<span class="spinner is-active" style="float:none; margin:0 10px 0 0;"></span>
							<span id="mep-wc-install-status" style="color:#2271b1;"><?php esc_html_e( 'Working...', 'mage-eventpress' ); ?></span>
						</div>
						<button type="button" id="mep-wc-start-install" style="background:linear-gradient(135deg,#96588a,#6b3d6b); color:#fff; border:none; padding:14px 0; font-size:16px; font-weight:700; border-radius:8px; cursor:pointer; width:100%; box-shadow:0 4px 12px rgba(150,88,138,0.4); transition:all 0.2s;">
							<?php echo esc_html( $modal_btn ); ?>
						</button>
					</div>
				</div>
			</div>

			<script>
			jQuery(document).ready(function($) {
				// Inject the warning banner right after the tab nav wrapper (visibility controlled by tabs)
				var $tabWrapper = $('.payment-sub-tabs-wrapper');
				if ($tabWrapper.length > 0) {
					$('#mep-wc-warning-banner').insertAfter($tabWrapper);
				}
			});
			</script>
			<?php
		}

		function render_gateway_modals() {
			$screen = get_current_screen();
			if ( ! $screen || ! in_array( $screen->id, array( 'mep_events_page_mep_event_settings_page', 'mep_events', 'mep_events_page_mpwem_event_edit' ), true ) ) {
				return;
			}
			$opts        = get_option( 'payment_setting_sec', array() );
			$pp_enabled  = ! empty( $opts['mep_paypal_enable'] ) && $opts['mep_paypal_enable'] === 'on';
			$pp_sandbox  = ! empty( $opts['mep_paypal_sandbox'] ) && $opts['mep_paypal_sandbox'] === 'on';
			$pp_client   = esc_attr( $opts['mep_paypal_client_id'] ?? '' );
			$pp_secret   = esc_attr( $opts['mep_paypal_secret'] ?? '' );
			$st_enabled  = ! empty( $opts['mep_stripe_enable'] ) && $opts['mep_stripe_enable'] === 'on';
			$st_sandbox  = ! empty( $opts['mep_stripe_sandbox'] ) && $opts['mep_stripe_sandbox'] === 'on';
			$st_test_pub = esc_attr( $opts['mep_stripe_test_pub'] ?? '' );
			$st_test_sec = esc_attr( $opts['mep_stripe_test_sec'] ?? '' );
			$st_live_pub = esc_attr( $opts['mep_stripe_live_pub'] ?? '' );
			$st_live_sec = esc_attr( $opts['mep_stripe_live_sec'] ?? '' );
			$nonce       = wp_create_nonce( 'mep_save_gateway' );
			?>
			<style>
			.mep-gw-modal {
				display: none; position: fixed; inset: 0; z-index: 999999;
				background: rgba(10,10,30,0.65); align-items: center; justify-content: center;
				backdrop-filter: blur(3px);
			}
			.mep-gw-modal-box {
				background: #fff; border-radius: 16px; width: 540px; max-width: 94vw;
				max-height: 92vh; overflow-y: auto; overflow-x: hidden;
				box-shadow: 0 24px 64px rgba(0,0,0,0.3);
				animation: mepModalIn 0.22s ease;
			}
			.mep-gw-modal-box::-webkit-scrollbar { width: 6px; }
			.mep-gw-modal-box::-webkit-scrollbar-track { background: transparent; margin: 16px 0; }
			.mep-gw-modal-box::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
			.mep-gw-modal-box::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
			@keyframes mepModalIn { from { transform: scale(0.94) translateY(12px); opacity:0; } to { transform: scale(1) translateY(0); opacity:1; } }
			.mep-gw-modal-header {
				padding: 22px 26px; display: flex; align-items: center; justify-content: space-between;
				border-radius: 16px 16px 0 0;
			}
			.mep-gw-modal-header h2 { margin: 0; font-size: 19px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 12px; }
			.mep-gw-modal-close {
				background: rgba(255,255,255,0.2); border: none; border-radius: 50%; width: 34px; height: 34px;
				font-size: 20px; line-height: 1; cursor: pointer; color: #fff; display: flex; align-items: center; justify-content: center;
				transition: background 0.2s;
			}
			.mep-gw-modal-close:hover { background: rgba(255,255,255,0.35); }
			.mep-gw-modal-body { padding: 26px 26px 10px; }
			.mep-gw-field { margin-bottom: 20px; }
			.mep-gw-field label.mep-gw-label {
				display: block; font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 7px; letter-spacing: 0.01em;
			}
			.mep-gw-field input[type="text"], .mep-gw-field input[type="password"] {
				width: 100%; padding: 10px 14px; border: 1.5px solid #d1d5db; border-radius: 8px;
				font-size: 14px; color: #111; background: #f9fafb; box-sizing: border-box;
				transition: border-color 0.2s, box-shadow 0.2s;
			}
			.mep-gw-field input[type="text"]:focus, .mep-gw-field input[type="password"]:focus {
				border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); outline: none; background: #fff;
			}
			.mep-gw-toggle-row {
				display: flex; align-items: center; justify-content: space-between;
				padding: 14px 16px; background: #f9fafb; border-radius: 10px; margin-bottom: 20px;
				border: 1.5px solid #e5e7eb;
			}
			.mep-gw-toggle-label { font-weight: 600; font-size: 14px; color: #111827; }
			.mep-gw-toggle-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
			.mep-gw-divider { border: none; border-top: 1px solid #e5e7eb; margin: 4px 0 20px; }
			.mep-gw-section-title { font-size: 12px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px; }
			.mep-gw-modal-footer {
				padding: 16px 26px 22px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
			}
			.mep-gw-save-btn {
				padding: 11px 28px; border: none; border-radius: 8px; font-size: 15px; font-weight: 700;
				cursor: pointer; color: #fff; transition: all 0.2s; flex-shrink: 0;
			}
			.mep-gw-save-btn:hover { opacity: 0.88; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.18); }
			.mep-gw-save-msg {
				display: none; padding: 9px 14px; border-radius: 7px; font-size: 13px; font-weight: 500; flex: 1;
			}
			/* Fancy toggle switch for modals */
			.mep-gw-switch { position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0; }
			.mep-gw-switch input { opacity: 0; width: 0; height: 0; }
			.mep-gw-slider {
				position: absolute; cursor: pointer; inset: 0; background: #d1d5db;
				border-radius: 26px; transition: 0.3s;
			}
			.mep-gw-slider:before {
				content: ""; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px;
				background: #fff; border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
			}
			.mep-gw-switch input:checked + .mep-gw-slider { background: #22c55e; }
			.mep-gw-switch input:checked + .mep-gw-slider:before { transform: translateX(22px); }
			</style>

			<!-- PayPal Config Modal -->
			<div id="mep-paypal-modal" class="mep-gw-modal" style="display:none;">
				<div class="mep-gw-modal-box">
					<div class="mep-gw-modal-header" style="background: linear-gradient(135deg, #003087 0%, #0079C1 100%);">
						<h2>
							<svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z" fill="#fff"/>
								<path d="M11.5 7.1c.05.27.01.59-.09.91-.98 5.05-4.35 6.79-8.65 6.79H4.95l-1.12 7.11a.64.64 0 0 0 .63.74h4.6a.64.64 0 0 0 .63-.54l.87-5.55a.64.64 0 0 1 .63-.54h1.08c3.5 0 6.23-1.42 7.03-5.52.2-.99.23-1.89.09-2.65-.48-2.6-2.58-3.41-5.63-3.41h-2.22z" fill="rgba(255,255,255,0.7)"/>
							</svg>
							<?php esc_html_e( 'PayPal Configuration', 'mage-eventpress' ); ?>
						</h2>
						<button type="button" class="mep-gw-modal-close">&times;</button>
					</div>
					<div class="mep-gw-modal-body">
						<!-- Enable PayPal -->
						<div class="mep-gw-toggle-row">
							<div>
								<div class="mep-gw-toggle-label"><?php esc_html_e( 'Enable PayPal', 'mage-eventpress' ); ?></div>
								<div class="mep-gw-toggle-sub"><?php esc_html_e( 'Accept payments via PayPal', 'mage-eventpress' ); ?></div>
							</div>
							<label class="mep-gw-switch">
								<input type="checkbox" data-field="mep_paypal_enable" <?php checked( $pp_enabled ); ?>>
								<span class="mep-gw-slider"></span>
							</label>
						</div>
						<!-- Sandbox Mode -->
						<div class="mep-gw-toggle-row">
							<div>
								<div class="mep-gw-toggle-label"><?php esc_html_e( 'Sandbox / Test Mode', 'mage-eventpress' ); ?></div>
								<div class="mep-gw-toggle-sub"><?php esc_html_e( 'Use sandbox credentials for testing', 'mage-eventpress' ); ?></div>
							</div>
							<label class="mep-gw-switch">
								<input type="checkbox" data-field="mep_paypal_sandbox" <?php checked( $pp_sandbox ); ?>>
								<span class="mep-gw-slider"></span>
							</label>
						</div>
						<hr class="mep-gw-divider">
						<p class="mep-gw-section-title"><?php esc_html_e( 'API Credentials', 'mage-eventpress' ); ?></p>
						<div class="mep-gw-field">
							<label class="mep-gw-label"><?php esc_html_e( 'PayPal Client ID', 'mage-eventpress' ); ?></label>
							<input type="text" data-field="mep_paypal_client_id" value="<?php echo $pp_client; ?>" placeholder="<?php esc_attr_e( 'Enter your PayPal Client ID', 'mage-eventpress' ); ?>">
						</div>
						<div class="mep-gw-field">
							<label class="mep-gw-label"><?php esc_html_e( 'PayPal Secret Key', 'mage-eventpress' ); ?></label>
							<input type="password" data-field="mep_paypal_secret" value="<?php echo $pp_secret; ?>" placeholder="<?php esc_attr_e( 'Enter your PayPal Secret Key', 'mage-eventpress' ); ?>">
						</div>
					</div>
					<div class="mep-gw-modal-footer">
						<button type="button" class="mep-gw-save-btn" data-gateway="paypal" style="background: linear-gradient(135deg,#003087,#0079C1);">
							<?php esc_html_e( 'Save PayPal Settings', 'mage-eventpress' ); ?>
						</button>
						<span class="mep-gw-save-msg"></span>
					</div>
				</div>
			</div>

			<!-- Stripe Config Modal -->
			<div id="mep-stripe-modal" class="mep-gw-modal" style="display:none;">
				<div class="mep-gw-modal-box">
					<div class="mep-gw-modal-header" style="background: linear-gradient(135deg, #635bff 0%, #3f36c5 100%);">
						<h2>
							<svg width="26" height="26" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
								<path fill="#fff" d="M14.07 15.11c-1.85-.43-2.61-.79-2.61-1.63 0-.79.75-1.33 1.95-1.33 1.34 0 2.87.41 4.31 1.09V8.65c-1.39-.56-2.93-.84-4.52-.84-3.8 0-6.66 1.96-6.66 5.25 0 3.73 3.32 4.96 6.03 5.61 2.05.49 2.8.92 2.8 1.8 0 .86-.87 1.48-2.3 1.48-1.57 0-3.37-.53-5.06-1.54v4.75c1.67.75 3.59 1.13 5.51 1.13 4.13 0 7-2 7-5.34-.01-3.6-3.6-4.41-6.45-5.84z"/>
							</svg>
							<?php esc_html_e( 'Stripe Configuration', 'mage-eventpress' ); ?>
						</h2>
						<button type="button" class="mep-gw-modal-close">&times;</button>
					</div>
					<div class="mep-gw-modal-body">
						<!-- Enable Stripe -->
						<div class="mep-gw-toggle-row">
							<div>
								<div class="mep-gw-toggle-label"><?php esc_html_e( 'Enable Stripe', 'mage-eventpress' ); ?></div>
								<div class="mep-gw-toggle-sub"><?php esc_html_e( 'Accept payments via Stripe', 'mage-eventpress' ); ?></div>
							</div>
							<label class="mep-gw-switch">
								<input type="checkbox" data-field="mep_stripe_enable" <?php checked( $st_enabled ); ?>>
								<span class="mep-gw-slider"></span>
							</label>
						</div>
						<!-- Sandbox Mode -->
						<div class="mep-gw-toggle-row">
							<div>
								<div class="mep-gw-toggle-label"><?php esc_html_e( 'Sandbox / Test Mode', 'mage-eventpress' ); ?></div>
								<div class="mep-gw-toggle-sub"><?php esc_html_e( 'Use test keys instead of live keys', 'mage-eventpress' ); ?></div>
							</div>
							<label class="mep-gw-switch">
								<input type="checkbox" data-field="mep_stripe_sandbox" <?php checked( $st_sandbox ); ?>>
								<span class="mep-gw-slider"></span>
							</label>
						</div>
						<hr class="mep-gw-divider">
						<p class="mep-gw-section-title"><?php esc_html_e( 'Test / Sandbox Keys', 'mage-eventpress' ); ?></p>
						<div class="mep-gw-field">
							<label class="mep-gw-label"><?php esc_html_e( 'Test Publishable Key', 'mage-eventpress' ); ?></label>
							<input type="text" data-field="mep_stripe_test_pub" value="<?php echo $st_test_pub; ?>" placeholder="pk_test_...">
						</div>
						<div class="mep-gw-field">
							<label class="mep-gw-label"><?php esc_html_e( 'Test Secret Key', 'mage-eventpress' ); ?></label>
							<input type="password" data-field="mep_stripe_test_sec" value="<?php echo $st_test_sec; ?>" placeholder="sk_test_...">
						</div>
						<hr class="mep-gw-divider">
						<p class="mep-gw-section-title"><?php esc_html_e( 'Live Keys', 'mage-eventpress' ); ?></p>
						<div class="mep-gw-field">
							<label class="mep-gw-label"><?php esc_html_e( 'Live Publishable Key', 'mage-eventpress' ); ?></label>
							<input type="text" data-field="mep_stripe_live_pub" value="<?php echo $st_live_pub; ?>" placeholder="pk_live_...">
						</div>
						<div class="mep-gw-field">
							<label class="mep-gw-label"><?php esc_html_e( 'Live Secret Key', 'mage-eventpress' ); ?></label>
							<input type="password" data-field="mep_stripe_live_sec" value="<?php echo $st_live_sec; ?>" placeholder="sk_live_...">
						</div>
					</div>
					<div class="mep-gw-modal-footer">
						<button type="button" class="mep-gw-save-btn" data-gateway="stripe" style="background: linear-gradient(135deg,#635bff,#3f36c5);">
							<?php esc_html_e( 'Save Stripe Settings', 'mage-eventpress' ); ?>
						</button>
						<span class="mep-gw-save-msg"></span>
					</div>
				</div>
			</div>

			<script>
			var mepGateway = <?php echo wp_json_encode(array(
				'nonce'    => $nonce,
				'enabled'  => __( 'Enabled', 'mage-eventpress' ),
				'disabled' => __( 'Disabled', 'mage-eventpress' )
			)); ?>;
			
			jQuery(document).ready(function($) {
				// Gateway Configure Buttons — open respective modals
				$(document).on('click', '#mep-paypal-configure-btn', function(e) {
					e.preventDefault();
					$('#mep-paypal-modal').css('display','flex').hide().fadeIn(220);
				});
				$(document).on('click', '#mep-stripe-configure-btn', function(e) {
					e.preventDefault();
					$('#mep-stripe-modal').css('display','flex').hide().fadeIn(220);
				});
				// Close modals
				$(document).on('click', '.mep-gw-modal-close, .mep-gw-modal-backdrop', function() {
					$('.mep-gw-modal').fadeOut(200);
				});
				$(document).on('click', '.mep-gw-modal', function(e) {
					if ($(e.target).hasClass('mep-gw-modal')) $(this).fadeOut(200);
				});
				// Save gateway settings via AJAX
				$(document).on('click', '.mep-gw-save-btn', function(e) {
					e.preventDefault();
					var $btn    = $(this);
					var $modal  = $btn.closest('.mep-gw-modal-box');
					var gateway = $btn.data('gateway');
					var $msg    = $modal.find('.mep-gw-save-msg');
					var fields  = {};
					$modal.find('input[data-field]').each(function() {
						var key = $(this).data('field');
						if ($(this).attr('type') === 'checkbox') {
							fields[key] = $(this).is(':checked') ? 'on' : 'off';
						} else {
							fields[key] = $(this).val();
						}
					});
					$btn.prop('disabled', true).css('opacity','0.7');
					$msg.hide();
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action:  'mep_save_gateway_settings',
							nonce:   mepGateway.nonce,
							gateway: gateway,
							fields:  fields
						},
						success: function(res) {
							if (res.success) {
								$msg.css({'color':'#0f5132','background':'#d1e7dd','border':'1px solid #badbcc'}).text(res.data).fadeIn(200);
								setTimeout(function() { $msg.fadeOut(400); }, 1000);
								// Update status badge on the card (only if on global settings page)
								if ($('.' + gateway + '-card .gateway-status').length > 0) {
									var isEnabled = fields['mep_' + gateway + '_enable'] === 'on';
									var $badge = $('.' + gateway + '-card .gateway-status');
									$badge.text(isEnabled ? mepGateway.enabled : mepGateway.disabled);
									$badge.toggleClass('active', isEnabled);
								}
							} else {
								$msg.css({'color':'#842029','background':'#f8d7da','border':'1px solid #f5c2c7'}).text(res.data).fadeIn(200);
								setTimeout(function() { $msg.fadeOut(400); }, 1000);
							}
						},
						error: function() {
							$msg.css({'color':'#842029','background':'#f8d7da','border':'1px solid #f5c2c7'}).text('A network error occurred.').fadeIn(200);
							setTimeout(function() { $msg.fadeOut(400); }, 1000);
						},
						complete: function() {
							$btn.prop('disabled', false).css('opacity','1');
						}
					});
				});
			});
			</script>
			<?php
		}

		function ajax_save_gateway_settings() {
			check_ajax_referer( 'mep_save_gateway', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'Permission denied.', 'mage-eventpress' ) );
			}
			$gateway  = sanitize_key( $_POST['gateway'] ?? '' );
			$fields   = $_POST['fields'] ?? array();
			$existing = get_option( 'payment_setting_sec', array() );

			$allowed = array(
				'paypal' => array( 'mep_paypal_enable', 'mep_paypal_sandbox', 'mep_paypal_client_id', 'mep_paypal_secret' ),
				'stripe' => array( 'mep_stripe_enable', 'mep_stripe_sandbox', 'mep_stripe_test_pub', 'mep_stripe_test_sec', 'mep_stripe_live_pub', 'mep_stripe_live_sec' ),
			);

			if ( ! array_key_exists( $gateway, $allowed ) ) {
				wp_send_json_error( __( 'Invalid gateway.', 'mage-eventpress' ) );
			}

			foreach ( $allowed[ $gateway ] as $key ) {
				$val = isset( $fields[ $key ] ) ? $fields[ $key ] : 'off';
				// Toggles are on/off; other fields are text
				if ( in_array( $key, array( 'mep_paypal_enable', 'mep_paypal_sandbox', 'mep_stripe_enable', 'mep_stripe_sandbox' ), true ) ) {
					$existing[ $key ] = ( $val === 'on' ) ? 'on' : 'off';
				} else {
					$existing[ $key ] = sanitize_text_field( $val );
				}
			}

			update_option( 'payment_setting_sec', $existing );
			wp_send_json_success( __( 'Settings saved successfully!', 'mage-eventpress' ) );
		}

			function payment_tabs_script() {
				?>
				<style>
					.payment-sub-tabs-wrapper {
						margin-top: 15px;
						margin-bottom: 25px;
						background: #f8f9fa;
						padding: 10px 15px;
						border-radius: 8px;
						border: 1px solid #e2e4e7;
						box-shadow: 0 1px 3px rgba(0,0,0,0.05);
					}
					.payment-sub-tabs.nav-tab-wrapper {
						border-bottom: none !important;
						padding: 0 !important;
						margin: 0 !important;
						display: flex;
						gap: 10px;
					}
					.payment-sub-tabs .nav-tab {
						background: #fff;
						border: 1px solid #ccd0d4;
						border-radius: 6px;
						padding: 8px 16px;
						font-size: 14px;
						font-weight: 500;
						color: #3c434a !important;
						transition: all 0.2s ease;
						text-decoration: none;
						margin: 0;
					}
					.payment-sub-tabs .nav-tab:hover {
						background: #f0f0f1;
						color: #2271b1 !important;
						border-color: #2271b1;
					}
					.payment-sub-tabs .nav-tab-active,
					.payment-sub-tabs .nav-tab-active:hover {
						background: #2271b1;
						color: #fff !important;
						border-color: #2271b1;
						box-shadow: 0 2px 5px rgba(34,113,177,0.2);
					}
				</style>

				<style> /* CSS for Toggle Switch */
					input[type="checkbox"]#wpuf-payment_setting_sec\[mep_enable_wc_payment\] {
						appearance: none;
						-webkit-appearance: none;
						outline: none;
						cursor: pointer;
						width: 44px;
						height: 24px;
						background: #ccc;
						border-radius: 24px;
						position: relative;
						transition: background 0.3s;
						vertical-align: middle;
						margin-right: 10px;
						border: none;
					}
					input[type="checkbox"]#wpuf-payment_setting_sec\[mep_enable_wc_payment\]::after {
						content: '';
						position: absolute;
						top: 3px;
						left: 3px;
						width: 18px;
						height: 18px;
						background: #fff;
						border-radius: 50%;
						transition: left 0.3s;
						box-shadow: 0 1px 3px rgba(0,0,0,0.3);
					}
					input[type="checkbox"]#wpuf-payment_setting_sec\[mep_enable_wc_payment\]:checked {
						background: #2271b1 !important;
						background-image: none !important;
					}
					input[type="checkbox"]#wpuf-payment_setting_sec\[mep_enable_wc_payment\]:checked::before {
						content: none !important;
						display: none !important;
					}
					input[type="checkbox"]#wpuf-payment_setting_sec\[mep_enable_wc_payment\]:checked::after {
						left: 23px;
					}
				</style>
				<style>
tr.payment_tabs_html { display: none !important; }
.payment-gateways-container th { display: none; }
.payment-gateways-container td { padding: 15px 20px !important; }
.gateway-card {
    background: #fff;
    border: 1px solid #e2e4e7;
    border-radius: 10px;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    width: 100%;
    box-sizing: border-box;
    transition: all 0.3s ease;
}
.gateway-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.gateway-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e2e4e7;
    background: #fcfcfc;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    position: relative;
}
.gateway-header h3 {
    margin: 0;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1e1e1e;
    font-weight: 600;
}
.gateway-header .gateway-status {
    font-size: 13px;
    padding: 4px 12px;
    border-radius: 20px;
    background: #f0f0f1;
    color: #555;
    font-weight: 500;
}
.gateway-header .gateway-status.active {
    background: #d1e7dd;
    color: #0f5132;
}
.gateway-body {
    padding: 25px;
    display: none;
}
.gateway-field {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.gateway-field:last-child {
    margin-bottom: 0;
}
.gateway-field label.gateway-label {
    display: block;
    font-weight: 600;
    color: #2c3338;
    font-size: 14px;
}
.gateway-field input[type="text"], .gateway-field input[type="password"] {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #c3c4c7;
    border-radius: 6px;
    font-size: 14px;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.gateway-field input[type="text"]:focus, .gateway-field input[type="password"]:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}
.gateway-field p.description {
    margin: 0;
    font-style: normal;
    color: #646970;
    font-size: 13px;
}
/* Switch */
.gateway-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    vertical-align: middle;
}
.gateway-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.gateway-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 24px;
}
.gateway-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
.gateway-switch input:checked + .gateway-slider {
    background-color: #2271b1;
}
.gateway-switch input:checked + .gateway-slider:before {
    transform: translateX(20px);
}

/* Colorful Brands UI - Full Card */
.gateway-card.paypal-card {
    background: linear-gradient(135deg, #003087 0%, #0079C1 100%);
    border: none;
    color: #fff;
}
.gateway-card.paypal-card .gateway-header { background: transparent; border-bottom: 1px solid rgba(255,255,255,0.15); }
.gateway-card.paypal-card .gateway-header h3 { color: #fff; }
.gateway-card.paypal-card .gateway-header svg path { fill: #fff !important; }
.gateway-card.paypal-card .gateway-status { background: rgba(255,255,255,0.2); color: #fff; }
.gateway-card.paypal-card .gateway-status.active { background: #fff; color: #003087; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.gateway-card.paypal-card .gateway-configure-btn { color: #003087 !important; background: #fff !important; border: none !important; font-weight:600 !important; border-radius: 6px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important; }
.gateway-card.paypal-card label.gateway-label { color: #fff; }
.gateway-card.paypal-card p.description { color: rgba(255,255,255,0.85); }
.gateway-card.paypal-card input[type="text"], .gateway-card.paypal-card input[type="password"] { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: #fff; }
.gateway-card.paypal-card input[type="text"]:focus, .gateway-card.paypal-card input[type="password"]:focus { background: rgba(255,255,255,0.2); border-color: #fff; box-shadow: 0 0 0 1px #fff; }

.gateway-card.stripe-card {
    background: linear-gradient(135deg, #635bff 0%, #3f36c5 100%);
    border: none;
    color: #fff;
	margin-bottom: 0;
}
.gateway-card.stripe-card .gateway-header { background: transparent; border-bottom: 1px solid rgba(255,255,255,0.15); }
.gateway-card.stripe-card .gateway-header h3 { color: #fff; }
.gateway-card.stripe-card .gateway-header svg path { fill: #fff !important; }
.gateway-card.stripe-card .gateway-status { background: rgba(255,255,255,0.2); color: #fff; }
.gateway-card.stripe-card .gateway-status.active { background: #fff; color: #635bff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.gateway-card.stripe-card .gateway-configure-btn { color: #635bff !important; background: #fff !important; border: none !important; font-weight:600 !important; border-radius: 6px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important; }
.gateway-card.stripe-card label.gateway-label { color: #fff; }
.gateway-card.stripe-card p.description { color: rgba(255,255,255,0.85); }
.gateway-card.stripe-card input[type="text"], .gateway-card.stripe-card input[type="password"] { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: #fff; }
.gateway-card.stripe-card input[type="text"]:focus, .gateway-card.stripe-card input[type="password"]:focus { background: rgba(255,255,255,0.2); border-color: #fff; box-shadow: 0 0 0 1px #fff; }
</style>

				<script>
					jQuery(document).ready(function($) {
						if ($('.payment-sub-tabs').length > 0) {
							// WooCommerce Setting Toggle Logic
							function toggleWcSettings() {
								var isChecked = $('#wpuf-payment_setting_sec\\[mep_enable_wc_payment\\]').is(':checked');
								var $wcFields = $('tr.woocommerce-field').not('tr.woocommerce-main-toggle');
								if (isChecked) {
									$wcFields.fadeIn(200);
								} else {
									$wcFields.hide();
								}
							}
							
							$('#wpuf-payment_setting_sec\\[mep_enable_wc_payment\\]').on('change', toggleWcSettings);
							function updateTabs() {
								var activeTabId = $(".payment-sub-tabs .nav-tab-active").attr("href").replace("#", "");
								$("tr.woocommerce-field, div.woocommerce-field, tr.no-woocommerce-field").hide();
								
								// Hide save button on Custom Payment tab
								if (activeTabId === 'no-woocommerce-field') {
									$('#payment_setting_sec .submit').hide();
								} else {
									$('#payment_setting_sec .submit').show();
								}
								
								var isWcActive = <?php echo class_exists('WooCommerce') ? 'true' : 'false'; ?>;
								
								// Special handling: if we have a div.woocommerce-field (the warning), show it
								if (activeTabId === 'woocommerce-field') {
									$("div.woocommerce-field").show();
									$('#mep-wc-warning-banner').show();
									
									if (isWcActive) {
										$("tr.woocommerce-field").show();
										toggleWcSettings();
									} else {
										// Hide the save button if WooCommerce isn't active since settings are hidden
										$('#payment_setting_sec .submit').hide();
									}
								} else {
									$('#mep-wc-warning-banner').hide();
									$("tr." + activeTabId).show();
								}
							}
													$(".payment-sub-tabs .nav-tab").click(function(e) {
								e.preventDefault();
								$(".payment-sub-tabs .nav-tab").removeClass("nav-tab-active");
								$(this).addClass("nav-tab-active");
								updateTabs();
							});
							updateTabs();
						}
						
					


							// Modal logic — outside tab guard so it always binds
							$(document).on('click', '.mep-install-wc-trigger', function(e) {
								e.preventDefault();
								$('#mep-wc-install-modal').css('display', 'flex').hide().fadeIn(200);
							});

							$(document).on('click', '.mep-close-modal', function() {
								$('#mep-wc-install-modal').fadeOut(200);
							});

							$('#mep-wc-start-install').click(function() {
								var $btn = $(this);
								var $progress = $('#mep-wc-install-progress');
								var $status = $('#mep-wc-install-status');
								
								$btn.prop('disabled', true);
								$btn.css('opacity', '0.6');
								$progress.fadeIn(200);
								$status.text( <?php echo json_encode( __( "Please wait, configuring WooCommerce...", "mage-eventpress" ) ); ?> );
								
								$.ajax({
									url: ajaxurl,
									type: "POST",
									data: {
										action: "mep_install_activate_wc",
										nonce: "<?php echo wp_create_nonce('mep_install_wc'); ?>"
									},
									success: function(response) {
										if (response.success) {
											$status.css('color', '#0f5132');
											$status.text( <?php echo json_encode( __( "Successfully Activated!", "mage-eventpress" ) ); ?> );
											setTimeout(function() {
												$('#mep-wc-install-modal').fadeOut(200);
												$('.mpwem-woo-warning-notice').hide();
												$('#mep-woo-warning-style').remove(); // remove the !important CSS override
												
												var activeTabId = $(".payment-sub-tabs .nav-tab-active").attr("href").replace("#", "");
												if (activeTabId === 'woocommerce-field') {
													$('tr.woocommerce-field').css('display', 'table-row').hide().fadeIn(200);
													$('#payment_setting_sec .submit').fadeIn(200);
												}
												$('#wpuf-payment_setting_sec\\[mep_enable_wc_payment\\]').trigger('change');
												
												setTimeout(function() {
													$btn.prop("disabled", false).css('opacity', '1');
													$progress.hide();
												}, 200);
											}, 1000);
										} else {
											$status.css('color', '#dc3545');
											$status.text( <?php echo json_encode( __( "Error: ", "mage-eventpress" ) ); ?> + (response.data || "Unknown error") );
											$btn.prop("disabled", false);
											$btn.css('opacity', '1');
										}
									},
									error: function() {
										$status.css('color', '#dc3545');
										$status.text( <?php echo json_encode( __( "A network error occurred. Please try again.", "mage-eventpress" ) ); ?> );
										$btn.prop("disabled", false);
										$btn.css('opacity', '1');
									}
								});
							});
							// Move the wrapper out of the table so it displays like a real tab bar spanning full width
							var $tabContainer = $('.payment-sub-tabs-wrapper');
							var $table = $tabContainer.closest('table.form-table');
							$tabContainer.insertBefore($table);
							
							// The tab container was originally inside a tr. We should hide that tr to prevent an empty row.
							// But since we already moved $tabContainer, we need to hide the tr that has an empty th and a td containing just a p.description
							$table.find('tr').each(function() {
								if ($(this).find('.payment-sub-tabs-wrapper').length === 0 && $(this).text().trim() === '') {
									$(this).hide();
								}
							});
							// Add styles for text color
							$('.payment-sub-tabs .nav-tab').css('color', 'black');
					});
				</script>
				<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
				<div id="mep-wc-install-modal" style="display:none; position:fixed; z-index:99999999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
					<div style="background:#fff; border-radius:12px; width:450px; max-width:90%; box-shadow:0 10px 30px rgba(0,0,0,0.3); overflow:hidden;">
						<div style="padding:20px; border-bottom:1px solid #e2e4e7; display:flex; justify-content:space-between; align-items:center; background:#f8f9fa;">
							<h3 style="margin:0; font-size:18px; color:#2c3338;"><?php _e( "WooCommerce Configuration", "mage-eventpress" ); ?></h3>
							<button type="button" class="mep-close-modal" style="background:none; border:none; font-size:24px; cursor:pointer; color:#666; line-height:1;">&times;</button>
						</div>
						<div style="padding:30px 25px; text-align:center;">
							<span class="dashicons dashicons-cart" style="font-size:64px; width:64px; height:64px; color:#96588a; margin-bottom:20px;"></span>
							<p style="font-size:16px; color:#444; margin-bottom:25px; line-height:1.5;">
								<?php if ( file_exists( WP_PLUGIN_DIR . "/woocommerce/woocommerce.php" ) ) : ?>
									<?php _e( "WooCommerce is required to process payments. It is already installed on your site, but needs to be activated. Click the button below to activate it right now.", "mage-eventpress" ); ?>
								<?php else : ?>
									<?php _e( "WooCommerce is required to process payments. We will securely download, install, and activate it for you right now.", "mage-eventpress" ); ?>
								<?php endif; ?>
							</p>
							<div id="mep-wc-install-progress" style="display:none; margin-bottom:20px; font-weight:500; font-size:15px; background:#f0f0f1; padding:12px; border-radius:6px;">
								<span class="spinner is-active" style="float:none; margin:0 10px 0 0;"></span> <span id="mep-wc-install-status" style="color:#2271b1;"><?php _e( "Working...", "mage-eventpress" ); ?></span>
							</div>
							<button type="button" id="mep-wc-start-install" class="button button-primary button-hero" style="background:#96588a; border-color:#703c66; width:100%; box-shadow:0 2px 4px rgba(150,88,138,0.3); transition:all 0.2s;">
								<?php echo file_exists( WP_PLUGIN_DIR . "/woocommerce/woocommerce.php" ) ? esc_html__( "Activate WooCommerce Now", "mage-eventpress" ) : esc_html__( "Install & Activate Now", "mage-eventpress" ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<?php
			}


			function ajax_save_payment_settings_modal() {
				if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_posts' ) ) {
					wp_send_json_error( __( 'Permission denied.', 'mage-eventpress' ) );
				}

				$payment_settings = get_option( 'payment_setting_sec', array() );
				$payment_settings['mep_enable_wc_payment'] = isset( $_POST['mep_enable_wc_payment'] ) ? sanitize_text_field( $_POST['mep_enable_wc_payment'] ) : 'off';
				$payment_settings['mep_paypal_enable'] = isset( $_POST['mep_paypal_enable'] ) ? sanitize_text_field( $_POST['mep_paypal_enable'] ) : 'off';
				$payment_settings['mep_stripe_enable'] = isset( $_POST['mep_stripe_enable'] ) ? sanitize_text_field( $_POST['mep_stripe_enable'] ) : 'off';
				
				$payment_settings['mep_wc_add_to_cart_redirect'] = isset( $_POST['mep_wc_add_to_cart_redirect'] ) ? sanitize_text_field( $_POST['mep_wc_add_to_cart_redirect'] ) : 'checkout';
				$payment_settings['mep_wc_after_order_redirect'] = isset( $_POST['mep_wc_after_order_redirect'] ) ? sanitize_text_field( $_POST['mep_wc_after_order_redirect'] ) : 'plugin_thankyou';
				$payment_settings['mep_wc_require_login'] = isset( $_POST['mep_wc_require_login'] ) ? sanitize_text_field( $_POST['mep_wc_require_login'] ) : '';
				$payment_settings['mep_wc_show_billing_info'] = isset( $_POST['mep_wc_show_billing_info'] ) ? sanitize_text_field( $_POST['mep_wc_show_billing_info'] ) : '';
				
				if ( isset( $_POST['mep_wc_confirm_ticket_status'] ) && is_array( $_POST['mep_wc_confirm_ticket_status'] ) ) {
					$statuses = array();
					foreach ( $_POST['mep_wc_confirm_ticket_status'] as $status ) {
						$sanitized = sanitize_text_field( $status );
						$statuses[ $sanitized ] = $sanitized;
					}
					$payment_settings['mep_wc_confirm_ticket_status'] = $statuses;
				} else {
					$payment_settings['mep_wc_confirm_ticket_status'] = array();
				}

				update_option( 'payment_setting_sec', $payment_settings );
				wp_send_json_success( __( 'Settings saved.', 'mage-eventpress' ) );
			}

			function ajax_install_activate_wc() {
				check_ajax_referer( 'mep_install_wc', 'nonce' );

				if ( ! current_user_can( 'install_plugins' ) ) {
					wp_send_json_error( __( 'Permission denied.', 'mage-eventpress' ) );
				}

				// Load all required WP admin includes — not auto-loaded in AJAX context
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/misc.php';

				$plugin_slug = 'woocommerce';
				$plugin_file = 'woocommerce/woocommerce.php';

				// Install if not already downloaded
				if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
					$api = plugins_api( 'plugin_information', array(
						'slug'   => $plugin_slug,
						'fields' => array( 'sections' => false ),
					) );

					if ( is_wp_error( $api ) ) {
						wp_send_json_error( $api->get_error_message() );
					}

					$upgrader       = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
					$install_result = $upgrader->install( $api->download_link );

					if ( is_wp_error( $install_result ) ) {
						wp_send_json_error( $install_result->get_error_message() );
					} elseif ( ! $install_result ) {
						wp_send_json_error( __( 'Installation failed. Please try manually.', 'mage-eventpress' ) );
					}
				}

				// Activate directly via the options table — avoids loading woocommerce.php
				// into this PHP process which would cause a "Cannot redeclare WC()" fatal
				// because our WC() fallback is already declared at plugins_loaded priority 1.
				$current = get_option( 'active_plugins', array() );
				if ( ! in_array( $plugin_file, $current, true ) ) {
					$current[] = $plugin_file;
					sort( $current );
					update_option( 'active_plugins', $current );
				}

				// Run the plugin's activation hook cleanly via a separate internal request
				do_action( 'activate_' . $plugin_file );
				do_action( 'activated_plugin', $plugin_file, false );

				wp_send_json_success( __( 'WooCommerce activated successfully!', 'mage-eventpress' ) );
			}

			function admin_init() {
				//set the settings
				$this->settings_api->set_sections( $this->get_settings_sections() );
				$this->settings_api->set_fields( $this->get_settings_fields() );
				//initialize settings
				$this->settings_api->admin_init();
			}

			function admin_menu() {
				$event_label = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				//add_options_page( 'Event Settings', 'Event Settings', 'delete_posts', 'mep_event_settings_page', array($this, 'plugin_page') );
				$menu_label = sprintf(
				/* translators: %s is the event label, e.g., "Conference" */
					__( '%s Settings', 'mage-eventpress' ),
					$event_label
				);
				add_submenu_page(
					'edit.php?post_type=mep_events',
					$menu_label,
					$menu_label,
					'manage_options',
					'mep_event_settings_page',
					array( $this, 'plugin_page' )
				);
			}

			function get_settings_sections() {
				$sections = array(
					array(
						'id'    => 'general_setting_sec',
						'title' => '<i class="mi mi-settings"></i>' . __( 'General Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'event_list_setting_sec',
						'title' => '<i class="mi mi-rectangle-list"></i>' . __( 'Event List Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'single_event_setting_sec',
						'title' => '<i class="mi mi-calendar"></i>' . __( 'Single Event Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'email_setting_sec',
						'title' => '<i class="mi mi-envelope"></i>' . __( 'Email Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'style_setting_sec',
						'title' => '<i class="mi mi-palette"></i>' . __( 'Style Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'icon_setting_sec',
						'title' => '<i class="mi mi-icon-star"></i>' . __( 'Icon Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'carousel_setting_sec',
						'title' => '<i class="mi mi-copy-image"></i>' . __( 'Carousel Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'mp_slider_settings',
						'title' => '<i class="mi mi-settings-sliders"></i>' . __( 'Slider Settings', 'mage-eventpress' )
					),
					array(
						'id'    => 'payment_setting_sec',
						'title' => '<i class="mi mi-shopping-cart"></i>' . __( 'Payment', 'mage-eventpress' )
					),
					array(
						'id'    => 'mep_settings_licensing',
						'title' => '<i class="mi mi-license"></i>'. __( 'License', 'mage-eventpress' )
					)
				);

				return apply_filters( 'mep_settings_sec_reg', $sections );
			}

			/**
			 * Returns all the settings fields
			 *
			 * @return array settings fields
			 */
			function get_settings_fields() {
				$current_date = current_time( 'Y-m-d' );
				$lang         = get_bloginfo( "language" );
				
				$payment_opts = get_option('payment_setting_sec');
				$pp_enable = isset($payment_opts['mep_paypal_enable']) ? checked($payment_opts['mep_paypal_enable'], 'on', false) : '';
				$pp_sandbox = isset($payment_opts['mep_paypal_sandbox']) ? checked($payment_opts['mep_paypal_sandbox'], 'on', false) : 'checked="checked"';
				$pp_client = isset($payment_opts['mep_paypal_client_id']) ? esc_attr($payment_opts['mep_paypal_client_id']) : '';
				$pp_secret = isset($payment_opts['mep_paypal_secret']) ? esc_attr($payment_opts['mep_paypal_secret']) : '';

				$st_enable = isset($payment_opts['mep_stripe_enable']) ? checked($payment_opts['mep_stripe_enable'], 'on', false) : '';
				$st_sandbox = isset($payment_opts['mep_stripe_sandbox']) ? checked($payment_opts['mep_stripe_sandbox'], 'on', false) : 'checked="checked"';
				$st_test_pub = isset($payment_opts['mep_stripe_test_pub']) ? esc_attr($payment_opts['mep_stripe_test_pub']) : '';
				$st_test_sec = isset($payment_opts['mep_stripe_test_sec']) ? esc_attr($payment_opts['mep_stripe_test_sec']) : '';
				$st_live_pub = isset($payment_opts['mep_stripe_live_pub']) ? esc_attr($payment_opts['mep_stripe_live_pub']) : '';
				$st_live_sec = isset($payment_opts['mep_stripe_live_sec']) ? esc_attr($payment_opts['mep_stripe_live_sec']) : '';
				$settings_fields = array(
					'general_setting_sec'      => apply_filters( 'mep_settings_general_arr', array(
							array(
								'name'    => 'seat_reserved_order_status',
								'label'   => __( 'Seat Reserved Order Status', 'mage-eventpress' ),
								'desc'    => __( 'Please select in which order status seat will mark as reserved/booked. By Default is Processing & Completed.', 'mage-eventpress' ),
								'type'    => 'multicheck',
								'default' => array( 'processing' => 'processing', 'completed' => 'completed' ),
								'options' => array(
									'on-hold'    => 'On Hold',
									'pending'    => 'Pending',
									'processing' => 'Processing',
									'completed'  => 'Completed'
									// 'cancelled'     => 'Cancelled'
								)
							),
							array(
								'name'    => 'mep_disable_block_editor',
								'label'   => __( 'Block/Gutenberg Editor In Event', 'mage-eventpress' ),
								'desc'    => __( 'By default, the Gutenberg editor is disabled. To enable the Gutenberg editor, you need to activate this option and also ensure that the REST API is enabled in the settings below.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Disable',
									'no'  => 'Enable'
								)
							),
							array(
								'name'    => 'mep_event_list_page_style',
								'label'   => __( 'Dashboard Event List Page Style', 'mage-eventpress' ),
								'desc'    => __( 'You can choose the Event List Page Design in Dasboard', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'new',
								'options' => array(
									'new' => 'New Modern Style',
									'wp'  => 'WordPress Default Post List Style'
								)
							),
							array(
								'name'    => 'mep_event_edit_page_mode',
								'label'   => __( 'Event Edit Page Mode', 'mage-eventpress' ),
								'desc'    => __( 'Choose which editor opens by default when you add or edit an event.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'modern',
								'options' => array(
									'modern'  => __( 'Modern', 'mage-eventpress' ),
									'classic' => __( 'Classic', 'mage-eventpress' )
								)
							),
							array(
								'name'    => 'mep_rest_api_status',
								'label'   => __( 'Enable Rest API?', 'mage-eventpress' ),
								'desc'    => __( 'If you want to enable event data available in the Rest API Please enable this.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_multi_lang_plugin',
								'label'   => __( 'Choose Multilingual Plugin', 'mage-eventpress' ),
								'desc'    => __( 'Please select the name of your multilingual plugin from the list below.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'none',
								'options' => array(
									'none'     => 'None',
									'polylang' => 'Polylang',
									'wpml'     => 'WPML'
								)
							),
							array(
								'name'    => 'mep_event_list_order_by',
								'label'   => __( 'Event List Order By', 'mage-eventpress' ),
								'desc'    => __( 'Please select Event list order by which value Event Title or Event Date. By Default is: Event Upcoming Date', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'meta_value',
								'options' => array(
									'meta_value' => 'Event Upcoming Date',
									'title'      => 'Event Title'
								)
							),
							array(
								'name'    => 'mep_event_label',
								'label'   => __( 'Event Label', 'mage-eventpress' ),
								'desc'    => __( 'It will change the event post type label for the entire plugin.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Events'
							),
							array(
								'name'    => 'mep_event_slug',
								'label'   => __( 'Event Slug', 'mage-eventpress' ),
								'desc'    => __( 'It will change the event slug throughout the entire plugin. Remember, after changing this slug you need to flush permalinks. Just go to <strong>Settings->Permalinks</strong> hit the Save Settings button', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'events'
							),
							array(
								'name'    => 'mep_event_icon',
								'label'   => __( 'Event Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please enter the icon class name for the event custom post type. You can find icons here: Example: dashicons-calendar-alt. Find Icons: <a href="https://developer.wordpress.org/resource/dashicons/">Dashicons</a>', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'dashicons-calendar-alt'
							),
							array(
								'name'    => 'mep_event_cat_label',
								'label'   => __( 'Event Category Label', 'mage-eventpress' ),
								'desc'    => __( 'This change will apply the event category label to the whole plugin.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Category'
							),
							array(
								'name'    => 'mep_event_cat_slug',
								'label'   => __( 'Event Category Slug', 'mage-eventpress' ),
								'desc'    => __( 'It will change the category slug for the entire plugin. Remember that after you change this slug, you need to flush permalinks. To do this, just go to <strong>Settings->Permalinks</strong> hit the Save Settings button.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'mep_cat'
							),
							array(
								'name'    => 'mep_event_org_label',
								'label'   => __( 'Event Organizer Label', 'mage-eventpress' ),
								'desc'    => __( 'This will change the event organizer label throughout the plugin.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Organizer'
							),
							array(
								'name'    => 'mep_event_org_slug',
								'label'   => __( 'Event Organizer Slug', 'mage-eventpress' ),
								'desc'    => __( 'Changing the event organizer slug will have an effect on the entire plugin. Remember, after changing the slug, you will need to flush the permalinks. To do so, simply go to your settings page and select the flush permalinks option. <strong>Settings->Permalinks</strong> hit the Save Settings button.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'mep_org'
							),
							array(
								'name'    => 'mep_google_map_type',
								'label'   => __( 'Google Map Type', 'mage-eventpress' ),
								'desc'    => __( 'Please select the preferred map type you want to be displayed on the front page.<br><strong>Note:</strong> It"s been known that Iframe does not always show the precise location, whereas the API enabled map has a drag and drop feature for more accuracy. So if necessary, you can drag the point to the desired location.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									''       => 'Please Select a Map Type',
									'api'    => 'API',
									'iframe' => 'Iframe'
								)
							),
							array(
								'name'    => 'google-map-api',
								'label'   => __( 'Google Map API Key', 'mage-eventpress' ),
								'desc'    => __( 'Enter Your Google Map API key. <a href=https://developers.google.com/maps/documentation/javascript/get-api-key target=_blank>Get API Key</a>. <br><strong>Note:</strong> You must enter your billing address and information into the Google Maps API account to make it perfectly workable on your website.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => ''
							),
							array(
								'name'    => 'mep_event_expire_on_datetimes',
								'label'   => __( 'When will the event expire', 'mage-eventpress' ),
								'desc'    => __( 'Please select when the event will end', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'mep_event_start_date',
								'options' => array(
									'event_start_datetime'  => 'Event Start Time',
									'event_expire_datetime' => 'Event End Time'
								)
							),
							array(
								'name'    => 'mep_hide_old_date',
								'label'   => __( 'Hide old date from date picker', 'mage-eventpress' ),
								'desc'    => __( 'If you would like to hide the location details from the order details section on the thank you page and confirmation email body, please choose "Yes". If you would like to show the location details, please choose "No". The default setting is "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_expire_ticket',
								'label'   => __( 'Hide expire ticket type', 'mage-eventpress' ),
								'desc'    => __( 'If you would like to hide the location details from the order details section on the thank you page and confirmation email body, please choose "Yes". If you would like to show the location details, please choose "No". The default setting is "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_location_from_order_page',
								'label'   => __( 'Hide Location From Order Details & Email Section', 'mage-eventpress' ),
								'desc'    => __( 'If you would like to hide the location details from the order details section on the thank you page and confirmation email body, please choose "Yes". If you would like to show the location details, please choose "No". The default setting is "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_date_from_order_page',
								'label'   => __( 'Hide Date From Order Details & Email Section', 'mage-eventpress' ),
								'desc'    => __( 'This toggle determines whether or not the date is shown in the order details section of the thank you page and confirmation email body. Choose "Yes" to hide the date or "No" to show it. The default is "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_expired_date_in_calendar',
								'label'   => __( 'Hide Expired Event from Calendar', 'mage-eventpress' ),
								'desc'    => __( 'If you want to hide the expired event from the calendar please select Yes. Its applicable for the Free Calendar', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_direct_checkout',
								'label'   => __( 'Redirect Checkout after Booking', 'mage-eventpress' ),
								'desc'    => __( 'This setting controls whether or not the checkout page is redirected after booking an event.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Enable',
									'no'  => 'Disable'
								)
							),
							array(
								'name'    => 'mep_show_zero_as_free',
								'label'   => __( 'Show 0 Price as Free', 'mage-eventpress' ),
								'desc'    => __( 'This setting enables you to a "Free" at a price of 0. By default, this setting is enabled.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'        => 'mep_ticket_expire_time',
								'label'       => __( 'Event Ticket Expire before minutes', 'mage-eventpress' ),
								'desc'        => __( 'Please enter the number of minutes before the event that an attendee cannot book/register a ticket.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => '0',
								'placeholder' => '15'
							),
							array(
								'name'        => 'mep_ticket_expire_time_on_cart',
								'label'       => __( 'Event Expire Time on Cart', 'mage-eventpress' ),
								'desc'        => __( 'Please enter the number of minutes after that the event will removed from the cart', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => '10',
								'placeholder' => '10'
							),							
							array(
								'name'    => 'mep_load_fontawesome_from_theme',
								'label'   => __( 'Load Font Awesome From Theme?', 'mage-eventpress' ),
								'desc'    => __( 'If the icons are not working and you want to disable Font Awesome loading from the plugin, select Yes.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_load_flaticon_from_theme',
								'label'   => __( 'Load Flat Icon From Theme?', 'mage-eventpress' ),
								'desc'    => __( 'If the icons are not working, and you want to remove Flat Icon load from the plugin, select "Yes."', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_speed_up_list_page',
								'label'   => __( 'Speed up the Event List Page Loading?', 'mage-eventpress' ),
								'desc'    => __( 'If your event list page is loading slowly, you can select this option to improve performance. Keep in mind that selecting this option will disable Waitlist and Seat count features. ', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_not_available_event_from_list_page',
								'label'   => __( 'Disappear Event from list when fully booked?', 'mage-eventpress' ),
								'desc'    => __( 'If you want your event to be removed from the list once it is fully booked, you can select "Yes" here.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_sold_out_ribbon_list_page',
								'label'   => __( 'Show Sold out Ribon?', 'mage-eventpress' ),
								'desc'    => __( 'You can show a "Sold Out" Ribbon on the event list when it is fully booked by selecting "Yes" here.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_limited_availability_ribbon',
								'label'   => __( 'Show Limited Availability Ribbon?', 'mage-eventpress' ),
								'desc'    => __( 'Display a "Limited Availability" ribbon when tickets are running low but not sold out yet.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'        => 'mep_limited_availability_threshold',
								'label'       => __( 'Limited Availability Threshold', 'mage-eventpress' ),
								'desc'        => __( 'Show "Limited Availability" ribbon when available seats are less than or equal to this number.', 'mage-eventpress' ),
								'type'        => 'number',
								'default'     => '0',
								'placeholder' => '5'
							),
							array(
								'name'        => 'mep_limited_availability_text',
								'label'       => __( 'Limited Availability Ribbon Text', 'mage-eventpress' ),
								'desc'        => __( 'The text to display on the limited availability ribbon.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => 'Limited Availability',
								'placeholder' => 'Limited Availability'
							),
							array(
								'name'    => 'mep_show_low_stock_warning',
								'label'   => __( 'Show Low Stock Warning?', 'mage-eventpress' ),
								'desc'    => __( 'Enable this to show a warning message when event seats are running low.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'        => 'mep_low_stock_threshold',
								'label'       => __( 'Low Stock Threshold', 'mage-eventpress' ),
								'desc'        => __( 'Show low stock warning when available seats are less than or equal to this number.', 'mage-eventpress' ),
								'type'        => 'number',
								'default'     => '0',
								'placeholder' => '3'
							),
							array(
								'name'        => 'mep_low_stock_text',
								'label'       => __( 'Low Stock Warning Text', 'mage-eventpress' ),
								'desc'        => __( 'The text to display when seats are running low.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => 'Hurry! Only %s seats left',
								'placeholder' => 'Hurry! Only %s seats left'
							),
							array(
								'name'    => 'mep_enable_low_stock_email',
								'label'   => __( 'Send Low Stock Email Notifications?', 'mage-eventpress' ),
								'desc'    => __( 'Enable this to send email notifications to admin when event seats are running low.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_hidden_wc_product',
								'label'   => __( 'Show Hidden Woocommerce Products?', 'mage-eventpress' ),
								'desc'    => __( 'With every creation of an event there is a Woocommerce product is also created. By default its hidden in the Product list. If you want to show them in the list select Yes', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_google_map_zoom_level',
								'label'   => __( 'Set the Google Map Zoom Level', 'mage-eventpress' ),
								'desc'    => __( 'Select the Google Map zoom level. By default is 17', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => '17',
								'options' => array(
									'5'  => '5',
									'6'  => '6',
									'7'  => '7',
									'8'  => '8',
									'9'  => '9',
									'10' => '10',
									'11' => '11',
									'12' => '12',
									'13' => '13',
									'14' => '14',
									'15' => '15',
									'16' => '16',
									'17' => '17',
									'18' => '18',
									'19' => '19',
									'20' => '20',
									'21' => '21',
									'22' => '22',
									'23' => '23',
									'24' => '24',
									'25' => '25'
								)
							),
							array(
								'name'    => 'mep_show_event_sidebar',
								'label'   => __( 'Show Event Sidebar Widgets?', 'mage-eventpress' ),
								'desc'    => __( 'If you enable this then a Widget area will be registred and you can add any widgets from the Widget Menu. By default its disabled', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_clear_cart_after_checkout',
								'label'   => __( 'Clear Cart after Checkout Order Placed?', 'mage-eventpress' ),
								'desc'    => __( 'By default we clear the cart after order placed, But some payment gateway need cart data after order placed. If you get any warning after order placed please disabled this and try again. Unless please do not change this settings.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'enable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_manual_seat_Left_fix',
								'label'   => __( 'Manual Seat Left Fixing?', 'mage-eventpress' ),
								'desc'    => __( 'If you encounter the message "Sorry, There Are No Seats Available" after updating to version 4.3.0 or later, you may enable this setting. Otherwise, please keep it unchanged.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_fix_details_page_fatal_error',
								'label'   => __( 'Event Details Page Fatal Error Fix?', 'mage-eventpress' ),
								'desc'    => __( 'If you encounter a Fatal Error message on the event details page, you can enable this patch and check if the error persists. However, if there is no error, we recommend keeping the patch disabled', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_datepicker_format',
								'label'   => __( 'Date Picker Format', 'mage-eventpress' ),
								'desc'    => __( 'If you want to change Date Picker Format, please select format. Default is yy-mm-dd. <b>Text Based Date format will not works in other language except english. Is your website is not English language please do not use any text based datepicker.</b>', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yy-mm-dd'   => $current_date,
									'yy/mm/dd'   => date( 'Y/m/d', strtotime( $current_date ) ),
									// 'yy-dd-mm'      => date('Y-d-m',strtotime($current_date)),
									// 'yy/dd/mm'      => date('Y/d/m',strtotime($current_date)),
									'dd-mm-yy'   => date( 'd-m-Y', strtotime( $current_date ) ),
									'dd.mm.yy'   => date( 'd.m.Y', strtotime( $current_date ) ),
									// 'dd/mm/yy'      => date('d/m/Y',strtotime($current_date)),
									'mm-dd-yy'   => date( 'm-d-Y', strtotime( $current_date ) ),
									'mm/dd/yy'   => date( 'm/d/Y', strtotime( $current_date ) ),
									'd M , yy'   => date( 'j M , Y', strtotime( $current_date ) ),
									'D d M , yy' => date( 'D j M , Y', strtotime( $current_date ) ),
									'M d , yy'   => date( 'M  j, Y', strtotime( $current_date ) ),
									'D M d , yy' => date( 'D M  j, Y', strtotime( $current_date ) ),
									$lang        => $lang,
								)
							)
						)
					),
					'event_list_setting_sec'   => apply_filters( 'mep_settings_event_list_arr', array(
							array(
								'name'    => 'mep_event_price_show',
								'label'   => __( 'On/Off Event Price in List', 'mage-eventpress' ),
								'desc'    => __( 'This enables or disables the event price in the list. By default, it is enabled.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_price_show', 'general_setting_sec', 'event_list_setting_sec', 'yes' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_date_list_in_event_listing',
								'label'   => __( 'On/Off Multi Date List in Event listing Page', 'mage-eventpress' ),
								'desc'    => __( 'This feature enables or disables the full date list for multi-date events in the event listing page. By default, this feature is enabled.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_date_list_in_event_listing', 'general_setting_sec', 'event_list_setting_sec', 'yes' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_organizer_list',
								'label'   => __( 'Hide Organizer Section from list', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_organizer_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_location_list',
								'label'   => __( 'Hide Location Section from list', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_location_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time_list',
								'label'   => __( 'Hide Full Time Section from list', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_time_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_end_time_list',
								'label'   => __( 'Hide Only End Time Section from list', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_end_time_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_event_hover_btn',
								'label'   => __( 'Hide/Show Event Hover Book Now Button', 'mage-eventpress' ),
								'desc'    => __( 'Please select either \'Yes\' to hide or \'No\' to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_hide_event_hover_btn', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
                            array(
                                'name'    => 'mep_hide_event_list_msg',
                                'label'   => __( 'Hide/Show Event list Massage', 'mage-eventpress' ),
                                'desc'    => __( 'Please select either \'Yes\' to hide or \'No\' to display.', 'mage-eventpress' ),
                                'type'    => 'select',
                                'default' => mep_change_global_option_section( 'mep_hide_event_list_msg', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
                                'options' => array(
                                    'yes' => 'Yes',
                                    'no'  => 'No'
                                )
                            ),
						)
					),
					'single_event_setting_sec' => apply_filters( 'mep_settings_single_event_arr', array(
							array(
								'name'    => 'mep_enable_speaker_list',
								'label'   => __( 'On/Off Speaker List', 'mage-eventpress' ),
								'desc'    => __( 'Please select \'Yes\' to display the speaker list. By default, the speaker list is disabled.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_enable_speaker_list', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_product_cat_in_event',
								'label'   => __( 'On/Off Product Category in Event', 'mage-eventpress' ),
								'desc'    => __( 'Enabling this feature will allow you to assign a product category to the event edit page. If you have a product category-based coupon code that you want to use, you have to assign the event to the same product category. In order to enable this feature, please select \'Yes\'.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_show_product_cat_in_event', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_global_single_template',
								'label'   => __( 'Single Event Page Template', 'mage-eventpress' ),
								'desc'    => __( 'This change will impact the template for the single event details page.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'default-theme.php',
								'options' => mep_event_template_name()
							),
							array(
								'name'    => 'mep_event_product_type',
								'label'   => __( 'On/Off Shipping Method on event', 'mage-eventpress' ),
								'desc'    => __( 'The event product type in WooCommerce is set to virtual by default. If you change this type, you will need to save all of your events again.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_product_type', 'general_setting_sec', 'single_event_setting_sec', 'yes' ),
								'options' => array(
									'yes' => 'No',
									'no'  => 'Yes'
								)
							),
							array(
								'name'    => 'mep_event_hide_date_from_details',
								'label'   => __( 'Hide Event Date Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_date_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time_from_details',
								'label'   => __( 'Hide Event Time Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_time_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_location_from_details',
								'label'   => __( 'Hide Event Location Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_location_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_total_seat_from_details',
								'label'   => __( 'Hide Event Total Seats Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_total_seat_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_org_from_details',
								'label'   => __( 'Hide "Org By" Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_org_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_address_from_details',
								'label'   => __( 'Hide Event Address Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_address_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_event_schedule_details',
								'label'   => __( 'Hide Event Schedule Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_event_schedule_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_share_this_details',
								'label'   => __( 'Hide Event Share this Section from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_share_this_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_calendar_details',
								'label'   => __( 'Hide Add Calendar Button from Single Event Details page', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_calendar_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_description_title',
								'label'   => __( 'Hide Description Title', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_description_title', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_left_sidebar_title',
								'label'   => __( 'Hide Left Sidebar Title', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_left_sidebar_title', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time',
								'label'   => __( 'Hide Display Event Time Below Title', 'mage-eventpress' ),
								'desc'    => __( 'Please select "Yes" to hide or "No" to display.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_time', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
						)
					),
					'email_setting_sec'        => apply_filters( 'mep_settings_email_arr', array(
							array(
								'name'    => 'mep_email_sending_order_status',
								'label'   => __( 'Email Sent on order status', 'mage-eventpress' ),
								'desc'    => __( 'Please select when you would like the customer to receive an email confirming their order status event.', 'mage-eventpress' ),
								'type'    => 'multicheck',
								'default' => array( 'completed' => 'completed' ),
								'options' => array(
									'processing' => 'Processing',
									'completed'  => 'Completed'
								)
							),
							array(
								'name'    => 'mep_email_form_name',
								'label'   => __( 'Email From Name', 'mage-eventpress' ),
								'desc'    => __( 'Email From Name', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => get_bloginfo( 'name' )
							),
							array(
								'name'    => 'mep_email_form_email',
								'label'   => __( 'From Email', 'mage-eventpress' ),
								'desc'    => __( 'From Email', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => get_option( 'admin_email' )
							),
							array(
								'name'    => 'mep_email_subject',
								'label'   => __( 'Email Subject', 'mage-eventpress' ),
								'desc'    => __( 'Email Subject', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Event Notification'
							),
							array(
								'name'    => 'mep_confirmation_email_text',
								'label'   => __( 'Confirmation Email Text', 'mage-eventpress' ),
								'desc'    => __( 'Confirmation Email Text <b>Usable Dynamic tags:</b><br/> Attendee
                                        Name:<b>{name}</b><br/>
                                        Event Name: <b>{event}</b><br/>
                                        Ticket Type: <b>{ticket_type}</b><br/>
										Order ID: <b>{order_id}</b><br/>
                                        Event Date: <b>{event_date}</b><br/>
                                        Start Time: <b>{event_time}</b><br/>
                                        Full DateTime: <b>{event_datetime}</b><br/>
                                        Payment Method: <b>{payment_method}</b><br/>
                                        Amount Paid: <b>{amount_paid}</b>', 'mage-eventpress' ),
								'type'    => 'wysiwyg',
								'default' => 'Hi {name},<br><br>Thanks for joining the event.<br><br>Here are the event details:<br><br>Event Name: {event}<br><br>Ticket Type: {ticket_type}<br><br>Event Date: {event_date}<br><br>Start Time: {event_time}<br><br>Full DateTime: {event_datetime}<br><br>Payment Method: {payment_method}<br><br>Amount Paid: {amount_paid}<br><br>Thanks',
							),
							array(
								'name'    => 'mep_send_confirmation_to_billing_email',
								'label'   => __( 'Send Confirmation Email to Billing Email Address', 'mage-eventpress' ),
								'desc'    => __( 'By default Plugin sent the Event Confirmation Email to the Billing Email Address. If you want to turn off this you can disbale this setting.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'enable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							)
						)
					),
					'style_setting_sec'        => apply_filters( 'mep_settings_styling_arr', array(
							// Base Background & Text Color
							array(
								'name'    => 'mpev_primary_color',
								'label'   => __( 'Primary Color', 'mage-eventpress' ),
								'desc'    => __( 'Choose a basic color, it will change the icon background color & border color.', 'mage-eventpress' ),
								'type'    => 'color',
								'default' => '#6046ff'
							),
							array(
								'name'    => 'mpev_secondary_color',
								'label'   => __( 'Secondary Color', 'mage-eventpress' ),
								'desc'    => __( 'Choose a basic text color, it will change the text color.', 'mage-eventpress' ),
								'type'    => 'color',
								'default' => '#f1f5ff'
							),
						)
					),
					'icon_setting_sec'         => apply_filters( 'mep_settings_icon_arr', array(
							array(
								'name'    => 'mep_event_date_icon',
								'label'   => __( 'Choose Event Date Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event date icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-calendar',
							),
							array(
								'name'    => 'mep_event_time_icon',
								'label'   => __( 'Choose Event Time Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event time icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-clock',
							),
							array(
								'name'    => 'mep_event_location_icon',
								'label'   => __( 'Choose Event Location Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event location icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-marker',
							),
							array(
								'name'    => 'mep_event_organizer_icon',
								'label'   => __( 'Choose Event Organizer Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event organizer icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-badge',
							),
							array(
								'name'    => 'mep_event_location_list_icon',
								'label'   => __( 'Choose Event Sidebar Location List Icon', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event sidebar location list icon.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-arrow-circle-right',
							),
							array(
								'name'    => 'mep_event_ss_fb_icon',
								'label'   => __( 'Choose Event Social Share Icon for Facebook', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for facebook.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-facebook-f',
							),
							array(
								'name'    => 'mep_event_ss_twitter_icon',
								'label'   => __( 'Choose Event Social Share Icon for Twitter', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for twitter.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-twitter',
							),
							array(
								'name'    => 'mep_event_ss_linkedin_icon',
								'label'   => __( 'Choose Event Social Share Icon for Linkedin', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for linkedin.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-linkedin',
							),
							array(
								'name'    => 'mep_event_ss_whatsapp_icon',
								'label'   => __( 'Choose Event Social Share Icon for Whatsapp', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for whatsapp.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-whatsapp',
							),
							array(
								'name'    => 'mep_event_ss_email_icon',
								'label'   => __( 'Choose Event Social Share Icon for Email', 'mage-eventpress' ),
								'desc'    => __( 'Please choose event social share icon for email.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-envelope',
							),
						)
					),
					'carousel_setting_sec'     => apply_filters( 'mep_settings_carousel_arr', array(
							array(
								'name'    => 'mep_load_carousal_from_theme',
								'label'   => __( 'Load Owl Carousel From Theme', 'mage-eventpress' ),
								'desc'    => __( 'Select "Yes" only if your theme already includes Owl Carousel library. Select "No" (recommended) to let the plugin load its own Owl Carousel library. If carousel is not working, ensure this is set to "No".', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'no'  => __( 'No - Load from Plugin (Recommended)', 'mage-eventpress' ),
									'yes' => __( 'Yes - Load from Theme', 'mage-eventpress' )
								)
							),
							array(
								'name'    => 'mep_autoplay_carousal',
								'label'   => __( 'Auto Play', 'mage-eventpress' ),
								'desc'    => __( 'Please select Carousel will auto play or not.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'true'  => 'Yes',
									'false' => 'No'
								)
							),
							array(
								'name'    => 'mep_loop_carousal',
								'label'   => __( 'Infinite Loop', 'mage-eventpress' ),
								'desc'    => __( 'Please select Carousel will Infinite Loop or not.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'true'  => 'Yes',
									'false' => 'No'
								)
							),
							array(
								'name'    => 'mep_speed_carousal',
								'label'   => __( 'Carousel Auto Play Speed', 'mage-eventpress' ),
								'desc'    => __( 'Please Enter Carousel Auto Play Speed. Default is 5000', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => '5000'
							),
						)
					),
					'mp_slider_settings'       => array(
						array(
							'name'    => 'slider_type',
							'label'   => esc_html__( 'Slider Type', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Type Default Slider', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'slider',
							'options' => array(
								'slider'       => esc_html__( 'Slider', 'mage-eventpress' ),
								'single_image' => esc_html__( 'Post Thumbnail', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'slider_style',
							'label'   => esc_html__( 'Slider Style', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Style Default Style One', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'style_1',
							'options' => array(
								'style_1' => esc_html__( 'Style One', 'mage-eventpress' ),
								'style_2' => esc_html__( 'Style Two', 'mage-eventpress' ),
							)
						),
						array(
							'name'    => 'indicator_visible',
							'label'   => esc_html__( 'Slider Indicator Visible?', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Indicator Visible or Not? Default ON', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'indicator_type',
							'label'   => esc_html__( 'Slider Indicator Type', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Indicator Type Default Icon', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'icon',
							'options' => array(
								'icon'  => esc_html__( 'Icon Indicator', 'mage-eventpress' ),
								'image' => esc_html__( 'image Indicator', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'showcase_visible',
							'label'   => esc_html__( 'Slider Showcase Visible?', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Showcase Visible or Not? Default ON', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'showcase_position',
							'label'   => esc_html__( 'Slider Showcase Position', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Showcase Position Default Right', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'right',
							'options' => array(
								'top'    => esc_html__( 'At Top Position', 'mage-eventpress' ),
								'right'  => esc_html__( 'At Right Position', 'mage-eventpress' ),
								'bottom' => esc_html__( 'At Bottom Position', 'mage-eventpress' ),
								'left'   => esc_html__( 'At Left Position', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'popup_image_indicator',
							'label'   => esc_html__( 'Slider Popup Image Indicator', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Popup Indicator Image ON or Off? Default ON', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'popup_icon_indicator',
							'label'   => esc_html__( 'Slider Popup Icon Indicator', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Popup Indicator Icon ON or Off? Default ON', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'slider_height',
							'label'   => esc_html__( 'Slider height', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Slider Height', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'avg',
							'options' => array(
								'min' => esc_html__( 'Minimum', 'mage-eventpress' ),
								'avg' => esc_html__( 'Average', 'mage-eventpress' ),
								'max' => esc_html__( 'Maximum', 'mage-eventpress' )
							)
						)
					),
					'payment_setting_sec' => apply_filters( 'mep_settings_payment_arr', array(
							array(
								'name'  => 'payment_tabs_html',
								'type'  => 'html',
								'desc'  => (function() {
									$wc_active = MPWEM_Global_Function::has_woocommerce();
									$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
									$woo_btn_text = $is_installed ? __( "Activate WooCommerce Now", "mage-eventpress" ) : __( "Install & Activate Now", "mage-eventpress" );
									$html = '
									<div class="payment-sub-tabs-wrapper">
										<h2 class="nav-tab-wrapper payment-sub-tabs">
										<a href="#woocommerce-field" class="nav-tab nav-tab-active">' . __( "WooCommerce", "mage-eventpress" ) . '</a>
										<a href="#no-woocommerce-field" class="nav-tab">' . __( "Custom Payment", "mage-eventpress" ) . '</a>
										</h2>
									</div>
									';
									if ( ! $wc_active ) {
										$html .= '
										<div class="woocommerce-field">
											<div class="mpwem-woo-warning-notice" style="background: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; border-radius: var(--mpwem-radius); margin-bottom: 10px; margin-top: 15px;">
												<div style="display: flex; flex-direction: column; align-items: flex-start; gap: 15px;">
													<div style="width: 100%;">
														<strong style="display: block; font-size: 14px; margin-bottom: 5px;"><i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>' . __( "Notice: WooCommerce is Not Activated", "mage-eventpress" ) . '</strong>
														<span style="font-size: 13px; display: block;">' . __( "You can explore and manage ticket types, prices, and related settings here. However, you cannot save the event type as \"Ticket-Selling\" without WooCommerce. To actually use the \"Ticket-Selling\" event type and allow ticket sales, you must install and activate WooCommerce.", "mage-eventpress" ) . '</span>
													</div>
													<div>
														<button type="button" class="button button-primary mep-install-wc-trigger" style="white-space: nowrap;">' . $woo_btn_text . '</button>
													</div>
												</div>
											</div>
										</div>
										<style id="mep-woo-warning-style">tr.woocommerce-field { display: none !important; }</style>
										';
									}
									return $html;
								})()
							),
							array(
								'name'    => 'mep_enable_wc_payment',
								'label'   => __( 'Enable WooCommerce Payment', 'mage-eventpress' ),
								'desc'    => __( 'If enabled, WooCommerce payment gateway will be used for checkout.', 'mage-eventpress' ),
								'type'    => 'checkbox',
								'default' => 'on',
								'class'   => 'woocommerce-field woocommerce-main-toggle'
							),
							array(
								'name'    => 'mep_wc_add_to_cart_redirect',
								'label'   => __( 'After Adding to Cart, Redirect to', 'mage-eventpress' ),
								'desc'    => __( 'Select where to redirect after adding tickets to cart.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'checkout',
								'options' => array(
									'cart'     => __( 'Cart', 'mage-eventpress' ),
									'checkout' => __( 'Checkout', 'mage-eventpress' ),
								),
								'class'   => 'woocommerce-field'
							),
							array(
								'name'    => 'mep_wc_after_order_redirect',
								'label'   => __( 'After Confirming the Order, Redirect To', 'mage-eventpress' ),
								'desc'    => __( 'Select where to redirect after confirming the order.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'plugin_thankyou',
								'options' => array(
									'plugin_thankyou' => __( 'Plugin Thank You Page', 'mage-eventpress' ),
									'woo_thankyou'    => __( 'WooCommerce Thank You Page', 'mage-eventpress' ),
								),
								'class'   => 'woocommerce-field'
							),
							array(
								'name'    => 'mep_wc_require_login',
								'label'   => __( 'Require Account Login', 'mage-eventpress' ),
								'desc'    => __( 'Require login to purchase event tickets.', 'mage-eventpress' ),
								'type'    => 'checkbox',
								'default' => '',
								'class'   => 'woocommerce-field'
							),
							array(
								'name'    => 'mep_wc_show_billing_info',
								'label'   => __( 'Show Billing Info', 'mage-eventpress' ),
								'desc'    => __( 'Show billing info on WooCommerce checkout page.', 'mage-eventpress' ),
								'type'    => 'checkbox',
								'default' => '',
								'class'   => 'woocommerce-field'
							),
							array(
								'name'    => 'mep_wc_confirm_ticket_status',
								'label'   => __( 'Confirm Ticket Based on Payment Status', 'mage-eventpress' ),
								'desc'    => __( 'Select the payment statuses that will trigger ticket confirmation.', 'mage-eventpress' ),
								'type'    => 'multicheck',
								'default' => array( 'processing' => 'processing', 'completed' => 'completed' ),
								'options' => array(
									'pending'    => __( 'Pending payment', 'mage-eventpress' ),
									'processing' => __( 'Processing', 'mage-eventpress' ),
									'on-hold'    => __( 'On hold', 'mage-eventpress' ),
									'completed'  => __( 'Completed', 'mage-eventpress' ),
								),
								'class'   => 'woocommerce-field'
							),
							array(
								'name'    => 'payment_gateways_ui',
								'type'    => 'html',
								'class'   => 'no-woocommerce-field payment-gateways-container',
								'desc'    => '
<!-- PayPal Card -->
<div class="gateway-card paypal-card">
    <div class="gateway-header">
        <h3>
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right:4px;">
                <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z" fill="#003087"/>
                <path d="M11.5 7.1c.05.27.01.59-.09.91-.98 5.05-4.35 6.79-8.65 6.79H4.95l-1.12 7.11a.64.64 0 0 0 .63.74h4.6a.64.64 0 0 0 .63-.54l.87-5.55a.64.64 0 0 1 .63-.54h1.08c3.5 0 6.23-1.42 7.03-5.52.2-.99.23-1.89.09-2.65-.48-2.6-2.58-3.41-5.63-3.41h-2.22z" fill="#0079C1"/>
                <path d="M11.5 7.1c-.02-.13-.05-.27-.08-.41C10.3 5.4 8.3 4.86 5.73 4.86H3.54l-1.5 9.54h2.72c.52 0 .97-.38 1.05-.9l.87-5.5c.08-.52.53-.9.1-.9h2.19c3.5 0 6.23-1.42 7.03-5.52-.06.32-.14.64-.09.91z" fill="#00457C"/>
            </svg>
            ' . __( "PayPal", "mage-eventpress" ) . '
        </h3>
        <span class="gateway-status ' . ($pp_enable ? "active" : "") . '" style="position:absolute;left:50%;transform:translateX(-50%);font-size:13px;font-weight:600;">' . ($pp_enable ? __("Enabled", "mage-eventpress") : __("Disabled", "mage-eventpress")) . '</span>
        <button type="button" class="button button-secondary gateway-configure-btn" id="mep-paypal-configure-btn">' . __( "Configure", "mage-eventpress" ) . '</button>
    </div>
</div>

<!-- Stripe Card -->
<div class="gateway-card stripe-card">
    <div class="gateway-header">
        <h3>
            <svg width="36" height="36" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="margin-right:4px;">
                <path fill="#6772E5" d="M14.07 15.11c-1.85-.43-2.61-.79-2.61-1.63 0-.79.75-1.33 1.95-1.33 1.34 0 2.87.41 4.31 1.09V8.65c-1.39-.56-2.93-.84-4.52-.84-3.8 0-6.66 1.96-6.66 5.25 0 3.73 3.32 4.96 6.03 5.61 2.05.49 2.8.92 2.8 1.8 0 .86-.87 1.48-2.3 1.48-1.57 0-3.37-.53-5.06-1.54v4.75c1.67.75 3.59 1.13 5.51 1.13 4.13 0 7-2 7-5.34-.01-3.6-3.6-4.41-6.45-5.84z"/>
            </svg>
            ' . __( "Stripe", "mage-eventpress" ) . '
        </h3>
        <span class="gateway-status ' . ($st_enable ? "active" : "") . '" style="position:absolute;left:50%;transform:translateX(-50%);font-size:13px;font-weight:600;">' . ($st_enable ? __("Enabled", "mage-eventpress") : __("Disabled", "mage-eventpress")) . '</span>
        <button type="button" class="button button-secondary gateway-configure-btn" id="mep-stripe-configure-btn">' . __( "Configure", "mage-eventpress" ) . '</button>
    </div>
</div>
'
							)
						)
					),
				);

				return apply_filters( 'mep_settings_sec_fields', $settings_fields );
			}

			function plugin_page() {
				$label = get_plugin_data( __FILE__ )['Name'];
				?>
                <div class="wrap">
                    <div class="mp_settings_panel_header">
                        <h3>
							<?php echo esc_html( $label . esc_html__( ' Global Settings', 'mage-eventpress' ) ); ?>
                        </h3>
                    </div>
                    <div class="mp_settings_panel">
						<?php $this->settings_api->show_navigation(); ?>
						<?php $this->settings_api->show_forms(); ?>
                    </div>
                </div>
				<?php
			}

			/**
			 * Get all the pages
			 *
			 * @return array page names with key value pairs
			 */
			function get_pages() {
				$pages         = get_pages();
				$pages_options = array();
				if ( $pages ) {
					foreach ( $pages as $page ) {
						$pages_options[ $page->ID ] = $page->post_title;
					}
				}

				return $pages_options;
			}
		}
	endif;
	$settings = new MAGE_Events_Setting_Controls();
	function mep_get_option( $option, $section, $default = '' ) {
		$options = get_option( $section );
		if ( isset( $options[ $option ] ) ) {
			if ( is_array( $options[ $option ] ) ) {
				if ( ! empty( $options[ $option ] ) ) {
					return $options[ $option ];
				} else {
					return $default;
				}
			} else {
				if ( ! empty( $options[ $option ] ) ) {
					// return $options[$option];
					return wp_kses_post( $options[ $option ] );
				} else {
					return $default;
				}
			}
		}
		if ( is_array( $default ) ) {
			return $default;
		} else {
			return wp_kses_post( $default );
		}
	}
	add_action( 'wsa_form_bottom_mep_settings_licensing', 'mep_licensing_page', 5 );
	function mep_licensing_page( $form ) {
		?>
        <div class='mep-licensing-page'>
            <h3>Event Manager For Woocommerce Licensing</h3>
            <p>Thank you for using our Event Manager for WooCommerce plugin! This plugin is free to use and no license is required. However, we do have some additional add-ons which enhance the features and functionality of this plugin. If you have any of these add-ons, you will need to enter a valid license key below in order to continue using them. </p>
            <div class="mep_licensae_info"></div>
            <table class='wp-list-table widefat striped posts mep-licensing-table'>
                <thead>
                <tr>
                    <th>Plugin Name</th>
                    <th width=10%>Order No</th>
                    <th width=15%>Expire on</th>
                    <th width=30%>License Key</th>
                    <th width=10%>Status</th>
                    <th width=10%>Action</th>
                </tr>
                </thead>
                <tbody>
				<?php do_action( 'mep_license_page_addon_list' ); ?>
                </tbody>
            </table>
        </div>
		<?php
	}
	add_action( 'wsa_form_bottom_mep_settings_templates', 'mep_settings_template_page', 5 );
	function mep_settings_template_page( $form ) {
		?>
        <div class='mep-licensing-page'>
            <h3>Ready Templates For Event Details Page.</h3>
            <div class="mep_licensae_info"></div>
            <div class="mep-template-lists">
				<?php
					$url  = 'https://vaincode.com/update/template/template.json';
					$curl = curl_init();
					curl_setopt( $curl, CURLOPT_URL, $url );
					curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $curl, CURLOPT_HEADER, false );
					$data = curl_exec( $curl );
					curl_close( $curl );
					$obj = json_decode( $data, true );
					// print_r($data);
					if ( is_array( $obj ) && sizeof( $obj ) > 0 ) {
						?>
                        <div class="mep_ready_template_sec">
                            <ul class="mep_ready_template_list">
								<?php
									foreach ( $obj as $list ) {
										$name         = $list['name'];
										$banner       = $list['banner'];
										$url          = $list['url'];
										$type         = $list['type'];
										$editor       = $list['editor'];
										$preview      = $list['preview'];
										$name_slug    = sanitize_title( $name );
										$count_import = get_option( 'mep_import_template_' . $name_slug ) ? get_option( 'mep_import_template_' . $name_slug ) : 0;
										?>
                                        <li>
                                            <div class="template-thumb"><img src="<?php echo esc_url( $banner ); ?>" alt=""></div>
                                            <h3><?php echo esc_html( $name ); ?></h3>
											<?php if ( $count_import > 0 ) { ?>
                                                <p class="mep-template-import-count"> Imported <?php echo esc_html( $count_import ); ?> times</p>
												<?php
											}
												if ( did_action( 'elementor/loaded' ) && $editor == 'elm' ) {
													?>
                                                    <button class='import_template' data-file="<?php echo esc_attr( $url ); ?>" data-name="<?php echo esc_attr( $name ); ?>" data-editor="<?php echo esc_attr( $editor ); ?>" data-type="<?php echo esc_attr( $type ); ?>">Import</button>
													<?php
												} else {
													?>
                                                    <p class='mep-msg mep-msg-warning'>Elementor Not Installed</p>
												<?php } ?>
                                            <a href="<?php echo esc_url( $preview ); ?>" class='preview-btn btn' target='_blank'>Preview</a>
                                        </li>
									<?php } ?>
                            </ul>
                        </div>
					<?php } ?>
            </div>
            <script>
                (function ($) {
                    'use strict';
                    jQuery('.import_template').on('click', function () {
                        if (confirm('Are You Sure to Import this Template ? \n\n 1. Ok : To Import . \n 2. Cancel : To Cancel .')) {
                            let file = jQuery(this).data('file');
                            let type = jQuery(this).data('type');
                            let editor = jQuery(this).data('editor');
                            let name = jQuery(this).data('name');
                            jQuery.ajax({
                                type: 'POST',
                                url: mpwem_ajax_url,
                                data: {
                                    "action": "mep_import_ajax_template",
                                    "nonce": '<?php echo wp_create_nonce( 'mep-ajax-import-template-nonce' ); ?>',
                                    "file": file,
                                    "editor": editor,
                                    "name": name,
                                    "type": type
                                },
                                beforeSend: function () {
                                    jQuery('.mep_licensae_info').html('<h5 class="mep-msg mep-msg-process">Please wait.. Importing Template..</h5>');
                                },
                                success: function (data) {
                                    jQuery('.mep_licensae_info').html(data);
                                    window.location.reload();
                                }
                            });
                        } else {
                            return false;
                        }
                        return false;
                    });
                })(jQuery);
            </script>
        </div>
		<?php
	}
