<script>
<?php 
if($time_status == 'yes'){ 
    $time_auto_select   = mep_get_option('mep_auto_select_first_time', 'general_setting_sec', 'yes');
?>

function mep_re_timeList_loading(){
    var event_date = jQuery('#mep_everyday_datepicker').val();          
            var event_id = jQuery('#mep_event_id').val();          
              jQuery.ajax({
                type: 'POST',                
                url: ajaxurl,
                data: {
                    "action": "mep_re_ajax_load_ticket_time_list", 
                    "nonce"     : '<?php echo wp_create_nonce('mep-ajax-recurring-nonce'); ?>',
                    "event_date":event_date, 
                    "event_id":event_id
                    },
                    beforeSend: function(){
                        jQuery('#mep_everyday_event_time_list').html('<span class=search-text style="display:block;background:#ddd:color:#000:font-weight:bold;text-align:center"><?php echo mep_get_option( 'mep_event_rec_please_wait_time_loading_text', 'label_setting_sec', 'Time List is Loading..'); ?></span>');
                    },
                    success: function(data){                       
                            jQuery('#mep_everyday_event_time_list').html(data);   
                            jQuery('#mep_recutting_ticket_type_list').html('<h5 class="mep-warning"><?php echo mep_get_option( 'mep_event_rec_please_select_time_text', 'label_setting_sec', __('Please Select Time','mage-eventpress-re')); ?></h5>');                       
                            jQuery('#mep_recurring_extra_service_list').html('');  
                            <?php if($time_auto_select == 'yes'){ ?>
                            jQuery('#mep_everyday_ticket_time option[class="availabe-date"]').first().attr("selected", "selected");   
                            <?php } ?>
                            mep_rec_ticketType_on_time_change();                                                    
                    }
                });
               return false;
}

jQuery('#mep_everyday_datepicker').on('change', function () {
        mep_re_timeList_loading();
}); 

jQuery(document).ready(function ($) {
    mep_re_timeList_loading();
});


<?php }else{ ?>

    jQuery(document).ready(function ($) {

        var event_date = jQuery('#mep_everyday_datepicker').val();          
            var event_id = jQuery('#mep_event_id').val();  
            jQuery.ajax({
                type: 'POST',
                // url:mep_ajax.mep_ajaxurl,
                url: ajaxurl,
                data: {
                    "action"        : "mep_re_ajax_load_ticket_type_list", 
                    "nonce"     : '<?php echo wp_create_nonce('mep-ajax-recurring-nonce'); ?>',
                    "event_date"    :event_date, 
                    "is_admin"    :'<?php if(is_admin()){ echo 1; }else{ echo 0; } ?>', 
                    "event_id"      :event_id
                    },
                    beforeSend: function(){
                        jQuery('#mep_recutting_ticket_type_list').html('<h5 class="mep-processing"><?php echo mep_get_option( 'mep_event_rec_please_wait_ticket_loading_text', 'label_setting_sec', 'Please wait! Ticket List is Loading..'); ?></h5>');                       
                    },
                    success: function(data){                       
                           jQuery('#mep_recutting_ticket_type_list').html(data);    
                                                    
                    }
                });
               return false;

    });

    jQuery(document).ready(function ($) {

        var event_date = jQuery('#mep_everyday_datepicker').val();          
            var event_id = jQuery('#mep_event_id').val();  
            jQuery.ajax({
                type: 'POST',
                // url:mep_ajax.mep_ajaxurl,
                url: ajaxurl,
                data: {
                    "action": "mep_re_ajax_load_extra_service_list", 
                    "nonce"     : '<?php echo wp_create_nonce('mep-ajax-recurring-nonce'); ?>',
                    "event_date":event_date, 
                    "event_id":event_id
                },
                    beforeSend: function(){
                        jQuery('#mep_recurring_extra_service_list').html('');                       
                    },
                    success: function(data){                       
                           jQuery('#mep_recurring_extra_service_list').html(data);    
                                                    
                    }
                });
               return false;

    });

jQuery('#mep_everyday_datepicker').on('change', function () {
            var event_date = jQuery(this).val();          
            var event_id = jQuery('#mep_event_id').val();  
            jQuery.ajax({
                type: 'POST',
                // url:mep_ajax.mep_ajaxurl,
                url: ajaxurl,
                data: {
                        "action": "mep_re_ajax_load_ticket_type_list", 
                        "nonce"     : '<?php echo wp_create_nonce('mep-ajax-recurring-nonce'); ?>',
                        "event_date":event_date, 
                        "is_admin"    :'<?php if(is_admin()){ echo 1; }else{ echo 0; } ?>', 
                        "event_id":event_id
                    },
                    beforeSend: function(){
                        jQuery('#mep_recutting_ticket_type_list').html('<h5 class="mep-processing"><?php echo mep_get_option( 'mep_event_rec_please_wait_ticket_loading_text', 'label_setting_sec', 'Please wait! Ticket List is Loading..'); ?></h5>');                       
                    },
                    success: function(data){                       
                           jQuery('#mep_recutting_ticket_type_list').html(data);    
                                                    
                    }
                });

            jQuery.ajax({
                type: 'POST',
                // url:mep_ajax.mep_ajaxurl,
                url: ajaxurl,
                data: {
                    "action"    : "mep_re_ajax_load_extra_service_list", 
                    "nonce"     : '<?php echo wp_create_nonce('mep-ajax-recurring-nonce'); ?>',
                    "event_date":event_date, 
                    "event_id"  :event_id
                    },
                beforeSend: function(){
                        jQuery('#mep_recurring_extra_service_list').html('');                       
                    },
                    success: function(data){                       
                           jQuery('#mep_recurring_extra_service_list').html(data);    
                                                    
                    }
                });
               return false;

}); 
<?php } ?>
</script>