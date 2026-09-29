<?php
	/**
	 * Style & Icon Settings — single-page modern card UI.
	 *
	 * Option group ids stay style_setting_sec and icon_setting_sec so saved data is unchanged.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Style_Icon_Settings_UI' ) ) {
		class MPWEM_Style_Icon_Settings_UI {

			/**
			 * Render Style & Icon page (no subtabs).
			 *
			 * @param array $subtabs  Unused (kept for call-site compatibility).
			 * @param array $fields   Settings fields map.
			 * @param array $sections Unused (kept for call-site compatibility).
			 */
			public static function render_hub( $subtabs, $fields, $sections = array() ) {
				$style_fields = isset( $fields['style_setting_sec'] ) && is_array( $fields['style_setting_sec'] ) ? $fields['style_setting_sec'] : array();
				$icon_fields  = isset( $fields['icon_setting_sec'] ) && is_array( $fields['icon_setting_sec'] ) ? $fields['icon_setting_sec'] : array();
				?>
				<div class="mep-si">
					<div class="mep-si__header">
						<div class="mep-si__header-text">
							<h2 class="mep-si__title"><?php esc_html_e( 'Style & Icon', 'mage-eventpress' ); ?></h2>
							<p class="mep-si__subtitle"><?php esc_html_e( 'Customize brand colors and icons shown on event pages.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-si__page">
						<?php self::render_style_form( $style_fields ); ?>
						<?php self::render_icon_form( $icon_fields ); ?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array $fields Style fields.
			 */
			private static function render_style_form( $fields ) {
				$sec = 'style_setting_sec';
				echo '<form method="post" action="options.php" class="mep-si__form mep-si__form--style" data-si-group="' . esc_attr( $sec ) . '">';
				settings_fields( $sec );
				self::render_style_cards( $fields );
				echo '<div style="display:none;">';
				submit_button();
				echo '</div></form>';
			}

			/**
			 * @param array $fields Icon fields.
			 */
			private static function render_icon_form( $fields ) {
				$sec = 'icon_setting_sec';
				echo '<form method="post" action="options.php" class="mep-si__form mep-si__form--icon" data-si-group="' . esc_attr( $sec ) . '">';
				settings_fields( $sec );
				self::render_icon_cards( $fields );
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
			 * Full-width brand colors (+ optional extras).
			 *
			 * @param array $fields Style fields.
			 */
			private static function render_style_cards( $fields ) {
				$sec    = 'style_setting_sec';
				$by     = self::index_fields( $fields );
				$known  = array( 'mpev_primary_color', 'mpev_secondary_color' );
				$prim   = self::get_opt( $sec, 'mpev_primary_color', '#6046ff' );
				$sec_c  = self::get_opt( $sec, 'mpev_secondary_color', '#f1f5ff' );
				$extras = array();
				foreach ( $fields as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( $name && ! in_array( $name, $known, true ) ) {
						$extras[] = $field;
					}
				}
				?>
				<div class="mep-si__card mep-si__card--style">
					<div class="mep-si__card-head">
						<span class="mep-si__card-icon"><i class="fas fa-palette"></i></span>
						<div>
							<h3 class="mep-si__card-title"><?php esc_html_e( 'Brand Colors', 'mage-eventpress' ); ?></h3>
							<p class="mep-si__card-desc"><?php esc_html_e( 'Set the primary and secondary colors used across event pages.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-si__card-body">
						<div class="mep-si__preview" id="mep-si-color-preview" style="--mep-si-primary: <?php echo esc_attr( $prim ); ?>; --mep-si-secondary: <?php echo esc_attr( $sec_c ); ?>;">
							<div class="mep-si__preview-bar"></div>
							<div class="mep-si__preview-body">
								<span class="mep-si__preview-badge"><?php esc_html_e( 'Live preview', 'mage-eventpress' ); ?></span>
								<span class="mep-si__preview-btn"><?php esc_html_e( 'Book Now', 'mage-eventpress' ); ?></span>
							</div>
						</div>

						<div class="mep-si__colors">
							<?php
							self::render_color_field(
								$sec,
								'mpev_primary_color',
								isset( $by['mpev_primary_color']['label'] ) ? $by['mpev_primary_color']['label'] : __( 'Primary Color', 'mage-eventpress' ),
								isset( $by['mpev_primary_color']['desc'] ) ? $by['mpev_primary_color']['desc'] : '',
								$prim,
								'#6046ff'
							);
							self::render_color_field(
								$sec,
								'mpev_secondary_color',
								isset( $by['mpev_secondary_color']['label'] ) ? $by['mpev_secondary_color']['label'] : __( 'Secondary Color', 'mage-eventpress' ),
								isset( $by['mpev_secondary_color']['desc'] ) ? $by['mpev_secondary_color']['desc'] : '',
								$sec_c,
								'#f1f5ff'
							);
							?>
						</div>
					</div>
				</div>

				<?php if ( ! empty( $extras ) ) : ?>
					<div class="mep-si__card">
						<div class="mep-si__card-head">
							<span class="mep-si__card-icon"><i class="fas fa-sliders-h"></i></span>
							<div>
								<h3 class="mep-si__card-title"><?php esc_html_e( 'Additional Style Options', 'mage-eventpress' ); ?></h3>
							</div>
						</div>
						<div class="mep-si__card-body">
							<?php foreach ( $extras as $field ) : ?>
								<?php self::render_extra_field( $sec, $field ); ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif;
			}

			/**
			 * Two-column icon cards: Event Info | Social Share.
			 *
			 * @param array $fields Icon fields.
			 */
			private static function render_icon_cards( $fields ) {
				$sec = 'icon_setting_sec';
				$by  = self::index_fields( $fields );

				$event_icons = array(
					'mep_event_date_icon',
					'mep_event_time_icon',
					'mep_event_location_icon',
					'mep_event_organizer_icon',
					'mep_event_location_list_icon',
				);
				$social_icons = array(
					'mep_event_ss_fb_icon',
					'mep_event_ss_twitter_icon',
					'mep_event_ss_linkedin_icon',
					'mep_event_ss_whatsapp_icon',
					'mep_event_ss_email_icon',
				);
				$known  = array_merge( $event_icons, $social_icons );
				$extras = array();
				foreach ( $fields as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( $name && ! in_array( $name, $known, true ) ) {
						$extras[] = $field;
					}
				}

				$defaults = array(
					'mep_event_date_icon'          => 'mi mi-calendar',
					'mep_event_time_icon'          => 'mi mi-clock',
					'mep_event_location_icon'      => 'mi mi-marker',
					'mep_event_organizer_icon'     => 'mi mi-badge',
					'mep_event_location_list_icon' => 'mi mi-arrow-circle-right',
					'mep_event_ss_fb_icon'         => 'fab fa-facebook-f',
					'mep_event_ss_twitter_icon'    => 'fab fa-twitter',
					'mep_event_ss_linkedin_icon'   => 'fab fa-linkedin',
					'mep_event_ss_whatsapp_icon'   => 'fab fa-whatsapp',
					'mep_event_ss_email_icon'      => 'mi mi-envelope',
				);
				?>
				<div class="mep-si__icons-row">
					<div class="mep-si__card">
						<div class="mep-si__card-head">
							<span class="mep-si__card-icon"><i class="fas fa-calendar-alt"></i></span>
							<div>
								<h3 class="mep-si__card-title"><?php esc_html_e( 'Event Info Icons', 'mage-eventpress' ); ?></h3>
								<p class="mep-si__card-desc"><?php esc_html_e( 'Icons shown next to date, time, location, and organizer details.', 'mage-eventpress' ); ?></p>
							</div>
						</div>
						<div class="mep-si__card-body">
							<div class="mep-si__icon-grid">
								<?php foreach ( $event_icons as $name ) : ?>
									<?php
									$field = isset( $by[ $name ] ) ? $by[ $name ] : array( 'name' => $name );
									$def   = isset( $defaults[ $name ] ) ? $defaults[ $name ] : '';
									self::render_icon_field(
										$sec,
										$name,
										isset( $field['label'] ) ? $field['label'] : $name,
										isset( $field['desc'] ) ? $field['desc'] : '',
										self::get_opt( $sec, $name, $def ),
										$def
									);
									?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

					<div class="mep-si__card">
						<div class="mep-si__card-head">
							<span class="mep-si__card-icon"><i class="fas fa-share-alt"></i></span>
							<div>
								<h3 class="mep-si__card-title"><?php esc_html_e( 'Social Share Icons', 'mage-eventpress' ); ?></h3>
								<p class="mep-si__card-desc"><?php esc_html_e( 'Icons used on social sharing buttons for each event.', 'mage-eventpress' ); ?></p>
							</div>
						</div>
						<div class="mep-si__card-body">
							<div class="mep-si__icon-grid">
								<?php foreach ( $social_icons as $name ) : ?>
									<?php
									$field = isset( $by[ $name ] ) ? $by[ $name ] : array( 'name' => $name );
									$def   = isset( $defaults[ $name ] ) ? $defaults[ $name ] : '';
									self::render_icon_field(
										$sec,
										$name,
										isset( $field['label'] ) ? $field['label'] : $name,
										isset( $field['desc'] ) ? $field['desc'] : '',
										self::get_opt( $sec, $name, $def ),
										$def
									);
									?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>

				<?php if ( ! empty( $extras ) ) : ?>
					<div class="mep-si__card">
						<div class="mep-si__card-head">
							<span class="mep-si__card-icon"><i class="fas fa-puzzle-piece"></i></span>
							<div>
								<h3 class="mep-si__card-title"><?php esc_html_e( 'Additional Icons', 'mage-eventpress' ); ?></h3>
							</div>
						</div>
						<div class="mep-si__card-body">
							<div class="mep-si__icon-grid">
								<?php foreach ( $extras as $field ) : ?>
									<?php
									$name = $field['name'];
									$def  = isset( $field['default'] ) ? $field['default'] : '';
									self::render_icon_field(
										$sec,
										$name,
										isset( $field['label'] ) ? $field['label'] : $name,
										isset( $field['desc'] ) ? $field['desc'] : '',
										self::get_opt( $sec, $name, $def ),
										$def
									);
									?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endif;
			}

			/**
			 * Color picker field — custom swatch + hex + edit (iris under the hood).
			 */
			private static function render_color_field( $sec, $name, $label, $hint, $value, $default ) {
				$id    = $sec . '[' . $name . ']';
				$value = $value ? $value : $default;
				?>
				<div class="mep-si__color-field">
					<label class="mep-si__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<div class="mep-si__color-control" data-mep-si-picker>
						<span class="mep-si__color-swatch" style="background-color: <?php echo esc_attr( $value ); ?>;"></span>
						<input type="text"
							class="mep-si__color-hex"
							id="<?php echo esc_attr( $id ); ?>"
							name="<?php echo esc_attr( $id ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
							data-default-color="<?php echo esc_attr( $default ); ?>"
							data-mep-si-color="<?php echo esc_attr( $name ); ?>"
							spellcheck="false"
							autocomplete="off" />
						<button type="button" class="mep-si__color-edit" aria-label="<?php esc_attr_e( 'Pick color', 'mage-eventpress' ); ?>">
							<span class="fas fa-pen" aria-hidden="true"></span>
						</button>
					</div>
					<?php if ( $hint ) : ?>
						<p class="mep-si__hint"><?php echo esc_html( $hint ); ?></p>
					<?php endif; ?>
				</div>
				<?php
			}

			/**
			 * Icon library field — markup matches callback_iconlib so existing popup JS works.
			 */
			private static function render_icon_field( $sec, $name, $label, $hint, $value, $default ) {
				$value = $value ? $value : $default;
				?>
				<div class="mep-si__icon-item">
					<div class="mep-si__icon-meta">
						<span class="mep-si__label"><?php echo esc_html( $label ); ?></span>
						<?php if ( $hint ) : ?>
							<p class="mep-si__hint"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<div class="mep-si__icon-picker mep_settings_icon">
						<div class="mep-si__icon-preview mep_global_settings_icon_preview" data-key="<?php echo esc_attr( $name ); ?>">
							<?php if ( $value ) : ?>
								<i class="<?php echo esc_attr( $value ); ?>"></i>
							<?php endif; ?>
						</div>
						<a href="#" class="mep-si__icon-btn mep_global_icon_lib_btn" data-key="<?php echo esc_attr( $name ); ?>">
							<?php esc_html_e( 'Change', 'mage-eventpress' ); ?>
						</a>
						<input type="hidden"
							class="mep_global_settings_icon"
							id="<?php echo esc_attr( $name ); ?>"
							name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
							data-key="<?php echo esc_attr( $name ); ?>" />
					</div>
				</div>
				<?php
			}

			/**
			 * Fallback for filter-added style fields.
			 *
			 * @param string $sec   Option group.
			 * @param array  $field Field def.
			 */
			private static function render_extra_field( $sec, $field ) {
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

				if ( 'color' === $type ) {
					self::render_color_field( $sec, $name, $label, $hint, $value, $default );
					return;
				}
				if ( 'iconlib' === $type ) {
					self::render_icon_field( $sec, $name, $label, $hint, $value, $default );
					return;
				}
				?>
				<div class="mep-si__field">
					<label class="mep-si__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="text" class="mep-si__input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>" />
					<?php if ( $hint ) : ?>
						<p class="mep-si__hint"><?php echo esc_html( $hint ); ?></p>
					<?php endif; ?>
				</div>
				<?php
			}
		}
	}
