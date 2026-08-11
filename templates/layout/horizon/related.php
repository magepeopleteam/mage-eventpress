<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id      = $event_id ?? 0;
	$event_infos   = $event_infos ?? [];
	$event_infos   = ( is_array( $event_infos ) && sizeof( $event_infos ) > 0 ) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$related_tours = is_array( $event_infos ) && array_key_exists( 'event_list', $event_infos ) ? array_filter( $event_infos['event_list'] ) : [];
	$display       = is_array( $event_infos ) && array_key_exists( 'mep_related_event_status', $event_infos ) ? $event_infos['mep_related_event_status'] : 'on';
	if ( ! is_array( $related_tours ) || empty( $related_tours ) || $display !== 'on' ) {
		return;
	}
	$related_tours = array_slice( array_values( $related_tours ), 0, 3 );
?>
<div class="horizon_related">
	<h2 class="horizon_section_title"><?php esc_html_e( 'You Might Also Like', 'mage-eventpress' ); ?></h2>
	<div class="horizon_related_grid">
		<?php foreach ( $related_tours as $_event_id ) {
			$_event_id = (int) $_event_id;
			if ( $_event_id <= 0 ) {
				continue;
			}
			$info      = MPWEM_Functions::get_all_info( $_event_id );
			$thumb     = get_the_post_thumbnail_url( $_event_id, 'large' );
			$title     = get_the_title( $_event_id );
			$link      = get_permalink( $_event_id );
			$start     = is_array( $info ) && ! empty( $info['upcoming_date'] ) ? $info['upcoming_date'] : ( is_array( $info ) && ! empty( $info['event_start_datetime'] ) ? $info['event_start_datetime'] : '' );
			$date_txt  = $start ? date_i18n( get_option( 'date_format' ), strtotime( $start ) ) : '';
			$loc       = is_array( $info ) && ! empty( $info['full_address'] ) && is_array( $info['full_address'] ) ? implode( ', ', $info['full_address'] ) : '';
			$cats      = get_the_terms( $_event_id, 'mep_cat' );
			$cat_name  = ( is_array( $cats ) && ! empty( $cats ) ) ? $cats[0]->name : __( 'Event', 'mage-eventpress' );
			$price_html = '';
			if ( method_exists( 'MPWEM_Functions', 'get_min_price' ) ) {
				$min_price = MPWEM_Functions::get_min_price( $_event_id );
				if ( $min_price !== '' && $min_price !== null ) {
					if ( function_exists( 'wc_price' ) ) {
						$price_html = wc_price( $min_price );
					} elseif ( method_exists( 'MPWEM_Global_Function', 'mep_format_price' ) ) {
						$price_html = MPWEM_Global_Function::mep_format_price( $min_price );
					} else {
						$price_html = esc_html( $min_price );
					}
				}
			}
			?>
			<article class="horizon_related_card">
				<a class="horizon_related_media" href="<?php echo esc_url( $link ); ?>">
					<?php if ( $thumb ) : ?>
						<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>"/>
					<?php else : ?>
						<span class="horizon_related_placeholder"></span>
					<?php endif; ?>
					<span class="horizon_related_badge"><?php echo esc_html( strtoupper( $cat_name ) ); ?></span>
				</a>
				<div class="horizon_related_body">
					<h3 class="horizon_related_title">
						<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
					</h3>
					<div class="horizon_related_meta">
						<?php if ( $date_txt ) : ?>
							<span><i class="far fa-calendar-alt" aria-hidden="true"></i> <?php echo esc_html( $date_txt ); ?></span>
						<?php endif; ?>
						<?php if ( $loc ) : ?>
							<span><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <?php echo esc_html( $loc ); ?></span>
						<?php endif; ?>
					</div>
					<div class="horizon_related_foot">
						<div class="horizon_related_price_wrap">
							<?php if ( $price_html ) : ?>
								<span class="horizon_related_price_label"><?php esc_html_e( 'From', 'mage-eventpress' ); ?></span>
								<span class="horizon_related_price"><?php echo wp_kses_post( $price_html ); ?></span>
							<?php else : ?>
								<span class="horizon_related_price horizon_related_price--empty"><?php esc_html_e( 'See tickets', 'mage-eventpress' ); ?></span>
							<?php endif; ?>
						</div>
						<a class="horizon_related_btn" href="<?php echo esc_url( $link ); ?>">
							<span><?php esc_html_e( 'View Details', 'mage-eventpress' ); ?></span>
							<i class="fas fa-arrow-right" aria-hidden="true"></i>
						</a>
					</div>
				</div>
			</article>
		<?php } ?>
	</div>
</div>
