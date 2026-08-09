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
	$event_infos               = MPWEM_Functions::get_all_info( $event_id );
	$map_status                = is_array( $event_infos ) && array_key_exists( 'mep_sgm', $event_infos ) ? $event_infos['mep_sgm'] : '';
	$is_virtual                = is_array( $event_infos ) && array_key_exists( 'mep_event_type', $event_infos ) ? $event_infos['mep_event_type'] : '';
	$event_template            = is_array( $event_infos ) && array_key_exists( 'mep_event_template', $event_infos ) ? $event_infos['mep_event_template'] : '';
	$_single_event_setting_sec = is_array( $event_infos ) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_location_details     = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_event_hide_location_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_location_from_details'] : 'no';
	$icon_setting_sec          = is_array( $event_infos ) && array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
	$icon_setting_sec          = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
	$location_icon             = is_array( $icon_setting_sec ) && array_key_exists( 'mep_event_location_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_location_icon'] : 'mi mi-marker';
	if ( $hide_location_details == 'no' && $is_virtual != 'online' ) {
		$location = is_array( $event_infos ) && array_key_exists( 'full_address', $event_infos ) ? $event_infos['full_address'] : [];
		if ( is_array( $location ) && sizeof( $location ) > 0 ) {
			$location_parts = array_filter( array_map( 'trim', array_values( $location ) ) );
			$full_address   = implode( ', ', $location_parts );
			$venue_name     = ! empty( $location['location'] ) ? $location['location'] : ( ! empty( $location_parts ) ? reset( $location_parts ) : '' );
			$secondary_bits = array();
			foreach ( array( 'street', 'city', 'state', 'zip', 'country' ) as $loc_key ) {
				if ( ! empty( $location[ $loc_key ] ) && $location[ $loc_key ] !== $venue_name ) {
					$secondary_bits[] = $location[ $loc_key ];
				}
			}
			$secondary_line = implode( ', ', $secondary_bits );

			if ( $type == 'sidebar' ) {
				?>
                <div class="mpwem_location_sidebar">
                    <div class="mpwem_location_sidebar__head">
                        <span class="mpwem_location_sidebar__icon" aria-hidden="true">
                            <i class="<?php echo esc_attr( $location_icon ); ?>"></i>
                        </span>
                        <div class="mpwem_location_sidebar__copy">
                            <span class="mpwem_location_sidebar__eyebrow"><?php esc_html_e( 'Venue', 'mage-eventpress' ); ?></span>
                            <h5 class="widgets_title"><?php esc_html_e( 'Event Location', 'mage-eventpress' ); ?></h5>
                        </div>
                    </div>
                    <div class="mpwem_location_sidebar__body">
                        <?php if ( $venue_name ) : ?>
                            <p class="mpwem_location_sidebar__venue"><?php echo esc_html( $venue_name ); ?></p>
                        <?php endif; ?>
                        <?php if ( $secondary_line ) : ?>
                            <p class="mpwem_location_sidebar__address"><?php echo esc_html( $secondary_line ); ?></p>
                        <?php elseif ( $full_address && $full_address !== $venue_name ) : ?>
                            <p class="mpwem_location_sidebar__address"><?php echo esc_html( $full_address ); ?></p>
                        <?php endif; ?>
                    </div>
					<?php if ( $map_status ) { ?>
						<div class="mpwem_location_sidebar__actions">
						<?php if ( $event_template == 'smart.php' ) { ?>
                            <button type="button" class="mpwem_location_map_btn" onclick="window.location.href = '#mpwem_map_area'">
                                <i class="<?php echo esc_attr( $location_icon ); ?>" aria-hidden="true"></i>
                                <span><?php esc_html_e( 'Find In Map', 'mage-eventpress' ); ?></span>
                            </button>
						<?php } else { ?>
                            <button type="button" class="mpwem_location_map_btn" data-target-popup="mpwem_popup_map">
                                <i class="<?php echo esc_attr( $location_icon ); ?>" aria-hidden="true"></i>
                                <span><?php esc_html_e( 'Find In Map', 'mage-eventpress' ); ?></span>
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
						</div>
					<?php } ?>
                </div>
			<?php } elseif ( $type == 'sort' ) { ?>
                <div class="mpwem_location">
                    <i class="<?php echo esc_attr( $location_icon ); ?>"></i>
                    <div><?php echo esc_html( $full_address ); ?></div>
                </div>
			<?php } elseif ( $type == 'only' ) { ?>
                <div class="short_item">
                    <h4 class="__icon_circle_mr"><span class="<?php echo esc_attr( $location_icon ); ?>"></span></h4>
                    <div class="_fdColumn">
                        <h6><?php esc_html_e( 'Event Location:', 'mage-eventpress' ); ?></h6>
                        <p><?php echo esc_html( $full_address ); ?></p>
                    </div>
                </div>
				<?php
			} elseif ( empty( $type ) ) {
                echo esc_html( $full_address );
            } else {
				echo esc_html( is_array( $location ) && array_key_exists( $type, $location ) ? $location[ $type ] : '' );
			}
		}
	}
