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
			let current=$(this).siblings('ul.mp_event_more_date_list');
			if(current.is(':visible')){
				let active_text=$(this).data('active-text');
				$(this).html(active_text);
				current.slideUp(200);
			}

		}).promise().done(function () {
			let current_list=target.siblings('ul.mp_event_more_date_list');
			if(current_list.length>0){
				if(current_list.is(':visible')){
					current_list.slideUp(200);
					target.html(target.data('active-text'));
				}else{
					current_list.slideDown(200);
					target.html(target.data('hide-text'));
				}
			}else{
				let event_id = target.data('event-id');
				$.ajax({
					type: 'POST',
					url: mp_ajax_url,
					data: {"action": "mep_event_list_date_schedule", "event_id":event_id},
					beforeSend: function(){
						target.html('<span class="fas fa-spinner fa-pulse"></span>');
					},
					success: function(data){
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