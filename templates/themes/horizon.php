<?php
	// Template Name: Horizon Theme  
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? MPWEM_Functions::get_all_info( $event_id );
	if ( ! is_array( $event_infos ) ) {
		$event_infos = [];
	}
	$all_dates                 = is_array( $event_infos ) && array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_times                 = is_array( $event_infos ) && array_key_exists( 'all_time', $event_infos ) ? $event_infos['all_time'] : [];
	$upcoming_date             = is_array( $event_infos ) && array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$speaker_title             = is_array( $event_infos ) && array_key_exists( 'mep_speaker_title', $event_infos ) ? $event_infos['mep_speaker_title'] : __( 'Artists & Performers', 'mage-eventpress' );
	$speaker_lists             = is_array( $event_infos ) && array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
	$speaker_lists             = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
	$_single_event_setting_sec = is_array( $event_infos ) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_date_list            = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_event_hide_event_schedule_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_event_schedule_details'] : 'no';
	$event_speaker_enabled     = is_array( $event_infos ) && array_key_exists( 'mep_event_enable_speaker', $event_infos ) ? $event_infos['mep_event_enable_speaker'] : 'no';
	$horizon_dir               = MPWEM_PLUGIN_DIR . '/templates/layout/horizon/';
?>
<div class="horizon_theme" data-horizon-theme="1">
	<?php
		if ( file_exists( $horizon_dir . 'hero.php' ) ) {
			include $horizon_dir . 'hero.php';
		}
	?>

	<div class="horizon_shell">
		<div class="horizon_body">
			<div class="horizon_main">
				<?php
					if ( file_exists( $horizon_dir . 'organizer.php' ) ) {
						include $horizon_dir . 'organizer.php';
					}
				?>

				<section class="horizon_section horizon_about">
					<?php
						if ( file_exists( $horizon_dir . 'about.php' ) ) {
							include $horizon_dir . 'about.php';
						} else {
							do_action( 'mpwem_description', $event_id, $event_infos );
						}
					?>
				</section>

				<?php
					if ( file_exists( $horizon_dir . 'gallery.php' ) ) {
						include $horizon_dir . 'gallery.php';
					}
				?>

				<?php if ( $event_speaker_enabled == 'yes' && is_array( $speaker_lists ) && sizeof( $speaker_lists ) > 0 ) { ?>
					<section class="horizon_section horizon_artists">
						<h2 class="horizon_section_title"><?php echo esc_html( $speaker_title ?: __( 'Artists & Performers', 'mage-eventpress' ) ); ?></h2>
						<?php
							if ( file_exists( $horizon_dir . 'speakers.php' ) ) {
								include $horizon_dir . 'speakers.php';
							} else {
								do_action( 'mpwem_speaker', $event_id, $event_infos );
							}
						?>
					</section>
				<?php } ?>

				<?php
					$timeline_status = get_post_meta( $event_id, 'mep_timeline_status', true ) ? get_post_meta( $event_id, 'mep_timeline_status', true ) : 'on';
					if ( $timeline_status == 'on' && file_exists( $horizon_dir . 'timeline.php' ) ) {
						include $horizon_dir . 'timeline.php';
					} elseif ( $timeline_status == 'on' ) {
						echo '<section class="horizon_section horizon_timeline_wrap">';
						do_action( 'mpwem_timeline', $event_id );
						echo '</section>';
					}
				?>

				<?php
					if ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 && $hide_date_list == 'no' && file_exists( $horizon_dir . 'dates.php' ) ) {
						include $horizon_dir . 'dates.php';
					}
				?>

				<?php
					$faq_status = get_post_meta( $event_id, 'mep_faq_status', true ) ? get_post_meta( $event_id, 'mep_faq_status', true ) : 'on';
					if ( $faq_status == 'on' && file_exists( $horizon_dir . 'faq.php' ) ) {
						include $horizon_dir . 'faq.php';
					} elseif ( $faq_status == 'on' ) {
						echo '<section class="horizon_section horizon_faq_wrap">';
						do_action( 'mpwem_faq', $event_id, $event_infos );
						echo '</section>';
					}
				?>
			</div>

			<aside class="horizon_sidebar">
				<div class="horizon_ticket_card">
					<div class="horizon_ticket_head">
						<span class="horizon_ticket_eyebrow"><?php esc_html_e( 'Reserve Your Spot', 'mage-eventpress' ); ?></span>
						<h3 class="horizon_ticket_title"><?php echo esc_html( get_the_title( $event_id ) ); ?></h3>
						<?php
							$venue = is_array( $event_infos ) && ! empty( $event_infos['mep_location_venue'] ) ? $event_infos['mep_location_venue'] : '';
							$city  = is_array( $event_infos ) && ! empty( $event_infos['mep_city'] ) ? $event_infos['mep_city'] : '';
							$loc_line = $venue;
							if ( $city && ( ! $venue || stripos( $venue, $city ) === false ) ) {
								$loc_line = trim( $venue . ( $venue ? ' · ' : '' ) . $city );
							}
							if ( ! $loc_line ) {
								$location = is_array( $event_infos ) && array_key_exists( 'full_address', $event_infos ) ? $event_infos['full_address'] : [];
								if ( is_array( $location ) && sizeof( $location ) > 0 ) {
									$loc_line = implode( ' · ', array_filter( array_slice( $location, 0, 2 ) ) );
								}
							}
							if ( $loc_line ) {
								?>
								<p class="horizon_ticket_location">
									<span class="fas fa-map-marker-alt" aria-hidden="true"></span>
									<span><?php echo esc_html( $loc_line ); ?></span>
								</p>
								<?php
							}
						?>
					</div>
					<div class="horizon_ticket_body">
						<?php do_action( 'mpwem_registration', $event_id, $event_infos ); ?>
					</div>
					<div class="horizon_ticket_foot">
						<div class="horizon_ticket_share">
							<span class="horizon_ticket_share_label"><?php esc_html_e( 'Share', 'mage-eventpress' ); ?></span>
							<?php do_action( 'mpwem_social', $event_id, $event_infos ); ?>
						</div>
						<div class="horizon_ticket_calendar">
							<?php do_action( 'mpwem_add_calender', $event_id, $all_dates, $upcoming_date ); ?>
						</div>
					</div>
				</div>
			</aside>
		</div>

		<?php
			/**
			 * Reviews sit after the main/sidebar row and before related events.
			 * Pro review addon hooks here on Horizon so the list stays inside .horizon_theme.
			 */
		?>
		<section class="horizon_section horizon_reviews_wrap" aria-label="<?php esc_attr_e( 'Event reviews', 'mage-eventpress' ); ?>">
			<?php do_action( 'mpwem_horizon_reviews', $event_id ); ?>
		</section>

		<section class="horizon_related_wrap">
			<?php
				if ( file_exists( $horizon_dir . 'related.php' ) ) {
					include $horizon_dir . 'related.php';
				} else {
					if ( empty( $event_infos['related_section_label'] ) ) {
						$event_infos['related_section_label'] = __( 'You Might Also Like', 'mage-eventpress' );
					}
					do_action( 'mpwem_related', $event_id, $event_infos );
				}
			?>
		</section>
	</div>

	<?php do_action( 'mpwem_template_footer', $event_id ); ?>
</div>
