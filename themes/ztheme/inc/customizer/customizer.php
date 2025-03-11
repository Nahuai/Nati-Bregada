<?php

if (!class_exists('WP_Customize_Control'))
    return NULL;

//* Include custom classes
require_once(CHILD_DIR . '/inc/customizer/inc/class-wp-customize-range.php');
require_once(CHILD_DIR . '/inc/customizer/inc/class-wp-customize-text-editor.php');
require_once(CHILD_DIR . '/inc/customizer/inc/class-wp-customize-google-font.php');
require_once(CHILD_DIR . '/inc/customizer/inc/class-wp-customize-multiselect.php');

//* Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
add_action('customize_controls_enqueue_scripts', 'asitheme_customize_controls_enqueue_scripts');

function asitheme_customize_controls_enqueue_scripts() {

    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css', array(), CHILD_THEME_VERSION);
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js', array(), CHILD_THEME_VERSION, true);

    wp_enqueue_style(CHILD_THEME_SLUG . '-customizer', CHILD_URL . '/assets/css/customizer.css', array(), CHILD_THEME_VERSION);
    wp_register_script(CHILD_THEME_SLUG . '-customizer', CHILD_URL . '/assets/js/customizer.js', array('customize-preview'), CHILD_THEME_VERSION, true);
    wp_localize_script(CHILD_THEME_SLUG . '-customizer', 'asitheme_customizer_slug', CHILD_THEME_SLUG);
    wp_enqueue_script(CHILD_THEME_SLUG . '-customizer');
}

add_action('customize_register', 'asitheme_customizer', 20);

function asitheme_customizer($wp_customize) {

    if (!isset($wp_customize)) {
        return;
    }

    $wp_customize->remove_section('colors');
    $wp_customize->remove_section('background_image');
    $wp_customize->remove_control('blog_title');
    $wp_customize->remove_section('genesis_header');

    asitheme_customizer_panel_colors($wp_customize);
    asitheme_customizer_panel_header($wp_customize);
    asitheme_customizer_panel_fonts($wp_customize);
    asitheme_customizer_panel_social($wp_customize);
}

function asitheme_sanitize_text($input) {
    return wp_kses_post(force_balance_tags($input));
}

function asitheme_customizer_panel_colors($wp_customize) {

    //* Add Colores panel
    $wp_customize->add_panel(CHILD_THEME_SLUG . '_colors', array(
        'title' => __('Colors', CHILD_THEME_SLUG),
        'priority' => 11,
    ));

    //* Add Botones section
    $wp_customize->add_section(CHILD_THEME_SLUG . '_colors_buttons', array(
        'title' => __('Buttons', CHILD_THEME_SLUG),
        'panel' => CHILD_THEME_SLUG . '_colors',
    ));

    //* Button color
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_button_color', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_color'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, CHILD_THEME_SLUG . '_button_color', array(
        'label' => __('Text color', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_colors_buttons',
        'settings' => CHILD_THEME_SLUG . '_button_color',
    )));

    //* Button background
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_button_background', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_background'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, CHILD_THEME_SLUG . '_button_background', array(
        'label' => __('Background color', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_colors_buttons',
        'settings' => CHILD_THEME_SLUG . '_button_background',
    )));

    //* Button border color
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_button_border_color', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_border_color'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, CHILD_THEME_SLUG . '_button_border_color', array(
        'label' => __('Border color', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_colors_buttons',
        'settings' => CHILD_THEME_SLUG . '_button_border_color',
    )));

    //* Button color hover
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_button_color_hover', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_color_hover'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, CHILD_THEME_SLUG . '_button_color_hover', array(
        'label' => __('Text color when the mouse passes over', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_colors_buttons',
        'settings' => CHILD_THEME_SLUG . '_button_color_hover',
    )));

    //* Button background hover
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_button_background_hover', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_background_hover'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, CHILD_THEME_SLUG . '_button_background_hover', array(
        'label' => __('Background color when the mouse passes over', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_colors_buttons',
        'settings' => CHILD_THEME_SLUG . '_button_background_hover',
    )));

    //* Button border color hover
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_button_border_color_hover', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_border_color_hover'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, CHILD_THEME_SLUG . '_button_border_color_hover', array(
        'label' => __('Border color when the mouse passes over', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_colors_buttons',
        'settings' => CHILD_THEME_SLUG . '_button_border_color_hover',
    )));

    //* Add Enlaces section
    $wp_customize->add_section(CHILD_THEME_SLUG . '_colors_links', array(
        'title' => __('Links', CHILD_THEME_SLUG),
        'panel' => CHILD_THEME_SLUG . '_colors',
    ));

    //* Link color
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_link_color', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_link_color'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, CHILD_THEME_SLUG . '_link_color', array(
        'label' => __('Text color', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_colors_links',
        'settings' => CHILD_THEME_SLUG . '_link_color',
    )));

    //* Border width
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_button_border_width', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_border_width'],
    ));

    $wp_customize->add_control(new WP_Customize_Range($wp_customize, CHILD_THEME_SLUG . '_button_border_width', array(
        'label' => __('Border width', CHILD_THEME_SLUG) . ' (%)',
        'min' => 0,
        'max' => 10,
        'step' => 1,
        'section' => CHILD_THEME_SLUG . '_colors_buttons',
    )));
}

function asitheme_customizer_panel_header($wp_customize) {

    //* Add header setup panel
    $wp_customize->add_section(CHILD_THEME_SLUG . '_header', array(
        'title' => __('Header', CHILD_THEME_SLUG),
        'priority' => 12,
    ));

    $wp_customize->add_setting(CHILD_THEME_SLUG . '_header_position', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_position'],
        'type' => 'theme_mod'
    ));

    $wp_customize->add_control(CHILD_THEME_SLUG . '_header_position', array(
        'type' => 'select',
        'section' => CHILD_THEME_SLUG . '_header',
        'label' => __('Header position', CHILD_THEME_SLUG),
        'choices' => array(
            'fixed' => __('Header fixed', CHILD_THEME_SLUG),
            'relative' => __('Header relative', CHILD_THEME_SLUG)
        ),
    ));

    $wp_customize->add_setting(CHILD_THEME_SLUG . '_header_transparency', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_transparency'],
    ));

    $wp_customize->add_control(new WP_Customize_Range($wp_customize, CHILD_THEME_SLUG . '_header_transparency', array(
        'label' => __('Background transparency', CHILD_THEME_SLUG) . ' (%)',
        'min' => 0,
        'max' => 100,
        'step' => 1,
        'section' => CHILD_THEME_SLUG . '_header',
    )));

    //* Header search center
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_header_search', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_search'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(CHILD_THEME_SLUG . '_header_search', array(
        'label' => __('Show search engine as in the demo?', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_header',
        'settings' => CHILD_THEME_SLUG . '_header_search',
        'type' => 'checkbox'
    ));
}

function asitheme_customizer_panel_fonts($wp_customize) {

    //* Add font panel
    $wp_customize->add_section(CHILD_THEME_SLUG . '_font', array(
        'title' => __('Fonts', CHILD_THEME_SLUG),
        'priority' => 14,
    ));

    //* Main Google Font Setting
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_font', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Google_Font_Control($wp_customize, CHILD_THEME_SLUG . '_font', array(
        'label' => __('Body', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_font',
        'settings' => CHILD_THEME_SLUG . '_font'
    )));

    //* Headings Google Font Setting
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_font_headings', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font_headings'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(new WP_Customize_Google_Font_Control($wp_customize, CHILD_THEME_SLUG . '_font_headings', array(
        'label' => __('Headings', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_font',
        'settings' => CHILD_THEME_SLUG . '_font_headings',
    )));
}

function asitheme_customizer_panel_social($wp_customize) {

    //* Add Social panel
    $wp_customize->add_section(CHILD_THEME_SLUG . '_social', array(
        'title' => __('Social networks', CHILD_THEME_SLUG),
        'priority' => 14,
    ));

    //* Footer active social
    $wp_customize->add_setting(CHILD_THEME_SLUG . '_social_footer', array(
        'default' => CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_social_footer'],
        'type' => 'theme_mod'
    ));
    $wp_customize->add_control(CHILD_THEME_SLUG . '_social_footer', array(
        'label' => __('Add social networks in the footer?', CHILD_THEME_SLUG),
        'section' => CHILD_THEME_SLUG . '_social',
        'settings' => CHILD_THEME_SLUG . '_social_footer',
        'type' => 'checkbox'
    ));

    //* Social networks Setting
    foreach (CHILD_THEME_DEFAULTS_NETWORKS as $key => $value) {

        $wp_customize->add_setting(CHILD_THEME_SLUG . '_social_' . $value, array(
            'default' => '',
            'type' => 'theme_mod'
        ));
        $wp_customize->add_control(CHILD_THEME_SLUG . '_social_' . $value, array(
            'label' => $key . ' ' . __('link', CHILD_THEME_SLUG),
            'section' => CHILD_THEME_SLUG . '_social',
            'settings' => CHILD_THEME_SLUG . '_social_' . $value,
            'type' => 'text'
        ));
    }
}