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

		var $dateSelect = $area.find('#mpwem_date_time, select[name="mpwem_date_time"]').first();
		if ($dateSelect.length && $dateSelect.is('select') && !$dateSelect.data('hz-short')) {
			$dateSelect.data('hz-short', 1);
			$dateSelect.find('option').each(function () {
				var $opt = $(this);
				var val = $opt.attr('value') || $opt.val();
				if (val) {
					$opt.text(formatShortDate(val));
				}
			});
		}

		if ($area.find('select, input[type="text"]').filter(':visible').length <= 1 && !$area.find('.horizon_dt_time').length) {
			var $select = $area.find('select, input').first();
			var $timeWrap = $(
				'<label class="horizon_dt_time horizon_dt_time_display">' +
					'<span>' + i18n('time', 'Time') + '</span>' +
					'<div class="horizon_dt_fake"></div>' +
				'</label>'
			);
			$area.append($timeWrap);

			function syncTime() {
				var raw = $select.is('select') ? ($select.val() || $select.find('option:selected').attr('value')) : $select.val();
				$timeWrap.find('.horizon_dt_fake').text(formatTime(raw) || '—');
			}
			syncTime();
			$select.off('change.horizonDt').on('change.horizonDt', syncTime);
		} else if ($area.find('.horizon_dt_time_display').length && $dateSelect.length) {
			$dateSelect.off('change.horizonDtSync').on('change.horizonDtSync', function () {
				$area.find('.horizon_dt_fake').text(formatTime($(this).val()) || '—');
			});
		}
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
			var qtyVal = parseInt($item.find('.inputIncDec, select[name="option_qty[]"], select[name="event_extra_service_qty[]"]').val(), 10) || 0;

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

	function enhanceAttendee($root) {
		$root.find('.horizon_ticket_body .mep_attendee_info, .horizon_ticket_body .mep_attendee_info_hidden, .horizon_ticket_body .mep_form_item').addClass('horizon_attendee_block');
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

	function initHorizonTheme() {
		var $root = $('.horizon_theme');
		if (!$root.length) {
			return;
		}
		bindHeroDesc($root);
		initHorizonTicket();
	}

	$(document).ready(initHorizonTheme);
	$(document).on('mpwem_ticket_reload mpwem_registration_reload', function () {
		setTimeout(initHorizonTicket, 80);
	});

	$(document).on('click', '.horizon_theme .qtyIncDec .incQty, .horizon_theme .qtyIncDec .decQty', function () {
		var $root = $('.horizon_theme');
		setTimeout(function () {
			refreshTicketUi($root);
		}, 50);
	});

	$(document).on(
		'change input',
		'.horizon_theme .inputIncDec, .horizon_theme select[name="option_qty[]"], .horizon_theme select[name="event_extra_service_qty[]"]',
		function () {
			refreshTicketUi($('.horizon_theme'));
		}
	);
})(jQuery);
