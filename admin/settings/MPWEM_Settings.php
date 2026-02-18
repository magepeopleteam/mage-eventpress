<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Settings' ) ) {
		class MPWEM_Settings {
			public function __construct() {
				add_action( 'add_meta_boxes', array( $this, 'event_meta_tab' ) );
				add_action( 'save_post', array( $this, 'save_settings' ) );
			}
			public function event_meta_tab() {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', __( 'Events', 'mage-eventpress' ) );
				add_meta_box( 'mp_event_all_info_in_tab',
					sprintf( '<i class="fas fa-info-circle"></i> %1$s Information: %2$s', esc_html( $event_label ), esc_html( get_the_title( get_the_ID() ) ) ),
					array( $this, 'event_tab' ), 'mep_events', 'normal', 'high' );
			}
			public function event_tab() {
				$post_id = get_the_id();
				wp_nonce_field( 'mpwem_type_nonce', 'mpwem_type_nonce' );
				$event_infos              = MPWEM_Functions::get_all_info( $post_id );
				$single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
				$speaker_status           = array_key_exists( 'mep_enable_speaker_list', $single_event_setting_sec ) ? $single_event_setting_sec['mep_enable_speaker_list'] : 'no';
				?>
                <div class="mp_event_all_meta_in_tab mp_event_tab_area">
                    <div class="mp_tab_menu">
                        <ul>
                            <li data-target-tabs="#mp_event_venue"><i class="mi mi-marker"></i><?php esc_html_e( 'Venue/Location', 'mage-eventpress' ); ?> </li>
                            <li data-target-tabs="#mpwem_ticket_pricing_settings"><i class="mi mi-coins"></i><?php esc_html_e( 'Ticket & Pricing', 'mage-eventpress' ); ?> </li>
                            <li data-target-tabs="#mpwem_date_settings"><i class="mi mi-calendar-clock"></i><?php esc_html_e( 'Date & Time', 'mage-eventpress' ); ?> </li>
                            <li data-target-tabs="#mpwem_event_settings"><i class="mi mi-settings"></i><?php esc_html_e( 'Settings', 'mage-eventpress' ); ?></li>
                            <li data-target-tabs="#mep_event_faq_meta"><i class="mi mi-messages-question"></i><?php esc_html_e( 'F.A.Q', 'mage-eventpress' ); ?></li>
							<?php if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) { ?>
                                <li data-target-tabs="#mp_event_tax_settings"><i class="mi mi-calendar-event-tax"></i><?php esc_html_e( 'Tax', 'mage-eventpress' ); ?></li>
							<?php } ?>
                            <li data-target-tabs="#mp_event_rich_text"><i class="mi mi-analyse"></i><?php esc_html_e( 'SEO Content', 'mage-eventpress' ); ?>  </li>
                            <li data-target-tabs="#mpwem_email_text_settings"><i class="mi mi-envelope-open-text"></i><?php esc_html_e( 'Email Text', 'mage-eventpress' ); ?></li>
                            <li data-target-tabs="#mep_event_template"><i class="mi mi-table-layout"></i><?php esc_html_e( 'Template', 'mage-eventpress' ); ?></li>
                            <li data-target-tabs="#mep_related_event_meta"><i class="mi mi-plug-connection"></i><?php esc_html_e( 'Related Events', 'mage-eventpress' ); ?></li>
							<?php if ( $speaker_status == 'yes' ) { ?>
                                <li data-target-tabs="#mpwem_speaker_settings"><i class="mi mi-microphone"></i><?php esc_html_e( 'Speaker Information', 'mage-eventpress' ); ?></li>
							<?php } ?>
							<?php do_action( 'mep_admin_event_details_before_tab_name_rich_text', $post_id ); ?>
                            <li data-target-tabs="#mep_event_timeline_meta"><i class="mi mi-list-timeline"></i><?php esc_html_e( 'Timeline Details', 'mage-eventpress' ); ?> </li>
							<?php do_action( 'mp_event_all_in_tab_menu' ); ?>
							<?php do_action( 'mep_admin_event_details_end_of_tab_name', $post_id ); ?>
                            <li data-target-tabs="#ttbm_settings_gallery"><i class="mi mi-gallery"></i><?php esc_html_e( 'Gallery ', 'mage-eventpress' ); ?></li>
                        </ul>
                    </div>
                    <div class="mp_tab_details">
						<?php do_action( 'mpwem_event_tab_setting_item', $post_id, $event_infos ); ?>
                        <!-- ==================================  -->
						<?php do_action( 'mep_admin_event_details_before_tab_details_location', $post_id ); ?>
						<?php do_action( 'mp_event_all_in_tab_item', $post_id ); ?>
                        <p style="font-size: 10px;text-align: right;position: absolute;bottom: -6px;right: 14px;"> #WC:<?php echo get_post_meta( $post_id, 'link_wc_product', true ); ?></p>
                    </div>
                </div>
                <script type="text/javascript">
                    jQuery(function ($) {
                        $("#mp_event_all_info_in_tab").parent().removeClass('meta-box-sortables');
                    });
                </script>
				<?php
			}
			public function save_settings( $post_id ) {
				if ( ! isset( $_POST['mpwem_type_nonce'] ) || ! wp_verify_nonce( $_POST['mpwem_type_nonce'], 'mpwem_type_nonce' ) || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				/**********Venue/Location Setting**********/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$mep_event_type  = isset( $_POST['mep_event_type'] ) && sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['mep_event_type'] ) ) ? 'online' : 'offline';
					$mep_org_address = isset( $_POST['mep_org_address'] ) ? sanitize_text_field( $_POST['mep_org_address'] ) : "";
					// Handle venue location with coordinate detection
					if ( isset( $_POST['mep_location_venue'] ) ) {
						$raw_venue_value = $_POST['mep_location_venue'];
						// Check if it looks like coordinates (lat,lng format)
						if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', trim( $raw_venue_value ) ) ) {
							// For coordinates, preserve comma, decimal point, and minus sign
							$mep_location_venue = sanitize_text_field( trim( $raw_venue_value ) );
						} else {
							// For regular location names, use the filter
							$after_filter       = mep_letters_numbers_spaces_only( $raw_venue_value );
							$mep_location_venue = sanitize_text_field( $after_filter );
						}
					} else {
						$mep_location_venue = "";
					}
					$mep_street    = isset( $_POST['mep_street'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['mep_street'] ) ) : "";
					$mep_city      = isset( $_POST['mep_city'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['mep_city'] ) ) : "";
					$mep_state     = isset( $_POST['mep_state'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['mep_state'] ) ) : "";
					$mep_postcode  = isset( $_POST['mep_postcode'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['mep_postcode'] ) ) : "";
					$mep_country   = isset( $_POST['mep_country'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['mep_country'] ) ) : "";
					$latitude      = isset( $_POST['latitude'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['latitude'] ) ) : "";
					$longitude     = isset( $_POST['longitude'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['longitude'] ) ) : "";
					$mep_sgm       = isset( $_POST['mep_sgm'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['mep_sgm'] ) ) : 0;
					$location_name = isset( $_POST['location_name'] ) ? sanitize_text_field( mep_letters_numbers_spaces_only( $_POST['location_name'] ) ) : "";
					update_post_meta( $post_id, 'mep_event_type', $mep_event_type );
					update_post_meta( $post_id, 'mep_org_address', mep_letters_numbers_spaces_only( mep_prevent_serialized_input( $mep_org_address ) ) );
					update_post_meta( $post_id, 'mep_location_venue', $mep_location_venue );
					update_post_meta( $post_id, 'mep_street', $mep_street );
					update_post_meta( $post_id, 'mep_city', $mep_city );
					update_post_meta( $post_id, 'mep_state', $mep_state );
					update_post_meta( $post_id, 'mep_postcode', $mep_postcode );
					update_post_meta( $post_id, 'mep_country', $mep_country );
					update_post_meta( $post_id, 'longitude', $longitude );
					update_post_meta( $post_id, 'latitude', $latitude );
					update_post_meta( $post_id, 'mep_sgm', $mep_sgm );
					update_post_meta( $post_id, 'location_name', $location_name );
				}
				/**********Ticket & Extra service Setting**********/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$mep_reg_status              = isset( $_POST['mep_reg_status'] ) && sanitize_text_field( wp_unslash( $_POST['mep_reg_status'] ) ) ? 'on' : 'off';
					$mep_show_advance_col_status = isset( $_POST['mep_show_advance_col_status'] ) && sanitize_text_field( wp_unslash( $_POST['mep_show_advance_col_status'] ) ) ? 'on' : 'off';
					
					$mep_reg_status_msg              = isset( $_POST['mep_reg_status_show_msg'] ) && sanitize_text_field( wp_unslash( $_POST['mep_reg_status_show_msg'] ) ) ? 'on' : 'off';
					$mep_reg_status_msg_txt 		 = isset( $_POST['mep_reg_status_show_msg_txt'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_status_show_msg_txt'] ) ) : '';
					
					
					update_post_meta( $post_id, 'mep_reg_status', $mep_reg_status );
					update_post_meta( $post_id, 'mep_show_advance_col_status', $mep_show_advance_col_status );

					update_post_meta( $post_id, 'mep_reg_status_show_msg', $mep_reg_status_msg );
					update_post_meta( $post_id, 'mep_reg_status_show_msg_txt', $mep_reg_status_msg_txt );

					/********************************/
					$new_ticket_type = array();
					$names           = isset( $_POST['option_name_t'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_name_t'] ) ) : [];
					$details         = isset( $_POST['option_details_t'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_details_t'] ) ) : [];
					$ticket_price    = isset( $_POST['option_price_t'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_price_t'] ) ) : [];
					$qty             = isset( $_POST['option_qty_t'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_qty_t'] ) ) : [];
					$dflt_qty        = isset( $_POST['option_default_qty_t'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_default_qty_t'] ) ) : [];
					$rsv             = isset( $_POST['option_rsv_t'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_rsv_t'] ) ) : [];
					$qty_type        = isset( $_POST['option_qty_t_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_qty_t_type'] ) ) : [];
					$sale_end_date   = isset( $_POST['option_sale_end_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_sale_end_date'] ) ) : [];
					$sale_end_time   = isset( $_POST['option_sale_end_time'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_sale_end_time'] ) ) : [];
					$option_ticket_enable   = isset( $_POST['option_ticket_enable'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_ticket_enable'] ) ) : [];
					$count           = count( $names );
					for ( $i = 0; $i < $count; $i ++ ) {
						if ( $names[ $i ] ) {
							$new_ticket_type[ $i ]['option_name_t']          = $names[ $i ];
							$new_ticket_type[ $i ]['option_details_t']       = $details[ $i ];
							$new_ticket_type[ $i ]['option_price_t']         = $ticket_price[ $i ] ?? 0;
							$new_ticket_type[ $i ]['option_qty_t']           = $qty[ $i ] ?? 0;
							$new_ticket_type[ $i ]['option_rsv_t']           = $rsv[ $i ] ?? 0;
							$new_ticket_type[ $i ]['option_default_qty_t']   = $dflt_qty[ $i ] ?? 0;
							$new_ticket_type[ $i ]['option_qty_t_type']      = $qty_type[ $i ] ?? '';
							$new_ticket_type[ $i ]['option_ticket_enable']      = $option_ticket_enable[ $i ] ?? 'yes';
							$new_ticket_type[ $i ]['option_sale_end_date']   = $sale_end_date[ $i ] ?? '';
							$new_ticket_type[ $i ]['option_sale_end_time']   = $sale_end_time[ $i ] ?? '';
							$new_ticket_type[ $i ]['option_sale_end_date_t'] = $sale_end_date[ $i ] . ' ' . $sale_end_time[ $i ];
						}
					}
					$ticket_type_list = apply_filters( 'mep_ticket_type_arr_save', $new_ticket_type );
					$ticket_type_list = apply_filters( 'mpwem_ticket_type_arr_save', $ticket_type_list );
					update_post_meta( $post_id, 'mep_event_ticket_type', $ticket_type_list );
					/**********Extra service**********/
					$new_extra_service = array();
					$extra_names       = isset( $_POST['option_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_name'] ) ) : [];
					$extra_price       = isset( $_POST['option_price'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_price'] ) ) : [];
					$extra_qty         = isset( $_POST['option_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_qty'] ) ) : [];
					$extra_qty_type    = isset( $_POST['option_qty_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option_qty_type'] ) ) : [];
					$extra_count       = count( $extra_names );
					for ( $i = 0; $i < $extra_count; $i ++ ) {
						if ( $extra_names[ $i ] && $extra_price[ $i ] >= 0 && $extra_qty[ $i ] > 0 ) {
							$new_extra_service[ $i ]['option_name']     = $extra_names[ $i ];
							$new_extra_service[ $i ]['option_price']    = $extra_price[ $i ];
							$new_extra_service[ $i ]['option_qty']      = $extra_qty[ $i ];
							$new_extra_service[ $i ]['option_qty_type'] = $extra_qty_type[ $i ] ?? 'inputbox';
						}
					}
					update_post_meta( $post_id, 'mep_events_extra_prices', $new_extra_service );
				}
				/**********Date**********/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					//************************************//
					$date_type = isset( $_POST['mep_enable_recurring'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_enable_recurring'] ) ) : 'no';
					update_post_meta( $post_id, 'mep_enable_recurring', $date_type );
					//**********************//
					if ( $date_type == 'no' ) {
						$start_date = isset( $_POST['event_start_date_normal'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_date_normal'] ) ) : '';
						$start_time = isset( $_POST['event_start_time_normal'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_time_normal'] ) ) : '';
						$end_date   = isset( $_POST['event_end_date_normal'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_date_normal'] ) ) : '';
						$end_time   = isset( $_POST['event_end_time_normal'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_time_normal'] ) ) : '';
						update_post_meta( $post_id, 'event_start_date', $start_date );
						update_post_meta( $post_id, 'event_start_time', $start_time );
						update_post_meta( $post_id, 'event_end_date', $end_date );
						update_post_meta( $post_id, 'event_end_time', $end_time );
						/********************/
						$event_start_datetime = date( 'Y-m-d H:i:s', strtotime( $start_date . ' ' . $start_time ) );
						$event_end_datetime   = date( 'Y-m-d H:i:s', strtotime( $end_date . ' ' . $end_time ) );
						update_post_meta( $post_id, 'event_start_datetime', $event_start_datetime );
						update_post_meta( $post_id, 'event_end_datetime', $event_end_datetime );
						$start_date_more = isset( $_POST['event_more_start_date_normal'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_more_start_date_normal'] ) ) : [];
						$start_time_more = isset( $_POST['event_more_start_time_normal'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_more_start_time_normal'] ) ) : [];
						$end_date_more   = isset( $_POST['event_more_end_date_normal'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_more_end_date_normal'] ) ) : [];
						$end_time_more   = isset( $_POST['event_more_end_time_normal'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_more_end_time_normal'] ) ) : [];
						$more_dates      = [];
						if ( sizeof( $start_date_more ) > 0 && sizeof( $end_date_more ) ) {
							foreach ( $start_date_more as $key => $start_date ) {
								if ( $start_date && $end_date_more[ $key ] ) {
									$more_dates[ $key ]['event_more_start_date'] = $start_date;
									$more_dates[ $key ]['event_more_start_time'] = $start_time_more[ $key ];
									$more_dates[ $key ]['event_more_end_date']   = $end_date_more[ $key ];
									$more_dates[ $key ]['event_more_end_time']   = $end_time_more[ $key ];
								}
							}
						}
						$more_dates = apply_filters( 'mep_more_date_arr_save', $more_dates );
						update_post_meta( $post_id, 'mep_event_more_date', $more_dates );
						$md                    = sizeof( $more_dates ) > 0 ? end( $more_dates ) : array();
						$event_expire_datetime = sizeof( $md ) > 0 ? date( 'Y-m-d H:i:s', strtotime( $md['event_more_end_date'] . ' ' . $md['event_more_end_time'] ) ) : $event_end_datetime;
						update_post_meta( $post_id, 'event_expire_datetime', $event_expire_datetime );
					} elseif ( $date_type == 'yes' ) {
						$start_date = isset( $_POST['event_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_date'] ) ) : '';
						$start_time = isset( $_POST['event_start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_time'] ) ) : '';
						$end_date   = isset( $_POST['event_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_date'] ) ) : '';
						$end_time   = isset( $_POST['event_end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_time'] ) ) : '';
						update_post_meta( $post_id, 'event_start_date', $start_date );
						update_post_meta( $post_id, 'event_start_time', $start_time );
						update_post_meta( $post_id, 'event_end_date', $end_date );
						update_post_meta( $post_id, 'event_end_time', $end_time );
						/********************/
						$event_start_datetime = date( 'Y-m-d H:i:s', strtotime( $start_date . ' ' . $start_time ) );
						$event_end_datetime   = date( 'Y-m-d H:i:s', strtotime( $end_date . ' ' . $end_time ) );
						update_post_meta( $post_id, 'event_start_datetime', $event_start_datetime );
						update_post_meta( $post_id, 'event_end_datetime', $event_end_datetime );
						$start_date_more = isset( $_POST['event_more_start_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_more_start_date'] ) ) : [];
						$start_time_more = isset( $_POST['event_more_start_time'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_more_start_time'] ) ) : [];
						$end_date_more   = isset( $_POST['event_more_end_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_more_end_date'] ) ) : [];
						$end_time_more   = isset( $_POST['event_more_end_time'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_more_end_time'] ) ) : [];
						$more_dates      = [];
						if ( sizeof( $start_date_more ) > 0 && sizeof( $end_date_more ) ) {
							foreach ( $start_date_more as $key => $start_date ) {
								if ( $start_date && $end_date_more[ $key ] ) {
									$more_dates[ $key ]['event_more_start_date'] = $start_date;
									$more_dates[ $key ]['event_more_start_time'] = $start_time_more[ $key ];
									$more_dates[ $key ]['event_more_end_date']   = $end_date_more[ $key ];
									$more_dates[ $key ]['event_more_end_time']   = $end_time_more[ $key ];
								}
							}
						}
						$more_dates = apply_filters( 'mep_more_date_arr_save', $more_dates );
						update_post_meta( $post_id, 'mep_event_more_date', $more_dates );
						$md                    = sizeof( $more_dates ) > 0 ? end( $more_dates ) : array();
						$event_expire_datetime = sizeof( $md ) > 0 ? date( 'Y-m-d H:i:s', strtotime( $md['event_more_end_date'] . ' ' . $md['event_more_end_time'] ) ) : $event_end_datetime;
						update_post_meta( $post_id, 'event_expire_datetime', $event_expire_datetime );
					} else {
						$start_date = isset( $_POST['event_start_date_everyday'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_date_everyday'] ) ) : '';
						$start_time = isset( $_POST['event_start_time_everyday'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_time_everyday'] ) ) : '';
						$end_date   = isset( $_POST['event_end_date_everyday'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_date_everyday'] ) ) : '';
						$end_time   = isset( $_POST['event_end_time_everyday'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_time_everyday'] ) ) : '';
						update_post_meta( $post_id, 'event_start_date', $start_date );
						update_post_meta( $post_id, 'event_start_time', $start_time );
						update_post_meta( $post_id, 'event_end_date', $end_date );
						update_post_meta( $post_id, 'event_end_time', $end_time );
						/********************/
						$event_start_datetime = date( 'Y-m-d H:i:s', strtotime( $start_date . ' ' . $start_time ) );
						$event_end_datetime   = date( 'Y-m-d H:i:s', strtotime( $end_date . ' ' . $end_time ) );
						update_post_meta( $post_id, 'event_start_datetime', $event_start_datetime );
						update_post_meta( $post_id, 'event_end_datetime', $event_end_datetime );
						update_post_meta( $post_id, 'event_expire_datetime', $event_end_datetime );
						//*******************//
						$periods = isset( $_POST['mep_repeated_periods'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_repeated_periods'] ) ) : '1';
						update_post_meta( $post_id, 'mep_repeated_periods', $periods );
						$off_days = isset( $_POST['mep_ticket_offdays'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_ticket_offdays'] ) ) : '';
						$off_days = $off_days ? explode( ',', $off_days ) : '';
						update_post_meta( $post_id, 'mep_ticket_offdays', $off_days );
						$all_off_dates = [];
						$off_dates     = isset( $_POST['mep_ticket_off_dates'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_ticket_off_dates'] ) ) : [];
						if ( sizeof( $off_dates ) > 0 ) {
							foreach ( $off_dates as $key => $off_date ) {
								if ( $off_date ) {
									$all_off_dates[ $key ]['mep_ticket_off_date'] = $off_date;
								}
							}
						}
						update_post_meta( $post_id, 'mep_ticket_off_dates', $all_off_dates );
						/******************************/
						$display_time = isset( $_POST['mep_disable_ticket_time'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_disable_ticket_time'] ) ) : '';
						$display_time = $display_time ? 'yes' : 'no';
						update_post_meta( $post_id, 'mep_disable_ticket_time', $display_time );
						/******************************/
						$this->day_wise_slot_save( $post_id, 'mep_ticket_times_global' );
						$this->day_wise_slot_save( $post_id, 'mep_ticket_times_sat' );
						$this->day_wise_slot_save( $post_id, 'mep_ticket_times_sun' );
						$this->day_wise_slot_save( $post_id, 'mep_ticket_times_mon' );
						$this->day_wise_slot_save( $post_id, 'mep_ticket_times_tue' );
						$this->day_wise_slot_save( $post_id, 'mep_ticket_times_wed' );
						$this->day_wise_slot_save( $post_id, 'mep_ticket_times_thu' );
						$this->day_wise_slot_save( $post_id, 'mep_ticket_times_fri' );
						//***************//
						$special_dates = array();
						$hidden_name   = isset( $_POST['mep_special_date_hidden_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_special_date_hidden_name'] ) ) : [];
						$date_labels   = isset( $_POST['mep_special_date_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_special_date_name'] ) ) : [];
						$start_date    = isset( $_POST['mep_special_start_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_special_start_date'] ) ) : [];
						$end_date      = isset( $_POST['mep_special_end_date'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_special_end_date'] ) ) : [];
						if ( count( $start_date ) > 0 ) {
							for ( $i = 0; $i < count( $start_date ); $i ++ ) {
								$time_labels = isset( $_POST[ 'mep_special_time_label_' . $hidden_name[ $i ] ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ 'mep_special_time_label_' . $hidden_name[ $i ] ] ) ) : [];
								$times       = isset( $_POST[ 'mep_special_time_value_' . $hidden_name[ $i ] ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ 'mep_special_time_value_' . $hidden_name[ $i ] ] ) ) : [];
								if ( $start_date[ $i ] != '' && $end_date[ $i ] != '' && sizeof( $time_labels ) > 0 && sizeof( $times ) > 0 ) {
									$special_dates[ $i ]['date_label'] = $date_labels[ $i ];
									$special_dates[ $i ]['start_date'] = date( 'Y-m-d', strtotime( $start_date[ $i ] ) );
									$special_dates[ $i ]['end_date']   = date( 'Y-m-d', strtotime( $end_date[ $i ] ) );
									$time_slot                         = array();
									for ( $j = 0; $j < count( $time_labels ); $j ++ ) {
										if ( $time_labels[ $j ] && $times[ $j ] != '' ) {
											$time_slot[ $j ]['mep_ticket_time_name'] = $time_labels[ $j ];
											$time_slot[ $j ]['mep_ticket_time']      = $times[ $j ];
										}
									}
									$special_dates[ $i ]['time'] = $time_slot;
								}
							}
						}
						update_post_meta( $post_id, 'mep_special_date_info', $special_dates );
						update_post_meta( $post_id, 'mep_special_date_info', $special_dates );
					}
					$buffer_time = isset( $_POST['mep_buffer_time'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_buffer_time'] ) ) : 0;
					update_post_meta( $post_id, 'mep_buffer_time', $buffer_time );
					/********************/
					$mep_enable_custom_dt_format = isset( $_POST['mep_enable_custom_dt_format'] ) && sanitize_text_field( wp_unslash( $_POST['mep_enable_custom_dt_format'] ) ) ? 'on' : 'off';
					update_post_meta( $post_id, 'mep_enable_custom_dt_format', $mep_enable_custom_dt_format );
					$mep_event_date_format = isset( $_POST['mep_event_date_format'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_event_date_format'] ) ) : '';
					update_post_meta( $post_id, 'mep_event_date_format', $mep_event_date_format );
					$mep_event_custom_date_format = isset( $_POST['mep_event_custom_date_format'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_event_custom_date_format'] ) ) : '';
					update_post_meta( $post_id, 'mep_event_custom_date_format', $mep_event_custom_date_format );
					$mep_event_time_format = isset( $_POST['mep_event_time_format'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_event_time_format'] ) ) : '';
					update_post_meta( $post_id, 'mep_event_time_format', $mep_event_time_format );
					$mep_custom_event_time_format = isset( $_POST['mep_custom_event_time_format'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_custom_event_time_format'] ) ) : '';
					update_post_meta( $post_id, 'mep_custom_event_time_format', $mep_custom_event_time_format );
					$mep_time_zone_display = isset( $_POST['mep_time_zone_display'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_time_zone_display'] ) ) : 'no';
					update_post_meta( $post_id, 'mep_time_zone_display', $mep_time_zone_display );
				}
				/**********Form empty data Setting**********/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$mep_full_name           = isset( $_POST['mep_full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_full_name'] ) ) : "";
					$mep_reg_email           = isset( $_POST['mep_reg_email'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_email'] ) ) : "";
					$mep_reg_phone           = isset( $_POST['mep_reg_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_phone'] ) ) : "";
					$mep_reg_address         = isset( $_POST['mep_reg_address'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_address'] ) ) : "";
					$mep_reg_designation     = isset( $_POST['mep_reg_designation'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_designation'] ) ) : "";
					$mep_reg_website         = isset( $_POST['mep_reg_website'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_website'] ) ) : "";
					$mep_reg_veg             = isset( $_POST['mep_reg_veg'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_veg'] ) ) : "";
					$mep_reg_company         = isset( $_POST['mep_reg_company'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_company'] ) ) : "";
					$mep_reg_gender          = isset( $_POST['mep_reg_gender'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_gender'] ) ) : "";
					$mep_reg_tshirtsize      = isset( $_POST['mep_reg_tshirtsize'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_tshirtsize'] ) ) : "";
					$mep_reg_tshirtsize_list = isset( $_POST['mep_reg_tshirtsize_list'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_reg_tshirtsize_list'] ) ) : "";
					update_post_meta( $post_id, 'mep_full_name', $mep_full_name );
					update_post_meta( $post_id, 'mep_reg_email', $mep_reg_email );
					update_post_meta( $post_id, 'mep_reg_phone', $mep_reg_phone );
					update_post_meta( $post_id, 'mep_reg_address', $mep_reg_address );
					update_post_meta( $post_id, 'mep_reg_designation', $mep_reg_designation );
					update_post_meta( $post_id, 'mep_reg_website', $mep_reg_website );
					update_post_meta( $post_id, 'mep_reg_veg', $mep_reg_veg );
					update_post_meta( $post_id, 'mep_reg_company', $mep_reg_company );
					update_post_meta( $post_id, 'mep_reg_gender', $mep_reg_gender );
					update_post_meta( $post_id, 'mep_reg_tshirtsize', $mep_reg_tshirtsize );
					update_post_meta( $post_id, 'mep_reg_tshirtsize_list', $mep_reg_tshirtsize_list );
				}
				/**********event Setting**********/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$sku = isset( $_POST['mep_event_sku'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_event_sku'] ) ) : $post_id;
					update_post_meta( $post_id, '_sku', $sku );
					$mep_show_end_datetime = isset( $_POST['mep_show_end_datetime'] ) && sanitize_text_field( wp_unslash( $_POST['mep_show_end_datetime'] ) ) ? 'yes' : 'no';
					update_post_meta( $post_id, 'mep_show_end_datetime', $mep_show_end_datetime );
					$mep_available_seat = isset( $_POST['mep_available_seat'] ) && sanitize_text_field( wp_unslash( $_POST['mep_available_seat'] ) ) ? 'on' : 'off';
					update_post_meta( $post_id, 'mep_available_seat', $mep_available_seat );
					$mep_event_member_type = isset( $_POST['mep_member_only_event'] ) && sanitize_text_field( wp_unslash( $_POST['mep_member_only_event'] ) ) ? 'member_only' : 'for_all';
					update_post_meta( $post_id, 'mep_member_only_event', $mep_event_member_type );
					$mep_member_only_user_role = isset( $_POST['mep_member_only_user_role'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_member_only_user_role'] ) ) : [ 'all' ];
					update_post_meta( $post_id, 'mep_member_only_user_role', $mep_member_only_user_role );
				}
				/**********Tax & others Setting**********/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$_tax_status = isset( $_POST['_tax_status'] ) ? sanitize_text_field( wp_unslash( $_POST['_tax_status'] ) ) : 'none';
					$_tax_class  = isset( $_POST['_tax_class'] ) ? sanitize_text_field( wp_unslash( $_POST['_tax_class'] ) ) : '';
					update_post_meta( $post_id, '_tax_status', $_tax_status );
					update_post_meta( $post_id, '_tax_class', $_tax_class );
					update_post_meta( $post_id, '_stock_msg', 'new' );
					update_post_meta( $post_id, '_sold_individually', 'no' );
					update_post_meta( $post_id, '_price', 0 );
					update_post_meta( $post_id, '_virtual', 'yes' );
				}
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$event_list    = isset( $_POST['event_list'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_list'] ) ) : [];
					$column_number = isset( $_POST['event_list_column'] ) ? sanitize_text_field( wp_unslash( $_POST['event_list_column'] ) ) : '';
					$section_label = isset( $_POST['related_section_label'] ) ? sanitize_text_field( wp_unslash( $_POST['related_section_label'] ) ) : '';
					$event_status  = isset( $_POST['mep_related_event_status'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_related_event_status'] ) ) : 'off';
					update_post_meta( $post_id, '_list_column', $column_number );
					update_post_meta( $post_id, 'event_list', $event_list );
					update_post_meta( $post_id, 'related_section_label', $section_label );
					update_post_meta( $post_id, 'mep_related_event_status', $event_status );
				}
				/********Speaker************/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$speaker_title = isset( $_POST['mep_speaker_title'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_speaker_title'] ) ) : '';
					$speaker_icon  = isset( $_POST['mep_event_speaker_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_event_speaker_icon'] ) ) : '';
					$speakers      = isset( $_POST['mep_event_speakers_list'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_event_speakers_list'] ) ) : [];
					update_post_meta( $post_id, 'mep_speaker_title', $speaker_title );
					update_post_meta( $post_id, 'mep_event_speaker_icon', $speaker_icon );
					update_post_meta( $post_id, 'mep_event_speakers_list', $speakers );
				}
				/********Gallery************/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$slider = isset( $_POST['mep_display_slider'] ) && sanitize_text_field( wp_unslash( $_POST['mep_display_slider'] ) ) ? 'on' : 'off';
					update_post_meta( $post_id, 'mep_display_slider', $slider );
					$images       = isset( $_POST['mep_gallery_images'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_gallery_images'] ) ) : '';
					$single_image = isset( $_POST['mep_list_thumbnail'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_list_thumbnail'] ) ) : '';
					$all_images   = explode( ',', $images );
					update_post_meta( $post_id, 'mep_gallery_images', $all_images );
					update_post_meta( $post_id, 'mep_list_thumbnail', $single_image );
				}
				/********************/
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$event_rt_status              = sanitize_text_field( $_POST['mep_rt_event_status'] );
					$event_rt_atdnce_mode         = sanitize_text_field( $_POST['mep_rt_event_attandence_mode'] );
					$event_rt_prv_date            = sanitize_text_field( $_POST['mep_rt_event_prvdate'] );
					$seat                         = 0;
					$mep_event_template_file_name = isset( $_POST['mep_event_template'] ) && mep_isValidFilename( $_POST['mep_event_template'] ) ? sanitize_file_name( $_POST['mep_event_template'] ) : "default-theme.php";
					$mep_event_template           = mep_template_file_validate( $mep_event_template_file_name );
					$mep_rich_text_status         = isset( $_POST['mep_rich_text_status'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_rich_text_status'] ) ) : 'enable';
					update_post_meta( $post_id, 'mep_rich_text_status', $mep_rich_text_status );
					update_post_meta( $post_id, 'mep_rt_event_status', $event_rt_status );
					update_post_meta( $post_id, 'mep_rt_event_attandence_mode', $event_rt_atdnce_mode );
					update_post_meta( $post_id, 'mep_rt_event_prvdate', $event_rt_prv_date );
					update_post_meta( $post_id, 'mep_event_template', $mep_event_template );
					update_post_meta( $post_id, '_stock', $seat );
					$mp_event_virtual_type_des = isset( $_POST['mp_event_virtual_type_des'] ) ? htmlspecialchars( mage_array_strip( $_POST['mp_event_virtual_type_des'] ) ) : "";
					update_post_meta( $post_id, 'mp_event_virtual_type_des', $mp_event_virtual_type_des );
				}
				$mep_show_upcoming_event = isset( $_POST['mep_show_upcoming_event'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_show_upcoming_event'] ) ) : '';
				update_post_meta( $post_id, 'mep_show_upcoming_event', $mep_show_upcoming_event );
				/*******************************/
				$mep_event_cc_email_text = isset( $_POST['mep_event_cc_email_text'] ) ? wp_kses_post( wp_unslash( $_POST['mep_event_cc_email_text'] ) ) : '';
				update_post_meta( $post_id, 'mep_event_cc_email_text', $mep_event_cc_email_text );
				do_action( 'mpwem_settings_save', $post_id );
				$mep_faq_title             = isset( $_POST['mep_faq_title'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mep_faq_title'] ) ) : [];
				$mep_faq_content             = isset( $_POST['mep_faq_content'] ) ? wp_unslash( $_POST['mep_faq_content'] ) : [];
                $faqs=[];
				if ( sizeof( $mep_faq_title ) > 0) {
                    foreach ($mep_faq_title as $key=>$title){
                        if($title){
	                        $faqs[$key]['mep_faq_title']=$title;
	                        $faqs[$key]['mep_faq_content']=$mep_faq_content[$key];
                        }
                    }
                }
				update_post_meta( $post_id, 'mep_event_faq', $faqs );
				$des = isset( $_POST['mep_faq_description'] ) ? wp_kses_post( wp_unslash( $_POST['mep_faq_description'] ) ) : '';
				update_post_meta( $post_id, 'mep_faq_description', $des );

				$mep_faq_status              = isset( $_POST['mep_faq_status'] ) && sanitize_text_field( wp_unslash( $_POST['mep_faq_status'] ) ) ? 'on' : 'off';
				update_post_meta( $post_id, 'mep_faq_status', $mep_faq_status );
				$mep_faq_status              = isset( $_POST['mep_timeline_status'] ) && sanitize_text_field( wp_unslash( $_POST['mep_timeline_status'] ) ) ? 'on' : 'off';
				update_post_meta( $post_id, 'mep_timeline_status', $mep_faq_status );
			}
			public function day_wise_slot_save( $post_id, $name ) {
				$all_global   = [];
				$global_label = isset( $_POST[ $name . '_label' ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $name . '_label' ] ) ) : [];
				$global_time  = isset( $_POST[ $name . '_time' ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $name . '_time' ] ) ) : [];
				if ( sizeof( $global_time ) > 0 && sizeof( $global_label ) ) {
					foreach ( $global_time as $key => $time ) {
						if ( $time && $global_label[ $key ] ) {
							$all_global[ $key ]['mep_ticket_time_name'] = $global_label[ $key ];
							$all_global[ $key ]['mep_ticket_time']      = $time;
						}
					}
				}
				update_post_meta( $post_id, $name, $all_global );
			}
			public static function des_array( $key ) {
				$des = array(
					'mep_display_slider'           => __( 'By default slider is ON but you can keep it off by switching this option', 'mage-eventpress' ),
					'mep_gallery_images'           => __( 'Please upload images for gallery', 'mage-eventpress' ),
					'gallery_settings_description' => __( 'Here gallery image can be added  to event so that guest can understand about this event.', 'mage-eventpress' ),
				);
				$des = apply_filters( 'mpwem_filter_description_array', $des );
				return $des[ $key ];
			}
			public static function des_p( $key ) {
				echo self::des_array( $key );
			}
		}
		new MPWEM_Settings();
	}
