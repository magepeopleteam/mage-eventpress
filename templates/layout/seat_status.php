<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$date     = $date ?? '';
	if ( $event_id > 0 ) {
		$show_available_seat = MPWEM_Global_Function::get_post_info( $event_id, 'mep_available_seat', 'on' );
		$total_sold          = MPWEM_Functions::get_total_sold( $event_id, $date );
		$total_ticket        = MPWEM_Functions::get_total_ticket( $event_id, $date );
		$total_reserve       = MPWEM_Functions::get_reserve_ticket( $event_id, $date );
		$total_available     = $total_ticket - ( $total_sold + $total_reserve );
		?>
		<div class="mep-default-sidrbar-price-seat">
			<div class="setas-info">
				<div class="total-seats">
					<div><?php esc_html_e( 'Total Seats', 'mage-eventpress' ); ?></div>
					<strong><?php echo esc_html( $total_ticket ); ?></strong>
				</div>
				<?php if ( $show_available_seat == 'on' ) { ?>
					<div class="available-seats">
						<div><?php esc_html_e( 'Available Seats', 'mage-eventpress' ); ?></div>
						<strong><?php echo esc_html( $total_available ); ?></strong>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

