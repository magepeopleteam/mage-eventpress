<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

//Add admin page to the menu
add_action('admin_menu', 'mep_event_status_admin_menu');
function mep_event_status_admin_menu()
{
    add_submenu_page('edit.php?post_type=mep_events', __('Status', 'mage-eventpress'), __('<span style="color:yellow">Status</span>', 'mage-eventpress'), 'manage_options', 'mep_event_status_page',  'mep_event_status_page');
}
function mep_event_status_page()
{
$wp_v 			= get_bloginfo( 'version' );
$wc_v 			= WC()->version;
$wc_i 			= mep_woo_install_check();
$from_name 		= mep_get_option( 'mep_email_form_name', 'email_setting_sec', '');
$from_email 	= mep_get_option( 'mep_email_form_email', 'email_setting_sec', '');
?>

    <!-- Create a header in the default WordPress 'wrap' container -->
    <div class="wrap"></div>   
    <?php do_action('mep_event_status_notice_sec'); ?>
	<div class="wc_status_table_wrapper">  
    <table class="wc_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="WordPress Environment"><h2>Event Manager For Woocommerce Environment Status</h2></th>
		</tr>
	</thead>
	<tbody>		
		<tr>
			<td data-export-label="WC Version">WordPress Version:</td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td><?php if($wp_v > 5.5){ echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>'.esc_html($wp_v).'</span>'; }else{ echo '<span class="mep_warning"> <span class="dashicons dashicons-saved"></span>'.esc_html($wp_v).'</span>'; }  ?></td>
		</tr>
		
		<tr>
			<td data-export-label="WC Version">Woocommerce Installed:</td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td><?php if($wc_i == 'Yes'){ echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>'.esc_html($wc_i).'</span>'; }else{ echo '<span class="mep_error"> <span class="dashicons dashicons-no-alt"></span>'.esc_html($wc_i).'</span>'; }  ?></td>
		</tr>
	    <?php if(mep_woo_install_check() == 'Yes'){ ?>
		<tr>
			<td data-export-label="WC Version">Woocommerce Version:</td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td><?php if($wc_v > 4.8){ echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>'.esc_html($wc_v).'</span>'; }else{ echo '<span class="mep_warning"> <span class="dashicons dashicons-no-alt"></span>'.esc_html($wc_v).'</span>'; }  ?></td>
		</tr>
		<tr>
			<td data-export-label="WC Version">Email From Name:</td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td><?php if($from_name){ echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>'.esc_html($from_name).'</span>'; }else{ echo '<span class="mep_error"> <span class="dashicons dashicons-no-alt"></span></span>'; }  ?></td>
		</tr>
		<tr>
			<td data-export-label="WC Version">From Email Address:</td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td><?php if($from_email){ echo '<span class="mep_success"> <span class="dashicons dashicons-saved"></span>'.esc_html($from_email).'</span>'; }else{ echo '<span class="mep_error"> <span class="dashicons dashicons-no-alt"></span></span>'; }  ?></td>
		</tr>
        <?php } 
        do_action('mep_event_status_table_item_sec'); ?>    
	</tbody>
</table>
</div>   
<?php
}