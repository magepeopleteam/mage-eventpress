<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

appsero_init_tracker_mage_eventpress();

// Language Load
add_action( 'init', 'mep_language_load');
if (!function_exists('mep_language_load')) {
  function mep_language_load(){
      $plugin_dir = basename(dirname(__DIR__))."/languages/";
      load_plugin_textdomain( 'mage-eventpress', false, $plugin_dir );
  }
}

if (!function_exists('mep_get_builder_version')) {
  function mep_get_builder_version(){
    if(is_plugin_active( 'woocommerce-event-manager-addon-form-builder/addon-builder.php' )){
      $data = get_plugin_data( ABSPATH . "wp-content/plugins/woocommerce-event-manager-addon-form-builder/addon-builder.php", false, false );
      return $data['Version'];
    }else{
      return 0;
    }
  }
}

if (!function_exists('mep_check_builder_status')) {
function mep_check_builder_status(){
    $version = '3.2';
    if(is_plugin_active( 'woocommerce-event-manager-addon-form-builder/addon-builder.php' )){
        $data = get_plugin_data( ABSPATH . "wp-content/plugins/woocommerce-event-manager-addon-form-builder/addon-builder.php", false, false );
      if ( is_plugin_active( 'woocommerce-event-manager-addon-form-builder/addon-builder.php' ) && $data['Version'] >= $version ) {
      return true;
    }elseif ( is_plugin_active( 'woocommerce-event-manager-addon-form-builder/addon-builder.php' ) && $data['Version'] < $version ) {
      return false;
    }else{
      return true;
    }
    }else{
      return true;
    }
  }
}


if (!function_exists('mep_get_all_tax_list')) {
function mep_get_all_tax_list($current_tax=null){
    global $wpdb;
    $table_name = $wpdb->prefix . 'wc_tax_rate_classes';
    $result = $wpdb->get_results( "SELECT * FROM $table_name" );
  
    foreach ( $result as $tax ){
    ?>
    <option value="<?php echo $tax->slug;  ?>" <?php if($current_tax == $tax->slug ){ echo 'Selected'; } ?>><?php echo $tax->name;  ?></option>
    <?php
    }
  }
}
  
  
  
  // Class for Linking with Woocommerce with Event Pricing 
  add_action('plugins_loaded', 'mep_load_wc_class');
  if (!function_exists('mep_load_wc_class')) {  
  function mep_load_wc_class() {
      
    if ( class_exists('WC_Product_Data_Store_CPT') ) {
  
     class MEP_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {
  
      public function read( &$product ) {
          $product->set_defaults();
          if ( ! $product->get_id() || ! ( $post_object = get_post( $product->get_id() ) ) || ! in_array( $post_object->post_type, array( 'mep_events', 'product' ) ) ) { // change birds with your post type
              throw new Exception( __( 'Invalid product.', 'woocommerce' ) );
          }
  
          $id = $product->get_id();
  
          $product->set_props( array(
              'name'              => $post_object->post_title,
              'slug'              => $post_object->post_name,
              'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
              'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
              'product_id'        => $post_object->ID,
              'sku'               => $post_object->ID,
              'status'            => $post_object->post_status,
              'description'       => $post_object->post_content,
              'short_description' => $post_object->post_excerpt,
              'parent_id'         => $post_object->post_parent,
              'menu_order'        => $post_object->menu_order,
              'reviews_allowed'   => 'open' === $post_object->comment_status,
          ) );
  
          $this->read_attributes( $product );
          $this->read_downloads( $product );
          $this->read_visibility( $product );
          $this->read_product_data( $product );
          $this->read_extra_data( $product );
          $product->set_object_read( true );
      }
  
      /**
       * Get the product type based on product ID.
       *
       * @since 3.0.0
       * @param int $product_id
       * @return bool|string
       */
      public function get_product_type( $product_id ) {
          $post_type = get_post_type( $product_id );
          if ( 'product_variation' === $post_type ) {
              return 'variation';
          } elseif ( in_array( $post_type, array( 'mep_events', 'product' ) ) ) { // change birds with your post type
              $terms = get_the_terms( $product_id, 'product_type' );
              return ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';
          } else {
              return false;
          }
      }
  }
}
  }
}  









  add_action('woocommerce_before_checkout_form', 'mep_displays_cart_products_feature_image');
  if (!function_exists('mep_displays_cart_products_feature_image')) {  
  function mep_displays_cart_products_feature_image() {
      foreach ( WC()->cart->get_cart() as $cart_item ) {
          $item = $cart_item['data'];
      }
  }
  }


 // Send Confirmation email to customer
 if (!function_exists('mep_event_confirmation_email_sent')) {   
 function mep_event_confirmation_email_sent($event_id,$sent_email){
    $values = get_post_custom($event_id);
    
    $global_email_text = mep_get_option( 'mep_confirmation_email_text', 'email_setting_sec', '');
    $global_email_form_email = mep_get_option( 'mep_email_form_email', 'email_setting_sec', '');
    $global_email_form = mep_get_option( 'mep_email_form_name', 'email_setting_sec', '');
    $global_email_sub = mep_get_option( 'mep_email_subject', 'email_setting_sec', '');
    $event_email_text = $values['mep_event_cc_email_text'][0];
    $admin_email = get_option( 'admin_email' );
    $site_name = get_option( 'blogname' );
    
    
      if($global_email_sub){
        $email_sub = $global_email_sub;
      }else{
        $email_sub = 'Confirmation Email';
      }
    
      if($global_email_form){
        $form_name = $global_email_form;
      }else{
        $form_name = $site_name;
      }
    
      if($global_email_form_email){
        $form_email = $global_email_form_email;
      }else{
        $form_email = $admin_email;
      }
    
      if($event_email_text){
        $email_body = $event_email_text;
      }else{
        $email_body = $global_email_text;
      }
    
      $headers[] = "From: $form_name <$form_email>";
    
      if($email_body){
      $sent = wp_mail( $sent_email, $email_sub, nl2br($email_body), $headers );
      }
    }
  }



  if (!function_exists('mep_event_get_order_meta')) {  
  function mep_event_get_order_meta($item_id,$key){
  global $wpdb;
    $table_name = $wpdb->prefix."woocommerce_order_itemmeta";
    $sql = 'SELECT meta_value FROM '.$table_name.' WHERE order_item_id ='.$item_id.' AND meta_key="'.$key.'"';
    $results = $wpdb->get_results($sql); //or die(mysql_error());
    foreach( $results as $result ) {
       $value = $result->meta_value;
    }
    $val = isset($value) ? $value : '';
    return $val;
  }  
}
  

if (!function_exists('mep_event_get_event_city_list')) {  
 function mep_event_get_event_city_list(){
  global $wpdb;
    $table_name = $wpdb->prefix."postmeta";
    $sql = "SELECT meta_value FROM $table_name WHERE meta_key ='mep_city' GROUP BY meta_value";
    $results = $wpdb->get_results($sql); //or die(mysql_error());
    ob_start();
    ?>
    <div class='mep-city-list'>
    <ul>
    <?php
    foreach( $results as $result ) {
     ?>
       <li><a href='<?php echo get_site_url(); ?>/event-by-city-name/<?php echo $result->meta_value; ?>/'><?php echo $result->meta_value; ?></a></li>
     <?php
    }
    ?>
    </ul>
    </div>
    <?php
    return ob_get_clean();
  }
}


// Function to get page slug
if (!function_exists('mep_get_page_by_slug')) {
function mep_get_page_by_slug($slug) {
    if ($pages = get_pages())
        foreach ($pages as $page)
            if ($slug === $page->post_name) return $page;
    return false;
}
}

//add_action('admin_init','mep_page_create');
// Cretae pages on plugin activation
if (!function_exists('mep_page_create')) {
function mep_page_create() {

        if (! mep_get_page_by_slug('event-by-city-name')) {
            $mep_search_page = array(
            'post_type' => 'page',
            'post_name' => 'event-by-city-name',
            'post_title' => 'Event By City',
            'post_content' => '',
            'post_status' => 'publish',
            );

            wp_insert_post($mep_search_page);
        }

} 
}  
  
if (!function_exists('mep_city_filter_rewrite_rule')) {
function mep_city_filter_rewrite_rule() {    
	add_rewrite_rule(
		'^event-by-city-name/(.+)/?$',    
		'index.php?cityname=$matches[1]&pagename=event-by-city-name',
		'top'
		);
}
}
add_action( 'init', 'mep_city_filter_rewrite_rule' );


if (!function_exists('mep_city_filter_query_var')) {
function mep_city_filter_query_var( $vars ) {
	$vars[] = 'cityname';
	return $vars;
}
}
add_filter( 'query_vars', 'mep_city_filter_query_var' );
  

if (!function_exists('mep_city_template_chooser')) {  
function mep_city_template_chooser($template){
  if ( get_query_var( 'cityname' ) ) {
    $template = mep_template_file_path('page-city-filter.php');
  }
  return $template;  
}
}
add_filter('template_include', 'mep_city_template_chooser');

  

function mep_get_event_ticket_price_by_name($event,$type) {
  $ticket_type = get_post_meta($event,'mep_event_ticket_type',true);
  if(sizeof($ticket_type) > 0){    
         foreach ($ticket_type as $key => $val) {
         if ($val['option_name_t'] === $type) {
          return $val['option_price_t'];
         }
     }
     return 0;
  }
  }





if (!function_exists('mep_attendee_create')) {  
function mep_attendee_create($type,$order_id,$event_id,$_user_info = array()){
  
  // Getting an instance of the order object
  $order              = wc_get_order( $order_id );
  $order_meta         = get_post_meta($order_id); 
  $order_status       = $order->get_status();
  
  
  $billing_intotal  = isset($order_meta['_billing_address_index'][0]) ? $order_meta['_billing_address_index'][0] : '';
  $payment_method   = isset($order_meta['_payment_method_title'][0]) ? $order_meta['_payment_method_title'][0] : '';
  $user_id          = isset($order_meta['_customer_user'][0]) ? $order_meta['_customer_user'][0] : '';
  
  if($type == 'billing'){
  // Billing Information 
    $first_name       = isset($order_meta['_billing_first_name'][0]) ? $order_meta['_billing_first_name'][0] : '';
    $last_name        = isset($order_meta['_billing_last_name'][0]) ? $order_meta['_billing_last_name'][0] : '';
    $uname            = $first_name.' '.$last_name;
    $company          = isset($order_meta['_billing_company'][0]) ?  $order_meta['_billing_company'][0] : '';
    $address_1        = isset($order_meta['_billing_address_1'][0]) ? $order_meta['_billing_address_1'][0] : '';
    $address_2        = isset($order_meta['_billing_address_2'][0]) ? $order_meta['_billing_address_2'][0] : '';
    $address          = $address_1.' '.$address_2;
    $gender = '';
    $designation = '';
    $website = '';
    $vegetarian = '';
    $tshirtsize = '';
    $city             = isset($order_meta['_billing_city'][0]) ? $order_meta['_billing_city'][0] : '';
    $state            = isset($order_meta['_billing_state'][0]) ? $order_meta['_billing_state'][0] : '';
    $postcode         = isset($order_meta['_billing_postcode'][0]) ? $order_meta['_billing_postcode'][0] : '';
    $country          = isset($order_meta['_billing_country'][0]) ? $order_meta['_billing_country'][0] : '';
    $email            = isset($order_meta['_billing_email'][0]) ? $order_meta['_billing_email'][0] : '';
    $phone            = isset($order_meta['_billing_phone'][0]) ? $order_meta['_billing_phone'][0] : '';
    $ticket_type      = $_user_info['ticket_name'];
    $event_date       = $_user_info['event_date'];
      $ticket_qty =  $_user_info['ticket_qty'];
  
  }elseif($type == 'user_form'){
  
  
      $uname          = $_user_info['user_name'];
      $email          = $_user_info['user_email'];
      $phone          = $_user_info['user_phone'];
      $address        = $_user_info['user_address'];
      $gender         = $_user_info['user_gender'];
      $company        = $_user_info['user_company'];
      $designation    = $_user_info['user_designation'];
      $website        = $_user_info['user_website'];
      $vegetarian     = $_user_info['user_vegetarian'];
      $tshirtsize     = $_user_info['user_tshirtsize'];
      $ticket_type    = $_user_info['user_ticket_type'];
      $ticket_qty     = $_user_info['user_ticket_qty'];
      $event_date     = $_user_info['user_event_date'];
      $event_id       = $_user_info['user_event_id'] ? $_user_info['user_event_id'] : $event_id;
      $mep_ucf        = isset($_user_info['mep_ucf']) ? $_user_info['mep_ucf'] : "";
  
  }
  
  
// $ticket_single_price = mep_get_event_ticket_price_by_name($event_id,$ticket_type);
$ticket_total_price = (mep_get_event_ticket_price_by_name($event_id,$ticket_type) * $ticket_qty);
  
  $new_post = array(
    'post_title'    =>   $uname,
    'post_content'  =>   '',
    'post_category' =>   array(),  // Usable for custom taxonomies too
    'tags_input'    =>   array(),
    'post_status'   =>   'publish', // Choose: publish, preview, future, draft, etc.
    'post_type'     =>   'mep_events_attendees'  //'post',page' or use a custom post type if you want to
    );
  
    //SAVE THE POST
    $pid                = wp_insert_post($new_post);
    $pin                = $user_id.$order_id.$event_id.$pid;
      update_post_meta( $pid, 'ea_name', $uname );
      update_post_meta( $pid, 'ea_address_1', $address );
      update_post_meta( $pid, 'ea_email', $email );
      update_post_meta( $pid, 'ea_phone', $phone );
      update_post_meta( $pid, 'ea_gender', $gender );
      update_post_meta( $pid, 'ea_company', $company );
      update_post_meta( $pid, 'ea_desg', $designation );
      update_post_meta( $pid, 'ea_website', $website );
      update_post_meta( $pid, 'ea_vegetarian', $vegetarian );
      update_post_meta( $pid, 'ea_tshirtsize', $tshirtsize );
      update_post_meta( $pid, 'ea_ticket_type', $ticket_type );
      update_post_meta( $pid, 'ea_ticket_qty', $ticket_qty);      
      update_post_meta( $pid, 'ea_ticket_price', $ticket_total_price);
      update_post_meta( $order_id, 'ea_ticket_qty', $ticket_qty);
      update_post_meta( $order_id, 'ea_ticket_type', $ticket_type );
      update_post_meta( $order_id, 'ea_event_id', $event_id );
      update_post_meta( $pid, 'ea_payment_method', $payment_method );
      update_post_meta( $pid, 'ea_event_name', get_the_title( $event_id ) );
      update_post_meta( $pid, 'ea_event_id', $event_id );
      update_post_meta( $pid, 'ea_order_id', $order_id );
      update_post_meta( $pid, 'ea_user_id', $user_id );
      update_post_meta( $order_id, 'ea_user_id', $user_id );
      update_post_meta( $order_id, 'order_type_name', 'mep_events' );
      update_post_meta( $pid, 'ea_ticket_no', $pin );
      update_post_meta( $pid, 'ea_event_date', $event_date );
      update_post_meta( $pid, 'ea_order_status', $order_status );
      update_post_meta( $order_id, 'ea_order_status', $order_status );
  
      $hooking_data = apply_filters('mep_event_attendee_dynamic_data',array(),$pid,$type,$order_id,$event_id,$_user_info); 
      
      if(is_array($hooking_data) && sizeof($hooking_data) > 0){
        foreach ($hooking_data as $_data) {
          update_post_meta( $pid, $_data['name'], $_data['value'] );
        }
      }

    // Checking if the form builder addon is active and have any custom fields
    $mep_form_builder_data = get_post_meta($event_id, 'mep_form_builder_data', true);
      if ( $mep_form_builder_data ) {
        foreach ( $mep_form_builder_data as $_field ) {
          update_post_meta( $pid, "ea_".$_field['mep_fbc_id'], $_user_info[$_field['mep_fbc_id']]); 
        }
    } // End User Form builder data update loop
  
  }
}

  
if (!function_exists('mep_attendee_extra_service_create')) { 
  function mep_attendee_extra_service_create($order_id,$event_id, $_event_extra_service){
  
    $order              = wc_get_order( $order_id );
    $order_meta         = get_post_meta($order_id); 
    $order_status       = $order->get_status();
     if(is_array($_event_extra_service) && sizeof($_event_extra_service) > 0){
  
     foreach($_event_extra_service as $extra_serive){
       if($extra_serive['service_name']){
      $uname = 'Extra Service for '.get_the_title($event_id).' Order #'.$order_id;
      $new_post = array(
        'post_title'    =>   $uname,
        'post_content'  =>   '',
        'post_category' =>   array(), 
        'tags_input'    =>   array(),
        'post_status'   =>   'publish', 
        'post_type'     =>   'mep_extra_service'
      );
  
       $pid             = wp_insert_post($new_post);
     
      update_post_meta( $pid, 'ea_extra_service_name', $extra_serive['service_name'] );
      update_post_meta( $pid, 'ea_extra_service_qty', $extra_serive['service_qty'] );
      update_post_meta( $pid, 'ea_extra_service_unit_price', $extra_serive['service_price'] );
      update_post_meta( $pid, 'ea_extra_service_total_price', $extra_serive['service_qty'] * $extra_serive['service_price'] );
      update_post_meta( $pid, 'ea_extra_service_event', $event_id );
      update_post_meta( $pid, 'ea_extra_service_order', $order_id );
      update_post_meta( $pid, 'ea_extra_service_order_status', $order_status );
      update_post_meta( $pid, 'ea_extra_service_event_date', $extra_serive['event_date'] );
    }
     }
  
  }

  }
}
  
  

  add_action('woocommerce_checkout_order_processed', 'mep_event_booking_management', 10);
  if (!function_exists('mep_event_booking_management')) {   
  function mep_event_booking_management( $order_id) {

  
  if ( ! $order_id )
    return;
  
  // Getting an instance of the order object
  $order              = wc_get_order( $order_id );
  $order_meta         = get_post_meta($order_id); 
  $order_status       = $order->get_status();
  
  $form_position = mep_get_option( 'mep_user_form_position', 'general_attendee_sec', 'details_page' );
  
  if($form_position=='checkout_page'){
  
    foreach ( $order->get_items() as $item_id => $item_values ) {
      $item_id                    = $item_id;
    }
    $event_id                     = wc_get_order_item_meta($item_id,'event_id',true);
    if (get_post_type($event_id)  == 'mep_events') { 
       
      $event_name               = get_the_title($event_id);
      $user_info_arr            = wc_get_order_item_meta($item_id,'_event_user_info',true);
      $service_info_arr         = wc_get_order_item_meta($item_id,'_event_service_info',true);
      $event_ticket_info_arr    = wc_get_order_item_meta($item_id,'_event_ticket_info',true);
      $item_quantity            = 0;
      
  
      foreach ( $event_ticket_info_arr as $field ) {
        if($field['ticket_qty']>0){
            $item_quantity = $item_quantity + $field['ticket_qty'];
        }
      } 
      if(is_array($user_info_arr) & sizeof($user_info_arr) > 0){
        foreach ($user_info_arr as $_user_info) {
            mep_attendee_create('user_form',$order_id,$event_id,$_user_info);
        } 
      }else{
          foreach($event_ticket_info_arr as $tinfo){
            for ($x = 1; $x <= $tinfo['ticket_qty']; $x++) {
                mep_attendee_create('billing',$order_id,$event_id,$tinfo);
            } 
          }
      }  
    }
  }else{
    foreach ( $order->get_items() as $item_id => $item_values ) {
      $item_id                    = $item_id;
      $event_id                   = wc_get_order_item_meta($item_id,'event_id',true);
        if (get_post_type($event_id) == 'mep_events') { 
          $event_name             = get_the_title($event_id);
          $user_info_arr          = wc_get_order_item_meta($item_id,'_event_user_info',true);
          $service_info_arr       = wc_get_order_item_meta($item_id,'_event_service_info',true);
          $event_ticket_info_arr  = wc_get_order_item_meta($item_id,'_event_ticket_info',true);
          $_event_extra_service   = wc_get_order_item_meta($item_id,'_event_extra_service',true);
          $item_quantity          = 0;

          mep_attendee_extra_service_create($order_id,$event_id,$_event_extra_service);
          foreach ( $event_ticket_info_arr as $field ) {
            if($field['ticket_qty']>0){
                $item_quantity    = $item_quantity + $field['ticket_qty'];
            }
          } 
          if(is_array($user_info_arr) & sizeof($user_info_arr) > 0){
            foreach ($user_info_arr as $_user_info) {
                mep_attendee_create('user_form',$order_id,$event_id,$_user_info);
            } 
          }else{
              foreach($event_ticket_info_arr as $tinfo){
                for ($x = 1; $x <= $tinfo['ticket_qty']; $x++) {
                    mep_attendee_create('billing',$order_id,$event_id,$tinfo);
                } 
              }
          }  
        } // end of check post type
    }
  }
  do_action('mep_after_event_booking',$order_id,$order->get_status());
  
    
  }
}
  
  
if (!function_exists('change_attandee_order_status')) {    
  function change_attandee_order_status($order_id,$set_status,$post_status,$qr_status=null){
  
      $args = array (
          'post_type'         => array( 'mep_events_attendees' ),
          'posts_per_page'    => -1,
          'post_status' => $post_status,
          'meta_query' => array(
               array(
                               'key'       => 'ea_order_id',
                               'value'     => $order_id,
                               'compare'   => '='
                       )
               )
           );
          $loop = new WP_Query($args);
           $tid = array();
          foreach ($loop->posts as $ticket) {
              $post_id = $ticket->ID;
          
              update_post_meta($post_id, 'ea_order_status', $qr_status);
              
              $current_post = get_post( $post_id, 'ARRAY_A' );
              $current_post['post_status'] = $set_status;
              wp_update_post($current_post);
          }
  }
}  
  

if (!function_exists('change_extra_service_status')) {   
  function change_extra_service_status($order_id,$set_status,$post_status,$qr_status=null){
  
      $args = array (
          'post_type'         => array( 'mep_extra_service' ),
          'posts_per_page'    => -1,
          'post_status' => $post_status,
          'meta_query' => array(
               array(
                               'key'       => 'ea_extra_service_order',
                               'value'     => $order_id,
                               'compare'   => '='
                       )
               )
           );
          $loop = new WP_Query($args);
           $tid = array();
          foreach ($loop->posts as $ticket) {
              $post_id = $ticket->ID;
              
              update_post_meta($post_id, 'ea_extra_service_order_status', $qr_status);
              
              $current_post = get_post( $post_id, 'ARRAY_A' );
              $current_post['post_status'] = $set_status;
              wp_update_post($current_post);
          }
  }
}
  
  
if (!function_exists('change_wc_event_product_status')) {   
  function change_wc_event_product_status($order_id,$set_status,$post_status,$qr_status=null){
  
      $args = array (
          'post_type'         => array( 'product' ),
          'posts_per_page'    => -1,
          'post_status' => $post_status,
          'meta_query' => array(
               array(
                               'key'       => 'link_mep_event',
                               'value'     => $order_id,
                               'compare'   => '='
                       )
               )
           );
          $loop = new WP_Query($args);
           $tid = array();
          foreach ($loop->posts as $ticket) {
              $post_id = $ticket->ID;
              if(!empty($qr_status)){
                  //update_post_meta($post_id, 'ea_order_status', $qr_status);
              }
              $current_post = get_post( $post_id, 'ARRAY_A' );
              $current_post['post_status'] = $set_status;
              wp_update_post($current_post);
          }
  }
}
  
  
  
  
  add_action('wp_trash_post','mep_addendee_trash',90);
  if (!function_exists('mep_addendee_trash')) {     
  function mep_addendee_trash( $post_id ) {
    $post_type   = get_post_type( $post_id );
    $post_status = get_post_status( $post_id );
    
    if ( $post_type == 'shop_order' ) {
      change_attandee_order_status( $post_id, 'trash', 'publish', '' );
      change_extra_service_status( $post_id, 'trash', 'publish', '' );
    }
    
    
    if ( $post_type == 'mep_events' ) {
      change_wc_event_product_status( $post_id, 'trash', 'publish', '' );
    }
  }
}
  
  add_action('untrash_post','mep_addendee_untrash',90);
  if (!function_exists('mep_addendee_untrash')) {   
  function mep_addendee_untrash( $post_id ) {
    $post_type   = get_post_type( $post_id );
    $post_status = get_post_status( $post_id );
    if ( $post_type == 'shop_order' ) {
      $order            = wc_get_order( $post_id );
      $order_status     = $order->get_status();
      change_attandee_order_status( $post_id, 'publish', 'trash', '' );
      change_extra_service_status( $post_id, 'publish', 'trash', '' );
    }
    
    if ( $post_type == 'mep_events' ) {
      change_wc_event_product_status( $post_id, 'publish', 'trash', '' );
    }  
    
  }
}
  
  
  add_action('woocommerce_order_status_changed', 'mep_attendee_status_update', 10, 4);
  if (!function_exists('mep_attendee_status_update')) {   
  function mep_attendee_status_update($order_id, $from_status, $to_status, $order ){

    global $wpdb,$wotm;
    // Getting an instance of the order object
     $order      = wc_get_order( $order_id );
     $order_meta = get_post_meta($order_id); 
     $email      = isset($order_meta['_billing_email'][0]) ? $order_meta['_billing_email'][0] : array();
     $email_send_status = mep_get_option('mep_email_sending_order_status','email_setting_sec',array('completed' => 'completed'));
    //  mep_email_sending_order_status

    
  
     foreach ( $order->get_items() as $item_id => $item_values ) {
      $item_id        = $item_id;
      $event_id      = mep_event_get_order_meta($item_id,'event_id');
      
      if (get_post_type($event_id) == 'mep_events') {
  
  
        if($order->has_status( 'processing' ) ) {

          if(in_array('processing',$email_send_status)){
            mep_event_confirmation_email_sent($event_id,$email);
          }

          change_attandee_order_status($order_id,'publish','trash','processing');
          change_attandee_order_status($order_id,'publish','publish','processing');        
          
          change_extra_service_status($order_id,'publish','trash','processing');
          change_extra_service_status($order_id,'publish','publish','processing');



        }
        if($order->has_status( 'pending' )) {
          change_attandee_order_status($order_id,'publish','trash','pending');
          change_attandee_order_status($order_id,'publish','publish','pending');        
          
          change_extra_service_status($order_id,'publish','trash','pending');
          change_extra_service_status($order_id,'publish','publish','pending');
        }
        if($order->has_status( 'on-hold' )) {
          change_attandee_order_status($order_id,'publish','trash','on-hold');
          change_attandee_order_status($order_id,'publish','publish','on-hold');
        }
        if($order->has_status( 'completed' ) ) {

          if(in_array('completed',$email_send_status)){
            mep_event_confirmation_email_sent($event_id,$email);
          }

          change_attandee_order_status($order_id,'publish','trash','completed');
          change_attandee_order_status($order_id,'publish','publish','completed');        
          
          change_extra_service_status($order_id,'publish','trash','completed');
          change_extra_service_status($order_id,'publish','publish','completed');
        }
        if($order->has_status( 'cancelled' ) ) {
          change_attandee_order_status($order_id,'trash','publish','cancelled');
          
          change_extra_service_status($order_id,'trash','publish','cancelled');
        }
        if($order->has_status( 'refunded' ) ) {
          change_attandee_order_status($order_id,'trash','publish','refunded');
          
          change_extra_service_status($order_id,'trash','publish','refunded');
        }
        if($order->has_status( 'failed' ) ) {
          change_attandee_order_status($order_id,'trash','publish','failed');
          
          change_extra_service_status($order_id,'trash','publish','failed');
        }
      } // End of Post Type Check
     } // End order item foreach
  } // End Function  
}
  
  

  
  
  
  add_action('restrict_manage_posts', 'mep_filter_post_type_by_taxonomy');
  if (!function_exists('mep_filter_post_type_by_taxonomy')) {    
  function mep_filter_post_type_by_taxonomy() {
    global $typenow;
    $post_type = 'mep_events'; // change to your post type
    $taxonomy  = 'mep_cat'; // change to your taxonomy
    if ($typenow == $post_type) {
      $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
      $info_taxonomy = get_taxonomy($taxonomy);
      wp_dropdown_categories(array(
        'show_option_all' => __("Show All {$info_taxonomy->label}"),
        'taxonomy'        => $taxonomy,
        'name'            => $taxonomy,
        'orderby'         => 'name',
        'selected'        => $selected,
        'show_count'      => true,
        'hide_empty'      => true,
      ));
    };
  }
}
  
  
  
  add_filter('parse_query', 'mep_convert_id_to_term_in_query');
  if (!function_exists('mep_convert_id_to_term_in_query')) {
  function mep_convert_id_to_term_in_query($query) {
    global $pagenow;
    $post_type = 'mep_events'; // change to your post type
    $taxonomy  = 'mep_cat'; // change to your taxonomy
    $q_vars    = &$query->query_vars;
  
    if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
      $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
      $q_vars[$taxonomy] = $term->slug;
    }
  
  }
}
  
  
  add_filter('parse_query', 'mep_attendee_filter_query');
  if (!function_exists('mep_attendee_filter_query')) {  
  function mep_attendee_filter_query($query) {
    global $pagenow;
    $post_type = 'mep_events_attendees'; 
    $q_vars    = &$query->query_vars;
  
    if ( $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == $post_type && isset($_GET['meta_value']) && $_GET['meta_value'] != 0) {
  
      $q_vars['meta_key'] = 'ea_event_id';
      $q_vars['meta_value'] = $_GET['meta_value'];
  
    }elseif ( $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == $post_type && isset($_GET['event_id']) && $_GET['event_id'] != 0 && !isset($_GET['action']) ) {
      
      $event_date = date('Y-m-d',strtotime($_GET['ea_event_date']));
      $meta_query = array([
        'key'     => 'ea_event_id',
        'value'   => $_GET['event_id'],
        'compare' => '='
      ],[
        'key'     => 'ea_event_date',
        'value'   => $event_date,
        'compare' => 'LIKE'
      ],[
        'key'     => 'ea_order_status',
        'value'   => 'completed',
        'compare' => '='
      ]);
        
        $query->set( 'meta_query', $meta_query );
  
    }
  }
}
  
  
  
  
  
  
  // Add the data to the custom columns for the book post type:
  add_action( 'manage_mep_events_posts_custom_column' , 'mep_custom_event_column', 10, 2 );
  if (!function_exists('mep_custom_event_column')) {    
  function mep_custom_event_column( $column, $post_id ) {
  switch ( $column ) {
  
          case 'mep_status' :          
            $values               = get_post_custom( $post_id );  
            $recurring            = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';
  
  
            if($recurring == 'yes'){
              $event_more_dates    = get_post_meta($post_id,'mep_event_more_date',true);
              $seat_left = 10;
              $md = end($event_more_dates);
              $more_date = $md['event_more_start_date'].' '.$md['event_more_start_time'];
              $event_date = date('Y-m-d H:i:s',strtotime($more_date));
            }else{
                $event_expire_on_old = mep_get_option( 'mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
                $event_expire_on    = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
                
              $event_date = $values[$event_expire_on][0];  
            }           
            echo mep_get_event_status($event_date); 
          break;
  
          case 'mep_atten' :
            $multi_date           = get_post_meta($post_id,'mep_event_more_date',true) ? get_post_meta($post_id,'mep_event_more_date',true) : array();
            $recurring            = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';
            // print_r($multi_date);
            // if($recurring == 'yes'){ 
            ?>
            <form action="" method="get">
              <?php 
              if($recurring == 'everyday'){
                do_action('mep_before_attendee_list_btn',$post_id);
              }else{
              ?>
              <select name="ea_event_date" id="" style='font-size: 14px;border: 1px solid blue;width: 110px;display:<?php if($recurring == 'yes'){ echo 'block'; }else{ echo 'none'; } ?>'>
                  <option value="<?php echo date('Y-m-d H:i:s',strtotime(get_post_meta($post_id,'event_start_date',true).' '.get_post_meta($post_id,'event_start_time',true))); ?>"><?php echo get_mep_datetime(get_post_meta($post_id,'event_start_date',true),'date-text').' '.get_mep_datetime(get_post_meta($post_id,'event_start_date',true).' '.get_post_meta($post_id,'event_start_time',true),'time'); ?></option>
                  <?php foreach($multi_date as $multi){ ?>
                    <option value="<?php echo date('Y-m-d H:i:s',strtotime($multi['event_more_start_date'].' '.$multi['event_more_start_time'])); ?>"><?php echo get_mep_datetime($multi['event_more_start_date'],'date-text').' '.get_mep_datetime($multi['event_more_start_time'],'time'); ?></option>
                  <?php } ?>
              </select>
              <?php } ?>
              <input type="hidden" name='post_type' value='mep_events_attendees'>
              <input type="hidden" name='event_id' value='<?php echo $post_id; ?>'>
              <button class="button button-primary button-large"><?php _e('Attendees List','mage-eventpress'); ?></button>
            </form>
            <?php
            // }else{
            //  echo '<a class="button button-primary button-large" href="'.get_site_url().'/wp-admin/edit.php?post_type=mep_events_attendees&meta_value='.$post_id.'">Attendees List</a></form>'; 
            // }
              break;
      }
  }
}
  
  // Getting event exprie date & time
  if (!function_exists('mep_get_event_status')) {     
  function mep_get_event_status($startdatetime){
  
    $current = current_time('Y-m-d H:i:s');
    $newformat = date('Y-m-d H:i:s',strtotime($startdatetime));
    
    $datetime1 = new DateTime($newformat);
    $datetime2 = new DateTime($current);
  
    $interval = date_diff($datetime2, $datetime1);
  
    if(current_time('Y-m-d H:i:s') > $newformat){
      return "<span class=err>Expired</span>";
    }
    else{
    $days = $interval->days;
    $hours = $interval->h;
    $minutes = $interval->i;
    if($days>0){ $dd = $days." days "; }else{ $dd=""; }
    if($hours>0){ $hh = $hours." hours "; }else{ $hh=""; }
    if($minutes>0){ $mm = $minutes." minutes "; }else{ $mm=""; }
     return "<span class='active'>$dd $hh $mm</span>";
    }
  }
}
  
if (!function_exists('mep_merge_saved_array')) {  
function mep_merge_saved_array($arr1,$arr2){
    $output = [];
    for ($i=0; $i<count($arr1); $i++) {
        $output[$i] = array_merge($arr1[$i], $arr2[$i]);
    }
    return $output;
}
}  
  
  // Redirect to Checkout after successfuly event registration
  add_filter ('woocommerce_add_to_cart_redirect', 'mep_event_redirect_to_checkout');
  if (!function_exists('mep_event_redirect_to_checkout')) {   
  function mep_event_redirect_to_checkout() {
      global $woocommerce;
      $redirect_status = mep_get_option( 'mep_event_direct_checkout', 'general_setting_sec', 'yes' );
      if($redirect_status=='yes'){
      $checkout_url = wc_get_checkout_url();
      return $checkout_url;
    }
  }
}
  
  add_action('init','mep_include_template_parts');
  if (!function_exists('mep_include_template_parts')) {   
  function mep_include_template_parts(){
          require_once(dirname(__DIR__) . "/inc/template-prts/templating.php");
  }
}


if (!function_exists('mep_template_file_path')) { 
  function mep_template_file_path($file_name){
        // $template_path      = ;
      $template_path      = get_stylesheet_directory().'/mage-events/';
        $default_path       = plugin_dir_path( __DIR__ ) . 'templates/'; 
        $thedir             = is_dir($template_path) ? $template_path : $default_path;
        $themedir           = $thedir.$file_name;
        $the_file_path      = locate_template( array('mage-events/' . $file_name) ) ? $themedir : $default_path.$file_name; 
      return $the_file_path;
  }
}

if (!function_exists('mep_template_part_file_path')) { 
function mep_template_part_file_path($file_name){
      $the_file_path       = plugin_dir_path( __DIR__ ) . 'inc/template-prts/'.$file_name;  
    return $the_file_path;
}
}
  
if (!function_exists('mep_load_events_templates')) { 
  function mep_load_events_templates($template) {
    global $post;
    
    if ($post->post_type == "mep_events"){
      $template = mep_template_part_file_path('single-events.php');
      return $template;
    }  

    if ($post->post_type == "mep_event_speaker"){
      $template = mep_template_file_path('single-speaker.php');
      return $template;
    }  

    if ($post->post_type == "mep_events_attendees"){
      $template = mep_template_part_file_path('single-mep_events_attendees.php');
      return $template;          
    }
  
    return $template;
  }
}
add_filter('single_template', 'mep_load_events_templates');
  
  
  
add_filter('template_include', 'mep_organizer_set_template');
if (!function_exists('mep_organizer_set_template')) {   
  function mep_organizer_set_template( $template ){        
      if( is_tax('mep_org')){
        $template = mep_template_file_path('taxonomy-organozer.php');
      }
      if( is_tax('mep_cat')){
        $template = mep_template_file_path('taxonomy-category.php');
      }    
      return $template;
  }
}
  
if (!function_exists('mep_social_share')) {   
  function mep_social_share(){
  ?>
  <ul class='mep-social-share'>
         <li> <a data-toggle="tooltip" title="" class="facebook" onclick="window.open('https://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" data-original-title="Share on Facebook"><i class="fa fa-facebook"></i></a></li>
          <li><a data-toggle="tooltip" title="" class="twitter" onclick="window.open('https://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title(); ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title(); ?>" data-original-title="Twittet it"><i class="fa fa-twitter"></i></a></li>
          </ul>
  <?php
  }
}

if (!function_exists('mep_calender_date')) { 
  function mep_calender_date($datetime){
    $time       = strtotime($datetime);
    $newdate    = date_i18n('Ymd',$time);
    $newtime    = date('Hi',$time);
    $newformat  = $newdate."T".$newtime."00";
  return $newformat;
  }
}
  
  
if (!function_exists('mep_add_to_google_calender_link')) { 
  function mep_add_to_google_calender_link($pid){
    $event        = get_post($pid);
    $event_meta   = get_post_custom($pid);
    $event_start  = $event_meta['event_start_date'][0].' '.$event_meta['event_start_time'][0];
    $event_end    = $event_meta['event_end_date'][0].' '.$event_meta['event_end_time'][0];
  
  $location = $event_meta['mep_location_venue'][0]." ".$event_meta['mep_street'][0]." ".$event_meta['mep_city'][0]." ".$event_meta['mep_state'][0]." ".$event_meta['mep_postcode'][0]." ".$event_meta['mep_country'][0];
  ob_start();
  require(mep_template_file_path('single/add_calendar.php'));
  ?>

  <script type="text/javascript">
  jQuery(document).ready(function() {
  jQuery("#mep_add_calender_button").click(function () {
  jQuery("#mep_add_calender_links").toggle()
  });
  });
  
  </script>
  <style type="text/css">
    #mep_add_calender_links{    display: none;
      background: transparent;
      margin-top: -7px;
      list-style: navajowhite;
      margin: 0;
      padding: 0;}
  /*  #mep_add_calender_links li{list-style: none !important; line-height: 0.2px; border:1px solid #d5d5d5; border-radius: 10px; margin-bottom: 5px;}
    #mep_add_calender_links a{background: none !important; color: #333 !important; line-height: 0.5px !important; padding:10px; margin-bottom: 3px;}
    #mep_add_calender_links a:hover{color:#ffbe30;}*/
    #mep_add_calender_button{
     /*background: #ffbe30 none repeat scroll 0 0;*/
      border: 0 none;
      border-radius: 50px;
      /*color: #ffffff !important;*/
      display: inline-flex;
      font-size: 14px;
      font-weight: 600;
      overflow: hidden;
      padding: 15px 35px;
      position: relative;
      text-align: center;
      text-transform: uppercase;
      z-index: 1;
      cursor: pointer;
    }
  .mep-default-sidrbar-social .mep-event-meta{text-align: center;}
  </style>
  <?php
    $content = ob_get_clean();
    echo $content;
  }
}
  
  
if (!function_exists('mep_get_item_name')) { 
  function mep_get_item_name($name){
    $explode_name = explode('_', $name, 2);
    $the_item_name = str_replace('-', ' ', $explode_name[0]);
    return $the_item_name;
  }
}  
  
if (!function_exists('mep_get_item_price')) {   
  function mep_get_item_price($name){
    $explode_name = explode('_', $name, 2);
    $the_item_name = str_replace('-', ' ', $explode_name[1]);
    return $the_item_name;
  }
}
  
  
if (!function_exists('mep_get_string_part')) {   
  function mep_get_string_part($data,$string){  
    $pieces = explode(" x ", $data);
  return $pieces[$string]; // piece1
  }
}

  
if (!function_exists('mep_get_event_order_metadata')) {  
  function mep_get_event_order_metadata($id,$part){
  global $wpdb;
  $table_name = $wpdb->prefix . 'woocommerce_order_itemmeta';
  $result = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_item_id=$id" );
  
  foreach ( $result as $page )
  {
    if (strpos($page->meta_key, '_') !== 0) {
     echo mep_get_string_part($page->meta_key,$part).'<br/>';
   }
  }
  }
}
  
  add_action('woocommerce_account_dashboard','mep_ticket_lits_users');
  if (!function_exists('mep_ticket_lits_users')) {    
  function mep_ticket_lits_users(){
  ob_start();
  ?>
  <div class="mep-user-ticket-list">
    <table>
      <tr>
        <th><?php _e('Name','mage-eventpress'); ?></th>
        <th><?php _e('Ticket','mage-eventpress'); ?></th>
        <th><?php _e('Event','mage-eventpress'); ?></th>
        <?php do_action('mep_user_order_list_table_head'); ?>
      </tr>
      <?php 
       $args_search_qqq = array (
                       'post_type'        => array( 'mep_events_attendees' ),
                       'posts_per_page'   => -1,
      'meta_query' => array(
          array(
              'key' => 'ea_user_id',
              'value' => get_current_user_id()
          )
      )
    );
    $loop = new WP_Query( $args_search_qqq );
    while ($loop->have_posts()) {
    $loop->the_post(); 
  $event_id = get_post_meta( get_the_id(), 'ea_event_id', true );
    $event_meta = get_post_custom($event_id);
  
    $time = strtotime($event_meta['event_start_date'][0].' '.$event_meta['event_start_time'][0]);
      $newformat = date('Y-m-d H:i:s',$time);
        if ( time() < strtotime( $newformat ) ) {
            ?>
            <tr>
                <td><?php echo get_post_meta( get_the_id(), 'ea_name', true ); ?></td>
                <td><?php echo get_post_meta( get_the_id(), 'ea_ticket_type', true ); ?></td>
                <td><?php echo get_post_meta( get_the_id(), 'ea_event_name', true ); ?></td>
                <?php do_action('mep_user_order_list_table_row',get_the_id()); ?>
            </tr>
            <?php
        }
    }    
      ?>
    </table>
  </div>
  <?php
  $content = ob_get_clean();
  echo $content;
  }
}

if (!function_exists('mep_event_template_name')) {  
  function mep_event_template_name(){
  
            $template_name = 'index.php';
            $template_path = get_stylesheet_directory().'/mage-events/themes/';
            $default_path = plugin_dir_path( __DIR__ ) . 'templates/themes/'; 
  
          $template = locate_template( array($template_path . $template_name) );
  
         if ( ! $template ) :
           $template = $default_path . $template_name;
         endif;
  
  // echo $template_path;
  if (is_dir($template_path)) {
    $thedir = glob($template_path."*");
  }else{
  $thedir = glob($default_path."*");
  // file_get_contents('./people.txt', FALSE, NULL, 20, 14);
  }
  
  $theme = array();
  foreach($thedir as $filename){
      //Use the is_file function to make sure that it is not a directory.
      if(is_file($filename)){
        $file = basename($filename);
       $naame = str_replace("?>","",strip_tags(file_get_contents($filename, FALSE, NULL, 24, 14))); 
      }   
       $theme[$file] = $naame;
  }
  return $theme;
  }
}
  
  
if (!function_exists('event_single_template_list')) {  
  function event_single_template_list($current_theme){
  $themes = mep_event_template_name();
          $buffer = '<select name="mep_event_template">';
          foreach ($themes as $num=>$desc){
            if($current_theme==$num){ $cc = 'selected'; }else{ $cc = ''; }
              $buffer .= "<option value=$num $cc>$desc</option>";
          }//end foreach
          $buffer .= '</select>';
          echo $buffer;
  }
}


if (!function_exists('mep_title_cutoff_words')) {  
  function mep_title_cutoff_words($text, $length){
      if(strlen($text) > $length) {
          $text = substr($text, 0, strpos($text, ' ', $length));
      }
  
      return $text;
  }
}


if (!function_exists('mep_get_tshirts_sizes')) {  
  function mep_get_tshirts_sizes($event_id){
    $event_meta   = get_post_custom($event_id);
    $tee_sizes  = $event_meta['mep_reg_tshirtsize_list'][0];
    $tszrray = explode(',', $tee_sizes);
  $ts = "";
    foreach ($tszrray as $value) {
      $ts .= "<option value='$value'>$value</option>";
    }
  return $ts;
  }
}  
  

  
  
  
  
if (!function_exists('mep_event_list_price')) {  
  function mep_event_list_price($pid){
  global $post;
    $cur = get_woocommerce_currency_symbol();
    $mep_event_ticket_type = get_post_meta($pid, 'mep_event_ticket_type', true);
    $mep_events_extra_prices = get_post_meta($pid, 'mep_events_extra_prices', true);
    $n_price = get_post_meta($pid, '_price', true);
  
    if($n_price==0){
      $gn_price = "Free";
    }else{
      $gn_price = wc_price($n_price);
    }
  
    // if($mep_events_extra_prices){
    //   $gn_price = $cur.$mep_events_extra_prices[0]['option_price'];
    // }
  
    if($mep_event_ticket_type){
      $gn_price = wc_price($mep_event_ticket_type[0]['option_price_t']);
    }
    
  return $gn_price;
  }
}


if (!function_exists('mep_get_label')) {    
  function mep_get_label($pid,$label_id,$default_text){
   return  mep_get_option( $label_id, 'label_setting_sec', $default_text);
  }
}
  
  // Add the custom columns to the book post type:
  add_filter( 'manage_mep_events_posts_columns', 'mep_set_custom_edit_event_columns' );
  if (!function_exists('mep_set_custom_edit_event_columns')) {     
  function mep_set_custom_edit_event_columns($columns) {
      unset( $columns['date'] );
      $columns['mep_status'] = __( 'Status', 'mage-eventpress' );  
      return $columns;
  }
  }  
  
  if (!function_exists('mep_get_full_time_and_date')) {    
  function mep_get_full_time_and_date($datetime){
     $date_format       = get_option( 'date_format' );
     $time_format       = get_option( 'time_format' );
     $wpdatesettings    = $date_format.'  '.$time_format; 
     $user_set_format   = mep_get_option( 'mep_event_time_format','general_setting_sec','wtss');
  
      if($user_set_format==12){
        echo wp_date('D, d M Y  h:i A', strtotime($datetime));
      }
      if($user_set_format==24){
        echo wp_date('D, d M Y  H:i', strtotime($datetime));
      }
      if($user_set_format=='wtss'){
        echo wp_date($wpdatesettings, strtotime($datetime));
      }
  }
}
  
if (!function_exists('mep_get_only_time')) {   
  function mep_get_only_time($datetime){
    $user_set_format = mep_get_option( 'mep_event_time_format','general_setting_sec','wtss');
    $time_format = get_option( 'time_format' );
       if($user_set_format==12){
        echo date('h:i A', strtotime($datetime));
      }
      if($user_set_format==24){
        echo date('H:i', strtotime($datetime));
      }
      if($user_set_format=='wtss'){
        echo date($time_format, strtotime($datetime));
      }
  }
}   
  
if (!function_exists('mep_get_event_city')) {  
  function mep_get_event_city($id){
  $location_sts = get_post_meta($id,'mep_org_address',true);
  $event_meta = get_post_custom($id);
  if($location_sts){
  $org_arr = get_the_terms( $id, 'mep_org' );
  if(is_array($org_arr) && sizeof($org_arr) > 0 ){
  $org_id = $org_arr[0]->term_id;
    echo "<span>".mep_ev_venue($id).', '.get_term_meta( $org_id, 'org_city', true )."</span>";
  }
  }else{
    echo "<span>".mep_ev_venue($id).', '.$event_meta['mep_city'][0]."</span>";
  }
  }
}  
   
if (!function_exists('mep_get_total_available_seat')) {   
  function mep_get_total_available_seat($event_id, $event_meta){
  $total_seat = mep_event_total_seat($event_id,'total');
  $total_resv = mep_event_total_seat($event_id,'resv');
  $total_sold = mep_ticket_sold($event_id);
  $total_left = $total_seat - ($total_sold + $total_resv);
  return $total_left;
  }
}  
  
  
if (!function_exists('mep_event_location_item')) {    
  function mep_event_location_item($event_id,$item_name){
    return get_post_meta($event_id,$item_name,true);
  }
}

if (!function_exists('mep_event_org_location_item')) { 
  function mep_event_org_location_item($event_id,$item_name){
    $location_sts = get_post_meta($event_id,'mep_org_address',true);
  
      $org_arr      = get_the_terms( $event_id, 'mep_org' );
      if($org_arr){
      $org_id       = $org_arr[0]->term_id;
      return get_term_meta( $org_id, $item_name, true );
  }
  }
}
  
if (!function_exists('mep_get_all_date_time')) { 
  function mep_get_all_date_time( $start_datetime, $more_datetime, $end_datetime ) {
  ob_start();
  
   $date_format = get_option( 'date_format' );
   $time_format = get_option( 'time_format' );
   $wpdatesettings = $date_format.$time_format; 
  $user_set_format = mep_get_option( 'mep_event_time_format','general_setting_sec','wtss');
    ?>
        <ul>
       <?php if($user_set_format==12){ ?>
        <?php $timeformatassettings = 'h:i A'; ?>
            <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime( $start_datetime ) ); ?>   <i class="fa fa-clock-o"></i> <?php echo date( 'h:i A', strtotime( $start_datetime ) ); ?></li>
        <?php } ?>    
      <?php  if($user_set_format==24){ ?>
      <?php $timeformatassettings = 'H:i'; ?>
            <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime( $start_datetime ) ); ?>   <i class="fa fa-clock-o"></i> <?php echo date( 'H:i', strtotime( $start_datetime ) );  ?></li>
        <?php } ?>
      <?php if($user_set_format=='wtss'){ ?>
      <?php $timeformatassettings = get_option( 'time_format' ); ?>
            <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime( $start_datetime ) ); ?>   <i class="fa fa-clock-o"></i> <?php echo date( $time_format, strtotime( $start_datetime ) ); } ?></li>
         }
         }
  
        ?>
      <?php
    
  
      foreach ( $more_datetime as $_more_datetime ) {
        ?>
                <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime( $_more_datetime['event_more_date'] ) ); ?> <i class="fa fa-clock-o"></i> <?php echo date_i18n($timeformatassettings, strtotime( $_more_datetime['event_more_date'] ) ) ?></li>
        <?php
      }
      ?>
  
       <?php 
       if($user_set_format==12){ 
         $timeformatassettings = 'h:i A'; 
       }   
       if($user_set_format==24){ 
       $timeformatassettings = 'H:i'; 
        } 
        if($user_set_format=='wtss'){ 
        $timeformatassettings = get_option( 'time_format' );
        }
  
        ?>
            <li><i class="fa fa-calendar"></i> <?php echo date_i18n($date_format, strtotime( $end_datetime ) ); ?>   <i class="fa fa-clock-o"></i> <?php echo date($timeformatassettings, strtotime( $end_datetime ) ); ?> <span style='font-size: 12px;font-weight: bold;'>(<?php _e('End','mage-eventpress'); ?>)</span></li>
        </ul>
    <?php
  $content = ob_get_clean();
  echo $content;
  }
}


  
if (!function_exists('mep_get_event_locaion_item')) {  
  function mep_get_event_locaion_item($event_id,$item_name){
    if($event_id){
  $location_sts = get_post_meta($event_id,'mep_org_address',true);
  
  
  if($item_name=='mep_location_venue'){
    if($location_sts){
      $org_arr      = get_the_terms( $event_id, 'mep_org' );
     
      if(is_array($org_arr) && sizeof($org_arr)>0 ){
      $org_id       = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_location', true );
      }
    }else{
      return get_post_meta($event_id,'mep_location_venue',true);
    }
    return null;
  }
  
  if($item_name=='mep_location_venue'){
    if($location_sts){
      $org_arr      = get_the_terms( $event_id, 'mep_org' );
  if(is_array($org_arr) && sizeof($org_arr)>0 ){
      $org_id       = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_location', true );
      }
      
    }else{
      return get_post_meta($event_id,'mep_location_venue',true);
    }
  }
  
  
  if($item_name=='mep_street'){
    if($location_sts){
      $org_arr      = get_the_terms( $event_id, 'mep_org' );
      if(is_array($org_arr) && sizeof($org_arr)>0 ){
      $org_id       = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_street', true );
      }
    }else{
      return get_post_meta($event_id,'mep_street',true);
    }
  }
  
  
  if($item_name=='mep_city'){
    if($location_sts){
      $org_arr      = get_the_terms( $event_id, 'mep_org' );
      if(is_array($org_arr) && sizeof($org_arr)>0 ){
      $org_id       = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_city', true );
      }
    }else{
      return get_post_meta($event_id,'mep_city',true);
    }
  }
  
  
  if($item_name=='mep_state'){
    if($location_sts){
      $org_arr      = get_the_terms( $event_id, 'mep_org' );
      if(is_array($org_arr) && sizeof($org_arr)>0 ){
      $org_id       = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_state', true );
      }
    }else{
      return get_post_meta($event_id,'mep_state',true);
    }
  }
  
  
  
  if($item_name=='mep_postcode'){
    if($location_sts){
      $org_arr      = get_the_terms( $event_id, 'mep_org' );
      if(is_array($org_arr) && sizeof($org_arr)>0 ){
      $org_id       = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_postcode', true );
      }
    }else{
      return get_post_meta($event_id,'mep_postcode',true);
    }
  }
  
  
  if($item_name=='mep_country'){
    if($location_sts){
      $org_arr      = get_the_terms( $event_id, 'mep_org' );
      if(is_array($org_arr) && sizeof($org_arr)>0 ){
      $org_id       = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_country', true );
      }
    }else{
      return get_post_meta($event_id,'mep_country',true);
    }
  }
  } 
  }
}
  







if (!function_exists('mep_save_attendee_info_into_cart')) {  
  function mep_save_attendee_info_into_cart($product_id){
  
    $user = array();
  
    if(isset($_POST['user_name'])){
      $mep_user_name          = $_POST['user_name'];
    }else{ $mep_user_name=""; } 
  
    if(isset($_POST['user_email'])){  
      $mep_user_email         = $_POST['user_email'];
    }else{ $mep_user_email=""; } 
  
    if(isset($_POST['user_phone'])){  
      $mep_user_phone         = $_POST['user_phone'];
    }else{ $mep_user_phone=""; } 
  
    if(isset($_POST['user_address'])){  
      $mep_user_address       = $_POST['user_address'];
    }else{ $mep_user_address=""; } 
  
    if(isset($_POST['gender'])){  
      $mep_user_gender        = $_POST['gender'];
    }else{ $mep_user_gender=""; } 
  
    if(isset($_POST['tshirtsize'])){  
      $mep_user_tshirtsize    = $_POST['tshirtsize'];
    }else{ $mep_user_tshirtsize=""; } 
  
    if(isset($_POST['user_company'])){  
      $mep_user_company       = $_POST['user_company'];
    }else{ $mep_user_company=""; } 
  
    if(isset($_POST['user_designation'])){  
      $mep_user_desg          = $_POST['user_designation'];
    }else{ $mep_user_desg=""; } 
  
    if(isset($_POST['user_website'])){  
      $mep_user_website       = $_POST['user_website'];
    }else{ $mep_user_website=""; } 
  
    if(isset($_POST['vegetarian'])){  
      $mep_user_vegetarian    = $_POST['vegetarian'];
    }else{ $mep_user_vegetarian=""; } 
  
  
    
    if(isset($_POST['ticket_type'])){  
      $mep_user_ticket_type   = $_POST['ticket_type'];
    }else{ $mep_user_ticket_type=""; } 
  
  
  
    if(isset($_POST['event_date'])){  
      $event_date   = $_POST['event_date'];
    }else{ $event_date=""; } 
  
  
    if(isset($_POST['mep_event_id'])){  
      $mep_event_id   = $_POST['mep_event_id'];
    }else{ $mep_event_id=""; }
  
  
  
      if ( isset( $_POST['option_qty'] ) ) {
          $mep_user_option_qty = $_POST['option_qty'];
      } else {
          $mep_user_option_qty = "";
      }

  
  
    if(isset($_POST['mep_ucf'])){
      $mep_user_cfd           = $_POST['mep_ucf'];
    }else{
      $mep_user_cfd           = "";
    }
  

// echo $p =  




    if($mep_user_name){ $count_user = count($mep_user_name); } else{ $count_user = 0; }
  
    for ( $iu = 0; $iu < $count_user; $iu++ ) {
      
      if (isset($mep_user_name[$iu])):
        $user[$iu]['user_name'] = stripslashes( strip_tags( $mep_user_name[$iu] ) );
        endif;
  
      if (isset($mep_user_email[$iu])) :
        $user[$iu]['user_email'] = stripslashes( strip_tags( $mep_user_email[$iu] ) );
        endif;
  
      if (isset($mep_user_phone[$iu])) :
        $user[$iu]['user_phone'] = stripslashes( strip_tags( $mep_user_phone[$iu] ) );
        endif;
  
      if (isset($mep_user_address[$iu])) :
        $user[$iu]['user_address'] = stripslashes( strip_tags( $mep_user_address[$iu] ) );
        endif;
  
      if (isset($mep_user_gender[$iu])) :
        $user[$iu]['user_gender'] = stripslashes( strip_tags( $mep_user_gender[$iu] ) );
        endif;
  
      if (isset($mep_user_tshirtsize[$iu])) :
        $user[$iu]['user_tshirtsize'] = stripslashes( strip_tags( $mep_user_tshirtsize[$iu] ) );
        endif;
  
      if (isset($mep_user_company[$iu])) :
        $user[$iu]['user_company'] = stripslashes( strip_tags( $mep_user_company[$iu] ) );
        endif;
  
      if (isset($mep_user_desg[$iu])) :
        $user[$iu]['user_designation'] = stripslashes( strip_tags( $mep_user_desg[$iu] ) );
        endif;
  
      if (isset($mep_user_website[$iu])) :
        $user[$iu]['user_website'] = stripslashes( strip_tags( $mep_user_website[$iu] ) );
        endif;
  
      if (isset($mep_user_vegetarian[$iu])) :
        $user[$iu]['user_vegetarian'] = stripslashes( strip_tags( $mep_user_vegetarian[$iu] ) );
        endif;
  
      if (isset($mep_user_ticket_type[$iu])) :
        $user[$iu]['user_ticket_type'] = stripslashes( strip_tags( $mep_user_ticket_type[$iu] ) );
        endif;    
  
      // if ($ticket_price) :
        // $user[$iu]['user_ticket_price'] =  mep_get_event_ticket_price_by_name($mep_event_id,$mep_user_ticket_type);
        // endif;    
  
      if (isset($event_date[$iu])) :
        $user[$iu]['user_event_date'] = stripslashes( strip_tags( $event_date[$iu] ) );
        endif;    
  
      if (isset($mep_event_id[$iu])) :
        $user[$iu]['user_event_id'] = stripslashes( strip_tags( $mep_event_id[$iu] ) );
        endif;
  
        if ( isset( $mep_user_option_qty[ $iu ] ) ) :
            $user[ $iu ]['user_ticket_qty'] = stripslashes( strip_tags( $mep_user_option_qty[ $iu ] ) );
        endif;
  
  
    $mep_form_builder_data = get_post_meta($product_id, 'mep_form_builder_data', true);
    if ( $mep_form_builder_data ) {
      foreach ( $mep_form_builder_data as $_field ) {
            $user[$iu][$_field['mep_fbc_id']] = stripslashes( strip_tags( $_POST[$_field['mep_fbc_id']][$iu] ) );
        }
      }
    }
    return apply_filters('mep_cart_user_data_prepare',$user,$product_id);
  }
}  
  
  
if (!function_exists('mep_wc_price')) { 
  function mep_wc_price( $price, $args = array() ) {
    $args = apply_filters(
      'wc_price_args', wp_parse_args(
        $args, array(
          'ex_tax_label'       => false,
          'currency'           => '',
          'decimal_separator'  => wc_get_price_decimal_separator(),
          'thousand_separator' => wc_get_price_thousand_separator(),
          'decimals'           => wc_get_price_decimals(),
          'price_format'       => get_woocommerce_price_format(),
        )
      )
    );
  
    $unformatted_price = $price;
    $negative          = $price < 0;
    $price             = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
    $price             = apply_filters( 'formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );
  
    if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
      $price = wc_trim_zeros( $price );
    }
  
    $formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], '' . '' . '', $price );
    $return          = '' . $formatted_price . '';
  
    if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
      $return .= '' . WC()->countries->ex_tax_or_vat() . '';
    }
  
    /**
     * Filters the string of price markup.
     *
     * @param string $return            Price HTML markup.
     * @param string $price             Formatted price.
     * @param array  $args              Pass on the args.
     * @param float  $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
     */
    return apply_filters( 'mep_wc_price', $return, $price, $args, $unformatted_price );
  }
}
  
  
if (!function_exists('mep_get_event_total_seat')) { 
  function mep_get_event_total_seat($event_id,$m=null,$t=null){

  $total_seat = apply_filters( 'mep_event_total_seat_counts', mep_event_total_seat($event_id,'total'), $event_id );
  $total_resv = apply_filters( 'mep_event_total_resv_seat_count', mep_event_total_seat($event_id,'resv'), $event_id );
  $total_sold = mep_ticket_sold($event_id);
  $total_left = $total_seat - ($total_sold + $total_resv);
  if($t=='multi'){
    ?>
      <span style="background: #dc3232;color: #fff;padding: 5px 10px;"> <?php echo ($total_seat * $m) - ($total_sold + $total_resv); ?>/<?php echo $total_seat * $m; ?> </span>
    <?php
  }else{
    ?>
  <span style="background: #dc3232;color: #fff;padding: 5px 10px;"> <?php echo $total_left; ?>/<?php echo $total_seat; ?> </span>
    <?php
  }
  }
}
  
  
if (!function_exists('mep_reset_event_booking')) {   
  function mep_reset_event_booking($event_id){
    $mep_event_ticket_type = get_post_meta($event_id, 'mep_event_ticket_type', true);
    if($mep_event_ticket_type){
        foreach ( $mep_event_ticket_type as $field ) {
          $qm = $field['option_name_t'];
          $tesqn = $event_id.str_replace(' ', '', $qm);
          $reset =  update_post_meta($event_id,"mep_xtra_$tesqn",0);
        }
      // if($reset){ return 'Reset Done!'; }
    }else{
      $reset =  update_post_meta($event_id,"total_booking",0);
      // if($reset){ return 'Reset Done!'; }
    }
    $args_search_qqq = array (
                       'post_type'        => array( 'mep_events_attendees' ),
                       'posts_per_page'   => -1,
                       'post_status'      => 'publish',
                       'meta_query'       => array(
                          array(
                              'key'       => 'ea_event_id',
                              'value'     => $event_id,
                              'compare'   => '='
                          )
                      )                      
                  );  
    $loop = new WP_Query($args_search_qqq);
    while ($loop->have_posts()) {
    $loop->the_post(); 
      $post_id = get_the_id();
      $status = 'trash';
      $current_post = get_post( $post_id, 'ARRAY_A' );
      $current_post['post_status'] = $status;
      wp_update_post($current_post);
    }
  }
}
  
  
  // Add the custom columns to the book post type:
  add_filter( 'manage_mep_events_posts_columns', 'mep_set_custom_mep_events_columns' );
  if (!function_exists('mep_set_custom_mep_events_columns')) {     
  function mep_set_custom_mep_events_columns($columns) {
      $columns['mep_event_seat'] = __( 'Seats', 'mage-eventpress' );
      return $columns;
  }
}
  
  // Add the data to the custom columns for the book post type:
  add_action( 'manage_mep_events_posts_custom_column' , 'mep_mep_events_column', 10, 2 );
  if (!function_exists('mep_mep_events_column')) {     
  function mep_mep_events_column( $column, $post_id ) {
      switch ( $column ) {
  
          case 'mep_event_seat' : 
            $recurring            = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';
  
  
            if($recurring == 'yes'){
              $event_more_dates    = count(get_post_meta($post_id,'mep_event_more_date',true))+1;
              echo mep_get_event_total_seat($post_id,$event_more_dates,'multi'); 
            }else{
              echo mep_get_event_total_seat($post_id); 
            }          
            
          break; 
      }
  }
}
  
if (!function_exists('mep_get_term_as_class')) {    
  function mep_get_term_as_class($post_id,$taxonomy){
      $tt     = get_the_terms($post_id,$taxonomy);
      if($tt){
      $t_class = array();
      foreach($tt as $tclass){
          $t_class[] = 'mage-'.$tclass->slug;         
      }
      $main_class = implode(' ',$t_class);
      return $main_class;
    }else{
      return null;
    }
  }
}
  
if (!function_exists('mep_ticket_type_sold')) {   
  function mep_ticket_type_sold($event_id,$type,$date=''){
    
  if($date){
    $args = array(
            'post_type' => 'mep_events_attendees',
            'posts_per_page' => -1,
  
        'meta_query' => array(    
          'relation' => 'AND',
          array(    
            'relation' => 'AND',           
            array(
              'key'       => 'ea_event_id',
              'value'     => $event_id,
              'compare'   => '='
            ),		        
            array(
              'key'       => 'ea_ticket_type',
              'value'     => $type,
              'compare'   => '='          
            ),		        
            array(
              'key'       => 'ea_event_date',
              'value'     => $date,
              'compare'   => 'LIKE'
            )
            ),array(    
              'relation' => 'OR',           
              array(
                'key'       => 'ea_order_status',
                'value'     => 'processing',
                'compare'   => '='
              ),		        
              array(
                'key'       => 'ea_order_status',
                'value'     => 'completed',
                'compare'   => '='
              )
              )
          )            
        ); 
     }else{
         $args = array(
            'post_type' => 'mep_events_attendees',
            'posts_per_page' => -1,
            'meta_query' => array(    
              'relation' => 'AND',
              array(    
                'relation' => 'AND',           
                array(
                  'key'       => 'ea_event_id',
                  'value'     => $event_id,
                  'compare'   => '='
                ),		        
                array(
                  'key'       => 'ea_ticket_type',
                  'value'     => $type,
                  'compare'   => '='          
                )
                ),array(    
                  'relation' => 'OR',           
                  array(
                    'key'       => 'ea_order_status',
                    'value'     => 'processing',
                    'compare'   => '='
                  ),		        
                  array(
                    'key'       => 'ea_order_status',
                    'value'     => 'completed',
                    'compare'   => '='
                  )
                  )
              )            
        ); 
     }
     $loop = new WP_Query($args);
    return $loop->post_count;
  }
}  
  
if (!function_exists('mep_extra_service_sold')) { 
  function mep_extra_service_sold($event_id,$type,$date){
      //echo $date;
    $args = array(
            'post_type' => 'mep_extra_service',
            'posts_per_page' => -1,
  
        'meta_query' => array(    
          'relation' => 'AND',
          array(    
            'relation' => 'AND',           
            array(
              'key'       => 'ea_extra_service_event',
              'value'     => $event_id,
              'compare'   => '='
            ),		        
            array(
              'key'       => 'ea_extra_service_name',
              'value'     => $type,
              'compare'   => '='          
            ),		        
            array(
              'key'       => 'ea_extra_service_event_date',
              'value'     => $date,
              'compare'   => 'LIKE'
            )
            ),array(    
              'relation' => 'OR',           
              array(
                'key'       => 'ea_extra_service_order_status',
                'value'     => 'processing',
                'compare'   => '='
              ),		        
              array(
                'key'       => 'ea_extra_service_order_status',
                'value'     => 'completed',
                'compare'   => '='
              )
              )
          )            
        );            
     $loop = new WP_Query($args);
     $count = 0;
     foreach($loop->posts as $sold_service){
         $pid = $sold_service->ID;
         $count = $count + get_post_meta($pid,'ea_extra_service_qty',true);
     }
     
     
     
    return $count;
  }
}
  
if (!function_exists('mep_ticket_sold')) { 
  function mep_ticket_sold($event_id){
    $event_start_date = date('Y-m-d',strtotime(get_post_meta($event_id,'event_start_date',true)));
    // $get_ticket_type_list = get_post_meta($event_id,'mep_event_ticket_type',true) ? get_post_meta($event_id,'mep_event_ticket_type',true) : array();
    $get_ticket_type_list = metadata_exists( 'post', $event_id, 'mep_event_ticket_type' ) ? get_post_meta($event_id,'mep_event_ticket_type',true) : array();
    
    $recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
    
    
    $sold = 0;
    if(is_array($get_ticket_type_list) && sizeof($get_ticket_type_list) > 0){
        foreach($get_ticket_type_list as $ticket_type){
          $sold = $sold + mep_ticket_type_sold($event_id,$ticket_type['option_name_t'],$event_start_date);
        }
    }
    
    if($recurring == 'yes'){
    //   $mep_event_more_date = get_post_meta($event_id,'mep_event_more_date',true);
      $mep_event_more_date = metadata_exists( 'post', $event_id, 'mep_event_more_date' ) ? get_post_meta($event_id,'mep_event_more_date',true) : array();
     if(is_array($mep_event_more_date) && sizeof($mep_event_more_date) > 0){
          foreach ($mep_event_more_date as $md) {
               if(is_array($get_ticket_type_list) && sizeof($get_ticket_type_list) > 0){
                      foreach($get_ticket_type_list as $ticket_type){
                        $sold = $sold + mep_ticket_type_sold($event_id,$ticket_type['option_name_t'],$md['event_more_start_date']);
                      }
               }
          }
       }
    }

    return $sold;
  }
}  
  
  
if (!function_exists('mep_event_total_seat')) { 
  function mep_event_total_seat($event_id,$type){
    $mep_event_ticket_type = get_post_meta($event_id, 'mep_event_ticket_type', true);
    // print_r($mep_event_ticket_type);
    $total = 0;
    if(is_array($mep_event_ticket_type) && sizeof($mep_event_ticket_type) > 0){
    foreach ( $mep_event_ticket_type as $field ) {
        if($type == 'total'){
          $total_name = array_key_exists('option_qty_t', $field) ? (int) $field['option_qty_t'] : 0;
        }elseif($type == 'resv'){
          $total_name = array_key_exists('option_rsv_t', $field) ? (int) $field['option_rsv_t'] : 0;
        }
      $total = $total_name + $total;
    }
    }
    return $total;
  }
}  
  
  
  
  
  
if (!function_exists('get_mep_datetime')) {   
function get_mep_datetime($date,$type){
  $date_format        = get_option( 'date_format' );
  $time_format        = get_option( 'time_format' );
  $wpdatesettings     = $date_format.'  '.$time_format; 
  $timezone           = wp_timezone_string();
  $timestamp          = strtotime( $date . ' '. $timezone);

    if($type == 'date'){
        return wp_date( $date_format, $timestamp );    
    }
    if($type == 'date-time'){
         return wp_date( $wpdatesettings, $timestamp );    
    }
    if($type == 'date-text'){
         
        return wp_date( $date_format, $timestamp );    
    }
  
    if($type == 'date-time-text'){
         return wp_date( $wpdatesettings, $timestamp, wp_timezone() );    
    }
    if($type == 'time'){
        return wp_date( $time_format, $timestamp, wp_timezone());
    }
    
    if($type == 'day'){
         return wp_date( 'd', $timestamp );    
    }
    if($type == 'month'){
         return wp_date( 'M', $timestamp );    
    }
 }
}
  
if (!function_exists('mep_get_event_upcomming_date')) { 
 function mep_get_event_upcomming_date($event_id,$type){
  
         $recurring              = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
         $more_date              = get_post_meta($event_id,'mep_event_more_date',true) ? get_post_meta($event_id,'mep_event_more_date',true) : array();
         $start_datetime            = get_post_meta($event_id,'event_start_datetime',true);
         $start_date                = date('Y-m-d H:i:s',strtotime(get_post_meta($event_id,'event_start_datetime',true)));
         $end_date                  = get_post_meta($event_id,'event_end_date',true);
         $end_datetime              = get_post_meta($event_id,'event_end_datetime',true);
         $show_multidate             = mep_get_option('mep_date_list_in_event_listing', 'general_setting_sec', 'no');
     
    //     if (strtotime(current_time('Y-m-d H:i')) < strtotime($start_datetime)) {
    
    $all_datetime = array($start_date);
        
    if(sizeof($more_date) > 0){
        foreach($more_date as $mdate){
            $all_datetime[] = date('Y-m-d H:i:s',strtotime($mdate['event_more_start_date'].' '.$mdate['event_more_start_time']));
        }
    }
    
    $adt = [];
    foreach($all_datetime as $ald){
        if (strtotime(current_time('Y-m-d H:i')) < strtotime($ald)) {
            $adt[] = $ald;
        }
    }
    if(sizeof($adt) > 0){
      return get_mep_datetime($adt[0],$type);
    }
 }
}
  
  
if (!function_exists('mep_on_post_publish')) { 
  function mep_on_post_publish( $post_id, $post, $update ) {
    if ($post->post_type == 'mep_events' && $post->post_status == 'publish' && empty(get_post_meta( $post_id, 'check_if_run_once' ))) {
  
      // ADD THE FORM INPUT TO $new_post ARRAY
      $new_post = array(
        'post_title'    =>   $post->post_title,
        'post_content'  =>   '',
        'post_name'     =>   uniqid(),
        'post_category' =>   array(),  // Usable for custom taxonomies too
        'tags_input'    =>   array(),
        'post_status'   =>   'publish', // Choose: publish, preview, future, draft, etc.
        'post_type'     =>   'product'  //'post',page' or use a custom post type if you want to
        );
        //SAVE THE POST
        $pid                = wp_insert_post($new_post);
        $product_type = mep_get_option('mep_event_product_type', 'general_setting_sec','yes');
        update_post_meta( $post_id, 'link_wc_product', $pid );
        update_post_meta( $pid, 'link_mep_event', $post_id );
        update_post_meta( $pid, '_price', 0.01 );
        update_post_meta( $pid, '_sold_individually', 'yes' );
        update_post_meta( $pid, '_virtual', $product_type );
        $terms = array( 'exclude-from-catalog', 'exclude-from-search' );
        wp_set_object_terms( $pid, $terms, 'product_visibility' );
        update_post_meta( $post_id, 'check_if_run_once', true );
    }
  }
}
  add_action(  'wp_insert_post',  'mep_on_post_publish', 10, 3 );
  
  if (!function_exists('mep_count_hidden_wc_product')) {   
  function mep_count_hidden_wc_product($event_id){
    $args = array(
      'post_type'      => 'product',
      'posts_per_page' => -1,            
          'meta_query' => array(		        					 
              array(
                  'key'       => 'link_mep_event',
                  'value'     => $event_id,
                  'compare'   => '='
              )            
      )            
  );                
  $loop = new WP_Query($args);
  print_r($loop->posts);
  return $loop->post_count;
  }
}
  
  add_action('save_post','mep_wc_link_product_on_save',99,1);
  if (!function_exists('mep_wc_link_product_on_save')) {   
  function mep_wc_link_product_on_save($post_id){
  
    if (get_post_type($post_id) == 'mep_events') { 
  
      if ( ! isset( $_POST['mep_event_reg_btn_nonce'] ) ||
      ! wp_verify_nonce( $_POST['mep_event_reg_btn_nonce'], 'mep_event_reg_btn_nonce' ) )
        return;
      
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
      
      if (!current_user_can('edit_post', $post_id))
        return;
        $event_name = get_the_title($post_id);
  
        if(mep_count_hidden_wc_product($post_id) == 0 || empty(get_post_meta($post_id,'link_wc_product',true))){
          mep_create_hidden_event_product($post_id,$event_name);
        }
  
        $product_id = get_post_meta($post_id,'link_wc_product',true) ? get_post_meta($post_id,'link_wc_product',true) : $post_id;
        set_post_thumbnail( $product_id, get_post_thumbnail_id($post_id) );
        wp_publish_post( $product_id );
        
          $product_type               = mep_get_option('mep_event_product_type', 'general_setting_sec','yes');
   
          $_tax_status                = isset($_POST['_tax_status']) ? strip_tags($_POST['_tax_status']) : 'none';
          $_tax_class                 = isset($_POST['_tax_class']) ? strip_tags($_POST['_tax_class']) : '';
      
          $update__tax_status         = update_post_meta( $product_id, '_tax_status', $_tax_status);
          $update__tax_class          = update_post_meta( $product_id, '_tax_class', $_tax_class);
          $update__tax_class          = update_post_meta( $product_id, '_stock_status', 'instock');
          $update__tax_class          = update_post_meta( $product_id, '_manage_stock', 'no');
          $update__tax_class          = update_post_meta( $product_id, '_virtual', $product_type);
          $update__tax_class          = update_post_meta( $product_id, '_sold_individually', 'yes');
        
        
        
        // Update post
        $my_post = array(
          'ID'           => $product_id,
          'post_title'   => $event_name, // new title
          'post_name' =>  uniqid()// do your thing here
        );
  
        // unhook this function so it doesn't loop infinitely
        remove_action('save_post', 'mep_wc_link_product_on_save');
        // update the post, which calls save_post again
        wp_update_post( $my_post );
        // re-hook this function
        add_action('save_post', 'mep_wc_link_product_on_save');
        // Update the post into the database
       
  
    }
  
  }
  }  
  
  
  add_action('parse_query', 'mep_product_tags_sorting_query');
  if (!function_exists('mep_product_tags_sorting_query')) {     
  function mep_product_tags_sorting_query($query) {
      global $pagenow;
      $taxonomy  = 'product_visibility';
      $q_vars    = &$query->query_vars;
      if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == 'product') {
          $tax_query = array([
            'taxonomy' => 'product_visibility',
            'field' => 'slug',
            'terms' => 'exclude-from-catalog',
            'operator' => 'NOT IN',
            ]);          
            $query->set( 'tax_query', $tax_query );
      }
  
  }
}
  
add_action('wp','mep_hide_hidden_product_from_single');
if (!function_exists('mep_hide_hidden_product_from_single')) {  
function mep_hide_hidden_product_from_single(){
  global $post,$wp_query;
  if(is_product()){
   $post_id = $post->ID;
   $visibility =  get_the_terms( $post_id, 'product_visibility' );
    if($visibility[0]->name == 'exclude-from-catalog'){
      $check_event_hidden = get_post_meta($post_id,'link_mep_event',true) ? get_post_meta($post_id,'link_mep_event',true) : 0;
      if($check_event_hidden > 0){
      $wp_query->set_404();
      status_header( 404 );
      get_template_part( 404 ); 
      exit();
      }
    }
  }
}
}


  
if (!function_exists('get_event_list_js')) {   
  function get_event_list_js($id,$event_meta,$currency_pos){
      ob_start();
  ?>
  <script>
  jQuery(document).ready( function() {
  
  
  
  jQuery(document).on("change", ".etp_<?php echo $id; ?>", function() {
      var sum = 0;
      jQuery(".etp_<?php echo $id; ?>").each(function(){
          sum += +jQuery(this).val();
      });
      jQuery("#ttyttl_<?php echo $id; ?>").html(sum);
  });
  
  jQuery(".extra-qty-box_<?php echo $id; ?>").on('change', function() {
          var sum = 0;
          var total = <?php if($event_meta['_price'][0]){ echo $event_meta['_price'][0]; }else{ echo 0; } ?>;
  
          jQuery('.price_jq_<?php echo $id; ?>').each(function () {
              var price = jQuery(this);
              var count = price.closest('tr').find('.extra-qty-box_<?php echo $id; ?>');
              sum = (price.html() * count.val());
              total = total + sum;
              // price.closest('tr').find('.cart_total_price').html(sum + "â‚´");
  
          });
  
          jQuery('#usertotal_<?php echo $id; ?>').html("<?php if($currency_pos=="left"){ echo get_woocommerce_currency_symbol(); } ?>" + total + "<?php if($currency_pos=="right"){ echo get_woocommerce_currency_symbol(); } ?>");
          jQuery('#rowtotal_<?php echo $id; ?>').val(total);
  
      }).change(); //trigger change event on page load
  
  
  <?php 
  $mep_event_ticket_type = get_post_meta($id, 'mep_event_ticket_type', true);
  if($mep_event_ticket_type){
  $count =1;
  foreach ( $mep_event_ticket_type as $field ) {
  $qm = $field['option_name_t'];
  ?>
  
  //jQuery('.btn-mep-event-cart').hide();
  
  jQuery('.btn-mep-event-cart_<?php echo $id; ?>').attr('disabled','disabled');
  
  jQuery('#eventpxtp_<?php echo $id; ?>_<?php echo $count; ?>').on('change', function () {
          
          var inputs = jQuery("#ttyttl_<?php echo $id; ?>").html() || 0;
          var inputs = jQuery('#eventpxtp_<?php echo $id; ?>_<?php echo $count; ?>').val() || 0;
          var input = parseInt(inputs);
          var children=jQuery('#dadainfo_<?php echo $count; ?> > div').length || 0; 
             
          jQuery(document).on("change", ".etp_<?php echo $id; ?>", function() {
              var TotalQty = 0;
              jQuery(".etp_<?php echo $id; ?>").each(function(){
              TotalQty += +jQuery(this).val();
              });
              //alert(sum);
  
              if(TotalQty == 0){
                  //jQuery('.btn-mep-event-cart').hide();
                  jQuery('.btn-mep-event-cart_<?php echo $id; ?>').attr('disabled','disabled');
                  jQuery('#mep_btn_notice_<?php echo $id; ?>').show();
              }else{
                  //jQuery('.btn-mep-event-cart').show();
                  jQuery('.btn-mep-event-cart_<?php echo $id; ?>').removeAttr('disabled');
                  jQuery('#mep_btn_notice_<?php echo $id; ?>').hide();
              }     
  
          });
  
          if(input < children){
              jQuery('#dadainfo_<?php echo $count; ?>').empty();
              children=0;
          }
          
          for (var i = children+1; i <= input; i++) {
              jQuery('#dadainfo_<?php echo $count; ?>').append(
              jQuery('<div/>')
                  .attr("id", "newDiv" + i)
                  .html("<?php do_action("mep_reg_fields",$id); ?>")
                  );
          }
      });
  <?php 
      $count++;
      }
   }else{
  ?>
  
  jQuery('#mep_btn_notice_<?php echo $id; ?>').hide();
  
  jQuery('#quantity_5a7abbd1bff73').on('change', function () {        
          var input = jQuery('#quantity_5a7abbd1bff73').val() || 0;
          var children=jQuery('#divParent > div').length || 0;     
          
          if(input < children){
              jQuery('#divParent').empty();
              children=0;
          }        
          for (var i = children+1; i <= input; i++) {
              jQuery('#divParent').append(
              jQuery('<div/>')
                  .attr("id", "newDiv" + i)
                  .html("<?php do_action('mep_reg_fields',$id); ?>")
                  );
          }
      });
  <?php
  } 
  ?>
  });
  </script>
  
  <?php
  echo $content = ob_get_clean();
  }
}
  
if (!function_exists('mep_set_email_content_type')) {   
function mep_set_email_content_type(){
      return "text/html";
  }
}
add_filter( 'wp_mail_content_type','mep_set_email_content_type' );
  
  
add_filter('woocommerce_cart_item_price', 'mep_avada_mini_cart_price_fixed', 100, 3);
if (!function_exists('mep_avada_mini_cart_price_fixed')) {   
function mep_avada_mini_cart_price_fixed($price,$cart_item,$r){
    $price = wc_price($cart_item['line_total']);
    return $price;
  }
}  
  
if (!function_exists('mage_array_strip')) {     
function mage_array_strip($string, $allowed_tags = NULL){
    if (is_array($string)){
        foreach ($string as $k => $v){
          $string[$k] = mage_array_strip($v, $allowed_tags);
        }
      return $string;
    }
    return strip_tags($string, $allowed_tags);
 }
}

/**
 * The Giant SEO Plugin Yoast PRO doing some weird thing and that is its auto create a 301 redirect url when delete a post its causing our event some issue Thats why i disable those part for our event post type with the below filter hoook which is provide by Yoast.
 */
add_filter('wpseo_premium_post_redirect_slug_change', '__return_true' );                    
add_filter('wpseo_premium_term_redirect_slug_change', '__return_true' );
add_filter('wpseo_enable_notification_term_slug_change','__return_false');

/**
 * The below function will add the event more date list into the event list shortcode, Bu default it will be hide with a Show Date button, after click on that button it will the full list. 
 */
add_action('mep_event_list_loop_footer','mep_event_recurring_date_list_in_event_list_loop');
if (!function_exists('mep_event_recurring_date_list_in_event_list_loop')) {  
function mep_event_recurring_date_list_in_event_list_loop($event_id){
        $recurring              = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
        $more_date              = get_post_meta($event_id,'mep_event_more_date',true);
         $start_datetime            = get_post_meta($event_id,'event_start_datetime',true);
         $start_date                = get_post_meta($event_id,'event_start_date',true);
         $end_date                  = get_post_meta($event_id,'event_end_date',true);
         $end_datetime              = get_post_meta($event_id,'event_end_datetime',true);
         $show_multidate             = mep_get_option('mep_date_list_in_event_listing', 'general_setting_sec', 'no');
                            
       if(is_array($more_date) && sizeof($more_date) > 0){

        ?>
         <ul class='mep-more-date-lists mep-more-date-lists<?php echo $event_id; ?>'>
         <?php
                if (strtotime(current_time('Y-m-d H:i')) < strtotime($start_datetime)) {
            ?>
            <!--li><span class='mep-more-date'><i class="fa fa-calendar"></i> <?php echo get_mep_datetime($start_datetime, 'date-text'); ?></span> <span class='mep-more-time'><i class="fa fa-clock-o"></i> <?php echo get_mep_datetime($start_datetime, 'time'); ?> - <?php if ($start_date != $end_date) {
                    echo get_mep_datetime($end_datetime, 'date-text') . ' - ';
                }
                echo get_mep_datetime($end_datetime, 'time'); ?></span></li-->
            <?php
        }
        
        foreach ($more_date as $_more_date) {
            if (strtotime(current_time('Y-m-d H:i')) < strtotime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'])) {
             
                    ?>
                    <li><span class='mep-more-date'><i class="fa fa-calendar"></i> <?php echo get_mep_datetime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'date-text'); ?></span> <span class='mep-more-time'><i class="fa fa-clock-o"></i> <?php echo get_mep_datetime($_more_date['event_more_start_date'] . ' ' . $_more_date['event_more_start_time'], 'time'); ?> - <?php if ($_more_date['event_more_start_date'] != $_more_date['event_more_end_date']) {
                            echo get_mep_datetime($_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'date-text') . ' - ';
                        }
                        echo get_mep_datetime($_more_date['event_more_end_date'] . ' ' . $_more_date['event_more_end_time'], 'time'); ?></span></li>
                    <?php                                 
            }
        }
        echo '</ul>';
        ?>
        <?php if($show_multidate == 'yes'){ ?><span id="show_event_schdule<?php echo $event_id; ?>" class='mep_more_date_btn mep-tem3-title-sec'><?php echo mep_get_option('mep_event_view_more_date_btn_text', 'label_setting_sec', __('View More Date', 'mage-eventpress')); //_e('View More Date','mage-eventpress'); ?></span><?php } ?>
        <span id="hide_event_schdule<?php echo $event_id; ?>" class='mep_more_date_btn mep-tem3-title-sec'><?php echo mep_get_option('mep_event_hide_date_list_btn_text', 'label_setting_sec', __('Hide Date Lists', 'mage-eventpress')); // _e('Hide Date Lists','mage-eventpress'); ?></span>
        <script>
            jQuery('.mep-more-date-lists<?php echo $event_id; ?>, #hide_event_schdule<?php echo $event_id; ?>').hide();
            
            jQuery('#show_event_schdule<?php echo $event_id; ?>').click(function(){
                 jQuery(this).hide()
                 jQuery('.mep-more-date-lists<?php echo $event_id; ?>, #hide_event_schdule<?php echo $event_id; ?>').show(100);
            });
            jQuery('#hide_event_schdule<?php echo $event_id; ?>').click(function(){
                 jQuery(this).hide()
                 jQuery('.mep-more-date-lists<?php echo $event_id; ?>').hide(100);
                 jQuery('#show_event_schdule<?php echo $event_id; ?>').show();
            });            
        </script>
        <?php
       }
}
}


if (!function_exists('mep_event_get_the_content')) {  
function mep_event_get_the_content( $post = 0 ){
  $post = get_post( $post );
  return ( !empty(apply_filters('the_content', $post->post_content)) );
}
}


/**
 * This the function which will create the Rich Text Schema For each event into the <head></head> section.
 */
add_action('wp_head','mep_event_rich_text_data');
if (!function_exists('mep_event_rich_text_data')) {  
function mep_event_rich_text_data(){
    global $post;

    if(is_single()){
      $event_id = $post->ID;
if($event_id && get_post_type($event_id) == 'mep_events'){

        $event_name = get_the_title($event_id);
        $event_start_date = get_post_meta($post->ID,'event_start_datetime',true) ? get_post_meta($post->ID,'event_start_datetime',true) : '';
        $event_end_date = get_post_meta($post->ID,'event_end_datetime',true) ? get_post_meta($post->ID,'event_end_datetime',true) : '';
        $event_rt_status = get_post_meta($post->ID,'mep_rt_event_status',true) ? get_post_meta($post->ID,'mep_rt_event_status',true) : 'EventRescheduled';
        $event_rt_atdnce_mode = get_post_meta($post->ID,'mep_rt_event_attandence_mode',true) ? get_post_meta($post->ID,'mep_rt_event_attandence_mode',true) : 'OfflineEventAttendanceMode';
        $event_rt_prv_date = get_post_meta($post->ID,'mep_rt_event_prvdate',true) ? get_post_meta($post->ID,'mep_rt_event_prvdate',true) : $event_start_date;
        $terms      = get_the_terms( $event_id, 'mep_org' );
        $org_name   = is_array($terms) && sizeof($terms) > 0 ? $terms[0]->name : 'No Performer';
        ob_start();
        
        ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Event",
      "name": "<?php echo $event_name; ?>",
      "startDate": "<?php echo $event_start_date; ?>",
      "endDate": "<?php echo $event_end_date; ?>",
      "eventStatus": "https://schema.org/<?php echo $event_rt_status; ?>",
      "eventAttendanceMode": "https://schema.org/<?php echo $event_rt_atdnce_mode; ?>",
      "previousStartDate": "<?php echo $event_rt_prv_date; ?>",
      "location": {
        "@type": "Place",
        "name": "<?php echo mep_get_event_location($event_id); ?>",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "<?php echo mep_get_event_location_street($event_id); ?>",
          "addressLocality": "<?php echo mep_get_event_location_city($event_id); ?>",
          "postalCode": "<?php echo mep_get_event_location_postcode($event_id) ?>",
          "addressRegion": "<?php echo mep_get_event_location_state($event_id) ?>",
          "addressCountry": "<?php echo mep_get_event_location_country($event_id) ?>"
        }
      },
      "image": [
        "<?php echo get_the_post_thumbnail_url($event_id,'full'); ?>"
       ],
      "description": "<?php echo get_the_excerpt($event_id); ?>",
      "performer": {
        "@type": "PerformingGroup",
        "name": "<?php echo $org_name; ?>"
      }
    }
    </script>        
        
        <?php
        echo $content = ob_get_clean();
  }
    }
}
}

/**
 * We added event id with every order for using in the attendee & seat inventory calculation, but this info was showing in the thank you page, so i decided to hide this, and here is the fucntion which will hide the event id from the thank you page.
 */
add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'mep_hide_event_order_meta_in_emails' );
if (!function_exists('mep_hide_event_order_meta_in_emails')) { 
function mep_hide_event_order_meta_in_emails( $meta ) {
    if( ! is_admin() ) {
        $criteria = array(  'key' => 'event_id' );
        $meta = wp_list_filter( $meta, $criteria, 'NOT' );
    }
    return $meta;
}
}
add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'mep_hide_event_order_data_from_thankyou_and_email', 10, 1 );
if (!function_exists('mep_hide_event_order_data_from_thankyou_and_email')) { 
function mep_hide_event_order_data_from_thankyou_and_email($formatted_meta){
  $hide_location_status  = mep_get_option('mep_hide_location_from_order_page', 'general_setting_sec', 'no');
  $hide_date_status  = mep_get_option('mep_hide_date_from_order_page', 'general_setting_sec', 'no');
  $hide_location  = $hide_location_status == 'yes' ? array('Location') : array();
  $hide_date  = $hide_date_status == 'yes' ? array('Date') : array();
  $default = array('event_id');
  $default = array_merge($default,$hide_date);

  $hide_them = array_merge($default,$hide_location);

    $temp_metas = [];
    foreach($formatted_meta as $key => $meta) {
        if ( isset( $meta->key ) && ! in_array( $meta->key, $hide_them ) ) {
            $temp_metas[ $key ] = $meta;
        }
    }
    return $temp_metas;
}
}


/**
 * This will create a new section Custom CSS into the Event Settings Page, I write this code here instead of the Admin Settings Class because of YOU! Yes who is reading this comment!! to get the clear idea how you can craete your own settings section and settings fields by using the filter hook from any where or your own plugin. Thanks For reading this comment. Cheers!!
 */
add_filter('mep_settings_sec_reg','mep_custom_css_settings_reg',90);
if (!function_exists('mep_custom_css_settings_reg')) { 
function mep_custom_css_settings_reg($default_sec){
    $sections = array(
        array(
            'id' => 'mep_settings_custom_css',
            'title' => __( 'Custom CSS', 'mage-eventpress' )
        )
    );
  return array_merge($default_sec,$sections);
}
}
add_filter('mep_settings_sec_fields','mep_custom_css_sectings_fields',90);
if (!function_exists('mep_custom_css_sectings_fields')) { 
function mep_custom_css_sectings_fields($default_fields){
  $settings_fields = array(
    'mep_settings_custom_css' => array(
          array(
              'name' => 'mep_custom_css',
              'label' => __( 'Custom CSS', 'mage-eventpress' ),
              'desc' => __( 'Write Your Custom CSS Code Here', 'mage-eventpress' ),
              'type' => 'textarea',
              
          )                              
      )
    );
    return array_merge($default_fields,$settings_fields);
}
}
add_action('wp_head','mep_apply_custom_css',90);
if (!function_exists('mep_apply_custom_css')) { 
function mep_apply_custom_css(){
  $custom_css = mep_get_option( 'mep_custom_css', 'mep_settings_custom_css', '');
  ob_start();
?>
<style>
  /*  Custom CSS Code From WooCommerce Event Manager Plugin */
<?php echo $custom_css; ?>
</style>
<?php
  echo ob_get_clean();
}
}


if (!function_exists('mep_cart_ticket_type')) { 
function mep_cart_ticket_type($type,$total_price,$product_id){

  $mep_event_start_date   = isset($_POST['mep_event_start_date']) ? $_POST['mep_event_start_date'] : array();
  $names                  = isset($_POST['option_name']) ? $_POST['option_name'] : array();
  $qty                    = isset($_POST['option_qty']) ? $_POST['option_qty'] : array(); 
  $max_qty                = isset($_POST['max_qty']) ? $_POST['max_qty'] : array();
  $price                  = isset($_POST['option_price']) ? $_POST['option_price'] : array();
  $count                  = count( $names );
  $ticket_type_arr        = [];
//   echo '<pre>';
// print_r($names);
// print_r($mep_event_start_date);
// print_r($qty);
// echo '</pre>';
  $vald = 0;
  if(sizeof($names) > 0){
    for ( $i = 0; $i < $count; $i++ ) {
      if($qty[$i] > 0){
            $ticket_type_arr[$i]['ticket_name']   = !empty($names[$i]) ? stripslashes( strip_tags( $names[$i] ) ) : '';
            $ticket_type_arr[$i]['ticket_price']  = !empty($price[$i]) ? stripslashes( strip_tags( $price[$i] ) ) : '';
            $ticket_type_arr[$i]['ticket_qty']    = !empty($qty[$i]) ? stripslashes( strip_tags( $qty[$i] ) ) : '';
            $ticket_type_arr[$i]['max_qty']       = !empty($max_qty[$i]) ? stripslashes( strip_tags( $max_qty[$i] ) ) : '';
            $ticket_type_arr[$i]['event_date']    = !empty($mep_event_start_date[$i]) ? stripslashes( strip_tags( $mep_event_start_date[$i] ) ) : '';
            $opttprice                            = ($price[$i]*$qty[$i]);
            $total_price                          = ($total_price+$opttprice);
            $validate[$i]['validation_ticket_qty'] = $vald + stripslashes( strip_tags( $qty[$i]  ) );              
            $validate[$i]['event_id']             = stripslashes( strip_tags( $product_id ) );                
        }
    }
  }
  if($type == 'ticket_price'){
    return $total_price;
  }elseif($type == 'validation_data'){
    return $validate;
  }else{
    return apply_filters('mep_cart_ticket_type_data_prepare',$ticket_type_arr,$type,$total_price,$product_id);
  }
}
}

if (!function_exists('mep_cart_event_extra_service')) { 
function mep_cart_event_extra_service($type,$total_price){
  $mep_event_start_date_es   = isset($_POST['mep_event_start_date_es']) ? $_POST['mep_event_start_date_es'] : array();
  $extra_service_name     = isset($_POST['event_extra_service_name']) ? mage_array_strip($_POST['event_extra_service_name']) : array();
  $extra_service_qty      = isset($_POST['event_extra_service_qty']) ? mage_array_strip($_POST['event_extra_service_qty']):array();
  $extra_service_price    = isset($_POST['event_extra_service_price']) ? mage_array_strip($_POST['event_extra_service_price']):array();
  $event_extra            = [];

  if($extra_service_name){
      for ( $i = 0; $i < count($extra_service_name); $i++ ) {
        if($extra_service_qty[$i] > 0){
          $event_extra[$i]['service_name']  = !empty($extra_service_name[$i]) ? stripslashes( strip_tags( $extra_service_name[$i] ) ) : '';
          $event_extra[$i]['service_price'] = !empty($extra_service_price[$i]) ? stripslashes( strip_tags( $extra_service_price[$i] ) ) : '';
          $event_extra[$i]['service_qty']   = !empty($extra_service_qty[$i]) ? stripslashes( strip_tags( $extra_service_qty[$i] ) ) : '';
          $event_extra[$i]['event_date']    = !empty($mep_event_start_date_es[$i]) ? stripslashes( strip_tags( $mep_event_start_date_es[$i] ) ) : '';
          $extprice                         = ($extra_service_price[$i]*$extra_service_qty[$i]);
          $total_price                      = ($total_price+$extprice);
    }
  }
}
  if($type == 'ticket_price'){
    return $total_price;
  }else{
    return $event_extra;
  }
}
}

if (!function_exists('mep_cart_display_user_list')) { 
function mep_cart_display_user_list($user_info){
  ob_start();
  foreach ($user_info as $userinf) {
    ?>
      <ul>
        <?php if ($userinf['user_name']) { ?> <li><?php _e('Name: ', 'mage-eventpress');
                                                echo $userinf['user_name']; ?></li> <?php } ?>
        <?php if ($userinf['user_email']) { ?> <li><?php _e('Email: ', 'mage-eventpress');
                                                  echo $userinf['user_email']; ?></li> <?php } ?>
        <?php if ($userinf['user_phone']) { ?> <li><?php _e('Phone: ', 'mage-eventpress');
                                                  echo $userinf['user_phone']; ?></li> <?php } ?>
        <?php if ($userinf['user_address']) { ?> <li><?php _e('Address: ', 'mage-eventpress');
                                                    echo $userinf['user_address']; ?></li> <?php } ?>
        <?php if ($userinf['user_gender']) { ?> <li><?php _e('Gender: ', 'mage-eventpress');
                                                  echo $userinf['user_gender']; ?></li> <?php } ?>
        <?php if ($userinf['user_tshirtsize']) { ?> <li><?php _e('T-Shirt Size: ', 'mage-eventpress');
                                                      echo $userinf['user_tshirtsize']; ?></li> <?php } ?>
        <?php if ($userinf['user_company']) { ?> <li><?php _e('Company: ', 'mage-eventpress');
                                                    echo $userinf['user_company']; ?></li> <?php } ?>
        <?php if ($userinf['user_designation']) { ?> <li><?php _e('Designation: ', 'mage-eventpress');
                                                        echo $userinf['user_designation']; ?></li> <?php } ?>
        <?php if ($userinf['user_website']) { ?> <li><?php _e('Website: ', 'mage-eventpress');
                                                    echo $userinf['user_website']; ?></li> <?php } ?>
        <?php if ($userinf['user_vegetarian']) { ?> <li><?php _e('Vegetarian: ', 'mage-eventpress');
                                                      echo $userinf['user_vegetarian']; ?></li> <?php } ?>
        <?php if ($userinf['user_ticket_type']) { ?> <li><?php _e('Ticket Type: ', 'mage-eventpress');
                                                        echo $userinf['user_ticket_type']; ?></li> <?php } ?>
        <li><?php _e('Event Date:', 'mage-eventpress'); ?> <?php echo get_mep_datetime($userinf['user_event_date'], 'date-time-text'); ?></li>
      </ul>

      <?php
    }
    return apply_filters('mep_display_user_info_in_cart_list',ob_get_clean(),$user_info);
}
}


if (!function_exists('mep_cart_display_ticket_type_list')) { 
function mep_cart_display_ticket_type_list($ticket_type_arr){
ob_start();
  foreach ($ticket_type_arr as $ticket) {
    echo '<li>' . $ticket['ticket_name'] . " - " . wc_price($ticket['ticket_price']) . ' x ' . $ticket['ticket_qty'] . ' = ' . wc_price((int) $ticket['ticket_price'] * (int) $ticket['ticket_qty']) . '</li>';
  }
  return apply_filters('mep_display_ticket_in_cart_list',ob_get_clean(),$ticket_type_arr);
}
}



if (!function_exists('mep_cart_order_data_save_ticket_type')) { 
function mep_cart_order_data_save_ticket_type($item,$ticket_type_arr){
  foreach ($ticket_type_arr as $ticket) {
    $ticket_type_name = $ticket['ticket_name'] . " - " . wc_price($ticket['ticket_price']) . ' x ' . $ticket['ticket_qty'] . ' = ';
    $ticket_type_val = wc_price($ticket['ticket_price'] * $ticket['ticket_qty']);
    $ticket_name_meta = apply_filters('mep_event_order_meta_ticket_name_filter',$ticket_type_name,$ticket);
    $item->add_meta_data($ticket_name_meta, $ticket_type_val);
  }
}
}




if (!function_exists('mep_get_event_expire_date')) { 
function mep_get_event_expire_date($event_id){
  $event_expire_on_old = mep_get_option('mep_event_expire_on_datetimes', 'general_setting_sec', 'event_start_datetime');
  $event_expire_on    = $event_expire_on_old == 'event_end_datetime' ? 'event_expire_datetime' : $event_expire_on_old;
  $event_start_datetime = get_post_meta($event_id, 'event_start_datetime', true);
  $event_expire_datetime = get_post_meta($event_id, 'event_expire_datetime', true);
  $expire_date = $event_expire_on == 'event_expire_datetime' ? $event_expire_datetime : $event_start_datetime;
return $expire_date;
}
}



add_action('mep_event_single_template_end','mep_single_page_js_script');
add_action('mep_add_to_cart_shortcode_js','mep_single_page_js_script');
if (!function_exists('mep_single_page_js_script')) { 
function mep_single_page_js_script($event_id){
  $currency_pos = get_option('woocommerce_currency_pos');
  ob_start();
?>
<script>
    jQuery(document).ready(function() {
                jQuery("#mep-event-accordion").accordion({
                    collapsible: true,
                    active: false
                });
                jQuery(document).on("change", ".etp", function() {
                    var sum = 0;
                    jQuery(".etp").each(function() {
                        sum += +jQuery(this).val();
                    });
                    jQuery("#ttyttl").html(sum);
                });

                jQuery("#ttypelist").change(function() {
                    vallllp = jQuery(this).val() + "_";
                    var n = vallllp.split('_');
                    var price = n[0];
                    var ctt = 99;
                    if (vallllp != "_") {

                        var currentValue = parseInt(ctt);
                        jQuery('#rowtotal').val(currentValue += parseFloat(price));
                    }
                    if (vallllp == "_") {
                        jQuery('#eventtp').attr('value', 0);
                        jQuery('#eventtp').attr('max', 0);
                        jQuery("#ttypeprice_show").html("")
                    }
                });
                function updateTotal() {
                    var total = 0;
                    vallllp = jQuery(this).val() + "_";
                    var n = vallllp.split('_');
                    var price = n[0];
                    total += parseFloat(price);
                    jQuery('#rowtotal').val(total);
                }
                //Bind the change event
                jQuery(".extra-qty-box").on('change', function() {
                    var sum = 0;
                    var total = 0;
                    jQuery('.price_jq').each(function() {
                        var price = jQuery(this);
                        var count = price.closest('tr').find('.extra-qty-box');
                        sum = (price.html() * count.val());
                        total = total + sum;
                        // price.closest('tr').find('.cart_total_price').html(sum + "â‚´");

                    });
                    jQuery('#usertotal').html("<?php if ($currency_pos == "left" || $currency_pos == 'left_space') {
                        echo get_woocommerce_currency_symbol();
                    } ?>" + total + "<?php if ($currency_pos == "right" || $currency_pos == 'right_space') {
                       echo get_woocommerce_currency_symbol();
                    } ?>");
                    jQuery('#rowtotal').val(total);
                }).change(); //trigger change event on page load
                <?php
               $mep_event_ticket_type = get_post_meta($event_id, 'mep_event_ticket_type', true) ? get_post_meta($event_id, 'mep_event_ticket_type', true) : array();
//This is if no ticket type
                if (sizeof($mep_event_ticket_type) > 0 ) {
                  //This is if get ticket type
                    $count = 1;                  
                    $event_more_date[0]['event_more_start_date']    = date('Y-m-d', strtotime(get_post_meta($event_id, 'event_start_date', true)));
                    $event_more_date[0]['event_more_start_time']    = date('H:i', strtotime(get_post_meta($event_id, 'event_start_time', true)));
                    $event_more_date[0]['event_more_end_date']      = date('Y-m-d', strtotime(get_post_meta($event_id, 'event_end_date', true)));
                    $event_more_date[0]['event_more_end_time']      = date('H:i', strtotime(get_post_meta($event_id, 'event_end_time', true)));
                    $event_more_dates                               = get_post_meta($event_id, 'mep_event_more_date', true) ? get_post_meta($event_id, 'mep_event_more_date', true) : array();
                    $recurring = get_post_meta($event_id, 'mep_enable_recurring', true) ? get_post_meta($event_id, 'mep_enable_recurring', true) : 'no';
                    if ($recurring == 'yes') {
                        $event_multi_date                               = array_merge($event_more_date, $event_more_dates);
                    } else {
                        $event_multi_date                               = $event_more_date;
                    }
                    foreach ($event_multi_date as $event_date) {
                      
                        $start_date = $recurring == 'yes' ? date('Y-m-d H:i:s', strtotime($event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'])) : date('Y-m-d H:i:s', strtotime(mep_get_event_expire_date($event_id)));
                        $event_start_date = $recurring == 'yes' ? date('Y-m-d H:i:s', strtotime($event_date['event_more_start_date'] . ' ' . $event_date['event_more_start_time'])) : get_post_meta($event_id,'event_start_datetime',true);
                        
                        if (strtotime(current_time('Y-m-d H:i:s')) < strtotime($start_date)) {
                            foreach ($mep_event_ticket_type as $field) {
                                $ticket_type = $field['option_name_t'];
                            ?>
                                var inputs = jQuery("#ttyttl").html() || 0;
                                var inputs = jQuery('#eventpxtp_<?php echo $count; ?>').val() || 0;
                                var input = parseInt(inputs);
                                var children = jQuery('#dadainfo_<?php echo $count; ?> > div').length || 0;

                                var selected_ticket = jQuery('#ttyttl').html();

                                if (input < children) {
                                    jQuery('#dadainfo_<?php echo $count; ?>').empty();
                                    children = 0;
                                }
                                for (var i = children + 1; i <= input; i++) {
                                    jQuery('#dadainfo_<?php echo $count; ?>').append(
                                        jQuery('<div/>')
                                        .attr("id", "newDiv" + i)
                                        .html("<?php do_action('mep_reg_fields', $event_start_date, $event_id, $ticket_type); ?>")
                                    );
                                }
                                jQuery('#eventpxtp_<?php echo $count; ?>').on('change', function() {
                                    var inputs = jQuery("#ttyttl").html() || 0;
                                    var inputs = jQuery('#eventpxtp_<?php echo $count; ?>').val() || 0;
                                    var input = parseInt(inputs);
                                    var children = jQuery('#dadainfo_<?php echo $count; ?> > div').length || 0;
                                    jQuery(document).on("change", ".etp", function() {
                                        var TotalQty = 0;
                                        jQuery(".etp").each(function() {
                                            TotalQty += +jQuery(this).val();
                                        });
                                    });
                                    if (input < children) {
                                        jQuery('#dadainfo_<?php echo $count; ?>').empty();
                                        children = 0;
                                    }
                                    for (var i = children + 1; i <= input; i++) {
                                        jQuery('#dadainfo_<?php echo $count; ?>').append(
                                            jQuery('<div/>')
                                            .attr("id", "newDiv" + i)
                                            .html("<?php do_action('mep_reg_fields', $event_start_date, $event_id, $ticket_type); ?>")
                                        );
                                    }
                                });
                    <?php
                                $count++;
                            }
                        }
                    }
                } 
              ?>
});
</script>
<?php
echo ob_get_clean();
}
}


add_action('after-single-events','mep_single_page_script');
if (!function_exists('mep_single_page_script')) { 
function mep_single_page_script(){
  ob_start();
?>
        <script>               
            jQuery('#mep_single_view_all_date').click(function(){
                 jQuery(this).hide()
                 jQuery('#mep_event_date_sch').addClass('mep_view_all_date');
                 jQuery('#mep_single_hide_all_date').show();
            });
            jQuery('#mep_single_hide_all_date').click(function(){
                 jQuery(this).hide()
                 jQuery('#mep_event_date_sch').removeClass('mep_view_all_date');
                 jQuery('#mep_single_view_all_date').show()
            });            
        </script>
<?php
  echo ob_get_clean();
}
}

if (!function_exists('mep_product_exists')) { 
function mep_product_exists( $id ) {	  
  return is_string( get_post_status( $id ) );	
}
}