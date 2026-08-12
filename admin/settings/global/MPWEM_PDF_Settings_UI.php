<?php
	/**
	 * PDF Settings — mockup-matched modern UI.
	 * Option group stays mep_pdf_gen_settings (keys unchanged).
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_PDF_Settings_UI' ) ) {
		class MPWEM_PDF_Settings_UI {

			const SECTION = 'mep_pdf_gen_settings';

			/**
			 * Billing toggle field order (2-col grid, row-major).
			 *
			 * @return array[] name => short label
			 */
			public static function billing_fields() {
				return array(
					'mep_pdf_billing_first_name'   => __( 'Name', 'mage-eventpress' ),
					'mep_pdf_billing_email'        => __( 'Email', 'mage-eventpress' ),
					'mep_pdf_billing_phone'        => __( 'Phone', 'mage-eventpress' ),
					'mep_pdf_billing_company_name' => __( 'Company', 'mage-eventpress' ),
					'mep_pdf_billing_address_1'    => __( 'Address', 'mage-eventpress' ),
					'mep_pdf_billing_city'         => __( 'City', 'mage-eventpress' ),
					'mep_pdf_billing_state'        => __( 'State', 'mage-eventpress' ),
					'mep_pdf_billing_postcode'     => __( 'Postcode', 'mage-eventpress' ),
					'mep_pdf_billing_country'      => __( 'Country', 'mage-eventpress' ),
					'mep_pdf_billing_method'       => __( 'Payment Method', 'mage-eventpress' ),
				);
			}

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

				$known = array_merge(
					array(
						'mep_pdf_lib',
						'mep_pdf_theme',
						'mep_pdf_extra_service_theme',
						'mep_pdf_logo',
						'mep_pdf_bg',
						'mep_pdf_show_price',
						'mep_pdf_bg_color',
						'mep_pdf_text_color',
						'mep_pdf_address',
						'mep_pdf_phone',
						'mep_pdf_tc_title',
						'mep_pdf_tc_text',
					),
					array_keys( self::billing_fields() )
				);
				$extras = array();
				foreach ( $all as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( $name && ! in_array( $name, $known, true ) ) {
						$extras[] = $field;
					}
				}

				echo '<form method="post" action="options.php" class="mep-pdf__form">';
				settings_fields( $sec );
				?>
				<div class="mep-pdf" data-ms-section="<?php echo esc_attr( $sec ); ?>">
					<div class="mep-pdf__header">
						<div class="mep-pdf__header-text">
							<h2 class="mep-pdf__title"><?php esc_html_e( 'PDF Settings', 'mage-eventpress' ); ?></h2>
							<p class="mep-pdf__subtitle"><?php esc_html_e( 'Customize PDF ticket design, company details, and billing fields.', 'mage-eventpress' ); ?></p>
						</div>
					</div>

					<?php self::render_design_card( $by ); ?>
					<?php self::render_company_card( $by ); ?>
					<?php self::render_billing_card( $by ); ?>

					<?php if ( $extras ) : ?>
						<div class="mep-pdf__card">
							<div class="mep-pdf__card-head">
								<span class="mep-pdf__card-icon mep-pdf__card-icon--purple"><i class="fas fa-puzzle-piece"></i></span>
								<div>
									<h3 class="mep-pdf__card-title"><?php esc_html_e( 'Additional Settings', 'mage-eventpress' ); ?></h3>
								</div>
							</div>
							<div class="mep-pdf__card-body">
								<?php foreach ( $extras as $field ) : ?>
									<?php self::render_fallback_field( $field ); ?>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<div style="display:none;"><?php submit_button(); ?></div>
				</form>
				<?php
			}

			/**
			 * @param array $by Fields by name.
			 */
			private static function render_design_card( $by ) {
				$theme   = self::get_opt( 'mep_pdf_theme', 'default.php' );
				$bg      = self::get_opt( 'mep_pdf_bg_color', '#FFFFFF' );
				$text    = self::get_opt( 'mep_pdf_text_color', '#1C1C22' );
				$show    = self::get_opt( 'mep_pdf_show_price', 'yes' );
				$logo    = self::get_opt( 'mep_pdf_logo', '' );
				$theme   = $theme ? $theme : 'default.php';
				$bg      = $bg ? $bg : '#FFFFFF';
				$text    = $text ? $text : '#1C1C22';
				$preview = self::theme_preview_slug( $theme );
				?>
				<div class="mep-pdf__top">
				<div class="mep-pdf__card mep-pdf__card--design">
					<div class="mep-pdf__card-head">
						<span class="mep-pdf__card-icon mep-pdf__card-icon--purple"><i class="fas fa-ticket-alt"></i></span>
						<div>
							<h3 class="mep-pdf__card-title"><?php esc_html_e( 'Ticket Design', 'mage-eventpress' ); ?></h3>
							<p class="mep-pdf__card-desc"><?php esc_html_e( 'Customize the layout, content, and branding of your PDF tickets.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-pdf__card-body">
						<div class="mep-pdf__grid mep-pdf__grid--2">
							<?php self::render_select_field( isset( $by['mep_pdf_lib'] ) ? $by['mep_pdf_lib'] : null, 'mep_pdf_lib' ); ?>
							<?php self::render_select_field( isset( $by['mep_pdf_theme'] ) ? $by['mep_pdf_theme'] : null, 'mep_pdf_theme', 'theme' ); ?>
						</div>

						<div class="mep-pdf__field">
							<?php self::render_select_field( isset( $by['mep_pdf_extra_service_theme'] ) ? $by['mep_pdf_extra_service_theme'] : null, 'mep_pdf_extra_service_theme' ); ?>
						</div>

						<div class="mep-pdf__grid mep-pdf__grid--2">
							<?php
							self::render_upload_field(
								isset( $by['mep_pdf_logo'] ) ? $by['mep_pdf_logo'] : null,
								'mep_pdf_logo',
								__( 'Logo', 'mage-eventpress' ),
								'fas fa-cloud-upload-alt',
								__( 'Click or drag to upload', 'mage-eventpress' ),
								__( 'PNG, JPG up to 2MB', 'mage-eventpress' )
							);
							self::render_upload_field(
								isset( $by['mep_pdf_bg'] ) ? $by['mep_pdf_bg'] : null,
								'mep_pdf_bg',
								__( 'Ticket Background Image', 'mage-eventpress' ),
								'fas fa-image',
								__( 'Click or drag to upload', 'mage-eventpress' ),
								__( 'PNG, JPG up to 5MB (A4 size recommended)', 'mage-eventpress' )
							);
							?>
						</div>

						<?php
						self::render_toggle_row(
							isset( $by['mep_pdf_show_price'] ) ? $by['mep_pdf_show_price'] : null,
							'mep_pdf_show_price',
							__( 'Show Price', 'mage-eventpress' ),
							__( 'Display ticket price on the PDF', 'mage-eventpress' ),
							'yes',
							'no',
							'yes'
						);
						?>

						<div class="mep-pdf__grid mep-pdf__grid--2">
							<?php
							self::render_color_field(
								isset( $by['mep_pdf_bg_color'] ) ? $by['mep_pdf_bg_color'] : null,
								'mep_pdf_bg_color',
								__( 'Background Color', 'mage-eventpress' ),
								'#FFFFFF'
							);
							self::render_color_field(
								isset( $by['mep_pdf_text_color'] ) ? $by['mep_pdf_text_color'] : null,
								'mep_pdf_text_color',
								__( 'Text Color', 'mage-eventpress' ),
								'#1C1C22'
							);
							?>
						</div>
					</div>
				</div>

					<div class="mep-pdf__card mep-pdf__card--preview">
						<div class="mep-pdf__preview-label">
							<span class="fas fa-eye" aria-hidden="true"></span>
							<?php esc_html_e( 'Live Preview', 'mage-eventpress' ); ?>
						</div>
						<div class="mep-pdf__preview-stage">
							<div
								id="mep-pdf-preview"
								class="mep-pdf__preview mep-pdf__preview--<?php echo esc_attr( $preview ); ?>"
								data-theme="<?php echo esc_attr( $theme ); ?>"
								style="--mep-pdf-preview-bg:<?php echo esc_attr( $bg ); ?>;--mep-pdf-preview-text:<?php echo esc_attr( $text ); ?>;"
							>
								<div class="mep-pdf__preview-accent" aria-hidden="true"></div>
								<div class="mep-pdf__preview-head">
									<div class="mep-pdf__preview-brand">
										<span class="mep-pdf__preview-logo<?php echo $logo ? ' has-img' : ''; ?>" data-pdf-preview-logo-wrap>
											<?php if ( $logo ) : ?>
												<img src="<?php echo esc_url( $logo ); ?>" alt="" data-pdf-preview-logo />
											<?php else : ?>
												<span data-pdf-preview-logo-fallback><?php esc_html_e( 'LOGO', 'mage-eventpress' ); ?></span>
											<?php endif; ?>
										</span>
										<span class="mep-pdf__preview-org"><?php esc_html_e( 'Event Organizer', 'mage-eventpress' ); ?></span>
									</div>
									<span class="mep-pdf__preview-badge"><?php esc_html_e( 'TICKET', 'mage-eventpress' ); ?></span>
								</div>
								<div class="mep-pdf__preview-title"><?php esc_html_e( 'Summer Music Festival', 'mage-eventpress' ); ?></div>
								<div class="mep-pdf__preview-meta">
									<span><?php esc_html_e( 'Aug 15, 2026 · 7:00 PM', 'mage-eventpress' ); ?></span>
									<span><?php esc_html_e( 'VIP Pass', 'mage-eventpress' ); ?></span>
								</div>
								<div class="mep-pdf__preview-body">
									<div class="mep-pdf__preview-attendee">
										<strong><?php esc_html_e( 'Alex Johnson', 'mage-eventpress' ); ?></strong>
										<span><?php esc_html_e( 'alex@example.com', 'mage-eventpress' ); ?></span>
									</div>
									<div class="mep-pdf__preview-qr" aria-hidden="true"></div>
								</div>
								<div class="mep-pdf__preview-foot">
									<span class="mep-pdf__preview-price<?php echo ( 'yes' === (string) $show ) ? '' : ' is-hidden'; ?>" data-pdf-preview-price>$99.00</span>
									<span class="mep-pdf__preview-code">#TKT-2048</span>
								</div>
							</div>
						</div>
						<p class="mep-pdf__preview-theme-name" id="mep-pdf-preview-theme-name"><?php echo esc_html( self::theme_preview_label( $theme, $by ) ); ?></p>
						<button type="button" class="mep-pdf__refresh" id="mep-pdf-refresh-preview">
							<span class="fas fa-sync-alt" aria-hidden="true"></span>
							<?php esc_html_e( 'Refresh Preview', 'mage-eventpress' ); ?>
						</button>
					</div>
				</div>
				<?php
			}

			/**
			 * @param string $theme Theme filename.
			 * @return string
			 */
			private static function theme_preview_slug( $theme ) {
				$map = array(
					'default.php'       => 'default',
					'ticket2.php'       => 'ticket2',
					'rcmmaa.php'        => 'rcmmaa',
					'gsound.php'        => 'gsound',
					'PWTinvoice.php'    => 'pwtinvoice',
					'invoice-style.php' => 'invoice',
				);
				$file = basename( (string) $theme );
				return isset( $map[ $file ] ) ? $map[ $file ] : 'default';
			}

			/**
			 * @param string $theme Theme filename.
			 * @param array  $by    Fields by name.
			 * @return string
			 */
			private static function theme_preview_label( $theme, $by ) {
				$file = basename( (string) $theme );
				if ( isset( $by['mep_pdf_theme']['options'][ $file ] ) ) {
					return trim( preg_replace( '/\s+/', ' ', (string) $by['mep_pdf_theme']['options'][ $file ] ) );
				}
				$labels = array(
					'default.php'       => __( 'Default Theme', 'mage-eventpress' ),
					'ticket2.php'       => __( 'Two Ticket', 'mage-eventpress' ),
					'rcmmaa.php'        => __( 'RCMMAA Theme', 'mage-eventpress' ),
					'gsound.php'        => __( 'G-Sound', 'mage-eventpress' ),
					'PWTinvoice.php'    => __( 'PWT Invoice', 'mage-eventpress' ),
					'invoice-style.php' => __( 'Invoice Style', 'mage-eventpress' ),
				);
				return isset( $labels[ $file ] ) ? $labels[ $file ] : $file;
			}

			/**
			 * @param array $by Fields by name.
			 */
			private static function render_company_card( $by ) {
				?>
				<div class="mep-pdf__card">
					<div class="mep-pdf__card-head">
						<span class="mep-pdf__card-icon mep-pdf__card-icon--slate"><i class="fas fa-building"></i></span>
						<div>
							<h3 class="mep-pdf__card-title"><?php esc_html_e( 'Company & Terms', 'mage-eventpress' ); ?></h3>
							<p class="mep-pdf__card-desc"><?php esc_html_e( 'Set company details and terms and conditions shown on tickets.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-pdf__card-body">
						<?php
						self::render_textarea_field(
							isset( $by['mep_pdf_address'] ) ? $by['mep_pdf_address'] : null,
							'mep_pdf_address',
							__( 'Company Address', 'mage-eventpress' ),
							__( 'Enter full company address...', 'mage-eventpress' )
						);
						?>

						<div class="mep-pdf__grid mep-pdf__grid--2">
							<?php
							self::render_text_field(
								isset( $by['mep_pdf_phone'] ) ? $by['mep_pdf_phone'] : null,
								'mep_pdf_phone',
								__( 'Phone Number', 'mage-eventpress' ),
								'+1 (555) 000-0000'
							);
							self::render_text_field(
								isset( $by['mep_pdf_tc_title'] ) ? $by['mep_pdf_tc_title'] : null,
								'mep_pdf_tc_title',
								__( 'Terms Title', 'mage-eventpress' ),
								__( 'Terms and Conditions', 'mage-eventpress' )
							);
							?>
						</div>

						<?php
						self::render_wysiwyg_field(
							isset( $by['mep_pdf_tc_text'] ) ? $by['mep_pdf_tc_text'] : null,
							'mep_pdf_tc_text',
							__( 'Terms Text', 'mage-eventpress' )
						);
						?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array $by Fields by name.
			 */
			private static function render_billing_card( $by ) {
				$billing = self::billing_fields();
				$has_any = false;
				foreach ( array_keys( $billing ) as $name ) {
					if ( isset( $by[ $name ] ) ) {
						$has_any = true;
						break;
					}
				}
				if ( ! $has_any ) {
					return;
				}
				?>
				<div class="mep-pdf__card">
					<div class="mep-pdf__card-head">
						<span class="mep-pdf__card-icon mep-pdf__card-icon--amber"><i class="fas fa-list-ul"></i></span>
						<div>
							<h3 class="mep-pdf__card-title"><?php esc_html_e( 'Billing Fields on Ticket', 'mage-eventpress' ); ?></h3>
							<p class="mep-pdf__card-desc"><?php esc_html_e( 'Select which attendee billing details to include on the generated PDF.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-pdf__card-body">
						<div class="mep-pdf__billing-grid">
							<?php foreach ( $billing as $name => $short_label ) : ?>
								<?php
								if ( ! isset( $by[ $name ] ) ) {
									continue;
								}
								self::render_billing_toggle( $by[ $name ], $name, $short_label );
								?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
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
			 * @param array|null $field   Field def.
			 * @param string     $name    Field name.
			 * @param string     $preview Optional data-pdf-preview key.
			 */
			private static function render_select_field( $field, $name, $preview = '' ) {
				if ( ! $field ) {
					return;
				}
				$sec     = self::SECTION;
				$label   = isset( $field['label'] ) ? $field['label'] : $name;
				$default = isset( $field['default'] ) ? $field['default'] : '';
				$value   = self::get_opt( $name, $default );
				$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
				$id      = 'mep-pdf-' . sanitize_html_class( $name );
				?>
				<div class="mep-pdf__field">
					<label class="mep-pdf__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<select class="mep-pdf__control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>"<?php echo $preview ? ' data-pdf-preview="' . esc_attr( $preview ) . '"' : ''; ?>>
						<?php foreach ( $options as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( trim( preg_replace( '/\s+/', ' ', (string) $lab ) ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php
			}

			/**
			 * @param array|null $field Field def.
			 * @param string     $name  Name.
			 * @param string     $label Label.
			 * @param string     $icon  FA class.
			 * @param string     $line1 Hint line 1.
			 * @param string     $line2 Hint line 2.
			 */
			private static function render_upload_field( $field, $name, $label, $icon, $line1, $line2 ) {
				if ( ! $field ) {
					return;
				}
				$sec   = self::SECTION;
				$value = self::get_opt( $name, isset( $field['default'] ) ? $field['default'] : '' );
				$value = is_scalar( $value ) ? (string) $value : '';
				$id    = 'mep-pdf-' . sanitize_html_class( $name );
				$has   = '' !== $value;
				?>
				<div class="mep-pdf__field">
					<span class="mep-pdf__label"><?php echo esc_html( $label ); ?></span>
					<div class="mep-pdf__upload<?php echo $has ? ' has-file' : ''; ?>" data-mep-pdf-upload>
						<input type="hidden" class="wpsa-url mep-pdf__upload-url" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>"<?php echo ( 'mep_pdf_logo' === $name ) ? ' data-pdf-preview="logo"' : ''; ?> />
						<button type="button" class="mep-pdf__upload-zone wpsa-browse" data-uploader_title="<?php echo esc_attr( $label ); ?>" data-uploader_button_text="<?php esc_attr_e( 'Use this image', 'mage-eventpress' ); ?>">
							<span class="mep-pdf__upload-preview" <?php echo $has ? '' : 'hidden'; ?>>
								<img src="<?php echo esc_url( $value ); ?>" alt="" />
							</span>
							<span class="mep-pdf__upload-empty" <?php echo $has ? 'hidden' : ''; ?>>
								<span class="mep-pdf__upload-icon"><i class="<?php echo esc_attr( $icon ); ?>"></i></span>
								<span class="mep-pdf__upload-line1"><?php echo esc_html( $line1 ); ?></span>
								<span class="mep-pdf__upload-line2"><?php echo esc_html( $line2 ); ?></span>
							</span>
						</button>
						<?php if ( $has ) : ?>
							<button type="button" class="mep-pdf__upload-clear"><?php esc_html_e( 'Remove', 'mage-eventpress' ); ?></button>
						<?php else : ?>
							<button type="button" class="mep-pdf__upload-clear" hidden><?php esc_html_e( 'Remove', 'mage-eventpress' ); ?></button>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array|null $field   Field.
			 * @param string     $name    Name.
			 * @param string     $label   Label.
			 * @param string     $desc    Description.
			 * @param string     $on_val  On value.
			 * @param string     $off_val Off value.
			 * @param string     $default Default.
			 */
			private static function render_toggle_row( $field, $name, $label, $desc, $on_val, $off_val, $default ) {
				if ( ! $field ) {
					return;
				}
				$sec     = self::SECTION;
				$default = isset( $field['default'] ) ? $field['default'] : $default;
				$value   = self::get_opt( $name, $default );
				$id      = 'mep-pdf-' . sanitize_html_class( $name );
				$checked = ( (string) $value === (string) $on_val );
				$preview = ( 'mep_pdf_show_price' === $name ) ? ' data-pdf-preview="price"' : '';
				?>
				<div class="mep-pdf__toggle-row">
					<div class="mep-pdf__toggle-text">
						<label class="mep-pdf__toggle-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<p class="mep-pdf__toggle-desc"><?php echo esc_html( $desc ); ?></p>
					</div>
					<label class="mep-pdf__switch">
						<input type="hidden" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $off_val ); ?>" />
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $on_val ); ?>" <?php checked( $checked ); ?><?php echo $preview; ?> />
						<span class="mep-pdf__switch-ui"></span>
					</label>
				</div>
				<?php
			}

			/**
			 * @param array|null $field   Field.
			 * @param string     $name    Name.
			 * @param string     $label   Label.
			 * @param string     $fallback Fallback hex.
			 */
			private static function render_color_field( $field, $name, $label, $fallback ) {
				if ( ! $field ) {
					return;
				}
				$sec     = self::SECTION;
				$default = isset( $field['default'] ) && $field['default'] ? $field['default'] : $fallback;
				$value   = self::get_opt( $name, $default );
				$value   = $value ? $value : $default;
				$id      = 'mep-pdf-' . sanitize_html_class( $name );
				$preview = ( 'mep_pdf_bg_color' === $name ) ? 'bg' : ( ( 'mep_pdf_text_color' === $name ) ? 'text' : '' );
				?>
				<div class="mep-pdf__field mep-pdf__color" data-mep-pdf-color>
					<div class="mep-pdf__color-box">
						<span class="mep-pdf__color-swatch" style="background-color: <?php echo esc_attr( $value ); ?>;"></span>
						<div class="mep-pdf__color-meta">
							<span class="mep-pdf__color-name"><?php echo esc_html( $label ); ?></span>
							<input type="text" class="mep-pdf__color-hex wp-color-picker-field" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default ); ?>"<?php echo $preview ? ' data-pdf-preview="' . esc_attr( $preview ) . '"' : ''; ?> />
						</div>
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
			private static function render_textarea_field( $field, $name, $label, $placeholder ) {
				if ( ! $field ) {
					return;
				}
				$sec   = self::SECTION;
				$value = self::get_opt( $name, '' );
				$id    = 'mep-pdf-' . sanitize_html_class( $name );
				?>
				<div class="mep-pdf__field">
					<label class="mep-pdf__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<textarea class="mep-pdf__control mep-pdf__textarea" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" rows="4" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( is_scalar( $value ) ? $value : '' ); ?></textarea>
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
				$sec   = self::SECTION;
				$value = self::get_opt( $name, isset( $field['default'] ) ? $field['default'] : '' );
				$id    = 'mep-pdf-' . sanitize_html_class( $name );
				?>
				<div class="mep-pdf__field">
					<label class="mep-pdf__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="text" class="mep-pdf__control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
				</div>
				<?php
			}

			/**
			 * @param array|null $field Field.
			 * @param string     $name  Name.
			 * @param string     $label Label.
			 */
			private static function render_wysiwyg_field( $field, $name, $label ) {
				if ( ! $field ) {
					return;
				}
				$sec       = self::SECTION;
				$value     = self::get_opt( $name, isset( $field['default'] ) ? $field['default'] : '' );
				$editor_id = $sec . '-' . $name;
				?>
				<div class="mep-pdf__field mep-pdf__field--editor">
					<span class="mep-pdf__label"><?php echo esc_html( $label ); ?></span>
					<div class="mep-pdf__editor">
						<?php
						wp_editor(
							is_scalar( $value ) ? $value : '',
							$editor_id,
							array(
								'textarea_name' => $sec . '[' . $name . ']',
								'textarea_rows' => 8,
								'media_buttons' => false,
								'teeny'         => true,
								'quicktags'     => true,
							)
						);
						?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array  $field Field.
			 * @param string $name  Name.
			 * @param string $label Short label.
			 */
			private static function render_billing_toggle( $field, $name, $label ) {
				$sec     = self::SECTION;
				$value   = self::get_opt( $name, 'off' );
				$value   = ( '' === $value || null === $value ) ? 'off' : $value;
				$id      = 'mep-pdf-' . sanitize_html_class( $name );
				$checked = ( 'on' === (string) $value );
				?>
				<div class="mep-pdf__billing-item">
					<label class="mep-pdf__billing-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<label class="mep-pdf__switch">
						<input type="hidden" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" value="off" />
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" value="on" <?php checked( $checked ); ?> />
						<span class="mep-pdf__switch-ui"></span>
					</label>
				</div>
				<?php
			}

			/**
			 * Minimal fallback for unexpected extra fields.
			 *
			 * @param array $field Field def.
			 */
			private static function render_fallback_field( $field ) {
				if ( class_exists( 'MPWEM_Modern_Section_Settings_UI' ) ) {
					// Reuse generic renderer via reflection is overkill — simple text/select here.
				}
				$name = isset( $field['name'] ) ? $field['name'] : '';
				$type = isset( $field['type'] ) ? $field['type'] : 'text';
				if ( ! $name || in_array( $type, array( 'html', 'title' ), true ) ) {
					return;
				}
				if ( 'checkbox' === $type ) {
					self::render_billing_toggle( $field, $name, isset( $field['label'] ) ? $field['label'] : $name );
					return;
				}
				if ( 'select' === $type ) {
					self::render_select_field( $field, $name );
					return;
				}
				if ( 'textarea' === $type ) {
					self::render_textarea_field( $field, $name, isset( $field['label'] ) ? $field['label'] : $name, '' );
					return;
				}
				self::render_text_field( $field, $name, isset( $field['label'] ) ? $field['label'] : $name, '' );
			}
		}
	}
