<?php

namespace MEPPlugin;

class MEPPluginElementor {
	
	private static $_instance = null;
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	public function widget_scripts() {
		//wp_register_script( 'tour-booking-helper-script', plugins_url( '/assets/js/hello-world.js', __FILE__ ), [ 'jquery' ], false, true );
	}
	
	public function add_widget_categories( $elements_manager ) {
		
		$elements_manager->add_category(
			'mep-elementor-support',
			[
				'title' => __( 'Event Manager For Woocommerce ', 'mage-eventpress'),
				'icon'  => 'fa fa-plug',
			]
		);
		
	}
	
	private function include_widgets_files() {
		require_once( __DIR__ . '/widget/event-calendar.php' );		
		require_once( __DIR__ . '/widget/event-list.php' );		
		// require_once( __DIR__ . '/widget/expired-event-list.php' );		
		require_once( __DIR__ . '/widget/event-speaker-list.php' );		
		require_once( __DIR__ . '/widget/event-add-cart-section.php' );		
		require_once( __DIR__ . '/widget/event-list-recurring.php' );		
		require_once( __DIR__ . '/widget/event-city-list.php' );		
		require_once( __DIR__ . '/widget/event-title.php' );		
		// require_once( __DIR__ . '/widget/event-thumbnail.php' );		
		// require_once( __DIR__ . '/widget/event-details.php' );		
		require_once( __DIR__ . '/widget/event-faq.php' );		
		require_once( __DIR__ . '/widget/event-date.php' );		
		require_once( __DIR__ . '/widget/event-location.php' );		
		require_once( __DIR__ . '/widget/event-map.php' );		
		require_once( __DIR__ . '/widget/event-total-seat.php' );		
		require_once( __DIR__ . '/widget/event-org.php' );		
		require_once( __DIR__ . '/widget/event-schedule.php' );		
		require_once( __DIR__ . '/widget/event-share-btn.php' );		
		require_once( __DIR__ . '/widget/event-add-calender.php' );		
		require_once( __DIR__ . '/widget/event-countdown.php' );		
	}
	
	public function register_widgets() {
		
		// Its is now safe to include Widgets files
		$this->include_widgets_files();
		
		// Register Widgets		
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPCalendarWidget() );		
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventListWidget() );		
		// \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPExpiredEventWidget() );		
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPSpeakerListWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPAddToCartSectionWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventListRecurringWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventCityListWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventTitletWidget() );
		// \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventThumbnailtWidget() );
		// \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventDetailstWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventFaqtWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventDateWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventLocationWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventMaptWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventSeattWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventOrgWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventScheduleWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventShareBTNtWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventAddCalendarWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\MEPEventCountdownWidget() );
	}
	
	public function __construct() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( \is_plugin_active( 'elementor/elementor.php' ) ) {
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'widget_scripts' ] );
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_widget_categories' ] );
        }
	}
}


// Instantiate Plugin Class
MEPPluginElementor::instance();