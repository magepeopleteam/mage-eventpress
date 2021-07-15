(function ($) {
    "use strict";
    $(window).load(function () {
        $('.mp_tab_menu').each(function () {
            $(this).find('ul li:first-child').trigger('click');
        });
        if ($('[name="mep_org_address"]').val() > 0) {
            $('.mp_event_address').slideUp(250);
        }
    });
    $(document).on('click', '[data-target-tabs]', function () {
        if (!$(this).hasClass('active')) {
            let tabsTarget = $(this).attr('data-target-tabs');
            let targetParent = $(this).closest('.mp_event_tab_area').find('.mp_tab_details').first();
            targetParent.children('.mp_tab_item:visible').slideUp('fast');
            targetParent.children('.mp_tab_item[data-tab-item="' + tabsTarget + '"]').slideDown(250);
            $(this).siblings('li.active').removeClass('active');
            $(this).addClass('active');
        }
        return false;
    });
    $(document).on('click', 'label.mp_event_virtual_type_des_switch input', function () {
        if ($(this).is(":checked")) {
            $(this).parents('label.mp_event_virtual_type_des_switch').siblings('label.mp_event_virtual_type_des').slideDown(200);
        } else {
            $(this).parents('label.mp_event_virtual_type_des_switch').siblings('label.mp_event_virtual_type_des').val('').slideUp(200);
        }
    });
    $(document).ready(function () {
        $('#add-row-t').on('click', function () {
            var row = $('.empty-row-t.screen-reader-text').clone(true);
            row.removeClass('empty-row-t screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one-t tbody>tr:last');
            $('#mep_ticket_type_empty option[value=inputbox]').attr('selected', 'selected');
            $('.empty-row-t #mep_ticket_type_empty option[value=inputbox]').removeAttr('selected');
            return false;
        });

        $('.remove-row-t').on('click', function () {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                $(this).parents('tr').remove();
                $('#mep_ticket_type_empty option[value=inputbox]').removeAttr('selected');
                $('#mep_ticket_type_empty option[value=dropdown]').removeAttr('selected');
            } else {
                return false;
            }
        });
        $(document).find('.mp_event_type_sortable').sortable({
            handle: $(this).find('.mp_event_type_sortable_button')
        });


        $('#add-row').on('click', function () {
            var row = $('.empty-row.screen-reader-text').clone(true);
            row.removeClass('empty-row screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
            return false;
        });

        $('.remove-row').on('click', function () {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                $(this).parents('tr').remove();
            } else {
                return false;
            }
        });

        $('#add-new-date-row').on('click', function () {
            var row = $('.empty-row-d.screen-reader-text').clone(true);
            row.removeClass('empty-row-d screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one-d tbody>tr:last');
            return false;
        });

        $('.remove-row-d').on('click', function () {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                $(this).parents('tr').remove();
            } else {
                return false;
            }
        });
    });

}(jQuery));