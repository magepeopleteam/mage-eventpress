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
        }
        public function speaker_tab(){
            ?>
            <li data-target-tabs="#mep_event_speakers_list_meta_boxes">
            <i class="fas fa-user-tie"></i><?php esc_html_e('Speaker Information', 'mage-eventpress'); ?>
            </li>
            <?php
        }
        public function speaker_tab_content($post_id) {
            $speakers = get_post_meta($post_id, 'mep_speaker_title', true);
            $speaker_icon = get_post_meta($post_id, 'mep_event_speaker_icon', true);
            $speaker_lists = get_post_meta($post_id, 'mep_event_speakers_list', true);
            $speaker_lists = unserialize($speaker_lists);
            
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
                        <input type="text" name="mep_speaker_title" id="mep_speaker_title" placeholder="<?php _e('Speaker\'s'); ?>" value="<?php echo esc_attr($speakers); ?>">
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
                            <h3><?php _e('Add Icon','mage-eventpress'); ?></h3>
                            <div id="mep-faq-msg"></div>
                        </div>
                        <div class="content">
                            <div class="fa-icon-lists">
                                <?php 
                                    $FormFieldsGenerator = new FormFieldsGenerator();
                                    $icons = $FormFieldsGenerator->get_font_aws_array();
                                    if(!empty($icons)):
                                        foreach ($icons as $iconindex=>$iconTitle):
                                            ?>
                                            <div class="icon" title="<?php echo esc_attr($iconTitle); ?>" data-icon="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></div>
                                            <?php
                                        endforeach;
                                    endif;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <section>
                    <?php 
                    $speaker_lists = [54,53,50];
                    $speaker = [
                            [
                                'id'=>54,
                                'title'=>'Title 1',
                            ],
                            [
                                'id'=>53,
                                'title'=>'Title 2',
                            ],
                            [
                                'id'=>50,
                                'title'=>'Title 3',
                            ]
                        ]; ?>
                    <label class="label">
                        <div>
                            <h2><span><?php echo esc_html__('Speaker Icon','mage-eventpress'); ?></span></h2>
                            <span><?php echo esc_html__('Please select the icon that will be used for the speaker icon.','mage-eventpress'); ?></span>
                        </div>
                        <div class="mep-icon-wrapper">
                            <select name="mep_event_speakers_list" id="" multiple>
                                <?php foreach($speaker as  $value): ?>
                                        <option value="<?php echo $value['id']; ?>" <?php echo in_array($value['id'], $speaker_lists)?'selected':''; ?>><?php echo $value['title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </label>
                </section>
            </div>
            <?php
        }

    }
    new MPWEM_Speaker_Settings();
}