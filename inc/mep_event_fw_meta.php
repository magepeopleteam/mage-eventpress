<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * In the Version 3.5 we will introducing Mage Freamwork, All of our Plugin will use this same Freamwork, This is the Beta test in the Event Plugin.
	 */
	add_action('admin_init', 'mep_fw_meta_boxs');
	function mep_fw_meta_boxs() {
		$speaker_status = mep_get_option('mep_enable_speaker_list', 'single_event_setting_sec', 'no');
		/**
		 * This Will create Meta Boxes For Speakers Custom Post Type.
		 */
		$speakers_meta_boxs = array(
			'page_nav' => __('Speakers Meta Box', 'mage-eventpress'),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title' => __('', 'mage-eventpress'),
					'description' => __('', 'mage-eventpress'),
					'options' => array(// Meta Boxes Will Here as Array
					)
				),
			),
		);
		$speaker_meta_args = array(
			'meta_box_id' => 'mep_event_speakers_meta_boxes',
			'meta_box_title' => __('Speakers Additional Information', 'mage-eventpress'),
			//'callback'       => '_meta_box_callback',
			'screen' => array('mep_event_speaker'),
			'context' => 'normal', // 'normal', 'side', and 'advanced'
			'priority' => 'high', // 'high', 'low'
			'callback_args' => array(),
			'nav_position' => 'none', // right, top, left, none
			'item_name' => "MagePeople",
			'item_version' => "2.0",
			'panels' => array(
				'speakers_meta_boxs' => $speakers_meta_boxs
			),
		);
		//new AddMetaBox( $speaker_meta_args );
		/**
		 * This Will create Meta Boxes For Events Custom Post Type.
		 */		
		$email_body_meta_boxs = array(
			'page_nav' => __('Event List Thumbnail', 'mage-eventpress-gq'),
			'priority' => 10,
			'sections' => array(
				'section_2' => array(
					'title' => __('', 'mage-eventpress-gq'),
					'description' => __('', 'mage-eventpress-gq'),
					'options' => array(
						array(
							'id' => 'mep_event_cc_email_text',
							'title' => __('Confirmation Email Text:', 'mage-eventpress'),
							'details' => __('<b>Usable Dynamic tags:</b><br/> Attendee
                        Name:<b>{name}</b><br/>
                        Event Name: <b>{event}</b><br/>
                        Ticket Type: <b>{ticket_type}</b><br/>
                        Event Date: <b>{event_date}</b><br/>
                        Start Time: <b>{event_time}</b><br/>
                        Full DateTime: <b>{event_datetime}</b>', 'mage-eventpress'),
							'type' => 'wp_editor',
							// 'editor_settings'=>array('textarea_name'=>'wp_editor_field', 'editor_height'=>'150px'),
							'placeholder' => __('wp_editor value', 'mage-eventpress'),
							'default' => '',
						),
					)
				),
			),
		);
		$email_body_meta_args = array(
			'meta_box_id' => 'mep_event_email_body_meta_boxes',
			'meta_box_title' => '<i class="far fa-envelope-open"></i>' . __('Email Text', 'mage-eventpress'),
			//'callback'       => '_meta_box_callback',
			'screen' => array('mep_events'),
			'context' => 'normal', // 'normal', 'side', and 'advanced'
			'priority' => 'low', // 'high', 'low'
			'callback_args' => array(),
			'nav_position' => 'none', // right, top, left, none
			'item_name' => "MagePeople",
			'item_version' => "2.0",
			'panels' => array(
				'speakers_meta_boxs' => $email_body_meta_boxs
			),
		);
		// new AddMetaBox( $email_body_meta_args );
	}