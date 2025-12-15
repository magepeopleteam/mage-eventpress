<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id                       = $event_id ?? 0;
	$columnNumber                   = $columnNumber ?? '';
	$class_name                     = $class_name ?? '';
	$style                          = $style ?? '';
	$reg_status                     = $reg_status ?? '';
	$org_class                      = $org_class ?? '';
	$cat_class                      = $cat_class ?? '';
	$tag_class                      = $tag_class ?? '';
	$taxonomy_category              = $taxonomy_category ?? '';
	$taxonomy_organizer             = $taxonomy_organizer ?? '';
	$upcoming_date                  = $upcoming_date ?? '';
	$end_datetime                   = $end_datetime ?? '';
	$start_time_format              = $start_time_format ?? '';
	$end_time_format                = $end_time_format ?? '';
	$width                          = $width ?? '';
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
<div class="timeline__item">
    <div class="timeline__content">
        <div class='mep_event_timeline_list'>
			<?php do_action( 'mep_event_minimal_list_loop_header', $event_id ); ?>
            <div class="mep_list_thumb">
                <a href="<?php echo get_the_permalink( $event_id ); ?>"><?php mep_get_list_thumbnail( $event_id ); ?></a>
                <div class="mep-ev-start-date">
                    <div class="mep-day"><?php echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date, 'day' ) ); ?></div>
                    <div class="mep-month"><?php echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date, 'month' ) ); ?></div>
                </div>
            </div>
            <div class="mep_list_event_details">
                <a href="<?php the_permalink(); ?>">
                    <div class="mep-list-header">
                        <h2 class='mep_list_title'><?php the_title(); ?></h2>
						<?php if ( $total_left == 0 ) {
							do_action( 'mep_show_waitlist_label' );
						} ?>
                        <p class='mep_list_date'>
                            <span class='mep_minimal_list_date'>
                                <i class="<?php echo $event_date_icon; ?>"></i>
                                <?php echo esc_html( MPWEM_Global_Function::date_format( $start_time_format, 'time' ) . ' ' . ( $end_time_format ? ' - ' . MPWEM_Global_Function::date_format( $end_time_format, 'time' ) : '' ) ); ?>
                            </span>
                            <span class='mep_minimal_list_location'><i class="<?php echo esc_attr( $event_location_icon ); ?>"></i> <?php echo esc_html( MPWEM_Functions::get_location( $event_id, 'location' ) ); ?></span>
							<?php if ( $hide_org_list == 'no' && sizeof( $author_terms ) > 0 ) { ?>
                                <span class='mep_minimal_list_organizer'><i class="<?php echo esc_attr( $event_organizer_icon ); ?>>"></i> <?php echo esc_html( $author_terms[0]->name ); ?></span>
							<?php } ?>
							</p>
                </a>
				<?php do_action( 'mep_event_list_loop_footer', $event_id ); ?>
            </div>
        </div>
		<?php do_action( 'mep_event_minimal_list_loop_end', $event_id ); ?>
    </div>
</div>
