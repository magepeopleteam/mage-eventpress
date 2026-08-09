<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$hide_calendar_details = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_hide_calendar_details', 'no' );
	if ( $hide_calendar_details == 'no' ) {
		$event_id  = $event_id ?? 0;
		$all_dates = $all_dates ?? [];
		$all_dates = ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) ? $all_dates : MPWEM_Functions::get_dates( $event_id );
		if ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) {
			$upcoming_date = $upcoming_date ?? '';
			$date_type     = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
			$end_time      = '';
			if ( $date_type == 'no' || $date_type == 'yes' ) {
				$dates    = current( $all_dates );
				$end_time = is_array( $all_dates ) && array_key_exists( 'end', $dates ) ? $dates['end'] : '';
			} else {
				$end_time = $upcoming_date;
			}
			$event_date_icon = MPWEM_Global_Function::get_settings( 'icon_setting_sec', 'mep_event_date_icon', 'far fa-calendar-plus' );
			do_action( 'mep_before_add_calendar_button' );

			$event_title = get_the_title( $event_id );
			$date        = MPWEM_Global_Function::calender_date_format( $upcoming_date );
			$end_time    = $end_time ? MPWEM_Global_Function::calender_date_format( $end_time ) : '';

			$content = get_post_field( 'post_content', $event_id );
			$content = wp_strip_all_tags( (string) $content );
			$content = preg_replace( '/\s+/u', ' ', $content );
			$content = trim( (string) $content );
			if ( function_exists( 'mb_substr' ) ) {
				$content = mb_substr( $content, 0, 500 );
			} else {
				$content = substr( $content, 0, 500 );
			}

			$location = MPWEM_Functions::get_location( $event_id );
			$location = is_array( $location ) ? implode( ', ', array_filter( array_map( 'trim', $location ) ) ) : '';

			$title_q    = rawurlencode( $event_title );
			$details_q  = rawurlencode( $content );
			$location_q = rawurlencode( $location );
			$date_q     = rawurlencode( $date );
			$end_q      = rawurlencode( $end_time );

			$google_url  = 'https://calendar.google.com/calendar/r/eventedit?text=' . $title_q . '&dates=' . $date_q . '/' . $end_q . '&details=' . $details_q . '&location=' . $location_q . '&sf=true';
			$yahoo_url   = 'https://calendar.yahoo.com/?v=60&view=d&type=20&title=' . $title_q . '&st=' . $date_q . '&et=' . $end_q . '&desc=' . $details_q . '&in_loc=' . $location_q . '&uid=';
			$outlook_url = 'https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent&startdt=' . $date_q . '&enddt=' . $end_q . '&subject=' . $title_q . '&body=' . $details_q;
			$apple_url   = 'https://webapps.genprod.com/wa/cal/download-ics.php?date_end=' . $end_q . '&date_start=' . $date_q . '&summary=' . $title_q . '&location=' . $location_q . '&description=' . $details_q;

			$head_month = '';
			$head_day   = '';
			$head_date  = '';
			if ( $upcoming_date ) {
				if ( class_exists( 'MPWEM_Global_Function' ) ) {
					$head_month = MPWEM_Global_Function::date_format( $upcoming_date, 'month' );
					$head_day   = MPWEM_Global_Function::date_format( $upcoming_date, 'day' );
					$head_date  = MPWEM_Global_Function::date_format( $upcoming_date );
				} else {
					$head_month = date_i18n( 'M', strtotime( $upcoming_date ) );
					$head_day   = date_i18n( 'd', strtotime( $upcoming_date ) );
					$head_date  = date_i18n( get_option( 'date_format' ), strtotime( $upcoming_date ) );
				}
			}
			?>
            <div class="mpwem_calender_area">
                <div class="mpwem_calender_area__head">
					<?php if ( $head_month || $head_day ) : ?>
                        <div class="mpwem_calender_area__date" aria-hidden="true">
                            <span class="mpwem_calender_area__month"><?php echo esc_html( $head_month ); ?></span>
                            <span class="mpwem_calender_area__day"><?php echo esc_html( $head_day ); ?></span>
                        </div>
					<?php else : ?>
                        <span class="mpwem_calender_area__icon" aria-hidden="true">
                            <i class="<?php echo esc_attr( $event_date_icon ); ?>"></i>
                        </span>
					<?php endif; ?>
                    <div class="mpwem_calender_area__copy">
                        <h5 class="mpwem_calender_area__title"><?php esc_html_e( 'Add to Calendar', 'mage-eventpress' ); ?></h5>
                        <p class="mpwem_calender_area__meta">
							<?php
							if ( $head_date ) {
								echo esc_html(
									sprintf(
										/* translators: 1: event title, 2: event date */
										__( '%1$s · %2$s', 'mage-eventpress' ),
										$event_title,
										$head_date
									)
								);
							} else {
								esc_html_e( 'Save this event to Google, Outlook, Apple, or Yahoo.', 'mage-eventpress' );
							}
							?>
                        </p>
                    </div>
                </div>
                <button type="button" class="mpwem_calender_toggle" data-collapse-target="#mpwem_calender_area" data-open-text="<?php esc_attr_e( 'Hide Calendar', 'mage-eventpress' ); ?>" data-close-text="<?php esc_attr_e( 'Choose Calendar', 'mage-eventpress' ); ?>">
                    <span class="mpwem_calender_toggle__icon" aria-hidden="true"><i class="far fa-calendar-plus"></i></span>
                    <span data-text><?php esc_html_e( 'Choose Calendar', 'mage-eventpress' ); ?></span>
                    <span class="mpwem_calender_toggle__chevron" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
                </button>
                <div class="mpwem_calender_providers" data-collapse="#mpwem_calender_area">
                    <div class="mpwem_calender_providers__list">
                        <a class="mpwem_calender_provider mpwem_calender_provider--google" href="<?php echo esc_url( $google_url ); ?>" rel="noopener noreferrer nofollow" target="_blank">
                            <span class="mpwem_calender_provider__icon" aria-hidden="true"><i class="fab fa-google"></i></span>
                            <span class="mpwem_calender_provider__label"><?php esc_html_e( 'Google', 'mage-eventpress' ); ?></span>
                        </a>
                        <a class="mpwem_calender_provider mpwem_calender_provider--yahoo" href="<?php echo esc_url( $yahoo_url ); ?>" rel="noopener noreferrer nofollow" target="_blank">
                            <span class="mpwem_calender_provider__icon" aria-hidden="true"><i class="fab fa-yahoo"></i></span>
                            <span class="mpwem_calender_provider__label"><?php esc_html_e( 'Yahoo', 'mage-eventpress' ); ?></span>
                        </a>
                        <a class="mpwem_calender_provider mpwem_calender_provider--outlook" href="<?php echo esc_url( $outlook_url ); ?>" rel="noopener noreferrer nofollow" target="_blank">
                            <span class="mpwem_calender_provider__icon" aria-hidden="true"><i class="far fa-envelope"></i></span>
                            <span class="mpwem_calender_provider__label"><?php esc_html_e( 'Outlook', 'mage-eventpress' ); ?></span>
                        </a>
                        <a class="mpwem_calender_provider mpwem_calender_provider--apple" href="<?php echo esc_url( $apple_url ); ?>" rel="noopener noreferrer nofollow" target="_blank">
                            <span class="mpwem_calender_provider__icon" aria-hidden="true"><i class="fab fa-apple"></i></span>
                            <span class="mpwem_calender_provider__label"><?php esc_html_e( 'Apple', 'mage-eventpress' ); ?></span>
                        </a>
                    </div>
                </div>
            </div>
			<?php
			do_action( 'mep_after_add_calendar_button' );
		}
	}
