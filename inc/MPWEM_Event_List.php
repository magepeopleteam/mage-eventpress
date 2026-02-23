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
				$event_infos          = MPWEM_Functions::get_all_info( $event_id );
				$icon_setting_sec     = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
				$icon_setting_sec     = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
				$event_organizer_icon = array_key_exists( 'mep_event_organizer_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_organizer_icon'] : 'far fa-list-alt';
				$event_location_icon  = array_key_exists( 'mep_event_location_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_location_icon'] : 'fas fa-map-marker-alt';
				$event_date_icon      = array_key_exists( 'mep_event_date_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_date_icon'] : 'far fa-calendar-alt';
				$event_time_icon      = array_key_exists( 'mep_event_time_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_time_icon'] : 'fas fa-clock';
				$torg                 = get_the_terms( $event_id, 'mep_org' );
				$tcat                 = get_the_terms( $event_id, 'mep_cat' );
				$author_terms         = get_the_terms( $event_id, 'mep_org' ) ? get_the_terms( $event_id, 'mep_org' ) : [];
				$organizer_name='';
                if(is_array( $author_terms ) && sizeof( $author_terms ) > 0 && $author_terms[0]->name){
                    $organizer_name=$author_terms[0]->name;
                }
				$org_class            = MPWEM_Global_Function::taxonomy_as_class( $event_id, 'mep_org', $unq_id );
				$cat_class            = MPWEM_Global_Function::taxonomy_as_class( $event_id, 'mep_cat', $unq_id );
				$tag_class            = MPWEM_Global_Function::taxonomy_as_class( $event_id, 'mep_tag', $unq_id );
				// Get category names for data attribute
				$taxonomy_category = '';
				if ( $tcat && ! is_wp_error( $tcat ) && count( $tcat ) > 0 ) {
					$cat_names = array();
					foreach ( $tcat as $cat ) {
						$cat_names[] = $cat->name;
					}
					$taxonomy_category = implode( ', ', $cat_names );
				}
				// Get organizer names for data attribute
				$taxonomy_organizer = '';
				if ( $torg && ! is_wp_error( $torg ) && count( $torg ) > 0 ) {
					$org_names = array();
					foreach ( $torg as $org ) {
						$org_names[] = $org->name;
					}
					$taxonomy_organizer = implode( ', ', $org_names );
				}
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
					$event_list_setting_sec=MPWEM_Global_Function::data_sanitize( get_option( 'event_list_setting_sec' ) );
					// PHP 8+ compatibility: ensure $event_list_setting_sec is an array
					if ( ! is_array( $event_list_setting_sec ) ) {
						$event_list_setting_sec = [];
					}
					$show_price                     = array_key_exists( 'mep_event_price_show', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_price_show'] : 'yes';
					$hide_org_list                  = array_key_exists( 'mep_event_hide_organizer_list', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_hide_organizer_list'] : 'no';
					$hide_location_list             = array_key_exists( 'mep_event_hide_location_list', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_hide_location_list'] : 'no';
					$hide_time_list                 = array_key_exists( 'mep_event_hide_time_list', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_hide_time_list'] : 'no';
					$hide_only_end_time_list        = array_key_exists( 'mep_event_hide_end_time_list', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_hide_end_time_list'] : 'no';
					$mep_hide_event_hover_btn       = array_key_exists( 'mep_hide_event_hover_btn', $event_list_setting_sec ) ? $event_list_setting_sec['mep_hide_event_hover_btn'] : 'no';
					$general_setting_sec            = array_key_exists( 'general_setting_sec', $event_infos ) ? $event_infos['general_setting_sec'] : [];
					$general_setting_sec            = empty( $general_setting_sec ) && ! is_array( $general_setting_sec ) ? [] : $general_setting_sec;
					$sold_out_ribbon                = array_key_exists( 'mep_show_sold_out_ribbon_list_page', $general_setting_sec ) ? $general_setting_sec['mep_show_sold_out_ribbon_list_page'] : 'no';
					$limited_availability_ribbon    = array_key_exists( 'mep_show_limited_availability_ribbon', $general_setting_sec ) ? $general_setting_sec['mep_show_limited_availability_ribbon'] : 'no';
					$limited_availability_threshold = array_key_exists( 'mep_limited_availability_threshold', $general_setting_sec ) ? $general_setting_sec['mep_limited_availability_threshold'] : 5;
					$now                            = current_time( 'Y-m-d H:i:s' );
					$all_dates                      = array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
					$all_times                      = array_key_exists( 'all_time', $event_infos ) ? $event_infos['all_time'] : [];
					$upcoming_date                  = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
					$event_type                     = array_key_exists( 'mep_event_type', $event_infos ) ? $event_infos['mep_event_type'] : 'offline';
					$recurring                      = array_key_exists( 'mep_enable_recurring', $event_infos ) && $event_infos['mep_enable_recurring'] ? $event_infos['mep_enable_recurring'] : 'no';
					$reg_status                     = array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
					$ticket_types                   = array_key_exists( 'mep_event_ticket_type', $event_infos ) ? $event_infos['mep_event_ticket_type'] : [];
					$event_multidate                = array_key_exists( 'mep_event_more_date', $event_infos ) ? $event_infos['mep_event_more_date'] : [];
					$total_left                     = $available_seat = MPWEM_Functions::get_total_available_seat( $event_id, $upcoming_date );
					$class_name                     = $total_left > 0 ? 'event-availabe-seat' : 'event-no-availabe-seat';
					$show_price_label               = (is_array( $ticket_types ) && sizeof( $ticket_types ) > 1) ? __( 'Price Starts from:', 'mage-eventpress' ) : __( 'Price:', 'mage-eventpress' );
					$start_time_format              = MPWEM_Global_Function::check_time_exit_date( $upcoming_date ) ? $upcoming_date : '';
					$end_time_format                = '';
					$end_datetime                   = '';
					if ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) {
						if ( $recurring == 'no' || $recurring == 'yes' ) {
							$end_date        = current( $all_dates );
							$end_datetime    = is_array( $end_date ) && array_key_exists( 'end', $end_date ) ? $end_date['end'] : '';
							$end_time_format = MPWEM_Global_Function::check_time_exit_date( $end_datetime ) ? $end_datetime : '';
						} else {
							$end_date = date( 'Y-m-d', strtotime( current( $all_dates ) ) );
							if ( is_array( $all_times ) && sizeof( $all_times ) > 0 ) {
								$all_times       = current( $all_times );
								$end_time        = is_array( $all_times ) && array_key_exists( 'end', $all_times ) ? $all_times['end']['time'] : '';
								$end_datetime    = $end_time ? $end_date . ' ' . $end_time : '';
								$end_time_format = MPWEM_Global_Function::check_time_exit_date( $end_time_format ) ? $end_time_format : '';
							}
						}
					}
					$event_infos[ 'available_seat' ] = $available_seat;
					$event_infos[ 'end_time' ] = $end_datetime;
					$event_infos[ 'event_list_setting_sec' ] = $event_list_setting_sec;
					$event_infos[ 'organizer_name' ] = $organizer_name;
					$event_infos[ 'category_tax' ] = $taxonomy_category;
					$event_infos[ 'organizer_tax' ] = $taxonomy_organizer;
					$event_infos[ 'width' ] = $width;
					$event_infos[ 'column_number' ] = $columnNumber;
					$event_infos[ 'class_name' ] = $class_name;
					$event_infos[ 'style' ] = $style;
					$event_infos[ 'org_class' ] = $org_class;
					$event_infos[ 'cat_class' ] = $cat_class;
					$event_infos[ 'tag_class' ] = $tag_class;
					//echo '<pre>';print_r($end_datetime);echo '</pre>';
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
										if ( $params['category-filter'] == 'yes' && is_array( $category_lists ) && sizeof( $category_lists ) > 0 ) {
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
										if ( $params['organizer-filter'] == 'yes' && is_array( $organizer_lists ) && sizeof( $organizer_lists ) > 0 ) {
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
												$event_infos = MPWEM_Functions::get_all_info( get_the_ID() );
												$state       = isset( $event_infos['mep_state'] ) ? $event_infos['mep_state'] : '';
												$state_type       = isset( $event_infos['mep_org_address'] ) ? $event_infos['mep_org_address'] : '';
												if ( ! empty( $state )  && $state_type==0) {
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
												$event_infos = MPWEM_Functions::get_all_info( get_the_ID() );
												$city        = isset( $event_infos['mep_city'] ) ? $event_infos['mep_city'] : '';
												$state       = isset( $event_infos['mep_state'] ) ? $event_infos['mep_state'] : '';
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
                    <p class="textGray _text_center search_sort_code_counts">
						<?php esc_html_e( 'Showing', 'mage-eventpress' ); ?>
                        <strong class="qty_count"><?php echo esc_html( $params['show'] == - 1 ? $loop->post_count : $params['show'] ); ?></strong>
						<?php esc_html_e( 'of', 'mage-eventpress' ); ?>
                        <strong class="total_filter_qty"><?php echo esc_html( $loop->found_posts ); ?></strong>
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
