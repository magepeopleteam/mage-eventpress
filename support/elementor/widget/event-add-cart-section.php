<?php
namespace MEPPlugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
// use Elementor\Scheme_Typography as Typography;

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
                    '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ticket_type .card-header' => 'display: {{VALUE}};',
                ],                
			]
		); 



		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_cart_ttt_typo',
				'label' => __( 'Title Text Typography', 'mage-eventpress' ),
				// 'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ticket_type .card-header, {{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ex_service .card-header',			
			]
        );

		$this->add_control(
			'mep_event_cart_ttt_bg_color',
			[
				'label' => __( 'Title Background', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ticket_type .card-header, {{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ex_service .card-header' => 'background: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'mep_event_cart_ttt_txt_color',
			[
				'label' => __( 'Title Text Color', 'mage-eventpress' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ticket_type .card-header, {{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ex_service .card-header' => 'color: {{VALUE}};',
				],
			]
		);


		$this->add_responsive_control(
			'mep_event_cart_ttt_padding',
			[
				'label' => __( 'Title Padding', 'mage-eventpress' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ticket_type .card-header, {{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ex_service .card-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'mep_cart_section_sec_border_radius',
			[
				'label' => __('Border Radius', 'mage-eventpress'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ticket_type, {{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_ex_service' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],


			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'mep_event_add_to_cart_section_style_settings',
			[
				'label' => __( 'Cart Button Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

	
		$this->add_control(
				'mep_event_button_bg_color',
				[
					'label' => __('Cart Button Background Color', 'mage-eventpress'),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_form_submit_area button[type=submit]' => 'background-color: {{VALUE}} !important;border-color:{{VALUE}} !important',
					],
				]
	    );

		$this->add_control(
				'mep_event_button_text_color',
				[
					'label' => __( 'Cart Button Text Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_form_submit_area button[type=submit]' => 'color: {{VALUE}};',
						'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_form_submit_area button[type=submit]' => 'color: {{VALUE}};',
					],
				]
	    );


		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mep_event_button_typography',
				'label' => __( 'Cart Button Text Typography', 'mage-eventpress' ),
				// 'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_form_submit_area button[type=submit]',			
			]
        );

		$this->add_responsive_control(
			'mep_event_btn_padding',
			[
				'label' => __('Cart Button Padding', 'mage-eventpress'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_form_submit_area button[type=submit]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
					'{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_registration_area .mpwem_booking_panel .mpwem_form_submit_area button[type=submit]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'label' => __( 'Box Shadow', 'mage-eventpress' ),
				'selector' => '{{WRAPPER}} .mep-elementor-event-add-to-cart-section-widget .mpwem_booking_panel',
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
