<?php

//if (!class_exists('Updater_Admin')) {
//    include(dirname(__FILE__) . '/updater-admin.php');
//}
//
//$theme_info = wp_get_theme();
//
//$config = array(
//    'remote_api_url' => $theme_info->get('AuthorURI'),
//    'item_name' => $theme_info->get('Name'),
//    'theme_slug' => CHILD_THEME_SLUG,
//    'version' => $theme_info->get('Version'),
//    'author' => $theme_info->get('Author'),
//    'download_id' => '',
//    'renew_url' => '',
//    'beta' => false,
//);
//
//$strings = array(
//    'error_message' => __('Error', CHILD_THEME_SLUG),
//    'error_try_again' => __('An error occurred, please try again.', CHILD_THEME_SLUG),
//    'error_license_expired' => __('Your license key expired on %s.', CHILD_THEME_SLUG),
//    'error_license_revoked' => __('Your license key has been disabled.', CHILD_THEME_SLUG),
//    'error_license_missing' => __('Invalid license.', CHILD_THEME_SLUG),
//    'error_license_invalid' => __('Your license is not active for this URL.', CHILD_THEME_SLUG),
//    'error_license_mismatch' => __('This appears to be an invalid license key for %s.', CHILD_THEME_SLUG),
//    'error_license_no_activations_left' => __('Your license key has reached its activation limit.', CHILD_THEME_SLUG),
//    'theme-license' => __('Así Themes License', CHILD_THEME_SLUG),
//    'enter-key' => __('Enter your license key.', CHILD_THEME_SLUG),
//    'license-key' => __('License Key', CHILD_THEME_SLUG),
//    'license-action' => __('License Action', CHILD_THEME_SLUG),
//    'license-information' => __('License information', CHILD_THEME_SLUG),
//    'deactivate-license' => __('Deactivate License', CHILD_THEME_SLUG),
//    'activate-license' => __('Activate License', CHILD_THEME_SLUG),
//    'license-active-url-%s' => __('Active license for "%s"', CHILD_THEME_SLUG),
//    'status-unknown' => __('License status is unknown.', CHILD_THEME_SLUG),
//    'renew' => __('Renew?', CHILD_THEME_SLUG),
//    'unlimited' => __('unlimited', CHILD_THEME_SLUG),
//    'license-key-is-active' => __('License key is active.', CHILD_THEME_SLUG),
//    'expires%s' => __('Expires %s.', CHILD_THEME_SLUG),
//    'expires-never' => __('Lifetime License.', CHILD_THEME_SLUG),
//    '%1$s/%2$-sites' => __('You have %1$s / %2$s sites activated.', CHILD_THEME_SLUG),
//    'license-key-expired-%s' => __('License key expired %s.', CHILD_THEME_SLUG),
//    'license-key-expired' => __('License key has expired.', CHILD_THEME_SLUG),
//    'license-keys-do-not-match' => __('License keys do not match.', CHILD_THEME_SLUG),
//    'license-is-inactive' => __('License is inactive.', CHILD_THEME_SLUG),
//    'license-key-is-disabled' => __('License key is disabled.', CHILD_THEME_SLUG),
//    'site-is-inactive' => __('Site is inactive.', CHILD_THEME_SLUG),
//    'license-status-unknown' => __('License status is unknown.', CHILD_THEME_SLUG),
//    'update-notice' => __("Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.", CHILD_THEME_SLUG),
//    'update-available' => __('<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', CHILD_THEME_SLUG),
//);
//
//$updater = new Updater_Admin($config, $strings);

function asitheme_updater_database() {
    $option = CHILD_THEME_SLUG . '_version';
    $db_version = (string)get_option($option);
    $theme_version = (string)CHILD_THEME_VERSION;

    if ($db_version === $theme_version) {
        return;
    }

    update_option($option, $theme_version);
}

asitheme_updater_database();
