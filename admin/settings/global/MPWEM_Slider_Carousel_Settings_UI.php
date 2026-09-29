<?php
	/**
	 * Slider & Carousel Settings — single-page modern card UI.
	 *
	 * Option group ids stay mp_slider_settings and carousel_setting_sec so saved data is unchanged.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Slider_Carousel_Settings_UI' ) ) {
		class MPWEM_Slider_Carousel_Settings_UI {

			/**
			 * Render Slider & Carousel page (two cards, no subtabs).
			 *
			 * @param array $subtabs  Unused (kept for call-site compatibility).
			 * @param array $fields   Settings fields map.
			 * @param array $sections Unused (kept for call-site compatibility).
			 */
			public static function render_hub( $subtabs, $fields, $sections = array() ) {
				$slider_fields   = isset( $fields['mp_slider_settings'] ) && is_array( $fields['mp_slider_settings'] ) ? $fields['mp_slider_settings'] : array();
				$carousel_fields = isset( $fields['carousel_setting_sec'] ) && is_array( $fields['carousel_setting_sec'] ) ? $fields['carousel_setting_sec'] : array();
				?>
				<div class="mep-sc">
					<div class="mep-sc__header">
						<div class="mep-sc__header-text">
							<h2 class="mep-sc__title"><?php esc_html_e( 'Slider & Carousel', 'mage-eventpress' ); ?></h2>
							<p class="mep-sc__subtitle"><?php esc_html_e( 'Configure event image sliders and carousel display options.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-sc__page">
						<div class="mep-sc__cards-row">
							<?php self::render_slider_form( $slider_fields ); ?>
							<?php self::render_carousel_form( $carousel_fields ); ?>
						</div>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array $fields Slider fields.
			 */
			private static function render_slider_form( $fields ) {
				$sec = 'mp_slider_settings';
				echo '<form method="post" action="options.php" class="mep-sc__form mep-sc__form--slider" data-sc-group="' . esc_attr( $sec ) . '">';
				settings_fields( $sec );
				self::render_slider_card( $fields );
				echo '<div style="display:none;">';
				submit_button();
				echo '</div></form>';
			}

			/**
			 * @param array $fields Carousel fields.
			 */
			private static function render_carousel_form( $fields ) {
				$sec = 'carousel_setting_sec';
				echo '<form method="post" action="options.php" class="mep-sc__form mep-sc__form--carousel" data-sc-group="' . esc_attr( $sec ) . '">';
				settings_fields( $sec );
				self::render_carousel_card( $fields );
				echo '<div style="display:none;">';
				submit_button();
				echo '</div></form>';
			}

			/**
			 * @param string $sec     Option group.
			 * @param string $key     Field name.
			 * @param mixed  $default Default.
			 * @return mixed
			 */
			private static function get_opt( $sec, $key, $default = '' ) {
				$options = get_option( $sec, array() );
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
			 * @param array $fields Field list.
			 * @return array
			 */
			private static function index_fields( $fields ) {
				$out = array();
				foreach ( $fields as $field ) {
					if ( ! empty( $field['name'] ) ) {
						$out[ $field['name'] ] = $field;
					}
				}
				return $out;
			}

			/**
			 * Slider settings card.
			 *
			 * @param array $fields Slider fields.
			 */
			private static function render_slider_card( $fields ) {
				$sec = 'mp_slider_settings';
				$by  = self::index_fields( $fields );

				$order = array(
					'slider_type',
					'slider_style',
					'indicator_visible',
					'indicator_type',
					'showcase_visible',
					'showcase_position',
					'popup_image_indicator',
					'popup_icon_indicator',
					'slider_height',
				);
				$known  = $order;
				$extras = array();
				foreach ( $fields as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( $name && ! in_array( $name, $known, true ) ) {
						$extras[] = $field;
					}
				}
				?>
				<div class="mep-sc__card">
					<div class="mep-sc__card-head">
						<span class="mep-sc__card-icon"><i class="fas fa-photo-video"></i></span>
						<div>
							<h3 class="mep-sc__card-title"><?php esc_html_e( 'Slider Settings', 'mage-eventpress' ); ?></h3>
							<p class="mep-sc__card-desc"><?php esc_html_e( 'Layout, indicators, and showcase options for the event image slider.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-sc__card-body">
						<?php
						foreach ( $order as $name ) {
							if ( ! isset( $by[ $name ] ) ) {
								continue;
							}
							self::render_field( $sec, $by[ $name ] );
						}
						foreach ( $extras as $field ) {
							self::render_field( $sec, $field );
						}
						?>
					</div>
				</div>
				<?php
			}

			/**
			 * Carousel settings card.
			 *
			 * @param array $fields Carousel fields.
			 */
			private static function render_carousel_card( $fields ) {
				$sec = 'carousel_setting_sec';
				$by  = self::index_fields( $fields );

				$order = array(
					'mep_load_carousal_from_theme',
					'mep_autoplay_carousal',
					'mep_loop_carousal',
					'mep_speed_carousal',
				);
				$known  = $order;
				$extras = array();
				foreach ( $fields as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( $name && ! in_array( $name, $known, true ) ) {
						$extras[] = $field;
					}
				}
				?>
				<div class="mep-sc__card">
					<div class="mep-sc__card-head">
						<span class="mep-sc__card-icon"><i class="fas fa-images"></i></span>
						<div>
							<h3 class="mep-sc__card-title"><?php esc_html_e( 'Carousel Settings', 'mage-eventpress' ); ?></h3>
							<p class="mep-sc__card-desc"><?php esc_html_e( 'Owl Carousel library, autoplay, loop, and speed for event carousels.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-sc__card-body">
						<?php
						foreach ( $order as $name ) {
							if ( ! isset( $by[ $name ] ) ) {
								continue;
							}
							self::render_field( $sec, $by[ $name ] );
						}
						foreach ( $extras as $field ) {
							self::render_field( $sec, $field );
						}
						?>
					</div>
				</div>
				<?php
			}

			/**
			 * Render a settings field (select or text).
			 *
			 * @param string $sec   Option group.
			 * @param array  $field Field def.
			 */
			private static function render_field( $sec, $field ) {
				$name = isset( $field['name'] ) ? $field['name'] : '';
				if ( ! $name ) {
					return;
				}
				$label   = isset( $field['label'] ) ? $field['label'] : $name;
				$hint    = isset( $field['desc'] ) ? $field['desc'] : '';
				$type    = isset( $field['type'] ) ? $field['type'] : 'text';
				$default = isset( $field['default'] ) ? $field['default'] : '';
				$value   = self::get_opt( $sec, $name, $default );
				$id      = $sec . '[' . $name . ']';
				$input_id = 'mep-sc-' . sanitize_html_class( $sec . '-' . $name );

				if ( 'select' === $type && ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
					?>
					<div class="mep-sc__field">
						<label class="mep-sc__label" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
						<select class="mep-sc__select" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $id ); ?>">
							<?php foreach ( $field['options'] as $k => $lab ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( $lab ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php if ( $hint ) : ?>
							<p class="mep-sc__hint"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<?php
					return;
				}
				?>
				<div class="mep-sc__field">
					<label class="mep-sc__label" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="text" class="mep-sc__input" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>" />
					<?php if ( $hint ) : ?>
						<p class="mep-sc__hint"><?php echo esc_html( $hint ); ?></p>
					<?php endif; ?>
				</div>
				<?php
			}
		}
	}
