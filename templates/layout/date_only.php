<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	if ( $event_id > 0 ) {
		$event_infos               = $event_infos ?? [];
		$event_recurring           = is_array( $event_infos ) && array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
		$event_infos               = ( is_array( $event_infos ) && sizeof( $event_infos ) > 0 ) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
		$_single_event_setting_sec = is_array( $event_infos ) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
		$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
		$hide_date_details         = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_event_hide_date_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_date_from_details'] : 'no';
		$upcoming_date             = is_array( $event_infos ) && array_key_exists( 'upcoming_date', $event_infos ) && $event_recurring == 'no' && array_key_exists( 'event_start_date', $event_infos ) ? $event_infos['event_start_date'] : ( is_array( $event_infos ) && array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '' );

		if ( $hide_date_details == 'no' && $upcoming_date ) {
			$date_day   = function_exists( 'get_mep_datetime' ) ? get_mep_datetime( $upcoming_date, 'day' ) : date_i18n( 'j', strtotime( $upcoming_date ) );
			$date_month = function_exists( 'get_mep_datetime' ) ? get_mep_datetime( $upcoming_date, 'month-name' ) : date_i18n( 'M', strtotime( $upcoming_date ) );
			$date_full  = MPWEM_Global_Function::date_format( $upcoming_date );
			?>
            <div class="short_item short_item--date">
                <div class="short_item__datechip" aria-hidden="true">
                    <span class="short_item__day"><?php echo esc_html( $date_day ); ?></span>
                    <span class="short_item__month"><?php echo esc_html( $date_month ); ?></span>
                </div>
                <div class="short_item__body">
                    <span class="short_item__label"><?php esc_html_e( 'Date', 'mage-eventpress' ); ?></span>
                    <p class="short_item__value mep_date_status"><?php echo esc_html( $date_full ); ?></p>
                </div>
            </div>
			<?php
		}
	}
