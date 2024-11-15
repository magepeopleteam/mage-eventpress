<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPWEM_Hooks')) {
		class MPWEM_Hooks {
			public function __construct() {
				add_action('mpwem_title', [$this, 'title'],10,2);
				add_action('mpwem_organizer', [$this, 'organizer'],10,2);
				add_action('mpwem_location', [$this, 'location'],10,2);
				add_action('mpwem_time', [$this, 'time'],10,5);
				add_action('mpwem_registration', [$this, 'registration'],10,4);
				add_action('mpwem_date_select', [$this, 'date_select'],10,4);
			}
			public function title($event_id,$only=''): void { require MPWEM_Functions::template_path('layout/title.php'); }
			public function organizer($event_id,$only=''): void { require MPWEM_Functions::template_path('layout/organizer.php'); }
			public function location($event_id,$type=''): void { require MPWEM_Functions::template_path('layout/location.php'); }
			public function time($event_id,$all_dates=[],$all_times=[],$date='',$single=true): void { require MPWEM_Functions::template_path('layout/time.php'); }
			public function registration($event_id,$all_dates=[],$all_times=[],$date=''): void { require MPWEM_Functions::template_path('layout/registration.php'); }
			public function date_select($event_id,$all_dates=[],$all_times=[],$date=''): void { require MPWEM_Functions::template_path('layout/date_select.php'); }
		}
		new MPWEM_Hooks();
	}