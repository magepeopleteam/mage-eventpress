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
				'mep_event_share-btn_bg_color',
				[
					'label' => __( 'Background Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a' => 'background-color: {{VALUE}};',
					],
				]
	    );
        
		$this->add_control(
				'mep_event_share-btn_icon_color',
				[
					'label' => __( 'Icon Color', 'mage-eventpress' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a i' => 'color: {{VALUE}};',
					],
				]
	    );
		$this->add_responsive_control(
			'mep_event_share_btn_icon_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .mep-default-share-btn.mep-elementor-widget-share-btn ul li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'mep_event_share_btn_icon_space',
			[
				'label' => __( 'Icon Space', 'plugin-domain' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 5,
				],
				'selectors' => [
					'{{WRAPPER}} .mep-default-share-btn.mep-elementor-widget-share-btn ul li' => 'margin: 0 {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'mep_event_share_btn_icon_size',
			[
				'label' => __( 'Icon Size', 'plugin-domain' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 25,
				],
				'selectors' => [
					'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'mep_event_share_btn_width',
			[
				'label' => __( 'Width', 'plugin-domain' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
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
					'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'mep_event_share_btn_height',
			[
				'label' => __( 'Height', 'plugin-domain' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
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
					'{{WRAPPER}} .mep-elementor-widget-share-btn ul.mep-social-share a' => 'height: {{SIZE}}{{UNIT}};',
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
        <div class="mep-default-share-btn mep-elementor-widget-share-btn">
            <?php  //do_action('mep_event_social_share',$event_id); ?>


<div class="mep-event-meta">
   <!-- <span class='mep-share-btn-title'> <?php //_e(mep_get_label($post_id, 'mep_share_text', "Share This $event_label:"), 'mage-eventpress'); ?></span> -->
    <ul class='mep-social-share'>
        <?php do_action('mep_before_social_share_list',$event_id); ?>
        <li> <a data-toggle="tooltip" title="" class="facebook" onclick="window.open('https://www.facebook.com/sharer.php?u=<?php echo get_the_permalink($event_id); ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://www.facebook.com/sharer.php?u=<?php echo get_the_permalink($event_id); ?>" data-original-title="Share on Facebook"><i class="fab fa-facebook-f"></i></a></li>
        <li><a data-toggle="tooltip" title="" class="twitter" onclick="window.open('https://twitter.com/share?url=<?php echo get_the_permalink($event_id); ?>&amp;text=<?php echo get_the_title($event_id); ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://twitter.com/share?url=<?php echo get_the_permalink($event_id); ?>&amp;text=<?php echo get_the_title($event_id); ?>" data-original-title="Twittet it"><i class="fab fa-twitter"></i></a></li>
        <?php //do_action('mep_after_social_share_list',$event_id); ?>
		<li>
			<a href="https://api.whatsapp.com/send?text=<?php echo get_the_title($event_id).' '; ?><?php echo get_the_permalink($event_id); ?>" target="_blank">
				<i class="fab fa-whatsapp"></i>
			</a>
		</li>
		<li>
			<a href="mailto:?subject=I wanted you to see this site&amp;body=<?php echo get_the_title($event_id).' '; ?><?php echo get_the_permalink($event_id); ?>" title="Share by Email">
				<i class="fa fa-envelope"></i>
			</a>
		</li>		
    </ul>
</div>



        </div>
	<?php
}
    }
}
