function mpwem_initWpEditor(id) {
    if (typeof tinymce !== 'undefined') {
        if (tinymce.get(id)) {
            tinymce.get(id).remove();
        }
        tinymce.init({selector: '#' + id});
    }
    if (typeof QTags !== 'undefined') {
        QTags({id: id});
    }
}
//*************Un control js********************//
(function ($) {
    "use strict";
    // =====================sidebar modal open close=============
    $(document).on('click', '[data-modal]', function (e) {
        const modalTarget = $(this).data('modal');
        $(`[data-modal-target="${modalTarget}"]`).addClass('open');
    });
    $(document).on('click', '[data-modal-target] .mep-modal-close', function (e) {
        $(this).closest('[data-modal-target]').removeClass('open');
    });
    // ========Initialize visibility based on the current selection ===========
    var initialStatus = $('#mep_rich_text_status').val();
    if (initialStatus === 'enable') {
        $('#mep_rich_text_table').slideDown();
    } else {
        $('#mep_rich_text_table').slideUp();
    }
    $(document).on('change', '#mep_rich_text_status', function () {
        var status = $(this).val();
        if (status === 'enable') {
            $('#mep_rich_text_table').slideDown(); // Show the section
        } else {
            $('#mep_rich_text_table').slideUp(); // Hide the section
        }
    });
    // ================ Template slection ===============
    $(document).on('click', '.mep-template img', function (e) {
        $('[name="mep_event_template"]').val($(this).data('mep-template'));
        $('.mep-template').removeClass('active')
        $(this).parent('.mep-template').addClass('active');
    });
    // ==============toggle switch radio button=================
    $(document).on('click', '.mpev-switch .mpev-slider', function () {
        var checkbox = $(this).prev('input[type="checkbox"]');
        var toggleValues = checkbox.data('toggle-values').split(',');
        var currentValue = checkbox.val();
        var nextValue = toggleValues[0];
        if (currentValue === toggleValues[0]) {
            nextValue = toggleValues[1];
            if (checkbox.attr('name') === 'mep_show_advance_col_status') {
                $(".mep_hide_on_load").slideUp(200);
            }
            if (checkbox.attr('name') === 'mep_disable_ticket_time') {
                $(".mep-special-datetime").slideUp(200);
            }
            if (checkbox.attr('name') === 'mep_show_category') {
                $(".mep_hide_on_load_cat").slideUp(200);
            }
        } else {
            nextValue = toggleValues[0];
            if (checkbox.attr('name') === 'mep_show_advance_col_status') {
                $(".mep_hide_on_load").slideDown(200);
            }
            if (checkbox.attr('name') === 'mep_disable_ticket_time') {
                $(".mep-special-datetime").slideDown(200);
            }
            if (checkbox.attr('name') === 'mep_show_category') {
                $(".mep_hide_on_load_cat").slideDown(200);
            }
        }
        checkbox.val(nextValue);
        var target = checkbox.data('collapse-target');
        var close = checkbox.data('close-target');
        $(target).slideToggle();
        $(close).slideToggle();
    });
    /**************************/
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
            $(".mep_event_tab_location_content").hide(200);
        } else {
            $(this).parents('label.mp_event_virtual_type_des_switch').siblings('label.mp_event_virtual_type_des').val('').slideUp(200);
            $(".mep_event_tab_location_content").show(200);
        }
    });
    $(document).on('click', 'label.mp_event_ticket_type_des_switch input', function () {
        if ($(this).is(":checked")) {
            // $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').slideDown(200);
            $(".mep_ticket_type_setting_sec").slideDown(200);
        } else {
            // $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').val('').slideUp(200);
            $(".mep_ticket_type_setting_sec").slideUp(200);
        }
    });
    $(document).on('click', 'label.mep_enable_custom_dt_format input', function () {
        if ($(this).is(":checked")) {
            // $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').slideDown(200);
            $(".mep_custom_timezone_setting").slideDown(200);
        } else {
            // $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').val('').slideUp(200);
            $(".mep_custom_timezone_setting").slideUp(200);
        }
    });
    $(document).on('click', 'label.mp_event_ticket_type_advance_col_switch input', function () {
        if ($(this).is(":checked")) {
            // $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').slideDown(200);
            $(".mep_hide_on_load").slideDown(200);
        } else {
            // $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').val('').slideUp(200);
            $(".mep_hide_on_load").slideUp(200);
        }
    });
    $(document).ready(function () {
        $('#add-row-t').on('click', function () {
            var row = $('.empty-row-t.screen-reader-text').clone(true);
            row.removeClass('empty-row-t screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one-t tbody>tr:last');
            $('#mep_ticket_type_empty option[value=inputbox]').attr('selected', 'selected');
            $('.empty-row-t #mep_ticket_type_empty option[value=inputbox]').removeAttr('selected');
            //return false;
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
    $(document).on('keyup change', '.mp_ticket_type_table [name="option_name_t[]"]', function () {
        let n = $(this).val();
        $(this).val(n.replace(/[@%'":;&_]/g, ''));
    });
    /**************************/
    /**************************/
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
    ////// templete override js //////
    // Copy template to theme
    $(document).on('click', '.mpwem-copy-template', function (e) {
        e.preventDefault();
        var button = $(this);
        var templatePath = button.data('template');
        var templateItem = button.closest('.mpwem-template-item');
        templateItem.addClass('mpwem-loading');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mep_copy_template_to_theme',
                template_path: templatePath,
                nonce: typeof mpwem_template_override_nonce !== 'undefined' ? mpwem_template_override_nonce : ''
            },
            success: function (response) {
                if (response.success) {
                    templateItem.addClass('overridden');
                    templateItem.find('.mpwem-template-status').html('<span class="mpwem-status-badge mpwem-status-overridden">Overridden</span>');
                    button.addClass('mpwem-hidden');
                    templateItem.find('.mpwem-remove-template, .mpwem-edit-template').removeClass('mpwem-hidden');
                    alert('Template copied successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                alert('An error occurred while copying the template. Check browser console for details.');
            },
            complete: function () {
                templateItem.removeClass('mpwem-loading');
            }
        });
    });
    // Remove template from theme
    $(document).on('click', '.mpwem-remove-template', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to remove this template override? This will restore the default plugin template.')) {
            return;
        }
        var button = $(this);
        var templatePath = button.data('template');
        var templateItem = button.closest('.mpwem-template-item');
        templateItem.addClass('mpwem-loading');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mep_remove_template_from_theme',
                template_path: templatePath,
                nonce: typeof mpwem_template_override_nonce !== 'undefined' ? mpwem_template_override_nonce : ''
            },
            success: function (response) {
                if (response.success) {
                    templateItem.removeClass('overridden');
                    templateItem.find('.mpwem-template-status').html('<span class="mpwem-status-badge mpwem-status-default">Default</span>');
                    button.addClass('mpwem-hidden');
                    templateItem.find('.mpwem-edit-template').addClass('mpwem-hidden');
                    templateItem.find('.mpwem-copy-template').removeClass('mpwem-hidden');
                    alert('Template override removed successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                alert('An error occurred while removing the template. Check browser console for details.');
            },
            complete: function () {
                templateItem.removeClass('mpwem-loading');
            }
        });
    });
}(jQuery));
//*************settings********************//
(function ($) {
    "use strict";
    $(document).on('click', 'button.mpwem_reset_booking', function (e) {
        e.preventDefault();
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this);
        jQuery.ajax({
            type: 'POST',
            url: mpwem_admin_var.url,
            data: {
                "action": "mpwem_reset_booking",
                "post_id": post_id,
                "nonce": mpwem_admin_var.nonce
            },
            beforeSend: function () {
                dLoader_xs(target);
            },
            success: function (data) {
                alert(data);
                dLoaderRemove(target);
            }
        });
    });
}(jQuery));
//*************date settings********************//
(function ($) {
    "use strict";
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
//*************Time Line Settings********************//
(function ($) {
    "use strict";
    function mpwem_timeline_save(parent) {
        let key = parent.find('[name="timeline_item_key"]').val();
        let title = parent.find('[name="mep_timeline_title"]').val();
        let time = parent.find('[name="mep_timeline_time"]').val();
        let content = tinyMCE.get('mep_timeline_content').getContent();
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = parent.closest('.mpwem_timeline_settings').find('.mpwem_timeline_area');
        let popup_target = parent.find('.timeline_input');
        jQuery.ajax({
            type: 'POST',
            url: mpwem_admin_var.url,
            data: {
                "action": "mpwem_save_timeline",
                "key": key,
                "title": title,
                "time": time,
                "content": content,
                "post_id": post_id,
                "nonce": mpwem_admin_var.nonce
            },
            beforeSend: function () {
                dLoader_xs(target);
                dLoader_xs(popup_target);
            },
            success: function (data) {
                target.html(data).promise().done(function () {
                    dLoaderRemove();
                });
            }
        });
    }
    $(document).on('click', 'div.mpwem_timeline_settings [data-target-popup]', function (e) {
        e.preventDefault();
        let popup_id = $(this).attr('data-active-popup', '').data('target-popup');
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_timeline_settings').find('.timeline_input');
        $('body').addClass('noScroll').find('[data-popup="' + popup_id + '"]').addClass('in').promise().done(function () {
            jQuery.ajax({
                type: 'POST',
                url: mpwem_admin_var.url,
                data: {
                    "action": "mpwem_load_timeline",
                    "key": key,
                    "post_id": post_id,
                    "nonce": mpwem_admin_var.nonce
                },
                beforeSend: function () {
                    dLoader_xs(target);
                },
                success: function (data) {
                    target.html(data).promise().done(function () {
                        mpwem_initWpEditor('mep_timeline_content');
                    });
                }
            });
        })
    });
    $(document).on('click', 'div.mpwem_timeline_popup .mpwem_timeline_save', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.mpwem_timeline_popup');
        mpwem_timeline_save(parent);
    });
    $(document).on('click', 'div.mpwem_timeline_popup .mpwem_timeline_save_close', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.mpwem_timeline_popup');
        mpwem_timeline_save(parent);
        parent.find('.popupClose').trigger('click');
    });
    $(document).on('click', 'div.mpwem_timeline_settings .mpwem_timeline_remove', function (e) {
        e.preventDefault();
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_timeline_settings').find('.mpwem_timeline_area');
        jQuery.ajax({
            type: 'POST',
            url: mpwem_admin_var.url,
            data: {
                "action": "mpwem_remove_timeline",
                "key": key,
                "post_id": post_id,
                "nonce": mpwem_admin_var.nonce
            },
            beforeSend: function () {
                dLoader_xs(target);
            },
            success: function (data) {
                target.html(data);
            }
        });
    });
}(jQuery));
//*************Faq Settings********************//
(function ($) {
    "use strict";
    function mpwem_faq_save(parent) {
        let key = parent.find('[name="faq_item_key"]').val();
        let title = parent.find('[name="mep_faq_title"]').val();
        let des = $('body').find('[name="mep_faq_description"]').val();
        let content = tinyMCE.get('mep_faq_content').getContent();
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = parent.closest('.mpwem_faq_settings').find('.mpwem_faq_area');
        let popup_target = parent.find('.faq_input');
        jQuery.ajax({
            type: 'POST',
            url: mpwem_admin_var.url,
            data: {
                "action": "mpwem_save_faq",
                "key": key,
                "title": title,
                "des": des,
                "content": content,
                "post_id": post_id,
                "nonce": mpwem_admin_var.nonce
            },
            beforeSend: function () {
                dLoader_xs(target);
                dLoader_xs(popup_target);
            },
            success: function (data) {
                target.html(data).promise().done(function () {
                    dLoaderRemove();
                });
            }
        });
    }
    $(document).on('click', 'div.mpwem_faq_settings [data-target-popup]', function (e) {
        e.preventDefault();
        let popup_id = $(this).attr('data-active-popup', '').data('target-popup');
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_faq_settings').find('.faq_input');
        $('body').addClass('noScroll').find('[data-popup="' + popup_id + '"]').addClass('in').promise().done(function () {
            jQuery.ajax({
                type: 'POST',
                url: mpwem_admin_var.url,
                data: {
                    "action": "mpwem_load_faq",
                    "key": key,
                    "post_id": post_id,
                    "nonce": mpwem_admin_var.nonce
                },
                beforeSend: function () {
                    dLoader_xs(target);
                },
                success: function (data) {
                    target.html(data).promise().done(function () {
                        mpwem_initWpEditor('mep_faq_content');
                    });
                }
            });
        })
    });
    $(document).on('click', 'div.mpwem_faq_popup .mpwem_faq_save', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.mpwem_faq_popup');
        mpwem_faq_save(parent);
    });
    $(document).on('click', 'div.mpwem_faq_popup .mpwem_faq_save_close', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.mpwem_faq_popup');
        mpwem_faq_save(parent);
        parent.find('.popupClose').trigger('click');
    });
    $(document).on('click', 'div.mpwem_faq_settings .mpwem_faq_remove', function (e) {
        e.preventDefault();
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_faq_settings').find('.mpwem_faq_area');
        jQuery.ajax({
            type: 'POST',
            url: mpwem_admin_var.url,
            data: {
                "action": "mpwem_remove_faq",
                "key": key,
                "post_id": post_id,
                "nonce": mpwem_admin_var.nonce
            },
            beforeSend: function () {
                dLoader_xs(target);
            },
            success: function (data) {
                target.html(data);
            }
        });
    });
}(jQuery));
//*********************************//
