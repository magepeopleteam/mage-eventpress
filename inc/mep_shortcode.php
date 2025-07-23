<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	/**
	 * This is the Shortcode For Display The City List of The Event
	 */
	add_shortcode( 'event-city-list', 'mep_event_city_list_shortcode_func' );
	function mep_event_city_list_shortcode_func( $atts, $content = null ) {
		ob_start();
		echo mep_event_get_event_city_list();

		return ob_get_clean();
	}
	/**
	 * This is the Shortcode For Display Event Calendar
	 */
	add_shortcode( 'event-calendar', 'mep_cal_func' );
	function mep_cal_func( $atts, $content = null ) {
		ob_start();
		echo mep_event_calender();

		return ob_get_clean();
	}
	function mep_event_calender() {
		?>
        <div class="event-calendar"></div>
        <script>
            jQuery(document).ready(function () {
                const myEvents = [
					<?php
					// $loop       = mep_event_query('all',-1);
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
					$count = $loop->post_count - 1;
					while ($loop->have_posts()) {
					$loop->the_post();
					$event_meta = get_post_custom( get_the_id() );
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
	}
	/**
	 * The Magical & The Main Event Listing Shortcode is Here, You can check the details with demo here https://wordpress.org/plugins/mage-eventpress/
	 */
	add_shortcode( 'event-list', 'mep_event_list' );
	function mep_event_list( $atts, $content = null ) {
		$defaults   = array(
			"cat"              => "0",
			"org"              => "0",
			"style"            => "grid",
			"column"           => 3,
			"cat-filter"       => "no",
			"org-filter"       => "no",
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
		$nav        = $params['carousal-nav'] == 'yes' ? 1 : 0;
		$dot        = $params['carousal-dots'] == 'yes' ? 1 : 0;
		$city       = $params['city'];
		$state      = $params['state'];
		$country    = $params['country'];
		$cid        = $params['carousal-id'];
		$status     = $params['status'];
		$year       = $params['year'];
		$filter = $params['search-filter'];
		$show   = ( $filter == 'yes' || $pagination == 'yes' && $style != 'timeline' ) ? - 1 : $show;
		$main_div = $pagination == 'carousal' ? '<div class="mage_grid_box owl-theme owl-carousel"  id="mep-carousel' . $cid . '">' : '<div class="mage_grid_box">';
		$time_line_div_start = $style == 'timeline' ? '<div class="timeline"><div class="timeline__wrap"><div class="timeline__items">' : '';
		$time_line_div_end   = $style == 'timeline' ? '</div></div></div>' : '';
		$flex_column     = $column;
		$mage_div_count  = 0;
		$event_expire_on = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
		$unq_id          = 'abr' . uniqid();
		ob_start();
		/**
		 * The Main Query function mep_event_query is locet in inc/mep_query.php File
		 */
		$loop = mep_event_query( $show, $sort, $cat, $org, $city, $country, $status, '', $year );
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
				if ( $filter == 'yes' && $style != 'timeline' ) {
					do_action( 'mpwem_list_with_filter_section', $loop, $params );
				}
			?>
            <div class="all_filter_item mep_event_list_sec" id='mep_event_list_<?php echo esc_attr( $unq_id ); ?>'>
				<?php
					$total_item = $loop->post_count;
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
                jQuery('#mep-carousel<?php echo esc_attr( $cid ); ?>').owlCarousel({
                    autoplay:  <?php echo mep_get_option( 'mep_autoplay_carousal', 'carousel_setting_sec', 'true' ); ?>,
                    autoplayTimeout:<?php echo mep_get_option( 'mep_speed_carousal', 'carousel_setting_sec', '5000' ); ?>,
                    autoplayHoverPause: true,
                    loop: <?php echo mep_get_option( 'mep_loop_carousal', 'carousel_setting_sec', 'true' ); ?>,
                    margin: 20,
                    nav: <?php echo esc_attr( $nav ); ?>,
                    dots: <?php echo esc_attr( $dot ); ?>,
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
				<?php } ?>
				<?php do_action( 'mep_event_shortcode_js_script', $params ); ?>
            });
        </script><?php
		$content = ob_get_clean();

		return $content;
	}
	/**
	 * This Is a Shortcode for display Expired Events, This will be depriciated in the version 4.0, because we added this feature into the main shortcode [event-list]. Just use [event-list status="expired"]
	 */
	add_shortcode( 'expire-event-list', 'mep_expire_event_list' );
	function mep_expire_event_list( $atts, $content = null ) {
		$defaults = array(
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
			'sort'          => 'ASC'
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
		$nav        = $params['carousal-nav'] == 'yes' ? 1 : 0;
		$dot        = $params['carousal-dots'] == 'yes' ? 1 : 0;
		$city       = $params['city'];
		$country    = $params['country'];
		$cid        = $params['carousal-id'];
		$main_div   = $pagination == 'carousal' ? '<div class="mage_grid_box owl-theme owl-carousel"  id="mep-carousel' . $cid . '">' : '<div class="mage_grid_box">';
		$time_line_div_start = $style == 'timeline' ? '<div class="timeline"><div class="timeline__wrap"><div class="timeline__items">' : '';
		$time_line_div_end   = $style == 'timeline' ? '</div></div></div>' : '';
		$flex_column     = $column;
		$mage_div_count  = 0;
		$event_expire_on = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
		ob_start();
		?>
        <div class='mep_event_list'>
			<?php if ( $cat_f == 'yes' ) {
				/**
				 * This is the hook where category filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
				 */
				do_action( 'mep_event_list_cat_names', $cat );
			}
				if ( $org_f == 'yes' ) {
					/**
					 * This is the hook where Organization filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
					 */
					do_action( 'mep_event_list_org_names', $org );
				} ?>
            <div class="mep_event_list_sec">
				<?php
					/**
					 * The Main Query function mep_event_query is locet in inc/mep_query.php File
					 */
					$loop       = mep_event_query( $show, $sort, $cat, $org, $city, $country, 'expired' );
					$total_post = $loop->post_count;
					echo wp_kses_post( $main_div );
					while ( $loop->have_posts() ) {
						$loop->the_post();
						mep_update_event_upcoming_date( get_the_id() );
						if ( $style == 'grid' && (int) $column > 0 ) {
							$columnNumber = 'column_style';
							if ( $pagination == 'carousal' ) {
								$width = 100;
							} else {
								$width = 100 / (int) $column;
							}
						} else {
							$columnNumber = 'one_column';
							$width        = 100;
						}
						/**
						 * This is the hook where Event Loop List fired from inc/template-parts/event_loop_list.php File
						 */
						do_action( 'mep_event_list_shortcode', get_the_id(), $columnNumber, $style, $width );
					}
					wp_reset_postdata();
				?>
            </div>
			<?php
				if ( $pagination == 'yes' ) {
					/**
					 * The Pagination function mep_event_pagination is locet in inc/mep_query.php File
					 */
					mep_event_pagination( $loop->max_num_pages );
				} elseif ( $pagination == 'carousal' ) {
					?>
                    <script>
                        jQuery(function () {
                            jQuery("<?php echo '#mep-carousel' . esc_attr( $cid ); ?>").owlCarousel({
                                autoplay:  <?php echo mep_get_option( 'mep_autoplay_carousal', 'carousel_setting_sec', 'true' ); ?>,
                                autoplayTimeout:<?php echo mep_get_option( 'mep_speed_carousal', 'carousel_setting_sec', '5000' ); ?>,
                                autoplayHoverPause: true,
                                loop: <?php echo mep_get_option( 'mep_loop_carousal', 'carousel_setting_sec', 'true' ); ?>,
                                margin: 20,
                                nav:<?php echo esc_attr( $nav ); ?>,
                                dots:<?php echo esc_attr( $dot ); ?>,
                                navText: ["<i class='fas fa-chevron-left'></i>", "<i class='fas fa-chevron-right'></i>"],
                                responsive: {
                                    0: {
                                        items: 1
                                    },
                                    600: {
                                        items:<?php echo esc_attr( $column ); ?>
                                    },
                                    1000: {
                                        items:<?php echo esc_attr( $column ); ?>
                                    }
                                }
                            });
                        });
                    </script>
					<?php
				}
			?>
        </div>
        </div>
        <script>
            jQuery(document).ready(function () {
                var containerEl = document.querySelector('.mep_event_list_sec');
                var mixer = mixitup(containerEl);
            });
        </script>
		<?php
		$content = ob_get_clean();

		return $content;
	}
	add_shortcode( 'event-speaker-list', 'mep_event_speaker_list_shortcode_section' );
	function mep_event_speaker_list_shortcode_section( $atts, $content = null ) {
		$defaults = array(
			"event" => "0"
		);
		$params   = shortcode_atts( $defaults, $atts );
		$event    = $params['event'];
		ob_start();
		if ( $event > 0 ) {
			echo mep_shortcode_speaker_list_html( $event );
		} else {
			echo mep_shortcode_all_speaker_list_html();
		}

		return ob_get_clean();
	}
	add_shortcode( 'event-list-onepage', 'mep_event_onepage_list' );
	function mep_event_onepage_list( $atts, $content = null ) {
		$defaults            = array(
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
		$params              = shortcode_atts( $defaults, $atts );
		$cat                 = $params['cat'];
		$org                 = $params['org'];
		$style               = $params['style'];
		$cat_f               = $params['cat-filter'];
		$org_f               = $params['org-filter'];
		$show                = $params['show'];
		$pagination          = $params['pagination'];
		$sort                = $params['sort'];
		$column              = $style != 'grid' ? 1 : $params['column'];
		$nav                 = $params['carousal-nav'] == 'yes' ? 1 : 0;
		$dot                 = $params['carousal-dots'] == 'yes' ? 1 : 0;
		$city                = $params['city'];
		$country             = $params['country'];
		$cid                 = $params['carousal-id'];
		$status              = $params['status'];
		$main_div            = $pagination == 'carousal' ? '<div class="mage_grid_box owl-theme owl-carousel"  id="mep-carousel' . $cid . '">' : '<div class="mage_grid_box">';
		$time_line_div_start = $style == 'timeline' ? '<div class="timeline"><div class="timeline__wrap"><div class="timeline__items">' : '';
		$time_line_div_end   = $style == 'timeline' ? '</div></div></div>' : '';
		$flex_column         = $column;
		$mage_div_count      = 0;
		$event_expire_on     = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
		ob_start();
		do_action( 'woocommerce_before_single_product' );
		?>
        <div class='mep_event_list'>
			<?php if ( $cat_f == 'yes' ) {
				/**
				 * This is the hook where category filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
				 */
				do_action( 'mep_event_list_cat_names', $cat );
			}
				if ( $org_f == 'yes' ) {
					/**
					 * This is the hook where Organization filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
					 */
					do_action( 'mep_event_list_org_names', $org );
				} ?>
            <div class="mep_event_list_sec">
				<?php
					$now              = current_time( 'Y-m-d H:i:s' );
					$show_price       = mep_get_option( 'mep_event_price_show', 'event_list_setting_sec', 'yes' );
					$show_price_label = mep_get_option( 'event-price-label', 'general_setting_sec', 'Price Starts from:' );
					$paged            = get_query_var( "paged" ) ? get_query_var( "paged" ) : 1;
				?>
                <div class="mep_event_list_sec">
					<?php
						/**
						 * The Main Query function mep_event_query is locet in inc/mep_query.php File
						 */
						$loop = mep_event_query( $show, $sort, $cat, $org, $city, $country, $status );
						$loop->the_post();
						$event_meta     = get_post_custom( get_the_id() );
						$author_terms   = get_the_terms( get_the_id(), 'mep_org' );
						$start_datetime = $event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0];
						$time           = strtotime( $start_datetime );
						$newformat      = date_i18n( 'Y-m-d H:i:s', $time );
						$tt             = get_the_terms( get_the_id(), 'mep_cat' );
						$torg           = get_the_terms( get_the_id(), 'mep_org' );
						$org_class      = mep_get_term_as_class( get_the_id(), 'mep_org' );
						$cat_class      = mep_get_term_as_class( get_the_id(), 'mep_cat' );
						$available_seat = mep_get_total_available_seat( get_the_id(), $event_meta );
						echo '<div class="mage_grid_box">';
						while ( $loop->have_posts() ) {
							$loop->the_post();
							if ( $style == 'grid' && (int) $column > 0 ) {
								$columnNumber = 'column_style';
								$width        = 100 / (int) $column;
							} else {
								$columnNumber = 'one_column';
								$width        = 100;
							}
							/**
							 * This is the hook where Event Loop List fired from inc/template-parts/event_loop_list.php File
							 */
							do_action( 'mep_event_list_shortcode', get_the_id(), $columnNumber, $style, $width );
							$currency_pos        = get_option( 'woocommerce_currency_pos' );
							$mep_full_name       = mage_array_strip( $event_meta['mep_full_name'][0] );
							$mep_reg_email       = mage_array_strip( $event_meta['mep_reg_email'][0] );
							$mep_reg_phone       = mage_array_strip( $event_meta['mep_reg_phone'][0] );
							$mep_reg_address     = mage_array_strip( $event_meta['mep_reg_address'][0] );
							$mep_reg_designation = mage_array_strip( $event_meta['mep_reg_designation'][0] );
							$mep_reg_website     = mage_array_strip( $event_meta['mep_reg_website'][0] );
							$mep_reg_veg         = mage_array_strip( $event_meta['mep_reg_veg'][0] );
							$mep_reg_company     = mage_array_strip( $event_meta['mep_reg_company'][0] );
							$mep_reg_gender      = mage_array_strip( $event_meta['mep_reg_gender'][0] );
							$mep_reg_tshirtsize  = mage_array_strip( $event_meta['mep_reg_tshirtsize'][0] );
							echo '<div class=event-cart-section-list>';
							do_action( 'mep_add_to_cart_list' );
							echo '</div>';
							get_event_list_js( get_the_id(), $event_meta, $currency_pos );
						}
						wp_reset_postdata();
						echo '</div>';
						if ( $pagination == 'yes' ) {
							/**
							 * The Pagination function mep_event_pagination is locet in inc/mep_query.php File
							 */
							mep_event_pagination( $loop->max_num_pages );
						} ?>
                </div>
            </div>
        </div>
		<?php
		$content = ob_get_clean();

		return $content;
	}