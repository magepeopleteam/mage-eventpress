<?php if ( wp_is_block_theme() ) {  ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php
	$block_content = do_blocks( '
		<!-- wp:group {"layout":{"type":"constrained"}} -->
		<div class="wp-block-group">
		<!-- wp:post-content /-->
		</div>
		<!-- /wp:group -->'
 	);
    wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
<header class="wp-block-template-part site-header">
    <?php block_header_area(); ?>
</header>
</div>
<?php
} else {
    get_header();	
    the_post();
}


$event_id       = get_the_id();
global $event_id, $post, $woocommerce;
$event_id       = !empty($event_id) ? $event_id : $post->ID;
$_the_event_id  = $event_id;

if (post_password_required()) {
    ?>
    <div class="mep-events-wrapper">
    <?php echo get_the_password_form();?>
    </div>
    <?php
} else {
    // echo $event_id;
    $event_meta            = get_post_custom($event_id);

// print_r($event_meta);

    $author_terms          = get_the_terms($event_id, 'mep_org');
    $book_count            = get_post_meta($event_id, 'total_booking', true);
    $user_api              = mep_get_option('google-map-api', 'general_setting_sec', '');
	//==========
    $mep_full_name         = array_key_exists('mep_full_name',$event_meta) && $event_meta['mep_full_name'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_full_name'][0]):'';
	$mep_reg_email         = array_key_exists('mep_reg_email',$event_meta) && $event_meta['mep_reg_email'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_email'][0]):'';
	$mep_reg_phone         = array_key_exists('mep_reg_phone',$event_meta) && $event_meta['mep_reg_phone'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_phone'][0]):'';
	$mep_reg_address         = array_key_exists('mep_reg_address',$event_meta) && $event_meta['mep_reg_address'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_address'][0]):'';
	$mep_reg_designation         = array_key_exists('mep_reg_designation',$event_meta) && $event_meta['mep_reg_designation'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_designation'][0]):'';
	$mep_reg_website         = array_key_exists('mep_reg_website',$event_meta) && $event_meta['mep_reg_website'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_website'][0]):'';
	$mep_reg_veg         = array_key_exists('mep_reg_veg',$event_meta) && $event_meta['mep_reg_veg'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_veg'][0]):'';
	$mep_reg_company         = array_key_exists('mep_reg_company',$event_meta) && $event_meta['mep_reg_company'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_company'][0]):'';
	$mep_reg_gender         = array_key_exists('mep_reg_gender',$event_meta) && $event_meta['mep_reg_gender'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_gender'][0]):'';
	$mep_reg_tshirtsize         = array_key_exists('mep_reg_tshirtsize',$event_meta) && $event_meta['mep_reg_tshirtsize'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_reg_tshirtsize'][0]):'';
	//==========
	$current_template         = array_key_exists('mep_event_template',$event_meta) && $event_meta['mep_event_template'][0] ?MP_Global_Function::data_sanitize($event_meta['mep_event_template'][0]):'';
    $global_template       = mep_get_option('mep_global_single_template', 'single_event_setting_sec', 'default-theme.php');
    $_current_template     = $current_template ?: $global_template;
    $currency_pos           = get_option('woocommerce_currency_pos');
    do_action('mep_event_single_page_after_header',$_the_event_id);
?>
    <div class="mep-events-wrapper wrapper">
        <div class="mep-events-container">
            <?php
            if (!class_exists('WC_Bundles')) {
				if (!class_exists('WEPOF_Extra_Product_Options')) {	
					if (!class_exists('WC_Advanced_Country_Restrictions_Dist')) {						
						if ( ! class_exists( 'WC_Google_Analytics_Integration' ) ) {							
							if ( ! class_exists( 'Xoo_Wl_Core' ) ) {
                                if ( ! class_exists( 'Ultimate_Woocommerce_Gift_Card_Public' ) ) {
               		 		        do_action('woocommerce_before_single_product');
                            }
						}
					  }
					}
			   }
            }
            $theme_name = "/themes/$_current_template";
            require_once(mep_template_file_path($theme_name));
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
        </div>
        <div class="mep-related-events-sec">
            <?php do_action('after-single-events'); ?>
        </div>
    </div>
<?php
// 	echo $_the_event_id;
    do_action('mep_event_single_template_end', $_the_event_id);
    do_action('mep_event_single_page_before_footer', $_the_event_id);
}

if ( wp_is_block_theme() ) {
// Code for block themes goes here.
?>
<footer class="wp-block-template-part">
    <?php block_footer_area(); ?>
</footer>
<?php wp_footer(); ?>
</body>    
<?php
} else {
    get_footer();
}