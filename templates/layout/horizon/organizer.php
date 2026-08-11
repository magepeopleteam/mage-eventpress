<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? [];
	$_single_event_setting_sec = is_array( $event_infos ) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_organizer            = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_event_hide_org_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_org_from_details'] : 'no';
	if ( $hide_organizer === 'yes' ) {
		return;
	}
	$orgs = get_the_terms( $event_id, 'mep_org' );
	if ( ! is_array( $orgs ) || empty( $orgs ) ) {
		return;
	}
	$org      = $orgs[0];
	$org_link = get_term_link( $org );
	$date     = ! empty( $upcoming_date ) ? $upcoming_date : ( is_array( $event_infos ) && array_key_exists( 'event_start_datetime', $event_infos ) ? $event_infos['event_start_datetime'] : '' );
	$total_sold  = function_exists( 'mep_ticket_type_sold' ) ? (int) mep_ticket_type_sold( $event_id, '', $date ) : 0;
	$total_seats = method_exists( 'MPWEM_Functions', 'get_total_ticket' ) ? (int) MPWEM_Functions::get_total_ticket( $event_id, $date ) : 0;
	$available   = method_exists( 'MPWEM_Functions', 'get_total_available_seat' ) ? (int) MPWEM_Functions::get_total_available_seat( $event_id, $date ) : max( $total_seats - $total_sold, 0 );
	$available   = max( $available, 0 );
	$thumb_url   = get_the_post_thumbnail_url( $event_id, 'thumbnail' );
?>
<section class="horizon_organizer_card">
	<div class="horizon_organizer_info">
		<div class="horizon_organizer_identity">
			<div class="horizon_organizer_avatar">
				<?php if ( $thumb_url ) : ?>
					<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $org->name ); ?>"/>
				<?php else : ?>
					<span class="horizon_organizer_initial"><?php echo esc_html( strtoupper( substr( $org->name, 0, 1 ) ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="horizon_organizer_meta">
				<small><?php esc_html_e( 'Organized by', 'mage-eventpress' ); ?></small>
				<a class="horizon_organizer_name" href="<?php echo esc_url( ! is_wp_error( $org_link ) ? $org_link : '#' ); ?>">
					<?php echo esc_html( $org->name ); ?>
				</a>
			</div>
		</div>
		<?php if ( ! is_wp_error( $org_link ) ) : ?>
			<a class="horizon_follow_btn" href="<?php echo esc_url( $org_link ); ?>"><?php esc_html_e( 'Follow', 'mage-eventpress' ); ?></a>
		<?php endif; ?>
	</div>
	<div class="horizon_organizer_stats">
		<div class="horizon_stat horizon_stat_purple">
			<strong><?php echo esc_html( number_format_i18n( max( $total_seats, 0 ) ) ); ?></strong>
			<span><?php esc_html_e( 'Total Seats', 'mage-eventpress' ); ?></span>
		</div>
		<div class="horizon_stat horizon_stat_green">
			<strong><?php echo esc_html( number_format_i18n( $available ) ); ?></strong>
			<span><?php esc_html_e( 'Available', 'mage-eventpress' ); ?></span>
		</div>
		<div class="horizon_stat horizon_stat_yellow">
			<strong><?php echo esc_html( number_format_i18n( max( $total_sold, 0 ) ) ); ?></strong>
			<span><?php esc_html_e( 'Sold', 'mage-eventpress' ); ?></span>
		</div>
	</div>
</section>
