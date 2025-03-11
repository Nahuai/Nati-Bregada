<?php
if (!defined('ABSPATH')) {
    exit;
}

class ASI_Widget_WooCommerce_Cart extends ASI_Widget {

    public function __construct() {
        $this->widget_id = 'asitheme_widget_woocommerce_cart';
        $this->widget_name = sprintf(__('%s - WooCommerce Cart', CHILD_THEME_SLUG), CHILD_THEME_AUTHOR);
        $this->widget_cssclass = 'asitheme-widget asitheme-widget-woocommerce-cart';
        $this->widget_description = __('Link to WooCommerce cart', CHILD_THEME_SLUG);
        $this->settings = array();
        parent::__construct();
    }

    public function widget($args, $instance) {
        ob_start();
        $this->widget_start($args, $instance);
        $count = WC()->cart->cart_contents_count;
        ?>
        <a class="cart-contents" href="<?php echo wc_get_cart_url() ?>" title="<?php __('View cart', CHILD_THEME_SLUG) ?>">
            <i class="fa fa-shopping-cart"></i>
            <span class="cart-contents-count"><?php esc_html($count) ?></span>
        </a>
        <?php
        $this->widget_end($args);
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }

}
