<?php
// Template Name: Deora

// Settings Value ::::::::::::::::::::::::::::::::::::::::
$hide_date_details          = mep_get_option('mep_event_hide_date_from_details', 'single_event_setting_sec', 'no');
$hide_time_details          = mep_get_option('mep_event_hide_time_from_details', 'single_event_setting_sec', 'no');
$hide_location_details      = mep_get_option('mep_event_hide_location_from_details', 'single_event_setting_sec', 'no');
$hide_total_seat_details    = mep_get_option('mep_event_hide_total_seat_from_details', 'single_event_setting_sec', 'no');
$hide_org_by_details        = mep_get_option('mep_event_hide_org_from_details', 'single_event_setting_sec', 'no');
$hide_address_details       = mep_get_option('mep_event_hide_address_from_details', 'single_event_setting_sec', 'no');
$hide_schedule_details      = mep_get_option('mep_event_hide_event_schedule_details', 'single_event_setting_sec', 'no');
$hide_share_details         = mep_get_option('mep_event_hide_share_this_details', 'single_event_setting_sec', 'no');
$hide_calendar_details      = mep_get_option('mep_event_hide_calendar_details', 'single_event_setting_sec', 'no');
$speaker_status             = mep_get_option('mep_enable_speaker_list', 'single_event_setting_sec', 'no');
$event_date_icon            = mep_get_option('mep_event_date_icon', 'icon_setting_sec', 'fa fa-calendar');
$event_location_icon        = mep_get_option('mep_event_location_icon', 'icon_setting_sec', 'fas fa-map-marker-alt');
?>
    <section class="mpe-new-event mpe-new-container">
        <h1> <?php do_action('mep_event_only_title'); ?></h1>
        <?php if ($hide_org_by_details == 'no' && has_term('','mep_org',get_the_id())) { ?>
            <h2><?php _e('Organized By:','mage-eventpress'); do_action('mep_event_organizer_name'); ?> </h2>
        <?php } ?>

        <div class="mpe-new-event-meta">
            <div class="mpe-new-location">
                <div class="mpe-new-icon-holder">                  
                    <img src="<?php echo MEP_URL ?>images/deora/assest/location.png" alt="location">
                </div>
                <div class="mpe-new-text-holder">
                    <p><?php do_action('mep_event_location', get_the_id()); ?></p>
                </div>
            </div>
            <div class="mpe-new-time">
                <div class="mpe-new-icon-holder">
                    <img src="<?php echo MEP_URL ?>images/deora/assest/time.png" alt="time">
                </div>
                <div class="mpe-new-text-holder">
                    <p> <?php do_action('mep_event_time_only',get_the_id()); ?> </p>
                </div>
            </div>
        </div>

        <!-- imageSection -->

        <div class="mpe-new-imageSection">
            <div class="mpe-new-thubnail-big">
                    <?php do_action('mep_event_thumbnail'); ?>
            </div>
            <div class="mpe-new-side-thubnail">
                <ul> 
                    <li> <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/Rectangle78.png" alt="thubnail"> </a> </li>
                    <li> <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/Rectangle79.png" alt="thubnail"> </a> </li>
                    <li> <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/Rectangle80.png" alt="thubnail"> </a> </li>
                </ul>
            </div>
        </div>


         <!-- Description Section-->         
         <div class="mpe-new-description-Section">
            <div class="mpe-new-event-desctiption">
            <?php do_action('mep_event_details'); ?>
                <!--Ticket and Prices -->
                <div class="mpe-new-ticket-and-prices">
                    <h2> <?php _e('Ticket and Prices','mage-eventpress'); ?></h2>
                    <p><?php _e('Search ticket availability by date','mage-eventpress'); ?> </p>
                    <div class="mpe-new-tp-form">
                        <form action="/action_page.php">
                            <div class="mpe-new-datepicker">
                                <input type="date" id="date" name="date">
                            </div>
                            <div class="mpe-new-timepicker">
                                <input type="time" id="time" name="time">
                            </div>
                          </form>
                    </div>
                </div>

                <div class="mpe-new-ticket">
                    <div class="mpe-new-ticket-card">
                        <div class="mpe-new-ticket-info">
                            <h3>General Admission</h3>
                            <span> Sales End On Mar 12, 2023</span>
                            <p>General Admission to the Cruise for one person. Contact rsvp@iboatnyc.com for VIP
                                upgrades & additional information.</p>
                        </div>
                        <div class="mpe-new-ticket-price">
                            <p class="mpe-new-price"><span>$</span>10.99</p>
                            <p> incl Vat <span>$</span>2.99</p>
                            <div class="mpe-new-buy-ticket">
                                <button style="background-color: #FF2424;">-</button>
                                <input class="mpe-new-input-group-field" type="number" name="quantity" value="0">
                                <button style="background-color: #6046FF;">+</button>
                            </div>    
                        </div>
                    </div>

                    <div class="mpe-new-ticket-card">
                        <div class="mpe-new-ticket-info">
                            <h3>VIP Tickets</h3>
                            <span> Sales End On Mar 12, 2023</span>
                            <p>VIP Skip the Line and entry to the Cruise for one person. Contact rsvp@iboatnyc.com
                                for bottle service & additional information.</p>
                        </div>
                        <div class="mpe-new-ticket-price">
                            <p class="mpe-new-price"><span>$</span>52.99</p>
                            <p> incl Vat <span>$</span>2.99</p>
                            <div class="mpe-new-buy-ticket">
                                <button style="background-color: #FF2424;">-</button>
                                <input class="mpe-new-input-group-field" type="number" name="quantity" value="0">
                                <button style="background-color: #6046FF;">+</button>
                            </div>    
                        </div>
                    </div>


                    <div class="mpe-new-ticket-card">
                        <div class="mpe-new-ticket-info">
                            <h3>The Lady Liberty VIP Bottle Package Deposit</h3>
                            <span> Sales End On Mar 12, 2023</span>
                            <p>VIP Skip the Line and entry to the Cruise for one person. Contact rsvp@iboatnyc.com
                                for bottle service & additional information.</p>
                        </div>
                        <div class="mpe-new-ticket-price">
                            <p class="mpe-new-price"><span>$</span>10.99</p>
                            <p> incl Vat <span>$</span>2.99</p>
                            <div class="mpe-new-buy-ticket">
                                <button style="background-color: #FF2424;">-</button>
                                <input class="mpe-new-input-group-field" type="number" name="quantity" value="0">
                                <button style="background-color: #6046FF;">+</button>
                            </div>    
                        </div>

                    </div>

                </div>
                <div class="mpe-new-ticket-btns">
                    <a href="#" class="mpe-new-totalPrice"> Total Price <span>$</span><span>5</span></a>
                    <a href="#" class="mpe-new-register"> Register Events</a>
                </div>


                <!-- Schedule Details -->
                <div class="mpe-new-schedule-details">
                    <h2>Schedule Details</h2>
                    <div class="mpe-new-sechule-container">




                        <div class="mpe-new-schedules">                            
                            <div class="mpe-new-icon mpe-new-iconEffect">
                                <img src="<?php echo MEP_URL ?>images/deora/assest/boost.png" alt="boost">
                            </div>
                            <div class="mpe-new-details">
                                <p><span>Day1:</span>29 Dec-2020 - We Started</p>
                                <span class="mpe-new-detailed-text">We will start the journey from Gabtoli by a AC Bus.</span>
                            </div>
                        </div>
    
                        <div class="mpe-new-schedules">
                            <div class="mpe-new-icon mpe-new-iconEffect">
                                <img src="<?php echo MEP_URL ?>images/deora/assest/sign.png" alt="sign">
                            </div>
                            <div class="mpe-new-details">
                                <p><span style="color: #6046FF;">Day2:</span>We Will Reached In The Location</p>
                                <span  class="mpe-new-detailed-text" >At the Morning we will reach in our location and check into the Hotel</span>
                            </div>
                        </div>    
                        <div class="mpe-new-schedules">
                            <div class="mpe-new-icon">
                                <img src="<?php echo MEP_URL ?>images/deora/assest/flag.png" alt="flag">
                            </div>
                            <div class="mpe-new-details">
                                <p><span style="color: #6046FF;">Day3:</span>Finish</p>
                                <span  class="mpe-new-detailed-text" > After lots of enjoyment and refreshment we will back to our home</span>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <div class="mpe-new-description-sidebar">
                <h2>When and where</h2>
                <div class="mpe-new-date-and-time-card">
                    <div class="mpe-new-ww-card">

                        <div class="mpe-new-card-icon">
                            <a href="#"><img src="<?php echo MEP_URL ?>images/deora/assest/Icon.png" alt="icon"></a>
                        </div>

                        <div class="mpe-new-card-content">                            
                            <h3>Date & Time</h3>
                            <p>Sun, March 12, 2023,<br> 
                            7:45 PM – 9:15 PM EDT</p>
                           <div class="mpe-new-card-btn">
                                <button> <a href="#"><img src="<?php echo MEP_URL ?>images/deora/assest/addtocalender.png" alt="icon"> Add to calendar</a></button>
                           </div>
                        </div>


                    </div>
                    <div class="mpe-new-ww-card">
                        <div class="mpe-new-card-icon">
                            <a href="#"><img src="<?php echo MEP_URL ?>images/deora/assest/location.png" alt="icon"></a>
                        </div>
                        <div class="mpe-new-card-content">
                            <h3>Location</h3>
                            <p>Sour Mouse 110 Delancey Street <br> New York, NY 10002 United States</p>
                           <div class="mpe-new-card-btn">
                                <button> <a href="#"><img src="<?php echo MEP_URL ?>images/deora/assest/locationicon.png" alt="icon"> Find in map</a></button>
                           </div>
                        </div>
                    </div>
                    <div class="mpe-new-ww-social-card">
                        <h2>Share This Event </h2>
                        <ul> 
                            <li> <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/facebook.png" alt="facebook" > </a> </li>
                            <li> <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/twitter.png" alt="twitter" ></a> </li>
                            <li> <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/insta.png" alt="instagram" > </a> </li>
                            <li> <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/Vector.png" alt="ticktok" > </a> </li>
                        </ul>
                    </div>
                </div>

                <!-- Speaker  -->
                <div class="mpe-new-speaker">
                    <h2>Speaker</h2>
                    <div class="mpe-new-speaker-card">
                        <div class="mpe-new-speaker-image">
                           <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse6.png" alt="alex jason"> </a>
                        </div>
                        <div class="mpe-new-speaker-data">
                            <h3>Alex Jason</h3>
                            <p> Motivational Speaker</p>
                        </div>
                    </div>

                    <div class="mpe-new-speaker-card">
                        <div class="mpe-new-speaker-image">
                           <a href="#"> <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse7.png" alt="Robinson Hudd"> </a>
                        </div>
                        <div class="mpe-new-speaker-data">
                            <h3>Robinson Hudd</h3>
                            <p> Guest Speaker</p>
                        </div>
                    </div>

                </div>
            </div>
         </div>

    </section>    

    <section class="mpe-new-map-location mpe-new-container">
        <h2>Map Location</h2>
        <div class="mpe-new-map">
            <iframe
            width="100%"
            height="450"
            style="border:0"
            loading="lazy"
            allowfullscreen
             referrerpolicy="no-referrer-when-downgrade"
                 src="https://www.google.com/maps/embed/v1/place?key=API_KEY
                  &q=Space+Needle,Seattle+WA">
            </iframe>
        </div>
    </section>

    <section class="mpe-new-faq-section mpe-new-container">
        <div class="mpe-new-faqHeader">
            <h2> Frequenly asked questions</h2>
            <p>We provide a complete service for the sale, purchase or rental of real estate. We provide a complete We provide a complete service for the sale.</p>
        </div>
        <div class="mpe-new-faqs">
            <div class="mpe-new-questions">
                <button class="mpe-new-accordion">how can i attend the event in time ?</button>
            <div class="mpe-new-panel">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
            </div>

            <div class="mpe-new-questions">
                <button class="mpe-new-accordion">how can i attend the event in time ?</button>
            <div class="mpe-new-panel">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
            </div>

            <div class="mpe-new-questions">
                <button class="mpe-new-accordion">how can i attend the event in time ?</button>
            <div class="mpe-new-panel">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
            </div>

            <div class="mpe-new-questions">
                <button class="mpe-new-accordion">how can i attend the event in time ?</button>
            <div class="mpe-new-panel">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
            </div>

            <div class="mpe-new-questions">
                <button class="mpe-new-accordion">how can i attend the event in time ?</button>
            <div class="mpe-new-panel">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
            </div>

            <div class="mpe-new-questions">
                <button class="mpe-new-accordion">how can i attend the event in time ?</button>
            <div class="mpe-new-panel">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
            </div>
        </div>
    </section>

    <section class="mpe-new-attendee mpe-new-container">
        <h2>Attende<span>(14)</span></h2>
        <div class="mpe-new-attende-container">
            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse8.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse81.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse82.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse83.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse84.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse85.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse86.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse87.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse88.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse89.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse810.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse811.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse812.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>

            <div class="mpe-new-attende-details">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Ellipse813.png"  alt="Maria Biyonce">
                <p>Maria Biyonce</p>
            </div>
        </div>
    </section>

    <section class="mpe-new-related-events mpe-new-container">
        <h2> Related Events</h2>
        <div id="owl-demo" class="owl-carousel owl-theme">
          
            <div class="item">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Rectangle5.png" alt="Rectangle5">
                <div class="mpe-new-related-items-row">
                    <div class="mpe-new-related-content">
                        <h3>Dj party</h3>
                        <span class="mpe-new-events-place">Richardson Road, NY</span>
                    </div>
                    <div class="mpe-new-related-item-price">
                        <p>$11.99</p>
                        <span class="mpe-new-price-category">Per Ticket</span>
                    </div>
                </div>
            </div>
    
            <div class="item">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Rectangle51.png" alt="Rectangle5">
                <div class="mpe-new-related-items-row">
                    <div class="mpe-new-related-content">
                        <h3>Dj party</h3>
                        <span class="mpe-new-events-place">Richardson Road, NY</span>
                    </div>
                    <div class="mpe-new-related-item-price">
                        <p>$11.99</p>
                        <span class="mpe-new-price-category">Per Ticket</span>
                    </div>
                </div>
            </div>
    
            <div class="item">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Rectangle52.png" alt="Rectangle5">
                <div class="mpe-new-related-items-row">
                    <div class="mpe-new-related-content">
                        <h3>Dj party</h3>
                        <span class="mpe-new-events-place">Richardson Road, NY</span>
                    </div>
                    <div class="mpe-new-related-item-price">
                        <p>$11.99</p>
                        <span class="mpe-new-price-category">Per Ticket</span>
                    </div>
                </div>
            </div>
           
            <div class="item">
                <img src="<?php echo MEP_URL ?>images/deora/assest/Rectangle5.png" alt="Rectangle5">
                <div class="mpe-new-related-items-row">
                    <div class="mpe-new-related-content">
                        <h3>Dj party</h3>
                        <span class="mpe-new-events-place">Richardson Road, NY</span>
                    </div>
                    <div class="mpe-new-related-item-price">
                        <p>$11.99</p>
                        <span class="mpe-new-price-category">Per Ticket</span>
                    </div>
                </div>
            </div>
          </div>
    </section>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<script type="text/javascript">
       $(document).ready(function() {
 
 $("#owl-demo").owlCarousel({
   navigation : true,
   autoplay : true,
   autoplayTimeout : 2000,
   autoplayHoverPause : true,
   responsive : {
    0:{
        items : 1,
        nav : false
    },
    600:{
        items : 2,
        nav : false
    },
    1000:{
        items : 3,
        nav : false
    }
   }
 });

});
    </script>

<script>
    var acc = document.getElementsByClassName("mpe-new-accordion");
    var i;
    
    for (i = 0; i < acc.length; i++) {
      acc[i].addEventListener("click", function() {
        this.classList.toggle("mpe-new-active");
        var panel = this.nextElementSibling;
        if (panel.style.maxHeight) {
          panel.style.maxHeight = null;
        } else {
          panel.style.maxHeight = panel.scrollHeight + "px";
        } 
      });
    }
    </script>