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
	$post_content              = get_post_field( 'post_content', $event_id );
	if ( $post_content ) {
		$word_count   = str_word_count( wp_strip_all_tags( $post_content ) );
		$has_readmore = $word_count > 200;
		$details_class = 'mpwem_details' . ( $has_readmore ? ' mpwem_details--has-readmore' : '' );
		?>
        <div class="<?php echo esc_attr( $details_class ); ?>"<?php echo $has_readmore ? ' data-readmore-words="200"' : ''; ?>>
			<?php if ( $description_title == 'no' ): ?>
                <h2 class="_mb"><?php esc_html_e( 'Event  Description', 'mage-eventpress' ); ?></h2>
			<?php endif; ?>
            <div class="mpwem_details_content mp_wp_editor<?php echo $has_readmore ? ' is-collapsed' : ''; ?>">
				<?php the_content(); ?>
            </div>
			<?php if ( $has_readmore ) : ?>
                <button type="button" class="mpwem_details_readmore" aria-expanded="false">
                    <span class="mpwem_details_readmore__more"><?php esc_html_e( 'Read More', 'mage-eventpress' ); ?></span>
                    <span class="mpwem_details_readmore__less"><?php esc_html_e( 'Read Less', 'mage-eventpress' ); ?></span>
                </button>
			<?php endif; ?>
        </div>
		<?php
	}
