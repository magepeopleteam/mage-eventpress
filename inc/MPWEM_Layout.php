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
			public function __construct() { }

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
                    <label class="_min_300">
                        <select class="formControl mp_select2" name="mpwem_post_id">
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