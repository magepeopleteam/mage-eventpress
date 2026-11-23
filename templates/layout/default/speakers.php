<?php
	/**
	 * Default theme speaker list.
	 *
	 * Two or more speakers render as a two-column card grid. A single speaker
	 * renders as one full-width profile card (avatar beside name and role), so
	 * it does not sit in half of an otherwise empty grid.
	 *
	 * @var int   $event_id
	 * @var array $event_infos
	 * @var array $speaker_lists
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$speaker_lists = isset( $speaker_lists ) && is_array( $speaker_lists ) ? $speaker_lists : [];
	$speaker_lists = array_values( array_filter( array_map( 'absint', $speaker_lists ) ) );
	if ( empty( $speaker_lists ) ) {
		return;
	}
	$is_single  = 1 === count( $speaker_lists );
	// Characters of the role shown before "Read more"; the single card has room for more.
	$role_limit = $is_single ? 90 : 30;
?>
<div class="speaker_list mep-default-speakers<?php echo $is_single ? ' mep-default-speakers--single' : ''; ?>">
	<?php foreach ( $speaker_lists as $speaker_id ) :
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
		if ( $role && $role_len > $role_limit ) {
			$role_short = function_exists( 'mb_substr' ) ? mb_substr( $role, 0, $role_limit ) : substr( $role, 0, $role_limit );
			if ( $is_single ) {
				// Cut on a word boundary so the preview never ends mid-word.
				$last_space = function_exists( 'mb_strrpos' ) ? mb_strrpos( $role_short, ' ' ) : strrpos( $role_short, ' ' );
				if ( $last_space ) {
					$role_short = function_exists( 'mb_substr' ) ? mb_substr( $role_short, 0, $last_space ) : substr( $role_short, 0, $last_space );
				}
				$role_short = rtrim( $role_short, " \t,.;:-" );
			}
		}
		ob_start();
		?>
		<span class="mep-default-speaker__avatar<?php echo $thumbnail ? ' has-image' : ''; ?>">
			<?php if ( $thumbnail ) : ?>
				<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
			<?php else : ?>
				<span class="mep-default-speaker__initial"><?php echo esc_html( $initial ? $initial : '?' ); ?></span>
			<?php endif; ?>
		</span>
		<?php
		$avatar_html = ob_get_clean();
		ob_start();
		?>
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
		<?php
		$role_html = ob_get_clean();
		?>
		<?php if ( $is_single ) : ?>
			<div class="mep-default-speaker mep-default-speaker--single">
				<a class="mep-default-speaker__avatar-link" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
					<?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput -- every value escaped when built above. ?>
				</a>
				<div class="mep-default-speaker__body">
					<a class="mep-default-speaker__link" href="<?php echo esc_url( $permalink ); ?>">
						<span class="mep-default-speaker__name"><?php echo esc_html( $name ); ?></span>
					</a>
					<?php echo $role_html; // phpcs:ignore WordPress.Security.EscapeOutput -- every value escaped when built above. ?>
				</div>
			</div>
		<?php else : ?>
			<div class="mep-default-speaker">
				<a class="mep-default-speaker__link" href="<?php echo esc_url( $permalink ); ?>">
					<?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput -- every value escaped when built above. ?>
					<span class="mep-default-speaker__meta">
						<span class="mep-default-speaker__name"><?php echo esc_html( $name ); ?></span>
					</span>
				</a>
				<?php echo $role_html; // phpcs:ignore WordPress.Security.EscapeOutput -- every value escaped when built above. ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
