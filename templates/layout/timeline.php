<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id        = $event_id ?? get_the_id();
	$time_line_infos = MP_Global_Function::get_post_info( $event_id, 'mep_event_day', [] );
	//echo '<pre>';print_r($time_line_infos);echo '</pre>';
	if ( sizeof( $time_line_infos ) > 0 ) {
		$counter = 0;
		?>
        <div class="mpwem_timeline_area _mB">
            <h3><?php esc_html_e( 'Event Timelines', 'mage-eventpress' ); ?></h3>
	        <div class="timeline_area">
			<?php
				foreach ( $time_line_infos as $time_line_info ) {
					$title       = array_key_exists( 'mep_day_title', $time_line_info ) ? $time_line_info['mep_day_title'] : '';
					$time        = array_key_exists( 'mep_day_time', $time_line_info ) ? $time_line_info['mep_day_time'] : '';
					$content     = array_key_exists( 'mep_day_content', $time_line_info ) ? $time_line_info['mep_day_content'] : '';
					$collapse_id = uniqid( 'mpwem_time_line' );
					$active_class=$counter==0 ? 'mActive' : '';
					$counter++;
					?>
                    <div class="timeline_item _mT">
	                    <span class="timeline_counter"><?php echo esc_html( $counter ); ?></span>
                        <div class="timeline_content">
                            <h6 class="_fullWidth justifyBetween alignCenter" data-collapse-target="<?php echo esc_attr( $collapse_id ); ?>">
                                <span><?php echo esc_html( $title ); ?></span>
                                <span class="timeline_time"><?php echo esc_html( $time ); ?>
                            </h6>
                            <div class="mp_wp_editor <?php echo esc_attr($active_class); ?>" data-collapse="<?php echo esc_attr( $collapse_id ); ?>">
                                <div class="_divider_xs"></div>
								<?php echo apply_filters( 'the_content', $content ); ?>
                            </div>
                        </div>
                    </div>
					<?php
				}
			?>
	        </div>
        </div>
		<?php
	}