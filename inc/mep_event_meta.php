<?php

use Sabberworm\CSS\Value\Value;

	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.






	class MP_Event_All_Info_In_One {
		public function __construct() {
			add_action('add_meta_boxes', array($this, 'mp_event_all_info_in_tab'));
			// add_action('wp_ajax_mep_reset_booking', [$this,'mep_reset_booking_callback']);
			// add_action('wp_ajax_nopriv_mep_reset_booking', [$this,'mep_reset_booking_callback']);
		}
		// public function mep_reset_booking_callback() {
		// 	echo $_POST['post_id'];
		// 	if(isset($_POST['post_id'])){

		// 		mep_reset_event_booking($_POST['post_id']);
		// 		wp_send_json_success(__("Successfully Booking Reset ", 'mage-eventpress'));
		// 		die;
		// 	}
			
		// }
		public function mp_event_all_info_in_tab() {
			$event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
			add_meta_box('mp_event_all_info_in_tab', __('<i class="fas fa-info-circle"></i> ' . $event_label . ' Information : ', 'mage-eventpress') . get_the_title(get_the_id()), array($this, 'mp_event_all_in_tab'), 'mep_events', 'normal', 'high');
			//add_meta_box('mep-event-template', __('Template bv', 'mage-eventpress'), array($this, 'mep_event_template_meta_box_cb'), 'mep_events', 'side', 'low');
		}
		public function mp_event_all_in_tab() {
			$event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
			$post_id = get_the_id();
			$event_type 		= get_post_meta($post_id, 'mep_event_type', true);
			$mep_reg_status = get_post_meta($post_id, 'mep_reg_status', true);
			$mep_reg_status = $mep_reg_status?$mep_reg_status:'on';
			wp_nonce_field('mpwem_type_nonce', 'mpwem_type_nonce');
			?>
            <div class="mp_event_all_meta_in_tab mp_event_tab_area">
                <div class="mp_tab_menu">
                    <ul>
						<?php do_action('mep_admin_event_details_before_tab_name_location', $post_id); ?>
                        <li data-target-tabs="#mp_event_venue">
                            <i class="fas fa-map-marker-alt"></i><?php esc_html_e('Venue/Location', 'mage-eventpress'); ?>
                        </li>
						<?php do_action('mep_admin_event_details_after_tab_name_location', $post_id); ?>
                        <li data-target-tabs="#mp_ticket_type_pricing">
                            <i class="fas fa-file-invoice-dollar"></i><?php esc_html_e('Ticket & Pricing', 'mage-eventpress'); ?>
                        </li>
						<?php do_action('mep_admin_event_details_before_tab_name_ticket_type', $post_id); ?>
                        <li data-target-tabs="#mp_event_time">
                            <i class="far fa-calendar-alt"></i><?php esc_html_e('Date & Time', 'mage-eventpress'); ?>
                        </li>
						<?php do_action('mep_admin_event_details_before_tab_name_date_time', $post_id); ?>
                        <li data-target-tabs="#mp_event_settings">
                            <i class="fas fa-cogs"></i><?php esc_html_e('Settings', 'mage-eventpress'); ?>
                        </li>
						<?php do_action('mep_admin_event_details_before_tab_name_settings', $post_id); ?>
						<?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
                            <li data-target-tabs="#mp_event_tax_settings">
                                <i class="fas fa-hand-holding-usd"></i><?php esc_html_e('Tax', 'mage-eventpress'); ?>
                            </li>
						<?php } ?>
						<?php do_action('mep_admin_event_details_before_tab_name_tax', $post_id); ?>
                        <li data-target-tabs="#mp_event_rich_text">
                            <i class="fas fa-search-location"></i><?php esc_html_e('SEO Content', 'mage-eventpress'); ?>
                        </li>
                        
						<?php do_action('mep_admin_event_details_before_tab_name_rich_text', $post_id); ?>
						<?php do_action('mp_event_all_in_tab_menu'); ?>

						<?php do_action('mep_admin_event_details_end_of_tab_name', $post_id); ?>
                    </ul>
                </div>
                <div class="mp_tab_details">
					
					<!-- =====================Tab event type online/offline=============  -->
					<?php do_action('mep_admin_event_details_before_tab_details_location', $post_id); ?>
                    <div class="mp_tab_item active" data-tab-item="#mp_event_venue">
						<h3><?php esc_html_e('Vanue/Location Settings','mage-eventpress') ?></h3>
						<p><?php esc_html_e('Configure Your Venue/Location Settings Here','mage-eventpress') ?></p>
						
						<section class="bg-light">
							<h2><?php esc_html_e('General Settings','mage-eventpress') ?></h2>
							<span><?php esc_html_e('Configure Event Locations and Virtual Venues','mage-eventpress') ?></span>
						</section>

						<?php do_action('mep_event_tab_before_location', $post_id); ?>
						
						<?php $this->event_online_enable($post_id); ?>
						<?php $this->mp_event_venue($post_id); ?>
                        
						<?php do_action('mep_event_tab_after_location'); ?>
                    </div>
					<?php do_action('mep_admin_event_details_after_tab_details_location', $post_id); ?>
					<!-- =====================Tab ticket and pricing=============  -->
                    <div class="mp_tab_item" data-tab-item="#mp_ticket_type_pricing">
						<h3><?php esc_html_e('Ticket & Pricing Settings','mage-eventpress') ?></h3>
						<p><?php esc_html_e('Configure Your Ticket & Pricing Settings Here','mage-eventpress') ?></p>
						
						<section class="bg-light">
							<h2><?php esc_html_e('General Settings','mage-eventpress') ?></h2>
							<span><?php esc_html_e('Configure Event Locations and Virtual Venues','mage-eventpress') ?></span>
						</section>
						
						<?php do_action('mep_event_tab_before_ticket_pricing', $post_id); ?>

						<?php $this->event_view_shortcode($post_id); ?>
						<?php $this->registration_on_off($post_id); ?>

                        <div id='mep_ticket_type_setting_sec' style="display:<?php echo esc_attr($mep_reg_status == 'on' ? 'block' : 'none'); ?>">
							<section class="bg-light" style="margin-top: 20px;">
								<h2><?php esc_html_e('Ticket Type List','mage-eventpress') ?></h2>
								<span><?php esc_html_e('Configure Ticket Type','mage-eventpress') ?></span>
							</section>
							<?php $this->mep_event_ticket_type($post_id); ?>

							<section class="bg-light" style="margin-top: 20px;">
								<h2><?php esc_html_e('Extra Service Area','mage-eventpress') ?></h2>
								<span><?php esc_html_e('Configure Extra Service','mage-eventpress') ?></span>
							</section>
							<?php $this->mep_event_extra_price_option($post_id); ?>
							<?php $this->mep_event_pro_purchase_notice($post_id); ?>
                        </div>
						<?php do_action('mep_event_tab_after_ticket_pricing'); ?>
                    </div>
					<!-- =====================Tab ticket and pricing=============  -->
					<?php do_action('mep_admin_event_details_after_tab_details_ticket_type', $post_id); ?>
					<?php do_action('add_mep_date_time_tab', $post_id); ?>
					<?php do_action('mep_admin_event_details_after_tab_details_date_time', $post_id); ?>
                    <div class="mp_tab_item" data-tab-item="#mp_event_rich_text">
						<h3><?php echo esc_html($event_label); esc_html_e('Rich Texts for SEO & Google Schema Text', 'mage-eventpress'); ?></h3>
						<p><?php esc_html_e('Configure Your Settings Here','mage-eventpress') ?></p>

						<?php $this->mp_event_rich_text($post_id); ?>
						<?php do_action('mep_event_tab_after_rich_text'); ?>
                    </div>
					<?php do_action('mep_admin_event_details_after_tab_details_rich_text', $post_id); ?>
                    <div class="mp_tab_item" data-tab-item="#mp_event_settings">
						
						<h3><?php echo esc_html($event_label); esc_html_e(' Settings :', 'mage-eventpress'); ?></h3>
						<p><?php esc_html_e('Configure Your Settings Here','mage-eventpress') ?></p>

						<?php $this->mp_event_settings($post_id); ?>
						<?php do_action('mep_event_tab_after_settings'); ?>
                    </div>
					<?php do_action('mep_admin_event_details_after_tab_details_settings', $post_id); ?>
					<?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
                        <div class="mp_tab_item" data-tab-item="#mp_event_tax_settings">
           					<h3><?php echo esc_html($event_label); esc_html_e('Tax Settings', 'mage-eventpress'); ?></h3>
							<p><?php esc_html_e('Configure Your Settings Here','mage-eventpress') ?></p>

							<?php $this->mp_event_tax($post_id); ?>
							<?php do_action('mep_event_tab_after_tax_settings'); ?>
                        </div>
					<?php } ?>
                   
					<?php do_action('mp_event_all_in_tab_item', $post_id); ?>
					<?php
						do_action('mep_admin_event_details_end_of_tab_details', $post_id); ?>
                    <p style="font-size: 10px;text-align: right;position: absolute;bottom: -6px;right: 14px;">
                        #WC:<?php echo get_post_meta($post_id, 'link_wc_product', true); ?>
                    </p>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(function ($) {
                    $("#mp_event_all_info_in_tab").parent().removeClass('meta-box-sortables');
                });
            </script>
			<?php
		}

		public function registration_on_off($post_id){
			wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
			$mep_reg_status = get_post_meta($post_id, 'mep_reg_status', true);
			$mep_reg_status= $mep_reg_status?$mep_reg_status:'on';
			?>
			<section>
				<div class="mpev-label">
					<div>
						<h2><?php esc_html_e('Registration Off/On:', 'mage-eventpress'); ?></h2>
						<span><?php esc_html_e('Registration Off/On:', 'mage-eventpress'); ?></span>
					</div>
					<label class="mpev-switch">
						<input type="checkbox" name="mep_reg_status" value="<?php echo esc_attr($mep_reg_status); ?>" <?php echo esc_attr(($mep_reg_status=='on')?'checked':''); ?> data-collapse-target="#mep_ticket_type_setting_sec" data-close-target="#" data-toggle-values="on,off">
						<span class="mpev-slider"></span>
					</label>
				</div>
			</section>
			<?php
		}
		public function event_view_shortcode($post_id){
			?>
			<section>
				<label class="mpev-label">
					<div style="width: 50%;">
						<h2><?php _e('Add To Cart Form Shortcode','mage-eventpress'); ?></h2>
						<span><?php _e('If you want to display the ticket type list with an add-to-cart button on any post or page of your website, simply copy the shortcode and paste it where desired.','mage-eventpress'); ?></span>
					</div>
					<code> [event-add-cart-section event="<?php echo $post_id; ?>"]</code>
				</label>
			</section>
			<?php
		}
		public function event_online_enable($post_id){
			$event_label        = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
			$event_type 		= get_post_meta($post_id, 'mep_event_type', true);
			$event_member_type 	= get_post_meta($post_id, 'mep_member_only_event', true);   
			$description 		= html_entity_decode(get_post_meta($post_id, 'mp_event_virtual_type_des', true));
			$checked 			= ($event_type == 'online') ? 'online' : '';
			?>
			<section>
				<div class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Online/Virtual ', 'mage-eventpress'); echo esc_html($event_label . '?');  ?> (No/Yes)</span></h2>
						<span><?php _e('If your event is online or virtual, please ensure that this option is enabled.','mage-eventpress'); ?></span>
					</div>
					<label class="mpev-switch">
						<input type="checkbox" name="mep_event_type" value="<?php echo esc_attr($checked); ?>" <?php echo esc_attr(($checked=='online')?'checked':''); ?> data-collapse-target="#mpev-online-event" data-close-target="#mpev-close-online-event" data-toggle-values="online,offline">
						<span class="mpev-slider"></span>
					</label>
				</div>
			</section>

			<?php do_action('mep_event_details_before_virtual_event_info_text_box',$post_id); ?>

			<section class="mp_event_virtual_type_des" id='mpev-online-event' style="display:<?php echo ($event_type == 'online') ? esc_attr('block') : esc_attr('none'); ?>">
				<p><?php esc_html_e('Please enter your virtual event joining details in the form below. This information will be sent to the buyer along with a confirmation email.', 'mage-eventpress') ?></p>
				<br>
				<?php wp_editor(html_entity_decode(nl2br($description)), 'mp_event_virtual_type_des'); ?>
			</section>

			<?php do_action('mep_event_details_after_virtual_event_info_text_box',$post_id); ?>

			<?php
		}

		public function is_gutenberg_active() {
			$gutenberg = false;
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
		public function mp_event_venue($post_id) {
			$event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
			$values = get_post_custom($post_id);
			$user_api = mep_get_option('google-map-api', 'general_setting_sec', '');
			$map_type = mep_get_option('mep_google_map_type', 'general_setting_sec', 'iframe');
			$mep_org_address = array_key_exists('mep_org_address', $values) ? $values['mep_org_address'][0] : 0;
			$map_visible = array_key_exists('mep_sgm', $values) ? $values['mep_sgm'][0] : 0;
			$author_id = get_post_field('post_author', $post_id);
			$event_type 		= get_post_meta($post_id, 'mep_event_type', true);
			$organizer = [ 
					$event_label.__(' Details'),
					__('Organizer'),
				];
			if ($this->is_gutenberg_active()) { ?>
                <input type="hidden" name="post_author_gutenberg" value="<?php echo esc_attr($author_id); ?>">
			<?php }
			?>
			<div class='mep_event_tab_location_content' id='mpev-close-online-event' style="display:<?php echo ($event_type == 'online') ? esc_attr('none') : esc_attr('block'); ?>">
				<section>
					<label class="mpev-label">
						<div>
							<h2><?php esc_html_e(" Location Source:", "mage-eventpress"); ?></h2>
							<span><?php esc_html_e('If you have saved organizer details, please select the "Organizer" option. Please note that if you select "Organizer" and have not checked the organizer from the Event Organizer list on the right sidebar, the Event Location section will not populate on the front end.', 'mage-eventpress'); ?></span>
						</div>
						<select class="mp_formControl" name="mep_org_address" class='mep_org_address_list' id='mep_org_address_list'>
							<?php foreach( $organizer as $key => $value): ?>
								<option value="<?php echo esc_attr($key); ?>" <?php echo ($mep_org_address == $key) ? esc_attr('selected') : ''; ?> > <?php esc_html_e($value); ?> </option>
							<?php endforeach; ?>
						</select>
					</label>
				</section>
				<section class="mp_event_address">
					<table>
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
				</section>
				<section>
					<div class="mpev-label">
						<div>
							<h2><?php esc_html_e('Show Google Map', 'mage-eventpress'); ?></h2>
							<span><?php esc_html_e('Show an interactive Google Map on your website, letting users easily explore and find locations.','mage-eventpress'); ?></span>
						</div>
						<label class="mpev-switch">
							<input type="checkbox" name="mep_sgm" value="<?php echo esc_attr($map_visible); ?>" <?php echo esc_attr(($map_visible==1)?'checked':''); ?> data-collapse-target="#mpev-show-map" data-close-target="#mpev-close-map" data-toggle-values="1,0">
							<span class="mpev-slider"></span>
						</label>
					</div>
				</section>
				<section class="mp_form_area" id="mpev-show-map" style="display:<?php echo ($map_visible == 1) ? esc_attr('block') : esc_attr('none'); ?>">
					<div class="mp_form_item">
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
									<input id="pac-input" name='location_name' value=''/>
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
								<?php esc_html_e('No Google MAP API Key Found. Please enter API KEY', 'mage-eventpress'); ?> <a href="<?php echo get_site_url() . esc_url('/wp-admin/options-general.php?page=mep_event_settings_page'); ?>"><?php esc_html_e('Here', 'mage-eventpress'); ?></a></span>
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
										google.maps.event.addListener(marker, 'dragend', function () {
											document.getElementsByName('latitude')[0].value = marker.getPosition().lat();
											document.getElementsByName('longitude')[0].value = marker.getPosition().lng();
										})
										autocomplete.addListener('place_changed', function () {
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
				</section>

				<script type="text/javascript">
					jQuery('[name="mep_org_address"]').change(function () {
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
					jQuery('[name="mep_location_venue"]').keypress(function () {
						let location = jQuery(this).val();
						if (location === '') {
							// alert('Please Enter Location First');
						} else {
							jQuery('#show_gmap').html('<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=' + location + '&t=&z=19&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>')
						}
					})
				</script>
			</div>
			<?php
		}
		public function mep_event_ticket_type($post_id) {
			$col_display = get_post_meta($post_id, 'mep_show_advance_col_status', true);
			$col_display = $col_display?$col_display:'off';
			$mep_event_ticket_type = get_post_meta($post_id, 'mep_event_ticket_type', true);
			$values = get_post_custom($post_id);
			wp_nonce_field('mep_event_ticket_type_nonce', 'mep_event_ticket_type_nonce');
			wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
			$css_value = 'none';
			if ($col_display == 'on') {
				$css_value = 'table-cell';
			}
			else{
				$css_value = 'none';
			}
			?>
			<section>
				<div class="mpev-label">
					<div>
						<h2><?php esc_html_e('Show Advanced Column:', 'mage-eventpress'); ?></h2>
						<span><?php esc_html_e('Ticket Type List', 'mage-eventpress'); ?></span>
					</div>
					<label class="mpev-switch">
						<input type="checkbox" name="mep_show_advance_col_status" value="<?php echo esc_attr($col_display); ?>" <?php echo esc_attr(($col_display=='on')?'checked':''); ?> data-collapse-target="#hide_column" data-toggle-values="on,off">
						<span class="mpev-slider"></span>
					</label>
				</div>
			</section>
			<style>
				.mep_hide_on_load{
					display:<?php echo $css_value; ?>;
				}
			</style>
			<?php do_action('mep_add_category_display',$post_id); ?>
            <section class="mp_ticket_type_table">
                <div class="mp_ticket_type_table_auto">
					<table id="repeatable-fieldset-one-t">
						<thead>
						<tr>
							<th style="min-width: 60px;" title="<?php esc_attr_e('Ticket Type Name', 'mage-eventpress'); ?>"><?php esc_html_e('Ticket', 'mage-eventpress'); ?></th>
							<th style="min-width: 60px;" title="<?php esc_attr_e('Ticket Type Details', 'mage-eventpress'); ?>"><?php esc_html_e('Short Desc.', 'mage-eventpress'); ?></th>
							<th style="min-width: 40px;" title="<?php esc_attr_e('Ticket Price', 'mage-eventpress'); ?>"><?php esc_html_e('Price', 'mage-eventpress'); ?></th>
							<?php do_action('mep_pricing_table_head_after_price_col'); ?>
							<th style="min-width: 40px;" title="<?php esc_attr_e('Available Qty', 'mage-eventpress'); ?>"><?php esc_html_e('Capacity', 'mage-eventpress'); ?>
							</th>
							<th class='mep_hide_on_load' style="min-width: 40px;" title="<?php esc_attr_e('Default Qty', 'mage-eventpress'); ?>"><?php esc_html_e('Default Qty', 'mage-eventpress'); ?></th>
							<th class='mep_hide_on_load' style="min-width: 40px;" title="<?php esc_attr_e('Reserve Qty', 'mage-eventpress'); ?>"><?php esc_html_e('Reserve Qty', 'mage-eventpress'); ?>
								<?php do_action('add_extra_field_icon', $post_id); ?>
							</th>
							<?php do_action('mep_add_extra_column'); ?>
							<th class='mep_hide_on_load' style="min-width: 60px;" title="<?php esc_attr_e('Sale End Date', 'mage-eventpress'); ?>"><?php esc_html_e('Sale End Date', 'mage-eventpress'); ?></th>
							<th class='mep_hide_on_load' style="min-width: 60px;" title="<?php esc_attr_e('Sale End Time', 'mage-eventpress'); ?>"><?php esc_html_e('Sale End Time', 'mage-eventpress'); ?></th>
							<th style="min-width: 60px;" title="<?php esc_attr_e('Qty Box Type', 'mage-eventpress'); ?>"><?php esc_html_e('Qty Box', 'mage-eventpress'); ?></th>
							<th style="min-width: 60px;"><?php esc_html_e('Action', 'mage-eventpress'); ?>
							</th>
						</tr>
						</thead>
						<tbody class="mp_event_type_sortable">
						<?php
							if ($mep_event_ticket_type) :
								$count = 0;
								foreach ($mep_event_ticket_type as $field) {
									$qty_t_type = array_key_exists('option_qty_t_type', $field) ? esc_attr($field['option_qty_t_type']) : 'inputbox';
									$option_details = array_key_exists('option_details_t', $field) ? esc_attr($field['option_details_t']) : '';
									$option_name = array_key_exists('option_name_t', $field) ? esc_attr($field['option_name_t']) : '';
									$option_name_text = preg_replace("/[{}()<>+ ]/", '_', $option_name) . '_' . $post_id;
									$option_price = array_key_exists('option_price_t', $field) ? esc_attr($field['option_price_t']) : '';
									$option_qty = array_key_exists('option_qty_t', $field) ? esc_attr($field['option_qty_t']) : 0;
									$option_default_qty = array_key_exists('option_default_qty_t', $field) ? esc_attr($field['option_default_qty_t']) : 0;
									$option_rsv_qty = array_key_exists('option_rsv_t', $field) ? esc_attr($field['option_rsv_t']) : 0;
									$count++;
									?>
									<tr class="data_required">
										<td>
											<input type="hidden" name="hidden_option_name_t[]" value="<?php echo esc_attr($option_name_text); ?>"/>
											<input data-required="" type="text" class="mp_formControl" name="option_name_t[]" placeholder="Ex: Adult" value="<?php echo esc_attr($option_name); ?>"/>
										</td>
										<td><input type="text" class="mp_formControl" name="option_details_t[]" placeholder="" value="<?php echo esc_attr($option_details); ?>"/></td>
										<td><input type="number" size="4" pattern="[0-9]*" step="0.001" class="mp_formControl" name="option_price_t[]" placeholder="Ex: 10" value="<?php echo esc_attr($option_price); ?>"/></td>
										<?php do_action('mep_pricing_table_data_after_price_col', $field, $post_id); ?>
										<td><input type="number" size="4" pattern="[0-9]*" step="1" class="mp_formControl" name="option_qty_t[]" placeholder="Ex: 500" value="<?php echo esc_attr($option_qty) ?>"/></td>
										<td class='mep_hide_on_load'><input type="number" size="2" pattern="[0-9]*" step="1" class="mp_formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value="<?php echo esc_attr($option_default_qty) ?>"/></td>
										<td class='mep_hide_on_load'><input type="number" class="mp_formControl" name="option_rsv_t[]" placeholder="Ex: 5" value="<?php echo esc_attr($option_rsv_qty); ?>"/></td>
										<?php do_action('mep_add_extra_input_box', $field, $count) ?>
										<td class='mep_hide_on_load'>
											<span class="sell_expire_date">
												<input type="date" id="ticket_sale_start_date" class="mp_formControl" value='<?php if (array_key_exists('option_sale_end_date_t', $field) && $field['option_sale_end_date_t'] != '') {
													echo esc_attr(date('Y-m-d', strtotime($field['option_sale_end_date_t'])));
												} ?>' name="option_sale_end_date[]"/>
											</span>
										</td>
										<td class='mep_hide_on_load'>
											<span class="sell_expire_date">
												<input type="time" id="ticket_sale_start_time" class="mp_formControl" value='<?php if (array_key_exists('option_sale_end_date_t', $field) && $field['option_sale_end_date_t'] != '') {
													echo esc_attr(date('H:i', strtotime($field['option_sale_end_date_t'])));
												} ?>' name="option_sale_end_time[]"/>
											</span>
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
						<tr class="empty-row-t screen-reader-text data_required">
							<td><input data-required="" type="text" class="mp_formControl" name="option_name_t[]" placeholder="Ex: Adult"/></td>
							<td><input type="text" class="mp_formControl" name="option_details_t[]" placeholder=""/></td>
							<td><input type="number" size="4" pattern="[0-9]*" class="mp_formControl" step="0.001" name="option_price_t[]" placeholder="Ex: 10" value=""/></td>
							<?php do_action('mep_pricing_table_empty_after_price_col'); ?>
							<td><input type="number" size="4" pattern="[0-9]*" step="1" class="mp_formControl" name="option_qty_t[]" placeholder="Ex: 15" value=""/></td>
							<td class='mep_hide_on_load'><input type="number" size="2" pattern="[0-9]*" class="mp_formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value=""/></td>
							<?php $option_rsv_t = '<td class="mep_hide_on_load"><input type="number" class="mp_formControl" name="option_rsv_t[]" placeholder="Ex: 5" value=""/></td>'; ?>
							<?php echo apply_filters('mep_add_field_to_ticket_type', mep_esc_html($option_rsv_t)); ?>
							<?php do_action('mep_add_extra_column_empty'); ?>
							<td class="mep_hide_on_load">
								<div class="sell_expire_date">
									<input type="date" id="ticket_sale_start_date" value='' name="option_sale_end_date[]"/>
								</div>
							</td>
							<td class="mep_hide_on_load">
								<div class="sell_expire_date">
									<input type="time" id="ticket_sale_start_time" value='' name="option_sale_end_time[]"/>
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
					<br>
					<button id="add-row-t" class="button" type="button"><i class="fas fa-plus-circle"></i> <?php esc_html_e('Add New Ticket Type', 'mage-eventpress'); ?></button>
            </section>

			<?php
		}

		public function mep_event_pro_purchase_notice($post_id) {
			?>
			<section class="bg-light" style="margin-top: 20px;">
				<h2><?php esc_html_e('Documentaion Links','mage-eventpress') ?></h2>
				<span><?php esc_html_e('Get Documentation','mage-eventpress') ?></span>
			</section>
			<section>
				<?php if(!mep_check_plugin_installed('woocommerce-event-manager-addon-form-builder/addon-builder.php') ) : ?>
					<p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Get Individual Attendee  Information, PDF Ticketing and Email Function with <a href='https://mage-people.com/product/mage-woo-event-booking-manager-pro/' target='_blank'>Event Manager Pro</a>", 'mage-eventpress'); ?></p>
				<?php endif; 
				if(!mep_check_plugin_installed('woocommerce-event-manager-addon-global-quantity/global-quantity.php')): ?>
					<p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Setup Event Common QTY of All Ticket Type get <a href='https://mage-people.com/product/global-common-qty-addon-for-event-manager' target='_blank'>Global QTY Addon</a>", 'mage-eventpress'); ?></p>
				<?php endif; 
				if(!mep_check_plugin_installed('woocommerce-event-manager-addon-membership-price/membership-price.php')): ?>
					<p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Special Price Option for each user type or membership get <a href='https://mage-people.com/product/membership-pricing-for-event-manager-plugin' target='_blank'>Membership Pricing Addon</a>", 'mage-eventpress'); ?></p>
				<?php endif;
				if(!mep_check_plugin_installed('woocommerce-event-manager-min-max-quantity-addon/mep_min_max_qty.php')): ?>
					<p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Set maximum/minimum qty buying option with <a href='https://mage-people.com/product/event-max-min-quantity-limiting-addon-for-woocommerce-event-manager' target='_blank'>Max/Min Qty Addon</a>", 'mage-eventpress'); ?></p>
				<?php endif; ?>
				<p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e("Read Documentation <a href='https://docs.mage-people.com/woocommerce-event-manager/' target='_blank'>Read Documentation</a>", 'mage-eventpress'); ?></p>

			</section>
			<?php
		}

		public function mep_event_pro_purchase_link(){
			?>

			<?php
		}

		public function mep_event_extra_price_option($post_id) {
			
			$mep_events_extra_prices = get_post_meta($post_id, 'mep_events_extra_prices', true);
			wp_nonce_field('mep_events_extra_price_nonce', 'mep_events_extra_price_nonce');
			?>
            <section>
				<p><?php esc_html_e('Extra Service as Product that you can sell and it is not included on event package', 'mage-eventpress'); ?></p>
				<br>
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
									$qty_type = array_key_exists('option_qty_type', $field) ? esc_attr($field['option_qty_type']) : 'inputbox';
									?>
									<tr>
										<td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap" value="<?php if ($field['option_name'] != '') {
												echo esc_attr($field['option_name']);
											} ?>"/></td>
										<td><input type="number" step="0.001" class="mp_formControl" name="option_price[]" placeholder="Ex: 10" value="<?php if ($field['option_price'] != '') {
												echo esc_attr($field['option_price']);
											} else {
												echo '';
											} ?>"/></td>
										<td>
											<input type="number" class="mp_formControl" name="option_qty[]"
												placeholder="Ex: 100"
												value="<?php echo esc_attr(($field['option_qty'] != '') ? $field['option_qty'] : ''); ?>"/>
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
							<td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap"/></td>
							<td><input type="number" class="mp_formControl" step="0.001" name="option_price[]" placeholder="Ex: 10" value=""/></td>
							<td><input type="number" class="mp_formControl" name="option_qty[]" placeholder="Ex: 100" value=""/></td>
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
				<br>
				<button id="add-row" class="button"><i class="fas fa-plus-circle"></i> <?php esc_html_e('Add Extra Price', 'mage-eventpress'); ?></button>
				
			</section>
			<?php
		}
		public function mp_event_rich_text($post_id) {
			wp_nonce_field('mep_event_ricn_text_nonce', 'mep_event_ricn_text_nonce');
			$event_start_date = get_post_meta($post_id, 'event_start_datetime', true) ? get_post_meta($post_id, 'event_start_datetime', true) : '';
			$event_end_date = get_post_meta($post_id, 'event_end_datetime', true) ? get_post_meta($post_id, 'event_end_datetime', true) : '';
			$event_rt_status = get_post_meta($post_id, 'mep_rt_event_status', true) ? get_post_meta($post_id, 'mep_rt_event_status', true) : '';
			$event_rt_atdnce_mode = get_post_meta($post_id, 'mep_rt_event_attandence_mode', true) ? get_post_meta($post_id, 'mep_rt_event_attandence_mode', true) : '';
			$event_rt_prv_date = get_post_meta($post_id, 'mep_rt_event_prvdate', true) ? get_post_meta($post_id, 'mep_rt_event_prvdate', true) : $event_start_date;
			$rt_status = get_post_meta($post_id, 'mep_rich_text_status', true);
			?>
			<section class="bg-light">
				<h2><?php esc_html_e('Tax Settings','mage-eventpress') ?></h2>
				<span><?php esc_html_e('Configure Event Tax','mage-eventpress') ?></span>
			</section>
			<section>
				<label class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Rich Text Status', 'mage-eventpress'); ?></span></h2>
						<span><?php _e('You can change the date and time format by going to the settings','mage-eventpress'); ?></span>
					</div>
					<select id="mep_rich_text_status" name="mep_rich_text_status">
						<option value="enable" <?php echo $rt_status=='eanble'?'selected':''; ?>> <?php echo esc_html__('Enable','mage-eventpress'); ?></option>
						<option value="disable" <?php echo $rt_status=='disable'?'selected':''; ?>> <?php echo esc_html__('Disable','mage-eventpress'); ?></option>
					</select>
				</label>
			</section>
			<section id='mep_rich_text_table' style="display:<?php echo ($rt_status == 'enable')? 'block':'none'; ?>">
				<table>
					<tr>
						<td><span><?php esc_html_e('Type :', 'mage-eventpress'); ?></span></td>
						<td colspan="3"><?php esc_html_e('Event', 'mage-eventpress'); ?></td>
					</tr>
					<tr>
						<td><span><?php esc_html_e('Name :', 'mage-eventpress'); ?></span></td>
						<td colspan="3"><?php echo get_the_title($post_id); ?></td>
					</tr>
					<tr>
						<td><span><?php esc_html_e('Start Date :', 'mage-eventpress'); ?></span></td>
						<td colspan="3"><?php echo esc_attr($event_start_date) ? get_mep_datetime($event_start_date, 'date-time-text') : ''; ?></td>
					</tr>
					<tr>
						<td><span><?php _e('End Date :', 'mage-eventpress'); ?></span></td>
						<td colspan="3"><?php echo esc_attr($event_end_date) ? get_mep_datetime($event_end_date, 'date-time-text') : ''; ?></td>
					</tr>
					<tr>
						<td><span><?php esc_html_e('Event Status:', 'mage-eventpress'); ?></span></td>
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
						<td><span><?php esc_html_e('Event Attendance Mode:', 'mage-eventpress'); ?></span></td>
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
						<td><span><?php esc_html_e('Previous Start Date:', 'mage-eventpress'); ?></span></td>
						<td colspan="3">
							<label>
								<input type='text' class="mp_formControl" name="mep_rt_event_prvdate" value='<?php echo esc_attr($event_rt_prv_date); ?>'/>
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
			</section>
			<?php
		}
		public function mp_event_settings($post_id) {
			$event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
			?>
			<section class="bg-light">
				<h2><?php esc_html_e('General Settings','mage-eventpress') ?></h2>
				<span><?php esc_html_e('Configure Event Locations and Virtual Venues','mage-eventpress') ?></span>
			</section>
            <?php $this->mp_event_reg_status($post_id); ?>
			<table>
				<?php
					$this->mp_event_enddatetime_status($post_id);
					$this->mp_event_available_seat_status($post_id);
					$this->mp_event_reset_booking_count($post_id);
					do_action('mp_event_switching_button_hook', $post_id);
					$this->mp_event_speaker_ticket_type($post_id);
				?>
            </table>
			<?php
		}
		public function mp_event_enddatetime_status($post_id) {
			$values = get_post_custom($post_id);
			// wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
			
			$mep_show_end_datetime = get_post_meta($post_id,'mep_show_end_datetime',true);
			$mep_show_end_datetime = $mep_show_end_datetime?$mep_show_end_datetime:'yes';

			?>
			<section>
				<div class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Display End Datetime', 'mage-eventpress'); ?></span></h2>
						<span><?php _e('You can change the date and time format by going to the settings','mage-eventpress'); ?></span>
					</div>
					<label class="mpev-switch">
						<input type="checkbox" name="mep_show_end_datetime" value="<?php echo esc_attr($mep_show_end_datetime); ?>" <?php echo esc_attr(($mep_show_end_datetime=='yes')?'checked':''); ?> data-toggle-values="yes,no">
						<span class="mpev-slider"></span>
					</label>
				</div>
			</section>
			<?php
		}
		public function mp_event_reg_status($post_id) {
			?>
			<section>
				<label class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Event SKU No', 'mage-eventpress');?></h2>
						<span><?php _e('Event SKU No','mage-eventpress'); ?></span>
					</div>
					<input class="mep_input_text" type="text" name="mep_event_sku" value="<?php echo get_post_meta($post_id, '_sku', true); ?>"/>
				</label>
			</section>
			<?php
		}
		public function mp_event_available_seat_status($post_id) {
			$values = get_post_custom($post_id);
			wp_nonce_field('mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce');
			$seat_checked = get_post_meta($post_id,'mep_available_seat',true);
			$seat_checked = $seat_checked? $seat_checked:'no';
			?>
			<section>
				<div class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Show Available Seat?', 'mage-eventpress');  ?></h2>
						<span><?php _e('You can change the date and time format by going to the settings','mage-eventpress'); ?></span>
					</div>
					<label class="mpev-switch">
						<input type="checkbox" name="mep_available_seat" value="<?php echo esc_attr($seat_checked); ?>" <?php echo esc_attr(($seat_checked=='on')?'checked':''); ?> data-toggle-values="on,off">
						<span class="mpev-slider"></span>
					</label>
				</div>
			</section>
			<?php
		}
		public function mp_event_reset_booking_count($post_id) {
			?>
			<section>
				<label class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Reset Booking Count', 'mage-eventpress'); ?></span></h2>
						<span><?php _e('If you reset this count, all booking information will be removed, including the attendee list. This action is irreversible, so please be sure before you proceed.','mage-eventpress'); ?></span>
					</div>
					<div class="mpStyle">
						<div class="_dFlex_justifyEnd">
							<button class="button" type="button" id="mep-reset-booking" data-post-id='<?php echo esc_html($post_id); ?>'>
							<input type="hidden" class="hidden" id='mep-reset-booking-nonce' name='reset-booking-nonce' value="<?php echo wp_create_nonce('mep-ajax-reset-booking-nonce'); ?>">	
							<span class="fas fa-refresh"></span>
								<span class="mL_xs"><?php esc_html_e('Reset Booking', 'mage-eventpress'); ?></span>
							</button>
							
						</div>
						<div class="_dFlex_justifyEnd" id="mp-reset-status"></div>
					</div>
				</label>
			</section>            
			<?php
		}
		public function mp_event_speaker_ticket_type($post_id) {
			$event_label = mep_get_option('mep_event_label', 'general_setting_sec', 'Events');
			$event_type = get_post_meta($post_id, 'mep_event_type', true);
			$event_member_type = get_post_meta($post_id, 'mep_member_only_event', true);
			$saved_user_role = get_post_meta($post_id, 'mep_member_only_user_role', true) ? get_post_meta($post_id, 'mep_member_only_user_role', true) : [];
			$description = html_entity_decode(get_post_meta($post_id, 'mp_event_virtual_type_des', true));
			$checked = ($event_type == 'online') ? 'checked' : '';

			?>
			<section>
				<div class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Member Only Event?', 'mage-eventpress'); ?></span></h2>
						<span><?php _e('You can change the date and time format by going to the settings','mage-eventpress'); ?></span>
					</div>
					<label class="mpev-switch">
						<input type="checkbox" name="mep_member_only_event" value="<?php echo esc_attr($event_member_type); ?>" <?php echo esc_attr(($event_member_type=='member_only')?'checked':''); ?> data-collapse-target="#event_virtual_type" data-toggle-values="member_only,for_all">
						<span class="mpev-slider"></span>
					</label>
				</div>
			</section>
			<section id="event_virtual_type" style="display: <?php echo $event_member_type=='member_only'? 'block':'none'; ?>;">
				<label class="mpev-label">
					<div>
						<h2><?php _e('Select User Role','mage-eventpress'); ?></h2>
						<span><?php _e('Select User Role','mage-eventpress'); ?></span>
					</div>
					<select name='mep_member_only_user_role[]' multiple>
						<option value="all" <?php if (in_array('all', $saved_user_role)) {
							echo esc_attr('Selected');
						} ?>><?php esc_html_e('For Any Logged in user', 'mage-eventpress'); ?> </option>
						<?php echo mep_get_user_list($saved_user_role); ?>
					</select>
				</label>
			</section>

			<?php
		}
		public function mp_event_tax($post_id) {
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
			<section class="bg-light">
				<h2><?php esc_html_e('Tax Settings','mage-eventpress') ?></h2>
				<span><?php esc_html_e('Configure Event Tax','mage-eventpress') ?></span>
			</section>
			<section>
				<label class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Tax status', 'mage-eventpress'); ?></span></h2>
						<span><?php _e('Tax status','mage-eventpress'); ?></span>
					</div>
					<select class="" name="_tax_status">
						<option value="taxable" <?php echo ($tx_status == 'taxable') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Taxable', 'mage-eventpress'); ?></option>
						<option value="shipping" <?php echo ($tx_status == 'shipping') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Shipping only', 'mage-eventpress'); ?></option>
						<option value="none" <?php echo ($tx_status == 'none') ? esc_attr('selected') : ''; ?>><?php esc_html_e('None', 'mage-eventpress'); ?></option>
					</select>
				</label>
			</section>
			<section>
				<label class="mpev-label">
					<div>
						<h2><span><?php esc_html_e('Tax class', 'mage-eventpress'); ?></span></h2>
						<span><?php _e('In order to add a new tax class, please go to WooCommerce -> Settings -> Tax Area','mage-eventpress'); ?></span>
					</div>
					<select class="" name="_tax_class">
						<option value="standard" <?php echo ($tx_class == 'standard') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Standard', 'mage-eventpress'); ?></option>
						<?php mep_get_all_tax_list($tx_class); ?>
					</select>
				</label>
			</section>
			<?php
		}
		//side meta box
		public function mep_event_template_meta_box_cb($post) {
			$values = get_post_custom($post->ID);
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
	function mep_events_ticket_type_save($post_id) {
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
			$new = array();
			$names = $_POST['option_name_t'] ? mage_array_strip($_POST['option_name_t']) : array();
			$details = $_POST['option_details_t'] ? mage_array_strip($_POST['option_details_t']) : array();
			$ticket_price = $_POST['option_price_t'] ? mage_array_strip($_POST['option_price_t']) : array();
			$qty = $_POST['option_qty_t'] ? mage_array_strip($_POST['option_qty_t']) : array();
			$dflt_qty = $_POST['option_default_qty_t'] ? mage_array_strip($_POST['option_default_qty_t']) : array();
			$rsv = $_POST['option_rsv_t'] ? mage_array_strip($_POST['option_rsv_t']) : array();
			$qty_type = $_POST['option_qty_t_type'] ? mage_array_strip($_POST['option_qty_t_type']) : array();
			$sale_end_date = $_POST['option_sale_end_date'] ? mage_array_strip($_POST['option_sale_end_date']) : array();
			$sale_end_time = $_POST['option_sale_end_time'] ? mage_array_strip($_POST['option_sale_end_time']) : array();
			$count = count($names);
			for ($i = 0; $i < $count; $i++) {
				if ($names[$i] != '') :
					$new[$i]['option_name_t'] = stripslashes(strip_tags($names[$i]));
				endif;
				if ($details[$i] != '') :
					$new[$i]['option_details_t'] = stripslashes(strip_tags($details[$i]));
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
				if ($sale_end_date[$i] != '') :
					$new[$i]['option_sale_end_date'] = stripslashes(strip_tags($sale_end_date[$i]));
				endif;
				if ($sale_end_time[$i] != '') :
					$new[$i]['option_sale_end_time'] = stripslashes(strip_tags($sale_end_time[$i]));
				endif;
				if ($sale_end_date[$i] != '') :
					$new[$i]['option_sale_end_date_t'] = stripslashes(strip_tags($sale_end_date[$i] . ' ' . $sale_end_time[$i]));
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
	function mep_events_repeatable_meta_box_save($post_id) {
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
			$old = get_post_meta($post_id, 'mep_events_extra_prices', true);
			$new = array();
			$names = isset($_POST['option_name']) ? mage_array_strip($_POST['option_name']) : [];
			$urls = isset($_POST['option_price']) ? mage_array_strip($_POST['option_price']) : [];
			$qty = isset($_POST['option_qty']) ? mage_array_strip($_POST['option_qty']) : [];
			$qty_type = isset($_POST['option_qty_type']) ? mage_array_strip($_POST['option_qty_type']) : [];
			$order_id = 0;
			$count = count($names);
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
	function mep_event_meta_save($post_id) {
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
			$pid = $post_id;
			$oldm = get_post_meta($post_id, 'mep_event_more_date', true);
			$more_start_date = isset($_POST['event_more_start_date']) ? mage_array_strip($_POST['event_more_start_date']) : array();
			$more_start_time = isset($_POST['event_more_start_time']) ? mage_array_strip($_POST['event_more_start_time']) : '';
			$more_end_date = isset($_POST['event_more_end_date']) ? mage_array_strip($_POST['event_more_end_date']) : '';
			$more_end_time = isset($_POST['event_more_end_time']) ? mage_array_strip($_POST['event_more_end_time']) : '';
			$mdate = [];
			$mcount = count($more_start_date);
			for ($m = 0; $m < $mcount; $m++) {
				if ($more_start_date[$m] != '') :
					$mdate[$m]['event_more_start_date'] = stripslashes(sanitize_text_field($more_start_date[$m]));
					$mdate[$m]['event_more_start_time'] = stripslashes(sanitize_text_field($more_start_time[$m]));
					$mdate[$m]['event_more_end_date'] = stripslashes(sanitize_text_field($more_end_date[$m]));
					$mdate[$m]['event_more_end_time'] = stripslashes(sanitize_text_field($more_end_time[$m]));
				endif;
			}
			$event_rt_status = sanitize_text_field($_POST['mep_rt_event_status']);
			$event_rt_atdnce_mode = sanitize_text_field($_POST['mep_rt_event_attandence_mode']);
			$event_rt_prv_date = sanitize_text_field($_POST['mep_rt_event_prvdate']);
			$seat = 0;
			$rsvs = 0;
			$mep_location_venue = isset($_POST['mep_location_venue']) ? sanitize_text_field($_POST['mep_location_venue']) : "";
			$mep_street = isset($_POST['mep_street']) ? sanitize_text_field($_POST['mep_street']) : "";
			$mep_city = isset($_POST['mep_city']) ? sanitize_text_field($_POST['mep_city']) : "";
			$mep_state = isset($_POST['mep_state']) ? sanitize_text_field($_POST['mep_state']) : "";
			$mep_postcode = isset($_POST['mep_postcode']) ? sanitize_text_field($_POST['mep_postcode']) : "";
			$mep_country = isset($_POST['mep_country']) ? sanitize_text_field($_POST['mep_country']) : "";
			$mep_sgm = isset($_POST['mep_sgm']) ? sanitize_text_field($_POST['mep_sgm']) : "";
			$mep_org_address = isset($_POST['mep_org_address']) ? sanitize_text_field($_POST['mep_org_address']) : "";
			$_price = isset($_POST['_price']) ? sanitize_text_field($_POST['_price']) : "";
			$event_start_date = sanitize_text_field($_POST['event_start_date']);
			$event_start_time = sanitize_text_field($_POST['event_start_time']);
			$event_end_date = sanitize_text_field($_POST['event_end_date']);
			$event_end_time = sanitize_text_field($_POST['event_end_time']);
			$latitude = isset($_POST['latitude']) ? sanitize_text_field($_POST['latitude']) : "";
			$longitude = isset($_POST['latitude']) ? sanitize_text_field($_POST['longitude']) : "";
			$location_name = isset($_POST['location_name']) ? sanitize_text_field($_POST['location_name']) : "";
			$mep_full_name = isset($_POST['mep_full_name']) ? sanitize_text_field($_POST['mep_full_name']) : "";
			$mep_reg_email = isset($_POST['mep_reg_email']) ? sanitize_text_field($_POST['mep_reg_email']) : "";
			$mep_reg_phone = isset($_POST['mep_reg_phone']) ? sanitize_text_field($_POST['mep_reg_phone']) : "";
			$mep_reg_address = isset($_POST['mep_reg_address']) ? sanitize_text_field($_POST['mep_reg_address']) : "";
			$mep_reg_designation = isset($_POST['mep_reg_designation']) ? sanitize_text_field($_POST['mep_reg_designation']) : "";
			$mep_reg_website = isset($_POST['mep_reg_website']) ? sanitize_text_field($_POST['mep_reg_website']) : "";
			$mep_reg_veg = isset($_POST['mep_reg_veg']) ? sanitize_text_field($_POST['mep_reg_veg']) : "";
			$mep_reg_company = isset($_POST['mep_reg_company']) ? sanitize_text_field($_POST['mep_reg_company']) : "";
			$mep_reg_gender = isset($_POST['mep_reg_gender']) ? sanitize_text_field($_POST['mep_reg_gender']) : "";
			$mep_reg_tshirtsize = isset($_POST['mep_reg_tshirtsize']) ? sanitize_text_field($_POST['mep_reg_tshirtsize']) : "";
			$mep_reg_tshirtsize_list = isset($_POST['mep_reg_tshirtsize_list']) ? sanitize_text_field($_POST['mep_reg_tshirtsize_list']) : "";

			$mep_event_template_file_name = isset($_POST['mep_event_template']) && mep_isValidFilename($_POST['mep_event_template']) ? sanitize_file_name($_POST['mep_event_template']) : "default-theme.php";

			$mep_event_template = mep_template_file_validate($mep_event_template_file_name);
			$event_start_datetime = date('Y-m-d H:i:s', strtotime($event_start_date . ' ' . $event_start_time));
			$event_end_datetime = date('Y-m-d H:i:s', strtotime($event_end_date . ' ' . $event_end_time));
			$md = sizeof($mdate) > 0 ? end($mdate) : array();
			$event_expire_datetime = sizeof($md) > 0 ? date('Y-m-d H:i:s', strtotime($md['event_more_end_date'] . ' ' . $md['event_more_end_time'])) : $event_end_datetime;
			$mep_reg_status = isset($_POST['mep_reg_status']) ? sanitize_text_field($_POST['mep_reg_status']) : 'off';
			$mep_show_advance_col_status = isset($_POST['mep_show_advance_col_status']) ? sanitize_text_field($_POST['mep_show_advance_col_status']) : 'off';
			$mep_enable_custom_dt_format = isset($_POST['mep_enable_custom_dt_format']) ? sanitize_text_field($_POST['mep_enable_custom_dt_format']) : 'off';
			$mep_show_end_datetime = isset($_POST['mep_show_end_datetime']) ? sanitize_text_field($_POST['mep_show_end_datetime']) : 'no';
			
			$mep_available_seat = isset($_POST['mep_available_seat']) ? sanitize_text_field($_POST['mep_available_seat']) : 'off';
			$_tax_status = isset($_POST['_tax_status']) ? sanitize_text_field($_POST['_tax_status']) : 'none';
			$_tax_class = isset($_POST['_tax_class']) ? sanitize_text_field($_POST['_tax_class']) : '';
			$mep_member_only_user_role = isset($_POST['mep_member_only_user_role']) && is_array($_POST['mep_member_only_user_role']) ? array_map('sanitize_text_field', $_POST['mep_member_only_user_role']) : array_map('sanitize_text_field', ['all']);
			$off_days = isset($_POST['mptbm_off_days']) && is_array($_POST['mptbm_off_days']) ?: [];
			$sku = isset($_POST['mep_event_sku']) ? sanitize_text_field($_POST['mep_event_sku']) : $post_id;
			$mep_rich_text_status = isset($_POST['mep_rich_text_status']) ? sanitize_text_field($_POST['mep_rich_text_status']) : 'enable';			
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			$date_format_arr = mep_date_format_list();
			$time_format_arr = mep_time_format_list();
			$current_global_date_format = mep_get_option('mep_global_date_format', 'datetime_setting_sec', $date_format);
			$current_global_time_format = mep_get_option('mep_global_time_format', 'datetime_setting_sec', $time_format);
			$current_global_custom_date_format = mep_get_option('mep_global_custom_date_format', 'datetime_setting_sec', $date_format);
			$current_global_custom_time_format = mep_get_option('mep_global_custom_time_format', 'datetime_setting_sec', $time_format);
			$current_global_timezone_display = mep_get_option('mep_global_timezone_display', 'datetime_setting_sec', 'no');
			$mep_event_date_format = isset($_POST['mep_event_date_format']) ? sanitize_text_field($_POST['mep_event_date_format']) : $current_global_date_format;
			$mep_event_time_format = isset($_POST['mep_event_time_format']) ? sanitize_text_field($_POST['mep_event_time_format']) : $current_global_time_format;
			$mep_event_custom_date_format = isset($_POST['mep_event_custom_date_format']) ? sanitize_text_field($_POST['mep_event_custom_date_format']) : $current_global_custom_date_format;
			$mep_custom_event_time_format = isset($_POST['mep_custom_event_time_format']) ? sanitize_text_field($_POST['mep_custom_event_time_format']) : $current_global_custom_time_format;
			$mep_time_zone_display = isset($_POST['mep_time_zone_display']) ? sanitize_text_field($_POST['mep_time_zone_display']) : $current_global_timezone_display;
			
			if ($mep_reg_status == 'on') {
				update_post_meta($post_id, 'mep_event_date_format', $mep_event_date_format);
				update_post_meta($post_id, 'mep_event_time_format', $mep_event_time_format);
				update_post_meta($post_id, 'mep_event_custom_date_format', $mep_event_custom_date_format);
				update_post_meta($post_id, 'mep_custom_event_time_format', $mep_custom_event_time_format);
				update_post_meta($post_id, 'mep_time_zone_display', $mep_time_zone_display);
			}
			
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
			$mp_event_virtual_type_des = isset($_POST['mp_event_virtual_type_des']) ? htmlspecialchars(mage_array_strip($_POST['mp_event_virtual_type_des'])) : "";
			update_post_meta($pid, 'mp_event_virtual_type_des', $mp_event_virtual_type_des);
			$_mdate = apply_filters('mep_more_date_arr_save', $mdate);
			if (!empty($_mdate) && $_mdate != $oldm) {
				update_post_meta($post_id, 'mep_event_more_date', $_mdate);
			} elseif (empty($_mdate) && $oldm) {
				delete_post_meta($post_id, 'mep_event_more_date', $oldm);
			}
		}
	}