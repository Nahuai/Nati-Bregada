<?php

class Updater_Admin {

    protected $remote_api_url = null;
    protected $theme_slug = null;
    protected $version = null;
    protected $author = null;
    protected $download_id = null;
    protected $renew_url = null;
    protected $strings = null;

    function __construct($config = array(), $strings = array()) {

        $config = wp_parse_args($config, array(
            'remote_api_url' => CHILD_THEME_AUTHORURI,
            'theme_slug' => get_template(),
            'item_name' => '',
            'license' => '',
            'version' => '',
            'author' => '',
            'download_id' => '',
            'renew_url' => '',
            'beta' => false,
        ));

        $this->remote_api_url = $config['remote_api_url'];
        $this->item_name = $config['item_name'];
        $this->theme_slug = sanitize_key($config['theme_slug']);
        $this->version = $config['version'];
        $this->author = $config['author'];
        $this->download_id = $config['download_id'];
        $this->renew_url = $config['renew_url'];
        $this->beta = $config['beta'];

        if ('' == $config['version']) {
            $theme = wp_get_theme($this->theme_slug);
            $this->version = $theme->get('Version');
        }

        $this->strings = $strings;

        add_action('init', array($this, 'updater'));
        add_action('admin_init', array($this, 'register_option'));
        add_action('admin_init', array($this, 'license_action'));
        add_action('admin_menu', array($this, 'license_menu'));
        add_filter('http_request_args', array($this, 'disable_wporg_request'), 5, 2);
    }

    function updater() {

        if (!current_user_can('manage_options')) {
            return;
        }

        if (get_option($this->theme_slug . '_license_key_status', false) != 'valid') {
            return;
        }

        if (!class_exists('Updater')) {
            include(dirname(__FILE__) . '/updater-class.php');
        }

        new Updater(array(
            'remote_api_url' => $this->remote_api_url,
            'version' => $this->version,
            'license' => trim(get_option($this->theme_slug . '_license_key')),
            'item_name' => $this->item_name,
            'author' => $this->author,
            'beta' => $this->beta), $this->strings);
    }

    function license_menu() {
        $strings = $this->strings;
        add_dashboard_page($strings['theme-license'], $strings['theme-license'], 'manage_options', $this->theme_slug . '-license', array($this, 'license_page'));
    }

    function license_page() {

        $strings = $this->strings;

        $license = trim(get_option($this->theme_slug . '_license_key'));
        $status = get_option($this->theme_slug . '_license_key_status', false);

        if (!$license) {
            $message = $strings['enter-key'];
        } else {
            $message = $this->check_license();
        }

        $input_disabled = '';
        $type = 'text';
        if ($license && 'valid' == $status) {
            $input_disabled = 'readonly="readonly"';
            $type = 'password';
        }
        ?>

        <div class="wrap">

            <h1><?php echo $strings['theme-license'] ?></h1>

            <form method="post" action="">

                <?php settings_fields($this->theme_slug . '-license'); ?>

                <div class="asi-box" id="asi-license-information">
                    <div class="title">
                        <h3><?php esc_html_e($strings['license-information']); ?></h3>
                    </div>
                    <div class="inner">
                        <table class="form-table">
                            <tbody>

                                <tr valign="top">
                                    <th scope="row" valign="top">
                                        <?php echo $strings['license-key']; ?>
                                    </th>
                                    <td>
                                        <input id="<?php echo $this->theme_slug; ?>_license_key" name="<?php echo $this->theme_slug; ?>_license_key" type="<?php echo esc_attr($type) ?>" class="regular-text" value="<?php echo esc_attr(str_repeat('*', strlen($license))); ?>" <?php echo $input_disabled ?>>
                                        <p class="description">
                                            <?php echo $message; ?>
                                        </p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row" valign="top"></th>
                                    <td>
                                        <?php
                                        wp_nonce_field($this->theme_slug . '_nonce', $this->theme_slug . '_nonce');
                                        if ($license && 'valid' == $status) {
                                            ?>
                                            <input type="submit" class="button-primary" name="<?php echo $this->theme_slug; ?>_license_deactivate" value="<?php esc_attr_e($strings['deactivate-license']); ?>">
                                        <?php } else { ?>
                                            <input type="submit" class="button-primary" name="<?php echo $this->theme_slug; ?>_license_activate" value="<?php esc_attr_e($strings['activate-license']); ?>">
                                        <?php }
                                        ?>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>

                <style type="text/css">
                    .asi-box{
                        background: #fff;
                        border: 1px solid #e5e5e5;
                        position: relative;
                        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
                        margin: 20px 0;
                    }
                    .asi-box .title{
                        border-bottom: 1px solid #EEEEEE;
                        margin: 0;
                        padding: 15px;
                        background: #FFFFFF;
                    }
                    .asi-box .title h3{
                        font-size: 14px;
                        line-height: 1em;
                        margin: 0;
                        padding: 0;
                    }
                    .asi-box .inner{
                        padding: 15px;
                    }
                    .asi-box .inner table{
                        margin: 0;
                    }
                    .asi-box p{
                        margin-top: 0.5em;
                    }
                    .asi-box p.description{
                        font-size: 15px;
                    }
                    .asi-box .regular-text{
                        width: 50%;
                        padding: 10px 15px;
                        font-size: 17px;
                    }
                    .asi-box .regular-text[readonly]{
                        background-color: #eee;
                    }
                </style>
            </form>
        </div>
        <?php
    }

    function register_option() {
        register_setting($this->theme_slug . '-license', $this->theme_slug . '_license_key', array($this, 'sanitize_license'));
    }

    function sanitize_license($new) {

        $old = get_option($this->theme_slug . '_license_key');

        if ($old && $old != $new) {
            delete_option($this->theme_slug . '_license_key_status');
            delete_transient($this->theme_slug . '_license_message');
        }

        return $new;
    }

    function get_api_response($api_params) {

        $response = wp_remote_post($this->remote_api_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (is_wp_error($response)) {
            wp_die($response->get_error_message(), $this->strings['error_message'] . ': ' . $response->get_error_code());
        }

        return $response;
    }

    function activate_license() {

        $license = trim(get_option($this->theme_slug . '_license_key'));

        $api_params = array(
            'edd_action' => 'activate_license',
            'license' => $license,
            'item_name' => urlencode($this->item_name),
            'url' => home_url()
        );

        $response = $this->get_api_response($api_params);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

            if (is_wp_error($response)) {
                $message = $response->get_error_message();
            } else {
                $message = $this->strings['error_try_again'];
            }
        } else {

            $license_data = json_decode(wp_remote_retrieve_body($response));

            if (false === $license_data->success) {

                switch ($license_data->error) {

                    case 'expired' :
                        $message = sprintf($this->strings['error_license_expired'], date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp'))));
                        break;
                    case 'revoked' :
                        $message = $this->strings['error_license_revoked'];
                        break;
                    case 'missing' :
                        $message = $this->strings['error_license_missing'];
                        break;
                    case 'invalid' :
                    case 'site_inactive' :
                        $message = $this->strings['error_license_invalid'];
                        break;
                    case 'item_name_mismatch' :
                        $message = sprintf($this->strings['error_license_mismatch'], $license_data->item_name);
                        break;
                    case 'no_activations_left':
                        $message = $this->strings['error_license_no_activations_left'];
                        break;
                    default :
                        $message = $this->strings['error_try_again'];
                        break;
                }

                if ($license_data->license !== 'valid') {
                    delete_option($this->theme_slug . '_license_key');
                }

                if (!empty($message)) {
                    $base_url = admin_url('index.php?page=' . $this->theme_slug . '-license');
                    $redirect = add_query_arg(array('sl_theme_activation' => 'false', 'message' => urlencode($message)), $base_url);

                    wp_redirect($redirect);
                    exit();
                }
            }
        }

        if ($license_data && isset($license_data->license)) {
            update_option($this->theme_slug . '_license_key_status', $license_data->license);
            delete_transient($this->theme_slug . '_license_message');
        }

        wp_redirect(admin_url('index.php?page=' . $this->theme_slug . '-license'));
        exit();
    }

    function deactivate_license() {

        $license = trim(get_option($this->theme_slug . '_license_key'));

        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license' => $license,
            'item_name' => urlencode($this->item_name),
            'url' => home_url()
        );

        $response = $this->get_api_response($api_params);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

            if (is_wp_error($response)) {
                $message = $response->get_error_message();
            } else {
                $message = $this->strings['error_try_again'];
            }
        } else {

            $license_data = json_decode(wp_remote_retrieve_body($response));

            if ($license_data && ($license_data->license == 'deactivated')) {
                delete_option($this->theme_slug . '_license_key');
                delete_option($this->theme_slug . '_license_key_status');
                delete_transient($this->theme_slug . '_license_message');
            }
        }

        if (!empty($message)) {
            $base_url = admin_url('index.php?page=' . $this->theme_slug . '-license');
            $redirect = add_query_arg(array('sl_theme_activation' => 'false', 'message' => urlencode($message)), $base_url);

            wp_redirect($redirect);
            exit();
        }

        wp_redirect(admin_url('index.php?page=' . $this->theme_slug . '-license'));
        exit();
    }

    function get_renewal_link() {

        if ('' != $this->renew_url) {
            return $this->renew_url;
        }

        $license_key = trim(get_option($this->theme_slug . '_license_key', false));
        if ('' != $this->download_id && $license_key) {
            $url = esc_url($this->remote_api_url);
            $url .= '/checkout/?edd_license_key=' . $license_key . '&download_id=' . $this->download_id;
            return $url;
        }

        return $this->remote_api_url;
    }

    function license_action() {

        if (isset($_POST[$this->theme_slug . '_license_activate'])) {
            if (check_admin_referer($this->theme_slug . '_nonce', $this->theme_slug . '_nonce')) {
                if (isset($_POST[$this->theme_slug . '_license_key'])) {
                    update_option($this->theme_slug . '_license_key', trim($_POST[$this->theme_slug . '_license_key']));
                }
                $this->activate_license();
            }
        }

        if (isset($_POST[$this->theme_slug . '_license_deactivate'])) {
            if (check_admin_referer($this->theme_slug . '_nonce', $this->theme_slug . '_nonce')) {
                $this->deactivate_license();
            }
        }
    }

    function check_license() {

        $license = trim(get_option($this->theme_slug . '_license_key'));
        $strings = $this->strings;

        $api_params = array(
            'edd_action' => 'check_license',
            'license' => $license,
            'item_name' => urlencode($this->item_name),
            'url' => home_url()
        );

        $response = $this->get_api_response($api_params);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

            if (is_wp_error($response)) {
                $message = $response->get_error_message();
            } else {
                $message = $strings['license-status-unknown'];
            }
        } else {

            $license_data = json_decode(wp_remote_retrieve_body($response));

            if (!isset($license_data->license)) {
                $message = $strings['license-status-unknown'];
                return $message;
            }

            if ($license_data && isset($license_data->license)) {
                update_option($this->theme_slug . '_license_key_status', $license_data->license);
            }

            $expires = false;
            if (isset($license_data->expires) && 'lifetime' != $license_data->expires) {
                $expires = date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')));
                $renew_link = '<a href="' . esc_url($this->get_renewal_link()) . '" target="_blank">' . $strings['renew'] . '</a>';
            } elseif (isset($license_data->expires) && 'lifetime' == $license_data->expires) {
                $expires = 'lifetime';
            }

            $site_count = $license_data->site_count;
            $license_limit = $license_data->license_limit;

            if (0 == $license_limit) {
                $license_limit = $strings['unlimited'];
            }

            if ($license_data->license == 'valid') {
                $message = $strings['license-key-is-active'] . ' ';
                if (isset($expires) && 'lifetime' != $expires) {
                    $message .= sprintf($strings['expires%s'], $expires) . ' ';
                }
                if (isset($expires) && 'lifetime' == $expires) {
                    $message .= $strings['expires-never'];
                }
                if ($site_count && $license_limit) {
                    $message .= sprintf($strings['%1$s/%2$-sites'], $site_count, $license_limit);
                }
            } else if ($license_data->license == 'expired') {
                if ($expires) {
                    $message = sprintf($strings['license-key-expired-%s'], $expires);
                } else {
                    $message = $strings['license-key-expired'];
                }
                if ($renew_link) {
                    $message .= ' ' . $renew_link;
                }
            } else if ($license_data->license == 'invalid') {
                $message = $strings['license-keys-do-not-match'];
            } else if ($license_data->license == 'inactive') {
                $message = $strings['license-is-inactive'];
            } else if ($license_data->license == 'disabled') {
                $message = $strings['license-key-is-disabled'];
            } else if ($license_data->license == 'site_inactive') {
                // Site is inactive
                $message = $strings['site-is-inactive'];
            } else {
                $message = $strings['license-status-unknown'];
            }
        }

        return $message;
    }

    function disable_wporg_request($r, $url) {

        if (0 !== strpos($url, 'https://api.wordpress.org/themes/update-check/1.1/')) {
            return $r;
        }

        $themes = json_decode($r['body']['themes']);

        $parent = get_option('template');
        $child = get_option('stylesheet');
        unset($themes->themes->$parent);
        unset($themes->themes->$child);

        $r['body']['themes'] = json_encode($themes);

        return $r;
    }

}

function asithemes_admin_notices() {

    if (isset($_GET['sl_theme_activation']) && !empty($_GET['message'])) {

        switch ($_GET['sl_theme_activation']) {

            case 'false':
                $message = urldecode($_GET['message']);
                ?>
                <div class="error">
                    <p><?php echo $message; ?></p>
                </div>
                <?php
                break;

            case 'true':
            default:
                break;
        }
    }
}

add_action('admin_notices', 'asithemes_admin_notices');
