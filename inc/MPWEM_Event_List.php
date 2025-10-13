<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Event_List' ) ) {
		class MPWEM_Event_List {
			public function __construct() {
				add_action( 'mep_event_list_shortcode', [ $this, 'event_list_shortcode' ], 10, 5 );
				add_action( 'mage_event_loop_list_shortcode', [ $this, 'event_loop_list_shortcode' ], 10, 5 );
			}

			public function event_list_shortcode( $event_id, $columnNumber = '', $style = '', $width = '', $unq_id = '' ) {
				$now         = current_time( 'Y-m-d H:i:s' );
				$show_price  = mep_get_option( 'mep_event_price_show', 'event_list_setting_sec', 'yes' );
				$price_count = mep_event_list_price( $event_id, 'count' );
				// event_price_label_single
				$show_price_label = $price_count == 1 ? mep_get_option( 'event_price_label_single', 'label_setting_sec', __( 'Price:', 'mage-eventpress' ) ) : mep_get_option( 'event-price-label', 'label_setting_sec', __( 'Price Starts from:', 'mage-eventpress' ) );
				$event_meta       = get_post_custom( $event_id );
				$author_terms     = get_the_terms( $event_id, 'mep_org' ) ? get_the_terms( $event_id, 'mep_org' ) : [];
				$time             = strtotime( $event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0] );
				$newformat        = date_i18n( 'Y-m-d H:i:s', $time );
				$tt               = get_the_terms( $event_id, 'mep_cat' );
				$torg             = get_the_terms( $event_id, 'mep_org' );
				$ttag             = get_the_terms( $event_id, 'mep_tag' );
				$org_class        = mep_get_term_as_class( $event_id, 'mep_org', $unq_id );
				$cat_class        = mep_get_term_as_class( $event_id, 'mep_cat', $unq_id );
				$tag_class        = mep_get_term_as_class( $event_id, 'mep_tag', $unq_id );
				$event_multidate  = array_key_exists( 'mep_event_more_date', $event_meta ) ? maybe_unserialize( $event_meta['mep_event_more_date'][0] ) : array();
				$available_seat   = apply_filters( 'mep_event_loop_list_available_seat', mep_get_total_available_seat( $event_id, $event_meta ), $event_id );
				// $available_seat         = 1;
				$hide_org_list           = mep_get_option( 'mep_event_hide_organizer_list', 'event_list_setting_sec', 'no' );
				$hide_location_list      = mep_get_option( 'mep_event_hide_location_list', 'event_list_setting_sec', 'no' );
				$hide_time_list          = mep_get_option( 'mep_event_hide_time_list', 'event_list_setting_sec', 'no' );
				$hide_only_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'event_list_setting_sec', 'no' );
				$recurring               = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
				$event_type              = get_post_meta( get_the_id(), 'mep_event_type', true ) ? get_post_meta( get_the_id(), 'mep_event_type', true ) : 'offline';
				$upcoming_date           = mep_get_event_upcoming_date( $event_id );
				// $post_id = get_the_id();
				$total_seat  = mep_event_total_seat( $event_id, 'total' );
				$total_resv  = mep_event_total_seat( $event_id, 'resv' );
				$total_sold  = mep_get_event_total_seat_left( $event_id, $upcoming_date );
				$_total_left = $total_seat - ( $total_sold + $total_resv );
				$total_left  = apply_filters( 'mep_event_list_total_seat_count', $_total_left, $event_id );
				$s           = $total_left;
				if ( $s > 0 ) {
					$class_name = 'event-availabe-seat';
				} else {
					$class_name = 'event-no-availabe-seat';
				}
				ob_start();
				require MPWEM_Functions::template_path( 'list/default.php' );
				do_action( 'mep_event_list_loop_end', $event_id );
				?>
                </div>
				<?php
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_loop_list_shortcode', $content, $event_id, $style, $unq_id );
			}

			public function event_loop_list_shortcode( $content, $event_id, $style, $unq_id = '' ) {
				ob_start();
				$org_class = mep_get_term_as_class( $event_id, 'mep_org', $unq_id );
				$cat_class = mep_get_term_as_class( $event_id, 'mep_cat', $unq_id );
				if ( $style == 'title' ) {
					?>
                    <div class='mep_event_title_list_item mix <?php echo esc_attr( $org_class ) . ' ' . esc_attr( $cat_class ); ?>'>
                        <a href='<?php the_permalink(); ?>'><?php the_title(); ?></a>
						<?php
							$event_organizer_icon = mep_get_option( 'mep_event_organizer_icon', 'icon_setting_sec', 'far fa-list-alt' );
							$org_terms            = get_the_terms( $event_id, 'mep_org' );
							if ( $org_terms && ! is_wp_error( $org_terms ) && count( $org_terms ) > 0 ) {
								echo ' - <span class="mep_title_list_organizer"><i class="' . esc_attr( $event_organizer_icon ) . '"></i> ' . esc_html( $org_terms[0]->name ) . '</span>';
							}
						?>
                    </div>
					<?php
					$content = ob_get_clean();
				} else {
					$now                     = current_time( 'Y-m-d H:i:s' );
					$show_price              = mep_get_option( 'mep_event_price_show', 'event_list_setting_sec', 'yes' );
					$price_count             = mep_event_list_price( $event_id, 'count' );
					$show_price_label        = $price_count == 1 ? mep_get_option( 'event_price_label_single', 'general_setting_sec', __( 'Price:', 'mage-eventpress' ) ) : mep_get_option( 'event-price-label', 'general_setting_sec', __( 'Price Starts from:', 'mage-eventpress' ) );
					$event_meta              = get_post_custom( $event_id );
					$author_terms            = get_the_terms( $event_id, 'mep_org' );
					$time                    = strtotime( $event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0] );
					$newformat               = date_i18n( 'Y-m-d H:i:s', $time );
					$tt                      = get_the_terms( $event_id, 'mep_cat' );
					$torg                    = get_the_terms( $event_id, 'mep_org' );
					$event_multidate         = array_key_exists( 'mep_event_more_date', $event_meta ) ? maybe_unserialize( $event_meta['mep_event_more_date'][0] ) : array();
					$available_seat          = mep_get_total_available_seat( $event_id, $event_meta );
					$hide_org_list           = mep_get_option( 'mep_event_hide_organizer_list', 'event_list_setting_sec', 'no' );
					$hide_location_list      = mep_get_option( 'mep_event_hide_location_list', 'event_list_setting_sec', 'no' );
					$hide_time_list          = mep_get_option( 'mep_event_hide_time_list', 'event_list_setting_sec', 'no' );
					$hide_only_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'event_list_setting_sec', 'no' );
					$recurring               = get_post_meta( $event_id, 'mep_enable_recurring', true ) ? get_post_meta( $event_id, 'mep_enable_recurring', true ) : 'no';
					$start_datetime          = $event_meta['event_start_date'][0];
					$end_datetime            = $event_meta['event_end_date'][0];
					$start_time              = strtotime( $event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0] );
					$end_time                = strtotime( $event_meta['event_end_date'][0] . ' ' . $event_meta['event_end_time'][0] );
					$start_date_format       = date_i18n( 'M d, Y', $start_time );
					$start_time_format       = date_i18n( 'g:i A', $start_time );
					$end_date_format         = date_i18n( 'M d, Y', $end_time );
					$end_time_format         = date_i18n( 'g:i A', $end_time );
					$event_type              = get_post_meta( get_the_id(), 'mep_event_type', true ) ? get_post_meta( get_the_id(), 'mep_event_type', true ) : 'offline';
					if ( $style == 'minimal' ) {
						require MPWEM_Functions::template_path( 'list/minimal.php' );
					} else if ( $style == 'native' ) {
						ob_start();
						require MPWEM_Functions::template_path( 'list/native.php' );
						$content = ob_get_clean();

						return $content;
					} else if ( $style == 'timeline' ) {
						require MPWEM_Functions::template_path( 'list/timeline.php' );
					} else if ( $style == 'spring' ) {
						require MPWEM_Functions::template_path( 'list/spring.php' );
					} else if ( $style == 'winter' ) {
						require MPWEM_Functions::template_path( 'list/winter.php' );
					}
					$content = ob_get_clean();
				}

				return $content;
			}
		}
		new MPWEM_Event_List();
	}