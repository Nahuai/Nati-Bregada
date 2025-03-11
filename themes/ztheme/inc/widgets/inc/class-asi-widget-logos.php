<?php
if (!defined('ABSPATH')) {
    exit;
}

class ASI_Widget_Logos extends ASI_Widget {

    public function __construct() {
        $this->widget_id = 'asitheme_widget_logos';
        $this->widget_name = sprintf(__('%s - Logos', CHILD_THEME_SLUG), CHILD_THEME_AUTHOR);
        $this->widget_cssclass = 'asitheme-widget asitheme-widget-logos';
        $this->widget_description = '';
        $this->settings = array(
            'background_color' => array(
                'type' => 'text',
                'std' => '#fff',
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
                'type' => 'text',
                'std' => __('Some of our customers', CHILD_THEME_SLUG),
                'label' => __('Title', CHILD_THEME_SLUG),
            ),
            'logos' => array(
                'type' => 'repeater',
                'label' => __('Logos', CHILD_THEME_SLUG),
                'items' => array(
                    'link' => array(
                        'type' => 'text',
                        'std' => '#',
                        'label' => __('Link', CHILD_THEME_SLUG),
                    ),
                    'target' => array(
                        'type' => 'checkbox',
                        'std' => 1,
                        'label' => __('Open in a new tab', CHILD_THEME_SLUG),
                    ),
                    'image' => array(
                        'type' => 'image',
                        'std' => '',
                        'label' => __('Image', CHILD_THEME_SLUG),
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
        ?>
        <div class="<?php echo esc_attr($this->widget_cssclass) ?>" <?php echo isset($instance['background_color']) && trim($instance['background_color']) ? 'style="background-color: ' . trim($instance['background_color']) . ';"' : ''; ?>>
            <?php
            if ($instance['title']) {
                genesis_markup(array('open' => "<$h_wrap %s>", 'context' => 'title'));
                echo apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
                genesis_markup(array('close' => "</$h_wrap>", 'context' => 'title'));
            }
            ?>
            <?php if ($instance['logos']) { ?>
                <div class="logos-wrapper">
                    <?php foreach ($instance['logos'] as $l) { ?>
                        <?php
                        $img = '';
                        if ($l['image']) {
                            $image = asitheme_get_image_sizes($l['image']);
                            if ($image) {
                                $img = $image['url'];
                            }
                        }
                        $l['link'] = trim($l['link']);
                        $target = 'target="_blank"';
                        if (isset($l['target']) && !$l['target']) {
                            $target = '';
                        }
                        ?>
                        <?php if ($l['link']) { ?>
                            <a class="logo" href="<?php echo $l['link']; ?>" <?php echo $target ?>>
                                <img src="<?php echo $img; ?>" alt="">
                            </a>
                        <?php } else { ?>
                            <span class="logo">
                                <img src="<?php echo $img; ?>" alt="">
                            </span>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <style>
            <?php echo '#' . $this->id ?>
            .asitheme-widget-logos .title{
                color: <?php echo $instance['text_color']; ?>;
            }
        </style>
        <?php
        $this->widget_end($args);
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }

}
