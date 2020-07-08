<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('wp_head', 'mep_user_custom_styles', 10, 999);
function mep_user_custom_styles()
{
    $base_color = mep_get_option('mep_base_color', 'style_setting_sec', '#ffbe30');
    $label_bg_color = mep_get_option('mep_title_bg_color', 'style_setting_sec', '#ffbe30');
    $label_text_color = mep_get_option('mep_title_text_color', 'style_setting_sec', '#ffffff');
    $cart_btn_bg_color = mep_get_option('mep_cart_btn_bg_color', 'style_setting_sec', '#ffbe30');
    $cart_btn_txt_color = mep_get_option('mep_cart_btn_text_color', 'style_setting_sec', '#ffffff');

    $calender_btn_bg_color = mep_get_option('mep_calender_btn_bg_color', 'style_setting_sec', '#ffbe30');
    $calender_btn_txt_color = mep_get_option('mep_calender_btn_text_color', 'style_setting_sec', '#ffffff');
    $faq_label_bg_color = mep_get_option('mep_faq_title_bg_color', 'style_setting_sec', '#ffbe30');
    $faq_label_text_color = mep_get_option('mep_faq_title_text_color', 'style_setting_sec', '#ffffff');

    ?>
    <style>
        .mep-default-sidrbar-events-schedule ul li i, .mep-ev-start-date, h3.mep_list_date i, .mep-list-footer ul li i, .df-ico i, .mep-default-sidrbar-meta i, .mep-default-sidrbar-address ul li i, .mep-default-sidrbar-social ul li a, .mep-tem3-title-sec {
            background: <?php echo $base_color; ?>;
        }

        .mep-default-sidrbar-events-schedule h3 i, .mep_event_list .mep_list_date, .mep-event-theme-1 .mep-social-share li a, .mep-template-2-hamza .mep-social-share li a {
            color: <?php echo $base_color; ?>;
        }

        .mep_event_list_item:hover {
            border-color: <?php echo $base_color; ?>;
        }

        .mep_event_list_item .mep-list-header:before, .mep_event_grid_item .mep-list-header:before {
            border-color: <?php echo $base_color; ?>;
        }


        /*Cart sec Label Style*/
        .mep-default-feature-cart-sec h3, .mep-event-theme-1 h3.ex-sec-title, .mep-tem3-mid-sec h3.ex-sec-title {
            background: <?php echo $label_bg_color; ?>;
            color: <?php echo $label_text_color; ?>;
        }

        /*FAQ Sec Style*/
        .mep-default-feature-faq-sec h4, .tmep-emplate-3-faq-sec .mep-event-faq-part h4 {
            background: <?php echo $faq_label_bg_color; ?>;
            color: <?php echo $faq_label_text_color; ?>;
        }

        h3.ex-sec-title {
            background: <?php echo $base_color; ?>;
        }

        /*Cart Button Style*/
        .mep-default-feature-cart-sec button.single_add_to_cart_button.button.alt.btn-mep-event-cart, .mep-event-theme-1 .btn-mep-event-cart, .mep-template-2-hamza .btn-mep-event-cart, .mep-tem3-mid-sec .btn-mep-event-cart {
            background: <?php echo $cart_btn_bg_color; ?>;
            color: <?php echo $cart_btn_txt_color; ?> !important;
            border-color: <?php echo $cart_btn_bg_color; ?>;
        }

        /*Calender Button Style*/
        .mep-default-sidrbar-calender-btn a, .mep-event-theme-1 .mep-add-calender, .mep-template-2-hamza .mep-add-calender, .mep-tem3-mid-sec .mep-add-calender, #mep_add_calender_button {
            background: <?php echo $calender_btn_bg_color; ?>;
            color: <?php echo $calender_btn_txt_color; ?> !important;
            border-color: <?php echo $calender_btn_bg_color; ?>;
        }
        #mep_add_calender_button,
        ul#mep_add_calender_links li a{
            background: <?php echo $base_color; ?>;
        }
        /**/
        .mep_list_event_details p.read-more a{
            color: <?php echo $base_color; ?>;
        }
        <?php do_action('mep_event_user_custom_styling'); ?>
    </style>
    <?php
}
