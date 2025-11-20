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
	$event_type                     = $recurring ?? 'offline';
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
<div class='filter_item mep-event-list-loop  mep_event_list_item mep_event_native_list mix <?php echo esc_attr( $org_class . ' ' . $cat_class ); ?>'
     data-title="<?php echo esc_attr( get_the_title( $event_id ) ); ?>"
     data-city-name="<?php echo esc_attr( MPWEM_Global_Function::get_post_info( $event_id, 'mep_city' ) ); ?>"
     data-state="<?php echo esc_attr( MPWEM_Global_Function::get_post_info( $event_id, 'mep_state' ) ); ?>"
     data-category="<?php echo esc_attr( $taxonomy_category ); ?>"
     data-organizer="<?php echo esc_attr( $taxonomy_organizer ); ?>"
     data-date="<?php echo esc_attr( date( 'Y-m-d', strtotime( $upcoming_date ) ) ); ?>"
>
	<?php do_action( 'mep_event_minimal_list_loop_header', $event_id ); ?>
    <div class="mep_list_thumb mpwem_style">
        <div data-href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>" data-bg-image="<?php echo esc_url( MPWEM_Global_Function::get_image_url( $event_id, '', 'full' ) ); ?>"></div>
    </div>
    <div class="mep_list_event_details">
        <a href="<?php the_permalink(); ?>">
            <div class="mep-list-header">
                <h2 class='mep_list_title'><?php the_title(); ?></h2>
				<?php if ( $total_left == 0 ) {
					do_action( 'mep_show_waitlist_label' );
				} ?>
                <p class='mep_list_date'>
					<?php do_action( 'mep_event_list_date_li', $event_id, 'minimal' ); ?>
                    <span class='mep_minimal_list_location'>
                        <i class='<?php echo esc_attr( $event_location_icon ); ?>'></i>
                        <?php echo esc_html( MPWEM_Functions::get_location( $event_id, 'location' ) ); ?>
                    </span>
					<?php if ( $hide_org_list == 'no' && sizeof( $author_terms ) > 0 ) { ?>
                        <span class='mep_minimal_list_organizer'>
                        <i class="<?php echo esc_attr( $event_organizer_icon ); ?>"></i>
                       <?php echo esc_html( $author_terms[0]->name ); ?>
                    </span>
					<?php } ?> 
					</p>
        </a>
		<?php do_action( 'mep_event_list_loop_footer', $event_id ); ?>
    </div>
</div>
<?php do_action( 'mep_event_minimal_list_loop_end', $event_id ); ?>
