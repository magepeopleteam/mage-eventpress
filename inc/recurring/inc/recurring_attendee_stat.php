<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

add_action('admin_menu', 'mep_recurring_attendee_stat_menu');
function mep_recurring_attendee_stat_menu()
{
    add_submenu_page('edit.php?post_type=mep_events', __('Recurring Event Attendee Stat', 'mage-eventpress'), __('Recurring Event Attendee Stat', 'mage-eventpress'), 'manage_woocommerce', 'attendee_stat_list', 'mep_recurring_attendee_stat_dashboard');
}

function mep_recurring_attendee_stat_dashboard(){
    $event_id =  0;
?>
<style>
    .attendee_filter_section ul {
        list-style: none;
        padding: 0;
        margin: 20px 0;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }

    .attendee_filter_section li {
        margin: 0;
    }

    .attendee_filter_section select,
    .attendee_filter_section input[type="text"] {
        padding: 6px 10px;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        min-width: 220px;
        font-size: 14px;
    }

    #event_attendee_filter_btn {
        background-color: #2271b1;
        border: none;
        color: #fff;
        padding: 8px 16px;
        font-size: 14px;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    #event_attendee_filter_btn:hover {
        background-color: #135e96;
    }

    .mep-processing {
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        margin: 20px 0;
    }
    .mep_everyday_date_secs h3 {
        display: none;
    }
    .wrap h2 {
        margin-bottom: 20px;
    }

    .wp-list-table th,
    .wp-list-table td {
        text-align: center;
        vertical-align: middle;
    }

    .wp-list-table {
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .attendee_filter_section ul {
            flex-direction: column;
            align-items: flex-start;
        }

        .attendee_filter_section select,
        .attendee_filter_section input[type="text"] {
            width: 100%;
        }

        #event_attendee_filter_btn {
            width: 100%;
        }
    }
</style>

<div class="wrap">
        <h2><?php _e('Recurring Event Attendee Stat. List', 'mage-eventpress'); ?></h2>
        <div class='attendee_filter_section'>           
            <ul>                
                <li>
                    <div class='event_filter'>
                    <select name="event_id" id="mep_event_id" class="select2" required>
                        <option value="0"><?php _e('Select Event', 'mage-eventpress'); ?></option>
                        <?php
                        $args = array(
                            'post_type' => 'mep_events',
                            'posts_per_page' => -1
                        );
                        $loop = new WP_Query($args);
                        $events_query = $loop->posts;
                        foreach ($events_query as $event) {
                            $post_id = $event -> ID;
                            echo $recurring                  = get_post_meta($post_id, 'mep_enable_recurring', true) ? get_post_meta($post_id, 'mep_enable_recurring', true) : 'no';  
                            if($recurring != 'no'){                          
                        ?>
                        <option value="<?php echo $event->ID; ?>" <?php if ($event_id == $event->ID) {  echo 'selected'; } ?>><?php echo get_the_title($event->ID); ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    </div>
                    <div class='attendee_key_filter' style="display: none;">
                        <input type="text" name='filter_key' value='' id='attendee_filter_key'>
                    </div>
                </li>
                <li id='filter_attitional_btn'>
                    <input type="hidden" id='mep_everyday_ticket_time' name='mep_attendee_list_filter_event_date' value='<?php //echo esc_attr($event_date); ?>'>
                </li>
                <?php do_action('mep_attendee_list_filter_form_before_btn'); ?>
                <li>
                    <button id='event_attendee_filter_btn'><?php _e('Filter','mage-eventpress'); ?></button>
                </li>
            </ul>
        </div>        
        <div id='event_attendee_list_table_item'>
        <table class="wp-list-table widefat striped posts">
            <thead>
            <tr>
                    <th><?php _e('Ticket Type Name','mage-eventpress');  ?></th>
                    <th><?php _e('Total Seat','mage-eventpress');  ?></th>
                    <th><?php _e('Total Reserved','mage-eventpress');  ?></th>
                    <th><?php _e('Ticket Sold','mage-eventpress');  ?></th>
                    <th><?php _e('Available Seat','mage-eventpress');  ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" align='center'>
                        <?php _e('Select an event and click the Filter button to view the statistics.','mage-eventpress') ?>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
    <script>
        (function($) {
            'use strict';
            jQuery(document).ready(function($) {
                
                <?php do_action('mep_fb_attendee_list_script'); ?>
            
                jQuery('#event_attendee_filter_btn').on('click', function() {
                    var event_id = jQuery('#mep_event_id').val();

                    // if (event_id > 0) {
                        var filter_by               = $("input[name='attendee_filter_by']:checked").val();
                        var filter_with_category               = $("input[name='filter_with_category']").val();
                        var ev_filter_key           = jQuery('#attendee_filter_key').val();
                        var ev_event_date           = jQuery('#mep_everyday_ticket_time').val();
                        var re_event_date           = jQuery('#mep_recurring_date').val();
                        var re_event_datepicker     = jQuery('#mep_everyday_datepicker').val();
                        var checkin_status          = jQuery('#mep_attendee_checkin').val() ? jQuery('#mep_attendee_checkin').val() : '';
                        var event_date_t            = re_event_date ? re_event_date : ev_event_date;
                        var event_date              = event_date_t != 0 && event_date_t ? event_date_t : re_event_datepicker;
                        
                        jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,                            
                            data: {
                                "action"            : "mep_recurring_ajax_attendee_stat_filter",
                                "filter_by"         : filter_by,
                                "ev_filter_key"     : ev_filter_key,
                                "event_date"        : event_date,
                                "checkin_status"    : checkin_status,
                                "event_id"          : event_id,
                                "filter_with_category"          : filter_with_category
                            },
                            beforeSend: function() {
                                jQuery('#event_attendee_list_table_item').html('<h5 class="mep-processing"><?php echo mep_get_option('mep_event_rec_please_wait_attendee_loading_text', 'label_setting_sec', 'Please wait! Attendee Stat. is Loading..'); ?></h5>');
                            },
                            success: function(data) {
                                jQuery('#event_attendee_list_table_item').html(data);                                                               
                            }
                        });
                    return false;
                });               
            });
        })(jQuery);
    </script>
<?php
}



add_action('wp_ajax_mep_recurring_ajax_attendee_stat_filter', 'mep_recurring_ajax_attendee_stat_filter');
function mep_recurring_ajax_attendee_stat_filter()
{
    $event_id               = isset($_REQUEST['event_id']) ? $_REQUEST['event_id'] : '';
    $event_date             = isset($_REQUEST['event_date']) ? $_REQUEST['event_date'] : '';
    $mep_event_ticket_type  = get_post_meta($event_id, 'mep_event_ticket_type', true) ? get_post_meta($event_id, 'mep_event_ticket_type', true) : array();

    // Check if global quantity addon is active and enabled
    $gq_addon_active = class_exists('MPWEMAGQ_Functions') || function_exists('mep_gq_ticket_total_sold');
    $event_global_qty_status = $gq_addon_active ? (get_post_meta($event_id, 'enable_global_qty', true) ? get_post_meta($event_id, 'enable_global_qty', true) : 'off') : 'off';
    $mep_gq_type = ($gq_addon_active && function_exists('mep_get_option')) ? mep_get_option('mep_gq_type', 'mep_gq_settings', 'global') : 'global';

?>
    <table class="wp-list-table widefat striped posts">
        <thead>
           <tr>
                <th><?php _e('Ticket Type Name','mage-eventpress');  ?></th>
                <th><?php _e('Total Seat','mage-eventpress');  ?></th>
                <th><?php _e('Total Reserved','mage-eventpress');  ?></th>
                <th><?php _e('Ticket Sold','mage-eventpress');  ?></th>
                <th><?php _e('Available Seat','mage-eventpress');  ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($gq_addon_active && $event_global_qty_status == 'on') {
                // Global quantity mode - show global summary first, then individual ticket types
                if ($mep_gq_type == 'global') {
                    // Global quantity for all events
                    $global_total_seat = get_post_meta($event_id, 'mep_gq_total_seat', true);
                    $global_total_resv = get_post_meta($event_id, 'mep_gq_total_resv_seat', true) ? get_post_meta($event_id, 'mep_gq_total_resv_seat', true) : 0;
                    
                    if (!empty($event_date) && function_exists('mep_gq_ticket_total_sold')) {
                        $global_total_sold = mep_gq_ticket_total_sold($event_id, $event_date);
                    } else {
                        $global_total_sold = function_exists('mep_ticket_sold') ? mep_ticket_sold($event_id) : 0;
                    }
                    
                    $global_available = (int)$global_total_seat - ((int)$global_total_sold + (int)$global_total_resv);
                    
                    ?>
                    <tr style="background-color: #f0f8ff; font-weight: bold;">
                        <td><?php _e('🌐 Global Quantity (All Ticket Types)', 'mage-eventpress'); ?></td>
                        <td><?php echo $global_total_seat; ?></td>
                        <td><?php echo $global_total_resv; ?></td>
                        <td><?php echo $global_total_sold; ?></td>
                        <td><?php echo $global_available; ?></td>
                    </tr>
                    <tr><td colspan="5" style="padding: 0; border: none;"><hr style="margin: 5px 0; border: 1px solid #ddd;"></td></tr>
                    <?php
                    
                    // Now show individual ticket type breakdown within global quantity
                    foreach ($mep_event_ticket_type as $field) {
                        $ticket_type_name = array_key_exists('option_name_t',$field) ? mep_remove_apostopie($field['option_name_t']) : '';
                        $original_qty = isset($field['option_qty_t']) ? $field['option_qty_t'] : 0;
                        $original_resv = isset($field['option_rsv_t']) ? $field['option_rsv_t'] : 0;
                        
                        // In global quantity mode, individual ticket type sales
                        if (!empty($event_date)) {
                            $type_sold = function_exists('mep_ticket_type_sold') ? mep_ticket_type_sold($event_id, $ticket_type_name, $event_date) : 0;
                        } else {
                            $type_sold = function_exists('mep_ticket_type_sold') ? mep_ticket_type_sold($event_id, $ticket_type_name) : 0;
                        }
                        
                        ?>
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding-left: 20px;">↳ <?php echo $ticket_type_name; ?> <small>(shares global pool)</small></td>
                            <td><small>Original: <?php echo $original_qty; ?></small></td>
                            <td><small>Original: <?php echo $original_resv; ?></small></td>
                            <td><?php echo $type_sold; ?></td>
                            <td><em><?php _e('Shared from global pool', 'mage-eventpress'); ?></em></td>
                        </tr>
                        <?php
                    }
                    
                } elseif ($mep_gq_type == 'datewise' && function_exists('mep_gq_get_datewise_gq')) {
                    // Date-wise global quantity
                    $datewise_total_seat = mep_gq_get_datewise_gq($event_id, $event_date);
                    $datewise_total_resv = get_post_meta($event_id, 'mep_gq_total_resv_seat', true) ? get_post_meta($event_id, 'mep_gq_total_resv_seat', true) : 0;
                    
                    if (!empty($event_date) && function_exists('mep_gq_ticket_total_sold')) {
                        $datewise_total_sold = mep_gq_ticket_total_sold($event_id, $event_date);
                    } else {
                        $datewise_total_sold = function_exists('mep_ticket_sold') ? mep_ticket_sold($event_id) : 0;
                    }
                    
                    $datewise_available = (int)$datewise_total_seat - ((int)$datewise_total_sold + (int)$datewise_total_resv);
                    
                    ?>
                    <tr style="background-color: #f0f8ff; font-weight: bold;">
                        <td><?php _e('📅 Date-wise Global Quantity (All Ticket Types)', 'mage-eventpress'); ?></td>
                        <td><?php echo $datewise_total_seat; ?></td>
                        <td><?php echo $datewise_total_resv; ?></td>
                        <td><?php echo $datewise_total_sold; ?></td>
                        <td><?php echo $datewise_available; ?></td>
                    </tr>
                    <tr><td colspan="5" style="padding: 0; border: none;"><hr style="margin: 5px 0; border: 1px solid #ddd;"></td></tr>
                    <?php
                    
                    // Show individual ticket type breakdown for date-wise
                    foreach ($mep_event_ticket_type as $field) {
                        $ticket_type_name = array_key_exists('option_name_t',$field) ? mep_remove_apostopie($field['option_name_t']) : '';
                        $original_qty = isset($field['option_qty_t']) ? $field['option_qty_t'] : 0;
                        $original_resv = isset($field['option_rsv_t']) ? $field['option_rsv_t'] : 0;
                        
                        // In date-wise global quantity mode
                        if (!empty($event_date)) {
                            $type_sold = function_exists('mep_ticket_type_sold') ? mep_ticket_type_sold($event_id, $ticket_type_name, $event_date) : 0;
                        } else {
                            $type_sold = function_exists('mep_ticket_type_sold') ? mep_ticket_type_sold($event_id, $ticket_type_name) : 0;
                        }
                        
                        ?>
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding-left: 20px;">↳ <?php echo $ticket_type_name; ?> <small>(shares date-wise pool)</small></td>
                            <td><small>Original: <?php echo $original_qty; ?></small></td>
                            <td><small>Original: <?php echo $original_resv; ?></small></td>
                            <td><?php echo $type_sold; ?></td>
                            <td><em><?php _e('Shared from date pool', 'mage-eventpress'); ?></em></td>
                        </tr>
                        <?php
                    }
                }
            } else {
                // Regular mode - show individual ticket types (fixed properly)
                if (empty($mep_event_ticket_type)) {
                    ?>
                    <tr>
                        <td colspan="5" align='center'>
                            <?php _e('No ticket types configured for this event.','mage-eventpress') ?>
                        </td>
                    </tr>
                    <?php
                } else {
                    // Check if all ticket quantities are 0
                    $all_quantities_zero = true;
                    foreach ($mep_event_ticket_type as $field) {
                        if (isset($field['option_qty_t']) && (int)$field['option_qty_t'] > 0) {
                            $all_quantities_zero = false;
                            break;
                        }
                    }
                    
                    if ($all_quantities_zero) {
                        ?>
                        <tr>
                            <td colspan="5" align='center' style="padding: 20px; background-color: #fff3cd; border: 1px solid #ffeaa7;">
                                <strong><?php _e('⚠️ Configuration Issue', 'mage-eventpress'); ?></strong><br/>
                                <?php _e('All ticket types have quantities set to 0. Please configure ticket quantities in the event settings.', 'mage-eventpress'); ?><br/>
                                <small><?php _e('Go to Events → Edit Event → Ticket Types and set quantities for each ticket type.', 'mage-eventpress'); ?></small>
                            </td>
                        </tr>
                        <?php
                    } else {
                        foreach ($mep_event_ticket_type as $field) {
                            $ticket_type_name       = array_key_exists('option_name_t',$field)  ? mep_remove_apostopie($field['option_name_t']) : '';
                            $total_quantity         = isset($field['option_qty_t']) ? (int)$field['option_qty_t'] : 0;
                            $total_resv_quantity    = isset($field['option_rsv_t']) ? (int)$field['option_rsv_t'] : 0;
                            
                            // Calculate sold tickets correctly for each ticket type
                            if (function_exists('mep_ticket_type_sold')) {
                                if (!empty($event_date)) {
                                    $total_sold = (int)mep_ticket_type_sold($event_id, $ticket_type_name, $event_date);
                                } else {
                                    $total_sold = (int)mep_ticket_type_sold($event_id, $ticket_type_name);
                                }
                            } else {
                                // Fallback if function doesn't exist
                                $total_sold = 0;
                            }
                            
                            // Calculate available tickets properly
                            $total_available_tickets = $total_quantity - ($total_sold + $total_resv_quantity);
                            $total_available_tickets = max(0, $total_available_tickets); // Don't show negative
                            
                            // Show warning for individual ticket types with 0 quantity
                            $is_zero_qty = ($total_quantity == 0);
                            ?>
                           <tr <?php echo $is_zero_qty ? 'style="background-color: #fff3cd;"' : ''; ?>>
                                <td><?php echo $ticket_type_name; if($is_zero_qty) echo ' <small style="color: #856404;">(No quantity set)</small>'; ?></td>
                                <td><?php echo $total_quantity;  ?></td>
                                <td><?php echo $total_resv_quantity;  ?></td>
                                <td><?php echo $total_sold;  ?></td>
                                <td><?php echo $is_zero_qty ? '<span style="color: #856404;">-</span>' : $total_available_tickets;  ?></td>
                            </tr>  
                            <?php 
                        }
                    }
                }
            } ?>          
        </tbody>
    </table>
<?php
    die();
}

add_filter('mep_attendee_stat_recurring','mep_recurring_attendee_stat_recurring', 10, 2);
function mep_recurring_attendee_stat_recurring($stat,$post_id){
   ?>
    <span>
        <b class="mep_seat_stat_info_82">
            <a href="<?php echo get_admin_url(); ?>edit.php?post_type=mep_events&page=attendee_stat_list&event_id=<?php echo $post_id; ?>" style='color:#fff'><?php _e('View Details','mage-eventpress'); ?></a>
        </b>
    </span>
   <?php
}
