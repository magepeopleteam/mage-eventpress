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
class MEPEventCityListWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-city-list-widget';
	}

	public function get_title() {
		return __( 'Event City List', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-skill-bar';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_city_list_settings',
			[
				'label' => __( 'Event City List Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'mep_event_city_list_icon',
			[
				'label' => __( 'Icon Before Title', 'mage-eventpress' ),
				'type' => Controls_Manager::ICON,
				'include' => [
					'fas fa-check-circle',
					'fas fa-check',
					'fas fa-check-square',
					'far fa-check-square',
					'far fa-check-circle',
					'fas fa-check-double',
					'fas fa-calendar-check'
				],
				'default' => 'fas fa-check-circle',
			]
		);


        $this->end_controls_section();


		$this->start_controls_section(
			'mep_event_city_style_settings',
			[
				'label' => __( 'Event City Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
				'mep_event_city_title_color',
				[
					'label' => __( 'Event City Title Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-city-list-widget .mep-city-list ul li a' => 'color: {{VALUE}};',
					],
				]
	    );

		$this->add_control(
				'mep_event_city_icon_color',
				[
					'label' => __( 'Event City Icon Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-city-list-widget .mep-city-list ul li a i' => 'color: {{VALUE}};',
					],
				]
	    );

        $this->end_controls_section();
	
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		$mep_event_city_list_icon_html = '<i class="' . esc_attr($settings['mep_event_city_list_icon']) . '" aria-hidden="true"></i>';
	?>	
	<div class="mep-elementor-event-city-list-widget">
		<?php echo do_shortcode('[event-city-list]'); ?>
	</div>
	<script type="text/javascript">
		 jQuery(document).ready(function(){
		 	jQuery('.mep-city-list li a').prepend('<?php echo wp_kses_post($mep_event_city_list_icon_html); ?>');
		});
	</script>
	<?php
}

}
