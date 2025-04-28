<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MP_Custom_Layout')) {
		class MP_Custom_Layout {
			public function __construct() {
				add_action('add_mp_hidden_table', array($this, 'hidden_table'), 10, 2);
				add_action('add_mp_pagination_section', array($this, 'pagination'), 10, 3);
			}
			public function hidden_table($hook_name, $data = array()) {
				?>
                <div class="mp_hidden_content">
                    <table>
                        <tbody class="mp_hidden_item">
						<?php do_action($hook_name, $data); ?>
                        </tbody>
                    </table>
                </div>
				<?php
			}
			public function pagination($params, $total_item, $active_page = 0) {
				ob_start();
				$per_page = $params['show'] > 1 ? $params['show'] : $total_item;
				?>
                <input type="hidden" name="pagination_per_page" value="<?php echo esc_attr($per_page); ?>"/>
                <input type="hidden" name="pagination_style" value="<?php echo esc_attr($params['pagination-style']); ?>"/>
                <input type="hidden" name="mp_total_item" value="<?php echo esc_attr($total_item); ?>"/>
				<?php if ($total_item > $per_page) { ?>
                    <div class="allCenter pagination_area" data-placeholder>
						<?php
							if ($params['pagination-style'] == 'load_more') {
								?>
                                <button type="button" class="_mpBtn_xs_min_200 pagination_load_more" data-load-more="0">
									<?php esc_html_e('Load More', 'mage-eventpress'); ?>
                                </button>
								<?php
							} else {
								$page_mod = $total_item % $per_page;
								$total_page = (int)($total_item / $per_page) + ($page_mod > 0 ? 1 : 0);
								?>
                                <div class="buttonGroup">
									<?php if ($total_page > 2) { ?>
                                        <button class="_mpBtn_xs page_prev" type="button" title="<?php esc_html_e('GoTO Previous Page', 'mage-eventpress'); ?>" disabled>
                                            <span class="fas fa-chevron-left mp_zero"></span>
                                        </button>
									<?php } ?>

									<?php if ($total_page > 5) { ?>
                                        <button class="_mpBtn_xs ellipse_left" type="button" disabled>
                                            <span class="fas fa-ellipsis-h mp_zero"></span>
                                        </button>
									<?php } ?>

									<?php for ($i = 0; $i < $total_page; $i++) { ?>
                                        <button class="_mpBtn_xs <?php echo esc_html($i) == $active_page ? 'active_pagination' : ''; ?>" type="button" data-pagination="<?php echo esc_html($i); ?>"><?php echo esc_html($i + 1); ?></button>
									<?php } ?>

									<?php if ($total_page > 5) { ?>
                                        <button class="_mpBtn_xs ellipse_right" type="button" disabled>
                                            <span class="fas fa-ellipsis-h mp_zero"></span>
                                        </button>
									<?php } ?>

									<?php if ($total_page > 2) { ?>
                                        <button class="_mpBtn_xs page_next" type="button" title="<?php esc_html_e('GoTO Next Page', 'mage-eventpress'); ?>">
                                            <span class="fas fa-chevron-right mp_zero"></span>
                                        </button>
									<?php } ?>
                                </div>
							<?php } ?>
                    </div>
					<?php
				}
				echo ob_get_clean();
			}
			/*****************************/
			public static function switch_button($name, $checked = '') {
				?>
                <label class="roundSwitchLabel">
                    <input type="checkbox" name="<?php echo esc_attr($name); ?>" <?php echo esc_attr($checked); ?>>
                    <span class="roundSwitch" data-collapse-target="#<?php echo esc_attr($name); ?>"></span>
                </label>
				<?php
			}
			public static function popup_button($target_popup_id, $text) {
				?>
                <button type="button" class="_dButton_bgBlue" data-target-popup="<?php echo esc_attr($target_popup_id); ?>">
                    <span class="fas fa-plus-square"></span>
					<?php echo esc_html($text); ?>
                </button>
				<?php
			}
			public static function popup_button_xs($target_popup_id, $text) {
				?>
                <button type="button" class="_dButton_xs_bgBlue" data-target-popup="<?php echo esc_attr($target_popup_id); ?>">
                    <span class="fas fa-plus-square"></span>
					<?php echo esc_html($text); ?>
                </button>
				<?php
			}
			/*****************************/
			public static function add_new_button($button_text, $class = 'mp_add_item', $button_class = '_themeButton_xs_mT_xs', $icon_class = 'fas fa-plus-square') {
				?>
                <button class="<?php echo esc_attr($button_class . ' ' . $class); ?>" type="button">
                    <span class="<?php echo esc_attr($icon_class); ?>"></span>
                    <span class="mL_xs"><?php echo MP_Global_Function::esc_html($button_text); ?></span>
                </button>
				<?php
			}
			public static function move_remove_button() {
				?>
                <div class="allCenter">
                    <div class="buttonGroup max_100">
						<?php
							self::remove_button();
							self::move_button();
						?>
                    </div>
                </div>
				<?php
			}
			public static function remove_button() {
				?>
                <button class="_warningButton_xs mp_item_remove" type="button">
                    <span class="fas fa-trash-alt mp_zero"></span>
                </button>
				<?php
			}
			public static function move_button() {
				?>
                <div class="_mpBtn_navy_blueButton_xs mp_sortable_button" type="">
                    <span class="fas fa-expand-arrows-alt mp_zero"></span>
                </div>
				<?php
			}
			public static function add_multi_image($name, $images) {
				$images = is_array($images) ? MP_Global_Function::array_to_string($images) : $images;
				?>
                <div class="mp_multi_image_area">
                    <input type="hidden" class="mp_multi_image_value" name="<?php echo esc_attr($name); ?>" value="<?php esc_attr_e($images); ?>"/>
                    <div class="mp_multi_image">
						<?php
							$all_images = explode(',', $images);
							if ($images && sizeof($all_images) > 0) {
								foreach ($all_images as $image) {
									?>
                                    <div class="mp_multi_image_item" data-image-id="<?php esc_attr_e($image); ?>">
                                        <span class="fas fa-times circleIcon_xs mp_remove_multi_image"></span>
                                        <img class="w-100" src="<?php echo MP_Global_Function::get_image_url('', $image, 'medium'); ?>" alt="<?php esc_attr_e($image); ?>"/>
                                    </div>
									<?php
								}
							}
						?>
                    </div>
                    <div class="">
						<?php MP_Custom_Layout::add_new_button(esc_html__('Add Image', 'mage-eventpress'), 'add_multi_image', '_dButton_xs_bgColor_1'); ?>
                    </div>
                </div>
				<?php
			}
			/*****************************/
			public static function load_more_text($text = '', $length = 150) {
				$text_length = strlen($text);
				if ($text && $text_length > $length) {
					?>
                    <div class="mp_load_more_text_area">
                        <span data-read-close><?php echo esc_html(substr($text, 0, $length)); ?> ....</span>
                        <span data-read-open class="dNone"><?php echo esc_html($text); ?></span>
                        <div data-read data-open-text="<?php esc_attr_e('Load More', 'mage-eventpress'); ?>" data-close-text="<?php esc_attr_e('Less More', 'mage-eventpress'); ?>">
                            <span data-text><?php esc_html_e('Load More', 'mage-eventpress'); ?></span>
                        </div>
                    </div>
					<?php
				} else {
					?>
                    <span><?php echo esc_html($text); ?></span>
					<?php
				}
			}
			/*****************************/
			public static function qty_input($data = []) {
				$input_name = array_key_exists('name', $data) ? $data['name'] : '';
				$price = array_key_exists('price', $data) ? $data['price'] : 0;
				$available_seat = array_key_exists('available', $data) ? $data['available'] : 1;
				$default_qty = array_key_exists('d_qty', $data) ? $data['d_qty'] : 0;
				$min_qty = array_key_exists('min_qty', $data) ? $data['min_qty'] : 0;
				$min_qty=max($min_qty, 0);
				$max_qty = array_key_exists('max_qty', $data) ? $data['max_qty'] : '';
				$input_type = array_key_exists('type', $data) ? $data['type'] : '';
				//$min_qty = max($default_qty, $min_qty);
				$max_qty = $max_qty > 0 ? $max_qty : $available_seat;
				$max_qty = min($available_seat, $max_qty);
				if ($max_qty > $min_qty) {
					if ($input_type == 'dropdown') {
						$text = array_key_exists('text', $data) ? $data['text'] : '';
						?>
                        <label>
                            <select class="formControl" name="<?php echo esc_attr($input_name); ?>" data-price="<?php echo esc_attr($price); ?>">
								<?php for ($i = $min_qty; $i <= $max_qty; $i++) { ?>
                                    <option value="<?php echo esc_attr($i); ?>" <?php echo esc_attr($i == $default_qty ? 'selected' : ''); ?>><?php echo esc_html($i . ' ' . $text) ?></option>
								<?php } ?>
                            </select>
                        </label>
					<?php } else { ?>
                        <div class="groupContent qtyIncDec">
                            <div class="decQty addonGroupContent">
                                <span class="fas fa-minus"></span>
                            </div>
                            <label>
                                <input type="text"
                                       class="formControl inputIncDec mp_number_validation"
                                       data-price="<?php echo esc_attr($price); ?>"
                                       name="<?php echo esc_attr($input_name); ?>"
                                       value="<?php echo esc_attr(max(0, $default_qty)); ?>"
                                       min="<?php echo esc_attr($min_qty); ?>"
                                       max="<?php echo esc_attr($max_qty); ?>"
                                />
                            </label>
                            <div class="incQty addonGroupContent">
                                <span class="fas fa-plus"></span>
                            </div>
                        </div>
						<?php
					}
				}else{
					?> <input type="hidden" name="<?php echo esc_attr($input_name); ?>" value="0"  data-price="<?php echo esc_attr($price); ?>"/><?php
					esc_html_e('Sorry, not available', 'mage-eventpress');
                }
			}
		}
		new MP_Custom_Layout();
	}