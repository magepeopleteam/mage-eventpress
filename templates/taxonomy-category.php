<?php
get_header();
the_post();
$term_id = get_queried_object()->term_id;
?>
<div class="mep-events-wrapper">
	<div class='mep_event_list'>
		<div class="mep_cat-details">
			<h1><?php echo get_queried_object()->name; ?></h1>
			<p><?php echo get_queried_object()->description; ?></p>
		</div>
		<div class='mage_grid_box'>
		<?php
		$loop =  mep_event_query(20, 'ASC', $term_id, '', '', '', 'upcoming');
		while ($loop->have_posts()) {
			$loop->the_post();
			do_action('mep_event_list_shortcode', get_the_id(), 'three_column', 'grid');
		}
		wp_reset_postdata();
		mep_event_pagination($loop->max_num_pages);
		?>
	</div>
	</div>
</div>
<?php
get_footer();