<?php
/**
 * Event Schedule — modern agenda date rows with AJAX "View More Dates".
 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? [];
	$event_infos               = ( is_array( $event_infos ) && sizeof( $event_infos ) > 0 ) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$all_dates                 = is_array( $event_infos ) && array_key_exists( 'all_date', $event_infos ) ? $event_infos['all_date'] : [];
	$all_dates                 = ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) ? $all_dates : MPWEM_Functions::get_dates( $event_id );
	$mep_show_end_datetime     = is_array( $event_infos ) && array_key_exists( 'mep_show_end_datetime', $event_infos ) ? $event_infos['mep_show_end_datetime'] : 'yes';
	$_single_event_setting_sec = is_array( $event_infos ) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_date_list            = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_event_hide_event_schedule_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_event_schedule_details'] : 'no';
	$selected_ts               = isset( $_GET['date'] ) ? absint( wp_unslash( $_GET['date'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- highlight only.

	$schedule_offset     = isset( $schedule_offset ) ? absint( $schedule_offset ) : 0;
	$schedule_limit      = isset( $schedule_limit ) ? absint( $schedule_limit ) : 5;
	$schedule_items_only = ! empty( $schedule_items_only );
	// Initial page load shows 5; AJAX passes limit=0 to mean "all remaining".
	$initial_limit = 5;

	if ( ! ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 && $hide_date_list == 'no' ) ) {
		return;
	}

	/**
	 * @param string $event_url
	 * @param int    $start_ts
	 * @param string $title
	 * @param string $meta
	 * @param bool   $is_active
	 * @param bool   $is_ajax_extra
	 */
	$render_simple_card = static function ( $event_url, $start_ts, $title, $meta, $is_active, $is_ajax_extra = false ) {
		$month   = wp_date( 'M', $start_ts );
		$day     = wp_date( 'j', $start_ts );
		$weekday = wp_date( 'l', $start_ts );
		$extra   = $is_ajax_extra ? ' mpwem-date-card--ajax' : '';
		?>
		<div class="date-list-item mpwem-date-card<?php echo $extra; ?><?php echo $is_active ? ' is-active' : ''; ?>">
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

	$date_type   = is_array( $event_infos ) && array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
	$date_count  = 0;
	$rendered    = 0;
	$total_dates = 0;

	ob_start();

	if ( $date_type == 'no' || $date_type == 'yes' ) {
		$date        = ! empty( $date ) ? $date : ( is_array( current( $all_dates ) ) && isset( current( $all_dates )['time'] ) ? current( $all_dates )['time'] : '' );
		$date_format = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : 'date';
		foreach ( $all_dates as $dates ) {
			$start_time = is_array( $dates ) && array_key_exists( 'time', $dates ) ? $dates['time'] : '';
			$end_time   = is_array( $dates ) && array_key_exists( 'end', $dates ) ? $dates['end'] : '';
			if ( ! $start_time ) {
				continue;
			}
			$total_dates++;
			$index = $date_count;
			$date_count++;

			if ( $index < $schedule_offset ) {
				continue;
			}
			if ( $schedule_limit > 0 && $rendered >= $schedule_limit ) {
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
			if ( ! $selected_ts && 0 === $index ) {
				$is_active = true;
			}

			$render_simple_card( $event_url, $start_ts, $title, $meta, $is_active, $schedule_items_only );
			$rendered++;
		}
	} else {
		foreach ( $all_dates as $date ) {
			$all_times  = MPWEM_Functions::get_times( $event_id, $all_dates, $date );
			$day_ts     = strtotime( $date );
			$day_active = false;
			$index      = $date_count;

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

				$total_dates++;
				$date_count++;

				if ( $index < $schedule_offset ) {
					continue;
				}
				if ( $schedule_limit > 0 && $rendered >= $schedule_limit ) {
					continue;
				}

				if ( ! $selected_ts && 0 === $index ) {
					$day_active            = true;
					$sessions[0]['active'] = true;
				}

				if ( 1 === count( $sessions ) ) {
					$session = $sessions[0];
					$title   = get_mep_datetime( $date, 'date' );
					$meta    = $session['label']
						? sprintf( '%s · %s', $session['label'], $session['time'] )
						: $session['time'];
					$render_simple_card( $session['url'], $session['ts'], $title, $meta, ! empty( $session['active'] ), $schedule_items_only );
				} else {
					$first_url = $sessions[0]['url'];
					$extra     = $schedule_items_only ? ' mpwem-date-card--ajax' : '';
					?>
					<div class="date-list-item mpwem-date-card mpwem-date-card--sessions<?php echo esc_attr( $extra ); ?><?php echo $day_active ? ' is-active' : ''; ?>">
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
												<span class="mpwem-date-card__session-sep" aria-hidden="true">·</span>
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
				$rendered++;
			} else {
				$total_dates++;
				$date_count++;

				if ( $index < $schedule_offset ) {
					continue;
				}
				if ( $schedule_limit > 0 && $rendered >= $schedule_limit ) {
					continue;
				}

				$event_url = add_query_arg(
					[
						'action'   => 'mpwem_date_' . $event_id,
						'date'     => $day_ts,
						'_wpnonce' => wp_create_nonce( 'mpwem_date_' . $event_id ),
					],
					get_the_permalink( $event_id )
				);
				$is_active = $selected_ts && (int) $selected_ts === (int) $day_ts;
				if ( ! $selected_ts && 0 === $index ) {
					$is_active = true;
				}
				$render_simple_card( $event_url, $day_ts, get_mep_datetime( $date, 'date' ), '', $is_active, $schedule_items_only );
				$rendered++;
			}
		}
	}

	$items_html = ob_get_clean();

	if ( $schedule_items_only ) {
		echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- template markup.
		return;
	}
	?>
	<div class="date_list_area mpwem-date-list" data-event-id="<?php echo esc_attr( (string) $event_id ); ?>" data-schedule-offset="<?php echo esc_attr( (string) $initial_limit ); ?>">
		<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- template markup. ?>
		<div class="mpwem-date-list__ajax" hidden></div>
	</div>
	<?php if ( $total_dates > $initial_limit ) { ?>
		<button type="button"
			class="mpwem-date-list__more mpwem_get_schedule_more _button_theme_margin_auto"
			data-event-id="<?php echo esc_attr( (string) $event_id ); ?>"
			data-offset="<?php echo esc_attr( (string) $initial_limit ); ?>"
			data-open-text="<?php esc_attr_e( 'Hide Date Lists', 'mage-eventpress' ); ?>"
			data-close-text="<?php esc_attr_e( 'View More Dates', 'mage-eventpress' ); ?>"
			aria-expanded="false">
			<span data-text><?php esc_html_e( 'View More Dates', 'mage-eventpress' ); ?></span>
		</button>
	<?php }
