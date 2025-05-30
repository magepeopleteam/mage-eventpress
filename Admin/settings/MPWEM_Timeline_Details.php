<?php
/**
 * @author Sahahdat Hossain <raselsha@gmail.com>
 * @license mage-people.com
 * @var 1.0.0
 */

if( ! defined('ABSPATH') ) die;

if( ! class_exists('MPWEM_Timeline_Details')){
    class MPWEM_Timeline_Details{
        
        public function __construct() {
            add_action('mep_admin_event_details_before_tab_name_rich_text', [$this, 'timeline_tab']);
            add_action('mp_event_all_in_tab_item', [$this, 'timeline_tab_content']);
            add_action('mpwem_timeline', [$this, 'frontend_timeline_data']);

            add_action('admin_enqueue_scripts',  [$this, 'custom_editor_enqueue']);
            // save timeline data
            add_action('wp_ajax_mep_timeline_data_save', [$this, 'save_timeline_data_settings']);
            // add_action('wp_ajax_nopriv_mep_timeline_data_save', [$this, 'save_timeline_data_settings']);
            
            // update timeline data
            add_action('wp_ajax_mep_timeline_data_update', [$this, 'timeline_data_update']);
            // add_action('wp_ajax_nopriv_mep_timeline_data_update', [$this, 'timeline_data_update']);
            
            // mep_delete_timeline_data
            add_action('wp_ajax_mep_timeline_delete_item', [$this, 'timeline_delete_item']);
            // add_action('wp_ajax_nopriv_mep_timeline_delete_item', [$this, 'timeline_delete_item']);

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
        
        public function timeline_tab(){
            ?>
            <li data-target-tabs="#mep_event_timeline_meta">
                <i class="far fa-newspaper"></i><?php esc_html_e('Timeline Details', 'mage-eventpress'); ?>
            </li>
            <?php
        }
        
        public function timeline_tab_content($post_id) {
            ?>
            <div class="mp_tab_item" data-tab-item="#mep_event_timeline_meta">
                
                <h3><?php esc_html_e('Timeline Settings', 'mage-eventpress'); ?></h3>
                <p><?php esc_html_e('Timeline Settings is an activity display system, designed to showcase event activities in a structured timeline format in event details page.', 'mage-eventpress'); ?></p>
                
                <section class="bg-light">
                    <h2><?php esc_html_e('Timeline Settings', 'mage-eventpress'); ?></h2>
                    <span><?php esc_html_e('Easily create and manage a timeline of activities.', 'mage-eventpress'); ?></span>
                </section>

                <section class="mep-timeline-section">
                    <div class="mep-timeline-items mB">
                        <?php 
                            $this->show_timeline_data($post_id);
                        ?>
                    </div>
                    <button class="button mep-timeline-item-new" data-modal="mep-timeline-item-new" type="button"><?php _e('Add New','mage-eventpress'); ?></button>
                </section>
                <!-- sidebar collapse open -->
                <div class="mep-modal-container" data-modal-target="mep-timeline-item-new">
                    <div class="mep-modal-content">
                        <span class="mep-modal-close"><i class="fas fa-times"></i></span>
                        <div class="title">
                            <h3><?php _e('Add Timeline Info','mage-eventpress'); ?></h3>
                            <div id="mep-timeline-msg"></div>
                        </div>
                        <div class="content">
                            <label>
                                <?php _e('Title','mage-eventpress'); ?>
                                <input type="hidden" name="mep_post_id" value="<?php echo $post_id; ?>" > 
                                <input type="text"   name="mep_timeline_title" placeholder="<?php esc_html_e('Pre-Event Setup', 'mage-eventpress'); ?>"> 
                                <input type="hidden" name="mep_timeline_item_id">
                            </label>
                            <label>
                                <?php _e('Time','mage-eventpress'); ?>
                                <input type="text"   name="mep_timeline_time" placeholder="<?php esc_html_e('8:00 AM - 9:00 AM', 'mage-eventpress'); ?>"> 
                            </label>
                            <label>
                                <?php _e('Add Content','mage-eventpress'); ?>
                            </label>
                            <?php 
                                $content = ''; 
                                $editor_id = 'mep_timeline_content';
                                $settings = array(
                                    'textarea_name' => 'mep_timeline_content',
                                    'media_buttons' => true,
                                    'textarea_rows' => 10,
                                );
                                wp_editor( $content, $editor_id, $settings );
                            ?>
                            <div style="margin-top: 10px;"></div>
                            <div class="mep_timeline_save_buttons">
                                <p><button id="mep_timeline_save" class="button button-primary button-large"><?php _e('Save','mage-eventpress'); ?></button> <button id="mep_timeline_save_close" class="button button-primary button-large">save close</button><p>
                            </div>
                            <div class="mep_timeline_update_buttons" style="display: none;">
                                <p><button id="mep_timeline_update" class="button button-primary button-large"><?php _e('Update and Close','mage-eventpress'); ?></button><p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function show_timeline_data($post_id){
            // $mep_timeline = get_post_meta($post_id,'mep_event_day',true);
            $mep_timeline = get_post_meta($post_id, 'mep_event_day', true) ? maybe_unserialize(get_post_meta($post_id, 'mep_event_day', true)) : '';
            if( ! empty($mep_timeline)):
                foreach ($mep_timeline as $key => $value) : 
                    ?>
                        <div class="mep-timeline-item mpStyle" data-id="<?php echo esc_attr($key); ?>">
                            <section class="timeline-header" data-collapse-target="#timeline-content-<?php echo esc_attr($key); ?>">
                                <label class="mpev-label">
                                    <p><b><?php _e('Title:','mage-eventpress'); ?></b> <span class="title"><?php echo esc_html($value['mep_day_title']); ?></span> <b><?php _e('Time:','mage-eventpress'); ?></b> <span class="time"><?php echo esc_html(isset($value['mep_day_time'])?$value['mep_day_time']:''); ?></span></p>
                                    <div class="timeline-action">
                                        <span class="" ><i class="fas fa-eye"></i></span>
                                        <span class="mep-timeline-item-edit" data-modal="mep-timeline-item-new" ><i class="fas fa-edit"></i></span>
                                        <span class="mep-timeline-item-delete"><i class="fas fa-trash"></i></span>
                                    </div>
                                </label>
                            </section>
                            <section class="timeline-content" data-collapse="#timeline-content-<?php echo esc_attr($key); ?>">
                                <?php echo htmlspecialchars_decode(wpautop(wp_kses_post($value['mep_day_content']))); ?>
                            </section>
                            <input type="hidden" name='mep_timeline_title_raw[]' value='<?php echo esc_html($value['mep_day_title']); ?>'/>
                            <textarea style='display:none;' name="mep_timeline_details_raw[]" id=""><?php echo htmlspecialchars_decode(wpautop(wp_kses_post($value['mep_day_content']))); ?></textarea>
                            <input type="hidden" name='mep_timeline_time_raw[]' value='<?php echo esc_html(isset($value['mep_day_time'])?$value['mep_day_time']:''); ?>'/>
                        </div>
                    <?php
                endforeach;
            endif;
        }

        public function frontend_timeline_data(){
            $post_id = get_the_ID();
            $mep_timeline = get_post_meta($post_id,'mep_event_day',true);
            if( ! empty($mep_timeline)):
                ?>
                <div class="mep-timeline">
                    <div class="section-title"><?php esc_html_e('Event Timelines','mage-eventpress'); ?></div>  
                    <div class="timeline">
                        <?php
                        $counter = 1;
                        foreach ($mep_timeline as $value) : 
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-point"><?php echo esc_html($counter); ?></div>
                                <div class="timeline-content">
                                    <div class="timeline-title"><?php echo esc_html($value['mep_day_title']); ?><?php if(!empty($value['mep_day_time']) ): ?><span class="timeline-time"><?php echo esc_html($value['mep_day_time']); ?></span><?php endif; ?></div>
                                    <div class="timeline-details">
                                        <?php echo wp_kses_post($value['mep_day_content']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $counter++;
                        endforeach;
                        ?>
                    </div>
                </div>
                <?php
            endif;
        }

        public function timeline_data_update() {

            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mep-ajax-nonce' ) ) {
                wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
                die;
            }
            if ( ! current_user_can( 'edit_post', $_POST['mep_timeline_postID'] ) ) {
                wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
                die;
            }

            $post_id = intval($_POST['mep_timeline_postID']);
            $mep_timeline = get_post_meta($post_id, 'mep_event_day', true);
            $mep_timeline = is_array($mep_timeline) ? $mep_timeline : [];
            $new_data = [
                'mep_day_title' => sanitize_text_field($_POST['mep_timeline_title']),
                'mep_day_time' => sanitize_text_field($_POST['mep_timeline_time']),
                'mep_day_content' => wp_kses_post($_POST['mep_timeline_content'])
            ];
            if( ! empty($mep_timeline)){
                if(isset($_POST['mep_timeline_itemID'])){
                    $mep_timeline[$_POST['mep_timeline_itemID']]=$new_data;
                }
            }
            update_post_meta($post_id, 'mep_event_day', $mep_timeline);
            ob_start();
            $resultMessage = __('Data Updated Successfully', 'mptbm_plugin_pro');
            $this->show_timeline_data($post_id);
            $html_output = ob_get_clean();
            wp_send_json_success([
                'message' => $resultMessage,
                'html' => $html_output,
            ]);
            die;
        }

        public function save_timeline_data_settings() {

            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mep-ajax-nonce' ) ) {
                wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
                die;
            }
            if ( ! current_user_can( 'edit_post', $_POST['mep_timeline_postID'] ) ) {
                wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
                die;
            }

            $post_id = intval($_POST['mep_timeline_postID']);
            $mep_timeline = get_post_meta($post_id, 'mep_event_day', true);
            $mep_timeline = is_array($mep_timeline) ? $mep_timeline : [];
            $new_data = [
                'mep_day_title' => sanitize_text_field($_POST['mep_timeline_title']),
                'mep_day_time' => sanitize_text_field($_POST['mep_timeline_time']),
                'mep_day_content' => wp_kses_post($_POST['mep_timeline_content'])
            ];
            array_push($mep_timeline,$new_data);
            $result = update_post_meta($post_id, 'mep_event_day', $mep_timeline);
            if($result){
                ob_start();
                $resultMessage = __('Data Added Successfully', 'mptbm_plugin_pro');
                $this->show_timeline_data($post_id);
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

        public function timeline_delete_item(){

            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mep-ajax-nonce' ) ) {
                wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
                die;
            }
            if ( ! current_user_can( 'edit_post', $_POST['mep_timeline_postID'] ) ) {
                wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
                die;
            }

            $post_id = intval($_POST['mep_timeline_postID']);
            $mep_timeline = get_post_meta($post_id,'mep_event_day',true);
            $mep_timeline =  is_array($mep_timeline) ? $mep_timeline : [];
            if( ! empty($mep_timeline)){
                if(isset($_POST['itemId'])){
                    unset($mep_timeline[$_POST['itemId']]);
                    $mep_timeline = array_values($mep_timeline);
                }
            }
            $result = update_post_meta($post_id, 'mep_event_day', $mep_timeline);
            if($result){
                ob_start();
                $resultMessage = __('Data Deleted Successfully', 'mptbm_plugin_pro');
                $this->show_timeline_data($post_id);
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
            if (
                !isset($_POST['mep_event_ticket_type_nonce']) ||
                !wp_verify_nonce($_POST['mep_event_ticket_type_nonce'], 'mep_event_ticket_type_nonce')
            ) {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }            
            if ( get_post_type( $post_id ) == 'mep_events' ) {

                $title   = isset($_POST['mep_timeline_title_raw']) ? (array) $_POST['mep_timeline_title_raw'] : array();
                $time   = isset($_POST['mep_timeline_time_raw']) ? (array) $_POST['mep_timeline_time_raw'] : array();
                $details = isset($_POST['mep_timeline_details_raw']) ? (array) $_POST['mep_timeline_details_raw'] : array();
                $combined = array();
                foreach ($title as $index => $value) {
                    if (isset($details[$index]) && trim($value) !== '') {
                        $combined[] = array(
                            'mep_day_title'   => sanitize_text_field($value),
                            'mep_day_time' => sanitize_text_field($time[$index]),
                            'mep_day_content' => sanitize_textarea_field($details[$index])
                        );
                    }
                }
                update_post_meta($post_id, 'mep_event_day', $combined);
                
            }
        }







    }
    new MPWEM_Timeline_Details();
}