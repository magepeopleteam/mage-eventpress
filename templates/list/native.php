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
	$org_class                      = array_key_exists( 'org_class', $event_infos ) ? $event_infos['org_class'] : '';
	$cat_class                      = array_key_exists( 'cat_class', $event_infos ) ? $event_infos['cat_class'] : '';
?>
<div class='filter_item mep-event-list-loop  mep_event_list_item mep_event_native_list mix <?php echo esc_attr( $org_class . ' ' . $cat_class ); ?>'
     data-title="<?php echo esc_attr( $title ); ?>"
     data-city-name="<?php echo esc_attr( array_key_exists( 'mep_city', $event_infos ) ? $event_infos['mep_city'] : '' ); ?>"
     data-state="<?php echo esc_attr( array_key_exists( 'mep_state', $event_infos ) ? $event_infos['mep_state'] : '' ); ?>"
     data-date="<?php echo esc_attr( $upcoming_date ? date( 'Y-m-d', strtotime( $upcoming_date ) ) : '' ); ?>"
     data-category="<?php echo esc_attr( $taxonomy_category ); ?>"
     data-organizer="<?php echo esc_attr( $taxonomy_organizer ); ?>"
>
	<?php do_action( 'mep_event_minimal_list_loop_header', $event_id ); ?>
	<?php do_action( 'mpwem_list_thumb', $event_infos ); ?>
    <div class="mep_list_event_details">
        <a href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>">
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

