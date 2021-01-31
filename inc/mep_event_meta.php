<?php
if (!defined('ABSPATH')) {
	die;
} // Cannot access pages directly.
class MP_Event_All_Info_In_One
{
	public function __construct()
	{
		add_action('add_meta_boxes', array($this, 'mp_event_all_info_in_tab'));
	}

	public function mp_event_all_info_in_tab()
	{
		$event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
		add_meta_box('mp_event_all_info_in_tab', __('<span class="dashicons dashicons-info"></span>' . $event_label . ' Information : ', 'mage-eventpress') . get_the_title(get_the_id()), array($this, 'mp_event_all_in_tab'), 'mep_events', 'normal', 'high');
		add_meta_box('mep-event-template', __('Template', 'mage-eventpress'), array($this, 'mep_event_template_meta_box_cb'), 'mep_events', 'side', 'low');
	}

	public function mp_event_all_in_tab()
	{
		$event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
		$post_id 			= get_the_id();
?>
		<div class="mp_event_all_meta_in_tab mp_event_tab_area">
			<div class="mp_tab_menu">
				<ul>
					<?php do_action('mep_admin_event_details_before_tab_name_location', $post_id); ?>
					<li data-target-tabs="#mp_event_venue">
						<span class="dashicons dashicons-location"></span>&nbsp;&nbsp;<?php _e('Venue/Location', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_after_tab_name_location', $post_id); ?>
					<li data-target-tabs="#mp_ticket_type_pricing">
						<span class="dashicons dashicons-buddicons-tracking"></span>&nbsp;&nbsp;<?php _e('Ticket Type and Pricing', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_before_tab_name_ticket_type', $post_id); ?>
					<li data-target-tabs="#mp_event_time">
						<span class="dashicons dashicons-calendar-alt"></span>&nbsp;&nbsp;<?php _e('Date & Time', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_before_tab_name_date_time', $post_id); ?>

					<li data-target-tabs="#mp_event_settings">
						<span class="dashicons dashicons-admin-generic"></span>&nbsp;&nbsp;<?php _e('Settings', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_before_tab_name_settings', $post_id); ?>
					<?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
						<li data-target-tabs="#mp_event_tax_settings">
							<span class="dashicons dashicons-admin-settings"></span>&nbsp;&nbsp;<?php _e('Tax', 'mage-eventpress'); ?>
						</li>
					<?php } ?>
					<?php do_action('mep_admin_event_details_before_tab_name_tax', $post_id); ?>
					<li data-target-tabs="#mp_event_rich_text">
						<span class="dashicons dashicons-admin-settings"></span>&nbsp;&nbsp;<?php _e('Rich text', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_before_tab_name_rich_text', $post_id); ?>
					<?php do_action('mp_event_all_in_tab_menu'); ?>
					<?php
					if (class_exists('MP_ESP_Admin')) {
					?>
						<li data-target-tabs="#mp_esp_seat_plan_setting">
							<span class="dashicons dashicons-admin-settings"></span>&nbsp;&nbsp;<?php _e('Seat Plan Settings', 'mage-eventpress'); ?>
						</li>
					<?php
					}
					?>
					<?php do_action('mep_admin_event_details_end_of_tab_name', $post_id); ?>
				</ul>
			</div>
			<div class="mp_tab_details">
				<?php do_action('mep_admin_event_details_before_tab_details_location', $post_id); ?>
				<div class="mp_tab_item active" data-tab-item="#mp_event_venue">
					<h3><?php echo $event_label;
						_e(' Location :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mp_event_venue($post_id); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_location', $post_id); ?>
				<div class="mp_tab_item" data-tab-item="#mp_ticket_type_pricing">
					<h3><?php _e('Ticket Type List :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mep_event_ticket_type($post_id); ?>
					<h3><?php _e('Extra service Area :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mep_event_extra_price_option($post_id); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_ticket_type', $post_id); ?>
				<div class="mp_tab_item" data-tab-item="#mp_event_time">
					<h3><?php echo $event_label;
						_e(' Date & TIme :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mep_event_date_meta_box_cb($post_id); ?>
					<?php do_action('mp_event_recurring_every_day_setting', $post_id); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_date_time', $post_id); ?>

				<div class="mp_tab_item" data-tab-item="#mp_event_rich_text">
					<h3><?php echo $event_label;
						_e(' Rich Texts for SEO & Google Schema Text :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mp_event_rich_text($post_id); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_rich_text', $post_id); ?>
				<div class="mp_tab_item" data-tab-item="#mp_event_settings">
					<h3><?php echo $event_label;
						_e(' Settings :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mp_event_settings($post_id); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_settings', $post_id); ?>
				<?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
					<div class="mp_tab_item" data-tab-item="#mp_event_tax_settings">

						<h3><?php echo $event_label;
							_e(' Tax Settings :', 'mage-eventpress'); ?></h3>
						<hr />
						<?php $this->mp_event_tax($post_id); ?>
					</div>
				<?php } ?>
				<?php do_action('mp_event_all_in_tab_item', $post_id); ?>
				<?php
				if (class_exists('MP_ESP_Admin')) {
				?>
					<div class="mp_tab_item" data-tab-item="#mp_esp_seat_plan_setting">
						<?php do_action('mp_event_all_in_tab_item_seat_plan', $post_id); ?>
					</div>
				<?php
				}
				do_action('mep_admin_event_details_end_of_tab_details', $post_id); ?>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(function($) {
				$( "#mp_event_all_info_in_tab" ).parent().removeClass('meta-box-sortables');
			});
		</script>
	<?php
	}

	public function mp_event_venue($post_id)
	{
		$event_label     = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
		$values          = get_post_custom($post_id);
		$user_api        = mep_get_option('google-map-api', 'general_setting_sec', '');
		$map_type        = mep_get_option('mep_google_map_type', 'general_setting_sec', 'iframe');
		$mep_org_address = array_key_exists('mep_org_address', $values) ? $values['mep_org_address'][0] : 0;
		$map_visible     = array_key_exists('mep_sgm', $values) ? $values['mep_sgm'][0] : 0;
	?>
		<table>
			<tr>
				<th><?php echo $event_label;
					_e(" Location Source:", "mage-eventpress"); ?></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="mep_org_address">
							<option value="0" <?php echo ($mep_org_address == 0) ? 'selected' : ''; ?>><?php echo $event_label;
																											_e(' Details', 'mage-eventpress'); ?></option>
							<option value="1" <?php echo ($mep_org_address == 1) ? 'selected' : ''; ?>><?php _e('Organizer', 'mage-eventpress'); ?></option>
						</select>
					</label>
					<p class="event_meta_help_txt">
						<?php _e('Select Organizer if you already save the organizer details. Please remember if you select orginizer and not checked the the organizer from the Event Organizer list from the right sidebar, Event Location section if the frontend will be blank.', 'mage-eventpress'); ?>
					</p>
				</td>
			</tr>
		</table>
		<div class="mp_event_address">
			<table>
				<tr>
					<th><?php _e('Location/Venue:', 'mage-eventpress'); ?></th>
					<td>
						<label>
							<input type="text" name='mep_location_venue' placeholder="Ex: New york Meeting Center" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_location_venue'); ?>'>
						</label>
					</td>
					<th><span><?php _e('Street:', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_street' placeholder="Ex: 10 E 33rd St" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_street'); ?>'>
						</label>
					</td>
				</tr>
				<tr>
					<th><span><?php _e('City: ', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_city' placeholder="Ex: New York" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_city'); ?>'>
						</label>
					</td>
					<th><span><?php _e('State: ', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_state' placeholder="Ex: NY" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_state'); ?>'>
						</label>
					</td>
				</tr>
				<tr>
					<th><span><?php _e('Postcode: ', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_postcode' placeholder="Ex: 10016" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_postcode'); ?>'>
						</label>
					</td>
					<th><span><?php _e('Country: ', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_country' placeholder="Ex: USA" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_country'); ?>'>
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="mp_form_area">
			<div class="mp_form_item">
				<label>
					<input type="checkbox" name='mep_sgm' value='1' <?php echo $map_visible > 0 ? 'checked' : ''; ?>>
					<span><?php _e('Show Google Map ', 'mage-eventpress'); ?></span>
				</label>
				<?php
				if ($map_type == 'iframe') {
				?>
					<div id="show_gmap">
						<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=<?php echo mep_get_event_locaion_item($post_id, 'mep_location_venue'); ?>&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
					</div>

					<?php }
				if ($map_type == 'api') {
					if ($user_api) {
					?>

						<div class='sec'>
							<input id="pac-input" name='location_name' value='<?php //echo $values['location_name'][0];
																				?>' />
						</div>


						<input type="hidden" class="form-control" required name="latitude" value="<?php if (array_key_exists('latitude', $values)) {
																										echo $values['latitude'][0];
																									} ?>">
						<input type="hidden" class="form-control" required name="longitude" value="<?php if (array_key_exists('longitude', $values)) {
																										echo $values['longitude'][0];
																									} ?>">
						<div id="map"></div>

					<?php
					} else {
						echo "<span class=mep_status><span class=err>No Google MAP API Key Found. Please enter API KEY <a href=" . get_site_url() . "/wp-admin/options-general.php?page=mep_event_settings_page>Here</a></span></span>";
					}


					if (array_key_exists('latitude', $values) && !empty($values['latitude'][0])) {
						$lat = $values['latitude'][0];
					} else {
						$lat = '37.0902';
					}


					if (array_key_exists('longitude', $values) && !empty($values['longitude'][0])) {
						$lon = $values['longitude'][0];
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
							});
						}

						google.maps.event.addDomListener(window, "load", initMap);
					</script>
				<?php
				}
				?>
			</div>
		</div>
		<script type="text/javascript">
			jQuery('[name="mep_org_address"]').change(function() {
				let source = jQuery(this).val();
				if (source === '1') {
					jQuery('.mp_event_address').slideUp(250);
					jQuery('[name="mep_location_venue"]').val('<?php echo mep_event_org_location_item($post_id, 'org_location'); ?>');
					jQuery('[name="mep_street"]').val('<?php echo mep_event_org_location_item($post_id, 'org_street'); ?>');
					jQuery('[name="mep_city"]').val('<?php echo mep_event_org_location_item($post_id, 'org_city'); ?>');
					jQuery('[name="mep_state"]').val('<?php echo mep_event_org_location_item($post_id, 'org_state'); ?>');
					jQuery('[name="mep_postcode"]').val('<?php echo mep_event_org_location_item($post_id, 'org_postcode'); ?>');
					jQuery('[name="mep_country"]').val('<?php echo mep_event_org_location_item($post_id, 'org_country'); ?>');
					let location = jQuery('[name="mep_location_venue"]').val();
					jQuery('#show_gmap').html('<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=' + location + '&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>')
				} else {
					jQuery('.mp_event_address').slideDown();
					jQuery('[name="mep_location_venue"]').val('<?php echo mep_event_location_item($post_id, 'mep_location_venue'); ?>');
					jQuery('[name="mep_street"]').val('<?php echo mep_event_location_item($post_id, 'mep_street'); ?>');
					jQuery('[name="mep_city"]').val('<?php echo mep_event_location_item($post_id, 'mep_city'); ?>');
					jQuery('[name="mep_state"]').val('<?php echo mep_event_location_item($post_id, 'mep_state'); ?>');
					jQuery('[name="mep_postcode"]').val('<?php echo mep_event_location_item($post_id, 'mep_postcode'); ?>');
					jQuery('[name="mep_country"]').val('<?php echo mep_event_location_item($post_id, 'mep_country'); ?>');
					let location = jQuery('[name="mep_location_venue"]').val();
					jQuery('#show_gmap').html('<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=' + location + '&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>')
				}
			})
			jQuery('[name="mep_location_venue"]').keypress(function() {
				let location = jQuery(this).val();
				if (location === '') {
					// alert('Please Enter Location First');
				} else {
					jQuery('#show_gmap').html('<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=' + location + '&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>')
				}
			})
		</script>
	<?php
	}

	public function mep_event_ticket_type($post_id)
	{
		$mep_event_ticket_type = get_post_meta($post_id, 'mep_event_ticket_type', true);
		wp_nonce_field('mep_event_ticket_type_nonce', 'mep_event_ticket_type_nonce');
	?>
		<table id="repeatable-fieldset-one-t">
			<thead>
				<tr>
					<th><?php _e('Ticket Type Name', 'mage-eventpress'); ?></th>
					<th><?php _e('Ticket Price', 'mage-eventpress'); ?></th>
					<th><?php _e('Available Qty', 'mage-eventpress'); ?></th>
					<th><?php _e('Default Qty', 'mage-eventpress'); ?></th>
					<?php echo $rsvqty = '<th>' . esc_html__("Reserve Qty", "mage-eventpress") . '</th>';					
					apply_filters('mep_add_extra_column', $rsvqty); ?>
					<th style='width:220px'><?php _e('Sale End Date', 'mage-eventpress'); ?></th>					
					<th><?php _e('Qty Box Type', 'mage-eventpress'); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody class="mp_event_type_sortable">
				<?php

				if ($mep_event_ticket_type) :
					$count = 0;
					foreach ($mep_event_ticket_type as $field) {
						$qty_t_type  = esc_attr($field['option_qty_t_type']);
						$count++;
				?>
						<tr>
							<td><input type="text" class="mp_formControl" name="option_name_t[]" placeholder="Ex: Adult" value="<?php if ($field['option_name_t'] != '') {
																																	echo esc_attr($field['option_name_t']);
																																} ?>" /></td>

							<td><input type="number" size="4" pattern="[0-9]*" step="0.001" class="mp_formControl" name="option_price_t[]" placeholder="Ex: 10" value="<?php if (array_key_exists('option_price_t', $field) && $field['option_price_t'] != '') {
																																											echo esc_attr($field['option_price_t']);
																																										} else {
																																											echo '';
																																										} ?>" /></td>

							<td><input type="number" size="4" pattern="[0-9]*" step="1" class="mp_formControl" name="option_qty_t[]" placeholder="Ex: 500" value="<?php if (isset($field['option_qty_t'])) {
																																										echo $field['option_qty_t'];
																																									} else {
																																										echo 0;
																																									} ?>" /></td>
							<td><input type="number" size="2" pattern="[0-9]*" step="1" class="mp_formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value="<?php if (isset($field['option_default_qty_t'])) {
																																											echo $field['option_default_qty_t'];
																																										} else {
																																											echo 0;
																																										} ?>" /></td>

							<td><input type="number" class="mp_formControl" name="option_rsv_t[]" placeholder="Ex: 5" value="<?php if (isset($field['option_rsv_t'])) {
																																	echo $field['option_rsv_t'];
																																} else {
																																	echo 0;
																																} ?>" /></td>

							<?php do_action('mep_add_extra_input_box', $field) ?>
							<td><input style='width:220px;' type="datetime-local" id="ticket_sale_start" value='<?php if ($field['option_sale_end_date_t'] != '') {
																																	echo esc_attr($field['option_sale_end_date_t']);
																																} ?>' name="option_sale_end_date_t[]"></td>
							<td>
								<select name="option_qty_t_type[]" class='mp_formControl'>
									<option value="inputbox" <?php if ($qty_t_type == 'inputbox') {
																	echo "Selected";
																} ?>><?php _e('Input Box', 'mage-eventpress'); ?></option>
									<option value="dropdown" <?php if ($qty_t_type == 'dropdown') {
																	echo "Selected";
																} ?>><?php _e('Dropdown List', 'mage-eventpress'); ?></option>
								</select>
							</td>
																								
							<td>
								<div class="mp_event_remove_move">
									<button class="button remove-row-t" type="button"><span class="dashicons dashicons-trash" style="margin-top: 3px;color: red;"></span></button>
									<div class="mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
								</div>
							</td>
						</tr>
				<?php
					}
				else :
				// show a blank one
				endif;
				?>

				<!-- empty hidden one for jQuery -->
				<tr class="empty-row-t screen-reader-text">
					<td><input type="text" class="mp_formControl" name="option_name_t[]" placeholder="Ex: Adult" /></td>
					<td><input type="number" size="4" pattern="[0-9]*" class="mp_formControl" step="0.001" name="option_price_t[]" placeholder="Ex: 10" value="" /></td>
					<td><input type="number" size="4" pattern="[0-9]*" step="1" class="mp_formControl" name="option_qty_t[]" placeholder="Ex: 15" value="" /></td>
					<td><input type="number" size="2" pattern="[0-9]*" class="mp_formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value="" /></td>
					<?php echo $option_rsv_t = '<td><input type="number" class="mp_formControl" name="option_rsv_t[]" placeholder="Ex: 5" value=""/></td>' ?>
					<?php apply_filters('mep_add_field_to_ticket_type', $option_rsv_t); ?>
					<td>
						<select name="option_qty_t_type[]" class='mp_formControl'>
							<option value=''><?php _e('Please Select', 'mage-eventpress'); ?></option>
							<option value="inputbox"><?php _e('Input Box', 'mage-eventpress'); ?></option>
							<option value="dropdown"><?php _e('Dropdown List', 'mage-eventpress'); ?></option>
						</select></td>
					<td>
						<button class="button remove-row-t" type="button"><span class="dashicons dashicons-trash" style="margin-top: 3px;color: red;"></span></button>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<button id="add-row-t" class="button" style="background:green; color:white;"><span class="dashicons dashicons-plus-alt" style="margin-top: 3px;color: white;"></span><?php _e('Add New Ticket Type', 'mage-eventpress'); ?></button>
		</p>
	<?php
	}

	public function mep_event_extra_price_option($post_id)
	{
		$mep_events_extra_prices = get_post_meta($post_id, 'mep_events_extra_prices', true);
		wp_nonce_field('mep_events_extra_price_nonce', 'mep_events_extra_price_nonce');
	?>
		<p class="event_meta_help_txt"><?php _e('Extra Service as Product that you can sell and it is not included on event package', 'mage-eventpress'); ?></p>

		<table id="repeatable-fieldset-one" width="100%">
			<thead>
				<tr>
					<th><?php _e('Extra Service Name', 'mage-eventpress'); ?></th>
					<th><?php _e('Service Price', 'mage-eventpress'); ?></th>
					<th><?php _e('Available Qty', 'mage-eventpress'); ?></th>
					<th><?php _e('Qty Box Type', 'mage-eventpress'); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody class="mp_event_type_sortable">
				<?php

				if ($mep_events_extra_prices) :

					foreach ($mep_events_extra_prices as $field) {
						$qty_type = esc_attr($field['option_qty_type']);
				?>
						<tr>
							<td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap" value="<?php if ($field['option_name'] != '') {
																																echo esc_attr($field['option_name']);
																															} ?>" /></td>

							<td><input type="number" step="0.001" class="mp_formControl" name="option_price[]" placeholder="Ex: 10" value="<?php if ($field['option_price'] != '') {
																																				echo esc_attr($field['option_price']);
																																			} else {
																																				echo '';
																																			} ?>" /></td>

							<td><input type="number" class="mp_formControl" name="option_qty[]" placeholder="Ex: 100" value="<?php if ($field['option_qty'] != '') {
																																	echo esc_attr($field['option_qty']);
																																} else {
																																	echo '';
																																} ?>" /></td>

							<td align="center">
								<select name="option_qty_type[]" class='mp_formControl'>
									<option value="inputbox" <?php if ($qty_type == 'inputbox') {
																	echo "Selected";
																} ?>><?php _e('Input Box', 'mage-eventpress'); ?></option>
									<option value="dropdown" <?php if ($qty_type == 'dropdown') {
																	echo "Selected";
																} ?>><?php _e('Dropdown List', 'mage-eventpress'); ?></option>
								</select>
							</td>
							<td>
								<div class="mp_event_remove_move">
									<button class="button remove-row" type="button"><span class="dashicons dashicons-trash" style="margin-top: 3px;color: red;"></span></button>
									<div class="mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
								</div>
							</td>
						</tr>
				<?php
					}
				else :
				// show a blank one
				endif;
				?>

				<!-- empty hidden one for jQuery -->
				<tr class="empty-row screen-reader-text">
					<td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap" /></td>
					<td><input type="number" class="mp_formControl" step="0.001" name="option_price[]" placeholder="Ex: 10" value="" /></td>
					<td><input type="number" class="mp_formControl" name="option_qty[]" placeholder="Ex: 100" value="" /></td>

					<td><select name="option_qty_type[]" class='mp_formControl'>
							<option value=""><?php _e('Please Select Type', 'mage-eventpress'); ?></option>
							<option value="inputbox"><?php _e('Input Box', 'mage-eventpress'); ?></option>
							<option value="dropdown"><?php _e('Dropdown List', 'mage-eventpress'); ?></option>
						</select></td>
					<td>
						<button class="button remove-row"><span class="dashicons dashicons-trash" style="margin-top: 3px;color: red;"></span><?php _e('Remove', 'mage-eventpress'); ?></button>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<button id="add-row" class="button" style="background:green; color:white;"><span class="dashicons dashicons-plus-alt" style="margin-top: 3px;color: white;"></span><?php _e('Add Extra Price', 'mage-eventpress'); ?></button>
		</p>
	<?php
	}

	public function mep_event_date_meta_box_cb($post_id)
	{
		$values = get_post_custom($post_id);
	?>
		<div class="sec">
			<table id="repeatable-fieldset-one-d" width="100%">
				<thead>
					<th><?php _e('Start Date', 'mage-eventpress'); ?></th>
					<th><?php _e('Start Time', 'mage-eventpress'); ?></th>
					<th><?php _e('End Date', 'mage-eventpress'); ?></th>
					<th><?php _e('End Time', 'mage-eventpress'); ?></th>
					<th><?php _e('Action', 'mage-eventpress'); ?></th>
				</thead>
				<tbody class="mp_event_type_sortable">
					<tr>
						<td>
							<input type="date" class="mp_formControl" name="event_start_date" placeholder="Start Date" value="<?php if (array_key_exists('event_start_date', $values)) {
																																	echo $values['event_start_date'][0];
																																} ?>" />
						</td>
						<td>
							<input type="time" class="mp_formControl" name="event_start_time" placeholder="Start Time" value="<?php if (array_key_exists('event_start_time', $values)) {
																																	echo $values['event_start_time'][0];
																																} ?>" />
						</td>
						<td>
							<input type="date" class="mp_formControl" name="event_end_date" placeholder="End Date" value="<?php if (array_key_exists('event_end_date', $values)) {
																																echo $values['event_end_date'][0];
																															} ?>" />
						</td>
						<td>
							<input type="time" class="mp_formControl" name="event_end_time" placeholder="End Time" value="<?php if (array_key_exists('event_end_time', $values)) {
																																echo date('H:i', strtotime($values['event_end_time'][0]));
																															} ?>" />
						</td>
						<td>
						</td>
					</tr>
					<?php
					$mep_event_multi_date = get_post_meta($post_id, 'mep_event_more_date', true);
					if ($mep_event_multi_date) :
					?>

						<?php
						foreach ($mep_event_multi_date as $field) {
						?>
							<tr>
								<td>
									<input type="date" class="mp_formControl" name="event_more_start_date[]" placeholder="Start Date" value="<?php if ($field['event_more_start_date'] != '') {
																																					echo date('Y-m-d', strtotime($field['event_more_start_date']));
																																				} ?>" />
								</td>
								<td>
									<input type="time" class="mp_formControl" name="event_more_start_time[]" placeholder="Start Time" value="<?php if ($field['event_more_start_time'] != '') {
																																					echo date('H:i', strtotime($field['event_more_start_time']));
																																				} ?>" />
								</td>
								<td>
									<input type="date" class="mp_formControl" name="event_more_end_date[]" placeholder="End Date" value="<?php if ($field['event_more_end_date'] != '') {
																																				echo date('Y-m-d', strtotime($field['event_more_end_date']));
																																			} ?>" />
								</td>
								<td>

									<input type="time" class="mp_formControl" name="event_more_end_time[]" placeholder="End Time" value="<?php if ($field['event_more_end_time'] != '') {
																																				echo date('H:i', strtotime($field['event_more_end_time']));
																																			} ?>" />
								</td>
								<td>
									<div class="mp_event_remove_move">
										<button class="button remove-row-d" type="button"><span class="dashicons dashicons-trash" style="margin-top: 3px;color: red;"></span></button>
										<div class="mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
									</div>
								</td>
							</tr>
					<?php
						}
					else :
					endif;
					?>
					<tr class="empty-row-d screen-reader-text">

						<td>
							<input type="date" class="mp_formControl" name="event_more_start_date[]" placeholder="Start Date" value="" />
						</td>
						<td>
							<input type="time" class="mp_formControl" name="event_more_start_time[]" placeholder="Start Time" value="" />
						</td>
						<td>
							<input type="date" class="mp_formControl" name="event_more_end_date[]" placeholder="End Date" value="" />
						</td>
						<td>
							<input type="time" class="mp_formControl" name="event_more_end_time[]" placeholder="End Time" value="" />
						</td>
						<td>
							<button class="button remove-row-d"><span class="dashicons dashicons-trash" style="margin-top: 3px;color: red;"></span><?php _e('Remove', 'mage-eventpress'); ?></button>
						</td>
					</tr>
				</tbody>
			</table>

			<button id="add-new-date-row" class="button" style="background:green; color:white;"><span class="dashicons dashicons-plus-alt" style="margin-top: 3px;color: white;"></span><?php _e('Add More Date', 'mage-eventpress'); ?></button>
		</div>

	<?php
		do_action('mep_after_date_section', $post_id);
	}




	public function mp_event_rich_text($post_id)
	{
		wp_nonce_field('mep_event_ricn_text_nonce', 'mep_event_ricn_text_nonce');
		$event_start_date     = get_post_meta($post_id, 'event_start_datetime', true) ? get_post_meta($post_id, 'event_start_datetime', true) : '';
		$event_end_date       = get_post_meta($post_id, 'event_end_datetime', true) ? get_post_meta($post_id, 'event_end_datetime', true) : '';
		$event_rt_status      = get_post_meta($post_id, 'mep_rt_event_status', true) ? get_post_meta($post_id, 'mep_rt_event_status', true) : '';
		$event_rt_atdnce_mode = get_post_meta($post_id, 'mep_rt_event_attandence_mode', true) ? get_post_meta($post_id, 'mep_rt_event_attandence_mode', true) : '';
		$event_rt_prv_date    = get_post_meta($post_id, 'mep_rt_event_prvdate', true) ? get_post_meta($post_id, 'mep_rt_event_prvdate', true) : $event_start_date;
		$rt_status    		  = get_post_meta($post_id, 'mep_rich_text_status', true) ? get_post_meta($post_id, 'mep_rich_text_status', true) : 'enable';
	?>
<div class='mep_rich_text_status_section'>
<label for='mep_rich_text_status'>
<?php _e('Rich Text Status','mage-eventpress'); ?>
	<select id='mep_rich_text_status' name='mep_rich_text_status'>
			<option value='enable' <?php if($rt_status == 'enable'){ echo 'Selected'; } ?>><?php _e('Enable','mage-eventpress'); ?></option>
			<option value='disable' <?php if($rt_status == 'disable'){ echo 'Selected'; } ?>><?php _e('Disable','mage-eventpress'); ?></option>
	</select>
</label>
</div>
		<table id='mep_rich_text_table' <?php if($rt_status == 'disable'){ ?> style='display:none;' <?php } ?>>
			<tr>
				<th><span><?php _e('Type :', 'mage-eventpress'); ?></span></th>
				<td colspan="3"><?php _e('Event', 'mage-eventpress'); ?></td>
			</tr>
			<tr>
				<th><span><?php _e('Name :', 'mage-eventpress'); ?></span></th>
				<td colspan="3"><?php echo get_the_title($post_id); ?></td>
			</tr>
			<tr>
				<th><span><?php _e('Start Date :', 'mage-eventpress'); ?></span></th>
				<td colspan="3"><?php echo $event_start_date ? get_mep_datetime($event_start_date, 'date-time-text') : ''; ?></td>
			</tr>
			<tr>
				<th><span><?php _e('End Date :', 'mage-eventpress'); ?></span></th>
				<td colspan="3"><?php echo $event_end_date ? get_mep_datetime($event_end_date, 'date-time-text') : ''; ?></td>
			</tr>
			<tr>
				<th><span><?php _e('Event Status:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="mep_rt_event_status">
							<option value="EventRescheduled" <?php echo ($event_rt_status == 'EventMovedOnline') ? 'selected' : ''; ?>><?php _e('Event Rescheduled', 'mage-eventpress'); ?></option>
							<option value="EventMovedOnline" <?php echo ($event_rt_status == 'EventMovedOnline') ? 'selected' : ''; ?>><?php _e('Event Moved Online', 'mage-eventpress'); ?></option>
							<option value="EventPostponed" <?php echo ($event_rt_status == 'EventPostponed') ? 'selected' : ''; ?>><?php _e('Event Postponed', 'mage-eventpress'); ?></option>
							<option value="EventCancelled" <?php echo ($event_rt_status == 'EventCancelled') ? 'selected' : ''; ?>><?php _e('Event Cancelled', 'mage-eventpress'); ?></option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<th><span><?php _e('Event Attendance Mode:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="mep_rt_event_attandence_mode">
							<option value="OfflineEventAttendanceMode" <?php echo ($event_rt_atdnce_mode == 'OfflineEventAttendanceMode') ? 'selected' : ''; ?>><?php _e('OfflineEventAttendanceMode', 'mage-eventpress'); ?></option>
							<option value="OnlineEventAttendanceMode" <?php echo ($event_rt_atdnce_mode == 'OnlineEventAttendanceMode') ? 'selected' : ''; ?>><?php _e('OnlineEventAttendanceMode', 'mage-eventpress'); ?></option>
							<option value="MixedEventAttendanceMode" <?php echo ($event_rt_atdnce_mode == 'MixedEventAttendanceMode') ? 'selected' : ''; ?>><?php _e('MixedEventAttendanceMode', 'mage-eventpress'); ?></option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<th><span><?php _e('Previous Start Date:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<input type='text' class="mp_formControl" name="mep_rt_event_prvdate" value='<?php echo $event_rt_prv_date; ?>' />
					</label>
				</td>
			</tr>
			<tr>
				<td colspan="4">
					<?php
					if ($post_id) {
					?>
						<p class="event_meta_help_txt">
							<a href='https://search.google.com/test/rich-results?utm_campaign=devsite&utm_medium=jsonld&utm_source=event&url=<?php echo get_the_permalink($post_id); ?>&user_agent=2' target="_blank"><?php _e('Check Rich Text Status', 'mage-eventpress'); ?></a>
						</p>
					<?php
					}
					?>
				</td>
			</tr>
		</table>
<script>
 	jQuery('[name="mep_rich_text_status"]').change(function() {
		var rich_status = jQuery(this).val() ? jQuery(this).val() : 'enable';
		if(rich_status == 'enable'){
			// mep_rich_text_table
			jQuery('#mep_rich_text_table').show(500);
		}
		else if(rich_status == 'disable'){
			jQuery('#mep_rich_text_table').hide(500);
		}
	 });
</script>




	<?php
	}

	public function mp_event_settings($post_id)
	{
		$event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
	?>
		<table>
			<?php
			$this->mp_event_reg_status($post_id);
			$this->mp_event_available_seat_status($post_id);
			$this->mp_event_reset_booking_count($post_id);
			do_action('mp_event_switching_button_hook', $post_id);
			$this->mp_event_speaker_ticket_type($post_id);
			?>
		</table>
	<?php
	}

	public function mp_event_reg_status($post_id)
	{
		$values = get_post_custom($post_id);
		wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
		$reg_checked = '';
		if (array_key_exists('mep_reg_status', $values)) {
			if ($values['mep_reg_status'][0] == 'on') {
				$reg_checked = 'checked';
			}
		} else {
			$reg_checked = 'checked';
		}
	?>
		<tr>
			<th><span><?php _e('Registration On/Off:', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label>

					<input class="mp_opacity_zero" type="checkbox" name="mep_reg_status" <?php echo $reg_checked; ?> />
					<span class="slider round"></span>
				</label>
			</td>
		</tr>
	<?php
	}

	public function mp_event_available_seat_status($post_id)
	{
		$values = get_post_custom($post_id);
		wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
		$seat_checked = '';
		if (array_key_exists('mep_available_seat', $values)) {
			if ($values['mep_available_seat'][0] == 'on') {
				$seat_checked = 'checked';
			}
		} else {
			$seat_checked = 'checked';
		}
	?>
		<tr>
			<th><span><?php _e('Show Available Seat?', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label>
					<input class="mp_opacity_zero" type="checkbox" name="mep_available_seat" <?php echo $seat_checked; ?> />
					<span class="slider round"></span>
				</label>
			</td>
		</tr>
	<?php
	}

	public function mp_event_reset_booking_count($post_id)
	{
		wp_nonce_field('mep_event_reset_btn_nonce', 'mep_event_reset_btn_nonce');
	?>
		<tr>
			<th><span><?php _e('Reset Booking Count :', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label>
					<input class="mp_opacity_zero" type="checkbox" name="mep_reset_status" class="switch_checkbox" />
					<span class="slider round"></span>
					<span style="padding: 0 0 0 60px;"><?php _e('Current Booking Status :', 'mage-eventpress'); ?></span>
					<span><?php mep_get_event_total_seat($post_id); ?></span>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<p class="event_meta_help_txt">
					<?php _e('If you reset this count, All booking information will be removed including attendee list & its impossible to undo', 'mage-eventpress'); ?>
				</p>
			</td>
		</tr>
	<?php
	}

	public function mp_event_speaker_ticket_type($post_id)
	{
		$event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
		$event_type = get_post_meta($post_id, 'mep_event_type', true);
		$description = get_post_meta($post_id, 'mp_event_virtual_type_des', true);
		$checked = ($event_type == 'online') ? 'checked' : '';
	?>
		<tr>
			<th><span><?php echo $event_label;
						_e(' Is Virtual?', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label class="mp_event_virtual_type_des_switch">
					<input class="mp_opacity_zero" type="checkbox" name="mep_event_type" <?php echo $checked; ?> />
					<span class="slider round"></span>
				</label>
				<p></p>
				<label class="mp_event_virtual_type_des <?php echo ($event_type == 'online') ? 'active' : ''; ?>">
					<textarea type="text" name="mp_event_virtual_type_des" placeholder="Description"><?php echo $description; ?></textarea>
					<p class="event_meta_help_txt"><?php _e('Please Enter Your Virtual event joining details Information. these information will send to buyer with confirmation email.', 'mage-eventpress') ?></p>
				</label>
			</td>
		</tr>
	<?php
	}

	public function mp_event_tax($post_id)
	{
		$values = get_post_custom($post_id);
		wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
		if (array_key_exists('_tax_status', $values)) {
			$tx_status = $values['_tax_status'][0];
		} else {
			$tx_status = '';
		}

		if (array_key_exists('_tax_class', $values)) {
			$tx_class = $values['_tax_class'][0];
		} else {
			$tx_class = '';
		}
	?>
		<table>
			<tr>
				<th><span><?php _e('Tax status:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="_tax_status">
							<option value="taxable" <?php echo ($tx_status == 'taxable') ? 'selected' : ''; ?>><?php _e('Taxable', 'mage-eventpress'); ?></option>
							<option value="shipping" <?php echo ($tx_status == 'shipping') ? 'selected' : ''; ?>><?php _e('Shipping only', 'mage-eventpress'); ?></option>
							<option value="none" <?php echo ($tx_status == 'none') ? 'selected' : ''; ?>><?php _e('None', 'mage-eventpress'); ?></option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<th><span><?php _e('Tax class:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="_tax_class">
							<option value="standard" <?php echo ($tx_class == 'standard') ? 'selected' : ''; ?>><?php _e('Standard', 'mage-eventpress'); ?></option>
							<?php mep_get_all_tax_list(); ?>
						</select>
					</label>
					<p class="event_meta_help_txt">
						<?php _e('To add any new tax class , Please go to WooCommerce ->Settings->Tax Area', 'mage-eventpress'); ?>
					</p>
				</td>
			</tr>
		</table>
	<?php
	}

	//side meta box
	public function mep_event_template_meta_box_cb($post)
	{
		$values          = get_post_custom($post->ID);
		$global_template = mep_get_option('mep_global_single_template', 'general_setting_sec', 'theme-2');
		if (array_key_exists('mep_event_template', $values)) {
			$current_template = $values['mep_event_template'][0];
		} else {
			$current_template = '';
		}
		if ($current_template) {
			$_current_template = $current_template;
		} else {
			$_current_template = $global_template;
		}
	?>
		<div class='sec'>
			<span><?php event_single_template_list($_current_template); ?></span>
		</div>
<?php
	}
}

new MP_Event_All_Info_In_One();







add_action('save_post', 'mep_events_ticket_type_save');
function mep_events_ticket_type_save($post_id)
{
	global $wpdb;

	if (
		!isset($_POST['mep_event_ticket_type_nonce']) ||
		!wp_verify_nonce($_POST['mep_event_ticket_type_nonce'], 'mep_event_ticket_type_nonce')
	) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}


	if (get_post_type($post_id) == 'mep_events') {

		$old = get_post_meta($post_id, 'mep_event_ticket_type', true) ? get_post_meta($post_id, 'mep_event_ticket_type', true) : array();


		$new          = array();
		$names        = $_POST['option_name_t'] ? $_POST['option_name_t'] : array();
		$ticket_price = $_POST['option_price_t'] ? $_POST['option_price_t'] : array();
		$qty          = $_POST['option_qty_t'] ? $_POST['option_qty_t'] : array();
		$dflt_qty     = $_POST['option_default_qty_t'] ? $_POST['option_default_qty_t'] : array();
		$rsv          = $_POST['option_rsv_t'] ? $_POST['option_rsv_t'] : array();
		$qty_type     = $_POST['option_qty_t_type'] ? $_POST['option_qty_t_type'] : array();
		$sale_end     = $_POST['option_sale_end_date_t'] ? $_POST['option_sale_end_date_t'] : array();

		$count = count($names);

		for ($i = 0; $i < $count; $i++) {

			if ($names[$i] != '') :
				$new[$i]['option_name_t'] = stripslashes(strip_tags($names[$i]));
			endif;

			if ($ticket_price[$i] != '') :
				$new[$i]['option_price_t'] = stripslashes(strip_tags($ticket_price[$i]));
			endif;

			if ($qty[$i] != '') :
				$new[$i]['option_qty_t'] = stripslashes(strip_tags($qty[$i]));
			endif;

			if ($rsv[$i] != '') :
				$new[$i]['option_rsv_t'] = stripslashes(strip_tags($rsv[$i]));
			endif;

			if ($dflt_qty[$i] != '') :
				$new[$i]['option_default_qty_t'] = stripslashes(strip_tags($dflt_qty[$i]));
			endif;

			if ($qty_type[$i] != '') :
				$new[$i]['option_qty_t_type'] = stripslashes(strip_tags($qty_type[$i]));
			endif;

			if ($sale_end[$i] != '') :
				$new[$i]['option_sale_end_date_t'] = stripslashes(strip_tags($sale_end[$i]));
			endif;

		}

		$ticket_type_list = apply_filters('mep_ticket_type_arr_save', $new);

		if (!empty($ticket_type_list) && $ticket_type_list != $old) {
			update_post_meta($post_id, 'mep_event_ticket_type', $ticket_type_list);
		} elseif (empty($ticket_type_list) && $old) {
			delete_post_meta($post_id, 'mep_event_ticket_type', $old);
		}
	}
}

add_action('save_post', 'mep_events_repeatable_meta_box_save');
function mep_events_repeatable_meta_box_save($post_id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'event_extra_options';
	if (
		!isset($_POST['mep_events_extra_price_nonce']) ||
		!wp_verify_nonce($_POST['mep_events_extra_price_nonce'], 'mep_events_extra_price_nonce')
	) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	if (get_post_type($post_id) == 'mep_events') {


		$old 	  = get_post_meta($post_id, 'mep_events_extra_prices', true);
		$new 	  = array();
		$names 	  = $_POST['option_name'];
		$urls     = $_POST['option_price'];
		$qty      = $_POST['option_qty'];
		$qty_type = $_POST['option_qty_type'];
		$order_id = 0;
		$count    = count($names);

		for ($i = 0; $i < $count; $i++) {
			if ($names[$i] != '') :
				$new[$i]['option_name'] = stripslashes(strip_tags($names[$i]));
			endif;

			if ($urls[$i] != '') :
				$new[$i]['option_price'] = stripslashes(strip_tags($urls[$i]));
			endif;

			if ($qty[$i] != '') :
				$new[$i]['option_qty'] = stripslashes(strip_tags($qty[$i]));
			endif;

			if ($qty_type[$i] != '') :
				$new[$i]['option_qty_type'] = stripslashes(strip_tags($qty_type[$i]));
			endif;
		}

		if (!empty($new) && $new != $old) {
			update_post_meta($post_id, 'mep_events_extra_prices', $new);
		} elseif (empty($new) && $old) {
			delete_post_meta($post_id, 'mep_events_extra_prices', $old);
		}
	}
}



/**
 * Now Saving the Event Meta Field Data
 */
add_action('save_post', 'mep_event_meta_save');
function mep_event_meta_save($post_id)
{

	if (!isset($_POST['mep_event_ricn_text_nonce']) || !wp_verify_nonce($_POST['mep_event_ricn_text_nonce'], 'mep_event_ricn_text_nonce')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	/**
	 * If the saving post is event then go on
	 */
	if (get_post_type($post_id) == 'mep_events') {

		$pid 			 = $post_id;
		$oldm 			 = get_post_meta($post_id, 'mep_event_more_date', true);
		$more_start_date = isset($_POST['event_more_start_date']) ? $_POST['event_more_start_date'] : array();
		$more_start_time = isset($_POST['event_more_start_time']) ? $_POST['event_more_start_time'] : '';
		$more_end_date   = isset($_POST['event_more_end_date']) ? $_POST['event_more_end_date'] : '';
		$more_end_time   = isset($_POST['event_more_end_time']) ? $_POST['event_more_end_time'] : '';
		$mdate           = [];

		$mcount = count($more_start_date);

		for ($m = 0; $m < $mcount; $m++) {
			if ($more_start_date[$m] != '') :
				$mdate[$m]['event_more_start_date'] = stripslashes(strip_tags($more_start_date[$m]));
				$mdate[$m]['event_more_start_time'] = stripslashes(strip_tags($more_start_time[$m]));
				$mdate[$m]['event_more_end_date']   = stripslashes(strip_tags($more_end_date[$m]));
				$mdate[$m]['event_more_end_time']   = stripslashes(strip_tags($more_end_time[$m]));
			endif;
		}


		$event_rt_status      = $_POST['mep_rt_event_status'];
		$event_rt_atdnce_mode = $_POST['mep_rt_event_attandence_mode'];
		$event_rt_prv_date    = $_POST['mep_rt_event_prvdate'];

		$seat               = 0;
		$rsvs               = 0;
		$mep_location_venue = isset($_POST['mep_location_venue']) ? strip_tags($_POST['mep_location_venue']) : "";
		$mep_street         = isset($_POST['mep_street']) ? strip_tags($_POST['mep_street']) : "";
		$mep_city           = isset($_POST['mep_city']) ? strip_tags($_POST['mep_city']) : "";
		$mep_state          = isset($_POST['mep_state']) ? strip_tags($_POST['mep_state']) : "";
		$mep_postcode       = isset($_POST['mep_postcode']) ? strip_tags($_POST['mep_postcode']) : "";
		$mep_country        = isset($_POST['mep_country']) ? strip_tags($_POST['mep_country']) : "";

		$mep_sgm         = isset($_POST['mep_sgm']) ? strip_tags($_POST['mep_sgm']) : "";
		$mep_org_address = isset($_POST['mep_org_address']) ? strip_tags($_POST['mep_org_address']) : "";
		$_price          = isset($_POST['_price']) ? strip_tags($_POST['_price']) : "";

		$event_start_date = strip_tags($_POST['event_start_date']);
		$event_start_time = strip_tags($_POST['event_start_time']);
		$event_end_date   = strip_tags($_POST['event_end_date']);
		$event_end_time   = strip_tags($_POST['event_end_time']);

		$latitude      = isset($_POST['latitude']) ? strip_tags($_POST['latitude']) : "";
		$longitude     = isset($_POST['latitude']) ? strip_tags($_POST['longitude']) : "";
		$location_name 			= isset($_POST['location_name']) ? strip_tags($_POST['location_name']) : "";

		$mep_full_name           = isset($_POST['mep_full_name']) ? strip_tags($_POST['mep_full_name']) : "";
		$mep_reg_email           = isset($_POST['mep_reg_email']) ? strip_tags($_POST['mep_reg_email']) : "";
		$mep_reg_phone           = isset($_POST['mep_reg_phone']) ? strip_tags($_POST['mep_reg_phone']) : "";
		$mep_reg_address         = isset($_POST['mep_reg_address']) ? strip_tags($_POST['mep_reg_address']) : "";
		$mep_reg_designation     = isset($_POST['mep_reg_designation']) ? strip_tags($_POST['mep_reg_designation']) : "";
		$mep_reg_website         = isset($_POST['mep_reg_website']) ? strip_tags($_POST['mep_reg_website']) : "";
		$mep_reg_veg             = isset($_POST['mep_reg_veg']) ? strip_tags($_POST['mep_reg_veg']) : "";
		$mep_reg_company         = isset($_POST['mep_reg_company']) ? strip_tags($_POST['mep_reg_company']) : "";
		$mep_reg_gender          = isset($_POST['mep_reg_gender']) ? strip_tags($_POST['mep_reg_gender']) : "";
		$mep_reg_tshirtsize      = isset($_POST['mep_reg_tshirtsize']) ? strip_tags($_POST['mep_reg_tshirtsize']) : "";
		$mep_reg_tshirtsize_list = isset($_POST['mep_reg_tshirtsize_list']) ? strip_tags($_POST['mep_reg_tshirtsize_list']) : "";
		$mep_event_template      = isset($_POST['mep_event_template']) ? strip_tags($_POST['mep_event_template']) : "";


		$event_start_datetime  	= date('Y-m-d H:i:s', strtotime($event_start_date . ' ' . $event_start_time));
		$event_end_datetime    	= date('Y-m-d H:i:s', strtotime($event_end_date . ' ' . $event_end_time));
		$md                    	= sizeof($mdate) > 0 ? end($mdate) : array();
		$event_expire_datetime 	= sizeof($md) > 0 ? date('Y-m-d H:i:s', strtotime($md['event_more_end_date'] . ' ' . $md['event_more_end_time'])) : $event_end_datetime;



		$mep_reg_status 		= isset($_POST['mep_reg_status']) ? strip_tags($_POST['mep_reg_status']) : 'off';
		$mep_reset_status 		= isset($_POST['mep_reset_status']) ? strip_tags($_POST['mep_reset_status']) : 'off';
		$mep_available_seat 	= isset($_POST['mep_available_seat']) ? strip_tags($_POST['mep_available_seat']) : 'off';
		$_tax_status 			= isset($_POST['_tax_status']) ? strip_tags($_POST['_tax_status']) : 'none';
		$_tax_class 			= isset($_POST['_tax_class']) ? strip_tags($_POST['_tax_class']) : '';
		$mep_rich_text_status 	= isset($_POST['mep_rich_text_status']) ? strip_tags($_POST['mep_rich_text_status']) : 'enable';

		if ($mep_reset_status == 'on') {
			mep_reset_event_booking($post_id);
		}

		update_post_meta($post_id, 'mep_rich_text_status', $mep_rich_text_status);
		update_post_meta($post_id, 'mep_available_seat', $mep_available_seat);
		update_post_meta($post_id, 'mep_reg_status', $mep_reg_status);
		update_post_meta($post_id, '_tax_status', $_tax_status);
		update_post_meta($post_id, '_tax_class', $_tax_class);


		update_post_meta($post_id, 'mep_rt_event_status', $event_rt_status);
		update_post_meta($post_id, 'mep_rt_event_attandence_mode', $event_rt_atdnce_mode);
		update_post_meta($post_id, 'mep_rt_event_prvdate', $event_rt_prv_date);

		update_post_meta($pid, 'mep_full_name', $mep_full_name);
		update_post_meta($pid, 'mep_reg_email', $mep_reg_email);
		update_post_meta($pid, 'mep_reg_phone', $mep_reg_phone);
		update_post_meta($pid, 'mep_reg_address', $mep_reg_address);
		update_post_meta($pid, 'mep_reg_designation', $mep_reg_designation);
		update_post_meta($pid, 'mep_reg_website', $mep_reg_website);

		update_post_meta($pid, 'mep_reg_veg', $mep_reg_veg);
		update_post_meta($pid, 'mep_reg_company', $mep_reg_company);
		update_post_meta($pid, 'mep_reg_gender', $mep_reg_gender);
		update_post_meta($pid, 'mep_reg_tshirtsize', $mep_reg_tshirtsize);
		update_post_meta($pid, 'mep_reg_tshirtsize_list', $mep_reg_tshirtsize_list);
		update_post_meta($pid, 'mep_event_template', $mep_event_template);
		update_post_meta($pid, 'mep_org_address', $mep_org_address);

		update_post_meta($pid, 'event_start_date', $event_start_date);
		update_post_meta($pid, 'event_start_time', $event_start_time);
		update_post_meta($pid, 'event_end_date', $event_end_date);
		update_post_meta($pid, 'event_end_time', $event_end_time);
		update_post_meta($post_id, 'event_start_datetime', $event_start_datetime);
		update_post_meta($post_id, 'event_end_datetime', $event_end_datetime);
		update_post_meta($post_id, 'event_expire_datetime', $event_expire_datetime);
		update_post_meta($pid, '_stock', $seat);

		update_post_meta($pid, '_stock_msg', 'new');
		update_post_meta($pid, 'longitude', $longitude);
		update_post_meta($pid, 'latitude', $latitude);
		update_post_meta($pid, 'location_name', $location_name);
		update_post_meta($pid, 'mep_location_venue', $mep_location_venue);
		update_post_meta($pid, 'mep_street', $mep_street);
		update_post_meta($pid, '_sold_individually', 'no');
		
		update_post_meta($pid, 'mep_city', $mep_city);
		update_post_meta($pid, 'mep_state', $mep_state);
		update_post_meta($pid, 'mep_postcode', $mep_postcode);
		update_post_meta($pid, 'mep_country', $mep_country);
		update_post_meta($pid, 'mep_sgm', $mep_sgm);
		update_post_meta($pid, '_price', 0);
		update_post_meta($pid, '_virtual', 'yes');
		update_post_meta($pid, '_sku', $pid);

		if (isset($_POST['mep_event_type']) && strip_tags($_POST['mep_event_type'])) {
			$mep_event_type = 'online';
		} else {
			$mep_event_type = 'offline';
		}
		update_post_meta($pid, 'mep_event_type', $mep_event_type);
		$mp_event_virtual_type_des = isset($_POST['mp_event_virtual_type_des']) ? strip_tags($_POST['mp_event_virtual_type_des']) : "";
		update_post_meta($pid, 'mp_event_virtual_type_des', $mp_event_virtual_type_des);


		if (!empty($mdate) && $mdate != $oldm) {
			update_post_meta($post_id, 'mep_event_more_date', $mdate);
		} elseif (empty($mdate) && $oldm) {
			delete_post_meta($post_id, 'mep_event_more_date', $oldm);
		}
	}
}
