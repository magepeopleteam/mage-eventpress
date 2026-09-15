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
		$role_len  = function_exists( 'mb_strlen' ) ? mb_strlen( $role ) : strlen( $role );
		$role_short = '';
		if ( $role && $role_len > 30 ) {
			$role_short = function_exists( 'mb_substr' ) ? mb_substr( $role, 0, 30 ) : substr( $role, 0, 30 );
		}
		?>
		<div class="mep-default-speaker">
			<a class="mep-default-speaker__link" href="<?php echo esc_url( $permalink ); ?>">
				<span class="mep-default-speaker__avatar<?php echo $thumbnail ? ' has-image' : ''; ?>">
					<?php if ( $thumbnail ) : ?>
						<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
					<?php else : ?>
						<span class="mep-default-speaker__initial"><?php echo esc_html( $initial ? $initial : '?' ); ?></span>
					<?php endif; ?>
				</span>
				<span class="mep-default-speaker__meta">
					<span class="mep-default-speaker__name"><?php echo esc_html( $name ); ?></span>
				</span>
			</a>
			<?php if ( $role ) : ?>
				<?php if ( $role_short ) : ?>
					<span class="mep-default-speaker__role is-collapsed" data-mep-speaker-role>
						<span class="mep-default-speaker__role-text" data-short="<?php echo esc_attr( $role_short ); ?>" data-full="<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $role_short ); ?>…</span>
						<button type="button" class="mep-default-speaker__role-more" data-mep-speaker-role-toggle aria-expanded="false">
							<span data-label-more><?php esc_html_e( 'Read more', 'mage-eventpress' ); ?></span>
							<span data-label-less hidden><?php esc_html_e( 'Read less', 'mage-eventpress' ); ?></span>
						</button>
					</span>
				<?php else : ?>
					<span class="mep-default-speaker__role"><?php echo esc_html( $role ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
