<?php
	/**
	 * Plugin deactivation flow for Event Booking Manager.
	 *
	 * Adds a modal on the Plugins screen that, when the user clicks "Deactivate",
	 * lets them choose between:
	 *   1. Delete all plugin data (events, attendees, hidden products, settings)
	 *      and then deactivate.
	 *   2. Deactivate the normal way without removing anything.
	 *
	 * Only data created by this plugin/suite is removed. WooCommerce orders and
	 * any other plugins' data are never touched.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	if ( ! class_exists( 'MPWEM_Deactivation' ) ) {
		class MPWEM_Deactivation {
			/**
			 * AJAX action + nonce names.
			 */
			const AJAX_ACTION = 'mpwem_deactivate_cleanup';
			const NONCE       = 'mpwem_deactivate_cleanup_nonce';

			public function __construct() {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
				add_action( 'admin_footer-plugins.php', array( $this, 'render_modal' ) );
				add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_cleanup' ) );
			}

			/**
			 * Plugin basename (dir/file.php) used to target the right row.
			 */
			public static function plugin_basename(): string {
				return plugin_basename( dirname( __DIR__ ) . '/woocommerce-event-press.php' );
			}

			public function enqueue( $hook ): void {
				if ( 'plugins.php' !== $hook || ! current_user_can( 'activate_plugins' ) ) {
					return;
				}
				$css = MPWEM_PLUGIN_DIR . '/assets/admin/mpwem-deactivation.css';
				$js  = MPWEM_PLUGIN_DIR . '/assets/admin/mpwem-deactivation.js';
				wp_enqueue_style(
					'mpwem-deactivation',
					MPWEM_PLUGIN_URL . '/assets/admin/mpwem-deactivation.css',
					array(),
					file_exists( $css ) ? filemtime( $css ) : '5.3.4'
				);
				wp_enqueue_script(
					'mpwem-deactivation',
					MPWEM_PLUGIN_URL . '/assets/admin/mpwem-deactivation.js',
					array( 'jquery' ),
					file_exists( $js ) ? filemtime( $js ) : '5.3.4',
					true
				);
				wp_localize_script(
					'mpwem-deactivation',
					'mpwemDeactivation',
					array(
						'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
						'action'   => self::AJAX_ACTION,
						'nonce'    => wp_create_nonce( self::NONCE ),
						'basename' => self::plugin_basename(),
						'i18n'     => array(
							'cleaning'  => esc_html__( 'Deleting data…', 'mage-eventpress' ),
							'finishing' => esc_html__( 'Finishing up…', 'mage-eventpress' ),
							'removed'   => esc_html__( '%1$s of %2$s items removed', 'mage-eventpress' ),
							'failed'    => esc_html__( 'Cleanup failed. Please try again or choose "Deactivate only".', 'mage-eventpress' ),
							'confirm'   => esc_html__( 'Please tick the confirmation box to permanently delete all data.', 'mage-eventpress' ),
						),
					)
				);
			}

			/**
			 * Print the modal markup once on the Plugins screen.
			 */
			public function render_modal(): void {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}
				?>
				<div class="mpwem-deact-overlay" id="mpwem-deact-modal" aria-hidden="true">
					<div class="mpwem-deact-modal" role="dialog" aria-modal="true" aria-labelledby="mpwem-deact-title">
						<div class="mpwem-deact-head">
							<h2 id="mpwem-deact-title"><?php esc_html_e( 'Deactivate Event Booking Manager', 'mage-eventpress' ); ?></h2>
							<button type="button" class="mpwem-deact-close" aria-label="<?php esc_attr_e( 'Close', 'mage-eventpress' ); ?>">&times;</button>
						</div>
						<div class="mpwem-deact-body">
							<div class="mpwem-deact-choice">
								<p class="mpwem-deact-intro"><?php esc_html_e( 'How would you like to deactivate this plugin?', 'mage-eventpress' ); ?></p>

								<label class="mpwem-deact-option">
									<input type="radio" name="mpwem_deact_mode" value="keep" checked>
									<span class="mpwem-deact-option__body">
										<span class="mpwem-deact-option__title"><?php esc_html_e( 'Deactivate only (recommended)', 'mage-eventpress' ); ?></span>
										<span class="mpwem-deact-option__desc"><?php esc_html_e( 'Turn the plugin off but keep all events, attendees and settings so everything is back when you reactivate.', 'mage-eventpress' ); ?></span>
									</span>
								</label>

								<label class="mpwem-deact-option mpwem-deact-option--danger">
									<input type="radio" name="mpwem_deact_mode" value="purge">
									<span class="mpwem-deact-option__body">
										<span class="mpwem-deact-option__title"><?php esc_html_e( 'Delete all plugin data, then deactivate', 'mage-eventpress' ); ?></span>
										<span class="mpwem-deact-option__desc"><?php esc_html_e( 'Permanently remove every event, attendee, registration form, hidden WooCommerce product and all plugin settings created by Event Booking Manager. WooCommerce orders and other plugins are not touched. This cannot be undone.', 'mage-eventpress' ); ?></span>
									</span>
								</label>

								<div class="mpwem-deact-confirm" hidden>
									<label>
										<input type="checkbox" id="mpwem-deact-understand">
										<span><?php esc_html_e( 'I understand this permanently deletes all Event Booking Manager data.', 'mage-eventpress' ); ?></span>
									</label>
								</div>

								<p class="mpwem-deact-error" role="alert" aria-live="polite" hidden></p>
							</div>

							<div class="mpwem-deact-progress" hidden>
								<p class="mpwem-deact-progress__label"><?php esc_html_e( 'Deleting plugin data… please keep this tab open.', 'mage-eventpress' ); ?></p>
								<div class="mpwem-deact-bar"><span class="mpwem-deact-bar__fill" style="width:0%"></span></div>
								<p class="mpwem-deact-progress__count"></p>
							</div>
						</div>
						<div class="mpwem-deact-foot">
							<button type="button" class="button mpwem-deact-cancel"><?php esc_html_e( 'Cancel', 'mage-eventpress' ); ?></button>
							<a href="#" class="button button-primary mpwem-deact-submit"><?php esc_html_e( 'Continue', 'mage-eventpress' ); ?></a>
						</div>
					</div>
				</div>
				<?php
			}

			/**
			 * The plugin's custom post types whose posts are removed on a full cleanup.
			 */
			private function post_types(): array {
				return array(
					'mep_events',
					'mep_events_attendees',
					'mep_events_reg_form',
					'mep_event_speaker',
					'mep_temp_attendee',
				);
			}

			/**
			 * AJAX: batched data erase.
			 *
			 * Steps (driven by the modal JS):
			 *   step=count  -> return how many posts/products will be removed (for the bar).
			 *   step=batch  -> delete one chunk; when nothing is left, finalize terms/options/files.
			 *
			 * Batching keeps each request small so sites with thousands of events (and their
			 * linked hidden products) never hit a PHP timeout mid-delete.
			 */
			public function ajax_cleanup(): void {
				if ( ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'mage-eventpress' ) ), 403 );
				}
				if ( ! current_user_can( 'activate_plugins' ) || ! current_user_can( 'delete_others_posts' ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'You are not allowed to perform this action.', 'mage-eventpress' ) ), 403 );
				}

				if ( function_exists( 'set_time_limit' ) ) {
					@set_time_limit( 0 );
				}

				$step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : 'batch';

				if ( 'count' === $step ) {
					wp_send_json_success( array( 'total' => $this->count_remaining() ) );
				}

				$batch_size = isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 20;
				$batch_size = max( 1, min( 100, $batch_size ) );

				wp_send_json_success( $this->purge_batch( $batch_size ) );
			}

			/**
			 * How many event posts + linked hidden products are still on disk.
			 */
			public function count_remaining(): int {
				global $wpdb;

				$post_types      = $this->post_types();
				$pt_placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
				$posts           = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type IN ( $pt_placeholders )",
						$post_types
					)
				);
				$products = (int) $wpdb->get_var(
					"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = 'link_mep_event'"
				);

				return $posts + $products;
			}

			/**
			 * Delete one chunk of posts + products. When none remain, run the final
			 * (fast, bounded) cleanup of taxonomy terms, options and uploaded files.
			 * Idempotent: re-running simply removes whatever is left.
			 *
			 * @return array { deleted, remaining, done }
			 */
			public function purge_batch( int $size ): array {
				global $wpdb;

				$deleted = 0;

				/* 1. Plugin custom post types (events, attendees, reg forms, speakers, temp). */
				$post_types      = $this->post_types();
				$pt_placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
				$post_ids        = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( $pt_placeholders ) LIMIT %d",
						array_merge( $post_types, array( $size ) )
					)
				);
				foreach ( $post_ids as $pid ) {
					if ( wp_delete_post( (int) $pid, true ) ) {
						$deleted ++;
					}
				}

				/* 2. Hidden WooCommerce products that were generated for events. */
				$product_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'link_mep_event' LIMIT %d",
						$size
					)
				);
				foreach ( $product_ids as $product_id ) {
					if ( 'product' === get_post_type( (int) $product_id ) ) {
						if ( wp_delete_post( (int) $product_id, true ) ) {
							$deleted ++;
						}
					} else {
						// Orphaned meta pointing at a non-product: drop it so we don't loop.
						delete_post_meta( (int) $product_id, 'link_mep_event' );
					}
				}

				$remaining = $this->count_remaining();
				$done      = false;

				if ( 0 === $remaining ) {
					$this->finalize();
					$done = true;
				}

				return array(
					'deleted'   => $deleted,
					'remaining' => $remaining,
					'done'      => $done,
				);
			}

			/**
			 * Remove the data that is fast/bounded to delete in a single pass:
			 * taxonomy terms, plugin options/transients and uploaded attendee files.
			 */
			private function finalize(): void {
				global $wpdb;

				/* 3. Plugin taxonomy terms (category / organizer / tag). */
				foreach ( array( 'mep_cat', 'mep_org', 'mep_tag' ) as $taxonomy ) {
					if ( ! taxonomy_exists( $taxonomy ) ) {
						register_taxonomy( $taxonomy, 'mep_events' );
					}
					$terms = get_terms(
						array(
							'taxonomy'   => $taxonomy,
							'hide_empty' => false,
							'fields'     => 'ids',
						)
					);
					if ( ! is_wp_error( $terms ) ) {
						foreach ( $terms as $term_id ) {
							wp_delete_term( (int) $term_id, $taxonomy );
						}
					}
				}

				/* 4. Plugin options + transients. Scoped to this plugin's naming only. */
				$like_mep        = $wpdb->esc_like( 'mep_' ) . '%';
				$like_mpwem      = $wpdb->esc_like( 'mpwem_' ) . '%';
				$like_setsec     = '%' . $wpdb->esc_like( '_setting_sec' );
				$like_t_mep      = $wpdb->esc_like( '_transient_mep_' ) . '%';
				$like_t_to_mep   = $wpdb->esc_like( '_transient_timeout_mep_' ) . '%';
				$like_t_mpwem    = $wpdb->esc_like( '_transient_mpwem_' ) . '%';
				$like_t_to_mpwem = $wpdb->esc_like( '_transient_timeout_mpwem_' ) . '%';
				$extra_options   = array(
					'mp_global_settings',
					'mp_style_settings',
					'mp_slider_settings',
					'mp_basic_license_settings',
				);
				$placeholders = implode( ', ', array_fill( 0, count( $extra_options ), '%s' ) );

				$sql = $wpdb->prepare(
					"DELETE FROM {$wpdb->options}
					 WHERE option_name LIKE %s
					    OR option_name LIKE %s
					    OR option_name LIKE %s
					    OR option_name LIKE %s
					    OR option_name LIKE %s
					    OR option_name LIKE %s
					    OR option_name LIKE %s
					    OR option_name IN ( $placeholders )",
					array_merge(
						array( $like_mep, $like_mpwem, $like_setsec, $like_t_mep, $like_t_to_mep, $like_t_mpwem, $like_t_to_mpwem ),
						$extra_options
					)
				);
				$wpdb->query( $sql );

				/* 5. Uploaded attendee files directory. */
				$upload  = wp_upload_dir();
				$att_dir = trailingslashit( $upload['basedir'] ) . 'mep_attendee_file_list';
				$this->delete_directory( $att_dir );
			}

			/**
			 * Recursively remove a directory (used for the attendee-file upload folder).
			 */
			private function delete_directory( string $dir ): void {
				if ( ! is_dir( $dir ) ) {
					return;
				}
				$items = scandir( $dir );
				if ( false === $items ) {
					return;
				}
				foreach ( $items as $item ) {
					if ( '.' === $item || '..' === $item ) {
						continue;
					}
					$path = $dir . DIRECTORY_SEPARATOR . $item;
					if ( is_dir( $path ) ) {
						$this->delete_directory( $path );
					} else {
						@unlink( $path );
					}
				}
				@rmdir( $dir );
			}
		}

		new MPWEM_Deactivation();
	}
