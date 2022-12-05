<?php	
if ( ! defined('ABSPATH')) exit;  // if direct access


wp_enqueue_script('welcome-tabs');
wp_enqueue_style( 'welcome-tabs' );


$mep_settings_tab = array();


$mep_settings_tab[] = array(
    'id' => 'start',
    'title' => sprintf(__('%s Welcome','mage-eventpress'),'<i class="far fa-thumbs-up"></i>'),
    'priority' => 1,
    'active' => true,
);

$mep_settings_tab[] = array(
    'id' => 'general',
    'title' => sprintf(__('%s General','mage-eventpress'),'<i class="fas fa-list-ul"></i>'),
    'priority' => 2,
    'active' => false,
);


$mep_settings_tab[] = array(
    'id' => 'done',
    'title' => sprintf(__('%s Done','mage-eventpress'),'<i class="fas fa-pencil-alt"></i>'),
    'priority' => 4,
    'active' => false,
);

$mep_settings_tab = apply_filters('qa_welcome_tabs', $mep_settings_tab);
$tabs_sorted = array();
foreach ($mep_settings_tab as $page_key => $tab) $tabs_sorted[$page_key] = isset( $tab['priority'] ) ? $tab['priority'] : 0;
array_multisort($tabs_sorted, SORT_ASC, $mep_settings_tab);
wp_enqueue_style('font-awesome-5');


    if (!function_exists('mep_woo_install_check')) {
        function mep_woo_install_check() {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
            if (is_plugin_active('woocommerce/woocommerce.php')) {
                return 'Yes';
            } elseif (is_dir($plugin_dir)) {
                return 'Installed But Not Active';
            } else {
                return 'No';
            }
        }
    }







add_action('mep_quick_setup_header','mep_woo_quick_setup_action',90);
function mep_woo_quick_setup_action(){

    if(isset($_POST['active_woo_btn'])){     
        activate_plugin('woocommerce/woocommerce.php' );        
        ?>
    <script>  location.reload(); </script>
        <?php
    }

    if(isset($_POST['install_and_active_woo_btn'])){   
        echo '<div style="display:none">';    
        include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..

        $plugin = 'woocommerce';
        
        $api = plugins_api( 'plugin_information', array(
            'slug' => $plugin,
            'fields' => array(
                'short_description' => false,
                'sections' => false,
                'requires' => false,
                'rating' => false,
                'ratings' => false,
                'downloaded' => false,
                'last_updated' => false,
                'added' => false,
                'tags' => false,
                'compatibility' => false,
                'homepage' => false,
                'donate_link' => false,
            ),
        ));
        
        //includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
        include_once( ABSPATH . 'wp-admin/includes/file.php' );
        include_once( ABSPATH . 'wp-admin/includes/misc.php' );
        include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        
        $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
        $upgrader->install($api->download_link);
        activate_plugin('woocommerce/woocommerce.php' );
        echo '</div>';
    }



if(isset($_POST['finish_quick_setup'])){
    $url                 = (isset($_SERVER['HTTPS']) ? "" : "") . "$_SERVER[HTTP_HOST]";
    $event_label         = isset($_POST['event_label']) ? sanitize_text_field($_POST['event_label']) : 'Events';
    $event_slug          = isset($_POST['event_slug']) ? sanitize_text_field($_POST['event_slug']) : 'event';
    $event_expire_on     = isset($_POST['event_expire_on']) ? sanitize_text_field($_POST['event_expire_on']) : 'event_expire_datetime';
    $email_from_name     = isset($_POST['email_from_name']) ? sanitize_text_field($_POST['email_from_name']) : get_bloginfo('name');
    $email_from_addrss   = isset($_POST['email_from_address']) ? sanitize_text_field($_POST['email_from_address']) : "no-reply@$url";


    $general_settings_data  = get_option('general_setting_sec') ? get_option('general_setting_sec') : [];
    $email_settings_data    = get_option('email_setting_sec') ? get_option('email_setting_sec') : [];

    $update_general_settings_arr = [
        'mep_event_label' => $event_label,
        'mep_event_expire_on_datetimes' => $event_expire_on,
        'mep_event_slug' => $event_slug
    ];

    $update_email_settings_arr = [
        'mep_email_form_name' => $email_from_name,
        'mep_email_form_email' => $email_from_addrss
    ];

    $new_general_settings_data = array_replace($general_settings_data,$update_general_settings_arr);
    $new_email_settings_data = array_replace($email_settings_data,$update_email_settings_arr);

	
	
    update_option( 'general_setting_sec', $new_general_settings_data);
    update_option( 'email_setting_sec', $new_email_settings_data);
    update_option( 'mep_quick_setup', 'done');
 


    flush_rewrite_rules();


    wp_redirect(admin_url('edit.php?post_type=mep_events&page=mep_event_welcome_page'));


}


}


    add_action('mep_quick_setup_content_start', 'mep_quick_setup_welcome_content');
    function mep_quick_setup_welcome_content($tab){
    mep_quick_setup_start();
    }

    add_action('mep_quick_setup_content_general', 'mep_quick_setup_general_content');
    function mep_quick_setup_general_content($tab){
    mep_quick_setup_general();
    }


    add_action('mep_quick_setup_content_done', 'mep_quick_setup_done_content');
    function mep_quick_setup_done_content($tab){
    mep_quick_setup_done();
    }


    do_action('mep_quick_setup_header');
    ?>


<div id="ttbm_quick_setup" class="wrap">
   
	<div id="icon-tools" class="icon32"><br></div>
    <h2></h2>
		<form  method="post" action="">
	        <input type="hidden" name="qa_hidden" value="Y">
            <?php
            
                ?>
                <div class="welcome-tabs">
                    
                    <ul class="tab-navs">
                        <?php
                        foreach ($mep_settings_tab as $tab){
                            $id = $tab['id'];
                            $title = $tab['title'];
                            $active = $tab['active'];
                            $data_visible = isset($tab['data_visible']) ? $tab['data_visible'] : '';
                            $hidden = isset($tab['hidden']) ? $tab['hidden'] : false;
                            ?>
                            <li <?php if(!empty($data_visible)):  ?> data_visible="<?php echo esc_html($data_visible); ?>" <?php endif; ?> class="tab-nav <?php if($hidden) echo 'hidden';?> <?php if($active) echo 'active';?>" data-id="<?php echo esc_html($id); ?>"><?php echo $title; ?></li>
                            <?php
                        }
                        ?>
                    </ul>
                    <?php
                    foreach ($mep_settings_tab as $tab){
                        $id = $tab['id'];
                        $title = $tab['title'];
                        $active = $tab['active'];
                        ?>

                        <div class="tab-content <?php if($active) echo 'active';?>" id="<?php echo esc_html($id); ?>">

                            <?php
                           
                            do_action('mep_quick_setup_content_'.$id, $tab);
                            do_action('mep_after_quick_setup_content', $tab);
                            ?>
                        </div>
                        <?php
                    }
                    
                    ?>
                    <div class="next-prev">
                        
                        <div class="prev"><span><?php echo sprintf(__('%s Previous','mage-eventpress'),'&longleftarrow;')?></span></div>
   
                        <div class="next"><span><?php echo sprintf(__('Next %s','mage-eventpress'),'&longrightarrow;')?></span></div>

                    </div>
                </div>
                <div class="clear clearfix"></div>
                <?Php            
            ?>
		</form>
</div>


<?php
function mep_quick_setup_start(){
	$status = mep_check_woocommerce();
    ?>
    <h2><?php echo __('Event Manager and Tickets Selling Plugin', 'mage-eventpress'); ?></h2>
    <p><?php echo __('Thanks for choosing Event Manager and Tickets Selling Plugin for WooCommerce for your site, Please go step by step and choose some options to get started.', 'mage-eventpress'); ?></p>

	<table class="wc_status_table widefat" cellspacing="0" id="status">
		<tr>
			<td data-export-label="WC Version">
				<?php if ( $status == 1 ) { ?>
					<?php _e( 'Woocommerce already installed and activated', 'mage-eventpress' ); ?>
				<?php } elseif ( $status == 0 ) { ?>
					<?php _e( 'Woocommerce need to install and active', 'mage-eventpress' ); ?>
				<?php } else { ?>
					<?php _e( 'Woocommerce already install , please activate it', 'mage-eventpress' ); ?>
				<?php } ?>
			</td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td class="woo_btn_td">
				<?php if ( $status == 1 ) { ?>
					<span class="fas fa-check-circle"></span>
				<?php } elseif ( $status == 0 ) { ?>
					<button class="button" type="submit" name="install_and_active_woo_btn">Install & Active Now</button>
				<?php } else { ?>
					<button class="button" type="submit" name="active_woo_btn">Active Now</button>
				<?php } ?>
			</td>
		</tr>
	</table>
    <?php
}

function mep_quick_setup_general(){
    $general_data  = get_option('general_setting_sec');
    $email_data    = get_option('email_setting_sec');

    $url = (isset($_SERVER['HTTPS']) ? "" : "") . "$_SERVER[HTTP_HOST]";

    $label              = isset($general_data['mep_event_label']) ? $general_data['mep_event_label'] : 'Events';
    $slug               = isset($general_data['mep_event_slug']) ? $general_data['mep_event_slug'] : 'event';
    $expire               = isset($general_data['mep_event_expire_on_datetimes']) ? $general_data['mep_event_expire_on_datetimes'] : 'event_expire_datetime';
    $from_email         = isset($email_data['mep_email_form_name']) ? $email_data['mep_email_form_name'] : get_bloginfo('name');
    $from_email_address = isset($email_data['mep_email_form_email']) ? $email_data['mep_email_form_email'] : "no-reply@$url";
    ?>
 <div class="section">
            <div class="section-title"><?php echo __('General settings', 'mage-eventpress'); ?></div>
            <p class="description section-description"><?php echo __('Choose some general option.', 'mage-eventpress'); ?></p>
    <table class="wc_status_table widefat" cellspacing="0" id="status">
        <tr>
			<td><?php _e('Event Label:','mage-eventpress'); ?></td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td> 
            <input type="text" name="event_label" value='<?php echo esc_html($label); ?>'/> 
            <p class="info"><?php _e('It will change the event post type label on the entire plugin.','mage-eventpress'); ?></p>
            </td>
		</tr>
        <tr>
			<td><?php _e('Event Slug:','mage-eventpress'); ?></td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td> 
                <input type="text" name="event_slug" value='<?php echo esc_html($slug); ?>'/> 
                <p class="info"><?php _e('It will change the event slug on the entire plugin. Remember after changing this slug you need to flush permalinks. Just go to Settings->Permalinks hit the Save Settings button','mage-eventpress'); ?></p>
            </td>
		</tr>
        <tr>
			<td><?php _e('When will the event expire:','mage-eventpress'); ?></td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td> 
            <select class="regular" name="event_expire_on">
                <option value="event_start_datetime" <?php if($expire == 'event_start_datetime'){ echo 'Selected'; } ?>><?php _e('Event Start Time','mage-eventpress'); ?></option>
                <option value="event_expire_datetime" <?php if($expire == 'event_expire_datetime'){ echo 'Selected'; } ?>><?php _e('Event End Time','mage-eventpress'); ?></option>
            </select>
                <p class="info"><?php _e('Please select when the event will expire','mage-eventpress'); ?></p>
            </td>
		</tr>
        <tr>
			<td><?php _e('Email From Name:','mage-eventpress'); ?></td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td> 
            <input type="text" name="email_from_name" value='<?php echo esc_html($from_email); ?>'/> 
                <p class="info"><?php _e('Please enter the email from name','mage-eventpress'); ?></p>
            </td>
		</tr>
        <tr>
			<td><?php _e('From Email Address:','mage-eventpress'); ?></td>
			<td class="help"><span class="woocommerce-help-tip"></span></td>
			<td> 
                
            <input type="text" name="email_from_address" value='<?php echo esc_html($from_email_address); ?>'/> 
                <p class="info"><?php _e('Please enter the email from name','mage-eventpress'); ?></p>
            </td>
		</tr>
    </table>
 </div>          
<?php
}

function mep_quick_setup_done(){
?>
 <div class="section">
            <div class="section-title"><?php echo __('Finalize Setup', 'mage-eventpress'); ?></div>
            <p class="description section-description"><?php echo __('You are about to Finish & Save Event Manager and Tickets Selling Plugin setup process', 'mage-eventpress'); ?></p>
	<div class="setup_save_finish_area">
            <button type="submit" name="finish_quick_setup" class="button setup_save_finish"><?php _e('Finish & Save','mage-eventpress'); ?></button>
	</div>
</div>            
<?php
}
	function mep_check_woocommerce(): int {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return 1;
		} elseif ( is_dir( $plugin_dir ) ) {
			return 2;
		} else {
			return 0;
		}
	}