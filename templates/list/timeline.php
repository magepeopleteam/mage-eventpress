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
	$event_infos = (is_array( $event_infos ) && sizeof( $event_infos ) > 0) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$upcoming_date = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$available_seat     = array_key_exists( 'available_seat', $event_infos ) ? $event_infos['available_seat'] : 0;
	$taxonomy_category  = array_key_exists( 'category_tax', $event_infos ) ? $event_infos['category_tax'] : '';
	$taxonomy_organizer = array_key_exists( 'organizer_tax', $event_infos ) ? $event_infos['organizer_tax'] : '';
	$title              = get_the_title( $event_id );
?>
<div class="timeline__item mep-event-list-loop">
    <div class="timeline__content">
        <div class='mep_event_timeline_list'>
			<?php do_action( 'mep_event_minimal_list_loop_header', $event_id ); ?>
	        <?php do_action( 'mpwem_list_sort_date', $event_infos ); ?>
	        <?php do_action( 'mpwem_list_thumb', $event_infos ); ?>
            <div class="mep_list_event_details">
                <a href="<?php the_permalink(); ?>">
                    <h5 class='mep_list_title'><?php echo esc_html( $title ); ?></h5>
					<?php
						do_action( 'mep_event_minimal_list_after_title', $event_id );
						if ( $available_seat == 0 ) {
							do_action( 'mep_show_waitlist_label' );
						}
						do_action( 'mpwem_list_upcoming_date', $event_infos );
						do_action( 'mpwem_list_location', $event_infos );
						do_action( 'mpwem_list_organizer', $event_infos );
						do_action( 'mep_event_minimal_list_after', $event_id ); ?>
                </a>
				<?php do_action( 'mpwem_list_more_date_button', $event_infos ); ?>
            </div>
        </div>
		<?php do_action( 'mep_event_minimal_list_loop_end', $event_id ); ?>
    </div>
</div>
