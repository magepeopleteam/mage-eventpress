<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id        = $event_id ?? 0;
	$faqs            = get_post_meta( $event_id, 'mep_event_faq', true );
	$faq_description = get_post_meta( $event_id, 'mep_faq_description', true );
	if ( ! is_array( $faqs ) || empty( $faqs ) ) {
		return;
	}
?>
<section class="horizon_section horizon_faq_wrap">
	<h2 class="horizon_section_title"><?php esc_html_e( 'Frequently Asked Questions', 'mage-eventpress' ); ?></h2>
	<?php if ( $faq_description ) : ?>
		<div class="horizon_faq_intro"><?php echo wp_kses_post( $faq_description ); ?></div>
	<?php endif; ?>
	<div class="horizon_faq_list">
		<?php foreach ( $faqs as $key => $faq ) {
			$is_open = (int) $key === 0;
			$content = $faq['mep_faq_content'] ?? '';
			$content = preg_replace( '/href\s*=\s*"wp-content\//i', 'href="/wp-content/', $content );
			$content = preg_replace( "/href\s*=\s*'wp-content\//i", "href='/wp-content/", $content );
			?>
			<div class="horizon_faq_item<?php echo $is_open ? ' is-open' : ''; ?>">
				<button type="button" class="horizon_faq_trigger" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
					<span class="horizon_faq_q"><?php echo esc_html( $faq['mep_faq_title'] ?? '' ); ?></span>
					<span class="horizon_faq_icon" aria-hidden="true"></span>
				</button>
				<div class="horizon_faq_answer" <?php echo $is_open ? '' : 'hidden'; ?>>
					<?php echo wpautop( wp_kses_post( $content ) ); ?>
				</div>
			</div>
		<?php } ?>
	</div>
</section>
