<?php

class Text_Editor_Custom_Control extends WP_Customize_Control {

    public function render_content() {
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <input type="hidden" <?php $this->link(); ?> value="<?php echo esc_textarea($this->value()); ?>">
            <?php
            $settings = array(
                'textarea_name' => $this->id,
                'media_buttons' => false,
                'drag_drop_upload' => false,
                'teeny' => true,
                'textarea_rows' => 10
            );
            wp_editor($this->value(), $this->id, $settings);
            ?>
            <style type="text/css">
                #wp-link-wrap{
                    z-index: 99999999999999 !important;
                }
                #wp-link-backdrop{
                    z-index: 99999999999999 !important;
                }
                .mce-floatpanel, .mce-toolbar-grp.mce-inline-toolbar-grp{
                    z-index: 99999999999999 !important;
                }
            </style>
        </label>
        <?php
        do_action('admin_print_footer_scripts');
    }

}
