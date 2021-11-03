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
class MEPExpiredEventWidget extends Widget_Base {

	public function get_name() {
		return 'mep-expired-event-list-widget';
	}

	public function get_title() {
		return __( 'Expired Event List', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'fas fa-history';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_list_settings',
			[
				'label' => __( 'Event List Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'mep_event_list_cat',
			[
				'label' => __( 'Event Category', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => mep_elementor_get_tax_term('mep_cat'),			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'divider1',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_list_org',
			[
				'label' => __( 'Event Organizer', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => mep_elementor_get_tax_term('mep_org'),			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'divider2',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_list_show',
			[
				'label' => __( 'No. of Events Show','mage-eventpress' ),
				'type' => Controls_Manager::NUMBER,
				'default' => __( '3', 'mage-eventpress' ),
			]
		);

		$this->add_control(
			'divider3',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_list_style',
			[
				'label' 		=> __( 'Event List Style', 'mage-eventpress' ),
				'type' 			=> Controls_Manager::SELECT,
				'default' 		=> 'grid',				
				'options' 		=> [
					'grid' 		=> __( 'Grid', 'mage-eventpress' ),
					'list' 		=> __( 'List', 'mage-eventpress' ),
					'minimal' 	=> __( 'Minimal', 'mage-eventpress' ),
					'native' 	=> __( 'Native', 'mage-eventpress' ),
					'timeline' 	=> __( 'Timeline', 'mage-eventpress' ),
					'title' 	=> __( 'Title Only', 'mage-eventpress' ),
				],
			]
		);

		$this->add_control(
			'divider4',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_list_timeline_mode',
			[
				'label' => __( 'Timeline Events Style', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => [
					'vertical' => __( 'Vertical', 'mage-eventpress' ),
					'horizontal' => __( 'Horizontal', 'mage-eventpress' )
				
				],
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_style',
		                    'operator' => '==',
		                    'value' => 'timeline'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'divider4_1',
			[
				'type' => Controls_Manager::DIVIDER,
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_style',
		                    'operator' => '==',
		                    'value' => 'timeline'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'mep_event_list_column',
			[
				'label' 		=> __( 'Event Grid Column', 'mage-eventpress' ),
				'type' 			=> Controls_Manager::SELECT,
				'default' 		=> '3',				
				'options' 		=> [
					'1' => __( '1', 'mage-eventpress' ),
					'2' => __( '2', 'mage-eventpress' ),
					'3' => __( '3', 'mage-eventpress' ),
					'4' => __( '4', 'mage-eventpress' )
				],
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_style',
		                    'operator' => '==',
		                    'value' => 'grid'
		                ]
		            ]
		        ]
		
			]
		);

		$this->add_control(
			'divider5',
			[
				'type' => Controls_Manager::DIVIDER,
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_style',
		                    'operator' => '==',
		                    'value' => 'grid'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'mep_event_list_cat_filter',
			[
				'label' => __( 'Filter Events by Category', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __('Yes', 'mage-eventpress' ),
					'no' => __( 'No', 'mage-eventpress' )
				],
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_org_filter',
		                    'operator' => '==',
		                    'value' => 'no'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'divider6',
			[
				'type' => Controls_Manager::DIVIDER,
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_org_filter',
		                    'operator' => '==',
		                    'value' => 'no'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'mep_event_list_org_filter',
			[
				'label' => __( 'Filter Events by Organizer', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __('Yes', 'mage-eventpress' ),
					'no' => __('No', 'mage-eventpress' )
				],
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_cat_filter',
		                    'operator' => '==',
		                    'value' => 'no'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'divider7',
			[
				'type' => Controls_Manager::DIVIDER,
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_cat_filter',
		                    'operator' => '==',
		                    'value' => 'no'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'mep_event_list_sort',
			[
				'label' => __( 'Sort Events', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC' => __( 'Ascending', 'mage-eventpress' ),
					'DESC' => __( 'Descending', 'mage-eventpress' )
				
				],
			]
		);

		$this->add_control(
			'divider8',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_list_pagination',
			[
				'label' => __( 'Pagination', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( 'Number Mode', 'mage-eventpress' ),
					'carousal' => __( 'Carousel Mode', 'mage-eventpress' ),
					'no' => __( 'None', 'mage-eventpress' )
				],
			]
		);

		$this->add_control(
			'divider9',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_carousel_id',
			[
				'label' => __( 'Carousel Unique ID', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '102448', 'mage-eventpress' ),
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_pagination',
		                    'operator' => '==',
		                    'value' => 'carousal'
		                ]
		            ]
		        ]
			]
		);

		$this->add_control(
			'divider9_1',
			[
				'type' => Controls_Manager::DIVIDER,
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_pagination',
		                    'operator' => '==',
		                    'value' => 'carousal'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'mep_event_list_carousel_nav',
			[
				'label' => __( 'On/Off Carousel Navigation', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( 'On', 'mage-eventpress' ),
					'no' => __( 'Off', 'mage-eventpress' )
				],
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_pagination',
		                    'operator' => '==',
		                    'value' => 'carousal'
		                ]
		            ]
		        ]				
			]
		);

		$this->add_control(
			'divider9_2',
			[
				'type' => Controls_Manager::DIVIDER,
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_pagination',
		                    'operator' => '==',
		                    'value' => 'carousal'
		                ]
		            ]
		        ]

			]
		);

		$this->add_control(
			'mep_event_list_carousel_dot',
			[
				'label' => __( 'On/Off Carousel Dot', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __('On', 'mage-eventpress' ),
					'no' => __('Off', 'mage-eventpress' )				
				],
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_pagination',
		                    'operator' => '==',
		                    'value' => 'carousal'
		                ]
		            ]
		        ]				
			]
		);

		$this->add_control(
			'divider10',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_show_date',
			[
				'label' => __( 'Show Date', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-expired-event-widget .mep-ev-start-date' => 'display: {{VALUE}};',
                   
                ],				
			]
		);	


		$this->end_controls_section();	           

        /*****************
		* Event List Style
        ******************/

		$this->start_controls_section(
			'mep_event_style_settings',
			[
				'label' => __( 'Event Style Settings', 'mage-eventpress' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]		
		);



		$this->add_control(
			'mep_day_bg_color',
			[
				'label' => __( 'Event Day Background Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-ev-start-date .mep-day' => 'background-color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider11',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_month_bg_color',
			[
				'label' => __( 'Event Month Background Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-ev-start-date mep-month' => 'background-color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider12',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_date_text_color',
			[
				'label' => __( 'Event Date Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-ev-start-date' => 'color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider13',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_date_text_typography',
				'label' => __( 'Event Date Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-expired-event-widget .mep-ev-start-date',
			]
        );

		$this->add_control(
			'divider14',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_title_text_color',
			[
				'label' => __( 'Event Title Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_list_title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_event_title_list_item a' => 'color: {{VALUE}} !important;',
				],
			]
        );

		$this->add_control(
			'divider14_1',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_title_typography',
				'label' => __( 'Event Title Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-expired-event-widget .mep_list_title',
				'selector' => '{{WRAPPER}} .mep-elementor-expired-event-widget .mep_event_title_list_item a',
			]
        );

		$this->add_control(
			'divider15',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_desc_color',
			[
				'label' => __( 'Event Description Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-event-excerpt' => 'color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider15_1',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_desc_typography',
				'label' => __( 'Event Description Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-expired-event-widget .mep-event-excerpt',
			]
        );

		$this->add_control(
			'divider15_2',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_price_text_color',
			[
				'label' => __( 'Event Price Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-list-header .mep_list_date' => 'color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider15_3',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_price_typography',
				'label' => __( 'Event Price Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-expired-event-widget .mep_list_date',
			]
        );

		$this->add_control(
			'divider16',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_border_color',
			[
				'label' => __( 'Event Header Border Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-list-header:before' => 'border-color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider17',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_icon_color',
			[
				'label' => __( 'Event Icon Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-list-footer ul li i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_event_minimal_list h3.mep_list_date i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_event_native_list h3.mep_list_date i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_event_timeline_list h3.mep_list_date i' => 'color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider18',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_footer_title_color',
			[
				'label' => __( 'Event Footer Title Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-list-footer ul li h5' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_minimal_list_location' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_minimal_list_date' => 'color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider19',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_footer_text_color',
			[
				'label' => __( 'Event Footer Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-list-footer ul li h6' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_minimal_list_location' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_minimal_list_date' => 'color: {{VALUE}};',
				],
			]
        );

		$this->add_control(
			'divider20',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_footer_text_typography',
				'label' => __( 'Event Footer Text Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-expired-event-widget .mep-list-footer ul li h5, {{WRAPPER}} .mep-elementor-expired-event-widget .mep-list-footer ul li h6, {{WRAPPER}} .mep-elementor-expired-event-widget .mep_minimal_list_location,  {{WRAPPER}} .mep-elementor-expired-event-widget .mep_minimal_list_date',			
			]
        );

		$this->add_control(
			'divider20_1',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_button_color',
			[
				'label' => __( 'Event Button Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_more_date_btn' => 'color: {{VALUE}};border-color: {{VALUE}}',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_more_date_btn:before' => 'background: {{VALUE}};border-color: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider20_2',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_button_typography',
				'label' => __( 'Event Button Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-expired-event-widget .mep_more_date_btn',			
			]
        );

		$this->add_control(
			'divider21',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_carousel_nav_bg_color',
			[
				'label' => __( 'Event Carousel Nav Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_event_list .owl-nav > button:hover' => 'background: {{VALUE}} !important',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_event_list .owl-dots button.active' => 'background: {{VALUE}} !important',
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_event_list .owl-dots button.active::before' => 'border-bottom-color: {{VALUE}} !important',
				],
			]
        );

		$this->add_control(
			'divider21_1',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_badge_bg_color',
			[
				'label' => __( 'Event Badge Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-multidate-ribbon' => 'background: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider22',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_details_bg_color',
			[
				'label' => __( 'Event Details Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_list_event_details' => 'background: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider23',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_responsive_control(
			'mep_event_details_padding',
			[
				'label' => __( 'Event Details Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep_list_event_details' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'divider24',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_item_bg_color',
			[
				'label' => __( 'Event Box Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .mep-event-list-loop' => 'background: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider25',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);


		$this->add_control(
			'mep_event_filter_button_bg_color',
			[
				'label' => __( 'Event Filter Button Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget button.mep-cat-control' => 'background: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider26',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_filter_active_button_bg_color',
			[
				'label' => __( 'Event Filter Active Button Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget button.mep-cat-control.mixitup-control-active' => 'background: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider27',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

 		$this->add_control(
			'mep_event_filter_active_button_text_color',
			[
				'label' => __( 'Event Filter Active Button Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget button.mep-cat-control.mixitup-control-active' => 'color: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider28',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_pagination_button_bg_color',
			[
				'label' => __( 'Event Pagination Button Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .page-numbers' => 'background: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider29',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'mep_event_pagination_active_button_bg_color',
			[
				'label' => __( 'Event Pagination Active Button Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .page-numbers.current' => 'background: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'divider30',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

 		$this->add_control(
			'mep_event_pagination_active_button_text_color',
			[
				'label' => __( 'Event Pagination Active Button Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-expired-event-widget .page-numbers.current' => 'color: {{VALUE}}',
				],
			]
        );

        $this->end_controls_section();

	
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		$cat = $settings['mep_event_list_cat'] > 0 ? esc_attr($settings['mep_event_list_cat']) : '';
		$org = $settings['mep_event_list_org'] > 0 ? esc_attr($settings['mep_event_list_org']) : '';
		$show = $settings['mep_event_list_show'] ? esc_attr($settings['mep_event_list_show']) : '3';
		$style = $settings['mep_event_list_style'] ? esc_attr($settings['mep_event_list_style']) : 'grid';
		$timeline_style = $settings['mep_event_list_timeline_mode'] ? esc_attr($settings['mep_event_list_timeline_mode']) : 'vertical';
		$column = $settings['mep_event_list_column'] ? esc_attr($settings['mep_event_list_column']) : '3';
		$cat_filter = $settings['mep_event_list_cat_filter'] ? esc_attr($settings['mep_event_list_cat_filter']) : 'no';
		$org_filter = $settings['mep_event_list_org_filter'] ? esc_attr($settings['mep_event_list_org_filter']) : 'no';
		$sort = $settings['mep_event_list_sort'] ? esc_attr($settings['mep_event_list_sort']) : 'DESC';
		$pagination = $settings['mep_event_list_pagination'] ? esc_attr($settings['mep_event_list_pagination']) : 'no';
		$carousel_id = $settings['mep_event_carousel_id'] ? esc_attr($settings['mep_event_carousel_id']) : '102448';
		$carousel_nav = $settings['mep_event_list_carousel_nav'] ? esc_attr($settings['mep_event_list_carousel_nav']) : 'no';
		$carousel_dot = $settings['mep_event_list_carousel_dot'] ? esc_attr($settings['mep_event_list_carousel_dot']) : 'yes';

	?>
	<div class="mep-elementor-expired-event-widget">
		<?php echo do_shortcode('[expire-event-list cat='.$cat.' org='.$org.' show='.$show.' style='.$style.' timeline-mode='.$timeline_style.' column='.$column.' cat-filter='.$cat_filter.' org-filter='.$org_filter.' sort='.$sort.' pagination='.$pagination.' carousal-id='.$carousel_id.' carousal-nav='.$carousel_nav.' carousal-dots='.$carousel_dot.']'); ?>
	</div>
	<?php
}

}
