<section class="mageStyle">
    <div class="unlimited_section">
        <div class="mage_container">
            <div class="mage_row">
                <div class="col_6 sd_12 alignCenter">
                        <h3 class="mep_welcome_title">WooCommerce Event Manager</h3>
                        <p>WooCommerce Event Manager Plugin for WordPress is the complete event solution. All major functions are available in this plugin which is needed in an Event booking website.</p>
                        <p>It uses WooCommerce to take payment, which provides freedom for using popular payment getaway via WooCommerce. This plugin supports all WordPress version and can be used to create any types of any types of events.</p>
                        <div class="mep_welcome_btns_wrapper">
                            <a href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/#mage_product_price" class="mep_welcome_btn mep_bn_btn">Buy Now</a>
                            <a href="https://event.mage-people.com/" class="mep_welcome_btn mep_vd_btn">View Demo</a>
                            <a href="https://docs.mage-people.com/woocommerce-event-manager/" class="mep_welcome_btn mep_doc_btn">Documentation</a>
                        </div>
                </div>
                <div class="col_6 sd_12">
                    <img src="<?php echo plugin_dir_url(__DIR__) . 'welcome/' ?>img/ullimited_img.png" alt="unlimited" class="mep_welcome_pro_img"/>
                </div>
            </div>
            <div class="mage-row">
                <div class="mep_welcome_pro_features_wrapper">
                    <div class="col_12">
                        <div class="mep_welcome_pro_features_heading">
                            <h3>Pro Features You'll Love</h3>
                            <div class="mep_welcome_divider"></div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>Attendee Management</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                            <i class="fab fa-wpforms"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>Attendee Custom Form</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>PDF Ticketing</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                            <i class="far fa-envelope"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>Custom Emailing</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>Attendee Edit Feature</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>Attendee CSV Export</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                            <i class="far fa-file-alt"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>Report Overview</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                            <i class="fas fa-palette"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>Custom Style Settings</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col_4 sd_12">
                        <div class="mep_welcome_pro_feature">
                        <div class="mep_welcome_pro_feature_icon">
                             <i class="fas fa-language"></i>
                        </div>
                            <div class="mep_welcome_pro_feature_title">
                                <h4>Translation Settings</h4>
                            </div>
                        </div>
                    </div>                                                            
                </div>
            </div>
        </div>
    </div>
    <div class="widget mep_welcome_pro_addons_wrapper">
        <div class="mage_container">
            <div class="mage_row">
                <div class="col_12">
                    <div class="mep_welcome_pro_addons_heading">
                        <h3 class="textCenter">WooCommerce Event Manager <span class="mep-color-l-r">Addons</span></h3>
                        <div class="mep_welcome_divider"></div>
                    </div>
                    
                    <div class="justifyBetween mep_welcome_pro_addons">
                        <?php
                        $url = 'https://vaincode.com/update/addon-list.json';
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        $data = curl_exec($curl);
                        curl_close($curl);
                        $obj = json_decode($data, true);

                        if (is_array($obj) && sizeof($obj) > 0) {
                        ?>
                            <div class="addon_list_sec">
                                <ul class="mep_addon_list">
                                    <?php foreach ($obj as $list) { ?>
                                        <li>
                                            <img src="<?php echo esc_url($list['banner']); ?>" alt="">
                                            <h3><?php echo esc_html($list['name']); ?></h3>
                                            <h4>Prices start at <?php echo esc_html($list['price']); ?></h4>
                                            <a href="<?php echo esc_url($list['url']); ?>" target="_blank"><?php echo esc_html($list['btn_txt']); ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="getPro">
            <div class="mage_row">
                <div class="col_12 mep_welcome_dflex">
                    <h2>Get Pro and Others Available Addon to get all these exciting features</h2>
                    <div class="justifyCenter">
                        <a href="https://mage-people.com/product/mage-woo-event-booking-manager-pro/" target="_blank" class="prePostDash customButton allCenter">Buy Now</a>
                    </div>
                </div>
            </div>
    </div>
</section>