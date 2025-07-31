<?php
$content = ''; // Initialize $content with a default value or fetch its value as needed
echo wp_kses_post( apply_filters('mep_event_details_content', $content, get_the_id()) );