<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Hooks' ) ) {
		class MPWEM_Hooks {
			public function __construct() {
				add_action( 'mpwem_title', [ $this, 'title' ], 10, 2 );
				add_action( 'mpwem_description', [ $this, 'description' ], 10, 2 );
				/**************************/
				add_action( 'mpwem_organizer', [ $this, 'organizer' ], 10, 3 );
				add_action( 'mpwem_taxonomy_filter', [ $this, 'taxonomy_filter' ], 10, 2 );
				/**************************/
				add_action( 'mpwem_location', [ $this, 'location' ], 10, 3 );
				add_action( 'mpwem_location_only', [ $this, 'location' ], 10, 3 );
				add_action( 'mpwem_map', [ $this, 'map' ], 10, 4 );
				/**************************/
				add_action( 'mpwem_date_select', [ $this, 'date_select' ], 10, 4 );
				add_action( 'mpwem_time', [ $this, 'time' ], 10, 5 );
				add_action( 'mpwem_registration', [ $this, 'registration' ], 10, 4 );
				add_action( 'mpwem_registration_content', [ $this, 'registration_content' ], 10, 4 );
				/**************************/
				add_action( 'mpwem_date_list', [ $this, 'event_date_list' ], 10, 3 );
				add_action( 'mpwem_date_only', [ $this, 'date_only' ], 10, 2 );
				add_action( 'mpwem_time_only', [ $this, 'time_only' ], 10, 2 );
				/**************************/
				add_action( 'mpwem_faq', [ $this, 'faq' ], 10, 4 );
				add_action( 'mpwem_related', [ $this, 'related' ], 10, 4 );
				add_action( 'mpwem_social', [ $this, 'social' ], 10, 4 );
				add_action( 'mpwem_timeline', [ $this, 'timeline' ], 10, 4 );
				add_action( 'mep_event_tags', [ $this, 'event_tags' ] );
				add_action( 'mpwem_add_calender', [ $this, 'event_add_calender' ], 10, 3 );
				add_action( 'mpwem_speaker', [ $this, 'speakers' ], 10, 2 );
				/**************************/
				add_action( 'wp_ajax_get_mpwem_ticket', array( $this, 'get_mpwem_ticket' ) );
				add_action( 'wp_ajax_nopriv_get_mpwem_ticket', array( $this, 'get_mpwem_ticket' ) );
				add_action( 'wp_ajax_get_mpwem_time', array( $this, 'get_mpwem_time' ) );
				add_action( 'wp_ajax_nopriv_get_mpwem_time', array( $this, 'get_mpwem_time' ) );
				add_action( 'wp_ajax_mpwem_load_event_list_page', array( $this, 'mpwem_load_event_list_page' ) );
				add_action( 'wp_ajax_nopriv_mpwem_load_event_list_page', array( $this, 'mpwem_load_event_list_page' ) );
				add_action( 'wp_ajax_mpwem_get_date_list', array( $this, 'mpwem_get_date_list' ) );
				add_action( 'wp_ajax_nopriv_mpwem_get_date_list', array( $this, 'mpwem_get_date_list' ) );
				add_action( 'wp_ajax_mpwem_load_date', array( $this, 'mpwem_load_date' ) );
				/***********************/
				add_action( 'mpwem_seat_status', [ $this, 'seat_status' ], 10, 3 );
				add_action( 'wp_ajax_mpwem_load_seat_status', array( $this, 'mpwem_load_seat_status' ) );
				add_action( 'wp_ajax_mpwem_reload_seat_status', array( $this, 'mpwem_reload_seat_status' ) );

				/*************************************/
				add_action( 'mpwem_list_thumb', [ $this, 'list_thumb' ], 10, 3 );
				add_action( 'mpwem_list_location', [ $this, 'list_location' ], 10, 3 );
				add_action( 'mpwem_list_organizer', [ $this, 'list_organizer' ], 10, 3 );
				add_action( 'mpwem_list_price', [ $this, 'list_price' ], 10, 3 );
				add_action( 'mpwem_list_upcoming_date', [ $this, 'list_upcoming_date' ], 10, 3 );
				add_action( 'mpwem_list_upcoming_date_only', [ $this, 'list_upcoming_date_only' ], 10, 3 );
				add_action( 'mpwem_list_upcoming_time', [ $this, 'list_upcoming_time' ], 10, 3 );
				add_action( 'mpwem_list_sort_date', [ $this, 'list_sort_date' ], 10, 3 );
				add_action( 'mpwem_list_more_date_button', [ $this, 'list_more_date_button' ], 10, 3 );
				add_action( 'mpwem_list_hover', [ $this, 'list_hover' ], 10, 3 );
				add_action( 'mpwem_list_ribbon', [ $this, 'list_ribbon' ], 10, 3 );
			}
			public function title( $event_id, $only = '' ): void { require MPWEM_Functions::template_path( 'layout/title.php' ); }
			public function description( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/description.php' ); }
			public function organizer( $event_id, $event_infos = [], $only = '' ): void { require MPWEM_Functions::template_path( 'layout/organizer.php' ); }
			public function taxonomy_filter( $taxonomy_name, $unq_id = '' ): void {
				$taxonomies = MPWEM_Global_Function::get_taxonomy( $taxonomy_name );
				if ( $taxonomies ) {
					?>
                    <div class="mep-events-cats-list">
                        <div class="mep-event-cat-controls">
                            <button type="button" class="mep-cat-control" data-mixitup-control data-filter="all"><?php esc_html_e( 'All', 'mage-eventpress' ); ?></button>
							<?php foreach ( $taxonomies as $_terms ) { ?>
                                <button type="button" class="mep-cat-control" data-mixitup-control data-filter=".<?php echo esc_attr( $unq_id . 'mage-' . $_terms->term_id ); ?>"><?php echo esc_html( $_terms->name ); ?></button>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}
			}
			/**********************************/
			public function location( $event_id, $event_infos = [], $type = '' ): void {  require MPWEM_Functions::template_path( 'layout/location.php' ); }
			public function map( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/map.php' ); }
			/*******************************/
			public function date_select( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/date_select.php' ); }
			public function time( $event_id, $all_dates = [], $all_times = [], $date = '', $single = true ): void { require MPWEM_Functions::template_path( 'layout/time.php' ); }
			public function registration( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/registration.php' ); }
			public function registration_content( $event_id, $all_dates = [], $all_times = [], $date = '' ): void { require MPWEM_Functions::template_path( 'layout/registration_content.php' ); }
			/*******************************/
			public function event_date_list( $event_id, $event_infos = [] ) { require MPWEM_Functions::template_path( 'layout/date_list.php' ); }
			public function date_only( $event_id, $event_infos = [] ) { require MPWEM_Functions::template_path( 'layout/date_only.php' ); }
			public function time_only( $event_id, $event_infos = [] ) { require MPWEM_Functions::template_path( 'layout/time_only.php' ); }
			/*************************************/
			public function faq( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/faq.php' ); }
			public function related( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/related_event.php' ); }
			public function social( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/social.php' ); }
			public function timeline( $event_id ): void { require MPWEM_Functions::template_path( 'layout/timeline.php' ); }
			public function event_tags( $event_id ) {
				ob_start();
				$tags = get_the_terms( $event_id, 'mep_tag' );
				if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
					?>
                    <div class="location-widgets mep-event-tags-widget">
                        <div>
                            <div class="location-title"><?php echo esc_html( mep_get_option( 'mep_tags_text', 'label_setting_sec', __( 'Event Tags', 'mage-eventpress' ) ) ); ?></div>
                            <p class="mep-event-tags-list">
								<?php
									foreach ( $tags as $tag ) {
										echo '<a href="' . esc_url( get_term_link( $tag->term_id, 'mep_tag' ) ) . '" rel="tag" class="mep-tag-link">' . esc_html( $tag->name ) . '</a>';
									}
								?>
                            </p>
                        </div>
                    </div>
					<?php
				}
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_single_tags', $content, $event_id );
			}
			public function event_add_calender( $event_id, $all_dates = [], $upcoming_date = '' ) { require MPWEM_Functions::template_path( 'layout/add_calendar.php' ); }
			public function speakers( $event_id, $event_infos = [] ) { require MPWEM_Functions::template_path( 'layout/speaker_list.php' ); }
			/**************************/
			public function get_mpwem_ticket() {
				// Sanitize and validate input
				$post_id = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;
				$dates   = isset( $_REQUEST['dates'] ) ? sanitize_text_field( $_REQUEST['dates'] ) : '';
				// Check if post exists and is published
				if ( ! $post_id || get_post_status( $post_id ) !== 'publish' ) {
					wp_send_json_error( 'Invalid or unpublished Event.', 'mage-eventpress' );
					wp_die();
				}
				// Trigger your action safely
				do_action( 'mpwem_registration_content', $post_id, [], [], $dates );
				wp_die(); // Always use wp_die() instead of die() in WordPress
			}
			public function get_mpwem_time() {
				$event_id    = $_REQUEST['post_id'] ?? '';
				$date        = $_REQUEST['dates'] ?? '';
				$hidden_date = $date ? date( 'Y-m-d', strtotime( $date ) ) : '';
				$all_dates   = MPWEM_Functions::get_dates( $event_id );
				$all_times   = MPWEM_Functions::get_times( $event_id, $all_dates, $hidden_date );
				//echo '<pre>';print_r($all_times);echo '</pre>';
				?>
                <label>
                    <span><?php esc_html_e( 'Select Time', 'mage-eventpress' ); ?></span>
                    <i class="far fa-clock"></i>
                    <select class="formControl" name="mpwem_time" id="mpwem_time">
						<?php foreach ( $all_times as $times ) { ?>
                            <option value="<?php echo esc_attr( $hidden_date . ' ' . $times['start']['time'] ); ?>"><?php echo esc_html( $times['start']['label'] ); ?></option>
						<?php } ?>
                    </select>
                </label>
				<?php
				die();
			}
			public function mpwem_load_event_list_page() {
				$atts   = array(
					'cat'     => sanitize_text_field( $_REQUEST['cat'] ?? '' ),
					'org'     => sanitize_text_field( $_REQUEST['org'] ?? '' ),
					'style'   => sanitize_text_field( $_REQUEST['style'] ?? 'grid' ),
					'column'  => intval( $_REQUEST['column'] ?? 3 ),
					'city'    => sanitize_text_field( $_REQUEST['city'] ?? '' ),
					'country' => sanitize_text_field( $_REQUEST['country'] ?? '' ),
					'status'  => sanitize_text_field( $_REQUEST['status'] ?? 'upcoming' ),
					'year'    => sanitize_text_field( $_REQUEST['year'] ?? '' ),
					'sort'    => sanitize_text_field( $_REQUEST['sort'] ?? 'ASC' ),
					'show'    => intval( $_REQUEST['show'] ?? - 1 ),
				);
				$paged  = intval( $_REQUEST['page'] ?? 1 );
				$style  = $atts['style'];
				$column = $style != 'grid' ? 1 : $atts['column'];
				$loop   = MPWEM_Query::event_query( $atts['show'], $atts['sort'], $atts['cat'], $atts['org'], $atts['city'], $atts['country'], $atts['status'], '', $atts['year'], $paged );
				ob_start();
				while ( $loop->have_posts() ) {
					$loop->the_post();
					if ( $style == 'grid' && (int) $column > 0 ) {
						$columnNumber = 'column_style';
						$width        = 100 / (int) $column;
					} else {
						$columnNumber = 'one_column';
						$width        = 100;
					}
					do_action( 'mep_event_list_shortcode', get_the_id(), $columnNumber, $style, $width, '' );
				}
				wp_reset_postdata();
				echo ob_get_clean();
				die();
			}
			public function mpwem_load_date() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$all_dates = MPWEM_Functions::get_all_dates( $post_id );
				MPWEM_Layout::load_date( $post_id, $all_dates );
				die();
			}
			public function mpwem_get_date_list() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
					wp_die();
				}
				$event_id              = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				$all_dates             = MPWEM_Functions::get_dates( $event_id );
				$date_type             = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
				$mep_show_end_datetime = MPWEM_Global_Function::get_post_info( $event_id, 'mep_show_end_datetime', 'yes' );
				array_shift( $all_dates );
				if ( sizeof( $all_dates ) > 0 ) {
					?>
                    <div class="list_date_list">
						<?php
							if ( $date_type == 'no' || $date_type == 'yes' ) {
								$date        = ! empty( $date ) ? $date : current( $all_dates )['time'];
								$date_format = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : 'date';
								foreach ( $all_dates as $dates ) {
									$start_time = array_key_exists( 'time', $dates ) ? $dates['time'] : '';
									$end_time   = array_key_exists( 'end', $dates ) ? $dates['end'] : '';
									if ( $start_time ) {
										$event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $start_time ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
										?>
                                        <div class="date_item">
											<?php if ( $end_time && $mep_show_end_datetime == 'yes' ) {
												if ( strtotime( gmdate( 'Y-m-d', strtotime( $start_time ) ) ) == strtotime( gmdate( 'Y-m-d', strtotime( $end_time ) ) ) ) { ?>
                                                    <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $start_time, $date_format, $event_id ) . ' - ' . MPWEM_Global_Function::date_format( $end_time, 'time', $event_id ) ); ?></a>
												<?php } else { ?>
                                                    <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $start_time, $date_format, $event_id ) ); ?></a>
                                                    <span>-</span>
                                                    <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $end_time, $date_format, $event_id ) ); ?></a>
													<?php
												}
											} else { ?>
                                                <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $start_time, $date_format, $event_id ) ); ?></a>
											<?php } ?>
                                        </div>
										<?php
									}
								}
							} else {
								foreach ( $all_dates as $date ) {
									$all_times = MPWEM_Functions::get_times( $event_id, $all_dates, $date );
									$event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $date ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
									?>
                                    <div class="date_item">
                                        <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( MPWEM_Global_Function::date_format( $date, '', $event_id ) ); ?></a>
										<?php if ( sizeof( $all_times ) ) {
											foreach ( $all_times as $times ) {
												$time_info = array_key_exists( 'start', $times ) ? $times['start'] : [];
												if ( sizeof( $time_info ) > 0 ) {
													$label = array_key_exists( 'label', $time_info ) ? $time_info['label'] : '';
													$time  = array_key_exists( 'time', $time_info ) ? $time_info['time'] : '';
													if ( $time ) {
														$full_date = $date . ' ' . $time;
														$time      = MPWEM_Global_Function::date_format( $full_date, 'time', $event_id );
														$event_url = add_query_arg( [ 'action' => 'mpwem_date_' . $event_id, 'date' => strtotime( $full_date ), '_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ) ], get_the_permalink( $event_id ) );
														?>
                                                        <a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( $label ? $label . '(' . $time . ')' : $time ) ?></a>
														<?php
													}
												}
											}
										} ?>
                                    </div>
									<?php
								}
							}
						?>
                    </div>
					<?php
				}
				wp_die();
			}
			/***********************/
			public function seat_status( $event_id, $event_infos = [], $date = '' ) {
				$event_infos               = sizeof( $event_infos ) > 0 ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
				$show_available_seat       = array_key_exists( 'mep_available_seat', $event_infos ) ? $event_infos['mep_available_seat'] : 'on';
				$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
				$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
				$hide_seat_status          = array_key_exists( 'mep_event_hide_total_seat_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_total_seat_from_details'] : 'no';
				$upcoming_date             = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
				$date                      = $date != '' ? $date : $upcoming_date;
				if ( $hide_seat_status == 'no' && $date ) { ?>
                    <div class="mpwem_seat_status">
						<?php require MPWEM_Functions::template_path( 'layout/seat_status.php' ); ?>
                    </div>
					<?php
				}
			}
			public function mpwem_load_seat_status() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
					wp_die();
				}
				$event_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				$date     = isset( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : '';
				require MPWEM_Functions::template_path( 'layout/seat_status.php' );
				wp_die();
			}
			public function mpwem_reload_seat_status() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
					die;
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
				if ( $post_id && $date ) {
					$total_sold      = mep_ticket_type_sold( $post_id, '', $date );
					$total_ticket    = MPWEM_Functions::get_total_ticket( $post_id, $date );
					$total_reserve   = MPWEM_Functions::get_reserve_ticket( $post_id, $date );
					$total_available = $total_ticket - ( $total_sold + $total_reserve );
					$total_available = max( $total_available, 0 );
					echo esc_html( $total_ticket . '-' . $total_sold . '-' . $total_reserve . '=' . $total_available );
				}
				die();
			}
			/*********************************/
			public function list_thumb( $event_infos ) {
				$event_id = array_key_exists( 'event_id', $event_infos ) ? $event_infos['event_id'] : 0;
				// Check for custom list thumbnail first (from Gallery Settings)
				$custom_thumbnail_id = get_post_meta( $event_id, 'mep_list_thumbnail', true );
				if ( $custom_thumbnail_id ) {
					// Use custom thumbnail from Gallery Settings
					$thumbnail_url = wp_get_attachment_image_url( $custom_thumbnail_id, 'full' );
				} else {
					// Fall back to featured image
					$thumbnail_url = MPWEM_Global_Function::get_image_url( $event_id, '', 'full' );
				}
				?>
                <div class="mep_list_thumb mpwem_style">
                    <div data-href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>" data-bg-image="<?php echo esc_url( $thumbnail_url ); ?>"></div>
					<?php do_action( 'mpwem_list_ribbon', $event_infos ); ?>
				</div>
				<?php
			}
			public function list_location( $event_infos ) {
				$event_id     = array_key_exists( 'event_id', $event_infos ) ? $event_infos['event_id'] : 0;
				$address_type = array_key_exists( 'mep_org_address', $event_infos ) ? $event_infos['mep_org_address'] : '';
				if ( $address_type ) {
					$org_arr  = get_the_terms( $event_id, 'mep_org' );
					$org_id   = $org_arr[0]->term_id;
					$location = get_term_meta( $org_id, 'org_location', true );
				} else {
					$location = array_key_exists( 'mep_location_venue', $event_infos ) ? $event_infos['mep_location_venue'] : '';
				}
				if ( $event_id > 0 && $location ) {
					$location_title      = array_key_exists( 'location_title', $event_infos ) ? $event_infos['location_title'] : '';
					$icon_setting_sec    = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
					$icon_setting_sec    = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
					$event_location_icon = array_key_exists( 'mep_event_location_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_location_icon'] : 'fas fa-map-marker-alt';
					?>
                    <div class="list_content upcomming_location">
                        <span class="<?php echo esc_attr( $event_location_icon ); ?>"></span>
						<?php echo esc_html( $location_title . ' ' . $location ); ?>
                    </div>
				<?php }
			}
			public function list_organizer( $event_infos ) {
				$organizer_name = array_key_exists( 'organizer_name', $event_infos ) ? $event_infos['organizer_name'] : '';
				if ( $organizer_name ) {
					$organizer_title      = array_key_exists( 'organizer_title', $event_infos ) ? $event_infos['organizer_title'] : '';
					$icon_setting_sec     = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
					$icon_setting_sec     = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
					$event_organizer_icon = array_key_exists( 'mep_event_organizer_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_organizer_icon'] : 'far fa-list-alt';
					?>
                    <div class="list_content upcomming_organizer">
                        <span class="<?php echo esc_attr( $event_organizer_icon ); ?>"></span>
						<?php echo esc_html( $organizer_title . ' ' . $organizer_name ); ?>
                    </div>
				<?php }
			}
			public function list_price( $event_infos ) {
				$event_list_setting_sec = array_key_exists( 'event_list_setting_sec', $event_infos ) ? $event_infos['event_list_setting_sec'] : [];
				$event_list_setting_sec = empty( $event_list_setting_sec ) && ! is_array( $event_list_setting_sec ) ? [] : $event_list_setting_sec;
				$show_price             = array_key_exists( 'mep_event_price_show', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_price_show'] : 'yes';
				if ( $show_price == 'yes' ) {
					$event_id         = array_key_exists( 'event_id', $event_infos ) ? $event_infos['event_id'] : 0;
					$ticket_types     = array_key_exists( 'mep_event_ticket_type', $event_infos ) ? $event_infos['mep_event_ticket_type'] : [];
					$show_price_label = sizeof( $ticket_types ) > 1 ? __( 'Price Starts from:', 'mage-eventpress' ) : __( 'Price:', 'mage-eventpress' );
					?>
                    <p class="list_price"><?php echo esc_html( $show_price_label ) . " " . wp_kses_post( wc_price( MPWEM_Functions::get_min_price( $event_id ) ) ); ?></p>
					<?php
				}
			}
			public function list_upcoming_date( $event_infos ) {
				$upcoming_date = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
				if ( $upcoming_date ) {
					$icon_setting_sec        = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
					$icon_setting_sec        = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
					$event_date_icon         = array_key_exists( 'mep_event_date_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_date_icon'] : 'far fa-calendar-alt';
					$event_list_setting_sec  = array_key_exists( 'event_list_setting_sec', $event_infos ) ? $event_infos['event_list_setting_sec'] : [];
					$event_list_setting_sec  = empty( $event_list_setting_sec ) && ! is_array( $event_list_setting_sec ) ? [] : $event_list_setting_sec;
					$hide_only_end_time_list = array_key_exists( 'mep_event_hide_end_time_list', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_hide_end_time_list'] : 'no';
					$date_format             = MPWEM_Global_Function::check_time_exit_date( $upcoming_date ) ? 'full' : 'date';
					$end_time                = array_key_exists( 'end_time', $event_infos ) ? $event_infos['end_time'] : '';
					$event_id                = array_key_exists( 'event_id', $event_infos ) ? $event_infos['event_id'] : '';
					?>
                    <div class="list_content upcomming_date_only_only">
                        <span class="<?php echo esc_attr( $event_date_icon ); ?>"></span><?php
							echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date, $date_format, $event_id ) );
							if ( $end_time && $hide_only_end_time_list == 'no' ) {
								if ( strtotime( date( 'Y-m-d', strtotime( $upcoming_date ) ) ) == strtotime( date( 'Y-m-d', strtotime( $end_time ) ) ) ) {
									$end_date_format = 'time';
								} else {
									$end_date_format = MPWEM_Global_Function::check_time_exit_date( $end_time ) ? 'full' : 'date';
								}
								echo ' - ' . esc_html( MPWEM_Global_Function::date_format( $end_time, $end_date_format, $event_id ) );
							}
						?>
                    </div>
				<?php }
			}
			public function list_upcoming_date_only( $event_infos ) {
				$upcoming_date = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
				if ( $upcoming_date ) {
					$icon_setting_sec        = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
					$icon_setting_sec        = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
					$event_date_icon         = array_key_exists( 'mep_event_date_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_date_icon'] : 'far fa-calendar-alt';
					$event_list_setting_sec  = array_key_exists( 'event_list_setting_sec', $event_infos ) ? $event_infos['event_list_setting_sec'] : [];
					$event_list_setting_sec  = empty( $event_list_setting_sec ) && ! is_array( $event_list_setting_sec ) ? [] : $event_list_setting_sec;
					$hide_only_end_time_list = array_key_exists( 'mep_event_hide_end_time_list', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_hide_end_time_list'] : 'no';
					$end_time                = array_key_exists( 'end_time', $event_infos ) ? $event_infos['end_time'] : '';
					?>
                    <div class="list_content upcomming_date_only">
                        <span class="<?php echo esc_attr( $event_date_icon ); ?>"></span><?php
							echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date ) );
							if ( $end_time && $hide_only_end_time_list == 'no' ) {
								if ( strtotime( date( 'Y-m-d', strtotime( $upcoming_date ) ) ) != strtotime( date( 'Y-m-d', strtotime( $end_time ) ) ) ) {
									echo ' - ' . esc_html( MPWEM_Global_Function::date_format( $end_time ) );
								}
							}
						?>
                    </div>
				<?php }
			}
			public function list_upcoming_time( $event_infos ) {
				$upcoming_date = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
				if ( $upcoming_date && MPWEM_Global_Function::check_time_exit_date( $upcoming_date ) ) {
					$icon_setting_sec = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
					$icon_setting_sec = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
					$time_icon        = array_key_exists( 'mep_event_time_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_time_icon'] : 'fas fa-clock';
					$end_time         = array_key_exists( 'end_time', $event_infos ) ? $event_infos['end_time'] : '';
					?>
                    <div class="list_content upcomming_time_only">
                        <span class="<?php echo esc_attr( $time_icon ); ?>"></span><?php
							echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date, 'time' ) );
							if ( $end_time && MPWEM_Global_Function::check_time_exit_date( $end_time ) ) {
								echo ' - ' . esc_html( MPWEM_Global_Function::date_format( $end_time, 'time' ) );
							}
						?>
                    </div>
				<?php }
			}
			public function list_sort_date( $event_infos ) {
				$upcoming_date = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
				if ( $upcoming_date ) {
					?>
                    <div class="mep-ev-start-date">
                        <div class="mep-day"><?php echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date, 'day' ) ); ?></div>
                        <div class="mep-month"><?php echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date, 'month' ) ); ?></div>
                    </div>
				<?php }
			}
			public function list_more_date_button( $event_infos ) {
				$event_id                  = array_key_exists( 'event_id', $event_infos ) ? $event_infos['event_id'] : 0;
				$all_dates                 = array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
				$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
				$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
				$hide_date_list            = array_key_exists( 'mep_event_hide_event_schedule_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_event_schedule_details'] : 'no';
				$event_list_setting_sec    = array_key_exists( 'event_list_setting_sec', $event_infos ) ? $event_infos['event_list_setting_sec'] : [];
				$event_list_setting_sec    = empty( $event_list_setting_sec ) && ! is_array( $event_list_setting_sec ) ? [] : $event_list_setting_sec;
				$show_date_list            = array_key_exists( 'mep_date_list_in_event_listing', $event_list_setting_sec ) ? $event_list_setting_sec['mep_date_list_in_event_listing'] : 'yes';
				if ( sizeof( $all_dates ) > 1 && $hide_date_list == 'no' && $show_date_list == 'yes' ) { ?>
                    <div class="mpwem_style mpwem_list_date_list">
                        <button type="button" data-event-id="<?php echo esc_attr( $event_id ); ?>" class="_button_theme_light_mt_xs mpwem_get_date_list" data-collapse-target="#mpwem_more_date_<?php echo esc_attr( $event_id ); ?>" data-open-text="<?php esc_attr_e( 'Hide Date Lists', 'mage-eventpress' ); ?>" data-close-text="<?php esc_attr_e( 'View More Date', 'mage-eventpress' ); ?>"><span data-text><?php esc_html_e( 'View More Date', 'mage-eventpress' ); ?></span></button>
                        <div class="date_list_area" data-collapse="#mpwem_more_date_<?php echo esc_attr( $event_id ); ?>"></div>
                    </div>
				<?php }
			}
			public function list_hover( $event_infos ) {
				$event_id                 = array_key_exists( 'event_id', $event_infos ) ? $event_infos['event_id'] : 0;
				$event_list_setting_sec   = array_key_exists( 'event_list_setting_sec', $event_infos ) ? $event_infos['event_list_setting_sec'] : [];
				$event_list_setting_sec   = empty( $event_list_setting_sec ) && ! is_array( $event_list_setting_sec ) ? [] : $event_list_setting_sec;
				$mep_hide_event_hover_btn = array_key_exists( 'mep_hide_event_hover_btn', $event_list_setting_sec ) ? $event_list_setting_sec['mep_hide_event_hover_btn'] : 'no';
				if ( 'yes' == $mep_hide_event_hover_btn ) { ?>
                    <div class="item_hover_effect">
                        <a href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>"><?php esc_html_e( 'Book Now', 'mage-eventpress' ); ?></a>
                    </div>
				<?php }
			}
			public function list_ribbon( $event_infos ) {
				$available                      = array_key_exists( 'available_seat', $event_infos ) ? $event_infos['available_seat'] : 0;
				$all_dates                      = array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
				$reg_status                     = array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
				$event_type                     = array_key_exists( 'mep_event_type', $event_infos ) ? $event_infos['mep_event_type'] : 'offline';
				$recurring                      = array_key_exists( 'mep_enable_recurring', $event_infos ) && $event_infos['mep_enable_recurring'] ? $event_infos['mep_enable_recurring'] : 'no';
				$general_setting_sec            = array_key_exists( 'general_setting_sec', $event_infos ) ? $event_infos['general_setting_sec'] : [];
				$general_setting_sec            = empty( $general_setting_sec ) && ! is_array( $general_setting_sec ) ? [] : $general_setting_sec;
				$sold_out_ribbon                = array_key_exists( 'mep_show_sold_out_ribbon_list_page', $general_setting_sec ) ? $general_setting_sec['mep_show_sold_out_ribbon_list_page'] : 'no';
				$limited_availability_ribbon    = array_key_exists( 'mep_show_limited_availability_ribbon', $general_setting_sec ) ? $general_setting_sec['mep_show_limited_availability_ribbon'] : 'no';
				$limited_availability_threshold = array_key_exists( 'mep_limited_availability_threshold', $general_setting_sec ) ? $general_setting_sec['mep_limited_availability_threshold'] : 5;
				?>
                <div class="mepev-ribbons">
					<?php
						if ( sizeof( $all_dates ) > 0 ) {
							if ( $recurring == 'yes' ) {
								?>
                                <div class='mepev-ribbon recurring'><i class="fas fa-history"></i> <?php esc_html_e( 'Recurring', 'mage-eventpress' ); ?></div><?php
							}
							if ( $recurring == 'everyday' ) {
								?>
                                <div class='mepev-ribbon multidate'><i class="far fa-calendar-alt"></i> <?php esc_html_e( 'Multi Date', 'mage-eventpress' ); ?></div><?php
							}
						}
						if ( $event_type == 'online' ) {
							?>
                            <div class='mepev-ribbon online'><i class="fas fa-vr-cardboard"></i> <?php esc_html_e( 'Virtual', 'mage-eventpress' ); ?></div><?php
						}
						if ( $sold_out_ribbon == 'yes' && $reg_status == 'on' && $available <= 0 ) {
							?>
                            <div class="mepev-ribbon sold-out"><?php esc_html_e( 'Sold Out', 'mage-eventpress' ); ?></div><?php
						} elseif ( $limited_availability_ribbon == 'yes' && $available > 0 && $available <= $limited_availability_threshold ) {
							?>
                            <div class="mepev-ribbon limited-availability"><?php esc_html_e( 'Limited Availability', 'mage-eventpress' ); ?></div><?php
						}
					?>
                </div>
				<?php
			}
		}
		new MPWEM_Hooks();
	}
