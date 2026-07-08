<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$event_id                  = $event_id ?? 0;
	$event_infos               = $event_infos ?? [];
	$event_infos               = (is_array( $event_infos ) && sizeof( $event_infos ) > 0) ? $event_infos : MPWEM_Functions::get_all_info( $event_id );
	$_single_event_setting_sec = is_array($event_infos) && array_key_exists( 'single_event_setting_sec', $event_infos ) ? $event_infos['single_event_setting_sec'] : [];
	$single_event_setting_sec  = is_array( $_single_event_setting_sec ) && ! empty( $_single_event_setting_sec ) ? $_single_event_setting_sec : [];
	$hide_share_details        = is_array($single_event_setting_sec) && array_key_exists( 'mep_event_hide_share_this_details', $single_event_setting_sec ) ? $single_event_setting_sec['mep_event_hide_share_this_details'] : 'no';
	if ( $hide_share_details == 'no' ) {
		$icon_setting_sec = is_array($event_infos) && array_key_exists( 'icon_setting_sec', $event_infos ) ? $event_infos['icon_setting_sec'] : [];
		$icon_setting_sec = empty( $icon_setting_sec ) && ! is_array( $icon_setting_sec ) ? [] : $icon_setting_sec;
		$fb_icon          = is_array($icon_setting_sec) && array_key_exists( 'mep_event_ss_fb_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_fb_icon'] : 'fab fa-facebook-f';
		$twitter_icon     = is_array($icon_setting_sec) && array_key_exists( 'mep_event_ss_twitter_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_twitter_icon'] : 'fab fa-x-twitter';
		$linkedin_icon    = is_array($icon_setting_sec) && array_key_exists( 'mep_event_ss_linkedin_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_linkedin_icon'] : 'fab fa-linkedin';
		$whatsapp_icon    = is_array($icon_setting_sec) && array_key_exists( 'mep_event_ss_whatsapp_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_whatsapp_icon'] : 'fab fa-whatsapp';
		$email_icon       = is_array($icon_setting_sec) && array_key_exists( 'mep_event_ss_email_icon', $icon_setting_sec ) ? $icon_setting_sec['mep_event_ss_email_icon'] : 'fa fa-envelope';
        $url=get_the_permalink( $event_id );
        $tile=get_the_title($event_id);
		$find    = [ '&', '#038;' ];
		$replace = [ 'and', '' ];
		$t_title= html_entity_decode( str_replace( $find, $replace, $tile ) );

		/**
		 * Filter the list of social share links shown on the single event page.
		 *
		 * @param array  $links    Social link definitions.
		 * @param int    $event_id Current event ID.
		 * @param string $url      Event permalink.
		 * @param string $t_title  Event title (decoded).
		 */
		$links = apply_filters( 'mep_social_share_links', [
			'facebook' => [
				'class'   => 'facebook',
				'href'    => 'http://www.facebook.com/sharer.php?u=' . esc_url( $url ),
				'onclick' => "window.open('https://www.facebook.com/sharer.php?u=" . esc_url( $url ) . "','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;",
				'title'   => __( 'Share on Facebook', 'mage-eventpress' ),
				'icon'    => esc_attr( $fb_icon ),
			],
			'twitter' => [
				'class'   => 'twitter',
				'href'    => 'http://twitter.com/share?url=' . esc_url( $url ) . '&amp;text=' . esc_html( $t_title ),
				'onclick' => "window.open('https://twitter.com/share?url=" . esc_url( $url ) . "&amp;text=" . esc_html( $t_title ) . "','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;",
				'title'   => __( 'Tweet it', 'mage-eventpress' ),
				'icon'    => esc_attr( $twitter_icon ),
			],
			'linkedin' => [
				'class'   => 'linkedin',
				'href'    => 'https://www.linkedin.com/shareArticle?mini=true&url=' . esc_url( $url ) . '&title=' . esc_html( $tile ) . ' &summary=' . esc_html( get_the_excerpt( $event_id ) ) . '&source=web',
				'target'  => '_blank',
				'title'   => __( 'Share on LinkedIn', 'mage-eventpress' ),
				'icon'    => esc_attr( $linkedin_icon ),
			],
			'whatsapp' => [
				'class'   => 'whatsapp',
				'href'    => 'https://api.whatsapp.com/send?text=' . esc_html( $tile ) . ' ' . esc_url( $url ),
				'target'  => '_blank',
				'title'   => __( 'Share on WhatsApp', 'mage-eventpress' ),
				'icon'    => esc_attr( $whatsapp_icon ),
			],
			'email' => [
				'class'   => 'email',
				'href'    => 'mailto:?subject=' . __( 'I wanted you to see this site', 'mage-eventpress' ) . '&amp;body=' . esc_html( $tile ) . ' ' . esc_url( $url ),
				'title'   => __( 'Share by Email', 'mage-eventpress' ),
				'icon'    => esc_attr( $email_icon ),
			],
		], $event_id, $url, $t_title );
		?>
        <div class="share_widgets">
            <h5 class="share_widgets_title"><?php esc_html_e( 'Share This Event', 'mage-eventpress' ); ?></h5>
            <ul class="share_widgets_list">
				<?php do_action( 'mep_before_social_share_list', $event_id ); ?>
				<?php foreach ( $links as $key => $link ) :
					$link = wp_parse_args( $link, [
						'class'   => $key,
						'href'    => '#',
						'onclick' => '',
						'target'  => '',
						'title'   => '',
						'icon'    => '',
					] );
				?>
				<li>
					<a class="<?php echo esc_attr( $link['class'] ); ?>"
					   <?php if ( $link['onclick'] ) : ?>onclick="<?php echo esc_attr( $link['onclick'] ); ?>"<?php endif; ?>
					   href="<?php echo esc_url( $link['href'] ); ?>"
					   <?php if ( $link['target'] ) : ?>target="<?php echo esc_attr( $link['target'] ); ?>"<?php endif; ?>
					   data-original-title="<?php echo esc_attr( $link['title'] ); ?>">
						<i class="<?php echo esc_attr( $link['icon'] ); ?>"></i>
					</a>
				</li>
				<?php endforeach; ?>
            </ul>
        </div>
		<?php
	}
