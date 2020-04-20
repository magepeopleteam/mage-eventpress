<?php
get_header();
the_post();

$term_id = get_queried_object()->term_id;
// print_r(get_queried_object());
?>
<div class="mep-events-wrapper">
<div class='mep_event_list'>

<div class="mep_cat-details">
	<h1><?php echo get_queried_object()->name; ?></h1>
	<p><?php echo get_queried_object()->description; ?></p>
</div>
<?php
     $args_search_qqq = array (
                     'post_type'        => array( 'mep_events' ),
                     'posts_per_page'   => -1,
                      'tax_query'       => array(
								array(
							            'taxonomy'  => 'mep_org',
							            'field'     => 'term_id',
							            'terms'     => $term_id
							        )
                        )

                );
	 $loop = new WP_Query( $args_search_qqq );
	 while ($loop->have_posts()) {
	$loop->the_post();
	$event_meta = get_post_custom(get_the_id());
	$time = strtotime($event_meta['event_start_date'][0].' '.$event_meta['event_start_time'][0]);
  	$newformat = date('Y-m-d H:i:s',$time);


 if(time() < strtotime($newformat)){


   	$start_datetime 	= $event_meta['event_start_date'][0].' '.$event_meta['event_start_time'][0];
	$start_date 		= $event_meta['event_start_date'][0];
	$start_time 		= $event_meta['event_start_time'][0];

	$end_datetime 		= $event_meta['event_end_date'][0].' '.$event_meta['event_end_time'][0];

	$end_date = $event_meta['event_end_date'][0];
	$end_time = $event_meta['event_end_time'][0];





	?>
	<div class='mep_event_list_item'>
		<div class="mep_list_thumb"><?php the_post_thumbnail('medium'); ?></div>
		<div class="mep_list_event_details"><a href="<?php the_permalink(); ?>">
			<h2 class='mep_list_title'><?php the_title(); ?></h2>
			<h3 class='mep_list_date'>on <?php echo get_mep_datetime($start_datetime,'date-text').' '.get_mep_datetime($start_datetime,'time'); ?> - <?php if($start_date != $end_date){  echo get_mep_datetime($end_datetime,'date-text') .' - '; }  echo get_mep_datetime($end_datetime,'time'); ?></h3>

			<p><?php echo $event_meta['mep_location_venue'][0]; ?>,<?php echo $event_meta['mep_street'][0]; ?>, <?php echo $event_meta['mep_city'][0]; ?>,<?php echo $event_meta['mep_state'][0]; ?>,<?php echo $event_meta['mep_postcode'][0]; ?>,<?php echo $event_meta['mep_country'][0]; ?></p>
		</a>
		</div>
	</div>
<?php
}
}
?>
</div>
</div>
<?php
get_footer();
?>
