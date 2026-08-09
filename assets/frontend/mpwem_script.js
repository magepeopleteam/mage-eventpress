function mpwem_qty_inperson(parent) {
    let total_qty = 0;
    parent.find('[name="option_qty[]"]').each(function () {
        const $item = jQuery(this).closest('.mep_ticket_item');
        if (!$item.find('.mep-ticket-mode-badge--online').length) {
            total_qty += parseInt(jQuery(this).val()) || 0;
        }
    });
    return total_qty;
}
function mpwem_price_calculation(parent) {
    // alert(123);
    try {
        const total_qty = mpwem_qty(parent);
        mpwem_attendee_management(parent, total_qty);
        const target_summary = parent.find('.mpwem_total');
        let total = mpwem_price(parent);
        if (total_qty > 0) {
            parent.find('.mpwem_form_submit_area button').removeAttr('disabled');
            // For hybrid events, only show extra service when at least one in-person ticket is selected
            const isHybrid = parent.find('.mep-ticket-mode-badge--online, .mep-ticket-mode-badge--inperson').length > 0;
            const inPersonQty = isHybrid ? mpwem_qty_inperson(parent) : total_qty;
            if (inPersonQty > 0) {
                parent.find('.mpwem_ex_service').slideDown('fast');
                total += mpwem_ex_price(parent);
            } else {
                parent.find('.mpwem_ex_service').slideUp('fast');
            }
        } else {
            parent.find('.mpwem_ex_service').slideUp('fast');
            parent.find('.mpwem_form_submit_area button').attr('disabled', 'disabled');
        }
        target_summary.html(mpwem_price_format(total));
    } catch (error) {
        console.error('Error in price calculation:', error);
    }
}
function mpwem_qty(parent) {
    let total_qty = 0;
    if (parent.find('.mpwem_seat_plan_area').length > 0) {
        // Count selected seats
        total_qty = parent.find('.mpwem_seat_available.mage_seat_selected').length;
    } else {
        // Sum up ticket quantities
        parent.find('[name="option_qty[]"]').each(function () {
            const qty = parseInt(jQuery(this).val()) || 0;
            total_qty += qty;
        });
    }
    return total_qty;
}
function mpwem_qty_ex(parent) {
    let total_qty = 0;
    parent.find('[name="event_extra_service_qty[]"]').each(function () {
        const qty = parseInt(jQuery(this).val()) || 0;
        total_qty += qty;
    });
    return total_qty;
}
function mpwem_price(parent) {
    let total = 0;
    if (parent.find('.mpwem_seat_plan_area').length > 0) {
        parent.find('.mpwem_seat_available.mage_seat_selected').each(function () {
            const seatPrice = parseFloat(jQuery(this).attr('data-seat_price')) || 0;
            total += seatPrice;
        });
    } else {
        // Calculate from ticket quantities and prices
        parent.find('[name="option_qty[]"]').each(function () {
            const qty = parseInt(jQuery(this).val()) || 0;
            const price = parseFloat(jQuery(this).attr('data-price')) || 0;
            total += price * qty;
        });
    }
    return total;
}
function mpwem_ex_price(parent) {
    let total = 0;
    parent.find('[name="event_extra_service_qty[]"]').each(function () {
        const ex_qty = parseInt(jQuery(this).val()) || 0;
        const ex_price = parseFloat(jQuery(this).attr('data-price')) || 0;
        total += ex_price * ex_qty;
    });
    return total;
}
function mpwem_attendee_management(parent, total_qty) {
    let form_target = parent.find('.mep_attendee_info');
    let same_attendee = parent.find('[name="mep_same_attendee"]').val();
    
    // Strip required attributes from hidden template inputs to avoid "invalid form control is not focusable" error
    parent.find('.mep_attendee_info_hidden').find('input, select, textarea').each(function () {
        if (jQuery(this).prop('required')) {
            jQuery(this).removeAttr('required').addClass('mep-originally-required');
        }
    });

    if (form_target.length > 0 && total_qty > 0) {
        if (same_attendee === 'yes' || same_attendee === 'must') {
            form_target.slideDown('fast');
        } else {
            let hidden_target = parent.find('.mep_attendee_info_hidden');
            if (parent.find('.mpwem_seat_plan_area').length > 0) {
                let current_parent = parent.find('.mpwem_seat_plan_area');
                let form_length = current_parent.find('.mep_form_item').length;
                form_target = current_parent.find('.mep_attendee_info');
                form_target.slideDown('fast');
                if (form_length !== total_qty) {
                    parent.find('.mpwem_seat_available.mage_seat_selected').each(function () {
                        let seat_name = jQuery(this).attr('data-seat-name');
                        let ticket_name = jQuery(this).attr('data-seat-type');
                        if (form_target.find('[data-seat_name="' + seat_name + '"]').length === 0) {
                            hidden_target.find('.mpwem_ticket_name').html(ticket_name);
                            hidden_target.find('.mep_form_item').attr('data-seat_name', seat_name);
                            hidden_target.find('.mpwem_ticket_count').html(seat_name).promise().done(function () {
                                form_target.append(hidden_target.html());
                                form_target.find('.mep-originally-required').attr('required', 'required');
                            }).promise().done(function () {
                                mpwem_load_date_picker(parent);
                            });
                        }
                    }).promise().done(function () {
                        form_length = form_target.find('.mep_form_item').length;
                        if (form_length !== total_qty) {
                            form_target.find('.mep_form_item').each(function () {
                                let seat_name = jQuery(this).attr('data-seat_name');
                                if (parent.find('.mpwem_seat_available.mage_seat_selected[data-seat-name="' + seat_name + '"]').length === 0) {
                                    jQuery(this).remove();
                                }
                            });
                        }
                    });
                }
            } else {
                parent.find('[name="option_qty[]"]').each(function () {
                    let current_parent = jQuery(this).closest('.mep_ticket_item');
                    let qty = parseInt(jQuery(this).val());
                    if (current_parent.find('[name="ticket_group_qty"]').length > 0) {
                        qty = qty * parseInt(current_parent.find('[name="ticket_group_qty"]').val());
                    }
                    if (current_parent.find('[name="mep_group_qty"]').length > 0) {
                        qty = qty * parseInt(current_parent.find('[name="mep_group_qty"]').val());
                    }
                    let form_length = current_parent.find('.mep_form_item').length;
                    form_target = current_parent.find('.mep_attendee_info');
                    form_target.slideDown('fast');
                    if (form_length !== qty) {
                        if (form_length > qty) {
                            for (let i = form_length; i > qty; i--) {
                                form_target.find('.mep_form_item:last-child').slideUp(250).remove();
                            }
                        } else {
                            for (let i = form_length; i < qty; i++) {
                                let ticket_name = current_parent.find('[name="option_name[]"]').val();
                                hidden_target.find('.mpwem_ticket_name').html(ticket_name);
                                hidden_target.find('.mpwem_ticket_count').html(i + 1).promise().done(function () {
                                    form_target.append(hidden_target.html()).promise().done(function () {
                                        jQuery(this).find('.mep-originally-required').attr('required', 'required');
                                        jQuery(this).find('.mp_form_item').each(function () {
                                            let condition_type = jQuery(this).attr('data-depend');
                                            let current_ticket_name = jQuery(this).attr('data-condition-value');
                                            if (condition_type === 'mep_ticket_type' && current_ticket_name === ticket_name) {
                                                jQuery(this).slideDown('fast').removeClass('dNone');
                                            }
                                        });
                                    });
                                }).promise().done(function () {
                                    mpwem_load_date_picker(parent);
                                });
                            }
                        }
                    }
                });
            }
        }
    } else {
        if (same_attendee === 'yes' || same_attendee === 'must') {
            form_target.slideUp(250);
        } else {
            form_target.html('').slideUp(250);
        }
    }
}
(function ($) {
    "use strict";
    $(document).ready(function () {
        $('body').find('.mpwem_registration_area').each(function () {
            mpwem_price_calculation($(this));
        });
    });
    $(document).on('change', '.mpwem_registration_area [name="mpwem_date_time"]', function () {
        const parent = $(this).closest('.mpwem_registration_area');
        const target = parent.find('.mpwem_time_area');
        if (target.length > 0) {
            const post_id = parent.find('[name="mpwem_post_id"]').val();
            const dates = parent.find('[name="mpwem_date_time"]').val();
            jQuery.ajax({
                type: 'POST',
                url: mpwem_ajax_url,
                data: {
                    action: "get_mpwem_time",
                    post_id: post_id,
                    dates: dates,
                },
                beforeSend: function () {
                    mpwem_loader_xs(target);
                },
                success: function (data) {
                    // Avoid slideDown here: it sets inline display/height on
                    // .mpwem_time_area and breaks display:contents grid + modern select.
                    target
                        .stop(true, true)
                        .removeAttr('style')
                        .removeClass('pRelative')
                        .html(data);

                    mpwem_loader_remove(target);
                    mpwem_init_modern_time_select(target);

                    const date = parent.find('[name="mpwem_time"]').val();
                    get_mpwem_ticket(target, date);
                },
                error: function (xhr, status, error) {
                    console.error('Error loading time slots:', error);
                    target.html('<p class="error">Error loading time slots. Please try again.</p>');
                },
                complete: function () {
                    mpwem_loader_remove(target);
                    target.removeAttr('style').removeClass('pRelative');
                }
            });
        } else {
            get_mpwem_ticket($(this));
        }
    });
    $(document).on('change', '.mpwem_registration_area [name="mpwem_time"]', function () {
        let parent = $(this).closest('.mpwem_registration_area');
        let date = parent.find('[name="mpwem_time"]').val();
        get_mpwem_ticket($(this), date);
    });
    function get_mpwem_ticket(current, date = '') {
        let parent = current.closest('.mpwem_registration_area');
        let post_id = parent.find('[name="mpwem_post_id"]').val();
        let dates = date ? date : parent.find('[name="mpwem_date_time"]').val();
        let target = parent.find('.mpwem_booking_panel');
        jQuery.ajax({
            type: 'POST',
            url: mpwem_ajax_url,
            data: {
                "action": "get_mpwem_ticket",
                "post_id": post_id,
                "dates": dates,
                "backend_order": window.location.href.search("backend_order"),
            },
            beforeSend: function () {
                mpwem_loader_xs(target);
            },
            success: function (data) {
                // alert(dates);
                target.html(data).slideDown('fast').promise().done(function () {
                    mpwem_load_seat_status(parent.closest('.mpwem_wrapper'),dates);
                    mep_change_date_status(parent.closest('.mpwem_wrapper'),dates);
                    mep_change_time_status(parent.closest('.mpwem_wrapper'),dates);
                    mpwem_price_calculation(parent);
                });
            }
        });
    }
    function mpwem_load_seat_status(parent,dates) {
        let target = parent.find('.mpwem_seat_status');
        if (target.length > 0) {
            let post_id = parent.find('[name="mpwem_post_id"]').val();
            let dates = parent.find('[name="mpwem_date_time"]').val();
            jQuery.ajax({
                type: 'POST',
                url: mpwem_script_var.url,
                data: {
                    "action": "mpwem_load_seat_status",
                    "post_id": post_id,
                    "dates": dates,
                    "nonce": mpwem_script_var.nonce
                },
                beforeSend: function () {
                    mpwem_loader_xs(target);
                },
                success: function (data) {
                    target.html(data);
                }
            });
        }
    }
    function mep_change_date_status(parent,dates) {
        let target = parent.find('.mep_date_status');

            let post_id = parent.find('[name="mpwem_post_id"]').val();
            //let dates = parent.find('[name="mpwem_date_time"]').val();
            jQuery.ajax({
                type: 'POST',
                url: mpwem_script_var.url,
                data: {
                    "action": "mep_change_date_status",
                    "post_id": post_id,
                    "dates": dates,
                    "nonce": mpwem_script_var.nonce
                },
                beforeSend: function () {
                    mpwem_loader_xs(target);
                },
                success: function (data) {
                    target.html(data);
                }
            });

    }
    function mep_change_time_status(parent,dates) {
        let target = parent.find('.mep_time_status');

        let post_id = parent.find('[name="mpwem_post_id"]').val();
        //let dates = parent.find('[name="mpwem_date_time"]').val();
        jQuery.ajax({
            type: 'POST',
            url: mpwem_script_var.url,
            data: {
                "action": "mep_change_time_status",
                "post_id": post_id,
                "dates": dates,
                "nonce": mpwem_script_var.nonce
            },
            beforeSend: function () {
                mpwem_loader_xs(target);
            },
            success: function (data) {
                target.html(data);
            }
        });

    }
    $(document).on("click", "div.mpwem_style .decQty, div.mpwem_style .incQty", function () {
        let parent = $(this).closest('.mpwem_registration_area');
        let current = $(this);
        if (!current.hasClass('mpDisabled')) {
            let target = current.closest('.qtyIncDec').find('input');
            let newValue = parseInt(target.val()) || 0;
            let min = parseInt(target.attr('min')) || 0;
            let max = parseInt(target.attr('max')) || Infinity;
            let minQty = parseInt(target.attr('data-min-qty')) || 0;
            if (current.hasClass('incQty')) {
                newValue = newValue + 1;
                if (newValue < min) {
                    newValue = min;
                }
            } else {
                newValue = newValue - 1;
                if (newValue < min) {
                    newValue = 0;
                }
            }
            if (minQty > 0) {
                newValue = Math.max(newValue, min);
            }
            newValue = Math.min(newValue, max);
            target.val(newValue);
            parent.find('.qtyIncDec').each(function () {
                let $this = $(this);
                $this.find('.incQty, .decQty').removeClass('mpDisabled');
                let loop_target = $(this).find('input');
                let loop_value = parseInt(loop_target.val()) || 0;
                //let loop_min = parseInt(loop_target.attr('min')) || 0;
                let loop_max = parseInt(loop_target.attr('max')) || Infinity;
                let loop_minQty = parseInt(loop_target.attr('data-min-qty')) || 0;
                if (loop_value >= loop_max) {
                    $this.find('.incQty').addClass('mpDisabled');
                }
                if (loop_value <= loop_minQty) {
                    $this.find('.decQty').addClass('mpDisabled');
                }
            }).promise().done(function () {
                target.trigger('change').trigger('input');
            });
        }
    });
    $(document).on('change', '.mpwem_registration_area [name="option_qty[]"]', function () {
        let parent = $(this).closest('.mpwem_registration_area');
        let qty = $(this).val();
        let total_qty = mpwem_qty(parent);
        if (parent.find('[name="mepgq_max_qty"]').length > 0) {
            let max_qty_gq = parseInt(parent.find('[name="mepgq_max_qty"]').val());
            if (max_qty_gq > 0 && total_qty > max_qty_gq) {
                qty = qty - total_qty + max_qty_gq;
                $(this).val(qty);
                mpwem_price_calculation(parent);
            } else {
                mpwem_price_calculation(parent);
            }
        } else if (parent.find('[name="mepmm_min_qty"]').length > 0) {
            let max_qty = parseInt(parent.find('[name="mepmm_max_qty"]').val());
            if (max_qty > 0 && total_qty > max_qty) {
                qty = qty - total_qty + max_qty;
                $(this).val(qty);
                mpwem_price_calculation(parent);
            } else {
                mpwem_price_calculation(parent);
            }
        } else {
            mpwem_price_calculation(parent);
        }
    });
    $(document).on("click", ".mpwem_book_now", function (e) {
        e.preventDefault();

        const $wrap = $(this).closest('.mpwem_summery');
        const alreadyInCart = 0;
        if (alreadyInCart === 1 || alreadyInCart === '1') {
            alert('This product is already in your cart.');
            return false;
        }else {
            let parent = $(this).closest('.mpwem_registration_area');
            let total_qty = mpwem_qty(parent);
            if (total_qty > 0) {
                if (parent.find('[name="mepmm_min_qty"]').length > 0) {
                    let min_qty = parseInt(parent.find('[name="mepmm_min_qty"]').val());
                    if (total_qty < min_qty) {
                        alert('must buy minimum number of ticket : ' + min_qty);
                    } else {
                        parent.find('.mpwem_add_to_cart').trigger('click');
                    }
                } else {
                    parent.find('.mpwem_add_to_cart').trigger('click');
                }
            } else {
                alert('Please Select Ticket Type');
                let currentTarget = $(this).closest('.mpwem_registration_area').find('[name="option_qty[]"]');
                currentTarget.addClass('error');
                return false;
            }
        }
    });
    $(document).on('change', '.mpwem_registration_area [name="event_extra_service_qty[]"]', function () {
        let parent = $(this).closest('.mpwem_registration_area');
        if (parent.find('[name="mepgq_max_ex_qty"]').length > 0) {
            let qty = $(this).val();
            let total_qty = mpwem_qty_ex(parent);
            let max_qty_gq = parseInt(parent.find('[name="mepgq_max_ex_qty"]').val());
            if (total_qty > max_qty_gq) {
                qty = qty - total_qty + max_qty_gq;
                $(this).val(qty);
                mpwem_price_calculation(parent);
            } else {
                mpwem_price_calculation(parent);
            }
        } else {
            mpwem_price_calculation(parent);
        }
    });
    /************File Upload*************/
    $(document).on('change', '.mep_form_item .mep_file_item input[type="file"]', function (e) {
        let parent = $(this).closest('.mep_file_item');
        let input = this;
        let url = input.value;
        let ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
        if (input.files && input.files[0] && (ext === "gif" || ext === "png" || ext === "jpg" || ext === "pdf")) {
            if (input.files[0].size <= 1024000) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    if (ext === "pdf") {
                        parent.find('.attach_file_view img').slideUp(250);
                        parent.find('.attach_file_view span').slideUp(250);
                        parent.find('.attach_file_view iframe').attr('src', e.target.result).slideDown(250);
                    } else {
                        parent.find('.attach_file_view iframe').slideUp(250);
                        parent.find('.attach_file_view span').slideUp(250);
                        parent.find('.attach_file_view img').attr('src', e.target.result).slideDown(250);
                    }
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                alert('Maximum size 1MB');
                parent.find('.attach_file_view img').slideUp(250);
                parent.find('.attach_file_view span').slideDown(250);
                parent.find('.attach_file_view iframe').slideUp(250);
                $(this).val('');
            }
        } else {
            parent.find('.attach_file_view img').slideUp(250);
            parent.find('.attach_file_view span').slideDown(250);
            parent.find('.attach_file_view iframe').slideUp(250);
            $(this).val('');
        }
    });
    /************conditional form*************/
    function mep_get_condition_trigger_value($el) {
        if ($el.is('select')) {
            let $opt = $el.find('option:selected');
            return ($opt.attr('data-target-child-id') || $el.val() || '').toString();
        }
        return ($el.val() || '').toString();
    }

    function mep_condition_value_matches(selected, expected) {
        selected = (selected || '').toString().trim();
        expected = (expected || '').toString().trim();
        if (!expected) {
            return false;
        }
        if (!selected) {
            return false;
        }
        if (selected.indexOf(',') !== -1) {
            return selected.split(',').map(function (part) {
                return part.trim();
            }).indexOf(expected) !== -1;
        }
        return selected === expected;
    }

    function mep_apply_field_conditions($trigger) {
        let condition_id = $trigger.attr('data-target-condition-id');
        if (!condition_id) {
            return;
        }
        let selected_value = mep_get_condition_trigger_value($trigger);
        // Scope to the attendee block (siblings), not the parent field wrapper.
        let $scope = $trigger.closest('.mep_attendee_info, .mep_attendee_info_hidden');
        if (!$scope.length) {
            $scope = $trigger.closest('.mpwem_registration_area, .mpwem_style');
        }
        if (!$scope.length) {
            $scope = $trigger.closest('form');
        }
        if (!$scope.length) {
            $scope = jQuery(document);
        }
        $scope.find('[data-condition-id="' + condition_id + '"]').each(function () {
            let $item = jQuery(this);
            let depend = ($item.attr('data-depend') || '').toString();
            if (depend === 'mep_ticket_type') {
                return;
            }
            let condition_value = $item.attr('data-condition-value');
            if (!condition_value) {
                return;
            }
            if (mep_condition_value_matches(selected_value, condition_value)) {
                $item.removeClass('dNone').stop(true, true).slideDown('fast');
            } else {
                $item.addClass('dNone').stop(true, true).slideUp('fast');
            }
        });
    }

    $(document).on('change', '.mep_form_item [data-target-condition-id]', function () {
        mep_apply_field_conditions(jQuery(this));
    });
}(jQuery));
//*****************************Related Event***********************************//
(function ($) {
    "use strict";
    $(document).ready(function () {
        //$('.mpwem_related_area').slideDown('fast').promise().done(function () {
        var $relatedSlider = $('.mpwem_related_area .related_item');
        if (!$relatedSlider.length || $relatedSlider.hasClass('slick-initialized')) {
            $('.mpwem_related_area').removeClass('on_load_off');
            return;
        }
        $relatedSlider.slick({
            dots: false,
            arrows: true,
            prevArrow: '.mpwem_related_area .related_prev',
            nextArrow: '.mpwem_related_area .related_next',
            infinite: $relatedSlider.children('.mpwem_related_card').length > 3,
            centerMode: false,
            autoplay: false,
            autoplaySpeed: 2000,
            centerPadding: '0px',
            slidesToShow: 3,
            slidesToScroll: 1,
            adaptiveHeight: false,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        infinite: $relatedSlider.children('.mpwem_related_card').length > 2,
                        dots: false,
                        centerMode: false
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        infinite: $relatedSlider.children('.mpwem_related_card').length > 1,
                        dots: false,
                        centerMode: false
                    }
                },
            ]
        }).promise().done(function () {
            $('.mpwem_related_area').removeClass('on_load_off');
        });
        //});
    });
}(jQuery));
//*****************************Event list***********************************//
(function ($) {
    "use strict";
    $(document).on('click', 'button.mpwem_get_date_list', function () {
        let $this = $(this);
        let parent = $this.closest('.mpwem_list_date_list');
        let target = parent.find('.date_list_area');
        if (target.find('.date_item').length === 0) {
            let event_id = $this.data('event-id');
            jQuery.ajax({
                type: 'POST',
                url: mpwem_script_var.url,
                data: {
                    "action": "mpwem_get_date_list",
                    "post_id": event_id,
                    "nonce": mpwem_script_var.nonce
                },
                beforeSend: function () {
                    mpwem_loader_xs($this);
                },
                success: function (data) {
                    target.html(data);
                    target.addClass('open_list');
                    mpwem_loader_remove($this);
                }
            });
        }
    });

    /**
     * Single-event Schedule: load remaining dates via AJAX after the first 5.
     */
    $(document).on('click', 'button.mpwem_get_schedule_more, button.mpwem-date-list__more.mpwem_get_schedule_more', function (e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.data('mpwemBusy')) {
            return;
        }

        var eventId = $btn.data('event-id');
        var offset = parseInt($btn.data('offset'), 10) || 5;
        var $list = $btn.prevAll('.mpwem-date-list, .date_list_area.mpwem-date-list').first();
        if (!$list.length) {
            $list = $btn.closest('.evently-event-schedule, .event_date_list_area, .mpwem_style').find('.mpwem-date-list').first();
        }
        var $extra = $list.find('.mpwem-date-list__ajax').first();
        if (!$extra.length) {
            $extra = $('<div class="mpwem-date-list__ajax" hidden></div>').appendTo($list);
        }

        var setButtonState = function (open) {
            $btn.toggleClass('is-open', open);
            $btn.attr('aria-expanded', open ? 'true' : 'false');
            var $text = $btn.find('[data-text]');
            if ($text.length) {
                $text.text(open
                    ? ($btn.attr('data-open-text') || 'Hide Date Lists')
                    : ($btn.attr('data-close-text') || 'View More Dates')
                );
            }
        };

        // Already loaded — just toggle visibility.
        if ($btn.data('mpwemLoaded')) {
            var isHidden = $extra.prop('hidden') === true || $extra.is('[hidden]');
            if (isHidden) {
                $extra.prop('hidden', false).removeAttr('hidden');
                setButtonState(true);
            } else {
                $extra.prop('hidden', true).attr('hidden', 'hidden');
                setButtonState(false);
            }
            return;
        }

        if (!eventId) {
            return;
        }

        $btn.data('mpwemBusy', true);
        jQuery.ajax({
            type: 'POST',
            url: (typeof mpwem_script_var !== 'undefined' && mpwem_script_var.url) ? mpwem_script_var.url : mpwem_ajax_url,
            data: {
                action: 'mpwem_get_schedule_more_dates',
                post_id: eventId,
                offset: offset,
                nonce: (typeof mpwem_script_var !== 'undefined') ? mpwem_script_var.nonce : ''
            },
            beforeSend: function () {
                if (typeof mpwem_loader_xs === 'function') {
                    mpwem_loader_xs($btn);
                }
            },
            success: function (data) {
                if (typeof data === 'object' && data && data.success === false) {
                    return;
                }
                $extra.html(data).prop('hidden', false).removeAttr('hidden');
                $btn.data('mpwemLoaded', true);
                setButtonState(true);
            },
            error: function () {
                console.error('Error loading more schedule dates');
            },
            complete: function () {
                $btn.data('mpwemBusy', false);
                if (typeof mpwem_loader_remove === 'function') {
                    mpwem_loader_remove($btn);
                }
            }
        });
    });

    $(document).on('click', 'button.mep_event_list_grid', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let target = parent.find('.mage_grid_box');
        let target_data = parent.find('.all_filter_item');
let id=target_data.attr('data-unq-id');
let style='grid';
let column=target_data.attr('data-column');
let cat=target_data.attr('data-cat');
let org=target_data.attr('data-org');
let tag=target_data.attr('data-tag');
let city=target_data.attr('data-city');
let country=target_data.attr('data-country');
let status=target_data.attr('data-status');
let year=target_data.attr('data-year');
let sort=target_data.attr('data-sort');
let show=target_data.attr('data-show');
let pagination=target_data.attr('data-pagination');
let pagination_style=target_data.attr('data-pagination-style');
            jQuery.ajax({
                type: 'POST',
                url: mpwem_script_var.url,
                data: {
                    "action": "mep_gat_event_list_all",
                    "id": id,
                    "style": style,
                    "column": column,
                    "cat": cat,
                    "org": org,
                    "tag": tag,
                    "city": city,
                    "country": country,
                    "status": status,
                    "year": year,
                    "sort": sort,
                    "show": show,
                    "pagination": pagination,
                    "pagination_style": pagination_style,
                    "nonce": mpwem_script_var.nonce
                },
                beforeSend: function () {
                    showLoader();
                },
                success: function (data) {
                    hideLoader();
                    target.html(data);
                    if (typeof mepCalendarInit === 'function') {
                        mepCalendarInit();
                    }
                    target.find('[data-bg-image]:visible').each(function () {
                        let target = jQuery(this);
                        if (target.closest('.sliderAllItem').length === 0) {
                            let width = target.outerWidth();
                            let height = target.outerHeight();
                            if (target.css('background-image') === 'none' || width === 0 || height === 0) {
                                let bg_url = target.data('bg-image');
                                if (!bg_url || bg_url.width === 0 || bg_url.width === 'undefined') {
                                    bg_url = mpwem_empty_image_url;
                                }
                                mpwem_resize_bg_image_area(target, bg_url);
                                target.css('background-image', 'url("' + bg_url + '")').promise().done(function () {
                                    mpwem_loader_remove(jQuery(this));
                                });
                            }
                        }

                    });
                    parent.find('.mep_event_list_grid').addClass('active');
                    parent.find('.mep_event_list_list').removeClass('active');
                    parent.find('.mep_event_list_calender').removeClass('active');
                    if(parent.find('.mep_event_list_today').hasClass( 'active' )){
                        parent.find('.mep_event_list_today').trigger('click');
                    }
                    if(parent.find('.mep_event_list_this_month').hasClass( 'active' )){
                        parent.find('.mep_event_list_this_month').trigger('click');
                    }
                }
            });

    });
    $(document).on('click', 'button.mep_event_list_list', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let target = parent.find('.mage_grid_box');
        let target_data = parent.find('.all_filter_item');
let id=target_data.attr('data-unq-id');
let style='list';
let column=target_data.attr('data-column');
let cat=target_data.attr('data-cat');
let org=target_data.attr('data-org');
let tag=target_data.attr('data-tag');
let city=target_data.attr('data-city');
let country=target_data.attr('data-country');
let status=target_data.attr('data-status');
let year=target_data.attr('data-year');
let sort=target_data.attr('data-sort');
let show=target_data.attr('data-show');
let pagination=target_data.attr('data-pagination');
let pagination_style=target_data.attr('data-pagination-style');
            jQuery.ajax({
                type: 'POST',
                url: mpwem_script_var.url,
                data: {
                    "action": "mep_gat_event_list_all",
                    "id": id,
                    "style": style,
                    "column": column,
                    "cat": cat,
                    "org": org,
                    "tag": tag,
                    "city": city,
                    "country": country,
                    "status": status,
                    "year": year,
                    "sort": sort,
                    "show": show,
                    "pagination": pagination,
                    "pagination_style": pagination_style,
                    "nonce": mpwem_script_var.nonce
                },
                beforeSend: function () {
                    showLoader();
                },
                success: function (data) {
                    hideLoader();
                    target.html(data);
                    target.find('[data-bg-image]:visible').each(function () {
                        let target = jQuery(this);
                        if (target.closest('.sliderAllItem').length === 0) {
                            let width = target.outerWidth();
                            let height = target.outerHeight();
                            if (target.css('background-image') === 'none' || width === 0 || height === 0) {
                                let bg_url = target.data('bg-image');
                                if (!bg_url || bg_url.width === 0 || bg_url.width === 'undefined') {
                                    bg_url = mpwem_empty_image_url;
                                }
                                mpwem_resize_bg_image_area(target, bg_url);
                                target.css('background-image', 'url("' + bg_url + '")').promise().done(function () {
                                    mpwem_loader_remove(jQuery(this));
                                });
                            }
                        }

                    });
                    parent.find('.mep_event_list_list').addClass('active');
                    parent.find('.mep_event_list_grid').removeClass('active');
                    parent.find('.mep_event_list_calender').removeClass('active');
                    if(parent.find('.mep_event_list_this_month').hasClass( 'active' )){
                        parent.find('.mep_event_list_this_month').trigger('click');
                    }
                }
            });

    });
    $(document).on('click', 'button.mep_event_list_calender', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let target = parent.find('.mage_grid_box');
        let target_data = parent.find('.all_filter_item');
        let id=target_data.attr('data-unq-id');
        let cat=target_data.attr('data-cat');
        let org=target_data.attr('data-org');
        let tag=target_data.attr('data-tag');
        let city=target_data.attr('data-city');
        let country=target_data.attr('data-country');
        let status=target_data.attr('data-status');
        let year=target_data.attr('data-year');
            jQuery.ajax({
                type: 'POST',
                url: mpwem_script_var.url,
                data: {
                    "action": "mep_gat_event_calender",
                    "nonce": mpwem_script_var.nonce,
                    "cat": cat,
                    "org": org,
                    "tag": tag,
                    "city": city,
                    "country": country,
                    "status": status,
                    "year": year
                },
                beforeSend: function () {
                    showLoader();
                },
                success: function (data) {
                    hideLoader();
                    target.html(data);
                    target.find('[data-bg-image]:visible').each(function () {
                        let target = jQuery(this);
                        if (target.closest('.sliderAllItem').length === 0) {
                            let width = target.outerWidth();
                            let height = target.outerHeight();
                            if (target.css('background-image') === 'none' || width === 0 || height === 0) {
                                let bg_url = target.data('bg-image');
                                if (!bg_url || bg_url.width === 0 || bg_url.width === 'undefined') {
                                    bg_url = mpwem_empty_image_url;
                                }
                                mpwem_resize_bg_image_area(target, bg_url);
                                target.css('background-image', 'url("' + bg_url + '")').promise().done(function () {
                                    mpwem_loader_remove(jQuery(this));
                                });
                            }
                        }

                    });
                    parent.find('.mep_event_list_list').removeClass('active');
                    parent.find('.mep_event_list_grid').removeClass('active');
                    parent.find('.mep_event_list_calender').addClass('active');
                }
            });

    });

    $(document).on('click', 'button.mep_event_list_all', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let target = parent.find('.mage_grid_box');
        let target_data = parent.find('.all_filter_item');
        var items = jQuery('.mep-event-list-loop');
        items.each(function () {jQuery(this).show();});
        parent.find('.pagination_area').slideUp('fast');
        parent.find('.mep_event_list_all').addClass('active');
        parent.find('.mep_event_list_today').removeClass('active');
        parent.find('.mep_event_list_this_week').removeClass('active');
        parent.find('.mep_event_list_this_month').removeClass('active');

    });

    $(document).on('click', 'button.mep_event_list_today', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let target = parent.find('.mage_grid_box');
        let target_data = parent.find('.all_filter_item');

        var items = jQuery('.mep-event-list-loop');
        let exit=0;
        items.each(function () {
            let today=$this.attr('data-today');
            var date = jQuery(this).data('date');
            let date1 = new Date(date);
            let date2 = new Date(date);
            if (date===today) {
                jQuery(this).show();
                exit=1;
            } else {
                jQuery(this).hide();
            }
        });
        if(exit===0){
            parent.find('.no_event_found').show();
        }else{
            parent.find('.no_event_found').hide();
        }
        parent.find('.pagination_area').slideUp('fast');
        parent.find('.mep_event_list_all').removeClass('active');
        parent.find('.mep_event_list_this_week').removeClass('active');
        parent.find('.mep_event_list_this_month').removeClass('active');
        parent.find('.mep_event_list_today').addClass('active');
    });
    $(document).on('click', 'button.mep_event_list_this_month', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');

        var items = jQuery('.mep-event-list-loop');
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth();
        let firstDay = new Date(year, month, 1);
        let lastDay = new Date(year, month + 1, 0);
        //alert(lastDay);
        let exit=0;
        items.each(function () {
            var date = jQuery(this).data('date');
            let date1 = new Date(date);
            if (date1 >= firstDay && date1 <= lastDay) {
                jQuery(this).show();
                exit=1;
            } else {
                jQuery(this).hide();
            }
        });
        if(exit===0){
            parent.find('.no_event_found').show();
        }else{
            parent.find('.no_event_found').hide();
        }
        parent.find('.pagination_area').slideUp('fast');
        parent.find('.mep_event_list_all').removeClass('active');
        parent.find('.mep_event_list_this_week').removeClass('active');
        parent.find('.mep_event_list_today').removeClass('active');
        parent.find('.mep_event_list_this_month').addClass('active');
    });
    $(document).on('click', 'button.mep_event_list_this_week', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let target = parent.find('.mage_grid_box');
        let target_data = parent.find('.all_filter_item');

        var items = jQuery('.mep-event-list-loop');
        let exit=0;
        items.each(function () {
            let week=$this.attr('data-week');
            let today=parent.find('.mep_event_list_today').attr('data-today');
            var date = jQuery(this).data('date');
            let date1 = new Date(date);
            let date2 = new Date(today);
            let date3 = new Date(week);
            if (date1>=date2 && date1<=date3) {
                jQuery(this).show();
                exit=1;
            } else {
                jQuery(this).hide();
            }
        });
        if(exit===0){
            parent.find('.no_event_found').show();
        }else{
            parent.find('.no_event_found').hide();
        }
        parent.find('.pagination_area').slideUp('fast');
        parent.find('.mep_event_list_all').removeClass('active');
        parent.find('.mep_event_list_today').removeClass('active');
        parent.find('.mep_event_list_this_month').removeClass('active');
        parent.find('.mep_event_list_this_week').addClass('active');
    });
    $(document).on('change', 'input[name="filter_with_start_date"]', function (e) {
        e.preventDefault();
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let target = parent.find('.mage_grid_box');
        let target_data = parent.find('.all_filter_item');
        let start_date=$this.val();
        //alert(start_date);
        let end_date=parent.find('input[name="filter_with_end_date"]').val();
        let exit=0;
        if(start_date && end_date) {
            var items = jQuery('.mep-event-list-loop');
            items.each(function () {
                let week = $this.attr('data-week');
                let today = parent.find('.mep_event_list_today').attr('data-today');
                var date = jQuery(this).data('date');
                let date1 = new Date(date);
                let date2 = new Date(start_date);
                let date3 = new Date(end_date);
                if (date1 >= date2 && date1 <= date3) {
                    jQuery(this).show();
                    exit=1;
                } else {
                    jQuery(this).hide();
                }
            });
            if(exit===0){
                parent.find('.no_event_found').show();
            }else{
                parent.find('.no_event_found').hide();
            }
            parent.find('.pagination_area').slideUp('fast');
            parent.find('.mep_event_list_all').removeClass('active');
            parent.find('.mep_event_list_today').removeClass('active');
            parent.find('.mep_event_list_this_month').removeClass('active');
            parent.find('.mep_event_list_this_week').removeClass('active');
        }
    });
    $(document).on('change', 'input[name="filter_with_end_date"]', function (e) {
        e.preventDefault();
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let target = parent.find('.mage_grid_box');
        let target_data = parent.find('.all_filter_item');
        let start_date=parent.find('input[name="filter_with_start_date"]').val();
        let end_date=$this.val();
       // alert(end_date);
        let exit=0;
        if(start_date && end_date) {
            var items = jQuery('.mep-event-list-loop');
            items.each(function () {
                let week = $this.attr('data-week');
                let today = parent.find('.mep_event_list_today').attr('data-today');
                var date = jQuery(this).data('date');
                let date1 = new Date(date);
                let date2 = new Date(start_date);
                let date3 = new Date(end_date);
                if (date1 >= date2 && date1 <= date3) {
                    jQuery(this).show();
                    exit=1;
                } else {
                    jQuery(this).hide();
                }
            });
            if(exit===0){
                parent.find('.no_event_found').show();
            }else{
                parent.find('.no_event_found').hide();
            }
            parent.find('.pagination_area').slideUp('fast');
            parent.find('.mep_event_list_all').removeClass('active');
            parent.find('.mep_event_list_today').removeClass('active');
            parent.find('.mep_event_list_this_month').removeClass('active');
            parent.find('.mep_event_list_this_week').removeClass('active');
        }
    });
    // Advanced Filter Panel Toggle
    $(document).on('click', 'button.mep_event_list_filter_toggle', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let panel = parent.find('.mep_event_filter_panel');
        panel.slideToggle('fast');
        $this.toggleClass('active');
        // Initialize datepickers with event dates on first open
        if ( ! panel.data('datepicker-init') ) {
            let datesAttr = panel.attr('data-event-dates');
            let availableDates = [];
            try {
                availableDates = datesAttr ? JSON.parse(datesAttr) : [];
            } catch (e) {
                availableDates = [];
            }
            let $startDate = panel.find('input[name="filter_with_start_date"]');
            let $endDate   = panel.find('input[name="filter_with_end_date"]');
            function initDatepicker($el) {
                $el.datepicker('destroy');
                $el.datepicker({
                    dateFormat: 'mm/dd/yy',
                    beforeShowDay: function (date) {
                        let m = date.getMonth() + 1;
                        let d = date.getDate();
                        let y = date.getFullYear();
                        let dateStr = ('0' + m).slice(-2) + '/' + ('0' + d).slice(-2) + '/' + y;
                        if ( availableDates.indexOf(dateStr) !== -1 ) {
                            return [true, '', 'Available'];
                        }
                        return [false, '', 'Unavailable'];
                    },
                    onSelect: function (selectedDate) {
                        if ( $(this).attr('name') === 'filter_with_start_date' ) {
                            $endDate.datepicker('option', 'minDate', selectedDate);
                            $endDate.trigger('change');
                        } else if ( $(this).attr('name') === 'filter_with_end_date' ) {
                            $startDate.datepicker('option', 'maxDate', selectedDate);
                            $startDate.trigger('change');
                        }
                        //applyAllFilters();
                    }
                });
            }
            initDatepicker($startDate);
            initDatepicker($endDate);
            panel.data('datepicker-init', true);
        }
    });

    // Clear All Filters
    $(document).on('click', 'button.mep_event_filter_clear', function () {
        let parent = $(this).closest('.mep_event_filter_panel');
        parent.find('input[name="filter_with_title"]').val('');
        parent.find('input[name="filter_with_start_date"]').val('');
        parent.find('input[name="filter_with_end_date"]').val('');
        parent.find('select[name="filter_with_category"]').val('');
        parent.find('select[name="filter_with_organizer"]').val('');
        parent.find('select[name="filter_with_city"]').val('');
        parent.find('select[name="filter_with_state"]').val('');
        // Also reset legacy single date filter if it exists
        parent.find('input[name="filter_with_date"]').val('');
        // Show all items
        jQuery('.mep-event-list-loop').each(function () {
            jQuery(this).show();
        });
        parent.find('.no_event_found').hide();
        // Update count display
        var totalItems = jQuery('.mep-event-list-loop').length;
        jQuery('.qty_count').text(totalItems);
    });
    $(document).on('click', 'button.mep_event_filter_close', function () {
        let $this = $(this);
        let parent = $this.closest('.list_with_filter_section');
        let panel = parent.find('.mep_event_filter_panel');
        panel.slideToggle('fast');
        $this.toggleClass('active');
        // Initialize datepickers with event dates on first open
        if ( ! panel.data('datepicker-init') ) {
            let datesAttr = panel.attr('data-event-dates');
            let availableDates = [];
            try {
                availableDates = datesAttr ? JSON.parse(datesAttr) : [];
            } catch (e) {
                availableDates = [];
            }
            let $startDate = panel.find('input[name="filter_with_start_date"]');
            let $endDate   = panel.find('input[name="filter_with_end_date"]');
            function initDatepicker($el) {
                $el.datepicker('destroy');
                $el.datepicker({
                    dateFormat: 'mm/dd/yy',
                    beforeShowDay: function (date) {
                        let m = date.getMonth() + 1;
                        let d = date.getDate();
                        let y = date.getFullYear();
                        let dateStr = ('0' + m).slice(-2) + '/' + ('0' + d).slice(-2) + '/' + y;
                        if ( availableDates.indexOf(dateStr) !== -1 ) {
                            return [true, '', 'Available'];
                        }
                        return [false, '', 'Unavailable'];
                    },
                    onSelect: function (selectedDate) {
                        if ( $(this).attr('name') === 'filter_with_start_date' ) {
                            $endDate.datepicker('option', 'minDate', selectedDate);
                            $endDate.trigger('change');
                        } else if ( $(this).attr('name') === 'filter_with_end_date' ) {
                            $startDate.datepicker('option', 'maxDate', selectedDate);
                            $startDate.trigger('change');
                        }
                        //applyAllFilters();
                    }
                });
            }
            initDatepicker($startDate);
            initDatepicker($endDate);
            panel.data('datepicker-init', true);
        }
    });
}(jQuery));
(function ($) {
    "use strict";
    //*****************************Faq***********************************//
    $(document).on('click', '.mep-event-faq-set > a', function () {
        let current = $(this);
        if (current.hasClass("active")) {
            current.removeClass("active");
            current.siblings(".mep-event-faq-content").slideUp(200);
            $(".mep-event-faq-set > a i").removeClass("fa-minus").addClass("fa-plus");
        } else {
            $(".mep-event-faq-set > a i").removeClass("fa-minus").addClass("fa-plus");
            current.find("i").removeClass("fa-plus").addClass("fa-minus");
            $(".mep-event-faq-set > a").removeClass("active");
            current.addClass("active");
            $(".mep-event-faq-content").slideUp(200);
            current.siblings(".mep-event-faq-content").slideDown(200);
        }
    });
}(jQuery));
(function ($) {
    $(document).on('click', 'button.mep_view_vr_btn', function () {
        $(this).closest('tr').next('tr.mep_virtual_event_info_sec').slideToggle('fast');
    });
    $(document).on('click', '.faq_items [data-collapse-target]', function () {
        var $trigger = $(this);
        var targetId = $trigger.data('collapse-target');
        var $panel = $('[data-collapse="' + targetId + '"]');
        window.setTimeout(function () {
            $trigger.attr('aria-expanded', $panel.hasClass('mActive') ? 'true' : 'false');
        }, 260);
    });
}(jQuery));
/******************** Remove below function after 2025**********************/
(function ($) {
    "use strict";
    $(document).on('click', '.mep-event-list-loop .mp_event_visible_event_time', function (e) {
        e.preventDefault();
        let target = $(this);
        $('.mep-event-list-loop .mp_event_visible_event_time').each(function () {
            let current = $(this).siblings('ul.mp_event_more_date_list');
            if (current.is(':visible')) {
                let active_text = $(this).data('active-text');
                $(this).html(active_text);
                current.slideUp(200);
            }
        }).promise().done(function () {
            let current_list = target.siblings('ul.mp_event_more_date_list');
            if (current_list.length > 0) {
                if (current_list.is(':visible')) {
                    current_list.slideUp(200);
                    target.html(target.data('active-text'));
                } else {
                    current_list.slideDown(200);
                    target.html(target.data('hide-text'));
                }
            } else {
                let event_id = target.data('event-id');
                $.ajax({
                    type: 'POST',
                    url: mpwem_ajax_url,
                    data: {"action": "mep_event_list_date_schedule", "event_id": event_id},
                    beforeSend: function () {
                        target.html('<span class="fas fa-spinner fa-pulse"></span>');
                    },
                    success: function (data) {
                        $(data).insertAfter(target);
                        target.html(target.data('hide-text'));
                    }
                });
            }
        });
    });
}(jQuery));


function showLoader() {
    const loader = document.getElementById('loader-overlay');
    loader.classList.add('active');
}

function hideLoader() {
    const loader = document.getElementById('loader-overlay');
    loader.classList.remove('active');
}
// DatePicker Function
jQuery(document).ready(function ($) {

    if (typeof mpwemDateData === 'undefined') {
        return;
    }

    let selector       = mpwemDateData.selector;
    let availableDates = mpwemDateData.availableDates;
    let minDateData    = mpwemDateData.minDate;
    let maxDateData    = mpwemDateData.maxDate;

    $(selector).datepicker({

        dateFormat: mpwem_date_format,

        minDate: new window.Date(
            minDateData.year,
            minDateData.month,
            minDateData.day
        ),

        maxDate: new window.Date(
            maxDateData.year,
            maxDateData.month,
            maxDateData.day
        ),

        autoSize: true,
        changeMonth: true,
        changeYear: true,
        showButtonPanel: false,

        beforeShow: function (input, inst) {
            if (inst && inst.dpDiv) {
                inst.dpDiv
                    .addClass('mpwem-datepicker-modern')
                    .attr('data-mpwem-picker', '1');
            }
        },

        onClose: function (dateText, inst) {
            if (inst && inst.dpDiv) {
                inst.dpDiv
                    .removeClass('mpwem-datepicker-modern')
                    .removeAttr('data-mpwem-picker');
            }
        },

        beforeShowDay: function (date) {

            let d = date.getDate();
            let m = date.getMonth() + 1;
            let y = date.getFullYear();

            let dmy = d + "-" + m + "-" + y;

            if ($.inArray(dmy, availableDates) !== -1) {
                return [true, "mpwem-day-available", "Available"];
            }

            return [false, "mpwem-day-unavailable", "Unavailable"];
        },
        onSelect: function (dateText, data) {

            let date = data.selectedYear + '-' +
                ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' +
                ('0' + parseInt(data.selectedDay)).slice(-2);

            $(this)
                .closest('label')
                .find('input[type="hidden"]')
                .val(date)
                .trigger('change');
        }

    });

});


document.querySelectorAll('li').forEach(function(li) {
    // চেক করুন এই li তে price আছে কিনা
    if (li.querySelector('.woocommerce-Price-amount')) {
        let strong = li.querySelector('.wc-item-meta-label');

        if (strong) {
            // strong element এর ভিতরের সব child nodes চেক করুন
            strong.childNodes.forEach(function(node) {
                // চেক করুন এটি text node কিনা
                if (node.nodeType === Node.TEXT_NODE) {
                    // সব text node থেকে : এবং - রিমুভ করে স্পেস দিন
                    node.textContent = node.textContent.replace(/[:-]/g, '  ');

                    // অতিরিক্ত স্পেস কমাতে চাইলে (optional)
                    //node.textContent = node.textContent.replace(/\s+/g, ' ').trim();
                }
            });
        }
    }
});


jQuery(function ($) {
    $('.mpwem_book_now').on('click', function (e) {
        e.preventDefault();

        const $wrap = $(this).closest('.mpwem_summery');
        const alreadyInCart = $wrap.data('in-cart');

        if (alreadyInCart === 1 || alreadyInCart === '1') {
            alert('This product is already in your cart.');
            return;
        }

        $wrap.find('.mpwem_add_to_cart').trigger('click');
    });
});

(function ($) {
    "use strict";
    $(document).on('submit', '#mpwem_registration', function(e) {
        if ($(this).find('.mep-rsvp-submit-btn').length === 0) {
            return;
        }
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('.mep-rsvp-submit-btn');
        const $msg = $form.find('.mep-rsvp-message');

        $btn.prop('disabled', true).find('span').text('Submitting...');
        $msg.hide().removeClass('success error');

        $.ajax({
            url: mpwem_script_var ? mpwem_script_var.url : mpwem_ajax_url,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).addClass('success').show();
                    $form.find('input[type="text"], input[type="email"]').val('');
                    $form.find('input[type="number"]').val(1);
                } else {
                    const errorMsg = response.data && response.data.message ? response.data.message : 'An error occurred. Please try again.';
                    $msg.text(errorMsg).addClass('error').show();
                }
            },
            error: function() {
                $msg.text('Connection error. Please try again.').addClass('error').show();
            },
            complete: function() {
                $btn.prop('disabled', false).find('span').text('Submit RSVP');
            }
        });
    });
}(jQuery));

/* ============================================================
 * Native Checkout — runs when WooCommerce is not active.
 * Intercepts mpwem_book_now clicks and shows the billing modal.
 * ============================================================ */
(function ($) {
    'use strict';

    // sessionStorage key prefix used to persist a booking intent across a login redirect.
    var PENDING_BOOKING_PREFIX = 'mep_pending_booking_';

    // Helper: resolve the selected occurrence's full date AND time for native checkout.
    // The hidden mep_event_start_date field can collapse to a date-only value (saved as
    // "Y-m-d 00:00") for recurring events that have several times per day, which loses the
    // chosen time slot. The date/time selectors always carry the full datetime, so prefer
    // them: mpwem_time (date + time-slot) > mpwem_date_time (occurrence) > mep_event_start_date.
    // This is native-only and deliberately does not touch the shared $user_date logic used
    // by the WooCommerce flow.
    function mepResolveEventDate(parent) {
        var candidates = [
            parent.find('[name="mpwem_time"]').val(),
            parent.find('[name="mpwem_date_time"]').val(),
            parent.find('[name="mep_event_start_date[]"]').first().val()
        ];
        // Prefer the first candidate that includes a time component.
        for (var i = 0; i < candidates.length; i++) {
            var v = $.trim(candidates[i] || '');
            if (v && v.indexOf(':') !== -1) { return v; }
        }
        // No time component anywhere (genuinely date-only event) — use the first value present.
        for (var j = 0; j < candidates.length; j++) {
            var v2 = $.trim(candidates[j] || '');
            if (v2) { return v2; }
        }
        return '';
    }

    // Helper: HTML-escape a string for safe insertion into the summary markup.
    function mepNativeEsc(str) {
        return $('<span>').text(str == null ? '' : String(str)).html();
    }

    // Helper: present the resolved event datetime, dropping a meaningless midnight time
    // (e.g. "2026-07-22 00:00" → "2026-07-22") so only real times are shown.
    function mepNativeFormatEventDate(raw) {
        raw = $.trim(raw || '');
        if (!raw) { return ''; }
        return raw.replace(/\s+00:00(:00)?$/, '');
    }

    // Helper: collect ticket data from the registration form
    function mepCollectTickets(parent) {
        var tickets = [];
        parent.find('[name="option_qty[]"]').each(function () {
            var qty = parseInt($(this).val()) || 0;
            if (qty <= 0) return;
            var price = parseFloat($(this).attr('data-price')) || 0;
            var $item = $(this).closest('.mep_ticket_item, .mpwem_ticket_row');
            var name  = $item.find('[name="option_name[]"]').val()
                     || $item.find('[name="ticket_type[]"]').val()
                     || 'Ticket';
            tickets.push({ ticket_name: name, ticket_qty: qty, ticket_price: price });
        });
        return tickets;
    }

    // Helper: collect form-builder attendee fields (rendered as name="field[]") into a plain object
    function mepCollectAttendeeFields(parent) {
        var fields = {};
        parent.find('.mep_attendee_info [name$="[]"], .mep_attendee_info_hidden [name$="[]"]').each(function () {
            var $field = $(this);
            if ($field.attr('type') === 'file') {
                return;
            }
            var name = ($field.attr('name') || '').replace(/\[\]$/, '');
            if (name) {
                fields[name] = $field.val();
            }
        });
        return fields;
    }

    // Helper: restore previously-selected ticket quantities (used after a login redirect)
    function mepRestoreTickets(parent, tickets) {
        if (!Array.isArray(tickets)) {
            return;
        }
        tickets.forEach(function (t) {
            parent.find('[name="option_qty[]"]').each(function () {
                var $qty  = $(this);
                var $item = $qty.closest('.mep_ticket_item, .mpwem_ticket_row');
                var name  = $item.find('[name="option_name[]"]').val()
                         || $item.find('[name="ticket_type[]"]').val();
                if (name === t.ticket_name) {
                    $qty.val(t.ticket_qty).trigger('change');
                }
            });
        });
    }

    // Helper: restore previously-entered attendee form fields (used after a login redirect)
    function mepRestoreAttendeeFields(parent, fields) {
        if (!fields) {
            return;
        }
        Object.keys(fields).forEach(function (name) {
            parent.find('.mep_attendee_info [name="' + name + '[]"], .mep_attendee_info_hidden [name="' + name + '[]"]')
                .val(fields[name]);
        });
    }

    // Helper: format a number as currency using the JS constants set by php
    function mepNativeFormatPrice(amount) {
        if (typeof mpwem_price_format === 'function') {
            return mpwem_price_format(amount);
        }
        var symbol   = (typeof mpwem_currency_symbol !== 'undefined')   ? mpwem_currency_symbol   : '$';
        var position = (typeof mpwem_currency_position !== 'undefined') ? mpwem_currency_position : 'left';
        var decimals = (typeof mpwem_num_of_decimal !== 'undefined')    ? parseInt(mpwem_num_of_decimal) : 2;
        var fixed    = parseFloat(amount).toFixed(decimals);
        switch (position) {
            case 'right':       return fixed + symbol;
            case 'left_space':  return symbol + ' ' + fixed;
            case 'right_space': return fixed + ' ' + symbol;
            default:            return symbol + fixed;
        }
    }

    // Open the native checkout modal, pre-populated with ticket summary
    function mepOpenNativeModal(parent) {
        var tickets  = mepCollectTickets(parent);
        if (!tickets.length) {
            alert('Please Select Ticket Type');
            return;
        }

        var total = 0;
        var summaryHtml = '';
        tickets.forEach(function (t) {
            var lineTotal = t.ticket_price * t.ticket_qty;
            total += lineTotal;
            summaryHtml += '<div class="mep-ticket-summary-row">'
                + '<div class="mep-tsr-info">'
                +   '<span class="mep-tsr-name">' + mepNativeEsc(t.ticket_name) + '</span>'
                +   '<span class="mep-tsr-sub">' + mepNativeEsc(String(t.ticket_qty)) + ' &times; ' + mepNativeFormatPrice(t.ticket_price) + '</span>'
                + '</div>'
                + '<span class="mep-tsr-total">' + mepNativeFormatPrice(lineTotal) + '</span>'
                + '</div>';
        });

        var $modal    = $('#mep-native-checkout-modal');
        var eventDate = mepResolveEventDate(parent);

        $modal.find('#mep-native-ticket-summary').html(summaryHtml);
        $modal.find('#mep-native-total-display').text(mepNativeFormatPrice(total));

        // Event datetime line (hide the time when it's a meaningless midnight value).
        var dtText = mepNativeFormatEventDate(eventDate);
        var $dt    = $modal.find('#mep-native-event-datetime');
        if (dtText) { $dt.text(dtText).show(); } else { $dt.text('').hide(); }
        $modal.find('#mep-native-ticket-data').val(JSON.stringify(tickets));
        $modal.find('#mep-native-event-date').val(eventDate);
        $modal.find('#mep-native-checkout-msg').hide().removeClass('success error').text('');

        // Collect attendee field values from the main registration form so they are
        // saved with the order even though the fields are not shown inside this modal.
        // Only take the first occurrence of each field name (handles multi-ticket layouts
        // where the same attendee form is cloned once per ticket row).
        // Collect the registration-form fields once per seat, mirroring the WooCommerce
        // flow: each field posts as an array indexed by the global seat order and the server
        // builds one attendee per seat from it. Only the visible per-seat forms
        // (.mep_attendee_info) are read — never the hidden clone template.
        var attendeeFieldArrays = {};   // field name -> [value per seat] (submitted to the server)
        var fieldOrder  = [];           // field names, in display order
        var fieldLabels = {};           // field name -> human label
        var fieldTypes  = {};           // field name -> input type
        parent.find('.mep_attendee_info [data-field-name][data-d-name]').each(function () {
            var $inp  = $(this);
            var fname = $inp.data('field-name');
            var type  = ($inp.attr('type') || '').toLowerCase();
            if (!fname || type === 'file') {
                return; // file uploads are not supported in native checkout
            }
            (attendeeFieldArrays[fname] = attendeeFieldArrays[fname] || []).push($.trim($inp.val()));
            if (!fieldLabels.hasOwnProperty(fname)) {
                fieldOrder.push(fname);
                fieldTypes[fname]  = type;
                fieldLabels[fname] = $.trim($inp.closest('.mp_form_item').find('label span').first().text().replace(/\*+\s*$/, ''))
                                  || $.trim($inp.attr('placeholder') || '')
                                  || fname;
            }
        });
        $modal.find('#mep-native-attendee-snapshot').val(JSON.stringify(attendeeFieldArrays));

        // Build a compact, per-attendee preview of the registration details.
        var seatCount = 0;
        fieldOrder.forEach(function (f) { seatCount = Math.max(seatCount, attendeeFieldArrays[f].length); });
        var detailsHtml = '';
        for (var s = 0; s < seatCount; s++) {
            var rows = '';
            fieldOrder.forEach(function (fname) {
                if (fieldTypes[fname] === 'hidden') { return; }
                var v = attendeeFieldArrays[fname][s];
                v = (v == null) ? '' : $.trim(v);
                if (!v) { return; }
                rows += '<div class="mep-native-detail-row">'
                    + '<span class="mep-ndr-label">' + mepNativeEsc(fieldLabels[fname]) + '</span>'
                    + '<span class="mep-ndr-value">' + mepNativeEsc(v) + '</span>'
                    + '</div>';
            });
            if (rows) {
                var heading = seatCount > 1 ? ('Attendee ' + (s + 1)) : 'Registration Details';
                detailsHtml += '<div class="mep-native-attendee-block">'
                    + '<div class="mep-native-detail-title">' + mepNativeEsc(heading) + '</div>'
                    + rows + '</div>';
            }
        }

        var $details = $modal.find('#mep-native-attendee-details');
        if (detailsHtml) {
            $details.html(detailsHtml).show();
        } else {
            $details.empty().hide();
        }

        // Prefill the billing form from the event's attendee name/email/phone fields
        // (when present) so the user doesn't retype them.
        var preBilling = { name: '', email: '', phone: '' };
        parent.find('[data-field-name][data-d-name]').each(function () {
            var dname = $(this).data('d-name');
            var val   = $.trim($(this).val());
            if (!val) { return; }
            if (dname === 'ea_name'  && !preBilling.name)  { preBilling.name  = val; }
            if (dname === 'ea_email' && !preBilling.email) { preBilling.email = val; }
            if (dname === 'ea_phone' && !preBilling.phone) { preBilling.phone = val; }
        });
        if (preBilling.name)  { $modal.find('#mep-native-billing-name').val(preBilling.name); }
        if (preBilling.email) { $modal.find('#mep-native-billing-email').val(preBilling.email); }
        if (preBilling.phone) { $modal.find('#mep-native-billing-phone').val(preBilling.phone); }

        // When login is required to complete checkout, persist the ticket selection so it can
        // be restored once the user logs in and is redirected back to this page.
        if ($modal.find('.mep-native-login-required').length && window.sessionStorage) {
            var eventId = $modal.find('#mep-native-event-id').val();
            if (eventId) {
                try {
                    sessionStorage.setItem(PENDING_BOOKING_PREFIX + eventId, JSON.stringify({
                        tickets:   tickets,
                        eventDate: eventDate
                    }));
                } catch (e) {}
            }
        }

        $modal.css('display', 'flex').hide().fadeIn(200);
    }

    // Intercept book-now when native mode is present
    $(document).on('click', '.mpwem_book_now', function () {
        var parent = $(this).closest('.mpwem_registration_area');
        if (!parent.find('.mpwem_native_checkout_trigger').length) {
            return; // WooCommerce mode — let the existing handlers work
        }
        var totalQty = 0;
        parent.find('[name="option_qty[]"]').each(function () {
            totalQty += parseInt($(this).val()) || 0;
        });
        if (totalQty <= 0) {
            parent.find('[name="option_qty[]"]').addClass('error');
            return;
        }
        parent.find('[name="option_qty[]"]').removeClass('error');
        mepOpenNativeModal(parent);
    });

    // Close modal
    $(document).on('click', '.mep-native-modal-close', function () {
        $('#mep-native-checkout-modal').fadeOut(200);
    });
    $(document).on('click', '#mep-native-checkout-modal', function (e) {
        if ($(e.target).is('#mep-native-checkout-modal')) {
            $(this).fadeOut(200);
        }
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $('#mep-native-checkout-modal').fadeOut(200);
        }
    });

    // Submit native checkout
    $(document).on('click', '#mep-native-confirm-btn', function () {
        var $btn       = $(this);
        var $modal     = $('#mep-native-checkout-modal');
        var $msg       = $modal.find('#mep-native-checkout-msg');
        var eventId    = $modal.find('#mep-native-event-id').val();
        var nonce      = $modal.find('#mep-native-nonce').val();
        var ticketData = $modal.find('#mep-native-ticket-data').val();
        var eventDate  = $modal.find('#mep-native-event-date').val();
        var payMethod  = $modal.find('[name="mep_payment_method"]:checked').val() || 'offline';

        $msg.hide().removeClass('success error').text('');

        // Read the attendee field snapshot collected from the main form when the modal opened.
        var attendeeFields = {};
        var billingName  = '';
        var billingEmail = '';
        var billingPhone = '';
        try {
            var snapshotRaw = $modal.find('#mep-native-attendee-snapshot').val();
            if (snapshotRaw) {
                attendeeFields = JSON.parse(snapshotRaw) || {};
            }
        } catch (e) {}

        // Read billing details from the modal's billing form (all fields required).
        var $bName = $modal.find('#mep-native-billing-name');
        if ($bName.length) {
            var $bEmail = $modal.find('#mep-native-billing-email');
            var $bPhone = $modal.find('#mep-native-billing-phone');
            billingName  = $.trim($bName.val()  || '');
            billingEmail = $.trim($bEmail.val() || '');
            billingPhone = $.trim($bPhone.val() || '');

            $modal.find('.mep-native-input').removeClass('mep-native-input-error');
            var missing = [];
            if (!billingName)  { missing.push($bName); }
            if (!billingEmail) { missing.push($bEmail); }
            if (!billingPhone) { missing.push($bPhone); }
            if (missing.length) {
                missing.forEach(function ($f) { $f.addClass('mep-native-input-error'); });
                $msg.text('Please fill in all billing details (name, email and phone).').addClass('error').show();
                return;
            }
        } else {
            // Fallback (older template): derive billing from the first attendee's fields.
            $('.mpwem_registration_area').find('.mep_attendee_info [data-field-name][data-d-name]').each(function () {
                var $inp  = $(this);
                var fname = $inp.data('field-name');
                var dname = $inp.data('d-name');
                var v = attendeeFields[fname];
                if (Array.isArray(v)) { v = v[0]; }
                if (v === undefined) { return; }
                if (dname === 'ea_name'  && !billingName)  billingName  = v;
                if (dname === 'ea_email' && !billingEmail) billingEmail = v;
                if (dname === 'ea_phone' && !billingPhone) billingPhone = v;
            });
        }

        if (billingEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(billingEmail)) {
            $modal.find('#mep-native-billing-email').addClass('mep-native-input-error');
            $msg.text('Please enter a valid email address.').addClass('error').show();
            return;
        }

        $btn.prop('disabled', true)
            .find('.mep-native-btn-text').hide().end()
            .find('.mep-native-btn-loading').show();

        var ajaxUrl = (typeof mpwem_script_var !== 'undefined' && mpwem_script_var.url)
            ? mpwem_script_var.url
            : (typeof mpwem_ajax_url !== 'undefined' ? mpwem_ajax_url : '/wp-admin/admin-ajax.php');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action:         'mep_native_checkout',
                nonce:          nonce,
                event_id:       eventId,
                billing_name:    billingName,
                billing_email:   billingEmail,
                billing_phone:   billingPhone,
                event_date:      eventDate,
                ticket_data:     ticketData,
                attendee_fields: JSON.stringify(attendeeFields),
                payment_method: payMethod
            },
            success: function (response) {
                if (response.success) {
                    // Booking succeeded — clear any persisted intent for this event.
                    if (window.sessionStorage) {
                        try { sessionStorage.removeItem(PENDING_BOOKING_PREFIX + eventId); } catch (e) {}
                    }
                    $msg.text(response.data.message).addClass('success').show();
                    // Gateway redirect (PayPal, Stripe) — go immediately, no delay
                    if (response.data.requires_redirect && response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        // Free / offline — show success message then redirect to confirmation page
                        setTimeout(function () {
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            } else {
                                $modal.fadeOut(300);
                            }
                        }, 1800);
                    }
                } else {
                    var errMsg = (response.data && response.data.message)
                        ? response.data.message
                        : 'An error occurred. Please try again.';
                    $msg.text(errMsg).addClass('error').show();
                    $btn.prop('disabled', false)
                        .find('.mep-native-btn-text').show().end()
                        .find('.mep-native-btn-loading').hide();
                }
            },
            error: function () {
                $msg.text('Connection error. Please try again.').addClass('error').show();
                $btn.prop('disabled', false)
                    .find('.mep-native-btn-text').show().end()
                    .find('.mep-native-btn-loading').hide();
            }
        });
    });

    // Restore a booking intent persisted before a login redirect, then reopen the modal.
    $(function () {
        if (typeof mpwem_script_var === 'undefined' || mpwem_script_var.is_logged_in !== '1' || !window.sessionStorage) {
            return;
        }
        $('.mpwem_registration_area').each(function () {
            var $area   = $(this);
            var eventId = $area.find('#mep-native-event-id').val();
            if (!eventId) {
                return;
            }
            var raw = sessionStorage.getItem(PENDING_BOOKING_PREFIX + eventId);
            if (!raw) {
                return;
            }
            sessionStorage.removeItem(PENDING_BOOKING_PREFIX + eventId);
            var pending;
            try {
                pending = JSON.parse(raw);
            } catch (e) {
                return;
            }
            mepRestoreTickets($area, pending.tickets);
            mepRestoreAttendeeFields($area, pending.attendeeFields);
            mepOpenNativeModal($area);
        });
    });

    /**
     * Modern custom dropdown for #mpwem_time (keeps native select synced for booking AJAX).
     */
    function mpwem_close_modern_selects($except) {
        $('.mpwem-modern-select.is-open').each(function () {
            var $wrap = $(this);
            if ($except && $wrap.is($except)) {
                return;
            }
            $wrap.removeClass('is-open');
            $wrap.find('.mpwem-modern-select__trigger').attr('aria-expanded', 'false');
            $wrap.find('.mpwem-modern-select__menu').attr('hidden', true);
            $wrap.closest('.date-time-area').removeClass('has-modern-select-open');
        });
    }

    function mpwem_sync_modern_time_select($select) {
        var $wrap = $select.closest('.mpwem-modern-select');
        if (!$wrap.length) {
            return;
        }
        var $selected = $select.find('option:selected');
        var label = $.trim($selected.text()) || $.trim($select.find('option').first().text()) || '';
        var value = $select.val() || '';
        $wrap.find('.mpwem-modern-select__value').text(label);
        $wrap.find('.mpwem-modern-select__option').each(function () {
            var $opt = $(this);
            var isActive = $opt.attr('data-value') === value;
            $opt.toggleClass('is-selected', isActive).attr('aria-selected', isActive ? 'true' : 'false');
        });
    }

    function mpwem_build_modern_time_select($select) {
        if (!$select.length || $select.data('mpwemModernSelect')) {
            return;
        }

        var $wrap = $('<div class="mpwem-modern-select" />');
        var uid = 'mpwem-modern-select-' + Math.random().toString(36).slice(2, 9);
        var $trigger = $(
            '<button type="button" class="mpwem-modern-select__trigger" aria-haspopup="listbox" aria-expanded="false">' +
            '<span class="mpwem-modern-select__value"></span>' +
            '<span class="mpwem-modern-select__chevron" aria-hidden="true"></span>' +
            '</button>'
        );
        var $menu = $('<ul class="mpwem-modern-select__menu" role="listbox" hidden></ul>');

        $trigger.attr('aria-controls', uid);
        $menu.attr('id', uid);

        $select.find('option').each(function () {
            var $option = $(this);
            var value = $option.attr('value');
            if (typeof value === 'undefined') {
                return;
            }
            var $item = $('<li class="mpwem-modern-select__option" role="option" tabindex="-1"></li>');
            $item.attr('data-value', value);
            $item.attr('aria-selected', $option.is(':selected') ? 'true' : 'false');
            $item.toggleClass('is-selected', $option.is(':selected'));
            $item.text($.trim($option.text()));
            $menu.append($item);
        });

        $select
            .addClass('mpwem-modern-select__native')
            .attr('tabindex', '-1')
            .attr('aria-hidden', 'true')
            .after($wrap);

        $wrap.append($select);
        $wrap.append($trigger);
        $wrap.append($menu);
        $select.data('mpwemModernSelect', true);
        mpwem_sync_modern_time_select($select);

        $select.closest('label').on('click.mpwemModernSelect', function (e) {
            if ($(e.target).closest('.mpwem-modern-select__menu, .mpwem-modern-select__option, .mpwem-modern-select__trigger').length) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            $trigger.trigger('focus').trigger('click');
        });

        $trigger.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var willOpen = !$wrap.hasClass('is-open');
            mpwem_close_modern_selects(willOpen ? $wrap : null);
            if (!willOpen) {
                return;
            }
            $wrap.addClass('is-open');
            $trigger.attr('aria-expanded', 'true');
            $menu.removeAttr('hidden');
            $wrap.closest('.date-time-area').addClass('has-modern-select-open');
            $menu.find('.mpwem-modern-select__option.is-selected').trigger('focus');
        });

        $menu.on('click', '.mpwem-modern-select__option', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var value = $(this).attr('data-value');
            if ($select.val() !== value) {
                $select.val(value).trigger('change');
            } else {
                mpwem_sync_modern_time_select($select);
            }
            mpwem_close_modern_selects();
            $trigger.trigger('focus');
        });

        $select.on('change.mpwemModernSelect', function () {
            mpwem_sync_modern_time_select($select);
        });

        $trigger.on('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (!$wrap.hasClass('is-open')) {
                    $trigger.trigger('click');
                }
            } else if (e.key === 'Escape') {
                mpwem_close_modern_selects();
            }
        });

        $menu.on('keydown', '.mpwem-modern-select__option', function (e) {
            var $items = $menu.find('.mpwem-modern-select__option');
            var index = $items.index(this);
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                $items.eq(Math.min(index + 1, $items.length - 1)).trigger('focus');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                $items.eq(Math.max(index - 1, 0)).trigger('focus');
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            } else if (e.key === 'Escape') {
                e.preventDefault();
                mpwem_close_modern_selects();
                $trigger.trigger('focus');
            }
        });
    }

    function mpwem_init_modern_time_select(context) {
        var $root = context ? $(context) : $(document);
        // Context may be the .mpwem_time_area itself (display:contents) or a parent.
        var $selects = $root.is('select#mpwem_time, select[name="mpwem_time"]')
            ? $root
            : $root.find('select#mpwem_time, select[name="mpwem_time"]');
        if (!$selects.length && $root.find('.mpwem_time_area').length) {
            $selects = $root.find('.mpwem_time_area select#mpwem_time, .mpwem_time_area select[name="mpwem_time"]');
        }
        $selects.each(function () {
            mpwem_build_modern_time_select($(this));
        });
    }

    $(document).on('click', function () {
        mpwem_close_modern_selects();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            mpwem_close_modern_selects();
        }
    });

    $(function () {
        mpwem_init_modern_time_select('.mpwem_registration_area');
    });

}(jQuery));
