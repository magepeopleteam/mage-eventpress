/**
 * Event Manager Global Settings — Tab switching & mobile sidebar
 * Mirrors bus plugin bmGs UX (classes: mep-gs__).
 */
(function ($, mepGs) {
	'use strict';

	var tabMeta = mepGs.tabMeta || {};
	var defaultTab = mepGs.defaultTab || '';
	var emailParent = mepGs.emailParent || 'email_setting_sec';
	var emailSubtabs = mepGs.emailSubtabs || [];
	var emailSubMeta = mepGs.emailSubMeta || {};

	function isEmailChild(id) {
		return emailSubtabs.indexOf(id) !== -1 && id !== emailParent;
	}

	mepGs.switchEmailSub = function (subId) {
		if (!subId || !$('#mep-email-sub-' + subId).length) {
			return;
		}

		$('.mep-gs__email-subnav-btn').removeClass('mep-gs--active').attr('aria-selected', 'false');
		$('.mep-gs__email-subpanel').removeClass('mep-gs--active');

		$('.mep-gs__email-subnav-btn[data-email-sub="' + subId + '"]')
			.addClass('mep-gs--active')
			.attr('aria-selected', 'true');
		$('#mep-email-sub-' + subId).addClass('mep-gs--active');

		var label = emailSubMeta[subId] || subId;
		var parentMeta = tabMeta[emailParent] || ['Email Settings', ''];
		$('#mep-topbar-title').text(parentMeta[0] || 'Email Settings');
		$('#mep-topbar-sub').text(label);

		var hasForm = $('#mep-email-sub-' + subId).find('form').length > 0;
		$('#mep-save-btn').toggle(hasForm);

		if (typeof localStorage !== 'undefined') {
			localStorage.setItem('mep_email_subtab', subId);
		}
		if (window.history && window.history.replaceState) {
			window.history.replaceState(null, '', '#' + subId);
		}
	};

	mepGs.switchTab = function (id, btn) {
		var targetTab = id;
		var targetSub = null;

		// Old bookmarks / localStorage for PDF / Waitlist email tabs.
		if (isEmailChild(id)) {
			targetTab = emailParent;
			targetSub = id;
		}

		$('.mep-gs__tab-panel').removeClass('mep-gs--active');
		$('.mep-gs__nav-item').removeClass('mep-gs--active');
		$('#mep-tab-' + targetTab).addClass('mep-gs--active');
		if (btn && !isEmailChild(id)) {
			$(btn).addClass('mep-gs--active');
		} else {
			$('.mep-gs__nav-item[data-tab="' + targetTab + '"]').addClass('mep-gs--active');
		}

		var meta = tabMeta[targetTab] || [targetTab, ''];
		$('#mep-topbar-title').text(meta[0] || targetTab);
		$('#mep-topbar-sub').text(meta[1] || '');
		mepGs.closeSidebar();

		if (targetTab === emailParent && $('#mep-tab-' + emailParent + ' .mep-gs__email-subnav').length) {
			var sub = targetSub;
			if (!sub && typeof localStorage !== 'undefined') {
				sub = localStorage.getItem('mep_email_subtab');
			}
			if (!sub || !$('#mep-email-sub-' + sub).length) {
				sub = emailSubtabs[0] || emailParent;
			}
			mepGs.switchEmailSub(sub);
		} else {
			var hasForm = $('#mep-tab-' + targetTab).find('form').length > 0;
			$('#mep-save-btn').toggle(hasForm);

			if (typeof localStorage !== 'undefined') {
				localStorage.setItem('mep_activetab', targetTab);
			}
			if (window.history && window.history.replaceState) {
				window.history.replaceState(null, '', '#' + targetTab);
			}
		}

		if (typeof localStorage !== 'undefined') {
			localStorage.setItem('mep_activetab', targetTab);
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

		$(document).on('click', '.mep-gs__email-subnav-btn', function (e) {
			e.preventDefault();
			var subId = $(this).data('email-sub');
			if (subId) {
				mepGs.switchEmailSub(subId);
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
			var $panel = $('.mep-gs__tab-panel.mep-gs--active');
			var $sub = $panel.find('.mep-gs__email-subpanel.mep-gs--active');
			var $f = $sub.length
				? $sub.find('form').first()
				: $panel.find('form').first();
			if ($f.length) {
				if (typeof tinymce !== 'undefined' && tinymce.triggerSave) {
					tinymce.triggerSave();
				}
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
			if ($('#mep-tab-' + hash).length || isEmailChild(hash) || $('#mep-email-sub-' + hash).length) {
				startTab = hash;
			}
		} else if (typeof localStorage !== 'undefined') {
			var stored = localStorage.getItem('mep_activetab');
			if (stored && ($('#mep-tab-' + stored).length || isEmailChild(stored))) {
				startTab = stored;
			}
		}

		if (startTab) {
			mepGs.switchTab(startTab);
		}
	});

})(jQuery, window.mepGs = window.mepGs || {});
