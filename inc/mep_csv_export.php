<?php
add_action( 'admin_notices', 'export_btn' );
function export_btn() {
    global $typenow;
    if ($typenow == 'mep_events_attendees') {
        ?>
        <div class="wrap alignright">
            <form method='get' action="edit.php">
                <input type="hidden" name='post_type' value="mep_events_attendees"/>
                <input type="hidden" name='noheader' value="1"/>
                <?php 
                  if ( isset( $_GET['meta_value'] )) {
                ?>
              <input type="hidden" name='meta_value' value="<?php echo $_GET['meta_value']; ?>"/>
              <?php } ?>                
              <input type="hidden" name='action' value="download_csv"/>
                <input type="submit" name='export' id="csvExport" value="<?php _e('Export to CSV','mage-eventpress'); ?>"/>
            </form>
        </div>
        <?php
    }
}


function mep_get_event_user_fields($post_id){
    global $woocommerce, $post;
    
    $tee = get_post_meta($post_id,'mep_reg_tshirtsize',true);
    $adrs = get_post_meta($post_id,'mep_reg_address',true);
    if($tee){ $teee = 'Tee Size'; }else{ $teee = ''; }
    if($adrs){ $address = 'Addresss'; }else{ $address = ''; }
    
      $row = array(
        'Ticket No',
        'Order ID',
        'Event',        
        'Ticket',                
        'Full Name',
        'Email',
        'Phone',
        $address,
        $teee,
        'Check in'
    );


$crow = array();
$mep_form_builder_data = get_post_meta($post_id, 'mep_form_builder_data', true);
  if ( $mep_form_builder_data ) {
    foreach ( $mep_form_builder_data as $_field ) {
      $crow[] = $_field['mep_fbc_label'];
    }
  }


$order      = get_post_meta($post_id, 'mep_events_extra_prices', true);
$exs        = array();
if($order){
foreach ($order as $_exs) {
    $exs[] = $_exs['option_name'];
}
}
return array_merge(array_filter($row), $crow, $exs);
}



function mep_get_event_user_fields_data($post_id,$event){
$values = get_post_custom( $post_id );

  $checkin_status = get_post_meta($post_id,'mep_checkin',true);

  if($checkin_status){
    $status = $checkin_status;
  }else{
    $status = 'no';
  }
   $tee = get_post_meta($event,'mep_reg_tshirtsize',true);
    $adrs = get_post_meta($event,'mep_reg_address',true);
    if($tee){ $teee = get_post_meta( $post_id, 'ea_tshirtsize', true ); }else{ $teee = ''; }
    if($adrs){ $address = get_post_meta( $post_id, 'ea_address_1', true ); }else{ $address = ''; }
    
    $ticket = get_post_meta( $post_id, 'ea_user_id', true ).get_post_meta( $post_id, 'ea_order_id', true ).$event.$post_id;
    
    
      $row = array(
          $ticket,
            get_post_meta( $post_id, 'ea_order_id', true ),
            get_post_meta( $post_id, 'ea_event_name', true ),  
            get_post_meta( $post_id, 'ea_ticket_type', true ),                      
            get_post_meta( $post_id, 'ea_name', true ),
            get_post_meta( $post_id, 'ea_email', true ),
            get_post_meta( $post_id, 'ea_phone', true ),
            $address,
            $teee,
            $status
        );
    $crow = array();
    $mep_form_builder_data = get_post_meta($event, 'mep_form_builder_data', true);
    if ( $mep_form_builder_data ) {
    foreach ( $mep_form_builder_data as $_field ) {
      $vname = "ea_".$_field['mep_fbc_id'];
      if(array_key_exists($vname, $values)){
      $crow[] =  get_post_meta( $post_id, $vname , true );
    }else{
        $crow[] =  '';
    }
    }
  }

$order      = get_post_meta($event, 'mep_events_extra_prices', true);
$exs        = array();
$order_extra_service_arr = mep_get_event_extra_service_items($post_id);
if($order_extra_service_arr){
  if($order){
foreach ($order as $_exs) {
    // $exs[] = $_exs['option_name'];
    $exs[] = mep_get_extra_service_order_qty($_exs['option_name'], $order_extra_service_arr);
}
}
}

return array_merge(array_filter($row), $crow, $exs);
}





function mep_get_event_extra_service_items($post_id){
global $wpdb;
$order_id = get_post_meta($post_id, 'ea_order_id', true);
$item_table_name = $wpdb->prefix."woocommerce_order_items";
if($order_id){
  $sql = "SELECT order_item_id FROM $item_table_name WHERE order_item_type = 'line_item' AND order_id=$order_id";
  $results = $wpdb->get_results($sql); //or die(mysql_error());

if(!empty($results)){
$order_item_id = $results[0]->order_item_id;

  $table_name = $wpdb->prefix."woocommerce_order_itemmeta";

  $sql2 = "SELECT meta_value FROM $table_name WHERE order_item_id =$order_item_id AND   meta_key='_event_service_info'";
  $results2 = $wpdb->get_results($sql2);

    if($results2){
        return unserialize($results2[0]->meta_value);
    }else{
        return array();
    }
}else{
    return array();
}
}else{
    return array();
}


}

function mep_get_extra_service_order_qty($name, $array) {
    if(!empty($array)){
   foreach ($array as $key => $val) {
       if ($val['option_name'] === $name ) {
           return $val['option_qty'];
           // return $key;
       }
   }
}
   return null;
}



// Add action hook only if action=download_csv
if ( isset($_GET['action'] ) && $_GET['action'] == 'download_csv' )  {
  // Handle CSV Export
  add_action( 'admin_init', 'csv_export') ;
}
function csv_export() {
    // Check for current user privileges 
    if( !current_user_can( 'manage_options' ) ){ return false; }
    // Check if we are in WP-Admin
    if( !is_admin() ){ return false; }
    // Nonce Check
    // $nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
    // if ( ! wp_verify_nonce( $nonce, 'download_csv' ) ) {
    //     die( 'Security check error' );
    // }
    ob_start();
    $domain = $_SERVER['SERVER_NAME'];
    $filename = 'Event_Manager_Export_' . $domain . '_' . time() . '.csv';
    

        if(isset($_GET['meta_value'])){
          $post_id  = strip_tags($_GET['meta_value']);
          $header_row      = mep_get_event_user_fields($post_id);          
        }else{
        $header_row = array(
        'Ticket No',
        'Order ID',
        'Event',        
        'Ticket',                
        'Full Name',
        'Email',
        'Phone',
        'Addresss',
        'Tee Size',
        'Check in'
        );         
        }





    $data_rows = array();
    global $wpdb;

if(isset($_GET['meta_value'])){
$meta = $_GET['meta_value'];
  $query = "SELECT post_id
        FROM {$wpdb->prefix}postmeta
        WHERE meta_value = $meta
        ORDER BY meta_id ASC";
}else{
  $query = "SELECT ID
        FROM {$wpdb->prefix}posts
        WHERE post_type ='mep_events_attendees'
        ORDER BY ID ASC";
}

    $posts   = $wpdb->get_results($query);
foreach ( $posts as $i=>$post ) {

        if(isset($_GET['meta_value'])){
          $post_id  = $post->post_id;
        }else{
          $post_id  = $post->ID;
        }





$status = get_post_status($post_id);

if($status=='publish'){
        if(isset($_GET['meta_value'])){
          
          $event    = strip_tags($_GET['meta_value']);
          $row      = mep_get_event_user_fields_data($post_id,$event);          
        }else{
          $post_id = $post->ID;


  $checkin_status = get_post_meta($post_id,'mep_checkin',true);

  if($checkin_status){
    $status = $checkin_status;
  }else{
    $status = 'no';
  }


$ticket = get_post_meta( $post_id, 'ea_user_id', true ).get_post_meta( $post_id, 'ea_order_id', true ).get_post_meta( $post_id, 'ea_event_id', true ).$post_id;


        $row = array(
            $ticket,
            get_post_meta( $post_id, 'ea_order_id', true ),
            get_post_meta( $post_id, 'ea_event_name', true ),  
            get_post_meta( $post_id, 'ea_ticket_type', true ),                      
            get_post_meta( $post_id, 'ea_name', true ),
            get_post_meta( $post_id, 'ea_email', true ),
            get_post_meta( $post_id, 'ea_phone', true ),
            get_post_meta( $post_id, 'ea_address_1', true ),
            get_post_meta( $post_id, 'ea_tshirtsize', true ),
            $status

        );          
        }


        $data_rows[] = $row;
      }
    }
    $fh = @fopen( 'php://output', 'w' );
    fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header( 'Content-Description: File Transfer' );
    header( 'Content-type: text/csv' );
    header( "Content-Disposition: attachment; filename={$filename}" );
    header( 'Expires: 0' );
    header( 'Pragma: public' );
    fputcsv( $fh, $header_row );
    foreach ( $data_rows as $data_row ) {
        fputcsv( $fh, $data_row );
    }
    fclose( $fh );
    
    ob_end_flush();
    
    die();
}