/**
 * Event Manager Global Settings — Tab switching & mobile sidebar
 * Mirrors bus plugin bmGs UX (classes: mep-gs__).
 * Includes Email Settings hub, Style & Icon hub, and Slider & Carousel hub.
 */
(function ($, mepGs) {
	'use strict';

	var tabMeta = mepGs.tabMeta || {};
	var defaultTab = mepGs.defaultTab || '';
	var emailParent = mepGs.emailParent || 'email_setting_sec';
	var emailSubtabs = mepGs.emailSubtabs || [];
	var emailSubMeta = mepGs.emailSubMeta || {};
	var styleParent = mepGs.styleParent || 'style_setting_sec';
	var styleSubtabs = mepGs.styleSubtabs || [];
	var sliderParent = mepGs.sliderParent || 'mp_slider_settings';
	var sliderSubtabs = mepGs.sliderSubtabs || [];
	var licenseParent = mepGs.licenseParent || 'mep_settings_licensing';
	var licenseSubtabs = mepGs.licenseSubtabs || [];
	var testEmail = mepGs.testEmail || {};
	var wlEditorMap = {
		admin: 'mep_waitlist_email_settings-mep_waitlist_email_template',
		spot: 'mep_waitlist_email_settings-mep_waitlist_spot_available_template',
		customer: 'mep_waitlist_email_settings-mep_waitlist_customer_email_template'
	};

	function isEmailChild(id) {
		return emailSubtabs.indexOf(id) !== -1 && id !== emailParent;
	}

	function isStyleChild(id) {
		return styleSubtabs.indexOf(id) !== -1 && id !== styleParent;
	}

	function isSliderChild(id) {
		return sliderSubtabs.indexOf(id) !== -1 && id !== sliderParent;
	}

	function isLicenseChild(id) {
		return licenseSubtabs.indexOf(id) !== -1 && id !== licenseParent;
	}

	function isNestedChild(id) {
		return isEmailChild(id) || isStyleChild(id) || isSliderChild(id) || isLicenseChild(id);
	}

	function getActiveEmailSub() {
		var $active = $('.mep-gs__email-subpanel.mep-gs--active, .mep-em__panel.mep-em--active[data-email-sub]').first();
		return $active.data('email-sub') || emailSubtabs[0] || emailParent;
	}

	function getEditorContent(editorId) {
		if (typeof tinymce !== 'undefined') {
			var ed = tinymce.get(editorId);
			if (ed && !ed.isHidden()) {
				return ed.getContent();
			}
		}
		var $ta = $('#' + editorId);
		return $ta.length ? $ta.val() : '';
	}

	function insertIntoEditor(editorId, text) {
		if (typeof tinymce !== 'undefined') {
			var ed = tinymce.get(editorId);
			if (ed && !ed.isHidden()) {
				ed.focus();
				ed.execCommand('mceInsertContent', false, text);
				return true;
			}
		}
		var $ta = $('#' + editorId);
		if ($ta.length) {
			var val = $ta.val() || '';
			var el = $ta[0];
			var start = el.selectionStart || val.length;
			var end = el.selectionEnd || val.length;
			$ta.val(val.substring(0, start) + text + val.substring(end));
			$ta.trigger('change');
			return true;
		}
		return false;
	}

	function insertIntoTextarea($ta, text) {
		if (!$ta || !$ta.length) {
			return;
		}
		var el = $ta[0];
		var val = $ta.val() || '';
		var start = typeof el.selectionStart === 'number' ? el.selectionStart : val.length;
		var end = typeof el.selectionEnd === 'number' ? el.selectionEnd : val.length;
		$ta.val(val.substring(0, start) + text + val.substring(end)).focus();
		if (el.setSelectionRange) {
			var pos = start + text.length;
			el.setSelectionRange(pos, pos);
		}
		$ta.trigger('change');
	}

	mepGs.switchEmailSub = function (subId) {
		if (!subId || !$('#mep-email-sub-' + subId).length) {
			return;
		}

		var $hub = $('#mep-tab-' + emailParent);
		$hub.find('.mep-gs__email-subnav-btn, .mep-em__tab[data-email-sub]').removeClass('mep-gs--active mep-em--active').attr('aria-selected', 'false');
		$hub.find('.mep-gs__email-subpanel, .mep-em__panel[data-email-sub]').removeClass('mep-gs--active mep-em--active');

		$hub.find('.mep-em__tab[data-email-sub="' + subId + '"], .mep-gs__email-subnav-btn[data-email-sub="' + subId + '"]')
			.addClass('mep-gs--active mep-em--active')
			.attr('aria-selected', 'true');
		$('#mep-email-sub-' + subId).addClass('mep-gs--active mep-em--active');

		var label = emailSubMeta[subId] || subId;
		var parentMeta = tabMeta[emailParent] || ['Email Settings', ''];
		$('#mep-topbar-title').text(parentMeta[0] || 'Email Settings');
		$('#mep-topbar-sub').text(label);

		var hasForm = $('#mep-email-sub-' + subId).find('form').length > 0;
		$('#mep-save-btn').toggle(hasForm);

		// Refresh TinyMCE after the panel becomes visible (hidden editors often fail to paint).
		window.setTimeout(function () {
			if (typeof mepGs.refreshEmailEditors === 'function') {
				mepGs.refreshEmailEditors();
			}
		}, 60);

		if (typeof localStorage !== 'undefined') {
			localStorage.setItem('mep_email_subtab', subId);
			localStorage.setItem('mep_activetab', emailParent);
		}
		if (window.history && window.history.replaceState) {
			window.history.replaceState(null, '', '#' + subId);
		}
	};

	mepGs.saveMultiOptionForms = function ($forms, done) {
		var list = $forms.toArray();
		var i = 0;

		function next() {
			if (i >= list.length) {
				if (typeof done === 'function') {
					done();
				}
				return;
			}
			var form = list[i++];
			var action = form.getAttribute('action') || 'options.php';
			var fd = new FormData(form);
			fetch(action, {
				method: 'POST',
				body: fd,
				credentials: 'same-origin',
				redirect: 'follow'
			}).then(function () {
				next();
			}).catch(function () {
				next();
			});
		}

		next();
	};

	// Back-compat alias used by Style & Icon.
	mepGs.saveStyleIconForms = mepGs.saveMultiOptionForms;

	mepGs.switchPaymentSub = function (subId) {
		if (!subId || !$('#mep-pay-sub-' + subId).length) {
			return;
		}
		var $hub = $('#mep-tab-payment_setting_sec');
		$hub.find('.mep-pay__tab').removeClass('mep-pay--active').attr('aria-selected', 'false');
		$hub.find('.mep-pay__panel').removeClass('mep-pay--active');

		$hub.find('.mep-pay__tab[data-pay-sub="' + subId + '"]')
			.addClass('mep-pay--active')
			.attr('aria-selected', 'true');
		$('#mep-pay-sub-' + subId).addClass('mep-pay--active');

		var label = $hub.find('.mep-pay__tab[data-pay-sub="' + subId + '"]').data('pay-label') || subId;
		var parentMeta = tabMeta.payment_setting_sec || ['Payment', ''];
		$('#mep-topbar-title').text(parentMeta[0] || 'Payment');
		$('#mep-topbar-sub').text(label);

		if (typeof localStorage !== 'undefined') {
			localStorage.setItem('mep_payment_subtab', subId);
			localStorage.setItem('mep_activetab', 'payment_setting_sec');
		}
	};

	mepGs.switchTab = function (id, btn) {
		var targetTab = id;
		var targetSub = null;
		var hubKind = null;

		if (isEmailChild(id) || id === emailParent || (emailSubtabs.indexOf(id) !== -1)) {
			if (isEmailChild(id) || (id !== emailParent && emailSubtabs.indexOf(id) !== -1)) {
				targetTab = emailParent;
				targetSub = id;
			} else if (id === emailParent) {
				targetTab = emailParent;
			}
			hubKind = 'email';
		} else if (isStyleChild(id) || id === styleParent || (styleSubtabs.indexOf(id) !== -1)) {
			targetTab = styleParent;
			hubKind = 'style';
		} else if (isSliderChild(id) || id === sliderParent || (sliderSubtabs.indexOf(id) !== -1)) {
			targetTab = sliderParent;
			hubKind = 'slider';
		} else if (isLicenseChild(id) || id === licenseParent || (licenseSubtabs.indexOf(id) !== -1)) {
			targetTab = licenseParent;
			hubKind = 'license';
		}

		$('.mep-gs__tab-panel').removeClass('mep-gs--active');
		$('.mep-gs__nav-item').removeClass('mep-gs--active');
		$('#mep-tab-' + targetTab).addClass('mep-gs--active');
		if (btn && !isNestedChild(id)) {
			$(btn).addClass('mep-gs--active');
		} else {
			$('.mep-gs__nav-item[data-tab="' + targetTab + '"]').addClass('mep-gs--active');
		}

		var meta = tabMeta[targetTab] || [targetTab, ''];
		$('#mep-topbar-title').text(meta[0] || targetTab);
		$('#mep-topbar-sub').text(meta[1] || '');
		mepGs.closeSidebar();

		if (hubKind === 'email' && $('#mep-tab-' + emailParent + ' .mep-em, #mep-tab-' + emailParent + ' .mep-gs__email-subnav').length) {
			var sub = targetSub;
			if (!sub && typeof localStorage !== 'undefined') {
				sub = localStorage.getItem('mep_email_subtab');
			}
			if (!sub || !$('#mep-email-sub-' + sub).length) {
				sub = emailSubtabs[0] || emailParent;
			}
			mepGs.switchEmailSub(sub);
		} else if (targetTab === 'payment_setting_sec' && $('#mep-tab-payment_setting_sec .mep-pay').length) {
			var paySub = null;
			if (typeof localStorage !== 'undefined') {
				paySub = localStorage.getItem('mep_payment_subtab');
			}
			if (!paySub || !$('#mep-pay-sub-' + paySub).length) {
				paySub = 'woocommerce';
			}
			mepGs.switchPaymentSub(paySub);
			var hasForm = $('#mep-tab-' + targetTab).find('form').length > 0;
			$('#mep-save-btn').toggle(hasForm);
			if (typeof localStorage !== 'undefined') {
				localStorage.setItem('mep_activetab', targetTab);
			}
			if (window.history && window.history.replaceState) {
				window.history.replaceState(null, '', '#' + targetTab);
			}
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

	function openTestModal() {
		var type = getActiveEmailSub();
		$('#mep-em-wl-type-wrap').toggle(type === 'mep_waitlist_email_settings');
		$('#mep-em-test-status').prop('hidden', true).removeClass('mep-em--ok mep-em--err').text('');
		$('#mep-em-test-modal').addClass('mep-em--open').attr('aria-hidden', 'false');
		$('#mep-em-test-to').trigger('focus');
	}

	function closeTestModal() {
		$('#mep-em-test-modal').removeClass('mep-em--open').attr('aria-hidden', 'true');
	}

	function collectTestPayload() {
		var type = getActiveEmailSub();
		var $panel = $('#mep-email-sub-' + type);
		var $form = $panel.find('form').first();
		var payload = {
			action: 'mep_send_test_email',
			nonce: testEmail.nonce || '',
			to: $('#mep-em-test-to').val(),
			type: type,
			wl_type: $('#mep-em-wl-type').val() || 'admin',
			from_name: $form.find('[data-em-field="from_name"]').val() || '',
			from_email: $form.find('[data-em-field="from_email"]').val() || '',
			subject: '',
			body: ''
		};

		if (type === 'email_setting_sec') {
			payload.subject = $form.find('[data-em-field="subject"]').val() || '';
			payload.body = getEditorContent('email_setting_sec-mep_confirmation_email_text');
		} else if (type === 'mep_pdf_email_settings') {
			payload.subject = $form.find('[data-em-field="subject"]').val() || '';
			payload.body = getEditorContent('mep_pdf_email_settings-mep_pdf_email_content');
		} else if (type === 'mep_waitlist_email_settings') {
			var wl = payload.wl_type;
			payload.subject = $form.find('[data-em-wl-type="' + wl + '"]').filter('input').first().val() || '';
			payload.body = getEditorContent(wlEditorMap[wl] || wlEditorMap.admin);
		}

		return payload;
	}

	function sendTestEmail() {
		var $btn = $('#mep-em-test-send');
		var $status = $('#mep-em-test-status');
		var payload = collectTestPayload();

		$status.prop('hidden', false).removeClass('mep-em--ok mep-em--err')
			.text((testEmail.i18n && testEmail.i18n.sending) || 'Sending…');
		$btn.prop('disabled', true);

		$.post(testEmail.ajaxUrl || ajaxurl, payload)
			.done(function (res) {
				if (res && res.success) {
					$status.addClass('mep-em--ok').text(res.data && res.data.message ? res.data.message : 'Sent.');
				} else {
					var msg = (res && res.data && res.data.message) ? res.data.message : ((testEmail.i18n && testEmail.i18n.error) || 'Error');
					$status.addClass('mep-em--err').text(msg);
				}
			})
			.fail(function () {
				$status.addClass('mep-em--err').text((testEmail.i18n && testEmail.i18n.error) || 'Error');
			})
			.always(function () {
				$btn.prop('disabled', false);
			});
	}

	$(function () {
		function syncSiPreview() {
			var $preview = $('#mep-si-color-preview');
			if (!$preview.length) {
				return;
			}
			var primary = $('[data-mep-si-color="mpev_primary_color"]').val() || '#6046ff';
			var secondary = $('[data-mep-si-color="mpev_secondary_color"]').val() || '#f1f5ff';
			$preview.css({
				'--mep-si-primary': primary,
				'--mep-si-secondary': secondary
			});
		}

		function syncSiSwatch($input) {
			var val = $input.val() || $input.data('default-color') || '#000000';
			$input.closest('[data-mep-si-picker]').find('.mep-si__color-swatch').css('background-color', val);
			syncSiPreview();
		}

		function initSiColorPickers() {
			if (typeof $.fn.iris !== 'function') {
				return;
			}
			$('.mep-si__color-hex').each(function () {
				var $input = $(this);
				if ($input.data('mepSiIris')) {
					return;
				}
				$input.data('mepSiIris', true);
				$input.iris({
					width: 220,
					hide: true,
					palettes: true,
					change: function () {
						syncSiSwatch($input);
					}
				});
				syncSiSwatch($input);
			});
		}

		function toggleSiIris($control, forceShow) {
			var $input = $control.find('.mep-si__color-hex');
			if (!$input.length || typeof $input.iris !== 'function') {
				return;
			}
			var show = typeof forceShow === 'boolean' ? forceShow : !$control.hasClass('mep-si--open');
			$('.mep-si__color-control.mep-si--open').not($control).each(function () {
				var $other = $(this);
				$other.removeClass('mep-si--open');
				$other.find('.mep-si__color-hex').iris('hide');
			});
			if (show) {
				$control.addClass('mep-si--open');
				$input.iris('show');
			} else {
				$control.removeClass('mep-si--open');
				$input.iris('hide');
			}
		}

		$('.wp-color-picker-field').wpColorPicker({
			change: function () {
				var $el = $(this);
				window.setTimeout(function () {
					syncSiPreview();
					if ($el.hasClass('mep-pdf__color-hex') || $el.closest('[data-mep-pdf-color]').length) {
						var $wrap = $el.closest('[data-mep-pdf-color]');
						var val = $el.val() || $el.data('default-color') || '#FFFFFF';
						$wrap.find('.mep-pdf__color-swatch').css('background-color', val);
					}
					if ($el.hasClass('mep-ssc__color-hex') || $el.closest('[data-mep-ssc-color]').length) {
						var $sscWrap = $el.closest('[data-mep-ssc-color]');
						var sscVal = $el.val() || $el.data('default-color') || '#111827';
						$sscWrap.find('.mep-ssc__color-swatch').css('background-color', sscVal);
						if (typeof window.mepSscRefreshPreview === 'function') {
							window.mepSscRefreshPreview();
						}
					}
				}, 10);
			},
			clear: function () {
				window.setTimeout(function () {
					syncSiPreview();
					if (typeof window.mepSscRefreshPreview === 'function') {
						window.mepSscRefreshPreview();
					}
				}, 10);
			}
		});
		initSiColorPickers();
		syncSiPreview();

		$(document).on('click', '.mep-si__color-swatch, .mep-si__color-edit', function (e) {
			e.preventDefault();
			e.stopPropagation();
			toggleSiIris($(this).closest('[data-mep-si-picker]'));
		});
		$(document).on('focus', '.mep-si__color-hex', function () {
			toggleSiIris($(this).closest('[data-mep-si-picker]'), true);
		});
		$(document).on('change input', '.mep-si__color-hex', function () {
			syncSiSwatch($(this));
		});
		$(document).on('click', function (e) {
			if (!$(e.target).closest('[data-mep-si-picker], .iris-picker').length) {
				$('.mep-si__color-control.mep-si--open').each(function () {
					var $c = $(this);
					$c.removeClass('mep-si--open');
					$c.find('.mep-si__color-hex').iris('hide');
				});
			}
		});

		$(document).on('click', '.mep-gs__nav-item', function (e) {
			e.preventDefault();
			var tabId = $(this).data('tab');
			if (tabId) {
				mepGs.switchTab(tabId, this);
			}
		});

		$(document).on('click', '.mep-gs__email-subnav-btn, .mep-em__tab[data-email-sub]', function (e) {
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
			var $multiForms = $panel.find('form.mep-si__form, form.mep-sc__form');

			// Style & Icon / Slider & Carousel have multiple option groups — save all, then reload.
			if ($multiForms.length > 1) {
				var $btn = $(this);
				var tabId = $panel.attr('id') ? $panel.attr('id').replace(/^mep-tab-/, '') : '';
				$btn.addClass('mep-gs--saving');
				mepGs.saveMultiOptionForms($multiForms, function () {
					var base = window.location.pathname + window.location.search;
					base = base.replace(/([?&])settings-updated=[^&]*/g, '$1').replace(/[?&]$/, '').replace(/\?&/, '?');
					base += (base.indexOf('?') === -1 ? '?' : '&') + 'settings-updated=true';
					window.location.href = base + '#' + (tabId || styleParent);
				});
				return;
			}

			var $sub = $panel.find('.mep-gs__email-subpanel.mep-gs--active, .mep-em__panel.mep-em--active, .mep-gs__hub-subpanel.mep-gs--active').first();
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

	var lastTa = null;
	var lastWlEditor = wlEditorMap.admin;

	function relocateEditorModeTabs() {
		$('.mep-em__mode-tabs[data-editor-tabs-for]').each(function () {
			var $slot = $(this);
			if ($slot.children().length) {
				return;
			}
			var editorId = $slot.data('editor-tabs-for');
			var $wrap = $('#wp-' + editorId + '-wrap');
			var $tabs = $wrap.find('.wp-editor-tabs').first();
			if (!$tabs.length) {
				// Tabs may still be under .wp-editor-tools when tools were hidden via CSS.
				$tabs = $wrap.find('.wp-editor-tools .wp-editor-tabs').first();
			}
			if ($tabs.length) {
				$slot.append($tabs);
			}
			syncEditorModeClass($wrap.closest('.mep-em__field--editor'), $wrap);
		});
	}

	function syncEditorModeClass($field, $wrap) {
		if (!$field.length || !$wrap.length) {
			return;
		}
		$field.toggleClass('mep-em--mode-visual', $wrap.hasClass('tmce-active'));
		$field.toggleClass('mep-em--mode-html', $wrap.hasClass('html-active'));
	}

	mepGs.refreshEmailEditors = function () {
		relocateEditorModeTabs();
		if (typeof tinymce === 'undefined') {
			return;
		}
		$('.mep-em__panel.mep-em--active .mep-em__editor textarea.wp-editor-area').each(function () {
			var id = this.id;
			if (!id) {
				return;
			}
			try {
				var ed = tinymce.get(id);
				if (ed) {
					ed.show();
					ed.fire('show');
					if (typeof ed.nodeChanged === 'function') {
						ed.nodeChanged();
					}
					// Fix zero-height iframe after being off-screen.
					var iframe = ed.getContentAreaContainer() && ed.getContentAreaContainer().querySelector('iframe');
					if (iframe && iframe.clientHeight < 50) {
						$(iframe).css('height', '200px');
					}
				} else if (window.wp && wp.editor && typeof wp.editor.initialize === 'function') {
					// Editor never booted (common when first painted off-screen).
					wp.editor.remove(id);
					wp.editor.initialize(id, {
						tinymce: {
							wpautop: true,
							toolbar1: 'bold,italic,underline,alignleft,aligncenter,alignright,bullist,numlist,link',
							toolbar2: '',
							menubar: false,
							statusbar: false
						},
						quicktags: { buttons: 'strong,em,link,ul,ol,close' },
						mediaButtons: false
					});
					window.setTimeout(relocateEditorModeTabs, 100);
				}
			} catch (err) { /* ignore */ }
		});
	};

	$(document).on('click', '.mep-em__mode-tabs .wp-switch-editor', function () {
		var $field = $(this).closest('.mep-em__field--editor');
		var editorId = $field.find('.mep-em__mode-tabs').data('editor-tabs-for');
		window.setTimeout(function () {
			syncEditorModeClass($field, $('#wp-' + editorId + '-wrap'));
		}, 30);
	});

	$(document).on('focus click', '.mep-em__field--editor[data-em-wl-body]', function () {
		var type = $(this).data('em-wl-body');
		if (type && wlEditorMap[type]) {
			lastWlEditor = wlEditorMap[type];
		}
	});

	/* Variable chips */
	$(document).on('focus', '.mep-em__textarea', function () {
		lastTa = this;
	});
	$(document).on('click', '.mep-em__var', function (e) {
		e.preventDefault();
		var text = $(this).data('var');
		var $vars = $(this).closest('.mep-em__vars');
		var editorId = $vars.data('editor');
		if (editorId) {
			insertIntoEditor(editorId, text);
			return;
		}
		if ($vars.data('for-wl-editors')) {
			insertIntoEditor(lastWlEditor, text);
			return;
		}
		if ($vars.data('for-textarea')) {
			var $ta = lastTa ? $(lastTa) : $('.mep-em__panel.mep-em--active .mep-em__textarea').first();
			insertIntoTextarea($ta, text);
		}
	});

	/* Move Visual/Code tabs beside labels */
	relocateEditorModeTabs();
	window.setTimeout(relocateEditorModeTabs, 300);
	$(document).on('click', '.mep-em__tab[data-email-sub], .mep-gs__email-subnav-btn', function () {
		window.setTimeout(relocateEditorModeTabs, 80);
	});

		/* Test email modal */
		$(document).on('click', '#mep-em-test-btn', function (e) {
			e.preventDefault();
			openTestModal();
		});
		$(document).on('click', '[data-em-close]', function (e) {
			e.preventDefault();
			closeTestModal();
		});
		$(document).on('keydown', function (e) {
			if (e.key === 'Escape' && $('#mep-em-test-modal').hasClass('mep-em--open')) {
				closeTestModal();
			}
		});
		$(document).on('click', '#mep-em-test-send', function (e) {
			e.preventDefault();
			sendTestEmail();
		});

		/* General Settings interactions */
		function syncFullyBooked() {
			var mode = $('input[name="mep_gn_fully_booked"]:checked').val() || 'show';
			$('#mep-gn-hide-full').val(mode === 'hide' ? 'yes' : 'no');
			$('#mep-gn-sold-ribbon').val(mode === 'ribbon' ? 'yes' : 'no');
		}
		$(document).on('change', 'input[data-mep-fully-booked]', syncFullyBooked);
		syncFullyBooked();

		$(document).on('input change', '#mep-gn-zoom', function () {
			$('#mep-gn-zoom-val').text($(this).val());
		});

		function syncMapEnable() {
			var on = $('#mep-gn-map-enable').is(':checked');
			var $fields = $('#mep-gn-map-fields');
			var $type = $('#mep-gn-mep_google_map_type');
			$fields.prop('hidden', !on);
			if (!$type.length) {
				$type = $('select[name="general_setting_sec[mep_google_map_type]"]');
			}
			if (on) {
				$type.prop('disabled', false);
				if (!$type.val()) {
					$type.val('iframe');
				}
				$('input[name="general_setting_sec[mep_google_map_type]"].mep-gn-map-off').remove();
			} else {
				$type.prop('disabled', true);
				if (!$('input.mep-gn-map-off').length) {
					$type.after('<input type="hidden" class="mep-gn-map-off" name="general_setting_sec[mep_google_map_type]" value="" />');
				}
			}
		}
		$(document).on('change', '#mep-gn-map-enable', syncMapEnable);
		syncMapEnable();

		$(document).on('click', 'a.mep-si__icon-btn', function (e) {
			e.preventDefault();
		});

		$('.wpsa-browse').on('click', function (event) {
			event.preventDefault();
			var self = $(this);
			var $upload = self.closest('[data-mep-pdf-upload], [data-mep-ssc-upload]');
			var $url = self.prev('.wpsa-url');
			if (!$url.length) {
				$url = self.siblings('.wpsa-url');
			}
			if (!$url.length) {
				$url = self.closest('.mep-ms__file, td, .mep-el__row, [data-mep-pdf-upload], [data-mep-ssc-upload]').find('.wpsa-url').first();
			}
			var file_frame = wp.media.frames.file_frame = wp.media({
				title: self.data('uploader_title'),
				button: { text: self.data('uploader_button_text') },
				multiple: false
			});
			file_frame.on('select', function () {
				var attachment = file_frame.state().get('selection').first().toJSON();
				$url.val(attachment.url).trigger('change');
				if ($upload.length) {
					$upload.addClass('has-file');
					$upload.find('.mep-pdf__upload-preview, .mep-ssc__upload-preview').prop('hidden', false).find('img').attr('src', attachment.url);
					$upload.find('.mep-pdf__upload-empty, .mep-ssc__upload-empty').prop('hidden', true);
					$upload.find('.mep-pdf__upload-clear, .mep-ssc__upload-clear').prop('hidden', false);
				}
				if (typeof window.mepSscRefreshPreview === 'function') {
					window.mepSscRefreshPreview();
				}
			});
			file_frame.open();
		});

		$(document).on('click', '.mep-pdf__upload-clear, .mep-ssc__upload-clear', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var $upload = $(this).closest('[data-mep-pdf-upload], [data-mep-ssc-upload]');
			$upload.removeClass('has-file');
			$upload.find('.wpsa-url').val('').trigger('change');
			$upload.find('.mep-pdf__upload-preview, .mep-ssc__upload-preview').prop('hidden', true).find('img').attr('src', '');
			$upload.find('.mep-pdf__upload-empty, .mep-ssc__upload-empty').prop('hidden', false);
			$(this).prop('hidden', true);
			if (typeof window.mepSscRefreshPreview === 'function') {
				window.mepSscRefreshPreview();
			}
		});

		function syncPdfColorSwatch($input) {
			var $wrap = $input.closest('[data-mep-pdf-color]');
			if (!$wrap.length) {
				return;
			}
			var val = $input.val() || $input.data('default-color') || '#FFFFFF';
			$wrap.find('.mep-pdf__color-swatch').css('background-color', val);
		}
		$(document).on('change input irischange', '.mep-pdf__color-hex', function () {
			syncPdfColorSwatch($(this));
		});
		$('.mep-pdf__color-hex').each(function () {
			syncPdfColorSwatch($(this));
		});

		function syncSscColorSwatch($input) {
			var $wrap = $input.closest('[data-mep-ssc-color]');
			if (!$wrap.length) {
				return;
			}
			var val = $input.val() || $input.data('default-color') || '#111827';
			$wrap.find('.mep-ssc__color-swatch').css('background-color', val);
		}
		function mepSscRefreshPreview() {
			var $card = $('#mep-ssc-preview-card');
			if (!$card.length) {
				return;
			}
			var $root = $card.closest('.mep-ssc');
			var frame = $root.find('[data-ssc-preview="frame"]').val() || '';
			var font = $root.find('[data-ssc-preview="font"]').val() || 'default';
			var text = $root.find('[data-ssc-preview="text"]').val() || '#111827';
			var accent = $root.find('[data-ssc-preview="accent"]').val() || '#059669';
			$card.css({
				'--mep-ssc-text': text,
				'--mep-ssc-accent': accent,
				'font-family': (font && font !== 'default') ? ("'" + font + "', sans-serif") : 'Inter, system-ui, sans-serif',
				'background-image': frame ? ('url(' + frame + ')') : 'none'
			});
			$card.toggleClass('has-frame', !!frame);
			if (!frame) {
				$card.css('background-image', '');
			}
		}
		window.mepSscRefreshPreview = mepSscRefreshPreview;
		$(document).on('change input irischange', '.mep-ssc__color-hex, [data-ssc-preview]', function () {
			syncSscColorSwatch($(this));
			mepSscRefreshPreview();
		});
		$(document).on('click', '#mep-ssc-refresh-preview', function (e) {
			e.preventDefault();
			mepSscRefreshPreview();
			var $btn = $(this);
			$btn.addClass('is-refreshing');
			window.setTimeout(function () {
				$btn.removeClass('is-refreshing');
			}, 400);
		});
		$('.mep-ssc__color-hex').each(function () {
			syncSscColorSwatch($(this));
		});
		mepSscRefreshPreview();
		$(document).on('click', '.mep-pdf__color-swatch, .mep-pdf__color-name', function () {
			var $input = $(this).closest('[data-mep-pdf-color]').find('.mep-pdf__color-hex');
			if ($input.length && $input.data('wpWpColorPicker')) {
				$input.wpColorPicker('open');
			} else {
				$input.trigger('focus').trigger('click');
			}
		});

		$(document).on('click', '.mep-ssc__color-swatch', function () {
			var $input = $(this).closest('[data-mep-ssc-color]').find('.mep-ssc__color-hex');
			if ($input.length && $input.data('wpWpColorPicker')) {
				$input.wpColorPicker('open');
			} else {
				$input.trigger('focus').trigger('click');
			}
		});

		$(document).on('click', '.mep-pay__tab[data-pay-sub]', function (e) {
			e.preventDefault();
			mepGs.switchPaymentSub($(this).data('pay-sub'));
		});

		$(document).on('change', '.mep-pay__wc-enable', function () {
			var on = $(this).is(':checked');
			$('.mep-pay__wc-body').prop('hidden', !on);
		});

		var startTab = defaultTab;
		if (window.location.hash) {
			var hash = window.location.hash.replace('#', '');
			if ($('#mep-tab-' + hash).length || isNestedChild(hash) || $('#mep-email-sub-' + hash).length) {
				startTab = hash;
			}
		} else if (typeof localStorage !== 'undefined') {
			var stored = localStorage.getItem('mep_activetab');
			if (stored && ($('#mep-tab-' + stored).length || isNestedChild(stored))) {
				startTab = stored;
			}
		}

		if (startTab) {
			mepGs.switchTab(startTab);
		}
	});

})(jQuery, window.mepGs || (window.mepGs = {}));
