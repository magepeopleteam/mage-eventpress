<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Settings_Gallery')) {
		class MPWEM_Settings_Gallery {
			public function __construct() {
				add_action('mp_event_all_in_tab_menu', [$this, 'add_tab'], 90);
				add_action('mp_event_all_in_tab_item', [$this, 'gallery_settings']);
				//add_action('mpwem_settings_save', [$this, 'save_gallery']);
				add_action('mpwem_settings_save', [$this, 'save_gallery']);
			}
			public function add_tab() {
				?>
				<li  data-target-tabs="#ttbm_settings_gallery">
					<i class="fas fa-images"></i><?php esc_html_e('Gallery ', 'mage-eventpress'); ?>
				</li>
				<?php
			}
			public function gallery_settings($tour_id) {
				$display = MP_Global_Function::get_post_info($tour_id, 'mep_display_slider', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				$image_ids = MP_Global_Function::get_post_info($tour_id, 'mep_gallery_images', array());
				?>
				
				<div class="mp_tab_item mpStyle" data-tab-item="#ttbm_settings_gallery">
					<h3><?php esc_html_e('Gallery Settings', 'mage-eventpress'); ?></h3>
					<p ><?php MPWEM_Settings::des_p('gallery_settings_description'); ?></p>
					<section class="bg-light">
						<label class="label">
							<div>
								<h2><?php esc_html_e('Gallery Settings', 'mage-eventpress'); ?></h2>
								<span class="text"><?php esc_html_e('Here you can add images for event.', 'mage-eventpress'); ?></span>
							</div>
						</label>
                    </section>
					<section>
                        <label class="label">
                            <div>
								<p><?php esc_html_e('On/Off Slider', 'mage-eventpress'); ?></p>
								<span class="text"><?php MPWEM_Settings::des_p('mep_display_slider'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('mep_display_slider', $checked); ?>
                        </label>
						
                    </section>
					<div data-collapse="#mep_display_slider" class="<?php echo esc_attr($active); ?>">
						
						<section>
							<div >
								<label class="label"><p><?php esc_html_e('Gallery Images ', 'mage-eventpress'); ?></p></label>
								<?php echo esc_html__('Please upload gallary images size in ratio 4:3. Ex: Image size width=1200px and height=900px. gallery and feature image should be in same size.','mage-eventpress'); ?>
								<div class="mt-5"></div>
								<?php MP_Custom_Layout::add_multi_image('mep_gallery_images', $image_ids); ?>
							</div>
						</section>
						
					</div>
					<section class="bg-light " style="margin-top: 20px;">
						<label class="label">
							<div>
								<h2><?php esc_html_e('Event List Thumbnail', 'mage-eventpress'); ?></h2>
								<span class="text"><?php esc_html_e('Here you can add thumbnail for event.', 'mage-eventpress'); ?></span>
							</div>
						</label>
                    </section>
					<section class="mpStyle">
					
							<h2><span><?php esc_html_e('Thumbnail', 'mage-eventpress'); ?></span></h2>
							<?php echo esc_html__('Add thumbnail for your event','mage-eventpress'); ?>
							<?php 
							
							$image_id = get_post_meta($tour_id,'mep_list_thumbnail',true);
							do_action('mp_add_single_image','mep_list_thumbnail',$image_id);
							?>
						
					</section>
				</div>
				<?php
			}
			public function save_gallery($post_id) {
				if (get_post_type($post_id) == 'mep_events') {
					$slider = MP_Global_Function::get_submit_info('mep_display_slider') ? 'on' : 'off';
					update_post_meta($post_id, 'mep_display_slider', $slider);
					$images = MP_Global_Function::get_submit_info('mep_gallery_images', array());
					$all_images = explode(',', $images);
					update_post_meta($post_id, 'mep_gallery_images', $all_images);

				}
			}
		}
		new MPWEM_Settings_Gallery();
	}