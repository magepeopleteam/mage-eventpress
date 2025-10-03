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
				add_shortcode( 'event-add-cart-section', array( $this, 'add_to_cart_section' ) );
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
				$cat                 = $params['cat'];
				$org                 = $params['org'];
				$tag                 = $params['tag'];
				$style               = $params['style'];
				$cat_f               = $params['cat-filter'];
				$org_f               = $params['org-filter'];
				$tag_f               = $params['tag-filter'];
				$show                = $params['show'];
				$pagination          = $params['pagination'];
				$pagination_style          = $params['pagination-style'];
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
				$show                = ( $filter == 'yes' || $pagination == 'yes' && $style != 'timeline' ) &&  $pagination_style !='ajax'? - 1 : $show;
				$main_div            = $pagination == 'carousal' ? '<div class="mage_grid_box owl-theme owl-carousel"  id="mep-carousel' . $cid . '">' : '<div class="mage_grid_box">';
				$time_line_div_start = $style == 'timeline' ? '<div class="timeline"><div class="timeline__wrap"><div class="timeline__items">' : '';
				$time_line_div_end   = $style == 'timeline' ? '</div></div></div>' : '';
				$unq_id              = 'abr' . uniqid();
				ob_start();
				/**
				 * The Main Query function mep_event_query is locet in inc/mep_query.php File
				 */
				$loop = mep_event_query( $show, $sort, $cat, $org, $city, $country, $status, '', $year, 0, $tag );
				$total_item = $loop->found_posts;
				?>
                <div class='list_with_filter_section mep_event_list'>
					<?php if ( $cat_f == 'yes' ) {
						/**
						 * This is the hook where category filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
						 */
						do_action( 'mep_event_list_cat_names', $cat, $unq_id );
					}
						if ( $org_f == 'yes' ) {
							/**
							 * This is the hook where Organization filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
							 */
							do_action( 'mep_event_list_org_names', $org, $unq_id );
						}
						if ( $tag_f == 'yes' ) {
							/**
							 * This is the hook where Tag filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
							 */
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
						<?php

							echo wp_kses_post( $main_div );
							echo wp_kses_post( $time_line_div_start );
							while ( $loop->have_posts() ) {
								$loop->the_post();
								mep_update_event_upcoming_date( get_the_id() );
								mep_update_event_upcoming_date( get_the_id() );
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
								/**
								 * This is the hook where Event Loop List fired from inc/template-parts/event_loop_list.php File
								 */
								do_action( 'mep_event_list_shortcode', get_the_id(), $columnNumber, $style, $width, $unq_id );
							}
							wp_reset_postdata();
							echo wp_kses_post( $time_line_div_end );
						?>
                    </div>
                </div>
				<?php
				do_action( 'mpwem_pagination', $params, $total_item );
				?>
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
						<?php } ?>
						<?php do_action( 'mep_event_shortcode_js_script', $params ); ?>
                    });
                </script><?php
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
		}
		new MPWEM_Shortcodes();
	}