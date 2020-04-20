<?php
function mep_add_custom_fields_text_to_cart_item( $cart_item_data, $product_id, $variation_id ){

    $product_id             = get_post_meta($product_id,'link_mep_event',true) ? get_post_meta($product_id,'link_mep_event',true) : $product_id;
    $recurring              = get_post_meta($product_id, 'mep_enable_recurring', true) ? get_post_meta($product_id, 'mep_enable_recurring', true) : 'no';
    if (get_post_type($product_id) == 'mep_events') { 
    $tp                     = get_post_meta($product_id,'_price',true);
    $new                    = array();

    $event_cart_location    = isset($_POST['mep_event_location_cart']) ? $_POST['mep_event_location_cart'] : array();
    $event_cart_date        = isset($_POST['mep_event_date_cart']) ? $_POST['mep_event_date_cart'] : array();
    $mep_event_start_date   = isset($_POST['mep_event_start_date']) ? $_POST['mep_event_start_date'] : array();
    $checked                = isset($_POST['event_addt_price']) ? $_POST['event_addt_price'] : array();
    $names                  = isset($_POST['option_name']) ? $_POST['option_name'] : array();
    $qty                    = isset($_POST['option_qty']) ? $_POST['option_qty'] : array(); 
    $max_qty                = isset($_POST['max_qty']) ? $_POST['max_qty'] : array();
    $price                  = isset($_POST['option_price']) ? $_POST['option_price'] : array();
    $recurring_event_date   = $recurring == 'yes' ? isset($_POST['recurring_event_date']) ? $_POST['recurring_event_date'] : array() : array();
    $count                  = count( $names );

    if(isset($_POST['option_name'])){
      for ( $i = 0; $i < $count; $i++ ) {
        if($qty[$i] > 0){
                if ( $names[$i] != '' ) :
                    $ticket_type_arr[$i]['ticket_name'] = stripslashes( strip_tags( $names[$i] ) );
                endif;
                if ( $price[$i] != '' ) :
                    $ticket_type_arr[$i]['ticket_price'] = stripslashes( strip_tags( $price[$i] ) );
                endif;
                if ( $qty[$i] != '' ) :
                    $ticket_type_arr[$i]['ticket_qty'] = stripslashes( strip_tags( $qty[$i] ) );
                endif;
                if ( $max_qty[$i] != '' ) :
        		  $ticket_type_arr[$i]['max_qty'] = stripslashes( strip_tags( $max_qty[$i] ) );
        	    endif; 
        	    if ( $mep_event_start_date[$i] != '' ) :
        		  $ticket_type_arr[$i]['event_date'] = stripslashes( strip_tags( $mep_event_start_date[$i] ) );
        	    endif;
              $opttprice  = ($price[$i]*$qty[$i]);
              $tp         = ($tp+$opttprice);
          }
      }
    }

    $extra_service_name     = isset($_POST['event_extra_service_name']) ? mage_array_strip($_POST['event_extra_service_name']) : array();
    $extra_service_qty      = isset($_POST['event_extra_service_qty'])? mage_array_strip($_POST['event_extra_service_qty']):array();
    $extra_service_price    = isset($_POST['event_extra_service_price'])? mage_array_strip($_POST['event_extra_service_price']):array();

    if($extra_service_name){
        for ( $i = 0; $i < count($extra_service_name); $i++ ) {
          if($extra_service_qty[$i] > 0){
        if ( $extra_service_name[$i] != '' ) :
            $event_extra[$i]['service_name'] = stripslashes( strip_tags( $extra_service_name[$i] ) );
          endif;
        if ( $extra_service_price[$i] != '' ) :
            $event_extra[$i]['service_price'] = stripslashes( strip_tags( $extra_service_price[$i] ) );
          endif;
        if ( $extra_service_qty[$i] != '' ) :
            $event_extra[$i]['service_qty'] = stripslashes( strip_tags( $extra_service_qty[$i] ) );
          endif;
          }
        $extprice =   ($extra_service_price[$i]*$extra_service_qty[$i]);
        $tp = ($tp+$extprice);
      }
    }

    if(isset($_POST['mep_event_ticket_type'])){
      $ttp                                  = $_POST['mep_event_ticket_type'];
      $ttpqt                                = $_POST['tcp_qty'];
      $ticket_type                          = mep_get_order_info($ttp,1);
      $ticket_type_price                    = (mep_get_order_info($ttp,0)*$ttpqt);
      $cart_item_data['event_ticket_type']  = $ticket_type;
      $cart_item_data['event_ticket_price'] = $ticket_type_price;
      $cart_item_data['event_ticket_qty']   = $ttpqt;
      $tp                                   = $tp+$ticket_type_price;
    }

    $form_position = mep_get_option( 'mep_user_form_position', 'general_attendee_sec', 'details_page' );
    if($form_position=='details_page'){
      $user = mep_save_attendee_info_into_cart($product_id);
    }else{
      $user = '';
    }


    
    
  $mep_event_ticket_type = get_post_meta($product_id, 'mep_event_ticket_type', true) ? get_post_meta($product_id, 'mep_event_ticket_type', true) : array();
    $cnt = 0;
    $vald = 0;
    // $ticket_type_arr = array();
    if(is_array($mep_event_ticket_type) && sizeof($mep_event_ticket_type) > 0){
    // foreach($mep_event_ticket_type as $_type){

    //   $cart_arr = $new[$cnt];
    //   $name_key = array_search($_type['option_name_t'],$cart_arr);
    //   $qty_key = array_search($_type['option_qty_t'],$cart_arr);
       
    //      if(is_array($name_key)){
    //         $total_found = count($name_key);
    //      }else{
    //         $total_found = 0; 
    //      }
    //      if($cart_arr['option_qty'] > 0){
             
    //           $ticket_type_arr[$cnt]['ticket_name'] = stripslashes( strip_tags( $cart_arr[$name_key] ) );              
    //           $ticket_type_arr[$cnt]['ticket_qty'] = stripslashes( strip_tags( $cart_arr['option_qty'] ) );              
    //           $ticket_type_arr[$cnt]['ticket_price'] = stripslashes( strip_tags( $cart_arr['option_price'] ) );
    //           $ticket_type_arr[$cnt]['event_date'] = stripslashes( strip_tags( $cart_arr['event_data'] ) );
    //           $validate[$cnt]['ticket_qty'] = $vald + stripslashes( strip_tags( $cart_arr['option_qty'] ) );              
    //           $validate[$cnt]['event_id'] = stripslashes( strip_tags( $product_id ) );              
    //     }
        
    //     $cnt++;
    // }
    }

// print_r($ticket_type_arr);
// die();


  $cart_item_data['event_ticket_info']    = $ticket_type_arr;
  $cart_item_data['event_validate_info']  = $validate;
  $cart_item_data['event_extra_option']   = $new;
  $cart_item_data['event_user_info']      = $user;
  $cart_item_data['event_tp']             = $tp;
  $cart_item_data['line_total']           = $tp;
  $cart_item_data['line_subtotal']        = $tp;
  $cart_item_data['event_extra_service']  = $event_extra;
  $cart_item_data['event_cart_location']  = $event_cart_location;
  $cart_item_data['event_cart_date']      = $mep_event_start_date;
  $cart_item_data['event_recurring_date'] = array_unique($recurring_event_date);
  $cart_item_data['event_recurring_date_arr'] = $recurring_event_date;
  // $cart_item_data['event_cart_date']      = $event_cart_date;
}
  $cart_item_data['event_id']             = $product_id;

  return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'mep_add_custom_fields_text_to_cart_item', 90, 3);



add_action( 'woocommerce_before_calculate_totals', 'mep_add_custom_price',90,1 );
function mep_add_custom_price( $cart_object ) {

foreach ( $cart_object->cart_contents as $key => $value ) {
$eid = array_key_exists('event_id', $value) ? $value['event_id'] : 0; //$value['event_id'];
if (get_post_type($eid) == 'mep_events') {      
            $cp = $value['event_tp'];
            $value['data']->set_price($cp);
            $value['data']->set_regular_price($cp);
            $value['data']->set_sale_price($cp);
            $value['data']->set_sold_individually('yes');
            $new_price = $value['data']->get_price();
    }
  }
}





function mep_display_custom_fields_text_cart( $item_data, $cart_item ) {
$mep_events_extra_prices = array_key_exists('event_extra_option', $cart_item) ? $cart_item['event_extra_option'] : array(); //$cart_item['event_extra_option'];

$eid                    = array_key_exists('event_id', $cart_item) ? $cart_item['event_id'] : 0; //$cart_item['event_id'];

if (get_post_type($eid) == 'mep_events') { 
  $user_info                  = $cart_item['event_user_info'];
  $ticket_type_arr            = $cart_item['event_ticket_info'];
  $event_extra_service        = $cart_item['event_extra_service'];
  $event_recurring_date       = $cart_item['event_recurring_date'];

// echo '<pre>';
// print_r($ticket_type_arr);



  $recurring = get_post_meta($eid, 'mep_enable_recurring', true) ? get_post_meta($eid, 'mep_enable_recurring', true) : 'no';

echo "<ul class='event-custom-price'>";

if($recurring == 'yes'){
  if(is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0 && sizeof($user_info) == 0){
    foreach($ticket_type_arr as $_event_recurring_date){
  ?>
    <li><?php _e('Event Date','mage-eventpress'); ?>: <?php echo get_mep_datetime($_event_recurring_date['event_date'], 'date-time-text'); //date('D, d M Y H:i:s',strtotime($_event_recurring_date)); ?></li>
  <?php
    }
  }
  
  if(is_array($user_info) && sizeof($user_info) > 0){
    echo '<li>';
  foreach($user_info as $userinf){
  ?>
  <ul>
      <?php if($userinf['user_name']){ ?> <li><?php _e('Name: ','mage-eventpress'); echo $userinf['user_name']; ?></li> <?php } ?>
      <?php if($userinf['user_email']){ ?> <li><?php _e('Email: ','mage-eventpress'); echo $userinf['user_email']; ?></li> <?php } ?>
      <?php if($userinf['user_phone']){ ?> <li><?php _e('Phone: ','mage-eventpress'); echo $userinf['user_phone']; ?></li> <?php } ?>
      <?php if($userinf['user_address']){ ?> <li><?php _e('Address: ','mage-eventpress'); echo $userinf['user_address']; ?></li> <?php } ?>
      <?php if($userinf['user_gender']){ ?> <li><?php _e('Gender: ','mage-eventpress'); echo $userinf['user_gender']; ?></li> <?php } ?>
      <?php if($userinf['user_tshirtsize']){ ?> <li><?php _e('T-Shirt Size: ','mage-eventpress'); echo $userinf['user_tshirtsize']; ?></li> <?php } ?>
      <?php if($userinf['user_company']){ ?> <li><?php _e('Company: ','mage-eventpress'); echo $userinf['user_company']; ?></li> <?php } ?>
      <?php if($userinf['user_designation']){ ?> <li><?php _e('Designation: ','mage-eventpress'); echo $userinf['user_designation']; ?></li> <?php } ?>
      <?php if($userinf['user_website']){ ?> <li><?php _e('Website: ','mage-eventpress'); echo $userinf['user_website']; ?></li> <?php } ?>
      <?php if($userinf['user_vegetarian']){ ?> <li><?php _e('Vegetarian: ','mage-eventpress'); echo $userinf['user_vegetarian']; ?></li> <?php } ?>
      <?php if($userinf['user_ticket_type']){ ?> <li><?php _e('Ticket Type: ','mage-eventpress'); echo $userinf['user_ticket_type']; ?></li> <?php } ?>
       <li><?php _e('Event Date:','mage-eventpress'); ?> <?php echo get_mep_datetime($userinf['user_event_date'], 'date-time-text'); ?></li>
  </ul>
    
  <?php
  }
     echo '</li>'; 
}
  
  
  
  
  
  
}else{

if(is_array($user_info) && sizeof($user_info) > 0){
    echo '<li>';
  foreach($user_info as $userinf){
  ?>
  <ul>
      <?php if($userinf['user_name']){ ?> <li><?php _e('Name: ','mage-eventpress'); echo $userinf['user_name']; ?></li> <?php } ?>
      <?php if($userinf['user_email']){ ?> <li><?php _e('Email: ','mage-eventpress'); echo $userinf['user_email']; ?></li> <?php } ?>
      <?php if($userinf['user_phone']){ ?> <li><?php _e('Phone: ','mage-eventpress'); echo $userinf['user_phone']; ?></li> <?php } ?>
      <?php if($userinf['user_address']){ ?> <li><?php _e('Address: ','mage-eventpress'); echo $userinf['user_address']; ?></li> <?php } ?>
      <?php if($userinf['user_gender']){ ?> <li><?php _e('Gender: ','mage-eventpress'); echo $userinf['user_gender']; ?></li> <?php } ?>
      <?php if($userinf['user_tshirtsize']){ ?> <li><?php _e('T-Shirt Size: ','mage-eventpress'); echo $userinf['user_tshirtsize']; ?></li> <?php } ?>
      <?php if($userinf['user_company']){ ?> <li><?php _e('Company: ','mage-eventpress'); echo $userinf['user_company']; ?></li> <?php } ?>
      <?php if($userinf['user_designation']){ ?> <li><?php _e('Designation: ','mage-eventpress'); echo $userinf['user_designation']; ?></li> <?php } ?>
      <?php if($userinf['user_website']){ ?> <li><?php _e('Website: ','mage-eventpress'); echo $userinf['user_website']; ?></li> <?php } ?>
      <?php if($userinf['user_vegetarian']){ ?> <li><?php _e('Vegetarian: ','mage-eventpress'); echo $userinf['user_vegetarian']; ?></li> <?php } ?>
      <?php if($userinf['user_ticket_type']){ ?> <li><?php _e('Ticket Type: ','mage-eventpress'); echo $userinf['user_ticket_type']; ?></li> <?php } ?>
       <li><?php _e('Event Date:','mage-eventpress'); ?> <?php echo get_mep_datetime($userinf['user_event_date'], 'date-time-text'); ?></li>
  </ul>
    
  <?php
  }
     echo '</li>'; 
}else{
  if(is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0){
    foreach($ticket_type_arr as $_event_recurring_date){
  ?>
    <li><?php _e('Event Date','mage-eventpress'); ?>: <?php echo get_mep_datetime($_event_recurring_date['event_date'], 'date-time-text'); //date('D, d M Y H:i:s',strtotime($_event_recurring_date)); ?></li>
  <?php
    }
  }
}

}

?>
<li><?php _e('Event Location','mage-eventpress'); ?>: <?php echo $cart_item['event_cart_location']; //echo $cart_item['event_ticket_type']; ?></li>
<?php
if(is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0){
    foreach($ticket_type_arr as $ticket){
        echo '<li>'.$ticket['ticket_name']." - ".wc_price($ticket['ticket_price']).' x '.$ticket['ticket_qty'].' = '.wc_price($ticket['ticket_price'] * $ticket['ticket_qty']).'</li>';
    }
}

if(is_array($event_extra_service) && sizeof($event_extra_service) > 0){
    foreach($event_extra_service as $extra_service){
        echo '<li>'.$extra_service['service_name']." - ".wc_price($extra_service['service_price']).' x '.$extra_service['service_qty'].' = '.wc_price($extra_service['service_price'] * $extra_service['service_qty']).'</li>';
    }
}




  echo "</ul>";
}
  return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'mep_display_custom_fields_text_cart', 90, 2 );



add_action( 'woocommerce_after_checkout_validation', 'mep_checkout_validation');
function mep_checkout_validation( $posted ) {
  global $woocommerce;
  $items    = $woocommerce->cart->get_cart();
  foreach($items as $item => $values) { 
    $event_id              = array_key_exists('event_id', $values) ? $values['event_id'] : 0; // $values['event_id'];
    if (get_post_type($event_id) == 'mep_events') {
    $total_seat = mep_event_total_seat($event_id,'total');
	$total_resv = mep_event_total_seat($event_id,'resv');
	$total_sold = mep_ticket_sold($event_id);
    $total_left = $total_seat - ($total_sold + $total_resv);
    ;
    $event_validate_info        = $values['event_validate_info'] ? $values['event_validate_info'] : array();  
    
    $ee = 0;
    
    if(is_array($event_validate_info) && sizeof($event_validate_info) > 0){
        foreach($event_validate_info as $inf){
           $ee = $ee + $inf['ticket_qty'];
        }
    }
    
    if($ee > $total_left) {
      $event = get_the_title($event_id);
        wc_add_notice( __( "Sorry, Seats are not available in <b>$event</b>, Available Seats <b>$total_left</b> but you selected <b>$ee</b>", 'mage-eventpress' ), 'error' );
    }
    
  }
  }
}










function mep_add_custom_fields_text_to_order_items( $item, $cart_item_key, $values, $order ) {

        $eid                    = array_key_exists('event_id', $values) ? $values['event_id'] : 0; //$values['event_id'];
        
        if (get_post_type($eid) == 'mep_events') { 
        $mep_events_extra_prices = $values['event_extra_option'];
        if(isset($values['event_ticket_type'])){
          $event_ticket_type       = $values['event_ticket_type'];
        }else{
          $event_ticket_type = " "; 
        }
        if(isset($values['event_ticket_price'])){
          $event_ticket_price      = $values['event_ticket_price'];
        }else{
          $event_ticket_price      = " ";
        }
        if(isset($values['event_ticket_qty'])){
          $event_ticket_qty        = $values['event_ticket_qty'];
        }else{
          $event_ticket_qty        = " ";  
        }


    $product_id              = $values['product_id'];
    $cart_location           = $values['event_cart_location'];
    $event_extra_service     = $values['event_extra_service'];
    $ticket_type_arr         = $values['event_ticket_info'];
    $cart_date               = $values['event_cart_date'];
    
    $form_position = mep_get_option( 'mep_user_form_position', 'general_attendee_sec', 'details_page' );

    if($form_position=='details_page'){
      $event_user_info          = $values['event_user_info'];
    }else{
      $event_user_info          = mep_save_attendee_info_into_cart($eid);
    }

    $recurring = get_post_meta($eid, 'mep_enable_recurring', true) ? get_post_meta($eid, 'mep_enable_recurring', true) : 'no';
    if($recurring == 'yes'){
      if(is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0){
        foreach($ticket_type_arr as $_event_recurring_date){
          $item->add_meta_data('Date',get_mep_datetime($_event_recurring_date['event_date'], 'date-time-text'));
        }
      }
    }else{
      $item->add_meta_data('Date',get_mep_datetime($cart_date, 'date-time-text'));
    }
    
    $item->add_meta_data('Location',$cart_location);
    $item->add_meta_data('_event_ticket_info',$values['event_ticket_info']);

    if(is_array($ticket_type_arr) && sizeof($ticket_type_arr) > 0){
          foreach($ticket_type_arr as $ticket){
              $ticket_type_name = $ticket['ticket_name']." - ".wc_price($ticket['ticket_price']).' x '.$ticket['ticket_qty'].' = ';
              $ticket_type_val= wc_price($ticket['ticket_price'] * $ticket['ticket_qty']);
              $item->add_meta_data($ticket_type_name, $ticket_type_val );
            
          }
    }

    if(is_array($event_extra_service) && sizeof($event_extra_service) > 0){
          foreach($event_extra_service as $extra_service){
              
              $service_type_name = $extra_service['service_name']." - ".wc_price($extra_service['service_price']).' x '.$extra_service['service_qty'].' = ';
              $service_type_val= wc_price($extra_service['service_price'] * $extra_service['service_qty']);
              $item->add_meta_data($service_type_name, $service_type_val );
              
          }
     }


if($event_ticket_type){

}else{
    $item->add_meta_data('_event_ticket_type','normal');
}
    $item->add_meta_data('_event_user_info',$event_user_info);
    // $item->add_meta_data('_no_of_ticket',count($event_user_info));
    $item->add_meta_data('_event_service_info',$mep_events_extra_prices);
    $item->add_meta_data('event_id',$eid);
    $item->add_meta_data('_product_id',$eid);
    $item->add_meta_data('_event_extra_service',$event_extra_service);
}

}
add_action( 'woocommerce_checkout_create_order_line_item', 'mep_add_custom_fields_text_to_order_items', 90, 4 );