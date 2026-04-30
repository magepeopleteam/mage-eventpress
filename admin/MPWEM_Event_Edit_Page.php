<?php
/*
 * @Author      mage-people.com
 */

if (! defined('ABSPATH')) {
	die;
}

if (! class_exists('MPWEM_Event_Edit_Page')) {
	/**
	 * Event edit page controller.
	 */
	class MPWEM_Event_Edit_Page
	{
		private const POST_TYPE = 'mep_events';
		private const PAGE_SLUG = 'mpwem_event_edit';
		private const NONCE_ACTION_SAVE = 'mpwem_event_edit_save';
		private const NONCE_ACTION_CREATE = 'mpwem_event_edit_create';
		private const CLASSIC_BYPASS_QUERY = 'mpwem_classic';

		public function __construct()
		{
			add_action('admin_menu', [$this, 'register_menu'], 80);
			add_action('load-post.php', [$this, 'maybe_redirect_edit_screen']);
			add_action('load-post-new.php', [$this, 'maybe_redirect_new_screen']);
			add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
			add_action('admin_head', [$this, 'hide_menu_styles']);
			add_filter('admin_body_class', [$this, 'admin_body_class']);
			add_action('admin_post_mpwem_event_edit_save', [$this, 'handle_save']);
			add_action('wp_ajax_mpwem_event_edit_create', [$this, 'ajax_create_draft']);
			add_filter('get_edit_post_link', [$this, 'filter_edit_post_link'], 20, 3);
			add_filter('post_row_actions', [$this, 'filter_row_actions'], 20, 2);
			add_filter('page_row_actions', [$this, 'filter_row_actions'], 20, 2);
			add_filter('parent_file', [$this, 'filter_parent_file']);
			add_filter('submenu_file', [$this, 'filter_submenu_file'], 10, 2);
			add_filter('admin_title', [$this, 'filter_admin_title'], 10, 2);
		}

		public function register_menu(): void
		{
			add_submenu_page(
				'edit.php?post_type=' . self::POST_TYPE,
				esc_html__('Event Edit Page', 'mage-eventpress'),
				esc_html__('Event Edit Page', 'mage-eventpress'),
				'edit_posts',
				self::PAGE_SLUG,
				[$this, 'render_page']
			);
		}

		public function hide_menu_styles(): void
		{
?>
			<style>
				#adminmenu a[href="edit.php?post_type=mep_events&page=<?php echo esc_attr(self::PAGE_SLUG); ?>"] {
					display: none !important;
				}
			</style>
		<?php
		}

		/**
		 * Filters the parent menu item for the custom edit screen.
		 *
		 * @param string $parent_file Parent menu slug.
		 * @return string
		 */
		public function filter_parent_file($parent_file)
		{
			if (! $this->is_edit_screen()) {
				return $parent_file;
			}
			return 'edit.php?post_type=' . self::POST_TYPE;
		}

		/**
		 * Filters the highlighted submenu item for the custom edit screen.
		 *
		 * @param string $submenu_file Submenu slug.
		 * @param string $parent_file  Parent menu slug.
		 * @return string
		 */
		public function filter_submenu_file($submenu_file, $parent_file = '')
		{
			unset($parent_file);

			if (! $this->is_edit_screen()) {
				return $submenu_file;
			}
			return 'mep_event_lists';
		}

		public function admin_body_class($classes)
		{
			if (! $this->is_edit_screen()) {
				return $classes;
			}
			return $classes . ' mpwem-event-wizard-screen';
		}

		/**
		 * Filters the admin browser tab title for the custom event edit screen.
		 *
		 * @param string $admin_title Full admin title.
		 * @param string $title       Current page title.
		 * @return string
		 */
		public function filter_admin_title($admin_title, $title)
		{
			if (! $this->is_edit_screen()) {
				return $admin_title;
			}

			$post_id = isset($_GET['event_id']) ? absint($_GET['event_id']) : 0;
			$post    = $post_id ? get_post($post_id) : null;

			$screen_title = $post_id && $post ? $post->post_title : __('Create Event', 'mage-eventpress');
			if ($post_id && '' === trim((string) $screen_title)) {
				$screen_title = sprintf(__('Edit Event #%d', 'mage-eventpress'), $post_id);
			}

			return str_replace($title, $screen_title, $admin_title);
		}

		private function is_edit_screen(): bool
		{
			return is_admin()
				&& isset($_GET['page'])
				&& sanitize_key(wp_unslash($_GET['page'])) === self::PAGE_SLUG;
		}

		private function is_classic_bypass(): bool
		{
			return isset($_GET[self::CLASSIC_BYPASS_QUERY]) && (string) wp_unslash($_GET[self::CLASSIC_BYPASS_QUERY]) === '1';
		}

		/**
		 * Builds the custom edit page URL.
		 *
		 * @param int    $post_id  Event post ID.
		 * @param string $step_key Wizard step key.
		 * @return string
		 */
		private function edit_url(int $post_id = 0, string $step_key = 'basic'): string
		{
			$base = admin_url('edit.php?post_type=' . self::POST_TYPE . '&page=' . self::PAGE_SLUG);
			$step = sanitize_key($step_key) ?: 'basic';
			if ($post_id > 0) {
				return $base . '&event_id=' . $post_id . '#/events/edit/' . $post_id . '/' . $step;
			}
			return $base . '#/events/new/' . $step;
		}

		/**
		 * Checks whether the current user can manage events in the custom UI.
		 *
		 * @return bool
		 */
		private function can_manage_events(): bool
		{
			return current_user_can('edit_posts') || current_user_can('manage_woocommerce');
		}

		/**
		 * Gets a stable asset version for the given plugin-relative file.
		 *
		 * @param string $relative_path Asset path relative to the plugin root.
		 * @return string
		 */
		private function get_asset_version(string $relative_path): string
		{
			$asset_path = trailingslashit(MPWEM_PLUGIN_DIR) . ltrim($relative_path, '/');

			if (file_exists($asset_path)) {
				$filemtime = filemtime($asset_path);

				if (false !== $filemtime) {
					return (string) $filemtime;
				}
			}

			return '1.0.0';
		}

		/**
		 * Gets the classic post editor URL for an event.
		 *
		 * @param int $post_id Event post ID.
		 * @return string
		 */
		private function get_classic_edit_url(int $post_id): string
		{
			return admin_url(
				sprintf(
					'post.php?post=%d&action=edit&%s=1',
					$post_id,
					self::CLASSIC_BYPASS_QUERY
				)
			);
		}

		public function maybe_redirect_edit_screen(): void
		{
			if (defined('DOING_AJAX') && DOING_AJAX) {
				return;
			}
			if (! is_admin() || $this->is_edit_screen() || $this->is_classic_bypass()) {
				return;
			}
			if (! $this->can_manage_events()) {
				return;
			}

			$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
			if (! $post_id || get_post_type($post_id) !== self::POST_TYPE) {
				return;
			}
			if (! current_user_can('edit_post', $post_id)) {
				return;
			}

			wp_safe_redirect($this->edit_url($post_id, 'basic'));
			exit;
		}

		public function maybe_redirect_new_screen(): void
		{
			if (defined('DOING_AJAX') && DOING_AJAX) {
				return;
			}
			if (! is_admin() || $this->is_edit_screen() || $this->is_classic_bypass()) {
				return;
			}
			if (! $this->can_manage_events()) {
				return;
			}

			$post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : '';
			if ($post_type !== self::POST_TYPE) {
				return;
			}

			wp_safe_redirect($this->edit_url(0, 'basic'));
			exit;
		}

		public function filter_edit_post_link($link, $post_id, $context)
		{
			unset($context);

			if (! $post_id || get_post_type($post_id) !== self::POST_TYPE) {
				return $link;
			}
			// Keep classic editor available by explicitly requesting it.
			if ($this->is_classic_bypass()) {
				return $link;
			}
			return $this->edit_url($post_id, 'basic');
		}

		public function filter_row_actions($actions, $post)
		{
			if (! $post || $post->post_type !== self::POST_TYPE) {
				return $actions;
			}
			if (isset($actions['edit'])) {
				$actions['edit'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url($this->edit_url((int) $post->ID, 'basic')),
					esc_html__('Edit', 'mage-eventpress')
				);
			}
			$actions['classic'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url($this->get_classic_edit_url((int) $post->ID)),
				esc_html__('Classic Editor', 'mage-eventpress')
			);
			return $actions;
		}

		public function enqueue_assets($hook): void
		{
			unset($hook);

			if (! $this->is_edit_screen()) {
				return;
			}

			wp_enqueue_media();

			wp_enqueue_style(
				'mpwem_event_edit',
				MPWEM_PLUGIN_URL . '/assets/admin/mpwem_event_edit.css',
				['mpwem_admin'],
				$this->get_asset_version('assets/admin/mpwem_event_edit.css')
			);

			wp_enqueue_script(
				'mpwem_event_edit',
				MPWEM_PLUGIN_URL . '/assets/admin/mpwem_event_edit.js',
				['jquery', 'mpwem_admin'],
				$this->get_asset_version('assets/admin/mpwem_event_edit.js'),
				true
			);

			wp_localize_script(
				'mpwem_event_edit',
				'mpwemEventEdit',
				[
					'ajax_url'      => admin_url('admin-ajax.php'),
					'admin_nonce'   => wp_create_nonce('mpwem_admin_nonce'),
					'create_nonce'  => wp_create_nonce(self::NONCE_ACTION_CREATE),
					'page_url'      => admin_url('edit.php?post_type=' . self::POST_TYPE . '&page=' . self::PAGE_SLUG),
				]
			);
		}

		public function render_page(): void
		{
			if (! $this->can_manage_events()) {
				wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'mage-eventpress'));
			}

			$post_id = isset($_GET['event_id']) ? absint($_GET['event_id']) : 0;
			$post    = $post_id ? get_post($post_id) : null;

			if ($post_id && (! $post || $post->post_type !== self::POST_TYPE)) {
				wp_die(esc_html__('Invalid event ID.', 'mage-eventpress'));
			}

			if ($post_id && ! current_user_can('edit_post', $post_id)) {
				wp_die(esc_html__('Sorry, you are not allowed to edit this event.', 'mage-eventpress'));
			}

			$steps = [
				['key' => 'basic', 'label' => __('Basic', 'mage-eventpress'), 'panel' => '#mpwem_wizard_basic'],
				['key' => 'tickets', 'label' => __('Ticket & Pricing', 'mage-eventpress'), 'panel' => '#mpwem_wizard_tickets'],
				['key' => 'date', 'label' => __('Date & Time', 'mage-eventpress'), 'panel' => '#mpwem_wizard_date'],
				['key' => 'display', 'label' => __('Advanced', 'mage-eventpress'), 'panel' => '#mpwem_wizard_display'],
			];
			$steps = apply_filters('mpwem_event_edit_steps', $steps, $post_id);

			$event_infos = $post_id ? MPWEM_Functions::get_all_info($post_id) : [];

			$back_to_list = admin_url('edit.php?post_type=' . self::POST_TYPE);
			$screen_title = $post_id && $post ? $post->post_title : __('Create Event', 'mage-eventpress');
			if ($post_id && '' === trim((string) $screen_title)) {
				$screen_title = sprintf(__('Edit Event #%d', 'mage-eventpress'), $post_id);
			}
			$featured_id  = $post_id ? (int) get_post_thumbnail_id($post_id) : 0;
			$featured_url = $featured_id ? wp_get_attachment_image_url($featured_id, 'medium') : '';
			$classical_url = $post_id ? $this->get_classic_edit_url($post_id) : '';
			$frontend_url = $post_id ? get_permalink($post_id) : '';

		?>
			<div class="mpwem-event-wizard is-loading" data-event-id="<?php echo esc_attr($post_id); ?>">
				<div class="mpwem-event-wizard__skeleton" aria-hidden="true">
					<div class="mpwem-skeleton-topbar">
						<span class="mpwem-skeleton-line mpwem-skeleton-line--back"></span>
						<div class="mpwem-skeleton-title-group">
							<span class="mpwem-skeleton-line mpwem-skeleton-line--title"></span>
							<span class="mpwem-skeleton-line mpwem-skeleton-line--subtitle"></span>
						</div>
						<span class="mpwem-skeleton-line mpwem-skeleton-line--action"></span>
					</div>
					<div class="mpwem-skeleton-steps">
						<?php foreach ($steps as $step) : ?>
							<span class="mpwem-skeleton-pill"></span>
						<?php endforeach; ?>
					</div>
					<div class="mpwem-skeleton-layout">
						<div class="mpwem-skeleton-main">
							<div class="mpwem-skeleton-card">
								<div class="mpwem-skeleton-card__head">
									<span class="mpwem-skeleton-line mpwem-skeleton-line--card-title"></span>
									<span class="mpwem-skeleton-line mpwem-skeleton-line--card-subtitle"></span>
								</div>
								<div class="mpwem-skeleton-advanced-list">
									<?php for ($i = 0; $i < 6; $i++) : ?>
										<div class="mpwem-skeleton-section">
											<div class="mpwem-skeleton-section__head">
												<span class="mpwem-skeleton-block mpwem-skeleton-block--badge"></span>
												<div class="mpwem-skeleton-section__text">
													<span class="mpwem-skeleton-line mpwem-skeleton-line--section-title"></span>
													<span class="mpwem-skeleton-line mpwem-skeleton-line--section-copy"></span>
												</div>
												<span class="mpwem-skeleton-block mpwem-skeleton-block--toggle"></span>
											</div>
											<?php if ($i === 0) : ?>
												<div class="mpwem-skeleton-template-grid">
													<span class="mpwem-skeleton-block mpwem-skeleton-block--template"></span>
													<span class="mpwem-skeleton-block mpwem-skeleton-block--template"></span>
													<span class="mpwem-skeleton-block mpwem-skeleton-block--template"></span>
												</div>
											<?php else : ?>
												<div class="mpwem-skeleton-section__body">
													<span class="mpwem-skeleton-block mpwem-skeleton-block--input"></span>
												</div>
											<?php endif; ?>
										</div>
									<?php endfor; ?>
								</div>
							</div>
							<div class="mpwem-skeleton-card">
								<div class="mpwem-skeleton-card__head">
									<span class="mpwem-skeleton-line mpwem-skeleton-line--card-title"></span>
									<span class="mpwem-skeleton-line mpwem-skeleton-line--card-subtitle"></span>
								</div>
								<div class="mpwem-skeleton-grid">
									<span class="mpwem-skeleton-block mpwem-skeleton-block--input"></span>
									<span class="mpwem-skeleton-block mpwem-skeleton-block--input"></span>
									<span class="mpwem-skeleton-block mpwem-skeleton-block--input"></span>
									<span class="mpwem-skeleton-block mpwem-skeleton-block--input"></span>
								</div>
							</div>
						</div>
						<div class="mpwem-skeleton-sidebar">
							<div class="mpwem-skeleton-card">
								<div class="mpwem-skeleton-card__head">
									<span class="mpwem-skeleton-line mpwem-skeleton-line--card-title"></span>
									<span class="mpwem-skeleton-line mpwem-skeleton-line--card-subtitle"></span>
								</div>
								<div class="mpwem-skeleton-guide">
									<?php for ($i = 0; $i < 6; $i++) : ?>
										<div class="mpwem-skeleton-guide__item">
											<span class="mpwem-skeleton-block mpwem-skeleton-block--guide-dot"></span>
											<div class="mpwem-skeleton-guide__text">
												<span class="mpwem-skeleton-line mpwem-skeleton-line--guide-title"></span>
												<span class="mpwem-skeleton-line mpwem-skeleton-line--guide-copy"></span>
											</div>
										</div>
									<?php endfor; ?>
								</div>
							</div>
							<div class="mpwem-skeleton-card">
								<div class="mpwem-skeleton-card__head">
									<span class="mpwem-skeleton-line mpwem-skeleton-line--card-title"></span>
									<span class="mpwem-skeleton-line mpwem-skeleton-line--card-subtitle"></span>
								</div>
								<span class="mpwem-skeleton-block mpwem-skeleton-block--image"></span>
							</div>
						</div>
					</div>
				</div>
				<header class="mpwem-event-wizard__topbar">
					<div class="mpwem-event-wizard__topbar-inner">
						<a class="mpwem-link" href="<?php echo esc_url($back_to_list); ?>">
							<span class="dashicons dashicons-arrow-left-alt"></span>
							<?php esc_html_e('Back to Events', 'mage-eventpress'); ?>
						</a>
						<div class="mpwem-event-wizard__title">
							<h1 title="<?php echo esc_attr($screen_title); ?>"><?php echo esc_html($screen_title); ?></h1>
							<?php if ($frontend_url) : ?>
								<p class="description mpwem-event-wizard__frontend-url">
									<?php esc_html_e('Frontend URL:', 'mage-eventpress'); ?>
									<a href="<?php echo esc_url($frontend_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($frontend_url); ?></a>
								</p>
							<?php else : ?>
								<p class="description mpwem-event-wizard__frontend-url">
									<?php esc_html_e('Frontend URL will be available after creating this event.', 'mage-eventpress'); ?>
								</p>
							<?php endif; ?>
						</div>
						<div class="mpwem-event-wizard__actions">
							<?php if ($classical_url) : ?>
								<a class="mpwem-link" href="<?php echo esc_url($classical_url); ?>"><?php esc_html_e('Classic editor', 'mage-eventpress'); ?></a>
							<?php endif; ?>
							<button type="button" class="button mpwem-wizard-save-draft"><?php esc_html_e('Save', 'mage-eventpress'); ?></button>
						</div>
					</div>
				</header>

				<nav class="mpwem-event-wizard__steps" aria-label="<?php esc_attr_e('Event steps', 'mage-eventpress'); ?>">
					<div class="mpwem-event-wizard__steps-inner">
						<?php foreach ($steps as $step) : ?>
							<button
								type="button"
								class="mpwem-step"
								data-step-key="<?php echo esc_attr($step['key']); ?>"
								data-panel="<?php echo esc_attr($step['panel']); ?>">
								<span class="mpwem-step__label"><?php echo esc_html($step['label']); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</nav>

				<main class="mpwem-event-wizard__main">
					<div class="mpwem-event-wizard__container">
						<div class="mpwem-wizard-notice" aria-live="polite" style="display:none;"></div>

						<?php if (! $post_id) : ?>
							<div class="mpwem-wizard-empty">
								<p><?php esc_html_e('Create a new event to start editing.', 'mage-eventpress'); ?></p>
								<button type="button" class="button button-primary mpwem-wizard-create">
									<?php esc_html_e('Create New Event', 'mage-eventpress'); ?>
								</button>
							</div>
						<?php else : ?>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="mpwem-event-edit-form">
								<input type="hidden" name="action" value="mpwem_event_edit_save" />
								<input type="hidden" name="post_ID" value="<?php echo esc_attr($post_id); ?>" />
								<?php wp_nonce_field(self::NONCE_ACTION_SAVE, '_mpwem_edit_nonce'); ?>
								<?php wp_nonce_field('mpwem_type_nonce', 'mpwem_type_nonce'); ?>

								<div class="mpwem-event-wizard__content">
									<div class="mpwem-wizard-panels">
										<!-- Step 1: Basic -->
										<section class="mpwem-wizard-panel mp_tab_item" data-tab-item="#mpwem_wizard_basic">
											<div class="mpwem-event-wizard__grid">
												<div class="mpwem-event-wizard__main">
													<div class="mpwem-card">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Basic Information', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('The core details of your event.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<label class="mpwem-field">
																<span class="mpwem-field__label"><?php esc_html_e('Event Title', 'mage-eventpress'); ?> <span class="required">*</span></span>
																<input id="title" name="post_title" type="text" class="regular-text mpwem-input" value="<?php echo esc_attr($post ? $post->post_title : ''); ?>" required />
															</label>

															<div class="mpwem-field">
																<span class="mpwem-field__label"><?php esc_html_e('Description', 'mage-eventpress'); ?></span>
																<?php
																wp_editor(
																	$post ? $post->post_content : '',
																	'mpwem_wizard_content',
																	[
																		'textarea_name' => 'content',
																		'textarea_rows' => 10,
																		'media_buttons' => true,
																	]
																);
																?>
															</div>
														</div>
													</div>
													<div class="mpwem-card">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Venue/Location', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Where will this event take place?', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<div class="mpwem-panel-mount" id="mpwem_wizard_venue_mount"></div>
														</div>
													</div>
												</div>
												<aside class="mpwem-event-wizard__sidebar">
													<div class="mpwem-card">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Featured Image', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Main event thumbnail.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<input type="hidden" name="_thumbnail_id" id="mpwem_featured_image_id" value="<?php echo esc_attr($featured_id); ?>" />
															<div class="mpwem-featured-image" data-has-image="<?php echo esc_attr($featured_id ? '1' : '0'); ?>">
																<div class="mpwem-featured-image__preview">
																	<?php if ($featured_url) : ?>
																		<img src="<?php echo esc_url($featured_url); ?>" alt="" />
																	<?php else : ?>
																		<div class="mpwem-featured-image__placeholder"><?php esc_html_e('No image selected', 'mage-eventpress'); ?></div>
																	<?php endif; ?>
																</div>
																<div class="mpwem-featured-image__actions">
																	<button type="button" class="button button-primary mpwem-featured-select"><?php esc_html_e('Select', 'mage-eventpress'); ?></button>
																	<button type="button" class="button mpwem-featured-remove" <?php echo $featured_id ? '' : 'disabled'; ?>>
																		<?php esc_html_e('Remove', 'mage-eventpress'); ?>
																	</button>
																</div>
															</div>
														</div>
													</div>
													<div class="mpwem-card" id="mpwem_gallery_images_card">
														<div class="mpwem-card__head">
															<div class="mpwem-card__head-copy">
																<h2><?php esc_html_e('Gallery Images', 'mage-eventpress'); ?></h2>
																<p><?php esc_html_e('Additional photos.', 'mage-eventpress'); ?></p>
															</div>
															<div class="mpwem-card__head-actions" id="mpwem_gallery_images_card_toggle"></div>
														</div>
														<div class="mpwem-card__body">
															<div class="mpwem-media-mount" id="mpwem_wizard_media_mount_basic"></div>
														</div>
													</div>
													<div class="mpwem-card">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Event List Thumbnail', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Thumbnail used in event listing layouts.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<div class="mpwem-media-mount" id="mpwem_wizard_thumbnail_mount_basic"></div>
														</div>
													</div>
												</aside>
											</div>
										</section>

										<!-- Step 2: Tickets -->
										<section class="mpwem-wizard-panel mp_tab_item" data-tab-item="#mpwem_wizard_tickets">
											<div class="mpwem-event-wizard__grid">
												<div class="mpwem-event-wizard__main">
													<div class="mpwem-panel-mount mpwem-ticket-legacy-mount" id="mpwem_wizard_tickets_mount"></div>
													<div class="mpwem-card" id="mpwem_wizard_extra_services_card" style="display:none;">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Extra Services', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Optional add-ons that attendees can purchase with tickets.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<div class="mpwem-panel-mount" id="mpwem_wizard_extra_services_mount"></div>
														</div>
													</div>
													<div class="mpwem-ticket-modal" id="mpwem_ticket_editor_modal" aria-hidden="true">
														<div class="mpwem-ticket-modal__backdrop" data-mpwem-ticket-modal-close></div>
														<div class="mpwem-ticket-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem_ticket_modal_title">
															<div class="mpwem-ticket-modal__header">
																<div class="mpwem-ticket-modal__header-copy">
																	<span class="mpwem-ticket-modal__eyebrow"><?php esc_html_e('Ticket Editor', 'mage-eventpress'); ?></span>
																	<h3 id="mpwem_ticket_modal_title"><?php esc_html_e('Manage ticket types', 'mage-eventpress'); ?></h3>
																	<p id="mpwem_ticket_modal_description"><?php esc_html_e('Edit pricing, capacities, advanced columns, and ticket settings without leaving this step.', 'mage-eventpress'); ?></p>
																</div>
																<div class="mpwem-ticket-modal__header-actions">
																	<div id="mpwem_ticket_modal_advance_toggle"></div>
																	<button type="button" class="mpwem-ticket-modal__close" aria-label="<?php esc_attr_e('Close ticket editor', 'mage-eventpress'); ?>" data-mpwem-ticket-modal-close>
																		<span class="dashicons dashicons-no-alt"></span>
																	</button>
																</div>
															</div>
															<div class="mpwem-ticket-modal__body">
																<div class="mpwem-ticket-modal__mount" id="mpwem_ticket_modal_mount"></div>
															</div>
														</div>
													</div>
												</div>
												<aside class="mpwem-event-wizard__sidebar">
													<div class="mpwem-card mpwem-card--help mpwem-card--help-pricing">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Pricing Help', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Quick tips for tickets.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<p class="description"><?php esc_html_e('Ensure you have at least one ticket type defined to allow registrations.', 'mage-eventpress'); ?></p>
														</div>
													</div>
												</aside>
											</div>
										</section>

										<!-- Step 3: Date & Time -->
										<section class="mpwem-wizard-panel mp_tab_item" data-tab-item="#mpwem_wizard_date">
											<div class="mpwem-event-wizard__grid">
												<div class="mpwem-event-wizard__main">
													<div class="mpwem-card">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Date & Time', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Schedule your event start and end times.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<div class="mpwem-panel-mount" id="mpwem_wizard_date_mount"></div>
														</div>
													</div>
												</div>
												<aside class="mpwem-event-wizard__sidebar">
													<div class="mpwem-card mpwem-card--help mpwem-card--help-schedule">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Schedule Summary', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Review your event dates.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<p class="description"><?php esc_html_e('Check for potential schedule conflicts with other events.', 'mage-eventpress'); ?></p>
														</div>
													</div>
												</aside>
											</div>
										</section>

										<!-- Step 4: Advanced -->
										<section class="mpwem-wizard-panel mp_tab_item" data-tab-item="#mpwem_wizard_display">
											<div class="mpwem-event-wizard__grid">
												<div class="mpwem-event-wizard__main">
													<div class="mpwem-card">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Advanced', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Templates, messaging, timeline, related events, and search visibility.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body mpwem-display-stack">
															<div class="mpwem-panel-mount" id="mpwem_wizard_template_mount"></div>
															<div class="mpwem-panel-mount" id="mpwem_wizard_faq_mount"></div>
															<div class="mpwem-panel-mount" id="mpwem_wizard_timeline_mount"></div>
															<div class="mpwem-panel-mount" id="mpwem_wizard_related_mount"></div>
															<div class="mpwem-panel-mount" id="mpwem_wizard_email_mount"></div>
															<div class="mpwem-panel-mount" id="mpwem_wizard_seo_mount"></div>
														</div>
													</div>
												</div>
												<aside class="mpwem-event-wizard__sidebar">
													<div class="mpwem-card mpwem-card--help mpwem-card--help-display">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Quick Guide', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('A simple guide for the Advanced step sections.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<p class="description"><?php esc_html_e('Review each section below and enable only the parts this event actually needs.', 'mage-eventpress'); ?></p>
														</div>
													</div>
													<div class="mpwem-card mpwem-card--danger mpwem-card--danger-advanced" id="mpwem_advanced_danger_zone">
														<div class="mpwem-card__head">
															<h2><?php esc_html_e('Danger Zone', 'mage-eventpress'); ?></h2>
															<p><?php esc_html_e('Use with care.', 'mage-eventpress'); ?></p>
														</div>
														<div class="mpwem-card__body">
															<div id="mpwem_wizard_danger_mount"></div>
														</div>
													</div>
												</aside>
											</div>
										</section>

										<?php
										do_action('mpwem_event_tab_setting_item', $post_id, $event_infos);
										do_action('mep_admin_event_details_before_tab_details_location', $post_id);
										do_action('mp_event_all_in_tab_item', $post_id);
										?>
									</div>
									<div class="mpwem-edit-page-additional" id="mpwem_edit_page_additional" style="display:none;">
										<div class="mpwem-card mpwem-card--help mpwem-card--help-additional">
											<div class="mpwem-card__head">
												<h2><?php esc_html_e('Additional Sections', 'mage-eventpress'); ?></h2>
												<p><?php esc_html_e('Left in the current design for now until we place them into the new step flow.', 'mage-eventpress'); ?></p>
											</div>
											<div class="mpwem-card__body">
												<div class="mpwem-additional-panels" id="mpwem_additional_sections_mount"></div>
											</div>
										</div>
									</div>
								</div>
								<footer class="mpwem-event-wizard__footer">
									<div class="mpwem-event-wizard__footer-inner">
										<button type="button" class="button mpwem-wizard-prev"><?php esc_html_e('Back', 'mage-eventpress'); ?></button>
										<div class="mpwem-event-wizard__footer-center">
											<span class="mpwem-wizard-progress" aria-live="polite"></span>
										</div>
										<button type="button" class="button button-primary mpwem-wizard-next"><?php esc_html_e('Next', 'mage-eventpress'); ?></button>
									</div>
								</footer>
								<div class="mpwem-confirm-modal" id="mpwem_reset_booking_modal" aria-hidden="true">
									<div class="mpwem-confirm-modal__backdrop"></div>
									<div class="mpwem-confirm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mpwem_reset_booking_modal_title">
										<div class="mpwem-confirm-modal__head">
											<h3 id="mpwem_reset_booking_modal_title"><?php esc_html_e('Reset Booking Count?', 'mage-eventpress'); ?></h3>
											<button type="button" class="mpwem-confirm-modal__close" aria-label="<?php esc_attr_e('Close confirmation dialog', 'mage-eventpress'); ?>">&times;</button>
										</div>
										<div class="mpwem-confirm-modal__body">
											<p><?php esc_html_e('This will remove all booking information for this event, including the attendee list. This action cannot be undone.', 'mage-eventpress'); ?></p>
										</div>
										<div class="mpwem-confirm-modal__actions">
											<button type="button" class="button mpwem-confirm-cancel"><?php esc_html_e('Cancel', 'mage-eventpress'); ?></button>
											<button type="button" class="button mpwem-btn-danger mpwem-confirm-reset"><?php esc_html_e('Yes, Reset Booking', 'mage-eventpress'); ?></button>
										</div>
									</div>
								</div>
							</form>
						<?php endif; ?>
					</div>
				</main>
			</div>
<?php
		}

		public function handle_save(): void
		{
			if (! $this->can_manage_events()) {
				wp_die(esc_html__('Sorry, you are not allowed to do that.', 'mage-eventpress'));
			}

			check_admin_referer(self::NONCE_ACTION_SAVE, '_mpwem_edit_nonce');

			$post_id = isset($_POST['post_ID']) ? absint($_POST['post_ID']) : 0;
			if (! $post_id || get_post_type($post_id) !== self::POST_TYPE) {
				wp_die(esc_html__('Invalid event.', 'mage-eventpress'));
			}
			if (! current_user_can('edit_post', $post_id)) {
				wp_die(esc_html__('Sorry, you are not allowed to edit this event.', 'mage-eventpress'));
			}

			$post_title   = isset($_POST['post_title']) ? sanitize_text_field(wp_unslash($_POST['post_title'])) : '';
			$post_content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';
			$thumb_id     = isset($_POST['_thumbnail_id']) ? absint($_POST['_thumbnail_id']) : 0;

			$updated_post = wp_update_post(
				[
					'ID'           => $post_id,
					'post_title'   => $post_title,
					'post_content' => $post_content,
				],
				true
			);

			if (is_wp_error($updated_post)) {
				wp_die(esc_html($updated_post->get_error_message()));
			}

			if (function_exists('set_post_thumbnail') && function_exists('delete_post_thumbnail')) {
				if ($thumb_id > 0) {
					set_post_thumbnail($post_id, $thumb_id);
				} else {
					delete_post_thumbnail($post_id);
				}
			} else {
				update_post_meta($post_id, '_thumbnail_id', $thumb_id ? $thumb_id : '');
			}

			$redirect = add_query_arg(
				[
					'post_type' => self::POST_TYPE,
					'page'      => self::PAGE_SLUG,
					'event_id'  => $post_id,
					'saved'     => 1,
				],
				admin_url('edit.php')
			);
			wp_safe_redirect($redirect);
			exit;
		}

		public function ajax_create_draft(): void
		{
			if (! $this->can_manage_events()) {
				wp_send_json_error(['message' => __('Not allowed.', 'mage-eventpress')], 403);
			}
			check_ajax_referer(self::NONCE_ACTION_CREATE, 'nonce');

			$post_id = wp_insert_post(
				[
					'post_type'   => self::POST_TYPE,
					'post_status' => 'draft',
					'post_title'  => __('New Event', 'mage-eventpress'),
				],
				true
			);

			if (is_wp_error($post_id)) {
				wp_send_json_error(['message' => $post_id->get_error_message()], 500);
			}

			wp_send_json_success(['event_id' => (int) $post_id]);
		}
	}

	new MPWEM_Event_Edit_Page();
}
