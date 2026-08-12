<?php
	/**
	 * Modern Email Settings hub UI (Confirmation / PDF / Waitlist)
	 * + Send Test Email modal / AJAX.
	 *
	 * Option keys stay identical to the Settings API groups so existing data is safe.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Email_Settings_UI' ) ) {
		class MPWEM_Email_Settings_UI {

			public static function init() {
				add_action( 'wp_ajax_mep_send_test_email', array( __CLASS__, 'ajax_send_test_email' ) );
				add_filter( 'teeny_mce_buttons', array( __CLASS__, 'filter_teeny_mce_buttons' ), 10, 2 );
			}

			/**
			 * Keep email body editors to a short toolbar with alignment.
			 *
			 * @param array  $buttons   Teeny toolbar buttons.
			 * @param string $editor_id Editor id.
			 * @return array
			 */
			public static function filter_teeny_mce_buttons( $buttons, $editor_id ) {
				$email_editors = array(
					'email_setting_sec-mep_confirmation_email_text',
					'mep_pdf_email_settings-mep_pdf_email_content',
					'mep_waitlist_email_settings-mep_waitlist_email_template',
					'mep_waitlist_email_settings-mep_waitlist_spot_available_template',
					'mep_waitlist_email_settings-mep_waitlist_customer_email_template',
				);
				if ( ! in_array( $editor_id, $email_editors, true ) ) {
					return $buttons;
				}
				return array( 'bold', 'italic', 'underline', 'alignleft', 'aligncenter', 'alignright', 'bullist', 'numlist', 'link' );
			}

			/**
			 * Render the full Email Settings hub (header, sub-nav, panels, modal).
			 *
			 * @param array $email_subtabs Subtab map from get_email_settings_subtabs().
			 * @param array $fields        Settings fields map.
			 */
			public static function render_hub( $email_subtabs, $fields ) {
				$show_subnav = count( $email_subtabs ) > 1;
				$first_sub   = array_key_first( $email_subtabs );
				?>
				<div class="mep-em">
					<div class="mep-em__header">
						<div class="mep-em__header-text">
							<h2 class="mep-em__title"><?php esc_html_e( 'Email Settings', 'mage-eventpress' ); ?></h2>
							<p class="mep-em__subtitle"><?php esc_html_e( 'Configure automated email communications and templates.', 'mage-eventpress' ); ?></p>
						</div>
						<button type="button" class="mep-em__test-btn" id="mep-em-test-btn">
							<span class="fas fa-paper-plane"></span>
							<?php esc_html_e( 'Send Test Email', 'mage-eventpress' ); ?>
						</button>
					</div>

					<?php if ( $show_subnav ) : ?>
						<nav class="mep-em__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Email Settings', 'mage-eventpress' ); ?>">
							<?php
							$i = 0;
							foreach ( $email_subtabs as $sub_id => $sub_cfg ) :
								$active = ( 0 === $i );
								?>
								<button type="button"
									class="mep-em__tab<?php echo $active ? ' mep-em--active' : ''; ?>"
									role="tab"
									aria-selected="<?php echo $active ? 'true' : 'false'; ?>"
									data-email-sub="<?php echo esc_attr( $sub_id ); ?>">
									<span class="mep-em__tab-icon <?php echo esc_attr( $sub_cfg['icon'] ); ?>"></span>
									<span class="mep-em__tab-label"><?php echo esc_html( $sub_cfg['label'] ); ?></span>
								</button>
								<?php
								$i++;
							endforeach;
							?>
						</nav>
					<?php endif; ?>

					<div class="mep-em__panels">
						<?php
						$i = 0;
						foreach ( $email_subtabs as $sub_id => $sub_cfg ) :
							$active = ( 0 === $i ) || ( ! $show_subnav && $sub_id === $first_sub );
							?>
							<div class="mep-em__panel mep-gs__email-subpanel<?php echo $active ? ' mep-gs--active mep-em--active' : ''; ?>"
								id="mep-email-sub-<?php echo esc_attr( $sub_id ); ?>"
								data-email-sub="<?php echo esc_attr( $sub_id ); ?>"
								role="tabpanel">
								<?php self::render_panel( $sub_id, $fields ); ?>
							</div>
							<?php
							$i++;
						endforeach;
						?>
					</div>
				</div>
				<?php
				self::render_test_modal();
			}

			/**
			 * @param string $sub_id Section id.
			 * @param array  $fields Fields map.
			 */
			private static function render_panel( $sub_id, $fields ) {
				if ( empty( $fields[ $sub_id ] ) && 'email_setting_sec' !== $sub_id ) {
					return;
				}

				echo '<form method="post" action="options.php" class="mep-em__form" data-email-type="' . esc_attr( $sub_id ) . '">';
				settings_fields( $sub_id );

				if ( 'email_setting_sec' === $sub_id ) {
					self::render_confirmation();
				} elseif ( 'mep_pdf_email_settings' === $sub_id ) {
					self::render_pdf();
				} elseif ( 'mep_waitlist_email_settings' === $sub_id ) {
					self::render_waitlist();
				} else {
					do_settings_sections( $sub_id );
				}

				echo '<div style="display:none;">';
				submit_button();
				echo '</div></form>';
			}

			/**
			 * Minimal TinyMCE settings for email body fields (no media, lean toolbar).
			 *
			 * @param string $textarea_name Form field name.
			 * @return array
			 */
			private static function get_minimal_editor_settings( $textarea_name ) {
				return array(
					'media_buttons'    => false,
					'drag_drop_upload' => false,
					'teeny'            => true,
					'textarea_name'    => $textarea_name,
					'textarea_rows'    => 8,
					'editor_class'     => 'mep-em-body-editor',
					'default_editor'   => 'tinymce',
					'quicktags'        => array(
						'buttons' => 'strong,em,link,ul,ol,close',
					),
					'tinymce'          => array(
						'toolbar1'         => 'bold,italic,underline,alignleft,aligncenter,alignright,bullist,numlist,link',
						'toolbar2'         => '',
						'toolbar3'         => '',
						'toolbar4'         => '',
						'menubar'          => false,
						'statusbar'        => false,
						'branding'         => false,
						'wp_autoresize_on' => true,
						'resize'           => 'vertical',
					),
				);
			}

			/**
			 * Labeled email body editor with Visual/Code switcher (defaults to Visual).
			 *
			 * @param string $label         Field label.
			 * @param string $content       Editor content.
			 * @param string $editor_id     Unique editor id.
			 * @param string $textarea_name Form field name.
			 * @param string $wl_type       Optional waitlist type key for test-email mapping.
			 */
			private static function render_body_editor_field( $label, $content, $editor_id, $textarea_name, $wl_type = '' ) {
				?>
				<div class="mep-em__field mep-em__field--editor"<?php echo $wl_type ? ' data-em-wl-body="' . esc_attr( $wl_type ) . '"' : ''; ?>>
					<div class="mep-em__editor-head">
						<label class="mep-em__label"><?php echo esc_html( $label ); ?></label>
						<div class="mep-em__mode-tabs" data-editor-tabs-for="<?php echo esc_attr( $editor_id ); ?>"></div>
					</div>
					<div class="mep-em__editor" data-em-editor="<?php echo esc_attr( $editor_id ); ?>">
						<?php
						wp_editor(
							$content,
							$editor_id,
							self::get_minimal_editor_settings( $textarea_name )
						);
						?>
					</div>
				</div>
				<?php
			}

			/**
			 * Preset email body templates used when no content is saved yet.
			 *
			 * @param string $type confirmation|pdf|waitlist_admin|waitlist_spot|waitlist_customer
			 * @return string
			 */
			public static function get_preset_template( $type ) {
				switch ( $type ) {
					case 'confirmation':
						return '<p>Hi {name},</p>
<p>Thanks for joining the event.</p>
<p>Here are your event details:</p>
<p><strong>Event Name:</strong> {event}<br>
<strong>Ticket Type:</strong> {ticket_type}<br>
<strong>Event Date:</strong> {event_date}<br>
<strong>Start Time:</strong> {event_time}<br>
<strong>Full DateTime:</strong> {event_datetime}<br>
<strong>Payment Method:</strong> {payment_method}<br>
<strong>Amount Paid:</strong> {amount_paid}<br>
<strong>Order ID:</strong> {order_id}</p>
<p>We look forward to seeing you there.</p>
<p>Thanks</p>';

					case 'pdf':
						return '<p>Hello {customer_name},</p>
<p>Thank you for registering.</p>
<p>Please download your PDF ticket from the attachment and bring a printed or digital copy to the event.</p>
<p><strong>Event Name:</strong> {event_name}<br>
<strong>Event Date:</strong> {event_date}<br>
<strong>Event Venue:</strong> {event_venue}<br>
<strong>Payment Method:</strong> {payment_method}<br>
<strong>Amount Paid:</strong> {amount_paid}<br>
<strong>Order ID:</strong> {order_id}</p>
<p>See you at the event.</p>';

					case 'waitlist_admin':
						// Body-only preset (full HTML documents break TinyMCE Visual mode).
						return '<p>Hello Admin,</p>
<p>A new guest has joined the waitlist.</p>
<p><strong>Name:</strong> {name}<br>
<strong>Email:</strong> {email}<br>
<strong>Phone:</strong> {phone}<br>
<strong>Event:</strong> {event_name}<br>
<strong>Date:</strong> {event_date}<br>
<strong>Tickets:</strong> {ticket_qty}</p>
<p><a href="{admin_url}">View waitlist in admin</a></p>
<p>Submitted at {current_time}</p>';

					case 'waitlist_spot':
						return '<p>Hi {name},</p>
<p>Great news! A spot is now available for <strong>{event_name}</strong> on {event_date}.</p>
<p>You requested <strong>{ticket_qty}</strong> ticket(s).</p>
<p><a href="{event_url}">Book your tickets now</a></p>
<p>Spots are limited and available on a first-come, first-served basis.</p>
<p>Best regards,<br>Event Team</p>';

					case 'waitlist_customer':
						return '<p>Dear {name},</p>
<p>Thank you for joining the waitlist for <strong>{event_name}</strong>.</p>
<p><strong>Event:</strong> {event_name}<br>
<strong>Date:</strong> {event_date}<br>
<strong>Tickets requested:</strong> {ticket_qty}</p>
<p>We will email you as soon as tickets become available.</p>
<p><a href="{event_url}">View event details</a></p>
<p>Best regards,<br>{site_name}</p>';
				}

				return '';
			}

			/**
			 * Read email body without wp_kses_post stripping (needed for HTML templates).
			 *
			 * @param string $key     Option key.
			 * @param string $section Option group.
			 * @param string $type    Preset type key.
			 * @return string
			 */
			private static function get_email_body_or_preset( $key, $section, $type ) {
				$options = get_option( $section, array() );
				$value   = ( is_array( $options ) && isset( $options[ $key ] ) ) ? $options[ $key ] : '';
				if ( is_string( $value ) ) {
					$value = trim( $value );
				}
				// Treat blank or tag-only shells as "no content".
				if ( '' === $value || '' === trim( wp_strip_all_tags( $value ) ) ) {
					return self::get_preset_template( $type );
				}
				// Full HTML email documents break TinyMCE Visual — use inner <body> when present.
				if ( preg_match( '/<body[^>]*>(.*)<\/body>/is', $value, $matches ) ) {
					$inner = trim( $matches[1] );
					if ( '' !== $inner && '' !== trim( wp_strip_all_tags( $inner ) ) ) {
						return $inner;
					}
					return self::get_preset_template( $type );
				}
				if ( preg_match( '/<!DOCTYPE|<html[\s>]/i', $value ) ) {
					return self::get_preset_template( $type );
				}
				return $value;
			}

			/* ───────────── Confirmation Email ───────────── */

			private static function render_confirmation() {
				$sec     = 'email_setting_sec';
				$from    = mep_get_option( 'mep_email_form_name', $sec, get_bloginfo( 'name' ) );
				$email   = mep_get_option( 'mep_email_form_email', $sec, get_option( 'admin_email' ) );
				$subject = mep_get_option( 'mep_email_subject', $sec, 'Event Notification' );
				$body    = self::get_email_body_or_preset( 'mep_confirmation_email_text', $sec, 'confirmation' );
				$status  = mep_get_option( 'mep_email_sending_order_status', $sec, array( 'completed' => 'completed' ) );
				$billing = mep_get_option( 'mep_send_confirmation_to_billing_email', $sec, 'enable' );
				if ( ! is_array( $status ) ) {
					$status = array();
				}

				$vars = array( '{name}', '{event}', '{ticket_type}', '{order_id}', '{event_date}', '{event_time}', '{event_datetime}', '{payment_method}', '{amount_paid}' );
				?>
				<div class="mep-em__grid">
					<div class="mep-em__col-main">
						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-user"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Sender Identity', 'mage-eventpress' ); ?></h3>
									<p class="mep-em__card-desc"><?php esc_html_e( 'Configure who the email appears to be from.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
							<div class="mep-em__card-body">
								<div class="mep-em__row-2">
									<div class="mep-em__field">
										<label class="mep-em__label" for="mep-em-from-name"><?php esc_html_e( 'From Name', 'mage-eventpress' ); ?></label>
										<input type="text" class="mep-em__input" id="mep-em-from-name" name="<?php echo esc_attr( $sec ); ?>[mep_email_form_name]" value="<?php echo esc_attr( $from ); ?>" data-em-field="from_name" />
										<p class="mep-em__hint"><?php esc_html_e( 'Sender name shown on confirmation emails.', 'mage-eventpress' ); ?></p>
									</div>
									<div class="mep-em__field">
										<label class="mep-em__label" for="mep-em-from-email"><?php esc_html_e( 'From Email', 'mage-eventpress' ); ?></label>
										<input type="email" class="mep-em__input" id="mep-em-from-email" name="<?php echo esc_attr( $sec ); ?>[mep_email_form_email]" value="<?php echo esc_attr( $email ); ?>" data-em-field="from_email" />
										<p class="mep-em__hint"><?php esc_html_e( 'Sender address for confirmation emails.', 'mage-eventpress' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-file-alt"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Email Content', 'mage-eventpress' ); ?></h3>
									<p class="mep-em__card-desc"><?php esc_html_e( 'Design the content of your automated email.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
							<div class="mep-em__card-body">
								<div class="mep-em__field">
									<label class="mep-em__label" for="mep-em-subject"><?php esc_html_e( 'Email Subject', 'mage-eventpress' ); ?></label>
									<input type="text" class="mep-em__input" id="mep-em-subject" name="<?php echo esc_attr( $sec ); ?>[mep_email_subject]" value="<?php echo esc_attr( $subject ); ?>" data-em-field="subject" />
									<p class="mep-em__hint"><?php esc_html_e( 'Subject line for the confirmation email.', 'mage-eventpress' ); ?></p>
								</div>
								<?php
								self::render_body_editor_field(
									__( 'Confirmation Email Body', 'mage-eventpress' ),
									$body,
									'email_setting_sec-mep_confirmation_email_text',
									$sec . '[mep_confirmation_email_text]'
								);
								?>
								<p class="mep-em__hint"><?php esc_html_e( 'Use the variables panel on the right to insert dynamic content placeholders.', 'mage-eventpress' ); ?></p>
							</div>
						</div>
					</div>

					<div class="mep-em__col-side">
						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-bolt"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Triggers & Rules', 'mage-eventpress' ); ?></h3>
								</div>
							</div>
							<div class="mep-em__card-body">
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Send Confirmation on Order Status', 'mage-eventpress' ); ?></label>
									<div class="mep-em__check-box">
										<input type="hidden" name="<?php echo esc_attr( $sec ); ?>[mep_email_sending_order_status]" value="" />
										<?php foreach ( array( 'processing' => __( 'Processing', 'mage-eventpress' ), 'completed' => __( 'Completed', 'mage-eventpress' ) ) as $key => $label ) : ?>
											<label class="mep-em__check">
												<input type="checkbox" name="<?php echo esc_attr( $sec ); ?>[mep_email_sending_order_status][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $key ); ?>" <?php checked( isset( $status[ $key ] ) && $status[ $key ] === $key ); ?> />
												<span><?php echo esc_html( $label ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
									<p class="mep-em__hint"><?php esc_html_e( 'Choose which order statuses trigger the email.', 'mage-eventpress' ); ?></p>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label" for="mep-em-billing"><?php esc_html_e( 'Send Confirmation to Billing Email', 'mage-eventpress' ); ?></label>
									<select class="mep-em__select" id="mep-em-billing" name="<?php echo esc_attr( $sec ); ?>[mep_send_confirmation_to_billing_email]">
										<option value="enable" <?php selected( $billing, 'enable' ); ?>><?php esc_html_e( 'Enable', 'mage-eventpress' ); ?></option>
										<option value="disable" <?php selected( $billing, 'disable' ); ?>><?php esc_html_e( 'Disable', 'mage-eventpress' ); ?></option>
									</select>
									<p class="mep-em__hint"><?php esc_html_e( 'Send copy to the billing address.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
						</div>

						<?php self::render_variables_card( $vars, 'email_setting_sec-mep_confirmation_email_text' ); ?>
					</div>
				</div>
				<?php
			}

			/* ───────────── PDF Email ───────────── */

			private static function render_pdf() {
				$sec = 'mep_pdf_email_settings';
				$get = function( $key, $default = '' ) use ( $sec ) {
					return mep_get_option( $key, $sec, $default );
				};

				$status = $get( 'mep_pdf_email_status', array() );
				if ( ! is_array( $status ) ) {
					$status = array();
				}

				$vars = array( '{customer_name}', '{event_name}', '{event_venue}', '{event_date}', '{order_id}', '{payment_method}', '{amount_paid}' );
				?>
				<div class="mep-em__grid">
					<div class="mep-em__col-main">
						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-user"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Sender Identity', 'mage-eventpress' ); ?></h3>
									<p class="mep-em__card-desc"><?php esc_html_e( 'Configure who the PDF email appears to be from.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
							<div class="mep-em__card-body">
								<div class="mep-em__row-2">
									<div class="mep-em__field">
										<label class="mep-em__label"><?php esc_html_e( 'From Name', 'mage-eventpress' ); ?></label>
										<input type="text" class="mep-em__input" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_email_from_name]" value="<?php echo esc_attr( $get( 'mep_pdf_email_from_name', get_bloginfo( 'name' ) ) ); ?>" data-em-field="from_name" />
										<p class="mep-em__hint"><?php esc_html_e( 'Sender name for PDF emails.', 'mage-eventpress' ); ?></p>
									</div>
									<div class="mep-em__field">
										<label class="mep-em__label"><?php esc_html_e( 'From Email', 'mage-eventpress' ); ?></label>
										<input type="email" class="mep-em__input" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_email_from]" value="<?php echo esc_attr( $get( 'mep_pdf_email_from', get_option( 'admin_email' ) ) ); ?>" data-em-field="from_email" />
										<p class="mep-em__hint"><?php esc_html_e( 'Sender email for PDF emails.', 'mage-eventpress' ); ?></p>
									</div>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Admin Copy Email', 'mage-eventpress' ); ?></label>
									<input type="email" class="mep-em__input" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_admin_notification_email]" value="<?php echo esc_attr( $get( 'mep_pdf_admin_notification_email', get_option( 'admin_email' ) ) ); ?>" />
									<p class="mep-em__hint"><?php esc_html_e( 'Email address that receives a copy of each PDF ticket.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
						</div>

						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-file-alt"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Email Content', 'mage-eventpress' ); ?></h3>
									<p class="mep-em__card-desc"><?php esc_html_e( 'Subject and body for the PDF ticket email.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
							<div class="mep-em__card-body">
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Email Subject', 'mage-eventpress' ); ?></label>
									<input type="text" class="mep-em__input" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_email_subject]" value="<?php echo esc_attr( $get( 'mep_pdf_email_subject', 'PDF Ticket Confirmation' ) ); ?>" data-em-field="subject" />
								</div>
								<?php
								self::render_body_editor_field(
									__( 'Email Content', 'mage-eventpress' ),
									self::get_email_body_or_preset( 'mep_pdf_email_content', $sec, 'pdf' ),
									'mep_pdf_email_settings-mep_pdf_email_content',
									$sec . '[mep_pdf_email_content]'
								);
								?>
								<p class="mep-em__hint"><?php esc_html_e( 'Use the variables panel on the right to insert dynamic content placeholders.', 'mage-eventpress' ); ?></p>
							</div>
						</div>
					</div>

					<div class="mep-em__col-side">
						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-bolt"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Triggers & Rules', 'mage-eventpress' ); ?></h3>
								</div>
							</div>
							<div class="mep-em__card-body">
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Attach Ticket When', 'mage-eventpress' ); ?></label>
									<select class="mep-em__select" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_send_status]">
										<option value="yes" <?php selected( $get( 'mep_pdf_send_status', 'yes' ), 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $get( 'mep_pdf_send_status', 'yes' ), 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Send PDF Email on Status', 'mage-eventpress' ); ?></label>
									<div class="mep-em__check-box">
										<input type="hidden" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_email_status]" value="" />
										<?php
										$opts = array(
											'pending'    => __( 'Pending', 'mage-eventpress' ),
											'on-hold'    => __( 'On Hold', 'mage-eventpress' ),
											'processing' => __( 'Processing', 'mage-eventpress' ),
											'completed'  => __( 'Completed', 'mage-eventpress' ),
										);
										foreach ( $opts as $key => $label ) :
											?>
											<label class="mep-em__check">
												<input type="checkbox" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_email_status][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $key ); ?>" <?php checked( isset( $status[ $key ] ) && (string) $status[ $key ] === (string) $key ); ?> />
												<span><?php echo esc_html( $label ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Add to Calendar Link', 'mage-eventpress' ); ?></label>
									<select class="mep-em__select" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_add_to_calendar]">
										<option value="yes" <?php selected( $get( 'mep_pdf_add_to_calendar', 'no' ), 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $get( 'mep_pdf_add_to_calendar', 'no' ), 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Send PDF to Billing Email', 'mage-eventpress' ); ?></label>
									<select class="mep-em__select" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_send_to_billing]">
										<option value="yes" <?php selected( $get( 'mep_pdf_send_to_billing', 'yes' ), 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $get( 'mep_pdf_send_to_billing', 'yes' ), 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Send PDF to Each Attendee', 'mage-eventpress' ); ?></label>
									<select class="mep-em__select" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_send_to_attendees]">
										<option value="yes" <?php selected( $get( 'mep_pdf_send_to_attendees', 'no' ), 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $get( 'mep_pdf_send_to_attendees', 'no' ), 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'When to Email Attendees', 'mage-eventpress' ); ?></label>
									<select class="mep-em__select" name="<?php echo esc_attr( $sec ); ?>[mep_pdf_attendee_email_condition]">
										<option value="all" <?php selected( $get( 'mep_pdf_attendee_email_condition', 'all' ), 'all' ); ?>><?php esc_html_e( 'Send to all attendees', 'mage-eventpress' ); ?></option>
										<option value="different" <?php selected( $get( 'mep_pdf_attendee_email_condition', 'all' ), 'different' ); ?>><?php esc_html_e( 'Only if differs from billing', 'mage-eventpress' ); ?></option>
										<option value="form" <?php selected( $get( 'mep_pdf_attendee_email_condition', 'all' ), 'form' ); ?>><?php esc_html_e( 'Only if form enables confirmation', 'mage-eventpress' ); ?></option>
									</select>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Confirmation to Billing Email', 'mage-eventpress' ); ?></label>
									<select class="mep-em__select" name="<?php echo esc_attr( $sec ); ?>[mep_send_confirmation_to_billing]">
										<option value="yes" <?php selected( $get( 'mep_send_confirmation_to_billing', 'yes' ), 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $get( 'mep_send_confirmation_to_billing', 'yes' ), 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
								</div>
								<div class="mep-em__field">
									<label class="mep-em__label"><?php esc_html_e( 'Confirmation to Attendees', 'mage-eventpress' ); ?></label>
									<select class="mep-em__select" name="<?php echo esc_attr( $sec ); ?>[mep_send_confirmation_to_attendees]">
										<option value="yes" <?php selected( $get( 'mep_send_confirmation_to_attendees', 'no' ), 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $get( 'mep_send_confirmation_to_attendees', 'no' ), 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
								</div>
							</div>
						</div>

						<?php self::render_variables_card( $vars, 'mep_pdf_email_settings-mep_pdf_email_content' ); ?>
					</div>
				</div>
				<?php
			}

			/* ───────────── Waitlist Email ───────────── */

			private static function render_waitlist() {
				$sec = 'mep_waitlist_email_settings';
				$get = function( $key, $default = '' ) use ( $sec ) {
					return mep_get_option( $key, $sec, $default );
				};

				$vars = array( '{name}', '{email}', '{phone}', '{event_name}', '{event_date}', '{ticket_qty}', '{event_url}', '{admin_url}', '{site_name}', '{current_time}' );
				?>
				<div class="mep-em__grid">
					<div class="mep-em__col-main">
						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-user-shield"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Admin Notification', 'mage-eventpress' ); ?></h3>
									<p class="mep-em__card-desc"><?php esc_html_e( 'Email the admin when someone joins the waitlist.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
							<div class="mep-em__card-body">
								<input type="hidden" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_admin_email_enable]" value="off" />
								<label class="mep-em__check mep-em__check--inline">
									<input type="checkbox" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_admin_email_enable]" value="on" <?php checked( $get( 'mep_waitlist_admin_email_enable', 'on' ), 'on' ); ?> />
									<span><?php esc_html_e( 'Notify Admin of New Waitlist Entries', 'mage-eventpress' ); ?></span>
								</label>
								<div class="mep-em__row-2" style="margin-top:14px;">
									<div class="mep-em__field">
										<label class="mep-em__label"><?php esc_html_e( 'Admin Email', 'mage-eventpress' ); ?></label>
										<input type="email" class="mep-em__input" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_admin_email]" value="<?php echo esc_attr( $get( 'mep_waitlist_admin_email', get_option( 'admin_email' ) ) ); ?>" data-em-field="from_email" />
									</div>
									<div class="mep-em__field">
										<label class="mep-em__label"><?php esc_html_e( 'Email Subject', 'mage-eventpress' ); ?></label>
										<input type="text" class="mep-em__input" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_email_subject]" value="<?php echo esc_attr( $get( 'mep_waitlist_email_subject', __( 'New Waitlist Entry - {event_name}', 'mage-eventpress' ) ) ); ?>" data-em-field="subject" data-em-wl-type="admin" />
									</div>
								</div>
								<?php
								self::render_body_editor_field(
									__( 'Email Template', 'mage-eventpress' ),
									self::get_email_body_or_preset( 'mep_waitlist_email_template', $sec, 'waitlist_admin' ),
									'mep_waitlist_email_settings-mep_waitlist_email_template',
									$sec . '[mep_waitlist_email_template]',
									'admin'
								);
								?>
							</div>
						</div>

						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-door-open"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Seat Available Email', 'mage-eventpress' ); ?></h3>
									<p class="mep-em__card-desc"><?php esc_html_e( 'Notify waitlisted guests when seats open.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
							<div class="mep-em__card-body">
								<input type="hidden" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_auto_notify_enable]" value="off" />
								<label class="mep-em__check mep-em__check--inline">
									<input type="checkbox" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_auto_notify_enable]" value="on" <?php checked( $get( 'mep_waitlist_auto_notify_enable', 'on' ), 'on' ); ?> />
									<span><?php esc_html_e( 'Notify When Seats Open', 'mage-eventpress' ); ?></span>
								</label>
								<div class="mep-em__field" style="margin-top:14px;">
									<label class="mep-em__label"><?php esc_html_e( 'Seat Available Email Subject', 'mage-eventpress' ); ?></label>
									<input type="text" class="mep-em__input" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_spot_available_subject]" value="<?php echo esc_attr( $get( 'mep_waitlist_spot_available_subject', __( 'Spot Available: {event_name}', 'mage-eventpress' ) ) ); ?>" data-em-wl-type="spot" />
								</div>
								<?php
								self::render_body_editor_field(
									__( 'Seat Available Email Body', 'mage-eventpress' ),
									self::get_email_body_or_preset( 'mep_waitlist_spot_available_template', $sec, 'waitlist_spot' ),
									'mep_waitlist_email_settings-mep_waitlist_spot_available_template',
									$sec . '[mep_waitlist_spot_available_template]',
									'spot'
								);
								?>
							</div>
						</div>

						<div class="mep-em__card">
							<div class="mep-em__card-head">
								<span class="mep-em__card-icon"><i class="fas fa-envelope-open-text"></i></span>
								<div>
									<h3 class="mep-em__card-title"><?php esc_html_e( 'Customer Confirmation', 'mage-eventpress' ); ?></h3>
									<p class="mep-em__card-desc"><?php esc_html_e( 'Email guests when they join the waitlist.', 'mage-eventpress' ); ?></p>
								</div>
							</div>
							<div class="mep-em__card-body">
								<input type="hidden" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_customer_email_enable]" value="off" />
								<label class="mep-em__check mep-em__check--inline">
									<input type="checkbox" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_customer_email_enable]" value="on" <?php checked( $get( 'mep_waitlist_customer_email_enable', 'on' ), 'on' ); ?> />
									<span><?php esc_html_e( 'Waitlist Confirmation Email', 'mage-eventpress' ); ?></span>
								</label>
								<div class="mep-em__field" style="margin-top:14px;">
									<label class="mep-em__label"><?php esc_html_e( 'Confirmation Email Subject', 'mage-eventpress' ); ?></label>
									<input type="text" class="mep-em__input" name="<?php echo esc_attr( $sec ); ?>[mep_waitlist_customer_email_subject]" value="<?php echo esc_attr( $get( 'mep_waitlist_customer_email_subject', __( 'Thank you for joining the waitlist - {event_name}', 'mage-eventpress' ) ) ); ?>" data-em-wl-type="customer" />
								</div>
								<?php
								self::render_body_editor_field(
									__( 'Confirmation Email Body', 'mage-eventpress' ),
									self::get_email_body_or_preset( 'mep_waitlist_customer_email_template', $sec, 'waitlist_customer' ),
									'mep_waitlist_email_settings-mep_waitlist_customer_email_template',
									$sec . '[mep_waitlist_customer_email_template]',
									'customer'
								);
								?>
							</div>
						</div>
					</div>

					<div class="mep-em__col-side">
						<?php self::render_variables_card( $vars, null, false, true ); ?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param array       $vars           Placeholder list.
			 * @param string|null $editor_id      TinyMCE editor id for insert target.
			 * @param bool        $for_ta         Insert into focused textarea when true.
			 * @param bool        $for_wl_editors Insert into last focused waitlist editor.
			 */
			private static function render_variables_card( $vars, $editor_id = null, $for_ta = false, $for_wl_editors = false ) {
				?>
				<div class="mep-em__card mep-em__card--vars">
					<div class="mep-em__card-head">
						<span class="mep-em__card-icon"><i class="fas fa-code"></i></span>
						<div>
							<h3 class="mep-em__card-title"><?php esc_html_e( 'Dynamic Variables', 'mage-eventpress' ); ?></h3>
						</div>
					</div>
					<div class="mep-em__card-body">
						<p class="mep-em__hint" style="margin-top:0;"><?php esc_html_e( 'Click a variable to insert it into the email body.', 'mage-eventpress' ); ?></p>
						<div class="mep-em__vars"
							<?php echo $editor_id ? ' data-editor="' . esc_attr( $editor_id ) . '"' : ''; ?>
							<?php echo $for_ta ? ' data-for-textarea="1"' : ''; ?>
							<?php echo $for_wl_editors ? ' data-for-wl-editors="1"' : ''; ?>>
							<?php foreach ( $vars as $var ) : ?>
								<button type="button" class="mep-em__var" data-var="<?php echo esc_attr( $var ); ?>"><?php echo esc_html( $var ); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php
			}

			private static function render_test_modal() {
				$admin_email = get_option( 'admin_email' );
				?>
				<div class="mep-em-modal" id="mep-em-test-modal" aria-hidden="true">
					<div class="mep-em-modal__backdrop" data-em-close></div>
					<div class="mep-em-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mep-em-modal-title">
						<button type="button" class="mep-em-modal__close" data-em-close aria-label="<?php esc_attr_e( 'Close', 'mage-eventpress' ); ?>">
							<span class="fas fa-times"></span>
						</button>
						<div class="mep-em-modal__icon"><i class="fas fa-paper-plane"></i></div>
						<h3 class="mep-em-modal__title" id="mep-em-modal-title"><?php esc_html_e( 'Send Test Email', 'mage-eventpress' ); ?></h3>
						<p class="mep-em-modal__desc"><?php esc_html_e( 'Send a preview of the active email template to verify content and delivery.', 'mage-eventpress' ); ?></p>

						<div class="mep-em__field">
							<label class="mep-em__label" for="mep-em-test-to"><?php esc_html_e( 'Send To', 'mage-eventpress' ); ?></label>
							<input type="email" class="mep-em__input" id="mep-em-test-to" value="<?php echo esc_attr( $admin_email ); ?>" placeholder="you@example.com" />
						</div>

						<div class="mep-em__field mep-em-modal__wl-type" id="mep-em-wl-type-wrap" style="display:none;">
							<label class="mep-em__label" for="mep-em-wl-type"><?php esc_html_e( 'Waitlist Template', 'mage-eventpress' ); ?></label>
							<select class="mep-em__select" id="mep-em-wl-type">
								<option value="admin"><?php esc_html_e( 'Admin Notification', 'mage-eventpress' ); ?></option>
								<option value="spot"><?php esc_html_e( 'Seat Available', 'mage-eventpress' ); ?></option>
								<option value="customer"><?php esc_html_e( 'Customer Confirmation', 'mage-eventpress' ); ?></option>
							</select>
						</div>

						<div class="mep-em-modal__status" id="mep-em-test-status" hidden></div>

						<div class="mep-em-modal__actions">
							<button type="button" class="mep-em-modal__cancel" data-em-close><?php esc_html_e( 'Cancel', 'mage-eventpress' ); ?></button>
							<button type="button" class="mep-em-modal__send" id="mep-em-test-send">
								<span class="fas fa-paper-plane"></span>
								<?php esc_html_e( 'Send Test', 'mage-eventpress' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php
			}

			/**
			 * AJAX: send test email for the active email tab.
			 */
			public static function ajax_send_test_email() {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mage-eventpress' ) ), 403 );
				}
				check_ajax_referer( 'mep_send_test_email', 'nonce' );

				$to      = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
				$type    = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'email_setting_sec';
				$wl_type = isset( $_POST['wl_type'] ) ? sanitize_key( wp_unslash( $_POST['wl_type'] ) ) : 'admin';
				$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
				$body    = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
				$from_n  = isset( $_POST['from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['from_name'] ) ) : get_bloginfo( 'name' );
				$from_e  = isset( $_POST['from_email'] ) ? sanitize_email( wp_unslash( $_POST['from_email'] ) ) : get_option( 'admin_email' );

				if ( ! is_email( $to ) ) {
					wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'mage-eventpress' ) ) );
				}

				// Fall back to saved options when the client did not send body/subject.
				if ( 'email_setting_sec' === $type ) {
					if ( '' === $subject ) {
						$subject = mep_get_option( 'mep_email_subject', 'email_setting_sec', 'Event Notification' );
					}
					if ( '' === trim( wp_strip_all_tags( $body ) ) ) {
						$body = self::get_preset_template( 'confirmation' );
					}
					if ( '' === $from_n ) {
						$from_n = mep_get_option( 'mep_email_form_name', 'email_setting_sec', get_bloginfo( 'name' ) );
					}
					if ( '' === $from_e ) {
						$from_e = mep_get_option( 'mep_email_form_email', 'email_setting_sec', get_option( 'admin_email' ) );
					}
				} elseif ( 'mep_pdf_email_settings' === $type ) {
					if ( '' === $subject ) {
						$subject = mep_get_option( 'mep_pdf_email_subject', 'mep_pdf_email_settings', 'PDF Ticket Confirmation' );
					}
					if ( '' === trim( wp_strip_all_tags( $body ) ) ) {
						$body = self::get_preset_template( 'pdf' );
					}
					if ( '' === $from_n ) {
						$from_n = mep_get_option( 'mep_pdf_email_from_name', 'mep_pdf_email_settings', get_bloginfo( 'name' ) );
					}
					if ( '' === $from_e ) {
						$from_e = mep_get_option( 'mep_pdf_email_from', 'mep_pdf_email_settings', get_option( 'admin_email' ) );
					}
					$body .= '<p style="margin-top:16px;color:#64748b;font-size:12px;"><em>' . esc_html__( '(Test email — PDF attachment is not included.)', 'mage-eventpress' ) . '</em></p>';
				} elseif ( 'mep_waitlist_email_settings' === $type ) {
					$map = array(
						'admin'    => array( 'mep_waitlist_email_subject', 'waitlist_admin' ),
						'spot'     => array( 'mep_waitlist_spot_available_subject', 'waitlist_spot' ),
						'customer' => array( 'mep_waitlist_customer_email_subject', 'waitlist_customer' ),
					);
					$keys = isset( $map[ $wl_type ] ) ? $map[ $wl_type ] : $map['admin'];
					if ( '' === $subject ) {
						$subject = mep_get_option( $keys[0], 'mep_waitlist_email_settings', 'Waitlist Email' );
					}
					if ( '' === trim( wp_strip_all_tags( $body ) ) ) {
						$body = self::get_preset_template( $keys[1] );
					}
					$from_n = get_bloginfo( 'name' );
					$from_e = get_option( 'admin_email' );
				}

				$subject = '[TEST] ' . $subject;
				$sample  = array(
					'{name}'            => 'John Doe',
					'{customer_name}'   => 'John Doe',
					'{event}'           => 'Sample Event',
					'{event_name}'      => 'Sample Event',
					'{ticket_type}'     => 'General Admission',
					'{order_id}'        => '12345',
					'{event_date}'      => gmdate( 'Y-m-d' ),
					'{event_time}'      => '10:00 AM',
					'{event_datetime}'  => gmdate( 'Y-m-d' ) . ' 10:00 AM',
					'{event_venue}'     => 'Main Hall',
					'{payment_method}'  => 'Credit Card',
					'{amount_paid}'     => '$50.00',
					'{email}'           => $to,
					'{phone}'           => '+1 555 0100',
					'{ticket_qty}'      => '2',
					'{event_url}'       => home_url( '/' ),
					'{admin_url}'       => admin_url(),
					'{site_name}'       => get_bloginfo( 'name' ),
					'{current_time}'    => current_time( 'mysql' ),
				);
				$body = str_replace( array_keys( $sample ), array_values( $sample ), $body );
				$body = wpautop( $body );

				$headers   = array();
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				if ( $from_n && is_email( $from_e ) ) {
					$headers[] = 'From: ' . $from_n . ' <' . $from_e . '>';
				}

				$sent = wp_mail( $to, $subject, $body, $headers );
				if ( $sent ) {
					wp_send_json_success( array( 'message' => sprintf( /* translators: %s email */ __( 'Test email sent to %s.', 'mage-eventpress' ), $to ) ) );
				}
				wp_send_json_error( array( 'message' => __( 'Failed to send test email. Check your mail configuration.', 'mage-eventpress' ) ) );
			}
		}

		MPWEM_Email_Settings_UI::init();
	}
