(function ($) {
    "use strict";
    $(document).on('click', '#publish,#save-post', function (e) {
		e.preventDefault();
        let parent = $('#mp_event_all_info_in_tab');
		//alert(parent.length);
        if (parent.length > 0 && parent.find('.data_required').length > 0) {
            parent.find('.data_required').each(function (){
                //alert(parent.find('.data_required').length);
                if(!$(this).hasClass('screen-reader-text')){
                    $(this).find('[data-required]').each(function (){
                        if(!$(this).val()){

                            let target_id=$(this).closest('.mp_tab_item').attr('data-tab-item');
                            parent.find('.mp_tab_menu').find('[data-target-tabs="'+target_id+'"]').trigger('click');
                            $(this).focus();
                            return false;
                            //alert(target_id);
                        }
                    });
                }
            }).promise().done(function () {
                $(this).closest('form').submit();
            });
            //alert(parent.find('.data_required').length);
            //return false;
			//$(this).closest('form').submit();
        }
    });
}(jQuery));