<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( ! class_exists( 'MEP_Quick_Setup' ) ) {
    class MEP_Quick_Setup {
        public function __construct() {
            add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ), 10, 1 );
            add_action( 'admin_menu', array( $this, 'quick_setup_menu' ) );
        }
        public function add_admin_scripts() {
            wp_enqueue_style( 'mp_plugin_global', MEP_PLUGIN_URL . '/assets/helper/mp_style/mp_style.css', array(), time() );
            wp_enqueue_script( 'mp_plugin_global', MEP_PLUGIN_URL . '/assets/helper/mp_style/mp_script.js', array( 'jquery' ), time(), true );
            wp_enqueue_script( 'mp_admin_settings', MEP_PLUGIN_URL . '/assets/admin/mp_admin_settings.js', array( 'jquery' ), time(), true );
            wp_enqueue_style( 'mp_admin_settings', MEP_PLUGIN_URL . '/assets/admin/mp_admin_settings.css', array(), time() );
            wp_enqueue_style( 'mp_font_awesome', '//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css', array(), '5.15.4' );
        }
        public function quick_setup_menu() {
            $status = MP_Global_Function::check_woocommerce();
            if ( $status == 1 ) {
                add_submenu_page( 'edit.php?post_type=mep_events', __( 'Quick Setup', 'mage-eventpress' ), '<span style="color:#10dd10">' . esc_html__( 'Quick Setup', 'mage-eventpress' ) . '</span>', 'manage_options', 'mep_event_quick_setup_page', array( $this, 'quick_setup' ) );
                add_submenu_page( 'mep_events', esc_html__( 'Quick Setup', 'mage-eventpress' ), '<span style="color:#10dd10">' . esc_html__( 'Quick Setup', 'mage-eventpress' ) . '</span>', 'manage_options', 'mep_event_quick_setup_page', array( $this, 'quick_setup' ) );
            } else {
                add_menu_page( esc_html__( 'Events', 'mage-eventpress' ), esc_html__( 'Events', 'mage-eventpress' ), 'manage_options', 'mep_events', array( $this, 'quick_setup' ), 'dashicons-calendar-al', 6 );
                add_submenu_page( 'mep_events', esc_html__( 'Quick Setup', 'mage-eventpress' ), '<span style="color:#10dd17">' . esc_html__( 'Quick Setup', 'mage-eventpress' ) . '</span>', 'manage_options', 'mep_event_quick_setup_page', array( $this, 'quick_setup' ) );
            }
        }
        public function quick_setup() {
            if ( isset( $_POST['active_woo_btn'] ) ) {
                ?>
                <script>
                    dLoaderBody();
                </script>
                <?php
                activate_plugin( 'woocommerce/woocommerce.php' );
                ?>
                <script>
                    let mep_admin_location = window.location.href;
                    mep_admin_location = mep_admin_location.replace('admin.php?page=mep_events', 'edit.php?post_type=mep_events&page=mep_event_quick_setup_page');
                    window.location.href = mep_admin_location;
                </script>
                <?php
            }
            if ( isset( $_POST['install_and_active_woo_btn'] ) ) {
                echo '<div style="display:none">';
                include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
                $plugin = 'woocommerce';
                $api    = plugins_api( 'plugin_information', array(
                    'slug'   => $plugin,
                    'fields' => array(
                        'short_description' => false,
                        'sections'          => false,
                        'requires'          => false,
                        'rating'            => false,
                        'ratings'           => false,
                        'downloaded'        => false,
                        'last_updated'      => false,
                        'added'             => false,
                        'tags'              => false,
                        'compatibility'     => false,
                        'homepage'          => false,
                        'donate_link'       => false,
                    ),
                ) );
                //includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
                include_once( ABSPATH . 'wp-admin/includes/file.php' );
                include_once( ABSPATH . 'wp-admin/includes/misc.php' );
                include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
                $woocommerce_plugin = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
                $woocommerce_plugin->install( $api->download_link );
                activate_plugin( 'woocommerce/woocommerce.php' );
                echo '</div>';
                ?>
                <script>
                    let mep_admin_location = window.location.href;
                    mep_admin_location = mep_admin_location.replace('admin.php?page=mep_events', 'edit.php?post_type=mep_events&page=mep_event_quick_setup_page');
                    window.location.href = mep_admin_location;
                </script>
                <?php
            }
            if ( isset( $_POST['finish_quick_setup'] ) ) {
                $label                       = isset( $_POST['mep_event_label'] ) ? sanitize_text_field( $_POST['mep_event_label'] ) : 'mage-eventpress';
                $slug                        = isset( $_POST['mep_event_slug'] ) ? sanitize_text_field( $_POST['mep_event_slug'] ) : 'mage-eventpress';
                $general_settings_data       = get_option( 'general_setting_sec' );
                $update_general_settings_arr = [
                    'mep_event_label' => $label,
                    'mep_event_slug'  => $slug
                ];
                $new_general_settings_data   = is_array( $general_settings_data ) ? array_replace( $general_settings_data, $update_general_settings_arr ) : $update_general_settings_arr;
                update_option( 'general_setting_sec', $new_general_settings_data );
                flush_rewrite_rules();
                wp_redirect( admin_url( 'edit.php?post_type=mep_events' ) );
            }
            ?>
            <div id="mp_quick_setup" class="mpStyle">
                <form method="post" action="">
                    <div class="mpTabsNext">
                        <div class="tabListsNext _max_700_mAuto">
                            <div data-tabs-target-next="#mpwpb_qs_welcome" class="tabItemNext">
                                <h4 class="circleIcon">1</h4>
                                <h5 class="circleTitle"><?php esc_html_e( 'Welcome', 'mage-eventpress' ); ?></h5>
                            </div>
                            <div data-tabs-target-next="#mpwpb_qs_general" class="tabItemNext">
                                <h4 class="circleIcon">2</h4>
                                <h5 class="circleTitle"><?php esc_html_e( 'General', 'mage-eventpress' ); ?></h5>
                            </div>
                            <div data-tabs-target-next="#mpwpb_qs_done" class="tabItemNext">
                                <h4 class="circleIcon">3</h4>
                                <h5 class="circleTitle"><?php esc_html_e( 'Done', 'mage-eventpress' ); ?></h5>
                            </div>
                        </div>
                        <div class="tabsContentNext _infoLayout_mT">
                            <?php
                            $this->setup_welcome_content();
                            $this->setup_general_content();
                            $this->setup_content_done();
                            ?>
                        </div>
                        <div class="justifyBetween">
                            <button type="button" class="mpBtn nextTab_prev"><span>&longleftarrow;<?php esc_html_e( 'Previous', 'mage-eventpress' ); ?></span></button>
                            <div></div>
                            <button type="button" class="themeButton nextTab_next"><span><?php esc_html_e( 'Next', 'mage-eventpress' ); ?>&longrightarrow;</span></button>
                        </div>
                    </div>
                </form>
            </div>
            <?php
        }
        public function setup_welcome_content() {
            $status = MP_Global_Function::check_woocommerce();
            ?>
            <div data-tabs-next="#mpwpb_qs_welcome">
                <h2><?php esc_html_e( 'Event Manager and Tickets Selling Plugin', 'mage-eventpress' ); ?></h2>
                <p class="mTB_xs"><?php esc_html_e( 'Thanks for choosing Event Manager and Tickets Selling Plugin for WooCommerce for your site, Please go step by step and choose some options to get started.', 'mage-eventpress' ); ?></p>
                <div class="_dLayout_mT_alignCenter justifyBetween">
                    <h5>
                        <?php if ( $status == 1 ) {
                            esc_html_e( 'Woocommerce already installed and activated', 'mage-eventpress' );
                        } elseif ( $status == 0 ) {
                            esc_html_e( 'Woocommerce need to install and active', 'mage-eventpress' );
                        } else {
                            esc_html_e( 'Woocommerce already install , please activate it', 'mage-eventpress' );
                        } ?>
                    </h5>
                    <?php if ( $status == 1 ) { ?>
                        <h5><span class="fas fa-check-circle textSuccess"></span></h5>
                    <?php } elseif ( $status == 0 ) { ?>
                        <button class="warningButton" type="submit" name="install_and_active_woo_btn"><?php esc_html_e( 'Install & Active Now', 'mage-eventpress' ); ?></button>
                    <?php } else { ?>
                        <button class="themeButton" type="submit" name="active_woo_btn"><?php esc_html_e( 'Active Now', 'mage-eventpress' ); ?></button>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
        public function setup_general_content() {
            $label        = MP_Global_Function::get_settings('general_setting_sec', 'mep_event_label', 'Events');
            $slug        = MP_Global_Function::get_settings('general_setting_sec', 'mep_event_slug', 'mage-eventpress');
            ?>
            <div data-tabs-next="#mpwpb_qs_general">
                <div class="section">
                    <h2><?php esc_html_e( 'General settings', 'mage-eventpress' ); ?></h2>
                    <p class="mTB_xs"><?php esc_html_e( 'Choose some general option.', 'mage-eventpress' ); ?></p>
                    <div class="_dLayout_mT">
                        <label class="fullWidth">
                            <span class="min_200"><?php esc_html_e( 'Events Label:', 'mage-eventpress' ); ?></span>
                            <input type="text" class="formControl" name="mep_event_label" value='<?php echo esc_attr( $label ); ?>'/>
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e( 'It will change the Events post type label on the entire plugin.', 'mage-eventpress' ); ?>
                        </i>
                        <div class="divider"></div>
                        <label class="fullWidth">
                            <span class="min_200"><?php esc_html_e( 'Events Slug:', 'mage-eventpress' ); ?></span>
                            <input type="text" class="formControl" name="mep_event_slug" value='<?php echo esc_attr( $slug ); ?>'/>
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e( 'It will change the Events slug on the entire plugin. Remember after changing this slug you need to flush permalinks. Just go to Settings->Permalinks hit the Save Settings button', 'mage-eventpress' ); ?>
                        </i>
                    </div>
                </div>
            </div>
            <?php
        }
        public function setup_content_done() {
            ?>
            <div data-tabs-next="#mpwpb_qs_done">
                <h2><?php esc_html_e( 'Finalize Setup', 'mage-eventpress' ); ?></h2>
                <p class="mTB_xs"><?php esc_html_e( 'You are about to Finish & Save mage-eventpress For Woocommerce Plugin setup process', 'mage-eventpress' ); ?></p>
                <div class="mT allCenter">
                    <button type="submit" name="finish_quick_setup" class="themeButton"><?php esc_html_e( 'Finish & Save', 'mage-eventpress' ); ?></button>
                </div>
            </div>
            <?php
        }
    }
    new MEP_Quick_Setup();
}