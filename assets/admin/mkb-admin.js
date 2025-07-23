/**
 * @author Shahadat Hossain <raselsha@gmail.com>
 */
(function ($) {
// ================ Icon Select Settings ===============
    $(document).on('click', '.fa-icon-lists [data-icon]', function (e) {
        e.preventDefault();
        var icon = $(this).data('icon');
        $('.fa-icon-lists [data-icon]').removeClass('active');
        $(this).addClass('active');
        $("input[name='mep_event_speaker_icon']").val(icon);
        $('.mep-icon-wrapper i').removeClass();
        $('.mep-icon-wrapper i').addClass(icon);
        $('.mep-icon-preview i').removeClass();
        $('.mep-icon-preview i').addClass(icon);
    });
    $(document).on('input', "input[name='mep_icon_search_box']", function () {
        var searchQuery = $(this).val();
        $.ajax({
            url: ajaxurl, // WordPress's AJAX URL (for admin-ajax.php)
            method: 'POST',
            data: {
                action: 'mep_pick_icon', // The action name to hook into
                query: searchQuery, // The search query
            },
            success: function (response) {
                // Clear the icon list container
                $('.fa-icon-lists').html('');
                console.log(response);
                $.each(response, function (className, title) {
                    $('.fa-icon-lists').append(`
					<div class="icon" title="${title}" data-icon="${className}">
						<i class="${className}"></i>
					</div>
				`);
                });
            },
        });
    });
})(jQuery);



