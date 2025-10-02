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
				add_action( 'mpwem_load_date_picker_js', [ $this, 'date_picker_js' ], 10, 2 );
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
                            dateFormat: mp_date_format,
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
            //=================//
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
			//=================//
			//=================//
			public static function get_meta_id_by_name( $taxonomy, $meta_key, $meta_value ) {
				$term    = get_term_by( $meta_key, $meta_value, $taxonomy );
				$term_id = false;
				if ( $term && ! is_wp_error( $term ) ) {
					$term_id = $term->term_id;
				}

				return $term_id;
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
		}
		new MPWEM_Global_Function();
	}