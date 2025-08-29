<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Venue_Settings' ) ) {
		class MPWEM_Venue_Settings {
			public function __construct() {
				add_action( 'mp_event_all_in_tab_item', array( $this, 'venue_settings' ) );
			}

			public function venue_settings( $event_id ) {
				?>
                <div class="mp_tab_item active" data-tab-item="#mp_event_venue">
                    <h3><?php esc_html_e( 'Venue/Location Settings', 'mage-eventpress' ) ?></h3>
                    <p><?php esc_html_e( 'Configure Your Venue/Location Settings Here', 'mage-eventpress' ) ?></p>
                    <section class="bg-light">
                        <h2><?php esc_html_e( 'General Settings', 'mage-eventpress' ) ?></h2>
                        <span><?php esc_html_e( 'Configure Event Locations and Virtual Venues', 'mage-eventpress' ) ?></span>
                    </section>
					<?php do_action( 'mep_event_tab_before_location', $event_id ); ?>

					<?php $this->event_online_enable( $event_id ); ?>
					<?php $this->event_venue( $event_id ); ?>

					<?php do_action( 'mep_event_tab_after_location' ); ?>
                </div>
				<?php
			}

			public function event_venue( $post_id ) {
				$event_label     = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				$values          = get_post_custom( $post_id );
				$user_api        = mep_get_option( 'google-map-api', 'general_setting_sec', '' );
				$map_type        = mep_get_option( 'mep_google_map_type', 'general_setting_sec', 'iframe' );
				$mep_org_address = array_key_exists( 'mep_org_address', $values ) ? $values['mep_org_address'][0] : 0;
				$map_visible     = array_key_exists( 'mep_sgm', $values ) ? $values['mep_sgm'][0] : 0;
				$author_id       = get_post_field( 'post_author', $post_id );
				$event_type      = get_post_meta( $post_id, 'mep_event_type', true );
				$organizer       = [

					$event_label . __( ' Details', 'mage-eventpress' ),
					__( 'Organizer' ,'mage-eventpress'),
				];
				if ( $this->is_gutenberg_active() ) { ?>
                    <input type="hidden" name="post_author_gutenberg" value="<?php echo esc_attr( $author_id ); ?>">
				<?php }
				?>
                <div class='mep_event_tab_location_content' id='mpev-close-online-event' style="display:<?php echo ( $event_type == 'online' ) ? esc_attr( 'none' ) : esc_attr( 'block' ); ?>">
                    <section>
                        <label class="mpev-label">
                            <div>
                                <h2><?php esc_html_e( " Location Source:", "mage-eventpress" ); ?></h2>
                                <span><?php esc_html_e( 'If you have saved organizer details, please select the "Organizer" option. Please note that if you select "Organizer" and have not checked the organizer from the Event Organizer list on the right sidebar, the Event Location section will not populate on the front end.', 'mage-eventpress' ); ?></span>
                            </div>
                            <select class="mp_formControl" name="mep_org_address" class='mep_org_address_list' id='mep_org_address_list'>
								<?php foreach ( $organizer as $key => $value ): ?>
                                    <option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $mep_org_address == $key ) ? esc_attr( 'selected' ) : ''; ?> > <?php esc_html_e( $value ); ?> </option>
								<?php endforeach; ?>
                            </select>
                        </label>
                    </section>
                    <section class="mp_event_address">
                        <table>
                            <tr class="mp_event_address">
                                <th><?php esc_html_e( 'Location/Venue:', 'mage-eventpress' ); ?></th>
                                <td>
                                    <label>
                                        <input type="text" name='mep_location_venue' placeholder="Ex: New york Meeting Center" class="mp_formControl" value='<?php echo mep_get_event_locaion_item( $post_id, 'mep_location_venue' ); ?>'>
                                    </label>
                                </td>
                                <th><span><?php esc_html_e( 'Street:', 'mage-eventpress' ); ?></span></th>
                                <td>
                                    <label>
                                        <input type="text" name='mep_street' placeholder="Ex: 10 E 33rd St" class="mp_formControl" value='<?php echo mep_get_event_locaion_item( $post_id, 'mep_street' ); ?>'>
                                    </label>
                                </td>
                            </tr>
                            <tr class="mp_event_address">
                                <th><span><?php esc_html_e( 'City: ', 'mage-eventpress' ); ?></span></th>
                                <td>
                                    <label>
                                        <input type="text" name='mep_city' placeholder="Ex: New York" class="mp_formControl" value='<?php echo mep_get_event_locaion_item( $post_id, 'mep_city' ); ?>'>
                                    </label>
                                </td>
                                <th><span><?php esc_html_e( 'State: ', 'mage-eventpress' ); ?></span></th>
                                <td>
                                    <label>
                                        <input type="text" name='mep_state' placeholder="Ex: NY" class="mp_formControl" value='<?php echo mep_get_event_locaion_item( $post_id, 'mep_state' ); ?>'>
                                    </label>
                                </td>
                            </tr>
                            <tr class="mp_event_address">
                                <th><span><?php esc_html_e( 'Postcode: ', 'mage-eventpress' ); ?></span></th>
                                <td>
                                    <label>
                                        <input type="text" name='mep_postcode' placeholder="Ex: 10016" class="mp_formControl" value='<?php echo mep_get_event_locaion_item( $post_id, 'mep_postcode' ); ?>'>
                                    </label>
                                </td>
                                <th><span><?php esc_html_e( 'Country: ', 'mage-eventpress' ); ?></span></th>
                                <td>
                                    <label>
                                        <input type="text" name='mep_country' placeholder="Ex: USA" class="mp_formControl" value='<?php echo mep_get_event_locaion_item( $post_id, 'mep_country' ); ?>'>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </section>
                    <section>
                        <div class="mpev-label">
                            <div>
                                <h2><?php esc_html_e( 'Show Google Map', 'mage-eventpress' ); ?></h2>
                                <span><?php esc_html_e( 'Show an interactive Google Map on your website, letting users easily explore and find locations.', 'mage-eventpress' ); ?></span>
                            </div>
                            <label class="mpev-switch">
                                <input type="checkbox" name="mep_sgm" value="<?php echo esc_attr( $map_visible ); ?>" <?php echo esc_attr( ( $map_visible == 1 ) ? 'checked' : '' ); ?> data-collapse-target="#mpev-show-map" data-close-target="#mpev-close-map" data-toggle-values="1,0">
                                <span class="mpev-slider"></span>
                            </label>
                        </div>
                    </section>
                    <section class="mp_form_area" id="mpev-show-map" style="display:<?php echo ( $map_visible == 1 ) ? esc_attr( 'block' ) : esc_attr( 'none' ); ?>">
                        <div class="mp_form_item">
							<?php
								if ( $map_type == 'iframe' ) {
									?>
                                    <div id="show_gmap">
                                        <iframe id="gmap_canvas" src="https://maps.google.com/maps?q=<?php echo mep_get_event_locaion_item( $post_id, 'mep_location_venue' ); ?>&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                                    </div>
								<?php }
								if ( $map_type == 'api' ) {
								if ( $user_api ) {
									?>
                                    <div class='sec'>
                                        <input id="pac-input" name='location_name' value=''/>
                                    </div>
                                <input type="hidden" class="form-control" id="latitude" name="latitude" value="<?php 
                                    if ( array_key_exists( 'latitude', $values ) && !empty($values['latitude'][0]) ) {
                                        echo esc_attr( $values['latitude'][0] );
                                    } else {
                                        echo esc_attr( get_post_meta( $post_id, 'latitude', true ) );
                                    }
                                ?>">
                                <input type="hidden" class="form-control" id="longitude" name="longitude" value="<?php 
                                    if ( array_key_exists( 'longitude', $values ) && !empty($values['longitude'][0]) ) {
                                        echo esc_attr( $values['longitude'][0] );
                                    } else {
                                        echo esc_attr( get_post_meta( $post_id, 'longitude', true ) );
                                    }
                                ?>">
                                    <div id="map"></div>
									<?php
								} else {
									?>
                                    <span class=mep_status><span class=err>
								<?php esc_html_e( 'No Google MAP API Key Found. Please enter API KEY', 'mage-eventpress' ); ?> <a href="<?php echo get_site_url() . esc_url( '/wp-admin/options-general.php?page=mep_event_settings_page' ); ?>"><?php esc_html_e( 'Here', 'mage-eventpress' ); ?></a></span>
							</span>
								<?php
									}
									                                    // Get coordinates from post meta or form values
                                    $saved_lat = get_post_meta( $post_id, 'latitude', true );
                                    $saved_lon = get_post_meta( $post_id, 'longitude', true );
                                    
                                    if ( array_key_exists( 'latitude', $values ) && ! empty( $values['latitude'][0] ) ) {
                                        $lat = str_replace( ',', '.', $values['latitude'][0] );
                                    } elseif ( !empty( $saved_lat ) ) {
                                        $lat = str_replace( ',', '.', $saved_lat );
                                    } else {
                                        $lat = '37.0902';
                                    }
                                    
                                    if ( array_key_exists( 'longitude', $values ) && ! empty( $values['longitude'][0] ) ) {
                                        $lon = str_replace( ',', '.', $values['longitude'][0] );
                                    } elseif ( !empty( $saved_lon ) ) {
                                        $lon = str_replace( ',', '.', $saved_lon );
                                    } else {
                                        $lon = '95.7129';
                                    }
								?>
                                   <script>
                                       function initMap() {
                                           var map = new google.maps.Map(document.getElementById('map'), {
                                               center: {
                                                   lat: <?php echo esc_attr($lat); ?>,
                                                   lng: <?php echo esc_attr($lon); ?>
                                               },
                                               zoom: 17
                                           });
                                           
                                           var input = document.getElementById('pac-input');
                                           if (input) {
                                               map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
                                           }
                                           
                                           var autocomplete = new google.maps.places.Autocomplete(input);
                                           autocomplete.bindTo('bounds', map);
                                           
                                           var infowindow = new google.maps.InfoWindow();
                                           var marker = new google.maps.Marker({
                                               map: map,
                                               anchorPoint: new google.maps.Point(0, -29),
                                               draggable: true,
                                               position: {
                                                   lat: <?php echo esc_attr($lat); ?>,
                                                   lng: <?php echo esc_attr($lon); ?>
                                               }
                                           });
                                           
                                           google.maps.event.addListener(marker, 'dragend', function() {
                                               var lat = marker.getPosition().lat();
                                               var lng = marker.getPosition().lng();
                                               document.getElementById('latitude').value = lat;
                                               document.getElementById('longitude').value = lng;
                                               jQuery('#latitude').val(lat).trigger('change');
                                               jQuery('#longitude').val(lng).trigger('change');
                                               console.log('Coordinates updated: ', lat, lng);
                                           });
                                           autocomplete.addListener('place_changed', function() {
                                               infowindow.close();
                                               marker.setVisible(false);
                                               var place = autocomplete.getPlace();
                                               if (!place.geometry) {
                                                   window.alert("Autocomplete's returned place contains no geometry");
                                                   return;
                                               }
                                               // If the place has a geometry, then present it on a map.
                                               if (place.geometry.viewport) {
                                                   map.fitBounds(place.geometry.viewport);
                                               } else {
                                                   map.setCenter(place.geometry.location);
                                                   map.setZoom(17); // Why 17? Because it looks good.
                                               }
                                               marker.setIcon( /** @type {google.maps.Icon} */ ({
                                                   url: 'http://maps.google.com/mapfiles/ms/icons/red.png',
                                                   size: new google.maps.Size(71, 71),
                                                   origin: new google.maps.Point(0, 0),
                                                   anchor: new google.maps.Point(17, 34),
                                                   scaledSize: new google.maps.Size(35, 35)
                                               }));
                                               marker.setPosition(place.geometry.location);
                                               marker.setVisible(true);
                                               var address = '';
                                               if (place.address_components) {
                                                   address = [
                                                       (place.address_components[0] && place.address_components[0].short_name || ''),
                                                       (place.address_components[1] && place.address_components[1].short_name || ''),
                                                       (place.address_components[2] && place.address_components[2].short_name || '')
                                                   ].join(' ');
                                               }
                                                var latitude = place.geometry.location.lat();
                                                var longitude = place.geometry.location.lng();
                                                
                                                // Update both hidden fields and trigger change events
                                                document.getElementById('latitude').value = latitude;
                                                document.getElementById('longitude').value = longitude;
                                                jQuery('#latitude').val(latitude).trigger('change');
                                                jQuery('#longitude').val(longitude).trigger('change');
                                                
                                                console.log('Place selected - Coordinates updated: ', latitude, longitude);
                                           });

         function debounce(func, wait, immediate) {
          var timeout;
          return function() {
           var context = this, args = arguments;
           var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
           };
           var callNow = immediate && !timeout;
           clearTimeout(timeout);
           timeout = setTimeout(later, wait);
           if (callNow) func.apply(context, args);
          };
         };

          // Enhanced function to handle both coordinates and addresses
          var geocodeAddress = debounce(function() {
           var geocoder = new google.maps.Geocoder();
           var locationInput = jQuery('[name="mep_location_venue"]').val().trim();
           
           // Check if input looks like coordinates (lat,lng format)
           var coordinatePattern = /^(-?\d+\.?\d*),\s*(-?\d+\.?\d*)$/;
           var coordinateMatch = locationInput.match(coordinatePattern);
           
           if (coordinateMatch) {
               // Handle direct coordinate input
               var lat = parseFloat(coordinateMatch[1]);
               var lng = parseFloat(coordinateMatch[2]);
               
               // Validate coordinate ranges
               if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                   var position = new google.maps.LatLng(lat, lng);
                   
                   map.setCenter(position);
                   marker.setPosition(position);
                   map.setZoom(15); // Zoom in for coordinate-based locations
                   
                   // Update coordinate fields
                   document.getElementById('latitude').value = lat;
                   document.getElementById('longitude').value = lng;
                   jQuery('#latitude').val(lat).trigger('change');
                   jQuery('#longitude').val(lng).trigger('change');
                   
                   console.log('Direct coordinates used - Map updated: ', lat, lng);
                   
                   // Optionally reverse geocode to get address
                   geocoder.geocode({'location': position}, function(results, status) {
                       if (status === 'OK' && results[0]) {
                           console.log('Reverse geocoded address: ', results[0].formatted_address);
                       }
                   });
               } else {
                   console.warn('Invalid coordinate ranges: ', lat, lng);
               }
           } else {
               // Handle regular address input
               var address = locationInput + ', ' + jQuery('[name="mep_street"]').val() + ', ' + jQuery('[name="mep_city"]').val() + ', ' + jQuery('[name="mep_state"]').val() + ', ' + jQuery('[name="mep_postcode"]').val() + ', ' + jQuery('[name="mep_country"]').val();
               
               // Only geocode if we have a meaningful address
               if (address.replace(/,\s*/g, '').trim().length > 3) {
                   geocoder.geocode({
                    'address': address
                   }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK && results[0]) {
                     var lat = results[0].geometry.location.lat();
                     var lng = results[0].geometry.location.lng();
                     
                     map.setCenter(results[0].geometry.location);
                     marker.setPosition(results[0].geometry.location);
                     
                     // Update coordinates with proper change triggers
                     document.getElementById('latitude').value = lat;
                     document.getElementById('longitude').value = lng;
                     jQuery('#latitude').val(lat).trigger('change');
                     jQuery('#longitude').val(lng).trigger('change');
                     
                     console.log('Geocoded address - Coordinates updated: ', lat, lng);
                    } else {
                     console.warn('Geocoding failed: ', status);
                    }
                   });
               }
           }
          }, 800);

          jQuery('[name="mep_location_venue"], [name="mep_street"], [name="mep_city"], [name="mep_state"], [name="mep_postcode"], [name="mep_country"]').on('input', geocodeAddress);

                                        }
                                        
                                        (function() {
                                            var google_map_api_key = '<?php echo $user_api; ?>';
                                            var script = document.createElement('script');
                                            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + google_map_api_key + '&libraries=places&callback=initMap';
                                            script.defer = true;
                                            script.async = true;
                                            script.onerror = function() {
                                                console.error('Failed to load Google Maps API. Please check your API key.');
                                            };
                                            document.head.appendChild(script);
                                        })();
                                    </script>
					<?php
				}
			?>
                        </div>
                    </section>
                    <script type="text/javascript">
                        jQuery('[name="mep_org_address"]').change(function () {
                            let source = jQuery(this).val();
                            if (source === '1') {
                                jQuery('.mp_event_address').slideUp(250);
                                jQuery('[name="mep_location_venue"]').val('<?php echo mep_event_org_location_item( $post_id, 'org_location' ); ?>');
                                jQuery('[name="mep_street"]').val('<?php echo mep_event_org_location_item( $post_id, 'org_street' ); ?>');
                                jQuery('[name="mep_city"]').val('<?php echo mep_event_org_location_item( $post_id, 'org_city' ); ?>');
                                jQuery('[name="mep_state"]').val('<?php echo mep_event_org_location_item( $post_id, 'org_state' ); ?>');
                                jQuery('[name="mep_postcode"]').val('<?php echo mep_event_org_location_item( $post_id, 'org_postcode' ); ?>');
                                jQuery('[name="mep_country"]').val('<?php echo mep_event_org_location_item( $post_id, 'org_country' ); ?>');
                                let location = jQuery('[name="mep_location_venue"]').val();
                                jQuery('#show_gmap').html('<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=' + location + '&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>')
                            } else {
                                jQuery('.mp_event_address').slideDown();
                                jQuery('[name="mep_location_venue"]').val('<?php echo mep_event_location_item( $post_id, 'mep_location_venue' ); ?>');
                                jQuery('[name="mep_street"]').val('<?php echo mep_event_location_item( $post_id, 'mep_street' ); ?>');
                                jQuery('[name="mep_city"]').val('<?php echo mep_event_location_item( $post_id, 'mep_city' ); ?>');
                                jQuery('[name="mep_state"]').val('<?php echo mep_event_location_item( $post_id, 'mep_state' ); ?>');
                                jQuery('[name="mep_postcode"]').val('<?php echo mep_event_location_item( $post_id, 'mep_postcode' ); ?>');
                                jQuery('[name="mep_country"]').val('<?php echo mep_event_location_item( $post_id, 'mep_country' ); ?>');
                                let location = jQuery('[name="mep_location_venue"]').val();
                                jQuery('#show_gmap').html('<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=' + location + '&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>')
                            }
                        })
                        jQuery('[name="mep_location_venue"]').on('input', function () {
                            let location = jQuery(this).val();
                            if (location !== '') {
                                jQuery('#show_gmap').html('<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=' + encodeURIComponent(location) + '&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>')
                            }
                        })
                    </script>
                </div>
				<?php
			}

			public function event_online_enable( $post_id ) {
				$event_label       = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				$event_type        = get_post_meta( $post_id, 'mep_event_type', true );
				$event_member_type = get_post_meta( $post_id, 'mep_member_only_event', true );
				$description       = html_entity_decode( get_post_meta( $post_id, 'mp_event_virtual_type_des', true ) );
				$checked           = ( $event_type == 'online' ) ? 'online' : '';
				?>
                <section>
                    <div class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Online/Virtual ', 'mage-eventpress' );
										echo esc_html( $event_label . '?' ); ?> (No/Yes)</span></h2>
                            <span><?php _e( 'If your event is online or virtual, please ensure that this option is enabled.', 'mage-eventpress' ); ?></span>
                        </div>
                        <label class="mpev-switch">
                            <input type="checkbox" name="mep_event_type" value="<?php echo esc_attr( $checked ); ?>" <?php echo esc_attr( ( $checked == 'online' ) ? 'checked' : '' ); ?> data-collapse-target="#mpev-online-event" data-close-target="#mpev-close-online-event" data-toggle-values="online,offline">
                            <span class="mpev-slider"></span>
                        </label>
                    </div>
                </section>
				<?php do_action( 'mep_event_details_before_virtual_event_info_text_box', $post_id ); ?>
                <section class="mp_event_virtual_type_des" id='mpev-online-event' style="display:<?php echo ( $event_type == 'online' ) ? esc_attr( 'block' ) : esc_attr( 'none' ); ?>">
                    <p><?php esc_html_e( 'Please enter your virtual event joining details in the form below. This information will be sent to the buyer along with a confirmation email.', 'mage-eventpress' ) ?></p>
                    <br>
					<?php wp_editor( html_entity_decode( nl2br( $description ) ), 'mp_event_virtual_type_des' ); ?>
                </section>
				<?php do_action( 'mep_event_details_after_virtual_event_info_text_box', $post_id ); ?>
				<?php
			}

			public function is_gutenberg_active() {
				$gutenberg    = false;
				$block_editor = false;
				if ( has_filter( 'replace_editor', 'gutenberg_init' ) ) {
					// Gutenberg is installed and activated.
					$gutenberg = true;
				}
				if ( version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' ) ) {
					// Block editor.
					$block_editor = true;
				}
				if ( ! $gutenberg && ! $block_editor ) {
					return false;
				}
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
					return true;
				}
				$use_block_editor = ( get_option( 'classic-editor-replace' ) === 'no-replace' );

				return $use_block_editor;
			}
		}
		new MPWEM_Venue_Settings();
	}