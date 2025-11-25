<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Attendee_Statistics' ) ) {
		class MPWEM_Attendee_Statistics {
			public function __construct() {
				add_action( 'admin_menu', array( $this, 'attendee_statistics_menu' ) );
				add_action( 'wp_ajax_mpwem_load_date', array( $this, 'mpwem_load_date' ) );
				add_action( 'wp_ajax_mpwem_load_time', array( $this, 'mpwem_load_time' ) );
				add_action( 'wp_ajax_mpwem_load_attendee_statistics', array( $this, 'mpwem_load_attendee_statistics' ) );
			}
			public function attendee_statistics_menu() {
				add_submenu_page( 'edit.php?post_type=mep_events', __( 'Attendee Statistics ', 'mage-eventpress' ), __( 'Attendee Statistics', 'mage-eventpress' ), 'manage_woocommerce', 'attendee_statistics', [ $this, 'attendee_statistics' ] );
			}
			public function attendee_statistics() {
				?>
                <div class="wrap"></div>
                <div id="mpwem_recurring_statistics">
                    <div class="mpwem_style">
                        <div class="filter_area">
                            <div class="dLayout _pRelative">
                                <h4><?php esc_html_e( 'Event Attendee Statistics List', 'mage-eventpress' ); ?></h4>
                                <div class="_divider"></div>
                                <div class="_dFlex">
									<?php MPWEM_Layout::select_post_id(); ?>
                                    <div class="date_time_area"></div>
                                </div>
                                <button type="button" class="themeButton _min_100_mT" id="mpwem_load_attendee_statistics"><?php esc_html_e( 'Filter', 'mage-eventpress' ); ?></button>
                            </div>
                        </div>
                        <div class="statistics_list">
                        </div>
                    </div>
                </div>
				<?php
			}
			public function mpwem_load_date() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$all_dates = MPWEM_Functions::get_all_dates( $post_id );
				MPWEM_Layout::load_date( $post_id, $all_dates );
				die();
			}
			public function mpwem_load_time() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$date      = isset( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : '';
				$all_times = MPWEM_Functions::get_all_times( $post_id, $date );
				MPWEM_Layout::load_time( $all_times, $date );
				//echo '<pre>';print_r(MPWEM_Functions::get_all_dates($post_id));echo '</pre>';
				die();
			}
			public function mpwem_load_attendee_statistics() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mpwem_admin_nonce' ) ) {
					wp_send_json_error( 'Invalid nonce!' ); // Prevent unauthorized access
				}
				$event_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				if ( ! current_user_can( 'edit_post', $event_id ) ) {
					wp_send_json_error( [ 'message' => 'User cannot edit this post' ] );
					die;
				}
				$date = isset( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : '';
				if ( $event_id && $date ) {
					$ticket_types = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_ticket_type', [] );
					$date_format  = MPWEM_Global_Function::check_time_exit_date( $date ) ? 'full' : 'date';
					?>
                    <div class="dLayout">
                        <h4><?php echo esc_html( get_the_title( $event_id ) . ' -  ' . MPWEM_Global_Function::date_format( $date, $date_format ) ); ?></h4>
                        <div class="_divider"></div>
                        <table>
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Ticket Type Name', 'mage-eventpress' ); ?></th>
                                <th><?php esc_html_e( 'Total Seat', 'mage-eventpress' ); ?></th>
                                <th><?php esc_html_e( 'Total Reserved', 'mage-eventpress' ); ?></th>
                                <th><?php esc_html_e( 'Ticket Sold', 'mage-eventpress' ); ?></th>
                                <th><?php esc_html_e( 'Available Seat', 'mage-eventpress' ); ?></th>
                            </tr>
                            </thead>
							<?php if ( sizeof( $ticket_types ) > 0 ) { ?>
                                <tbody>
								<?php
									do_action( 'mpwem_gq_statistics', $event_id, $date );
									foreach ( $ticket_types as $ticket_type ) {
										$ticket_name      = array_key_exists( 'option_name_t', $ticket_type ) ? $ticket_type['option_name_t'] : '';
										$ticket_qty       = array_key_exists( 'option_qty_t', $ticket_type ) ? $ticket_type['option_qty_t'] : 0;
										$ticket_r_qty     = array_key_exists( 'option_rsv_t', $ticket_type ) ? $ticket_type['option_rsv_t'] : 0;
										$total_sold       = mep_get_ticket_type_seat_count( $event_id, $ticket_name, $date, $ticket_qty, $ticket_r_qty );
										$available_ticket = (int) $ticket_qty - ( (int) $total_sold + (int) $ticket_r_qty );
										?>
                                        <tr>
                                            <th><?php echo esc_html( $ticket_name ); ?></th>
                                            <th><?php echo esc_html( apply_filters( 'mpwem_gq_qty_statistics', $ticket_qty, $event_id ) ); ?></th>
                                            <th><?php echo esc_html( apply_filters( 'mpwem_gq_qty_statistics', $ticket_r_qty, $event_id ) ); ?></th>
                                            <th><?php echo esc_html( $total_sold ); ?></th>
                                            <th><?php echo esc_html( apply_filters( 'mpwem_gq_qty_statistics', $available_ticket, $event_id ) ); ?></th>
                                        </tr>
									<?php } ?>
                                </tbody>
							<?php } ?>
                        </table>
                    </div>
					<?php
				}
				//echo '<pre>';print_r(MPWEM_Functions::get_all_dates($post_id));echo '</pre>';
				die();
			}
		}
		new MPWEM_Attendee_Statistics();
	}