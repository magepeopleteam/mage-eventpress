<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Settings_Gallery' ) ) {
		class MPWEM_Settings_Gallery {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', [ $this, 'gallery_settings' ] );
			}

			public function gallery_settings( $tour_id ) {
				$display_gallary = MPWEM_Global_Function::get_post_info( $tour_id, 'mep_display_slider', 'on' );
				$image_ids       = MPWEM_Global_Function::get_post_info( $tour_id, 'mep_gallery_images', array() );
				?>
                <div class="mp_tab_item" data-tab-item="#ttbm_settings_gallery">
                    <h3><?php esc_html_e( 'Gallery Settings', 'mage-eventpress' ); ?></h3>
                    <p><?php MPWEM_Settings::des_p( 'gallery_settings_description' ); ?></p>
                    <section class="bg-light">
                        <div class="mpev-label">
                            <div>
                                <h2><?php esc_html_e( 'Gallery Settings', 'mage-eventpress' ); ?></h2>
                                <span class="text"><?php esc_html_e( 'Here you can add images for event.', 'mage-eventpress' ); ?></span>
                            </div>
                        </div>
                    </section>
                    <section>
                        <div class="mpev-label">
                            <div>
                                <h2><?php esc_html_e( 'On/Off Slider', 'mage-eventpress' ); ?></h2>
                                <span class="label-text"><?php MPWEM_Settings::des_p( 'mep_display_slider' ); ?></span>
                            </div>
                            <label class="mpev-switch">
                                <input type="checkbox" name="mep_display_slider" value="<?php echo esc_attr( $display_gallary ); ?>" <?php echo $display_gallary == 'on' ? 'checked' : ''; ?> data-collapse-target="#mep_display_slider" data-toggle-values="on,off">
                                <span class="mpev-slider"></span>
                            </label>
                        </div>
                    </section>
                    <div id="mep_display_slider" class="mpwem_style" style="display: <?php echo esc_attr( $display_gallary == 'on' ? 'block' : 'none' ); ?>;">
                        <section>
                            <div class="mpev-label">
                                <h2><?php esc_html_e( 'Gallery Images ', 'mage-eventpress' ); ?></h2>
                            </div>
                            <span class="label-text"><?php echo esc_html__( 'Please upload gallary images size in ratio 4:3. Ex: Image size width=1200px and height=900px. gallery and feature image should be in same size.', 'mage-eventpress' ); ?></span>
                            <div style="margin-top: 20px;">
								<?php MPWEM_Custom_Layout::add_multi_image( 'mep_gallery_images', $image_ids ); ?>
                            </div>
                        </section>
                    </div>
                    <section class="bg-light " style="margin-top: 20px;">
                        <div class="mpev-label">
                            <div>
                                <h2><?php esc_html_e( 'Event List Thumbnail', 'mage-eventpress' ); ?></h2>
                                <span><?php esc_html_e( 'Here you can add thumbnail for event.', 'mage-eventpress' ); ?></span>
                            </div>
                        </div>
                    </section>
                    <div class="mpwem_style">
                        <section>
                            <h2><?php esc_html_e( 'Thumbnail', 'mage-eventpress' ); ?></h2>
                            <span class="label-text"><?php esc_html_e( 'Add thumbnail for your event lists', 'mage-eventpress' ); ?></span>
                            <div style="margin-top: 20px;">
								<?php
									$image_id = get_post_meta( $tour_id, 'mep_list_thumbnail', true );
									do_action( 'mpwem_add_single_image', 'mep_list_thumbnail', $image_id );
								?>
                            </div>
                        </section>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Settings_Gallery();
	}