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
?>
<section class="horizon_section horizon_dates">
	<h2 class="horizon_section_title"><?php esc_html_e( 'Event Dates', 'mage-eventpress' ); ?></h2>
	<div class="horizon_dates_row" role="list">
		<?php
			$i = 0;
			foreach ( $all_dates as $dates ) {
				$time_value = is_array( $dates ) && isset( $dates['time'] ) ? $dates['time'] : ( is_string( $dates ) ? $dates : '' );
				if ( ! $time_value ) {
					continue;
				}
				$ts        = strtotime( $time_value );
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
				?>
				<a class="horizon_date_card<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $link ); ?>" role="listitem">
					<span class="horizon_date_day"><?php echo esc_html( strtoupper( $day_name ) ); ?></span>
					<span class="horizon_date_num"><?php echo esc_html( $day_num ); ?></span>
				</a>
				<?php
				$i++;
			}
		?>
	</div>
</section>
