<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$event_infos = $event_infos ?? [];
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$type = $type ?? '';
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$event_infos               = sizeof( $event_infos ) > 0 ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$map_status                = array_key_exists( 'mep_sgm', $event_infos ) ? $event_infos['mep_sgm'] : '';
	$is_virtual                = array_key_exists( 'mep_event_type', $event_infos ) ? $event_infos['mep_event_type'] : '';
	$event_template            = array_key_exists( 'mep_event_template', $event_infos ) ? $event_infos['mep_event_template'] : '';
	$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_location_details     = array_key_exists( 'mep_event_hide_location_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_location_from_details'] : 'no';
	$icon_setting_sec          = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
	$icon_setting_sec          = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
	$location_icon             = array_key_exists( 'mep_event_location_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_location_icon'] : 'fas fa-map-marker-alt';
	if ( $hide_location_details == 'no' && $is_virtual != 'online' ) {
		$location = array_key_exists( 'full_address', $event_infos ) ? $event_infos['full_address'] : [];
		if ( is_array( $location ) && sizeof( $location ) > 0 ) {
			if ( $type == 'sidebar' ) {
				?>
                <div class="mpwem_location_sidebar">
                    <h5 class="widgets_title ">
						<?php if ( $event_template == 'smart.php' ) { ?>
                            <span class="<?php echo esc_attr( $location_icon ); ?> _mr_xs"></span>
						<?php } ?>
						<?php esc_html_e( 'Event Location', 'mage-eventpress' ); ?>
                    </h5>
                    <p><?php echo esc_html( implode( ', ', $location ) ); ?> </p>
					<?php if ( $map_status ) { ?>
						<?php if ( $event_template == 'smart.php' ) { ?>
                            <button type="button" class="_button_theme_margin_auto" onclick="window.location.href = '#mpwem_map_area'">
                                <i class="<?php echo esc_attr( $location_icon ); ?> _mr_xs"></i><?php esc_html_e( 'Find In Map', 'mage-eventpress' ); ?>
                            </button>
						<?php } else { ?>
                            <button type="button" class="_button_theme_margin_auto" data-target-popup="mpwem_popup_map">
                                <i class="<?php echo esc_attr( $location_icon ); ?> _mr_xs"></i><?php esc_html_e( 'Find In Map', 'mage-eventpress' ); ?>
                            </button>
                            <div class="mpPopup" data-popup="mpwem_popup_map">
                                <div class="popupMainArea _max_1000">
                                    <span class="fas fa-times popup_close"></span>
                                    <div class="popupBody _mp_zero">
										<?php do_action( 'mpwem_map', $event_id, $event_infos ); ?>
                                    </div>
                                </div>
                            </div>
						<?php } ?>
					<?php } ?>
                </div>
			<?php } elseif ( $type == 'sort' ) { ?>
                <div class="mpwem_location">
                    <i class="<?php echo esc_attr( $location_icon ); ?>"></i>
                    <div><?php echo esc_html( implode( ', ', $location ) ); ?></div>
                </div>
			<?php } elseif ( $type == 'only' ) { ?>
                <div class="short_item">
                    <h4 class="__icon_circle_mr"><span class="<?php echo esc_attr( $location_icon ); ?>"></span></h4>
                    <div class="_fdColumn">
                        <h6><?php esc_html_e( 'Event Location:', 'mage-eventpress' ); ?></h6>
                        <p><?php echo esc_html( implode( ', ', $location ) ); ?></p>
                    </div>
                </div>
				<?php
			} else {
				echo esc_html( $location[ $type ] );
			}
		}
	}
