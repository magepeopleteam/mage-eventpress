<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'MPWEM_Related_Settings' ) ) {
		class MPWEM_Related_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', [ $this, 'event_related_content' ] );
				add_action( 'wp_ajax_mpwem_search_related_events', [ $this, 'ajax_search_events' ] );
			}

			public function ajax_search_events() {
				check_ajax_referer( 'mpwem_admin_nonce', 'nonce' );
				$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
				$exclude = isset( $_POST['exclude'] ) ? array_map( 'intval', (array) $_POST['exclude'] ) : [];
				$exclude[] = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

			$args = [
				'post_type'      => 'mep_events',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'post__not_in'   => array_filter( $exclude ),
				'orderby'        => 'title',
				'order'          => 'ASC',
			];

			if ( ! empty( $search ) ) {
				$args['s'] = $search;
				$args['posts_per_page'] = 10;
			}

				$query   = new WP_Query( $args );
				$results = [];

				if ( $query->have_posts() ) {
					foreach ( $query->posts as $p ) {
						$event_date = get_post_meta( $p->ID, 'event_start_date', true );
						$results[] = [
							'id'    => $p->ID,
							'title' => $p->post_title,
							'date'  => $event_date ? date_i18n( get_option( 'date_format' ), strtotime( $event_date ) ) : '',
						];
					}
				}

				wp_send_json_success( $results );
			}

			public function event_related_content( $post_id ) {
				global $post;
				$section_label = get_post_meta( $post_id, 'related_section_label', true );
				$related_event_status = get_post_meta( $post_id, 'mep_related_event_status', true );
				$related_event_status = $related_event_status ? $related_event_status : 'off';
				$selected_ids = get_post_meta( $post_id, 'event_list', true ) ? get_post_meta( $post_id, 'event_list', true ) : [];
				$selected_events = [];
				if ( ! empty( $selected_ids ) ) {
					$selected_query = new WP_Query( [
						'post_type'      => 'mep_events',
						'posts_per_page' => -1,
						'post__in'       => $selected_ids,
						'orderby'        => 'post__in',
					] );
					foreach ( $selected_query->posts as $p ) {
						$selected_events[] = [
							'id'    => $p->ID,
							'title' => $p->post_title,
						];
					}
				}
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
                                <input type="text" max="4" min="2" name="related_section_label" class="related_section_label" id="related_section_label" value="<?php echo esc_attr( $section_label ); ?>" placeholder="Label text">
                            </label>
                        </section>
                        <section class="mpwem-related-search-section">
                            <label class="mpev-label">
                                <div>
                                    <h2><?php esc_html_e( 'Event List', 'mage-eventpress' ); ?></h2>
                                    <span class="label-text"><?php esc_html_e( 'Search and select related events', 'mage-eventpress' ); ?></span>
                                </div>
                                <div class="mpwem-related-search-wrap">
                                    <div class="mpwem-related-search-input-wrap">
                                        <span class="mpwem-related-search-icon dashicons dashicons-search"></span>
                                        <input type="text" class="mpwem-related-search-input" placeholder="<?php esc_attr_e( 'Search events...', 'mage-eventpress' ); ?>" autocomplete="off">
                                        <span class="mpwem-related-search-spinner"></span>
                                    </div>
                                    <div class="mpwem-related-pills" data-empty-msg="<?php esc_attr_e( 'No related events selected.', 'mage-eventpress' ); ?>">
                                        <?php foreach ( $selected_events as $ev ) : ?>
                                            <span class="mpwem-related-pill" data-id="<?php echo intval( $ev['id'] ); ?>">
                                                <span class="mpwem-related-pill__title"><?php echo esc_html( $ev['title'] ); ?></span>
                                                <button type="button" class="mpwem-related-pill__remove" title="<?php esc_attr_e( 'Remove', 'mage-eventpress' ); ?>" aria-label="<?php esc_attr_e( 'Remove', 'mage-eventpress' ); ?>">&times;</button>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mpwem-related-results" style="display:none;"></div>
                                    <div class="mpwem-related-empty-state" style="display:none;">
                                        <span class="dashicons dashicons-search"></span>
                                        <p><?php esc_html_e( 'No events found. Try a different search term.', 'mage-eventpress' ); ?></p>
                                    </div>
                                    <input type="hidden" name="event_list[]" class="mpwem-related-ids" value="">
                                    <?php foreach ( $selected_ids as $sid ) : ?>
                                        <input type="hidden" name="event_list[]" class="mpwem-related-ids" value="<?php echo intval( $sid ); ?>">
                                    <?php endforeach; ?>
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