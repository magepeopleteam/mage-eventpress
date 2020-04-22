<?php

/**
 * In the Version 3.5 we will introducing Mage Freamwork, All of our Plugin will use this same Freamwork, This is the Beta test in the Event Plugin.
 */

add_action('admin_init', 'mep_fw_meta_boxs');
function mep_fw_meta_boxs()
{

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
                        'id'            => 'mep_event_speakers_list',
                        'title'            => __('Speakers', 'mage-eventpress'),
                        'details'        => __('Please select Speakers, You can <a href="' . get_admin_url() . 'post-new.php?post_type=mep_event_speaker' . '">Add New Speakers From Here</a>', 'mage-eventpress'),
                        'multiple'        => true,
                        'limit'            => '3',
                        'type'            => 'select2',
                        'args'            => 'CPT_%mep_event_speaker%',
                    ),

                    array(
                        'id'            => 'mep_event_type',
                        'title'            => __('Event Type', 'mage-eventpress'),
                        'details'        => __('Please Select Event Type', 'mage-eventpress'),
                        'type'            => 'select',
                        'args'            => array(
                            'offline' => __('Offline Event', 'mage-eventpress'),
                            'online' => __('Online/Virtual Event', 'mage-eventpress')
                        )
                    ),
                )
            ),

        ),
    );
    $events_speaker_list_meta_args = array(
        'meta_box_id'               => 'mep_event_speakers_list_meta_boxes',
        'meta_box_title'            => __('Event Additional Information', 'mage-eventpress'),
        //'callback'       => '_meta_box_callback',
        'screen'                    => array('mep_events'),
        'context'                   => 'normal', // 'normal', 'side', and 'advanced'
        'priority'                  => 'high', // 'high', 'low'
        'callback_args'             => array(),
        'nav_position'              => 'none', // right, top, left, none
        'item_name'                 => "MagePeople",
        'item_version'              => "2.0",
        'panels'                     => array(
            'events_speaker_list_meta_boxs' => $events_speaker_list_meta_boxs

        ),
    );
    new AddMetaBox($events_speaker_list_meta_args);
}
