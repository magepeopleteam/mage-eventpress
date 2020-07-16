<?php
get_header();
the_post();
?>
<div class="mep_events_speaker_wraper">
    <div class="mep_speaker_thumbnail">
        <?php the_post_thumbnail('full'); ?>
    </div>
    <div class="mep_speaker_name">
        <h2><?php the_title(); ?></h2>
    </div>
    <div class="mep_speaker_details">
        <?php the_content(); ?>
    </div>


    <div class='mep_event_list'>
		<div class="mep_cat-details">
			<h3><?php _e('All Event Of ','mage-eventpress'); the_title(); ?></h3>			
		</div>
		<div class='mage_grid_box'>
        <?php
    $paged              = get_query_var("paged") ? get_query_var("paged") : 1;
    $args = array(
        'post_type'         => array('mep_events'),
        'paged'             => $paged,
        // 'posts_per_page'    => -1,
        'orderby'           => 'meta_value',
        'meta_key'          => 'event_start_datetime',
        'meta_query' => array(
            array(
                'key'       => 'mep_event_speakers_list',
                'value'     => get_the_id(),
                'compare'   => 'LIKE'
            )
        )
    );

    $loop = new WP_Query($args);        
		// $loop =  mep_event_query(20, 'ASC', $term_id, '', '', '', 'upcoming');
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