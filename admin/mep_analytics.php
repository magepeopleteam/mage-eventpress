<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
// Add admin page to the menu
	add_action( 'admin_menu', 'mep_event_analytics_admin_menu' );
	function mep_event_analytics_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=mep_events',
			__( 'Analytics', 'mage-eventpress' ),
			'<span style="color:#32c1a4">Analytics</span>', // Menu title with HTML outside translation
			'manage_options',
			'mep_event_analytics_page',
			'mep_event_analytics_page'
		);
	}

// Main analytics page content
	function mep_event_analytics_page() {
		// Get current date and 30 days ago for default date range
		$end_date   = date( 'Y-m-d' );
		$start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
		// Get all events for the filter dropdown
		$events = get_posts( array(
			'post_type'      => 'mep_events',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		) );
		?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Event Analytics Dashboard', 'mage-eventpress' ); ?></h1>
            <div class="mep-analytics-filters">
                <div class="mep-filter-group">
                    <label for="mep-date-range"><?php esc_html_e( 'Date Range:', 'mage-eventpress' ); ?></label>
                    <select id="mep-date-range" class="mep-filter">
                        <option value="7"><?php esc_html_e( 'Last 7 Days', 'mage-eventpress' ); ?></option>
                        <option value="30" selected><?php esc_html_e( 'Last 30 Days', 'mage-eventpress' ); ?></option>
                        <option value="90"><?php esc_html_e( 'Last 90 Days', 'mage-eventpress' ); ?></option>
                        <option value="365"><?php esc_html_e( 'Last Year', 'mage-eventpress' ); ?></option>
                        <option value="custom"><?php esc_html_e( 'Custom Range', 'mage-eventpress' ); ?></option>
                    </select>
                    <div id="mep-custom-date-range" style="display: none;">
                        <label for="mep-start-date"><?php esc_html_e( 'From:', 'mage-eventpress' ); ?></label>
                        <input type="date" id="mep-start-date" value="<?php echo esc_attr($start_date); ?>" class="mep-filter">
                        <label for="mep-end-date"><?php esc_html_e( 'To:', 'mage-eventpress' ); ?></label>
                        <input type="date" id="mep-end-date" value="<?php echo esc_attr($end_date); ?>" class="mep-filter">
                    </div>
                </div>
                <div class="mep-filter-group">
                    <label for="mep-event-filter"><?php esc_html_e( 'Event:', 'mage-eventpress' ); ?></label>
                    <select id="mep-event-filter" class="mep-filter">
                        <option value="all"><?php esc_html_e( 'All Events', 'mage-eventpress' ); ?></option>
						<?php foreach ( $events as $event ) : ?>
                            <option value="<?php echo esc_attr($event->ID); ?>"><?php echo esc_html($event->post_title); ?></option>
						<?php endforeach; ?>
                    </select>
                </div>
	            <?php
		            $category_lists = MPWEM_Global_Function::get_all_term_data( 'mep_cat' );
		            if ( is_array( $category_lists ) && sizeof( $category_lists ) > 0 ) {
			            ?>
                        <label>
                            <span>Category Filter</span>
                            <select class="formControl" name="filter_with_category">
                                <option selected value=""><?php esc_html_e( 'Select Category', 'mage-eventpress' ); ?></option>
					            <?php foreach ( $category_lists as $category ) { ?>
                                    <option value="<?php echo esc_attr( $category ); ?>"><?php echo esc_html( $category ); ?></option>
					            <?php } ?>
                            </select>
                        </label>
		            <?php } ?>
                <button id="mep-apply-filters" class="button button-primary"><?php esc_html_e( 'Apply Filters', 'mage-eventpress' ); ?></button>
                <button id="mep-export-csv" class="button"><?php esc_html_e( 'Export to CSV', 'mage-eventpress' ); ?></button>
            </div>
            <div class="mep-analytics-dashboard">
                <!-- Summary Cards -->
                <div class="mep-summary-cards">
                    <div class="mep-card" id="mep-total-sales">
                        <div class="mep-card-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="mep-card-content">
                            <h3><?php esc_html_e( 'Total Sales', 'mage-eventpress' ); ?></h3>
                            <p class="mep-card-value">0</p>
                        </div>
                    </div>
                    <div class="mep-card" id="mep-tickets-sold">
                        <div class="mep-card-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="mep-card-content">
                            <h3><?php esc_html_e( 'Tickets Sold', 'mage-eventpress' ); ?></h3>
                            <p class="mep-card-value">0</p>
                        </div>
                    </div>
                    <div class="mep-card" id="mep-total-events">
                        <div class="mep-card-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="mep-card-content">
                            <h3><?php esc_html_e( 'Total Events', 'mage-eventpress' ); ?></h3>
                            <p class="mep-card-value">0</p>
                        </div>
                    </div>
                    <div class="mep-card" id="mep-avg-ticket-price">
                        <div class="mep-card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="mep-card-content">
                            <h3><?php esc_html_e( 'Avg. Ticket Price', 'mage-eventpress' ); ?></h3>
                            <p class="mep-card-value">0</p>
                        </div>
                    </div>
                </div>
                <!-- Charts -->
                <div class="mep-charts-container">
                    <div class="mep-chart-wrapper">
                        <h2><?php esc_html_e( 'Sales Over Time', 'mage-eventpress' ); ?></h2>
                        <div class="mep-chart-container">
                            <canvas id="mep-sales-chart"></canvas>
                        </div>
                    </div>
                    <div class="mep-chart-wrapper">
                        <h2><?php esc_html_e( 'Tickets Sold by Event', 'mage-eventpress' ); ?></h2>
                        <div class="mep-chart-container">
                            <canvas id="mep-events-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="mep-charts-container">
                    <div class="mep-chart-wrapper">
                        <h2><?php esc_html_e( 'Ticket Types Distribution', 'mage-eventpress' ); ?></h2>
                        <div class="mep-chart-container">
                            <canvas id="mep-ticket-types-chart"></canvas>
                        </div>
                    </div>
                    <div class="mep-chart-wrapper">
                        <h2><?php esc_html_e( 'Sales by Day of Week', 'mage-eventpress' ); ?></h2>
                        <div class="mep-chart-container">
                            <canvas id="mep-weekday-chart"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Detailed Data Table -->
                <div class="mep-data-table-wrapper">
                    <h2><?php esc_html_e( 'Detailed Event Data', 'mage-eventpress' ); ?></h2>
                    <table class="mep-data-table widefat striped">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Event', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Tickets Sold', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Total Sales', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Available Seats', 'mage-eventpress' ); ?></th>
                            <th><?php esc_html_e( 'Occupancy Rate', 'mage-eventpress' ); ?></th>
                        </tr>
                        </thead>
                        <tbody id="mep-data-table-body">
                        <tr>
                            <td colspan="6"><?php esc_html_e( 'Loading data...', 'mage-eventpress' ); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
		<?php
	}
// AJAX handler for fetching analytics data
	add_action( 'wp_ajax_mep_get_analytics_data', 'mep_get_analytics_data' );
	function mep_get_analytics_data() {
		// Check nonce for security
		check_ajax_referer( 'mep_analytics_nonce', 'nonce' );
		// Get filter parameters
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : date( 'Y-m-d' );
		$event_id   = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 'all';
		$filter_with_category = $_POST['filter_with_category'] ?? '';
		// Define variables used throughout the function
		$_user_set_status = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
		$_order_status    = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
		$order_status     = array_values( $_order_status );
		// Convert dates to timestamp for comparison
		$start_timestamp = strtotime( $start_date );
		$end_timestamp   = strtotime( $end_date . ' 23:59:59' ); // Include the entire end day
		// Initialize data arrays
		$summary_data = array(
			'total_sales'      => 0,
			'tickets_sold'     => 0,
			'total_events'     => 0,
			'avg_ticket_price' => 0,
		);
		$sales_by_date     = array();
		$tickets_by_event  = array();
		$ticket_types_data = array();
		$sales_by_weekday  = array(
			'Sunday'    => 0,
			'Monday'    => 0,
			'Tuesday'   => 0,
			'Wednesday' => 0,
			'Thursday'  => 0,
			'Friday'    => 0,
			'Saturday'  => 0,
		);
		$detailed_data = array();
		// Get events based on filter
		$event_args = array(
			'post_type'      => 'mep_events',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);
		if ( $event_id !== 'all' ) {
			$event_args['p'] = $event_id; // Use 'p' instead of 'include' for direct ID query
		}
		$events                       = get_posts( $event_args );
		$summary_data['total_events'] = count( $events );
		// Process each event
		foreach ( $events as $event ) {
			$event_id    = $event->ID;
			$event_title = $event->post_title;
			// Get attendees for this event
			$attendee_args = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'ea_event_id',
						'value'   => $event_id,
						'compare' => '=',
					)
				),
			);
			$attendees = get_posts( $attendee_args );
			// Initialize event data
			$event_data = array(
				'event_id'     => $event_id,
				'event_title'  => $event_title,
				'tickets_sold' => 0,
				'total_sales'  => 0,
				'dates'        => array(),
			);
			// De-duplication: Only count unique attendee/order/date/ticket_type
			$unique_attendees = array();
			// Process each attendee
			foreach ( $attendees as $attendee ) {
				$attendee_id = $attendee->ID;
				$event_id = MPWEM_Global_Function::get_post_info( $attendee_id, 'ea_event_id' );
				$order_id     = get_post_meta( $attendee_id, 'ea_order_id', true );
				$ticket_type  = get_post_meta( $attendee_id, 'ea_ticket_type', true );
				$event_date   = get_post_meta( $attendee_id, 'ea_event_date', true );
				$unique_key = $order_id . '_' . $event_id . '_' . $event_date . '_' . $ticket_type;
				if (isset($unique_attendees[$unique_key])) {
					continue; // Skip duplicate
				}
				$unique_attendees[$unique_key] = true;
				$exit_true=false;
				if($filter_with_category){
					$taxonomy_info=MPWEM_Global_Function::all_taxonomy_data($event_id,'mep_cat');
					if(in_array($filter_with_category,$taxonomy_info)){
						$exit_true=true;
					}
				}else{
					$exit_true=true;
				}
				if($exit_true) {
					// Get attendee data
					$order_id     = get_post_meta( $attendee_id, 'ea_order_id', true );
					$ticket_price = get_post_meta( $attendee_id, 'ea_ticket_price', true );
					$ticket_type  = get_post_meta( $attendee_id, 'ea_ticket_type', true );
					$event_date   = get_post_meta( $attendee_id, 'ea_event_date', true );
					// Get order date
					$order = wc_get_order( $order_id );
					if ( ! $order ) {
						continue;
					}
					$order_date = $order->get_date_created()->getTimestamp();
					// Check if order date is within the selected range
					if ( $order_date < $start_timestamp || $order_date > $end_timestamp ) {
						continue;
					}
					// Update summary data
					$summary_data['tickets_sold'] ++;
					$summary_data['total_sales'] += floatval( $ticket_price );
					// Update event data
					$event_data['tickets_sold'] ++;
					$event_data['total_sales'] += floatval( $ticket_price );
					// Format date for chart
					$date_formatted = date( 'Y-m-d', $order_date );
					// Update sales by date
					if ( ! isset( $sales_by_date[ $date_formatted ] ) ) {
						$sales_by_date[ $date_formatted ] = 0;
					}
					$sales_by_date[ $date_formatted ] += floatval( $ticket_price );
					// Update sales by weekday
					$weekday                      = date( 'l', $order_date );
					$sales_by_weekday[ $weekday ] += floatval( $ticket_price );
					// Update ticket types data
					if ( ! isset( $ticket_types_data[ $ticket_type ] ) ) {
						$ticket_types_data[ $ticket_type ] = 0;
					}
					$ticket_types_data[ $ticket_type ] ++;
					// Track event dates
					if ( ! isset( $event_data['dates'][ $event_date ] ) ) {
						$event_data['dates'][ $event_date ] = array(
							'tickets_sold' => 0,
							'total_sales'  => 0,
						);
					}
					$event_data['dates'][ $event_date ]['tickets_sold'] ++;
					$event_data['dates'][ $event_date ]['total_sales'] += floatval( $ticket_price );
				}
			}
			// Add event to tickets by event data
			$tickets_by_event[ $event_title ] = $event_data['tickets_sold'];
			// Process detailed data for each event date
			foreach ( $event_data['dates'] as $date => $date_data ) {
				// Get total seats and available seats
				$total_seats     = mep_event_total_seat( $event_id, 'total' );
				$available_seats = mep_get_event_total_available_seat( $event_id, $date );
				$occupancy_rate  = $total_seats > 0 ? round( ( $date_data['tickets_sold'] / $total_seats ) * 100, 2 ) : 0;
				$normalized_title = trim(html_entity_decode($event_title));
				$normalized_date = date('Y-m-d', strtotime($date));
				$detailed_key = $normalized_title . '||' . $normalized_date;
				if (!isset($detailed_data[$detailed_key])) {
					$detailed_data[$detailed_key] = array(
						'event'           => $normalized_title,
						'date'            => $normalized_date,
						'tickets_sold'    => 0,
						'total_sales'     => 0,
						'available_seats' => $available_seats,
						'occupancy_rate'  => 0,
					);
				}
				$detailed_data[$detailed_key]['tickets_sold'] += $date_data['tickets_sold'];
				$detailed_data[$detailed_key]['total_sales'] += $date_data['total_sales'];
				$detailed_data[$detailed_key]['available_seats'] = $available_seats;
				$detailed_data[$detailed_key]['occupancy_rate'] = $occupancy_rate;
			}
		}
		// Calculate average ticket price
		$summary_data['avg_ticket_price'] = $summary_data['tickets_sold'] > 0 ?
			round( $summary_data['total_sales'] / $summary_data['tickets_sold'], 2 ) : 0;
		// Sort sales by date
		ksort( $sales_by_date );
		// Format data for charts
		$sales_chart_data = array();
		foreach ( $sales_by_date as $date => $amount ) {
			$sales_chart_data[] = array(
				'x' => $date,
				'y' => $amount,
			);
		}
		// Prepare response
		$response = array(
			'summary'            => $summary_data,
			'sales_chart'        => $sales_chart_data,
			'events_chart'       => array(
				'labels' => array_keys( $tickets_by_event ),
				'data'   => array_values( $tickets_by_event ),
			),
			'ticket_types_chart' => array(
				'labels' => array_keys( $ticket_types_data ),
				'data'   => array_values( $ticket_types_data ),
			),
			'weekday_chart'      => array(
				'labels' => array_keys( $sales_by_weekday ),
				'data'   => array_values( $sales_by_weekday ),
			),
			'detailed_data'      => array_values($detailed_data)
		);
		wp_send_json_success( $response );
	}
// AJAX handler for exporting data to CSV
	add_action( 'wp_ajax_mep_export_analytics_csv', 'mep_export_analytics_csv' );
	function mep_export_analytics_csv() {
		// Check nonce for security
		check_ajax_referer( 'mep_analytics_nonce', 'nonce' );
		// Get filter parameters
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : date( 'Y-m-d' );
		$event_id   = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 'all';
		// Define variables used throughout the function
		$_user_set_status = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
		$_order_status    = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
		$order_status     = array_values( $_order_status );
		// Convert dates to timestamp for comparison
		$start_timestamp = strtotime( $start_date );
		$end_timestamp   = strtotime( $end_date . ' 23:59:59' ); // Include the entire end day
		// Initialize CSV data
		$csv_data   = array();
		$csv_data[] = array(
			__( 'Event', 'mage-eventpress' ),
			__( 'Date', 'mage-eventpress' ),
			__( 'Tickets Sold', 'mage-eventpress' ),
			__( 'Total Sales', 'mage-eventpress' ),
			__( 'Available Seats', 'mage-eventpress' ),
			__( 'Occupancy Rate (%)', 'mage-eventpress' ),
		);
		// Get events based on filter
		$event_args = array(
			'post_type'      => 'mep_events',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);
		if ( $event_id !== 'all' ) {
			$event_args['p'] = $event_id; // Use 'p' instead of 'include' for direct ID query
		}
		$events = get_posts( $event_args );
		// Process each event
		foreach ( $events as $event ) {
			$event_id    = $event->ID;
			$event_title = $event->post_title;
			// Get attendees for this event
			$attendee_args = array(
				'post_type'      => 'mep_events_attendees',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'ea_event_id',
						'value'   => $event_id,
						'compare' => '=',
					)
				),
			);
			$attendees = get_posts( $attendee_args );
			// Initialize event data
			$event_data = array(
				'dates' => array(),
			);
			// Process each attendee
			foreach ( $attendees as $attendee ) {
				$attendee_id = $attendee->ID;
				// Get attendee data
				$order_id     = get_post_meta( $attendee_id, 'ea_order_id', true );
				$ticket_price = get_post_meta( $attendee_id, 'ea_ticket_price', true );
				$event_date   = get_post_meta( $attendee_id, 'ea_event_date', true );
				// Get order date
				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					continue;
				}
				$order_date = $order->get_date_created()->getTimestamp();
				// Check if order date is within the selected range
				if ( $order_date < $start_timestamp || $order_date > $end_timestamp ) {
					continue;
				}
				// Track event dates
				if ( ! isset( $event_data['dates'][ $event_date ] ) ) {
					$event_data['dates'][ $event_date ] = array(
						'tickets_sold' => 0,
						'total_sales'  => 0,
					);
				}
				$event_data['dates'][ $event_date ]['tickets_sold'] ++;
				$event_data['dates'][ $event_date ]['total_sales'] += floatval( $ticket_price );
			}
			// Process detailed data for each event date
			foreach ( $event_data['dates'] as $date => $date_data ) {
				// Get total seats and available seats
				$total_seats     = mep_event_total_seat( $event_id, 'total' );
				$available_seats = mep_get_event_total_available_seat( $event_id, $date );
				$occupancy_rate  = $total_seats > 0 ? round( ( $date_data['tickets_sold'] / $total_seats ) * 100, 2 ) : 0;
				$normalized_title = trim(html_entity_decode($event_title));
				$normalized_date = date('Y-m-d', strtotime($date));
				$csv_data[] = array(
					$normalized_title,
					$normalized_date,
					$date_data['tickets_sold'],
					$date_data['total_sales'],
					$available_seats,
					$occupancy_rate,
				);
			}
		}
		// Convert CSV data to string
		$csv_string = '';
		foreach ( $csv_data as $row ) {
			$csv_string .= implode( ',', $row ) . "\n";
		}
		// Send CSV data
		$filename = 'event-analytics-' . $start_date . '-to-' . $end_date . '.csv';
		wp_send_json_success( array(
			'filename' => $filename,
			'csv_data' => $csv_string,
		) );
	}
