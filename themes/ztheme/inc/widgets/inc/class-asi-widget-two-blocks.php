<?php
if (!defined('ABSPATH')) {
    exit;
}

class ASI_Widget_Two_Blocks extends ASI_Widget {

    public function __construct() {
        $this->widget_id = 'asitheme_widget_two_blocks';
        $this->widget_name = sprintf(__('%s - Two Blocks', CHILD_THEME_SLUG), CHILD_THEME_AUTHOR);
        $this->widget_cssclass = 'asitheme-widget asitheme-widget-two-blocks';
        $this->widget_description = __('This widgets allows us to create a space with a block of text and another image', CHILD_THEME_SLUG);

        $button_color = get_theme_mod(CHILD_THEME_SLUG . '_button_color', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_color']);
        $button_background = get_theme_mod(CHILD_THEME_SLUG . '_button_background', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_background']);
        $button_border = get_theme_mod(CHILD_THEME_SLUG . '_button_border_color', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_border_color']);
        $button_color_hover = get_theme_mod(CHILD_THEME_SLUG . '_button_color_hover', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_color_hover']);
        $button_background_hover = get_theme_mod(CHILD_THEME_SLUG . '_button_background_hover', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_background_hover']);
        $button_border_hover = get_theme_mod(CHILD_THEME_SLUG . '_button_border_color_hover', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_border_color_hover']);

        $this->settings = array(
            'image' => array(
                'type' => 'image',
                'std' => '',
                'label' => __('Image', CHILD_THEME_SLUG),
                'desc' => __('The image will be seen in the background, therefore, we recommend putting an image where the important element to show is centered', CHILD_THEME_SLUG)
            ),
            'text_position' => array(
                'type' => 'select',
                'std' => 'left',
                'label' => __('Text position', CHILD_THEME_SLUG),
                'options' => array(
                    'left' => __('Left'),
                    'right' => __('Right')
                )
            ),
            'background_color' => array(
                'type' => 'text',
                'std' => '#708C8E',
                'label' => __('Background color', CHILD_THEME_SLUG),
                'class_input' => 'widget-color-picker'
            ),
            'text_color' => array(
                'type' => 'text',
                'std' => '#fff',
                'label' => __('Text color', CHILD_THEME_SLUG),
                'class_input' => 'widget-color-picker'
            ),
            'text_align' => array(
                'type' => 'select',
                'std' => 'center',
                'label' => __('Text align', CHILD_THEME_SLUG),
                'options' => array(
                    'left' => __('Left'),
                    'right' => __('Right'),
                    'center' => __('Center')
                )
            ),
            'subtitle' => array(
                'type' => 'textarea',
                'std' => "Hi there, I'm a Genesis<br>Wordpress Theme",
                'label' => __('Subtitle', CHILD_THEME_SLUG),
            ),
            'title' => array(
                'type' => 'textarea',
                'std' => "LOLA\nSTYLE",
                'label' => __('Title', CHILD_THEME_SLUG),
            ),
            'title_size_desktop' => array(
                'type' => 'range',
                'std' => '126',
                'label' => __('Size (Desktop)'),
                'min' => 10,
                'max' => 200,
                'step' => 1,
            ),
            'title_size_mobile' => array(
                'type' => 'range',
                'std' => '90',
                'label' => __('Size (Mobile)'),
                'min' => 10,
                'max' => 200,
                'step' => 1,
            ),
            'text' => array(
                'type' => 'textarea',
                'std' => '',
                'label' => __('Text', CHILD_THEME_SLUG),
                'desc' => __('Each paragraph in a different line', CHILD_THEME_SLUG),
            ),
            'group_1' => array(
                'type' => 'group',
                'label' => __('Button', CHILD_THEME_SLUG),
                'items' => array(
                    'button_text' => array(
                        'type' => 'text',
                        'std' => 'Contact',
                        'label' => __('Button text', CHILD_THEME_SLUG),
                    ),
                    'button_link' => array(
                        'type' => 'text',
                        'std' => get_home_url(),
                        'label' => __('Button link', CHILD_THEME_SLUG),
                    ),
                    'button_target' => array(
                        'type' => 'checkbox',
                        'label' => __('Open in a new tab', CHILD_THEME_SLUG),
                    ),
                )
            ),
            'group_colors' => array(
                'type' => 'group',
                'label' => __('Buttons', CHILD_THEME_SLUG),
                'items' => array(
                    'button_color' => array(
                        'type' => 'text',
                        'std' => $button_color,
                        'label' => __('Text color', CHILD_THEME_SLUG),
                        'class_input' => 'widget-color-picker'
                    ),
                    'button_background' => array(
                        'type' => 'text',
                        'std' => $button_background,
                        'label' => __('Background color', CHILD_THEME_SLUG),
                        'class_input' => 'widget-color-picker'
                    ),
                    'button_border_color' => array(
                        'type' => 'text',
                        'std' => $button_border,
                        'label' => __('Border color', CHILD_THEME_SLUG),
                        'class_input' => 'widget-color-picker'
                    ),
                    'button_color_hover' => array(
                        'type' => 'text',
                        'std' => $button_color_hover,
                        'label' => __('Text color when the mouse passes over', CHILD_THEME_SLUG),
                        'class_input' => 'widget-color-picker'
                    ),
                    'button_background_hover' => array(
                        'type' => 'text',
                        'std' => $button_background_hover,
                        'label' => __('Background color when the mouse passes over', CHILD_THEME_SLUG),
                        'class_input' => 'widget-color-picker'
                    ),
                    'button_border_color_hover' => array(
                        'type' => 'text',
                        'std' => $button_border_hover,
                        'label' => __('Border color when the mouse passes over', CHILD_THEME_SLUG),
                        'class_input' => 'widget-color-picker'
                    ),
                )
            ),
        );

        parent::__construct();
    }

    public function admin_head_widgets() {
        parent::admin_head_widgets();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    public function widget($args, $instance) {
        if ($this->settings) {
            foreach ($this->settings as $k => $v) {
                if (isset($v['std']) && $v['std']) {
                    $instance[$k] = isset($instance[$k]) ? $instance[$k] : $v['std'];
                }
                if (isset($v['type']) && $v['type'] == 'group') {
                    $items = $v['items'];
                    if ($items) {
                        foreach ($items as $kk => $vv) {
                            if (isset($vv['std']) && $vv['std']) {
                                $instance[$kk] = isset($instance[$kk]) ? $instance[$kk] : $vv['std'];
                            }
                        }
                    }
                }
            }
        }
        $h_wrap = 'h2';
        if (asitheme_is_first_widget_in_front_page($args['id'], $this->id)) {
            $h_wrap = 'h1';
        }
        ob_start();
        $this->widget_start($args, $instance);
        $img = '';
        if ($instance['image']) {
            $image = asitheme_get_image_sizes($instance['image']);
            if ($image) {
                $img = $image['url'];
            }
        }
        ?>
        <div class="<?php echo esc_attr($this->widget_cssclass) ?>">
            <div class="block-wrapper text-wrapper">
                <div class="block-inner">
                    <?php if ($instance['subtitle']) { ?>
                        <div class="subtitle">
                            <?php echo nl2br($instance['subtitle']); ?>
                        </div>
                    <?php } ?>
                    <?php
                    if ($instance['title']) {
                        genesis_markup(array('open' => "<$h_wrap %s>", 'context' => 'title'));
                        echo do_shortcode(nl2br($instance['title']));
                        genesis_markup(array('close' => "</$h_wrap>", 'context' => 'title'));
                    }
                    ?>
                    <?php if ($instance['text']) { ?>
                        <div class="text">
                            <?php echo nl2br($instance['text']); ?>
                        </div>
                    <?php } ?>
                    <?php if ($instance['button_link'] && $instance['button_text']) { ?>
                        <a <?php echo $instance['button_target'] ? 'target="_blank"' : ''; ?> class="button" href="<?php echo $instance['button_link']; ?>">
                            <?php echo $instance['button_text'] ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <?php if ($img) { ?>
                <div class="block-wrapper image-wrapper">&nbsp;</div>
            <?php } ?>
        </div>
        <style>
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks{
                background-color: <?php echo $instance['background_color'] ? $instance['background_color'] : 'transparent'; ?>;
            }
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks *{
                color: <?php echo $instance['text_color']; ?>;
            }
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks .text-wrapper{
                text-align: <?php echo $instance['text_align'] ?>;
            }
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks .image-wrapper{
                background-image: url('<?php echo $img; ?>');
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
            }
            <?php if($instance['text_position'] == 'right'){ ?>
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks .text-wrapper{
                order: 2;
            }
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks .image-wrapper{
                order: 1;
            }
            <?php } else { ?>
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks .text-wrapper{
                order: 1;
            }
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks .image-wrapper{
                order: 2;
            }
            <?php } ?>
            <?php echo '#' . $this->id ?>
            .asitheme-widget-two-blocks .text-wrapper .title{
                font-size: <?php echo $instance['title_size_desktop'] ?>px;
            }
            @media only screen and (max-width: 990px){
            <?php echo '#' . $this->id ?>
                .asitheme-widget-two-blocks .text-wrapper .title{
                    font-size: <?php echo $instance['title_size_mobile'] ?>px;
                }
            }
            @media only screen and (max-width: 650px){
            <?php echo '#' . $this->id ?>
                .asitheme-widget-two-blocks .text-wrapper{
                    order: 2;
                }

            <?php echo '#' . $this->id ?>
                .asitheme-widget-two-blocks .image-wrapper{
                    order: 1;
                }
            }
            /* button */
            <?php echo '#' . $this->id ?>
            .button{
                color: <?php echo trim($instance['button_color']) ? trim($instance['button_color']) : 'inherit'; ?>;
                background-color: <?php echo trim($instance['button_background']) ? trim($instance['button_background']) : 'transparent'; ?>;
                border-color: <?php echo trim($instance['button_border_color']) ? trim($instance['button_border_color']) : 'transparent'; ?>;
            }
            <?php echo '#' . $this->id ?>
            .button:hover{
                color: <?php echo trim($instance['button_color_hover']) ? trim($instance['button_color_hover']) : 'inherit'; ?>;
                background-color: <?php echo trim($instance['button_background_hover']) ? trim($instance['button_background_hover']) : 'transparent'; ?>;
                border-color: <?php echo trim($instance['button_border_color_hover']) ? trim($instance['button_border_color_hover']) : 'transparent'; ?>;
            }
        </style>
        <?php
        $this->widget_end($args);
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }

}
