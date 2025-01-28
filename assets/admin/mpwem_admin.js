(function ($) {
    "use strict";
    $(document).on('click', '#publish,#save-post', function (e) {
        let exit = 1;
        let parent = $('#mp_event_all_info_in_tab');
        if (parent.length > 0 && parent.find('.data_required').length > 0) {
            parent.find('.data_required').each(function () {
                if (!$(this).hasClass('screen-reader-text')) {
                    $(this).find('[data-required]').each(function () {
                        if (!$(this).val()) {
                            let target_id = $(this).closest('.mp_tab_item').attr('data-tab-item');
                            parent.find('.mp_tab_menu').find('[data-target-tabs="' + target_id + '"]').trigger('click');
                            $(this).addClass('mpRequired').focus();
                            exit = 0;
                        }
                    });
                }
            }).promise().done(function () {
                if (exit === 0) {
                    e.preventDefault();
                }
            });
        }
    });
    $(document).on('keyup', '[data-required]', function () {
        if ($(this).val().length > 0) {
            $(this).removeClass('mpRequired')
        } else {
            $(this).addClass('mpRequired');
        }
    });
    //==========Special date==================//
    $(document).on('click', '.ttbm_add_new_special_date', function () {
        let parent = $(this).closest('.mp_settings_area');
        let target_item = parent.find('>.mp_hidden_content').find('.mp_hidden_item');
        let item = target_item.html();
        load_sortable_datepicker(parent, item);
        let unique_id = 'ttbm_hidden_name_' + Math.floor((Math.random() * 9999) + 999);
        target_item.find('[name="mep_special_date_hidden_name[]"]').val(unique_id);
        target_item.find('[name*="mep_special_time_label"]').attr('name', 'mep_special_time_label_' + unique_id + '[]');
        target_item.find('[name*="mep_special_time_value"]').attr('name', 'mep_special_time_value_' + unique_id + '[]');
    });
}(jQuery));