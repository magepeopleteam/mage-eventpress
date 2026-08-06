<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id    = $event_id ?? 0;
	$event_infos = $event_infos ?? [];
	$display     = is_array( $event_infos ) && array_key_exists( 'mep_display_slider', $event_infos ) ? $event_infos['mep_display_slider'] : 'on';
	if ( $display !== 'on' ) {
		return;
	}
	$gallery = MPWEM_Global_Function::get_post_info( $event_id, 'mep_gallery_images', array() );
	$gallery = is_array( $gallery ) ? array_values( array_filter( array_unique( $gallery ) ) ) : [];
	$thumb   = get_post_thumbnail_id( $event_id );
	if ( $thumb && ! in_array( $thumb, $gallery, true ) ) {
		array_unshift( $gallery, $thumb );
	}
	if ( empty( $gallery ) ) {
		return;
	}
	$gallery = array_slice( $gallery, 0, 4 );
	$count   = count( $gallery );
?>
<section class="horizon_section horizon_gallery">
	<h2 class="horizon_section_title"><?php esc_html_e( 'Event Gallery', 'mage-eventpress' ); ?></h2>
	<div class="horizon_gallery_grid horizon_gallery_count_<?php echo esc_attr( $count ); ?>">
		<?php foreach ( $gallery as $index => $image_id ) {
			$url = MPWEM_Global_Function::get_image_url( '', $image_id, 'large' );
			if ( ! $url ) {
				continue;
			}
			?>
			<figure class="horizon_gallery_item horizon_gallery_item_<?php echo esc_attr( $index + 1 ); ?>">
				<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( get_the_title( $event_id ) ); ?>" loading="lazy"/>
			</figure>
		<?php } ?>
	</div>
</section>
