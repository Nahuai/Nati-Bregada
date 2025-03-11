<?php

$defaults = array(
    CHILD_THEME_SLUG . '_button_color' => '#000',
    CHILD_THEME_SLUG . '_button_background' => 'transparent',
    CHILD_THEME_SLUG . '_button_border_color' => '#000',
    CHILD_THEME_SLUG . '_button_color_hover' => '#fff',
    CHILD_THEME_SLUG . '_button_background_hover' => '#000',
    CHILD_THEME_SLUG . '_button_border_color_hover' => '#000',
    CHILD_THEME_SLUG . '_link_color' => '#000',
    CHILD_THEME_SLUG . '_footer_text' => '[footer_copyright]',
    CHILD_THEME_SLUG . '_footer_menu' => '',
    CHILD_THEME_SLUG . '_header_position' => 'relative',
    CHILD_THEME_SLUG . '_header_transparency' => 100,
    CHILD_THEME_SLUG . '_font' => 'Montserrat:300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i',
    CHILD_THEME_SLUG . '_font_headings' => 'Abril+Fatface',
    CHILD_THEME_SLUG . '_social_footer' => false,
    CHILD_THEME_SLUG . '_button_border_width' => 1,
    CHILD_THEME_SLUG . '_header_search' => true,
    CHILD_THEME_SLUG . '_woocommerce_buy_button_text' => __('Buy', CHILD_THEME_SLUG),
);

define('CHILD_THEME_DEFAULTS', $defaults);

$defaults_networks = array(
    'Facebook' => 'facebook',
    'Twitter' => 'twitter',
    'Instagram' => 'instagram',
    'Pinterest' => 'pinterest',
    'Google plus' => 'google_plus',
    'Flickr' => 'flickr',
    'Linkedin' => 'linkedin',
    'Skype' => 'skype',
    'TripAdvisor' => 'tripadvisor',
    'Tumblr' => 'tumblr',
    'Vimeo' => 'vimeo',
    'YouTube' => 'youtube',
    'Spotify' => 'spotify'
);

define('CHILD_THEME_DEFAULTS_NETWORKS', $defaults_networks);