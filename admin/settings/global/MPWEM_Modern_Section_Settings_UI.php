<?php
	/**
	 * Generic modern settings section UI (Event List–style).
	 * Used for addon/remaining tabs that still use Settings API fields,
	 * and as a chrome wrapper for custom-only tabs (License, Status, Google Sheets).
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Modern_Section_Settings_UI' ) ) {
		class MPWEM_Modern_Section_Settings_UI {

			/**
			 * Default titles / subtitles when tab config is sparse.
			 *
			 * @return array
			 */
			public static function meta_defaults() {
				return array(
					'mep_eb_settings'               => array(
						'title'    => __( 'Early Birds', 'mage-eventpress' ),
						'subtitle' => __( 'Control how early bird tickets are displayed and limited.', 'mage-eventpress' ),
						'icon'     => 'fas fa-dove',
					),
					'mep_pdf_gen_settings'          => array(
						'title'    => __( 'PDF Settings', 'mage-eventpress' ),
						'subtitle' => __( 'Design and content options for PDF tickets.', 'mage-eventpress' ),
						'icon'     => 'fas fa-file-pdf',
					),
					'csv_checkout_export_fileds_sec' => array(
						'title'    => __( 'CSV Settings', 'mage-eventpress' ),
						'subtitle' => __( 'Choose which columns are included in CSV exports.', 'mage-eventpress' ),
						'icon'     => 'fas fa-file-csv',
					),
					'mep_certificate_settings'      => array(
						'title'    => __( 'Certificate Settings', 'mage-eventpress' ),
						'subtitle' => __( 'Configure certificate templates, branding, and text.', 'mage-eventpress' ),
						'icon'     => 'fas fa-certificate',
					),
					'mep_ai_assistant_settings'     => array(
						'title'    => __( 'AI Assistant Settings', 'mage-eventpress' ),
						'subtitle' => __( 'Connect AI providers and manage API keys for the event assistant.', 'mage-eventpress' ),
						'icon'     => 'fas fa-robot',
					),
					'mep_deposit_settings'          => array(
						'title'    => __( 'Deposit / Partial Payment', 'mage-eventpress' ),
						'subtitle' => __( 'Let customers pay a deposit and settle the balance later.', 'mage-eventpress' ),
						'icon'     => 'fas fa-percentage',
					),
					'mep_review_permission_settings' => array(
						'title'    => __( 'Review & Rating', 'mage-eventpress' ),
						'subtitle' => __( 'Control who can leave reviews and how ratings appear.', 'mage-eventpress' ),
						'icon'     => 'fas fa-star',
					),
					'mep_social_card_setting_sec'   => array(
						'title'    => __( 'Social Share Card', 'mage-eventpress' ),
						'subtitle' => __( 'Branding and options for downloadable social share cards.', 'mage-eventpress' ),
						'icon'     => 'fas fa-share-alt',
					),
					'mep_settings_licensing'        => array(
						'title'    => __( 'License', 'mage-eventpress' ),
						'subtitle' => __( 'Activate license keys for Event Manager Pro add-ons.', 'mage-eventpress' ),
						'icon'     => 'fas fa-key',
					),
					'mep_status_setting_sec'        => array(
						'title'    => __( 'Status', 'mage-eventpress' ),
						'subtitle' => __( 'Check the WordPress and WooCommerce environment for Event Manager.', 'mage-eventpress' ),
						'icon'     => 'fas fa-heartbeat',
					),
					'mep_gsheet_settings'           => array(
						'title'    => __( 'Google Sheets', 'mage-eventpress' ),
						'subtitle' => __( 'Sync event orders to a Google Spreadsheet in real time.', 'mage-eventpress' ),
						'icon'     => 'fas fa-table',
					),
				);
			}

			/**
			 * @param string $tab_id   Section id.
			 * @param array  $config   Tab display config.
			 * @param array  $fields   All fields map.
			 * @param array  $sections Sections keyed by id.
			 */
			public static function render( $tab_id, $config, $fields, $sections = array() ) {
				$defaults = self::meta_defaults();
				$meta     = isset( $defaults[ $tab_id ] ) ? $defaults[ $tab_id ] : array();
				$title    = ! empty( $config['title'] ) ? $config['title'] : ( isset( $meta['title'] ) ? $meta['title'] : $tab_id );
				$subtitle = ! empty( $config['subtitle'] ) ? $config['subtitle'] : ( isset( $meta['subtitle'] ) ? $meta['subtitle'] : '' );
				$icon     = ! empty( $config['icon'] ) ? $config['icon'] : ( isset( $meta['icon'] ) ? $meta['icon'] : 'fas fa-cog' );
				$sec_arg  = isset( $sections[ $tab_id ] ) ? $sections[ $tab_id ] : array( 'id' => $tab_id );
				$all      = isset( $fields[ $tab_id ] ) && is_array( $fields[ $tab_id ] ) ? $fields[ $tab_id ] : array();
				$has_fields = ! empty( $all );

				if ( $has_fields ) {
					echo '<form method="post" action="options.php" class="mep-el__form mep-ms__form">';
					settings_fields( $tab_id );
				}
				?>
				<div class="mep-el mep-ms" data-ms-section="<?php echo esc_attr( $tab_id ); ?>">
					<div class="mep-el__header">
						<div class="mep-el__header-text">
							<h2 class="mep-el__title"><?php echo esc_html( $title ); ?></h2>
							<?php if ( $subtitle ) : ?>
								<p class="mep-el__subtitle"><?php echo esc_html( $subtitle ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<?php do_action( 'wsa_form_top_' . $tab_id, $sec_arg ); ?>

					<?php if ( $has_fields ) : ?>
						<?php self::render_field_cards( $tab_id, $all, $icon ); ?>
					<?php else : ?>
						<div class="mep-el__card mep-ms__custom-card">
							<div class="mep-ms__custom-body">
								<?php do_action( 'wsa_form_bottom_' . $tab_id, $sec_arg ); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $has_fields ) : ?>
						<?php do_action( 'wsa_form_bottom_' . $tab_id, $sec_arg ); ?>
					<?php endif; ?>
				</div>
				<?php
				if ( $has_fields ) {
					echo '<div style="display:none;">';
					submit_button();
					echo '</div></form>';
				}
			}

			/**
			 * Group fields into cards when possible.
			 *
			 * @param string $sec    Option group.
			 * @param array  $fields Field defs.
			 * @param string $icon   Default icon.
			 */
			private static function render_field_cards( $sec, $fields, $icon ) {
				$groups = self::group_fields( $sec, $fields );
				foreach ( $groups as $group ) {
					?>
					<div class="mep-el__card mep-ms__card">
						<?php if ( ! empty( $group['title'] ) ) : ?>
							<div class="mep-ms__card-head">
								<span class="mep-ms__card-icon"><i class="<?php echo esc_attr( $group['icon'] ); ?>"></i></span>
								<div>
									<h3 class="mep-ms__card-title"><?php echo esc_html( $group['title'] ); ?></h3>
									<?php if ( ! empty( $group['desc'] ) ) : ?>
										<p class="mep-ms__card-desc"><?php echo esc_html( $group['desc'] ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="mep-el__rows">
							<?php foreach ( $group['fields'] as $field ) : ?>
								<?php self::render_field( $sec, $field ); ?>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
				}
			}

			/**
			 * Split long option groups into logical cards.
			 *
			 * @param string $sec    Section id.
			 * @param array  $fields Fields.
			 * @return array
			 */
			private static function group_fields( $sec, $fields ) {
				// PDF: appearance | company | billing columns
				if ( 'mep_pdf_gen_settings' === $sec ) {
					return self::split_named(
						$fields,
						array(
							array(
								'title'  => __( 'Ticket Design', 'mage-eventpress' ),
								'icon'   => 'fas fa-palette',
								'desc'   => __( 'Library, theme, logo, and colors for PDF tickets.', 'mage-eventpress' ),
								'names'  => array( 'mep_pdf_lib', 'mep_pdf_show_price', 'mep_pdf_theme', 'mep_pdf_extra_service_theme', 'mep_pdf_logo', 'mep_pdf_bg', 'mep_pdf_bg_color', 'mep_pdf_text_color' ),
							),
							array(
								'title'  => __( 'Company & Terms', 'mage-eventpress' ),
								'icon'   => 'fas fa-building',
								'desc'   => __( 'Contact details and terms shown on the ticket.', 'mage-eventpress' ),
								'names'  => array( 'mep_pdf_address', 'mep_pdf_phone', 'mep_pdf_tc_title', 'mep_pdf_tc_text' ),
							),
							array(
								'title'  => __( 'Billing Fields on Ticket', 'mage-eventpress' ),
								'icon'   => 'fas fa-list-check',
								'desc'   => __( 'Choose which billing details appear on the PDF.', 'mage-eventpress' ),
								'names'  => array( 'mep_pdf_billing_first_name', 'mep_pdf_billing_email', 'mep_pdf_billing_phone', 'mep_pdf_billing_company_name', 'mep_pdf_billing_address_1', 'mep_pdf_billing_city', 'mep_pdf_billing_state', 'mep_pdf_billing_postcode', 'mep_pdf_billing_country', 'mep_pdf_billing_method' ),
							),
						)
					);
				}

				if ( 'mep_certificate_settings' === $sec ) {
					return self::split_named(
						$fields,
						array(
							array(
								'title' => __( 'Certificate Options', 'mage-eventpress' ),
								'icon'  => 'fas fa-certificate',
								'desc'  => __( 'Enable certificates and choose a template.', 'mage-eventpress' ),
								'names' => array( 'mep_certificate_enable', 'mep_certificate_template', 'mep_certificate_logo' ),
							),
							array(
								'title' => __( 'Certificate Content', 'mage-eventpress' ),
								'icon'  => 'fas fa-align-left',
								'desc'  => __( 'Titles, body text, and footer copy.', 'mage-eventpress' ),
								'names' => array( 'mep_certificate_company_name', 'mep_certificate_title', 'mep_certificate_subtitle', 'mep_certificate_body', 'mep_certificate_footer', 'mep_certificate_attended_text' ),
							),
							array(
								'title' => __( 'Colors', 'mage-eventpress' ),
								'icon'  => 'fas fa-fill-drip',
								'desc'  => __( 'Background, text, and accent colors.', 'mage-eventpress' ),
								'names' => array( 'mep_certificate_bg_color', 'mep_certificate_text_color', 'mep_certificate_accent_color' ),
							),
						)
					);
				}

				if ( 'mep_ai_assistant_settings' === $sec ) {
					return self::split_named(
						$fields,
						array(
							array(
								'title' => __( 'General', 'mage-eventpress' ),
								'icon'  => 'fas fa-robot',
								'desc'  => __( 'Enable the assistant and choose a provider.', 'mage-eventpress' ),
								'names' => array( 'mep_ai_enabled', 'mep_ai_provider' ),
							),
							array(
								'title' => __( 'Provider API Keys & Models', 'mage-eventpress' ),
								'icon'  => 'fas fa-key',
								'desc'  => __( 'Credentials and models for each AI provider.', 'mage-eventpress' ),
								'names' => array(), // remaining
								'rest'  => true,
							),
						)
					);
				}

				if ( 'mep_social_card_setting_sec' === $sec ) {
					return self::split_named(
						$fields,
						array(
							array(
								'title' => __( 'Share Card Options', 'mage-eventpress' ),
								'icon'  => 'fas fa-share-alt',
								'desc'  => __( 'Enable the card and choose when it appears.', 'mage-eventpress' ),
								'names' => array( 'mep_social_card_enable', 'mep_social_card_wc_statuses', 'mep_social_card_native_statuses', 'mep_social_card_button_text', 'mep_social_card_networks' ),
							),
							array(
								'title' => __( 'Card Design', 'mage-eventpress' ),
								'icon'  => 'fas fa-paint-brush',
								'desc'  => __( 'Frame image, font, and colors.', 'mage-eventpress' ),
								'names' => array( 'mep_social_card_frame_image', 'mep_social_card_font_family', 'mep_social_card_text_color', 'mep_social_card_accent_color' ),
							),
						)
					);
				}

				if ( 'csv_checkout_export_fileds_sec' === $sec ) {
					return array(
						array(
							'title'  => __( 'CSV Export Columns', 'mage-eventpress' ),
							'icon'   => 'fas fa-file-csv',
							'desc'   => __( 'Select which billing and payment fields appear in CSV exports.', 'mage-eventpress' ),
							'fields' => $fields,
						),
					);
				}

				// Default: single card.
				$defaults = self::meta_defaults();
				$meta     = isset( $defaults[ $sec ] ) ? $defaults[ $sec ] : array();
				return array(
					array(
						'title'  => isset( $meta['title'] ) ? $meta['title'] : '',
						'icon'   => isset( $meta['icon'] ) ? $meta['icon'] : 'fas fa-cog',
						'desc'   => '',
						'fields' => $fields,
					),
				);
			}

			/**
			 * @param array $fields All fields.
			 * @param array $specs  Group specs with names / rest.
			 * @return array
			 */
			private static function split_named( $fields, $specs ) {
				$by_name = array();
				foreach ( $fields as $field ) {
					if ( ! empty( $field['name'] ) ) {
						$by_name[ $field['name'] ] = $field;
					}
				}
				$used   = array();
				$groups = array();
				foreach ( $specs as $spec ) {
					$group_fields = array();
					if ( ! empty( $spec['rest'] ) ) {
						foreach ( $fields as $field ) {
							$name = isset( $field['name'] ) ? $field['name'] : '';
							if ( $name && ! in_array( $name, $used, true ) ) {
								$group_fields[] = $field;
								$used[]         = $name;
							}
						}
					} else {
						foreach ( $spec['names'] as $name ) {
							if ( isset( $by_name[ $name ] ) ) {
								$group_fields[] = $by_name[ $name ];
								$used[]         = $name;
							}
						}
					}
					if ( empty( $group_fields ) ) {
						continue;
					}
					$groups[] = array(
						'title'  => $spec['title'],
						'icon'   => $spec['icon'],
						'desc'   => isset( $spec['desc'] ) ? $spec['desc'] : '',
						'fields' => $group_fields,
					);
				}
				// Leftovers.
				$extra = array();
				foreach ( $fields as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( $name && ! in_array( $name, $used, true ) ) {
						$extra[] = $field;
					}
				}
				if ( $extra ) {
					$groups[] = array(
						'title'  => __( 'Additional Settings', 'mage-eventpress' ),
						'icon'   => 'fas fa-puzzle-piece',
						'desc'   => '',
						'fields' => $extra,
					);
				}
				return $groups;
			}

			/**
			 * @param string $sec   Option group.
			 * @param string $key   Field name.
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
					if ( null !== $val ) {
						return $val;
					}
				}
				return $default;
			}

			/**
			 * @param string $sec   Option group.
			 * @param array  $field Field def.
			 */
			private static function render_field( $sec, $field ) {
				$name = isset( $field['name'] ) ? $field['name'] : '';
				$type = isset( $field['type'] ) ? $field['type'] : 'text';

				if ( 'html' === $type || 'title' === $type ) {
					self::render_html_block( $field );
					return;
				}
				if ( ! $name ) {
					return;
				}

				$label   = isset( $field['label'] ) ? $field['label'] : $name;
				$hint    = isset( $field['desc'] ) ? $field['desc'] : '';
				$default = isset( $field['default'] ) ? $field['default'] : ( isset( $field['std'] ) ? $field['std'] : '' );
				$value   = self::get_opt( $sec, $name, $default );
				$options = isset( $field['options'] ) ? $field['options'] : array();
				$id      = 'mep-ms-' . sanitize_html_class( $sec . '-' . $name );
				$input_n = $sec . '[' . $name . ']';

				// Yes/No select → toggle.
				if ( 'select' === $type && is_array( $options ) && 2 === count( $options ) && isset( $options['yes'], $options['no'] ) ) {
					self::render_toggle( $input_n, $id, $label, $hint, $value, 'yes', 'no' );
					return;
				}
				// Enable-style on/off select.
				if ( 'select' === $type && is_array( $options ) && 2 === count( $options ) && isset( $options['on'], $options['off'] ) ) {
					self::render_toggle( $input_n, $id, $label, $hint, $value ? $value : 'off', 'on', 'off' );
					return;
				}
				if ( 'checkbox' === $type ) {
					$on  = 'on';
					$off = 'off';
					// Some checkboxes use empty default for off.
					$cur = ( '' === $value || null === $value ) ? $off : $value;
					if ( 'yes' === $default || ( is_array( $options ) && isset( $options['yes'] ) ) ) {
						$on  = 'yes';
						$off = 'no';
						$cur = ( '' === $value || null === $value ) ? $off : $value;
					}
					self::render_toggle( $input_n, $id, $label, $hint, $cur, $on, $off );
					return;
				}
				if ( 'multicheck' === $type && is_array( $options ) ) {
					self::render_multicheck( $sec, $name, $label, $hint, $options, is_array( $value ) ? $value : array() );
					return;
				}
				if ( 'select' === $type && is_array( $options ) ) {
					self::render_select( $input_n, $id, $label, $hint, $options, $value );
					return;
				}
				if ( 'textarea' === $type ) {
					self::render_textarea( $input_n, $id, $label, $hint, $value );
					return;
				}
				if ( 'wysiwyg' === $type ) {
					self::render_wysiwyg( $sec, $name, $label, $hint, $value );
					return;
				}
				if ( 'file' === $type ) {
					$btn = isset( $options['button_label'] ) ? $options['button_label'] : __( 'Choose File', 'mage-eventpress' );
					self::render_file( $input_n, $id, $label, $hint, $value, $btn );
					return;
				}
				if ( 'color' === $type ) {
					self::render_color( $input_n, $id, $label, $hint, $value, $default );
					return;
				}
				if ( 'password' === $type ) {
					self::render_text( $input_n, $id, $label, $hint, $value, 'password' );
					return;
				}
				self::render_text( $input_n, $id, $label, $hint, $value, 'text' );
			}

			private static function render_html_block( $field ) {
				$hint = isset( $field['desc'] ) ? $field['desc'] : '';
				if ( ! $hint ) {
					return;
				}
				echo '<div class="mep-ms__html-block">' . wp_kses_post( $hint ) . '</div>';
			}

			private static function render_toggle( $name, $id, $label, $hint, $value, $on_val, $off_val ) {
				$checked = ( (string) $value === (string) $on_val );
				?>
				<div class="mep-el__row">
					<div class="mep-el__row-text">
						<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?><p class="mep-el__row-desc"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<label class="mep-el__switch">
						<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $off_val ); ?>" />
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $on_val ); ?>" <?php checked( $checked ); ?> />
						<span class="mep-el__switch-ui"></span>
					</label>
				</div>
				<?php
			}

			private static function render_select( $name, $id, $label, $hint, $options, $value ) {
				?>
				<div class="mep-el__row mep-el__row--stack">
					<div class="mep-el__row-text">
						<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?><p class="mep-el__row-desc"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<select class="mep-el__select" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
						<?php foreach ( $options as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php
			}

			private static function render_text( $name, $id, $label, $hint, $value, $type = 'text' ) {
				?>
				<div class="mep-el__row mep-el__row--stack">
					<div class="mep-el__row-text">
						<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?><p class="mep-el__row-desc"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<input type="<?php echo esc_attr( $type ); ?>" class="mep-el__input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>" />
				</div>
				<?php
			}

			private static function render_textarea( $name, $id, $label, $hint, $value ) {
				?>
				<div class="mep-el__row mep-el__row--stack">
					<div class="mep-el__row-text">
						<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?><p class="mep-el__row-desc"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<textarea class="mep-el__input mep-ms__textarea" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="4"><?php echo esc_textarea( is_scalar( $value ) ? $value : '' ); ?></textarea>
				</div>
				<?php
			}

			private static function render_wysiwyg( $sec, $name, $label, $hint, $value ) {
				$editor_id = $sec . '-' . $name;
				?>
				<div class="mep-el__row mep-el__row--stack mep-ms__row--editor">
					<div class="mep-el__row-text">
						<span class="mep-el__row-label"><?php echo esc_html( $label ); ?></span>
						<?php if ( $hint ) : ?><p class="mep-el__row-desc"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<div class="mep-ms__editor">
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

			private static function render_file( $name, $id, $label, $hint, $value, $btn_label ) {
				?>
				<div class="mep-el__row mep-el__row--stack">
					<div class="mep-el__row-text">
						<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?><p class="mep-el__row-desc"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<div class="mep-ms__file">
						<input type="text" class="mep-el__input wpsa-url" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>" />
						<button type="button" class="mep-ms__file-btn wpsa-browse"><?php echo esc_html( $btn_label ); ?></button>
					</div>
				</div>
				<?php
			}

			private static function render_color( $name, $id, $label, $hint, $value, $default ) {
				$value = $value ? $value : $default;
				?>
				<div class="mep-el__row mep-el__row--stack">
					<div class="mep-el__row-text">
						<label class="mep-el__row-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?><p class="mep-el__row-desc"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<input type="text" class="mep-el__input wp-color-picker-field" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default ); ?>" />
				</div>
				<?php
			}

			private static function render_multicheck( $sec, $name, $label, $hint, $options, $value ) {
				?>
				<div class="mep-el__row mep-el__row--stack">
					<div class="mep-el__row-text">
						<span class="mep-el__row-label"><?php echo esc_html( $label ); ?></span>
						<?php if ( $hint ) : ?><p class="mep-el__row-desc"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<div class="mep-ms__checks">
						<input type="hidden" name="<?php echo esc_attr( $sec . '[' . $name . ']' ); ?>" value="" />
						<?php foreach ( $options as $key => $lab ) : ?>
							<label class="mep-ms__check">
								<input type="checkbox" name="<?php echo esc_attr( $sec . '[' . $name . '][' . $key . ']' ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( isset( $value[ $key ] ) && (string) $value[ $key ] === (string) $key ); ?> />
								<span><?php echo esc_html( $lab ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
			}
		}
	}
