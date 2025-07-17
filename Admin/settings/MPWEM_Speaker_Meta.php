<?php
/**
 * Speaker Meta Box Handler
 * @author Custom Development
 * @license mage-people.com
 * @var 1.0.0
 */
if( ! defined('ABSPATH') ) die;

if( ! class_exists('MPWEM_Speaker_Meta')){
    class MPWEM_Speaker_Meta{
        public function __construct() {
            add_action('add_meta_boxes', array($this, 'add_speaker_meta_boxes'));
            add_action('save_post', array($this, 'save_speaker_meta'));
        }
        
        public function add_speaker_meta_boxes() {
            add_meta_box(
                'speaker_contact_info',
                __('Speaker Contact Information', 'mage-eventpress'),
                array($this, 'speaker_contact_meta_box'),
                'mep_event_speaker',
                'normal',
                'high'
            );
        }
        
        public function speaker_contact_meta_box($post) {
            wp_nonce_field('speaker_contact_nonce', 'speaker_contact_nonce');
            
            $phone = get_post_meta($post->ID, 'mep_speaker_phone', true);
            $email = get_post_meta($post->ID, 'mep_speaker_email', true);
            $website = get_post_meta($post->ID, 'mep_speaker_website', true);
            $designation = get_post_meta($post->ID, 'mep_speaker_designation', true);
            $company = get_post_meta($post->ID, 'mep_speaker_company', true);
            
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="mep_speaker_designation"><?php _e('Designation', 'mage-eventpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="mep_speaker_designation" name="mep_speaker_designation" value="<?php echo esc_attr($designation); ?>" class="regular-text" />
                        <p class="description"><?php _e('Speaker\'s job title or designation', 'mage-eventpress'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mep_speaker_company"><?php _e('Company', 'mage-eventpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="mep_speaker_company" name="mep_speaker_company" value="<?php echo esc_attr($company); ?>" class="regular-text" />
                        <p class="description"><?php _e('Company or organization name', 'mage-eventpress'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mep_speaker_phone"><?php _e('Phone', 'mage-eventpress'); ?></label>
                    </th>
                    <td>
                        <input type="tel" id="mep_speaker_phone" name="mep_speaker_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" />
                        <p class="description"><?php _e('Contact phone number', 'mage-eventpress'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mep_speaker_email"><?php _e('Email', 'mage-eventpress'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="mep_speaker_email" name="mep_speaker_email" value="<?php echo esc_attr($email); ?>" class="regular-text" />
                        <p class="description"><?php _e('Contact email address', 'mage-eventpress'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mep_speaker_website"><?php _e('Website', 'mage-eventpress'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="mep_speaker_website" name="mep_speaker_website" value="<?php echo esc_attr($website); ?>" class="regular-text" />
                        <p class="description"><?php _e('Personal or company website URL', 'mage-eventpress'); ?></p>
                    </td>
                </tr>
            </table>
            <?php
        }
        
        public function save_speaker_meta($post_id) {
            if (!isset($_POST['speaker_contact_nonce']) || !wp_verify_nonce($_POST['speaker_contact_nonce'], 'speaker_contact_nonce')) {
                return;
            }
            
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
            
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
            
            if (get_post_type($post_id) != 'mep_event_speaker') {
                return;
            }
            
            $fields = array(
                'mep_speaker_phone',
                'mep_speaker_email', 
                'mep_speaker_website',
                'mep_speaker_designation',
                'mep_speaker_company'
            );
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
                }
            }
        }
    }
    
    new MPWEM_Speaker_Meta();
}