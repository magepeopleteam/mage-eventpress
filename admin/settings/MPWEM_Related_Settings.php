<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Related_Settings' ) ) {
		class MPWEM_Related_Settings {
			public function __construct() {
				add_action( 'after-single-events', [ $this, 'related_events' ] );
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
                <div class="mp_tab_item mep-related-events" data-tab-item="#mep_related_event_meta">
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

			public function related_events() {
				global $post;
				$product_ids          = get_post_meta( $post->ID, 'event_list', true ) ? get_post_meta( $post->ID, 'event_list', true ) : [];
				$section_label        = get_post_meta( $post->ID, 'related_section_label', true );
				$smart_theme          = get_post_meta( $post->ID, 'mep_event_template', true );
				$related_event_status = get_post_meta( $post->ID, 'mep_related_event_status', true );
				$related_event_status = $related_event_status ? $related_event_status : 'off';
				?>
				<?php if ( $related_event_status == 'on' ): ?>
                    <div class="<?php echo $smart_theme == 'smart.php' ? 'mep_smart_theme' : ''; ?>">
                        <div class="mep-related-events">
                            <div class="related-events-header mpwem_style">
                                <h2><?php echo $section_label; ?></h2>
                                <div class="related-events-navigation">
                                    <button class="mep-ev-prev"><i class="fas fa-chevron-left"></i></button>
                                    <button class="mep-ev-next"><i class="fas fa-chevron-right"></i></button>
                                </div>
                            </div>
                            <div class="mep-related-events-items">
								<?php
									$event_expire_on = mep_get_option( 'mep_event_expire_on_datetime', 'general_setting_sec', 'event_start_datetime' );
									$now             = current_time( 'Y-m-d H:i:s' );
									$args_search_qqq = array(
										'post_type'      => array( 'mep_events' ),
										'posts_per_page' => - 1,
										'post__in'       => $product_ids,
										'order'          => 'ASC',
										'orderby'        => 'meta_value',
										'meta_key'       => 'event_start_datetime',
										'meta_query'     => array(
											array(
												'key'     => $event_expire_on,
												'value'   => $now,
												'compare' => '>'
											)
										)
									);
									$loop            = new WP_Query( $args_search_qqq );
									if ( is_array( $product_ids ) && sizeof( $product_ids ) > 0 ) {
										while ( $loop->have_posts() ) {
											$loop->the_post();
											$values           = get_the_id();
											$event_meta       = get_post_custom( $values );
											$show_price       = mep_get_option( 'mep_event_price_show', 'general_setting_sec', 'yes' );
											$show_price_label = mep_get_option( 'event-price-label', 'general_setting_sec', 'Price Starts from' );
											?>
                                            <div class="item">
                                                <a href="<?php echo get_the_permalink( $values ); ?>">
                                                    <img src="<?php echo MPWEM_Global_Function::get_image_url( $values ); ?>" alt="">
													<?php if ( isset( $event_meta['mep_event_start_date'][0] ) ): ?>
                                                        <div class="mep-ev-start-date">
                                                            <div class="mep-day"><?php echo date_i18n( 'd', strtotime( $event_meta['mep_event_start_date'][0] ) ); ?></div>
                                                            <div class="mep-month"><?php echo date_i18n( 'M', strtotime( $event_meta['mep_event_start_date'][0] ) ); ?></div>
                                                        </div>
													<?php endif; ?>
                                                </a>
                                                <div class="item-info">
                                                    <div class="title">
                                                        <a href="<?php echo get_the_permalink( $values ); ?>">
                                                            <h2>
																<?php echo mb_substr( get_the_title(), 0, 35 ) . '...'; ?>
                                                            </h2>
                                                        </a>
														<?php
															$locations = MPWEM_Functions::get_location( $values );
															$data      = [];
															if ( ! empty( $locations ) ) {
																foreach ( $locations as $location ) {
																	$data[] = $location;
																}
																echo implode( ', ', $data );
															}
														?>
                                                    </div>
                                                    <div class="price">
                                                        <p><?php echo $show_price_label ?></p>
                                                        <h2 class='mep_list_date'>
															<?php if ( $show_price == 'yes' ) {
																echo wc_price( MPWEM_Functions::get_min_price( $values ) );
															} ?>
                                                        </h2>
                                                    </div>
                                                </div>
                                            </div>
											<?php
										}
										wp_reset_postdata();
									} ?>
                            </div>
                        </div>
                    </div>
                    <script>
                        (function ($) {
                            $(document).ready(function () {
                                $('.mep-related-events-items').slick({
                                    dots: true,
                                    arrows: true,
                                    prevArrow: '.mep-ev-prev',
                                    nextArrow: '.mep-ev-next',
                                    infinite: true,
                                    centerMode: false, // Make sure centerMode is false
                                    autoplay: true,
                                    autoplaySpeed: 2000,
                                    centerPadding: '0px',
                                    slidesToShow: 3,
                                    slidesToScroll: 1,
                                    responsive: [
                                        {
                                            breakpoint: 1024,
                                            settings: {
                                                slidesToShow: 2,
                                                slidesToScroll: 2,
                                                infinite: true,
                                                dots: true,
                                                centerMode: false // Ensure left alignment on responsive
                                            }
                                        },
                                        {
                                            breakpoint: 480,
                                            settings: {
                                                slidesToShow: 1,
                                                slidesToScroll: 1,
                                                centerMode: false // Ensure left alignment on mobile
                                            }
                                        }
                                    ]
                                });
                            });
                        })(jQuery);
                    </script>
				<?php endif; ?>
				<?php
			}
		}
		new MPWEM_Related_Settings();
	}