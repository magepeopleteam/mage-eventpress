<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id    = $event_id ?? 0;
	$event_infos = $event_infos ?? [];
	$thumb_id    = get_post_thumbnail_id( $event_id );
	$hero_url    = $thumb_id ? MPWEM_Global_Function::get_image_url( '', $thumb_id, 'full' ) : '';

	$cats     = get_the_terms( $event_id, 'mep_cat' );
	$cat_names = [];
	if ( is_array( $cats ) && ! empty( $cats ) ) {
		foreach ( $cats as $cat ) {
			$cat_names[] = strtoupper( $cat->name );
		}
	}
	$badge_parts = $cat_names;
	if ( empty( $badge_parts ) ) {
		$badge_parts = [ __( 'EVENT', 'mage-eventpress' ) ];
	}
	$badge_parts[] = __( 'LIVE', 'mage-eventpress' );
	$badge_text    = implode( ' • ', array_slice( $badge_parts, 0, 3 ) );

	$desc_full = '';
	$excerpt   = get_the_excerpt( $event_id );
	$content   = wp_strip_all_tags( (string) get_post_field( 'post_content', $event_id ) );
	$content   = trim( preg_replace( '/\s+/u', ' ', $content ) );
	$excerpt   = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $excerpt ) ) );
	if ( $content ) {
		$desc_full = $content;
	} elseif ( $excerpt ) {
		$desc_full = $excerpt;
	}
	$desc_len      = function_exists( 'mb_strlen' ) ? mb_strlen( $desc_full ) : strlen( $desc_full );
	$desc_needs_more = $desc_len > 90;

	$location     = is_array( $event_infos ) && array_key_exists( 'full_address', $event_infos ) ? $event_infos['full_address'] : [];
	$venue        = is_array( $event_infos ) && ! empty( $event_infos['mep_location_venue'] ) ? $event_infos['mep_location_venue'] : '';
	$city         = is_array( $event_infos ) && ! empty( $event_infos['mep_city'] ) ? $event_infos['mep_city'] : '';
	$location_full = '';
	if ( is_array( $location ) && sizeof( $location ) > 0 ) {
		$location_full = implode( ', ', array_filter( $location ) );
	}
	if ( ! $location_full ) {
		$location_full = trim( $venue . ( $venue && $city ? ', ' : '' ) . $city );
	}

	// Short label for the hero card: venue · city, then hard truncate.
	$location_short = $venue ?: $location_full;
	if ( $city && $venue && stripos( $venue, $city ) === false ) {
		$location_short = $venue . ' · ' . $city;
	} elseif ( ! $venue && $city ) {
		$location_short = $city;
	}
	$location_limit = 36;
	$full_len       = function_exists( 'mb_strlen' ) ? mb_strlen( $location_full ) : strlen( $location_full );
	$short_len      = function_exists( 'mb_strlen' ) ? mb_strlen( $location_short ) : strlen( $location_short );
	$needs_more     = ( $full_len > $location_limit ) || ( $location_full && $location_short !== $location_full );
	if ( $short_len > $location_limit ) {
		$location_short = function_exists( 'mb_substr' )
			? rtrim( mb_substr( $location_short, 0, $location_limit ) ) . '…'
			: rtrim( substr( $location_short, 0, $location_limit ) ) . '…';
		$needs_more = true;
	}
	$location_modal_id = 'horizon_location_modal_' . (int) $event_id;

	$start_dt = is_array( $event_infos ) && ! empty( $event_infos['event_start_datetime'] ) ? $event_infos['event_start_datetime'] : '';
	if ( ! $start_dt && ! empty( $upcoming_date ) ) {
		$start_dt = $upcoming_date;
	}
	$end_dt = is_array( $event_infos ) && ! empty( $event_infos['event_end_datetime'] ) ? $event_infos['event_end_datetime'] : '';

	$date_txt = $start_dt ? date_i18n( 'F j, Y', strtotime( $start_dt ) ) : '';
	$time_fmt = get_option( 'time_format' );
	$time_txt = '';
	if ( $start_dt ) {
		$time_txt = date_i18n( $time_fmt, strtotime( $start_dt ) );
		if ( $end_dt ) {
			$time_txt .= ' – ' . date_i18n( $time_fmt, strtotime( $end_dt ) );
		}
	}
?>
<section class="horizon_hero" <?php echo $hero_url ? 'style="--horizon-hero-image:url(\'' . esc_url( $hero_url ) . '\')"' : ''; ?>>
	<div class="horizon_hero_media" aria-hidden="true"></div>
	<div class="horizon_hero_shade" aria-hidden="true"></div>

	<div class="horizon_hero_inner">
		<div class="horizon_hero_content">
			<span class="horizon_hero_badge">
				<span class="horizon_hero_live" aria-hidden="true"></span>
				<?php echo esc_html( $badge_text ); ?>
			</span>
			<h1 class="horizon_hero_title"><?php echo esc_html( get_the_title( $event_id ) ); ?></h1>
			<?php if ( $desc_full ) : ?>
				<div class="horizon_hero_desc_wrap<?php echo $desc_needs_more ? ' is-collapsed' : ''; ?>">
					<p class="horizon_hero_desc"><?php echo esc_html( $desc_full ); ?></p>
					<?php if ( $desc_needs_more ) : ?>
						<button type="button" class="horizon_hero_desc_more" aria-expanded="false">
							<?php esc_html_e( 'Load more', 'mage-eventpress' ); ?>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="horizon_hero_cards">
			<?php if ( $date_txt ) : ?>
				<div class="horizon_info_card">
					<div class="horizon_info_label">
						<span class="horizon_info_icon"><i class="far fa-calendar" aria-hidden="true"></i></span>
						<span><?php esc_html_e( 'Date', 'mage-eventpress' ); ?></span>
					</div>
					<strong class="horizon_info_value"><?php echo esc_html( $date_txt ); ?></strong>
				</div>
			<?php endif; ?>

			<?php if ( $time_txt ) : ?>
				<div class="horizon_info_card">
					<div class="horizon_info_label">
						<span class="horizon_info_icon"><i class="far fa-clock" aria-hidden="true"></i></span>
						<span><?php esc_html_e( 'Time', 'mage-eventpress' ); ?></span>
					</div>
					<strong class="horizon_info_value"><?php echo esc_html( $time_txt ); ?></strong>
				</div>
			<?php endif; ?>

			<?php if ( $location_full ) : ?>
				<div class="horizon_info_card horizon_info_card_location">
					<div class="horizon_info_label">
						<span class="horizon_info_icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></span>
						<span><?php esc_html_e( 'Location', 'mage-eventpress' ); ?></span>
					</div>
					<div class="horizon_info_value_row">
						<strong class="horizon_info_value" title="<?php echo esc_attr( $location_full ); ?>">
							<?php echo esc_html( $location_short ); ?>
						</strong>
						<?php if ( $needs_more ) : ?>
							<button type="button" class="horizon_location_more" data-horizon-modal="<?php echo esc_attr( $location_modal_id ); ?>">
								<?php esc_html_e( 'View more', 'mage-eventpress' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( $needs_more ) : ?>
					<div class="horizon_modal" id="<?php echo esc_attr( $location_modal_id ); ?>" hidden>
						<div class="horizon_modal_backdrop" data-horizon-modal-close></div>
						<div class="horizon_modal_dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $location_modal_id ); ?>_title">
							<button type="button" class="horizon_modal_close" data-horizon-modal-close aria-label="<?php esc_attr_e( 'Close', 'mage-eventpress' ); ?>">
								<span class="fas fa-times" aria-hidden="true"></span>
							</button>
							<h3 class="horizon_modal_title" id="<?php echo esc_attr( $location_modal_id ); ?>_title">
								<span class="fas fa-map-marker-alt" aria-hidden="true"></span>
								<?php esc_html_e( 'Event Location', 'mage-eventpress' ); ?>
							</h3>
							<p class="horizon_modal_text"><?php echo esc_html( $location_full ); ?></p>
							<?php
								$map_query = rawurlencode( $location_full );
								$map_url   = 'https://www.google.com/maps/search/?api=1&query=' . $map_query;
							?>
							<a class="horizon_modal_map_btn" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Open in Google Maps', 'mage-eventpress' ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
