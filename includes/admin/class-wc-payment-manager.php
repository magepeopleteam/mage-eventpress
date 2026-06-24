<?php
/**
 * WooCommerce Payment Methods Manager for MageEventPress.
 *
 * Renders every WooCommerce payment gateway's OWN native settings form inline,
 * inside the plugin's WooCommerce settings tab. Each gateway's fields are
 * produced by WooCommerce itself (generate_settings_html / get_form_fields)
 * and saved through the gateway's own process_admin_options(). Nothing is
 * re-implemented — this is WooCommerce's real configuration, embedded inline
 * (no iframe, no React dependency).
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'MPWEM_WC_Payment_Manager' ) ) :

	class MPWEM_WC_Payment_Manager {

		private static $instance = null;

		public static function instance(): self {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ], 20 );
			add_action( 'wp_ajax_mep_wc_save_gateway', [ $this, 'ajax_save_gateway' ] );
			add_action( 'wp_ajax_mep_wc_toggle_gateway', [ $this, 'ajax_toggle_gateway' ] );
			add_action( 'wp_ajax_mep_toggle_builtin_gateway', [ $this, 'ajax_toggle_builtin_gateway' ] );
		}

		// ---------------------------------------------------------------
		// Assets
		// ---------------------------------------------------------------

		public function enqueue_assets( string $hook ): void {
			unset( $hook );

			// Load on the Global Payment Settings page AND the event edit screens
			// (classic post editor + the custom event edit page), since the same
			// WooCommerce Payment Methods manager is reused inside the event-level
			// Payment Configuration modal.
			$screen  = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			$allowed = array( 'mep_events_page_mep_event_settings_page', 'mep_events', 'mep_events_page_mpwem_event_edit' );
			if ( ! $screen || ! in_array( $screen->id, $allowed, true ) ) {
				return;
			}

			// WooCommerce admin styling + the scripts its native fields rely on.
			if ( function_exists( 'WC' ) ) {
				wp_enqueue_style( 'woocommerce_admin_styles' );
				wp_enqueue_script( 'wc-enhanced-select' );
				wp_enqueue_script( 'wc-jquery-tiptip' );
			}

			$js_path = MPWEM_PLUGIN_DIR . '/assets/admin/wc-payment-manager.js';
			$js_ver  = file_exists( $js_path ) ? (string) filemtime( $js_path ) : MPWEM_PLUGIN_VERSION;

			wp_enqueue_script(
				'mep-wc-payment-manager',
				MPWEM_PLUGIN_URL . '/assets/admin/wc-payment-manager.js',
				[ 'jquery' ],
				$js_ver,
				true
			);
			wp_localize_script(
				'mep-wc-payment-manager',
				'mepWcPaymentManager',
				[
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'mep_wc_payment_manager' ),
					'i18n'    => [
						'saving'   => __( 'Saving…', 'mage-eventpress' ),
						'saved'    => __( 'Saved!', 'mage-eventpress' ),
						'error'    => __( 'An error occurred. Please try again.', 'mage-eventpress' ),
						'enabled'  => __( 'Enabled', 'mage-eventpress' ),
						'disabled' => __( 'Disabled', 'mage-eventpress' ),
						'configure'=> __( 'Configure', 'mage-eventpress' ),
						'close'    => __( 'Close', 'mage-eventpress' ),
					],
				]
			);
		}

		// ---------------------------------------------------------------
		// Gateway collection (includes suppressed ones, e.g. PayPal Standard)
		// ---------------------------------------------------------------

		private function get_all_gateways(): array {
			$wc_defaults = [ 'WC_Gateway_BACS', 'WC_Gateway_Cheque', 'WC_Gateway_COD', 'WC_Gateway_Paypal' ];
			$gateway_classes = apply_filters( 'woocommerce_payment_gateways', $wc_defaults );

			$loaded = WC()->payment_gateways()->payment_gateways();

			$gateways = [];
			foreach ( $loaded as $g ) {
				if ( $g instanceof WC_Payment_Gateway ) {
					$gateways[ $g->id ] = $g;
				}
			}
			foreach ( $gateway_classes as $class ) {
				if ( ! is_string( $class ) || ! class_exists( $class ) ) {
					continue;
				}
				$already = false;
				foreach ( $gateways as $g ) {
					if ( $g instanceof $class ) {
						$already = true;
						break;
					}
				}
				if ( ! $already ) {
					$instance = new $class();
					if ( $instance instanceof WC_Payment_Gateway && ! isset( $gateways[ $instance->id ] ) ) {
						$gateways[ $instance->id ] = $instance;
					}
				}
			}

			// Respect WooCommerce's saved gateway order.
			$order = (array) get_option( 'woocommerce_gateway_order', [] );
			if ( ! empty( $order ) ) {
				uasort(
					$gateways,
					static function ( $a, $b ) use ( $order ) {
						$pa = isset( $order[ $a->id ] ) ? (int) $order[ $a->id ] : 999;
						$pb = isset( $order[ $b->id ] ) ? (int) $order[ $b->id ] : 999;
						return $pa <=> $pb;
					}
				);
			}

			return $gateways;
		}

		private function get_gateway( string $gateway_id ): ?WC_Payment_Gateway {
			$gateways = $this->get_all_gateways();
			return $gateways[ $gateway_id ] ?? null;
		}

		private function verify_request(): void {
			check_ajax_referer( 'mep_wc_payment_manager', 'nonce' );
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error( __( 'Permission denied.', 'mage-eventpress' ), 403 );
			}
			if ( ! class_exists( 'WooCommerce' ) ) {
				wp_send_json_error( __( 'WooCommerce is not active.', 'mage-eventpress' ) );
			}
		}

		// ---------------------------------------------------------------
		// AJAX: save one gateway's native form (process_admin_options)
		// ---------------------------------------------------------------

		public function ajax_save_gateway(): void {
			$this->verify_request();

			$gateway_id = sanitize_key( $_POST['gateway_id'] ?? '' );
			$gateway    = $this->get_gateway( $gateway_id );
			if ( ! $gateway ) {
				wp_send_json_error( __( 'Gateway not found.', 'mage-eventpress' ) );
			}

			// process_admin_options() reads $_POST keyed as woocommerce_{id}_{field};
			// our JS submits the native form fields under exactly those names.
			$gateway->process_admin_options();

			$errors = $gateway->get_errors();
			if ( ! empty( $errors ) ) {
				wp_send_json_error( implode( ' ', array_map( 'wp_strip_all_tags', $errors ) ) );
			}

			do_action( 'woocommerce_update_options_payment_gateways_' . $gateway->id );
			if ( WC()->payment_gateways() ) {
				WC()->payment_gateways()->init();
			}

			$refreshed = $this->get_gateway( $gateway_id );
			wp_send_json_success(
				[
					'message' => __( 'Settings saved successfully!', 'mage-eventpress' ),
					'enabled' => ( $refreshed && $refreshed->enabled === 'yes' ) ? 'yes' : 'no',
				]
			);
		}

		// ---------------------------------------------------------------
		// AJAX: quick enable/disable from the card header
		// ---------------------------------------------------------------

		public function ajax_toggle_gateway(): void {
			$this->verify_request();

			$gateway_id = sanitize_key( $_POST['gateway_id'] ?? '' );
			$enabled    = ( isset( $_POST['enabled'] ) && $_POST['enabled'] === 'yes' ) ? 'yes' : 'no';
			if ( empty( $gateway_id ) ) {
				wp_send_json_error( __( 'Invalid gateway.', 'mage-eventpress' ) );
			}

			$option_key = 'woocommerce_' . $gateway_id . '_settings';
			$opts       = get_option( $option_key, [] );
			if ( ! is_array( $opts ) ) {
				$opts = [];
			}
			$opts['enabled'] = $enabled;
			if ( $enabled === 'yes' ) {
				$opts['_should_load'] = 'yes';
			}
			update_option( $option_key, $opts );

			if ( WC()->payment_gateways() ) {
				WC()->payment_gateways()->init();
			}

			// WooCommerce can refuse to enable a gateway even after the option is
			// saved — e.g. PayPal Standard force-disables itself for store currencies
			// it doesn't support (BDT, etc.). Read the gateway back and report its
			// REAL state instead of the requested one, so the toggle never shows a
			// method as enabled that checkout cannot actually use.
			$refreshed    = $this->get_gateway( $gateway_id );
			$real_enabled = ( $refreshed && $refreshed->enabled === 'yes' ) ? 'yes' : 'no';
			$response     = [ 'enabled' => $real_enabled ];

			if ( 'yes' === $enabled && 'yes' !== $real_enabled ) {
				// The enable did not take effect — don't leave a misleading saved
				// flag that the banner/checkout would have to reconcile later.
				$opts['enabled'] = 'no';
				update_option( $option_key, $opts );

				$name     = $refreshed ? ( $refreshed->get_method_title() ?: $refreshed->get_title() ) : $gateway_id;
				$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '';
				$response['notice'] = $currency
					? sprintf(
						/* translators: 1: gateway name, 2: store currency code */
						__( '%1$s can\'t be enabled for your store currency (%2$s). Choose a gateway that supports %2$s.', 'mage-eventpress' ),
						$name,
						$currency
					)
					: sprintf(
						/* translators: %s: gateway name */
						__( '%s can\'t be enabled for your store right now.', 'mage-eventpress' ),
						$name
					);
			}

			wp_send_json_success( $response );
		}

		// ---------------------------------------------------------------
		// Built-in gateway specs (always shown regardless of WooCommerce)
		// ---------------------------------------------------------------

		private function builtin_gateway_specs(): array {
			return [
				'bacs' => [
					'title' => __( 'Bank Transfer', 'mage-eventpress' ),
					'desc'  => __( 'Allow customers to pay via direct bank transfer.', 'mage-eventpress' ),
				],
				'cod'  => [
					'title' => __( 'Cash on Delivery', 'mage-eventpress' ),
					'desc'  => __( 'Allow customers to pay with cash upon delivery or at the event.', 'mage-eventpress' ),
				],
			];
		}

		// ---------------------------------------------------------------
		// Render — called from the Payment Configuration modal
		// ---------------------------------------------------------------

		public function render(): void {
			$wc_active     = class_exists( 'WooCommerce' );
			$builtin_specs = $this->builtin_gateway_specs();
			$stored        = get_option( 'mep_builtin_gateways', [] );

			// When WooCommerce is active, get its registered gateway objects.
			$wc_gateways = $wc_active ? $this->get_all_gateways() : [];

			// Build a unified render list: WC gateways first, then inject any
			// built-in spec that WooCommerce hasn't registered, so BACS and COD
			// are always present even on a fresh WooCommerce install or when
			// WooCommerce is not active at all.
			$render_items = [];

			foreach ( $wc_gateways as $id => $gw ) {
				$render_items[ $id ] = [
					'id'      => $id,
					'title'   => $gw->get_method_title() ?: $gw->get_title(),
					'desc'    => $gw->get_method_description() ?: $gw->get_description(),
					'enabled' => $gw->enabled === 'yes',
					'source'  => 'wc',
					'gw_obj'  => $gw,
				];
			}

			foreach ( $builtin_specs as $id => $spec ) {
				if ( isset( $render_items[ $id ] ) ) {
					continue; // Already covered by WooCommerce.
				}
				// Prefer the WooCommerce option value (accurate after WC is activated
				// mid-session), fall back to plugin's own storage.
				$wc_opt  = get_option( 'woocommerce_' . $id . '_settings', [] );
				$enabled = isset( $wc_opt['enabled'] ) ? $wc_opt['enabled'] === 'yes'
						 : ( isset( $stored[ $id ] ) && $stored[ $id ] === 'yes' );

				$render_items[ $id ] = [
					'id'      => $id,
					'title'   => $spec['title'],
					'desc'    => $spec['desc'],
					'enabled' => $enabled,
					'source'  => 'builtin',
					'gw_obj'  => null,
				];
			}

			if ( empty( $render_items ) ) {
				return;
			}
			?>
			<div class="mep-wc-payment-manager">
				<div class="mep-wc-pm-bar">
					<h3 class="mep-wc-pm-heading"><?php esc_html_e( 'Payment Methods', 'mage-eventpress' ); ?></h3>
					<?php if ( $wc_active ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ); ?>"
					   class="button button-small mep-wc-pm-wc-link" target="_blank">
						<?php esc_html_e( 'Open in WooCommerce', 'mage-eventpress' ); ?>
						<span class="dashicons dashicons-external" style="font-size:14px;line-height:1.4;vertical-align:middle;"></span>
					</a>
					<?php endif; ?>
				</div>

				<?php foreach ( $render_items as $item ) :
					$is_enabled = $item['enabled'];
				?>
				<div class="mep-gw-card <?php echo $is_enabled ? 'is-enabled' : 'is-disabled'; ?>"
				     data-gateway-id="<?php echo esc_attr( $item['id'] ); ?>"
				     data-gateway-source="<?php echo esc_attr( $item['source'] ); ?>">
					<div class="mep-gw-head">
						<div class="mep-gw-head-main">
							<label class="mep-gw-toggle" title="<?php esc_attr_e( 'Enable / disable', 'mage-eventpress' ); ?>">
								<input type="checkbox" class="mep-gw-toggle-input"
								       data-gateway-id="<?php echo esc_attr( $item['id'] ); ?>"
								       <?php checked( $is_enabled ); ?>>
								<span class="mep-gw-toggle-slider"></span>
							</label>
							<span class="mep-gw-title"><?php echo esc_html( $item['title'] ); ?></span>
							<span class="mep-gw-badge"><?php echo $is_enabled ? esc_html__( 'Enabled', 'mage-eventpress' ) : esc_html__( 'Disabled', 'mage-eventpress' ); ?></span>
						</div>
						<?php if ( $item['gw_obj'] ) : ?>
						<button type="button" class="button mep-gw-configure-btn"><?php esc_html_e( 'Configure', 'mage-eventpress' ); ?></button>
						<?php endif; ?>
					</div>

					<?php if ( $item['desc'] ) : ?>
					<div class="mep-gw-desc"><?php echo wp_kses_post( wpautop( $item['desc'] ) ); ?></div>
					<?php endif; ?>

					<?php if ( $item['gw_obj'] ) : ?>
					<div class="mep-gw-body" style="display:none;">
						<form class="mep-gw-form" data-gateway-id="<?php echo esc_attr( $item['id'] ); ?>">
							<table class="form-table mep-gw-form-table">
								<?php
								echo $item['gw_obj']->generate_settings_html( $item['gw_obj']->get_form_fields(), false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</table>
							<div class="mep-gw-form-footer">
								<button type="submit" class="button button-primary mep-gw-save-btn"><?php esc_html_e( 'Save changes', 'mage-eventpress' ); ?></button>
								<span class="mep-gw-status"></span>
							</div>
						</form>
					</div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>

				<?php $this->render_styles(); ?>
			</div>
			<?php
		}

		// ---------------------------------------------------------------
		// AJAX: toggle a built-in gateway (works without WooCommerce)
		// ---------------------------------------------------------------

		public function ajax_toggle_builtin_gateway(): void {
			check_ajax_referer( 'mep_wc_payment_manager', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error( __( 'Permission denied.', 'mage-eventpress' ), 403 );
			}

			$gateway_id = sanitize_key( $_POST['gateway_id'] ?? '' );
			$enabled    = ( isset( $_POST['enabled'] ) && $_POST['enabled'] === 'yes' ) ? 'yes' : 'no';

			if ( empty( $gateway_id ) ) {
				wp_send_json_error( __( 'Invalid gateway.', 'mage-eventpress' ) );
			}

			// Persist in the plugin's own store so state survives without WooCommerce.
			$stored = get_option( 'mep_builtin_gateways', [] );
			if ( ! is_array( $stored ) ) {
				$stored = [];
			}
			$stored[ $gateway_id ] = $enabled;
			update_option( 'mep_builtin_gateways', $stored );

			// When WooCommerce is active, mirror the change in WooCommerce settings
			// so enabling from here is also reflected in WC → Settings → Payments.
			if ( class_exists( 'WooCommerce' ) ) {
				$option_key = 'woocommerce_' . $gateway_id . '_settings';
				$opts       = get_option( $option_key, [] );
				if ( ! is_array( $opts ) ) {
					$opts = [];
				}
				$opts['enabled'] = $enabled;
				if ( $enabled === 'yes' ) {
					$opts['_should_load'] = 'yes';
				}
				update_option( $option_key, $opts );
				if ( WC()->payment_gateways() ) {
					WC()->payment_gateways()->init();
				}
			}

			wp_send_json_success( [ 'enabled' => $enabled ] );
		}

		private function render_styles(): void {
			?>
			<style>
				/* Full-width fallback for the no-JS path. NOTE: we deliberately do
				   NOT force the <tr> to display:block!important — that would beat
				   the payment-tab JS's .hide() and leak the panel into other tabs.
				   We only collapse the empty label cell and widen the content cell;
				   the tab JS keeps full control of the row's show/hide. */
				.mp_settings_panel table tr:has(.mep-wc-payment-manager) > th { display:none !important; }
				.mp_settings_panel table tr:has(.mep-wc-payment-manager) > td { width:100% !important; box-sizing:border-box; padding:0 !important; }

				.mep-wc-payment-manager { display:block; width:100%; box-sizing:border-box; margin-top:24px; }
				.mep-wc-pm-bar { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
				.mep-wc-pm-heading { margin:0; font-size:15px; }
				.mep-wc-pm-wc-link { font-size:12px; font-weight:normal; }

				.mep-gw-card { border:1px solid #dcdcde; border-radius:8px; background:#fff; margin-bottom:14px; overflow:hidden; }
				.mep-gw-card.is-enabled { border-left:3px solid #2271b1; }
				.mep-gw-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
				.mep-gw-head-main { display:flex; align-items:center; gap:12px; }
				.mep-gw-title { font-size:14px; font-weight:600; color:#1d2327; }
				.mep-gw-badge { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.3px; padding:2px 8px; border-radius:9px; background:#f0f0f1; color:#646970; }
				.mep-gw-card.is-enabled .mep-gw-badge { background:#e6f4ea; color:#0a7c2f; }
				.mep-gw-desc { padding:0 16px 12px; color:#50575e; font-size:13px; }
				.mep-gw-desc p { margin:0 0 6px; }

				.mep-gw-body { padding:6px 16px 16px; border-top:1px solid #f0f0f1; background:#fbfbfc; }
				.mep-gw-form-table { width:100%; background:transparent; }
				.mep-gw-form-table th { width:200px; padding:14px 10px 14px 0; background:transparent; font-weight:600; vertical-align:top; }
				.mep-gw-form-table td { padding:12px 0; background:transparent; }
				.mep-gw-form-table input[type=text], .mep-gw-form-table input[type=password],
				.mep-gw-form-table input[type=email], .mep-gw-form-table input[type=number],
				.mep-gw-form-table textarea, .mep-gw-form-table select { min-width:320px; max-width:100%; }
				.mep-gw-form-footer { display:flex; align-items:center; gap:12px; margin-top:8px; padding-top:12px; border-top:1px solid #f0f0f1; }
				.mep-gw-status { font-size:13px; }
				.mep-gw-status.is-success { color:#0a7c2f; }
				.mep-gw-status.is-error { color:#d63638; }

				/* Toggle switch */
				.mep-gw-toggle { position:relative; display:inline-block; width:42px; height:24px; cursor:pointer; flex:0 0 auto; }
				/* Fully neutralise the native checkbox so WooCommerce/WP core checkbox
				   styles (native appearance + the :checked ::before checkmark) can never
				   flash into view during the toggle animation. The slider is the only
				   visual; the input stays on top, invisible, for click + a11y. */
				.mep-gw-toggle-input {
					position:absolute; inset:0; margin:0; padding:0;
					width:100%; height:100%; min-width:0 !important; min-height:0 !important;
					opacity:0 !important; cursor:pointer; z-index:1;
					-webkit-appearance:none !important; -moz-appearance:none !important; appearance:none !important;
					background:none !important; border:none !important; box-shadow:none !important;
				}
				.mep-gw-toggle-input::before,
				.mep-gw-toggle-input::after { content:none !important; display:none !important; }
				.mep-gw-toggle-slider { position:absolute; inset:0; background:#b5b5ba; border-radius:24px; transition:background .2s; }
				.mep-gw-toggle-slider::before { content:''; position:absolute; height:18px; width:18px; left:3px; top:3px; background:#fff; border-radius:50%; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.3); }
				.mep-gw-toggle-input:checked + .mep-gw-toggle-slider { background:#2271b1; }
				.mep-gw-toggle-input:checked + .mep-gw-toggle-slider::before { transform:translateX(18px); }
				.mep-gw-toggle-input:disabled + .mep-gw-toggle-slider { opacity:.5; cursor:not-allowed; }
			</style>
			<?php
		}
	}

	// Always instantiate so the admin_enqueue_scripts + AJAX hooks register.
	// (This file is required during plugin include, before WooCommerce has
	// loaded — gating on class_exists('WooCommerce') here would silently skip
	// hook registration. Each method guards WC availability internally.)
	MPWEM_WC_Payment_Manager::instance();

endif;
