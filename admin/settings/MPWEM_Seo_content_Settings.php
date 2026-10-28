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
				add_action( 'mpwem_event_tab_setting_item', array( $this, 'seo_settings' ) );
			}

			public function seo_settings( $event_id ) {
				$event_label = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				$event_start_date     = get_post_meta( $event_id, 'event_start_datetime', true ) ? get_post_meta( $event_id, 'event_start_datetime', true ) : '';
				$event_end_date       = get_post_meta( $event_id, 'event_end_datetime', true ) ? get_post_meta( $event_id, 'event_end_datetime', true ) : '';
				$event_rt_status      = get_post_meta( $event_id, 'mep_rt_event_status', true ) ? get_post_meta( $event_id, 'mep_rt_event_status', true ) : 'EventScheduled';
				$event_rt_atdnce_mode = get_post_meta( $event_id, 'mep_rt_event_attandence_mode', true ) ? get_post_meta( $event_id, 'mep_rt_event_attandence_mode', true ) : '';
				// Left empty on purpose: previousStartDate only applies to a rescheduled or postponed event.
				$event_rt_prv_date    = get_post_meta( $event_id, 'mep_rt_event_prvdate', true ) ? get_post_meta( $event_id, 'mep_rt_event_prvdate', true ) : '';
				$rt_status            = get_post_meta( $event_id, 'mep_rich_text_status', true );
				?>
                <div class="mp_tab_item" data-tab-item="#mp_event_rich_text" style="padding:15px">
                    <h3><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Rich Texts for SEO & Google Schema Text', 'mage-eventpress' ); ?></h3>
                    <p><?php esc_html_e( 'Configure Your Settings Here', 'mage-eventpress' ) ?></p>
                    <section>
                        <label class="mpev-label">
                            <div>
                                <h2><?php esc_html_e( 'Rich Text Status', 'mage-eventpress' ); ?></h2>
                                <span class="label-text"><?php _e( 'You can change the date and time format by going to the settings', 'mage-eventpress' ); ?></span>
                            </div>
                            <select id="mep_rich_text_status" name="mep_rich_text_status">
								<option value="enable" <?php selected( $rt_status, 'enable' ); ?>> <?php  esc_html_e( 'Enable', 'mage-eventpress' ); ?></option>
                                <option value="disable" <?php echo $rt_status == 'disable' ? 'selected' : ''; ?>> <?php esc_html_e( 'Disable', 'mage-eventpress' ); ?></option>
                            </select>
                        </label>
                    </section>
                    <section id='mep_rich_text_table' class="mpwem-schema-panel" style="display:<?php echo ( $rt_status == 'enable' ) ? 'block' : 'none'; ?>">

                        <div class="mpwem-schema-panel__group">
                            <span class="mpwem-schema-panel__group-label"><?php esc_html_e( 'Schema Preview', 'mage-eventpress' ); ?></span>
                            <div class="mpwem-schema-panel__chips">
                                <span class="mpwem-schema-panel__chip"><?php esc_html_e( 'Type:', 'mage-eventpress' ); ?> <?php esc_html_e( 'Event', 'mage-eventpress' ); ?></span>
                                <span class="mpwem-schema-panel__chip"><?php esc_html_e( 'Name:', 'mage-eventpress' ); ?> <?php echo esc_html( get_the_title( $event_id ) ); ?></span>
								<?php if ( $event_start_date ) { ?>
                                    <span class="mpwem-schema-panel__chip"><?php esc_html_e( 'Start:', 'mage-eventpress' ); ?> <?php echo esc_html( get_mep_datetime( $event_start_date, 'date-time-text' ) ); ?></span>
								<?php } else { ?>
                                    <span class="mpwem-schema-panel__chip mpwem-schema-panel__chip--warn"><?php esc_html_e( 'Start date not set', 'mage-eventpress' ); ?></span>
								<?php } ?>
								<?php if ( $event_end_date ) { ?>
                                    <span class="mpwem-schema-panel__chip"><?php esc_html_e( 'End:', 'mage-eventpress' ); ?> <?php echo esc_html( get_mep_datetime( $event_end_date, 'date-time-text' ) ); ?></span>
								<?php } else { ?>
                                    <span class="mpwem-schema-panel__chip mpwem-schema-panel__chip--warn"><?php esc_html_e( 'End date not set', 'mage-eventpress' ); ?></span>
								<?php } ?>
                            </div>
                        </div>

                        <div class="mpwem-schema-panel__group">
                            <span class="mpwem-schema-panel__group-label"><?php esc_html_e( 'Schema Settings', 'mage-eventpress' ); ?></span>

                            <div class="mpwem-schema-panel__fields">
                                <label class="mpwem-schema-panel__field">
                                    <span class="mpwem-schema-panel__field-label"><?php esc_html_e( 'Event Status', 'mage-eventpress' ); ?></span>
                                    <select class="mp_formControl" name="mep_rt_event_status">
                                        <option value="EventScheduled" <?php selected( $event_rt_status, 'EventScheduled' ); ?>><?php esc_html_e( 'Event Scheduled', 'mage-eventpress' ); ?></option>
                                        <option value="EventRescheduled" <?php selected( $event_rt_status, 'EventRescheduled' ); ?>><?php esc_html_e( 'Event Rescheduled', 'mage-eventpress' ); ?></option>
                                        <option value="EventMovedOnline" <?php echo ( $event_rt_status == 'EventMovedOnline' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Event Moved Online', 'mage-eventpress' ); ?></option>
                                        <option value="EventPostponed" <?php echo ( $event_rt_status == 'EventPostponed' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Event Postponed', 'mage-eventpress' ); ?></option>
                                        <option value="EventCancelled" <?php echo ( $event_rt_status == 'EventCancelled' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Event Cancelled', 'mage-eventpress' ); ?></option>
                                    </select>
                                </label>
                                <label class="mpwem-schema-panel__field">
                                    <span class="mpwem-schema-panel__field-label"><?php esc_html_e( 'Attendance Mode', 'mage-eventpress' ); ?></span>
                                    <select class="mp_formControl" name="mep_rt_event_attandence_mode">
                                        <option value="OfflineEventAttendanceMode" <?php echo ( $event_rt_atdnce_mode == 'OfflineEventAttendanceMode' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'OfflineEventAttendanceMode', 'mage-eventpress' ); ?></option>
                                        <option value="OnlineEventAttendanceMode" <?php echo ( $event_rt_atdnce_mode == 'OnlineEventAttendanceMode' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'OnlineEventAttendanceMode', 'mage-eventpress' ); ?></option>
                                        <option value="MixedEventAttendanceMode" <?php echo ( $event_rt_atdnce_mode == 'MixedEventAttendanceMode' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'MixedEventAttendanceMode', 'mage-eventpress' ); ?></option>
                                    </select>
                                </label>
                            </div>

                            <label class="mpwem-schema-panel__field">
                                <span class="mpwem-schema-panel__field-label">
													<?php esc_html_e( 'Previous Start Date', 'mage-eventpress' ); ?>
                                    <span class="mpwem-info-tip" title="<?php esc_attr_e( 'Only used when the event status is Rescheduled or Postponed. Leave empty otherwise.', 'mage-eventpress' ); ?>">i</span>
                                </span>
                                <input type='text' class="mp_formControl" name="mep_rt_event_prvdate" placeholder="YYYY-MM-DD HH:MM:SS" value='<?php echo esc_attr( $event_rt_prv_date ); ?>'/>
                                <span class="mpwem-schema-panel__field-hint"><?php esc_html_e( 'Only used when the event status is Rescheduled or Postponed. Leave empty otherwise.', 'mage-eventpress' ); ?></span>
                            </label>

							<?php if ( $event_id ) { ?>
                                <a class="mpwem-schema-panel__check" href="<?php echo esc_url( add_query_arg( array( 'utm_campaign' => 'devsite', 'utm_medium' => 'jsonld', 'utm_source' => 'event', 'url' => get_permalink( $event_id ), 'user_agent' => 2 ), 'https://search.google.com/test/rich-results' ) ); ?>" target="_blank" rel="noopener noreferrer">
												<?php esc_html_e( 'Check Rich Text Status', 'mage-eventpress' ); ?>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7"></path><path d="M7 7h10v10"></path></svg>
                                </a>
							<?php } ?>
                        </div>

                    </section>
					<?php do_action( 'mep_event_tab_after_rich_text' ); ?>
                </div>
				<?php
			}
		}
		new MPWEM_Seo_content_Settings();
	}
