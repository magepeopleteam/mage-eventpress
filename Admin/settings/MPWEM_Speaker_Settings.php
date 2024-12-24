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
            $speakers = $speakers??'';
            $speaker_icon = $speaker_icon??'';
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
                        <input type="text" name="mep_speaker_title" id="mep_speaker_title" placeholder="<?php _e('Speaker\'s'); ?>" value="<?php echo esc_attr($speakers); ?>">
                    </label>
                </section>
                <section>
                    <label class="label">
                        <div>
                            <h2><span><?php echo esc_html__('Speaker Icon','mage-eventpress'); ?></span></h2>
                            <span><?php echo esc_html__('Set an icon for speaker section','mage-eventpress'); ?></span>
                        </div>
                        <div class="mep-icon-wrapper">
                            <i class="<?php echo esc_attr($speaker_icon); ?>"></i>
                            <input type="hidden" name="mep_event_speaker_icon"  value="<?php echo esc_attr($speaker_icon); ?>">
                            <button class="button mep-pick-icon" type="button"><?php _e('Choose Icon','mage-eventpress') ?></button>
                        </div>
                    </label>
                </section>
                <section class="mep-icon-display" style="display: none;">
                    <?php 
                        $FormFieldsGenerator = new FormFieldsGenerator();
                        $icons = $FormFieldsGenerator->get_font_aws_array();
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?>
                                <li title="<?php echo esc_attr($iconTitle); ?>" iconData="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></li>
                                <?php
                            endforeach;
                        endif;
                    ?>
                </section>
                <script>
                    (function($){
                        $(document).on('click','.mep-pick-icon',function(e){
                            e.preventDefault();
                            $('.mep-icon-display').slideToggle();
                            $.ajax({
                                url: mep_ajax.mep_ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'mep_pick_icon',
                                },
                                success: function(response) {
                                    // $('.mep-icon-display').html('');
                                    // $('.mep-icon-display').html(response);
                                    console.log('Success:', response);
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error:', error);
                                }
                            });
                        });
                    })(jQuery);
                </script>
            </div>
            <?php
        }
        public function pick_icon(){
            
        }
    }
    new MPWEM_Speaker_Settings();
}