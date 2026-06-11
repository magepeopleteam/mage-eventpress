/**
 * Event Booking Manager – deactivation modal.
 *
 * Flow when the plugin's "Deactivate" link is clicked:
 *   1. Our modal opens (capture-phase listener, so it runs before – and suppresses –
 *      any other popup bound to the same link, e.g. the Appsero feedback survey).
 *   2. User chooses "Deactivate only" or "Delete all data".
 *      - Delete: data is removed in small batches with a progress bar (so sites with
 *        thousands of events + linked hidden products never time out).
 *   3. What happens next depends on the choice:
 *      - Deactivate only: we re-fire the original click with a pass-through flag so
 *        the Appsero feedback survey opens through its own handler (with the correct
 *        deactivate URL). If Appsero isn't present the click just navigates.
 *      - Delete all data: once the cleanup finishes we go straight to the deactivate
 *        URL and skip the survey entirely (the user has already committed to leaving,
 *        and the data is gone, so there is nothing to cancel back to).
 */
(function ($) {
	'use strict';

	var cfg = window.mpwemDeactivation || {};
	var i18n = cfg.i18n || {};
	var deactivateLinkEl = null; // the actual <a> we intercepted
	var deactivateUrl = '';
	var passThrough = false; // set right before we re-fire the click for Appsero/native

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

	function openModal() {
		var $m = $modal();
		$m.find('input[name="mpwem_deact_mode"][value="keep"]').prop('checked', true);
		$m.find('#mpwem-deact-understand').prop('checked', false);
		$m.find('.mpwem-deact-confirm').attr('hidden', true);
		$m.find('.mpwem-deact-error').attr('hidden', true).text('');
		$m.find('.mpwem-deact-choice').attr('hidden', false);
		$m.find('.mpwem-deact-progress').attr('hidden', true);
		$m.find('.mpwem-deact-bar__fill').css('width', '0%');
		$m.find('.mpwem-deact-cancel').attr('hidden', false);
		resetSubmit();
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

	/**
	 * Continue to deactivation.
	 *
	 * @param {boolean} skipSurvey When true (the delete path) we navigate straight to
	 *   the deactivate URL and bypass the Appsero survey. Otherwise we re-dispatch a
	 *   real click that our capture listener lets through (passThrough), so the Appsero
	 *   feedback survey can open via its own handler; if Appsero is absent the click
	 *   simply navigates.
	 */
	function proceedToDeactivate(skipSurvey) {
		if (skipSurvey || !deactivateLinkEl) {
			window.location.href = deactivateUrl;
			return;
		}
		passThrough = true;
		closeModal();
		// Native .click() fires a real event: capture (us, passes) -> bubble (Appsero).
		deactivateLinkEl.click();
	}

	function runPurge() {
		var $m = $modal();
		var total = 0;
		var removed = 0;
		var lastRemaining = Infinity;

		$m.find('.mpwem-deact-choice').attr('hidden', true);
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
			$m.find('.mpwem-deact-choice').attr('hidden', false);
			$m.find('.mpwem-deact-progress').attr('hidden', true);
			$m.find('.mpwem-deact-cancel').attr('hidden', false);
			resetSubmit();
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
					// Delete path: deactivate immediately, skip the Appsero survey.
					proceedToDeactivate(true);
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
			// Our re-fired click: let it flow through to Appsero / native navigation.
			if (passThrough) {
				passThrough = false;
				return;
			}
			e.preventDefault();
			e.stopImmediatePropagation();
			deactivateLinkEl = anchor;
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

		$m.on('change', 'input[name="mpwem_deact_mode"]', function () {
			var purge = selectedMode() === 'purge';
			$m.find('.mpwem-deact-confirm').attr('hidden', !purge);
			$m.find('.mpwem-deact-error').attr('hidden', true);
		});

		$m.on('click', '.mpwem-deact-close, .mpwem-deact-cancel', function (e) {
			e.preventDefault();
			closeModal();
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
				// Deactivate only: show the Appsero feedback survey before leaving.
				proceedToDeactivate(false);
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
