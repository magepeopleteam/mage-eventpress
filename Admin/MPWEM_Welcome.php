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
				add_submenu_page('edit.php?post_type=mep_events', __('Welcome', 'mage-eventpress'), __('<span style="color:#10dd10">Welcome</span>', 'mage-eventpress'), 'manage_options', 'mep_event_welcome_page', array($this, 'welcome_page'));
			}
			public function welcome_page() {
				?>
				<div class="wrap"></div>
				<div class="mpStyle mpwem_welcome_page">
					<div class='padding'>
						<div class="mpTabs tabBorder _dShadow_6">
							<ul class="tabLists bgLight_1">
								<li data-tabs-target="#mpwem_welcome">
									<h4><?php esc_html_e('Welcome', 'mage-eventpress'); ?></h4>
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
								<h6 class="_mT_textInfo"><?php esc_html_e('WooCommerce Event Manager Plugin for WordPress is the complete event solution. All major functions are available in this plugin which is needed in an Event booking website.', 'mage-eventpress'); ?></h6>
								<h6 class="_mT_textInfo"><?php esc_html_e('It uses WooCommerce to take payment, which provides freedom for using popular payment getaway via WooCommerce. This plugin supports all WordPress version and can be used to create any types of any types of events.', 'mage-eventpress'); ?></h6>
								<div class="_mT_40_dFlex">
									<button class="_navy_blueButton_mR_xs" type="button" data-href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/#mage_product_price">
										<?php esc_html_e('Buy Now', 'mage-eventpress'); ?>
									</button>
									<button class="_themeButton_mR_xs" type="button" data-href="https://event.mage-people.com/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
									<button class="_dButton_mR_xs" type="button" data-href="https://docs.mage-people.com/woocommerce-event-manager/"><?php esc_html_e('Documentation', 'mage-eventpress'); ?></button>
								</div>
							</div>
							<div class="col_4">
								<div class="_mAuto_max_300">
									<div class="bg_image_area">
										<div data-bg-image="<?php echo esc_attr(MPWEM_PLUGIN_URL . '/assets/helper/images/ullimited_img.png'); ?>"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="mpRow _mT_40">
							<div class="col_12">
								<h2><?php esc_html_e("Pro Features You'll Love", 'mage-eventpress'); ?></h2>
								<div class="divider"></div>
								<div class="flexWrap">
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="fas fa-tasks"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('Attendee Management', 'mage-eventpress'); ?></h5>
									</div>
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="fab fa-wpforms"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('Attendee Custom Form', 'mage-eventpress'); ?></h5>
									</div>
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="fas fa-file-pdf"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('PDF Ticketing', 'mage-eventpress'); ?></h5>
									</div>
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="far fa-envelope"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('Custom Emailing', 'mage-eventpress'); ?></h5>
									</div>
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="fas fa-user-edit"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('Attendee Edit Feature', 'mage-eventpress'); ?></h5>
									</div>
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="fas fa-file-alt"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('Attendee CSV Export', 'mage-eventpress'); ?></h5>
									</div>
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="far fa-file-alt"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('Report Overview', 'mage-eventpress'); ?></h5>
									</div>
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="fas fa-palette"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('Custom Style Settings', 'mage-eventpress'); ?></h5>
									</div>
									<div class="groupContent mpwem_pro_feature">
										<h5 class="_bgInfo_textLight_padding_xs_min_50 addonGroupContent">
											<span class="fas fa-language"></span>
										</h5>
										<h5 class="_textInfo_bgLight_padding_xs_textLeft_fullWidth"><?php esc_html_e('Translation Settings', 'mage-eventpress'); ?></h5>
									</div>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-list-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-list-style-with-search-box/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-grid-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-minimal-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-winter-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-native-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-vertical-timeline-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-horizontal-timeline-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-list-with-search-filter/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-title-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-carousel-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-spring-style/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/speakers/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/recurring-events/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-city-list/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</td>
										<td>
											<code>[event-city-list]</code>
										</td>
										<td></td>
									</tr>
									<tr>
										<td>
											Events With Pagination
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-with-pagination/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-by-single-organizer/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-filter-by-organization/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-by-single-category/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-filter-by-category/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-by-country/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-by-city/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/expired-events/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
										</th>
										<th>
											<code>[expire-event-list]</code>
										</th>
										<td></td>
									</tr>
									<tr>
										<th>
											Single Event Registration
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/single-event-registration/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-calendar/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
											<button type="button" class="dButton_xs" data-href="https://event.mage-people.com/events-calendar-pro/"><?php esc_html_e('View Demo', 'mage-eventpress'); ?></button>
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
												<h5 class="mp_faq_title" data-open-icon="fa-plus" data-close-icon="fa-minus" data-collapse-target="#mpwem_faq_datails_<?php esc_attr_e($key); ?>" data-add-class="active">
													<span data-icon class="fas fa-plus"></span>
													<?php echo esc_html($faq['title']); ?>
												</h5>
												<div data-collapse="#mpwem_faq_datails_<?php esc_attr_e($key); ?>">
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
				<div class="_bgBlack_padding_mT_40">
					<div class="allCenter">
						<h3 class="textWhite"><?php esc_html_e('Get Pro and Others Available Addon to get all these exciting features', 'mage-eventpress'); ?></h3>
						<button class="_themeButton_mL" type="button" data-href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/">
							<?php esc_html_e('Buy Now', 'mage-eventpress'); ?>
						</button>
					</div>
				</div>
				<?php
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