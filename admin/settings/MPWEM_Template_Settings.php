<?php
	/**
	 * @author Shahadat Hossain <raselsha@gmail.com>
	 * @version 1.0.0
	 * @copyright 2024 Mage People
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'MPWEM_Template_Settings' ) ) {
		class MPWEM_Template_Settings {
			public function __construct() {
				add_action( 'mpwem_event_tab_setting_item', [ $this, 'template_tab_content' ] );
			}
			public function template_tab_content( $post_id ) {
				$_current_template = MPWEM_Functions::get_details_template_name($post_id);
				$templates         = [];
				$themes            = mep_event_template_name();
				foreach ( $themes as $theme_file => $theme_name ) {
					$templates[] = [
						'name'  => $theme_name,
						'value' => $theme_file,
					];
				}
				?>
                <div class="mp_tab_item" data-tab-item="#mep_event_template">
                    <h3><?php esc_html_e( 'Template Settings', 'mage-eventpress' ); ?></h3>
                    <p><?php esc_html_e( 'Template Settings is a display template designed to showcase activity details on the event details page.', 'mage-eventpress' ); ?></p>
                    <section class="bg-light">
                        <h2><?php esc_html_e( 'Template Settings', 'mage-eventpress' ); ?></h2>
                        <span><?php esc_html_e( 'Select Template from below. Read documention to override template, click', 'mage-eventpress' ); ?> <a href="https://docs.mage-people.com/woocommerce-event-manager/how-to-override-and-change-event-templates/"><?php _e( 'Here', 'mage-eventpress' ) ?></a></span>
                    </section>
                    <section>
                        <div class="mep-template-section">
                            <input type="hidden" name="mep_event_template" value="<?php echo esc_attr( $_current_template ); ?>"/>
							<?php foreach ( $templates as $template ) {
								$image = preg_replace( '/\.php$/', '.webp', $template['value'] );
								?>
                                <div class="mep-template <?php echo $_current_template == $template['value'] ? 'active' : ''; ?>">
                                    <img src="<?php echo esc_attr( MPWEM_PLUGIN_URL . '/templates/screenshot/' .$image); ?>" data-mep-template="<?php echo $template['value']; ?>">
                                    <h5><?php echo $template['name']; ?></h5>
                                </div>
							<?php } ?>
                        </div>
                    </section>
                </div>
				<?php
			}
		}
		new MPWEM_Template_Settings();
	}