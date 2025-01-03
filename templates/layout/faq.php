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
	            <?php foreach ($faqs as $faq){ ?>
                <div class="item">
                    <div class="title">
                        <h2><?php echo esc_html($faq['mep_faq_title']); ?></h2>
                        <i class="fa fa-chevron-right"></i>
                    </div>
                    <div class="content">
	                    <?php echo mep_esc_html(html_entity_decode(nl2br($faq['mep_faq_content']))); ?>
                    </div>
                </div>
			<?php } ?>
            </div>
        </div>
		<?php
	}