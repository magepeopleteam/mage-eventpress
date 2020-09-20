jQuery(document).ready(function($) {
	"use strict";
	//-------------posttype slider----------------
	jQuery.fn.slzCom.slzexploore_core_slider();
	//------------------tool tip------------------------
	jQuery.fn.slzCom.slzexploore_core_tooltip();
	//--------------Page template & mbox------------------
	//Set link for page
	jQuery.fn.slzCom.slzexploore_core_link();
	// Show or hide metabox
	var slzPageTemplate = $("#page_template"),
		slzMboxes = $(".slz-mbox"),
		slzTabMboxes = $(".slz-tab-mbox"),
		slzCurrent,
		slzTpValues = [];
	
	function slzShow(selected) {
		var tab_active = "", active = ".slz-mbox-active-all";
		if (selected) {
			tab_active = ".slz-tab-template-"+selected;
			active += ",.slz-mbox-active-"+selected;
		}
		slzMboxes.parents(".postbox").hide();
		slzMboxes.filter(active).parents(".postbox").show();
		$(".slz-tab-mbox .slz-tab-template").addClass('hide');
		$(".slz-tab-mbox " + tab_active ).removeClass('hide');
		$(".slz-tab-mbox .slz-tab").removeClass('active');
		$(".slz-tab-mbox .tab-content").hide();
		if( selected != 'default' && $(tab_active).length > 0 ) {
			$(".slz-tab-mbox " + tab_active ).addClass('active');
			$(".slz-tab-mbox " + tab_active ).show();
		} else {
			$(".slz-tab-general").addClass('active');
			$(".slz-tab-general").show();
		}
	}
	function slzGetTpClass(template) {
		template = template.replace("page-templates/","");;
		return template.replace(".php","");
	}
	function slzGetTpValue(idx,el) {
		slzTpValues[idx]=slzGetTpClass($(el).attr("value"));
	}
	function slzTpChange(el) {
		var selected = slzGetTpClass(slzPageTemplate.val());
		if (selected != slzCurrent) {
			slzCurrent = selected;
			slzShow(selected);
		}
	}
	if (slzPageTemplate.length > 0) {
		slzPageTemplate
			.find("option")
			.each(slzGetTpValue)
			.end()
			.change(slzTpChange)
			.triggerHandler("change");
	}
	$(".slz-mbox-blog-column").change(function() {
		if( $(this).val() != '1') {
			$(".slz-mbox-blog-grid").show();
		} else {
			$(".slz-mbox-blog-grid").hide();
		}
		
	}).triggerHandler("change");
	
	//Select radio (image format)
	$('.slz-mbox-radio-row label').click(function() {
		$(this).parent().find('label').removeClass('slz-image-select-selected');
		$(this).addClass('slz-image-select-selected');
	});
	$('.slz-mbox-radio-row input').change(function() {
		if ($(this).is(':checked')) {
			$('label[for="' + $(this).attr('id') + '"]').click();
		}
	});
	// images in label ie fix
	$(document).on('click', 'label img', function() {
		$('#' + $(this).parents('label').attr('for')).click();
	});
	//Check box
	$('.slz-mbox-custom-bg-row input[type=checkbox]').click(function() {
		var divcolor = $(this).parent().parent().find('div');
		if ($(this).is(':checked')) {
			divcolor.removeClass('hide');
		} else {
			divcolor.addClass('hide');
		}
	});
	
	if(0 == $("#post-body-content > *").length) {
		$("#post-body-content").hide();
	}

	//  Tab Panel in page option
	$('.tab-list a').on('click', function(e){
		e.preventDefault();
		var tab_id = $(this).attr('href');
		var tab_content = $(this).parents('.tab-panel').find('.tab-container ' + '#' + tab_id);

		$(this).parents('.tab-list').find('li').removeClass('active');
		$(this).parent().addClass('active');

		$(this).parents('.tab-panel').find('.tab-container .tab-content.active').hide();
		tab_content.fadeIn().addClass('active');
	});
	// display / hide when default setting checkbox checked
	$('.slz-footer-option').live('click', function(){
		if ($(this).is(':checked')) {
			$("#div_slz_footer_option").addClass('hide');
		} else {
			$("#div_slz_footer_option").removeClass('hide');
		}
	});
	$('.slz-sidebar-option').live('click', function(){
		if ($(this).is(':checked')) {
			$("#div_slz_sidebar_option").addClass('hide');
		} else {
			$("#div_slz_sidebar_option").removeClass('hide');
		}
	});
	$('.slz-general-option').live('click', function(){
		if ($(this).is(':checked')) {
			$("#div_slz_general_option").addClass('hide');
		} else {
			$("#div_slz_general_option").removeClass('hide');
		}
	});
	$('.slz-header-option').live('click', function(){
		if ($(this).is(':checked')) {
			$("#div_slz_header_option").addClass('hide');
		} else {
			$("#div_slz_header_option").removeClass('hide');
		}
	});
	$('.slz-page-title-option').live('click', function(){
		if ($(this).is(':checked')) {
			$("#div_slz_page_title_option").addClass('hide');
		} else {
			$("#div_slz_page_title_option").removeClass('hide');
		}
	});
	$('.slz-mbox-header-content input').change(function() {
		if ($(this).is(':checked')) {
			if($(this).val() == "1") {
				$('.slz-mbox-header-content-slider').removeClass('hide');
				$('.slz-mbox-header-content-custom').addClass('hide');
			} else if($(this).val() == "2") {
				$('.slz-mbox-header-content-slider').addClass('hide');
				$('.slz-mbox-header-content-custom').removeClass('hide');
			} else {
				$('.slz-mbox-header-content-slider').addClass('hide');
				$('.slz-mbox-header-content-custom').addClass('hide');
			}
		}
	});
	$('.slz-mbox-show-header-top-menu select').change(function() {
		if($(this).val() == "1") {
			$('.slz-mbox-header-top-menu').removeClass('hide');
		} else {
			$('.slz-mbox-header-top-menu').addClass('hide');
		}
	});
	// Accommodation Options metabox js
	$('#slzexploore_hotel_meta_slzexploore_hotel_is_featured input').on('change', function(){
		
		if($(this).attr('id') == 'slzexploore_hotel_meta_slzexploore_hotel_is_featured_0'){
			$(this).closest('tr').next().removeClass('hide');
		}else{
			$(this).closest('tr').next().addClass('hide');
		}
	});
	$('#slz-tab-accommodation-discount .slz-show-discount').on('change', function(){
		if ($(this).is(':checked')) {
			$(this).closest('table').find("tr.discount").removeClass('hide');
		}
		else {
			$(this).closest('table').find("tr.discount").addClass('hide');
		}
	});
	$('#slz-tab-accommodation-gallery .slz-video-type').on('change', function(){
		if ($(this).val() == 'vimeo' ) {
			$(this).closest('table').find("tr.youtube-id").addClass('hide');
			$(this).closest('table').find("tr.vimeo-id").removeClass('hide');
		}
		else {
			$(this).closest('table').find("tr.vimeo-id").addClass('hide');
			$(this).closest('table').find("tr.youtube-id").removeClass('hide');
		}
	});
	// delete room type in accommodation
	$('#slz-tab-accommodation-room-types a.delete-room').on('click', function(){
		var room_type = $(this).parents('td').find('.room_type').val();
		var str_replace = room_type.replace( $(this).data('id') + ',', '' );
		$(this).parents('td').find('.room_type').val( str_replace );
		$(this).parent().remove();
	});
	// sort room type in accommodation
	if( $('#slz-tab-accommodation-room-types table td ul').length ){
		$('#slz-tab-accommodation-room-types table td ul').sortable({
			items: 'li',
			cursor: 'move',
			scrollSensitivity: 40,
			forcePlaceholderSize: true,
			forceHelperSize: false,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'wc-metabox-sortable-placeholder',
			start: function( event, ui ) {
				ui.item.css( 'background-color', '#f6f6f6' );
			},
			stop: function( event, ui ) {
				ui.item.removeAttr( 'style' );
			},
			update: function() {
				var room_ids = '';
				$( '#slz-tab-accommodation-room-types table td ul li' ).each( function() {
					var room_id = jQuery( this ).find('a.delete-room').attr( 'data-id' );
					room_ids = room_ids + room_id + ',';
				});
				$( '#slz-tab-accommodation-room-types table td .room_type' ).val( room_ids );
			}
		});
	}
	// Tour Options metabox js
	$('#slzexploore_tour_meta_slzexploore_tour_is_featured input').on('change', function(){
		
		if($(this).attr('id') == 'slzexploore_tour_meta_slzexploore_tour_is_featured_0'){
			$(this).closest('tr').next().removeClass('hide');
		}else{
			$(this).closest('tr').next().addClass('hide');
		}
	});
	$('#slz-tab-tour-general .slz-date-type, #slz-tab-cruise-general .slz-date-type').each(function() {
		if( $(this).is(':checked') && $(this).val() != '1' ){
			$(this).closest('table').find("tr.fixed").addClass('hide2');
		}
	});
	$('#slz-tab-tour-general .slz-date-type, #slz-tab-cruise-general .slz-date-type').on('change', function(){
		if ( $(this).val() == 1 ) {
			$(this).closest('table').find("tr.fixed").removeClass('hide2');
		}
		else {
			$(this).closest('table').find("tr.fixed").addClass('hide2');
		}
	});
	$('#slz-tab-tour-general .slz-date-frequency, #slz-tab-cruise-general .slz-date-frequency').each(function() {
		if( $(this).is(':checked') && $(this).val() == 'monthly' ){
			$(this).closest('table').find("tr.weekly").addClass('hide');
			$(this).closest('table').find("tr.monthly").removeClass('hide');
			$(this).closest('table').find("tr.fixed-date").addClass('hide');
		}
		else if( $(this).is(':checked') && ( $(this).val() == 'other' || $(this).val() == 'season' ) ){
			$(this).closest('table').find("tr.weekly").addClass('hide');
			$(this).closest('table').find("tr.monthly").addClass('hide');
			$(this).closest('table').find("tr.fixed-date").removeClass('hide');
		}
	});
	$('#slz-tab-tour-general .slz-date-frequency, #slz-tab-cruise-general .slz-date-frequency').on('change', function(){
		if ( $(this).val() == 'weekly' ) {
			$(this).closest('table').find("tr.weekly").removeClass('hide');
			$(this).closest('table').find("tr.monthly").addClass('hide');
			$(this).closest('table').find("tr.fixed-date").addClass('hide');
		}
		else if( $(this).val() == 'monthly' ){
			$(this).closest('table').find("tr.weekly").addClass('hide');
			$(this).closest('table').find("tr.monthly").removeClass('hide');
			$(this).closest('table').find("tr.fixed-date").addClass('hide');
		}
		else {
			
			$(this).closest('table').find("tr.weekly").addClass('hide');
			$(this).closest('table').find("tr.monthly").addClass('hide');
			$(this).closest('table').find("tr.fixed-date").removeClass('hide');
		}
	});
	// Car Options metabox js
	$('#slzexploore_car_meta_slzexploore_car_is_featured input').on('change', function(){
		
		if($(this).attr('id') == 'slzexploore_car_meta_slzexploore_car_is_featured_0'){
			$(this).closest('tr').next().removeClass('hide');
		}else{
			$(this).closest('tr').next().addClass('hide');
		}
	});
	$('#slz-tab-car-discount .slz-show-discount').on('change', function(){
		if ($(this).is(':checked')) {
			$(this).closest('table').find("tr.discount").removeClass('hide');
		}
		else {
			$(this).closest('table').find("tr.discount").addClass('hide');
		}
	});
	// delete cabin type in cruises
	$('#slzexploore_cruise_meta_slzexploore_cruise_is_featured input').on('change', function(){
		
		if($(this).attr('id') == 'slzexploore_cruise_meta_slzexploore_cruise_is_featured_0'){
			$(this).closest('tr').next().removeClass('hide');
		}else{
			$(this).closest('tr').next().addClass('hide');
		}
	});
	$('#slz-tab-cabin-type a.delete-cabin').on('click', function(){
		var cabin_type = $(this).parents('td').find('.cabin_type').val();
		var str_replace = cabin_type.replace( $(this).data('id') + ',', '' );
		$(this).parents('td').find('.cabin_type').val( str_replace );
		$(this).parent().remove();
	});
	
	//--------------End page template & mbox------- 
	//--------------Pricing Table << ------------------
	var slzPricingTable     = $(".slz-pricing-table"),
		slzPricingItemDel   = $(".slz-custom-meta .pricing-item-remove"),
		slzPricingItemAdd   = $(".slz-custom-meta .pricing-item-add"),
		slzPricingItemClone = $(".slz-pricing-item-clone"),
		slzPricingAddRow    = $(".slz-custom-meta .pricing-row-add"),
		slzPricingDelRow    = $(".slz-custom-meta .pricing-row-remove"),
		slzPricingRowClone  = $(".slz-pricing-row-clone"),
		slzHidPricingItem   = $("#slz_hid_pricing_item");
	
	// Del Pricing Item
	slzPricingItemDel.live('click', function() {
		$(this).parent().remove();
	});
	// Add Pricing Item
	slzPricingItemAdd.live('click', function() {
		var regEx  = new RegExp("pricing_item","g"),
			itemID,
			itemName,
			newItem;
		itemID = jQuery.fn.slzCom.cnvInt( $(this).attr("data-item") ) + 1;
		// change item name
		newItem = slzPricingItemClone.html().replace( regEx, itemID );
		// change item id
		regEx = new RegExp("slz_pricing_meta_id","g");
		newItem = newItem.replace( regEx, "slz_pricing_meta_"+itemID );
		slzPricingTable.append(newItem);
		$(this).attr("data-item", itemID);
		// reload meta color
		slzPricingTable.find(".slz-color").addClass( jQuery.fn.slzCom.colorCss );
		jQuery.fn.slzCom.reloadMetaColor();
		
	});
	// Add Pricing Feature Row
	slzPricingAddRow.live('click', function() {
		var regEx  = new RegExp("pricing_item","g"),
			itemId = $(this).attr("data-item"),
			rowId = jQuery.fn.slzCom.cnvInt( $(this).attr("data-row") ) + 1,
			newRow;
		// change item name
		newRow = slzPricingRowClone.html().replace( regEx, itemId );
		// change row id
		regEx = new RegExp("feature_row","g");
		newRow = newRow.replace( regEx, rowId );
		$(this).attr("data-row", rowId);
		$(this).parent().find( '.slz-pricing-content' ).append( newRow );
	});
	// Del Pricing Feature Row
	slzPricingDelRow.live('click', function() {
		$(this).parent().remove();
	});
	$('.slz-pricing-icon label').live('click', function() {
		$(this).parent().find('label').removeClass('slz-icon-selected');
		$(this).addClass('slz-icon-selected');
	});
	$('.slz-pricing-icon input').live('change', function() {
		if ($(this).is(':checked')) {
			$('label[for="' + $(this).attr('id') + '"]').click();
		}
	});
	//--------------Pricing Table >> ------------------
	//video post type
	$("#slzexploore_core_mbox_video_type").change(function(){
		if ( $(this).val() === 'vimeo'){
			$(this).parents('.slz-video-meta').find('.vimeo-id').addClass('active');
			$(this).parents('.slz-video-meta').find('.video_upload').removeClass('active');
			$(this).parents('.slz-video-meta').find('.youtube-id').removeClass('active');
			$(this).parents('.slz-video-meta').find('.video-option').addClass('active');
			$(this).parents('.slz-video-meta').find('.video-option').find('.hide-control').addClass('hide');
		}
		else if ( $(this).val() === 'youtube'){
			$(this).parents('.slz-video-meta').find('.youtube-id').addClass('active');
			$(this).parents('.slz-video-meta').find('.video_upload').removeClass('active');
			$(this).parents('.slz-video-meta').find('.vimeo-id').removeClass('active');
			$(this).parents('.slz-video-meta').find('.video-option').addClass('active');
			$(this).parents('.slz-video-meta').find('.video-option').find('.hide-control').removeClass('hide');
		}
		else if( $(this).val() === 'video-upload'){
			$(this).parents('.slz-video-meta').find('.video_upload').addClass('active');
			$(this).parents('.slz-video-meta').find('.vimeo-id').removeClass('active');
			$(this).parents('.slz-video-meta').find('.youtube-id').removeClass('active');
			$(this).parents('.slz-video-meta').find('.video-option').addClass('active');
		}
		else{
			$(this).parents('.slz-video-meta').find('.vimeo-id').removeClass('active');
			$(this).parents('.slz-video-meta').find('.youtube-id').removeClass('active');
			$(this).parents('.slz-video-meta').find('.video_upload').removeClass('active');
			$(this).parents('.slz-video-meta').find('.video-option').removeClass('active');
		}
	})
	
	if($('.slz_upload_button').length ) {
		window.uploadfield = '';
		$('.slz_upload_button').live('click', function() {
			window.uploadfield = $('.textbox-url-video', $(this).parents( '.upload' ));
			tb_show('Upload', 'media-upload.php?type=image&TB_iframe=true', false);
			return false;
		});
		window.send_to_editor_backup = window.send_to_editor;
		window.send_to_editor = function(html) {
			if(window.uploadfield) {
				if($('img', html).length >= 1) {
					var image_url = $('img', html).attr('src');
				} else {
					var image_url = $($(html)[0]).attr('href');
				}
				$(window.uploadfield).val(image_url);
				window.uploadfield = '';
				tb_remove();
			} else {
				window.send_to_editor_backup(html);
			}
		}
	}
	//upload video in post and post type video
	if ( $('#slzexploore_core_mbox_video_type').val() === 'video-upload' ){
		$('.slz-video-meta').find('.video_upload').addClass('active');
	}
	// Vacancy Metabox
	$('.slz-vacancy-options .slz-accommodation-id, #slz-tab-accommodation-booking .slz-accommodation-id').on('change', function(){
		var $this = $(this);
		$this.closest('table').find('td.slz-room-type select').attr('disabled', 'true');
		var accommodation_id = $(this).val();
		$.fn.Form.ajax(['posttype.Vacancy_Controller', 'ajax_get_room_type'], {'accommodation_id' : accommodation_id}, function(res) {
				$this.closest('table').find('td.slz-room-type').html(res);
				$this.closest('table').find('td.slz-room-type select').removeAttr('disabled');
		});
	});
	// Tour metabox
	function slz_check_show_discount() {
		if ($('#slz-tab-tour-general .tour_show_discount').is(':checked')) {
			$('.check-tour_show_discount').fadeIn();
		} else {
			$('.check-tour_show_discount').fadeOut();
		}
		if ($('#slz-tab-cruise-general .cruise_show_discount').is(':checked')) {
			$('.check-cruise_show_discount').fadeIn();
		} else {
			$('.check-cruise_show_discount').fadeOut();
		}
	}
	$('#slz-tab-tour-general .tour_show_discount, #slz-tab-cruise-general .cruise_show_discount').click(function() {
		slz_check_show_discount();
	});
	slz_check_show_discount();
});