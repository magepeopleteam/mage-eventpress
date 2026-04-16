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
			public static function event_list_query($show,$evnt_type = 'upcoming',$sort = '',$paged_override = 0) {
				$etype          = $evnt_type == 'expired' ? '<' : '>';
				$etype=$evnt_type=='today'?'LIKE':$etype;
				$event_expire_on_old = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
				$event_order_by      = mep_get_option( 'mep_event_list_order_by', 'general_setting_sec', 'meta_value' );
				if ( $event_expire_on_old === 'event_end_datetime' || $event_expire_on_old === 'event_expire_datetime' ) {
					$event_expire_on = 'event_expire_datetime';
				} else {
					$event_expire_on = 'event_upcoming_datetime';
				}
				if ( $paged_override && is_numeric( $paged_override ) ) {
					$paged = intval( $paged_override );
				} elseif ( get_query_var( 'paged' ) ) {
					$paged = get_query_var( 'paged' );
				} elseif ( get_query_var( 'page' ) ) {
					$paged = get_query_var( 'page' );
				} else {
					$paged = 1;
				}
				$now                 = current_time( 'Y-m-d H:i:s' );
				if($evnt_type=='today'){
					$now                 = current_time( 'Y-m-d' );
				}
				$expire_filter = '';
				if ( ! empty( $event_expire_on ) ) {
					if ( $event_expire_on === 'event_upcoming_datetime' ) {
						// Some selected-date recurring events never get event_upcoming_datetime populated.
						// In that case fall back to the saved start datetime so expired events still appear.
						$expire_filter = array(
							'relation' => 'OR',
							array(
								'key'     => 'event_upcoming_datetime',
								'value'   => $now,
								'compare' => $etype,
								'type'    => 'DATETIME'
							),
							array(
								'relation' => 'AND',
								array(
									'relation' => 'OR',
									array(
										'key'     => 'event_upcoming_datetime',
										'compare' => 'NOT EXISTS'
									),
									array(
										'key'     => 'event_upcoming_datetime',
										'value'   => '',
										'compare' => '='
									)
								),
								array(
									'key'     => 'event_start_datetime',
									'value'   => $now,
									'compare' => $etype,
									'type'    => 'DATETIME'
								)
							)
						);
					} else {
						$expire_filter = array(
							'key'     => $event_expire_on,
							'value'   => $now,
							'compare' => $etype,
							'type'    => 'DATETIME'
						);
					}
				}
				$meta_query = array();
				if ( ! empty( $expire_filter ) ) {
					$meta_query[] = $expire_filter;
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
					//'tax_query'      => $tax_query
				);
				return new WP_Query( $args );
			}
			public static function event_query( $show, $sort = '', $cat = '', $org = '', $city = '', $country = '', $evnt_type = 'upcoming', $state = '', $year = '', $paged_override = 0, $tag = '' ) {
				$event_expire_on_old = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime' );
				$event_order_by      = mep_get_option( 'mep_event_list_order_by', 'general_setting_sec', 'meta_value' );

				$event_expire_on = ( in_array( $event_expire_on_old, ['event_end_datetime', 'event_expire_datetime'] ) ) ? 'event_expire_datetime' : 'event_upcoming_datetime';

				$now = current_time( 'mysql' );

				if ( $paged_override && is_numeric( $paged_override ) ) {
					$paged = intval( $paged_override );
				} else {
					$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
				}

				$etype = ( $evnt_type === 'expired' ) ? '<' : '>';

				$process_tax = function( $input, $taxonomy ) {
					if ( empty( $input ) ) return '';
					$terms = array_map( 'trim', explode( ',', $input ) );
					$ids   = array_filter( $terms, 'is_numeric' );
					$slugs = array_diff( $terms, $ids );

					$tax_clauses = [];
					if ( ! empty( $ids ) ) {
						$tax_clauses[] = [ 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $ids ];
					}
					if ( ! empty( $slugs ) ) {
						$tax_clauses[] = [ 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $slugs ];
					}

					if ( empty( $tax_clauses ) ) return '';
					return ( count( $tax_clauses ) > 1 ) ? array_merge( [ 'relation' => 'OR' ], $tax_clauses ) : $tax_clauses[0];
				};

				$tax_query = array_values( array_filter( [
					$process_tax( $cat, 'mep_cat' ),
					$process_tax( $org, 'mep_org' ),
					$process_tax( $tag, 'mep_tag' )
				] ) );

				if ( count( $tax_query ) > 1 ) {
					$tax_query['relation'] = 'AND';
				}

				// Optimized Date & Location Query
				global $wpdb;
				$sql_joins = "";
				$sql_where = "AND p.post_type = 'mep_events' AND p.post_status = 'publish'";
				$prepare_args = [];

				if ( $event_expire_on === 'event_upcoming_datetime' ) {
					$sql_joins .= "
						LEFT JOIN {$wpdb->postmeta} pm_upc ON p.ID = pm_upc.post_id AND pm_upc.meta_key = 'event_upcoming_datetime'
						LEFT JOIN {$wpdb->postmeta} pm_start ON p.ID = pm_start.post_id AND pm_start.meta_key = 'event_start_datetime'
					";
					$sql_where .= " AND (
						(pm_upc.meta_value IS NOT NULL AND pm_upc.meta_value != '' AND pm_upc.meta_value {$etype} %s)
						OR
						((pm_upc.meta_value IS NULL OR pm_upc.meta_value = '') AND pm_start.meta_value {$etype} %s)
					)";
					$prepare_args[] = $now;
					$prepare_args[] = $now;
				} else {
					$sql_joins .= " INNER JOIN {$wpdb->postmeta} pm_exp ON p.ID = pm_exp.post_id AND pm_exp.meta_key = %s";
					$prepare_args[] = $event_expire_on;
					$sql_where .= " AND pm_exp.meta_value {$etype} %s";
					$prepare_args[] = $now;
				}

				if ( ! empty( $year ) && preg_match( '/^\d{4}$/', $year ) ) {
					if ( strpos($sql_joins, 'pm_start') === false ) {
						$sql_joins .= " INNER JOIN {$wpdb->postmeta} pm_start ON p.ID = pm_start.post_id AND pm_start.meta_key = 'event_start_datetime'";
					}
					$sql_where .= " AND pm_start.meta_value >= %s AND pm_start.meta_value <= %s";
					$prepare_args[] = "$year-01-01 00:00:00";
					$prepare_args[] = "$year-12-31 23:59:59";
				}
				
				$location_filters = ['city' => ['mep_city', 'org_city'], 'state' => ['mep_state', 'org_state'], 'country' => ['mep_country', 'org_country']];
				$lc = 0;
				foreach ($location_filters as $var_name => $keys) {
					$val = $$var_name;
					if (!empty($val)) {
						$lc++;
						$pm_alias = "pm_loc_" . $lc;
						$tr_alias = "tr_loc_" . $lc;
						$tm_alias = "tm_loc_" . $lc;
						$sql_joins .= "
							LEFT JOIN {$wpdb->postmeta} $pm_alias ON p.ID = $pm_alias.post_id AND $pm_alias.meta_key = %s
							LEFT JOIN {$wpdb->term_relationships} $tr_alias ON p.ID = $tr_alias.object_id
							LEFT JOIN {$wpdb->termmeta} $tm_alias ON $tr_alias.term_taxonomy_id = $tm_alias.term_id AND $tm_alias.meta_key = %s
						";
						$prepare_args[] = $keys[0];
						$prepare_args[] = $keys[1];
						
						$search = '%' . $wpdb->esc_like( $val ) . '%';
						$sql_where .= " AND ($pm_alias.meta_value LIKE %s OR $tm_alias.meta_value LIKE %s)";
						$prepare_args[] = $search;
						$prepare_args[] = $search;
					}
				}

				$query = "SELECT DISTINCT p.ID FROM {$wpdb->posts} p {$sql_joins} WHERE 1=1 {$sql_where}";
				if ( ! empty($prepare_args) ) {
					$query = $wpdb->prepare($query, $prepare_args);
				}
				
				$final_ids = $wpdb->get_col($query);

				if ( empty($final_ids) ) {
					$final_ids = [0];
				}

				$args = [
					'post_type'      => 'mep_events',
					'paged'          => $paged,
					'posts_per_page' => (int)$show,
					'post_status'    => 'publish',
					'order'          => $sort,
					'orderby'        => $event_order_by,
					'post__in'       => $final_ids,
					'tax_query'      => $tax_query,
				];
				
				if ( $event_order_by === 'meta_value' || $event_order_by === 'meta_value_num' ) {
					$args['meta_key'] = 'event_start_datetime';
				}

				return new WP_Query( $args );
			}
			public static function attendee_query( $filter_args = [], $show = - 1, $page = 1 ) {
				global $wpdb;
				$sql_joins = "";
				$sql_where = "AND p.post_type = 'mep_events_attendees' AND p.post_status = 'publish'";
				$prepare_args = [];
				$join_count = 0;

				$direct_matches = [
					'post_id'        => 'ea_event_id',
					'ea_user_id'     => 'ea_user_id',
					'ea_ticket_type' => 'ea_ticket_type',
					'ea_seat_name'   => 'ea_seat_name',
					'mep_checkin'    => 'mep_checkin',
				];

				foreach ($direct_matches as $arg_key => $meta_key) {
					if ( array_key_exists( $arg_key, $filter_args ) && $filter_args[$arg_key] ) {
						$join_count++;
						$alias = "pm_{$join_count}";
						$sql_joins .= " INNER JOIN {$wpdb->postmeta} {$alias} ON p.ID = {$alias}.post_id AND {$alias}.meta_key = %s ";
						$sql_where .= " AND {$alias}.meta_value = %s ";
						$prepare_args[] = $meta_key;
						$prepare_args[] = $filter_args[$arg_key];
					}
				}

				if ( array_key_exists( 'event_date', $filter_args ) && $filter_args['event_date'] ) {
					try {
						$date = new DateTime($filter_args['event_date']);
						$hasTime = $date->format('H:i:s') !== '00:00:00';
						$date_val = $hasTime ? $date->format('Y-m-d H:i') : $date->format('Y-m-d');
						$join_count++;
						$alias = "pm_{$join_count}";
						$sql_joins .= " INNER JOIN {$wpdb->postmeta} {$alias} ON p.ID = {$alias}.post_id AND {$alias}.meta_key = 'ea_event_date' ";
						$sql_where .= " AND {$alias}.meta_value LIKE %s ";
						$prepare_args[] = '%' . $wpdb->esc_like( $date_val ) . '%';
					} catch (Exception $e) {}
				}

				if ( array_key_exists( 'filter_key', $filter_args ) && $filter_args['filter_key'] && array_key_exists( 'filter_value', $filter_args ) && $filter_args['filter_value'] ) {
					$join_count++;
					$alias = "pm_{$join_count}";
					$sql_joins .= " INNER JOIN {$wpdb->postmeta} {$alias} ON p.ID = {$alias}.post_id AND {$alias}.meta_key = %s ";
					$sql_where .= " AND {$alias}.meta_value LIKE %s ";
					$prepare_args[] = $filter_args['filter_key'];
					$prepare_args[] = '%' . $wpdb->esc_like( $filter_args['filter_value'] ) . '%';
				}

				$booked_status   = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'seat_reserved_order_status', [ 'processing', 'completed' ] );
				if (!is_array($booked_status)) $booked_status = (array)$booked_status;
				$booked_status[] = 'partially-paid';
				$booked_status   = array_unique(array_values( $booked_status ));

				$join_count++;
				$alias = "pm_{$join_count}";
				$sql_joins .= " INNER JOIN {$wpdb->postmeta} {$alias} ON p.ID = {$alias}.post_id AND {$alias}.meta_key = 'ea_order_status' ";
				
				$in_placeholders = implode(',', array_fill(0, count($booked_status), '%s'));
				$sql_where .= " AND {$alias}.meta_value IN ($in_placeholders) ";
				foreach ($booked_status as $st) {
					$prepare_args[] = $st;
				}

				$query = "SELECT DISTINCT p.ID FROM {$wpdb->posts} p {$sql_joins} WHERE 1=1 {$sql_where}";
				if ( ! empty( $prepare_args ) ) {
					$query = $wpdb->prepare( $query, $prepare_args );
				}

				$final_ids = $wpdb->get_col( $query );
				if ( empty( $final_ids ) ) {
					$final_ids = [0];
				}

				$args = array(
					'post_type'      => 'mep_events_attendees',
					'posts_per_page' => (int)$show,
					'paged'          => (int)$page,
					'post__in'       => $final_ids
				);

				return new WP_Query( $args );
			}
		}
		new MPWEM_Query();
	}