<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'asi_setup_wizard_init', 1);

function asi_setup_wizard_init() {

    // Setup/welcome
    if (!empty($_GET['page'])) {
        switch ($_GET['page']) {
            case 'asi-setup' :
                add_filter('woocommerce_enable_setup_wizard', '__return_false');
                include_once(dirname(__FILE__) . '/class-asi-setup-wizard.php');
                break;
        }
    }
}

add_action('admin_init', 'asi_admin_redirects');

function asi_admin_redirects() {

    // Setup wizard redirect
    if (!get_option('asi_setup_wizard_launched_' . CHILD_THEME_SLUG)) {

        update_option('asi_setup_wizard_launched_' . CHILD_THEME_SLUG, 1);

        if ((!empty($_GET['page']) && in_array($_GET['page'], array('asi-setup'))) || is_network_admin() || isset($_GET['activate-multi']) || !current_user_can('manage_options')) {
            return;
        }

        wp_safe_redirect(admin_url('themes.php?page=asi-setup'));
        exit;
    }
}

add_action('admin_menu', 'asi_setup_wizard_menu');

function asi_setup_wizard_menu() {
    add_theme_page(__('Setup wizard', CHILD_THEME_SLUG), __('Setup wizard', CHILD_THEME_SLUG), 'manage_options', 'asi-setup', 'asi_setup_wizard_page');
}

function asi_set_time_limit($limit = 0) {
    if (function_exists('set_time_limit') && false === strpos(ini_get('disable_functions'), 'set_time_limit') && !ini_get('safe_mode')) {
        @set_time_limit($limit);
    }
}

if (!function_exists('array_column')) {

    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if (!array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }

}