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
	$event_infos        = ( is_array( $event_infos ) && sizeof( $event_infos ) > 0 ) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$upcoming_date      = is_array( $event_infos ) && array_key_exists( 'upcoming_date', $event_infos ) ? $event_infos['upcoming_date'] : '';
	$available_seat     = is_array( $event_infos ) && array_key_exists( 'available_seat', $event_infos ) ? $event_infos['available_seat'] : 0;
	$taxonomy_category  = is_array( $event_infos ) && array_key_exists( 'category_tax', $event_infos ) ? $event_infos['category_tax'] : '';
	$taxonomy_organizer = is_array( $event_infos ) && array_key_exists( 'organizer_tax', $event_infos ) ? $event_infos['organizer_tax'] : '';
	$title              = get_the_title( $event_id );
	$permalink          = get_the_permalink( $event_id );
	$org_class          = is_array( $event_infos ) && array_key_exists( 'org_class', $event_infos ) ? $event_infos['org_class'] : '';
	$cat_class          = is_array( $event_infos ) && array_key_exists( 'cat_class', $event_infos ) ? $event_infos['cat_class'] : '';
	$first_category     = '';
	if ( is_string( $taxonomy_category ) && $taxonomy_category !== '' ) {
		$category_parts = array_map( 'trim', explode( ',', $taxonomy_category ) );
		$first_category = $category_parts[0] ?? '';
	}
	$date_day   = $upcoming_date ? MPWEM_Global_Function::date_format( $upcoming_date, 'day' ) : '';
	$date_month = $upcoming_date ? date_i18n( 'M', strtotime( $upcoming_date ) ) : '';
	$date_year  = $upcoming_date ? date_i18n( 'Y', strtotime( $upcoming_date ) ) : '';
?>
<div class="filter_item mep-event-list-loop mep_event_list_item mep_event_winter_list mix <?php echo esc_attr( $org_class . ' ' . $cat_class ); ?>"
     data-title="<?php echo esc_attr( $title ); ?>"
     data-city-name="<?php echo esc_attr( is_array( $event_infos ) && array_key_exists( 'mep_city', $event_infos ) ? $event_infos['mep_city'] : '' ); ?>"
     data-state="<?php echo esc_attr( is_array( $event_infos ) && array_key_exists( 'mep_state', $event_infos ) ? $event_infos['mep_state'] : '' ); ?>"
     data-date="<?php echo esc_attr( $upcoming_date ? date( 'Y-m-d', strtotime( $upcoming_date ) ) : '' ); ?>"
     data-category="<?php echo esc_attr( $taxonomy_category ); ?>"
     data-organizer="<?php echo esc_attr( $taxonomy_organizer ); ?>"
>
	<?php do_action( 'mep_event_winter_list_loop_header', $event_id ); ?>
    <div class="mpwem_style spring_area mep_list_event_details mep_event_winter_list__surface">
        <div class="mep_list_date_wrapper">
            <p class="mep_winter_list_date">
                <span class="mep_winter_list_dd"><?php echo esc_html( $date_day ); ?></span>
                <span class="mep_winter_list_mm_yy">
                    <span class="mep_winter_list_mm"><?php echo esc_html( $date_month ); ?></span>
                    <span class="mep_winter_list_yy"><?php echo esc_html( $date_year ); ?></span>
                </span>
            </p>
        </div>

        <a class="spring_item_4 mep_event_winter_list__media" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
			<?php do_action( 'mpwem_list_thumb', $event_infos ); ?>
			<?php if ( $first_category ) : ?>
                <span class="mep_event_card__category"><?php echo esc_html( $first_category ); ?></span>
			<?php endif; ?>
        </a>

        <div class="spring_item_2 mep_event_winter_list__main">
            <a class="mep_event_winter_list__link" href="<?php echo esc_url( $permalink ); ?>">
                <h5 class="mep_list_title"><?php echo esc_html( $title ); ?></h5>
				<?php
					do_action( 'mep_event_minimal_list_after_title', $event_id );
					if ( $available_seat == 0 ) {
						do_action( 'mep_show_waitlist_label' );
					}
					do_action( 'mpwem_list_upcoming_time', $event_infos );
					do_action( 'mpwem_list_location', $event_infos );
					do_action( 'mpwem_list_upcoming_date_only', $event_infos );
					do_action( 'mpwem_list_organizer', $event_infos );
				?>
            </a>
            <div class="mep_event_winter_list__footer">
				<?php do_action( 'mpwem_list_more_date_button', $event_infos ); ?>
				<?php do_action( 'mpwem_list_price', $event_infos ); ?>
                <a class="mep_event_card__book" href="<?php echo esc_url( $permalink ); ?>">
					<?php esc_html_e( 'Book', 'mage-eventpress' ); ?>
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </div>
</div>
