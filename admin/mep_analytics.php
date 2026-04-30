<?php
/**
 * Event Analytics Dashboard
 *
 * Supports dual data sources:
 * 1. Form Builder Addon active: Uses mep_events_attendees CPT
 * 2. No Form Builder: Uses WooCommerce order items directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// ==================== ADMIN MENU ====================

add_action( 'admin_menu', 'mep_event_analytics_admin_menu' );
function mep_event_analytics_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=mep_events',
		__( 'Analytics', 'mage-eventpress' ),
		'<span style="color:#32c1a4">Analytics</span>',
		'manage_options',
		'mep_event_analytics_page',
		'mep_event_analytics_page'
	);
}

// ==================== RENDER PAGE ====================

function mep_event_analytics_page() {
	$end_date   = date( 'Y-m-d' );
	$start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
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
					<input type="date" id="mep-start-date" value="<?php echo esc_attr( $start_date ); ?>" class="mep-filter">
					<label for="mep-end-date"><?php esc_html_e( 'To:', 'mage-eventpress' ); ?></label>
					<input type="date" id="mep-end-date" value="<?php echo esc_attr( $end_date ); ?>" class="mep-filter">
				</div>
			</div>
			<div class="mep-filter-group">
				<label for="mep-event-filter"><?php esc_html_e( 'Event:', 'mage-eventpress' ); ?></label>
				<select id="mep-event-filter" class="mep-filter">
					<option value="all"><?php esc_html_e( 'All Events', 'mage-eventpress' ); ?></option>
					<?php foreach ( $events as $event ) : ?>
						<option value="<?php echo esc_attr( $event->ID ); ?>"><?php echo esc_html( $event->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php
			$category_lists = MPWEM_Global_Function::get_all_term_data( 'mep_cat' );
			if ( is_array( $category_lists ) && sizeof( $category_lists ) > 0 ) {
				?>
				<label>
					<span><?php esc_html_e( 'Category Filter', 'mage-eventpress' ); ?></span>
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
			<div class="mep-summary-cards">
				<div class="mep-card" id="mep-total-sales">
					<div class="mep-card-icon"><i class="fas fa-dollar-sign"></i></div>
					<div class="mep-card-content">
						<h3><?php esc_html_e( 'Total Sales', 'mage-eventpress' ); ?></h3>
						<p class="mep-card-value">0</p>
					</div>
				</div>
				<div class="mep-card" id="mep-tickets-sold">
					<div class="mep-card-icon"><i class="fas fa-ticket-alt"></i></div>
					<div class="mep-card-content">
						<h3><?php esc_html_e( 'Tickets Sold', 'mage-eventpress' ); ?></h3>
						<p class="mep-card-value">0</p>
					</div>
				</div>
				<div class="mep-card" id="mep-total-events">
					<div class="mep-card-icon"><i class="fas fa-calendar-alt"></i></div>
					<div class="mep-card-content">
						<h3><?php esc_html_e( 'Total Events', 'mage-eventpress' ); ?></h3>
						<p class="mep-card-value">0</p>
					</div>
				</div>
				<div class="mep-card" id="mep-avg-ticket-price">
					<div class="mep-card-icon"><i class="fas fa-chart-line"></i></div>
					<div class="mep-card-content">
						<h3><?php esc_html_e( 'Avg. Ticket Price', 'mage-eventpress' ); ?></h3>
						<p class="mep-card-value">0</p>
					</div>
				</div>
			</div>
			<div class="mep-charts-container">
				<div class="mep-chart-wrapper">
					<h2><?php esc_html_e( 'Sales Over Time', 'mage-eventpress' ); ?></h2>
					<div class="mep-chart-container"><canvas id="mep-sales-chart"></canvas></div>
				</div>
				<div class="mep-chart-wrapper">
					<h2><?php esc_html_e( 'Tickets Sold by Event', 'mage-eventpress' ); ?></h2>
					<div class="mep-chart-container"><canvas id="mep-events-chart"></canvas></div>
				</div>
			</div>
			<div class="mep-charts-container">
				<div class="mep-chart-wrapper">
					<h2><?php esc_html_e( 'Ticket Types Distribution', 'mage-eventpress' ); ?></h2>
					<div class="mep-chart-container"><canvas id="mep-ticket-types-chart"></canvas></div>
				</div>
				<div class="mep-chart-wrapper">
					<h2><?php esc_html_e( 'Sales by Day of Week', 'mage-eventpress' ); ?></h2>
					<div class="mep-chart-container"><canvas id="mep-weekday-chart"></canvas></div>
				</div>
			</div>
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
						<tr><td colspan="6"><?php esc_html_e( 'Loading data...', 'mage-eventpress' ); ?></td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}

// ==================== HELPER: DETECT DATA SOURCE ====================

/**
 * Detect which data source to use for analytics.
 * Priority: Form Builder attendee CPT > WooCommerce orders fallback.
 *
 * @return string 'attendees' or 'orders'
 */
function mep_analytics_detect_source() {
	if ( post_type_exists( 'mep_events_attendees' ) ) {
		return 'attendees';
	}
	return 'orders';
}

/**
 * Get valid order statuses for analytics.
 *
 * @return array Order statuses without 'wc-' prefix.
 */
function mep_analytics_get_order_statuses() {
	$_user_set_status = mep_get_option( 'seat_reserved_order_status', 'general_setting_sec', array( 'processing', 'completed' ) );
	$_order_status    = ! empty( $_user_set_status ) ? $_user_set_status : array( 'processing', 'completed' );
	$order_status     = array_values( $_order_status );
	return array_map( function( $s ) {
		return strpos( $s, 'wc-' ) === 0 ? substr( $s, 3 ) : $s;
	}, $order_status );
}

/**
 * Get filtered events.
 *
 * @param string|int $event_id 'all' or specific event ID.
 * @param string     $category Category name filter.
 * @return array WP_Post array.
 */
function mep_analytics_get_events( $event_id, $category = '' ) {
	$args = array(
		'post_type'      => 'mep_events',
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
	);
	if ( $event_id !== 'all' ) {
		$args['p'] = intval( $event_id );
	}
	if ( $category ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'mep_cat',
				'field'    => 'name',
				'terms'    => $category,
			),
		);
	}
	return get_posts( $args );
}

// ==================== DATA COLLECTORS ====================

/**
 * Collect analytics data from mep_events_attendees CPT (Form Builder mode).
 *
 * @param array $events      Filtered events.
 * @param int   $start_ts    Start timestamp.
 * @param int   $end_ts      End timestamp.
 * @param array $statuses    Valid order statuses.
 * @return array Aggregated data.
 */
function mep_analytics_collect_from_attendees( $events, $start_ts, $end_ts, $statuses ) {
	$data = array(
		'total_sales'      => 0,
		'tickets_sold'     => 0,
		'total_events'     => count( $events ),
		'avg_ticket_price' => 0,
		'sales_by_date'    => array(),
		'tickets_by_event' => array(),
		'ticket_types'     => array(),
		'sales_by_weekday' => array(
			'Sunday' => 0, 'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0,
			'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0,
		),
		'detailed'         => array(),
	);

	$event_ids = wp_list_pluck( $events, 'ID' );
	if ( empty( $event_ids ) ) {
		return $data;
	}

	foreach ( $events as $event ) {
		$eid         = $event->ID;
		$event_title = $event->post_title;

		$attendee_args = array(
			'post_type'      => 'mep_events_attendees',
			'posts_per_page' => - 1,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'ea_event_id',
					'value'   => $eid,
					'compare' => '=',
				),
			),
		);
		$attendees = get_posts( $attendee_args );

		$event_data = array(
			'tickets_sold' => 0,
			'total_sales'  => 0,
			'dates'        => array(),
		);
		$seen = array();

		foreach ( $attendees as $attendee ) {
			$aid       = $attendee->ID;
			$order_id  = get_post_meta( $aid, 'ea_order_id', true );
			$t_type    = get_post_meta( $aid, 'ea_ticket_type', true );
			$e_date    = get_post_meta( $aid, 'ea_event_date', true );
			$t_price   = get_post_meta( $aid, 'ea_ticket_price', true );
			$t_price   = is_numeric( $t_price ) ? floatval( $t_price ) : 0;

			if ( empty( $order_id ) ) {
				continue;
			}

			$unique_key = $order_id . '_' . $eid . '_' . $e_date . '_' . $t_type;
			if ( isset( $seen[ $unique_key ] ) ) {
				continue;
			}
			$seen[ $unique_key ] = true;

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}

			$order_status = $order->get_status();
			if ( ! in_array( $order_status, $statuses, true ) ) {
				continue;
			}

			$order_date_obj = $order->get_date_created();
			if ( ! $order_date_obj ) {
				continue;
			}
			$order_date_ts = $order_date_obj->getTimestamp();

			if ( $order_date_ts < $start_ts || $order_date_ts > $end_ts ) {
				continue;
			}

			// Aggregate
			$data['tickets_sold'] ++;
			$data['total_sales'] += $t_price;
			$event_data['tickets_sold'] ++;
			$event_data['total_sales'] += $t_price;

			$date_fmt = date( 'Y-m-d', $order_date_ts );
			$data['sales_by_date'][ $date_fmt ] = ( $data['sales_by_date'][ $date_fmt ] ?? 0 ) + $t_price;

			$weekday = date( 'l', $order_date_ts );
			$data['sales_by_weekday'][ $weekday ] = ( $data['sales_by_weekday'][ $weekday ] ?? 0 ) + $t_price;

			$type_label = $t_type ?: 'General';
			$data['ticket_types'][ $type_label ] = ( $data['ticket_types'][ $type_label ] ?? 0 ) + 1;

			if ( ! isset( $event_data['dates'][ $e_date ] ) ) {
				$event_data['dates'][ $e_date ] = array( 'tickets_sold' => 0, 'total_sales' => 0 );
			}
			$event_data['dates'][ $e_date ]['tickets_sold'] ++;
			$event_data['dates'][ $e_date ]['total_sales'] += $t_price;
		}

		$data['tickets_by_event'][ $event_title ] = $event_data['tickets_sold'];

		// Detailed
		foreach ( $event_data['dates'] as $date => $d_data ) {
			$total_seats     = mep_event_total_seat( $eid, 'total' );
			$total_seats     = is_numeric( $total_seats ) ? intval( $total_seats ) : 0;
			$available_seats = mep_get_event_total_available_seat( $eid, $date );
			$available_seats = is_numeric( $available_seats ) ? intval( $available_seats ) : 0;
			$occupancy       = $total_seats > 0 ? round( ( $d_data['tickets_sold'] / $total_seats ) * 100, 2 ) : 0;
			$norm_date       = ! empty( $date ) ? date( 'Y-m-d', strtotime( $date ) ) : '';
			if ( empty( $norm_date ) ) {
				continue;
			}
			$data['detailed'][] = array(
				'event'           => trim( html_entity_decode( $event_title, ENT_QUOTES, 'UTF-8' ) ),
				'date'            => $norm_date,
				'tickets_sold'    => $d_data['tickets_sold'],
				'total_sales'     => $d_data['total_sales'],
				'available_seats' => $available_seats,
				'occupancy_rate'  => $occupancy,
			);
		}
	}

	$data['avg_ticket_price'] = $data['tickets_sold'] > 0
		? round( $data['total_sales'] / $data['tickets_sold'], 2 )
		: 0;

	return $data;
}

/**
 * Collect analytics data from WooCommerce orders (no Form Builder).
 *
 * @param array $events   Filtered events.
 * @param int   $start_ts Start timestamp.
 * @param int   $end_ts   End timestamp.
 * @param array $statuses Valid order statuses.
 * @return array Aggregated data.
 */
function mep_analytics_collect_from_orders( $events, $start_ts, $end_ts, $statuses ) {
	$data = array(
		'total_sales'      => 0,
		'tickets_sold'     => 0,
		'total_events'     => count( $events ),
		'avg_ticket_price' => 0,
		'sales_by_date'    => array(),
		'tickets_by_event' => array(),
		'ticket_types'     => array(),
		'sales_by_weekday' => array(
			'Sunday' => 0, 'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0,
			'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0,
		),
		'detailed'         => array(),
	);

	// Build event map: event_id => event_title
	$event_map = array();
	foreach ( $events as $event ) {
		$event_map[ $event->ID ] = $event->post_title;
	}
	if ( empty( $event_map ) ) {
		return $data;
	}

	// Build reverse map: product_id => event_id
	$product_to_event = array();
	foreach ( $events as $event ) {
		$product_id = get_post_meta( $event->ID, 'link_wc_product', true );
		if ( $product_id ) {
			$product_to_event[ intval( $product_id ) ] = $event->ID;
		}
	}

	// Query WooCommerce orders
	$orders = wc_get_orders( array(
		'limit'        => -1,
		'status'       => $statuses,
		'date_created' => $start_ts . '...' . $end_ts,
		'return'       => 'objects',
	) );

	$event_date_agg = array(); // event_id => date => data

	foreach ( $orders as $order ) {
		$order_date_obj = $order->get_date_created();
		if ( ! $order_date_obj ) {
			continue;
		}
		$order_date_ts = $order_date_obj->getTimestamp();
		if ( $order_date_ts < $start_ts || $order_date_ts > $end_ts ) {
			continue;
		}

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			if ( ! $product_id || ! isset( $product_to_event[ $product_id ] ) ) {
				continue;
			}

			$eid         = $product_to_event[ $product_id ];
			$event_title = $event_map[ $eid ];
			$item_qty    = $item->get_quantity();
			$item_total  = floatval( $item->get_total() );
			$ticket_type = $item->get_meta( 'Ticket Type' ) ?: $item->get_name();
			$event_date  = $item->get_meta( 'Event Date' ) ?: date( 'Y-m-d', $order_date_ts );

			// Summary
			$data['tickets_sold'] += $item_qty;
			$data['total_sales']  += $item_total;

			// By event
			$data['tickets_by_event'][ $event_title ] = ( $data['tickets_by_event'][ $event_title ] ?? 0 ) + $item_qty;

			// By date
			$date_fmt = date( 'Y-m-d', $order_date_ts );
			$data['sales_by_date'][ $date_fmt ] = ( $data['sales_by_date'][ $date_fmt ] ?? 0 ) + $item_total;

			// By weekday
			$weekday = date( 'l', $order_date_ts );
			$data['sales_by_weekday'][ $weekday ] = ( $data['sales_by_weekday'][ $weekday ] ?? 0 ) + $item_total;

			// By ticket type
			$type_label = $ticket_type ?: 'General';
			$data['ticket_types'][ $type_label ] = ( $data['ticket_types'][ $type_label ] ?? 0 ) + $item_qty;

			// Detailed aggregation
			if ( ! isset( $event_date_agg[ $eid ] ) ) {
				$event_date_agg[ $eid ] = array(
					'event' => $event_title,
					'dates' => array(),
				);
			}
			if ( ! isset( $event_date_agg[ $eid ]['dates'][ $event_date ] ) ) {
				$event_date_agg[ $eid ]['dates'][ $event_date ] = array(
					'tickets_sold' => 0,
					'total_sales'  => 0,
				);
			}
			$event_date_agg[ $eid ]['dates'][ $event_date ]['tickets_sold'] += $item_qty;
			$event_date_agg[ $eid ]['dates'][ $event_date ]['total_sales']  += $item_total;
		}
	}

	// Build detailed output with seat info
	foreach ( $event_date_agg as $eid => $e_data ) {
		foreach ( $e_data['dates'] as $date => $d_data ) {
			$total_seats     = mep_event_total_seat( $eid, 'total' );
			$total_seats     = is_numeric( $total_seats ) ? intval( $total_seats ) : 0;
			$available_seats = mep_get_event_total_available_seat( $eid, $date );
			$available_seats = is_numeric( $available_seats ) ? intval( $available_seats ) : 0;
			$occupancy       = $total_seats > 0 ? round( ( $d_data['tickets_sold'] / $total_seats ) * 100, 2 ) : 0;
			$norm_date       = ! empty( $date ) ? date( 'Y-m-d', strtotime( $date ) ) : '';
			if ( empty( $norm_date ) ) {
				continue;
			}
			$data['detailed'][] = array(
				'event'           => trim( html_entity_decode( $e_data['event'], ENT_QUOTES, 'UTF-8' ) ),
				'date'            => $norm_date,
				'tickets_sold'    => $d_data['tickets_sold'],
				'total_sales'     => $d_data['total_sales'],
				'available_seats' => $available_seats,
				'occupancy_rate'  => $occupancy,
			);
		}
	}

	$data['avg_ticket_price'] = $data['tickets_sold'] > 0
		? round( $data['total_sales'] / $data['tickets_sold'], 2 )
		: 0;

	return $data;
}

// ==================== AJAX HANDLERS ====================

add_action( 'wp_ajax_mep_get_analytics_data', 'mep_get_analytics_data' );
function mep_get_analytics_data() {
	check_ajax_referer( 'mep_analytics_nonce', 'nonce' );

	// Parse inputs
	$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : date( 'Y-m-d', strtotime( '-30 days' ) );
	$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : date( 'Y-m-d' );
	$raw_event_id = isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 'all';
	$event_id   = ( $raw_event_id === 'all' ) ? 'all' : intval( $raw_event_id );
	$category   = isset( $_POST['filter_with_category'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_with_category'] ) ) : '';

	$start_ts   = strtotime( $start_date );
	$end_ts     = strtotime( $end_date . ' 23:59:59' );
	$statuses   = mep_analytics_get_order_statuses();
	$events     = mep_analytics_get_events( $event_id, $category );
	$source     = mep_analytics_detect_source();

	// Collect data
	if ( $source === 'attendees' ) {
		$raw = mep_analytics_collect_from_attendees( $events, $start_ts, $end_ts, $statuses );
	} else {
		$raw = mep_analytics_collect_from_orders( $events, $start_ts, $end_ts, $statuses );
	}

	// Sort sales by date
	ksort( $raw['sales_by_date'] );
	$sales_chart = array();
	foreach ( $raw['sales_by_date'] as $d => $amt ) {
		$sales_chart[] = array( 'x' => $d, 'y' => $amt );
	}

	wp_send_json_success( array(
		'summary'            => array(
			'total_sales'      => $raw['total_sales'],
			'tickets_sold'     => $raw['tickets_sold'],
			'total_events'     => $raw['total_events'],
			'avg_ticket_price' => $raw['avg_ticket_price'],
		),
		'sales_chart'        => $sales_chart,
		'events_chart'       => array(
			'labels' => array_keys( $raw['tickets_by_event'] ),
			'data'   => array_values( $raw['tickets_by_event'] ),
		),
		'ticket_types_chart' => array(
			'labels' => array_keys( $raw['ticket_types'] ),
			'data'   => array_values( $raw['ticket_types'] ),
		),
		'weekday_chart'      => array(
			'labels' => array_keys( $raw['sales_by_weekday'] ),
			'data'   => array_values( $raw['sales_by_weekday'] ),
		),
		'detailed_data'      => $raw['detailed'],
	) );
}

add_action( 'wp_ajax_mep_export_analytics_csv', 'mep_export_analytics_csv' );
function mep_export_analytics_csv() {
	check_ajax_referer( 'mep_analytics_nonce', 'nonce' );

	$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : date( 'Y-m-d', strtotime( '-30 days' ) );
	$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : date( 'Y-m-d' );
	$raw_event_id = isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 'all';
	$event_id   = ( $raw_event_id === 'all' ) ? 'all' : intval( $raw_event_id );
	$category   = isset( $_POST['filter_with_category'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_with_category'] ) ) : '';

	$start_ts   = strtotime( $start_date );
	$end_ts     = strtotime( $end_date . ' 23:59:59' );
	$statuses   = mep_analytics_get_order_statuses();
	$events     = mep_analytics_get_events( $event_id, $category );
	$source     = mep_analytics_detect_source();

	if ( $source === 'attendees' ) {
		$raw = mep_analytics_collect_from_attendees( $events, $start_ts, $end_ts, $statuses );
	} else {
		$raw = mep_analytics_collect_from_orders( $events, $start_ts, $end_ts, $statuses );
	}

	$csv_data = array();
	$csv_data[] = array(
		__( 'Event', 'mage-eventpress' ),
		__( 'Date', 'mage-eventpress' ),
		__( 'Tickets Sold', 'mage-eventpress' ),
		__( 'Total Sales', 'mage-eventpress' ),
		__( 'Available Seats', 'mage-eventpress' ),
		__( 'Occupancy Rate (%)', 'mage-eventpress' ),
	);

	foreach ( $raw['detailed'] as $row ) {
		$csv_data[] = array(
			$row['event'],
			$row['date'],
			$row['tickets_sold'],
			$row['total_sales'],
			$row['available_seats'],
			$row['occupancy_rate'],
		);
	}

	$csv_string = '';
	foreach ( $csv_data as $row ) {
		$escaped = array_map( function( $field ) {
			$field = str_replace( '"', '""', $field );
			return '"' . $field . '"';
		}, $row );
		$csv_string .= implode( ',', $escaped ) . "\n";
	}

	$filename = 'event-analytics-' . $start_date . '-to-' . $end_date . '.csv';
	wp_send_json_success( array(
		'filename' => $filename,
		'csv_data' => $csv_string,
	) );
}
