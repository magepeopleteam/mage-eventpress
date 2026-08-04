<?php
	/**
	 * Modern General Settings card UI.
	 * Same option group/keys (general_setting_sec) — layout only.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_General_Settings_UI' ) ) {
		class MPWEM_General_Settings_UI {

			const SECTION = 'general_setting_sec';

			/**
			 * Core field names rendered in the card UI (extras from filters go to Additional).
			 *
			 * @return string[]
			 */
			public static function known_field_names() {
				return array(
					'seat_reserved_order_status',
					'mep_disable_block_editor',
					'mep_event_list_page_style',
					'mep_event_edit_page_mode',
					'mep_rest_api_status',
					'mep_multi_lang_plugin',
					'mep_event_list_order_by',
					'mep_event_label',
					'mep_event_slug',
					'mep_event_icon',
					'mep_event_cat_label',
					'mep_event_cat_slug',
					'mep_event_org_label',
					'mep_event_org_slug',
					'mep_google_map_type',
					'google-map-api',
					'mep_google_map_zoom_level',
					'mep_event_expire_on_datetimes',
					'mep_hide_old_date',
					'mep_hide_expire_ticket',
					'mep_hide_location_from_order_page',
					'mep_hide_date_from_order_page',
					'mep_hide_expired_date_in_calendar',
					'mep_event_direct_checkout',
					'mep_show_zero_as_free',
					'mep_ticket_expire_time',
					'mep_ticket_expire_time_on_cart',
					'mep_load_fontawesome_from_theme',
					'mep_load_flaticon_from_theme',
					'mep_speed_up_list_page',
					'mep_hide_not_available_event_from_list_page',
					'mep_show_sold_out_ribbon_list_page',
					'mep_show_limited_availability_ribbon',
					'mep_limited_availability_threshold',
					'mep_limited_availability_text',
					'mep_show_low_stock_warning',
					'mep_low_stock_threshold',
					'mep_low_stock_text',
					'mep_enable_low_stock_email',
					'mep_show_hidden_wc_product',
					'mep_show_event_sidebar',
					'mep_clear_cart_after_checkout',
					'mep_manual_seat_Left_fix',
					'mep_fix_details_page_fatal_error',
					'mep_datepicker_format',
				);
			}

			/**
			 * @param array $fields Full settings fields map.
			 */
			public static function render( $fields ) {
				$sec     = self::SECTION;
				$all     = isset( $fields[ $sec ] ) && is_array( $fields[ $sec ] ) ? $fields[ $sec ] : array();
				$known   = self::known_field_names();
				$extras  = array();
				foreach ( $all as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( $name && ! in_array( $name, $known, true ) ) {
						$extras[] = $field;
					}
				}

				$g = function( $key, $default = '' ) use ( $sec ) {
					$options = get_option( $sec, array() );
					if ( is_array( $options ) && array_key_exists( $key, $options ) ) {
						$val = $options[ $key ];
						if ( is_array( $val ) ) {
							return $val;
						}
						if ( '' !== $val && null !== $val ) {
							return $val;
						}
					}
					return $default;
				};

				echo '<form method="post" action="options.php" class="mep-gn__form">';
				settings_fields( $sec );
				?>
				<div class="mep-gn">
					<div class="mep-gn__header">
						<div class="mep-gn__header-text">
							<h2 class="mep-gn__title"><?php esc_html_e( 'General Settings', 'mage-eventpress' ); ?></h2>
							<p class="mep-gn__subtitle"><?php esc_html_e( 'Configure core booking behavior, labels, maps, and plugin-wide options.', 'mage-eventpress' ); ?></p>
						</div>
					</div>
					<div class="mep-gn__grid">
						<div class="mep-gn__col">
							<?php self::card_general( $g ); ?>
							<?php self::card_event_config( $g ); ?>
							<?php self::card_category_organizer( $g ); ?>
							<?php self::card_map( $g ); ?>
							<?php if ( ! empty( $extras ) ) : ?>
								<?php self::card_extras( $extras, $g ); ?>
							<?php endif; ?>
						</div>
						<div class="mep-gn__col">
							<?php self::card_booking_rules( $g ); ?>
							<?php self::card_inventory( $g ); ?>
							<?php self::card_advanced( $g ); ?>
						</div>
					</div>
				</div>
				<div style="display:none;"><?php submit_button(); ?></div>
				</form>
				<?php
			}

			/* ── Cards ───────────────────────────────────────── */

			private static function card_general( $g ) {
				$status = $g( 'seat_reserved_order_status', array( 'processing' => 'processing', 'completed' => 'completed' ) );
				if ( ! is_array( $status ) ) {
					$status = array();
				}
				$seat_opts = array(
					'pending'    => __( 'Pending Payment', 'mage-eventpress' ),
					'processing' => __( 'Processing', 'mage-eventpress' ),
					'on-hold'    => __( 'On Hold', 'mage-eventpress' ),
					'completed'  => __( 'Completed', 'mage-eventpress' ),
				);
				self::open_card( 'fas fa-cog', __( 'General Settings', 'mage-eventpress' ) );
				self::multicheck(
					'seat_reserved_order_status',
					__( 'Order Status for Available Seats', 'mage-eventpress' ),
					__( 'Order statuses that count a seat as booked.', 'mage-eventpress' ),
					$seat_opts,
					$status
				);
				// Stored inverted: yes = Disable block editor, no = Enable.
				self::toggle(
					'mep_disable_block_editor',
					__( 'Turn On/Off Block Editor', 'mage-eventpress' ),
					__( 'Enable the WordPress block editor for events. Also turn on the REST API below.', 'mage-eventpress' ),
					$g( 'mep_disable_block_editor', 'yes' ),
					'no',
					'yes'
				);
				self::select(
					'mep_event_list_page_style',
					__( 'Admin List Style', 'mage-eventpress' ),
					__( 'How events appear in the admin list.', 'mage-eventpress' ),
					array(
						'new' => __( 'New Style', 'mage-eventpress' ),
						'wp'  => __( 'WordPress Default', 'mage-eventpress' ),
					),
					$g( 'mep_event_list_page_style', 'new' )
				);
				self::select(
					'mep_event_edit_page_mode',
					__( 'Edit Screen', 'mage-eventpress' ),
					__( 'Editor used when adding or editing an event.', 'mage-eventpress' ),
					array(
						'modern'  => __( 'Modern Mode', 'mage-eventpress' ),
						'classic' => __( 'Classic Mode', 'mage-eventpress' ),
					),
					$g( 'mep_event_edit_page_mode', 'modern' )
				);
				self::close_card();
			}

			private static function card_booking_rules( $g ) {
				$expire = $g( 'mep_event_expire_on_datetimes', 'event_start_datetime' );
				if ( 'mep_event_start_date' === $expire ) {
					$expire = 'event_start_datetime';
				}
				self::open_card( 'fas fa-sliders-h', __( 'Booking Rules', 'mage-eventpress' ) );
				self::select(
					'mep_event_expire_on_datetimes',
					__( 'Expiry Time', 'mage-eventpress' ),
					__( 'When the event should stop accepting bookings.', 'mage-eventpress' ),
					array(
						'event_start_datetime'  => __( 'Event Start Time', 'mage-eventpress' ),
						'event_expire_datetime' => __( 'Event End Time', 'mage-eventpress' ),
					),
					$expire
				);
				self::toggle_yesno( 'mep_hide_old_date', __( 'Hide Past Dates', 'mage-eventpress' ), __( 'Hide past dates in the booking date picker.', 'mage-eventpress' ), $g( 'mep_hide_old_date', 'yes' ) );
				self::toggle_yesno( 'mep_hide_expire_ticket', __( 'Hide Expired Ticket Types', 'mage-eventpress' ), __( 'Hide ticket types that are no longer available.', 'mage-eventpress' ), $g( 'mep_hide_expire_ticket', 'no' ) );
				self::select(
					'mep_event_direct_checkout',
					__( 'Checkout Behavior', 'mage-eventpress' ),
					__( 'Where customers go after booking.', 'mage-eventpress' ),
					array(
						'yes' => __( 'Redirect to Checkout', 'mage-eventpress' ),
						'no'  => __( 'Stay / Cart Flow', 'mage-eventpress' ),
					),
					$g( 'mep_event_direct_checkout', 'yes' )
				);
				self::toggle_yesno( 'mep_hide_location_from_order_page', __( 'Hide Location in Orders & Emails', 'mage-eventpress' ), __( 'Hide event location on thank-you page and emails.', 'mage-eventpress' ), $g( 'mep_hide_location_from_order_page', 'no' ) );
				self::toggle_yesno( 'mep_hide_date_from_order_page', __( 'Hide Date in Orders & Emails', 'mage-eventpress' ), __( 'Hide event date on thank-you page and emails.', 'mage-eventpress' ), $g( 'mep_hide_date_from_order_page', 'no' ) );
				self::close_card();
			}

			private static function card_event_config( $g ) {
				self::open_card( 'fas fa-calendar-alt', __( 'Event Configuration', 'mage-eventpress' ) );
				self::toggle(
					'mep_rest_api_status',
					__( 'REST API Support', 'mage-eventpress' ),
					__( 'Allow event data through the WordPress REST API.', 'mage-eventpress' ),
					$g( 'mep_rest_api_status', 'disable' ),
					'enable',
					'disable'
				);
				self::select(
					'mep_multi_lang_plugin',
					__( 'Multilingual Plugin Support', 'mage-eventpress' ),
					__( 'Select the translation plugin you use, if any.', 'mage-eventpress' ),
					array(
						'none'     => __( 'None', 'mage-eventpress' ),
						'polylang' => __( 'Polylang', 'mage-eventpress' ),
						'wpml'     => __( 'WPML', 'mage-eventpress' ),
					),
					$g( 'mep_multi_lang_plugin', 'none' )
				);
				self::select(
					'mep_event_list_order_by',
					__( 'Events Sort Order', 'mage-eventpress' ),
					__( 'Sort the event list by upcoming date or title.', 'mage-eventpress' ),
					array(
						'meta_value' => __( 'ASC (Upcoming Date)', 'mage-eventpress' ),
						'title'      => __( 'Event Title', 'mage-eventpress' ),
					),
					$g( 'mep_event_list_order_by', 'meta_value' )
				);
				echo '<div class="mep-gn__row-2">';
				self::text( 'mep_event_label', __( 'Event Label', 'mage-eventpress' ), '', $g( 'mep_event_label', 'Events' ) );
				self::text( 'mep_event_slug', __( 'Event Slug', 'mage-eventpress' ), __( 'Save Permalinks after changing.', 'mage-eventpress' ), $g( 'mep_event_slug', 'events' ) );
				echo '</div>';
				self::text( 'mep_event_icon', __( 'Menu Icon', 'mage-eventpress' ), __( 'Dashicon class, e.g. dashicons-calendar-alt.', 'mage-eventpress' ), $g( 'mep_event_icon', 'dashicons-calendar-alt' ) );
				self::close_card();
			}

			private static function card_inventory( $g ) {
				self::open_card( 'fas fa-box', __( 'Inventory & Stock', 'mage-eventpress' ) );
				self::toggle_yesno( 'mep_show_zero_as_free', __( 'Show Zero Price', 'mage-eventpress' ), __( 'Display "Free" instead of 0 when a ticket has no price.', 'mage-eventpress' ), $g( 'mep_show_zero_as_free', 'yes' ) );
				echo '<div class="mep-gn__row-2">';
				self::text( 'mep_ticket_expire_time', __( 'Stop Sales (Minutes)', 'mage-eventpress' ), __( 'Minutes before event when sales close. 0 = no limit.', 'mage-eventpress' ), $g( 'mep_ticket_expire_time', '0' ) );
				self::text( 'mep_ticket_expire_time_on_cart', __( 'Cart Hold Time (Min)', 'mage-eventpress' ), __( 'Minutes before abandoned cart tickets release.', 'mage-eventpress' ), $g( 'mep_ticket_expire_time_on_cart', '10' ) );
				echo '</div>';

				$hide_full = $g( 'mep_hide_not_available_event_from_list_page', 'no' );
				$ribbon    = $g( 'mep_show_sold_out_ribbon_list_page', 'no' );
				$booked    = ( 'yes' === $hide_full ) ? 'hide' : ( ( 'yes' === $ribbon ) ? 'ribbon' : 'show' );
				?>
				<div class="mep-gn__field">
					<label class="mep-gn__label"><?php esc_html_e( 'Fully Booked Events', 'mage-eventpress' ); ?></label>
					<div class="mep-gn__choice-box">
						<label class="mep-gn__radio">
							<input type="radio" name="mep_gn_fully_booked" value="ribbon" <?php checked( $booked, 'ribbon' ); ?> data-mep-fully-booked />
							<span><?php esc_html_e( 'Show Ribbon', 'mage-eventpress' ); ?></span>
						</label>
						<label class="mep-gn__radio">
							<input type="radio" name="mep_gn_fully_booked" value="hide" <?php checked( $booked, 'hide' ); ?> data-mep-fully-booked />
							<span><?php esc_html_e( 'Hide Event', 'mage-eventpress' ); ?></span>
						</label>
						<label class="mep-gn__radio">
							<input type="radio" name="mep_gn_fully_booked" value="show" <?php checked( $booked, 'show' ); ?> data-mep-fully-booked />
							<span><?php esc_html_e( 'Show Normally', 'mage-eventpress' ); ?></span>
						</label>
					</div>
					<input type="hidden" name="<?php echo esc_attr( self::SECTION ); ?>[mep_hide_not_available_event_from_list_page]" id="mep-gn-hide-full" value="<?php echo esc_attr( $hide_full ); ?>" />
					<input type="hidden" name="<?php echo esc_attr( self::SECTION ); ?>[mep_show_sold_out_ribbon_list_page]" id="mep-gn-sold-ribbon" value="<?php echo esc_attr( $ribbon ); ?>" />
					<p class="mep-gn__hint"><?php esc_html_e( 'How fully booked events appear in listings.', 'mage-eventpress' ); ?></p>
				</div>
				<?php
				self::toggle_yesno( 'mep_show_low_stock_warning', __( 'Low Stock Warnings', 'mage-eventpress' ), __( 'Show a warning when seats are running low.', 'mage-eventpress' ), $g( 'mep_show_low_stock_warning', 'yes' ) );
				echo '<div class="mep-gn__row-2 mep-gn__low-stock-fields">';
				self::text( 'mep_low_stock_threshold', __( 'Low Stock Threshold', 'mage-eventpress' ), '', $g( 'mep_low_stock_threshold', '0' ) );
				self::text( 'mep_low_stock_text', __( 'Low Stock Text', 'mage-eventpress' ), __( 'Use %s for seats left.', 'mage-eventpress' ), $g( 'mep_low_stock_text', 'Hurry! Only %s seats left' ) );
				echo '</div>';
				self::toggle_yesno( 'mep_enable_low_stock_email', __( 'Low Stock Email Alerts', 'mage-eventpress' ), __( 'Email the admin when seats are running low.', 'mage-eventpress' ), $g( 'mep_enable_low_stock_email', 'yes' ) );
				self::toggle_yesno( 'mep_show_limited_availability_ribbon', __( 'Limited Availability Ribbon', 'mage-eventpress' ), __( 'Show a badge when only a few seats are left.', 'mage-eventpress' ), $g( 'mep_show_limited_availability_ribbon', 'no' ) );
				echo '<div class="mep-gn__row-2">';
				self::text( 'mep_limited_availability_threshold', __( 'Limited Threshold', 'mage-eventpress' ), '', $g( 'mep_limited_availability_threshold', '0' ) );
				self::text( 'mep_limited_availability_text', __( 'Limited Ribbon Text', 'mage-eventpress' ), '', $g( 'mep_limited_availability_text', 'Limited Availability' ) );
				echo '</div>';
				self::close_card();
			}

			private static function card_category_organizer( $g ) {
				self::open_card( 'fas fa-sitemap', __( 'Category & Organizer', 'mage-eventpress' ) );
				echo '<div class="mep-gn__row-2">';
				self::text( 'mep_event_cat_label', __( 'Category Label', 'mage-eventpress' ), '', $g( 'mep_event_cat_label', 'Category' ) );
				self::text( 'mep_event_cat_slug', __( 'Category Slug', 'mage-eventpress' ), '', $g( 'mep_event_cat_slug', 'mep_cat' ) );
				echo '</div>';
				echo '<div class="mep-gn__row-2">';
				self::text( 'mep_event_org_label', __( 'Organizer Label', 'mage-eventpress' ), '', $g( 'mep_event_org_label', 'Organizer' ) );
				self::text( 'mep_event_org_slug', __( 'Organizer Slug', 'mage-eventpress' ), '', $g( 'mep_event_org_slug', 'mep_org' ) );
				echo '</div>';
				self::close_card();
			}

			private static function card_advanced( $g ) {
				$current_date = current_time( 'Y-m-d' );
				$lang         = get_bloginfo( 'language' );
				self::open_card( 'fas fa-sliders-h', __( 'Advanced Options', 'mage-eventpress' ) );
				self::toggle_yesno( 'mep_hide_expired_date_in_calendar', __( 'Hide Expired Events', 'mage-eventpress' ), __( 'Hide past events from the free calendar view.', 'mage-eventpress' ), $g( 'mep_hide_expired_date_in_calendar', 'no' ) );
				self::toggle(
					'mep_clear_cart_after_checkout',
					__( 'Clear Cart After Order', 'mage-eventpress' ),
					__( 'Empty the cart after an order is placed.', 'mage-eventpress' ),
					$g( 'mep_clear_cart_after_checkout', 'enable' ),
					'enable',
					'disable'
				);
				self::select(
					'mep_datepicker_format',
					__( 'Date Picker Format', 'mage-eventpress' ),
					__( 'Avoid text-based formats on non-English sites.', 'mage-eventpress' ),
					array(
						'yy-mm-dd'   => $current_date,
						'yy/mm/dd'   => date( 'Y/m/d', strtotime( $current_date ) ),
						'dd-mm-yy'   => date( 'd-m-Y', strtotime( $current_date ) ),
						'dd.mm.yy'   => date( 'd.m.Y', strtotime( $current_date ) ),
						'mm-dd-yy'   => date( 'm-d-Y', strtotime( $current_date ) ),
						'mm/dd/yy'   => date( 'm/d/Y', strtotime( $current_date ) ),
						'd M , yy'   => date( 'j M , Y', strtotime( $current_date ) ),
						'D d M , yy' => date( 'D j M , Y', strtotime( $current_date ) ),
						'M d , yy'   => date( 'M  j, Y', strtotime( $current_date ) ),
						'D M d , yy' => date( 'D M  j, Y', strtotime( $current_date ) ),
						$lang        => $lang,
					),
					$g( 'mep_datepicker_format', 'yy-mm-dd' )
				);
				self::toggle_yesno( 'mep_speed_up_list_page', __( 'Faster Event List Loading', 'mage-eventpress' ), __( 'Speeds up the list; disables waitlist/seat counts there.', 'mage-eventpress' ), $g( 'mep_speed_up_list_page', 'no' ) );
				self::toggle(
					'mep_show_event_sidebar',
					__( 'Event Sidebar', 'mage-eventpress' ),
					__( 'Register a widget area for the event page.', 'mage-eventpress' ),
					$g( 'mep_show_event_sidebar', 'disable' ),
					'enable',
					'disable'
				);
				self::toggle_yesno( 'mep_show_hidden_wc_product', __( 'Show Hidden WooCommerce Products', 'mage-eventpress' ), __( 'Show hidden WC products created for events.', 'mage-eventpress' ), $g( 'mep_show_hidden_wc_product', 'no' ) );
				self::toggle_yesno( 'mep_load_fontawesome_from_theme', __( 'Use Theme Font Awesome', 'mage-eventpress' ), __( 'Turn on if your theme already loads Font Awesome.', 'mage-eventpress' ), $g( 'mep_load_fontawesome_from_theme', 'no' ) );
				self::toggle_yesno( 'mep_load_flaticon_from_theme', __( 'Use Theme Flat Icon', 'mage-eventpress' ), __( 'Turn on if your theme already loads Flat Icon.', 'mage-eventpress' ), $g( 'mep_load_flaticon_from_theme', 'no' ) );
				self::toggle(
					'mep_manual_seat_Left_fix',
					__( 'Seat Count Fix', 'mage-eventpress' ),
					__( 'Enable only if seat availability shows incorrectly after update.', 'mage-eventpress' ),
					$g( 'mep_manual_seat_Left_fix', 'disable' ),
					'enable',
					'disable'
				);
				self::toggle(
					'mep_fix_details_page_fatal_error',
					__( 'Event Page Error Fix', 'mage-eventpress' ),
					__( 'Enable only if the event page shows a fatal error.', 'mage-eventpress' ),
					$g( 'mep_fix_details_page_fatal_error', 'disable' ),
					'enable',
					'disable'
				);
				self::close_card();
			}

			private static function card_map( $g ) {
				$map_type = $g( 'mep_google_map_type', 'iframe' );
				if ( '' === $map_type ) {
					$map_type = 'iframe';
					$map_on   = false;
				} else {
					$map_on = true;
				}
				self::open_card( 'fas fa-map-marker-alt', __( 'Map Configuration', 'mage-eventpress' ) );
				?>
				<div class="mep-gn__field mep-gn__toggle-row">
					<div class="mep-gn__toggle-text">
						<label class="mep-gn__label"><?php esc_html_e( 'Enable Map', 'mage-eventpress' ); ?></label>
						<p class="mep-gn__hint"><?php esc_html_e( 'Show maps on event pages.', 'mage-eventpress' ); ?></p>
					</div>
					<label class="mep-gn__switch">
						<input type="checkbox" id="mep-gn-map-enable" <?php checked( $map_on ); ?> />
						<span class="mep-gn__switch-ui"></span>
					</label>
				</div>
				<div class="mep-gn__map-fields" id="mep-gn-map-fields"<?php echo $map_on ? '' : ' hidden'; ?>>
					<?php
					// Real field — JS clears to empty string when map is disabled (legacy "off").
					self::select(
						'mep_google_map_type',
						__( 'Map Type', 'mage-eventpress' ),
						__( 'API maps are more accurate; Iframe needs no key.', 'mage-eventpress' ),
						array(
							'api'    => __( 'Google Maps (API)', 'mage-eventpress' ),
							'iframe' => __( 'Iframe', 'mage-eventpress' ),
						),
						in_array( $map_type, array( 'api', 'iframe' ), true ) ? $map_type : 'iframe'
					);
					self::text( 'google-map-api', __( 'Google Maps API Key', 'mage-eventpress' ), __( 'Required for API maps.', 'mage-eventpress' ), $g( 'google-map-api', '' ) );
					$zoom = $g( 'mep_google_map_zoom_level', '17' );
					?>
					<div class="mep-gn__field">
						<label class="mep-gn__label" for="mep-gn-zoom"><?php esc_html_e( 'Default Zoom Level', 'mage-eventpress' ); ?></label>
						<div class="mep-gn__zoom-row">
							<input type="range" min="5" max="25" step="1" id="mep-gn-zoom" class="mep-gn__range" name="<?php echo esc_attr( self::SECTION ); ?>[mep_google_map_zoom_level]" value="<?php echo esc_attr( $zoom ); ?>" />
							<span class="mep-gn__zoom-val" id="mep-gn-zoom-val"><?php echo esc_html( $zoom ); ?></span>
						</div>
					</div>
				</div>
				<?php
				self::close_card();
			}

			private static function card_extras( $extras, $g ) {
				self::open_card( 'fas fa-puzzle-piece', __( 'Additional Settings', 'mage-eventpress' ) );
				foreach ( $extras as $field ) {
					$name = isset( $field['name'] ) ? $field['name'] : '';
					if ( ! $name ) {
						continue;
					}
					$type    = isset( $field['type'] ) ? $field['type'] : 'text';
					$label   = isset( $field['label'] ) ? $field['label'] : $name;
					$desc    = isset( $field['desc'] ) ? $field['desc'] : '';
					$default = isset( $field['default'] ) ? $field['default'] : '';
					$value   = $g( $name, $default );
					$options = isset( $field['options'] ) ? $field['options'] : array();

					if ( 'select' === $type && is_array( $options ) ) {
						self::select( $name, $label, $desc, $options, $value );
					} elseif ( 'multicheck' === $type && is_array( $options ) ) {
						self::multicheck( $name, $label, $desc, $options, is_array( $value ) ? $value : array() );
					} elseif ( 'checkbox' === $type ) {
						self::toggle( $name, $label, $desc, $value ? $value : 'off', 'on', 'off' );
					} else {
						self::text( $name, $label, $desc, is_scalar( $value ) ? $value : $default );
					}
				}
				self::close_card();
			}

			/* ── Field helpers ───────────────────────────────── */

			private static function open_card( $icon, $title ) {
				?>
				<div class="mep-gn__card">
					<div class="mep-gn__card-head">
						<span class="mep-gn__card-icon"><i class="<?php echo esc_attr( $icon ); ?>"></i></span>
						<h3 class="mep-gn__card-title"><?php echo esc_html( $title ); ?></h3>
					</div>
					<div class="mep-gn__card-body">
				<?php
			}

			private static function close_card() {
				echo '</div></div>';
			}

			private static function text( $name, $label, $hint, $value, $input_type = 'text' ) {
				$id = 'mep-gn-' . sanitize_html_class( $name );
				?>
				<div class="mep-gn__field">
					<label class="mep-gn__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="<?php echo esc_attr( $input_type ); ?>" class="mep-gn__input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" />
					<?php if ( $hint ) : ?><p class="mep-gn__hint"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
				</div>
				<?php
			}

			private static function select( $name, $label, $hint, $options, $value ) {
				$id = 'mep-gn-' . sanitize_html_class( $name );
				?>
				<div class="mep-gn__field">
					<label class="mep-gn__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
					<select class="mep-gn__select" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>">
						<?php foreach ( $options as $k => $lab ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( (string) $value, (string) $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php if ( $hint ) : ?><p class="mep-gn__hint"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
				</div>
				<?php
			}

			private static function toggle_yesno( $name, $label, $hint, $value ) {
				self::toggle( $name, $label, $hint, $value, 'yes', 'no' );
			}

			/**
			 * @param string $on_val  Stored value when switch is ON.
			 * @param string $off_val Stored value when switch is OFF.
			 */
			private static function toggle( $name, $label, $hint, $value, $on_val, $off_val ) {
				$checked = ( (string) $value === (string) $on_val );
				$id      = 'mep-gn-' . sanitize_html_class( $name );
				?>
				<div class="mep-gn__field mep-gn__toggle-row">
					<div class="mep-gn__toggle-text">
						<label class="mep-gn__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
						<?php if ( $hint ) : ?><p class="mep-gn__hint"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
					</div>
					<label class="mep-gn__switch">
						<input type="hidden" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $off_val ); ?>" />
						<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="<?php echo esc_attr( $on_val ); ?>" <?php checked( $checked ); ?> />
						<span class="mep-gn__switch-ui"></span>
					</label>
				</div>
				<?php
			}

			private static function multicheck( $name, $label, $hint, $options, $value ) {
				?>
				<div class="mep-gn__field">
					<label class="mep-gn__label"><?php echo esc_html( $label ); ?></label>
					<div class="mep-gn__checks">
						<input type="hidden" name="<?php echo esc_attr( self::SECTION . '[' . $name . ']' ); ?>" value="" />
						<?php foreach ( $options as $key => $lab ) : ?>
							<label class="mep-gn__check">
								<input type="checkbox" name="<?php echo esc_attr( self::SECTION . '[' . $name . '][' . $key . ']' ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( isset( $value[ $key ] ) && (string) $value[ $key ] === (string) $key ); ?> />
								<span><?php echo esc_html( $lab ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<?php if ( $hint ) : ?><p class="mep-gn__hint"><?php echo wp_kses_post( $hint ); ?></p><?php endif; ?>
				</div>
				<?php
			}
		}
	}
