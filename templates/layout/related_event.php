<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id = $event_id ?? 0;
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$event_infos = $event_infos ?? [];
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$event_infos     = (is_array( $event_infos ) && sizeof( $event_infos ) > 0) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$related_tours   = array_key_exists( 'event_list', $event_infos ) ? $event_infos['event_list'] : [];

	$display_related = array_key_exists( 'display_related', $event_infos ) ? $event_infos['display_related'] : 'on';
	if ( is_array( $related_tours ) && sizeof( $related_tours ) > 0 && $display_related == 'on' ) {
		$related_label   = array_key_exists( 'related_section_label', $event_infos ) ? $event_infos['related_section_label'] : [];
		$related_label=$related_label?:__( 'Related Events', 'mage-eventpress' );
		?>
        <div class="mpwem_related_area">
            <div class="related_title _align_center_justify_between">
                <h3><?php echo esc_html($related_label); ?></h3>
                <div class="related_navigation">
                    <button class="related_prev _button_theme_xs" type="button"><span class="fas fa-chevron-left"></span></button>
                    <button class="related_next _button_theme_xs" type="button"><span class="fas fa-chevron-right"></span></button>
                </div>
            </div>

            <div class="related_item">
				<?php foreach ( $related_tours as $_event_id ) {
					do_action( 'mep_event_list_shortcode', $_event_id,'column_style', 'grid',25 );
				} ?>
            </div>
        </div>
	<?php } ?>