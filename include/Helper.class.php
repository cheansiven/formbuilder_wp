<?php

class FCF_Helper {

    public function form_generate_post_meta_box_output($post) {
        $form_code = get_post_meta($post->ID, 'form_code', TRUE);
        if (empty($form_code)) {
            $form_code = '{"fields":[{"label":"Full Name","field_type":"text","required":true,"field_options":{"size":"small"},"cid":"c2"},{"label":"Email Address","field_type":"email","required":true,"field_options":{},"cid":"c6"}]}';
        }
        ?>
        <div class="fcf_form_builder"></div>
        <input type="hidden" name="form_code" value='<?php echo $form_code ?>'>
        <script>
            var fcf_use = true;
        </script>
        <?php
    }

    public function shortcode_fcf_shortcode_out($post) {
        $submit_txt = get_post_meta($post->ID, 'submit_txt', TRUE);
        $redirect = get_post_meta($post->ID,'redirect_url',TRUE);

        if (empty($submit_txt))
            $submit_txt = "Submit";
        ?>
        <code>[fcf id=<?php echo $post->ID ?>]</code>
        <p>Copy and Paste this Code, where you would like to show form.</p>
        <p><label>Submit Button Text</label><input type="text" name="submit_txt" value="<?php echo $submit_txt ?>" class="widefat"></p>
        <p><label>Redirect Url:</label><input type="text" class="widefat" name="redirect_url" value="<?php echo $redirect ?>"></p>
        <?php
    }



    static public function fcf_generate_form_shortcode_out($atts) {
        if (!isset($atts['id']))
            return;
        if (empty($atts['id']))
            return;
        $post_id = (int) $atts['id'];
        $redirect_url = get_post_meta($post_id,'redirect_url',true);
        $form_code = get_post_meta($post_id, 'form_code', TRUE);
        $form_code = json_decode($form_code);
        $form_code = $form_code->fields;
        $submit_txt = get_post_meta($post_id, 'submit_txt', TRUE);
        ob_start();
        ?>
        <script>
            var redirect_url = "<?php echo trim($redirect_url) ?>";
        </script>
        <div class="fcf end_message"></div>
        <div class="fcf loader" style="display:none;margin:0 auto;width:100px;height:100px;">
            <img src="<?php echo FCF_URL ?>/resources/loader.gif"  >
        </div>
        <form method="POST" class="fcf-form" id="fcf-<?php echo $post_id; ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="fcf_submit">
            <input type="hidden" name="form_id" value="<?php echo $post_id; ?>">
            <div class="fcf message"></div>
            <table>
                <?php
                foreach ($form_code as $field) {
                    FCF_Helper::makeField($field);
                }
                ?>
                <tr>
                    <td colspan="2">
                        <input type="submit" class="fcf submit" value="<?php echo $submit_txt ?>">
                    </td>
                </tr>
            </table>
        </form>
        <?php
        return ob_get_clean();
    }

    static public function makeField($field) {
        switch ($field->field_type) {
            case 'section_break':
                ?>
                <tr>
                    <td colspan="2">
                        <h2 class="fcf section_break"><?php echo $field->label ?></h2>
                    </td>
                </tr>
                <?php
                break;
            case 'text':
            case 'email':
            case 'date':
            case 'time':
            case 'website':
                $req = 'required';
                if (!$field->required)
                    $req = '';
                if ($field->field_type == 'website')
                    $field->field_type = 'url';
                ?>
                <tr>
                    <td><label><?php echo $field->label ?></label></td>
                    <td><input data-label="<?php echo $field->label ?>" class="fcf <?php echo $field->field_type . ' ' . $req; ?>" name="fcf[<?php echo $field->cid ?>]" type="<?php echo $field->field_type ?>" ></td>
                </tr>
                <?php
                break;
            case 'file':
                $req = 'required';
                if (!$field->required)
                    $req = '';
                ?>
                <tr>
                    <td><label><?php echo $field->label ?></label></td>
                    <td><input data-label="<?php echo $field->label ?>" class="fcf <?php echo $field->field_type . ' ' . $req; ?>" name="<?php echo $field->cid ?>" type="<?php echo $field->field_type ?>" ></td>
                </tr>
                <?php
                break;
            case 'paragraph':
                $req = 'required';
                if (!$field->required)
                    $req = '';
                ?>
                <tr>
                    <td><label><?php echo $field->label ?></label></td>
                    <td><textarea data-label="<?php echo $field->label ?>" class="fcf <?php echo $field->field_type . ' ' . $req; ?>" name="fcf[<?php echo $field->cid ?>]"></textarea></td>
                </tr>
                <?php
                break;
            case 'number':
                $field_options = $field->field_options;
                $req = 'required';
                if (!$field->required)
                    $req = '';
                ?>
                <tr>
                    <td><label><?php echo $field->label ?></label></td>
                    <td><input data-label="<?php echo $field->label ?>" class="fcf <?php echo $field->field_type . ' ' . $req; ?>" name="fcf[<?php echo $field->cid ?>]" min="<?php echo $field_options->min ?>" max="<?php echo $field_options->max ?>" type="<?php echo $field->field_type ?>" ><span class="unit"><?php echo $field_options->units ?></span></td>
                </tr>
                <?php
                break;
            case 'address':
                $req = 'required';
                if (!$field->required)
                    $req = '';
                ?>
                <input type="hidden" name="fcf[<?php echo $field->cid ?>][array_type]" value="address">
                <input type="hidden" name="fcf[<?php echo $field->cid ?>][label]" value="<?php echo $field->label ?>">
                <tr>
                    <td colspan="2"><?php echo $field->label ?></td>
                </tr>
                <tr>
                    <td><label>Address</label></td>
                    <td><input data-label="Address" class="fcf <?php echo $field->field_type . ' ' . $req; ?>" name="fcf[<?php echo $field->cid ?>][adr]" type="text" ></td>
                </tr>
                <tr>
                    <td><label>City</label></td>
                    <td><input data-label="City" class="fcf city <?php echo $req; ?>" name="fcf[<?php echo $field->cid ?>][city]" type="text" ></td>
                </tr>
                <tr>
                    <td><label>State</label></td>
                    <td><input data-label="State" class="fcf state <?php echo $req; ?>" name="fcf[<?php echo $field->cid ?>][state]" type="text" ></td>
                </tr>
                <tr>
                    <td><label>Zip</label></td>
                    <td><input data-label="Zip" class="fcf zip <?php echo $req; ?>" name="fcf[<?php echo $field->cid ?>][zip]" type="text" ></td>
                </tr>
                <tr>
                    <td><label>Country</label></td>
                    <td>
                        <select data-label="Country" class="fcf city <?php echo $req; ?>" name="fcf[<?php echo $field->cid ?>][country]">
                            <?php FCF_Helper::makeCountriesOptions(); ?>
                        </select>
                    </td>
                </tr>
                <?php
                break;
            case 'dropdown':
                $req = 'required';
                if (!$field->required)
                    $req = '';
                $options = $field->field_options->options;
                ?>
                <tr>
                    <td><label><?php echo $field->label ?></label></td>
                    <td>
                        <select data-label="<?php echo $field->label ?>" class="fcf <?php echo $field->field_type . ' ' . $req; ?>" name="fcf[<?php echo $field->cid ?>]">
                            <?php foreach ($options as $option): ?>
                                <option <?php echo ($option->checked) ? 'selected' : '' ?> value="<?php echo $option->label ?>"><?php echo $option->label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php
                break;
            case 'checkboxes':
                $req = 'required';
                if (!$field->required)
                    $req = '';
                $options = $field->field_options->options;
                ?>
                <input type="hidden" name="fcf[<?php echo $field->cid ?>][array_type]" value="checkboxes">
                <input type="hidden" name="fcf[<?php echo $field->cid ?>][label]" value="<?php echo $field->label ?>">
                <tr>
                    <td><label><?php echo $field->label ?></label></td>
                    <td>
                        <?php foreach ($options as $option): ?>
                            <label><input data-label="<?php echo $field->label ?>" name="fcf[<?php echo $field->cid ?>][]" class="fcf checkbox <?php echo $req; ?>" value="<?php echo $option->label ?>" <?php echo ($option->checked) ? 'checked' : '' ?> type="checkbox"> <?php echo $option->label ?></label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php
                break;
            case 'radio':
                $req = 'required';
                if (!$field->required)
                    $req = '';
                $options = $field->field_options->options;
                ?>
                <tr>
                    <td><label><?php echo $field->label ?></label></td>
                    <td>
                        <?php foreach ($options as $option): ?>
                            <label><input data-label="<?php echo $field->label ?>" name="fcf[<?php echo $field->cid ?>]" class="fcf radio <?php echo $req; ?>" value="<?php echo $option->label ?>" <?php echo ($option->checked) ? 'checked' : '' ?> type="radio"> <?php echo $option->label ?></label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php
                break;
            case 'price':
                $req = 'required';
                if (!$field->required)
                    $req = '';
                if ($field->field_type == 'website')
                    $field->field_type = 'url';
                ?>
                <input type="hidden" name="fcf[<?php echo $field->cid ?>][array_type]" value="price">
                <input type="hidden" name="fcf[<?php echo $field->cid ?>][label]" value="<?php echo $field->label ?>">
                <tr>
                    <td><label><?php echo $field->label ?></label></td>
                    <td>$<input data-label="<?php echo $field->label ?>" class="fcf number <?php echo $field->field_type . ' ' . $req; ?>" name="fcf[<?php echo $field->cid ?>][price]" value="00" type="number" >.<input class="fcf number <?php echo $field->field_type . '_cent ' . $req; ?>" name="fcf[<?php echo $field->cid ?>][cent]" type="number" value="00"></td>
                </tr>
                <?php
                break;
            default:
                break;
        }
    }

    static public function set_html_content_type() {
        return 'text/html';
    }

}
