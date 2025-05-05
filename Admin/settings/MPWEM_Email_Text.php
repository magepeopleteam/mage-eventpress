<?php
/**
 * @author Shahadat Hossain <email@email.com>
 * @version 1.0.0
 * @copyright 2024 magepeople
 */
if( ! defined('ABSPATH') ) die;
if(!class_exists('MPWEM_Email_Text')){

    class MPWEM_Email_Text{
        public function __construct() {
            add_action('mep_admin_event_details_before_tab_name_rich_text', [$this, 'email_text_tab']);
            add_action('mp_event_all_in_tab_item', [$this, 'email_text_tab_content']);


            add_action('wp_ajax_mep_email_text_save', [$this, 'email_text_save']);
            
        }

        public function email_text_save() {

            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mep-ajax-nonce' ) ) {
                wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
                die;
            }
            if ( ! current_user_can( 'edit_post', $_POST['mep_email_text_postID'] ) ) {
                wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
                die;
            }

            $post_id            = intval($_POST['mep_email_text_postID']);
            $email_text         = wp_kses_post($_POST['mep_email_text_content']);
            
            update_post_meta($post_id, 'mep_event_cc_email_text', $email_text);
            
            ob_start();
            $resultMessage = __('Data Added Successfully', 'mptbm_plugin_pro');
            $this->show_email_text($post_id);
            $html_output = ob_get_clean();
            wp_send_json_success([
                'message' => $resultMessage,
                'html' => $html_output,
            ]);

            die;
        }

        public function show_email_text($post_id){
            $text = get_post_meta($post_id, 'mep_event_cc_email_text', true);
            ?>
                <?php echo wp_kses_post($text); ?>
            <?php
        }

        public function email_text_tab(){
            ?>
            <li data-target-tabs="#mp_event_email_text">
                <i class="far fa-envelope-open"></i><?php esc_html_e('Email Text', 'mage-eventpress'); ?>
            </li>
            <?php
        }

        public function email_text_tab_content($post_id){
            ?>
            <div class="mp_tab_item" data-tab-item="#mp_event_email_text">
                <h3><?php echo esc_html_e('Email Text settings', 'mage-eventpress'); ?></h3>
                <p><?php esc_html_e('Email text settings','mage-eventpress') ?></p>

                <section class="bg-light">
                    <h2><?php esc_html_e('Email Template Preview','mage-eventpress') ?></h2>
                    <span><?php esc_html_e('Configure email template text','mage-eventpress') ?></span>
                </section>
                <section>
                    <div class="mep-email-text">
                        <?php $this->show_email_text($post_id); ?>
                    </div>
                    <button class="button mep-email-text-new" data-modal="mep-email-text-new" type="button"><?php _e('Manage Email Text','mage-eventpress'); ?></button>
                </section>

                <!-- sidebar collapse open -->
                <div class="mep-modal-container" data-modal-target="mep-email-text-new">
                    <div class="mep-modal-content">
                        <span class="mep-modal-close"><i class="fas fa-times"></i></span>
                        <div class="title">
                            <h3><?php _e('Add Email Text','mage-eventpress'); ?></h3>
                            <div id="mep-email-text-msg"></div>
                        </div>
                        <div class="content">
                            <div>
                                <p><b><?php _e('Usable Dynamic tags','mage-eventpress') ?></b></p>
                                <?php _e('Attendee Name','mage-eventpress') ?><code>{name}</code>
                                <?php _e('Event Name','mage-eventpress') ?><code>{event}</code>
                                <?php _e('Ticket Type','mage-eventpress') ?><code>{ticket_type}</code>
                                <?php _e('Event Date','mage-eventpress') ?><code>{event_date}</code>
                                <?php _e('Start Time','mage-eventpress') ?><code>{event_time}</code>
                                <?php _e('Full DateTime','mage-eventpress') ?><code>{event_datetime}</code></p>
                            </div>
                            <label>
                                <input type="hidden" name="mep_post_id" value="<?php echo $post_id; ?>">
                            </label>
                            <?php 
                                $content = ''; 
                                $editor_id = 'mep_event_cc_email_text';
                                $settings = array(
                                    'textarea_name' => 'mep_event_cc_email_text',
                                    'media_buttons' => true,
                                    'textarea_rows' => 20,
                                );
                                wp_editor( $content, $editor_id, $settings );
                            ?>
                            <div class="mT"></div>
                            <div class="mep_email_text_save_buttons">
                                <p><button id="mep_email_text_save" class="button button-primary button-large"><?php _e('Save','mage-eventpress'); ?></button> <button id="mep_email_text_save_close" class="button button-primary button-large">save close</button><p>
                            </div>
                            <div class="mep_email_text_update_buttons" style="display: none;">
                                <p><button id="mep_email_text_update" class="button button-primary button-large"><?php _e('Update and Close','mage-eventpress'); ?></button><p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            <?php
        }
    }   

    new MPWEM_Email_Text();
}