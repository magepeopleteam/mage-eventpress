<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Quick_Setup' ) ) {
		class MPWEM_Quick_Setup {
			public function __construct() {
				if ( ! class_exists( 'MPTBM_Dependencies' ) ) {
					add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
				}
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_modern_assets' ) );
				add_action( 'admin_menu', array( $this, 'quick_setup_menu' ) );
			}

			public function add_admin_scripts( $hook ) {
				if ( strpos( (string) $hook, 'mpwem_quick_setup' ) === false ) {
					return;
				}
				wp_enqueue_style( 'mpwem_global', MPWEM_PLUGIN_URL . '/assets/helper/mp_style/mpwem_global.css', array(), MPWEM_PLUGIN_VERSION );
				wp_enqueue_script( 'mpwem_global', MPWEM_PLUGIN_URL . '/assets/helper/mp_style/mpwem_global.js', array( 'jquery' ), MPWEM_PLUGIN_VERSION, true );
				wp_enqueue_style( 'mpwem_admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_admin.css', array(), MPWEM_PLUGIN_VERSION );
				wp_enqueue_style( 'mp_font_awesome', '//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css', array(), '5.15.4' );
			}

			public function enqueue_modern_assets( $hook ) {
				if ( strpos( (string) $hook, 'mpwem_quick_setup' ) === false
					&& ! ( isset( $_GET['page'] ) && 'mpwem_quick_setup' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) ) {
					return;
				}
				$css = MPWEM_PLUGIN_DIR . '/assets/admin/css/mpwem-quick-setup.css';
				wp_enqueue_style(
					'mpwem-quick-setup',
					MPWEM_PLUGIN_URL . '/assets/admin/css/mpwem-quick-setup.css',
					array( 'dashicons' ),
					file_exists( $css ) ? (string) filemtime( $css ) : MPWEM_PLUGIN_VERSION
				);
			}

			public function quick_setup_menu() {
				$status = MPWEM_Global_Function::check_woocommerce();
				if ( $status == 1 ) {
					add_submenu_page( 'edit.php?post_type=mep_events', __( 'Quick Setup', 'mage-eventpress' ), '<span style="color:#10dd10">' . esc_html__( 'Quick Setup', 'mage-eventpress' ) . '</span>', 'manage_options', 'mpwem_quick_setup', array( $this, 'quick_setup' ) );
				} else {
					add_submenu_page( 'edit.php?post_type=mep_events', esc_html__( 'Quick Setup', 'mage-eventpress' ), '<span style="color:#10dd17">' . esc_html__( 'Quick Setup', 'mage-eventpress' ) . '</span>', 'manage_options', 'mpwem_quick_setup', array( $this, 'quick_setup' ) );
				}
			}

			private function reload_setup_script() {
				?>
				<script>
					(function ($) {
						"use strict";
						$(document).ready(function () {
							let mpwem_admin_location = window.location.href;
							mpwem_admin_location = mpwem_admin_location.replace('admin.php?post_type=mep_events&page=mpwem_quick_setup', 'edit.php?post_type=mep_events&page=mpwem_quick_setup');
							mpwem_admin_location = mpwem_admin_location.replace('admin.php?page=mep_events', 'edit.php?post_type=mep_events&page=mpwem_quick_setup');
							mpwem_admin_location = mpwem_admin_location.replace('admin.php?page=mpwem_quick_setup', 'edit.php?post_type=mep_events&page=mpwem_quick_setup');
							window.location.href = mpwem_admin_location;
						});
					}(jQuery));
				</script>
				<?php
			}

			public function quick_setup() {
				$status = MPWEM_Global_Function::check_woocommerce();
				if ( isset( $_POST['active_woo_btn'] ) ) {
					?>
					<script>mpwem_loader_body();</script>
					<?php
					activate_plugin( 'woocommerce/woocommerce.php' );
					$this->reload_setup_script();
				}
				if ( isset( $_POST['install_and_active_woo_btn'] ) ) {
					echo '<div style="display:none">';
					include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
					include_once( ABSPATH . 'wp-admin/includes/file.php' );
					include_once( ABSPATH . 'wp-admin/includes/misc.php' );
					include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
					$plugin = 'woocommerce';
					$api    = plugins_api( 'plugin_information', array(
						'slug'   => $plugin,
						'fields' => array(
							'short_description' => false,
							'sections'          => false,
							'requires'          => false,
							'rating'            => false,
							'ratings'           => false,
							'downloaded'        => false,
							'last_updated'      => false,
							'added'             => false,
							'tags'              => false,
							'compatibility'     => false,
							'homepage'          => false,
							'donate_link'       => false,
						),
					) );
					$title              = 'title';
					$url                = 'url';
					$nonce              = 'nonce';
					$woocommerce_plugin = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
					$woocommerce_plugin->install( $api->download_link );
					activate_plugin( 'woocommerce/woocommerce.php' );
					echo '</div>';
					$this->reload_setup_script();
				}
				if ( isset( $_POST['finish_quick_setup'] ) ) {
					$host                        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'example.com';
					$label                       = isset( $_POST['event_label'] ) ? sanitize_text_field( wp_unslash( $_POST['event_label'] ) ) : 'Events';
					$slug                        = isset( $_POST['event_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['event_slug'] ) ) : 'event';
					$event_expire_on             = isset( $_POST['event_expire_on'] ) ? sanitize_text_field( wp_unslash( $_POST['event_expire_on'] ) ) : 'event_expire_datetime';
					$email_from_name             = isset( $_POST['email_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['email_from_name'] ) ) : get_bloginfo( 'name' );
					$email_from_addrss           = isset( $_POST['email_from_address'] ) ? sanitize_text_field( wp_unslash( $_POST['email_from_address'] ) ) : 'no-reply@' . $host;
					$general_settings_data       = get_option( 'general_setting_sec' );
					$email_settings_data         = get_option( 'email_setting_sec' );
					$update_general_settings_arr = array(
						'mep_event_label'               => $label,
						'mep_event_slug'                => $slug,
						'mep_event_expire_on_datetimes' => $event_expire_on,
					);
					$update_email_settings_arr   = array(
						'mep_email_form_name'  => $email_from_name,
						'mep_email_form_email' => $email_from_addrss,
					);
					$new_general_settings_data   = is_array( $general_settings_data ) ? array_replace( $general_settings_data, $update_general_settings_arr ) : $update_general_settings_arr;
					$new_email_settings_data     = is_array( $email_settings_data ) ? array_replace( $email_settings_data, $update_email_settings_arr ) : $update_email_settings_arr;
					update_option( 'general_setting_sec', $new_general_settings_data );
					update_option( 'email_setting_sec', $new_email_settings_data );
					update_option( 'mep_quick_setup', 'done' );
					wp_redirect(
						add_query_arg(
							array(
								'post_type'        => 'mep_events',
								'page'             => 'mep_event_lists',
								'_mep_flush_nonce' => wp_create_nonce( 'mep_flush_rules_action' ),
							),
							admin_url( 'edit.php' )
						)
					);
					exit;
				}
				?>
				<div class="wrap mpwem-quick-setup-wrap">
					<div class="mpwem_style mep-quick-setup mpwem-qs-modern">
						<header class="mpwem-qs-hero">
							<div class="mpwem-qs-hero-copy">
								<span class="mpwem-qs-eyebrow">
									<span class="dashicons dashicons-admin-generic"></span>
									<?php esc_html_e( 'Getting started', 'mage-eventpress' ); ?>
								</span>
								<h1><?php esc_html_e( 'Quick Setup', 'mage-eventpress' ); ?></h1>
								<p><?php esc_html_e( 'Configure the essentials in a few steps so you can start selling event tickets with WooCommerce.', 'mage-eventpress' ); ?></p>
							</div>
							<div class="mpwem-qs-hero-meta">
								<span class="dashicons dashicons-tickets-alt"></span>
								<span><?php esc_html_e( '3-step wizard', 'mage-eventpress' ); ?></span>
							</div>
						</header>

						<div class="_shadow_6_admin_layout mpwem-qs-shell">
							<form method="post" action="">
								<div class="tabs_next">
									<div class="tabListsNext _margin_auto mpwem-qs-steps">
										<div data-tabs-target-next="#mpwem_qs_welcome" class="tabItemNext" data-open-text="1" data-close-text=" " data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
											<h4 class="_icon_circle" data-class>
												<span class="_mp_zero" data-icon></span>
												<span class="_mp_zero" data-text>1</span>
											</h4>
											<h6 class="circleTitle" data-class><?php esc_html_e( 'Welcome', 'mage-eventpress' ); ?></h6>
										</div>
										<div data-tabs-target-next="#mpwem_qs_general" class="tabItemNext" data-open-text="2" data-close-text="" data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
											<h4 class="_icon_circle" data-class>
												<span class="_mp_zero" data-icon></span>
												<span class="_mp_zero" data-text>2</span>
											</h4>
											<h6 class="circleTitle" data-class><?php esc_html_e( 'General', 'mage-eventpress' ); ?></h6>
										</div>
										<div data-tabs-target-next="#mpwem_qs_done" class="tabItemNext" data-open-text="3" data-close-text="" data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
											<h4 class="_icon_circle" data-class>
												<span class="_mp_zero" data-icon></span>
												<span class="_mp_zero" data-text>3</span>
											</h4>
											<h6 class="circleTitle" data-class><?php esc_html_e( 'Done', 'mage-eventpress' ); ?></h6>
										</div>
									</div>
									<div class="tabsContentNext _mt mpwem-qs-panels">
										<?php
										$this->setup_welcome_content();
										$this->setup_general_content();
										$this->setup_content_done();
										?>
									</div>
									<?php if ( $status == 1 ) { ?>
										<div class="justify_between mpwem-qs-nav">
											<button type="button" class="_button_general nextTab_prev mpwem-qs-btn mpwem-qs-btn-secondary">
												<span class="dashicons dashicons-arrow-left-alt2"></span>
												<span><?php esc_html_e( 'Previous', 'mage-eventpress' ); ?></span>
											</button>
											<div></div>
											<button type="button" class="_button_theme nextTab_next mpwem-qs-btn mpwem-qs-btn-primary">
												<span><?php esc_html_e( 'Next', 'mage-eventpress' ); ?></span>
												<span class="dashicons dashicons-arrow-right-alt2"></span>
											</button>
										</div>
									<?php } ?>
								</div>
							</form>
						</div>
					</div>
				</div>
				<?php
			}

			public function setup_welcome_content() {
				$status = MPWEM_Global_Function::check_woocommerce();
				?>
				<div data-tabs-next="#mpwem_qs_welcome" class="mpwem-qs-panel">
					<div class="mpwem-qs-panel-head">
						<span class="mpwem-qs-panel-icon"><span class="dashicons dashicons-smiley"></span></span>
						<div>
							<h2><?php esc_html_e( 'Welcome to Event Manager', 'mage-eventpress' ); ?></h2>
							<p><?php esc_html_e( 'Thanks for choosing Event Manager & Tickets for WooCommerce. Confirm WooCommerce is ready, then continue.', 'mage-eventpress' ); ?></p>
						</div>
					</div>

					<div class="mpwem-qs-woo-card <?php echo 1 === (int) $status ? 'is-ready' : 'is-pending'; ?>">
						<div class="mpwem-qs-woo-copy">
							<span class="mpwem-qs-woo-label"><?php esc_html_e( 'WooCommerce', 'mage-eventpress' ); ?></span>
							<strong>
								<?php
								if ( 1 === (int) $status ) {
									esc_html_e( 'Already installed and activated', 'mage-eventpress' );
								} elseif ( 0 === (int) $status ) {
									esc_html_e( 'Needs to be installed and activated', 'mage-eventpress' );
								} else {
									esc_html_e( 'Installed — please activate it', 'mage-eventpress' );
								}
								?>
							</strong>
						</div>
						<div class="mpwem-qs-woo-action">
							<?php if ( 1 === (int) $status ) { ?>
								<span class="mpwem-qs-status-pill">
									<span class="dashicons dashicons-yes-alt"></span>
									<?php esc_html_e( 'Ready', 'mage-eventpress' ); ?>
								</span>
							<?php } elseif ( 0 === (int) $status ) { ?>
								<button class="_button_warning mpwem-qs-btn mpwem-qs-btn-warning" type="submit" name="install_and_active_woo_btn">
									<?php esc_html_e( 'Install & Activate', 'mage-eventpress' ); ?>
								</button>
							<?php } else { ?>
								<button class="_button_theme mpwem-qs-btn mpwem-qs-btn-primary" type="submit" name="active_woo_btn">
									<?php esc_html_e( 'Activate Now', 'mage-eventpress' ); ?>
								</button>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
			}

			public function setup_general_content() {
				$host               = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'example.com';
				$label              = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$slug               = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_slug', 'event' );
				$expire             = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_expire_on_datetimes', 'event_expire_datetime' );
				$from_email         = MPWEM_Global_Function::get_settings( 'email_setting_sec', 'mep_email_form_name', get_bloginfo( 'name' ) );
				$from_email_address = MPWEM_Global_Function::get_settings( 'email_setting_sec', 'mep_email_form_email', 'no-reply@' . $host );
				?>
				<div data-tabs-next="#mpwem_qs_general" class="mpwem-qs-panel">
					<div class="mpwem-qs-panel-head">
						<span class="mpwem-qs-panel-icon"><span class="dashicons dashicons-admin-settings"></span></span>
						<div>
							<h2><?php esc_html_e( 'General settings', 'mage-eventpress' ); ?></h2>
							<p><?php esc_html_e( 'Set labels, permalinks, expiry behavior, and default outgoing email details.', 'mage-eventpress' ); ?></p>
						</div>
					</div>

					<div class="mpwem-qs-fields section">
						<div class="mpwem-qs-field">
							<label class="_fullWidth" for="mpwem_qs_event_label">
								<span class="mpwem-qs-field-title"><?php esc_html_e( 'Events Label', 'mage-eventpress' ); ?></span>
								<input type="text" id="mpwem_qs_event_label" class="formControl" name="event_label" value="<?php echo esc_attr( $label ); ?>"/>
							</label>
							<p class="info_text">
								<span class="dashicons dashicons-info-outline"></span>
								<?php esc_html_e( 'It will change the Events post type label across the plugin.', 'mage-eventpress' ); ?>
							</p>
						</div>

						<div class="mpwem-qs-field">
							<label class="_fullWidth" for="mpwem_qs_event_slug">
								<span class="mpwem-qs-field-title"><?php esc_html_e( 'Events Slug', 'mage-eventpress' ); ?></span>
								<input type="text" id="mpwem_qs_event_slug" class="formControl" name="event_slug" value="<?php echo esc_attr( $slug ); ?>"/>
							</label>
							<p class="info_text">
								<span class="dashicons dashicons-info-outline"></span>
								<?php esc_html_e( 'After changing the slug, go to Settings → Permalinks and click Save.', 'mage-eventpress' ); ?>
							</p>
						</div>

						<div class="mpwem-qs-field">
							<label class="_fullWidth" for="mpwem_qs_event_expire">
								<span class="mpwem-qs-field-title"><?php esc_html_e( 'When will the event expire', 'mage-eventpress' ); ?></span>
								<select id="mpwem_qs_event_expire" class="formControl" name="event_expire_on">
									<option value="event_start_datetime" <?php selected( $expire, 'event_start_datetime' ); ?>><?php esc_html_e( 'Event Start Time', 'mage-eventpress' ); ?></option>
									<option value="event_expire_datetime" <?php selected( $expire, 'event_expire_datetime' ); ?>><?php esc_html_e( 'Event End Time', 'mage-eventpress' ); ?></option>
								</select>
							</label>
							<p class="info_text">
								<span class="dashicons dashicons-info-outline"></span>
								<?php esc_html_e( 'Choose when events are treated as expired on the front end.', 'mage-eventpress' ); ?>
							</p>
						</div>

						<div class="mpwem-qs-field">
							<label class="_fullWidth" for="mpwem_qs_email_name">
								<span class="mpwem-qs-field-title"><?php esc_html_e( 'Email From Name', 'mage-eventpress' ); ?></span>
								<input type="text" id="mpwem_qs_email_name" class="formControl" name="email_from_name" value="<?php echo esc_attr( $from_email ); ?>"/>
							</label>
							<p class="info_text">
								<span class="dashicons dashicons-info-outline"></span>
								<?php esc_html_e( 'Name shown on outgoing event emails.', 'mage-eventpress' ); ?>
							</p>
						</div>

						<div class="mpwem-qs-field">
							<label class="_fullWidth" for="mpwem_qs_email_address">
								<span class="mpwem-qs-field-title"><?php esc_html_e( 'From Email Address', 'mage-eventpress' ); ?></span>
								<input type="email" id="mpwem_qs_email_address" class="formControl" name="email_from_address" value="<?php echo esc_attr( $from_email_address ); ?>"/>
							</label>
							<p class="info_text">
								<span class="dashicons dashicons-info-outline"></span>
								<?php esc_html_e( 'Address used as the From email for notifications.', 'mage-eventpress' ); ?>
							</p>
						</div>
					</div>
				</div>
				<?php
			}

			public function setup_content_done() {
				?>
				<div data-tabs-next="#mpwem_qs_done" class="mpwem-qs-panel">
					<div class="mpwem-qs-done-card">
						<span class="mpwem-qs-done-icon"><span class="dashicons dashicons-flag"></span></span>
						<h2><?php esc_html_e( 'Finalize Setup', 'mage-eventpress' ); ?></h2>
						<p><?php esc_html_e( 'You are about to finish and save your Event Booking Manager for WooCommerce setup.', 'mage-eventpress' ); ?></p>
						<button type="submit" name="finish_quick_setup" class="_button_theme mpwem-qs-btn mpwem-qs-btn-primary mpwem-qs-btn-lg">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Finish & Save', 'mage-eventpress' ); ?>
						</button>
					</div>
				</div>
				<?php
			}
		}
		new MPWEM_Quick_Setup();
	}
