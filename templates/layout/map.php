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
	$event_infos               = sizeof( $event_infos ) > 0 ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$map_status                = array_key_exists( 'mep_sgm', $event_infos ) ? $event_infos['mep_sgm'] : '';
	$mep_org_address           = array_key_exists( 'mep_org_address', $event_infos ) ? $event_infos['mep_org_address'] : '';
	$venue_value               = array_key_exists( 'mep_location_venue', $event_infos ) ? $event_infos['mep_location_venue'] : '';
	$is_virtual                = array_key_exists( 'mep_event_type', $event_infos ) ? $event_infos['mep_event_type'] : '';
	$general_setting_sec       = array_key_exists( 'general_setting_sec', $event_infos ) ? $event_infos['general_setting_sec'] : [];
	$general_setting_sec       = is_array( $general_setting_sec ) && ! empty( $general_setting_sec ) ? $general_setting_sec : [];
	$map_api                   = array_key_exists( 'google-map-api', $general_setting_sec ) ? $general_setting_sec['google-map-api'] : '';
	$map_type                  = array_key_exists( 'mep_google_map_type', $general_setting_sec ) ? $general_setting_sec['mep_google_map_type'] : 'iframe';
	$map_zoom                  = array_key_exists( 'mep_google_map_zoom_level', $general_setting_sec ) ? $general_setting_sec['mep_google_map_zoom_level'] : '17';
	$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_location_details     = array_key_exists( 'mep_event_hide_location_from_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_location_from_details'] : 'no';
	if ( $hide_location_details == 'no' && $map_status && $is_virtual != 'online' ) {
		$lat = 0;
		$lon = 0;
		if ( $mep_org_address ) {
			$org_arr = get_the_terms( $event_id, 'mep_org' );
			if ( $org_arr && ! is_wp_error( $org_arr ) ) {
				$org_id      = $org_arr[0]->term_id;
				$venue_value = get_term_meta( $org_id, 'org_location', true );
				$latitude    = get_term_meta( $org_id, 'latitude', true );
				$lat         = $latitude ? floatval( $latitude ) : 0;
				$longitude   = get_term_meta( $org_id, 'longitude', true );
				$lon         = $longitude ? floatval( $longitude ) : 0;
			}
		} else {
			$latitude  = array_key_exists( 'latitude', $event_infos ) ? $event_infos['latitude'] : '';
			$longitude = array_key_exists( 'longitude', $event_infos ) ? $event_infos['longitude'] : '';
			$lat       = $latitude ? floatval( $latitude ) : 0;
			$lon       = $longitude ? floatval( $longitude ) : 0;
		}
		if ( $map_type == 'iframe' || ! $map_api ) {
			if ( $lat != 0 && $lon != 0 ) {
				$location_query = $lat . ',' . $lon;
			} else {
				$location_query = urlencode( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $venue_value ) ? sanitize_text_field( $venue_value ) : $venue_value );
			}
			if ( $location_query ) {
				?>
                <div id="mpwem_map_area">
                    <h5 class="map_title"><?php esc_html_e( 'Event Location', 'mage-eventpress' ); ?></h5>
                    <div class="map_section">
                        <iframe src="https://maps.google.com/maps?q=<?php echo esc_attr( $location_query ); ?>&t=&z=17&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                    </div>
                </div>
				<?php
			}
		} else {
			if ( $lat == 0 && $lon == 0 ) {
				$lat = 37.0902; // Default latitude
				$lon = - 95.7129; // Default longitude
			}
			?>
            <div id="mpwem_map_area">
                <h5 class="map_title"><?php esc_html_e( 'Event Location', 'mage-eventpress' ); ?></h5>
                <div class="map_section">
                    <div id="mpwem_map"></div>
                </div>
            </div>
            <script>
                var map;
                var marker;
                function initMap() {
                    var mapElement = document.getElementById('mpwem_map');
                    if (!mapElement) {
                        console.error('Map container not found');
                        return;
                    }
                    var mapCenter = {
                        lat: <?php echo esc_js( $lat ); ?>,
                        lng: <?php echo esc_js( $lon ); ?>
                    };
                    var zoomLevel = <?php echo absint( $map_zoom ); ?>;
                    if (zoomLevel < 1 || zoomLevel > 20) {
                        zoomLevel = 17;
                    }
                    map = new google.maps.Map(mapElement, {
                        center: mapCenter,
                        zoom: zoomLevel
                    });
                    marker = new google.maps.Marker({
                        map: map,
                        draggable: false,
                        animation: google.maps.Animation.DROP,
                        position: {
                            lat: <?php echo esc_js( $lat ); ?>,
                            lng: <?php echo esc_js( $lon ); ?>
                        }
                    });
                    marker.addListener('click', toggleBounce);
                }
                function toggleBounce() {
                    if (marker && marker.getAnimation() !== null) {
                        marker.setAnimation(null);
                    } else if (marker) {
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                    }
                }
                // Fallback if Google Maps API fails to load
                window.addEventListener('load', function() {
                    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                        console.error('Google Maps API failed to load');
                    }
                });
            </script>
            <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr( $map_api ); ?>&callback=initMap" async defer></script>
			<?php
		}
	}

