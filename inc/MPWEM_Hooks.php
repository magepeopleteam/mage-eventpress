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
				add_action('mpwem_registration_content', [$this, 'registration_content'],10,4);
				add_action('mpwem_date_select', [$this, 'date_select'],10,4);
				add_action('mpwem_date_time', [$this, 'date_time'],10,4);
				add_action('mpwem_faq', [$this, 'faq'],10,4);
				add_action('mpwem_map', [$this, 'map'],10,4);
				add_action('mpwem_related', [$this, 'related'],10,4);
				add_action('mpwem_social', [$this, 'social'],10,4);
				/**************************/
				add_action( 'wp_ajax_get_mpwem_ticket', array( $this, 'get_mpwem_ticket' ) );
				add_action( 'wp_ajax_nopriv_get_mpwem_ticket', array( $this, 'get_mpwem_ticket' ) );
				add_action( 'wp_ajax_get_mpwem_time', array( $this, 'get_mpwem_time' ) );
				add_action( 'wp_ajax_nopriv_get_mpwem_time', array( $this, 'get_mpwem_time' ) );
			}
			public function title($event_id,$only=''): void { require MPWEM_Functions::template_path('layout/title.php'); }
			public function organizer($event_id,$only=''): void { require MPWEM_Functions::template_path('layout/organizer.php'); }
			public function location($event_id,$type=''): void { require MPWEM_Functions::template_path('layout/location.php'); }
			public function time($event_id,$all_dates=[],$all_times=[],$date='',$single=true): void { require MPWEM_Functions::template_path('layout/time.php'); }
			public function registration($event_id,$all_dates=[],$all_times=[],$date=''): void { require MPWEM_Functions::template_path('layout/registration.php'); }
			public function registration_content($event_id,$all_dates=[],$all_times=[],$date=''): void { require MPWEM_Functions::template_path('layout/registration_content.php'); }
			public function date_select($event_id,$all_dates=[],$all_times=[],$date=''): void { require MPWEM_Functions::template_path('layout/date_select.php'); }
			public function date_time($event_id,$all_dates=[],$all_times=[]): void { require MPWEM_Functions::template_path('layout/date_time.php'); }
			public function faq($event_id): void { require MPWEM_Functions::template_path('layout/faq.php'); }
			public function map($event_id): void { require MPWEM_Functions::template_path('layout/map.php'); }
			public function related($event_id): void { require MPWEM_Functions::template_path('layout/related_event.php'); }
			public function social($event_id): void { require MPWEM_Functions::template_path('layout/social.php'); }
			/**************************/

			public function get_mpwem_ticket() {
				$post_id     = $_REQUEST['post_id'] ?? '';
				$dates        = $_REQUEST['dates'] ?? '';
				do_action('mpwem_registration_content', $post_id,[],[],$dates);
				die();
			}
			public function get_mpwem_time() {
				$event_id     = $_REQUEST['post_id'] ?? '';
				$date        = $_REQUEST['dates'] ?? '';
				$hidden_date  = $date ? date( 'Y-m-d', strtotime( $date ) ) : '';
				$all_dates = MPWEM_Functions::get_dates($event_id);
                //echo '<pre>';print_r(MP_Global_Function::get_post_info($event_id, 'mep_special_date_info', []));echo '</pre>';
                //echo '<pre>';print_r($date);echo '</pre>';
                //echo '<pre>';print_r($all_dates);echo '</pre>';
				$all_times    = MPWEM_Functions::get_times( $event_id, $all_dates, $hidden_date );
				//echo '<pre>';print_r($all_times);echo '</pre>';
				?>
				<label>
					<span><?php esc_html_e( 'Select Time', 'mage-eventpress' ); ?></span>
					<i class="far fa-clock"></i>
					<select class="formControl" name="mpwem_time" id="mpwem_time">
						<?php foreach ( $all_times as $times ) { ?>
							<option value="<?php echo esc_attr( $hidden_date . ' ' . $times['start']['time'] ); ?>"><?php echo esc_html( $times['start']['label'] ); ?></option>
						<?php } ?>
					</select>
				</label>
				<?php
				die();
			}
		}
		new MPWEM_Hooks();
	}