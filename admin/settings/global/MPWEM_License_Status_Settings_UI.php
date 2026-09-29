<?php
	/**
	 * License & Status Settings — mockup-matched modern hub.
	 * Section ids stay mep_settings_licensing and mep_status_setting_sec.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_License_Status_Settings_UI' ) ) {
		class MPWEM_License_Status_Settings_UI {

			/**
			 * Render License & Status page.
			 */
			public static function render_hub() {
				$has_pro = function_exists( 'mep_check_plugin_installed' )
					? mep_check_plugin_installed( 'mage-eventpress-pro/woocommerce-event-manager-pro.php' )
					: false;
				?>
				<div class="mep-ls">
					<div class="mep-ls__header">
						<div class="mep-ls__header-text">
							<h2 class="mep-ls__title"><?php esc_html_e( 'License and Status', 'mage-eventpress' ); ?></h2>
							<p class="mep-ls__subtitle"><?php esc_html_e( 'Manage your event manager licenses and review system health.', 'mage-eventpress' ); ?></p>
						</div>
					</div>

					<div class="mep-ls__page">
						<?php self::render_license_card( $has_pro ); ?>
						<?php self::render_status_card(); ?>
					</div>
				</div>
				<?php
			}

			/**
			 * @param bool $has_pro Whether Pro is installed.
			 */
			private static function render_license_card( $has_pro ) {
				?>
				<div class="mep-ls__card mep-ls__card--license">
					<div class="mep-ls__card-head mep-ls__card-head--row">
						<div class="mep-ls__card-head-left">
							<span class="mep-ls__card-icon"><i class="fas fa-key"></i></span>
							<h3 class="mep-ls__card-title"><?php esc_html_e( 'License Management', 'mage-eventpress' ); ?></h3>
						</div>
						<?php if ( $has_pro ) : ?>
							<span class="mep-ls__pro-badge"><span class="mep-ls__pro-dot"></span><?php esc_html_e( 'Pro Version', 'mage-eventpress' ); ?></span>
						<?php endif; ?>
					</div>
					<div class="mep-ls__card-body">
						<div class="mep_licensae_info"></div>
						<div class="mep-ls__table-wrap">
							<table class="mep-ls__license-table mep-licensing-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Plugin Name', 'mage-eventpress' ); ?></th>
										<th><?php esc_html_e( 'Order No', 'mage-eventpress' ); ?></th>
										<th><?php esc_html_e( 'Expire on', 'mage-eventpress' ); ?></th>
										<th><?php esc_html_e( 'License Key', 'mage-eventpress' ); ?></th>
										<th><?php esc_html_e( 'Status', 'mage-eventpress' ); ?></th>
										<th><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									ob_start();
									do_action( 'mep_license_page_addon_list' );
									$license_rows = trim( ob_get_clean() );
									echo $license_rows; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — addon HTML
									?>
								</tbody>
							</table>
						</div>
						<?php if ( '' === $license_rows ) : ?>
							<p class="mep-ls__empty"><?php esc_html_e( 'No Pro add-ons detected. Install Event Manager Pro to manage license keys here.', 'mage-eventpress' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}

			/**
			 * Environment status grid (mockup layout).
			 */
			private static function render_status_card() {
				$env_items   = self::get_env_status_items();
				$extra_items = self::get_additional_status_items();
				?>
				<div class="mep-ls__card mep-ls__card--status">
					<div class="mep-ls__card-head">
						<span class="mep-ls__card-icon"><i class="fas fa-tachometer-alt"></i></span>
						<h3 class="mep-ls__card-title"><?php esc_html_e( 'Environment Status', 'mage-eventpress' ); ?></h3>
					</div>
					<div class="mep-ls__card-body">
						<?php do_action( 'mep_event_status_notice_sec' ); ?>
						<div class="mep-ls__status-grid">
							<?php foreach ( $env_items as $item ) : ?>
								<?php self::render_status_item( $item ); ?>
							<?php endforeach; ?>
						</div>

						<?php
						$cart_count = function_exists( 'mep_event_cart_temp_count' ) ? (int) mep_event_cart_temp_count() : 0;
						if ( $cart_count > 0 ) :
							?>
							<div class="mep-ls__cart-row">
								<div>
									<span class="mep-ls__status-label"><?php esc_html_e( 'Event on Cart (Temporary Booked)', 'mage-eventpress' ); ?></span>
									<span class="mep-ls__status-value"><?php echo esc_html( (string) $cart_count ); ?></span>
								</div>
								<div id="empty-cart-message"></div>
								<button type="button" id="empty-cart-btn" class="mep-ls__cart-btn"><?php esc_html_e( 'Empty Cart', 'mage-eventpress' ); ?></button>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( ! empty( $extra_items ) ) : ?>
					<div class="mep-ls__card mep-ls__card--status mep-ls__card--checks">
						<div class="mep-ls__card-head">
							<span class="mep-ls__card-icon"><i class="fas fa-check-double"></i></span>
							<h3 class="mep-ls__card-title"><?php esc_html_e( 'Additional Checks', 'mage-eventpress' ); ?></h3>
						</div>
						<div class="mep-ls__card-body">
							<div class="mep-ls__status-grid">
								<?php foreach ( $extra_items as $item ) : ?>
									<?php self::render_status_item( $item ); ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<?php
			}

			/**
			 * @param array $item Status item with label, value, ok.
			 */
			private static function render_status_item( $item ) {
				$ok = ! empty( $item['ok'] );
				?>
				<div class="mep-ls__status-item">
					<span class="mep-ls__status-label"><?php echo esc_html( $item['label'] ); ?></span>
					<span class="mep-ls__status-value">
						<?php
						if ( ! empty( $item['value_html'] ) ) {
							echo wp_kses_post( $item['value_html'] );
						} else {
							echo esc_html( $item['value'] );
						}
						?>
					</span>
					<span class="mep-ls__status-icon <?php echo $ok ? 'is-ok' : 'is-bad'; ?>">
						<?php if ( $ok ) : ?>
							<i class="fas fa-check-circle"></i>
						<?php else : ?>
							<i class="fas fa-exclamation-circle"></i>
						<?php endif; ?>
					</span>
				</div>
				<?php
			}

			/**
			 * Parse addon status table rows into the same grid item shape.
			 *
			 * @return array[]
			 */
			private static function get_additional_status_items() {
				ob_start();
				do_action( 'mep_event_status_table_item_sec' );
				$html = trim( ob_get_clean() );
				if ( '' === $html ) {
					return array();
				}

				$items = array();
				$dom   = new DOMDocument();
				$prev  = libxml_use_internal_errors( true );
				$dom->loadHTML( '<?xml encoding="utf-8" ?><table><tbody>' . $html . '</tbody></table>' );
				libxml_clear_errors();
				libxml_use_internal_errors( $prev );

				foreach ( $dom->getElementsByTagName( 'tr' ) as $tr ) {
					$cells = array();
					foreach ( $tr->childNodes as $child ) {
						if ( ! ( $child instanceof DOMElement ) ) {
							continue;
						}
						if ( 'td' !== $child->tagName && 'th' !== $child->tagName ) {
							continue;
						}
						$class = $child->getAttribute( 'class' );
						if ( false !== strpos( $class, 'help' ) ) {
							continue;
						}
						$cells[] = $child;
					}
					if ( count( $cells ) < 2 ) {
						continue;
					}

					$label_el = $cells[0];
					$value_el = $cells[ count( $cells ) - 1 ];
					$label    = trim( preg_replace( '/:\s*$/', '', $label_el->textContent ) );
					$label    = preg_replace( '/\s+/', ' ', $label );

					$value_html_raw = '';
					foreach ( $value_el->childNodes as $node ) {
						$value_html_raw .= $dom->saveHTML( $node );
					}

					$ok_haystack = strtolower( $value_html_raw . ' ' . $value_el->textContent );
					$ok          = true;
					if (
						false !== strpos( $ok_haystack, 'mep_error' )
						|| false !== strpos( $ok_haystack, '_text_warning' )
						|| false !== strpos( $ok_haystack, 'dashicons-no' )
						|| false !== strpos( $ok_haystack, 'fa-exclamation' )
						|| false !== strpos( $ok_haystack, 'not installed' )
						|| false !== strpos( $ok_haystack, 'not active' )
					) {
						$ok = false;
					}

					// Prefer plain text; keep action links when present.
					$value_text = trim( preg_replace( '/\s+/', ' ', $value_el->textContent ) );
					$value_html = '';
					$links      = $value_el->getElementsByTagName( 'a' );
					if ( $links->length > 0 ) {
						$parts = array();
						foreach ( $value_el->childNodes as $node ) {
							if ( XML_TEXT_NODE === $node->nodeType ) {
								$t = trim( preg_replace( '/\s+/', ' ', $node->textContent ) );
								if ( '' !== $t ) {
									$parts[] = esc_html( $t );
								}
							} elseif ( $node instanceof DOMElement && 'a' === $node->tagName ) {
								$parts[] = $dom->saveHTML( $node );
							} elseif ( $node instanceof DOMElement ) {
								$inner_links = $node->getElementsByTagName( 'a' );
								$text        = trim( preg_replace( '/\s+/', ' ', $node->textContent ) );
								// Remove link labels from surrounding text once.
								foreach ( $inner_links as $a ) {
									$lt = trim( $a->textContent );
									if ( '' !== $lt ) {
										$text = trim( str_replace( $lt, '', $text ) );
									}
								}
								$text = trim( preg_replace( '/\s+/', ' ', $text ) );
								if ( '' !== $text ) {
									$parts[] = esc_html( $text );
								}
								foreach ( $inner_links as $a ) {
									$parts[] = $dom->saveHTML( $a );
								}
							}
						}
						$value_html = trim( implode( ' ', $parts ) );
					}

					if ( '' === $label ) {
						continue;
					}

					$items[] = array(
						'label'      => $label,
						'value'      => $value_text ? $value_text : '—',
						'value_html' => $value_html,
						'ok'         => $ok,
					);
				}

				return $items;
			}

			/**
			 * @return array[]
			 */
			private static function get_env_status_items() {
				global $wpdb;

				$wp_v   = get_bloginfo( 'version' );
				$wc_i   = function_exists( 'mep_woo_install_check' ) ? mep_woo_install_check() : 'No';
				$wc_v   = ( function_exists( 'WC' ) && WC() ) ? WC()->version : '—';
				$php_v  = PHP_VERSION;
				$mysql  = isset( $wpdb->db_version ) ? $wpdb->db_version() : '—';
				$wp_mem = defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : '—';
				$php_mem = ini_get( 'memory_limit' );
				$upload  = size_format( wp_max_upload_size() );
				$post    = ini_get( 'post_max_size' );
				$exec    = ini_get( 'max_execution_time' );
				$input   = ini_get( 'max_input_vars' );

				$wp_ok   = version_compare( $wp_v, '5.5', '>' );
				$wc_ok   = ( 'Yes' === $wc_i );
				$wc_v_ok = $wc_ok && $wc_v && version_compare( $wc_v, '4.8', '>' );
				$php_ok  = version_compare( $php_v, '7.4', '>=' );

				return array(
					array(
						'label' => __( 'WordPress Version', 'mage-eventpress' ),
						'value' => $wp_v,
						'ok'    => $wp_ok,
					),
					array(
						'label' => __( 'Woocommerce Installed', 'mage-eventpress' ),
						'value' => $wc_i,
						'ok'    => $wc_ok,
					),
					array(
						'label' => __( 'Woocommerce Version', 'mage-eventpress' ),
						'value' => $wc_ok ? $wc_v : '—',
						'ok'    => $wc_v_ok,
					),
					array(
						'label' => __( 'PHP Version', 'mage-eventpress' ),
						'value' => $php_v,
						'ok'    => $php_ok,
					),
					array(
						'label' => __( 'MySQL Version', 'mage-eventpress' ),
						'value' => $mysql,
						'ok'    => true,
					),
					array(
						'label' => __( 'WP Memory Limit', 'mage-eventpress' ),
						'value' => $wp_mem,
						'ok'    => true,
					),
					array(
						'label' => __( 'PHP Memory Limit', 'mage-eventpress' ),
						'value' => $php_mem ? $php_mem : '—',
						'ok'    => true,
					),
					array(
						'label' => __( 'PHP Max Upload Size', 'mage-eventpress' ),
						'value' => $upload ? $upload : '—',
						'ok'    => true,
					),
					array(
						'label' => __( 'PHP Max Post Size', 'mage-eventpress' ),
						'value' => $post ? $post : '—',
						'ok'    => true,
					),
					array(
						'label' => __( 'PHP Max Execution Time', 'mage-eventpress' ),
						'value' => ( '' !== $exec && false !== $exec ) ? (string) $exec : '—',
						'ok'    => true,
					),
					array(
						'label' => __( 'PHP Max Input Vars', 'mage-eventpress' ),
						'value' => $input ? (string) $input : '—',
						'ok'    => true,
					),
				);
			}
		}
	}
