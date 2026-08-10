<?php
	/**
	 * Default theme speaker list.
	 *
	 * @var int   $event_id
	 * @var array $event_infos
	 * @var array $speaker_lists
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$speaker_lists = isset( $speaker_lists ) && is_array( $speaker_lists ) ? $speaker_lists : [];
	if ( empty( $speaker_lists ) ) {
		return;
	}
?>
<div class="speaker_list mep-default-speakers">
	<?php foreach ( $speaker_lists as $speaker_id ) :
		$speaker_id = absint( $speaker_id );
		if ( ! $speaker_id ) {
			continue;
		}
		$thumbnail = MPWEM_Global_Function::get_image_url( $speaker_id );
		$name      = get_the_title( $speaker_id );
		$role      = trim( (string) get_the_excerpt( $speaker_id ) );
		if ( ! $role ) {
			$role = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $speaker_id ) ), 8, '…' );
		}
		$initial = function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $name, 0, 1 ) ) : strtoupper( substr( $name, 0, 1 ) );
		$permalink = get_permalink( $speaker_id );
		?>
		<a class="mep-default-speaker" href="<?php echo esc_url( $permalink ); ?>">
			<span class="mep-default-speaker__avatar<?php echo $thumbnail ? ' has-image' : ''; ?>">
				<?php if ( $thumbnail ) : ?>
					<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
				<?php else : ?>
					<span class="mep-default-speaker__initial"><?php echo esc_html( $initial ? $initial : '?' ); ?></span>
				<?php endif; ?>
			</span>
			<span class="mep-default-speaker__meta">
				<span class="mep-default-speaker__name"><?php echo esc_html( $name ); ?></span>
				<?php if ( $role ) : ?>
					<span class="mep-default-speaker__role"><?php echo esc_html( $role ); ?></span>
				<?php endif; ?>
			</span>
		</a>
	<?php endforeach; ?>
</div>
