(function ($) {
//added by sumon
    $(document).on('click', '.mp_event_visible_event_time', function (e) {
        e.preventDefault();
        let target=$(this);
        $('.mp_event_more_date_list:visible').each(function (index){
            $(this).slideUp('fast').siblings('.mp_event_visible_event_time').slideDown('slow').siblings('.mp_event_hide_event_time').slideUp('slow');
        }).promise().done(function (){
            target.slideUp('fast').siblings('.mp_event_more_date_list , .mp_event_hide_event_time').slideDown('slow');
        });
    });
    $(document).on('click', '.mp_event_hide_event_time', function (e) {
        e.preventDefault();
        $('.mp_event_more_date_list:visible').each(function (index){
            $(this).slideUp('fast').siblings('.mp_event_visible_event_time').slideDown('slow').siblings('.mp_event_hide_event_time').slideUp('slow');
        });
    });
}(jQuery));