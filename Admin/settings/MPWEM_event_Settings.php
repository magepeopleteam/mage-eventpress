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
			}

			public function event_settings( $event_id ) {
				$event_label = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				?>
                <div class="mp_tab_item" data-tab-item="#mp_event_settings">
                    <h3><?php echo esc_html( $event_label );
							esc_html_e( ' Settings :', 'mage-eventpress' ); ?></h3>
                    <p><?php esc_html_e( 'Configure Your Settings Here', 'mage-eventpress' ) ?></p>
					<?php $this->mp_event_settings( $event_id ); ?>
					<?php do_action( 'mep_event_tab_after_settings' ); ?>
                </div>
				<?php
			}

			public function mp_event_settings( $post_id ) {
				?>
                <section class="bg-light">
                    <h2><?php esc_html_e( 'General Settings', 'mage-eventpress' ) ?></h2>
                    <span><?php esc_html_e( 'Configure Event Locations and Virtual Venues', 'mage-eventpress' ) ?></span>
                </section>
				<?php $this->mp_event_reg_status( $post_id ); ?>
                <table>
					<?php
						$this->mp_event_enddatetime_status( $post_id );
						$this->mp_event_available_seat_status( $post_id );
						$this->mp_event_reset_booking_count( $post_id );
						do_action( 'mp_event_switching_button_hook', $post_id );
						$this->mp_event_speaker_ticket_type( $post_id );
					?>
                </table>
				<?php
			}

			public function mp_event_reg_status( $post_id ) {
				?>
                <section>
                    <label class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Event SKU No', 'mage-eventpress' ); ?></h2>
                            <span><?php _e( 'Event SKU No', 'mage-eventpress' ); ?></span>
                        </div>
                        <input class="mep_input_text" type="text" name="mep_event_sku" value="<?php echo get_post_meta( $post_id, '_sku', true ); ?>"/>
                    </label>
                </section>
				<?php
			}

			public function mp_event_enddatetime_status( $post_id ) {
				$mep_show_end_datetime = get_post_meta( $post_id, 'mep_show_end_datetime', true );
				$mep_show_end_datetime = $mep_show_end_datetime ? $mep_show_end_datetime : 'yes';
				?>
                <section>
                    <div class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Display End Datetime', 'mage-eventpress' ); ?></span></h2>
                            <span><?php _e( 'You can change the date and time format by going to the settings', 'mage-eventpress' ); ?></span>
                        </div>
                        <label class="mpev-switch">
                            <input type="checkbox" name="mep_show_end_datetime" value="<?php echo esc_attr( $mep_show_end_datetime ); ?>" <?php echo esc_attr( ( $mep_show_end_datetime == 'yes' ) ? 'checked' : '' ); ?> data-toggle-values="yes,no">
                            <span class="mpev-slider"></span>
                        </label>
                    </div>
                </section>
				<?php
			}

			public function mp_event_available_seat_status( $post_id ) {
				wp_nonce_field( 'mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce' );
				$seat_checked = get_post_meta( $post_id, 'mep_available_seat', true );
				$seat_checked = $seat_checked ? $seat_checked : 'no';
				?>
                <section>
                    <div class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Show Available Seat?', 'mage-eventpress' ); ?></h2>
                            <span><?php _e( 'You can change the date and time format by going to the settings', 'mage-eventpress' ); ?></span>
                        </div>
                        <label class="mpev-switch">
                            <input type="checkbox" name="mep_available_seat" value="<?php echo esc_attr( $seat_checked ); ?>" <?php echo esc_attr( ( $seat_checked == 'on' ) ? 'checked' : '' ); ?> data-toggle-values="on,off">
                            <span class="mpev-slider"></span>
                        </label>
                    </div>
                </section>
				<?php
			}

			public function mp_event_reset_booking_count( $post_id ) {
				?>
                <section>
                    <label class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Reset Booking Count', 'mage-eventpress' ); ?></span></h2>
                            <span><?php _e( 'If you reset this count, all booking information will be removed, including the attendee list. This action is irreversible, so please be sure before you proceed.', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="mpStyle">
                            <div class="_dFlex_justifyEnd">
                                <button class="button" type="button" id="mep-reset-booking" data-post-id='<?php echo esc_html( $post_id ); ?>'>
                                    <input type="hidden" class="hidden" id='mep-reset-booking-nonce' name='reset-booking-nonce' value="<?php echo wp_create_nonce( 'mep-ajax-reset-booking-nonce' ); ?>">
                                    <span class="fas fa-refresh"></span>
                                    <span class="mL_xs"><?php esc_html_e( 'Reset Booking', 'mage-eventpress' ); ?></span>
                                </button>
                            </div>
                            <div class="_dFlex_justifyEnd" id="mp-reset-status"></div>
                        </div>
                    </label>
                </section>
				<?php
			}

			public function mp_event_speaker_ticket_type( $post_id ) {
				$event_type        = get_post_meta( $post_id, 'mep_event_type', true );
				$event_member_type = get_post_meta( $post_id, 'mep_member_only_event', true );
				$saved_user_role   = get_post_meta( $post_id, 'mep_member_only_user_role', true ) ? get_post_meta( $post_id, 'mep_member_only_user_role', true ) : [];
				?>
                <section>
                    <div class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Member Only Event?', 'mage-eventpress' ); ?></span></h2>
                            <span><?php _e( 'You can change the date and time format by going to the settings', 'mage-eventpress' ); ?></span>
                        </div>
                        <label class="mpev-switch">
                            <input type="checkbox" name="mep_member_only_event" value="<?php echo esc_attr( $event_member_type ); ?>" <?php echo esc_attr( ( $event_member_type == 'member_only' ) ? 'checked' : '' ); ?> data-collapse-target="#event_virtual_type" data-toggle-values="member_only,for_all">
                            <span class="mpev-slider"></span>
                        </label>
                    </div>
                </section>
                <section id="event_virtual_type" style="display: <?php echo $event_member_type == 'member_only' ? 'block' : 'none'; ?>;">
                    <label class="mpev-label">
                        <div>
                            <h2><?php _e( 'Select User Role', 'mage-eventpress' ); ?></h2>
                            <span><?php _e( 'Select User Role', 'mage-eventpress' ); ?></span>
                        </div>
                        <select name='mep_member_only_user_role[]' multiple>
                            <option value="all" <?php if ( in_array( 'all', $saved_user_role ) ) {
								echo esc_attr( 'Selected' );
							} ?>><?php esc_html_e( 'For Any Logged in user', 'mage-eventpress' ); ?> </option>
							<?php echo mep_get_user_list( $saved_user_role ); ?>
                        </select>
                    </label>
                </section>
				<?php
			}
		}
		new MPWEM_event_Settings();
	}