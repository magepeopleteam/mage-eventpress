(function ($) {


    // Filter by category
    $('#mpwem_event_filter_by_category').on('change', function() {
        applyFilters();
    });

    // Search events
    $('#mpwem_search_event_list').on('keyup', function() {
        applyFilters();
    });

    // Filter by date range
    $('#mpwem_date_from, #mpwem_date_to').on('change', function() {
        applyFilters();
    });

    // Clear date filter
    $('#mpwem_clear_date_filter').on('click', function() {
        $('#mpwem_date_from').val('');
        $('#mpwem_date_to').val('');
        applyFilters();
    });

    // Combined filter function
    function applyFilters() {
        var selectedCategory = $('#mpwem_event_filter_by_category').val().toLowerCase();
        var searchTerm = $('#mpwem_search_event_list').val().toLowerCase().trim();
        var dateFrom = $('#mpwem_date_from').val();
        var dateTo = $('#mpwem_date_to').val();
        
        var fromTimestamp = dateFrom ? new Date(dateFrom + 'T00:00:00').getTime() / 1000 : null;
        var toTimestamp = dateTo ? new Date(dateTo + 'T23:59:59').getTime() / 1000 : null;
        
        $('.mpwem_event_list_card').each(function() {
            var eventCategory = $(this).data('filter-by-category').toLowerCase();
            var eventName = $(this).data('filter-by-event-name').toLowerCase();
            var eventOrganiser = $(this).data('filter-by-event-organiser').toLowerCase();
            var eventDate = parseInt($(this).data('event-date'));
            
            // Category filter
            var showByCategory = selectedCategory === 'all categories' || eventCategory.includes(selectedCategory);
            
            // Search filter
            var showBySearch = searchTerm === '' || eventName.includes(searchTerm) || eventOrganiser.includes(searchTerm) || eventCategory.includes(searchTerm);
            
            // Date filter
            var showByDate = true;
            if (fromTimestamp && eventDate < fromTimestamp) {
                showByDate = false;
            }
            if (toTimestamp && eventDate > toTimestamp) {
                showByDate = false;
            }
            
            // Show/hide based on all filters
            if (showByCategory && showBySearch && showByDate) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }



    /*const mpwem_itemsPerPage = 2;
    let mpwem_totalItems = $('.mpwem_event_list_card').length;
    let mpwem_visibleItems = 0;
    $('#totalCount').text(mpwem_totalItems);
    $('.mpwem_event_list_card').hide();
    function showMoreItems() {
        let hiddenItems = $('.mpwem_event_list_card:hidden');
        let itemsToShow = hiddenItems.slice(0, mpwem_itemsPerPage);
        itemsToShow.show();
        mpwem_visibleItems = $('.mpwem_event_list_card:visible').length;
        $('#visibleCount').text(mpwem_visibleItems);

        // Hide load more button if all items are visible
        if (mpwem_visibleItems >= mpwem_totalItems) {
            $('#loadMoreBtn').hide();
        }
    }
    showMoreItems();
    $(document).on('click', '#loadMoreBtn', function() {
        showMoreItems();
    });*/

    const itemsPerPage = 20;
    let currentFilteredItems = $('.mpwem_event_list_card');
    let totalVisible = 0;
    function showNextItems() {
        let hiddenItems = currentFilteredItems.filter(':hidden');
        hiddenItems.slice(0, itemsPerPage).fadeIn();

        let currentlyVisible = currentFilteredItems.filter(':visible').length;
        $('#visibleCount').text(currentlyVisible);
        $('#totalCount').text(currentFilteredItems.length);

        // Hide Load More button if all items are shown
        if (currentlyVisible >= currentFilteredItems.length) {
            $('#loadMoreBtn').hide();
        } else {
            $('#loadMoreBtn').show();
        }
    }
    function initialLoad() {
        $('.mpwem_event_list_card').hide();
        currentFilteredItems = $('.mpwem_event_list_card');
        showNextItems();
    }
    initialLoad();
    $('#loadMoreBtn').on('click', function() {
        showNextItems();
    });
    $(document).on('click', '.mpwem_filter_by_status', function () {
        $('.mpwem_filter_by_status').removeClass('mpwem_filter_btn_active_bg_color').addClass('mpwem_filter_btn_bg_color');
        $(this).removeClass('mpwem_filter_btn_bg_color').addClass('mpwem_filter_btn_active_bg_color');

        let searchText = $(this).attr('data-by-filter').toLowerCase();
        $('.mpwem_event_list_card').hide();
        currentFilteredItems = $('.mpwem_event_list_card').filter(function () {
            let status = $(this).data('event-status').toLowerCase();
            return (searchText === 'all' || status.includes(searchText));
        });
        // Reset counter and show first N
        $('#visibleCount').text(0);
        showNextItems();
    });

    $(document).on('click', '.mpwem_filter_by_active_status', function () {
        $('.mpwem_filter_by_active_status').removeClass('mpwem_filter_btn_active_bg_color').addClass('mpwem_filter_btn_bg_color');
        $(this).removeClass('mpwem_filter_btn_bg_color').addClass('mpwem_filter_btn_active_bg_color');

        let searchText = $(this).attr('data-by-filter').toLowerCase();
        $('.mpwem_event_list_card').hide();
        currentFilteredItems = $('.mpwem_event_list_card').filter(function () {
            let status = $(this).data('event-active-status').toLowerCase();
            return (searchText === 'all' || status.includes(searchText));
        });
        // Reset counter and show first N
        $('#visibleCount').text(0);
        showNextItems();
    });

    $(document).on('click', '#mpwem_select_all_post', function() {
        let isChecked = $(this).prop('checked');
        $('.mpwem_select_single_post').prop('checked', isChecked);
        if (isChecked) {
            $('#mpwem_multiple_trash_holder').fadeIn();
        } else {
            $('#mpwem_multiple_trash_holder').fadeOut();
        }
    });

    // When any single checkbox is clicked
    $(document).on('click', '.mpwem_select_single_post', function() {

        let total = $('.mpwem_select_single_post').length;
        let checked = $('.mpwem_select_single_post:checked').length;

        $('#mpwem_select_all_post').prop('checked', total === checked);
        if (checked > 0) {
            $('#mpwem_multiple_trash_holder').fadeIn();
        } else {
            $('#mpwem_multiple_trash_holder').fadeOut();
        }

    });

    $(document).on('click', '#mpwem_multiple_trash_btn', function(e) {
        e.preventDefault();
        let nonce = $('#mpwem_multiple_trash_nonce').val(); 
        if (!nonce || !mep_ajax || !mep_ajax.nonce) {
            alert('Nonce is missing or invalid.');
            return;
        }

        let selectedIDs = [];
        $('.mpwem_select_single_post:checked').each(function() {
            let idAttr = $(this).attr('id');
            let postId = idAttr.split('_').pop();
            selectedIDs.push(postId);
        });

        if (selectedIDs.length === 0) {
            alert('Please select at least one post to trash.');
            return;
        }

        $.ajax({
            url: mep_ajax .url,
            type: 'POST',
            data: {
                action: 'mpwem_trash_multiple_posts',
                post_ids: selectedIDs,
                nonce: nonce
            },
            success: function(response) {
                alert(response.data.message);
                location.reload();
            },
            error: function() {
                alert('An error occurred while trashing posts.');
            }
        });
    });

    $('.mpwem_event_list_capacity').each(function() {
        let capacityNumber = $(this).find('.mpwem_event_list_capacity-number').text().trim(); // e.g. "600/600"
        let parts = capacityNumber.split('/');
        if(parts.length === 2) {
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

    // Sorting functionality
    let sortDirection = {};
    $('.sortable').on('click', function() {
        let sortBy = $(this).data('sort');
        let currentDirection = sortDirection[sortBy] || 'asc';
        let newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        sortDirection[sortBy] = newDirection;

        // Update sort indicators
        $('.sort-indicator').removeClass('asc desc');
        $(this).find('.sort-indicator').addClass(newDirection);

        // Sort the table rows
        let $tbody = $('.event-table tbody');
        let $rows = $tbody.find('.mpwem_event_list_card').get();

        $rows.sort(function(a, b) {
            let aVal, bVal;
            
            if (sortBy === 'date') {
                aVal = parseInt($(a).data('event-date'));
                bVal = parseInt($(b).data('event-date'));
            } else if (sortBy === 'title') {
                aVal = $(a).data('event-title').toLowerCase();
                bVal = $(b).data('event-title').toLowerCase();
            }

            if (newDirection === 'asc') {
                return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
            } else {
                return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
            }
        });

        // Re-append sorted rows (along with their quick-edit rows)
        $.each($rows, function(index, row) {
            let $row = $(row);
            let $quickEditRow = $row.next('.quick-edit-row');
            $tbody.append($row);
            if ($quickEditRow.length) {
                $tbody.append($quickEditRow);
            }
        });
    });

    // Quick Edit functionality
    $(document).on('click', '.editinline, .action-btn.quick-edit', function(e) {
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
        // Ensure dropdowns are properly initialized
        $quickEditRow.find('select').each(function() {
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
    $(document).on('click', '.quick-edit-row select', function(e) {
        e.stopPropagation();
        $(this).focus();
    });
    
    $(document).on('mousedown', '.quick-edit-row select', function(e) {
        e.stopPropagation();
    });

    // Cancel quick edit
    $(document).on('click', '.quick-edit-row .cancel', function() {
        let $quickEditRow = $(this).closest('.quick-edit-row');
        let $mainRow = $quickEditRow.prev('.mpwem_event_list_card');
        
        $quickEditRow.hide();
        $mainRow.show();
    });

    // Save quick edit
    $(document).on('click', '.quick-edit-row .save', function() {
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
            success: function(response) {
                if (response.success) {
                    // Update the main row with new data
                    location.reload(); // Simple reload for now
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error occurred'));
                }
            },
            error: function() {
                alert('An error occurred while saving the event.');
            },
            complete: function() {
                $quickEditRow.find('.spinner').removeClass('is-active');
                $button.prop('disabled', false);
            }
        });
    });



}(jQuery));