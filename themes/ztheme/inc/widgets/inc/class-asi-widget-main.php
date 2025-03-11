<?php
if (!defined('ABSPATH')) {
    exit;
}

class ASI_Widget_Main extends ASI_Widget {

    public function __construct() {
        $this->widget_id = 'asitheme_widget_main';
        $this->widget_name = sprintf(__('%s - Main', CHILD_THEME_SLUG), CHILD_THEME_AUTHOR);
        $this->widget_cssclass = 'asitheme-widget asitheme-widget-main';
        $this->widget_description = '';
        $this->settings = array(
            'border_color' => array(
                'type' => 'text',
                'std' => '#708C8E',
                'label' => __('Border color', CHILD_THEME_SLUG),
                'class_input' => 'widget-color-picker'
            ),
            'background_color' => array(
                'type' => 'text',
                'std' => '',
                'label' => __('Background color', CHILD_THEME_SLUG),
                'class_input' => 'widget-color-picker'
            ),
            'text_color' => array(
                'type' => 'text',
                'std' => '#000',
                'label' => __('Text color', CHILD_THEME_SLUG),
                'class_input' => 'widget-color-picker'
            ),
            'title' => array(
                'type' => 'textarea',
                'std' => "New York\nstyle never",
                'label' => __('Title'),
            ),
            'text' => array(
                'type' => 'textarea',
                'std' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eu eros dui. Aliquam erat volutpat. Pellentesque quis efficitur sapien.',
                'label' => __('Text'),
            ),
            'group_1' => array(
                'type' => 'group',
                'label' => __('Button', CHILD_THEME_SLUG),
                'items' => array(
                    'button_text' => array(
                        'type' => 'text',
                        'std' => 'Cras ut risus',
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
            'image' => array(
                'type' => 'image',
                'std' => '',
                'label' => __('Image', CHILD_THEME_SLUG),
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
            <?php if ($img) { ?>
                <div class="im">
                    <img src="<?php echo esc_url($img) ?>" alt="">
                </div>
            <?php } ?>
            <div class="inner">
                <?php
                if ($instance['title']) {
                    genesis_markup(array('open' => "<$h_wrap %s>", 'context' => 'title'));
                    echo do_shortcode(nl2br($instance['title']));
                    genesis_markup(array('close' => "</$h_wrap>", 'context' => 'title'));
                }
                ?>
                <?php if ($instance['text']) { ?>
                    <p class="text">
                        <?php echo do_shortcode($instance['text']) ?>
                    </p>
                <?php } ?>
                <?php if ($instance['button_link'] && $instance['button_text']) { ?>
                    <a <?php echo $instance['button_target'] ? 'target="_blank"' : ''; ?> class="b" href="<?php echo $instance['button_link']; ?>">
                        <?php echo $instance['button_text'] ?>
                        <img src="<?php echo CHILD_URL ?>/assets/images/icon-arrow-right.svg" alt="">
                    </a>
                <?php } ?>
            </div>
            <div class="clear"></div>
        </div>
        <style>
            <?php echo '#' . $this->id ?>
            .asitheme-widget-main .inner{
                border-color: <?php echo $instance['border_color'] ? $instance['border_color'] : 'transparent'; ?>;
                background-color: <?php echo $instance['background_color'] ? $instance['background_color'] : 'transparent'; ?>;
            }
            <?php echo '#' . $this->id ?>
            .asitheme-widget-main *{
                color: <?php echo $instance['text_color']; ?>;
            }
            @media only screen and (max-width: 650px){
            <?php echo '#' . $this->id ?>
                .asitheme-widget-main .inner{
                    border-color: transparent;
                    background-color: transparent;
                }
            <?php echo '#' . $this->id ?>
                .asitheme-widget-main{
                    border-color: <?php echo $instance['border_color'] ? $instance['border_color'] : 'transparent'; ?>;
                    background-color: <?php echo $instance['background_color'] ? $instance['background_color'] : 'transparent'; ?>;
                }
            }
        </style>
        <?php
        $this->widget_end($args);
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }

}
