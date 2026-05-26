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
		$all_dates = MPWEM_Functions::get_dates( $event_id );
		$all_times = MPWEM_Functions::get_times( $event_id, $all_dates );
		$date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
		?>
		<div class="mpwem_booking_panel mep-rsvp-container" style="padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-top: 30px; border: 1px solid #eaeaea;">
			<h3 style="margin-top: 0; margin-bottom: 20px; font-weight: 700; color: #1a1a1a; font-size: 20px;"><?php esc_html_e( 'Free RSVP Registration', 'mage-eventpress' ); ?></h3>
			<form id="mep-rsvp-form" method="post">
				<input type="hidden" name="action" value="mep_submit_rsvp" />
				<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
				<?php wp_nonce_field( 'mep_rsvp_nonce', 'nonce' ); ?>

				<div class="mep-rsvp-fields" style="display: grid; grid-gap: 16px; margin-bottom: 20px;">
					<div class="mep-rsvp-field">
						<label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #4b5563;"><?php esc_html_e( 'Full Name', 'mage-eventpress' ); ?> <span style="color: #ef4444;">*</span></label>
						<input type="text" name="rsvp_name" required style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; outline: none; transition: border-color 0.2s;" placeholder="<?php esc_attr_e( 'Your name', 'mage-eventpress' ); ?>" />
					</div>
					<div class="mep-rsvp-field">
						<label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #4b5563;"><?php esc_html_e( 'Email Address', 'mage-eventpress' ); ?> <span style="color: #ef4444;">*</span></label>
						<input type="email" name="rsvp_email" required style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; outline: none; transition: border-color 0.2s;" placeholder="<?php esc_attr_e( 'Your email', 'mage-eventpress' ); ?>" />
					</div>
					<div class="mep-rsvp-field">
						<label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #4b5563;"><?php esc_html_e( 'Phone Number', 'mage-eventpress' ); ?></label>
						<input type="text" name="rsvp_phone" style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; outline: none; transition: border-color 0.2s;" placeholder="<?php esc_attr_e( 'Your phone', 'mage-eventpress' ); ?>" />
					</div>

					<?php if ( is_array( $all_dates ) && count( $all_dates ) > 0 ) : ?>
						<div class="mep-rsvp-field">
							<label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #4b5563;"><?php esc_html_e( 'Select Date', 'mage-eventpress' ); ?></label>
							<select name="rsvp_date" style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; outline: none; background: #fff;">
								<?php foreach ( $all_dates as $d ) : ?>
									<option value="<?php echo esc_attr( $d ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $d ) ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php else : ?>
						<input type="hidden" name="rsvp_date" value="<?php echo esc_attr( $date ); ?>" />
					<?php endif; ?>

					<div class="mep-rsvp-field">
						<label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #4b5563;"><?php esc_html_e( 'Number of Seats', 'mage-eventpress' ); ?></label>
						<input type="number" name="rsvp_qty" min="1" max="10" value="1" style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; outline: none;" />
					</div>
				</div>

				<div class="mep-rsvp-message" style="display: none; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 500;"></div>

				<button type="submit" class="mep-rsvp-submit-btn" style="background: #007cba; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; width: 100%;">
					<span><?php esc_html_e( 'Submit RSVP', 'mage-eventpress' ); ?></span>
				</button>
			</form>

			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('#mep-rsvp-form').on('submit', function(e) {
						e.preventDefault();
						const $form = $(this);
						const $btn = $form.find('.mep-rsvp-submit-btn');
						const $msg = $form.find('.mep-rsvp-message');

						$btn.prop('disabled', true).css('opacity', '0.6').find('span').text('<?php esc_html_e( "Submitting...", "mage-eventpress" ); ?>');
						$msg.hide().removeClass('success error').css('background', 'none');

						$.ajax({
							url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
							type: 'POST',
							data: $form.serialize(),
							success: function(response) {
								if (response.success) {
									$msg.text(response.data.message).addClass('success').css({
										'display': 'block',
										'background': '#ecfdf5',
										'color': '#065f46',
										'border': '1px solid #a7f3d0'
									});
									$form.find('input[type="text"], input[type="email"]').val('');
									$form.find('input[type="number"]').val(1);
								} else {
									const errorMsg = response.data && response.data.message ? response.data.message : '<?php esc_html_e( "An error occurred. Please try again.", "mage-eventpress" ); ?>';
									$msg.text(errorMsg).addClass('error').css({
										'display': 'block',
										'background': '#fef2f2',
										'color': '#991b1b',
										'border': '1px solid #fca5a5'
									});
								}
							},
							error: function() {
								$msg.text('<?php esc_html_e( "Connection error. Please try again.", "mage-eventpress" ); ?>').addClass('error').css({
									'display': 'block',
									'background': '#fef2f2',
									'color': '#991b1b',
									'border': '1px solid #fca5a5'
								});
							},
							complete: function() {
								$btn.prop('disabled', false).css('opacity', '1').find('span').text('<?php esc_html_e( "Submit RSVP", "mage-eventpress" ); ?>');
							}
						});
					});
				});
			</script>
		</div>
		<?php
		return;
	}

	// Ticket/WooCommerce mode (default layout)
	$all_dates = MPWEM_Functions::get_dates( $event_id );
	$all_times = MPWEM_Functions::get_times( $event_id, $all_dates );
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
	$upcoming_date            = is_array($event_infos) && array_key_exists( 'event_upcoming_datetime', $event_infos ) && $event_recurring == 'no' ? $event_infos['event_start_datetime'] : $event_infos['event_upcoming_datetime'];
    $date                    = $url_date ?: $upcoming_date;
	ob_start();
	if ( $event_id > 0 ) {
		$reg_status = MPWEM_Global_Function::get_post_info( $event_id, 'mep_reg_status', 'on' );
		if ( $reg_status == 'on' ) {
			$full_location = MPWEM_Functions::get_location( $event_id );
			$total_sold      = mep_ticket_type_sold( $event_id, '', $date );
			$total_ticket    = MPWEM_Functions::get_total_ticket( $event_id, $date );
			$total_reserve   = MPWEM_Functions::get_reserve_ticket( $event_id, $date );
			$total_available = $total_ticket - ( $total_sold + $total_reserve );
			$total_available = max( $total_available, 0 );
			?>
            <div class="mpwem_booking_panel">
                <input type="hidden" name='mpwem_post_id' value='<?php echo esc_attr( $event_id ); ?>'/>
                <input type="hidden" name='mep_event_start_date[]' value='<?php echo esc_attr( $date ); ?>'/>
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