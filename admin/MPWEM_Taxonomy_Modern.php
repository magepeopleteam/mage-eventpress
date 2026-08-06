<?php
	/*
	 * Modern reskin of Event taxonomy screens (edit-tags.php for mep_cat and
	 * mep_org) — CSS/JS on top of WordPress's term list, with modal CRUD,
	 * view details, Events column, and AJAX pagination (parity with Speakers).
	 *
	 * @Author MagePeople Team
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'MPWEM_Taxonomy_Modern' ) ) {
		class MPWEM_Taxonomy_Modern {
			const TAXONOMIES = [
				'mep_cat',
				'mep_org',
			];

			const ORG_TAXONOMY = 'mep_org';

			const TERM_PER_PAGE = 10;

			const ORG_META_KEYS = [
				'org_location',
				'org_street',
				'org_city',
				'org_state',
				'org_postcode',
				'org_email',
				'org_country',
				'latitude',
				'longitude',
			];

			public function __construct() {
				add_filter( 'screen_options_show_screen', [ $this, 'maybe_hide_screen_options' ], 10, 2 );
				add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
				add_action( 'all_admin_notices', [ $this, 'render_hero' ] );
				add_action( 'wp_ajax_mpwem_add_taxonomy_term', [ $this, 'ajax_add_term' ] );
				add_action( 'wp_ajax_mpwem_get_taxonomy_term', [ $this, 'ajax_get_term' ] );
				add_action( 'wp_ajax_mpwem_edit_taxonomy_term', [ $this, 'ajax_edit_term' ] );
				add_action( 'wp_ajax_mpwem_delete_taxonomy_term', [ $this, 'ajax_delete_term' ] );
				add_action( 'wp_ajax_mpwem_taxonomy_list_paginate', [ $this, 'ajax_list_paginate' ] );
				add_filter( 'edit_tags_per_page', [ $this, 'force_term_per_page' ] );
				foreach ( self::TAXONOMIES as $taxonomy ) {
					add_filter( "manage_edit-{$taxonomy}_columns", [ $this, 'modern_columns' ], 20 );
					add_filter( "manage_{$taxonomy}_custom_column", [ $this, 'render_custom_column' ], 10, 3 );
					add_filter( "edit_{$taxonomy}_per_page", [ $this, 'force_term_user_per_page' ] );
					add_filter( "get_user_option_edit_{$taxonomy}_per_page", [ $this, 'force_term_user_per_page' ] );
				}
			}

			private function current_target_taxonomy() {
				if ( ! function_exists( 'get_current_screen' ) ) {
					return null;
				}
				$screen = get_current_screen();
				if ( $screen && isset( $screen->taxonomy ) && in_array( $screen->taxonomy, self::TAXONOMIES, true ) ) {
					return $screen->taxonomy;
				}

				return null;
			}

			private function taxonomy_from_request() {
				$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';

				return in_array( $taxonomy, self::TAXONOMIES, true ) ? $taxonomy : '';
			}

			public function maybe_hide_screen_options( $show_screen, $screen ) {
				if ( isset( $screen->taxonomy ) && in_array( $screen->taxonomy, self::TAXONOMIES, true ) ) {
					return false;
				}

				return $show_screen;
			}

			/**
			 * Force Category / Organizer lists to 10 items per page.
			 *
			 * @param int $per_page Per-page value.
			 * @return int
			 */
			public function force_term_per_page( $per_page ) {
				if ( $this->current_target_taxonomy() ) {
					return self::TERM_PER_PAGE;
				}

				return $per_page;
			}

			/**
			 * Override stored user screen option for terms per page.
			 *
			 * @return int
			 */
			public function force_term_user_per_page() {
				return self::TERM_PER_PAGE;
			}

			public function modern_columns( $columns ) {
				$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
				if ( ! $taxonomy && function_exists( 'get_current_screen' ) ) {
					$screen   = get_current_screen();
					$taxonomy = ( $screen && ! empty( $screen->taxonomy ) ) ? $screen->taxonomy : '';
				}

				$cols = [
					'name'            => __( 'Name', 'mage-eventpress' ),
					'mep_term_events' => __( 'Event', 'mage-eventpress' ),
					'posts'           => __( 'Count', 'mage-eventpress' ),
				];

				if ( self::ORG_TAXONOMY === $taxonomy ) {
					$cols = [
						'name'            => __( 'Name', 'mage-eventpress' ),
						'mep_term_events' => __( 'Event', 'mage-eventpress' ),
						'posts'           => __( 'Count', 'mage-eventpress' ),
					];
				}

				return $cols;
			}

			/**
			 * Custom column content for Event + Email.
			 *
			 * @param string $out         Existing output.
			 * @param string $column_name Column key.
			 * @param int    $term_id     Term ID.
			 * @return string
			 */
			public function render_custom_column( $out, $column_name, $term_id ) {
				$taxonomy = $this->current_target_taxonomy();
				if ( ! $taxonomy ) {
					$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
				}

				if ( 'org_email' === $column_name ) {
					$email = get_term_meta( (int) $term_id, 'org_email', true );

					return $email ? esc_html( $email ) : '&#8212;';
				}

				if ( 'mep_term_events' === $column_name && in_array( $taxonomy, self::TAXONOMIES, true ) ) {
					ob_start();
					$this->render_term_event_cell( (int) $term_id, $taxonomy );

					return ob_get_clean();
				}

				return $out;
			}

			/**
			 * @param int    $term_id  Term ID.
			 * @param string $taxonomy Taxonomy slug.
			 * @param int    $limit    Max events to resolve (0 = all for view).
			 * @return array<int,array{id:int,title:string,url:string}>
			 */
			private function get_events_for_term( $term_id, $taxonomy, $limit = 8 ) {
				$term_id = absint( $term_id );
				if ( ! $term_id || ! in_array( $taxonomy, self::TAXONOMIES, true ) ) {
					return [];
				}

				$args = [
					'post_type'              => 'mep_events',
					'post_status'            => [ 'publish', 'draft', 'pending', 'private', 'future' ],
					'posts_per_page'         => $limit > 0 ? $limit : -1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'tax_query'              => [
						[
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $term_id,
						],
					],
				];

				$event_ids = get_posts( $args );
				if ( empty( $event_ids ) ) {
					return [];
				}

				$events = [];
				foreach ( $event_ids as $event_id ) {
					$event_id    = absint( $event_id );
					$event_title = wp_specialchars_decode( get_the_title( $event_id ), ENT_QUOTES );
					$edit_link   = get_edit_post_link( $event_id, 'raw' );
					$events[]    = [
						'id'    => $event_id,
						'title' => $event_title ? $event_title : sprintf( __( 'Event #%d', 'mage-eventpress' ), $event_id ),
						'url'   => $edit_link ? $edit_link : '',
					];
				}

				return $events;
			}

			/**
			 * Output the Event column cell for a term.
			 *
			 * @param int    $term_id  Term ID.
			 * @param string $taxonomy Taxonomy slug.
			 */
			private function render_term_event_cell( $term_id, $taxonomy ) {
				$visible = 3;
				$events  = $this->get_events_for_term( $term_id, $taxonomy, $visible + 1 );
				if ( empty( $events ) ) {
					echo '<span class="mpwem-term-list-event-empty">' . esc_html__( 'Not assigned', 'mage-eventpress' ) . '</span>';
					return;
				}

				$term  = get_term( $term_id, $taxonomy );
				$total = ( $term && ! is_wp_error( $term ) ) ? (int) $term->count : count( $events );
				$show  = array_slice( $events, 0, $visible );
				$extra = max( 0, $total - count( $show ) );

				echo '<div class="mpwem-term-list-events">';
				foreach ( $show as $event ) {
					$title = $event['title'];
					if ( ! empty( $event['url'] ) ) {
						printf(
							'<a class="mpwem-term-list-event" href="%s" title="%s"><span class="dashicons dashicons-calendar-alt mpwem-term-list-event-icon" aria-hidden="true"></span><span class="mpwem-term-list-event-label">%s</span></a>',
							esc_url( $event['url'] ),
							esc_attr( $title ),
							esc_html( $title )
						);
					} else {
						printf(
							'<span class="mpwem-term-list-event" title="%s"><span class="dashicons dashicons-calendar-alt mpwem-term-list-event-icon" aria-hidden="true"></span><span class="mpwem-term-list-event-label">%s</span></span>',
							esc_attr( $title ),
							esc_html( $title )
						);
					}
				}
				if ( $extra > 0 ) {
					$list_url = '';
					if ( $term && ! is_wp_error( $term ) ) {
						$list_url = admin_url( 'edit.php?post_type=mep_events&' . $taxonomy . '=' . $term->slug );
					}
					$more_label = sprintf(
						/* translators: %d: number of additional events */
						_n( '+%d more', '+%d more', $extra, 'mage-eventpress' ),
						$extra
					);
					if ( $list_url ) {
						printf(
							'<a class="mpwem-term-list-event-more" href="%s">%s</a>',
							esc_url( $list_url ),
							esc_html( $more_label )
						);
					} else {
						printf( '<span class="mpwem-term-list-event-more">%s</span>', esc_html( $more_label ) );
					}
				}
				echo '</div>';
			}

			private function asset_ver( $rel_path ) {
				$file = MPWEM_PLUGIN_DIR . $rel_path;

				return file_exists( $file ) ? (string) filemtime( $file ) : MPWEM_PLUGIN_VERSION;
			}

			private function copy_for( $taxonomy ) {
				$map = [
					'mep_cat' => [
						'subheading'      => __( 'Organize events into categories so visitors can browse by type, topic, or audience.', 'mage-eventpress' ),
						'namePlaceholder' => __( 'e.g. Music Festival', 'mage-eventpress' ),
						'slugPlaceholder' => 'music-festival',
						'nameFieldLabel'  => __( 'Name', 'mage-eventpress' ),
					],
					'mep_org' => [
						'subheading'      => __( 'Manage organizers and their contact details. Assign organizers to events so attendees know who is hosting.', 'mage-eventpress' ),
						'namePlaceholder' => __( 'e.g. City Arts Foundation', 'mage-eventpress' ),
						'slugPlaceholder' => 'city-arts-foundation',
						'nameFieldLabel'  => __( 'Name', 'mage-eventpress' ),
					],
				];

				return isset( $map[ $taxonomy ] ) ? $map[ $taxonomy ] : [
					'subheading'      => '',
					'namePlaceholder' => __( 'e.g. New Item', 'mage-eventpress' ),
					'slugPlaceholder' => 'new-item',
					'nameFieldLabel'  => __( 'Name', 'mage-eventpress' ),
				];
			}

			private function per_taxonomy_bundle( $taxonomy ) {
				$tax_object = get_taxonomy( $taxonomy );
				if ( ! $tax_object ) {
					return null;
				}
				$labels      = $tax_object->labels;
				$singular    = $labels->singular_name;
				$singular_lc = function_exists( 'mb_strtolower' ) ? mb_strtolower( $singular ) : strtolower( $singular );
				$copy        = $this->copy_for( $taxonomy );

				return [
					'heading'         => sprintf( esc_html__( '%s Management', 'mage-eventpress' ), $labels->name ),
					'subheading'      => esc_html( $copy['subheading'] ),
					'addButtonLabel'  => sprintf( esc_html__( '+ Add New %s', 'mage-eventpress' ), $singular ),
					'modalTitle'      => sprintf( esc_html__( 'Add New %s', 'mage-eventpress' ), $singular ),
					'editModalTitle'  => sprintf( esc_html__( 'Edit %s', 'mage-eventpress' ), $singular ),
					'namePlaceholder' => esc_attr( $copy['namePlaceholder'] ),
					'nameFieldLabel'  => esc_html( $copy['nameFieldLabel'] ),
					'slugPlaceholder' => esc_attr( $copy['slugPlaceholder'] ),
					'descPlaceholder' => sprintf( esc_attr__( 'Enter a detailed description of this %s…', 'mage-eventpress' ), $singular_lc ),
					'submit'          => sprintf( esc_html__( 'Add New %s', 'mage-eventpress' ), $singular ),
					'confirmDelete'   => sprintf( esc_html__( 'Delete this %s? This cannot be undone.', 'mage-eventpress' ), $singular_lc ),
					'deleteTitle'     => sprintf( esc_html__( 'Delete %s?', 'mage-eventpress' ), $singular ),
					'deleteText'      => sprintf( esc_html__( 'This %s will be permanently deleted. This cannot be undone.', 'mage-eventpress' ), $singular_lc ),
					'viewTitle'       => sprintf( esc_html__( '%s Details', 'mage-eventpress' ), $singular ),
					'viewEdit'        => sprintf( esc_html__( 'Edit %s', 'mage-eventpress' ), $singular ),
					'emptyList'       => sprintf( esc_html__( 'No %s found.', 'mage-eventpress' ), $singular_lc ),
					'itemsLabel'      => function_exists( 'mb_strtolower' ) ? mb_strtolower( $labels->name ) : strtolower( $labels->name ),
					'hasOrgMeta'      => self::ORG_TAXONOMY === $taxonomy,
				];
			}

			private function taxonomy_icon( $taxonomy ) {
				return self::ORG_TAXONOMY === $taxonomy ? 'dashicons-building' : 'dashicons-category';
			}

			/**
			 * Friendly plural/singular nouns for hero copy — the taxonomies' own
			 * registered labels (e.g. "Events Category") read awkwardly in a
			 * "9 Events Category" count, so use plain words here instead.
			 */
			private function taxonomy_noun( $taxonomy, $count = 2 ) {
				if ( self::ORG_TAXONOMY === $taxonomy ) {
					return 1 === $count ? esc_html__( 'Organizer', 'mage-eventpress' ) : esc_html__( 'Organizers', 'mage-eventpress' );
				}

				return 1 === $count ? esc_html__( 'Category', 'mage-eventpress' ) : esc_html__( 'Categories', 'mage-eventpress' );
			}

			/**
			 * Server-rendered hero header + stats bar for the Category / Organizer
			 * list screens, matching the Speakers Management page design.
			 */
			public function render_hero() {
				if ( ! function_exists( 'get_current_screen' ) ) {
					return;
				}
				$screen = get_current_screen();
				if ( ! $screen || 'edit-tags' !== $screen->base ) {
					return;
				}
				$taxonomy = $this->current_target_taxonomy();
				if ( ! $taxonomy ) {
					return;
				}
				$bundle = $this->per_taxonomy_bundle( $taxonomy );
				if ( ! $bundle ) {
					return;
				}

				$icon   = $this->taxonomy_icon( $taxonomy );
				$is_org = self::ORG_TAXONOMY === $taxonomy;

				$total = (int) wp_count_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
				$in_use_ids = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => true, 'fields' => 'ids' ] );
				$in_use     = is_array( $in_use_ids ) ? count( $in_use_ids ) : 0;

				if ( $is_org ) {
					$third_label = esc_html__( 'With Email', 'mage-eventpress' );
					$with_email  = get_terms(
						[
							'taxonomy'   => $taxonomy,
							'hide_empty' => false,
							'fields'     => 'ids',
							'meta_query' => [
								[
									'key'     => 'org_email',
									'value'   => '',
									'compare' => '!=',
								],
							],
						]
					);
					$third_value = is_array( $with_email ) ? count( $with_email ) : 0;
					$third_icon  = 'dashicons-email';
				} else {
					$third_label = esc_html__( 'Unused', 'mage-eventpress' );
					$third_value = max( 0, $total - $in_use );
					$third_icon  = 'dashicons-hidden';
				}

				$eyebrow = $is_org
					? esc_html__( 'Organizer tools', 'mage-eventpress' )
					: esc_html__( 'Category tools', 'mage-eventpress' );
				?>
				<div class="mpwem-taxonomy-hero-wrap">
					<header class="bde-hero">
						<div class="bde-hero-copy">
							<span class="bde-eyebrow"><span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span> <?php echo esc_html( $eyebrow ); ?></span>
							<h1 class="bde-title"><?php echo esc_html( $bundle['heading'] ); ?></h1>
							<p class="bde-subtitle"><?php echo esc_html( $bundle['subheading'] ); ?></p>
						</div>
						<div class="bde-hero-badge">
							<span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
							<span>
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: count, 2: "Category"/"Categories" or "Organizer"/"Organizers" */
										'%1$s %2$s',
										number_format_i18n( $total ),
										$this->taxonomy_noun( $taxonomy, $total )
									)
								);
								?>
							</span>
						</div>
					</header>
					<div class="mep-stats-bar">
						<div class="mep-stat-card">
							<div class="mep-stat-icon"><span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span></div>
							<div>
								<div class="mep-stat-value"><?php echo esc_html( number_format_i18n( $total ) ); ?></div>
								<div class="mep-stat-label"><?php echo esc_html( sprintf( esc_html__( 'Total %s', 'mage-eventpress' ), $this->taxonomy_noun( $taxonomy, 2 ) ) ); ?></div>
							</div>
						</div>
						<div class="mep-stat-card">
							<div class="mep-stat-icon"><span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span></div>
							<div>
								<div class="mep-stat-value"><?php echo esc_html( number_format_i18n( $in_use ) ); ?></div>
								<div class="mep-stat-label"><?php esc_html_e( 'Assigned to Events', 'mage-eventpress' ); ?></div>
							</div>
						</div>
						<div class="mep-stat-card">
							<div class="mep-stat-icon"><span class="dashicons <?php echo esc_attr( $third_icon ); ?>" aria-hidden="true"></span></div>
							<div>
								<div class="mep-stat-value"><?php echo esc_html( number_format_i18n( $third_value ) ); ?></div>
								<div class="mep-stat-label"><?php echo esc_html( $third_label ); ?></div>
							</div>
						</div>
					</div>
				</div>
				<?php
			}

			private function save_org_meta( $term_id ) {
				foreach ( self::ORG_META_KEYS as $key ) {
					if ( ! isset( $_POST[ $key ] ) ) {
						continue;
					}
					$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
					update_term_meta( (int) $term_id, $key, $value );
				}
			}

			private function get_org_meta( $term_id ) {
				$meta = [];
				foreach ( self::ORG_META_KEYS as $key ) {
					$meta[ $key ] = (string) get_term_meta( (int) $term_id, $key, true );
				}

				return $meta;
			}

			public function ajax_add_term() {
				check_ajax_referer( 'mpwem_add_taxonomy_term', 'nonce' );

				$taxonomy        = $this->taxonomy_from_request();
				$taxonomy_object = $taxonomy ? get_taxonomy( $taxonomy ) : false;
				if ( ! $taxonomy_object || ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to add items here.', 'mage-eventpress' ) ], 403 );
				}

				$name = isset( $_POST['tag-name'] ) ? sanitize_text_field( wp_unslash( $_POST['tag-name'] ) ) : '';
				if ( $name === '' ) {
					wp_send_json_error( [ 'message' => esc_html__( 'Please enter a name.', 'mage-eventpress' ) ], 422 );
				}

				$args = [
					'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
				];
				$slug = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
				if ( $slug !== '' ) {
					$args['slug'] = $slug;
				}

				$result = wp_insert_term( $name, $taxonomy, $args );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ], 400 );
				}

				if ( self::ORG_TAXONOMY === $taxonomy ) {
					$this->save_org_meta( (int) $result['term_id'] );
				}

				wp_send_json_success( [ 'message' => esc_html__( 'Added.', 'mage-eventpress' ) ] );
			}

			public function ajax_get_term() {
				check_ajax_referer( 'mpwem_get_taxonomy_term', 'nonce' );

				$taxonomy        = $this->taxonomy_from_request();
				$taxonomy_object = $taxonomy ? get_taxonomy( $taxonomy ) : false;
				if ( ! $taxonomy_object || ! current_user_can( $taxonomy_object->cap->manage_terms ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to view this item.', 'mage-eventpress' ) ], 403 );
				}

				$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
				$term    = $term_id ? get_term( $term_id, $taxonomy ) : null;
				if ( ! $term || is_wp_error( $term ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'That item could not be found.', 'mage-eventpress' ) ], 404 );
				}

				$events = $this->get_events_for_term( $term_id, $taxonomy, 0 );

				$data = [
					'id'          => (int) $term->term_id,
					'name'        => $term->name,
					'slug'        => $term->slug,
					'description' => $term->description,
					'count'       => (int) $term->count,
					'events'      => $events,
					'can_edit'    => current_user_can( $taxonomy_object->cap->edit_terms ),
					'can_delete'  => current_user_can( $taxonomy_object->cap->delete_terms ),
				];

				if ( self::ORG_TAXONOMY === $taxonomy ) {
					$data['orgMeta'] = $this->get_org_meta( $term_id );
				}

				wp_send_json_success( $data );
			}

			public function ajax_edit_term() {
				check_ajax_referer( 'mpwem_edit_taxonomy_term', 'nonce' );

				$taxonomy        = $this->taxonomy_from_request();
				$taxonomy_object = $taxonomy ? get_taxonomy( $taxonomy ) : false;
				if ( ! $taxonomy_object || ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to edit this item.', 'mage-eventpress' ) ], 403 );
				}

				$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
				$name    = isset( $_POST['tag-name'] ) ? sanitize_text_field( wp_unslash( $_POST['tag-name'] ) ) : '';
				if ( ! $term_id ) {
					wp_send_json_error( [ 'message' => esc_html__( 'That item could not be found.', 'mage-eventpress' ) ], 404 );
				}
				if ( $name === '' ) {
					wp_send_json_error( [ 'message' => esc_html__( 'Please enter a name.', 'mage-eventpress' ) ], 422 );
				}

				$args = [
					'name'        => $name,
					'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
				];
				$slug = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
				if ( $slug !== '' ) {
					$args['slug'] = $slug;
				}

				$result = wp_update_term( $term_id, $taxonomy, $args );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ], 400 );
				}

				if ( self::ORG_TAXONOMY === $taxonomy ) {
					$this->save_org_meta( $term_id );
				}

				wp_send_json_success( [ 'message' => esc_html__( 'Updated.', 'mage-eventpress' ) ] );
			}

			public function ajax_delete_term() {
				check_ajax_referer( 'mpwem_delete_taxonomy_term', 'nonce' );

				$taxonomy        = $this->taxonomy_from_request();
				$taxonomy_object = $taxonomy ? get_taxonomy( $taxonomy ) : false;
				if ( ! $taxonomy_object || ! current_user_can( $taxonomy_object->cap->delete_terms ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to delete this item.', 'mage-eventpress' ) ], 403 );
				}

				$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
				if ( ! $term_id ) {
					wp_send_json_error( [ 'message' => esc_html__( 'That item could not be found.', 'mage-eventpress' ) ], 404 );
				}

				$result = wp_delete_term( $term_id, $taxonomy );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ], 400 );
				}
				if ( ! $result ) {
					wp_send_json_error( [ 'message' => esc_html__( 'That item could not be found.', 'mage-eventpress' ) ], 404 );
				}

				wp_send_json_success( [ 'message' => esc_html__( 'Deleted.', 'mage-eventpress' ) ] );
			}

			/**
			 * AJAX: Category / Organizer table page (10 per page).
			 */
			public function ajax_list_paginate() {
				check_ajax_referer( 'mpwem_taxonomy_list_paginate', 'nonce' );

				$taxonomy        = $this->taxonomy_from_request();
				$taxonomy_object = $taxonomy ? get_taxonomy( $taxonomy ) : false;
				if ( ! $taxonomy_object || ! current_user_can( $taxonomy_object->cap->manage_terms ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to manage these items.', 'mage-eventpress' ) ], 403 );
				}

				$page   = max( 1, isset( $_POST['paged'] ) ? absint( wp_unslash( $_POST['paged'] ) ) : 1 );
				$search = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';

				$query = $this->query_terms( $taxonomy, $page, $search );
				$total = (int) $query['total'];
				$pages = max( 1, (int) ceil( $total / self::TERM_PER_PAGE ) );
				if ( $page > $pages ) {
					$page  = $pages;
					$query = $this->query_terms( $taxonomy, $page, $search );
					$total = (int) $query['total'];
					$pages = max( 1, (int) ceil( $total / self::TERM_PER_PAGE ) );
				}

				$bundle = $this->per_taxonomy_bundle( $taxonomy );
				ob_start();
				if ( empty( $query['terms'] ) ) {
					$colspan = self::ORG_TAXONOMY === $taxonomy ? 4 : 5;
					?>
					<tr class="no-items">
						<td class="colspanchange" colspan="<?php echo esc_attr( $colspan ); ?>">
							<?php echo esc_html( $bundle['emptyList'] ); ?>
						</td>
					</tr>
					<?php
				} else {
					foreach ( $query['terms'] as $term ) {
						$this->render_term_list_row( $term, $taxonomy, $taxonomy_object );
					}
				}
				$tbody = ob_get_clean();

				wp_send_json_success(
					[
						'tbody'      => $tbody,
						'pagination' => $this->render_pagination_html( $page, $pages, $total, $bundle ),
						'page'       => $page,
						'pages'      => $pages,
						'total'      => $total,
					]
				);
			}

			/**
			 * @param string $taxonomy Taxonomy slug.
			 * @param int    $page     Page number.
			 * @param string $search   Search string.
			 * @return array{terms:array<int,\WP_Term>,total:int}
			 */
			private function query_terms( $taxonomy, $page, $search ) {
				$args = [
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'number'     => self::TERM_PER_PAGE,
					'offset'     => ( max( 1, (int) $page ) - 1 ) * self::TERM_PER_PAGE,
					'orderby'    => 'name',
					'order'      => 'ASC',
				];
				if ( $search ) {
					$args['search'] = $search;
				}

				$terms = get_terms( $args );
				if ( is_wp_error( $terms ) ) {
					$terms = [];
				}

				$count_args = [
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'fields'     => 'count',
				];
				if ( $search ) {
					$count_args['search'] = $search;
				}
				$total = get_terms( $count_args );
				if ( is_wp_error( $total ) ) {
					$total = 0;
				}

				return [
					'terms' => $terms,
					'total' => (int) $total,
				];
			}

			/**
			 * @param \WP_Term $term             Term object.
			 * @param string   $taxonomy         Taxonomy slug.
			 * @param object   $taxonomy_object  Taxonomy object.
			 */
			private function render_term_list_row( $term, $taxonomy, $taxonomy_object ) {
				$term_id    = (int) $term->term_id;
				$can_edit   = current_user_can( $taxonomy_object->cap->edit_terms );
				$can_delete = current_user_can( $taxonomy_object->cap->delete_terms );
				$list_url   = admin_url( 'edit.php?post_type=mep_events&' . $taxonomy . '=' . $term->slug );
				?>
				<tr id="tag-<?php echo esc_attr( $term_id ); ?>" class="level-0" data-term-id="<?php echo esc_attr( $term_id ); ?>">
					<td class="name column-name has-row-actions column-primary" data-colname="<?php esc_attr_e( 'Name', 'mage-eventpress' ); ?>">
						<strong><span class="row-title"><?php echo esc_html( $term->name ); ?></span></strong>
						<div class="row-actions">
							<?php if ( $can_edit ) : ?>
								<span class="edit"><a href="#" data-term-edit="<?php echo esc_attr( $term_id ); ?>"><?php esc_html_e( 'Edit', 'mage-eventpress' ); ?></a></span>
							<?php endif; ?>
							<?php if ( $can_delete ) : ?>
								<?php if ( $can_edit ) : ?> | <?php endif; ?>
								<span class="delete"><a class="delete-tag" href="#" data-term-delete="<?php echo esc_attr( $term_id ); ?>" data-term-name="<?php echo esc_attr( $term->name ); ?>"><?php esc_html_e( 'Delete', 'mage-eventpress' ); ?></a></span>
							<?php endif; ?>
						</div>
					</td>
					<td class="mep_term_events column-mep_term_events" data-colname="<?php esc_attr_e( 'Event', 'mage-eventpress' ); ?>">
						<?php $this->render_term_event_cell( $term_id, $taxonomy ); ?>
					</td>
					<td class="posts column-posts" data-colname="<?php esc_attr_e( 'Count', 'mage-eventpress' ); ?>">
						<a href="<?php echo esc_url( $list_url ); ?>"><?php echo esc_html( (string) (int) $term->count ); ?></a>
					</td>
				</tr>
				<?php
			}

			/**
			 * @param int   $current Current page.
			 * @param int   $pages   Total pages.
			 * @param int   $total   Total items.
			 * @param array $bundle  Taxonomy copy bundle.
			 * @return string
			 */
			private function render_pagination_html( $current, $pages, $total, $bundle ) {
				$per_page = self::TERM_PER_PAGE;
				$start    = $total > 0 ? ( ( $current - 1 ) * $per_page ) + 1 : 0;
				$end      = min( $current * $per_page, $total );
				$items_label = isset( $bundle['itemsLabel'] ) ? $bundle['itemsLabel'] : __( 'items', 'mage-eventpress' );
				$info        = $total > 0
					? sprintf(
						/* translators: 1: start, 2: end, 3: total, 4: item label */
						esc_html__( 'Showing %1$d–%2$d of %3$d %4$s', 'mage-eventpress' ),
						(int) $start,
						(int) $end,
						(int) $total,
						$items_label
					)
					: sprintf(
						/* translators: %s: item label */
						esc_html__( '0 %s', 'mage-eventpress' ),
						$items_label
					);

				ob_start();
				?>
				<div class="mpwem-term-pagination" data-page="<?php echo esc_attr( $current ); ?>" data-pages="<?php echo esc_attr( $pages ); ?>">
					<div class="mpwem-term-pagination-info"><?php echo esc_html( $info ); ?></div>
					<?php if ( $pages > 1 ) : ?>
						<div class="mpwem-term-pagination-links">
							<button type="button" class="mpwem-term-page-btn" data-page="1" <?php disabled( $current <= 1 ); ?> title="<?php esc_attr_e( 'First page', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-controls-skipback"></span>
							</button>
							<button type="button" class="mpwem-term-page-btn" data-page="<?php echo esc_attr( max( 1, $current - 1 ) ); ?>" <?php disabled( $current <= 1 ); ?> title="<?php esc_attr_e( 'Previous page', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-controls-back"></span>
							</button>
							<?php
							$window = 2;
							$from   = max( 1, $current - $window );
							$to     = min( $pages, $current + $window );
							for ( $i = $from; $i <= $to; $i++ ) :
								?>
								<button type="button" class="mpwem-term-page-btn<?php echo $i === $current ? ' is-active' : ''; ?>" data-page="<?php echo esc_attr( $i ); ?>">
									<?php echo esc_html( (string) $i ); ?>
								</button>
							<?php endfor; ?>
							<button type="button" class="mpwem-term-page-btn" data-page="<?php echo esc_attr( min( $pages, $current + 1 ) ); ?>" <?php disabled( $current >= $pages ); ?> title="<?php esc_attr_e( 'Next page', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-controls-forward"></span>
							</button>
							<button type="button" class="mpwem-term-page-btn" data-page="<?php echo esc_attr( $pages ); ?>" <?php disabled( $current >= $pages ); ?> title="<?php esc_attr_e( 'Last page', 'mage-eventpress' ); ?>">
								<span class="dashicons dashicons-controls-skipforward"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>
				<?php
				return ob_get_clean();
			}

			public function enqueue() {
				$taxonomy = $this->current_target_taxonomy();
				if ( ! $taxonomy ) {
					return;
				}

				$bundle = $this->per_taxonomy_bundle( $taxonomy );
				if ( ! $bundle ) {
					return;
				}

				wp_enqueue_style(
					'mpwem-taxonomy-modern',
					MPWEM_PLUGIN_URL . '/assets/admin/css/mpwem-taxonomy-modern.css',
					[],
					$this->asset_ver( '/assets/admin/css/mpwem-taxonomy-modern.css' )
				);
				wp_enqueue_script(
					'mpwem-taxonomy-modern',
					MPWEM_PLUGIN_URL . '/assets/admin/js/mpwem-taxonomy-modern.js',
					[],
					$this->asset_ver( '/assets/admin/js/mpwem-taxonomy-modern.js' ),
					true
				);

				wp_localize_script(
					'mpwem-taxonomy-modern',
					'mpwemTaxonomyModern',
					[
						'taxonomy'       => $taxonomy,
						'heading'        => $bundle['heading'],
						'subheading'     => $bundle['subheading'],
						'addButtonLabel' => $bundle['addButtonLabel'],
						'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
						'ajaxPagination' => true,
						'perPage'        => self::TERM_PER_PAGE,
						'nonce'          => wp_create_nonce( 'mpwem_add_taxonomy_term' ),
						'getNonce'       => wp_create_nonce( 'mpwem_get_taxonomy_term' ),
						'editNonce'      => wp_create_nonce( 'mpwem_edit_taxonomy_term' ),
						'deleteNonce'    => wp_create_nonce( 'mpwem_delete_taxonomy_term' ),
						'paginateNonce'  => wp_create_nonce( 'mpwem_taxonomy_list_paginate' ),
						'proUrl'         => 'https://mage-people.com/product/mage-woo-event-booking-manager-pro/',
						'perTaxonomy'    => [ $taxonomy => $bundle ],
						'strings'        => [
							'modalTitle'      => $bundle['modalTitle'],
							'name'            => esc_html__( 'Name', 'mage-eventpress' ),
							'namePlaceholder' => $bundle['namePlaceholder'],
							'nameHelp'        => esc_html__( 'The name is how it appears on your site.', 'mage-eventpress' ),
							'slug'            => esc_html__( 'Slug', 'mage-eventpress' ),
							'slugPlaceholder' => $bundle['slugPlaceholder'],
							'slugHelp'        => esc_html__( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'mage-eventpress' ),
							'description'     => esc_html__( 'Description', 'mage-eventpress' ),
							'descPlaceholder' => $bundle['descPlaceholder'],
							'descHelp'        => esc_html__( 'The description is not prominent by default; however, some themes may show it.', 'mage-eventpress' ),
							'actionsColumn'   => esc_html__( 'Actions', 'mage-eventpress' ),
							'cancel'          => esc_html__( 'Cancel', 'mage-eventpress' ),
							'submit'          => $bundle['submit'],
							'submitting'      => esc_html__( 'Adding…', 'mage-eventpress' ),
							'genericError'    => esc_html__( 'Something went wrong. Please try again.', 'mage-eventpress' ),
							'nameRequired'    => esc_html__( 'Please enter a name.', 'mage-eventpress' ),
							'editModalTitle'  => $bundle['editModalTitle'],
							'saveChanges'     => esc_html__( 'Save Changes', 'mage-eventpress' ),
							'saving'          => esc_html__( 'Saving…', 'mage-eventpress' ),
							'loadingTerm'     => esc_html__( 'Loading…', 'mage-eventpress' ),
							'loading'         => esc_html__( 'Loading…', 'mage-eventpress' ),
							'confirmDelete'   => $bundle['confirmDelete'],
							'deleteTitle'     => $bundle['deleteTitle'],
							'deleteText'      => $bundle['deleteText'],
							'deleteConfirm'   => esc_html__( 'Delete Permanently', 'mage-eventpress' ),
							'deleting'        => esc_html__( 'Deleting…', 'mage-eventpress' ),
							'deleteFailed'    => esc_html__( 'Could not delete this item. Please try again.', 'mage-eventpress' ),
							'view'            => esc_html__( 'View', 'mage-eventpress' ),
							'edit'            => esc_html__( 'Edit', 'mage-eventpress' ),
							'delete'          => esc_html__( 'Delete', 'mage-eventpress' ),
							'viewTitle'       => $bundle['viewTitle'],
							'viewEdit'        => $bundle['viewEdit'],
							'viewClose'       => esc_html__( 'Close', 'mage-eventpress' ),
							'viewDesc'        => esc_html__( 'Description', 'mage-eventpress' ),
							'viewSlug'        => esc_html__( 'Slug', 'mage-eventpress' ),
							'viewCount'       => esc_html__( 'Assigned Events Count', 'mage-eventpress' ),
							'viewEvents'      => esc_html__( 'Assigned Events', 'mage-eventpress' ),
							'viewEmpty'       => esc_html__( '—', 'mage-eventpress' ),
							'viewNoEvents'    => esc_html__( 'Not assigned to any event.', 'mage-eventpress' ),
							'loadError'       => esc_html__( 'Could not load details.', 'mage-eventpress' ),
							'promoEyebrow'    => esc_html__( 'EVENT PRO', 'mage-eventpress' ),
							'promoTitle'      => esc_html__( 'Get more out of your events', 'mage-eventpress' ),
							'promoBody'       => esc_html__( 'Unlock advanced attendee tools, PDF tickets, and reporting with Event Manager Pro and addons.', 'mage-eventpress' ),
							'promoCta'        => esc_html__( 'Explore Pro & Addons', 'mage-eventpress' ),
							'orgLocation'     => esc_html__( 'Location/Venue', 'mage-eventpress' ),
							'orgStreet'       => esc_html__( 'Street', 'mage-eventpress' ),
							'orgCity'         => esc_html__( 'City', 'mage-eventpress' ),
							'orgState'        => esc_html__( 'State', 'mage-eventpress' ),
							'orgPostcode'     => esc_html__( 'Postcode', 'mage-eventpress' ),
							'orgEmail'        => esc_html__( 'Email', 'mage-eventpress' ),
							'orgCountry'      => esc_html__( 'Country', 'mage-eventpress' ),
							'orgSection'      => esc_html__( 'Contact & location', 'mage-eventpress' ),
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
		new MPWEM_Taxonomy_Modern();
	}
