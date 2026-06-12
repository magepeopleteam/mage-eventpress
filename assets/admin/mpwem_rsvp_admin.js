jQuery(document).ready(function($) {

    let currentPage = 1;

    function fetchRSVPs() {
        $('#mep-rsvp-table-body').html('<tr><td colspan="8" class="mep-loading-msg">Loading...</td></tr>');
        
        let data = {
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
                $('#mep-total-rsvps').text(response.data.total_items);
                $('#mep-total-checkedin').text(response.data.total_checked);
            } else {
                $('#mep-rsvp-table-body').html('<tr><td colspan="7" class="mep-error-msg">Error loading data.</td></tr>');
            }
        });
    }

    function renderTable(rsvps) {
        let html = '';
        if (rsvps.length === 0) {
            html = '<tr><td colspan="8" style="text-align:center">No RSVP responses found.</td></tr>';
        } else {
            rsvps.forEach(function(rsvp) {
                let checkinBtnClass = rsvp.is_checked_in ? 'mep-btn-checkin is-checked' : 'mep-btn-checkin';
                let checkinBtnText = rsvp.is_checked_in ? '<span class="dashicons dashicons-yes-alt"></span> Checked In' : '<span class="dashicons dashicons-yes"></span> Check In';
                let statusBadge = rsvp.is_checked_in ? '<span class="mep-badge mep-badge-success">Checked In</span>' : '<span class="mep-badge mep-badge-warning">Not Checked In</span>';

                html += `<tr>
                    <th scope="row" class="check-column">
                        <input type="checkbox" class="mep-rsvp-cb" value="${rsvp.id}">
                    </th>
                    <td class="column-name">
                        <strong>${rsvp.name}</strong>
                        <small><a href="mailto:${rsvp.email}">${rsvp.email}</a></small>
                        <small>${rsvp.phone}</small>
                    </td>
                    <td class="column-event">${rsvp.event_name}</td>
                    <td class="column-event-date">${rsvp.event_date ? rsvp.event_date : '-'}</td>
                    <td class="column-qty">${rsvp.qty}</td>
                    <td class="column-status">${statusBadge}</td>
                    <td class="column-date">${rsvp.date}</td>
                    <td class="column-actions">
                        <button class="button ${checkinBtnClass}" data-id="${rsvp.id}" data-status="${rsvp.is_checked_in ? 0 : 1}">${checkinBtnText}</button>
                        ${rsvp.extra_actions}
                    </td>
                </tr>`;
            });
        }
        $('#mep-rsvp-table-body').html(html);
        $('#mep-select-all, #mep-select-all-footer').prop('checked', false);
    }

    function renderPagination(totalPages, currentPage) {
        let html = '';
        if (totalPages > 1) {
            html += `<span class="pagination-links">`;
            
            if (currentPage > 1) {
                html += `<a class="prev-page button" href="#" data-page="${currentPage - 1}">&lsaquo;</a>`;
            } else {
                html += `<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>`;
            }

            html += `<span class="paging-input"><span class="current-page">${currentPage}</span> of <span class="total-pages">${totalPages}</span></span>`;

            if (currentPage < totalPages) {
                html += `<a class="next-page button" href="#" data-page="${currentPage + 1}">&rsaquo;</a>`;
            } else {
                html += `<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>`;
            }

            html += `</span>`;
        }
        $('.mep-rsvp-pagination').html(html);
    }

    // Initial Fetch
    fetchRSVPs();

    // Search and Filters
    $('#mep-do-search').on('click', function() {
        currentPage = 1;
        fetchRSVPs();
    });

    $('#mep-filter-event, #mep-filter-status').on('change', function() {
        currentPage = 1;
        fetchRSVPs();
    });

    // Pagination Click
    $(document).on('click', '.mep-rsvp-pagination a', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        fetchRSVPs();
    });

    // Check-in Toggle
    $(document).on('click', '.mep-btn-checkin', function(e) {
        e.preventDefault();
        let btn = $(this);
        let id = btn.data('id');
        let newStatus = btn.data('status');
        
        btn.addClass('updating').prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Updating...');

        $.post(mep_rsvp_ajax.ajax_url, {
            action: 'mep_checkin_rsvp',
            nonce: mep_rsvp_ajax.nonce,
            post_id: id,
            status: newStatus
        }, function(response) {
            if (response.success) {
                fetchRSVPs(); // Refresh to update counts and statuses correctly
            } else {
                alert('Error updating status.');
                btn.removeClass('updating').prop('disabled', false).html('<span class="dashicons dashicons-warning"></span> Error');
            }
        });
    });

    // Select All
    $('#mep-select-all, #mep-select-all-footer').on('change', function() {
        let isChecked = $(this).prop('checked');
        $('.mep-rsvp-cb').prop('checked', isChecked);
        $('#mep-select-all, #mep-select-all-footer').prop('checked', isChecked);
    });

    // Bulk Actions
    $('#mep-do-bulk-action').on('click', function(e) {
        e.preventDefault();
        let action = $('#mep-bulk-action-selector').val();
        if (action === '-1') {
            alert('Please select a bulk action.');
            return;
        }

        let selectedIds = [];
        $('.mep-rsvp-cb:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one item.');
            return;
        }

        if (action === 'delete' && !confirm('Are you sure you want to delete the selected RSVPs?')) {
            return;
        }

        let btn = $(this);
        btn.prop('disabled', true).text('Applying...');

        $.post(mep_rsvp_ajax.ajax_url, {
            action: 'mep_bulk_action_rsvp',
            nonce: mep_rsvp_ajax.nonce,
            bulk_action: action,
            ids: selectedIds
        }, function(response) {
            btn.prop('disabled', false).text('Apply');
            if (response.success) {
                fetchRSVPs();
            } else {
                alert('Error applying bulk action.');
            }
        });
    });

    // CSV Export
    $('.mep-export-rsvp-csv').on('click', function(e) {
        e.preventDefault();
        let search = $('#mep-rsvp-search').val();
        let event_id = $('#mep-filter-event').val();
        let status = $('#mep-filter-status').val();
        
        let url = new URL(window.location.href);
        url.searchParams.set('mep_export_rsvps', '1');
        if (search) url.searchParams.set('s', search);
        if (event_id) url.searchParams.set('event_id', event_id);
        if (status) url.searchParams.set('status', status);
        
        window.location.href = url.toString();
    });

});
