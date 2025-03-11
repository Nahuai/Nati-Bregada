/* eslint consistent-this: [ "error", "control" ] */
(function (component) {
    'use strict';

    var GalleryWidgetModel, GalleryWidgetControl, GalleryDetailsMediaFrame;

    /**
     * Custom gallery details frame.
     *
     * @since 4.9.0
     * @class GalleryDetailsMediaFrame
     * @constructor
     */
    GalleryDetailsMediaFrame = wp.media.view.MediaFrame.Post.extend({

        /**
         * Create the default states.
         *
         * @since 4.9.0
         * @returns {void}
         */
        createStates: function createStates() {
            this.states.add([
                new wp.media.controller.Library({
                    id: 'gallery',
                    title: wp.media.view.l10n.createGalleryTitle,
                    priority: 40,
                    toolbar: 'main-gallery',
                    filterable: 'uploaded',
                    multiple: 'add',
                    editable: true,

                    library: wp.media.query(_.defaults({
                        type: 'image'
                    }, this.options.library))
                }),

                // Gallery states.
                new wp.media.controller.GalleryEdit({
                    library: this.options.selection,
                    editing: this.options.editing,
                    menu: 'gallery'
                }),

                new wp.media.controller.GalleryAdd()
            ]);
        }
    });

    /**
     * Gallery widget model.
     *
     * See WP_Widget_Gallery::enqueue_admin_scripts() for amending prototype from PHP exports.
     *
     * @since 4.9.0
     * @class GalleryWidgetModel
     * @constructor
     */
    GalleryWidgetModel = component.MediaWidgetModel.extend({});

    /**
     * Gallery widget control.
     *
     * See WP_Widget_Gallery::enqueue_admin_scripts() for amending prototype from PHP exports.
     *
     * @since 4.9.0
     * @class GalleryWidgetControl
     * @constructor
     */
    GalleryWidgetControl = component.MediaWidgetControl.extend({

        /**
         * View events.
         *
         * @since 4.9.0
         * @type {object}
         */
        events: _.extend({}, component.MediaWidgetControl.prototype.events, {
            'click .media-widget-gallery-preview': 'editMedia'
        }),

        /**
         * Initialize.
         *
         * @since 4.9.0
         * @param {Object}         options - Options.
         * @param {Backbone.Model} options.model - Model.
         * @param {jQuery}         options.el - Control field container element.
         * @param {jQuery}         options.syncContainer - Container element where fields are synced for the server.
         * @returns {void}
         */
        initialize: function initialize(options) {
            var control = this;

            component.MediaWidgetControl.prototype.initialize.call(control, options);

            _.bindAll(control, 'updateSelectedAttachments', 'handleAttachmentDestroy');
            control.selectedAttachments = new wp.media.model.Attachments();
            control.model.on('change:ids', control.updateSelectedAttachments);
            control.selectedAttachments.on('change', control.renderPreview);
            control.selectedAttachments.on('reset', control.renderPreview);
            control.updateSelectedAttachments();

            // Update the title.
            control.$el.on('input change', '.title', function updateTitle() {
                control.model.set({
                    title: jQuery.trim(jQuery(this).val())
                });
            });

            // Update the subtitle.
            control.$el.on('input change', '.asi-subtitle', function updateTitle() {
                control.model.set({
                    subtitle: jQuery.trim(jQuery(this).val())
                });
            });

            // Update the menu.
            control.$el.on('input change', '.asi-menu', function updateTitle() {
                control.model.set({
                    menu: jQuery.trim(jQuery(this).val())
                });
            });

            // Update the button_text.
            control.$el.on('input change', '.asi-button_text', function updateTitle() {
                control.model.set({
                    button_text: jQuery.trim(jQuery(this).val())
                });
            });

            // Update the button_link.
            control.$el.on('input change', '.asi-button_link', function updateTitle() {
                control.model.set({
                    button_link: jQuery.trim(jQuery(this).val())
                });
            });

            /*
             * Refresh a Gallery widget partial when the user modifies one of the selected attachments.
             * This ensures that when an attachment's caption is updated in the media modal the Gallery
             * widget in the preview will then be refreshed to show the change. Normally doing this
             * would not be necessary because all of the state should be contained inside the changeset,
             * as everything done in the Customizer should not make a change to the site unless the
             * changeset itself is published. Attachments are a current exception to this rule.
             * For a proposal to include attachments in the customized state, see #37887.
             */
            if (wp.customize && wp.customize.previewer) {
                control.selectedAttachments.on('change', function () {
                    wp.customize.previewer.send('refresh-widget-partial', control.model.get('widget_id'));
                });
            }
        },

        /**
         * Update the selected attachments if necessary.
         *
         * @since 4.9.0
         * @returns {void}
         */
        updateSelectedAttachments: function updateSelectedAttachments() {
            var control = this, newIds, oldIds, removedIds, addedIds, addedQuery;

            newIds = control.model.get('ids');
            oldIds = _.pluck(control.selectedAttachments.models, 'id');

            removedIds = _.difference(oldIds, newIds);
            _.each(removedIds, function (removedId) {
                control.selectedAttachments.remove(control.selectedAttachments.get(removedId));
            });

            addedIds = _.difference(newIds, oldIds);
            if (addedIds.length) {
                addedQuery = wp.media.query({
                    order: 'ASC',
                    orderby: 'post__in',
                    perPage: -1,
                    post__in: newIds,
                    query: true,
                    type: 'image'
                });
                addedQuery.more().done(function () {
                    control.selectedAttachments.reset(addedQuery.models);
                });
            }
        },

        /**
         * Render template.
         *
         * @returns {void}
         */
        render: function render() {
            var control = this, titleInput;

            if (!control.templateRendered) {
                control.$el.html(control.template()(control.model.toJSON()));
                control.renderPreview(); // Hereafter it will re-render when control.selectedAttachment changes.
                control.templateRendered = true;
            }

            titleInput = control.$el.find('.title');
            if (!titleInput.is(document.activeElement)) {
                titleInput.val(control.model.get('title'));
            }

            titleInput = control.$el.find('.asi-subtitle');
            if (!titleInput.is(document.activeElement)) {
                titleInput.val(control.model.get('subtitle'));
            }

            titleInput = control.$el.find('.asi-menu');
            if (!titleInput.is(document.activeElement)) {
                titleInput.val(control.model.get('menu'));
            }

            titleInput = control.$el.find('.asi-button_text');
            if (!titleInput.is(document.activeElement)) {
                titleInput.val(control.model.get('button_text'));
            }

            titleInput = control.$el.find('.asi-button_link');
            if (!titleInput.is(document.activeElement)) {
                titleInput.val(control.model.get('button_link'));
            }

            control.$el.toggleClass('selected', control.isSelected());
        },

        /**
         * Render preview.
         *
         * @since 4.9.0
         * @returns {void}
         */
        renderPreview: function renderPreview() {
            var control = this, previewContainer, previewTemplate, data;

            previewContainer = control.$el.find('.media-widget-preview');
            previewTemplate = wp.template('wp-media-widget-gallery-preview');

            data = control.previewTemplateProps.toJSON();
            data.attachments = {};
            control.selectedAttachments.each(function (attachment) {
                data.attachments[attachment.id] = attachment.toJSON();
            });

            previewContainer.html(previewTemplate(data));
        },

        /**
         * Determine whether there are selected attachments.
         *
         * @since 4.9.0
         * @returns {boolean} Selected.
         */
        isSelected: function isSelected() {
            var control = this;

            if (control.model.get('error')) {
                return false;
            }

            return control.model.get('ids').length > 0;
        },

        /**
         * Open the media select frame to edit images.
         *
         * @since 4.9.0
         * @returns {void}
         */
        editMedia: function editMedia() {
            var control = this, selection, mediaFrame, mediaFrameProps;

            selection = new wp.media.model.Selection(control.selectedAttachments.models, {
                multiple: true
            });

            mediaFrameProps = control.mapModelToMediaFrameProps(control.model.toJSON());
            selection.gallery = new Backbone.Model(mediaFrameProps);
            if (mediaFrameProps.size) {
                control.displaySettings.set('size', mediaFrameProps.size);
            }
            mediaFrame = new GalleryDetailsMediaFrame({
                frame: 'manage',
                text: control.l10n.add_to_widget,
                selection: selection,
                mimeType: control.mime_type,
                selectedDisplaySettings: control.displaySettings,
                showDisplaySettings: control.showDisplaySettings,
                metadata: mediaFrameProps,
                editing: true,
                multiple: true,
                state: 'gallery-edit'
            });
            wp.media.frame = mediaFrame; // See wp.media().

            // Handle selection of a media item.
            mediaFrame.on('update', function onUpdate(newSelection) {
                var state = mediaFrame.state(), resultSelection;

                resultSelection = newSelection || state.get('selection');
                if (!resultSelection) {
                    return;
                }

                // Copy orderby_random from gallery state.
                if (resultSelection.gallery) {
                    control.model.set(control.mapMediaToModelProps(resultSelection.gallery.toJSON()));
                }

                // Directly update selectedAttachments to prevent needing to do additional request.
                control.selectedAttachments.reset(resultSelection.models);

                // Update models in the widget instance.
                control.model.set({
                    ids: _.pluck(resultSelection.models, 'id')
                });
            });

            mediaFrame.$el.addClass('media-widget');
            mediaFrame.open();

            if (selection) {
                selection.on('destroy', control.handleAttachmentDestroy);
            }
        },

        /**
         * Open the media select frame to chose an item.
         *
         * @since 4.9.0
         * @returns {void}
         */
        selectMedia: function selectMedia() {
            var control = this, selection, mediaFrame, mediaFrameProps;
            selection = new wp.media.model.Selection(control.selectedAttachments.models, {
                multiple: true
            });

            mediaFrameProps = control.mapModelToMediaFrameProps(control.model.toJSON());
            if (mediaFrameProps.size) {
                control.displaySettings.set('size', mediaFrameProps.size);
            }
            mediaFrame = new GalleryDetailsMediaFrame({
                frame: 'select',
                text: control.l10n.add_to_widget,
                selection: selection,
                mimeType: control.mime_type,
                selectedDisplaySettings: control.displaySettings,
                showDisplaySettings: control.showDisplaySettings,
                metadata: mediaFrameProps,
                state: 'gallery'
            });
            wp.media.frame = mediaFrame; // See wp.media().

            // Handle selection of a media item.
            mediaFrame.on('update', function onUpdate(newSelection) {
                var state = mediaFrame.state(), resultSelection;

                resultSelection = newSelection || state.get('selection');
                if (!resultSelection) {
                    return;
                }

                // Copy orderby_random from gallery state.
                if (resultSelection.gallery) {
                    control.model.set(control.mapMediaToModelProps(resultSelection.gallery.toJSON()));
                }

                // Directly update selectedAttachments to prevent needing to do additional request.
                control.selectedAttachments.reset(resultSelection.models);

                // Update widget instance.
                control.model.set({
                    ids: _.pluck(resultSelection.models, 'id')
                });
            });

            mediaFrame.$el.addClass('media-widget');
            mediaFrame.open();

            if (selection) {
                selection.on('destroy', control.handleAttachmentDestroy);
            }

            /*
             * Make sure focus is set inside of modal so that hitting Esc will close
             * the modal and not inadvertently cause the widget to collapse in the customizer.
             */
            mediaFrame.$el.find(':focusable:first').focus();
        },

        /**
         * Clear the selected attachment when it is deleted in the media select frame.
         *
         * @since 4.9.0
         * @param {wp.media.models.Attachment} attachment - Attachment.
         * @returns {void}
         */
        handleAttachmentDestroy: function handleAttachmentDestroy(attachment) {
            var control = this;
            control.model.set({
                ids: _.difference(
                    control.model.get('ids'),
                    [attachment.id]
                )
            });
        }
    });

    // Exports.
    component.controlConstructors.asitheme_widget_gallery = GalleryWidgetControl;
    component.modelConstructors.asitheme_widget_gallery = GalleryWidgetModel;

})(wp.mediaWidgets);
