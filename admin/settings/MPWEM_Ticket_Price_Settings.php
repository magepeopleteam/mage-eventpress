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
				$reg_status        = is_array($event_infos) && array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
				$active_reg_status = $reg_status == 'on' ? 'mActive' : '';
				$display_rsvp = $reg_status == 'rsvp' ? '' : 'display:none;';
				?>
                <div class="mpwem_style mp_tab_item mpwem_ticket_pricing_settings" data-tab-item="#mpwem_ticket_pricing_settings">
					<?php
					$payment_opts = get_option('payment_setting_sec', []);
					$woo_enabled = isset($payment_opts['mep_enable_woocommerce_payment']) && $payment_opts['mep_enable_woocommerce_payment'] === 'on';
					$paypal_enabled = isset($payment_opts['mep_paypal_enable']) && $payment_opts['mep_paypal_enable'] === 'on';
					$stripe_enabled = isset($payment_opts['mep_stripe_enable']) && $payment_opts['mep_stripe_enable'] === 'on';
					$show_payment_warning = !$woo_enabled && !$paypal_enabled && !$stripe_enabled;
					$wc_active = MPWEM_Global_Function::has_woocommerce();
					?>
					<div class="mpwem-ticket-warnings <?php echo esc_attr( $active_reg_status ); ?>" data-collapse="#mep_reg_status" style="margin-bottom: 20px;">
						<?php if ( ! $wc_active ) : ?>
							<div class="mpwem-woo-warning-notice" style="background: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; border-radius: var(--mpwem-radius);">
								<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
									<div style="flex: 1; min-width: 250px;">
										<strong style="display: block; font-size: 14px; margin-bottom: 5px;"><i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i><?php esc_html_e( 'Notice: WooCommerce is Not Activated', 'mage-eventpress' ); ?></strong>
										<span style="font-size: 13px;"><?php esc_html_e( 'You can explore and manage ticket types, prices, and related settings here. However, you cannot save the event type as "Ticket-Selling" without WooCommerce. To actually use the "Ticket-Selling" event type and allow ticket sales, you must install and activate WooCommerce.', 'mage-eventpress' ); ?></span>
									</div>
									<div>
										<button type="button" class="button button-primary mep-install-wc-trigger" style="white-space: nowrap;"><?php echo file_exists( WP_PLUGIN_DIR . "/woocommerce/woocommerce.php" ) ? esc_html__( "Activate WooCommerce Now", "mage-eventpress" ) : esc_html__( "Install & Activate Now", "mage-eventpress" ); ?></button>
									</div>
								</div>
							</div>
						<?php elseif ( $show_payment_warning ) : ?>
							<div class="mpwem-payment-warning" style="background: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; border-radius: var(--mpwem-radius); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
								<div>
									<strong style="display: block; font-size: 14px; margin-bottom: 5px;"><i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i><?php esc_html_e( 'No Payment Method Enabled', 'mage-eventpress' ); ?></strong>
									<span style="font-size: 13px;"><?php esc_html_e( 'You have selected to sell tickets, but no payment methods (WooCommerce or Custom Payments) are currently enabled. Please configure a payment method to accept payments.', 'mage-eventpress' ); ?></span>
								</div>
								<div>
									<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mep_events&page=mep_event_settings_page' ) ); ?>" target="_blank" class="button button-primary" style="white-space: nowrap;"><?php esc_html_e( 'Configure Payments', 'mage-eventpress' ); ?></a>
								</div>
							</div>
						<?php endif; ?>
					</div>
					
					<?php $this->setting_head( $event_id, $event_infos ); ?>
                    <div class="<?php echo esc_attr( $active_reg_status ); ?>" data-collapse="#mep_reg_status">
						<?php $this->ticket_setting( $event_id, $event_infos ); ?>
						<?php $this->ex_service_setting( $event_id ); ?>
                    </div>
					
					<div class="mpwem-rsvp-settings-area" style="<?php echo esc_attr( $display_rsvp ); ?>">
						<?php $this->rsvp_setting( $event_id, $event_infos ); ?>
					</div>

					<?php $this->mep_event_pro_purchase_notice(); ?>
					
					<!-- WooCommerce Install/Activate Modal -->
					<?php if ( ! $wc_active ) :
						$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
						$modal_desc   = $is_installed
							? esc_html__( 'WooCommerce is already installed but not active. Click the button below to activate it right now.', 'mage-eventpress' )
							: esc_html__( 'WooCommerce is required to process payments. We will securely download, install, and activate it for you right now.', 'mage-eventpress' );
						$modal_btn    = $is_installed
							? esc_html__( 'Activate WooCommerce Now', 'mage-eventpress' )
							: esc_html__( 'Install & Activate Now', 'mage-eventpress' );
					?>
					<div id="mep-wc-install-modal" style="display:none; position:fixed; z-index:999999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
						<div style="background:#fff; border-radius:12px; width:480px; max-width:92%; box-shadow:0 10px 40px rgba(0,0,0,0.35); overflow:hidden;">
							<div style="padding:22px 25px; border-bottom:1px solid #e2e4e7; display:flex; justify-content:space-between; align-items:center; background:#f8f9fa;">
								<h3 style="margin:0; font-size:18px; color:#2c3338;"><?php esc_html_e( 'WooCommerce Setup', 'mage-eventpress' ); ?></h3>
								<button type="button" class="mep-close-modal" style="background:none; border:none; font-size:26px; cursor:pointer; color:#666; line-height:1; padding:0;">&times;</button>
							</div>
							<div style="padding:32px 28px; text-align:center;">
								<span class="dashicons dashicons-cart" style="font-size:72px; width:72px; height:72px; color:#96588a; margin-bottom:18px; display:block; margin-left:auto; margin-right:auto;"></span>
								<p style="font-size:15px; color:#444; margin-bottom:28px; line-height:1.6;"><?php echo esc_html( $modal_desc ); ?></p>
								<div id="mep-wc-install-progress" style="display:none; margin-bottom:22px; font-weight:500; font-size:15px; background:#f0f0f1; padding:14px; border-radius:8px;">
									<span class="spinner is-active" style="float:none; margin:0 10px 0 0;"></span>
									<span id="mep-wc-install-status" style="color:#2271b1;"><?php esc_html_e( 'Working...', 'mage-eventpress' ); ?></span>
								</div>
								<button type="button" id="mep-wc-start-install" style="background:linear-gradient(135deg,#96588a,#6b3d6b); color:#fff; border:none; padding:14px 0; font-size:16px; font-weight:700; border-radius:8px; cursor:pointer; width:100%; box-shadow:0 4px 12px rgba(150,88,138,0.4); transition:all 0.2s;">
									<?php echo esc_html( $modal_btn ); ?>
								</button>
							</div>
						</div>
					</div>
					<script>
					jQuery(document).ready(function($) {
						$(document).on('click', '.mep-install-wc-trigger', function(e) {
							e.preventDefault();
							$('#mep-wc-install-modal').css('display', 'flex').hide().fadeIn(200);
						});
						$(document).on('click', '.mep-close-modal', function() {
							$('#mep-wc-install-modal').fadeOut(200);
						});
						$('#mep-wc-start-install').click(function() {
							var $btn = $(this);
							var $progress = $('#mep-wc-install-progress');
							var $status = $('#mep-wc-install-status');
							$btn.prop('disabled', true);
							$btn.css('opacity', '0.6');
							$progress.fadeIn(200);
							$status.text( <?php echo json_encode( __( "Please wait, configuring WooCommerce...", "mage-eventpress" ) ); ?> );
							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "mep_install_activate_wc",
									nonce: "<?php echo wp_create_nonce('mep_install_wc'); ?>"
								},
								success: function(response) {
									if (response.success) {
										$status.css('color', '#0f5132');
										$status.text( <?php echo json_encode( __( "Successfully Activated! Reloading page...", "mage-eventpress" ) ); ?> );
										setTimeout(function() {
											location.reload();
										}, 1500);
									} else {
										$status.css('color', '#dc3545');
										$status.text( <?php echo json_encode( __( "Error: ", "mage-eventpress" ) ); ?> + (response.data || "Unknown error") );
										$btn.prop("disabled", false);
										$btn.css('opacity', '1');
									}
								},
								error: function() {
									$status.css('color', '#dc3545');
									$status.text( <?php echo json_encode( __( "A network error occurred. Please try again.", "mage-eventpress" ) ); ?> );
									$btn.prop("disabled", false);
									$btn.css('opacity', '1');
								}
							});
						});
					});
					</script>
					<?php endif; ?>
                </div>
				<?php
			}
			public function setting_head( $event_id, $event_infos ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$is_custom_event_edit = is_admin()
					&& isset( $_GET['page'] )
					&& sanitize_key( wp_unslash( $_GET['page'] ) ) === 'mpwem_event_edit';
				?>
                <div class="_layout_default_xs_mp_zero mpwem-ticket-settings-head" style="border-radius: var(--mpwem-radius);">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Ticket & Pricing Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Your Ticket & Pricing Settings Here', 'mage-eventpress' ); ?></span>
                    </div>
					<?php
						do_action( 'mep_event_tab_before_ticket_pricing', $event_id );
						if ( ! $is_custom_event_edit ) {
							$this->event_view_shortcode( $event_id );
						}
						do_action( 'mep_add_category_display', $event_id );
						$this->registration_on_off( $event_id, $event_infos );
						if ( ! $is_custom_event_edit ) {
							do_action( 'mpwem_after_registration_on_off', $event_id );
						}
					?>
                </div>
				<?php
			}
			public function ticket_setting( $event_id, $event_infos ) {
				$ticket_infos        = is_array($event_infos) && array_key_exists( 'mep_event_ticket_type', $event_infos ) ? $event_infos['mep_event_ticket_type'] : [];
				$show_advance_column = is_array($event_infos) && array_key_exists( 'mep_show_advance_col_status', $event_infos ) ? $event_infos['mep_show_advance_col_status'] : 'off';
				$active_category     = $show_advance_column == 'on' ? 'mActive' : '';
				$ticket_infos          = array_key_exists( 'mep_event_ticket_type', $event_infos ) ? $event_infos['mep_event_ticket_type'] : [];
					$early_bird_status     = array_key_exists( 'mep_enable_early_bird_status', $event_infos ) ? $event_infos['mep_enable_early_bird_status'] : 'off';
					$advanced_col_status   = array_key_exists( 'mep_show_advanced_column', $event_infos ) ? $event_infos['mep_show_advanced_column'] : 'off';
					$global_qty_status     = array_key_exists( 'enable_global_qty', $event_infos ) ? $event_infos['enable_global_qty'] : 'off';
					$active_category       = $early_bird_status == 'on' ? 'mActive' : 'mpwem-ticket-col-hidden';
					$capacity_col_status   = $global_qty_status == 'on' ? 'mpwem-ticket-col-hidden' : '';
					$event_label         = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				//echo '<pre>';print_r($ticket_infos);echo '</pre>';
				?>
                <div class="_mt"></div>
                <div class="_layout_default_xs_mp_zero mpwem-ticket-editor-section">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Ticket Type Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Ticket Type', 'mage-eventpress' ); ?></span>
                    </div>
					<?php
						do_action( 'mpwem_before_ticket_type', $event_id );
						$this->show_advance_column( $event_id, $event_infos );
					?>
                    <div class="_padding_bt mpwem_settings_area">
                        <div class="_ov_auto mpwem-ticket-table-wrap">
                            <table class="mpwem_ticket_table mpwem-ticket-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Ticket Type', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></th>
	                                        <th class="mpwem-ticket-card__capacity <?php echo esc_attr( $capacity_col_status ); ?>"><?php esc_html_e( 'Capacity', 'mage-eventpress' ); ?></th>
                                        <th><?php esc_html_e( 'Qty Box', 'mage-eventpress' ); ?></th>
                                        <th class="<?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column"><?php esc_html_e( 'Default Qty', 'mage-eventpress' ); ?></th>
                                        <th class="<?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column"><?php esc_html_e( 'Reserve Qty', 'mage-eventpress' ); ?></th>
                                        <?php do_action( 'mpwem_add_extra_column', $event_id ); ?>
                                        <th class="mpwem-ticket-table__sale-period <?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_enable_early_bird_status"><?php esc_html_e( 'Sale Period', 'mage-eventpress' ); ?></th>
                                        <th class="mpwem-ticket-table__actions"><?php esc_html_e( 'Actions', 'mage-eventpress' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="mpwem-ticket-cards-container mpwem_sortable_area mpwem_item_insert">
                                    <?php
                                        if ( is_array($ticket_infos) && sizeof( $ticket_infos ) > 0 ) {
                                            foreach ( $ticket_infos as $ticket_info ) {
	                                                $this->ticket_info( $event_id, $active_category, $advanced_col_status, $ticket_info, $global_qty_status );
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mpwem-ticket-footer">
                            <?php MPWEM_Custom_Layout::add_new_button( __( 'Add New Ticket Type', 'mage-eventpress' ), 'mpwem_add_item', 'mpwem-add-ticket-btn', 'fas fa-plus' ); ?>
                        </div>

                        <div class="mpwem_hidden_content">
                            <table>
                                <tbody class="mpwem_hidden_item">
                                    <?php $this->ticket_info( $event_id, $active_category, $advanced_col_status, [], $global_qty_status ); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
				<?php
			}
				public function ticket_info( $event_id, $active_category, $advanced_col_status, $ticket_info = [], $global_qty_status = 'off' ) {
				$qty_t_type           = is_array($ticket_info) && array_key_exists( 'option_qty_t_type', $ticket_info ) ? $ticket_info['option_qty_t_type'] : 'inputbox';
				$option_details       = is_array($ticket_info) && array_key_exists( 'option_details_t', $ticket_info ) ? $ticket_info['option_details_t'] : '';
				$option_name          = is_array($ticket_info) && array_key_exists( 'option_name_t', $ticket_info ) ? $ticket_info['option_name_t'] : '';
				$option_name_text     = preg_replace( "/[{}()<>+ ]/", '_', $option_name ) . '_' . $event_id;
				$option_price         = is_array($ticket_info) && array_key_exists( 'option_price_t', $ticket_info ) ? $ticket_info['option_price_t'] : '';
				$option_qty           = is_array($ticket_info) && array_key_exists( 'option_qty_t', $ticket_info ) ? $ticket_info['option_qty_t'] : 0;
				$option_default_qty   = is_array($ticket_info) && array_key_exists( 'option_default_qty_t', $ticket_info ) ? $ticket_info['option_default_qty_t'] : 0;
				$option_rsv_qty       = is_array($ticket_info) && array_key_exists( 'option_rsv_t', $ticket_info ) ? $ticket_info['option_rsv_t'] : 0;
				$sale_end             = is_array($ticket_info) && array_key_exists( 'option_sale_end_date_t', $ticket_info ) ? $ticket_info['option_sale_end_date_t'] : '';
				$option_ticket_enable = is_array($ticket_info) && array_key_exists( 'option_ticket_enable', $ticket_info ) && $ticket_info['option_ticket_enable'] ? $ticket_info['option_ticket_enable'] : 'yes';
				$checked              = $option_ticket_enable == 'yes' ? 'checked' : '';
				$ticket_sold          = 0;
				if ( $option_name ) {
					$filter_args['post_id']        = $event_id;
					$filter_args['ea_ticket_type'] = $option_name;
					$ticket_sold                   = MPWEM_Query::attendee_query( $filter_args )->post_count;
				}
				?>
                <tr class="mpwem-ticket-card mpwem-ticket-row mpwem_remove_area data_required <?php echo esc_attr( $option_ticket_enable !== 'yes'  && $ticket_sold>0? 'disable_row' : '' ); ?>">
                    <td class="mpwem-ticket-card__group mpwem-ticket-card__identity">
                        <label class="mpwem-card-label"><?php esc_html_e( 'Ticket Type', 'mage-eventpress' ); ?></label>
                        <div class="mpwem-ticket-card__field">
                            <input type="hidden" name="hidden_option_name_t[]" value="<?php echo esc_attr( $option_name_text ); ?>"/>
                            <?php if ( $ticket_sold > 0 ) { ?>
                                <input type="hidden" name="option_name_t[]" value="<?php echo esc_attr( $option_name ); ?>"/>
                                <div class="mpwem-ticket-card__locked-name"><?php echo esc_html( $option_name ); ?></div>
                            <?php } else { ?>
                                <input data-required="" type="text" class="mpwem-card-input mpwem-card-input--large name_validation" name="option_name_t[]" placeholder="Ticket Name (Ex: Adult)" value="<?php echo esc_attr( $option_name ); ?>"/>
                            <?php } ?>
                        </div>
                        <div class="mpwem-ticket-card__field mpwem-ticket-card__description <?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column">
                            <input type="text" class="mpwem-card-input" name="option_details_t[]" placeholder="Add short description" value="<?php echo esc_attr( $option_details ); ?>"/>
                        </div>
                    </td>

                    <td class="mpwem-ticket-card__group mpwem-ticket-card__price">
                        <label class="mpwem-card-label"><?php esc_html_e( 'Price', 'mage-eventpress' ); ?></label>
                        <div class="mpwem-card-input-wrapper mpwem-card-input-wrapper--currency">
                            <input type="number" size="4" pattern="[0-9]*" step="0.001" class="mpwem-card-input" name="option_price_t[]" placeholder="0.00" value="<?php echo esc_attr( $option_price ); ?>"/>
                        </div>
                    </td>

	                    <td class="mpwem-ticket-card__group mpwem-ticket-card__capacity <?php echo esc_attr( $global_qty_status == 'on' ? 'mpwem-ticket-col-hidden' : '' ); ?>">
                        <label class="mpwem-card-label"><?php esc_html_e( 'Capacity', 'mage-eventpress' ); ?></label>
                        <input type="number" size="4" pattern="[0-9]*" step="1" class="mpwem-card-input" name="option_qty_t[]" placeholder="100" value="<?php echo esc_attr( $option_qty ) ?>"/>
                    </td>

					<td class="mpwem-ticket-card__group mpwem-ticket-card__qty-box">
						<label class="mpwem-card-label"><?php esc_html_e( 'Qty Box', 'mage-eventpress' ); ?></label>
						<select class="mpwem-card-input" name="option_qty_t_type[]">
							<option value="inputbox" <?php selected( $qty_t_type, 'inputbox' ); ?>><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
							<option value="dropdown" <?php selected( $qty_t_type, 'dropdown' ); ?>><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
						</select>
					</td>

					<td class="mpwem-ticket-card__group mpwem-ticket-card__default-qty <?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column">
						<label class="mpwem-card-label"><?php esc_html_e( 'Default Qty', 'mage-eventpress' ); ?></label>
						<input type="number" size="2" pattern="[0-9]*" step="1" class="mpwem-card-input mpwem-card-input--small" name="option_default_qty_t[]" placeholder="1" value="<?php echo esc_attr( $option_default_qty ); ?>"/>
					</td>

					<td class="mpwem-ticket-card__group mpwem-ticket-card__advance-qty <?php echo esc_attr( $advanced_col_status === 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#mep_show_advanced_column">
						<label class="mpwem-card-label"><?php esc_html_e( 'Reserve Qty', 'mage-eventpress' ); ?></label>
						<input type="number" class="mpwem-card-input mpwem-card-input--small" name="option_rsv_t[]" placeholder="0" value="<?php echo esc_attr( $option_rsv_qty ); ?>"/>
					</td>

                    <?php do_action( 'mpwem_add_extra_input_box', $event_id, $ticket_info ); ?>

                    <td class="mpwem-ticket-card__group mpwem-ticket-card__sale-period <?php echo esc_attr( $active_category ); ?>" data-collapse="#mep_enable_early_bird_status">
                        <label class="mpwem-card-label"><?php esc_html_e( 'Sale Period', 'mage-eventpress' ); ?></label>
                        <div class="mpwem-card-row mpwem-card-row--date">
                            <?php do_action( 'mpwem_add_sale_period_input_box', $event_id, $ticket_info ); ?>
                            <div class="mpwem-card-date-wrapper mpwem-card-date-wrapper--end">
                                <div style="font-size: 11px; color: #646970; margin-bottom: 0; font-weight: 600; text-transform: uppercase;text-align:left"><?php esc_html_e('End Date', 'mage-eventpress'); ?></div>
                                <div class="mpwem-card-date-field">
                                <?php MPWEM_Date_Settings::date_item( 'option_sale_end_date[]', $sale_end ); ?>
                                    <div class="mpwem-card-time-field">
                                        <input type="time" value="<?php echo esc_attr( strlen(trim((string)$sale_end)) > 10 ? date( 'H:i', strtotime( $sale_end ) ) : '' ); ?>" name="option_sale_end_time[]" class="formControl"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="mpwem-ticket-card__actions">
                        <div class="mpwem-ticket-card__action-btn mpwem_sortable_button" title="<?php esc_attr_e( 'Drag to reorder', 'mage-eventpress' ); ?>">
                            <span class="fas fa-ellipsis-v"></span>
                            <span class="fas fa-ellipsis-v"></span>
                        </div>
                        <button class="mpwem-ticket-card__action-btn mpwem-ticket-card__action-btn--danger mpwem_item_remove" type="button" title="<?php esc_attr_e( 'Delete', 'mage-eventpress' ); ?>">
                            <span class="fas fa-trash-alt"></span>
                        </button>
                        <?php if($ticket_sold > 0) { 
                            MPWEM_Custom_Layout::show_hide_button( 'option_ticket_enable[]', $option_ticket_enable );
                        } else { ?>
                            <input type="hidden" name="option_ticket_enable[]" value="yes">
                        <?php } ?>
                    </td>
                </tr>
				<?php
			}
			public function ex_service_setting( $event_id ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$ex_infos    = MPWEM_Global_Function::get_post_info( $event_id, 'mep_events_extra_prices', [] );
				?>
                <div class="_mt"></div>
                <div class="_layout_default_xs_mp_zero mpwem-extra-service-section">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'Extra Service Area', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure Extra Service Here. Extra Service as Product that you can sell and it is not included on event package', 'mage-eventpress' ); ?></span>
                    </div>
					<?php do_action( 'mpwem_before_ex_service', $event_id ); ?>
                    <div class="_padding_bt mpwem_settings_area">
                        
                        <div class="mpwem-ticket-cards-container mpwem_sortable_area mpwem_item_insert">
                            <?php
                                if ( is_array($ex_infos) && sizeof( $ex_infos ) > 0 ) {
                                    foreach ( $ex_infos as $ticket_info ) {
                                        $this->ex_info( $ticket_info );
                                    }
                                }
                            ?>
                        </div>

                        <div class="mpwem-ticket-footer">
                            <?php MPWEM_Custom_Layout::add_new_button( __( 'Add Extra Price', 'mage-eventpress' ), 'mpwem_add_item', 'mpwem-add-ticket-btn', 'fas fa-plus' ); ?>
                        </div>

                        <div class="mpwem_hidden_content">
                            <div class="mpwem_hidden_item">
                                <?php $this->ex_info(); ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function ex_info( $ticket_info = [] ) {
				$option_name  = is_array($ticket_info) && array_key_exists( 'option_name', $ticket_info ) ? $ticket_info['option_name'] : '';
				$option_price = is_array($ticket_info) && array_key_exists( 'option_price', $ticket_info ) ? $ticket_info['option_price'] : '';
				$option_qty   = is_array($ticket_info) && array_key_exists( 'option_qty', $ticket_info ) ? $ticket_info['option_qty'] : 0;
				$qty_t_type   = is_array($ticket_info) && array_key_exists( 'option_qty_type', $ticket_info ) ? $ticket_info['option_qty_type'] : 'inputbox';
				?>
                <div class="mpwem-ticket-card mpwem_remove_area data_required">
                    <div class="mpwem-ticket-card__main mpwem-ticket-card__main--extra-service">
                        <!-- Identity Group -->
                        <div class="mpwem-ticket-card__group mpwem-ticket-card__identity">
                            <div class="mpwem-ticket-card__field">
                                <label class="mpwem-card-label"><?php esc_html_e( 'Title', 'mage-eventpress' ); ?></label>
                                <input type="text" class="mpwem-card-input mpwem-card-input--large" name="option_name[]" placeholder="Service Name" value="<?php echo esc_attr( $option_name ); ?>"/>
                            </div>
                        </div>

                        <!-- Price Group -->
                        <div class="mpwem-ticket-card__group mpwem-ticket-card__price">
                            <label class="mpwem-card-label"><?php esc_html_e( 'PRICE', 'mage-eventpress' ); ?></label>
                            <div class="mpwem-card-input-wrapper mpwem-card-input-wrapper--currency">
                                <input type="number" class="mpwem-card-input" name="option_price[]" placeholder="0.00" value="<?php echo esc_attr( $option_price ); ?>"/>
                            </div>
                        </div>

                        <!-- Capacity Group -->
                        <div class="mpwem-ticket-card__group mpwem-ticket-card__capacity">
                            <label class="mpwem-card-label"><?php esc_html_e( 'AVAILABLE QTY', 'mage-eventpress' ); ?></label>
                            <input type="number" class="mpwem-card-input" name="option_qty[]" placeholder="100" value="<?php echo esc_attr( $option_qty ); ?>"/>
                        </div>

                        <!-- Qty Box Group -->
                        <div class="mpwem-ticket-card__group mpwem-ticket-card__qty-box">
                            <label class="mpwem-card-label"><?php esc_html_e( 'QTY BOX', 'mage-eventpress' ); ?></label>
                            <select class="mpwem-card-input" name="option_qty_type[]">
                                <option value="inputbox" <?php echo esc_attr( $qty_t_type == 'inputbox' ? 'Selected' : '' ); ?>><?php esc_html_e( 'Input Box', 'mage-eventpress' ); ?></option>
                                <option value="dropdown" <?php echo esc_attr( $qty_t_type == 'dropdown' ? 'Selected' : '' ); ?>><?php esc_html_e( 'Dropdown List', 'mage-eventpress' ); ?></option>
                            </select>
                        </div>

                        <!-- Spacer for Sale Period (not used in Extra Service usually) -->
                        <!-- <div class="mpwem-ticket-card__group"></div> -->

                        <!-- Action Group -->
                        <div class="mpwem-ticket-card__actions">
                            <div class="mpwem-ticket-card__action-btn mpwem_sortable_button" title="<?php esc_attr_e( 'Drag to reorder', 'mage-eventpress' ); ?>">
                                <span class="fas fa-ellipsis-v"></span>
                                <span class="fas fa-ellipsis-v"></span>
                            </div>
                            <button class="mpwem-ticket-card__action-btn mpwem-ticket-card__action-btn--danger mpwem_item_remove" type="button" title="<?php esc_attr_e( 'Delete', 'mage-eventpress' ); ?>">
                                <span class="fas fa-trash-alt"></span>
                            </button>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function event_view_shortcode( $post_id ) {
				self::render_shortcode_help( $post_id );
			}
			public static function render_shortcode_help( $post_id, $is_sidebar = false ) {
				$wrapper_classes = 'mpwem-shortcode-help';
				if ( $is_sidebar ) {
					$wrapper_classes .= ' mpwem-shortcode-help--sidebar';
				}
				?>
                <div class="<?php echo esc_attr( $wrapper_classes ); ?>">
                    <div class="mpwem-shortcode-help__row">
                        <span class="mpwem-shortcode-help__title"><?php esc_html_e( 'Add To Cart Form Shortcode', 'mage-eventpress' ); ?></span>
                        <code class="mpwem-shortcode-help__code">[event-add-cart-section event="<?php echo esc_html( $post_id ); ?>"]</code>
                    </div>
                    <p class="mpwem-shortcode-help__description"><?php esc_html_e( 'If you want to display the ticket type list with an add-to-cart button on any post or page of your website, simply copy the shortcode and paste it where desired.', 'mage-eventpress' ); ?></p>
                </div>
				<?php
			}
			public function registration_on_off( $event_id, $event_infos ) {
				$reg_status = is_array($event_infos) && array_key_exists( 'mep_reg_status', $event_infos ) ? $event_infos['mep_reg_status'] : 'on';
                $reg_status_msg_status = is_array($event_infos) && array_key_exists( 'mep_reg_status_show_msg', $event_infos ) ? $event_infos['mep_reg_status_show_msg'] : '';
                $reg_status_msg_txt = is_array($event_infos) && array_key_exists( 'mep_reg_status_show_msg_txt', $event_infos ) ? $event_infos['mep_reg_status_show_msg_txt'] : '';
				$is_custom_event_edit = is_admin()
					&& isset( $_GET['page'] )
					&& sanitize_key( wp_unslash( $_GET['page'] ) ) === 'mpwem_event_edit';

				$checked    = $reg_status == 'on' ? 'checked' : '';
                $reg_msg_checked    = $reg_status_msg_status == 'on' ? 'checked' : '';

				if ( ! $is_custom_event_edit ) {
					?>
					<div class="mpwem-ticket-registration-block">
						<div class=" _justify_between_align_center_wrap">
							<label><span class="_mr"><?php esc_html_e( 'Registration Off/On', 'mage-eventpress' ); ?></span></label>
							<?php MPWEM_Custom_Layout::switch_button( 'mep_reg_status', $checked ); ?>
						</div>
						<span class="label-text"><?php esc_html_e( 'Registration Off/On', 'mage-eventpress' ); ?></span>
					</div>
					<?php
				}
				?>
                <div class="_padding_bt reg_close_msg_dash mpwem-ticket-registration-message">
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
			public function show_advance_column( $event_id, $event_infos ) {
				$early_bird_status   = array_key_exists( 'mep_enable_early_bird_status', $event_infos ) ? $event_infos['mep_enable_early_bird_status'] : 'off';
				$global_qty_status   = array_key_exists( 'enable_global_qty', $event_infos ) ? $event_infos['enable_global_qty'] : 'off';
				$advanced_col_status = array_key_exists( 'mep_show_advanced_column', $event_infos ) ? $event_infos['mep_show_advanced_column'] : 'off';
				$global_qty_type     = array_key_exists( 'mep_gq_type', $event_infos ) ? $event_infos['mep_gq_type'] : 'global';
				$date_schedule_type  = array_key_exists( 'mep_enable_recurring', $event_infos ) ? $event_infos['mep_enable_recurring'] : 'no';
				$total_qty           = array_key_exists( 'mep_gq_total_seat', $event_infos ) ? $event_infos['mep_gq_total_seat'] : 0;
				$reserve_qty         = array_key_exists( 'mep_gq_total_resv_seat', $event_infos ) ? $event_infos['mep_gq_total_resv_seat'] : 0;

				$early_bird_checked = $early_bird_status == 'on' ? 'checked' : '';
				$global_qty_checked = $global_qty_status == 'on' ? 'checked' : '';
				$advanced_checked    = $advanced_col_status == 'on' ? 'checked' : '';
				?>
                <div class="mpwem-ticket-action-bar">
                    <div class="mpwem-ticket-action-bar__item">
                        <label><?php esc_html_e( 'ENABLE GLOBAL QTY', 'mage-eventpress' ); ?></label>
                        <?php MPWEM_Custom_Layout::switch_button( 'enable_global_qty', $global_qty_checked ); ?>
                    </div>
                    <div class="mpwem-ticket-action-bar__divider"></div>
                    <div class="mpwem-ticket-action-bar__item">
                        <label><?php esc_html_e( 'EARLY BIRD', 'mage-eventpress' ); ?></label>
                        <?php MPWEM_Custom_Layout::switch_button( 'mep_enable_early_bird_status', $early_bird_checked ); ?>
                    </div>
                    <div class="mpwem-ticket-action-bar__divider"></div>
                    <div class="mpwem-ticket-action-bar__item">
                        <label><?php esc_html_e( 'SHOW ADVANCED COLUMN', 'mage-eventpress' ); ?></label>
                        <?php MPWEM_Custom_Layout::switch_button( 'mep_show_advanced_column', $advanced_checked ); ?>
                    </div>
                </div>

                <!-- Global Settings Card -->
                <div class="mpwem-ticket-global-card <?php echo esc_attr( $global_qty_status == 'on' ? 'mActive' : 'mpwem-ticket-col-hidden' ); ?>" data-collapse="#enable_global_qty">
                    <div class="mpwem-ticket-global-card__content">
                        <div class="mpwem-ticket-card__group">
                            <label class="mpwem-card-label"><?php esc_html_e( 'GLOBAL QUANTITY TYPE?', 'mage-eventpress' ); ?></label>
                            <select class="mpwem-card-input" name="mep_gq_type">
                                <option value="date_wise" <?php selected( $global_qty_type, 'date_wise' ); ?>><?php esc_html_e( 'Particular Date Wise', 'mage-eventpress' ); ?></option>
                                <option value="global" <?php selected( $global_qty_type, 'global' ); ?>><?php esc_html_e( 'Full Event Base', 'mage-eventpress' ); ?></option>
                            </select>
                            <p class="mpwem-global-qty-warning <?php echo esc_attr( ( $global_qty_status === 'on' && $global_qty_type === 'date_wise' && $date_schedule_type === 'yes' ) ? 'is-visible' : '' ); ?>">
                                <span class="mpwem-global-qty-warning__text">
								    <?php esc_html_e( 'Please set the Global Qty in Date & Time Steps -> Particular Date Wise table.', 'mage-eventpress' ); ?>
                                </span>
                                <button type="button" class="button button-secondary mpwem-global-qty-warning__action" data-mpwem-open-particular-date-modal>
									<?php esc_html_e( 'Open Particular Date Table', 'mage-eventpress' ); ?>
                                </button>
                            </p>
                        </div>
                        <div class="mpwem-ticket-card__group <?php echo esc_attr( $global_qty_type === 'date_wise' ? 'mpwem-ticket-col-hidden' : '' ); ?>">
                            <label class="mpwem-card-label">
								<?php esc_html_e( 'TOTAL QTY', 'mage-eventpress' ); ?>
                                <span class="mpwem-info-tip mpwem-info-tip--mini" title="<?php echo esc_attr__( 'Enter The Total Seat of this event.', 'mage-eventpress' ); ?>">i</span>
                            </label>
                            <input type="number" class="mpwem-card-input" name="mep_gq_total_seat" placeholder="0" value="<?php echo esc_attr( $total_qty ); ?>"/>
                        </div>
                        <div class="mpwem-ticket-card__group <?php echo esc_attr( $global_qty_type === 'date_wise' ? 'mpwem-ticket-col-hidden' : '' ); ?>">
                            <label class="mpwem-card-label">
								<?php esc_html_e( 'RESERVE QTY', 'mage-eventpress' ); ?>
                                <span class="mpwem-info-tip mpwem-info-tip--mini" title="<?php echo esc_attr__( 'Enter The Total Reserve Seat Qty of this event.', 'mage-eventpress' ); ?>">i</span>
                            </label>
                            <input type="number" class="mpwem-card-input" name="mep_gq_total_resv_seat" placeholder="0" value="<?php echo esc_attr( $reserve_qty ); ?>"/>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function rsvp_setting( $event_id, $event_infos ) {
				$event_label = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_event_label', 'Events' );
				$name_label  = is_array($event_infos) && array_key_exists( 'mep_rsvp_name_label', $event_infos ) ? $event_infos['mep_rsvp_name_label'] : '';
				$email_label = is_array($event_infos) && array_key_exists( 'mep_rsvp_email_label', $event_infos ) ? $event_infos['mep_rsvp_email_label'] : '';
				$phone_label = is_array($event_infos) && array_key_exists( 'mep_rsvp_phone_label', $event_infos ) ? $event_infos['mep_rsvp_phone_label'] : '';
				$qty_label   = is_array($event_infos) && array_key_exists( 'mep_rsvp_qty_label', $event_infos ) ? $event_infos['mep_rsvp_qty_label'] : '';
				?>
                <div class="_mt"></div>
                <div class="_layout_default_xs_mp_zero mpwem-rsvp-settings-section">
                    <div class="_bg_light_padding">
                        <h4><?php echo esc_html( $event_label ) . ' ' . esc_html__( 'RSVP Settings', 'mage-eventpress' ); ?></h4>
                        <span class="_mp_zero"><?php esc_html_e( 'Configure RSVP Registration Field Labels', 'mage-eventpress' ); ?></span>
                    </div>
                    <div class="_padding_bt mpwem_settings_area">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="mpwem-ticket-card__group">
                                <label class="mpwem-card-label"><?php esc_html_e( 'Full Name Label', 'mage-eventpress' ); ?></label>
                                <input type="text" class="mpwem-card-input" name="mep_rsvp_name_label" placeholder="<?php esc_attr_e( 'Full Name', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $name_label ); ?>"/>
                            </div>
                            <div class="mpwem-ticket-card__group">
                                <label class="mpwem-card-label"><?php esc_html_e( 'Email Address Label', 'mage-eventpress' ); ?></label>
                                <input type="text" class="mpwem-card-input" name="mep_rsvp_email_label" placeholder="<?php esc_attr_e( 'Email Address', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $email_label ); ?>"/>
                            </div>
                            <div class="mpwem-ticket-card__group">
                                <label class="mpwem-card-label"><?php esc_html_e( 'Phone Number Label', 'mage-eventpress' ); ?></label>
                                <input type="text" class="mpwem-card-input" name="mep_rsvp_phone_label" placeholder="<?php esc_attr_e( 'Phone Number', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $phone_label ); ?>"/>
                            </div>
                            <div class="mpwem-ticket-card__group">
                                <label class="mpwem-card-label"><?php esc_html_e( 'Number of Seats Label', 'mage-eventpress' ); ?></label>
                                <input type="text" class="mpwem-card-input" name="mep_rsvp_qty_label" placeholder="<?php esc_attr_e( 'Number of Seats', 'mage-eventpress' ); ?>" value="<?php echo esc_attr( $qty_label ); ?>"/>
                            </div>
                        </div>
                        <p style="margin-top: 15px; font-size: 13px; color: #646970; margin-bottom: 0;">
                            <em><?php esc_html_e( 'Note: Leave blank to use the default labels.', 'mage-eventpress' ); ?></em>
                        </p>
                    </div>
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
