<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	add_action('mep_event_organizer', 'mep_ev_org');
	if (!function_exists('mep_ev_org')) {
		function mep_ev_org($event_id) {
			global $post, $author_terms;
			ob_start();
			$org = get_the_terms($event_id, 'mep_org');
			if (!empty($org)) {
				require(mep_template_file_path('single/organizer.php'));
			}
			$content = ob_get_clean();
			echo apply_filters('mage_event_single_org_name', $content, $event_id);
		}
	}
	
	add_action('mep_event_organizer_name', 'mep_ev_org_name');
	if (!function_exists('mep_ev_org_name')) {
		function mep_ev_org_name() {
			global $post, $author_terms;
			ob_start();
			$org = get_the_terms(get_the_id(), 'mep_org');
			$names = [];
			if (sizeof($org) > 0) {
				foreach ($org as $key => $value) {
					$names[] = $value->name;
				}
			}
			echo esc_html(implode(', ', $names));
			$content = ob_get_clean();
			echo apply_filters('mage_event_single_org_name', $content, $post->ID);
		}
	}

	add_action('mep_event_organized_by', 'mep_event_organized_by');
	function mep_event_organized_by($event_id) {
		// Get organizer terms to identify primary organizer
		$org_terms = get_the_terms($event_id, 'mep_org');
		$links = array();
		if ($org_terms && !is_wp_error($org_terms) && count($org_terms) > 0) :?>
				<div class="mep-org-details">
					<div class="org-name">
						<div><?php echo mep_get_option('mep_organized_by_text', 'label_setting_sec') ? mep_get_option('mep_organized_by_text', 'label_setting_sec') : _e('Organized By:', 'mage-eventpress'); ?></div>
						<?php foreach ($org_terms as $index => $org): ?>
							<strong><?php echo esc_html($org->name); ?><?php if ($index < count($org_terms) - 1): ?>|<?php endif; ?></strong>
						<?php endforeach; ?>
					</div>
				</div>
			<?php else :
				// If no custom organizer display is needed, use the default
				do_action('mep_event_organizer', $event_id);
		endif;

	}