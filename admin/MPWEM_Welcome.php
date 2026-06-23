<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Welcome')) {
		class MPWEM_Welcome {
			public function __construct() {
				add_action('admin_menu', array($this, 'welcome_menu'));
			}
			public function welcome_menu() {
				add_submenu_page(
					'edit.php?post_type=mep_events',
					__('Welcome', 'mage-eventpress'), // page title
					'<span style="color:#10dd10">' . __('Welcome', 'mage-eventpress') . '</span>', // menu title with HTML outside translation
					'manage_options',
					'mep_event_welcome_page',
					array($this, 'welcome_page')
				);
			}
			public function welcome_page() {
				?>
				<div class="wrap"></div>
				<div class="mpwem_style mpwem_welcome_page">
					<div class='padding'>
						<div class="mpTabs tabBorder _shadow_6">
							<ul class="tabLists _bg_light_1">
								<li data-tabs-target="#mpwem_welcome">
									<h4><?php esc_html_e('Welcome', 'mage-eventpress'); ?></h4>
								</li>
								<li data-tabs-target="#mpwem_addons">
									<h4><?php esc_html_e('Addons', 'mage-eventpress'); ?></h4>
								</li>
								<li data-tabs-target="#mpwem_support">
									<h4><?php esc_html_e('Support & Knowledge Base', 'mage-eventpress'); ?></h4>
								</li>
								<li data-tabs-target="#mpwem_faq">
									<h4><?php esc_html_e('F.A.Q', 'mage-eventpress'); ?></h4>
								</li>
							</ul>
							<div class="tabsContent bgWhite">
								<?php $this->welcome_content(); ?>
								<?php $this->addons_content(); ?>
								<?php $this->support_content(); ?>
								<?php $this->faq(); ?>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			public function welcome_content() {
				?>
				<div class="tabsItem" data-tabs="#mpwem_welcome">
					<div class="mpContainer">
						<div class="mpRow">
							<div class="col_8">
								<h2><?php esc_html_e('WooCommerce Event Manager', 'mage-eventpress'); ?></h2>
								<div class="divider"></div>
								<h6 class="_mt_textInfo"><?php esc_html_e('WooCommerce Event Manager Plugin for WordPress is the complete event solution. All major functions are available in this plugin which is needed in an Event booking website.', 'mage-eventpress'); ?></h6>
								<h6 class="_mt_textInfo"><?php esc_html_e('It uses WooCommerce to take payment, which provides freedom for using popular payment getaway via WooCommerce. This plugin supports all WordPress version and can be used to create any types of any types of events.', 'mage-eventpress'); ?></h6>
								<div class="_mt_40_dFlex">
									<button class="_button_navy_blue_mr_xs" type="button" data-href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/#mage_product_price">
										<?php esc_html_e('Buy Now', 'mage-eventpress'); ?>
									</button>
									<button class="_button_theme_mr_xs" type="button" data-href="https://event.mage-people.com/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
									<button class="_button_default_mr_xs" type="button" data-href="https://docs.mage-people.com/woocommerce-event-manager/"><?php esc_html_e('Documentation', 'mage-eventpress'); ?></button>
								</div>
							</div>
							<div class="col_4">
								<div class="_margin_auto_max_300">
									<div class="bg_image_area">
										<div data-bg-image="<?php echo esc_attr(MPWEM_PLUGIN_URL . '/assets/helper/images/ullimited_img.png'); ?>"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="mpRow _mt_40">
							<div class="col_12">
								<h2><?php esc_html_e("Pro Features You'll Love", 'mage-eventpress'); ?></h2>
								<div class="divider"></div>
								<div class="flexWrap">
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="fas fa-tasks"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('Attendee Management', 'mage-eventpress'); ?></h5>
									</div>
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="fab fa-wpforms"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('Attendee Custom Form', 'mage-eventpress'); ?></h5>
									</div>
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="fas fa-file-pdf"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('PDF Ticketing', 'mage-eventpress'); ?></h5>
									</div>
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="far fa-envelope"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('Custom Emailing', 'mage-eventpress'); ?></h5>
									</div>
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="fas fa-user-edit"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('Attendee Edit Feature', 'mage-eventpress'); ?></h5>
									</div>
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="fas fa-file-alt"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('Attendee CSV Export', 'mage-eventpress'); ?></h5>
									</div>
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="far fa-file-alt"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('Report Overview', 'mage-eventpress'); ?></h5>
									</div>
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="fas fa-palette"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('Custom Style Settings', 'mage-eventpress'); ?></h5>
									</div>
									<div class="_group_content mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 _group_addon">
											<span class="fas fa-language"></span>
										</h5>
										<h5 class="_textInfo_bg_light_padding_xs_text_left_fullWidth"><?php esc_html_e('Translation Settings', 'mage-eventpress'); ?></h5>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php $this->bottom_info(); ?>
				</div>
				<?php
			}
			public function addons_content() {
				$addons = $this->get_addons_list();
				?>
				<style>
					.mpwem_addon_item {
						width: calc(33.33% - 20px);
						margin: 10px;
						box-sizing: border-box;
					}
					
					@media screen and (max-width: 1024px) {
						.mpwem_addon_item {
							width: calc(50% - 20px) !important;
						}
					}
					
					@media screen and (max-width: 768px) {
						.mpwem_addon_item {
							width: calc(100% - 20px) !important;
							margin: 10px auto !important;
						}
						
						.mpwem_addon_item p {
							min-height: auto !important;
						}
					}
					
					@media screen and (max-width: 480px) {
						.mpwem_addon_item {
							width: 100% !important;
							margin: 10px 0 !important;
						}
					}
				</style>
				<div class="tabsItem" data-tabs="#mpwem_addons">
					<div class="mpContainer">
						<div class="mpRow">
							<div class="col_12">
								<h2><?php esc_html_e('Available Addons', 'mage-eventpress'); ?></h2>
								<p><?php esc_html_e('Extend your Event Manager with these powerful addons', 'mage-eventpress'); ?></p>
								<div class="divider"></div>
								<div class="flexWrap">
									<?php foreach ($addons as $addon) : ?>
										<div class="_group_content mpwem_addon_item">
											<div class="_bg_light_padding_xs">
												<div class="_bgInfo_textLight_padding_xs_min_50 _group_addon allCenter">
													<span class="<?php echo esc_attr($addon['icon']); ?>" style="font-size: 32px;"></span>
												</div>
												<h4 class="_textInfo_padding_xs_text_center"><?php echo esc_html($addon['name']); ?></h4>
												<p class="_padding_xs" style="min-height: 80px; color: #333;"><?php echo esc_html($addon['description']); ?></p>
												<div class="_padding_xs allCenter">
													<button class="_button_theme_xs" type="button" onclick="window.open('<?php echo esc_url($addon['link']); ?>', '_blank'); return false;">
														<?php esc_html_e('View Details', 'mage-eventpress'); ?>
													</button>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
					<?php $this->bottom_info(); ?>
				</div>
				<?php
			}
			public function support_content() {
				?>
				<div class="tabsItem" data-tabs="#mpwem_support">
					<div class="mpContainer">
						<div class="mpRow">
							<div class="col_12">
								<h2><?php esc_html_e('All Shortcode list', 'mage-eventpress'); ?></h2>
								<div class="divider"></div>
								<table>
									<thead>
									<tr>
										<th>Name</th>
										<th>Shortcode</th>
										<th>Parameter Description</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<th>
											Events – List Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-list-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<code>[event-list show='2' pagination='yes']</code>
										</th>
										<td>
											<strong>style</strong>
											<p>Number of events show (integer number only) | Default:
												<strong>-1</strong>
												to show all
											</p>
											<strong>pagination</strong>
											<p>
												<strong>yes</strong>
												or
												<strong>no</strong>
												or
												<strong>carousal</strong>
												| Default:
												<strong>no</strong>
											</p>
										</td>
									</tr>
									<tr>
										<th>
											Events – List Style with Search Box
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-list-style-with-search-box/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<code>[event-list column=4 search-filter='yes']</code>
										</th>
										<td>
											<p>
												<code>column</code>
												<strong>3</strong>
												or
												<strong>4</strong>
												(integer number only) | Default:
												<strong>3</strong>
											</p>
											<p>
												<code>search-filter</code>
												<strong>yes</strong>
												or
												<strong>no</strong>
												| Default:
												<strong>no</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Grid Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-grid-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list show='3' style='grid']</code>
										</td>
										<td>
											<p>
												<code>show</code>
												Number of events show (integer number only) | Default:
												<strong>-1</strong>
												to show all
											</p>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Minimal Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-minimal-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list show='2' style='minimal']</code>
										</td>
										<td>
											<p>
												<code>show</code>
												Number of events show (integer number only) | Default:
												<strong>-1</strong>
												to show all
											</p>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Winter Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-winter-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list show='2' style='winter']</code>
										</td>
										<td>
											<p>
												<code>show</code>
												Number of events show (integer number only) | Default:
												<strong>-1</strong>
												to show all
											</p>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Native Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-native-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list show='2' style='native']</code>
										</td>
										<td>
											<p>
												<code>show</code>
												Number of events show (integer number only) | Default:
												<strong>-1</strong>
												to show all
											</p>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Vertical Timeline Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-vertical-timeline-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list show='5' style='timeline' timeline-mode='vertical']</code>
										</td>
										<td>
											<p>
												<code>show</code>
												Number of events show (integer number only) | Default:
												<strong>-1</strong>
												to show all
											</p>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
											<p>
												<code>timeline-mode</code>
												<strong>vertical</strong>
												or
												<strong>horizontal</strong>
												| Default:
												<strong>vertical</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Horizontal Timeline Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-horizontal-timeline-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list show='5' style='timeline' timeline-mode='horizontal']</code>
										</td>
										<td>
											<p>
												<code>show</code>
												Number of events show (integer number only) | Default:
												<strong>-1</strong>
												to show all
											</p>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
											<p>
												<code>timeline-mode</code>
												<strong>vertical</strong>
												or
												<strong>horizontal</strong>
												| Default:
												<strong>vertical</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events list with search filter
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-list-with-search-filter/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list show='8' style='grid' column='3' search-filter='yes']</code>
										</td>
										<td>
											<p>
												<code>show</code>
												Number of events show (integer number only) | Default:
												<strong>-1</strong>
												to show all
											</p>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
											<p>
												<code>column</code>
												<strong>2</strong>
												to
												<strong>6</strong>
												| Default:
												<strong>3</strong>
												| Applicable for grid style only
											</p>
											<p>
												<code>search-filter</code>
												<strong>yes</strong>
												or
												<strong>no</strong>
												| Default:
												<strong>no</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Title Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-title-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list style='title']</code>
										</td>
										<td>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Carousel Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-carousel-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list style='grid' pagination='carousal' carousal-dots='yes' carousal-nav='yes' column=3]</code>
										</td>
										<td>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
											<p>
												<code>pagination</code>
												<strong>yes</strong>
												or
												<strong>no</strong>
												or
												<strong>carousal</strong>
												| Default:
												<strong>no</strong>
											</p>
											<p>
												<code>carousal-dots</code>
												<strong>yes</strong>
												or
												<strong>no</strong>
												| Default:
												<strong>no</strong>
											</p>
											<p>
												<code>carousal-nav</code>
												<strong>yes</strong>
												or
												<strong>no</strong>
												| Default:
												<strong>no</strong>
											</p>
											<p>
												<code>column</code>
												<strong>1</strong>
												to
												<strong>4</strong>
												| Default:
												<strong>3</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events – Spring Style
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-spring-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list style='spring']</code>
										</td>
										<td>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Event Speakers
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/speakers/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-speaker-list event=14829]</code>
										</td>
										<td>
											<p>
												<code>event</code>
												event
												<strong>ID</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Recurring Events
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/recurring-events/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list-recurring column='3']</code>
										</td>
										<td>
											<p>
												<code>column</code>
												<strong>3</strong>
												or
												<strong>4</strong>
												(integer number only) | Default:
												<strong>3</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events City List
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-city-list/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-city-list]</code>
										</td>
										<td></td>
									</tr>
									<tr>
										<td>
											Events With Pagination
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-with-pagination/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list style='grid' pagination='yes']</code>
										</td>
										<td>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
											<p>
												<code>pagination</code>
												<strong>yes</strong>
												or
												<strong>no</strong>
												or
												<strong>carousal</strong>
												| Default:
												<strong>no</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events by Single Organizer
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-by-single-organizer/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list style='grid' org='15']</code>
										</td>
										<td>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
											<p>
												<code>org</code>
												organizer ID (integer number only) | Default:
												<strong>0</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events Filter by Organization
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-filter-by-organization/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list style='grid' org-filter='yes']</code>
										</td>
										<td>
											<p>
												<code>style</code>
												<strong>grid</strong>
												or
												<strong>list</strong>
												or
												<strong></strong>
												<strong>minimal</strong>
												or
												<strong>native</strong>
												or
												<strong>timeline</strong>
												or
												<strong>title</strong>
												or
												<strong>spring</strong>
												or
												<strong>winter</strong>
												| Default:
												<strong>grid</strong>
											</p>
											<p>
												<code>org-filter</code>
												<strong>yes</strong>
												or
												<strong>no</strong>
												| Default:
												<strong>no</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events by Single Category
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-by-single-category/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list cat='44']</code>
										</td>
										<td>
											<p>
												<code>cat</code>
												category ID (integer number only) | Default:
												<strong>0</strong>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											Events Filter by Category
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-filter-by-category/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-list cat-filter='yes']</code>
										</td>
										<td>
											<p>
												<code>cat-filter</code>
												<strong>yes</strong>
												or
												<strong>no</strong>
												| Default:
												<strong>no</strong>
											</p>
										</td>
									</tr>
									<tr>
										<th>
											Events by Country
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-by-country/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<code>[event-list country='US']</code>
										</th>
										<td>
											<p>
												<code>country</code>
												country name | Default:
												<strong>null</strong>
											</p>
										</td>
									</tr>
									<tr>
										<th>
											Events by City
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-by-city/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<code>[event-list city='Texas']</code>
										</th>
										<td>
											<p>
												<code>city</code>
												city name | Default:
												<strong>null</strong>
											</p>
										</td>
									</tr>
									<tr>
										<th>
											Expired Events
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/expired-events/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<code>[expire-event-list]</code>
										</th>
										<td></td>
									</tr>
									<tr>
										<th>
											Single Event Registration
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/single-event-registration/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<code>[event-add-cart-section event=10408]</code>
										</th>
										<td>
											<p>
												<code>event</code>
												Event
												<strong>ID</strong>
											</p>
										</td>
									</tr>
									<tr>
										<th>
											Events Calendar
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-calendar/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<code>[event-calendar]</code>
										</th>
										<td></td>
									</tr>
									<tr>
										<th>
											Events Calendar Pro
											<span class="mep_welcome_pro_badge">Addon</span>
											<button type="button" class="_button_default_xs" data-href="https://event.mage-people.com/events-calendar-pro/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<p>
												<code>[mep-event-calendar]</code>
											</p>
											<p>
												<code>[mep-event-calendar cat_id='44']</code>
											</p>
											<p>
												<code>[mep-event-calendar-month month='2028-09']</code>
											</p>
										</th>
										<td>
											<p>
												<code>cat_id</code>
												Event Category
												<strong>ID</strong>
											</p>
											<p>
												<code>month</code>
												Events year-month |
												<strong>yyyy-mm</strong>
											</p>
										</td>
									</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<?php $this->bottom_info(); ?>
				</div>
				<?php
			}
			public function faq() {
				$faqs = $this->faq_array();
				?>
				<div class="tabsItem" data-tabs="#mpwem_faq">
					<div class="mpContainer">
						<div class="mpRow">
							<div class="col_12">
								<h2><?php esc_html_e('Frequently Asked Questions', 'mage-eventpress'); ?></h2>
								<p><?php esc_html_e('Here is list of question that may help.', 'mage-eventpress'); ?></p>
								<div class="divider"></div>
								<div class='mp_faq_area'>
									<?php
										foreach ($faqs as $key => $faq) {
											?>
											<div class="mp_faq_item">
												<h5 class="mp_faq_title" data-open-icon="fa-plus" data-close-icon="fa-minus" data-collapse-target="#mpwem_faq_datails_<?php echo esc_attr($key); ?>" data-add-class="active">
													<span data-icon class="fas fa-plus"></span>
													<?php echo esc_html($faq['title']); ?>
												</h5>
												<div data-collapse="#mpwem_faq_datails_<?php echo esc_attr($key); ?>">
													<div class="mp_faq_content">
														<?php echo esc_html($faq['des']); ?>
													</div>
												</div>
											</div>
										<?php } ?>
								</div>
							</div>
						</div>
					</div>
					<?php $this->bottom_info(); ?>
				</div>
				<?php
			}
			public function bottom_info() {
				?>
				<div class="_bgBlack_padding_mt_40">
					<div class="allCenter">
						<h3 class="textWhite"><?php esc_html_e('Get Pro and Others Available Addon to get all these exciting features', 'mage-eventpress'); ?></h3>
						<button class="_button_theme_ml" type="button" data-href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/">
							<?php esc_html_e('Buy Now', 'mage-eventpress'); ?>
						</button>
					</div>
				</div>
				<?php
			}
			public function get_addons_list() {
				return array(
					array(
						'name' => esc_html__('Global/Common Qty Addon', 'mage-eventpress'),
						'description' => esc_html__('Global Qty Addon is an excellent solution for managing qty as total available quantity. It\'s a smart idea that you don\'t want to manage qty as common qty from this addon useful.', 'mage-eventpress'),
						'icon' => 'fas fa-layer-group',
						'link' => 'https://mage-people.com/product/global-common-qty-addon-for-event-manager/#mage_product_price'
					),
					array(
						'name' => esc_html__('Event Max-Min Quantity Limiting Addon', 'mage-eventpress'),
						'description' => esc_html__('This event max-min addon is necessary for limiting the amount of tickets sold. Organizers can set a minimum buying quantity or minimum purchase quantity.', 'mage-eventpress'),
						'icon' => 'fas fa-sort-numeric-up',
						'link' => 'https://mage-people.com/product/event-max-min-quantity-limiting-addon-for-woocommerce-event-manager/#mage_product_price'
					),
					array(
						'name' => esc_html__('Marketplace / Event Frontend Submit Addon', 'mage-eventpress'),
						'description' => esc_html__('This is a marketplace addon that is needed to allow event submit from frontend, multiple organizer can see ticket. Together in a same website.', 'mage-eventpress'),
						'icon' => 'fas fa-store',
						'link' => 'https://mage-people.com/product/event-frontend-submit-addon-for-event-manager/#mage_product_price'
					),
					array(
						'name' => esc_html__('WooCommerce Event QR Code Addon', 'mage-eventpress'),
						'description' => esc_html__('QR code addon is necessary for the ticket. If you have a large event then this addon must need to check ticket validity by scanning by QR scanner or mobile.', 'mage-eventpress'),
						'icon' => 'fas fa-qrcode',
						'link' => 'https://mage-people.com/product/woocommerce-event-qr-code-addon/#mage_product_price'
					),
					array(
						'name' => esc_html__('WooCommerce Event Calendar Addon', 'mage-eventpress'),
						'description' => esc_html__('Event Calendar addon is nice addon that can list display in calendar view. Customer can easily understand event date.', 'mage-eventpress'),
						'icon' => 'fas fa-calendar-alt',
						'link' => 'https://mage-people.com/product/woocommerce-event-calendar-addon/#mage_product_price'
					),
					array(
						'name' => esc_html__('WooCommerce Event: Book an Event From Dashboard', 'mage-eventpress'),
						'description' => esc_html__('This Addon is mainly used for admin, if anyone want to get order offline like phone order then after taking order admin can add attendee from dashboard.', 'mage-eventpress'),
						'icon' => 'fas fa-user-plus',
						'link' => 'https://mage-people.com/product/woocommerce-event-book-an-event-from-dashboard/#mage_product_price'
					),
					array(
						'name' => esc_html__('Email Reminder Addon', 'mage-eventpress'),
						'description' => esc_html__('This Addon is mainly used for addon, if anyone want to get order offline like phone order then after taking order admin can add attendee from dashboard.', 'mage-eventpress'),
						'icon' => 'fas fa-envelope',
						'link' => 'https://mage-people.com/product/event-email-reminder-addon/#mage_product_price'
					),
					array(
						'name' => esc_html__('Early Bird Ticketing Discount Addon', 'mage-eventpress'),
						'description' => esc_html__('Early bird addon is marketing addon, if someone wants to give discount based on date then this addon is very useful.', 'mage-eventpress'),
						'icon' => 'fas fa-percentage',
						'link' => 'https://mage-people.com/product/early-bird-pricing-addon-for-event-manager/#mage_product_price'
					),
					array(
						'name' => esc_html__('WooCommerce Event Waitlist Addon', 'mage-eventpress'),
						'description' => esc_html__('Waitlist addon is very useful, if any ticket become sold out organizer can get subscription for next available ticket.', 'mage-eventpress'),
						'icon' => 'fas fa-clock',
						'link' => 'https://mage-people.com/product/woocommerce-event-waitlist-addon/#mage_product_price'
					),
					array(
						'name' => esc_html__('Event Seat Plan Addon', 'mage-eventpress'),
						'description' => esc_html__('A seat plan addon is needed for that organizer who wants to display a seat plan for customers to choose seat during event ticket buying. Seat plan needed for movie ticket, concert ticket booking.', 'mage-eventpress'),
						'icon' => 'fas fa-chair',
						'link' => 'https://mage-people.com/product/seat-plan-addon-for-event-manager/#mage_product_price'
					),
					array(
						'name' => esc_html__('Membership Price Addon', 'mage-eventpress'),
						'description' => esc_html__('Membership pricing addon for the event is needed for that organizer who wants to offer different pricing based on the role. one of a wordpress website.', 'mage-eventpress'),
						'icon' => 'fas fa-users',
						'link' => 'https://mage-people.com/product/membership-pricing-for-event-manager-plugin/#mage_product_price'
					),
					array(
						'name' => esc_html__('WooCommerce Events: Duplicator Addon', 'mage-eventpress'),
						'description' => esc_html__('Duplication addon mainly needed to save time while event creating, if you have an existing event then just you can re-create event with all setting needed.', 'mage-eventpress'),
						'icon' => 'fas fa-copy',
						'link' => 'https://mage-people.com/product/woocommerce-event-duplicator-addon/#mage_product_price'
					),
					array(
						'name' => esc_html__('WooCommerce Event Coupon Code Addon', 'mage-eventpress'),
						'description' => esc_html__('Event coupon addon that is used for marketing purpose and giving discount to customer for boosting sell.', 'mage-eventpress'),
						'icon' => 'fas fa-ticket-alt',
						'link' => 'https://mage-people.com/product/woocommerce-event-coupon-code-addon/'
					),
					array(
						'name' => esc_html__('Review and Rating Addon', 'mage-eventpress'),
						'description' => esc_html__('It is a feature of adding addon designed to help added offering feedback from customers about their events.', 'mage-eventpress'),
						'icon' => 'fas fa-star',
						'link' => 'https://mage-people.com/product/review-and-rating-addon-for-event-manager/#mage_product_price'
					),
					array(
						'name' => esc_html__('Mage WP Login Page Designer', 'mage-eventpress'),
						'description' => esc_html__('This plugin that will design a nice and login panel. It will look more professional than the wordpress default login panel.', 'mage-eventpress'),
						'icon' => 'fas fa-sign-in-alt',
						'link' => 'https://mage-people.com/product/mage-wp-login-page-designer/'
					)
				);
			}
			public function faq_array() {
				return array(
					1 => array(
						'title' => esc_html__('Where can I find the Attendee registration Form?', 'mage-eventpress'),
						'des' => esc_html__('To enable attendee form you must first install a premium addon name “Form Builder”. Once you are done with installing – Click on “Events” -> Click on “All Events” -> Click on Edit of any existing event -> Scroll down below to find "Attendee Registration Form"', 'mage-eventpress')
					),
					2 => array(
						'title' => esc_html__('How can I see event wise registered attendee list?', 'mage-eventpress'),
						'des' => esc_html__(' If you visit attendee list menu in event section then you will see all attendee list here. You can filter choosing event name and date if event is recurring event.', 'mage-eventpress')
					),
					3 => array(
						'title' => esc_html__('How can I Export attendee list as CSV?', 'mage-eventpress'),
						'des' => esc_html__('If you visit attendee list menu in event section then you will see all attendee list here. You can filter choosing event name and date if event is recurring event. After filtering right section there is 2 button to export attendee and extra service.', 'mage-eventpress')
					),
					4 => array(
						'title' => esc_html__('My plugin page shows 404 error?', 'mage-eventpress'),
						'des' => esc_html__('Please re-save the permalink to solve the problem.', 'mage-eventpress')
					),
					5 => array(
						'title' => esc_html__('Where Can I change Event Slug Url?', 'mage-eventpress'),
						'des' => esc_html__('In Event Settings area we have slug changing option. You can change it and resave permalink to avoid 404 error.', 'mage-eventpress')
					),
					6 => array(
						'title' => esc_html__('Where Can I configure Pdf Email?', 'mage-eventpress'),
						'des' => esc_html__('If you visit Event settings page then You will see PDF email tab top right, you can configure pdf email here.', 'mage-eventpress')
					),
					7 => array(
						'title' => esc_html__('I have configured correctly but pdf email I am not getting.', 'mage-eventpress'),
						'des' => esc_html__('PDF email with pdf send based on some configuration. If order status processing or complete then only pdf email will send as we considered these 2 order status come after order payment done. If order status holds or pending, then email of pdf will not send.', 'mage-eventpress')
					),
					8 => array(
						'title' => esc_html__('Can I hide any section from event list and details page?', 'mage-eventpress'),
						'des' => esc_html__('Yes You can hide any section from event list and details page. If you go event settings area in general section, you will find lots of settings regarding all section.', 'mage-eventpress')
					),
					9 => array(
						'title' => esc_html__('How Can I configure Virtual Event?', 'mage-eventpress'),
						'des' => esc_html__('For virtual event we know there should not have any location or physical address so we recommend to use template virtual that we have during event adding time and also you can use location hide settings from list and details page.', 'mage-eventpress')
					),
					10 => array(
						'title' => esc_html__('I installed event manager plugin but it does not work?', 'mage-eventpress'),
						'des' => esc_html__('Please install WooCommerce plugin first, before installing any plugin.', 'mage-eventpress')
					),
					11 => array(
						'title' => esc_html__('Do you offer customization?', 'mage-eventpress'),
						'des' => esc_html__('Yes! we offer customization service for our client. If you want any new features don’t hesitate to contact us. Email: magepeopleteam@gmail.com.', 'mage-eventpress')
					),
				);
			}
		}
		new MPWEM_Welcome();
	}
