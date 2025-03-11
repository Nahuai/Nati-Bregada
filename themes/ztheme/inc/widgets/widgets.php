<?php

if (!defined('ABSPATH')) {
    exit;
}

// Include widget classes.
include_once(CHILD_DIR . '/inc/widgets/inc/abstract-asi-widget.php');
include_once(CHILD_DIR . '/inc/widgets/inc/class-asi-widget-main.php');
include_once(CHILD_DIR . '/inc/widgets/inc/class-asi-widget-features.php');
include_once(CHILD_DIR . '/inc/widgets/inc/class-asi-widget-logos.php');
include_once(CHILD_DIR . '/inc/widgets/inc/class-asi-widget-two-blocks.php');
include_once(CHILD_DIR . '/inc/widgets/inc/class-asi-widget-quotes.php');

add_action('widgets_init', 'asi_register_widgets');

function asi_register_widgets() {

    //* We declare the widgets areas

    //* Add support for Homepage widgets
    genesis_register_sidebar(array(
        'id' => 'asi-front-page',
        'name' => __('Homepage', CHILD_THEME_SLUG),
    ));

    //* Add support for before entry widget
    genesis_register_sidebar(array(
        'id' => 'asi-before-entry',
        'name' => __('Before entry', CHILD_THEME_SLUG),
        'description' => __('Widgets in this widget area will display before single entries.', CHILD_THEME_SLUG)
    ));

    register_widget('ASI_Widget_Main');
    register_widget('ASI_Widget_Features');
    register_widget('ASI_Widget_Logos');
    register_widget('ASI_Widget_Two_Blocks');
    register_widget('ASI_Widget_Quotes');
}

add_action('wp_enqueue_scripts', 'asitheme_widgets_wp_enqueue_scripts');

function asitheme_widgets_wp_enqueue_scripts() {
    wp_enqueue_style('asitheme-widgets', CHILD_URL . '/assets/css/widgets.css', array(), CHILD_THEME_VERSION);
    wp_enqueue_script('asitheme-widgets', CHILD_URL . '/assets/js/widgets.js', array(), CHILD_THEME_VERSION, true);
}

add_action('wp_head', 'asitheme_widgets_wp_head');

function asitheme_widgets_wp_head() {
    ?>
    <script type="text/javascript">
        var asitheme_magnific_tCounter = '<?php echo esc_js(__('%curr% of %total%', CHILD_THEME_SLUG)); ?>';
    </script>
    <?php
}

//* Add endpoints custom URLs in Appearance > Menus > Pages.
add_action('admin_head-nav-menus.php', 'asitheme_add_nav_menu_meta_boxes');

function asitheme_add_nav_menu_meta_boxes() {
    $sidebars_widgets = wp_get_sidebars_widgets();
    if ($sidebars_widgets['asi-front-page']) {
        add_meta_box('asitheme_nav_menu_links_widgets', sprintf(__('%s Widgets links', CHILD_THEME_SLUG), CHILD_THEME_AUTHOR), 'asitheme_nav_menu_links_widgets', 'nav-menus', 'side', 'low');
    }
}

//* Add box to admin menu page
function asitheme_nav_menu_links_widgets() {

    global $wp_registered_widgets;

    $sidebars_widgets = wp_get_sidebars_widgets();

    $front_pages = get_posts(array(
        'post_type' => 'page',
        'meta_key' => '_wp_page_template',
        'meta_value' => 'templates/front-page.php',
    ));
    $page_id = 0;
    $url = '';
    foreach ($front_pages as $p) {
        $link = get_permalink($p->ID);
        if (get_permalink($p->ID) == home_url('/')) {
            $page_id = $p->ID;
            $url = $link;
        }
    }
    if ($url == '') {
        $url = get_permalink($front_pages[0]->ID);
        $page_id = $front_pages[0]->ID;
    }

    ?>
    <div id="posttype-asitheme-menu-widgets" class="posttypediv">
        <div id="tabs-panel-asitheme-menu-widgets" class="tabs-panel tabs-panel-active">
            <ul id="asitheme-menu-widgets-checklist" class="categorychecklist form-no-clear">
                <?php
                $i = -1;
                foreach ($sidebars_widgets['asi-front-page'] as $widget_id) {
                    $id_base = _get_widget_id_base($widget_id);
                    $instance = get_option('widget_' . $id_base);
                    $id = str_replace($id_base . '-', '', $widget_id);
                    if (isset($instance[$id]) && $instance[$id]) {
                        $title = $instance[$id]['title'];
                        if ($title == '') {
                            $title = $wp_registered_widgets[$widget_id]['name'];
                        }
                        ?>
                        <li>
                            <label class="menu-item-title">
                                <input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-object-id]" value="<?php echo esc_attr($page_id); ?>"/>
                                <?php echo esc_html($wp_registered_widgets[$widget_id]['name'] . ' - ' . $instance[$id]['title']); ?>
                            </label>
                            <input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-type]" value="custom"/>
                            <input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-title]" value="<?php echo esc_html($title); ?>"/>
                            <input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-url]" value="<?php echo esc_url($url . '#' . $widget_id); ?>"/>
                            <input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-classes]" value="widget-link">
                        </li>
                        <?php
                    }
                    $i--;
                }
                ?>
            </ul>
        </div>
        <p class="button-controls wp-clearfix">
			<span class="add-to-menu">
				<input type="submit" class="button submit-add-to-menu right" value="Añadir al menú" name="add-asitheme-menu-widgets-item" id="submit-posttype-asitheme-menu-widgets">
				<span class="spinner"></span>
			</span>
        </p>
    </div>
    <?php
}

if (!function_exists('asitheme_get_image_sizes')) {

    function asitheme_get_image_sizes($post_id) {
        $post = get_post($post_id);
        if (!$post)
            return false;
        $a = array();
        $id = $post->ID;
        $src = wp_get_attachment_image_src($id, 'full');
        $a['url'] = $src[0];
        $a['width'] = $src[1];
        $a['height'] = $src[2];
        if ($sizes = get_intermediate_image_sizes()) {
            $a['sizes'] = array();
            foreach ($sizes as $size) {
                $src = wp_get_attachment_image_src($id, $size);
                $a['sizes'][$size] = $src[0];
                $a['sizes'][$size . '-width'] = $src[1];
                $a['sizes'][$size . '-height'] = $src[2];
            }
        }
        return $a;
    }
}

if (!function_exists('asitheme_minify_output')) {

    function asitheme_minify_output($buffer) {
        $search = array(
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s'       // shorten multiple whitespace sequences
        );
        $replace = array(
            '>',
            '<',
            '\\1'
        );
        $buffer = preg_replace($search, $replace, $buffer);
        return $buffer;
    }
}

//* Add widget area before entry
add_action('genesis_entry_content', 'asitheme_widgets_genesis_entry_content', 0);

function asitheme_widgets_genesis_entry_content() {

    if (is_singular('post') && is_active_sidebar('asi-before-entry')) {
        genesis_widget_area('asi-before-entry', array(
            'before' => '<div class="asi-before-entry widget-area">',
            'after' => '</div>',
        ));
    }
}

function asitheme_is_first_widget_in_front_page($sidebar_id, $widget_id) {
    if ($sidebar_id != 'asi-front-page') {
        return false;
    }
    global $asi_all_sidebars_widgets;
    if (!$asi_all_sidebars_widgets) {
        $asi_all_sidebars_widgets = wp_get_sidebars_widgets();
    }
    return ($asi_all_sidebars_widgets[$sidebar_id][0] == $widget_id) ? true : false;
}