<?php

add_filter('genesis_theme_support_menus', 'asitheme_genesis_theme_support_menus');

function asitheme_genesis_theme_support_menus($menus) {
    unset($menus['secondary']);
    return $menus;
}