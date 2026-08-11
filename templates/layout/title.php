<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$only     = $only ?? '';
	if ( $event_id > 0 ) {
		ob_start();
		if ( $only ) {
			echo get_the_title( $event_id );
		} else {
			$event_template = '';
			if ( isset( $event_infos ) && is_array( $event_infos ) && ! empty( $event_infos['mep_event_template'] ) ) {
				$event_template = $event_infos['mep_event_template'];
			} elseif ( class_exists( 'MPWEM_Global_Function' ) ) {
				$event_template = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_template', '' );
			}
			if ( ! $event_template && function_exists( 'mep_get_option' ) ) {
				$event_template = mep_get_option( 'mep_event_template', 'general_setting_sec', 'default-theme.php' );
			}
			$event_template = $event_template ? $event_template : 'default-theme.php';
			$is_default     = ( 'default-theme.php' === $event_template );
			$title_text     = get_the_title( $event_id );
			if ( $is_default ) {
				?>
                <header class="mpwem_title_block">
                    <h1 class="mpwem_tile"><?php echo esc_html( $title_text ); ?></h1>
                </header>
				<?php
			} else {
				?>
                <h1 class="mpwem_tile"><?php echo esc_html( $title_text ); ?></h1>
				<?php
			}
		}
		$content = ob_get_clean();
		echo apply_filters( 'mage_event_single_title', $content, $event_id );
	}
