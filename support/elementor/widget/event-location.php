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
class MEPEventLocationWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-location-widget';
	}

	public function get_title() {
		return __( 'Event Location', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_city_list_settings',
			[
				'label' => __( 'Event Location Settings', 'mage-eventpress' ),
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
		$this->add_control(
			'mep_location_icon',
			[
				'label' => __( 'Icon Before', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'solid',
				],
			]
		);		
		$this->add_control(
			'mep_ele_location_before_text',
			[
				'label' => __( 'Before Text', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '', 'mage-eventpress' ),
			]
		);
		$this->add_control(
			'mep_ele_location_after_text',
			[
				'label' => __( 'After Text', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '', 'mage-eventpress' ),
			]
		);
		$this->add_control(
			'mep_event_location_style',
			[
				'label' => __( 'Location Style', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'full',
				'options' => [
					'full'              => __( 'Full Location Address', 'mage-eventpress' ),
					'location'          => __( 'location Name', 'mage-eventpress' ),
					'street'            => __( 'Street', 'mage-eventpress' ),
					'state'             => __( 'State', 'mage-eventpress' ),
					'city'              => __( 'City', 'mage-eventpress' ),
					'zip'               => __( 'Postcode', 'mage-eventpress' ),
					'country'           => __( 'Country', 'mage-eventpress' )			
				],				
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
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_date_typo',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-widget-location span',
			]
        ); 
		$this->add_control(
				'mep_event_date_color',
				[
					'label' => __( 'Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-location span' => 'color: {{VALUE}};',
					],
				]
		);
		
		$this->add_control(
			'mep_event_location_icon_size',
			[
				'label' => __( 'Icon Size', 'plugin-domain' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-location span i' => 'font-size:{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'mep_event_location_icon_color',
			[
				'label' => __( 'Icon Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-location span i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'mep_event_location_icon_margin',
			[
				'label' => __( 'Icon Margin', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-location span i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);



        $this->end_controls_section();
	
	}

	protected function render() {
        global $post;
        $settings           = $this->get_settings_for_display();
        $user_select_event  = $settings['mep_event_list'];    
		$mep_event_location_style  = $settings['mep_event_location_style'];       
        $before_text  		= $settings['mep_ele_location_before_text'];    
        $after_text  		= $settings['mep_ele_location_after_text']; 		        
        $mep_location_icon  = sizeof($settings['mep_location_icon']) > 0 ? "<i class='".$settings['mep_location_icon']['value']."'></i>" : ''; 		        
		$event_id           = $user_select_event > 0 ? $user_select_event : $post->ID;   

// print_r();

		if (get_post_type($event_id) == 'mep_events') {    
        if(!empty($mep_event_location_style)){
	?>	
        <div class="mep-default-location mep-elementor-widget-location">
		<span><?php echo mep_esc_html($mep_location_icon.' '.$before_text); ?></span>
            <span><?php mep_get_location($event_id,$mep_event_location_style); ?></span>
            <span><?php echo mep_esc_html($after_text); ?></span>
        </div>
	<?php
        }
}
	}
}
