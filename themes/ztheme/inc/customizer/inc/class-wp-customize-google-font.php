<?php

class WP_Customize_Google_Font_Control extends WP_Customize_Control {

    public function render_content() {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, CHILD_THEME_AUTHORURI . 'google-fonts.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $fonts_json = curl_exec($ch);
        curl_close($ch);

        $value = $this->value();
        if ($value == '') {
            $value = CHILD_THEME_DEFAULTS[CHILD_THEME_SLUG . '_font'];
        }

        $fonts = json_decode($fonts_json);
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <span class="description customize-control-description"><?php _e('We use Google Fonts, simply select your choice', CHILD_THEME_SLUG) ?></span>
            <select id="<?php echo esc_attr($this->id); ?>" name="<?php echo esc_attr($this->id); ?>" data-customize-setting-link="<?php echo esc_attr($this->id); ?>">
                <?php foreach ($fonts as $font): ?>
                    <option value="<?php echo esc_sql($font->font_enqueue); ?>" <?php echo selected($value, $font->font_enqueue, false) ?>>
                        <?php echo $font->font_name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php
    }

}
