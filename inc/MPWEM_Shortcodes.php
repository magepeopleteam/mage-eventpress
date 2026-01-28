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
				add_shortcode( 'event-city-list', array( $this, 'event_city_list' ) );
				add_shortcode( 'event-speaker-list', array( $this, 'speaker_list' ) );
				add_shortcode( 'event-calendar', array( $this, 'calender' ) );
			}
			public function event_list( $atts, $content = null ) {
				$defaults         = array(
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
			$params           = shortcode_atts( $defaults, $atts );
			
			// Sanitize all customer-provided shortcode attributes to prevent XSS and injection attacks
			$tmode            = sanitize_text_field( $params['timeline-mode'] );
			$cat              = sanitize_text_field( $params['cat'] );
			$org              = sanitize_text_field( $params['org'] );
			$tag              = sanitize_text_field( $params['tag'] );
			$style            = sanitize_text_field( $params['style'] );
			$cat_f            = sanitize_text_field( $params['cat-filter'] );
			$org_f            = sanitize_text_field( $params['org-filter'] );
			$tag_f            = sanitize_text_field( $params['tag-filter'] );
			// $show          = ( ! empty( $params['show'] ) && $params['show'] != 0 ) ? absint( $params['show'] ) : - 1;
            $show             = isset( $atts['show'] ) && $atts['show'] !== '' ? intval( $atts['show'] ) : -1;
			$pagination       = sanitize_text_field( $params['pagination'] );
			$pagination_style = sanitize_text_field( $params['pagination-style'] );
			$sort             = sanitize_text_field( $params['sort'] );
			$column           = $style != 'grid' ? 1 : absint( $params['column'] );
			$nav              = sanitize_text_field( $params['carousal-nav'] ) == 'yes' ? 1 : 0;
			$dot              = sanitize_text_field( $params['carousal-dots'] ) == 'yes' ? 1 : 0;
			// Sanitize location parameters - remove dangerous characters that could break queries
			$city             = sanitize_text_field( $params['city'] );
			$city             = str_replace( array( '[', ']', '<', '>', '"', "'", '`' ), '', $city );
			$state            = sanitize_text_field( $params['state'] );
			$state            = str_replace( array( '[', ']', '<', '>', '"', "'", '`' ), '', $state );
			$country          = sanitize_text_field( $params['country'] );
			$country          = str_replace( array( '[', ']', '<', '>', '"', "'", '`' ), '', $country );
			$cid              = sanitize_text_field( $params['carousal-id'] );
			$status           = sanitize_text_field( $params['status'] );
			$year             = sanitize_text_field( $params['year'] );
			$filter           = sanitize_text_field( $params['search-filter'] );
				$show             = ( $filter == 'yes' || $pagination == 'yes' && $style != 'timeline' ) && $pagination_style != 'ajax' ? - 1 : $show;
				$unq_id           = 'abr' . uniqid();
				ob_start();
				$loop       = MPWEM_Query::event_query( $show, $sort, $cat, $org, $city, $country, $status, $state, $year, 0, $tag );
				$total_item = $loop->found_posts;
				?>
                <div class='mage list_with_filter_section mep_event_list' id='mage-container'>
					<?php
						if ( $total_item > 0 ) {
							if ( $cat_f == 'yes' && $cat < 1 ) {
								do_action( 'mpwem_taxonomy_filter', 'mep_cat', $unq_id );
							}
							if ( $org_f == 'yes' && $org < 1 ) {
								do_action( 'mpwem_taxonomy_filter', 'mep_org', $unq_id );
							}
							if ( $tag_f == 'yes' && $tag < 1 ) {
								do_action( 'mpwem_taxonomy_filter', 'mep_tag', $unq_id );
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
									<?php if ( $style == 'timeline' ){ ?>
                                    <div class="timeline">
                                        <div class="timeline__wrap">
                                            <div class="timeline__items">
												<?php } ?>
												<?php while ( $loop->have_posts() ) {
													$loop->the_post();
													$event_id = get_the_id();
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
													wp_reset_postdata(); ?>
												<?php if ( $style == 'timeline' ){ ?>
                                            </div>
                                        </div>
                                    </div>
								<?php } ?>
                                </div>
                            </div>
							<?php
							//do_action( 'add_mpwem_pagination_section', $params, $total_item );
							do_action( 'mpwem_pagination', $params, $total_item );
						} else {
							echo esc_html__( 'There are currently no events scheduled.', 'mage-eventpress' );
						} ?>
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
                            applyAllFilters();
                        });
                        // Handle category filter change
                        jQuery('select[name="filter_with_category"]').on('change', function () {
                            applyAllFilters();
                        });
                        // Handle organizer filter change
                        jQuery('select[name="filter_with_organizer"]').on('change', function () {
                            applyAllFilters();
                        });
                        // Combined filter function that applies all filters
                        function applyAllFilters() {
                            var titleFilter = jQuery('input[name="filter_with_title"]').val().toLowerCase();
                            var dateFilter = jQuery('input[name="filter_with_date"]').val();
                            var stateFilter = jQuery('select[name="filter_with_state"]').val();
                            var cityFilter = jQuery('select[name="filter_with_city"]').val();
                            var categoryFilter = jQuery('select[name="filter_with_category"]').val();
                            var organizerFilter = jQuery('select[name="filter_with_organizer"]').val();
                            var visibleCount = 0;
                            jQuery('.mep-event-list-loop').each(function () {
                                var $item = jQuery(this);
                                var show = true;
                                // Title filter
                                if (titleFilter) {
                                    var itemTitle = ($item.data('title') || '').toLowerCase();
                                    if (itemTitle.indexOf(titleFilter) === -1) {
                                        show = false;
                                    }
                                }
                                // Date filter
                                if (show && dateFilter) {
                                    var itemDate = $item.data('date');
                                    if (itemDate) {
                                        var filterDate = new Date(dateFilter);
                                        filterDate.setHours(0, 0, 0, 0);
                                        var itemDateObj = new Date(itemDate);
                                        itemDateObj.setHours(0, 0, 0, 0);
                                        if (itemDateObj.getTime() !== filterDate.getTime()) {
                                            show = false;
                                        }
                                    } else {
                                        show = false;
                                    }
                                }
                                // State filter
                                if (show && stateFilter) {
                                    var itemState = $item.data('state') || '';
                                    if (itemState !== stateFilter) {
                                        show = false;
                                    }
                                }
                                // City filter
                                if (show && cityFilter) {
                                    var itemCity = $item.data('city-name') || '';
                                    if (itemCity !== cityFilter) {
                                        show = false;
                                    }
                                }
                                // Category filter
                                if (show && categoryFilter) {
                                    var itemCategory = $item.data('category') || '';
                                    // Check if category matches (can be comma-separated)
                                    var itemCategories = itemCategory.split(',').map(function (c) {
                                        return c.trim();
                                    });
                                    if (itemCategories.indexOf(categoryFilter) === -1) {
                                        show = false;
                                    }
                                }
                                // Organizer filter
                                if (show && organizerFilter) {
                                    var itemOrganizer = $item.data('organizer') || '';
                                    // Check if organizer matches (can be comma-separated)
                                    var itemOrganizers = itemOrganizer.split(',').map(function (o) {
                                        return o.trim();
                                    });
                                    if (itemOrganizers.indexOf(organizerFilter) === -1) {
                                        show = false;
                                    }
                                }
                                if (show) {
                                    $item.show();
                                    visibleCount++;
                                } else {
                                    $item.hide();
                                }
                            });
                            // Update count display
                            jQuery('.qty_count').text(visibleCount);
                        }
                        // Update title filter to use combined function
                        jQuery('input[name="filter_with_title"]').off('keyup').on('keyup', function () {
                            applyAllFilters();
                        });
                        // Update date filter to use combined function
                        jQuery('input[name="filter_with_date"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        // Update state filter to use combined function
                        jQuery('select[name="filter_with_state"]').off('change').on('change', function () {
                            applyAllFilters();
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
                </script>
                <?php
				$content = ob_get_clean();
				return $content;
			}
			public function add_to_cart_section( $atts, $content = null ) {
				$defaults = array( "event" => "0" );
				$params   = shortcode_atts( $defaults, $atts );
				$event_id = $params['event'];
				ob_start();
				if ( $event_id > 0 ) {
					do_action( 'mpwem_registration', $event_id );
				}
				return ob_get_clean();
			}
			public function event_city_list() {
				ob_start();
				$city_lists = MPWEM_Query::get_all_post_meta_value( 'mep_city' );
				if ( sizeof( $city_lists ) > 0 ) {
					?>
                    <div class='mep-city-list'>
                        <ul>
							<?php foreach ( $city_lists as $city_name ) { ?>
                                <li><a href='<?php echo esc_url( get_site_url() ); ?>/event-by-city-name/<?php echo esc_attr( $city_name ); ?>/'><?php echo esc_html( $city_name ); ?></a></li>
							<?php } ?>
                        </ul>
                    </div>
					<?php
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
					$event_infos               = MPWEM_Functions::get_all_info( $event_id );
					$speaker_lists             = array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
					$speaker_lists             = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
					$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
					$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
					$speaker_status            = array_key_exists( 'mep_enable_speaker_list', $single_event_setting_sec ) ? $single_event_setting_sec['mep_enable_speaker_list'] : 'no';
					if ( $speaker_status == 'yes' && sizeof( $speaker_lists ) > 0 ) { ?>
                        <div class="default_theme mpwem_style">
                            <div class="event_speaker_list_area">
								<?php do_action( 'mpwem_speaker', $event_id, $event_infos ); ?>
                            </div>
                        </div>
					<?php }
				} else {
					$speaker_lists = MPWEM_Query::get_all_post_ids( 'mep_event_speaker' );
					if ( sizeof( $speaker_lists ) > 0 ) {
						?>
                        <div class="default_theme mpwem_style">
                            <div class="event_speaker_list_area">
                                <div class="speaker_list">
									<?php foreach ( $speaker_lists as $speaker_id ) {
										$thumbnail = MPWEM_Global_Function::get_image_url( $speaker_id );
										?>
                                        <a href="<?php echo esc_url( get_the_permalink( $speaker_id ) ); ?>">
                                            <div data-bg-image="<?php echo esc_html( $thumbnail ); ?>"></div>
                                            <h6><?php echo esc_html( get_the_title( $speaker_id ) ); ?></h6>
                                        </a>
									<?php } ?>
                                </div>
                            </div>
                        </div>
						<?php
					}
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
