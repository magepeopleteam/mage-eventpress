<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id    = $event_id ?? 0;
	$event_infos = $event_infos ?? [];
	$all_dates   = is_array( $all_dates ) ? $all_dates : [];
	if ( empty( $all_dates ) ) {
		return;
	}
	$upcoming  = ! empty( $upcoming_date ) ? $upcoming_date : ( is_array( $event_infos ) && array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '' );
	$recurring = is_array( $event_infos ) && array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
	$visible_limit = 8;
	$now_ts        = current_time( 'timestamp' );

	$date_items = [];
	foreach ( $all_dates as $dates ) {
		$time_value = is_array( $dates ) && isset( $dates['time'] ) ? $dates['time'] : ( is_string( $dates ) ? $dates : '' );
		if ( ! $time_value ) {
			continue;
		}
		$ts = strtotime( $time_value );
		if ( ! $ts ) {
			continue;
		}
		// Prefer upcoming/next dates; keep past only if nothing upcoming would remain.
		$date_items[] = [
			'ts'         => $ts,
			'time_value' => $time_value,
		];
	}

	if ( empty( $date_items ) ) {
		return;
	}

	$upcoming_items = array_values(
		array_filter(
			$date_items,
			static function ( $item ) use ( $now_ts ) {
				return (int) $item['ts'] >= ( $now_ts - DAY_IN_SECONDS );
			}
		)
	);
	if ( ! empty( $upcoming_items ) ) {
		$date_items = $upcoming_items;
	}

	$total_dates = count( $date_items );
	$extra_count = max( 0, $total_dates - $visible_limit );
?>
<section class="horizon_section horizon_dates">
	<div class="horizon_dates_head">
		<h2 class="horizon_section_title"><?php esc_html_e( 'Event Dates', 'mage-eventpress' ); ?></h2>
		<?php if ( $extra_count > 0 ) { ?>
			<button type="button"
				class="horizon_dates_more"
				aria-expanded="false"
				data-open-text="<?php esc_attr_e( 'Less', 'mage-eventpress' ); ?>"
				data-close-text="<?php esc_attr_e( 'More', 'mage-eventpress' ); ?>">
				<span data-text><?php esc_html_e( 'More', 'mage-eventpress' ); ?></span>
				<span class="horizon_dates_more_count">+<?php echo esc_html( (string) $extra_count ); ?></span>
			</button>
		<?php } ?>
	</div>
	<div class="horizon_dates_row" role="list">
		<?php
			$i = 0;
			foreach ( $date_items as $item ) {
				$ts        = (int) $item['ts'];
				$is_active = false;
				if ( $upcoming && abs( strtotime( $upcoming ) - $ts ) < 60 ) {
					$is_active = true;
				} elseif ( ! $upcoming && $i === 0 ) {
					$is_active = true;
				}
				$day_name = date_i18n( 'D', $ts );
				$day_num  = date_i18n( 'j', $ts );
				$link     = get_permalink( $event_id );
				if ( $recurring !== 'no' ) {
					$link = add_query_arg( 'date', $ts, $link );
				}
				$is_extra = $i >= $visible_limit;
				?>
				<a class="horizon_date_card<?php echo $is_active ? ' is-active' : ''; ?><?php echo $is_extra ? ' horizon_date_card--extra' : ''; ?>"
					href="<?php echo esc_url( $link ); ?>"
					role="listitem"
					<?php echo $is_extra ? 'hidden' : ''; ?>>
					<span class="horizon_date_day"><?php echo esc_html( strtoupper( $day_name ) ); ?></span>
					<span class="horizon_date_num"><?php echo esc_html( $day_num ); ?></span>
				</a>
				<?php
				$i++;
			}
		?>
	</div>
</section>
