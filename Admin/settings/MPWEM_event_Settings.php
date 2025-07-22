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
				add_action( 'mp_event_all_in_tab_item', array( $this, 'event_settings' ) );
				add_action( 'wp_ajax_mpwem_reset_booking', array( $this, 'mpwem_reset_booking' ) );
				add_action( 'wp_ajax_nopriv_mpwem_reset_booking', array( $this, 'mpwem_reset_booking' ) );
			}

			public function event_settings( $event_id ) {
				$event_label = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				?>
                <div class="mpStyle mp_tab_item mpwem_event_settings" data-tab-item="#mpwem_event_settings">
                    <div class="_dLayout_xs_mp_zero">
                        <div class="_bgLight_padding">
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
				$sku = MP_Global_Function::get_post_info( $event_id, '_sku' );
				?>
                <div class="_padding_bT">
                    <label class="justifyBetween _alignCenter">
                        <span><?php esc_html_e( 'Event SKU No', 'mage-eventpress' ); ?></span>
                        <input class="formControl mp_id_validation" type="text" name="mep_event_sku" value="<?php echo esc_attr( $sku ); ?>" placeholder="<?php esc_attr_e( 'Event SKU No', 'mage-eventpress' ); ?>"/>
                    </label>
                    <span class="des_info"><?php esc_html_e( 'Event SKU No', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function display_end_date_time( $event_id ) {
				$display = MP_Global_Function::get_post_info( $event_id, 'mep_show_end_datetime', 'yes' );
				$checked = $display == 'no' ? '' : 'checked';
				?>
                <div class="_padding_bT">
                    <div class="justifyBetween _alignCenter">
                        <label><span><?php esc_html_e( 'Display End Datetime', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_show_end_datetime', $checked ); ?>
                    </div>
                    <span class="des_info"><?php esc_html_e( 'You can ON/OFF End date  time display by going to the settings', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function display_available_seat( $event_id ) {
				$display = MP_Global_Function::get_post_info( $event_id, 'mep_available_seat', 'off' );
				$checked = $display == 'off' ? '' : 'checked';
				?>
                <div class="_padding_bT">
                    <div class="justifyBetween _alignCenter">
                        <label><span><?php esc_html_e( 'Show Available Seat?', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_available_seat', $checked ); ?>
                    </div>
                    <span class="des_info"><?php esc_html_e( 'You can ON/OFF available seat display by going to the settings', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function reset_booking() {
				?>
                <div class="_padding_bT">
                    <div class="justifyBetween _alignCenter">
                        <label><span><?php esc_html_e( 'Reset Booking Count', 'mage-eventpress' ); ?></span></label>
                        <button type="button" class="_mpBtn_xs_primaryButton mpwem_reset_booking"><span class="fas fa-refresh _mR_xs"></span><?php esc_html_e( 'Reset Booking', 'mage-eventpress' ); ?></button>
                    </div>
                    <span class="des_info"><?php esc_html_e( 'If you reset this count, all booking information will be removed, including the attendee list. This action is irreversible, so please be sure before you proceed.', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}

			public function event_member( $event_id ) {
				$user_roles     = MP_Global_Function::get_post_info( $event_id, 'mep_member_only_user_role', [] );
				$display        = MP_Global_Function::get_post_info( $event_id, 'mep_member_only_event', 'for_all' );
				$checked        = $display == 'for_all' ? '' : 'checked';
				$active         = $display == 'for_all' ? '' : 'mActive';
				$editable_roles = get_editable_roles();
				?>
                <div class="_padding_bT">
                    <div class="justifyBetween _alignCenter">
                        <label><span><?php esc_html_e( 'Member Only Event?', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_member_only_event', $checked ); ?>
                    </div>
                    <span class="des_info"><?php esc_html_e( 'You can change event ticket role by going to the settings', 'mage-eventpress' ); ?></span>
                </div>
                <div class="_padding_bT <?php echo esc_attr( $active ); ?>" data-collapse="#mep_member_only_event">
                    <label class="justifyBetween _alignCenter">
                        <span><?php esc_html_e( 'Select User Role', 'mage-eventpress' ); ?></span>
                        <select name='mep_member_only_user_role[]' class="fornControl mp_select2" multiple>
                            <option value="all" <?php echo esc_attr( in_array( 'all', $user_roles ) ? 'selected' : '' ); ?>><?php esc_html_e( 'For Any Logged in user', 'mage-eventpress' ); ?></option>
							<?php foreach ( $editable_roles as $role => $details ) { ?>
                                <option value="<?php echo esc_attr( $role ); ?>" <?php echo esc_attr( in_array( $role, $user_roles ) ? 'selected' : '' ); ?>><?php echo translate_user_role( $details['name'] ); ?></option>
							<?php } ?>
                        </select>
                    </label>
                    <span class="des_info"><?php esc_html_e( 'You can select event ticket role by going to the settings', 'mage-eventpress' ); ?></span>
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