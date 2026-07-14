<?php
	/*
	 * @Author      engr.sumonazma@gmail.com
	 * Copyright:   mage-people.com
	 *
	 * Registers the "Social Share Card" tab inside Event Settings. Lets the admin turn the
	 * booking social-share image card on/off and choose which payment statuses trigger it,
	 * for both the WooCommerce thank-you page and the Custom (native) Payment thank-you page.
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	// The frame-image field below uses the WP media uploader ("file" field type in
	// MAGE_Setting_API), which needs wp_enqueue_media() — not loaded by default on this
	// settings page (only on the event edit / calendar admin screens elsewhere in the plugin).
	add_action( 'admin_enqueue_scripts', 'mep_social_card_settings_media_uploader' );
	function mep_social_card_settings_media_uploader() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'mep_event_settings_page' ) {
			wp_enqueue_media();
		}
	}

	add_filter( 'mep_settings_sec_reg', 'mep_social_card_settings_section' );
	function mep_social_card_settings_section( $sections ) {
		$sections[] = array(
			'id'    => 'mep_social_card_setting_sec',
			'title' => '<i class="mi mi-copy-image"></i>' . __( 'Social Share Card', 'mage-eventpress' ),
		);
		return $sections;
	}

	add_filter( 'mep_settings_sec_fields', 'mep_social_card_settings_fields' );
	function mep_social_card_settings_fields( $settings_fields ) {
		$frame_template_url = MPWEM_PLUGIN_URL . '/assets/frontend/images/mep-social-card-frame-template.png';

		$settings_fields['mep_social_card_setting_sec'] = array(
			array(
				'name'    => 'mep_social_card_enable',
				'label'   => __( 'Enable Social Share Card', 'mage-eventpress' ),
				'desc'    => __( 'Show a downloadable/shareable registration image card (avatar, name, event, date & ticket) on the booking thank-you page.', 'mage-eventpress' ),
				'type'    => 'checkbox',
				'default' => '',
			),
			array(
				'name'    => 'mep_social_card_wc_statuses',
				'label'   => __( 'Show On WooCommerce Order Status', 'mage-eventpress' ),
				'desc'    => __( 'On the WooCommerce order-received (thank-you) page, show the card only when the order has one of these statuses.', 'mage-eventpress' ),
				'type'    => 'multicheck',
				'default' => array( 'processing' => 'processing', 'completed' => 'completed' ),
				'options' => array(
					'pending'    => __( 'Pending payment', 'mage-eventpress' ),
					'processing' => __( 'Processing', 'mage-eventpress' ),
					'on-hold'    => __( 'On hold', 'mage-eventpress' ),
					'completed'  => __( 'Completed', 'mage-eventpress' ),
				),
			),
			array(
				'name'    => 'mep_social_card_native_statuses',
				'label'   => __( 'Show On Custom Payment Booking Status', 'mage-eventpress' ),
				'desc'    => __( 'On the Custom (native) Payment thank-you page, show the card only when the booking has one of these statuses.', 'mage-eventpress' ),
				'type'    => 'multicheck',
				'default' => array( 'success' => 'success' ),
				'options' => array(
					'success' => __( 'Registration successful', 'mage-eventpress' ),
					'pending' => __( 'Registration pending payment', 'mage-eventpress' ),
				),
			),
			array(
				'name'    => 'mep_social_card_button_text',
				'label'   => __( 'Download Button Text', 'mage-eventpress' ),
				'desc'    => __( 'Label for the button that downloads the card as an image.', 'mage-eventpress' ),
				'type'    => 'text',
				'default' => __( 'Download Image', 'mage-eventpress' ),
			),
			array(
				'name'    => 'mep_social_card_networks',
				'label'   => __( 'Social Share Buttons', 'mage-eventpress' ),
				'desc'    => __( 'Which social networks to show as share buttons below the card.', 'mage-eventpress' ),
				'type'    => 'multicheck',
				'default' => array( 'facebook' => 'facebook', 'twitter' => 'twitter', 'whatsapp' => 'whatsapp' ),
				'options' => array(
					'facebook'  => __( 'Facebook', 'mage-eventpress' ),
					'twitter'   => __( 'Twitter / X', 'mage-eventpress' ),
					'whatsapp'  => __( 'WhatsApp', 'mage-eventpress' ),
					'linkedin'  => __( 'LinkedIn', 'mage-eventpress' ),
					'instagram' => __( 'Instagram', 'mage-eventpress' ),
				),
			),
			array(
				'name'    => 'mep_social_card_frame_image',
				'label'   => __( 'Custom Card Frame Image', 'mage-eventpress' ),
				'desc'    => sprintf(
					/* translators: %s: URL to the downloadable sample frame layout guide (PNG) */
					__( 'Upload your own background/frame for the card, replacing the default design. Exact size: 380 × 475px (ratio 4:5) — that\'s the card\'s default size, so artwork placed there lines up correctly with the avatar, text and badge. If an event name or other text is long enough to make the card taller than that, your image stretches to cover the extra height, so keep important design elements away from the very top/bottom edges and rely on the center staying safe. Leave empty to use the default design. <a href="%s" download>Download a blank sample frame</a> to see exactly where the avatar/name/date/ticket/event-name/site-name will be placed, then design around it.', 'mage-eventpress' ),
					esc_url( $frame_template_url )
				),
				'type'    => 'file',
				'default' => '',
				'options' => array( 'button_label' => __( 'Choose Frame Image', 'mage-eventpress' ) ),
			),
			array(
				'name'    => 'mep_social_card_font_family',
				'label'   => __( 'Card Font', 'mage-eventpress' ),
				'desc'    => __( 'Font used for the name, ticket/date and event name text on the card. (The cursive status headline always keeps its script font regardless of this setting.)', 'mage-eventpress' ),
				'type'    => 'select',
				'default' => 'default',
				'options' => array(
					'default'          => __( 'Default (System Font)', 'mage-eventpress' ),
					'Poppins'          => 'Poppins',
					'Roboto'           => 'Roboto',
					'Montserrat'       => 'Montserrat',
					'Open Sans'        => 'Open Sans',
					'Lato'             => 'Lato',
					'Inter'            => 'Inter',
					'Playfair Display' => 'Playfair Display',
					'Merriweather'     => 'Merriweather',
				),
			),
			array(
				'name'    => 'mep_social_card_text_color',
				'label'   => __( 'Text Color', 'mage-eventpress' ),
				'desc'    => __( 'Color of the attendee name, event name and footer text on the card.', 'mage-eventpress' ),
				'type'    => 'color',
				'default' => '#111827',
			),
			array(
				'name'    => 'mep_social_card_accent_color',
				'label'   => __( 'Accent Color', 'mage-eventpress' ),
				'desc'    => __( 'Color of the status headline, ticket/date labels, the checkmark badge and the avatar ring.', 'mage-eventpress' ),
				'type'    => 'color',
				'default' => '#059669',
			),
		);

		return $settings_fields;
	}
