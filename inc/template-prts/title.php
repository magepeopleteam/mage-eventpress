<?php 
add_action('mep_event_title','mep_ev_title');
function mep_ev_title(){
    global $post;
    ob_start();     
	?>
	    <h2><?php the_title(); ?></h2>
	<?php
    $content = ob_get_clean();
    echo apply_filters('mage_event_single_title', $content,$post->ID); 	
}
