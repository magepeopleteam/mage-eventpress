<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Query' ) ) {
		class MPWEM_Query {
			public function __construct() { }
			public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => 'publish'
				);
				return new WP_Query($args);
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
				$meta_query    = array(
					'relation' => 'AND',
					$expire_filter,
					$city_filter,
					$state_filter,
					$country_filter,
					$year_filter
				);
				$tax_query     = array(
					'relation' => 'AND',
					$cat_filter,
					$org_filter,
					$tag_filter
				);
				$args          = array(
					'post_type'      => array( 'mep_events' ),
					'paged'          => $paged,
					'posts_per_page' => $show,
					'order'          => $sort,
					'orderby'        => $event_order_by,
					'meta_key'       => 'event_upcoming_datetime',
					'meta_query'     => array_filter( $meta_query ),
					'tax_query'      => array_filter( $tax_query )
				);

				return new WP_Query( $args );
			}

		}
		new MPWEM_Query();
	}