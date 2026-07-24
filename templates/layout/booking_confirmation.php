<?php
/*
 * @Author      engr.sumonazma@gmail.com
 * Copyright:   mage-people.com
 *
 * Booking confirmation/cancellation notice — rendered above the registration form when
 * the URL contains ?mep_booking=success|pending|cancelled (and optionally &mep_booking_id=).
 * Receives $event_id, $booking_status and $booking_id from MPWEM_Booking_Confirmation::render().
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$event_id       = $event_id ?? 0;
$booking_status = $booking_status ?? '';
$booking_id     = $booking_id ?? '';

$status_map = array(
	'success'   => array(
		'class'   => 'mep-booking-success',
		'title'   => __( 'Registration Successful', 'mage-eventpress' ),
		'message' => __( 'Thank you! Your registration has been confirmed and a confirmation email has been sent to you.', 'mage-eventpress' ),
	),
	'pending'   => array(
		'class'   => 'mep-booking-pending',
		'title'   => __( 'Registration Received', 'mage-eventpress' ),
		'message' => __( 'Your registration has been received and is pending payment confirmation. The organizer will be in touch with payment details.', 'mage-eventpress' ),
	),
	'cancelled' => array(
		'class'   => 'mep-booking-cancelled',
		'title'   => __( 'Registration Cancelled', 'mage-eventpress' ),
		'message' => __( 'Your registration was cancelled and no payment was taken. Feel free to try again below.', 'mage-eventpress' ),
	),
);

$status = isset( $status_map[ $booking_status ] ) ? $status_map[ $booking_status ] : null;
if ( ! $status ) {
	return;
}

// Both the free plugin and the pro add-on store every booked ticket as a mep_events_attendees
// record with ea_order_id = the booking reference, so a single lookup covers both paths.
$attendees = array();
if ( $booking_id ) {
	$attendees = get_posts( array(
		'post_type'   => 'mep_events_attendees',
		'post_status' => 'any',
		'numberposts' => -1,
		'orderby'     => 'ID',
		'order'       => 'ASC',
		'meta_query'  => array(
			'relation' => 'AND',
			array(
				'key'   => 'ea_order_id',
				'value' => $booking_id,
			),
			array(
				'key'   => 'ea_event_id',
				'value' => $event_id,
			),
		),
	) );
}

$form_array = MPWEM_Layout::get_form_array( $event_id );
?>
<div class="mpwem_booking_panel mep-booking-confirmation <?php echo esc_attr( $status['class'] ); ?>">
	<div class="mep-booking-confirmation-header">
		<h3><?php echo esc_html( $status['title'] ); ?></h3>
		<p><?php echo esc_html( $status['message'] ); ?></p>
		<?php if ( $booking_id ) : ?>
			<p class="mep-booking-reference">
				<?php
				printf(
					/* translators: %s: booking reference number */
					esc_html__( 'Booking Reference: %s', 'mage-eventpress' ),
					'<strong>' . esc_html( $booking_id ) . '</strong>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>

	<?php if ( class_exists( 'MPWEM_Social_Card' ) && $booking_id ) : ?>
		<?php MPWEM_Social_Card::render_for_native( $event_id, $booking_id, $booking_status ); ?>
	<?php endif; ?>

	<?php if ( ! empty( $attendees ) ) :
		$order_total = 0.0;
		?>
		<div class="mep-booking-confirmation-summary">
			<h4><?php esc_html_e( 'Order Summary', 'mage-eventpress' ); ?></h4>
			<table class="mep-booking-summary-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Ticket', 'mage-eventpress' ); ?></th>
						<th><?php esc_html_e( 'Qty', 'mage-eventpress' ); ?></th>
						<th><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></th>
						<th><?php esc_html_e( 'Subtotal', 'mage-eventpress' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $attendees as $attendee ) :
						$ticket_name  = get_post_meta( $attendee->ID, 'ea_ticket_type', true );
						$ticket_qty   = (int) get_post_meta( $attendee->ID, 'ea_ticket_qty', true );
						$ticket_price = (float) get_post_meta( $attendee->ID, 'ea_ticket_price', true );
						$line_total   = (float) get_post_meta( $attendee->ID, 'ea_ticket_order_amount', true );
						$order_total += $line_total;
						?>
						<tr>
							<td><?php echo esc_html( $ticket_name ); ?></td>
							<td><?php echo esc_html( $ticket_qty ); ?></td>
							<td><?php echo MPWEM_Global_Function::mep_format_price( $ticket_price ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
							<td><?php echo MPWEM_Global_Function::mep_format_price( $line_total ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="3"><?php esc_html_e( 'Total', 'mage-eventpress' ); ?></th>
						<th><?php echo MPWEM_Global_Function::mep_format_price( $order_total ); // phpcs:ignore WordPress.Security.EscapeOutput ?></th>
					</tr>
				</tfoot>
			</table>
		</div>

		<?php
		$first  = $attendees[0];
		$name   = get_post_meta( $first->ID, 'ea_name', true );
		$email  = get_post_meta( $first->ID, 'ea_email', true );
		$phone  = get_post_meta( $first->ID, 'ea_phone', true );
		$shown  = array( 'ea_name', 'ea_email', 'ea_phone' );
		?>
		<div class="mep-booking-confirmation-attendee">
			<h4><?php esc_html_e( 'Attendee Details', 'mage-eventpress' ); ?></h4>
			<?php if ( $name ) : ?>
				<div class="mep-booking-detail-row">
					<span class="mep-booking-detail-label"><?php esc_html_e( 'Name', 'mage-eventpress' ); ?></span>
					<span class="mep-booking-detail-value"><?php echo esc_html( $name ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<div class="mep-booking-detail-row">
					<span class="mep-booking-detail-label"><?php esc_html_e( 'Email', 'mage-eventpress' ); ?></span>
					<span class="mep-booking-detail-value"><?php echo esc_html( $email ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
				<div class="mep-booking-detail-row">
					<span class="mep-booking-detail-label"><?php esc_html_e( 'Phone', 'mage-eventpress' ); ?></span>
					<span class="mep-booking-detail-value"><?php echo esc_html( $phone ); ?></span>
				</div>
			<?php endif; ?>
			<?php
			foreach ( $form_array as $field ) {
				$d_name = is_array( $field ) && array_key_exists( 'd_name', $field ) ? $field['d_name'] : '';
				$type   = is_array( $field ) && array_key_exists( 'type', $field ) ? $field['type'] : '';
				$label  = is_array( $field ) && array_key_exists( 'label', $field ) ? $field['label'] : '';

				if ( ! $d_name || in_array( $d_name, $shown, true ) || $type === 'file' || $type === 'title' ) {
					continue;
				}

				$value = get_post_meta( $first->ID, $d_name, true );
				if ( $value === '' || $value === null ) {
					continue;
				}
				?>
				<div class="mep-booking-detail-row">
					<span class="mep-booking-detail-label"><?php echo esc_html( $label ); ?></span>
					<span class="mep-booking-detail-value"><?php echo esc_html( $value ); ?></span>
				</div>
				<?php
			}
			?>
		</div>
	<?php endif; ?>
</div>

<style>
.mep-booking-confirmation {
	border-radius: 8px;
	padding: 18px 22px;
	margin-bottom: 20px;
	border: 1px solid #e5e7eb;
}
.mep-booking-confirmation-header h3 {
	margin: 0 0 6px;
}
.mep-booking-confirmation-header p {
	margin: 0 0 4px;
}
.mep-booking-confirmation.mep-booking-success {
	background: #d1fae5;
	border-color: #6ee7b7;
	color: #065f46;
}
.mep-booking-confirmation.mep-booking-pending {
	background: #fef3c7;
	border-color: #fcd34d;
	color: #92400e;
}
.mep-booking-confirmation.mep-booking-cancelled {
	background: #fee2e2;
	border-color: #fca5a5;
	color: #991b1b;
}
.mep-booking-confirmation-summary,
.mep-booking-confirmation-attendee {
	margin-top: 14px;
}
.mep-booking-confirmation-summary h4,
.mep-booking-confirmation-attendee h4 {
	margin: 0 0 8px;
	font-size: 13px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.06em;
}
.mep-booking-summary-table {
	width: 100%;
	border-collapse: collapse;
	background: #fff;
	color: #111827;
}
.mep-booking-summary-table th,
.mep-booking-summary-table td {
	padding: 8px 10px;
	border: 1px solid #e5e7eb;
	text-align: left;
	font-size: 13px;
}
.mep-booking-detail-row {
	display: flex;
	justify-content: space-between;
	gap: 10px;
	padding: 4px 0;
	font-size: 13px;
}
.mep-booking-detail-label {
	font-weight: 600;
}
</style>
