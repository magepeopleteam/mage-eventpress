<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Related_Settings')) {
		class MPWEM_Related_Settings {
			public function __construct() {
				add_action( 'woocommerce_product_options_related', [$this,'woocom_linked_products_data_custom_field'] );
				add_action( 'woocommerce_process_product_meta', [$this,'woocom_linked_products_data_custom_field_save'] );
				add_action( 'woocommerce_after_single_product', [$this,'related_single_products'] );
				add_action( 'after-single-events', [$this,'related_events'] );
				add_action('wp_enqueue_scripts', [$this,'enqueue_slick_carousel']);

				add_action('mep_admin_event_details_before_tab_name_rich_text',[$this,'event_related_tab']);
				add_action('mp_event_all_in_tab_item',[$this,'event_related_content']);
				add_action( 'save_post', [$this,'mep_event_related_products_data_save'] );
			}

			public function enqueue_slick_carousel() {
				wp_enqueue_style('slick-carousel', 'https://cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick.css', array(), '1.8.1');
				wp_enqueue_style('slick-carousel-theme', 'https://cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick-theme.css', array('slick-carousel'), '1.8.1'); 
				wp_enqueue_script('slick-carousel', 'https://cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', false);
			}

			public function event_related_tab() {
				?>
					<li data-target-tabs="#mep_related_event_meta">
						<i class="fas fa-plug"></i><?php esc_html_e('Related Events', 'mage-eventpress'); ?>
					</li>
				<?php
			}

		
			public function mep_event_related_products_data_save( $post_id ) {
				global $wpdb;
				if ( get_post_type( $post_id ) == 'mep_events' ) {
					$event_list    = isset( $_POST['event_list'] ) ? $_POST['event_list'] : array();
					$column_number = isset( $_POST['event_list_column'] ) ? $_POST['event_list_column'] : '';
					$section_label = isset( $_POST['related_section_label'] ) ? $_POST['related_section_label'] : '';
					$event_status = isset( $_POST['mep_related_event_status'] ) ? $_POST['mep_related_event_status'] : 'off';
					update_post_meta( $post_id, '_list_column', $column_number );
					update_post_meta( $post_id, 'event_list', $event_list );
					update_post_meta( $post_id, 'related_section_label', $section_label );
					update_post_meta( $post_id, 'mep_related_event_status', $event_status );
				}
			}
		
			public function event_related_content($post_id) {
				global $woocommerce, $post;
				$args             = array(
					'post_type'      => array( 'mep_events' ),
					'posts_per_page' => - 1,
				);
				$loop             = new WP_Query( $args );
				$posts_array      = $loop->posts;
				$post_title_array = wp_list_pluck( $posts_array, 'post_title', 'ID' );
				if (isset($post_title_array[get_the_ID()])){
					unset($post_title_array[get_the_ID()]);
				}
				$product_ids   = get_post_meta( $post_id, 'event_list', true ) ? get_post_meta( $post_id, 'event_list', true ) : [];
				// $column_num    = get_post_meta( $post_id, '_list_column', true );
				$section_label = get_post_meta( $post_id, 'related_section_label', true );
				// $column_num = $column_num[0];
				$related_event_status = get_post_meta($post_id,'mep_related_event_status',true);
				$related_event_status = $related_event_status?$related_event_status:'off';
				?>
				<div class="mp_tab_item mep-related-events" data-tab-item="#mep_related_event_meta">
					<h3><?php esc_html_e('Related Event Settings', 'mage-eventpress'); ?></h3>
					<p><?php esc_html_e('Related Event setup.', 'mage-eventpress'); ?></p>
					
					<section class="bg-light">
						<h2><?php esc_html_e('Related Event', 'mage-eventpress'); ?></h2>
						<span><?php esc_html_e('Related Event', 'mage-eventpress'); ?></span>
					</section>

					<section>
						<div class="mpev-label">
							<div>
								<h2><span><?php esc_html_e('Show Related Events', 'mage-eventpress'); ?></span></h2>
								<span><?php esc_html_e('Show/hide releated events in frontend template', 'mage-eventpress'); ?></span>
							</div>
							<label class="mpev-switch">
								<input type="checkbox" name="mep_related_event_status" value="<?php echo esc_attr($related_event_status); ?>" <?php echo esc_attr($related_event_status=='on'?'checked':''); ?> data-collapse-target="#mpev-related-event-display" data-toggle-values="on,off">
								<span class="mpev-slider"></span>
							</label>
						</div>
					</section>
					<div id="mpev-related-event-display" style="display: <?php echo esc_html($related_event_status=='on'?'block':'none'); ?>;">
						<section>
							<label class="mpev-label">
								<div>
									<h2><span><?php esc_html_e('Related Events Section Label', 'mage-eventpress'); ?></span></h2>
									<span><?php esc_html_e('Add a title above the releated events', 'mage-eventpress'); ?></span>
								</div>
								<input type="text" max="4" min="2" name="related_section_label" class="related_section_label"
									id="related_section_label" value="<?php echo $section_label; ?>" placeholder="Label text">
							</label>
						</section>
			
						<section>
							<label class="mpev-label">
								<div>
									<h2><span><?php esc_html_e('Event List', 'mage-eventpress'); ?></span></h2>
									<span><?php esc_html_e('Event List', 'mage-eventpress'); ?></span>
								</div>
								
								<div>
									<select class="chosen-select" multiple="multiple" id="upsizing_products"
											name="event_list[]"
											data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
											data-action="woocommerce_json_search_products_and_variations"
											data-exclude="<?php echo intval( $post->ID ); ?>">
											
										<?php
				
										foreach ( $post_title_array as $product_id => $value ) : ?>
											<option value="<?php echo $product_id; ?>" <?php echo in_array($product_id, $product_ids)?'selected':''; ?> ><?php echo $value; ?></option>
										<?php endforeach; ?>
									</select> 
									<?php echo wc_help_tip( __( 'Select Products Here.', 'woocommerce' ) ); ?>
								</div>
							</label>
						</section>
					</div>
				</div>
		
				<?php
			}
		
			public function woocom_linked_products_data_custom_field() {
				global $woocommerce, $post;
				?>
				<!-- <p class="form-field">
					<label for="upsizing_products"><?php _e( 'Event List', 'woocommerce' ); ?></label>
					<select class="chosen-select" multiple="multiple" style="width: 50%;" id="upsizing_products"
							name="upsizing_products[]"
							data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
							data-action="woocommerce_json_search_products_and_variations"
							data-exclude="<?php echo intval( $post->ID ); ?>">
						<?php
						$args             = array(
							'post_type'      => array( 'mep_events' ),
							'posts_per_page' => - 1,
						);
						$loop             = new WP_Query( $args );
						$posts_array      = $loop->posts;
						$post_title_array = wp_list_pluck( $posts_array, 'post_title', 'ID' );
		
						$product_ids = get_post_meta( $post->ID, '_upsizing_products_ids', true ) ? get_post_meta( $post->ID, '_upsizing_products_ids', true ) : [];
		
		
						foreach ( $post_title_array as $product_id => $value ) {
							if ( in_array( $product_id, $product_ids ) ) {
								$selected = 'selected';
							} else {
								$selected = '';
							}
							echo '<option value="' . esc_attr( $product_id ) . '"' . $selected . '>' .
								 $value
								 . '</option>';
						}
						?>
					</select> <?php echo wc_help_tip( __( 'Select Products Here.', 'woocommerce' ) ); ?>
				</p> -->
		
				<?php
			}
		
			public function woocom_linked_products_data_custom_field_save( $post_id ) {
				$event_list = $_POST['upsizing_products'];
				update_post_meta( $post_id, '_upsizing_products_ids', $event_list );
			}
		
			public function related_events_after_single() {
		
				global $woocommerce, $post;
				$product_ids   = get_post_meta( $post->ID, 'event_list', true ) ? get_post_meta( $post->ID, 'event_list', true ) : [];
				$section_label = get_post_meta( $post->ID, 'related_section_label', true );
				$column_num    = get_post_meta( $post->ID, '_list_column', true );
				if ( $column_num == 3 ) {
					$columnNumber = 'three_column';
				} elseif ( $column_num == 4 ) {
					$columnNumber = 'four_column';
				} else {
					$columnNumber = 'two_column';
				}
		
				$style = 'grid'; ?>
				<div class="section-heading">
					<h2><?php echo $section_label; ?></h2>
				</div>
				<div class="related_events">
					<?php
						$event_expire_on 			= mep_get_option( 'mep_event_expire_on_datetime', 'general_setting_sec', 'event_start_datetime');
						$now                        = current_time('Y-m-d H:i:s');
						$args_search_qqq = array (
								'post_type'            => array( 'mep_events' ),
								'posts_per_page'       => -1,
								'post__in'             => $product_ids,
								'order'                => 'ASC',
								'orderby'              => 'meta_value',
								'meta_key'             => 'event_start_datetime',
								'meta_query'           => array(
								array(
										'key'       => $event_expire_on,
										'value'     => $now,
										'compare'   => '>'
									)
								)
		
						);
						$loop = new WP_Query( $args_search_qqq );
	
					if ( is_array( $product_ids ) && sizeof($product_ids) > 0 ) {
						while ($loop->have_posts()) {
						$loop->the_post(); 
						$values = get_the_id();
							$event_meta         = get_post_custom( $values );
							$tt                 = get_the_terms( $values, 'mep_cat' );
							$org_class          = mep_get_term_as_class( $values, 'mep_org' );
							$torg               = get_the_terms( $values, 'mep_org' );
							$cat_class          = mep_get_term_as_class( $values, 'mep_cat' );
							$available_seat     = mep_get_total_available_seat( $values, $event_meta );
							$show_price         = mep_get_option( 'mep_event_price_show', 'general_setting_sec', 'yes' );
							$show_price_label   = mep_get_option( 'event-price-label', 'general_setting_sec', 'Price Starts from:' );
							$author_terms       = get_the_terms( $values, 'mep_org' );
							?>
							<div class='related_items <?php echo $columnNumber . ' '; ?>mep_event_<?php echo $style; ?>_item mix <?php if ( $tt ) {
								echo 'mage-' . $org_class;
							} ?> <?php if ( $torg ) {
								echo 'mage-' . $cat_class;
							} ?>'>
								<div class="mep_list_thumb">
									<a href="<?php echo get_the_permalink( $values ); ?>"><?php echo get_the_post_thumbnail( $values ); ?></a>
									<?php if(isset($event_meta['mep_event_start_date'][0])): ?>
									<div class="mep-ev-start-date">
										<div class="mep-day"><?php echo date_i18n( 'd', strtotime( $event_meta['mep_event_start_date'][0] ) ); ?></div>
										<div class="mep-month"><?php echo date_i18n( 'M', strtotime( $event_meta['mep_event_start_date'][0] ) ); ?></div>
									</div>
									<?php endif; ?>
								</div>
								<div class="mep_list_event_details"><a
											href="<?php echo get_the_permalink( $values ); ?>">
										<div class="mep-list-header">
											<h2 class='mep_list_title'><?php echo get_the_title( $values ); ?></h2>
											<?php if ( $available_seat == 0 ) {
												do_action( 'mep_show_waitlist_label' );
											} ?>
											<h3 class='mep_list_date'> <?php if ( $show_price == 'yes' ) {
													echo $show_price_label . " " . mep_event_list_price( $values );
												} ?><!-- <i class="far fa-calendar-alt"></i> <?php echo date_i18n( 'h:i A', strtotime( $event_meta['mep_event_start_date'][0] ) ); ?> - <?php echo $event_meta['mep_event_end_date'][0]; ?> --></h3>
										</div>
										<?php
										if ( $style == 'grid' ) {
											?>
											<div class="mep-event-excerpt">
												<?php get_the_excerpt( $values ); ?>
											</div>
										<?php } ?>
										<div class="mep-list-footer">
											<ul>
												<?php
	
												$mep_hide_org_list      = mep_get_option( 'mep_event_hide_organizer_list', 'general_setting_sec' );
												$mep_hide_location_list = mep_get_option( 'mep_event_hide_location_list', 'general_setting_sec' );
												$mep_hide_time_list     = mep_get_option( 'mep_event_hide_time_list', 'general_setting_sec' );
												$mep_hide_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'general_setting_sec' );
	
	
												?>
												<?php if ( $mep_hide_org_list != 'yes' ) { ?>
													<li>
														<div class="evl-ico"><i class="fa fa-university"></i></div>
														<div class="evl-cc">
															<h5>
																<?php echo mep_get_option( 'mep_organized_by_text', 'label_setting_sec' ) ? mep_get_option( 'mep_organized_by_text', 'label_setting_sec' ) : _e( 'Organized By:', 'mage-eventpress' ); ?>
															</h5>
															<h6><?php if ( $author_terms ) {
																	echo $author_terms[0]->name;
																} ?></h6>
														</div>
													</li>
												<?php } ?>
												<?php if ( $mep_hide_location_list != 'yes' ) { ?>
													<li>
														<div class="evl-ico"><i class="fa fa-location-arrow"></i></div>
														<div class="evl-cc">
															<h5>
																<?php echo mep_get_option( 'mep_location_text', 'label_setting_sec' ) ? mep_get_option( 'mep_location_text', 'label_setting_sec' ) : _e( 'Location:', 'mage-eventpress' ); ?>
	
															</h5>
															<h6><?php mep_get_event_city( $values ); ?></h6>
														</div>
													</li>
												<?php } ?>
												<?php if ( $mep_hide_time_list != 'yes' ) { ?>
													<li>
														<div class="evl-ico"><i class="far fa-calendar-alt"></i></div>
														<div class="evl-cc">
															<h5>
																<?php echo mep_get_option( 'mep_time_text', 'label_setting_sec' ) ? mep_get_option( 'mep_time_text', 'label_setting_sec' ) : _e( 'Time:', 'mage-eventpress' ); ?>
															</h5>
															<h6><?php mep_get_only_time( $event_meta['mep_event_start_date'][0] ); ?>
																<?php if ( $mep_hide_end_time_list != 'yes' ) { ?>
																	- <?php mep_get_only_time( $event_meta['mep_event_end_date'][0] ); ?>
																<?php } ?>
															</h6>
														</div>
													</li>
												<?php } ?>
											</ul>
										</div>
									</a>
								</div>
							</div>
							<?php
						}
						wp_reset_postdata();
					} ?>
					</div>
				<?php
		
			}
			public function related_events(){
				global $woocommerce, $post;
				$product_ids   = get_post_meta( $post->ID, 'event_list', true ) ? get_post_meta( $post->ID, 'event_list', true ) : [];
				$section_label = get_post_meta( $post->ID, 'related_section_label', true );
				$column_num    = get_post_meta( $post->ID, '_list_column', true );
				$smart_theme    = get_post_meta( $post->ID, 'mep_event_template', true );
				$related_event_status = get_post_meta( $post->ID, 'mep_related_event_status', true );
				$related_event_status= $related_event_status ? $related_event_status:'off';
				?>
				<?php if($related_event_status=='on'): ?>
				<div class="<?php echo $smart_theme=='smart.php'?'mep_smart_theme':''; ?>">
					<div class="mep-related-events">
						<div class="related-events-header mpStyle">
							<h2><?php echo $section_label; ?></h2>
							<div class="related-events-navigation">
								<button class="mep-ev-prev"><i class="fas fa-chevron-left"></i></button>
								<button class="mep-ev-next"><i class="fas fa-chevron-right"></i></button>
							</div>
						</div>
						<div class="mep-related-events-items">
							<?php
								$event_expire_on 			= mep_get_option( 'mep_event_expire_on_datetime', 'general_setting_sec', 'event_start_datetime');
								$now                        = current_time('Y-m-d H:i:s');
								$args_search_qqq = array (
										'post_type'            => array( 'mep_events' ),
										'posts_per_page'       => -1,
										'post__in'             => $product_ids,
										'order'                => 'ASC',
										'orderby'              => 'meta_value',
										'meta_key'             => 'event_start_datetime',
										'meta_query'           => array(
										array(
												'key'       => $event_expire_on,
												'value'     => $now,
												'compare'   => '>'
											)
										)

								);
								$loop = new WP_Query( $args_search_qqq );

							if ( is_array( $product_ids ) && sizeof($product_ids) > 0 ) {
								while ($loop->have_posts()) {
								$loop->the_post(); 
								$values = get_the_id();
								$event_meta         = get_post_custom( $values );
								$tt                 = get_the_terms( $values, 'mep_cat' );
								$org_class          = mep_get_term_as_class( $values, 'mep_org' );
								$torg               = get_the_terms( $values, 'mep_org' );
								$cat_class          = mep_get_term_as_class( $values, 'mep_cat' );
								$available_seat     = mep_get_total_available_seat( $values, $event_meta );
								$show_price         = mep_get_option( 'mep_event_price_show', 'general_setting_sec', 'yes' );
								$show_price_label   = mep_get_option( 'event-price-label', 'general_setting_sec', 'Price Starts from' );
								$author_terms       = get_the_terms( $values, 'mep_org' );
								?>
								<div class="item">
									<a href="<?php echo get_the_permalink( $values ); ?>">
										<img src="<?php echo get_the_post_thumbnail_url(); ?>" alt="">
										<?php if(isset($event_meta['mep_event_start_date'][0])): ?>
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
													<?php echo mb_substr(get_the_title(), 0, 35) . '...'; ?>
												</h2>
											</a>
											<?php 
											$locations = MPWEM_Functions::get_location($values);
											$data=[];
											if (!empty($locations)) {
												foreach ($locations as $location) {
													$data[] = $location;
												}
												echo implode(', ', $data);
											}
											
											?>
										</div>
										<div class="price">
											<p><?php echo $show_price_label   ?></p>
											<h2 class='mep_list_date'> 
												<?php if ( $show_price == 'yes' ) {
														echo mep_event_list_price( $values );
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
					
					(function($) {
						$(document).ready(function() {
							$('.mep-related-events-items').slick({
								dots: true,
								arrows: true,
								prevArrow:'.mep-ev-prev',
								nextArrow:'.mep-ev-next',
								infinite: true,
								centerMode: true,
								autoplay: false,
								autoplaySpeed: 2000,
								centerPadding: '10px',
								slidesToShow: 3,
								slidesToScroll: 1,
								responsive: [
									{
									breakpoint: 1024,
									settings: {
										slidesToShow:2,
										slidesToScroll: 2,
										infinite: true,
										dots: true
									}
									},
									{
									breakpoint: 480,
									settings: {
										slidesToShow: 1,
										slidesToScroll: 1
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
			public function related_single_products() {
				global $woocommerce, $post;
				$product_ids = get_post_meta( $post->ID, '_upsizing_products_ids', true ) ? get_post_meta( $post->ID, '_upsizing_products_ids', true ) : [];
				//print_r($product_ids);
				$style = 'grid'; ?>
				<div class="mep_event_list">
					<div class="mep_event_list_sec">
						<?php
							$event_expire_on 			= mep_get_option( 'mep_event_expire_on_datetime', 'general_setting_sec', 'event_start_datetime');
							$now                        = current_time('Y-m-d H:i:s');
							$args_search_qqq = array (
								 'post_type'            => array( 'mep_events' ),
								 'posts_per_page'       => -1,
								 'post__in'             => $product_ids,
								 'order'                => 'ASC',
								 'orderby'              => 'meta_value',
								 'meta_key'             => 'event_start_datetime',
								 'meta_query'           => array(
									array(
											'key'       => $event_expire_on,
											'value'     => $now,
											'compare'   => '>'
										)
									)
			
							);
							$loop                       = new WP_Query( $args_search_qqq );
		
						if ( is_array( $product_ids ) && sizeof($product_ids) > 0 ) {
							while ($loop->have_posts()) {
							$loop->the_post(); 
							$values = get_the_id();
								$event_meta             = get_post_custom( $values );
								$tt                     = get_the_terms( $values, 'mep_cat' );
								$org_class              = mep_get_term_as_class( $values, 'mep_org' );
								$torg                   = get_the_terms( $values, 'mep_org' );
								$cat_class              = mep_get_term_as_class( $values, 'mep_cat' );
								$available_seat         = mep_get_total_available_seat( $values, $event_meta );
								$show_price             = mep_get_option( 'mep_event_price_show', 'general_setting_sec', 'yes' );
								$show_price_label       = mep_get_option( 'event-price-label', 'general_setting_sec', 'Price Starts from:' );
								$author_terms           = get_the_terms( $values, 'mep_org' );
								?>
								<div class='related-event mep_event_<?php echo $style; ?>_item mix <?php if ( $tt ) {
									echo 'mage-' . $org_class;
								} ?> <?php if ( $torg ) {
									echo 'mage-' . $cat_class;
								} ?>'>
									<div class="mep_list_thumb">
										<a href="<?php echo get_the_permalink( $values ); ?>"><?php echo get_the_post_thumbnail( $values ); ?></a>
										<div class="mep-ev-start-date">
											<div class="mep-day"><?php echo date_i18n( 'd', strtotime( $event_meta['mep_event_start_date'][0] ) ); ?></div>
											<div class="mep-month"><?php echo date_i18n( 'M', strtotime( $event_meta['mep_event_start_date'][0] ) ); ?></div>
										</div>
									</div>
									<div class="mep_list_event_details"><a
												href="<?php echo get_the_permalink( $values ); ?>">
											<div class="mep-list-header">
												<h2 class='mep_list_title'><?php echo get_the_title( $values ); ?></h2>
												<?php if ( $available_seat == 0 ) {
													do_action( 'mep_show_waitlist_label' );
												} ?>
												<h3 class='mep_list_date'> <?php if ( $show_price == 'yes' ) {
														echo $show_price_label . " " . mep_event_list_price( $values );
													} ?><!-- <i class="far fa-calendar-alt"></i> <?php echo date_i18n( 'h:i A', strtotime( $event_meta['mep_event_start_date'][0] ) ); ?> - <?php echo $event_meta['mep_event_end_date'][0]; ?> --></h3>
											</div>
											<?php
											if ( $style == 'grid' ) {
												?>
												<div class="mep-event-excerpt">
													<?php get_the_excerpt( $values ); ?>
												</div>
											<?php } ?>
											<div class="mep-list-footer">
												<ul>
													<?php
		
													$mep_hide_org_list      = mep_get_option( 'mep_event_hide_organizer_list', 'general_setting_sec' );
													$mep_hide_location_list = mep_get_option( 'mep_event_hide_location_list', 'general_setting_sec' );
													$mep_hide_time_list     = mep_get_option( 'mep_event_hide_time_list', 'general_setting_sec' );
													$mep_hide_end_time_list = mep_get_option( 'mep_event_hide_end_time_list', 'general_setting_sec' );
		
		
													?>
													<?php if ( $mep_hide_org_list != 'yes' ) { ?>
														<li>
															<div class="evl-ico"><i class="fa fa-university"></i></div>
															<div class="evl-cc">
																<h5>
																	<?php echo mep_get_option( 'mep_organized_by_text', 'label_setting_sec' ) ? mep_get_option( 'mep_organized_by_text', 'label_setting_sec' ) : _e( 'Organized By:', 'mage-eventpress' ); ?>
																</h5>
																<h6><?php if ( $author_terms ) {
																		echo $author_terms[0]->name;
																	} ?></h6>
															</div>
														</li>
													<?php } ?>
													<?php if ( $mep_hide_location_list != 'yes' ) { ?>
														<li>
															<div class="evl-ico"><i class="fa fa-location-arrow"></i></div>
															<div class="evl-cc">
																<h5>
																	<?php echo mep_get_option( 'mep_location_text', 'label_setting_sec' ) ? mep_get_option( 'mep_location_text', 'label_setting_sec' ) : _e( 'Location:', 'mage-eventpress' ); ?>
		
																</h5>
																<h6><?php mep_get_event_city( $values ); ?></h6>
															</div>
														</li>
													<?php } ?>
													<?php if ( $mep_hide_time_list != 'yes' ) { ?>
														<li>
															<div class="evl-ico"><i class="far fa-calendar-alt"></i></div>
															<div class="evl-cc">
																<h5>
																	<?php echo mep_get_option( 'mep_time_text', 'label_setting_sec' ) ? mep_get_option( 'mep_time_text', 'label_setting_sec' ) : _e( 'Time:', 'mage-eventpress' ); ?>
																</h5>
																<h6><?php mep_get_only_time( $event_meta['mep_event_start_date'][0] ); ?>
																	<?php if ( $mep_hide_end_time_list != 'yes' ) { ?>
																		- <?php mep_get_only_time( $event_meta['mep_event_end_date'][0] ); ?>
																	<?php } ?>
																</h6>
															</div>
														</li>
													<?php } ?>
												</ul>
											</div>
										</a>
									</div>
								</div>
								<?php
							}
								wp_reset_postdata();
						} ?>
					</div>
				</div>
				<?php
			}
		}
		new MPWEM_Related_Settings();
	}