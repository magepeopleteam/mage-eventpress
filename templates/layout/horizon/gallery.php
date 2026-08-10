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
	$title   = get_the_title( $event_id );
?>
<section class="horizon_section horizon_gallery" data-horizon-gallery>
	<h2 class="horizon_section_title"><?php esc_html_e( 'Event Gallery', 'mage-eventpress' ); ?></h2>
	<div class="horizon_gallery_grid horizon_gallery_count_<?php echo esc_attr( $count ); ?>">
		<?php
		$visible_index = 0;
		foreach ( $gallery as $image_id ) {
			$preview = MPWEM_Global_Function::get_image_url( '', $image_id, 'large' );
			$full    = MPWEM_Global_Function::get_image_url( '', $image_id, 'full' );
			if ( ! $preview ) {
				continue;
			}
			if ( ! $full ) {
				$full = $preview;
			}
			$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			if ( ! is_string( $alt ) || '' === trim( $alt ) ) {
				$alt = $title;
			}
			$visible_index++;
			?>
			<figure class="horizon_gallery_item horizon_gallery_item_<?php echo esc_attr( $visible_index ); ?>">
				<button
					type="button"
					class="horizon_gallery_zoom"
					data-horizon-gallery-trigger
					data-horizon-gallery-src="<?php echo esc_url( $full ); ?>"
					data-horizon-gallery-alt="<?php echo esc_attr( $alt ); ?>"
					data-horizon-gallery-index="<?php echo esc_attr( $visible_index - 1 ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: image alt text */ __( 'Zoom image: %s', 'mage-eventpress' ), $alt ) ); ?>"
				>
					<img src="<?php echo esc_url( $preview ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy"/>
					<span class="horizon_gallery_zoom_icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/>
							<path d="M16.5 16.5L20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
							<path d="M11 8.5V13.5M8.5 11H13.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
						</svg>
					</span>
				</button>
			</figure>
		<?php } ?>
	</div>

	<div class="horizon_gallery_lightbox" data-horizon-gallery-lightbox hidden>
		<button type="button" class="horizon_gallery_lightbox__backdrop" data-horizon-gallery-close aria-label="<?php esc_attr_e( 'Close gallery', 'mage-eventpress' ); ?>"></button>
		<div class="horizon_gallery_lightbox__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Gallery zoom', 'mage-eventpress' ); ?>">
			<button type="button" class="horizon_gallery_lightbox__close" data-horizon-gallery-close aria-label="<?php esc_attr_e( 'Close', 'mage-eventpress' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
			<button type="button" class="horizon_gallery_lightbox__nav horizon_gallery_lightbox__prev" data-horizon-gallery-prev aria-label="<?php esc_attr_e( 'Previous image', 'mage-eventpress' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<figure class="horizon_gallery_lightbox__figure">
				<img src="" alt="" data-horizon-gallery-img />
			</figure>
			<button type="button" class="horizon_gallery_lightbox__nav horizon_gallery_lightbox__next" data-horizon-gallery-next aria-label="<?php esc_attr_e( 'Next image', 'mage-eventpress' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<p class="horizon_gallery_lightbox__counter" data-horizon-gallery-counter hidden></p>
		</div>
	</div>
</section>
