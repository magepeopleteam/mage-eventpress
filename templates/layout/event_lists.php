<?php
$counts = wp_count_posts('mep_events');
// Prepare the count data
$post_counts = array(
    'publish' => isset($counts->publish) ? $counts->publish : 0,
    'draft'   => isset($counts->draft) ? $counts->draft : 0,
    'trash'   => isset($counts->trash) ? $counts->trash : 0,
    'pending' => isset($counts->pending) ? $counts->pending : 0,
    'future'  => isset($counts->future) ? $counts->future : 0,
    'private' => isset($counts->private) ? $counts->private : 0,
);

$total_event = $post_counts['publish'] + $post_counts['draft']  + $post_counts['trash'] ;

$statuses = ['publish', 'draft', 'trash'];
$events = get_posts(array(
    'post_type'   => 'mep_events',
    'post_status' => $statuses,
    'numberposts' => -1
));

$post_type = 'mep_events'; // Replace with your custom post type slug
$add_new_link = admin_url('post-new.php?post_type=' . $post_type);
$trash_url = admin_url('edit.php?post_status=trash&post_type=mep_events');

function get_all_event_taxonomy(){
    $categories = array();

    $terms = get_terms( array(
        'taxonomy'   => 'mep_cat',
        'hide_empty' => false,
    ) );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $categories[ $term->slug ] = $term->name;
        }
    }

    error_log( print_r( [ '$categories' => $categories ], true ) );
}

function render_mep_events_by_status( $posts ) {
    ob_start();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $id    = $post->ID;

                $title = get_the_title($id);
                $status = get_post_status($id);
                // Action links
                $edit_link   = get_edit_post_link($id);
                $delete_link = get_delete_post_link($id); // Moves to Trash
                $view_link   = get_permalink($id);
                $duplicate_link = admin_url('admin.php?action=duplicate_post&post=' . $id);

                // Get postmeta fields
                $start_date      = get_post_meta($id, 'event_start_datetime', true);
                $start_date = date('F j, Y', strtotime( $start_date ));
                $start_time      = get_post_meta($id, 'event_start_time', true);
                $end_date        = get_post_meta($id, 'event_end_datetime', true);
                $more_dates      = get_post_meta($id, 'mep_event_more_date', true);
                $recurring       = get_post_meta($id, 'mep_enable_recurring', true);
                $ticket_type     = get_post_meta($id, 'mep_event_ticket_type', true);
                $extra_prices    = get_post_meta($id, 'mep_events_extra_prices', true);
                $location           = get_post_meta($id, 'mep_location_venue', true);
                $street          = get_post_meta($id, 'mep_street', true);
                $city            = get_post_meta($id, 'mep_city', true);
                $country         = get_post_meta($id, 'mep_country', true);


                $event_id           = $id ?? 0;
                $all_dates          = $all_dates ?? MPWEM_Functions::get_dates( $event_id );
                $all_times          = $all_times ?? MPWEM_Functions::get_times( $event_id, $all_dates );
                $date               = $date ?? MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );

                $total_ticket       = MPWEM_Functions::get_total_ticket( $event_id );
                $total_sold         = mep_get_event_total_seat_left( $event_id, $date );
                if( $total_ticket === $total_sold ){
                    $text = 'Full';
                    $full_class = 'capacity-full';
                }else{
                    $text = 'Available';
                    $full_class = '';
                }

                $total_reserve      = MPWEM_Functions::get_reserve_ticket( $event_id );
                $total_available    = $total_ticket - ( $total_sold + $total_reserve );

                $tax = $terms = get_the_terms($id, 'mep_cat');
                $cat_data = [];
                if (!empty($terms) && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $cat_data[] = [
                            'name' => $term->name,
                            'slug' => $term->slug,
                        ];
                    }
                }

                $category = isset( $cat_data[0] ) ? $cat_data[0]['name'] : '';


                /*$start_timestamp = strtotime($start_date);
                $end_timestamp = strtotime($end_date);
                $today_timestamp = strtotime(date('Y-m-d'));*/

                $start_timestamp = strtotime($start_date);
                $end_timestamp   = strtotime($end_date);
                $now             = time();

                if ( $now < $start_timestamp ) {
                    $event_status =  'Upcoming';
                    $event_status_class = 'status-upcoming';
                } elseif ($now >= $start_timestamp && $now <= $end_timestamp) {
                    $event_status = 'Active';
                    $event_status_class = 'status-active';
                } else if( $now > $end_timestamp ) {
                    $event_status = 'Expired';
                    $event_status_class = 'status-expired';
                }
//                error_log( print_r( [ '$event_status' => $event_status ], true ) );

                $ticket_type_count = 0;
                ?>

                <tr class="mpwem_event_list_card" data-event-status="<?php echo esc_attr( $status );?>">
                    <td>
                        <div class="event-image-placeholder">📚</div>
                    </td>
                    <td>
                        <div class="event-name">
                            <?php echo esc_attr($title);?>
                            <div class="event-status-inline">
                                <?php if( $status === 'publish'){?>
                                <div class="status-live-inline">
                                    <div class="live-indicator-inline"></div>
                                    Live
                                </div>
                                <?php } else if($status === 'draft'){?>
                                    <div class="event-status-inline">
                                        <div class="status-draft-inline">Draft</div>
                                    </div>
                                <?php } else{?>
                                    <div class="event-status-inline">
                                        <div class="status-draft-inline">Trash</div>
                                    </div>
                                <?php } ?>

                            </div>
                        </div>
                        <div class="event-category"><?php echo esc_attr( $category );?></div>
                    </td>
                    <td>
                        <div class="status-badge <?php echo esc_attr( $event_status_class );?>"><?php echo esc_attr( $event_status );?></div>
                    </td>
                    <td>
                        <div class="location">
                            📍 <?php echo esc_attr( $location );?>
                        </div>
                    </td>
                    <td>
                        <div class="date-time">
                            <span ><?php echo esc_attr( $start_date );?></span>
                            <span class="time"><?php echo esc_attr( $start_time );?></span>
                        </div>
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
                    <td class="capacity">
                        <div class="capacity-number"><?php echo esc_attr( $total_sold );?>/<?php echo esc_attr( $total_ticket );?></div>
                        <div class="capacity-bar">
                            <div class="capacity-fill <?php echo esc_attr( $full_class );?>" style="width: 100%"></div>
                        </div>
                        <div class="capacity-status"><?php echo esc_attr( $text );?></div>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="<?php echo esc_url( $view_link );?>"><button class="action-btn view" title="View Event">👁️</button></a>
                            <a href="<?php echo esc_url( $edit_link );?>"><button class="action-btn edit" title="Edit Event">✏️</button></a>
                            <a href="<?php echo esc_url( $delete_link );?>"><button class="action-btn delete" title="Delete Event">🗑️</button></a>
                            <a href="<?php echo esc_url( $duplicate_link )?>"><button class="action-btn duplicate" title="Duplicate Event">📋</button></a>
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
                        <h3><?php echo esc_attr( $post_counts['publish'] );?></h3>
                        <p><?php esc_attr_e( 'Active Events', 'mage-eventpress' );?></p>
                        <div class="trend neutral">→ <?php esc_attr_e( 'Same as last week', 'mage-eventpress' );?></div>
                    </div>
                    <div class="analytics-card">
                        <h3>2,847</h3>
                        <p><?php esc_attr_e( 'Total Registrations', 'mage-eventpress' );?></p>
                        <div class="trend up">↗ <?php esc_attr_e( '+23% this month', 'mage-eventpress' );?></div>
                    </div>
                    <div class="analytics-card">
                        <h3>94%</h3>
                        <p><?php esc_attr_e( 'Avg. Capacity Filled', 'mage-eventpress' );?></p>
                        <div class="trend up"><?php esc_attr_e( '↗ +5% this month', 'mage-eventpress' );?></div>
                    </div>
                    <div class="analytics-card">
                        <h3>$12,450</h3>
                        <p><?php esc_attr_e( 'Revenue This Month', 'mage-eventpress' );?></p>
                        <div class="trend up"><?php esc_attr_e( '↗ +18% vs last month', 'mage-eventpress' );?></div>
                    </div>
                </div>

                <div class="stats-summary">
                    <div class="stat-item mpwem_filter_by_status mpwem_filter_btn_active_bg_color" data-by-filter="all">
                        <span><?php esc_attr_e( 'All Events', 'mage-eventpress' );?></span>
                        <span class="stat-number"><?php echo esc_attr( $total_event );?></span>
                    </div>
                    <div class="stat-item mpwem_filter_by_status" data-by-filter="publish">
                        <span><?php esc_attr_e( 'Published', 'mage-eventpress' );?></span>
                        <span class="stat-number"><?php echo esc_attr( $post_counts['publish'] );?></span>
                    </div>
                    <div class="stat-item mpwem_filter_by_status" data-by-filter="draft">
                        <span><?php esc_attr_e( 'Draft', 'mage-eventpress' );?></span>
                        <span class="stat-number"><?php echo esc_attr( $post_counts['draft'] );?></span>
                    </div>
                    <a href="<?php echo esc_url( $trash_url );?>"><div class="stat-item">
                        <span><?php esc_attr_e( 'Trash', 'mage-eventpress' );?></span>
                        <span class="stat-number"><?php echo esc_attr( $post_counts['trash'] );?></span>
                        </div></a>
                </div>
            </div>

            <div class="controls">
                <div class="search-box">
                    <div class="search-icon">🔍</div>
                    <input type="text" placeholder="<?php esc_attr_e( 'Search events, locations, or organizers...', 'mage-eventpress' );?>">
                </div>
                <select class="category-select">
                    <option><?php esc_attr_e( 'All Categories', 'mage-eventpress' );?></option>
                    <option><?php esc_attr_e( 'Education', 'mage-eventpress' );?></option>
                    <option><?php esc_attr_e( 'Travel', 'mage-eventpress' );?></option>
                    <option><?php esc_attr_e( 'Entertainment', 'mage-eventpress' );?></option>
                    <option><?php esc_attr_e( 'Reunion Event', 'mage-eventpress' );?></option>
                    <option><?php esc_attr_e( 'Indoor Games', 'mage-eventpress' );?></option>
                    <option><?php esc_attr_e( 'Cooking Class', 'mage-eventpress' );?></option>
                </select>
                <button class="filter-btn"><?php esc_attr_e( 'Filter', 'mage-eventpress' );?></button>
<!--                <button class="filter-btn">Export</button>-->
            </div>

            <div class="table-container">
                <table class="event-table">
                    <thead>
                    <tr>
                        <th><?php esc_attr_e( 'Image', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Event Name', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Status', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Location', 'mage-eventpress' );?></th>
                        <th><?php esc_attr_e( 'Event Date', 'mage-eventpress' );?></th>
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

            <!--<div class="pagination">
                <div class="pagination-info">
                    Showing 8 of 43 events
                </div>
                <button class="load-more-btn" id="loadMoreBtn">
                    <span>Load More Events</span>
                    <span>↓</span>
                </button>
            </div>-->
        </div>
    </div>
</div>
