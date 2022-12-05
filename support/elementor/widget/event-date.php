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
class MEPEventDateWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-date-widget';
	}

	public function get_title() {
		return __( 'Event DateTime', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-date';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_city_list_settings',
			[
				'label' => __( 'Event Date Settings', 'mage-eventpress' ),
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
			'mep_ele_date_before_text',
			[
				'label' => __( 'Before Text', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '', 'mage-eventpress' ),
			]
		);
		$this->add_control(
			'mep_ele_date_after_text',
			[
				'label' => __( 'After Text', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '', 'mage-eventpress' ),
			]
		);
		$this->add_control(
			'mep_event_date_type',
			[
				'label' => __( 'Event Start/End Date', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'event_start_datetime',
				'options' => [
					'event_start_datetime'      => __( 'Start Datetime', 'mage-eventpress' ),
					'event_expire_datetime'     => __( 'End Datetime', 'mage-eventpress' ),			
					'event_upcoming_datetime'   => __( 'Upcoming Datetime', 'mage-eventpress' ),			
				],				
			]
		);

		$this->add_control(
			'mep_event_date_display_style',
			[
				'label' => __( 'DateTime Style', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'date-time-text',
				'options' => [
					'date-time-text'        => __( 'DateTime', 'mage-eventpress' ),
					'date-text'             => __( 'Date', 'mage-eventpress' ),
					'day'                   => __( 'Day', 'mage-eventpress' ),
					'Dday'                  => __( 'Day Name', 'mage-eventpress' ),
					'month'                 => __( 'Month', 'mage-eventpress' ),
					'month-name'            => __( 'Month Name', 'mage-eventpress' ),
					'year'                  => __( 'Year', 'mage-eventpress' ),
					'year-full'             => __( 'Year Full', 'mage-eventpress' ),
					'time'                  => __( 'Time', 'mage-eventpress' ),				
				],				
			]
		);
		$this->add_control(
			'mep_event_date_display_icon',
			[
				'label' => __( 'Icon Before', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-calendar-alt',
					'library' => 'solid',
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
				'selector' => '{{WRAPPER}} .mep-elementor-widget-datetime span',
			]
        ); 
		$this->add_control(
				'mep_event_date_color',
				[
					'label' => __( 'Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-datetime span' => 'color: {{VALUE}};',
					],
				]
		);
		
		$this->add_control(
			'mep_event_date_icon_size',
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
					'{{WRAPPER}} .mep-elementor-widget-datetime span i' => 'font-size:{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'mep_event_date_icon_color',
			[
				'label' => __( 'Icon Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-datetime span i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'mep_event_date_icon_margin',
			[
				'label' => __( 'Icon Margin', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-datetime span i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);






        $this->end_controls_section();
	
	}

	protected function render() {
        global $post;
        $settings           = $this->get_settings_for_display();
        $user_select_event  = $settings['mep_event_list'];    
        $datetdisplaystyle  = $settings['mep_event_date_display_style'];    
        $datetype           = $settings['mep_event_date_type'];            
        $event_id           = $user_select_event > 0 ? $user_select_event : $post->ID;
		$event_datetime     = get_post_meta($event_id,$datetype,true) ? get_post_meta($event_id,$datetype,true) : '';
        $before_text  		= $settings['mep_ele_date_before_text'];    
		$after_text  		= $settings['mep_ele_date_after_text'];  	
		$mep_location_icon  = sizeof($settings['mep_event_date_display_icon']) > 0 ? "<i class='".$settings['mep_event_date_display_icon']['value']."'></i>" : ''; 		        				
		if (get_post_type($event_id) == 'mep_events') {
        if(!empty($event_datetime)){
		?>	
			<div class="mep-default-datetime mep-elementor-widget-datetime">
				<span><?php echo mep_esc_html($mep_location_icon.' '.$before_text); ?></span>
                <span><?php echo esc_html(get_mep_datetime($event_datetime,$datetdisplaystyle)); ?></span> <span><?php echo mep_esc_html($after_text); ?></span>
			</div>
		<?php
        }
	  }
	}

}