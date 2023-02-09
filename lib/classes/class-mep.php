<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if (!class_exists('MPWEM_Helper')) {
    class MPWEM_Helper {
        public function __construct() {
            add_action('mpwem_list_with_filter_section', array($this,'list_with_filter_section'), 10, 2);
            add_action('mpwem_pagination', array($this, 'pagination'), 10, 2);
        }
        public function list_with_filter_section($loop, $params) {
            ob_start();
            ?>
            <div class="mpStyle">
                <div class="search_sort_code_area">
                    <div class="search_sort_code">
                        <div class="sort_code_search_box defaultLayout_xs">
                            <div class="flexEqual filter_input_area">
                                <?php
                                $category_lists = MPWEM_Helper::get_all_taxonomy('mep_cat');
                                if ($params['category-filter'] == 'yes' && $category_lists && sizeof($category_lists) > 0) {
                                    ?>
                                    <label>
                                        <select class="formControl" name="filter_with_category">
                                            <option selected value=""><?php esc_html_e('Select Category', 'mage-eventpress'); ?></option>
                                            <?php foreach ($category_lists as $category) { ?>
                                                <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
                                            <?php } ?>
                                        </select>
                                    </label>
                                <?php } ?>
                                <?php
                                $organizer_lists = MPWEM_Helper::get_all_taxonomy('mep_org');
                                if ($params['organizer-filter'] == 'yes' && $organizer_lists && sizeof($organizer_lists) > 0) {
                                    ?>
                                    <label>
                                        <select class="formControl" name="filter_with_organizer">
                                            <option selected value=""><?php esc_html_e('Select Organizer', 'mage-eventpress'); ?></option>
                                            <?php foreach ($organizer_lists as $organizer) { ?>
                                                <option value="<?php echo esc_attr($organizer); ?>"><?php echo esc_html($organizer); ?></option>
                                            <?php } ?>
                                        </select>
                                    </label>
                                <?php } ?>
                                <?php
                                $city_lists = self::get_all_city();
                                if ($params['city-filter'] == 'yes' && sizeof($city_lists) > 0) {
                                    ?>
                                    <label>
                                        <select class="formControl" name="filter_with_city">
                                            <option selected value=""><?php esc_html_e('Select City', 'mage-eventpress'); ?></option>
                                            <?php foreach ($city_lists as $city_list) { ?>
                                                <option value="<?php echo esc_attr($city_list); ?>"><?php echo esc_html($city_list); ?></option>
                                            <?php } ?>
                                        </select>
                                    </label>
                                <?php } ?>
                                <?php if ($params['date-filter'] == 'yes') { ?>
                                    <label>
                                        <input type="text" class="formControl filter_datepicker search_with_start_date" placeholder="<?php esc_html_e('Start date', 'mage-eventpress'); ?>"/>
                                        <span class="fas fa-calendar-alt filter_date_icon"></span>
                                    </label>
                                    <label>
                                        <input type="text" class="formControl filter_datepicker search_with_end_date" placeholder="<?php esc_html_e('End date', 'mage-eventpress'); ?>"/>
                                        <span class="fas fa-calendar-alt filter_date_icon"></span>
                                    </label>
                                <?php } ?>
                            </div>
                            <?php if ($params['title-filter'] == 'yes') { ?>
                                <div class="filter_input_area">
                                    <label>
                                        <input type="text" name="title_filter" placeholder="<?php esc_html_e('Type Name here ....', 'mage-eventpress'); ?>" class="formControl"/>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="search_result_empty"><?php esc_html_e('No Match Result Found!', 'mage-eventpress'); ?></div>
                    </div>
                </div>
                <p class="textGray textCenter">
                    <?php esc_html_e('Showing', 'mage-eventpress'); ?>
                    <strong class="qty_count"><?php echo esc_html($params['show']); ?></strong>
                    <?php esc_html_e('of', 'mage-eventpress'); ?>
                    <strong class="total_filter_qty"><?php echo esc_html($loop->post_count); ?></strong>
                </p>
            </div>
            <?php
            echo ob_get_clean();
        }

        public static function get_all_taxonomy($name): array {
            $taxonomy = array();
            $categories = get_terms(array(
                'taxonomy' => $name
            ));
            foreach ($categories as $category) {
                $taxonomy[] = $category->name;
            }
            return array_unique($taxonomy);
        }

        public static function all_taxonomy_as_text($event_id, $taxonomy): string {
            $taxonomy_text = '';
            $all_taxonomy = get_the_terms($event_id, $taxonomy);
            if ($all_taxonomy && sizeof($all_taxonomy) > 0) {
                foreach ($all_taxonomy as $category) {
                    $taxonomy_text = $taxonomy_text . '- ' . $category->name;
                }
            }
            return $taxonomy_text;
        }

        public static function get_all_city(): array {

            //ob_start();
            global $wpdb;
            $table_name = $wpdb->prefix . "postmeta";
            $sql = "SELECT meta_value FROM $table_name WHERE meta_key ='mep_city' GROUP BY meta_value";
            $results = $wpdb->get_results($sql); //or die(mysql_error());
            $city_list = array();
            foreach ($results as $post) {
                if ($post->meta_value) {
                    $city_list[] = $post->meta_value;
                }
            }
            return $city_list;
        }

        public function pagination($params, $total_item) {
            ob_start();
            $per_page = $params['show']>1?$params['show']:$total_item;
            ?>
            <input type="hidden" name="pagination_per_page" value="<?php echo esc_attr($per_page); ?>"/>
            <input type="hidden" name="pagination_style" value="<?php echo esc_attr($params['pagination-style']); ?>"/>
            <?php if (($params['search-filter'] == 'yes' || $params['pagination'] == 'yes') && $total_item > $per_page) { ?>
                <div class="mpStyle pagination_area">
                    <div class="allCenter">

                        <?php
                        if ($params['pagination-style'] == 'load_more') {
                            ?>
                            <button type="button"
                                    class="defaultButton pagination_load_more"
                                    data-load-more="0"
                                    data-load-more-text="<?php esc_html_e('Load More', 'mage-eventpress'); ?>"
                                    data-load-less-text="<?php esc_html_e('Less More', 'mage-eventpress'); ?>"
                            >
                                <?php esc_html_e('Load More', 'mage-eventpress'); ?>
                            </button>
                            <?php
                        } else {
                            $page_mod = $total_item % $per_page;
                            $total_page = (int)($total_item / $per_page) + ($page_mod > 0 ? 1 : 0);
                            ?>
                            <div class="buttonGroup">

                                <?php if ($total_page > 2) { ?>
                                    <button class="defaultButton_xs page_prev" type="button" title="<?php esc_html_e('GoTO Previous Page', 'mage-eventpress'); ?>" disabled>
                                        <span class="fas fa-chevron-left"></span>
                                    </button>
                                <?php } ?>
                                <?php if ($total_page > 5) { ?>
                                    <div class="ellipse_left" disabled>
                                        <div><span class="fas fa-ellipsis-h"></span></div>
                                    </div>
                                <?php } ?>
                                <?php for ($i = 0; $i < $total_page; $i++) { ?>
                                    <button class="defaultButton_xs <?php echo esc_html($i) == 0 ? 'active_pagination' : ''; ?>" type="button" data-pagination="<?php echo esc_attr($i); ?>"><?php echo esc_html($i + 1); ?></button>
                                <?php } ?>
                                <?php if ($total_page > 5) { ?>
                                    <div class="ellipse_right">
                                        <div><span class="fas fa-ellipsis-h"></span></div>
                                    </div>
                                <?php } ?>

                                <?php if ($total_page > 2) { ?>
                                    <button class="defaultButton_xs page_next" type="button" title="<?php esc_html_e('GoTO Next Page', 'mage-eventpress'); ?>">
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
    new MPWEM_Helper();
}