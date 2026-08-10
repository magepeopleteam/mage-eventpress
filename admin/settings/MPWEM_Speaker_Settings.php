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
				$speaker_title       = is_array( $event_infos ) && array_key_exists( 'mep_speaker_title', $event_infos ) ? $event_infos['mep_speaker_title'] : '';
				$speaker_icon        = is_array( $event_infos ) && array_key_exists( 'mep_event_speaker_icon', $event_infos ) ? $event_infos['mep_event_speaker_icon'] : '';
				$speaker_lists       = is_array( $event_infos ) && array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
				$speaker_lists       = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
				$speaker_lists       = array_filter( array_map( 'absint', (array) $speaker_lists ) );
				$general_setting_sec = is_array( $event_infos ) && array_key_exists( 'general_setting_sec', $event_infos ) ? $event_infos['general_setting_sec'] : [];
				$event_label         = is_array( $general_setting_sec ) && array_key_exists( 'mep_event_label', $general_setting_sec ) ? $general_setting_sec['mep_event_label'] : __( 'Events', 'mage-eventpress' );
				$all_speakers        = MPWEM_Query::get_all_post_ids( 'mep_event_speaker' );
				$speaker_enabled     = $event_id ? get_post_meta( $event_id, 'mep_event_enable_speaker', true ) : '';
				if ( '' === $speaker_enabled ) {
					$speaker_enabled = 'no';
				}
				$selected_count = count( array_intersect( $all_speakers, $speaker_lists ) );
				$manage_url     = admin_url( 'edit.php?post_type=mep_event_speaker' );
				?>
                <div class="mpwem_style mp_tab_item mpwem_speaker_settings" data-tab-item="#mpwem_speaker_settings">
                    <div class="mpwem-speaker-panel__intro _bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Speaker Settings', 'mage-eventpress' ); ?></h4>
                        <p><?php esc_html_e( 'Choose who appears on this event page and how the speakers section is labeled.', 'mage-eventpress' ); ?></p>
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

                    <div class="mpwem-speaker-panel" id="mpwem-speaker-fields" style="display:<?php echo esc_attr( $speaker_enabled === 'yes' ? 'block' : 'none' ); ?>">
                        <div class="mpwem-speaker-display" data-mpwem-speaker-display="1">
                            <div class="mpwem-speaker-display__head">
                                <span class="mpwem-speaker-display__badge" aria-hidden="true">
                                    <span class="dashicons dashicons-art"></span>
                                </span>
                                <div class="mpwem-speaker-display__head-copy">
                                    <h3 class="mpwem-speaker-display__title"><?php esc_html_e( 'Section Display', 'mage-eventpress' ); ?></h3>
                                    <p class="mpwem-speaker-display__subtitle"><?php esc_html_e( 'Choose the heading and icon shown above speakers on the event page.', 'mage-eventpress' ); ?></p>
                                </div>
                            </div>
                            <div class="mpwem-speaker-display__body">
                                <div class="mpwem-speaker-display__row mpwem-speaker-display__row--label">
                                    <div class="mpwem-speaker-display__meta">
                                        <label class="mpwem-speaker-display__field-label" for="mep_speaker_title">
                                            <?php esc_html_e( 'Section Label', 'mage-eventpress' ); ?>
                                        </label>
                                        <span class="mpwem-speaker-display__hint"><?php esc_html_e( 'Heading shown above the speaker list.', 'mage-eventpress' ); ?></span>
                                    </div>
                                    <div class="mpwem-speaker-display__control">
                                        <div class="mpwem-speaker-display__input-wrap">
                                            <span class="dashicons dashicons-editor-textcolor" aria-hidden="true"></span>
                                            <input type="text" class="formControl mpwem-speaker-display__input" id="mep_speaker_title" name="mep_speaker_title" value="<?php echo esc_attr( $speaker_title ); ?>" placeholder="<?php esc_attr_e( 'Speakers', 'mage-eventpress' ); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="mpwem-speaker-display__row mpwem-speaker-display__row--icon">
                                    <div class="mpwem-speaker-display__meta">
                                        <span class="mpwem-speaker-display__field-label"><?php esc_html_e( 'Section Icon', 'mage-eventpress' ); ?></span>
                                        <span class="mpwem-speaker-display__hint"><?php esc_html_e( 'Optional icon next to the section heading.', 'mage-eventpress' ); ?></span>
                                    </div>
                                    <div class="mpwem-speaker-display__control mpwem-speaker-display__icon-picker">
										<?php do_action( 'mpwem_input_add_icon', 'mep_event_speaker_icon', $speaker_icon ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mpwem-speaker-picker">
                            <div class="mpwem-speaker-picker__toolbar">
                                <div class="mpwem-speaker-picker__title-wrap">
                                    <h3 class="mpwem-speaker-picker__title"><?php esc_html_e( 'Select Speakers', 'mage-eventpress' ); ?></h3>
                                    <span class="mpwem-speaker-picker__count" data-speaker-count><?php
										echo esc_html(
											sprintf(
												/* translators: %d: number of selected speakers */
												_n( '%d selected', '%d selected', $selected_count, 'mage-eventpress' ),
												$selected_count
											)
										);
									?></span>
                                </div>
                                <div class="mpwem-speaker-picker__actions">
                                    <a class="mpwem-speaker-picker__manage" href="<?php echo esc_url( $manage_url ); ?>" target="_blank" rel="noopener noreferrer">
                                        <span class="dashicons dashicons-groups" aria-hidden="true"></span>
										<?php esc_html_e( 'Manage', 'mage-eventpress' ); ?>
                                    </a>
                                    <button type="button" class="mpwem-speaker-picker__add" data-mpwem-speaker-add>
                                        <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
										<?php esc_html_e( 'Add Speaker', 'mage-eventpress' ); ?>
                                    </button>
                                </div>
                            </div>

							<?php if ( ! empty( $all_speakers ) ) : ?>
                                <div class="mpwem-speaker-select">
                                    <div class="mpwem-speaker-select__grid">
										<?php foreach ( $all_speakers as $value ) :
											$value   = absint( $value );
											$checked = in_array( $value, $speaker_lists, true );
											$title   = get_the_title( $value );
											$post    = get_post( $value );
											$role    = $post ? trim( (string) $post->post_excerpt ) : '';
											if ( ! $role && $post ) {
												$role = wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 8, '…' );
											}
											$thumb_url = get_the_post_thumbnail_url( $value, [ 80, 80 ] );
											$initial   = function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $title, 0, 1 ) ) : strtoupper( substr( $title, 0, 1 ) );
											?>
                                            <label class="mpwem-speaker-option<?php echo $checked ? ' is-selected' : ''; ?>">
                                                <input type="checkbox" name="mep_event_speakers_list[]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $checked, true ); ?> data-no-mpwem-switch="1" />
                                                <span class="mpwem-speaker-option__avatar<?php echo $thumb_url ? ' has-image' : ''; ?>" aria-hidden="true">
													<?php if ( $thumb_url ) : ?>
                                                        <img src="<?php echo esc_url( $thumb_url ); ?>" alt="" />
													<?php else : ?>
                                                        <span class="mpwem-speaker-option__initial"><?php echo esc_html( $initial ? $initial : '?' ); ?></span>
													<?php endif; ?>
                                                    <span class="mpwem-speaker-option__check"><span class="dashicons dashicons-yes"></span></span>
                                                </span>
                                                <span class="mpwem-speaker-option__meta">
                                                    <span class="mpwem-speaker-option__name"><?php echo esc_html( $title ); ?></span>
													<?php if ( $role ) : ?>
                                                        <span class="mpwem-speaker-option__role"><?php echo esc_html( $role ); ?></span>
													<?php endif; ?>
                                                </span>
                                            </label>
										<?php endforeach; ?>
                                    </div>
                                </div>
							<?php else : ?>
                                <div class="mpwem-speaker-select__empty-state">
                                    <span class="dashicons dashicons-microphone" aria-hidden="true"></span>
                                    <p><?php esc_html_e( 'No speakers yet. Create speaker profiles, then assign them here.', 'mage-eventpress' ); ?></p>
                                    <button type="button" class="button button-primary" data-mpwem-speaker-add>
										<?php esc_html_e( 'Create First Speaker', 'mage-eventpress' ); ?>
                                    </button>
                                </div>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Speaker_Settings();
	}
