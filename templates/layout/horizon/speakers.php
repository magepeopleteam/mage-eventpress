<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$speaker_lists = is_array( $speaker_lists ) ? $speaker_lists : [];
	if ( empty( $speaker_lists ) ) {
		return;
	}
?>
<div class="horizon_artists_grid">
	<?php foreach ( $speaker_lists as $speaker_id ) {
		$speaker_id = (int) $speaker_id;
		if ( $speaker_id <= 0 ) {
			continue;
		}
		$thumbnail = MPWEM_Global_Function::get_image_url( $speaker_id );
		$name      = get_the_title( $speaker_id );
		$role      = get_the_excerpt( $speaker_id );
		$bio       = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $speaker_id ) ), 18, '…' );
		if ( ! $role && $bio ) {
			$role = __( 'Performer', 'mage-eventpress' );
		}
		?>
		<article class="horizon_artist_card">
			<a class="horizon_artist_media" href="<?php echo esc_url( get_permalink( $speaker_id ) ); ?>">
				<?php if ( $thumbnail ) : ?>
					<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $name ); ?>"/>
				<?php else : ?>
					<span class="horizon_artist_placeholder"><?php echo esc_html( strtoupper( substr( $name, 0, 1 ) ) ); ?></span>
				<?php endif; ?>
			</a>
			<div class="horizon_artist_body">
				<h3 class="horizon_artist_name">
					<a href="<?php echo esc_url( get_permalink( $speaker_id ) ); ?>"><?php echo esc_html( $name ); ?></a>
				</h3>
				<?php if ( $role ) : ?>
					<span class="horizon_artist_role"><?php echo esc_html( $role ); ?></span>
				<?php endif; ?>
				<?php if ( $bio ) : ?>
					<p class="horizon_artist_bio"><?php echo esc_html( $bio ); ?></p>
				<?php endif; ?>
			</div>
		</article>
	<?php } ?>
</div>
