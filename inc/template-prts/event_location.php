<?php 
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_location','mep_ev_location');
add_action('mep_event_location_ticket','mep_ev_location_ticket');

if (!function_exists('mep_ev_location_cart')) {
function mep_ev_location_cart($event_id,$event_meta){
$location_sts = get_post_meta($event_id,'mep_org_address',true) ? get_post_meta($event_id,'mep_org_address',true) : '';
ob_start();
if($location_sts){
    $org_arr    = get_the_terms( $event_id, 'mep_org' );
    $org_id     = $org_arr[0]->term_id;
        echo  get_term_meta( $org_id, 'org_location', true ); ?>,<?php if(get_term_meta( $org_id, 'org_street', true )){ ?><?php echo get_term_meta( $org_id, 'org_street', true ); ?>,
<?php }  if(get_term_meta( $org_id, 'org_city', true )){ ?> <?php echo get_term_meta( $org_id, 'org_city', true ); ?>,
<?php } if(get_term_meta( $org_id, 'org_state', true )){ echo get_term_meta( $org_id, 'org_state', true ); ?>,
<?php } if(get_term_meta( $org_id, 'org_postcode', true )){ ?>
<?php echo get_term_meta( $org_id, 'org_postcode', true ); ?>,
<?php } if(get_term_meta( $org_id, 'org_country', true )){ ?>
<?php echo get_term_meta( $org_id, 'org_country', true ); ?> <?php } 
    }else{
        echo $event_meta['mep_location_venue'][0]; ?>,
<?php if($event_meta['mep_street'][0]){ ?><?php echo $event_meta['mep_street'][0]; ?>,
<?php }  if($event_meta['mep_city'][0]){ ?> <?php echo $event_meta['mep_city'][0]; ?>,
<?php } if($event_meta['mep_state'][0]){ ?> <?php echo $event_meta['mep_state'][0]; ?>,
<?php } if($event_meta['mep_postcode'][0]){ ?> <?php echo $event_meta['mep_postcode'][0]; ?>,
<?php } if($event_meta['mep_country'][0]){ ?> <?php echo $event_meta['mep_country'][0]; ?> <?php } 
     }
     
     
    $content = ob_get_clean();
    echo apply_filters('mage_event_location_in_cart', $content,$event_id,$event_meta);   
}
}


if (!function_exists('mep_ev_location_ticket')) {
function mep_ev_location_ticket($event_id,$event_meta){
$location_sts = get_post_meta($event_id,'mep_org_address',true) ? get_post_meta($event_id,'mep_org_address',true) : '';
ob_start();
if($location_sts){
$org_arr    = get_the_terms( $event_id, 'mep_org' );
$org_id     = $org_arr[0]->term_id;
?>
<?php echo get_term_meta( $org_id, 'org_location', true ); ?>,
<?php if(get_term_meta( $org_id, 'org_street', true )){ ?><?php echo get_term_meta( $org_id, 'org_street', true ); ?>,
<?php } ?>
<?php if(get_term_meta( $org_id, 'org_city', true )){ ?> <?php echo get_term_meta( $org_id, 'org_city', true ); ?>,
<?php } ?>
<?php if(get_term_meta( $org_id, 'org_state', true )){ ?> <?php echo get_term_meta( $org_id, 'org_state', true ); ?>,
<?php } ?>
<?php if(get_term_meta( $org_id, 'org_postcode', true )){ ?>
<?php echo get_term_meta( $org_id, 'org_postcode', true ); ?>, <?php } ?>
<?php if(get_term_meta( $org_id, 'org_country', true )){ ?> <?php echo get_term_meta( $org_id, 'org_country', true ); ?> <?php } 
}else{
?>
<?php echo $event_meta['mep_location_venue'][0]; ?>,
<?php if($event_meta['mep_street'][0]){ ?><?php echo $event_meta['mep_street'][0]; ?>, <?php } ?>
<?php if($event_meta['mep_city'][0]){ ?> <?php echo $event_meta['mep_city'][0]; ?>, <?php } ?>
<?php if($event_meta['mep_state'][0]){ ?> <?php echo $event_meta['mep_state'][0]; ?>, <?php } ?>
<?php if($event_meta['mep_postcode'][0]){ ?> <?php echo $event_meta['mep_postcode'][0]; ?>, <?php } ?>
<?php if($event_meta['mep_country'][0]){ ?> <?php echo $event_meta['mep_country'][0]; ?> <?php } 
         
	}
    $content = ob_get_clean();
    echo apply_filters('mage_event_location_in_ticket', $content,$event_id,$event_meta);   
}
}

if (!function_exists('mep_ev_location')) {
function mep_ev_location(){
global $post,$event_meta;	
$location_sts       = get_post_meta($post->ID,'mep_org_address',true) ? get_post_meta($post->ID,'mep_org_address',true) : '';
ob_start();
if($location_sts){
$org_arr = get_the_terms( $post->ID, 'mep_org' );
$org_id = $org_arr[0]->term_id;
?>
<p><?php echo get_term_meta( $org_id, 'org_location', true ); ?>,</p>
<?php if(get_term_meta( $org_id, 'org_street', true )){ ?><p>
    <?php echo get_term_meta( $org_id, 'org_street', true ); ?>,</p> <?php } ?>
<?php if(get_term_meta( $org_id, 'org_city', true )){ ?> <p><?php echo get_term_meta( $org_id, 'org_city', true ); ?>,
</p> <?php } ?>
<?php if(get_term_meta( $org_id, 'org_state', true )){ ?> <p><?php echo get_term_meta( $org_id, 'org_state', true ); ?>,
</p> <?php } ?>
<?php if(get_term_meta( $org_id, 'org_postcode', true )){ ?> <p>
    <?php echo get_term_meta( $org_id, 'org_postcode', true ); ?>,</p> <?php } ?>
<?php if(get_term_meta( $org_id, 'org_country', true )){ ?> <p>
    <?php echo get_term_meta( $org_id, 'org_country', true ); ?></p> <?php } 
}else{
?>
<p><?php echo $event_meta['mep_location_venue'][0]; ?>,</p>
<?php if($event_meta['mep_street'][0]){ ?><p><?php echo $event_meta['mep_street'][0]; ?>,</p> <?php } ?>
<?php if($event_meta['mep_city'][0]){ ?> <p><?php echo $event_meta['mep_city'][0]; ?>,</p> <?php } ?>
<?php if($event_meta['mep_state'][0]){ ?> <p><?php echo $event_meta['mep_state'][0]; ?>,</p> <?php } ?>
<?php if($event_meta['mep_postcode'][0]){ ?> <p><?php echo $event_meta['mep_postcode'][0]; ?>,</p> <?php } ?>
<?php if($event_meta['mep_country'][0]){ ?> <p><?php echo $event_meta['mep_country'][0]; ?></p> <?php } 
         
	}
    $content = ob_get_clean();
    echo apply_filters('mage_event_location_content', $content,$post->ID,$event_meta);  
}
}



add_action('mep_event_location_venue','mep_ev_venue');
if (!function_exists('mep_ev_venue')) {
function mep_ev_venue($event_id=''){
global $post,$event_meta;	
if($event_id){
    $event = $event_id;
}else{
    $event = $post->ID;
}
$location_sts = get_post_meta($event,'mep_org_address',true);
if($location_sts){
$org_arr = get_the_terms( $event, 'mep_org' );
$org_id = $org_arr[0]->term_id;
	echo get_term_meta( $org_id, 'org_location', true );
}else{
 echo get_post_meta($event,'mep_location_venue',true);

}
}
}
/**
 * Event Location Get Functions
 */
if (!function_exists('mep_get_event_location')) {
function mep_get_event_location($event_id){
$location_sts = get_post_meta($event_id,'mep_org_address',true);
    if($location_sts){
        $org_arr = get_the_terms( $event_id, 'mep_org' );
        $org_id = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_location', true );
    }else{
         return get_post_meta($event_id,'mep_location_venue',true); 
    }
}
}

if (!function_exists('mep_get_event_location_street')) {
function mep_get_event_location_street($event_id){
$location_sts = get_post_meta($event_id,'mep_org_address',true);
    if($location_sts){
        $org_arr = get_the_terms( $event_id, 'mep_org' );
        $org_id = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_street', true );
    }else{
         return get_post_meta($event_id,'mep_street',true); 
    }
}
}

if (!function_exists('mep_get_event_location_city')) {
function mep_get_event_location_city($event_id){
$location_sts = get_post_meta($event_id,'mep_org_address',true);
    if($location_sts){
        $org_arr = get_the_terms( $event_id, 'mep_org' );
        $org_id = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_city', true );
    }else{
         return get_post_meta($event_id,'mep_city',true); 
    }
}
}

if (!function_exists('mep_get_event_location_state')) {
function mep_get_event_location_state($event_id){
$location_sts = get_post_meta($event_id,'mep_org_address',true);
    if($location_sts){
        $org_arr = get_the_terms( $event_id, 'mep_org' );
        $org_id = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_state', true );
    }else{
         return get_post_meta($event_id,'mep_state',true); 
    }
}
}

function mep_get_location_name_for_list($event_id){

}

if (!function_exists('mep_get_event_location_postcode')) {
function mep_get_event_location_postcode($event_id){
$location_sts = get_post_meta($event_id,'mep_org_address',true);
    if($location_sts){
        $org_arr = get_the_terms( $event_id, 'mep_org' );
        $org_id = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_postcode', true );
    }else{
         return get_post_meta($event_id,'mep_postcode',true); 
    }
}
}

if (!function_exists('mep_get_event_location_country')) {
function mep_get_event_location_country($event_id){
$location_sts = get_post_meta($event_id,'mep_org_address',true);
    if($location_sts){
        $org_arr = get_the_terms( $event_id, 'mep_org' );
        $org_id = $org_arr[0]->term_id;
        return get_term_meta( $org_id, 'org_country', true );
    }else{
         return get_post_meta($event_id,'mep_country',true); 
    }
}
}






add_action('mep_event_location_street','mep_ev_street');
if (!function_exists('mep_ev_street')) {
function mep_ev_street(){
global $post,$event_meta;	
$location_sts = get_post_meta($post->ID,'mep_org_address',true);
if($location_sts){
$org_arr = get_the_terms( $post->ID, 'mep_org' );
$org_id = $org_arr[0]->term_id;
	echo "<span>".get_term_meta( $org_id, 'org_street', true )."</span>";
}else{
?>
<span><?php echo $event_meta['mep_street'][0]; ?></span>
<?php
}
}
}


add_action('mep_event_location_city','mep_ev_city');
if (!function_exists('mep_ev_city')) {
function mep_ev_city(){
global $post,$event_meta;	
$location_sts = get_post_meta($post->ID,'mep_org_address',true);
if($location_sts){
$org_arr = get_the_terms( $post->ID, 'mep_org' );
$org_id = $org_arr[0]->term_id;
	echo "<span>".get_term_meta( $org_id, 'org_city', true )."</span>";
}else{
?>
<span><?php echo $event_meta['mep_city'][0]; ?></span>
<?php
}
}
}



add_action('mep_event_location_state','mep_ev_state');
if (!function_exists('mep_ev_state')) {
function mep_ev_state(){
global $post,$event_meta;	
$location_sts = get_post_meta($post->ID,'mep_org_address',true);
if($location_sts){
$org_arr = get_the_terms( $post->ID, 'mep_org' );
$org_id = $org_arr[0]->term_id;
	echo "<span>".get_term_meta( $org_id, 'org_state', true )."</span>";
}else{
?>
<span><?php echo $event_meta['mep_state'][0]; ?></span>
<?php
}
}
}



add_action('mep_event_location_postcode','mep_ev_postcode');
if (!function_exists('mep_ev_postcode')) {
function mep_ev_postcode(){
global $post,$event_meta;	
$location_sts = get_post_meta($post->ID,'mep_org_address',true);
if($location_sts){
$org_arr = get_the_terms( $post->ID, 'mep_org' );
$org_id = $org_arr[0]->term_id;
	echo "<span>".get_term_meta( $org_id, 'org_postcode', true )."</span>";
}else{
?>
<span><?php echo $event_meta['mep_postcode'][0]; ?></span>
<?php
}
}
}


add_action('mep_event_location_country','mep_ev_country');
if (!function_exists('mep_ev_country')) {
function mep_ev_country(){
global $post,$event_meta;	
$location_sts = get_post_meta($post->ID,'mep_org_address',true);
if($location_sts){
$org_arr = get_the_terms( $post->ID, 'mep_org' );
$org_id = $org_arr[0]->term_id;
	echo "<span>".get_term_meta( $org_id, 'org_country', true )."</span>";
}else{
?>
<span><?php echo $event_meta['mep_country'][0]; ?></span>
<?php
}
}
}

add_action('mep_event_address_list_sidebar','mep_event_address_list_sidebar_html');
if (!function_exists('mep_event_address_list_sidebar_html')) {
function mep_event_address_list_sidebar_html($event_id){
    $location_sts   = get_post_meta($event_id,'mep_org_address',true);
    $org_arr        = get_the_terms( $event_id, 'mep_org' ) ? get_the_terms( $event_id, 'mep_org' ) : '';
    $org_id         = !empty($org_arr) ? $org_arr[0]->term_id : '';    

    $venue = !empty($location_sts) ? get_term_meta( $org_id, 'org_location', true ) : get_post_meta($event_id,'mep_location_venue',true);
    $street = !empty($location_sts) ? get_term_meta( $org_id, 'org_street', true ) : get_post_meta($event_id,'mep_street',true);
    $city = !empty($location_sts) ? get_term_meta( $org_id, 'org_city', true ) : get_post_meta($event_id,'mep_city',true);
    $state = !empty($location_sts) ? get_term_meta( $org_id, 'org_state', true ) : get_post_meta($event_id,'mep_state',true);
    $country = !empty($location_sts) ? get_term_meta( $org_id, 'org_country', true ) : get_post_meta($event_id,'mep_country',true);
    ob_start();
        require(mep_template_file_path('single/location_list.php')); 
    echo ob_get_clean();
}
}