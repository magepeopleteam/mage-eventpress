function mpwem_price_calculation(parent) {
    try {
        const total_qty = mpwem_qty(parent);
        mpwem_attendee_management(parent, total_qty);
        const target_summary = parent.find('.mpwem_total');
        let total = mpwem_price(parent);
        if (total_qty > 0) {
            parent.find('.mpwem_ex_service').slideDown('fast');
            parent.find('.mpwem_form_submit_area button').removeAttr('disabled');
            total += mpwem_ex_price(parent);
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
        const time_slot = parent.find('#mpwem_time');
        if (time_slot.length > 0) {
            const post_id = parent.find('[name="mpwem_post_id"]').val();
            const dates = parent.find('[name="mpwem_date_time"]').val();
            const target = parent.find('.mpwem_time_area');
            jQuery.ajax({
                type: 'POST',
                url: mpwem_ajax_url,
                data: {
                    action: "get_mpwem_time",
                    post_id: post_id,
                    dates: dates,
                },
                beforeSend: function () {
                    dLoader_xs(target);
                },
                success: function (data) {
                    target.html(data).slideDown('fast').promise().done(function () {
                        const date = parent.find('[name="mpwem_time"]').val();
                        get_mpwem_ticket(target, date);
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error loading time slots:', error);
                    target.html('<p class="error">Error loading time slots. Please try again.</p>');
                },
                complete: function () {
                    dLoaderRemove(target);
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
                "backend_order": window.location.href.search("mpwem_backend_order"),
            },
            beforeSend: function () {
                dLoader_xs(target);
            },
            success: function (data) {
                target.html(data).slideDown('fast').promise().done(function () {
                    mpwem_price_calculation(parent);
                });
            }
        });
    }
    $(document).on('change', '.mpwem_registration_area [name="option_qty[]"]', function () {
        let parent = $(this).closest('.mpwem_registration_area');
        let qty = $(this).val();
        let total_qty = mpwem_qty(parent);
        if (parent.find('[name="mepgq_max_qty"]').length > 0) {
            let max_qty_gq = parseInt(parent.find('[name="mepgq_max_qty"]').val());
            if ( max_qty_gq>0 && total_qty > max_qty_gq) {
                qty = qty - total_qty + max_qty_gq;
                $(this).val(qty);
                mpwem_price_calculation(parent);
            } else {
                mpwem_price_calculation(parent);
            }
        } else if (parent.find('[name="mepmm_min_qty"]').length > 0) {
            let max_qty = parseInt(parent.find('[name="mepmm_max_qty"]').val());
            if (max_qty>0 && total_qty > max_qty) {
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
    $(document).on('change', '.mep_form_item [data-target-condition-id]', function () {
        let condition_id = $(this).attr('data-target-condition-id');
        //alert(condition_id);
        if (condition_id) {
            //alert(condition_id);
            let child_id = $(this).find('option:selected').attr('data-target-child-id');
            let parent = $(this).closest('.mep_form_item');
            $(this).find('option').each(function () {
                parent.find('[data-condition-id="' + condition_id + '"]').each(function () {
                    let condition_value = $(this).attr('data-condition-value');
                    if (condition_value) {
                        if (condition_value === child_id) {
                            $(this).removeClass('dNone').slideDown('fast');
                        } else {
                            $(this).slideUp('fast')
                        }
                    }
                });
            });
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
    //****************************************************************//
    //****************************************************************//
    //****************************************************************//
    //****************************************************************//
}(jQuery));
function mp_event_wo_commerce_price_format(price) {
    let currency_position = jQuery('input[name="currency_position"]').val();
    let currency_symbol = jQuery('input[name="currency_symbol"]').val();
    let currency_decimal = jQuery('input[name="currency_decimal"]').val();
    let currency_thousands_separator = jQuery('input[name="currency_thousands_separator"]').val();
    let currency_number_of_decimal = jQuery('input[name="currency_number_of_decimal"]').val();
    let price_text = '';
    price = price.toFixed(currency_number_of_decimal);
// console.log('price= '+ price);
    let total_part = price.toString().split(".");
    total_part[0] = total_part[0].replace(/\B(?=(\d{3})+(?!\d))/g, currency_thousands_separator);
    price = total_part.join(currency_decimal);
    if (currency_position === 'right') {
        price_text = price + currency_symbol;
    } else if (currency_position === 'right_space') {
        price_text = price + '&nbsp;' + currency_symbol;
    } else if (currency_position === 'left') {
        price_text = currency_symbol + price;
    } else {
        price_text = currency_symbol + '&nbsp;' + price;
    }
    // console.log('price= '+ price_text);
    return price_text;
}
(function ($) {
//added by sumon
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
    $(document).on('click', 'button.mep_view_vr_btn', function () {
        $(this).closest('tr').next('tr.mep_virtual_event_info_sec').slideToggle('fast');
    });
}(jQuery));