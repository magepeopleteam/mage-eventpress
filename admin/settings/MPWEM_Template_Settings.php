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
			$global_template = mep_get_option('mep_global_single_template', 'single_event_setting_sec', 'default-theme');
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
                    <span><?php esc_html_e('Select Template from below. Read documention to override template, click', 'mage-eventpress'); ?> <a href="https://docs.mage-people.com/woocommerce-event-manager/how-to-override-and-change-event-templates/"><?php _e('Here','mage-eventpress') ?></a></span>
                </section>
                
                <section>
                    <div class="mep-template-section">
                        <input type="hidden" name="mep_event_template" value="<?php echo esc_attr($_current_template); ?>" />
                        <?php $templates = $this->get_template($_current_template); ?>
                        <?php foreach($templates as $template):  ?>
                            <?php 
                                $image = preg_replace('/\.php$/', '.webp', $template['value']);
                            ?>
                            <div class="mep-template <?php echo $_current_template == $template['value']?'active':''; ?>">
                                <img src="<?php echo mep_template_file_url( 'screenshot/').$image; ?>" data-mep-template="<?php echo $template['value']; ?>">
                                <h5><?php echo $template['name']; ?></h5>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
            <?php
        }

        public function get_template($current_theme){
                $themes = mep_event_template_name();
				$deprecated_themes = ['royal.php', 'theme-1.php', 'theme-2.php', 'theme-3.php', 'vanilla.php'];
				
				foreach ( $themes as $theme_file => $theme_name ) {
					if ( in_array( $theme_file, $deprecated_themes ) && $current_theme !== $theme_file ) {
						continue;
					}
                    $template[] = [
                        'name' => $theme_name,
                        'value' => $theme_file,
                    ];
				}
                return $template;
        }
        
    }

    new MPWEM_Template_Settings();
}