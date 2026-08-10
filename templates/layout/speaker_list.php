<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id                 = $event_id ?? 0;
	$event_infos              = $event_infos ?? [];
	$event_infos              = ( is_array( $event_infos ) && sizeof( $event_infos ) > 0 ) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$event_speaker_enabled    = is_array( $event_infos ) && array_key_exists( 'mep_event_enable_speaker', $event_infos ) ? $event_infos['mep_event_enable_speaker'] : 'no';
	$speaker_lists            = is_array( $event_infos ) && array_key_exists( 'mep_event_speakers_list', $event_infos ) ? $event_infos['mep_event_speakers_list'] : [];
	$speaker_lists            = is_array( $speaker_lists ) ? $speaker_lists : explode( ',', $speaker_lists );
	if ( $event_speaker_enabled == 'yes' && is_array( $speaker_lists ) && sizeof( $speaker_lists ) > 0 ) {
		?>
        <div class="speaker_list">
			<?php foreach ( $speaker_lists as $speaker_id ) {
				$thumbnail = MPWEM_Global_Function::get_image_url( $speaker_id );
				?>
                <a href="<?php echo esc_url( get_the_permalink( $speaker_id ) ); ?>">
					<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( get_the_title( $speaker_id ) ); ?>">
                    <h6><?php echo esc_html( get_the_title( $speaker_id ) ); ?></h6>
                </a>
			<?php } ?>
        </div>
		<?php
	}
