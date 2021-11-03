<?php
namespace MEPPlugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @since 1.1.0
 */

class MEPEventListWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-list-widget';
	}

	public function get_title() {
		return __( 'Event List', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-archive-posts';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Event List', 'mage-eventpress' ),
			]
		);




		$this->add_control(
			'mep_event_list_cat',
			[
				'label' => __( 'Category', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => mep_elementor_get_tax_term('mep_cat'),			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'mep_event_list_org',
			[
				'label' => __( 'Organizer', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => mep_elementor_get_tax_term('mep_org'),			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'mep_event_list_style',
			[
				'label' 		=> __( 'List Style', 'mage-eventpress' ),
				'type' 			=> Controls_Manager::SELECT,
				'default' 		=> 'grid',				
				'options' 		=> [
					'grid' 		=> __( 'Grid', 'mage-eventpress' ),
					'list' 		=> __( 'List', 'mage-eventpress' ),
					'minimal' 	=> __( 'Minimal', 'mage-eventpress' ),
					'native' 	=> __( 'Native', 'mage-eventpress' ),
					'timeline' 	=> __( 'Timeline', 'mage-eventpress' ),
					'title' 	=> __( 'Title Only', 'mage-eventpress' ),
					'spring' 	=> __( 'Spring', 'mage-eventpress' ),
					'winter' 	=> __( 'Winter', 'mage-eventpress' ),
				],			
				'separator' 	=> 'none',
			]
		);

		$this->add_control(
			'mep_event_list_column',
			[
				'label' 		=> __( 'Column', 'mage-eventpress' ),
				'type' 			=> Controls_Manager::SELECT,
				'default' 		=> '3',				
				'options' 		=> [
					'1' => __( '1', 'mage-eventpress' ),
					'2' => __( '2', 'mage-eventpress' ),
					'3' => __( '3', 'mage-eventpress' ),
					'4' => __( '4', 'mage-eventpress' )
				],			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'mep_event_list_cat_filter',
			[
				'label' => __( 'Category Filter', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( 'Yes', 'mage-eventpress' ),
					'no' => __( 'No', 'mage-eventpress' )
				],		
				'separator' => 'none',
			]
		);

		$this->add_control(
			'mep_event_list_org_filter',
			[
				'label' => __( 'Organizer Filter', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( 'Yes', 'mage-eventpress' ),
					'no' => __( 'No', 'mage-eventpress' )
				],		
				'separator' => 'none',
			]
		);

		$this->add_control(
			'mep_event_list_show',
			[
				'label' => __( 'No of Item Show', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '10', 'mage-eventpress' ),
			]
		);
		
		$this->add_control(
			'mep_event_list_pagination',
			[
				'label' => __( 'Pagination', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( 'Yes', 'mage-eventpress' ),
					'carousal' => __( 'Carousal', 'mage-eventpress' ),
					'no' => __( 'No', 'mage-eventpress' )
				],			
				'separator' => 'none',
			]
		);
		$this->add_control(
			'mep_event_carousal_id',
			[
				'label' => __( 'Carousal Unique ID', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '102448', 'mage-eventpress' ),
			]
		);		
		$this->add_control(
			'mep_event_list_carousal_nav',
			[
				'label' => __( 'Carousal Navigation', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( 'Yes', 'mage-eventpress' ),
					'no' => __( 'No', 'mage-eventpress' )
				],			
				'separator' => 'none',
			]
		);
		
		$this->add_control(
			'mep_event_list_carousal_dot',
			[
				'label' => __( 'Carousal Dot', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'mage-eventpress' ),
					'no' => __( 'No', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
			]
		);
		
		$this->add_control(
			'mep_event_list_timeline_mode',
			[
				'label' => __( 'Timeline Style', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => [
					'vertical' => __( 'Vertical', 'mage-eventpress' ),
					'horizontal' => __( 'Horizontal', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
			]
		);
		
		$this->add_control(
			'mep_event_list_sort',
			[
				'label' => __( 'Sort', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => [
					'ASC' => __( 'Assending', 'mage-eventpress' ),
					'DESC' => __( 'Dessending', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
			]
		);
		

        
		
		$this->add_control(
			'mep_event_list_status',
			[
				'label' => __( 'Status', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'upcoming',
				'options' => [
					'upcoming' => __( 'Upcoming', 'mage-eventpress' ),
					'expired' => __( 'Expired', 'mage-eventpress' )
				
				],			
				'separator' => 'none',

			]
		);
		

		$this->add_control(
			'mep_event_show_thumbnail',
			[
				'label' => __( 'Show Thumbnail', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_thumb' => 'display: {{VALUE}};',
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_spring_thumb_wrapper' => 'display: {{VALUE}};',
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_winter_thumb_wrapper' => 'display: {{VALUE}};',
                   
                ],				
			]
		);		

				

		$this->add_control(
			'mep_event_show_multidate_ribbon',
			[
				'label' => __( 'Show Ribbon', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep-multidate-ribbon.mep-tem3-title-sec' => 'display: {{VALUE}};',
                   
                ],				
			]
		);		

		$this->add_control(
			'mep_event_show_view_more_date_ribbon',
			[
				'label' => __( 'Show View More Date Button', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => [
					'inline' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_more_date_btn.mep-tem3-title-sec:not(.mep_more_date_btn.mep-tem3-title-sec.mp_event_hide_event_time)' => 'display: {{VALUE}};',
                   
                ],				
			]
		);		      
		$this->end_controls_section();
        
    


		
        // Date Style
		$this->start_controls_section(
			'mep_event_list_date_style',
			[
				'label' => __( 'Date', 'mage-eventpress' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date' => 'display: {{VALUE}};',
                   
                ],				
			]
		);	
		$this->add_control(
			'mep_event_date_width',
			[
				'label' => __( 'Width', 'simple-email-mailchimp-subscriber' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1200,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);    		

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'mep_date_border',
                'selector' => '{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date',
            ]
		);		
        $this->add_responsive_control(
            'mep_date_border_radius',
            [
                'label' => __( 'Border Radius', 'mage-eventpress' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                   
                ],
            ]
		);    
		        
		$this->add_responsive_control(
			'mep_date_padding',
			[
				'label' => __( 'Padding', 'plugin-name' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);         
		$this->add_responsive_control(
			'mep_date_margin',
			[
				'label' => __( 'Margin', 'plugin-name' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);         
		$this->add_control(
			'mep_date_bg_color',
			[
				'label' => __( 'Background Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date' => 'background: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_spring_list .mep_list_date_wrapper' => 'background: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_winter_list .mep_list_date_wrapper' => 'background: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_winter_list .mep_list_date_wrapper i' => 'color: {{VALUE}};',
				],
			]
        );
			
		$this->add_control(
			'mep_date_text_color',
			[
				'label' => __( 'Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_spring_list .mep_list_date_wrapper .mep_spring_list_date' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_winter_list .mep_list_date_wrapper .mep_winter_list_date' => 'color: {{VALUE}};',
				],
			]
        ); 

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_date_typo',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date,{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_spring_list .mep_list_date_wrapper .mep_spring_list_date,{{WRAPPER}} .mep-elementor-event-list-widget .mep-ev-start-date,{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_winter_list .mep_list_date_wrapper .mep_winter_list_date',
			]
        );

        $this->end_controls_section();  
		
        // Title Style
		$this->start_controls_section(
			'mep_event_title_style',
			[
				'label' => __( 'Title', 'mage-eventpress' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'mep_event_show_title',
			[
				'label' => __( 'Show Title', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_title' => 'display: {{VALUE}};',
                   
                ],				
			]
		);		             
		$this->add_responsive_control(
			'mep_event_title_style_padding',
			[
				'label' => __( 'Padding', 'plugin-name' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);   
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_title_style_type',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_title',
			]
        );    
		$this->add_control(
			'mep_event_title_style_color',
			[
				'label' => __( 'Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_title a' => 'color: {{VALUE}};',
				],
			]
        );            
		$this->add_control(
			'mep_event_title_style_border_color',
			[
				'label' => __( 'Border Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_grid_item .mep-list-header:before' => 'border-color: {{VALUE}};',
				],
			]
        );            
        $this->end_controls_section();  
        


        // Price Style
		$this->start_controls_section(
			'mep_event_price_style',
			[
				'label' => __( 'Price', 'mage-eventpress' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'mep_event_show_price',
			[
				'label' => __( 'Show Price', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_date' => 'display: {{VALUE}};',
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_price' => 'display: {{VALUE}};',
                   
                ],				
			]
		);			             
		$this->add_responsive_control(
			'mep_event_price_style_padding',
			[
				'label' => __( 'Padding', 'plugin-name' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_date' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);   
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_price_style_type',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_date',
				'selector' => '{{WRAPPER}} .mep-elementor-event-list-widget .mep_price',
			]
        );    
		$this->add_control(
			'mep_event_price_style_color',
			[
				'label' => __( 'Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_date' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_price' => 'color: {{VALUE}};',
				],
			]
        );            
        $this->end_controls_section();  
        



        // Event Info Style
		$this->start_controls_section(
			'mep_event_info_style',
			[
				'label' => __( 'Event Information List', 'mage-eventpress' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'mep_event_show_info',
			[
				'label' => __( 'Show Event Info', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep-list-footer' => 'display: {{VALUE}};',                   
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_spring_list .mep_list_details_col_one' => 'display: {{VALUE}};',                   
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_winter_list .mep_list_details_col_one' => 'display: {{VALUE}};',                   
                ],				
			]
		);		

		$this->add_control(
			'mep_event_info_org_name',
			[
				'label' => __( 'Show Organiztion?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_org_name' => 'display: {{VALUE}};',                   
                ],				
			]
		);		

		$this->add_control(
			'mep_event_info_location_name',
			[
				'label' => __( 'Show Location?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_location_name' => 'display: {{VALUE}};',                   
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_spring_event_location' => 'display: {{VALUE}};',                   
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_winter_event_location' => 'display: {{VALUE}};',                   
                ],				
			]
		);		

		$this->add_control(
			'mep_event_info_date',
			[
				'label' => __( 'Show Date?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => __( 'Yes', 'mage-eventpress' ),
					'none' => __( 'No', 'mage-eventpress' )				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_event_date' => 'display: {{VALUE}};',                   
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_spring_event_date' => 'display: {{VALUE}};',                   
                    '{{WRAPPER}} .mep-elementor-event-list-widget .mep_winter_event_date' => 'display: {{VALUE}};',                   
                ],				
			]
		);	
  
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_info_style_typo',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-list-widget .mep-list-footer,{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_details_col_one',
			]
		);    
		
		$this->add_control(
			'mep_event_info_style_text_color',
			[
				'label' => __( 'Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-list-footer h5, {{WRAPPER}} .mep-elementor-event-list-widget .mep-list-footer h6, {{WRAPPER}}  ul.mep-more-date-lists li,{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_details_col_one span' => 'color: {{VALUE}};',
				],
			]
		);  
		
		$this->add_control(
			'mep_event_info_style_icon_bg_color',
			[
				'label' => __( 'Icon Background Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-list-footer li .evl-ico i, {{WRAPPER}}  ul.mep-more-date-lists i,{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_details_col_one span i' => 'background-color: {{VALUE}};',
				],
			]
		);  
		
		$this->add_control(
			'mep_event_info_style_icon_color',
			[
				'label' => __( 'Icon Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-list-footer li .evl-ico i, {{WRAPPER}}  ul.mep-more-date-lists i,{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_details_col_one span i' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'mep_event_info_style_thumb_border_color',
			[
				'label' => __( 'Thumbnail Border Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_list_winter_thumb' => 'border-color: {{VALUE}};',
				],
				'conditions' => [
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_style',
		                    'operator' => '==',
		                    'value' => 'winter'
		                ]
		            ]
		        ]
			]
		);
		$this->add_control(
			'mep_event_info_style_list_bg_color',
			[
				'label' => __( 'List Background Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_spring_list' => 'background: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_event_winter_list' => 'background: {{VALUE}};',
				],
				'conditions' => [
					'relation' => 'or',
		            'terms' => [
		                [
		                    'name' => 'mep_event_list_style',
		                    'operator' => '==',
		                    'value' => 'winter'
		                ],
		                [
		                    'name' => 'mep_event_list_style',
		                    'operator' => '==',
		                    'value' => 'spring'
		                ]		                
		            ]
		        ]				
			]
		);
		$this->add_control(
			'mep_event_info_style_ribbon_bg_color',
			[
				'label' => __( 'Ribbon Background Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-multidate-ribbon' => 'background: {{VALUE}};',
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep-eventtype-ribbon' => 'background: {{VALUE}};',
				],
			]
		);						  				
		$this->end_controls_section();   
		
		

		$this->start_controls_section(
			'mep_event_btn_style',
			[
				'label' => __( 'Button Style', 'mage-eventpress' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
			             
   
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_btn_style_type',
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-list-widget .mep-tem3-title-sec, {{WRAPPER}} .mep-elementor-event-list-widget .pagination-sec a',
			]
        );    
		$this->add_control(
			'mep_event_btn_bg_color',
			[
				'label' => __( 'Background Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_more_date_btn.mep-tem3-title-sec, {{WRAPPER}} .mep-elementor-event-list-widget .pagination-sec a' => 'background-color: {{VALUE}};',
				],
			]
        );            
		$this->add_control(
			'mep_event_btn_text_color',
			[
				'label' => __( 'Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .mep-elementor-event-list-widget .mep_more_date_btn.mep-tem3-title-sec, {{WRAPPER}} .mep-elementor-event-list-widget .pagination-sec a' => 'color: {{VALUE}};',
				],
			]
        );            
        $this->end_controls_section();  







	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		// $this->add_inline_editing_attributes( 'title', 'none' );
		// $this->add_inline_editing_attributes( 'description', 'basic' );
		// $this->add_inline_editing_attributes( 'content', 'advanced' );
        // $id = $settings['wpmsems_form_id'] ? $settings['wpmsems_form_id'] : 102448;



		$cat 			= $settings['mep_event_list_cat'] > 0 ? esc_attr($settings['mep_event_list_cat']) : '';
		$org 			= $settings['mep_event_list_org'] > 0 ? esc_attr($settings['mep_event_list_org']) : '';

		$style 			= $settings['mep_event_list_style'] ? esc_attr($settings['mep_event_list_style']) : 'grid';
		$column 		= $settings['mep_event_list_column'] ? esc_attr($settings['mep_event_list_column']) : '3';
		$cat_filter 	= $settings['mep_event_list_cat_filter'] ? esc_attr($settings['mep_event_list_cat_filter']) : 'no';
		$org_filter 	= $settings['mep_event_list_org_filter'] ? esc_attr($settings['mep_event_list_org_filter']) : 'no';
		$show 			= $settings['mep_event_list_show'] ? esc_attr($settings['mep_event_list_show']) : '10';
		$pagination 	= $settings['mep_event_list_pagination'] ? esc_attr($settings['mep_event_list_pagination']) : 'no';
		$carousal_id 	= $settings['mep_event_carousal_id'] ? esc_attr($settings['mep_event_carousal_id']) : '102448';
		$carousal_nav 	= $settings['mep_event_list_carousal_nav'] ? esc_attr($settings['mep_event_list_carousal_nav']) : 'no';
		$carousal_dot 	= $settings['mep_event_list_carousal_dot'] ? esc_attr($settings['mep_event_list_carousal_dot']) : 'yes';
		$timeline_style = $settings['mep_event_list_timeline_mode'] ? esc_attr($settings['mep_event_list_timeline_mode']) : 'vertical';
		$sort 			= $settings['mep_event_list_sort'] ? esc_attr($settings['mep_event_list_sort']) : 'ASC';
		$status 		= $settings['mep_event_list_status'] ? esc_attr($settings['mep_event_list_status']) : 'upcoming';

?>
<div class="mep-elementor-event-list-widget">
		<?php echo do_shortcode("[event-list cat='$cat' org='$org' style='$style' column='$column' cat-filter='$cat_filter' org-filter='$org_filter' show='$show' pagination='$pagination' carousal-nav='$carousal_nav' carousal-dots='$carousal_dot' carousal-id='$carousal_id' timeline-mode='$timeline_style' sort='$sort' status='$status']"); ?>
</div>
<?php
}
}
