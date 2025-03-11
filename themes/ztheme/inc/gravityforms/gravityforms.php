<?php

add_filter('gform_ajax_spinner_url', 'asitheme_gform_ajax_spinner_url');

function asitheme_gform_ajax_spinner_url($url) {
    $url = CHILD_URL . '/assets/images/spinner-dark.svg';
    return $url;
}
