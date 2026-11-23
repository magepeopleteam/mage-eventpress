<?php
	/**
	 * Regression tests: the default theme speaker list with one speaker.
	 *
	 * The default theme laid speakers out in a fixed two-column grid, so a single speaker
	 * filled half of the sidebar box with a narrow card and its role cut after 30 characters.
	 * One speaker now renders as a full-width profile card (avatar beside name and role,
	 * role preview up to 90 characters cut on a word boundary). Two or more speakers keep the
	 * existing grid markup.
	 *
	 * Not loaded by the plugin. Run it explicitly, on a development or staging site:
	 *
	 *   wp eval-file wp-content/plugins/mage-eventpress/tests/regression/single-speaker-card.php
	 *
	 * A site whose environment type is "production" is refused unless `allow-production` is
	 * passed as an argument. The run creates its own speaker posts and deletes them
	 * afterwards. Exits with status 1 when any case fails.
	 *
	 * @package mage-eventpress
	 */

	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		die( 'Run this through WP-CLI: wp eval-file ' . basename( __FILE__ ) );
	}
	if ( ! defined( 'MPWEM_PLUGIN_DIR' ) || ! class_exists( 'MPWEM_Global_Function' ) ) {
		WP_CLI::error( 'Event Booking Manager for WooCommerce must be active.' );
	}
	$mep_ssc_args = isset( $args ) && is_array( $args ) ? $args : array();
	if ( 'production' === wp_get_environment_type() && ! in_array( 'allow-production', $mep_ssc_args, true ) ) {
		WP_CLI::error( 'This site reports a production environment. Run these tests on a test site, or pass allow-production to override.' );
	}

	if ( ! class_exists( 'MEP_SSC_Run' ) ) {
		final class MEP_SSC_Run {
			/** @var array<string, bool> */
			public static $results = array();
			/** @var int[] */
			public static $posts = array();
			/** @var array<int, string> Speaker id => forced excerpt. */
			public static $excerpts = array();
			/** @var array<int, string> Speaker id => forced title. */
			public static $titles = array();
			/** @var int[] Speakers given a fake featured image. */
			public static $with_image = array();

			public static function check( $label, $pass, $detail = '' ) {
				self::$results[ $label ] = (bool) $pass;
				WP_CLI::log( sprintf( '  %s  %s%s', $pass ? 'PASS' : 'FAIL', $label, ( ! $pass && '' !== $detail ) ? ' - ' . $detail : '' ) );
			}

			public static function speaker( $title, $excerpt, $image = true ) {
				$id = wp_insert_post(
					array(
						'post_type'    => 'mep_event_speaker',
						'post_status'  => 'publish',
						'post_title'   => $title,
						'post_content' => 'Speaker biography used by the single speaker card regression test.',
					),
					true
				);
				if ( is_wp_error( $id ) ) {
					WP_CLI::error( 'Could not create a test speaker: ' . $id->get_error_message() );
				}
				self::$posts[]       = (int) $id;
				self::$excerpts[ $id ] = $excerpt;
				if ( $image ) {
					self::$with_image[] = (int) $id;
				}
				return (int) $id;
			}

			/** Render the plugin's own default speaker template (never a theme override). */
			public static function render( array $speaker_lists ) {
				$event_id    = 0;
				$event_infos = array();
				ob_start();
				include MPWEM_PLUGIN_DIR . '/templates/layout/default/speakers.php';
				return trim( (string) ob_get_clean() );
			}

			public static function xpath( $html ) {
				$doc = new DOMDocument();
				libxml_use_internal_errors( true );
				$doc->loadHTML( '<?xml encoding="utf-8"?><div id="mep-ssc-root">' . $html . '</div>' );
				libxml_clear_errors();
				return new DOMXPath( $doc );
			}

			public static function has_class( $class ) {
				return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
			}

			public static function cleanup() {
				foreach ( self::$posts as $id ) {
					wp_delete_post( $id, true );
				}
				self::$posts = array();
			}
		}
	}

	// Test-only data: forced excerpts/titles and a fake featured image, scoped to the test posts.
	add_filter(
		'get_the_excerpt',
		static function ( $excerpt, $post ) {
			return ( $post && isset( MEP_SSC_Run::$excerpts[ $post->ID ] ) ) ? MEP_SSC_Run::$excerpts[ $post->ID ] : $excerpt;
		},
		99,
		2
	);
	add_filter(
		'the_title',
		static function ( $title, $id = 0 ) {
			return isset( MEP_SSC_Run::$titles[ $id ] ) ? MEP_SSC_Run::$titles[ $id ] : $title;
		},
		99,
		2
	);
	add_filter(
		'post_thumbnail_id',
		static function ( $thumbnail_id, $post ) {
			$post = get_post( $post );
			return ( $post && in_array( (int) $post->ID, MEP_SSC_Run::$with_image, true ) ) ? 990000 + (int) $post->ID : $thumbnail_id;
		},
		10,
		2
	);
	add_filter(
		'wp_get_attachment_image_src',
		static function ( $image, $attachment_id ) {
			return ( $attachment_id > 990000 ) ? array( 'https://example.test/speaker-' . ( $attachment_id - 990000 ) . '.jpg', 300, 300, false ) : $image;
		},
		10,
		2
	);

	register_shutdown_function( array( 'MEP_SSC_Run', 'cleanup' ) );

	$long_role  = 'Meike is a paramedic and a mother. She combines her operational experience with everyday parenting and explains things in a way that makes the knowledge stick.';
	$short_role = 'Paramedic, mother and first aid trainer';

	$solo       = MEP_SSC_Run::speaker( 'Meike Single', $short_role );
	$solo_long  = MEP_SSC_Run::speaker( 'Meike Long', $long_role );
	$solo_plain = MEP_SSC_Run::speaker( 'zoe no photo', 'Trainer', false );
	$other      = MEP_SSC_Run::speaker( 'Second Speaker', $long_role );
	$hostile    = MEP_SSC_Run::speaker( 'Hostile', '' );
	MEP_SSC_Run::$titles[ $hostile ]   = '<img src=x onerror=alert(1)>Eve';
	MEP_SSC_Run::$excerpts[ $hostile ] = '"><script>alert(2)</script> payload that is long enough to collapse behind the read more toggle of the single card';

	WP_CLI::log( 'One speaker: full-width profile card' );
	$x     = MEP_SSC_Run::xpath( MEP_SSC_Run::render( array( (string) $solo ) ) );
	$list  = $x->query( '//div[' . MEP_SSC_Run::has_class( 'mep-default-speakers' ) . ']' );
	$cards = $x->query( '//div[' . MEP_SSC_Run::has_class( 'mep-default-speaker' ) . ']' );
	MEP_SSC_Run::check( 'list carries the single modifier', 1 === $list->length && false !== strpos( $list->item( 0 )->getAttribute( 'class' ), 'mep-default-speakers--single' ) );
	MEP_SSC_Run::check( 'exactly one card, marked single', 1 === $cards->length && false !== strpos( $cards->item( 0 )->getAttribute( 'class' ), 'mep-default-speaker--single' ) );
	$avatar_link = $x->query( '//a[' . MEP_SSC_Run::has_class( 'mep-default-speaker__avatar-link' ) . ']' );
	MEP_SSC_Run::check(
		'avatar link points at the speaker and is hidden from keyboard and screen readers',
		1 === $avatar_link->length
		&& get_permalink( $solo ) === $avatar_link->item( 0 )->getAttribute( 'href' )
		&& '-1' === $avatar_link->item( 0 )->getAttribute( 'tabindex' )
		&& 'true' === $avatar_link->item( 0 )->getAttribute( 'aria-hidden' )
		&& 1 === $x->query( './/img[@src="https://example.test/speaker-' . $solo . '.jpg"]', $avatar_link->item( 0 ) )->length
	);
	$name = $x->query( '//div[' . MEP_SSC_Run::has_class( 'mep-default-speaker__body' ) . ']/a[' . MEP_SSC_Run::has_class( 'mep-default-speaker__link' ) . ']/span[' . MEP_SSC_Run::has_class( 'mep-default-speaker__name' ) . ']' );
	MEP_SSC_Run::check( 'name is the focusable link inside the card body', 1 === $name->length && 'Meike Single' === trim( $name->item( 0 )->textContent ) );
	$role = $x->query( '//div[' . MEP_SSC_Run::has_class( 'mep-default-speaker__body' ) . ']/span[' . MEP_SSC_Run::has_class( 'mep-default-speaker__role' ) . ']' );
	MEP_SSC_Run::check( 'role up to 90 characters is shown whole, without a toggle', 1 === $role->length && $short_role === trim( $role->item( 0 )->textContent ) && 0 === $x->query( '//button' )->length );
	MEP_SSC_Run::check( 'grid-only meta wrapper is not used', 0 === $x->query( '//span[' . MEP_SSC_Run::has_class( 'mep-default-speaker__meta' ) . ']' )->length );

	WP_CLI::log( 'One speaker among blank and zero ids' );
	$x = MEP_SSC_Run::xpath( MEP_SSC_Run::render( array( '', '0', (string) $solo, 'abc' ) ) );
	MEP_SSC_Run::check( 'invalid ids do not count as speakers', 1 === $x->query( '//div[' . MEP_SSC_Run::has_class( 'mep-default-speaker--single' ) . ']' )->length && 1 === $x->query( '//div[' . MEP_SSC_Run::has_class( 'mep-default-speakers--single' ) . ']' )->length );
	MEP_SSC_Run::check( 'nothing renders when no id is valid', '' === MEP_SSC_Run::render( array( '', '0', 'abc' ) ) && '' === MEP_SSC_Run::render( array() ) );

	WP_CLI::log( 'One speaker with a long role' );
	$x    = MEP_SSC_Run::xpath( MEP_SSC_Run::render( array( $solo_long ) ) );
	$text = $x->query( '//span[' . MEP_SSC_Run::has_class( 'mep-default-speaker__role-text' ) . ']' );
	$short = $text->length ? $text->item( 0 )->getAttribute( 'data-short' ) : '';
	$len   = mb_strlen( $short );
	MEP_SSC_Run::check( 'preview is longer than the grid limit and at most 90 characters', $len > 30 && $len <= 90, (string) $len );
	MEP_SSC_Run::check(
		'preview ends on a word boundary',
		'' !== $short && 0 === strpos( $long_role, $short ) && ' ' === mb_substr( $long_role, $len, 1 ),
		$short
	);
	MEP_SSC_Run::check( 'full role kept for Read more', $text->length && $long_role === $text->item( 0 )->getAttribute( 'data-full' ) );
	MEP_SSC_Run::check( 'Read more toggle present and collapsed', 1 === $x->query( '//button[@data-mep-speaker-role-toggle and @aria-expanded="false"]' )->length );

	WP_CLI::log( 'One speaker without a photo' );
	$x = MEP_SSC_Run::xpath( MEP_SSC_Run::render( array( $solo_plain ) ) );
	$initial = $x->query( '//span[' . MEP_SSC_Run::has_class( 'mep-default-speaker__initial' ) . ']' );
	MEP_SSC_Run::check( 'initial shown instead of an image', 1 === $initial->length && 'Z' === trim( $initial->item( 0 )->textContent ) && 0 === $x->query( '//img' )->length );

	WP_CLI::log( 'Two speakers: unchanged grid' );
	$x = MEP_SSC_Run::xpath( MEP_SSC_Run::render( array( $solo, $other ) ) );
	$list = $x->query( '//div[' . MEP_SSC_Run::has_class( 'mep-default-speakers' ) . ']' );
	MEP_SSC_Run::check( 'no single modifier on the list', 1 === $list->length && false === strpos( $list->item( 0 )->getAttribute( 'class' ), '--single' ) );
	MEP_SSC_Run::check( 'no card is marked single', 0 === $x->query( '//*[' . MEP_SSC_Run::has_class( 'mep-default-speaker--single' ) . ']' )->length && 0 === $x->query( '//*[' . MEP_SSC_Run::has_class( 'mep-default-speaker__body' ) . ']' )->length );
	$grid_links = $x->query( '//div[@class="mep-default-speaker"]/a[@class="mep-default-speaker__link"]' );
	$grid_ok    = 2 === $grid_links->length;
	foreach ( $grid_links as $link ) {
		$grid_ok = $grid_ok
			&& 1 === $x->query( './span[' . MEP_SSC_Run::has_class( 'mep-default-speaker__avatar' ) . ']', $link )->length
			&& 1 === $x->query( './span[@class="mep-default-speaker__meta"]/span[@class="mep-default-speaker__name"]', $link )->length;
	}
	MEP_SSC_Run::check( 'each card keeps avatar + name inside one link', $grid_ok );
	$grid_short = $x->query( '//span[' . MEP_SSC_Run::has_class( 'mep-default-speaker__role-text' ) . ']' );
	$grid_cut   = 2 === $grid_short->length;
	foreach ( $grid_short as $span ) {
		$full     = $span->getAttribute( 'data-full' );
		$grid_cut = $grid_cut && mb_substr( $full, 0, 30 ) === $span->getAttribute( 'data-short' );
	}
	MEP_SSC_Run::check( 'grid role previews still cut at exactly 30 characters', $grid_cut );

	WP_CLI::log( 'Escaping' );
	$html = MEP_SSC_Run::render( array( $hostile ) );
	$x    = MEP_SSC_Run::xpath( $html );
	MEP_SSC_Run::check( 'no script element or onerror image from title or role', 0 === $x->query( '//script' )->length && 0 === $x->query( '//*[@onerror]' )->length );
	MEP_SSC_Run::check( 'title and role printed as text', false !== strpos( $html, '&lt;img src=x onerror=alert(1)&gt;Eve' ) && false !== strpos( $html, '&lt;script&gt;' ) );
	$hostile_text = $x->query( '//span[' . MEP_SSC_Run::has_class( 'mep-default-speaker__role-text' ) . ']' );
	MEP_SSC_Run::check( 'role attributes keep the payload as data', 1 === $hostile_text->length && MEP_SSC_Run::$excerpts[ $hostile ] === $hostile_text->item( 0 )->getAttribute( 'data-full' ) );

	MEP_SSC_Run::cleanup();
	$failed = array_keys( array_filter( MEP_SSC_Run::$results, static function ( $ok ) {
		return ! $ok;
	} ) );
	$total  = count( MEP_SSC_Run::$results );
	if ( $failed ) {
		WP_CLI::log( sprintf( '%d of %d checks failed.', count( $failed ), $total ) );
		exit( 1 );
	}
	WP_CLI::success( sprintf( 'All %d checks passed.', $total ) );
