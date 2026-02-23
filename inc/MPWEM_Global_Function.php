<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Global_Function' ) ) {
		class MPWEM_Global_Function {
			public function __construct() {
				add_action( 'mpwem_load_date_picker_js', [ $this, 'enqueue_date_picker' ], 10, 2 );
				add_filter( 'wc_price', [ $this, 'wc_price_free_text' ], 10, 4 );
			}
			public static function enqueue_date_picker( $selector, $dates ) {

				if ( empty( $dates ) ) {
					return;
				}

				$start_date = current( $dates );
				$end_date   = end( $dates );

				$available_dates = [];

				foreach ( $dates as $date ) {
					$available_dates[] = date( 'j-n-Y', strtotime( $date ) );
				}

				wp_enqueue_script(
					'mpwem-datepicker',
					plugin_dir_url( __FILE__ ) . '../assets/js/mpwem-datepicker.js',
					array( 'jquery', 'jquery-ui-datepicker' ),
					'1.0',
					true
				);

				wp_localize_script(
					'mpwem-datepicker',
					'mpwemDateData',
					array(
						'selector'       => $selector,
						'availableDates' => $available_dates,
						'minDate'        => array(
							'year'  => (int) date( 'Y', strtotime( $start_date ) ),
							'month' => (int) date( 'n', strtotime( $start_date ) ) - 1,
							'day'   => (int) date( 'j', strtotime( $start_date ) ),
						),
						'maxDate'        => array(
							'year'  => (int) date( 'Y', strtotime( $end_date ) ),
							'month' => (int) date( 'n', strtotime( $end_date ) ) - 1,
							'day'   => (int) date( 'j', strtotime( $end_date ) ),
						),
					)
				);
			}			
			public function date_picker_js( $selector, $dates ) {
				if ( is_array( $dates ) && sizeof( $dates ) > 0 ) {
					$start_date  = current( $dates );
					$start_year  = date( 'Y', strtotime( $start_date ) );
					$start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
					$start_day   = date( 'j', strtotime( $start_date ) );
					$end_date    = end( $dates );
					$end_year    = date( 'Y', strtotime( $end_date ) );
					$end_month   = ( date( 'n', strtotime( $end_date ) ) - 1 );
					$end_day     = date( 'j', strtotime( $end_date ) );
					$all_date    = [];
					foreach ( $dates as $date ) {
						$all_date[] = '"' . date( 'j-n-Y', strtotime( $date ) ) . '"';
					}
					?>
                    <script>
                        jQuery(document).ready(function () {

                            jQuery("<?php echo esc_attr( $selector ); ?>").datepicker({

                                dateFormat: mpwem_date_format,

                                minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),

                                maxDate: new Date(<?php echo esc_attr( $end_year ); ?>, <?php echo esc_attr( $end_month ); ?>, <?php echo esc_attr( $end_day ); ?>),

                                autoSize: true,
                                changeMonth: true,
                                changeYear: true,

                                beforeShowDay: WorkingDates,

                                onSelect: function (dateString, inst) {

                                    let selectedDate = jQuery(this).datepicker("getDate");

                                    if (typeof Fecha !== "undefined") {

                                        let formattedDate = Fecha.format(selectedDate, 'YYYY-MM-DD');

                                        jQuery(this)
                                            .closest('label')
                                            .find('input[type="hidden"]')
                                            .val(formattedDate)
                                            .trigger('change');

                                        console.log(formattedDate);

                                    } else {

                                        let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                        jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');

                                    }

                                }

                            });

                            function WorkingDates(date) {

                                let availableDates = [<?php echo implode( ',', $all_date ); ?>];

                                let dmy = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear();

                                if (jQuery.inArray(dmy, availableDates) !== -1) {
                                    return [true, "", "Available"];
                                } else {
                                    return [false, "", "unAvailable"];
                                }

                            }

                        });
                    </script>



					<?php
				}
			}
			public static function date_picker_format( $key = 'mep_datepicker_format' ): string {
				$format      = self::get_settings( 'general_setting_sec', $key, 'D d M , yy' );
				//$format      = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_datepicker_format', 'D d M , yy' );
				$date_format = 'Y-m-d';
				$date_format = $format == 'yy/mm/dd' ? 'Y/m/d' : $date_format;
				$date_format = $format == 'yy-dd-mm' ? 'Y-d-m' : $date_format;
				$date_format = $format == 'yy/dd/mm' ? 'Y/d/m' : $date_format;
				$date_format = $format == 'dd-mm-yy' ? 'd-m-Y' : $date_format;
				$date_format = $format == 'dd/mm/yy' ? 'd/m/Y' : $date_format;
				$date_format = $format == 'mm-dd-yy' ? 'm-d-Y' : $date_format;
				$date_format = $format == 'mm/dd/yy' ? 'm/d/Y' : $date_format;
				$date_format = $format == 'd M , yy' ? 'j M , Y' : $date_format;
				$date_format = $format == 'D d M , yy' ? 'D j M , Y' : $date_format;
				$date_format = $format == 'M d , yy' ? 'M  j, Y' : $date_format;
				return $format == 'D M d , yy' ? 'D M  j, Y' : $date_format;
			}
			public static function date_format( $date, $format = 'date', $post_id = '' ) {
				if ( $date ) {
                    $format=$format?:'date';
					$date_format = get_option( 'date_format' );
					$time_format = get_option( 'time_format' );
					$time_zone   = '';
					if ( $post_id ) {
						$display_format = MPWEM_Global_Function::get_post_info( $post_id, 'mep_enable_custom_dt_format' );
						if ( $display_format == 'on' ) {
							$custom_date_format = MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_date_format' );
							if ( $custom_date_format ) {
								if ( $custom_date_format == 'custom' ) {
									$custom_date_format = MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_custom_date_format' );
								}
								$date_format = $custom_date_format ?: $date_format;
							}
							$custom_time_format = MPWEM_Global_Function::get_post_info( $post_id, 'mep_event_time_format' );
							if ( $custom_time_format ) {
								if ( $custom_time_format == 'custom' ) {
									$custom_time_format = MPWEM_Global_Function::get_post_info( $post_id, 'mep_custom_event_time_format' );
								}
								$time_format = $custom_time_format ?: $time_format;
							}
							$time_zone = MPWEM_Global_Function::get_post_info( $post_id, 'mep_time_zone_display' );
						}
					}
					$wp_settings = $date_format . '  ' . $time_format;
					if ( $time_zone=='yes' ) {
						$wp_settings = $wp_settings .' '.'T';
						$date_format = $date_format .' '.'T';
						$time_format = $time_format .' '.'T';
					}
					$timestamp = strtotime( $date );
					if ( $format == 'date' ) {
						$date = date_i18n( $date_format, $timestamp );
					} elseif ( $format == 'time' ) {
						$date = date_i18n( $time_format, $timestamp );
					} elseif ( $format == 'full' ) {
						$date = date_i18n( $wp_settings, $timestamp );
					} elseif ( $format == 'day' ) {
						$date = date_i18n( 'd', $timestamp );
					} elseif ( $format == 'month' ) {
						$date = date_i18n( 'M', $timestamp );
					} elseif ( $format == 'year' ) {
						$date = date_i18n( 'Y', $timestamp );
					} else {
						if ( $time_zone ) {
							$format = $format . ' ' . 'T';
						}
						$date = date_i18n( $format, $timestamp );
					}
				}
				return $date;
			}
			public static function date_separate_period( $start_date, $end_date, $repeat = 1 ): DatePeriod {
				$repeat    = max( $repeat, 1 );
				$_interval = "P" . $repeat . "D";
				$end_date  = date( 'Y-m-d', strtotime( $end_date . ' +1 day' ) );
				return new DatePeriod( new DateTime( $start_date ), new DateInterval( $_interval ), new DateTime( $end_date ) );
			}
			public static function check_time_exit_date( $date ) {
				if ( $date ) {
					$parse_date = date_parse( $date );
					if ( ( $parse_date['hour'] && $parse_date['hour'] > 0 ) || ( $parse_date['minute'] && $parse_date['minute'] > 0 ) || ( $parse_date['second'] && $parse_date['second'] > 0 ) ) {
						return true;
					}
				}
				return false;
			}
			public static function sort_date( $a, $b ) {
				return strtotime( $a ) - strtotime( $b );
			}
			public static function sort_date_array( $a, $b ) {
				$dateA = strtotime( $a['time'] );
				$dateB = strtotime( $b['time'] );
				if ( $dateA == $dateB ) {
					return 0;
				} elseif ( $dateA > $dateB ) {
					return 1;
				} else {
					return - 1;
				}
			}
			public static function calender_date_format( $date_time ) {
				$time     = strtotime( $date_time );
				$new_date = date_i18n( 'Ymd', $time );
				$new_time = date( 'Hi', $time );
				return $new_date . "T" . $new_time . "00";
			}
			//=================//
			public static function get_post_info( $post_id, $key, $default = '' ) {
				$data = get_post_meta( $post_id, $key, true ) ?: $default;
				return self::data_sanitize( $data );
			}
			public static function data_sanitize( $data ) {
				if ( is_serialized( $data ) ) {
					$data = unserialize( $data );
					$data = self::data_sanitize( $data );
				} elseif ( is_string( $data ) ) {
					$data = sanitize_text_field( stripslashes( strip_tags( $data ) ) );
				} elseif ( is_array( $data ) ) {
					foreach ( $data as $key => $value ) {
						$data[ $key ] = self::data_sanitize( $value );
					}
				} elseif ( is_object( $data ) ) {
					$data = (array) $data;
					$data = self::data_sanitize( $data );
				}
				return $data;
			}
			//=================//
			public static function price_convert_raw( $price ) {
				$price = wp_strip_all_tags( $price );
				$price = str_replace( get_woocommerce_currency_symbol(), '', $price );
				$price = str_replace( wc_get_price_thousand_separator(), 't_s', $price );
				$price = str_replace( wc_get_price_decimal_separator(), 'd_s', $price );
				$price = str_replace( 't_s', '', $price );
				$price = str_replace( 'd_s', '.', $price );
				$price = str_replace( '&nbsp;', '', $price );
				$price = preg_replace( '/[^0-9.]/', '', $price );
				$price = (float) $price;
				return max( $price, 0 );
			}
			public static function get_wc_raw_price( $price ) {
				$price = wc_price( $price );
				return self::price_convert_raw( $price );
			}
			public function wc_price_free_text( $return, $price, $args, $unformatted_price ) {
				$show_free_text = self::get_settings( 'general_setting_sec', 'mep_show_zero_as_free', 'yes' );
				if ( $unformatted_price == 0 && $show_free_text == 'yes' ) {
					$return = __( 'Free', 'mage-eventpress' );
				}
				return $return;
			}
			//=================//
			public static function get_image_url( $post_id = '', $image_id = '', $size = 'full' ) {
				if ( $post_id ) {
					$image_id = get_post_thumbnail_id( $post_id );
					$image_id = $image_id ?: self::get_post_info( $post_id, 'mep_list_thumbnail' );
					if ( ! $image_id ) {
						$image_ids = MPWEM_Global_Function::get_post_info( $post_id, 'mep_gallery_images', array() );
						$image_id  = (is_array( $image_ids ) && sizeof( $image_ids ) > 0) ? current( $image_ids ) : $image_id;
					}
				}
				return wp_get_attachment_image_url( $image_id, $size );
			}
			//=================//
			public static function get_taxonomy( $name ) {
				return get_terms( array( 'taxonomy' => $name, 'hide_empty' => false ) );
			}
			public static function get_term_meta( $meta_id, $meta_key, $default = '' ) {
				$data = get_term_meta( $meta_id, $meta_key, true ) ?: $default;
				return self::data_sanitize( $data );
			}
			public static function get_all_term_data( $term_name, $value = 'name' ) {
				$all_data   = [];
				$taxonomies = self::get_taxonomy( $term_name );
				if ( $taxonomies && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$all_data[] = $taxonomy->$value;
					}
				}
				return array_unique( $all_data );
			}
			public static function get_meta_id_by_name( $taxonomy, $meta_key, $meta_value ) {
				$term    = get_term_by( $meta_key, $meta_value, $taxonomy );
				$term_id = false;
				if ( $term && ! is_wp_error( $term ) ) {
					$term_id = $term->term_id;
				}
				return $term_id;
			}
			public static function all_taxonomy_as_text( $post_id, $taxonomy ): string {
				$taxonomy_text = '';
				$all_taxonomy  = get_the_terms( $post_id, $taxonomy );
				if ( $all_taxonomy && ! is_wp_error( $all_taxonomy ) && is_array( $all_taxonomy ) && sizeof( $all_taxonomy ) > 0 ) {
					foreach ( $all_taxonomy as $category ) {
						$taxonomy_text = $taxonomy_text ? $taxonomy_text . '- ' . $category->name : $category->name;
					}
				}
				return $taxonomy_text;
			}
			public static function all_taxonomy_data( $post_id, $taxonomy ) {
				$taxonomy_data = [];
				$all_taxonomy  = get_the_terms( $post_id, $taxonomy );
				if ( $all_taxonomy && ! is_wp_error( $all_taxonomy ) && is_array( $all_taxonomy ) && sizeof( $all_taxonomy ) > 0 ) {
					foreach ( $all_taxonomy as $category ) {
						$taxonomy_data[] = $category->name;
					}
				}
				return $taxonomy_data;
			}
			public static function taxonomy_as_class( $post_id, $taxonomy, $unq_id = '' ) {
				$class         = null;
				$all_taxonomy  = get_the_terms( $post_id, $taxonomy );
				$taxonomy_data = [];
				if ( $all_taxonomy && ! is_wp_error( $all_taxonomy ) && is_array( $all_taxonomy ) && sizeof( $all_taxonomy ) > 0 ) {
					foreach ( $all_taxonomy as $category ) {
						$taxonomy_data[] = $unq_id . 'mage-' . $category->term_id;
					}
					$class = implode( ' ', $taxonomy_data );
				}
				return $class;
			}
			public static function get_order_item_meta( $item_id, $key ): string {
				global $wpdb;
				$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
				$results    = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM $table_name WHERE order_item_id = %d AND meta_key = %s", $item_id, $key ) );
				foreach ( $results as $result ) {
					$value = $result->meta_value;
				}
				return $value ?? '';
			}
			//=================//
			public static function wc_product_sku( $product_id ) {
				if ( $product_id ) {
					return new WC_Product( $product_id );
				}
				return null;
			}
			public static function check_woocommerce(): int {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
				if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					return 1;
				} elseif ( is_dir( $plugin_dir ) ) {
					return 2;
				} else {
					return 0;
				}
			}
			//=================//
			public static function get_setting( $section, $default = [] ) {
				$options = get_option( $section );
				return $options ? self::data_sanitize( $options ) : $default;
			}
			public static function get_settings( $section, $key, $default = '' ) {
				$options = get_option( $section );
				if ( isset( $options[ $key ] ) && $options[ $key ] ) {
					$default = $options[ $key ];
				}
				return $default ? self::data_sanitize( $default ) : $default;
			}
			public static function get_style_settings( $key, $default = '' ) {
				return self::get_settings( 'style_setting_sec', $key, $default );
			}
			public static function get_slider_settings( $key, $default = '' ) {
				return self::get_settings( 'mp_slider_settings', $key, $default );
			}
			public static function get_licence_settings( $key, $default = '' ) {
				return self::get_settings( 'mp_basic_license_settings', $key, $default );
			}
			//=================//
			public static function array_to_string( $array ) {
				$ids = '';
				if ( is_array( $array ) && sizeof( $array ) > 0 ) {
					foreach ( $array as $data ) {
						if ( $data ) {
							$ids = $ids ? $ids . ',' . $data : $data;
						}
					}
				}
				return $ids;
			}
			//=================//
			public static function week_day(): array {
				return [
					'mon' => esc_html__( 'Monday', 'mage-eventpress' ),
					'tue' => esc_html__( 'Tuesday', 'mage-eventpress' ),
					'wed' => esc_html__( 'Wednesday', 'mage-eventpress' ),
					'thu' => esc_html__( 'Thursday', 'mage-eventpress' ),
					'fri' => esc_html__( 'Friday', 'mage-eventpress' ),
					'sat' => esc_html__( 'Saturday', 'mage-eventpress' ),
					'sun' => esc_html__( 'Sunday', 'mage-eventpress' ),
				];
			}
			public static function date_format_list(): array {
				return [
					'F j, Y'    => date( 'F j, Y' ),
					'j F, Y'    => date( 'j F, Y' ),
					'D, F j, Y' => date( 'D, F j, Y' ),
					'l, F j, Y' => date( 'l, F j, Y' ),
					'Y-m-d'     => date( 'Y-m-d' ),
					'm/d/Y'     => date( 'm/d/Y' ),
					'd/m/Y'     => date( 'd/m/Y' ),
				];
			}
			public static function time_format_list(): array {
				return [
					'g:i a'       => date( 'g:i a' ),
					'g:i A'       => date( 'g:i A' ),
					'H:i'         => date( 'H:i' ),
					'H\H i\m\i\n' => date( 'H\H i\m\i\n' ),
				];
			}
		}
		new MPWEM_Global_Function();
	}
	//===Old function use change to new fn date 03/10/25===//
	if ( ! class_exists( 'MP_Global_Function' ) ) {
		class MP_Global_Function {
			public function __construct() {
				add_action( 'mp_load_date_picker_js', [ $this, 'date_picker_js' ], 10, 2 );
			}
			public static function query_post_type( $post_type, $show = - 1, $page = 1 ): WP_Query {
				$args = array(
					'post_type'      => $post_type,
					'posts_per_page' => $show,
					'paged'          => $page,
					'post_status'    => 'publish'
				);
				return new WP_Query( $args );
			}
			public static function get_all_post_id( $post_type, $show = - 1, $page = 1, $status = 'publish' ): array {
				$all_data = get_posts( array(
					'fields'         => 'ids',
					'post_type'      => $post_type,
					'posts_per_page' => $show,
					'paged'          => $page,
					'post_status'    => $status
				) );
				return array_unique( $all_data );
			}
			public static function get_post_info( $post_id, $key, $default = '' ) {
				$data = get_post_meta( $post_id, $key, true ) ?: $default;
				return self::data_sanitize( $data );
			}
			//***********************************//
			public static function get_taxonomy( $name ) {
				return get_terms( array( 'taxonomy' => $name, 'hide_empty' => false ) );
			}
			public static function get_term_meta( $meta_id, $meta_key, $default = '' ) {
				$data = get_term_meta( $meta_id, $meta_key, true ) ?: $default;
				return self::data_sanitize( $data );
			}
			public static function get_meta_id_by_name( $taxonomy, $meta_key, $meta_value ) {
				$term    = get_term_by( $meta_key, $meta_value, $taxonomy );
				$term_id = false;
				if ( $term && ! is_wp_error( $term ) ) {
					$term_id = $term->term_id;
				}
				return $term_id;
			}
			public static function get_all_term_data( $term_name, $value = 'name' ) {
				$all_data   = [];
				$taxonomies = self::get_taxonomy( $term_name );
				if ( $taxonomies && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$all_data[] = $taxonomy->$value;
					}
				}
				return $all_data;
			}
			public static function all_taxonomy_as_text( $event_id, $taxonomy ): string {
				$taxonomy_text = '';
				$all_taxonomy  = get_the_terms( $event_id, $taxonomy );
				if ( $all_taxonomy && ! is_wp_error( $all_taxonomy ) && is_array( $all_taxonomy ) && sizeof( $all_taxonomy ) > 0 ) {
					foreach ( $all_taxonomy as $category ) {
						$taxonomy_text = $taxonomy_text ? $taxonomy_text . '- ' . $category->name : $category->name;
					}
				}
				return $taxonomy_text;
			}
			public static function all_taxonomy_data( $event_id, $taxonomy ) {
				$taxonomy_data = [];
				$all_taxonomy  = get_the_terms( $event_id, $taxonomy );
				if ( $all_taxonomy && ! is_wp_error( $all_taxonomy ) && is_array( $all_taxonomy ) && sizeof( $all_taxonomy ) > 0 ) {
					foreach ( $all_taxonomy as $category ) {
						$taxonomy_data[] = $category->name;
					}
				}
				return $taxonomy_data;
			}
			//***********************************//
			public static function get_submit_info( $key, $default = '' ) {
				return self::data_sanitize( $_POST[ $key ] ?? $default );
			}
			public static function get_submit_info_get_method( $key, $default = '' ) {
				return self::data_sanitize( $_GET[ $key ] ?? $default );
			}
			public static function data_sanitize( $data ) {
				if ( is_serialized( $data ) ) {
					$data = unserialize( $data );
					$data = self::data_sanitize( $data );
				} elseif ( is_string( $data ) ) {
					$data = sanitize_text_field( stripslashes( strip_tags( $data ) ) );
				} elseif ( is_array( $data ) ) {
					foreach ( $data as $key => $value ) {
						$data[ $key ] = self::data_sanitize( $value );
					}
				} elseif ( is_object( $data ) ) {
					$data = (array) $data;
					$data = self::data_sanitize( $data );
				}
				return $data;
			}
			//**************Date related*********************//
			public static function date_picker_format_without_year( $key = 'date_format' ): string {
				$format      = self::get_settings( 'mp_global_settings', $key, 'D d M , yy' );
				$date_format = 'm-d';
				$date_format = $format == 'yy/mm/dd' ? 'm/d' : $date_format;
				$date_format = $format == 'yy-dd-mm' ? 'd-m' : $date_format;
				$date_format = $format == 'yy/dd/mm' ? 'd/m' : $date_format;
				$date_format = $format == 'dd-mm-yy' ? 'd-m' : $date_format;
				$date_format = $format == 'dd/mm/yy' ? 'd/m' : $date_format;
				$date_format = $format == 'mm-dd-yy' ? 'm-d' : $date_format;
				$date_format = $format == 'mm/dd/yy' ? 'm/d' : $date_format;
				$date_format = $format == 'd M , yy' ? 'j M' : $date_format;
				$date_format = $format == 'D d M , yy' ? 'D j M' : $date_format;
				$date_format = $format == 'M d , yy' ? 'M  j' : $date_format;
				return $format == 'D M d , yy' ? 'D M  j' : $date_format;
			}
			public static function date_picker_format( $key = 'mep_datepicker_format' ): string {
				$format      = self::get_settings( 'general_setting_sec', $key, 'D d M , yy' );
				$date_format = 'Y-m-d';
				$date_format = $format == 'yy/mm/dd' ? 'Y/m/d' : $date_format;
				$date_format = $format == 'yy-dd-mm' ? 'Y-d-m' : $date_format;
				$date_format = $format == 'yy/dd/mm' ? 'Y/d/m' : $date_format;
				$date_format = $format == 'dd-mm-yy' ? 'd-m-Y' : $date_format;
				$date_format = $format == 'dd/mm/yy' ? 'd/m/Y' : $date_format;
				$date_format = $format == 'mm-dd-yy' ? 'm-d-Y' : $date_format;
				$date_format = $format == 'mm/dd/yy' ? 'm/d/Y' : $date_format;
				$date_format = $format == 'd M , yy' ? 'j M , Y' : $date_format;
				$date_format = $format == 'D d M , yy' ? 'D j M , Y' : $date_format;
				$date_format = $format == 'M d , yy' ? 'M  j, Y' : $date_format;
				return $format == 'D M d , yy' ? 'D M  j, Y' : $date_format;
			}
			public function date_picker_js( $selector, $dates ) {
				$start_date  = $dates[0];
				$start_year  = date( 'Y', strtotime( $start_date ) );
				$start_month = ( date( 'n', strtotime( $start_date ) ) - 1 );
				$start_day   = date( 'j', strtotime( $start_date ) );
				$end_date    = end( $dates );
				$end_year    = date( 'Y', strtotime( $end_date ) );
				$end_month   = ( date( 'n', strtotime( $end_date ) ) - 1 );
				$end_day     = date( 'j', strtotime( $end_date ) );
				$all_date    = [];
				foreach ( $dates as $date ) {
					$all_date[] = '"' . date( 'j-n-Y', strtotime( $date ) ) . '"';
				}
				?>
                <script>
                    jQuery(document).ready(function () {
                        jQuery("<?php echo esc_attr( $selector ); ?>").datepicker({
                            dateFormat: mpwem_date_format,
                            minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                            maxDate: new Date(<?php echo esc_attr( $end_year ); ?>, <?php echo esc_attr( $end_month ); ?>, <?php echo esc_attr( $end_day ); ?>),
                            autoSize: true,
                            changeMonth: true,
                            changeYear: true,
                            beforeShowDay: WorkingDates,
                            onSelect: function (dateString, data) {
                                let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                            }
                        });
                        function WorkingDates(date) {
                            let availableDates = [<?php echo implode( ',', $all_date ); ?>];
                            let dmy = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear();
                            if (jQuery.inArray(dmy, availableDates) !== -1) {
                                return [true, "", "Available"];
                            } else {
                                return [false, "", "unAvailable"];
                            }
                        }
                    });
                </script>
				<?php
			}
			public static function date_format( $date, $format = 'date' ) {
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );
				$wp_settings = $date_format . '  ' . $time_format;
				//$timezone = wp_timezone_string();
				$timestamp = strtotime( $date );
				if ( $format == 'date' ) {
					$date = date_i18n( $date_format, $timestamp );
				} elseif ( $format == 'time' ) {
					$date = date_i18n( $time_format, $timestamp );
				} elseif ( $format == 'full' ) {
					$date = date_i18n( $wp_settings, $timestamp );
				} elseif ( $format == 'day' ) {
					$date = date_i18n( 'd', $timestamp );
				} elseif ( $format == 'month' ) {
					$date = date_i18n( 'M', $timestamp );
				} elseif ( $format == 'year' ) {
					$date = date_i18n( 'Y', $timestamp );
				} else {
					$date = date_i18n( $format, $timestamp );
				}
				return $date;
			}
			public static function date_separate_period( $start_date, $end_date, $repeat = 1 ): DatePeriod {
				$repeat    = max( $repeat, 1 );
				$_interval = "P" . $repeat . "D";
				$end_date  = date( 'Y-m-d', strtotime( $end_date . ' +1 day' ) );
				return new DatePeriod( new DateTime( $start_date ), new DateInterval( $_interval ), new DateTime( $end_date ) );
			}
			public static function check_time_exit_date( $date ) {
				if ( $date ) {
					$parse_date = date_parse( $date );
					if ( ( $parse_date['hour'] && $parse_date['hour'] > 0 ) || ( $parse_date['minute'] && $parse_date['minute'] > 0 ) || ( $parse_date['second'] && $parse_date['second'] > 0 ) ) {
						return true;
					}
				}
				return false;
			}
			public static function check_licensee_date( $date ) {
				if ( $date ) {
					if ( $date == 'lifetime' ) {
						return esc_html__( 'Lifetime', 'mage-eventpress' );
					} else if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( date( 'Y-m-d H:i', strtotime( $date ) ) ) ) {
						return self::date_format( $date, 'full' );
					} else {
						return esc_html__( 'Expired', 'mage-eventpress' );
					}
				}
				return $date;
			}
			public static function sort_date( $a, $b ) {
				return strtotime( $a ) - strtotime( $b );
			}
			public static function sort_date_array( $a, $b ) {
				$dateA = strtotime( $a['time'] );
				$dateB = strtotime( $b['time'] );
				if ( $dateA == $dateB ) {
					return 0;
				} elseif ( $dateA > $dateB ) {
					return 1;
				} else {
					return - 1;
				}
			}
			public static function date_difference( $startdate, $enddate ) {
				$starttimestamp = strtotime( $startdate );
				$endtimestamp   = strtotime( $enddate );
				$difference     = abs( $endtimestamp - $starttimestamp ) / 3600;
				//return $difference;
				$datetime1 = new DateTime( $startdate );
				$datetime2 = new DateTime( $enddate );
				$interval  = $datetime1->diff( $datetime2 );
				return $interval->format( '%h' ) . "H " . $interval->format( '%i' ) . "M";
			}
			//***********************************//
			public static function get_settings( $section, $key, $default = '' ) {
				$options = get_option( $section );
				if ( isset( $options[ $key ] ) && $options[ $key ] ) {
					$default = $options[ $key ];
				}
				return $default;
			}
			public static function get_style_settings( $key, $default = '' ) {
				return self::get_settings( 'mp_style_settings', $key, $default );
			}
			public static function get_slider_settings( $key, $default = '' ) {
				return self::get_settings( 'mp_slider_settings', $key, $default );
			}
			public static function get_licence_settings( $key, $default = '' ) {
				return self::get_settings( 'mp_basic_license_settings', $key, $default );
			}
			//***********************************//
			public static function price_convert_raw( $price ) {
				$price = wp_strip_all_tags( $price );
				$price = str_replace( get_woocommerce_currency_symbol(), '', $price );
				$price = str_replace( wc_get_price_thousand_separator(), 't_s', $price );
				$price = str_replace( wc_get_price_decimal_separator(), 'd_s', $price );
				$price = str_replace( 't_s', '', $price );
				$price = str_replace( 'd_s', '.', $price );
				$price = str_replace( '&nbsp;', '', $price );
				$price = preg_replace( '/[^0-9.]/', '', $price );
				$price = (float) $price;
				return max( $price, 0 );
			}
			public static function wc_price( $post_id, $price, $args = array() ): string {
				$num_of_decimal = get_option( 'woocommerce_price_num_decimals', 2 );
				$args           = wp_parse_args( $args, array(
					'qty'   => '',
					'price' => '',
				) );
				$_product       = self::get_post_info( $post_id, 'link_wc_product', $post_id );
				$product        = wc_get_product( $_product );
				$qty            = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;
				$tax_with_price = get_option( 'woocommerce_tax_display_shop' );
				if ( '' === $price ) {
					return '';
				} elseif ( empty( $qty ) ) {
					return 0.0;
				}
				$line_price   = (float) $price * (int) $qty;
				$return_price = $line_price;
				if ( $product && $product->is_taxable() ) {
					if ( ! wc_prices_include_tax() ) {
						$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
						$taxes     = WC_Tax::calc_tax( $line_price, $tax_rates );
						if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
							$taxes_total = array_sum( $taxes );
						} else {
							$taxes_total = array_sum( array_map( 'wc_round_tax_total', $taxes ) );
						}
						$return_price = $tax_with_price == 'excl' ? round( $line_price, $num_of_decimal ) : round( $line_price + $taxes_total, $num_of_decimal );
					} else {
						$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
						$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
						if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) { // @codingStandardsIgnoreLine.
							$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
							if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
								$remove_taxes_total = array_sum( $remove_taxes );
							} else {
								$remove_taxes_total = array_sum( array_map( 'wc_round_tax_total', $remove_taxes ) );
							}
							// $return_price = round( $line_price, $num_of_decimal);
							$return_price = round( $line_price - $remove_taxes_total, $num_of_decimal );
						} else {
							$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
							$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates );
							if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
								$base_taxes_total   = array_sum( $base_taxes );
								$modded_taxes_total = array_sum( $modded_taxes );
							} else {
								$base_taxes_total   = array_sum( array_map( 'wc_round_tax_total', $base_taxes ) );
								$modded_taxes_total = array_sum( array_map( 'wc_round_tax_total', $modded_taxes ) );
							}
							$return_price = $tax_with_price == 'excl' ? round( $line_price - $base_taxes_total, $num_of_decimal ) : round( $line_price - $base_taxes_total + $modded_taxes_total, $num_of_decimal );
						}
					}
				}
				$return_price   = apply_filters( 'woocommerce_get_price_including_tax', $return_price, $qty, $product );
				$display_suffix = get_option( 'woocommerce_price_display_suffix' ) ? get_option( 'woocommerce_price_display_suffix' ) : '';
				return wc_price( $return_price ) . ' ' . $display_suffix;
			}
			public static function get_wc_raw_price( $price ) {
				$price = wc_price( $price );
				return self::price_convert_raw( $price );
			}
			//***********************************//
			public static function get_image_url( $post_id = '', $image_id = '', $size = 'full' ) {
				if ( $post_id ) {
					$image_id = get_post_thumbnail_id( $post_id );
					$image_id = $image_id ?: self::get_post_info( $post_id, 'mp_thumbnail' );
				}
				return wp_get_attachment_image_url( $image_id, $size );
			}
			public static function get_page_by_slug( $slug ) {
				if ( $pages = get_pages() ) {
					foreach ( $pages as $page ) {
						if ( $slug === $page->post_name ) {
							return $page;
						}
					}
				}
				return false;
			}
			//***********************************//
			public static function check_plugin( $plugin_dir_name, $plugin_file ): int {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				$plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
				if ( is_plugin_active( $plugin_dir_name . '/' . $plugin_file ) ) {
					return 1;
				} elseif ( is_dir( $plugin_dir ) ) {
					return 2;
				} else {
					return 0;
				}
			}
			public static function check_woocommerce(): int {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
				if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					return 1;
				} elseif ( is_dir( $plugin_dir ) ) {
					return 2;
				} else {
					return 0;
				}
			}
			public static function get_order_item_meta( $item_id, $key ): string {
				global $wpdb;
				$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
				$results    = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM $table_name WHERE order_item_id = %d AND meta_key = %s", $item_id, $key ) );
				foreach ( $results as $result ) {
					$value = $result->meta_value;
				}
				return $value ?? '';
			}
			public static function check_product_in_cart( $post_id ) {
				$status = self::check_woocommerce();
				if ( $status == 1 ) {
					$product_id = self::get_post_info( $post_id, 'link_wc_product' );
					foreach ( WC()->cart->get_cart() as $cart_item ) {
						if ( $cart_item['product_id'] == $product_id ) {
							return true;
						}
					}
				}
				return false;
			}
			public static function wc_product_sku( $product_id ) {
				if ( $product_id ) {
					return new WC_Product( $product_id );
				}
				return null;
			}
			//***********************************//
			public static function all_tax_list(): array {
				global $wpdb;
				$table_name = $wpdb->prefix . 'wc_tax_rate_classes';
				$result     = $wpdb->get_results( "SELECT * FROM $table_name" );
				$tax_list   = [];
				foreach ( $result as $tax ) {
					$tax_list[ $tax->slug ] = $tax->name;
				}
				return $tax_list;
			}
			public static function week_day(): array {
				return [
					'mon' => esc_html__( 'Monday', 'mage-eventpress' ),
					'tue' => esc_html__( 'Tuesday', 'mage-eventpress' ),
					'wed' => esc_html__( 'Wednesday', 'mage-eventpress' ),
					'thu' => esc_html__( 'Thursday', 'mage-eventpress' ),
					'fri' => esc_html__( 'Friday', 'mage-eventpress' ),
					'sat' => esc_html__( 'Saturday', 'mage-eventpress' ),
					'sun' => esc_html__( 'Sunday', 'mage-eventpress' ),
				];
			}
			public static function array_to_string( $array ) {
				$ids = '';
				if ( is_array( $array ) && sizeof( $array ) > 0 ) {
					foreach ( $array as $data ) {
						if ( $data ) {
							$ids = $ids ? $ids . ',' . $data : $data;
						}
					}
				}
				return $ids;
			}
			//***********************************//
			public static function license_error_text( $response, $license_data, $plugin_name ) {
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					$message = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : esc_html__( 'An error occurred, please try again.', 'mage-eventpress' );
				} else {
					if ( false === $license_data->success ) {
						switch ( $license_data->error ) {
							case 'expired':
								$message = esc_html__( 'Your license key expired on ', 'mage-eventpress' ) . ' ' . date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) );
								break;
							case 'revoked':
								$message = esc_html__( 'Your license key has been disabled.', 'mage-eventpress' );
								break;
							case 'missing':
								$message = esc_html__( 'Missing license.', 'mage-eventpress' );
								break;
							case 'invalid':
								$message = esc_html__( 'Invalid license.', 'mage-eventpress' );
								break;
							case 'site_inactive':
								$message = esc_html__( 'Your license is not active for this URL.', 'mage-eventpress' );
								break;
							case 'item_name_mismatch':
								$message = esc_html__( 'This appears to be an invalid license key for .', 'mage-eventpress' ) . ' ' . $plugin_name;
								break;
							case 'no_activations_left':
								$message = esc_html__( 'Your license key has reached its activation limit.', 'mage-eventpress' );
								break;
							default:
								$message = esc_html__( 'An error occurred, please try again.', 'mage-eventpress' );
								break;
						}
					} else {
						$payment_id = $license_data->payment_id;
						$expire     = $license_data->expires;
						$message    = esc_html__( 'Success, License Key is valid for the plugin', 'mage-eventpress' ) . ' ' . $plugin_name . ' ' . esc_html__( 'Your Order id is', 'mage-eventpress' ) . ' ' . $payment_id . ' ' . $plugin_name . ' ' . esc_html__( 'Validity of this licenses is', 'mage-eventpress' ) . ' ' . self::check_licensee_date( $expire );
					}
				}
				return $message;
			}
			//***********************************//
			public static function get_country_list() {
				return array(
					'AF' => 'Afghanistan',
					'AX' => 'Aland Islands',
					'AL' => 'Albania',
					'DZ' => 'Algeria',
					'AS' => 'American Samoa',
					'AD' => 'Andorra',
					'AO' => 'Angola',
					'AI' => 'Anguilla',
					'AQ' => 'Antarctica',
					'AG' => 'Antigua And Barbuda',
					'AR' => 'Argentina',
					'AM' => 'Armenia',
					'AW' => 'Aruba',
					'AU' => 'Australia',
					'AT' => 'Austria',
					'AZ' => 'Azerbaijan',
					'BS' => 'Bahamas',
					'BH' => 'Bahrain',
					'BD' => 'Bangladesh',
					'BB' => 'Barbados',
					'BY' => 'Belarus',
					'BE' => 'Belgium',
					'BZ' => 'Belize',
					'BJ' => 'Benin',
					'BM' => 'Bermuda',
					'BT' => 'Bhutan',
					'BO' => 'Bolivia',
					'BA' => 'Bosnia And Herzegovina',
					'BW' => 'Botswana',
					'BV' => 'Bouvet Island',
					'BR' => 'Brazil',
					'IO' => 'British Indian Ocean Territory',
					'BN' => 'Brunei Darussalam',
					'BG' => 'Bulgaria',
					'BF' => 'Burkina Faso',
					'BI' => 'Burundi',
					'KH' => 'Cambodia',
					'CM' => 'Cameroon',
					'CA' => 'Canada',
					'CV' => 'Cape Verde',
					'KY' => 'Cayman Islands',
					'CF' => 'Central African Republic',
					'TD' => 'Chad',
					'CL' => 'Chile',
					'CN' => 'China',
					'CX' => 'Christmas Island',
					'CC' => 'Cocos (Keeling) Islands',
					'CO' => 'Colombia',
					'KM' => 'Comoros',
					'CG' => 'Congo',
					'CD' => 'Congo, Democratic Republic',
					'CK' => 'Cook Islands',
					'CR' => 'Costa Rica',
					'CI' => 'Cote D\'Ivoire',
					'HR' => 'Croatia',
					'CU' => 'Cuba',
					'CY' => 'Cyprus',
					'CZ' => 'Czech Republic',
					'DK' => 'Denmark',
					'DJ' => 'Djibouti',
					'DM' => 'Dominica',
					'DO' => 'Dominican Republic',
					'EC' => 'Ecuador',
					'EG' => 'Egypt',
					'SV' => 'El Salvador',
					'GQ' => 'Equatorial Guinea',
					'ER' => 'Eritrea',
					'EE' => 'Estonia',
					'ET' => 'Ethiopia',
					'FK' => 'Falkland Islands (Malvinas)',
					'FO' => 'Faroe Islands',
					'FJ' => 'Fiji',
					'FI' => 'Finland',
					'FR' => 'France',
					'GF' => 'French Guiana',
					'PF' => 'French Polynesia',
					'TF' => 'French Southern Territories',
					'GA' => 'Gabon',
					'GM' => 'Gambia',
					'GE' => 'Georgia',
					'DE' => 'Germany',
					'GH' => 'Ghana',
					'GI' => 'Gibraltar',
					'GR' => 'Greece',
					'GL' => 'Greenland',
					'GD' => 'Grenada',
					'GP' => 'Guadeloupe',
					'GU' => 'Guam',
					'GT' => 'Guatemala',
					'GG' => 'Guernsey',
					'GN' => 'Guinea',
					'GW' => 'Guinea-Bissau',
					'GY' => 'Guyana',
					'HT' => 'Haiti',
					'HM' => 'Heard Island & Mcdonald Islands',
					'VA' => 'Holy See (Vatican City State)',
					'HN' => 'Honduras',
					'HK' => 'Hong Kong',
					'HU' => 'Hungary',
					'IS' => 'Iceland',
					'IN' => 'India',
					'ID' => 'Indonesia',
					'IR' => 'Iran, Islamic Republic Of',
					'IQ' => 'Iraq',
					'IE' => 'Ireland',
					'IM' => 'Isle Of Man',
					'IL' => 'Israel',
					'IT' => 'Italy',
					'JM' => 'Jamaica',
					'JP' => 'Japan',
					'JE' => 'Jersey',
					'JO' => 'Jordan',
					'KZ' => 'Kazakhstan',
					'KE' => 'Kenya',
					'KI' => 'Kiribati',
					'KR' => 'Korea',
					'KW' => 'Kuwait',
					'KG' => 'Kyrgyzstan',
					'LA' => 'Lao People\'s Democratic Republic',
					'LV' => 'Latvia',
					'LB' => 'Lebanon',
					'LS' => 'Lesotho',
					'LR' => 'Liberia',
					'LY' => 'Libyan Arab Jamahiriya',
					'LI' => 'Liechtenstein',
					'LT' => 'Lithuania',
					'LU' => 'Luxembourg',
					'MO' => 'Macao',
					'MK' => 'Macedonia',
					'MG' => 'Madagascar',
					'MW' => 'Malawi',
					'MY' => 'Malaysia',
					'MV' => 'Maldives',
					'ML' => 'Mali',
					'MT' => 'Malta',
					'MH' => 'Marshall Islands',
					'MQ' => 'Martinique',
					'MR' => 'Mauritania',
					'MU' => 'Mauritius',
					'YT' => 'Mayotte',
					'MX' => 'Mexico',
					'FM' => 'Micronesia, Federated States Of',
					'MD' => 'Moldova',
					'MC' => 'Monaco',
					'MN' => 'Mongolia',
					'ME' => 'Montenegro',
					'MS' => 'Montserrat',
					'MA' => 'Morocco',
					'MZ' => 'Mozambique',
					'MM' => 'Myanmar',
					'NA' => 'Namibia',
					'NR' => 'Nauru',
					'NP' => 'Nepal',
					'NL' => 'Netherlands',
					'AN' => 'Netherlands Antilles',
					'NC' => 'New Caledonia',
					'NZ' => 'New Zealand',
					'NI' => 'Nicaragua',
					'NE' => 'Niger',
					'NG' => 'Nigeria',
					'NU' => 'Niue',
					'NF' => 'Norfolk Island',
					'MP' => 'Northern Mariana Islands',
					'NO' => 'Norway',
					'OM' => 'Oman',
					'PK' => 'Pakistan',
					'PW' => 'Palau',
					'PS' => 'Palestinian Territory, Occupied',
					'PA' => 'Panama',
					'PG' => 'Papua New Guinea',
					'PY' => 'Paraguay',
					'PE' => 'Peru',
					'PH' => 'Philippines',
					'PN' => 'Pitcairn',
					'PL' => 'Poland',
					'PT' => 'Portugal',
					'PR' => 'Puerto Rico',
					'QA' => 'Qatar',
					'RE' => 'Reunion',
					'RO' => 'Romania',
					'RU' => 'Russian Federation',
					'RW' => 'Rwanda',
					'BL' => 'Saint Barthelemy',
					'SH' => 'Saint Helena',
					'KN' => 'Saint Kitts And Nevis',
					'LC' => 'Saint Lucia',
					'MF' => 'Saint Martin',
					'PM' => 'Saint Pierre And Miquelon',
					'VC' => 'Saint Vincent And Grenadines',
					'WS' => 'Samoa',
					'SM' => 'San Marino',
					'ST' => 'Sao Tome And Principe',
					'SA' => 'Saudi Arabia',
					'SN' => 'Senegal',
					'RS' => 'Serbia',
					'SC' => 'Seychelles',
					'SL' => 'Sierra Leone',
					'SG' => 'Singapore',
					'SK' => 'Slovakia',
					'SI' => 'Slovenia',
					'SB' => 'Solomon Islands',
					'SO' => 'Somalia',
					'ZA' => 'South Africa',
					'GS' => 'South Georgia And Sandwich Isl.',
					'ES' => 'Spain',
					'LK' => 'Sri Lanka',
					'SD' => 'Sudan',
					'SR' => 'Suriname',
					'SJ' => 'Svalbard And Jan Mayen',
					'SZ' => 'Swaziland',
					'SE' => 'Sweden',
					'CH' => 'Switzerland',
					'SY' => 'Syrian Arab Republic',
					'TW' => 'Taiwan',
					'TJ' => 'Tajikistan',
					'TZ' => 'Tanzania',
					'TH' => 'Thailand',
					'TL' => 'Timor-Leste',
					'TG' => 'Togo',
					'TK' => 'Tokelau',
					'TO' => 'Tonga',
					'TT' => 'Trinidad And Tobago',
					'TN' => 'Tunisia',
					'TR' => 'Turkey',
					'TM' => 'Turkmenistan',
					'TC' => 'Turks And Caicos Islands',
					'TV' => 'Tuvalu',
					'UG' => 'Uganda',
					'UA' => 'Ukraine',
					'AE' => 'United Arab Emirates',
					'GB' => 'United Kingdom',
					'US' => 'United States',
					'UM' => 'United States Outlying Islands',
					'UY' => 'Uruguay',
					'UZ' => 'Uzbekistan',
					'VU' => 'Vanuatu',
					'VE' => 'Venezuela',
					'VN' => 'Viet Nam',
					'VG' => 'Virgin Islands, British',
					'VI' => 'Virgin Islands, U.S.',
					'WF' => 'Wallis And Futuna',
					'EH' => 'Western Sahara',
					'YE' => 'Yemen',
					'ZM' => 'Zambia',
					'ZW' => 'Zimbabwe',
				);
			}
		}
		new MP_Global_Function();
	}
