<?php
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
$term_id = get_queried_object()->term_id;
?>
<div id="mage-container" class="mage" style="width: 100%;">
    <div class="mep-events-wrapper">
        <div class='mep_event_list'>
            <div class="mep_cat-details">
                <h1><?php echo get_queried_object()->name; ?></h1>
                <p><?php echo get_queried_object()->description; ?></p>
            </div>
            <div class='mage_grid_box'>
                <?php
                $loop = MPWEM_Query::event_query(20, 'ASC', '', $term_id, '', '', 'upcoming');
                while ($loop->have_posts()) {
                    $loop->the_post();
                    do_action('mep_event_list_shortcode', get_the_id(), 'three_column', 'grid');
                }
                wp_reset_postdata();
                ?>
            </div>
            <?php mep_event_pagination($loop->max_num_pages); ?>
        </div>
    </div>
</div>
<?php
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

