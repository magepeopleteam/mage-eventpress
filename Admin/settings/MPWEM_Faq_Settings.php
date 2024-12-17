<?php
/**
 * @author Sahahdat Hossain <raselsha@gmail.com>
 * @license mage-people.com
 * @var 1.0.0
 */

if( ! defined('ABSPATH') ) die;

if( ! class_exists('MPWPB_Faq_Settings')){
    class MPWPB_Faq_Settings{
        
        public function __construct() {
            add_action('mep_admin_event_details_before_tab_name_rich_text', [$this, 'faq_tab']);
            add_action('mp_event_all_in_tab_item', [$this, 'faq_tab_content']);

            add_action('mpwpb_settings_save', [$this, 'save_faq_settings']);

            add_action('admin_enqueue_scripts',  [$this, 'my_custom_editor_enqueue']);
            // save faq data
            add_action('wp_ajax_mpwpb_faq_data_save', [$this, 'save_faq_data_settings']);
            add_action('wp_ajax_nopriv_mpwpb_faq_data_save', [$this, 'save_faq_data_settings']);
            
            // update faq data
            add_action('wp_ajax_mpwpb_faq_data_update', [$this, 'faq_data_update']);
            add_action('wp_ajax_nopriv_mpwpb_faq_data_update', [$this, 'faq_data_update']);
            
            // mpwpb_delete_faq_data
            add_action('wp_ajax_mpwpb_faq_delete_item', [$this, 'faq_delete_item']);
            add_action('wp_ajax_nopriv_mpwpb_faq_delete_item', [$this, 'faq_delete_item']);
        }

        public function my_custom_editor_enqueue() {
            // Enqueue necessary scripts
            wp_enqueue_script('jquery');
            wp_enqueue_script('editor');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }
        
        public function faq_tab(){
            ?>
            <li data-target-tabs="#mep_event_faq_meta">
                <i class="far fa-question-circle"></i><?php esc_html_e('F.A.Q', 'mage-eventpress'); ?>
            </li>
            <?php
        }
        
        public function faq_tab_content($post_id) {
            $mpwpb_faq_active = MP_Global_Function::get_post_info($post_id, 'mpwpb_faq_active', 'off');
            $active_class = $mpwpb_faq_active == 'on' ? 'mActive' : '';
            $mpwpb_faq_active_checked = $mpwpb_faq_active == 'on' ? 'checked' : '';
            ?>
            <div class="mp_tab_item" data-tab-item="#mep_event_faq_meta">
                
                <h3><?php esc_html_e('FAQ Settings', 'service-booking-manager'); ?></h3>
                <p><?php esc_html_e('FAQ Settings will be here.', 'service-booking-manager'); ?></p>
                
                <section class="bg-light">
                    <h2><?php esc_html_e('FAQ Settings', 'service-booking-manager'); ?></h2>
                    <span><?php esc_html_e('FAQ Settings', 'service-booking-manager'); ?></span>
                </section>

                <section>
                    <label class="label">
                        <div>
                            <p><?php esc_html_e('Enable FAQ Section', 'service-booking-manage'); ?></p>
                            <span><?php esc_html_e('Enable FAQ Section', 'service-booking-manage'); ?></span>
                        </div>
                        <div>
                            <?php MP_Custom_Layout::switch_button('mpwpb_faq_active', $mpwpb_faq_active_checked); ?>
                        </div>
                    </label>
                </section>
                <section class="mpwpb-faq-section <?php echo $active_class; ?>" data-collapse="#mpwpb_faq_active">
                    <div class="mpwpb-faq-items mB">
                        <?php 
                            $this->show_faq_data($post_id);
                        ?>
                    </div>
                    <button class="button mpwpb-faq-item-new" data-modal="mpwpb-faq-item-new" type="button"><?php _e('Add FAQ','service-booking-manager'); ?></button>
                </section>
                <!-- sidebar collapse open -->
                <div class="mpwpb-modal-container" data-modal-target="mpwpb-faq-item-new">
                    <div class="mpwpb-modal-content">
                        <span class="mpwpb-modal-close"><i class="fas fa-times"></i></span>
                        <div class="title">
                            <h3><?php _e('Add F.A.Q.','service-booking-manager'); ?></h3>
                            <div id="mpwpb-service-msg"></div>
                        </div>
                        <div class="content">
                            <label>
                                <?php _e('Add Title','service-booking-manager'); ?>
                                <input type="hidden" name="mpwpb_post_id" value="<?php echo $post_id; ?>"> 
                                <input type="text"   name="mpwpb_faq_title"> 
                                <input type="hidden" name="mpwpb_faq_item_id">
                            </label>
                            <label>
                                <?php _e('Add Content','service-booking-manager'); ?>
                            </label>
                            <?php 
                                $content = ''; 
                                $editor_id = 'mpwpb_faq_content';
                                $settings = array(
                                    'textarea_name' => 'mpwpb_faq_content',
                                    'media_buttons' => true,
                                    'textarea_rows' => 10,
                                );
                                wp_editor( $content, $editor_id, $settings );
                            ?>
                            <div class="mT"></div>
                            <div class="mpwpb_faq_save_buttons">
                                <p><button id="mpwpb_faq_save" class="button button-primary button-large"><?php _e('Save','service-booking-manager'); ?></button> <button id="mpwpb_faq_save_close" class="button button-primary button-large">save close</button><p>
                            </div>
                            <div class="mpwpb_faq_update_buttons" style="display: none;">
                                <p><button id="mpwpb_faq_update" class="button button-primary button-large"><?php _e('Update and Close','service-booking-manager'); ?></button><p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function show_faq_data($post_id){
            $mpwpb_faq = get_post_meta($post_id,'mep_event_faq',true);
            $mpwpb_faq = unserialize($mpwpb_faq);
            if( ! empty($mpwpb_faq)):
                foreach ($mpwpb_faq as $key => $value) : 
                    ?>
                        <div class="mpwpb-faq-item" data-id="<?php echo esc_attr($key); ?>">
                            <section class="faq-header" data-collapse-target="#faq-content-<?php echo esc_attr($key); ?>">
                                <label class="label">
                                    <p><?php echo esc_html($value['mep_faq_title']); ?></p>
                                    <div class="faq-action">
                                        <span class="" ><i class="fas fa-eye"></i></span>
                                        <span class="mpwpb-faq-item-edit" data-modal="mpwpb-faq-item-new" ><i class="fas fa-edit"></i></span>
                                        <span class="mpwpb-faq-item-delete"><i class="fas fa-trash"></i></span>
                                    </div>
                                </label>
                            </section>
                            <section class="faq-content mB" data-collapse="#faq-content-<?php echo esc_attr($key); ?>">
                                <?php echo wpautop(wp_kses_post($value['mep_faq_content'])); ?>
                            </section>
                        </div>
                    <?php
                endforeach;
            endif;
        }

        public function save_faq_settings($post_id) {
            if (get_post_type($post_id) == MPWPB_Function::get_cpt()) {
                $mpwpb_faq_active = MP_Global_Function::get_submit_info('mep_event_faq');
                update_post_meta($post_id, 'mep_event_faq', $mpwpb_faq_active);
            }
        }

        public function faq_data_update() {
            $post_id = $_POST['mpwpb_faq_postID'];
            $mpwpb_faq = get_post_meta($post_id,'mep_event_faq',true);
            $mpwpb_faq =!empty($mpwpb_faq)?$mpwpb_faq:[];
            $new_data = [ 'title'=> sanitize_text_field($_POST['mep_faq_title']), 'content'=> wp_kses_post($_POST['mep_faq_content'])];
            if( ! empty($mpwpb_faq)){
                if(isset($_POST['mpwpb_faq_itemID'])){
                    $mpwpb_faq[$_POST['mpwpb_faq_itemID']]=$new_data;
                }
            }
            update_post_meta($post_id, 'mep_event_faq', $mpwpb_faq);
            ob_start();
            $resultMessage = __('Data Updated Successfully', 'mptbm_plugin_pro');
            $this->show_faq_data($post_id);
            $html_output = ob_get_clean();
            wp_send_json_success([
                'message' => $resultMessage,
                'html' => $html_output,
            ]);
            die;
        }

        public function save_faq_data_settings() {
            update_post_meta($_POST['mpwpb_faq_postID'], 'mpwpb_faq_active', 'on');
            $post_id = $_POST['mpwpb_faq_postID'];
            $mpwpb_faq = get_post_meta($post_id,'mep_event_faq',true);
            $mpwpb_faq =!empty($mpwpb_faq)?$mpwpb_faq:[];
            $new_data = [ 'title'=> sanitize_text_field($_POST['mep_faq_title']), 'content'=> wp_kses_post($_POST['mep_faq_content'])];
            if( isset($post_id)){
                array_push($mpwpb_faq,$new_data);
            }
            $result = update_post_meta($post_id, 'mep_event_faq', $mpwpb_faq);
            if($result){
                ob_start();
                $resultMessage = __('Data Added Successfully', 'mptbm_plugin_pro');
                $this->show_faq_data($post_id);
                $html_output = ob_get_clean();
                wp_send_json_success([
                    'message' => $resultMessage,
                    'html' => $html_output,
                ]);
            }
            else{
                wp_send_json_success([
                    'message' => 'Data not inserted',
                    'html' => 'error',
                ]);
            }
            die;
        }

        public function faq_delete_item(){
            $post_id = $_POST['mpwpb_faq_postID'];
            $mpwpb_faq = get_post_meta($post_id,'mpwpb_faq',true);
            $mpwpb_faq =!empty($mpwpb_faq)?$mpwpb_faq:[];
            if( ! empty($mpwpb_faq)){
                if(isset($_POST['itemId'])){
                    unset($mpwpb_faq[$_POST['itemId']]);
                    $mpwpb_faq = array_values($mpwpb_faq);
                }
            }
            $result = update_post_meta($post_id, 'mpwpb_faq', $mpwpb_faq);
            if($result){
                ob_start();
                $resultMessage = __('Data Deleted Successfully', 'mptbm_plugin_pro');
                $this->show_faq_data($post_id);
                $html_output = ob_get_clean();
                wp_send_json_success([
                    'message' => $resultMessage,
                    'html' => $html_output,
                ]);
            }
            else{
                wp_send_json_success([
                    'message' => 'Data not inserted',
                    'html' => '',
                ]);
            }
            die;
        }
    }
    new MPWPB_Faq_Settings();
}