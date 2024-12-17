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
	$(document).on('keyup change', '.mp_ticket_type_table [name="option_name_t[]"],.mp_ticket_type_table [name="option_name[]"]', function () {
		let n = $(this).val();
		$(this).val(n.replace(/[@%'":;&_]/g, ''));
	});

	// =====================sidebar modal open close=============
	$(document).on('click', '[data-modal]', function (e) {
		const modalTarget = $(this).data('modal');
		$(`[data-modal-target="${modalTarget}"]`).addClass('open');
	});

	$(document).on('click', '[data-modal-target] .mpwpb-modal-close', function (e) {
		$(this).closest('[data-modal-target]').removeClass('open');
	});
	
}(jQuery));

/**
 * @author Shahadat Hossain <raselsha@gmail.com>
 */
jQuery(document).ready(function ($) {
	mpevSwitch();
	resetBooking();
	richTextStatus();
	//  ===============Toggle radio switch==============
	function mpevSwitch() {
		$('.mpev-switch .slider').click(function() {
			var checkbox = $(this).prev('input[type="checkbox"]');
			var toggleValues = checkbox.data('toggle-values').split(',');
			var currentValue = checkbox.val();
			var nextValue = toggleValues[0];

			if (currentValue === toggleValues[0]) {
				nextValue = toggleValues[1];
				$(".mep_hide_on_load").slideUp(200);
			} else {
				nextValue = toggleValues[0];
				$(".mep_hide_on_load").slideDown(200);
			}
			
			checkbox.val(nextValue);

			var target = checkbox.data('collapse-target');
			var close = checkbox.data('close-target');
			$(target).slideToggle();
			$(close).slideToggle();
		});
	}
/**
 * @description Reset booking by ajax
 **/
	function resetBooking(){
		$('#mep-reset-booking').click(function(e){
			e.preventDefault();
			var postID = $(this).data('post-id');
			$.ajax({
				url: mep_ajax.mep_ajaxurl,
				type: 'POST',
				data: {
					action: 'mep_reset_booking', 
					nonce: mep_ajax.nonce,
					post_id: postID
				},
				success: function(response) {
					$('#mp-reset-status').html(response.data);
				},
				error: function(xhr, status, error) {
					console.log('Error:', error);
				}
			});
		});
	}
	/**
 * @description Rich text status update
 **/
	function richTextStatus(){
		$('#mep_rich_text_status').on('change', function() {
			var status = $(this).val();
			
			if (status === 'enable') {
				$('#mep_rich_text_table').slideDown(); // Show the section
			} else {
				$('#mep_rich_text_table').slideUp(); // Hide the section
			}
		});
	
		// Initialize visibility based on the current selection on page load
		var initialStatus = $('#mep_rich_text_status').val();
		if (initialStatus === 'enable') {
			$('#mep_rich_text_table').slideDown();
		} else {
			$('#mep_rich_text_table').slideUp();
		}
	}
	
});



