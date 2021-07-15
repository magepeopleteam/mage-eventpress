function mp_event_wo_commerce_price_format(price) {
    let currency_position = jQuery('input[name="currency_position"]').val();
    let currency_symbol = jQuery('input[name="currency_symbol"]').val();
    let currency_decimal = jQuery('input[name="currency_decimal"]').val();
    let currency_thousands_separator = jQuery('input[name="currency_thousands_separator"]').val();
    let currency_number_of_decimal = jQuery('input[name="currency_number_of_decimal"]').val();
    let price_text = '';

    price = price.toFixed(currency_number_of_decimal);
console.log('price= '+ price);
    let total_part = price.toString().split(".");
    total_part[0] = total_part[0].replace(/\B(?=(\d{3})+(?!\d))/g, currency_thousands_separator);
    price = total_part.join(currency_decimal);

    if (currency_position === 'right') {
        price_text = price + currency_symbol;
    } else if (currency_position === 'right_space') {
        price_text = price + '&nbsp;' + currency_symbol;
    } else if (currency_position === 'left') {
        price_text = currency_symbol + price;
    } else {
        price_text = currency_symbol + '&nbsp;' + price;
    }
	console.log('price= '+ price_text);
    return price_text;
}
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