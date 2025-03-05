<?php
/**
 * @author Shahadat Hossain <raselsha@gmail.com>
 * @version 1.0.0
 * @copyright 2024 Mage People
 */
if(!defined('ABSPATH'))die;

if(!class_exists('MPWEM_Template_Settings')){
    class MPWEM_Template_Settings{
        public function __construct() {
            add_action('mep_admin_event_details_before_tab_name_rich_text', [$this, 'template_tab']);
            add_action('mp_event_all_in_tab_item', [$this, 'template_tab_content']);
        }
        public function template_tab(){
            ?>
            <li data-target-tabs="#mep_event_template">
                <i class="fas fa-pager"></i><?php esc_html_e('Template', 'mage-eventpress'); ?>
            </li>
            <?php
        }
        public function template_tab_content($post_id){
            $values = get_post_custom($post_id);
			$global_template = mep_get_option('mep_global_single_template', 'single_event_setting_sec', 'theme-2');
			if (array_key_exists('mep_event_template', $values)) {
				$current_template = $values['mep_event_template'][0];
			} else {
				$current_template = '';
			}
			if ($current_template) {
				$_current_template = $current_template;
			} else {
				$_current_template = $global_template;
			}
            ?>
            <div class="mp_tab_item" data-tab-item="#mep_event_template">
                
                <h3><?php esc_html_e('Template Settings', 'mage-eventpress'); ?></h3>
                <p><?php esc_html_e('Template Settings is a display template designed to showcase activity details on the event details page.', 'mage-eventpress'); ?></p>
                
                <section class="bg-light">
                    <h2><?php esc_html_e('Template Settings', 'mage-eventpress'); ?></h2>
                    <span><?php esc_html_e('Easily create and manage a timeline of activities.', 'mage-eventpress'); ?></span>
                </section>
                
                <section>
                    <label class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e('Template', 'mage-eventpress'); ?></span></h2>
                            <span><?php esc_html_e('Select a template to show your event', 'mage-eventpress'); ?></span>
                        </div>
                        <div class='sec'>
                            <span><?php event_single_template_list($_current_template); ?></span>
                        </div>
                    </label>
                </section>
            </div>
            <?php
        }
    }

    new MPWEM_Template_Settings();
}