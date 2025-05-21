<div class='mep_event_title_list_item mix <?php echo esc_attr($org_class) . ' ' . esc_attr($cat_class); ?>'>
    <a href='<?php the_permalink(); ?>'><?php the_title(); ?></a>
    <?php 
    // Display the first organizer (primary organizer)
    $event_organizer_icon = mep_get_option('mep_event_organizer_icon', 'icon_setting_sec', 'far fa-list-alt');
    $org_terms = get_the_terms($event_id, 'mep_org');
    if ($org_terms && !is_wp_error($org_terms) && count($org_terms) > 0) {
        echo ' - <span class="mep_title_list_organizer"><i class="' . esc_attr($event_organizer_icon) . '"></i> ' . esc_html($org_terms[0]->name) . '</span>';
    }
    ?>
</div>