<?php
	/**
	 * Single Event Settings — modern card UI with toggle rows.
	 * Same option group/keys (single_event_setting_sec) — layout only.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Single_Event_Settings_UI' ) ) {
		class MPWEM_Single_Event_Settings_UI {

			const SECTION = 'single_event_setting_sec';

			/**
			 * Core field order for the single-event card.
			 *
			 * @return string[]
			 */
			public static function known_field_names() {
				return array(
					'mep_enable_speaker_list',
					'mep_show_product_cat_in_event',
					'mep_global_single_template',
					'mep_event_product_type',
					'mep_event_hide_date_from_details',
					'mep_event_hide_time_from_details',
					'mep_event_hide_location_from_details',
					'mep_event_hide_total_seat_from_details',
					'mep_event_hide_org_from_details',
					'mep_event_hide_address_from_details',
					'mep_event_hide_event_schedule_details',
					'mep_event_hide_share_this_details',
					'mep_event_hide_calendar_details',
					'mep_enable_description_read_more',
					'mep_description_read_more_word_limit',
					'mep_event_hide_description_title',
					'mep_event_hide_left_sidebar_title',
					'mep_event_hide_time',
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
				<div class="mep-el mep-se">
					<div class="mep-el__header">
						<div class="mep-el__header-text">
							<h2 class="mep-el__title"><?php esc_html_e( 'Single Event Settings', 'mage-eventpress' ); ?></h2>
							<p class="mep-el__subtitle"><?php esc_html_e( 'Control the event details page layout and which information is visible.', 'mage-eventpress' ); ?></p>
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
					if ( '' !== $val && null !== $val ) {
						return $val;
					}
				}
				return $default;
			}

			/**
			 * Yes/No toggle row.
			 *
			 * @param array  $field   Field def.
			 * @param string $on_val  Stored value when ON.
			 * @param string $off_val Stored value when OFF.
			 */
			private static function render_toggle_row( $field, $on_val = 'yes', $off_val = 'no' ) {
				$name = isset( $field['name'] ) ? $field['name'] : '';
				if ( ! $name ) {
					return;
				}
				$label   = isset( $field['label'] ) ? $field['label'] : $name;
				$hint    = isset( $field['desc'] ) ? $field['desc'] : '';
				$default = isset( $field['default'] ) ? $field['default'] : $off_val;
				$value   = self::get_opt( $name, $default );
				$checked = ( (string) $value === (string) $on_val );
				$id      = 'mep-se-' . sanitize_html_class( $name );
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
			 * Event page template picker with screenshot thumbnails.
			 *
			 * @param array $field Field def.
			 */
			private static function render_template_picker( $field ) {
				$name    = isset( $field['name'] ) ? $field['name'] : 'mep_global_single_template';
				$label   = isset( $field['label'] ) ? $field['label'] : __( 'Event Page Template', 'mage-eventpress' );
				$hint    = isset( $field['desc'] ) ? $field['desc'] : '';
				$default = isset( $field['default'] ) ? $field['default'] : 'default-theme.php';
				$value   = (string) self::get_opt( $name, $default );
				$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
				if ( empty( $options ) && function_exists( 'mep_event_template_name' ) ) {
					$options = mep_event_template_name();
				}
				if ( empty( $options ) ) {
					$options = array(
						'default-theme.php' => __( 'Default Theme', 'mage-eventpress' ),
						'smart.php'         => __( 'Smart Theme', 'mage-eventpress' ),
						'virtual.php'       => __( 'Virtual Event', 'mage-eventpress' ),
					);
				}
				if ( ! isset( $options[ $value ] ) ) {
					$value = $default;
				}
				$id      = 'mep-se-' . sanitize_html_class( $name );
				$shot_dir = trailingslashit( MPWEM_PLUGIN_DIR ) . 'templates/screenshot/';
				$shot_url = trailingslashit( MPWEM_PLUGIN_URL ) . 'templates/screenshot/';
				?>
				<div class="mep-el__row mep-el__row--stack mep-se__template-row">
					<div class="mep-el__row-text">
						<span class="mep-el__row-label" id="<?php echo esc_attr( $id ); ?>-label"><?php echo esc_html( $label ); ?></span>
						<?php if ( $hint ) : ?>
							<p class="mep-el__row-desc"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" />
					<div class="mep-se__templates" role="listbox" aria-labelledby="<?php echo esc_attr( $id ); ?>-label">
						<?php foreach ( $options as $file => $lab ) :
							$file     = (string) $file;
							$active   = ( (string) $value === $file );
							$slug     = preg_replace( '/\.php$/i', '', basename( $file ) );
							$webp     = $slug . '.webp';
							$png      = $slug . '.png';
							$jpg      = $slug . '.jpg';
							$img      = '';
							if ( file_exists( $shot_dir . $webp ) ) {
								$img = $shot_url . $webp;
							} elseif ( file_exists( $shot_dir . $png ) ) {
								$img = $shot_url . $png;
							} elseif ( file_exists( $shot_dir . $jpg ) ) {
								$img = $shot_url . $jpg;
							}
							$thumb_class = 'mep-se__template-thumb' . ( $img ? '' : ' mep-se__template-thumb--empty' );
							?>
							<button
								type="button"
								class="mep-se__template<?php echo $active ? ' is-active' : ''; ?>"
								role="option"
								aria-selected="<?php echo $active ? 'true' : 'false'; ?>"
								data-mep-template="<?php echo esc_attr( $file ); ?>"
							>
								<span class="<?php echo esc_attr( $thumb_class ); ?>">
									<?php if ( $img ) : ?>
										<img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy" />
									<?php else : ?>
										<span class="mep-se__template-fallback" aria-hidden="true"><?php echo esc_html( strtoupper( substr( $slug, 0, 1 ) ) ); ?></span>
									<?php endif; ?>
								</span>
								<span class="mep-se__template-name"><?php echo esc_html( trim( $lab ) ); ?></span>
								<span class="mep-se__template-badge"><?php esc_html_e( 'Active', 'mage-eventpress' ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
			}

			/**
			 * Render a field as toggle or select/text row.
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
				$id      = 'mep-se-' . sanitize_html_class( $name );

				// Special case: labels are inverted (yes => No, no => Yes) but stored values stay yes/no.
				// Toggle ON = virtual product (stored yes), matching the field label.
				if ( 'mep_event_product_type' === $name ) {
					self::render_toggle_row( $field, 'yes', 'no' );
					return;
				}

				if ( 'mep_global_single_template' === $name ) {
					self::render_template_picker( $field );
					return;
				}

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
					<input
						type="<?php echo esc_attr( 'number' === $type ? 'number' : 'text' ); ?>"
						class="mep-el__input"
						id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>"
						value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>"
						<?php if ( 'number' === $type && isset( $field['min'] ) ) : ?>min="<?php echo esc_attr( $field['min'] ); ?>"<?php endif; ?>
						<?php if ( 'number' === $type && isset( $field['max'] ) ) : ?>max="<?php echo esc_attr( $field['max'] ); ?>"<?php endif; ?>
						<?php if ( 'number' === $type && isset( $field['step'] ) ) : ?>step="<?php echo esc_attr( $field['step'] ); ?>"<?php endif; ?>
					/>
				</div>
				<?php
			}
		}
	}
