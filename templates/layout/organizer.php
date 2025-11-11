<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? [];
	$event_infos               = sizeof( $event_infos ) > 0 ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$only                      = $only ?? '';
	$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_organizer            = array_key_exists( 'mep_event_hide_org_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_org_from_details'] : 'no';
	if ( $event_id > 0 && $hide_organizer == 'no' ) {
		$names = MPWEM_Global_Function::all_taxonomy_data( $event_id, 'mep_org' );
		if ( sizeof( $names ) > 0 ) {
			ob_start();
			if ( $only ) {
				echo esc_html( implode( ', ', $names ) );
			} else {
				$org = get_the_terms( $event_id, 'mep_org' );
				?>
                <div class="mpwem_organizer">
                    <h5><?php esc_html_e( 'Organized By : ', 'mage-eventpress' ); ?>&nbsp;</h5>
                    <div class="mpwem_organizer_item">
						<?php
							$total = count( $org );
							$index = 0;
							foreach ( $org as $value ) {
								echo '<a href="' . esc_url( get_term_link( $value->term_id, 'mep_org' ) ) . '">' . esc_html( $value->name ) . '</a>';
								if ( ++ $index < $total ) {
									echo ' |  ';
								}
							}
						?>
                    </div>
                </div>
				<?php
			}
			$content = ob_get_clean();
			echo apply_filters( 'mage_event_single_org_name', $content, $event_id );
		}
	}
