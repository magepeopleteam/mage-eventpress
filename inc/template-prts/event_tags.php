<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	
	add_action('mep_event_tags', 'mep_ev_tags');
	if (!function_exists('mep_ev_tags')) {
		function mep_ev_tags($event_id) {
			global $post;
			ob_start();
			$tags = get_the_terms($event_id, 'mep_tag');
			if (!empty($tags) && !is_wp_error($tags)) {
				?>
				<div class="location-widgets mep-event-tags-widget">
					<div>
						<div class="location-title"><?php echo esc_html(mep_get_option('mep_tags_text', 'label_setting_sec', __('Event Tags', 'mage-eventpress'))); ?></div>
						<p class="mep-event-tags-list">
							<?php
							foreach ($tags as $tag) {
								echo '<a href="' . esc_url(get_term_link($tag->term_id, 'mep_tag')) . '" rel="tag" class="mep-tag-link">' . esc_html($tag->name) . '</a>';
							}
							?>
						</p>
					</div>
				</div>
				<?php
			}
			$content = ob_get_clean();
			echo apply_filters('mage_event_single_tags', $content, $event_id);
		}
	}
	
	add_action('mep_event_tags_name', 'mep_ev_tags_name');
	if (!function_exists('mep_ev_tags_name')) {
		function mep_ev_tags_name() {
			global $post;
			ob_start();
			$tags = get_the_terms(get_the_id(), 'mep_tag');
			$names = [];
			if (sizeof($tags) > 0 && !is_wp_error($tags)) {
				foreach ($tags as $key => $value) {
					$names[] = $value->name;
				}
			}
			echo esc_html(implode(', ', $names));
			$content = ob_get_clean();
			echo apply_filters('mage_event_single_tags_name', $content, $post->ID);
		}
	}

