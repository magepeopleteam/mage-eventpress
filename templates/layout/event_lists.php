<?php
$counts = wp_count_posts('mep_events');
// Prepare the count data
$post_counts = array(
    'publish' => isset($counts->publish) ? $counts->publish : 0,
    'draft'   => isset($counts->draft) ? $counts->draft : 0,
    'trash'   => isset($counts->trash) ? $counts->trash : 0,
);

$total_event = $post_counts['publish'] + $post_counts['draft']  + $post_counts['trash'] ;

//$statuses = ['publish', 'draft', 'trash'];
$statuses = ['publish', 'draft'];
$events = get_posts(array(
    'post_type'   => 'mep_events',
    'post_status' => $statuses,
    'numberposts' => -1
));

function get_dates_by_interval($start, $end, $days = 4) {
    $start_date = new DateTime($start);
    $end_date = new DateTime($end);
    $interval = new DateInterval("P{$days}D"); // Dynamic day interval
    $date_range = new DatePeriod($start_date, $interval, $end_date);

    $dates = [];

    foreach ($date_range as $date) {
        $dates[] = $date->format('D j M, Y'); // e.g., Thu 1 May, 2025
    }

    return $dates;
}

function get_active_expire_upcoming_count( $events ){
    $active_count = 0 ;
    $expire_count = 0 ;
    $upcoming_count = 0 ;
    if (!empty($events)) {
        foreach ($events as $post) {
            $id = $post->ID;

            $start_date = get_post_meta($id, 'event_start_datetime', true);
            $start_date = date('F j, Y', strtotime($start_date));
            $end_date = get_post_meta($id, 'event_end_datetime', true);


            $start_timestamp = strtotime($start_date);
            $end_timestamp = strtotime($end_date);
            $now = time();

            if ( $now < $start_timestamp ) {
                $upcoming_count++;
            } elseif ($now >= $start_timestamp && $now <= $end_timestamp) {
                $active_count++;
            } else if ( $now > $end_timestamp) {

                $expire_count++;
            }
        }
    }

    $active_count = $active_count + $upcoming_count;

    return array(
        'active_count' => $active_count,
        'expire_count' => $expire_count,
        'upcoming_count' => $upcoming_count,
    );
}

$event_status_count = get_active_expire_upcoming_count( $events );


$post_type = 'mep_events';
$add_new_link = admin_url('post-new.php?post_type=' . $post_type);
$trash_url = admin_url('edit.php?post_status=trash&post_type=mep_events');

function get_all_event_taxonomy( $taxonomy ){
    $taxonomies = array();

    $terms = get_terms( array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ) );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $taxonomies[ $term->slug ] = $term->name;
        }
    }

    return $taxonomies;
}
function get_event_wise_taxonomy( $event_id, $taxonomy ){
    $terms = get_the_terms( $event_id, $taxonomy );
    $cat_data = $category_data =[];
    $all_category = '';
    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $all_category .= $term->name.', ';
            $cat_data[] = [
                'name' => $term->name,
                'slug' => $term->slug,
            ];
        }
    }

    $all_category = rtrim($all_category, ", \t\n\r\0\x0B");
    $category_data  = array(
        'all_category' =>$all_category,
        'cat_data' =>$cat_data,
    );

    return $category_data;
}

$order_status = array( 'wc-completed', 'wc-processing');
$completed_orders = wc_get_orders([
    'status' => $order_status,
    'limit'  => -1,
    'return' => 'ids',
]);

$total_registration = count($completed_orders);
function get_monthly_revenue($year = null, $month = null) {
    if (!$year) {
        $year = date('Y');
    }
    if (!$month) {
        $month = date('m');
    }
    $start_date = "$year-$month-01 00:00:00";
    $order_status = array( 'wc-completed', 'wc-processing');
    $end_date   = date('Y-m-t 23:59:59', strtotime($start_date));
    $orders = wc_get_orders([
        'limit'        => -1,
        'status'       => $order_status,
        'date_created' => $start_date . '...' . $end_date,
        'return'       => 'ids',
    ]);
    $total = 0;

    $each_month_order_count = count( $orders );

    foreach ($orders as $order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $total += $order->get_total();
        }
    }

    return array(
        'revenue' => $total,
        'each_month_registration' => $each_month_order_count,
    );
}
function get_change_in_percent( $current_month, $prev_month){
    $change = $current_month - $prev_month;
    if ($prev_month > 0) {
        $percent_change = ($change / $prev_month) * 100;
    } else {
        $percent_change = 100;
    }

    $direction_icon = $change > 0 ? '+' : ($change < 0 ? '-' : '+');

    return  array(
           'percent_change' => $percent_change,
           'inc_dec_sign' => $direction_icon,
    );
}

$year = date('Y');
$month = date('m');

$prev_year = $year;
$prev_month = $month - 1;
if( $month === 1 ){
    $prev_month = 12;
    $prev_year = $year - 1;
}
$currency = get_woocommerce_currency();
$currency_symbol = get_woocommerce_currency_symbol($currency);
$header_info = get_monthly_revenue( $year, $month);
$prev_header_info = get_monthly_revenue( $prev_year, $prev_month);
$current_month_revenue = $header_info['revenue'];
$current_month_registration = $header_info['each_month_registration'];

$prev_month_revenue = $prev_header_info['revenue'];
$prev_month_registration = $prev_header_info['each_month_registration'];

$rev_change = $current_month_revenue - $prev_month_revenue;
$revenue_percent_change = get_change_in_percent( $current_month_revenue, $prev_month_revenue );
$reg_percent_change= get_change_in_percent( $current_month_registration, $prev_month_registration );

$get_all_categories = get_all_event_taxonomy( 'mep_cat' );

function get_time_remaining( $future_datetime, $end_date ) {
    $now = new DateTime();
    $future = new DateTime( $future_datetime );
    $end_date = new DateTime( $end_date );

    if ( $future <= $now ) {
        return 'Expired!';
    }

    /*if ( $now >= $future && $now <= $end_date ) {
        return 'Running!';
    }*/

    $interval = $now->diff( $future );

    return sprintf(
        '%d days, %d hours, %d minutes remaining',
        $interval->days,
        $interval->h,
        $interval->i
    );
}
function render_mep_events_by_status( $posts ) {
    ob_start();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $id             = $post->ID;
                $title          = get_the_title($id);
                $thumbnail_url  = get_the_post_thumbnail_url($id, 'small');
                $status         = get_post_status($id);
                $edit_link      = get_edit_post_link($id);
                $delete_link    = get_delete_post_link($id); // Moves to Trash
                $view_link      = get_permalink($id);
                $start_date     = get_post_meta($id, 'event_start_datetime', true);
                $remaining_date = $start_date;
                $start_date     = date('F j, Y', strtotime( $start_date ));
                $start_time     = get_post_meta($id, 'event_start_time', true);
                $end_date       = get_post_meta($id, 'event_end_datetime', true);
                $ticket_type    = get_post_meta($id, 'mep_event_ticket_type', true);
                $location       = get_post_meta($id, 'mep_location_venue', true);

                $time_remaining = get_time_remaining( $remaining_date, $end_date );

                $event_type = MP_Global_Function::get_post_info( $id, 'mep_enable_recurring', 'no' );

                $event_id           = $id ?? 0;
                $all_dates          =  MPWEM_Functions::get_dates( $event_id );
                $all_times          =  MPWEM_Functions::get_times( $event_id, $all_dates );

                if( !empty( $all_dates ) ){
                    $date               =  MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
                }else{
                    $date = $start_date;
                }
                if( !empty( $all_dates ) && !empty( $all_times ) ) {
                    $time = MPWEM_Functions::get_upcoming_date_time($event_id, $all_dates, $all_times);
                    $time = date('H:i', strtotime( $time ));
                }else{
                    $time = $start_time;
                }


                $total_ticket       = MPWEM_Functions::get_total_ticket( $id, $date );
                $total_sold         = mep_get_event_total_seat_left( $id );

                if( $event_type === 'everyday' ){
                    $time_remaining = get_time_remaining( $date, $end_date );
                    $start_date = date('F j, Y', strtotime( $date ));
                    $event_type_status = 'Recurring Event (Repeated)';
                    $total_sold         = mep_get_event_total_seat_left( $id, $date );
                }else if( $event_type === 'yes' ){
                    $time_remaining = get_time_remaining( $date, $end_date );
                    $start_date = date('F j, Y', strtotime( $date ));
                    $event_type_status = 'Recurring Event (Selected Dates)';
                    $total_sold         = mep_get_event_total_seat_left( $id, $date );
                }else{
                    $event_type_status = '';
                }

                if( $total_ticket === $total_sold ){
                    $text = 'Full';
                    $full_class = 'capacity-full';
                }else{
                    $text = 'Available';
                    $full_class = '';
                }

                $cat_data = get_event_wise_taxonomy( $id, 'mep_cat' );
                $organiser_data = get_event_wise_taxonomy( $id, 'mep_org' );
                $category = isset( $cat_data['cat_data'][0] ) ? $cat_data['cat_data'][0]['name'] : '';
                $event_category =  isset( $cat_data['all_category'] ) ? $cat_data['all_category'] : '';
                $event_organiser =  isset( $organiser_data['all_category'] ) ? $organiser_data['all_category'] : '';

                $start_timestamp = strtotime($start_date);
                $end_timestamp   = strtotime($end_date);
                $now             = time();

                if ( $now < $start_timestamp ) {
                    $event_status =  'Active';
                    $event_status_class = 'status-active';
                } elseif ($now >= $start_timestamp && $now <= $end_timestamp) {
                    $event_status = 'Active';
                    $event_status_class = 'status-active';
                } elseif( $now > $end_timestamp ) {
                    $event_status = 'Expired';
                    $event_status_class = 'status-expired';
                }else{
                    $event_status = '';
                    $event_status_class = '';
                }

                if( $time_remaining === 'Expired!' ){
                    $event_status_class = 'status-expired';
                }


                $ticket_type_count = 0;
                ?>

                <tr class="mpwem_event_list_card" data-event-status="<?php echo esc_attr( $status );?>" data-event-active-status="<?php echo esc_attr( $event_status );?>" data-filter-by-category="<?php echo esc_attr( $event_category );?>"
                    data-filter-by-event-name="<?php echo esc_attr( $title );?>"
                    data-filter-by-event-organiser="<?php echo esc_attr( $event_organiser );?>"
                >
                    <td data-event-id="<?php echo esc_attr( $id );?>"> 
                        <input type="checkbox" class="checkbox mpwem_select_single_post" id="mpwem_select_single_post_<?php echo esc_attr( $id );?>" name="mpwem_checkbox_post_id[]">
                    </td>
                    <td>
                        <div class="mpwem_event-image-placeholder">
                            <img class="mpwem_event_feature_image" src="<?php echo esc_url( !empty($thumbnail_url) ? $thumbnail_url : 'https://placehold.co/300x300?text=No+Event+Image+Found' ); ?>">
                        </div>
                    </td>
                    <td class="mpwem_event_title">
                        <div class="event-name">
                            <a href="<?php echo esc_url( $edit_link );?>"><?php echo esc_attr($title .' '.$event_type_status );?></a>
                            <div class="event-status-inline">
                                <?php if( $status === 'publish'){?>
                                <div class="status-live-inline">
                                    <div class="live-indicator-inline"></div>
                                    <?php _e('Published','mage-eventpress'); ?>
                                </div>
                                <?php } else if($status === 'draft'){?>
                                    <div class="event-status-inline">
                                        <div class="status-draft-inline"><?php _e('Draft','mage-eventpress'); ?></div>
                                    </div>
                                <?php } else{?>
                                    <div class="event-status-inline">
                                        <div class="status-draft-inline"><?php _e('Trash','mage-eventpress'); ?></div>
                                    </div>
                                <?php } ?>

                            </div>
                           
                        </div>
                         <div class='mep_after_event_title'>
                                <?php do_action('mep_dashboard_event_list_after_event_title',$id); ?>
                            </div>
                        <div class="event-category" style='margin:10px 0;'><?php echo esc_attr( $category );?></div>
                    </td>

                    <td>
                        <div class="location">
                            📍 <?php echo esc_attr( $location );?>
                        </div>
                    </td>
                    <td>
                        <div class="date-time">
                            <span ><?php echo esc_attr( $start_date );?></span>
                            <span class="time"><?php echo esc_attr( $time );?></span>
<!--                            <span class="mpwem_remaining_days">--><?php //echo esc_attr( $time_remaining );?><!--</span>-->
                        </div>
                    </td>
                    <td>
                        <div class="status-badge mpwem_remaining_days <?php echo esc_attr( $event_status_class ); ?>"><?php echo esc_attr( $time_remaining );?></div>
                    </td>
                    <td>
                        <div class="ticket-types">
                            <?php
                            $dis_ticket_type_count = 0; ;
                            if( is_array( $ticket_type ) && !empty( $ticket_type ) ){
                                $ticket_type_count = count( $ticket_type );
                                foreach ( $ticket_type as $type ) {
                                    if( $dis_ticket_type_count < 2 ){
                                    ?>
                                    <div class="ticket-item">
                                        <span class="ticket-name"><?php echo esc_html( $type['option_name_t']);?></span>
                                        <span class="ticket-price ticket-free"><?php echo esc_attr( $type['option_price_t']);?></span>
                                    </div>
                              <?php
                                    }
                                    $dis_ticket_type_count++;
                                }
                                ?>
                            <?php }
                            if( $ticket_type_count > 2 ){
                                $more_ticket_type = $ticket_type_count - 2;
                            ?>
                            <div class="ticket-more">+<?php echo esc_attr( $more_ticket_type );?> more</div>
                            <?php }?>
                        </div>
                    </td>
                    <td class="mpwem_event_list_capacity">
                        <div class="mpwem_event_list_capacity-number"><?php echo esc_attr( $total_sold );?>/<?php echo esc_attr( $total_ticket );?></div>
                        <div class="mpwem_event_list_capacity-bar">
                            <div class="mpwem_event_list_capacity-fill <?php echo esc_attr( $full_class );?>" style="width: 100%"></div>
                        </div>
                        <div class="mpwem_event_list_capacity-status"><?php echo esc_attr( $text );?></div>
                    </td>
                    <td>
                        <div class="actions">
                        <?php do_action('mep_before_dashboard_event_list',$id); ?>
                            <a href="<?php echo esc_url( $view_link );?>"><button class="action-btn view" title="View Event"><span class="dashicons dashicons-visibility"></span></button></a>
                            <a href="<?php echo esc_url( $edit_link );?>"><button class="action-btn edit" title="Edit Event"><span class="dashicons dashicons-edit"></span></button></a>
                            <a href="<?php echo esc_url( $delete_link );?>"><button class="action-btn delete" title="Delete Event"><span class="dashicons dashicons-trash"></span></button></a>
                            <!--<a href="--><?php //echo esc_url( $duplicate_link )?><!--"><button class="action-btn duplicate" title="Duplicate Event">📋</button></a>-->
                            <!-- <a title="<?php //echo esc_attr__('Duplicate Hotel ', 'tour-booking-manager') . ' : ' . get_the_title($id); ?>"  href="<?php //echo wp_nonce_url(admin_url('admin.php?action=mpwem_duplicate_post&post_id=' . $id),'mpwem_duplicate_post_' . $id; ?>"><button class="action-btn duplicate" title="Duplicate Event">📋</button></a> -->
                        <?php do_action('mep_after_dashboard_event_list',$id); ?>
                        </div>
                    </td>
                </tr>

         <?php   }
        } else {
            echo '<p>No posts found.</p>';
        }

    return ob_get_clean(); // return the entire buffered content
}


?>
<div class="wrap"></div>
<div class="mpwem_event_list mpStyle mpwem_welcome_page">
    <div class='padding'>
        <div class="container">
            <div class="header">
                <div class="header-top">
                    <h1><?php esc_html_e( 'Event Management Dashboard', 'mage-eventpress' )?></h1>
                    <a href="<?php echo esc_url( $add_new_link );?>"><button class="add-event-btn">
                        <span>+</span>
                        <?php esc_html_e( 'Add New Event', 'mage-eventpress' )?>
                        </button></a>
                </div>

                <div class="analytics">
                    <div class="analytics-card">
                        <h3><?php echo esc_attr( $total_event );?></h3>
                        <p><?php esc_attr_e( 'Total Events', 'mage-eventpress' );?></p>
                        <div class="trend up">↗ +12% this month</div>
                    </div>
                    <div class="analytics-card">
                        <h3><?php echo esc_attr( $event_status_count['active_count'] );?></h3>
                        <p><?php esc_attr_e( 'Active Events', 'mage-eventpress' );?></p>
                        <div class="trend neutral">→ <?php esc_attr_e( 'Same as last week', 'mage-eventpress' );?></div>
                    </div>
                    <div class="analytics-card">
                        <h3><?php echo esc_attr( $total_registration ); ?></h3>
                        <p><?php esc_attr_e( 'Total Registrations', 'mage-eventpress' );?></p>
                        <div class="trend up">↗ <?php echo esc_attr( $reg_percent_change['inc_dec_sign'].'%'.$reg_percent_change['percent_change'].' vs last month');?></div>
                    </div>
                    <!--<div class="analytics-card">
                        <h3>94%</h3>
                        <p><?php /*esc_attr_e( 'Avg. Capacity Filled', 'mage-eventpress' );*/?></p>
                        <div class="trend up"><?php /*esc_attr_e( '↗ +5% this month', 'mage-eventpress' );*/?></div>
                    </div>-->
                    <div class="analytics-card">
                        <h3><?php echo $currency_symbol.' '.$current_month_revenue?></h3>
                        <p><?php esc_attr_e( 'Revenue This Month', 'mage-eventpress' );?></p>
                        <div class="trend up"><?php esc_attr_e( '↗ '.$revenue_percent_change['inc_dec_sign'].$revenue_percent_change['percent_change'].'% vs last month', 'mage-eventpress' );?></div>
                    </div>
                </div>

                <div class="stats-summary">
                    <div class="stat-item mpwem_filter_by_status mpwem_filter_btn_active_bg_color" data-by-filter="all">
                        <span><?php esc_attr_e( 'All Events', 'mage-eventpress' );?></span>
                        <span class="stat-number">(<?php echo esc_attr( $total_event );?>)</span>
                    </div>
                    <div class="stat-item mpwem_filter_by_status" data-by-filter="publish">
                        <span><?php esc_attr_e( 'Published', 'mage-eventpress' );?></span>
                        <span class="stat-number">(<?php echo esc_attr( $post_counts['publish'] );?>)</span>
                    </div>
                    <div class="stat-item mpwem_filter_by_status" data-by-filter="draft">
                        <span><?php esc_attr_e( 'Draft', 'mage-eventpress' );?></span>
                        <span class="stat-number">(<?php echo esc_attr( $post_counts['draft'] );?>)</span>
                    </div>
                    <div class="stat-item mpwem_filter_by_active_status" data-by-filter="active">
                        <span><?php esc_attr_e( 'Active', 'mage-eventpress' );?></span>
                        <span class="stat-number">(<?php echo esc_attr( $event_status_count['active_count'] );?>)</span>
                    </div>
                    <!--<div class="stat-item mpwem_filter_by_active_status" data-by-filter="upcoming">
                        <span><?php /*esc_attr_e( 'Upcoming', 'mage-eventpress' );*/?></span>
                        <span class="stat-number"><?php /*echo esc_attr( $event_status_count['upcoming_count'] );*/?></span>
                    </div>-->
                    <div class="stat-item mpwem_filter_by_active_status" data-by-filter="expired">
                        <span><?php esc_attr_e( 'Expired', 'mage-eventpress' );?></span>
                        <span class="stat-number">(<?php echo esc_attr( $event_status_count['expire_count'] );?>)</span>
                    </div>
                    <a href="<?php echo esc_url( $trash_url );?>"><div class="stat-item">
                        <span><?php esc_attr_e( 'Trash', 'mage-eventpress' );?></span>
                        <span class="stat-number">(<?php echo esc_attr( $post_counts['trash'] );?>)</span>
                        </div></a>
                </div>
            </div>

            <div class="controls">
                <div class="mpwem_multiple_trash_holder" id="mpwem_multiple_trash_holder" style="display: none">
                    <button class="mpwem_multiple_trash_btn" id="mpwem_multiple_trash_btn">Trash</button>
                </div>
                <div class="search-box">
                    <div class="search-icon">🔍</div>
                    <input id="mpwem_search_event_list" type="text" placeholder="<?php esc_attr_e( 'Search events, locations, or organizers...', 'mage-eventpress' );?>">
                </div>
                <select class="category-select" id="mpwem_event_filter_by_category">
                    <option><?php esc_attr_e( 'All Categories', 'mage-eventpress' );?></option>
                    <?php
                    if( is_array( $get_all_categories ) && !empty( $get_all_categories ) ){
                        foreach ( $get_all_categories as $key => $event_categories ){ ?>
                            <option><?php echo esc_attr( $event_categories );?></option>
                       <?php }

                    }
                    ?>
                </select>
<!--                <button class="filter-btn">--><?php //esc_attr_e( 'Filter', 'mage-eventpress' );?><!--</button>-->
<!--                <button class="filter-btn">Export</button>-->
            </div>

            <div class="table-container">
                <table class="event-table">
                    <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="checkbox" id="mpwem_select_all_post">
                        </th>
                        <th><?php esc_attr_e( 'Image', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Event Name', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Location', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Event Date', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Event Starts In', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Ticket Types', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Capacity', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Actions', 'mage-eventpress' );?></th>
                    </tr>
                    </thead>
                    <tbody>
                       <?php
                        echo render_mep_events_by_status( $events );
                       ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <div class="pagination-info">
                    <?php esc_attr_e( 'Showing', 'mage-eventpress' );?> <span id="visibleCount">0</span> of <span id="totalCount">0</span> <?php esc_attr_e( ' git events', 'mage-eventpress' );?>
                </div>
                <button class="load-more-btn" id="loadMoreBtn">
                    <span><?php esc_attr_e( 'Load More Events', 'mage-eventpress' );?></span>
                    <span>↓</span>
                </button>
            </div>

        </div>
    </div>
</div>
