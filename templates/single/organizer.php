<p>
<?php
echo mep_get_option('mep_by_text', 'label_setting_sec', __('By:', 'mage-eventpress'));
$count_term = sizeof($org);
$count = 1;
if ($count_term > 1) {
    foreach ($org as $_org) {
        ?>
	<a href="<?php echo get_term_link($_org->term_id, 'mep_org'); ?>"><?php echo esc_html($_org->name);if ($count_term == $count) {} else {echo ', ';} ?></a>
<?php
$count++;
    }
} else {
    ?>
    <a href="<?php echo get_term_link($org[0]->term_id, 'mep_org'); ?>"><?php echo esc_html($org[0]->name); ?></a>
    <?php }?>
</p>