<?php
	/*
	 * @Author      engr.sumonazma@gmail.com
	 * Copyright:   mage-people.com
	 *
	 * Booking social-share image card: a downloadable/shareable PNG (avatar, attendee name,
	 * event name, date/time, ticket type, site name) offered on the booking thank-you page.
	 * The image itself is composited client-side (assets/frontend/js/mep-social-card.js via
	 * html2canvas) from the markup in templates/layout/social_card.php; this class only
	 * decides *whether* to print that markup and supplies the data it needs.
	 *
	 * Two thank-you surfaces call into this class:
	 *   - WooCommerce order-received page  -> render_on_wc_thankyou() (hooked here)
	 *   - Custom/native payment thank-you  -> render_for_native() (called by
	 *     templates/layout/booking_confirmation.php and, in PRO, MEP_Pro_Booking_Confirmation)
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! class_exists( 'MPWEM_Social_Card' ) ) {
		class MPWEM_Social_Card {

			const SECTION = 'mep_social_card_setting_sec';

			/**
			 * Order IDs already rendered during this request, so the card doesn't print
			 * twice: WooCommerce's classic thank-you template fires both
			 * 'woocommerce_before_thankyou' and 'woocommerce_thankyou', and the block-based
			 * Order Confirmation page only fires 'woocommerce_before_thankyou' (inside the
			 * "Order Confirmation Status" block — 'woocommerce_thankyou' there only fires if
			 * the site's template also has the "Additional Information" confirmation block,
			 * which most block-theme setups don't include).
			 */
			private static $rendered_orders = array();

			public function __construct() {
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
				if ( MPWEM_Global_Function::has_woocommerce() ) {
					add_action( 'woocommerce_before_thankyou', array( __CLASS__, 'render_on_wc_thankyou' ), 20 );
					add_action( 'woocommerce_thankyou', array( __CLASS__, 'render_on_wc_thankyou' ), 20 );
				}
			}

			/**
			 * filemtime()-based version so browsers fetch the new file immediately after an
			 * edit instead of serving a stale cached copy under the same ?ver=MPWEM_PLUGIN_VERSION
			 * query string (see the same pattern for mpwem_event_lists.js in MPWEM_Dependencies).
			 */
			private static function asset_version( $relative_path ) {
				$file = MPWEM_PLUGIN_DIR . $relative_path;
				return file_exists( $file ) ? filemtime( $file ) : MPWEM_PLUGIN_VERSION;
			}

			/**
			 * Admin-configurable look of the card: frame image, font, text/accent colors.
			 * Read directly by the template (for the inline style on #mep-sc-card) and by
			 * register_assets() (to know which Google Font, if any, to load alongside the
			 * headline's always-on Dancing Script).
			 */
			public static function get_style_settings(): array {
				return array(
					'frameImage'  => mep_get_option( 'mep_social_card_frame_image', self::SECTION, '' ),
					'fontFamily'  => mep_get_option( 'mep_social_card_font_family', self::SECTION, 'default' ),
					'textColor'   => mep_get_option( 'mep_social_card_text_color', self::SECTION, '#111827' ),
					'accentColor' => mep_get_option( 'mep_social_card_accent_color', self::SECTION, '#059669' ),
				);
			}

			/**
			 * Google Fonts URL for the headline's fixed cursive font plus whichever font
			 * family the admin picked for the rest of the card (skipped for 'default', which
			 * means "use the theme's system font stack").
			 */
			private static function google_font_url(): string {
				$families = array( 'Dancing+Script:wght@700' );
				$selected = self::get_style_settings()['fontFamily'];
				if ( $selected && $selected !== 'default' ) {
					$families[] = str_replace( ' ', '+', $selected ) . ':wght@400;600;700;800';
				}
				return 'https://fonts.googleapis.com/css2?' . implode( '&', array_map( function ( $f ) {
					return 'family=' . $f;
				}, $families ) ) . '&display=swap';
			}

			public static function register_assets() {
				wp_register_style( 'mep-social-card-font', self::google_font_url(), array(), null );
				wp_register_style( 'mep-social-card', MPWEM_PLUGIN_URL . '/assets/frontend/css/mep-social-card.css', array( 'mep-social-card-font' ), self::asset_version( '/assets/frontend/css/mep-social-card.css' ) );
				wp_register_script( 'mep-html2canvas', MPWEM_PLUGIN_URL . '/assets/helper/html2canvas/html2canvas.min.js', array(), '1.4.1', true );
				wp_register_script( 'mep-social-card', MPWEM_PLUGIN_URL . '/assets/frontend/js/mep-social-card.js', array( 'mep-html2canvas' ), self::asset_version( '/assets/frontend/js/mep-social-card.js' ), true );
			}

			public static function is_enabled(): bool {
				return mep_get_option( 'mep_social_card_enable', self::SECTION, '' ) === 'on';
			}

			public static function wc_status_allowed( $status ): bool {
				$allowed = mep_get_option( 'mep_social_card_wc_statuses', self::SECTION, array( 'processing' => 'processing', 'completed' => 'completed' ) );
				return is_array( $allowed ) && isset( $allowed[ $status ] );
			}

			public static function native_status_allowed( $status ): bool {
				$allowed = mep_get_option( 'mep_social_card_native_statuses', self::SECTION, array( 'success' => 'success' ) );
				return is_array( $allowed ) && isset( $allowed[ $status ] );
			}

			private static function status_label( $source, $status ): string {
				if ( $source === 'native' ) {
					$map = array(
						'success' => __( 'Registration Completed', 'mage-eventpress' ),
						'pending' => __( 'Registration Pending', 'mage-eventpress' ),
					);
				} else {
					$map = array(
						'completed'  => __( 'Registration Completed', 'mage-eventpress' ),
						'processing' => __( 'Registration Confirmed', 'mage-eventpress' ),
						'on-hold'    => __( 'Registration Received', 'mage-eventpress' ),
						'pending'    => __( 'Registration Pending', 'mage-eventpress' ),
					);
				}
				return isset( $map[ $status ] ) ? $map[ $status ] : __( 'Registration Completed', 'mage-eventpress' );
			}

			/**
			 * Fired on the WooCommerce order-received (thank-you) page.
			 */
			public static function render_on_wc_thankyou( $order_id ) {
				if ( ! self::is_enabled() || ! $order_id || isset( self::$rendered_orders[ $order_id ] ) ) {
					return;
				}
				$order = wc_get_order( $order_id );
				if ( ! $order instanceof WC_Order ) {
					return;
				}
				$status = $order->get_status();
				if ( ! self::wc_status_allowed( $status ) ) {
					return;
				}

				$event_id = 0;
				foreach ( $order->get_items() as $item_id => $item ) {
					$item_event_id = (int) MPWEM_Global_Function::get_order_item_meta( $item_id, 'event_id' );
					if ( $item_event_id && get_post_type( $item_event_id ) === 'mep_events' ) {
						$event_id = $item_event_id;
						break;
					}
				}
				if ( ! $event_id ) {
					return;
				}

				self::$rendered_orders[ $order_id ] = true;
				self::render( $event_id, $order_id, self::status_label( 'wc', $status ) );
			}

			/**
			 * Called by the Custom (native) Payment thank-you surfaces
			 * ($native_status is the same 'success' / 'pending' value used in ?mep_booking=).
			 */
			public static function render_for_native( $event_id, $order_id, $native_status ) {
				if ( ! self::is_enabled() || ! in_array( $native_status, array( 'success', 'pending' ), true ) ) {
					return;
				}
				if ( ! self::native_status_allowed( $native_status ) ) {
					return;
				}
				self::render( $event_id, $order_id, self::status_label( 'native', $native_status ) );
			}

			/**
			 * Gathers name / avatar / event / date / ticket for the card. Tries the
			 * mep_events_attendees record first (works for WooCommerce and native orders
			 * alike, per mep_get_attendee_info_query()); falls back to order-level billing
			 * data when no attendee record is resolvable yet (e.g. status not in the
			 * "seat reserved" list configured under General Settings).
			 */
			public static function get_card_data( $event_id, $order_id, $attendee_id = 0 ) {
				$event_id = absint( $event_id );
				$order_id = absint( $order_id );
				if ( ! $event_id ) {
					return null;
				}

				if ( ! $attendee_id && function_exists( 'mep_get_attendee_info_query' ) ) {
					$query = mep_get_attendee_info_query( $event_id, $order_id );
					if ( $query && ! empty( $query->posts ) ) {
						$attendee_id = $query->posts[0]->ID;
					}
				}

				$name        = '';
				$email       = '';
				$ticket_type = '';

				if ( $attendee_id ) {
					$name        = get_post_meta( $attendee_id, 'ea_name', true );
					$email       = get_post_meta( $attendee_id, 'ea_email', true );
					$ticket_type = get_post_meta( $attendee_id, 'ea_ticket_type', true );
				}

				if ( ( ! $name || ! $email ) && MPWEM_Global_Function::has_woocommerce() ) {
					$order = wc_get_order( $order_id );
					if ( $order instanceof WC_Order ) {
						$name  = $name ?: trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
						$email = $email ?: $order->get_billing_email();
						if ( ! $ticket_type ) {
							foreach ( $order->get_items() as $item_id => $item ) {
								$item_event_id = (int) MPWEM_Global_Function::get_order_item_meta( $item_id, 'event_id' );
								if ( $item_event_id === $event_id ) {
									$ticket_type = $item->get_name();
									break;
								}
							}
						}
					}
				}

				if ( ( ! $name || ! $email ) && get_post_type( $order_id ) === 'mep_custom_order' ) {
					$name  = $name ?: get_post_meta( $order_id, '_mep_customer_name', true );
					$email = $email ?: get_post_meta( $order_id, '_mep_customer_email', true );
					if ( ! $ticket_type ) {
						$items = (array) get_post_meta( $order_id, '_mep_order_items', true );
						$first = reset( $items );
						$ticket_type = ( is_array( $first ) && isset( $first['name'] ) ) ? $first['name'] : '';
					}
				}

				if ( ! $name ) {
					return null;
				}

				$event_datetime = function_exists( 'mep_get_email_event_datetime' ) ? mep_get_email_event_datetime( $event_id, $order_id, $attendee_id ) : '';
				$date_time_text = ( $event_datetime && function_exists( 'mep_get_email_datetime_text' ) ) ? mep_get_email_datetime_text( $event_id, $event_datetime, 'date-time-text' ) : '';

				$avatar_url = get_avatar_url( $email ?: $name, array( 'size' => 300, 'default' => 'identicon' ) );

				$logo_id  = get_theme_mod( 'custom_logo' );
				$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : get_site_icon_url( 128 );

				// Not get_the_title(): WooCommerce's wc_page_endpoint_title() hooks the generic
				// 'the_title' filter and, on any WC endpoint page (e.g. order-received), rewrites
				// the *first* title it sees while in_the_loop() is true to the endpoint's own
				// title ("Order received") — regardless of which post ID was asked for — then
				// removes itself. Since this runs from woocommerce_before_thankyou/thankyou
				// (fired from inside the block-rendered loop on that page), get_the_title()
				// here would return "Order received" instead of the event's real title.
				$event_post = get_post( $event_id );
				$event_name = $event_post ? $event_post->post_title : '';

				return array(
					'name'        => $name,
					'avatarUrl'   => $avatar_url,
					'eventName'   => $event_name,
					'eventDate'   => $date_time_text,
					'ticketType'  => $ticket_type,
					'siteName'    => get_bloginfo( 'name' ),
					'siteLogo'    => $logo_url ? $logo_url : '',
					'eventUrl'    => get_permalink( $event_id ),
				);
			}

			/**
			 * Prints the card markup (visible immediately, no click needed) + localizes its
			 * data. PNG compositing for the download/Instagram buttons happens client-side
			 * in mep-social-card.js; the Facebook/Twitter/WhatsApp/LinkedIn buttons are plain
			 * share-intent links and need no JavaScript at all.
			 */
			public static function render( $event_id, $order_id, $status_label = '', $attendee_id = 0 ) {
				$data = self::get_card_data( $event_id, $order_id, $attendee_id );
				if ( ! $data ) {
					return;
				}
				$data['statusLabel'] = $status_label;
				$data['buttonText']  = mep_get_option( 'mep_social_card_button_text', self::SECTION, __( 'Download Image', 'mage-eventpress' ) );
				$data['networks']    = array_keys( (array) mep_get_option( 'mep_social_card_networks', self::SECTION, array( 'facebook' => 'facebook', 'twitter' => 'twitter', 'whatsapp' => 'whatsapp' ) ) );

				self::enqueue_assets();
				wp_add_inline_script(
					'mep-social-card',
					'window.mepSocialCardData = ' . wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';',
					'before'
				);

				$template = MPWEM_Functions::template_path( 'layout/social_card.php' );
				if ( file_exists( $template ) ) {
					include $template;
				}
			}

			private static function enqueue_assets() {
				// WooCommerce Blocks renders the Order Confirmation blocks (and fires
				// woocommerce_before_thankyou/woocommerce_thankyou from inside them) *before*
				// the wp_enqueue_scripts action runs on a block-theme thank-you page — unlike
				// the classic checkout template, where wp_head() (and wp_enqueue_scripts) always
				// runs first. register_assets() is normally hooked to wp_enqueue_scripts, so on
				// that page it hasn't registered these handles yet by the time we get here; call
				// it directly (it's just wp_register_*, safe to call more than once) so
				// wp_enqueue_script()/wp_add_inline_script() below always have a handle to work with.
				if ( ! wp_script_is( 'mep-social-card', 'registered' ) ) {
					self::register_assets();
				}
				wp_enqueue_style( 'mep-social-card-font' );
				wp_enqueue_style( 'mep-social-card' );
				wp_enqueue_script( 'mep-html2canvas' );
				wp_enqueue_script( 'mep-social-card' );
			}
		}
		new MPWEM_Social_Card();
	}
