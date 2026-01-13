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
	$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$description_title         = array_key_exists( 'mep_event_hide_description_title', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_description_title'] : 'no';
	if ( get_post_field( 'post_content', $event_id ) ) {
		?>
        <div class="mpwem_details">
			<?php if ( $description_title == 'no' ): ?>
                <h2 class="_mb"><?php esc_html_e( 'Event  Description', 'mage-eventpress' ); ?></h2>
			<?php endif; ?>
            <div class="mpwem_details_content mp_wp_editor"><?php the_content(); ?></div>
        </div>
		<?php
	}