<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id = $event_id ?? 0;
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$event_infos = $event_infos ?? [];
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$event_infos     = ( is_array( $event_infos ) && sizeof( $event_infos ) > 0 ) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$related_tours   = is_array( $event_infos ) && array_key_exists( 'event_list', $event_infos ) ? array_filter( $event_infos['event_list'] ) : [];
	$display_related = is_array( $event_infos ) && array_key_exists( 'mep_related_event_status', $event_infos ) ? $event_infos['mep_related_event_status'] : 'on';

	if ( ! is_array( $related_tours ) || empty( $related_tours ) || $display_related !== 'on' ) {
		return;
	}

	$related_label = is_array( $event_infos ) && array_key_exists( 'related_section_label', $event_infos ) ? $event_infos['related_section_label'] : [];
	$related_label = $related_label ?: __( 'Related Events', 'mage-eventpress' );
	?>
	<div class="mpwem_related_area on_load_off">
		<div class="related_title">
			<div class="related_heading">
				<span class="related_eyebrow"><?php esc_html_e( 'Discover more', 'mage-eventpress' ); ?></span>
				<h3><?php echo esc_html( $related_label ); ?></h3>
			</div>
			<div class="related_navigation">
				<button class="related_prev" type="button" aria-label="<?php esc_attr_e( 'Previous related events', 'mage-eventpress' ); ?>">
					<span class="fas fa-arrow-left" aria-hidden="true"></span>
				</button>
				<button class="related_next" type="button" aria-label="<?php esc_attr_e( 'Next related events', 'mage-eventpress' ); ?>">
					<span class="fas fa-arrow-right" aria-hidden="true"></span>
				</button>
			</div>
		</div>

		<div class="related_item">
			<?php
			foreach ( $related_tours as $_event_id ) {
				$_event_id = (int) $_event_id;
				if ( $_event_id <= 0 ) {
					continue;
				}

				$info  = MPWEM_Functions::get_all_info( $_event_id );
				$thumb = get_the_post_thumbnail_url( $_event_id, 'large' );
				$title = get_the_title( $_event_id );
				$link  = get_permalink( $_event_id );

				$start = '';
				if ( is_array( $info ) ) {
					if ( ! empty( $info['upcoming_date'] ) ) {
						$start = $info['upcoming_date'];
					} elseif ( ! empty( $info['event_start_datetime'] ) ) {
						$start = $info['event_start_datetime'];
					} elseif ( ! empty( $info['event_start_date'] ) ) {
						$start = $info['event_start_date'];
					}
				}

				$month    = $start && class_exists( 'MPWEM_Global_Function' ) ? MPWEM_Global_Function::date_format( $start, 'month' ) : ( $start ? date_i18n( 'M', strtotime( $start ) ) : '' );
				$day      = $start && class_exists( 'MPWEM_Global_Function' ) ? MPWEM_Global_Function::date_format( $start, 'day' ) : ( $start ? date_i18n( 'd', strtotime( $start ) ) : '' );
				$date_txt = $start && class_exists( 'MPWEM_Global_Function' ) ? MPWEM_Global_Function::date_format( $start ) : ( $start ? date_i18n( get_option( 'date_format' ), strtotime( $start ) ) : '' );

				$venue = is_array( $info ) && ! empty( $info['mep_location_venue'] ) ? $info['mep_location_venue'] : '';

				$cats     = get_the_terms( $_event_id, 'mep_cat' );
				$cat_name = ( is_array( $cats ) && ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

				$price_html = '';
				if ( method_exists( 'MPWEM_Functions', 'get_min_price' ) ) {
					$min_price = MPWEM_Functions::get_min_price( $_event_id );
					if ( $min_price !== '' && $min_price !== null && (float) $min_price > 0 ) {
						if ( function_exists( 'wc_price' ) ) {
							$price_html = wc_price( $min_price );
						} else {
							$price_html = esc_html( $min_price );
						}
					}
				}
				?>
				<article class="mpwem_related_card">
					<a class="mpwem_related_media" href="<?php echo esc_url( $link ); ?>">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy"/>
						<?php else : ?>
							<span class="mpwem_related_placeholder" aria-hidden="true"></span>
						<?php endif; ?>
						<?php if ( $month || $day ) : ?>
							<span class="mpwem_related_date">
								<span class="mpwem_related_month"><?php echo esc_html( $month ); ?></span>
								<span class="mpwem_related_day"><?php echo esc_html( $day ); ?></span>
							</span>
						<?php endif; ?>
						<?php if ( $cat_name ) : ?>
							<span class="mpwem_related_badge"><?php echo esc_html( $cat_name ); ?></span>
						<?php endif; ?>
					</a>
					<div class="mpwem_related_body">
						<h4 class="mpwem_related_event_title">
							<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
						</h4>
						<div class="mpwem_related_meta">
							<?php if ( $date_txt ) : ?>
								<span class="mpwem_related_meta_item">
									<span class="mi mi-clock" aria-hidden="true"></span>
									<?php echo esc_html( $date_txt ); ?>
								</span>
							<?php endif; ?>
							<?php if ( $venue ) : ?>
								<span class="mpwem_related_meta_item">
									<span class="mi mi-marker" aria-hidden="true"></span>
									<?php echo esc_html( $venue ); ?>
								</span>
							<?php endif; ?>
						</div>
						<div class="mpwem_related_foot">
							<?php if ( $price_html ) : ?>
								<span class="mpwem_related_price">
									<span class="mpwem_related_price_label"><?php esc_html_e( 'From', 'mage-eventpress' ); ?></span>
									<?php echo wp_kses_post( $price_html ); ?>
								</span>
							<?php else : ?>
								<span class="mpwem_related_price mpwem_related_price--empty">&nbsp;</span>
							<?php endif; ?>
							<a class="mpwem_related_link" href="<?php echo esc_url( $link ); ?>">
								<?php esc_html_e( 'View', 'mage-eventpress' ); ?>
								<span class="fas fa-arrow-right" aria-hidden="true"></span>
							</a>
						</div>
					</div>
				</article>
			<?php } ?>
		</div>
	</div>
