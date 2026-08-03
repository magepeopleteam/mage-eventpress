<?php
	/*
	 * Modern reskin of Event taxonomy screens (edit-tags.php for mep_cat and
	 * mep_org) — pure CSS/JS on top of WordPress's own term list + add-term
	 * form. Organizer screens also surface contact/location term meta in the
	 * Add/Edit modal (same keys as mep_tax_meta.php).
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
				add_action( 'wp_ajax_mpwem_add_taxonomy_term', [ $this, 'ajax_add_term' ] );
				add_action( 'wp_ajax_mpwem_get_taxonomy_term', [ $this, 'ajax_get_term' ] );
				add_action( 'wp_ajax_mpwem_edit_taxonomy_term', [ $this, 'ajax_edit_term' ] );
				add_action( 'wp_ajax_mpwem_delete_taxonomy_term', [ $this, 'ajax_delete_term' ] );
				foreach ( self::TAXONOMIES as $taxonomy ) {
					add_filter( "manage_edit-{$taxonomy}_columns", [ $this, 'modern_columns' ], 20 );
				}
				add_filter( 'manage_mep_org_custom_column', [ $this, 'render_org_email_column' ], 10, 3 );
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

			public function modern_columns( $columns ) {
				$cols = [
					'name'        => __( 'Name', 'mage-eventpress' ),
					'description' => __( 'Description', 'mage-eventpress' ),
					'slug'        => __( 'Slug', 'mage-eventpress' ),
					'posts'       => __( 'Count', 'mage-eventpress' ),
				];

				$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
				if ( self::ORG_TAXONOMY === $taxonomy ) {
					$cols = [
						'name'        => __( 'Name', 'mage-eventpress' ),
						'description' => __( 'Description', 'mage-eventpress' ),
						'org_email'   => __( 'Email', 'mage-eventpress' ),
						'slug'        => __( 'Slug', 'mage-eventpress' ),
						'posts'       => __( 'Count', 'mage-eventpress' ),
					];
				}

				return $cols;
			}

			public function render_org_email_column( $out, $column_name, $term_id ) {
				if ( 'org_email' !== $column_name ) {
					return $out;
				}
				$email = get_term_meta( (int) $term_id, 'org_email', true );

				return $email ? esc_html( $email ) : '&#8212;';
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
					'hasOrgMeta'      => self::ORG_TAXONOMY === $taxonomy,
				];
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
				if ( ! $taxonomy_object || ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to edit this item.', 'mage-eventpress' ) ], 403 );
				}

				$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
				$term    = $term_id ? get_term( $term_id, $taxonomy ) : null;
				if ( ! $term || is_wp_error( $term ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'That item could not be found.', 'mage-eventpress' ) ], 404 );
				}

				$data = [
					'id'          => (int) $term->term_id,
					'name'        => $term->name,
					'slug'        => $term->slug,
					'description' => $term->description,
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
						'nonce'          => wp_create_nonce( 'mpwem_add_taxonomy_term' ),
						'getNonce'       => wp_create_nonce( 'mpwem_get_taxonomy_term' ),
						'editNonce'      => wp_create_nonce( 'mpwem_edit_taxonomy_term' ),
						'deleteNonce'    => wp_create_nonce( 'mpwem_delete_taxonomy_term' ),
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
							'confirmDelete'   => $bundle['confirmDelete'],
							'deleteFailed'    => esc_html__( 'Could not delete this item. Please try again.', 'mage-eventpress' ),
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
