function mpwem_add_loader(target) {
	target.css('position', 'relative');
	target.append('<div class="spinner_loading"><div class="icon_loader"><span class="fas fa-spinner fa-pulse"></span></div></div>');
}
function mpwem_add_loader_xs(target) {
	target.css('position', 'relative');
	target.append('<div class="spinner_loading"><div class="icon_loader_xs"><span class="fas fa-spinner fa-pulse"></span></div></div>');
}
function mpwem_remove_loader(target) {
	target.find('.spinner_loading').remove();
}
function mpwem_page_scroll_to(target) {
	jQuery('html, body').animate({
		scrollTop: target.offset().top -= 100
	}, 1000);
}
(function ($) {
	"use strict";
	let bg_image_load = false;
	load_pagination_initial_item();
	$(".filter_datepicker").datepicker({dateFormat: "mm/dd/yy"});
	$(document).ready(function () {
		$(window).on('load', function () {
			load_bg_img();
			bg_image_load = true;
		});
		if (!bg_image_load) {
			load_bg_img();
			$(document).scroll(function () {
				load_bg_img();
				bg_image_load = true;
			});
		}
	});
	let filter_input_list = {
		title_filter: 'data-title',
		filter_with_city: 'data-city-name',
		filter_with_category: 'data-category',
		filter_with_organizer: 'data-organizer'
	};
	for (let name in filter_input_list) {
		$(document).on('change keyup', '[name="' + name + '"] ', function (e) {
			e.preventDefault();
			load_filter($(this));
		});
	}
	$(document).on('change', '.search_with_start_date , .search_with_end_date', function () {
		load_filter($(this));
	});
	function load_filter(target) {
		let parent = target.closest('.list_with_filter_section');
		let result = 0;
		if (check_search_value_exit(parent)) {
			let date_check = date_filter_check(parent);
			parent.find('.all_filter_item .filter_item').each(function () {
				let active = 1;
				for (let name in filter_input_list) {
					if (single_text_check(parent, name) && active > 0) {
						active = single_text_search(parent, $(this), name);
					}
				}
				if (date_check && active > 0) {
					active = date_search(parent, $(this));				
				}
				if (active > 1) {
					result++;
					$(this).addClass('search_on').removeClass('search_of');
				} else {
					$(this).addClass('search_of').removeClass('search_on');
				}
			}).promise().done(function () {
				if (result > 0) {
					parent.find('.all_filter_item').slideDown('fast');
					parent.find('.search_result_empty').slideUp('fast');
				} else {
					parent.find('.all_filter_item').slideUp('fast');
					parent.find('.search_result_empty').slideDown('fast');
				}
			}).promise().done(function () {
				load_pagination(parent, 0);
			});
		} else {
			parent.find('.all_filter_item').slideDown('fast');
			parent.find('.all_filter_item .filter_item').each(function () {
				$(this).removeClass('search_of').removeClass('search_on');
			}).promise().done(function () {
				load_pagination(parent, 0);
			});
			parent.find('.search_result_empty').slideUp('fast');
		}
	}
	function date_convert_to_str(date) {
		date = new Date(date).getTime();
		if (date && date !== 'NaN') {
			return date;
		} else {
			return 0;
		}
	}
	function single_text_check(parent, inputName) {
		let inputText = parent.find('[name="' + inputName + '"]').val();
		return (inputText && inputText.length > 0) ? 1 : false;
	}
	function single_text_search(parent, item, inputName) {
		let target = parent.find('[name="' + inputName + '"]');
		let inputText = target.val();
		let currentValue = item.attr(filter_input_list[inputName]);
		return (currentValue && currentValue.match(new RegExp(inputText, "i"))) ? 2 : 0;
	}
	function date_filter_check(parent) {
		let start_date = date_convert_to_str(parent.find('.search_with_start_date').val());
		let end_date = date_convert_to_str(parent.find('.search_with_end_date').val());
		return (start_date > 0 && end_date > 0) ? 1 : false;
	}
	function date_search(parent, target) {
		let start_date = date_convert_to_str(parent.find('.search_with_start_date').val());
		let end_date = date_convert_to_str(parent.find('.search_with_end_date').val());
		let date = date_convert_to_str(target.attr('data-date'));
		return (date >= start_date && end_date >= date) ? 2 : 0;
	}
	function check_search_value_exit(parent) {
		let date_result = date_filter_check(parent);
		let active = 0;
		for (let name in filter_input_list) {
			if (single_text_check(parent, name)) {
				active = 1;
			}
		}
		return (date_result || active > 0) ? 1 : false;
	}
	function load_bg_img() {
		$('.filter_item:visible').each(function () {
			let target = $(this);
			if (target.find('[data-bg-image]').css('background-image') === 'none') {
				target.find('[data-bg-image]').css('background-image', 'url("' + target.find('[data-bg-image]').data('bg-image') + '")').promise().done(function () {
					mpwem_remove_loader(target);
				});
			}
		});
		return true;
	}
	//************Pagination*************//
	$(document).on('click', '.pagination_area [data-pagination]', function (e) {
		e.preventDefault();
		let pagination_page = $(this).data('pagination');
		let parent = $(this).closest('.list_with_filter_section');
		parent.find('[data-pagination]').removeClass('active_pagination');
		$(this).addClass('active_pagination').promise().done(function () {
			load_pagination(parent, pagination_page);
		}).promise().done(function () {
			mpwem_page_scroll_to(parent);
			load_bg_img();
		});
	});
	$(document).on('click', '.pagination_area .page_prev', function (e) {
		e.preventDefault();
		let parent = $(this).closest('.pagination_area');
		let page_no = parseInt(parent.find('.active_pagination').data('pagination')) - 1;
		parent.find('[data-pagination="' + page_no + '"]').trigger('click');
	});
	$(document).on('click', '.pagination_area .page_next', function (e) {
		e.preventDefault();
		let parent = $(this).closest('.pagination_area');
		let page_no = parseInt(parent.find('.active_pagination').data('pagination')) + 1;
		parent.find('[data-pagination="' + page_no + '"]').trigger('click');
	});
	$(document).on('click', '.pagination_area .pagination_load_more', function () {
		let pagination_page = parseInt($(this).attr('data-load-more'));
		let parent = $(this).closest('.list_with_filter_section');
		let item_class = get_item_class(parent);
		if (parent.find(item_class + ':hidden').length > 0) {
			pagination_page = pagination_page + 1;
		} else {
			pagination_page = 0;
		}
		$(this).attr('data-load-more', pagination_page).promise().done(function () {
			load_pagination(parent, pagination_page);
		}).promise().done(function () {
			if (parent.find(item_class + ':hidden').length === 0) {
				$(this).attr('disabled', 'disabled');
			}
		}).promise().done(function () {
			load_bg_img();
		});
	});
	function load_more_scroll(parent, pagination_page) {
		let per_page_item = parseInt(parent.find('input[name="pagination_per_page"]').val());
		let start_item = pagination_page > 0 ? pagination_page * per_page_item : 0;
		let item_class = get_item_class(parent);
		let target = parent.find(item_class + ':nth-child(' + (start_item + 1) + ')');
		mpwem_page_scroll_to(target);
	}
	function load_pagination_initial_item() {
		$('.list_with_filter_section').each(function () {
			mpwem_add_loader($(this));
			$(this).find('[data-bg-image]').each(function () {
				mpwem_add_loader($(this));
			});
			load_pagination($(this), 0);
		}).promise().done(function () {
			$('.list_with_filter_section').each(function () {
				mpwem_remove_loader($(this));
				$(this).find('.all_filter_item').css({'height': 'auto', 'overflow': 'inherit'}).slideDown('slow');
			});
		});
	}
	function load_pagination(parent, pagination_page) {
		let all_item = parent.find('.all_filter_item');
		let per_page_item = parseInt(parent.find('input[name="pagination_per_page"]').val());
		let pagination_type = parent.find('input[name="pagination_style"]').val();
		let start_item = pagination_page > 0 ? pagination_page * per_page_item : 0;
		let end_item = pagination_page > 0 ? start_item + per_page_item : per_page_item;
		let item = 0;
		let items_class = get_item_class(parent);
		if (pagination_type === 'load_more') {
			start_item = 0;
		} else {
			let all_item_height = all_item.outerHeight();
			//all_item.css({"height": all_item_height, "overflow": "hidden"});
			mpwem_add_loader(all_item);
		}
		parent.find(items_class).each(function () {
			if (item >= start_item && item < end_item) {
				if ($(this).is(':hidden')) {
					$(this).slideDown(200);
				}
			} else {
				$(this).slideUp('fast');
			}
			item++;
		}).promise().done(function () {
			all_item.css({'height': 'auto', 'overflow': 'inherit'}).promise().done(function () {
				filter_qty_palace(parent, items_class);
				pagination_management(parent, pagination_page);
				mpwem_remove_loader(all_item);
			});
		});
	}
	function pagination_management(parent, pagination_page) {
		let pagination_type = parent.find('input[name="pagination_style"]').val();
		let per_page_item = parseInt(parent.find('input[name="pagination_per_page"]').val());
		let total_item = parent.find(get_item_class(parent)).length;
		if (total_item <= per_page_item) {
			parent.find('.pagination_area').slideUp(200);
		} else {
			parent.find('.pagination_area').slideDown(200);
			if (pagination_type === 'load_more') {
				parent.find('[data-load-more]').attr('data-load-more', pagination_page)
			} else {
				pagination_page_management(parent, pagination_page);
			}
		}
	}
	function pagination_page_management(parent, pagination_page) {
		let per_page_item = parseInt(parent.find('input[name="pagination_per_page"]').val());
		let total_item = parent.find(get_item_class(parent)).length;
		let total_active_page = (total_item / per_page_item) + ((total_item % per_page_item) > 0 ? 1 : 0);
		let page_limit_start = (pagination_page > 2) ? (pagination_page - 2) : 0;
		let page_limit_end = (pagination_page > 2) ? (pagination_page + 2) : 4;
		let limit_dif = total_active_page - pagination_page;
		if (total_active_page > 5 && limit_dif < 3) {
			page_limit_start = page_limit_start - ((limit_dif > 1) ? 1 : 2);
		}
		let total_page = parent.find('[data-pagination]').length;
		for (let i = 0; i < total_page; i++) {
			if (i < total_active_page && i >= page_limit_start && i <= page_limit_end) {
				parent.find('[data-pagination="' + i + '"]').slideDown(200);
			} else {
				parent.find('[data-pagination="' + i + '"]').slideUp(200);
			}
		}
		if (pagination_page > 0) {
			parent.find('.page_prev').removeAttr('disabled');
		} else {
			parent.find('.page_prev').prop('disabled', true);
		}
		if (pagination_page > 2 && total_active_page > 5) {
			parent.find('.ellipse_left').slideDown(200);
		} else {
			parent.find('.ellipse_left').slideUp(200);
		}
		if (pagination_page < total_active_page - 3 && total_active_page > 5) {
			parent.find('.ellipse_right').slideDown(200);
		} else {
			parent.find('.ellipse_right').slideUp(200);
		}
		if (pagination_page < total_active_page - 1) {
			parent.find('.page_next').removeAttr('disabled');
		} else {
			parent.find('.page_next').prop('disabled', true);
		}
	}
	function get_item_class(parent, items = '.filter_item') {
		if (parent.find('.filter_item.search_on').length > 0 || parent.find('.filter_item.search_of').length > 0) {
			items = '.filter_item.search_on';
			parent.find('.filter_item.search_of').slideUp('fast');
		}
		return items;
	}
	function filter_qty_palace(parent, item_class) {
		parent.find('.qty_count').html($(parent).find(item_class + ':visible').length);
		parent.find('.total_filter_qty').html($(parent).find(item_class).length);
	}
}(jQuery));