<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'MPWEM_Timeline_Details' ) ) {
		class MPWEM_Timeline_Details {
			public function __construct() {
				add_action( 'mp_event_all_in_tab_item', [ $this, 'timeline_tab_content' ] );
				add_action( 'wp_ajax_mpwem_load_timeline', array( $this, 'mpwem_load_timeline' ) );
				add_action( 'wp_ajax_nopriv_mpwem_load_timeline', array( $this, 'mpwem_load_timeline' ) );
				add_action( 'wp_ajax_mpwem_save_timeline', array( $this, 'mpwem_save_timeline' ) );
				add_action( 'wp_ajax_nopriv_mpwem_save_timeline', array( $this, 'mpwem_save_timeline' ) );
				add_action( 'wp_ajax_mpwem_remove_timeline', array( $this, 'mpwem_remove_timeline' ) );
				add_action( 'wp_ajax_nopriv_mpwem_remove_timeline', array( $this, 'mpwem_remove_timeline' ) );
			}

			public function timeline_tab_content( $post_id ) {
				$time_line_infos = MP_Global_Function::get_post_info( $post_id, 'mep_event_day', [] );
				?>
                <div class="mp_tab_item mpStyle mpwem_timeline_settings" data-tab-item="#mep_event_timeline_meta">
                    <div class="_dLayout_xs_mp_zero">
                        <div class="_bgLight_padding_bB">
                            <h4><?php esc_html_e( 'Timeline Settings', 'mage-eventpress' ); ?></h4>
                            <span class="_mp_zero"><?php esc_html_e( 'Timeline Settings is an activity display system, designed to showcase event activities in a structured timeline format in event details page.', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_padding_bB">
                            <div class="mpwem_timeline_area">
								<?php $this->timeline_item( $time_line_infos ); ?>
                            </div>
                            <button type="button" class="_dButton_xs_bgBlue" data-key="" data-target-popup="#mpwem_timeline_popup"> <?php esc_html_e( 'Add New', 'mage-eventpress' ); ?></button>
							<?php //echo '<pre>';print_r($time_line_infos);echo '</pre>'; ?>
                        </div>
                    </div>
                    <div class="mpPopup right_popup mpwem_timeline_popup" data-popup="#mpwem_timeline_popup">
                        <div class="popupMainArea">
                            <span class="fas fa-times popupClose"></span>
                            <div class="popupBody timeline_input">
								<?php //$this->mpwem_load_timeline(); ?>
                            </div>
                            <div class="popupFooter">
                                <div class="buttonGroup">
                                    <button type="button" class="_mpBtn_xs_bgLight mpwem_timeline_save"><?php esc_html_e( 'Save', 'mage-eventpress' ); ?></button>
                                    <button type="button" class="_mpBtn_xs_bgLight mpwem_timeline_save_close"><?php esc_html_e( 'Save & Close', 'mage-eventpress' ); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function timeline_item( $time_line_infos ) {
				if ( sizeof( $time_line_infos ) > 0 ) {
					foreach ( $time_line_infos as $key => $time_line_info ) {
						if ( is_array( $time_line_info ) && sizeof( $time_line_info ) > 0 ) {
							$title       = array_key_exists( 'mep_day_title', $time_line_info ) ? $time_line_info['mep_day_title'] : '';
							$time        = array_key_exists( 'mep_day_time', $time_line_info ) ? $time_line_info['mep_day_time'] : '';
							$content     = array_key_exists( 'mep_day_content', $time_line_info ) ? $time_line_info['mep_day_content'] : '';
							$collapse_id = uniqid( 'mpwem_time_line' );
							?>
                            <div class="_padding_border_mB_xs">
                                <div class="justifyBetween alignCenter">
                                    <h6 class="_fullWidth justifyBetween" data-collapse-target="<?php echo esc_attr( $collapse_id ); ?>">
                                        <span><?php echo esc_html( $title ); ?></span>
                                        <span class="_pR_pL"><?php echo esc_html( $time ); ?>
                                    </h6>
                                    <div class="buttonGroup">
                                        <button type="button" data-collapse-target="<?php echo esc_attr( $collapse_id ); ?>" class="_mpBtn_xs_bgLight"><span class="fas fa-eye"></span></button>
                                        <button type="button" data-target-popup="#mpwem_timeline_popup" data-key="<?php echo esc_attr( $key ); ?>" class="_mpBtn_xs_bgLight"><span class="fas fa-edit"></span></button>
                                        <button type="button" class="_mpBtn_xs_bgLight mpwem_timeline_remove" data-key="<?php echo esc_attr( $key ); ?>"><span class="fas fa-trash"></span></button>
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

			public function mpwem_load_timeline() {
				$post_id        = isset( $_REQUEST['post_id'] ) ? sanitize_text_field( $_REQUEST['post_id'] ) : '';
				$key            = isset( $_REQUEST['key'] ) ? sanitize_text_field( $_REQUEST['key'] ) : '';
				$time_line_info = [];
				if ( $post_id ) {
					$time_line_infos = MP_Global_Function::get_post_info( $post_id, 'mep_event_day', [] );
					if ( sizeof( $time_line_infos ) > 0 && array_key_exists( $key, $time_line_infos ) ) {
						$time_line_info = $time_line_infos[ $key ];
					}
				}
				$title   = array_key_exists( 'mep_day_title', $time_line_info ) ? $time_line_info['mep_day_title'] : '';
				$time    = array_key_exists( 'mep_day_time', $time_line_info ) ? $time_line_info['mep_day_time'] : '';
				$content = html_entity_decode( array_key_exists( 'mep_day_content', $time_line_info ) ? $time_line_info['mep_day_content'] : '' );
				if ( $title ) {
					?>
                    <h4 class="_mB"><?php echo esc_html__( 'Edit Timeline Info : ', 'mage-eventpress' ) . esc_html( $title ); ?></h4>
					<?php
				} else {
					?>
                    <h4 class="_mB"><?php esc_html_e( 'Add New Timeline Info', 'mage-eventpress' ); ?></h4>
					<?php
				}
				?>
                <input type="hidden" name="timeline_item_key" value="<?php echo esc_attr( $key ); ?>">
                <label>
                    <span><?php esc_html_e( 'Title', 'mage-eventpress' ); ?></span>
                    <input type="text" name="mep_timeline_title" class="formControl" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_html_e( 'Title', 'mage-eventpress' ); ?>">
                </label>
                <label>
                    <span><?php esc_html_e( 'Time', 'mage-eventpress' ); ?></span>
                    <input type="text" name="mep_timeline_time" class="formControl" value="<?php echo esc_attr( $time ); ?>" placeholder="<?php esc_html_e( 'Time', 'mage-eventpress' ); ?>">
                </label>
                <label>
                    <span><?php esc_html_e( 'Content', 'mage-eventpress' ); ?></span>
					<?php
						$editor_id = 'mep_timeline_content';
						$settings  = array(
							'textarea_name' => 'mep_timeline_content',
							'media_buttons' => true,
							'textarea_rows' => 10,
						);
						wp_editor( $content, $editor_id, $settings );
					?>
                </label>
				<?php
				die();
			}

			public function mpwem_remove_timeline() {
				$post_id = isset( $_REQUEST['post_id'] ) ? sanitize_text_field( $_REQUEST['post_id'] ) : '';
				$key     = isset( $_REQUEST['key'] ) ? sanitize_text_field( $_REQUEST['key'] ) : '';
				if ( $post_id ) {
					$time_line_infos = MP_Global_Function::get_post_info( $post_id, 'mep_event_day', [] );
					if ( sizeof( $time_line_infos ) > 0 && array_key_exists( $key, $time_line_infos ) ) {
						unset( $time_line_infos[ $key ] );
						$time_line_infos = array_values( $time_line_infos );
						update_post_meta( $post_id, 'mep_event_day', $time_line_infos );
						$this->timeline_item( $time_line_infos );
					}
				}
				die();
			}

			public function mpwem_save_timeline() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mep-ajax-nonce' ) ) {
					wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
					die;
				}
				$post_id = isset( $_REQUEST['post_id'] ) ? sanitize_text_field( $_REQUEST['post_id'] ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$key     = isset( $_REQUEST['key'] ) ? sanitize_text_field( $_REQUEST['key'] ) : '';
				$title   = isset( $_REQUEST['title'] ) ? sanitize_text_field( $_REQUEST['title'] ) : '';
				$time    = isset( $_REQUEST['time'] ) ? sanitize_text_field( $_REQUEST['time'] ) : '';
				$content = isset( $_REQUEST['content'] ) ? wp_kses_post( $_REQUEST['content'] ) : '';
				if ( $post_id ) {
					$time_line_infos = MP_Global_Function::get_post_info( $post_id, 'mep_event_day', [] );
					if ( ! array_key_exists( $key, $time_line_infos ) ) {
						$key = sizeof( $time_line_infos );
					}
					$time_line_infos[ $key ]['mep_day_title']   = $title;
					$time_line_infos[ $key ]['mep_day_time']    = $time;
					$time_line_infos[ $key ]['mep_day_content'] = $content;
					update_post_meta( $post_id, 'mep_event_day', $time_line_infos );
					$this->timeline_item( $time_line_infos );
				}
				die();
			}
		}
		new MPWEM_Timeline_Details();
	}