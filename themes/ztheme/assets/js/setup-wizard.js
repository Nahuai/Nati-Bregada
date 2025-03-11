jQuery(function ($) {
    function blockWizardUI() {
        $('.asi-setup-content').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    }

    $('.button-next').on('click', function () {
        var form = $(this).parents('form').get(0);
        if (('function' !== typeof form.checkValidity) || form.checkValidity()) {
            blockWizardUI();
        }
        return true;
    });

    $('.asi-wizard-enable input[type="checkbox"]').each(function () {
        if ($(this).is(':checked')) {
            $(this).closest('.asi-wizard-toggle').removeClass('disabled');
        } else {
            $(this).closest('.asi-wizard-toggle').addClass('disabled');
        }
    });

    $('.asi-wizard-enable input[type="checkbox"]').change(function () {
        if ($(this).is(':checked')) {
            $(this).closest('.asi-wizard-toggle').removeClass('disabled');
        } else {
            $(this).closest('.asi-wizard-toggle').addClass('disabled');
        }
    });
});
