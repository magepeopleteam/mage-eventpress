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

			/**
			 * Build short initials for avatar fallback.
			 *
			 * @param string $name Speaker display name.
			 * @return string
			 */
			private function speaker_initials( $name ) {
				$name  = trim( wp_strip_all_tags( (string) $name ) );
				$parts = preg_split( '/\s+/', $name );
				$initials = '';
				if ( ! empty( $parts[0] ) ) {
					$initials .= mb_substr( $parts[0], 0, 1 );
				}
				if ( ! empty( $parts[1] ) ) {
					$initials .= mb_substr( $parts[1], 0, 1 );
				}
				if ( '' === $initials && '' !== $name ) {
					$initials = mb_substr( $name, 0, 1 );
				}

				return strtoupper( $initials );
			}

			public function speaker_tab_setting_item( $event_id, $event_infos ) {
				$speaker_title       = is_array( $event_infos ) && array_key_exists( 'mep_speaker_title', $event_infos ) ? $event_infos['mep_speaker_title'] : '';
				$speaker_icon        = is_array( $event_infos ) && array_key_exists( 'mep_event_speaker_icon', $event_infos ) ? $event_infos['mep_event_speaker_icon'] : '';
				$speaker_lists       = is_array( $event_infos ) && array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
				$speaker_lists       = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
				$speaker_lists       = array_map( 'strval', $speaker_lists );
				$general_setting_sec = is_array( $event_infos ) && array_key_exists( 'general_setting_sec', $event_infos ) ? $event_infos['general_setting_sec'] : [];
				$event_label         = is_array( $general_setting_sec ) && array_key_exists( 'mep_event_label', $general_setting_sec ) ? $general_setting_sec['mep_event_label'] : __( 'Events', 'mage-eventpress' );
				$all_speakers        = MPWEM_Query::get_all_post_ids( 'mep_event_speaker' );
				$speaker_enabled     = $event_id ? get_post_meta( $event_id, 'mep_event_enable_speaker', true ) : '';
				if ( '' === $speaker_enabled ) {
					$speaker_enabled = 'no';
				}
				$selected_count = 0;
				if ( ! empty( $all_speakers ) ) {
					foreach ( $all_speakers as $speaker_id ) {
						if ( in_array( (string) $speaker_id, $speaker_lists, true ) ) {
							$selected_count++;
						}
					}
				}
				$speaker_count = is_array( $all_speakers ) ? count( $all_speakers ) : 0;
				?>
                <div class="mpwem_style mp_tab_item mpwem_speaker_settings" data-tab-item="#mpwem_speaker_settings">
                    <div class="mpwem-speaker-panel">
                        <div class="mpwem-speaker-panel__intro">
                            <div class="mpwem-speaker-panel__intro-icon" aria-hidden="true">
                                <span class="dashicons dashicons-groups"></span>
                            </div>
                            <div class="mpwem-speaker-panel__intro-copy">
                                <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Speaker Settings', 'mage-eventpress' ); ?></h4>
                                <p><?php esc_html_e( 'Show featured speakers on the event page and choose who appears in the lineup.', 'mage-eventpress' ); ?></p>
                            </div>
                        </div>

                        <div class="mpwem-speaker-panel__enable">
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

                        <div class="mpwem-speaker-panel__fields _layout_default_xs_mp_zero" id="mpwem-speaker-fields" style="display:<?php echo esc_attr( $speaker_enabled === 'yes' ? 'block' : 'none' ); ?>">
                            <div class="mpwem-speaker-field">
                                <div class="mpwem-speaker-field__meta">
                                    <label class="mpwem-speaker-field__label" for="mep_speaker_title"><?php esc_html_e( 'Section Label', 'mage-eventpress' ); ?></label>
                                    <span class="info_text"><?php esc_html_e( 'Heading shown above the speaker list on the frontend. Default: Speakers.', 'mage-eventpress' ); ?></span>
                                </div>
                                <div class="mpwem-speaker-field__control">
                                    <input type="text" class="formControl" id="mep_speaker_title" name="mep_speaker_title" value="<?php echo esc_attr( $speaker_title ); ?>" placeholder="<?php esc_attr_e( 'Speakers', 'mage-eventpress' ); ?>"/>
                                </div>
                            </div>

                            <div class="mpwem-speaker-field">
                                <div class="mpwem-speaker-field__meta">
                                    <span class="mpwem-speaker-field__label"><?php esc_html_e( 'Section Icon', 'mage-eventpress' ); ?></span>
                                    <span class="info_text"><?php esc_html_e( 'Optional icon used next to the speaker section heading.', 'mage-eventpress' ); ?></span>
                                </div>
                                <div class="mpwem-speaker-field__control mpwem-speaker-field__control--icon">
									<?php do_action( 'mpwem_input_add_icon', 'mep_event_speaker_icon', $speaker_icon ); ?>
                                </div>
                            </div>

                            <div class="mpwem-speaker-field mpwem-speaker-field--select">
                                <div class="mpwem-speaker-field__meta">
                                    <span class="mpwem-speaker-field__label"><?php esc_html_e( 'Select Speakers', 'mage-eventpress' ); ?></span>
                                    <span class="info_text"><?php esc_html_e( 'Pick the speakers who should appear on this event page.', 'mage-eventpress' ); ?></span>
                                </div>
                                <div class="mpwem-speaker-select">
                                    <div class="mpwem-speaker-select__toolbar">
                                        <span
                                            class="mpwem-speaker-select__count"
                                            data-mpwem-speaker-count
                                            data-label-template="<?php echo esc_attr(
												/* translators: 1: selected speakers count, 2: total speakers */
												__( '%1$d of %2$d selected', 'mage-eventpress' )
											); ?>"
                                        >
											<?php
											echo esc_html(
												sprintf(
													/* translators: 1: selected speakers count, 2: total speakers */
													__( '%1$d of %2$d selected', 'mage-eventpress' ),
													$selected_count,
													$speaker_count
												)
											);
											?>
                                        </span>
                                        <a class="mpwem-speaker-select__add" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mep_event_speaker' ) ); ?>" target="_blank" rel="noopener noreferrer">
                                            <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
											<?php esc_html_e( 'Add speaker', 'mage-eventpress' ); ?>
                                        </a>
                                    </div>

									<?php if ( ! empty( $all_speakers ) ) : ?>
                                        <div class="mpwem-speaker-select__grid">
											<?php foreach ( $all_speakers as $value ) :
												$checked   = in_array( (string) $value, $speaker_lists, true );
												$name      = get_the_title( $value );
												$thumb_url = get_the_post_thumbnail_url( $value, 'thumbnail' );
												$initials  = $this->speaker_initials( $name );
												?>
                                                <label class="mpwem-speaker-card<?php echo $checked ? ' is-selected' : ''; ?>">
                                                    <input type="checkbox" name="mep_event_speakers_list[]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $checked, true ); ?> data-no-mpwem-switch="1" />
                                                    <span class="mpwem-speaker-card__avatar<?php echo $thumb_url ? ' has-image' : ''; ?>" aria-hidden="true">
														<?php if ( $thumb_url ) : ?>
                                                            <img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="lazy" />
														<?php else : ?>
                                                            <span class="mpwem-speaker-card__initials"><?php echo esc_html( $initials ); ?></span>
														<?php endif; ?>
                                                    </span>
                                                    <span class="mpwem-speaker-card__body">
                                                        <span class="mpwem-speaker-card__name"><?php echo esc_html( $name ); ?></span>
                                                    </span>
                                                    <span class="mpwem-speaker-card__check" aria-hidden="true"></span>
                                                </label>
											<?php endforeach; ?>
                                        </div>
									<?php else : ?>
                                        <div class="mpwem-speaker-select__empty">
                                            <span class="dashicons dashicons-groups" aria-hidden="true"></span>
                                            <p><?php esc_html_e( 'No speakers found yet.', 'mage-eventpress' ); ?></p>
                                            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mep_event_speaker' ) ); ?>" target="_blank" rel="noopener noreferrer">
												<?php esc_html_e( 'Create your first speaker', 'mage-eventpress' ); ?>
                                            </a>
                                        </div>
									<?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Speaker_Settings();
	}
