<?php
	namespace MEPPlugin\Widgets;
	use Elementor\Widget_Base;
	use Elementor\Controls_Manager;
	use Elementor\Group_Control_Typography;
	use MPWEM_Functions;
// use Elementor\Core\Schemes\Typography;
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	} // Exit if accessed directly
	/**
	 * @since 1.1.0
	 */
	class MEPEventShareBTNtWidget extends Widget_Base {
		public function get_name() {
			return 'mep-event-share-btn-widget';
		}
		public function get_title() {
			return __( 'Event Share Buttons', 'mage-eventpress' );
		}
		public function get_icon() {
			return 'eicon-social-icons';
		}
		public function get_categories() {
			return [ 'mep-elementor-support' ];
		}
		protected function _register_controls() {
			$this->start_controls_section(
				'mep_event_city_list_settings',
				[
					'label' => __( 'Event Share Button Settings', 'mage-eventpress' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				]
			);
			$this->add_control(
				'mep_event_list',
				[
					'label'   => __( 'Select Event', 'mage-eventpress' ),
					'type'    => Controls_Manager::SELECT,
					'default' => '0',
					'options' => mep_elementor_get_events( 'None' ),
				]
			);
			$this->end_controls_section();
			$this->start_controls_section(
				'mep_event_city_style_settings',
				[
					'label' => __( 'Style Settings', 'mage-eventpress' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				]
			);
			$this->add_control(
				'mep_event_share-btn_bg_color',
				[
					'label'     => __( 'Background Color', 'mage-eventpress' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a' => 'background-color: {{VALUE}};',
					],
				]
			);
			$this->add_control(
				'mep_event_share-btn_icon_color',
				[
					'label'     => __( 'Icon Color', 'mage-eventpress' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a i' => 'color: {{VALUE}};',
					],
				]
			);
			$this->add_responsive_control(
				'mep_event_share_btn_icon_border_radius',
				[
					'label'      => __( 'Border Radius', 'mage-eventpress' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%', 'rem' ],
					'selectors'  => [
						'{{WRAPPER}} .mep-default-share-btn.mep-elementor-widget-share-btn ul li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'mep_event_share_btn_icon_space',
				[
					'label'      => __( 'Icon Space', 'mage-eventpress' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range'      => [
						'px' => [
							'min'  => 0,
							'max'  => 1000,
							'step' => 1,
						],
						'%'  => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default'    => [
						'unit' => 'px',
						'size' => 5,
					],
					'selectors'  => [
						'{{WRAPPER}} .mep-default-share-btn.mep-elementor-widget-share-btn ul li' => 'margin: 0 {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'mep_event_share_btn_icon_size',
				[
					'label'      => __( 'Icon Size', 'mage-eventpress' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range'      => [
						'px' => [
							'min'  => 0,
							'max'  => 1000,
							'step' => 1,
						],
						'%'  => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default'    => [
						'unit' => 'px',
						'size' => 25,
					],
					'selectors'  => [
						'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'mep_event_share_btn_width',
				[
					'label'      => __( 'Width', 'mage-eventpress' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range'      => [
						'px' => [
							'min'  => 0,
							'max'  => 1000,
							'step' => 1,
						],
						'%'  => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default'    => [
						'unit' => 'px',
						'size' => 50,
					],
					'selectors'  => [
						'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'mep_event_share_btn_height',
				[
					'label'      => __( 'Height', 'mage-eventpress' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range'      => [
						'px' => [
							'min'  => 0,
							'max'  => 1000,
							'step' => 1,
						],
						'%'  => [
							'min' => 0,
							'max' => 100,
						],
					],
					'default'    => [
						'unit' => 'px',
						'size' => 50,
					],
					'selectors'  => [
						'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->end_controls_section();
		}
		protected function render() {
			global $post;
			$settings          = $this->get_settings_for_display();
			$user_select_event = $settings['mep_event_list'];
			$event_id          = $user_select_event > 0 ? $user_select_event : $post->ID;
			if ( get_post_type( $event_id ) == 'mep_events' ) {
				?>
                <div class="mep-default-share-btn mep-elementor-widget-share-btn">
					<?php require MPWEM_Functions::template_path( 'layout/social.php' ); ?>
                </div>
				<?php
			}
		}
	}
