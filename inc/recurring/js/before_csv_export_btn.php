<script>
jQuery(document).ready(function($){
    <?php 
 

    $event_off_days        = get_post_meta($event_id,'mep_ticket_offdays',true) ? maybe_unserialize(get_post_meta($event_id,'mep_ticket_offdays',true)) : [];

    $global_off_dates       = get_post_meta($event_id,'mep_ticket_off_dates',true) ? maybe_unserialize(get_post_meta($event_id,'mep_ticket_off_dates',true)) : [];
    
    
 
    $global_off_dates_arr = [];
    $off_dates = '';
    if(sizeof($global_off_dates) > 0){
        foreach ($global_off_dates as $off_dates) {    
            $global_off_dates_arr[] = '"'.date('j-n-Y',strtotime($off_dates['mep_ticket_off_date'])).'"';
        }
        $off_dates = implode(',',$global_off_dates_arr);
    }
    
 
    $global_off_days_arr = [];
    $off_days = '';
    
    if(sizeof($event_off_days) > 0){
        foreach ($event_off_days as $off_days) { 

            if($off_days == 'sat'){
                $off_days = 'satur';
            } 
            
            if($off_days == 'tue'){
                $off_days = 'tues';
            } 

            if($off_days == 'wed'){
                $off_days = 'wednes';
            } 

            if($off_days == 'thu'){
                $off_days = 'thurs';
            }                         
            $global_off_days_arr[] = '['.ucwords($off_days.'day').']';
        }

        $off_days = implode(',',$global_off_days_arr);
    }

    $event_start_date = date('Y-m-d',strtotime(get_post_meta($event_id,'event_start_date',true)));
    $event_end_date = date('Y-m-d',strtotime(get_post_meta($event_id,'event_end_date',true))); 
    $interval = get_post_meta($event_id,'mep_repeated_periods',true) ? get_post_meta($event_id,'mep_repeated_periods',true) : 1;        
    $period = mep_re_get_repeted_event_period_date_arr($event_start_date,$event_end_date,$interval);

    $global_on_days_arr = [];
    $events_days = '';    
        foreach ($period as $key => $value) {
            $global_on_days_arr[] = '"'.date('j-n-Y',strtotime($value->format('Y-m-d'))).'"';
        }
        $last_date = end($global_on_days_arr);
        $last_date = str_replace('"','',$last_date);
        $add_one_date = '"'.date('j-n-Y', strtotime($last_date . ' +1 day')).'"';
        if($interval == 1){   array_push($global_on_days_arr, $add_one_date); }
        $events_days = implode(',',$global_on_days_arr);
        $end_year = date('Y',strtotime($event_end_date));
        $end_month = (date('n',strtotime($event_end_date)) - 1);
        $end_day = date('j',strtotime($event_end_date));
    ?>
    jQuery("#mep_everyday_datepicker_csv_<?php echo $event_id; ?>").datepicker({ 
        dateFormat: 'yy-mm-dd',
        minDate:-0,
        maxDate: new Date(<?php echo $end_year; ?>, <?php echo $end_month; ?>, <?php echo $end_day; ?>),
        beforeShowDay: nonWorkingDates_<?php echo $event_id; ?>,  
    }); 
    

function nonWorkingDates_<?php echo $event_id; ?>(date){

var unavailableDates = [<?php echo $off_dates; ?>];
var availableDates = [<?php echo $events_days; ?>];

    var day = date.getDay(), Sunday = 0, Monday = 1, Tuesday = 2, Wednesday = 3, Thursday = 4, Friday = 5, Saturday = 6;
    var closedDates = [[7, 29, 2009], [8, 25, 2010]];
    var closedDays = [<?php echo $off_days; ?>];
    for (var i = 0; i < closedDays.length; i++) {
        if (day == closedDays[i][0]) {
            return [false];
        }
    }

    for (i = 0; i < closedDates.length; i++) {
        if (date.getMonth() == closedDates[i][0] - 1 &&
        date.getDate() == closedDates[i][1] &&
        date.getFullYear() == closedDates[i][2]) {
            return [false];
        }
    }

    var dmy = date.getDate() + "-" + (date.getMonth()+1) + "-" + date.getFullYear();
    if (jQuery.inArray(dmy, availableDates) != -1 && jQuery.inArray(dmy, unavailableDates) == -1) {
    return [true, "","Available"];
  } else {
    return [false,"","unAvailable"];
  }

    return [true];
}
    
    
// jQuery("#mep_everyday_datepicker_csv").datepicker({ 
//         dateFormat: "yy-mm-dd",
//         minDate:0,
//         beforeShowDay: nonWorkingDates,  
// });
    
    
    
    
    
    
    <?php
    
    
    
    if($time_status == 'yes'){ ?>
jQuery('#mep_everyday_datepicker_csv_<?php echo $event_id; ?>').on('change', function () {
            var event_date = jQuery(this).val();          
            var event_id = <?php echo $event_id; ?>;          
              jQuery.ajax({
                type: 'POST',
                // url:mep_ajax.mep_ajaxurl,
                url: ajaxurl,
                data: {
                    "action": "mep_re_ajax_load_ticket_time_list", 
                    "nonce"     : '<?php echo wp_create_nonce('mep-ajax-recurring-nonce'); ?>',
                    "event_date":event_date, 
                    "event_id":event_id
                    },
                    beforeSend: function(){
                        jQuery('#mep_everyday_event_time_list_csv_<?php echo $event_id; ?>').html('<span class=search-text style="display:block;background:#ddd:color:#000:font-weight:bold;text-align:center"><?php echo mep_get_option( 'mep_event_rec_please_wait_time_loading_text', 'label_setting_sec', 'Time List is Loading..'); ?></span>');
                    },
                    success: function(data){                       
                            jQuery('#mep_everyday_event_time_list_csv_<?php echo $event_id; ?>').html(data);                           
                    }
                });
               return false;

}); 
<?php }else{ ?>

jQuery('#mep_everyday_datepicker_csv_<?php echo $event_id; ?>').on('change', function () {
            var event_date = jQuery(this).val();          
            var event_id = jQuery('#mep_event_id').val();  
            jQuery.ajax({
                type: 'POST',
                // url:mep_ajax.mep_ajaxurl,
                url: ajaxurl,
                data: {"action": 
                        "mep_re_ajax_load_ticket_type_list", 
                        "nonce"     : '<?php echo wp_create_nonce('mep-ajax-recurring-nonce'); ?>',
                        "event_date":event_date, 
                        "is_admin"    :'<?php if(is_admin()){ echo 1; }else{ echo 0; } ?>', 
                        "event_id":event_id
                    },
                    beforeSend: function(){
                        jQuery('#mep_everyday_event_time_list_csv_<?php echo $event_id; ?>').html('<span class=search-text style="display:block;background:#ddd:color:#000:font-weight:bold;text-align:center"><?php echo mep_get_option( 'mep_event_rec_please_wait_ticket_loading_text', 'label_setting_sec', 'Please wait! Ticket List is Loading..'); ?></span>');
                    },
                    success: function(data){                       
                            jQuery('#mep_everyday_event_time_list_csv_<?php echo $event_id; ?>').html(data);                           
                    }
                });
               return false;

}); 

<?php } ?>
}); 
</script>