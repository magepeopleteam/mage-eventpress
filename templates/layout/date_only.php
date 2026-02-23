<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	if ( $event_id > 0 ) {
		$event_infos                 = $event_infos ?? [];
		$event_infos              = (is_array($event_infos) && sizeof($event_infos)>0) ?$event_infos: MPWEM_Functions::get_all_info( $event_id );
		$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
		$single_event_setting_sec = is_array($_single_event_setting_sec) && !empty($_single_event_setting_sec) ? $_single_event_setting_sec : [];
		$hide_date_details        = array_key_exists( 'mep_event_hide_date_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_date_from_details'] : 'no';
		$upcoming_date            = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
		if ( $hide_date_details == 'no' && $upcoming_date ) {
			$icon_setting_sec    = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
			$icon_setting_sec = empty($icon_setting_sec) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
			$mep_event_date_icon = array_key_exists( 'mep_event_date_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_date_icon'] : 'far fa-calendar-alt';
			?>
            <div class="short_item">
                <h4 class="__icon_circle_mr"><span class="<?php echo esc_attr( $mep_event_date_icon ); ?>"></span></h4>
                <div class="_fdColumn">
                    <h6><?php esc_html_e( 'Event Date:', 'mage-eventpress' ); ?></h6>
                    <p><?php echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date ) ); ?></p>
                </div>
            </div>
			<?php
		}
	}