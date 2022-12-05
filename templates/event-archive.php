<?php
get_header();
the_post();
do_action('mep_event_single_page_after_header',get_the_id());
?>
<div class="mep-events-wrapper">
        <div class='mep_event_list'>
            <div class='mage_grid_box'>
            <?php
            $loop =  mep_event_query(18, 'ASC', '', '', '', '', 'upcoming');
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
    do_action('mep_event_single_page_before_footer');
get_footer();