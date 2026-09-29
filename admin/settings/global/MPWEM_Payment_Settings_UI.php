<?php
	/**
	 * Payment Settings — modern hub UI (WooCommerce / Custom Payment).
	 * Same option group/keys (payment_setting_sec) — layout only.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Payment_Settings_UI' ) ) {
		class MPWEM_Payment_Settings_UI {

			const SECTION = 'payment_setting_sec';

			/**
			 * Render Payment Settings hub.
			 *
			 * @param array $fields Settings fields map (unused; options read directly).
			 */
			public static function render( $fields = array() ) {
				$opts      = get_option( self::SECTION, array() );
				if ( ! is_array( $opts ) ) {
					$opts = array();
				}
				$wc_active = class_exists( 'WooCommerce' ) || ( class_exists( 'MPWEM_Global_Function' ) && MPWEM_Global_Function::has_woocommerce() );
				$has_pro   = function_exists( 'mep_check_plugin_installed' )
					? mep_check_plugin_installed( 'mage-eventpress-pro/woocommerce-event-manager-pro.php' )
					: false;

				echo '<form method="post" action="options.php" class="mep-pay__form">';
				settings_fields( self::SECTION );
				?>
				<div class="mep-pay">
					<div class="mep-pay__header">
						<div class="mep-pay__header-text">
							<h2 class="mep-pay__title"><?php esc_html_e( 'Payment Settings', 'mage-eventpress' ); ?></h2>
							<p class="mep-pay__subtitle"><?php esc_html_e( 'Choose how customers pay for tickets — WooCommerce checkout or custom gateways.', 'mage-eventpress' ); ?></p>
						</div>
					</div>

					<nav class="mep-pay__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Payment Settings', 'mage-eventpress' ); ?>">
						<button type="button"
							class="mep-pay__tab mep-pay--active"
							role="tab"
							aria-selected="true"
							data-pay-sub="woocommerce"
							data-pay-label="<?php esc_attr_e( 'WooCommerce', 'mage-eventpress' ); ?>">
							<span class="mep-pay__tab-icon fas fa-shopping-cart"></span>
							<span class="mep-pay__tab-label"><?php esc_html_e( 'WooCommerce', 'mage-eventpress' ); ?></span>
						</button>
						<button type="button"
							class="mep-pay__tab"
							role="tab"
							aria-selected="false"
							data-pay-sub="custom"
							data-pay-label="<?php esc_attr_e( 'Custom Payment', 'mage-eventpress' ); ?>">
							<span class="mep-pay__tab-icon fas fa-credit-card"></span>
							<span class="mep-pay__tab-label"><?php esc_html_e( 'Custom Payment', 'mage-eventpress' ); ?></span>
						</button>
					</nav>

					<div class="mep-pay__panels">
						<div class="mep-pay__panel mep-pay--active" id="mep-pay-sub-woocommerce" data-pay-sub="woocommerce" role="tabpanel">
							<?php self::render_woocommerce_panel( $opts, $wc_active ); ?>
						</div>
						<div class="mep-pay__panel" id="mep-pay-sub-custom" data-pay-sub="custom" role="tabpanel">
							<?php self::render_custom_panel( $opts, $has_pro ); ?>
						</div>
					</div>
				</div>
				<div style="display:none;"><?php submit_button(); ?></div>
				</form>
				<?php
			}

			/**
			 * @param array $opts     Saved options.
			 * @param bool  $wc_active Whether WooCommerce is active.
			 */
			private static function render_woocommerce_panel( $opts, $wc_active ) {
				$wc_on = self::opt( $opts, 'mep_enable_wc_payment', 'on' );
				?>
				<?php if ( ! $wc_active ) : ?>
					<?php self::render_woo_warning(); ?>
				<?php endif; ?>

				<div class="mep-pay__card">
					<div class="mep-pay__row">
						<div class="mep-pay__row-text">
							<label class="mep-pay__row-label" for="mep-pay-wc-enable"><?php esc_html_e( 'Use WooCommerce Checkout', 'mage-eventpress' ); ?></label>
							<p class="mep-pay__row-desc"><?php esc_html_e( 'Process ticket payments through WooCommerce.', 'mage-eventpress' ); ?></p>
						</div>
						<label class="mep-pay__switch">
							<input type="hidden" name="<?php echo esc_attr( self::SECTION ); ?>[mep_enable_wc_payment]" value="off" />
							<input type="checkbox" id="mep-pay-wc-enable" class="mep-pay__wc-enable" name="<?php echo esc_attr( self::SECTION ); ?>[mep_enable_wc_payment]" value="on" <?php checked( $wc_on, 'on' ); ?> <?php disabled( ! $wc_active ); ?> />
							<span class="mep-pay__switch-ui"></span>
						</label>
					</div>
				</div>

				<div class="mep-pay__wc-body"<?php echo ( 'on' === $wc_on && $wc_active ) ? '' : ' hidden'; ?>>
					<div class="mep-pay__card mep-pay__card--methods">
						<div class="mep-pay__card-head">
							<span class="mep-pay__card-icon"><i class="fas fa-wallet"></i></span>
							<div>
								<h3 class="mep-pay__card-title"><?php esc_html_e( 'WooCommerce Payment Methods', 'mage-eventpress' ); ?></h3>
								<p class="mep-pay__card-desc"><?php esc_html_e( 'Enable and configure gateways available at checkout.', 'mage-eventpress' ); ?></p>
							</div>
						</div>
						<div class="mep-pay__card-body">
							<?php
							if ( $wc_active && class_exists( 'MPWEM_WC_Payment_Manager' ) ) {
								MPWEM_WC_Payment_Manager::instance()->render();
							} else {
								echo '<p class="mep-pay__empty">' . esc_html__( 'Activate WooCommerce to manage payment methods here.', 'mage-eventpress' ) . '</p>';
							}
							?>
						</div>
					</div>

					<div class="mep-pay__card">
						<div class="mep-pay__card-head">
							<span class="mep-pay__card-icon"><i class="fas fa-sliders-h"></i></span>
							<div>
								<h3 class="mep-pay__card-title"><?php esc_html_e( 'Additional Settings', 'mage-eventpress' ); ?></h3>
								<p class="mep-pay__card-desc"><?php esc_html_e( 'Redirects, login requirements, and ticket confirmation rules.', 'mage-eventpress' ); ?></p>
							</div>
						</div>
						<div class="mep-pay__card-body mep-pay__rows">
							<?php
							self::select_row(
								'mep_wc_add_to_cart_redirect',
								__( 'After Add to Cart', 'mage-eventpress' ),
								__( 'Where to send customers after tickets are added to the cart.', 'mage-eventpress' ),
								array(
									'cart'     => __( 'Cart', 'mage-eventpress' ),
									'checkout' => __( 'Checkout', 'mage-eventpress' ),
								),
								self::opt( $opts, 'mep_wc_add_to_cart_redirect', 'checkout' )
							);
							self::select_row(
								'mep_wc_after_order_redirect',
								__( 'After Order Confirmation', 'mage-eventpress' ),
								__( 'Where to send customers after the order is confirmed.', 'mage-eventpress' ),
								array(
									'plugin_thankyou' => __( 'Plugin Thank You Page', 'mage-eventpress' ),
									'woo_thankyou'    => __( 'WooCommerce Thank You Page', 'mage-eventpress' ),
								),
								self::opt( $opts, 'mep_wc_after_order_redirect', 'plugin_thankyou' )
							);
							self::toggle_row(
								'mep_wc_require_login',
								__( 'Require Login to Buy', 'mage-eventpress' ),
								__( 'Customers must log in before buying tickets.', 'mage-eventpress' ),
								self::opt( $opts, 'mep_wc_require_login', '' ),
								'on',
								'off'
							);
							self::toggle_row(
								'mep_wc_show_billing_info',
								__( 'Show Billing Fields', 'mage-eventpress' ),
								__( 'Show billing fields on the WooCommerce checkout page.', 'mage-eventpress' ),
								self::opt( $opts, 'mep_wc_show_billing_info', '' ),
								'on',
								'off'
							);
							self::multicheck_row(
								'mep_wc_confirm_ticket_status',
								__( 'Confirm Tickets on Payment Status', 'mage-eventpress' ),
								__( 'Order statuses that mark tickets as confirmed.', 'mage-eventpress' ),
								array(
									'pending'    => __( 'Pending payment', 'mage-eventpress' ),
									'processing' => __( 'Processing', 'mage-eventpress' ),
									'on-hold'    => __( 'On hold', 'mage-eventpress' ),
									'completed'  => __( 'Completed', 'mage-eventpress' ),
								),
								self::opt( $opts, 'mep_wc_confirm_ticket_status', array( 'processing' => 'processing', 'completed' => 'completed' ) )
							);
							?>
						</div>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array $opts    Saved options.
			 * @param bool  $has_pro Whether Pro is installed.
			 */
			private static function render_custom_panel( $opts, $has_pro ) {
				$pp_on  = self::opt( $opts, 'mep_paypal_enable', 'off' ) === 'on';
				$st_on  = self::opt( $opts, 'mep_stripe_enable', 'off' ) === 'on';
				$off_on = self::opt( $opts, 'mep_offline_enable', 'off' ) === 'on';
				$page   = ! empty( $opts['mep_confirmation_page_id'] ) ? absint( $opts['mep_confirmation_page_id'] ) : 0;
				?>
				<div class="mep-pay__gateways">
					<?php
					self::gateway_card(
						'paypal',
						__( 'PayPal', 'mage-eventpress' ),
						$pp_on,
						$has_pro,
						true,
						'mep-paypal-configure-btn',
						'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z" fill="#003087"/><path d="M11.5 7.1c.05.27.01.59-.09.91-.98 5.05-4.35 6.79-8.65 6.79H4.95l-1.12 7.11a.64.64 0 0 0 .63.74h4.6a.64.64 0 0 0 .63-.54l.87-5.55a.64.64 0 0 1 .63-.54h1.08c3.5 0 6.23-1.42 7.03-5.52.2-.99.23-1.89.09-2.65-.48-2.6-2.58-3.41-5.63-3.41h-2.22z" fill="#0079C1"/></svg>'
					);
					self::gateway_card(
						'stripe',
						__( 'Stripe', 'mage-eventpress' ),
						$st_on,
						$has_pro,
						true,
						'mep-stripe-configure-btn',
						'<svg width="28" height="28" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="#6772E5" d="M14.07 15.11c-1.85-.43-2.61-.79-2.61-1.63 0-.79.75-1.33 1.95-1.33 1.34 0 2.87.41 4.31 1.09V8.65c-1.39-.56-2.93-.84-4.52-.84-3.8 0-6.66 1.96-6.66 5.25 0 3.73 3.32 4.96 6.03 5.61 2.05.49 2.8.92 2.8 1.8 0 .86-.87 1.48-2.3 1.48-1.57 0-3.37-.53-5.06-1.54v4.75c1.67.75 3.59 1.13 5.51 1.13 4.13 0 7-2 7-5.34-.01-3.6-3.6-4.41-6.45-5.84z"/></svg>'
					);
					self::gateway_card(
						'offline',
						__( 'Offline Payment', 'mage-eventpress' ),
						$off_on,
						true,
						false,
						'mep-offline-configure-btn',
						'<svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 19h18a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" stroke="#0f766e" stroke-width="1.6" stroke-linejoin="round"/><path d="M2 10h20M6 14h4" stroke="#0f766e" stroke-width="1.6" stroke-linecap="round"/></svg>'
					);
					?>
				</div>

				<div class="mep-pay__card" style="margin-top:18px;">
					<div class="mep-pay__card-head">
						<span class="mep-pay__card-icon"><i class="fas fa-check-circle"></i></span>
						<div>
							<h3 class="mep-pay__card-title"><?php esc_html_e( 'Booking Confirmation Page', 'mage-eventpress' ); ?></h3>
							<p class="mep-pay__card-desc"><?php esc_html_e( 'Select a page with the [mep_booking_confirmation] shortcode. After booking, customers are redirected here instead of back to the event page.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-pay__card-body">
						<?php
						wp_dropdown_pages(
							array(
								'name'              => self::SECTION . '[mep_confirmation_page_id]',
								'id'                => 'mep_confirmation_page_id',
								'selected'          => $page,
								'show_option_none'  => __( '— Default —', 'mage-eventpress' ),
								'option_none_value' => '0',
								'class'             => 'mep-pay__select',
								'echo'              => 1,
							)
						);
						?>
					</div>
				</div>
				<?php
			}

			/**
			 * WooCommerce not active notice.
			 */
			private static function render_woo_warning() {
				$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
				$btn_text     = $is_installed
					? __( 'Activate WooCommerce Now', 'mage-eventpress' )
					: __( 'Install & Activate Now', 'mage-eventpress' );
				?>
				<div class="mep-pay__notice">
					<div class="mep-pay__notice-icon"><i class="fas fa-exclamation-triangle"></i></div>
					<div class="mep-pay__notice-body">
						<strong><?php esc_html_e( 'WooCommerce is not activated', 'mage-eventpress' ); ?></strong>
						<p><?php esc_html_e( 'To use ticket-selling events and WooCommerce checkout, install and activate WooCommerce.', 'mage-eventpress' ); ?></p>
					</div>
					<button type="button" class="mep-pay__notice-btn mep-install-wc-trigger"><?php echo esc_html( $btn_text ); ?></button>
				</div>
				<?php
			}

			/**
			 * @param string $slug          paypal|stripe|offline.
			 * @param string $title         Gateway title.
			 * @param bool   $enabled       Whether enabled.
			 * @param bool   $can_configure Whether Configure is available (Pro for PayPal/Stripe).
			 * @param bool   $needs_pro     Show PRO badge when not available.
			 * @param string $btn_id        Configure button id.
			 * @param string $icon_svg      Inline SVG markup.
			 */
			private static function gateway_card( $slug, $title, $enabled, $can_configure, $needs_pro, $btn_id, $icon_svg ) {
				?>
				<div class="mep-pay__gateway mep-pay__gateway--<?php echo esc_attr( $slug ); ?><?php echo $enabled ? ' is-enabled' : ''; ?>">
					<div class="mep-pay__gateway-main">
						<span class="mep-pay__gateway-icon"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div>
							<h3 class="mep-pay__gateway-title"><?php echo esc_html( $title ); ?></h3>
							<span class="mep-pay__gateway-status<?php echo $enabled ? ' is-on' : ''; ?>">
								<?php echo $enabled ? esc_html__( 'Enabled', 'mage-eventpress' ) : esc_html__( 'Disabled', 'mage-eventpress' ); ?>
							</span>
						</div>
					</div>
					<div class="mep-pay__gateway-actions">
						<?php if ( $can_configure ) : ?>
							<button type="button" class="mep-pay__config-btn gateway-configure-btn" id="<?php echo esc_attr( $btn_id ); ?>">
								<?php esc_html_e( 'Configure', 'mage-eventpress' ); ?>
							</button>
						<?php elseif ( $needs_pro ) : ?>
							<span class="mep-pay__pro-badge" title="<?php esc_attr_e( 'Available in Pro version', 'mage-eventpress' ); ?>">PRO</span>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}

			/* ── Field helpers ───────────────────────────────── */

			/**
			 * @param array  $opts    Options.
			 * @param string $key     Key.
			 * @param mixed  $default Default.
			 * @return mixed
			 */
			private static function opt( $opts, $key, $default = '' ) {
				if ( is_array( $opts ) && array_key_exists( $key, $opts ) ) {
					$val = $opts[ $key ];
					if ( is_array( $val ) ) {
						return $val;
					}
					if ( '' !== $val && null !== $val ) {
						return $val;
					}
				}
				return $default;
			}

			private static function toggle_row( $name, $label, $hint, $value, $on_val = 'on', $off_val = 'off' ) {
				// Empty stored value for checkboxes means off.
				if ( '' === $value || null === $value ) {
					$value = $off_val;
				}
				$checked = ( (string) $value === (string) $on_val );
				$id      = 'mep-pay-' . sanitize_html_class( $name );
				?>
				<div class="mep-pay__row">
					<div class="mep-pay__row-text">
						<label class="mep-pay__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?>
							<p class="mep-pay__row-desc"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<label class="mep-pay__switch">
						<input type="hidden" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $off_val ); ?>" />
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $on_val ); ?>" <?php checked( $checked ); ?> />
						<span class="mep-pay__switch-ui"></span>
					</label>
				</div>
				<?php
			}

			private static function select_row( $name, $label, $hint, $options, $value ) {
				$id = 'mep-pay-' . sanitize_html_class( $name );
				?>
				<div class="mep-pay__row mep-pay__row--stack">
					<div class="mep-pay__row-text">
						<label class="mep-pay__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?>
							<p class="mep-pay__row-desc"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<select class="mep-pay__select" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>">
						<?php foreach ( $options as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php
			}

			private static function multicheck_row( $name, $label, $hint, $options, $value ) {
				if ( ! is_array( $value ) ) {
					$value = array();
				}
				?>
				<div class="mep-pay__row mep-pay__row--stack">
					<div class="mep-pay__row-text">
						<span class="mep-pay__row-label"><?php echo esc_html( $label ); ?></span>
						<?php if ( $hint ) : ?>
							<p class="mep-pay__row-desc"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<div class="mep-pay__checks">
						<input type="hidden" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="" />
						<?php foreach ( $options as $key => $lab ) : ?>
							<label class="mep-pay__check">
								<input type="checkbox" name="<?php echo esc_attr( self::SECTION . '[' . $name . '][' . $key . ']' ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( isset( $value[ $key ] ) && (string) $value[ $key ] === (string) $key ); ?> />
								<span><?php echo esc_html( $lab ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
			}
		}
	}
