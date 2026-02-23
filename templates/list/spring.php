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
	$event_infos        = (is_array( $event_infos ) && sizeof( $event_infos ) > 0) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$upcoming_date      = array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$available_seat     = array_key_exists( 'available_seat', $event_infos ) ? $event_infos['available_seat'] : 0;
	$taxonomy_category  = array_key_exists( 'category_tax', $event_infos ) ? $event_infos['category_tax'] : '';
	$taxonomy_organizer = array_key_exists( 'organizer_tax', $event_infos ) ? $event_infos['organizer_tax'] : '';
	$title              = get_the_title( $event_id );
	$org_class          = array_key_exists( 'org_class', $event_infos ) ? $event_infos['org_class'] : '';
	$cat_class          = array_key_exists( 'cat_class', $event_infos ) ? $event_infos['cat_class'] : '';
	/***********************/
	$end_datetime                   = $end_datetime ?? '';
	$start_time_format              = $start_time_format ?? '';
	$end_time_format                = $end_time_format ?? '';
	$show_price_label               = $show_price_label ?? '';
	$total_left                     = $total_left ?? '';
	$recurring                      = $recurring ?? 'no';
	$show_price                     = $show_price ?? 'yes';
	$event_type                     = $event_type ?? 'offline';
	$event_multidate                = $event_multidate ?? [];
	$author_terms                   = $author_terms ?? [];
	$mep_hide_event_hover_btn       = $mep_hide_event_hover_btn ?? 'no';
	$sold_out_ribbon                = $sold_out_ribbon ?? 'no';
	$limited_availability_ribbon    = $limited_availability_ribbon ?? 'no';
	$hide_org_list                  = $hide_org_list ?? 'no';
	$hide_location_list             = $hide_location_list ?? 'no';
	$hide_time_list                 = $hide_time_list ?? 'no';
	$limited_availability_threshold = $limited_availability_threshold ?? 5;
	$event_location_icon            = $event_location_icon ?? 'fas fa-map-marker-alt';
	$event_organizer_icon           = $event_organizer_icon ?? 'far fa-list-alt';
	$event_date_icon                = $event_date_icon ?? 'far fa-calendar-alt';
	$event_time_icon                = $event_time_icon ?? 'fas fa-clock';
?>
<div class='filter_item mep-event-list-loop  mep_event_list_item mep_event_spring_list mix <?php echo esc_attr( $org_class ) . ' ' . esc_attr( $cat_class ); ?>'
     data-title="<?php echo esc_attr( $title ); ?>"
     data-city-name="<?php echo esc_attr( array_key_exists( 'mep_city', $event_infos ) ? $event_infos['mep_city'] : '' ); ?>"
     data-state="<?php echo esc_attr( array_key_exists( 'mep_state', $event_infos ) ? $event_infos['mep_state'] : '' ); ?>"
     data-date="<?php echo esc_attr( $upcoming_date ? date( 'Y-m-d', strtotime( $upcoming_date ) ) : '' ); ?>"
     data-category="<?php echo esc_attr( $taxonomy_category ); ?>"
     data-organizer="<?php echo esc_attr( $taxonomy_organizer ); ?>"
>
	<?php do_action( 'mep_event_spring_list_loop_header', $event_id ); ?>
    <div class="mpwem_style spring_area mep_list_event_details">
        <div class="spring_item_1 _all_center"><h5><?php echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date ,'',$event_id) ); ?></h5></div>
        <div class="spring_item_2">
            <a href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>">
				<?php
					do_action( 'mpwem_list_upcoming_time', $event_infos );
					do_action( 'mpwem_list_location', $event_infos );
					do_action( 'mpwem_list_upcoming_date_only', $event_infos );
					do_action( 'mpwem_list_organizer', $event_infos );
				?>
            </a>
			<?php do_action( 'mpwem_list_more_date_button', $event_infos ); ?>
        </div>
        <a class="spring_item_3" href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>">
            <h5 class='mep_list_title'><?php echo esc_html( $title ); ?></h5>
			<?php
				if ( $available_seat == 0 ) {
					do_action( 'mep_show_waitlist_label' );
				}
				do_action( 'mpwem_list_price', $event_infos );
			?>
        </a>
        <a class="spring_item_4" href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>">
			<?php do_action( 'mpwem_list_thumb', $event_infos ); ?>
        </a>
    </div>
</div>
