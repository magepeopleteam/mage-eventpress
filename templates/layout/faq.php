<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$faqs     = MP_Global_Function::get_post_info( $event_id, 'mep_event_faq', [] );
	if ( $faqs && sizeof( $faqs ) > 0 ) {
		//echo '<pre>';print_r( $faqs );echo '</pre>';
		?>
        <div class="faq_area">
            <h2><?php esc_html_e( 'Frequently asked questions', 'mage-eventpress' ); ?></h2>

            <div class="faq_items">
	            <?php foreach ($faqs as $key => $faq){ ?>
                <div class="item">
                    <div class="title" data-collapse-target="faq-content-<?php echo esc_attr($key); ?>">
                        <h2><?php echo esc_html($faq['mep_faq_title']); ?></h2>
                        <i class="fa fa-chevron-right"></i>
                    </div>
                    <div class="content" data-collapse="faq-content-<?php echo esc_attr($key); ?>">
	                    <?php echo mep_esc_html(html_entity_decode(nl2br($faq['mep_faq_content']))); ?>
                    </div>
                </div>
			<?php } ?>
            </div>
        </div>
		<?php
	}