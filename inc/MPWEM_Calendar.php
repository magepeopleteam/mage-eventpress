<?php
/**
 * MEP Event Calendar - FullCalendar Integration
 *
 * Migrated from WooCommerce Event Manager Addon: Calendar
 * All functionality merged into the main mage-eventpress plugin.
 *
 * @package MageEventPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

if ( ! function_exists( 'mep_cal_get_all_settings' ) ) {
	function mep_cal_get_all_settings() {
		$defaults = array(
			'mep_cal_default_view'         => 'dayGridMonth',
			'mep_cal_first_day'            => '1',
			'mep_cal_calendar_width'       => '100%',
			'mep_cal_calendar_height'      => 'auto',
			'mep_cal_event_source'         => 'all',
			'mep_cal_specific_events'      => '',
			'mep_cal_event_color'          => '#3a87ad',
			'mep_cal_event_text_color'     => '#ffffff',
			'mep_cal_today_color'          => '#fcf8e3',
			'mep_cal_header_bg'            => '#2c3e50',
			'mep_cal_header_text'          => '#ffffff',
			'mep_cal_border_color'         => '#ddd',
			'mep_cal_sold_out_color'       => '#dc3545',
			'mep_cal_low_stock_color'      => '#ffc107',
			'mep_cal_low_stock_threshold'  => '5',
			'mep_cal_weekday_bg_sun'       => '',
			'mep_cal_weekday_bg_mon'       => '',
			'mep_cal_weekday_bg_tue'       => '',
			'mep_cal_weekday_bg_wed'       => '',
			'mep_cal_weekday_bg_thu'       => '',
			'mep_cal_weekday_bg_fri'       => '',
			'mep_cal_weekday_bg_sat'       => '',
			'mep_cal_day_background_rules' => array(),
			'mep_cal_show_tooltip'         => 'yes',
			'mep_cal_hide_expired'         => 'no',
			'mep_cal_show_year_nav'        => 'yes',
			'mep_cal_show_prev_next'       => 'yes',
			'mep_cal_show_expired_events'  => 'yes',
			'mep_cal_expired_event_color'  => '#999999',
			'mep_cal_expired_opacity'      => '0.6',
			'mep_cal_event_click'          => 'navigate',
			'mep_cal_show_thumbnail'       => 'yes',
			'mep_cal_show_view_switcher'   => 'yes',
			'mep_cal_show_venue'           => 'no',
			'mep_cal_show_speakers'        => 'no',
			'mep_cal_show_extra_services'  => 'no',
			'mep_cal_locale'               => '',
		);

		$settings = get_option( 'mep_calendar_settings', array() );
		return wp_parse_args( $settings, $defaults );
	}
}

if ( ! function_exists( 'mep_cal_get_setting' ) ) {
	function mep_cal_get_setting( $key, $default = '' ) {
		$settings = mep_cal_get_all_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}
}

// =============================================================================
// ASSETS
// =============================================================================

if ( ! function_exists( 'mep_cal_enqueue_assets' ) ) {
	function mep_cal_enqueue_assets() {
		$fullcalendar_local_file = MPWEM_PLUGIN_DIR . '/assets/helper/calendar/fullcalendar.index.global.min.js';
		$fullcalendar_url        = file_exists( $fullcalendar_local_file )
			? MPWEM_PLUGIN_URL . '/assets/helper/calendar/fullcalendar.index.global.min.js'
			: 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js';

		wp_register_script(
			'fullcalendar-js',
			$fullcalendar_url,
			array(),
			'6.1.17',
			true
		);

		wp_register_style(
			'mep-calendar-css',
			MPWEM_PLUGIN_URL . '/assets/helper/calendar/calendar.css',
			array(),
			time()
		);

		wp_register_script(
			'mep-calendar-js',
			MPWEM_PLUGIN_URL . '/assets/helper/calendar/calendar.js',
			array( 'jquery', 'fullcalendar-js' ),
			time(),
			true
		);

		$settings = mep_cal_get_all_settings();
		wp_localize_script( 'mep-calendar-js', 'mepCalendar', array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'mep_calendar_nonce' ),
			'settings' => $settings,
			'i18n'     => array(
				'today'           => __( 'Today', 'mage-eventpress' ),
				'month'           => __( 'Month', 'mage-eventpress' ),
				'week'            => __( 'Week', 'mage-eventpress' ),
				'day'             => __( 'Day', 'mage-eventpress' ),
				'list'            => __( 'List', 'mage-eventpress' ),
				'noEvents'        => __( 'No events to display', 'mage-eventpress' ),
				'allDay'          => __( 'All Day', 'mage-eventpress' ),
				'soldOut'         => __( 'Sold Out', 'mage-eventpress' ),
				'available'       => __( 'Available', 'mage-eventpress' ),
				'total'           => __( 'Total', 'mage-eventpress' ),
				'sold'            => __( 'Sold', 'mage-eventpress' ),
				'reserved'        => __( 'Reserved', 'mage-eventpress' ),
				'price'           => __( 'Price', 'mage-eventpress' ),
				'location'        => __( 'Location', 'mage-eventpress' ),
				'organizer'       => __( 'Organizer', 'mage-eventpress' ),
				'recurring'       => __( 'Recurring', 'mage-eventpress' ),
				'multiDate'       => __( 'Multi Date', 'mage-eventpress' ),
				'virtual'         => __( 'Virtual', 'mage-eventpress' ),
				'allCategories'   => __( 'All Categories', 'mage-eventpress' ),
				'searchEvents'    => __( 'Search events...', 'mage-eventpress' ),
				'seats'           => __( 'Seats', 'mage-eventpress' ),
				'ticketType'      => __( 'Ticket Type', 'mage-eventpress' ),
				'prevYear'        => __( 'Prev Year', 'mage-eventpress' ),
				'nextYear'        => __( 'Next Year', 'mage-eventpress' ),
				'expired'         => __( 'Expired', 'mage-eventpress' ),
				'venue'           => __( 'Venue', 'mage-eventpress' ),
				'speakers'        => __( 'Speakers', 'mage-eventpress' ),
				'extraServices'   => __( 'Extra Services', 'mage-eventpress' ),
				'bookNow'         => __( 'Book Now', 'mage-eventpress' ),
				'viewDetails'     => __( 'View Details', 'mage-eventpress' ),
				'close'           => __( 'Close', 'mage-eventpress' ),
				'from'            => __( 'From', 'mage-eventpress' ),
				'to'              => __( 'To', 'mage-eventpress' ),
				'online'          => __( 'Online', 'mage-eventpress' ),
				'offline'         => __( 'In Person', 'mage-eventpress' ),
				'organizerFilter' => __( 'All Organizers', 'mage-eventpress' ),
				'locationFilter'  => __( 'Location, city, or country', 'mage-eventpress' ),
				'startDate'       => __( 'Start Date', 'mage-eventpress' ),
				'endDate'         => __( 'End Date', 'mage-eventpress' ),
				'resetFilters'    => __( 'Reset Filters', 'mage-eventpress' ),
				'loadingSeats'    => __( 'Loading seat details...', 'mage-eventpress' ),
			),
		) );

		// Always load calendar assets on frontend so AJAX-loaded calendars work
		wp_enqueue_style( 'mep-calendar-css' );
		wp_enqueue_script( 'mep-calendar-js' );
	}
}
add_action( 'wp_enqueue_scripts', 'mep_cal_enqueue_assets' );

if ( ! function_exists( 'mep_cal_admin_enqueue' ) ) {
	function mep_cal_admin_enqueue( $hook ) {
		if ( strpos( $hook, 'mep_calendar_settings' ) === false ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();
		wp_enqueue_style( 'mep-calendar-admin-css', MPWEM_PLUGIN_URL . '/assets/admin/calendar-admin.css', array(), time() );
		wp_enqueue_script( 'mep-calendar-admin-js', MPWEM_PLUGIN_URL . '/assets/admin/calendar-admin.js', array( 'jquery', 'wp-color-picker' ), time(), true );
		wp_localize_script(
			'mep-calendar-admin-js',
			'mepCalendarAdmin',
			array(
				'chooseImage' => __( 'Choose Background Image', 'mage-eventpress' ),
				'useImage'    => __( 'Use This Image', 'mage-eventpress' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'mep_cal_admin_enqueue' );

// =============================================================================
// SHORTCODE
// =============================================================================

if ( ! class_exists( 'MPWEM_Calendar_Shortcode' ) ) {
	class MPWEM_Calendar_Shortcode {

		public function __construct() {
			add_shortcode( 'mep-event-calendar', array( $this, 'render_calendar' ) );
		}

		public function render_calendar( $atts ) {
			$settings = mep_cal_get_all_settings();
			$tooltip_default       = ( isset( $settings['mep_cal_show_tooltip'] ) && $settings['mep_cal_show_tooltip'] === 'no' ) ? 'yes' : 'no';
			$view_switcher_default = isset( $settings['mep_cal_show_view_switcher'] ) ? $settings['mep_cal_show_view_switcher'] : 'yes';
			$event_click_default   = isset( $settings['mep_cal_event_click'] ) ? $settings['mep_cal_event_click'] : 'navigate';

			$defaults = array(
				'style'                  => 'full',
				'show_stock_details'     => 'no',
				'hide_time'              => 'no',
				'split_multi_day'        => 'no',
				'cat'                    => '0',
				'org'                    => '0',
				'tag'                    => '0',
				'city'                   => '',
				'country'                => '',
				'status'                 => 'upcoming',
				'event_source'           => isset( $settings['mep_cal_event_source'] ) ? $settings['mep_cal_event_source'] : 'all',
				'specific_events'        => isset( $settings['mep_cal_specific_events'] ) ? $settings['mep_cal_specific_events'] : '',
				'default_view'           => $settings['mep_cal_default_view'],
				'first_day'              => $settings['mep_cal_first_day'],
				'event_limit'            => '-1',
				'show_category_filter'   => 'no',
				'show_location_filter'   => 'no',
				'show_organizer_filter'  => 'no',
				'show_date_range_filter' => 'no',
				'show_reset_filters'     => 'yes',
				'show_search'            => 'no',
				'width'                  => isset( $settings['mep_cal_calendar_width'] ) ? $settings['mep_cal_calendar_width'] : '100%',
				'event_color'            => '',
				'text_color'             => '',
				'height'                 => isset( $settings['mep_cal_calendar_height'] ) ? $settings['mep_cal_calendar_height'] : 'auto',
				'show_price'             => 'no',
				'show_location'          => 'no',
				'show_organizer'         => 'no',
				'show_recurring_badge'   => 'yes',
				'hide_tooltip'           => $tooltip_default,
				'show_navigation'        => 'yes',
				'show_view_switcher'     => $view_switcher_default,
				'show_year_nav'          => $settings['mep_cal_show_year_nav'],
				'show_prev_next'         => $settings['mep_cal_show_prev_next'],
				'show_expired_events'    => $settings['mep_cal_show_expired_events'],
				'expired_event_color'    => '',
				'expired_opacity'        => '',
				'event_click'            => $event_click_default,
				'id'                     => 'mep-cal-' . uniqid(),
			);

			$atts = shortcode_atts( $defaults, $atts, 'mep-event-calendar' );
			$atts = apply_filters( 'mep_calendar_shortcode_atts', $atts );

			foreach ( $atts as $key => $value ) {
				$atts[ $key ] = sanitize_text_field( $value );
			}

			$toggle_keys = array(
				'show_stock_details',
				'hide_time',
				'split_multi_day',
				'show_category_filter',
				'show_location_filter',
				'show_organizer_filter',
				'show_date_range_filter',
				'show_reset_filters',
				'show_search',
				'show_price',
				'show_location',
				'show_organizer',
				'show_recurring_badge',
				'hide_tooltip',
				'show_navigation',
				'show_view_switcher',
				'show_year_nav',
				'show_prev_next',
				'show_expired_events',
			);

			foreach ( $toggle_keys as $toggle_key ) {
				if ( isset( $atts[ $toggle_key ] ) ) {
					$atts[ $toggle_key ] = $this->normalize_toggle( $atts[ $toggle_key ], $defaults[ $toggle_key ] );
				}
			}

			$allowed_styles = array( 'full', 'lite' );
			if ( ! in_array( $atts['style'], $allowed_styles, true ) ) {
				$atts['style'] = $defaults['style'];
			}

			$allowed_views = array( 'dayGridMonth', 'timeGridWeek', 'timeGridDay', 'listMonth' );
			if ( ! in_array( $atts['default_view'], $allowed_views, true ) ) {
				$atts['default_view'] = $defaults['default_view'];
			}

			$allowed_statuses = array( 'upcoming', 'all', 'expired' );
			if ( ! in_array( $atts['status'], $allowed_statuses, true ) ) {
				$atts['status'] = $defaults['status'];
			}

			$allowed_sources = array( 'all', 'single', 'multi_date', 'repeated', 'specific' );
			if ( ! in_array( $atts['event_source'], $allowed_sources, true ) ) {
				$atts['event_source'] = $defaults['event_source'];
			}

			$allowed_event_click = array( 'navigate', 'tooltip', 'none' );
			if ( ! in_array( $atts['event_click'], $allowed_event_click, true ) ) {
				$atts['event_click'] = $defaults['event_click'];
			}

			$atts['specific_events'] = $this->normalize_event_ids( $atts['specific_events'] );
			$atts['width']           = $this->normalize_dimension( $atts['width'], $defaults['width'], false );
			$atts['height']          = $this->normalize_dimension( $atts['height'], $defaults['height'], true );
			$atts['event_limit']     = is_numeric( $atts['event_limit'] ) ? (string) intval( $atts['event_limit'] ) : $defaults['event_limit'];
			$atts['first_day']       = is_numeric( $atts['first_day'] ) ? (string) intval( $atts['first_day'] ) : $defaults['first_day'];

			wp_enqueue_style( 'mep-calendar-css' );
			wp_enqueue_script( 'fullcalendar-js' );
			wp_enqueue_script( 'mep-calendar-js' );

			ob_start();

			do_action( 'mep_calendar_before_render', $atts );

			$this->output_dynamic_css( $atts, $settings );
			?>
			<div class="mep-calendar-wrapper" id="<?php echo esc_attr( $atts['id'] ); ?>-wrapper" style="<?php echo esc_attr( $this->get_wrapper_style( $atts ) ); ?>">

				<?php if ( $atts['show_category_filter'] === 'yes' || $atts['show_search'] === 'yes' || $atts['show_location_filter'] === 'yes' || $atts['show_organizer_filter'] === 'yes' || $atts['show_date_range_filter'] === 'yes' ) : ?>
				<div class="mep-calendar-filters">
					<?php if ( $atts['show_search'] === 'yes' ) : ?>
					<div class="mep-cal-filter-item mep-cal-search">
						<input type="text"
							class="mep-cal-search-input"
							placeholder="<?php esc_attr_e( 'Search events...', 'mage-eventpress' ); ?>"
							data-calendar-id="<?php echo esc_attr( $atts['id'] ); ?>" />
						<span class="mep-cal-search-icon"><i class="fas fa-search"></i></span>
					</div>
					<?php endif; ?>

					<?php if ( $atts['show_category_filter'] === 'yes' ) : ?>
					<div class="mep-cal-filter-item mep-cal-category-filter">
						<select class="mep-cal-category-select" data-calendar-id="<?php echo esc_attr( $atts['id'] ); ?>">
							<option value=""><?php esc_html_e( 'All Categories', 'mage-eventpress' ); ?></option>
							<?php
							$categories = get_terms( array( 'taxonomy' => 'mep_cat', 'hide_empty' => true ) );
							if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
								foreach ( $categories as $category ) {
									echo '<option value="' . esc_attr( $category->term_id ) . '">' . esc_html( $category->name ) . '</option>';
								}
							}
							?>
						</select>
					</div>
					<?php endif; ?>

					<?php if ( $atts['show_organizer_filter'] === 'yes' ) : ?>
					<div class="mep-cal-filter-item mep-cal-organizer-filter">
						<select class="mep-cal-organizer-select" data-calendar-id="<?php echo esc_attr( $atts['id'] ); ?>">
							<option value=""><?php esc_html_e( 'All Organizers', 'mage-eventpress' ); ?></option>
							<?php
							$organizers = get_terms( array( 'taxonomy' => 'mep_org', 'hide_empty' => true ) );
							if ( ! is_wp_error( $organizers ) && ! empty( $organizers ) ) {
								foreach ( $organizers as $organizer ) {
									echo '<option value="' . esc_attr( $organizer->term_id ) . '">' . esc_html( $organizer->name ) . '</option>';
								}
							}
							?>
						</select>
					</div>
					<?php endif; ?>

					<?php if ( $atts['show_location_filter'] === 'yes' ) : ?>
					<div class="mep-cal-filter-item mep-cal-location-filter">
						<input type="text"
							class="mep-cal-location-input"
							placeholder="<?php esc_attr_e( 'Location, city, or country', 'mage-eventpress' ); ?>"
							data-calendar-id="<?php echo esc_attr( $atts['id'] ); ?>" />
					</div>
					<?php endif; ?>

					<?php if ( $atts['show_date_range_filter'] === 'yes' ) : ?>
					<div class="mep-cal-filter-item mep-cal-date-filter">
						<label class="screen-reader-text" for="<?php echo esc_attr( $atts['id'] ); ?>-date-start"><?php esc_html_e( 'Start Date', 'mage-eventpress' ); ?></label>
						<input type="date"
							id="<?php echo esc_attr( $atts['id'] ); ?>-date-start"
							class="mep-cal-date-start"
							data-calendar-id="<?php echo esc_attr( $atts['id'] ); ?>" />
					</div>
					<div class="mep-cal-filter-item mep-cal-date-filter">
						<label class="screen-reader-text" for="<?php echo esc_attr( $atts['id'] ); ?>-date-end"><?php esc_html_e( 'End Date', 'mage-eventpress' ); ?></label>
						<input type="date"
							id="<?php echo esc_attr( $atts['id'] ); ?>-date-end"
							class="mep-cal-date-end"
							data-calendar-id="<?php echo esc_attr( $atts['id'] ); ?>" />
					</div>
					<?php endif; ?>

					<?php if ( $atts['show_reset_filters'] === 'yes' ) : ?>
					<div class="mep-cal-filter-item mep-cal-reset-filter">
						<button type="button" class="mep-cal-reset-button" data-calendar-id="<?php echo esc_attr( $atts['id'] ); ?>">
							<?php esc_html_e( 'Reset Filters', 'mage-eventpress' ); ?>
						</button>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<div class="mep-calendar-container <?php echo esc_attr( 'mep-cal-style-' . $atts['style'] ); ?>"
					id="<?php echo esc_attr( $atts['id'] ); ?>"
					data-style="<?php echo esc_attr( $atts['style'] ); ?>"
					data-default-view="<?php echo esc_attr( $atts['default_view'] ); ?>"
					data-first-day="<?php echo esc_attr( $atts['first_day'] ); ?>"
					data-cat="<?php echo esc_attr( $atts['cat'] ); ?>"
					data-org="<?php echo esc_attr( $atts['org'] ); ?>"
					data-tag="<?php echo esc_attr( $atts['tag'] ); ?>"
					data-city="<?php echo esc_attr( $atts['city'] ); ?>"
					data-country="<?php echo esc_attr( $atts['country'] ); ?>"
					data-status="<?php echo esc_attr( $atts['status'] ); ?>"
					data-event-source="<?php echo esc_attr( $atts['event_source'] ); ?>"
					data-specific-events="<?php echo esc_attr( $atts['specific_events'] ); ?>"
					data-event-limit="<?php echo esc_attr( $atts['event_limit'] ); ?>"
					data-event-color="<?php echo esc_attr( $atts['event_color'] ); ?>"
					data-text-color="<?php echo esc_attr( $atts['text_color'] ); ?>"
					data-height="<?php echo esc_attr( $atts['height'] ); ?>"
					data-show-stock="<?php echo esc_attr( $atts['show_stock_details'] ); ?>"
					data-hide-time="<?php echo esc_attr( $atts['hide_time'] ); ?>"
					data-split-multi-day="<?php echo esc_attr( $atts['split_multi_day'] ); ?>"
					data-show-price="<?php echo esc_attr( $atts['show_price'] ); ?>"
					data-show-location="<?php echo esc_attr( $atts['show_location'] ); ?>"
					data-show-organizer="<?php echo esc_attr( $atts['show_organizer'] ); ?>"
					data-show-recurring-badge="<?php echo esc_attr( $atts['show_recurring_badge'] ); ?>"
					data-hide-tooltip="<?php echo esc_attr( $atts['hide_tooltip'] ); ?>"
					data-show-navigation="<?php echo esc_attr( $atts['show_navigation'] ); ?>"
					data-show-view-switcher="<?php echo esc_attr( $atts['show_view_switcher'] ); ?>"
					data-show-year-nav="<?php echo esc_attr( $atts['show_year_nav'] ); ?>"
					data-show-prev-next="<?php echo esc_attr( $atts['show_prev_next'] ); ?>"
					data-show-expired-events="<?php echo esc_attr( $atts['show_expired_events'] ); ?>"
					data-expired-event-color="<?php echo esc_attr( $atts['expired_event_color'] ); ?>"
					data-expired-opacity="<?php echo esc_attr( $atts['expired_opacity'] ); ?>"
					data-event-click="<?php echo esc_attr( $atts['event_click'] ); ?>"
				></div>

				<!-- Tooltip container -->
				<div class="mep-cal-tooltip" id="<?php echo esc_attr( $atts['id'] ); ?>-tooltip" style="display:none;"></div>
			</div>
			<?php

			do_action( 'mep_calendar_after_render', $atts );
			do_action( 'mep_calendar_enqueue_scripts' );
			?>
			<script>
			(function() {
				function tryInit() {
					if (typeof mepCalendarInit === 'function') {
						mepCalendarInit();
					} else {
						setTimeout(tryInit, 100);
					}
				}
				tryInit();
			})();
			</script>
			<?php
			return ob_get_clean();
		}

		private function output_dynamic_css( $atts, $settings ) {
			$event_color = ! empty( $atts['event_color'] ) ? $atts['event_color'] : $settings['mep_cal_event_color'];
			$text_color  = ! empty( $atts['text_color'] ) ? $atts['text_color'] : $settings['mep_cal_event_text_color'];
			?>
			<style>
				#<?php echo esc_attr( $atts['id'] ); ?>-wrapper {
					--mep-cal-event-color: <?php echo esc_attr( $event_color ); ?>;
					--mep-cal-event-text: <?php echo esc_attr( $text_color ); ?>;
					--mep-cal-today: <?php echo esc_attr( $settings['mep_cal_today_color'] ); ?>;
					--mep-cal-header-bg: <?php echo esc_attr( $settings['mep_cal_header_bg'] ); ?>;
					--mep-cal-header-text: <?php echo esc_attr( $settings['mep_cal_header_text'] ); ?>;
					--mep-cal-border: <?php echo esc_attr( $settings['mep_cal_border_color'] ); ?>;
					--mep-cal-sold-out: <?php echo esc_attr( $settings['mep_cal_sold_out_color'] ); ?>;
					--mep-cal-low-stock: <?php echo esc_attr( $settings['mep_cal_low_stock_color'] ); ?>;
				}
			</style>
			<?php
		}

		private function normalize_toggle( $value, $default = 'no' ) {
			$value = strtolower( trim( (string) $value ) );

			if ( in_array( $value, array( 'yes', 'true', '1', 'on' ), true ) ) {
				return 'yes';
			}

			if ( in_array( $value, array( 'no', 'false', '0', 'off' ), true ) ) {
				return 'no';
			}

			return $default;
		}

		private function normalize_dimension( $value, $default = 'auto', $allow_auto = true ) {
			$value = trim( (string) $value );

			if ( $value === '' ) {
				return $default;
			}

			$lower_value = strtolower( $value );
			if ( $allow_auto && in_array( $lower_value, array( 'auto', 'parent' ), true ) ) {
				return $lower_value;
			}

			if ( preg_match( '/^\d+$/', $value ) ) {
				return $value . 'px';
			}

			if ( preg_match( '/^\d+(\.\d+)?(px|%|vh|vw|rem|em)$/i', $value ) ) {
				return $value;
			}

			return $default;
		}

		private function get_wrapper_style( $atts ) {
			$styles = array(
				'width:' . $atts['width'],
				'max-width:100%',
				'margin-left:auto',
				'margin-right:auto',
			);

			return implode( ';', $styles ) . ';';
		}

		private function normalize_event_ids( $value ) {
			$parts = array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );
			$ids   = array();

			foreach ( $parts as $part ) {
				if ( is_numeric( $part ) ) {
					$ids[] = absint( $part );
				}
			}

			$ids = array_filter( array_unique( $ids ) );

			return implode( ',', $ids );
		}
	}

	new MPWEM_Calendar_Shortcode();
}


// =============================================================================
// AJAX HANDLER
// =============================================================================

if ( ! class_exists( 'MPWEM_Calendar_Ajax' ) ) {
	class MPWEM_Calendar_Ajax {

		public function __construct() {
			add_action( 'wp_ajax_mep_calendar_get_events', array( $this, 'get_events' ) );
			add_action( 'wp_ajax_nopriv_mep_calendar_get_events', array( $this, 'get_events' ) );
			add_action( 'wp_ajax_mep_calendar_get_event_stock', array( $this, 'get_event_stock' ) );
			add_action( 'wp_ajax_nopriv_mep_calendar_get_event_stock', array( $this, 'get_event_stock' ) );
		}

		public function get_events() {
			check_ajax_referer( 'mep_calendar_nonce', 'nonce' );

			$cat             = isset( $_POST['cat'] ) ? sanitize_text_field( wp_unslash( $_POST['cat'] ) ) : '';
			$org             = isset( $_POST['org'] ) ? sanitize_text_field( wp_unslash( $_POST['org'] ) ) : '';
			$tag             = isset( $_POST['tag'] ) ? sanitize_text_field( wp_unslash( $_POST['tag'] ) ) : '';
			$city            = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
			$country         = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
			$status          = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'upcoming';
			$event_source    = isset( $_POST['event_source'] ) ? sanitize_text_field( wp_unslash( $_POST['event_source'] ) ) : 'all';
			$specific_events = isset( $_POST['specific_events'] ) ? sanitize_text_field( wp_unslash( $_POST['specific_events'] ) ) : '';
			$limit           = isset( $_POST['event_limit'] ) ? intval( $_POST['event_limit'] ) : -1;
			$search          = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$cat_filter      = isset( $_POST['cat_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['cat_filter'] ) ) : '';
			$org_filter      = isset( $_POST['org_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['org_filter'] ) ) : '';
			$location_filter = isset( $_POST['location_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['location_filter'] ) ) : '';
			$date_start      = isset( $_POST['date_start'] ) ? sanitize_text_field( wp_unslash( $_POST['date_start'] ) ) : '';
			$date_end        = isset( $_POST['date_end'] ) ? sanitize_text_field( wp_unslash( $_POST['date_end'] ) ) : '';
			$visible_start   = isset( $_POST['visible_start'] ) ? sanitize_text_field( wp_unslash( $_POST['visible_start'] ) ) : '';
			$visible_end     = isset( $_POST['visible_end'] ) ? sanitize_text_field( wp_unslash( $_POST['visible_end'] ) ) : '';

			$show_stock      = isset( $_POST['show_stock_details'] ) ? sanitize_text_field( wp_unslash( $_POST['show_stock_details'] ) ) : 'no';
			$hide_time       = isset( $_POST['hide_time'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_time'] ) ) : 'no';
			$split_multi_day = isset( $_POST['split_multi_day'] ) ? sanitize_text_field( wp_unslash( $_POST['split_multi_day'] ) ) : 'no';
			$show_price      = isset( $_POST['show_price'] ) ? sanitize_text_field( wp_unslash( $_POST['show_price'] ) ) : 'no';
			$show_location   = isset( $_POST['show_location'] ) ? sanitize_text_field( wp_unslash( $_POST['show_location'] ) ) : 'no';
			$show_organizer  = isset( $_POST['show_organizer'] ) ? sanitize_text_field( wp_unslash( $_POST['show_organizer'] ) ) : 'no';
			$hide_tooltip    = isset( $_POST['hide_tooltip'] ) ? sanitize_text_field( wp_unslash( $_POST['hide_tooltip'] ) ) : 'no';

			$effective_cat          = ! empty( $cat_filter ) ? $cat_filter : $cat;
			$effective_org          = ! empty( $org_filter ) ? $org_filter : $org;
			$effective_date_start   = ! empty( $date_start ) ? $date_start : $this->normalize_date_boundary( $visible_start, 'start' );
			$effective_date_end     = ! empty( $date_end ) ? $date_end : $this->normalize_date_boundary( $visible_end, 'end' );

			$settings                   = mep_cal_get_all_settings();
			$hide_expired               = $settings['mep_cal_hide_expired'];
			$show_expired_events        = isset( $_POST['show_expired_events'] ) ? sanitize_text_field( wp_unslash( $_POST['show_expired_events'] ) ) : $settings['mep_cal_show_expired_events'];
			$expired_event_color        = $settings['mep_cal_expired_event_color'];
			$event_color                = isset( $_POST['event_color'] ) && ! empty( $_POST['event_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['event_color'] ) ) : $settings['mep_cal_event_color'];
			$text_color                 = isset( $_POST['text_color'] ) && ! empty( $_POST['text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['text_color'] ) ) : $settings['mep_cal_event_text_color'];
			$sold_out_color             = $settings['mep_cal_sold_out_color'];
			$low_stock_color            = $settings['mep_cal_low_stock_color'];
			$low_threshold              = intval( $settings['mep_cal_low_stock_threshold'] );

			$hide_expired               = $hide_expired === 'yes' ? 'yes' : 'no';
			$show_expired_events        = $show_expired_events === 'no' ? 'no' : 'yes';
			$include_expired_in_results = ( $hide_expired !== 'yes' && $show_expired_events === 'yes' );
			$query_status               = $status;
			$expire_on                  = $this->get_expire_mode();

			if ( $query_status === 'upcoming' && $include_expired_in_results ) {
				$query_status = 'all';
			}

			$args = array(
				'post_type'           => array( 'mep_events' ),
				'posts_per_page'      => $limit,
				'post_status'         => array( 'publish' ),
				'order'               => 'ASC',
				'orderby'             => 'meta_value',
				'meta_key'            => 'event_start_datetime',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'fields'              => 'ids',
			);

			$allowed_sources = array( 'all', 'single', 'multi_date', 'repeated', 'specific' );
			if ( ! in_array( $event_source, $allowed_sources, true ) ) {
				$event_source = 'all';
			}

			if ( 'specific' === $event_source ) {
				$specific_ids = $this->parse_event_ids( $specific_events );
				if ( empty( $specific_ids ) ) {
					wp_send_json_success( array() );
				}
				$args['post__in'] = $specific_ids;
				$args['orderby']  = 'post__in';
			}

			if ( ! empty( $search ) ) {
				$args['s'] = $search;
			}

			$tax_query = array();
			if ( ! empty( $effective_cat ) && $effective_cat !== '0' ) {
				$terms = explode( ',', $effective_cat );
				$ids   = array();
				$slugs = array();
				foreach ( $terms as $term ) {
					$term = trim( $term );
					if ( is_numeric( $term ) ) {
						$ids[] = intval( $term );
					} elseif ( ! empty( $term ) ) {
						$slugs[] = $term;
					}
				}
				if ( ! empty( $ids ) ) {
					$tax_query[] = array( 'taxonomy' => 'mep_cat', 'field' => 'term_id', 'terms' => $ids );
				}
				if ( ! empty( $slugs ) ) {
					$tax_query[] = array(
						'relation' => 'OR',
						array( 'taxonomy' => 'mep_cat', 'field' => 'slug', 'terms' => $slugs ),
						array( 'taxonomy' => 'mep_cat', 'field' => 'name', 'terms' => $slugs ),
					);
				}
			}

			if ( ! empty( $effective_org ) && $effective_org !== '0' ) {
				if ( is_numeric( $effective_org ) ) {
					$tax_query[] = array( 'taxonomy' => 'mep_org', 'field' => 'term_id', 'terms' => array( intval( $effective_org ) ) );
				} else {
					$tax_query[] = array(
						'relation' => 'OR',
						array( 'taxonomy' => 'mep_org', 'field' => 'slug', 'terms' => array( $effective_org ) ),
						array( 'taxonomy' => 'mep_org', 'field' => 'name', 'terms' => array( $effective_org ) ),
					);
				}
			}

			if ( ! empty( $tag ) && $tag !== '0' ) {
				if ( is_numeric( $tag ) ) {
					$tax_query[] = array( 'taxonomy' => 'mep_tag', 'field' => 'term_id', 'terms' => array( intval( $tag ) ) );
				} else {
					$tax_query[] = array(
						'relation' => 'OR',
						array( 'taxonomy' => 'mep_tag', 'field' => 'slug', 'terms' => array( $tag ) ),
						array( 'taxonomy' => 'mep_tag', 'field' => 'name', 'terms' => array( $tag ) ),
					);
				}
			}

			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			if ( ! empty( $tax_query ) ) {
				$args['tax_query'] = $tax_query;
			}

			$meta_query = array();
			if ( ! empty( $city ) ) {
				$meta_query[] = array( 'key' => 'mep_city', 'value' => $city, 'compare' => 'LIKE' );
			}
			if ( ! empty( $country ) ) {
				$meta_query[] = array( 'key' => 'mep_country', 'value' => $country, 'compare' => 'LIKE' );
			}
			if ( 'single' === $event_source ) {
				$meta_query[] = array(
					'relation' => 'OR',
					array( 'key' => 'mep_enable_recurring', 'value' => 'no', 'compare' => '=' ),
					array( 'key' => 'mep_enable_recurring', 'compare' => 'NOT EXISTS' ),
				);
			} elseif ( 'multi_date' === $event_source ) {
				$meta_query[] = array( 'key' => 'mep_enable_recurring', 'value' => 'yes', 'compare' => '=' );
			} elseif ( 'repeated' === $event_source ) {
				$meta_query[] = array( 'key' => 'mep_enable_recurring', 'value' => 'everyday', 'compare' => '=' );
			}
			if ( ! empty( $location_filter ) ) {
				$meta_query[] = array(
					'relation' => 'OR',
					array( 'key' => 'mep_location_venue', 'value' => $location_filter, 'compare' => 'LIKE' ),
					array( 'key' => 'mep_city', 'value' => $location_filter, 'compare' => 'LIKE' ),
					array( 'key' => 'mep_country', 'value' => $location_filter, 'compare' => 'LIKE' ),
				);
			}

			if ( $query_status === 'upcoming' || $query_status === 'expired' ) {
				$now        = current_time( 'Y-m-d H:i:s' );
				$expire_key = function_exists( 'mep_get_option' ) ? mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' ) : 'event_start_datetime';
				$expire_key = $expire_key == 'event_end_datetime' ? 'event_end_datetime' : 'event_upcoming_datetime';
				$compare    = $query_status === 'expired' ? '<' : '>';
				$meta_query[] = array(
					'key'     => $expire_key,
					'value'   => $now,
					'compare' => $compare,
					'type'    => 'DATETIME',
				);
			}

			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}
			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = isset( $args['meta_query'] ) ? array_merge( $args['meta_query'], $meta_query ) : $meta_query;
			}

			$args   = apply_filters( 'mep_calendar_query_args', $args );
			$loop   = new WP_Query( $args );
			$events = array();
			$now    = current_time( 'Y-m-d H:i:s' );
			$event_ids = array_map( 'intval', (array) $loop->posts );
			$needs_tooltip_meta = 'yes' !== $hide_tooltip;

			if ( ! empty( $event_ids ) ) {
				update_meta_cache( 'post', $event_ids );
				update_object_term_cache( $event_ids, 'mep_events' );
			}

			foreach ( $event_ids as $event_id ) {
				$title      = get_the_title( $event_id );
				$url        = get_permalink( $event_id );
				$event_type = class_exists( 'MPWEM_Global_Function' )
					? MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' )
					: get_post_meta( $event_id, 'mep_enable_recurring', true );
				$event_type = $event_type ? $event_type : 'no';

				$all_dates = $this->get_event_dates_for_range( $event_id, $event_type, $effective_date_start, $effective_date_end );

				$location_text = '';
				if ( $needs_tooltip_meta && $show_location === 'yes' && class_exists( 'MPWEM_Functions' ) ) {
					$location = MPWEM_Functions::get_location( $event_id );
					if ( is_array( $location ) && ! empty( $location ) ) {
						$parts = array();
						if ( ! empty( $location['location'] ) ) $parts[] = $location['location'];
						if ( ! empty( $location['city'] ) )     $parts[] = $location['city'];
						if ( ! empty( $location['country'] ) )  $parts[] = $location['country'];
						$location_text = implode( ', ', $parts );
					}
				}

				$organizer_name = '';
				if ( $needs_tooltip_meta && $show_organizer === 'yes' ) {
					$org_terms = get_the_terms( $event_id, 'mep_org' );
					if ( ! empty( $org_terms ) && ! is_wp_error( $org_terms ) ) {
						$organizer_name = $org_terms[0]->name;
					}
				}

				$cat_names = array();
				if ( $needs_tooltip_meta ) {
					$cat_terms = get_the_terms( $event_id, 'mep_cat' );
					if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
						foreach ( $cat_terms as $cat_term ) {
							$cat_names[] = $cat_term->name;
						}
					}
				}

				$min_price = 0;
				if ( $needs_tooltip_meta && $show_price === 'yes' && class_exists( 'MPWEM_Functions' ) ) {
					$min_price = MPWEM_Functions::get_min_price( $event_id );
				}

				$event_mode = get_post_meta( $event_id, 'mep_event_type', true );
				$event_mode = $event_mode ? $event_mode : 'offline';

				$thumbnail  = $needs_tooltip_meta ? get_the_post_thumbnail_url( $event_id, 'thumbnail' ) : '';
				$reg_status = get_post_meta( $event_id, 'mep_available_seat', true );
				$reg_status = $reg_status ? $reg_status : 'on';

				if ( $event_type === 'no' || $event_type === 'yes' ) {
					if ( ! empty( $all_dates ) ) {
						foreach ( $all_dates as $date_entry ) {
							if ( ! is_array( $date_entry ) || ! isset( $date_entry['time'] ) ) {
								continue;
							}
							$start_dt = $date_entry['time'];
							$end_dt   = isset( $date_entry['end'] ) ? $date_entry['end'] : $start_dt;

							$is_date_expired = $this->is_event_instance_expired( $start_dt, $end_dt, $now, $expire_on );

							if ( ! $this->date_matches_range( $start_dt, $end_dt, $effective_date_start, $effective_date_end ) ) {
								continue;
							}

							if ( $hide_expired === 'yes' && $is_date_expired ) {
								continue;
							}
							if ( $show_expired_events === 'no' && $is_date_expired ) {
								continue;
							}

							foreach ( $this->build_event_instances(
								$event_id, $title, $url, $start_dt, $end_dt,
								$event_type, $event_mode, $reg_status,
								$location_text, $organizer_name, $cat_names, $min_price, $thumbnail,
								$event_color, $text_color, $sold_out_color, $low_stock_color, $low_threshold,
								$show_stock, $hide_time, $show_price, $show_location, $show_organizer,
								$is_date_expired, $expired_event_color, $split_multi_day
							) as $event_data ) {
								$events[] = apply_filters( 'mep_calendar_event_data', $event_data, $event_id );
							}
						}
					}
				} else {
					if ( ! empty( $all_dates ) ) {
						foreach ( $all_dates as $date_str ) {
							if ( is_array( $date_str ) ) {
								continue;
							}

							$times = array();
							if ( class_exists( 'MPWEM_Functions' ) ) {
								$times = MPWEM_Functions::get_times( $event_id, $all_dates, $date_str );
							}

							if ( ! empty( $times ) ) {
								foreach ( $times as $time_slot ) {
									$start_time = isset( $time_slot['start']['time'] ) ? $time_slot['start']['time'] : '';
									$end_time   = isset( $time_slot['end']['time'] ) ? $time_slot['end']['time'] : '';

									$start_dt = $start_time ? $date_str . ' ' . $start_time : $date_str;
									$end_dt   = $end_time ? $date_str . ' ' . $end_time : $start_dt;

									if ( ! $this->date_matches_range( $start_dt, $end_dt, $effective_date_start, $effective_date_end ) ) {
										continue;
									}

									$is_date_expired_r = $this->is_event_instance_expired( $start_dt, $end_dt, $now, $expire_on );

									if ( $hide_expired === 'yes' && $is_date_expired_r ) {
										continue;
									}
									if ( $show_expired_events === 'no' && $is_date_expired_r ) {
										continue;
									}

									foreach ( $this->build_event_instances(
										$event_id, $title, $url, $start_dt, $end_dt,
										$event_type, $event_mode, $reg_status,
										$location_text, $organizer_name, $cat_names, $min_price, $thumbnail,
										$event_color, $text_color, $sold_out_color, $low_stock_color, $low_threshold,
										$show_stock, $hide_time, $show_price, $show_location, $show_organizer,
										$is_date_expired_r, $expired_event_color, $split_multi_day
									) as $event_data ) {
										$events[] = apply_filters( 'mep_calendar_event_data', $event_data, $event_id );
									}
								}
							} else {
								$start_time = get_post_meta( $event_id, 'event_start_time', true );
								$end_time   = get_post_meta( $event_id, 'event_end_time', true );

								$start_dt = $start_time ? $date_str . ' ' . $start_time : $date_str;
								$end_dt   = $end_time ? $date_str . ' ' . $end_time : $start_dt;

								if ( ! $this->date_matches_range( $start_dt, $end_dt, $effective_date_start, $effective_date_end ) ) {
									continue;
								}

								$is_date_expired_f = $this->is_event_instance_expired( $start_dt, $end_dt, $now, $expire_on );

								if ( $hide_expired === 'yes' && $is_date_expired_f ) {
									continue;
								}
								if ( $show_expired_events === 'no' && $is_date_expired_f ) {
									continue;
								}

								foreach ( $this->build_event_instances(
									$event_id, $title, $url, $start_dt, $end_dt,
									$event_type, $event_mode, $reg_status,
									$location_text, $organizer_name, $cat_names, $min_price, $thumbnail,
									$event_color, $text_color, $sold_out_color, $low_stock_color, $low_threshold,
									$show_stock, $hide_time, $show_price, $show_location, $show_organizer,
									$is_date_expired_f, $expired_event_color, $split_multi_day
								) as $event_data ) {
									$events[] = apply_filters( 'mep_calendar_event_data', $event_data, $event_id );
								}
							}
						}
					}
				}

				if ( empty( $all_dates ) ) {
					$fallback_start = get_post_meta( $event_id, 'event_start_datetime', true );
					$fallback_end   = get_post_meta( $event_id, 'event_end_datetime', true );
					if ( $fallback_start ) {
						$fallback_end = $fallback_end ? $fallback_end : $fallback_start;

						if ( ! $this->date_matches_range( $fallback_start, $fallback_end, $effective_date_start, $effective_date_end ) ) {
							continue;
						}

						$is_fb_expired = $this->is_event_instance_expired( $fallback_start, $fallback_end, $now, $expire_on );

						if ( ! ( $hide_expired === 'yes' && $is_fb_expired ) ) {
							if ( ! ( $show_expired_events === 'no' && $is_fb_expired ) ) {
								foreach ( $this->build_event_instances(
									$event_id, $title, $url, $fallback_start, $fallback_end,
									$event_type, $event_mode, $reg_status,
									$location_text, $organizer_name, $cat_names, $min_price, $thumbnail,
									$event_color, $text_color, $sold_out_color, $low_stock_color, $low_threshold,
									$show_stock, $hide_time, $show_price, $show_location, $show_organizer,
									$is_fb_expired, $expired_event_color, $split_multi_day
								) as $event_data ) {
									$events[] = apply_filters( 'mep_calendar_event_data', $event_data, $event_id );
								}
							}
						}
					}
				}
			}

			wp_send_json_success( $events );
		}

		private function get_expire_mode() {
			$expire_on = function_exists( 'mep_get_option' ) ? mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' ) : 'event_start_datetime';
			return $expire_on === 'event_end_datetime' ? 'event_end_datetime' : 'event_start_datetime';
		}

		private function is_event_instance_expired( $start_dt, $end_dt, $now, $expire_on = 'event_start_datetime' ) {
			$reference_dt = $expire_on === 'event_end_datetime' ? $end_dt : $start_dt;
			$reference_ts = strtotime( $reference_dt ? $reference_dt : $end_dt );
			$now_ts       = strtotime( $now );

			if ( ! $reference_ts || ! $now_ts ) {
				return false;
			}

			return $reference_ts < $now_ts;
		}

		public function get_event_stock() {
			check_ajax_referer( 'mep_calendar_nonce', 'nonce' );

			$event_id   = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
			$event_date = isset( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : '';
			$show_price = isset( $_POST['show_price'] ) ? sanitize_text_field( wp_unslash( $_POST['show_price'] ) ) : 'no';

			if ( ! $event_id || empty( $event_date ) ) {
				wp_send_json_error( array( 'message' => 'Missing stock parameters.' ), 400 );
			}

			$stock_info = $this->get_stock_info( $event_id, $event_date );

			wp_send_json_success(
				array(
					'totalSeats'     => $stock_info['total'],
					'availableSeats' => $stock_info['available'],
					'soldSeats'      => $stock_info['sold'],
					'reservedSeats'  => $stock_info['reserved'],
					'ticketTypes'    => $stock_info['ticket_types'],
					'stockLoaded'    => 'yes',
					'minPriceHtml'   => $show_price === 'yes' && function_exists( 'wc_price' ) ? $this->normalize_text_value( wc_price( MPWEM_Functions::get_min_price( $event_id ) ) ) : '',
				)
			);
		}

		private function build_event_data(
			$event_id, $title, $url, $start_dt, $end_dt,
			$event_type, $event_mode, $reg_status,
			$location_text, $organizer_name, $cat_names, $min_price, $thumbnail,
			$event_color, $text_color, $sold_out_color, $low_stock_color, $low_threshold,
			$show_stock, $hide_time, $show_price, $show_location, $show_organizer,
			$is_expired = false, $expired_event_color = '#999999'
		) {
			$total_seats     = 0;
			$available_seats = 0;
			$total_sold      = 0;
			$total_reserved  = 0;
			$ticket_details  = array();

			if ( $show_stock === 'yes' ) {
				$stock_info      = $this->get_stock_info( $event_id, $start_dt );
				$total_seats     = $stock_info['total'];
				$available_seats = $stock_info['available'];
				$total_sold      = $stock_info['sold'];
				$total_reserved  = $stock_info['reserved'];
				$ticket_details  = $stock_info['ticket_types'];
			} elseif ( $show_price === 'yes' ) {
				$ticket_details = $this->get_ticket_price_info( $event_id );
			}

			$bg_color = $event_color;
			if ( $reg_status === 'on' && $total_seats > 0 ) {
				if ( $available_seats <= 0 ) {
					$bg_color = $sold_out_color;
				} elseif ( $available_seats <= $low_threshold ) {
					$bg_color   = $low_stock_color;
					$text_color = '#333333';
				}
			}

			$bg_color = apply_filters( 'mep_calendar_event_color', $bg_color, $event_id, $available_seats, $total_seats );

			if ( $is_expired ) {
				$bg_color   = $expired_event_color;
				$text_color = '#ffffff';
			}

			$fc_start = date( 'Y-m-d\TH:i:s', strtotime( $start_dt ) );
			$fc_end   = date( 'Y-m-d\TH:i:s', strtotime( $end_dt ) );

			$all_day = false;
			if ( class_exists( 'MPWEM_Global_Function' ) ) {
				$all_day = ! MPWEM_Global_Function::check_time_exit_date( $start_dt );
			}
			if ( $hide_time === 'yes' ) {
				$all_day = true;
			}

			$normalized_title      = $this->normalize_text_value( $title );
			$normalized_location   = $this->normalize_text_value( $location_text );
			$normalized_organizer  = $this->normalize_text_value( $organizer_name );
			$normalized_categories = is_array( $cat_names ) ? array_map( array( $this, 'normalize_text_value' ), $cat_names ) : array();

			return array(
				'id'              => $event_id . '_' . strtotime( $start_dt ),
				'title'           => $normalized_title,
				'start'           => $fc_start,
				'end'             => $fc_end,
				'url'             => $url,
				'backgroundColor' => $bg_color,
				'borderColor'     => $bg_color,
				'textColor'       => $text_color,
				'allDay'          => $all_day,
				'extendedProps'   => array(
					'eventId'                => $event_id,
					'eventType'              => $event_type,
					'eventMode'              => $event_mode,
					'regStatus'              => $reg_status,
					'defaultBackgroundColor' => $bg_color,
					'defaultBorderColor'     => $bg_color,
					'defaultTextColor'       => $text_color,
					'location'               => $normalized_location,
					'organizer'              => $normalized_organizer,
					'categories'             => $normalized_categories,
					'thumbnail'              => $thumbnail ? $thumbnail : '',
					'minPrice'               => $min_price,
					'minPriceHtml'           => $show_price === 'yes' && function_exists( 'wc_price' ) ? $this->normalize_text_value( wc_price( $min_price ) ) : '',
					'totalSeats'             => $total_seats,
					'availableSeats'         => $available_seats,
					'soldSeats'              => $total_sold,
					'reservedSeats'          => $total_reserved,
					'ticketTypes'            => $ticket_details,
					'stockLoaded'            => $show_stock === 'yes' ? 'no' : 'yes',
					'eventDate'              => $this->normalize_stock_date( $start_dt ),
					'showStock'              => $show_stock,
					'showPrice'              => $show_price,
					'showLocation'           => $show_location,
					'showOrganizer'          => $show_organizer,
					'hideTime'               => $hide_time,
					'isExpired'              => $is_expired,
					'expiredBadge'           => $is_expired,
				),
			);
		}

		private function date_matches_range( $start_dt, $end_dt, $date_start = '', $date_end = '' ) {
			if ( empty( $date_start ) && empty( $date_end ) ) {
				return true;
			}

			$start_timestamp = strtotime( $start_dt );
			$end_timestamp   = strtotime( $end_dt ? $end_dt : $start_dt );

			if ( $start_timestamp === false || $end_timestamp === false ) {
				return true;
			}

			if ( ! empty( $date_start ) ) {
				$range_start = strtotime( $date_start . ' 00:00:00' );
				if ( $range_start !== false && $end_timestamp < $range_start ) {
					return false;
				}
			}

			if ( ! empty( $date_end ) ) {
				$range_end = strtotime( $date_end . ' 23:59:59' );
				if ( $range_end !== false && $start_timestamp > $range_end ) {
					return false;
				}
			}

			return true;
		}

		private function normalize_date_boundary( $value, $boundary = 'start' ) {
			if ( empty( $value ) ) {
				return '';
			}
			$timestamp = strtotime( $value );
			if ( false === $timestamp ) {
				return '';
			}
			return 'end' === $boundary ? date( 'Y-m-d', $timestamp ) : date( 'Y-m-d', $timestamp );
		}

		private function parse_event_ids( $value ) {
			$parts = array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );
			$ids   = array();
			foreach ( $parts as $part ) {
				if ( is_numeric( $part ) ) {
					$ids[] = absint( $part );
				}
			}
			return array_values( array_filter( array_unique( $ids ) ) );
		}

		private function normalize_text_value( $value ) {
			$value = wp_strip_all_tags( (string) $value );
			return html_entity_decode( $value, ENT_QUOTES, get_bloginfo( 'charset' ) );
		}

		private function build_event_instances(
			$event_id, $title, $url, $start_dt, $end_dt,
			$event_type, $event_mode, $reg_status,
			$location_text, $organizer_name, $cat_names, $min_price, $thumbnail,
			$event_color, $text_color, $sold_out_color, $low_stock_color, $low_threshold,
			$show_stock, $hide_time, $show_price, $show_location, $show_organizer,
			$is_expired = false, $expired_event_color = '#999999', $split_multi_day = 'no'
		) {
			$instances = array();

			if ( 'yes' !== $split_multi_day || ! $this->spans_multiple_days( $start_dt, $end_dt ) ) {
				$instances[] = $this->build_event_data(
					$event_id, $title, $url, $start_dt, $end_dt,
					$event_type, $event_mode, $reg_status,
					$location_text, $organizer_name, $cat_names, $min_price, $thumbnail,
					$event_color, $text_color, $sold_out_color, $low_stock_color, $low_threshold,
					$show_stock, $hide_time, $show_price, $show_location, $show_organizer,
					$is_expired, $expired_event_color
				);
				return $instances;
			}

			$current_day = strtotime( date( 'Y-m-d', strtotime( $start_dt ) ) );
			$final_day   = strtotime( date( 'Y-m-d', strtotime( $end_dt ) ) );
			$start_day   = date( 'Y-m-d', strtotime( $start_dt ) );
			$end_day     = date( 'Y-m-d', strtotime( $end_dt ) );

			while ( false !== $current_day && $current_day <= $final_day ) {
				$current_date  = date( 'Y-m-d', $current_day );
				$segment_start = $current_date === $start_day ? $start_dt : $current_date . ' 00:00:00';
				$segment_end   = $current_date === $end_day ? $end_dt : $current_date . ' 23:59:59';

				$instances[] = $this->build_event_data(
					$event_id, $title, $url, $segment_start, $segment_end,
					$event_type, $event_mode, $reg_status,
					$location_text, $organizer_name, $cat_names, $min_price, $thumbnail,
					$event_color, $text_color, $sold_out_color, $low_stock_color, $low_threshold,
					$show_stock, $hide_time, $show_price, $show_location, $show_organizer,
					$is_expired, $expired_event_color
				);

				$current_day = strtotime( '+1 day', $current_day );
			}

			return $instances;
		}

		private function spans_multiple_days( $start_dt, $end_dt ) {
			return date( 'Y-m-d', strtotime( $start_dt ) ) !== date( 'Y-m-d', strtotime( $end_dt ) );
		}

		private function get_event_dates_for_range( $event_id, $event_type, $range_start = '', $range_end = '' ) {
			if ( 'everyday' === $event_type ) {
				return $this->get_repeated_dates_for_range( $event_id, $range_start, $range_end );
			}
			return $this->get_single_and_multi_dates_for_range( $event_id, $event_type, $range_start, $range_end );
		}

		private function get_single_and_multi_dates_for_range( $event_id, $event_type, $range_start = '', $range_end = '' ) {
			$dates = array();
			$count = 0;

			$start_date = get_post_meta( $event_id, 'event_start_date', true );
			$start_time = get_post_meta( $event_id, 'event_start_time', true );
			$end_date   = get_post_meta( $event_id, 'event_end_date', true );
			$end_time   = get_post_meta( $event_id, 'event_end_time', true );

			$start_date_time = $start_time ? $start_date . ' ' . $start_time : $start_date;
			$end_date_time   = $end_time ? $end_date . ' ' . $end_time : $end_date;

			if ( $start_date_time && $end_date_time && $this->date_matches_range( $start_date_time, $end_date_time, $range_start, $range_end ) ) {
				$dates[ $count ] = array(
					'time' => $start_date_time,
					'end'  => $end_date_time,
				);
			}

			if ( 'yes' === $event_type ) {
				$more_dates = get_post_meta( $event_id, 'mep_event_more_date', true );
				if ( is_array( $more_dates ) && ! empty( $more_dates ) ) {
					foreach ( $more_dates as $more_date ) {
						$more_start_date      = isset( $more_date['event_more_start_date'] ) ? $more_date['event_more_start_date'] : '';
						$more_start_time      = isset( $more_date['event_more_start_time'] ) ? $more_date['event_more_start_time'] : '';
						$more_start_date_time = $more_start_time ? $more_start_date . ' ' . $more_start_time : $more_start_date;
						$more_end_date        = isset( $more_date['event_more_end_date'] ) ? $more_date['event_more_end_date'] : '';
						$more_end_time        = isset( $more_date['event_more_end_time'] ) ? $more_date['event_more_end_time'] : '';
						$more_end_date_time   = $more_end_time ? $more_end_date . ' ' . $more_end_time : $more_end_date;

						if ( ! $more_start_date_time || ! $more_end_date_time ) {
							continue;
						}
						if ( strtotime( $more_start_date_time ) >= strtotime( $more_end_date_time ) ) {
							continue;
						}
						if ( ! $this->date_matches_range( $more_start_date_time, $more_end_date_time, $range_start, $range_end ) ) {
							continue;
						}

						$count++;
						$dates[ $count ] = array(
							'time' => $more_start_date_time,
							'end'  => $more_end_date_time,
						);
					}
				}
			}

			if ( count( $dates ) > 1 ) {
				usort( $dates, array( $this, 'sort_event_date_entries' ) );
			}

			return $dates;
		}

		private function get_repeated_dates_for_range( $event_id, $range_start = '', $range_end = '' ) {
			$all_dates      = array();
			$start_date     = get_post_meta( $event_id, 'event_start_date', true );
			$end_date       = get_post_meta( $event_id, 'event_end_date', true );
			$repeated_after = max( 1, absint( get_post_meta( $event_id, 'mep_repeated_periods', true ) ) );

			if ( $start_date && $end_date && strtotime( $end_date ) >= strtotime( $start_date ) ) {
				$window_start = $range_start ? max( strtotime( $start_date ), strtotime( $range_start ) ) : strtotime( $start_date );
				$window_end   = $range_end ? min( strtotime( $end_date ), strtotime( $range_end ) ) : strtotime( $end_date );

				if ( $window_start <= $window_end ) {
					$all_off_dates_raw = get_post_meta( $event_id, 'mep_ticket_off_dates', true );
					$all_off_days      = get_post_meta( $event_id, 'mep_ticket_offdays', true );
					$off_dates         = array();
					$off_days          = is_array( $all_off_days ) ? $all_off_days : array();

					if ( is_array( $all_off_dates_raw ) ) {
						foreach ( $all_off_dates_raw as $off_date ) {
							$current_off_date = is_array( $off_date ) ? current( $off_date ) : $off_date;
							if ( $current_off_date ) {
								$off_dates[] = date( 'Y-m-d', strtotime( $current_off_date ) );
							}
						}
					}

					$current_timestamp = $window_start;
					while ( $current_timestamp <= $window_end ) {
						$date = date( 'Y-m-d', $current_timestamp );
						$day  = strtolower( date( 'D', $current_timestamp ) );
						if ( ! in_array( $date, $off_dates, true ) && ! in_array( $day, $off_days, true ) ) {
							$all_dates[] = $date;
						}
						$current_timestamp = strtotime( '+' . $repeated_after . ' day', $current_timestamp );
					}
				}
			}

			$special_dates = get_post_meta( $event_id, 'mep_special_date_info', true );
			if ( is_array( $special_dates ) && ! empty( $special_dates ) ) {
				foreach ( $special_dates as $special_date ) {
					$special_start = isset( $special_date['start_date'] ) ? $special_date['start_date'] : '';
					if ( ! $special_start ) {
						continue;
					}
					if ( $range_start && strtotime( $special_start ) < strtotime( $range_start ) ) {
						continue;
					}
					if ( $range_end && strtotime( $special_start ) > strtotime( $range_end ) ) {
						continue;
					}
					$all_dates[] = date( 'Y-m-d', strtotime( $special_start ) );
				}
			}

			$all_dates = array_values( array_unique( array_filter( $all_dates ) ) );
			sort( $all_dates );

			return $all_dates;
		}

		private function sort_event_date_entries( $left, $right ) {
			$left_time  = isset( $left['time'] ) ? strtotime( $left['time'] ) : 0;
			$right_time = isset( $right['time'] ) ? strtotime( $right['time'] ) : 0;
			if ( $left_time === $right_time ) {
				return 0;
			}
			return $left_time < $right_time ? -1 : 1;
		}

		private function get_stock_info( $event_id, $date ) {
			$result = array(
				'total'        => 0,
				'available'    => 0,
				'sold'         => 0,
				'reserved'     => 0,
				'ticket_types' => array(),
			);

			$event_date = $this->normalize_stock_date( $date );

			$ticket_types = get_post_meta( $event_id, 'mep_event_ticket_type', true );
			if ( ! is_array( $ticket_types ) || empty( $ticket_types ) ) {
				return $result;
			}

			$total_ticket             = 0;
			$total_reserved           = 0;
			$total_sold               = 0;
			$total_available_visible  = class_exists( 'MPWEM_Functions' ) ? (int) MPWEM_Functions::get_total_available_seat( $event_id, $event_date ) : 0;

			foreach ( $ticket_types as $ticket ) {
				$is_enabled = isset( $ticket['option_ticket_enable'] ) ? $ticket['option_ticket_enable'] : 'yes';
				if ( 'yes' !== $is_enabled ) {
					continue;
				}

				$name  = isset( $ticket['option_name_t'] ) ? $ticket['option_name_t'] : '';
				$qty   = isset( $ticket['option_qty_t'] ) ? intval( $ticket['option_qty_t'] ) : 0;
				$rsv   = isset( $ticket['option_rsv_t'] ) ? intval( $ticket['option_rsv_t'] ) : 0;
				$price = isset( $ticket['option_price_t'] ) ? $ticket['option_price_t'] : 0;

				if ( empty( $name ) ) {
					continue;
				}

				if ( ! $this->should_include_ticket_in_calendar( $event_id, $ticket, $event_date ) ) {
					continue;
				}

				$sold = 0;
				if ( function_exists( 'mep_ticket_type_sold' ) ) {
					$sold = (int) mep_ticket_type_sold( $event_id, $name, $event_date );
				} elseif ( class_exists( 'MPWEM_Query' ) ) {
					$filter_args = array(
						'post_id'        => $event_id,
						'event_date'     => $event_date,
						'ea_ticket_type' => $name,
					);
					$sold = (int) MPWEM_Query::attendee_query( $filter_args )->post_count;
				}

				$available = class_exists( 'MPWEM_Functions' )
					? (int) MPWEM_Functions::get_available_ticket( $event_id, $name, $event_date, $ticket )
					: max( $qty - ( $sold + $rsv ), 0 );

				$available = apply_filters( 'filter_mpwem_gq_ticket', $available, $total_available_visible, $event_id );
				$available = apply_filters( 'mpwem_group_ticket_qty', $available, $event_id, $name );
				$available = max( 0, (int) floor( $available ) );

				$total_ticket   += $qty;
				$total_reserved += $rsv;
				$total_sold     += $sold;

				$result['ticket_types'][] = array(
					'name'      => $name,
					'total'     => $qty,
					'sold'      => $sold,
					'reserved'  => $rsv,
					'available' => $available,
					'price'     => $price,
					'priceHtml' => function_exists( 'wc_price' ) ? $this->normalize_text_value( wc_price( $price ) ) : $this->normalize_text_value( $price ),
				);
			}

			$result['total']     = $total_ticket;
			$result['sold']      = $total_sold;
			$result['reserved']  = $total_reserved;
			$result['available'] = max( $total_ticket - ( $total_sold + $total_reserved ), 0 );

			return $result;
		}

		private function get_ticket_price_info( $event_id ) {
			$result = array();
			$ticket_types = get_post_meta( $event_id, 'mep_event_ticket_type', true );

			if ( ! is_array( $ticket_types ) || empty( $ticket_types ) ) {
				return $result;
			}

			foreach ( $ticket_types as $ticket ) {
				$is_enabled = isset( $ticket['option_ticket_enable'] ) ? $ticket['option_ticket_enable'] : 'yes';
				if ( 'yes' !== $is_enabled ) {
					continue;
				}

				$name  = isset( $ticket['option_name_t'] ) ? $this->normalize_text_value( $ticket['option_name_t'] ) : '';
				$price = isset( $ticket['option_price_t'] ) ? $ticket['option_price_t'] : 0;

				if ( empty( $name ) ) {
					continue;
				}

				if ( ! $this->should_include_ticket_in_calendar( $event_id, $ticket ) ) {
					continue;
				}

				$result[] = array(
					'name'      => $name,
					'total'     => 0,
					'sold'      => 0,
					'reserved'  => 0,
					'available' => 0,
					'price'     => $price,
					'priceHtml' => function_exists( 'wc_price' ) ? $this->normalize_text_value( wc_price( $price ) ) : $this->normalize_text_value( $price ),
				);
			}

			return $result;
		}

		private function normalize_stock_date( $date ) {
			$date = trim( (string) $date );
			if ( empty( $date ) ) {
				return '';
			}
			return class_exists( 'MPWEM_Global_Function' ) && MPWEM_Global_Function::check_time_exit_date( $date )
				? date( 'Y-m-d H:i', strtotime( $date ) )
				: date( 'Y-m-d', strtotime( $date ) );
		}

		private function should_include_ticket_in_calendar( $event_id, $ticket, $event_date = '' ) {
			if ( ! is_array( $ticket ) ) {
				return false;
			}

			$ticket_permission = apply_filters( 'mpwem_ticket_permission', true, $ticket );
			if ( ! $ticket_permission ) {
				return false;
			}

			$ticket_name = isset( $ticket['option_name_t'] ) ? trim( (string) $ticket['option_name_t'] ) : '';
			if ( '' === $ticket_name ) {
				return false;
			}

			$mep_hide_expire_ticket = function_exists( 'mep_get_option' ) ? mep_get_option( 'mep_hide_expire_ticket', 'general_setting_sec', 'no' ) : 'no';
			$early_date             = apply_filters( 'mpwem_early_date', true, $ticket, $event_id );

			if ( $early_date ) {
				$sale_end_datetime = isset( $ticket['option_sale_end_date_t'] ) && ! empty( $ticket['option_sale_end_date_t'] )
					? date( 'Y-m-d H:i', strtotime( $ticket['option_sale_end_date_t'] ) )
					: '';

				if ( $sale_end_datetime ) {
					$current_time = current_time( 'Y-m-d H:i' );
					if ( strtotime( $current_time ) > strtotime( $sale_end_datetime ) && 'no' === $mep_hide_expire_ticket ) {
						return false;
					}
				}
			}

			return true;
		}
	}

	new MPWEM_Calendar_Ajax();
}


// =============================================================================
// ADMIN SETTINGS
// =============================================================================

if ( ! class_exists( 'MPWEM_Calendar_Settings' ) ) {
	class MPWEM_Calendar_Settings {

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_head', array( $this, 'hide_unrelated_notices_on_settings_page' ) );
		}

		public function add_settings_menu() {
			add_submenu_page(
				'edit.php?post_type=mep_events',
				__( 'Calendar Settings', 'mage-eventpress' ),
				__( 'Calendar Settings', 'mage-eventpress' ),
				'manage_options',
				'mep_calendar_settings',
				array( $this, 'render_settings_page' )
			);
		}

		public function register_settings() {
			register_setting( 'mep_calendar_settings_group', 'mep_calendar_settings', array( $this, 'sanitize_settings' ) );
		}

		public function sanitize_settings( $input ) {
			if ( ! is_array( $input ) ) {
				return array();
			}

			$sanitized    = array();
			$color_fields = array(
				'mep_cal_event_color', 'mep_cal_event_text_color', 'mep_cal_today_color',
				'mep_cal_header_bg', 'mep_cal_header_text', 'mep_cal_border_color',
				'mep_cal_sold_out_color', 'mep_cal_low_stock_color', 'mep_cal_expired_event_color',
				'mep_cal_weekday_bg_sun', 'mep_cal_weekday_bg_mon', 'mep_cal_weekday_bg_tue',
				'mep_cal_weekday_bg_wed', 'mep_cal_weekday_bg_thu', 'mep_cal_weekday_bg_fri',
				'mep_cal_weekday_bg_sat'
			);

			foreach ( $input as $key => $value ) {
				if ( in_array( $key, $color_fields, true ) ) {
					$sanitized[ $key ] = sanitize_hex_color( $value );
				} elseif ( 'mep_cal_day_background_rules' === $key ) {
					$sanitized[ $key ] = $this->sanitize_day_background_rules( $value );
				} elseif ( in_array( $key, array( 'mep_cal_calendar_width', 'mep_cal_calendar_height' ), true ) ) {
					$sanitized[ $key ] = $this->sanitize_dimension( $value, $key === 'mep_cal_calendar_width' ? '100%' : 'auto' );
				} elseif ( 'mep_cal_event_source' === $key ) {
					$allowed_sources   = array( 'all', 'single', 'multi_date', 'repeated', 'specific' );
					$sanitized[ $key ] = in_array( $value, $allowed_sources, true ) ? $value : 'all';
				} elseif ( 'mep_cal_specific_events' === $key ) {
					$sanitized[ $key ] = $this->sanitize_event_ids( $value );
				} elseif ( $key === 'mep_cal_low_stock_threshold' ) {
					$sanitized[ $key ] = absint( $value );
				} else {
					$sanitized[ $key ] = sanitize_text_field( $value );
				}
			}

			return $sanitized;
		}

		public function render_settings_page() {
			$settings = mep_cal_get_all_settings();
			?>
			<div class="wrap mep-calendar-settings-wrap mpwem_style mep_settings_wrapper">
				<h1><span class="dashicons dashicons-calendar-alt" style="font-size:28px;margin-right:8px;"></span><?php esc_html_e( 'Event Calendar Settings', 'mage-eventpress' ); ?></h1>
				<p class="description"><?php esc_html_e( 'Configure the appearance and behavior of the event calendar.', 'mage-eventpress' ); ?></p>

				<?php do_action( 'mep_calendar_settings_before' ); ?>

				<form method="post" action="options.php">
					<?php settings_fields( 'mep_calendar_settings_group' ); ?>

					<!-- General Settings -->
					<div class="mep-cal-settings-section">
						<h2><?php esc_html_e( 'General Settings', 'mage-eventpress' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="mep_cal_default_view"><?php esc_html_e( 'Default View', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_default_view]" id="mep_cal_default_view">
										<option value="dayGridMonth" <?php selected( $settings['mep_cal_default_view'], 'dayGridMonth' ); ?>><?php esc_html_e( 'Month Grid', 'mage-eventpress' ); ?></option>
										<option value="timeGridWeek" <?php selected( $settings['mep_cal_default_view'], 'timeGridWeek' ); ?>><?php esc_html_e( 'Week Grid', 'mage-eventpress' ); ?></option>
										<option value="timeGridDay" <?php selected( $settings['mep_cal_default_view'], 'timeGridDay' ); ?>><?php esc_html_e( 'Day Grid', 'mage-eventpress' ); ?></option>
										<option value="listMonth" <?php selected( $settings['mep_cal_default_view'], 'listMonth' ); ?>><?php esc_html_e( 'List View', 'mage-eventpress' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_first_day"><?php esc_html_e( 'First Day of Week', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_first_day]" id="mep_cal_first_day">
										<option value="0" <?php selected( $settings['mep_cal_first_day'], '0' ); ?>><?php esc_html_e( 'Sunday', 'mage-eventpress' ); ?></option>
										<option value="1" <?php selected( $settings['mep_cal_first_day'], '1' ); ?>><?php esc_html_e( 'Monday', 'mage-eventpress' ); ?></option>
										<option value="6" <?php selected( $settings['mep_cal_first_day'], '6' ); ?>><?php esc_html_e( 'Saturday', 'mage-eventpress' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_show_tooltip"><?php esc_html_e( 'Show Tooltip on Hover', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_show_tooltip]" id="mep_cal_show_tooltip">
										<option value="yes" <?php selected( $settings['mep_cal_show_tooltip'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $settings['mep_cal_show_tooltip'], 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_hide_expired"><?php esc_html_e( 'Hide Expired Events', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_hide_expired]" id="mep_cal_hide_expired">
										<option value="no" <?php selected( $settings['mep_cal_hide_expired'], 'no' ); ?>><?php esc_html_e( 'No (Show All)', 'mage-eventpress' ); ?></option>
										<option value="yes" <?php selected( $settings['mep_cal_hide_expired'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_locale"><?php esc_html_e( 'Calendar Locale', 'mage-eventpress' ); ?></label></th>
								<td>
									<input type="text" name="mep_calendar_settings[mep_cal_locale]" id="mep_cal_locale" value="<?php echo esc_attr( $settings['mep_cal_locale'] ); ?>" class="regular-text" placeholder="auto (e.g. en, it, de, fr)" />
									<p class="description"><?php esc_html_e( 'Leave empty for auto-detect. Use ISO codes like: en, it, de, fr, es, pt, ar, etc.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_calendar_width"><?php esc_html_e( 'Calendar Width', 'mage-eventpress' ); ?></label></th>
								<td>
									<input type="text" name="mep_calendar_settings[mep_cal_calendar_width]" id="mep_cal_calendar_width" value="<?php echo esc_attr( $settings['mep_cal_calendar_width'] ); ?>" class="regular-text" placeholder="100%" />
									<p class="description"><?php esc_html_e( 'Default frontend width. Examples: 100%, 1200px, 90vw.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_calendar_height"><?php esc_html_e( 'Calendar Height', 'mage-eventpress' ); ?></label></th>
								<td>
									<input type="text" name="mep_calendar_settings[mep_cal_calendar_height]" id="mep_cal_calendar_height" value="<?php echo esc_attr( $settings['mep_cal_calendar_height'] ); ?>" class="regular-text" placeholder="auto" />
									<p class="description"><?php esc_html_e( 'Default frontend height. Examples: auto, 700px, 80vh.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_event_source"><?php esc_html_e( 'Event Source Filter', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_event_source]" id="mep_cal_event_source">
										<option value="all" <?php selected( $settings['mep_cal_event_source'], 'all' ); ?>><?php esc_html_e( 'Show All Events', 'mage-eventpress' ); ?></option>
										<option value="single" <?php selected( $settings['mep_cal_event_source'], 'single' ); ?>><?php esc_html_e( 'Single-Date Events Only', 'mage-eventpress' ); ?></option>
										<option value="multi_date" <?php selected( $settings['mep_cal_event_source'], 'multi_date' ); ?>><?php esc_html_e( 'Multi-Date Events Only', 'mage-eventpress' ); ?></option>
										<option value="repeated" <?php selected( $settings['mep_cal_event_source'], 'repeated' ); ?>><?php esc_html_e( 'Repeated Events Only', 'mage-eventpress' ); ?></option>
										<option value="specific" <?php selected( $settings['mep_cal_event_source'], 'specific' ); ?>><?php esc_html_e( 'Specific Events Only', 'mage-eventpress' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Choose which event type the calendar should show by default.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_specific_events"><?php esc_html_e( 'Specific Event IDs', 'mage-eventpress' ); ?></label></th>
								<td>
									<input type="text" name="mep_calendar_settings[mep_cal_specific_events]" id="mep_cal_specific_events" value="<?php echo esc_attr( $settings['mep_cal_specific_events'] ); ?>" class="regular-text" placeholder="12,15,18" />
									<p class="description"><?php esc_html_e( 'Used when "Specific Events Only" is selected. Add event IDs separated by commas.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Navigation Settings -->
					<div class="mep-cal-settings-section">
						<h2><?php esc_html_e( 'Navigation Settings', 'mage-eventpress' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="mep_cal_show_prev_next"><?php esc_html_e( 'Show Previous/Next Buttons', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_show_prev_next]" id="mep_cal_show_prev_next">
										<option value="yes" <?php selected( $settings['mep_cal_show_prev_next'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $settings['mep_cal_show_prev_next'], 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Show or hide the month prev/next navigation arrows.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_show_year_nav"><?php esc_html_e( 'Show Year Navigation', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_show_year_nav]" id="mep_cal_show_year_nav">
										<option value="yes" <?php selected( $settings['mep_cal_show_year_nav'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $settings['mep_cal_show_year_nav'], 'no' ); ?>><?php esc_html_e( 'No', 'mage-eventpress' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Show previous/next year buttons to jump by year.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Expired Events Settings -->
					<div class="mep-cal-settings-section">
						<h2><?php esc_html_e( 'Expired Event Settings', 'mage-eventpress' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="mep_cal_show_expired_events"><?php esc_html_e( 'Show Expired Events', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_show_expired_events]" id="mep_cal_show_expired_events">
										<option value="yes" <?php selected( $settings['mep_cal_show_expired_events'], 'yes' ); ?>><?php esc_html_e( 'Yes (Show with faded style)', 'mage-eventpress' ); ?></option>
										<option value="no" <?php selected( $settings['mep_cal_show_expired_events'], 'no' ); ?>><?php esc_html_e( 'No (Hide completely)', 'mage-eventpress' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Expired Event Color', 'mage-eventpress' ); ?></label></th>
								<td>
									<input type="text" name="mep_calendar_settings[mep_cal_expired_event_color]" value="<?php echo esc_attr( $settings['mep_cal_expired_event_color'] ); ?>" class="mep-cal-color-picker" />
									<p class="description"><?php esc_html_e( 'Color for expired events when shown on calendar.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_expired_opacity"><?php esc_html_e( 'Expired Event Opacity', 'mage-eventpress' ); ?></label></th>
								<td>
									<select name="mep_calendar_settings[mep_cal_expired_opacity]" id="mep_cal_expired_opacity">
										<option value="1" <?php selected( $settings['mep_cal_expired_opacity'], '1' ); ?>>100%</option>
										<option value="0.8" <?php selected( $settings['mep_cal_expired_opacity'], '0.8' ); ?>>80%</option>
										<option value="0.6" <?php selected( $settings['mep_cal_expired_opacity'], '0.6' ); ?>>60%</option>
										<option value="0.4" <?php selected( $settings['mep_cal_expired_opacity'], '0.4' ); ?>>40%</option>
										<option value="0.3" <?php selected( $settings['mep_cal_expired_opacity'], '0.3' ); ?>>30%</option>
									</select>
									<p class="description"><?php esc_html_e( 'Opacity level for expired events (lower = more faded).', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Color Settings -->
					<div class="mep-cal-settings-section">
						<h2><?php esc_html_e( 'Color Settings', 'mage-eventpress' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Event Background Color', 'mage-eventpress' ); ?></label></th>
								<td><input type="text" name="mep_calendar_settings[mep_cal_event_color]" value="<?php echo esc_attr( $settings['mep_cal_event_color'] ); ?>" class="mep-cal-color-picker" /></td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Event Text Color', 'mage-eventpress' ); ?></label></th>
								<td><input type="text" name="mep_calendar_settings[mep_cal_event_text_color]" value="<?php echo esc_attr( $settings['mep_cal_event_text_color'] ); ?>" class="mep-cal-color-picker" /></td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Today Highlight Color', 'mage-eventpress' ); ?></label></th>
								<td><input type="text" name="mep_calendar_settings[mep_cal_today_color]" value="<?php echo esc_attr( $settings['mep_cal_today_color'] ); ?>" class="mep-cal-color-picker" /></td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Header Background', 'mage-eventpress' ); ?></label></th>
								<td><input type="text" name="mep_calendar_settings[mep_cal_header_bg]" value="<?php echo esc_attr( $settings['mep_cal_header_bg'] ); ?>" class="mep-cal-color-picker" /></td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Header Text Color', 'mage-eventpress' ); ?></label></th>
								<td><input type="text" name="mep_calendar_settings[mep_cal_header_text]" value="<?php echo esc_attr( $settings['mep_cal_header_text'] ); ?>" class="mep-cal-color-picker" /></td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Cell Border Color', 'mage-eventpress' ); ?></label></th>
								<td><input type="text" name="mep_calendar_settings[mep_cal_border_color]" value="<?php echo esc_attr( $settings['mep_cal_border_color'] ); ?>" class="mep-cal-color-picker" /></td>
							</tr>
						</table>
					</div>

					<!-- Weekday Column Styles -->
					<div class="mep-cal-settings-section">
						<h2><?php esc_html_e( 'Weekday Column Styles', 'mage-eventpress' ); ?></h2>
						<table class="form-table">
							<?php foreach ( $this->get_weekday_labels() as $weekday_key => $weekday_label ) : ?>
							<tr>
								<th scope="row">
									<label for="<?php echo esc_attr( 'mep_cal_' . $weekday_key ); ?>">
										<?php echo esc_html( $weekday_label ); ?>
									</label>
								</th>
								<td>
									<input
										type="text"
										id="<?php echo esc_attr( 'mep_cal_' . $weekday_key ); ?>"
										name="mep_calendar_settings[<?php echo esc_attr( 'mep_cal_' . $weekday_key ); ?>]"
										value="<?php echo esc_attr( isset( $settings[ 'mep_cal_' . $weekday_key ] ) ? $settings[ 'mep_cal_' . $weekday_key ] : '' ); ?>"
										class="mep-cal-color-picker"
									/>
									<p class="description">
										<?php esc_html_e( 'Optional background color for this weekday column. It will style the full day column in month/week/day views.', 'mage-eventpress' ); ?>
									</p>
								</td>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>

					<!-- Day Background Images -->
					<div class="mep-cal-settings-section">
						<h2><?php esc_html_e( 'Day Background Images', 'mage-eventpress' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Background Rules', 'mage-eventpress' ); ?></th>
								<td>
									<div class="mep-cal-day-rule-list">
										<p class="description">
											<?php esc_html_e( 'Add background images by weekday or by exact date. Exact date rules will override weekday rules.', 'mage-eventpress' ); ?>
										</p>
										<div class="mep-cal-day-rule-items" data-next-index="<?php echo esc_attr( count( $settings['mep_cal_day_background_rules'] ) ); ?>">
											<?php
											$background_rules = isset( $settings['mep_cal_day_background_rules'] ) && is_array( $settings['mep_cal_day_background_rules'] ) ? $settings['mep_cal_day_background_rules'] : array();
											if ( empty( $background_rules ) ) {
												$this->render_day_background_rule_row( 0 );
											} else {
												foreach ( $background_rules as $rule_index => $rule ) {
													$this->render_day_background_rule_row( $rule_index, $rule );
												}
											}
											?>
										</div>
										<p>
											<button type="button" class="button button-secondary mep-cal-add-day-rule">
												<?php esc_html_e( 'Add Background Rule', 'mage-eventpress' ); ?>
											</button>
										</p>
										<script type="text/template" id="mep-cal-day-rule-template">
											<?php
											ob_start();
											$this->render_day_background_rule_row( '__index__' );
											echo trim( ob_get_clean() );
											?>
										</script>
									</div>
								</td>
							</tr>
						</table>
					</div>

					<!-- Stock Color Settings -->
					<div class="mep-cal-settings-section">
						<h2><?php esc_html_e( 'Stock Indicator Colors', 'mage-eventpress' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Sold Out Event Color', 'mage-eventpress' ); ?></label></th>
								<td><input type="text" name="mep_calendar_settings[mep_cal_sold_out_color]" value="<?php echo esc_attr( $settings['mep_cal_sold_out_color'] ); ?>" class="mep-cal-color-picker" /></td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Low Stock Event Color', 'mage-eventpress' ); ?></label></th>
								<td><input type="text" name="mep_calendar_settings[mep_cal_low_stock_color]" value="<?php echo esc_attr( $settings['mep_cal_low_stock_color'] ); ?>" class="mep-cal-color-picker" /></td>
							</tr>
							<tr>
								<th scope="row"><label for="mep_cal_low_stock_threshold"><?php esc_html_e( 'Low Stock Threshold', 'mage-eventpress' ); ?></label></th>
								<td>
									<input type="number" name="mep_calendar_settings[mep_cal_low_stock_threshold]" id="mep_cal_low_stock_threshold" value="<?php echo esc_attr( $settings['mep_cal_low_stock_threshold'] ); ?>" min="1" max="100" class="small-text" />
									<p class="description"><?php esc_html_e( 'Events with available seats below this number will show the low-stock color.', 'mage-eventpress' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Shortcode Reference -->
					<div class="mep-cal-settings-section">
						<h2><?php esc_html_e( 'Shortcode Reference', 'mage-eventpress' ); ?></h2>
						<div class="mep-cal-shortcode-ref">
							<code>[mep-event-calendar]</code>
							<p><?php esc_html_e( 'Available parameters:', 'mage-eventpress' ); ?></p>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Parameter', 'mage-eventpress' ); ?></th>
										<th><?php esc_html_e( 'Default', 'mage-eventpress' ); ?></th>
										<th><?php esc_html_e( 'Description', 'mage-eventpress' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr><td><code>style</code></td><td>full</td><td><?php esc_html_e( 'Calendar style: full or lite', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_stock_details</code></td><td>no</td><td><?php esc_html_e( 'Show seat availability in tooltip', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>hide_time</code></td><td>no</td><td><?php esc_html_e( 'Hide event time', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>split_multi_day</code></td><td>no</td><td><?php esc_html_e( 'Show multi-day events as separate day blocks instead of one long spanning bar', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>cat</code></td><td>0</td><td><?php esc_html_e( 'Filter by category ID/slug (comma-separated)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>org</code></td><td>0</td><td><?php esc_html_e( 'Filter by organizer ID/slug', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>tag</code></td><td>0</td><td><?php esc_html_e( 'Filter by tag ID/slug', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>city</code></td><td></td><td><?php esc_html_e( 'Filter by city name', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>country</code></td><td></td><td><?php esc_html_e( 'Filter by country', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>status</code></td><td>upcoming</td><td><?php esc_html_e( 'Event status: upcoming, all, expired', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>event_source</code></td><td>(setting)</td><td><?php esc_html_e( 'all, single, multi_date, repeated, specific', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>specific_events</code></td><td>(setting)</td><td><?php esc_html_e( 'Comma-separated event IDs for specific mode', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>default_view</code></td><td>(setting)</td><td><?php esc_html_e( 'dayGridMonth, timeGridWeek, timeGridDay, listMonth', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>first_day</code></td><td>(setting)</td><td><?php esc_html_e( 'First day: 0=Sun, 1=Mon, 6=Sat', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>event_limit</code></td><td>-1</td><td><?php esc_html_e( 'Max events to load (-1 = all)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_category_filter</code></td><td>no</td><td><?php esc_html_e( 'Show category dropdown filter', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_organizer_filter</code></td><td>no</td><td><?php esc_html_e( 'Show organizer dropdown filter', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_location_filter</code></td><td>no</td><td><?php esc_html_e( 'Show location text filter (venue, city, country)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_date_range_filter</code></td><td>no</td><td><?php esc_html_e( 'Show start/end date range filters', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_reset_filters</code></td><td>yes</td><td><?php esc_html_e( 'Show reset button for all active filters', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_search</code></td><td>no</td><td><?php esc_html_e( 'Show search box', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>event_color</code></td><td>(setting)</td><td><?php esc_html_e( 'Override event color (#hex)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>text_color</code></td><td>(setting)</td><td><?php esc_html_e( 'Override text color (#hex)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>width</code></td><td>(setting)</td><td><?php esc_html_e( 'Calendar width (100%, 1200px, 90vw, etc.)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>height</code></td><td>(setting)</td><td><?php esc_html_e( 'Calendar height (auto, 600px, 80vh, etc.)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_price</code></td><td>no</td><td><?php esc_html_e( 'Show min price in tooltip', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_location</code></td><td>no</td><td><?php esc_html_e( 'Show location in tooltip', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_organizer</code></td><td>no</td><td><?php esc_html_e( 'Show organizer in tooltip', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_recurring_badge</code></td><td>yes</td><td><?php esc_html_e( 'Show recurring/multi-date badge', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>hide_tooltip</code></td><td>no</td><td><?php esc_html_e( 'Disable tooltip completely', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_navigation</code></td><td>yes</td><td><?php esc_html_e( 'Show prev/next/today buttons', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_year_nav</code></td><td>(setting)</td><td><?php esc_html_e( 'Show prev/next year buttons', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_prev_next</code></td><td>(setting)</td><td><?php esc_html_e( 'Show prev/next month arrows', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>show_expired_events</code></td><td>(setting)</td><td><?php esc_html_e( 'Show expired events: yes (with faded style) or no (hide)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>expired_event_color</code></td><td>(setting)</td><td><?php esc_html_e( 'Override expired event color (#hex)', 'mage-eventpress' ); ?></td></tr>
									<tr><td><code>expired_opacity</code></td><td>(setting)</td><td><?php esc_html_e( 'Expired event opacity: 0.3 to 1', 'mage-eventpress' ); ?></td></tr>
								</tbody>
							</table>
							<h4><?php esc_html_e( 'Example:', 'mage-eventpress' ); ?></h4>
							<code>[mep-event-calendar event_source="specific" specific_events="12,15,18" style="lite" width="100%" height="700px" show_stock_details="yes" hide_time="yes" split_multi_day="yes" show_price="yes" show_location="yes" cat="5" show_category_filter="yes" show_organizer_filter="yes" show_location_filter="yes" show_date_range_filter="yes" show_search="yes"]</code>
						</div>
					</div>

					<?php do_action( 'mep_calendar_settings_after' ); ?>

					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		public function hide_unrelated_notices_on_settings_page() {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( ! $screen || strpos( $screen->id, 'mep_calendar_settings' ) === false ) {
				return;
			}
			?>
			<style>
				.notice:not(.mep-cal-page-notice),
				.update-nag,
				.updated:not(.mep-cal-page-notice),
				.error:not(.mep-cal-page-notice),
				.is-dismissible:not(.mep-cal-page-notice) {
					display: none !important;
				}
			</style>
			<?php
		}

		private function get_weekday_labels() {
			return array(
				'weekday_bg_sun' => __( 'Sunday Column Color', 'mage-eventpress' ),
				'weekday_bg_mon' => __( 'Monday Column Color', 'mage-eventpress' ),
				'weekday_bg_tue' => __( 'Tuesday Column Color', 'mage-eventpress' ),
				'weekday_bg_wed' => __( 'Wednesday Column Color', 'mage-eventpress' ),
				'weekday_bg_thu' => __( 'Thursday Column Color', 'mage-eventpress' ),
				'weekday_bg_fri' => __( 'Friday Column Color', 'mage-eventpress' ),
				'weekday_bg_sat' => __( 'Saturday Column Color', 'mage-eventpress' ),
			);
		}

		private function get_weekday_options() {
			return array(
				'sun' => __( 'Sunday', 'mage-eventpress' ),
				'mon' => __( 'Monday', 'mage-eventpress' ),
				'tue' => __( 'Tuesday', 'mage-eventpress' ),
				'wed' => __( 'Wednesday', 'mage-eventpress' ),
				'thu' => __( 'Thursday', 'mage-eventpress' ),
				'fri' => __( 'Friday', 'mage-eventpress' ),
				'sat' => __( 'Saturday', 'mage-eventpress' ),
			);
		}

		private function render_day_background_rule_row( $index, $rule = array() ) {
			$type    = isset( $rule['type'] ) && in_array( $rule['type'], array( 'weekday', 'date' ), true ) ? $rule['type'] : 'weekday';
			$value   = isset( $rule['value'] ) ? (string) $rule['value'] : 'mon';
			$image   = isset( $rule['image'] ) ? esc_url( $rule['image'] ) : '';
			$preview = $image ? ' style="background-image:url(' . esc_url( $image ) . ');"' : '';
			?>
			<div class="mep-cal-day-rule-item" data-rule-index="<?php echo esc_attr( $index ); ?>">
				<div class="mep-cal-day-rule-grid">
					<div class="mep-cal-day-rule-field">
						<label><?php esc_html_e( 'Rule Type', 'mage-eventpress' ); ?></label>
						<select name="mep_calendar_settings[mep_cal_day_background_rules][<?php echo esc_attr( $index ); ?>][type]" class="mep-cal-day-rule-type">
							<option value="weekday" <?php selected( $type, 'weekday' ); ?>><?php esc_html_e( 'Weekday', 'mage-eventpress' ); ?></option>
							<option value="date" <?php selected( $type, 'date' ); ?>><?php esc_html_e( 'Exact Date', 'mage-eventpress' ); ?></option>
						</select>
					</div>

					<div class="mep-cal-day-rule-field mep-cal-day-rule-value-weekday<?php echo $type === 'date' ? ' is-hidden' : ''; ?>">
						<label><?php esc_html_e( 'Weekday', 'mage-eventpress' ); ?></label>
						<select name="mep_calendar_settings[mep_cal_day_background_rules][<?php echo esc_attr( $index ); ?>][value_weekday]" class="mep-cal-day-rule-weekday">
							<?php foreach ( $this->get_weekday_options() as $weekday_key => $weekday_label ) : ?>
								<option value="<?php echo esc_attr( $weekday_key ); ?>" <?php selected( $type === 'weekday' ? $value : '', $weekday_key ); ?>><?php echo esc_html( $weekday_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="mep-cal-day-rule-field mep-cal-day-rule-value-date<?php echo $type === 'weekday' ? ' is-hidden' : ''; ?>">
						<label><?php esc_html_e( 'Date', 'mage-eventpress' ); ?></label>
						<input type="date" name="mep_calendar_settings[mep_cal_day_background_rules][<?php echo esc_attr( $index ); ?>][value_date]" class="mep-cal-day-rule-date" value="<?php echo esc_attr( $type === 'date' ? $value : '' ); ?>" />
					</div>

					<div class="mep-cal-day-rule-field mep-cal-day-rule-field-image">
						<label><?php esc_html_e( 'Background Image', 'mage-eventpress' ); ?></label>
						<div class="mep-cal-day-rule-image-input">
							<input type="text" name="mep_calendar_settings[mep_cal_day_background_rules][<?php echo esc_attr( $index ); ?>][image]" class="regular-text mep-cal-media-url" value="<?php echo esc_attr( $image ); ?>" placeholder="https://example.com/image.jpg" />
							<button type="button" class="button mep-cal-media-upload"><?php esc_html_e( 'Upload / Choose', 'mage-eventpress' ); ?></button>
						</div>
					</div>

					<div class="mep-cal-day-rule-actions">
						<button type="button" class="button-link-delete mep-cal-day-rule-remove"><?php esc_html_e( 'Remove Rule', 'mage-eventpress' ); ?></button>
					</div>
				</div>

				<div class="mep-cal-day-rule-preview<?php echo $image ? ' has-image' : ''; ?>"<?php echo $preview; ?>></div>
			</div>
			<?php
		}

		private function sanitize_day_background_rules( $rules ) {
			if ( ! is_array( $rules ) ) {
				return array();
			}

			$sanitized_rules = array();
			$weekday_options = array_keys( $this->get_weekday_options() );

			foreach ( $rules as $rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}

				$type  = isset( $rule['type'] ) && in_array( $rule['type'], array( 'weekday', 'date' ), true ) ? $rule['type'] : 'weekday';
				$image = isset( $rule['image'] ) ? esc_url_raw( $rule['image'] ) : '';

				if ( empty( $image ) ) {
					continue;
				}

				$value = '';
				if ( 'date' === $type ) {
					$value = isset( $rule['value_date'] ) ? sanitize_text_field( $rule['value_date'] ) : '';
					if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
						continue;
					}
				} else {
					$value = isset( $rule['value_weekday'] ) ? sanitize_text_field( $rule['value_weekday'] ) : '';
					if ( ! in_array( $value, $weekday_options, true ) ) {
						continue;
					}
				}

				$sanitized_rules[] = array(
					'type'  => $type,
					'value' => $value,
					'image' => $image,
				);
			}

			return array_values( $sanitized_rules );
		}

		private function sanitize_dimension( $value, $default = 'auto' ) {
			$value = trim( (string) $value );

			if ( $value === '' ) {
				return $default;
			}

			$lower_value = strtolower( $value );
			if ( in_array( $lower_value, array( 'auto', 'parent' ), true ) ) {
				return $lower_value;
			}

			if ( preg_match( '/^\d+$/', $value ) ) {
				return $value . 'px';
			}

			if ( preg_match( '/^\d+(\.\d+)?(px|%|vh|vw|rem|em)$/i', $value ) ) {
				return $value;
			}

			return $default;
		}

		private function sanitize_event_ids( $value ) {
			$parts = array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );
			$ids   = array();

			foreach ( $parts as $part ) {
				if ( is_numeric( $part ) ) {
					$ids[] = absint( $part );
				}
			}

			$ids = array_filter( array_unique( $ids ) );

			return implode( ',', $ids );
		}
	}

	new MPWEM_Calendar_Settings();
}
