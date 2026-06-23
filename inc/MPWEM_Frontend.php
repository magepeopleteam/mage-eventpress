<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Frontend' ) ) {
		class MPWEM_Frontend {
			public function __construct() {
				add_filter( 'single_template', [ $this, 'load_events_templates' ] );
				add_filter( 'template_include', [ $this, 'load_tax_templates' ] );
				add_filter( 'archive_template', [ $this, 'load_archive_template' ] );
			}
			public function load_events_templates( $template ) {
				global $post;
				if ( $post->post_type == "mep_events" ) {
					$template = MPWEM_Functions::template_path( 'single-events.php' );
				}
				if ( $post->post_type == "mep_event_speaker" ) {
					$template = MPWEM_Functions::template_path( 'single-speaker.php' );
				}
				if ( $post->post_type == "mep_events_attendees" ) {
					$template = MPWEM_Functions::template_path( 'single-mep_events_attendees.php' );
				}
				return $template;
			}
			public function load_tax_templates( $template ) {
				if ( is_tax( 'mep_org' ) ) {
					$template = MPWEM_Functions::template_path( 'taxonomy-organozer.php' );
				}
				if ( is_tax( 'mep_cat' ) || is_tax( 'mep_tag' ) ) {
					$template = MPWEM_Functions::template_path( 'taxonomy-category.php' );
				}
				if ( get_query_var( 'cityname' ) ) {
					$template = MPWEM_Functions::template_path( 'page-city-filter.php' );
				}
				return $template;
			}
			public function load_archive_template( $template ) {
				if ( is_post_type_archive( 'mep_events' ) ) {
					$template = MPWEM_Functions::template_path( 'event-archive.php' );
				}
				return $template;
			}
		}
		new MPWEM_Frontend();
	}