jQuery(document).ready(function () {

    /* Widget links animation */
    if (jQuery('.widget-link').length) {
        jQuery('.widget-link a').click(function (e) {
            jQuery('.site-header.open').find('#menu-btn').click();
            e.preventDefault();
            var id = jQuery(this).attr('href');
            if (jQuery(id).length) {
                jQuery('html, body').animate({
                    scrollTop: jQuery(id).offset().top - 50
                }, 500);
            }
        });
    }

    try {
        if (jQuery('.asitheme-modal-gallery').length) {
            jQuery('.asitheme-modal-gallery').magnificPopup({
                delegate: 'a',
                type: 'image',
                tCounter: '<span class="mfp-counter">' + asitheme_magnific_tCounter + '</span>',
                gallery: {
                    enabled: true,
                    navigateByImgClick: true,
                    preload: [0, 1],
                    tCounter: '<span class="mfp-counter">' + asitheme_magnific_tCounter + '</span>',
                }
            });
        }
    } catch (err) {
    }

    var widgets_main_background = jQuery('.widget.asitheme_widget.asitheme_widget_main_box');
    widgets_main_background.each(function () {
        var buttons = jQuery(this).find('.wbutton');
        buttons.css('min-width', '0');
        var wbutton_max_w = [];
        buttons.each(function () {
            wbutton_max_w.push(jQuery(this).outerWidth());
        });
        var max = Math.max.apply(null, wbutton_max_w);
        if (max) {
            buttons.each(function () {
                jQuery(this).css('min-width', max);
            });
        }
    });
});