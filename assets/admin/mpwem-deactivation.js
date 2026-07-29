/**
 * Event Booking Manager – deactivation modal.
 *
 * A single static modal — the deactivate-mode choice and the optional
 * "why are you deactivating" feedback fields are both visible at once, no
 * step/screen transitions. This used to hand off to a second, separate
 * Appsero feedback survey popup after this modal closed; that hand-off is
 * gone, and any reason the user picks here is instead posted straight to
 * Appsero's own existing uninstall-reason endpoint (no popup involved), so
 * that feedback channel keeps working without a second dialog.
 *
 * Flow when the plugin's "Deactivate" link is clicked:
 *   1. Our modal opens (capture-phase listener stops the click before the
 *      native deactivate link, or Appsero's own click handler, ever see it).
 *   2. User picks "Deactivate only" or "Delete all data", optionally picks a
 *      reason, and clicks the single action button.
 *      - Deactivate only: reason (if any) posted to Appsero, then navigate
 *        to the deactivate URL.
 *      - Delete all data: data is removed in small batches with a progress
 *        bar (so sites with thousands of events + linked hidden products
 *        never time out); reason (if any) posted alongside, then navigate.
 */
(function ($) {
	'use strict';

	var cfg = window.mpwemDeactivation || {};
	var i18n = cfg.i18n || {};
	var deactivateUrl = '';

	function $modal() {
		return $('#mpwem-deact-modal');
	}

	function isOurDeactivateLink(anchor) {
		if (!anchor || anchor.tagName !== 'A') {
			return false;
		}
		var href = anchor.getAttribute('href') || '';
		if (href.indexOf('action=deactivate') === -1) {
			return false;
		}
		var row = anchor.closest('tr[data-plugin]');
		if (row && cfg.basename && row.getAttribute('data-plugin') === cfg.basename) {
			return true;
		}
		// Fallback: match the plugin slug inside the deactivate URL.
		return cfg.basename && href.indexOf(encodeURIComponent(cfg.basename)) !== -1;
	}

	function updateSubmitLabel() {
		var purge = selectedMode() === 'purge';
		$modal().find('.mpwem-deact-submit')
			.text(purge ? (i18n.deleteAndDeactivate || 'Delete & Deactivate') : (i18n.deactivate || 'Deactivate'));
	}

	function openModal() {
		var $m = $modal();
		$m.find('input[name="mpwem_deact_mode"][value="keep"]').prop('checked', true);
		$m.find('#mpwem-deact-understand').prop('checked', false);
		$m.find('input[name="mpwem_deact_reason"]').prop('checked', false);
		$m.find('.mpwem-deact-reason-detail').val('').attr('placeholder', cfg.reasonPlaceholder || '');
		$m.find('.mpwem-deact-confirm').attr('hidden', true);
		$m.find('.mpwem-deact-error').attr('hidden', true).text('');
		$m.find('.mpwem-deact-choice, .mpwem-deact-reason').attr('hidden', false);
		$m.find('.mpwem-deact-progress').attr('hidden', true);
		$m.find('.mpwem-deact-bar__fill').css('width', '0%');
		$m.find('.mpwem-deact-cancel').attr('hidden', false);
		resetSubmit();
		updateSubmitLabel();
		$m.addClass('is-open').attr('aria-hidden', 'false');
		document.body.classList.add('mpwem-deact-lock');
	}

	function closeModal() {
		$modal().removeClass('is-open').attr('aria-hidden', 'true');
		document.body.classList.remove('mpwem-deact-lock');
	}

	function selectedMode() {
		return $modal().find('input[name="mpwem_deact_mode"]:checked').val();
	}

	function resetSubmit() {
		$modal().find('.mpwem-deact-submit')
			.removeClass('is-loading')
			.text(cfg.submitText || 'Continue');
	}

	function showError(msg) {
		$modal().find('.mpwem-deact-error').text(msg).attr('hidden', false);
	}

	function goToDeactivateUrl() {
		window.location.href = deactivateUrl;
	}

	/**
	 * Submits the picked reason (if any) to Appsero's own AJAX endpoint —
	 * reusing their existing feedback channel without opening their popup —
	 * then navigates to the deactivate URL regardless of the AJAX outcome.
	 */
	function submitReasonThenDeactivate() {
		var $m = $modal();
		var $radio = $m.find('input[name="mpwem_deact_reason"]:checked');
		var reasonId = $radio.length ? $radio.val() : 'none';
		var reasonInfo = $m.find('.mpwem-deact-reason-detail').val() || '';

		if (!cfg.appseroAction || !cfg.appseroNonce) {
			goToDeactivateUrl();
			return;
		}

		$.ajax({
			url: cfg.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: cfg.appseroAction,
				nonce: cfg.appseroNonce,
				reason_id: reasonId,
				reason_info: reasonInfo
			}
		}).always(goToDeactivateUrl);
	}

	function runPurge() {
		var $m = $modal();
		var total = 0;
		var removed = 0;
		var lastRemaining = Infinity;

		$m.find('.mpwem-deact-choice, .mpwem-deact-reason').attr('hidden', true);
		$m.find('.mpwem-deact-progress').attr('hidden', false);
		$m.find('.mpwem-deact-cancel').attr('hidden', true);
		$m.find('.mpwem-deact-submit').addClass('is-loading').text(i18n.cleaning || 'Deleting data…');

		function setBar(done, isFinishing) {
			var pct = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : (isFinishing ? 100 : 0);
			$m.find('.mpwem-deact-bar__fill').css('width', pct + '%');
			var label;
			if (isFinishing) {
				label = i18n.finishing || 'Finishing up…';
			} else {
				label = (i18n.removed || '%1$s of %2$s items removed')
					.replace('%1$s', done).replace('%2$s', total);
			}
			$m.find('.mpwem-deact-progress__count').text(label);
		}

		function fail() {
			$m.find('.mpwem-deact-choice, .mpwem-deact-reason').attr('hidden', false);
			$m.find('.mpwem-deact-progress').attr('hidden', true);
			$m.find('.mpwem-deact-cancel').attr('hidden', false);
			resetSubmit();
			updateSubmitLabel();
			showError(i18n.failed || 'Cleanup failed.');
		}

		function ajaxStep(extra) {
			return $.ajax({
				url: cfg.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: $.extend({ action: cfg.action, nonce: cfg.nonce }, extra)
			});
		}

		function nextBatch() {
			ajaxStep({ step: 'batch', batch_size: 20 }).done(function (res) {
				if (!res || !res.success || !res.data) {
					fail();
					return;
				}
				if (res.data.done) {
					setBar(total, true);
					submitReasonThenDeactivate();
					return;
				}
				var remaining = parseInt(res.data.remaining, 10) || 0;
				// Safety: if a round neither deletes anything nor reduces the
				// remaining count, stop instead of looping forever.
				if ((res.data.deleted | 0) === 0 && remaining >= lastRemaining) {
					fail();
					return;
				}
				lastRemaining = remaining;
				removed = Math.max(0, total - remaining);
				setBar(removed, false);
				nextBatch();
			}).fail(fail);
		}

		// First get the total for the bar, then loop batches.
		ajaxStep({ step: 'count' }).done(function (res) {
			total = (res && res.success && res.data) ? parseInt(res.data.total, 10) || 0 : 0;
			setBar(0, total === 0);
			nextBatch();
		}).fail(fail);
	}

	// Capture phase: run before the theme/SDK bubble-phase handlers.
	document.addEventListener(
		'click',
		function (e) {
			var anchor = e.target.closest ? e.target.closest('a') : null;
			if (!anchor || !isOurDeactivateLink(anchor)) {
				return;
			}
			e.preventDefault();
			e.stopImmediatePropagation();
			deactivateUrl = anchor.getAttribute('href');
			openModal();
		},
		true
	);

	$(function () {
		var $m = $modal();
		if (!$m.length) {
			return;
		}

		cfg.submitText = $m.find('.mpwem-deact-submit').text();
		cfg.reasonPlaceholder = $m.find('.mpwem-deact-reason-detail').attr('placeholder') || '';

		$m.on('change', 'input[name="mpwem_deact_mode"]', function () {
			var purge = selectedMode() === 'purge';
			$m.find('.mpwem-deact-confirm').attr('hidden', !purge);
			$m.find('.mpwem-deact-error').attr('hidden', true);
			updateSubmitLabel();
		});

		$m.on('click', '.mpwem-deact-close, .mpwem-deact-cancel', function (e) {
			e.preventDefault();
			closeModal();
		});

		$m.on('click', 'input[name="mpwem_deact_reason"]', function () {
			var $textarea = $m.find('.mpwem-deact-reason-detail');
			var placeholder = $(this).data('placeholder');
			if (placeholder) {
				$textarea.attr('placeholder', placeholder);
			}
		});

		$m.on('click', function (e) {
			if (e.target === this) {
				closeModal();
			}
		});

		$(document).on('keydown', function (e) {
			if (e.key === 'Escape' && $m.hasClass('is-open') && !$m.find('.mpwem-deact-submit').hasClass('is-loading')) {
				closeModal();
			}
		});

		$m.on('click', '.mpwem-deact-submit', function (e) {
			e.preventDefault();
			var $btn = $(this);
			if ($btn.hasClass('is-loading')) {
				return;
			}

			if (selectedMode() !== 'purge') {
				$btn.addClass('is-loading').text(i18n.submitting || 'Deactivating…');
				submitReasonThenDeactivate();
				return;
			}

			if (!$m.find('#mpwem-deact-understand').is(':checked')) {
				showError(i18n.confirm || 'Please confirm before deleting.');
				return;
			}

			runPurge();
		});
	});
})(jQuery);
