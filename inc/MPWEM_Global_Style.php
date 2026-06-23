<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPWEM_Global_Style' ) ) {
		class MPWEM_Global_Style {
			public function __construct() {
				add_action( 'wp_head', array( $this, 'add_global_style' ), 100 );
				add_action( 'admin_head', array( $this, 'add_global_style' ), 100 );
			}

			public function add_global_style() {
				$theme_color     = MPWEM_Global_Function::get_style_settings( 'mpev_primary_color', '#6046FF' );
				$secondary       = MPWEM_Global_Function::get_style_settings( 'mpev_secondary_color', '#F1F5FF' );
				$default_color   = MPWEM_Global_Function::get_style_settings( 'default_text_color', '#303030' );
				$alternate_color = MPWEM_Global_Function::get_style_settings( 'theme_alternate_color', '#fff' );
				$warning_color   = MPWEM_Global_Function::get_style_settings( 'warning_color', '#f8d7da' );
				$default_fs      = MPWEM_Global_Function::get_style_settings( 'default_font_size', '14' ) . 'px';
				$fs_h1           = MPWEM_Global_Function::get_style_settings( 'font_size_h1', '35' ) . 'px';
				$fs_h2           = MPWEM_Global_Function::get_style_settings( 'font_size_h2', '30' ) . 'px';
				$fs_h3           = MPWEM_Global_Function::get_style_settings( 'font_size_h3', '25' ) . 'px';
				$fs_h4           = MPWEM_Global_Function::get_style_settings( 'font_size_h4', '22' ) . 'px';
				$fs_h5           = MPWEM_Global_Function::get_style_settings( 'font_size_h5', '18' ) . 'px';
				$fs_h6           = MPWEM_Global_Function::get_style_settings( 'font_size_h6', '16' ) . 'px';
				$fs_label        = MPWEM_Global_Function::get_style_settings( 'font_size_label', '16' ) . 'px';
				$button_fs       = MPWEM_Global_Function::get_style_settings( 'button_font_size', '16' ) . 'px';
				$button_color    = MPWEM_Global_Function::get_style_settings( 'button_color', $alternate_color );
				$button_bg       = MPWEM_Global_Function::get_style_settings( 'button_bg', '#ea8125' );
				$section_bg      = MPWEM_Global_Function::get_style_settings( 'section_bg', '#FAFCFE' );
				?>
                <style>
					:root {
						--d_container_width: 1320px;
						--sidebar_left: 280px;
						--sidebar_right: 300px;
						--main_section: calc(100% - 300px);
						--dmpl: 40px;
						--dmp: 20px;
						--dmp_negetive: -20px;
						--dmp_xs: 10px;
						--dmp_xxs: 5px;
						--dmp_xs_negative: -10px;
						--dbrl: 10px;
						--dbr: 5px;
						--d_shadow: 0 0 2px #665F5F7A;
					}
					/*****Font size********/
					:root {
						--fs: <?php echo esc_attr($default_fs); ?>;
						--fw: normal;
						--fs_small: 11px;
						--fs_label: <?php echo esc_attr($fs_label); ?>;
						--fs_h6: <?php echo esc_attr($fs_h6); ?>;
						--fs_h5: <?php echo esc_attr($fs_h5); ?>;
						--fs_h4: <?php echo esc_attr($fs_h4); ?>;
						--fs_h3: <?php echo esc_attr($fs_h3); ?>;
						--fs_h2: <?php echo esc_attr($fs_h2); ?>;
						--fs_h1: <?php echo esc_attr($fs_h1); ?>;
						--fw-thin: 300; /*font weight medium*/
						--fw-normal: 500; /*font weight medium*/
						--fw-medium: 600; /*font weight medium*/
						--fw-bold: bold; /*font weight bold*/
					}
					/*****Button********/
					:root {
						--button_bg: <?php echo esc_attr($button_bg); ?>;
						--color_button: <?php echo esc_attr($button_color); ?>;
						--button_fs: <?php echo esc_attr($button_fs); ?>;
						--button_height: 40px;
						--button_height_xs: 30px;
						--button_width: 120px;
						--button_shadows: 0 8px 12px rgb(51 65 80 / 6%), 0 14px 44px rgb(51 65 80 / 11%);
					}
					/*******Color***********/
					:root {
						--d_color: <?php echo esc_attr($default_color); ?>;
						--color_border: #DDD;
						--color_active: #0E6BB7;
						--color_section: <?php echo esc_attr($section_bg); ?>;
						--color_theme: <?php echo esc_attr($theme_color); ?>;
						--color_theme_ee: <?php echo esc_attr($theme_color).'ee'; ?>;
						--color_theme_cc: <?php echo esc_attr($theme_color).'cc'; ?>;
						--color_theme_aa: <?php echo esc_attr($theme_color).'aa'; ?>;
						--color_theme_88: <?php echo esc_attr($theme_color).'88'; ?>;
						--color_theme_77: <?php echo esc_attr($theme_color).'77'; ?>;
						--color_theme_alter: <?php echo esc_attr($alternate_color); ?>;
						--color_warning: <?php echo esc_attr($warning_color); ?>;
						--color_primary: #007bff;
						--color_secondary: #6c757d;
						--color_black: #000;
						--color_success: #006607;
						--color_danger: #dc3545;
						--color_required: #C00;
						--color_white: #FFFFFF;
						--color_light: #F2F2F2;
						--color_light_1: #BBB;
						--color_light_2: #EAECEE;
						--color_light_3: #878787;
						--color_light_4: #f9f9f9;
						--color_info: #666;
						--color_yellow: #FEBB02;
						--color_blue: #815DF2;
						--color_navy_blue: #007CBA;
						--color_1: #0C5460;
						--color_2: #0CB32612;
						--color_3: #FAFCFE;
						--color_4: #6148BA;
						--color_5: #BCB;
						--color_6: #ffbe30;
						--color_7: <?php echo esc_attr($secondary); ?>;
						--color_dark: #303030;
						--color_gray: #D3D3D3;
						--color_green: #32C1A4;
						--color_red: #ED5A54;
						--color_orange: #FFBE00;
					}
					@media only screen and (max-width: 1100px) {
						:root {
							--fs: 14px;
							--fs_small: 12px;
							--fs_label: 15px;
							--fs_h4: 20px;
							--fs_h3: 22px;
							--fs_h2: 25px;
							--fs_h1: 30px;
							--dmpl: 32px;
							--dmp: 16px;
							--dmp_negetive: -16px;
							--dmp_xs: 8px;
							--dmp_xs_negative: -8px;
						}
					}
					@media only screen and (max-width: 700px) {
						:root {
							--fs: 12px;
							--fs_small: 10px;
							--fs_label: 13px;
							--fs_h6: 15px;
							--fs_h5: 16px;
							--fs_h4: 18px;
							--fs_h3: 20px;
							--fs_h2: 22px;
							--fs_h1: 24px;
							--dmp: 10px;
							--dmp_xs: 5px;
							--dmp_xs_negative: -5px;
							--button_fs: 14px;
						}
					}
                </style>
				<?php
			}
		}
		new MPWEM_Global_Style();
	}