/**
 * MEP Event Calendar - Frontend JavaScript
 * 
 * Initializes FullCalendar instances, handles AJAX event loading,
 * tooltips, search, and category filtering.
 */
(function($) {
    'use strict';

    // Store calendar instances
    var calendars = {};
    var stockRequestCache = {};
    var tooltipPointerState = {};

    function normalizeCalendarLocaleCode(value) {
        var locale = String(value || '').trim();

        if (!locale || locale.toLowerCase() === 'auto') {
            return '';
        }

        return locale.replace(/_/g, '-');
    }

    function resolveCalendarLocaleCode(value) {
        var locale = normalizeCalendarLocaleCode(value);
        var docLocale;
        var browserLocale;

        if (locale) {
            return locale;
        }

        docLocale = normalizeCalendarLocaleCode(document.documentElement && document.documentElement.lang ? document.documentElement.lang : '');
        if (docLocale) {
            return docLocale;
        }

        browserLocale = normalizeCalendarLocaleCode(window.navigator && window.navigator.language ? window.navigator.language : '');
        return browserLocale || '';
    }

    function getSupportedFullCalendarLocaleCode(localeCode) {
        var normalized = normalizeCalendarLocaleCode(localeCode);
        var locales = window.FullCalendar && Array.isArray(window.FullCalendar.globalLocales) ? window.FullCalendar.globalLocales : [];
        var normalizedLower = normalized.toLowerCase();
        var baseCode = normalizedLower.split('-')[0];
        var i;
        var code;

        if (!normalized || !locales.length) {
            return '';
        }

        for (i = 0; i < locales.length; i++) {
            code = normalizeCalendarLocaleCode(locales[i] && locales[i].code ? locales[i].code : '');
            if (code && code.toLowerCase() === normalizedLower) {
                return code;
            }
        }

        for (i = 0; i < locales.length; i++) {
            code = normalizeCalendarLocaleCode(locales[i] && locales[i].code ? locales[i].code : '');
            if (code && code.toLowerCase() === baseCode) {
                return code;
            }
        }

        return '';
    }

    function getCalendarIntlLocale(localeCode) {
        return resolveCalendarLocaleCode(localeCode) || undefined;
    }

    function formatIntlDate(date, localeCode, options) {
        var d = normalizeDateInput(date);
        var locale = getCalendarIntlLocale(localeCode);

        if (!d || isNaN(d.getTime())) {
            return '';
        }

        try {
            return new Intl.DateTimeFormat(locale, options).format(d);
        } catch (err) {
            return d.toLocaleDateString(undefined, options);
        }
    }

    function formatIntlTime(date, localeCode, options) {
        var d = normalizeDateInput(date);
        var locale = getCalendarIntlLocale(localeCode);

        if (!d || isNaN(d.getTime())) {
            return '';
        }

        try {
            return new Intl.DateTimeFormat(locale, options).format(d);
        } catch (err) {
            return d.toLocaleTimeString(undefined, options);
        }
    }

    function addDays(date, amount) {
        var d = normalizeDateInput(date);

        if (!d || isNaN(d.getTime())) {
            return d;
        }

        d = new Date(d.getTime());
        d.setDate(d.getDate() + amount);
        return d;
    }

    function formatIntlRange(startDate, endDate, localeCode, options) {
        var locale = getCalendarIntlLocale(localeCode);
        var start = normalizeDateInput(startDate);
        var end = normalizeDateInput(endDate);
        var formatter;

        if (!start || !end || isNaN(start.getTime()) || isNaN(end.getTime())) {
            return '';
        }

        try {
            formatter = new Intl.DateTimeFormat(locale, options);
            if (typeof formatter.formatRange === 'function') {
                return formatter.formatRange(start, end);
            }

            return formatter.format(start) + ' - ' + formatter.format(end);
        } catch (err) {
            return formatIntlDate(start, localeCode, options) + ' - ' + formatIntlDate(end, localeCode, options);
        }
    }

    function formatCalendarTitle(view, localeCode) {
        var currentStart;
        var currentEnd;

        if (!view) {
            return '';
        }

        currentStart = normalizeDateInput(view.currentStart || view.activeStart || view.start);
        currentEnd = normalizeDateInput(view.currentEnd || view.activeEnd || view.end);

        if (view.type === 'dayGridMonth' || view.type === 'listMonth') {
            return formatIntlDate(currentStart, localeCode, { month: 'long', year: 'numeric' });
        }

        if (view.type === 'timeGridWeek') {
            return formatIntlRange(currentStart, addDays(currentEnd, -1), localeCode, {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        if (view.type === 'timeGridDay') {
            return formatIntlDate(currentStart, localeCode, {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        return formatIntlDate(currentStart, localeCode, { month: 'long', year: 'numeric' });
    }

    function formatCalendarDayHeader(date, viewType, localeCode) {
        var d = normalizeDateInput(date);
        var locale = normalizeCalendarLocaleCode(localeCode).toLowerCase();
        var baseCode = locale.split('-')[0];
        var localizedWeekdays = {
            de: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa']
        };
        var weekdayLabel = '';

        if (d && !isNaN(d.getTime()) && localizedWeekdays[baseCode]) {
            weekdayLabel = localizedWeekdays[baseCode][d.getDay()] || '';
        }

        if (viewType === 'timeGridWeek' || viewType === 'timeGridDay') {
            if (weekdayLabel) {
                return weekdayLabel + ' ' + d.getDate() + '.' + (d.getMonth() + 1) + '.';
            }

            return formatIntlDate(date, localeCode, {
                weekday: 'short',
                month: 'numeric',
                day: 'numeric'
            });
        }

        if (weekdayLabel) {
            return weekdayLabel;
        }

        return formatIntlDate(date, localeCode, { weekday: 'short' });
    }

    function getLocaleUiText(localeCode, key, defaultValue) {
        var locale = normalizeCalendarLocaleCode(localeCode).toLowerCase();
        var baseCode = locale.split('-')[0];
        var translations = {
            bn: {
                today: 'আজ',
                month: 'মাস',
                week: 'সপ্তাহ',
                day: 'দিন',
                list: 'তালিকা',
                allDay: 'সারাদিন',
                noEvents: 'কোনও ইভেন্ট নেই',
                viewAll: 'সব {num}টি ইভেন্ট দেখুন'
            },
            de: {
                today: 'Heute',
                month: 'Monat',
                week: 'Woche',
                day: 'Tag',
                list: 'Liste',
                allDay: 'Ganztagig',
                noEvents: 'Keine Ereignisse',
                viewAll: 'Alle {num} Ereignisse anzeigen'
            }
        };

        if (translations[locale] && translations[locale][key]) {
            return translations[locale][key];
        }

        if (translations[baseCode] && translations[baseCode][key]) {
            return translations[baseCode][key];
        }

        return defaultValue;
    }

    function getCalendarButtonText(localeCode, defaults) {
        return {
            today: getLocaleUiText(localeCode, 'today', defaults.today),
            month: getLocaleUiText(localeCode, 'month', defaults.month),
            week: getLocaleUiText(localeCode, 'week', defaults.week),
            day: getLocaleUiText(localeCode, 'day', defaults.day),
            list: getLocaleUiText(localeCode, 'list', defaults.list)
        };
    }

    function buildMoreLinkText(localeCode, num) {
        return getLocaleUiText(localeCode, 'viewAll', 'View all {num} events').replace('{num}', String(num));
    }

    function updateCalendarToolbarTitle(calendar, localeCode) {
        var titleEl;
        var titleText;

        if (!calendar || !calendar.el || !localeCode) {
            return;
        }

        titleEl = calendar.el.querySelector('.fc-toolbar-title');
        titleText = formatCalendarTitle(calendar.view, localeCode);

        if (titleEl && titleText) {
            titleEl.textContent = titleText;
        }
    }

    function updateCalendarDayHeaders(calendar, localeCode) {
        var cells;
        var viewType;
        var baseDate;

        if (!calendar || !calendar.el || !localeCode || !calendar.view) {
            return;
        }

        cells = calendar.el.querySelectorAll('.fc-col-header-cell');
        viewType = calendar.view.type || '';
        baseDate = calendar.view.activeStart || calendar.view.currentStart || null;

        Array.prototype.forEach.call(cells, function(cell, index) {
            var target = cell.querySelector('.fc-col-header-cell-cushion') || cell.querySelector('.fc-scrollgrid-sync-inner') || cell;
            var headerDate = baseDate ? addDays(baseDate, index) : cell.getAttribute('data-date');
            var label;

            if (!target || !headerDate) {
                return;
            }

            label = formatCalendarDayHeader(headerDate, viewType, localeCode);
            if (label) {
                target.textContent = label;
            }
        });
    }

    function updateCalendarListHeaders(calendar, localeCode) {
        var rows;
        var visibleDates;

        if (!calendar || !calendar.el || !localeCode || !calendar.view || calendar.view.type !== 'listMonth') {
            return;
        }

        rows = calendar.el.querySelectorAll('.fc-list-day');
        visibleDates = getVisibleListHeaderDates(calendar);
        Array.prototype.forEach.call(rows, function(row) {
            var rowIndex = Array.prototype.indexOf.call(rows, row);
            var dateStr = row.getAttribute('data-date') || visibleDates[rowIndex] || '';
            var dateEl = row.querySelector('.fc-list-day-text');
            var sideEl = row.querySelector('.fc-list-day-side-text');
            var dateText;
            var sideText;

            if (!dateStr) {
                return;
            }

            dateText = formatIntlDate(dateStr, localeCode, {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            sideText = formatIntlDate(dateStr, localeCode, {
                weekday: 'long'
            });

            if (dateEl && dateText) {
                dateEl.textContent = dateText;
            }

            if (sideEl && sideText) {
                sideEl.textContent = sideText;
            }
        });
    }

    function getVisibleListHeaderDates(calendar) {
        var map = {};
        var viewStart;
        var viewEnd;
        var events;

        if (!calendar || !calendar.view || typeof calendar.getEvents !== 'function') {
            return [];
        }

        viewStart = normalizeDateInput(calendar.view.currentStart || calendar.view.activeStart || calendar.view.start);
        viewEnd = normalizeDateInput(calendar.view.currentEnd || calendar.view.activeEnd || calendar.view.end);
        events = calendar.getEvents();

        if (!viewStart || !viewEnd || isNaN(viewStart.getTime()) || isNaN(viewEnd.getTime())) {
            return [];
        }

        events.forEach(function(event) {
            var start = normalizeDateInput(event && event.start ? event.start : null);
            var end = normalizeDateInput(event && event.end ? event.end : event.start);
            var cursor;
            var lastDate;

            if (!start || !end || isNaN(start.getTime()) || isNaN(end.getTime())) {
                return;
            }

            if (event.allDay && end.getTime() > start.getTime()) {
                end = new Date(end.getTime() - 1000);
            }

            if (end < viewStart || start >= viewEnd) {
                return;
            }

            if (start < viewStart) {
                start = new Date(viewStart.getTime());
            }

            if (end >= viewEnd) {
                end = new Date(viewEnd.getTime() - 1000);
            }

            cursor = new Date(start.getFullYear(), start.getMonth(), start.getDate());
            lastDate = new Date(end.getFullYear(), end.getMonth(), end.getDate());

            while (cursor <= lastDate) {
                map[formatDateKey(cursor)] = true;
                cursor.setDate(cursor.getDate() + 1);
            }
        });

        return Object.keys(map).sort();
    }

    function localizeCalendarChrome(calendar, localeCode) {
        updateCalendarToolbarTitle(calendar, localeCode);
        updateCalendarDayHeaders(calendar, localeCode);
        updateCalendarListHeaders(calendar, localeCode);
    }

    function applyCalendarLocaleOptions(calendarOptions, localeCode) {
        var supportedLocale = getSupportedFullCalendarLocaleCode(localeCode);
        var locales = window.FullCalendar && Array.isArray(window.FullCalendar.globalLocales) ? window.FullCalendar.globalLocales : [];

        if (!localeCode) {
            return;
        }

        if (locales.length) {
            calendarOptions.locales = locales;
        }

        if (supportedLocale) {
            calendarOptions.locale = supportedLocale;
        }
    }

    $(document).ready(function() {
        if (typeof window.mepCalendar === 'undefined') {
            return;
        }

        initAllCalendars();
        initFilters();
    });

    /**
     * Initialize all calendar containers on the page
     */
    function initAllCalendars() {
        $('.mep-calendar-container').each(function() {
            var $el = $(this);
            var calId = $el.attr('id');
            if (!calId) return;
            if ($el.attr('data-mep-cal-rendered') === 'yes') return;

            resetCalendarContainer($el, calId);

            var style = $el.data('style') || 'full';

            if (style === 'lite') {
                initLiteCalendar($el, calId);
            } else {
                initFullCalendar($el, calId);
            }
        });
    }

    /**
     * Initialize FullCalendar instance
     */
    function initFullCalendar($el, calId) {
        var containerEl = document.getElementById(calId);
        if (!containerEl) return;
        if (typeof window.FullCalendar === 'undefined') {
            renderCalendarError($el);
            return;
        }

        var settings = mepCalendar.settings || {};
        var defaultView = $el.data('default-view') || 'dayGridMonth';
        var firstDay = parseInt($el.data('first-day')) || 0;
        var height = normalizeCalendarHeight($el.data('height') || 'auto');
        var showNav = $el.data('show-navigation') !== 'no';
        var showViewSwitcher = $el.data('show-view-switcher') !== 'no';
        var showYearNav = $el.data('show-year-nav') !== 'no';
        var showPrevNext = $el.data('show-prev-next') !== 'no';
        var hideTooltip = $el.data('hide-tooltip') === 'yes';
        var clickAction = $el.data('event-click') || settings.mep_cal_event_click || 'navigate';
        var expiredColor = $el.data('expired-event-color') || settings.mep_cal_expired_event_color || '#999999';
        var expiredTextColor = $el.data('expired-text-color') || settings.mep_cal_expired_text_color || '#ffffff';
        var expiredOpacity = $el.data('expired-opacity') || settings.mep_cal_expired_opacity || '0.6';
        var locale = resolveCalendarLocaleCode(settings.mep_cal_locale || '');
        var explicitEventColor = String($el.data('event-color') || '').trim();

        // Build header toolbar
        var headerLeftParts = [];
        if (showNav && showPrevNext) headerLeftParts.push('prev,next');
        if (showNav && showYearNav) headerLeftParts.push('prevYear,nextYear');
        if (showNav) headerLeftParts.push('today');
        var headerLeft = headerLeftParts.join(' ');
        var headerCenter = 'title';
        var headerRight = showNav && showViewSwitcher ? 'dayGridMonth,timeGridWeek,timeGridDay,listMonth' : '';

        var calendarOptions = {
            initialView: defaultView,
            firstDay: firstDay,
            height: height,
            headerToolbar: {
                left: headerLeft,
                center: headerCenter,
                right: headerRight
            },
            buttonText: getCalendarButtonText(locale, {
                today: mepCalendar.i18n.today,
                month: mepCalendar.i18n.month,
                week: mepCalendar.i18n.week,
                day: mepCalendar.i18n.day,
                list: mepCalendar.i18n.list
            }),
            noEventsText: getLocaleUiText(locale, 'noEvents', mepCalendar.i18n.noEvents),
            allDayText: getLocaleUiText(locale, 'allDay', mepCalendar.i18n.allDay),
            editable: false,
            selectable: false,
            expandRows: true,
            dayMaxEvents: true,
            navLinks: true,
            eventDisplay: 'block',
            slotEventOverlap: false,
            moreLinkClick: 'popover',
            views: {
                dayGridMonth: {
                    eventDisplay: 'list-item',
                    dayMaxEvents: 4,
                    dayMaxEventRows: 4
                },
                timeGridWeek: {
                    dayMaxEventRows: 2,
                    eventMaxStack: 2
                },
                timeGridDay: {
                    dayMaxEventRows: 2,
                    eventMaxStack: 2
                }
            },

            moreLinkContent: function(args) {
                return {
                    html: '<span class="mep-cal-more-link-text">' + escapeHtml(buildMoreLinkText(locale, args.num)) + '</span>'
                };
            },

            dayHeaderDidMount: function(info) {
                applyCalendarDayAppearance($el, info.el, info.date, true);
            },

            dayCellDidMount: function(info) {
                applyCalendarDayAppearance($el, info.el, info.date, false);
            },

            // Style expired events
            eventDidMount: function(info) {
                var props = info.event.extendedProps || {};
                if (props.isExpired) {
                    info.el.classList.add('mep-cal-expired-event');
                    info.el.style.backgroundColor = expiredColor;
                    info.el.style.borderColor = expiredColor;
                    info.el.style.color = expiredTextColor;
                    info.el.style.opacity = expiredOpacity;
                    if (props.expiredBadge) {
                        info.el.title = mepCalendar.i18n.expired + ': ' + info.event.title;
                    }
                }

                styleCalendarEventCard(info, explicitEventColor);
                stabilizeMonthGridEvent(info);
                stabilizeTimeGridAllDayEvent(info);
            },

            eventsSet: function(events) {
                renderTimeGridDaySummaryButtons($el, calId, events);
                setTimeout(function() {
                    if (calendars[calId]) {
                        localizeCalendarChrome(calendars[calId], locale);
                    }
                }, 0);
            },

            datesSet: function() {
                setTimeout(function() {
                    if (calendars[calId]) {
                        if (typeof calendars[calId].getEvents === 'function') {
                            renderTimeGridDaySummaryButtons($el, calId, calendars[calId].getEvents());
                        }
                        localizeCalendarChrome(calendars[calId], locale);
                    }
                }, 0);
            },

            // Event source via AJAX
            events: function(info, successCallback, failureCallback) {
                loadEvents($el, calId, info, successCallback, failureCallback);
            },

            // Click to navigate to event page
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (clickAction === 'none') {
                    return;
                }
                if (clickAction === 'tooltip') {
                    showTooltip(info.event, info.jsEvent, calId);
                    return;
                }
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },

            // Tooltip on hover
            eventMouseEnter: function(info) {
                if (hideTooltip) return;
                showTooltip(info.event, info.jsEvent, calId);
            },

            eventMouseLeave: function(info) {
                hideTooltipEl(calId);
            },

            // Add event content customization
            eventContent: function(arg) {
                var props = arg.event.extendedProps || {};
                var showBadge = $el.data('show-recurring-badge') !== 'no';
                var hideTime = props.hideTime === 'yes';
                var isTimeGrid = arg.view && isTimeGridViewType(arg.view.type);
                var isMonthGrid = arg.view && arg.view.type === 'dayGridMonth';

                var html = '<div class="mep-cal-event-inner' + (isTimeGrid ? ' mep-cal-event-inner-timegrid' : '') + (isMonthGrid ? ' mep-cal-event-inner-month' : '') + '">';
                
                // Time
                if (!hideTime && !arg.event.allDay) {
                    var displayTime = formatEventTimeText(arg);
                    if (displayTime) {
                        html += '<span class="mep-cal-event-time">' + escapeHtml(displayTime) + ' </span>';
                    }
                }
                
                // Title
                html += '<span class="mep-cal-event-title">' + escapeHtml(arg.event.title) + '</span>';

                // Badge
                if (showBadge && !isMonthGrid) {
                    if (props.eventType === 'everyday') {
                        html += ' <span class="mep-cal-event-badge" title="' + mepCalendar.i18n.recurring + '">â†»</span>';
                    } else if (props.eventType === 'yes') {
                        html += ' <span class="mep-cal-event-badge" title="' + mepCalendar.i18n.multiDate + '">ðŸ“…</span>';
                    }
                    if (props.eventMode === 'online') {
                        html += ' <span class="mep-cal-event-badge" title="' + mepCalendar.i18n.virtual + '">ðŸŒ</span>';
                    }
                }

                // Stock indicator dot
                if (!isMonthGrid && props.showStock === 'yes' && props.regStatus === 'on' && props.totalSeats > 0) {
                    if (props.availableSeats <= 0) {
                        html += ' <span class="mep-cal-stock-dot mep-cal-stock-dot-soldout" title="' + mepCalendar.i18n.soldOut + '">â—</span>';
                    }
                }

                html += '</div>';

                return { html: html };
            }
        };
        applyCalendarLocaleOptions(calendarOptions, locale);

        var calendar = new FullCalendar.Calendar(containerEl, calendarOptions);
        calendar.render();
        calendars[calId] = calendar;
        localizeCalendarChrome(calendar, locale);
        $el.attr('data-mep-cal-rendered', 'yes');
    }

    /**
     * Initialize Lite style calendar (simple month grid)
     */
    function initLiteCalendar($el, calId) {
        // Lite mode still uses FullCalendar but with simpler options
        var containerEl = document.getElementById(calId);
        if (!containerEl) return;
        if (typeof window.FullCalendar === 'undefined') {
            renderCalendarError($el);
            return;
        }

        var firstDay = parseInt($el.data('first-day')) || 0;
        var hideTooltip = $el.data('hide-tooltip') === 'yes';
        var clickAction = $el.data('event-click') || 'navigate';
        var settings = mepCalendar.settings || {};
        var locale = resolveCalendarLocaleCode(settings.mep_cal_locale || '');
        var height = normalizeCalendarHeight($el.data('height') || 'auto');

        var calendarOptions = {
            initialView: 'dayGridMonth',
            firstDay: firstDay,
            height: height,
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'today'
            },
            buttonText: {
                today: getLocaleUiText(locale, 'today', mepCalendar.i18n.today)
            },
            noEventsText: getLocaleUiText(locale, 'noEvents', mepCalendar.i18n.noEvents),
            editable: false,
            selectable: false,
            dayMaxEvents: 3,
            navLinks: false,
            eventDisplay: 'block',

            dayHeaderDidMount: function(info) {
                applyCalendarDayAppearance($el, info.el, info.date, true);
            },

            dayCellDidMount: function(info) {
                applyCalendarDayAppearance($el, info.el, info.date, false);
            },

            events: function(info, successCallback, failureCallback) {
                loadEvents($el, calId, info, successCallback, failureCallback);
            },

            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (clickAction === 'none') {
                    return;
                }
                if (clickAction === 'tooltip') {
                    showTooltip(info.event, info.jsEvent, calId);
                    return;
                }
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },

            eventMouseEnter: function(info) {
                if (hideTooltip) return;
                showTooltip(info.event, info.jsEvent, calId);
            },

            eventMouseLeave: function(info) {
                hideTooltipEl(calId);
            },

            datesSet: function() {
                setTimeout(function() {
                    if (calendars[calId]) {
                        localizeCalendarChrome(calendars[calId], locale);
                    }
                }, 0);
            },

            eventContent: function(arg) {
                var html = '<div class="mep-cal-event-inner mep-cal-lite-event">';
                html += '<span class="mep-cal-event-title">' + escapeHtml(arg.event.title) + '</span>';
                html += '</div>';
                return { html: html };
            }
        };
        applyCalendarLocaleOptions(calendarOptions, locale);

        var calendar = new FullCalendar.Calendar(containerEl, calendarOptions);
        calendar.render();
        calendars[calId] = calendar;
        localizeCalendarChrome(calendar, locale);
        $el.attr('data-mep-cal-rendered', 'yes');
    }

    function resetCalendarContainer($el, calId) {
        if (calendars[calId] && typeof calendars[calId].destroy === 'function') {
            calendars[calId].destroy();
            delete calendars[calId];
        }

        if ($el.attr('data-mep-cal-rendered') === 'yes') {
            $el.empty();
            $el.removeAttr('data-mep-cal-rendered');
        }
    }

    /**
     * Load events via AJAX
     */
    function loadEvents($el, calId, info, successCallback, failureCallback) {
        var data = {
            action: 'mep_calendar_get_events',
            nonce: mepCalendar.nonce,
            visible_start: info && info.startStr ? info.startStr : '',
            visible_end: info && info.endStr ? info.endStr : '',
            view_type: info && info.view && info.view.type ? info.view.type : '',
            cat: $el.data('cat') || '',
            org: $el.data('org') || '',
            tag: $el.data('tag') || '',
            city: $el.data('city') || '',
            country: $el.data('country') || '',
            status: $el.data('status') || 'upcoming',
            event_source: $el.data('event-source') || 'all',
            specific_events: $el.data('specific-events') || '',
            event_limit: $el.data('event-limit') || '-1',
            event_color: $el.data('event-color') || '',
            text_color: $el.data('text-color') || '',
            show_stock_details: $el.data('show-stock') || 'no',
            hide_time: $el.data('hide-time') || 'no',
            split_multi_day: $el.data('split-multi-day') || 'no',
            hide_tooltip: $el.data('hide-tooltip') || 'no',
            show_price: $el.data('show-price') || 'no',
            show_location: $el.data('show-location') || 'no',
            show_organizer: $el.data('show-organizer') || 'no',
            show_expired_events: $el.data('show-expired-events') || 'yes'
        };

        // Add search filter if present
        var $wrapper = $el.closest('.mep-calendar-wrapper');
        var $searchInput = $wrapper.find('.mep-cal-search-input');
        if ($searchInput.length && $searchInput.val()) {
            data.search = $searchInput.val();
        }

        // Add category filter from dropdown
        var $catSelect = $wrapper.find('.mep-cal-category-select');
        if ($catSelect.length && $catSelect.val()) {
            data.cat_filter = $catSelect.val();
        }

        var $orgSelect = $wrapper.find('.mep-cal-organizer-select');
        if ($orgSelect.length && $orgSelect.val()) {
            data.org_filter = $orgSelect.val();
        }

        var $locationInput = $wrapper.find('.mep-cal-location-input');
        if ($locationInput.length && $locationInput.val()) {
            data.location_filter = $locationInput.val();
        }

        var $dateStart = $wrapper.find('.mep-cal-date-start');
        if ($dateStart.length && $dateStart.val()) {
            data.date_start = $dateStart.val();
        }

        var $dateEnd = $wrapper.find('.mep-cal-date-end');
        if ($dateEnd.length && $dateEnd.val()) {
            data.date_end = $dateEnd.val();
        }

        $.ajax({
            url: mepCalendar.ajaxurl,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    successCallback(response.data);
                } else {
                    successCallback([]);
                }
            },
            error: function() {
                failureCallback();
            }
        });
    }

    /**
     * Show tooltip near the event
     */
    function showTooltip(event, jsEvent, calId) {
        var $tooltip = $('#' + calId + '-tooltip');
        if (!$tooltip.length) return;

        var props = event.extendedProps || {};
        tooltipPointerState[calId] = {
            clientX: jsEvent.clientX,
            clientY: jsEvent.clientY
        };
        var html = buildTooltipHtml(event, props);
        
        $tooltip.html(html).show();

        // Position tooltip near mouse
        positionTooltip($tooltip, jsEvent);
        maybeLoadStockDetails(event, calId);
    }

    /**
     * Build tooltip HTML content
     */
    function buildTooltipHtml(event, props) {
        var html = '';

        // Header with thumbnail
        html += '<div class="mep-cal-tooltip-header">';
        if (props.thumbnail) {
            html += '<img class="mep-cal-tooltip-thumb" src="' + escapeHtml(props.thumbnail) + '" alt="" />';
        }
        html += '<div>';
        html += '<div class="mep-cal-tooltip-title">' + escapeHtml(event.title) + '</div>';
        
        // Badges
        html += '<div class="mep-cal-tooltip-badges">';
        if (props.eventType === 'everyday') {
            html += '<span class="mep-cal-tooltip-badge mep-cal-badge-recurring">' + mepCalendar.i18n.recurring + '</span>';
        } else if (props.eventType === 'yes') {
            html += '<span class="mep-cal-tooltip-badge mep-cal-badge-multidate">' + mepCalendar.i18n.multiDate + '</span>';
        }
        if (props.eventMode === 'online') {
            html += '<span class="mep-cal-tooltip-badge mep-cal-badge-virtual">' + mepCalendar.i18n.virtual + '</span>';
        }
        if (props.regStatus === 'on' && props.totalSeats > 0 && props.availableSeats <= 0) {
            html += '<span class="mep-cal-tooltip-badge mep-cal-badge-soldout">' + mepCalendar.i18n.soldOut + '</span>';
        }
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Body
        html += '<div class="mep-cal-tooltip-body">';

        // Date/Time
        if (event.start) {
            var dateStr = formatDate(event.start);
            if (props.hideTime !== 'yes' && !event.allDay) {
                dateStr += ' ' + formatTime(event.start);
                if (event.end) {
                    dateStr += ' - ' + formatTime(event.end);
                }
            }
            html += '<div class="mep-cal-tooltip-row"><i class="far fa-calendar-alt"></i> <span>' + escapeHtml(dateStr) + '</span></div>';
        }

        // Location
        if (props.showLocation === 'yes' && props.location) {
            html += '<div class="mep-cal-tooltip-row"><i class="fas fa-map-marker-alt"></i> <span>' + escapeHtml(props.location) + '</span></div>';
        }

        // Organizer
        if (props.showOrganizer === 'yes' && props.organizer) {
            html += '<div class="mep-cal-tooltip-row"><i class="far fa-user"></i> <span>' + escapeHtml(props.organizer) + '</span></div>';
        }

        // Categories
        if (props.categories && props.categories.length > 0) {
            html += '<div class="mep-cal-tooltip-row"><i class="fas fa-tags"></i> <span>' + escapeHtml(props.categories.join(', ')) + '</span></div>';
        }

        // Price
        if (props.showPrice === 'yes') {
            html += buildPriceHtml(props);
        }

        // Stock Details
        if (props.showStock === 'yes') {
            if (props.stockLoaded === 'no') {
                html += '<div class="mep-cal-tooltip-row"><span>' + escapeHtml((mepCalendar.i18n && mepCalendar.i18n.loadingSeats) ? mepCalendar.i18n.loadingSeats : 'Loading seat details...') + '</span></div>';
            } else if ((props.totalSeats > 0) || (props.ticketTypes && props.ticketTypes.length > 0)) {
                html += buildStockHtml(props);
            }
        }

        html += '</div>';

        return html;
    }

    /**
     * Build stock details HTML
     */
    function buildStockHtml(props) {
        var html = '<div class="mep-cal-tooltip-stock">';
        
        // Summary
        var stockClass = 'mep-cal-stock-available';
        var settings = mepCalendar.settings || {};
        var threshold = parseInt(settings.mep_cal_low_stock_threshold) || 5;

        if (props.availableSeats <= 0) {
            stockClass = 'mep-cal-stock-soldout';
        } else if (props.availableSeats <= threshold) {
            stockClass = 'mep-cal-stock-low';
        }

        html += '<div class="mep-cal-tooltip-stock-header">';
        html += '<span>' + mepCalendar.i18n.seats + '</span>';
        html += '<span class="mep-cal-tooltip-stock-summary ' + stockClass + '">';
        html += props.availableSeats + ' / ' + props.totalSeats + ' ' + mepCalendar.i18n.available;
        html += '</span>';
        html += '</div>';

        // Progress bar
        var fillPercent = props.totalSeats > 0 ? ((props.totalSeats - props.availableSeats) / props.totalSeats * 100) : 0;
        var barColor = '#28a745';
        if (props.availableSeats <= 0) {
            barColor = settings.mep_cal_sold_out_color || '#dc3545';
        } else if (props.availableSeats <= threshold) {
            barColor = settings.mep_cal_low_stock_color || '#ffc107';
        }

        html += '<div class="mep-cal-stock-bar"><div class="mep-cal-stock-bar-fill" style="width:' + fillPercent + '%;background:' + barColor + ';"></div></div>';

        // Ticket type breakdown
        if (props.ticketTypes && props.ticketTypes.length > 0) {
            html += '<div class="mep-cal-tooltip-ticket-types">';
            for (var i = 0; i < props.ticketTypes.length; i++) {
                var t = props.ticketTypes[i];
                html += '<div class="mep-cal-ticket-row">';
                html += '<span class="ticket-name">' + escapeHtml(t.name) + '</span>';
                html += '<span class="ticket-avail">' + t.available + '/' + t.total;
                if (props.showPrice === 'yes' && t.priceHtml) {
                    html += ' (' + escapeHtml(t.priceHtml) + ')';
                }
                html += '</span>';
                html += '</div>';
            }
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    /**
     * Build price rows for each ticket type
     */
    function buildPriceHtml(props) {
        var html = '';

        if (props.ticketTypes && props.ticketTypes.length > 0) {
            html += '<div class="mep-cal-tooltip-price-block">';
            html += '<div class="mep-cal-tooltip-price-heading">' + escapeHtml(mepCalendar.i18n.price) + '</div>';

            for (var i = 0; i < props.ticketTypes.length; i++) {
                var ticket = props.ticketTypes[i];
                if (!ticket.priceHtml) {
                    continue;
                }

                html += '<div class="mep-cal-tooltip-price-row">';
                html += '<span class="price-ticket-name">' + escapeHtml(ticket.name) + '</span>';
                html += '<span class="price-ticket-value">' + escapeHtml(ticket.priceHtml) + '</span>';
                html += '</div>';
            }

            html += '</div>';
            return html;
        }

        if (props.minPriceHtml) {
            html += '<div class="mep-cal-tooltip-row"><i class="fas fa-tag"></i> <span><strong>' + mepCalendar.i18n.price + ':</strong> ' + escapeHtml(props.minPriceHtml) + '</span></div>';
        }

        return html;
    }

    /**
     * Position tooltip near the mouse event
     */
    function positionTooltip($tooltip, jsEvent) {
        var x = jsEvent.clientX;
        var y = jsEvent.clientY;
        var tooltipW = $tooltip.outerWidth();
        var tooltipH = $tooltip.outerHeight();
        var winW = $(window).width();
        var winH = $(window).height();

        // Position to the right of cursor by default
        var left = x + 15;
        var top = y - 10;

        // If would overflow right, show to the left
        if (left + tooltipW > winW - 10) {
            left = x - tooltipW - 15;
        }

        // If would overflow bottom, move up
        if (top + tooltipH > winH - 10) {
            top = winH - tooltipH - 10;
        }

        // Don't go above viewport
        if (top < 10) {
            top = 10;
        }

        // Don't go left of viewport
        if (left < 10) {
            left = 10;
        }

        $tooltip.css({
            left: left + 'px',
            top: top + 'px'
        });
    }

    function maybeLoadStockDetails(event, calId) {
        var props = event.extendedProps || {};
        var cacheKey;

        if (props.showStock !== 'yes' || !props.eventId || !props.eventDate) {
            return;
        }

        if (props.stockLoaded === 'yes') {
            return;
        }

        cacheKey = String(props.eventId) + '|' + String(props.eventDate);

        if (stockRequestCache[cacheKey]) {
            applyStockDataToEvent(event, stockRequestCache[cacheKey]);
            refreshTooltipContent(event, calId);
            return;
        }

        $.ajax({
            url: mepCalendar.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'mep_calendar_get_event_stock',
                nonce: mepCalendar.nonce,
                event_id: props.eventId,
                event_date: props.eventDate,
                show_price: props.showPrice || 'no'
            },
            success: function(response) {
                if (!response || !response.success || !response.data) {
                    return;
                }

                stockRequestCache[cacheKey] = response.data;
                applyStockDataToEvent(event, response.data);
                refreshTooltipContent(event, calId);
            }
        });
    }

    function applyStockDataToEvent(event, stockData) {
        if (!event || !stockData) {
            return;
        }

        var mappedProps = {
            totalSeats: parseInt(stockData.totalSeats, 10) || 0,
            availableSeats: parseInt(stockData.availableSeats, 10) || 0,
            soldSeats: parseInt(stockData.soldSeats, 10) || 0,
            reservedSeats: parseInt(stockData.reservedSeats, 10) || 0,
            ticketTypes: Array.isArray(stockData.ticketTypes) ? stockData.ticketTypes : [],
            stockLoaded: stockData.stockLoaded || 'yes'
        };

        if (typeof stockData.minPriceHtml !== 'undefined' && stockData.minPriceHtml !== null && stockData.minPriceHtml !== '') {
            mappedProps.minPriceHtml = stockData.minPriceHtml;
        }

        Object.keys(mappedProps).forEach(function(key) {
            if (typeof event.setExtendedProp === 'function') {
                event.setExtendedProp(key, mappedProps[key]);
            } else {
                event.extendedProps[key] = mappedProps[key];
            }
        });

        applyStockStylesToEvent(event, $.extend({}, event.extendedProps || {}, mappedProps));
    }

    function refreshTooltipContent(event, calId) {
        var $tooltip = $('#' + calId + '-tooltip');
        var pointer = tooltipPointerState[calId];

        if (!$tooltip.length || !$tooltip.is(':visible')) {
            return;
        }

        $tooltip.html(buildTooltipHtml(event, event.extendedProps || {}));

        if (pointer) {
            positionTooltip($tooltip, pointer);
        }
    }

    function applyStockStylesToEvent(event, props) {
        if (!event || !props || props.isExpired || props.showStock !== 'yes' || props.regStatus !== 'on') {
            return;
        }

        var settings = mepCalendar.settings || {};
        var threshold = parseInt(settings.mep_cal_low_stock_threshold, 10) || 5;
        var defaultBg = props.defaultBackgroundColor || '';
        var defaultBorder = props.defaultBorderColor || defaultBg;
        var defaultText = props.defaultTextColor || '#ffffff';
        var nextBg = defaultBg;
        var nextBorder = defaultBorder;
        var nextText = defaultText;

        if (parseInt(props.totalSeats, 10) > 0) {
            if (parseInt(props.availableSeats, 10) <= 0) {
                nextBg = settings.mep_cal_sold_out_color || '#dc3545';
                nextBorder = nextBg;
            } else if (parseInt(props.availableSeats, 10) <= threshold) {
                nextBg = settings.mep_cal_low_stock_color || '#ffc107';
                nextBorder = nextBg;
                nextText = '#333333';
            }
        }

        if (typeof event.setProp === 'function') {
            event.setProp('backgroundColor', nextBg);
            event.setProp('borderColor', nextBorder);
            event.setProp('textColor', nextText);
        }
    }

    function styleCalendarEventCard(info, explicitEventColor) {
        if (!info || !info.el || !info.event || !info.view) {
            return;
        }

        if (info.view.type === 'dayGridMonth') {
            return;
        }

        var props = info.event.extendedProps || {};
        if (props.isExpired) {
            return;
        }

        var isTimeGrid = isTimeGridViewType(info.view.type);
        var baseColor = explicitEventColor || props.paletteAccent || info.event.backgroundColor || props.defaultBackgroundColor || '#3a87ad';

        if (!explicitEventColor) {
            baseColor = props.paletteAccent || getEventPaletteColor(info.event, baseColor);
        }

        var palette = buildSoftEventPalette(baseColor);
        var mainEl = info.el.querySelector('.fc-event-main');

        info.el.style.setProperty('--mep-cal-event-accent', palette.accent);
        info.el.style.setProperty('--mep-cal-event-soft-bg', palette.softBackground);
        info.el.style.setProperty('--mep-cal-event-soft-border', palette.softBorder);
        info.el.style.setProperty('--mep-cal-event-soft-text', palette.text);
        info.el.style.setProperty('--mep-cal-event-dot', palette.accent);
        info.el.style.backgroundColor = palette.softBackground;
        info.el.style.borderColor = palette.softBorder;
        info.el.style.color = palette.text;
        info.el.style.borderLeftColor = palette.accent;

        if (mainEl) {
            mainEl.style.color = palette.text;
        }

        if (!isTimeGrid) {
            return;
        }
    }

    function isTimeGridViewType(viewType) {
        return viewType === 'timeGridWeek' || viewType === 'timeGridDay';
    }

    function stabilizeTimeGridAllDayEvent(info) {
        var nodes = [];
        var innerEl;
        var titleEl;
        var harnessEl;

        if (!info || !info.el || !info.view || !isTimeGridViewType(info.view.type) || !info.event || !info.event.allDay) {
            return;
        }

        harnessEl = info.el.closest('.fc-daygrid-event-harness');
        innerEl = info.el.querySelector('.mep-cal-event-inner');
        titleEl = info.el.querySelector('.mep-cal-event-title');

        nodes.push(info.el);
        if (harnessEl) {
            nodes.push(harnessEl);
        }

        [
            info.el.querySelector('.fc-event-main'),
            info.el.querySelector('.fc-event-main-frame'),
            info.el.querySelector('.fc-event-title-container'),
            info.el.querySelector('.fc-event-title'),
            innerEl,
            titleEl
        ].forEach(function(node) {
            if (node) {
                nodes.push(node);
            }
        });

        nodes.forEach(function(node) {
            node.style.maxWidth = '100%';
            node.style.minWidth = '0';
            node.style.overflow = 'hidden';
            node.style.boxSizing = 'border-box';
        });

        if (innerEl) {
            innerEl.style.display = 'flex';
            innerEl.style.alignItems = 'center';
            innerEl.style.gap = '4px';
            innerEl.style.whiteSpace = 'nowrap';
        }

        if (titleEl) {
            titleEl.style.display = 'block';
            titleEl.style.whiteSpace = 'nowrap';
            titleEl.style.textOverflow = 'ellipsis';
        }
    }

    function getEventPaletteColor(event, fallbackColor) {
        var props = event && event.extendedProps ? event.extendedProps : {};
        var seed = String(
            (props.eventId || event.id || '') +
            '|' +
            (event.title || '') +
            '|' +
            ((props.categories && props.categories[0]) ? props.categories[0] : '')
        );
        var hash = Math.abs(hashString(seed || fallbackColor || 'mep-calendar-event'));
        var hue = hash % 360;
        var saturation = 62 + (hash % 12);
        var lightness = 44 + (hash % 8);

        return hslToHex(hue, saturation, lightness);
    }

    function buildSoftEventPalette(color) {
        var accent = normalizeHexColor(color) || '#3a87ad';

        return {
            accent: accent,
            softBackground: hexToRgba(accent, 0.18),
            softBorder: hexToRgba(accent, 0.92),
            text: shadeHexColor(accent, -0.42) || accent
        };
    }

    function normalizeHexColor(color) {
        if (!color) {
            return '';
        }

        color = String(color).trim();
        if (!/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(color)) {
            return '';
        }

        if (color.length === 4) {
            return '#' + color.charAt(1) + color.charAt(1) + color.charAt(2) + color.charAt(2) + color.charAt(3) + color.charAt(3);
        }

        return color.toLowerCase();
    }

    function hexToRgba(color, alpha) {
        var hex = normalizeHexColor(color);
        if (!hex) {
            return color;
        }

        var r = parseInt(hex.substr(1, 2), 16);
        var g = parseInt(hex.substr(3, 2), 16);
        var b = parseInt(hex.substr(5, 2), 16);

        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    function shadeHexColor(color, percent) {
        var hex = normalizeHexColor(color);
        if (!hex) {
            return color;
        }

        var factor = typeof percent === 'number' ? percent : 0;
        var r = parseInt(hex.substr(1, 2), 16);
        var g = parseInt(hex.substr(3, 2), 16);
        var b = parseInt(hex.substr(5, 2), 16);

        r = clampColorChannel(Math.round(r * (1 + factor)));
        g = clampColorChannel(Math.round(g * (1 + factor)));
        b = clampColorChannel(Math.round(b * (1 + factor)));

        return '#' + toHex(r) + toHex(g) + toHex(b);
    }

    function clampColorChannel(value) {
        return Math.max(0, Math.min(255, value));
    }

    function toHex(value) {
        return value.toString(16).padStart(2, '0');
    }

    function hslToHex(h, s, l) {
        var hue = ((h % 360) + 360) % 360;
        var sat = Math.max(0, Math.min(100, s)) / 100;
        var light = Math.max(0, Math.min(100, l)) / 100;
        var c = (1 - Math.abs(2 * light - 1)) * sat;
        var x = c * (1 - Math.abs((hue / 60) % 2 - 1));
        var m = light - c / 2;
        var r = 0;
        var g = 0;
        var b = 0;

        if (hue < 60) {
            r = c; g = x; b = 0;
        } else if (hue < 120) {
            r = x; g = c; b = 0;
        } else if (hue < 180) {
            r = 0; g = c; b = x;
        } else if (hue < 240) {
            r = 0; g = x; b = c;
        } else if (hue < 300) {
            r = x; g = 0; b = c;
        } else {
            r = c; g = 0; b = x;
        }

        return '#' + toHex(Math.round((r + m) * 255)) + toHex(Math.round((g + m) * 255)) + toHex(Math.round((b + m) * 255));
    }

    function hashString(value) {
        var str = String(value);
        var hash = 0;
        var i;

        for (i = 0; i < str.length; i++) {
            hash = ((hash << 5) - hash) + str.charCodeAt(i);
            hash |= 0;
        }

        return hash;
    }

    function renderTimeGridDaySummaryButtons($el, calId, events) {
        var calendar = calendars[calId];
        var viewType;
        var $existingButtons;

        if (!calendar || !calendar.view) {
            return;
        }

        viewType = calendar.view.type;
        $existingButtons = $el.find('.mep-cal-day-summary-button');
        $existingButtons.remove();

        if (!isTimeGridViewType(viewType)) {
            hideDayEventsPopover(calId);
            return;
        }

        $el.find('.fc-timegrid-col').each(function() {
            var colEl = this;
            var dateStr = colEl.getAttribute('data-date');
            var dayEvents;
            var buttonText;
            var buttonEl;

            if (!dateStr) {
                return;
            }

            dayEvents = getEventsForDate(events, dateStr);
            if (!dayEvents.length) {
                return;
            }

            buttonText = 'View all ' + dayEvents.length + ' events';
            buttonEl = document.createElement('button');
            buttonEl.type = 'button';
            buttonEl.className = 'mep-cal-day-summary-button';
            buttonEl.setAttribute('data-calendar-id', calId);
            buttonEl.setAttribute('data-date', dateStr);
            buttonEl.textContent = buttonText;

            colEl.appendChild(buttonEl);
        });
    }

    function stabilizeMonthGridEvent(info) {
        var nodes = [];
        var harnessEl;
        var titleEl;
        var innerEl;

        if (!info || !info.el || !info.view || info.view.type !== 'dayGridMonth') {
            return;
        }

        harnessEl = info.el.closest('.fc-daygrid-event-harness');
        titleEl = info.el.querySelector('.mep-cal-event-title');
        innerEl = info.el.querySelector('.mep-cal-event-inner, .mep-cal-event-inner-month');

        [
            harnessEl,
            info.el,
            info.el.querySelector('.fc-event-main'),
            info.el.querySelector('.fc-event-main-frame'),
            info.el.querySelector('.fc-event-title-container'),
            info.el.querySelector('.fc-event-title'),
            info.el.querySelector('.fc-event-time'),
            titleEl,
            innerEl
        ].forEach(function(node) {
            if (node) {
                nodes.push(node);
            }
        });

        nodes.forEach(function(node) {
            node.style.maxWidth = '100%';
            node.style.minWidth = '0';
            node.style.overflow = 'hidden';
            node.style.boxSizing = 'border-box';
        });

        if (harnessEl) {
            harnessEl.style.left = '0';
            harnessEl.style.right = '0';
            harnessEl.style.width = '100%';
            harnessEl.style.maxWidth = '100%';
        }

        info.el.style.width = '100%';
        info.el.style.maxWidth = '100%';
        info.el.style.display = 'flex';
        info.el.style.alignItems = 'center';

        if (titleEl) {
            titleEl.style.display = 'block';
            titleEl.style.width = '100%';
            titleEl.style.whiteSpace = 'nowrap';
            titleEl.style.textOverflow = 'ellipsis';
        }
    }

    function getEventsForDate(events, dateStr) {
        var list = Array.isArray(events) ? events.slice() : [];

        return list.filter(function(event) {
            return eventOccursOnDate(event, dateStr);
        }).sort(function(a, b) {
            var startA = a && a.start ? a.start.getTime() : 0;
            var startB = b && b.start ? b.start.getTime() : 0;

            return startA - startB;
        });
    }

    function eventOccursOnDate(event, dateStr) {
        var start;
        var end;
        var dateStart;
        var dateEnd;

        if (!event || !event.start || !dateStr) {
            return false;
        }

        start = new Date(event.start);
        end = event.end ? new Date(event.end) : new Date(event.start);
        dateStart = new Date(dateStr + 'T00:00:00');
        dateEnd = new Date(dateStr + 'T23:59:59');

        if (event.allDay && end.getTime() > start.getTime()) {
            end = new Date(end.getTime() - 1000);
        }

        return start <= dateEnd && end >= dateStart;
    }

    function ensureDayEventsPopover($el, calId) {
        var $popover = $('#' + calId + '-day-events-popover');

        if ($popover.length) {
            return $popover;
        }

        $popover = $('<div/>', {
            id: calId + '-day-events-popover',
            class: 'mep-cal-day-events-popover',
            css: { display: 'none' }
        });

        $el.closest('.mep-calendar-wrapper').append($popover);
        return $popover;
    }

    function showDayEventsPopover($el, calId, dateStr, triggerEl) {
        var calendar = calendars[calId];
        var $popover = ensureDayEventsPopover($el, calId);
        var events;
        var html;

        if (!calendar || !calendar.view) {
            return;
        }

        events = getEventsForDate(calendar.getEvents(), dateStr);
        if (!events.length) {
            hideDayEventsPopover(calId);
            return;
        }

        html = buildDayEventsPopoverHtml(events, dateStr);
        $popover.html(html).show();
        positionDayEventsPopover($popover, triggerEl);
    }

    function hideDayEventsPopover(calId) {
        $('#' + calId + '-day-events-popover').hide();
    }

    function buildDayEventsPopoverHtml(events, dateStr) {
        var html = '';
        var i;

        html += '<div class="mep-cal-day-popover-header">';
        html += '<span class="mep-cal-day-popover-title">All Events</span>';
        html += '<button type="button" class="mep-cal-day-popover-close" aria-label="Close">&times;</button>';
        html += '</div>';
        html += '<div class="mep-cal-day-popover-date">' + escapeHtml(formatDate(dateStr)) + '</div>';
        html += '<div class="mep-cal-day-popover-list">';

        for (i = 0; i < events.length; i++) {
            html += buildDayEventsPopoverItem(events[i]);
        }

        html += '</div>';

        return html;
    }

    function buildDayEventsPopoverItem(event) {
        var props = event && event.extendedProps ? event.extendedProps : {};
        var accent = props.paletteAccent || getEventPaletteColor(event, event.backgroundColor || '#3a87ad');
        var timeText = getEventSummaryTimeText(event);
        var url = event && event.url ? String(event.url) : '';
        var wrapperTag = url ? 'a' : 'div';
        var wrapperAttr = url ? ' href="' + escapeHtml(url) + '"' : '';
        var html = '';

        html += '<' + wrapperTag + ' class="mep-cal-day-popover-item"' + wrapperAttr + '>';
        html += '<span class="mep-cal-day-popover-dot" style="background:' + escapeHtml(accent) + ';"></span>';
        html += '<span class="mep-cal-day-popover-content">';
        html += '<span class="mep-cal-day-popover-item-title">' + escapeHtml(event.title) + '</span>';
        if (timeText) {
            html += '<span class="mep-cal-day-popover-item-time">' + escapeHtml(timeText) + '</span>';
        }
        html += '</span>';
        html += '</' + wrapperTag + '>';

        return html;
    }

    function positionDayEventsPopover($popover, triggerEl) {
        var rect;
        var popoverW;
        var popoverH;
        var left;
        var top;
        var viewportW = $(window).width();
        var viewportH = $(window).height();

        if (!$popover.length || !triggerEl || !triggerEl.getBoundingClientRect) {
            return;
        }

        rect = triggerEl.getBoundingClientRect();
        popoverW = $popover.outerWidth();
        popoverH = $popover.outerHeight();
        left = rect.right - popoverW;
        top = rect.bottom - popoverH - 12;

        if (left < 12) {
            left = 12;
        }

        if (left + popoverW > viewportW - 12) {
            left = viewportW - popoverW - 12;
        }

        if (top < 12) {
            top = rect.top + 12;
        }

        if (top + popoverH > viewportH - 12) {
            top = viewportH - popoverH - 12;
        }

        $popover.css({
            left: left + 'px',
            top: top + 'px'
        });
    }

    function getEventSummaryTimeText(event) {
        if (!event || !event.start) {
            return '';
        }

        if (event.allDay || (event.extendedProps && event.extendedProps.hideTime === 'yes')) {
            return mepCalendar.i18n && mepCalendar.i18n.allDay ? mepCalendar.i18n.allDay : 'All day';
        }

        if (event.end) {
            return formatTime(event.start) + ' - ' + formatTime(event.end);
        }

        return formatTime(event.start);
    }

    /**
     * Hide tooltip
     */
    function hideTooltipEl(calId) {
        $('#' + calId + '-tooltip').hide();
    }

    /**
     * Show a visible message instead of a blank area if FullCalendar fails to load
     */
    function renderCalendarError($el) {
        $el.addClass('mep-cal-init-error');
        if (!$el.children().length) {
            $el.html('<div class="mep-cal-error-message">Calendar could not load. Please check the FullCalendar script and refresh the page.</div>');
        }
    }

    function applyCalendarDayAppearance($el, cellEl, date, isHeader) {
        if (!cellEl || !date) {
            return;
        }

        var appearance = getCalendarDayAppearance(date);
        var targetEl = getCalendarDayTarget(cellEl, isHeader);

        if (!appearance.color && !appearance.image) {
            return;
        }

        if (appearance.color) {
            targetEl.style.setProperty('background-color', appearance.color, 'important');
        }

        if (appearance.image && !isHeader) {
            targetEl.classList.add('mep-cal-day-has-bg-image');
            targetEl.style.setProperty('background-image', 'linear-gradient(rgba(255,255,255,0.76), rgba(255,255,255,0.76)), url("' + appearance.image.replace(/"/g, '\\"') + '")');
            targetEl.style.setProperty('background-size', 'cover');
            targetEl.style.setProperty('background-position', 'center center');
            targetEl.style.setProperty('background-repeat', 'no-repeat');
        }

        if (appearance.color || appearance.image) {
            targetEl.classList.add('mep-cal-day-customized');
        }
    }

    function getCalendarDayTarget(cellEl, isHeader) {
        if (isHeader) {
            return cellEl.querySelector('.fc-scrollgrid-sync-inner') || cellEl;
        }

        return cellEl.querySelector('.fc-daygrid-day-frame') ||
            cellEl.querySelector('.fc-timegrid-col-frame') ||
            cellEl;
    }

    function getCalendarDayAppearance(date) {
        var settings = mepCalendar.settings || {};
        var weekdayKey = getWeekdayKey(date);
        var dateKey = formatDateKey(date);
        var rules = Array.isArray(settings.mep_cal_day_background_rules) ? settings.mep_cal_day_background_rules : [];
        var appearance = {
            color: settings['mep_cal_weekday_bg_' + weekdayKey] || '',
            image: ''
        };

        for (var i = 0; i < rules.length; i++) {
            if (rules[i] && rules[i].type === 'weekday' && rules[i].value === weekdayKey && rules[i].image) {
                appearance.image = rules[i].image;
            }
        }

        for (var j = 0; j < rules.length; j++) {
            if (rules[j] && rules[j].type === 'date' && rules[j].value === dateKey && rules[j].image) {
                appearance.image = rules[j].image;
                break;
            }
        }

        return appearance;
    }

    function getWeekdayKey(date) {
        var day = new Date(date).getDay();
        var map = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        return map[day] || 'sun';
    }

    function formatDateKey(date) {
        var d = new Date(date);
        var year = d.getFullYear();
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    /**
     * Initialize filter controls
     */
    function initFilters() {
        // Search input with debounce
        var searchTimer;
        $(document).on('input', '.mep-cal-search-input', function() {
            var $input = $(this);
            var calId = $input.data('calendar-id');
            
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                if (calendars[calId]) {
                    calendars[calId].refetchEvents();
                }
            }, 400);
        });

        // Category select filter
        $(document).on('change', '.mep-cal-category-select', function() {
            var calId = $(this).data('calendar-id');
            if (calendars[calId]) {
                calendars[calId].refetchEvents();
            }
        });

        $(document).on('change', '.mep-cal-organizer-select, .mep-cal-date-start, .mep-cal-date-end', function() {
            var calId = $(this).data('calendar-id');
            if (calendars[calId]) {
                calendars[calId].refetchEvents();
            }
        });

        $(document).on('input', '.mep-cal-location-input', function() {
            var $input = $(this);
            var calId = $input.data('calendar-id');

            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                if (calendars[calId]) {
                    calendars[calId].refetchEvents();
                }
            }, 400);
        });

        $(document).on('click', '.mep-cal-reset-button', function() {
            var calId = $(this).data('calendar-id');
            var $wrapper = $(this).closest('.mep-calendar-wrapper');

            $wrapper.find('.mep-cal-search-input').val('');
            $wrapper.find('.mep-cal-category-select').val('');
            $wrapper.find('.mep-cal-organizer-select').val('');
            $wrapper.find('.mep-cal-location-input').val('');
            $wrapper.find('.mep-cal-date-start').val('');
            $wrapper.find('.mep-cal-date-end').val('');

            if (calendars[calId]) {
                calendars[calId].refetchEvents();
            }
        });

        $(document).on('click', '.mep-cal-day-summary-button', function(e) {
            var $button = $(this);
            var calId = $button.data('calendar-id');
            var dateStr = $button.data('date');
            var $calendarEl = $('#' + calId);
            var $popover = $('#' + calId + '-day-events-popover');

            e.preventDefault();
            e.stopPropagation();

            if ($popover.length && $popover.is(':visible') && $popover.data('activeDate') === dateStr) {
                hideDayEventsPopover(calId);
                $popover.removeData('activeDate');
                return;
            }

            showDayEventsPopover($calendarEl, calId, dateStr, this);
            $('#' + calId + '-day-events-popover').data('activeDate', dateStr);
        });

        $(document).on('click', '.mep-cal-day-popover-close', function(e) {
            var $popover = $(this).closest('.mep-cal-day-events-popover');

            e.preventDefault();
            $popover.hide().removeData('activeDate');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.mep-cal-day-events-popover, .mep-cal-day-summary-button').length) {
                $('.mep-cal-day-events-popover').hide().removeData('activeDate');
            }
        });
    }

    /**
     * Format date for display
     */
    function formatDate(date) {
        if (!date) return '';
        return formatIntlDate(date, '', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    /**
     * Format time for display
     */
    function formatTime(date) {
        if (!date) return '';
        return formatIntlTime(date, '', { hour: '2-digit', minute: '2-digit' });
    }

    function normalizeDateInput(date) {
        if (date instanceof Date) {
            return date;
        }

        if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
            return new Date(date + 'T00:00:00');
        }

        return new Date(date);
    }

    /**
     * Use a readable event time instead of FullCalendar's compact month token like "9a"
     */
    function formatEventTimeText(arg) {
        if (!arg || !arg.event || !arg.event.start) return '';

        var startText = formatTime(arg.event.start);
        if (!startText) return '';

        if (arg.view && arg.view.type === 'timeGridDay' && arg.event.end) {
            return startText + ' - ' + formatTime(arg.event.end);
        }

        return startText;
    }

    /**
     * Normalize height values passed from shortcode/admin settings
     */
    function normalizeCalendarHeight(value) {
        if (!value) return 'auto';

        value = String(value).trim();
        if (!value || value === 'auto' || value === 'parent') {
            return value || 'auto';
        }

        if (/^\d+$/.test(value)) {
            return parseInt(value, 10);
        }

        return value;
    }

    /**
     * HTML escape utility
     */
    function escapeHtml(text) {
        if (!text) return '';
        text = String(text);
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Expose init function for AJAX-loaded calendars
    window.mepCalendarInit = initAllCalendars;

})(jQuery);
