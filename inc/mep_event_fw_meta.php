<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * In the Version 3.5 we will introducing Mage Freamwork, All of our Plugin will use this same Freamwork, This is the Beta test in the Event Plugin.
 */

 
add_action('admin_init', 'mep_fw_meta_boxs');
function mep_fw_meta_boxs()
{
    $speaker_status = mep_get_option('mep_enable_speaker_list', 'general_setting_sec', 'no');
    /**
     * This Will create Meta Boxes For Speakers Custom Post Type.
     */
    $speakers_meta_boxs = array(
        'page_nav'     => __('Speakers Meta Box', 'mage-eventpress'),
        'priority' => 10,
        'sections' => array(
            'section_2' => array(
                'title'     =>     __('', 'mage-eventpress'),
                'description'     => __('', 'mage-eventpress'),
                'options'     => array(
                    // Meta Boxes Will Here as Array

                )
            ),

        ),
    );
    $speaker_meta_args = array(
        'meta_box_id'               => 'mep_event_speakers_meta_boxes',
        'meta_box_title'            => __('Speakers Additional Information', 'mage-eventpress'),
        //'callback'       => '_meta_box_callback',
        'screen'                    => array('mep_event_speaker'),
        'context'                   => 'normal', // 'normal', 'side', and 'advanced'
        'priority'                  => 'high', // 'high', 'low'
        'callback_args'             => array(),
        'nav_position'              => 'none', // right, top, left, none
        'item_name'                 => "MagePeople",
        'item_version'              => "2.0",
        'panels'                     => array(
            'speakers_meta_boxs' => $speakers_meta_boxs

        ),
    );
    //new AddMetaBox( $speaker_meta_args );


    /**
     * This Will create Meta Boxes For Events Custom Post Type.
     */
    $events_speaker_list_meta_boxs = array(
        'page_nav'     => __('Event Additional Meta Boxes', 'mage-eventpress'),
        'priority' => 10,
        'sections' => array(
            'section_2' => array(
                'title'     =>     __('', 'mage-eventpress'),
                'description'     => __('', 'mage-eventpress'),
                'options'     => array(
                    // Meta Boxes Will Here as Array
                    array(
                        'id'		=> 'mep_event_speaker_icon',
                        'title'		=> __('Speaker Icon','mage-eventpress'),
                        'details'	=> __('Please Select the Icon which will show as Speaker Icon','mage-eventpress'),
                        'default'	=> 'fas fa-microphone',
                        'type'		=> 'icon',
                        'args'		=> 'FONTAWESOME_ARRAY',
                    ),
                    array(
                        'id'		    => 'mep_speaker_title',
                        'title'		    => __('Section Label','mage-eventpress'),
                        'details'	    => __('This Text will be the heading of the Speaker List in the frontend. by default: Speakers ','mage-eventpress'),
                        'type'		    => 'text',
                        'default'		=> "Speaker's",
                        'placeholder'   => __("Speaker's",'mage-eventpress'),
                    ),                    
                    array(
                        'id'            => 'mep_event_speakers_list',
                        'title'            => __('Speakers', 'mage-eventpress'),
                        'details'        => __('Please select Speakers, You can <a href="' . get_admin_url() . 'post-new.php?post_type=mep_event_speaker' . '">Add New Speakers From Here</a>', 'mage-eventpress'),
                        'multiple'        => true,
                        'limit'            => '3',
                        'type'            => 'select2',
                        'args'            => 'CPT_%mep_event_speaker%',
                    ),
                )
            ),

        ),
    );
    $events_speaker_list_meta_args = array(
        'meta_box_id'               => 'mep_event_speakers_list_meta_boxes',
        'meta_box_title'            => '<span class="dashicons dashicons-businessman"></span>&nbsp;&nbsp;'.__('Speaker Information', 'mage-eventpress'),
        'screen'                    => array('mep_events'),
        'context'                   => 'normal',
        'priority'                  => 'high', 
        'callback_args'             => array(),
        'nav_position'              => 'none',
        'item_name'                 => "MagePeople",
        'item_version'              => "2.0",
        'panels'                     => array(
            'events_speaker_list_meta_boxs' => $events_speaker_list_meta_boxs
        )
    );

    if($speaker_status == 'yes'){
       new AddMetaBox($events_speaker_list_meta_args);
    }




    $events_faq_boxs = array(
        'page_nav'     => __('Event FAQ', 'mage-eventpress'),
        'priority' => 10,
        'sections' => array(
            'section_2' => array(
                'title'     =>     __('', 'mage-eventpress'),
                'description'     => __('', 'mage-eventpress'),
                'options'     => array(
                    array(
                            'id' => 'mep_event_faq',
                            'title' => __('F.A.Q Details', 'mage-eventpress'),
                            'details' => __('', 'mage-eventpress'),
                            'collapsible' => true,
                            'type' => 'repeatable',
                            'btn_text' => __('Add New F.A.Q','mage-eventpress'),
                            'title_field' => 'mep_faq_title',
                            'fields' => array(                              
                                array(
                                    'type' => 'text',
                                    'default' => '',
                                    'item_id' => 'mep_faq_title',
                                    'name' => __('Title','mage-eventpress')
                                ),
                                array(
                                    'type' => 'textarea',
                                    'default' => '',
                                    'item_id' => 'mep_faq_content',
                                    'name' => __('Content','mage-eventpress')
                                ),
                            ),
                        ),
                )
            ),

        ),
    );
    $events_faq_meta_args = array(
        'meta_box_id'               => 'mep_event_faq_meta_boxes',
        'meta_box_title'            => '<span class="dashicons dashicons-info"></span>&nbsp;&nbsp;'.__('F.A.Q', 'mage-eventpress'),
        'screen'                    => array('mep_events'),
        'context'                   => 'normal',
        'priority'                  => 'high', 
        'callback_args'             => array(),
        'nav_position'              => 'none',
        'item_name'                 => "MagePeople",
        'item_version'              => "2.0",
        'panels'                     => array(
            'events_faq_meta_boxs' => $events_faq_boxs
        )
    );
    new AddMetaBox($events_faq_meta_args);





    $events_dd_boxs = array(
        'page_nav'     => __('Event Daywise Details', 'mage-eventpress'),
        'priority' => 10,
        'sections' => array(
            'section_2' => array(
                'title'     =>     __('', 'mage-eventpress'),
                'description'     => __('', 'mage-eventpress'),
                'options'     => array(
                    array(
                            'id' => 'mep_event_day',
                            'title' => __('Daywise Details', 'mage-eventpress'),
                            'details' => __('', 'mage-eventpress'),
                            'collapsible' => true,
                            'type' => 'repeatable',
                            'btn_text' => __('Add New Days','mage-eventpress'),
                            'title_field' => 'mep_day_title',
                            'fields' => array(                              
                                array(
                                    'type' => 'text',
                                    'default' => '',
                                    'item_id' => 'mep_day_title',
                                    'name' => __('Title','mage-eventpress')
                                ),
                                array(
                                    'type' => 'textarea',
                                    'default' => '',
                                    'item_id' => 'mep_day_content',
                                    'name' => __('Content','mage-eventpress')
                                ),
                            ),
                        ),
                )
            ),

        ),
    );
    $events_dd_meta_args = array(
        'meta_box_id'               => 'mep_event_dd_meta_boxes',
        'meta_box_title'            => '<span class="dashicons dashicons-analytics"></span>&nbsp;&nbsp;'.__('Daywise Details', 'mage-eventpress'),
        'screen'                    => array('mep_events'),
        'context'                   => 'normal',
        'priority'                  => 'high', 
        'callback_args'             => array(),
        'nav_position'              => 'none',
        'item_name'                 => "MagePeople",
        'item_version'              => "2.0",
        'panels'                     => array(
            'events_dd_meta_boxs' => $events_dd_boxs
        )
    );
    new AddMetaBox($events_dd_meta_args);







    $list_thumb_meta_boxs = array(
        'page_nav'     => __('Event List Thumbnail', 'mage-eventpress-gq'),
        'priority' => 10,
        'sections' => array(
            'section_2' => array(
                'title'     =>     __('', 'mage-eventpress'),
                'description'     => __('', 'mage-eventpress'),
                'options'     => array(

                    array(
                        'id'          => 'mep_list_thumbnail',
                        'title'       => __('Thumbmnail ','mage-eventpress'),
                        'details'     => __('Please upload image for event list','mage-eventpress'),
                        'placeholder' => 'https://via.placeholder.com/1000x500',
                        'type'        => 'media',
                    )


                )
            ),

        ),
    );
    $list_thumb_meta_args = array(
        'meta_box_id'               => 'mep_event_list_thumbnail_meta_boxes',
        'meta_box_title'            => __('Event List Thumbnail', 'mage-eventpress'),
        //'callback'       => '_meta_box_callback',
        'screen'                    => array('mep_events'),
        'context'                   => 'side', // 'normal', 'side', and 'advanced'
        'priority'                  => 'low', // 'high', 'low'
        'callback_args'             => array(),
        'nav_position'              => 'none', // right, top, left, none
        'item_name'                 => "MagePeople",
        'item_version'              => "2.0",
        'panels'                     => array(
            'speakers_meta_boxs' => $list_thumb_meta_boxs
        ),
    );

    new AddMetaBox( $list_thumb_meta_args );


    $email_body_meta_boxs = array(
        'page_nav'     => __('Event List Thumbnail', 'mage-eventpress-gq'),
        'priority' => 10,
        'sections' => array(
            'section_2' => array(
                'title'     =>     __('', 'mage-eventpress-gq'),
                'description'     => __('', 'mage-eventpress-gq'),
                'options'     => array(

                    array(
                        'id'    => 'mep_event_cc_email_text',
                        'title'    => __('Confirmation Email Text:','mage-eventpress'),
                        'details'  => __('','mage-eventpress'),
                        'type'    => 'wp_editor',
                        // 'editor_settings'=>array('textarea_name'=>'wp_editor_field', 'editor_height'=>'150px'),
                        'placeholder' => __('wp_editor value','mage-eventpress'),
                        'default'    => '',
                    ),
                )
            ),

        ),
    );
    $email_body_meta_args = array(
        'meta_box_id'               => 'mep_event_email_body_meta_boxes',
        'meta_box_title'            => '<span class="dashicons dashicons-email"></span>&nbsp;&nbsp;'.__('Email Confirmation Text', 'mage-eventpress'),
        //'callback'       => '_meta_box_callback',
        'screen'                    => array('mep_events'),
        'context'                   => 'normal', // 'normal', 'side', and 'advanced'
        'priority'                  => 'low', // 'high', 'low'
        'callback_args'             => array(),
        'nav_position'              => 'none', // right, top, left, none
        'item_name'                 => "MagePeople",
        'item_version'              => "2.0",
        'panels'                     => array(
            'speakers_meta_boxs' => $email_body_meta_boxs
        ),
    );
    new AddMetaBox( $email_body_meta_args );
}