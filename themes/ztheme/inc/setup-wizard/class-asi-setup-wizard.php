<?php
if (!defined('ABSPATH')) {
    exit;
}

class ASI_Setup_Wizard {

    private $step = '';
    private $steps = array();

    public function __construct() {
        if (current_user_can('manage_options')) {
            add_action('admin_init', array($this, 'setup_wizard'));
        }
    }

    public function setup_wizard() {
        if (empty($_GET['page']) || 'asi-setup' !== $_GET['page']) {
            return;
        }
        $default_steps = array(
            'install_plugins' => array(
                'name' => __('Install plugins', CHILD_THEME_SLUG),
                'view' => array($this, 'asi_setup_install_plugins'),
                'handler' => array($this, 'asi_setup_install_plugins_save'),
            ),
            'content' => array(
                'name' => __('Content', CHILD_THEME_SLUG),
                'view' => array($this, 'asi_setup_content'),
                'handler' => array($this, 'asi_setup_content_save'),
            ),
            'header' => array(
                'name' => __('Header', CHILD_THEME_SLUG),
                'view' => array($this, 'asi_setup_header'),
                'handler' => array($this, 'asi_setup_header_save'),
            ),
            'import' => array(
                'name' => __('Import', CHILD_THEME_SLUG),
                'view' => array($this, 'asi_setup_import'),
                'handler' => array($this, 'asi_setup_import_save'),
            ),
            'next_steps' => array(
                'name' => __('Ready!', CHILD_THEME_SLUG),
                'view' => array($this, 'asi_setup_ready'),
                'handler' => '',
            ),
        );

        $this->steps = $default_steps;
        $this->step = isset($_GET['step']) ? sanitize_key($_GET['step']) : current(array_keys($this->steps));

        wp_register_script('jquery-blockui', CHILD_URL . '/assets/js/jquery.blockUI.min.js', array('jquery'), '2.70');
        wp_register_script('jquery-repeater', CHILD_URL . '/assets/js/jquery.repeater.min.js', array('jquery'), '1.2.1');
        wp_register_script('setup-wizard', CHILD_URL . '/assets/js/setup-wizard.js', array('jquery', 'jquery-blockui', 'jquery-repeater', 'wp-util'), CHILD_THEME_VERSION);

        wp_enqueue_style('setup-wizard', CHILD_URL . '/assets/css/setup-wizard.css', array('dashicons', 'install'), CHILD_THEME_VERSION);

        asi_set_time_limit(0);

        if (!empty($_POST['save_step']) && isset($this->steps[$this->step]['handler'])) {
            call_user_func($this->steps[$this->step]['handler'], $this);
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }

    public function get_next_step_link($step = '') {
        if (!$step) {
            $step = $this->step;
        }

        $keys = array_keys($this->steps);
        if (end($keys) === $step) {
            return admin_url();
        }

        $step_index = array_search($step, $keys);
        if (false === $step_index) {
            return '';
        }

        return add_query_arg('step', $keys[$step_index + 1]);
    }

public function setup_wizard_header() {

    set_current_screen();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width"/>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title><?php esc_html_e('Setup wizard', CHILD_THEME_SLUG); ?></title>
            <?php wp_print_scripts('setup-wizard'); ?>
            <?php do_action('admin_print_styles'); ?>
            <?php do_action('admin_head'); ?>
            <style>
                .asi-setup-steps li{
                    width: <?php echo number_format(100 / count($this->steps), 2, '.', '') ?>%;
                }
            </style>
        </head>
        <body class="asi-setup wp-core-ui">
            <h1 id="asi-logo"><?php echo CHILD_THEME_NAME ?></h1>
            <?php
            }

            public function setup_wizard_footer() {
            ?>
            <a class="asi-return-to-dashboard" href="<?php echo esc_url(admin_url()); ?>"><?php echo esc_html(__('Return to your dashboard', CHILD_THEME_SLUG)); ?></a>
        </body>
    </html>
    <?php
}

    public function setup_wizard_steps() {
        $output_steps = $this->steps;
        ?>
        <ol class="asi-setup-steps">
            <?php foreach ($output_steps as $step_key => $step) : ?>
                <li class="<?php
                if ($step_key === $this->step) {
                    echo 'active';
                } elseif (array_search($this->step, array_keys($this->steps)) > array_search($step_key, array_keys($this->steps))) {
                    echo 'done';
                }
                ?>"><?php echo esc_html($step['name']); ?></li>
            <?php endforeach; ?>
        </ol>
        <?php
    }

    public function setup_wizard_content() {
        echo '<div class="asi-setup-content">';
        call_user_func($this->steps[$this->step]['view'], $this);
        echo '</div>';
    }

    public function asi_setup_install_plugins() {

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
        require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugins = array(
            'woocommerce',
            'ninja-forms'
        );
        ?>
        <form method="post" class="install-plugins-step">
            <?php wp_nonce_field('asi-setup'); ?>

            <h1><?php echo esc_html($this->steps[$this->step]['name']); ?></h1>
            <p class="title"><?php echo esc_html(__('Check the plugins you want to install and activate', CHILD_THEME_SLUG)); ?></p>

            <div class="inside">
                <?php
                foreach ($plugins as $plugin_slug) {

                    $plugin_information = plugins_api('plugin_information', array(
                        'slug' => $plugin_slug,
                        'fields' => array(
                            'short_description' => true,
                            'sections' => false,
                            'requires' => false,
                            'rating' => false,
                            'ratings' => false,
                            'downloaded' => false,
                            'last_updated' => false,
                            'added' => false,
                            'tags' => false,
                            'homepage' => false,
                            'donate_link' => false,
                            'author_profile' => false,
                            'author' => false,
                        ),
                    ));

                    if (is_wp_error($plugin_information)) {
                        continue;
                    }

                    $plugin = $plugin_slug . '/' . $plugin_slug . '.php';
                    $plugin_active = false;
                    if (is_plugin_active($plugin)) {
                        $plugin_active = true;
                    }
                    ?>
                    <div class="asi-wizard-enable plugin-<?php echo $plugin_active ? 'active' : 'inactive' ?>">
                        <label class="block">
                            <?php if (!$plugin_active) { ?>
                                <span class="asi-wizard-toggle disabled">
                                    <input type="checkbox" name="asi-setup-install-plugin-<?php echo $plugin_slug ?>">
                                </span>
                            <?php } else { ?>
                                <span class="dashicons dashicons-yes"></span>
                            <?php } ?>
                            <div class="plugin-description">
                                <img class="plugin-logo" src="https://ps.w.org/<?php echo $plugin_slug ?>/assets/icon-256x256.png">
                                <h3><?php echo $plugin_information->name; ?></h3>
                                <?php echo $plugin_information->short_description; ?>
                            </div>
                            <div class="clear"></div>
                        </label>
                    </div>
                <?php } ?>
            </div>

            <p class="asi-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Next'); ?>" name="save_step"/>
            </p>
        </form>
        <?php
    }

    public function asi_setup_install_plugins_save() {

        check_admin_referer('asi-setup');

        $plugins = array(
            'woocommerce',
            'ninja-forms'
        );
        foreach ($plugins as $plugin_slug) {
            if (empty($_POST['asi-setup-install-plugin-' . $plugin_slug])) {
                continue;
            }

            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
            require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

            WP_Filesystem();

            $skin = new Automatic_Upgrader_Skin;
            $upgrader = new WP_Upgrader($skin);
            $installed_plugins = array_map(array(__CLASS__, 'format_plugin_slug'), array_keys(get_plugins()));
            $plugin = $plugin_slug . '/' . $plugin_slug . '.php';
            $installed = false;
            $activate = false;

            if (in_array($plugin_slug, $installed_plugins)) {
                $installed = true;
                $activate = !is_plugin_active($plugin);
            }

            if (!$installed) {
                ob_start();

                try {
                    $plugin_information = plugins_api('plugin_information', array(
                        'slug' => $plugin_slug,
                        'fields' => array(
                            'short_description' => false,
                            'sections' => false,
                            'requires' => false,
                            'rating' => false,
                            'ratings' => false,
                            'downloaded' => false,
                            'last_updated' => false,
                            'added' => false,
                            'tags' => false,
                            'homepage' => false,
                            'donate_link' => false,
                            'author_profile' => false,
                            'author' => false,
                        ),
                    ));

                    if (is_wp_error($plugin_information)) {
                        throw new Exception($plugin_information->get_error_message());
                    }

                    $package = $plugin_information->download_link;
                    $download = $upgrader->download_package($package);

                    if (is_wp_error($download)) {
                        throw new Exception($download->get_error_message());
                    }

                    $working_dir = $upgrader->unpack_package($download, true);

                    if (is_wp_error($working_dir)) {
                        throw new Exception($working_dir->get_error_message());
                    }

                    $result = $upgrader->install_package(array(
                        'source' => $working_dir,
                        'destination' => WP_PLUGIN_DIR,
                        'clear_destination' => false,
                        'abort_if_destination_exists' => true,
                        'clear_working' => true,
                        'hook_extra' => array(
                            'type' => 'plugin',
                            'action' => 'install',
                        ),
                    ));

                    if (is_wp_error($result)) {
                        throw new Exception($result->get_error_message());
                    }

                    $activate = true;
                } catch (Exception $e) {

                }

                ob_end_clean();
            }

            wp_clean_plugins_cache();

            if ($activate) {
                activate_plugin($plugin);
            }
        }

        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    public function asi_setup_content() {
        ?>
        <form method="post" class="create-pages-step">
            <?php wp_nonce_field('asi-setup'); ?>

            <h1><?php echo esc_html($this->steps[$this->step]['name']); ?></h1>

            <div class="inside">
                <div class="asi-wizard-enable">
                    <label>
                        <span class="asi-wizard-toggle disabled">
                            <input id="asi-setup-create-front-page" type="checkbox" name="asi-setup-create-front-page">
                        </span>
                        <?php echo esc_html(__('Create a Homepage like the demo', CHILD_THEME_SLUG)); ?>
                    </label>
                </div>
                <div class="asi-wizard-enable" id="asi-setup-create-blog-page">
                    <label>
                        <span class="asi-wizard-toggle disabled">
                            <input class="checkbox" type="checkbox" name="asi-setup-create-blog-page">
                        </span>
                        <?php echo esc_html(__('Create a Blog page', CHILD_THEME_SLUG)); ?>
                    </label>
                </div>
                <div class="asi-wizard-enable" id="asi-setup-create-header-widgets">
                    <label>
                        <span class="asi-wizard-toggle disabled">
                            <input class="checkbox" type="checkbox" name="asi-setup-create-header-widgets">
                        </span>
                        <?php echo esc_html(__('Create the cart widget in the header', CHILD_THEME_SLUG)); ?>
                    </label>
                </div>
                <div class="asi-wizard-enable" id="asi-setup-create-footer-widgets">
                    <label>
                        <span class="asi-wizard-toggle disabled">
                            <input class="checkbox" type="checkbox" name="asi-setup-create-footer-widgets">
                        </span>
                        <?php echo esc_html(__('Create footer widgets', CHILD_THEME_SLUG)); ?>
                    </label>
                </div>
                <?php if (is_plugin_active('ninja-forms/ninja-forms.php')) { ?>
                    <div class="asi-wizard-enable">
                        <label>
                            <span class="asi-wizard-toggle disabled">
                                <input type="checkbox" name="asi-setup-create-contact-page">
                            </span>
                            <?php echo esc_html(__('Create Contact page', CHILD_THEME_SLUG)); ?>
                        </label>
                    </div>
                <?php } ?>
                <div class="asi-wizard-enable">
                    <label>
                        <span class="asi-wizard-toggle disabled">
                            <input type="checkbox" name="asi-setup-create-about-page">
                        </span>
                        <?php echo esc_html(__('Create About me page', CHILD_THEME_SLUG)); ?>
                    </label>
                </div>
            </div>

            <p class="asi-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Next'); ?>" name="save_step"/>
            </p>
        </form>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#asi-setup-create-front-page').change(function () {
                    jQuery('#asi-setup-create-blog-page').toggle(this.checked);
                });
                jQuery('#asi-setup-create-blog-page').toggle(jQuery('#asi-setup-create-front-page').is(':checked'));
            });
        </script>
        <style type="text/css">
            #asi-setup-create-blog-page{
                display: none;
            }
        </style>
        <?php
    }

    public function asi_setup_content_save() {
        check_admin_referer('asi-setup');

        $frontpage = empty($_POST['asi-setup-create-front-page']) ? 0 : 1;
        $blogpage = empty($_POST['asi-setup-create-blog-page']) ? 0 : 1;
        $contact = empty($_POST['asi-setup-create-contact-page']) ? 0 : 1;
        $header_widgets = empty($_POST['asi-setup-create-header-widgets']) ? 0 : 1;
        $footer_widgets = empty($_POST['asi-setup-create-footer-widgets']) ? 0 : 1;
        $about = empty($_POST['asi-setup-create-about-page']) ? 0 : 1;

        $active_widgets = get_option('sidebars_widgets');

        if ($contact) {
            $page_data = array(
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
                'post_name' => 'contact',
                'post_title' => __('Contact', CHILD_THEME_SLUG),
                'post_content' => '[ninja_form id=1]',
                'comment_status' => 'closed',
            );
            wp_insert_post($page_data);
        }

        if ($about) {
            wp_insert_post([
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
                'post_name' => 'about',
                'post_title' => __('About', CHILD_THEME_SLUG),
                'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis neque orci, tristique eu lacinia vel, gravida ac neque. Proin rhoncus velit quis purus viverra gravida. Cras vitae ex laoreet, maximus enim ut, sagittis nisi. Cras condimentum accumsan est, in tincidunt ex vulputate id. Pellentesque at convallis libero. Phasellus erat leo, eleifend ut commodo sit amet, efficitur vel tellus. Cras nec finibus purus, nec faucibus risus. Etiam vulputate viverra viverra. Donec ac blandit sapien. Vivamus egestas feugiat pellentesque. Pellentesque ullamcorper blandit massa a blandit. Praesent eu dui pretium, suscipit sem sit amet, placerat nisi. Sed molestie sodales ante at maximus. Aliquam mauris nibh, rhoncus at maximus at, lacinia in nulla. In sodales accumsan vestibulum. Pellentesque enim tortor, consectetur vitae fermentum non, rutrum nec dui.<br/>Pellentesque porta lorem a nunc posuere laoreet. Donec imperdiet erat orci, quis volutpat felis vehicula eget. Aenean sed lobortis justo, eget suscipit velit. Sed semper tellus in condimentum tempor. In venenatis maximus consequat. Vivamus luctus ullamcorper arcu gravida iaculis. Nam interdum, magna et aliquam eleifend, ligula mauris ullamcorper eros, et faucibus lacus dui et sem.<br/>Vivamus sit amet lobortis lorem, at consectetur velit. Etiam finibus ligula et lorem eleifend condimentum. Nunc et nunc eleifend, mattis nibh vel, venenatis velit. Mauris mi neque, convallis id aliquet sit amet, dictum vitae enim. Cras eros purus, mollis sed placerat vitae, ornare in tellus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sollicitudin tristique tellus ut porta. Nulla ut maximus lacus, sed pellentesque velit. Etiam cursus neque vitae elit iaculis elementum. In sed consectetur sem, vitae sagittis nisl. Proin molestie elementum arcu, eget vehicula urna porttitor a. Nullam nec arcu sodales, fringilla nibh quis, hendrerit ex.',
                'comment_status' => 'closed',
            ]);
        }

        if ($frontpage) {

            $frontpage_id = get_option('page_on_front');
            if (!$frontpage_id) {
                $page_data = array(
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => 1,
                    'post_name' => 'homepage',
                    'post_title' => __('Homepage', CHILD_THEME_SLUG),
                    'post_content' => '',
                    'comment_status' => 'closed',
                );
                $frontpage_id = wp_insert_post($page_data);
            }

            if ($frontpage_id) {

                update_post_meta($frontpage_id, '_wp_page_template', 'templates/front-page.php');
                update_option('show_on_front', 'page');
                update_option('page_on_front', $frontpage_id);

                if ($blogpage) {
                    $page_data = array(
                        'post_status' => 'publish',
                        'post_type' => 'page',
                        'post_author' => 1,
                        'post_name' => 'blog',
                        'post_title' => 'Blog',
                        'post_content' => '',
                        'comment_status' => 'closed',
                    );
                    $blogpage_id = wp_insert_post($page_data);
                    if ($blogpage_id) {
                        update_option('page_for_posts', $blogpage_id);
                    }
                }

                //* Install home widgets
                $wp_upload_dir = wp_upload_dir();
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $images = array();
                $items = array(
                    'widget-main-1.jpg',
                    'widget-two-blocks-1.jpg',
                    'widget-logos-1.jpg',
                    'widget-logos-2.jpg',
                    'widget-logos-3.jpg',
                    'widget-logos-4.jpg',
                    'widget-logos-5.jpg',
                    'widget-logos-6.jpg',
                );
                foreach ($items as $item) {
                    $filename = CHILD_DIR . '/assets/images/' . $item;
                    $filename_upload = $wp_upload_dir['url'] . '/' . basename($filename);
                    $filename_upload_path = $wp_upload_dir['path'] . '/' . basename($filename);
                    copy($filename, $filename_upload_path);
                    $filetype = wp_check_filetype(basename($filename), null);
                    $attachment = array(
                        'guid' => $filename_upload,
                        'post_mime_type' => $filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    $attach_id = wp_insert_attachment($attachment, $filename_upload_path);
                    $attach_data = wp_generate_attachment_metadata($attach_id, $filename_upload_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    $images[$item] = $attach_id;
                }

                $widgets = array();
                $widgets[] = array(
                    'id_base' => 'asitheme_widget_two_blocks',
                    'data' => array(
                        'background_color' => '#708C8E',
                        'text_color' => '#fff',
                        'text_align' => 'center',
                        'subtitle' => "Hi there, I'm a Genesis<br>Wordpress Theme",
                        'title' => 'LOLA<br>STYLE',
                        'title_size' => 126,
                        'text' => '',
                        'image' => $images['widget-two-blocks-1.jpg'],
                        'text_position' => 'right',
                        'button_text' => 'Contact',
                        'button_link' => CHILD_THEME_AUTHORURI,
                        'button_target' => 0,
                        'button_color' => '#fff',
                        'button_background' => 'transparent',
                        'button_border_color' => '#fff',
                        'button_color_hover' => '#000',
                        'button_background_hover' => '#fff',
                        'button_border_color_hover' => '#fff',
                    )
                );
                if (is_plugin_active('woocommerce/woocommerce.php')) {
                    $widgets[] = array(
                        'id_base' => 'woocommerce_products',
                        'data' => array(
                            'title' => __('Featured products', CHILD_THEME_SLUG),
                            'number' => 2,
                        )
                    );
                }
                $widgets[] = array(
                    'id_base' => 'asitheme_widget_quotes',
                    'data' => array(
                        'items' => array(
                            array(
                                'quote' => "I'm not afraid of death; I just don't want to be there when it happens.",
                                'author' => 'Woody Allen',
                            ),
                        ),
                    )
                );
                $widgets[] = array(
                    'id_base' => 'asitheme_widget_main',
                    'data' => array(
                        'border_color' => '#708C8E',
                        'background_color' => '',
                        'text_color' => '#000',
                        'title' => 'New York',
                        'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eu eros dui. Aliquam erat volutpat. Pellentesque quis efficitur sapien.',
                        'button_text' => 'Cras ut risus',
                        'button_link' => get_home_url(),
                        'button_target' => 0,
                        'image' => $images['widget-main-1.jpg'],
                    )
                );
                $widgets[] = array(
                    'id_base' => 'asitheme_widget_features',
                    'data' => array(
                        'background_color' => '#fff',
                        'text_color' => '#000',
                        'title' => '',
                        'items' => array(
                            array(
                                'title' => 'Feature One',
                                'text' => 'Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet',
                                'link' => '',
                            ),
                            array(
                                'title' => 'Feature Two',
                                'text' => 'Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet',
                                'link' => '',
                            ),
                            array(
                                'title' => 'Feature Three',
                                'text' => 'Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet',
                                'link' => '',
                            ),
                            array(
                                'title' => 'Feature Four',
                                'text' => 'Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet<br>Lorem ipsum dolor sit amet',
                                'link' => '',
                            )
                        ),
                    )
                );
                $widgets[] = array(
                    'id_base' => 'asitheme_widget_logos',
                    'data' => array(
                        'background_color' => '#fff',
                        'text_color' => '#000',
                        'title' => __('Some of our customers', CHILD_THEME_SLUG),
                        'logos' => array(
                            array(
                                'link' => '#',
                                'image' => $images['widget-logos-1.jpg'],
                            ),
                            array(
                                'link' => '#',
                                'image' => $images['widget-logos-2.jpg'],
                            ),
                            array(
                                'link' => '#',
                                'image' => $images['widget-logos-3.jpg'],
                            ),
                            array(
                                'link' => '#',
                                'image' => $images['widget-logos-4.jpg'],
                            ),
                            array(
                                'link' => '#',
                                'image' => $images['widget-logos-5.jpg'],
                            ),
                            array(
                                'link' => '#',
                                'image' => $images['widget-logos-6.jpg'],
                            ),
                        ),
                    )
                );

                $active_widgets['asi-front-page'] = array();
                foreach ($widgets as $item) {
                    $id_base = $item['id_base'];
                    $data = $item['data'];
                    $widget = get_option("widget_$id_base");
                    $i = (int)max(array_keys($widget)) + 1;
                    $widget[$i] = $data;
                    update_option("widget_$id_base", $widget);
                    $active_widgets['asi-front-page'][] = "$id_base-$i";
                }
                update_option('sidebars_widgets', $active_widgets);
            }
        }

        //* Install header right widgets
        if ($header_widgets) {
            $widgets = array(
                array('id_base' => 'asitheme_widget_woocommerce_cart'),
            );
            $active_widgets['header-right'] = array();
            foreach ($widgets as $item) {
                $id_base = $item['id_base'];
                $data = $item['data'];
                $widget = get_option("widget_$id_base");
                $i = (int)max(array_keys($widget)) + 1;
                $widget[$i] = $data;
                update_option("widget_$id_base", $widget);
                $active_widgets['header-right'][] = "$id_base-$i";
            }
            update_option('sidebars_widgets', $active_widgets);
        }

        //* Install footer widgets
        if ($footer_widgets) {

            $widgets_1 = array(
                'id_base' => 'custom_html',
                'data' => array(
                    'title' => __('Legal issues', CHILD_THEME_SLUG),
                    'content' => '<a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">' . __('Legal warning', CHILD_THEME_SLUG) . '</a><br/>
                                    <a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">' . __('Privacy Policy', CHILD_THEME_SLUG) . '</a><br/>
                                    <a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">' . __('General conditions of online purchase', CHILD_THEME_SLUG) . '</a><br/>
                                    <a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">' . __('Cookies policy', CHILD_THEME_SLUG) . '</a>',
                )
            );
            $id_base = $widgets_1['id_base'];
            $data = $widgets_1['data'];
            $widget = get_option("widget_$id_base");
            $i = (int)max(array_keys($widget)) + 1;
            $widget[$i] = $data;
            update_option("widget_$id_base", $widget);
            $active_widgets['footer-1'][] = "$id_base-$i";

            $widgets_2 = array(
                'id_base' => 'custom_html',
                'data' => array(
                    'title' => __('Client area', CHILD_THEME_SLUG),
                    'content' => '<a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">' . __('Log in', CHILD_THEME_SLUG) . '</a><br/>
                                    <a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">' . __('Contact', CHILD_THEME_SLUG) . '</a><br/>
                                    <a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">' . __('Faqs', CHILD_THEME_SLUG) . '</a>',
                )
            );
            $id_base = $widgets_2['id_base'];
            $data = $widgets_2['data'];
            $widget = get_option("widget_$id_base");
            $i = (int)max(array_keys($widget)) + 1;
            $widget[$i] = $data;
            update_option("widget_$id_base", $widget);
            $active_widgets['footer-2'][] = "$id_base-$i";

            $widgets_3 = array(
                'id_base' => 'custom_html',
                'data' => array(
                    'title' => __('Social networks', CHILD_THEME_SLUG),
                    'content' => '<a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">Facebook</a><br/>
                                    <a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">Twitter</a><br/>
                                    <a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">Youtube</a><br/>
                                    <a href="' . CHILD_THEME_AUTHORURI . '" target="_blank">Instagram</a>',
                )
            );
            $id_base = $widgets_3['id_base'];
            $data = $widgets_3['data'];
            $widget = get_option("widget_$id_base");
            $i = (int)max(array_keys($widget)) + 1;
            $widget[$i] = $data;
            update_option("widget_$id_base", $widget);
            $active_widgets['footer-3'][] = "$id_base-$i";

            update_option('sidebars_widgets', $active_widgets);
        }

        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    public function asi_setup_header() {
        $header_position = get_theme_mod(CHILD_THEME_SLUG . '_header_position', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_position']);
        $header_transparency = get_theme_mod(CHILD_THEME_SLUG . '_header_transparency', CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_header_transparency']);
        $pages = get_posts(array(
            'posts_per_page' => -1,
            'post_type' => 'page',
            'orderby' => 'title',
            'order' => 'asc'
        ));
        ?>
        <form method="post" class="header-step">
            <?php wp_nonce_field('asi-setup'); ?>

            <h1><?php echo esc_html($this->steps[$this->step]['name']); ?></h1>

            <p class="title">
                <?php echo esc_html(__('Header position & transparency', CHILD_THEME_SLUG)); ?>
            </p>

            <div class="inside">
                <p>
                    <label class="block">
                        <?php echo esc_html(__('Header position', CHILD_THEME_SLUG)); ?>
                    </label>
                    <select class="widefat" name="asi-setup-header-position" id="asi-setup-header-position">
                        <option value="fixed" <?php echo $header_position == 'fixed' ? 'selected="selected"' : ''; ?>><?php echo esc_html(__('Header fixed', CHILD_THEME_SLUG)); ?></option>
                        <option value="relative"<?php echo $header_position == 'relative' ? 'selected="selected"' : ''; ?>><?php echo esc_html(__('Header relative', CHILD_THEME_SLUG)); ?></option>
                    </select>
                </p>
                <p id="asi-setup-fixed-header">
                    <label class="block">
                        <?php echo esc_html(__('Background transparency', CHILD_THEME_SLUG)); ?>
                    </label>
                    <input name="asi-setup-header-transparency" min="0" max="100" step="1" type='range' value="<?php echo esc_attr($header_transparency); ?>" oninput="jQuery(this).next('input').val( jQuery(this).val() )">
                    <input class="widefat number-inline" onKeyUp="jQuery(this).prev('input').val(jQuery(this).val())" type='text' value='<?php echo esc_attr($header_transparency); ?>'>
                </p>
                <p class="asi-wizard-enable" id="asi-setup-header-search">
                    <label>
                        <span class="asi-wizard-toggle disabled">
                            <input class="checkbox" type="checkbox" name="asi-setup-header-search">
                        </span>
                        <?php echo esc_html(__('Show search engine as in the demo?', CHILD_THEME_SLUG)); ?>
                    </label>
                </p>
            </div>

            <?php if ($pages) { ?>

                <p class="title">
                    <?php echo esc_html(__('Menu', CHILD_THEME_SLUG)); ?>
                </p>

                <div class="inside">
                    <div class="asi-wizard-enable">
                        <label>
                            <span class="asi-wizard-toggle disabled">
                                <input class="checkbox" type="checkbox" name="asi-setup-header-create-menu" id="asi-setup-header-create-menu">
                            </span>
                            <?php echo esc_html(__('Create menu and assign it to primary', CHILD_THEME_SLUG)); ?>
                        </label>
                    </div>
                    <div id="asi-setup-menu-items" class="repeater">
                        <label class="block">
                            <?php echo esc_html(__('Menu items', CHILD_THEME_SLUG)); ?>
                        </label>
                        <div data-repeater-list="asi-setup-menu-items">
                            <div data-repeater-item>
                                <div class="table">
                                    <div class="row">
                                        <div class="cell nombre">
                                            <select name="asi-setup-menu-item" class="widefat">
                                                <option value=""><?php echo esc_html(__('Select a page', CHILD_THEME_SLUG)); ?></option>
                                                <?php foreach ($pages as $page) { ?>
                                                    <option value="<?php echo esc_attr($page->ID) ?>"><?php echo esc_html($page->post_title) ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="cell delete">
                                            <input data-repeater-delete class="button action delete" type="button" value="<?php echo esc_attr(__('Remove')); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input data-repeater-create class="button action add" type="button" value="<?php echo esc_attr(__('Add')); ?>">
                    </div>
                </div>

            <?php } ?>

            <p class="asi-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Next'); ?>" name="save_step"/>
            </p>
        </form>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#asi-setup-header-position').change(function () {
                    jQuery('#asi-setup-fixed-header').toggle(this.checked);
                });
                jQuery('#asi-setup-fixed-header').toggle(jQuery('#asi-setup-header-position').val() === 'fixed');

                jQuery('#asi-setup-header-create-menu').change(function () {
                    jQuery('#asi-setup-menu-items').toggle(this.checked);
                });
                jQuery('#asi-setup-menu-items').toggle(jQuery('#asi-setup-header-create-menu').is(':checked'));

                jQuery('#asi-setup-menu-items').repeater({
                    show: function () {
                        jQuery(this).slideDown(200);
                    },
                    hide: function (remove) {
                        if (confirm('¿Seguro que quieres eliminarlo?')) {
                            jQuery(this).slideUp(remove, function () {
                                jQuery(this).remove();
                            });
                        }
                    }
                });
            });
        </script>
        <style type="text/css">
            #asi-setup-menu-items,
            #asi-setup-fixed-header{
                display: none;
            }
        </style>
        <?php
    }

    public function asi_setup_header_save() {
        check_admin_referer('asi-setup');

        $menu = empty($_POST['asi-setup-header-create-menu']) ? 0 : 1;
        $search = empty($_POST['asi-setup-header-search']) ? 0 : 1;
        $position = trim($_POST['asi-setup-header-position']);
        $transparency = (int)trim($_POST['asi-setup-header-transparency']);
        $transparency = max($transparency, 0);
        $transparency = min($transparency, 100);

        set_theme_mod(CHILD_THEME_SLUG . '_header_position', $position);
        set_theme_mod(CHILD_THEME_SLUG . '_header_transparency', $transparency);

        if ($menu) {

            $menu_items = (array)$_POST['asi-setup-menu-items'];
            $menu_items = array_column($menu_items, 'asi-setup-menu-item');
            $menu_items = array_unique(array_filter($menu_items));

            $menu_name = 'Menu Primary ' . CHILD_THEME_NAME;
            $menu_exists = wp_get_nav_menu_object($menu_name);

            if (!$menu_exists) {
                $menu_id = wp_create_nav_menu($menu_name);

                $menus = get_theme_mod('nav_menu_locations');
                $menus['primary'] = $menu_id;
                set_theme_mod('nav_menu_locations', $menus);

                if ($menu_items) {
                    foreach ($menu_items as $item) {
                        wp_update_nav_menu_item($menu_id, 0, array(
                            'menu-item-title' => get_the_title($item),
                            'menu-item-url' => get_permalink($item),
                            'menu-item-status' => 'publish'));
                    }
                }
            }
        }

        if ($search) {
            set_theme_mod(CHILD_THEME_SLUG . '_header_search', true);
        } else {
            set_theme_mod(CHILD_THEME_SLUG . '_header_search', false);
        }

        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    public function asi_setup_import() {
        ?>
        <form method="post" class="import-step">
            <?php wp_nonce_field('asi-setup'); ?>

            <h1><?php echo esc_html($this->steps[$this->step]['name']); ?></h1>

            <div class="inside">
                <div class="asi-wizard-enable">
                    <label>
                        <span class="asi-wizard-toggle disabled">
                            <input type="checkbox" name="asi-setup-import-posts">
                        </span>
                        <?php echo esc_html(__('Create test posts', CHILD_THEME_SLUG)); ?>
                    </label>
                </div>
                <?php if (is_plugin_active('woocommerce/woocommerce.php')) { ?>
                    <div class="asi-wizard-enable">
                        <label>
                            <span class="asi-wizard-toggle disabled">
                                <input type="checkbox" name="asi-setup-import-products">
                            </span>
                            <?php echo esc_html(__('Create test products', CHILD_THEME_SLUG) . ' (WooCommerce)'); ?>
                        </label>
                    </div>
                <?php } ?>
            </div>

            <p class="asi-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Next'); ?>" name="save_step"/>
            </p>
        </form>
        <?php
    }

    public function asi_setup_import_save() {
        check_admin_referer('asi-setup');

        $posts = empty($_POST['asi-setup-import-posts']) ? 0 : 1;
        $products = empty($_POST['asi-setup-import-products']) ? 0 : 1;

        if ($posts) {

            $wp_upload_dir = wp_upload_dir();
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $ids = array(6, 5, 4, 3, 2, 1);

            foreach ($ids as $id) {
                $post_id = wp_insert_post(array(
                    'post_title' => "I'm a WordPress Genesis demo post " . $id,
                    'post_content' => "<h2>Header H2</h2>\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam dictum eleifend aliquet. Donec consectetur tempus leo ut pharetra. Sed quis aliquam lorem. Fusce ac viverra lacus, vitae interdum erat. Aenean tempus dui nisl, id pharetra mauris tincidunt finibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent euismod at nunc sit amet aliquam. Phasellus vitae tellus a eros fringilla mollis in id eros. Etiam at turpis purus. Proin vulputate nisi tellus, quis egestas ipsum pharetra ut. Morbi aliquet lacus ex, ut bibendum libero auctor sit amet. Integer non euismod felis, quis vestibulum erat. Duis sed blandit justo, et dapibus arcu. Cras condimentum felis vel arcu dictum, vitae lobortis lectus pellentesque. Nullam leo lacus, tincidunt eget ornare eget, dictum vel ipsum. Nulla cursus tortor eros, a dictum dui mollis eget.\n\nPellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin sodales neque a dictum egestas. Suspendisse venenatis eleifend diam. Aenean id dapibus leo. Nullam scelerisque tellus vel sem sollicitudin semper. Ut volutpat convallis ex, id venenatis lectus pellentesque sit amet. Aenean ut ligula quis purus malesuada lobortis a ac ex. Aenean ac lorem nec tellus condimentum finibus.\n<h2>Header H2</h2>\nPhasellus efficitur tortor quis orci venenatis malesuada. Cras viverra tellus ante, ultricies molestie odio scelerisque eget. Nam elementum in purus ac consectetur. Sed nisl mauris, commodo eget lorem id, scelerisque pretium libero. Pellentesque et tellus pulvinar, condimentum purus nec, tempor lacus. In non erat non dui mattis finibus et placerat mi. Ut condimentum ligula sem, sit amet eleifend felis fringilla non. Nunc aliquet lorem ac odio efficitur luctus. Fusce erat ipsum, suscipit in fringilla eget, lobortis in leo. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent sit amet quam fermentum, molestie enim id, maximus enim. Sed malesuada posuere lacus at congue. In vestibulum est tortor, vel posuere ipsum suscipit vestibulum. Ut ut massa porttitor, sollicitudin ipsum malesuada, malesuada velit. In viverra posuere mollis.",
                    'post_excerpt' => 'Et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.',
                    'post_status' => 'publish'
                ));
                $filename = CHILD_DIR . '/assets/images/post-asithemes-' . $id . '.jpg';
                $filename_upload = $wp_upload_dir['url'] . '/' . basename($filename);
                $filename_upload_path = $wp_upload_dir['path'] . '/' . basename($filename);
                copy($filename, $filename_upload_path);
                $filetype = wp_check_filetype(basename($filename), null);
                $attachment = array(
                    'guid' => $filename_upload,
                    'post_mime_type' => $filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment($attachment, $filename_upload_path, $post_id);
                $attach_data = wp_generate_attachment_metadata($attach_id, $filename_upload_path);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($post_id, $attach_id);
            }
        }

        if ($products && is_plugin_active('woocommerce/woocommerce.php')) {

            if ((int)WC()->version > 2) {
                $exist_products = wc_get_products(array(
                    'status' => array('publish'),
                    'limit' => 1,
                    'return' => 'ids',
                    'orderby' => 'id',
                    'order' => 'ASC',
                ));
            } else {
                $exist_products = get_posts(array(
                    'post_status' => array('publish'),
                    'post_type' => array('product'),
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'orderby' => 'ID',
                    'order' => 'ASC',
                ));
            }

            if (!$exist_products) {

                $wp_upload_dir = wp_upload_dir();
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                /* Create test products */
                $ids = array(6, 5, 4, 3, 2, 1);

                foreach ($ids as $id) {

                    $post_id = wp_insert_post(array(
                        'post_title' => 'Product ' . $id,
                        'post_excerpt' => 'Cras sit amet felis faucibus, commodo lacus eget, feugiat nulla. Vestibulum dignissim dapibus lacinia. Suspendisse vitae felis rutrum, convallis lacus ac, pulvinar mauris. Proin eu dapibus justo. Fusce ex tortor, gravida sed lectus ut, consectetur mattis mauris. Sed consectetur sit amet tellus lacinia scelerisque. Suspendisse quis enim lorem.',
                        'post_status' => 'publish',
                        'post_type' => 'product'
                    ));

                    wp_set_object_terms($post_id, 'simple', 'product_type');

                    if ((int)WC()->version > 2) {
                        $product = wc_get_product($post_id);
                        $product->set_stock_status('instock');
                        $product->set_total_sales(0);
                        $product->set_downloadable(false);
                        $product->set_virtual(false);
                        $product->set_price(9.99);
                        $product->set_regular_price(9.99);
                        $product->set_sale_price('');
                        $product->set_purchase_note('');
                        $product->set_featured(false);
                        $product->set_weight(0);
                        $product->set_length(0);
                        $product->set_width(0);
                        $product->set_height(0);
                        $product->set_sku('');
                        $product->set_attributes(array());
                        $product->set_date_on_sale_from('');
                        $product->set_date_on_sale_to('');
                        $product->set_sold_individually(false);
                        $product->set_manage_stock(false);
                        $product->set_backorders('no');
                        //$product->set_stock('');
                        wc_update_product_stock($product, '', 'set');
                        $product->save();
                    } else {
                        update_post_meta($post_id, '_visibility', 'visible');
                        update_post_meta($post_id, '_stock_status', 'instock');
                        update_post_meta($post_id, 'total_sales', '0');
                        update_post_meta($post_id, '_downloadable', 'no');
                        update_post_meta($post_id, '_virtual', 'no');
                        update_post_meta($post_id, '_price', 9.99);
                        update_post_meta($post_id, '_regular_price', 9.99);
                        update_post_meta($post_id, '_sale_price', '');
                        update_post_meta($post_id, '_purchase_note', '');
                        update_post_meta($post_id, '_featured', 'no');
                        update_post_meta($post_id, '_weight', '');
                        update_post_meta($post_id, '_length', '');
                        update_post_meta($post_id, '_width', '');
                        update_post_meta($post_id, '_height', '');
                        update_post_meta($post_id, '_sku', '');
                        update_post_meta($post_id, '_product_attributes', array());
                        update_post_meta($post_id, '_sale_price_dates_from', '');
                        update_post_meta($post_id, '_sale_price_dates_to', '');
                        update_post_meta($post_id, '_sold_individually', '');
                        update_post_meta($post_id, '_manage_stock', 'no');
                        update_post_meta($post_id, '_backorders', 'no');
                        update_post_meta($post_id, '_stock', '');
                    }

                    $filename = CHILD_DIR . '/assets/images/product-asithemes-' . $id . '.jpg';
                    $filename_upload = $wp_upload_dir['url'] . '/' . basename($filename);
                    $filename_upload_path = $wp_upload_dir['path'] . '/' . basename($filename);
                    copy($filename, $filename_upload_path);
                    $filetype = wp_check_filetype(basename($filename), null);
                    $attachment = array(
                        'guid' => $filename_upload,
                        'post_mime_type' => $filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    $attach_id = wp_insert_attachment($attachment, $filename_upload_path, $post_id);
                    $attach_data = wp_generate_attachment_metadata($attach_id, $filename_upload_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    set_post_thumbnail($post_id, $attach_id);
                }

                /* Modify product image settings of WooCommerce customizer */
                update_option('woocommerce_thumbnail_cropping', '1:1');
                update_option('woocommerce_catalog_columns', 3);
            }
        }

        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    public function asi_setup_ready() {
        $items = array(
            array(
                'description' => __('Customize', CHILD_THEME_SLUG),
                'extra-info' => __('You can modify the theme from the customizer', CHILD_THEME_SLUG),
                'button-text' => __('Go to Customizer', CHILD_THEME_SLUG),
                'button-link' => admin_url('customize.php?return=' . urlencode(admin_url('/'))),
            ),
            array(
                'description' => __('Dashboard', CHILD_THEME_SLUG),
                'extra-info' => __('Go to Dashboard', CHILD_THEME_SLUG),
                'button-text' => __('Go to Dashboard', CHILD_THEME_SLUG),
                'button-link' => admin_url('/'),
            ),
            array(
                'description' => __('Homepage', CHILD_THEME_SLUG),
                'extra-info' => __('Theme is set, now you can go to the homepage', CHILD_THEME_SLUG),
                'button-text' => __('Go to homepage', CHILD_THEME_SLUG),
                'button-link' => home_url('/'),
            )
        );
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            array_unshift($items, array(
                'description' => 'WooCommerce',
                'extra-info' => __('Setup wizard', 'woocommerce'),
                'button-text' => __('Run the Setup Wizard', 'woocommerce'),
                'button-link' => admin_url('index.php?page=wc-setup'),
            ));
        }
        ?>
        <h1><?php echo esc_html($this->steps[$this->step]['name']); ?></h1>

        <ul class="asi-wizard-next-steps">
            <?php foreach ($items as $item) { ?>
                <li class="asi-wizard-next-step-item">
                    <div class="asi-wizard-next-step-description">
                        <h3 class="next-step-description"><?php echo esc_html($item['description']) ?></h3>
                        <p class="next-step-extra-info"><?php echo esc_html($item['extra-info']) ?></p>
                    </div>
                    <div class="asi-wizard-next-step-action">
                        <p class="asi-setup-actions step">
                            <a class="button button-large" href="<?php echo esc_url($item['button-link']) ?>">
                                <?php echo esc_html($item['button-text']) ?>
                            </a>
                        </p>
                    </div>
                </li>
            <?php } ?>
        </ul>
        <?php
    }

    private static function format_plugin_slug($key) {
        $slug = explode('/', $key);
        $slug = explode('.', end($slug));
        return $slug[0];
    }

}

new ASI_Setup_Wizard();
