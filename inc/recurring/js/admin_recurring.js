(function ($) {
	"use strict";
	//=========collape sp date==============//
	$(document).ready(function () {
		$('#mp_event_all_info_in_tab [data-tab-item="#mp_event_time"] .wrap.ppof-settings.ppof-metabox .section-title').each(function (){
			$(this).trigger('click');
		});
	});
	$(document).on('click', '#mp_event_all_info_in_tab [data-tab-item="#mp_event_time"] .wrap.ppof-settings.ppof-metabox .section-title', function () {
		let parent=$(this).closest('.section');
		parent.find('.form-table').slideToggle(250);
		$(this).toggleClass('close_settings');
	});
}(jQuery));