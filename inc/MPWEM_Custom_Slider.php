<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	//echo '<pre>';print_r();echo '</pre>';
	if ( ! class_exists( 'MPWEM_Custom_Slider' ) ) {
		class MPWEM_Custom_Slider {
			public function __construct() {
				add_action( 'mpwem_custom_slider', array( $this, 'super_slider' ), 10, 2 );
				add_action( 'mpwem_custom_slider_only', array( $this, 'super_slider_only' ) );
				add_action( 'mpwem_custom_slider_icon_indicator', array( $this, 'icon_indicator' ) );
			}
			public function super_slider( $post_id = '', $event_infos = [] ) {
				$event_infos = is_array( $event_infos ) && sizeof( $event_infos ) > 0 ? $event_infos : MPWEM_Functions::get_all_info( $post_id );
				$display     = is_array($event_infos) && array_key_exists( 'mep_display_slider', $event_infos ) ? $event_infos['mep_display_slider'] : 'on';
					?>
                    <div class="mpwem_slider_area">
					<?php
					$type      = MPWEM_Global_Function::get_slider_settings( 'slider_type', 'slider' );
					$post_id   = $post_id > 0 ? $post_id : get_the_id();
					$image_ids = $this->get_slider_ids( $post_id, 'mep_gallery_images' );
					if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 && $display == 'on' ) {
						if ( $type == 'slider' && is_array( $image_ids ) && sizeof( $image_ids ) > 1 ) {
							$this->slider( $post_id, $image_ids );
						} else {
							$this->post_thumbnail( $image_ids[0] );
						}
					} else {
						$thumb_id  = get_post_thumbnail_id( $post_id );
						$this->post_thumbnail($thumb_id);
					}
					?></div><?php
			}
			public function super_slider_only( $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
                    <div class="mpwem_slider_area">
                        <div class="superSlider placeholder_area">
							<?php $this->slider_all_item( $image_ids ); ?>
                        </div>
                    </div>
					<?php
				}
			}
			public function slider( $post_id, $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$showcase_position = MPWEM_Global_Function::get_slider_settings( 'showcase_position', 'right' );
					$slider_style      = MPWEM_Global_Function::get_slider_settings( 'slider_style', 'style_1' );
					$is_style_two      = ( $slider_style === 'style_2' );
					$column_class      = ( ! $is_style_two && ( $showcase_position == 'top' || $showcase_position == 'bottom' ) ) ? 'area_column' : '';
					$image_count       = count( $image_ids );
					?>
                    <div class="superSlider placeholder_area fdColumn <?php echo $is_style_two ? 'mpwem-slider--style-2' : 'mpwem-slider--style-1'; ?>">
                        <input type="hidden" name="slider_height_type" value="<?php echo esc_attr( MPWEM_Global_Function::get_slider_settings( 'slider_height', 'avg' ) ); ?>"/>
                        <div class="dFlex <?php echo esc_attr( $column_class ); ?>">
							<?php
								if ( ! $is_style_two && ( $showcase_position == 'top' || $showcase_position == 'left' ) ) {
									$this->slider_showcase( $image_ids );
								}
								$this->slider_all_item( $image_ids, '', $is_style_two ? 'force' : '' );
								if ( ! $is_style_two && ( $showcase_position == 'bottom' || $showcase_position == 'right' ) ) {
									$this->slider_showcase( $image_ids );
								}
								if ( $is_style_two ) {
									?>
                                    <div class="mpwem-slider-style2__view-all">
                                        <button type="button" class="mpwem-slider-style2__view-all-btn" data-target-popup="superSlider" data-slide-index="1">
											<?php
											echo esc_html(
												sprintf(
													/* translators: %d: number of gallery images */
													__( 'View All %d Images', 'mage-eventpress' ),
													$image_count
												)
											);
											?>
                                        </button>
                                    </div>
                                    <div class="mpwem-slider-style2__count" aria-hidden="true">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %d: number of gallery images */
												_n( '%d photo', '%d photos', $image_count, 'mage-eventpress' ),
												$image_count
											)
										);
										?>
                                    </div>
									<?php
								}
							?>
                        </div>
						<?php
							if ( ! $is_style_two ) {
								$slider_indicator = MPWEM_Global_Function::get_slider_settings( 'indicator_visible', 'on' );
								$icon             = MPWEM_Global_Function::get_slider_settings( 'indicator_type', 'icon' );
								if ( $slider_indicator == 'on' && $icon == 'image' ) {
									$this->image_indicator( $image_ids );
								}
							}
						?>
						<?php $this->slider_popup( $post_id, $image_ids ); ?>
                    </div>
					<?php
				}
			}
			public function post_thumbnail( $image_id = '' ) {
				$thumbnail = MPWEM_Global_Function::get_image_url( '', $image_id );
				if ( $thumbnail ) {
					?>
                    <div class="post_thumb">
	                    <?php //MPWEM_Custom_Layout::bg_image( '', $image_id ); ?>
                        <img src="<?php echo esc_url($thumbnail); ?>" class="img" alt="">
                    </div>
					<?php
				}
			}
			public function slider_all_item( $image_ids, $popup_slider_icon = '', $force_icons = '' ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
                    <div class="sliderAllItem">
						<?php
							$count = 1;
							foreach ( $image_ids as $id ) {
								?>
                                <div class="sliderItem" data-slide-index="<?php echo esc_html( $count ); ?>" data-target-popup="superSlider" data-placeholder>
									<?php MPWEM_Custom_Layout::bg_image( '', $id ); ?>
                                </div>
								<?php
								$count ++;
							}
						?>
						<?php
							$icon = MPWEM_Global_Function::get_slider_settings( 'indicator_type', 'icon' );
							if ( $force_icons === 'force' || ( ( $icon == 'icon' || $popup_slider_icon == 'on' ) && is_array( $image_ids ) && sizeof( $image_ids ) > 1 ) ) {
								if ( $force_icons === 'force' || $popup_slider_icon == 'on' || MPWEM_Global_Function::get_slider_settings( 'indicator_visible', 'on' ) == 'on' ) {
									$this->icon_indicator( $force_icons === 'force' ? 'style_2' : $popup_slider_icon );
								}
							}
						?>
                    </div>
					<?php
				}
			}
			public function slider_showcase( $image_ids ) {
				$showcase = MPWEM_Global_Function::get_slider_settings( 'showcase_visible', 'on' );
				if ( $showcase == 'on' && is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$showcase_position = MPWEM_Global_Function::get_slider_settings( 'showcase_position', 'right' );
					$slider_style      = MPWEM_Global_Function::get_slider_settings( 'slider_style', 'style_1' );
					?>
                    <div class="sliderShowcase <?php echo esc_attr( $showcase_position . ' ' . $slider_style ); ?>">
						<?php
							if ( $slider_style == 'style_1' ) {
								$this->slider_showcase_style_1( $image_ids );
							} else {
								$this->slider_showcase_style_2( $image_ids );
							}
						?>
                    </div>
					<?php
				}
			}
			public function slider_showcase_style_1( $image_ids ) {
				$count = 1;
				foreach ( $image_ids as $id ) {
					$image_url = MPWEM_Global_Function::get_image_url( '', $id );
					if ( $count < 4 ) {
						?>
                        <div class="sliderShowcaseItem" data-slide-target="<?php echo esc_html( $count ); ?>" data-placeholder>
                            <div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
                        </div>
						<?php
					}
					if ( $count == 4 ) {
						?>
                        <div class="sliderShowcaseItem" data-target-popup="superSlider" data-placeholder>
                            <div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
                            <div class="sliderMoreItem">
                                <span class="fas fa-plus"></span>
								<?php echo (is_array( $image_ids ) ? sizeof( $image_ids ) : 0) - 4; ?>
                                <span class="far fa-image"></span>
                            </div>
                        </div>
						<?php
					}
					$count ++;
				}
			}
			public function slider_showcase_style_2( $image_ids ) {
				$count = 1;
				foreach ( $image_ids as $id ) {
					$image_url = MPWEM_Global_Function::get_image_url( '', $id );
					if ( $count > 1 && $count < 5 ) {
						?>
                        <div class="sliderShowcaseItem" data-target-popup="superSlider" data-slide-index="<?php echo esc_html( $count ); ?>" data-placeholder>
                            <div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
                        </div>
						<?php
					}
					$count ++;
				}
			}
			public function image_indicator( $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
                    <div class="slideIndicator">
						<?php
							$count = 1;
							foreach ( $image_ids as $id ) {
								$image_url = MPWEM_Global_Function::get_image_url( '', $id, array( 150, 100 ) );
								?>
                                <div class="slideIndicatorItem" data-slide-target="<?php echo esc_html( $count ); ?>">
                                    <div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
                                </div>
								<?php
								$count ++;
							}
						?>
                    </div>
					<?php
				}
			}
			public function icon_indicator( $popup_slider_icon = '' ) {
				$slider_indicator = MPWEM_Global_Function::get_slider_settings( 'indicator_visible', 'on' );
				if ( $slider_indicator == 'on' || $popup_slider_icon == 'on' || $popup_slider_icon == 'style_2' ) {
					$prev_icon = ( $popup_slider_icon === 'style_2' ) ? 'fas fa-chevron-left' : 'fas fa-chevron-circle-left';
					$next_icon = ( $popup_slider_icon === 'style_2' ) ? 'fas fa-chevron-right' : 'fas fa-chevron-circle-right';
					?>
                    <div class="iconIndicator prevItem" role="button" tabindex="0" aria-label="<?php echo esc_attr__( 'Previous image', 'mage-eventpress' ); ?>">
                        <span class="<?php echo esc_attr( $prev_icon ); ?>"></span>
                    </div>
                    <div class="iconIndicator nextItem" role="button" tabindex="0" aria-label="<?php echo esc_attr__( 'Next image', 'mage-eventpress' ); ?>">
                        <span class="<?php echo esc_attr( $next_icon ); ?>"></span>
                    </div>
					<?php
				}
			}
			public function slider_popup( $post_id, $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$popup_icon_indicator = MPWEM_Global_Function::get_slider_settings( 'popup_icon_indicator', 'on' );
					?>
                    <div class="sliderPopup" data-popup="superSlider">
                        <div class="superSlider">
                            <div class="popupHeader">
                                <h2><?php echo get_the_title( $post_id ); ?></h2>
                                <span class="fas fa-times popup_close"></span>
                            </div>
                            <div class="popupBody">
								<?php $this->slider_all_item( $image_ids, $popup_icon_indicator ); ?>
                            </div>
                            <div class="popupFooter">
								<?php
									$indicator = MPWEM_Global_Function::get_slider_settings( 'popup_image_indicator', 'on' );
									if ( $indicator == 'on' ) {
										$this->image_indicator( $image_ids );
									}
								?>
                            </div>
                        </div>
                    </div>
					<?php
				}
			}
			//==============//
			public function get_slider_ids( $post_id, $key ) {
				$thumb_id  = get_post_thumbnail_id( $post_id );
				$image_ids = MPWEM_Global_Function::get_post_info( $post_id, $key, array() );
				if ( $thumb_id && $thumb_id > 0 ) {
					array_unshift( $image_ids, $thumb_id );
				}
				return array_filter( array_unique( $image_ids ) );
			}
		}
		new MPWEM_Custom_Slider();
	}
