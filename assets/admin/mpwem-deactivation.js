/**
 * Event Booking Manager – deactivation modal.
 *
 * A single static modal — the deactivate-mode choice and the optional
 * "why are you deactivating" survey are both visible at once, no
 * step/screen transitions. The Appneck SDK bundles its own separate
 * deactivation survey popup; that hand-off is skipped (our capture-phase
 * click listener below stops it from ever opening) and its *same*
 * dynamically-configured questions are instead fetched and rendered
 * straight into this modal's reason section, with answers posted straight
 * to the SDK's own survey AJAX endpoint — so responses land on the
 * Appneck server exactly as they would from the SDK's own popup, just
 * without a second dialog.
 *
 * Flow when the plugin's "Deactivate" link is clicked:
 *   1. Our modal opens (capture-phase listener stops the click before the
 *      native deactivate link, or the Appneck SDK's own click handler, ever
 *      see it) and, in the background, fetches the survey questions.
 *   2. User picks "Deactivate only" or "Delete all data", optionally answers
 *      the survey, and clicks the single action button.
 *      - Deactivate only: answers (if any) posted to the Appneck survey
 *        endpoint, then navigate to the deactivate URL.
 *      - Delete all data: data is removed in small batches with a progress
 *        bar (so sites with thousands of events + linked hidden products
 *        never time out); answers (if any) posted alongside, then navigate.
 */
(function ($) {
	'use strict';

	var cfg = window.mpwemDeactivation || {};
	var i18n = cfg.i18n || {};
	var deactivateUrl = '';
	var surveyQuestions = null; // null = not fetched yet, [] = fetched but empty/unavailable.
	var surveyFetching = false;

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

	function esc(text) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(text == null ? '' : String(text)));
		return div.innerHTML;
	}

	/**
	 * Renders the Appneck-supplied questions into our own modal markup.
	 * Mirrors the field types the SDK's own survey popup understands
	 * (radio, checkbox, dropdown, rating, text_area, conditional) so an
	 * unmodified answer collected here validates the same way against the
	 * server. An unknown/newer type is skipped, same as the SDK's own popup.
	 */
	function renderSurveyFields(questions) {
		var $fields = $modal().find('[data-mpwem-survey-fields]');
		var html = '';

		for (var i = 0; i < questions.length; i++) {
			var q = questions[i];
			var choices = (q.options && q.options.choices) || [];
			var body = '';

			if (q.type === 'radio' || q.type === 'checkbox') {
				body += '<fieldset><legend>' + esc(q.text) + '</legend>';
				for (var c = 0; c < choices.length; c++) {
					body += '<label class="mpwem-deact-reason-option"><input type="' + (q.type === 'radio' ? 'radio' : 'checkbox') + '" name="mpwem_survey_q' + i + '" value="' + esc(choices[c]) + '"> <span>' + esc(choices[c]) + '</span></label>';
				}
				body += '</fieldset>';
			} else if (q.type === 'dropdown') {
				body += '<label class="mpwem-deact-reason-option mpwem-deact-reason-option--select"><span>' + esc(q.text) + '</span><select name="mpwem_survey_q' + i + '"><option value="">&mdash;</option>';
				for (var d = 0; d < choices.length; d++) {
					body += '<option value="' + esc(choices[d]) + '">' + esc(choices[d]) + '</option>';
				}
				body += '</select></label>';
			} else if (q.type === 'rating') {
				var max = (q.options && q.options.max) ? parseInt(q.options.max, 10) : 5;
				body += '<fieldset><legend>' + esc(q.text) + '</legend><div class="mpwem-deact-rating">';
				for (var r = 1; r <= max; r++) {
					body += '<label class="mpwem-deact-reason-option"><input type="radio" name="mpwem_survey_q' + i + '" value="' + r + '"> <span>' + r + '</span></label>';
				}
				body += '</div></fieldset>';
			} else if (q.type === 'text_area') {
				body += '<label class="mpwem-deact-reason-option mpwem-deact-reason-option--text"><span>' + esc(q.text) + '</span><textarea class="mpwem-deact-reason-detail" name="mpwem_survey_q' + i + '" rows="3" maxlength="' + (cfg.surveyMaxLength || 2000) + '"></textarea></label>';
			} else if (q.type === 'conditional') {
				body += '<fieldset><legend>' + esc(q.text) + '</legend>';
				for (var cc = 0; cc < choices.length; cc++) {
					var choice = choices[cc];
					var choiceText = (choice && typeof choice === 'object') ? choice.text : choice;
					var needsText = !!(choice && typeof choice === 'object' && choice.requires_text);
					body += '<label class="mpwem-deact-reason-option"><input type="radio" name="mpwem_survey_q' + i + '" value="' + esc(choiceText) + '" data-choice-index="' + cc + '" data-needs-text="' + (needsText ? '1' : '0') + '"> <span>' + esc(choiceText) + '</span></label>';
					if (needsText) {
						body += '<div class="mpwem-deact-followup" data-followup-index="' + cc + '" hidden><textarea class="mpwem-deact-reason-detail" maxlength="' + (cfg.surveyMaxLength || 2000) + '" placeholder="' + esc(i18n.tellUsMore || 'Optional – tell us more') + '"></textarea></div>';
					}
				}
				body += '</fieldset>';
			}

			if (body === '') {
				continue;
			}
			html += '<div class="mpwem-deact-question" data-question-id="' + esc(q.id) + '" data-question-type="' + esc(q.type) + '">' + body + '</div>';
		}

		$fields.html(html);
		return html !== '';
	}

	/**
	 * Collects answers out of the rendered survey fields in the shape the
	 * Appneck SDK's survey endpoint expects: {questionId: value}, where
	 * value is a string (radio/rating/dropdown/text_area), an array of
	 * strings (checkbox), or {value, text?} (conditional).
	 */
	function collectSurveyAnswers() {
		var values = {};
		$modal().find('.mpwem-deact-question').each(function () {
			var $block = $(this);
			var id = $block.data('question-id');
			var type = $block.data('question-type');

			if (type === 'checkbox') {
				var list = [];
				$block.find('input:checked').each(function () { list.push(this.value); });
				if (list.length) {
					values[id] = list;
				}
			} else if (type === 'conditional') {
				var $picked = $block.find('input:checked');
				if ($picked.length) {
					var entry = { value: $picked.val() };
					var idx = $picked.data('choice-index');
					var $followup = $block.find('[data-followup-index="' + idx + '"] textarea');
					if ($followup.length && !$followup.parent().attr('hidden') && $followup.val() !== '') {
						entry.text = $followup.val();
					}
					values[id] = entry;
				}
			} else if (type === 'radio' || type === 'rating') {
				var $one = $block.find('input:checked');
				if ($one.length) {
					values[id] = $one.val();
				}
			} else {
				var $field = $block.find('select, textarea');
				if ($field.length && $field.val() !== '') {
					values[id] = $field.val();
				}
			}
		});
		return values;
	}

	/**
	 * Fetches the dynamically-configured survey questions from the Appneck
	 * SDK's own deactivation-survey AJAX action (op=questions) and renders
	 * them into the modal. No-ops (and hides the reason section) when the
	 * SDK didn't expose a survey endpoint, the fetch fails, or the product
	 * has no questions configured on the Appneck dashboard.
	 */
	function loadSurveyQuestions() {
		var $reason = $modal().find('.mpwem-deact-reason');

		if (Array.isArray(surveyQuestions)) {
			$reason.attr('hidden', surveyQuestions.length === 0);
			return;
		}

		if (!cfg.surveyAction || !cfg.surveyNonce) {
			surveyQuestions = [];
			$reason.attr('hidden', true);
			return;
		}

		if (surveyFetching) {
			return;
		}
		surveyFetching = true;

		$reason.attr('hidden', false);
		$modal().find('[data-mpwem-survey-fields]').html(
			'<p class="mpwem-deact-survey-loading">' + esc(i18n.surveyLoading || 'Loading…') + '</p>'
		);

		$.ajax({
			url: cfg.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: cfg.surveyAction,
				nonce: cfg.surveyNonce,
				op: 'questions'
			}
		}).done(function (res) {
			var questions = (res && res.success && res.data && Array.isArray(res.data.questions)) ? res.data.questions : [];
			surveyQuestions = questions;
			var hasFields = questions.length ? renderSurveyFields(questions) : false;
			$reason.attr('hidden', !hasFields);
		}).fail(function () {
			// Could not reach the Appneck server; deactivation must never
			// wait on or be blocked by this, so just skip the survey.
			surveyQuestions = [];
			$reason.attr('hidden', true);
		}).always(function () {
			surveyFetching = false;
		});
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
		updateSubmitLabel();
		$m.addClass('is-open').attr('aria-hidden', 'false');
		document.body.classList.add('mpwem-deact-lock');
		loadSurveyQuestions();
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
	 * Submits the collected survey answers (if any) straight to the Appneck
	 * SDK's own survey AJAX endpoint (op=submit) — the same endpoint its own
	 * popup would use — then navigates to the deactivate URL regardless of
	 * the AJAX outcome. Deactivation must never wait on, or be blocked by, a
	 * lost or refused submission.
	 */
	function submitSurveyThenDeactivate() {
		if (!cfg.surveyAction || !cfg.surveyNonce || !Array.isArray(surveyQuestions) || !surveyQuestions.length) {
			goToDeactivateUrl();
			return;
		}

		var answers = collectSurveyAnswers();
		if ($.isEmptyObject(answers)) {
			goToDeactivateUrl();
			return;
		}

		$.ajax({
			url: cfg.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: cfg.surveyAction,
				nonce: cfg.surveyNonce,
				op: 'submit',
				answers: JSON.stringify(answers)
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
			$m.find('.mpwem-deact-choice').attr('hidden', false);
			$m.find('.mpwem-deact-reason').attr('hidden', !(Array.isArray(surveyQuestions) && surveyQuestions.length));
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
					submitSurveyThenDeactivate();
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

		// Conditional-type follow-up textareas only appear once their own
		// choice is picked.
		$m.on('change', '[data-mpwem-survey-fields] input[data-needs-text]', function () {
			var $block = $(this).closest('.mpwem-deact-question');
			$block.find('[data-followup-index]').attr('hidden', true);
			if ($(this).data('needs-text') == 1) {
				var idx = $(this).data('choice-index');
				$block.find('[data-followup-index="' + idx + '"]').attr('hidden', false);
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
				submitSurveyThenDeactivate();
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
