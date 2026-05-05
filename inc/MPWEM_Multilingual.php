<?php
/**
 * Multilingual Support for MageEventPress
 * Handles WPML and Polylang integration for events, products, and taxonomies
 *
 * @package MageEventPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'MPWEM_Multilingual' ) ) {
	class MPWEM_Multilingual {

		/**
		 * Current multilingual plugin
		 *
		 * @var string
		 */
		private $plugin = 'none';

		/**
		 * Singleton instance
		 *
		 * @var MPWEM_Multilingual
		 */
		private static $instance = null;

		/**
		 * Get singleton instance
		 *
		 * @return MPWEM_Multilingual
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			$this->plugin = mep_get_option( 'mep_multi_lang_plugin', 'general_setting_sec', 'none' );

			if ( $this->plugin === 'none' ) {
				return;
			}

			$this->init_hooks();
		}

		/**
		 * Initialize all hooks
		 */
		private function init_hooks() {
			// Event ID translations
			add_filter( 'mep_event_id', array( $this, 'translate_event_id' ), 10, 3 );
			add_filter( 'mep_get_default_lang_event_id', array( $this, 'get_default_language_event_id' ), 10, 2 );

			// Product ID translations
			add_filter( 'mep_event_product_id', array( $this, 'translate_product_id' ), 10, 2 );

			// Query modifications for event listing
			add_action( 'pre_get_posts', array( $this, 'filter_event_query' ), 10, 1 );

			// Taxonomy term translations (organizers, categories)
			add_filter( 'mep_get_organizer_id', array( $this, 'translate_organizer_id' ), 10, 2 );
			add_filter( 'mep_get_category_id', array( $this, 'translate_category_id' ), 10, 2 );

			// Admin column for translation status
			add_filter( 'manage_mep_events_posts_columns', array( $this, 'add_translation_columns' ), 20 );
			add_action( 'manage_mep_events_posts_custom_column', array( $this, 'render_translation_column' ), 10, 2 );

			// Sync product translations when event is saved
			add_action( 'save_post_mep_events', array( $this, 'sync_product_translations' ), 20, 3 );
			add_action( 'wp_after_insert_post', array( $this, 'sync_product_translations' ), 20, 4 );

			// Add translation link in admin bar
			add_filter( 'post_row_actions', array( $this, 'add_translate_link' ), 10, 2 );

			// Handle AJAX for fetching translations
			add_action( 'wp_ajax_mep_get_translations', array( $this, 'ajax_get_translations' ) );

			// Filter event URLs
			add_filter( 'mep_event_url', array( $this, 'translate_event_url' ), 10, 2 );

			// Translation status indicator
			add_action( 'add_meta_boxes', array( $this, 'add_translation_meta_box' ), 10, 2 );

			// Sync attendee data across translations
			add_filter( 'mep_get_event_attendees', array( $this, 'filter_attendees_by_language' ), 10, 3 );

			// Register AJAX handlers
			add_action( 'wp_ajax_mep_sync_product_translation', array( $this, 'ajax_sync_product_translation' ) );
			add_action( 'wp_ajax_mep_create_event_translation', array( $this, 'ajax_create_event_translation' ) );
			add_action( 'wp_ajax_mep_sync_all_product_translations', array( $this, 'ajax_sync_all_product_translations' ) );
			add_action( 'wp_ajax_mep_bulk_sync_all_products', array( $this, 'ajax_bulk_sync_all_products' ) );

			// Initialize multilingual plugin registration
			$this->register_multilingual_plugin_settings();

			// Enqueue admin scripts on event pages
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Register settings
			add_filter( 'mep_settings_sec_reg', array( $this, 'add_settings_section' ), 5 );
			add_filter( 'mep_settings_sec_fields', array( $this, 'add_settings_fields' ), 5 );

			// Add bulk actions to events list
			add_filter( 'bulk_actions-edit-mep_events', array( $this, 'register_bulk_actions' ) );
			add_filter( 'handle_bulk_actions-edit-mep_events', array( $this, 'handle_bulk_sync_products' ), 10, 3 );
			add_action( 'admin_notices', array( $this, 'admin_notice_after_bulk_sync' ) );
		}

		/**
		 * Get the current multilingual plugin
		 *
		 * @return string
		 */
		public function get_plugin() {
			return $this->plugin;
		}

		/**
		 * Check if a specific plugin is active
		 *
		 * @param string $plugin Plugin name to check
		 * @return bool
		 */
		public function is_plugin_active( $plugin ) {
			return $this->plugin === $plugin;
		}

		/**
		 * Get current language code
		 *
		 * @return string
		 */
		public function get_current_lang() {
			if ( $this->plugin === 'polylang' && function_exists( 'pll_current_language' ) ) {
				return pll_current_language();
			} elseif ( $this->plugin === 'wpml' && defined( 'ICL_LANGUAGE_CODE' ) ) {
				return ICL_LANGUAGE_CODE;
			}
			return '';
		}

		/**
		 * Get default language code
		 *
		 * @return string
		 */
		public function get_default_lang() {
			if ( $this->plugin === 'polylang' && function_exists( 'pll_default_language' ) ) {
				return pll_default_language();
			} elseif ( $this->plugin === 'wpml' && defined( 'ICL_LANGUAGE_CODE' ) ) {
				global $sitepress;
				if ( $sitepress ) {
					return $sitepress->get_default_language();
				}
			}
			return get_locale();
		}

		/**
		 * Translate event ID to current language
		 *
		 * @param int    $event_id Event ID
		 * @param string $context  Context (frontend, admin, etc.)
		 * @param bool   $reverse  If true, get default language ID
		 * @return int
		 */
		public function translate_event_id( $event_id, $context = 'frontend', $reverse = false ) {
			if ( ! $event_id || $this->plugin === 'none' ) {
				return $event_id;
			}

			if ( $this->plugin === 'polylang' ) {
				return $this->polylang_translate_event_id( $event_id, $reverse );
			} elseif ( $this->plugin === 'wpml' ) {
				return $this->wpml_translate_event_id( $event_id, $reverse );
			}

			return $event_id;
		}

		/**
		 * Polylang: Translate event ID
		 *
		 * @param int  $event_id
		 * @param bool $to_default
		 * @return int
		 */
		private function polylang_translate_event_id( $event_id, $to_default = false ) {
			if ( ! function_exists( 'pll_get_post_translations' ) ) {
				return $event_id;
			}

			$translations = pll_get_post_translations( $event_id );

			if ( empty( $translations ) || ! is_array( $translations ) ) {
				return $event_id;
			}

			if ( $to_default ) {
				$default_lang = $this->get_default_lang();
				return isset( $translations[ $default_lang ] ) ? $translations[ $default_lang ] : $event_id;
			}

			$current_lang = $this->get_current_lang();
			return isset( $translations[ $current_lang ] ) ? $translations[ $current_lang ] : $event_id;
		}

		/**
		 * WPML: Translate event ID
		 *
		 * @param int  $event_id
		 * @param bool $to_default
		 * @return int
		 */
		private function wpml_translate_event_id( $event_id, $to_default = false ) {
			if ( ! function_exists( 'wpml_object_id_filter' ) ) {
				return $event_id;
			}

			$lang = $to_default ? $this->get_default_lang() : $this->get_current_lang();

			$translated_id = apply_filters(
				'wpml_object_id',
				$event_id,
				'mep_events',
				false,
				$lang
			);

			return $translated_id ? $translated_id : $event_id;
		}

		/**
		 * Get default language event ID (alias for backwards compatibility)
		 *
		 * @param int $event_id
		 * @return int
		 */
		public function get_default_language_event_id( $event_id ) {
			return $this->translate_event_id( $event_id, 'default', true );
		}

		/**
		 * Translate WooCommerce product ID
		 *
		 * @param int $product_id Product ID
		 * @param int $event_id   Event ID (optional, for context)
		 * @return int
		 */
		public function translate_product_id( $product_id, $event_id = 0 ) {
			if ( ! $product_id || $this->plugin === 'none' ) {
				return $product_id;
			}

			if ( $this->plugin === 'polylang' ) {
				return $this->polylang_translate_product_id( $product_id );
			} elseif ( $this->plugin === 'wpml' ) {
				return $this->wpml_translate_product_id( $product_id );
			}

			return $product_id;
		}

		/**
		 * Polylang: Translate product ID
		 *
		 * @param int $product_id
		 * @return int
		 */
		private function polylang_translate_product_id( $product_id ) {
			if ( ! function_exists( 'pll_get_post_translations' ) ) {
				return $product_id;
			}

			$translations = pll_get_post_translations( $product_id );

			if ( empty( $translations ) || ! is_array( $translations ) ) {
				return $product_id;
			}

			$current_lang = $this->get_current_lang();
			return isset( $translations[ $current_lang ] ) ? $translations[ $current_lang ] : $product_id;
		}

		/**
		 * WPML: Translate product ID
		 *
		 * @param int $product_id
		 * @return int
		 */
		private function wpml_translate_product_id( $product_id ) {
			if ( ! function_exists( 'wpml_object_id_filter' ) ) {
				return $product_id;
			}

			$lang = $this->get_current_lang();

			$translated_id = apply_filters(
				'wpml_object_id',
				$product_id,
				'product',
				false,
				$lang
			);

			return $translated_id ? $translated_id : $product_id;
		}

		/**
		 * Filter event query for multilingual
		 *
		 * @param WP_Query $query
		 */
		public function filter_event_query( $query ) {
			if ( ! is_admin() || ! $query->is_main_query() ) {
				return;
			}

			if ( isset( $query->query['post_type'] ) && $query->query['post_type'] === 'mep_events' ) {
				if ( $this->plugin === 'polylang' && function_exists( 'pll_current_language' ) ) {
					// Polylang will handle this automatically
				} elseif ( $this->plugin === 'wpml' ) {
					// WPML handles this automatically for translated post types
				}
			}
		}

		/**
		 * Translate organizer term ID
		 *
		 * @param int $term_id Term ID
		 * @param int $event_id Event ID (optional)
		 * @return int
		 */
		public function translate_organizer_id( $term_id, $event_id = 0 ) {
			if ( ! $term_id || $this->plugin === 'none' ) {
				return $term_id;
			}

			if ( $this->plugin === 'polylang' ) {
				return $this->polylang_translate_term_id( $term_id, 'mep_org' );
			} elseif ( $this->plugin === 'wpml' ) {
				return $this->wpml_translate_term_id( $term_id, 'mep_org' );
			}

			return $term_id;
		}

		/**
		 * Translate category term ID
		 *
		 * @param int $term_id Term ID
		 * @param int $event_id Event ID (optional)
		 * @return int
		 */
		public function translate_category_id( $term_id, $event_id = 0 ) {
			if ( ! $term_id || $this->plugin === 'none' ) {
				return $term_id;
			}

			if ( $this->plugin === 'polylang' ) {
				return $this->polylang_translate_term_id( $term_id, 'mep_cat' );
			} elseif ( $this->plugin === 'wpml' ) {
				return $this->wpml_translate_term_id( $term_id, 'mep_cat' );
			}

			return $term_id;
		}

		/**
		 * Polylang: Translate term ID
		 *
		 * @param int    $term_id
		 * @param string $taxonomy
		 * @return int
		 */
		private function polylang_translate_term_id( $term_id, $taxonomy ) {
			if ( ! function_exists( 'pll_get_term_translations' ) ) {
				return $term_id;
			}

			$translations = pll_get_term_translations( $term_id );

			if ( empty( $translations ) || ! is_array( $translations ) ) {
				return $term_id;
			}

			$current_lang = $this->get_current_lang();
			return isset( $translations[ $current_lang ] ) ? $translations[ $current_lang ] : $term_id;
		}

		/**
		 * WPML: Translate term ID
		 *
		 * @param int    $term_id
		 * @param string $taxonomy
		 * @return int
		 */
		private function wpml_translate_term_id( $term_id, $taxonomy ) {
			if ( ! function_exists( 'wpml_object_id_filter' ) ) {
				return $term_id;
			}

			$lang = $this->get_current_lang();

			$translated_id = apply_filters(
				'wpml_object_id',
				$term_id,
				$taxonomy,
				false,
				$lang
			);

			return $translated_id ? $translated_id : $term_id;
		}

		/**
		 * Get all translations of an event
		 *
		 * @param int $event_id Event ID
		 * @return array Array of event IDs keyed by language code
		 */
		public function get_event_translations( $event_id ) {
			$translations = array();

			if ( $this->plugin === 'polylang' && function_exists( 'pll_get_post_translations' ) ) {
				$translations = pll_get_post_translations( $event_id );
			} elseif ( $this->plugin === 'wpml' && function_exists( 'wpml_get_element_translations' ) ) {
				$wpml_element_id = apply_filters( 'wpml_element_id', $event_id, 'post_mep_events' );
				$translations_raw = wpml_get_element_translations( $wpml_element_id, 'post_mep_events' );
				if ( is_array( $translations_raw ) ) {
					foreach ( $translations_raw as $lang => $translation ) {
						$translations[ $lang ] = $translation->element_id;
					}
				}
			}

			return is_array( $translations ) ? $translations : array();
		}

		/**
		 * Check if an event has translations
		 *
		 * @param int $event_id Event ID
		 * @return bool
		 */
		public function has_translations( $event_id ) {
			$translations = $this->get_event_translations( $event_id );
			return count( $translations ) > 1;
		}

		/**
		 * Get missing translations for an event
		 *
		 * @param int $event_id Event ID
		 * @return array Array of language codes missing translation
		 */
		public function get_missing_translations( $event_id ) {
			$available_langs = $this->get_available_languages();
			$translations = $this->get_event_translations( $event_id );
			$missing = array();

			foreach ( $available_langs as $lang ) {
				if ( ! isset( $translations[ $lang ] ) ) {
					$missing[] = $lang;
				}
			}

			return $missing;
		}

		/**
		 * Get available languages from the plugin
		 *
		 * @return array
		 */
		public function get_available_languages() {
			$languages = array();

			if ( $this->plugin === 'polylang' && function_exists( 'pll_languages_list' ) ) {
				$languages = pll_languages_list( array( 'fields' => 'locale' ) );
				$languages = array_map( function( $locale ) {
					return substr( $locale, 0, 2 );
				}, $languages );
			} elseif ( $this->plugin === 'wpml' && function_exists( 'wpml_get_active_languages' ) ) {
				$raw_langs = wpml_get_active_languages();
				if ( is_array( $raw_langs ) ) {
					$languages = array_keys( $raw_langs );
				}
			}

			return is_array( $languages ) ? $languages : array();
		}

		/**
		 * Add translation status columns to admin list
		 *
		 * @param array $columns
		 * @return array
		 */
		public function add_translation_columns( $columns ) {
			$new_columns = array();
			foreach ( $columns as $key => $value ) {
				$new_columns[ $key ] = $value;
				if ( $key === 'title' ) {
					$new_columns['mep_translations'] = __( 'Translations', 'mage-eventpress' );
				}
			}
			return $new_columns;
		}

		/**
		 * Render translation column content
		 *
		 * @param string $column
		 * @param int    $post_id
		 */
		public function render_translation_column( $column, $post_id ) {
			if ( $column !== 'mep_translations' ) {
				return;
			}

			$translations = $this->get_event_translations( $post_id );
			$current_lang = $this->get_current_lang();
			$available_langs = $this->get_available_languages();

			if ( empty( $translations ) ) {
				echo '<span class="mep-lang-badge mep-lang-none">' . esc_html__( 'No translations', 'mage-eventpress' ) . '</span>';
				return;
			}

			foreach ( $available_langs as $lang ) {
				$has_trans = isset( $translations[ $lang ] );
				$is_current = ( $lang === $current_lang );
				$trans_id = $has_trans ? $translations[ $lang ] : 0;

				$class = $is_current ? 'mep-lang-current' : ( $has_trans ? 'mep-lang-yes' : 'mep-lang-no' );

				echo sprintf(
					'<span class="mep-lang-badge %s" title="%s">%s</span>',
					esc_attr( $class ),
					esc_attr( $this->get_language_name( $lang ) ),
					esc_html( strtoupper( $lang ) )
				);
			}
		}

		/**
		 * Get language name by code
		 *
		 * @param string $lang_code
		 * @return string
		 */
		private function get_language_name( $lang_code ) {
			$names = array(
				'en' => 'English',
				'es' => 'Spanish',
				'fr' => 'French',
				'de' => 'German',
				'it' => 'Italian',
				'nl' => 'Dutch',
				'pt' => 'Portuguese',
				'ru' => 'Russian',
				'zh' => 'Chinese',
				'ja' => 'Japanese',
				'ar' => 'Arabic',
				'hi' => 'Hindi',
				'bn' => 'Bengali',
				'pa' => 'Punjabi',
				'vi' => 'Vietnamese',
				'cs' => 'Czech',
			);

			return isset( $names[ $lang_code ] ) ? $names[ $lang_code ] : strtoupper( $lang_code );
		}

		/**
		 * Sync WooCommerce product translations when event is saved
		 *
		 * @param int     $post_id
		 * @param WP_Post $post
		 * @param bool    $update
		 */
		public function sync_product_translations( $post_id, $post = null, $update = false ) {
			if ( ! $post || $post->post_type !== 'mep_events' ) {
				return;
			}

			if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			$product_id = get_post_meta( $post_id, '_product_id', true );

			if ( ! $product_id ) {
				return;
			}

			$event_translations = $this->get_event_translations( $post_id );

			if ( empty( $event_translations ) || count( $event_translations ) <= 1 ) {
				return;
			}

			// Get product translations
			$product_translations = $this->get_product_translations( $product_id );

			// Sync each translation
			foreach ( $event_translations as $lang => $trans_event_id ) {
				if ( $trans_event_id == $post_id ) {
					continue;
				}

				// Check if translation product exists
				$trans_product_id = isset( $product_translations[ $lang ] ) ? $product_translations[ $lang ] : 0;

				if ( ! $trans_product_id ) {
					// Create new translation for product
					$trans_product_id = $this->create_product_translation( $product_id, $lang );
				}

				if ( $trans_product_id ) {
					// Update the event's product reference
					update_post_meta( $trans_event_id, '_product_id', $trans_product_id );
				}
			}
		}

		/**
		 * Get product translations
		 *
		 * @param int $product_id
		 * @return array
		 */
		private function get_product_translations( $product_id ) {
			if ( $this->plugin === 'polylang' && function_exists( 'pll_get_post_translations' ) ) {
				return pll_get_post_translations( $product_id );
			} elseif ( $this->plugin === 'wpml' && function_exists( 'wpml_get_element_translations' ) ) {
				$wpml_element_id = apply_filters( 'wpml_element_id', $product_id, 'post_product' );
				$translations_raw = wpml_get_element_translations( $wpml_element_id, 'post_product' );
				$translations = array();
				if ( is_array( $translations_raw ) ) {
					foreach ( $translations_raw as $lang => $translation ) {
						$translations[ $lang ] = $translation->element_id;
					}
				}
				return $translations;
			}
			return array();
		}

		/**
		 * Create a translation for a WooCommerce product
		 *
		 * @param int    $product_id Original product ID
		 * @param string $lang       Target language code
		 * @return int New product ID
		 */
		private function create_product_translation( $product_id, $lang ) {
			// Get the original product
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return 0;
			}

			// Create new product as translation
			$new_product = new WC_Product_Simple();
			$new_product->set_status( 'publish' );

			// Copy basic fields (title, description will be handled by translation system)
			$original_title = $product->get_name();
			$new_product->set_name( $original_title . ' (' . strtoupper( $lang ) . ')' );
			$new_product->set_regular_price( $product->get_regular_price() );
			$new_product->set_sale_price( $product->get_sale_price() );
			$new_product->set_sku( $product->get_sku() . '-' . $lang );
			$new_product->set_stock_status( $product->get_stock_status() );
			$new_product->set_virtual( 'yes' );
			$new_product->set_downloadable( 'no' );

			// Copy visibility
			$new_product->set_catalog_visibility( $product->get_catalog_visibility() );

			// Save and get ID
			$new_product_id = $new_product->save();

			if ( $new_product_id ) {
				// Set translation relationship
				if ( $this->plugin === 'polylang' && function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' ) ) {
					pll_set_post_language( $new_product_id, $lang );

					$translations = pll_get_post_translations( $product_id );
					$translations[ $lang ] = $new_product_id;
					pll_save_post_translations( $translations );
				} elseif ( $this->plugin === 'wpml' && function_exists( 'wpml_set_element_language_details' ) ) {
					global $wpdb;

					$original_element_id = apply_filters( 'wpml_element_id', $product_id, 'post_product' );

					$wpdb->insert(
						$wpdb->prefix . 'icl_translations',
						array(
							'element_id' => $new_product_id,
							'element_type' => 'post_product',
							'trid' => $original_element_id,
							'language_code' => $lang,
						)
					);
				}
			}

			return $new_product_id ? $new_product_id : 0;
		}

		/**
		 * Add translation link to row actions
		 *
		 * @param array   $actions
		 * @param WP_Post $post
		 * @return array
		 */
		public function add_translate_link( $actions, $post ) {
			if ( $post->post_type !== 'mep_events' ) {
				return $actions;
			}

			if ( ! $this->has_translations( $post->ID ) ) {
				return $actions;
			}

			$missing = $this->get_missing_translations( $post->ID );

			if ( ! empty( $missing ) ) {
				$actions['mep_translate'] = sprintf(
					'<a href="#" class="mep-translate-link" data-event-id="%d" title="%s">%s</a>',
					esc_attr( $post->ID ),
					esc_attr__( 'Manage translations', 'mage-eventpress' ),
					esc_html__( 'Translations', 'mage-eventpress' )
				);
			}

			return $actions;
		}

		/**
		 * AJAX: Get translations for an event
		 */
		public function ajax_get_translations() {
			check_ajax_referer( 'mep-ajax-nonce', 'nonce' );

			$event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;

			if ( ! $event_id ) {
				wp_send_json_error( array( 'message' => __( 'Invalid event ID', 'mage-eventpress' ) ) );
			}

			$translations = $this->get_event_translations( $event_id );
			$available_langs = $this->get_available_languages();
			$missing = $this->get_missing_translations( $event_id );

			wp_send_json_success( array(
				'translations' => $translations,
				'available_langs' => $available_langs,
				'missing' => $missing,
				'has_translations' => $this->has_translations( $event_id ),
			) );
		}

		/**
		 * AJAX: Sync product translation manually
		 */
		public function ajax_sync_product_translation() {
			check_ajax_referer( 'mep-ajax-nonce', 'nonce' );

			$event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;
			$lang = isset( $_POST['lang'] ) ? sanitize_text_field( $_POST['lang'] ) : '';

			if ( ! $event_id || ! $lang ) {
				wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'mage-eventpress' ) ) );
			}

			$product_id = get_post_meta( $event_id, '_product_id', true );

			if ( ! $product_id ) {
				wp_send_json_error( array( 'message' => __( 'No product associated with this event', 'mage-eventpress' ) ) );
			}

			$new_product_id = $this->create_product_translation( $product_id, $lang );

			if ( $new_product_id ) {
				// Find the translated event ID
				$translations = $this->get_event_translations( $event_id );
				$trans_event_id = isset( $translations[ $lang ] ) ? $translations[ $lang ] : 0;

				if ( $trans_event_id ) {
					update_post_meta( $trans_event_id, '_product_id', $new_product_id );
				}

				wp_send_json_success( array(
					'message' => __( 'Product translation created successfully', 'mage-eventpress' ),
					'product_id' => $new_product_id,
				) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to create product translation', 'mage-eventpress' ) ) );
			}
		}

		/**
		 * Translate event URL
		 *
		 * @param string $url
		 * @param int    $event_id
		 * @return string
		 */
		public function translate_event_url( $url, $event_id ) {
			if ( $this->plugin === 'none' || ! $event_id ) {
				return $url;
			}

			$translated_id = $this->translate_event_id( $event_id );

			if ( $translated_id && $translated_id !== $event_id ) {
				return get_permalink( $translated_id );
			}

			return $url;
		}

		/**
		 * Add translation meta box in admin
		 *
		 * @param string $post_type
		 * @param WP_Post $post
		 */
		public function add_translation_meta_box( $post_type, $post ) {
			if ( $post_type !== 'mep_events' || $this->plugin === 'none' ) {
				return;
			}

			add_meta_box(
				'mep_translation_status',
				__( 'Translation Status', 'mage-eventpress' ),
				array( $this, 'render_translation_meta_box' ),
				'mep_events',
				'side',
				'low'
			);
		}

		/**
		 * Render translation meta box content
		 *
		 * @param WP_Post $post
		 */
		public function render_translation_meta_box( $post ) {
			$translations = $this->get_event_translations( $post->ID );
			$available_langs = $this->get_available_languages();
			$current_lang = $this->get_current_lang();

			echo '<div class="mep-translation-status-box">';

			if ( empty( $available_langs ) ) {
				echo '<p>' . esc_html__( 'No languages configured.', 'mage-eventpress' ) . '</p>';
				echo '</div>';
				return;
			}

			echo '<table class="mep-translation-table">';
			echo '<thead><tr><th>' . esc_html__( 'Language', 'mage-eventpress' ) . '</th>';
			echo '<th>' . esc_html__( 'Status', 'mage-eventpress' ) . '</th>';
			echo '<th>' . esc_html__( 'Action', 'mage-eventpress' ) . '</th></tr></thead>';
			echo '<tbody>';

			foreach ( $available_langs as $lang ) {
				$has_trans = isset( $translations[ $lang ] );
				$is_current = ( $lang === $current_lang );
				$trans_id = $has_trans ? $translations[ $lang ] : 0;

				echo '<tr>';
				echo '<td><strong>' . esc_html( $this->get_language_name( $lang ) ) . '</strong> (' . esc_html( strtoupper( $lang ) ) . ')</td>';
				echo '<td>';

				if ( $is_current ) {
					echo '<span class="mep-status-badge current">' . esc_html__( 'Current', 'mage-eventpress' ) . '</span>';
				} elseif ( $has_trans ) {
					echo '<span class="mep-status-badge translated">' . esc_html__( 'Translated', 'mage-eventpress' ) . '</span>';
				} else {
					echo '<span class="mep-status-badge missing">' . esc_html__( 'Missing', 'mage-eventpress' ) . '</span>';
				}

				echo '</td>';
				echo '<td>';

				if ( $is_current ) {
					echo '-';
				} elseif ( $has_trans ) {
					echo '<a href="' . esc_url( get_edit_post_link( $trans_id ) ) . '" class="button button-small">' . esc_html__( 'Edit', 'mage-eventpress' ) . '</a>';
				} else {
					echo '<button type="button" class="button button-small mep-create-translation" data-lang="' . esc_attr( $lang ) . '" data-event="' . esc_attr( $post->ID ) . '">' . esc_html__( 'Create', 'mage-eventpress' ) . '</button>';
				}

				echo '</td>';
				echo '</tr>';
			}

			echo '</tbody>';
			echo '</table>';

			// Sync status
			$product_id = get_post_meta( $post->ID, '_product_id', true );
			if ( $product_id ) {
				$product_translations = $this->get_product_translations( $product_id );
				$missing_products = 0;

				foreach ( $translations as $lang => $trans_event_id ) {
					if ( $lang !== $current_lang && ! isset( $product_translations[ $lang ] ) ) {
						$missing_products++;
					}
				}

				if ( $missing_products > 0 ) {
					echo '<p class="mep-sync-warning">';
					echo '<span class="dashicons dashicons-warning"></span>';
					echo sprintf( esc_html__( '%d product translation(s) missing. ', 'mage-eventpress' ), $missing_products );
					echo '<button type="button" class="button button-small mep-sync-products" data-event="' . esc_attr( $post->ID ) . '">' . esc_html__( 'Sync Now', 'mage-eventpress' ) . '</button>';
					echo '</p>';
				}
			}

			echo '</div>';

			// Add inline styles
			echo '<style>
				.mep-translation-status-box { padding: 10px; }
				.mep-translation-table { width: 100%; border-collapse: collapse; }
				.mep-translation-table th, .mep-translation-table td { padding: 8px 5px; text-align: left; border-bottom: 1px solid #eee; }
				.mep-status-badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
				.mep-status-badge.current { background: #2271b1; color: #fff; }
				.mep-status-badge.translated { background: #00a32a; color: #fff; }
				.mep-status-badge.missing { background: #d63638; color: #fff; }
				.mep-sync-warning { margin-top: 15px; padding: 10px; background: #fffbeb; border-left: 3px solid #f59e0b; }
				.mep-sync-warning .dashicons { vertical-align: middle; margin-right: 5px; }
			</style>';

			// Add AJAX script
			echo '<script>
				jQuery(document).ready(function($) {
					$(".mep-create-translation").on("click", function() {
						var btn = $(this);
						var event_id = btn.data("event");
						var lang = btn.data("lang");

						btn.prop("disabled", true).text("' . esc_js__( 'Creating...', 'mage-eventpress' ) . '");

						$.ajax({
							url: ajaxurl,
							type: "POST",
							data: {
								action: "mep_create_event_translation",
								event_id: event_id,
								lang: lang,
								nonce: "' . esc_js( wp_create_nonce( 'mep-ajax-nonce' ) ) . '"
							},
							success: function(response) {
								if (response.success) {
									location.reload();
								} else {
									alert(response.data.message || "' . esc_js__( 'Failed to create translation', 'mage-eventpress' ) . '");
									btn.prop("disabled", false).text("' . esc_js__( 'Create', 'mage-eventpress' ) . '");
								}
							}
						});
					});

					$(".mep-sync-products").on("click", function() {
						var btn = $(this);
						var event_id = btn.data("event");

						btn.prop("disabled", true).text("' . esc_js__( 'Syncing...', 'mage-eventpress' ) . '");

						$.ajax({
							url: ajaxurl,
							type: "POST",
							data: {
								action: "mep_sync_all_product_translations",
								event_id: event_id,
								nonce: "' . esc_js( wp_create_nonce( 'mep-ajax-nonce' ) ) . '"
							},
							success: function(response) {
								if (response.success) {
									location.reload();
								} else {
									alert(response.data.message || "' . esc_js__( 'Sync failed', 'mage-eventpress' ) . '");
									btn.prop("disabled", false).text("' . esc_js__( 'Sync Now', 'mage-eventpress' ) . '");
								}
							}
						});
					});
				});
			</script>';
		}

		/**
		 * Filter attendees by language
		 *
		 * @param array $attendees
		 * @param int   $event_id
		 * @param array $args
		 * @return array
		 */
		public function filter_attendees_by_language( $attendees, $event_id, $args = array() ) {
			if ( $this->plugin === 'none' ) {
				return $attendees;
			}

			// For attendees, we always want to show those from the original event
			// when looking at a translated version
			$default_event_id = $this->get_default_language_event_id( $event_id );

			if ( $default_event_id !== $event_id ) {
				// This is a translated event, filter attendees to original event
				$args['event_id'] = $default_event_id;
			}

			return $attendees;
		}

		/**
		 * Get the original (default language) event ID from any translated event
		 *
		 * @param int $event_id
		 * @return int
		 */
		public function get_original_event_id( $event_id ) {
			return $this->translate_event_id( $event_id, 'original', true );
		}

		/**
		 * Check if we're on a translated version of an event
		 *
		 * @param int $event_id
		 * @return bool
		 */
		public function is_translated_event( $event_id ) {
			$original_id = $this->get_original_event_id( $event_id );
			return $original_id !== $event_id;
		}

		/**
		 * Enqueue admin scripts for translation management
		 */
		public function enqueue_admin_scripts() {
			if ( ! is_admin() ) {
				return;
			}

			$screen = get_current_screen();

			if ( ! $screen || $screen->post_type !== 'mep_events' ) {
				return;
			}

			wp_enqueue_style(
				'mep-multilingual-admin',
				MPWEM_PLUGIN_URL . '/assets/admin/mep-multilingual.css',
				array(),
				MPWEM_VERSION
			);

			wp_enqueue_script(
				'mep-multilingual-admin',
				MPWEM_PLUGIN_URL . '/assets/admin/mep-multilingual.js',
				array( 'jquery' ),
				MPWEM_VERSION,
				true
			);

			wp_localize_script(
				'mep-multilingual-admin',
				'mep_multilingual',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'mep-multilingual-nonce' ),
					'strings' => array(
						'syncing' => __( 'Syncing...', 'mage-eventpress' ),
						'sync_complete' => __( 'Sync Complete', 'mage-eventpress' ),
						'creating' => __( 'Creating...', 'mage-eventpress' ),
						'translation_created' => __( 'Translation Created', 'mage-eventpress' ),
					),
				)
			);
		}

		/**
		 * Create a new event translation
		 *
		 * @param int    $original_event_id Original event ID
		 * @param string $lang              Target language
		 * @return int|false New event ID or false on failure
		 */
		public function create_event_translation( $original_event_id, $lang ) {
			$original_event = get_post( $original_event_id );

			if ( ! $original_event || $original_event->post_type !== 'mep_events' ) {
				return false;
			}

			// Create new event post
			$new_event_id = wp_insert_post( array(
				'post_type' => 'mep_events',
				'post_status' => 'draft',
				'post_title' => $original_event->post_title . ' (' . strtoupper( $lang ) . ')',
				'post_content' => $original_event->post_content,
			) );

			if ( is_wp_error( $new_event_id ) || ! $new_event_id ) {
				return false;
			}

			// Copy all meta fields
			$meta_fields = get_post_meta( $original_event_id );
			foreach ( $meta_fields as $key => $value ) {
				if ( is_array( $value ) && count( $value ) === 1 ) {
					update_post_meta( $new_event_id, $key, maybe_unserialize( $value[0] ) );
				} else {
					update_post_meta( $new_event_id, $key, $value );
				}
			}

			// Set language for new post
			if ( $this->plugin === 'polylang' && function_exists( 'pll_set_post_language' ) ) {
				pll_set_post_language( $new_event_id, $lang );

				// Get existing translations and add new one
				$translations = pll_get_post_translations( $original_event_id );
				$translations[ $lang ] = $new_event_id;
				pll_save_post_translations( $translations );
			} elseif ( $this->plugin === 'wpml' ) {
				global $wpdb;

				$original_element_id = apply_filters( 'wpml_element_id', $original_event_id, 'post_mep_events' );

				// Get trid from original
				$trid = $wpdb->get_var( $wpdb->prepare(
					"SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type = 'post_mep_events'",
					$original_event_id
				) );

				if ( $trid ) {
					$wpdb->insert(
						$wpdb->prefix . 'icl_translations',
						array(
							'element_id' => $new_event_id,
							'element_type' => 'post_mep_events',
							'trid' => $trid,
							'language_code' => $lang,
						)
					);
				}
			}

			// Copy product if exists
			$product_id = get_post_meta( $original_event_id, '_product_id', true );
			if ( $product_id ) {
				$new_product_id = $this->create_product_translation( $product_id, $lang );
				if ( $new_product_id ) {
					update_post_meta( $new_event_id, '_product_id', $new_product_id );
				}
			}

			return $new_event_id;
		}

		/**
		 * AJAX: Create event translation
		 */
		public function ajax_create_event_translation() {
			check_ajax_referer( 'mep-ajax-nonce', 'nonce' );

			$event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;
			$lang = isset( $_POST['lang'] ) ? sanitize_text_field( $_POST['lang'] ) : '';

			if ( ! $event_id || ! $lang ) {
				wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'mage-eventpress' ) ) );
			}

			$new_event_id = $this->create_event_translation( $event_id, $lang );

			if ( $new_event_id ) {
				wp_send_json_success( array(
					'message' => __( 'Translation created successfully', 'mage-eventpress' ),
					'event_id' => $new_event_id,
					'edit_url' => get_edit_post_link( $new_event_id, 'api' ),
				) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to create translation', 'mage-eventpress' ) ) );
			}
		}

		/**
		 * AJAX: Sync all product translations
		 */
		public function ajax_sync_all_product_translations() {
			check_ajax_referer( 'mep-ajax-nonce', 'nonce' );

			$event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;

			if ( ! $event_id ) {
				wp_send_json_error( array( 'message' => __( 'Invalid event ID', 'mage-eventpress' ) ) );
			}

			$this->sync_product_translations( $event_id );

			wp_send_json_success( array(
				'message' => __( 'All product translations synced', 'mage-eventpress' ),
			) );
		}

		/**
		 * AJAX: Bulk sync all products from settings page
		 */
		public function ajax_bulk_sync_all_products() {
			check_ajax_referer( 'mep-multilingual-nonce', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied', 'mage-eventpress' ) ) );
			}

			$sync_count = $this->bulk_sync_all_product_translations();

			wp_send_json_success( array(
				'message' => sprintf( __( 'Successfully synced %d products', 'mage-eventpress' ), $sync_count ),
				'synced_count' => $sync_count,
			) );
		}

		/**
		 * Bulk sync all product translations for all events
		 *
		 * @return int Number of products synced
		 */
		public function bulk_sync_all_product_translations() {
			$synced_count = 0;

			$events = get_posts( array(
				'post_type' => 'mep_events',
				'posts_per_page' => -1,
				'post_status' => 'any',
				'fields' => 'ids',
			) );

			foreach ( $events as $event_id ) {
				$product_id = get_post_meta( $event_id, '_product_id', true );

				if ( ! $product_id ) {
					continue;
				}

				$translations = $this->get_event_translations( $event_id );

				if ( empty( $translations ) || count( $translations ) <= 1 ) {
					continue;
				}

				$product_translations = $this->get_product_translations( $product_id );

				foreach ( $translations as $lang => $trans_event_id ) {
					if ( $trans_event_id == $event_id ) {
						continue;
					}

					$trans_product_id = isset( $product_translations[ $lang ] ) ? $product_translations[ $lang ] : 0;

					if ( ! $trans_product_id ) {
						$new_product_id = $this->create_product_translation( $product_id, $lang );

						if ( $new_product_id ) {
							update_post_meta( $trans_event_id, '_product_id', $new_product_id );
							$synced_count++;
						}
					}
				}
			}

			return $synced_count;
		}

		/**
		 * Register the mep_events post type with WPML/Polylang
		 * This ensures proper translation support
		 */
		public function register_multilingual_plugin_settings() {
			if ( $this->plugin === 'none' ) {
				return;
			}

			// WPML: Register custom post type for translation
			add_filter( 'wpml_get_job_synchronization_data', array( $this, 'wpml_sync_jobs' ), 10, 2 );
			add_filter( 'wpml_get_setting', array( $this, 'wpml_custom_post_types_to_translate' ), 10, 2 );

			// Polylang: Register taxonomy for translation
			add_filter( 'pll_get_post_types', array( $this, 'polylang_register_post_types' ), 10, 2 );
			add_filter( 'pll_get_taxonomies', array( $this, 'polylang_register_taxonomies' ), 10, 2 );

			// Add custom translation management for event custom fields
			add_filter( 'wpml_config_blacklist', array( $this, 'wpml_config_blacklist' ) );

			// WPML: Make sure our custom fields are translatable
			add_filter( 'wpml_config_array', array( $this, 'wpml_config_array' ) );
		}

		/**
		 * Register mep_events post type with Polylang
		 *
		 * @param array $post_types
		 * @param bool  $is_settings
		 * @return array
		 */
		public function polylang_register_post_types( $post_types, $is_settings ) {
			if ( ! isset( $post_types['mep_events'] ) ) {
				$post_types['mep_events'] = 'mep_events';
			}
			return $post_types;
		}

		/**
		 * Register event taxonomies with Polylang
		 *
		 * @param array $taxonomies
		 * @param bool  $is_settings
		 * @return array
		 */
		public function polylang_register_taxonomies( $taxonomies, $is_settings ) {
			$event_taxonomies = array( 'mep_org', 'mep_cat' );

			foreach ( $event_taxonomies as $tax ) {
				if ( ! isset( $taxonomies[ $tax ] ) ) {
					$taxonomies[ $tax ] = $tax;
				}
			}

			return $taxonomies;
		}

		/**
		 * Add custom post type to WPML translation management
		 *
		 * @param mixed $value
		 * @param string $setting
		 * @return mixed
		 */
		public function wpml_custom_post_types_to_translate( $value, $setting ) {
			if ( $setting === 'custom_posts_sync_option' ) {
				$value['mep_events'] = 1;
			}
			return $value;
		}

		/**
		 * Add custom fields to WPML config to exclude from translation
		 *
		 * @param array $blacklist
		 * @return array
		 */
		public function wpml_config_blacklist( $blacklist ) {
			$blacklist[] = '_product_id';
			$blacklist[] = 'event_ticket_id';
			return $blacklist;
		}

		/**
		 * Generate WPML configuration for custom fields
		 *
		 * @param array $config_array
		 * @return array
		 */
		public function wpml_config_array( $config_array ) {
			return $config_array;
		}

		/**
		 * Enqueue admin scripts only on event pages
		 */
		public function enqueue_admin_scripts( $hook ) {
			// Only load on event admin pages
			if ( strpos( $hook, 'mep_events' ) === false && strpos( $hook, 'mep_event' ) === false ) {
				// Also check for settings page
				if ( $hook !== 'events_page_mep_event_settings_page' ) {
					return;
				}
			}

			// Enqueue styles and scripts
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style(
				'mep-multilingual-admin',
				MPWEM_PLUGIN_URL . '/assets/admin/mep-multilingual.css',
				array(),
				time()
			);

			wp_enqueue_script(
				'mep-multilingual-admin',
				MPWEM_PLUGIN_URL . '/assets/admin/mep-multilingual.js',
				array( 'jquery' ),
				time(),
				true
			);

			$language_names = array(
				'en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German',
				'it' => 'Italian', 'nl' => 'Dutch', 'pt' => 'Portuguese', 'ru' => 'Russian',
				'zh' => 'Chinese', 'ja' => 'Japanese', 'ar' => 'Arabic', 'hi' => 'Hindi',
				'bn' => 'Bengali', 'pa' => 'Punjabi', 'vi' => 'Vietnamese', 'cs' => 'Czech',
				'pl' => 'Polish', 'sv' => 'Swedish', 'da' => 'Danish', 'fi' => 'Finnish',
				'no' => 'Norwegian', 'tr' => 'Turkish', 'ko' => 'Korean', 'th' => 'Thai',
				'id' => 'Indonesian', 'ms' => 'Malay', 'el' => 'Greek', 'he' => 'Hebrew',
				'uk' => 'Ukrainian', 'ro' => 'Romanian', 'hu' => 'Hungarian', 'sk' => 'Slovak',
				'bg' => 'Bulgarian', 'hr' => 'Croatian', 'sl' => 'Slovenian', 'et' => 'Estonian',
				'lv' => 'Latvian', 'lt' => 'Lithuanian', 'sr' => 'Serbian', 'ca' => 'Catalan',
				'gl' => 'Galician', 'eu' => 'Basque'
			);

			wp_localize_script(
				'mep-multilingual-admin',
				'mep_multilingual',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'mep-multilingual-nonce' ),
					'plugin' => $this->plugin,
					'current_lang' => $this->get_current_lang(),
					'available_langs' => $this->get_available_languages(),
					'strings' => array(
						'syncing' => __( 'Syncing...', 'mage-eventpress' ),
						'sync_complete' => __( 'Sync Complete', 'mage-eventpress' ),
						'creating' => __( 'Creating...', 'mage-eventpress' ),
						'translation_created' => __( 'Translation Created', 'mage-eventpress' ),
						'translations' => __( 'Translations', 'mage-eventpress' ),
						'no_languages' => __( 'No languages configured in your multilingual plugin.', 'mage-eventpress' ),
						'configure_now' => __( 'Please configure languages in your multilingual plugin settings first.', 'mage-eventpress' ),
					),
					'language_names' => $language_names,
				)
			);
		}

		/**
		 * WPML: Sync translation jobs
		 *
		 * @param array $data
		 * @param int   $job_id
		 * @return array
		 */
		public function wpml_sync_jobs( $data, $job_id ) {
			return $data;
		}

		/**
		 * Get translation status summary for all events
		 *
		 * @return array
		 */
		public function get_translation_status_summary() {
			$summary = array(
				'total_events' => 0,
				'events_with_translations' => 0,
				'events_without_translations' => 0,
				'total_products' => 0,
				'products_synced' => 0,
				'products_need_sync' => 0,
			);

			if ( $this->plugin === 'none' ) {
				return $summary;
			}

			$events = get_posts( array(
				'post_type' => 'mep_events',
				'posts_per_page' => -1,
				'post_status' => 'any',
				'fields' => 'ids',
			) );

			$summary['total_events'] = count( $events );

			foreach ( $events as $event_id ) {
				$product_id = get_post_meta( $event_id, '_product_id', true );

				if ( $product_id ) {
					$summary['total_products']++;
				}

				$translations = $this->get_event_translations( $event_id );

				if ( ! empty( $translations ) && count( $translations ) > 1 ) {
					$summary['events_with_translations']++;

					if ( $product_id ) {
						$product_translations = $this->get_product_translations( $product_id );
						$sync_needed = false;

						foreach ( $translations as $lang => $trans_event_id ) {
							if ( $trans_event_id != $event_id && ! isset( $product_translations[ $lang ] ) ) {
								$sync_needed = true;
								break;
							}
						}

						if ( $sync_needed ) {
							$summary['products_need_sync']++;
						} else {
							$summary['products_synced']++;
						}
					}
				} else {
					$summary['events_without_translations']++;
				}
			}

			return $summary;
		}

		/**
		 * Add multilingual settings section to admin
		 */
		public function add_settings_section( $sections ) {
			$sections['multilingual_settings_sec'] = array(
				'id' => 'multilingual_settings_sec',
				'title' => '<i class="mi mi-translate"></i>' . __( 'Multilingual Settings', 'mage-eventpress' ),
			);

			return $sections;
		}

		/**
		 * Add multilingual settings fields
		 */
		public function add_settings_fields( $fields ) {
			$status_summary = $this->get_translation_status_summary();

			$fields['multilingual_settings_sec'] = apply_filters( 'mep_settings_multilingual_arr', array(
				array(
					'name'    => 'mep_ml_status',
					'label'   => __( 'Translation Status', 'mage-eventpress' ),
					'desc'    => $this->get_translation_status_html( $status_summary ),
					'type'    => 'html',
				),
				array(
					'name'    => 'mep_bulk_sync_products',
					'label'   => __( 'Sync All Products', 'mage-eventpress' ),
					'desc'    => __( 'Click this button to sync product translations for all events. This will create missing product translations for all events that have multilingual translations.', 'mage-eventpress' ),
					'type'    => 'html',
					'html'    => '<button type="button" id="mep_bulk_sync_all_products" class="button button-primary">' . __( 'Sync All Products', 'mage-eventpress' ) . '</button>' .
					             '<span id="mep_sync_status" style="margin-left: 10px; display: none;"></span>',
				),
				array(
					'name'    => 'mep_ml_info',
					'label'   => __( 'Setup Guide', 'mage-eventpress' ),
					'desc'    => $this->get_setup_guide_html(),
					'type'    => 'html',
				),
				array(
					'name'    => 'mep_auto_sync_translations',
					'label'   => __( 'Auto-Sync on Event Save', 'mage-eventpress' ),
					'desc'    => __( 'Automatically sync product translations when an event is saved.', 'mage-eventpress' ),
					'type'    => 'select',
					'default' => 'yes',
					'options' => array(
						'yes' => __( 'Yes', 'mage-eventpress' ),
						'no'  => __( 'No', 'mage-eventpress' ),
					),
				),
				array(
					'name'    => 'mep_show_translation_column',
					'label'   => __( 'Show Translation Column', 'mage-eventpress' ),
					'desc'    => __( 'Show translation status column in the events list table.', 'mage-eventpress' ),
					'type'    => 'select',
					'default' => 'yes',
					'options' => array(
						'yes' => __( 'Yes', 'mage-eventpress' ),
						'no'  => __( 'No', 'mage-eventpress' ),
					),
				),
			) );

			return $fields;
		}

		/**
		 * Get translation status HTML for settings
		 *
		 * @param array $summary
		 * @return string
		 */
		private function get_translation_status_html( $summary ) {
			$html = '<div class="mep-ml-status-panel">';
			$html .= '<table style="width: 100%;">';
			$html .= '<tr><td><strong>' . __( 'Total Events', 'mage-eventpress' ) . '</strong></td><td>' . esc_html( $summary['total_events'] ) . '</td></tr>';
			$html .= '<tr><td><strong>' . __( 'Events with Translations', 'mage-eventpress' ) . '</strong></td><td style="color: #00a32a;">' . esc_html( $summary['events_with_translations'] ) . '</td></tr>';
			$html .= '<tr><td><strong>' . __( 'Events without Translations', 'mage-eventpress' ) . '</strong></td><td style="color: #d63638;">' . esc_html( $summary['events_without_translations'] ) . '</td></tr>';
			$html .= '<tr><td colspan="2"><hr></td></tr>';
			$html .= '<tr><td><strong>' . __( 'Total Products', 'mage-eventpress' ) . '</strong></td><td>' . esc_html( $summary['total_products'] ) . '</td></tr>';
			$html .= '<tr><td><strong>' . __( 'Products Synced', 'mage-eventpress' ) . '</strong></td><td style="color: #00a32a;">' . esc_html( $summary['products_synced'] ) . '</td></tr>';
			$html .= '<tr><td><strong>' . __( 'Products Need Sync', 'mage-eventpress' ) . '</strong></td><td style="color: #d63638;">' . esc_html( $summary['products_need_sync'] ) . '</td></tr>';
			$html .= '</table>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Get setup guide HTML
		 *
		 * @return string
		 */
		private function get_setup_guide_html() {
			$plugin = $this->plugin;
			$html = '<div class="mep-ml-setup-guide">';

			if ( $plugin === 'polylang' ) {
				$html .= '<h4>' . __( 'Polylang Setup', 'mage-eventpress' ) . '</h4>';
				$html .= '<ol>';
				$html .= '<li>' . __( 'Go to <strong>Languages → Languages</strong> and add your desired languages.', 'mage-eventpress' ) . '</li>';
				$html .= '<li>' . __( 'Go to <strong>Languages → Settings</strong> and ensure "mep_events" is checked in the "Custom post types" section.', 'mage-eventpress' ) . '</li>';
				$html .= '<li>' . __( 'Go to <strong>Languages → Settings</strong> and ensure "mep_org" and "mep_cat" are checked in the "Custom taxonomies" section.', 'mage-eventpress' ) . '</li>';
				$html .= '<li>' . __( 'Edit your event and click "Duplicate" to create translations.', 'mage-eventpress' ) . '</li>';
				$html .= '<li>' . __( 'The product translations will be automatically synced when you save.', 'mage-eventpress' ) . '</li>';
				$html .= '</ol>';
			} elseif ( $plugin === 'wpml' ) {
				$html .= '<h4>' . __( 'WPML Setup', 'mage-eventpress' ) . '</h4>';
				$html .= '<ol>';
				$html .= '<li>' . __( 'Go to <strong>WPML → Languages</strong> and add your desired languages.', 'mage-eventpress' ) . '</li>';
				$html .= '<li>' . __( 'Go to <strong>WPML → Translation Management</strong> and ensure "mep_events" is set to "Translatable - use translation if available or fall back to default language".', 'mage-eventpress' ) . '</li>';
				$html .= '<li>' . __( 'Go to <strong>WPML → Settings</strong> and ensure the custom post types and taxonomies are configured for translation.', 'mage-eventpress' ) . '</li>';
				$html .= '<li>' . __( 'Use the WPML translation editor to create translations for your events.', 'mage-eventpress' ) . '</li>';
				$html .= '<li>' . __( 'Run "Sync All Products" to ensure all product translations are linked.', 'mage-eventpress' ) . '</li>';
				$html .= '</ol>';
			} else {
				$html .= '<p>' . __( 'Please select a multilingual plugin in the General Settings tab.', 'mage-eventpress' ) . '</p>';
			}

			$html .= '<p><strong>' . __( 'Note:', 'mage-eventpress' ) . '</strong> ';
			$html .= __( 'WooCommerce products associated with events will be automatically translated when you create event translations.', 'mage-eventpress' ) . '</p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Register bulk actions for events list
		 *
		 * @param array $actions
		 * @return array
		 */
		public function register_bulk_actions( $actions ) {
			if ( $this->plugin === 'none' ) {
				return $actions;
			}

			$actions['mep_sync_product_translations'] = __( 'Sync Product Translations', 'mage-eventpress' );
			return $actions;
		}

		/**
		 * Handle bulk action for syncing product translations
		 *
		 * @param string $redirect_to Redirect URL
		 * @param string $action      Action name
		 * @param array  $post_ids   Post IDs
		 * @return string
		 */
		public function handle_bulk_sync_products( $redirect_to, $action, $post_ids ) {
			if ( $action !== 'mep_sync_product_translations' ) {
				return $redirect_to;
			}

			$synced_count = 0;

			foreach ( $post_ids as $event_id ) {
				$product_id = get_post_meta( $event_id, '_product_id', true );

				if ( ! $product_id ) {
					continue;
				}

				$translations = $this->get_event_translations( $event_id );

				if ( empty( $translations ) || count( $translations ) <= 1 ) {
					continue;
				}

				$product_translations = $this->get_product_translations( $product_id );

				foreach ( $translations as $lang => $trans_event_id ) {
					if ( $trans_event_id == $event_id ) {
						continue;
					}

					$trans_product_id = isset( $product_translations[ $lang ] ) ? $product_translations[ $lang ] : 0;

					if ( ! $trans_product_id ) {
						$new_product_id = $this->create_product_translation( $product_id, $lang );

						if ( $new_product_id ) {
							update_post_meta( $trans_event_id, '_product_id', $new_product_id );
							$synced_count++;
						}
					}
				}
			}

			return add_query_arg( 'mep_synced_count', $synced_count, $redirect_to );
		}

		/**
		 * Display admin notice after bulk sync
		 */
		public function admin_notice_after_bulk_sync() {
			if ( ! isset( $_GET['mep_synced_count'] ) ) {
				return;
			}

			$count = (int) $_GET['mep_synced_count'];
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %d: number of products synced */
						_n(
							'Product translation synced for %d event.',
							'Product translations synced for %d events.',
							$count,
							'mage-eventpress'
						),
						$count
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get the multilingual helper instance
	 *
	 * @return MPWEM_Multilingual
	 */
	function MPWEM_ML() {
		return MPWEM_Multilingual::instance();
	}

	// Initialize
	MPWEM_Multilingual::instance();

	/**
	 * Backwards compatibility wrapper for old function
	 */
	if ( ! function_exists( 'mep_get_default_lang_event_id' ) ) {
		function mep_get_default_lang_event_id( $event_id ) {
			return MPWEM_ML()->get_default_language_event_id( $event_id );
		}
	}
}