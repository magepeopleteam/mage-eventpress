<?php
/*
 * Booking social-share card: the card is visible immediately (no click needed).
 * Name/event/date/ticket text and the avatar/logo images are filled in by
 * assets/frontend/js/mep-social-card.js from window.mepSocialCardData (via
 * textContent/src, never innerHTML). The Download and Instagram buttons rasterize the
 * card client-side with html2canvas; Facebook/Twitter/WhatsApp/LinkedIn are plain
 * share-intent links built here in PHP and work with JavaScript disabled.
 *
 * Available: $data (see MPWEM_Social_Card::get_card_data() for keys).
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$networks   = isset( $data['networks'] ) && is_array( $data['networks'] ) ? $data['networks'] : array();
$share_url  = isset( $data['eventUrl'] ) ? $data['eventUrl'] : '';
$share_text = trim( ( isset( $data['statusLabel'] ) ? $data['statusLabel'] . ' — ' : '' ) . ( isset( $data['eventName'] ) ? $data['eventName'] : '' ) );

$network_links = array(
	'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $share_url ),
	'twitter'   => 'https://twitter.com/intent/tweet?text=' . rawurlencode( $share_text ) . '&url=' . rawurlencode( $share_url ),
	'whatsapp'  => 'https://wa.me/?text=' . rawurlencode( $share_text . ' ' . $share_url ),
	'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $share_url ),
);
$network_labels = array(
	'facebook'  => __( 'Facebook', 'mage-eventpress' ),
	'twitter'   => __( 'Twitter / X', 'mage-eventpress' ),
	'whatsapp'  => __( 'WhatsApp', 'mage-eventpress' ),
	'linkedin'  => __( 'LinkedIn', 'mage-eventpress' ),
	'instagram' => __( 'Instagram', 'mage-eventpress' ),
);
$network_icons = array(
	'facebook'  => '&#102;',
	'twitter'   => '&#120;',
	'whatsapp'  => '&#128241;',
	'linkedin'  => '&#105;&#110;',
	'instagram' => '&#128247;',
);

$style      = class_exists( 'MPWEM_Social_Card' ) ? MPWEM_Social_Card::get_style_settings() : array();
$frame_image = ! empty( $style['frameImage'] ) ? $style['frameImage'] : '';
$font_family = ! empty( $style['fontFamily'] ) && $style['fontFamily'] !== 'default' ? $style['fontFamily'] : '';
$text_color  = ! empty( $style['textColor'] ) ? $style['textColor'] : '#111827';
$accent_color = ! empty( $style['accentColor'] ) ? $style['accentColor'] : '#059669';

// Individual values aren't escaped here — the whole attribute is escaped once, below,
// where it's echoed (esc_url() is still needed on the image URL, since that's sanitizing
// the URL itself, not just making it attribute-safe).
$card_style_vars = sprintf( '--mep-sc-text-color:%s;--mep-sc-accent-color:%s;', $text_color, $accent_color );
if ( $font_family ) {
	$card_style_vars .= sprintf( "--mep-sc-font-family:'%s';", $font_family );
}
if ( $frame_image ) {
	$card_style_vars .= sprintf( "background-image:url('%s');", esc_url( $frame_image ) );
}
$card_class = 'mep-sc-card' . ( $frame_image ? ' mep-sc-card--has-frame' : '' );
?>
<div class="mep-sc-wrap" id="mep-sc-wrap">
	<div class="mep-sc-stage">
		<div class="<?php echo esc_attr( $card_class ); ?>" id="mep-sc-card" style="<?php echo esc_attr( $card_style_vars ); ?>">
			<div class="mep-sc-card-lines" aria-hidden="true"></div>

			<div class="mep-sc-card-header">
				<div class="mep-sc-brand">
					<img class="mep-sc-logo" id="mep-sc-logo" alt="" hidden crossorigin="anonymous">
					<span class="mep-sc-brand-name" id="mep-sc-brand-name"></span>
				</div>
				<span class="mep-sc-badge" aria-hidden="true">&#10003;</span>
			</div>

			<div class="mep-sc-avatar-ring">
				<img class="mep-sc-avatar" id="mep-sc-avatar" crossorigin="anonymous" alt="">
			</div>

			<h2 class="mep-sc-name" id="mep-sc-name"></h2>
			<p class="mep-sc-headline" id="mep-sc-headline"></p>

			<div class="mep-sc-meta">
				<div class="mep-sc-meta-row" id="mep-sc-ticket-row">
					<span class="mep-sc-meta-icon" aria-hidden="true">&#127903;</span>
					<span class="mep-sc-meta-text" id="mep-sc-ticket"></span>
				</div>
				<div class="mep-sc-meta-row" id="mep-sc-date-row">
					<span class="mep-sc-meta-icon" aria-hidden="true">&#128197;</span>
					<span class="mep-sc-meta-text" id="mep-sc-date"></span>
				</div>
			</div>

			<div class="mep-sc-event-name" id="mep-sc-event-name"></div>

			<div class="mep-sc-footer">
				<span id="mep-sc-footer-site"></span>
			</div>
		</div>
	</div>

	<div class="mep-sc-actions">
		<button type="button" class="mep-sc-btn mep-sc-btn-primary" id="mep-sc-download"><?php echo esc_html( $data['buttonText'] ); ?></button>
	</div>
	<p class="mep-sc-status" id="mep-sc-status" aria-live="polite"></p>

	<?php if ( ! empty( $networks ) ) : ?>
		<div class="mep-sc-share-row">
			<?php foreach ( $networks as $network ) :
				if ( ! isset( $network_labels[ $network ] ) ) {
					continue;
				}
				?>
				<?php if ( $network === 'instagram' ) : ?>
					<button type="button" class="mep-sc-share-btn mep-sc-share-btn--instagram" id="mep-sc-share-instagram" aria-label="<?php echo esc_attr( $network_labels[ $network ] ); ?>">
						<span aria-hidden="true"><?php echo $network_icons[ $network ]; // phpcs:ignore WordPress.Security.EscapeOutput -- static HTML entity ?></span>
						<span class="mep-sc-share-btn-label"><?php echo esc_html( $network_labels[ $network ] ); ?></span>
					</button>
				<?php else : ?>
					<a
						class="mep-sc-share-btn mep-sc-share-btn--<?php echo esc_attr( $network ); ?>"
						href="<?php echo esc_url( $network_links[ $network ] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php echo esc_attr( $network_labels[ $network ] ); ?>"
					>
						<span aria-hidden="true"><?php echo $network_icons[ $network ]; // phpcs:ignore WordPress.Security.EscapeOutput -- static HTML entity ?></span>
						<span class="mep-sc-share-btn-label"><?php echo esc_html( $network_labels[ $network ] ); ?></span>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
