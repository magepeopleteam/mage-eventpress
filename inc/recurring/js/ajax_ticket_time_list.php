
<script>
jQuery('#mep_everyday_ticket_time').on('change', function () {
    mep_rec_ticketType_on_time_change();
}); 

function mep_rec_ticketType_on_time_change(){

            var event_date = jQuery('#mep_everyday_ticket_time option:selected').val();          
            var event_date_text = jQuery('#mep_everyday_ticket_time option:selected').html();          
            jQuery('#time_slot_name').val(event_date_text);          
            var event_id = jQuery('#mep_event_id').val(); 
            if(event_date == 0){ 
                // jQuery('#mep_recutting_ticket_type_list').hide(); 
                jQuery('#mep_recutting_ticket_type_list').html('<h5 class="mep-warning"><?php echo mep_get_option( 'mep_event_rec_please_select_time_text', 'label_setting_sec', __('Please Select Time','mage-eventpress-re')); ?></h5>'); 
            }else{

                jQuery.ajax({
                type: 'POST',                
                url: ajaxurl,
                data: {"action": 
                        "mep_re_ajax_load_ticket_type_list", 
                        "nonce"     : '<?php echo wp_create_nonce('mep-ajax-recurring-nonce'); ?>',
                        "event_date":event_date, 
                        "is_admin"    :'<?php if(is_admin()){ echo 1; }else{ echo 0; } ?>', 
                        "event_id":event_id
                    },
                    beforeSend: function(){
                        jQuery('#mep_recutting_ticket_type_list').show(); 
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
                
            }
               return false;
}
</script>