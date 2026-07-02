<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
// The Status screen now lives inside Event Settings as a "Status" tab. Register the tab
// (it has no settings fields; its content is printed via the wsa_form_bottom hook below,
// mirroring the License tab).
	add_filter('mep_settings_sec_reg', 'mep_event_status_settings_section');
	function mep_event_status_settings_section($sections) {
		$sections[] = array(
			'id'    => 'mep_status_setting_sec',
			'title' => '<i class="mi mi-info"></i>' . __('Status', 'mage-eventpress'),
		);
		return $sections;
	}
	add_action('wsa_form_bottom_mep_status_setting_sec', 'mep_event_status_page');

// Keep the legacy Status page registered but hidden from the admin menu. It is no longer
// a visible submenu item (its content moved to the Event Settings → Status tab), but the
// page must stay reachable so existing deep links keep working — e.g. the PRO PDF-support
// "Install Now / Active Now" buttons that point to page=mep_event_status_page. Registering
// then removing the submenu keeps it in $_registered_pages (accessible) while hiding the
// menu link.
	add_action('admin_menu', 'mep_event_status_admin_menu', 100);
	function mep_event_status_admin_menu() {
		add_submenu_page('edit.php?post_type=mep_events', __('Status', 'mage-eventpress'), __('Status', 'mage-eventpress'), 'manage_options', 'mep_event_status_page', 'mep_event_status_page');
		remove_submenu_page('edit.php?post_type=mep_events', 'mep_event_status_page');
	}
	function mep_event_status_page() {
		$wp_v = get_bloginfo('version');
		$wc_v = ( function_exists('WC') && WC() ) ? WC()->version : '';
		$wc_i = mep_woo_install_check();
		$from_name = mep_get_option('mep_email_form_name', 'email_setting_sec', '');
		$from_email = mep_get_option('mep_email_form_email', 'email_setting_sec', '');
		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		
		<?php do_action('mep_event_status_notice_sec'); ?>
		<div class="wrap">
			<div class="wc_status_table_wrapper">
				<table class="wc_status_table widefat" cellspacing="0" id="status">
					<thead>
					<tr>
						<th colspan="3" data-export-label="WordPress Environment">
							<h2>Event Manager For Woocommerce Environment Status</h2>
						</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td data-export-label="WC Version">WordPress Version:</td>
						<td class="help">
							<span class="woocommerce-help-tip"></span>
						</td>
						<td><?php if ($wp_v > 5.5) {
								echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>' . esc_html($wp_v) . '</span>';
							} else {
								echo '<span class="mep_warning"> <span class="dashicons dashicons-saved"></span>' . esc_html($wp_v) . '</span>';
							} ?></td>
					</tr>
					<tr>
						<td data-export-label="WC Version">Woocommerce Installed:</td>
						<td class="help">
							<span class="woocommerce-help-tip"></span>
						</td>
						<td><?php if ($wc_i == 'Yes') {
								echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>' . esc_html($wc_i) . '</span>';
							} else {
								echo '<span class="mep_error"> <span class="dashicons dashicons-no-alt"></span>' . esc_html($wc_i) . '</span>';
							} ?></td>
					</tr>
					<?php if (mep_woo_install_check() == 'Yes') { ?>
						<tr>
							<td data-export-label="WC Version">Woocommerce Version:</td>
							<td class="help">
								<span class="woocommerce-help-tip"></span>
							</td>
							<td><?php if ( version_compare( $wc_v, '4.8', '>' ) ) {
										echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>' . esc_html($wc_v) . '</span>';
									} else {
										echo '<span class="mep_warning"> <span class="dashicons dashicons-no-alt"></span>' . esc_html($wc_v) . '</span>';
									} ?>
							</td>
						</tr>
						<tr>
							<td data-export-label="WC Version">Email From Name:</td>
							<td class="help">
								<span class="woocommerce-help-tip"></span>
							</td>
							<td><?php if ($from_name) {
									echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>' . esc_html($from_name) . '</span>';
								} else {
									echo '<span class="mep_error"> <span class="dashicons dashicons-no-alt"></span></span>';
								} ?></td>
						</tr>
						<tr>
							<td data-export-label="WC Version">From Email Address:</td>
							<td class="help">
								<span class="woocommerce-help-tip"></span>
							</td>
							<td><?php if ($from_email) {
									echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>' . esc_html($from_email) . '</span>';
								} else {
									echo '<span class="mep_error"> <span class="dashicons dashicons-no-alt"></span></span>';
								} ?></td>
						</tr>

						<tr>
						<td data-export-label="WC Version">Event on Cart (Temporary Booked):</td>
						<td class="help">
							<span class="woocommerce-help-tip"></span> 
						</td>
						<td>	
						<div id="empty-cart-message"></div>
						<span class="mep_success"> <?php echo mep_event_cart_temp_count(); ?></span>
						<?php if(mep_event_cart_temp_count() > 0){ ?>
								<?php wp_nonce_field( 'delete-event-temp-cart'); ?>
								<input type="hidden" name='empty_event_cart_temp' value='yes'/>
								<button id="empty-cart-btn" class="button button-primary"><?php _e('Empty Cart','mage-eventpress'); ?></button>								
							<?php
						} ?>
						</td>
					</tr>



					<?php }
						do_action('mep_event_status_table_item_sec'); ?>
					</tbody>
				</table>
			</div>
		</div>
		<script>


		</script>
		<?php
	}

/**
 * AJAX Callback Function to Empty WooCommerce Cart
 */
/**
 * Secure AJAX handler to empty WooCommerce cart
 */
add_action( 'wp_ajax_empty_woocommerce_cart', 'mep_empty_woocommerce_cart_ajax' );
function mep_empty_woocommerce_cart_ajax() {

	// WooCommerce active check
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( 'WooCommerce is not active.', 400 );
	}

	// Capability check (Admin + Shop Manager)
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_send_json_error( 'Unauthorized request.', 403 );
	}

	// CSRF protection
	check_ajax_referer( 'mep_admin_nonce', 'nonce' );

	// Empty WooCommerce cart
	if ( WC()->cart ) {
		WC()->cart->empty_cart();
	}

	// Clear temporary event holds
	if ( function_exists( 'mep_temp_event_cart_empty' ) ) {
		mep_temp_event_cart_empty();
	}

	wp_send_json_success( 'Cart emptied successfully.' );
}
