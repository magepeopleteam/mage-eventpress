<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Layout' ) ) {
		class MPWEM_Layout {
			public function __construct() {
				add_action( 'mep_event_expire_text', [ $this, 'event_expire_text' ] );
				add_action( 'mep_event_no_seat_text', [ $this, 'event_no_seat_text' ] );
			}

			public function event_expire_text() {
				ob_start();
				?>
                <span class=event-expire-btn><?php echo mep_get_option( 'mep_event_expired_text', 'label_setting_sec', __( 'Sorry, this event is expired and no longer available.', 'mage-eventpress' ) ); ?></span>
				<?php
				echo ob_get_clean();
			}

			public function event_no_seat_text() {
				ob_start();
				?>
                <span class=event-expire-btn><?php echo mep_get_option( 'mep_no_seat_available_text', 'label_setting_sec', __( 'Sorry, There Are No Seats Available', 'mage-eventpress' ) ); ?></span>
				<?php
				echo ob_get_clean();
			}

			public static function msg( $msg, $class = '' ): void {
				?>
                <div class="_mZero_textCenter <?php echo esc_attr( $class ); ?>">
                    <label class="_textTheme"><?php echo esc_html( $msg ); ?></label>
                </div>
				<?php
			}

			public static function select_post_id() {
				$post_ids = MPWEM_Global_Function::get_all_post_id( 'mep_events' );
				if ( $post_ids && sizeof( $post_ids ) > 0 ) {
					?>
                    <label>
                        <select class="formControl" name="mpwem_post_id">
                            <option value="0" selected><?php esc_html_e( 'Select Event', 'mage-eventpress' ); ?></option>
							<?php foreach ( $post_ids as $post_id ) { ?>
                                <option value="<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></option>
							<?php } ?>
                        </select>
                    </label>
					<?php
				}
			}
		}
		new MPWEM_Layout();
	}