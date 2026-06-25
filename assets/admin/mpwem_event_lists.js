(function ($) {

    const DEFAULT_PER_PAGE = 20;
    const PAGINATION_MODE_KEY = 'mpwem_pagination_mode';
    const PER_PAGE_KEY = 'mpwem_per_page';

    function getSavedPaginationMode() {
        try {
            let m = window.localStorage.getItem(PAGINATION_MODE_KEY);
            return (m === 'numbered' || m === 'loadmore') ? m : 'loadmore';
        } catch (e) {
            return 'loadmore';
        }
    }

    function clampPerPage(n) {
        n = parseInt(n, 10);
        if (isNaN(n) || n < 1) { return DEFAULT_PER_PAGE; }
        return Math.min(100, n);
    }

    function getSavedPerPage() {
        try {
            return clampPerPage(window.localStorage.getItem(PER_PAGE_KEY));
        } catch (e) {
            return DEFAULT_PER_PAGE;
        }
    }

    const state = {
        page: 1,
        perPage: getSavedPerPage(),
        search: '',
        category: '',
        dateFrom: '',
        dateTo: '',
        status: 'all',
        activeStatus: 'all',
        orderby: '',
        order: 'asc',
        maxPages: 1,
        found: 0,
        loading: false,
        paginationMode: getSavedPaginationMode() // 'loadmore' | 'numbered'
    };

    const $body = $('#mpwem_event_list_body');

    function debounce(fn, wait) {
        let t;
        return function () {
            const ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    // Recalculate capacity bars for the given scope (defaults to whole table body).
    function renderCapacityBars($scope) {
        $scope = $scope || $body;
        $scope.find('.mpwem_event_list_capacity').each(function () {
            let capacityNumber = $(this).find('.mpwem_event_list_capacity-number').text().trim(); // e.g. "600/600"
            let parts = capacityNumber.split('/');
            if (parts.length === 2) {
                let current = parseFloat(parts[0]);
                let total = parseFloat(parts[1]);
                if (!isNaN(current) && !isNaN(total) && total > 0) {
                    let percent = (current / total) * 100;
                    percent = Math.min(percent, 100); // max 100%

                    let $fill = $(this).find('.mpwem_event_list_capacity-fill');
                    $fill.css('width', percent + '%');

                    if (percent >= 100) {
                        $fill.css('background-color', '#dc3545'); // red when full
                        $(this).find('.mpwem_event_list_capacity-status').text('Full').css('color', '#dc3545');
                    } else {
                        $fill.css('background-color', '#28a745');
                        $(this).find('.mpwem_event_list_capacity-status').text('Available').css('color', '#28a745');
                    }
                }
            }
        });
    }

    // Reset to page 1 and reload (filters/search/sort/mode changed).
    function reloadEvents() {
        state.page = 1;
        fetchEvents(false);
    }

    // Fetch a page of events from the server. append = add rows; otherwise replace tbody.
    function fetchEvents(append) {
        if (state.loading) {
            return;
        }
        state.loading = true;
        $('#loadMoreBtn').prop('disabled', true);

        if (!append) {
            $body.html('<tr class="mpwem_event_list_loading_row"><td colspan="9" style="text-align:center;padding:30px;">Loading events…</td></tr>');
        }

        $.ajax({
            url: mep_ajax.url,
            type: 'POST',
            data: {
                action: 'mpwem_load_event_list',
                nonce: mep_ajax.nonce,
                page: state.page,
                per_page: state.perPage,
                search: state.search,
                category: state.category,
                date_from: state.dateFrom,
                date_to: state.dateTo,
                status: state.status,
                active_status: state.activeStatus,
                orderby: state.orderby,
                order: state.order
            },
            success: function (response) {
                if (!response || !response.success) {
                    $body.html('<tr><td colspan="9" style="text-align:center;padding:30px;">Unable to load events.</td></tr>');
                    return;
                }
                const data = response.data;
                state.maxPages = Math.max(1, data.max_pages);
                state.found = data.found;

                if (append) {
                    $body.append(data.html || '');
                } else if (!data.html || $.trim(data.html) === '') {
                    $body.html('<tr><td colspan="9" style="text-align:center;padding:30px;">No events found.</td></tr>');
                } else {
                    $body.html(data.html);
                }

                updatePaginationUI();
                renderCapacityBars($body);
            },
            error: function () {
                if (!append) {
                    $body.html('<tr><td colspan="9" style="text-align:center;padding:30px;">An error occurred while loading events.</td></tr>');
                }
            },
            complete: function () {
                state.loading = false;
                $('#loadMoreBtn').prop('disabled', false);
            }
        });
    }

    // Update "Showing X of Y", the Load More button and the numbered nav per mode.
    function updatePaginationUI() {
        let visible = $body.find('.mpwem_event_list_card').length;
        $('#visibleCount').text(visible);
        $('#totalCount').text(state.found);

        if (state.paginationMode === 'numbered') {
            $('#loadMoreBtn').hide();
            renderNumberedPagination();
        } else {
            $('#mpwem_numbered_pagination').empty();
            if (state.page >= state.maxPages || visible >= state.found) {
                $('#loadMoreBtn').hide();
            } else {
                $('#loadMoreBtn').show();
            }
        }
    }

    // Build a windowed numbered pager: « ‹ 1 … 4 5 [6] 7 8 … 20 › »
    function renderNumberedPagination() {
        const $nav = $('#mpwem_numbered_pagination');
        const total = state.maxPages;
        const cur = state.page;
        $nav.empty();
        if (total <= 1) {
            return;
        }

        const chevronLeft = '<svg class="mpwem-pg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>';
        const chevronRight = '<svg class="mpwem-pg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>';

        function btn(label, page, opts) {
            opts = opts || {};
            let $b = $('<button type="button" class="mpwem-pg-btn"></button>').html(label);
            if (opts.nav) { $b.addClass('nav'); }
            if (opts.active) { $b.addClass('active'); }
            if (opts.disabled) { $b.addClass('disabled').prop('disabled', true); }
            if (opts.dots) { $b.addClass('dots').prop('disabled', true); }
            if (!opts.disabled && !opts.dots && page) { $b.attr('data-page', page); }
            return $b;
        }

        $nav.append(btn(chevronLeft, cur - 1, { nav: true, disabled: cur <= 1 }));

        let pages = [];
        const windowSize = 2; // pages on each side of current
        for (let i = 1; i <= total; i++) {
            if (i === 1 || i === total || (i >= cur - windowSize && i <= cur + windowSize)) {
                pages.push(i);
            }
        }
        let prev = 0;
        pages.forEach(function (p) {
            if (prev && p - prev > 1) {
                $nav.append(btn('…', 0, { dots: true }));
            }
            $nav.append(btn(String(p), p, { active: p === cur }));
            prev = p;
        });

        $nav.append(btn(chevronRight, cur + 1, { nav: true, disabled: cur >= total }));
    }

    // Highlight the active pagination-mode button.
    function syncPaginationSwitch() {
        $('.mpwem-pg-mode').removeClass('active');
        $('.mpwem-pg-mode[data-mode="' + state.paginationMode + '"]').addClass('active');
    }

    // Deferred heavy analytics (registrations + revenue).
    function loadDashboardStats() {
        $.ajax({
            url: mep_ajax.url,
            type: 'POST',
            data: {
                action: 'mpwem_dashboard_stats',
                nonce: mep_ajax.nonce
            },
            success: function (response) {
                if (!response || !response.success) {
                    return;
                }
                const d = response.data;
                $('#mpwem_total_registration').removeClass('mpwem-stat-loading').text(d.total_registration);
                $('#mpwem_month_revenue').removeClass('mpwem-stat-loading').text(d.revenue);
                $('#mpwem_registration_trend').text(d.registration_trend);
                $('#mpwem_revenue_trend').text(d.revenue_trend);
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* Filters / search / sort -> reset & reload from the server           */
    /* ------------------------------------------------------------------ */

    $('#mpwem_event_filter_by_category').on('change', function () {
        // Option value is the category slug ('' = All Categories).
        state.category = $(this).val() || '';
        reloadEvents();
    });

    $('#mpwem_search_event_list').on('keyup', debounce(function () {
        state.search = $(this).val().trim();
        reloadEvents();
    }, 350));

    // "Show N events" per-page select (persisted)
    $('#mpwem_per_page').on('change', function () {
        let val = clampPerPage($(this).val());
        state.perPage = val;
        try { window.localStorage.setItem(PER_PAGE_KEY, val); } catch (e) {}
        reloadEvents();
    });

    $('#mpwem_date_from, #mpwem_date_to').on('change', function () {
        state.dateFrom = $('#mpwem_date_from').val();
        state.dateTo = $('#mpwem_date_to').val();
        reloadEvents();
    });

    $('#mpwem_clear_date_filter').on('click', function () {
        $('#mpwem_date_from').val('');
        $('#mpwem_date_to').val('');
        state.dateFrom = '';
        state.dateTo = '';
        reloadEvents();
    });

    $(document).on('click', '.mpwem_filter_by_status', function () {
        $('.mpwem_filter_by_status').removeClass('mpwem_filter_btn_active_bg_color').addClass('mpwem_filter_btn_bg_color');
        $(this).removeClass('mpwem_filter_btn_bg_color').addClass('mpwem_filter_btn_active_bg_color');
        // Reset the active/expired chips when switching post-status.
        $('.mpwem_filter_by_active_status').removeClass('mpwem_filter_btn_active_bg_color');

        state.status = $(this).attr('data-by-filter').toLowerCase();
        state.activeStatus = 'all';
        reloadEvents();
    });

    $(document).on('click', '.mpwem_filter_by_active_status', function () {
        $('.mpwem_filter_by_active_status').removeClass('mpwem_filter_btn_active_bg_color').addClass('mpwem_filter_btn_bg_color');
        $(this).removeClass('mpwem_filter_btn_bg_color').addClass('mpwem_filter_btn_active_bg_color');

        state.activeStatus = $(this).attr('data-by-filter').toLowerCase();
        reloadEvents();
    });

    // Sorting -> server-side
    $('.sortable').on('click', function () {
        let sortBy = $(this).data('sort'); // 'title' or 'date'
        let newDirection = (state.orderby === sortBy && state.order === 'asc') ? 'desc' : 'asc';
        state.orderby = sortBy;
        state.order = newDirection;

        $('.sort-indicator').removeClass('asc desc');
        $(this).find('.sort-indicator').addClass(newDirection);

        reloadEvents();
    });

    // Load more -> next page, append
    $('#loadMoreBtn').on('click', function () {
        if (state.page < state.maxPages) {
            state.page++;
            fetchEvents(true);
        }
    });

    // Numbered pagination -> jump to page, replace rows
    $(document).on('click', '#mpwem_numbered_pagination .mpwem-pg-btn', function () {
        let page = parseInt($(this).attr('data-page'), 10);
        if (!page || page === state.page) {
            return;
        }
        state.page = page;
        fetchEvents(false);
        // Keep the table in view when jumping pages.
        if ($('.table-container').length) {
            $('html, body').animate({ scrollTop: $('.table-container').offset().top - 60 }, 200);
        }
    });

    // Pagination mode switch (persisted in localStorage)
    $(document).on('click', '.mpwem-pg-mode', function () {
        let mode = $(this).attr('data-mode');
        if (mode === state.paginationMode) {
            return;
        }
        state.paginationMode = mode;
        try { window.localStorage.setItem(PAGINATION_MODE_KEY, mode); } catch (e) {}
        syncPaginationSwitch();
        reloadEvents();
    });

    /* ------------------------------------------------------------------ */
    /* Modern confirmation modal                                           */
    /* ------------------------------------------------------------------ */

    function buildConfirmModal() {
        if ($('#mpwem_confirm_modal').length) {
            return $('#mpwem_confirm_modal');
        }
        let html =
            '<div class="mpwem-modal-overlay" id="mpwem_confirm_modal" aria-hidden="true">' +
            '  <div class="mpwem-modal" role="dialog" aria-modal="true" aria-labelledby="mpwem_modal_title">' +
            '    <div class="mpwem-modal__icon">' +
            '      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>' +
            '    </div>' +
            '    <h3 class="mpwem-modal__title" id="mpwem_modal_title"></h3>' +
            '    <p class="mpwem-modal__text"></p>' +
            '    <div class="mpwem-modal__actions">' +
            '      <button type="button" class="mpwem-modal__btn mpwem-modal__btn--cancel"></button>' +
            '      <button type="button" class="mpwem-modal__btn mpwem-modal__btn--confirm"></button>' +
            '    </div>' +
            '  </div>' +
            '</div>';
        return $(html).appendTo('body');
    }

    // Show the modal. opts: { title, text, confirmText, cancelText, onConfirm }
    function mpwemConfirm(opts) {
        opts = opts || {};
        let $modal = buildConfirmModal();
        $modal.find('.mpwem-modal__title').text(opts.title || 'Are you sure?');
        $modal.find('.mpwem-modal__text').text(opts.text || '');
        $modal.find('.mpwem-modal__btn--confirm').text(opts.confirmText || 'Yes, delete');
        $modal.find('.mpwem-modal__btn--cancel').text(opts.cancelText || 'Cancel');

        function close() {
            $modal.removeClass('is-open').attr('aria-hidden', 'true');
            $(document).off('keydown.mpwemConfirm');
            setTimeout(function () { $('body').removeClass('mpwem-modal-open'); }, 200);
        }

        $modal.find('.mpwem-modal__btn--confirm').off('click').on('click', function () {
            close();
            if (typeof opts.onConfirm === 'function') { opts.onConfirm(); }
        });
        $modal.find('.mpwem-modal__btn--cancel').off('click').on('click', close);
        // Click on the dimmed backdrop closes; clicks inside the card do not.
        $modal.off('click').on('click', function (e) {
            if (e.target === this) { close(); }
        });
        $(document).on('keydown.mpwemConfirm', function (e) {
            if (e.key === 'Escape') { close(); }
        });

        $('body').addClass('mpwem-modal-open');
        // Force reflow so the open transition runs.
        $modal.attr('aria-hidden', 'false')[0].offsetWidth;
        $modal.addClass('is-open');
        $modal.find('.mpwem-modal__btn--confirm').focus();
    }

    // Intercept the delete (trash) icon and confirm first.
    $(document).on('click', '.action-btn.delete', function (e) {
        e.preventDefault();
        let href = $(this).attr('href');
        if (!href || href === '#') {
            return;
        }
        let $row = $(this).closest('tr.mpwem_event_list_card');
        let title = ($row.data('event-title') || '').toString().trim();
        mpwemConfirm({
            title: 'Delete this event?',
            text: title
                ? 'Are you sure you want to move "' + title + '" to Trash? You can restore it later from Trash.'
                : 'Are you sure you want to move this event to Trash? You can restore it later from Trash.',
            confirmText: 'Yes, delete',
            cancelText: 'Cancel',
            onConfirm: function () {
                window.location.href = href;
            }
        });
    });

    /* ------------------------------------------------------------------ */
    /* Bulk select / trash (unchanged, uses event delegation)              */
    /* ------------------------------------------------------------------ */

    $(document).on('click', '#mpwem_select_all_post', function () {
        let isChecked = $(this).prop('checked');
        $('.mpwem_select_single_post').prop('checked', isChecked);
        if (isChecked) {
            $('#mpwem_multiple_trash_holder').fadeIn();
        } else {
            $('#mpwem_multiple_trash_holder').fadeOut();
        }
    });

    // When any single checkbox is clicked
    $(document).on('click', '.mpwem_select_single_post', function () {

        let total = $('.mpwem_select_single_post').length;
        let checked = $('.mpwem_select_single_post:checked').length;

        $('#mpwem_select_all_post').prop('checked', total === checked);
        if (checked > 0) {
            $('#mpwem_multiple_trash_holder').fadeIn();
        } else {
            $('#mpwem_multiple_trash_holder').fadeOut();
        }

    });

    $(document).on('click', '#mpwem_multiple_trash_btn', function (e) {
        e.preventDefault();
        let nonce = $('#mpwem_multiple_trash_nonce').val();
        if (!nonce || !mep_ajax || !mep_ajax.nonce) {
            alert('Nonce is missing or invalid.');
            return;
        }

        let selectedIDs = [];
        $('.mpwem_select_single_post:checked').each(function () {
            let idAttr = $(this).attr('id');
            let postId = idAttr.split('_').pop();
            selectedIDs.push(postId);
        });

        if (selectedIDs.length === 0) {
            alert('Please select at least one post to trash.');
            return;
        }

        mpwemConfirm({
            title: 'Delete selected events?',
            text: 'Are you sure you want to move ' + selectedIDs.length + ' selected event' + (selectedIDs.length > 1 ? 's' : '') + ' to Trash? You can restore them later from Trash.',
            confirmText: 'Yes, delete',
            cancelText: 'Cancel',
            onConfirm: function () {
                $.ajax({
                    url: mep_ajax.url,
                    type: 'POST',
                    data: {
                        action: 'mpwem_trash_multiple_posts',
                        post_ids: selectedIDs,
                        nonce: nonce
                    },
                    success: function () {
                        location.reload();
                    },
                    error: function () {
                        alert('An error occurred while trashing posts.');
                    }
                });
            }
        });
    });

    /* ------------------------------------------------------------------ */
    /* Quick Edit (unchanged, uses event delegation)                       */
    /* ------------------------------------------------------------------ */

    $(document).on('click', '.action-btn.quick-edit', function (e) {
        e.preventDefault();
        let $row = $(this).closest('tr');
        // If triggered from the icon, $row may be the <tr> or a <div> inside <td>
        if (!$row.hasClass('mpwem_event_list_card')) {
            $row = $row.closest('tr.mpwem_event_list_card');
        }
        let $quickEditRow = $row.next('.quick-edit-row');
        // Hide all other quick edit rows
        $('.quick-edit-row').hide();
        $('.mpwem_event_list_card').show();
        // Show this quick edit row and hide the main row
        $row.hide();
        $quickEditRow.show();

        // Only handle custom quick edit on the custom event lists page
        if (!$('.mpwem_event_list').length) {
            return;
        }
        // Ensure dropdowns are properly initialized
        $quickEditRow.find('select').each(function () {
            $(this).prop('disabled', false);
            $(this).css({
                'pointer-events': 'auto',
                'z-index': '999',
                'position': 'relative'
            });
        });
        // Focus on first input
        $quickEditRow.find('input[name="post_title"]').focus();
    });

    // Ensure dropdown functionality
    $(document).on('click', '.quick-edit-row select', function (e) {
        e.stopPropagation();
        $(this).focus();
    });

    $(document).on('mousedown', '.quick-edit-row select', function (e) {
        e.stopPropagation();
    });

    // Cancel quick edit
    $(document).on('click', '.quick-edit-row .cancel', function () {
        let $quickEditRow = $(this).closest('.quick-edit-row');
        let $mainRow = $quickEditRow.prev('.mpwem_event_list_card');

        $quickEditRow.hide();
        $mainRow.show();
    });

    // Save quick edit
    $(document).on('click', '.quick-edit-row .save', function () {
        let $button = $(this);
        let $quickEditRow = $button.closest('.quick-edit-row');
        let $mainRow = $quickEditRow.prev('.mpwem_event_list_card');
        let eventId = $quickEditRow.data('event-id');

        // Show spinner
        $quickEditRow.find('.spinner').addClass('is-active');
        $button.prop('disabled', true);

        // Collect form data
        let formData = {
            action: 'mpwem_quick_edit_event',
            post_id: eventId,
            post_title: $quickEditRow.find('input[name="post_title"]').val(),
            event_start_datetime: $quickEditRow.find('input[name="event_start_datetime"]').val(),
            event_end_datetime: $quickEditRow.find('input[name="event_end_datetime"]').val(),
            mep_location_venue: $quickEditRow.find('input[name="mep_location_venue"]').val(),
            post_status: $quickEditRow.find('select[name="_status"]').val(),
            mep_cat: $quickEditRow.find('select[name="mep_cat[]"]').val() || [],
            nonce: $quickEditRow.find('.mep-quick-edit-nonce').val()
        };

        $.ajax({
            url: mep_ajax.url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    // Update the main row with new data
                    location.reload(); // Simple reload for now
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error occurred'));
                }
            },
            error: function () {
                alert('An error occurred while saving the event.');
            },
            complete: function () {
                $quickEditRow.find('.spinner').removeClass('is-active');
                $button.prop('disabled', false);
            }
        });
    });

    /* ------------------------------------------------------------------ */
    /* Init                                                                */
    /* ------------------------------------------------------------------ */
    $(function () {
        let $pp = $('#mpwem_per_page');
        $pp.val(String(state.perPage));
        // If the saved value isn't one of the select options, fall back to the default.
        if ($pp.val() === null) {
            state.perPage = DEFAULT_PER_PAGE;
            $pp.val(String(DEFAULT_PER_PAGE));
        }
        syncPaginationSwitch();
        loadDashboardStats();
        reloadEvents();
    });

}(jQuery));
