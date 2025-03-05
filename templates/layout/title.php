<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	$event_id = $event_id ?? 0;
	$only = $only ?? '';
	if ($event_id > 0) {
		ob_start();
		if ($only) {
			echo get_the_title($event_id);
		} else {
			?><h1 class="mpwem_tile"><?php echo get_the_title($event_id); ?></h1><?php
		}
		$content = ob_get_clean();
		echo apply_filters('mage_event_single_title', $content, $event_id);
	}