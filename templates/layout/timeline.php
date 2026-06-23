<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id = $event_id ?? get_the_id();
	$time_line_infos = get_post_meta( $event_id, 'mep_event_day', true );
	if ( is_array( $time_line_infos ) && sizeof( $time_line_infos ) > 0 ) {
		$counter = 0;
		?>
        <div class="mpwem_timeline_area">
            <div class="timeline_section_header">
                <h2><?php esc_html_e( 'Event Timelines', 'mage-eventpress' ); ?></h2>
            </div>
            <div class="timeline_body">
                <div class="timeline_area">
                    <?php
                        foreach ( $time_line_infos as $time_line_info ) {
                            $title        = is_array($time_line_info) && array_key_exists( 'mep_day_title', $time_line_info ) ? $time_line_info['mep_day_title'] : '';
                            $time         = is_array($time_line_info) && array_key_exists( 'mep_day_time', $time_line_info ) ? $time_line_info['mep_day_time'] : '';
                            $content      = is_array($time_line_info) && array_key_exists( 'mep_day_content', $time_line_info ) ? $time_line_info['mep_day_content'] : '';
                            $collapse_id  = uniqid( 'mpwem_time_line' );
                            $active_class = $counter == 0 ? 'mActive' : '';
                            $counter++;
                            ?>
                            <div class="timeline_item">
                                <span class="timeline_counter"><?php echo esc_html( $counter ); ?></span>
                                <div class="timeline_content">
                                    <div class="timeline_header" data-collapse-target="<?php echo esc_attr( $collapse_id ); ?>">
                                        <span class="timeline_tag">
                                            <span class="tl_title"><?php echo esc_html( $title ); ?></span>
                                        </span>
                                        <span class="timeline_connector"></span>
                                        <?php if ( $time ) : ?>
                                            <span class="timeline_time">
                                                <i class="fa fa-clock-o"></i><?php echo esc_html( $time ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mp_wp_editor <?php echo esc_attr( $active_class ); ?>" data-collapse="<?php echo esc_attr( $collapse_id ); ?>">
                                        <?php echo apply_filters( 'the_content', $content ); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                </div>
            </div>
        </div>
		<?php
	}