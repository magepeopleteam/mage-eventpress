<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*
	* Cancelled event notice. Rendered by MPWEM_Hooks for an event whose
	* "Cancel this event" switch is on, in one of two places:
	*
	*   banner  - under the event title, the full notice;
	*   booking - in place of the ticket / RSVP / enquiry box. When the banner has
	*             already been printed on this page it shrinks to a short
	*             "Booking closed" note so the message is not repeated; otherwise
	*             (Horizon theme, [event-add-cart-section], a custom template) it
	*             carries the full notice itself.
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id       = $event_id ?? 0;
	$cancel_context = $cancel_context ?? 'booking';
	$compact        = $compact ?? false;
	if ( $event_id <= 0 ) {
		return;
	}
	$notice = MPWEM_Global_Function::get_cancellation_notice( $event_id );

	// Visitors should still see when the event was meant to happen. A recurring
	// event has no single "original" date, so the line is left out for it.
	$scheduled = '';
	if ( 'no' === MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' ) && ! MPWEM_Global_Function::is_undated_event( $event_id ) ) {
		$start_dt  = (string) get_post_meta( $event_id, 'event_start_datetime', true );
		$scheduled = $start_dt ? MPWEM_Global_Function::date_format( $start_dt, 'full', $event_id ) : '';
	}
?>
<?php if ( $compact ) { ?>
    <div class="mpwem_cancelled_notice mpwem_cancelled_notice--compact" role="status">
        <span class="mpwem_cancelled_notice__icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
        <div class="mpwem_cancelled_notice__body">
            <h4 class="mpwem_cancelled_notice__title"><?php esc_html_e( 'Booking closed', 'mage-eventpress' ); ?></h4>
            <p class="mpwem_cancelled_notice__text"><?php esc_html_e( 'Tickets and registration are no longer available because this event has been cancelled.', 'mage-eventpress' ); ?></p>
        </div>
    </div>
<?php } else { ?>
    <div class="mpwem_cancelled_notice mpwem_cancelled_notice--<?php echo esc_attr( $cancel_context ); ?>" role="status">
        <span class="mpwem_cancelled_notice__icon" aria-hidden="true"><i class="fas fa-ban"></i></span>
        <div class="mpwem_cancelled_notice__body">
            <span class="mpwem_cancelled_notice__eyebrow"><?php esc_html_e( 'Cancelled', 'mage-eventpress' ); ?></span>
            <h4 class="mpwem_cancelled_notice__title"><?php echo esc_html( $notice['title'] ); ?></h4>
			<?php if ( '' !== $notice['message'] ) { ?>
                <div class="mpwem_cancelled_notice__text"><?php echo wp_kses_post( wpautop( $notice['message'] ) ); ?></div>
			<?php } ?>
            <ul class="mpwem_cancelled_notice__meta">
				<?php if ( $scheduled ) { ?>
                    <li>
                        <i class="far fa-calendar-times" aria-hidden="true"></i>
						<?php
							/* translators: %s: the event's original date and time. */
							echo esc_html( sprintf( __( 'Originally scheduled for %s', 'mage-eventpress' ), $scheduled ) );
						?>
                    </li>
				<?php } ?>
                <li>
                    <i class="fas fa-lock" aria-hidden="true"></i>
					<?php esc_html_e( 'Booking is closed for this event.', 'mage-eventpress' ); ?>
                </li>
            </ul>
			<?php do_action( 'mpwem_after_cancelled_notice', $event_id, $cancel_context ); ?>
        </div>
    </div>
<?php } ?>
