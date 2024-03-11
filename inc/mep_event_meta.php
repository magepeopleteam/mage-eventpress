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
		add_meta_box('mp_event_all_info_in_tab', __('<i class="fas fa-info-circle"></i> ' . $event_label . ' Information : ', 'mage-eventpress') . get_the_title(get_the_id()), array($this, 'mp_event_all_in_tab'), 'mep_events', 'normal', 'high');
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
						<i class="fas fa-map-marked"></i><?php esc_html_e('Venue/Location', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_after_tab_name_location', $post_id); ?>
					<li data-target-tabs="#mp_ticket_type_pricing">
						<i class="fas fa-dollar-sign"></i><?php esc_html_e('Ticket & Pricing', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_before_tab_name_ticket_type', $post_id); ?>
					<li data-target-tabs="#mp_event_time">
						<i class="far fa-calendar-alt"></i><?php esc_html_e('Date & Time', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_before_tab_name_date_time', $post_id); ?>

					<li data-target-tabs="#mp_event_settings">
						<i class="fas fa-users-cog"></i><?php esc_html_e('Settings', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_before_tab_name_settings', $post_id); ?>
					<?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
						<li data-target-tabs="#mp_event_tax_settings">
							<span class="dashicons dashicons-admin-settings"></span>&nbsp;&nbsp;<?php esc_html_e('Tax', 'mage-eventpress'); ?>
						</li>
					<?php } ?>
					<?php do_action('mep_admin_event_details_before_tab_name_tax', $post_id); ?>
					<li data-target-tabs="#mp_event_rich_text">
						<i class="far fa-newspaper"></i><?php esc_html_e('Rich text', 'mage-eventpress'); ?>
					</li>
					<li data-target-tabs="#mp_event_email_text">
						<i class="far fa-envelope-open"></i><?php esc_html_e('Email Text', 'mage-eventpress'); ?>
					</li>
					<?php do_action('mep_admin_event_details_before_tab_name_rich_text', $post_id); ?>
					<?php do_action('mp_event_all_in_tab_menu'); ?>

					<?php do_action('mep_admin_event_details_end_of_tab_name', $post_id); ?>
				</ul>
			</div>
			<div class="mp_tab_details">
				<?php do_action('mep_admin_event_details_before_tab_details_location', $post_id); ?>
				<div class="mp_tab_item active" data-tab-item="#mp_event_venue">
				<?php do_action('mep_event_tab_before_location',$post_id); ?>
				<div class='mep_event_tab_location_content'>
					<h3><?php echo esc_html($event_label); esc_html_e(' Location :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mp_event_venue($post_id); ?>
				</div>
					<?php do_action('mep_event_tab_after_location'); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_location', $post_id); ?>
				<div class="mp_tab_item" data-tab-item="#mp_ticket_type_pricing">
					<?php do_action('mep_event_tab_before_ticket_pricing',$post_id); ?>
				<div class='mep_ticket_type_setting_sec'>
					<h3><?php esc_html_e('Ticket Type List :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mep_event_ticket_type($post_id); ?>
					<h3><?php esc_html_e('Extra service Area :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mep_event_extra_price_option($post_id); ?>
				</div>
					<?php do_action('mep_event_tab_after_ticket_pricing'); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_ticket_type', $post_id); ?>
				<div class="mp_tab_item" data-tab-item="#mp_event_time">
					<h3><?php echo esc_html($event_label);
						esc_html_e(' Date & TIme :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mep_event_date_meta_box_cb($post_id); ?>
					<?php do_action('mp_event_recurring_every_day_setting', $post_id); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_date_time', $post_id); ?>

				<div class="mp_tab_item" data-tab-item="#mp_event_rich_text">
					<h3><?php echo esc_html($event_label);
						esc_html_e(' Rich Texts for SEO & Google Schema Text :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mp_event_rich_text($post_id); ?>
					<?php do_action('mep_event_tab_after_rich_text'); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_rich_text', $post_id); ?>
				<div class="mp_tab_item" data-tab-item="#mp_event_settings">
					<h3><?php echo esc_html($event_label);
						esc_html_e(' Settings :', 'mage-eventpress'); ?></h3>
					<hr />
					<?php $this->mp_event_settings($post_id); ?>
					<?php do_action('mep_event_tab_after_settings'); ?>
				</div>
				<?php do_action('mep_admin_event_details_after_tab_details_settings', $post_id); ?>
				<?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
					
					<div class="mp_tab_item" data-tab-item="#mp_event_tax_settings">

						<h3><?php echo esc_html($event_label);
							esc_html_e(' Tax Settings :', 'mage-eventpress'); ?></h3>
						<hr />
						<?php $this->mp_event_tax($post_id); ?>
						<?php do_action('mep_event_tab_after_tax_settings'); ?>
						
					</div>				
				<?php } ?>
				<div class="mp_tab_item" data-tab-item="#mp_event_email_text">
                        <?php 
                        $text= get_post_meta($post_id, 'mep_event_cc_email_text' , true );
                        wp_editor( htmlspecialchars_decode($text), 'mep_event_cc_email_text', $settings = array('textarea_name'=>'mep_event_cc_email_text',  'editor_height' => 625,) );
                        ?>
                        <b>Usable Dynamic tags:</b>
                        <br/> Attendee Name:<b>{name}</b><br/>
                        Event Name: <b>{event}</b><br/>
                        Ticket Type: <b>{ticket_type}</b><br/>
                        Event Date: <b>{event_date}</b><br/>
                        Start Time: <b>{event_time}</b><br/>
                        Full DateTime: <b>{event_datetime}</b>                        
                </div>					
				<?php do_action('mp_event_all_in_tab_item', $post_id); ?>
				<?php
				do_action('mep_admin_event_details_end_of_tab_details', $post_id); ?>
				<p style="font-size: 10px;text-align: right;position: absolute;bottom: -6px;right: 14px;">
					#WC:<?php echo get_post_meta($post_id, 'link_wc_product', true); ?>
				</p>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(function($) {
				$("#mp_event_all_info_in_tab").parent().removeClass('meta-box-sortables');
			});
		</script>
		<?php
	}

	public function is_gutenberg_active()
	{
		$gutenberg    = false;
		$block_editor = false;

		if (has_filter('replace_editor', 'gutenberg_init')) {
			// Gutenberg is installed and activated.
			$gutenberg = true;
		}

		if (version_compare($GLOBALS['wp_version'], '5.0-beta', '>')) {
			// Block editor.
			$block_editor = true;
		}

		if (!$gutenberg && !$block_editor) {
			return false;
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if (!is_plugin_active('classic-editor/classic-editor.php')) {
			return true;
		}

		$use_block_editor = (get_option('classic-editor-replace') === 'no-replace');

		return $use_block_editor;
	}

	public function mp_event_venue($post_id)
	{
		$event_label     = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
		$values          = get_post_custom($post_id);
		$user_api        = mep_get_option('google-map-api', 'general_setting_sec', '');
		$map_type        = mep_get_option('mep_google_map_type', 'general_setting_sec', 'iframe');
		$mep_org_address = array_key_exists('mep_org_address', $values) ? $values['mep_org_address'][0] : 0;
		$map_visible     = array_key_exists('mep_sgm', $values) ? $values['mep_sgm'][0] : 0;
		$author_id 		 = get_post_field('post_author', $post_id);

		if ($this->is_gutenberg_active()) { ?>
			<input type="hidden" name="post_author_gutenberg" value="<?php echo esc_attr($author_id); ?>">
		<?php }
		?>
		<div class="mp_ticket_type_table">
			<table>
				<tr>
					<th style="min-width: 160px;"><?php esc_html_e(" Location Source:", "mage-eventpress"); ?></th>
					<td colspan="3" style="min-width: 450px;">
						<label for='mep_org_address_list'>
							<select class="mp_formControl" name="mep_org_address" class='mep_org_address_list' id='mep_org_address_list'>
								<option value="0" <?php echo ($mep_org_address == 0) ? esc_attr('selected') : ''; ?>><?php echo esc_html($event_label);
																												_e(' Details', 'mage-eventpress'); ?></option>
								<option value="1" <?php echo ($mep_org_address == 1) ? esc_attr('selected') : ''; ?>><?php esc_html_e('Organizer', 'mage-eventpress'); ?></option>
							</select>
						</label>
						<p class="event_meta_help_txt">
						<?php esc_html_e('If you have saved organizer details, please select the "Organizer" option. Please note that if you select "Organizer" and have not checked the organizer from the Event Organizer list on the right sidebar, the Event Location section will not populate on the front end.', 'mage-eventpress'); ?>
						</p>
					</td>
				</tr>
				<tr class="mp_event_address">
					<th><?php esc_html_e('Location/Venue:', 'mage-eventpress'); ?></th>
					<td>
						<label>
							<input type="text" name='mep_location_venue' placeholder="Ex: New york Meeting Center" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_location_venue'); ?>'>
						</label>
					</td>
					<th><span><?php esc_html_e('Street:', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_street' placeholder="Ex: 10 E 33rd St" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_street'); ?>'>
						</label>
					</td>
				</tr>
				<tr class="mp_event_address">
					<th><span><?php esc_html_e('City: ', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_city' placeholder="Ex: New York" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_city'); ?>'>
						</label>
					</td>
					<th><span><?php esc_html_e('State: ', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_state' placeholder="Ex: NY" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_state'); ?>'>
						</label>
					</td>
				</tr>
				<tr class="mp_event_address">
					<th><span><?php esc_html_e('Postcode: ', 'mage-eventpress'); ?></span></th>
					<td>
						<label>
							<input type="text" name='mep_postcode' placeholder="Ex: 10016" class="mp_formControl" value='<?php echo mep_get_event_locaion_item($post_id, 'mep_postcode'); ?>'>
						</label>
					</td>
					<th><span><?php esc_html_e('Country: ', 'mage-eventpress'); ?></span></th>
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
					<input type="checkbox" name='mep_sgm' value='1' <?php echo esc_attr($map_visible) > 0 ? esc_attr('checked') : ''; ?>>
					<span><?php esc_html_e('Show Google Map ', 'mage-eventpress'); ?></span>
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
							<input id="pac-input" name='location_name' value='' />
						</div>


						<input type="hidden" class="form-control" required name="latitude" value="<?php if (array_key_exists('latitude', $values)) {
																										echo esc_attr($values['latitude'][0]);
																									} ?>">
						<input type="hidden" class="form-control" required name="longitude" value="<?php if (array_key_exists('longitude', $values)) {
																										echo esc_attr($values['longitude'][0]);
																									} ?>">
						<div id="map"></div>

					<?php
					} else {
						?>
						<span class=mep_status><span class=err>
							<?php esc_html_e('No Google MAP API Key Found. Please enter API KEY','mage-eventpress'); ?> <a href="<?php echo get_site_url() . esc_url('/wp-admin/options-general.php?page=mep_event_settings_page'); ?>"><?php esc_html_e('Here','mage-eventpress'); ?></a></span>
						</span>
						<?php
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
									lat: <?php echo esc_attr($lat); ?>,
									lng: <?php echo esc_attr($lon); ?>
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
									lat: <?php echo esc_attr($lat); ?>,
									lng: <?php echo esc_attr($lon); ?>
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

								// $("input[name=coordinate]").val(address);
								jQuery("input[name=latitude]").val(latitude);
								jQuery("input[name=longitude]").val(longitude);
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

		$values = get_post_custom($post_id);
		wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
		$reg_checked = '';
		$col_display = 'none';
		if (array_key_exists('mep_show_advance_col_status', $values)) {
			if ($values['mep_show_advance_col_status'][0] == 'on') {
				$reg_checked = 'checked';
				$col_display = 'table-cell';
			}
		}

	?>
<style>
	.mep_hide_on_load{
		display:<?php echo $col_display; ?>;
	}
</style>
		<div class='mep-event-show-advance-col-info'>
			<ul>
				<li><span><?php esc_html_e('Show Advanced Column:', 'mage-eventpress'); ?></span></li>
				<li>
					<label class='mp_event_ticket_type_advance_col_switch'>
						<input class="mp_opacity_zero" type="checkbox" name="mep_show_advance_col_status" <?php echo esc_attr($reg_checked); ?> /><span class="mep_slider round"></span>
					</label>
					</li>
			</ul>
		</div>
		<div class="mp_ticket_type_table">
			<table id="repeatable-fieldset-one-t">
				<thead>
					<tr>
						<th style="min-width: 80px;" title="<?php esc_attr_e('Ticket Type Name', 'mage-eventpress'); ?>"><?php esc_html_e('Ticket', 'mage-eventpress'); ?></th>
						<th style="min-width: 80px;" title="<?php esc_attr_e('Ticket Type Details', 'mage-eventpress'); ?>"><?php esc_html_e('Short Desc.', 'mage-eventpress'); ?></th>
						<th style="min-width: 80px;" title="<?php esc_attr_e('Ticket Price', 'mage-eventpress'); ?>"><?php esc_html_e('Price', 'mage-eventpress'); ?></th>
						<?php do_action('mep_pricing_table_head_after_price_col'); ?>
						<th style="min-width: 80px;" title="<?php esc_attr_e('Available Qty', 'mage-eventpress'); ?>"><?php esc_html_e('Capacity', 'mage-eventpress'); ?>
					
					</th>
						

						<th class='mep_hide_on_load' style="min-width: 80px;" title="<?php esc_attr_e('Default Qty', 'mage-eventpress'); ?>"><?php esc_html_e('Default Qty', 'mage-eventpress'); ?></th>
						<th class='mep_hide_on_load' style="min-width: 80px;" title="<?php esc_attr_e('Reserve Qty', 'mage-eventpress'); ?>"><?php esc_html_e('Reserve Qty', 'mage-eventpress'); ?>
						<?php do_action('add_extra_field_icon',$post_id); ?>
						</th>
                        <?php do_action('mep_add_extra_column'); ?>
						<th class='mep_hide_on_load' style="min-width: 150px;" title="<?php esc_attr_e('Sale End Date', 'mage-eventpress'); ?>"><?php esc_html_e('Sale End Date', 'mage-eventpress'); ?></th>
						<th class='mep_hide_on_load' style="min-width: 120px;" title="<?php esc_attr_e('Sale End Time', 'mage-eventpress'); ?>"><?php esc_html_e('Sale End Time', 'mage-eventpress'); ?></th>



						<th style="min-width: 140px;" title="<?php esc_attr_e('Qty Box Type', 'mage-eventpress'); ?>"><?php esc_html_e('Qty Box', 'mage-eventpress'); ?></th>
						<th style="min-width: 80px;"><?php esc_html_e('Action', 'mage-eventpress'); ?>
						
					</th>
					</tr>
				</thead>
				<tbody class="mp_event_type_sortable">
					<?php

					if ($mep_event_ticket_type) :
						$count = 0;
						foreach ($mep_event_ticket_type as $field) {
							$qty_t_type  = array_key_exists('option_qty_t_type', $field) ? esc_attr($field['option_qty_t_type']) : 'inputbox';
							$count++;
					?>
							<tr>
								<td>
									<input type="text" class="mp_formControl" name="option_name_t[]" placeholder="Ex: Adult" value="<?php if ($field['option_name_t'] != '') { echo esc_attr($field['option_name_t']); } ?>" />
								</td>
								<td>
									<input type="text" class="mp_formControl" name="option_details_t[]" placeholder="" value="<?php  if (array_key_exists('option_details_t', $field) && $field['option_price_t'] != '') { echo esc_attr($field['option_details_t']); } ?>" />
								</td>
								<td>
									<input type="number" size="4" pattern="[0-9]*" step="0.001" class="mp_formControl" name="option_price_t[]" placeholder="Ex: 10" value="<?php if (array_key_exists('option_price_t', $field) && $field['option_price_t'] != '') {
																																												echo esc_attr($field['option_price_t']);
																																											} else {
																																												echo '';
																																											} ?>" />
								</td>
								<?php do_action('mep_pricing_table_data_after_price_col', $field, $post_id); ?>
								<td>
									<input type="number" size="4" pattern="[0-9]*" step="1" class="mp_formControl" name="option_qty_t[]" placeholder="Ex: 500" value="<?php if (isset($field['option_qty_t'])) {
																																											echo esc_attr($field['option_qty_t']);
																																										} else {
																																											echo 0;
																																										} ?>" />
								</td>
								<td class='mep_hide_on_load'>
									<input type="number" size="2" pattern="[0-9]*" step="1" class="mp_formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value="<?php if (isset($field['option_default_qty_t'])) {
																																												echo esc_attr($field['option_default_qty_t']);
																																											} else {
																																												echo 0;
																																											} ?>" />
								</td>
								<td class='mep_hide_on_load'>
									<input type="number" class="mp_formControl" name="option_rsv_t[]" placeholder="Ex: 5" value="<?php if (isset($field['option_rsv_t'])) {
																																		echo esc_attr($field['option_rsv_t']);
																																	} else {
																																		echo 0;
																																	} ?>" />
								</td>

								<?php do_action('mep_add_extra_input_box', $field,$count) ?>
								<td class='mep_hide_on_load'>
									<div class="sell_expire_date">
										<input type="date" id="ticket_sale_start_date" class="mp_formControl" value='<?php if (array_key_exists('option_sale_end_date_t', $field) && $field['option_sale_end_date_t'] != '') {
																															echo esc_attr(date('Y-m-d', strtotime($field['option_sale_end_date_t'])));
																														} ?>' name="option_sale_end_date[]" />
									</div>
								</td>
								<td class='mep_hide_on_load'>
									<div class="sell_expire_date">

										<input type="time" id="ticket_sale_start_time" class="mp_formControl" value='<?php if (array_key_exists('option_sale_end_date_t', $field) && $field['option_sale_end_date_t'] != '') {
																															echo esc_attr(date('H:i', strtotime($field['option_sale_end_date_t'])));
																														} ?>' name="option_sale_end_time[]" />
									</div>
								</td>
								<td>
									<select name="option_qty_t_type[]" class='mp_formControl'>
										<option value="inputbox" <?php if ($qty_t_type == 'inputbox') {
																		echo esc_attr("Selected");
																	} ?>><?php esc_html_e('Input Box', 'mage-eventpress'); ?></option>
										<option value="dropdown" <?php if ($qty_t_type == 'dropdown') {
																		echo esc_attr("Selected");
																	} ?>><?php esc_html_e('Dropdown List', 'mage-eventpress'); ?></option>
									</select>
								</td>

								<td>
									<div class="mp_event_remove_move">
										<button class="button remove-row-t" type="button"><span class="dashicons dashicons-trash"></span></button>
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
						<td><input type="text" class="mp_formControl" name="option_details_t[]" placeholder="" /></td>
						<td><input type="number" size="4" pattern="[0-9]*" class="mp_formControl" step="0.001" name="option_price_t[]" placeholder="Ex: 10" value="" /></td>
						<?php do_action('mep_pricing_table_empty_after_price_col'); ?>
						<td><input type="number" size="4" pattern="[0-9]*" step="1" class="mp_formControl" name="option_qty_t[]" placeholder="Ex: 15" value="" /></td>
						<td class='mep_hide_on_load'><input type="number" size="2" pattern="[0-9]*" class="mp_formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value="" /></td>
						<?php $option_rsv_t = '<td class="mep_hide_on_load"><input type="number" class="mp_formControl" name="option_rsv_t[]" placeholder="Ex: 5" value=""/></td>'; ?>
						<?php echo apply_filters('mep_add_field_to_ticket_type', mep_esc_html($option_rsv_t)); ?>
						<?php do_action('mep_add_extra_column_empty'); ?> 
						<td class="mep_hide_on_load">
							<div class="sell_expire_date" >
								<input type="date" id="ticket_sale_start_date" value='' name="option_sale_end_date[]" />
							</div>
						</td>
						<td class="mep_hide_on_load"> 
							<div class="sell_expire_date">
								<input type="time" id="ticket_sale_start_time" value='' name="option_sale_end_time[]" />
							</div>
						</td>
						<td>
							<select name="option_qty_t_type[]" class='mp_formControl'>
								<option value=''><?php esc_html_e('Please Select', 'mage-eventpress'); ?></option>
								<option value="inputbox"><?php esc_html_e('Input Box', 'mage-eventpress'); ?></option>
								<option value="dropdown"><?php esc_html_e('Dropdown List', 'mage-eventpress'); ?></option>
							</select>
						</td>
						<td>
						<button class="button remove-row-t" type="button"><i class="fas fa-trash"></i></button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<p>
		<button id="add-row-t" class="button" type="button"><i class="fas fa-plus-circle"></i> <?php esc_html_e('Add New Ticket Type', 'mage-eventpress'); ?></button>
		</p>






	<?php
	}

	public function mep_event_extra_price_option($post_id)
	{
		$mep_events_extra_prices = get_post_meta($post_id, 'mep_events_extra_prices', true);
		wp_nonce_field('mep_events_extra_price_nonce', 'mep_events_extra_price_nonce');
	?>
		<p class="event_meta_help_txt"><?php esc_html_e('Extra Service as Product that you can sell and it is not included on event package', 'mage-eventpress'); ?></p>
		<div class="mp_ticket_type_table">
			<table id="repeatable-fieldset-one">
				<thead>
					<tr>
						<th title="<?php esc_attr_e('Extra Service Name', 'mage-eventpress'); ?>"><?php esc_html_e('Name', 'mage-eventpress'); ?></th>
						<th title="<?php esc_attr_e('Extra Service Price', 'mage-eventpress'); ?>"><?php esc_html_e('Price', 'mage-eventpress'); ?></th>
						<th title="<?php esc_attr_e('Available Qty', 'mage-eventpress'); ?>"><?php esc_html_e('Available Qty', 'mage-eventpress'); ?></th>
						<th title="<?php esc_attr_e('Qty Box Type', 'mage-eventpress'); ?>" style="min-width: 140px;"><?php esc_html_e('Qty Box', 'mage-eventpress'); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody class="mp_event_type_sortable">
					<?php

					if ($mep_events_extra_prices) :
						foreach ($mep_events_extra_prices as $field) {
							$qty_type  = array_key_exists('option_qty_type', $field) ? esc_attr($field['option_qty_type']) : 'inputbox';
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

                                <td>
                                    <input type="number" class="mp_formControl" name="option_qty[]"
                                           placeholder="Ex: 100"
                                           value="<?php echo esc_attr(($field['option_qty'] != '') ? $field['option_qty'] : ''); ?>" />
                                </td>

								<td align="center">
									<select name="option_qty_type[]" class='mp_formControl'>
										<option value="inputbox" <?php if ($qty_type == 'inputbox') {
																		echo esc_attr("Selected");
																	} ?>><?php esc_html_e('Input Box', 'mage-eventpress'); ?></option>
										<option value="dropdown" <?php if ($qty_type == 'dropdown') {
																		echo esc_attr("Selected");
																	} ?>><?php esc_html_e('Dropdown List', 'mage-eventpress'); ?></option>
									</select>
								</td>
								<td>
									<div class="mp_event_remove_move">
									<button class="button remove-row" type="button"><i class="fas fa-trash"></i></button>
										<div class="mp_event_type_sortable_button"><i class="fas fa-grip-vertical"></i></div>
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
								<option value=""><?php esc_html_e('Please Select Type', 'mage-eventpress'); ?></option>
								<option value="inputbox"><?php esc_html_e('Input Box', 'mage-eventpress'); ?></option>
								<option value="dropdown"><?php esc_html_e('Dropdown List', 'mage-eventpress'); ?></option>
							</select></td>
						<td>
							<button class="button remove-row"><i class="fas fa-trash"></i></button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<p>
		<button id="add-row" class="button"><i class="fas fa-plus-circle"></i> <?php esc_html_e('Add Extra Price', 'mage-eventpress'); ?></button>
		</p>
	<?php
	}

	public function mep_event_date_meta_box_cb($post_id)
	{
		$values = get_post_custom($post_id);
	?>
		<div class="sec">
			<div class="mp_ticket_type_table">
				<table id="repeatable-fieldset-one-d">
					<thead>
						<th style="min-width: 120px;"><?php esc_html_e('Start Date', 'mage-eventpress'); ?></th>
						<th style="min-width: 120px;"><?php esc_html_e('Start Time', 'mage-eventpress'); ?></th>
						<th style="min-width: 120px;"><?php esc_html_e('End Date', 'mage-eventpress'); ?></th>
						<th style="min-width: 120px;"><?php esc_html_e('End Time', 'mage-eventpress'); ?></th>
						<?php do_action('mep_date_table_head', $post_id); ?>
						<th style="min-width: 60px;"><?php esc_html_e('Action', 'mage-eventpress'); ?></th>
					</thead>
					<tbody class="mp_event_type_sortable">
						<tr>
							<td>
								<input type="date" class="mp_formControl" name="event_start_date" placeholder="Start Date" value="<?php if (array_key_exists('event_start_date', $values)) {
																																		echo esc_attr($values['event_start_date'][0]);
																																	} ?>" />
							</td>
							<td>
								<input type="time" class="mp_formControl" name="event_start_time" placeholder="Start Time" value="<?php if (array_key_exists('event_start_time', $values)) {
																																		echo esc_attr($values['event_start_time'][0]);
																																	} ?>" />
							</td>
							<td>
								<input type="date" class="mp_formControl" name="event_end_date" placeholder="End Date" value="<?php if (array_key_exists('event_end_date', $values)) {
																																	echo esc_attr($values['event_end_date'][0]);
																																} ?>" />
							</td>
							<td>
								<input type="time" class="mp_formControl" name="event_end_time" placeholder="End Time" value="<?php if (array_key_exists('event_end_time', $values)) {
																																	echo esc_attr(date('H:i', strtotime($values['event_end_time'][0])));
																																} ?>" />
							</td>
							<?php do_action('mep_date_table_body_default_date', $post_id); ?>
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
																																						echo esc_attr(date('Y-m-d', strtotime($field['event_more_start_date'])));
																																					} ?>" />
									</td>
									<td>
										<input type="time" class="mp_formControl" name="event_more_start_time[]" placeholder="Start Time" value="<?php if ($field['event_more_start_time'] != '') {
																																						echo esc_attr(date('H:i', strtotime($field['event_more_start_time'])));
																																					} ?>" />
									</td>
									<td>
										<input type="date" class="mp_formControl" name="event_more_end_date[]" placeholder="End Date" value="<?php if ($field['event_more_end_date'] != '') {
																																					echo esc_attr(date('Y-m-d', strtotime($field['event_more_end_date'])));
																																				} ?>" />
									</td>
									<td>

										<input type="time" class="mp_formControl" name="event_more_end_time[]" placeholder="End Time" value="<?php if ($field['event_more_end_time'] != '') {
																																					echo esc_attr(date('H:i', strtotime($field['event_more_end_time'])));
																																				} ?>" />
									</td>
									<?php do_action('mep_date_table_body_more_date', $post_id, $field); ?>

									<td>
										<div class="mp_event_remove_move">
											<button class="button remove-row-d" type="button"><i class="fas fa-trash"></i></button>
											<div class="mp_event_type_sortable_button"><i class="fas fa-grip-vertical"></i></div>
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
							<?php do_action('mep_date_table_empty', $post_id); ?>
							<td>
								<button class="button remove-row-d"><i class="fas fa-trash"></i></button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<button id="add-new-date-row" class="button"><i class="fas fa-plus-circle"></i> <?php esc_html_e('Add More Dates', 'mage-eventpress'); ?></button>
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
				<?php _e('Rich Text Status', 'mage-eventpress'); ?>
				<select id='mep_rich_text_status' name='mep_rich_text_status'>
					<option value='enable' <?php if ($rt_status == 'enable') {
												echo esc_html('Selected');
											} ?>><?php esc_html_e('Enable', 'mage-eventpress'); ?></option>
					<option value='disable' <?php if ($rt_status == 'disable') {
												echo esc_html('Selected');
											} ?>><?php esc_html_e('Disable', 'mage-eventpress'); ?></option>
				</select>
			</label>
		</div>
		<table id='mep_rich_text_table' <?php if ($rt_status == 'disable') { ?> style='display:none;' <?php } ?>>
			<tr>
				<th><span><?php esc_html_e('Type :', 'mage-eventpress'); ?></span></th>
				<td colspan="3"><?php esc_html_e('Event', 'mage-eventpress'); ?></td>
			</tr>
			<tr>
				<th><span><?php esc_html_e('Name :', 'mage-eventpress'); ?></span></th>
				<td colspan="3"><?php echo get_the_title($post_id); ?></td>
			</tr>
			<tr>
				<th><span><?php esc_html_e('Start Date :', 'mage-eventpress'); ?></span></th>
				<td colspan="3"><?php echo esc_attr($event_start_date) ? get_mep_datetime($event_start_date, 'date-time-text') : ''; ?></td>
			</tr>
			<tr>
				<th><span><?php _e('End Date :', 'mage-eventpress'); ?></span></th>
				<td colspan="3"><?php echo esc_attr($event_end_date) ? get_mep_datetime($event_end_date, 'date-time-text') : ''; ?></td>
			</tr>
			<tr>
				<th><span><?php esc_html_e('Event Status:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="mep_rt_event_status">
							<option value="EventRescheduled" <?php echo ($event_rt_status == 'EventMovedOnline') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Event Rescheduled', 'mage-eventpress'); ?></option>
							<option value="EventMovedOnline" <?php echo ($event_rt_status == 'EventMovedOnline') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Event Moved Online', 'mage-eventpress'); ?></option>
							<option value="EventPostponed" <?php echo ($event_rt_status == 'EventPostponed') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Event Postponed', 'mage-eventpress'); ?></option>
							<option value="EventCancelled" <?php echo ($event_rt_status == 'EventCancelled') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Event Cancelled', 'mage-eventpress'); ?></option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<th><span><?php esc_html_e('Event Attendance Mode:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="mep_rt_event_attandence_mode">
							<option value="OfflineEventAttendanceMode" <?php echo ($event_rt_atdnce_mode == 'OfflineEventAttendanceMode') ? esc_attr('selected') : ''; ?>><?php esc_html_e('OfflineEventAttendanceMode', 'mage-eventpress'); ?></option>
							<option value="OnlineEventAttendanceMode" <?php echo ($event_rt_atdnce_mode == 'OnlineEventAttendanceMode') ? esc_attr('selected') : ''; ?>><?php esc_html_e('OnlineEventAttendanceMode', 'mage-eventpress'); ?></option>
							<option value="MixedEventAttendanceMode" <?php echo ($event_rt_atdnce_mode == 'MixedEventAttendanceMode') ? esc_attr('selected') : ''; ?>><?php esc_html_e('MixedEventAttendanceMode', 'mage-eventpress'); ?></option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<th><span><?php esc_html_e('Previous Start Date:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<input type='text' class="mp_formControl" name="mep_rt_event_prvdate" value='<?php echo esc_attr($event_rt_prv_date); ?>' />
					</label>
				</td>
			</tr>
			<tr>
				<td colspan="4">
					<?php
					if ($post_id) {
					?>
						<p class="event_meta_help_txt">
							<a href='https://search.google.com/test/rich-results?utm_campaign=devsite&utm_medium=jsonld&utm_source=event&url=<?php echo get_the_permalink($post_id); ?>&user_agent=2' target="_blank"><?php esc_html_e('Check Rich Text Status', 'mage-eventpress'); ?></a>
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
				if (rich_status == 'enable') {
					// mep_rich_text_table
					jQuery('#mep_rich_text_table').show(500);
				} else if (rich_status == 'disable') {
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
			$this->mp_event_enddatetime_status($post_id);
			$this->mp_event_available_seat_status($post_id);
			$this->mp_event_reset_booking_count($post_id);
			do_action('mp_event_switching_button_hook', $post_id);
			$this->mp_event_speaker_ticket_type($post_id);
			?>
		</table>
	<?php
	}


	public function mp_event_enddatetime_status($post_id)
	{
		$values = get_post_custom($post_id);
		// wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
		$mep_show_end_datetime = '';
		if (array_key_exists('mep_show_end_datetime', $values)) {			
			if ($values['mep_show_end_datetime'][0] == 'yes') {
				$mep_show_end_datetime = 'checked';
			}
		} else {
				$mep_show_end_datetime = 'checked';
		}
	?>


		<tr>
			<th><span><?php _e('Display End Datetime:', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label>
					<input class="mp_opacity_zero" type="checkbox" name="mep_show_end_datetime" value='yes' <?php echo esc_attr($mep_show_end_datetime); ?> />
					<span class="mep_slider round"></span>
				</label>
			</td>
		</tr>
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
			<th title="<?php esc_html_e('Event SKU No:', 'mage-eventpress'); ?>"><span><?php esc_html_e('SKU No:', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label>
					<input class="mep_input_text" type="text" name="mep_event_sku" value="<?php echo get_post_meta($post_id, '_sku', true); ?>" />
				</label>
			</td>
		</tr>

		<!--tr>
			<th><span><?php esc_html_e('Registration On/Off:', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label>
					<input class="mp_opacity_zero" type="checkbox" name="mep_reg_status" <?php echo esc_attr($reg_checked); ?> />
					<span class="mep_slider round"></span>
				</label>
			</td>
		</tr> -->
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
			<th><span><?php esc_html_e('Show Available Seat?', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label>
					<input class="mp_opacity_zero" type="checkbox" name="mep_available_seat" <?php echo esc_attr($seat_checked); ?> />
					<span class="mep_slider round"></span>
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
			<th><span><?php esc_html_e('Reset Booking Count :', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label>
					<input class="mp_opacity_zero" type="checkbox" name="mep_reset_status" class="switch_checkbox" />
					<span class="mep_slider round"></span>
					<span style="padding: 0 0 0 60px;"><?php esc_html_e('Current Booking Status :', 'mage-eventpress'); ?></span>
					<span><?php mep_get_event_total_seat($post_id); ?></span>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<p class="event_meta_help_txt">
					<?php esc_html_e('If you reset this count, all booking information will be removed, including the attendee list. This action is irreversible, so please be sure before you proceed.', 'mage-eventpress'); ?>
				</p>
			</td>
		</tr>
	<?php
	}

	public function mp_event_speaker_ticket_type($post_id)
	{
		$event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
		$event_type 		= get_post_meta($post_id, 'mep_event_type', true);
		$event_member_type 	= get_post_meta($post_id, 'mep_member_only_event', true);
		$saved_user_role 	= get_post_meta($post_id, 'mep_member_only_user_role', true) ? get_post_meta($post_id, 'mep_member_only_user_role', true) : [];
		$description 		= html_entity_decode(get_post_meta($post_id, 'mp_event_virtual_type_des', true));
		$checked 			= ($event_type == 'online') ? 'checked' : '';
		$member_checked 	= ($event_member_type == 'member_only') ? 'checked' : '';
	?>
		<!-- <tr>
			<th><span><?php esc_html_e('Virtual ', 'mage-eventpress');
						echo esc_html($event_label . '?');  ?></span></th>
			<td colspan="3">
				<label class="mp_event_virtual_type_des_switch">
					<input class="mp_opacity_zero" type="checkbox" name="mep_event_type" <?php echo esc_attr($checked); ?> />
					<span class="mep_slider round"></span>
				</label>
				<p></p>
				<label class="mp_event_virtual_type_des <?php echo ($event_type == 'online') ? esc_attr('active') : ''; ?>">
					<?php wp_editor(html_entity_decode(nl2br($description)), 'mp_event_virtual_type_des'); ?>
					<p class="event_meta_help_txt"><?php esc_html_e('Please enter your virtual event joining details information below. This information will be sent to the buyer along with a confirmation email.', 'mage-eventpress') ?></p>
				</label>
			</td>
		</tr> -->
		<tr>
			<th><span><?php esc_html_e('Member Only Event?', 'mage-eventpress'); ?></span></th>
			<td colspan="3">
				<label class="mp_event_virtual_type_des_switch">
					<input class="mp_opacity_zero" type="checkbox" name="mep_member_only_event" <?php echo esc_attr($member_checked); ?> />
					<span class="mep_slider round"></span>
				</label>
				<p></p>
				<label class="mp_event_virtual_type_des <?php echo ($event_member_type == 'member_only') ? esc_attr('active') : ''; ?>">
					<select name='mep_member_only_user_role[]' multiple>
						<option value="all" <?php if (in_array('all', $saved_user_role)) { echo esc_attr('Selected'); } ?>><?php esc_html_e('For Any Logged in user', 'mage-eventpress'); ?> </option>
						<?php echo mep_get_user_list($saved_user_role); ?>
					</select>
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
				<th><span><?php esc_html_e('Tax status:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="_tax_status">
							<option value="taxable" <?php echo ($tx_status == 'taxable') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Taxable', 'mage-eventpress'); ?></option>
							<option value="shipping" <?php echo ($tx_status == 'shipping') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Shipping only', 'mage-eventpress'); ?></option>
							<option value="none" <?php echo ($tx_status == 'none') ? esc_attr('selected') : ''; ?>><?php esc_html_e('None', 'mage-eventpress'); ?></option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<th><span><?php esc_html_e('Tax class:', 'mage-eventpress'); ?></span></th>
				<td colspan="3">
					<label>
						<select class="mp_formControl" name="_tax_class">
							<option value="standard" <?php echo ($tx_class == 'standard') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Standard', 'mage-eventpress'); ?></option>
							<?php mep_get_all_tax_list($tx_class); ?>
						</select>
					</label>
					<p class="event_meta_help_txt">
						<?php esc_html_e('In order to add a new tax class, please go to WooCommerce -> Settings -> Tax Area', 'mage-eventpress'); ?>
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
		$global_template = mep_get_option('mep_global_single_template', 'single_event_setting_sec', 'theme-2');
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

		$new          		= array();
		$names        		= $_POST['option_name_t'] ? mage_array_strip($_POST['option_name_t']) : array();
		$details        	= $_POST['option_details_t'] ? mage_array_strip($_POST['option_details_t']) : array();
		$ticket_price 		= $_POST['option_price_t'] ? mage_array_strip($_POST['option_price_t']) : array();
		$qty          		= $_POST['option_qty_t'] ? mage_array_strip($_POST['option_qty_t']) : array();
		$dflt_qty     		= $_POST['option_default_qty_t'] ? mage_array_strip($_POST['option_default_qty_t']) : array();
		$rsv          		= $_POST['option_rsv_t'] ? mage_array_strip($_POST['option_rsv_t']) : array();
		$qty_type     		= $_POST['option_qty_t_type'] ? mage_array_strip($_POST['option_qty_t_type']) : array();
		$sale_end_date     	= $_POST['option_sale_end_date'] ? mage_array_strip($_POST['option_sale_end_date']) : array();
		$sale_end_time     	= $_POST['option_sale_end_time'] ? mage_array_strip($_POST['option_sale_end_time']) : array();

		$count = count($names);

		for ($i = 0; $i < $count; $i++) {

			if ($names[$i] != '') :
				$new[$i]['option_name_t'] 			= stripslashes(strip_tags($names[$i]));
			endif;

			if ($details[$i] != '') :
				$new[$i]['option_details_t'] 		= stripslashes(strip_tags($details[$i]));
			endif;

			if ($ticket_price[$i] != '') :
				$new[$i]['option_price_t'] 			= stripslashes(strip_tags($ticket_price[$i]));
			endif;

			if ($qty[$i] != '') :
				$new[$i]['option_qty_t'] 			= stripslashes(strip_tags($qty[$i]));
			endif;

			if ($rsv[$i] != '') :
				$new[$i]['option_rsv_t'] 			= stripslashes(strip_tags($rsv[$i]));
			endif;

			if ($dflt_qty[$i] != '') :
				$new[$i]['option_default_qty_t'] 	= stripslashes(strip_tags($dflt_qty[$i]));
			endif;

			if ($qty_type[$i] != '') :
				$new[$i]['option_qty_t_type'] 		= stripslashes(strip_tags($qty_type[$i]));
			endif;

			if ($sale_end_date[$i] != '') :
				$new[$i]['option_sale_end_date'] 	= stripslashes(strip_tags($sale_end_date[$i]));
			endif;

			if ($sale_end_time[$i] != '') :
				$new[$i]['option_sale_end_time'] 	= stripslashes(strip_tags($sale_end_time[$i]));
			endif;

			if ($sale_end_date[$i] != '') :
				$new[$i]['option_sale_end_date_t'] 	= stripslashes(strip_tags($sale_end_date[$i] . ' ' . $sale_end_time[$i]));
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
		$names 	  = isset($_POST['option_name']) ? mage_array_strip($_POST['option_name'])  : [];
		$urls     = isset($_POST['option_price']) ? mage_array_strip($_POST['option_price'])  : [];
		$qty      = isset($_POST['option_qty']) ? mage_array_strip($_POST['option_qty'])  : [];
		$qty_type = isset($_POST['option_qty_type']) ? mage_array_strip($_POST['option_qty_type']) : [];
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
	global $wpdb;
	$table_name = $wpdb->prefix . "posts";

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
		$more_start_date = isset($_POST['event_more_start_date']) ? mage_array_strip($_POST['event_more_start_date']) : array();
		$more_start_time = isset($_POST['event_more_start_time']) ? mage_array_strip($_POST['event_more_start_time']) : '';
		$more_end_date   = isset($_POST['event_more_end_date']) ? mage_array_strip($_POST['event_more_end_date']) : '';
		$more_end_time   = isset($_POST['event_more_end_time']) ? mage_array_strip($_POST['event_more_end_time']) : '';
		$mdate           = [];


		$mcount = count($more_start_date);

		for ($m = 0; $m < $mcount; $m++) {
			if ($more_start_date[$m] != '') :
				$mdate[$m]['event_more_start_date'] = stripslashes(sanitize_text_field($more_start_date[$m]));
				$mdate[$m]['event_more_start_time'] = stripslashes(sanitize_text_field($more_start_time[$m]));
				$mdate[$m]['event_more_end_date']   = stripslashes(sanitize_text_field($more_end_date[$m]));
				$mdate[$m]['event_more_end_time']   = stripslashes(sanitize_text_field($more_end_time[$m]));
			endif;
		}


		$event_rt_status      		= sanitize_text_field($_POST['mep_rt_event_status']);
		$event_rt_atdnce_mode 		= sanitize_text_field($_POST['mep_rt_event_attandence_mode']);
		$event_rt_prv_date    		= sanitize_text_field($_POST['mep_rt_event_prvdate']);

		$seat               		= 0;
		$rsvs               		= 0;
		$mep_location_venue 		= isset($_POST['mep_location_venue']) ? sanitize_text_field($_POST['mep_location_venue']) : "";
		$mep_street         		= isset($_POST['mep_street']) ? sanitize_text_field($_POST['mep_street']) : "";
		$mep_city           		= isset($_POST['mep_city']) ? sanitize_text_field($_POST['mep_city']) : "";
		$mep_state          		= isset($_POST['mep_state']) ? sanitize_text_field($_POST['mep_state']) : "";
		$mep_postcode       		= isset($_POST['mep_postcode']) ? sanitize_text_field($_POST['mep_postcode']) : "";
		$mep_country        		= isset($_POST['mep_country']) ? sanitize_text_field($_POST['mep_country']) : "";

		$mep_sgm         			= isset($_POST['mep_sgm']) ? sanitize_text_field($_POST['mep_sgm']) : "";
		$mep_org_address 			= isset($_POST['mep_org_address']) ? sanitize_text_field($_POST['mep_org_address']) : "";
		$_price          			= isset($_POST['_price']) ? sanitize_text_field($_POST['_price']) : "";

		$event_start_date 			= sanitize_text_field($_POST['event_start_date']);
		$event_start_time 			= sanitize_text_field($_POST['event_start_time']);
		$event_end_date   			= sanitize_text_field($_POST['event_end_date']);
		$event_end_time   			= sanitize_text_field($_POST['event_end_time']);

		$latitude      				= isset($_POST['latitude']) ? sanitize_text_field($_POST['latitude']) : "";
		$longitude     				= isset($_POST['latitude']) ? sanitize_text_field($_POST['longitude']) : "";
		$location_name 				= isset($_POST['location_name']) ? sanitize_text_field($_POST['location_name']) : "";

		$mep_full_name           	= isset($_POST['mep_full_name']) ? sanitize_text_field($_POST['mep_full_name']) : "";
		$mep_reg_email           	= isset($_POST['mep_reg_email']) ? sanitize_text_field($_POST['mep_reg_email']) : "";
		$mep_reg_phone           	= isset($_POST['mep_reg_phone']) ? sanitize_text_field($_POST['mep_reg_phone']) : "";
		$mep_reg_address         	= isset($_POST['mep_reg_address']) ? sanitize_text_field($_POST['mep_reg_address']) : "";
		$mep_reg_designation     	= isset($_POST['mep_reg_designation']) ? sanitize_text_field($_POST['mep_reg_designation']) : "";
		$mep_reg_website         	= isset($_POST['mep_reg_website']) ? sanitize_text_field($_POST['mep_reg_website']) : "";
		$mep_reg_veg             	= isset($_POST['mep_reg_veg']) ? sanitize_text_field($_POST['mep_reg_veg']) : "";
		$mep_reg_company         	= isset($_POST['mep_reg_company']) ? sanitize_text_field($_POST['mep_reg_company']) : "";
		$mep_reg_gender          	= isset($_POST['mep_reg_gender']) ? sanitize_text_field($_POST['mep_reg_gender']) : "";
		$mep_reg_tshirtsize      	= isset($_POST['mep_reg_tshirtsize']) ? sanitize_text_field($_POST['mep_reg_tshirtsize']) : "";
		$mep_reg_tshirtsize_list 	= isset($_POST['mep_reg_tshirtsize_list']) ? sanitize_text_field($_POST['mep_reg_tshirtsize_list']) : "";
		$mep_event_template      	= isset($_POST['mep_event_template']) ? sanitize_text_field($_POST['mep_event_template']) : "";


		$event_start_datetime  	= date('Y-m-d H:i:s', strtotime($event_start_date . ' ' . $event_start_time));
		$event_end_datetime    	= date('Y-m-d H:i:s', strtotime($event_end_date . ' ' . $event_end_time));
		$md                    	= sizeof($mdate) > 0 ? end($mdate) : array();
		$event_expire_datetime 	= sizeof($md) > 0 ? date('Y-m-d H:i:s', strtotime($md['event_more_end_date'] . ' ' . $md['event_more_end_time'])) : $event_end_datetime;



		$mep_reg_status 		= isset($_POST['mep_reg_status']) ? sanitize_text_field($_POST['mep_reg_status']) : 'off';
		$mep_show_advance_col_status 		= isset($_POST['mep_show_advance_col_status']) ? sanitize_text_field($_POST['mep_show_advance_col_status']) : 'off';
		$mep_enable_custom_dt_format 		= isset($_POST['mep_enable_custom_dt_format']) ? sanitize_text_field($_POST['mep_enable_custom_dt_format']) : 'off';
		$mep_show_end_datetime 		= isset($_POST['mep_show_end_datetime']) ? sanitize_text_field($_POST['mep_show_end_datetime']) : 'no';
		$mep_reset_status 		= isset($_POST['mep_reset_status']) ? sanitize_text_field($_POST['mep_reset_status']) : 'off';
		$mep_available_seat 	= isset($_POST['mep_available_seat']) ? sanitize_text_field($_POST['mep_available_seat']) : 'off';
		$_tax_status 			= isset($_POST['_tax_status']) ? sanitize_text_field($_POST['_tax_status']) : 'none';
		$_tax_class 			= isset($_POST['_tax_class']) ? sanitize_text_field($_POST['_tax_class']) : '';
		
		$mep_member_only_user_role 	= isset($_POST['mep_member_only_user_role']) && is_array($_POST['mep_member_only_user_role']) ? array_map('sanitize_text_field',$_POST['mep_member_only_user_role']) : array_map('sanitize_text_field',['all']);


		$off_days = isset($_POST['mptbm_off_days']) && is_array($_POST['mptbm_off_days']) ?  : [];


		$sku 					= isset($_POST['mep_event_sku']) ? sanitize_text_field($_POST['mep_event_sku']) : $post_id;
		$mep_rich_text_status 	= isset($_POST['mep_rich_text_status']) ? sanitize_text_field($_POST['mep_rich_text_status']) : 'enable';




		if ($mep_reset_status == 'on') {
			mep_reset_event_booking($post_id);
		}


		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		
		$date_format_arr = mep_date_format_list();
		$time_format_arr = mep_time_format_list();

		$current_global_date_format = mep_get_option('mep_global_date_format','datetime_setting_sec',$date_format);
		$current_global_time_format = mep_get_option('mep_global_time_format','datetime_setting_sec',$time_format);
	
		$current_global_custom_date_format = mep_get_option('mep_global_custom_date_format','datetime_setting_sec',$date_format);
		$current_global_custom_time_format = mep_get_option('mep_global_custom_time_format','datetime_setting_sec',$time_format);
	
		$current_global_timezone_display = mep_get_option('mep_global_timezone_display','datetime_setting_sec','no');


		$mep_event_date_format 			= isset($_POST['mep_event_date_format']) ? sanitize_text_field($_POST['mep_event_date_format']) : $current_global_date_format;

		$mep_event_time_format 			= isset($_POST['mep_event_time_format']) ? sanitize_text_field($_POST['mep_event_time_format']) : $current_global_time_format;

		$mep_event_custom_date_format 	= isset($_POST['mep_event_custom_date_format']) ? sanitize_text_field($_POST['mep_event_custom_date_format']) : $current_global_custom_date_format;

		$mep_custom_event_time_format 	= isset($_POST['mep_custom_event_time_format']) ? sanitize_text_field($_POST['mep_custom_event_time_format']) : $current_global_custom_time_format;

		$mep_time_zone_display 			= isset($_POST['mep_time_zone_display']) ? sanitize_text_field($_POST['mep_time_zone_display']) : $current_global_timezone_display;
		
		$mep_event_cc_email_text 			= isset($_POST['mep_event_cc_email_text']) ? wp_kses_post($_POST['mep_event_cc_email_text']) : '';

		if($mep_reg_status == 'on'){
			update_post_meta($post_id, 'mep_event_date_format', $mep_event_date_format);
			update_post_meta($post_id, 'mep_event_time_format', $mep_event_time_format);
			update_post_meta($post_id, 'mep_event_custom_date_format', $mep_event_custom_date_format);
			update_post_meta($post_id, 'mep_custom_event_time_format', $mep_custom_event_time_format);
			update_post_meta($post_id, 'mep_time_zone_display', $mep_time_zone_display);
		}



		update_post_meta($post_id, 'mep_event_cc_email_text', $mep_event_cc_email_text);
		update_post_meta($post_id, 'mep_show_end_datetime', $mep_show_end_datetime);
		update_post_meta($post_id, 'mep_rich_text_status', $mep_rich_text_status);
		update_post_meta($post_id, 'mep_available_seat', $mep_available_seat);
		update_post_meta($post_id, 'mep_reg_status', $mep_reg_status);
		update_post_meta($post_id, 'mep_show_advance_col_status', $mep_show_advance_col_status);
		update_post_meta($post_id, 'mep_enable_custom_dt_format', $mep_enable_custom_dt_format);
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
		update_post_meta($pid, '_sku', $sku);
		update_post_meta($pid, 'mep_member_only_user_role', $mep_member_only_user_role);

		if (isset($_POST['mep_event_type']) && mage_array_strip($_POST['mep_event_type'])) {
			$mep_event_type = 'online';
		} else {
			$mep_event_type = 'offline';
		}
		if (isset($_POST['mep_member_only_event']) && mage_array_strip($_POST['mep_member_only_event'])) {
			$mep_event_member_type = 'member_only';
		} else {
			$mep_event_member_type = 'for_all';
		}
		update_post_meta($pid, 'mep_member_only_event', $mep_event_member_type);
		update_post_meta($pid, 'mep_event_type', $mep_event_type);
		$mp_event_virtual_type_des = isset($_POST['mp_event_virtual_type_des']) ? htmlspecialchars(mage_array_strip($_POST['mp_event_virtual_type_des']))  : "";
		update_post_meta($pid, 'mp_event_virtual_type_des', $mp_event_virtual_type_des);


		$_mdate = apply_filters('mep_more_date_arr_save', $mdate);

		if (!empty($_mdate) && $_mdate != $oldm) {
			update_post_meta($post_id, 'mep_event_more_date', $_mdate);
		} elseif (empty($_mdate) && $oldm) {
			delete_post_meta($post_id, 'mep_event_more_date', $oldm);
		}
	}
}