<?php

//* Setup theme
add_action('after_setup_theme', 'asitheme_after_setup_theme_gutenberg');

function asitheme_after_setup_theme_gutenberg() {
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
}

//* Enqueue Scripts & Styles
add_action('wp_enqueue_scripts', 'asitheme_wp_enqueue_scripts_gutenberg');

function asitheme_wp_enqueue_scripts_gutenberg() {
    wp_enqueue_style('asitheme-gutenberg', CHILD_URL . '/assets/css/gutenberg.css', array(), CHILD_THEME_VERSION);
}