<?php
/*
 * @Author      engr.sumonazma@gmail.com
 * Copyright:   mage-people.com
 *
 * Registers the [mep_booking_confirmation] shortcode.
 * Place this shortcode on a dedicated WordPress page, then select that page
 * in MEP Settings → Payment → "Booking Confirmation Page".
 * After a successful native checkout the user is redirected there instead of
 * back to the event page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MEP_Pro_Booking_Confirmation' ) ) {
	class MEP_Pro_Booking_Confirmation {

		public static function init() {
			add_shortcode( 'mep_booking_confirmation', array( __CLASS__, 'render' ) );
		}

		public static function render( $atts ) {
			$order_id = isset( $_GET['mep_booking_id'] ) ? absint( $_GET['mep_booking_id'] ) : 0;
			$status   = isset( $_GET['mep_booking'] )    ? sanitize_key( $_GET['mep_booking'] ) : '';

			ob_start();

			if ( ! $order_id || get_post_type( $order_id ) !== 'mep_custom_order' ) {
				echo '<p class="mep-bc-notice mep-bc-error">' . esc_html__( 'No booking found.', 'mage-eventpress' ) . '</p>';
				return ob_get_clean();
			}

			$event_id      = (int) get_post_meta( $order_id, '_mep_event_id',       true );
			$total         = (float) get_post_meta( $order_id, '_mep_order_total',  true );
			$customer_name = get_post_meta( $order_id, '_mep_customer_name',        true );
			$customer_email= get_post_meta( $order_id, '_mep_customer_email',       true );
			$customer_phone= get_post_meta( $order_id, '_mep_customer_phone',       true );
			$event_date    = get_post_meta( $order_id, '_mep_event_date',           true );
			$items         = (array) get_post_meta( $order_id, '_mep_order_items',  true );
			$gateway       = get_post_meta( $order_id, '_mep_payment_gateway',      true );

			$event_title   = $event_id ? get_the_title( $event_id ) : '';
			$event_url     = $event_id ? get_permalink( $event_id ) : '';

			$is_success    = ( $status === 'success' );
			$is_pending    = ( $status === 'pending' );

			$currency_settings = get_option( 'mep_currency_settings', array() );
			$currency_symbol   = ! empty( $currency_settings['mep_currency_symbol'] ) ? $currency_settings['mep_currency_symbol'] : '$';
			$currency_pos      = ! empty( $currency_settings['mep_currency_position'] ) ? $currency_settings['mep_currency_position'] : 'left';

			$fmt_price = function( $amount ) use ( $currency_symbol, $currency_pos ) {
				$formatted = number_format( (float) $amount, 2 );
				switch ( $currency_pos ) {
					case 'right':       return $formatted . $currency_symbol;
					case 'left_space':  return $currency_symbol . ' ' . $formatted;
					case 'right_space': return $formatted . ' ' . $currency_symbol;
					default:            return $currency_symbol . $formatted;
				}
			};

			?>
			<div class="mep-bc-wrap">

				<!-- Status banner -->
				<?php if ( $is_success ) : ?>
				<div class="mep-bc-banner mep-bc-banner--success">
					<span class="mep-bc-banner-icon">&#10003;</span>
					<div>
						<h2 class="mep-bc-banner-title"><?php esc_html_e( 'Registration Confirmed!', 'mage-eventpress' ); ?></h2>
						<p class="mep-bc-banner-sub"><?php esc_html_e( 'Your registration is complete. A confirmation email has been sent to you.', 'mage-eventpress' ); ?></p>
					</div>
				</div>
				<?php elseif ( $is_pending ) : ?>
				<div class="mep-bc-banner mep-bc-banner--pending">
					<span class="mep-bc-banner-icon">&#9719;</span>
					<div>
						<h2 class="mep-bc-banner-title"><?php esc_html_e( 'Registration Received', 'mage-eventpress' ); ?></h2>
						<p class="mep-bc-banner-sub"><?php esc_html_e( 'Your registration is pending payment. Please complete payment to secure your spot.', 'mage-eventpress' ); ?></p>
					</div>
				</div>
				<?php endif; ?>

				<div class="mep-bc-body">

					<!-- Order reference -->
					<div class="mep-bc-section mep-bc-order-ref">
						<span class="mep-bc-label"><?php esc_html_e( 'Booking ID:', 'mage-eventpress' ); ?></span>
						<span class="mep-bc-value mep-bc-booking-id">#<?php echo esc_html( $order_id ); ?></span>
					</div>

					<!-- Event info -->
					<?php if ( $event_title ) : ?>
					<div class="mep-bc-section">
						<h3 class="mep-bc-section-title"><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></h3>
						<div class="mep-bc-event-name">
							<?php if ( $event_url ) : ?>
								<a href="<?php echo esc_url( $event_url ); ?>"><?php echo esc_html( $event_title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $event_title ); ?>
							<?php endif; ?>
						</div>
						<?php if ( $event_date ) : ?>
						<div class="mep-bc-event-date">
							<span class="mep-bc-meta-label"><?php esc_html_e( 'Date:', 'mage-eventpress' ); ?></span>
							<?php echo esc_html( $event_date ); ?>
						</div>
						<?php endif; ?>
					</div>
					<?php endif; ?>

					<!-- Ticket summary -->
					<?php if ( ! empty( $items ) ) : ?>
					<div class="mep-bc-section">
						<h3 class="mep-bc-section-title"><?php esc_html_e( 'Tickets', 'mage-eventpress' ); ?></h3>
						<table class="mep-bc-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Ticket', 'mage-eventpress' ); ?></th>
									<th class="mep-bc-cell-center"><?php esc_html_e( 'Qty', 'mage-eventpress' ); ?></th>
									<th class="mep-bc-cell-right"><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></th>
									<th class="mep-bc-cell-right"><?php esc_html_e( 'Subtotal', 'mage-eventpress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $items as $item ) :
									$item_name  = isset( $item['name'] )  ? $item['name']           : '';
									$item_qty   = isset( $item['qty'] )   ? absint( $item['qty'] )   : 0;
									$item_price = isset( $item['price'] ) ? (float) $item['price']   : 0.0;
									$item_total = isset( $item['total'] ) ? (float) $item['total']   : $item_price * $item_qty;
								?>
								<tr>
									<td><?php echo esc_html( $item_name ); ?></td>
									<td class="mep-bc-cell-center"><?php echo esc_html( $item_qty ); ?></td>
									<td class="mep-bc-cell-right"><?php echo esc_html( $fmt_price( $item_price ) ); ?></td>
									<td class="mep-bc-cell-right"><?php echo esc_html( $fmt_price( $item_total ) ); ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
							<tfoot>
								<tr class="mep-bc-total-row">
									<td colspan="3" class="mep-bc-cell-right"><?php esc_html_e( 'Total', 'mage-eventpress' ); ?></td>
									<td class="mep-bc-cell-right"><?php echo esc_html( $fmt_price( $total ) ); ?></td>
								</tr>
							</tfoot>
						</table>
					</div>
					<?php endif; ?>

					<!-- Customer info -->
					<div class="mep-bc-section">
						<h3 class="mep-bc-section-title"><?php esc_html_e( 'Your Details', 'mage-eventpress' ); ?></h3>
						<dl class="mep-bc-details">
							<?php if ( $customer_name ) : ?>
							<div class="mep-bc-detail-row">
								<dt><?php esc_html_e( 'Name', 'mage-eventpress' ); ?></dt>
								<dd><?php echo esc_html( $customer_name ); ?></dd>
							</div>
							<?php endif; ?>
							<?php if ( $customer_email ) : ?>
							<div class="mep-bc-detail-row">
								<dt><?php esc_html_e( 'Email', 'mage-eventpress' ); ?></dt>
								<dd><?php echo esc_html( $customer_email ); ?></dd>
							</div>
							<?php endif; ?>
							<?php if ( $customer_phone ) : ?>
							<div class="mep-bc-detail-row">
								<dt><?php esc_html_e( 'Phone', 'mage-eventpress' ); ?></dt>
								<dd><?php echo esc_html( $customer_phone ); ?></dd>
							</div>
							<?php endif; ?>
							<?php if ( $gateway ) : ?>
							<div class="mep-bc-detail-row">
								<dt><?php esc_html_e( 'Payment', 'mage-eventpress' ); ?></dt>
								<dd><?php echo esc_html( ucfirst( $gateway ) ); ?></dd>
							</div>
							<?php endif; ?>
						</dl>
					</div>

					<?php if ( class_exists( 'MPWEM_Social_Card' ) ) : ?>
						<div class="mep-bc-section mep-bc-social-card">
							<?php MPWEM_Social_Card::render_for_native( $event_id, $order_id, $status ); ?>
						</div>
					<?php endif; ?>

					<!-- Actions -->
					<div class="mep-bc-actions">
						<?php if ( $event_url ) : ?>
						<a href="<?php echo esc_url( $event_url ); ?>" class="mep-bc-btn mep-bc-btn--secondary">
							<?php esc_html_e( 'Back to Event', 'mage-eventpress' ); ?>
						</a>
						<?php endif; ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mep-bc-btn mep-bc-btn--secondary">
							<?php esc_html_e( 'Go to Home', 'mage-eventpress' ); ?>
						</a>
					</div>

				</div><!-- .mep-bc-body -->
			</div><!-- .mep-bc-wrap -->

			<style>
			.mep-bc-wrap {
				max-width: 680px;
				margin: 32px auto;
				font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
				color: #1f2937;
			}
			.mep-bc-banner {
				display: flex;
				align-items: flex-start;
				gap: 18px;
				padding: 22px 26px;
				border-radius: 12px;
				margin-bottom: 28px;
			}
			.mep-bc-banner--success {
				background: #d1fae5;
				border: 1px solid #6ee7b7;
			}
			.mep-bc-banner--pending {
				background: #fef3c7;
				border: 1px solid #fcd34d;
			}
			.mep-bc-banner-icon {
				font-size: 28px;
				line-height: 1;
				flex-shrink: 0;
				margin-top: 2px;
			}
			.mep-bc-banner--success .mep-bc-banner-icon { color: #059669; }
			.mep-bc-banner--pending .mep-bc-banner-icon { color: #d97706; }
			.mep-bc-banner-title {
				margin: 0 0 4px;
				font-size: 20px;
				font-weight: 700;
			}
			.mep-bc-banner--success .mep-bc-banner-title { color: #065f46; }
			.mep-bc-banner--pending .mep-bc-banner-title { color: #92400e; }
			.mep-bc-banner-sub {
				margin: 0;
				font-size: 14px;
			}
			.mep-bc-banner--success .mep-bc-banner-sub { color: #065f46; }
			.mep-bc-banner--pending .mep-bc-banner-sub { color: #92400e; }
			.mep-bc-body {
				background: #fff;
				border: 1px solid #e5e7eb;
				border-radius: 12px;
				overflow: hidden;
			}
			.mep-bc-order-ref {
				display: flex;
				align-items: center;
				gap: 10px;
				background: #f9fafb;
				border-bottom: 1px solid #e5e7eb;
			}
			.mep-bc-label {
				font-size: 13px;
				color: #6b7280;
				font-weight: 600;
			}
			.mep-bc-booking-id {
				font-size: 15px;
				font-weight: 700;
				color: #4f46e5;
			}
			.mep-bc-section {
				padding: 22px 26px;
				border-bottom: 1px solid #e5e7eb;
			}
			.mep-bc-section:last-child { border-bottom: 0; }
			.mep-bc-section-title {
				font-size: 12px;
				font-weight: 700;
				text-transform: uppercase;
				letter-spacing: 0.07em;
				color: #9ca3af;
				margin: 0 0 14px;
			}
			.mep-bc-event-name {
				font-size: 16px;
				font-weight: 600;
				margin-bottom: 6px;
			}
			.mep-bc-event-name a {
				color: #4f46e5;
				text-decoration: none;
			}
			.mep-bc-event-name a:hover { text-decoration: underline; }
			.mep-bc-event-date {
				font-size: 14px;
				color: #6b7280;
			}
			.mep-bc-meta-label {
				font-weight: 600;
				color: #374151;
				margin-right: 4px;
			}
			.mep-bc-table {
				width: 100%;
				border-collapse: collapse;
				font-size: 14px;
			}
			.mep-bc-table thead th {
				text-align: left;
				font-size: 12px;
				font-weight: 700;
				text-transform: uppercase;
				letter-spacing: 0.05em;
				color: #9ca3af;
				padding: 0 12px 10px;
				border-bottom: 1px solid #e5e7eb;
			}
			.mep-bc-table thead th:first-child { padding-left: 0; }
			.mep-bc-table thead th:last-child  { padding-right: 0; }
			.mep-bc-table tbody td {
				padding: 10px 12px;
				border-bottom: 1px solid #f3f4f6;
				color: #374151;
			}
			.mep-bc-table tbody td:first-child { padding-left: 0; }
			.mep-bc-table tbody td:last-child  { padding-right: 0; }
			.mep-bc-table tfoot td {
				padding: 12px 12px 0;
				font-weight: 700;
				color: #111827;
				font-size: 15px;
			}
			.mep-bc-table tfoot td:first-child { padding-left: 0; }
			.mep-bc-table tfoot td:last-child  { padding-right: 0; }
			.mep-bc-table thead th.mep-bc-cell-center,
			.mep-bc-table tbody td.mep-bc-cell-center { text-align: center; }
			.mep-bc-table thead th.mep-bc-cell-right,
			.mep-bc-table tbody td.mep-bc-cell-right,
			.mep-bc-table tfoot td.mep-bc-cell-right  { text-align: right; }
			.mep-bc-details {
				margin: 0;
				display: flex;
				flex-direction: column;
				gap: 10px;
			}
			.mep-bc-detail-row {
				display: flex;
				gap: 12px;
				font-size: 14px;
			}
			.mep-bc-detail-row dt {
				font-weight: 600;
				color: #374151;
				min-width: 80px;
			}
			.mep-bc-detail-row dd {
				margin: 0;
				color: #6b7280;
			}
			.mep-bc-actions {
				padding: 22px 26px;
				display: flex;
				gap: 12px;
				flex-wrap: wrap;
			}
			.mep-bc-btn {
				display: inline-block;
				padding: 10px 20px;
				border-radius: 8px;
				font-size: 14px;
				font-weight: 600;
				text-decoration: none;
				transition: background 0.18s, color 0.18s;
			}
			.mep-bc-btn--secondary {
				background: #f3f4f6;
				color: #374151;
				border: 1px solid #e5e7eb;
			}
			.mep-bc-btn--secondary:hover {
				background: #e5e7eb;
				color: #111827;
			}
			.mep-bc-notice { padding: 16px; border-radius: 8px; font-size: 14px; }
			.mep-bc-error  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
			</style>
			<?php

			return ob_get_clean();
		}
	}
}
