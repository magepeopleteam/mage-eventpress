(function ($) {


    $('#mpwem_event_filter_by_category').on('change', function() {
        let filter_value = $(this).val().toLowerCase();

        $('.mpwem_event_list_card').each(function() {
            let categories = $(this).data('filter-by-category').toLowerCase();

            if( filter_value === 'all categories' ){
                $(this).show();
            }else{
                if (filter_value === '' || categories.includes(filter_value)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            }

        });
    });

    $('#mpwem_search_event_list').on('keyup', function() {
        let search = $(this).val().toLowerCase().trim();

        $('.mpwem_event_list_card').each(function() {
            var name = $(this).data('filter-by-event-name').toLowerCase();
            var category = $(this).data('filter-by-category').toLowerCase();
            var organiser = $(this).data('filter-by-event-organiser').toLowerCase();

            // Check if search term is in any of the fields
            if (name.includes(search) || category.includes(search) || organiser.includes(search)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });



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
                nonce: mep_ajax.nonce
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



}(jQuery));