<?php
if (!defined('ABSPATH')) {
    exit;
}

class ASI_Widget_Quotes extends ASI_Widget {

    public function __construct() {
        $this->widget_id = 'asitheme_widget_quotes';
        $this->widget_name = sprintf(__('%s - Quotes', CHILD_THEME_SLUG), CHILD_THEME_AUTHOR);
        $this->widget_cssclass = 'asitheme-widget asitheme-widget-quotes';
        $this->widget_description = '';
        $this->settings = array(
            'items' => array(
                'type' => 'repeater',
                'label' => __('Items', CHILD_THEME_SLUG),
                'items' => array(
                    'quote' => array(
                        'type' => 'textarea',
                        'std' => "I'm not afraid of death; I just don't want to be there when it happens.",
                        'label' => __('Quote'),
                    ),
                    'author' => array(
                        'type' => 'text',
                        'std' => 'Woody Allen',
                        'label' => __('Author'),
                    ),
                )
            )
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
            }
        }
        if (!$instance['items']) {
            return;
        }
        ob_start();
        $this->widget_start($args, $instance);
        ?>
        <div class="<?php echo esc_attr($this->widget_cssclass) ?>">
            <img class="a-top" src="<?php echo CHILD_URL ?>/assets/images/icon-arrow-right.svg" alt="">
            <?php foreach ($instance['items'] as $item) { ?>
                <div class="quote">
                    <cite><?php echo $item['quote'] ?></cite>
                    <span><?php echo $item['author'] ?></span>
                </div>
            <?php } ?>
            <img class="a-bottom" src="<?php echo CHILD_URL ?>/assets/images/icon-arrow-right.svg" alt="">
        </div>
        <style>

        </style>
        <?php
        $this->widget_end($args);
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }

}
