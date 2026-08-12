<?php
	/**
	 * Event List Settings — modern card UI with toggle rows.
	 * Same option group/keys (event_list_setting_sec) — layout only.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Event_List_Settings_UI' ) ) {
		class MPWEM_Event_List_Settings_UI {

			const SECTION = 'event_list_setting_sec';

			/**
			 * Core field order for the list visibility card.
			 *
			 * @return string[]
			 */
			public static function known_field_names() {
				return array(
					'mep_event_price_show',
					'mep_date_list_in_event_listing',
					'mep_event_hide_organizer_list',
					'mep_event_hide_location_list',
					'mep_event_hide_time_list',
					'mep_event_hide_end_time_list',
					'mep_hide_event_hover_btn',
					'mep_hide_event_list_msg',
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
					if ( ! $name ) {
						continue;
					}
					if ( in_array( $name, $known, true ) ) {
						$by[ $name ] = $field;
					} else {
						$extras[] = $field;
					}
				}

				echo '<form method="post" action="options.php" class="mep-el__form">';
				settings_fields( $sec );
				?>
				<div class="mep-el">
					<div class="mep-el__header">
						<div class="mep-el__header-text">
							<h2 class="mep-el__title"><?php esc_html_e( 'Event List Settings', 'mage-eventpress' ); ?></h2>
							<p class="mep-el__subtitle"><?php esc_html_e( 'Configure how events are displayed and what information is visible in the list view.', 'mage-eventpress' ); ?></p>
						</div>
					</div>

					<div class="mep-el__card">
						<div class="mep-el__rows">
							<?php
							foreach ( $known as $name ) {
								if ( ! isset( $by[ $name ] ) ) {
									continue;
								}
								self::render_toggle_row( $by[ $name ] );
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
					if ( '' !== $val && null !== $val ) {
						return $val;
					}
				}
				return $default;
			}

			/**
			 * Yes/No toggle row matching the mockup.
			 *
			 * @param array $field Field def.
			 */
			private static function render_toggle_row( $field ) {
				$name    = isset( $field['name'] ) ? $field['name'] : '';
				if ( ! $name ) {
					return;
				}
				$label   = isset( $field['label'] ) ? $field['label'] : $name;
				$hint    = isset( $field['desc'] ) ? $field['desc'] : '';
				$default = isset( $field['default'] ) ? $field['default'] : 'no';
				$value   = self::get_opt( $name, $default );
				$on_val  = 'yes';
				$off_val = 'no';
				$checked = ( (string) $value === (string) $on_val );
				$id      = 'mep-el-' . sanitize_html_class( $name );
				?>
				<div class="mep-el__row">
					<div class="mep-el__row-text">
						<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?>
							<p class="mep-el__row-desc"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<label class="mep-el__switch">
						<input type="hidden" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $off_val ); ?>" />
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $on_val ); ?>" <?php checked( $checked ); ?> />
						<span class="mep-el__switch-ui"></span>
					</label>
				</div>
				<?php
			}

			/**
			 * Fallback for filter-added fields.
			 *
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
				$id      = 'mep-el-' . sanitize_html_class( $name );

				if ( 'select' === $type && is_array( $options ) && isset( $options['yes'], $options['no'] ) && 2 === count( $options ) ) {
					self::render_toggle_row( $field );
					return;
				}

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
