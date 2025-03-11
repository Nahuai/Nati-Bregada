jQuery(document).ready(function () {

    jQuery('#menu-btn').click(function (e) {
        e.preventDefault();
        jQuery('.site-header').toggleClass('open');
        jQuery('body').toggleClass('noscroll');
    });

    var anchors = jQuery('.nav-primary').find('.genesis-nav-menu').find('a[href^="#"]');
    if (anchors.length) {
        anchors.click(function (e) {
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

    jQuery('body:not(.woocommerce-page) table').each(function (i, e) {
        var headers = [];

        if (jQuery(this).find('tr:nth-child(1) th').length > 0) {
            jQuery(this).find('tr:nth-child(1) th').each(function () {
                var text = jQuery(this).text();
                if (jQuery(this).attr('colspan')) {
                    for (var j = 0; j < jQuery(this).attr('colspan'); j++) {
                        headers.push(text + ':');
                    }
                } else {
                    headers.push(text + ':');
                }
            });
        }
        if (headers.length > 0) {
            jQuery(this).find('tr').each(function (i, e) {
                var colCount = 0;
                jQuery(this).find('td').each(function (i) {
                    jQuery(this).attr('data-title', headers[colCount]);
                    if (jQuery(this).attr('colspan')) {
                        for (var j = 0; j < jQuery(this).attr('colspan'); j++) {
                            colCount++;
                        }
                    } else {
                        colCount++;
                    }
                });
            });
        }
    });

    jQuery(document).find('.woocommerce .quantity').each(function () {
        var qty_div = jQuery(this);
        var qty = qty_div.find('.input-text.qty');
        qty.before("<input type='button' value='-' class='qtyminus qtychange'>");
        qty.after("<input type='button' value='+' class='qtyplus qtychange'>");

        qty_div.find('.qtyplus').click(function (e) {
            e.preventDefault();
            var currentVal = parseInt(qty.val());
            if (!isNaN(currentVal)) {
                qty.val(currentVal + 1);
            } else {
                qty.val(1);
            }
            qty.change();
        });
        qty_div.find('.qtyminus').click(function (e) {
            e.preventDefault();
            var currentVal = parseInt(qty.val());
            if (!isNaN(currentVal) && currentVal > 1) {
                qty.val(currentVal - 1);
            } else {
                qty.val(1);
            }
            qty.change();
        });
    });

    var current_url = window.location.href;
    jQuery('article.front-page .banner-inner .button').click(function (e) {
        if (jQuery(this).attr('href') === current_url) {
            jQuery('html, body').animate({
                scrollTop: jQuery('article.front-page .banner-wrapper').next().offset().top
            }, 500);
            e.preventDefault();
        }
    });

    jQuery(window).resize(function () {
        var h = jQuery('.site-header.fixed').outerHeight();
        if (h > 0) {
            jQuery('.site-inner.fixed').css('padding-top', h + 'px');
        }
    });

    jQuery(window).resize();
});

jQuery(window).scroll(function () {

    var scroll = jQuery(window).scrollTop();
    if (scroll > 0) {
        jQuery('.site-header').addClass('scroll');
    } else {
        jQuery('.site-header').removeClass('scroll');
    }

});

jQuery(window).on('load', function () {
    jQuery(window).resize();
});