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
				/**************************/
				add_action( 'mpwem_organizer', [ $this, 'organizer' ], 10, 2 );
				add_action( 'mep_event_list_org_names', [ $this, 'event_list_org_names' ], 10, 2 );
				add_action( 'mep_event_list_cat_names', [ $this, 'event_list_cat_names' ], 10, 2 );
				add_action( 'mep_event_organizer', [ $this, 'event_organizer' ] );
				add_action( 'mep_event_organizer_name', [ $this, 'event_organizer_name' ] );
				add_action( 'mep_event_organized_by', [ $this, 'event_organized_by' ] );
				/**************************/
				add_action( 'mpwem_location', [ $this, 'location' ], 10, 2 );
				add_action( 'mpwem_location_only', [ $this, 'location_only' ], 10, 2 );
				add_action( 'mep_event_location_street', [ $this, 'event_location_street' ] );
				add_action( 'mep_event_location_city', [ $this, 'event_location_city' ] );
				add_action( 'mep_event_location_state', [ $this, 'event_location_state' ] );
				add_action( 'mep_event_location_postcode', [ $this, 'event_location_postcode' ] );
				add_action( 'mep_event_location_country', [ $this, 'event_location_country' ] );
				add_action( 'mep_event_address_list_sidebar', [ $this, 'event_address_list_sidebar' ] );
				add_action( 'mep_event_location', [ $this, 'event_location' ], 10, 2 );
				add_action( 'mep_event_location_ticket', [ $this, 'event_location' ], 10, 2 );
				/**************************/
				add_action( 'mpwem_time', [ $this, 'time' ], 10, 5 );
				add_action( 'mpwem_registration', [ $this, 'registration' ], 10, 4 );
				add_action( 'mep_add_to_cart', [ $this, 'registration' ], 10, 4 );
				add_action( 'mpwem_registration_content', [ $this, 'registration_content' ], 10, 4 );
				/**************************/
				add_action( 'mpwem_date_select', [ $this, 'date_select' ], 10, 4 );
				add_action( 'mep_event_date', [ $this, 'event_date' ] );
				add_action( 'mpwem_date_list', [ $this, 'event_date_list' ], 10, 3 );
				add_action( 'mpwem_date_only', [ $this, 'date_only' ], 10, 2 );
				add_action( 'mpwem_time_only', [ $this, 'time_only' ], 10, 2 );
				/**************************/
				add_action( 'mpwem_faq', [ $this, 'faq' ], 10, 4 );
				add_action( 'mep_event_faq', [ $this, 'event_faq' ] );
				/**************************/
				add_action( 'mpwem_map', [ $this, 'map' ], 10, 4 );
				add_action( 'mep_event_map', [ $this, 'event_map' ] );
				/**************************/
				//add_action( 'mpwem_related', [ $this, 'related' ], 10, 4 );
				/**************************/
				add_action( 'mpwem_social', [ $this, 'social' ], 10, 4 );
				add_action( 'mep_event_social_share', [ $this, 'event_social_share' ] );
				/**************************/
				add_action( 'mpwem_timeline', [ $this, 'timeline' ], 10, 4 );
				/**************************/
				add_action( 'mep_event_seat', [ $this, 'event_seat' ] );
				/**************************/
				add_action( 'mep_event_speakers_list', [ $this, 'event_speakers_list' ] );
				/**************************/
				add_action( 'mep_event_tags', [ $this, 'event_tags' ] );
				add_action( 'mep_event_tags_name', [ $this, 'event_tags_name' ] );
				add_action( 'mep_event_list_tag_names', [ $this, 'event_list_tag_names' ], 10, 2 );
				/**************************/
				add_action( 'mpwem_add_calender', [ $this, 'event_add_calender' ], 10, 3 );
				/**************************/
				add_action( 'wp_ajax_get_mpwem_ticket', array( $this, 'get_mpwem_ticket' ) );
				add_action( 'wp_ajax_nopriv_get_mpwem_ticket', array( $this, 'get_mpwem_ticket' ) );
				add_action( 'wp_ajax_get_mpwem_time', array( $this, 'get_mpwem_time' ) );
				add_action( 'wp_ajax_nopriv_get_mpwem_time', array( $this, 'get_mpwem_time' ) );
				add_action( 'wp_ajax_mpwem_load_event_list_page', array( $this, 'mpwem_load_event_list_page' ) );
				add_action( 'wp_ajax_nopriv_mpwem_load_event_list_page', array( $this, 'mpwem_load_event_list_page' ) );
			}
			public function title( $event_id, $only = '' ): void { require MPWEM_Functions::template_path( 'layout/title.php' ); }
			public function organizer( $event_id, $only = '' ): void { require MPWEM_Functions::template_path( 'layout/organizer.php' ); }
			public function event_list_org_names( $org, $unq_id = '' ): void {
				ob_start();
				?>
                <div class="mep-events-cats-list">
					<?php
						if ( $org > 0 ) {
							$terms = get_terms( array(
								'parent'   => $org,
								'taxonomy' => 'mep_org'
							) );
						} else {
							$terms = get_terms( array(
								'taxonomy' => 'mep_org'
							) );
						}
					?>
                    <div class="mep-event-cat-controls">
                        <button type="button" class="mep-cat-control" data-mixitup-control data-filter="all"><?php esc_html_e( 'All', 'mage-eventpress' ); ?></button><?php foreach ( $terms as $_terms ) { ?>
                            <button type="button" class="mep-cat-control" data-mixitup-control data data-filter=".<?php echo esc_attr( $unq_id . 'mage-' . $_terms->term_id ); ?>"><?php echo esc_html( $_terms->name ); ?></button><?php } ?>
                    </div>
                </div>
				<?php
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_organization_name_filter_list', $content );
			}
			public function event_list_cat_names( $cat, $unq_id = '' ): void {
				ob_start();
				?>
                <div class="mep-events-cats-list">
					<?php
						if ( $cat > 0 ) {
							$terms = get_terms( array(
								'parent'   => $cat,
								'taxonomy' => 'mep_cat'
							) );
						} else {
							$terms = get_terms( array(
								'taxonomy' => 'mep_cat'
							) );
						}
					?>
                    <div class="mep-event-cat-controls">
                        <button type="button" class="mep-cat-control" data-mixitup-control data-filter="all"><?php esc_html_e( 'All', 'mage-eventpress' ); ?></button>
						<?php foreach ( $terms as $_terms ) { ?>
                            <button type="button" class="mep-cat-control" data-mixitup-control data data-filter=".<?php echo esc_attr( $unq_id . 'mage-' . $_terms->term_id ); ?>"><?php echo esc_html( $_terms->name ); ?></button>
						<?php } ?>
                    </div>
                </div>
				<?php
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_category_name_filter_list', $content );
			}
			public function event_organizer( $event_id ) {
				ob_start();
				$org = get_the_terms( $event_id, 'mep_org' );
				if ( ! empty( $org ) ) {
					require MPWEM_Functions::template_path( 'single/organizer.php' );
				}
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_single_org_name', $content, $event_id );
			}
			public function event_organizer_name( $event_id ) {
				ob_start();
				$org   = get_the_terms( get_the_id(), 'mep_org' );
				$names = [];
				if ( sizeof( $org ) > 0 ) {
					foreach ( $org as $value ) {
						$names[] = $value->name;
					}
				}
				echo esc_html( implode( ', ', $names ) );
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_single_org_name', $content, $event_id );
			}
			public function event_organized_by( $event_id ) {
				$org_terms = get_the_terms( $event_id, 'mep_org' );
				if ( $org_terms && ! is_wp_error( $org_terms ) && count( $org_terms ) > 0 ) :?>
                    <div class="mep-org-details">
                        <div class="org-name">
                            <div><?php echo mep_get_option( 'mep_organized_by_text', 'label_setting_sec' ) ? mep_get_option( 'mep_organized_by_text', 'label_setting_sec' ) : __( 'Organized By:', 'mage-eventpress' ); ?></div>
							<?php foreach ( $org_terms as $index => $org ): ?>
                                <strong><?php echo esc_html( $org->name ); ?><?php if ( $index < count( $org_terms ) - 1 ): ?>|<?php endif; ?></strong>
							<?php endforeach; ?>
                        </div>
                    </div>
				<?php else :
					do_action( 'mep_event_organizer', $event_id );
				endif;
			}
			/**********************************/
			public function location( $event_id, $type = '' ): void { require MPWEM_Functions::template_path( 'layout/location.php' ); }
			public function location_only( $event_id, $type = '' ): void { require MPWEM_Functions::template_path( 'layout/location_only.php' ); }
			public function event_location_street( $event_id ) {
				$location = MPWEM_Functions::get_location( $event_id, 'street' );
				if ( $location ) {
					?><span><?php echo esc_html( $location ); ?></span><?php
				}
			}
			public function event_location_city( $event_id ) {
				$location = MPWEM_Functions::get_location( $event_id, 'city' );
				if ( $location ) {
					?><span><?php echo esc_html( $location ); ?></span><?php
				}
			}
			public function event_location_state( $event_id ) {
				$location = MPWEM_Functions::get_location( $event_id, 'state' );
				if ( $location ) {
					?><span><?php echo esc_html( $location ); ?></span><?php
				}
			}
			public function event_location_postcode( $event_id ) {
				$location = MPWEM_Functions::get_location( $event_id, 'zip' );
				if ( $location ) {
					?><span><?php echo esc_html( $location ); ?></span><?php
				}
			}
			public function event_location_country( $event_id ) {
				$location = MPWEM_Functions::get_location( $event_id, 'country' );
				if ( $location ) {
					?><span><?php echo esc_html( $location ); ?></span><?php
				}
			}
			public function event_address_list_sidebar( $event_id ) {
				ob_start();
				require MPWEM_Functions::template_path( 'single/location_list.php' );
				echo ob_get_clean();
			}
			public function event_location( $event_id, $event_meta = '' ) {
				$location_info = MPWEM_Functions::get_location( $event_id );
				ob_start();
				echo esc_html( implode( ', ', array_filter( $location_info ) ) );
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_location_in_ticket', $content, $event_id, $event_meta, $location_info );
			}
			/*******************************/
			public function time( $event_id, $all_dates = [], $all_times = [], $date = '', $single = true ): void { require MPWEM_Functions::template_path( 'layout/time.php' ); }
			public function registration( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/registration.php' ); }
			public function registration_content( $event_id, $all_dates = [], $all_times = [], $date = '' ): void { require MPWEM_Functions::template_path( 'layout/registration_content.php' ); }
			public function date_select( $event_id, $event_infos = [] ): void { require MPWEM_Functions::template_path( 'layout/date_select.php' ); }
			/*******************************/
			public function event_date( $event_id ) {
				$start_datetime          = get_post_meta( get_the_id(), 'event_start_datetime', true );
				$start_date              = get_post_meta( get_the_id(), 'event_start_date', true );
				$end_datetime            = get_post_meta( get_the_id(), 'event_end_datetime', true );
				$end_date                = get_post_meta( get_the_id(), 'event_end_date', true );
				$more_date               = get_post_meta( get_the_id(), 'mep_event_more_date', true ) ? maybe_unserialize( get_post_meta( get_the_id(), 'mep_event_more_date', true ) ) : [];
				$recurring               = get_post_meta( get_the_id(), 'mep_enable_recurring', true ) ? get_post_meta( get_the_id(), 'mep_enable_recurring', true ) : 'no';
				$mep_show_upcoming_event = get_post_meta( get_the_id(), 'mep_show_upcoming_event', true ) ? get_post_meta( get_the_id(), 'mep_show_upcoming_event', true ) : 'no';
				$cn                      = 1;
				if ( $recurring == 'yes' ) {
					if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $start_datetime ) ) {
						?>
                        <p><?php echo get_mep_datetime( $start_datetime, 'date-text' ) . ' ' . get_mep_datetime( $start_datetime, 'time' ); ?> - <?php if ( $start_date != $end_date ) {
								echo get_mep_datetime( $end_datetime, 'date-text' ) . ' - ';
							}
								echo get_mep_datetime( $end_datetime, 'time' ); ?></p>,
						<?php
					}
					foreach ( $more_date as $_more_date ) {
						if ( strtotime( current_time( 'Y-m-d H:i' ) ) < strtotime( $_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'] ) ) {
							if ( $mep_show_upcoming_event == 'yes' ) {
								$cnt = 1;
							} else {
								$cnt = $cn;
							}
							if ( $cn == $cnt ) {
								?>
                                <p><?php echo get_mep_datetime( $_more_date['event_more_start_date'], 'date-text' ) . ' ' . get_mep_datetime( $_more_date['event_more_start_time'], 'time' ); ?> - <?php if ( $_more_date['event_more_start_date'] != $_more_date['event_more_end_date'] ) {
										echo get_mep_datetime( $_more_date['event_more_end_date'], 'date-text' ) . ' - ';
									}
										echo get_mep_datetime( $_more_date['event_more_end_time'], 'time' ); ?></p>
								<?php
								$cn ++;
							}
						}
					}
				} elseif ( is_array( $more_date ) && sizeof( $more_date ) > 0 ) {
					?>
                    <p><?php echo get_mep_datetime( $start_datetime, 'date-text' ) . ' ' . get_mep_datetime( $start_datetime, 'time' ); ?> - <?php if ( $start_date != $end_date ) {
							echo get_mep_datetime( $end_datetime, 'date-text' ) . ' - ';
						}
							echo get_mep_datetime( $end_datetime, 'time' ); ?></p>
					<?php foreach ( $more_date as $_more_date ) {
						?>
                        <p><?php echo get_mep_datetime( $_more_date['event_more_start_date'], 'date-text' ) . ' ' . get_mep_datetime( $_more_date['event_more_start_time'], 'time' ); ?> - <?php if ( $_more_date['event_more_start_date'] != $_more_date['event_more_end_date'] ) {
								echo get_mep_datetime( $_more_date['event_more_end_date'], 'date-text' ) . ' - ';
							}
								echo get_mep_datetime( $_more_date['event_more_end_time'], 'time' ); ?></p>
						<?php
					}
				} else {
					?>
                    <p><?php echo get_mep_datetime( $start_datetime, 'date-text' ) . ' ' . get_mep_datetime( $start_datetime, 'time' ); ?> - <?php if ( $start_date != $end_date ) {
							echo get_mep_datetime( $end_datetime, 'date-text' ) . ' - ';
						}
							echo get_mep_datetime( $end_datetime, 'time' ); ?></p>
					<?php
				}
			}
			public function event_date_list( $event_id, $event_infos = [] ) { require MPWEM_Functions::template_path( 'layout/date_list.php' ); }
			public function date_only( $event_id, $event_infos = [] ) { require MPWEM_Functions::template_path( 'layout/date_only.php' ); }
			public function time_only( $event_id, $event_infos = [] ) { require MPWEM_Functions::template_path( 'layout/time_only.php' ); }
			/*************************************/
			public function faq( $event_id ): void { require MPWEM_Functions::template_path( 'layout/faq.php' ); }
			/****************************************/
			public function map( $event_id ): void { require MPWEM_Functions::template_path( 'layout/map.php' ); }
			public function event_map( $event_id ): void { require MPWEM_Functions::template_path( 'layout/map_only.php' ); }
			/*****************************************/
			public function related( $event_id ): void { require MPWEM_Functions::template_path( 'layout/related_event.php' ); }
			/**************************/
			public function social( $event_id ): void { require MPWEM_Functions::template_path( 'layout/social.php' ); }
			public function event_social_share( $event_id ) {
				ob_start();
				$post_id     = $event_id;
				$event_label = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				require MPWEM_Functions::template_path( 'single/share_btn.php' );
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_single_social_share', $content, $event_id );
			}
			/**************************/
			public function timeline( $event_id ): void { require MPWEM_Functions::template_path( 'layout/timeline.php' ); }
			/*************************************/
			public function event_seat( $event_id ) {
				ob_start();
				$all_dates          = MPWEM_Functions::get_dates( $event_id );
				$all_times          = MPWEM_Functions::get_times( $event_id, $all_dates );
				$date               = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
				$total_available    = MPWEM_Functions::get_total_available_seat( $event_id, $date );
				$total_seat         = max( $total_available, 0 );
				$total_sold         = MPWEM_Functions::get_total_sold( $event_id, $date );
				$total_left         = $total_seat - $total_sold;
				$mep_available_seat = MPWEM_Global_Function::get_post_info( $event_id, 'mep_available_seat', 'on' );
				require MPWEM_Functions::template_path( 'single/total_seat.php' );
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_single_total_seat', $content, $event_id );
			}
			public function event_faq( $event_id ) {
				ob_start();
				$mep_event_faq = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_faq' );
				if ( $mep_event_faq ) {
					require MPWEM_Functions::template_path( 'single/faq.php' );
				}
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_faq_list', $content, $event_id );
			}
			public function event_speakers_list( $event_id ) {
				$speakers_id   = get_post_meta( $event_id, 'mep_event_speakers_list', true ) ? maybe_unserialize( get_post_meta( $event_id, 'mep_event_speakers_list', true ) ) : array();
				$speaker_icon  = get_post_meta( $event_id, 'mep_event_speaker_icon', true ) ? get_post_meta( $event_id, 'mep_event_speaker_icon', true ) : 'fa fa-microphone';
				$speaker_label = get_post_meta( $event_id, 'mep_speaker_title', true ) ? get_post_meta( $event_id, 'mep_speaker_title', true ) : esc_html__( "Speaker's", "mage-eventpress" );
				if ( is_array( $speakers_id ) && sizeof( $speakers_id ) > 0 ) {
					require MPWEM_Functions::template_path( 'single/speaker-list.php' );
				}
			}
			/***********************************/
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
			public function event_tags_name() {
				global $post;
				ob_start();
				$tags  = get_the_terms( get_the_id(), 'mep_tag' );
				$names = [];
				if ( sizeof( $tags ) > 0 && ! is_wp_error( $tags ) ) {
					foreach ( $tags as $key => $value ) {
						$names[] = $value->name;
					}
				}
				echo esc_html( implode( ', ', $names ) );
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_single_tags_name', $content, $post->ID );
			}
			public function event_list_tag_names( $tag, $unq_id = '' ) {
				ob_start();
				?>
                <div class="mep-events-cats-list">
					<?php
						if ( $tag > 0 ) {
							$terms = get_terms( array(
								'include'  => explode( ',', $tag ),
								'taxonomy' => 'mep_tag'
							) );
						} else {
							$terms = get_terms( array(
								'taxonomy' => 'mep_tag'
							) );
						}
					?>
                    <div class="mep-event-cat-controls">
                        <button type="button" class="mep-cat-control" data-mixitup-control data-filter="all"><?php esc_html_e( 'All', 'mage-eventpress' ); ?></button><?php foreach ( $terms as $_terms ) { ?>
                            <button type="button" class="mep-cat-control" data-mixitup-control data data-filter=".<?php echo esc_attr( $unq_id . 'mage-' . $_terms->term_id ); ?>"><?php echo esc_html( $_terms->name ); ?></button><?php } ?>
                    </div>
                </div>
				<?php
				$content = ob_get_clean();
				echo apply_filters( 'mage_event_tag_name_filter_list', $content );
			}
			/***********************************/
			public function event_add_calender( $event_id, $all_dates = [], $upcoming_date = '' ) { require MPWEM_Functions::template_path( 'layout/add_calendar.php' ); }
			/**************************/
			public function get_mpwem_ticket() {
				$post_id = $_REQUEST['post_id'] ?? '';
				$dates   = $_REQUEST['dates'] ?? '';
				do_action( 'mpwem_registration_content', $post_id, [], [], $dates );
				die();
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
					mep_update_event_upcoming_date( get_the_id() );
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
		}
		new MPWEM_Hooks();
	}