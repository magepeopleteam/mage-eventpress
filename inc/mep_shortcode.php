<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * This is the Shortcode For Display The City List of The Event
 */
add_shortcode('event-city-list', 'mep_event_city_list_shortcode_func');
function mep_event_city_list_shortcode_func($atts, $content = null)
{
    ob_start();
     echo mep_event_get_event_city_list();
    return ob_get_clean();
}


/**
 * This is the Shortcode For Display Event Calendar
 */
add_shortcode('event-calendar', 'mep_cal_func');
function mep_cal_func($atts, $content = null)
{
    ob_start();
    echo mep_event_calender();
    return ob_get_clean();
}

function mep_event_calender()
{
?>
    <div class="event-calendar"></div>
    <script>
        jQuery(document).ready(function() {
            const myEvents = [
                <?php
                // $loop       = mep_event_query('all',-1);
                $args = array(
                    'post_type'         => array('mep_events'),
                    'posts_per_page'    => -1,
                    'order'             => 'ASC',
                    'orderby'           => 'meta_value',
                    'meta_key'          => 'event_start_datetime'
                );
                $loop = new WP_Query($args);
                $i          = 1;
                $count      = $loop->post_count - 1;
                while ($loop->have_posts()) {
                    $loop->the_post();
                    $event_meta = get_post_custom(get_the_id());
                    $author_terms = get_the_terms(get_the_id(), 'mep_org');
                    $time = strtotime($event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0]);
                    $newformat = date_i18n('Y-m-d H:i:s', $time);
                ?> {
                        start: '<?php echo date_i18n('Y-m-d H:i', strtotime($event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0])); ?>',
                        end: '<?php echo date_i18n('Y-m-d H:i', strtotime($event_meta['event_end_date'][0] . ' ' . $event_meta['event_end_time'][0])); ?>',
                        title: '<?php the_title(); ?>',
                        url: '<?php the_permalink(); ?>',
                        class: '',
                        color: '#000',
                        data: {}
                    },
                    <?php
                    $event_multidate = maybe_unserialize($event_meta['mep_event_more_date'][0]);
                    if (is_array($event_multidate) && sizeof($event_multidate) > 0) {
                        foreach ($event_multidate as $_event_multidate) {
                    ?>

                            {
                                start: '<?php echo date_i18n('Y-m-d H:i', strtotime($_event_multidate['event_more_start_date'] . ' ' . $_event_multidate['event_more_start_time'])); ?>',
                                end: '<?php echo date_i18n('Y-m-d H:i', strtotime($_event_multidate['event_more_end_date'] . ' ' . $_event_multidate['event_more_end_time'])); ?>',
                                title: '<?php the_title(); ?>',
                                url: '<?php the_permalink(); ?>',
                                class: '',
                                color: '#000',
                                data: {}
                            },


                <?php }
                    }
                    $i++;
                }
                wp_reset_postdata(); ?>
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
add_shortcode('event-list', 'mep_event_list');
function mep_event_list($atts, $content = null)
{
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
        "carousal-id" => "102448",
        "timeline-mode" => "vertical",
        'sort'          => 'ASC',
        'status'          => 'upcoming'
    );
    $params         = shortcode_atts($defaults, $atts);
    $cat            = $params['cat'];
    $org            = $params['org'];
    $style          = $params['style'];
    $cat_f          = $params['cat-filter'];
    $org_f          = $params['org-filter'];
    $show           = $params['show'];
    $pagination     = $params['pagination'];
    $sort           = $params['sort'];
    $column         = $style != 'grid' ? 1 : $params['column'];
    $nav            = $params['carousal-nav'] == 'yes' ? 1 : 0;
    $dot            = $params['carousal-dots'] == 'yes' ? 1 : 0;
    $city           = $params['city'];
    $country        = $params['country'];
    $cid            = $params['carousal-id'];
    $status            = $params['status'];
    $main_div       = $pagination == 'carousal' ? '<div class="mage_grid_box owl-theme owl-carousel"  id="mep-carousel' . $cid . '">' : '<div class="mage_grid_box">';

    $time_line_div_start    = $style == 'timeline' ? '<div class="timeline"><div class="timeline__wrap"><div class="timeline__items">' : '';
    $time_line_div_end      = $style == 'timeline' ? '</div></div></div>' : '';

    $flex_column    = $column;
    $mage_div_count = 0;
    $event_expire_on = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
    ob_start();
?>
    <div class='mep_event_list'>
        <?php if ($cat_f == 'yes') {
            /**
             * This is the hook where category filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
             */
            do_action('mep_event_list_cat_names');
        }
        if ($org_f == 'yes') {
            /**
             * This is the hook where Organization filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
             */
            do_action('mep_event_list_org_names');
        } ?>

        <div class="mep_event_list_sec">
            <?php
            /**
             * The Main Query function mep_event_query is locet in inc/mep_query.php File
             */
            $loop =  mep_event_query($show, $sort, $cat, $org, $city, $country, $status);
            $total_post = $loop->post_count;
            echo $main_div;
            echo $time_line_div_start;
            while ($loop->have_posts()) {
                $loop->the_post();
                if ($style == 'grid') {
                    if ($column == 2) {
                        $columnNumber = 'two_column';
                    } elseif ($column == 3) {
                        $columnNumber = 'three_column';
                    } elseif ($column == 4) {
                        $columnNumber = 'four_column';
                    } else {
                        $columnNumber = 'two_column';
                    }
                } else {
                    $columnNumber = 'one_column';
                }
                /**
                 * This is the hook where Event Loop List fired from inc/template-parts/event_loop_list.php File
                 */
                do_action('mep_event_list_shortcode', get_the_id(), $columnNumber, $style);
            }
            wp_reset_postdata();
            echo $time_line_div_end;
            if ($pagination == 'yes') {
                /**
                 * The Pagination function mep_event_pagination is locet in inc/mep_query.php File
                 */
                mep_event_pagination($loop->max_num_pages);
            } ?>


        </div>
    </div>
    </div>
    <script>
        jQuery(document).ready(function() {
            var containerEl = document.querySelector('.mep_event_list_sec');
            var mixer = mixitup(containerEl);
            <?php if ($pagination == 'carousal') { ?>
                jQuery('#mep-carousel<?php echo $cid; ?>').owlCarousel({
                    autoplay: true,
                    autoplayHoverPause: true,
                    loop: true,
                    margin: 20,
                    nav: <?php echo $nav; ?>,
                    dots: <?php echo $dot; ?>,
                    responsiveClass: true,
                    responsive: {
                        0: {
                            items: 1,

                        },
                        600: {
                            items: 2,

                        },
                        1000: {
                            items: <?php echo $column; ?>,
                        }
                    }
                });

            <?php } ?>

            <?php do_action('mep_event_shortcode_js_script', $params); ?>
        });
    </script>
<?php
    $content = ob_get_clean();
    return $content;
}





/**
 * This Is a Shortcode for display Expired Events, This will be depriciated in the version 4.0, because we added this feature into the main shortcode [event-list]. Just use [event-list status="expired"]
 */
add_shortcode('expire-event-list', 'mep_expire_event_list');
function mep_expire_event_list($atts, $content = null)
{
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
        "carousal-id" => "102448",
        "timeline-mode" => "vertical",
        'sort'          => 'ASC'
    );
    $params         = shortcode_atts($defaults, $atts);
    $cat            = $params['cat'];
    $org            = $params['org'];
    $style          = $params['style'];
    $cat_f          = $params['cat-filter'];
    $org_f          = $params['org-filter'];
    $show           = $params['show'];
    $pagination     = $params['pagination'];
    $sort           = $params['sort'];
    $column         = $style != 'grid' ? 1 : $params['column'];
    $nav            = $params['carousal-nav'] == 'yes' ? 1 : 0;
    $dot            = $params['carousal-dots'] == 'yes' ? 1 : 0;
    $city           = $params['city'];
    $country        = $params['country'];
    $cid            = $params['carousal-id'];
    $main_div       = $pagination == 'carousal' ? '<div class="mage_grid_box owl-theme owl-carousel"  id="mep-carousel' . $cid . '">' : '<div class="mage_grid_box">';

    $time_line_div_start    = $style == 'timeline' ? '<div class="timeline"><div class="timeline__wrap"><div class="timeline__items">' : '';
    $time_line_div_end      = $style == 'timeline' ? '</div></div></div>' : '';

    $flex_column    = $column;
    $mage_div_count = 0;
    $event_expire_on = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
    ob_start();
?>
    <div class='mep_event_list'>
        <?php if ($cat_f == 'yes') {
            /**
             * This is the hook where category filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
             */
            do_action('mep_event_list_cat_names');
        }
        if ($org_f == 'yes') {
            /**
             * This is the hook where Organization filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
             */
            do_action('mep_event_list_org_names');
        } ?>
        <div class="mep_event_list_sec">
            <?php
            /**
             * The Main Query function mep_event_query is locet in inc/mep_query.php File
             */
            $loop =  mep_event_query($show, $sort, $cat, $org, $city, $country, 'expired');
            $total_post =    $loop->post_count;
            echo '<div class="mage_grid_box">';
            while ($loop->have_posts()) {
                $loop->the_post();
                if ($style == 'grid') {
                    if ($column == 2) {
                        $columnNumber = 'two_column';
                    } elseif ($column == 3) {
                        $columnNumber = 'three_column';
                    } elseif ($column == 4) {
                        $columnNumber = 'four_column';
                    } else {
                        $columnNumber = 'two_column';
                    }
                } else {
                    $columnNumber = 'one_column';
                }
                /**
                 * This is the hook where Event Loop List fired from inc/template-parts/event_loop_list.php File
                 */
                do_action('mep_event_list_shortcode', get_the_id(), $columnNumber, $style);
            }
            wp_reset_postdata();
            echo '</div>';
            if ($pagination == 'yes') {
                /**
                 * The Pagination function mep_event_pagination is locet in inc/mep_query.php File
                 */
                mep_event_pagination($loop->max_num_pages);
            } ?>
        </div>
    </div>
    <script>
        jQuery(document).ready(function() {
            var containerEl = document.querySelector('.mep_event_list_sec');
            var mixer = mixitup(containerEl);
        });
    </script>
<?php
    $content = ob_get_clean();
    return $content;
}



add_shortcode('event-add-cart-section', 'mep_event_add_to_cart_section');
function mep_event_add_to_cart_section($atts, $content = null)
{
    $defaults = array(
        "event" => "0"
    );
    $params = shortcode_atts($defaults, $atts);
    $event = $params['event'];   
    ob_start();
    if($event > 0){
       echo mep_shortcode_add_cart_section_html($event);            
    }
    return ob_get_clean();
}



add_shortcode('event-speaker-list', 'mep_event_speaker_list_shortcode_section');
function mep_event_speaker_list_shortcode_section($atts, $content = null)
{
    $defaults = array(
        "event" => "0"
    );
    $params = shortcode_atts($defaults, $atts);
    $event = $params['event'];
    ob_start();
    if($event > 0){
        echo mep_shortcode_speaker_list_html($event);              
    }else{
        echo mep_shortcode_all_speaker_list_html();              
    }
    return ob_get_clean();
}













add_shortcode('event-list-onepage', 'mep_event_onepage_list');
function mep_event_onepage_list($atts, $content = null)
{
    $defaults = array(
        "cat" => "0",
        "org" => "0",
        "style" => "grid",
        "cat-filter" => "no",
        "org-filter" => "no",
        "show" => "-1",
        "pagination" => "no",
        'sort' => 'ASC'
    );

    $params = shortcode_atts($defaults, $atts);
    $cat = $params['cat'];
    $org = $params['org'];
    $style = $params['style'];
    $cat_f = $params['cat-filter'];
    $org_f = $params['org-filter'];
    $show = $params['show'];
    $pagination = $params['pagination'];
    $sort = $params['sort'];
    $event_expire_on             = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
    ob_start();
    do_action('woocommerce_before_single_product');
?>
    <div class='mep_event_list'>
        <?php if ($cat_f == 'yes') {
            /**
             * This is the hook where category filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
             */
            do_action('mep_event_list_cat_names');
        }
        if ($org_f == 'yes') {
            /**
             * This is the hook where Organization filter lists are fired from inc/template-parts/event_list_tax_name_list.php File
             */
            do_action('mep_event_list_org_names');
        } ?>

        <div class="mep_event_list_sec">
            <?php
            $now                = current_time('Y-m-d H:i:s');
            $show_price         = mep_get_option('mep_event_price_show', 'general_setting_sec', 'yes');
            $show_price_label   = mep_get_option('event-price-label', 'general_setting_sec', 'Price Starts from:');
            $paged              = get_query_var("paged") ? get_query_var("paged") : 1;

            /**
             * The Main Query function mep_event_query is locet in inc/mep_query.php File
             */
            if ($cat > 0) {
                $loop =  mep_event_query('cat', $show, $sort, $cat, 0, 'upcoming');
            } elseif ($org > 0) {
                $loop =  mep_event_query('org', $show, $sort, 0, $org, 'upcoming');
            } else {
                $loop =  mep_event_query('all', $show, $sort, 0, 0, 'upcoming');
            }
            ?>
            <div class="mep_event_list_sec">
                <?php
                /**
                 * The Main Query function mep_event_query is locet in inc/mep_query.php File
                 */
                if ($cat > 0) {
                    $loop =  mep_event_query('cat', $show, $sort, $cat, 0, 'upcoming');
                } elseif ($org > 0) {
                    $loop =  mep_event_query('org', $show, $sort, 0, $org, 'upcoming');
                } else {
                    $loop =  mep_event_query('all', $show, $sort, 0, 0, 'upcoming');
                }
                $loop->the_post();
                $event_meta         = get_post_custom(get_the_id());
                $author_terms       = get_the_terms(get_the_id(), 'mep_org');
                $start_datetime     = $event_meta['event_start_date'][0] . ' ' . $event_meta['event_start_time'][0];
                $time = strtotime($start_datetime);
                $newformat = date_i18n('Y-m-d H:i:s', $time);
                $tt = get_the_terms(get_the_id(), 'mep_cat');
                $torg = get_the_terms(get_the_id(), 'mep_org');
                $org_class = mep_get_term_as_class(get_the_id(), 'mep_org');
                $cat_class = mep_get_term_as_class(get_the_id(), 'mep_cat');
                $available_seat = mep_get_total_available_seat(get_the_id(), $event_meta);
                echo '<div class="mage_grid_box">';
                while ($loop->have_posts()) {
                    $loop->the_post();
                    if ($style == 'grid') {
                        if ($column == 2) {
                            $columnNumber = 'two_column';
                        } elseif ($column == 3) {
                            $columnNumber = 'three_column';
                        } elseif ($column == 4) {
                            $columnNumber = 'four_column';
                        } else {
                            $columnNumber = 'two_column';
                        }
                    } else {
                        $columnNumber = 'one_column';
                    }
                    /**
                     * This is the hook where Event Loop List fired from inc/template-parts/event_loop_list.php File
                     */
                    do_action('mep_event_list_shortcode', get_the_id(), $columnNumber, $style);

                    $currency_pos = get_option('woocommerce_currency_pos');
                    $mep_full_name = strip_tags($event_meta['mep_full_name'][0]);
                    $mep_reg_email = strip_tags($event_meta['mep_reg_email'][0]);
                    $mep_reg_phone = strip_tags($event_meta['mep_reg_phone'][0]);
                    $mep_reg_address = strip_tags($event_meta['mep_reg_address'][0]);
                    $mep_reg_designation = strip_tags($event_meta['mep_reg_designation'][0]);
                    $mep_reg_website = strip_tags($event_meta['mep_reg_website'][0]);
                    $mep_reg_veg = strip_tags($event_meta['mep_reg_veg'][0]);
                    $mep_reg_company = strip_tags($event_meta['mep_reg_company'][0]);
                    $mep_reg_gender = strip_tags($event_meta['mep_reg_gender'][0]);
                    $mep_reg_tshirtsize = strip_tags($event_meta['mep_reg_tshirtsize'][0]);
                    echo '<div class=event-cart-section-list>';
                    do_action('mep_add_to_cart_list');
                    echo '</div>';
                    get_event_list_js(get_the_id(), $event_meta, $currency_pos);
                }
                wp_reset_postdata();
                echo '</div>';
                if ($pagination == 'yes') {
                    /**
                     * The Pagination function mep_event_pagination is locet in inc/mep_query.php File
                     */
                    mep_event_pagination($loop->max_num_pages);
                } ?>
            </div>
        </div>
    </div>
<?php
    $content = ob_get_clean();
    return $content;
}