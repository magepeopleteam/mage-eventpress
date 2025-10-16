<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Tax_Settings' ) ) {
		class MPWEM_Tax_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', array( $this, 'tax_settings' ) );
			}

			public function tax_settings( $event_id ) {
				$event_label = mep_get_option( 'mep_event_label', 'general_setting_sec', 'Events' );
				if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) { ?>
                    <div class="mp_tab_item" data-tab-item="#mp_event_tax_settings">
                        <h3><?php echo esc_html( $event_label );
								esc_html_e( 'Tax Settings', 'mage-eventpress' ); ?></h3>
                        <p><?php esc_html_e( 'Configure Your Settings Here', 'mage-eventpress' ) ?></p>
						<?php $this->mp_event_tax( $event_id ); ?>
						<?php do_action( 'mep_event_tab_after_tax_settings' ); ?>
                    </div>
				<?php }
			}

			public function mp_event_tax( $post_id ) {
				$values = get_post_custom( $post_id );
				if ( array_key_exists( '_tax_status', $values ) ) {
					$tx_status = $values['_tax_status'][0];
				} else {
					$tx_status = '';
				}
				if ( array_key_exists( '_tax_class', $values ) ) {
					$tx_class = $values['_tax_class'][0];
				} else {
					$tx_class = '';
				}
				?>
                <section class="bg-light">
                    <h2><?php esc_html_e( 'Tax Settings', 'mage-eventpress' ) ?></h2>
                    <span><?php esc_html_e( 'Configure Event Tax', 'mage-eventpress' ) ?></span>
                </section>
                <section>
                    <label class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Tax status', 'mage-eventpress' ); ?></span></h2>
                            <span><?php _e( 'Tax status', 'mage-eventpress' ); ?></span>
                        </div>
                        <select class="" name="_tax_status">
                            <option value="taxable" <?php echo ( $tx_status == 'taxable' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Taxable', 'mage-eventpress' ); ?></option>
                            <option value="shipping" <?php echo ( $tx_status == 'shipping' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Shipping only', 'mage-eventpress' ); ?></option>
                            <option value="none" <?php echo ( $tx_status == 'none' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'None', 'mage-eventpress' ); ?></option>
                        </select>
                    </label>
                </section>
                <section>
                    <label class="mpev-label">
                        <div>
                            <h2><span><?php esc_html_e( 'Tax class', 'mage-eventpress' ); ?></span></h2>
                            <span><?php _e( 'In order to add a new tax class, please go to WooCommerce -> Settings -> Tax Area', 'mage-eventpress' ); ?></span>
                        </div>
                        <select class="" name="_tax_class">
                            <option value="standard" <?php echo ( $tx_class == 'standard' ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Standard', 'mage-eventpress' ); ?></option>
							<?php mep_get_all_tax_list( $tx_class ); ?>
                        </select>
                    </label>
                </section>
				<?php
			}
		}
		new MPWEM_Tax_Settings();
	}