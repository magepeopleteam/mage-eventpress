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
//*********************************//
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
//*************Time Line Settings********************//
(function ($) {
    "use strict";
    $(document).on('click', 'div.mpwem_timeline_settings [data-target-popup]', function (e) {
        e.preventDefault();
        let popup_id = $(this).attr('data-active-popup', '').data('target-popup');
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_timeline_settings').find('.timeline_input');
        $('body').addClass('noScroll').find('[data-popup="' + popup_id + '"]').addClass('in').promise().done(function () {
            jQuery.ajax({
                type: 'POST',
                url: mp_ajax_url,
                data: {
                    "action": "mpwem_load_timeline",
                    "key": key,
                    "post_id": post_id,
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
        let parent=$(this).closest('.mpwem_timeline_popup');
        mpwem_timeline_save(parent);
    });
    $(document).on('click', 'div.mpwem_timeline_popup .mpwem_timeline_save_close', function (e) {
        e.preventDefault();
        let parent=$(this).closest('.mpwem_timeline_popup');
        mpwem_timeline_save(parent);
        parent.find('.popupClose').trigger('click');
    });
    function mpwem_timeline_save(parent){
        let key = parent.find('[name="timeline_item_key"]').val();
        let title = parent.find('[name="mep_timeline_title"]').val();
        let time = parent.find('[name="mep_timeline_time"]').val();
        let content = tinyMCE.get('mep_timeline_content').getContent();
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = parent.closest('.mpwem_timeline_settings').find('.mpwem_timeline_area');
        let popup_target =parent.find('.timeline_input');
        jQuery.ajax({
            type: 'POST',
            url: mp_ajax_url,
            data: {
                "action": "mpwem_save_timeline",
                "key": key,
                "title": title,
                "time": time,
                "content": content,
                "post_id": post_id,
                nonce: mep_ajax.nonce
            },
            beforeSend: function () {
                dLoader_xs(target);
                dLoader_xs(popup_target);
            },
            success: function (data) {
                target.html(data).promise().done(function (){
                    dLoaderRemove();
                });
            }
        });
    }
    $(document).on('click', 'div.mpwem_timeline_settings .mpwem_timeline_remove', function (e) {
        e.preventDefault();
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_timeline_settings').find('.mpwem_timeline_area');
        jQuery.ajax({
            type: 'POST',
            url: mp_ajax_url,
            data: {
                "action": "mpwem_remove_timeline",
                "key": key,
                "post_id": post_id,
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
    $(document).on('click', 'div.mpwem_faq_settings [data-target-popup]', function (e) {
        e.preventDefault();
        let popup_id = $(this).attr('data-active-popup', '').data('target-popup');
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_faq_settings').find('.faq_input');
        $('body').addClass('noScroll').find('[data-popup="' + popup_id + '"]').addClass('in').promise().done(function () {
            jQuery.ajax({
                type: 'POST',
                url: mp_ajax_url,
                data: {
                    "action": "mpwem_load_faq",
                    "key": key,
                    "post_id": post_id,
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
        let parent=$(this).closest('.mpwem_faq_popup');
        mpwem_faq_save(parent);
    });
    $(document).on('click', 'div.mpwem_faq_popup .mpwem_faq_save_close', function (e) {
        e.preventDefault();
        let parent=$(this).closest('.mpwem_faq_popup');
        mpwem_faq_save(parent);
        parent.find('.popupClose').trigger('click');
    });
    function mpwem_faq_save(parent){
        let key = parent.find('[name="faq_item_key"]').val();
        let title = parent.find('[name="mep_faq_title"]').val();
        let des = $('body').find('[name="mep_faq_description"]').val();
        let content = tinyMCE.get('mep_faq_content').getContent();
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = parent.closest('.mpwem_faq_settings').find('.mpwem_faq_area');
        let popup_target =parent.find('.faq_input');
        jQuery.ajax({
            type: 'POST',
            url: mp_ajax_url,
            data: {
                "action": "mpwem_save_faq",
                "key": key,
                "title": title,
                "des": des,
                "content": content,
                "post_id": post_id,
                nonce: mep_ajax.nonce
            },
            beforeSend: function () {
                dLoader_xs(target);
                dLoader_xs(popup_target);
            },
            success: function (data) {
                target.html(data).promise().done(function (){
                    dLoaderRemove();
                });
            }
        });
    }
    $(document).on('click', 'div.mpwem_faq_settings .mpwem_faq_remove', function (e) {
        e.preventDefault();
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_faq_settings').find('.mpwem_faq_area');
        jQuery.ajax({
            type: 'POST',
            url: mp_ajax_url,
            data: {
                "action": "mpwem_remove_faq",
                "key": key,
                "post_id": post_id,
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
