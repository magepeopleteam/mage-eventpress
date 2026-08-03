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

			public function __construct() {
				add_filter( 'screen_options_show_screen', [ $this, 'maybe_hide_screen_options' ], 10, 2 );
				add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
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
				wp_enqueue_script(
					'mpwem-post-list-modern',
					MPWEM_PLUGIN_URL . '/assets/admin/js/mpwem-post-list-modern.js',
					[],
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
						'strings'        => [
							'actionsColumn' => esc_html__( 'Actions', 'mage-eventpress' ),
							'promoEyebrow'  => esc_html__( 'EVENT PRO', 'mage-eventpress' ),
							'promoTitle'    => esc_html__( 'Get more out of your events', 'mage-eventpress' ),
							'promoBody'     => esc_html__( 'Unlock advanced attendee tools, PDF tickets, and reporting with Event Manager Pro and addons.', 'mage-eventpress' ),
							'promoCta'      => esc_html__( 'Explore Pro & Addons', 'mage-eventpress' ),
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
