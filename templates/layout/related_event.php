<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id = $event_id ?? 0;
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$event_infos = $event_infos ?? [];
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$event_infos               = sizeof( $event_infos ) > 0 ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$related_tours                = array_key_exists( 'event_list', $event_infos ) ? $event_infos['event_list'] : [];
	$display_related                = array_key_exists( 'display_related', $event_infos ) ? $event_infos['display_related'] :'on';

	$related_tour_count = sizeof( $related_tours );
   // echo '<pre>';print_r($related_tours);echo '</pre>';
	$num_of_tour        = $num_of_tour ?? '';
	if ( is_array($related_tours) && sizeof($related_tours)>0 && $display_related== 'on') {
		$num_of_tour = sizeof( $related_tours );
		$num_of_tour = min( $num_of_tour, $related_tour_count );
		$grid_class  = $related_tour_count <= $num_of_tour ? 'grid_' . $num_of_tour : '';
		$div_class   = $related_tour_count == 1 ? 'flexWrap modern' : 'flexWrap grid';
		?>
        <div class="related_events">
            <h2><?php esc_html_e( 'Related Events', 'mage-eventpress' ); ?></h2>
            <div class="related_items">
				<?php foreach ( $related_tours as $ttbm_post_id ) { ?>
                    <div class="item">
                        <img src="<?php echo MPWEM_Global_Function::get_image_url($ttbm_post_id);?>" alt="">
                        <div class="item-info">
                            <div class="title">
                                <h2><?php echo get_the_title($ttbm_post_id); ?></h2>
                            </div>
                            <div class="price">
                                <h2><?php echo wp_kses_post(wc_price(MPWEM_Functions::get_min_price($ttbm_post_id))); ?></h2>
                                <p><?php esc_html_e('Per Ticket', 'mage-eventpress'); ?></p>
                            </div>
                        </div>
                    </div>
				<?php } ?>
            </div>
        </div>
	<?php } ?>