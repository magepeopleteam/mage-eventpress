<?php
	/**
	 * Currency Settings — modern card UI matching Event List Settings.
	 * Same option group/keys (mep_currency_settings) — layout only.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Currency_Settings_UI' ) ) {
		class MPWEM_Currency_Settings_UI {

			const SECTION = 'mep_currency_settings';

			/**
			 * Core field order for the currency card.
			 *
			 * @return string[]
			 */
			public static function known_field_names() {
				return array(
					'mep_currency_symbol',
					'mep_currency_position',
					'mep_currency_decimal_sep',
					'mep_currency_thousand_sep',
					'mep_currency_num_decimals',
				);
			}

			/**
			 * @param array $fields Full settings fields map.
			 */
			public static function render( $fields ) {
				$sec    = self::SECTION;
				$all    = isset( $fields[ $sec ] ) && is_array( $fields[ $sec ] ) ? $fields[ $sec ] : array();
				$by     = array();
				$extras = array();
				$known  = self::known_field_names();

				foreach ( $all as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( ! $name || 'mep_currency_info' === $name ) {
						continue;
					}
					if ( in_array( $name, $known, true ) ) {
						$by[ $name ] = $field;
					} else {
						$extras[] = $field;
					}
				}

				$has_woo = class_exists( 'MPWEM_Global_Function' ) && MPWEM_Global_Function::has_woocommerce();

				echo '<form method="post" action="options.php" class="mep-el__form">';
				settings_fields( $sec );
				?>
				<div class="mep-el mep-cu">
					<div class="mep-el__header">
						<div class="mep-el__header-text">
							<h2 class="mep-el__title"><?php esc_html_e( 'Currency Settings', 'mage-eventpress' ); ?></h2>
							<p class="mep-el__subtitle"><?php esc_html_e( 'Set how prices and currency symbols appear across your events.', 'mage-eventpress' ); ?></p>
						</div>
					</div>

					<div class="mep-cu__notice <?php echo $has_woo ? 'mep-cu__notice--info' : 'mep-cu__notice--warn'; ?>">
						<span class="mep-cu__notice-icon">
							<i class="fas <?php echo $has_woo ? 'fa-info-circle' : 'fa-exclamation-triangle'; ?>"></i>
						</span>
						<div class="mep-cu__notice-body">
							<?php if ( $has_woo ) : ?>
								<strong><?php esc_html_e( 'WooCommerce is active', 'mage-eventpress' ); ?></strong>
								<p><?php esc_html_e( 'Currency display is controlled by WooCommerce settings. The settings below are used as a fallback when WooCommerce is deactivated.', 'mage-eventpress' ); ?></p>
							<?php else : ?>
								<strong><?php esc_html_e( 'WooCommerce is not active', 'mage-eventpress' ); ?></strong>
								<p><?php esc_html_e( 'These currency settings are used for price display and the native checkout flow.', 'mage-eventpress' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<div class="mep-el__card">
						<div class="mep-el__rows">
							<?php
							foreach ( $known as $name ) {
								if ( ! isset( $by[ $name ] ) ) {
									continue;
								}
								self::render_field( $by[ $name ] );
							}
							foreach ( $extras as $field ) {
								self::render_field( $field );
							}
							?>
						</div>
					</div>
				</div>
				<div style="display:none;"><?php submit_button(); ?></div>
				</form>
				<?php
			}

			/**
			 * @param string $key     Field name.
			 * @param mixed  $default Default.
			 * @return mixed
			 */
			private static function get_opt( $key, $default = '' ) {
				$options = get_option( self::SECTION, array() );
				if ( is_array( $options ) && array_key_exists( $key, $options ) ) {
					$val = $options[ $key ];
					if ( is_array( $val ) ) {
						return $val;
					}
					// Allow empty string for thousand separator.
					if ( null !== $val ) {
						return $val;
					}
				}
				return $default;
			}

			/**
			 * @param array $field Field def.
			 */
			private static function render_field( $field ) {
				$name = isset( $field['name'] ) ? $field['name'] : '';
				if ( ! $name ) {
					return;
				}
				$type    = isset( $field['type'] ) ? $field['type'] : 'text';
				$label   = isset( $field['label'] ) ? $field['label'] : $name;
				$hint    = isset( $field['desc'] ) ? $field['desc'] : '';
				$default = isset( $field['default'] ) ? $field['default'] : '';
				$value   = self::get_opt( $name, $default );
				$options = isset( $field['options'] ) ? $field['options'] : array();
				$id      = 'mep-cu-' . sanitize_html_class( $name );

				if ( 'select' === $type && is_array( $options ) ) {
					?>
					<div class="mep-el__row mep-el__row--stack">
						<div class="mep-el__row-text">
							<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
							<?php if ( $hint ) : ?>
								<p class="mep-el__row-desc"><?php echo esc_html( $hint ); ?></p>
							<?php endif; ?>
						</div>
						<select class="mep-el__select" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>">
							<?php foreach ( $options as $k => $lab ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( $lab ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php
					return;
				}
				?>
				<div class="mep-el__row mep-el__row--stack">
					<div class="mep-el__row-text">
						<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?>
							<p class="mep-el__row-desc"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<input type="text" class="mep-el__input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>" />
				</div>
				<?php
			}
		}
	}
