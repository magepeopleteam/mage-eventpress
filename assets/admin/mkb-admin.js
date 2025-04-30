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

}(jQuery));

/**
 * @author Shahadat Hossain <raselsha@gmail.com>
 */

(function($){
	// ==============toggle switch radio button=================
	$(document).on('click','.mpev-switch .mpev-slider',function(){
		var checkbox = $(this).prev('input[type="checkbox"]');
		var toggleValues = checkbox.data('toggle-values').split(',');
		var currentValue = checkbox.val();
		var nextValue = toggleValues[0];

		if (currentValue === toggleValues[0]) {
			nextValue = toggleValues[1];
			if (checkbox.attr('name') === 'mep_show_advance_col_status') {
				$(".mep_hide_on_load").slideUp(200);
			}
			if(checkbox.attr('name') ==='mep_disable_ticket_time'){
				$(".mep-special-datetime").slideUp(200);
			}
			if(checkbox.attr('name') ==='mep_show_category'){
				$(".mep_hide_on_load_cat").slideUp(200);
			}
		} else {
			nextValue = toggleValues[0];
			if (checkbox.attr('name') === 'mep_show_advance_col_status') {
				$(".mep_hide_on_load").slideDown(200);
			}
			if(checkbox.attr('name') ==='mep_disable_ticket_time'){
				$(".mep-special-datetime").slideDown(200);
			}
			if(checkbox.attr('name') ==='mep_show_category'){
				$(".mep_hide_on_load_cat").slideDown(200);
			}
		}
		checkbox.val(nextValue);
		var target = checkbox.data('collapse-target');
		var close = checkbox.data('close-target');
		$(target).slideToggle();
		$(close).slideToggle();
	});
	//========================reset booking==================
	$(document).on('click', '#mep-reset-booking', function(e) {

        // mep-reset-booking-nonce
		var postID = $(this).data('post-id'); 
		var resetNonce = $('#mep-reset-booking-nonce').val(); 
            jQuery.ajax({
                type: 'POST',
                // url:mep_ajax.mep_ajaxurl,
                url: ajaxurl,
                data: {
					action: 'mep_reset_booking_func', 
					nonce: resetNonce,
					post_id: postID
                },
                    beforeSend: function(){
                        jQuery('#mp-reset-status').html('');                       
                    },
                    success: function(data){                       
                           jQuery('#mp-reset-status').html(data);    
                                                    
                    }
                });
               return false;

	});

	// ========Initialize visibility based on the current selection ===========
	var initialStatus = $('#mep_rich_text_status').val();
	if (initialStatus === 'enable') {
		$('#mep_rich_text_table').slideDown();
	} else {
		$('#mep_rich_text_table').slideUp();
	}

	$(document).on('change','#mep_rich_text_status',function(){
		var status = $(this).val();
		if (status === 'enable') {
			$('#mep_rich_text_table').slideDown(); // Show the section
		} else {
			$('#mep_rich_text_table').slideUp(); // Hide the section
		}
	});
// =====================sidebar modal open close=============
	$(document).on('click', '[data-modal]', function (e) {
		const modalTarget = $(this).data('modal');
		$(`[data-modal-target="${modalTarget}"]`).addClass('open');
	});

	$(document).on('click', '[data-modal-target] .mep-modal-close', function (e) {
		$(this).closest('[data-modal-target]').removeClass('open');
	});
	
// ================ F.A.Q. ===================================
	$(document).on('click', '.mep-faq-item-new', function (e) {
		$('#mep-faq-msg').html('');
		$('.mep_faq_save_buttons').show();
		$('.mep_faq_update_buttons').hide();
		empty_faq_form();
	});

	

	function close_sidebar_modal(e){
		e.preventDefault();
		e.stopPropagation();
		$('.mep-modal-container').removeClass('open');
	}

	$(document).on('click', '.mep-faq-item-edit', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$('#mep-faq-msg').html('');
		$('.mep_faq_save_buttons').hide();
		$('.mep_faq_update_buttons').show();
		var itemId = $(this).closest('.mep-faq-item').data('id');
		var parent = $(this).closest('.mep-faq-item');
		var headerText = parent.find('.faq-header p').text().trim();
		var faqContentId = parent.find('.faq-content').text().trim();
		var editorId = 'mep_faq_content';
		$('input[name="mep_faq_title"]').val(headerText);
		$('input[name="mep_faq_item_id"]').val(itemId);
		if (tinymce.get(editorId)) {
			tinymce.get(editorId).setContent(faqContentId);
		} else {
			$('#' + editorId).val(faqContentId);
		}
	});

	$(document).on('click', '.mep-faq-item-delete', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var itemId = $(this).closest('.mep-faq-item').data('id');

		var isConfirmed = confirm('Are you sure you want to delete this row?');
		if (isConfirmed) {
			delete_faq_item(itemId);
		} else {
			console.log('Deletion canceled.'+itemId);
		}
	});
	

	function empty_faq_form(){
		$('input[name="mep_faq_title"]').val('');
		tinyMCE.get('mep_faq_content').setContent('');
		$('input[name="mep_faq_item_id"]').val('');
	}
	

	$(document).on('click', '#mep_faq_update', function (e) {
		e.preventDefault();
		update_faq();
	});

	$(document).on('click', '#mep_faq_save', function (e) {
		e.preventDefault();
		save_faq();
	});

	$(document).on('click', '#mep_faq_save_close', function (e) {
		e.preventDefault();
		save_faq();
		close_sidebar_modal(e);
	});

	function update_faq(){
		var title   = $('input[name="mep_faq_title"]');
		var content = tinyMCE.get('mep_faq_content').getContent();
		var postID  = $('input[name="mep_post_id"]');
		var itemId = $('input[name="mep_faq_item_id"]');
		$.ajax({
			url: mp_ajax_url,
			type: 'POST',
			data: {
				action: 'mep_faq_data_update',
				mep_faq_title:title.val(),
				mep_faq_content:content,
				mep_faq_postID:postID.val(),
				mep_faq_itemID:itemId.val(),
				nonce: mep_ajax.nonce
			},
			success: function(response) {
				$('#mep-faq-msg').html(response.data.message);
				$('.mep-faq-items').html('');
				$('.mep-faq-items').append(response.data.html);
				setTimeout(function(){
					$('.mep-modal-container').removeClass('open');
					empty_faq_form();
				},1000);
				
			},
			error: function(error) {
				console.log('Error:', error);
			}
		});
	}

	function save_faq(){
		var title   = $('input[name="mep_faq_title"]');
		var content = tinyMCE.get('mep_faq_content').getContent();
		var postID  = $('input[name="mep_post_id"]');
		$.ajax({
			url: mp_ajax_url,
			type: 'POST',
			data: {
				action: 'mep_faq_data_save',
				mep_faq_title:title.val(),
				mep_faq_content:content,
				mep_faq_postID:postID.val(),
				nonce: mep_ajax.nonce
			},
			success: function(response) {
				$('#mep-faq-msg').html(response.data.message);
				$('.mep-faq-items').html('');
				$('.mep-faq-items').append(response.data.html);
				empty_faq_form();
			},
			error: function(error) {
				console.log('Error:', error);
			}
		});
	}

	function delete_faq_item(itemId){
		var postID  = $('input[name="mep_post_id"]');
		$.ajax({
			url: mp_ajax_url,
			type: 'POST',
			data: {
				action: 'mep_faq_delete_item',
				mep_faq_postID:postID.val(),
				itemId:itemId,
				nonce: mep_ajax.nonce
			},
			success: function(response) {
				$('.mep-faq-items').html('');
				$('.mep-faq-items').append(response.data.html);
			},
			error: function(error) {
				console.log('Error:', error);
			}
		});
	}
// ================ Timeline Details ===================================
$(document).on('click', '.mep-timeline-item-new', function (e) {
	$('#mep-timeline-msg').html('');
	$('.mep_timeline_save_buttons').show();
	$('.mep_timeline_update_buttons').hide();
	empty_timeline_form();
});



function close_sidebar_modal(e){
	e.preventDefault();
	e.stopPropagation();
	$('.mep-modal-container').removeClass('open');
}

$(document).on('click', '.mep-timeline-item-edit', function (e) {
	e.preventDefault();
	e.stopPropagation();
	$('#mep-timeline-msg').html('');
	$('.mep_timeline_save_buttons').hide();
	$('.mep_timeline_update_buttons').show();
	var itemId = $(this).closest('.mep-timeline-item').data('id');
	var parent = $(this).closest('.mep-timeline-item');
	var headerText = parent.find('.timeline-header p').text().trim();
	var timelineContentId = parent.find('.timeline-content').text().trim();
	var editorId = 'mep_timeline_content';
	$('input[name="mep_timeline_title"]').val(headerText);
	$('input[name="mep_timeline_item_id"]').val(itemId);
	if (tinymce.get(editorId)) {
		tinymce.get(editorId).setContent(timelineContentId);
	} else {
		$('#' + editorId).val(timelineContentId);
	}
});

$(document).on('click', '.mep-timeline-item-delete', function (e) {
	e.preventDefault();
	e.stopPropagation();
	var itemId = $(this).closest('.mep-timeline-item').data('id');

	var isConfirmed = confirm('Are you sure you want to delete this row?');
	if (isConfirmed) {
		delete_timeline_item(itemId);
	} else {
		console.log('Deletion canceled.'+itemId);
	}
});


function empty_timeline_form(){
	$('input[name="mep_timeline_title"]').val('');
	tinyMCE.get('mep_timeline_content').setContent('');
	$('input[name="mep_timeline_item_id"]').val('');
}


$(document).on('click', '#mep_timeline_update', function (e) {
	e.preventDefault();
	update_timeline();
});

$(document).on('click', '#mep_timeline_save', function (e) {
	e.preventDefault();
	save_timeline();
});

$(document).on('click', '#mep_timeline_save_close', function (e) {
	e.preventDefault();
	save_timeline();
	close_sidebar_modal(e);
});

function update_timeline(){
	var title   = $('input[name="mep_timeline_title"]');
	var content = tinyMCE.get('mep_timeline_content').getContent();
	var postID  = $('input[name="mep_post_id"]');
	var itemId = $('input[name="mep_timeline_item_id"]');
	$.ajax({
		url: mp_ajax_url,
		type: 'POST',
		data: {
			action: 'mep_timeline_data_update',
			mep_timeline_title:title.val(),
			mep_timeline_content:content,
			mep_timeline_postID:postID.val(),
			mep_timeline_itemID:itemId.val(),
			nonce: mep_ajax.nonce
		},
		success: function(response) {
			$('#mep-timeline-msg').html(response.data.message);
			$('.mep-timeline-items').html('');
			$('.mep-timeline-items').append(response.data.html);
			setTimeout(function(){
				$('.mep-modal-container').removeClass('open');
				empty_timeline_form();
			},1000);
			
		},
		error: function(error) {
			console.log('Error:', error);
		}
	});
}

function save_timeline(){
	var title   = $('input[name="mep_timeline_title"]');
	var content = tinyMCE.get('mep_timeline_content').getContent();
	var postID  = $('input[name="mep_post_id"]');
	$.ajax({
		url: mp_ajax_url,
		type: 'POST',
		data: {
			action: 'mep_timeline_data_save',
			mep_timeline_title:title.val(),
			mep_timeline_content:content,
			mep_timeline_postID:postID.val(),
			nonce: mep_ajax.nonce
		},
		success: function(response) {
			$('#mep-timeline-msg').html(response.data.message);
			$('.mep-timeline-items').html('');
			$('.mep-timeline-items').append(response.data.html);
			empty_timeline_form();
		},
		error: function(error) {
			console.log('Error:', error);
		}
	});
}

function delete_timeline_item(itemId){
	var postID  = $('input[name="mep_post_id"]');
	$.ajax({
		url: mp_ajax_url,
		type: 'POST',
		data: {
			action: 'mep_timeline_delete_item',
			mep_timeline_postID:postID.val(),
			itemId:itemId,
			nonce: mep_ajax.nonce
		},
		success: function(response) {
			$('.mep-timeline-items').html('');
			$('.mep-timeline-items').append(response.data.html);
		},
		error: function(error) {
			console.log('Error:', error);
		}
	});
}	

// ================ Email Text Settings ===================================
$(document).on('click', '.mep-email-text-new', function (e) {
	$('#mep-email-text-msg').html('');
	var email_text = $(this).siblings('.mep-email-text').html().trim();
	var editorId = 'mep_event_cc_email_text';
	if (tinymce.get(editorId)) {
		tinymce.get(editorId).setContent(email_text);
	} else {
		$('#' + editorId).val(email_text);
	}
});

function close_sidebar_modal(e){
	e.preventDefault();
	e.stopPropagation();
	$('.mep-modal-container').removeClass('open');
}

$(document).on('click', '#mep_email_text_save', function (e) {
	e.preventDefault();
	save_email_text();
});

$(document).on('click', '#mep_email_text_save_close', function (e) {
	e.preventDefault();
	save_email_text();
	close_sidebar_modal(e);
});

function save_email_text(){
	var content = tinyMCE.get('mep_event_cc_email_text').getContent();
	var postID  = $('input[name="mep_post_id"]');	
	$.ajax({
		url: mp_ajax_url,
		type: 'POST',
		data: {
			action: 'mep_email_text_save',
			mep_email_text_content:content,
			mep_email_text_postID:postID.val(),
			nonce: mep_ajax.nonce
		},
		success: function(response) {
			$('#mep-email-text-msg').html(response.data.message);
			$('.mep-email-text').html('');
			$('.mep-email-text').append(response.data.html);
			
		},
		error: function(error) {
			console.log('Error:', error);
		}
	});
}

// ================ Icon Select Settings ===============
$(document).on('click','.fa-icon-lists [data-icon]',function(e){
	e.preventDefault();
	var icon = $(this).data('icon');
	$('.fa-icon-lists [data-icon]').removeClass('active');
	$(this).addClass('active');
	$("input[name='mep_event_speaker_icon']").val(icon);
	$('.mep-icon-wrapper i').removeClass();
	$('.mep-icon-wrapper i').addClass(icon);
	
	$('.mep-icon-preview i').removeClass();
	$('.mep-icon-preview i').addClass(icon);
});

$(document).on('input',"input[name='mep_icon_search_box']",function(){
	var searchQuery=$(this).val();
	$.ajax({
		url: ajaxurl, // WordPress's AJAX URL (for admin-ajax.php)
		method: 'POST',
		data: {
			action: 'mep_pick_icon', // The action name to hook into
			query: searchQuery, // The search query
		},
		success: function (response) {
			// Clear the icon list container
			$('.fa-icon-lists').html('');
			console.log(response);
			$.each(response, function (className, title) {
				$('.fa-icon-lists').append(`
					<div class="icon" title="${title}" data-icon="${className}">
						<i class="${className}"></i>
					</div>
				`);
			});
		},
	});
});

})(jQuery);



