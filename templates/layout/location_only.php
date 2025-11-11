<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	if ( $event_id > 0 ) {
		$event_infos                 = $event_infos ?? [];
		$event_infos              =sizeof($event_infos)>0 ?$event_infos: MPWEM_Functions::get_all_info( $event_id );
		$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
		$single_event_setting_sec = is_array($_single_event_setting_sec) && !empty($_single_event_setting_sec) ? $_single_event_setting_sec : [];
		$hide_location_details    = array_key_exists( 'mep_event_hide_location_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_location_from_details'] : 'no';
		$full_address             = array_key_exists( 'full_address', $event_infos ) ? $event_infos['full_address'] : [];
		$location                 = array_key_exists( 'location', $full_address ) ? $full_address['location'] : '';
		if ( $hide_location_details == 'no' && $location ) {
			$icon_setting_sec  = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
			$icon_setting_sec = empty($icon_setting_sec) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
			$mep_location_icon = array_key_exists( 'mep_event_location_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_location_icon'] : 'fas fa-map-marker-alt';
			?>
            <div class="short_item">
                <h4 class="_circleIcon_mR"><span class="<?php echo esc_attr( $mep_location_icon ); ?>"></span></h4>
                <div class="_fdColumn">
                    <h6><?php esc_html_e( 'Event Location:', 'mage-eventpress' ); ?></h6>
                    <p><?php echo esc_html( $location ); ?></p>
                </div>
            </div>
			<?php
		}
	}