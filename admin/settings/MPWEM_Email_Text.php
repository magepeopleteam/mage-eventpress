<?php
	/**
	 * @author Shahadat Hossain <email@email.com>
	 * @version 1.0.0
	 * @copyright 2024 magepeople
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'MPWEM_Email_Text' ) ) {
		class MPWEM_Email_Text {
			public function __construct() {
				add_action( 'mp_event_all_in_tab_item', [ $this, 'email_text_tab_content' ] );
			}

			public function email_text_tab_content( $post_id ) {
				?>
                <div class="mp_tab_item mpStyle mpwem_email_text_settings" data-tab-item="#mpwem_email_text_settings">
                    <div class="_dLayout_xs_mp_zero">
                        <div class="_bgLight_padding_bB">
                            <h4><?php esc_html_e( 'Email Text settings', 'mage-eventpress' ); ?></h4>
                            <span class="_mp_zero"><?php esc_html_e( 'Configure email template text', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_padding_bB">
                            <h5><?php esc_html_e( 'Email Text', 'mage-eventpress' ); ?></h5>
                            <span class="_mp_zero"><?php esc_html_e( 'Usable Dynamic tags', 'mage-eventpress' ) ?></span>
                            <ul class="mp_list">
                                <li><span class="_min_200"><?php esc_html_e( 'Attendee Name', 'mage-eventpress' ); ?></span><code>{name}</code></li>
                                <li><span class="_min_200"><?php esc_html_e( 'Event Name', 'mage-eventpress' ); ?></span><code>{event}</code></li>
                                <li><span class="_min_200"><?php esc_html_e( 'Ticket Type', 'mage-eventpress' ); ?></span><code>{ticket_type}</code></li>
                                <li><span class="_min_200"><?php esc_html_e( 'Event Date', 'mage-eventpress' ); ?></span><code>{event_date}</code></li>
                                <li><span class="_min_200"><?php esc_html_e( 'Start Time', 'mage-eventpress' ); ?></span><code>{event_time}</code></li>
                                <li><span class="_min_200"><?php esc_html_e( 'Full DateTime', 'mage-eventpress' ); ?></span><code>{event_datetime}</code></li>
                            </ul>
                            <div class="_mT">
								<?php
									$content   = get_post_meta( $post_id, 'mep_event_cc_email_text', true ) ?: '';
									//echo '<pre>';print_r($content);echo '</pre>';
									wp_editor($content, 'mep_event_cc_email_text', array(
										'editor_height' => 150,
										'media_buttons' => true,
										'textarea_name' => 'mep_event_cc_email_text',
									));
								?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Email_Text();
	}