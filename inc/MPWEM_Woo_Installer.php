<?php
/**
 * MPWEM WooCommerce Installer
 * Handles WooCommerce dependency check, beautiful popup display,
 * and AJAX-based installation & activation.
 * The popup shows on EVERY admin page when WooCommerce is not active.
 *
 * @package MageEventPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'MPWEM_Woo_Installer' ) ) {

	class MPWEM_Woo_Installer {

		/**
		 * Constructor – hooks into WordPress.
		 */
		public function __construct() {
			// On admin_init, check if our plugin was just activated (for redirect)
			add_action( 'admin_init', array( $this, 'handle_activation_redirect' ) );
			// Enqueue popup assets on all admin pages (only outputs if WooCommerce is missing)
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			// Render the popup markup in admin footer
			add_action( 'admin_footer', array( $this, 'render_popup' ) );
			// AJAX handlers for install, activate & dismiss
			add_action( 'wp_ajax_mpwem_install_woocommerce', array( $this, 'ajax_install_woocommerce' ) );
			add_action( 'wp_ajax_mpwem_activate_woocommerce', array( $this, 'ajax_activate_woocommerce' ) );
		}

		/**
		 * Check if WooCommerce plugin file exists (installed but maybe not active).
		 *
		 * @return bool
		 */
		private function is_woo_installed() {
			$plugin_file = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
			return file_exists( $plugin_file );
		}

		/**
		 * Check if WooCommerce is active.
		 *
		 * @return bool
		 */
		private function is_woo_active() {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			return is_plugin_active( 'woocommerce/woocommerce.php' );
		}

		/**
		 * Runs on admin_init. If the transient from activation exists
		 * and WooCommerce IS active, redirect to event lists page.
		 */
		public function handle_activation_redirect() {
			if ( ! get_transient( 'mpwem_plugin_activated' ) ) {
				return;
			}

			// Don't redirect on multi-site bulk activations
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				delete_transient( 'mpwem_plugin_activated' );
				return;
			}

			// WooCommerce is active → redirect immediately
			if ( $this->is_woo_active() ) {
				delete_transient( 'mpwem_plugin_activated' );
				wp_safe_redirect( admin_url( 'edit.php?post_type=mep_events&page=mep_event_lists' ) );
				exit;
			}

			// WooCommerce is NOT active → clear transient, popup will show via should_show_popup()
			delete_transient( 'mpwem_plugin_activated' );
		}

		/**
		 * Should we show the popup on this page load?
		 * Always show when WooCommerce is not active and our plugin is active.
		 *
		 * @return bool
		 */
		private function should_show_popup() {
			// Always show the popup when WooCommerce is not active
			return ! $this->is_woo_active();
		}

		/**
		 * Enqueue CSS & JS for the popup only when needed.
		 */
		public function enqueue_assets() {
			if ( ! $this->should_show_popup() ) {
				return;
			}

			wp_enqueue_style(
				'mpwem-woo-installer',
				MPWEM_PLUGIN_URL . '/assets/admin/mpwem_woo_installer.css',
				array(),
				filemtime( MPWEM_PLUGIN_DIR . '/assets/admin/mpwem_woo_installer.css' )
			);

			wp_enqueue_script(
				'mpwem-woo-installer',
				MPWEM_PLUGIN_URL . '/assets/admin/mpwem_woo_installer.js',
				array( 'jquery' ),
				filemtime( MPWEM_PLUGIN_DIR . '/assets/admin/mpwem_woo_installer.js' ),
				true
			);

			wp_localize_script( 'mpwem-woo-installer', 'mpwem_woo_installer', array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'install_nonce'    => wp_create_nonce( 'mpwem_install_woo' ),
				'activate_nonce'   => wp_create_nonce( 'mpwem_activate_woo' ),
				'redirect_url'     => admin_url( 'edit.php?post_type=mep_events&page=mep_event_lists' ),
				'woo_installed'    => $this->is_woo_installed() ? 'yes' : 'no',
				'i18n'             => array(
					'installing'     => __( 'Installing WooCommerce...', 'mage-eventpress' ),
					'activating'     => __( 'Activating WooCommerce...', 'mage-eventpress' ),
					'success'        => __( 'WooCommerce activated successfully!', 'mage-eventpress' ),
					'redirecting'    => __( 'Redirecting...', 'mage-eventpress' ),
					'error'          => __( 'Something went wrong. Please try again.', 'mage-eventpress' ),
					'install_error'  => __( 'Installation failed. Please install WooCommerce manually.', 'mage-eventpress' ),
					'activate_error' => __( 'Activation failed. Please activate WooCommerce manually.', 'mage-eventpress' ),
				),
			) );
		}

		/**
		 * Render the popup HTML in admin footer.
		 */
		public function render_popup() {
			if ( ! $this->should_show_popup() ) {
				return;
			}

			$is_installed = $this->is_woo_installed();
			$btn_text     = $is_installed
				? __( 'Activate WooCommerce', 'mage-eventpress' )
				: __( 'Install & Activate WooCommerce', 'mage-eventpress' );
			?>
			<!-- MPWEM WooCommerce Installer Popup Overlay -->
			<div id="mpwem-woo-overlay" class="mpwem-woo-overlay">
				<div class="mpwem-woo-popup">

					<!-- Header strip -->
					<div class="mpwem-woo-header">
						<div class="mpwem-woo-header-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
								<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<span class="mpwem-woo-header-text"><?php esc_html_e( 'Event Booking Manager', 'mage-eventpress' ); ?></span>
					</div>

					<!-- Icon -->
					<div class="mpwem-woo-icon-wrapper">
						<div class="mpwem-woo-icon">
							<svg width="40" height="40" viewBox="0 0 24 24" fill="none">
								<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
								<path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</div>
					</div>

					<!-- Content -->
					<div class="mpwem-woo-content">
						<h2 class="mpwem-woo-title"><?php esc_html_e( 'WooCommerce Required', 'mage-eventpress' ); ?></h2>
						<p class="mpwem-woo-desc">
							<?php esc_html_e( 'Event Booking Manager requires WooCommerce to manage event registrations, tickets, and payments. Please install and activate WooCommerce to continue using this plugin.', 'mage-eventpress' ); ?>
						</p>
					</div>

					<!-- Feature highlights -->
					<div class="mpwem-woo-features">
						<div class="mpwem-woo-feature">
							<span class="mpwem-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Ticket selling & payments', 'mage-eventpress' ); ?></span>
						</div>
						<div class="mpwem-woo-feature">
							<span class="mpwem-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Order management', 'mage-eventpress' ); ?></span>
						</div>
						<div class="mpwem-woo-feature">
							<span class="mpwem-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Attendee registration', 'mage-eventpress' ); ?></span>
						</div>
					</div>

					<!-- Progress area (hidden by default) -->
					<div id="mpwem-woo-progress" class="mpwem-woo-progress" style="display:none;">
						<div class="mpwem-woo-progress-bar">
							<div id="mpwem-woo-progress-fill" class="mpwem-woo-progress-fill"></div>
						</div>
						<p id="mpwem-woo-status-text" class="mpwem-woo-status-text"></p>
					</div>

					<!-- Action buttons -->
					<div class="mpwem-woo-actions">
						<button type="button" id="mpwem-woo-install-btn" class="mpwem-woo-btn mpwem-woo-btn-primary">
							<span class="mpwem-woo-btn-icon">
								<svg width="18" height="18" viewBox="0 0 20 20" fill="none">
									<path d="M10 3v10m0 0l-4-4m4 4l4-4M3 17h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
							<span class="mpwem-woo-btn-text"><?php echo esc_html( $btn_text ); ?></span>
						</button>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>" class="mpwem-woo-btn mpwem-woo-btn-secondary">
							<?php esc_html_e( 'Install Manually', 'mage-eventpress' ); ?>
						</a>
					</div>

					<!-- Footer note -->
					<p class="mpwem-woo-footer-note">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align: -2px; flex-shrink: 0;">
							<path d="M7 1a6 6 0 100 12A6 6 0 007 1zm0 8.5a.75.75 0 110-1.5.75.75 0 010 1.5zM7.75 6.25a.75.75 0 01-1.5 0V4a.75.75 0 011.5 0v2.25z" fill="currentColor"/>
						</svg>
						<?php esc_html_e( 'WooCommerce is free, open-source, and trusted by millions of stores worldwide.', 'mage-eventpress' ); ?>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * AJAX: Install WooCommerce from WordPress.org repository.
		 */
		public function ajax_install_woocommerce() {
			check_ajax_referer( 'mpwem_install_woo', 'nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'mage-eventpress' ) ) );
			}

			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/misc.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$api = plugins_api( 'plugin_information', array(
				'slug'   => 'woocommerce',
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

			if ( is_wp_error( $api ) ) {
				wp_send_json_error( array( 'message' => $api->get_error_message() ) );
			}

			$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$result   = $upgrader->install( $api->download_link );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

			if ( $result === false ) {
				wp_send_json_error( array( 'message' => __( 'Installation failed.', 'mage-eventpress' ) ) );
			}

			wp_send_json_success( array( 'message' => __( 'WooCommerce installed successfully.', 'mage-eventpress' ) ) );
		}

		/**
		 * AJAX: Activate WooCommerce plugin.
		 */
		public function ajax_activate_woocommerce() {
			check_ajax_referer( 'mpwem_activate_woo', 'nonce' );

			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to activate plugins.', 'mage-eventpress' ) ) );
			}

			$result = activate_plugin( 'woocommerce/woocommerce.php' );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

			wp_send_json_success( array( 'message' => __( 'WooCommerce activated successfully!', 'mage-eventpress' ) ) );
		}
	}

	new MPWEM_Woo_Installer();
}
