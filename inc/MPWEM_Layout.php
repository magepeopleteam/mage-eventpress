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
				} else {
					MPWEM_Layout::msg( __( 'Event Not Found !', 'mage-eventpress' ) );
				}
			}
			public static function select_category() {
				$category_lists = MPWEM_Global_Function::get_all_term_data( 'mep_cat' );
				if ( $category_lists && sizeof( $category_lists ) > 0 ) {
					?>
                    <label>
                        <select class="formControl" name="filter_with_category">
                            <option selected value=""><?php esc_html_e( 'Select Category', 'mage-eventpress' ); ?></option>
							<?php foreach ( $category_lists as $category ) { ?>
                                <option value="<?php echo esc_attr( $category ); ?>"><?php echo esc_html( $category ); ?></option>
							<?php } ?>
                        </select>
                    </label>
				<?php }
			}
			public static function load_date( $event_id, $all_dates ) {
				$date = MPWEM_Functions::get_upcoming_date_time( $event_id );
				if ( sizeof( $all_dates ) > 0 ) {
					$date_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
					if ( $date_type == 'no' || $date_type == 'yes' ) {
						$date        = ! empty( $date ) ? $date : current( $all_dates )['time'];
						$date_format = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : '';
						if ( sizeof( $all_dates ) == 1 ) {
							?>
                            <input type="hidden" id="mpwem_date_time" name='mpwem_date_time' value='<?php echo esc_attr( $date ); ?>'/>
						<?php } else { ?>
                            <label>
                                <select class="formControl _min_250" name="mpwem_date_time">
									<?php foreach ( $all_dates as $dates ) { ?>
                                        <option value="<?php echo esc_attr( $dates['time'] ); ?>" <?php echo esc_attr( strtotime( $date ) == strtotime( $dates['time'] ) ? 'selected' : '' ); ?>>
											<?php echo esc_html( MPWEM_Global_Function::date_format( $dates['time'], $date_format ) ); ?>
                                        </option>
									<?php } ?>
                                </select>
                            </label>
							<?php
						}
					} else {
						$date         = $date ?: current( $all_dates );
						$date_format  = MPWEM_Global_Function::date_picker_format();
						$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
						$hidden_date  = $date ? date( 'Y-m-d', strtotime( $date ) ) : '';
						$visible_date = $date ? date_i18n( $date_format, strtotime( $date ) ) : '';
						$all_times    = $all_times ?? MPWEM_Functions::get_times( $event_id, $all_dates, $date );
						$display_time = get_post_meta( $event_id, 'mep_disable_ticket_time', true );
						$display_time = $display_time ?: 'no';
						?>
                        <div class="_dFlex">
                            <label>
                                <input type="hidden" name="mpwem_date_time" value="<?php echo esc_attr( $hidden_date ); ?>" required/>
                                <input id="mpwem_date_time" type="text" value="<?php echo esc_attr( $visible_date ); ?>" class="formControl _min_250" placeholder="<?php echo esc_attr( $now ); ?>" readonly required/>
                            </label>
							<?php if ( $display_time != 'no' && sizeof( $all_times ) > 0 ) { ?>
                                <div class="mpwem_time_area">
									<?php self::load_time( $all_times, $date ); ?>
                                </div>
							<?php } ?>
                        </div>
						<?php
						do_action( 'mpwem_load_date_picker_js', '#mpwem_date_time', $all_dates );
						//echo '<pre>';			print_r($all_times);			echo '</pre>';
					}
				}
			}
			public static function load_time( $all_times, $date ) {
				$hidden_date = $date ? date( 'Y-m-d', strtotime( $date ) ) : '';
				?>
                <label>
                    <select class="formControl _min_200" name="mpwem_time" id="mpwem_time">
						<?php foreach ( $all_times as $times ) { ?>
                            <option value="<?php echo esc_attr( $hidden_date . ' ' . $times['start']['time'] ); ?>"><?php echo esc_html( $times['start']['label'] ?: $times['start']['time'] ); ?></option>
						<?php } ?>
                    </select>
                </label>
				<?php
			}
			public static function elapsed_time_status( $date ) {
				?>
                <div class="mpwem_style">
					<?php
						if ( $date ) {
							$current = current_time( 'Y-m-d H:i:s' );
							if ( strtotime( $current ) >= strtotime( $date ) ) {
								?>
                                <button type="button" class="_successButton_xxs"><?php esc_html_e( "Event Running", "mage-eventpress" ); ?></button>
								<?php
							} else {
								$newformat = date( 'Y-m-d H:i:s', strtotime( $date ) );
								$start     = new DateTime( $newformat );
								$end       = new DateTime( $current );
								$diff      = $start->diff( $end );
								?>
                                <button type="button" class="_themeButton_xxs"><?php echo esc_html( $diff->format( '%a days, %h hours, %i minutes' ) ); ?></button>
								<?php
							}
						} else {
							?>
                            <button type="button" class="_warningButton_xxs"><?php esc_html_e( "Already Expired", "mage-eventpress" ); ?></button>
						<?php } ?>
                </div>
				<?php
			}
			public static function seat_status( $post_id, $date ) {
				$admin_url = get_admin_url();
				?>
                <div class="mpwem_style">
					<?php
						if ( $date ) {
							$total_sold      = mep_ticket_type_sold( $post_id, '', $date );
							$total_ticket    = MPWEM_Functions::get_total_ticket( $post_id, $date );
							$total_reserve   = MPWEM_Functions::get_reserve_ticket( $post_id, $date );
							$total_available = $total_ticket - ( $total_sold + $total_reserve );
							$total_available = max( $total_available, 0 );
							?>
                            <div class="buttonGroup status_action">
                                <button type="button" class="_themeButton_xxs seat_status_area"><?php echo esc_html( $total_ticket . '-' . $total_sold . '-' . $total_reserve . '=' . $total_available ); ?></button>
                                <button type="button" class="_secondaryButton_xxs mpwem_reload_seat_status" data-date="<?php echo esc_attr($date); ?>" data-post_id="<?php echo esc_attr($post_id); ?>" title="<?php esc_attr_e( "Reload Seat Status", "mage-eventpress" ); ?>"><span class="fas fa-refresh mp_zero"></span></button>
                                <button class="_primaryButton_xxs" type="button" data-blank="yes" data-href="<?php echo esc_url( $admin_url ); ?>edit.php?post_type=mep_events&page=attendee_statistics&event_id=<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( "Click To View Statistics", "mage-eventpress" ); ?>"><span class="fas fa-stream mp_zero"></span></button>
                            </div>
							<?php
						} else {
							?>
                            <button class="_primaryButton_xxs" type="button" data-blank="yes" data-href="<?php echo esc_url( $admin_url ); ?>edit.php?post_type=mep_events&page=attendee_statistics&event_id=<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( "Click To View Statistics", "mage-eventpress" ); ?>"><?php esc_html_e( "View Statistics", "mage-eventpress" ); ?></button>
							<?php
						}
					?>
                </div>
				<?php
			}
		}
		new MPWEM_Layout();
	}