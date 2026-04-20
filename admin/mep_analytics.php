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
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : date( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : date( 'Y-m-d' );
		// FIX: Don't use intval() on 'all' - it returns 0. Keep as string first.
		$raw_event_id = isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 'all';
		$event_id   = ( $raw_event_id === 'all' ) ? 'all' : intval( $raw_event_id );
		$filter_with_category = isset( $_POST['filter_with_category'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_with_category'] ) ) : '';

		// Define order statuses to include
		$_user_set_status = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
		$_order_status    = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
		$order_status     = array_values( $_order_status );
		// Ensure statuses don't have 'wc-' prefix for wc_get_orders
		$wc_statuses = array_map( function( $s ) {
			return strpos( $s, 'wc-' ) === 0 ? substr( $s, 3 ) : $s;
		}, $order_status );

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
			$event_args['p'] = $event_id;
		}
		if ( $filter_with_category ) {
			$event_args['tax_query'] = array(
				array(
					'taxonomy' => 'mep_cat',
					'field'    => 'name',
					'terms'    => $filter_with_category,
				),
			);
		}

		$events                       = get_posts( $event_args );
		$summary_data['total_events'] = count( $events );

		// Build a map of event_id => hidden_product_id for quick lookup
		$event_product_map = array();
		foreach ( $events as $event ) {
			$product_id = get_post_meta( $event->ID, 'link_wc_product', true );
			if ( $product_id ) {
				$event_product_map[ $event->ID ] = intval( $product_id );
			}
		}

		// Get all WooCommerce orders in the date range with valid statuses
		$order_args = array(
			'limit'        => -1,
			'status'       => $wc_statuses,
			'date_created' => $start_timestamp . '...' . $end_timestamp,
			'return'       => 'objects',
		);
		$orders = wc_get_orders( $order_args );

		// Process each order
		foreach ( $orders as $order ) {
			$order_id     = $order->get_id();
			$order_date   = $order->get_date_created();
			$order_date_ts = $order_date ? $order_date->getTimestamp() : 0;

			if ( ! $order_date_ts || $order_date_ts < $start_timestamp || $order_date_ts > $end_timestamp ) {
				continue;
			}

			// Process each item in the order
			foreach ( $order->get_items() as $item ) {
				$product_id = $item->get_product_id();
				if ( ! $product_id ) {
					continue;
				}

				// Check if this product is linked to an event
				$linked_event_id = get_post_meta( $product_id, 'link_mep_event', true );
				if ( ! $linked_event_id ) {
					continue; // Not an event product
				}
				$linked_event_id = intval( $linked_event_id );

				// Check if this event is in our filtered list
				if ( $event_id !== 'all' && $linked_event_id !== $event_id ) {
					continue;
				}

				// Get event info
				$event_post = get_post( $linked_event_id );
				if ( ! $event_post || $event_post->post_status !== 'publish' ) {
					continue;
				}
				$event_title = $event_post->post_title;

				// Get ticket info from order item meta
				$ticket_type  = $item->get_meta( 'Ticket Type' ) ?: $item->get_name();
				$event_date   = $item->get_meta( 'Event Date' ) ?: '';
				$item_total   = floatval( $item->get_total() );
				$item_qty     = $item->get_quantity();

				// Update summary data
				$summary_data['tickets_sold'] += $item_qty;
				$summary_data['total_sales']  += $item_total;

				// Update tickets by event
				if ( ! isset( $tickets_by_event[ $event_title ] ) ) {
					$tickets_by_event[ $event_title ] = 0;
				}
				$tickets_by_event[ $event_title ] += $item_qty;

				// Format date for chart
				$date_formatted = date( 'Y-m-d', $order_date_ts );

				// Update sales by date
				if ( ! isset( $sales_by_date[ $date_formatted ] ) ) {
					$sales_by_date[ $date_formatted ] = 0;
				}
				$sales_by_date[ $date_formatted ] += $item_total;

				// Update sales by weekday
				$weekday                      = date( 'l', $order_date_ts );
				$sales_by_weekday[ $weekday ] += $item_total;

				// Update ticket types data
				$type_label = $ticket_type ?: 'General';
				if ( ! isset( $ticket_types_data[ $type_label ] ) ) {
					$ticket_types_data[ $type_label ] = 0;
				}
				$ticket_types_data[ $type_label ] += $item_qty;

				// Track event dates for detailed data
				$date_key = $event_date ?: $date_formatted;
				if ( ! isset( $detailed_data[ $linked_event_id ] ) ) {
					$detailed_data[ $linked_event_id ] = array(
						'event_id'        => $linked_event_id,
						'event'           => $event_title,
						'dates'           => array(),
						'tickets_sold'    => 0,
						'total_sales'     => 0,
					);
				}
				$detailed_data[ $linked_event_id ]['tickets_sold'] += $item_qty;
				$detailed_data[ $linked_event_id ]['total_sales']  += $item_total;

				if ( ! isset( $detailed_data[ $linked_event_id ]['dates'][ $date_key ] ) ) {
					$detailed_data[ $linked_event_id ]['dates'][ $date_key ] = array(
						'tickets_sold' => 0,
						'total_sales'  => 0,
					);
				}
				$detailed_data[ $linked_event_id ]['dates'][ $date_key ]['tickets_sold'] += $item_qty;
				$detailed_data[ $linked_event_id ]['dates'][ $date_key ]['total_sales']  += $item_total;
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

		// Build detailed data array with seat info
		$detailed_output = array();
		foreach ( $detailed_data as $event_id => $event_summary ) {
			foreach ( $event_summary['dates'] as $date => $date_data ) {
				$total_seats     = mep_event_total_seat( $event_id, 'total' );
				$total_seats     = is_numeric( $total_seats ) ? intval( $total_seats ) : 0;
				$available_seats = mep_get_event_total_available_seat( $event_id, $date );
				$available_seats = is_numeric( $available_seats ) ? intval( $available_seats ) : 0;
				$occupancy_rate  = $total_seats > 0 ? round( ( $date_data['tickets_sold'] / $total_seats ) * 100, 2 ) : 0;

				$normalized_date = ! empty( $date ) ? date( 'Y-m-d', strtotime( $date ) ) : '';
				if ( empty( $normalized_date ) ) {
					continue;
				}

				$detailed_output[] = array(
					'event'           => trim( html_entity_decode( $event_summary['event'], ENT_QUOTES, 'UTF-8' ) ),
					'date'            => $normalized_date,
					'tickets_sold'    => $date_data['tickets_sold'],
					'total_sales'     => $date_data['total_sales'],
					'available_seats' => $available_seats,
					'occupancy_rate'  => $occupancy_rate,
				);
			}
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
			'detailed_data'      => $detailed_output,
		);
		wp_send_json_success( $response );
	}
// AJAX handler for exporting data to CSV
	add_action( 'wp_ajax_mep_export_analytics_csv', 'mep_export_analytics_csv' );
	function mep_export_analytics_csv() {
		// Check nonce for security
		check_ajax_referer( 'mep_analytics_nonce', 'nonce' );

		// Get filter parameters
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : date( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : date( 'Y-m-d' );
		// FIX: Don't use intval() on 'all' - it returns 0. Keep as string first.
		$raw_event_id = isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 'all';
		$event_id   = ( $raw_event_id === 'all' ) ? 'all' : intval( $raw_event_id );

		// Define order statuses to include
		$_user_set_status = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
		$_order_status    = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
		$order_status     = array_values( $_order_status );
		// Ensure statuses don't have 'wc-' prefix for wc_get_orders
		$wc_statuses = array_map( function( $s ) {
			return strpos( $s, 'wc-' ) === 0 ? substr( $s, 3 ) : $s;
		}, $order_status );

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
			$event_args['p'] = $event_id;
		}

		$events = get_posts( $event_args );

		// Build a map of event_id => event_title
		$event_map = array();
		foreach ( $events as $event ) {
			$event_map[ $event->ID ] = $event->post_title;
		}

		// Get all WooCommerce orders in the date range with valid statuses
		$order_args = array(
			'limit'        => -1,
			'status'       => $wc_statuses,
			'date_created' => $start_timestamp . '...' . $end_timestamp,
			'return'       => 'objects',
		);
		$orders = wc_get_orders( $order_args );

		// Aggregate data by event and date
		$event_date_data = array();

		// Process each order
		foreach ( $orders as $order ) {
			$order_date   = $order->get_date_created();
			$order_date_ts = $order_date ? $order_date->getTimestamp() : 0;

			if ( ! $order_date_ts || $order_date_ts < $start_timestamp || $order_date_ts > $end_timestamp ) {
				continue;
			}

			// Process each item in the order
			foreach ( $order->get_items() as $item ) {
				$product_id = $item->get_product_id();
				if ( ! $product_id ) {
					continue;
				}

				// Check if this product is linked to an event
				$linked_event_id = get_post_meta( $product_id, 'link_mep_event', true );
				if ( ! $linked_event_id ) {
					continue; // Not an event product
				}
				$linked_event_id = intval( $linked_event_id );

				// Check if this event is in our filtered list
				if ( $event_id !== 'all' && $linked_event_id !== $event_id ) {
					continue;
				}
				if ( ! isset( $event_map[ $linked_event_id ] ) ) {
					continue;
				}

				$event_title = $event_map[ $linked_event_id ];
				$event_date  = $item->get_meta( 'Event Date' ) ?: date( 'Y-m-d', $order_date_ts );
				$item_qty    = $item->get_quantity();
				$item_total  = floatval( $item->get_total() );

				$key = $linked_event_id . '||' . $event_date;
				if ( ! isset( $event_date_data[ $key ] ) ) {
					$event_date_data[ $key ] = array(
						'event_id'     => $linked_event_id,
						'event_title'  => $event_title,
						'date'         => $event_date,
						'tickets_sold' => 0,
						'total_sales'  => 0,
					);
				}
				$event_date_data[ $key ]['tickets_sold'] += $item_qty;
				$event_date_data[ $key ]['total_sales']  += $item_total;
			}
		}

		// Process aggregated data for CSV
		foreach ( $event_date_data as $data ) {
			$event_id        = $data['event_id'];
			$event_title     = $data['event_title'];
			$date            = $data['date'];
			$tickets_sold    = $data['tickets_sold'];
			$total_sales     = $data['total_sales'];

			// Get total seats and available seats
			$total_seats     = mep_event_total_seat( $event_id, 'total' );
			$total_seats     = is_numeric( $total_seats ) ? intval( $total_seats ) : 0;
			$available_seats = mep_get_event_total_available_seat( $event_id, $date );
			$available_seats = is_numeric( $available_seats ) ? intval( $available_seats ) : 0;
			$occupancy_rate  = $total_seats > 0 ? round( ( $tickets_sold / $total_seats ) * 100, 2 ) : 0;

			$normalized_title = trim( html_entity_decode( $event_title, ENT_QUOTES, 'UTF-8' ) );
			$normalized_date  = ! empty( $date ) ? date( 'Y-m-d', strtotime( $date ) ) : '';

			if ( empty( $normalized_date ) ) {
				continue;
			}

			$csv_data[] = array(
				$normalized_title,
				$normalized_date,
				$tickets_sold,
				$total_sales,
				$available_seats,
				$occupancy_rate,
			);
		}

		// Convert CSV data to string with proper escaping
		$csv_string = '';
		foreach ( $csv_data as $row ) {
			$escaped_row = array_map( function( $field ) {
				$field = str_replace( '"', '""', $field );
				return '"' . $field . '"';
			}, $row );
			$csv_string .= implode( ',', $escaped_row ) . "\n";
		}

		// Send CSV data
		$filename = 'event-analytics-' . $start_date . '-to-' . $end_date . '.csv';
		wp_send_json_success( array(
			'filename' => $filename,
			'csv_data' => $csv_string,
		) );
	}
