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
</div>
<?php
get_footer();