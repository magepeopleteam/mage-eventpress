<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'MPWEM_Speaker_Settings' ) ) {
		class MPWEM_Speaker_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', [ $this, 'speaker_tab_setting_item' ], 10, 2 );
			}
			public function speaker_tab_setting_item( $event_id, $event_infos ) {
				$speaker_title       = array_key_exists( 'mep_speaker_title', $event_infos ) ? $event_infos['mep_speaker_title'] : '';
				$speaker_icon        = array_key_exists( 'mep_event_speaker_icon', $event_infos ) ? $event_infos['mep_event_speaker_icon'] : '';
				$speaker_lists       = array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
				$speaker_lists       = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
				$general_setting_sec = array_key_exists( 'general_setting_sec', $event_infos ) ? $event_infos['general_setting_sec'] : [];
				$event_label         = array_key_exists( 'mep_event_label', $general_setting_sec ) ? $general_setting_sec['mep_event_label'] : __( 'Events', 'mage-eventpress' );
				$all_speakers        = MPWEM_Query::get_all_post_ids( 'mep_event_speaker' );
				?>
                <div class="mpwem_style mp_tab_item mpwem_speaker_settings" data-tab-item="#mpwem_speaker_settings">
                    <div class="_layout_default_xs_mp_zero">
                        <div class="_bg_light_padding">
                            <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Speaker Settings', 'mage-eventpress' ); ?></h4>
                            <span class="_mp_zero"><?php esc_html_e( 'Speaker Settings will be here.', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_padding_bt">
                            <label class="_justify_between_align_center_wrap ">
                                <span class="_mr"><?php esc_html_e( 'Speaker Section\'s Label', 'mage-eventpress' ); ?></span>
                                <input type="text" class="formControl" name="mep_speaker_title" value="<?php echo esc_attr( $speaker_title ); ?>" placeholder="<?php esc_attr_e( 'Speakers', 'mage-eventpress' ); ?>"/>
                            </label>
                            <span class="info_text"><?php esc_html_e( 'This is the heading for the Speaker List that will be displayed on the frontend. The default heading is "Speakers."', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_padding_bt">
                            <div class="_justify_between_align_center_wrap ">
                                <label><span class="_mr"><?php esc_html_e( 'Speaker Icon', 'mage-eventpress' ); ?></span></label>
								<?php do_action( 'mpwem_input_add_icon', 'mep_event_speaker_icon', $speaker_icon ); ?>
                            </div>
                            <span class="info_text">
                                <?php esc_html_e( 'Please select Speakers. You can add new speakers from ', 'mage-eventpress' ); ?>
                                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mep_event_speaker' ) ) ?>"><?php esc_html_e( 'here', 'mage-eventpress' ); ?></a>
                            </span>
                        </div>
                        <div class="_padding_bt">
                            <label class="_justify_between_align_center_wrap ">
                                <span class="_mr"><?php esc_html_e( 'Speaker Icon', 'mage-eventpress' ); ?></span>
                                <select name="mep_event_speakers_list[]" id="" multiple>
									<?php foreach ( $all_speakers as $value ) { ?>
                                        <option value="<?php echo esc_attr( $value ); ?>" <?php echo in_array( $value, $speaker_lists ) ? 'selected' : ''; ?>>
											<?php echo esc_html( get_the_title( $value ) ); ?>
                                        </option>
									<?php } ?>
                                </select>
                            </label>
                            <span class="info_text"><?php esc_html_e( 'Please select the icon that will be used for the speaker icon.', 'mage-eventpress' ); ?></span>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Speaker_Settings();
	}