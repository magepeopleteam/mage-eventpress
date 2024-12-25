<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$map_status     = MP_Global_Function::get_post_info( $event_id, 'mep_sgm');
	if ($map_status) {
		//echo '<pre>';print_r( $faqs );echo '</pre>';
		?>
		<div class="map_location">
			<h2><?php esc_html_e( 'Map Location', 'mage-eventpress' ); ?></h2>
			<?php do_action('mep_event_map', $event_id); ?>
		</div>
		<?php
	}