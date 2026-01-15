<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$hide_calendar_details = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_hide_calendar_details', 'no' );
	if ( $hide_calendar_details == 'no' ) {
		$event_id  = $event_id ?? 0;
		$all_dates = $all_dates ?? [];
		$all_dates = sizeof( $all_dates ) > 0 ? $all_dates : MPWEM_Functions::get_dates( $event_id );
		if ( sizeof( $all_dates ) > 0 ) {
			$upcoming_date = $upcoming_date ?? '';
			$date_type     = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
			$end_time      = '';
			if ( $date_type == 'no' || $date_type == 'yes' ) {
				$dates    = current( $all_dates );
				$end_time = is_array( $all_dates ) && array_key_exists( 'end', $dates ) ? $dates['end'] : '';
			} else {
				$end_time = $upcoming_date;
			}
			$event_date_icon = MPWEM_Global_Function::get_settings( 'icon_setting_sec', 'mep_event_date_icon', 'far fa-calendar' );
			do_action( 'mep_before_add_calendar_button' );
			$event_title = get_the_title( $event_id );
			$date        = MPWEM_Global_Function::calender_date_format( $upcoming_date );
			$end_time    = $end_time ? MPWEM_Global_Function::calender_date_format( $end_time ) : '';
			$content     = substr( get_the_content( $event_id ), 0, 1000 );
			$location    = MPWEM_Functions::get_location( $event_id );
			$location    = implode( '  ', $location );
			?>
            <div class="mpwem_calender_area">
                <button type="button" class="_button_theme_margin_auto_min_150" data-collapse-target="#mpwem_calender_area" data-open-text="<?php esc_attr_e( 'Hide Calender', 'mage-eventpress' ); ?>" data-close-text="<?php esc_attr_e( 'Add Calendar', 'mage-eventpress' ); ?>">
                    <span data-text><?php esc_html_e( 'Add Calendar', 'mage-eventpress' ); ?></span>
                </button>
                <div data-collapse="#mpwem_calender_area">
                    <div class="_mt_xs_fdColumn">
                        <a class="_button_general_mt_xs" href="https://calendar.google.com/calendar/r/eventedit?text=<?php echo esc_url( $event_title ); ?>&dates=<?php echo esc_attr( $date ); ?>/<?php echo esc_attr( $end_time ); ?>&details=<?php echo esc_attr( $content ); ?>&location=<?php echo esc_attr( $location ); ?>&sf=true" rel="noopener noreferrer" target='_blank' rel="nofollow"><?php esc_html_e( 'Google', 'mage-eventpress' ); ?></a>
                        <a class="_button_general_mt_xs" href="https://calendar.yahoo.com/?v=60&view=d&type=20&title=<?php echo esc_url( $event_title ); ?>&st=<?php echo esc_attr( $date ); ?>&et=<?php echo esc_attr( $end_time ); ?>&desc=<?php echo esc_attr( $content ); ?>&in_loc=<?php echo esc_attr( $location ); ?>&uid=" rel="noopener noreferrer" target='_blank' rel="nofollow"><?php esc_html_e( 'Yahoo', 'mage-eventpress' ); ?></a>
                        <a class="_button_general_mt_xs" href="https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent&startdt=<?php echo esc_attr( $date ); ?>&enddt=<?php echo esc_attr( $end_time ); ?>&subject=<?php echo esc_attr( $event_title ); ?>&body=<?php echo esc_url( $event_title ); ?>" rel="noopener noreferrer" target='_blank' rel="nofollow"><?php esc_html_e( 'Outlook', 'mage-eventpress' ); ?></a>
                        <a class="_button_general_mt_xs" href="https://webapps.genprod.com/wa/cal/download-ics.php?date_end=<?php echo esc_attr( $end_time ); ?>&date_start=<?php echo esc_attr( $date ); ?>&summary=<?php echo esc_url( $event_title ); ?>&location=<?php echo esc_attr( $location ); ?>&description=<?php echo esc_attr( $content ); ?>" rel="noopener noreferrer" target='_blank'><?php esc_html_e( 'Apple', 'mage-eventpress' ); ?></a>
                    </div>
                </div>
            </div>
			<?php
			do_action( 'mep_after_add_calendar_button' );
		}
	}