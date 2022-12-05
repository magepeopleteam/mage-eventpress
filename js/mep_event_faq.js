jQuery(document).ready(function() {
    jQuery(".mep-event-faq-set > a").on("click", function() {
        if (jQuery(this).hasClass("active")) {
            jQuery(this).removeClass("active");
            jQuery(this)
                .siblings(".mep-event-faq-content")
                .slideUp(200);
            jQuery(".mep-event-faq-set > a i")
                .removeClass("fa-minus")
                .addClass("fa-plus");
        } else {
            jQuery(".mep-event-faq-set > a i")
                .removeClass("fa-minus")
                .addClass("fa-plus");
            jQuery(this)
                .find("i")
                .removeClass("fa-plus")
                .addClass("fa-minus");
            jQuery(".mep-event-faq-set > a").removeClass("active");
            jQuery(this).addClass("active");
            jQuery(".mep-event-faq-content").slideUp(200);
            jQuery(this)
                .siblings(".mep-event-faq-content")
                .slideDown(200);
        }
    });
});