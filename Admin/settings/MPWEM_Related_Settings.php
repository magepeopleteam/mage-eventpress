<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Related_Settings')) {
		class MPWEM_Related_Settings {
			public function __construct() {
				add_action('mp_event_all_in_tab_menu', [$this, 'add_tab'], 90);
				add_action('mp_event_all_in_tab_item', [$this, 'related_settings']);
				add_action('mpwem_settings_save', [$this, 'save_related']);
			}
			public function add_tab() {
				?>
				<li data-target-tabs="#mpwem_related_tab">
					<i class="fas fa-map-marked-alt"></i><?php esc_html_e( 'Related Products', 'mage-eventpress' ); ?>
				</li>
				<?php
			}
			public function related_settings($post_id) {
				$display       = MP_Global_Function::get_post_info( $post_id, 'display_related', 'on' );
				$active        = $display == 'off' ? '' : 'mActive';
				$related_tours = MP_Global_Function::get_post_info( $post_id, 'related_event', array() );
				$all_tours     = MP_Global_Function::query_post_type( MPWEM_Functions::get_cpt() );
				$tours         = $all_tours->posts;
				$checked       = $display == 'off' ? '' : 'checked';
				?>
				<div class="mp_tab_item" data-tab-item="#mpwem_related_tab">
					<h2><?php esc_html_e( 'Related Event Settings', 'mage-eventpress' ); ?></h2>
					<section class="bg-light">
						<label for="" class="label">
							<div>
								<p><?php echo esc_html__( 'Related Event Settings', 'mage-eventpress' ); ?></p>
								<span class="text"><?php echo esc_html__( 'You can set related Event here. ', 'mage-eventpress' ); ?></span>
							</div>
						</label>
					</section>
					<section>
						<label class="label">
							<div>
								<p><?php echo esc_html__( 'Related Event Settings', 'mage-eventpress' ) ?></p>
							</div>
							<?php MP_Custom_Layout::switch_button( 'display_related', $checked ); ?>
						</label>
					</section>
					<div data-collapse="#mpwem_related_tab" class=" <?php echo esc_attr( $active ); ?>">
                        <label>
                            <span><?php esc_html_e( 'Related Event ', 'mage-eventpress' ); ?></span>
                            <select name="related_event[]" multiple='multiple' class='mp_select2' data-placeholder="<?php echo esc_html__( 'Please Select Event ', 'mage-eventpress' ); ?>">
								<?php
									foreach ( $tours as $tour ) {
										$ttbm_id = $tour->ID;
										?>
                                        <option value="<?php echo esc_attr( $ttbm_id ) ?>" <?php echo in_array( $ttbm_id, $related_tours ) ? 'selected' : ''; ?>><?php echo get_the_title( $ttbm_id ); ?></option>
									<?php } ?>
                            </select>
                        </label>
					</div>
				</div>
				<?php
				wp_reset_postdata();
			}
			public function save_related($post_id) {
				if (get_post_type($post_id) == 'mep_events') {
					$slider = MP_Global_Function::get_submit_info('display_related') ? 'on' : 'off';
					update_post_meta($post_id, 'display_related', $slider);
					$_event_list = MP_Global_Function::get_submit_info('related_event', array());
					update_post_meta($post_id, 'related_event', $_event_list);

				}
			}
		}
		new MPWEM_Related_Settings();
	}