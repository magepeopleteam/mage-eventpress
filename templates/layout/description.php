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
	$event_infos               = (is_array( $event_infos ) && sizeof( $event_infos ) > 0) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$_single_event_setting_sec = is_array($event_infos) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$description_title         = is_array($single_event_setting_sec) && array_key_exists( 'mep_event_hide_description_title', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_description_title'] : 'no';
	$read_more_enabled         = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_enable_description_read_more', $single_event_setting_sec ) ? $single_event_setting_sec['mep_enable_description_read_more'] : 'yes';
	$read_more_word_limit      = is_array( $single_event_setting_sec ) && array_key_exists( 'mep_description_read_more_word_limit', $single_event_setting_sec ) ? absint( $single_event_setting_sec['mep_description_read_more_word_limit'] ) : 200;
	$read_more_word_limit      = max( 1, $read_more_word_limit );
	$read_more_enabled         = (bool) apply_filters( 'mpwem_enable_description_read_more', 'yes' === $read_more_enabled, $event_id );
	$read_more_word_limit      = max( 1, absint( apply_filters( 'mpwem_description_read_more_word_limit', $read_more_word_limit, $event_id ) ) );
	$post_content              = get_post_field( 'post_content', $event_id );
	if ( $post_content ) {
		$plain_description = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $post_content ) ) ) );
		$description_words = '' === $plain_description ? [] : preg_split( '/\s+/u', $plain_description );
		$has_read_more     = $read_more_enabled && count( $description_words ) > $read_more_word_limit;
		$details_class     = 'mpwem_details' . ( $has_read_more ? ' mpwem_details--has-readmore' : '' );
		$content_id        = 'mpwem-details-content-' . absint( $event_id );
		?>
		<div class="<?php echo esc_attr( $details_class ); ?>"<?php echo $has_read_more ? ' data-readmore-words="' . esc_attr( $read_more_word_limit ) . '"' : ''; ?>>
			<?php if ( $description_title == 'no' ): ?>
                <h2 class="_mb"><?php esc_html_e( 'Event  Description', 'mage-eventpress' ); ?></h2>
			<?php endif; ?>
			<div<?php echo $has_read_more ? ' id="' . esc_attr( $content_id ) . '"' : ''; ?> class="mpwem_details_content mp_wp_editor">
				<?php the_content(); ?>
            </div>
			<?php if ( $has_read_more ) : ?>
				<button type="button" class="mpwem_details_readmore" aria-expanded="false" aria-controls="<?php echo esc_attr( $content_id ); ?>">
					<span class="mpwem_details_readmore__more"><?php esc_html_e( 'Read More', 'mage-eventpress' ); ?></span>
					<span class="mpwem_details_readmore__less"><?php esc_html_e( 'Read Less', 'mage-eventpress' ); ?></span>
				</button>
			<?php endif; ?>
        </div>
		<?php
	}
