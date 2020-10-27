<h3><?php ?><i class="<?php echo $speaker_icon; ?>"></i> <?php echo $speaker_label; ?></h3>
<ul>
    <?php
    foreach ($speakers_id as $speakers) {
    ?>
        <li>
            <a href='<?php echo get_the_permalink($speakers); ?>'><?php if (has_post_thumbnail($speakers)) {
                     echo get_the_post_thumbnail($speakers, 'medium');
                 } else {
                      echo '<img src="' . plugins_url('../images/no-photo.jpg', __DIR__) . '"/>';
                  } ?>
                <h6><?php echo get_the_title($speakers); ?></h6>
            </a>
        </li>
    <?php
    }
    ?>
</ul>