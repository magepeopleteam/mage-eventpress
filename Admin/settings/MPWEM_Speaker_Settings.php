<?php
/**
 * @author Sahahdat Hossain <raselsha@gmail.com>
 * @license mage-people.com
 * @var 1.0.0
 */
if( ! defined('ABSPATH') ) die;

if( ! class_exists('MPWEM_Speaker_Settings')){
    class MPWEM_Speaker_Settings{
        public function __construct() {
            $speaker_status = mep_get_option('mep_enable_speaker_list', 'single_event_setting_sec', 'no');
            if( $speaker_status == 'yes'){
                add_action('mep_admin_event_details_before_tab_name_rich_text', [$this, 'speaker_tab']);
                add_action('mp_event_all_in_tab_item', [$this, 'speaker_tab_content']);
            }
            
            //ajax icon loader
            add_action('wp_ajax_mep_pick_icon',[$this,'pick_icon']);
            add_action('wp_ajax_nopriv_mep_pick_icon',[$this,'pick_icon']);

            add_action('mpwem_settings_save', array($this, 'save_settings'));
        }
        public function speaker_tab(){
            ?>
            <li data-target-tabs="#mep_event_speakers_list_meta_boxes">
            <i class="fas fa-user-tie"></i><?php esc_html_e('Speaker Information', 'mage-eventpress'); ?>
            </li>
            <?php
        }
        public function speaker_tab_content($post_id) {
            $speakers_label = get_post_meta($post_id, 'mep_speaker_title', true);
            $speaker_icon = get_post_meta($post_id, 'mep_event_speaker_icon', true);
            $speaker_lists = get_post_meta($post_id, 'mep_event_speakers_list', true);
            $speakers = $this->get_speakers();
            ?>
            <div class="mp_tab_item" data-tab-item="#mep_event_speakers_list_meta_boxes">
                
                <h3><?php esc_html_e('Speaker Settings', 'mage-eventpress'); ?></h3>
                <p><?php esc_html_e('Speaker Settings will be here.', 'mage-eventpress'); ?></p>
                
                <section class="bg-light">
                    <h2><?php esc_html_e('Speaker Settings', 'mage-eventpress'); ?></h2>
                    <span><?php esc_html_e('Speaker Settings', 'mage-eventpress'); ?></span>
                </section>
                <section>
                    <label class="label">
                        <div>
                            <h2><span><?php echo esc_html__('Speaker Section\'s Label','mage-eventpress'); ?></span></h2>
                            <span><?php echo esc_html__('This is the heading for the Speaker List that will be displayed on the frontend. The default heading is "Speakers."','mage-eventpress'); ?></span>
                        </div>
                        <input type="text" name="mep_speaker_title" id="mep_speaker_title" placeholder="<?php _e('Speaker\'s'); ?>" value="<?php echo esc_attr($speakers_label); ?>">
                    </label>
                </section>
                <section>
                    <label class="label">
                        <div>
                            <h2><span><?php echo esc_html__('Speaker Icon','mage-eventpress'); ?></span></h2>
                            <span><?php echo esc_html__('Please select the icon that will be used for the speaker icon.','mage-eventpress'); ?></span>
                        </div>
                        <div class="mep-icon-wrapper">
                            <i class="<?php echo esc_attr($speaker_icon); ?>"></i>
                            <input type="hidden" name="mep_event_speaker_icon"  value="<?php echo esc_attr($speaker_icon); ?>">
                            <button class="button mep-pick-icon" data-modal="mep-pick-icon-new"  type="button"><?php _e('Choose Icon','mage-eventpress') ?></button>
                        </div>
                    </label>
                </section>
                <!-- sidebar collapse open -->
                <div class="mep-modal-container" data-modal-target="mep-pick-icon-new">
                    <div class="mep-modal-content">
                        <span class="mep-modal-close"><i class="fas fa-times"></i></span>
                        <div class="title">
                            <h3><?php _e('Select Icon','mage-eventpress'); ?></h3>
                            <div class="mep-icon-search-box">
                                <div class="mep-icon-preview">
                                    <i class="<?php echo esc_attr($speaker_icon); ?>"></i>
                                </div>
                                <input class="search-box" type="text" name="mep_icon_search_box" placeholder="search">
                            </div>
                        </div>
                        <div class="content">
                            <div class="fa-icon-lists">
                                <?php 
                                    $this->show_all_icons();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <section>
                    <label class="label">
                        <div>
                            <h2><span><?php echo esc_html__('Speaker Icon','mage-eventpress'); ?></span></h2>
                            <span><?php echo esc_html__('Please select the icon that will be used for the speaker icon.','mage-eventpress'); ?></span>
                        </div>
                        <div class="mep-speaker-wrapper">
                            <select name="mep_event_speakers_list[]" id="" multiple>
                                <?php foreach($speakers as  $value): ?>
                                        <option value="<?php echo $value['id']; ?>" <?php echo in_array($value['id'], $speaker_lists)?'selected':''; ?>><?php echo $value['title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </label>
                </section>
            </div>
            <?php
        }
        public function get_speakers() {
            $args = array(
                'post_type'      => 'mep_event_speaker',
                'posts_per_page' => -1,       
                'post_status'    => 'publish',
            );
            $speakers = [];
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    
                    $speakers[] = [
                        'id'    => get_the_ID(),    
                        'title' => get_the_title(),
                    ];
                }
                wp_reset_postdata();
            } else {
                $speakers=[];
            }
            return $speakers;
        }
        public function get_icons(){
            $FormFieldsGenerator = new FormFieldsGenerator();
            $icons = $FormFieldsGenerator->get_font_aws_array();
            return $icons;
        }
        public function show_all_icons() {
            $icons = $this->get_icons();
            if(!empty($icons)):
                foreach ($icons as $iconindex=>$iconTitle):
                    ?>
                    <div class="icon" title="<?php echo esc_attr($iconTitle); ?>" data-icon="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></div>
                    <?php
                endforeach;
            endif;
        }
        public function pick_icon() {
            $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
            $all_icons = $this->get_icons();
            if($query!=''){
                $filtered_icons = array_filter($all_icons, function ($name, $class) use ($query) {
                    return stripos($name, $query) !== false; // Case-insensitive search
                }, ARRAY_FILTER_USE_BOTH);
                wp_send_json($filtered_icons);
                die;
            }
            else{
                wp_send_json($all_icons);
                die;
            }
            
        }
        public function save_settings($post_id) {
            if (get_post_type($post_id) == 'mep_events') {
                $speaker_title = MP_Global_Function::get_submit_info('mep_speaker_title') ? $_POST['mep_speaker_title'] : 'faka';
                $speaker_icon = MP_Global_Function::get_submit_info('mep_event_speaker_icon') ? $_POST['mep_event_speaker_icon'] : '';
                $speakers = MP_Global_Function::get_submit_info('mep_event_speakers_list') ? $_POST['mep_event_speakers_list'] : [];
                
                update_post_meta($post_id, 'mep_speaker_title', $speaker_title);
                update_post_meta($post_id, 'mep_event_speaker_icon', $speaker_icon);
                update_post_meta($post_id, 'mep_event_speakers_list', $speakers);
            }
        }
    }
    new MPWEM_Speaker_Settings();
}