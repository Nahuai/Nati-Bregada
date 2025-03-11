<?php
if (!defined('ABSPATH')) {
    exit;
}

class ASI_Widget_Features extends ASI_Widget {

    public function __construct() {
        $this->widget_id = 'asitheme_widget_features';
        $this->widget_name = sprintf(__('%s - Features', CHILD_THEME_SLUG), CHILD_THEME_AUTHOR);
        $this->widget_cssclass = 'asitheme-widget asitheme-widget-features';
        $this->widget_description = '';
        $this->settings = array(
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
                'type' => 'text',
                'std' => 'Features',
                'label' => __('Title', CHILD_THEME_SLUG),
            ),
            'items' => array(
                'type' => 'repeater',
                'label' => __('Items', CHILD_THEME_SLUG),
                'items' => array(
                    'title' => array(
                        'type' => 'textarea',
                        'std' => 'Feature',
                        'label' => __('Title', CHILD_THEME_SLUG),
                    ),
                    'text' => array(
                        'type' => 'textarea',
                        'std' => 'Lorem ipsum dolor sit amet, consectetur ol adipiscing elit.',
                        'label' => __('Text', CHILD_THEME_SLUG),
                        'desc' => __('Each item in a line', CHILD_THEME_SLUG),
                    ),
                    'link' => array(
                        'type' => 'text',
                        'std' => get_home_url(),
                        'label' => __('Link', CHILD_THEME_SLUG),
                    ),
                    'image' => array(
                        'type' => 'image',
                        'std' => CHILD_URL . '/assets/images/widget-featured-1.jpg',
                        'label' => __('Image', CHILD_THEME_SLUG),
                    ),
                )
            ),
        );

        parent::__construct();
    }

    public function widget($args, $instance) {
        if ($this->settings) {
            foreach ($this->settings as $k => $v) {
                if (isset($v['std']) && $v['std']) {
                    $instance[$k] = isset($instance[$k]) ? $instance[$k] : $v['std'];
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
        <?php if ($instance['items'] && is_array($instance['items'])) { ?>
            <div class="items">
            <?php
            foreach ($instance['items'] as $item) {
                $img = '';
                if ($item['image']) {
                    $image = asitheme_get_image_sizes($item['image']);
                    if ($image) {
                        $img = $image['url'];
                    }
                }
                ?>
                <?php if ($item['link']) { ?>
                    <a class="item" href="<?php echo esc_url($item['link']) ?>">
                <?php } else { ?>
                    <div class="item">
                <?php } ?>
                <?php if ($img) { ?>
                    <div class="image">
                        <img src="<?php echo esc_url($img); ?>" alt="">
                    </div>
                <?php } ?>
                <?php if ($item['title'] || $item['text']) { ?>
                    <div class="text">
                        <?php if ($item['title']) { ?>
                            <h3 class="i-title"><?php echo $item['title'] ?></h3>
                        <?php } ?>
                        <?php if ($item['text']) { ?>
                            <div class="i-text">
                                <?php echo asitheme_nl2p($item['text']); ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if ($item['link']) { ?>
                    </a>
                <?php } else { ?>
                    </div>
                <?php } ?>
            <?php } ?>
            </div>
        <?php } ?>
        </div>
        <style>
            <?php echo '#' . $this->id ?>
            .asitheme-widget-features h2,
            <?php echo '#' . $this->id ?> .asitheme-widget-features h3,
            <?php echo '#' . $this->id ?> .asitheme-widget-features .i-text{
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
