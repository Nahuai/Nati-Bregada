var asitheme_repeater_item = {};

jQuery(window).on('load', function () {
    asitheme_link_widget();
    asitheme_field_chooser();
});
jQuery(document).on('widget-updated', function (e, obj) {
    asitheme_link_widget();
    asitheme_field_chooser();
    asitheme_widget_colorpicker();
});
jQuery(document).on('widget-added', function (e, obj) {
    obj = jQuery(obj);
    asitheme_widget_repeater_sortable(obj.find('.asitheme-widget-repeater-items'));
    asitheme_link_widget();
    asitheme_field_chooser();
    asitheme_widget_colorpicker();
});
jQuery(document).ready(function ($) {
    asitheme_widget_colorpicker();
});

function asitheme_widget_colorpicker() {
    jQuery('.widget').each(function () {
        var id = jQuery(this).attr('id');
        if (id.indexOf('__i__') === -1) {
            try {
                jQuery(this).find('.widget-color-picker').wpColorPicker({
                    change: function (e, ui) {
                        jQuery(e.target).val(ui.color.toString());
                        jQuery(e.target).trigger('change');
                    },
                    clear: function (e) {
                        jQuery(e.target).val('');
                        jQuery(e.target).trigger('change');
                    }
                });
            } catch (err) {
            }
        }
    });
}

function asitheme_widget_image_upload_add(btn) {
    btn = jQuery(btn);
    var mediaUploader;
    if (mediaUploader) {
        mediaUploader.open();
        return;
    }
    mediaUploader = wp.media.frames.file_frame = wp.media({
        title: _wpMediaViewsL10n['chooseImage'],
        button: {
            text: _wpMediaViewsL10n['chooseImage'],
        },
        multiple: false
    });

    mediaUploader.on('select', function () {
        var attachment = mediaUploader.state().get('selection').first().toJSON();
        if (attachment) {
            btn.parent().children('.asitheme-widget-image-upload-input').val(attachment.id);
            btn.parent().children('.asitheme-widget-image-upload-input').change();
            btn.parent().children('.asitheme-widget-image-upload-image').attr('src', attachment.url);
            btn.parent().children('.asitheme-widget-image-upload-image').css('display', 'block');
            btn.parent().children('.asitheme-widget-image-upload-remove').show();
        }
    });
    mediaUploader.open();
    return false;
}

function asitheme_widget_image_upload_remove(btn) {
    btn = jQuery(btn);
    btn.parent().children('.asitheme-widget-image-upload-input').val('');
    btn.parent().children('.asitheme-widget-image-upload-input').change();
    btn.parent().children('.asitheme-widget-image-upload-image').attr('src', '');
    btn.parent().children('.asitheme-widget-image-upload-image').hide();
    btn.hide();
    return false;
}

function asitheme_widget_repeater_add(btn, number, id) {
    btn = jQuery(btn);
    var widget = btn.parents('.widget');
    if (typeof asitheme_repeater_item[id] === 'undefined') {
        id = id.replace(number, '__i__');
    }
    try {
        btn.parent().parent().children('.asitheme-widget-repeater-items').append(asitheme_repeater_item[id]);
        var added = btn.parent().parent().children('.asitheme-widget-repeater-items').children(':last-child');
        added.children('.asitheme-widget-top').click();
        widget.find('input:eq(0)').change();
    } catch (err) {
    }
    return false;
}

function asitheme_widget_repeater_remove(btn) {
    btn = jQuery(btn);
    var widget = btn.parents('.widget');
    btn.closest('.asitheme-widget').remove();
    widget.find('input:eq(0)').change();
    return false;
}

function asitheme_widget_repeater_toggle(btn) {
    btn = jQuery(btn);
    var widget = btn.parents('.asitheme-widget');
    widget.toggleClass('open');
    widget.find('.asitheme-widget-inside').slideToggle();
}

function asitheme_widget_repeater_sortable(obj) {
    obj.sortable({
        placeholder: 'asitheme-widget-placeholder',
        items: '> .asitheme-widget',
        handle: '> .asitheme-widget-top > .asitheme-widget-title',
        cursor: 'move',
        distance: 2,
        containment: 'parent',
        tolerance: 'pointer',
        refreshPositions: true,
        change: function (event, ui) {
            ui.item.parents('.widget').find('input:eq(0)').change();
        }
    });
}

function asitheme_widget_checkbox(btn) {
    btn = jQuery(btn);
    var val = btn.is(':checked') ? 1 : 0;
    btn.next('.asi-hidden').val(val).change();
    return false;
}

function asitheme_widget_gallery(btn) {
    btn = jQuery(btn);
    var mediaUploader;
    if (mediaUploader) {
        mediaUploader.open();
        return;
    }
    mediaUploader = wp.media.frames.file_frame = wp.media({
        id: 'gallery',
        title: wp.media.view.l10n.createGalleryTitle,
        priority: 40,
        toolbar: 'main-gallery',
        filterable: 'uploaded',
        multiple: 'add',
        editable: true,
        library: wp.media.query({
            type: 'image'
        })
    });

    mediaUploader.on('select', function () {
        var attachment = mediaUploader.state().get('selection').toJSON();
        if (attachment) {
            btn.parent().children('.asitheme-widget-image-upload-input').val(attachment.id);
            btn.parent().children('.asitheme-widget-image-upload-input').change();
            btn.parent().children('.asitheme-widget-image-upload-image').attr('src', attachment.url);
            btn.parent().children('.asitheme-widget-image-upload-image').css('display', 'block');
            btn.parent().children('.asitheme-widget-image-upload-remove').show();
        }
    });
    mediaUploader.open();
    return false;
}

function asitheme_link_widget() {
    var widgets = jQuery('#asi-front-page').children('.widget');
    if (widgets.length === 0) {
        widgets = jQuery('#sub-accordion-section-sidebar-widgets-asi-front-page').children('.customize-control-widget_form');
    }
    if (widgets.length === 0) {
        return;
    }
    jQuery('.asi-link-widget').each(function () {
        var select = jQuery(this);
        var current_val = select.val();
        jQuery(this).html('');
        var option = jQuery('<option/>');
        option.val('').text('—');
        select.append(option);
        widgets.each(function () {
            var widget = jQuery(this);
            var id = widget.find('input[name="widget-id"]').val();
            if (id) {
                var title = widget.find('.widget-title>h3').text();
                var option = jQuery('<option/>');
                option.val(id).text(title);
                select.append(option);
            }
        });
        select.val(current_val);
        if (!select.val()) {

        }
    });
}

function asitheme_field_chooser() {

    jQuery('.asi-field-chooser-select').each(function () {
        var select = jQuery(this);
        var content = select.parents('.asitheme-widget-content');
        if (content.length === 0) {
            content = select.parents('.widget-content');
        }
        var current_val = select.val();

        select.children('option').each(function () {
            content.find('[data-field_id="' + this.value + '"]').hide();
        });
        content.find('[data-field_id="' + current_val + '"]').show();

        select.change(function () {
            var current_val = jQuery(this).val();
            jQuery(this).children('option').each(function () {
                content.find('[data-field_id="' + this.value + '"]').hide();
            });
            content.find('[data-field_id="' + current_val + '"]').show();
        });
    });
}