<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id  = $event_id ?? 0;
	if ( ! $event_id ) {
		return;
	}

	$reg_status = get_post_meta( $event_id, 'mep_reg_status', true );
	if ( empty( $reg_status ) ) {
		$reg_status = MPWEM_Global_Function::has_woocommerce() ? 'on' : 'off';
	}

	// Listing mode has no registration box at all
	if ( $reg_status === 'off' ) {
		return;
	}

	// RSVP mode renders a free RSVP form with direct attendee database storage
	if ( $reg_status === 'rsvp' ) {
		$url_date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : null;
		$url_date_2 = isset( $_GET['date_time'] ) ? sanitize_text_field( wp_unslash( $_GET['date_time'] ) ) : null;
		$url_date = $url_date ?: $url_date_2;
		$url_date = $url_date ? date( 'Y-m-d H:i', (int)$url_date ) : '';
		if ($url_date) {
			$date_format = MPWEM_Global_Function::check_time_exit_date( $url_date ) ? 'Y-m-d H:i' : 'Y-m-d';
			$url_date = date( $date_format, strtotime($url_date) );
		}

		$all_dates = MPWEM_Functions::get_dates( $event_id );
		$all_times = MPWEM_Functions::get_times( $event_id, $all_dates );
		$upcoming_date = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
		
		$date = $url_date ?: ($date ?: $upcoming_date);
		
		$event_infos = MPWEM_Functions::get_all_info( $event_id );
		$label_name  = !empty($event_infos['mep_rsvp_name_label']) ? $event_infos['mep_rsvp_name_label'] : __( 'Full Name', 'mage-eventpress' );
		$label_email = !empty($event_infos['mep_rsvp_email_label']) ? $event_infos['mep_rsvp_email_label'] : __( 'Email Address', 'mage-eventpress' );
		$label_phone = !empty($event_infos['mep_rsvp_phone_label']) ? $event_infos['mep_rsvp_phone_label'] : __( 'Phone Number', 'mage-eventpress' );
		$label_qty   = !empty($event_infos['mep_rsvp_qty_label']) ? $event_infos['mep_rsvp_qty_label'] : __( 'Number of Seats', 'mage-eventpress' );

		if ( ! wp_doing_ajax() ) {
			?>
			<div class="mpwem_booking_panel mep-rsvp-container">
			<?php
		}
		?>
			<input type="hidden" name="mpwem_post_id" value="<?php echo esc_attr( $event_id ); ?>" />
			<h3><?php esc_html_e( 'Free RSVP Registration', 'mage-eventpress' ); ?></h3>
			<div id="mep-rsvp-form">
				<input type="hidden" name="action" value="mep_submit_rsvp" />
				<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
				<input type="hidden" name="rsvp_date" value="<?php echo esc_attr( $date ); ?>" />
				<?php wp_nonce_field( 'mep_rsvp_nonce', 'nonce' ); ?>

				<div class="mep-rsvp-fields">
					<div class="mep-rsvp-field">
						<label><?php echo esc_html( $label_name ); ?> <span>*</span></label>
						<input type="text" name="rsvp_name" required placeholder="<?php echo esc_attr( $label_name ); ?>" />
					</div>
					<div class="mep-rsvp-field">
						<label><?php echo esc_html( $label_email ); ?> <span>*</span></label>
						<input type="email" name="rsvp_email" required placeholder="<?php echo esc_attr( $label_email ); ?>" />
					</div>
					<div class="mep-rsvp-field">
						<label><?php echo esc_html( $label_phone ); ?> <span>*</span></label>
						<input type="text" name="rsvp_phone" required placeholder="<?php echo esc_attr( $label_phone ); ?>" />
					</div>

					<div class="mep-rsvp-field">
						<label><?php echo esc_html( $label_qty ); ?></label>
						<input type="number" name="rsvp_qty" min="1" max="10" value="1" />
					</div>
				</div>

				<div class="mep-rsvp-message"></div>

				<button type="submit" class="mep-rsvp-submit-btn">
					<span><?php esc_html_e( 'Submit RSVP', 'mage-eventpress' ); ?></span>
				</button>
			</div>

		<?php
		if ( ! wp_doing_ajax() ) {
			?>
			</div>
			<?php
		}
		return;
	}

	// Ticket/WooCommerce mode (default layout)
	$all_dates = MPWEM_Functions::get_dates( $event_id );
	$all_times = MPWEM_Functions::get_times( $event_id, $all_dates );
	$user_date = $date;
	$event_type     = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
	// $date      = empty( $date ) || $event_type == 'no' ? get_post_meta( $event_id, 'event_start_datetime', true ) : MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
// echo $date;

	$date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	$event_infos              = MPWEM_Functions::get_all_info( $event_id );
	$event_recurring			= is_array($event_infos) && array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
    $url_date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : null;
    $url_date_2 = isset( $_GET['date_time'] ) ? sanitize_text_field( wp_unslash( $_GET['date_time'] ) ) : null;
    $url_date=$url_date?:$url_date_2;
    $url_date=$url_date ? date( 'Y-m-d H:i', $url_date ) : '';
    $date_format = MPWEM_Global_Function::check_time_exit_date( $url_date ) ? 'Y-m-d H:i' : 'Y-m-d';
    $url_date    = $url_date ? date( $date_format, strtotime($url_date) ) : '';
    $all_dates   = MPWEM_Functions::get_dates( $event_id );
    $all_times   = MPWEM_Functions::get_times( $event_id, $all_dates, $url_date );
	$upcoming_date            = is_array($event_infos) && array_key_exists( 'event_upcoming_datetime', $event_infos ) && $event_recurring == 'no' && array_key_exists('event_start_datetime', $event_infos) ? $event_infos['event_start_datetime'] : (is_array($event_infos) && array_key_exists('event_upcoming_datetime', $event_infos) ? $event_infos['event_upcoming_datetime'] : '');
    $date                    = $url_date ?: $upcoming_date;

	// Block booking for a past/expired selected occurrence (e.g. opened from a calendar
	// link pointing at a date that has already passed). Mirrors the calendar's expiry rule
	// so the detail page and the calendar agree on what is expired.
	$selected_date_expired = false;
	if ( ! empty( $user_date ) ) {
		$expire_on = function_exists( 'mep_get_option' )
			? mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' )
			: 'event_start_datetime';
		$reference_dt = $user_date;
		if ( $expire_on === 'event_end_datetime' ) {
			$end_ref = $event_type === 'no' ? get_post_meta( $event_id, 'event_end_datetime', true ) : '';
			if ( empty( $end_ref ) ) {
				$end_time = get_post_meta( $event_id, 'event_end_time', true );
				$end_ref  = $end_time ? date( 'Y-m-d', strtotime( $user_date ) ) . ' ' . $end_time : $user_date;
			}
			$reference_dt = $end_ref ?: $user_date;
		}
		$reference_ts = strtotime( $reference_dt );
		$now_ts       = strtotime( current_time( 'Y-m-d H:i:s' ) );
		if ( $reference_ts && $now_ts && $reference_ts < $now_ts ) {
			$selected_date_expired = true;
		}
	}

	ob_start();
	if ( $event_id > 0 ) {
		$reg_status = MPWEM_Global_Function::get_post_info( $event_id, 'mep_reg_status', 'on' );
		if ( $reg_status == 'on' && $selected_date_expired ) {
			MPWEM_Layout::msg( esc_html__( 'Sorry, this date has expired and is no longer available for booking.', 'mage-eventpress' ), 'mpwem_date_expired_msg' );
		} elseif ( $reg_status == 'on' ) {
			$full_location = MPWEM_Functions::get_location( $event_id );
			$total_sold      = mep_ticket_type_sold( $event_id, '', $date );
			$total_ticket    = MPWEM_Functions::get_total_ticket( $event_id, $date );
			$total_reserve   = MPWEM_Functions::get_reserve_ticket( $event_id, $date );
			$total_available = $total_ticket - ( $total_sold + $total_reserve );
			$total_available = max( $total_available, 0 );
			?>
            <div class="mpwem_booking_panel">
                <input type="hidden" name='mpwem_post_id' value='<?php echo esc_attr( $event_id ); ?>'/>
                <input type="hidden" name='mep_event_start_date[]' value='<?php echo esc_attr( $user_date ); ?>'/>
                <input type="hidden" name='mep_event_location_cart' value='<?php echo esc_attr( implode( ', ', $full_location ) ); ?>'/>
                <input type="hidden" name='mep_same_attendee' value='<?php echo esc_attr( MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_enable_same_attendee', 'no' ) ); ?>'/>
				<?php require apply_filters( 'mpwem_ticket_file', MPWEM_Functions::template_path( 'layout/ticket_type.php' ), $event_id ); ?>
				<?php do_action( 'mpwem_single_attendee', $event_id ); ?>
				<?php require MPWEM_Functions::template_path( 'layout/extra_service.php' ); ?>
                <div class="mpwem_form_submit_area">
					<?php do_action( 'mep_add_term_condition', $event_id ); ?>
					<?php
						if ( $total_available > 0 ) {
							require MPWEM_Functions::template_path( 'layout/add_to_cart.php' );
						}
					?>
                </div>
            </div>
			<?php
		} else {
			MPWEM_Layout::msg( esc_html__( 'Sorry, this event is  no longer available', 'mage-eventpress' ) );
		}
	}
	echo ob_get_clean();