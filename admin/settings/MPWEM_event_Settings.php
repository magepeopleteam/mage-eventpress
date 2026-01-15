<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_event_Settings' ) ) {
		class MPWEM_event_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', array( $this, 'event_settings' ) );
				add_action( 'wp_ajax_mpwem_reset_booking', array( $this, 'mpwem_reset_booking' ) );
				add_action( 'wp_ajax_nopriv_mpwem_reset_booking', array( $this, 'mpwem_reset_booking' ) );
			}

			public function event_settings( $event_id ) {
				$event_label=MPWEM_Global_Function::get_settings('general_setting_sec','mep_event_label','Events');
				?>
                <div class="mpwem_style mp_tab_item mpwem_event_settings" data-tab-item="#mpwem_event_settings">
                    <div class="_layout_default_xs_mp_zero">
                        <div class="_bg_light_padding">
                            <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Settings', 'mage-eventpress' ); ?></h4>
                            <span class="_mp_zero"><?php esc_html_e( 'Configure Your Settings Here.', 'mage-eventpress' ); ?></span>
                        </div>
						<?php
							$this->sku( $event_id );
							$this->display_end_date_time( $event_id );
							$this->display_available_seat( $event_id );
							$this->reset_booking();
							$this->event_member( $event_id );
						?>
                    </div>
					<?php do_action( 'mp_event_switching_button_hook', $event_id ); ?>
					<?php do_action( 'mep_event_tab_after_settings' ); ?>
                </div>
				<?php
			}

			public function sku( $event_id ) {
				$sku = MPWEM_Global_Function::get_post_info( $event_id, '_sku' );
				?>
                <div class="_padding_bt">
                    <label class="_justify_between_align_center_wrap ">
                        <span class="_mr"><?php esc_html_e( 'Event SKU No', 'mage-eventpress' ); ?></span>
                        <input class="formControl id_validation" type="text" name="mep_event_sku" value="<?php echo esc_attr( $sku ); ?>" placeholder="<?php esc_attr_e( 'Event SKU No', 'mage-eventpress' ); ?>"/>
                    </label>
                    <span class="label-text"><?php esc_html_e( 'Event SKU No', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function display_end_date_time( $event_id ) {
				$display = MPWEM_Global_Function::get_post_info( $event_id, 'mep_show_end_datetime', 'yes' );
				$checked = $display == 'no' ? '' : 'checked';
				?>
                <div class="_padding_bt">
                    <div class="_justify_between_align_center_wrap ">
                        <label><span class="_mr"><?php esc_html_e( 'Display End Datetime', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_show_end_datetime', $checked ); ?>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'You can ON/OFF End date  time display by going to the settings', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function display_available_seat( $event_id ) {
				$display = MPWEM_Global_Function::get_post_info( $event_id, 'mep_available_seat', 'on' );
				$checked = $display == 'off' ? '' : 'checked';
				?>
                <div class="_padding_bt">
                    <div class="_justify_between_align_center_wrap ">
                        <label><span class="_mr"><?php esc_html_e( 'Show Available Seat?', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_available_seat', $checked ); ?>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'You can ON/OFF available seat display by going to the settings', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function reset_booking() {
				?>
                <div class="_padding_bt">
                    <div class="_justify_between_align_center_wrap ">
                        <label><span class="_mr"><?php esc_html_e( 'Reset Booking Count', 'mage-eventpress' ); ?></span></label>
                        <button type="button" class="_button_general_xs_button_primary mpwem_reset_booking"><span class="fas fa-refresh _mr_xs"></span><?php esc_html_e( 'Reset Booking', 'mage-eventpress' ); ?></button>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'If you reset this count, all booking information will be removed, including the attendee list. This action is irreversible, so please be sure before you proceed.', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function event_member( $event_id ) {
				$user_roles     = MPWEM_Global_Function::get_post_info( $event_id, 'mep_member_only_user_role', [] );
				$display        = MPWEM_Global_Function::get_post_info( $event_id, 'mep_member_only_event', 'for_all' );
				$checked        = $display == 'for_all' ? '' : 'checked';
				$active         = $display == 'for_all' ? '' : 'mActive';
				$editable_roles = get_editable_roles();
				?>
                <div class="_padding_bt">
                    <div class="_justify_between_align_center_wrap ">
                        <label><span class="_mr"><?php esc_html_e( 'Member Only Event?', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_member_only_event', $checked ); ?>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'You can change event ticket role by going to the settings', 'mage-eventpress' ); ?></span>
                </div>
                <div class="_padding_bt <?php echo esc_attr( $active ); ?>" data-collapse="#mep_member_only_event">
                    <label class="_justify_between_align_center_wrap ">
                        <span class="_mr"><?php esc_html_e( 'Select User Role', 'mage-eventpress' ); ?></span>
                        <select name='mep_member_only_user_role[]' class="formControl mp_select2" multiple>
                            <option value="all" <?php echo esc_attr( in_array( 'all', $user_roles ) ? 'selected' : '' ); ?>><?php esc_html_e( 'For Any Logged in user', 'mage-eventpress' ); ?></option>
							<?php foreach ( $editable_roles as $role => $details ) { ?>
                                <option value="<?php echo esc_attr( $role ); ?>" <?php echo esc_attr( in_array( $role, $user_roles ) ? 'selected' : '' ); ?>><?php echo translate_user_role( $details['name'] ); ?></option>
							<?php } ?>
                        </select>
                    </label>
                    <span class="label-text"><?php esc_html_e( 'You can select event ticket role by going to the settings', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function mpwem_reset_booking() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$reset = mep_reset_event_booking( $post_id );
				if ( $reset ) {
					esc_html_e( "Successfully Booking Reset ", 'mage-eventpress' );
				} else {
					esc_html_e( "Booking Reset unsuccessful", 'mage-eventpress' );
				}
				die();
			}
		}
		new MPWEM_event_Settings();
	}