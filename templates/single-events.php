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
	if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
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
	$event_id = get_the_ID();
	if ( post_password_required() ) { ?>
        <div class="mep-events-wrapper">
			<?php echo get_the_password_form(); ?>
        </div>
	<?php } else {
		$event_infos = MPWEM_Functions::get_all_info( $event_id );
		//echo '<pre>';print_r( $event_infos );echo '</pre>';
		$current_template          = array_key_exists( 'mep_event_template', $event_infos ) ? $event_infos['mep_event_template'] : '';
		$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
		$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
        $template=MPWEM_Functions::get_details_template_name($event_id);
		$general_setting_sec       = array_key_exists( 'general_setting_sec', $event_infos ) ? $event_infos['general_setting_sec'] : [];
		$fatal_error_fix           = array_key_exists( 'mep_fix_details_page_fatal_error', $general_setting_sec ) ? $general_setting_sec['mep_fix_details_page_fatal_error'] : 'disable';
		?>
		<div id="mage-container" class="mage">
			<div class="mpwem_style mpwem_wrapper mep-events-wrapper wrapper" style="max-width: 100%;">
				<div class="mpwem_container">
					<?php
						if ( $fatal_error_fix === 'disable' ) {
							if ( ! class_exists( 'WC_Bundles' ) ) {
								if ( ! class_exists( 'WEPOF_Extra_Product_Options' ) ) {
									if ( ! class_exists( 'WC_Advanced_Country_Restrictions_Dist' ) ) {
										if ( ! class_exists( 'WC_Google_Analytics_Integration' ) ) {
											if ( ! class_exists( 'Xoo_Wl_Core' ) ) {
												if ( ! class_exists( 'Ultimate_Woocommerce_Gift_Card_Public' ) ) {
													if ( ! class_exists( 'WC_Google_Analytics' ) ) {
														do_action( 'woocommerce_before_single_product' );
													}
												}
											}
										}
									}
								}
							}
						}
						require_once MPWEM_Functions::details_template_path( $template );
						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
					?>
				</div>
				<?php do_action( 'after-single-events' ); ?>
			</div>
			<?php do_action( 'mep_event_single_page_before_footer', $event_id ); ?>
		</div>
		<?php
	}
// ==============================
// FOOTER
// ==============================
	if ( function_exists( 'block_footer_area' ) && function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
		echo '<footer class="wp-block-template-part mep-site-footer">';
		block_footer_area();
		echo '</footer>';
		wp_footer();
	} else {
		get_footer();
	}
