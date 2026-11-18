<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$hide_calendar_details = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_hide_calendar_details', 'no' );
	if ( $hide_calendar_details == 'no' ) {
		$event_id  = $event_id ?? 0;
		$all_dates = $all_dates ?? [];
		$all_dates = (is_array( $all_dates ) && sizeof( $all_dates ) > 0) ? $all_dates : MPWEM_Functions::get_dates( $event_id );
		$upcoming_date = $upcoming_date ?? '';
		// Every session of a multi-date Single Event, otherwise the one occurrence; see MPWEM_Functions::get_calendar_occurrences().
		$occurrences      = ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) ? MPWEM_Functions::get_calendar_occurrences( $event_id, $all_dates, $upcoming_date ) : [];
		$is_multi_session = count( $occurrences ) > 1;
		$first_links      = ( ! empty( $occurrences ) && ! $is_multi_session ) ? mep_get_event_calendar_links( $event_id, $occurrences[0]['start'] ) : [];
		if ( $is_multi_session || ! empty( $first_links ) ) {
			$event_date_icon = MPWEM_Global_Function::get_settings( 'icon_setting_sec', 'mep_event_date_icon', 'far fa-calendar' );
			do_action( 'mep_before_add_calendar_button' );
			$event_template   = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_template', 'default-theme.php' );
			$is_default_theme = ( $event_template === 'default-theme.php' );
			?>
            <div class="mpwem_calender_area<?php echo $is_default_theme ? ' mpwem_calender_area--default' : ''; ?>">
                <button type="button" class="_button_theme_margin_auto_min_150" data-collapse-target="#mpwem_calender_area" data-open-text="<?php esc_attr_e( 'Hide Calender', 'mage-eventpress' ); ?>" data-close-text="<?php esc_attr_e( 'Add Calendar', 'mage-eventpress' ); ?>">
					<?php if ( $is_default_theme ) { ?>
                        <i class="<?php echo esc_attr( $event_date_icon ); ?>" aria-hidden="true"></i>
					<?php } ?>
                    <span data-text><?php esc_html_e( 'Add Calendar', 'mage-eventpress' ); ?></span>
                </button>
                <div data-collapse="#mpwem_calender_area">
					<?php if ( $is_multi_session ) { ?>
                        <div class="mpwem_calender_sessions">
                            <div class="mpwem_calender_sessions__label"><?php echo esc_html( sprintf( /* translators: %d: number of event dates */ _n( 'Add all %d date at once', 'Add all %d dates at once', count( $occurrences ), 'mage-eventpress' ), count( $occurrences ) ) ); ?></div>
                            <div class="_mt_xs_fdColumn mpwem_calender_all">
                                <a class="_button_general_mt_xs mpwem_cal_google mpwem_cal_google_all" href="<?php echo esc_url( mep_get_event_google_subscribe_url( $event_id, $occurrences[0]['start'] ) ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Google Calendar', 'mage-eventpress' ); ?></a>
                                <a class="_button_general_mt_xs mpwem_cal_apple" href="<?php echo esc_url( mep_get_event_ics_url( $event_id, $occurrences[0]['start'], false, true ) ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Apple / Outlook / iCal', 'mage-eventpress' ); ?></a>
                            </div>
                            <div class="mpwem_calender_sessions__label"><?php esc_html_e( 'Or add one date', 'mage-eventpress' ); ?></div>
							<?php
								// Google, Yahoo and Outlook.com add one event per link: one row per session.
								foreach ( $occurrences as $occurrence ) {
									$links = mep_get_event_calendar_links( $event_id, $occurrence['start'] );
									if ( empty( $links ) ) {
										continue;
									}
									$has_time = MPWEM_Global_Function::check_time_exit_date( $occurrence['start'] );
									$label    = MPWEM_Global_Function::date_format( $occurrence['start'], $has_time ? 'full' : 'date', $event_id );
									if ( $has_time && $occurrence['end'] && strtotime( $occurrence['end'] ) > strtotime( $occurrence['start'] ) ) {
										$same_day = gmdate( 'Y-m-d', strtotime( $occurrence['end'] ) ) === gmdate( 'Y-m-d', strtotime( $occurrence['start'] ) );
										$label   .= ' - ' . MPWEM_Global_Function::date_format( $occurrence['end'], $same_day ? 'time' : 'full', $event_id );
									}
									?>
                                    <div class="mpwem_calender_session">
                                        <div class="mpwem_calender_session__date"><?php echo esc_html( $label ); ?></div>
                                        <div class="_mt_xs_fdColumn">
                                            <a class="_button_general_mt_xs mpwem_cal_google" href="<?php echo esc_url( $links['google'] ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Google', 'mage-eventpress' ); ?></a>
                                            <a class="_button_general_mt_xs mpwem_cal_yahoo" href="<?php echo esc_url( $links['yahoo'] ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Yahoo', 'mage-eventpress' ); ?></a>
                                            <a class="_button_general_mt_xs mpwem_cal_outlook" href="<?php echo esc_url( $links['outlook'] ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Outlook', 'mage-eventpress' ); ?></a>
                                        </div>
                                    </div>
									<?php
								}
							?>
                        </div>
					<?php } else { ?>
                        <div class="_mt_xs_fdColumn">
                            <a class="_button_general_mt_xs mpwem_cal_google" href="<?php echo esc_url( $first_links['google'] ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Google', 'mage-eventpress' ); ?></a>
                            <a class="_button_general_mt_xs mpwem_cal_yahoo" href="<?php echo esc_url( $first_links['yahoo'] ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Yahoo', 'mage-eventpress' ); ?></a>
                            <a class="_button_general_mt_xs mpwem_cal_outlook" href="<?php echo esc_url( $first_links['outlook'] ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Outlook', 'mage-eventpress' ); ?></a>
                            <a class="_button_general_mt_xs mpwem_cal_apple" href="<?php echo esc_url( mep_get_event_ics_url( $event_id, $occurrences[0]['start'] ) ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Apple', 'mage-eventpress' ); ?></a>
                        </div>
					<?php } ?>
                </div>
            </div>
			<?php
			do_action( 'mep_after_add_calendar_button' );
		}
	}
