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
class MEPEventCountdownWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-countdown-widget';
	}

	public function get_title() {
		return __( 'Event Countdown', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-countdown';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_city_list_settings',
			[
				'label' => __( 'Event Countdown Settings', 'mage-eventpress' ),
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

        $this->end_controls_section();


		$this->start_controls_section(
			'mep_event_city_style_settings',
			[
				'label' => __( 'Days Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
        );
		$this->add_control(
			'mep_event_cd_day_display',
			[
				'label' => __( 'Display?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'inline-block',
				'options' => [
					'inline-block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days' => 'display: {{VALUE}} !important;',
                ],                
			]
		);        
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_cd_day_type',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days span',
			]
        ); 
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'mep_event_cd_day_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days span',
			]
		);   
		
		


		$this->add_control(
				'mep_event_cd_day_bg_color',
				[
					'label' => __( 'Background Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days span' => 'background: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_cd_day_time_color',
				[
					'label' => __( 'Time Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days span' => 'color: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_cd_day_label_color',
				[
					'label' => __( 'Label Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days .label' => 'color: {{VALUE}};',
					],
				]
		);
		


		$this->add_responsive_control(
			'mep_event_cd_day_padding',
			[
				'label' => __( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'mep_event_cd_day_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'mep_event_cd_day_box_shadow',
				'label' => __( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#days span',
			]
		);


        $this->end_controls_section();



		$this->start_controls_section(
			'mep_event_cd_hours_style_settings',
			[
				'label' => __( 'Hours Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
        );
		$this->add_control(
			'mep_event_cd_hours_display',
			[
				'label' => __( 'Display?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'inline-block',
				'options' => [
					'inline-block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours' => 'display: {{VALUE}};',
                ],                
			]
		);        
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_cd_hours_type',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours span',
			]
        ); 
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'mep_event_cd_hours_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours span',
			]
		);        
		$this->add_control(
				'mep_event_cd_hours_bg_color',
				[
					'label' => __( 'Background Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours span' => 'background: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_cd_hours_time_color',
				[
					'label' => __( 'Time Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours span' => 'color: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_cd_hours_label_color',
				[
					'label' => __( 'Label Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours .label' => 'color: {{VALUE}};',
					],
				]
		);
		$this->add_responsive_control(
			'mep_event_cd_hours_padding',
			[
				'label' => __( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'mep_event_cd_hours_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'mep_event_cd_hours_box_shadow',
				'label' => __( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#hours span',
			]
		);		
        $this->end_controls_section();


		$this->start_controls_section(
			'mep_event_cd_minutes_style_settings',
			[
				'label' => __( 'Minutes Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
        );
		$this->add_control(
			'mep_event_cd_minutes_display',
			[
				'label' => __( 'Display?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'inline-block',
				'options' => [
					'inline-block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes' => 'display: {{VALUE}};',
                ],                
			]
		);        
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_cd_minutes_type',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes span',
			]
        ); 
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'mep_event_cd_minutes_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes .wrapper',
			]
		);        
		$this->add_control(
				'mep_event_cd_minutes_bg_color',
				[
					'label' => __( 'Background Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes span' => 'background: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_cd_minutes_time_color',
				[
					'label' => __( 'Time Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes span' => 'color: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_cd_minutes_label_color',
				[
					'label' => __( 'Label Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes .label' => 'color: {{VALUE}};',
					],
				]
		);
		$this->add_responsive_control(
			'mep_event_cd_minutes_padding',
			[
				'label' => __( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'mep_event_cd_minutes_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'mep_event_cd_minutes_box_shadow',
				'label' => __( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#minutes span',
			]
		);			
        $this->end_controls_section();

		$this->start_controls_section(
			'mep_event_cd_seconds_style_settings',
			[
				'label' => __( 'Seconds Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
        );
		$this->add_control(
			'mep_event_cd_seconds_display',
			[
				'label' => __( 'Display?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'inline-block',
				'options' => [
					'inline-block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds' => 'display: {{VALUE}};',
                ],                
			]
		);        
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_cd_seconds_type',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds span',
			]
        ); 
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'mep_event_cd_seconds_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds span',
			]
		);        
		$this->add_control(
				'mep_event_cd_seconds_bg_color',
				[
					'label' => __( 'Background Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds span' => 'background: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_cd_seconds_time_color',
				[
					'label' => __( 'Time Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds span' => 'color: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_cd_seconds_label_color',
				[
					'label' => __( 'Label Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds .label' => 'color: {{VALUE}};',
					],
				]
		);
		$this->add_responsive_control(
			'mep_event_cd_seconds_padding',
			[
				'label' => __( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'mep_event_cd_seconds_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'mep_event_cd_seconds_box_shadow',
				'label' => __( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-widget-countdown .mep-event-countdown-timer div#seconds span',
			]
		);		
        $this->end_controls_section();
	
	}

	protected function render() {
        global $post;
        $settings           = $this->get_settings_for_display();

        $user_select_event  = $settings['mep_event_list'];    
        $datetype           = $settings['mep_event_date_type'];            
        $event_id           = $user_select_event > 0 ? $user_select_event : $post->ID;		        
        $event_datetime     = get_post_meta($event_id,$datetype,true) ? get_post_meta($event_id,$datetype,true) : '';
        $year               = get_mep_datetime($event_datetime,'year-full');
        $month              = get_mep_datetime($event_datetime,'month');
        $day                = get_mep_datetime($event_datetime,'day');
        $hour               = get_mep_datetime($event_datetime,'Hour');
        $minute             = get_mep_datetime($event_datetime,'minute');
        $second             = get_mep_datetime($event_datetime,'second');
        $timezone           = get_mep_datetime($event_datetime,'timezone');

		$month_name 		= date('F',strtotime($event_datetime));
	?>	
        <div class="mep-default-title mep-elementor-widget-countdown mep-event-countdown-sec">
            <div id="countdown<?php echo esc_attr($event_id); ?>" class="mep-event-countdown-timer">
				<div id="timer" class='wrapper'>
					<div id="days"></div>
					<div id="hours"></div>
					<div id="minutes"></div>
					<div id="seconds"></div>
				</div>		
			</div>
        </div>
        <script>
		jQuery(document).ready(function($) { 

			function makeTimer() {	
				var endTime = new Date("<?php echo esc_attr($day); ?> <?php echo $month_name ?> <?php echo esc_attr($year); ?> <?php echo esc_attr($hour); ?>:<?php echo esc_attr($minute); ?>:00 <?php //echo esc_attr($timezone); ?>");			
					endTime = (Date.parse(endTime) / 1000);

					var now = new Date();
					now = (Date.parse(now) / 1000);
					var timeLeft = endTime - now;

					var days = Math.floor(timeLeft / 86400); 
					var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
					var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600 )) / 60);
					var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));

					if (hours < "10") { hours = "0" + hours; }
					if (minutes < "10") { minutes = "0" + minutes; }
					if (seconds < "10") { seconds = "0" + seconds; }

					jQuery("#days").html("<span>" + days + "<span class='label'>Days</span></span>");
					jQuery("#hours").html("<span>" + hours + "<span class='label'>Hours</span></span>");
					jQuery("#minutes").html("<span>" + minutes + "<span class='label'>Minutes</span></span>");
					jQuery("#seconds").html("<span>" + seconds + "<span class='label'>Seconds</span></span>");		

			}
			setInterval(function() { makeTimer(); }, 1000);
        });
    </script>       
	<?php
}

}