<?php
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
	if ( post_password_required() ) { ?>
        <div class="mep-events-wrapper">
			<?php echo get_the_password_form(); ?>
        </div>
	<?php } else {
		// Sanitize the cityname parameter to prevent XSS attacks
		$city = get_query_var( 'cityname' );
		$city = sanitize_text_field( $city );
		
		// Remove any potentially dangerous characters that could break shortcode syntax
		// Remove brackets, quotes, and script tags to prevent injection
		$city = str_replace( array( '[', ']', '<', '>', '"', "'", '`' ), '', $city );
		$city = trim( $city );
		
		// Build the shortcode with properly escaped and quoted attribute value
		if ( ! empty( $city ) ) {
			// Use esc_attr() to escape the value and wrap in quotes to prevent shortcode injection
			$shortcode = '[event-list city="' . esc_attr( $city ) . '"]';
		} else {
			// If city is empty, show all events
			$shortcode = '[event-list]';
		}
		?>
		<div id="mage-container" class="mage">
			<div class="mpwem_style mpwem_wrapper">
				<div class="mpwem_container">
					<div class='mep_city_filter_page'>
						<?php echo do_shortcode( $shortcode ); ?>
					</div>
				</div>
			</div>
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

