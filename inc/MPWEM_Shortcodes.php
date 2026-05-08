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
				add_shortcode( 'event-list-recurring', array( $this, 'eventlistrecurring' ) );
				add_shortcode( 'event-list', array( $this, 'event_list' ) );
				add_shortcode( 'events_list', array( $this, 'event_lists' ) );
				add_shortcode( 'expire-event-list', array( $this, 'expired_event_list' ) );
				add_shortcode( 'event-add-cart-section', array( $this, 'add_to_cart_section' ) );
				add_shortcode( 'event-city-list', array( $this, 'event_city_list' ) );
				add_shortcode( 'event-speaker-list', array( $this, 'speaker_list' ) );
				add_shortcode( 'event-calendar', array( $this, 'calender' ) );
			}
            public function eventlistrecurring( $atts, $content = null ) {
	            return $this->event_list( $atts, $content );
            }
			public function expired_event_list( $atts, $content = null ) {
				$atts           = is_array( $atts ) ? $atts : array();
				$atts['status'] = 'expired';
				return $this->event_list( $atts, $content );
			}
            public function event_lists($atts, $content = null ) {
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
                $filter           = sanitize_text_field( $params['search-filter'] );
                $pagination       = sanitize_text_field( $params['pagination'] );
                $pagination_style = sanitize_text_field( $params['pagination-style'] );
                $style            = sanitize_text_field( $params['style'] );
                $column           = $style != 'grid' ? 1 : absint( $params['column'] );
                $cat              = sanitize_text_field( $params['cat'] );
                $org              = sanitize_text_field( $params['org'] );
                $tag              = sanitize_text_field( $params['tag'] );
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
                $sort             = sanitize_text_field( $params['sort'] );
                $cat_f            = sanitize_text_field( $params['cat-filter'] );
                $org_f            = sanitize_text_field( $params['org-filter'] );
                $tag_f            = sanitize_text_field( $params['tag-filter'] );
                $show             = isset( $atts['show'] ) && $atts['show'] !== '' ? intval( $atts['show'] ) : -1;
                $show             = ( $filter == 'yes' || $pagination == 'yes') && $pagination_style != 'ajax' ? - 1 : $show;
                $loop       = MPWEM_Query::event_list_query( $show,$status,$sort);
                $unq_id           = 'abr' . uniqid();
                $total_item = $loop->found_posts;
                ob_start();
                //echo $total_item;
                ?>
                <div class='mage list_with_filter_section mep_event_list' id='mage-container'>
                    <?php if ( $total_item > 0 ) {
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
                        <div class="mep_event_list_doc_area">
                            <div class="mep_event_list_doc">
                                <button type="button" class="mep_event_list_all active"><?php esc_attr_e('All','mage-eventpress'); ?></button>
                                <button type="button" class="mep_event_list_today" data-today="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"><?php esc_attr_e('Today','mage-eventpress'); ?></button>
                                <button type="button" class="mep_event_list_this_week" data-week="<?php echo esc_attr(date('Y-m-d', strtotime('+7 days', time()))); ?>"><?php esc_attr_e('This Week','mage-eventpress'); ?></button>
                                <button type="button" class="mep_event_list_this_month"><?php esc_attr_e('This Month','mage-eventpress'); ?></button>
                            </div>
                            <div class="mep_event_list_doc">
                                <button type="button" class="mep_event_list_filter_toggle"><i class="fas fa-filter"></i><?php esc_attr_e('Filter','mage-eventpress'); ?></button>
                                <button type="button" class="mep_event_list_grid active"><i class="fa-solid fa-border-all"></i><?php esc_attr_e('Grid','mage-eventpress'); ?></button>
                                <button type="button" class="mep_event_list_list"><i class="fa-solid fa-list"></i><?php esc_attr_e('List','mage-eventpress'); ?></button>
                                <button type="button" class="mep_event_list_calender"><i class="fa-regular fa-calendar-days"></i><?php esc_attr_e('Calender','mage-eventpress'); ?></button>
                            </div>
                        </div>
                        <?php
                        // Collect filter data from the loop
                        $filter_cities = array();
                        $filter_states = array();
                        $filter_dates  = array();
                        $filter_loop = MPWEM_Query::event_list_query( $show, $status, $sort );
                        while ( $filter_loop->have_posts() ) {
                            $filter_loop->the_post();
                            $f_id    = get_the_ID();
                            $f_infos = MPWEM_Functions::get_all_info( $f_id );
                            $f_city  = isset( $f_infos['mep_city'] ) ? $f_infos['mep_city'] : '';
                            $f_state = isset( $f_infos['mep_state'] ) ? $f_infos['mep_state'] : '';
                            // Collect ALL event dates (single, multi-date, recurring)
                            $event_date_arr = mep_get_event_dates_arr( $f_id );
                            if ( is_array( $event_date_arr ) && sizeof( $event_date_arr ) > 0 ) {
                                foreach ( $event_date_arr as $ed ) {
                                    $ds = isset( $ed['start'] ) && ! empty( $ed['start'] ) ? date( 'm/d/Y', strtotime( $ed['start'] ) ) : '';
                                    if ( ! empty( $ds ) ) {
                                        $filter_dates[] = $ds;
                                    }
                                }
                            }
                            if ( ! empty( $f_city ) ) {
                                $filter_cities[] = array(
                                    'city'    => $f_city,
                                    'state'   => $f_state,
                                    'display' => ! empty( $f_state ) ? $f_city . ', ' . $f_state : $f_city,
                                );
                            }
                            if ( ! empty( $f_state ) ) {
                                $filter_states[] = $f_state;
                            }
                        }
                        wp_reset_postdata();
                        $filter_cities = array_map( 'unserialize', array_unique( array_map( 'serialize', $filter_cities ) ) );
                        usort( $filter_cities, function ( $a, $b ) { return strcmp( strtolower( $a['city'] ), strtolower( $b['city'] ) ); } );
                        $filter_states = array_unique( $filter_states );
                        sort( $filter_states );
                        $filter_dates  = array_unique( $filter_dates );
                        sort( $filter_dates );
                        $filter_categories = MPWEM_Global_Function::get_all_term_data( 'mep_cat' );
                        $filter_organizers = MPWEM_Global_Function::get_all_term_data( 'mep_org' );
                        $filter_dates_json = wp_json_encode( array_values( $filter_dates ) );
                        ?>
                        <div class="mep_event_filter_panel" style="display:none;" data-event-dates='<?php echo esc_attr( $filter_dates_json ); ?>'>
                            <div class="mep_filter_grid">
                                <label>
                                    <span><?php esc_html_e( 'Search', 'mage-eventpress' ); ?></span>
                                    <input type="text" name="filter_with_title" class="formControl" placeholder="<?php esc_attr_e( 'Search events...', 'mage-eventpress' ); ?>">
                                </label>
                                <label>
                                    <span><?php esc_html_e( 'Start Date', 'mage-eventpress' ); ?></span>
                                    <input type="text" name="filter_with_start_date" class="formControl filter_datepicker" placeholder="<?php esc_attr_e( 'mm/dd/yyyy', 'mage-eventpress' ); ?>">
                                </label>
                                <label>
                                    <span><?php esc_html_e( 'End Date', 'mage-eventpress' ); ?></span>
                                    <input type="text" name="filter_with_end_date" class="formControl filter_datepicker" placeholder="<?php esc_attr_e( 'mm/dd/yyyy', 'mage-eventpress' ); ?>">
                                </label>
                                <?php if ( is_array( $filter_categories ) && sizeof( $filter_categories ) > 0 ) { ?>
                                <label>
                                    <span><?php esc_html_e( 'Category', 'mage-eventpress' ); ?></span>
                                    <select class="formControl" name="filter_with_category">
                                        <option selected value=""><?php esc_html_e( 'All Categories', 'mage-eventpress' ); ?></option>
                                        <?php foreach ( $filter_categories as $category ) { ?>
                                            <option value="<?php echo esc_attr( $category ); ?>"><?php echo esc_html( $category ); ?></option>
                                        <?php } ?>
                                    </select>
                                </label>
                                <?php } ?>
                                <?php if ( is_array( $filter_organizers ) && sizeof( $filter_organizers ) > 0 ) { ?>
                                <label>
                                    <span><?php esc_html_e( 'Organizer', 'mage-eventpress' ); ?></span>
                                    <select class="formControl" name="filter_with_organizer">
                                        <option selected value=""><?php esc_html_e( 'All Organizers', 'mage-eventpress' ); ?></option>
                                        <?php foreach ( $filter_organizers as $organizer ) { ?>
                                            <option value="<?php echo esc_attr( $organizer ); ?>"><?php echo esc_html( $organizer ); ?></option>
                                        <?php } ?>
                                    </select>
                                </label>
                                <?php } ?>
                                <?php if ( ! empty( $filter_cities ) ) { ?>
                                <label>
                                    <span><?php esc_html_e( 'City', 'mage-eventpress' ); ?></span>
                                    <select class="formControl" name="filter_with_city">
                                        <option selected value=""><?php esc_html_e( 'All Cities', 'mage-eventpress' ); ?></option>
                                        <?php foreach ( $filter_cities as $city_data ) { ?>
                                            <option value="<?php echo esc_attr( $city_data['city'] ); ?>"><?php echo esc_html( $city_data['display'] ); ?></option>
                                        <?php } ?>
                                    </select>
                                </label>
                                <?php } ?>
                                <?php if ( ! empty( $filter_states ) ) { ?>
                                <label>
                                    <span><?php esc_html_e( 'State', 'mage-eventpress' ); ?></span>
                                    <select class="formControl" name="filter_with_state">
                                        <option selected value=""><?php esc_html_e( 'All States', 'mage-eventpress' ); ?></option>
                                        <?php foreach ( $filter_states as $state ) { ?>
                                            <option value="<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $state ); ?></option>
                                        <?php } ?>
                                    </select>
                                </label>
                                <?php } ?>
                                <div class="mep_filter_actions">
                                    <button type="button" class="mep_event_filter_clear"><?php esc_html_e( 'Clear', 'mage-eventpress' ); ?></button>
                                </div>
                                <button type="button" class="mep_event_filter_close"><i class="fa-regular fa-circle-xmark"></i></button>

                            </div>
                            <p class="textGray _text_center search_sort_code_counts">
                                <?php esc_html_e( 'Showing', 'mage-eventpress' ); ?>
                                <strong class="qty_count"><?php echo esc_html( $total_item ); ?></strong>
                                <?php esc_html_e( 'of', 'mage-eventpress' ); ?>
                                <strong class="total_filter_qty"><?php echo esc_html( $total_item ); ?></strong>
                            </p>
                        </div>

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
                        <div class="mage_grid_box">
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
                                //echo $event_id;
                                do_action( 'mep_event_list_shortcode', $event_id, $columnNumber, $style, $width, $unq_id );
                            }
                                wp_reset_postdata(); ?>
                        </div>
                    </div>
                    <?php 	do_action( 'mpwem_pagination', $params, $total_item );
                    } else {
                        echo esc_html__( 'There are currently no events scheduled.', 'mage-eventpress' );
                    }?>
                    <div class="no_event_found"><?php echo esc_html__( 'There are currently no events scheduled.', 'mage-eventpress' ); ?></div>
                </div>
                <div id="loader-overlay" class="loader-overlay">
                    <div class="modern-spinner">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
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
                            var startDateFilter = jQuery('input[name="filter_with_start_date"]').val();
                            var endDateFilter = jQuery('input[name="filter_with_end_date"]').val();
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
                                // Single Date filter (legacy)
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
                                // Date Range filter

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
                            jQuery('.qty_count').text(visibleCount);
                        }
                        jQuery('input[name="filter_with_title"]').off('keyup').on('keyup', function () {
                            applyAllFilters();
                        });
                        jQuery('input[name="filter_with_date"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        // jQuery('input[name="filter_with_start_date"]').off('change').on('change', function () {
                        //     applyAllFilters();
                        // });
                        // jQuery('input[name="filter_with_end_date"]').off('change').on('change', function () {
                        //     applyAllFilters();
                        // });
                        jQuery('select[name="filter_with_state"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('select[name="filter_with_city"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('select[name="filter_with_category"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('select[name="filter_with_organizer"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                    });
                </script>
                <?php
                $content = ob_get_clean();
                return $content;
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
                            var startDateFilter = jQuery('input[name="filter_with_start_date"]').val();
                            var endDateFilter = jQuery('input[name="filter_with_end_date"]').val();
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
                                // Single Date filter (legacy)
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
                                // Date Range filter
                                if (show && (startDateFilter || endDateFilter)) {
                                    var itemDate = $item.data('date');
                                    if (itemDate) {
                                        var itemDateObj = new Date(itemDate);
                                        itemDateObj.setHours(0, 0, 0, 0);
                                        if (startDateFilter) {
                                            var startDate = new Date(startDateFilter);
                                            startDate.setHours(0, 0, 0, 0);
                                            if (itemDateObj < startDate) {
                                                show = false;
                                            }
                                        }
                                        if (endDateFilter) {
                                            var endDate = new Date(endDateFilter);
                                            endDate.setHours(0, 0, 0, 0);
                                            if (itemDateObj > endDate) {
                                                show = false;
                                            }
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
                            jQuery('.qty_count').text(visibleCount);
                        }
                        jQuery('input[name="filter_with_title"]').off('keyup').on('keyup', function () {
                            applyAllFilters();
                        });
                        jQuery('input[name="filter_with_date"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('input[name="filter_with_start_date"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('input[name="filter_with_end_date"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('select[name="filter_with_state"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('select[name="filter_with_city"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('select[name="filter_with_category"]').off('change').on('change', function () {
                            applyAllFilters();
                        });
                        jQuery('select[name="filter_with_organizer"]').off('change').on('change', function () {
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
				if ( is_array( $city_lists ) && sizeof( $city_lists ) > 0 ) {
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
					$speaker_lists             = is_array($event_infos) && array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
					$speaker_lists             = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
					$_single_event_setting_sec = is_array($event_infos) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
					$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
					$speaker_status            = is_array($single_event_setting_sec) && array_key_exists( 'mep_enable_speaker_list', $single_event_setting_sec ) ? $single_event_setting_sec['mep_enable_speaker_list'] : 'no';
					if ( $speaker_status == 'yes' && is_array( $speaker_lists ) && sizeof( $speaker_lists ) > 0 ) { ?>
                        <div class="default_theme mpwem_style">
                            <div class="event_speaker_list_area">
								<?php do_action( 'mpwem_speaker', $event_id, $event_infos ); ?>
                            </div>
                        </div>
					<?php }
				} else {
					$speaker_lists = MPWEM_Query::get_all_post_ids( 'mep_event_speaker' );
					if ( is_array( $speaker_lists ) && sizeof( $speaker_lists ) > 0 ) {
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
			public function calender( $atts = array() ) {
				return do_shortcode( '[mep-event-calendar]' );
			}
		}
		new MPWEM_Shortcodes();
	}
