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
class MEPEventScheduleWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-schedule-widget';
	}

	public function get_title() {
		return __( 'Event Schedule', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-price-list';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_city_list_settings',
			[
				'label' => __( 'Event Schedule Settings', 'mage-eventpress' ),
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
			'mep_event_sch_sec_height',
			[
				'label' => __( 'Section Height', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px'],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 900,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 270,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-schedule ul#mep_event_date_sch' => 'height: {{SIZE}}{{UNIT}};',
                ],                
			]
		);        
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_title_typo',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-widget-schedule',
			]
        ); 
		$this->add_control(
				'mep_event_date_icon_color',
				[
					'label' => __( 'Date Icon Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-schedule ul li span.mep-more-date i' => 'color: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_date_text_color',
				[
					'label' => __( 'Date Text Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-schedule ul li span.mep-more-date' => 'color: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_time_icon_color',
				[
					'label' => __( 'Time Icon Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-schedule ul li span.mep-more-time i' => 'color: {{VALUE}};',
					],
				]
	    );
		$this->add_control(
				'mep_event_time_text_color',
				[
					'label' => __( 'Time Text Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-schedule ul li span.mep-more-time' => 'color: {{VALUE}};',
					],
				]
	    );

		$this->add_control(
			'mep_event_sch_item_space',
			[
				'label' => __( 'Item Space', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px'],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 200,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-schedule ul li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],                
			]
		);
		$this->add_control(
			'mep_event_sch_view_more-btn_style',
			[
				'label' => __( 'View More Button Display?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block'        => __( 'Yes', 'mage-eventpress' ),
					'none'         => __( 'No', 'mage-eventpress' ),			
                ],	
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-schedule #mep_single_view_all_date' => 'display: {{VALUE}};',
                ],                			
			]
        );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_btn_typo',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-widget-schedule #mep_single_view_all_date, {{WRAPPER}} .mep-elementor-widget-schedule #mep_single_hide_all_date',
			]
        );  
        
		$this->add_control(
            'mep_btn_bg_color',
            [
                'label' => __( 'View Button Background Color', 'mage-eventpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-schedule #mep_single_view_all_date, {{WRAPPER}} .mep-elementor-widget-schedule #mep_single_hide_all_date' => 'background-color: {{VALUE}};',
                ],
            ]
    );        
        
		$this->add_control(
            'mep_btn_text_color',
            [
                'label' => __( 'View Button Text Color', 'mage-eventpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-widget-schedule #mep_single_view_all_date, {{WRAPPER}} .mep-elementor-widget-schedule #mep_single_hide_all_date' => 'color: {{VALUE}};',
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
        <div class="mep-default-schedule mep-elementor-widget-schedule">
          <?php echo do_action('mep_event_date_default_theme',$event_id,'no');  ?>
        </div>
	<?php
        }
}

}
