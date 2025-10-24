<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Shortcodes' ) ) {
		class MPWEM_Shortcodes {
			public function __construct() {
				add_shortcode( 'event-list', array( $this, 'event_list' ) );
				add_shortcode( 'event-city-list', array( $this, 'event_city_list' ) );
				add_shortcode( 'event-list-onepage', array( $this, 'event_list_one_page' ) );
				add_shortcode( 'event-add-cart-section', array( $this, 'add_to_cart_section' ) );
				add_shortcode( 'event-speaker-list', array( $this, 'speaker_list' ) );
				add_shortcode( 'event-calendar', array( $this, 'calender' ) );
			}

			public function event_list( $atts, $content = null ) {
				$defaults            = array(
					"cat"              => "0",
					"org"              => "0",
					"tag"              => "0",
					"style"            => "grid",
					"column"           => 3,
					"cat-filter"       => "no",
					"org-filter"       => "no",
					"tag-filter"       => "no",
					"show"             => "-1",
					"pagination"       => "no",
					"pagination-style" => "load_more",
					"city"             => "",
					"state"            => "",
					"country"          => "",
					"carousal-nav"     => "no",
					"carousal-dots"    => "yes",
					"carousal-id"      => "102448",
					"timeline-mode"    => "vertical",
					'sort'             => 'ASC',
					'status'           => 'upcoming',
					'search-filter'    => '',
					'title-filter'     => 'yes',
					'category-filter'  => 'yes',
					'organizer-filter' => 'yes',
					'city-filter'      => 'yes',
					'state-filter'     => 'yes',
					'date-filter'      => 'yes',
					'year'             => '',
				);
				$params              = shortcode_atts( $defaults, $atts );
				$tmode               = $params['timeline-mode'];
				$cat                 = $params['cat'];
				$org                 = $params['org'];
				$tag                 = $params['tag'];
				$style               = $params['style'];
				$cat_f               = $params['cat-filter'];
				$org_f               = $params['org-filter'];
				$tag_f               = $params['tag-filter'];
				$show                = $params['show'];
				$pagination          = $params['pagination'];
				$pagination_style    = $params['pagination-style'];
				$sort                = $params['sort'];
				$column              = $style != 'grid' ? 1 : $params['column'];
				$nav                 = $params['carousal-nav'] == 'yes' ? 1 : 0;
				$dot                 = $params['carousal-dots'] == 'yes' ? 1 : 0;
				$city                = $params['city'];
				$country             = $params['country'];
				$cid                 = $params['carousal-id'];
				$status              = $params['status'];
				$year                = $params['year'];
				$filter              = $params['search-filter'];
				$show                = ( $filter == 'yes' || $pagination == 'yes' && $style != 'timeline' ) && $pagination_style != 'ajax' ? - 1 : $show;
				$time_line_div_start = $style == 'timeline' ? '<div class="timeline"><div class="timeline__wrap"><div class="timeline__items">' : '';
				$time_line_div_end   = $style == 'timeline' ? '</div></div></div>' : '';
				$unq_id              = 'abr' . uniqid();
				ob_start();
				$loop       = MPWEM_Query::event_query( $show, $sort, $cat, $org, $city, $country, $status, '', $year, 0, $tag );

				$total_item = $loop->found_posts;
				?>
                <div class='list_with_filter_section mep_event_list'>
					<?php
						if ( $cat_f == 'yes' ) {
							do_action( 'mep_event_list_cat_names', $cat, $unq_id );
						}
						if ( $org_f == 'yes' ) {
							do_action( 'mep_event_list_org_names', $org, $unq_id );
						}
						if ( $tag_f == 'yes' ) {
							do_action( 'mep_event_list_tag_names', $tag, $unq_id );
						}
						if ( $filter == 'yes' && $style != 'timeline' ) {
							do_action( 'mpwem_list_with_filter_section', $loop, $params );
						}
					?>
                    <div class="all_filter_item mep_event_list_sec" id='mep_event_list_<?php echo esc_attr( $unq_id ); ?>'
                         data-unq-id="<?php echo esc_attr( $unq_id ); ?>"
                         data-style="<?php echo esc_attr( $style ); ?>"
                         data-column="<?php echo esc_attr( $column ); ?>"
                         data-cat="<?php echo esc_attr( $cat ); ?>"
                         data-org="<?php echo esc_attr( $org ); ?>"
                         data-tag="<?php echo esc_attr( $tag ); ?>"
                         data-city="<?php echo esc_attr( $city ); ?>"
                         data-country="<?php echo esc_attr( $country ); ?>"
                         data-status="<?php echo esc_attr( $status ); ?>"
                         data-year="<?php echo esc_attr( $year ); ?>"
                         data-sort="<?php echo esc_attr( $sort ); ?>"
                         data-show="<?php echo esc_attr( $show ); ?>"
                         data-pagination="<?php echo esc_attr( $pagination ); ?>"
                         data-pagination-style="<?php echo esc_attr( $pagination_style ); ?>"
                    >
                        <div class="mage_grid_box <?php echo esc_attr( $pagination == 'carousal' ? 'owl-theme owl-carousel' : '' ); ?>" id="<?php echo esc_attr( $pagination == 'carousal' ? 'mep-carousel' . $cid : '' ); ?>">
							<?php
								echo wp_kses_post( $time_line_div_start );
								while ( $loop->have_posts() ) {
									$loop->the_post();
									$event_id = get_the_id();
									mep_update_event_upcoming_date( $event_id );
									if ( $style == 'grid' && (int) $column > 0 && $pagination != 'carousal' ) {
										$columnNumber = 'column_style';
										$width        = 100 / (int) $column;
									} elseif ( $pagination == 'carousal' && $style == 'grid' ) {
										$columnNumber = 'grid';
										$width        = 100;
									} else {
										$columnNumber = 'one_column';
										$width        = 100;
									}
									do_action( 'mep_event_list_shortcode', $event_id, $columnNumber, $style, $width, $unq_id );
								}
								wp_reset_postdata();
								echo wp_kses_post( $time_line_div_end );
							?>
                        </div>
                    </div>
					<?php do_action( 'mpwem_pagination', $params, $total_item ); ?>
                </div>
                <script>
                    jQuery(document).ready(function () {
                        var containerEl = document.querySelector('#mep_event_list_<?php echo esc_attr( $unq_id ); ?>');
                        var mixer = mixitup(containerEl, {
                            selectors: {
                                target: '.mep-event-list-loop',
                                control: '[data-mixitup-control]'
                            }
                        });
                        // Handle title filter input
                        jQuery('input[name="filter_with_title"]').on('keyup', function () {
                            var searchText = jQuery(this).val().toLowerCase();
                            var items = jQuery('.mep-event-list-loop');
                            items.each(function () {
                                var itemTitle = jQuery(this).data('title').toLowerCase();
                                if (itemTitle.indexOf(searchText) > -1) {
                                    jQuery(this).show();
                                } else {
                                    jQuery(this).hide();
                                }
                            });
                        });
                        // Handle date filter change
                        jQuery('input[name="filter_with_date"]').on('change', function () {
                            var selectedDate = jQuery(this).val();
                            var items = jQuery('.mep-event-list-loop');
                            if (!selectedDate) {
                                items.show();
                            } else {
                                var filterDate = new Date(selectedDate);
                                filterDate.setHours(0, 0, 0, 0); // Reset time part for date comparison
                                items.each(function () {
                                    var itemDate = new Date(jQuery(this).data('date'));
                                    itemDate.setHours(0, 0, 0, 0); // Reset time part for date comparison
                                    if (itemDate.getTime() === filterDate.getTime()) {
                                        jQuery(this).show();
                                    } else {
                                        jQuery(this).hide();
                                    }
                                });
                            }
                        });
                        // Handle state filter change
                        jQuery('select[name="filter_with_state"]').on('change', function () {
                            var state = jQuery(this).val();
                            var items = jQuery('.mep-event-list-loop');
                            if (state === '') {
                                items.show();
                            } else {
                                items.each(function () {
                                    var itemState = jQuery(this).data('state');
                                    if (itemState === state) {
                                        jQuery(this).show();
                                    } else {
                                        jQuery(this).hide();
                                    }
                                });
                            }
                        });
                        // Handle city filter change
                        jQuery('select[name="filter_with_city"]').on('change', function () {
                            var city = jQuery(this).val();
                            var items = jQuery('.mep-event-list-loop');
                            if (city === '') {
                                items.show();
                            } else {
                                items.each(function () {
                                    var itemCity = jQuery(this).data('city-name');
                                    if (itemCity === city) {
                                        jQuery(this).show();
                                    } else {
                                        jQuery(this).hide();
                                    }
                                });
                            }
                        });
						<?php if ($pagination == 'carousal') { ?>
                        // Initialize Owl Carousel
                        if (typeof jQuery().owlCarousel === 'function') {
                            jQuery('#mep-carousel<?php echo esc_attr( $cid ); ?>').owlCarousel({
                                autoplay:  <?php echo mep_get_option( 'mep_autoplay_carousal', 'carousel_setting_sec', 'true' ); ?>,
                                autoplayTimeout:<?php echo mep_get_option( 'mep_speed_carousal', 'carousel_setting_sec', '5000' ); ?>,
                                autoplayHoverPause: true,
                                loop: <?php echo mep_get_option( 'mep_loop_carousal', 'carousel_setting_sec', 'true' ); ?>,
                                margin: 20,
                                nav: <?php echo esc_attr( $nav ) == '1' ? 'true' : 'false'; ?>,
                                dots: <?php echo esc_attr( $dot ) == '1' ? 'true' : 'false'; ?>,
                                responsiveClass: true,
                                responsive: {
                                    0: {
                                        items: 1,
                                    },
                                    600: {
                                        items: 2,
                                    },
                                    1000: {
                                        items: <?php echo esc_attr( $column ); ?>,
                                    }
                                }
                            });
                        } else {
                            console.warn('Event Press: Owl Carousel library not found. Please go to Events → Global Settings → Carousel Settings and set "Load Owl Carousel From Theme" to "No" if your theme does not include Owl Carousel.');
                            // Fallback: Display items in a simple grid layout
                            jQuery('#mep-carousel<?php echo esc_attr( $cid ); ?>').addClass('mep-carousel-fallback');
                        }
						<?php }
						if ( $style == 'timeline' ) { ?>
                        jQuery('.timeline').timeline({
                            mode: '<?php echo esc_attr( $tmode ); ?>',
                            visibleItems: 4
                        });
						<?php  } ?>
                    });
                </script><?php
				$content = ob_get_clean();

				return $content;
			}

			public function event_city_list() {
				global $wpdb;
				$table_name = $wpdb->prefix . "postmeta";
				$sql        = "SELECT meta_value FROM $table_name WHERE meta_key ='mep_city' GROUP BY meta_value";
				$results    = $wpdb->get_results( $sql ); //or die(mysql_error());
				ob_start();
				?>
                <div class='mep-city-list'>
                    <ul>
						<?php foreach ( $results as $result ) { ?>
                            <li><a href='<?php echo get_site_url(); ?>/event-by-city-name/<?php echo esc_attr( $result->meta_value ); ?>/'><?php echo esc_html( $result->meta_value ); ?></a></li>
						<?php } ?>
                    </ul>
                </div>
				<?php
				return ob_get_clean();
			}

			public function event_list_one_page( $atts ) {
				$defaults   = array(
					"cat"           => "0",
					"org"           => "0",
					"style"         => "grid",
					"column"        => 3,
					"cat-filter"    => "no",
					"org-filter"    => "no",
					"show"          => "-1",
					"pagination"    => "no",
					"city"          => "",
					"country"       => "",
					"carousal-nav"  => "no",
					"carousal-dots" => "yes",
					"carousal-id"   => "102448",
					"timeline-mode" => "vertical",
					'sort'          => 'ASC',
					'status'        => 'upcoming'
				);
				$params     = shortcode_atts( $defaults, $atts );
				$cat        = $params['cat'];
				$org        = $params['org'];
				$style      = $params['style'];
				$cat_f      = $params['cat-filter'];
				$org_f      = $params['org-filter'];
				$show       = $params['show'];
				$pagination = $params['pagination'];
				$sort       = $params['sort'];
				$column     = $style != 'grid' ? 1 : $params['column'];
				$city       = $params['city'];
				$country    = $params['country'];
				$status     = $params['status'];
				ob_start();
				do_action( 'woocommerce_before_single_product' );
				?>
                <div class='mep_event_list'>
					<?php if ( $cat_f == 'yes' ) {
						do_action( 'mep_event_list_cat_names', $cat );
					}
						if ( $org_f == 'yes' ) {
							do_action( 'mep_event_list_org_names', $org );
						} ?>
                    <div class="mep_event_list_sec">
                        <div class="mep_event_list_sec">
							<?php
								$loop = MPWEM_Query::event_query( $show, $sort, $cat, $org, $city, $country, $status );
								$loop->the_post();
								echo '<div class="mage_grid_box">';
								while ( $loop->have_posts() ) {
									$loop->the_post();
									$event_id = get_the_id();
									if ( $style == 'grid' && (int) $column > 0 ) {
										$columnNumber = 'column_style';
										$width        = 100 / (int) $column;
									} else {
										$columnNumber = 'one_column';
										$width        = 100;
									}
									do_action( 'mep_event_list_shortcode', $event_id, $columnNumber, $style, $width );
									echo '<div class=event-cart-section-list>';
									$all_dates     = MPWEM_Functions::get_dates( $event_id );
									$all_times     = MPWEM_Functions::get_times( $event_id, $all_dates );
									$upcoming_date = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
									do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date );
									echo '</div>';
								}
								wp_reset_postdata();
								echo '</div>';
								if ( $pagination == 'yes' ) {
									mep_event_pagination( $loop->max_num_pages );
								} ?>
                        </div>
                    </div>
                </div>
				<?php
				$content = ob_get_clean();

				return $content;
			}

			public function add_to_cart_section( $atts, $content = null ) {
				$defaults = array(
					"event"               => "0",
					"cart-btn-label"      => __( 'Register For This Event', 'mage-eventpress' ),
					"ticket-label"        => __( 'Ticket Type', 'mage-eventpress' ),
					"extra-service-label" => __( 'Extra Service', 'mage-eventpress' )
				);
				$params   = shortcode_atts( $defaults, $atts );
				$event_id = $params['event'];
				ob_start();
				if ( $event_id > 0 ) {
					$all_dates     = MPWEM_Functions::get_dates( $event_id );
					$all_times     = MPWEM_Functions::get_times( $event_id, $all_dates );
					$upcoming_date = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
					do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date, $params );
				}

				return ob_get_clean();
			}

			public function speaker_list( $atts ) {
				$defaults = array(
					"event" => "0"
				);
				$params   = shortcode_atts( $defaults, $atts );
				$event_id = $params['event'];
				ob_start();
				if ( $event_id > 0 ) {
					?>
                    <div class="mep-default-sidebar-speaker-list">
						<?php do_action( 'mep_event_speakers_list', $event_id ); ?>
                    </div>
					<?php
				} else {
					$args = array(
						'post_type'      => array( 'mep_event_speaker' ),
						'posts_per_page' => - 1
					);
					$loop = new WP_Query( $args );
					?>
                    <div class="mep-default-sidebar-speaker-list">
                        <ul>
							<?php
								foreach ( $loop->posts as $speaker ) {
									$speakers = $speaker->ID;
									?>
                                    <li>
                                        <a href='<?php echo get_the_permalink( $speakers ); ?>'>
											<?php if ( has_post_thumbnail( $speakers ) ) {
												echo get_the_post_thumbnail( $speakers, 'medium' );
											} else { ?>
                                                <img src="<?php echo esc_url( MPWEM_PLUGIN_URL . '/assets/helper/images/no-photo.jpg' ); ?>" alt=""/>;
											<?php } ?>
                                            <h6><?php echo get_the_title( $speakers ); ?></h6>
                                        </a>
                                    </li>
									<?php
								}
							?>
                        </ul>
                    </div>
					<?php
				}

				return ob_get_clean();
			}

			public function calender() {
				ob_start();
				?>
                <div class="event-calendar"></div>
                <script>
                    jQuery(document).ready(function () {
                        const myEvents = [
							<?php
							// mep_hide_expired_date_in_calendar
							$event_expire_on_old = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
							$hide_expired = mep_get_option( 'mep_hide_expired_date_in_calendar', 'general_setting_sec', 'no' );
							$event_expire_on = $event_expire_on_old == 'event_expire_datetime' ? 'end' : 'start';
							$args = array(
								'post_type'      => array( 'mep_events' ),
								'posts_per_page' => - 1,
								'order'          => 'ASC',
								'orderby'        => 'meta_value',
								'meta_key'       => 'event_start_datetime'
							);
							$loop = new WP_Query( $args );
							$i = 1;
							while ($loop->have_posts()) {
							$loop->the_post();
							$event_dates = mep_get_event_dates_arr( get_the_id() );
							$now = current_time( 'Y-m-d H:i:s' );
							foreach ($event_dates as $_dates) {
							if($hide_expired == 'no'){
							?>
                            {
                                start: '<?php echo date_i18n( 'Y-m-d H:i', strtotime( $_dates['start'] ) ); ?>',
                                end: '<?php echo date_i18n( 'Y-m-d H:i', strtotime( $_dates['end'] ) ); ?>',
                                title: '<?php the_title(); ?>',
                                url: '<?php the_permalink(); ?>',
                                class: 'eventID-<?php echo get_the_id(); ?>',
                                color: '#000',
                                data: {}
                            },
							<?php
							}else{


							if(strtotime( $now ) < strtotime( $_dates[ $event_expire_on ] ) ){
							?>
                            {
                                start: '<?php echo date_i18n( 'Y-m-d H:i', strtotime( $_dates['start'] ) ); ?>',
                                end: '<?php echo date_i18n( 'Y-m-d H:i', strtotime( $_dates['end'] ) ); ?>',
                                title: '<?php the_title(); ?>',
                                url: '<?php the_permalink(); ?>',
                                class: 'eventID-<?php echo get_the_id(); ?>',
                                color: '#000',
                                data: {}
                            },
							<?php
							}
							}
							}
							}
							$i ++;
							wp_reset_postdata();
							?>
                        ]
                        jQuery('.event-calendar').equinox({
                            events: myEvents
                        });
                    });
                </script>
				<?php
				return ob_get_clean();
			}
		}
		new MPWEM_Shortcodes();
	}