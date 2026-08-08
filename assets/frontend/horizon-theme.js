/**
 * Horizon Theme — ticket sidebar body redesign.
 * Preserves booking/AJAX; restyles date, tickets, summary, attendee UI.
 */
(function ($) {
	'use strict';

	function i18n(key, fallback) {
		return (typeof mep_horizon_i18n !== 'undefined' && mep_horizon_i18n[key]) ? mep_horizon_i18n[key] : fallback;
	}

	function monthShort(d) {
		var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		return months[d.getMonth()] || '';
	}

	function parseDateValue(raw) {
		if (!raw) {
			return null;
		}
		var normalized = String(raw).trim().replace(' ', 'T');
		var d = new Date(normalized);
		if (isNaN(d.getTime())) {
			d = new Date(String(raw).replace(/-/g, '/'));
		}
		return isNaN(d.getTime()) ? null : d;
	}

	function formatShortDate(raw) {
		var d = parseDateValue(raw);
		if (!d) {
			return raw;
		}
		return monthShort(d) + ' ' + d.getDate() + ', ' + d.getFullYear();
	}

	function formatTime(raw) {
		var d = parseDateValue(raw);
		if (!d) {
			var match = String(raw || '').match(/(\d{1,2}:\d{2}\s*(?:am|pm|AM|PM)?)/);
			if (!match) {
				return '';
			}
			return match[1].replace(/\s+/g, ' ').toUpperCase();
		}
		var h = d.getHours();
		var m = d.getMinutes();
		var ap = h >= 12 ? 'PM' : 'AM';
		h = h % 12;
		if (h === 0) {
			h = 12;
		}
		return h + ':' + (m < 10 ? '0' : '') + m + ' ' + ap;
	}

	function currencyFormat(amount) {
		if (typeof mpwem_price_format === 'function') {
			try {
				return mpwem_price_format(amount);
			} catch (e) { /* fall through */ }
		}
		var n = Number(amount || 0);
		return '$' + n.toFixed(2);
	}

	function dateKeyFromValue(raw) {
		var d = parseDateValue(raw);
		if (!d) {
			var m = String(raw || '').match(/(\d{4}-\d{2}-\d{2})/);
			return m ? m[1] : '';
		}
		var y = d.getFullYear();
		var mo = d.getMonth() + 1;
		var day = d.getDate();
		return y + '-' + (mo < 10 ? '0' : '') + mo + '-' + (day < 10 ? '0' : '') + day;
	}

	function collectDateTimeSlots($select) {
		var slots = [];
		$select.find('option').each(function () {
			var $opt = $(this);
			var value = $opt.attr('value');
			if (value === undefined || value === null || value === '') {
				return;
			}
			value = String(value);
			var key = dateKeyFromValue(value);
			if (!key) {
				return;
			}
			slots.push({
				value: value,
				dateKey: key,
				dateLabel: formatShortDate(value),
				timeLabel: formatTime(value) || $.trim($opt.text()) || value
			});
		});
		return slots;
	}

	function uniqueDateEntries(slots) {
		var seen = {};
		var list = [];
		slots.forEach(function (slot) {
			if (seen[slot.dateKey]) {
				return;
			}
			seen[slot.dateKey] = 1;
			list.push({ dateKey: slot.dateKey, dateLabel: slot.dateLabel });
		});
		return list;
	}

	function slotsForDate(slots, dateKey) {
		return slots.filter(function (slot) {
			return slot.dateKey === dateKey;
		});
	}

	function enhanceDateTime($root) {
		var $header = $root.find('.horizon_ticket_body .date-time-header').first();
		if (!$header.length) {
			return;
		}

		var $area = $header.find('.date-time-area');
		$header.find('.ticket-title').remove();

		if (!$area.length) {
			return;
		}

		$area.addClass('horizon_dt_grid');
		$area.find('label').each(function () {
			var $label = $(this);
			var $span = $label.children('span').first();
			var text = ($span.text() || '').toLowerCase();
			if (text.indexOf('time') !== -1) {
				$span.text(i18n('time', 'Time'));
				$label.addClass('horizon_dt_time');
			} else {
				$span.text(i18n('date', 'Date'));
				$label.addClass('horizon_dt_date');
			}
			$label.find('i').remove();
		});

		// Everyday / recurring: real #mpwem_time already exists — keep it interactive.
		if ($area.find('#mpwem_time, select[name="mpwem_time"]').length) {
			$area.find('.horizon_dt_time_display').remove();
			return;
		}

		var $source = $area.find('select#mpwem_date_time, select[name="mpwem_date_time"]').first();
		if (!$source.length || !$source.is('select')) {
			return;
		}

		// Already split into interactive Date + Time controls.
		if ($source.data('hz-dt-split')) {
			return;
		}

		var slots = collectDateTimeSlots($source);
		if (!slots.length) {
			return;
		}

		$source.data('hz-dt-split', 1);

		var dates = uniqueDateEntries(slots);
		var currentValue = $source.val() || ($source.find('option:selected').attr('value') || '');
		var currentKey = dateKeyFromValue(currentValue) || (dates[0] && dates[0].dateKey) || '';
		var currentSlots = slotsForDate(slots, currentKey);
		if (!currentSlots.length && dates[0]) {
			currentKey = dates[0].dateKey;
			currentSlots = slotsForDate(slots, currentKey);
		}
		if (!currentValue && currentSlots[0]) {
			currentValue = currentSlots[0].value;
			$source.val(currentValue);
		}

		var $dateLabel = $source.closest('label');
		if (!$dateLabel.length) {
			$dateLabel = $('<label class="horizon_dt_date"></label>').prependTo($area);
			$dateLabel.append($source);
		}
		$dateLabel.addClass('horizon_dt_date');
		if (!$dateLabel.children('span').length) {
			$dateLabel.prepend($('<span></span>').text(i18n('date', 'Date')));
		} else {
			$dateLabel.children('span').first().text(i18n('date', 'Date'));
		}

		// Visible date select (no name — source keeps mpwem_date_time).
		var $uiDate = $('<select class="formControl horizon_dt_date_select" aria-label="' + i18n('date', 'Date') + '"></select>');
		dates.forEach(function (entry) {
			$uiDate.append(
				$('<option></option>').attr('value', entry.dateKey).text(entry.dateLabel)
			);
		});
		$uiDate.val(currentKey);
		$source.hide().attr('tabindex', '-1').attr('aria-hidden', 'true');
		$source.after($uiDate);

		// Interactive time select (replaces old fake display).
		$area.find('.horizon_dt_time_display').remove();
		var $timeLabel = $(
			'<label class="horizon_dt_time horizon_dt_time_select_wrap">' +
				'<span>' + i18n('time', 'Time') + '</span>' +
			'</label>'
		);
		var $uiTime = $('<select class="formControl horizon_dt_time_select" aria-label="' + i18n('time', 'Time') + '"></select>');
		$timeLabel.append($uiTime);
		$area.append($timeLabel);

		function fillTimes(dateKey, preferredValue) {
			var daySlots = slotsForDate(slots, dateKey);
			$uiTime.empty();
			if (!daySlots.length) {
				$uiTime.append($('<option></option>').attr('value', '').text('—'));
				$uiTime.prop('disabled', true);
				return '';
			}
			$uiTime.prop('disabled', false);
			daySlots.forEach(function (slot) {
				$uiTime.append(
					$('<option></option>').attr('value', slot.value).text(slot.timeLabel)
				);
			});
			var next = preferredValue;
			var match = daySlots.some(function (slot) {
				return slot.value === preferredValue;
			});
			if (!match) {
				next = daySlots[0].value;
			}
			$uiTime.val(next);
			return next;
		}

		function commitValue(nextValue, triggerChange) {
			if (!nextValue || $source.val() === nextValue) {
				if (triggerChange && nextValue) {
					$source.trigger('change');
				}
				return;
			}
			$source.val(nextValue);
			if (triggerChange) {
				$source.trigger('change');
			}
		}

		fillTimes(currentKey, currentValue);
		commitValue($uiTime.val() || currentValue, false);

		$uiDate.off('change.horizonDtSplit').on('change.horizonDtSplit', function () {
			var key = $(this).val();
			var next = fillTimes(key, '');
			commitValue(next, true);
		});

		$uiTime.off('change.horizonDtSplit').on('change.horizonDtSplit', function () {
			commitValue($(this).val(), true);
		});
	}

	function cleanTicketName($item) {
		var $name = $item.find('.ticket-name').first();
		if (!$name.length) {
			return;
		}

		$name.find('.mep-ticket-mode-badge').remove();

		if (!$name.find('.horizon_ticket_label').length) {
			var labelParts = [];
			$name.contents().each(function () {
				if (this.nodeType === 3) {
					var t = this.textContent.replace(/\s+/g, ' ').trim();
					if (t) {
						labelParts.push(t);
					}
				}
			});
			if (labelParts.length) {
				$name.contents().filter(function () {
					return this.nodeType === 3;
				}).remove();
				$name.prepend($('<span class="horizon_ticket_label"></span>').text(labelParts.join(' ')));
			}
		}

		var $desc = $item.find('.ticket-description').first();
		if ($desc.length && !$desc.data('hz-full')) {
			var full = $.trim($desc.text());
			$desc.data('hz-full', full);
			$desc.attr('title', full);
		}
	}

	function enhanceTickets($root) {
		$root.find('.horizon_ticket_body .mep_ticket_item').each(function () {
			var $item = $(this);
			var $data = $item.children('.ticket-data');
			if (!$data.length) {
				return;
			}

			var $info = $data.find('> .ticket-info, > .horizon_ticket_top > .ticket-info').first();
			var $qty = $data.find('> .quantity-control, > .horizon_ticket_bottom > .quantity-control').first();
			var $price = $data.find('> .ticket-price, > .horizon_ticket_top > .ticket-price').first();
			var isExtra = $item.closest('.mpwem_ex_service').length > 0;
			var qtyVal = parseInt(
				$item.find(
					isExtra
						? '[name="event_extra_service_qty[]"], .inputIncDec'
						: '[name="option_qty[]"], .inputIncDec'
				).first().val(),
				10
			) || 0;

			$item.toggleClass('is-selected', qtyVal > 0);
			$item.removeClass('is-premium');

			if (!$data.children('.horizon_ticket_top').length && $info.length && $price.length) {
				var $top = $('<div class="horizon_ticket_top"></div>');
				$info.appendTo($top);
				$price.appendTo($top);
				$top.prependTo($data);
			}

			if (!$data.children('.horizon_ticket_bottom').length) {
				var $bottom = $('<div class="horizon_ticket_bottom"></div>');
				$info.find('.ticket-remaining').hide();
				$bottom.append(
					'<span class="horizon_available"><span class="horizon_available_dot" aria-hidden="true"></span>' +
					i18n('available', 'Available') +
					'</span>'
				);
				if ($qty.length) {
					$qty.appendTo($bottom);
				}
				$bottom.appendTo($data);
			} else {
				var $existingBottom = $data.children('.horizon_ticket_bottom').first();
				var $avail = $existingBottom.find('.horizon_available').first();
				var $qtyCtrl = $existingBottom.find('.quantity-control').first();
				if ($avail.length) {
					$avail.prependTo($existingBottom);
				}
				if ($qtyCtrl.length) {
					$qtyCtrl.appendTo($existingBottom);
				}
			}

			$item.find('.decQty').addClass('horizon_qty_minus');
			$item.find('.incQty').addClass('horizon_qty_plus');
			cleanTicketName($item);
		});

		$root.find('.mpwem_ex_service .card-header').each(function () {
			var t = $.trim($(this).text());
			if (t) {
				$(this).text(t);
			}
		});
	}

	function enhanceSummary($root) {
		$root.find('.horizon_ticket_body .mpwem_summery').each(function () {
			var $sum = $(this);
			if (!$sum.find('.horizon_summary_box').length) {
				var $total = $sum.children('.total');
				var $box = $('<div class="horizon_summary_box"></div>');
				$total.appendTo($box);
				$box.prependTo($sum);
			}

			var $totalEl = $sum.find('.total').first();
			$totalEl.contents().filter(function () {
				return this.nodeType === 3;
			}).each(function () {
				var raw = String(this.textContent || '').replace(/Total Price\s*:?/i, i18n('total', 'Total')).replace(/\s+/g, ' ').trim();
				if (!raw) {
					$(this).remove();
					return;
				}
				if (!$totalEl.find('.horizon_total_label').length) {
					$(this).replaceWith($('<span class="horizon_total_label"></span>').text(raw.replace(/:$/, '')));
				} else {
					$(this).remove();
				}
			});
			if (!$totalEl.find('.horizon_total_label').length) {
				$totalEl.prepend($('<span class="horizon_total_label"></span>').text(i18n('total', 'Total')));
			}
		});
		updateSummaryLines($root);
	}

	function updateSummaryLines($root) {
		var $box = $root.find('.horizon_summary_box').first();
		if (!$box.length) {
			return;
		}

		$box.find('.horizon_summary_lines').remove();
		var lines = [];

		$root.find('.mpwem_ticket_type .mep_ticket_item').each(function () {
			var $item = $(this);
			var qty = parseInt($item.find('[name="option_qty[]"]').val(), 10) || 0;
			if (qty <= 0) {
				return;
			}
			var name = $.trim($item.find('.ticket-name .horizon_ticket_label').text()) ||
				$.trim($item.find('.ticket-name').clone().children().remove().end().text()).replace(/\s+/g, ' ');
			var price = parseFloat($item.find('[name="option_qty[]"]').attr('data-price')) || 0;
			lines.push({
				label: name + ' × ' + qty,
				value: currencyFormat(price * qty),
				muted: false
			});
		});

		$root.find('.mpwem_ex_service .mep_ticket_item').each(function () {
			var $item = $(this);
			var qty = parseInt($item.find('[name="event_extra_service_qty[]"]').val(), 10) || 0;
			if (qty <= 0) {
				return;
			}
			var name = $.trim($item.find('.ticket-name .horizon_ticket_label').text()) ||
				$.trim($item.find('.ticket-name').clone().children().remove().end().text()).replace(/\s+/g, ' ');
			var price = parseFloat($item.find('[name="event_extra_service_qty[]"]').attr('data-price')) || 0;
			lines.push({
				label: name + ' × ' + qty,
				value: currencyFormat(price * qty),
				muted: true
			});
		});

		if (!lines.length) {
			return;
		}

		var $lines = $('<div class="horizon_summary_lines"></div>');
		$.each(lines, function (_, row) {
			$lines.append(
				'<div class="horizon_summary_row' + (row.muted ? ' horizon_summary_row--muted' : '') + '">' +
					'<span>' + $('<div>').text(row.label).html() + '</span>' +
					'<strong>' + row.value + '</strong>' +
				'</div>'
			);
		});
		$lines.append('<div class="horizon_summary_divider"></div>');
		$box.prepend($lines);
	}

	function updateCta($root) {
		var totalQty = 0;
		$root.find('.mpwem_ticket_type [name="option_qty[]"]').each(function () {
			totalQty += parseInt($(this).val(), 10) || 0;
		});

		var label = i18n('reserve', 'Reserve Tickets →');
		if (totalQty === 1) {
			label = i18n('reserveTicket', 'Reserve 1 Ticket →');
		} else if (totalQty > 1) {
			label = i18n('reserveTickets', 'Reserve %d Tickets →').replace('%d', totalQty);
		}

		$root.find('.horizon_ticket_body .mpwem_book_now').each(function () {
			var $btn = $(this);
			if ($btn.hasClass('dNone') || $btn.attr('name') === 'add-to-cart') {
				return;
			}
			$btn.text(label);
		});
	}

	function cssEscape(value) {
		var str = String(value || '');
		if (window.CSS && typeof window.CSS.escape === 'function') {
			return window.CSS.escape(str);
		}
		return str.replace(/["\\]/g, '\\$&');
	}

	function hasAttendeeSupport($area) {
		if (!$area || !$area.length) {
			return false;
		}
		var $root = $area.closest('.horizon_theme');
		return $area.find('.mep_attendee_info_hidden .mep_form_item').length > 0 ||
			$area.find('.mep_attendee_info .mep_form_item').length > 0 ||
			$area.find('.mpwem_booking_panel > .mep_attendee_info').length > 0 ||
			$root.find('.horizon_attendee_drawer__body .mep_form_item').length > 0;
	}

	function getSameAttendeeSetting($area) {
		var val = '';
		var $field = $();
		if ($area && $area.length) {
			$field = $area.find('[name="mep_same_attendee"]').first();
		}
		if (!$field.length) {
			$field = $('.horizon_theme [name="mep_same_attendee"]').first();
		}
		if ($field.length) {
			val = String($field.val() || '').toLowerCase().trim();
		}
		if (!val && typeof mep_horizon_i18n !== 'undefined' && mep_horizon_i18n.sameAttendee) {
			val = String(mep_horizon_i18n.sameAttendee).toLowerCase().trim();
		}
		return val;
	}

	function isSameAttendeeMode($area) {
		var val = getSameAttendeeSetting($area);
		// general_setting_sec[mep_enable_same_attendee] => yes | must
		return val === 'yes' || val === 'must';
	}

	function getTicketKeyFromItem($item) {
		if (!$item || !$item.length) {
			return '';
		}
		var key = $.trim($item.find('[name="option_name[]"]').first().val() || '');
		if (!key) {
			key = $.trim($item.find('[name="ticket_type[]"]').first().val() || '');
		}
		if (!key) {
			key = 'ticket-' + ($item.index() + 1);
		}
		return key;
	}

	function getTicketLabelFromItem($item) {
		if (!$item || !$item.length) {
			return i18n('attendeeDrawerTitle', 'Attendee details');
		}
		var label = $.trim($item.find('.ticket-name .horizon_ticket_label').first().text());
		if (!label) {
			label = $.trim($item.find('.ticket-name').clone().children().remove().end().text()).replace(/\s+/g, ' ');
		}
		if (!label) {
			label = getTicketKeyFromItem($item);
		}
		return label;
	}

	function getItemQty($item) {
		if (!$item || !$item.length) {
			return 0;
		}
		return parseInt($item.find('[name="option_qty[]"]').first().val(), 10) || 0;
	}

	function findTicketItemByKey($root, ticketKey) {
		var $found = $();
		$root.find('.mpwem_ticket_type .mep_ticket_item').each(function () {
			if (getTicketKeyFromItem($(this)) === ticketKey) {
				$found = $(this);
				return false;
			}
		});
		return $found;
	}

	function getSavedMap($root) {
		return $root.data('hz-attendee-saved') || {};
	}

	function setTicketSaved($root, ticketKey, saved) {
		var map = getSavedMap($root);
		map[ticketKey] = !!saved;
		$root.data('hz-attendee-saved', map);
	}

	function getDrawerForTicket($root, ticketKey) {
		if (!ticketKey) {
			return $root.children('.horizon_attendee_drawer').first();
		}
		return $root.children('.horizon_attendee_drawer[data-hz-drawer-ticket="' + cssEscape(ticketKey) + '"]');
	}

	function getAttendeeScope($root, ticketKey) {
		if (!ticketKey) {
			return $root.find('.horizon_attendee_drawer__body');
		}
		var $drawer = getDrawerForTicket($root, ticketKey);
		if ($drawer.length) {
			return $drawer.find('.horizon_attendee_drawer__body').children('.mep_attendee_info');
		}
		// Fallback while forms are still under ticket rows (before first relocate).
		return $root.find('.mep_attendee_info[data-hz-ticket-key="' + cssEscape(ticketKey) + '"]');
	}

	function getAttendeeFields($root, ticketKey) {
		return getAttendeeScope($root, ticketKey).find('input, select, textarea').filter(function () {
			var $el = $(this);
			if ($el.prop('disabled') || $el.attr('type') === 'hidden') {
				return false;
			}
			if ($el.closest('.dNone, .mep_attendee_info_hidden').length) {
				return false;
			}
			return true;
		});
	}

	function getRequiredAttendeeFields($root, ticketKey) {
		return getAttendeeFields($root, ticketKey).filter(function () {
			var $el = $(this);
			return !!$el.prop('required') || $el.hasClass('mep-originally-required');
		});
	}

	function areAttendeeFieldsComplete($root, ticketKey) {
		var $fields = getRequiredAttendeeFields($root, ticketKey);
		if (!$fields.length) {
			return true;
		}
		var complete = true;
		$fields.each(function () {
			var $el = $(this);
			var type = ($el.attr('type') || '').toLowerCase();
			if (type === 'checkbox' || type === 'radio') {
				var name = $el.attr('name');
				var $group = $el.closest('.mp_form_item, .mep_checkbox_item, .groupCheckBox');
				if ($group.length) {
					if (!$group.find('input[name="' + name + '"]:checked').length && !$el.is(':checked')) {
						complete = false;
						return false;
					}
				} else if (!$el.is(':checked')) {
					complete = false;
					return false;
				}
				return;
			}
			if ($.trim(String($el.val() || '')) === '') {
				complete = false;
				return false;
			}
		});
		return complete;
	}

	function markInvalidAttendeeFields($root, ticketKey) {
		var $first = null;
		getAttendeeFields($root, ticketKey).removeClass('is-hz-invalid');
		getRequiredAttendeeFields($root, ticketKey).each(function () {
			var $el = $(this);
			var type = ($el.attr('type') || '').toLowerCase();
			var invalid = false;
			if (type === 'checkbox' || type === 'radio') {
				var name = $el.attr('name');
				var $group = $el.closest('.mp_form_item, .mep_checkbox_item, .groupCheckBox');
				invalid = $group.length
					? !$group.find('input[name="' + name + '"]:checked').length && !$el.is(':checked')
					: !$el.is(':checked');
			} else {
				invalid = $.trim(String($el.val() || '')) === '';
			}
			if (invalid) {
				$el.addClass('is-hz-invalid');
				if (!$first) {
					$first = $el;
				}
			}
		});
		return $first;
	}

	function getRegistrationFormId($area) {
		var $form = $area.closest('form');
		var id = $form.attr('id');
		if (!id) {
			id = 'mpwem_registration';
			$form.attr('id', id);
		}
		return id;
	}

	function bindDrawerFieldsToForm($drawerBody, formId) {
		if (!$drawerBody.length || !formId) {
			return;
		}
		$drawerBody.find('input, select, textarea').each(function () {
			$(this).attr('form', formId);
		});
	}

	function unbindDrawerFieldsFromForm($scope) {
		$scope.find('input[form], select[form], textarea[form]').removeAttr('form');
	}

	function drawerTitleId(ticketKey) {
		var safe = String(ticketKey || 'same').replace(/[^a-zA-Z0-9_-]+/g, '-').replace(/^-+|-+$/g, '') || 'ticket';
		return 'horizon_attendee_drawer_title_' + safe;
	}

	/**
	 * One modal instance per ticket type (or a single __same__ modal when
	 * "same attendee" mode is on). Opening ticket A never shows ticket B fields.
	 */
	function ensureAttendeeDrawer($root, $area, ticketKey) {
		ticketKey = ticketKey || '__same__';
		var $drawer = getDrawerForTicket($root, ticketKey);
		if ($drawer.length) {
			if (!$drawer.parent().is($root)) {
				$root.append($drawer);
			}
			return $drawer;
		}

		// Migrate a legacy single shared drawer (no ticket key) once.
		var $legacy = $root.children('.horizon_attendee_drawer:not([data-hz-drawer-ticket])').first();
		if (!$legacy.length && $area && $area.length) {
			$legacy = $area.find('.horizon_attendee_drawer:not([data-hz-drawer-ticket])').first();
		}
		if ($legacy.length) {
			$legacy.attr('data-hz-drawer-ticket', ticketKey);
			$legacy.find('.horizon_attendee_drawer__title').attr('id', drawerTitleId(ticketKey));
			$legacy.find('.horizon_attendee_drawer__panel').attr('aria-labelledby', drawerTitleId(ticketKey));
			if (!$legacy.parent().is($root)) {
				$root.append($legacy);
			}
			return $legacy;
		}

		var titleId = drawerTitleId(ticketKey);
		$drawer = $(
			'<div class="horizon_attendee_drawer" hidden>' +
				'<button type="button" class="horizon_attendee_drawer__backdrop" data-hz-attendee-close aria-label="' + i18n('close', 'Close') + '"></button>' +
				'<div class="horizon_attendee_drawer__panel" role="dialog" aria-modal="true" aria-labelledby="' + titleId + '">' +
					'<div class="horizon_attendee_drawer__head">' +
						'<div>' +
							'<span class="horizon_attendee_drawer__eyebrow">' + i18n('attendeeDetails', 'Enter attendee details') + '</span>' +
							'<h3 class="horizon_attendee_drawer__title" id="' + titleId + '">' + i18n('attendeeDrawerTitle', 'Attendee details') + '</h3>' +
							'<p class="horizon_attendee_drawer__help">' + i18n('attendeeDrawerHelp', 'Complete the required fields for this ticket, then save.') + '</p>' +
						'</div>' +
						'<button type="button" class="horizon_attendee_drawer__close" data-hz-attendee-close aria-label="' + i18n('close', 'Close') + '">' +
							'<i class="fas fa-times" aria-hidden="true"></i>' +
						'</button>' +
					'</div>' +
					'<div class="horizon_attendee_drawer__body"></div>' +
					'<div class="horizon_attendee_drawer__foot">' +
						'<button type="button" class="horizon_attendee_drawer__continue">' + i18n('attendeeContinue', 'Save attendee details') + '</button>' +
					'</div>' +
				'</div>' +
			'</div>'
		);
		$drawer.attr('data-hz-drawer-ticket', ticketKey);
		$root.append($drawer);
		return $drawer;
	}

	function collectAttendeeHomes($area) {
		var homes = [];
		$area.find('.mep_ticket_item > .mep_attendee_info').each(function () {
			homes.push($(this));
		});
		$area.find('.mpwem_booking_panel > .mep_attendee_info').each(function () {
			homes.push($(this));
		});
		$area.find('.mpwem_seat_plan_area .mep_attendee_info').each(function () {
			homes.push($(this));
		});
		return homes;
	}

	function resolveTicketKeyForInfo($info, $area) {
		var existing = $info.attr('data-hz-ticket-key');
		if (existing) {
			return existing;
		}
		if (isSameAttendeeMode($area) || $info.parent().is('.mpwem_booking_panel') || $info.parent().is('.horizon_attendee_drawer__body') && $info.attr('data-hz-same') === '1') {
			if ($info.closest('.mep_ticket_item').length === 0 && ($info.parent().is('.mpwem_booking_panel') || $info.attr('data-hz-same') === '1' || isSameAttendeeMode($area))) {
				return '__same__';
			}
		}
		var $item = $info.closest('.mep_ticket_item');
		if ($item.length) {
			return getTicketKeyFromItem($item);
		}
		if (isSameAttendeeMode($area)) {
			return '__same__';
		}
		return '__same__';
	}

	function restoreAttendeesFromDrawer($root) {
		var $area = $root.find('.mpwem_registration_area').first();
		if (!$area.length) {
			return;
		}

		$root.find('.horizon_attendee_drawer__body').children('.mep_attendee_info').each(function () {
			var $info = $(this);
			var id = $info.attr('data-hz-home-id');
			if (!id) {
				return;
			}
			var $home = $area.find('.hz-attendee-home[data-hz-placeholder="' + id + '"]').first();
			if ($home.length) {
				unbindDrawerFieldsFromForm($info);
				$home.before($info);
			}
		});
	}

	function relocateAttendeesToDrawer($root) {
		var $area = $root.find('.mpwem_registration_area').first();
		if (!$area.length || !hasAttendeeSupport($area)) {
			return;
		}

		var formId = getRegistrationFormId($area);
		var sameMode = isSameAttendeeMode($area);

		$.each(collectAttendeeHomes($area), function (_, $info) {
			if (!$info || !$info.length || $info.closest('.horizon_attendee_drawer').length) {
				return;
			}
			var id = $info.attr('data-hz-home-id');
			if (!id) {
				id = 'hz-home-' + Math.random().toString(36).slice(2, 10);
				$info.attr('data-hz-home-id', id);
			}
			var $item = $info.closest('.mep_ticket_item');
			var ticketKey = sameMode || !$item.length ? '__same__' : getTicketKeyFromItem($item);
			$info.attr('data-hz-ticket-key', ticketKey);
			if (ticketKey === '__same__') {
				$info.attr('data-hz-same', '1');
			}
			var $home = $area.find('.hz-attendee-home[data-hz-placeholder="' + id + '"]').first();
			if (!$home.length) {
				$home = $('<div class="hz-attendee-home" data-hz-placeholder="' + id + '" hidden></div>');
				$info.after($home);
			}
			$home.attr('data-hz-ticket-key', ticketKey);

			var $drawer = ensureAttendeeDrawer($root, $area, ticketKey);
			var $drawerBody = $drawer.find('.horizon_attendee_drawer__body');
			$drawerBody.append($info);
		});

		// Move panels that landed in the wrong ticket modal (e.g. after upgrading
		// from a shared drawer) into the drawer that matches their ticket key.
		$root.find('.horizon_attendee_drawer__body > .mep_attendee_info').each(function () {
			var $info = $(this);
			var ticketKey = $info.attr('data-hz-ticket-key') || (sameMode ? '__same__' : '');
			if (!ticketKey) {
				var homeId = $info.attr('data-hz-home-id');
				var $home = homeId ? $area.find('.hz-attendee-home[data-hz-placeholder="' + homeId + '"]').first() : $();
				ticketKey = ($home.attr('data-hz-ticket-key') || '') || (sameMode ? '__same__' : '');
			}
			if (!ticketKey) {
				return;
			}
			$info.attr('data-hz-ticket-key', ticketKey);
			var $targetDrawer = ensureAttendeeDrawer($root, $area, ticketKey);
			var $targetBody = $targetDrawer.find('.horizon_attendee_drawer__body');
			if (!$info.parent().is($targetBody)) {
				$targetBody.append($info);
			}
		});

		// Merge any orphaned form clones left in home placeholders back into their panel.
		$area.find('.hz-attendee-home').each(function () {
			var $home = $(this);
			var id = $home.attr('data-hz-placeholder');
			var ticketKey = $home.attr('data-hz-ticket-key') || '__same__';
			var $drawerBody = getDrawerForTicket($root, ticketKey).find('.horizon_attendee_drawer__body');
			var $info = $drawerBody.children('.mep_attendee_info[data-hz-home-id="' + id + '"]');
			if ($info.length && $home.children().length) {
				$info.append($home.children());
			}
		});

		$root.find('.horizon_attendee_drawer__body .mep_attendee_info, .horizon_attendee_drawer__body .mep_form_item')
			.addClass('horizon_attendee_block is-hz-active-ticket');
		$root.find('.horizon_attendee_drawer__body').each(function () {
			bindDrawerFieldsToForm($(this), formId);
			enhanceAttendeeFormCards($(this));
		});
	}

	function ticketHasAttendeeForms($root, ticketKey) {
		return getAttendeeScope($root, ticketKey).find('.mep_form_item').length > 0;
	}

	function setActiveTicketInDrawer($root, ticketKey) {
		var $drawer = getDrawerForTicket($root, ticketKey);
		if (!$drawer.length) {
			return;
		}

		var title = i18n('attendeeDrawerTitle', 'Attendee details');
		var label = '';
		if (ticketKey === '__same__') {
			label = title;
		} else {
			var $item = findTicketItemByKey($root, ticketKey);
			label = getTicketLabelFromItem($item);
			title = i18n('attendeeForTicket', 'Attendees for %s').replace('%s', label);
		}
		$drawer.find('.horizon_attendee_drawer__title').text(title);
		$drawer.attr('data-hz-active-ticket', ticketKey || '');
		$root.data('hz-active-ticket-key', ticketKey || '');
	}

	function openAttendeeDrawer($root, ticketKey) {
		var $area = $root.find('.mpwem_registration_area').first();
		if (!$area.length) {
			return;
		}
		relocateAttendeesToDrawer($root);
		if (!ticketKey) {
			ticketKey = isSameAttendeeMode($area) ? '__same__' : ($root.data('hz-active-ticket-key') || '');
		}
		if (!ticketKey || !ticketHasAttendeeForms($root, ticketKey)) {
			return;
		}

		// Close every other ticket modal so only this ticket type is visible.
		$root.find('.horizon_attendee_drawer').each(function () {
			var $other = $(this);
			if (($other.attr('data-hz-drawer-ticket') || '') !== ticketKey) {
				$other.attr('hidden', true);
			}
		});

		var $drawer = ensureAttendeeDrawer($root, $area, ticketKey);
		setActiveTicketInDrawer($root, ticketKey);
		$drawer.find('.horizon_attendee_drawer__body .mep_attendee_info').addClass('is-hz-active-ticket');
		enhanceAttendeeFormCards($drawer.find('.horizon_attendee_drawer__body'));
		$drawer.removeAttr('hidden');
		$('body').addClass('horizon-attendee-drawer-open horizon-modal-open');
		setTimeout(function () {
			var $focus = getAttendeeScope($root, ticketKey).find('input, select, textarea').filter(':visible').first();
			if ($focus.length) {
				$focus.trigger('focus');
			}
		}, 40);
	}

	function closeAttendeeDrawer($root, ticketKey) {
		var $drawers = ticketKey
			? getDrawerForTicket($root, ticketKey)
			: $root.find('.horizon_attendee_drawer');
		if (!$drawers.length) {
			return;
		}
		$drawers.attr('hidden', true);
		if (!$root.find('.horizon_attendee_drawer:not([hidden])').length) {
			$('body').removeClass('horizon-attendee-drawer-open');
		}
		if (!$root.find('.horizon_modal:not([hidden]), .horizon_attendee_drawer:not([hidden])').length) {
			$('body').removeClass('horizon-modal-open');
		}
		updateAttendeeStatusCards($root);
	}

	function ensureStatusCard($host, ticketKey) {
		var $card = $();
		$host.children('.horizon_attendee_status').each(function () {
			if (($(this).attr('data-hz-ticket-key') || '') === ticketKey) {
				$card = $(this);
				return false;
			}
		});
		if ($card.length) {
			return $card;
		}
		$card = $(
			'<div class="horizon_attendee_status">' +
				'<span class="horizon_attendee_status__icon" aria-hidden="true"><i class="fas fa-user-check"></i></span>' +
				'<span class="horizon_attendee_status__text"></span>' +
				'<button type="button" class="horizon_attendee_status__edit"></button>' +
			'</div>'
		);
		$card.attr('data-hz-ticket-key', ticketKey);
		$host.append($card);
		return $card;
	}

	function updateAttendeeStatusCards($root) {
		var $area = $root.find('.mpwem_registration_area').first();
		if (!$area.length || !hasAttendeeSupport($area)) {
			$root.find('.horizon_attendee_status').remove();
			return;
		}

		relocateAttendeesToDrawer($root);
		var sameMode = isSameAttendeeMode($area);
		var savedMap = getSavedMap($root);

		if (sameMode) {
			$root.find('.mpwem_ticket_type .horizon_attendee_status').remove();
			var $submit = $area.find('.mpwem_form_submit_area').first();
			var $host = $submit.length ? $submit.parent() : $area.find('.mpwem_booking_panel').first();
			var formCount = getAttendeeScope($root, '__same__').find('.mep_form_item').length;
			var totalQty = getTicketQty($root);
			var $card = ensureStatusCard($host, '__same__');
			if ($submit.length && !$card.next().is($submit) && !$submit.find($card).length) {
				$card.insertBefore($submit);
			}
			if (totalQty < 1 || formCount < 1) {
				$card.removeClass('is-visible is-complete is-incomplete').attr('hidden', true);
				return;
			}
			var complete = areAttendeeFieldsComplete($root, '__same__') && !!savedMap.__same__;
			$card.removeAttr('hidden').addClass('is-visible')
				.toggleClass('is-complete', complete)
				.toggleClass('is-incomplete', !complete);
			$card.find('.horizon_attendee_status__text').text(
				complete
					? i18n('attendeeAdded', 'Attendee details added')
					: i18n('attendeeDetails', 'Enter attendee details')
			);
			$card.find('.horizon_attendee_status__edit').text(
				complete ? i18n('attendeeEdit', 'Edit') : i18n('attendeeDetails', 'Enter attendee details')
			);
			return;
		}

		$area.find('.horizon_attendee_status[data-hz-ticket-key="__same__"]').remove();

		$root.find('.mpwem_ticket_type .mep_ticket_item').each(function () {
			var $item = $(this);
			if ($item.closest('.mpwem_ex_service').length) {
				return;
			}
			var key = getTicketKeyFromItem($item);
			var qty = getItemQty($item);
			var formCount = getAttendeeScope($root, key).find('.mep_form_item').length;
			var $card = ensureStatusCard($item, key);

			if (qty < 1 || formCount < 1) {
				$card.removeClass('is-visible is-complete is-incomplete').attr('hidden', true);
				return;
			}

			var complete = areAttendeeFieldsComplete($root, key) && !!savedMap[key];
			$card.removeAttr('hidden').addClass('is-visible')
				.toggleClass('is-complete', complete)
				.toggleClass('is-incomplete', !complete);
			$card.find('.horizon_attendee_status__text').text(
				complete
					? i18n('attendeeAdded', 'Attendee details added')
					: i18n('attendeeDetails', 'Enter attendee details')
			);
			$card.find('.horizon_attendee_status__edit').text(
				complete ? i18n('attendeeEdit', 'Edit') : i18n('attendeeIncomplete', 'Required')
			);
		});
	}

	function getFirstIncompleteTicketKey($root) {
		var $area = $root.find('.mpwem_registration_area').first();
		if (!$area.length) {
			return '';
		}
		if (isSameAttendeeMode($area)) {
			if (getTicketQty($root) > 0 && ticketHasAttendeeForms($root, '__same__') && !areAttendeeFieldsComplete($root, '__same__')) {
				return '__same__';
			}
			if (getTicketQty($root) > 0 && ticketHasAttendeeForms($root, '__same__') && !getSavedMap($root).__same__) {
				return '__same__';
			}
			return '';
		}
		var incomplete = '';
		$root.find('.mpwem_ticket_type .mep_ticket_item').each(function () {
			var $item = $(this);
			if ($item.closest('.mpwem_ex_service').length) {
				return;
			}
			var key = getTicketKeyFromItem($item);
			var qty = getItemQty($item);
			if (qty < 1 || !ticketHasAttendeeForms($root, key)) {
				return;
			}
			if (!areAttendeeFieldsComplete($root, key) || !getSavedMap($root)[key]) {
				incomplete = key;
				return false;
			}
		});
		return incomplete;
	}

	function enhanceAttendeeFormCards($scope) {
		if (!$scope || !$scope.length) {
			return;
		}
		$scope.find('.mep_attendee_info > .mep_form_item').each(function () {
			var $card = $(this);
			if ($card.data('hzCardEnhanced')) {
				var $count = $card.find('.mpwem_ticket_count').first();
				var $name = $card.find('.mpwem_ticket_name').first();
				var countText = $.trim($count.text()) || '1';
				var nameText = $.trim($name.text()) || '';
				$card.find('.horizon_attendee_card__index').text(countText);
				$card.find('.horizon_attendee_card__pill').text('#' + countText);
				if (nameText) {
					$card.find('.horizon_attendee_card__ticket-name').text(nameText);
				}
				return;
			}
			var $h6 = $card.children('h6').first();
			if (!$h6.length) {
				return;
			}
			var ticketName = $.trim($h6.find('.mpwem_ticket_name').first().text()) || '';
			var ticketCount = $.trim($h6.find('.mpwem_ticket_count').first().text()) || '1';
			var $head = $(
				'<div class="horizon_attendee_card__head">' +
					'<span class="horizon_attendee_card__index" aria-hidden="true"></span>' +
					'<div class="horizon_attendee_card__meta">' +
						'<span class="horizon_attendee_card__label"></span>' +
						'<span class="horizon_attendee_card__ticket">' +
							'<span class="horizon_attendee_card__ticket-name"></span>' +
							'<span class="horizon_attendee_card__pill"></span>' +
						'</span>' +
					'</div>' +
				'</div>'
			);
			$head.find('.horizon_attendee_card__index').text(ticketCount);
			$head.find('.horizon_attendee_card__label').text(i18n('attendeeCardLabel', 'Attendee'));
			$head.find('.horizon_attendee_card__ticket-name').text(ticketName || i18n('attendeeDrawerTitle', 'Attendee details'));
			$head.find('.horizon_attendee_card__pill').text('#' + ticketCount);
			// Keep legacy spans (visually hidden) so qty sync / clone scripts still find them.
			var $legacySpans = $h6.find('.mpwem_ticket_name, .mpwem_ticket_count').detach();
			$legacySpans.css({
				position: 'absolute',
				width: '1px',
				height: '1px',
				padding: 0,
				margin: '-1px',
				overflow: 'hidden',
				clip: 'rect(0, 0, 0, 0)',
				border: 0
			});
			$h6.replaceWith($head);
			$head.append($legacySpans);
			$card.data('hzCardEnhanced', 1);
		});
	}

	function enhanceAttendee($root) {
		updateAttendeeStatusCards($root);
		enhanceAttendeeFormCards($root.find('.horizon_attendee_drawer__body'));
	}

	function bindAttendeeDrawer($root) {
		$root.off('click.hzAttendeeEdit').on('click.hzAttendeeEdit', '.horizon_attendee_status__edit, .horizon_attendee_status.is-incomplete', function (e) {
			e.preventDefault();
			var $card = $(this).closest('.horizon_attendee_status');
			var key = $card.attr('data-hz-ticket-key') || '';
			if (!key) {
				return;
			}
			openAttendeeDrawer($root, key);
		});

		$root.off('click.hzAttendeeClose').on('click.hzAttendeeClose', '[data-hz-attendee-close]', function (e) {
			e.preventDefault();
			var ticketKey = $(this).closest('.horizon_attendee_drawer').attr('data-hz-drawer-ticket') || '';
			closeAttendeeDrawer($root, ticketKey);
		});

		$root.off('click.hzAttendeeContinue').on('click.hzAttendeeContinue', '.horizon_attendee_drawer__continue', function (e) {
			e.preventDefault();
			var $drawer = $(this).closest('.horizon_attendee_drawer');
			var ticketKey = $drawer.attr('data-hz-drawer-ticket') ||
				$drawer.attr('data-hz-active-ticket') ||
				$root.data('hz-active-ticket-key') || '';
			var $invalid = markInvalidAttendeeFields($root, ticketKey);
			if ($invalid && $invalid.length) {
				openAttendeeDrawer($root, ticketKey);
				$invalid.trigger('focus');
				return;
			}
			setTicketSaved($root, ticketKey, true);
			closeAttendeeDrawer($root, ticketKey);
		});

		$root.off('input.hzAttendeeChange change.hzAttendeeChange').on(
			'input.hzAttendeeChange change.hzAttendeeChange',
			'.horizon_attendee_drawer__body input, .horizon_attendee_drawer__body select, .horizon_attendee_drawer__body textarea',
			function () {
				$(this).removeClass('is-hz-invalid');
				var ticketKey = $(this).closest('.horizon_attendee_drawer').attr('data-hz-drawer-ticket') ||
					$root.data('hz-active-ticket-key') || '';
				if (ticketKey) {
					setTicketSaved($root, ticketKey, false);
				}
			}
		);
	}

	function enhanceCalendar($root) {
		var providers = {
			google: { cls: 'horizon_cal_google', icon: 'fab fa-google' },
			yahoo: { cls: 'horizon_cal_yahoo', icon: 'fab fa-yahoo' },
			outlook: { cls: 'horizon_cal_outlook', icon: 'fab fa-microsoft' },
			apple: { cls: 'horizon_cal_apple', icon: 'fab fa-apple' }
		};

		$root.find('.horizon_ticket_calendar .mpwem_calender_area').each(function () {
			var $area = $(this);
			var $btn = $area.children('button').first();
			if ($btn.length && !$btn.data('hz-cal')) {
				$btn.data('hz-cal', 1);
				$btn.addClass('horizon_cal_btn');
				var closeText = i18n('addCalendar', 'Add to Calendar');
				$btn.attr('data-close-text', closeText);
				$btn.attr('data-open-text', i18n('hideCalendar', 'Hide Calendar'));

				$btn.find('i').not('.horizon_cal_chevron').remove();
				if (!$btn.find('.horizon_cal_icon').length) {
					$btn.prepend('<span class="horizon_cal_icon"><i class="far fa-calendar-alt" aria-hidden="true"></i></span>');
				}
				var $text = $btn.find('[data-text]');
				if ($text.length) {
					$text.text(closeText);
				} else {
					$btn.append($('<span data-text></span>').text(closeText));
				}
				if (!$btn.find('.horizon_cal_chevron').length) {
					$btn.append('<i class="fas fa-chevron-down horizon_cal_chevron" aria-hidden="true"></i>');
				}

				$btn.off('click.horizonCal').on('click.horizonCal', function () {
					setTimeout(function () {
						var open = $area.find('[data-collapse]:visible').length > 0 ||
							$area.find('[data-collapse]').css('display') !== 'none';
						$btn.toggleClass('is-open', !!open);
					}, 30);
				});
			}

			$area.find('[data-collapse] a').each(function () {
				var $link = $(this);
				if ($link.data('hz-cal-link')) {
					return;
				}
				$link.data('hz-cal-link', 1);
				var key = $.trim($link.text()).toLowerCase();
				var meta = providers[key];
				if (!meta) {
					return;
				}
				$link.addClass(meta.cls);
				if (!$link.find('.horizon_cal_provider_icon').length) {
					$link.prepend('<span class="horizon_cal_provider_icon"><i class="' + meta.icon + '" aria-hidden="true"></i></span>');
				}
			});
		});
	}

	function enhanceShare($root) {
		$root.find('.horizon_ticket_share .share_widgets_title').hide();
		var $foot = $root.find('.horizon_ticket_foot').first();
		if ($foot.length && !$foot.find('.horizon_ticket_foot_row').length) {
			var $share = $foot.children('.horizon_ticket_share').first();
			if ($share.length) {
				$share.wrap('<div class="horizon_ticket_foot_row"></div>');
			}
		}
	}

	function bindLocationModal($root) {
		$root.off('click.horizonModal').on('click.horizonModal', '[data-horizon-modal]', function (e) {
			e.preventDefault();
			var id = $(this).attr('data-horizon-modal');
			var $modal = $('#' + id);
			if (!$modal.length) {
				return;
			}
			$modal.removeAttr('hidden');
			$('body').addClass('horizon-modal-open');
		});

		$root.off('click.horizonModalClose').on('click.horizonModalClose', '[data-horizon-modal-close]', function (e) {
			e.preventDefault();
			$(this).closest('.horizon_modal').attr('hidden', true);
			if (!$('.horizon_modal:not([hidden])').length) {
				$('body').removeClass('horizon-modal-open');
			}
		});

		$(document).off('keydown.horizonModal').on('keydown.horizonModal', function (e) {
			if (e.key !== 'Escape') {
				return;
			}
			if ($('.horizon_attendee_drawer:not([hidden])').length) {
				return;
			}
			$('.horizon_modal:not([hidden])').attr('hidden', true);
			$('body').removeClass('horizon-modal-open');
		});
	}

	function refreshTicketUi($root) {
		enhanceTickets($root);
		enhanceSummary($root);
		updateCta($root);
		enhanceAttendee($root);
	}

	function getTicketQty($root) {
		var totalQty = 0;
		$root.find('.mpwem_ticket_type [name="option_qty[]"]').each(function () {
			totalQty += parseInt($(this).val(), 10) || 0;
		});
		return totalQty;
	}

	function maybeAutoOpenAttendeeDrawer($root, ticketKey, prevItemQty, nextItemQty) {
		var $area = $root.find('.mpwem_registration_area').first();
		if (!$area.length) {
			return;
		}
		relocateAttendeesToDrawer($root);

		// Same-attendee setting (yes/must): only one form is required.
		// Open popup once on first ticket qty; further qty / other ticket types must not reopen it.
		if (isSameAttendeeMode($area)) {
			ticketKey = '__same__';
			if (!ticketHasAttendeeForms($root, ticketKey)) {
				return;
			}
			var prevTotal = parseInt($root.data('hz-prev-total-qty'), 10);
			if (isNaN(prevTotal)) {
				prevTotal = 0;
			}
			var nextTotal = getTicketQty($root);
			if (nextTotal < 1) {
				setTicketSaved($root, ticketKey, false);
				$root.removeData('hz-same-attendee-opened');
				updateAttendeeStatusCards($root);
				return;
			}
			// First selection only (0 → 1+). Never reopen on extra qty or other ticket types.
			if (prevTotal === 0 && nextTotal > 0 && !$root.data('hz-same-attendee-opened')) {
				$root.data('hz-same-attendee-opened', 1);
				openAttendeeDrawer($root, ticketKey);
			}
			return;
		}

		if (!ticketKey) {
			return;
		}
		if (!ticketHasAttendeeForms($root, ticketKey)) {
			return;
		}
		// Per-ticket mode: open when qty increases for that ticket type.
		if ((nextItemQty || 0) > (prevItemQty || 0) && nextItemQty > 0) {
			setTicketSaved($root, ticketKey, false);
			openAttendeeDrawer($root, ticketKey);
		}
	}

	function buildStarRow(score) {
		var n = Math.max(0, Math.min(5, parseInt(score, 10) || 0));
		var html = '';
		var i;
		for (i = 1; i <= 5; i++) {
			html += '<span class="' + (i <= n ? 'is-filled' : 'is-empty') + '" aria-hidden="true">★</span>';
		}
		return $('<span class="horizon_reviews_stars" role="img" aria-label="' + n + ' out of 5 stars"></span>').html(html);
	}

	function ratingFromImageSrc(src) {
		var match = String(src || '').match(/\/(\d)\.png(?:\?|$)/i);
		if (match) {
			return parseInt(match[1], 10);
		}
		return 0;
	}

	function enhanceReviews($root) {
		var $panel = $root.find('.horizon_reviews_panel').first();
		if (!$panel.length || $panel.data('hz-reviews-ready')) {
			return;
		}
		$panel.data('hz-reviews-ready', 1);

		var $list = $panel.find('.mep-event-review-list').first();
		var $items = $list.find('.mep-event-review-item');
		var $btn = $panel.find('#give-review-btn').first();
		var $avg = $list.find('.mep-event-review-avg').first();
		var count = $items.length;
		var countLabel = '';
		var isEmpty = count === 0;

		if ($avg.length) {
			var headerCount = parseInt($.trim($avg.find('.mep-avg-review-header h4 span').first().text()), 10);
			if (!isNaN(headerCount) && headerCount > 0) {
				count = headerCount;
				isEmpty = false;
			}
			if (!$avg.find('.mep-avg-review-item').length) {
				$avg.hide();
			}
		}

		if (count === 1) {
			countLabel = i18n('reviewSingular', '1 review');
		} else if (count > 1) {
			countLabel = i18n('reviewPlural', '%d reviews').replace('%d', String(count));
		}

		var summaryScore = 0;
		var scored = 0;
		$items.each(function () {
			var score = ratingFromImageSrc($(this).find('.mep-event-review-rating img').attr('src'));
			if (score > 0) {
				summaryScore += score;
				scored += 1;
			}
		});
		var avgScore = scored ? Math.round((summaryScore / scored) * 10) / 10 : 0;
		var avgStars = scored ? Math.round(summaryScore / scored) : 0;

		$panel.toggleClass('is-empty', isEmpty);
		$panel.toggleClass('has-reviews', !isEmpty);

		if (!$panel.children('.horizon_reviews_head').length) {
			var $head = $(
				'<div class="horizon_reviews_head">' +
					'<div class="horizon_reviews_head__copy">' +
						'<span class="horizon_reviews_eyebrow"></span>' +
						'<h2 class="horizon_reviews_title"></h2>' +
						'<div class="horizon_reviews_meta"></div>' +
					'</div>' +
					'<div class="horizon_reviews_head__actions"></div>' +
				'</div>'
			);
			$head.find('.horizon_reviews_eyebrow').text(i18n('reviewsEyebrow', 'Reviews'));
			$head.find('.horizon_reviews_title').text(i18n('reviewsTitle', 'What attendees say'));

			var $meta = $head.find('.horizon_reviews_meta');
			if (!isEmpty && avgStars > 0) {
				var $score = $('<div class="horizon_reviews_score"></div>');
				$score.append(
					$('<span class="horizon_reviews_score__value"></span>').text(
						avgScore % 1 === 0 ? String(avgScore.toFixed(0)) : String(avgScore.toFixed(1))
					)
				);
				$score.append(buildStarRow(avgStars));
				$meta.append($score);
			}
			if (countLabel) {
				$meta.append($('<span class="horizon_reviews_count"></span>').text(countLabel));
			}

			if ($btn.length) {
				$btn.addClass('horizon_reviews_write');
				if (!$btn.find('.horizon_reviews_write__icon').length) {
					$btn.prepend('<span class="horizon_reviews_write__icon" aria-hidden="true">✦</span>');
				}
				$head.find('.horizon_reviews_head__actions').append($btn);
			}
			$panel.prepend($head);
		}

		$items.each(function () {
			var $item = $(this);
			if ($item.data('hz-review-card')) {
				return;
			}
			$item.data('hz-review-card', 1);

			var $imgWrap = $item.find('.mep-event-review-img').first();
			var $avatar = $imgWrap.find('img').first();
			var $name = $imgWrap.find('.mep-event-review-name').first();
			var $rating = $item.find('.mep-event-review-rating').first();
			var $date = $rating.find('.mep-event-review-date').first();
			var score = ratingFromImageSrc($rating.find('img').attr('src'));

			var $top = $('<div class="horizon_review_card__top"></div>');
			if ($avatar.length) {
				$avatar.addClass('horizon_review_avatar');
				$top.append($avatar);
			} else {
				var initial = $.trim($name.text()).charAt(0) || '?';
				$top.append(
					$('<span class="horizon_review_avatar horizon_review_avatar--fallback" aria-hidden="true"></span>').text(initial.toUpperCase())
				);
			}
			var $cardMeta = $('<div class="horizon_review_meta"></div>');
			if ($name.length) {
				$name.addClass('horizon_review_name');
				$cardMeta.append($name);
			}
			if ($date.length) {
				$date.addClass('horizon_review_date');
				$cardMeta.append($date);
			}
			$top.append($cardMeta);
			if (score > 0) {
				$top.append(buildStarRow(score));
			}
			$item.prepend($top);
			$imgWrap.remove();
			$rating.remove();
		});

		var $existingEmpty = $panel.find('.horizon_reviews_empty').first();
		if (isEmpty) {
			if (!$existingEmpty.length) {
				$existingEmpty = $(
					'<div class="horizon_reviews_empty" role="status">' +
						'<div class="horizon_reviews_empty__icon" aria-hidden="true">' +
							'<span>★</span><span>★</span><span>★</span>' +
						'</div>' +
						'<h3 class="horizon_reviews_empty__title"></h3>' +
						'<p class="horizon_reviews_empty__text"></p>' +
					'</div>'
				);
				$list.append($existingEmpty);
			} else if (!$existingEmpty.find('.horizon_reviews_empty__title').length) {
				$existingEmpty
					.attr('role', 'status')
					.empty()
					.append(
						'<div class="horizon_reviews_empty__icon" aria-hidden="true">' +
							'<span>★</span><span>★</span><span>★</span>' +
						'</div>' +
						'<h3 class="horizon_reviews_empty__title"></h3>' +
						'<p class="horizon_reviews_empty__text"></p>'
					);
			}
			$existingEmpty.find('.horizon_reviews_empty__title').text(i18n('noReviewsTitle', 'No reviews yet'));
			$existingEmpty.find('.horizon_reviews_empty__text').text(
				i18n('noReviewsYet', 'Be the first to share your experience.')
			);
		} else {
			$existingEmpty.remove();
		}
	}

	function initHorizonTicket() {
		var $root = $('.horizon_theme');
		if (!$root.length) {
			return;
		}
		$root.closest('.mpwem_wrapper, .mpwem_style, .mep-events-wrapper, .mpwem_container').addClass('horizon_parent_reset');
		$('body').addClass('mep-horizon-active');

		enhanceDateTime($root);
		refreshTicketUi($root);
		enhanceCalendar($root);
		enhanceShare($root);
		bindLocationModal($root);
		bindAttendeeDrawer($root);
		enhanceReviews($root);
	}

	function bindHeroDesc($root) {
		$root.find('.horizon_hero_desc_more').off('click.horizonDesc').on('click.horizonDesc', function () {
			var $btn = $(this);
			var $wrap = $btn.closest('.horizon_hero_desc_wrap');
			var expanded = $wrap.hasClass('is-collapsed') === false;
			if (expanded) {
				$wrap.addClass('is-collapsed');
				$btn.attr('aria-expanded', 'false').text(i18n('loadMore', 'Load more'));
			} else {
				$wrap.removeClass('is-collapsed');
				$btn.attr('aria-expanded', 'true').text(i18n('showLess', 'Show less'));
			}
		});
	}

	function bindFaqAccordion($root) {
		var $list = $root.find('.horizon_faq_list');
		if (!$list.length) {
			return;
		}

		$list.off('click.horizonFaq', '.horizon_faq_trigger').on('click.horizonFaq', '.horizon_faq_trigger', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var $item = $btn.closest('.horizon_faq_item');
			var $answer = $item.children('.horizon_faq_answer').first();
			var willOpen = !$item.hasClass('is-open');

			// Accordion: close siblings
			$item.siblings('.horizon_faq_item.is-open').each(function () {
				var $other = $(this);
				$other.removeClass('is-open');
				$other.children('.horizon_faq_trigger').attr('aria-expanded', 'false');
				$other.children('.horizon_faq_answer').attr('hidden', true);
			});

			$item.toggleClass('is-open', willOpen);
			$btn.attr('aria-expanded', willOpen ? 'true' : 'false');
			if (willOpen) {
				$answer.removeAttr('hidden');
			} else {
				$answer.attr('hidden', true);
			}
		});
	}

	function initHorizonTheme() {
		var $root = $('.horizon_theme');
		if (!$root.length) {
			return;
		}
		bindHeroDesc($root);
		bindFaqAccordion($root);
		initHorizonTicket();
	}

	$(document).ready(initHorizonTheme);
	$(document).on('mpwem_ticket_reload mpwem_registration_reload', function () {
		setTimeout(initHorizonTicket, 80);
	});

	// Core get_mpwem_ticket replaces .mpwem_booking_panel HTML but does not fire
	// mpwem_ticket_reload — re-apply Horizon ticket layout after that AJAX.
	$(document).ajaxComplete(function (event, xhr, settings) {
		if (!$('.horizon_theme').length || !settings) {
			return;
		}
		var payload = settings.data;
		var query = '';
		if (typeof payload === 'string') {
			query = payload;
		} else if (payload && typeof payload === 'object' && typeof $.param === 'function' && !(payload instanceof FormData)) {
			try {
				query = $.param(payload);
			} catch (e) {
				query = '';
			}
		}
		if (!query || query.indexOf('get_mpwem_ticket') === -1) {
			return;
		}
		setTimeout(function () {
			initHorizonTicket();
		}, 60);
	});

	// Restore attendee blocks into ticket DOM before core qty/attendee cloning runs.
	document.addEventListener('click', function (e) {
		var t = e.target;
		if (!t || !t.closest) {
			return;
		}
		if (!t.closest('.horizon_theme')) {
			return;
		}
		if (!t.closest('.incQty, .decQty, .qtyIncDec')) {
			return;
		}
		// Extra services never collect attendee info — leave drawer alone.
		if (t.closest('.mpwem_ex_service')) {
			return;
		}
		var $root = $('.horizon_theme');
		$root.data('hz-prev-total-qty', getTicketQty($root));
		var $item = $(t).closest('.mep_ticket_item');
		if ($item.length) {
			$root.data('hz-focus-ticket-key', getTicketKeyFromItem($item));
			$root.data('hz-prev-item-qty', getItemQty($item));
		}
		restoreAttendeesFromDrawer($root);
	}, true);

	document.addEventListener('focusin', function (e) {
		var t = e.target;
		if (!t || !t.closest || !t.closest('.horizon_theme')) {
			return;
		}
		if (t.closest('.mpwem_ex_service')) {
			return;
		}
		if (!t.matches || !t.matches('.inputIncDec, select[name="option_qty[]"], [name="option_qty[]"]')) {
			return;
		}
		var $root = $('.horizon_theme');
		$root.data('hz-prev-total-qty', getTicketQty($root));
		var $item = $(t).closest('.mep_ticket_item');
		if ($item.length) {
			$root.data('hz-focus-ticket-key', getTicketKeyFromItem($item));
			$root.data('hz-prev-item-qty', getItemQty($item));
		}
	}, true);

	document.addEventListener('change', function (e) {
		var t = e.target;
		if (!t || !t.closest) {
			return;
		}
		if (!t.closest('.horizon_theme')) {
			return;
		}
		if (t.closest('.mpwem_ex_service')) {
			return;
		}
		if (!t.matches || !t.matches('.inputIncDec, select[name="option_qty[]"], [name="option_qty[]"]')) {
			return;
		}
		restoreAttendeesFromDrawer($('.horizon_theme'));
	}, true);

	$(document).on('click', '.horizon_theme .qtyIncDec .incQty, .horizon_theme .qtyIncDec .decQty', function () {
		var $root = $('.horizon_theme');
		var $btn = $(this);
		// Extra service qty must not open the attendee drawer.
		if ($btn.closest('.mpwem_ex_service').length) {
			setTimeout(function () {
				refreshTicketUi($root);
			}, 80);
			return;
		}
		var $item = $btn.closest('.mep_ticket_item');
		var ticketKey = $root.data('hz-focus-ticket-key') || getTicketKeyFromItem($item);
		var prevItemQty = parseInt($root.data('hz-prev-item-qty'), 10);
		if (isNaN(prevItemQty)) {
			prevItemQty = 0;
		}
		setTimeout(function () {
			var $freshItem = findTicketItemByKey($root, ticketKey);
			var nextItemQty = getItemQty($freshItem.length ? $freshItem : $item);
			refreshTicketUi($root);
			maybeAutoOpenAttendeeDrawer($root, ticketKey, prevItemQty, nextItemQty);
		}, 80);
	});

	$(document).on(
		'change input',
		'.horizon_theme .inputIncDec, .horizon_theme select[name="option_qty[]"], .horizon_theme select[name="event_extra_service_qty[]"]',
		function () {
			var $root = $('.horizon_theme');
			var $el = $(this);
			var isExtraService = $el.closest('.mpwem_ex_service').length > 0 ||
				$el.is('[name="event_extra_service_qty[]"]');
			if (isExtraService || !$el.is('[name="option_qty[]"], .inputIncDec')) {
				setTimeout(function () {
					refreshTicketUi($root);
				}, 80);
				return;
			}
			var $item = $el.closest('.mep_ticket_item');
			var ticketKey = $root.data('hz-focus-ticket-key') || getTicketKeyFromItem($item);
			var prevItemQty = parseInt($root.data('hz-prev-item-qty'), 10);
			if (isNaN(prevItemQty)) {
				prevItemQty = 0;
			}
			setTimeout(function () {
				var $freshItem = findTicketItemByKey($root, ticketKey);
				var nextItemQty = getItemQty($freshItem.length ? $freshItem : $item);
				refreshTicketUi($root);
				maybeAutoOpenAttendeeDrawer($root, ticketKey, prevItemQty, nextItemQty);
			}, 80);
		}
	);

	// Block booking until every selected ticket's attendee details are saved.
	document.addEventListener('click', function (e) {
		var t = e.target;
		if (!t || !t.closest) {
			return;
		}
		var bookBtn = t.closest('.horizon_theme .mpwem_book_now');
		if (!bookBtn) {
			return;
		}
		var $root = $('.horizon_theme');
		var $area = $root.find('.mpwem_registration_area').first();
		if (!$area.length || !hasAttendeeSupport($area)) {
			return;
		}
		relocateAttendeesToDrawer($root);
		var incompleteKey = getFirstIncompleteTicketKey($root);
		if (!incompleteKey) {
			return;
		}
		e.preventDefault();
		e.stopImmediatePropagation();
		openAttendeeDrawer($root, incompleteKey);
		var $invalid = markInvalidAttendeeFields($root, incompleteKey);
		if ($invalid && $invalid.length) {
			$invalid.trigger('focus');
		}
		updateAttendeeStatusCards($root);
	}, true);

	$(document).on('keydown.hzAttendeeDrawer', function (e) {
		if (e.key !== 'Escape') {
			return;
		}
		var $root = $('.horizon_theme');
		if ($root.find('.horizon_attendee_drawer:not([hidden])').length) {
			closeAttendeeDrawer($root);
		}
	});

	// Horizon-only review modal (Pro addon markup when template is horizon.php).
	(function initHorizonReviewModal() {
		var modal = document.getElementById('mageModal');
		if (!modal || !modal.classList.contains('merr-modal--horizon')) {
			return;
		}
		var btn = document.getElementById('give-review-btn');

		function openModal(e) {
			if (e) {
				e.preventDefault();
			}
			modal.style.display = 'flex';
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
			document.body.classList.add('mage-modal-open');
		}

		function closeModal() {
			modal.style.display = 'none';
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('mage-modal-open');
		}

		if (btn) {
			btn.addEventListener('click', openModal);
		}

		modal.addEventListener('click', function (e) {
			var closer = e.target.closest('[data-mage-modal-close], .mage-modal-close, .mage-modal-header .close');
			if (closer || e.target.classList.contains('mage-modal-backdrop')) {
				e.preventDefault();
				closeModal();
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && modal.classList.contains('is-open')) {
				closeModal();
			}
		});
	})();
})(jQuery);
