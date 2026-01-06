<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Related_Settings' ) ) {
		class MPWEM_Related_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', [ $this, 'event_related_content' ] );
			}
			public function event_related_content( $post_id ) {
				global $post;
				$args             = array(
					'post_type'      => array( 'mep_events' ),
					'posts_per_page' => - 1,
				);
				$loop             = new WP_Query( $args );
				$posts_array      = $loop->posts;
				$post_title_array = wp_list_pluck( $posts_array, 'post_title', 'ID' );
				if ( isset( $post_title_array[ get_the_ID() ] ) ) {
					unset( $post_title_array[ get_the_ID() ] );
				}
				$product_ids = get_post_meta( $post_id, 'event_list', true ) ? get_post_meta( $post_id, 'event_list', true ) : [];
				// $column_num    = get_post_meta( $post_id, '_list_column', true );
				$section_label = get_post_meta( $post_id, 'related_section_label', true );
				// $column_num = $column_num[0];
				$related_event_status = get_post_meta( $post_id, 'mep_related_event_status', true );
				$related_event_status = $related_event_status ? $related_event_status : 'off';
				?>
                <div class="mp_tab_item" data-tab-item="#mep_related_event_meta">
                    <h3><?php esc_html_e( 'Related Event Settings', 'mage-eventpress' ); ?></h3>
                    <p><?php esc_html_e( 'Related Event setup.', 'mage-eventpress' ); ?></p>
                    <section class="bg-light">
                        <h2><?php esc_html_e( 'Related Event', 'mage-eventpress' ); ?></h2>
                        <span><?php esc_html_e( 'Related Event', 'mage-eventpress' ); ?></span>
                    </section>
                    <section>
                        <div class="mpev-label">
                            <div>
                                <h2><?php esc_html_e( 'Show Related Events', 'mage-eventpress' ); ?></h2>
                                <span class="label-text"><?php esc_html_e( 'Show/hide releated events in frontend template', 'mage-eventpress' ); ?></span>
                            </div>
                            <label class="mpev-switch">
                                <input type="checkbox" name="mep_related_event_status" value="<?php echo esc_attr( $related_event_status ); ?>" <?php echo esc_attr( $related_event_status == 'on' ? 'checked' : '' ); ?> data-collapse-target="#mpev-related-event-display" data-toggle-values="on,off">
                                <span class="mpev-slider"></span>
                            </label>
                        </div>
                    </section>
                    <div id="mpev-related-event-display" style="display: <?php echo esc_html( $related_event_status == 'on' ? 'block' : 'none' ); ?>;">
                        <section>
                            <label class="mpev-label">
                                <div>
                                    <h2><?php esc_html_e( 'Related Events Section Label', 'mage-eventpress' ); ?></h2>
                                    <span class="label-text"><?php esc_html_e( 'Add a title above the releated events', 'mage-eventpress' ); ?></span>
                                </div>
                                <input type="text" max="4" min="2" name="related_section_label" class="related_section_label" id="related_section_label" value="<?php echo $section_label; ?>" placeholder="Label text">
                            </label>
                        </section>
                        <section>
                            <label class="mpev-label">
                                <div>
                                    <h2><?php esc_html_e( 'Event List', 'mage-eventpress' ); ?></h2>
                                    <span class="label-text"><?php esc_html_e( 'Event List', 'mage-eventpress' ); ?></span>
                                </div>
                                <div>
                                    <select class="chosen-select" multiple="multiple" id="upsizing_products"
                                            name="event_list[]"
                                            data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'mage-eventpress' ); ?>"
                                            data-action="woocommerce_json_search_products_and_variations"
                                            data-exclude="<?php echo intval( $post->ID ); ?>">
										<?php
											foreach ( $post_title_array as $product_id => $value ) : ?>
                                                <option value="<?php echo $product_id; ?>" <?php echo in_array( $product_id, $product_ids ) ? 'selected' : ''; ?> ><?php echo $value; ?></option>
											<?php endforeach; ?>
                                    </select>
									<?php echo wc_help_tip( __( 'Select Products Here.', 'mage-eventpress' ) ); ?>
                                </div>
                            </label>
                        </section>
                    </div>
                </div>
				<?php
			}
		}
		new MPWEM_Related_Settings();
	}