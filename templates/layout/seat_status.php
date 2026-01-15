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
        <div class="_layout_info_xs_dBRL_equalChild">
            <div class="_fdColumn_align_center">
                <span><?php esc_html_e( 'Total Seats', 'mage-eventpress' ); ?></span>
                <h6 class="_mp_zero"><?php echo esc_html( $total_ticket ); ?></h6>
            </div>
			<?php if ( $show_available_seat == 'on' ) { ?>
                <div class="_fdColumn_align_center">
                    <span><?php esc_html_e( 'Available', 'mage-eventpress' ); ?></span>
                    <h6 class="_mp_zero"><?php echo esc_html( $total_available ); ?></h6>
                </div>
			<?php } ?>
        </div>
		<?php
	}

