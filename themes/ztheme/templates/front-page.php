<?php
/**
 * Template Name: Front Page
 */
if (!defined('ABSPATH'))
    exit;

add_action('genesis_meta', 'asitheme_homepage_front_page_genesis_meta');

/**
 * Add widget support for homepage. If no widgets active, display the default loop.
 *
 */
function asitheme_homepage_front_page_genesis_meta() {

    if (is_active_sidebar('asi-front-page')) {

        // Add front-page body class.
        add_filter('body_class', 'asitheme_homepage_body_class');

        // Force full width content layout.
        add_filter('genesis_site_layout', '__genesis_return_full_width_content');

        // Remove breadcrumbs.
        remove_action('genesis_before_loop', 'genesis_do_breadcrumbs');

        // Remove the default Genesis loop.
        remove_action('genesis_loop', 'genesis_do_loop');

        // Add homepage widgets.
        add_action('genesis_loop', 'asitheme_homepage_front_page_widgets');
    }
}

// Define front-page body class.
function asitheme_homepage_body_class($classes) {
    $classes[] = 'asi-front-page';
    return $classes;
}

// Define featured-section body class.
function asitheme_homepage_featured_body_class($classes) {
    $classes[] = 'featured-section';
    return $classes;
}

// Add markup for front page widgets.
function asitheme_homepage_front_page_widgets() {

    genesis_widget_area('asi-front-page', array(
        'before' => '<div id="asi-front-page" class="asi-front-page" tabindex="-1"><div class="flexible-widgets widget-area"><div class="wrap">',
        'after' => '</div></div></div>',
    ));
}

// Run the Genesis loop.
genesis();
