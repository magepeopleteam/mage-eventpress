<?php 
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

add_action('mep_event_price','mep_ev_price');
if (!function_exists('mep_ev_price')) {
function mep_ev_price(){
global $post,$event_meta;
 ob_start();
    if($event_meta['_price'][0]>0){
		if($event_meta['mep_price_label'][0]){ ?>
		    <h3><?php echo $event_meta['mep_price_label'][0]; ?>: </h3>
			<?php } 
		echo wc_price($event_meta['_price'][0]);  
    } 
$content = ob_get_clean();
echo apply_filters('mage_event_single_price', $content,$post->ID);
}
}