<?php
if (!defined('ABSPATH')) {
  die;
} // Cannot access pages directly.

add_action('mep_org_add_form_fields', 'mep_org_tax_location_fileds', 10, 2);
function mep_org_tax_location_fileds($taxonomy)
{
?>
  <div class="form-field term-group">
    <label for="org_location"><?php _e('Location/Venue', 'mage-eventpress'); ?></label>
    <input type="text" name="org_location" id='org_location' class="postform">
  </div>

  <div class="form-field term-group">
    <label for="org_street"><?php _e('Street:', 'mage-eventpress'); ?></label>
    <input type="text" name="org_street" id='org_street' class="postform">
  </div>

  <div class="form-field term-group">
    <label for="org_city"><?php _e('City:', 'mage-eventpress'); ?></label>
    <input type="text" name="org_city" id='org_city' class="postform">
  </div>

  <div class="form-field term-group">
    <label for="org_state"><?php _e('State:', 'mage-eventpress'); ?></label>
    <input type="text" name="org_state" id='org_state' class="postform">
  </div>

  <div class="form-field term-group">
    <label for="org_postcode"><?php _e('Postcode:', 'mage-eventpress'); ?></label>
    <input type="text" name="org_postcode" id='org_postcode' class="postform">
  </div>

  <div class="form-field term-group">
    <label for="org_country"><?php _e('Country:', 'mage-eventpress'); ?></label>
    <input type="text" name="org_country" id='org_country' class="postform">
  </div>


  <div class='sec'>
    <?php
    $user_api = mep_get_option('google-map-api', 'general_setting_sec', '');
    if ($user_api) {
    ?>
      <input id="pac-input" name='location_name' value='<?php //echo $values['location_name'][0]; 
                                                        ?>' />


      <input type="text" class="form-control" style="display: none;" name="latitude" value="">
      <input type="text" class="form-control" style="display: none;" name="longitude" value="">
      <!-- <div id="map"></div> -->
      <?php
      $user_api = mep_get_option('google-map-api', 'general_setting_sec', '');
      if ($user_api) {
        //wp_enqueue_script('gmap-libs','https://maps.googleapis.com/maps/api/js?key='.$user_api.'&libraries=places&callback=initMap',array('jquery','gmap-scripts'),1,true);
      ?>
        <script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=<?php echo $user_api; ?>&#038;libraries=places&#038;callback=initMap&#038;ver=1'></script>
      <?php
      }
      ?>
      <script>
        function initMap() {
          var map = new google.maps.Map(document.getElementById('map'), {
            center: {
              lat: 37.0902,
              lng: 95.7129
            },
            zoom: 17
          });



          var input = /** @type {!HTMLInputElement} */ (
            document.getElementById('pac-input'));

          var types = document.getElementById('type-selector');
          map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
          map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);

          var autocomplete = new google.maps.places.Autocomplete(input);
          autocomplete.bindTo('bounds', map);

          var infowindow = new google.maps.InfoWindow();
          var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29),
            draggable: true,
            position: {
              lat: 37.0902,
              lng: 95.7129
            }
          });

          google.maps.event.addListener(marker, 'dragend', function() {
            document.getElementsByName('latitude')[0].value = marker.getPosition().lat();
            document.getElementsByName('longitude')[0].value = marker.getPosition().lng();
          })



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

            $("input[name=coordinate]").val(address);
            $("input[name=latitude]").val(latitude);
            $("input[name=longitude]").val(longitude);

            //infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
            //infowindow.open(map, marker);
          });
        }
        google.maps.event.addDomListener(window, "load", initMap);
      </script>
    <?php
    } else {
      // echo "<span class=mep_status><span class=err>No Google MAP API Key Found. Please enter API KEY <a href=".get_site_url()."/wp-admin/options-general.php?page=mep_event_settings_page>Here</a></span></span>";
    }
    ?>
  </div>
<?php
}

add_action('created_mep_org', 'save_feature_meta', 10, 2);

function save_feature_meta($term_id, $tt_id)
{

  if (isset($_POST['org_location'])) {
    $org_location = strip_tags($_POST['org_location']);
    add_term_meta($term_id, 'org_location', $org_location);
  }
  if (isset($_POST['org_street'])) {
    $org_street = strip_tags($_POST['org_street']);
    add_term_meta($term_id, 'org_street', $org_street);
  }
  if (isset($_POST['org_city'])) {
    $org_city = strip_tags($_POST['org_city']);
    add_term_meta($term_id, 'org_city', $org_city);
  }
  if (isset($_POST['org_state'])) {
    $org_state = strip_tags($_POST['org_state']);
    add_term_meta($term_id, 'org_state', $org_state);
  }
  if (isset($_POST['org_postcode'])) {
    $org_postcode = strip_tags($_POST['org_postcode']);
    add_term_meta($term_id, 'org_postcode', $org_postcode);
  }

  if (isset($_POST['org_country'])) {
    $org_country = strip_tags($_POST['org_country']);
    add_term_meta($term_id, 'org_country', $org_country);
  }

  if (isset($_POST['latitude'])) {
    $latitude = strip_tags($_POST['latitude']);
    add_term_meta($term_id, 'latitude', $latitude);
  }

  if (isset($_POST['longitude'])) {
    $longitude = strip_tags($_POST['longitude']);
    add_term_meta($term_id, 'longitude', $longitude);
  }
}




add_action('mep_org_edit_form_fields', 'edit_feature_group_field', 10, 2);

function edit_feature_group_field($term, $taxonomy)
{
?>
  <tr class="form-field term-group-wrap">
    <th scope="row"><label for="org_location"><?php _e('Location/Venue', 'mage-eventpress'); ?></label></th>
    <td>
      <input type="text" name="org_location" id='org_location' class="postform" value='<?php echo get_term_meta($term->term_id, 'org_location', true); ?>'>
    </td>
  </tr>
  <tr class="form-field term-group-wrap">
    <th scope="row"><label for="org_street"><?php _e('Street:', 'mage-eventpress'); ?></label></th>
    <td>
      <input type="text" name="org_street" id='org_street' class="postform" value='<?php echo get_term_meta($term->term_id, 'org_street', true); ?>'>
    </td>
  </tr>
  <tr class="form-field term-group-wrap">
    <th scope="row"><label for="org_city"><?php _e('City:', 'mage-eventpress'); ?></label></th>
    <td>
      <input type="text" name="org_city" id='org_city' class="postform" value='<?php echo get_term_meta($term->term_id, 'org_city', true); ?>'>
    </td>
  </tr>
  <tr class="form-field term-group-wrap">
    <th scope="row"><label for="org_state"><?php _e('State:', 'mage-eventpress'); ?></label></th>
    <td>
      <input type="text" name="org_state" id='org_state' class="postform" value='<?php echo get_term_meta($term->term_id, 'org_state', true); ?>'>
    </td>
  </tr>
  <tr class="form-field term-group-wrap">
    <th scope="row"><label for="org_postcode"><?php _e('Postcode:', 'mage-eventpress'); ?></label></th>
    <td>
      <input type="text" name="org_postcode" id='org_postcode' class="postform" value='<?php echo get_term_meta($term->term_id, 'org_postcode', true); ?>'>
    </td>
  </tr>
  <tr class="form-field term-group-wrap">
    <th scope="row"><label for="org_country"><?php _e('Country:', 'mage-eventpress'); ?></label></th>
    <td>
      <input type="text" name="org_country" id='org_country' class="postform" value='<?php echo get_term_meta($term->term_id, 'org_country', true); ?>'>
    </td>
  </tr>
  <tr class="form-field term-group-wrap">
    <th scope="row"><label for="org_country"><?php _e('Map:', 'mage-eventpress'); ?></label></th>
    <td>

      <?php
      $user_api = mep_get_option('google-map-api', 'general_setting_sec', '');
      if ($user_api) {
      ?>
        <div class='sec'>
          <input id="pac-input" name='location_name' value='<?php //echo $values['location_name'][0]; 
                                                            ?>' />
        </div>

        <input type="text" class="form-control" style="display: none;" name="latitude" value="<?php echo get_term_meta($term->term_id, 'latitude', true); ?>">
        <input type="text" class="form-control" style="display: none;" name="longitude" value="<?php echo get_term_meta($term->term_id, 'longitude', true); ?>">
        <!-- <div id="map"></div> -->
        <?php

        if ($user_api) {
        ?>
          <script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=<?php echo $user_api; ?>&#038;libraries=places&#038;callback=initMap&#038;ver=1'></script>
        <?php
        }
        if (get_term_meta($term->term_id, 'latitude', true)) {
          $lat = get_term_meta($term->term_id, 'latitude', true);
        } else {
          $lat = '37.0902';
        }


        if (get_term_meta($term->term_id, 'longitude', true)) {
          $lon = get_term_meta($term->term_id, 'longitude', true);
        } else {
          $lon = '95.7129';
        }

        ?>
        <script>
          function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
              center: {
                lat: <?php echo $lat; ?>,
                lng: <?php echo $lon; ?>
              },
              zoom: 17
            });



            var input = /** @type {!HTMLInputElement} */ (
              document.getElementById('pac-input'));

            var types = document.getElementById('type-selector');
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);

            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            var infowindow = new google.maps.InfoWindow();
            var marker = new google.maps.Marker({
              map: map,
              anchorPoint: new google.maps.Point(0, -29),
              draggable: true,
              position: {
                lat: <?php echo $lat; ?>,
                lng: <?php echo $lon; ?>
              }
            });

            google.maps.event.addListener(marker, 'dragend', function() {
              document.getElementsByName('latitude')[0].value = marker.getPosition().lat();
              document.getElementsByName('longitude')[0].value = marker.getPosition().lng();
            })

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

              $("input[name=coordinate]").val(address);
              $("input[name=latitude]").val(latitude);
              $("input[name=longitude]").val(longitude);

              //infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
              //infowindow.open(map, marker);
            });
          }
          google.maps.event.addDomListener(window, "load", initMap);
        </script>
      <?php
      } else {
        // echo "<span class=mep_status><span class=err>No Google MAP API Key Found. Please enter API KEY <a href=".get_site_url()."/wp-admin/options-general.php?page=mep_event_settings_page>Here</a></span></span>";
      }
      ?>
    </td>
  </tr>
<?php
}


add_action('edited_mep_org', 'update_feature_meta', 10, 2);

function update_feature_meta($term_id, $tt_id)
{

  if (isset($_POST['org_location'])) {
    $org_location = strip_tags($_POST['org_location']);
    update_term_meta($term_id, 'org_location', $org_location);
  }

  if (isset($_POST['org_street'])) {
    $org_street = strip_tags($_POST['org_street']);
    update_term_meta($term_id, 'org_street', $org_street);
  }

  if (isset($_POST['org_city'])) {
    $org_city = strip_tags($_POST['org_city']);
    update_term_meta($term_id, 'org_city', $org_city);
  }

  if (isset($_POST['org_state'])) {
    $org_state = strip_tags($_POST['org_state']);
    update_term_meta($term_id, 'org_state', $org_state);
  }

  if (isset($_POST['org_postcode'])) {
    $org_postcode = strip_tags($_POST['org_postcode']);
    update_term_meta($term_id, 'org_postcode', $org_postcode);
  }

  if (isset($_POST['org_country'])) {
    $org_country = strip_tags($_POST['org_country']);
    update_term_meta($term_id, 'org_country', $org_country);
  }

  if (isset($_POST['latitude'])) {
    $latitude = strip_tags($_POST['latitude']);
    update_term_meta($term_id, 'latitude', $latitude);
  }

  if (isset($_POST['longitude'])) {
    $longitude = strip_tags($_POST['longitude']);
    update_term_meta($term_id, 'longitude', $longitude);
  }
}
