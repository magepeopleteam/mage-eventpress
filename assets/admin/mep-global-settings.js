/**
 * Event Manager Global Settings — Tab switching & mobile sidebar
 * Mirrors bus plugin bmGs UX (classes: mep-gs__).
 */
(function ($, mepGs) {
	'use strict';

	var tabMeta = mepGs.tabMeta || {};
	var defaultTab = mepGs.defaultTab || '';

	mepGs.switchTab = function (id, btn) {
		$('.mep-gs__tab-panel').removeClass('mep-gs--active');
		$('.mep-gs__nav-item').removeClass('mep-gs--active');
		$('#mep-tab-' + id).addClass('mep-gs--active');
		if (btn) {
			$(btn).addClass('mep-gs--active');
		} else {
			$('.mep-gs__nav-item[data-tab="' + id + '"]').addClass('mep-gs--active');
		}
		var meta = tabMeta[id] || [id, ''];
		$('#mep-topbar-title').text(meta[0] || id);
		$('#mep-topbar-sub').text(meta[1] || '');
		mepGs.closeSidebar();

		var hasForm = $('#mep-tab-' + id).find('form').length > 0;
		$('#mep-save-btn').toggle(hasForm);

		if (typeof localStorage !== 'undefined') {
			localStorage.setItem('mep_activetab', id);
		}
		if (window.history && window.history.replaceState) {
			window.history.replaceState(null, '', '#' + id);
		}
	};

	mepGs.openSidebar = function () {
		$('#mep-sidebar').addClass('mep-gs--open');
		$('#mep-overlay').addClass('mep-gs--open');
	};

	mepGs.closeSidebar = function () {
		$('#mep-sidebar').removeClass('mep-gs--open');
		$('#mep-overlay').removeClass('mep-gs--open');
	};

	$(function () {
		$('.wp-color-picker-field').wpColorPicker();

		$(document).on('click', '.mep-gs__nav-item', function (e) {
			e.preventDefault();
			var tabId = $(this).data('tab');
			if (tabId) {
				mepGs.switchTab(tabId, this);
			}
		});

		$(document).on('click', '#mep-menu-btn', function () {
			mepGs.openSidebar();
		});

		$(document).on('click', '#mep-overlay', function () {
			mepGs.closeSidebar();
		});

		$(document).on('click', '#mep-save-btn', function (e) {
			e.preventDefault();
			var $f = $('.mep-gs__tab-panel.mep-gs--active').find('form').first();
			if ($f.length) {
				HTMLFormElement.prototype.submit.call($f[0]);
			}
		});

		$('.wpsa-browse').on('click', function (event) {
			event.preventDefault();
			var self = $(this);
			var file_frame = wp.media.frames.file_frame = wp.media({
				title: self.data('uploader_title'),
				button: { text: self.data('uploader_button_text') },
				multiple: false
			});
			file_frame.on('select', function () {
				var attachment = file_frame.state().get('selection').first().toJSON();
				self.prev('.wpsa-url').val(attachment.url).change();
			});
			file_frame.open();
		});

		var startTab = defaultTab;
		if (window.location.hash) {
			var hash = window.location.hash.replace('#', '');
			if ($('#mep-tab-' + hash).length) {
				startTab = hash;
			}
		} else if (typeof localStorage !== 'undefined') {
			var stored = localStorage.getItem('mep_activetab');
			if (stored && $('#mep-tab-' + stored).length) {
				startTab = stored;
			}
		}

		if (startTab && $('#mep-tab-' + startTab).length) {
			mepGs.switchTab(startTab);
		}
	});

})(jQuery, window.mepGs = window.mepGs || {});
