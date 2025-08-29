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
				add_action( 'mp_event_all_in_tab_item', array( $this, 'ticket_settings' ) );
			}

			public function ticket_settings( $event_id ) {
				$mep_reg_status = get_post_meta( $event_id, 'mep_reg_status', true );
				$mep_reg_status = $mep_reg_status ? $mep_reg_status : 'on';
				?>
                <div class="mp_tab_item" data-tab-item="#mp_ticket_type_pricing">
                    <h3><?php esc_html_e( 'Ticket & Pricing Settings', 'mage-eventpress' ) ?></h3>
                    <p><?php esc_html_e( 'Configure Your Ticket & Pricing Settings Here', 'mage-eventpress' ) ?></p>
                    <section class="bg-light">
                        <h2><?php esc_html_e( 'General Settings', 'mage-eventpress' ) ?></h2>
                        <span><?php esc_html_e( 'Configure Event Locations and Virtual Venues', 'mage-eventpress' ) ?></span>
                    </section>
					<?php do_action( 'mep_event_tab_before_ticket_pricing', $event_id ); ?>

					<?php $this->event_view_shortcode( $event_id ); ?>
					<?php $this->registration_on_off( $event_id ); ?>
                    <div id='mep_ticket_type_setting_sec' style="display:<?php echo esc_attr( $mep_reg_status == 'on' ? 'block' : 'none' ); ?>">
                        <section class="bg-light" style="margin-top: 20px;">
                            <h2><?php esc_html_e( 'Ticket Type List', 'mage-eventpress' ) ?></h2>
                            <span><?php esc_html_e( 'Configure Ticket Type', 'mage-eventpress' ) ?></span>
                        </section>
						<?php $this->mep_event_ticket_type( $event_id ); ?>
                        <section class="bg-light" style="margin-top: 20px;">
                            <h2><?php esc_html_e( 'Extra Service Area', 'mage-eventpress' ) ?></h2>
                            <span><?php esc_html_e( 'Configure Extra Service', 'mage-eventpress' ) ?></span>
                        </section>
						<?php $this->mep_event_extra_price_option( $event_id ); ?>
						<?php $this->mep_event_pro_purchase_notice(); ?>
                    </div>
					<?php do_action( 'mep_event_tab_after_ticket_pricing' ); ?>
                </div>
				<?php
			}

			public function event_view_shortcode( $post_id ) {
				?>
                <section>
                    <label class="mpev-label">
                        <div style="width: 50%;">
                            <h2><?php _e( 'Add To Cart Form Shortcode', 'mage-eventpress' ); ?></h2>
                            <span><?php _e( 'If you want to display the ticket type list with an add-to-cart button on any post or page of your website, simply copy the shortcode and paste it where desired.', 'mage-eventpress' ); ?></span>
                        </div>
                        <code> [event-add-cart-section event="<?php echo $post_id; ?>"]</code>
                    </label>
                </section>
				<?php
			}

			public function registration_on_off( $post_id ) {
				wp_nonce_field( 'mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce' );
				$mep_reg_status = get_post_meta( $post_id, 'mep_reg_status', true );
				$mep_reg_status = $mep_reg_status ? $mep_reg_status : 'on';
				?>
                <section>
                    <div class="mpev-label">
                        <div>
                            <h2><?php esc_html_e( 'Registration Off/On:', 'mage-eventpress' ); ?></h2>
                            <span><?php esc_html_e( 'Registration Off/On:', 'mage-eventpress' ); ?></span>
                        </div>
                        <label class="mpev-switch">
                            <input type="checkbox" name="mep_reg_status" value="<?php echo esc_attr( $mep_reg_status ); ?>" <?php echo esc_attr( ( $mep_reg_status == 'on' ) ? 'checked' : '' ); ?> data-collapse-target="#mep_ticket_type_setting_sec" data-close-target="#" data-toggle-values="on,off">
                            <span class="mpev-slider"></span>
                        </label>
                    </div>
                </section>
				<?php
			}

			public function mep_event_ticket_type( $post_id ) {
				$col_display           = get_post_meta( $post_id, 'mep_show_advance_col_status', true );
				$col_display           = $col_display ? $col_display : 'off';
				$mep_event_ticket_type = get_post_meta( $post_id, 'mep_event_ticket_type', true );
				wp_nonce_field( 'mep_event_ticket_type_nonce', 'mep_event_ticket_type_nonce' );
				wp_nonce_field( 'mep_event_reg_btn_nonce', 'mep_event_reg_btn_nonce' );
				if ( $col_display == 'on' ) {
					$css_value = 'table-cell';
				} else {
					$css_value = 'none';
				}
				?>
                <section>
                    <div class="mpev-label">
                        <div>
                            <h2><?php esc_html_e( 'Show Advanced Column:', 'mage-eventpress' ); ?></h2>
                            <span><?php esc_html_e( 'Ticket Type List', 'mage-eventpress' ); ?></span>
                        </div>
                        <label class="mpev-switch">
                            <input type="checkbox" name="mep_show_advance_col_status" value="<?php echo esc_attr( $col_display ); ?>" <?php echo esc_attr( ( $col_display == 'on' ) ? 'checked' : '' ); ?> data-collapse-target="#hide_column" data-toggle-values="on,off">
                            <span class="mpev-slider"></span>
                        </label>
                    </div>
                </section>
                <style>
					.mep_hide_on_load {
						display: <?php echo $css_value; ?>;
					}
                </style>
				<?php do_action( 'mep_add_category_display', $post_id ); ?>
                <section class="mp_ticket_type_table">
                    <div class="mp_ticket_type_table_auto">
                        <table id="repeatable-fieldset-one-t">
                            <thead>
                            <tr>
                                <th style="min-width: 60px;" title="<?php esc_attr_e( 'Ticket Type Name', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Ticket', 'mage-eventpress' ); ?></th>
                                <th style="min-width: 60px;" title="<?php esc_attr_e( 'Ticket Type Details', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Short Desc.', 'mage-eventpress' ); ?></th>
                                <th style="min-width: 40px;" title="<?php esc_attr_e( 'Ticket Price', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></th>
								<?php do_action( 'mep_pricing_table_head_after_price_col' ); ?>
                                <th style="min-width: 40px;" title="<?php esc_attr_e( 'Available Qty', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Capacity', 'mage-eventpress' ); ?>
                                </th>
                                <th class='mep_hide_on_load' style="min-width: 40px;" title="<?php esc_attr_e( 'Default Qty', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Default Qty', 'mage-eventpress' ); ?></th>
                                <th class='mep_hide_on_load' style="min-width: 40px;" title="<?php esc_attr_e( 'Reserve Qty', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Reserve Qty', 'mage-eventpress' ); ?>
									<?php do_action( 'add_extra_field_icon', $post_id ); ?>
                                </th>
								<?php do_action( 'mep_add_extra_column' ,$post_id); ?>
                                <th class='mep_hide_on_load' style="min-width: 60px;" title="<?php esc_attr_e( 'Sale End Date', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Sale End Date', 'mage-eventpress' ); ?></th>
                                <th class='mep_hide_on_load' style="min-width: 60px;" title="<?php esc_attr_e( 'Sale End Time', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Sale End Time', 'mage-eventpress' ); ?></th>
                                <th style="min-width: 60px;" title="<?php esc_attr_e( 'Qty Box Type', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Qty Box', 'mage-eventpress' ); ?></th>
                                <th style="min-width: 60px;"><?php esc_html_e( 'Action', 'mage-eventpress' ); ?>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="mp_event_type_sortable">
							<?php
								if ( $mep_event_ticket_type ) :
									$count = 0;
									foreach ( $mep_event_ticket_type as $field ) {
										$qty_t_type         = array_key_exists( 'option_qty_t_type', $field ) ? esc_attr( $field['option_qty_t_type'] ) : 'inputbox';
										$option_details     = array_key_exists( 'option_details_t', $field ) ? esc_attr( $field['option_details_t'] ) : '';
										$option_name        = array_key_exists( 'option_name_t', $field ) ? esc_attr( $field['option_name_t'] ) : '';
										$option_name_text   = preg_replace( "/[{}()<>+ ]/", '_', $option_name ) . '_' . $post_id;
										$option_price       = array_key_exists( 'option_price_t', $field ) ? esc_attr( $field['option_price_t'] ) : '';
										$option_qty         = array_key_exists( 'option_qty_t', $field ) ? esc_attr( $field['option_qty_t'] ) : 0;
										$option_default_qty = array_key_exists( 'option_default_qty_t', $field ) ? esc_attr( $field['option_default_qty_t'] ) : 0;
										$option_rsv_qty     = array_key_exists( 'option_rsv_t', $field ) ? esc_attr( $field['option_rsv_t'] ) : 0;
										$count ++;
										?>
                                        <tr class="data_required">
                                            <td>
                                                <input type="hidden" name="hidden_option_name_t[]" value="<?php echo esc_attr( $option_name_text ); ?>"/>
                                                <input data-required="" type="text" class="mp_formControl" name="option_name_t[]" placeholder="Ex: Adult" value="<?php echo esc_attr( $option_name ); ?>"/>
                                            </td>
                                            <td><input type="text" class="mp_formControl" name="option_details_t[]" placeholder="" value="<?php echo esc_attr( $option_details ); ?>"/></td>
                                            <td><input type="number" size="4" pattern="[0-9]*" step="0.001" class="mp_formControl" name="option_price_t[]" placeholder="Ex: 10" value="<?php echo esc_attr( $option_price ); ?>"/></td>
											<?php do_action( 'mep_pricing_table_data_after_price_col', $field, $post_id ); ?>
                                            <td><input type="number" size="4" pattern="[0-9]*" step="1" class="mp_formControl" name="option_qty_t[]" placeholder="Ex: 500" value="<?php echo esc_attr( $option_qty ) ?>"/></td>
                                            <td class='mep_hide_on_load'><input type="number" size="2" pattern="[0-9]*" step="1" class="mp_formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value="<?php echo esc_attr( $option_default_qty ) ?>"/></td>
                                            <td class='mep_hide_on_load'><input type="number" class="mp_formControl" name="option_rsv_t[]" placeholder="Ex: 5" value="<?php echo esc_attr( $option_rsv_qty ); ?>"/></td>
											<?php do_action( 'mep_add_extra_input_box', $field, $count,$post_id ) ?>
                                            <td class='mep_hide_on_load'>
											<span class="sell_expire_date">
												<input type="date" id="ticket_sale_start_date" class="mp_formControl" value='<?php if ( array_key_exists( 'option_sale_end_date_t', $field ) && $field['option_sale_end_date_t'] != '' ) {
													echo esc_attr( date( 'Y-m-d', strtotime( $field['option_sale_end_date_t'] ) ) );
												} ?>' name="option_sale_end_date[]"/>
											</span>
                                            </td>
                                            <td class='mep_hide_on_load'>
											<span class="sell_expire_date">
												<input type="time" id="ticket_sale_start_time" class="mp_formControl" value='<?php if ( array_key_exists( 'option_sale_end_date_t', $field ) && $field['option_sale_end_date_t'] != '' ) {
													echo esc_attr( date( 'H:i', strtotime( $field['option_sale_end_date_t'] ) ) );
												} ?>' name="option_sale_end_time[]"/>
											</span>
                                            </td>
                                            <td>
                                                <select name="option_qty_t_type[]" class='mp_formControl'>
                                                    <option value="inputbox" <?php if ( $qty_t_type == 'inputbox' ) {
														echo esc_attr( "Selected" );
													} ?>><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
                                                    <option value="dropdown" <?php if ( $qty_t_type == 'dropdown' ) {
														echo esc_attr( "Selected" );
													} ?>><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="mp_event_remove_move">
                                                    <button class="button remove-row-t" type="button"><span class="dashicons dashicons-trash"></span></button>
                                                    <div class="mp_event_type_sortable_button"><span class="dashicons dashicons-move"></span></div>
                                                </div>
                                            </td>
                                        </tr>
										<?php
									}
								else :
									// show a blank one
								endif;
							?>
                            <!-- empty hidden one for jQuery -->
                            <tr class="empty-row-t screen-reader-text data_required">
                                <td><input data-required="" type="text" class="mp_formControl" name="option_name_t[]" placeholder="Ex: Adult"/></td>
                                <td><input type="text" class="mp_formControl" name="option_details_t[]" placeholder=""/></td>
                                <td><input type="number" size="4" pattern="[0-9]*" class="mp_formControl" step="0.001" name="option_price_t[]" placeholder="Ex: 10" value=""/></td>
								<?php do_action( 'mep_pricing_table_empty_after_price_col' ); ?>
                                <td><input type="number" size="4" pattern="[0-9]*" step="1" class="mp_formControl" name="option_qty_t[]" placeholder="Ex: 15" value=""/></td>
                                <td class='mep_hide_on_load'><input type="number" size="2" pattern="[0-9]*" class="mp_formControl" name="option_default_qty_t[]" placeholder="Ex: 1" value=""/></td>
								<?php $option_rsv_t = '<td class="mep_hide_on_load"><input type="number" class="mp_formControl" name="option_rsv_t[]" placeholder="Ex: 5" value=""/></td>'; ?>
								<?php echo apply_filters( 'mep_add_field_to_ticket_type', mep_esc_html( $option_rsv_t ) ); ?>
								<?php do_action( 'mep_add_extra_column_empty' ,$post_id); ?>
                                <td class="mep_hide_on_load">
                                    <div class="sell_expire_date">
                                        <input type="date" id="ticket_sale_start_date" value='' name="option_sale_end_date[]"/>
                                    </div>
                                </td>
                                <td class="mep_hide_on_load">
                                    <div class="sell_expire_date">
                                        <input type="time" id="ticket_sale_start_time" value='' name="option_sale_end_time[]"/>
                                    </div>
                                </td>
                                <td>
                                    <select name="option_qty_t_type[]" class='mp_formControl'>
                                        <option value=''><?php esc_html_e( 'Please Select', 'mage-eventpress' ); ?></option>
                                        <option value="inputbox"><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
                                        <option value="dropdown"><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
                                    </select>
                                </td>
                                <td>
                                    <button class="button remove-row-t" type="button"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <button id="add-row-t" class="button" type="button"><i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Add New Ticket Type', 'mage-eventpress' ); ?></button>
                </section>
				<?php
			}

			public function mep_event_extra_price_option( $post_id ) {
				$mep_events_extra_prices = get_post_meta( $post_id, 'mep_events_extra_prices', true );
				?>
                <section>
                    <p><?php esc_html_e( 'Extra Service as Product that you can sell and it is not included on event package', 'mage-eventpress' ); ?></p>
                    <br>
                    <div class="mp_ticket_type_table">
                        <table id="repeatable-fieldset-one">
                            <thead>
                            <tr>
                                <th title="<?php esc_attr_e( 'Extra Service Name', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Name', 'mage-eventpress' ); ?></th>
                                <th title="<?php esc_attr_e( 'Extra Service Price', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></th>
                                <th title="<?php esc_attr_e( 'Available Qty', 'mage-eventpress' ); ?>"><?php esc_html_e( 'Available Qty', 'mage-eventpress' ); ?></th>
                                <th title="<?php esc_attr_e( 'Qty Box Type', 'mage-eventpress' ); ?>" style="min-width: 140px;"><?php esc_html_e( 'Qty Box', 'mage-eventpress' ); ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="mp_event_type_sortable">
							<?php
								if ( $mep_events_extra_prices ) :
									foreach ( $mep_events_extra_prices as $field ) {
										$qty_type = array_key_exists( 'option_qty_type', $field ) ? esc_attr( $field['option_qty_type'] ) : 'inputbox';
										?>
                                        <tr>
                                            <td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap" value="<?php if ( $field['option_name'] != '' ) {
													echo esc_attr( $field['option_name'] );
												} ?>"/></td>
                                            <td><input type="number" step="0.001" class="mp_formControl" name="option_price[]" placeholder="Ex: 10" value="<?php if ( $field['option_price'] != '' ) {
													echo esc_attr( $field['option_price'] );
												} else {
													echo '';
												} ?>"/></td>
                                            <td>
                                                <input type="number" class="mp_formControl" name="option_qty[]"
                                                       placeholder="Ex: 100"
                                                       value="<?php echo esc_attr( ( $field['option_qty'] != '' ) ? $field['option_qty'] : '' ); ?>"/>
                                            </td>
                                            <td align="center">
                                                <select name="option_qty_type[]" class='mp_formControl'>
                                                    <option value="inputbox" <?php if ( $qty_type == 'inputbox' ) {
														echo esc_attr( "Selected" );
													} ?>><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
                                                    <option value="dropdown" <?php if ( $qty_type == 'dropdown' ) {
														echo esc_attr( "Selected" );
													} ?>><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="mp_event_remove_move">
                                                    <button class="button remove-row" type="button"><i class="fas fa-trash"></i></button>
                                                    <div class="mp_event_type_sortable_button"><i class="fas fa-grip-vertical"></i></div>
                                                </div>
                                            </td>
                                        </tr>
										<?php
									}
								else :
									// show a blank one
								endif;
							?>
                            <!-- empty hidden one for jQuery -->
                            <tr class="empty-row screen-reader-text">
                                <td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap"/></td>
                                <td><input type="number" class="mp_formControl" step="0.001" name="option_price[]" placeholder="Ex: 10" value=""/></td>
                                <td><input type="number" class="mp_formControl" name="option_qty[]" placeholder="Ex: 100" value=""/></td>
                                <td><select name="option_qty_type[]" class='mp_formControl'>
                                        <option value=""><?php esc_html_e( 'Please Select Type', 'mage-eventpress' ); ?></option>
                                        <option value="inputbox"><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
                                        <option value="dropdown"><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
                                    </select></td>
                                <td>
                                    <button class="button remove-row"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <button id="add-row" class="button"><i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Add Extra Price', 'mage-eventpress' ); ?></button>
                </section>
				<?php
			}

			public function mep_event_pro_purchase_notice() {
				?>
                <section class="bg-light" style="margin-top: 20px;">
                    <h2><?php esc_html_e( 'Documentaion Links', 'mage-eventpress' ) ?></h2>
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