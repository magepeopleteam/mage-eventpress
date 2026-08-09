<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? [];
	$event_infos               = ( is_array( $event_infos ) && sizeof( $event_infos ) > 0 ) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$all_dates                 = is_array( $event_infos ) && array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_dates                 = ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) ? $all_dates : MPWEM_Functions::get_dates( $event_id );
	$upcoming_date             = is_array( $event_infos ) && array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$mep_show_end_datetime     = is_array( $event_infos ) && array_key_exists( 'mep_show_end_datetime', $event_infos ) ? $event_infos['mep_show_end_datetime'] : 'yes';
	$_single_event_setting_sec = is_array( $event_infos ) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_date_list            = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_event_hide_event_schedule_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_event_schedule_details'] : 'no';
	$date_count                = 0;
	$selected_ts               = isset( $_GET['date'] ) ? absint( wp_unslash( $_GET['date'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- highlight only.

	if ( ! ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 && $hide_date_list == 'no' ) ) {
		return;
	}

	/**
	 * @param string $event_url
	 * @param int    $start_ts
	 * @param string $title
	 * @param string $meta        Optional time/range line.
	 * @param bool   $is_active
	 * @param bool   $collapsed
	 */
	$render_simple_card = static function ( $event_url, $start_ts, $title, $meta, $is_active, $collapsed ) {
		$month   = wp_date( 'M', $start_ts );
		$day     = wp_date( 'j', $start_ts );
		$weekday = wp_date( 'l', $start_ts );
		?>
		<div class="date-list-item mpwem-date-card<?php echo $is_active ? ' is-active' : ''; ?>" <?php if ( $collapsed ) { ?>data-collapse="#mpwem_more_date"<?php } ?>>
			<div class="date_item mpwem-date-card__inner">
				<span class="mpwem-date-card__badge" aria-hidden="true">
					<span class="mpwem-date-card__month"><?php echo esc_html( $month ); ?></span>
					<span class="mpwem-date-card__day"><?php echo esc_html( $day ); ?></span>
				</span>
				<div class="mpwem-date-card__content">
					<span class="mpwem-date-card__weekday"><?php echo esc_html( $weekday ); ?></span>
					<a class="mpwem-date-card__date" href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( $title ); ?></a>
					<?php if ( $meta ) { ?>
						<span class="mpwem-date-card__meta"><?php echo esc_html( $meta ); ?></span>
					<?php } ?>
				</div>
				<a class="mpwem-date-card__go" href="<?php echo esc_url( $event_url ); ?>" aria-label="<?php esc_attr_e( 'Select this date', 'mage-eventpress' ); ?>">
					<span aria-hidden="true">›</span>
				</a>
			</div>
		</div>
		<?php
	};
	?>
	<div class="date_list_area mpwem-date-list">
		<?php
		$date_type = is_array( $event_infos ) && array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';

		if ( $date_type == 'no' || $date_type == 'yes' ) {
			$date        = ! empty( $date ) ? $date : current( $all_dates )['time'];
			$date_format = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : 'date';
			foreach ( $all_dates as $dates ) {
				$start_time = is_array( $dates ) && array_key_exists( 'time', $dates ) ? $dates['time'] : '';
				$end_time   = is_array( $dates ) && array_key_exists( 'end', $dates ) ? $dates['end'] : '';
				if ( ! $start_time ) {
					continue;
				}
				$start_ts  = strtotime( $start_time );
				$event_url = add_query_arg(
					[
						'action'   => 'mpwem_date_' . $event_id,
						'date'     => $start_ts,
						'_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ),
					],
					get_the_permalink( $event_id )
				);

				if ( $end_time && $mep_show_end_datetime == 'yes' ) {
					if ( strtotime( gmdate( 'Y-m-d', strtotime( $start_time ) ) ) == strtotime( gmdate( 'Y-m-d', strtotime( $end_time ) ) ) ) {
						$title = MPWEM_Global_Function::date_format( $start_time, $date_format );
						$meta  = MPWEM_Global_Function::date_format( $end_time, 'time' );
						$meta  = $meta ? sprintf(
							/* translators: %s: end time */
							__( 'Ends %s', 'mage-eventpress' ),
							$meta
						) : '';
					} else {
						$title = MPWEM_Global_Function::date_format( $start_time, $date_format ) . ' – ' . MPWEM_Global_Function::date_format( $end_time, $date_format );
						$meta  = '';
					}
				} else {
					$title = MPWEM_Global_Function::date_format( $start_time, $date_format );
					$meta  = '';
				}

				$is_active = $selected_ts && (int) $selected_ts === (int) $start_ts;
				if ( ! $selected_ts && 0 === $date_count ) {
					$is_active = true;
				}

				$render_simple_card( $event_url, $start_ts, $title, $meta, $is_active, $date_count > 4 );
				$date_count++;
			}
		} else {
			foreach ( $all_dates as $date ) {
				$all_times = MPWEM_Functions::get_times( $event_id, $all_dates, $date );
				$day_ts    = strtotime( $date );
				$collapsed = $date_count > 4;
				$day_active = false;

				if ( is_array( $all_times ) && sizeof( $all_times ) > 0 ) {
					$sessions = [];
					foreach ( $all_times as $times ) {
						$time_info = is_array( $times ) && array_key_exists( 'start', $times ) ? $times['start'] : [];
						if ( ! is_array( $time_info ) || empty( $time_info ) ) {
							continue;
						}
						$label = array_key_exists( 'label', $time_info ) ? $time_info['label'] : '';
						$time  = array_key_exists( 'time', $time_info ) ? $time_info['time'] : '';
						if ( ! $time ) {
							continue;
						}
						$full_date = $date . ' ' . $time;
						$start_ts  = strtotime( $full_date );
						$time_disp = MPWEM_Global_Function::date_format( $full_date, 'time' );
						$event_url = add_query_arg(
							[
								'action'   => 'mpwem_date_' . $event_id,
								'date'     => $start_ts,
								'_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ),
							],
							get_the_permalink( $event_id )
						);
						$session_active = $selected_ts && (int) $selected_ts === (int) $start_ts;
						if ( $session_active ) {
							$day_active = true;
						}
						$sessions[] = [
							'url'    => $event_url,
							'label'  => $label,
							'time'   => $time_disp,
							'active' => $session_active,
							'ts'     => $start_ts,
						];
					}

					if ( empty( $sessions ) ) {
						continue;
					}

					if ( ! $selected_ts && 0 === $date_count ) {
						$day_active           = true;
						$sessions[0]['active'] = true;
					}

					if ( 1 === count( $sessions ) ) {
						$session = $sessions[0];
						$title   = get_mep_datetime( $date, 'date' );
						$meta    = $session['label']
							? sprintf( '%s · %s', $session['label'], $session['time'] )
							: $session['time'];
						$render_simple_card( $session['url'], $session['ts'], $title, $meta, ! empty( $session['active'] ), $collapsed );
					} else {
						$first_url = $sessions[0]['url'];
						?>
						<div class="date-list-item mpwem-date-card mpwem-date-card--sessions<?php echo $day_active ? ' is-active' : ''; ?>" <?php if ( $collapsed ) { ?>data-collapse="#mpwem_more_date"<?php } ?>>
							<div class="date_item mpwem-date-card__inner">
								<span class="mpwem-date-card__badge" aria-hidden="true">
									<span class="mpwem-date-card__month"><?php echo esc_html( wp_date( 'M', $day_ts ) ); ?></span>
									<span class="mpwem-date-card__day"><?php echo esc_html( wp_date( 'j', $day_ts ) ); ?></span>
								</span>
								<div class="mpwem-date-card__content">
									<span class="mpwem-date-card__weekday"><?php echo esc_html( wp_date( 'l', $day_ts ) ); ?></span>
									<a class="mpwem-date-card__date" href="<?php echo esc_url( $first_url ); ?>"><?php echo esc_html( get_mep_datetime( $date, 'date' ) ); ?></a>
									<div class="mpwem-date-card__sessions">
										<?php foreach ( $sessions as $session ) { ?>
											<a class="mpwem-date-card__session<?php echo ! empty( $session['active'] ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( $session['url'] ); ?>">
												<?php if ( $session['label'] ) { ?>
													<span class="mpwem-date-card__session-label"><?php echo esc_html( $session['label'] ); ?></span>
												<?php } ?>
												<span class="mpwem-date-card__session-time"><?php echo esc_html( $session['time'] ); ?></span>
											</a>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					$date_count++;
				} else {
					$event_url = add_query_arg(
						[
							'action'   => 'mpwem_date_' . $event_id,
							'date'     => $day_ts,
							'_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ),
						],
						get_the_permalink( $event_id )
					);
					$is_active = $selected_ts && (int) $selected_ts === (int) $day_ts;
					if ( ! $selected_ts && 0 === $date_count ) {
						$is_active = true;
					}
					$render_simple_card( $event_url, $day_ts, get_mep_datetime( $date, 'date' ), '', $is_active, $collapsed );
					$date_count++;
				}
			}
		}
		?>
	</div>
	<?php if ( $date_count > 4 ) { ?>
		<button type="button" class="mpwem-date-list__more _button_theme_margin_auto" data-collapse-target="#mpwem_more_date" data-open-text="<?php esc_attr_e( 'Hide Date Lists', 'mage-eventpress' ); ?>" data-close-text="<?php esc_attr_e( 'View More Dates', 'mage-eventpress' ); ?>">
			<span data-text><?php esc_html_e( 'View More Dates', 'mage-eventpress' ); ?></span>
		</button>
	<?php }
