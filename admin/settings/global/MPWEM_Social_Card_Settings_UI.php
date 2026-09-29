<?php
	/**
	 * Social Share Card settings — mockup-matched modern UI.
	 * Option group stays mep_social_card_setting_sec (keys unchanged).
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Social_Card_Settings_UI' ) ) {
		class MPWEM_Social_Card_Settings_UI {

			const SECTION = 'mep_social_card_setting_sec';

			/**
			 * @param array $fields Full settings fields map.
			 */
			public static function render( $fields ) {
				$sec = self::SECTION;
				$all = isset( $fields[ $sec ] ) && is_array( $fields[ $sec ] ) ? $fields[ $sec ] : array();
				$by  = array();
				foreach ( $all as $field ) {
					if ( ! empty( $field['name'] ) ) {
						$by[ $field['name'] ] = $field;
					}
				}

				if ( empty( $all ) ) {
					?>
					<div class="mep-ssc" id="mep_social_card_setting_sec">
						<div class="mep-ssc__header">
							<h2 class="mep-ssc__title"><?php esc_html_e( 'Social Share Card', 'mage-eventpress' ); ?></h2>
							<p class="mep-ssc__subtitle"><?php esc_html_e( 'Configure the digital share cards generated for your attendees. Customize the design, trigger conditions, and available social networks to maximize organic reach.', 'mage-eventpress' ); ?></p>
						</div>
						<div class="mep-ssc__card">
							<div class="mep-ssc__empty"><?php esc_html_e( 'Social Share Card settings are available with Event Manager Pro.', 'mage-eventpress' ); ?></div>
						</div>
					</div>
					<?php
					return;
				}

				$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

				echo '<form method="post" action="options.php" class="mep-ssc__form" id="mep_social_card_setting_sec">';
				settings_fields( $sec );
				?>
				<div class="mep-ssc" data-ms-section="<?php echo esc_attr( $sec ); ?>">
					<div class="mep-ssc__header">
						<h2 class="mep-ssc__title"><?php esc_html_e( 'Social Share Card', 'mage-eventpress' ); ?></h2>
						<p class="mep-ssc__subtitle"><?php esc_html_e( 'Configure the digital share cards generated for your attendees. Customize the design, trigger conditions, and available social networks to maximize organic reach.', 'mage-eventpress' ); ?></p>
					</div>

					<div class="mep-ssc__top">
						<?php self::render_options_card( $by ); ?>
						<?php self::render_preview_card( $by, $site_name ); ?>
					</div>

					<?php self::render_design_card( $by ); ?>
				</div>
				<div style="display:none;"><?php submit_button(); ?></div>
				</form>
				<?php
			}

			/**
			 * @param string $key     Option key.
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
					if ( null !== $val ) {
						return $val;
					}
				}
				return $default;
			}

			/**
			 * @param array $by Fields by name.
			 */
			private static function render_options_card( $by ) {
				$enable_field = isset( $by['mep_social_card_enable'] ) ? $by['mep_social_card_enable'] : null;
				$enabled      = ( 'on' === (string) self::get_opt( 'mep_social_card_enable', '' ) );
				?>
				<div class="mep-ssc__card mep-ssc__card--options">
					<div class="mep-ssc__card-head mep-ssc__card-head--row">
						<div>
							<h3 class="mep-ssc__card-title"><?php esc_html_e( 'Share Card Options', 'mage-eventpress' ); ?></h3>
							<p class="mep-ssc__card-desc"><?php esc_html_e( 'Control when and how share cards are distributed.', 'mage-eventpress' ); ?></p>
						</div>
						<?php if ( $enable_field ) : ?>
							<label class="mep-ssc__switch" title="<?php esc_attr_e( 'Enable Social Share Card', 'mage-eventpress' ); ?>">
								<input type="hidden" name="<?php echo esc_attr( self::SECTION . '[mep_social_card_enable]' ); ?>" value="" />
								<input type="checkbox" id="mep-ssc-enable" name="<?php echo esc_attr( self::SECTION . '[mep_social_card_enable]' ); ?>" value="on" <?php checked( $enabled ); ?> />
								<span class="mep-ssc__switch-ui"></span>
							</label>
						<?php endif; ?>
					</div>
					<div class="mep-ssc__card-body">
						<?php
						self::render_status_pills(
							isset( $by['mep_social_card_wc_statuses'] ) ? $by['mep_social_card_wc_statuses'] : null,
							'mep_social_card_wc_statuses',
							__( 'WooCommerce Order Statuses', 'mage-eventpress' ),
							array(
								'completed'  => __( 'Completed', 'mage-eventpress' ),
								'processing' => __( 'Processing', 'mage-eventpress' ),
								'on-hold'    => __( 'On hold', 'mage-eventpress' ),
								'pending'    => __( 'Pending payment', 'mage-eventpress' ),
							)
						);
						self::render_status_pills(
							isset( $by['mep_social_card_native_statuses'] ) ? $by['mep_social_card_native_statuses'] : null,
							'mep_social_card_native_statuses',
							__( 'Custom Payment Statuses', 'mage-eventpress' ),
							array(
								'success' => __( 'Paid', 'mage-eventpress' ),
								'pending' => __( 'Pending', 'mage-eventpress' ),
							)
						);
						self::render_text_field(
							isset( $by['mep_social_card_button_text'] ) ? $by['mep_social_card_button_text'] : null,
							'mep_social_card_button_text',
							__( 'Download Button Label', 'mage-eventpress' ),
							__( 'Download Your Card', 'mage-eventpress' )
						);
						self::render_network_pills(
							isset( $by['mep_social_card_networks'] ) ? $by['mep_social_card_networks'] : null
						);
						?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array  $by        Fields.
			 * @param string $site_name Site name.
			 */
			private static function render_preview_card( $by, $site_name ) {
				$frame   = self::get_opt( 'mep_social_card_frame_image', '' );
				$font    = self::get_opt( 'mep_social_card_font_family', 'default' );
				$text    = self::get_opt( 'mep_social_card_text_color', '#111827' );
				$accent  = self::get_opt( 'mep_social_card_accent_color', '#059669' );
				$text    = $text ? $text : '#111827';
				$accent  = $accent ? $accent : '#059669';
				$font_css = ( $font && 'default' !== $font ) ? "'" . esc_attr( $font ) . "', sans-serif" : 'Inter, system-ui, sans-serif';
				$bg_style = $frame ? 'background-image:url(' . esc_url( $frame ) . ');' : '';
				?>
				<div class="mep-ssc__card mep-ssc__card--preview">
					<div class="mep-ssc__preview-label">
						<span class="fas fa-eye" aria-hidden="true"></span>
						<?php esc_html_e( 'Live Preview', 'mage-eventpress' ); ?>
					</div>
					<div class="mep-ssc__phone">
						<div class="mep-ssc__phone-notch" aria-hidden="true"></div>
						<div class="mep-ssc__phone-screen">
							<div
								class="mep-ssc__preview-card<?php echo $frame ? ' has-frame' : ''; ?>"
								id="mep-ssc-preview-card"
								style="--mep-ssc-text:<?php echo esc_attr( $text ); ?>;--mep-ssc-accent:<?php echo esc_attr( $accent ); ?>;font-family:<?php echo esc_attr( $font_css ); ?>;<?php echo esc_attr( $bg_style ); ?>"
							>
								<div class="mep-ssc__preview-top">
									<span class="mep-ssc__preview-brand"><?php echo esc_html( $site_name ? $site_name : __( 'Your Site', 'mage-eventpress' ) ); ?></span>
									<span class="mep-ssc__preview-badge">✓</span>
								</div>
								<div class="mep-ssc__preview-avatar" aria-hidden="true"></div>
								<div class="mep-ssc__preview-name"><?php esc_html_e( 'Alex Johnson', 'mage-eventpress' ); ?></div>
								<div class="mep-ssc__preview-headline"><?php esc_html_e( "I'm Going!", 'mage-eventpress' ); ?></div>
								<div class="mep-ssc__preview-meta">
									<span><?php esc_html_e( 'VIP Pass', 'mage-eventpress' ); ?></span>
									<span><?php esc_html_e( 'Aug 15, 2026', 'mage-eventpress' ); ?></span>
								</div>
								<div class="mep-ssc__preview-event"><?php esc_html_e( 'Summer Music Festival', 'mage-eventpress' ); ?></div>
								<div class="mep-ssc__preview-footer"><?php echo esc_html( $site_name ); ?></div>
							</div>
						</div>
					</div>
					<button type="button" class="mep-ssc__refresh" id="mep-ssc-refresh-preview">
						<span class="fas fa-sync-alt" aria-hidden="true"></span>
						<?php esc_html_e( 'Refresh Preview', 'mage-eventpress' ); ?>
					</button>
				</div>
				<?php
			}

			/**
			 * @param array $by Fields by name.
			 */
			private static function render_design_card( $by ) {
				?>
				<div class="mep-ssc__card mep-ssc__card--design">
					<div class="mep-ssc__card-head">
						<div>
							<h3 class="mep-ssc__card-title"><?php esc_html_e( 'Card Design', 'mage-eventpress' ); ?></h3>
							<p class="mep-ssc__card-desc"><?php esc_html_e( 'Customize the visual frame and typography applied to generated cards.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-ssc__card-body">
						<?php
						self::render_upload_field(
							isset( $by['mep_social_card_frame_image'] ) ? $by['mep_social_card_frame_image'] : null
						);
						?>
						<div class="mep-ssc__design-grid">
							<?php
							self::render_select_field(
								isset( $by['mep_social_card_font_family'] ) ? $by['mep_social_card_font_family'] : null,
								'mep_social_card_font_family',
								__( 'Card Font', 'mage-eventpress' )
							);
							self::render_color_field(
								isset( $by['mep_social_card_text_color'] ) ? $by['mep_social_card_text_color'] : null,
								'mep_social_card_text_color',
								__( 'Text Color', 'mage-eventpress' ),
								'#111827'
							);
							self::render_color_field(
								isset( $by['mep_social_card_accent_color'] ) ? $by['mep_social_card_accent_color'] : null,
								'mep_social_card_accent_color',
								__( 'Accent Color', 'mage-eventpress' ),
								'#059669'
							);
							?>
						</div>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array|null $field   Field.
			 * @param string     $name    Name.
			 * @param string     $label   Section label.
			 * @param array      $options Key => short label.
			 */
			private static function render_status_pills( $field, $name, $label, $options ) {
				if ( ! $field ) {
					return;
				}
				$default = isset( $field['default'] ) && is_array( $field['default'] ) ? $field['default'] : array();
				$value   = self::get_opt( $name, $default );
				if ( ! is_array( $value ) ) {
					$value = $default;
				}
				// Prefer short labels from $options; fall back to field options.
				$field_opts = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
				$keys       = array_keys( $field_opts ? $field_opts : $options );
				?>
				<div class="mep-ssc__field">
					<span class="mep-ssc__field-label"><?php echo esc_html( $label ); ?></span>
					<input type="hidden" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="" />
					<div class="mep-ssc__pills">
						<?php foreach ( $keys as $key ) : ?>
							<?php
							$lab     = isset( $options[ $key ] ) ? $options[ $key ] : ( isset( $field_opts[ $key ] ) ? $field_opts[ $key ] : $key );
							$checked = isset( $value[ $key ] ) && (string) $value[ $key ] === (string) $key;
							?>
							<label class="mep-ssc__pill">
								<input type="checkbox" name="<?php echo esc_attr( self::SECTION . '[' . $name . '][' . $key . ']' ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $checked ); ?> />
								<span class="mep-ssc__pill-ui">
									<span class="mep-ssc__pill-check" aria-hidden="true"><i class="fas fa-check"></i></span>
									<span class="mep-ssc__pill-text"><?php echo esc_html( $lab ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array|null $field Field.
			 */
			private static function render_network_pills( $field ) {
				if ( ! $field ) {
					return;
				}
				$default = isset( $field['default'] ) && is_array( $field['default'] ) ? $field['default'] : array();
				$value   = self::get_opt( 'mep_social_card_networks', $default );
				if ( ! is_array( $value ) ) {
					$value = $default;
				}
				$networks = array(
					'facebook'  => array( 'label' => __( 'Facebook', 'mage-eventpress' ), 'icon' => 'fab fa-facebook-f' ),
					'twitter'   => array( 'label' => __( 'Twitter / X', 'mage-eventpress' ), 'icon' => 'fab fa-twitter' ),
					'whatsapp'  => array( 'label' => __( 'WhatsApp', 'mage-eventpress' ), 'icon' => 'fab fa-whatsapp' ),
					'linkedin'  => array( 'label' => __( 'LinkedIn', 'mage-eventpress' ), 'icon' => 'fab fa-linkedin-in' ),
					'instagram' => array( 'label' => __( 'Instagram', 'mage-eventpress' ), 'icon' => 'fab fa-instagram' ),
				);
				?>
				<div class="mep-ssc__field">
					<span class="mep-ssc__field-label"><?php esc_html_e( 'Share Networks', 'mage-eventpress' ); ?></span>
					<input type="hidden" name="<?php echo esc_attr( self::SECTION . '[mep_social_card_networks]' ); ?>" value="" />
					<div class="mep-ssc__networks">
						<?php foreach ( $networks as $key => $meta ) : ?>
							<?php $checked = isset( $value[ $key ] ) && (string) $value[ $key ] === (string) $key; ?>
							<label class="mep-ssc__network">
								<input type="checkbox" name="<?php echo esc_attr( self::SECTION . '[mep_social_card_networks][' . $key . ']' ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $checked ); ?> />
								<span class="mep-ssc__network-ui">
									<i class="<?php echo esc_attr( $meta['icon'] ); ?>" aria-hidden="true"></i>
									<span><?php echo esc_html( $meta['label'] ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array|null $field       Field.
			 * @param string     $name        Name.
			 * @param string     $label       Label.
			 * @param string     $placeholder Placeholder.
			 */
			private static function render_text_field( $field, $name, $label, $placeholder ) {
				if ( ! $field ) {
					return;
				}
				$default = isset( $field['default'] ) ? $field['default'] : '';
				$value   = self::get_opt( $name, $default );
				$id      = 'mep-ssc-' . sanitize_html_class( $name );
				?>
				<div class="mep-ssc__field">
					<label class="mep-ssc__field-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="text" class="mep-ssc__control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
				</div>
				<?php
			}

			/**
			 * @param array|null $field Field.
			 * @param string     $name  Name.
			 * @param string     $label Label.
			 */
			private static function render_select_field( $field, $name, $label ) {
				if ( ! $field ) {
					return;
				}
				$default = isset( $field['default'] ) ? $field['default'] : '';
				$value   = self::get_opt( $name, $default );
				$options = isset( $field['options'] ) ? $field['options'] : array();
				$id      = 'mep-ssc-' . sanitize_html_class( $name );
				?>
				<div class="mep-ssc__field">
					<label class="mep-ssc__field-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<select class="mep-ssc__control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" data-ssc-preview="font">
						<?php foreach ( $options as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php
			}

			/**
			 * @param array|null $field Field.
			 */
			private static function render_upload_field( $field ) {
				if ( ! $field ) {
					return;
				}
				$value = self::get_opt( 'mep_social_card_frame_image', '' );
				$has   = (bool) $value;
				$id    = 'mep-ssc-frame-image';
				?>
				<div class="mep-ssc__field">
					<span class="mep-ssc__field-label"><?php esc_html_e( 'Card Frame Image', 'mage-eventpress' ); ?></span>
					<p class="mep-ssc__hint"><?php esc_html_e( 'Upload a transparent PNG frame. Recommended size 380×475px (4:5).', 'mage-eventpress' ); ?></p>
					<div class="mep-ssc__upload<?php echo $has ? ' has-file' : ''; ?>" data-mep-ssc-upload>
						<input type="hidden" class="wpsa-url mep-ssc__control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[mep_social_card_frame_image]' ); ?>" value="<?php echo esc_attr( $value ); ?>" data-ssc-preview="frame" />
						<button type="button" class="mep-ssc__upload-zone wpsa-browse" data-uploader_title="<?php esc_attr_e( 'Choose Frame Image', 'mage-eventpress' ); ?>" data-uploader_button_text="<?php esc_attr_e( 'Use this image', 'mage-eventpress' ); ?>">
							<span class="mep-ssc__upload-preview" <?php echo $has ? '' : 'hidden'; ?>>
								<img src="<?php echo esc_url( $value ); ?>" alt="" />
							</span>
							<span class="mep-ssc__upload-empty" <?php echo $has ? 'hidden' : ''; ?>>
								<span class="mep-ssc__upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
								<span class="mep-ssc__upload-line1"><?php esc_html_e( 'Click to upload or drag and drop', 'mage-eventpress' ); ?></span>
								<span class="mep-ssc__upload-line2"><?php esc_html_e( 'PNG, JPG up to 5MB', 'mage-eventpress' ); ?></span>
							</span>
						</button>
						<button type="button" class="mep-ssc__upload-clear" <?php echo $has ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove', 'mage-eventpress' ); ?></button>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array|null $field    Field.
			 * @param string     $name     Name.
			 * @param string     $label    Label.
			 * @param string     $fallback Fallback hex.
			 */
			private static function render_color_field( $field, $name, $label, $fallback ) {
				if ( ! $field ) {
					return;
				}
				$default = isset( $field['default'] ) && $field['default'] ? $field['default'] : $fallback;
				$value   = self::get_opt( $name, $default );
				$value   = $value ? $value : $default;
				$id      = 'mep-ssc-' . sanitize_html_class( $name );
				$preview = ( false !== strpos( $name, 'accent' ) ) ? 'accent' : 'text';
				?>
				<div class="mep-ssc__field mep-ssc__color" data-mep-ssc-color>
					<label class="mep-ssc__field-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<div class="mep-ssc__color-box">
						<span class="mep-ssc__color-swatch" style="background-color: <?php echo esc_attr( $value ); ?>;"></span>
						<input type="text" class="mep-ssc__color-hex wp-color-picker-field" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default ); ?>" data-ssc-preview="<?php echo esc_attr( $preview ); ?>" />
					</div>
				</div>
				<?php
			}
		}
	}
