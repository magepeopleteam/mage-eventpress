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
				$speaker_title       = is_array($event_infos) && array_key_exists( 'mep_speaker_title', $event_infos ) ? $event_infos['mep_speaker_title'] : '';
				$speaker_icon        = is_array($event_infos) && array_key_exists( 'mep_event_speaker_icon', $event_infos ) ? $event_infos['mep_event_speaker_icon'] : '';
				$speaker_lists       = is_array($event_infos) && array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
				$speaker_lists       = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
				$general_setting_sec = is_array($event_infos) && array_key_exists( 'general_setting_sec', $event_infos ) ? $event_infos['general_setting_sec'] : [];
				$event_label         = is_array($general_setting_sec) && array_key_exists( 'mep_event_label', $general_setting_sec ) ? $general_setting_sec['mep_event_label'] : __( 'Events', 'mage-eventpress' );
				$all_speakers        = MPWEM_Query::get_all_post_ids( 'mep_event_speaker' );
				$speaker_enabled     = $event_id ? get_post_meta( $event_id, 'mep_event_enable_speaker', true ) : '';
				if ( '' === $speaker_enabled ) {
					$speaker_enabled = 'no';
				}
				?>
                <div class="mpwem_style mp_tab_item mpwem_speaker_settings" data-tab-item="#mpwem_speaker_settings">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Speaker Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Speaker Settings will be here.', 'mage-eventpress' ); ?></span>
                    </div>
                    <div class="_padding_bt">
                        <div class="mpev-label">
                            <div>
                                <h2><?php esc_html_e( 'Enable Speaker Section', 'mage-eventpress' ); ?></h2>
                                <span class="label-text"><?php esc_html_e( 'Enable this to select speakers for this event. When disabled, the speaker section will not appear on the event page.', 'mage-eventpress' ); ?></span>
                            </div>
                            <label class="mpev-switch">
                                <input type="checkbox" name="mep_event_enable_speaker" id="mep_event_enable_speaker" value="<?php echo esc_attr( $speaker_enabled ); ?>" <?php echo esc_attr( ( $speaker_enabled === 'yes' ) ? 'checked' : '' ); ?> data-collapse-target="#mpwem-speaker-fields" data-close-target="" data-toggle-values="yes,no">
                                <span class="mpev-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="_layout_default_xs_mp_zero" id="mpwem-speaker-fields" style="display:<?php echo esc_attr( $speaker_enabled === 'yes' ? 'block' : 'none' ); ?>">
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
                            <span class="info_text"><?php esc_html_e( 'Please select the icon that will be used for the speaker section heading.', 'mage-eventpress' ); ?></span>
                        </div>
                        <div class="_padding_bt">
                            <div class="_justify_between_align_center_wrap mpwem-speaker-row">
                                <span class="_mr"><?php esc_html_e( 'Select Speakers', 'mage-eventpress' ); ?></span>
                                <div class="mpwem-speaker-select">
                                    <?php if ( ! empty( $all_speakers ) ) : ?>
                                        <div class="mpwem-speaker-select__grid">
                                            <?php foreach ( $all_speakers as $value ) : $checked = in_array( $value, $speaker_lists ); ?>
                                                <label class="mpwem-speaker-option<?php echo $checked ? ' is-selected' : ''; ?>">
                                                    <input type="checkbox" name="mep_event_speakers_list[]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $checked, true ); ?> />
                                                    <span class="mpwem-speaker-option__name"><?php echo esc_html( get_the_title( $value ) ); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <p class="mpwem-speaker-select__empty"><?php esc_html_e( 'No speakers found.', 'mage-eventpress' ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="info_text">
                                <?php esc_html_e( 'Tick a speaker to select/deselect. Add new speakers from ', 'mage-eventpress' ); ?>
                                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mep_event_speaker' ) ) ?>" target="_blank"><?php esc_html_e( 'here', 'mage-eventpress' ); ?></a>
                            </span>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Speaker_Settings();
	}