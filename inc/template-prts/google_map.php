<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_map', 'mep_event_google_map');
if (!function_exists('mep_event_google_map')) {
	function mep_event_google_map($event_id)
	{
		global $post, $event_meta, $user_api;		
		$map_type       = mep_get_option('mep_google_map_type', 'general_setting_sec', 'iframe');
		$map_zoom       = mep_get_option('mep_google_map_zoom_level', 'general_setting_sec', '17');
		$location_sts   = get_post_meta($event_id, 'mep_org_address', true) ? get_post_meta($event_id, 'mep_org_address', true) : '';
		$status   		= get_post_meta($event_id, 'mep_sgm', true) ? get_post_meta($event_id, 'mep_sgm', true) : '';
		ob_start();
		do_action('mep_event_before_google_map');
		if ($location_sts) {
			$org_arr 	= get_the_terms($event_id, 'mep_org');
			$org_id 	= $org_arr[0]->term_id;
			$lat 		= get_term_meta($org_id, 'latitude', true) ? get_term_meta($org_id, 'latitude', true) : 0;
			$lon 		= get_term_meta($org_id, 'longitude', true) ? get_term_meta($org_id, 'longitude', true) : 0;
		} else {
			$lat 		= get_post_meta($event_id, 'latitude', true) ? get_post_meta($event_id, 'latitude', true) : 0;			
			$lon 		= get_post_meta($event_id, 'longitude', true) ? get_post_meta($event_id, 'longitude', true) : 0;						
		}
		if ($status) {
			if ($map_type == 'iframe') {
?>
				<div class="mep-gmap-sec">
					<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=<?php echo mep_get_event_locaion_item($event_id, 'mep_location_venue'); ?>&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style='width: 100%;min-height: 250px;'></iframe>
				</div>
				<?php
			} else {
				if ($user_api) {
				?>
					<div class="mep-gmap-sec">
						<div id="map" class='mep_google_map'></div>
					</div>
					<script>
						var map;
						function initMap() {
							map = new google.maps.Map(document.getElementById('map'), {
								center: {
									lat: <?php echo esc_attr($lat); ?>,
									lng: <?php echo esc_attr($lon); ?>
								},
								zoom: <?php echo $map_zoom; ?>
							});
							marker = new google.maps.Marker({
								map: map,
								draggable: false,
								animation: google.maps.Animation.DROP,
								position: {
									lat: <?php echo esc_attr($lat); ?>,
									lng: <?php echo esc_attr($lon); ?>
								}
							});
							marker.addListener('click', toggleBounce);
						}

						function toggleBounce() {
							if (marker.getAnimation() !== null) {
								marker.setAnimation(null);
							} else {
								marker.setAnimation(google.maps.Animation.BOUNCE);
							}
						}
					</script>
					<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($user_api); ?>&callback=initMap" async defer></script>
			<?php }
			}
		}
		do_action('mep_event_after_google_map');
		$content = ob_get_clean();
		echo apply_filters('mage_event_google_map', $content, $event_id);
	}
}