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
?>
<div class='filter_item mep-event-list-loop mix <?php echo esc_attr( $columnNumber . ' ' . $class_name . '  mep_event_' . $style . '_item  ' . $org_class . ' ' . $cat_class . ' ' . $tag_class ); ?>'
     data-title="<?php echo esc_attr( get_the_title( $event_id ) ); ?>"
     data-city-name="<?php echo esc_attr( MPWEM_Global_Function::get_post_info( $event_id, 'mep_city' ) ); ?>"
     data-state="<?php echo esc_attr( MPWEM_Global_Function::get_post_info( $event_id, 'mep_state' ) ); ?>"
     data-category="<?php echo esc_attr( $taxonomy_category ); ?>"
     data-organizer="<?php echo esc_attr( $taxonomy_organizer ); ?>"
     data-date="<?php echo esc_attr( date( 'Y-m-d', strtotime( $upcoming_date ) ) ); ?>" style="width:calc(<?php echo esc_attr( $width ); ?>% - 14px);">
	<?php do_action( 'mep_event_list_loop_header', $event_id ); ?>
    <div class="mep_list_thumb mpwem_style">
        <div data-href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>" data-bg-image="<?php echo esc_url( MPWEM_Global_Function::get_image_url( $event_id, '', 'large' ) ); ?>"></div>
        <div class="mep-ev-start-date">
            <div class="mep-day"><?php echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date, 'day' ) ); ?></div>
            <div class="mep-month"><?php echo esc_html( MPWEM_Global_Function::date_format( $upcoming_date, 'month' ) ); ?></div>
        </div>
        <div class="mepev-ribbon">
			<?php
				if ( is_array( $event_multidate ) && sizeof( $event_multidate ) > 0 && $recurring == 'no' ) { ?>
                    <div class='ribbon multidate'><i class="far fa-calendar-alt"></i> <?php esc_html_e( 'Multi Date', 'mage-eventpress' ); ?></div>
				<?php } elseif ( $recurring != 'no' ) { ?>
                    <div class='ribbon recurring'><i class="fas fa-history"></i> <?php esc_html_e( 'Recurring', 'mage-eventpress' ); ?></div>
				<?php }
				if ( $event_type == 'online' ) { ?>
                    <div class='ribbon online'><i class="fas fa-vr-cardboard"></i> <?php esc_html_e( 'Virtual', 'mage-eventpress' ); ?></div>
				<?php }
				if ( $sold_out_ribbon == 'yes' && $reg_status == 'on' && $total_left <= 0 ) { ?>
                    <div class="ribbon sold-out">                        <?php esc_html_e( 'Sold Out', 'mage-eventpress' ); ?></div>
				<?php } elseif ( $limited_availability_ribbon == 'yes' && $total_left > 0 && $total_left <= $limited_availability_threshold ) { ?>
                    <div class="ribbon limited-availability"><?php esc_html_e( 'Limited Availability', 'mage-eventpress' ); ?></div>
				<?php } ?>
        </div>
    </div>
    <div class="mep_list_event_details">
        <a href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>">
            <div class="mep-list-header">
                <p class='mep_list_title'><?php echo esc_html( get_the_title( $event_id ) ); ?></p>
				<?php if ( $total_left == 0 ) {
					do_action( 'mep_show_waitlist_label' );
				} ?>
                <h3 class='mep_list_date'>
					<?php if ( $show_price == 'yes' ) {
						echo esc_html( $show_price_label ) . " " . wp_kses_post( wc_price( MPWEM_Functions::get_min_price( $event_id ) ) );;
					} ?>
                </h3>
            </div>
			<?php if ( $style == 'list' ) { ?>
                <div class="mep-event-excerpt">
					<?php echo mb_strimwidth( get_the_excerpt(), 0, 220, '...' ); ?>
                </div>
			<?php } ?>
            <div class="mep-list-footer">
                <ul class="mep-list-footer-ul">
					<?php if ( $hide_org_list == 'no' && sizeof( $author_terms ) > 0 ) { ?>
                        <li class="mep_list_org_name">
                            <div class="evl-ico"><i class="<?php echo esc_attr( $event_organizer_icon ); ?>"></i></div>
                            <div class="evl-cc">
                                <h5><?php esc_html_e( 'Organized By:', 'mage-eventpress' ) ?></h5>
                                <h6><?php echo esc_html( $author_terms[0]->name ); ?></h6>
                            </div>
                        </li>
					<?php }
						if ( $event_type != 'online' ) {
							if ( $hide_location_list == 'no' ) { 
								$location_data = MPWEM_Functions::get_location( $event_id );
								$location_display = '';
								
								// Get location/venue first
								if ( ! empty( $location_data['location'] ) ) {
									$location_display = $location_data['location'];
								} else {
									// If no location/venue, build from street + city
									$location_parts = array();
									if ( ! empty( $location_data['street'] ) ) {
										$location_parts[] = $location_data['street'];
									}
									if ( ! empty( $location_data['city'] ) ) {
										$location_parts[] = $location_data['city'];
									}
									if ( ! empty( $location_parts ) ) {
										$location_display = implode( ' ', $location_parts );
									}
								}
								
								// Always show location section to avoid gaps
								?>
                                <li class="mep_list_location_name">
                                    <div class="evl-ico"><i class="<?php echo esc_attr( $event_location_icon ); ?>"></i></div>
                                    <div class="evl-cc">
                                        <h5> <?php esc_html_e( 'Location:', 'mage-eventpress' ); ?> </h5>
                                        <h6><?php echo esc_html( $location_display ); ?></h6>
                                    </div>
                                </li>
							<?php }
						}
						if ( $hide_time_list == 'no' && $recurring == 'no' ) {
							do_action( 'mep_event_list_date_li', $event_id, 'grid' );
						} elseif ( $hide_time_list == 'no' && $recurring != 'no' ) {
							do_action( 'mep_event_list_upcoming_date_li', $event_id );
						} ?>
                </ul>
            </div>
        </a>
		<?php if ( 'yes' == $mep_hide_event_hover_btn ) { ?>
            <div class="item_hover_effect">
                <a href="<?php echo esc_url( get_the_permalink( $event_id ) ); ?>"><?php esc_html_e( 'Book Now', 'mage-eventpress' ); ?></a>
            </div>
		<?php } ?>
    </div>
	<?php //} ?>
