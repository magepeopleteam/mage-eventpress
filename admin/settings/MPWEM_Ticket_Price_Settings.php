<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Ticket_Price_Settings' ) ) {
		class MPWEM_Ticket_Price_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', array( $this, 'ticket_settings' ), 10, 2 );
			}
			public function ticket_settings( $event_id, $event_infos ) {
				$reg_status        = array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
				$active_reg_status = $reg_status == 'on' ? 'mActive' : '';
				?>
                <div class="mpwem_style mp_tab_item mpwem_ticket_pricing_settings" data-tab-item="#mpwem_ticket_pricing_settings">
					<?php $this->setting_head( $event_id, $event_infos ); ?>
                    <div class="<?php echo esc_attr( $active_reg_status ); ?>" data-collapse="#mep_reg_status">
						<?php $this->ticket_setting( $event_id, $event_infos ); ?>
						<?php $this->ex_service_setting( $event_id ); ?>
                    </div>
					<?php $this->mep_event_pro_purchase_notice(); ?>
                </div>
				<?php
			}
			public function setting_head( $event_id, $event_infos ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				?>
                <div class="_layout_default_xs_mp_zero">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Ticket & Pricing Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Your Ticket & Pricing Settings Here', 'mage-eventpress' ); ?></span>
                    </div>
					<?php
						do_action( 'mep_event_tab_before_ticket_pricing', $event_id );
						$this->event_view_shortcode( $event_id );
						$this->registration_on_off( $event_id, $event_infos );
					?>
                </div>
				<?php
			}
			public function ticket_setting( $event_id, $event_infos ) {
				$ticket_infos        = array_key_exists( 'mep_event_ticket_type', $event_infos ) ? $event_infos['mep_event_ticket_type'] : [];
				$show_advance_column = array_key_exists( 'mep_show_advance_col_status', $event_infos ) ? $event_infos['mep_show_advance_col_status'] : 'off';
				$active_category     = $show_advance_column == 'on' ? 'mActive' : '';
				$event_label         = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				//echo '<pre>';print_r($ticket_infos);echo '</pre>';
				?>
                <div class="_mt"></div>
                <div class="_layout_default_xs_mp_zero ">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Ticket Type Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Ticket Type', 'mage-eventpress' ); ?></span>
                    </div>
					<?php
						do_action( 'mpwem_before_ticket_type', $event_id );
						do_action( 'mep_add_category_display', $event_id );
						$this->show_advance_column( $show_advance_column );
					?>
                    <div class="_padding_bt mpwem_settings_area">
                        <p><?php esc_html_e( 'Once a ticket is sold, it cannot be edited anymore.', 'mage-eventpress' ); ?></p>
                        <div class="_ov_auto">
                            <table class="mpwem_ticket_table">
                                <thead>
                                <tr>
                                    <th class="_min_150" title="<?php esc_attr_e( 'Ticket Type Name', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Ticket', 'mage-eventpress' ); ?></th>
                                    <th class="_min_150" title="<?php esc_attr_e( 'Ticket Type Details', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Short Desc.', 'mage-eventpress' ); ?></th>
                                    <th class="_min_100" title="<?php esc_attr_e( 'Ticket Price', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></th>
                                    <th class="_min_100" title="<?php esc_attr_e( 'Available Qty', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Capacity', 'mage-eventpress' ); ?></th>
                                    <th class="_min_100 <?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_show_advance_col_status" title="<?php esc_attr_e( 'Default Qty', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Default Qty', 'mage-eventpress' ); ?></th>
                                    <th class="_min_100 <?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_show_advance_col_status" title="<?php esc_attr_e( 'Reserve Qty', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Reserve Qty', 'mage-eventpress' ); ?></th>
									<?php do_action( 'mpwem_add_extra_column', $event_id ); ?>
                                    <th class="_min_250 <?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_show_advance_col_status" title="<?php esc_attr_e( 'Sale End Date & Time', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Sale End Date & Time', 'mage-eventpress' ); ?></th>
                                    <th class="_min_150" title="<?php esc_attr_e( 'Qty Box Type', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Qty Box', 'mage-eventpress' ); ?></th>
                                    <th><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></th>
                                </tr>
                                </thead>
                                <tbody class="mpwem_item_insert mpwem_sortable_area">
								<?php
									if ( sizeof( $ticket_infos ) > 0 ) {
										foreach ( $ticket_infos as $ticket_info ) {
											$this->ticket_info( $event_id, $active_category, $ticket_info );
										}
									}
								?>
                                </tbody>
                            </table>
                        </div>
						<?php MPWEM_Custom_Layout::add_new_button( __( 'Add New Ticket Type', 'mage-eventpress' ) ); ?>
                        <div class="mpwem_hidden_content">
                            <table>
                                <tbody class="mpwem_hidden_item">
								<?php $this->ticket_info( $event_id, $active_category ); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function ticket_info( $event_id, $active_category, $ticket_info = [] ) {
				$qty_t_type           = array_key_exists( 'option_qty_t_type', $ticket_info ) ? $ticket_info['option_qty_t_type'] : 'inputbox';
				$option_details       = array_key_exists( 'option_details_t', $ticket_info ) ? $ticket_info['option_details_t'] : '';
				$option_name          = array_key_exists( 'option_name_t', $ticket_info ) ? $ticket_info['option_name_t'] : '';
				$option_name_text     = preg_replace( "/[{}()<>+ ]/", '_', $option_name ) . '_' . $event_id;
				$option_price         = array_key_exists( 'option_price_t', $ticket_info ) ? $ticket_info['option_price_t'] : '';
				$option_qty           = array_key_exists( 'option_qty_t', $ticket_info ) ? $ticket_info['option_qty_t'] : 0;
				$option_default_qty   = array_key_exists( 'option_default_qty_t', $ticket_info ) ? $ticket_info['option_default_qty_t'] : 0;
				$option_rsv_qty       = array_key_exists( 'option_rsv_t', $ticket_info ) ? $ticket_info['option_rsv_t'] : 0;
				$sale_end             = array_key_exists( 'option_sale_end_date_t', $ticket_info ) ? $ticket_info['option_sale_end_date_t'] : '';
				$option_ticket_enable = array_key_exists( 'option_ticket_enable', $ticket_info ) && $ticket_info['option_ticket_enable'] ? $ticket_info['option_ticket_enable'] : 'yes';
				$checked              = $option_ticket_enable == 'yes' ? 'checked' : '';
				$ticket_sold          = 0;
				if ( $option_name ) {
					$filter_args['post_id']        = $event_id;
					$filter_args['ea_ticket_type'] = $option_name;
					$ticket_sold                   = MPWEM_Query::attendee_query( $filter_args )->post_count;
				}
				?>
                <tr class="mpwem_remove_area data_required <?php echo esc_attr( $option_ticket_enable !== 'yes'  && $ticket_sold>0? 'disable_row' : '' ); ?>">
                    <td class="ticket_name">
						<?php  //echo '<pre>';print_r($ticket_sold);echo '</pre>'; ?>
                        <input type="hidden" name="hidden_option_name_t[]" value="<?php echo esc_attr( $option_name_text ); ?>"/>
						<?php if ( $ticket_sold > 0 ) {
							$close_text=$option_ticket_enable == 'yes' ? __( 'SALES DISABLED', 'mage-eventpress' ) : __( 'SALES ACTIVE', 'mage-eventpress' );
							$open_text=$option_ticket_enable == 'yes' ? __( 'SALES ACTIVE', 'mage-eventpress' ) : __( 'SALES DISABLED', 'mage-eventpress' );

                            $ticket_class=$option_ticket_enable == 'yes'?'_button_success_xxs':'_button_danger_xxs';

                            ?>
                            <input type="hidden" class="" name="option_name_t[]" value="<?php echo esc_attr( $option_name ); ?>"/>
                            <div class="_flex_wrap"><span class="_mr_xxs"><?php echo esc_html( $option_name ); ?></span>
                                <span class="<?php echo esc_attr($ticket_class); ?> ticket_status" data-open-text="<?php echo esc_attr($open_text); ?>" data-close-text="<?php echo esc_attr($close_text); ?>"><span data-text><?php echo esc_html($open_text); ?></span></span>
                            </div>
                            <span class="_button_danger_xxs ticket_info" title="<?php _e( 'Ticket type is locked because it already has  ', 'mage-eventpress' );echo $ticket_sold; _e( ' bookings', 'mage-eventpress' )?>"><span data-text><?php esc_html_e( 'Ticket type locked', 'mage-eventpress' ); ?></span></span>

						<?php } else { ?>
                            <label>
                                <input data-required="" type="text" class="formControl name_validation" name="option_name_t[]" placeholder="Ex: Adult" value="<?php echo esc_attr( $option_name ); ?>"/>
                            </label>
						<?php } ?>
                    </td>
                    <td><label><input type="text" class="formControl" name="option_details_t[]" placeholder="" value="<?php echo esc_attr( $option_details ); ?>"/></label></td>
                    <td><label><input type="number" size="4" pattern="[0-9]*" step="0.001" class="formControl" name="option_price_t[]" placeholder="Ex: 10" value="<?php echo esc_attr( $option_price ); ?>"/></label></td>
                    <td><label><input type="number" size="4" pattern="[0-9]*" step="1" class="formControl" name="option_qty_t[]" placeholder="Ex: 500" value="<?php echo esc_attr( $option_qty ) ?>"/></label></td>
                    <td class="<?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_show_advance_col_status">
                        <label><input type="number" size="2" pattern="[0-9]*" step="1" class="formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value="<?php echo esc_attr( $option_default_qty ) ?>"/></label>
                    </td>
                    <td class="<?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_show_advance_col_status">
                        <label><input type="number" class="formControl" name="option_rsv_t[]" placeholder="Ex: 5" value="<?php echo esc_attr( $option_rsv_qty ); ?>"/></label>
                    </td>
					<?php do_action( 'mpwem_add_extra_input_box', $event_id, $ticket_info ); ?>
                    <td class="<?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_show_advance_col_status">
                        <div class="_dFlex">
							<?php MPWEM_Date_Settings::date_item( 'option_sale_end_date[]', $sale_end ); ?>
                            <label>
                                <input type="time" value="<?php echo esc_attr( MPWEM_Global_Function::check_time_exit_date( $sale_end ) ? date( 'H:i', strtotime( $sale_end ) ) : '' ); ?>" name="option_sale_end_time[]" class="formControl"/>
                            </label>
                        </div>
                    </td>
                    <td>
                        <label>
                            <select class="formControl" name="option_qty_t_type[]">
                                <option value="inputbox" <?php echo esc_attr( $qty_t_type == 'inputbox' ? 'Selected' : '' ); ?>><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
                                <option value="dropdown" <?php echo esc_attr( $qty_t_type == 'dropdown' ? 'Selected' : '' ); ?>><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
                            </select>
                        </label>
                    </td>
                    <td>
                        <div class="allCenter">
                            <div class="buttonGroup">
                                <button class="_button_warning_xs mpwem_item_remove" type="button">
                                    <span class="fas fa-trash-alt _mp_zero"></span>
                                </button>
                                <div class="_button_navy_blue_xs mpwem_sortable_button" type="button">
                                    <span class="fas fa-expand-arrows-alt _mp_zero"></span>
                                </div>
	                            <?php
		                            if($ticket_sold>0) {
			                            MPWEM_Custom_Layout::show_hide_button( 'option_ticket_enable[]', $option_ticket_enable );
		                            }else{
			                            ?>
                                        <input type="hidden" name="option_ticket_enable[]" value="yes">
			                            <?php
		                            }
	                            ?>
                            </div>
                        </div>

                    </td>
                </tr>
				<?php
			}
			public function ex_service_setting( $event_id ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$ex_infos    = MPWEM_Global_Function::get_post_info( $event_id, 'mep_events_extra_prices', [] );
				?>
                <div class="_mt"></div>
                <div class="_layout_default_xs_mp_zero">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Extra Service Area', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Extra Service Here. Extra Service as Product that you can sell and it is not included on event package', 'mage-eventpress' ); ?></span>
                    </div>
					<?php do_action( 'mpwem_before_ex_service', $event_id ); ?>
                    <div class="_padding_bt mpwem_settings_area">
                        <div class="_ov_auto">
                            <table>
                                <thead>
                                <tr>
                                    <th title="<?php esc_attr_e( 'Extra Service Name', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Name', 'mage-eventpress' ); ?></th>
                                    <th title="<?php esc_attr_e( 'Extra Service Price', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></th>
                                    <th title="<?php esc_attr_e( 'Available Qty', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Available Qty', 'mage-eventpress' ); ?></th>
                                    <th title="<?php esc_attr_e( 'Qty Box Type', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Qty Box', 'mage-eventpress' ); ?></th>
                                    <th><?php esc_html_e( 'Action', 'mage-eventpress' ); ?></th>
                                </tr>
                                </thead>
                                <tbody class="mpwem_item_insert mpwem_sortable_area">
								<?php
									if ( sizeof( $ex_infos ) > 0 ) {
										foreach ( $ex_infos as $ticket_info ) {
											$this->ex_info( $ticket_info );
										}
									}
								?>
                                </tbody>
                            </table>
                        </div>
						<?php MPWEM_Custom_Layout::add_new_button( __( 'Add Extra Price', 'mage-eventpress' ) ); ?>
                        <div class="mpwem_hidden_content">
                            <table>
                                <tbody class="mpwem_hidden_item">
								<?php $this->ex_info(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function ex_info( $ticket_info = [] ) {
				$option_name  = array_key_exists( 'option_name', $ticket_info ) ? $ticket_info['option_name'] : '';
				$option_price = array_key_exists( 'option_price', $ticket_info ) ? $ticket_info['option_price'] : '';
				$option_qty   = array_key_exists( 'option_qty', $ticket_info ) ? $ticket_info['option_qty'] : 0;
				$qty_t_type   = array_key_exists( 'option_qty_type', $ticket_info ) ? $ticket_info['option_qty_type'] : 'inputbox';
				?>
                <tr class="mpwem_remove_area data_required">
                    <td><label> <input data-required="" type="text" class="formControl" name="option_name[]" placeholder="Ex: Cap" value="<?php echo esc_attr( $option_name ); ?>"/> </label></td>
                    <td><label><input type="number" size="4" pattern="[0-9]*" step="0.001" class="formControl" name="option_price[]" placeholder="Ex: 10" value="<?php echo esc_attr( $option_price ); ?>"/></label></td>
                    <td><label><input type="number" size="4" pattern="[0-9]*" step="1" class="formControl" name="option_qty[]" placeholder="Ex: 500" value="<?php echo esc_attr( $option_qty ) ?>"/></label></td>
                    <td>
                        <label>
                            <select class="formControl" name="option_qty_type[]">
                                <option value="inputbox" <?php echo esc_attr( $qty_t_type == 'inputbox' ? 'Selected' : '' ); ?>><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
                                <option value="dropdown" <?php echo esc_attr( $qty_t_type == 'dropdown' ? 'Selected' : '' ); ?>><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
                            </select>
                        </label>
                    </td>
                    <td><?php MPWEM_Custom_Layout::move_remove_button(); ?></td>
                </tr>
				<?php
			}
			public function event_view_shortcode( $post_id ) {
				?>
                <div class="_padding ">
                    <label class=" _justify_between_align_center_wrap">
                        <span><?php esc_html_e( 'Add To Cart Form Shortcode', 'mage-eventpress' ); ?></span>
                        <code> [event-add-cart-section event="<?php echo esc_html( $post_id ); ?>"]</code>
                    </label>
                    <span class="label-text"><?php esc_html_e( 'If you want to display the ticket type list with an add-to-cart button on any post or page of your website, simply copy the shortcode and paste it where desired.', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}
			public function registration_on_off( $event_id, $event_infos ) {
				$reg_status = array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
                $reg_status_msg_status = array_key_exists( 'mep_reg_status_show_msg', $event_infos ) ? $event_infos['mep_reg_status_show_msg'] : '';
                $reg_status_msg_txt = array_key_exists( 'mep_reg_status_show_msg_txt', $event_infos ) ? $event_infos['mep_reg_status_show_msg_txt'] : '';

				$checked    = $reg_status == 'on' ? 'checked' : '';
                $reg_msg_checked    = $reg_status_msg_status == 'on' ? 'checked' : '';

				?>
                <div class="_padding_bt">
                    <div class=" _justify_between_align_center_wrap">
                        <label><span class="_mr"><?php esc_html_e( 'Registration Off/On', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_reg_status', $checked ); ?>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'Registration Off/On', 'mage-eventpress' ); ?></span>
                </div>
                <div class="_padding_bt reg_close_msg_dash">
                    <div class=" _justify_between_align_center_wrap">
                        <label><span class="_mr"><?php esc_html_e( 'Show Registration Off Message in Event details Page?', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_reg_status_show_msg', $reg_msg_checked ); ?>
                        <div class="mep_reg_status_show_msg_txt_sec">
                            <textarea name="mep_reg_status_show_msg_txt" id="mep_reg_status_show_msg_txt" class="formControl" placeholder="<?php _e( 'Registration for this event is currently closed.', 'mage-eventpress' ); ?>"><?php echo esc_html( $reg_status_msg_txt ); ?></textarea>
                        </div>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'Show Message Off/On', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}
			public function show_advance_column( $show_category ) {
				$checked = $show_category == 'off' ? '' : 'checked';
				?>
                <div class="_padding_bt">
                    <div class=" _justify_between_align_center_wrap">
                        <label><span class="_mr"><?php esc_html_e( 'Show Advanced Column:', 'mage-eventpress' ); ?></span></label>
						<?php MPWEM_Custom_Layout::switch_button( 'mep_show_advance_col_status', $checked ); ?>
                    </div>
                    <span class="label-text"><?php esc_html_e( 'Show Advanced Column:', 'mage-eventpress' ); ?></span>
                </div>
				<?php
			}
			public function mep_event_pro_purchase_notice() {
				?>
                <section class="bg-light" style="margin-top: 20px;">
                    <h2><?php esc_html_e( 'Documentation Links', 'mage-eventpress' ) ?></h2>
                    <span><?php esc_html_e( 'Get Documentation', 'mage-eventpress' ) ?></span>
                </section>
                <section>
					<?php if ( ! mep_check_plugin_installed( 'woocommerce-event-manager-addon-form-builder/addon-builder.php' ) ) : ?>
                        <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Get Individual Attendee  Information, PDF Ticketing and Email Function with <a href='https://mage-people.com/product/mage-woo-event-booking-manager-pro/' target='_blank'>Event Manager Pro</a>", 'mage-eventpress' ); ?></p>
					<?php endif;
						if ( ! mep_check_plugin_installed( 'woocommerce-event-manager-addon-global-quantity/global-quantity.php' ) ): ?>
                            <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Setup Event Common QTY of All Ticket Type get <a href='https://mage-people.com/product/global-common-qty-addon-for-event-manager' target='_blank'>Global QTY Addon</a>", 'mage-eventpress' ); ?></p>
						<?php endif;
						if ( ! mep_check_plugin_installed( 'woocommerce-event-manager-addon-membership-price/membership-price.php' ) ): ?>
                            <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Special Price Option for each user type or membership get <a href='https://mage-people.com/product/membership-pricing-for-event-manager-plugin' target='_blank'>Membership Pricing Addon</a>", 'mage-eventpress' ); ?></p>
						<?php endif;
						if ( ! mep_check_plugin_installed( 'woocommerce-event-manager-min-max-quantity-addon/mep_min_max_qty.php' ) ): ?>
                            <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Set maximum/minimum qty buying option with <a href='https://mage-people.com/product/event-max-min-quantity-limiting-addon-for-woocommerce-event-manager' target='_blank'>Max/Min Qty Addon</a>", 'mage-eventpress' ); ?></p>
						<?php endif; ?>
                    <p class="event_meta_help_txtx"><span class="dashicons dashicons-info"></span> <?php _e( "Read Documentation <a href='https://docs.mage-people.com/woocommerce-event-manager/' target='_blank'>Read Documentation</a>", 'mage-eventpress' ); ?></p>
                </section>
				<?php
			}
		}
		new MPWEM_Ticket_Price_Settings();
	}