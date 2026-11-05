/**
 * Event Status (cancel / restore an event) - Modern and Classic editors.
 *
 * Keeps the status chip and the notice field in step with the switch, and lets the
 * "Cancel Event" / "Restore Event" item in the Modern editor's status menu jump to
 * the Event Status card on the Ticket & Pricing step.
 */
(function () {
	'use strict';

	function sync(panel) {
		var toggle = panel.querySelector('[data-mpwem-event-status-toggle]');
		var details = panel.querySelector('[data-mpwem-event-status-details]');
		var on = !!(toggle && toggle.checked);
		panel.classList.toggle('is-cancelled', on);
		if (details) {
			details.hidden = !on;
		}
		var card = panel.closest('#mpwem_event_status_card');
		if (card) {
			card.classList.toggle('is-cancelled', on);
		}
	}

	function closeStatusMenu(item) {
		var wrap = item.closest('.mpwem-status-actions__menu-wrap');
		if (!wrap) {
			return;
		}
		wrap.classList.remove('is-open');
		var menuToggle = wrap.querySelector('.mpwem-status-actions__toggle');
		if (menuToggle) {
			menuToggle.setAttribute('aria-expanded', 'false');
		}
	}

	function revealStatusCard() {
		var card = document.getElementById('mpwem_event_status_card');
		if (!card) {
			return;
		}
		// Switch steps through the editor's own tab so its validation and URL hash
		// behave exactly as if the step had been clicked.
		var stepTab = document.querySelector('.mpwem-step[data-step-key="tickets"]');
		if (stepTab && !stepTab.classList.contains('is-active')) {
			stepTab.click();
		}
		window.setTimeout(function () {
			if (!card.offsetParent) {
				return; // The editor kept the current step (a validation error is shown).
			}
			card.scrollIntoView({ behavior: 'smooth', block: 'start' });
			var toggle = card.querySelector('[data-mpwem-event-status-toggle]');
			if (toggle) {
				toggle.focus({ preventScroll: true });
			}
		}, 60);
	}

	document.addEventListener('change', function (event) {
		var target = event.target;
		if (!target || !target.matches) {
			return;
		}
		var panel = target.closest('[data-mpwem-event-status]');
		if (!panel) {
			return;
		}
		if (target.matches('[data-mpwem-event-status-toggle]')) {
			sync(panel);
		} else if (target.matches('[data-mpwem-event-status-notify]')) {
			var email = panel.querySelector('[data-mpwem-event-status-email]');
			if (email) {
				email.hidden = !target.checked;
			}
		} else if (target.matches('[data-mpwem-event-status-refund]') && target.checked) {
			// Refunds move money and cannot be undone: ask before arming them.
			if (!window.confirm(target.getAttribute('data-confirm') || '')) {
				target.checked = false;
			}
		}
	});

	document.addEventListener('click', function (event) {
		var item = event.target && event.target.closest ? event.target.closest('[data-mpwem-goto-event-status]') : null;
		if (!item) {
			return;
		}
		event.preventDefault();
		closeStatusMenu(item);
		revealStatusCard();
	});

	function init() {
		var panels = document.querySelectorAll('[data-mpwem-event-status]');
		for (var i = 0; i < panels.length; i++) {
			sync(panels[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
