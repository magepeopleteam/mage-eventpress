<?php
/**
 * WPML and Multilingual Synchronization Helper Class
 * 
 * Centralizes critical event settings and handles synchronization between 
 * translations to prevent overbooking and inconsistencies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'MPWEM_WPML_Sync' ) ) {
	class MPWEM_WPML_Sync {

		/**
		 * List of settings that should be shared across all translations
		 */
		public static $shared_settings = [
			'mep_event_ticket_type',    // Ticket types (structure/prices/quantities)
			'event_start_date',         // Start date
			'event_end_date',           // End date
			'event_start_datetime',     // Start date time
			'event_end_datetime',       // End date time
			'mep_event_more_date',      // Recurring dates (Critical!)
			'mep_location_venue',      // Venue name
			'mep_street',              // Street
			'mep_city',                // City
			'mep_state',               // State
			'mep_zip',                 // Zip
			'mep_country',             // Country
			'mep_events_extra_prices', // Extra services (structure/prices)
			'mep_buffer_time',         // Buffer time
			'mep_available_seat',      // Seat display setting
		];

		/**
		 * Settings that contain translatable text fields even in shared settings
		 */
		public static $translatable_fields = [
			'mep_event_ticket_type'   => [ 'option_name_t', 'option_details_t' ],
			'mep_events_extra_prices' => [ 'option_name' ],
		];

		public static function init() {
			// Hook into save_post to sync settings
			add_action( 'save_post_mep_events', [ __CLASS__, 'handle_event_save' ], 20, 3 );
			
			// Admin UI notice
			add_action( 'admin_notices', [ __CLASS__, 'show_translation_notice' ] );
		}

		/**
		 * Check if a setting key is shared across translations
		 */
		public static function is_shared_setting( $key ) {
			return in_array( $key, self::$shared_settings );
		}

		/**
		 * Handle event save and trigger sync
		 */
		public static function handle_event_save( $post_id, $post, $update ) {
			if ( ! $update || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
				return;
			}

			// Only run for mep_events
			if ( $post->post_type !== 'mep_events' ) {
				return;
			}

			// Avoid infinite recursion
			static $syncing = false;
			if ( $syncing ) {
				return;
			}

			$default_event_id = mep_get_default_lang_event_id( $post_id );

			// Only sync FROM the default language version to others
			if ( (int) $post_id === (int) $default_event_id ) {
				$syncing = true;
				self::sync_all_settings( $post_id );
				$syncing = false;
			}
		}

		/**
		 * Sync all shared settings from original event to translations
		 */
		public static function sync_all_settings( $original_id ) {
			$translation_ids = mep_get_all_translation_event_ids( $original_id );
			
			// Remove the original ID from the list
			$translation_ids = array_diff( $translation_ids, [ $original_id ] );

			if ( empty( $translation_ids ) ) {
				return;
			}

			foreach ( self::$shared_settings as $key ) {
				$value = get_post_meta( $original_id, $key, true );
				
				foreach ( $translation_ids as $lang_post_id ) {
					if ( isset( self::$translatable_fields[ $key ] ) ) {
						// Special handling for merged settings (ticket types, etc.)
						$current_value = get_post_meta( $lang_post_id, $key, true );
						$value_to_save = self::merge_translatable_data( $key, $value, $current_value );
						update_post_meta( $lang_post_id, $key, $value_to_save );
					} else {
						// direct copy for non-translatable shared settings
						update_post_meta( $lang_post_id, $key, $value );
					}
				}
			}
		}

		/**
		 * Merge structure from original and text from translation
		 */
		public static function merge_translatable_data( $key, $original_data, $translation_data ) {
			if ( ! is_array( $original_data ) || empty( $original_data ) ) {
				return $original_data;
			}

			if ( ! is_array( $translation_data ) ) {
				return $original_data;
			}

			$fields = self::$translatable_fields[ $key ];
			$merged = $original_data;

			foreach ( $merged as $i => $item ) {
				// Try to find matching item in translation data
				// For tickets, we match by price (since names might have changed)
				// or by index if it's the same
				$found_match = false;
				
				// Identify price key
				$price_key = ( $key === 'mep_event_ticket_type' ) ? 'option_price_t' : 'option_price';

				foreach ( $translation_data as $t_item ) {
					if ( isset( $item[ $price_key ] ) && isset( $t_item[ $price_key ] ) && $item[ $price_key ] == $t_item[ $price_key ] ) {
						// Found match by price, copy translatable text
						foreach ( $fields as $field ) {
							if ( isset( $t_item[ $field ] ) && ! empty( $t_item[ $field ] ) ) {
								$merged[ $i ][ $field ] = $t_item[ $field ];
							}
						}
						$found_match = true;
						break;
					}
				}
			}

			return $merged;
		}

		/**
		 * Show informational notice on translation pages
		 */
		public static function show_translation_notice() {
			global $pagenow, $post;
			
			if ( $pagenow !== 'post.php' || ! isset( $post ) || $post->post_type !== 'mep_events' ) {
				return;
			}

			$default_event_id = mep_get_default_lang_event_id( $post->ID );
			if ( (int) $post->ID !== (int) $default_event_id ) {
				?>
                <div class="notice notice-info is-dismissible">
                    <p>
						<?php 
						printf( 
							__( 'This is a translated event. Event settings (dates, ticket prices, quantities, venue) are managed in the <a href="%s">default language version</a>. You can translate: Event title, description, timeline details, FAQ, email text, SEO content, ticket names/descriptions, and extra service names.', 'mage-eventpress' ),
							get_edit_post_link( $default_event_id )
						); 
						?>
                    </p>
                </div>
				<?php
			}
		}
	}

	MPWEM_WPML_Sync::init();
}
