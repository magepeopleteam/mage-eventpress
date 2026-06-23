<?php
	/**
	 * Template Override Settings Class
	 *
	 * Handles the template override functionality for Mage EventPress plugin.
	 * Provides secure copying and removal of template files to/from active theme.
	 *
	 * @package MageEventPress
	 * @subpackage admin\Settings
	 * @author engr.sumonazma@gmail.com
	 * @copyright mage-people.com
	 * @since 1.0.0
	 */
// Prevent direct access
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'MPWEM_Template_Override_Settings' ) ) {
		/**
		 * MPWEM_Template_Override_Settings Class
		 *
		 * Manages template override functionality with security measures
		 * for WordPress.org compliance.
		 */
		class MPWEM_Template_Override_Settings {
			/**
			 * Constructor
			 *
			 * Initializes the template override settings and registers AJAX actions.
			 * All AJAX actions are restricted to admin users with manage_options capability.
			 */
			public function __construct() {
				// Register AJAX actions for authenticated admin users only
				add_action( 'wp_ajax_mep_copy_template_to_theme', array( $this, 'copy_template_to_theme' ) );
				add_action( 'wp_ajax_mep_remove_template_from_theme', array( $this, 'remove_template_from_theme' ) );
				add_action( 'wp_ajax_mep_get_template_status', array( $this, 'get_template_status' ) );
			}
			/**
			 * Render the template override settings page
			 */
			public function template_override_settings_page() {
				?>
                <div class="mpwem-template-override-wrapper">
                    <h2><?php esc_html_e( 'Template Override System', 'mage-eventpress' ); ?></h2>
                    <p class="description">
						<?php esc_html_e( 'This system allows you to automatically copy plugin templates to your active theme for customization. Templates copied to your theme will override the plugin defaults.', 'mage-eventpress' ); ?>
                    </p>
                    <div class="mpwem-template-override-notice">
                        <p><strong><?php esc_html_e( 'Important:', 'mage-eventpress' ); ?></strong></p>
                        <ul>
                            <li><?php esc_html_e( 'Templates will be copied to: ', 'mage-eventpress' ); ?><code><?php echo esc_html( get_stylesheet_directory() ); ?>/mage-event/</code></li>
                            <li><?php esc_html_e( 'Always backup your theme before making changes', 'mage-eventpress' ); ?></li>
                            <li><?php esc_html_e( 'Template overrides will persist through plugin updates', 'mage-eventpress' ); ?></li>
                        </ul>
                    </div>
                    <div class="mpwem-template-categories">
						<?php $this->render_template_category( 'themes', __( 'Event Themes', 'mage-eventpress' ), 'templates/themes/' ); ?>
						<?php $this->render_template_category( 'layout', __( 'Layout Templates', 'mage-eventpress' ), 'templates/layout/' ); ?>
						<?php //$this->render_template_category( 'single', __( 'Single Event Templates', 'mage-eventpress' ), 'templates/single/' ); ?>
						<?php $this->render_template_category( 'list', __( 'Event List Templates', 'mage-eventpress' ), 'templates/list/' ); ?>
                    </div>
                </div>
                <script type="text/javascript">
                    // Pass nonce to JavaScript
                    var mpwem_template_override_nonce = '<?php echo esc_js( wp_create_nonce( 'mep_template_override_nonce' ) ); ?>';
                </script>
				<?php
			}
			/**
			 * Render a template category section
			 */
			private function render_template_category( $category, $title, $template_dir ) {
				$plugin_template_dir = MPWEM_PLUGIN_DIR . '/' . $template_dir;
				$theme_template_dir  = get_stylesheet_directory() . '/mage-event/' . str_replace( 'templates/', '', $template_dir );
				if ( ! is_dir( $plugin_template_dir ) ) {
					return;
				}
				$templates = $this->get_templates_in_directory( $plugin_template_dir );
				if ( empty( $templates ) ) {
					return;
				}
				?>
                <div class="mpwem-template-category">
                    <h3><?php echo esc_html( $title ); ?></h3>
                    <div class="mpwem-template-list">
						<?php foreach ( $templates as $template_file ) :
							$template_path = $template_dir . $template_file;
							$is_overridden = $this->is_template_overridden( $template_path );
							$template_name = $this->get_template_display_name( $template_file );
							?>
                            <div class="mpwem-template-item <?php echo $is_overridden ? 'overridden' : ''; ?>">
                                <div class="mpwem-template-name"><?php echo esc_html( $template_name ); ?></div>
                                <div class="mpwem-template-path"><?php echo esc_html( $template_path ); ?></div>
                                <div class="mpwem-template-status">
									<?php if ( $is_overridden ) : ?>
                                        <span class="mpwem-status-badge mpwem-status-overridden"><?php esc_html_e( 'Overridden', 'mage-eventpress' ); ?></span>
									<?php else : ?>
                                        <span class="mpwem-status-badge mpwem-status-default"><?php esc_html_e( 'Default', 'mage-eventpress' ); ?></span>
									<?php endif; ?>
                                </div>
                                <div class="mpwem-template-actions">
                                    <button class="mpwem-btn mpwem-btn-primary mpwem-copy-template<?php echo $is_overridden ? ' mpwem-hidden' : ''; ?>"
                                            data-template="<?php echo esc_attr( $template_path ); ?>">
										<?php esc_html_e( 'Copy to Theme', 'mage-eventpress' ); ?>
                                    </button>
                                    <button class="mpwem-btn mpwem-btn-danger mpwem-remove-template<?php echo ! $is_overridden ? ' mpwem-hidden' : ''; ?>"
                                            data-template="<?php echo esc_attr( $template_path ); ?>">
										<?php esc_html_e( 'Remove Override', 'mage-eventpress' ); ?>
                                    </button>
                                    <a href="<?php echo esc_url( admin_url( 'theme-editor.php?file=mage-event/' . str_replace( 'templates/', '', $template_path ) . '&theme=' . get_stylesheet() ) ); ?>"
                                       class="mpwem-btn mpwem-btn-secondary mpwem-edit-template<?php echo ! $is_overridden ? ' mpwem-hidden' : ''; ?>"
                                       target="_blank">
										<?php esc_html_e( 'Edit in Theme Editor', 'mage-eventpress' ); ?>
                                    </a>
                                </div>
                            </div>
						<?php endforeach; ?>
                    </div>
                </div>
				<?php
			}
			/**
			 * Get all template files in a directory
			 *
			 * @param string $dir Directory path to scan
			 *
			 * @return array Array of template files
			 */
			private function get_templates_in_directory( $dir ) {
				$templates = array();
				// Validate directory path
				if ( ! is_dir( $dir ) || ! is_readable( $dir ) ) {
					return $templates;
				}
				// Security check: ensure directory is within plugin directory
				$real_dir        = realpath( $dir );
				$real_plugin_dir = realpath( MPWEM_PLUGIN_DIR );
				if ( ! $real_dir || ! $real_plugin_dir || strpos( $real_dir, $real_plugin_dir ) !== 0 ) {
					return $templates;
				}
				$files = scandir( $dir );
				if ( $files === false ) {
					return $templates;
				}
				// Define deprecated themes that should not appear in template override
				$deprecated_themes = array( 'royal.php', 'theme-1.php', 'theme-2.php', 'theme-3.php', 'vanilla.php' );
				foreach ( $files as $file ) {
					// Skip hidden files and directories
					if ( strpos( $file, '.' ) === 0 ) {
						continue;
					}
					if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' && $file !== 'index.php' ) {
						// Skip deprecated themes in template override interface
						if ( strpos( $dir, 'themes' ) !== false && in_array( $file, $deprecated_themes, true ) ) {
							continue;
						}
						$templates[] = $file;
					}
				}
				return $templates;
			}
			/**
			 * Check if a template is overridden in the theme
			 */
			private function is_template_overridden( $template_path ) {
				$theme_template_path = get_stylesheet_directory() . '/mage-event/' . str_replace( 'templates/', '', $template_path );
				return file_exists( $theme_template_path );
			}
			/**
			 * Get display name for template
			 *
			 * @param string $template_file Template filename
			 *
			 * @return string Display name for the template
			 */
			private function get_template_display_name( $template_file ) {
				// Sanitize input
				$template_file = sanitize_file_name( $template_file );
				// Try to extract template name from file header
				$plugin_file = MPWEM_PLUGIN_DIR . '/templates/themes/' . $template_file;
				// Security check: ensure file is within plugin directory
				$real_file       = realpath( $plugin_file );
				$real_plugin_dir = realpath( MPWEM_PLUGIN_DIR );
				if ( $real_file && $real_plugin_dir && strpos( $real_file, $real_plugin_dir ) === 0 && file_exists( $plugin_file ) ) {
					// Limit file size to prevent memory issues
					$max_file_size = 1024 * 1024; // 1MB
					if ( filesize( $plugin_file ) <= $max_file_size ) {
						$file_content = file_get_contents( $plugin_file );
						if ( $file_content !== false && preg_match( '/Template Name:\s*(.+)/i', $file_content, $matches ) ) {
							return sanitize_text_field( trim( $matches[1] ) );
						}
					}
				}
				// Fallback to filename
				return sanitize_text_field( ucwords( str_replace( array( '-', '_', '.php' ), array( ' ', ' ', '' ), $template_file ) ) );
			}
			/**
			 * AJAX handler to copy template to theme
			 */
			public function copy_template_to_theme() {
				// Verify nonce
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_template_override_nonce' ) ) {
					wp_send_json_error( esc_html__( 'Security check failed', 'mage-eventpress' ) );
				}
				// Check user capabilities
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( esc_html__( 'Insufficient permissions', 'mage-eventpress' ) );
				}
				// Validate and sanitize input
				if ( ! isset( $_POST['template_path'] ) || empty( $_POST['template_path'] ) ) {
					wp_send_json_error( esc_html__( 'Template path is required', 'mage-eventpress' ) );
				}
				$template_path = sanitize_text_field( wp_unslash( $_POST['template_path'] ) );
				// Additional path validation - ensure it's a valid template path
				if ( ! $this->is_valid_template_path( $template_path ) ) {
					wp_send_json_error( esc_html__( 'Invalid template path', 'mage-eventpress' ) );
				}
				$source_file = MPWEM_PLUGIN_DIR . '/' . $template_path;
				// Validate source file exists and is within plugin directory
				if ( ! file_exists( $source_file ) ) {
					wp_send_json_error( esc_html__( 'Source template file not found', 'mage-eventpress' ) );
				}
				// Security check: ensure source file is within plugin directory
				$real_source     = realpath( $source_file );
				$real_plugin_dir = realpath( MPWEM_PLUGIN_DIR );
				if ( ! $real_source || ! $real_plugin_dir || strpos( $real_source, $real_plugin_dir ) !== 0 ) {
					wp_send_json_error( esc_html__( 'Invalid source file path', 'mage-eventpress' ) );
				}
				// Create destination directory structure
				$theme_dir        = get_stylesheet_directory() . '/mage-event/';
				$relative_path    = str_replace( 'templates/', '', $template_path );
				$destination_file = $theme_dir . $relative_path;
				$destination_dir  = dirname( $destination_file );
				// Security check: ensure destination is within theme directory
				// Normalize paths for comparison (handle both existing and non-existing directories)
				$real_theme_dir = realpath( get_stylesheet_directory() );
				if ( ! $real_theme_dir ) {
					wp_send_json_error( esc_html__( 'Could not resolve theme directory', 'mage-eventpress' ) );
				}
				// For non-existing directories, we need to validate the intended path
				$normalized_destination = wp_normalize_path( $destination_dir );
				$normalized_theme_dir   = wp_normalize_path( $real_theme_dir );
				// Check if the destination path is within the theme directory
				if ( strpos( $normalized_destination, $normalized_theme_dir ) !== 0 ) {
					wp_send_json_error( esc_html__( 'Invalid destination path', 'mage-eventpress' ) );
				}
				// Additional security check: ensure no directory traversal in the relative path
				if ( strpos( $relative_path, '..' ) !== false || strpos( $relative_path, './' ) !== false ) {
					wp_send_json_error( esc_html__( 'Invalid destination path - directory traversal detected', 'mage-eventpress' ) );
				}
				// Create directory if it doesn't exist
				if ( ! wp_mkdir_p( $destination_dir ) ) {
					wp_send_json_error( esc_html__( 'Could not create theme directory', 'mage-eventpress' ) );
				}
				// Copy the file
				if ( ! copy( $source_file, $destination_file ) ) {
					wp_send_json_error( esc_html__( 'Could not copy template file', 'mage-eventpress' ) );
				}
				// Verify the file was copied successfully
				if ( ! file_exists( $destination_file ) ) {
					wp_send_json_error( esc_html__( 'File copy failed', 'mage-eventpress' ) );
				}
				wp_send_json_success( esc_html__( 'Template copied successfully', 'mage-eventpress' ) );
			}
			/**
			 * AJAX handler to remove template from theme
			 */
			public function remove_template_from_theme() {
				// Verify nonce
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_template_override_nonce' ) ) {
					wp_send_json_error( esc_html__( 'Security check failed', 'mage-eventpress' ) );
				}
				// Check user capabilities
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( esc_html__( 'Insufficient permissions', 'mage-eventpress' ) );
				}
				// Validate and sanitize input
				if ( ! isset( $_POST['template_path'] ) || empty( $_POST['template_path'] ) ) {
					wp_send_json_error( esc_html__( 'Template path is required', 'mage-eventpress' ) );
				}
				$template_path = sanitize_text_field( wp_unslash( $_POST['template_path'] ) );
				// Additional path validation
				if ( ! $this->is_valid_template_path( $template_path ) ) {
					wp_send_json_error( esc_html__( 'Invalid template path', 'mage-eventpress' ) );
				}
				$theme_dir      = get_stylesheet_directory() . '/mage-event/';
				$relative_path  = str_replace( 'templates/', '', $template_path );
				$file_to_remove = $theme_dir . $relative_path;
				// Enhanced security validation
				if ( ! file_exists( $file_to_remove ) ) {
					wp_send_json_error( esc_html__( 'Template file not found', 'mage-eventpress' ) );
				}
				// Enhanced security validation with normalized paths
				$real_file      = realpath( $file_to_remove );
				$real_theme_dir = realpath( $theme_dir );
				// Use normalized paths for comparison if realpath fails
				if ( ! $real_file ) {
					$normalized_file      = wp_normalize_path( $file_to_remove );
					$normalized_theme_dir = wp_normalize_path( $theme_dir );
					if ( strpos( $normalized_file, $normalized_theme_dir ) !== 0 ) {
						wp_send_json_error( esc_html__( 'Invalid file path', 'mage-eventpress' ) );
					}
				} else {
					if ( ! $real_theme_dir || strpos( $real_file, $real_theme_dir ) !== 0 ) {
						wp_send_json_error( esc_html__( 'Invalid file path', 'mage-eventpress' ) );
					}
				}
				// Remove the file
				if ( ! unlink( $file_to_remove ) ) {
					wp_send_json_error( esc_html__( 'Could not remove template file', 'mage-eventpress' ) );
				}
				// Check if we need to clean up empty directories
				$this->cleanup_empty_directories( $theme_dir );
				wp_send_json_success( esc_html__( 'Template override removed successfully', 'mage-eventpress' ) );
			}
			/**
			 * Clean up empty directories recursively
			 */
			private function cleanup_empty_directories( $base_dir ) {
				// Ensure the base directory exists
				if ( ! is_dir( $base_dir ) ) {
					return;
				}
				// Get all subdirectories
				$subdirs  = array();
				$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $base_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
					RecursiveIteratorIterator::CHILD_FIRST
				);
				foreach ( $iterator as $file ) {
					if ( $file->isDir() ) {
						$subdirs[] = $file->getPathname();
					}
				}
				// Remove empty directories from deepest to shallowest
				// But preserve the main mage-events directory
				foreach ( $subdirs as $dir ) {
					// Don't delete the base mage-events directory
					if ( rtrim( $dir, '/\\' ) !== rtrim( $base_dir, '/\\' ) ) {
						$this->remove_directory_if_empty( $dir );
					}
				}
			}
			/**
			 * Remove directory if it's empty
			 */
			private function remove_directory_if_empty( $dir ) {
				// Don't remove if directory doesn't exist
				if ( ! is_dir( $dir ) ) {
					return false;
				}
				// Check if directory is empty (only contains . and .. entries)
				$files = scandir( $dir );
				$files = array_diff( $files, array( '.', '..' ) );
				// If directory is empty, remove it
				if ( empty( $files ) ) {
					return rmdir( $dir );
				}
				return false;
			}
			/**
			 * Validate template path to prevent directory traversal and ensure security
			 *
			 * @param string $template_path The template path to validate
			 *
			 * @return bool True if valid, false otherwise
			 */
			private function is_valid_template_path( $template_path ) {
				// Sanitize input
				$template_path = sanitize_text_field( $template_path );
				// Check for empty path
				if ( empty( $template_path ) ) {
					return false;
				}
				// Check for directory traversal attempts
				if ( strpos( $template_path, '..' ) !== false || strpos( $template_path, './' ) !== false ) {
					return false;
				}
				// Check for null bytes (security vulnerability)
				if ( strpos( $template_path, "\0" ) !== false ) {
					return false;
				}
				// Ensure path starts with templates/
				if ( strpos( $template_path, 'templates/' ) !== 0 ) {
					return false;
				}
				// Ensure it's a PHP file
				if ( pathinfo( $template_path, PATHINFO_EXTENSION ) !== 'php' ) {
					return false;
				}
				// Validate filename characters (alphanumeric, dash, underscore, dot, slash)
				if ( ! preg_match( '/^[a-zA-Z0-9\/_.-]+$/', $template_path ) ) {
					return false;
				}
				// Additional validation - check if file exists in plugin
				$source_file = MPWEM_PLUGIN_DIR . '/' . $template_path;
				if ( ! file_exists( $source_file ) || ! is_readable( $source_file ) ) {
					return false;
				}
				// Final security check: ensure source file is within plugin directory
				$real_source     = realpath( $source_file );
				$real_plugin_dir = realpath( MPWEM_PLUGIN_DIR );
				if ( ! $real_source || ! $real_plugin_dir || strpos( $real_source, $real_plugin_dir ) !== 0 ) {
					return false;
				}
				return true;
			}
			/**
			 * AJAX handler to get template status
			 */
			public function get_template_status() {
				// Verify nonce
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mep_template_override_nonce' ) ) {
					wp_send_json_error( esc_html__( 'Security check failed', 'mage-eventpress' ) );
				}
				// Check user capabilities
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( esc_html__( 'Insufficient permissions', 'mage-eventpress' ) );
				}
				// Validate and sanitize input
				if ( ! isset( $_POST['template_path'] ) || empty( $_POST['template_path'] ) ) {
					wp_send_json_error( esc_html__( 'Template path is required', 'mage-eventpress' ) );
				}
				$template_path = sanitize_text_field( wp_unslash( $_POST['template_path'] ) );
				// Additional path validation
				if ( ! $this->is_valid_template_path( $template_path ) ) {
					wp_send_json_error( esc_html__( 'Invalid template path', 'mage-eventpress' ) );
				}
				$is_overridden = $this->is_template_overridden( $template_path );
				wp_send_json_success( array(
					'is_overridden' => $is_overridden
				) );
			}
		}
		// Initialize the class
		new MPWEM_Template_Override_Settings();
	}