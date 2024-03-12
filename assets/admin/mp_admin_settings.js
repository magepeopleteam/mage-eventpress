function load_sortable_datepicker(parent, item) {
	parent.find('.mp_item_insert').first().append(item).promise().done(function () {
		parent.find('.mp_sortable_area').sortable({
			handle: jQuery(this).find('.mp_sortable_button')
		});
		mp_load_date_picker(parent);
	});
	return true;
}
(function ($) {
	"use strict";
	$(document).ready(function () {
		//=========Short able==============//
		$(document).find('.mp_sortable_area').sortable({
			handle: $(this).find('.mp_sortable_button')
		});
	});
	//=========Remove Setting Item ==============//
	$(document).on('click', '.mp_item_remove', function () {
		if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
			$(this).closest('.mp_remove_area').slideUp(250, function () {
				$(this).remove();
			});
			return true;
		}
		return false;
	});
	//=========Add Setting Item==============//
	$(document).on('click', '.mp_add_item', function () {
		let parent = $(this).closest('.mp_settings_area');
		let item = $(this).next($('.mp_hidden_content')).find(' .mp_hidden_item').html();
		if (!item || item === "undefined" || item === " ") {
			item = parent.find('.mp_hidden_content').first().find('.mp_hidden_item').html();
		}
		load_sortable_datepicker(parent, item);
		parent.find('.mp_item_insert').find('.add_mp_select2').select2({});
		return true;
	});
}(jQuery));
//=========upload image==============//
(function ($) {
	"use strict";
	$(document).on('click', '.mp_add_single_image', function () {
		let parent = $(this);
		parent.find('.mp_single_image_item').remove();
		wp.media.editor.send.attachment = function (props, attachment) {
			let attachment_id = attachment.id;
			let attachment_url = attachment.url;
			let html = '<div class="mp_single_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times circleIcon_xs mp_remove_single_image"></span>';
			html += '<img src="' + attachment_url + '" alt="' + attachment_id + '"/>';
			html += '</div>';
			parent.append(html);
			parent.find('input').val(attachment_id);
			parent.find('button').slideUp('fast');
		}
		wp.media.editor.open($(this));
		return false;
	});
	$(document).on('click', '.mp_remove_single_image', function (e) {
		e.stopPropagation();
		let parent = $(this).closest('.mp_add_single_image');
		$(this).closest('.mp_single_image_item').remove();
		parent.find('input').val('');
		parent.find('button').slideDown('fast');
	});
	$(document).on('click', '.mp_remove_multi_image', function () {
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
	$(document).on('click', '.add_multi_image', function () {
		let parent = $(this).closest('.mp_multi_image_area');
		wp.media.editor.send.attachment = function (props, attachment) {
			let attachment_id = attachment.id;
			let attachment_url = attachment.url;
			let html = '<div class="mp_multi_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times circleIcon_xs mp_remove_multi_image"></span>';
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
//=================select icon / image=========================//
(function ($) {
	"use strict";
	$(document).on('click', 'button.mp_image_add', function () {
		let $this = $(this);
		let parent = $this.closest('.mp_add_icon_image_area');
		wp.media.editor.send.attachment = function (props, attachment) {
			let attachment_id = attachment.id;
			let attachment_url = attachment.url;
			parent.find('input[type="hidden"]').val(attachment_id);
			parent.find('.mp_icon_item').slideUp('fast');
			parent.find('img').attr('src', attachment_url);
			parent.find('.mp_image_item').slideDown('fast');
			parent.find('.mp_add_icon_image_button_area').slideUp('fast');
		}
		wp.media.editor.open($this);
		return false;
	});
	$(document).on('click', '.mp_add_icon_image_area .mp_image_remove', function () {
		let parent = $(this).closest('.mp_add_icon_image_area');
		parent.find('input[type="hidden"]').val('');
		parent.find('img').attr('src', '');
		parent.find('.mp_image_item').slideUp('fast');
		parent.find('.mp_add_icon_image_button_area').slideDown('fast');
	});
	$(document).on('click', '.mp_add_icon_image_area button.mp_icon_add', function () {
		let target_popup = $('.mp_add_icon_popup');
		target_popup.find('.iconItem').click(function () {
			let parent = $('[data-active-popup]').closest('.mp_add_icon_image_area');
			let icon_class = $(this).data('icon-class');
			if (icon_class) {
				parent.find('input[type="hidden"]').val(icon_class);
				parent.find('.mp_add_icon_image_button_area').slideUp('fast');
				parent.find('.mp_image_item').slideUp('fast');
				parent.find('.mp_icon_item').slideDown('fast');
				parent.find('[data-add-icon]').removeAttr('class').addClass(icon_class);
				target_popup.find('.iconItem').removeClass('active');
				target_popup.find('.popupClose').trigger('click');
			}
		});
		target_popup.find('[data-icon-menu]').click(function () {
			if (!$(this).hasClass('active')) {
				//target_popup.find('[name="mp_select_icon_name"]').val('');
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
		target_popup.find('.popupClose').click(function () {
			target_popup.find('[data-icon-menu="all_item"]').trigger('click');
			target_popup.find('.iconItem').removeClass('active');
		});
	});
	$(document).on('click', '.mp_add_icon_image_area .mp_icon_remove', function () {
		let parent = $(this).closest('.mp_add_icon_image_area');
		parent.find('input[type="hidden"]').val('');
		parent.find('[data-add-icon]').removeAttr('class');
		parent.find('.mp_icon_item').slideUp('fast');
		parent.find('.mp_add_icon_image_button_area').slideDown('fast');
	});
	$(document).on('keyup change', '.mp_add_icon_popup [name="mp_select_icon_name"]', function () {
		let parent = $(this).closest('.mp_add_icon_popup');
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