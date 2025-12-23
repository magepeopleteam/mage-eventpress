<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? [];
	$event_infos               = sizeof( $event_infos ) > 0 ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$_single_event_setting_sec = array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_share_details        = array_key_exists( 'mep_event_hide_share_this_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_share_this_details'] : 'no';
	if ( $hide_share_details == 'no' ) {
		$icon_setting_sec = array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
		$icon_setting_sec = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
		$fb_icon          = array_key_exists( 'mep_event_ss_fb_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_fb_icon'] : 'fab fa-facebook-f';
		$twitter_icon     = array_key_exists( 'mep_event_ss_twitter_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_twitter_icon'] : 'fab fa-x-twitter';
		$linkedin_icon    = array_key_exists( 'mep_event_ss_linkedin_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_linkedin_icon'] : 'fab fa-linkedin';
		$whatsapp_icon    = array_key_exists( 'mep_event_ss_whatsapp_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_whatsapp_icon'] : 'fab fa-whatsapp';
		$email_icon       = array_key_exists( 'mep_event_ss_email_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_email_icon'] : 'fa fa-envelope';
        $url=get_the_permalink( $event_id );
        $tile=get_the_title($event_id);
		$find    = [ '&', '#038;' ];
		$replace = [ 'and', '' ];
		$t_title= html_entity_decode( str_replace( $find, $replace, $tile ) );
		?>
        <div class="share_widgets">
            <h5 class="share_widgets_title"><?php esc_html_e( 'Share This Event', 'mage-eventpress' ); ?></h5>
            <ul class="share_widgets_list">
				<?php do_action( 'mep_before_social_share_list', $event_id ); ?>
                <li><a class="facebook" onclick="window.open('https://www.facebook.com/sharer.php?u=<?php echo esc_url($url);?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://www.facebook.com/sharer.php?u=<?php echo esc_url($url); ?>" data-original-title="Share on Facebook"><i class="<?php echo esc_attr( $fb_icon ); ?>"></i></a></li>
                <li><a class="twitter" onclick="window.open('https://twitter.com/share?url=<?php echo esc_url($url); ?>&amp;text=<?php echo esc_html($t_title) ; ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://twitter.com/share?url=<?php echo esc_url($url); ?>&amp;text=<?php echo esc_html($t_title) ; ?>" data-original-title="Twittet it"><i class="<?php echo esc_attr( $twitter_icon ); ?>"></i></a></li>
                <li><a class="linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url($url);?>&title=<?php echo esc_html($tile)  . ' '; ?>&summary=<?php echo esc_html( get_the_excerpt( $event_id ) ); ?>&source=web" target="_blank"> <i class="<?php echo esc_attr( $linkedin_icon ); ?>"></i></a></li>
                <li><a class="whatsapp" href="https://api.whatsapp.com/send?text=<?php echo esc_html($tile)  . ' '; ?><?php echo esc_url($url); ?>" target="_blank"> <i class="<?php echo esc_attr( $whatsapp_icon ); ?>"></i> </a></li>
                <li><a class="email" href="mailto:?subject=I wanted you to see this site&amp;body=<?php echo esc_html($tile) . ' '; ?><?php echo esc_url($url); ?>" title="Share by Email"> <i class="<?php echo esc_attr( $email_icon ); ?>"></i> </a></li>
            </ul>
        </div>
		<?php
	}

