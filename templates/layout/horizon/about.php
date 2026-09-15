<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? [];
	$_single_event_setting_sec = is_array( $event_infos ) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$description_title         = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_event_hide_description_title', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_description_title'] : 'no';
	$content                   = get_post_field( 'post_content', $event_id );
	if ( ! $content ) {
		return;
	}
?>
<div class="horizon_about_inner">
	<?php if ( $description_title == 'no' ) : ?>
		<h2 class="horizon_section_title"><?php esc_html_e( 'About This Event', 'mage-eventpress' ); ?></h2>
	<?php endif; ?>
	<div class="horizon_about_content mp_wp_editor">
		<?php echo apply_filters( 'the_content', $content ); ?>
	</div>
</div>
