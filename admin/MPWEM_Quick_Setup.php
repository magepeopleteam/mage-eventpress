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
				add_action( 'admin_menu', array( $this, 'quick_setup_menu' ) );
			}
			public function add_admin_scripts() {
				wp_enqueue_style( 'mpwem_global', MPWEM_PLUGIN_URL . '/assets/helper/mp_style/mpwem_global.css', array(), time() );
				wp_enqueue_script( 'mpwem_global', MPWEM_PLUGIN_URL . '/assets/helper/mp_style/mpwem_global.js', array( 'jquery' ), time(), true );
				wp_enqueue_style( 'mpwem_admin', MPWEM_PLUGIN_URL . '/assets/admin/mpwem_admin.css', array(), time() );
				wp_enqueue_style( 'mp_font_awesome', '//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css', array(), '5.15.4' );
			}
			public function quick_setup_menu() {
				$status = MPWEM_Global_Function::check_woocommerce();
				if ( $status == 1 ) {
					add_submenu_page( 'edit.php?post_type=mep_events', __( 'Quick Setup', 'mage-eventpress' ), '<span style="color:#10dd10">' . esc_html__( 'Quick Setup', 'mage-eventpress' ) . '</span>', 'manage_options', 'mpwem_quick_setup', array( $this, 'quick_setup' ) );
					add_submenu_page( 'mep_events', esc_html__( 'Quick Setup', 'mage-eventpress' ), '<span style="color:#10dd10">' . esc_html__( 'Quick Setup', 'mage-eventpress' ) . '</span>', 'manage_options', 'mpwem_quick_setup', array( $this, 'quick_setup' ) );
				} else {
					add_menu_page( esc_html__( 'Events', 'mage-eventpress' ), esc_html__( 'Events', 'mage-eventpress' ), 'manage_options', 'mep_events', array( $this, 'quick_setup' ), 'dashicons-calendar-alt', 6 );
					add_submenu_page( 'mep_events', esc_html__( 'Quick Setup', 'mage-eventpress' ), '<span style="color:#10dd17">' . esc_html__( 'Quick Setup', 'mage-eventpress' ) . '</span>', 'manage_options', 'mpwem_quick_setup', array( $this, 'quick_setup' ) );
				}
			}
			public function quick_setup() {
				$status = MPWEM_Global_Function::check_woocommerce();
				if ( isset( $_POST['active_woo_btn'] ) ) {
					?>
                    <script>
                        mpwem_loader_body();
                    </script>
					<?php
					activate_plugin( 'woocommerce/woocommerce.php' );
					//MPTBM_Plugin::on_activation_page_create();
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
				if ( isset( $_POST['install_and_active_woo_btn'] ) ) {
					echo '<div style="display:none">';
					include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
					include_once( ABSPATH . 'wp-admin/includes/file.php' );
					include_once( ABSPATH . 'wp-admin/includes/misc.php' );
					include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
					$plugin             = 'woocommerce';
					$api                = plugins_api( 'plugin_information', array(
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
					//MPTBM_Plugin::on_activation_page_create();
					echo '</div>';
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
				if ( isset( $_POST['finish_quick_setup'] ) ) {
					$label                       = isset( $_POST['event_label'] ) ? sanitize_text_field( $_POST['event_label'] ) : 'Events';
					$slug                        = isset( $_POST['event_slug'] ) ? sanitize_text_field( $_POST['event_slug'] ) : 'event';
					$event_expire_on             = isset( $_POST['event_expire_on'] ) ? sanitize_text_field( $_POST['event_expire_on'] ) : 'event_expire_datetime';
					$email_from_name             = isset( $_POST['email_from_name'] ) ? sanitize_text_field( $_POST['email_from_name'] ) : get_bloginfo( 'name' );
					$email_from_addrss           = isset( $_POST['email_from_address'] ) ? sanitize_text_field( $_POST['email_from_address'] ) : "no-reply@$url";
					$general_settings_data       = get_option( 'general_setting_sec' );
					$email_settings_data         = get_option( 'email_setting_sec' );
					$update_general_settings_arr = [
						'mep_event_label'               => $label,
						'mep_event_slug'                => $slug,
						'mep_event_expire_on_datetimes' => $event_expire_on
					];
					$update_email_settings_arr   = [
						'mep_email_form_name'  => $email_from_name,
						'mep_email_form_email' => $email_from_addrss
					];
					$new_general_settings_data   = is_array( $general_settings_data ) ? array_replace( $general_settings_data, $update_general_settings_arr ) : $update_general_settings_arr;
					$new_email_settings_data     = is_array( $email_settings_data ) ? array_replace( $email_settings_data, $update_email_settings_arr ) : $update_email_settings_arr;
					update_option( 'general_setting_sec', $new_general_settings_data );
					update_option( 'email_setting_sec', $new_email_settings_data );
					update_option( 'mep_quick_setup', 'done' );
					wp_redirect(
						add_query_arg(
							[
								'post_type'        => 'mep_events',
								'page'             => 'mep_event_lists',
								'_mep_flush_nonce' => wp_create_nonce( 'mep_flush_rules_action' ),
							],
							admin_url( 'edit.php' )
						)
					);
					exit;
				}
				?>
                <div class="mpwem_style mep-quick-setup">
                    <div class="_shadow_6_admin_layout">
                        <form method="post" action="">
                            <div class="tabs_next">
                                <div class="tabListsNext _margin_auto">
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
                                <div class="tabsContentNext _mt">
									<?php
										$this->setup_welcome_content();
										$this->setup_general_content();
										$this->setup_content_done();
									?>
                                </div>
								<?php if ( $status == 1 ) { ?>
                                    <div class="justify_between">
                                        <button type="button" class="_button_general nextTab_prev">
                                            <span>&longleftarrow;<?php esc_html_e( 'Previous', 'mage-eventpress' ); ?></span>
                                        </button>
                                        <div></div>
                                        <button type="button" class="_button_theme nextTab_next">
                                            <span><?php esc_html_e( 'Next', 'mage-eventpress' ); ?>&longrightarrow;</span>
                                        </button>
                                    </div>
								<?php } ?>
                            </div>
                        </form>
                    </div>
                </div>
				<?php
			}
			public function setup_welcome_content() {
				$status = MPWEM_Global_Function::check_woocommerce();
				?>
                <div data-tabs-next="#mpwem_qs_welcome">
                    <h2><?php esc_html_e( 'Event Manager and Tickets Selling Plugin', 'mage-eventpress' ); ?></h2>
                    <p class="_mtb_xs"><?php esc_html_e( 'Thanks for choosing the Event Manager & Tickets Plugin for WooCommerce! Follow the steps below to get started.
', 'mage-eventpress' ); ?></p>
                    <div class="_layout_default_mt_align_center justify_between">
                        <h5>
							<?php if ( $status == 1 ) {
								esc_html_e( 'Woocommerce already installed and activated', 'mage-eventpress' );
							} elseif ( $status == 0 ) {
								esc_html_e( 'Woocommerce need to install and active', 'mage-eventpress' );
							} else {
								esc_html_e( 'Woocommerce already install , please activate it', 'mage-eventpress' );
							} ?>
                        </h5>
						<?php if ( $status == 1 ) { ?>
                            <h5>
                                <span class="fas fa-check-circle _text_success"></span>
                            </h5>
						<?php } elseif ( $status == 0 ) { ?>
                            <button class="_button_warning" type="submit" name="install_and_active_woo_btn"><?php esc_html_e( 'Install & Active Now', 'mage-eventpress' ); ?></button>
						<?php } else { ?>
                            <button class="_button_theme" type="submit" name="active_woo_btn"><?php esc_html_e( 'Active Now', 'mage-eventpress' ); ?></button>
						<?php } ?>
                    </div>
                </div>
				<?php
			}
			public function setup_general_content() {
				$url                = ( isset( $_SERVER['HTTPS'] ) ? "" : "" ) . "$_SERVER[HTTP_HOST]";
				$label              = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$slug               = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_slug', 'event' );
				$expire             = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_expire_on_datetimes', 'event_expire_datetime' );
				$from_email         = MPWEM_Global_Function::get_settings( 'email_setting_sec', 'mep_email_form_name', get_bloginfo( 'name' ) );
				$from_email_address = MPWEM_Global_Function::get_settings( 'email_setting_sec', 'mep_email_form_email', "no-reply@$url" );
				?>
                <div data-tabs-next="#mpwem_qs_general">
                    <div class="section">
                        <h2><?php esc_html_e( 'General settings', 'mage-eventpress' ); ?></h2>
                        <p class="_mtb_xs"><?php esc_html_e( 'Choose some general option.', 'mage-eventpress' ); ?></p>
                        <div class="_mt">
                            <label class="_fullWidth">
                                <span class="min_200"><?php esc_html_e( 'Events Label:', 'mage-eventpress' ); ?></span>
                                <input type="text" class="formControl" name="event_label" value='<?php echo esc_attr( $label ); ?>'/>
                            </label>
                            <i class="info_text">
                                <span class="fas fa-info-circle"></span>
								<?php esc_html_e( 'It will change the Events post type label on the entire plugin.', 'mage-eventpress' ); ?>
                            </i>
                            <label class="_fullWidth">
                                <span class="min_200"><?php esc_html_e( 'Events Slug:', 'mage-eventpress' ); ?></span>
                                <input type="text" class="formControl" name="event_slug" value='<?php echo esc_attr( $slug ); ?>'/>
                            </label>
                            <i class="info_text">
                                <span class="fas fa-info-circle"></span>
								<?php esc_html_e( 'Changing this will update the Events slug across the plugin. After that, go to Settings → Permalinks and click Save to refresh links.', 'mage-eventpress' ); ?>
                            </i>
                            <label class="_fullWidth">
                                <span class="min_200"><?php esc_html_e( 'When will the event expire', 'mage-eventpress' ); ?></span>
                                <select class="formControl" name="event_expire_on">
                                    <option value="event_start_datetime" <?php echo esc_attr( $expire == 'event_start_datetime' ? 'selected' : '' ); ?>><?php esc_html_e( 'Event Start Time', 'mage-eventpress' ); ?></option>
                                    <option value="event_expire_datetime" <?php echo esc_attr( $expire == 'event_expire_datetime' ? 'selected' : '' ); ?>><?php esc_html_e( 'Event End Time', 'mage-eventpress' ); ?></option>
                                </select>
                            </label>
                            <i class="info_text">
                                <span class="fas fa-info-circle"></span>
								<?php esc_html_e( 'Please select when the event will expire', 'mage-eventpress' ); ?>
                            </i>
                            <label class="_fullWidth">
                                <span class="min_200"><?php esc_html_e( 'Email From Name:', 'mage-eventpress' ); ?></span>
                                <input type="text" class="formControl" name="email_from_name" value='<?php echo esc_attr( $from_email ); ?>'/>
                            </label>
                            <i class="info_text">
                                <span class="fas fa-info-circle"></span>
								<?php esc_html_e( 'Please enter the email from name', 'mage-eventpress' ); ?>
                            </i>
                            <label class="_fullWidth">
                                <span class="min_200"><?php esc_html_e( 'From Email Address:', 'mage-eventpress' ); ?></span>
                                <input type="text" class="formControl" name="email_from_address" value='<?php echo esc_attr( $from_email_address ); ?>'/>
                            </label>
                            <i class="info_text">
                                <span class="fas fa-info-circle"></span>
								<?php esc_html_e( 'Please enter the email from name', 'mage-eventpress' ); ?>
                            </i>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function setup_content_done() {
				?>
                <div data-tabs-next="#mpwem_qs_done">
                    <h2><?php esc_html_e( 'Finalize Setup', 'mage-eventpress' ); ?></h2>
                    <p class="_mtb_xs"><?php esc_html_e( 'You are about to Finish & Save Events Booking Manager For Woocommerce Plugin setup process', 'mage-eventpress' ); ?></p>
                    <div class="_mt allCenter">
                        <button type="submit" name="finish_quick_setup" class="_button_theme"><?php esc_html_e( 'Finish & Save', 'mage-eventpress' ); ?></button>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Quick_Setup();
	}