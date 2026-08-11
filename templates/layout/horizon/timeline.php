<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id = $event_id ?? get_the_id();
	$time_line_infos = get_post_meta( $event_id, 'mep_event_day', true );
	if ( ! is_array( $time_line_infos ) || empty( $time_line_infos ) ) {
		return;
	}
?>
<section class="horizon_section horizon_timeline_wrap">
	<h2 class="horizon_section_title"><?php esc_html_e( 'Event Timeline', 'mage-eventpress' ); ?></h2>
	<div class="horizon_timeline">
		<?php
			$counter = 0;
			foreach ( $time_line_infos as $time_line_info ) {
				$title   = is_array( $time_line_info ) && array_key_exists( 'mep_day_title', $time_line_info ) ? $time_line_info['mep_day_title'] : '';
				$time    = is_array( $time_line_info ) && array_key_exists( 'mep_day_time', $time_line_info ) ? $time_line_info['mep_day_time'] : '';
				$content = is_array( $time_line_info ) && array_key_exists( 'mep_day_content', $time_line_info ) ? $time_line_info['mep_day_content'] : '';
				$plain   = wp_trim_words( wp_strip_all_tags( $content ), 28, '…' );
				$is_done = $counter < 2;
				$counter++;
				?>
				<div class="horizon_timeline_item<?php echo $is_done ? ' is-active' : ''; ?>">
					<span class="horizon_timeline_dot" aria-hidden="true"></span>
					<div class="horizon_timeline_card">
						<?php if ( $time ) : ?>
							<span class="horizon_timeline_time"><?php echo esc_html( $time ); ?></span>
						<?php endif; ?>
						<?php if ( $title ) : ?>
							<h3 class="horizon_timeline_title"><?php echo esc_html( $title ); ?></h3>
						<?php endif; ?>
						<?php if ( $plain ) : ?>
							<p class="horizon_timeline_desc"><?php echo esc_html( $plain ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php } ?>
	</div>
</section>
