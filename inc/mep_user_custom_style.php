<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('wp_head', 'mep_user_custom_styles', 10, 999);
function mep_user_custom_styles()
{
    // mep_base_text_color
    $base_color                 = mep_get_option('mep_base_color', 'style_setting_sec', '#ffbe30');
    $base_text_color            = mep_get_option('mep_base_text_color', 'style_setting_sec', '#ffffff');

    $label_bg_color             = mep_get_option('mep_title_bg_color', 'style_setting_sec', '#ffbe30');
    $label_text_color           = mep_get_option('mep_title_text_color', 'style_setting_sec', '#ffffff');
    $cart_btn_bg_color          = mep_get_option('mep_cart_btn_bg_color', 'style_setting_sec', '#ffbe30');
    $cart_btn_txt_color         = mep_get_option('mep_cart_btn_text_color', 'style_setting_sec', '#ffffff');

    $calender_btn_bg_color      = mep_get_option('mep_calender_btn_bg_color', 'style_setting_sec', '#ffbe30');
    $calender_btn_txt_color     = mep_get_option('mep_calender_btn_text_color', 'style_setting_sec', '#ffffff');
   
    $faq_label_bg_color         = mep_get_option('mep_faq_title_bg_color', 'style_setting_sec', '#ffbe30');
    $faq_label_text_color       = mep_get_option('mep_faq_title_text_color', 'style_setting_sec', '#ffffff');
   
    $royal_primary_bg_color     = mep_get_option('mep_royal_primary_bg_color', 'style_setting_sec', '');
    $royal_secondary_bg_color   = mep_get_option('mep_royal_secondary_bg_color', 'style_setting_sec', '');
    $royal_icons_bg_color       = mep_get_option('mep_royal_icons_bg_color', 'style_setting_sec', '');
    $royal_border_color         = mep_get_option('mep_royal_border_color', 'style_setting_sec', '');
    $royal_text_color           = mep_get_option('mep_royal_text_color', 'style_setting_sec', '');

    ?>
    <style>
        .mep-event-faq-part .ex-sec-title.faq-title-section{background: <?php echo esc_attr($faq_label_bg_color); ?>;color: <?php echo $faq_label_text_color; ?>;}
		.pagination_area button[class*="defaultButton_xs"],
        .list_with_filter_section [class*="defaultButton"],
        div.item_hover_effect a{
            background-color:<?php echo esc_attr($base_color); ?>;
        }
        div.item_hover_effect a:hover{
            color:<?php echo esc_attr($base_color); ?>;background-color:#fff;border:1px solid <?php echo esc_attr($base_color); ?>;
        }
        ul.mp_event_more_date_list li:hover{
            background-color:<?php echo esc_attr($base_color); ?>;
        }
        .mep-default-sidrbar-events-schedule ul li i, .mep-ev-start-date, h3.mep_list_date i, .df-ico i, .mep-default-sidrbar-address ul li i, .mep-default-sidrbar-social ul li a, button.mep-cat-control, .pagination-sec a, .mep-tem3-title-sec.mep_single_date_btn {
            background: <?php echo esc_attr($base_color); ?>;
            color: <?php echo esc_attr($base_text_color); ?>
        }
        .mep-default-sidrbar-meta .fa-list-alt,.mep-list-footer ul li i {
            background: transparent;
            color: <?php echo esc_attr($base_color); ?>;
        }
        .mep_more_date_btn{
            border: 1px solid <?php echo esc_attr($base_color); ?>;
            background: transparent;
            color: <?php echo esc_attr($base_color); ?>;
        }
        .mep-default-sidrbar-meta p a{
            color: <?php echo esc_attr($base_color); ?>;
        }
        .mep_more_date_btn:before{
            background: <?php echo esc_attr($base_color); ?>;
            border-color: <?php echo esc_attr($base_color); ?>;
        }
        .mep-default-sidrbar-events-schedule h3 i, .mep_event_list .mep_list_date, .mep-event-theme-1 .mep-social-share li a, .mep-template-2-hamza .mep-social-share li a {
            color: <?php echo esc_attr($base_color); ?>;
        }

        .mep_event_list_item:hover {
            border-color: <?php echo esc_attr($base_color); ?>;
        }

        .mep_event_list_item .mep-list-header:before, .mep_event_grid_item .mep-list-header:before {
            border-color: <?php echo esc_attr($base_color); ?>;
        }


        /*Cart sec Label Style*/
        .mep-default-feature-cart-sec h3, .mep-event-theme-1 h3.ex-sec-title, .mep-tem3-mid-sec h3.ex-sec-title, .mep-tem3-title-sec, 
		.royal_theme h3.ex-sec-title,
		.mep-events-wrapper .royal_theme table.mep_event_add_cart_table,
		.vanilla_theme.mep-default-theme div.mep-default-feature-date, 
		.vanilla_theme.mep-default-theme div.mep-default-feature-time, 
		.vanilla_theme.mep-default-theme div.mep-default-feature-location,
		.vanilla_theme h3.ex-sec-title,
		.vanilla_theme div.df-dtl h3,
		.vanilla_theme div.df-dtl p, .ex-sec-title, .mep_everyday_date_secs{
            background: <?php echo esc_attr($label_bg_color); ?>;
            color: <?php echo esc_attr($label_text_color); ?>;
        }
        .mpwemasp_ticket_area .mep_everyday_date_secs{ background: <?php echo esc_attr($label_bg_color); ?>; color: <?php echo esc_attr($label_text_color); ?>; }
        /*FAQ Sec Style*/
        .mep-default-feature-faq-sec h4, .tmep-emplate-3-faq-sec .mep-event-faq-part h4 {
            background: <?php echo esc_attr($faq_label_bg_color); ?>;
            color: <?php echo esc_attr($faq_label_text_color); ?>;
        }

        /* h3.ex-sec-title{
            background: <?php echo esc_attr($base_color); ?>;
        }

        .ex-sec-title{
            background: <?php echo esc_attr($base_color); ?>;
            color: <?php echo esc_attr($label_text_color); ?>;
        } */

        /*Cart Button Style*/
		button.mpwemasp_get_sp,
        .mep-default-feature-cart-sec button.single_add_to_cart_button.button.alt.btn-mep-event-cart, .mep-event-theme-1 .btn-mep-event-cart, .mep-template-2-hamza .btn-mep-event-cart, .mep-tem3-mid-sec .btn-mep-event-cart, .button.button-default.woocommerce.button.alt.button.alt.btn-mep-event-cart {
            background: <?php echo esc_attr($cart_btn_bg_color); ?>;
            color: <?php echo esc_attr($cart_btn_txt_color); ?> !important;
            border-color: <?php echo esc_attr($cart_btn_bg_color); ?>;
        }

        /*Calender Button Style*/
        .mep-default-sidrbar-calender-btn a, .mep-event-theme-1 .mep-add-calender, .mep-template-2-hamza .mep-add-calender, .mep-tem3-mid-sec .mep-add-calender, #mep_add_calender_button, .royal_theme #mep_add_calender_button, .royal_theme ul#mep_add_calender_links li a {
            background: <?php echo esc_attr($calender_btn_bg_color); ?>;
            color: <?php echo esc_attr($calender_btn_txt_color); ?> !important;
            border-color: <?php echo esc_attr($calender_btn_bg_color); ?>;
        }
        #mep_add_calender_button,
        ul#mep_add_calender_links li a{
            background: <?php echo esc_attr($calender_btn_bg_color); ?>;
            color: <?php echo esc_attr($calender_btn_txt_color); ?> !important;
        }
        /**/
        .mep_list_event_details p.read-more a{
            color: <?php echo esc_attr($base_color); ?>;
        }
		.royal_theme .mep-royal-header,
		.royal_theme .mep-default-feature-content{
		    background: <?php echo esc_attr($royal_primary_bg_color); ?>;
		}
		.royal_theme .mep-default-col-1,
		.royal_theme .mep-default-col-2{
			background-color: <?php echo esc_attr($royal_secondary_bg_color); ?>;
		}
		.royal_theme .df-ico i,
		.royal_theme .mep-default-sidrbar-social ul li a,
		.royal_theme .mep-default-sidrbar-events-schedule ul li i,
        .royal_theme .mep-default-sidrbar-meta .fa-list-alt,
        .royal_theme .mep-default-sidrbar-events-schedule h3 i{
			background-color: <?php echo esc_attr($royal_icons_bg_color); ?>;
		}
		.royal_theme .mep-default-title,
		.royal_theme div.df-dtl h3,
		.royal_theme .mep-default-col-2,
		.mep-events-wrapper .royal_theme table td{
			border-color: <?php echo esc_attr($royal_border_color); ?>;
		}
		.royal_theme .mep-default-title h2, 
		.royal_theme .section-heading h2,
		.royal_theme div.df-dtl h3,
		.royal_theme div.df-dtl p,
		.royal_theme .mep-default-sidrbar-map h3, 
		.royal_theme .mep-default-sidrbar-events-schedule h3, 
		.royal_theme h4.mep-cart-table-title,
		.royal_theme table td,
		.royal_theme div.mep-default-feature-content p, 
		.royal_theme div.mep-default-feature-content ul, 
		.royal_theme div.mep-default-feature-content ul li,
		.royal_theme .mep-default-sidrbar-meta p, 
		.royal_theme .mep-default-sidrbar-meta p a, 
		.royal_theme .mep-default-sidrbar-events-schedule h3,
		.royal_theme .mep-default-sidrbar-events-schedule,
		.royal_theme .mep-default-sidrbar-price-seat h5{
            color: <?php echo esc_attr($royal_text_color); ?>;
        }
        <?php do_action('mep_event_user_custom_styling'); ?>
    </style>
    <?php
}
