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
			public static function get_all_post_ids( $post_type, $show = - 1, $page = 1, $status = 'publish' ): array {
				$all_data = get_posts( array(
					'fields'         => 'ids',
					'post_type'      => $post_type,
					'posts_per_page' => $show,
					'paged'          => $page,
					'post_status'    => $status
				) );
				$all_data = array_unique( $all_data );
				sort( $all_data );
				return $all_data;
			}
			public static function get_all_post_meta_value( $meta_key, $post_type = 'mep_events', $status = 'publish' ) {
				global $wpdb;
				$sql         = $wpdb->prepare(
					"    SELECT DISTINCT pm.meta_value    FROM {$wpdb->postmeta} pm    INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id    WHERE pm.meta_key = %s      AND p.post_status = %s      AND p.post_type = %s    ",
					$meta_key, $status, $post_type
				);
				$meta_values = $wpdb->get_col( $sql );
				$meta_values = array_values( array_filter( $meta_values, 'strlen' ) );
				$meta_values = array_unique( $meta_values );
				sort( $meta_values, SORT_NATURAL );
				return $meta_values;
			}
			public static function event_query( $show, $sort = '', $cat = '', $org = '', $city = '', $country = '', $evnt_type = 'upcoming', $state = '', $year = '', $paged_override = 0, $tag = '' ) {
				$event_expire_on_old = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
				$event_order_by      = mep_get_option( 'mep_event_list_order_by', 'general_setting_sec', 'meta_value' );
				$event_expire_on     = $event_expire_on_old == 'event_end_datetime' ? 'event_end_datetime' : $event_expire_on_old;
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
				
				// Taxonomy filters helper
				$process_tax = function($input, $taxonomy) {
					if ( empty( $input ) ) return '';
					$terms = explode( ',', $input );
					$ids = [];
					$slugs = [];
					foreach($terms as $term) {
						$term = trim($term);
						if ( is_numeric($term) ) {
							$id = intval($term);
							if ($id !== 0) $ids[] = $id;
						} elseif (!empty($term)) {
							$slugs[] = $term;
						}
					}
					
					$clauses = [];
					if ( ! empty($ids) ) {
						$clauses[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $ids
						);
					}
					
					if ( ! empty($slugs) ) {
						$clauses[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $slugs
						);
						$clauses[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'name',
							'terms'    => $slugs
						);
					}
					
					if (count($clauses) === 0) return '';
					if (count($clauses) === 1) return $clauses[0];
					
					return array_merge(['relation' => 'OR'], $clauses);
				};

				$cat_filter = $process_tax($cat, 'mep_cat');
				$org_filter = $process_tax($org, 'mep_org');
				$tag_filter = $process_tax($tag, 'mep_tag');

				// Location Filter Resolver (Post Meta OR Organizer Meta)
				$matching_post_ids = null;
				$apply_location_filter = false;

				$resolve_location = function($value, $meta_key, $term_meta_key) {
					global $wpdb;
					// 1. Get post IDs where city/state is in postmeta
					$ids_from_meta = $wpdb->get_col($wpdb->prepare(
						"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value LIKE %s",
						$meta_key, '%' . $wpdb->esc_like($value) . '%'
					));

					// 2. Get organizer term IDs where city/state is in termmeta
					$term_ids = $wpdb->get_col($wpdb->prepare(
						"SELECT term_id FROM $wpdb->termmeta WHERE meta_key = %s AND meta_value LIKE %s",
						$term_meta_key, '%' . $wpdb->esc_like($value) . '%'
					));

					$ids_from_terms = [];
					if (!empty($term_ids)) {
						// Get posts assigned to these organizers
						$ids_from_terms = get_posts(array(
							'post_type' => 'mep_events',
							'posts_per_page' => -1,
							'fields' => 'ids',
							'post_status' => 'publish',
							'tax_query' => array(
								array(
									'taxonomy' => 'mep_org',
									'field'    => 'term_id',
									'terms'    => $term_ids,
								),
							),
						));
					}

					return array_unique(array_merge($ids_from_meta, $ids_from_terms));
				};

				if (!empty($city)) {
					$apply_location_filter = true;
					$city_ids = $resolve_location($city, 'mep_city', 'org_city');
					$matching_post_ids = is_null($matching_post_ids) ? $city_ids : array_intersect($matching_post_ids, $city_ids);
				}

				if (!empty($state)) {
					$apply_location_filter = true;
					$state_ids = $resolve_location($state, 'mep_state', 'org_state');
					$matching_post_ids = is_null($matching_post_ids) ? $state_ids : array_intersect($matching_post_ids, $state_ids);
				}

				if (!empty($country)) {
					$apply_location_filter = true;
					$country_ids = $resolve_location($country, 'mep_country', 'org_country');
					$matching_post_ids = is_null($matching_post_ids) ? $country_ids : array_intersect($matching_post_ids, $country_ids);
				}

				$year_filter = '';
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

				// Build meta_query
				$meta_query = array();
				if ( ! empty( $expire_filter ) ) {
					$meta_query[] = $expire_filter;
				}
				if ( ! empty( $year_filter ) ) {
					$meta_query[] = $year_filter;
				}
				if ( count( $meta_query ) > 1 ) {
					$meta_query['relation'] = 'AND';
				}

				// Build tax_query
				$tax_query = array();
				if ( ! empty( $cat_filter ) ) {
					$tax_query[] = $cat_filter;
				}
				if ( ! empty( $org_filter ) ) {
					$tax_query[] = $org_filter;
				}
				if ( ! empty( $tag_filter ) ) {
					$tax_query[] = $tag_filter;
				}
				if ( count( $tax_query ) > 1 ) {
					$tax_query['relation'] = 'AND';
				}

				$args = array(
					'post_type'      => array( 'mep_events' ),
					'paged'          => $paged,
					'posts_per_page' => $show,
					'post_status'    => array( 'publish' ),
					'order'          => $sort,
					'orderby'        => $event_order_by,
					'meta_key'       => 'event_start_datetime',
					'meta_query'     => $meta_query,
					'tax_query'      => $tax_query
				);

				if ($apply_location_filter) {
					$args['post__in'] = !empty($matching_post_ids) ? $matching_post_ids : array(0);
				}

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
						'compare' => 'LIKE'
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
