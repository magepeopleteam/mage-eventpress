(function ($) {

    $(document).on('click', '.mpwem_filter_by_status', function() {

        $('.mpwem_filter_by_status').removeClass('mpwem_filter_btn_active_bg_color').addClass('mpwem_filter_btn_bg_color');
        $(this).removeClass('mpwem_filter_btn_bg_color').addClass('mpwem_filter_btn_active_bg_color');
        let searchText = $(this).attr('data-by-filter');
        $('.mpwem_event_list_card').each(function() {
            let by_filter = $(this).data('event-status').toLowerCase();
            if( searchText === 'all' ){
                $(this).fadeIn();
            }else{
                if ( by_filter.includes( searchText ) ) {
                    $(this).fadeIn();
                } else {
                    $(this).fadeOut();
                }
            }

        });
    });



}(jQuery));