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
            add_action('mep_admin_event_details_before_tab_name_rich_text', [$this, 'speaker_tab']);
            add_action('mp_event_all_in_tab_item', [$this, 'speaker_tab_content']);

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
            $speaker_status = mep_get_option('mep_enable_speaker_list', 'single_event_setting_sec', 'no');
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
                            <span><?php echo esc_html__('Give a title for speaker section','mage-eventpress'); ?></span>
                        </div>
                        <input type="text" name="mep_speaker_title" id="mep_speaker_title" placeholder="Speaker's" value="Speaker's">
                    </label>
                </section>
                <?php print_r($speakers); ?>
            </div>
            <?php
        }
    }
    new MPWEM_Speaker_Settings();
}