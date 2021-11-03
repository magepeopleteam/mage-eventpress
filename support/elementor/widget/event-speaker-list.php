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
class MEPSpeakerListWidget extends Widget_Base {

	public function get_name() {
		return 'mep-event-speaker-list-widget';
	}

	public function get_title() {
		return __( 'Event Speaker List', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-nerd-wink';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'mep_event_speaker_list_settings',
			[
				'label' => __( 'Event Speaker List Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'mep_event_list',
			[
				'label' => __( 'Select Event', 'mage-eventpress' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => mep_elementor_get_events('Show All'),
			]
		);

        $this->end_controls_section();


		$this->start_controls_section(
			'mep_event_speaker_style_settings',
			[
				'label' => __( 'Event Speaker Style Settings', 'mage-eventpress' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
				'mep_event_speaker_title_color',
				[
					'label' => __( 'Title Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-event-speaker-list-widget .mep-default-sidebar-speaker-list ul li h6' => 'color: {{VALUE}};',
					],
				]
	    );

        $this->end_controls_section();
	
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		$mep_event_list = $settings['mep_event_list'] > 0 ? esc_attr($settings['mep_event_list']) : '';
	?>
	<div class="mep-elementor-event-speaker-list-widget">
		<?php echo do_shortcode('[event-speaker-list event="'.$mep_event_list.'"]'); ?>
	</div>
	<?php
}

}
