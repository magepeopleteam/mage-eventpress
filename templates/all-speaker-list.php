<li>
    <a href='<?php echo get_the_permalink($speakers); ?>'>
        <?php if (has_post_thumbnail($speakers)) {
            echo get_the_post_thumbnail($speakers, 'medium');
        } else { ?>
            <img src="<?php echo esc_url(MPWEM_PLUGIN_URL . '/assets/helper/images/no-photo.jpg'); ?>" alt=""/>;
        <?php } ?>
        <h6><?php echo get_the_title($speakers); ?></h6>
    </a>
</li>