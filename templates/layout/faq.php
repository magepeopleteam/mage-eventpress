<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$faqs            = get_post_meta( $event_id, 'mep_event_faq', true );
	$faq_description = get_post_meta( $event_id, 'mep_faq_description', true );
	$faq_description = $faq_description ?: '';
	if ( $faqs && is_array( $faqs ) && sizeof( $faqs ) > 0 ) {
		?>
        <div class="faq_area">
            <h2><?php esc_html_e( 'Frequently asked questions', 'mage-eventpress' ); ?></h2>
            <div class="description"><?php echo wp_kses_post( $faq_description ); ?></div>
            <div class="faq_items">
				<?php foreach ( $faqs as $key => $faq ) { ?>
                    <div class="item">
                        <div class="title" data-collapse-target="faq-content-<?php echo esc_attr( $key ); ?>">
                            <h3><?php echo esc_html( $faq['mep_faq_title'] ); ?></h3>
                            <i class="fa fa-chevron-right"></i>
                        </div>
                        <div class="content" data-collapse="faq-content-<?php echo esc_attr( $key ); ?>">
                            <?php
                                $content = $faq['mep_faq_content'] ?? '';
                                $content = preg_replace('/href\s*=\s*"wp-content\//i', 'href="/wp-content/', $content);
                                $content = preg_replace("/href\s*=\s*'wp-content\//i", "href='/wp-content/", $content);
                                echo wpautop( wp_kses_post( $content ) );
                            ?>
                        </div>
                    </div>
				<?php } ?>
            </div>
        </div>
		<?php
	}
