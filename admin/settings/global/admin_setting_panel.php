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
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_modern_settings_assets' ) );
				add_action( 'admin_footer', array( $this, 'payment_tabs_script' ) );
				add_action( 'wp_ajax_mep_install_activate_wc', array( $this, 'ajax_install_activate_wc' ) );
				add_action( 'wp_ajax_mep_save_gateway_settings', array( $this, 'ajax_save_gateway_settings' ) );
				// Inject WooCommerce warning + modal in footer so it's outside hidden group divs
				add_action( 'admin_footer', array( $this, 'render_wc_warning_banner' ) );
				// Inject PayPal + Stripe config modals in footer
				add_action( 'admin_footer', array( $this, 'render_gateway_modals' ) );
				add_action( 'wp_ajax_mep_save_payment_settings_modal', array( $this, 'ajax_save_payment_settings_modal' ) );
			}

			/**
			 * Assets for the modern global settings shell (bus-plugin style).
			 */
			function enqueue_modern_settings_assets( $hook ) {
				if ( $hook !== 'mep_events_page_mep_event_settings_page' ) {
					return;
				}
				$css = MPWEM_PLUGIN_DIR . '/assets/admin/mep-global-settings.css';
				$js  = MPWEM_PLUGIN_DIR . '/assets/admin/mep-global-settings.js';
				wp_enqueue_style(
					'mep-global-settings',
					MPWEM_PLUGIN_URL . '/assets/admin/mep-global-settings.css',
					array(),
					file_exists( $css ) ? filemtime( $css ) : MPWEM_PLUGIN_VERSION
				);
				wp_enqueue_script(
					'mep-global-settings',
					MPWEM_PLUGIN_URL . '/assets/admin/mep-global-settings.js',
					array( 'jquery', 'wp-color-picker' ),
					file_exists( $js ) ? filemtime( $js ) : MPWEM_PLUGIN_VERSION,
					true
				);
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_media();
			}

			function render_wc_warning_banner() {
			$screen = get_current_screen();
			if ( ! $screen || $screen->id !== 'mep_events_page_mep_event_settings_page' ) {
				return;
			}
			if ( class_exists( 'WooCommerce' ) ) {
				return;
			}

			$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
			$modal_desc   = $is_installed
				? __( 'WooCommerce is already installed but not active. Click the button below to activate it right now.', 'mage-eventpress' )
				: __( 'WooCommerce is required to process payments. We will securely download, install, and activate it for you right now.', 'mage-eventpress' );
			$modal_btn    = $is_installed
				? __( 'Activate WooCommerce Now', 'mage-eventpress' )
				: __( 'Install &amp; Activate Now', 'mage-eventpress' );
			?>
			<div id="mep-wc-install-modal" style="display:none; position:fixed; z-index:999999; inset:0; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
				<div style="background:#fff; border-radius:12px; width:520px; max-width:92vw; box-shadow:0 10px 40px rgba(0,0,0,0.35); overflow:hidden;">
					<div style="padding:18px 24px; border-bottom:1px solid #e2e4e7; display:flex; justify-content:space-between; align-items:center; background:#f8f9fa;">
						<h3 style="margin:0; font-size:17px; color:#2c3338; display:flex; align-items:center; gap:8px;">
							<span class="dashicons dashicons-plugins-checked" style="font-size:20px; color:#2271b1;"></span>
							<?php esc_html_e( 'Set Up WooCommerce', 'mage-eventpress' ); ?>
						</h3>
						<button type="button" id="mep-wc-install-modal-close" style="background:none; border:none; font-size:24px; line-height:1; cursor:pointer; color:#666; padding:0;">&times;</button>
					</div>
					<div style="padding:24px;">
						<div id="mep-wc-modal-info">
							<p style="margin:0 0 18px; font-size:14px; color:#3c434a; line-height:1.6;">
								<?php echo esc_html( $modal_desc ); ?>
							</p>
							<button type="button" id="mep-wc-modal-action-btn" class="button button-primary" style="white-space:nowrap; padding:6px 18px;">
								<?php echo wp_kses_post( $modal_btn ); ?>
							</button>
						</div>
						<div id="mep-wc-modal-progress" style="display:none;">
							<div style="width:100%; height:8px; background:#f0f0f1; border-radius:100px; overflow:hidden; margin-bottom:10px;">
								<div id="mep-wc-modal-progress-fill" style="height:100%; width:0%; border-radius:100px; background:linear-gradient(90deg,#7b5ea7,#9b72cf); transition:width 0.5s cubic-bezier(0.16,1,0.3,1);"></div>
							</div>
							<p id="mep-wc-modal-status-text" style="font-size:13px; color:#50575e; margin:0; text-align:center; min-height:20px;"></p>
						</div>
					</div>
				</div>
			</div>

			<script>
			jQuery(document).ready(function($) {
				var mepWcIsInstalled = <?php echo $is_installed ? 'true' : 'false'; ?>;
				var mepWcNonce       = '<?php echo esc_js( wp_create_nonce( 'mep_install_wc' ) ); ?>';

				// Open modal when the warning button is clicked
				$(document).on('click', '.mep-install-wc-trigger', function(e) {
					e.preventDefault();
					$('#mep-wc-install-modal').css('display', 'flex').hide().fadeIn(200);
				});

				// Close modal via × button or backdrop click
				$('#mep-wc-install-modal-close').on('click', function() {
					$('#mep-wc-install-modal').fadeOut(200);
				});
				$(document).on('click', '#mep-wc-install-modal', function(e) {
					if ($(e.target).is('#mep-wc-install-modal')) {
						$(this).fadeOut(200);
					}
				});

				// Action button inside modal
				$('#mep-wc-modal-action-btn').on('click', function() {
					var $info     = $('#mep-wc-modal-info');
					var $progress = $('#mep-wc-modal-progress');
					var $fill     = $('#mep-wc-modal-progress-fill');
					var $status   = $('#mep-wc-modal-status-text');

					$info.hide();
					$fill.css('width', '0%');
					$progress.fadeIn(200);

					var texts = mepWcIsInstalled
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

					var duration  = mepWcIsInstalled ? 3000 : 15000;
					var startTime = Date.now();
					var isDone    = false;
					var frameId;

					$status.text(texts[0]);

					function animateBar() {
						if (isDone) return;
						var elapsed = Date.now() - startTime;
						var raw     = Math.min(elapsed / duration, 1);
						var eased   = raw * (2 - raw);
						var pct     = eased * 95;
						$fill.css('width', pct + '%');
						var idx = Math.min(Math.floor((pct / 95) * texts.length), texts.length - 1);
						$status.text(texts[idx] + ' ' + Math.round(pct) + '%');
						if (pct < 95) frameId = requestAnimationFrame(animateBar);
					}
					frameId = requestAnimationFrame(animateBar);

					$.ajax({
						url:  ajaxurl,
						type: 'POST',
						data: { action: 'mep_install_activate_wc', nonce: mepWcNonce },
						success: function(response) {
							var minWait  = mepWcIsInstalled ? 1500 : 3000;
							var leftover = Math.max(0, minWait - (Date.now() - startTime));
							setTimeout(function() {
								isDone = true;
								cancelAnimationFrame(frameId);
								$fill.css('width', '100%');
								if (response.success) {
									$status.css('color', '#039855').text(<?php echo wp_json_encode( __( 'Successfully Activated! 100%', 'mage-eventpress' ) ); ?>);
									setTimeout(function() {
										$('#mep-wc-install-modal').fadeOut(300);
										// Remove the style rule that was hiding woocommerce settings rows
										$('#mep-woo-warning-style').remove();
										// Slide up the warning notice in the tab
										$('div.woocommerce-field').slideUp(300);
										// Reveal the WooCommerce settings rows and save button
										$('tr.woocommerce-field').fadeIn(200);
										$('#payment_setting_sec .submit').show();
									}, 1200);
								} else {
									$status.css('color', '#d92d20').text(<?php echo wp_json_encode( __( 'Error: ', 'mage-eventpress' ) ); ?> + (response.data || 'Unknown error'));
									setTimeout(function() {
										$progress.hide();
										$info.show();
									}, 5000);
								}
							}, leftover);
						},
						error: function() {
							isDone = true;
							cancelAnimationFrame(frameId);
							$fill.css('width', '100%');
							$status.css('color', '#d92d20').text(<?php echo wp_json_encode( __( 'A network error occurred. Please try again.', 'mage-eventpress' ) ); ?>);
							setTimeout(function() {
								$progress.hide();
								$info.show();
							}, 5000);
						}
					});
				});
			});
			</script>
			<?php
		}

		function render_gateway_modals() {
			// These modals expose live gateway credentials and a save nonce, so
			// they are limited to the capability that owns the Payment settings
			// page. The event screens only require edit_posts.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
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
			$off_enabled = ! empty( $opts['mep_offline_enable'] ) && $opts['mep_offline_enable'] === 'on';
			$off_label   = esc_attr( $opts['mep_offline_label'] ?? __( 'Offline Payment', 'mage-eventpress' ) );
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

			<!-- Offline Payment Config Modal -->
			<div id="mep-offline-modal" class="mep-gw-modal" style="display:none;">
				<div class="mep-gw-modal-box">
					<div class="mep-gw-modal-header" style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);">
						<h2>
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3 19h18a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/>
								<path d="M2 10h20M6 14h4" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
							</svg>
							<?php esc_html_e( 'Offline Payment Configuration', 'mage-eventpress' ); ?>
						</h2>
						<button type="button" class="mep-gw-modal-close">&times;</button>
					</div>
					<div class="mep-gw-modal-body">
						<!-- Enable Offline Payment -->
						<div class="mep-gw-toggle-row">
							<div>
								<div class="mep-gw-toggle-label"><?php esc_html_e( 'Enable Offline Payment', 'mage-eventpress' ); ?></div>
								<div class="mep-gw-toggle-sub"><?php esc_html_e( 'Let customers pay offline (bank transfer, cash, pay at venue).', 'mage-eventpress' ); ?></div>
							</div>
							<label class="mep-gw-switch">
								<input type="checkbox" data-field="mep_offline_enable" <?php checked( $off_enabled ); ?>>
								<span class="mep-gw-slider"></span>
							</label>
						</div>
						<hr class="mep-gw-divider">
						<div class="mep-gw-field">
							<label class="mep-gw-label"><?php esc_html_e( 'Payment Label', 'mage-eventpress' ); ?></label>
							<input type="text" data-field="mep_offline_label" value="<?php echo $off_label; ?>" placeholder="<?php esc_attr_e( 'e.g. Pay at Venue / Bank Transfer', 'mage-eventpress' ); ?>">
							<p style="margin:8px 0 0; font-size:12px; color:#6b7280;"><?php esc_html_e( 'This label is shown to customers on the frontend payment page.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-gw-modal-footer">
						<button type="button" class="mep-gw-save-btn" data-gateway="offline" style="background: linear-gradient(135deg,#0f766e,#115e59);">
							<?php esc_html_e( 'Save Offline Settings', 'mage-eventpress' ); ?>
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
				if ($("#mep-progress-styles").length === 0) {
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
					$("head").append("<style id=\"mep-progress-styles\">" + styles + "</style>");
				}
				// Gateway Configure Buttons — open respective modals
				$(document).on('click', '#mep-paypal-configure-btn', function(e) {
					e.preventDefault();
					$('#mep-paypal-modal').css('display','flex').hide().fadeIn(220);
				});
				$(document).on('click', '#mep-stripe-configure-btn', function(e) {
					e.preventDefault();
					$('#mep-stripe-modal').css('display','flex').hide().fadeIn(220);
				});
				$(document).on('click', '#mep-offline-configure-btn', function(e) {
					e.preventDefault();
					$('#mep-offline-modal').css('display','flex').hide().fadeIn(220);
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

								// Reflect the change on the Event Edit → Payment status banner
								// (when configured from inside the event modal) and enforce
								// mutual exclusivity: enabling a custom gateway turns the
								// WooCommerce payment toggle off.
								var $statusRow = $('.mpwem-payment-status');
								if ($statusRow.length) {
									var gwOn = fields['mep_' + gateway + '_enable'] === 'on';
									$statusRow.attr('data-' + gateway, gwOn ? '1' : '0').data(gateway, gwOn ? 1 : 0);
									if (gateway === 'offline' && fields['mep_offline_label']) {
										$statusRow.attr('data-offline-label', fields['mep_offline_label']).data('offline-label', fields['mep_offline_label']);
									}
									if (gwOn) {
										$('#mep_modal_enable_wc').prop('checked', false);
										$('.mep-modal-wc-fields').stop(true, true).slideUp(200);
										$statusRow.attr('data-woo-enabled', '0').data('woo-enabled', 0);
										$statusRow.attr('data-option-set', '1').data('option-set', 1);
									}
									$(document).trigger('mep:gateways-refresh');
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
			// This writes the site-wide payment_setting_sec option (gateway
			// credentials and enable flags), so it requires the same capability as
			// the Payment settings page that owns those values. The Configure
			// modals are only rendered for that capability as well.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'Permission denied.', 'mage-eventpress' ) );
			}
			$gateway  = sanitize_key( $_POST['gateway'] ?? '' );
			$fields   = isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : array();
			$existing = get_option( 'payment_setting_sec', array() );
			if ( ! is_array( $existing ) ) {
				$existing = array();
			}

			$allowed = array(
				'paypal'  => array( 'mep_paypal_enable', 'mep_paypal_sandbox', 'mep_paypal_client_id', 'mep_paypal_secret' ),
				'stripe'  => array( 'mep_stripe_enable', 'mep_stripe_sandbox', 'mep_stripe_test_pub', 'mep_stripe_test_sec', 'mep_stripe_live_pub', 'mep_stripe_live_sec' ),
				'offline' => array( 'mep_offline_enable', 'mep_offline_label' ),
			);

			if ( ! array_key_exists( $gateway, $allowed ) ) {
				wp_send_json_error( __( 'Invalid gateway.', 'mage-eventpress' ) );
			}

			foreach ( $allowed[ $gateway ] as $key ) {
				$val = isset( $fields[ $key ] ) ? $fields[ $key ] : 'off';
				// Toggles are on/off; other fields are text
				if ( in_array( $key, array( 'mep_paypal_enable', 'mep_paypal_sandbox', 'mep_stripe_enable', 'mep_stripe_sandbox', 'mep_offline_enable' ), true ) ) {
					$existing[ $key ] = ( $val === 'on' ) ? 'on' : 'off';
				} else {
					$existing[ $key ] = sanitize_text_field( $val );
				}
			}

			// Custom payment is mutually exclusive with the WooCommerce checkout.
			// Switching a custom gateway on turns the WooCommerce payment master
			// toggle off, so the banner and checkout never advertise both at once.
			$enable_key = 'mep_' . $gateway . '_enable';
			if ( isset( $existing[ $enable_key ] ) && 'on' === $existing[ $enable_key ] ) {
				$existing['mep_enable_wc_payment'] = 'off';
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
/* Stripe is no longer the last card; restore its spacing above the Offline card. */
.gateway-card.stripe-card { margin-bottom: 10px; }
.gateway-card.offline-card {
    background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
    border: none;
    color: #fff;
    margin-bottom: 0;
}
.gateway-card.offline-card .gateway-header { background: transparent; border-bottom: 1px solid rgba(255,255,255,0.15); }
.gateway-card.offline-card .gateway-header h3 { color: #fff; }
.gateway-card.offline-card .gateway-header svg path { stroke: #fff !important; }
.gateway-card.offline-card .gateway-status { background: rgba(255,255,255,0.2); color: #fff; }
.gateway-card.offline-card .gateway-status.active { background: #fff; color: #0f766e; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.gateway-card.offline-card .gateway-configure-btn { color: #0f766e !important; background: #fff !important; border: none !important; font-weight:600 !important; border-radius: 6px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important; }
</style>

				<style> /* Payment settings accordions (WooCommerce sub-tab) */
					tr.mep-acc-header > td.mep-acc-header-cell {
						padding: 0 !important;
					}
					tr.mep-acc-header .mep-acc-bar {
						display: flex;
						align-items: center;
						justify-content: space-between;
						gap: 10px;
						cursor: pointer;
						user-select: none;
						background: #f6f7f7;
						border: 1px solid #dcdcde;
						border-radius: 8px;
						padding: 12px 16px;
						margin: 14px 4px 4px;
						transition: background 0.2s ease, border-color 0.2s ease;
					}
					tr.mep-acc-header .mep-acc-bar:hover {
						background: #f0f0f1;
						border-color: #c3c4c7;
					}
					tr.mep-acc-header.open .mep-acc-bar {
						background: #eef5fb;
						border-color: #2271b1;
					}
					tr.mep-acc-header .mep-acc-title {
						display: flex;
						align-items: center;
						gap: 8px;
						font-size: 14px;
						font-weight: 600;
						color: #1d2327;
						margin: 0;
					}
					tr.mep-acc-header.open .mep-acc-title {
						color: #2271b1;
					}
					tr.mep-acc-header .mep-acc-arrow {
						transition: transform 0.2s ease;
						color: #50575e;
						line-height: 1;
					}
					tr.mep-acc-header.open .mep-acc-arrow {
						transform: rotate(180deg);
						color: #2271b1;
					}
					/* The accordion header already shows this title; avoid duplicating it
					   inside the manager. Keep the bar (it holds the "Open in WooCommerce" link). */
					tr.wc-payment-methods-field .mep-wc-pm-heading { display: none; }
					tr.wc-payment-methods-field .mep-wc-payment-manager { margin-top: 4px; padding: 10px }
				</style>

				<script>
					jQuery(document).ready(function($) {
						// Modern Payment hub handles its own UI — skip legacy table tabs/accordions.
						if ($('.mep-pay').length) {
							return;
						}
						var wc_active = <?php echo MPWEM_Global_Function::has_woocommerce() ? 'true' : 'false'; ?>;
						if (!wc_active) {
							$('head').append('<style id="mep-woo-warning-style">tr.woocommerce-field { display: none !important; }</style>');
						}

						if ($('.payment-sub-tabs').length > 0) {
							// WooCommerce Setting Toggle Logic
							function toggleWcSettings() {
								var isChecked = $('#wpuf-payment_setting_sec\\[mep_enable_wc_payment\\]').is(':checked');
								var $wcFields = $('tr.woocommerce-field').not('tr.woocommerce-main-toggle');
								if (isChecked) {
									$wcFields.stop(true, true).fadeIn(200);
									refreshAccordions();
								} else {
									$wcFields.hide();
								}
							}

							// --- WooCommerce sub-tab accordions: Payment Methods (open) + Additional Settings (collapsed) ---
							var $methodsRows    = $('tr.wc-payment-methods-field');
							var $additionalRows = $('tr.wc-additional-field');
							var $methodsHeader  = $();
							var $additionalHeader = $();

							function buildAccordionHeader(extraClass, title, isOpen) {
								return $(
									'<tr class="woocommerce-field mep-acc-header ' + extraClass + (isOpen ? ' open' : '') + '">' +
										'<td colspan="2" class="mep-acc-header-cell">' +
											'<div class="mep-acc-bar">' +
												'<span class="mep-acc-title">' + title + '</span>' +
												'<span class="mep-acc-arrow dashicons dashicons-arrow-down-alt2"></span>' +
											'</div>' +
										'</td>' +
									'</tr>'
								);
							}

							function refreshAccordions() {
								if ( ! $methodsHeader.length ) { return; }
								if ( $methodsHeader.hasClass('open') ) { $methodsRows.show(); } else { $methodsRows.hide(); }
								if ( $additionalHeader.hasClass('open') ) { $additionalRows.show(); } else { $additionalRows.hide(); }
							}

							if ( $methodsRows.length || $additionalRows.length ) {
								var $toggleRow = $('tr.woocommerce-main-toggle');
								$methodsHeader    = buildAccordionHeader('mep-acc-methods', '<?php echo esc_js( __( 'WooCommerce Payment Methods', 'mage-eventpress' ) ); ?>', true);
								$additionalHeader = buildAccordionHeader('mep-acc-additional', '<?php echo esc_js( __( 'Additional Settings', 'mage-eventpress' ) ); ?>', false);

								// Make the payment-methods row span the full table width. Without this,
								// its hidden <th> + width:100% <td> distort the shared column widths and
								// squeeze sibling rows (e.g. the Enable toggle above). colspan=2 spans both
								// columns cleanly — same pattern as the accordion header bars.
								$methodsRows.each(function () {
									var $r = $(this);
									$r.children('th').remove();
									$r.children('td').attr('colspan', 2);
								});

								// Re-order: toggle -> [Payment Methods header + rows] -> [Additional Settings header + rows]
								$methodsRows.detach();
								$additionalRows.detach();
								$toggleRow.after($methodsHeader);
								$methodsHeader.after($methodsRows);
								$methodsRows.last().after($additionalHeader);
								$additionalHeader.after($additionalRows);

								// Exclusive toggle: opening one closes the other; only one ever expanded.
								$methodsHeader.find('.mep-acc-bar').on('click', function() {
									var willOpen = ! $methodsHeader.hasClass('open');
									$methodsHeader.toggleClass('open', willOpen);
									if ( willOpen ) { $additionalHeader.removeClass('open'); }
									refreshAccordions();
								});
								$additionalHeader.find('.mep-acc-bar').on('click', function() {
									var willOpen = ! $additionalHeader.hasClass('open');
									$additionalHeader.toggleClass('open', willOpen);
									if ( willOpen ) { $methodsHeader.removeClass('open'); }
									refreshAccordions();
								});
							}

							$('#wpuf-payment_setting_sec\\[mep_enable_wc_payment\\]').on('change', toggleWcSettings);
							function updateTabs() {
								var activeTabId = $(".payment-sub-tabs .nav-tab-active").attr("href").replace("#", "");
								$("tr.woocommerce-field, div.woocommerce-field, tr.no-woocommerce-field").hide();
								
								// Show save button on Custom Payment tab (needed for Booking Confirmation Page)
								if (activeTabId === 'no-woocommerce-field') {
									$('#payment_setting_sec .submit').show();
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

				<?php
			}


			function ajax_save_payment_settings_modal() {
				check_ajax_referer( 'mep_save_payment_settings', 'nonce' );

				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( __( 'Permission denied.', 'mage-eventpress' ) );
				}

				$payment_settings = get_option( 'payment_setting_sec', array() );
				if ( ! is_array( $payment_settings ) ) {
					$payment_settings = array();
				}
				$payment_settings['mep_enable_wc_payment'] = isset( $_POST['mep_enable_wc_payment'] ) ? sanitize_text_field( $_POST['mep_enable_wc_payment'] ) : 'off';
				// mep_paypal_enable / mep_stripe_enable are managed solely by each gateway's
				// Configure modal (its own enable toggle). The Custom Payment tab no longer
				// has those checkboxes, so we must NOT touch those keys here or every save
				// would disable the gateways.
				//
				// Exception: WooCommerce and the custom (native checkout) gateways are
				// mutually exclusive. When WooCommerce payment is turned ON, switch the
				// custom gateways OFF so the banner/checkout never advertise both.
				if ( 'on' === $payment_settings['mep_enable_wc_payment'] ) {
					$payment_settings['mep_paypal_enable']  = 'off';
					$payment_settings['mep_stripe_enable']  = 'off';
					$payment_settings['mep_offline_enable'] = 'off';
				}

				$payment_settings['mep_wc_add_to_cart_redirect'] = isset( $_POST['mep_wc_add_to_cart_redirect'] ) ? sanitize_text_field( $_POST['mep_wc_add_to_cart_redirect'] ) : 'checkout';
				$payment_settings['mep_wc_after_order_redirect'] = isset( $_POST['mep_wc_after_order_redirect'] ) ? sanitize_text_field( $_POST['mep_wc_after_order_redirect'] ) : 'plugin_thankyou';
				$payment_settings['mep_wc_require_login'] = isset( $_POST['mep_wc_require_login'] ) ? sanitize_text_field( $_POST['mep_wc_require_login'] ) : '';
				$payment_settings['mep_wc_show_billing_info'] = isset( $_POST['mep_wc_show_billing_info'] ) ? sanitize_text_field( $_POST['mep_wc_show_billing_info'] ) : '';
				$payment_settings['mep_confirmation_page_id'] = isset( $_POST['mep_confirmation_page_id'] ) ? absint( $_POST['mep_confirmation_page_id'] ) : 0;
				
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
				// Preserve PayPal/Stripe keys when the Settings API saves payment_setting_sec.
				// Those fields are managed via their own AJAX modals and are never part of
				// the settings form, so without this they get wiped on every "Save Changes".
				//
				// IMPORTANT: only restore a key when it is ABSENT from the incoming value.
				// admin-ajax.php fires admin_init (and thus this filter) before the ajax
				// action runs, so the gateway modals' own save (which DOES include these
				// keys with new values) must not be clobbered back to the old values.
				//
				// This filter is cheap and is registered on EVERY admin request because the
				// gateway modals save payment_setting_sec via update_option (bypassing
				// options.php), so it must be active regardless of the page gate below.
				add_filter( 'pre_update_option_payment_setting_sec', function( $new_value, $old_value ) {
					$protected_keys = array(
						'mep_paypal_enable', 'mep_paypal_sandbox', 'mep_paypal_client_id', 'mep_paypal_secret',
						'mep_stripe_enable', 'mep_stripe_sandbox',
						'mep_stripe_test_pub', 'mep_stripe_test_sec',
						'mep_stripe_live_pub', 'mep_stripe_live_sec',
						'mep_offline_enable', 'mep_offline_label',
					);
					if ( ! is_array( $new_value ) ) {
						return $new_value;
					}
					foreach ( $protected_keys as $key ) {
						if ( ! isset( $new_value[ $key ] ) && isset( $old_value[ $key ] ) ) {
							$new_value[ $key ] = $old_value[ $key ];
						}
					}
					return $new_value;
				}, 10, 2 );

				// Building the settings schema (hundreds of fields + translations + a
				// wp_dropdown_pages/get_pages query) and registering it is only needed when
				// rendering the plugin's settings page or saving it. Doing it on every admin
				// page was the primary cause of admin_init being slow site-wide, so gate it.
				if ( ! $this->should_register_settings() ) {
					return;
				}

				//set the settings
				$this->settings_api->set_sections( $this->get_settings_sections() );
				$this->settings_api->set_fields( $this->get_settings_fields() );
				//initialize settings
				$this->settings_api->admin_init();
			}

			/**
			 * Whether the global settings schema needs to be built and registered on this
			 * request. True only on the plugin's settings page (to render the form) or when
			 * core options.php is saving one of the plugin's registered option groups (so
			 * register_setting whitelisting + sanitize callbacks run). False everywhere else.
			 *
			 * @return bool
			 */
			private function should_register_settings() {
				// Rendering the plugin settings page.
				if ( isset( $_GET['page'] ) && $_GET['page'] === 'mep_event_settings_page' ) {
					return true;
				}

				// Saving the settings form through core options.php.
				$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
				if ( $pagenow === 'options.php' && isset( $_POST['option_page'] ) ) {
					$option_page = sanitize_text_field( wp_unslash( $_POST['option_page'] ) );
					$section_ids = wp_list_pluck( $this->get_settings_sections(), 'id' );
					if ( in_array( $option_page, $section_ids, true ) ) {
						return true;
					}
				}

				return false;
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
						'title' => '<i class="mi mi-palette"></i>' . __( 'Style & Icon', 'mage-eventpress' )
					),
					array(
						'id'        => 'icon_setting_sec',
						'title'     => '<i class="mi mi-icon-star"></i>' . __( 'Icon Settings', 'mage-eventpress' ),
						'parent'    => 'style_setting_sec',
						'sub_label' => __( 'Icon', 'mage-eventpress' ),
						'sub_icon'  => 'fas fa-icons',
					),
					array(
						'id'    => 'mp_slider_settings',
						'title' => '<i class="mi mi-settings-sliders"></i>' . __( 'Slider & Carousel', 'mage-eventpress' )
					),
					array(
						'id'        => 'carousel_setting_sec',
						'title'     => '<i class="mi mi-copy-image"></i>' . __( 'Carousel Settings', 'mage-eventpress' ),
						'parent'    => 'mp_slider_settings',
						'sub_label' => __( 'Carousel', 'mage-eventpress' ),
						'sub_icon'  => 'fas fa-images',
					),
					array(
						'id'    => 'payment_setting_sec',
						'title' => '<i class="mi mi-shopping-cart"></i>' . __( 'Payment', 'mage-eventpress' )
					),
					array(
						'id'    => 'mep_currency_settings',
						'title' => '<i class="mi mi-usd-circle"></i>' . __( 'Currency', 'mage-eventpress' )
					),
					array(
						'id'    => 'mep_settings_licensing',
						'title' => '<i class="mi mi-license"></i>' . __( 'License & Status', 'mage-eventpress' )
					),
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
				$off_enable = isset($payment_opts['mep_offline_enable']) ? checked($payment_opts['mep_offline_enable'], 'on', false) : '';
				$settings_fields = array(
					'general_setting_sec'      => apply_filters( 'mep_settings_general_arr', array(
							array(
								'name'    => 'seat_reserved_order_status',
								'label'   => __( 'Seat Reserved Order Status', 'mage-eventpress' ),
								'desc'    => __( 'Choose which order statuses count a seat as booked. Default: Processing and Completed.', 'mage-eventpress' ),
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
								'label'   => __( 'Block Editor for Events', 'mage-eventpress' ),
								'desc'    => __( 'Enable the WordPress block editor for events. Also turn on the REST API below.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Disable',
									'no'  => 'Enable'
								)
							),
							array(
								'name'    => 'mep_event_list_page_style',
								'label'   => __( 'Admin Event List Style', 'mage-eventpress' ),
								'desc'    => __( 'Choose how events appear in the admin list.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'new',
								'options' => array(
									'new' => 'New Modern Style',
									'wp'  => 'WordPress Default Post List Style'
								)
							),
							array(
								'name'    => 'mep_event_edit_page_mode',
								'label'   => __( 'Event Edit Screen', 'mage-eventpress' ),
								'desc'    => __( 'Choose which editor opens when you add or edit an event.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'modern',
								'options' => array(
									'modern'  => __( 'Modern', 'mage-eventpress' ),
									'classic' => __( 'Classic', 'mage-eventpress' )
								)
							),
							array(
								'name'    => 'mep_rest_api_status',
								'label'   => __( 'REST API', 'mage-eventpress' ),
								'desc'    => __( 'Allow event data to be accessed through the WordPress REST API.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_multi_lang_plugin',
								'label'   => __( 'Multilingual Plugin', 'mage-eventpress' ),
								'desc'    => __( 'Select the translation plugin you use, if any.', 'mage-eventpress' ),
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
								'label'   => __( 'Event List Sort Order', 'mage-eventpress' ),
								'desc'    => __( 'Sort the event list by upcoming date or title.', 'mage-eventpress' ),
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
								'desc'    => __( 'Name shown for events throughout the admin and site.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Events'
							),
							array(
								'name'    => 'mep_event_slug',
								'label'   => __( 'Event Slug', 'mage-eventpress' ),
								'desc'    => __( 'URL slug for event pages. After changing it, go to <strong>Settings → Permalinks</strong> and click Save.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'events'
							),
							array(
								'name'    => 'mep_event_icon',
								'label'   => __( 'Event Icon', 'mage-eventpress' ),
								'desc'    => __( 'Dashicon class for the Events menu. Example: dashicons-calendar-alt. <a href="https://developer.wordpress.org/resource/dashicons/">Browse icons</a>', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'dashicons-calendar-alt'
							),
							array(
								'name'    => 'mep_event_cat_label',
								'label'   => __( 'Category Label', 'mage-eventpress' ),
								'desc'    => __( 'Name shown for event categories.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Category'
							),
							array(
								'name'    => 'mep_event_cat_slug',
								'label'   => __( 'Category Slug', 'mage-eventpress' ),
								'desc'    => __( 'URL slug for category pages. After changing it, save <strong>Settings → Permalinks</strong>.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'mep_cat'
							),
							array(
								'name'    => 'mep_event_org_label',
								'label'   => __( 'Organizer Label', 'mage-eventpress' ),
								'desc'    => __( 'Name shown for event organizers.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Organizer'
							),
							array(
								'name'    => 'mep_event_org_slug',
								'label'   => __( 'Organizer Slug', 'mage-eventpress' ),
								'desc'    => __( 'URL slug for organizer pages. After changing it, save <strong>Settings → Permalinks</strong>.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'mep_org'
							),
							array(
								'name'    => 'mep_google_map_type',
								'label'   => __( 'Google Map Type', 'mage-eventpress' ),
								'desc'    => __( 'Choose how maps appear on the site. API maps are more accurate and support drag-and-drop.', 'mage-eventpress' ),
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
								'desc'    => __( 'Required for API maps. <a href=https://developers.google.com/maps/documentation/javascript/get-api-key target=_blank>Get an API key</a>. Billing must be enabled in Google Cloud.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => ''
							),
							array(
								'name'    => 'mep_event_expire_on_datetimes',
								'label'   => __( 'Event Expiry Time', 'mage-eventpress' ),
								'desc'    => __( 'When the event should stop accepting bookings.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'mep_event_start_date',
								'options' => array(
									'event_start_datetime'  => 'Event Start Time',
									'event_expire_datetime' => 'Event End Time'
								)
							),
							array(
								'name'    => 'mep_hide_old_date',
								'label'   => __( 'Hide Past Dates in Date Picker', 'mage-eventpress' ),
								'desc'    => __( 'Hide past dates in the booking date picker.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_expire_ticket',
								'label'   => __( 'Hide Expired Ticket Types', 'mage-eventpress' ),
								'desc'    => __( 'Hide ticket types that are no longer available.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_location_from_order_page',
								'label'   => __( 'Hide Location in Orders & Emails', 'mage-eventpress' ),
								'desc'    => __( 'Hide the event location on the thank-you page and in confirmation emails.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_date_from_order_page',
								'label'   => __( 'Hide Date in Orders & Emails', 'mage-eventpress' ),
								'desc'    => __( 'Hide the event date on the thank-you page and in confirmation emails.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_expired_date_in_calendar',
								'label'   => __( 'Hide Expired Events in Calendar', 'mage-eventpress' ),
								'desc'    => __( 'Hide past events from the free calendar view.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_direct_checkout',
								'label'   => __( 'Go to Checkout After Booking', 'mage-eventpress' ),
								'desc'    => __( 'Send customers straight to checkout after they book.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Enable',
									'no'  => 'Disable'
								)
							),
							array(
								'name'    => 'mep_show_zero_as_free',
								'label'   => __( 'Show Zero Price as Free', 'mage-eventpress' ),
								'desc'    => __( 'Display "Free" instead of 0 when a ticket has no price.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'        => 'mep_ticket_expire_time',
								'label'       => __( 'Stop Sales Before Event (Minutes)', 'mage-eventpress' ),
								'desc'        => __( 'Minutes before the event when ticket sales close. Use 0 for no limit.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => '0',
								'placeholder' => '15'
							),
							array(
								'name'        => 'mep_ticket_expire_time_on_cart',
								'label'       => __( 'Cart Hold Time (Minutes)', 'mage-eventpress' ),
								'desc'        => __( 'Minutes before tickets are removed from an abandoned cart.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => '10',
								'placeholder' => '10'
							),							
							array(
								'name'    => 'mep_load_fontawesome_from_theme',
								'label'   => __( 'Use Theme Font Awesome', 'mage-eventpress' ),
								'desc'    => __( 'Turn on if your theme already loads Font Awesome and icons conflict.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_load_flaticon_from_theme',
								'label'   => __( 'Use Theme Flat Icon', 'mage-eventpress' ),
								'desc'    => __( 'Turn on if your theme already loads Flat Icon and icons conflict.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_load_assets_only_on_event_pages',
								'label'   => __( 'Load Event Styles/Scripts Only on Event Pages?', 'mage-eventpress' ),
								'desc'    => __( 'Improves page speed by only loading the plugin\'s icon font, carousel, calendar and other frontend assets on pages that actually contain an event (single event, event archive, or an event shortcode/block). If an event shortcode is placed inside a widget or page builder module that this can\'t detect, select "No" or use the mpwem_force_load_frontend_assets filter.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_speed_up_list_page',
								'label'   => __( 'Faster Event List Loading', 'mage-eventpress' ),
								'desc'    => __( 'Speeds up the event list. Disables waitlist and seat counts on that page.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_not_available_event_from_list_page',
								'label'   => __( 'Hide Fully Booked Events', 'mage-eventpress' ),
								'desc'    => __( 'Remove events from the list when no seats remain.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_sold_out_ribbon_list_page',
								'label'   => __( 'Show Sold Out Ribbon', 'mage-eventpress' ),
								'desc'    => __( 'Show a Sold Out badge on fully booked events in the list.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_limited_availability_ribbon',
								'label'   => __( 'Show Limited Availability Ribbon', 'mage-eventpress' ),
								'desc'    => __( 'Show a badge when only a few seats are left.', 'mage-eventpress' ),
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
								'desc'        => __( 'Show the ribbon when remaining seats are at or below this number.', 'mage-eventpress' ),
								'type'        => 'number',
								'default'     => '0',
								'placeholder' => '5'
							),
							array(
								'name'        => 'mep_limited_availability_text',
								'label'       => __( 'Limited Availability Ribbon Text', 'mage-eventpress' ),
								'desc'        => __( 'Text shown on the limited availability badge.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => 'Limited Availability',
								'placeholder' => 'Limited Availability'
							),
							array(
								'name'    => 'mep_show_low_stock_warning',
								'label'   => __( 'Show Low Stock Warning', 'mage-eventpress' ),
								'desc'    => __( 'Show a warning when seats are running low.', 'mage-eventpress' ),
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
								'desc'        => __( 'Show the warning when remaining seats are at or below this number.', 'mage-eventpress' ),
								'type'        => 'number',
								'default'     => '0',
								'placeholder' => '3'
							),
							array(
								'name'        => 'mep_low_stock_text',
								'label'       => __( 'Low Stock Warning Text', 'mage-eventpress' ),
								'desc'        => __( 'Warning text. Use %s for the number of seats left.', 'mage-eventpress' ),
								'type'        => 'text',
								'default'     => 'Hurry! Only %s seats left',
								'placeholder' => 'Hurry! Only %s seats left'
							),
							array(
								'name'    => 'mep_enable_low_stock_email',
								'label'   => __( 'Low Stock Email Alerts', 'mage-eventpress' ),
								'desc'    => __( 'Email the admin when seats are running low.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_hidden_wc_product',
								'label'   => __( 'Show Hidden WooCommerce Products', 'mage-eventpress' ),
								'desc'    => __( 'Show the hidden WooCommerce products created for each event.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'no',
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_google_map_zoom_level',
								'label'   => __( 'Map Zoom Level', 'mage-eventpress' ),
								'desc'    => __( 'Default zoom level for Google Maps. Higher is closer.', 'mage-eventpress' ),
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
								'label'   => __( 'Event Sidebar', 'mage-eventpress' ),
								'desc'    => __( 'Register a widget area for the event page. Add widgets under Appearance → Widgets.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_clear_cart_after_checkout',
								'label'   => __( 'Clear Cart After Order', 'mage-eventpress' ),
								'desc'    => __( 'Empty the cart after an order is placed. Disable only if a payment gateway needs cart data.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'enable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_manual_seat_Left_fix',
								'label'   => __( 'Seat Count Fix', 'mage-eventpress' ),
								'desc'    => __( 'Enable only if you see "Sorry, There Are No Seats Available" after updating.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'disable',
								'options' => array(
									'enable'  => 'Enable',
									'disable' => 'Disable'
								)
							),
							array(
								'name'    => 'mep_fix_details_page_fatal_error',
								'label'   => __( 'Event Page Error Fix', 'mage-eventpress' ),
								'desc'    => __( 'Enable only if the event page shows a fatal error. Leave off otherwise.', 'mage-eventpress' ),
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
								'desc'    => __( 'Date format for the date picker. Avoid text-based formats on non-English sites.', 'mage-eventpress' ),
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
								'label'   => __( 'Show Price in Event List', 'mage-eventpress' ),
								'desc'    => __( 'Show the event price on event list pages.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_price_show', 'general_setting_sec', 'event_list_setting_sec', 'yes' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_date_list_in_event_listing',
								'label'   => __( 'Show Multi-Date List', 'mage-eventpress' ),
								'desc'    => __( 'Show all dates for multi-date events in the list.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_date_list_in_event_listing', 'general_setting_sec', 'event_list_setting_sec', 'yes' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_organizer_list',
								'label'   => __( 'Hide Organizer in List', 'mage-eventpress' ),
								'desc'    => __( 'Hide the organizer on event list cards.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_organizer_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_location_list',
								'label'   => __( 'Hide Location in List', 'mage-eventpress' ),
								'desc'    => __( 'Hide the location on event list cards.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_location_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time_list',
								'label'   => __( 'Hide Time in List', 'mage-eventpress' ),
								'desc'    => __( 'Hide the full time on event list cards.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_time_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_end_time_list',
								'label'   => __( 'Hide End Time in List', 'mage-eventpress' ),
								'desc'    => __( 'Hide only the end time on event list cards.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_end_time_list', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_hide_event_hover_btn',
								'label'   => __( 'Hide Book Now on Hover', 'mage-eventpress' ),
								'desc'    => __( 'Hide the Book Now button that appears on hover in the event list.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_hide_event_hover_btn', 'general_setting_sec', 'event_list_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
                            array(
                                'name'    => 'mep_hide_event_list_msg',
                                'label'   => __( 'Hide Event List Message', 'mage-eventpress' ),
                                'desc'    => __( 'Hide the message shown on the event list page.', 'mage-eventpress' ),
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
								'label'   => __( 'Show Speaker List', 'mage-eventpress' ),
								'desc'    => __( 'Show the speaker list on the event page. You can also control this per event in the event editor.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_enable_speaker_list', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_show_product_cat_in_event',
								'label'   => __( 'WooCommerce Product Categories', 'mage-eventpress' ),
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
								'label'   => __( 'Event Page Template', 'mage-eventpress' ),
								'desc'    => __( 'Layout used for the single event page.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'default-theme.php',
								'options' => mep_event_template_name()
							),
							array(
								'name'    => 'mep_event_product_type',
								'label'   => __( 'Virtual Event Product', 'mage-eventpress' ),
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
								'label'   => __( 'Hide Date on Event Page', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_date_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time_from_details',
								'label'   => __( 'Hide Time on Event Page', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_time_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_location_from_details',
								'label'   => __( 'Hide Location on Event Page', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_location_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_total_seat_from_details',
								'label'   => __( 'Hide Seat Count on Event Page', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_total_seat_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_org_from_details',
								'label'   => __( 'Hide Organizer on Event Page', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_org_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_address_from_details',
								'label'   => __( 'Hide Address on Event Page', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_address_from_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_event_schedule_details',
								'label'   => __( 'Hide Schedule on Event Page', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_event_schedule_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_share_this_details',
								'label'   => __( 'Hide Social Share on Event Page', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_share_this_details', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_calendar_details',
								'label'   => __( 'Hide Add to Calendar', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
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
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_description_title', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_left_sidebar_title',
								'label'   => __( 'Hide Sidebar Title', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => mep_change_global_option_section( 'mep_event_hide_left_sidebar_title', 'general_setting_sec', 'single_event_setting_sec', 'no' ),
								'options' => array(
									'yes' => 'Yes',
									'no'  => 'No'
								)
							),
							array(
								'name'    => 'mep_event_hide_time',
								'label'   => __( 'Hide Time Below Title', 'mage-eventpress' ),
								'desc'    => __( 'Choose Yes to hide this on the event page, or No to show it.', 'mage-eventpress' ),
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
								'label'   => __( 'Send Confirmation on Order Status', 'mage-eventpress' ),
								'desc'    => __( 'Choose which order statuses trigger the confirmation email.', 'mage-eventpress' ),
								'type'    => 'multicheck',
								'default' => array( 'completed' => 'completed' ),
								'options' => array(
									'processing' => 'Processing',
									'completed'  => 'Completed'
								)
							),
							array(
								'name'    => 'mep_email_form_name',
								'label'   => __( 'From Name', 'mage-eventpress' ),
								'desc'    => __( 'Sender name shown on confirmation emails.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => get_bloginfo( 'name' )
							),
							array(
								'name'    => 'mep_email_form_email',
								'label'   => __( 'From Email', 'mage-eventpress' ),
								'desc'    => __( 'Sender address for confirmation emails.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => get_option( 'admin_email' )
							),
							array(
								'name'    => 'mep_email_subject',
								'label'   => __( 'Email Subject', 'mage-eventpress' ),
								'desc'    => __( 'Subject line for the confirmation email.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => 'Event Notification'
							),
							array(
								'name'    => 'mep_confirmation_email_text',
								'label'   => __( 'Confirmation Email Body', 'mage-eventpress' ),
								'desc'    => __( 'Email content. Placeholders: <b>{name}</b>, <b>{event}</b>, <b>{ticket_type}</b>, <b>{order_id}</b>, <b>{event_date}</b>, <b>{event_time}</b>, <b>{event_datetime}</b>, <b>{payment_method}</b>, <b>{amount_paid}</b>', 'mage-eventpress' ),
								'type'    => 'wysiwyg',
								'default' => 'Hi {name},<br><br>Thanks for joining the event.<br><br>Here are the event details:<br><br>Event Name: {event}<br><br>Ticket Type: {ticket_type}<br><br>Event Date: {event_date}<br><br>Start Time: {event_time}<br><br>Full DateTime: {event_datetime}<br><br>Payment Method: {payment_method}<br><br>Amount Paid: {amount_paid}<br><br>Thanks',
							),
							array(
								'name'    => 'mep_send_confirmation_to_billing_email',
								'label'   => __( 'Send Confirmation to Billing Email', 'mage-eventpress' ),
								'desc'    => __( 'Send the confirmation email to the billing email address.', 'mage-eventpress' ),
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
								'desc'    => __( 'Main color for icons, buttons, and borders.', 'mage-eventpress' ),
								'type'    => 'color',
								'default' => '#6046ff'
							),
							array(
								'name'    => 'mpev_secondary_color',
								'label'   => __( 'Secondary Color', 'mage-eventpress' ),
								'desc'    => __( 'Secondary text and accent color.', 'mage-eventpress' ),
								'type'    => 'color',
								'default' => '#f1f5ff'
							),
						)
					),
					'icon_setting_sec'         => apply_filters( 'mep_settings_icon_arr', array(
							array(
								'name'    => 'mep_event_date_icon',
								'label'   => __( 'Event Date Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon shown next to the event date.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-calendar',
							),
							array(
								'name'    => 'mep_event_time_icon',
								'label'   => __( 'Event Time Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon shown next to the event time.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-clock',
							),
							array(
								'name'    => 'mep_event_location_icon',
								'label'   => __( 'Event Location Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon shown next to the event location.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-marker',
							),
							array(
								'name'    => 'mep_event_organizer_icon',
								'label'   => __( 'Event Organizer Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon shown next to the organizer.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-badge',
							),
							array(
								'name'    => 'mep_event_location_list_icon',
								'label'   => __( 'Sidebar Location Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon for locations in the event sidebar.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-arrow-circle-right',
							),
							array(
								'name'    => 'mep_event_ss_fb_icon',
								'label'   => __( 'Facebook Share Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon for sharing on Facebook.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-facebook-f',
							),
							array(
								'name'    => 'mep_event_ss_twitter_icon',
								'label'   => __( 'Twitter Share Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon for sharing on Twitter.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-twitter',
							),
							array(
								'name'    => 'mep_event_ss_linkedin_icon',
								'label'   => __( 'LinkedIn Share Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon for sharing on LinkedIn.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-linkedin',
							),
							array(
								'name'    => 'mep_event_ss_whatsapp_icon',
								'label'   => __( 'WhatsApp Share Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon for sharing on WhatsApp.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'fab fa-whatsapp',
							),
							array(
								'name'    => 'mep_event_ss_email_icon',
								'label'   => __( 'Email Share Icon', 'mage-eventpress' ),
								'desc'    => __( 'Icon for sharing by email.', 'mage-eventpress' ),
								'type'    => 'iconlib',
								'default' => 'mi mi-envelope',
							),
						)
					),
					'carousel_setting_sec'     => apply_filters( 'mep_settings_carousel_arr', array(
							array(
								'name'    => 'mep_load_carousal_from_theme',
								'label'   => __( 'Use Theme Owl Carousel', 'mage-eventpress' ),
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
								'desc'    => __( 'Automatically advance carousel slides.', 'mage-eventpress' ),
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
								'desc'    => __( 'Restart the carousel after the last slide.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'yes',
								'options' => array(
									'true'  => 'Yes',
									'false' => 'No'
								)
							),
							array(
								'name'    => 'mep_speed_carousal',
								'label'   => __( 'Autoplay Speed (ms)', 'mage-eventpress' ),
								'desc'    => __( 'Time between slides in milliseconds. Default: 5000.', 'mage-eventpress' ),
								'type'    => 'text',
								'default' => '5000'
							),
						)
					),
					'mp_slider_settings'       => array(
						array(
							'name'    => 'slider_type',
							'label'   => esc_html__( 'Slider Type', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Choose the slider layout.', 'mage-eventpress' ),
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
							'desc'    => esc_html__( 'Choose the visual style of the slider.', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'style_1',
							'options' => array(
								'style_1' => esc_html__( 'Style One', 'mage-eventpress' ),
								'style_2' => esc_html__( 'Style Two', 'mage-eventpress' ),
							)
						),
						array(
							'name'    => 'indicator_visible',
							'label'   => esc_html__( 'Show Slider Indicators', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Show navigation dots or icons on the slider.', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'indicator_type',
							'label'   => esc_html__( 'Indicator Type', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Indicator Type Default Icon', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'icon',
							'options' => array(
								'icon'  => esc_html__( 'Icon Indicator', 'mage-eventpress' ),
								'image' => esc_html__( 'image Indicator', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'showcase_visible',
							'label'   => esc_html__( 'Show Showcase Thumbnails', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Show thumbnail previews beside the slider.', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'showcase_position',
							'label'   => esc_html__( 'Showcase Position', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Please Select Showcase Position Default Right', 'mage-eventpress' ),
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
							'label'   => esc_html__( 'Popup Image Indicator', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Show image indicators in the slider popup.', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'popup_icon_indicator',
							'label'   => esc_html__( 'Popup Icon Indicator', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Show icon indicators in the slider popup.', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'mage-eventpress' ),
								'off' => esc_html__( 'Off', 'mage-eventpress' )
							)
						),
						array(
							'name'    => 'slider_height',
							'label'   => esc_html__( 'Slider Height', 'mage-eventpress' ),
							'desc'    => esc_html__( 'Height of the image slider.', 'mage-eventpress' ),
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
									';
									if ( ! $wc_active ) {
										$html .= '
										<div class="woocommerce-field">
											<div class="mpwem-woo-warning-notice" style="background: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; border-radius: var(--mpwem-radius); margin-bottom: 10px; margin-top: 15px;">
												<div style="display: flex; flex-direction: column; align-items: flex-start; gap: 15px;">
													<div style="width: 100%;">
														<strong style="display: block; font-size: 14px; margin-bottom: 5px;"><i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>' . __( "Notice: WooCommerce is Not Activated", "mage-eventpress" ) . '</strong>
														<span style="font-size: 13px; display: block;">' . __( "To actually use the \"Ticket-Selling\" event type and allow ticket sales, you must install and activate WooCommerce.", "mage-eventpress" ) . '</span>
													</div>
													<div>
														<button type="button" class="button button-primary mep-install-wc-trigger" style="white-space: nowrap;">' . $woo_btn_text . '</button>
													</div>
												</div>
											</div>
										</div>
										';
									}
									$html .= '</div>';
									return $html;
								})()
							),
							array(
								'name'    => 'mep_enable_wc_payment',
								'label'   => __( 'Use WooCommerce Checkout', 'mage-eventpress' ),
								'desc'    => __( 'Process ticket payments through WooCommerce.', 'mage-eventpress' ),
								'type'    => 'checkbox',
								'default' => 'on',
								'class'   => 'woocommerce-field woocommerce-main-toggle'
							),
							array(
								'name'    => 'mep_wc_add_to_cart_redirect',
								'label'   => __( 'After Add to Cart', 'mage-eventpress' ),
								'desc'    => __( 'Where to send customers after tickets are added to the cart.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'checkout',
								'options' => array(
									'cart'     => __( 'Cart', 'mage-eventpress' ),
									'checkout' => __( 'Checkout', 'mage-eventpress' ),
								),
								'class'   => 'woocommerce-field wc-additional-field'
							),
							array(
								'name'    => 'mep_wc_after_order_redirect',
								'label'   => __( 'After Order Confirmation', 'mage-eventpress' ),
								'desc'    => __( 'Where to send customers after the order is confirmed.', 'mage-eventpress' ),
								'type'    => 'select',
								'default' => 'plugin_thankyou',
								'options' => array(
									'plugin_thankyou' => __( 'Plugin Thank You Page', 'mage-eventpress' ),
									'woo_thankyou'    => __( 'WooCommerce Thank You Page', 'mage-eventpress' ),
								),
								'class'   => 'woocommerce-field wc-additional-field'
							),
							array(
								'name'    => 'mep_wc_require_login',
								'label'   => __( 'Require Login to Buy', 'mage-eventpress' ),
								'desc'    => __( 'Customers must log in before buying tickets.', 'mage-eventpress' ),
								'type'    => 'checkbox',
								'default' => '',
								'class'   => 'woocommerce-field wc-additional-field'
							),
							array(
								'name'    => 'mep_wc_show_billing_info',
								'label'   => __( 'Show Billing Fields', 'mage-eventpress' ),
								'desc'    => __( 'Show billing fields on the WooCommerce checkout page.', 'mage-eventpress' ),
								'type'    => 'checkbox',
								'default' => '',
								'class'   => 'woocommerce-field wc-additional-field'
							),
							array(
								'name'    => 'mep_wc_confirm_ticket_status',
								'label'   => __( 'Confirm Tickets on Payment Status', 'mage-eventpress' ),
								'desc'    => __( 'Order statuses that mark tickets as confirmed.', 'mage-eventpress' ),
								'type'    => 'multicheck',
								'default' => array( 'processing' => 'processing', 'completed' => 'completed' ),
								'options' => array(
									'pending'    => __( 'Pending payment', 'mage-eventpress' ),
									'processing' => __( 'Processing', 'mage-eventpress' ),
									'on-hold'    => __( 'On hold', 'mage-eventpress' ),
									'completed'  => __( 'Completed', 'mage-eventpress' ),
								),
								'class'   => 'woocommerce-field wc-additional-field'
							),
							array(
								'name'     => 'mep_wc_payment_gateways_manager',
								'label'    => '',
								'class'    => 'woocommerce-field wc-payment-methods-field',
								'callback' => function() {
									if ( class_exists( 'WooCommerce' ) && class_exists( 'MPWEM_WC_Payment_Manager' ) ) {
										MPWEM_WC_Payment_Manager::instance()->render();
									}
								},
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
        ' . ( mep_check_plugin_installed( "mage-eventpress-pro/woocommerce-event-manager-pro.php" ) ? '<span class="gateway-status ' . ($pp_enable ? "active" : "") . '" style="position:absolute;left:50%;transform:translateX(-50%);font-size:13px;font-weight:600;">' . ($pp_enable ? __("Enabled", "mage-eventpress") : __("Disabled", "mage-eventpress")) . '</span>' : '' ) . '
        ' . ( mep_check_plugin_installed( "mage-eventpress-pro/woocommerce-event-manager-pro.php" ) ? '<button type="button" class="button button-secondary gateway-configure-btn" id="mep-paypal-configure-btn">' . __( "Configure", "mage-eventpress" ) . '</button>' : '<span style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border:none; box-shadow: 0 2px 4px rgba(253,160,133,0.3); user-select: none;" title="' . esc_attr__("Available in Pro version", "mage-eventpress") . '">PRO</span>' ) . '
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
        ' . ( mep_check_plugin_installed( "mage-eventpress-pro/woocommerce-event-manager-pro.php" ) ? '<span class="gateway-status ' . ($st_enable ? "active" : "") . '" style="position:absolute;left:50%;transform:translateX(-50%);font-size:13px;font-weight:600;">' . ($st_enable ? __("Enabled", "mage-eventpress") : __("Disabled", "mage-eventpress")) . '</span>' : '' ) . '
        ' . ( mep_check_plugin_installed( "mage-eventpress-pro/woocommerce-event-manager-pro.php" ) ? '<button type="button" class="button button-secondary gateway-configure-btn" id="mep-stripe-configure-btn">' . __( "Configure", "mage-eventpress" ) . '</button>' : '<span style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border:none; box-shadow: 0 2px 4px rgba(253,160,133,0.3); user-select: none;" title="' . esc_attr__("Available in Pro version", "mage-eventpress") . '">PRO</span>' ) . '
    </div>
</div>

<!-- Offline Payment Card -->
<div class="gateway-card offline-card">
    <div class="gateway-header">
        <h3>
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right:4px;">
                <path d="M3 19h18a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/>
                <path d="M2 10h20M6 14h4" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            ' . __( "Offline Payment", "mage-eventpress" ) . '
        </h3>
        <span class="gateway-status ' . ($off_enable ? "active" : "") . '" style="position:absolute;left:50%;transform:translateX(-50%);font-size:13px;font-weight:600;">' . ($off_enable ? __("Enabled", "mage-eventpress") : __("Disabled", "mage-eventpress")) . '</span>
        <button type="button" class="button button-secondary gateway-configure-btn" id="mep-offline-configure-btn">' . __( "Configure", "mage-eventpress" ) . '</button>
    </div>
</div>

<!-- Booking Confirmation Page -->
<div style="margin-top:24px; padding-top:20px; border-top:1px solid #e2e4e7; display:flex; align-items:flex-start; gap:0;">
    <div style="width:30%; padding-right:20px; box-sizing:border-box;">
        <label style="display:block; font-weight:600; font-size:13px; color:#1d2327; margin:0 0 4px;">' . __( "Booking Confirmation Page", "mage-eventpress" ) . '</label>
        <span style="display:block; margin:0; font-size:11px; color:#9ca3af !important; line-height:1.6; font-weight:400;">' . __( "Select a page with the [mep_booking_confirmation] shortcode. After booking, customers are redirected here instead of back to the event page.", "mage-eventpress" ) . '</span>
    </div>
    <div style="width:70%; box-sizing:border-box;">
        ' . wp_dropdown_pages( array(
            'name'              => 'payment_setting_sec[mep_confirmation_page_id]',
            'id'                => 'mep_confirmation_page_id',
            'selected'          => ( function() { $o = get_option( 'payment_setting_sec', array() ); return ! empty( $o['mep_confirmation_page_id'] ) ? absint( $o['mep_confirmation_page_id'] ) : 0; } )(),
            'show_option_none'  => __( '— Default —', 'mage-eventpress' ),
            'option_none_value' => '0',
            'style'             => 'width:100%; max-width:320px; border:1px solid #d1d5db; border-radius:6px; padding:6px 10px; font-size:13px;',
            'echo'              => 0,
        ) ) . '
    </div>
</div>
'
							)
						)
					),
					'mep_currency_settings' => array(
						array(
							'name' => 'mep_currency_info',
							'type' => 'html',
							'desc' => ( function() {
								$has_woo = MPWEM_Global_Function::has_woocommerce();
								if ( $has_woo ) {
									return '<div style="background:#e8f4fd;border-left:4px solid #2271b1;padding:14px 18px;border-radius:4px;font-size:13px;color:#1e3a5f;margin-bottom:4px;">'
										. '<strong>' . esc_html__( 'WooCommerce is active', 'mage-eventpress' ) . '</strong> — '
										. esc_html__( 'Currency display is controlled by WooCommerce settings. The settings below are used as a fallback when WooCommerce is deactivated.', 'mage-eventpress' )
										. '</div>';
								}
								return '<div style="background:#fff3cd;border-left:4px solid #ffc107;padding:14px 18px;border-radius:4px;font-size:13px;color:#664d03;margin-bottom:4px;">'
									. '<strong>' . esc_html__( 'WooCommerce is not active', 'mage-eventpress' ) . '</strong> — '
									. esc_html__( 'These currency settings are used for price display and the native checkout flow.', 'mage-eventpress' )
									. '</div>';
							} )(),
						),
						array(
							'name'    => 'mep_currency_symbol',
							'label'   => __( 'Currency Symbol', 'mage-eventpress' ),
							'desc'    => __( 'The symbol to display next to prices (e.g. $, €, £, ৳).', 'mage-eventpress' ),
							'type'    => 'text',
							'default' => '$',
						),
						array(
							'name'    => 'mep_currency_position',
							'label'   => __( 'Currency Position', 'mage-eventpress' ),
							'desc'    => __( 'Place the currency symbol before or after the price.', 'mage-eventpress' ),
							'type'    => 'select',
							'default' => 'left',
							'options' => array(
								'left'        => __( 'Left — $99', 'mage-eventpress' ),
								'right'       => __( 'Right — 99$', 'mage-eventpress' ),
								'left_space'  => __( 'Left with space — $ 99', 'mage-eventpress' ),
								'right_space' => __( 'Right with space — 99 $', 'mage-eventpress' ),
							),
						),
						array(
							'name'    => 'mep_currency_decimal_sep',
							'label'   => __( 'Decimal Separator', 'mage-eventpress' ),
							'desc'    => __( 'Character used for decimals (for example . or ,).', 'mage-eventpress' ),
							'type'    => 'text',
							'default' => '.',
						),
						array(
							'name'    => 'mep_currency_thousand_sep',
							'label'   => __( 'Thousands Separator', 'mage-eventpress' ),
							'desc'    => __( 'Character used for thousands (for example , or .). Leave blank for none.', 'mage-eventpress' ),
							'type'    => 'text',
							'default' => ',',
						),
						array(
							'name'    => 'mep_currency_num_decimals',
							'label'   => __( 'Number of Decimals', 'mage-eventpress' ),
							'desc'    => __( 'How many decimal places to show in prices (e.g. 2 → $9.99).', 'mage-eventpress' ),
							'type'    => 'text',
							'default' => '2',
						),
					),
				);

				return apply_filters( 'mep_settings_sec_fields', $settings_fields );
			}

			function plugin_page() {
				$this->render_modern_settings_page();
			}

			/**
			 * Tab metadata for the modern settings sidebar (mirrors bus wbtm_settings_page).
			 */
			private function get_modern_tab_configs() {
				return array(
					'general_setting_sec'      => array(
						'title'    => __( 'General Settings', 'mage-eventpress' ),
						'icon'     => 'fas fa-sliders-h',
						'subtitle' => __( 'Core booking & plugin behavior', 'mage-eventpress' ),
					),
					'event_list_setting_sec'   => array(
						'title'    => __( 'Event List Settings', 'mage-eventpress' ),
						'icon'     => 'fas fa-list',
						'subtitle' => __( 'Archive & listing display', 'mage-eventpress' ),
					),
					'single_event_setting_sec' => array(
						'title'    => __( 'Single Event Settings', 'mage-eventpress' ),
						'icon'     => 'fas fa-calendar-alt',
						'subtitle' => __( 'Event details page options', 'mage-eventpress' ),
					),
					'email_setting_sec'        => array(
						'title'    => __( 'Email Settings', 'mage-eventpress' ),
						'icon'     => 'fas fa-envelope',
						'subtitle' => __( 'Confirmation, PDF & waitlist emails', 'mage-eventpress' ),
					),
					'style_setting_sec'        => array(
						'title'    => __( 'Style & Icon', 'mage-eventpress' ),
						'icon'     => 'fas fa-palette',
						'subtitle' => __( 'Colors & frontend icons', 'mage-eventpress' ),
					),
					'mp_slider_settings'       => array(
						'title'    => __( 'Slider & Carousel', 'mage-eventpress' ),
						'icon'     => 'fas fa-photo-video',
						'subtitle' => __( 'Slider & carousel display options', 'mage-eventpress' ),
					),
					'payment_setting_sec'      => array(
						'title'    => __( 'Payment', 'mage-eventpress' ),
						'icon'     => 'fas fa-credit-card',
						'subtitle' => __( 'Checkout & gateways', 'mage-eventpress' ),
					),
					'mep_currency_settings'    => array(
						'title'    => __( 'Currency', 'mage-eventpress' ),
						'icon'     => 'fas fa-dollar-sign',
						'subtitle' => __( 'Currency display', 'mage-eventpress' ),
					),
					'mep_settings_licensing'   => array(
						'title'    => __( 'License & Status', 'mage-eventpress' ),
						'icon'     => 'fas fa-key',
						'subtitle' => __( 'Licenses & system environment', 'mage-eventpress' ),
					),
					'mep_eb_settings'          => array(
						'title'    => __( 'Early Birds', 'mage-eventpress' ),
						'icon'     => 'fas fa-dove',
						'subtitle' => __( 'Early bird ticket display rules', 'mage-eventpress' ),
					),
					'mep_pdf_gen_settings'     => array(
						'title'    => __( 'PDF Settings', 'mage-eventpress' ),
						'icon'     => 'fas fa-file-pdf',
						'subtitle' => __( 'Customize PDF ticket design, company details, and billing fields.', 'mage-eventpress' ),
					),
					'csv_checkout_export_fileds_sec' => array(
						'title'    => __( 'CSV Settings', 'mage-eventpress' ),
						'icon'     => 'fas fa-file-csv',
						'subtitle' => __( 'CSV export column options', 'mage-eventpress' ),
					),
					'mep_certificate_settings' => array(
						'title'    => __( 'Certificate Settings', 'mage-eventpress' ),
						'icon'     => 'fas fa-certificate',
						'subtitle' => __( 'Certificate templates & branding', 'mage-eventpress' ),
					),
					'mep_ai_assistant_settings' => array(
						'title'    => __( 'AI Settings', 'mage-eventpress' ),
						'icon'     => 'fas fa-robot',
						'subtitle' => __( 'API keys & default models', 'mage-eventpress' ),
					),
					'mep_deposit_settings'     => array(
						'title'    => __( 'Deposit / Partial Payment', 'mage-eventpress' ),
						'icon'     => 'fas fa-percentage',
						'subtitle' => __( 'Deposit and balance due options', 'mage-eventpress' ),
					),
					'mep_review_permission_settings' => array(
						'title'    => __( 'Review & Rating', 'mage-eventpress' ),
						'icon'     => 'fas fa-star',
						'subtitle' => __( 'Review permissions & display', 'mage-eventpress' ),
					),
					'mep_social_card_setting_sec' => array(
						'title'    => __( 'Social Share Card', 'mage-eventpress' ),
						'icon'     => 'fas fa-share-alt',
						'subtitle' => __( 'Design, triggers & networks', 'mage-eventpress' ),
					),
					'mep_gsheet_settings'      => array(
						'title'    => __( 'Google Sheets', 'mage-eventpress' ),
						'icon'     => 'fas fa-table',
						'subtitle' => __( 'Sync orders to Google Sheets', 'mage-eventpress' ),
					),
				);
			}

			/**
			 * Sections nested under Email Settings (Confirmation / PDF / Waitlist).
			 * Option keys stay identical so existing saved data keeps working.
			 *
			 * @param array $sections Section map keyed by id.
			 * @return array Subtab definitions keyed by section id.
			 */
			private function get_email_settings_subtabs( $sections ) {
				$subtabs = array(
					'email_setting_sec' => array(
						'label' => __( 'Confirmation Email', 'mage-eventpress' ),
						'icon'  => 'fas fa-paper-plane',
					),
				);

				// Known email child sections (fallback if parent meta is missing on older addons).
				$known_children = array(
					'mep_pdf_email_settings'      => array(
						'label' => __( 'PDF Email', 'mage-eventpress' ),
						'icon'  => 'fas fa-file-pdf',
					),
					'mep_waitlist_email_settings' => array(
						'label' => __( 'Waitlist Email', 'mage-eventpress' ),
						'icon'  => 'fas fa-hourglass-half',
					),
				);

				foreach ( $sections as $section_id => $section ) {
					$parent = isset( $section['parent'] ) ? $section['parent'] : '';
					$is_known = isset( $known_children[ $section_id ] );
					if ( 'email_setting_sec' !== $parent && ! $is_known ) {
						continue;
					}
					$defaults = $is_known ? $known_children[ $section_id ] : array(
						'label' => wp_strip_all_tags( isset( $section['title'] ) ? $section['title'] : $section_id ),
						'icon'  => 'fas fa-envelope',
					);
					$subtabs[ $section_id ] = array(
						'label' => isset( $section['sub_label'] ) ? $section['sub_label'] : $defaults['label'],
						'icon'  => isset( $section['sub_icon'] ) ? $section['sub_icon'] : $defaults['icon'],
					);
				}

				return $subtabs;
			}

			/**
			 * Sections nested under Style & Icon (Style / Icon).
			 * Option keys stay identical so existing saved data keeps working.
			 *
			 * @param array $sections Section map keyed by id.
			 * @return array Subtab definitions keyed by section id.
			 */
			private function get_style_icon_settings_subtabs( $sections ) {
				$subtabs = array(
					'style_setting_sec' => array(
						'label' => __( 'Style', 'mage-eventpress' ),
						'icon'  => 'fas fa-palette',
					),
					'icon_setting_sec'  => array(
						'label' => __( 'Icon', 'mage-eventpress' ),
						'icon'  => 'fas fa-icons',
					),
				);

				foreach ( $sections as $section_id => $section ) {
					$parent = isset( $section['parent'] ) ? $section['parent'] : '';
					if ( 'style_setting_sec' !== $parent ) {
						continue;
					}
					$subtabs[ $section_id ] = array(
						'label' => isset( $section['sub_label'] ) ? $section['sub_label'] : wp_strip_all_tags( isset( $section['title'] ) ? $section['title'] : $section_id ),
						'icon'  => isset( $section['sub_icon'] ) ? $section['sub_icon'] : 'fas fa-icons',
					);
				}

				return $subtabs;
			}

			/**
			 * Sections nested under Slider & Carousel (Slider / Carousel).
			 * Option keys stay identical so existing saved data keeps working.
			 *
			 * @param array $sections Section map keyed by id.
			 * @return array Subtab definitions keyed by section id.
			 */
			private function get_slider_carousel_settings_subtabs( $sections ) {
				$subtabs = array(
					'mp_slider_settings'   => array(
						'label' => __( 'Slider', 'mage-eventpress' ),
						'icon'  => 'fas fa-photo-video',
					),
					'carousel_setting_sec' => array(
						'label' => __( 'Carousel', 'mage-eventpress' ),
						'icon'  => 'fas fa-images',
					),
				);

				foreach ( $sections as $section_id => $section ) {
					$parent = isset( $section['parent'] ) ? $section['parent'] : '';
					if ( 'mp_slider_settings' !== $parent ) {
						continue;
					}
					$subtabs[ $section_id ] = array(
						'label' => isset( $section['sub_label'] ) ? $section['sub_label'] : wp_strip_all_tags( isset( $section['title'] ) ? $section['title'] : $section_id ),
						'icon'  => isset( $section['sub_icon'] ) ? $section['sub_icon'] : 'fas fa-images',
					);
				}

				return $subtabs;
			}

			/**
			 * Sections nested under License & Status.
			 *
			 * @param array $sections Section map keyed by id.
			 * @return array Subtab definitions keyed by section id.
			 */
			private function get_license_status_settings_subtabs( $sections ) {
				$subtabs = array(
					'mep_settings_licensing' => array(
						'label' => __( 'License', 'mage-eventpress' ),
						'icon'  => 'fas fa-key',
					),
					'mep_status_setting_sec' => array(
						'label' => __( 'Status', 'mage-eventpress' ),
						'icon'  => 'fas fa-heartbeat',
					),
				);

				foreach ( $sections as $section_id => $section ) {
					$parent = isset( $section['parent'] ) ? $section['parent'] : '';
					if ( 'mep_settings_licensing' !== $parent ) {
						continue;
					}
					$subtabs[ $section_id ] = array(
						'label' => isset( $section['sub_label'] ) ? $section['sub_label'] : wp_strip_all_tags( isset( $section['title'] ) ? $section['title'] : $section_id ),
						'icon'  => isset( $section['sub_icon'] ) ? $section['sub_icon'] : 'fas fa-heartbeat',
					);
				}

				return $subtabs;
			}

			/**
			 * Render one settings section card + form (shared by top-level tabs and email subtabs).
			 *
			 * @param string $tab_id  Section / option group id.
			 * @param array  $config  Display config (title, icon).
			 * @param array  $fields  All settings fields.
			 * @param array  $sections All sections keyed by id.
			 */
			private function render_settings_section_form( $tab_id, $config, $fields, $sections ) {
				$has_fields = ! empty( $fields[ $tab_id ] );
				$sec_arg    = isset( $sections[ $tab_id ] ) ? $sections[ $tab_id ] : array( 'id' => $tab_id );
				$title      = isset( $config['title'] ) ? $config['title'] : ( isset( $config['label'] ) ? $config['label'] : $tab_id );
				$icon       = isset( $config['icon'] ) ? $config['icon'] : 'fas fa-cog';
				?>
				<?php if ( $has_fields ) : ?>
				<form method="post" action="options.php">
				<?php endif; ?>
					<div class="mep-gs__section-card">
						<div class="mep-gs__section-head">
							<span class="mep-gs__section-icon <?php echo esc_attr( $icon ); ?>"></span>
							<span class="mep-gs__section-head-label"><?php echo esc_html( $title ); ?></span>
						</div>
						<?php
						do_action( 'wsa_form_top_' . $tab_id, $sec_arg );
						if ( $has_fields ) {
							settings_fields( $tab_id );
							do_settings_sections( $tab_id );
						}
						do_action( 'wsa_form_bottom_' . $tab_id, $sec_arg );
						?>
					</div>
				<?php if ( $has_fields ) : ?>
						<div style="display:none;"><?php submit_button(); ?></div>
				</form>
				<?php endif;
			}

			/**
			 * Bus-style modern settings shell. Reuses the existing Settings API forms
			 * (do_settings_sections + wsa_form_bottom hooks) so Payment / License / Status
			 * keep working unchanged — only the chrome is new.
			 */
			private function render_modern_settings_page() {
				$sections_raw = $this->get_settings_sections();
				$fields       = $this->get_settings_fields();
				$tab_configs  = $this->get_modern_tab_configs();

				$sections = array();
				if ( is_array( $sections_raw ) ) {
					foreach ( $sections_raw as $sec ) {
						if ( ! empty( $sec['id'] ) ) {
							$sections[ $sec['id'] ] = $sec;
						}
					}
				}

				$email_subtabs = $this->get_email_settings_subtabs( $sections );
				$email_child_ids = array_values( array_filter( array_keys( $email_subtabs ), function( $id ) {
					return 'email_setting_sec' !== $id;
				} ) );

				$si_subtabs = $this->get_style_icon_settings_subtabs( $sections );
				$si_child_ids = array_values( array_filter( array_keys( $si_subtabs ), function( $id ) {
					return 'style_setting_sec' !== $id;
				} ) );

				$sc_subtabs = $this->get_slider_carousel_settings_subtabs( $sections );
				$sc_child_ids = array_values( array_filter( array_keys( $sc_subtabs ), function( $id ) {
					return 'mp_slider_settings' !== $id;
				} ) );

				$ls_subtabs = $this->get_license_status_settings_subtabs( $sections );
				$ls_child_ids = array_values( array_filter( array_keys( $ls_subtabs ), function( $id ) {
					return 'mep_settings_licensing' !== $id;
				} ) );

				$nested_child_ids = array_merge( $email_child_ids, $si_child_ids, $sc_child_ids, $ls_child_ids );

				$visible_tabs = array();
				foreach ( $tab_configs as $tab_id => $config ) {
					if ( in_array( $tab_id, $nested_child_ids, true ) ) {
						continue;
					}
					if ( isset( $sections[ $tab_id ] ) ) {
						$visible_tabs[ $tab_id ] = $config;
					}
				}
				foreach ( $sections as $section_id => $section ) {
					// Nested sections belong under their parent hub, not the sidebar.
					if ( ! empty( $section['parent'] ) || in_array( $section_id, $nested_child_ids, true ) ) {
						continue;
					}
					if ( isset( $visible_tabs[ $section_id ] ) ) {
						continue;
					}
					$visible_tabs[ $section_id ] = array(
						'title'    => wp_strip_all_tags( isset( $section['title'] ) ? $section['title'] : $section_id ),
						'icon'     => 'fas fa-cog',
						'subtitle' => '',
					);
				}

				$first_tab = ! empty( $visible_tabs ) ? array_key_first( $visible_tabs ) : '';
				$label     = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				$has_pro   = mep_check_plugin_installed( 'mage-eventpress-pro/woocommerce-event-manager-pro.php' );

				$tab_meta = array();
				foreach ( $visible_tabs as $id => $cfg ) {
					$tab_meta[ $id ] = array( $cfg['title'], isset( $cfg['subtitle'] ) ? $cfg['subtitle'] : '' );
				}

				$email_sub_meta = array();
				foreach ( $email_subtabs as $sub_id => $sub_cfg ) {
					$email_sub_meta[ $sub_id ] = isset( $sub_cfg['label'] ) ? $sub_cfg['label'] : $sub_id;
				}

				$si_sub_meta = array();
				foreach ( $si_subtabs as $sub_id => $sub_cfg ) {
					$si_sub_meta[ $sub_id ] = isset( $sub_cfg['label'] ) ? $sub_cfg['label'] : $sub_id;
				}

				wp_add_inline_script(
					'mep-global-settings',
					'window.mepGs = window.mepGs || {};'
					. 'window.mepGs.tabMeta = ' . wp_json_encode( $tab_meta ) . ';'
					. 'window.mepGs.defaultTab = ' . wp_json_encode( $first_tab ) . ';'
					. 'window.mepGs.emailParent = "email_setting_sec";'
					. 'window.mepGs.emailSubtabs = ' . wp_json_encode( array_keys( $email_subtabs ) ) . ';'
					. 'window.mepGs.emailSubMeta = ' . wp_json_encode( $email_sub_meta ) . ';'
					. 'window.mepGs.styleParent = "style_setting_sec";'
					. 'window.mepGs.styleSubtabs = ' . wp_json_encode( array_keys( $si_subtabs ) ) . ';'
					. 'window.mepGs.styleSubMeta = ' . wp_json_encode( $si_sub_meta ) . ';'
					. 'window.mepGs.sliderParent = "mp_slider_settings";'
					. 'window.mepGs.sliderSubtabs = ' . wp_json_encode( array_keys( $sc_subtabs ) ) . ';'
					. 'window.mepGs.licenseParent = "mep_settings_licensing";'
					. 'window.mepGs.licenseSubtabs = ' . wp_json_encode( array_keys( $ls_subtabs ) ) . ';'
					. 'window.mepGs.testEmail = ' . wp_json_encode( array(
						'ajaxUrl' => admin_url( 'admin-ajax.php' ),
						'nonce'   => wp_create_nonce( 'mep_send_test_email' ),
						'i18n'    => array(
							'sending' => __( 'Sending…', 'mage-eventpress' ),
							'error'   => __( 'Something went wrong. Please try again.', 'mage-eventpress' ),
						),
					) ) . ';'
					. 'window.mepGs.i18n = ' . wp_json_encode( array(
						'saved' => __( 'Settings saved successfully.', 'mage-eventpress' ),
					) ) . ';',
					'before'
				);
				?>
				<div class="mep-gs__root">
					<div class="mep-gs__wrap">
						<div class="mep-gs__overlay" id="mep-overlay"></div>

						<div class="mep-gs__sidebar" id="mep-sidebar">
							<div class="mep-gs__sb-header">
								<div class="mep-gs__sb-plugin-label"><?php echo esc_html( $label ); ?></div>
								<div class="mep-gs__sb-title">
									<span class="mep-gs__sb-dot"></span>
									<?php esc_html_e( 'Global Settings', 'mage-eventpress' ); ?>
								</div>
							</div>
							<nav class="mep-gs__sb-nav">
								<?php foreach ( $visible_tabs as $tab_id => $config ) : ?>
									<button type="button"
										class="mep-gs__nav-item<?php echo $tab_id === $first_tab ? ' mep-gs--active' : ''; ?>"
										data-tab="<?php echo esc_attr( $tab_id ); ?>">
										<span class="mep-gs__nav-icon <?php echo esc_attr( isset( $config['icon'] ) ? $config['icon'] : 'fas fa-cog' ); ?>"></span>
										<?php echo esc_html( $config['title'] ); ?>
									</button>
								<?php endforeach; ?>
							</nav>
							<div class="mep-gs__sb-footer">
								<div class="mep-gs__lic-badge <?php echo $has_pro ? 'mep-gs--pro' : ''; ?>">
									<span class="mep-gs__lic-dot"></span>
									<span class="mep-gs__lic-text">
										<?php
										echo $has_pro
											? esc_html__( 'PRO plan active', 'mage-eventpress' )
											: esc_html__( 'Free plan active', 'mage-eventpress' );
										?>
									</span>
								</div>
							</div>
						</div>

						<div class="mep-gs__main">
							<div class="mep-gs__topbar">
								<button type="button" class="mep-gs__menu-btn" id="mep-menu-btn" aria-label="<?php esc_attr_e( 'Open menu', 'mage-eventpress' ); ?>">
									<span class="fas fa-bars"></span>
								</button>
								<span class="mep-gs__topbar-title" id="mep-topbar-title">
									<?php echo esc_html( isset( $visible_tabs[ $first_tab ]['title'] ) ? $visible_tabs[ $first_tab ]['title'] : '' ); ?>
								</span>
								<span class="mep-gs__topbar-sep">&rsaquo;</span>
								<span class="mep-gs__topbar-sub" id="mep-topbar-sub">
									<?php echo esc_html( isset( $visible_tabs[ $first_tab ]['subtitle'] ) ? $visible_tabs[ $first_tab ]['subtitle'] : '' ); ?>
								</span>
								<?php if ( ! empty( $visible_tabs ) ) : ?>
									<button type="button" class="mep-gs__save-btn" id="mep-save-btn">
										<span class="fas fa-save"></span>
										<span class="mep-gs__save-text"><?php esc_html_e( 'Save Changes', 'mage-eventpress' ); ?></span>
									</button>
								<?php endif; ?>
							</div>

							<?php if ( isset( $_GET['settings-updated'] ) && 'true' === (string) wp_unslash( $_GET['settings-updated'] ) ) : ?>
								<div class="mep-gs__saved-banner" id="mep-gs-saved-banner" role="status">
									<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
									<span><?php esc_html_e( 'Settings saved successfully.', 'mage-eventpress' ); ?></span>
								</div>
							<?php endif; ?>

							<div class="mep-gs__content">
								<?php foreach ( $visible_tabs as $tab_id => $config ) : ?>
									<div class="mep-gs__tab-panel<?php echo $tab_id === $first_tab ? ' mep-gs--active' : ''; ?>"
										id="mep-tab-<?php echo esc_attr( $tab_id ); ?>">
										<?php if ( 'email_setting_sec' === $tab_id ) : ?>
											<?php MPWEM_Email_Settings_UI::render_hub( $email_subtabs, $fields ); ?>
										<?php elseif ( 'style_setting_sec' === $tab_id ) : ?>
											<?php MPWEM_Style_Icon_Settings_UI::render_hub( $si_subtabs, $fields, $sections ); ?>
										<?php elseif ( 'mp_slider_settings' === $tab_id ) : ?>
											<?php MPWEM_Slider_Carousel_Settings_UI::render_hub( $sc_subtabs, $fields, $sections ); ?>
										<?php elseif ( 'general_setting_sec' === $tab_id ) : ?>
											<?php MPWEM_General_Settings_UI::render( $fields ); ?>
										<?php elseif ( 'event_list_setting_sec' === $tab_id ) : ?>
											<?php MPWEM_Event_List_Settings_UI::render( $fields ); ?>
										<?php elseif ( 'single_event_setting_sec' === $tab_id ) : ?>
											<?php MPWEM_Single_Event_Settings_UI::render( $fields ); ?>
										<?php elseif ( 'payment_setting_sec' === $tab_id ) : ?>
											<?php MPWEM_Payment_Settings_UI::render( $fields ); ?>
										<?php elseif ( 'mep_currency_settings' === $tab_id ) : ?>
											<?php MPWEM_Currency_Settings_UI::render( $fields ); ?>
										<?php elseif ( 'mep_settings_licensing' === $tab_id ) : ?>
											<?php MPWEM_License_Status_Settings_UI::render_hub(); ?>
										<?php elseif ( 'mep_pdf_gen_settings' === $tab_id ) : ?>
											<?php MPWEM_PDF_Settings_UI::render( $fields ); ?>
										<?php elseif ( 'mep_ai_assistant_settings' === $tab_id ) : ?>
											<?php MPWEM_AI_Assistant_Settings_UI::render( $fields ); ?>
										<?php elseif ( 'mep_social_card_setting_sec' === $tab_id ) : ?>
											<?php MPWEM_Social_Card_Settings_UI::render( $fields ); ?>
										<?php elseif ( 'mep_gsheet_settings' === $tab_id ) : ?>
											<?php MPWEM_Google_Sheets_Settings_UI::render(); ?>
										<?php else : ?>
											<?php MPWEM_Modern_Section_Settings_UI::render( $tab_id, $config, $fields, $sections ); ?>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
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
            <p class="mep-ms__intro"><?php esc_html_e( 'Thank you for using Event Manager for WooCommerce. The free plugin needs no license. Enter license keys below for any Pro add-ons you use.', 'mage-eventpress' ); ?></p>
            <div class="mep_licensae_info"></div>
            <div class="mep-ms__table-wrap">
            <table class='wp-list-table widefat striped posts mep-licensing-table'>
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Plugin Name', 'mage-eventpress' ); ?></th>
                    <th width=10%><?php esc_html_e( 'Order No', 'mage-eventpress' ); ?></th>
                    <th width=15%><?php esc_html_e( 'Expire on', 'mage-eventpress' ); ?></th>
                    <th width=30%><?php esc_html_e( 'License Key', 'mage-eventpress' ); ?></th>
                    <th width=10%><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></th>
                    <th width=10%><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></th>
                </tr>
                </thead>
                <tbody>
				<?php do_action( 'mep_license_page_addon_list' ); ?>
                </tbody>
            </table>
            </div>
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
