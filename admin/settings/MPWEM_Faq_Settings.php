<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'MPWEM_Faq_Settings' ) ) {
		class MPWEM_Faq_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', [ $this, 'faq_tab_content' ] );
				add_action( 'wp_ajax_mpwem_load_faq', array( $this, 'mpwem_load_faq' ) );
				add_action( 'wp_ajax_mpwem_save_faq', array( $this, 'mpwem_save_faq' ) );
				add_action( 'wp_ajax_mpwem_remove_faq', array( $this, 'mpwem_remove_faq' ) );
			}

			public function faq_tab_content( $post_id ) {
				$faq_infos = get_post_meta($post_id,'mep_event_faq',true);
				// Ensure $faq_infos is an array to prevent sizeof() error
				if ( ! is_array( $faq_infos ) ) {
					$faq_infos = array();
				}
				$faq_des   = MPWEM_Global_Function::get_post_info( $post_id, 'mep_faq_description', '' );
				//echo '<pre>';print_r($faq_infos);echo '</pre>';
				?>
                <div class="mp_tab_item mpwem_style mpwem_faq_settings" data-tab-item="#mep_event_faq_meta">
                    <div class="_layout_default_xs_mp_zero">
                        <div class="_bg_light_padding_bB">
                            <h4><?php esc_html_e( 'FAQ Settings', 'mage-eventpress' ); ?></h4>
                            <span class="_mp_zero"><?php esc_html_e( 'FAQ Settings will be here.', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_padding_bB">
                            <label class="justify_between">
                                <span class="_mr"><?php esc_html_e( 'FAQ Description', 'mage-eventpress' ); ?></span>
                                <textarea class="formControl" name="mep_faq_description" rows="6" placeholder="Explore essential details and clear up any doubts about the event."><?php echo esc_textarea( $faq_des ); ?></textarea>
                            </label>
                        </div>
                        <div class="_padding_bB">
                            <div class="mpwem_faq_area">
								<?php $this->faq_item( $faq_infos ); ?>
                            </div>
                            <button type="button" class="_button_default_xs_bgBlue" data-key="" data-target-popup="#mpwem_faq_popup"> <?php esc_html_e( 'Add New', 'mage-eventpress' ); ?></button>
							<?php //echo '<pre>';print_r($time_line_infos);echo '</pre>'; ?>
                        </div>
                    </div>
                    <div class="mpPopup right_popup mpwem_faq_popup" data-popup="#mpwem_faq_popup">
                        <div class="popupMainArea">
                            <span class="fas fa-times popup_close"></span>
                            <div class="popupBody faq_input">
								<?php //$this->mpwem_load_timeline(); ?>
                            </div>
                            <div class="popupFooter">
                                <div class="buttonGroup">
                                    <button type="button" class="_button_general_xs_bg_light mpwem_faq_save"><?php esc_html_e( 'Save', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="_button_general_xs_bg_light mpwem_faq_save_close"><?php esc_html_e( 'Save & Close', 'mage-eventpress' ); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function faq_item( $faq_infos ) {
				if ( is_array( $faq_infos ) && sizeof( $faq_infos ) > 0 ) {
					foreach ( $faq_infos as $key => $faq_info ) {
						if ( is_array( $faq_info ) && sizeof( $faq_info ) > 0 ) {
							$title       = array_key_exists( 'mep_faq_title', $faq_info ) ? $faq_info['mep_faq_title'] : '';
							$content     = array_key_exists( 'mep_faq_content', $faq_info ) ? $faq_info['mep_faq_content'] : '';
							$collapse_id = uniqid( 'mpwem_faq' );
							?>
                            <div class="_padding_border_mb_xs">
                                <div class="justify_between alignCenter">
                                    <h6 class="_fullWidth" data-collapse-target="<?php echo esc_attr( $collapse_id ); ?>"><span><?php echo esc_html( $title ); ?></span></h6>
                                    <div class="buttonGroup">
                                        <button type="button" data-collapse-target="<?php echo esc_attr( $collapse_id ); ?>" class="_button_general_xs_bg_light"><span class="fas fa-eye"></span></button>
                                        <button type="button" data-target-popup="#mpwem_faq_popup" data-key="<?php echo esc_attr( $key ); ?>" class="_button_general_xs_bg_light"><span class="fas fa-edit"></span></button>
                                        <button type="button" class="_button_general_xs_bg_light mpwem_faq_remove" data-key="<?php echo esc_attr( $key ); ?>"><span class="fas fa-trash"></span></button>
                                    </div>
                                </div>
                                <div class="mp_wp_editor" data-collapse="<?php echo esc_attr( $collapse_id ); ?>">
                                    <div class="_divider_xs"></div>
									<?php echo apply_filters( 'the_content', $content ); ?>
                                </div>
                            </div>
							<?php
						}
					}
				}
			}

			public function mpwem_load_faq() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$key      = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
				$faq_info = [];
				if ( $post_id ) {
					$faq_infos = get_post_meta($post_id,'mep_event_faq',true);
					// Ensure $faq_infos is an array to prevent sizeof() error
					if ( ! is_array( $faq_infos ) ) {
						$faq_infos = array();
					}
					if ( is_array( $faq_infos ) && sizeof( $faq_infos ) > 0 && array_key_exists( $key, $faq_infos ) ) {
						$faq_info = $faq_infos[ $key ];
					}
				}
				$title   = array_key_exists( 'mep_faq_title', $faq_info ) ? $faq_info['mep_faq_title'] : '';
				$content = html_entity_decode( array_key_exists( 'mep_faq_content', $faq_info ) ? $faq_info['mep_faq_content'] : '' );
				if ( $title ) {
					?>
                    <h4 class="_mb"><?php echo esc_html__( 'Edit F.A.Q Info : ', 'mage-eventpress' ) . esc_html( $title ); ?></h4>
					<?php
				} else {
					?>
                    <h4 class="_mb"><?php esc_html_e( 'Add New F.A.Q Info', 'mage-eventpress' ); ?></h4>
					<?php
				}
				?>
                <input type="hidden" name="faq_item_key" value="<?php echo esc_attr( $key ); ?>">
                <label>
                    <span><?php esc_html_e( 'Title', 'mage-eventpress' ); ?></span>
                    <input type="text" name="mep_faq_title" class="formControl" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_html_e( 'Title', 'mage-eventpress' ); ?>">
                </label>
                <label>
                    <span><?php esc_html_e( 'Content', 'mage-eventpress' ); ?></span>
					<?php
						$editor_id = 'mep_faq_content';
						$settings  = array(
							'textarea_name' => 'mep_faq_content',
							'media_buttons' => true,
							'textarea_rows' => 10,
							'tinymce' => array(
								'toolbar1' => 'formatselect | fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | blockquote | link unlink | removeformat | undo redo | code',
								'toolbar2' => '',
								'fontsize_formats' => '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt 60pt 72pt',
								'plugins' => 'link,lists,textcolor,colorpicker,wordpress,wpeditimage,wplink,wpview',
							),
							'quicktags' => true,
						);
						wp_editor( $content, $editor_id, $settings );
					?>
                </label>
				<?php
				die();
			}

			public function mpwem_remove_faq() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
				if ( $post_id ) {
					$faq_infos = get_post_meta($post_id,'mep_event_faq',true);
					if ( is_array( $faq_infos ) && sizeof( $faq_infos ) > 0 && array_key_exists( $key, $faq_infos ) ) {
						unset( $faq_infos[ $key ] );
						$faq_infos = array_values( $faq_infos );
						update_post_meta( $post_id, 'mep_event_faq', $faq_infos );
						$this->faq_item( $faq_infos );
					}
				}
				die();
			}

			public function mpwem_save_faq() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$key     = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
				$title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
				$des     = isset( $_POST['des'] ) ? sanitize_text_field( wp_unslash( $_POST['des'] ) ) : '';
				$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
				if ( $post_id ) {
					$faq_infos = get_post_meta($post_id,'mep_event_faq',true);
					// Ensure $faq_infos is an array to prevent sizeof() error
					if ( ! is_array( $faq_infos ) ) {
						$faq_infos = array();
					}
					if ( ! array_key_exists( $key, $faq_infos ) ) {
						$key = sizeof( $faq_infos );
					}
					$faq_infos[ $key ]['mep_faq_title']   = $title;
					$faq_infos[ $key ]['mep_faq_content'] = $content;
					update_post_meta( $post_id, 'mep_event_faq', $faq_infos );
					update_post_meta( $post_id, 'mep_faq_description', $des );
					$this->faq_item( $faq_infos );
				}
				die();
			}
		}
		new MPWEM_Faq_Settings();
	}
