function mpwem_initWpEditor(id) {
    try {
        if (typeof tinymce !== 'undefined') {
            if (tinymce.get(id)) {
                tinymce.get(id).remove();
            }
            tinymce.init({
                selector: '#' + id, toolbar1: 'formatselect | fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | blockquote | link unlink | removeformat | undo redo | code', toolbar2: '', fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt 60pt 72pt', plugins: 'link,lists,textcolor,colorpicker,wordpress,wpeditimage,wplink,wpview', menubar: false, statusbar: true, setup: function (editor) {
                    // Initialize WordPress link dialog when editor is ready
                    editor.on('init', function () {
                        // Wait a bit for WordPress scripts to be ready
                        setTimeout(function () {
                            // Ensure WordPress link dialog is initialized
                            if (typeof wp !== 'undefined' && wp.link && typeof wp.link.init === 'function') {
                                wp.link.init();
                            } else if (typeof wpLink !== 'undefined' && typeof wpLink.init === 'function') {
                                wpLink.init();
                            }
                        }, 100);
                    });
                }
            });
        }
    } catch (error) {
        console.error('Error initializing WordPress editor:', error);
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
            if (checkbox.attr('name') === 'mep_disable_ticket_time') {
                $(".mep-special-datetime").slideUp(200);
            }
        } else {
            nextValue = toggleValues[0];
            if (checkbox.attr('name') === 'mep_disable_ticket_time') {
                $(".mep-special-datetime").slideDown(200);
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
            // Get post-specific storage key for better security and isolation
            const postId = $('body').find('[name="post_ID"]').val();
            const storageKey = postId ? 'mpwem_active_tab_' + postId : 'mpwem_active_tab';
            // Check if there's a saved active tab in localStorage
            let savedTab = null;
            try {
                savedTab = localStorage.getItem(storageKey);
                // Validate saved tab ID format for security
                if (savedTab && !/^#[a-zA-Z_\-0-9]+$/.test(savedTab)) {
                    savedTab = null; // Invalid format, ignore it
                }
            } catch (e) {
                // localStorage not available, continue without persistence
            }
            if (savedTab) {
                const savedTabElement = $(this).find('ul li[data-target-tabs="' + savedTab + '"]');
                if (savedTabElement.length) {
                    savedTabElement.trigger('click');
                } else {
                    // If saved tab doesn't exist, trigger first tab
                    $(this).find('ul li:first-child').trigger('click');
                }
            } else {
                // No saved tab, trigger first tab
                $(this).find('ul li:first-child').trigger('click');
            }
        });
        if ($('[name="mep_org_address"]').val() > 0) {
            $('.mp_event_address').slideUp(250);
        }
    });
    $(document).on('click', '[data-target-tabs]', function () {
        if (!$(this).hasClass('active')) {
            let tabsTarget = $(this).attr('data-target-tabs');
            // Sanitize tab ID to prevent any potential issues
            if (tabsTarget && /^#[a-zA-Z_\-0-9]+$/.test(tabsTarget)) {
                // Save the active tab to localStorage with post-specific key
                const postId = $('body').find('[name="post_ID"]').val();
                const storageKey = postId ? 'mpwem_active_tab_' + postId : 'mpwem_active_tab';
                try {
                    localStorage.setItem(storageKey, tabsTarget);
                } catch (e) {
                    // Silently fail if localStorage is not available or quota exceeded
                    // This ensures the tab switching still works even without persistence
                }
            }
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
    $(document).on('click', 'label.mep_enable_custom_dt_format input', function () {
        if ($(this).is(":checked")) {
            $(".mep_custom_timezone_setting").slideDown(200);
        } else {
            $(".mep_custom_timezone_setting").slideUp(200);
        }
    });
    $(document).on('click', 'label.mp_event_ticket_type_advance_col_switch input', function () {
        if ($(this).is(":checked")) {
            $(".mep_hide_on_load").slideDown(200);
        } else {
            $(".mep_hide_on_load").slideUp(200);
        }
    });
    $(document).ready(function () {
        $(document).find('.mp_event_type_sortable').sortable({
            handle: $(this).find('.mp_event_type_sortable_button')
        });
        $('.remove-row-d').on('click', function () {
            if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
                $(this).parents('tr').remove();
            } else {
                return false;
            }
        });
    });
    /**************************/
    // Clear localStorage when form is saved or updated
    function mpwem_clear_tab_storage() {
        try {
            const postId = $('body').find('[name="post_ID"]').val();
            if (postId) {
                localStorage.removeItem('mpwem_active_tab_' + postId);
            }
            localStorage.removeItem('mpwem_active_tab');
        } catch (e) {
            // localStorage not available, no action needed
        }
    }
    // Track if user is saving/updating to prevent clearing on reload
    let isSaving = false;
    // Clear on save/update
    $(document).on('click', '#publish,#save-post', function (e) {
        isSaving = true;
        // Clear tab storage after successful save (only after form submission completes)
        setTimeout(function () {
            mpwem_clear_tab_storage();
            isSaving = false;
        }, 1500);
    });
    // Clear when navigating away from the page (but NOT on reload)
    $(window).on('beforeunload', function (e) {
        // Check if this is a page reload or actual navigation
        const isReload = (e.currentTarget.performance && e.currentTarget.performance.navigation.type === 1);
        if (!isReload && !isSaving) {
            // Get current page info
            const urlParams = new URLSearchParams(window.location.search);
            const currentAction = urlParams.get('action');
            const currentPost = urlParams.get('post');
            // Only clear if we're leaving the edit page
            if (currentAction === 'edit' && currentPost) {
                mpwem_clear_tab_storage();
            }
        }
    });
    // Additional cleanup for link navigation (when clicking away from edit page)
    $(document).on('click', 'a:not([data-target-tabs]):not([target="_blank"])', function (e) {
        const href = $(this).attr('href');
        if (href && href !== '#' && href !== 'javascript:void(0)') {
            // Clear storage if navigating away from current post edit page
            const isEditPageLink = href.indexOf('post.php') !== -1 && href.indexOf('action=edit') !== -1;
            const urlParams = new URLSearchParams(window.location.search);
            const currentPostId = urlParams.get('post');
            const targetPostMatch = href.match(/[?&]post=(\d+)/);
            const targetPostId = targetPostMatch ? targetPostMatch[1] : null;
            // Clear if going to different page or different post
            if (!isEditPageLink || (currentPostId && targetPostId && currentPostId !== targetPostId)) {
                mpwem_clear_tab_storage();
            }
        }
    });
    /**************************/
    $(document).on('click', '#publish,#save-post', function (e) {
        let exit = 1;
        let parent = $('#mp_event_all_info_in_tab');
        if (parent.length > 0 && parent.find('.data_required').length > 0) {
            parent.find('.data_required').each(function () {
                if ($(this).closest('.mpwem_hidden_content').length === 0) {
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
            url: ajaxurl, type: 'POST', data: {
                action: 'mep_copy_template_to_theme', template_path: templatePath, nonce: typeof mpwem_template_override_nonce !== 'undefined' ? mpwem_template_override_nonce : ''
            }, success: function (response) {
                if (response.success) {
                    templateItem.addClass('overridden');
                    templateItem.find('.mpwem-template-status').html('<span class="mpwem-status-badge mpwem-status-overridden">Overridden</span>');
                    button.addClass('mpwem-hidden');
                    templateItem.find('.mpwem-remove-template, .mpwem-edit-template').removeClass('mpwem-hidden');
                    alert('Template copied successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            }, error: function (xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                alert('An error occurred while copying the template. Check browser console for details.');
            }, complete: function () {
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
            url: ajaxurl, type: 'POST', data: {
                action: 'mep_remove_template_from_theme', template_path: templatePath, nonce: typeof mpwem_template_override_nonce !== 'undefined' ? mpwem_template_override_nonce : ''
            }, success: function (response) {
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
            }, error: function (xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                alert('An error occurred while removing the template. Check browser console for details.');
            }, complete: function () {
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
            type: 'POST', url: mpwem_admin_var.url, data: {
                "action": "mpwem_reset_booking", "post_id": post_id, "nonce": mpwem_admin_var.nonce
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
                alert(data);
                mpwem_loader_remove(target);
            }
        });
    });
    $(document).on('click', 'table.mpwem_ticket_table .mpwem_show_hide_button', function () {
        $(this).toggleClass('_button_warning_xxs _button_danger_xxs');
        mpwem_content_text_change($(this));
        mpwem_content_icon_change($(this));
        let value = $(this).find('input').val();
        let new_value = value === 'yes' ? 'no' : 'yes';
        $(this).find('input').val(new_value);
        let parent = $(this).closest('tr');
        parent.toggleClass('disable_row');
        let target = parent.find('span.ticket_status');
        target.toggleClass('_button_danger_xxs _button_success_xxs');
        mpwem_content_text_change(target);
        let target_info = parent.find('span.ticket_info');
        target_info.toggleClass('_button_warning_xxs _button_danger_xxs');
        mpwem_content_text_change(target_info);
    });
}(jQuery));
//*************date settings********************//
(function ($) {
    "use strict";
    $(document).on('click', '.ttbm_add_new_special_date', function () {
        let parent = $(this).closest('.mpwem_settings_area');
        let target_item = parent.find('>.mpwem_hidden_content').find('.mpwem_hidden_item');
        let item = target_item.html();
        mpwem_load_sortable_datepicker(parent, item);
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
        if (title) {
            jQuery.ajax({
                type: 'POST', url: mpwem_admin_var.url, data: {
                    "action": "mpwem_save_timeline", "key": key, "title": title, "time": time, "content": content, "post_id": post_id, "nonce": mpwem_admin_var.nonce
                }, beforeSend: function () {
                    mpwem_loader_xs(target);
                    mpwem_loader_xs(popup_target);
                }, success: function (data) {
                    target.html(data).promise().done(function () {
                        mpwem_loader_remove();
                    });
                }
            });
        } else {
            alert('Timeline Title is required');
        }
    }
    $(document).on('click', 'div.mpwem_timeline_settings [data-target-popup]', function (e) {
        e.preventDefault();
        let popup_id = $(this).attr('data-active-popup', '').data('target-popup');
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_timeline_settings').find('.timeline_input');
        $('body').addClass('noScroll').find('[data-popup="' + popup_id + '"]').addClass('in').promise().done(function () {
            jQuery.ajax({
                type: 'POST', url: mpwem_admin_var.url, data: {
                    "action": "mpwem_load_timeline", "key": key, "post_id": post_id, "nonce": mpwem_admin_var.nonce
                }, beforeSend: function () {
                    mpwem_loader_xs(target);
                }, success: function (data) {
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
        parent.find('.popup_close').trigger('click');
    });
    $(document).on('click', 'div.mpwem_timeline_settings .mpwem_timeline_remove', function (e) {
        e.preventDefault();
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_timeline_settings').find('.mpwem_timeline_area');
        jQuery.ajax({
            type: 'POST', url: mpwem_admin_var.url, data: {
                "action": "mpwem_remove_timeline", "key": key, "post_id": post_id, "nonce": mpwem_admin_var.nonce
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
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
        if (title) {
            jQuery.ajax({
                type: 'POST', url: mpwem_admin_var.url, data: {
                    "action": "mpwem_save_faq", "key": key, "title": title, "des": des, "content": content, "post_id": post_id, "nonce": mpwem_admin_var.nonce
                }, beforeSend: function () {
                    mpwem_loader_xs(target);
                    mpwem_loader_xs(popup_target);
                }, success: function (data) {
                    target.html(data).promise().done(function () {
                        mpwem_loader_remove();
                    });
                }
            });
        } else {
            alert('FAQ Title is required');
        }
    }
    $(document).on('click', 'div.mpwem_faq_settings [data-target-popup]', function (e) {
        e.preventDefault();
        let popup_id = $(this).attr('data-active-popup', '').data('target-popup');
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_faq_settings').find('.faq_input');
        $('body').addClass('noScroll').find('[data-popup="' + popup_id + '"]').addClass('in').promise().done(function () {
            jQuery.ajax({
                type: 'POST', url: mpwem_admin_var.url, data: {
                    "action": "mpwem_load_faq", "key": key, "post_id": post_id, "nonce": mpwem_admin_var.nonce
                }, beforeSend: function () {
                    mpwem_loader_xs(target);
                }, success: function (data) {
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
        parent.find('.popup_close').trigger('click');
    });
    $(document).on('click', 'div.mpwem_faq_settings .mpwem_faq_remove', function (e) {
        e.preventDefault();
        let key = $(this).data('key');
        let post_id = $('body').find('[name="post_ID"]').val();
        let target = $(this).closest('.mpwem_faq_settings').find('.mpwem_faq_area');
        jQuery.ajax({
            type: 'POST', url: mpwem_admin_var.url, data: {
                "action": "mpwem_remove_faq", "key": key, "post_id": post_id, "nonce": mpwem_admin_var.nonce
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
                target.html(data);
            }
        });
    });
}(jQuery));
//*********************************//
function mpwem_load_sortable_datepicker(parent, item) {
    parent.find('.mpwem_item_insert').first().append(item).promise().done(function () {
        parent.find('.mpwem_sortable_area').sortable({
            handle: jQuery(this).find('.mpwem_sortable_button')
        });
        mpwem_load_date_picker(parent);
    });
    return true;
}
(function ($) {
    "use strict";
    $(document).ready(function () {
        //=========Short able==============//
        $(document).find('.mpwem_sortable_area').sortable({
            handle: $(this).find('.mpwem_sortable_button')
        });
    });
    //=========Remove Setting Item ==============//
    $(document).on('click', '.mpwem_item_remove', function () {
        if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
            $(this).closest('.mpwem_remove_area').slideUp(250, function () {
                $(this).remove();
            });
            return true;
        }
        return false;
    });
    //=========Add Setting Item==============//
    $(document).on('click', '.mpwem_add_item', function () {
        let parent = $(this).closest('.mpwem_settings_area');
        let item = $(this).next($('.mpwem_hidden_content')).find(' .mpwem_hidden_item').html();
        if (!item || item === "undefined" || item === " ") {
            item = parent.find('.mpwem_hidden_content').first().find('.mpwem_hidden_item').html();
        }
        mpwem_load_sortable_datepicker(parent, item);
        parent.find('.mpwem_item_insert').find('.add_mpwem_select2').select2({});
        return true;
    });
}(jQuery));
//=================select icon / image=========================//
(function ($) {
    "use strict";
    $(document).on('click', 'button.mpwem_image_add', function () {
        let $this = $(this);
        let parent = $this.closest('.mpwem_add_icon_image_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            parent.find('input[type="hidden"]').val(attachment_id);
            parent.find('.mpwem_icon_item').slideUp('fast');
            parent.find('img').attr('src', attachment_url);
            parent.find('.mpwem_image_item').slideDown('fast');
            parent.find('.add_icon_image_button_area').slideUp('fast');
        }
        wp.media.editor.open($this);
        return false;
    });
    $(document).on('click', '.mpwem_add_icon_image_area .mpwem_image_remove', function () {
        let parent = $(this).closest('.mpwem_add_icon_image_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('img').attr('src', '');
        parent.find('.mpwem_image_item').slideUp('fast');
        parent.find('.add_icon_image_button_area').slideDown('fast');
    });
    $(document).on('click', '.mpwem_add_icon_image_area button.mpwem_icon_add', function () {
        let target_popup = $('.mpwem_add_icon_popup');
        target_popup.find('.iconItem').click(function () {
            let parent = $('[data-active-popup]').closest('.mpwem_add_icon_image_area');
            let icon_class = $(this).data('icon-class');
            if (icon_class) {
                parent.find('input[type="hidden"]').val(icon_class);
                parent.find('.add_icon_image_button_area').slideUp('fast');
                parent.find('.mpwem_image_item').slideUp('fast');
                parent.find('.mpwem_icon_item').slideDown('fast');
                parent.find('[data-add-icon]').removeAttr('class').addClass(icon_class);
                target_popup.find('.iconItem').removeClass('active');
                target_popup.find('.popup_close').trigger('click');
            }
        });
        target_popup.find('[data-icon-menu]').click(function () {
            if (!$(this).hasClass('active')) {
                //target_popup.find('[name="mpwem_select_icon_name"]').val('');
                let target = $(this);
                let tabsTarget = target.data('icon-menu');
                target_popup.find('[data-icon-menu]').removeClass('active');
                target.addClass('active');
                target_popup.find('[data-icon-list]').each(function () {
                    let targetItem = $(this).data('icon-list');
                    if (tabsTarget === 'all_item' || targetItem === tabsTarget) {
                        $(this).slideDown(250);
                        $(this).find('.iconItem').each(function () {
                            $(this).slideDown('fast');
                        });
                    } else {
                        $(this).slideUp('fast');
                    }
                });
            }
            return false;
        });
        target_popup.find('.popup_close').click(function () {
            target_popup.find('[data-icon-menu="all_item"]').trigger('click');
            target_popup.find('.iconItem').removeClass('active');
        });
    });
    $(document).on('click', '.mpwem_add_icon_image_area .mpwem_icon_remove', function () {
        let parent = $(this).closest('.mpwem_add_icon_image_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('[data-add-icon]').removeAttr('class');
        parent.find('.mpwem_icon_item').slideUp('fast');
        parent.find('.add_icon_image_button_area').slideDown('fast');
    });
    $(document).on('keyup change', '.mpwem_add_icon_popup [name="mpwem_select_icon_name"]', function () {
        let parent = $(this).closest('.mpwem_add_icon_popup');
        let input = $(this).val().toString().toLowerCase();
        parent.find('[data-icon-menu="all_item"]').trigger('click');
        if (input) {
            parent.find('.popupTabItem').each(function () {
                let tabItem = $(this);
                let count = 0;
                let icon_type = $(this).data('icon-title').toString().toLowerCase();
                let active = (icon_type && icon_type.match(new RegExp(input, "i"))) ? 1 : 0;
                if (active > 0) {
                    tabItem.slideDown(250);
                    tabItem.find('.iconItem').each(function () {
                        $(this).slideDown('fast');
                    });
                } else {
                    tabItem.find('.iconItem').each(function () {
                        let icon_class = $(this).data('icon-class').toString().toLowerCase();
                        let icon_name = $(this).data('icon-name').toString().toLowerCase();
                        active = (icon_class && icon_class.match(new RegExp(input, "i"))) ? 1 : active;
                        active = (icon_name && icon_name.match(new RegExp(input, "i"))) ? 1 : active;
                        if (active > 0) {
                            $(this).slideDown('fast');
                            count++;
                        } else {
                            $(this).slideUp('fast');
                        }
                    }).promise().done(function () {
                        if (count > 0) {
                            tabItem.slideDown('fast');
                        } else {
                            tabItem.slideUp('fast');
                        }
                    });
                }
            });
        } else {
            parent.find('.popupTabItem').each(function () {
                $(this).slideDown(250);
                $(this).find('.iconItem').each(function () {
                    $(this).slideDown(250);
                });
            });
        }
    });
}(jQuery));
//=========upload image==============//
(function ($) {
    "use strict";
    $(document).on('click', '.mpwem_add_single_image', function () {
        let parent = $(this);
        parent.find('.mp_single_image_item').remove();
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="mp_single_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times _icon_circle_xs mpwem_remove_single_image"></span>';
            html += '<img src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';
            parent.append(html);
            parent.find('input').val(attachment_id);
            parent.find('button').slideUp('fast');
        }
        wp.media.editor.open($(this));
        return false;
    });
    $(document).on('click', '.mpwem_remove_single_image', function (e) {
        e.stopPropagation();
        let parent = $(this).closest('.mpwem_add_single_image');
        $(this).closest('.mp_single_image_item').remove();
        parent.find('input').val('');
        parent.find('button').slideDown('fast');
    });
    $(document).on('click', '.mpwem_remove_multi_image', function () {
        let parent = $(this).closest('.mp_multi_image_area');
        let current_parent = $(this).closest('.mp_multi_image_item');
        let img_id = current_parent.data('image-id');
        current_parent.remove();
        let all_img_ids = parent.find('.mp_multi_image_value').val();
        all_img_ids = all_img_ids.replace(',' + img_id, '')
        all_img_ids = all_img_ids.replace(img_id + ',', '')
        all_img_ids = all_img_ids.replace(img_id, '')
        parent.find('.mp_multi_image_value').val(all_img_ids);
    });
    $(document).on('click', '.mpwem_add_multi_image', function () {
        let parent = $(this).closest('.mp_multi_image_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="mp_multi_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times circleIcon_xs mpwem_remove_multi_image"></span>';
            html += '<img src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';
            parent.find('.mp_multi_image').append(html);
            let value = parent.find('.mp_multi_image_value').val();
            value = value ? value + ',' + attachment_id : attachment_id;
            parent.find('.mp_multi_image_value').val(value);
        }
        wp.media.editor.open($(this));
        return false;
    });
}(jQuery));
//=======================//
function mpwem_load_post_date(parent) {
    let post_id = parent.find('[name="mpwem_post_id"]').val();
    let target = parent.find('.date_time_area');
    if (post_id > 0) {
        jQuery.ajax({
            type: 'POST', url: mpwem_admin_var.url, data: {
                "action": "mpwem_load_date", "post_id": post_id, "nonce": mpwem_admin_var.nonce
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
                target.html(data);
                mpwem_loader_remove(target);
            }
        });
    }
}
function mpwem_load_past_date_time(parent) {
    let target = parent.find('.mpwem_time_area');
    if (target.length > 0) {
        let post_id = parent.find('[name="mpwem_post_id"]').val();
        let dates = parent.find('[name="mpwem_date_time"]').val();
        if (post_id > 0 && dates) {
            jQuery.ajax({
                type: 'POST', url: mpwem_admin_var.url, data: {
                    "action": "mpwem_load_time", "post_id": post_id, "dates": dates, "nonce": mpwem_admin_var.nonce
                }, beforeSend: function () {
                    mpwem_loader_xs(target);
                }, success: function (data) {
                    target.html(data);
                    mpwem_loader_remove(target);
                }
            });
        }
    }
}
//=========Seat status==============//
(function ($) {
    "use strict";
    $(document).on('click', '.mpwem_reload_seat_status', function () {
        let current = $(this);
        let parent = $(this).closest('.status_action');
        let post_id = current.attr('data-post_id');
        let date = current.attr('data-date');
        let target = parent.find('.seat_status_area');
        if (post_id > 0) {
            jQuery.ajax({
                type: 'POST', url: mpwem_admin_var.url, data: {
                    "action": "mpwem_reload_seat_status", "post_id": post_id, "date": date, "nonce": mpwem_admin_var.nonce
                }, beforeSend: function () {
                    mpwem_loader_xs(target);
                }, success: function (data) {
                    target.html(data);
                    mpwem_loader_remove(target);
                }
            });
        } else {
            alert('Something Wrong!');
        }
    });
}(jQuery));
//=========event list Statistics==============//
(function ($) {
    "use strict";
    $(document).on('change', '.mpwem_popup_attendee_statistic [name="mpwem_date_time"]', function () {
        let parent = $(this).closest('.mpwem_popup_attendee_statistic');
        let target = parent.find('.mpwem_time_area');
        if (target.length > 0) {
            mpwem_load_past_date_time(parent);
        } else {
            load_mpwem_popup_attendee_statistic(parent);
        }
    });
    $(document).on('change', '.mpwem_popup_attendee_statistic [name="mpwem_time"]', function () {
        let parent = $(this).closest('.mpwem_popup_attendee_statistic');
        load_mpwem_popup_attendee_statistic(parent);
    });
    function load_mpwem_popup_attendee_statistic(parent) {
        let post_id = parent.find('[name="mpwem_post_id"]').val();
        if (post_id > 0) {
            let target = parent.find('.mpwem_popup_attendee_statistic_body');
            let dates = parent.find('[name="mpwem_date_time"]').val();
            let time_area = parent.find('.mpwem_time_area');
            if (time_area.length > 0) {
                dates = parent.find('[name="mpwem_time"]').val();
            }
            jQuery.ajax({
                type: 'POST', url: mpwem_admin_var.url, data: {
                    "action": "mpwem_load_popup_attendee_statistics", "post_id": post_id, "dates": dates, "nonce": mpwem_admin_var.nonce
                }, beforeSend: function () {
                    mpwem_loader_xs(target);
                }, success: function (data) {
                    target.html(data);
                    mpwem_loader_remove(target);
                }
            });
        }
    }
    $(document).on('click', '[data-mpwem_popup_attendee_statistic]', function () {
        let post_id = $(this).data('event-id');
        if (post_id) {
            let target_id = $(this).attr('data-active-popup', '').data('mpwem_popup_attendee_statistic');
            let target = $('body').addClass('noScroll').find('[data-popup="' + target_id + '"]');
            target.addClass('in').promise().done(function () {
                jQuery.ajax({
                    type: 'POST', url: mpwem_admin_var.url, data: {
                        "action": "mpwem_popup_attendee_statistic", "post_id": post_id, "nonce": mpwem_admin_var.nonce
                    }, beforeSend: function () {
                        mpwem_loader(target);
                    }, success: function (data) {
                        target.html(data);
                        mpwem_load_date_picker(target);
                        mpwem_loader_remove(target);
                    }, error: function (response) {
                        console.log(response);
                    }
                });
            });
        }
    });
}(jQuery));
jQuery(function ($) {
    $('#empty-cart-btn').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: mepAjax.ajax_url, type: 'POST', dataType: 'json', data: {
                action: 'empty_woocommerce_cart', nonce: mepAjax.nonce
            }, success: function (response) {
                if (response.success) {
                    $('#empty-cart-message').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                } else {
                    $('#empty-cart-message').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            }, error: function () {
                $('#empty-cart-message').html('<div class="notice notice-error"><p>Unauthorized or invalid request.</p></div>');
            }
        });
    });
});
jQuery(function ($) {
    $('[name="event_start_date_normal"]').on('change', function (e) {
        e.preventDefault();
        let start_date=$('[name="event_start_date_normal"]').val();
        let end_date=$('[name="event_end_date_normal"]').val();
        let target=$('td.event_end_date_normal-td');
        $.ajax({
            url: mepAjax.ajax_url, type: 'POST', data: {
                action: 'load_event_end_date_normal',start_date:start_date, nonce: mepAjax.nonce,end_date:end_date
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
                target.html(data);
            }
        });
    });
    $('tr.disable_row .formControl').on('keydown keypress keyup paste drop', function(e){
        e.preventDefault();
    });
});


jQuery(document).ready(function($) {
    
    // Function for Registration Status (Unchecked = Show)
    function checkRegStatus() {
        var isChecked = $('input[name="mep_reg_status"]').is(':checked');
        // Logic: Show if NOT checked, Hide if checked
        if (!isChecked) {
            $('.reg_close_msg_dash').show();
        } else {
            $('.reg_close_msg_dash').hide();
        }
    }

    // Function for Message Text (Checked = Show)
    function checkMsgVisibility() {
        var isChecked = $('input[name="mep_reg_status_show_msg"]').is(':checked');
        if (isChecked) {
            $('.mep_reg_status_show_msg_txt_sec').show();
        } else {
            $('.mep_reg_status_show_msg_txt_sec').hide();
        }
    }

    // Trigger on Change
    $('input[name="mep_reg_status"]').on('change', function() {
        checkRegStatus();
    });

    $('input[name="mep_reg_status_show_msg"]').on('change', function() {
        checkMsgVisibility();
    });

    // Run on Page Load to check current saved values
    checkRegStatus();
    checkMsgVisibility();
});

jQuery(function ($) {
    $(document).on('change', '[name="event_more_start_date_normal[]"]', function (e) {
        e.preventDefault();
        let parent=$(this).closest('tr');
        let start_date=parent.find('[name="event_more_start_date_normal[]"]').val();
        let end_date=parent.find('[name="event_more_end_date_normal[]"]').val();
        let target=parent.find('[name="event_more_end_date_normal[]"]').closest('td');
        $.ajax({
            url: mepAjax.ajax_url, type: 'POST', data: {
                action: 'load_event_more_start_date_normal',start_date:start_date, nonce: mepAjax.nonce,end_date:end_date
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
                target.html(data);
            }
        });
    });
});
jQuery(function ($) {
    $(document).on('change', '[name="event_start_date_everyday"]', function (e) {
        e.preventDefault();
        let start_date=$('[name="event_start_date_everyday"]').val();
        let end_date=$('[name="event_end_date_everyday"]').val();
        let target=$('.mep_load_every_day');
        $.ajax({
            url: mepAjax.ajax_url, type: 'POST', data: {
                action: 'load_event_start_date_everyday',start_date:start_date, nonce: mepAjax.nonce,end_date:end_date
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
                target.html(data);
            }
        });
    });
});
// jQuery(document).ready(function () {
//     jQuery("#event_more_start_date_normal").datepicker({
//         dateFormat: mpwem_date_format,
//         minDate: 0,
//         autoSize: true,
//         changeMonth: true,
//         changeYear: true,
//         onSelect: function (dateString, data) {
//             let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
//             jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
//         }
//     });
//     jQuery("#event_more_end_date_normal").datepicker({
//         dateFormat: mpwem_date_format,
//         minDate: 0,
//         autoSize: true,
//         changeMonth: true,
//         changeYear: true,
//         onSelect: function (dateString, data) {
//             let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
//             jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
//         }
//     });
// });
jQuery(function ($) {
    $('[name="event_start_date"]').on('change', function (e) {
        e.preventDefault();
        let start_date=$('[name="event_start_date"]').val();
        let end_date=$('[name="event_end_date"]').val();
        let target=$('td.event_end_date-td');
        $.ajax({
            url: mepAjax.ajax_url, type: 'POST', data: {
                action: 'load_event_start_date',start_date:start_date, nonce: mepAjax.nonce,end_date:end_date
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
                target.html(data);
            }
        });
    });
});

jQuery(function ($) {
    $(document).on('change', '[name="event_more_start_date[]"]', function (e) {
        e.preventDefault();
        let parent=$(this).closest('tr');
        let start_date=parent.find('[name="event_more_start_date[]"]').val();
        let end_date=parent.find('[name="event_more_end_date[]"]').val();
        let target=parent.find('[name="event_more_end_date[]"]').closest('td');
        $.ajax({
            url: mepAjax.ajax_url, type: 'POST', data: {
                action: 'load_event_more_start_date',start_date:start_date, nonce: mepAjax.nonce,end_date:end_date
            }, beforeSend: function () {
                mpwem_loader_xs(target);
            }, success: function (data) {
                target.html(data);
            }
        });
    });
});
jQuery(document).ready(function($) {
    // Add new FAQ item
    $('#add-faq-item').on('click', function() {
        var template = $('#faq-item-template').html();
        var container = $('#faq-items-container');

        // ইউনিক আইডি তৈরি (একবার জেনারেট করে সব জায়গায় ব্যবহার করতে হবে)
        var uniqueId = 'faq_description_' + Date.now();

        // টেমপ্লেটের প্লেসহোল্ডার রিপ্লেস করা
        // নিশ্চিত করুন আপনার টেমপ্লেটে textarea-র ID এবং Name-এ 'index' শব্দটি আছে
        var newItem = template.replace(/faq_description_new/g, uniqueId);

        container.append(newItem);

        // ১. TinyMCE (Visual Editor) ইনিশিয়ালাইজ করা
        if (typeof tinyMCE !== 'undefined') {
            setTimeout(function() {
                // ডিফল্ট সেটিংস কপি করা (যাতে টুলবার বাটনগুলো ঠিকঠাক আসে)
                var settings = tinyMCEPreInit.mceInit['faq-item-template'] || {}; // আপনার টেমপ্লেট এডিটরের আইডি দিন
                settings.selector = '#' + uniqueId;

                tinyMCE.init(settings);
                tinyMCE.execCommand('mceAddEditor', false, uniqueId);
            }, 200);
        }

        // ২. Quicktags (Text/Code Buttons) ইনিশিয়ালাইজ করা
        if (typeof quicktags !== 'undefined') {
            setTimeout(function() {
                quicktags({id: uniqueId});
                // কুইক ট্যাগ বাটনগুলোকে ভিজিবল করা
                QTags._buttonsInit();
            }, 300);
        }
    });

    // Remove FAQ item
    $(document).on('click', '.remove-faq-item', function() {
        if (confirm('Are you sure you want to remove this FAQ item?')) {
            var item = $(this).closest('.faq-item');

            // Remove tinyMCE editor if exists
            var editorId = item.find('.wp-editor-area').attr('id');
            if (editorId && typeof tinyMCE !== 'undefined') {
                tinyMCE.execCommand('mceRemoveEditor', false, editorId);
            }

            item.remove();
        }
    });

    // Update indices after removal
    function updateFaqIndices() {
        $('.faq-item').each(function(index) {
            $(this).find('.faq-item-header h3').contents().first().replaceWith('FAQ Item ' + (index + 1) + ' ');
        });
    }

});

document.addEventListener('click', function (e) {
    // চেক করা হচ্ছে ক্লিক করা এলিমেন্টটি .edit-faq-item কি না
    // অথবা এর ভেতরে থাকা কোনো আইকন কি না
    const editBtn = e.target.closest('.edit-faq-item');

    if (editBtn) {
        e.preventDefault();

        // ক্লিক করা বাটনের প্যারেন্ট .faq-item খুঁজে বের করা
        const parentItem = editBtn.closest('.faq-item');

        // 'open' ক্লাসটি টগল করা (CSS এর মাধ্যমে স্লাইড হবে)
        if (parentItem) {
            parentItem.classList.toggle('open');
        }

        // (ঐচ্ছিক) একটি ওপেন করলে বাকিগুলো বন্ধ করতে চাইলে নিচের কোডটি আনকমেন্ট করুন
        /*
        document.querySelectorAll('.faq-item').forEach(item => {
            if (item !== parentItem) {
                item.classList.remove('open');
            }
        });
        */
    }
});