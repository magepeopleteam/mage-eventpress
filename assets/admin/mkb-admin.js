(function ($) {
	"use strict";
	jQuery(window).on('load',(function () {
		jQuery('.mp_tab_menu').each(function () {
			jQuery(this).find('ul li:first-child').trigger('click');
		});
		if (jQuery('[name="mep_org_address"]').val() > 0) {
			jQuery('.mp_event_address').slideUp(250);
		}
	}));
	jQuery(document).on('click', '[data-target-tabs]', function () {
		if (!jQuery(this).hasClass('active')) {
			let tabsTarget = $(this).attr('data-target-tabs');
			let targetParent = $(this).closest('.mp_event_tab_area').find('.mp_tab_details').first();
			targetParent.children('.mp_tab_item:visible').slideUp('fast');
			targetParent.children('.mp_tab_item[data-tab-item="' + tabsTarget + '"]').slideDown(250);
			jQuery(this).siblings('li.active').removeClass('active');
			jQuery(this).addClass('active');
		}
		return false;
	});
	jQuery(document).on('click', 'label.mp_event_virtual_type_des_switch input', function () {
		if (jQuery(this).is(":checked")) {
			jQuery(this).parents('label.mp_event_virtual_type_des_switch').siblings('label.mp_event_virtual_type_des').slideDown(200);
			jQuery(".mep_event_tab_location_content").hide(200);
		} else {
			jQuery(this).parents('label.mp_event_virtual_type_des_switch').siblings('label.mp_event_virtual_type_des').val('').slideUp(200);
			jQuery(".mep_event_tab_location_content").show(200);
		}
	});
	jQuery(document).on('click', 'label.mp_event_ticket_type_des_switch input', function () {
		if (jQuery(this).is(":checked")) {
			// $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').slideDown(200);
			jQuery(".mep_ticket_type_setting_sec").slideDown(200);
		} else {
			// $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').val('').slideUp(200);
			jQuery(".mep_ticket_type_setting_sec").slideUp(200);
		}
	});
	jQuery(document).on('click', 'label.mep_enable_custom_dt_format input', function () {
		if (jQuery(this).is(":checked")) {
			// $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').slideDown(200);
			jQuery(".mep_custom_timezone_setting").slideDown(200);
		} else {
			// $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').val('').slideUp(200);
			jQuery(".mep_custom_timezone_setting").slideUp(200);
		}
	});
	jQuery(document).on('click', 'label.mp_event_ticket_type_advance_col_switch input', function () {
		if (jQuery(this).is(":checked")) {
			// $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').slideDown(200);
			jQuery(".mep_hide_on_load").slideDown(200);
		} else {
			// $(this).parents('label.mp_event_ticket_type_des_switch').siblings('label.mep_ticket_type_setting_sec').val('').slideUp(200);
			jQuery(".mep_hide_on_load").slideUp(200);
		}
	});
	$(document).ready(function () {
		jQuery('#add-row-t').on('click', function () {
			var row = jQuery('.empty-row-t.screen-reader-text').clone(true);
			row.removeClass('empty-row-t screen-reader-text');
			row.insertBefore('#repeatable-fieldset-one-t tbody>tr:last');
			jQuery('#mep_ticket_type_empty option[value=inputbox]').attr('selected', 'selected');
			jQuery('.empty-row-t #mep_ticket_type_empty option[value=inputbox]').removeAttr('selected');
			//return false;
		});
		jQuery('.remove-row-t').on('click', function () {
			if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
				jQuery(this).parents('tr').remove();
				jQuery('#mep_ticket_type_empty option[value=inputbox]').removeAttr('selected');
				jQuery('#mep_ticket_type_empty option[value=dropdown]').removeAttr('selected');
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
}(jQuery));