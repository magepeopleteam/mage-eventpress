<?php
get_header();
the_post();
$city =  get_query_var( 'cityname' );
?>
<div class='mep_city_filter_page'>
    <?php echo do_shortcode('[event-list city='.$city.']'); ?>
</div>
<?php
get_footer();
?>