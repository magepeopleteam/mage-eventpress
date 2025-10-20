<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Event_List' ) ) {
		class MPWEM_Event_List {
			public function __construct() {
				add_action( 'mep_event_list_shortcode', [ $this, 'event_list_shortcode' ], 10, 5 );
				add_action( 'mpwem_list_with_filter_section', array( $this, 'list_with_filter_section' ), 10, 2 );
				add_action( 'mpwem_pagination', array( $this, 'pagination' ), 10, 2 );
			}

			public function event_list_shortcode( $event_id, $columnNumber = '', $style = '', $width = '', $unq_id = '' ) {
				$event_organizer_icon = MPWEM_Global_Function::get_settings( 'icon_setting_sec', 'mep_event_organizer_icon', 'far fa-list-alt' );
				$torg                 = get_the_terms( $event_id, 'mep_org' );
				$org_class            = MPWEM_Global_Function::taxonomy_as_class( $event_id, 'mep_org', $unq_id );
				$cat_class            = MPWEM_Global_Function::taxonomy_as_class( $event_id, 'mep_cat', $unq_id );
				ob_start();
				if ( $style == 'title' ) {
					?>
                    <div class='mep_event_title_list_item mix <?php echo esc_attr( $org_class . ' ' . $cat_class ); ?>'>
                        <a href='<?php echo esc_attr( get_the_permalink( $event_id ) ); ?>'><?php echo esc_html( get_the_title( $event_id ) ); ?></a>
						<?php if ( $torg && ! is_wp_error( $torg ) && count( $torg ) > 0 ) {
							echo ' - <span class="mep_title_list_organizer"><i class="' . esc_attr( $event_organizer_icon ) . '"></i> ' . esc_html( $torg[0]->name ) . '</span>';
						} ?>
                    </div>
					<?php
				} else {
					$now               = current_time( 'Y-m-d H:i:s' );
					$recurring         = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
					$all_dates         = MPWEM_Functions::get_dates( $event_id );
					$all_times         = MPWEM_Functions::get_times( $event_id, $all_dates );
					$upcoming_date     = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
					$start_time_format = MPWEM_Global_Function::check_time_exit_date( $upcoming_date ) ? $upcoming_date : '';
					$end_time_format   = '';
					$end_datetime      = '';
					if ( $recurring == 'no' || $recurring == 'yes' ) {
						$end_date        = current( $all_dates );
						$end_datetime    = is_array($end_date) && array_key_exists( 'end', $end_date ) ? $end_date['end'] : '';
						$end_time_format = MPWEM_Global_Function::check_time_exit_date( $end_datetime ) ? $end_datetime : '';
					} else {
						$end_date = date( 'Y-m-d', strtotime( current( $all_dates ) ) );
						if ( sizeof( $all_times ) > 0 ) {
							$all_times       = current( $all_times );
							$end_time        =  is_array($all_times) && array_key_exists( 'end', $all_times ) ? $all_times['end']['time'] : '';
							$end_datetime    = $end_time ? $end_date . ' ' . $end_time : '';
							$end_time_format = MPWEM_Global_Function::check_time_exit_date( $end_time_format ) ? $end_time_format : '';
						}
					}
					if ( strtotime( date( 'Y-m-d', strtotime( $upcoming_date ) ) ) == strtotime( date( 'Y-m-d', strtotime( $end_datetime ) ) ) ) {
						$end_datetime = '';
					}
					$total_left = $available_seat = MPWEM_Functions::get_total_available_seat( $event_id, $upcoming_date );
					$class_name = $total_left > 0 ? 'event-availabe-seat' : 'event-no-availabe-seat';;
					$tag_class                      = MPWEM_Global_Function::taxonomy_as_class( $event_id, 'mep_tag', $unq_id );
					$event_multidate                = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_more_date', [] );
					$recurring                      = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
					$event_type                     = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_type', 'offline' );
					$reg_status                     = MPWEM_Global_Function::get_post_info( $event_id, 'mep_reg_status' );
					$ticket_types                   = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
					$show_price_label               = sizeof( $ticket_types ) > 1 ? __( 'Price Starts from:', 'mage-eventpress' ) : __( 'Price:', 'mage-eventpress' );
					$author_terms                   = get_the_terms( $event_id, 'mep_org' ) ? get_the_terms( $event_id, 'mep_org' ) : [];
					$show_price                     = MPWEM_Global_Function::get_settings( 'event_list_setting_sec', 'mep_event_price_show', 'yes' );
					$hide_org_list                  = MPWEM_Global_Function::get_settings( 'event_list_setting_sec', 'mep_event_hide_organizer_list', 'no' );
					$hide_location_list             = MPWEM_Global_Function::get_settings( 'event_list_setting_sec', 'mep_event_hide_location_list', 'no' );
					$hide_time_list                 = MPWEM_Global_Function::get_settings( 'event_list_setting_sec', 'mep_event_hide_time_list', 'no' );
					$hide_only_end_time_list        = MPWEM_Global_Function::get_settings( 'event_list_setting_sec', 'mep_event_hide_end_time_list', 'no' );
					$mep_hide_event_hover_btn       = MPWEM_Global_Function::get_settings( 'event_list_setting_sec', 'mep_hide_event_hover_btn', 'no' );
					$sold_out_ribbon                = MPWEM_Global_Function::get_settings( 'event_list_setting_sec', 'mep_show_sold_out_ribbon_list_page', 'no' );
					$limited_availability_ribbon    = MPWEM_Global_Function::get_settings( 'event_list_setting_sec', 'mep_show_limited_availability_ribbon', 'no' );
					$limited_availability_threshold = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_limited_availability_threshold', 5 );
					$event_location_icon            = MPWEM_Global_Function::get_settings( 'icon_setting_sec', 'mep_event_location_icon', 'fas fa-map-marker-alt' );
					$event_organizer_icon           = MPWEM_Global_Function::get_settings( 'icon_setting_sec', 'mep_event_organizer_icon', 'far fa-list-alt' );
					$event_date_icon                = MPWEM_Global_Function::get_settings( 'icon_setting_sec', 'mep_event_date_icon', 'far fa-calendar-alt' );
					$event_time_icon                = MPWEM_Global_Function::get_settings( 'icon_setting_sec', 'mep_event_time_icon', 'fas fa-clock' );
					if ( $style == 'minimal' ) {
						require MPWEM_Functions::template_path( 'list/minimal.php' );
					} else if ( $style == 'native' ) {
						require MPWEM_Functions::template_path( 'list/native.php' );
					} else if ( $style == 'timeline' ) {
						require MPWEM_Functions::template_path( 'list/timeline.php' );
					} else if ( $style == 'spring' ) {
						require MPWEM_Functions::template_path( 'list/spring.php' );
					} else if ( $style == 'winter' ) {
						require MPWEM_Functions::template_path( 'list/winter.php' );
					} else {
						require MPWEM_Functions::template_path( 'list/default.php' );
					}
				}
				do_action( 'mep_event_list_loop_end', $event_id );
				?>
                </div>
				<?php
				$content = ob_get_clean();
				echo $content;
			}

			public function list_with_filter_section( $loop, $params ) {
				ob_start();
				?>
                <div class="mpwem_style">
                    <div class="search_sort_code_area">
                        <div class="search_sort_code">
                            <div class="sort_code_search_box defaultLayout_xs">
                                <div class="flexEqual filter_input_area">
									<?php
										if ( $params['title-filter'] == 'yes' ) { ?>
                                            <label>
                                                <input type="text" name="filter_with_title" class="formControl" placeholder="<?php esc_html_e( 'Search by Title', 'mage-eventpress' ); ?>">
                                            </label>
										<?php }
										$category_lists = MPWEM_Global_Function::get_all_term_data( 'mep_cat' );
										if ( $params['category-filter'] == 'yes' && $category_lists && sizeof( $category_lists ) > 0 ) {
											?>
                                            <label>
                                                <select class="formControl" name="filter_with_category">
                                                    <option selected value=""><?php esc_html_e( 'Select Category', 'mage-eventpress' ); ?></option>
													<?php foreach ( $category_lists as $category ) { ?>
                                                        <option value="<?php echo esc_attr( $category ); ?>"><?php echo esc_html( $category ); ?></option>
													<?php } ?>
                                                </select>
                                            </label>
										<?php }
										$organizer_lists = MPWEM_Global_Function::get_all_term_data( 'mep_org' );
										if ( $params['organizer-filter'] == 'yes' && $organizer_lists && sizeof( $organizer_lists ) > 0 ) {
											?>
                                            <label>
                                                <select class="formControl" name="filter_with_organizer">
                                                    <option selected value=""><?php esc_html_e( 'Select Organizer', 'mage-eventpress' ); ?></option>
													<?php foreach ( $organizer_lists as $organizer ) { ?>
                                                        <option value="<?php echo esc_attr( $organizer ); ?>"><?php echo esc_html( $organizer ); ?></option>
													<?php } ?>
                                                </select>
                                            </label>
										<?php }
										if ( $params['state-filter'] == 'yes' ) {
											$states = array();
											while ( $loop->have_posts() ) {
												$loop->the_post();
												$state = get_post_meta( get_the_ID(), 'mep_state', true );
												if ( ! empty( $state ) ) {
													$states[] = $state;
												}
											}
											$states = array_unique( $states );
											sort( $states );
											if ( ! empty( $states ) ) {
												?>
                                                <label>
                                                    <select class="formControl" name="filter_with_state">
                                                        <option selected value=""><?php esc_html_e( 'Select State', 'mage-eventpress' ); ?></option>
														<?php foreach ( $states as $state ) { ?>
                                                            <option value="<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $state ); ?></option>
														<?php } ?>
                                                    </select>
                                                </label>
												<?php
											}
											wp_reset_postdata();
										}
										if ( $params['city-filter'] == 'yes' ) {
											$cities = array();
											while ( $loop->have_posts() ) {
												$loop->the_post();
												$city  = get_post_meta( get_the_ID(), 'mep_city', true );
												$state = get_post_meta( get_the_ID(), 'mep_state', true );
												if ( ! empty( $city ) ) {
													$cities[] = array(
														'city'     => $city,
														'state'    => $state,
														'display'  => ! empty( $state ) ? $city . ', ' . $state : $city,
														'sort_key' => strtolower( $city ) // Add a lowercase version for sorting
													);
												}
											}
											// Remove duplicates based on city and state combination
											$cities = array_map( "unserialize", array_unique( array_map( "serialize", $cities ) ) );
											// Sort cities alphabetically by the lowercase city name
											usort( $cities, function ( $a, $b ) {
												return strcmp( $a['sort_key'], $b['sort_key'] );
											} );
											if ( ! empty( $cities ) ) {
												?>
                                                <label>
                                                    <select class="formControl" name="filter_with_city">
                                                        <option selected value=""><?php esc_html_e( 'Select City', 'mage-eventpress' ); ?></option>
														<?php foreach ( $cities as $city_data ) { ?>
                                                            <option value="<?php echo esc_attr( $city_data['city'] ); ?>"><?php echo esc_html( $city_data['display'] ); ?></option>
														<?php } ?>
                                                    </select>
                                                </label>
												<?php
											}
											wp_reset_postdata();
										}
										if ( $params['date-filter'] == 'yes' ) { ?>
                                            <label>
                                                <input type="date" name="filter_with_date" class="formControl">
                                            </label>
										<?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="textGray textCenter search_sort_code_counts">
						<?php esc_html_e( 'Showing', 'mage-eventpress' ); ?>
                        <strong class="qty_count"><?php echo esc_html( $params['show'] ); ?></strong>
						<?php esc_html_e( 'of', 'mage-eventpress' ); ?>
                        <strong class="total_filter_qty"><?php echo esc_html( $loop->post_count ); ?></strong>
                    </p>
                </div>
				<?php
				echo ob_get_clean();
			}

			public function pagination( $params, $total_item ) {
				ob_start();
				$per_page = $params['show'] > 1 ? $params['show'] : $total_item;
				?>
                <input type="hidden" name="pagination_per_page" value="<?php echo esc_attr( $per_page ); ?>"/>
                <input type="hidden" name="pagination_style" value="<?php echo esc_attr( $params['pagination-style'] ); ?>"/>
				<?php if ( ( $params['search-filter'] == 'yes' || $params['pagination'] == 'yes' ) && $total_item > $per_page ) { ?>
                    <div class="mpwem_style pagination_area">
                        <div class="allCenter">
							<?php
								if ( $params['pagination-style'] == 'load_more' ) {
									?>
                                    <button type="button"
                                            class="defaultButton pagination_load_more"
                                            data-load-more="0"
                                            data-load-more-text="<?php esc_html_e( 'Load More', 'mage-eventpress' ); ?>"
                                            data-load-less-text="<?php esc_html_e( 'Less More', 'mage-eventpress' ); ?>"
                                    >
										<?php esc_html_e( 'Load More', 'mage-eventpress' ); ?>
                                    </button>
									<?php
								} else {
									$page_mod   = $total_item % $per_page;
									$total_page = (int) ( $total_item / $per_page ) + ( $page_mod > 0 ? 1 : 0 );
									?>
                                    <div class="buttonGroup">
										<?php if ( $total_page > 2 ) { ?>
                                            <button class="defaultButton_xs page_prev" type="button" title="<?php esc_html_e( 'GoTO Previous Page', 'mage-eventpress' ); ?>" disabled>
                                                <span class="fas fa-chevron-left"></span>
                                            </button>
										<?php } ?>
										<?php if ( $total_page > 5 ) { ?>
                                            <div class="ellipse_left" disabled>
                                                <div><span class="fas fa-ellipsis-h"></span></div>
                                            </div>
										<?php } ?>
										<?php for ( $i = 0; $i < $total_page; $i ++ ) { ?>
                                            <button class="defaultButton_xs <?php echo esc_html( $i ) == 0 ? 'active_pagination' : ''; ?>" type="button" data-pagination="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i + 1 ); ?></button>
										<?php } ?>
										<?php if ( $total_page > 5 ) { ?>
                                            <div class="ellipse_right">
                                                <div><span class="fas fa-ellipsis-h"></span></div>
                                            </div>
										<?php } ?>

										<?php if ( $total_page > 2 ) { ?>
                                            <button class="defaultButton_xs page_next" type="button" title="<?php esc_html_e( 'GoTO Next Page', 'mage-eventpress' ); ?>">
                                                <span class="fas fa-chevron-right"></span>
                                            </button>
										<?php } ?>
                                    </div>
									<?php
								}
							?>
                        </div>
                    </div>
					<?php
				}
				echo ob_get_clean();
			}
		}
		new MPWEM_Event_List();
	}