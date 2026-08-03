jQuery(document).ready(function($) {

    let currentPage = 1;
    const i18n = (mep_rsvp_ajax.i18n || {});

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getInitials(name) {
        if (!name) return '?';
        const parts = String(name).trim().split(/\s+/).filter(Boolean);
        if (parts.length === 0) return '?';
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function updateBulkUi() {
        const count = $('.mep-rsvp-cb:checked').length;
        const $btn = $('#mep-do-bulk-action');
        const $count = $('#mep-bulk-count');
        $btn.prop('disabled', count === 0 || $('#mep-bulk-action-selector').val() === '-1');
        $count.text(count > 0 ? count + ' selected' : '');
        $('.mep-rsvp-table tbody tr').removeClass('is-selected');
        $('.mep-rsvp-cb:checked').closest('tr').addClass('is-selected');
    }

    function fetchRSVPs() {
        const loadingHtml = '<tr><td colspan="8" class="mep-rsvp-loading"><span class="mep-rsvp-spinner"></span>' +
            escapeHtml(i18n.loading || 'Loading responses…') + '</td></tr>';
        $('#mep-rsvp-table-body').html(loadingHtml);
        $('#mep-result-count').text(i18n.loading || 'Loading…');

        const data = {
            action: 'mep_fetch_rsvp_responses',
            nonce: mep_rsvp_ajax.nonce,
            paged: currentPage,
            search: $('#mep-rsvp-search').val(),
            event_id: $('#mep-filter-event').val(),
            status: $('#mep-filter-status').val()
        };

        $.post(mep_rsvp_ajax.ajax_url, data, function(response) {
            if (response.success) {
                renderTable(response.data.rsvps);
                renderPagination(response.data.total_pages, response.data.current_page);
                updateStats(response.data);
            } else {
                $('#mep-rsvp-table-body').html(
                    '<tr><td colspan="8" class="mep-rsvp-error">' +
                    escapeHtml(i18n.error_load || 'Could not load RSVP responses.') +
                    '</td></tr>'
                );
            }
        }).fail(function() {
            $('#mep-rsvp-table-body').html(
                '<tr><td colspan="8" class="mep-rsvp-error">' +
                escapeHtml(i18n.error_load || 'Could not load RSVP responses.') +
                '</td></tr>'
            );
        });
    }

    function updateStats(data) {
        const totalFiltered = parseInt(data.total_items, 10) || 0;
        const totalAll = parseInt(data.total_all, 10) || totalFiltered;
        const checked = parseInt(data.total_checked, 10) || 0;
        const pending = Math.max(0, totalAll - checked);
        const rate = totalAll > 0 ? Math.round((checked / totalAll) * 100) : 0;

        $('#mep-total-rsvps').text(totalAll);
        $('#mep-total-checkedin').text(checked);
        $('#mep-total-pending').text(pending);
        $('#mep-checkin-rate').text(rate + '%');

        const resultTpl = totalFiltered === 1
            ? (i18n.result_one || '%d response found')
            : (i18n.result_many || '%d responses found');
        $('#mep-result-count').text(resultTpl.replace('%d', totalFiltered));
    }

    function renderTable(rsvps) {
        let html = '';
        if (!rsvps || rsvps.length === 0) {
            html = '<tr><td colspan="8" class="mep-rsvp-empty"><div class="mep-rsvp-empty-inner">' +
                '<span class="dashicons dashicons-groups"></span>' +
                '<p>' + escapeHtml(i18n.no_results || 'No RSVP responses found.') + '</p>' +
                '<small>' + escapeHtml(i18n.no_results_hint || 'Try adjusting your filters or search.') + '</small>' +
                '</div></td></tr>';
        } else {
            rsvps.forEach(function(rsvp) {
                const checkinBtnClass = rsvp.is_checked_in ? 'mep-btn-checkin is-checked' : 'mep-btn-checkin';
                const checkinBtnText = rsvp.is_checked_in
                    ? '<span class="dashicons dashicons-yes-alt"></span> ' + escapeHtml(i18n.checked_in || 'Checked In')
                    : '<span class="dashicons dashicons-yes"></span> ' + escapeHtml(i18n.check_in || 'Check In');
                const statusBadge = rsvp.is_checked_in
                    ? '<span class="mep-rsvp-badge mep-rsvp-badge-success"><span class="dashicons dashicons-yes-alt"></span>' +
                      escapeHtml(i18n.checked_in || 'Checked In') + '</span>'
                    : '<span class="mep-rsvp-badge mep-rsvp-badge-warning"><span class="dashicons dashicons-clock"></span>' +
                      escapeHtml(i18n.not_checked_in || 'Not Checked In') + '</span>';

                const email = rsvp.email
                    ? '<a href="mailto:' + escapeHtml(rsvp.email) + '">' + escapeHtml(rsvp.email) + '</a>'
                    : '';
                const phone = rsvp.phone
                    ? '<span>' + escapeHtml(rsvp.phone) + '</span>'
                    : '';

                html += '<tr data-id="' + escapeHtml(rsvp.id) + '">' +
                    '<td class="column-cb check-column">' +
                        '<input type="checkbox" class="mep-rsvp-cb" value="' + escapeHtml(rsvp.id) + '" aria-label="Select">' +
                    '</td>' +
                    '<td class="column-name">' +
                        '<div class="mep-rsvp-attendee">' +
                            '<div class="mep-rsvp-avatar">' + escapeHtml(getInitials(rsvp.name)) + '</div>' +
                            '<div class="mep-rsvp-attendee-meta">' +
                                '<strong>' + escapeHtml(rsvp.name) + '</strong>' +
                                email + phone +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '<td class="column-event"><span class="mep-rsvp-event-name">' + escapeHtml(rsvp.event_name || '—') + '</span></td>' +
                    '<td class="column-event-date">' + escapeHtml(rsvp.event_date || '—') + '</td>' +
                    '<td class="column-qty"><span class="mep-rsvp-qty-pill">' + escapeHtml(rsvp.qty) + '</span></td>' +
                    '<td class="column-status">' + statusBadge + '</td>' +
                    '<td class="column-date">' + escapeHtml(rsvp.date) + '</td>' +
                    '<td class="column-actions">' +
                        '<button type="button" class="button ' + checkinBtnClass + '" data-id="' + escapeHtml(rsvp.id) +
                        '" data-status="' + (rsvp.is_checked_in ? 0 : 1) + '">' + checkinBtnText + '</button>' +
                        (rsvp.extra_actions || '') +
                    '</td>' +
                '</tr>';
            });
        }
        $('#mep-rsvp-table-body').html(html);
        $('#mep-select-all').prop('checked', false);
        updateBulkUi();
    }

    function renderPagination(totalPages, page) {
        let html = '';
        if (totalPages > 1) {
            html += '<span class="pagination-links">';

            if (page > 1) {
                html += '<a class="mep-rsvp-page-btn prev-page" href="#" data-page="' + (page - 1) + '">&lsaquo;</a>';
            } else {
                html += '<span class="mep-rsvp-page-btn disabled" aria-hidden="true">&lsaquo;</span>';
            }

            html += '<span class="paging-input"><span class="current-page">' + page + '</span> ' +
                escapeHtml(i18n.of || 'of') + ' <span class="total-pages">' + totalPages + '</span></span>';

            if (page < totalPages) {
                html += '<a class="mep-rsvp-page-btn next-page" href="#" data-page="' + (page + 1) + '">&rsaquo;</a>';
            } else {
                html += '<span class="mep-rsvp-page-btn disabled" aria-hidden="true">&rsaquo;</span>';
            }

            html += '</span>';
        }
        $('#mep-rsvp-pagination').html(html);
    }

    // Initial fetch
    fetchRSVPs();

    // Search
    $('#mep-do-search').on('click', function() {
        currentPage = 1;
        fetchRSVPs();
    });

    $('#mep-rsvp-search').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            currentPage = 1;
            fetchRSVPs();
        }
    });

    // Filters
    $('#mep-filter-event, #mep-filter-status').on('change', function() {
        currentPage = 1;
        fetchRSVPs();
    });

    // Reset
    $('#mep-reset-filters').on('click', function() {
        $('#mep-rsvp-search').val('');
        $('#mep-filter-event').val('');
        $('#mep-filter-status').val('');
        currentPage = 1;
        fetchRSVPs();
    });

    // Pagination
    $(document).on('click', '.mep-rsvp-pagination a', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'), 10);
        fetchRSVPs();
    });

    // Check-in toggle
    $(document).on('click', '.mep-btn-checkin', function(e) {
        e.preventDefault();
        const btn = $(this);
        const id = btn.data('id');
        const newStatus = btn.data('status');

        btn.addClass('updating').prop('disabled', true)
            .html('<span class="dashicons dashicons-update"></span> ' + escapeHtml(i18n.updating || 'Updating…'));

        $.post(mep_rsvp_ajax.ajax_url, {
            action: 'mep_checkin_rsvp',
            nonce: mep_rsvp_ajax.nonce,
            post_id: id,
            status: newStatus
        }, function(response) {
            if (response.success) {
                fetchRSVPs();
            } else {
                alert(i18n.error_status || 'Error updating status.');
                btn.removeClass('updating').prop('disabled', false)
                    .html('<span class="dashicons dashicons-warning"></span> Error');
            }
        });
    });

    // Select all
    $('#mep-select-all').on('change', function() {
        $('.mep-rsvp-cb').prop('checked', $(this).prop('checked'));
        updateBulkUi();
    });

    $(document).on('change', '.mep-rsvp-cb', function() {
        const total = $('.mep-rsvp-cb').length;
        const checked = $('.mep-rsvp-cb:checked').length;
        $('#mep-select-all').prop('checked', total > 0 && total === checked);
        updateBulkUi();
    });

    $('#mep-bulk-action-selector').on('change', updateBulkUi);

    // Bulk actions
    $('#mep-do-bulk-action').on('click', function(e) {
        e.preventDefault();
        const action = $('#mep-bulk-action-selector').val();
        if (action === '-1') {
            alert(i18n.select_bulk || 'Please select a bulk action.');
            return;
        }

        const selectedIds = [];
        $('.mep-rsvp-cb:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert(i18n.select_items || 'Please select at least one item.');
            return;
        }

        if (action === 'delete' && !confirm(i18n.confirm_delete || 'Are you sure you want to delete the selected RSVPs?')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).text(i18n.applying || 'Applying…');

        $.post(mep_rsvp_ajax.ajax_url, {
            action: 'mep_bulk_action_rsvp',
            nonce: mep_rsvp_ajax.nonce,
            bulk_action: action,
            ids: selectedIds
        }, function(response) {
            btn.prop('disabled', false).text(i18n.apply || 'Apply');
            if (response.success) {
                $('#mep-bulk-action-selector').val('-1');
                fetchRSVPs();
            } else {
                alert(i18n.error_bulk || 'Error applying bulk action.');
            }
        });
    });

    // CSV Export
    $('.mep-export-rsvp-csv').on('click', function(e) {
        e.preventDefault();
        const search = $('#mep-rsvp-search').val();
        const event_id = $('#mep-filter-event').val();
        const status = $('#mep-filter-status').val();

        const url = new URL(window.location.href);
        url.searchParams.set('mep_export_rsvps', '1');
        if (search) url.searchParams.set('s', search);
        if (event_id) url.searchParams.set('event_id', event_id);
        if (status) url.searchParams.set('status', status);

        window.location.href = url.toString();
    });

});
