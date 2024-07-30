(function ($) {
    "use strict";
    $(document).on('click', '#publish,#save-post', function (e) {
        e.preventDefault();
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
                if (exit > 0) {
                    $(this).closest('form').submit();
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
}(jQuery));