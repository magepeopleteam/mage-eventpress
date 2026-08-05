<?php
	/*
	 * Modern reskin of secondary Event CPT list screens (edit.php):
	 * Speakers, Waitlist Email Templates, Global Reg Forms, Reviews.
	 * Presentation-only — Add/Edit still go to post-new.php / post.php
	 * because those screens have rich editors and meta boxes.
	 *
	 * @Author MagePeople Team
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'MPWEM_Post_List_Modern' ) ) {
		class MPWEM_Post_List_Modern {
			const POST_TYPES = [
				'mep_event_speaker',
				'mep_waitlist_email',
				'mep_events_reg_form',
				'mep_events_review',
			];

			const SPEAKER_PER_PAGE = 10;

			public function __construct() {
				add_filter( 'screen_options_show_screen', [ $this, 'maybe_hide_screen_options' ], 10, 2 );
				add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
				add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_editor' ] );
				add_action( 'all_admin_notices', [ $this, 'render_editor_back_button' ] );
				add_filter( 'manage_mep_event_speaker_posts_columns', [ $this, 'speaker_columns' ] );
				add_action( 'manage_mep_event_speaker_posts_custom_column', [ $this, 'speaker_column_content' ], 10, 2 );
				add_filter( 'edit_posts_per_page', [ $this, 'force_speaker_per_page' ], 10, 2 );
				add_filter( 'get_user_option_edit_mep_event_speaker_per_page', [ $this, 'force_speaker_user_per_page' ] );
				add_action( 'wp_ajax_mpwem_speaker_list_paginate', [ $this, 'ajax_speaker_list_paginate' ] );
				add_action( 'wp_ajax_mpwem_speaker_create', [ $this, 'ajax_speaker_create' ] );
				add_action( 'wp_ajax_mpwem_speaker_get', [ $this, 'ajax_speaker_get' ] );
				add_action( 'wp_ajax_mpwem_speaker_update', [ $this, 'ajax_speaker_update' ] );
				add_action( 'wp_ajax_mpwem_speaker_delete', [ $this, 'ajax_speaker_delete' ] );
				add_action( 'wp_ajax_mpwem_waitlist_email_preview', [ $this, 'ajax_waitlist_email_preview' ] );
			}

			private function current_target_post_type() {
				if ( ! function_exists( 'get_current_screen' ) ) {
					return null;
				}
				$screen = get_current_screen();
				if ( ! $screen || empty( $screen->post_type ) || 'edit' !== $screen->base ) {
					return null;
				}
				if ( in_array( $screen->post_type, self::POST_TYPES, true ) ) {
					return $screen->post_type;
				}

				return null;
			}

			/**
			 * post.php / post-new.php for secondary Event CPTs (templates, speakers, etc.).
			 */
			private function current_editor_post_type() {
				if ( ! function_exists( 'get_current_screen' ) ) {
					return null;
				}
				$screen = get_current_screen();
				if ( ! $screen || empty( $screen->post_type ) || ! in_array( $screen->base, [ 'post', 'post-new' ], true ) ) {
					return null;
				}
				if ( in_array( $screen->post_type, self::POST_TYPES, true ) ) {
					return $screen->post_type;
				}

				return null;
			}

			private function editor_back_label( $post_type ) {
				$map = [
					'mep_waitlist_email'  => __( 'Back to Template List', 'mage-eventpress' ),
					'mep_event_speaker'   => __( 'Back to Speakers', 'mage-eventpress' ),
					'mep_events_reg_form' => __( 'Back to Forms', 'mage-eventpress' ),
					'mep_events_review'   => __( 'Back to Reviews', 'mage-eventpress' ),
				];

				return isset( $map[ $post_type ] ) ? $map[ $post_type ] : __( 'Back to List', 'mage-eventpress' );
			}

			public function enqueue_editor() {
				$post_type = $this->current_editor_post_type();
				if ( ! $post_type ) {
					return;
				}

				wp_enqueue_style(
					'mpwem-post-list-modern',
					MPWEM_PLUGIN_URL . '/assets/admin/css/mpwem-post-list-modern.css',
					[],
					$this->asset_ver( '/assets/admin/css/mpwem-post-list-modern.css' )
				);
			}

			public function render_editor_back_button() {
				$post_type = $this->current_editor_post_type();
				if ( ! $post_type ) {
					return;
				}

				$list_url = admin_url( 'edit.php?post_type=' . $post_type );
				$label    = $this->editor_back_label( $post_type );
				?>
				<div class="mpwem-editor-back-bar">
					<a class="mpwem-editor-back-btn" href="<?php echo esc_url( $list_url ); ?>">
						<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
						<span><?php echo esc_html( $label ); ?></span>
					</a>
				</div>
				<?php
			}

			public function maybe_hide_screen_options( $show_screen, $screen ) {
				if ( isset( $screen->post_type, $screen->base )
					&& 'edit' === $screen->base
					&& in_array( $screen->post_type, self::POST_TYPES, true )
				) {
					return false;
				}

				return $show_screen;
			}

			private function asset_ver( $rel_path ) {
				$file = MPWEM_PLUGIN_DIR . $rel_path;

				return file_exists( $file ) ? (string) filemtime( $file ) : MPWEM_PLUGIN_VERSION;
			}

			private function copy_for( $post_type ) {
				$map = [
					'mep_event_speaker'   => [
						'subheading' => __( 'Add and manage speakers you can assign to events. Profiles appear on event pages where speakers are featured.', 'mage-eventpress' ),
					],
					'mep_waitlist_email'  => [
						'subheading' => __( 'Create reusable email templates for waitlist notifications. Use dynamic tags so each message stays personalized.', 'mage-eventpress' ),
					],
					'mep_events_reg_form' => [
						'subheading' => __( 'Build global registration forms and field presets that events can reuse for attendee checkout.', 'mage-eventpress' ),
					],
					'mep_events_review'   => [
						'subheading' => __( 'Moderate attendee reviews and ratings submitted for your events.', 'mage-eventpress' ),
					],
				];

				return isset( $map[ $post_type ] ) ? $map[ $post_type ] : [ 'subheading' => '' ];
			}

			/**
			 * Speakers list: Image + Description + Event columns (no Date).
			 *
			 * @param array $columns Existing columns.
			 * @return array
			 */
			public function speaker_columns( $columns ) {
				$new = [];
				foreach ( $columns as $key => $label ) {
					if ( 'cb' === $key ) {
						$new[ $key ] = $label;
						continue;
					}
					if ( 'date' === $key ) {
						continue;
					}
					if ( 'title' === $key ) {
						$new['mep_speaker_image'] = __( 'Image', 'mage-eventpress' );
						$new[ $key ]             = $label;
						$new['mep_speaker_desc']  = __( 'Description', 'mage-eventpress' );
						$new['mep_speaker_event'] = __( 'Event', 'mage-eventpress' );
						continue;
					}
					$new[ $key ] = $label;
				}
				if ( ! isset( $new['mep_speaker_image'] ) ) {
					$new = array_merge(
						[ 'mep_speaker_image' => __( 'Image', 'mage-eventpress' ) ],
						$new
					);
				}
				if ( ! isset( $new['mep_speaker_desc'] ) ) {
					$new['mep_speaker_desc'] = __( 'Description', 'mage-eventpress' );
				}
				if ( ! isset( $new['mep_speaker_event'] ) ) {
					$new['mep_speaker_event'] = __( 'Event', 'mage-eventpress' );
				}
				unset( $new['date'] );

				return $new;
			}

			/**
			 * Render Speakers list custom columns.
			 *
			 * @param string $column  Column key.
			 * @param int    $post_id Speaker post ID.
			 */
			public function speaker_column_content( $column, $post_id ) {
				$post_id = absint( $post_id );
				if ( ! $post_id ) {
					return;
				}

				if ( 'mep_speaker_image' === $column ) {
					$thumb = get_the_post_thumbnail(
						$post_id,
						[ 72, 72 ],
						[
							'class' => 'mpwem-speaker-list-thumb',
							'alt'   => esc_attr( wp_specialchars_decode( get_the_title( $post_id ), ENT_QUOTES ) ),
						]
					);
					if ( $thumb ) {
						echo wp_kses_post( $thumb );
					} else {
						echo '<span class="mpwem-speaker-list-thumb mpwem-speaker-list-thumb--empty" aria-hidden="true"><span class="dashicons dashicons-admin-users"></span></span>';
					}
					return;
				}

				if ( 'mep_speaker_desc' === $column ) {
					$post = get_post( $post_id );
					if ( ! $post ) {
						echo '<span class="mpwem-speaker-list-desc-empty">&mdash;</span>';
						return;
					}
					$excerpt = trim( (string) $post->post_excerpt );
					$body    = trim( wp_strip_all_tags( (string) $post->post_content ) );
					$role    = $excerpt;
					$bio     = $body;
					if ( ! $role && $body ) {
						$role = wp_trim_words( $body, 12, '…' );
						$bio  = '';
					}
					if ( ! $role && ! $bio ) {
						echo '<span class="mpwem-speaker-list-desc-empty">' . esc_html__( 'No description yet.', 'mage-eventpress' ) . '</span>';
						return;
					}
					echo '<div class="mpwem-speaker-list-desc">';
					if ( $role ) {
						echo '<div class="mpwem-speaker-list-role">' . esc_html( $role ) . '</div>';
					}
					if ( $bio ) {
						echo '<div class="mpwem-speaker-list-bio">' . esc_html( wp_trim_words( $bio, 28, '…' ) ) . '</div>';
					}
					echo '</div>';
					return;
				}

				if ( 'mep_speaker_event' === $column ) {
					$this->render_speaker_event_cell( $post_id );
				}
			}

			/**
			 * Cached map of speaker_id => [ [id,title], ... ] for assigned events.
			 *
			 * @var array<int,array<int,array{id:int,title:string}>>|null
			 */
			private $speaker_event_map = null;

			/**
			 * Build speaker → events map once per request.
			 */
			private function build_speaker_event_map() {
				if ( null !== $this->speaker_event_map ) {
					return;
				}
				$this->speaker_event_map = [];
				$event_ids = get_posts(
					[
						'post_type'      => 'mep_events',
						'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'no_found_rows'  => true,
					]
				);
				if ( empty( $event_ids ) ) {
					return;
				}
				foreach ( $event_ids as $event_id ) {
					$event_id = absint( $event_id );
					$list     = get_post_meta( $event_id, 'mep_event_speakers_list', true );
					if ( ! is_array( $list ) ) {
						$list = ( '' !== $list && null !== $list ) ? explode( ',', (string) $list ) : [];
					}
					if ( empty( $list ) ) {
						continue;
					}
					$event_title = wp_specialchars_decode( get_the_title( $event_id ), ENT_QUOTES );
					foreach ( $list as $speaker_id ) {
						$speaker_id = absint( $speaker_id );
						if ( ! $speaker_id ) {
							continue;
						}
						if ( ! isset( $this->speaker_event_map[ $speaker_id ] ) ) {
							$this->speaker_event_map[ $speaker_id ] = [];
						}
						$this->speaker_event_map[ $speaker_id ][] = [
							'id'    => $event_id,
							'title' => $event_title ? $event_title : sprintf( __( 'Event #%d', 'mage-eventpress' ), $event_id ),
						];
					}
				}
			}

			/**
			 * @param int $speaker_id Speaker post ID.
			 * @return array<int,array{id:int,title:string}>
			 */
			private function get_events_for_speaker( $speaker_id ) {
				$this->build_speaker_event_map();
				$speaker_id = absint( $speaker_id );

				return isset( $this->speaker_event_map[ $speaker_id ] ) ? $this->speaker_event_map[ $speaker_id ] : [];
			}

			/**
			 * Output the Event column cell for a speaker.
			 *
			 * @param int $speaker_id Speaker post ID.
			 */
			private function render_speaker_event_cell( $speaker_id ) {
				$events = $this->get_events_for_speaker( $speaker_id );
				if ( empty( $events ) ) {
					echo '<span class="mpwem-speaker-list-event-empty">' . esc_html__( 'Not assigned', 'mage-eventpress' ) . '</span>';
					return;
				}
				echo '<div class="mpwem-speaker-list-events">';
				foreach ( $events as $event ) {
					$edit_link = get_edit_post_link( $event['id'], 'raw' );
					if ( $edit_link ) {
						printf(
							'<a class="mpwem-speaker-list-event" href="%s">%s</a>',
							esc_url( $edit_link ),
							esc_html( $event['title'] )
						);
					} else {
						printf(
							'<span class="mpwem-speaker-list-event">%s</span>',
							esc_html( $event['title'] )
						);
					}
				}
				echo '</div>';
			}

			/**
			 * Force Speakers Management to 10 items per page.
			 *
			 * @param int    $per_page  Current per-page value.
			 * @param string $post_type Post type.
			 * @return int
			 */
			public function force_speaker_per_page( $per_page, $post_type ) {
				if ( 'mep_event_speaker' === $post_type ) {
					return self::SPEAKER_PER_PAGE;
				}

				return $per_page;
			}

			/**
			 * Override stored user screen option for speakers per page.
			 *
			 * @return int
			 */
			public function force_speaker_user_per_page() {
				return self::SPEAKER_PER_PAGE;
			}

			/**
			 * AJAX: Speakers Management table page (10 per page).
			 */
			public function ajax_speaker_list_paginate() {
				check_ajax_referer( 'mpwem_speaker_list', 'nonce' );
				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_send_json_error( 'Unauthorized', 403 );
				}

				$page   = max( 1, isset( $_POST['paged'] ) ? absint( wp_unslash( $_POST['paged'] ) ) : 1 );
				$search = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
				$status = isset( $_POST['post_status'] ) ? sanitize_key( wp_unslash( $_POST['post_status'] ) ) : 'all';

				$query = $this->query_speakers( $page, $status, $search );
				$total = (int) $query->found_posts;
				$pages = max( 1, (int) $query->max_num_pages );
				if ( $page > $pages ) {
					$page  = $pages;
					$query = $this->query_speakers( $page, $status, $search );
					$total = (int) $query->found_posts;
					$pages = max( 1, (int) $query->max_num_pages );
				}

				ob_start();
				if ( empty( $query->posts ) ) {
					?>
					<tr class="no-items">
						<td class="colspanchange" colspan="5">
							<?php esc_html_e( 'No speakers found.', 'mage-eventpress' ); ?>
						</td>
					</tr>
					<?php
				} else {
					foreach ( $query->posts as $post ) {
						$this->render_speaker_list_row( $post );
					}
				}
				$tbody = ob_get_clean();

				wp_send_json_success(
					[
						'tbody'      => $tbody,
						'pagination' => $this->render_speaker_pagination_html( $page, $pages, $total ),
						'page'       => $page,
						'pages'      => $pages,
						'total'      => $total,
					]
				);
			}

			/**
			 * @param int    $page   Page number.
			 * @param string $status Status key.
			 * @param string $search Search string.
			 * @return \WP_Query
			 */
			private function query_speakers( $page, $status, $search ) {
				$args = [
					'post_type'      => 'mep_event_speaker',
					'posts_per_page' => self::SPEAKER_PER_PAGE,
					'paged'          => max( 1, (int) $page ),
					'orderby'        => 'date',
					'order'          => 'DESC',
				];

				if ( $search ) {
					$args['s'] = $search;
				}

				if ( 'trash' === $status ) {
					$args['post_status'] = 'trash';
				} elseif ( in_array( $status, [ 'publish', 'draft', 'pending', 'private', 'future' ], true ) ) {
					$args['post_status'] = $status;
				} else {
					$args['post_status'] = [ 'publish', 'draft', 'pending', 'private', 'future' ];
				}

				return new \WP_Query( $args );
			}

			/**
			 * @param \WP_Post $post Speaker post.
			 */
			private function render_speaker_list_row( $post ) {
				$post_id   = (int) $post->ID;
				$edit_link = get_edit_post_link( $post_id, 'raw' );
				$edit_link = $edit_link ? $edit_link : '#';
				$title     = wp_specialchars_decode( get_the_title( $post_id ), ENT_QUOTES );
				$is_trash  = ( 'trash' === $post->post_status );
				?>
				<tr id="post-<?php echo esc_attr( $post_id ); ?>" class="iedit author-self level-0 post-<?php echo esc_attr( $post_id ); ?> type-mep_event_speaker status-<?php echo esc_attr( $post->post_status ); ?> hentry" data-speaker-id="<?php echo esc_attr( $post_id ); ?>">
					<th scope="row" class="check-column">
						<input id="cb-select-<?php echo esc_attr( $post_id ); ?>" type="checkbox" name="post[]" value="<?php echo esc_attr( $post_id ); ?>" />
					</th>
					<td class="mep_speaker_image column-mep_speaker_image" data-colname="<?php esc_attr_e( 'Image', 'mage-eventpress' ); ?>">
						<?php $this->speaker_column_content( 'mep_speaker_image', $post_id ); ?>
					</td>
					<td class="title column-title has-row-actions column-primary page-title" data-colname="<?php esc_attr_e( 'Title', 'mage-eventpress' ); ?>">
						<strong>
							<a class="row-title" href="<?php echo esc_url( $edit_link ); ?>" data-speaker-edit="<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html( $title ); ?></a>
							<?php if ( 'draft' === $post->post_status ) : ?>
								<span class="post-state"><?php esc_html_e( 'Draft', 'mage-eventpress' ); ?></span>
							<?php elseif ( $is_trash ) : ?>
								<span class="post-state"><?php esc_html_e( 'Trash', 'mage-eventpress' ); ?></span>
							<?php endif; ?>
						</strong>
						<div class="row-actions">
							<?php if ( $is_trash ) : ?>
								<span class="untrash"><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=untrash' ), 'untrash-post_' . $post_id ) ); ?>"><?php esc_html_e( 'Restore', 'mage-eventpress' ); ?></a></span>
								|
								<span class="delete">
									<a class="submitdelete" href="#" data-speaker-delete="<?php echo esc_attr( $post_id ); ?>" data-speaker-name="<?php echo esc_attr( $title ); ?>" data-speaker-force="1"><?php esc_html_e( 'Delete Permanently', 'mage-eventpress' ); ?></a>
								</span>
							<?php else : ?>
								<span class="edit"><a href="<?php echo esc_url( $edit_link ); ?>" data-speaker-edit="<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Edit', 'mage-eventpress' ); ?></a></span>
								|
								<span class="trash">
									<a class="submitdelete" href="#" data-speaker-delete="<?php echo esc_attr( $post_id ); ?>" data-speaker-name="<?php echo esc_attr( $title ); ?>" data-speaker-force="0"><?php esc_html_e( 'Trash', 'mage-eventpress' ); ?></a>
								</span>
							<?php endif; ?>
						</div>
					</td>
					<td class="mep_speaker_desc column-mep_speaker_desc" data-colname="<?php esc_attr_e( 'Description', 'mage-eventpress' ); ?>">
						<?php $this->speaker_column_content( 'mep_speaker_desc', $post_id ); ?>
					</td>
					<td class="mep_speaker_event column-mep_speaker_event" data-colname="<?php esc_attr_e( 'Event', 'mage-eventpress' ); ?>">
						<?php $this->speaker_column_content( 'mep_speaker_event', $post_id ); ?>
					</td>
				</tr>
				<?php
			}

			/**
			 * @param int $current Current page.
			 * @param int $pages   Total pages.
			 * @param int $total   Total items.
			 * @return string
			 */
			private function render_speaker_pagination_html( $current, $pages, $total ) {
				$per_page = self::SPEAKER_PER_PAGE;
				$start    = $total > 0 ? ( ( $current - 1 ) * $per_page ) + 1 : 0;
				$end      = min( $current * $per_page, $total );

				ob_start();
				?>
				<div class="mpwem-speaker-pagination" data-page="<?php echo esc_attr( $current ); ?>" data-pages="<?php echo esc_attr( $pages ); ?>">
					<div class="mpwem-speaker-pagination-info">
						<?php
						if ( $total > 0 ) {
							printf(
								/* translators: 1: start, 2: end, 3: total */
								esc_html__( 'Showing %1$d–%2$d of %3$d speakers', 'mage-eventpress' ),
								(int) $start,
								(int) $end,
								(int) $total
							);
						} else {
							esc_html_e( '0 speakers', 'mage-eventpress' );
						}
						?>
					</div>
					<?php if ( $pages > 1 ) : ?>
						<div class="mpwem-speaker-pagination-links">
							<button type="button" class="mpwem-speaker-page-btn" data-page="1" <?php disabled( $current <= 1 ); ?> title="<?php esc_attr_e( 'First page', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-controls-skipback"></span>
							</button>
							<button type="button" class="mpwem-speaker-page-btn" data-page="<?php echo esc_attr( max( 1, $current - 1 ) ); ?>" <?php disabled( $current <= 1 ); ?> title="<?php esc_attr_e( 'Previous page', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-controls-back"></span>
							</button>
							<?php
							$window = 2;
							$from   = max( 1, $current - $window );
							$to     = min( $pages, $current + $window );
							for ( $i = $from; $i <= $to; $i++ ) :
								?>
								<button type="button" class="mpwem-speaker-page-btn<?php echo $i === $current ? ' is-active' : ''; ?>" data-page="<?php echo esc_attr( $i ); ?>">
									<?php echo esc_html( $i ); ?>
								</button>
							<?php endfor; ?>
							<button type="button" class="mpwem-speaker-page-btn" data-page="<?php echo esc_attr( min( $pages, $current + 1 ) ); ?>" <?php disabled( $current >= $pages ); ?> title="<?php esc_attr_e( 'Next page', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-controls-forward"></span>
							</button>
							<button type="button" class="mpwem-speaker-page-btn" data-page="<?php echo esc_attr( $pages ); ?>" <?php disabled( $current >= $pages ); ?> title="<?php esc_attr_e( 'Last page', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-controls-skipforward"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>
				<?php
				return ob_get_clean();
			}

			/**
			 * AJAX: create a speaker from the Speakers Management modal.
			 */
			public function ajax_speaker_create() {
				check_ajax_referer( 'mpwem_speaker_list', 'nonce' );

				$pto = get_post_type_object( 'mep_event_speaker' );
				if ( ! $pto || ! current_user_can( $pto->cap->create_posts ) ) {
					wp_send_json_error( __( 'You do not have permission to create speakers.', 'mage-eventpress' ), 403 );
				}

				$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
				$excerpt     = isset( $_POST['excerpt'] ) ? sanitize_text_field( wp_unslash( $_POST['excerpt'] ) ) : '';
				$description = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
				$image_id    = isset( $_POST['image_id'] ) ? absint( wp_unslash( $_POST['image_id'] ) ) : 0;
				$status      = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'publish';

				if ( '' === $name ) {
					wp_send_json_error( __( 'Speaker name is required.', 'mage-eventpress' ) );
				}

				if ( ! in_array( $status, [ 'publish', 'draft' ], true ) ) {
					$status = 'publish';
				}

				$post_id = wp_insert_post(
					[
						'post_type'    => 'mep_event_speaker',
						'post_status'  => $status,
						'post_title'   => $name,
						'post_excerpt' => $excerpt,
						'post_content' => $description,
					],
					true
				);

				if ( is_wp_error( $post_id ) ) {
					wp_send_json_error( $post_id->get_error_message() );
				}

				if ( $image_id && wp_attachment_is_image( $image_id ) ) {
					set_post_thumbnail( $post_id, $image_id );
				}

				wp_send_json_success(
					[
						'id'      => (int) $post_id,
						'message' => __( 'Speaker created successfully.', 'mage-eventpress' ),
						'editUrl' => get_edit_post_link( $post_id, 'raw' ),
					]
				);
			}

			/**
			 * AJAX: fetch one speaker for the edit modal.
			 */
			public function ajax_speaker_get() {
				check_ajax_referer( 'mpwem_speaker_list', 'nonce' );

				$post_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
				$post    = $post_id ? get_post( $post_id ) : null;
				if ( ! $post || 'mep_event_speaker' !== $post->post_type ) {
					wp_send_json_error( __( 'Speaker not found.', 'mage-eventpress' ), 404 );
				}
				if ( ! current_user_can( 'edit_post', $post_id ) && ! current_user_can( 'read_post', $post_id ) ) {
					wp_send_json_error( __( 'You do not have permission to view this speaker.', 'mage-eventpress' ), 403 );
				}

				$image_id   = (int) get_post_thumbnail_id( $post_id );
				$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
				$image_full = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
				$status_key = $post->post_status;
				$status     = in_array( $status_key, [ 'publish', 'draft' ], true ) ? $status_key : $status_key;
				$status_labels = [
					'publish' => __( 'Published', 'mage-eventpress' ),
					'draft'   => __( 'Draft', 'mage-eventpress' ),
					'pending' => __( 'Pending', 'mage-eventpress' ),
					'private' => __( 'Private', 'mage-eventpress' ),
					'trash'   => __( 'Trash', 'mage-eventpress' ),
					'future'  => __( 'Scheduled', 'mage-eventpress' ),
				];
				$events = $this->get_events_for_speaker( $post_id );
				$event_payload = [];
				foreach ( $events as $event ) {
					$event_payload[] = [
						'id'    => (int) $event['id'],
						'title' => $event['title'],
						'url'   => get_edit_post_link( $event['id'], 'raw' ),
					];
				}

				wp_send_json_success(
					[
						'id'           => $post_id,
						'name'         => $post->post_title,
						'excerpt'      => $post->post_excerpt,
						'description'  => $post->post_content,
						'description_plain' => wp_strip_all_tags( $post->post_content ),
						'status'       => in_array( $status, [ 'publish', 'draft' ], true ) ? $status : 'draft',
						'status_key'   => $status_key,
						'status_label' => isset( $status_labels[ $status_key ] ) ? $status_labels[ $status_key ] : ucfirst( $status_key ),
						'image_id'     => $image_id,
						'image_url'    => $image_url ? $image_url : '',
						'image_full'   => $image_full ? $image_full : ( $image_url ? $image_url : '' ),
						'events'       => $event_payload,
						'date'         => get_the_date( '', $post_id ),
						'time'         => get_the_time( '', $post_id ),
						'permalink'    => get_permalink( $post_id ),
						'can_edit'     => current_user_can( 'edit_post', $post_id ),
					]
				);
			}

			/**
			 * AJAX: update an existing speaker from the modal.
			 */
			public function ajax_speaker_update() {
				check_ajax_referer( 'mpwem_speaker_list', 'nonce' );

				$post_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
				$post    = $post_id ? get_post( $post_id ) : null;
				if ( ! $post || 'mep_event_speaker' !== $post->post_type ) {
					wp_send_json_error( __( 'Speaker not found.', 'mage-eventpress' ), 404 );
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( __( 'You do not have permission to edit this speaker.', 'mage-eventpress' ), 403 );
				}

				$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
				$excerpt     = isset( $_POST['excerpt'] ) ? sanitize_text_field( wp_unslash( $_POST['excerpt'] ) ) : '';
				$description = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
				$image_id    = isset( $_POST['image_id'] ) ? absint( wp_unslash( $_POST['image_id'] ) ) : 0;
				$status      = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'publish';

				if ( '' === $name ) {
					wp_send_json_error( __( 'Speaker name is required.', 'mage-eventpress' ) );
				}
				if ( ! in_array( $status, [ 'publish', 'draft' ], true ) ) {
					$status = 'publish';
				}

				$result = wp_update_post(
					[
						'ID'           => $post_id,
						'post_title'   => $name,
						'post_excerpt' => $excerpt,
						'post_content' => $description,
						'post_status'  => $status,
					],
					true
				);

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( $result->get_error_message() );
				}

				if ( $image_id && wp_attachment_is_image( $image_id ) ) {
					set_post_thumbnail( $post_id, $image_id );
				} else {
					delete_post_thumbnail( $post_id );
				}

				wp_send_json_success(
					[
						'id'      => $post_id,
						'message' => __( 'Speaker updated successfully.', 'mage-eventpress' ),
					]
				);
			}

			/**
			 * AJAX: trash or permanently delete a speaker.
			 */
			public function ajax_speaker_delete() {
				check_ajax_referer( 'mpwem_speaker_list', 'nonce' );

				$post_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
				$force   = ! empty( $_POST['force'] );
				$post    = $post_id ? get_post( $post_id ) : null;
				if ( ! $post || 'mep_event_speaker' !== $post->post_type ) {
					wp_send_json_error( __( 'Speaker not found.', 'mage-eventpress' ), 404 );
				}
				if ( ! current_user_can( 'delete_post', $post_id ) ) {
					wp_send_json_error( __( 'You do not have permission to delete this speaker.', 'mage-eventpress' ), 403 );
				}

				if ( $force || 'trash' === $post->post_status ) {
					$deleted = wp_delete_post( $post_id, true );
					if ( ! $deleted ) {
						wp_send_json_error( __( 'Could not delete speaker.', 'mage-eventpress' ) );
					}
					wp_send_json_success(
						[
							'id'      => $post_id,
							'message' => __( 'Speaker permanently deleted.', 'mage-eventpress' ),
						]
					);
				}

				$trashed = wp_trash_post( $post_id );
				if ( ! $trashed ) {
					wp_send_json_error( __( 'Could not move speaker to trash.', 'mage-eventpress' ) );
				}

				wp_send_json_success(
					[
						'id'      => $post_id,
						'message' => __( 'Speaker moved to trash.', 'mage-eventpress' ),
					]
				);
			}

			private function bundle_for( $post_type ) {
				$pto = get_post_type_object( $post_type );
				if ( ! $pto ) {
					return null;
				}
				$labels   = $pto->labels;
				$singular = $labels->singular_name ? $labels->singular_name : $labels->name;
				$copy     = $this->copy_for( $post_type );

				return [
					'heading'        => sprintf( esc_html__( '%s Management', 'mage-eventpress' ), $labels->name ),
					'subheading'     => esc_html( $copy['subheading'] ),
					'addButtonLabel' => sprintf( esc_html__( '+ Add New %s', 'mage-eventpress' ), $singular ),
					'addNewUrl'      => esc_url( admin_url( 'post-new.php?post_type=' . $post_type ) ),
				];
			}

			/**
			 * AJAX: email template HTML for list-page preview popup.
			 */
			public function ajax_waitlist_email_preview() {
				check_ajax_referer( 'mpwem_email_preview', 'nonce' );

				$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
				$post    = $post_id ? get_post( $post_id ) : null;

				if ( ! $post || 'mep_waitlist_email' !== $post->post_type ) {
					wp_send_json_error( __( 'Email template not found.', 'mage-eventpress' ), 404 );
				}

				if ( ! current_user_can( 'edit_post', $post_id ) && ! current_user_can( 'read_post', $post_id ) ) {
					wp_send_json_error( __( 'You do not have permission to preview this template.', 'mage-eventpress' ), 403 );
				}

				$html = (string) $post->post_content;
				$html = $this->preview_sample_tags( $html );

				wp_send_json_success(
					[
						'id'      => (int) $post_id,
						'title'   => wp_specialchars_decode( get_the_title( $post_id ), ENT_QUOTES ),
						'html'    => $html,
						'editUrl' => get_edit_post_link( $post_id, 'raw' ),
						'empty'   => ( '' === trim( wp_strip_all_tags( $html ) ) && false === stripos( $html, '<img' ) ),
					]
				);
			}

			/**
			 * Replace waitlist merge tags with sample values for preview.
			 *
			 * @param string $html Template HTML.
			 * @return string
			 */
			private function preview_sample_tags( $html ) {
				$samples = [
					'{name}'           => 'Jane Doe',
					'{email}'          => 'jane@example.com',
					'{event}'          => 'Summer Music Festival',
					'{event_name}'     => 'Summer Music Festival',
					'{event_date}'     => 'August 20, 2026',
					'{ticket_qty}'     => '2',
					'{phone}'          => '+1 555 0100',
					'{event_url}'      => home_url( '/' ),
					'{site_name}'      => get_bloginfo( 'name' ),
					'{site_url}'       => home_url( '/' ),
					'{payment_method}' => 'Credit Card',
					'{amount_paid}'    => '$50.00',
				];

				return str_replace( array_keys( $samples ), array_values( $samples ), (string) $html );
			}

			public function enqueue() {
				$post_type = $this->current_target_post_type();
				if ( ! $post_type ) {
					return;
				}

				$bundle = $this->bundle_for( $post_type );
				if ( ! $bundle ) {
					return;
				}

				wp_enqueue_style(
					'mpwem-post-list-modern',
					MPWEM_PLUGIN_URL . '/assets/admin/css/mpwem-post-list-modern.css',
					[],
					$this->asset_ver( '/assets/admin/css/mpwem-post-list-modern.css' )
				);

				$is_speaker = ( 'mep_event_speaker' === $post_type );
				$is_email   = ( 'mep_waitlist_email' === $post_type );
				$script_deps = [];
				if ( $is_speaker ) {
					wp_enqueue_media();
					$script_deps = [ 'jquery' ];
				}

				wp_enqueue_script(
					'mpwem-post-list-modern',
					MPWEM_PLUGIN_URL . '/assets/admin/js/mpwem-post-list-modern.js',
					$script_deps,
					$this->asset_ver( '/assets/admin/js/mpwem-post-list-modern.js' ),
					true
				);

				wp_localize_script(
					'mpwem-post-list-modern',
					'mpwemPostListModern',
					[
						'postType'       => $post_type,
						'heading'        => $bundle['heading'],
						'subheading'     => $bundle['subheading'],
						'addButtonLabel' => $bundle['addButtonLabel'],
						'addNewUrl'      => $bundle['addNewUrl'],
						'proUrl'         => 'https://mage-people.com/product/mage-woo-event-booking-manager-pro/',
						'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
						'ajaxPagination' => $is_speaker,
						'ajaxCreate'     => $is_speaker,
						'emailPreview'   => $is_email,
						'perPage'        => $is_speaker ? self::SPEAKER_PER_PAGE : 0,
						'nonce'          => $is_speaker ? wp_create_nonce( 'mpwem_speaker_list' ) : ( $is_email ? wp_create_nonce( 'mpwem_email_preview' ) : '' ),
						'strings'        => [
							'actionsColumn'   => esc_html__( 'Actions', 'mage-eventpress' ),
							'promoEyebrow'    => esc_html__( 'EVENT PRO', 'mage-eventpress' ),
							'promoTitle'      => esc_html__( 'Get more out of your events', 'mage-eventpress' ),
							'promoBody'       => esc_html__( 'Unlock advanced attendee tools, PDF tickets, and reporting with Event Manager Pro and addons.', 'mage-eventpress' ),
							'promoCta'        => esc_html__( 'Explore Pro & Addons', 'mage-eventpress' ),
							'loading'         => esc_html__( 'Loading…', 'mage-eventpress' ),
							'error'           => esc_html__( 'Could not load speakers. Please try again.', 'mage-eventpress' ),
							'modalTitle'      => esc_html__( 'Add New Speaker', 'mage-eventpress' ),
							'modalSubtitle'   => esc_html__( 'Create a speaker profile to assign on event pages.', 'mage-eventpress' ),
							'editModalTitle'  => esc_html__( 'Edit Speaker', 'mage-eventpress' ),
							'editModalSubtitle' => esc_html__( 'Update this speaker profile.', 'mage-eventpress' ),
							'nameLabel'       => esc_html__( 'Speaker Name', 'mage-eventpress' ),
							'namePlaceholder' => esc_html__( 'e.g. Alex Rivera', 'mage-eventpress' ),
							'roleLabel'       => esc_html__( 'Role / Title', 'mage-eventpress' ),
							'rolePlaceholder' => esc_html__( 'e.g. Keynote Speaker · CEO, TechVision', 'mage-eventpress' ),
							'descLabel'       => esc_html__( 'Description', 'mage-eventpress' ),
							'descPlaceholder' => esc_html__( 'Short biography shown on event pages…', 'mage-eventpress' ),
							'imageLabel'      => esc_html__( 'Featured Image', 'mage-eventpress' ),
							'imageSelect'     => esc_html__( 'Select Image', 'mage-eventpress' ),
							'imageChange'     => esc_html__( 'Change Image', 'mage-eventpress' ),
							'imageRemove'     => esc_html__( 'Remove', 'mage-eventpress' ),
							'statusLabel'     => esc_html__( 'Status', 'mage-eventpress' ),
							'statusPublish'   => esc_html__( 'Publish', 'mage-eventpress' ),
							'statusDraft'     => esc_html__( 'Draft', 'mage-eventpress' ),
							'cancel'          => esc_html__( 'Cancel', 'mage-eventpress' ),
							'save'            => esc_html__( 'Create Speaker', 'mage-eventpress' ),
							'update'          => esc_html__( 'Update Speaker', 'mage-eventpress' ),
							'saving'          => esc_html__( 'Creating…', 'mage-eventpress' ),
							'updating'        => esc_html__( 'Updating…', 'mage-eventpress' ),
							'nameRequired'    => esc_html__( 'Please enter a speaker name.', 'mage-eventpress' ),
							'createError'     => esc_html__( 'Could not create speaker. Please try again.', 'mage-eventpress' ),
							'updateError'     => esc_html__( 'Could not update speaker. Please try again.', 'mage-eventpress' ),
							'loadError'       => esc_html__( 'Could not load speaker details.', 'mage-eventpress' ),
							'createSuccess'   => esc_html__( 'Speaker created successfully.', 'mage-eventpress' ),
							'updateSuccess'   => esc_html__( 'Speaker updated successfully.', 'mage-eventpress' ),
							'deleteTitle'     => esc_html__( 'Delete Speaker?', 'mage-eventpress' ),
							'deleteText'      => esc_html__( 'This speaker will be moved to Trash. You can restore it later.', 'mage-eventpress' ),
							'deleteForceText' => esc_html__( 'This speaker will be permanently deleted. This cannot be undone.', 'mage-eventpress' ),
							'deleteConfirm'   => esc_html__( 'Move to Trash', 'mage-eventpress' ),
							'deleteForceConfirm' => esc_html__( 'Delete Permanently', 'mage-eventpress' ),
							'deleting'        => esc_html__( 'Deleting…', 'mage-eventpress' ),
							'deleteError'     => esc_html__( 'Could not delete speaker. Please try again.', 'mage-eventpress' ),
							'edit'            => esc_html__( 'Edit', 'mage-eventpress' ),
							'delete'          => esc_html__( 'Delete', 'mage-eventpress' ),
							'view'            => esc_html__( 'View', 'mage-eventpress' ),
							'preview'         => esc_html__( 'Preview', 'mage-eventpress' ),
							'previewTitle'    => esc_html__( 'Email Template Preview', 'mage-eventpress' ),
							'previewEmpty'    => esc_html__( 'This template has no content yet.', 'mage-eventpress' ),
							'previewError'    => esc_html__( 'Could not load email preview. Please try again.', 'mage-eventpress' ),
							'previewEdit'     => esc_html__( 'Edit Template', 'mage-eventpress' ),
							'previewClose'    => esc_html__( 'Close', 'mage-eventpress' ),
							'viewTitle'       => esc_html__( 'Speaker Details', 'mage-eventpress' ),
							'viewRole'        => esc_html__( 'Role / Title', 'mage-eventpress' ),
							'viewDesc'        => esc_html__( 'Description', 'mage-eventpress' ),
							'viewEvents'      => esc_html__( 'Assigned Events', 'mage-eventpress' ),
							'viewStatus'      => esc_html__( 'Status', 'mage-eventpress' ),
							'viewDate'        => esc_html__( 'Created', 'mage-eventpress' ),
							'viewEmpty'       => esc_html__( '—', 'mage-eventpress' ),
							'viewNoEvents'    => esc_html__( 'Not assigned to any event.', 'mage-eventpress' ),
							'viewEdit'        => esc_html__( 'Edit Speaker', 'mage-eventpress' ),
							'viewClose'       => esc_html__( 'Close', 'mage-eventpress' ),
							'at'              => esc_html__( 'at', 'mage-eventpress' ),
						],
						'proFeatures'    => [
							esc_html__( 'Attendee management & custom forms', 'mage-eventpress' ),
							esc_html__( 'PDF ticketing & custom emailing', 'mage-eventpress' ),
							esc_html__( 'Attendee CSV export & reports', 'mage-eventpress' ),
							esc_html__( 'Custom style & translation settings', 'mage-eventpress' ),
						],
					]
				);
			}
		}
		new MPWEM_Post_List_Modern();
	}
