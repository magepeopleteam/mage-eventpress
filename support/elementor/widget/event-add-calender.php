<?php
namespace MEPPlugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @since 1.1.0
 */
class MEPEventAddCalendarWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-calender-widget';
	}

	public function get_title() {
		return __( 'Event Add Calender', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_city_list_settings',
			[
				'label' => __( 'Event Add Calender Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'mep_event_list',
			[
				'label' => __( 'Select Event', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => mep_elementor_get_events('None'),
			]
		);


        $this->end_controls_section();


		$this->start_controls_section(
			'mep_event_city_style_settings',
			[
				'label' => __( 'Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
				'mep_event_share-btn_bg_color',
				[
					'label' => __( 'Background Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-calender-btn ul#mep_add_calender_links li a' => 'background-color: {{VALUE}};',
						'{{WRAPPER}} .mep-elementor-widget-calender-btn #mep_add_calender_button' => 'background-color: {{VALUE}};',
					],
				]
	    );        
		$this->add_control(
				'mep_event_share-btn_icon_color',
				[
					'label' => __( 'Icon Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-calender-btn ul#mep_add_calender_links li a' => 'color: {{VALUE}};',
						'{{WRAPPER}} .mep-elementor-widget-calender-btn #mep_add_calender_button' => 'color: {{VALUE}} !important;',
					],
				]
	    );
        $this->end_controls_section();	
	}

	protected function render() {
        global $post;
        $settings           = $this->get_settings_for_display();
        $user_select_event  = $settings['mep_event_list'];    
        $event_id           = $user_select_event > 0 ? $user_select_event : $post->ID;
        if (get_post_type($event_id) == 'mep_events') {
	?>	
        <div class="mep-default-calender mep-elementor-widget-calender-btn">
        <div class="calender-url">
			<?php
			do_action('mep_before_add_calendar_button');
				echo mep_add_to_google_calender_link($event_id);
			do_action('mep_after_add_calendar_button');
			?>
		</div>
        </div>
	<?php
    }
}
}