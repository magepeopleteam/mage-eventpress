<?php
/**
 * @author Sahahdat Hossain <raselsha@gmail.com>
 * @license mage-people.com
 * @var 1.0.0
 */

if( ! defined('ABSPATH') ) die;

if( ! class_exists('MPWEM_Faq_Settings')){
    class MPWEM_Faq_Settings{
        
        public function __construct() {
            add_action('mep_admin_event_details_before_tab_name_rich_text', [$this, 'faq_tab']);
            add_action('mp_event_all_in_tab_item', [$this, 'faq_tab_content']);

            add_action('admin_enqueue_scripts',  [$this, 'custom_editor_enqueue']);
            // save faq data
            add_action('wp_ajax_mep_faq_data_save', [$this, 'save_faq_data_settings']);
            
            // update faq data
            add_action('wp_ajax_mep_faq_data_update', [$this, 'faq_data_update']);
            
            // mep_delete_faq_data
            add_action('wp_ajax_mep_faq_delete_item', [$this, 'faq_delete_item']);
            // add_action('wp_ajax_nopriv_mep_faq_delete_item', [$this, 'faq_delete_item']);

            add_action( 'save_post', [$this,'data_save'] );
        }

        public function custom_editor_enqueue() {
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
            $faq_description = get_post_meta($post_id,'mep_faq_description',true);
            $faq_description = $faq_description?$faq_description:'';
            ?>
            <div class="mp_tab_item" data-tab-item="#mep_event_faq_meta">
                
                <h3><?php esc_html_e('FAQ Settings', 'mage-eventpress'); ?></h3>
                <p><?php esc_html_e('FAQ Settings will be here.', 'mage-eventpress'); ?></p>
                
                <section class="bg-light">
                    <h2><?php esc_html_e('FAQ Settings', 'mage-eventpress'); ?></h2>
                    <span><?php esc_html_e('FAQ Settings', 'mage-eventpress'); ?></span>
                </section>

                <section>
                    <label class="mpev-label">
                        <div>
                            <h2><?php esc_html_e('FAQ Description', 'mage-eventpress'); ?></h2>
                            <span><?php esc_html_e('FAQ Description', 'mage-eventpress'); ?></span>
                        </div>
                        <textarea name="mep_faq_description" cols="80" placeholder="Explore essential details and clear up any doubts about the event."><?php echo esc_textarea($faq_description); ?></textarea>
                    </label>
                </section>

                <section class="mep-faq-section">
                    <div class="mep-faq-items mB">
                        <?php 
                            $this->show_faq_data($post_id);
                        ?>
                    </div>
                    <button class="button mep-faq-item-new" data-modal="mep-faq-item-new" type="button"><?php _e('Add FAQ','mage-eventpress'); ?></button>
                </section>
                <!-- sidebar collapse open -->
                <div class="mep-modal-container" data-modal-target="mep-faq-item-new">
                    <div class="mep-modal-content">
                        <span class="mep-modal-close"><i class="fas fa-times"></i></span>
                        <div class="title">
                            <h3><?php _e('Add F.A.Q.','mage-eventpress'); ?></h3>
                            <div id="mep-faq-msg"></div>
                        </div>
                        <div class="content">
                            <label>
                                <?php _e('Add Title','mage-eventpress'); ?>
                                <input type="hidden" name="mep_post_id" value="<?php echo $post_id; ?>"> 
                                <input type="text"   name="mep_faq_title"> 
                                <input type="hidden" name="mep_faq_item_id">
                            </label>
                            <label>
                                <?php _e('Add Content','mage-eventpress'); ?>
                            </label>
                            <?php 
                                $content = ''; 
                                $editor_id = 'mep_faq_content';
                                $settings = array(
                                    'textarea_name' => 'mep_faq_content',
                                    'media_buttons' => true,
                                    'textarea_rows' => 10,
                                );
                                wp_editor( $content, $editor_id, $settings );
                            ?>
                            <div class="mT"></div>
                            <div class="mep_faq_save_buttons">
                                <p><button id="mep_faq_save" class="button button-primary button-large"><?php _e('Save','mage-eventpress'); ?></button> <button id="mep_faq_save_close" class="button button-primary button-large">save close</button><p>
                            </div>
                            <div class="mep_faq_update_buttons" style="display: none;">
                                <p><button id="mep_faq_update" class="button button-primary button-large"><?php _e('Update and Close','mage-eventpress'); ?></button><p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function show_faq_data($post_id){
            $mep_faq = get_post_meta($post_id,'mep_event_faq',true);
            if( ! empty($mep_faq)):
                foreach ($mep_faq as $key => $value) : 
                    ?>
                        <div class="mep-faq-item" data-id="<?php echo esc_attr($key); ?>">
                            <section class="faq-header" data-collapse-target="#faq-content-<?php echo esc_attr($key); ?>">
                                <label class="mpev-label">
                                    <p><?php echo esc_html($value['mep_faq_title']); ?></p>
                                    <div class="faq-action">
                                        <span class="" ><i class="fas fa-eye"></i></span>
                                        <span class="mep-faq-item-edit" data-modal="mep-faq-item-new" ><i class="fas fa-edit"></i></span>
                                        <span class="mep-faq-item-delete"><i class="fas fa-trash"></i></span>
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

        public function faq_data_update() {

            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mep-ajax-nonce' ) ) {
                wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
                die;
            }
            if ( ! current_user_can( 'edit_post', $_POST['mep_faq_postID'] ) ) {
                wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
                die;
            }
            
            $post_id = intval($_POST['mep_faq_postID']);
            $mep_faq = get_post_meta($post_id, 'mep_event_faq', true);
            $mep_faq = is_array($mep_faq) ? $mep_faq : [];
            $new_data = [
                'mep_faq_title' => sanitize_text_field($_POST['mep_faq_title']),
                'mep_faq_content' => wp_kses_post($_POST['mep_faq_content'])
            ];
            if( ! empty($mep_faq)){
                if(isset($_POST['mep_faq_itemID'])){
                    $mep_faq[$_POST['mep_faq_itemID']]=$new_data;
                }
            }
            update_post_meta($post_id, 'mep_event_faq', $mep_faq);
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
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mep-ajax-nonce' ) ) {
                wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
                die;
            }
            if ( ! current_user_can( 'edit_post', $_POST['mep_faq_postID'] ) ) {
                wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
                die;
            }
            $post_id = intval($_POST['mep_faq_postID']);
            $mep_faq = get_post_meta($post_id, 'mep_event_faq', true);
            $mep_faq = is_array($mep_faq) ? $mep_faq : [];
            $new_data = [
                'mep_faq_title' => sanitize_text_field($_POST['mep_faq_title']),
                'mep_faq_content' => wp_kses_post($_POST['mep_faq_content'])
            ];
            array_push($mep_faq,$new_data);
            $result = update_post_meta($post_id, 'mep_event_faq', $mep_faq);
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

            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mep-ajax-nonce' ) ) {
                wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
                die;
            }
            if ( ! current_user_can( 'edit_post', $_POST['mep_faq_postID'] ) ) {
                wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
                die;
            }

            $post_id = intval($_POST['mep_faq_postID']);
            $mep_faq = get_post_meta($post_id,'mep_event_faq',true);
            $mep_faq =  is_array($mep_faq) ? $mep_faq : [];
            if( ! empty($mep_faq)){
                if(isset($_POST['itemId'])){
                    unset($mep_faq[$_POST['itemId']]);
                    $mep_faq = array_values($mep_faq);
                }
            }
            $result = update_post_meta($post_id, 'mep_event_faq', $mep_faq);
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

        public function data_save( $post_id ) {
            global $wpdb;
            if ( get_post_type( $post_id ) == 'mep_events' ) {
                $faq_description    = isset( $_POST['mep_faq_description'] ) ? sanitize_text_field($_POST['mep_faq_description']) : '';
                update_post_meta( $post_id, 'mep_faq_description', $faq_description );
            }
        }
    }
    new MPWEM_Faq_Settings();
}