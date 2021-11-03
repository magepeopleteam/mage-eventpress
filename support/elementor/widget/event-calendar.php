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
class MEPCalendarWidget extends Widget_Base {

	public function get_name() {
		return 'mep-elementor-support';
	}

	public function get_title() {
		return esc_html__( 'Event Calendar', 'mage-eventpress' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'mep-elementor-support' ];
	}

	protected function render() {
?>
<div class="mep-elementor-event-calebdar-widget">
		<?php echo do_shortcode('[event-calendar]'); ?>
</div>
<?php
}
}
