<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Query' ) ) {
		class MPWEM_Query {
			public function __construct() { }
			public static function query_post_type( $post_type, $show = - 1, $page = 1 ): WP_Query {
				$args = array(
					'post_type'      => $post_type,
					'posts_per_page' => $show,
					'paged'          => $page,
					'post_status'    => 'publish'
				);
				return new WP_Query( $args );
			}
			public static function event_query( $show, $sort = '', $cat = '', $org = '', $city = '', $country = '', $evnt_type = 'upcoming', $state = '', $year = '', $paged_override = 0, $tag = '' ) {
				$event_expire_on_old = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
				$event_order_by      = mep_get_option( 'mep_event_list_order_by', 'general_setting_sec', 'meta_value' );
				$event_expire_on     = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
				$now                 = current_time( 'Y-m-d H:i:s' );
				if ( $paged_override && is_numeric( $paged_override ) ) {
					$paged = intval( $paged_override );
				} elseif ( get_query_var( 'paged' ) ) {
					$paged = get_query_var( 'paged' );
				} elseif ( get_query_var( 'page' ) ) {
					$paged = get_query_var( 'page' );
				} else {
					$paged = 1;
				}
				$etype          = $evnt_type == 'expired' ? '<' : '>';
				$cat_id         = explode( ',', $cat );
				$org_id         = explode( ',', $org );
				$tag_id         = explode( ',', $tag );
				$cat_filter     = ! empty( $cat ) ? array(
					'taxonomy' => 'mep_cat',
					'field'    => 'term_id',
					'terms'    => $cat_id
				) : '';
				$org_filter     = ! empty( $org ) ? array(
					'taxonomy' => 'mep_org',
					'field'    => 'term_id',
					'terms'    => $org_id
				) : '';
				$tag_filter     = ! empty( $tag ) ? array(
					'taxonomy' => 'mep_tag',
					'field'    => 'term_id',
					'terms'    => $tag_id
				) : '';
				$city_filter    = ! empty( $city ) ? array(
					'key'     => 'mep_city',
					'value'   => $city,
					'compare' => 'LIKE'
				) : '';
				$state_filter   = ! empty( $state ) ? array(
					'key'     => 'mep_state',
					'value'   => $state,
					'compare' => 'LIKE'
				) : '';
				$country_filter = ! empty( $country ) ? array(
					'key'     => 'mep_country',
					'value'   => $country,
					'compare' => 'LIKE'
				) : '';
				$year_filter    = '';
				if ( ! empty( $year ) && preg_match( '/^\\d{4}$/', $year ) ) {
					$year_filter = array(
						'key'     => 'event_start_datetime',
						'value'   => array( $year . '-01-01 00:00:00', $year . '-12-31 23:59:59' ),
						'compare' => 'BETWEEN',
						'type'    => 'DATETIME',
					);
				}
				$expire_filter = ! empty( $event_expire_on ) ? array(
					'key'     => $event_expire_on,
					'value'   => $now,
					'compare' => $etype,
					'type'    => 'DATETIME'
				) : '';
				// Build meta_query with only non-empty filters
				$meta_query_parts = array();
				if ( ! empty( $expire_filter ) ) {
					$meta_query_parts[] = $expire_filter;
				}
				if ( ! empty( $city_filter ) ) {
					$meta_query_parts[] = $city_filter;
				}
				if ( ! empty( $state_filter ) ) {
					$meta_query_parts[] = $state_filter;
				}
				if ( ! empty( $country_filter ) ) {
					$meta_query_parts[] = $country_filter;
				}
				if ( ! empty( $year_filter ) ) {
					$meta_query_parts[] = $year_filter;
				}
				// Only add relation if we have actual query parts
				if ( count( $meta_query_parts ) > 1 ) {
					$meta_query = array( 'relation' => 'AND' );
					foreach ( $meta_query_parts as $part ) {
						$meta_query[] = $part;
					}
				} elseif ( count( $meta_query_parts ) === 1 ) {
					$meta_query = $meta_query_parts[0];
				} else {
					$meta_query = '';
				}
				// Build tax_query with only non-empty filters
				$tax_query_parts = array();
				if ( ! empty( $cat_filter ) ) {
					$tax_query_parts[] = $cat_filter;
				}
				if ( ! empty( $org_filter ) ) {
					$tax_query_parts[] = $org_filter;
				}
				if ( ! empty( $tag_filter ) ) {
					$tax_query_parts[] = $tag_filter;
				}
				// Only add relation if we have actual query parts
				if ( count( $tax_query_parts ) > 1 ) {
					$tax_query = array( 'relation' => 'AND' );
					foreach ( $tax_query_parts as $part ) {
						$tax_query[] = $part;
					}
				} elseif ( count( $tax_query_parts ) === 1 ) {
					$tax_query = $tax_query_parts[0];
				} else {
					$tax_query = '';
				}
				$args = array(
					'post_type'      => array( 'mep_events' ),
					'paged'          => $paged,
					'posts_per_page' => $show,
					'order'          => $sort,
					'orderby'        => $event_order_by,
					'meta_key'       => 'event_start_datetime',
					'meta_query'     => $meta_query,
					'tax_query'      => $tax_query
				);
				return new WP_Query( $args );
			}
			public static function attendee_query( $filter_args = [], $show = - 1, $page = 1 ) {
				$meta_query = [];
				if ( array_key_exists( 'post_id', $filter_args ) && $filter_args['post_id'] ) {
					$meta_query[] = array(
						'key'     => 'ea_event_id',
						'value'   => $filter_args['post_id'],
						'compare' => '='
					);
				}
				if ( array_key_exists( 'ea_user_id', $filter_args ) && $filter_args['ea_user_id'] ) {
					$meta_query[] = array(
						'key'     => 'ea_user_id',
						'value'   => $filter_args['ea_user_id'],
						'compare' => '='
					);
				}
				if ( array_key_exists( 'event_date', $filter_args ) && $filter_args['event_date'] ) {
					$meta_query[] = array(
						'key'     => 'ea_event_date',
						'value'   => $filter_args['event_date'],
						'compare' => 'LIKE'
					);
				}
				if ( array_key_exists( 'ea_ticket_type', $filter_args ) && $filter_args['ea_ticket_type'] ) {
					$meta_query[] = array(
						'key'     => 'ea_ticket_type',
						'value'   => $filter_args['ea_ticket_type'],
						'compare' => '='
					);
				}
				if ( array_key_exists( 'ea_seat_name', $filter_args ) && $filter_args['ea_seat_name'] ) {
					$meta_query[] = array(
						'key'     => 'ea_seat_name',
						'value'   => $filter_args['ea_seat_name'],
						'compare' => '='
					);
				}
				if ( array_key_exists( 'mep_checkin', $filter_args ) && $filter_args['mep_checkin'] ) {
					$meta_query[] = array(
						'key'     => 'mep_checkin',
						'value'   => $filter_args['mep_checkin'],
						'compare' => '='
					);
				}
				if ( array_key_exists( 'filter_key', $filter_args ) && $filter_args['filter_key'] && array_key_exists( 'filter_value', $filter_args ) && $filter_args['filter_value'] ) {
					$meta_query[] = array(
						'key'     => $filter_args['filter_key'],
						'value'   => $filter_args['filter_value'],
						'compare' => '='
					);
				}
				$booked_status   = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'seat_reserved_order_status', [ 'processing', 'completed' ] );
				$booked_status[] = 'partially-paid';
				$booked_status   = array_values( $booked_status );
				$meta_query[]    = array(
					'key'     => 'ea_order_status',
					'value'   => $booked_status,
					'compare' => 'IN'
				);
				$args            = array(
					'post_type'      => 'mep_events_attendees',
					'posts_per_page' => $show,
					'paged'          => $page,
					'meta_query'     => $meta_query
				);
				return new WP_Query( $args );
			}
		}
		new MPWEM_Query();
	}
