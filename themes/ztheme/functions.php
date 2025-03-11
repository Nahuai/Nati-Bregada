<?php

//* Genesis Pre Init
require_once(get_stylesheet_directory() . '/inc/genesis/genesis-pre-init.php');

//* Init Genesis
include_once(get_template_directory() . '/lib/init.php');

$theme = wp_get_theme();
define('CHILD_THEME_NAME', $theme->get('Name'));
define('CHILD_THEME_VERSION', $theme->get('Version'));
define('CHILD_THEME_THEMEURI', $theme->get('ThemeURI'));
define('CHILD_THEME_AUTHOR', $theme->get('Author'));
define('CHILD_THEME_AUTHORURI', $theme->get('AuthorURI'));

define('CHILD_THEME_SLUG', 'ztheme');

//* Defaults
require_once(CHILD_DIR . '/inc/defaults.php');

//* Add Theme Customizer settings
require_once(CHILD_DIR . '/inc/customizer/customizer.php');

//* Genesis
require_once(CHILD_DIR . '/inc/genesis/genesis.php');

//* Add ASI custom widgets
require_once(CHILD_DIR . '/inc/widgets/widgets.php');

//* Setup Wizard
require_once(CHILD_DIR . '/inc/setup-wizard/setup-wizard.php');

//* Gutenberg
require_once(CHILD_DIR . '/inc/gutenberg/gutenberg.php');

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (is_plugin_active('woocommerce/woocommerce.php')) {
    require_once(CHILD_DIR . '/inc/woocommerce/woocommerce.php');
}
if (is_plugin_active('gravityforms/gravityforms.php')) {
    require_once(CHILD_DIR . '/inc/gravityforms/gravityforms.php');
}
if (is_plugin_active('gravityforms/gravityforms.php') || is_plugin_active('ninja-forms/ninja-forms.php') || is_plugin_active('wysija-newsletters/index.php') || is_plugin_active('mailpoet/mailpoet.php')) {

    add_action('wp_enqueue_scripts', 'asitheme_forms_wp_enqueue_scripts');

    function asitheme_forms_wp_enqueue_scripts() {
        wp_enqueue_style('asitheme-forms', CHILD_URL . '/assets/css/forms.css', array(), CHILD_THEME_VERSION);
    }
}

//* Add HTML5 markup structure
add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery'));

add_action('after_setup_theme', 'asitheme_after_setup_theme');

function asitheme_after_setup_theme() {

    load_theme_textdomain(CHILD_THEME_SLUG, CHILD_DIR . '/languages');

    //* Updater theme
    require(CHILD_DIR . '/inc/updater/updater.php');
}

//* Enqueue Scripts & Styles
add_action('wp_enqueue_scripts', 'asitheme_wp_enqueue_scripts');

function asitheme_wp_enqueue_scripts() {

    $content_font = get_theme_mod(CHILD_THEME_SLUG . '_font', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font']);
    wp_enqueue_style('google-fonts', add_query_arg(array('family' => $content_font), 'https://fonts.googleapis.com/css'), array(), CHILD_THEME_VERSION);
    $content_font_headings = get_theme_mod(CHILD_THEME_SLUG . '_font_headings', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font_headings']);
    wp_enqueue_style('google-fonts-headings', add_query_arg(array('family' => $content_font_headings), 'https://fonts.googleapis.com/css'), array(), CHILD_THEME_VERSION);
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), CHILD_THEME_VERSION);

    wp_enqueue_script('scripts', CHILD_URL . '/assets/js/scripts.js', array('jquery'), CHILD_THEME_VERSION);
}

add_filter('body_class', 'asitheme_body_class');

function asitheme_body_class($classes) {

    global $is_chrome, $is_safari;

    if ($is_chrome) {
        $classes[] = 'chrome';
    }

    if ($is_safari) {
        $classes[] = 'safari';
    }

    $s = get_theme_mod(CHILD_THEME_SLUG . '_font', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font']);
    $classes[] = 'google-font-general-' . sanitize_title(substr($s, 0, strpos($s, ':') ? strpos($s, ':') : strlen($s)));

    $s = get_theme_mod(CHILD_THEME_SLUG . '_font_headings', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font_headings']);
    $classes[] = 'google-font-headings-' . sanitize_title(substr($s, 0, strpos($s, ':') ? strpos($s, ':') : strlen($s)));

    return $classes;
}

add_filter('comment_class', 'asitheme_comment_class');

function asitheme_comment_class($classes) {
    if (get_option('show_avatars')) {
        $classes[] = 'has-avatar';
    }
    return $classes;
}

add_filter('nav_menu_link_attributes', 'asitheme_nav_menu_link_attributes', 10, 4);

function asitheme_nav_menu_link_attributes($atts, $item, $args, $depth) {
    $atts['data-title'] = strip_tags($item->title);
    return $atts;
}

add_action('wp_head', 'asitheme_wp_head');

function asitheme_wp_head() {
    $boton_color = get_theme_mod(CHILD_THEME_SLUG . '_button_color', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_color']);
    $boton_background = get_theme_mod(CHILD_THEME_SLUG . '_button_background', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_background']);
    $boton_border = get_theme_mod(CHILD_THEME_SLUG . '_button_border_color', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_border_color']);
    $boton_color_hover = get_theme_mod(CHILD_THEME_SLUG . '_button_color_hover', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_color_hover']);
    $boton_background_hover = get_theme_mod(CHILD_THEME_SLUG . '_button_background_hover', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_background_hover']);
    $boton_boder_hover = get_theme_mod(CHILD_THEME_SLUG . '_button_border_color_hover', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_border_color_hover']);
    $link_color = get_theme_mod(CHILD_THEME_SLUG . '_link_color', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_link_color']);
    $button_border_width = get_theme_mod(CHILD_THEME_SLUG . '_button_border_width', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_button_border_width']);

    $font_customize = get_theme_mod(CHILD_THEME_SLUG . '_font', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font']);
    $font = str_replace('+', ' ', explode(':', $font_customize));
    $font_customize_headings = get_theme_mod(CHILD_THEME_SLUG . '_font_headings', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font_headings']);
    $font_headings = str_replace('+', ' ', explode(':', $font_customize_headings));

    $header_transparency = get_theme_mod(CHILD_THEME_SLUG . '_header_transparency', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_transparency']);
    $header_transparency = (int)$header_transparency / 100;

    $boton_background = !$boton_background ? 'transparent' : $boton_background;
    $boton_color = !$boton_color ? '#000' : $boton_color;
    $boton_border = !$boton_border ? 'transparent' : $boton_border;
    $boton_background_hover = !$boton_background_hover ? 'transparent' : $boton_background_hover;
    $boton_color_hover = !$boton_color_hover ? '#000' : $boton_color_hover;
    $boton_boder_hover = !$boton_boder_hover ? 'transparent' : $boton_boder_hover;
    ?>
    <style type="text/css">
        body{
            font-family: "<?php echo $font[0]; ?>", sans-serif;
        }
        h1, h2, h3, h4, h5, h6,
        .site-header .site-title{
            font-family: "<?php echo $font_headings[0]; ?>", sans-serif;
        }
        .widget.asitheme-widget .asitheme-widget-features .items .item h3,
        .widget.asitheme-widget .asitheme-widget-logos .title,
        .widget.asitheme-widget .asitheme-widget-features .title,
        #asi-front-page .widget.woocommerce.widget_products .widget-wrap .widget-title,
        #asi-front-page .widget.woocommerce.widget_products .widget-wrap h4,
        .woocommerce ul.products li.product .woocommerce-loop-product__title,
        .woocommerce div.product .product_title{
            font-family: "<?php echo $font[0]; ?>", sans-serif;
        }
        .widget.asitheme-widget .asitheme-widget-quotes .quote cite,
        .widget.asitheme-widget .asitheme-widget-quotes .quote cite:before,
        .widget.asitheme-widget .asitheme-widget-quotes .quote cite:after{
            font-family: "<?php echo $font_headings[0]; ?>", sans-serif;
        }
        .button,
        button,
        input[type="button"],
        input[type="reset"],
        input[type="submit"],
        .comment-reply-link,
        .button.negative:hover,
        .button.negative:focus,
        .button.negative:active{
            background-color: <?php echo $boton_background; ?>;
            color: <?php echo $boton_color; ?>;
            border-color: <?php echo $boton_border; ?>;
            border-width: <?php echo $button_border_width; ?>px;
            border-style: solid;
        }
        button:hover,
        .button:hover,
        input:hover[type="button"],
        input:hover[type="reset"],
        input:hover[type="submit"],
        .comment-reply-link:hover,
        button:focus,
        .button:focus,
        input:focus[type="button"],
        input:focus[type="reset"],
        input:focus[type="submit"],
        .comment-reply-link:focus,
        button:active,
        .button:active,
        input:active[type="button"],
        input:active[type="reset"],
        input:active[type="submit"],
        .comment-reply-link:active,
        .button.negative{
            background-color: <?php echo $boton_background_hover; ?>;
            color: <?php echo $boton_color_hover; ?>;
            border-color: <?php echo $boton_boder_hover; ?>;
        }
        <?php if($link_color): ?>
        a,
        .entry-title a:hover,
        .entry-title a:focus,
        .nav-primary .genesis-nav-menu .sub-menu a:hover,
        .nav-primary .genesis-nav-menu .sub-menu a:focus,
        .nav-primary .genesis-nav-menu .sub-menu .current-menu-item > a{
            color: <?php echo $link_color; ?>;
        }
        <?php endif; ?>
        .site-header.fixed{
            background-color: rgba(255, 255, 255,<?php echo $header_transparency; ?>);
        }
    </style>
    <?php
}

function asitheme_nl2p($string) {
    /* Explode based on new-line */
    $string_parts = explode("\n", $string);
    /* Wrap each block in a p tag */
    $string = '<p>' . implode('</p><p>', $string_parts) . '</p>';
    /* Return the string with empty paragraphs removed */
    return str_replace("<p></p>", '', $string);
}

// Display Featured Image on top of the post
add_action( 'genesis_before_entry_content', 'featured_post_image', 8 );
function featured_post_image() {
  if ( ! is_singular( 'post' ) )  return;
	the_post_thumbnail('post-image');
}

// Add CTA after entry content
add_action( 'genesis_after_entry_content', 'bs_add_block_after_content');
function bs_add_block_after_content($content) {
	if ( is_single()) {
	$content = get_post_field( 'post_content', 20065); 
		if ( ! $content ) {
			return;
		}
	}		
	echo $content;
}


/* Añadir automáticamente un bloque de TOC al inicio de las entradas 
add_action('genesis_entry_content', 'cg_add_block_before_content', 8);
function cg_add_block_before_content() {
    if (is_single()) {
        // Create the TOC block dynamically
        $toc_block = '<!-- wp:simpletoc/toc {"title_text":"Índex"} /-->';
        
        // Get the current post content
        $post_content = get_post_field('post_content', get_the_ID());
        
        // Store the current post content
        global $post;
        $original_content = $post->post_content;
        $post->post_content = $post_content;
        
        // Render the TOC
        echo do_blocks($toc_block);
        
        // Restore the original content
        $post->post_content = $original_content;
    }
}*/

// Añadir patrones sincronizados al menú de WordPress admin
add_action( 'admin_menu', 'cg_sync_patterns_admin_menu' );
function cg_sync_patterns_admin_menu() {
    add_menu_page( 'Patrones sincronizados', 'Patrones <br>sincronizados', 'edit_posts', 'edit.php?post_type=wp_block', '', 'dashicons-controls-repeat', 33 );
}

// Cambiar la URL del autor
add_filter( 'author_link', 'admin_author_link', 10, 3);
function admin_author_link($link, $author_id, $author_nicename) {
    if( 2 === $author_id ) {
        $link = 'https://natibergada.cat/autor/nati/';
    }
    return $link;
}

add_theme_support('appearance-tools');
