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
class MEPAddToCartSectionWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-add-to-cart-section-widget';
	}

	public function get_title() {
		return __( 'Event Add to Cart Section', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-cart-solid';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_speaker_list_settings',
			[
				'label' => __( 'Event Add to Cart Section Settings', 'mage-eventpress' ),
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
			'mep_event_savq_display',
			[
				'label' => __( 'Display Available Quantity?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .xtra-item-left' => 'display: {{VALUE}};',
                ],                
			]
		); 

		$this->add_control(
			'mep_cart_btn_label',
			[
				'label' => __( 'Cart Button Text', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Register For This Event', 'mage-eventpress' ),
			]
		);






        $this->end_controls_section();



		$this->start_controls_section(
			'mep_event_cart_ticket_type_style_settings',
			[
				'label' => __( 'Ticket Type Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'mep_event_tt_display',
			[
				'label' => __( 'Display Ticket Type Title?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_ticket_type_title' => 'display: {{VALUE}};',
                ],                
			]
		); 
		$this->add_control(
			'mep_ticket_label',
			[
				'label' => __( 'Ticket Type Title', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Ticket Type:', 'mage-eventpress' ),
			]
		);
		$this->add_control(
			'mep_event_stqc_display',
			[
				'label' => __( 'Display Ticket Quantity Column?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'table-cell',
				'options' => [
					'table-cell' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget td.ticket-qty' => 'display: {{VALUE}};',
                ],                
			]
		); 


		$this->add_control(
			'mep_event_stpc_display',
			[
				'label' => __( 'Display Ticket Price Column?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'table-cell',
				'options' => [
					'table-cell' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget td.ticket-price' => 'display: {{VALUE}};',
                ],                
			]
		);

		$this->add_control(
			'mep_event_display_tkt_qty',
			[
				'label' => __( 'Display Ticket Qty: text?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget td span.tkt-qty' => 'display: {{VALUE}};',
                ],                
			]
		);

		$this->add_control(
			'mep_event_display_tkt_price_txt',
			[
				'label' => __( 'Display Per Ticket Price: text?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget td span.tkt-pric' => 'display: {{VALUE}};',
                ],                
			]
		);


		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_cart_ttt_typo',
				'label' => __( 'Title Text Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_ticket_type_title',			
			]
        );

		$this->add_control(
			'mep_event_cart_ttt_bg_color',
			[
				'label' => __( 'Title Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_ticket_type_title' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mep_event_cart_ttt_txt_color',
			[
				'label' => __( 'Title Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_ticket_type_title' => 'color: {{VALUE}};',
				],
			]
		);


		$this->add_responsive_control(
			'mep_event_cart_ttt_padding',
			[
				'label' => __( 'Title Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_ticket_type_title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


// Extra Service Table Styling Sec
		$this->start_controls_section(
			'mep_event_cart_extra_service_style_settings',
			[
				'label' => __( 'Extra Service Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'mep_event_exs_display',
			[
				'label' => __( 'Display Extra Service Title?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_extra_service_title' => 'display: {{VALUE}};',
                ],                
			]
		); 		
		$this->add_control(
			'mep_ex_service_label',
			[
				'label' => __( 'Extra Service Title', 'mage-eventpress' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Extra Service:', 'mage-eventpress' ),
			]
		);
		$this->add_control(
			'mep_event_exs_table_head_display',
			[
				'label' => __( 'Display Extra Service Table Head?', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'table-row' => __( 'Yes', 'mage-eventpress' ),					
					'none' => __( 'No', 'mage-eventpress' )
				],			
                'separator' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget table tr.mep_extra_service_table_head' => 'display: {{VALUE}};',
                ],                
			]
		); 


		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_cart_exst_typo',
				'label' => __( 'Title Text Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_extra_service_title',			
			]
        );

		$this->add_control(
			'mep_event_cart_exst_bg_color',
			[
				'label' => __( 'Title Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_extra_service_title' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mep_event_cart_exst_txt_color',
			[
				'label' => __( 'Title Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_extra_service_title' => 'color: {{VALUE}};',
				],
			]
		);


		$this->add_responsive_control(
			'mep_event_cart_exst_padding',
			[
				'label' => __( 'Title Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h3.mep_extra_service_title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();



		$this->start_controls_section(
			'mep_event_add_to_cart_section_style_settings',
			[
				'label' => __( 'Cart Section & Button Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_title_typography',
				'label' => __( 'Event Table Title Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget h4.mep-cart-table-title',			
			]
        );

		$this->add_control(
				'mep_event_table_headline_color',
				[
					'label' => __( 'Event Table Header Text Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [					
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mep_re_datelist_label' => 'color: {{VALUE}};',
					],
				]
	    );

		$this->add_control(
				'mep_event_table_headline_bg_color',
				[
					'label' => __( 'Event Table Header Background Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mep_everyday_date_secs' => 'background-color: {{VALUE}} !important;',
					],
				]
	    );

		$this->add_control(
				'mep_event_button_text_color',
				[
					'label' => __( 'Cart Button Text Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .single_add_to_cart_button' => 'color: {{VALUE}};',
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .single_add_to_cart_button:hover' => 'color: {{VALUE}};',
					],
				]
	    );


		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_button_typography',
				'label' => __( 'Cart Button Text Typography', 'mage-eventpress' ),
				'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .single_add_to_cart_button',			
			]
        );


		$this->add_control(
				'mep_event_button_bg_color',
				[
					'label' => __('Cart Button Background Color', 'mage-eventpress'),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .single_add_to_cart_button' => 'background-color: {{VALUE}} !important;border-color:{{VALUE}} !important',
					],
				]
	    );

		$this->add_responsive_control(
			'mep_event_btn_padding',
			[
				'label' => __('Cart Button Padding', 'mage-eventpress'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .single_add_to_cart_button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'mep_event_btn_padding_border_radius',
			[
				'label' => __('Border Radius', 'mage-eventpress'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .single_add_to_cart_button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
				'mep_event_section_bg_color',
				[
					'label' => __( 'Event Section Background Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mep-events-wrapper' => 'background-color: {{VALUE}};',
					],
				]
	    );

		$this->add_control(
				'mep_event_section_text_color',
				[
					'label' => __( 'Event Section Text Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mep-events-wrapper' => 'color: {{VALUE}};',
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mep-events-wrapper table td' => 'color: {{VALUE}};',
					],
				]
	    );

		$this->add_responsive_control(
			'mep_event_section_padding',
			[
				'label' => __( 'Event Section Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mep-events-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'label' => __( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mep-events-wrapper',
			]
		);
        $this->end_controls_section();
	
	}

	protected function render() {
		global $post;
		$settings 			= $this->get_settings_for_display();
		$user_select_event 	= $settings['mep_event_list'] > 0 ? esc_attr($settings['mep_event_list']) : 0;
		$ticket_table 		= $settings['mep_ticket_label'] ? esc_attr($settings['mep_ticket_label']) : __( 'Ticket Type:', 'mage-eventpress' );
		$cart_label 		= $settings['mep_cart_btn_label'] ? esc_attr($settings['mep_cart_btn_label']) : __( 'Register For This Event', 'mage-eventpress' );
		$ex_service_table 	= $settings['mep_ex_service_label'] ? esc_attr($settings['mep_ex_service_label']) : __( 'Extra Service:', 'mage-eventpress' );
		
		$event_id           = $user_select_event > 0 ? $user_select_event : $post->ID;
		if (get_post_type($event_id) == 'mep_events') {
		?>
		<div class="mep-elementor-event-add-to-cart-section-widget">
			<?php echo do_shortcode('[event-add-cart-section ticket-label="'.$ticket_table.'" cart-btn-label="'.$cart_label.'" extra-service-label="'.$ex_service_table.'" event="'.$event_id.'"]'); ?>
		</div>
		<?php
		}
	}

}
