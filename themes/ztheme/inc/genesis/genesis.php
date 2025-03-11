<?php
//* Add Accessibility support
add_theme_support('genesis-accessibility', array('headings', 'drop-down-menu', 'search-form', 'skip-links', 'rems'));

//* Add viewport meta tag for mobile browsers
add_theme_support('genesis-responsive-viewport');

//* Remove genesis layouts
genesis_unregister_layout('content-sidebar');
genesis_unregister_layout('sidebar-content');
genesis_unregister_layout('content-sidebar-sidebar');
genesis_unregister_layout('sidebar-sidebar-content');
genesis_unregister_layout('sidebar-content-sidebar');
genesis_unregister_layout('full-width-content');

//* Remove sidebars
unregister_sidebar('sidebar');
unregister_sidebar('sidebar-alt');

//* Add support for 3-column footer widgets
add_theme_support('genesis-footer-widgets', 3);

//* Add support for after entry widget
add_theme_support('genesis-after-entry-widget-area');

//* Remove secondary menu
remove_action('genesis_after_header', 'genesis_do_subnav');

//* Move navigation to header
remove_action('genesis_after_header', 'genesis_do_nav');
add_action('genesis_header_right', 'genesis_do_nav');

add_action('wp', 'asitheme_genesis_wp');

function asitheme_genesis_wp() {

    if (is_home() || is_author() || is_archive() || is_search() || is_page_template('page_blog.php')) {

        //* Remove the entry meta in the entry header (requires HTML5 theme support)
        remove_action('genesis_entry_header', 'genesis_post_info', 12);

        //* Remove the entry meta in the entry footer (requires HTML5 theme support)
        remove_action('genesis_entry_footer', 'genesis_post_meta');

        remove_action('genesis_entry_content', 'genesis_do_post_image', 8);
        remove_action('genesis_entry_content', 'genesis_do_post_content');
        add_action('genesis_entry_header', 'asitheme_genesis_do_post_image', 1);

        add_action('genesis_entry_header', 'asitheme_genesis_do_post_content_start_wrap', 2);
        add_action('genesis_entry_footer', 'asitheme_genesis_do_post_content_end_wrap', 100);

        add_filter('genesis_pre_get_option_content_archive', 'asitheme_genesis_pre_get_option_content_archive');
    }
}

function asitheme_genesis_do_post_image() {

    global $post;

    $image = false;
    $img = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium_large');
    if ($img && isset($img[0]) && $img[0]) {
        $image = $img[0];
    }
    if (!$image) {
        $image = CHILD_URL . '/assets/images/default-post.jpg';
    }
    if (!$image) {
        return;
    }
    ?>
    <a class="entry-image-link" href="<?php echo get_permalink($post->ID); ?>">
        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($post->post_title) ?>">
    </a>
    <?php
}

function asitheme_genesis_do_post_content_start_wrap() {
    echo '<div class="entry-content-wrap">';
}

function asitheme_genesis_do_post_content_end_wrap() {
    echo '</div>';
}

//* Footer copyright shortcode
add_shortcode(CHILD_THEME_SLUG . '_footer_copyright', function ($atts) {
    return sprintf(date('Y') . ' © <a href="' . CHILD_THEME_THEMEURI . '" rel="nofollow" target="_blank">' . CHILD_THEME_NAME . '</a> by <a href="' . CHILD_THEME_AUTHORURI . '" rel="nofollow" target="_blank">' . CHILD_THEME_AUTHOR . '</a>');
});

add_action('after_switch_theme', 'asitheme_after_switch_theme');

function asitheme_after_switch_theme() {
    if (!genesis_get_option('footer_text')) {
        genesis_update_settings(
            [
                'footer_text' => '[' . CHILD_THEME_SLUG . '_footer_copyright]',
            ]
        );
    }
}

//* Footer replace <p> with <div>
add_filter('genesis_footer_output', 'asitheme_genesis_footer_wrap', 1);

function asitheme_genesis_footer_wrap($output) {
    if (!$output) {
        return $output;
    }
    $output = preg_replace('/<p>/', '', $output, 1);
    $output = strrev($output);
    $output = preg_replace('/>p\/</', '', $output, 1);
    $output = strrev($output);
    $output = '<div>' . $output . '</div>';
    return $output;
}

//* Footer social
add_filter('genesis_footer_output', 'asitheme_genesis_footer_social');

function asitheme_genesis_footer_social($output) {
    if (!$output) {
        return $output;
    }

    $social_footer = get_theme_mod(CHILD_THEME_SLUG . '_social_footer', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_social_footer']);
    if (!$social_footer) {
        return $output;
    }

    $class = $output ? 'm' : '';
    $output .= '<div class="social-wrapper social-footer ' . $class . '">';
    foreach (CHILD_THEME_DEFAULTS_NETWORKS as $key => $value) {
        $social_link = get_theme_mod(CHILD_THEME_SLUG . '_social_' . $value, false);
        if ($social_link) {
            $output .= '<a class="social ' . str_replace('_', '-', $value) . '" href="' . $social_link . '" title="' . $key . '" target="_blank">';
            $output .= '<i class="fa fa-' . str_replace('_', '-', $value) . '"></i>';
            $output .= '</a>';
        }
    }
    $output .= '</div>';

    return $output;
}

//* Modify search icon
add_filter('genesis_search_button_text', 'asitheme_genesis_search_button_text_icon');

function asitheme_genesis_search_button_text_icon() {
    return esc_attr('&#xf002;');
}

function asitheme_genesis_search_button_text() {
    return __('Search', CHILD_THEME_SLUG);
}

add_filter('genesis_search_text', 'asitheme_genesis_search_text');

function asitheme_genesis_search_text() {
    return __('Search', CHILD_THEME_SLUG);
}

//* Add menu responsive button
add_action('genesis_header', 'asitheme_genesis_header');

function asitheme_genesis_header() {
    echo '<a id="menu-btn" href="#">';
    echo '<div class="line"></div>';
    echo '<div class="line"></div>';
    echo '<div class="line"></div>';
    echo '</a>';
}

function asitheme_genesis_pre_get_option_content_archive() {
    return 'excerpts';
}

add_theme_support('custom-logo', array(
    'height' => 80,
    'width' => 270,
    'flex-height' => true,
    'flex-width' => true,
));

add_filter('genesis_seo_title', 'asitheme_genesis_seo_title', 10, 3);

function asitheme_genesis_seo_title($title, $inside, $wrap) {

    if (function_exists('has_custom_logo') && has_custom_logo()) {
        $inside = sprintf('%s', get_custom_logo());
    } else {
        $inside = sprintf('<a href="%s" title="%s">%s</a>', trailingslashit(home_url()), esc_attr(get_bloginfo('name')), esc_attr(get_bloginfo('name')));
    }

    $title = sprintf('<%1$s %2$s>%3$s</%1$s>', $wrap, genesis_attr('site-title'), $inside);

    return $title;
}

add_action('genesis_before_while', 'asitheme_genesis_before_while', 20);

function asitheme_genesis_before_while() {
    if (is_home() || is_author() || is_archive() || is_search() || is_page_template('page_blog.php')) {
        echo '<div class="asi-posts-wrapper">';
    }
}

add_action('genesis_after_endwhile', 'asitheme_genesis_after_endwhile', 5);

function asitheme_genesis_after_endwhile() {
    if (is_home() || is_author() || is_archive() || is_search() || is_page_template('page_blog.php')) {
        echo '</div>';
    }
}

add_filter('genesis_search_form', 'asitheme_genesis_search_form');

function asitheme_genesis_search_form($form) {
    $form = str_replace('type="search" name="s"', 'type="search" required="required" name="s"', $form);
    return $form;
}

add_filter('genesis_attr_site-header', 'asitheme_genesis_attr_site_header');

//* Add classes for the relative or absolute header into site_header
function asitheme_genesis_attr_site_header($attributes) {
    if (isset($attributes['class']) && $attributes['class']) {
        $header_position = get_theme_mod(CHILD_THEME_SLUG . '_header_position', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_position']);
        $attributes['class'] = 'site-header ' . $header_position;
    }
    return $attributes;
}

add_filter('genesis_attr_site-inner', 'asitheme_genesis_attr_site_inner');

//* Add classes for the relative or absolute header into site_inner
function asitheme_genesis_attr_site_inner($attributes) {
    if (isset($attributes['class']) && $attributes['class']) {
        $header_position = get_theme_mod(CHILD_THEME_SLUG . '_header_position', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_position']);
        $attributes['class'] = 'site-inner ' . $header_position;
    }
    return $attributes;
}

//* Remove metaboxes of Genesis Theme Settings
add_action('genesis_admin_before_metaboxes', 'asitheme_genesis_admin_before_metaboxes');

function asitheme_genesis_admin_before_metaboxes() {
    remove_meta_box('genesis-theme-settings-header', 'toplevel_page_genesis', 'main');
}

//* Add admin custom css
add_action('admin_head', 'asitheme_genesis_admin_head');
add_action('customize_controls_print_styles', 'asitheme_genesis_admin_head');

function asitheme_genesis_admin_head() {
    wp_enqueue_style('admin-genesis-style', CHILD_URL . '/assets/css/admin-genesis-style.css', array(), CHILD_THEME_VERSION);
}

//* Add search widget
add_filter('genesis_markup_title-area_close', 'asitheme_genesis_markup_title_area_close', 10, 2);

function asitheme_genesis_markup_title_area_close($close_html, $args) {

    $header_search = get_theme_mod(CHILD_THEME_SLUG . '_header_search', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_search']);

    if ($close_html && $header_search) {
        ob_start();
        the_widget('WP_Widget_Search', array(), array('before_widget' => '<div class="widget widget-search-center %s">'));
        $additional_html = ob_get_contents();
        ob_end_clean();
        $close_html = $close_html . $additional_html;
    }
    return $close_html;
}

//* 404
add_action('genesis_loop', 'asitheme_genesis_404');

function asitheme_genesis_404() {
    if (!is_404()) {
        return;
    }
    remove_filter('genesis_search_button_text', 'asitheme_genesis_search_button_text_icon');
    add_filter('genesis_search_button_text', 'asitheme_genesis_search_button_text');
    add_action('genesis_pre_get_sitemap', '__return_false');
}
