<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id      = $event_id ?? 0;
	$type          = $type ?? '';
	$hide_location = MP_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_hide_location_from_details', 'no' );
	$isVirtual = get_post_meta($event_id,'mep_event_type',true);
	$show_map   	   = get_post_meta($event_id, 'mep_sgm', true);
	$show_map   	   = $show_map? $show_map : 0;
	$event_template   	   = get_post_meta($event_id, 'mep_event_template', true);
	$hide_location_details = mep_get_option('mep_event_hide_location_from_details', 'single_event_setting_sec', 'no');
	if($isVirtual!='online'){
		if($hide_location_details=='no'){
			if ( $event_id > 0 && $hide_location == 'no' ) {
				$location = MPWEM_Functions::get_location( $event_id );
				if ( sizeof( $location ) > 0 ) {
					ob_start();
					if ( $type && $type == 'sidebar' ) {
						?>
						<div class="location_widgets">
							<i class="fas fa-map-marker-alt"></i>
							<div>
								<h2><?php esc_html_e( 'Location', 'mage-eventpress' ); ?></h2>
								<p><?php echo esc_html( implode( ', ', $location ) ); ?> </p>
								<?php if($show_map): ?>
									<?php if($event_template=='smart.php'): ?>
										<button type="button" onclick="window.location.href = '#mep-map-location'">
											<i class="fas fa-map-marker-alt"></i><?php esc_html_e( 'Find In Map', 'mage-eventpress' ); ?>
										</button>
									<?php else: ?>
										<button type="button" data-target-popup="mpwem_popup_map" >
											<i class="fas fa-map-marker-alt"></i><?php esc_html_e( 'Find In Map', 'mage-eventpress' ); ?>
										</button>
									<?php endif; ?>
								<?php endif; ?>
							</div>
							<div class="mpPopup" data-popup="mpwem_popup_map">
								<div class="popupMainArea fullWidth">
									<div class="popupHeader">
										<h4>
											<?php esc_html_e('Map Location', 'mage-eventpress'); ?>
										</h4>
										<span class="fas fa-times popupClose"></span>
									</div>
									<div class="popupBody">
										<?php do_action('mep_event_map', $event_id); ?>
									</div>
								</div>

							</div>
						</div>

						<?php
					} elseif ( $type ) {
						echo esc_html( $location[ $type ] );
					} else {
						?>
						<div class="mpwem_location">
							<i class="fas fa-map-marker-alt"></i>
							<div><?php echo esc_html( implode( ', ', $location ) ); ?></div>
						</div>
						<?php
					}
					echo ob_get_clean();
				}
			}
		}
	}