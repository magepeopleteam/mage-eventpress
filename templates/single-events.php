<?php
	/**
	 * Template for Single Event Details
	 *
	 * Template Post Type: mep_event
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
// ==============================
// HEADER
// ==============================
	if ( wp_is_block_theme() ) {
		if ( function_exists( 'block_header_area' ) ) {
			// Try rendering block header
			ob_start();
			block_header_area();
			$header_html = trim( ob_get_clean() );
			if ( $header_html ) {
				// If the block theme has a header part, print it
				wp_head();
				wp_body_open();
				echo '<header class="wp-block-template-part site-header">';
				echo $header_html;
				echo '</header>';
			} else {
				// Fallback → if no header part is defined in theme
				get_header();
			}
		} else {
			// Fallback if function doesn't exist (older WP)
			get_header();
		}
	} else {
		// Classic theme → always works
		get_header();
	}
// ==============================
// MAIN CONTENT
// ==============================
	the_post();
	global $post, $woocommerce;
	$event_id              = get_the_ID();
	$event_meta            = get_post_custom( $event_id );
	if ( post_password_required() ) : ?>
        <div class="mep-events-wrapper">
			<?php echo get_the_password_form(); ?>
        </div>
	<?php else:
		$current_template = ! empty( $event_meta['mep_event_template'][0] ) ? $event_meta['mep_event_template'][0] : '';
		$global_template   = mep_get_option( 'mep_global_single_template', 'single_event_setting_sec', 'default-theme.php' );
		$_current_template = $current_template ?: $global_template;
		$fatal_error_fix = mep_get_option( 'mep_fix_details_page_fatal_error', 'general_setting_sec', 'disable' );
		do_action( 'mep_event_single_page_after_header', $event_id );
		?>
        <div class="mep-events-wrapper wrapper">
            <div class="mpwem_container">
				<?php
					if ( $fatal_error_fix === 'disable' ) {
						do_action( 'woocommerce_before_single_product' );
					}
					$theme_name = "/themes/$_current_template";
					require_once MPWEM_Functions::template_path( $theme_name );
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
				?>
            </div>
			<?php do_action( 'after-single-events' ); ?>
        </div>
		<?php
		do_action( 'mep_event_single_template_end', $event_id );
		do_action( 'mep_event_single_page_before_footer', $event_id );
	endif;
// ==============================
// FOOTER
// ==============================
	if ( function_exists( 'block_footer_area' ) && wp_is_block_theme() ) {
		echo '<footer class="wp-block-template-part mep-site-footer">';
		block_footer_area();
		echo '</footer>';
		wp_footer();
	} else {
		get_footer();
	}
