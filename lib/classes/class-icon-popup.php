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
            $icon_library_list = [];
            if ( class_exists( 'MPWEM_Select_Icon_image' ) && method_exists( 'MPWEM_Select_Icon_image', 'all_icon_array' ) ) {
                $icon_groups = MPWEM_Select_Icon_image::all_icon_array();
                if ( is_array( $icon_groups ) ) {
                    foreach ( $icon_groups as $icon_group ) {
                        $icons = isset( $icon_group['icon'] ) && is_array( $icon_group['icon'] ) ? $icon_group['icon'] : [];
                        foreach ( $icons as $icon_class => $icon_label ) {
                            $icon_library_list[ $icon_class ] = $icon_label;
                        }
                    }
                }
            }
            if ( empty( $icon_library_list ) ) {
                $icon_library = new mep_icon_library();
                $icon_library_list = $icon_library->mep_fontawesome_icons();
            }
            ?>
            <script>
                jQuery(document).ready(function(){
                    jQuery(document).on('click', '.mep_global_icon_lib_btn', function (e) {
                        e.preventDefault();
                        let data_key = jQuery(this).attr('data-key');
                        jQuery('#mep_global_icon_list_wrapper label').removeClass('selected');
                        jQuery('#mep_global_icon_list_wrapper').attr('data-key', data_key);
                        jQuery('#mep_search_icon').val('');
                        jQuery('.mep_global_icon_list_body label').show();

                        if (typeof jQuery.fn.mage_modal === 'function') {
                            jQuery('#mep_global_icon_list_wrapper').mage_modal({
                                escapeClose: false,
                                clickClose: false,
                                showClose: false
                            });
                        }
                    });

                    jQuery(document).on('click', '#mep_global_icon_list_wrapper label', function (e) {
                        e.preventDefault();
                        let selected_label = jQuery(this);
                        let selected_val = jQuery('input', this).val();
                        let selected_data_key = jQuery('#mep_global_icon_list_wrapper').attr('data-key');
                        jQuery('#mep_global_icon_list_wrapper label').removeClass('selected');
                        jQuery('.mep_global_settings_icon_preview[data-key="' + selected_data_key + '"]').empty();
                        jQuery(selected_label).addClass('selected');
                        jQuery('.mep_global_settings_icon[data-key="' + selected_data_key + '"]').val(selected_val);
                        jQuery('.mep_global_settings_icon_preview[data-key="' + selected_data_key + '"]').append('<span class="' + selected_val + '"></span>');
                        jQuery.mage_modal.close();
                    });

                    jQuery(document).on('keyup', '#mep_search_icon', function () {
                        let value = jQuery(this).val().toLowerCase();
                        jQuery('.mep_global_icon_list_body label[data-id]').each(function () {
                            let icon_name = (jQuery(this).attr('data-id') || '').toLowerCase();
                            let icon_class = (jQuery('input', this).val() || '').toLowerCase();
                            jQuery(this).toggle(icon_name.indexOf(value) > -1 || icon_class.indexOf(value) > -1);
                        });
                    });
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
                    <label for="<?php echo esc_attr( $input_id ); ?>" data-id="<?php echo esc_attr( $value ); ?>">
                    <input type="radio" name="mep_icon" id="<?php echo $input_id; ?>" value="<?php echo $key; ?>">
                    <span class="<?php echo esc_attr( $key ); ?>"></span>
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
