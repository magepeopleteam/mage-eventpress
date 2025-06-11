<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Shortcodes' ) ) {
		class MPWEM_Shortcodes {
			public function __construct() {
				add_shortcode( 'event-add-cart-section', array( $this, 'add_to_cart_section' ) );
			}

			public function add_to_cart_section( $atts, $content = null ) {
				$defaults = array(
					"event"               => "0",
					"cart-btn-label"      => __( 'Register For This Event', 'mage-eventpress' ),
					"ticket-label"        => __( 'Ticket Type', 'mage-eventpress' ),
					"extra-service-label" => __( 'Extra Service', 'mage-eventpress' )
				);
				$params   = shortcode_atts( $defaults, $atts );
				$event_id    = $params['event'];
				ob_start();
				if ( $event_id > 0 ) {
					$all_dates     = MPWEM_Functions::get_dates( $event_id );
					$all_times     = MPWEM_Functions::get_times( $event_id, $all_dates );
					$upcoming_date = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
					do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date,$params );
				}

				return ob_get_clean();
			}
		} 
		new MPWEM_Shortcodes();
	}