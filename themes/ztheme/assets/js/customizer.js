jQuery(window).on('load', function () {

    jQuery('textarea.wp-editor-area').each(function () {

        var tArea = jQuery(this);
        var id = tArea.attr('id');
        var input = jQuery('input[data-customize-setting-link="' + id + '"]');
        var editor = tinyMCE.get(id);
        var setChange;
        var content;

        if (editor) {
            editor.onChange.add(function (ed, e) {
                ed.save();
                content = editor.getContent();
                clearTimeout(setChange);
                setChange = setTimeout(function () {
                    input.val(content).trigger('change');
                }, 500);
            });
        }

        tArea.css({
            visibility: 'visible'
        }).on('keyup', function () {
            content = tArea.val();
            clearTimeout(setChange);
            setChange = setTimeout(function () {
                input.val(content).trigger('change');
            }, 500);
        });
    });

    //* Display fields by header position
    jQuery('select[data-customize-setting-link="' + asitheme_customizer_slug + '_header_position"]').change(function () {
        var id = jQuery(this).val();
        jQuery('#customize-control-' + asitheme_customizer_slug + '_header_transparency').hide();
        if (id === 'fixed') {
            jQuery('#customize-control-' + asitheme_customizer_slug + '_header_transparency').show();
        }
    });
    jQuery('select[data-customize-setting-link="' + asitheme_customizer_slug + '_header_position"]').change();

});