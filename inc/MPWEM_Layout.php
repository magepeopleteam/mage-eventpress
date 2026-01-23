<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Layout' ) ) {
		$GLOBALS['mpwem_event_statistics_exit'] = false;
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
                <div class="_margin_zero_text_center <?php echo esc_attr( $class ); ?>">
                    <label class="_text_theme"><?php echo esc_html( $msg ); ?></label>
                </div>
				<?php
			}
			public static function select_post_id() {
				$post_ids = MPWEM_Query::get_all_post_ids( 'mep_events' );
				if ( $post_ids && sizeof( $post_ids ) > 0 ) {
					?>
                    <label>
                        <select class="formControl" name="mpwem_post_id">
                            <option value="0" selected><?php esc_html_e( 'Select Event', 'mage-eventpress' ); ?></option>
							<?php foreach ( $post_ids as $post_id ) {
								// Get event title
								$event_title = get_the_title( $post_id );
								// Get event start date
								$all_dates  = MPWEM_Functions::get_all_dates( $post_id );
								$event_date = MPWEM_Functions::get_upcoming_date_time( $post_id, $all_dates );
								// Format the date if available
								$date_display = '';
								if ( $event_date ) {
									$date_display = ' - ' . date_i18n( get_option( 'date_format' ), strtotime( $event_date ) );
								}

								// Check if expired
								$active_dates  = MPWEM_Functions::get_dates( $post_id );
								$expired_label = empty( $active_dates ) ? ' (' . __( 'Expired', 'mage-eventpress' ) . ')' : '';

								// Create the display text: "Event Name - Date (Expired) (ID: XXX)"
								$display_text = $event_title . $date_display . $expired_label . ' (ID: ' . $post_id . ')';
								?>
                                <option value="<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html( $display_text ); ?></option>
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
				if ( sizeof( $all_dates ) > 0 ) {
					$date_type = MPWEM_Global_Function::get_post_info( $event_id, 'mep_enable_recurring', 'no' );
					if ( $date_type == 'no' || $date_type == 'yes' ) {
						if ( sizeof( $all_dates ) == 1 ) {
							$date = MPWEM_Functions::get_upcoming_date_time( $event_id );
							$date = ! empty( $date ) ? $date : current( $all_dates )['time'];
							?>
                            <input type="hidden" id="mpwem_date_time" name='mpwem_date_time' value='<?php echo esc_attr( $date ); ?>'/>
						<?php } else { ?>
                            <label>
                                <select class="formControl _min_250" name="mpwem_date_time">
                                    <option value="" selected><?php esc_html_e( 'Select Date', 'mage-eventpress' ); ?></option>
									<?php foreach ( $all_dates as $dates ) {
										$date_format = MPWEM_Global_Function::check_time_exit_date( $dates['time'] ) ? 'full' : '';
										?>
                                        <option value="<?php echo esc_attr( $dates['time'] ); ?>">
											<?php echo esc_html( MPWEM_Global_Function::date_format( $dates['time'], $date_format ,$event_id) ); ?>
                                        </option>
									<?php } ?>
                                </select>
                            </label>
							<?php
						}
					} else {
						$date         = MPWEM_Functions::get_upcoming_date_time( $event_id );
						$date_format  = MPWEM_Global_Function::date_picker_format();
						$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
						$all_times    = $all_times ?? MPWEM_Functions::get_times( $event_id, $all_dates, $date );
						$display_time = get_post_meta( $event_id, 'mep_disable_ticket_time', true );
						$display_time = $display_time ?: 'no';
						?>
                        <div class="_dFlex">
                            <label>
                                <input type="hidden" name="mpwem_date_time" value="" required/>
                                <input id="mpwem_date_time" type="text" value="" class="formControl _min_250" placeholder="<?php echo esc_attr( $now ); ?>" readonly required/>
                            </label>
							<?php if ( $display_time != 'no' && sizeof( $all_times ) > 0 ) { ?>
                                <div class="mpwem_time_area">
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
                                <button type="button" class="_button_success_xxs"><?php esc_html_e( "Event Running", "mage-eventpress" ); ?></button>
								<?php
							} else {
								$newformat = date( 'Y-m-d H:i:s', strtotime( $date ) );
								$start     = new DateTime( $newformat );
								$end       = new DateTime( $current );
								$diff      = $start->diff( $end );
								?>
                                <button type="button" class="_button_theme_xxs"><?php echo esc_html( $diff->format( '%a days, %h hours, %i minutes' ) ); ?></button>
								<?php
							}
						} else {
							?>
                            <button type="button" class="_button_warning_xxs"><?php esc_html_e( "Already Expired", "mage-eventpress" ); ?></button>
						<?php } ?>
                </div>
				<?php
			}
			public static function seat_status( $post_id, $date ) {
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
                                <button type="button" class="_button_theme_xxs seat_status_area"><?php echo esc_html( $total_ticket . '-' . $total_sold . '-' . $total_reserve . '=' . $total_available ); ?></button>
                                <button type="button" class="_button_secondary_xxs mpwem_reload_seat_status" data-date="<?php echo esc_attr( $date ); ?>" data-post_id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( "Reload Seat Status", "mage-eventpress" ); ?>"><span class="fas fa-refresh _mp_zero"></span></button>
                                <button class="_button_primary_xxs" type="button" data-mpwem_popup_attendee_statistic="mpwem_popup_attendee_statistic" data-event-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( "Click To View Statistics", "mage-eventpress" ); ?>"><span class="fas fa-stream _mp_zero"></span></button>
                            </div>
							<?php
						} else {
							?>
                            <button class="_button_primary_xxs" type="button" data-mpwem_popup_attendee_statistic="mpwem_popup_attendee_statistic" data-event-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( "Click To View Statistics", "mage-eventpress" ); ?>"><?php esc_html_e( "View Statistics", "mage-eventpress" ); ?></button>
							<?php
						}
					?>
                </div>
				<?php
				if (!$GLOBALS['mpwem_event_statistics_exit']) {
					$GLOBALS['mpwem_event_statistics_exit'] = true;
                    ?>
                    <div class="mpPopup mpwem_style mpwem_popup_attendee_statistic" data-popup="mpwem_popup_attendee_statistic"></div>
                    <?php
                }
			}
			public static function get_form_array( $event_id ) {
				$form_id    = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_reg_form_id', 'custom_form' );
				$form_id    = $form_id == 'custom_form' ? $event_id : $form_id;
				$form_array = [];
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_full_name' ) ) {
					$form_array['user_name'] = [
						'type'     => 'text',
						'name'     => 'user_name',
						'd_name'   => 'ea_name',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_name_label', esc_html__( 'Name', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_email' ) ) {
					$form_array['user_email'] = [
						'type'     => 'email',
						'name'     => 'user_email',
						'd_name'   => 'ea_email',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_email_label', esc_html__( 'Email', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_phone' ) ) {
					$form_array['user_phone'] = [
						'type'     => 'text',
						'name'     => 'user_phone',
						'd_name'   => 'ea_phone',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_phone_label', esc_html__( 'Phone', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_address' ) ) {
					$form_array['user_address'] = [
						'type'     => 'textarea',
						'name'     => 'user_address',
						'd_name'   => 'ea_address_1',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_address_label', esc_html__( 'Address', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_tshirtsize' ) ) {
					$form_array['tshirtsize'] = [
						'type'     => 'select',
						'name'     => 'user_tshirtsize',
						'd_name'   => 'ea_tshirtsize',
						'required' => 1,
						'data'     => MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_tshirtsize_list' ),
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_tshirt_label', esc_html__( 'T-Shirt Size', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_gender' ) ) {
					$form_array['gender'] = [
						'type'     => 'gender',
						'name'     => 'user_gender',
						'd_name'   => 'ea_gender',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_gender_label', esc_html__( 'Gender', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_company' ) ) {
					$form_array['user_company'] = [
						'type'     => 'text',
						'name'     => 'user_company',
						'd_name'   => 'ea_company',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_company_label', esc_html__( 'Company', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_designation' ) ) {
					$form_array['user_designation'] = [
						'type'     => 'text',
						'name'     => 'user_designation',
						'd_name'   => 'ea_desg',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_desg_label', esc_html__( 'Designation', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_website' ) ) {
					$form_array['user_website'] = [
						'type'     => 'text',
						'name'     => 'user_website',
						'd_name'   => 'ea_website',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_website_label', esc_html__( 'Website', 'mage-eventpress' ) ),
					];
				}
				if ( MPWEM_Global_Function::get_post_info( $form_id, 'mep_reg_veg' ) ) {
					$form_array['vegetarian'] = [
						'type'     => 'vegetarian',
						'name'     => 'user_vegetarian',
						'd_name'   => 'ea_vegetarian',
						'required' => 1,
						'label'    => MPWEM_Global_Function::get_post_info( $form_id, 'mep_veg_label', esc_html__( 'Vegetarian', 'mage-eventpress' ) ),
					];
				}
				$custom_forms = self::get_custom_form_array( $event_id, $form_id );
				return array_merge( $form_array, $custom_forms );
			}
			public static function get_custom_form_array( $event_id, $form_id = '' ) {
				if ( ! $form_id ) {
					$form_id = MPWEM_Global_Function::get_post_info( $event_id, 'mep_event_reg_form_id', 'custom_form' );
					$form_id = $form_id == 'custom_form' ? $event_id : $form_id;
				}
				$form_array   = [];
				$custom_forms = MPWEM_Global_Function::get_post_info( $form_id, 'mep_form_builder_data', [] );
				if ( sizeof( $custom_forms ) > 0 ) {
					$conditional_infos = MPWEM_Global_Function::get_post_info( $form_id, 'mep_conditional_infos', [] );
					$conditional_check = MPWEM_Global_Function::get_post_info( $form_id, 'mep_conditional_form_check', 'off' );
					foreach ( $custom_forms as $custom_form ) {
						$type  = array_key_exists( 'mep_fbc_type', $custom_form ) ? $custom_form['mep_fbc_type'] : '';
						$id    = array_key_exists( 'mep_fbc_id', $custom_form ) ? $custom_form['mep_fbc_id'] : '';
						$label = array_key_exists( 'mep_fbc_label', $custom_form ) ? $custom_form['mep_fbc_label'] : '';
						if ( $type && $id && $label ) {
							$form_array[ $id ]['type']     = $type;
							$form_array[ $id ]['name']     = $id;
							$form_array[ $id ]['d_name']   = 'ea_' . $id;
							$form_array[ $id ]['label']    = $label;
							$form_array[ $id ]['required'] = array_key_exists( 'mep_fbc_required', $custom_form ) ? $custom_form['mep_fbc_required'] : '';
							$form_array[ $id ]['data']     = array_key_exists( 'mep_fbc_dp_data', $custom_form ) ? $custom_form['mep_fbc_dp_data'] : '';
							$form_array[ $id ]['tag']      = array_key_exists( 'mep_title_type', $custom_form ) ? $custom_form['mep_title_type'] : '';
							$active_condition              = array_key_exists( 'mep_active_conditional', $custom_form ) ? $custom_form['mep_active_conditional'] : 0;
							if ( $conditional_check == 'on' && $active_condition > 0 && sizeof( $conditional_infos ) > 0 ) {
								foreach ( $conditional_infos as $conditional_info ) {
									$type_id = array_key_exists( 'child_id', $conditional_info ) ? $conditional_info['child_id'] : '';
									if ( $id == $type_id ) {
										$form_array[ $id ]['depend']       = array_key_exists( 'type', $conditional_info ) ? $conditional_info['type'] : '';
										$form_array[ $id ]['parent_id']    = array_key_exists( 'parent_id', $conditional_info ) ? $conditional_info['parent_id'] : '';
										$form_array[ $id ]['parent_value'] = array_key_exists( 'parent_value', $conditional_info ) ? $conditional_info['parent_value'] : '';
									}
								}
							}
						}
					}
				}
				return $form_array;
			}
		}
		new MPWEM_Layout();
	}