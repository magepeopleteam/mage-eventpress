<?php
	// Template Name: Smart Theme
	// Settings Value :::::::::::::::::::::::::::::::::::::::;
	$event_id           = empty( $event_id ) ? get_the_id() : $event_id;
	$all_dates          = MPWEM_Functions::get_dates( $event_id );
	$all_times          = MPWEM_Functions::get_times( $event_id, $all_dates );
	$upcoming_date      = MPWEM_Functions::get_upcoming_date_time( $event_id, $all_dates, $all_times );
	$hide_share_details = mep_get_option( 'mep_event_hide_share_this_details', 'single_event_setting_sec', 'no' );
	//echo '<pre>';print_r($all_dates);echo '</pre>';
?>
<div class="mpStyle mep_smart_theme">
	<?php do_action( 'mpwem_title', $event_id ); ?>
	<?php do_action( 'mpwem_organizer', $event_id ); ?>
    <div class="mpwem_location_time">
		<?php do_action( 'mpwem_location', $event_id ); ?>
		<?php do_action( 'mpwem_time', $event_id, $all_dates, $all_times ); ?>
    </div>
    <div class="_mT mpwem_slider_area">
		<?php do_action( 'add_mp_custom_slider', $event_id, 'mep_gallery_images' ); ?>
    </div>
    <div class="mpwem_content_area">
        <div class="mpwem_left_content">
			<?php if ( get_the_content( $event_id ) ) { ?>
                <div class="mpwem_details">
                    <h2 class="_mB"><?php esc_html_e( 'Event  Description', 'mage-eventpress' ); ?></h2>
                    <div class="mpwem_details_content"><?php the_content(); ?></div>
                </div>
			<?php } ?>
			<?php do_action( 'mpwem_registration', $event_id, $all_dates, $all_times, $upcoming_date ); ?>
			
		</div>
        <div class="mpwem_right_content">
			<h2 class="_mB"><?php esc_html_e( 'When and where', 'mage-eventpress' ); ?></h2>
			
			<div class="mpwem_sidebar_content">
				<div class="date_widgets">
					<i class="fa fa-calendar"></i>
					<div>
						<h2>Date & Time</h2>
						<p>Sunday, March 12, 2023</p>
						<p>7:45 PM – 9:15 PM EDT</p>
						<button>
							<i class="fa fa-calendar"></i>
							Add To Calender
						</button>
					</div>
				</div>
				<div class="location_widgets">
					<i class="fa fa-map-marker"></i>
					<div>
						<h2>Location</h2>
						<p>110 Delancey Street, New York, NY 10002, United States. </p>
						<button>
							<i class="fa fa-map-marker"></i>
							Find In Map
						</button>
					</div>
				</div>
				<div class="share_widgets">
					<h2>Share This Event</h2>
					<div class="icons">
						<i class="fab fa-facebook-f"></i>
						<i class="fab fa-twitter"></i>
						<i class="fab fa-instagram"></i>
						<i class="fab fa-youtube"></i>
					</div>
				</div>
			</div>
        </div>
		
    </div>
	<div class="map_location">
		<h2>Map Location</h2>
		<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1826.184348490902!2d90.33731996068609!3d23.734228205284513!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755bf01ff8f38b3%3A0x6fb57a2fb59bb549!2sMagePeople%2C%20Inc!5e0!3m2!1sen!2sbd!4v1734057814435!5m2!1sen!2sbd" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
	</div>
	<div class="faq_area">
		<h2>Frequently asked questions</h2>
		<p class="desc">Lorem ipsum dolor sit amet consectetur adipisicing elit. Veritatis odio quisquam assumenda deleniti illum eius cum? Autem in laboriosam quisquam deserunt esse. Expedita omnis consectetur repudiandae ipsa saepe? Eligendi, officiis.</p>
		<div class="faq_items">
			<div class="item">
				<div class="title">
					<h2>How can I Attend the event in just time?</h2>
					<i class="fa fa-chevron-right"></i>
				</div>
				<div class="content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem necessitatibus veniam illum adipisci officiis obcaecati accusantium qui nulla optio sequi corrupti deleniti sed eius commodi labore, ipsa perspiciatis quam minus.
				</div>
			</div>
			<div class="item">
				<div class="title">
					<h2>How can I Attend the event in just time?</h2>
					<i class="fa fa-chevron-right"></i>
				</div>
				<div class="content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem necessitatibus veniam illum adipisci officiis obcaecati accusantium qui nulla optio sequi corrupti deleniti sed eius commodi labore, ipsa perspiciatis quam minus.
				</div>
			</div>
			<div class="item">
				<div class="title">
					<h2>How can I Attend the event in just time?</h2>
					<i class="fa fa-chevron-right"></i>
				</div>
				<div class="content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem necessitatibus veniam illum adipisci officiis obcaecati accusantium qui nulla optio sequi corrupti deleniti sed eius commodi labore, ipsa perspiciatis quam minus.
				</div>
			</div>
			<div class="item">
				<div class="title">
					<h2>How can I Attend the event in just time?</h2>
					<i class="fa fa-chevron-right"></i>
				</div>
				<div class="content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem necessitatibus veniam illum adipisci officiis obcaecati accusantium qui nulla optio sequi corrupti deleniti sed eius commodi labore, ipsa perspiciatis quam minus.
				</div>
			</div>
			<div class="item">
				<div class="title">
					<h2>How can I Attend the event in just time?</h2>
					<i class="fa fa-chevron-right"></i>
				</div>
				<div class="content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem necessitatibus veniam illum adipisci officiis obcaecati accusantium qui nulla optio sequi corrupti deleniti sed eius commodi labore, ipsa perspiciatis quam minus.
				</div>
			</div>
			<div class="item">
				<div class="title">
					<h2>How can I Attend the event in just time?</h2>
					<i class="fa fa-chevron-right"></i>
				</div>
				<div class="content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem necessitatibus veniam illum adipisci officiis obcaecati accusantium qui nulla optio sequi corrupti deleniti sed eius commodi labore, ipsa perspiciatis quam minus.
				</div>
			</div>
		</div>
	</div>
	<div class="attendee_area">
			<h2>Attendee (14)</h2>
			<div class="attendee_lists">
				<div class="attendee">
					<img src="https://images.pexels.com/photos/32976/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/1040880/pexels-photo-1040880.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/1043474/pexels-photo-1043474.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/1516680/pexels-photo-1516680.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/846741/pexels-photo-846741.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/1043473/pexels-photo-1043473.jpeg?auto=compress&cs=tinysrgb&w=600" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/1212984/pexels-photo-1212984.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/3220388/pexels-photo-3220388.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/977796/pexels-photo-977796.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/29745523/pexels-photo-29745523/free-photo-of-elderly-man-in-xicotepec-de-juarez-outdoors.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/242829/pexels-photo-242829.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/39866/entrepreneur-startup-start-up-man-39866.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
				<div class="attendee">
					<img src="https://images.pexels.com/photos/2182970/pexels-photo-2182970.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
					<h2>Maria Biyonce</h2>
				</div>
			</div>
	</div>
	<div class="related_events">
		<h2>Related Events</h2>
		<div class="related_items">
			<div class="item">
				<img src="https://images.pexels.com/photos/247599/pexels-photo-247599.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
				<div class="item-info">
					<div class="title">
						<h2>Lorem ipsum</h2>
						<p>Lorem ipsum dolor sit.</p>
					</div>
					<div class="price">
						<h2>$11.22</h2>
						<p>Per Ticket</p>
					</div>
				</div>
			</div>
			<div class="item">
				<img src="https://images.pexels.com/photos/248159/pexels-photo-248159.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
				<div class="item-info">
					<div class="title">
						<h2>Lorem ipsum</h2>
						<p>Lorem ipsum dolor sit.</p>
					</div>
					<div class="price">
						<h2>$11.22</h2>
						<p>Per Ticket</p>
					</div>
				</div>
			</div>
			<div class="item">
				<img src="https://images.pexels.com/photos/1042423/pexels-photo-1042423.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="">
				<div class="item-info">
					<div class="title">
						<h2>Lorem ipsum</h2>
						<p>Lorem ipsum dolor sit.</p>
					</div>
					<div class="price">
						<h2>$11.22</h2>
						<p>Per Ticket</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>