<?php
	/**
	 * AI Assistant Settings — mockup-matched modern UI.
	 * Option group stays mep_ai_assistant_settings (keys unchanged).
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_AI_Assistant_Settings_UI' ) ) {
		class MPWEM_AI_Assistant_Settings_UI {

			const SECTION = 'mep_ai_assistant_settings';

			/**
			 * Provider blocks: slug => display title.
			 *
			 * @return array
			 */
			private static function providers() {
				return array(
					'chatgpt'    => __( 'OpenAI', 'mage-eventpress' ),
					'xai'        => __( 'xAI', 'mage-eventpress' ),
					'claude'     => __( 'Anthropic', 'mage-eventpress' ),
					'gemini'     => __( 'Google Gemini', 'mage-eventpress' ),
					'alibaba'    => __( 'Alibaba Cloud', 'mage-eventpress' ),
					'openrouter' => __( 'OpenRouter', 'mage-eventpress' ),
					'free_api'   => __( 'Free Provider', 'mage-eventpress' ),
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

				if ( empty( $all ) ) {
					?>
					<div class="mep-ai" id="mep_ai_assistant_settings" data-ms-section="<?php echo esc_attr( $sec ); ?>">
						<div class="mep-ai__header">
							<h2 class="mep-ai__title"><?php esc_html_e( 'AI Settings', 'mage-eventpress' ); ?></h2>
							<p class="mep-ai__subtitle"><?php esc_html_e( 'Configure your artificial intelligence preferences, API keys, and default models across various providers.', 'mage-eventpress' ); ?></p>
						</div>
						<div class="mep-ai__card">
							<div class="mep-ai__empty">
								<?php esc_html_e( 'AI Assistant settings are available with Event Manager Pro.', 'mage-eventpress' ); ?>
							</div>
						</div>
					</div>
					<?php
					return;
				}

				echo '<form method="post" action="options.php" class="mep-ai__form" id="mep_ai_assistant_settings">';
				settings_fields( $sec );
				?>
				<div class="mep-ai" data-ms-section="<?php echo esc_attr( $sec ); ?>">
					<div class="mep-ai__header">
						<h2 class="mep-ai__title"><?php esc_html_e( 'AI Settings', 'mage-eventpress' ); ?></h2>
						<p class="mep-ai__subtitle"><?php esc_html_e( 'Configure your artificial intelligence preferences, API keys, and default models across various providers.', 'mage-eventpress' ); ?></p>
					</div>

					<div class="mep-ai__card">
						<div class="mep-ai__card-head">
							<span class="mep-ai__card-icon mep-ai__card-icon--gear"><i class="fas fa-cog"></i></span>
							<h3 class="mep-ai__card-title"><?php esc_html_e( 'General', 'mage-eventpress' ); ?></h3>
						</div>
						<div class="mep-ai__card-body">
							<?php
							if ( isset( $by['mep_ai_enabled'] ) ) {
								$en = $by['mep_ai_enabled'];
								$en['label'] = __( 'Enable AI Assistant', 'mage-eventpress' );
								$en['desc']  = __( 'Toggle the AI assistant functionality on or off globally.', 'mage-eventpress' );
								self::render_toggle_field( $sec, $en );
							}
							if ( isset( $by['mep_ai_provider'] ) ) {
								$pr = $by['mep_ai_provider'];
								$pr['label'] = __( 'AI Provider', 'mage-eventpress' );
								$pr['desc']  = __( 'Select your preferred default AI provider.', 'mage-eventpress' );
								self::render_provider_select( $sec, $pr );
							}
							?>
						</div>
					</div>

					<div class="mep-ai__card">
						<div class="mep-ai__card-head">
							<span class="mep-ai__card-icon mep-ai__card-icon--key"><i class="fas fa-key"></i></span>
							<h3 class="mep-ai__card-title"><?php esc_html_e( 'Provider API Keys & Models', 'mage-eventpress' ); ?></h3>
						</div>
						<div class="mep-ai__card-body mep-ai__providers">
							<?php foreach ( self::providers() as $slug => $title ) : ?>
								<?php
								$key_name   = 'mep_ai_api_key_' . $slug;
								$model_name = 'mep_ai_model_' . $slug;
								if ( ! isset( $by[ $key_name ] ) && ! isset( $by[ $model_name ] ) ) {
									continue;
								}
								?>
								<div class="mep-ai__provider mep-ai-provider-row mep-ai-row-<?php echo esc_attr( $slug ); ?>">
									<h4 class="mep-ai__provider-name"><?php echo esc_html( $title ); ?></h4>
									<div class="mep-ai__provider-grid">
										<?php if ( isset( $by[ $key_name ] ) ) : ?>
											<?php self::render_api_key_cell( $sec, $by[ $key_name ], $slug ); ?>
										<?php endif; ?>
										<?php if ( isset( $by[ $model_name ] ) ) : ?>
											<?php self::render_model_cell( $sec, $by[ $model_name ], $slug ); ?>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div style="display:none;"><?php submit_button(); ?></div>
				</form>
				<?php
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
					if ( null !== $val ) {
						return $val;
					}
				}
				return $default;
			}

			private static function render_toggle_field( $sec, $field ) {
				$name    = $field['name'];
				$label   = isset( $field['label'] ) ? $field['label'] : $name;
				$hint    = isset( $field['desc'] ) ? $field['desc'] : '';
				$default = isset( $field['default'] ) ? $field['default'] : 'no';
				$value   = self::get_opt( $sec, $name, $default );
				$id      = 'mep-ai-' . sanitize_html_class( $name );
				$input_n = $sec . '[' . $name . ']';
				$checked = ( (string) $value === 'yes' );
				?>
				<div class="mep-ai__row mep-ai__row--toggle">
					<div class="mep-ai__row-text">
						<label class="mep-ai__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?>
							<p class="mep-ai__desc"><?php echo esc_html( $hint ); ?></p>
						<?php endif; ?>
					</div>
					<label class="mep-ai__switch">
						<input type="hidden" name="<?php echo esc_attr( $input_n ); ?>" value="no" />
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $input_n ); ?>" value="yes" <?php checked( $checked ); ?> />
						<span class="mep-ai__switch-ui"></span>
					</label>
				</div>
				<?php
			}

			private static function render_provider_select( $sec, $field ) {
				$name    = $field['name'];
				$label   = isset( $field['label'] ) ? $field['label'] : $name;
				$hint    = isset( $field['desc'] ) ? $field['desc'] : '';
				$default = isset( $field['default'] ) ? $field['default'] : '';
				$value   = self::get_opt( $sec, $name, $default );
				$options = isset( $field['options'] ) ? $field['options'] : array();
				$id      = 'mep-ai-' . sanitize_html_class( $name );
				$input_n = $sec . '[' . $name . ']';
				?>
				<div class="mep-ai__row mep-ai__row--stack">
					<label class="mep-ai__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<select class="mep-ai__control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $input_n ); ?>">
						<?php foreach ( $options as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php if ( $hint ) : ?>
						<p class="mep-ai__desc"><?php echo esc_html( $hint ); ?></p>
					<?php endif; ?>
				</div>
				<?php
			}

			private static function render_api_key_cell( $sec, $field, $slug ) {
				$name      = $field['name'];
				$default   = isset( $field['default'] ) ? $field['default'] : '';
				$value     = self::get_opt( $sec, $name, $default );
				$id        = 'mep-ai-' . sanitize_html_class( $name );
				$input_n   = $sec . '[' . $name . ']';
				$placeholders = array(
					'chatgpt'    => 'sk-...',
					'xai'        => 'xai-...',
					'claude'     => 'sk-ant-...',
					'gemini'     => 'AIza...',
					'alibaba'    => __( 'Enter Alibaba Key', 'mage-eventpress' ),
					'openrouter' => 'sk-or-v1-...',
					'free_api'   => __( 'Optional API key', 'mage-eventpress' ),
				);
				$ph = isset( $placeholders[ $slug ] ) ? $placeholders[ $slug ] : '';
				?>
				<div class="mep-ai__cell">
					<label class="mep-ai__field-label" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'API KEY', 'mage-eventpress' ); ?></label>
					<input type="password" class="mep-ai__control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $input_n ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? $value : '' ); ?>" placeholder="<?php echo esc_attr( $ph ); ?>" autocomplete="off" />
				</div>
				<?php
			}

			private static function render_model_cell( $sec, $field, $slug ) {
				$name    = $field['name'];
				$default = isset( $field['default'] ) ? $field['default'] : '';
				$value   = self::get_opt( $sec, $name, $default );
				$options = isset( $field['options'] ) ? $field['options'] : array();
				$id      = 'mep-ai-' . sanitize_html_class( $name );
				$input_n = $sec . '[' . $name . ']';
				?>
				<div class="mep-ai__cell">
					<label class="mep-ai__field-label" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'MODEL', 'mage-eventpress' ); ?></label>
					<select class="mep-ai__control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $input_n ); ?>">
						<?php foreach ( $options as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description mep-ai-model-status" aria-live="polite"></p>
				</div>
				<?php
			}
		}
	}
