<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*
	* Organizer output for the event list layouts (default, minimal, native,
	* spring, timeline, winter). Fired through do_action( 'mpwem_list_organizer', $event_infos ).
	*
	* Override in a child theme: wp-content/themes/<child>/mage-event/layout/list_organizer.php
	*
	* Note: most list layouts render this hook inside the card <a> wrapper, so the
	* default markup stays plain text on purpose - nested anchors are invalid HTML.
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_infos            = isset( $event_infos ) && is_array( $event_infos ) ? $event_infos : [];
	$event_list_setting_sec = array_key_exists( 'event_list_setting_sec', $event_infos ) ? $event_infos['event_list_setting_sec'] : [];
	$event_list_setting_sec = is_array( $event_list_setting_sec ) ? $event_list_setting_sec : [];
	$hide_org_list          = array_key_exists( 'mep_event_hide_organizer_list', $event_list_setting_sec ) ? $event_list_setting_sec['mep_event_hide_organizer_list'] : 'no';
	if ( $hide_org_list == 'no' ) {
		$event_id        = array_key_exists( 'event_id', $event_infos ) ? (int) $event_infos['event_id'] : 0;
		$organizer_names = [];
		if ( $event_id > 0 ) {
			$organizer_names = MPWEM_Global_Function::all_taxonomy_data( $event_id, 'mep_org' );
		}
		if ( empty( $organizer_names ) ) {
			// Fallbacks for callers that pass names without an event id.
			$organizer_tax = array_key_exists( 'organizer_tax', $event_infos ) ? $event_infos['organizer_tax'] : '';
			if ( $organizer_tax ) {
				$organizer_names = array_filter( array_map( 'trim', explode( ',', (string) $organizer_tax ) ) );
			} else {
				$organizer_name  = array_key_exists( 'organizer_name', $event_infos ) ? $event_infos['organizer_name'] : '';
				$organizer_names = $organizer_name ? [ $organizer_name ] : [];
			}
		}
		$organizer_names = apply_filters( 'mpwem_list_organizer_names', $organizer_names, $event_id, $event_infos );
		if ( is_array( $organizer_names ) && sizeof( $organizer_names ) > 0 ) {
			$organizer_title      = array_key_exists( 'organizer_title', $event_infos ) ? $event_infos['organizer_title'] : '';
			$icon_setting_sec     = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
			$icon_setting_sec     = is_array( $icon_setting_sec ) ? $icon_setting_sec : [];
			$event_organizer_icon = array_key_exists( 'mep_event_organizer_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_organizer_icon'] : 'mi mi-user';
			$separator            = apply_filters( 'mpwem_list_organizer_separator', ', ', $event_id, $event_infos );
			$organizer_text       = implode( $separator, $organizer_names );
			ob_start();
			?>
            <div class="list_content upcomming_organizer">
                <span class="<?php echo esc_attr( $event_organizer_icon ); ?>"></span><?php echo esc_html( trim( $organizer_title . ' ' . $organizer_text ) ); ?>
            </div>
			<?php
			$content = ob_get_clean();
			echo apply_filters( 'mpwem_list_organizer_content', $content, $event_id, $organizer_names, $event_infos );
		}
	}
