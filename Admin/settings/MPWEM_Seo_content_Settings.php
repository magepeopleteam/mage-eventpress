<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Seo_content_Settings' ) ) {
		class MPWEM_Seo_content_Settings {
			public function __construct() {
				add_action( 'mp_event_all_in_tab_item', array( $this, 'seo_settings' ) );
			}

			public function seo_settings( $event_id ) {
				$event_label = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				$event_start_date     = get_post_meta( $event_id, 'event_start_datetime', true ) ? get_post_meta( $event_id, 'event_start_datetime', true ) : '';
				$event_end_date       = get_post_meta( $event_id, 'event_end_datetime', true ) ? get_post_meta( $event_id, 'event_end_datetime', true ) : '';
				$event_rt_status      = get_post_meta( $event_id, 'mep_rt_event_status', true ) ? get_post_meta( $event_id, 'mep_rt_event_status', true ) : '';
				$event_rt_atdnce_mode = get_post_meta( $event_id, 'mep_rt_event_attandence_mode', true ) ? get_post_meta( $event_id, 'mep_rt_event_attandence_mode', true ) : '';
				$event_rt_prv_date    = get_post_meta( $event_id, 'mep_rt_event_prvdate', true ) ? get_post_meta( $event_id, 'mep_rt_event_prvdate', true ) : $event_start_date;
				$rt_status            = get_post_meta( $event_id, 'mep_rich_text_status', true );
				?>
                <div class="mp_tab_item" data-tab-item="#mp_event_rich_text">
                    <h3><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Rich Texts for SEO & Google Schema Text', 'mage-eventpress' ); ?></h3>
                    <p><?php esc_html_e( 'Configure Your Settings Here', 'mage-eventpress' ) ?></p>
                    <section class="bg-light">
                        <h2><?php esc_html_e( 'Tax Settings', 'mage-eventpress' ) ?></h2>
                        <span><?php esc_html_e( 'Configure Event Tax', 'mage-eventpress' ) ?></span>
                    </section>
                    <section>
                        <label class="mpev-label">
                            <div>
                                <h2><span><?php esc_html_e( 'Rich Text Status', 'mage-eventpress' ); ?></span></h2>
                                <span><?php _e( 'You can change the date and time format by going to the settings', 'mage-eventpress' ); ?></span>
                            </div>
                            <select id="mep_rich_text_status" name="mep_rich_text_status">
                                <option value="enable" <?php echo $rt_status == 'eanble' ? 'selected' : ''; ?>> <?php echo esc_html__( 'Enable', 'mage-eventpress' ); ?></option>
                                <option value="disable" <?php echo $rt_status == 'disable' ? 'selected' : ''; ?>> <?php echo esc_html__( 'Disable', 'mage-eventpress' ); ?></option>
                            </select>
                        </label>
                    </section>
                    <section id='mep_rich_text_table' style="display:<?php echo ( $rt_status == 'enable' ) ? 'block' : 'none'; ?>">
                        <table>
                            <tr>
                                <td><span><?php esc_html_e( 'Type :', 'mage-eventpress' ); ?></span></td>
                                <td colspan="3"><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></td>
                            </tr>
                            <tr>
                                <td><span><?php esc_html_e( 'Name :', 'mage-eventpress' ); ?></span></td>
                                <td colspan="3"><?php echo get_the_title( $event_id ); ?></td>
                            </tr>
                            <tr>
                                <td><span><?php esc_html_e( 'Start Date :', 'mage-eventpress' ); ?></span></td>
                                <td colspan="3"><?php echo esc_attr( $event_start_date ) ? get_mep_datetime( $event_start_date, 'date-time-text' ) : ''; ?></td>
                            </tr>
                            <tr>
                                <td><span><?php _e( 'End Date :', 'mage-eventpress' ); ?></span></td>
                                <td colspan="3"><?php echo esc_attr( $event_end_date ) ? get_mep_datetime( $event_end_date, 'date-time-text' ) : ''; ?></td>
                            </tr>
                            <tr>
                                <td><span><?php esc_html_e( 'Event Status:', 'mage-eventpress' ); ?></span></td>
                                <td colspan="3">
                                    <label>
                                        <select class="mp_formControl" name="mep_rt_event_status">
                                            <option value="EventRescheduled" <?php echo ( $event_rt_status == 'EventMovedOnline' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Event Rescheduled', 'mage-eventpress' ); ?></option>
                                            <option value="EventMovedOnline" <?php echo ( $event_rt_status == 'EventMovedOnline' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Event Moved Online', 'mage-eventpress' ); ?></option>
                                            <option value="EventPostponed" <?php echo ( $event_rt_status == 'EventPostponed' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Event Postponed', 'mage-eventpress' ); ?></option>
                                            <option value="EventCancelled" <?php echo ( $event_rt_status == 'EventCancelled' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Event Cancelled', 'mage-eventpress' ); ?></option>
                                        </select>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><span><?php esc_html_e( 'Event Attendance Mode:', 'mage-eventpress' ); ?></span></td>
                                <td colspan="3">
                                    <label>
                                        <select class="mp_formControl" name="mep_rt_event_attandence_mode">
                                            <option value="OfflineEventAttendanceMode" <?php echo ( $event_rt_atdnce_mode == 'OfflineEventAttendanceMode' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'OfflineEventAttendanceMode', 'mage-eventpress' ); ?></option>
                                            <option value="OnlineEventAttendanceMode" <?php echo ( $event_rt_atdnce_mode == 'OnlineEventAttendanceMode' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'OnlineEventAttendanceMode', 'mage-eventpress' ); ?></option>
                                            <option value="MixedEventAttendanceMode" <?php echo ( $event_rt_atdnce_mode == 'MixedEventAttendanceMode' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'MixedEventAttendanceMode', 'mage-eventpress' ); ?></option>
                                        </select>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><span><?php esc_html_e( 'Previous Start Date:', 'mage-eventpress' ); ?></span></td>
                                <td colspan="3">
                                    <label>
                                        <input type='text' class="mp_formControl" name="mep_rt_event_prvdate" value='<?php echo esc_attr( $event_rt_prv_date ); ?>'/>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
									<?php
										if ( $event_id ) {
											?>
                                            <p class="event_meta_help_txt">
                                                <a href='https://search.google.com/test/rich-results?utm_campaign=devsite&utm_medium=jsonld&utm_source=event&url=<?php echo get_the_permalink( $event_id ); ?>&user_agent=2' target="_blank"><?php esc_html_e( 'Check Rich Text Status', 'mage-eventpress' ); ?></a>
                                            </p>
											<?php
										}
									?>
                                </td>
                            </tr>
                        </table>
                    </section>
					<?php do_action( 'mep_event_tab_after_rich_text' ); ?>
                </div>
				<?php
			}
		}
		new MPWEM_Seo_content_Settings();
	}