<?php

class WP_Customize_Multiselect_Control extends WP_Customize_Control {

    public $type = 'multiselect';

    public function render_content() {
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <select <?php $this->link(); ?> multiple="multiple">
                <?php
                foreach ($this->choices as $value => $label) {
                    $selected = (in_array($value, $this->value())) ? selected(1, 1, false) : '';
                    echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . $label . '</option>';
                }
                ?>
            </select>
        </label>
        <?php
    }

}
