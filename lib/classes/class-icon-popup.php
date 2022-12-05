<?php
/*
* Author 	:	MagePeople Team
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.

if ( ! class_exists( 'class_icon_popup' ) ) {
    class class_icon_popup {

        public function __construct() {
            add_action( 'admin_footer',array($this,'mep_admin_icon_scripts'));
        }

        function mep_admin_icon_scripts(){
            $icon_library = new mep_icon_library();
            $icon_library_list = $icon_library->mep_fontawesome_icons();
            ?>
            <script>
                jQuery(document).ready(function(){
                            // Global Icon Popup
                            jQuery('.mep_global_icon_lib_btn').click(function (e) { 
                            let remove_active_label 	= jQuery('#mep_global_icon_list_wrapper label').removeClass('selected');
                            let data_key 				= jQuery(this).attr('data-key');
                            jQuery("#mep_global_icon_list_wrapper").attr('data-key', data_key);
                            jQuery('#mep_search_icon').val('');
                            jQuery('.mep_global_icon_list_body label').show();
                            jQuery("#mep_global_icon_list_wrapper").mage_modal({
                                escapeClose: false,
                                clickClose: false,
                                showClose: false
                            });
        
                            // Selected Global Icon Action
                            jQuery('#mep_global_icon_list_wrapper label').click(function (e) {
                                e.stopImmediatePropagation();
                                let selected_label 		= jQuery(this);
                                let selected_val 		= jQuery('input', this).val();
                                let selected_data_key 	= jQuery("#mep_global_icon_list_wrapper").attr('data-key');
                                jQuery('#mep_global_icon_list_wrapper label').removeClass('selected');
                                jQuery('.mep_global_settings_icon_preview[data-key="'+selected_data_key+'"]').empty();
                                jQuery(selected_label).addClass('selected');
                                jQuery('.mep_global_settings_icon[data-key="'+selected_data_key+'"]').val(selected_val);
                                jQuery('.mep_global_settings_icon_preview[data-key="'+selected_data_key+'"]').append('<i class="'+selected_val+'"></i>');
    
                            });				
                        });
                        // End Global Icon Popup
                        
                        // Icon Filter 
                            jQuery('#mep_search_icon').keyup(function (e) { 
                                let value = jQuery(this).val().toLowerCase();
                                jQuery(".mep_global_icon_list_body label[data-id]").show().filter(function() {
                                    jQuery(this).toggle(jQuery(this).attr('data-id').toLowerCase().indexOf(value) > -1)
                                }).hide();
                            });
                        // End Icon Filter
                });	
            </script>
            <div id="mep_global_icon_list_wrapper" class="mage_modal">
            <div class="mep_global_icon_list_header">
                <div class="mep_global_icon_list_header_group">
                    <a href="#mep_global_icon_list_wrapper" rel="mage_modal:close" class="mep_global_icon_list_close_button"><?php esc_html_e('Close','mage-eventpress'); ?></a>
                </div>    
                <div class="mep_global_icon_list_header_group">
                    <input type="text" id="mep_search_icon" placeholder="<?php esc_attr_e('Search Icon...','mage-eventpress'); ?>"> 
                </div>
            </div>
            <hr>
            <div class="mep_global_icon_list_body">		
                <?php 
                foreach ($icon_library_list as $key => $value) {
                    $input_id = str_replace(' ', '', $key);
                    ?>
                    <label for="<?php echo $input_id; ?>" data-id="<?php echo $value; ?>">
                    <input type="radio" name="mep_icon" id="<?php echo $input_id; ?>" value="<?php echo $key; ?>">
                    <i class="<?php echo $key; ?>"></i>
                    </label> 
                    <?php
                }
                ?>
            </div>    
            </div>
            <?php
        }        
    }
    new class_icon_popup();
}