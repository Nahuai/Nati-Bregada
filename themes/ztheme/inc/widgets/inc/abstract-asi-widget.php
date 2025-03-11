<?php
if (!defined('ABSPATH')) {
    exit;
}

abstract class ASI_Widget extends WP_Widget {

    public $widget_cssclass;
    public $widget_description;
    public $widget_id;
    public $widget_name;
    public $settings;
    public $l10n;
    protected $registered = false;

    public function __construct() {
        $widget_ops = array(
            'classname' => $this->widget_cssclass,
            'description' => $this->widget_description,
            'customize_selective_refresh' => true,
        );

        $this->l10n = array(
            'no_media_selected' => __('No media selected'),
            'add_media' => _x('Add Media', 'label for button in the media widget'),
            'replace_media' => _x('Replace Media', 'label for button in the media widget; should preferably not be longer than ~13 characters long'),
            'edit_media' => _x('Edit Media', 'label for button in the media widget; should preferably not be longer than ~13 characters long'),
            'add_to_widget' => __('Add to Widget'),
            'missing_attachment' => sprintf(
                __('We can&#8217;t find that file. Check your <a href="%s">media library</a> and make sure it wasn&#8217;t deleted.'), esc_url(admin_url('upload.php'))
            ),
            'media_library_state_multi' => _n_noop('Media Widget (%d)', 'Media Widget (%d)'),
            'media_library_state_single' => __('Media Widget'),
            'unsupported_file_type' => __('Looks like this isn&#8217;t the correct kind of file. Please link to an appropriate file instead.'),
        );
        $this->l10n = array_merge($this->l10n, array(
            'no_media_selected' => __('No images selected'),
            'add_media' => _x('Add Images', 'label for button in the gallery widget; should not be longer than ~13 characters long'),
            'replace_media' => '',
            'edit_media' => _x('Edit Gallery', 'label for button in the gallery widget; should not be longer than ~13 characters long'),
        ));

        parent::__construct($this->widget_id, $this->widget_name, $widget_ops);
    }

    public function widget_start($args, $instance) {
        $class = '';
        if (isset($instance['background_image']) && trim($instance['background_image'])) {
            $class = 'img-background';
        }
        if (isset($instance['background_color']) && trim($instance['background_color'])) {
            $class = 'color-background';
        }
        if (!$class) {
            $class = 'no-background';
        }
        $args['before_widget'] = str_replace('"><div class="widget-wrap">', ' ' . $class . '"><div class="widget-wrap">', $args['before_widget']);
        echo $args['before_widget'];
    }

    public function widget_end($args) {
        echo $args['after_widget'];
    }

    public function _register_one($number = -1) {
        parent::_register_one($number);
        if ($this->registered) {
            return;
        }
        $this->registered = true;

        add_action('customize_controls_print_scripts', array($this, 'admin_head_widgets'));
        add_action('admin_head-widgets.php', array($this, 'admin_head_widgets'));
    }

    public function admin_head_widgets() {
        wp_enqueue_editor();
        wp_enqueue_media();
        wp_enqueue_script('asitheme-widgets-admin', CHILD_URL . '/assets/js/widgets-admin.js', array('jquery'), CHILD_THEME_VERSION);
        wp_enqueue_style('asitheme-widgets-admin', CHILD_URL . '/assets/css/widgets-admin.css', array(), CHILD_THEME_VERSION);
    }

    public function wpkses_post_tags($tags, $context) {
        if ('post' === $context) {
            $tags['iframe'] = array(
                'src' => true,
                'height' => true,
                'width' => true,
                'frameborder' => true,
                'allowfullscreen' => true,
                'allow' => true,
                'style' => true,
            );
        }
        return $tags;
    }

    public function update($new_instance, $old_instance) {

        $instance = $old_instance;

        if (empty($this->settings)) {
            return $instance;
        }

        // Loop settings and get values to save.
        foreach ($this->settings as $key => $setting) {
            if (!isset($setting['type'])) {
                continue;
            }
            if ($setting['type'] == 'group') {
                $items = $setting['items'];
                if ($items) {
                    foreach ($items as $ikey => $isetting) {
                        $function = $this->asi_field_function('update', $isetting['type']);
                        $value = call_user_func(array($this, $function), $new_instance[$ikey], $isetting);
                        $instance[$ikey] = $value;
                    }
                }
                continue;
            }

            $function = $this->asi_field_function('update', $setting['type']);

            // Format the value based on settings type.
            switch ($setting['type']) {

                case 'repeater' :
                    if (isset($setting['items']) && isset($new_instance[$key])) {
                        $items = $setting['items'];
                        if ($items) {
                            $arr = (array)$new_instance[$key];
                            $result = [];
                            $ks = array_keys($arr);
                            for ($row = 0, $rows = count(reset($arr)); $row < $rows; $row++) {
                                foreach ($ks as $k) {
                                    $result[$row][$k] = $arr[$k][$row];
                                }
                            }
                            $value = array();
                            if ($result) {
                                foreach ($result as $i => $item) {
                                    if ($item) {
                                        foreach ($item as $k => $v) {
                                            $function = $this->asi_field_function('update', $items[$k]['type']);
                                            //$value[$i][$k] = $this->$function($v, $items[$k]);
                                            $value[$i][$k] = call_user_func(array($this, $function), $v, $items[$k]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;

                // Default: text
                default :
                    //$value = $this->$function($new_instance[$key], $setting);
                    $value = call_user_func(array($this, $function), $new_instance[$key], $setting);
                    break;
            }
            $instance[$key] = $value;
        }

        return $instance;
    }

    public function asi_field_function($action, $type) {
        $function = 'asi_' . trim($action) . '_field_' . trim($type);
        if (method_exists($this, $function)) {
            return $function;
        } else {
            return 'asi_' . trim($action) . '_field_default';
        }
    }

    public function asi_update_field_default($value, $setting) {
        $value = sanitize_text_field($value);
        return $value;
    }

    public function asi_update_field_image($value, $setting) {
        $value = absint($value);
        return $value;
    }

    public function asi_update_field_number($value, $setting) {
        $value = (float)$value;
        if (isset($setting['min']) && '' !== $setting['min']) {
            $value = max($value, $setting['min']);
        }
        if (isset($setting['max']) && '' !== $setting['max']) {
            $value = min($value, $setting['max']);
        }
        return $value;
    }

    public function asi_update_field_integer($value, $setting) {
        $value = (int)$value;
        if (isset($setting['min']) && '' !== $setting['min']) {
            $value = max($value, $setting['min']);
        }
        if (isset($setting['max']) && '' !== $setting['max']) {
            $value = min($value, $setting['max']);
        }
        return $value;
    }

    public function asi_update_field_range($value, $setting) {
        $value = (float)$value;
        if (isset($setting['min']) && '' !== $setting['min']) {
            $value = max($value, $setting['min']);
        }
        if (isset($setting['max']) && '' !== $setting['max']) {
            $value = min($value, $setting['max']);
        }
        return $value;
    }

    public function asi_update_field_wysiwyg($value, $setting) {
        add_filter('wp_kses_allowed_html', array($this, 'wpkses_post_tags'), 10, 2);
        $value = wp_kses(trim(wp_unslash($value)), wp_kses_allowed_html('post'));
        remove_filter('wp_kses_allowed_html', array($this, 'wpkses_post_tags'), 10, 2);
        return $value;
    }

    public function asi_update_field_textarea($value, $setting) {
        add_filter('wp_kses_allowed_html', array($this, 'wpkses_post_tags'), 10, 2);
        $value = wp_kses(trim(wp_unslash($value)), wp_kses_allowed_html('post'));
        remove_filter('wp_kses_allowed_html', array($this, 'wpkses_post_tags'), 10, 2);
        return $value;
    }

    public function asi_update_field_checkbox($value, $setting) {
        $value = empty($value) ? 0 : 1;
        return $value;
    }

    public function form($instance) {

        if (empty($this->settings)) {
            return;
        }

        foreach ($this->settings as $key => $setting) {

            $setting['std'] = isset($setting['std']) ? $setting['std'] : '';
            $value = isset($instance[$key]) ? $instance[$key] : $setting['std'];
            $function = $this->asi_field_function('form', $setting['type']);

            switch ($setting['type']) {

                case 'group' :
                    call_user_func(array($this, $function), $instance, $setting);
                    break;

                case 'repeater' :
                case 'gallery' :
                    $items = $setting['items'];
                    if ($items) {
                        //$this->$function($value, $key, $setting, $items);
                        call_user_func(array($this, $function), $value, $key, $setting, $items);
                    }
                    break;

                // Default: text
                default :
                    //$this->$function($value, $key, $setting);
                    call_user_func(array($this, $function), $value, $key, $setting);
                    break;
            }
        }
    }

    public function asi_form_field_default($value, $key, $setting, $k = '') {
        $setting['class_input'] = isset($setting['class_input']) ? $setting['class_input'] : '';
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <input class="widefat <?php echo esc_html($setting['class_input']) ?>" id="<?php echo $this->get_field_id($key); ?>" name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>" type="text" value="<?php echo esc_attr($value); ?>"/>
        </div>
        <?php
    }

    public function asi_form_field_number($value, $key, $setting, $k = '') {
        $setting['step'] = isset($setting['step']) ? $setting['step'] : 1;
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <input class="widefat" id="<?php echo $this->get_field_id($key); ?>" name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>" type="number" step="<?php echo esc_attr($setting['step']); ?>" min="<?php echo esc_attr($setting['min']); ?>" max="<?php echo esc_attr($setting['max']); ?>" value="<?php echo esc_attr($value); ?>"/>
        </div>
        <?php
    }

    public function asi_form_field_integer($value, $key, $setting, $k = '') {
        $setting['step'] = isset($setting['step']) ? $setting['step'] : 1;
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <input class="widefat" id="<?php echo $this->get_field_id($key); ?>" name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>" type="number" step="<?php echo esc_attr($setting['step']); ?>" min="<?php echo esc_attr($setting['min']); ?>" max="<?php echo esc_attr($setting['max']); ?>" value="<?php echo esc_attr($value); ?>"/>
        </div>
        <?php
    }

    public function asi_form_field_range($value, $key, $setting, $k = '') {
        $setting['step'] = isset($setting['step']) ? $setting['step'] : 1;
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <input class="widefat" id="<?php echo $this->get_field_id($key); ?>" name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>" type="range" step="<?php echo esc_attr($setting['step']); ?>" min="<?php echo esc_attr($setting['min']); ?>" max="<?php echo esc_attr($setting['max']); ?>" value="<?php echo esc_attr($value); ?>" oninput="jQuery(this).next('input').val(jQuery(this).val())">
            <input class="widefat" onKeyUp="jQuery(this).prev('input').val(jQuery(this).val())" type='text' value='<?php echo esc_attr($value); ?>'>
        </div>
        <?php
    }

    public function asi_form_field_select($value, $key, $setting, $k = '') {
        if (!array_key_exists($value, $setting['options'])) {
            $setting['options'][$value] = $value;
        }
        $setting['class'] = isset($setting['class']) ? $setting['class'] : '';
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <select class="widefat <?php echo esc_attr($setting['class']) ?>" id="<?php echo $this->get_field_id($key); ?>" name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>">
                <?php foreach ($setting['options'] as $option_key => $option_value) : ?>
                    <option value="<?php echo esc_attr($option_key); ?>" <?php selected($option_key, $value); ?>><?php echo esc_html($option_value); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    public function asi_form_field_chooser($value, $key, $setting, $k = '') {
        $setting['class'] = isset($setting['class']) ? $setting['class'] : '';
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <select class="widefat asi-field-<?php echo esc_attr($setting['type']) ?>-select <?php echo esc_attr($setting['class']) ?>" id="<?php echo $this->get_field_id($key); ?>" name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>">
                <?php foreach ($setting['options'] as $option_key => $option_value) : ?>
                    <option value="<?php echo esc_attr($option_key); ?>" <?php selected($option_key, $value); ?>><?php echo esc_html($option_value); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    public function asi_form_field_checkbox($value, $key, $setting, $k = '') {
        if ($value === '' && isset($setting['std'])) {
            $value = $setting['std'];
        }
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <label>
                <input class="checkbox asi-checkbox" type="checkbox" value="1" <?php checked($value, 1); ?> onchange="asitheme_widget_checkbox(this);"/>
                <input class="checkbox asi-hidden" id="<?php echo $this->get_field_id($key); ?>" name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>" type="hidden" value="<?php echo $value ?>"/>
                <?php echo $setting['label']; ?>
            </label>
        </div>
        <?php
    }

    public function asi_form_field_textarea($value, $key, $setting, $k = '') {
        $rows = isset($setting['rows']) ? (int)$setting['rows'] : 3;
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <textarea class="widefat" id="<?php echo $this->get_field_id($key); ?>" name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>" rows="<?php echo $rows; ?>"><?php echo esc_textarea($value); ?></textarea>
        </div>
        <?php
    }

    public function asi_form_field_wysiwyg($value, $key, $setting, $k = '') {
        //revisar funcionamiento
        return;
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>" style="margin-bottom:-20px;">
            <label><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
        </div>
        <?php
        wp_editor($value, $this->get_field_id($key) . $k, array(
            'textarea_name' => $this->get_field_name($key),
            'default_editor' => 'tmce',
            'media_buttons' => false,
            'drag_drop_upload' => false,
            'teeny' => true,
            'textarea_rows' => 5,
            'quicktags' => true,
            'tabindex' => 1000
        ));
    }

    public function asi_form_field_image($value, $key, $setting, $k = '') {
        $url = '';
        if ($value) {
            $image = asitheme_get_image_sizes($value);
            if ($image) {
                $url = $image['url'];
            }
        }
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
            <img
                    style="max-width:100%;margin:5px 0;display:<?php echo $url ? 'block' : 'none' ?>;"
                    class="asitheme-widget-image-upload-image"
                    src="<?php echo esc_url($url) ?>">
            <input
                    type="hidden"
                    name="<?php echo $this->get_field_name($key); ?><?php echo $k ?>"
                    class="asitheme-widget-image-upload-input"
                    id="<?php echo $this->get_field_id($key); ?>"
                    value="<?php echo esc_attr($value); ?>">
            <input
                    type="button"
                    class="button asitheme-widget-image-upload-add"
                    onclick="asitheme_widget_image_upload_add(this);"
                    data-id="<?php echo $this->get_field_id($key); ?>"
                    value="<?php _e('Upload Image', CHILD_THEME_SLUG) ?>">
            <input
                    style="display:<?php echo $url ? 'inline-block' : 'none' ?>;"
                    type="button"
                    class="button asitheme-widget-image-upload-remove"
                    onclick="asitheme_widget_image_upload_remove(this);"
                    data-id="<?php echo $this->get_field_id($key); ?>"
                    value="<?php _e('Remove') ?>">
        </div>
        <?php
    }

    public function asi_form_field_repeater($value, $key, $setting, $items) {
        ?>
        <div class="asi-widget-p asi-field-<?php echo esc_attr($setting['type']) ?>" data-field_id="<?php echo esc_attr($key); ?>">
            <label class="block"><?php echo $setting['label']; ?></label>
            <?php if (isset($setting['desc']) && $setting['desc']) { ?>
                <span class="desc"><?php echo esc_html($setting['desc']) ?></span>
            <?php } ?>
        </div>
        <div id="asitheme-widget-repeater-items-<?php echo $this->get_field_id($key); ?>" class="asitheme-widget-repeater-items ui-droppable ui-sortable">
            <?php
            if ($value && is_array($value)) {
                foreach ($value as $item) {
                    echo $this->show_item_repeater($key, $items, $item);
                }
            }
            ?>
        </div>
        <p>
            <input
                    type="button"
                    class="button asitheme-widget-image-upload-repeater-add"
                    onclick="asitheme_widget_repeater_add(this, '<?php echo $this->number; ?>', '<?php echo $this->get_field_id($key); ?>');"
                    value="<?php _e('Add') ?>">
        </p>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                asitheme_repeater_item['<?php echo $this->get_field_id($key); ?>'] = "<?php echo addslashes(trim(asitheme_minify_output($this->show_item_repeater($key, $items, array())))) ?>";
                asitheme_widget_repeater_sortable(jQuery('#asitheme-widget-repeater-items-<?php echo $this->get_field_id($key); ?>'));
            });
        </script>
        <?php
    }

    public function show_item_repeater($key, $items, $value) {
        ob_start();
        ?>
        <div class="asitheme-widget ui-sortable-handle">
            <div class="asitheme-widget-top" onclick="asitheme_widget_repeater_toggle(this);">
                <div class="asitheme-widget-title-action">
                    <button type="button" class="asitheme-widget-action hide-if-no-js" aria-expanded="true">
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="asitheme-widget-title">
                    <h3>
                        Item
                        <button type="button" class="button-link button-link-delete" aria-expanded="true" onclick="asitheme_widget_repeater_remove(this);">
                            <?php _e('Remove') ?>
                        </button>
                    </h3>
                </div>
            </div>
            <div class="asitheme-widget-inside">
                <div class="asitheme-widget-content">
                    <?php
                    foreach ($items as $k => $item) {
                        $function = $this->asi_field_function('form', $item['type']);
                        $v = isset($value[$k]) ? $value[$k] : '';
                        $this->$function($v, $key, $item, "[$k][]");
                    }
                    ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    public function asi_form_field_gallery($value, $key, $setting, $items) {
        ?>
        <div class="media-widget-control">
            <div class="media-widget-preview <?php echo esc_attr($this->id_base); ?>">
                <div class="attachment-media-view" onclick="asitheme_widget_gallery(this);">
                    <div class="placeholder"><?php echo esc_html($this->l10n['no_media_selected']); ?></div>
                </div>
            </div>
            <p class="media-widget-buttons">
                <button type="button" class="button edit-media selected" onclick="asitheme_widget_gallery(this);">
                    <?php echo esc_html($this->l10n['edit_media']); ?>
                </button>
                <button type="button" class="button select-media not-selected" onclick="asitheme_widget_gallery(this);">
                    <?php echo esc_html($this->l10n['add_media']); ?>
                </button>
            </p>
            <div class="media-widget-fields"></div>
            <?php foreach ($items as $name => $value) { ?>
                <input
                        type="hidden"
                        data-property="<?php echo esc_attr($name); ?>"
                        class="media-widget-instance-property"
                        name="<?php echo esc_attr($this->get_field_name($name)); ?>"
                        id="<?php echo esc_attr($this->get_field_id($name)); ?>"
                        value="<?php echo esc_attr(is_array($value) ? join(',', $value) : strval($value)); ?>">
            <?php } ?>
        </div>
        <?php
    }

    public function asi_form_field_group($instance, $settings) {
        ?>
        <div class="asi-field-group">
            <fieldset><label class="block"><?php echo $settings['label']; ?></label></fieldset>
            <div class="items">
                <?php
                foreach ($settings['items'] as $key => $setting) {

                    $setting['std'] = isset($setting['std']) ? $setting['std'] : '';
                    $value = isset($instance[$key]) ? $instance[$key] : $setting['std'];
                    $function = $this->asi_field_function('form', $setting['type']);

                    switch ($setting['type']) {

                        case 'group' :
                            call_user_func(array($this, $function), $instance, $setting);
                            break;

                        case 'repeater' :
                        case 'gallery' :
                            $items = $setting['items'];
                            if ($items) {
                                //$this->$function($value, $key, $setting, $items);
                                call_user_func(array($this, $function), $value, $key, $setting, $items);
                            }
                            break;

                        // Default: text
                        default :
                            //$this->$function($value, $key, $setting);
                            call_user_func(array($this, $function), $value, $key, $setting);
                            break;
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }

}
