<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id           = $event_id ?? get_the_id();
	$event_ss_fb_icon       = mep_get_option('mep_event_ss_fb_icon', 'icon_setting_sec', 'fab fa-facebook-f');
	$event_ss_twitter_icon  = mep_get_option('mep_event_ss_twitter_icon', 'icon_setting_sec', 'fab fa-x-twitter');
	$hide_share_details = mep_get_option( 'mep_event_hide_share_this_details', 'single_event_setting_sec', 'no' );
	?>
	<?php if($hide_share_details=='no'): ?>
		<div class="share_widgets">
			<h2><?php esc_html_e('Share This Event', 'mage-eventpress'); ?></h2>
			<ul class='mep-social-share'>
				<?php do_action('mep_before_social_share_list',$event_id); ?>
				<li> <a data-toggle="tooltip" title="" class="facebook" onclick="window.open('https://www.facebook.com/sharer.php?u=<?php echo get_the_permalink($event_id); ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://www.facebook.com/sharer.php?u=<?php echo get_the_permalink($event_id); ?>" data-original-title="Share on Facebook"><i class="<?php echo $event_ss_fb_icon; ?>"></i></a></li>
				<li><a data-toggle="tooltip" title="" class="twitter" onclick="window.open('https://twitter.com/share?url=<?php echo get_the_permalink($event_id); ?>&amp;text=<?php echo mep_html_chr(get_the_title($event_id)); ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://twitter.com/share?url=<?php echo get_the_permalink($event_id); ?>&amp;text=<?php echo mep_html_chr(get_the_title($event_id)); ?>" data-original-title="Twittet it"><i class="<?php echo $event_ss_twitter_icon; ?>"></i></a></li>
				<?php do_action('mep_after_social_share_list',$event_id); ?>
			</ul>
		</div>
	<?php endif; ?> 

